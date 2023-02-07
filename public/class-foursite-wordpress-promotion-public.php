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

		wp_enqueue_script( $this->foursite_wordpress_promotion, plugin_dir_url( __FILE__ ) . 'js/donation-lightbox-parent.js', array(), $this->version, false );
		wp_enqueue_script( 'foursite-wordpress-promotion-public', plugin_dir_url( __FILE__ ) . 'js/foursite-wordpress-promotion-public.js', array( 'jquery' ), $this->version, false );

		// populate this with raw html configs & js-triggered multisteps
		$client_side_triggered_config = [];

		foreach($lightbox_ids as $lightbox_id){
			$engrid_donation_page = get_field('engrid_donation_page', $lightbox_id);
			$engrid_promotion_type = get_field('engrid_promotion_type', $lightbox_id);
			$engrid_trigger_type = get_field('engrid_trigger_type', $lightbox_id);
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
			switch(trim($engrid_trigger_type)){
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

			// Only render the plugin if the donation page is set
			if($engrid_promotion_type == "multistep_lightbox" && trim($engrid_trigger_type) != "js" && $engrid_donation_page){
				$engrid_js_code = <<<ENGRID
				console.log('Wordpress Promotion ID: $lightbox_id');

				DonationLightboxOptions = {
					promotion_type: "$engrid_promotion_type",
					url: "$engrid_donation_page",
					image: "$engrid_image",
					logo: "$engrid_logo",
					video: "$engrid_video",
					autoplay: $engrid_video_auto_play,
					logo_position_top: "{$engrid_logo_position['top']}px",
					logo_position_left: "{$engrid_logo_position['left']}px",
					logo_position_right: "{$engrid_logo_position['right']}px",
					logo_position_bottom: "{$engrid_logo_position['bottom']}px",
					divider: "$engrid_divider",
					title: "$engrid_title",
					paragraph: `$engrid_paragraph`,
					footer: `$engrid_footer`,
					bg_color: "$engrid_bg_color",
					txt_color: "$engrid_text_color",
					form_color: "$engrid_form_color",
					cookie_hours: $engrid_cookie_hours,
					cookie_name: "$engrid_cookie_name",
					trigger: "$trigger",
					gtm_open_event_name: "$engrid_gtm_open_event_name",
					gtm_close_event_name: "$engrid_gtm_close_event_name",
					gtm_suppressed_event_name: "$engrid_gtm_suppressed_event_name",
					confetti: $engrid_confetti,
				};
				ENGRID;

				$client_side_triggered_config[$lightbox_id] = "Multistep lightbox";
				
				wp_add_inline_script($this->foursite_wordpress_promotion, $engrid_js_code, 'before');
			} else if($engrid_promotion_type == "raw_code") {
				$client_side_triggered_config[$lightbox_id] = [
					'promotion_type' => $engrid_promotion_type, 
					'html' => $engrid_html, 
					'js' => $engrid_js, 
					'css' => $engrid_css, 
					'cookie' => $engrid_cookie_name, 
					'cookie_hours' => $engrid_cookie_hours, 
					'id' => $lightbox_id, 
					'trigger' => $trigger, 
				];
			} else if (trim($engrid_promotion_type == "pushdown")) {
				$client_side_triggered_config[$lightbox_id] = [
					'promotion_type' => $engrid_promotion_type, 
					'url' => $engrid_pushdown_link,
					'pushdown_type' => $engrid_pushdown_type,
					'pushdown_title' => $engrid_pushdown_title,
					'image' => esc_url($resized_pushdown_image),
					'gif' => $engrid_pushdown_gif,
					'trigger' => $trigger,
					'cookie' => $engrid_cookie_name, 
					'cookie_hours' => $engrid_cookie_hours, 
					'id' => $lightbox_id, 
					'src' => plugins_url('pushdown/js/pushdown.js', __FILE__),
				];
			} else if(trim($engrid_promotion_type == "signup_lightbox")) {
				$signup_trigger = $engrid_trigger_seconds * 1000; // Convert to milliseconds
				wp_enqueue_script( 'foursite-wordpress-signup-lightbox', plugin_dir_url( __FILE__ ) . 'signup/js/website-lightbox.js', array( 'jquery' ), $this->version, false );
				$engrid_js_code = <<<ENGRID
				console.log('Wordpress Promotion ID: $lightbox_id');

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
				$client_side_triggered_config[$lightbox_id] = "Signup lightbox";
				
				wp_add_inline_script('foursite-wordpress-signup-lightbox', $engrid_js_code, 'before');
				} else if(trim($engrid_promotion_type) == "floating_tab") {
					wp_enqueue_style('fs-floating-tab', plugins_url('floating-tab/fs-floating-tab.css', __FILE__));
					
					add_action('wp_footer', function() use ($lightbox_id) {
						$fsft_colors = get_field('engrid_fsft_color', $lightbox_id);
						$fsft_radius = get_field('engrid_fsft_radius', $lightbox_id);
						$fsft_location = get_field('engrid_fsft_location', $lightbox_id);
						$fsft_image = get_field('engrid_fsft_image', $lightbox_id);
						$fsft_link = get_field('engrid_fsft_link', $lightbox_id);
						$fsft_id = 'fs-donation-tab';

						$style = '';
						if(!empty($fsft_colors['foreground'])) $style .= "color: {$fsft_colors['foreground']};";
						if(!empty($fsft_colors['background'])) $style .= "background-color: {$fsft_colors['background']};";
						if(!empty($fsft_radius)) $style .= "border-radius: {$fsft_radius} {$fsft_radius} 0 0;";

						$classes = "{$fsft_location} {$fsft_image}";

						if(!empty($svg_markup)) $svg_markup = " <div class='svg-wrapper'>{$svg_markup}</div>";

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

						$svg_markup = "
							<div class='candle'>
								<div class='candle-flame'>
									<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12.5 24.978'><g data-name='Layer 2'><g data-name='flame b'><path d='M0 15.157c0 6.897 6.25 9.821 6.25 9.821s6.25-2.924 6.25-9.821S6.25 0 6.25 0 0 8.26 0 15.157z' style='fill:#ff4700'></path><path d='M3.375 18.383a6.768 6.768 0 0 0 2.875 5.763 6.768 6.768 0 0 0 2.875-5.763c0-4.047-2.875-8.893-2.875-8.893s-2.875 4.846-2.875 8.893z' style='fill:#ffbf00'></path></g></g></svg>
								</div>
								<div class='candle-base'>
									<span class='spark'></span>
									<span class='spark'></span>
									<span class='spark'></span>
									<span class='spark'></span>
									<span class='spark'></span>
									<span class='spark'></span>
									<span class='spark'></span>
									<span class='spark'></span>
									<span class='spark'></span>
									<span class='spark'></span>
									<span class='spark'></span>
									<span class='spark'></span>
									<span class='spark'></span>
									<span class='spark'></span>
									<span class='spark'></span>
									<svg xmlns='http://www.w3.org/2000/svg' width='30' height='40' fill='none'><path xmlns='http://www.w3.org/2000/svg' transform='translate(-18,-12)' d='M38.843 35.815c-.202-.085-.224-.702-.336-.702-.171 0-.258.561-.588.644-.33.082-.845-.365-.99-.254-.145.11.318.913-.44.913-.226 0-.342-.257-.342-.454v-6.137c.123.012.245.037.364.074.225.059.45.17.45.481 0 .165-.06.503.055.562.114.058.2-.059.253-.087 1.038-.756 1.292.59 1.517.59.226 0 .17-.926.451-.926.225-.086 2.388.954 2.388 1.63 0 .14-.281.167-.306.31-.028.201.506.28.419.648-.03.225-.533.507-.402.644.13.136.819-.137.875-.137.139 0 .165.221.165.306 0 1.239-2.893 2.169-3.541 1.885l.008.01zm-7.713.97c-.169-.276-.084-.757-.251-.757-.085 0-.533.676-.789.676-.255 0-.672-.479-.84-.479-.114 0 .112.676-.114.954-.142.201-1.49.344-1.49.173v-9.561a.604.604 0 01.645-.618h5.03c.616 0 .872.201.872.843v8.58c0 .28-2.956.392-3.069.19l.006-.001zm3.075 12.119c0 .593-.201.73-.702.62l-5.37-1.07a.537.537 0 01-.48-.589V38.14c0-.254.872-.282 1.007-.17.253.226.169 1.041.336 1.041.227 0 .817-.986 1.07-.986.535 0 .897.31 1.006.254.109-.057.115-.507.254-.62.2-.135.786-.135 1.096-.169.507-.028 1.435-.113 1.435-.113.366 0 .338.258.338.791l.01 10.736zM28.412 20.68c0-3.881 5.736-6.102 6.129-8.07.422.402.479 1.04.479 1.61 0 2.36-4.105 6.323-4.105 9.193 0 .31.028.402.028.59 0 .279-.112.337-.167.337-.678 0-2.364-1.322-2.364-3.654v-.006zm-3.177 24.262c-.449 0-.73-.902-.843-.871-.113.03.03.758-.139.87-.169.113-3.12-1.237-3.346-1.458-.225-.221.085-.479 0-.676-.084-.197-.509-.115-.646-.201-.137-.087.137-.62-.028-.729-.165-.108-.45.507-.648.222a.964.964 0 01-.11-.56c0-1.716 1.824-2.414 3.064-2.839.503-.17.332.477.503.505.17.028.225-.308.45-.308.172 0 .817.308.956.202.113-.083-.336-.505-.2-.674.134-.17 1.474-.387 1.462-.05v6.005c0 .63-.483.568-.483.568l.008-.006zM42.885 34.6c.31-.532.31-1.18.422-1.433.165-.28.785-.225.785-.422 0-.083-.448-.202-.62-.308-.334-.201-.22-1.208-.39-1.208-.255 0-.507.7-.763.403-.446-.564-2.303-1.632-3.146-1.942-.31-.113.225-1.04.086-1.127-.139-.086-.507.566-1.042.453-.42-.086-.225-.845-.45-.845-.114 0-.536 1.069-.846.984-.31-.085-.783-.201-.783-.201v-2.821c0-.477-.322-.847-.734-.845h-3.028c-.843 0-.787-.618-.787-.759 0-3.738 5.433-4.356 5.433-9.255 0-2.14-1.348-3.572-2.924-4.84-.086-.054 0 .313 0 .451 0 2.278-8.463 3.46-8.463 8.717 0 4.581 4.556 4.778 4.556 5.482 0 .14-.28.202-.308.202H26.39c-.366 0-.682.225-.678.839v2.817c-.34.112-.787-.403-1.006-.403-.113 0-.055.873-.254.986-.169.083-3.344 1.207-3.621 1.238-.445.082-.332-.705-.586-.705-.253 0-.227.787-.789.787-.338 0-.617-.31-.869-.31-.169 0 .362.648.362.843 0 .535-1.036.73-1.036 1.099 0 .201.98-.085 1.123-.085.505 0 .45.592.644.592.193 0 .338-.678.509-.789.334-.201.73.141.9.141.279 0-.226-.676.027-.789 0 0 3.42-1.458 3.654-1.348.201.115.171.45.403.564.231.112.563-.787.563-.228v6.89c-.402.25-1.24.402-1.69.402-.223 0-.113-.732-.254-.732-.225 0-.45.617-.786.617s-.759-.756-1.065-.756c-.201 0 .362.953.024 1.122-1.295.648-3.346 1.268-3.346 3.316 0 .425.228.934.14 1.069-.081.201-.53.591-.504.76.026.17.843.111 1.006.369.083.136.139.756.31.756.137 0 .503-.477.815-.31.982.592 2.696 1.236 3.487 1.662.201.113.08.73.201.787.227.113.676-.592 1.382-.201a.604.604 0 01.282.537v2.3c0 .42.201.531.423.587l8.382 1.668c.328.077.66.133.996.167.292.012.654 0 .634-.706v-5.231a.201.201 0 01.179-.181c.493 0 .245 1.408.356 1.408.223 0 .364-.956.648-.956.402 0 .65.367.805.282.154-.084-.08-.535-.08-.817 0-.251.727-.364 1.629-.702.787-.278 1.432-.59 1.688-.59.201 0 .332.534.505.534.139 0 .167-.7.477-.7.251 0 .805.39.958.277.11-.08-.282-.618.024-.845.227-.139.76-.473.76-.616 0-.082-1.04-.114-1.096-.114-.201-.055-.42-.672-.566-.672-.145 0-.16.617-.418.814-.226.167-.787-.591-1.036-.591-.145 0 .165.563.165 1.006 0 .443-2.725 1.32-3.09 1.32-.564 0-.593-.704-.705-.704-.17 0-.165.859-.787.859a.403.403 0 01-.42-.403V37.5a.402.402 0 01.42-.47c.463-.023.475 1.023.644 1.023.31 0 .48-1.038 1.07-1.038.45 0 .815.62 1.097.62.201 0-.336-.648-.336-.958 0-.31 2.783-.201 3.851-2.082' fill='currentColor'/></svg>
								</div>
							</div>
						";

						echo "<a href='{$fsft_link['url']}' id='{$fsft_id}' style='{$style}' class='{$classes}' {$attributes}>{$fsft_link['label']}{$svg_markup}</a>";
					});
					$client_side_triggered_config[$lightbox_id] = "Floating Tab";
				}
				else if(trim($engrid_trigger_type) == "js") {
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
			}
		}
		if(count($client_side_triggered_config)) {
			wp_localize_script($this->foursite_wordpress_promotion, 'client_side_triggered_config', $client_side_triggered_config);
		}
	}
}