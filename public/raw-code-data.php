<?php
/*
*
* Separate file for sending data to Javascript when necessary
*
*/

define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/public/class-foursite-wordpress-promotion-public.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

$lightbox = new Foursite_Wordpress_Promotion_Public("foursite-wordpress-promotion", "1.0.0");
$lightbox_ids = $lightbox->get_lightbox_ids();
$script_name = "foursite-wordpress-promotion-".$lightbox_id."-js-before";
wp_enqueue_script( $script_name, plugin_dir_url( __FILE__ ) . 'js/donation-lightbox-parent.js', array(), "1.0.0", false );

if($lightbox_ids) {
  $output = [];

  foreach($lightbox_ids as $lightbox_id) {
    $promotion_type = get_field('engrid_promotion_type', $lightbox_id);
    $engrid_donation_page = get_field('engrid_donation_page', $lightbox_id);
    $engrid_trigger_type = get_field('engrid_trigger_type', $lightbox_id);
    
    if($promotion_type == "raw_code") {
      $engrid_javascript = get_field('engrid_javascript', $lightbox_id);
      $engrid_html = get_field('engrid_html', $lightbox_id);
      $engrid_css = get_field('engrid_css', $lightbox_id);
      $engrid_cookie_hours = get_field('engrid_cookie_hours', $lightbox_id);
      $engrid_cookie_name = get_field('engrid_cookie_name', $lightbox_id);
      $engrid_trigger_seconds = get_field('engrid_trigger_seconds', $lightbox_id);
      $engrid_trigger_scroll_pixels = get_field('engrid_trigger_scroll_pixels', $lightbox_id);
      $engrid_trigger_scroll_percentage = get_field('engrid_trigger_scroll_percentage', $lightbox_id);

      $raw_code_arr = [];
      $raw_code_arr["cookie"] = $engrid_cookie_name;
      $raw_code_arr["cookie_hours"] = $engrid_cookie_hours;
      $raw_code_arr["id"] = $lightbox_id;
      $raw_code_arr["type"] = "raw_code";
      
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

      $raw_code_arr["trigger"] = $trigger;

      if($engrid_css) {
        $engrid_css = `<style type='text/css'>`.$engrid_css.`</style>`;
        $raw_code_arr["css"] = $engrid_css;
      }

      if($engrid_html) {
        $raw_code_arr["html"] = $engrid_html;
      }

      if($engrid_javascript) {
        $raw_code_arr["javascript"] = $engrid_javascript;
      }

      if(count($raw_code_arr) > 0) {
        $output[] = $raw_code_arr;
      }
    }

    elseif($promotion_type == "multistep_lightbox" && trim($engrid_trigger_type) == "js") {
      $lightbox_arr = [];
      $lightbox_arr["type"] = "lightbox";
      $lightbox_arr["id"] = $lightbox_id;
      
      $engrid_donation_page = get_field('engrid_donation_page', $lightbox_id);
      $engrid_promotion_type = get_field('engrid_promotion_type', $lightbox_id);

      // Only render the plugin if the donation page is set
      if($engrid_donation_page){
        $engrid_hero_type = get_field('engrid_hero_type', $lightbox_id);
        $engrid_image = ($engrid_hero_type == 'image') ? get_field('engrid_image', $lightbox_id) : '';
        $engrid_video = ($engrid_hero_type != 'image') ? get_field('engrid_video', $lightbox_id) : '';
        $engrid_use_logo = get_field('engrid_use_logo', $lightbox_id);
        $engrid_logo = ($engrid_use_logo) ? get_field('engrid_logo', $lightbox_id) : '';
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
        $engrid_javascript = get_field('engrid_javascript', $lightbox_id);
        $engrid_html = get_field('engrid_html', $lightbox_id);
        $engrid_css = get_field('engrid_css', $lightbox_id);
        $confetti = array();

        if(have_rows('engrid_confetti', $lightbox_id) ){
          while( have_rows('engrid_confetti', $lightbox_id) ){
            the_row();
            $confetti[] = get_sub_field('color');
          }
        }

        $trigger = 0;

        $engrid_video_auto_play = ($engrid_hero_type == 'autoplay-video') ? true : false;

        $engrid_confetti = json_encode($confetti);

        $engrid_js_code = <<<ENGRID

        console.log('Wordpress Promotion ID: $lightbox_id');

        DonationLightboxOptions = {
          promotion_type: "$engrid_promotion_type",
          url: "$engrid_donation_page",
          image: "$engrid_image",
          logo: "$engrid_logo",
          video: "$engrid_video",
          autoplay: "$engrid_video_auto_play",
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
        
        $lightbox_arr["script_name"] = $script_name;
        $lightbox_arr["script_code"] = $engrid_js_code;

        $output[] = $lightbox_arr;
      }
    }
  }

  if(count($output) > 0) {
    echo json_encode($output);
  }
}

