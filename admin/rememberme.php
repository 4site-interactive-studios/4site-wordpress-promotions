<?php
add_action('acf/options_page/save', 'fwp_rememberme_options_page_save', 10, 2 );
function fwp_rememberme_options_page_save($post_id, $menu_slug) {
	$newline_separated_domains = get_field('promotion_lightbox_domains', 'options');
	if($newline_separated_domains) {
		$domains_list = explode("\n", $newline_separated_domains);
		foreach($domains_list as $idx => $domain) {
			$domains_list[$idx] = trim($domain);
			if(strlen($domains_list[$idx]) === 0) {
				unset($domains_list[$idx]);
			}
		}
		if(count($domains_list)) {
			fwp_generate_rememberme_html($domains_list);
		}	
	}
}

function fwp_generate_rememberme_html($domains_list) {
	$html_template = file_get_contents(plugin_dir_path(__FILE__) . 'rememberme.html.template');
	
	$domains_list_string = '';
	foreach($domains_list as $domain) {
		if($domains_list_string) $domains_list_string .= ",\n                ";
		$domains_list_string .= "'{$domain}'";
	}
	$html_template = str_replace('PLACEHOLDER_DOMAINS', $domains_list_string, $html_template);

	$title = get_bloginfo('name');
	$html_template = str_replace('PLACEHOLDER_TITLE', $title, $html_template);

	$upload_dir = wp_upload_dir();
	$fwp_promo_assets_path = $upload_dir['basedir'] .'/fwp-promo-assets';
	if(!is_dir($fwp_promo_assets_path)) {
		wp_mkdir_p($fwp_promo_assets_path);
	}

	$fh = fopen($fwp_promo_assets_path .'/rememberme.html', 'w');
	fwrite($fh, $html_template);
	fclose($fh);
}
