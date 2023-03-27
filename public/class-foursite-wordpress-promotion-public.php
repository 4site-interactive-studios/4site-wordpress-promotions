<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.4sitestudios.com
 * @since      1.0.0
 *
 * @package    Foursite_Wordpress_Promotion
 * @subpackage Foursite_Wordpress_Promotion/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Foursite_Wordpress_Promotion
 * @subpackage Foursite_Wordpress_Promotion/public
 * @author     Fernando Santos <fernando@4sitestudios.com>
 */
class Foursite_Wordpress_Promotion_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $foursite_wordpress_promotion    The ID of this plugin.
	 */
	private $foursite_wordpress_promotion;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $foursite_wordpress_promotion       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $foursite_wordpress_promotion, $version ) {

		$this->foursite_wordpress_promotion = $foursite_wordpress_promotion;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Foursite_Wordpress_Promotion_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Foursite_Wordpress_Promotion_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->foursite_wordpress_promotion, plugin_dir_url( __FILE__ ) . 'css/foursite-wordpress-promotion-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Get the lightbox from the database
	 *
	 * @since    1.0.0
	 */

	public function get_lightbox_ids() {
		// Get the current page URL
		$current_page_url = get_permalink();
		// Get the current page ID
		$current_page_id = url_to_postid( $current_page_url );
		$show_on_args = array(
			'relation' => 'OR',
			array(
					'key' => 'engrid_show_on',
					'value' => '"'.$current_page_id.'"',
					'compare' => 'LIKE',
				),
				array(
					'key' => 'engrid_show_on',
					'value' => '',
					'compare' => '=',
				),
			);
			
		$hide_on_args = array(
			'relation' => 'OR',
			array(
					'key' => 'engrid_hide_on',
					'value' => '"'.$current_page_id.'"',
					'compare' => 'NOT LIKE',
				),
				array(
					'key' => 'engrid_hide_on',
					'value' => '',
					'compare' => '=',
				),
			);
		$hide_on_args = array(
			'relation' => 'OR',
			array(
					'key' => 'engrid_hide_on',
					'value' => '"'.$current_page_id.'"',
					'compare' => 'NOT LIKE',
				),
				array(
					'key' => 'engrid_hide_on',
					'value' => '',
					'compare' => '=',
				),
			);
		$display_args = array(
			'relation' => 'OR',
			array(
				'key' => 'engrid_lightbox_display',
				'value' => 'turned-off',
				'compare' => '!=',
			),
			array(
				'key' => 'engrid_lightbox_display',
				'value' => '',
				'compare' => '=',
			),
		);
		$args = array(
			'numberposts'	=> -1,
			'post_type'		=> 'wordpress_promotion',
			'post_status'   => 'publish',
			'meta_query'	=> array(
				'relation'		=> 'AND',
				$show_on_args,
				$hide_on_args,
				$display_args,
			)
		);

		$lightbox = new WP_Query( $args );
		$lightbox_ids = [];

		if($lightbox->posts){
			foreach($lightbox->posts as $lightbox){
				$lightbox_id = $lightbox->ID;
				$whitelist = get_field('engrid_whitelist', $lightbox_id);
				$blacklist = get_field('engrid_blacklist', $lightbox_id);
				$lightbox_start = get_field('engrid_start_date', $lightbox_id);
				$lightbox_end = get_field('engrid_end_date', $lightbox_id);
				$lightbox_display = get_field("engrid_lightbox_display", $lightbox_id);
				
				if($whitelist){
					// Explode the whitelist into an array
					$whitelist_array = explode(',', $whitelist);
					foreach($whitelist_array as $whitelist_item){
						// Trim the whitespace from the whitelist item
						$whitelist_item = trim($whitelist_item);
						// Check if the current page URL contains the whitelist item
						if(strpos($current_page_url, $whitelist_item) !== false){
							// If it does, show the lightbox
							$lightbox_ids[] = $lightbox_id;
						}

					}
					// If whitelist is not empty and the current page URL does not contain any of the whitelist items, do not show the lightbox
					continue;
				}
				elseif($blacklist){
					// Explode the blacklist into an array
					$blacklist_array = explode(',', $blacklist);
					foreach($blacklist_array as $blacklist_item){
						// Trim the whitespace from the blacklist item
						$blacklist_item = trim($blacklist_item);
						// Check if the current page URL contains the blacklist item
						if(strpos($current_page_url, $blacklist_item) !== false){
							// If it does, do not show the lightbox
							continue;
						}
					}
					// If blacklist is not empty and the current page URL does not contain any of the blacklist items, show the lightbox
					$lightbox_ids[] = $lightbox_id;
				}

				// Check if scheduled lightbox is in date range
				elseif($lightbox_display == "scheduled" && $lightbox_start && $lightbox_end) {
					$today_date = date("Ymd");

					if($today_date >= date_format(date_create($lightbox_start), "Ymd") && $today_date <= date_format(date_create($lightbox_end), "Ymd")) {
						$lightbox_ids[] = $lightbox_id;
					} 
				}

				else{
					// If whitelist and blacklist are empty, show the lightbox
					$lightbox_ids[] = $lightbox_id;
				}
			}
		}

		if(count($lightbox_ids) > 0) {
			return $lightbox_ids;
		} else {
			return false;
		}
	}



	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$lightbox_ids = $this->get_lightbox_ids();
		if(!$lightbox_ids) return;


		// populate this with raw html configs & js-triggered multisteps
		$client_side_triggered_config = [];

		foreach($lightbox_ids as $lightbox_id){
			$engrid_donation_page = get_field('engrid_donation_page', $lightbox_id);
			$engrid_promotion_type = trim(get_field('engrid_promotion_type', $lightbox_id));
			$engrid_trigger_type = trim(get_field('engrid_trigger_type', $lightbox_id));
			$engrid_hero_type = get_field('engrid_hero_type', $lightbox_id);
			$engrid_image = ($engrid_hero_type == 'image' || $engrid_promotion_type == "signup_lightbox") ? get_field('engrid_image', $lightbox_id) : '';
			$engrid_video = ($engrid_hero_type != 'image') ? get_field('engrid_video', $lightbox_id) : '';
			$engrid_use_logo = get_field('engrid_use_logo', $lightbox_id);
			$engrid_logo = ($engrid_use_logo || $engrid_promotion_type == 'signup_lightbox') ? get_field('engrid_logo', $lightbox_id) : '';
			$engrid_logo_position = get_field('engrid_logo_position', $lightbox_id);
			$engrid_divider = get_field('engrid_divider', $lightbox_id);
			$engrid_title = get_field('engrid_title', $lightbox_id);
			$engrid_paragraph = get_field('engrid_paragraph', $lightbox_id);
			$engrid_footer = get_field('engrid_footer', $lightbox_id);
			$engrid_bg_color = get_field('engrid_bg_color', $lightbox_id);
			$engrid_text_color = get_field('engrid_text_color', $lightbox_id);
			$engrid_form_color = get_field('engrid_form_color', $lightbox_id);
			$engrid_show_on = get_field('engrid_show_on', $lightbox_id);
			$engrid_hide_on = get_field('engrid_hide_on', $lightbox_id);
			$engrid_start_date = get_field('engrid_start_date', $lightbox_id);
			$engrid_end_date = get_field('engrid_end_date', $lightbox_id);
			$engrid_cookie_hours = get_field('engrid_cookie_hours', $lightbox_id);
			$engrid_cookie_name = get_field('engrid_cookie_name', $lightbox_id);
			$engrid_trigger_seconds = get_field('engrid_trigger_seconds', $lightbox_id);
			$engrid_trigger_scroll_pixels = get_field('engrid_trigger_scroll_pixels', $lightbox_id);
			$engrid_trigger_scroll_percentage = get_field('engrid_trigger_scroll_percentage', $lightbox_id);
			$engrid_gtm_open_event_name = get_field('engrid_gtm_open_event_name', $lightbox_id);
			$engrid_gtm_close_event_name = get_field('engrid_gtm_close_event_name', $lightbox_id);
			$engrid_gtm_suppressed_event_name = get_field('engrid_gtm_suppressed_event_name', $lightbox_id);
			$engrid_display = get_field('engrid_lightbox_display', $lightbox_id);
			$engrid_js = get_field('engrid_javascript', $lightbox_id);
			$engrid_html = get_field('engrid_html', $lightbox_id);
			$engrid_css = get_field('engrid_css', $lightbox_id);
			$confetti = array();
			$engrid_pushdown_type = get_field('engrid_pushdown_type', $lightbox_id);
			$engrid_pushdown_image = get_field('engrid_pushdown_image', $lightbox_id);
			$engrid_pushdown_gif = get_field('engrid_pushdown_gif', $lightbox_id) ? get_field('engrid_pushdown_gif', $lightbox_id) : "";
			$engrid_pushdown_link = get_field('engrid_pushdown_link', $lightbox_id);
			$engrid_pushdown_title = get_field('engrid_pushdown_title', $lightbox_id);
			$resized_pushdown_image = $engrid_pushdown_image["sizes"]["2048x2048"];
			$engrid_signup_info = get_field('engrid_signup_info', $lightbox_id);

			if(have_rows('engrid_confetti', $lightbox_id) ){
				while( have_rows('engrid_confetti', $lightbox_id) ){
					the_row();
					$confetti[] = get_sub_field('color');
				}
			}

			$trigger = 0;
			switch($engrid_trigger_type) {
				case "0":
					$trigger = 0;
					break;
				case 'seconds':
					$trigger = $engrid_trigger_seconds;
					break;
				case 'px':
					$trigger = $engrid_trigger_scroll_pixels.'px';
					break;
				case '%':
					$trigger = $engrid_trigger_scroll_percentage.'%';
					break;
				case 'exit':
					$trigger = 'exit';
					break;
				case 'js':
					$trigger = 'js';
					break;
			}

			$engrid_video_auto_play = ($engrid_hero_type == 'autoplay-video') ? 'true' : 'false';
			$engrid_confetti = json_encode($confetti);

			if($engrid_promotion_type == "multistep_lightbox") {
				$client_side_triggered_config[$lightbox_id] = [
					'promotion_type' => $engrid_promotion_type, 
					'url' => $engrid_donation_page, 
					'image' => $engrid_image, 
					'logo' => $engrid_logo, 
					'video' => $engrid_video,
					'autoplay' => $engrid_video_auto_play,
					'logo_position_top' => "{$engrid_logo_position['top']}px",
					'logo_position_left' => "{$engrid_logo_position['left']}px",
					'logo_position_right' => "{$engrid_logo_position['right']}px",
					'logo_position_bottom' => "{$engrid_logo_position['bottom']}px",
					'divider' => $engrid_divider,
					'title' => $engrid_title,
					'paragraph' => $engrid_paragraph,
					'footer' => $engrid_footer,
					'bg_color' => $engrid_bg_color,
					'txt_color' => $engrid_text_color,
					'form_color' => $engrid_form_color,
					'cookie_hours' => $engrid_cookie_hours,
					'cookie_name' => $engrid_cookie_name,
					'trigger' => $trigger,
					'gtm_open_event_name' => $engrid_gtm_open_event_name,
					'gtm_close_event_name' => $engrid_gtm_close_event_name,
					'gtm_suppressed_event_name' => $engrid_gtm_suppressed_event_name,
					'confetti' => $engrid_confetti,
				];

				wp_enqueue_script( $this->foursite_wordpress_promotion, plugin_dir_url( __FILE__ ) . 'multistep/dist/donation-lightbox-parent.js', array(), $this->version, false );
				wp_enqueue_script( 'foursite-wordpress-promotion-public', plugin_dir_url( __FILE__ ) . 'js/foursite-wordpress-promotion-public.js', array( 'jquery', $this->foursite_wordpress_promotion ), $this->version, false );

			} else if($engrid_promotion_type == "raw_code") {

				$client_side_triggered_config[$lightbox_id] = [
					'promotion_type' => $engrid_promotion_type, 
					'html' => $engrid_html, 
					'js' => $engrid_js, 
					'css' => $engrid_css, 
					'cookie_name' => $engrid_cookie_name, 
					'cookie_hours' => $engrid_cookie_hours, 
					'id' => $lightbox_id, 
					'trigger' => $trigger, 
					'is_lightbox' => get_field('is_lightbox', $lightbox_id)
				];
				wp_enqueue_script( 'foursite-wordpress-promotion-public', plugin_dir_url( __FILE__ ) . 'js/foursite-wordpress-promotion-public.js', array( 'jquery', $this->foursite_wordpress_promotion ), $this->version, false );

			} else if ($engrid_promotion_type == "pushdown") {

				$client_side_triggered_config[$lightbox_id] = [
					'promotion_type' => $engrid_promotion_type, 
					'url' => $engrid_pushdown_link,
					'pushdown_type' => $engrid_pushdown_type,
					'pushdown_title' => $engrid_pushdown_title,
					'image' => esc_url($resized_pushdown_image),
					'gif' => $engrid_pushdown_gif,
					'trigger' => $trigger,
					'cookie_name' => $engrid_cookie_name, 
					'cookie_hours' => $engrid_cookie_hours, 
					'id' => $lightbox_id, 
					'src' => plugins_url('pushdown/js/pushdown.js', __FILE__),
				];
				wp_enqueue_script( 'foursite-wordpress-promotion-public', plugin_dir_url( __FILE__ ) . 'js/foursite-wordpress-promotion-public.js', array( 'jquery', $this->foursite_wordpress_promotion ), $this->version, false );

			} else if($engrid_promotion_type == "signup_lightbox") {

				$signup_trigger = $engrid_trigger_seconds * 1000; // Convert to milliseconds
				$engrid_js_code = <<<ENGRID
				fs_signup_options = {
					promotion_type: "$engrid_promotion_type",
					url: "$engrid_donation_page",
					imageURL: "$engrid_image",
					logoURL: "$engrid_logo",
					divider: "$engrid_divider",
					title: "$engrid_title",
					paragraph: `$engrid_paragraph`,
					info: `$engrid_footer`,
					cookie_hours: $engrid_cookie_hours,
					cookie_name: "$engrid_cookie_name",
					trigger: "$signup_trigger",
					gtm_open_event_name: "$engrid_gtm_open_event_name",
					gtm_close_event_name: "$engrid_gtm_close_event_name",
					gtm_suppressed_event_name: "$engrid_gtm_suppressed_event_name",
					confetti: $engrid_confetti,
					dates: [],
					blacklist: [],
					whitelist: [],
					iframe: `<iframe width='100%' scrolling='no' class='en-iframe ' data-src='$engrid_donation_page' frameborder='0' allowfullscreen='' style='display:none' allow='autoplay; encrypted-media'></iframe>`,
				};
				ENGRID;
				
				wp_enqueue_script('foursite-wordpress-signup-lightbox', plugin_dir_url( __FILE__ ) . 'signup/js/website-lightbox.js', array( 'jquery' ), $this->version, false);
				wp_add_inline_script('foursite-wordpress-signup-lightbox', $engrid_js_code, 'before');

			} else if($engrid_promotion_type == "floating_tab") {

				wp_enqueue_style('fs-floating-tab', plugins_url('floating-tab/fs-floating-tab.css', __FILE__));
				
				$fsft_colors = get_field('engrid_fsft_color', $lightbox_id);
				$fsft_radius = get_field('engrid_fsft_radius', $lightbox_id);
				$fsft_location = get_field('engrid_fsft_location', $lightbox_id);
				$fsft_link = get_field('engrid_fsft_link', $lightbox_id);
				$fsft_css = get_field('engrid_css', $lightbox_id);
				$fsft_trigger = get_field('engrid_fsft_trigger_type', $lightbox_id);
				// $fsft_lightbox = get_field('engrid_use_lightbox', $lightbox_id);
				$fsft_lightbox = $fsft_link['engrid_use_lightbox'];
				$fsft_svg = get_field('engrid_custom_svg', $lightbox_id);
				$fsft_id = 'fs-donation-tab';

				$style = '';
				if(!empty($fsft_colors['foreground'])) $style .= "color: {$fsft_colors['foreground']};";
				if(!empty($fsft_colors['background'])) $style .= "background-color: {$fsft_colors['background']};";
				if(!empty($fsft_radius)) $style .= "border-radius: {$fsft_radius} {$fsft_radius} 0 0;";

				$classes = "{$fsft_location}";

				$attributes = '';
				if(is_array($fsft_link['attributes'])) {
					for($i = 0; $i < count($fsft_link['attributes']); $i++) {
						$key = $fsft_link['attributes'][$i]['key'];
						$value = $fsft_link['attributes'][$i]['value'];

						if(stripos($key, 'class') !== false) {
							$classes .= " {$value}";				
						} else if(stripos($key, 'id') !== false) {
							$fsft_id = $value;
						} else if(stripos($key, 'href') !== false) {
							// ignore this key -- we set href via the $fsft_link['url'] field
						} else {
							$attributes .= "{$key}='{$value}' ";				
						}
					}
				}

				if($fsft_lightbox == "yes") {
					$attributes .= "data-donation-lightbox";
				}

				$client_side_triggered_config[$lightbox_id] = [
					'promotion_type' => $engrid_promotion_type,
					'css' => $engrid_css,
					'html' => "<a href='{$fsft_link['url']}' id='{$fsft_id}' style='{$style}' class='{$classes} hover-candle' {$attributes}>{$fsft_link['label']}{$fsft_svg}</a>",
					'trigger' => $fsft_trigger,
					'cookie_name' => $engrid_cookie_name, 
					'cookie_hours' => $engrid_cookie_hours, 
					'id' => $lightbox_id,
				];

				wp_enqueue_script( $this->foursite_wordpress_promotion, plugin_dir_url( __FILE__ ) . 'multistep/dist/donation-lightbox-parent.js', array(), $this->version, false );
				wp_enqueue_script( 'foursite-wordpress-promotion-public', plugin_dir_url( __FILE__ ) . 'js/foursite-wordpress-promotion-public.js', array( 'jquery', $this->foursite_wordpress_promotion ), $this->version, false );
			}
		}
		if(count($client_side_triggered_config)) {
			wp_localize_script($this->foursite_wordpress_promotion, 'client_side_triggered_config', $client_side_triggered_config);
		}
	}
}