<?php

class Foursite_Wordpress_Promotion_Migration_Base {
	static function acf_page_field_names() {
		return [
			'engrid_show_on' => '',
			'engrid_hide_on' => ''
		];
	}

	static function acf_repeater_field_names() {
		return [
			'engrid_confetti',
			'fes_gravity_form_submission_map',
			'attributes'
		];
	}
	static function acf_image_field_names() {
		return [
			'rollup' => 'image',
			'engrid_pushdown_image' => '',
			'engrid_pushdown_gif' => '',
			'engrid_image' => '',
			'engrid_divider' => '',
			'engrid_logo' => '',
			'modal_overlay' => 'background_image',
			'modal_overlay' => 'background_image_overlay',
			'image_file' => ''
		];
	}

	static function acf_include_if_empty_field_names() {
		return [
			'engrid_cookie_hours' => 0
		];
	}

	static function acf_field_names() {
		return [
			'engrid_start_date',
			'engrid_end_date',
			'engrid_lightbox_display',
			'engrid_hide_on',
			'engrid_show_on',
			'engrid_whitelist',
			'engrid_blacklist',
			'engrid_donation_page',
			'engrid_dp_append_chain',
			'engrid_promotion_type',
			'engrid_trigger_type',
			'engrid_hero_type',
			'engrid_image',
			'engrid_video',
			'engrid_use_logo',
			'engrid_logo',
			'engrid_logo_position',
			'engrid_raw_html',
			'engrid_divider',
			'engrid_title',
			'engrid_paragraph',
			'engrid_footer',
			'engrid_bg_color',
			'engrid_text_color',
			'engrid_form_color',
			'engrid_cookie_hours',
			'engrid_cookie_name',
			'engrid_trigger_seconds',
			'engrid_trigger_scroll_pixels',
			'engrid_trigger_scroll_percentage',
			'engrid_gtm_open_event_name',
			'engrid_gtm_close_event_name',
			'engrid_gtm_suppressed_event_name',
			'engrid_javascript',
			'engrid_html',
			'engrid_css',
			'engrid_show_view_more',
			'engrid_confetti',
			'engrid_fsft_color',
			'fes_button_colors',
			'fes_post_submission_button',
			'fes_gravity_form_id',
			'fes_gravity_form_email_field_id',
			'promotion_lightbox_recaptcha_site_key',
			'fes_closed_cookie_hours',
			'fes_post_submission_title',
			'fes_post_submission_paragraph',
			'fes_gravity_form_submission_map',
			'is_lightbox',
			'modal_overlay',
			'cta_type',
			'submit_button_label',
			'engrid_subtitle',
			'other_amount_label',
			'amount_options',
			'engrid_pushdown_type',
			'engrid_pushdown_image',
			'engrid_pushdown_gif',
			'engrid_pushdown_link',
			'engrid_pushdown_title',
			'pushdown_paragraph',
			'pushdown_button_label',
			'layout',
			'max_width',
			'engrid_fsft_radius',
			'engrid_fsft_location',
			'engrid_fsft_link',
			'engrid_fsft_trigger_type',
			'engrid_custom_svg',
			'rollup',
			'cta_lightbox',
			'attributes'
		];
	}
}