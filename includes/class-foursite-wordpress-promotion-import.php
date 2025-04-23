<?php

require_once('class-foursite-wordpress-promotion-migration-base.php');

class Foursite_Wordpress_Promotion_Import extends Foursite_Wordpress_Promotion_Migration_Base {
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
		if(isset($_FILES['export_file']['tmp_name'])) {
			$this->import_promos_from_xml($_FILES['export_file']['tmp_name']);
		}

		echo "
			<form method='post' enctype='multipart/form-data'>
				<h3>Select XML export to upload:</h3>
				<input type='file' name='export_file' id='export_file'>
				<input type='submit' value='Upload XML export' name='submit'>
			</form>
		";
	}

	function does_media_exist($fid, $url) {
		$filename = basename($url);
		$local_media_url = wp_get_attachment_url($fid);
		if($local_media_url) {
			$local_filename = basename($local_media_url);
			if($filename == $local_filename) {
				return true;
			}
		}
		return false;
	}
	
	function import_external_media($url) {
		$tmp = download_url($url);
		if(is_wp_error($tmp)) {
			@unlink($tmp);
			return null;
		}

		$file_array = array(
			'name' => basename($url),
			'tmp_name' => $tmp
		);

		$fid = media_handle_sideload($file_array, 0);
		if(is_wp_error($fid)) {
			@unlink($tmp);
			return null;
		}

		return $fid;
	}

	function import_media_if_not_exist($media) {
		$media_map = [];
		foreach($media as $fid => $file_data) {
			$url = $file_data['url'];
			$file_exists = $this->does_media_exist($fid, $url);
			if($file_exists) {
				$media_map[$fid] = $fid;
			} else {
				$local_fid = $this->import_external_media($url);
				$media_map[$fid] = $local_fid;
			}
		}
		return $media_map;
	}

	function check_if_pages_exist($pages) {
		$page_map = [];		

		foreach($pages as $id => $page) {
			$local_title = get_the_title($id);
			if($local_title === $page['title']) {
				$page_map[$id] = $id;
			} else {
				$page_map[$id] = null;
			}
		}

		return $page_map;
	}

	function import_promos_from_xml($xml_file) {
		$xml = simplexml_load_file($xml_file);

		$media = [];
		if(isset($xml->media)) {
			foreach($xml->media as $media_item) {
				$media[(int) $media_item->ID] = ['title' => (string) $media_item->title, 'url' => (string) $media_item->url];
			}
		}

		$pages = [];
		if(isset($xml->page)) {
			foreach($xml->page as $page_item) {
				$pages[(int) $page_item->ID] = ['title' => (string) $page_item->title, 'url' => (string) $page_item->url];
			}
		}

		$force_field_names = Foursite_Wordpress_Promotion_Import::acf_include_if_empty_field_names();

		$promos = [];
		if(isset($xml->promotion)) {
			foreach($xml->promotion as $xml_promo) {
				$promo_data = ['acf' => []];
				foreach($xml_promo->children() as $child) {
					$field_name = $child->getName();
					if(strpos($field_name, 'ACF_') === 0) {
						$field_name = substr($field_name, 4);
						// the ACF fields are serialized to account for different data types
						$field_value = unserialize((string) $child);
						if($field_name == 'engrid_donation_page') {
							$field_value = urldecode($field_value);
						} else if(in_array($field_name, ['engrid_javascript', 'engrid_footer', 'engrid_paragraph'])) {
							$field_value = htmlspecialchars_decode($field_value);
						}
						$promo_data['acf'][$field_name] = $field_value;
					} else {
						$promo_data[$field_name] = (string) $child;
					}
				}

				// Set all imported promos to be turned off
				$promo_data['acf']['engrid_lightbox_display'] = 'turned-off';

				foreach($force_field_names as $field_name => $default_value) {
					if(!array_key_exists($field_name, $promo_data['acf'])) {
						$promo_data['acf'][$field_name] = $default_value;
					}
				}

				$promos[(int) $xml_promo->ID] = $promo_data;
			}
		}

		// Update invalid media IDs with imported media IDs
		$media_map = $this->import_media_if_not_exist($media);
		$acf_image_field_names = Foursite_Wordpress_Promotion_Export::acf_image_field_names();
		foreach($promos as $promo_id => $promo_data) {
			foreach($promos[$promo_id]['acf'] as $field_name => $field_value) {
				if(array_key_exists($field_name, $acf_image_field_names)) {
					$subkey = $acf_image_field_names[$field_name];
					if($subkey) {
						if(!is_array($promos[$promo_id]['acf'][$field_name])) {
							$promos[$promo_id]['acf'][$field_name] = [];
						}
						if(is_array($field_value)) {
							$promos[$promo_id]['acf'][$field_name][$subkey] = $media_map[$field_value[$subkey]] ?? null;
						} else {
							$promos[$promo_id]['acf'][$field_name][$subkey] = null;
						}
					} else {						
						$promos[$promo_id]['acf'][$field_name] = $media_map[$field_value] ?? null;
					}
				}
			}
		}

		// Update invalid page IDs with nulls -- we're not going to import pages
		$page_map = $this->check_if_pages_exist($pages);
		$acf_page_field_names = Foursite_Wordpress_Promotion_Export::acf_page_field_names();
		foreach($promos as $promo_id => $promo_data) {
			foreach($promos[$promo_id]['acf'] as $field_name => $field_value) {
				if(array_key_exists($field_name, $acf_page_field_names)) {
					$subkey = $acf_page_field_names[$field_name];
					if($subkey) {
						if(is_array($field_value[$subkey])) {
							foreach($field_value[$subkey] as $idx => $page_id) {
								$promos[$promo_id]['acf'][$field_name][$subkey][$idx] = $page_map[$page_id] ?? null;
							}
						} else {
							$promos[$promo_id]['acf'][$field_name][$subkey] = $page_map[$field_value[$subkey]] ?? null;
						}
					} else {
						if(is_array($field_value)) {
							foreach($field_value as $idx => $page_id) {
								$promos[$promo_id]['acf'][$field_name][$idx] = $page_map[$page_id] ?? null;
							}
						} else {
							$promos[$promo_id]['acf'][$field_name] = $page_map[$field_value] ?? null;
						}
					}
				}
			}
		}

		foreach($promos as $promo_id => $promo_data) {
			$insert_data = [
				'post_title' => $promo_data['title'], 
				'post_status' => $promo_data['status'], 
				//'post_date' => date('Y-m-d h:i:s', strtotime($promo_data['pubDate'])),
				'post_type' => 'wordpress_promotion', 
				'meta_input' => $promo_data['acf']
			];

			$insert_result = wp_insert_post($insert_data);
			if(is_wp_error($insert_result)) {
				echo "<p>Error importing promo: {$insert_result->get_error_message()}</p>";
			} else {
				$promo_title = get_the_title($insert_result);
				echo "
					<p>Imported promo: <a href='/wp-admin/post.php?post={$insert_result}&action=edit'>{$insert_result} | {$promo_title}</a></p>
				";
			}
		}

		echo "
			<p>
				NOTE: The imported promos are turned off by default.<br>
				NOTE: Pages where promos are scheduled to appear are NOT be imported. You will need to manually set these.<br>
			</p>
		";
	}
}

new Foursite_Wordpress_Promotion_Import();