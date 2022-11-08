<?php
/*
*
* File to send acf fields to Javascript when Raw Code option is selected
*
*/

define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/public/class-foursite-wordpress-promotion-public.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');


$lightbox = new Foursite_Wordpress_Promotion_Public("foursite-wordpress-promotion", "1.0.0");
$lightbox_ids = $lightbox->get_lightbox();

if($lightbox_ids) {
  $output = [];

  foreach($lightbox_ids as $lightbox_id) {
    $promotion_type = get_field('engrid_promotion_type', $lightbox_id);

    if($promotion_type == "raw_code") {
      $engrid_javascript = get_field('engrid_javascript', $lightbox_id);
      $engrid_html = get_field('engrid_html', $lightbox_id);
      $engrid_css = get_field('engrid_css', $lightbox_id);
      $engrid_cookie_hours = get_field('engrid_cookie_hours', $lightbox_id);
      $engrid_cookie_name = get_field('engrid_cookie_name', $lightbox_id);
      $engrid_trigger_type = get_field('engrid_trigger_type', $lightbox_id);
      $engrid_trigger_seconds = get_field('engrid_trigger_seconds', $lightbox_id);
      $engrid_trigger_scroll_pixels = get_field('engrid_trigger_scroll_pixels', $lightbox_id);
      $engrid_trigger_scroll_percentage = get_field('engrid_trigger_scroll_percentage', $lightbox_id);

      $raw_code_arr = [];
      $raw_code_arr["cookie"] = $engrid_cookie_name;
      $raw_code_arr["cookie_hours"] = $engrid_cookie_hours;
      $raw_code_arr["id"] = $lightbox_id;
      
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
  }

  if(count($output) > 0) {
    echo json_encode($output);
  }
}