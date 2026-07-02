<?php

require_once('class-foursite-wordpress-promotion-migration-base.php');

/**
 * Exports selected promotions to a single self-describing JSON document.
 *
 * The document carries each promotion's ACF values (assembled, unformatted) as
 * native JSON, plus the metadata for every media attachment and page they
 * reference. No per-value serialization is used -- json_encode round-trips
 * HTML, newlines, ampersands and unicode losslessly, which the previous
 * XML + serialize() approach could not.
 */
class Foursite_Wordpress_Promotion_Export extends Foursite_Wordpress_Promotion_Migration_Base {
	public function __construct() {
		add_filter('bulk_actions-edit-wordpress_promotion',        [$this, 'bulk_actions']);
		add_filter('handle_bulk_actions-edit-wordpress_promotion', [$this, 'bulk_action_handler'], 10, 3);
		add_action('rest_api_init',                                [$this, 'register_rest_routes']);
	}

	function register_rest_routes() {
		register_rest_route('fswp/v1', '/exports/(?P<file>[A-Za-z0-9._-]+)', array(
			'methods'             => 'GET',
			'callback'            => [$this, 'download_export'],
			'permission_callback' => [$this, 'export_permissions_check'],
		));
	}

	function export_permissions_check() {
		// The download URL is opened via a normal browser navigation, so it
		// carries the auth cookie; the redirect appends a `wp_rest` nonce that
		// lets cookie auth resolve the current user for this REST request.
		return current_user_can('edit_posts');
	}

	function download_export($request) {
		$filename = basename($request['file']);

		// Only serve files we created, and never allow path traversal.
		if (strpos($filename, 'fswp-export-') !== 0 || substr($filename, -5) !== '.json') {
			return new WP_Error('rest_not_found', 'Export not found.', ['status' => 404]);
		}

		$path = $this->assets_dir() . $filename;
		if (!file_exists($path)) {
			return new WP_Error('rest_not_found', 'Export not found.', ['status' => 404]);
		}

		header('Content-Type: application/json');
		header('Content-Length: ' . filesize($path));
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		readfile($path);
		// Remove immediately: the file lives under a web-accessible uploads dir,
		// so we don't leave promotion data sitting there after it's collected.
		wp_delete_file($path);
		exit;
	}

	function bulk_actions($bulk_actions) {
		$bulk_actions['fswp_export'] = __('Export', 'foursite-wordpress-promotions');
		return $bulk_actions;
	}

	/**
	 * Build the export document for the given promotion IDs.
	 */
	function build_document($post_ids) {
		$fields    = $this->schema_fields();
		$media_ids = [];
		$page_ids  = [];
		$promotions = [];

		foreach ($post_ids as $post_id) {
			$post = get_post($post_id);
			if (!$post || $post->post_type !== self::POST_TYPE) {
				continue;
			}

			$values = function_exists('get_fields') ? get_fields($post_id, false) : [];
			if (!is_array($values)) {
				$values = [];
			}

			$this->collect_reference_ids($fields, $values, $media_ids, $page_ids);

			$promotions[] = [
				'source_id' => (int) $post_id,
				'title'     => $post->post_title,
				'status'    => $post->post_status,
				'pub_date'  => mysql2date('c', $post->post_date_gmt ?: $post->post_date),
				'acf'       => $values,
			];
		}

		return [
			'format'         => self::EXPORT_FORMAT,
			'format_version' => self::FORMAT_VERSION,
			'plugin_version' => foursite_wordpress_promotion_VERSION,
			'exported_at'    => gmdate('c'),
			'source_site'    => home_url(),
			'media'          => $this->build_media_manifest(array_keys($media_ids)),
			'pages'          => $this->build_page_manifest(array_keys($page_ids)),
			'promotions'     => $promotions,
		];
	}

	function build_media_manifest($media_ids) {
		$media = [];
		foreach ($media_ids as $id) {
			$url = wp_get_attachment_url($id);
			if (!$url) {
				continue;
			}
			$attachment = get_post($id);
			$media[$id] = [
				'title'    => $attachment ? $attachment->post_title : '',
				'url'      => $url,
				'filename' => basename(parse_url($url, PHP_URL_PATH)),
			];
		}
		return $media;
	}

	function build_page_manifest($page_ids) {
		$pages = [];
		foreach ($page_ids as $id) {
			$page = get_post($id);
			if (!$page) {
				continue;
			}
			$pages[$id] = [
				'title' => $page->post_title,
				'url'   => get_permalink($id),
			];
		}
		return $pages;
	}

	function bulk_action_handler($redirect_to, $action, $post_ids) {
		if ($action !== 'fswp_export') {
			return $redirect_to;
		}
		if (!current_user_can('edit_posts') || !is_array($post_ids) || count($post_ids) === 0) {
			return $redirect_to;
		}

		$dir = $this->assets_dir();
		if (!file_exists($dir)) {
			wp_mkdir_p($dir);
		}
		$this->cleanup_stale_exports($dir);

		// Unguessable filename: the uploads dir is web-readable, so the token
		// (not just the nonce on the REST route) is what protects the file.
		$file_name = 'fswp-export-' . foursite_wordpress_promotion_VERSION . '-' . time()
			. '-' . wp_generate_password(20, false) . '.json';

		$document = $this->build_document($post_ids);
		file_put_contents($dir . $file_name, wp_json_encode($document));

		$url = rest_url('fswp/v1/exports/' . $file_name);
		return add_query_arg('_wpnonce', wp_create_nonce('wp_rest'), $url);
	}

	/** Delete export files older than an hour that were never downloaded. */
	function cleanup_stale_exports($dir) {
		foreach ((array) glob($dir . 'fswp-export-*.json') as $file) {
			if (is_file($file) && (time() - filemtime($file)) > HOUR_IN_SECONDS) {
				wp_delete_file($file);
			}
		}
	}
}

new Foursite_Wordpress_Promotion_Export();
