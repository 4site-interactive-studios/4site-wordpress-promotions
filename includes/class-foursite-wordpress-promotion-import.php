<?php

require_once('class-foursite-wordpress-promotion-migration-base.php');

/**
 * Imports promotions from a JSON export document produced by the export class.
 *
 * Media referenced by the export is downloaded (or matched to existing local
 * media by filename) and remapped to local attachment IDs; page references are
 * remapped to same-titled local pages where they exist. ACF values are written
 * with update_field() so groups/repeaters are decomposed correctly and each
 * value's `_fieldname` field-key reference is stored -- meaning imported values
 * actually populate the ACF editor UI.
 */
class Foursite_Wordpress_Promotion_Import extends Foursite_Wordpress_Promotion_Migration_Base {
	/** Human-readable messages for media that failed to import, shown in the report. */
	protected $media_errors = [];

	public function __construct() {
		add_action('admin_menu', [$this, 'admin_menu']);
	}

	function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=wordpress_promotion',
			__('Import Promos', 'four-site-wordpress-promotions'),
			__('Import Promos', 'four-site-wordpress-promotions'),
			'manage_options',
			'fswp-import-promos',
			[$this, 'render_import_promos_page']
		);
	}

	function render_import_promos_page() {
		if (!current_user_can('manage_options')) {
			return;
		}

		if (
			isset($_POST['fswp_import_nonce'])
			&& wp_verify_nonce($_POST['fswp_import_nonce'], 'fswp_import_promos')
			&& !empty($_FILES['export_file']['tmp_name'])
			&& is_uploaded_file($_FILES['export_file']['tmp_name'])
		) {
			$this->import_promos_from_file($_FILES['export_file']['tmp_name']);
		}

		$nonce_field = wp_nonce_field('fswp_import_promos', 'fswp_import_nonce', true, false);
		echo "
			<div class='wrap'>
				<form method='post' enctype='multipart/form-data'>
					{$nonce_field}
					<h3>Select a JSON export to upload:</h3>
					<input type='file' name='export_file' id='export_file' accept='.json,application/json'>
					<input type='submit' class='button button-primary' value='Upload export' name='submit'>
				</form>
			</div>
		";
	}

	function import_promos_from_file($file) {
		// download_url() / media_handle_sideload() live in these admin includes,
		// which are not guaranteed to be loaded on this request.
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		$document = json_decode((string) file_get_contents($file), true);

		if (!is_array($document) || ($document['format'] ?? '') !== self::EXPORT_FORMAT) {
			echo "<p>Import failed: this file is not a valid promotions export.</p>";
			return;
		}
		if ((int) ($document['format_version'] ?? 0) !== self::FORMAT_VERSION) {
			echo "<p>Import failed: this export was made with an incompatible version of the plugin.</p>";
			return;
		}

		$this->media_errors = [];

		// WordPress blocks HTTP requests to hosts that resolve to private/loopback
		// IPs (SSRF protection). That stops us pulling media from the source site
		// when both sites are local (e.g. *.local -> 127.0.0.1). Permit fetches
		// from this export's declared source host for the duration of the media
		// import, then restore the default policy.
		$source_host = wp_parse_url($document['source_site'] ?? '', PHP_URL_HOST);
		$allow_source = null;
		if ($source_host) {
			$allow_source = function ($is_external, $host) use ($source_host) {
				return (strcasecmp($host, $source_host) === 0) ? true : $is_external;
			};
			add_filter('http_request_host_is_external', $allow_source, 10, 2);
		}

		$media_map = $this->build_media_map($document['media'] ?? []);

		if ($allow_source) {
			remove_filter('http_request_host_is_external', $allow_source, 10);
		}

		$page_map  = $this->build_page_map($document['pages'] ?? []);
		$fields_by_name = $this->schema_fields_by_name();

		// Pass 1: create every promo, remapping media and pages. Promo-to-promo
		// references are deferred so they can point at the new local promo IDs.
		$imported   = [];
		$promo_map  = [];
		foreach (($document['promotions'] ?? []) as $promo) {
			$result = $this->import_single_promo($promo, $fields_by_name, $media_map, $page_map);
			if ($result) {
				$imported[] = $result;
				if ($result['source_id']) {
					$promo_map[$result['source_id']] = $result['new_id'];
				}
			}
		}

		// Pass 2: resolve promo-to-promo references (e.g. AB Test candidates).
		$unresolved_refs = $this->apply_promo_reference_pass($imported, $fields_by_name, $promo_map, $document['pages'] ?? []);

		if ($unresolved_refs) {
			echo "<div class='notice notice-warning'><p><strong>Some referenced promotions could not be matched</strong> — they weren't part of this export and no existing promotion on this site shares their title, so those references (e.g. AB Test candidates) were left empty. Export them together and re-import, or set the references manually:</p><ul style='list-style:disc;margin-left:2em;'>";
			foreach ($unresolved_refs as $title) {
				echo '<li>' . esc_html($title) . '</li>';
			}
			echo "</ul></div>";
		}

		if ($this->media_errors) {
			echo "<div class='notice notice-error'><p><strong>Some images could not be imported</strong> and were left empty on the affected promos — fix the cause and re-import, or set them manually:</p><ul style='list-style:disc;margin-left:2em;'>";
			foreach ($this->media_errors as $err) {
				echo '<li>' . esc_html($err) . '</li>';
			}
			echo "</ul></div>";
		}

		echo "
			<p>
				NOTE: Imported promos are turned off by default.<br>
				NOTE: Page targeting is only carried over where a page with the same title exists on this site; otherwise you will need to set it manually.<br>
			</p>
		";
	}

	function import_single_promo($promo, $fields_by_name, array $media_map, array $page_map) {
		$acf = is_array($promo['acf'] ?? null) ? $promo['acf'] : [];
		// Remap media and pages now; leave promo-to-promo references (promo_map
		// null) until every promo in the batch has a local ID (second pass).
		$acf = $this->remap_reference_ids($fields_by_name, $acf, $media_map, $page_map, null);

		// Imported promos always start disabled.
		$acf['engrid_lightbox_display'] = 'turned-off';

		$post_data = [
			'post_title'  => $promo['title'] ?? '',
			'post_status' => $promo['status'] ?? 'draft',
			'post_type'   => self::POST_TYPE,
		];
		if (!empty($promo['pub_date'])) {
			$post_data['post_date'] = get_date_from_gmt(gmdate('Y-m-d H:i:s', strtotime($promo['pub_date'])));
		}

		$new_id = wp_insert_post($post_data, true);
		if (is_wp_error($new_id)) {
			echo "<p>Error importing promo: {$new_id->get_error_message()}</p>";
			return null;
		}

		// Write via ACF so containers decompose and field-key references are set.
		// update_field() runs the value through wp_unslash() (it expects slashed,
		// form-style input), so wp_slash() our already-clean JSON values first to
		// keep literal backslashes (CSS codes, regexes, Windows paths) intact.
		foreach ($acf as $name => $value) {
			$selector = isset($fields_by_name[$name]) ? $fields_by_name[$name]['key'] : $name;
			update_field($selector, wp_slash($value), $new_id);
		}

		$title = get_the_title($new_id);
		echo "<p>Imported promo: <a href='" . esc_url(admin_url("post.php?post={$new_id}&action=edit")) . "'>{$new_id} | " . esc_html($title) . "</a></p>";

		return ['new_id' => $new_id, 'source_id' => (int) ($promo['source_id'] ?? 0), 'acf' => $acf];
	}

	/**
	 * Second pass: now that every promo in the batch has a local ID, resolve
	 * promo-to-promo references (e.g. AB Test candidates) and write just those
	 * fields. Each referenced source promo resolves to, in order of preference:
	 * (1) the promo imported alongside it in this batch, else (2) an existing
	 * local promo with the same title, else it is dropped and reported.
	 */
	function apply_promo_reference_pass(array $imported, $fields_by_name, array $promo_map, $pages) {
		// Every source promo id referenced anywhere in the batch.
		$referenced = [];
		foreach ($imported as $item) {
			$this->collect_promo_ref_ids(array_values($fields_by_name), $item['acf'], $referenced);
		}

		// Build the resolution map: batch imports first, then title fallback.
		$resolved   = $promo_map;
		$unresolved = [];
		foreach (array_keys($referenced) as $src_id) {
			if (isset($resolved[$src_id])) {
				continue;
			}
			$title = $pages[$src_id]['title'] ?? '';
			$local = $this->find_promo_by_title($title);
			if ($local) {
				$resolved[$src_id] = $local;
			} else {
				$unresolved[$src_id] = ($title !== '') ? $title : "promo #{$src_id}";
			}
		}

		// Write the promo-reference fields using the resolved map.
		foreach ($imported as $item) {
			foreach ($fields_by_name as $name => $field) {
				if (!array_key_exists($name, $item['acf']) || !$this->field_contains_promo_ref($field)) {
					continue;
				}
				$remapped = $this->remap_field($field, $item['acf'][$name], null, null, $resolved);
				update_field($field['key'], wp_slash($remapped), $item['new_id']);
			}
		}

		return $unresolved;
	}

	/** Find an existing local promotion by exact title. */
	function find_promo_by_title($title) {
		if ($title === '' || $title === null) {
			return null;
		}
		$query = new WP_Query([
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'any',
			'title'          => $title,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		]);
		return !empty($query->posts) ? (int) $query->posts[0] : null;
	}

	/**
	 * Map source attachment IDs to local IDs, importing files that aren't
	 * already present (matched by filename, so it works across sites/domains).
	 */
	function build_media_map($media) {
		$map = [];
		foreach ($media as $source_id => $info) {
			$source_id = (int) $source_id;
			$url = $info['url'] ?? '';

			$existing = $this->find_existing_media($url);
			if ($existing) {
				$map[$source_id] = $existing;
				continue;
			}
			$map[$source_id] = $this->import_external_media($url);
		}
		return $map;
	}

	function find_existing_media($url) {
		if (!$url) {
			return null;
		}
		$filename = basename(parse_url($url, PHP_URL_PATH));
		if (!$filename) {
			return null;
		}

		global $wpdb;
		$id = $wpdb->get_var($wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s LIMIT 1",
			'%/' . $wpdb->esc_like($filename)
		));
		return $id ? (int) $id : null;
	}

	function import_external_media($url) {
		if (!$url) {
			return null;
		}
		$tmp = download_url($url);
		if (is_wp_error($tmp)) {
			$this->media_errors[] = $url . ' — could not be downloaded: ' . $tmp->get_error_message();
			return null;
		}

		$file_array = [
			'name'     => basename(parse_url($url, PHP_URL_PATH)),
			'tmp_name' => $tmp,
		];
		$fid = media_handle_sideload($file_array, 0);
		if (is_wp_error($fid)) {
			@unlink($tmp);
			$this->media_errors[] = $url . ' — could not be imported: ' . $fid->get_error_message();
			return null;
		}
		return (int) $fid;
	}

	/**
	 * Map source page IDs to local pages that share the same title. Pages are
	 * not created; unmatched references map to null (dropped on remap).
	 */
	function build_page_map($pages) {
		$map = [];
		foreach ($pages as $source_id => $info) {
			$source_id = (int) $source_id;
			$title = $info['title'] ?? '';
			$map[$source_id] = $title !== '' ? $this->find_page_by_title($title) : null;
		}
		return $map;
	}

	function find_page_by_title($title) {
		$query = new WP_Query([
			'post_type'      => 'any',
			'post_status'    => 'any',
			'title'          => $title,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		]);
		return !empty($query->posts) ? (int) $query->posts[0] : null;
	}
}

new Foursite_Wordpress_Promotion_Import();
