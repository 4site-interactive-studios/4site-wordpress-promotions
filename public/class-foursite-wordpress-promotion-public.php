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
class Foursite_Wordpress_Promotion_Public
{

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
	public function __construct($foursite_wordpress_promotion, $version)
	{

		$this->foursite_wordpress_promotion = $foursite_wordpress_promotion;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

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

		wp_enqueue_style($this->foursite_wordpress_promotion, plugin_dir_url(__FILE__) . 'css/foursite-wordpress-promotion-public.css', array(), $this->version, 'all');
	}

	/**
	 * Get the lightbox from the database
	 *
	 * @since    1.0.0
	 */

	public function get_lightbox_ids()
	{
		$current_page_url = $_SERVER['REQUEST_URI'];
		$current_page_id = url_to_postid(get_permalink());
		$is_404 = is_404();

		$args = array(
			'numberposts'	=> -1,
			'post_type'		=> 'wordpress_promotion',
			'post_status'   => 'publish',
			'meta_query'	=> array(
				array(
					'key' => 'engrid_lightbox_display',
					'value' => 'turned-off',
					'compare' => '!='
				),
			),
			'orderby' => 'date',
			'order' => 'DESC',
			'suppress_filters' => true
		);

		$lightbox_query = new WP_Query($args);

		// give the scheduled lightboxes priority by prepending it to the list of "on" lightboxes
		$scheduled_lightbox_ids = [];
		$lightbox_ids = [];

		if ($lightbox_query->posts) {
			foreach ($lightbox_query->posts as $lightbox) {
				$lightbox_id = $lightbox->ID;
				$whitelist = get_field('engrid_whitelist', $lightbox_id);
				$blacklist = get_field('engrid_blacklist', $lightbox_id);
				$lightbox_start = get_field('engrid_start_date', $lightbox_id);
				$lightbox_end = get_field('engrid_end_date', $lightbox_id);
				$lightbox_display = get_field("engrid_lightbox_display", $lightbox_id);
				$hide_on = get_field('engrid_hide_on', $lightbox_id);
				$show_on = get_field('engrid_show_on', $lightbox_id);
				$show_on_404 = get_field('show_404', $lightbox_id);

				$skip_checks = false;
				if($is_404) {
					if($show_on_404 == 'Y') {
						$skip_checks = true;
					} else {
						continue;
					}
				}

				if(!$skip_checks) {
					if ($blacklist) {
						// Explode the blacklist into an array
						$blacklist_array = explode(',', strtolower($blacklist));
						$is_blacklisted = false;
						$compare_url = strtolower($current_page_url);
						foreach ($blacklist_array as $blacklist_item) {
							// Trim the whitespace from the blacklist item
							$blacklist_item = trim($blacklist_item);
							// Check if the current page URL contains the blacklist item
							if (strpos($compare_url, $blacklist_item) !== false) {
								// If it does, do not show the lightbox
								$is_blacklisted = true;
								break;
							}
						}
						// If blacklist is not empty and the current page URL does not contain any of the blacklist items, show the lightbox
						if ($is_blacklisted) {
							continue;
						}
					}

					if (is_array($hide_on) && count($hide_on) && in_array($current_page_id, $hide_on)) {
						continue;
					}

					$whitelist_check_enabled = false;
					$eligible = false;
					if ($whitelist) {
						$whitelist_check_enabled = true;
						// Explode the whitelist into an array
						$whitelist_array = explode(',', strtolower($whitelist));
						$compare_url = strtolower($current_page_url);
						$is_whitelisted = false;
						foreach ($whitelist_array as $whitelist_item) {
							// Trim the whitespace from the whitelist item
							$whitelist_item = trim($whitelist_item);
							// Check if the current page URL contains the whitelist item
							if (strpos($compare_url, $whitelist_item) !== false) {
								// If it does, show the lightbox
								$is_whitelisted = true;
							}
						}
						// If whitelist is not empty and the current page URL does not contain any of the whitelist items, do not show the lightbox
						if ($is_whitelisted) {
							$eligible = true;
						}
					}

					if (is_array($show_on)) {
						$whitelist_check_enabled = true;
						if (in_array($current_page_id, $show_on)) {
							$eligible = true;
						}
					}

					if ($whitelist_check_enabled && !$eligible) {
						continue;
					}
				}

				$is_scheduled  = false;
				if ($lightbox_display == "scheduled" && $lightbox_start && $lightbox_end) {
					$is_scheduled = true;
				}

				// If we made it this far, we're okay to show
				if ($is_scheduled) {
					$scheduled_lightbox_ids[$lightbox->post_date] = $lightbox_id;
				} else {
					$lightbox_ids[$lightbox->post_date] = $lightbox_id;
				}
			}
		}

		// the WP_Query above doesn't perfectly sort the dates when it comes to the times.
		rsort($lightbox_ids);
		rsort($scheduled_lightbox_ids);
		$lightbox_ids = array_merge(array_values($scheduled_lightbox_ids), array_values($lightbox_ids));
		return $lightbox_ids;
	}

	/**
	 * Remove promotions that are used as candidates by an AB Test promotion in the list.
	 *
	 * When an AB Test promotion is eligible for the current page, its candidate promotions
	 * (the "promotion" and "ad_blocker_promotion" sub-fields of each ab_promotions row) are
	 * displayed via the AB Test itself. If such a candidate is also independently eligible for
	 * the current page it would otherwise be displayed twice, so we strip it from the list here.
	 *
	 * @param array $lightbox_ids
	 * @return array
	 */
	private function remove_ab_test_candidates($lightbox_ids)
	{
		$candidate_ids = [];
		foreach ($lightbox_ids as $lightbox_id) {
			if (trim(get_field('engrid_promotion_type', $lightbox_id)) !== 'ab_test') {
				continue;
			}
			if (have_rows('ab_promotions', $lightbox_id)) {
				while (have_rows('ab_promotions', $lightbox_id)) {
					the_row();
					$promo_id = get_sub_field('promotion');
					$ad_blocker_id = get_sub_field('ad_blocker_promotion');
					if ($promo_id) {
						$candidate_ids[] = (int) $promo_id;
					}
					if ($ad_blocker_id) {
						$candidate_ids[] = (int) $ad_blocker_id;
					}
				}
			}
		}

		if (count($candidate_ids) == 0) {
			return $lightbox_ids;
		}

		return array_values(array_filter($lightbox_ids, function ($lightbox_id) use ($candidate_ids) {
			return !in_array((int) $lightbox_id, $candidate_ids, true);
		}));
	}



	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		$lightbox_ids = $this->get_lightbox_ids();
		if (count($lightbox_ids) == 0) return;

		// remove any promotions that are candidates of an AB Test promotion in the list,
		// so a candidate isn't also displayed on its own when its AB Test parent is eligible
		$lightbox_ids = $this->remove_ab_test_candidates($lightbox_ids);
		if (count($lightbox_ids) == 0) return;

		// populate this with raw html configs & js-triggered multisteps
		$client_side_triggered_config = [];

		$multistep_script_url = get_field('promotion_lightbox_script', 'options');

		$script_ver = $this->version;
		$main_script_url = plugin_dir_url(__FILE__) . 'js/foursite-wordpress-promotion-public.js';

		foreach ($lightbox_ids as $lightbox_id) {
			$config = $this->prepare_config_for_promo($lightbox_id);
			if ($config !== null) {
				$client_side_triggered_config[] = $config;
			}
		}

		if (count($client_side_triggered_config)) {
			// move the floating_signup promo beyond any lightbox promos
			$client_side_triggered_config = $this->move_floating_signup_beyond_lightbox_promos($client_side_triggered_config);

			// move the floating_tab promo above any lightbox promos
			$client_side_triggered_config = $this->move_first_floating_tab_to_top($client_side_triggered_config);

			if ($multistep_script_url) {
				wp_enqueue_script('multistep-lightbox', $multistep_script_url, array(), $script_ver, false);
				wp_enqueue_script('foursite-wordpress-promotion-public', $main_script_url, array('multistep-lightbox'), $script_ver, false);
			} else {
				wp_enqueue_script('foursite-wordpress-promotion-public', $main_script_url, array(), $script_ver, false);
			}
			wp_localize_script('foursite-wordpress-promotion-public', 'client_side_triggered_config', $client_side_triggered_config);
		}
	}

	private function prepare_config_for_promo($lightbox_id)
	{
		$promotion_type = trim(get_field('engrid_promotion_type', $lightbox_id));

		switch ($promotion_type) {
			case 'multistep_lightbox':
				return $this->prepare_multistep_lightbox_config($lightbox_id);
			case 'floating_signup':
				return $this->prepare_floating_signup_config($lightbox_id);
			case 'raw_code':
				return $this->prepare_raw_code_config($lightbox_id);
			case 'overlay':
				return $this->prepare_overlay_config($lightbox_id);
			case 'pushdown':
				return $this->prepare_pushdown_config($lightbox_id);
			case 'signup_lightbox':
				return $this->prepare_signup_lightbox_config($lightbox_id);
			case 'floating_tab':
				return $this->prepare_floating_tab_config($lightbox_id);
			case 'rollup':
				return $this->prepare_rollup_config($lightbox_id);
			case 'cta_lightbox':
				return $this->prepare_cta_lightbox_config($lightbox_id);
			case 'email_capture_lightbox':
				return $this->prepare_email_capture_lightbox_config($lightbox_id);
			case 'video':
				return $this->prepare_video_config($lightbox_id);
			case 'redirect':
				return $this->prepare_redirect_config($lightbox_id);
			case 'ab_test':
				return $this->prepare_ab_test_config($lightbox_id);
			default:
				return null;
		}
	}

	private function compute_trigger_from_lightbox($lightbox_id)
	{
		$engrid_trigger_type = get_field('engrid_trigger_type', $lightbox_id);
		if ($engrid_trigger_type) $engrid_trigger_type = trim($engrid_trigger_type);

		switch ($engrid_trigger_type) {
			case 'seconds':
				return get_field('engrid_trigger_seconds', $lightbox_id);
			case 'px':
				return get_field('engrid_trigger_scroll_pixels', $lightbox_id) . 'px';
			case '%':
				return get_field('engrid_trigger_scroll_percentage', $lightbox_id) . '%';
			case 'exit':
				return 'exit';
			case 'js':
				return 'js';
			default:
				return 0;
		}
	}

	private function get_donation_page_url($lightbox_id)
	{
		$engrid_donation_page = get_field('engrid_donation_page', $lightbox_id);
		$engrid_dp_append_chain = get_field('engrid_dp_append_chain', $lightbox_id);
		if ($engrid_dp_append_chain) {
			if (strpos($engrid_donation_page, '?') === false) {
				$engrid_donation_page .= '?chain';
			} else {
				$engrid_donation_page .= '&chain';
			}
		}
		return $engrid_donation_page;
	}

	private function prepare_multistep_lightbox_config($lightbox_id)
	{
		$engrid_hero_type = get_field('engrid_hero_type', $lightbox_id);
		$engrid_use_logo = get_field('engrid_use_logo', $lightbox_id);
		$engrid_logo = $engrid_use_logo ? get_field('engrid_logo', $lightbox_id) : '';
		$engrid_logo_position = get_field('engrid_logo_position', $lightbox_id);
		$logo_position_options = isset($engrid_logo_position['position_options']) ? $engrid_logo_position['position_options'] : [];
		if (!is_array($logo_position_options)) {
			$logo_position_options = [];
		}

		$engrid_js = get_field('engrid_javascript', $lightbox_id);
		if ($engrid_js) {
			$engrid_js = $this->make_js_replacements($engrid_js);
		}

		$confetti = array();
		if (have_rows('engrid_confetti', $lightbox_id)) {
			while (have_rows('engrid_confetti', $lightbox_id)) {
				the_row();
				$confetti[] = get_sub_field('color');
			}
		}

		return [
			'id' => $lightbox_id,
			'promotion_type' => 'multistep_lightbox',
			'url' => $this->get_donation_page_url($lightbox_id),
			'image' => get_field('engrid_image', $lightbox_id),
			'logo' => $engrid_logo,
			'video' => ($engrid_hero_type == 'autoplay-video' || $engrid_hero_type == 'click-to-play-video') ? get_field('engrid_video', $lightbox_id) : "",
			'autoplay' => ($engrid_hero_type == 'autoplay-video'),
			'logo_position_top' => in_array('top', $logo_position_options) ? "{$engrid_logo_position['top']}px" : "unset",
			'logo_position_left' => in_array('left', $logo_position_options) ? "{$engrid_logo_position['left']}px" : "unset",
			'logo_position_right' => in_array('right', $logo_position_options) ? "{$engrid_logo_position['right']}px" : "unset",
			'logo_position_bottom' => in_array('bottom', $logo_position_options) ? "{$engrid_logo_position['bottom']}px" : "unset",
			'divider' => get_field('engrid_divider', $lightbox_id),
			'view_more' => get_field('engrid_show_view_more', $lightbox_id),
			'title' => get_field('engrid_title', $lightbox_id),
			'paragraph' => get_field('engrid_paragraph', $lightbox_id),
			'footer' => get_field('engrid_footer', $lightbox_id),
			'bg_color' => get_field('engrid_bg_color', $lightbox_id),
			'txt_color' => get_field('engrid_text_color', $lightbox_id),
			'form_color' => get_field('engrid_form_color', $lightbox_id),
			'custom_css' => get_field('engrid_css', $lightbox_id),
			'custom_js' => $engrid_js,
			'cookie_hours' => (int) get_field('engrid_cookie_hours', $lightbox_id),
			'cookie_name' => get_field('engrid_cookie_name', $lightbox_id),
			'trigger' => $this->compute_trigger_from_lightbox($lightbox_id),
			'gtm_open_event_name' => get_field('engrid_gtm_open_event_name', $lightbox_id),
			'gtm_close_event_name' => get_field('engrid_gtm_close_event_name', $lightbox_id),
			'gtm_suppressed_event_name' => get_field('engrid_gtm_suppressed_event_name', $lightbox_id),
			'confetti' => json_encode($confetti),
			'display' => get_field('engrid_lightbox_display', $lightbox_id),
			'start' => get_field('engrid_start_date', $lightbox_id),
			'end' => get_field('engrid_end_date', $lightbox_id),
			'raw_html' => get_field('engrid_raw_html', $lightbox_id),
			'page_host' => get_field('page_host', $lightbox_id),
			'promo_style' => get_field('ea_promo_style', $lightbox_id),
			'media_credit' => get_field('media_credit', $lightbox_id),
			'text_position' => 'top', // used by the EA multistep script
			'view_more_text' => 'Read More' // used by the EA multistep script
		];
	}

	private function prepare_floating_signup_config($lightbox_id)
	{
		$fsft_colors = get_field('engrid_fsft_color', $lightbox_id);
		$button_colors = get_field('fes_button_colors', $lightbox_id);
		$post_submission_button = get_field('fes_post_submission_button', $lightbox_id);
		if (!$post_submission_button) {
			$post_submission_button = ['title' => '', 'url' => '', 'target' => ''];
		}
		$gravity_form_id = get_field('fes_gravity_form_id', $lightbox_id);
		$gravity_form_email_field_id = get_field('fes_gravity_form_email_field_id', $lightbox_id);
		$fes_nonce = wp_create_nonce('fes_nonce');

		$recaptcha_site_key = get_field('promotion_lightbox_recaptcha_site_key', 'options');
		if ($recaptcha_site_key) {
			wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . $recaptcha_site_key);
		}

		return [
			'id' => $lightbox_id,
			'promotion_type' => 'floating_signup',
			'fg_color' => $fsft_colors['foreground'],
			'bg_color' => $fsft_colors['background'],
			'button_fg_color' => $button_colors['foreground'],
			'button_bg_color' => $button_colors['background'],
			'title' => get_field('engrid_title', $lightbox_id),
			'paragraph' => get_field('engrid_paragraph', $lightbox_id),
			'custom_css' => get_field('engrid_css', $lightbox_id),
			'gtm_open_event_name' => get_field('engrid_gtm_open_event_name', $lightbox_id),
			'gtm_close_event_name' => get_field('engrid_gtm_close_event_name', $lightbox_id),
			'gtm_suppressed_event_name' => get_field('engrid_gtm_suppressed_event_name', $lightbox_id),
			'cookie_hours' => (int) get_field('engrid_cookie_hours', $lightbox_id),
			'close_cookie_hours' => get_field('fes_closed_cookie_hours', $lightbox_id),
			'cookie_name' => get_field('engrid_cookie_name', $lightbox_id),
			'display' => get_field('engrid_lightbox_display', $lightbox_id),
			'start' => get_field('engrid_start_date', $lightbox_id),
			'end' => get_field('engrid_end_date', $lightbox_id),
			'trigger' => $this->compute_trigger_from_lightbox($lightbox_id),
			'post_submission_title' => get_field('fes_post_submission_title', $lightbox_id),
			'post_submission_paragraph' => get_field('fes_post_submission_paragraph', $lightbox_id),
			'post_submission_button' => $post_submission_button,
			'recaptcha' => $recaptcha_site_key,
			'submit_url' => ($gravity_form_id && $gravity_form_email_field_id) ? admin_url('admin-ajax.php?action=fes_submit&nonce=' . $fes_nonce . '&promo_id=' . $lightbox_id) : ''
		];
	}

	private function prepare_raw_code_config($lightbox_id)
	{
		$engrid_js = get_field('engrid_javascript', $lightbox_id);
		if ($engrid_js) {
			$engrid_js = $this->make_js_replacements($engrid_js);
		}

		return [
			'id' => $lightbox_id,
			'promotion_type' => 'raw_code',
			'html' => get_field('engrid_html', $lightbox_id),
			'js' => $engrid_js,
			'css' => get_field('engrid_css', $lightbox_id),
			'cookie_name' => get_field('engrid_cookie_name', $lightbox_id),
			'cookie_hours' => (int) get_field('engrid_cookie_hours', $lightbox_id),
			'trigger' => $this->compute_trigger_from_lightbox($lightbox_id),
			'is_lightbox' => boolval(get_field('is_lightbox', $lightbox_id)),
			'display' => get_field('engrid_lightbox_display', $lightbox_id),
			'start' => get_field('engrid_start_date', $lightbox_id),
			'end' => get_field('engrid_end_date', $lightbox_id)
		];
	}

	private function prepare_overlay_config($lightbox_id)
	{
		$styles = get_field('modal_overlay', $lightbox_id);
		$formatted_styles = [];
		if (!empty($styles['screen_overlay_background_color'])) {
			$formatted_styles['--bg-overlay-color'] = $styles['screen_overlay_background_color'];
		}

		if (!empty($styles['background_image_overlay']['gradient_start_color'])) {
			$formatted_styles['--bg-img-overlay-start-color'] = $styles['background_image_overlay']['gradient_start_color'];
		}
		if (!empty($styles['background_image_overlay']['gradient_end_color'])) {
			$formatted_styles['--bg-img-overlay-end-color'] = $styles['background_image_overlay']['gradient_end_color'];
		}

		if (!empty($styles['title']['color'])) {
			$formatted_styles['--title-color'] = $styles['title']['color'];
		}
		if (!empty($styles['title']['font'])) {
			$formatted_styles['--title-font'] = $styles['title']['font'];
		}
		if (!empty($styles['subtitle']['color'])) {
			$formatted_styles['--subtitle-color'] = $styles['subtitle']['color'];
		}
		if (!empty($styles['subtitle']['font'])) {
			$formatted_styles['--subtitle-font'] = $styles['subtitle']['font'];
		}
		if (!empty($styles['paragraph']['color'])) {
			$formatted_styles['--paragraph-color'] = $styles['paragraph']['color'];
		}
		if (!empty($styles['paragraph']['font'])) {
			$formatted_styles['--paragraph-font'] = $styles['paragraph']['font'];
		}
		if (!empty($styles['divider_line'])) {
			$formatted_styles['--divider-border'] = $styles['divider_line'];
		}

		if (!empty($styles['amount_button']['background_color'])) {
			$formatted_styles['--amount-button-bg-color'] = $styles['amount_button']['background_color'];
		}
		if (!empty($styles['amount_button']['text_color'])) {
			$formatted_styles['--amount-button-color'] = $styles['amount_button']['text_color'];
		}
		if (!empty($styles['amount_button']['border'])) {
			$formatted_styles['--amount-button-border'] = $styles['amount_button']['border'];
		}
		if (!empty($styles['amount_button']['border_radius'])) {
			$formatted_styles['--amount-button-border-radius'] = $styles['amount_button']['border_radius'];
		}

		if (!empty($styles['amount_button_hover']['background_color'])) {
			$formatted_styles['--amount-button-hover-bg-color'] = $styles['amount_button_hover']['background_color'];
		}
		if (!empty($styles['amount_button_hover']['text_color'])) {
			$formatted_styles['--amount-button-hover-color'] = $styles['amount_button_hover']['text_color'];
		}
		if (!empty($styles['amount_button_hover']['border'])) {
			$formatted_styles['--amount-button-hover-border'] = $styles['amount_button_hover']['border'];
		}
		if (!empty($styles['amount_button_hover']['border_radius'])) {
			$formatted_styles['--amount-button-hover-border-radius'] = $styles['amount_button_hover']['border_radius'];
		}

		if (!empty($styles['amount_button_selected']['background_color'])) {
			$formatted_styles['--amount-button-selected-bg-color'] = $styles['amount_button_selected']['background_color'];
		}
		if (!empty($styles['amount_button_selected']['text_color'])) {
			$formatted_styles['--amount-button-selected-color'] = $styles['amount_button_selected']['text_color'];
		}
		if (!empty($styles['amount_button_selected']['border'])) {
			$formatted_styles['--amount-button-selected-border'] = $styles['amount_button_selected']['border'];
		}
		if (!empty($styles['amount_button_selected']['border_radius'])) {
			$formatted_styles['--amount-button-selected-border-radius'] = $styles['amount_button_selected']['border_radius'];
		}

		if (!empty($styles['submit_button']['background_color'])) {
			$formatted_styles['--submit-button-bg-color'] = $styles['submit_button']['background_color'];
		}
		if (!empty($styles['submit_button']['text_color'])) {
			$formatted_styles['--submit-button-color'] = $styles['submit_button']['text_color'];
		}
		if (!empty($styles['submit_button']['border'])) {
			$formatted_styles['--submit-button-border'] = $styles['submit_button']['border'];
		}
		if (!empty($styles['submit_button']['border_radius'])) {
			$formatted_styles['--submit-button-border-radius'] = $styles['submit_button']['border_radius'];
		}

		if (!empty($styles['submit_button_hover']['background_color'])) {
			$formatted_styles['--submit-button-hover-bg-color'] = $styles['submit_button_hover']['background_color'];
		}
		if (!empty($styles['submit_button_hover']['text_color'])) {
			$formatted_styles['--submit-button-hover-color'] = $styles['submit_button_hover']['text_color'];
		}
		if (!empty($styles['submit_button_hover']['border'])) {
			$formatted_styles['--submit-button-hover-border'] = $styles['submit_button_hover']['border'];
		}
		if (!empty($styles['submit_button_hover']['border_radius'])) {
			$formatted_styles['--submit-button-hover-border-radius'] = $styles['submit_button_hover']['border_radius'];
		}

		$formated_styles_string = '';
		foreach ($formatted_styles as $key => $value) {
			$formated_styles_string .= "{$key}: {$value};";
		}

		$engrid_css = get_field('engrid_css', $lightbox_id);
		$image_url = (!empty($styles['background_image']['sizes']['large'])) ? $styles['background_image']['sizes']['large'] : '';
		$cta_type = get_field('cta_type', $lightbox_id);
		$submit_label = get_field('submit_button_label', $lightbox_id);

		return [
			'id' => $lightbox_id,
			'promotion_type' => 'overlay',
			'title' => get_field('engrid_title', $lightbox_id),
			'subtitle' => get_field('engrid_subtitle', $lightbox_id),
			'paragraph' => get_field('engrid_paragraph', $lightbox_id),
			'cookie_expiry' => (int) get_field('engrid_cookie_hours', $lightbox_id),
			'cookie_name' => get_field('engrid_cookie_name', $lightbox_id),
			'logo' => get_field('engrid_logo', $lightbox_id),
			'button_label' => ($submit_label) ? $submit_label : 'Donate Now',
			'image' => $image_url,
			'other_label' => ($cta_type == 'fundraising') ? get_field('other_amount_label', $lightbox_id) : '',
			'donation_form' => $this->get_donation_page_url($lightbox_id),
			'trigger' => $this->compute_trigger_from_lightbox($lightbox_id),
			'max_width' => $styles['modal_dimensions']['max_width'],
			'max_height' => $styles['modal_dimensions']['max_height'],
			'cta_type' => $cta_type,
			'amounts' => ($cta_type == 'fundraising') ? str_replace(' ', '', get_field('amount_options', $lightbox_id)) : '',
			'custom_css' => ".foursite-en-overlay { {$formated_styles_string} } {$engrid_css}",
			'js_url' => plugin_dir_url(__FILE__) . 'overlay/dist/foursite-en-overlay.js',
			'display' => get_field('engrid_lightbox_display', $lightbox_id),
			'start' => get_field('engrid_start_date', $lightbox_id),
			'end' => get_field('engrid_end_date', $lightbox_id)
		];
	}

	private function prepare_pushdown_config($lightbox_id)
	{
		$engrid_pushdown_image = get_field('engrid_pushdown_image', $lightbox_id);
		$engrid_pushdown_gif = get_field('engrid_pushdown_gif', $lightbox_id) ? get_field('engrid_pushdown_gif', $lightbox_id) : "";

		$resized_pushdown_image = '';
		if (isset($engrid_pushdown_image["sizes"]["2048x2048"])) {
			$resized_pushdown_image = $engrid_pushdown_image["sizes"]["2048x2048"];
		} else if (isset($engrid_pushdown_image["url"])) {
			$resized_pushdown_image = $engrid_pushdown_image["url"];
		}

		return [
			'id' => $lightbox_id,
			'promotion_type' => 'pushdown',
			'url' => get_field('engrid_pushdown_link', $lightbox_id),
			'pushdown_type' => get_field('engrid_pushdown_type', $lightbox_id),
			'pushdown_title' => get_field('engrid_pushdown_title', $lightbox_id),
			'pushdown_paragraph' => get_field('pushdown_paragraph', $lightbox_id),
			'pushdown_button' => get_field('pushdown_button_label', $lightbox_id),
			'image' => esc_url($resized_pushdown_image),
			'gif' => $engrid_pushdown_gif,
			'trigger' => $this->compute_trigger_from_lightbox($lightbox_id),
			'cookie_name' => get_field('engrid_cookie_name', $lightbox_id),
			'cookie_hours' => (int) get_field('engrid_cookie_hours', $lightbox_id),
			'src' => plugins_url('pushdown/js/pushdown.js', __FILE__),
			'bg_color' => get_field('engrid_bg_color', $lightbox_id),
			'fg_color' => get_field('engrid_text_color', $lightbox_id),
			'image_id' => isset($engrid_pushdown_image['ID']) ? $engrid_pushdown_image['ID'] : 0,
			'display' => get_field('engrid_lightbox_display', $lightbox_id),
			'start' => get_field('engrid_start_date', $lightbox_id),
			'end' => get_field('engrid_end_date', $lightbox_id),
			'custom_css' => get_field('engrid_css', $lightbox_id)
		];
	}

	private function prepare_signup_lightbox_config($lightbox_id)
	{
		$engrid_css = get_field('engrid_css', $lightbox_id);
		$layout = get_field('layout', $lightbox_id);
		if ($layout == 'one-col') {
			$max_width = get_field('max_width', $lightbox_id);
			if ($max_width) {
				$engrid_css = "
						.fs-signup-container,
						.fs-signup-lightbox .fs-signup-lightbox-content,
						.fs-signup-container-form {
							max-width: {$max_width};
						}
						" . $engrid_css;
			}
		}

		wp_enqueue_script('foursite-wordpress-signup-lightbox', plugin_dir_url(__FILE__) . 'signup/js/website-lightbox.js', array(), $this->version, false);

		return [
			'id' => $lightbox_id,
			'promotion_type' => 'signup_lightbox',
			'css' => $engrid_css,
			'cookie_hours' => (int) get_field('engrid_cookie_hours', $lightbox_id),
			'cookie_name' => get_field('engrid_cookie_name', $lightbox_id),
			'gtm_open_event_name' => get_field('engrid_gtm_open_event_name', $lightbox_id),
			'gtm_close_event_name' => get_field('engrid_gtm_close_event_name', $lightbox_id),
			'gtm_suppressed_event_name' => get_field('engrid_gtm_suppressed_event_name', $lightbox_id),
			'trigger' => $this->compute_trigger_from_lightbox($lightbox_id),
			'start' => get_field('engrid_start_date', $lightbox_id),
			'end' => get_field('engrid_end_date', $lightbox_id),
			'display' => get_field('engrid_lightbox_display', $lightbox_id),
			'url' => $this->get_donation_page_url($lightbox_id),
			'imageURL' => get_field('engrid_image', $lightbox_id),
			'logoURL' => get_field('engrid_logo', $lightbox_id),
			'title' => get_field('engrid_title', $lightbox_id),
			'paragraph' => get_field('engrid_paragraph', $lightbox_id),
			'footer' => get_field('engrid_footer', $lightbox_id),
			'layout' => $layout
		];
	}

	private function prepare_floating_tab_config($lightbox_id)
	{
		wp_enqueue_style('fs-floating-tab', plugins_url('floating-tab/fs-floating-tab.css', __FILE__), [], '1.0');

		$fsft_colors = get_field('engrid_fsft_color', $lightbox_id);
		$fsft_radius = get_field('engrid_fsft_radius', $lightbox_id);
		$fsft_location = get_field('engrid_fsft_location', $lightbox_id);
		$fsft_link = get_field('engrid_fsft_link', $lightbox_id);
		$fsft_trigger = get_field('engrid_fsft_trigger_type', $lightbox_id);
		$engrid_js = get_field('engrid_js', $lightbox_id);
		if ($engrid_js) {
			$engrid_js = $this->make_js_replacements($engrid_js);
		}
		$engrid_trigger_scroll_pixels = get_field('engrid_trigger_scroll_pixels', $lightbox_id);
		$engrid_trigger_scroll_percentage = get_field('engrid_trigger_scroll_percentage', $lightbox_id);
		$fsft_svg = get_field('engrid_custom_svg', $lightbox_id);
		$fsft_id = 'fs-donation-tab';

		$trigger = 0;
		switch ($fsft_trigger) {
			case "0":
				$trigger = 0;
				break;
			case 'px':
				$trigger = $engrid_trigger_scroll_pixels . 'px';
				break;
			case '%':
				$trigger = $engrid_trigger_scroll_percentage . '%';
				break;
			case 'js':
				$trigger = 'js';
				break;
		}

		$style = '';
		if (!empty($fsft_colors['foreground'])) $style .= "color: {$fsft_colors['foreground']};";
		if (!empty($fsft_colors['background'])) $style .= "background-color: {$fsft_colors['background']};";
		if (!empty($fsft_radius)) $style .= "border-radius: {$fsft_radius} {$fsft_radius} 0 0;";

		$classes = "{$fsft_location}";

		$attributes = '';

		if (is_array($fsft_link['attributes'])) {
			for ($i = 0; $i < count($fsft_link['attributes']); $i++) {
				$key = $fsft_link['attributes'][$i]['key'];
				$value = $fsft_link['attributes'][$i]['value'];

				if (stripos($key, 'class') !== false) {
					$classes .= " {$value}";
				} else if (stripos($key, 'id') !== false) {
					$fsft_id = $value;
				} else if (stripos($key, 'href') !== false) {
					// ignore this key -- we set href via the $fsft_link['url'] field
				} else {
					$value = str_replace('"', '&quot;', $value);
					$value = str_replace("'", '&apos;', $value);
					$attributes .= "{$key}=\"{$value}\" ";
				}
			}
		}

		if ($fsft_link['engrid_use_lightbox'] == 'yes') {
			$attributes .= "data-donation-lightbox";
		}

		return [
			'id' => $lightbox_id,
			'promotion_type' => 'floating_tab',
			'css' => get_field('engrid_css', $lightbox_id),
			'js' => $engrid_js,
			'html' => "<a href='{$fsft_link['url']}' id='{$fsft_id}' style='{$style}' class='{$classes} hover-candle' {$attributes}>{$fsft_link['label']}{$fsft_svg}</a>",
			'trigger' => $trigger,
			'cookie_name' => get_field('engrid_cookie_name', $lightbox_id),
			'cookie_hours' => (int) get_field('engrid_cookie_hours', $lightbox_id),
			'open_lightbox' => ($fsft_link['engrid_use_lightbox'] == 'yes') ? true : false,
			'display' => get_field('engrid_lightbox_display', $lightbox_id),
			'start' => get_field('engrid_start_date', $lightbox_id),
			'end' => get_field('engrid_end_date', $lightbox_id)
		];
	}

	private function prepare_rollup_config($lightbox_id)
	{
		$rollup_settings = get_field('rollup', $lightbox_id);
		$engrid_js = get_field('engrid_javascript', $lightbox_id);
		if ($engrid_js) {
			$engrid_js = $this->make_js_replacements($engrid_js);
		}

		if ($rollup_settings['enqueue_jquery']) {
			wp_enqueue_script('jquery-cdn', 'https://code.jquery.com/jquery-3.7.1.min.js', [], '3.7.1');
		}

		return [
			'id' => $lightbox_id,
			'promotion_type' => 'rollup',
			'image' => !empty($rollup_settings['image']['sizes']['2048x2048']) ? $rollup_settings['image']['sizes']['2048x2048'] : '',
			'link' => $rollup_settings['link'],
			'target' => ($rollup_settings['target']) ? '_blank' : '',
			'hide_under' => $rollup_settings['hide_under'],
			'close_if_oustide_click' => $rollup_settings['close_if_outside_click'],
			'close_if_inside_click' => $rollup_settings['close_if_inside_click'],
			'close_cookie_hours' => $rollup_settings['cookie_hours'],
			'html' => get_field('engrid_html', $lightbox_id),
			'js' => $engrid_js,
			'css' => get_field('engrid_css', $lightbox_id),
			'cookie_name' => get_field('engrid_cookie_name', $lightbox_id),
			'cookie_hours' => (int) get_field('engrid_cookie_hours', $lightbox_id),
			'trigger' => $this->compute_trigger_from_lightbox($lightbox_id),
			'display' => get_field('engrid_lightbox_display', $lightbox_id),
			'start' => get_field('engrid_start_date', $lightbox_id),
			'end' => get_field('engrid_end_date', $lightbox_id)
		];
	}

	private function prepare_cta_lightbox_config($lightbox_id)
	{
		$config = get_field('cta_lightbox', $lightbox_id);

		return [
			'id' => $lightbox_id,
			'promotion_type' => 'cta_lightbox',
			'trigger' => $this->compute_trigger_from_lightbox($lightbox_id),
			'cookie_name' => get_field('engrid_cookie_name', $lightbox_id),
			'cookie_hours' => (int) get_field('engrid_cookie_hours', $lightbox_id),
			'display' => get_field('engrid_lightbox_display', $lightbox_id),
			'start' => get_field('engrid_start_date', $lightbox_id),
			'end' => get_field('engrid_end_date', $lightbox_id),
			'header' => $config['copy_header'],
			'body' => $config['copy_body'],
			'bg_color' => $config['copy_bg_color'],
			'fg_color' => $config['copy_fg_color'],
			'cta_1' => [
				'label' => $config['cta_1_label'],
				'link' => $config['cta_1_link'],
				'bg_color' => $config['cta_1_bg_color'],
				'fg_color' => $config['cta_1_fg_color']
			],
			'cta_2' => [
				'label' => $config['cta_2_label'],
				'link' => $config['cta_2_link'],
				'bg_color' => $config['cta_2_bg_color'],
				'fg_color' => $config['cta_2_fg_color']
			],
			'image' => [
				'url' => isset($config['image_file']['sizes']['large']) ? $config['image_file']['sizes']['large'] : '',
				'alt' => isset($config['image_file']['alt']) ? $config['image_file']['alt'] : '',
				'position' => $config['image_position'],
				'bg_color' => $config['image_bg_color']
			],
			'css' => $config['custom_css']
		];
	}

	private function prepare_email_capture_lightbox_config($lightbox_id)
	{
		$config = get_field('email_capture_lightbox', $lightbox_id);

		$success_button = $config['success_button'];
		if (!$success_button) {
			$success_button = ['title' => '', 'url' => '', 'target' => ''];
		}

		// Submission is "ready" once the proxy endpoint is configured. The proxy holds the EN API
		// token on a whitelisted host, which is required when the token is IP-restricted / this host
		// has no fixed outbound IP (e.g. Pantheon).
		$en_ready = trim((string) get_field('promotion_en_proxy_url', 'options')) !== '';
		$eclb_nonce = wp_create_nonce('eclb_nonce');

		return [
			'id' => $lightbox_id,
			'promotion_type' => 'email_capture_lightbox',
			'trigger' => $this->compute_trigger_from_lightbox($lightbox_id),
			'cookie_name' => get_field('engrid_cookie_name', $lightbox_id),
			'cookie_hours' => (int) get_field('engrid_cookie_hours', $lightbox_id),
			'display' => get_field('engrid_lightbox_display', $lightbox_id),
			'start' => get_field('engrid_start_date', $lightbox_id),
			'end' => get_field('engrid_end_date', $lightbox_id),
			'header' => $config['copy_header'],
			'body' => $config['copy_body'],
			'bg_color' => $config['copy_bg_color'],
			'fg_color' => $config['copy_fg_color'],
			'email_placeholder' => $config['email_placeholder'] ? $config['email_placeholder'] : 'Email Address',
			'submit' => [
				'label' => $config['cta_1_label'],
				'bg_color' => $config['cta_1_bg_color'],
				'fg_color' => $config['cta_1_fg_color']
			],
			'submit_url' => $en_ready ? admin_url('admin-ajax.php?action=eclb_submit&nonce=' . $eclb_nonce . '&promo_id=' . $lightbox_id) : '',
			'success' => [
				'header' => $config['success_header'],
				'body' => $config['success_body'],
				'button' => [
					'title' => $success_button['title'],
					'url' => $success_button['url'],
					'target' => $success_button['target']
				]
			],
			'image' => [
				'url' => isset($config['image_file']['sizes']['large']) ? $config['image_file']['sizes']['large'] : '',
				'alt' => isset($config['image_file']['alt']) ? $config['image_file']['alt'] : '',
				'position' => $config['image_position'],
				'bg_color' => $config['image_bg_color']
			],
			'css' => $config['custom_css']
		];
	}

	private function prepare_video_config($lightbox_id)
	{
		$video_settings = get_field('video', $lightbox_id);

		return [
			'id' => $lightbox_id,
			'promotion_type' => 'video',
			'trigger' => $this->compute_trigger_from_lightbox($lightbox_id),
			'cookie_name' => get_field('engrid_cookie_name', $lightbox_id),
			'cookie_hours' => (int) get_field('engrid_cookie_hours', $lightbox_id),
			'video_url' => $video_settings['url'],
			'thumbnail' => isset($video_settings['thumbnail']['sizes']['large']) ? $video_settings['thumbnail']['sizes']['large'] : '',
			'options' => $video_settings['options'],
			'background_color' => $video_settings['background'],
			'foreground_color' => $video_settings['foreground'],
			'button' => $video_settings['button'],
			'button_background_color' => $video_settings['button_background'],
			'button_foreground_color' => $video_settings['button_foreground'],
			'css' => get_field('engrid_css', $lightbox_id)
		];
	}

	private function prepare_redirect_config($lightbox_id)
	{
		return [
			'id' => $lightbox_id,
			'promotion_type' => 'redirect',
			'url' => get_field('redirect', $lightbox_id),
			'trigger' => $this->compute_trigger_from_lightbox($lightbox_id),
			'cookie_name' => get_field('engrid_cookie_name', $lightbox_id),
			'cookie_hours' => (int) get_field('engrid_cookie_hours', $lightbox_id),
			'display' => get_field('engrid_lightbox_display', $lightbox_id),
			'start' => get_field('engrid_start_date', $lightbox_id),
			'end' => get_field('engrid_end_date', $lightbox_id)
		];
	}

	private function prepare_ab_test_config($lightbox_id)
	{
		$parent_cookie_name = get_field('engrid_cookie_name', $lightbox_id);
		$variants = [];
		if (have_rows('ab_promotions', $lightbox_id)) {
			$variant_index = 0;
			while (have_rows('ab_promotions', $lightbox_id)) {
				the_row();
				$promo_id = get_sub_field('promotion');
				$ad_blocker_id = get_sub_field('ad_blocker_promotion');

				$promo_config = $promo_id ? $this->prepare_config_for_promo($promo_id) : null;
				$ad_blocker_config = $ad_blocker_id ? $this->prepare_config_for_promo($ad_blocker_id) : null;

				$child_cookie_name = $parent_cookie_name . '_' . $variant_index;
				if ($promo_config) {
					$promo_config['cookie_name'] = $child_cookie_name;
					$promo_config['cookie_hours'] = 0;
					if (array_key_exists('close_cookie_hours', $promo_config)) {
						$promo_config['close_cookie_hours'] = 0;
					}
				}
				if ($ad_blocker_config) {
					$ad_blocker_config['cookie_name'] = $child_cookie_name;
					$ad_blocker_config['cookie_hours'] = 0;
					if (array_key_exists('close_cookie_hours', $ad_blocker_config)) {
						$ad_blocker_config['close_cookie_hours'] = 0;
					}
				}

				$variants[] = [
					'promotion' => $promo_config,
					'ad_blocker_promotion' => $ad_blocker_config
				];
				$variant_index++;
			}
		}

		return [
			'id' => $lightbox_id,
			'promotion_type' => 'ab_test',
			'trigger' => $this->compute_trigger_from_lightbox($lightbox_id),
			'cookie_name' => $parent_cookie_name,
			'cookie_hours' => (int) get_field('engrid_cookie_hours', $lightbox_id),
			'display' => get_field('engrid_lightbox_display', $lightbox_id),
			'start' => get_field('engrid_start_date', $lightbox_id),
			'end' => get_field('engrid_end_date', $lightbox_id),
			'variants' => $variants
		];
	}

	function is_lightbox($promotion) {
		$is_lightbox = in_array($promotion['promotion_type'], ['multistep_lightbox', 'overlay', 'signup_lightbox', 'cta_lightbox', 'email_capture_lightbox']);
		if(!$is_lightbox && $promotion['promotion_type'] == 'raw_code' && $promotion['is_lightbox']) {
			$is_lightbox = true;
		}
		return $is_lightbox;
	}

	function move_floating_signup_beyond_lightbox_promos($client_side_triggered_config) {
		$fs_idx = [];
		for($i = 0; $i < count($client_side_triggered_config); $i++) {
			if($client_side_triggered_config[$i]['promotion_type'] == 'floating_signup') {
				$fs_idx[] = $i;
			}
		}
		for($i = 0; $i < count($fs_idx); $i++) {
			$promo_to_move_idx = $fs_idx[$i];
			$last_lightbox_idx = -1;
			for($j = $promo_to_move_idx+1; $j < count($client_side_triggered_config); $j++) {
				if($this->is_lightbox($client_side_triggered_config[$j])) {
					$last_lightbox_idx = $j;
				}
			}
			if($promo_to_move_idx < $last_lightbox_idx) {
				$promo_to_move = $client_side_triggered_config[$promo_to_move_idx];
				array_splice($client_side_triggered_config, $promo_to_move_idx, 1);
				array_splice($client_side_triggered_config, $last_lightbox_idx, 0, [$promo_to_move]);
			}
		}
		return $client_side_triggered_config;
	}

	function move_first_floating_tab_to_top($client_side_triggered_config) {
		for($i = 0; $i < count($client_side_triggered_config); $i++) {
			if($client_side_triggered_config[$i]['promotion_type'] == 'floating_tab') {
				if($i == 0) {
					// already at the top
					break;
				}

				$promo_to_move = $client_side_triggered_config[$i];
				array_splice($client_side_triggered_config, $i, 1);
				array_splice($client_side_triggered_config, 0, 0, [$promo_to_move]);

				// we're only interested in the first floating tab promo
				break;
			}
		}
		return $client_side_triggered_config;
	}

	// Some hosts will not permit <script> tags in POSTs. This is a workaround for those hosts.
	function make_js_replacements($js) {
		$js = preg_replace("/\[script(.*)\]/U", "<script$1>", $js);
		$js = preg_replace("/\[\/script\]/", "</script>", $js);
		return $js;
	}
}
