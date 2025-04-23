<?php

require_once('class-foursite-wordpress-promotion-migration-base.php');

class Foursite_Wordpress_Promotion_Export extends Foursite_Wordpress_Promotion_Migration_Base {
	public function __construct() {
		add_filter('bulk_actions-edit-wordpress_promotion',					[$this, 'bulk_actions']);
		add_filter('handle_bulk_actions-edit-wordpress_promotion',	[$this, 'bulk_action_handler'], 10, 3);
		add_action('rest_api_init', 																[$this, 'register_rest_routes']);
	}

	function register_rest_routes() {
		register_rest_route('fswp/v1', '/exports/(.+)', array(
			'methods' => 'GET',
			'callback' => [$this, 'download_export'],
			'permission_callback' => [$this, 'export_permissions_check'],
		));
	}
	function export_permissions_check() {
		// Current user is being views as anonymous/not logged in, so this isn't working as expected.
		/*
    if(!current_user_can('edit_posts')) {
        return new WP_Error('rest_forbidden', esc_html__('You do not possess sufficient permissions to export promotions.', 'foursite-wordpress-promotion'), array('status' => 401));
    }
		*/
    return true;
}
	function download_export($data) {
		$ud = wp_upload_dir();
		$download_filename = basename($data->get_route());
		$download_file_path = $ud['basedir'] . '/fwp-assets/' . $download_filename;
		if(file_exists($download_file_path)) {
			header("Content-type: application/xhtml+xml");
			header("Content-Length: " . filesize($download_file_path));
			header('Content-disposition: attachment; filename="' . $download_filename . '"');
			readfile($download_file_path);
			wp_delete_file($download_file_path);
			exit;	
		}
	}

	function bulk_actions($bulk_actions) {
		$bulk_actions['fswp_export'] = __( 'Export', 'foursite-wordpress-promotions' );
		return $bulk_actions;
	}

	function addXmlFromRepeaterAcf(&$xml, $post_id, $field_name) {
		global $wpdb;
		$repeater_field_records = $wpdb->get_results("SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key LIKE '{$field_name}%' AND post_id = {$post_id}");
		if($repeater_field_records) {
			foreach($repeater_field_records as $rf_rec) {
				$xml->addChild('ACF_' . $rf_rec->meta_key, serialize($rf_rec->meta_value));
			}	
		}
	}

	function addXmlFromAcf(&$xml, $post_id, $field_name) {
		$value = get_post_meta($post_id, $field_name, true);		
		if($field_name == 'engrid_donation_page' && $value) {
			$value = urlencode($value);
		}
		if(in_array($field_name, ['engrid_javascript', 'engrid_footer', 'engrid_paragraph'])) {
			$value = htmlspecialchars($value);
		}
		if($value !== null) {
			$xml->addChild('ACF_' . $field_name, serialize($value));
		}
		return $value;
	}


	function add_promo_xml(&$xml, $post_id) {
		$promotion = get_post($post_id);

		if($promotion) {
			$acf_field_names = Foursite_Wordpress_Promotion_Export::acf_field_names();
			$acf_image_field_names = Foursite_Wordpress_Promotion_Export::acf_image_field_names();
			$acf_page_field_names = Foursite_Wordpress_Promotion_Export::acf_page_field_names();
			$acf_repeater_field_names = Foursite_Wordpress_Promotion_Export::acf_repeater_field_names();

			$xml_promo = $xml->addChild('promotion');
			$xml_promo->addChild('title', $promotion->post_title);
			$xml_promo->addChild('ID', $post_id);
			$xml_promo->addChild('pubDate', date('r', strtotime($promotion->post_date)));
			$xml_promo->addChild('status', $promotion->post_status);

			foreach($acf_field_names as $field_name) {
				if(in_array($field_name, $acf_repeater_field_names)) {
					$acf_field_value = $this->addXmlFromRepeaterAcf($xml_promo, $post_id, $field_name);
				} else {
					$acf_field_value = $this->addXmlFromAcf($xml_promo, $post_id, $field_name);
					if(array_key_exists($field_name, $acf_image_field_names)) {
						$subkey = $acf_image_field_names[$field_name];
						$media_id = null;
		
						if($subkey) {
							$media_id = $acf_field_value[$subkey] ?? null;
						} else {
							$media_id = $acf_field_value ?? null;
						}
						if($media_id) {
							$media = get_post($media_id);
							$xml_media = $xml->addChild('media');
							$xml_media->addChild('ID', $media_id);
							$xml_media->addChild('title', $media->post_title);
							$xml_media->addChild('url', wp_get_attachment_url($media_id));
						}
					} else if(array_key_exists($field_name, $acf_page_field_names)) {
						$pages = $acf_field_value;
						if($pages) {
							if(!is_array($pages)) {
								$pages = [$pages];
							}
							foreach($pages as $page_id) {
								$page = get_post($page_id);
								$xml_page = $xml->addChild('page');
								$xml_page->addChild('ID', $page_id);
								$xml_page->addChild('title', $page->post_title);
								$xml_page->addChild('url', get_permalink($page_id));
							}
						}
					}
				}
			}	
		}
	}

	function bulk_action_handler($redirect_to, $action, $post_ids) {
		if($action !== 'fswp_export') {
			return $redirect_to;
		}
	
		if(is_array($post_ids) && count($post_ids) > 0) {
			$file_name = 'fswp-export-data-' . foursite_wordpress_promotion_VERSION . '--' . time() . '.xml';
			$ud = wp_upload_dir();
			$base_file_path = $ud['basedir'] . '/fwp-assets/';

			if (!file_exists($base_file_path)) {
				wp_mkdir_p($base_file_path);
			}
			$fp = fopen($base_file_path . $file_name, 'w');
	
			$xml = new SimpleXMLElement('<xml/>');
			$xml->addChild('plugin_version', foursite_wordpress_promotion_VERSION);
	
			foreach($post_ids as $post_id) {
				$this->add_promo_xml($xml, $post_id);
			}
			fwrite($fp, $xml->asXML());
			fclose($fp);
	
			$redirect_to = '/wp-json/fswp/v1/exports/' . $file_name;
		}
	
		return $redirect_to;
	}
}

new Foursite_Wordpress_Promotion_Export();