<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.4sitestudios.com
 * @since             1.0.0
 * @package           foursite_wordpress_promotion
 *
 * @wordpress-plugin
 * Plugin Name:       Foursite Wordpress Promotion
 * Plugin URI:        https://www.4sitestudios.com/foursite-wordpress-promotion/
 * Description:       Add Foursite Wordpress Promotion Form to your WordPress site.
 * Version:           1.0.22
 * Author:            4Site Studios
 * Author URI:        https://www.4sitestudios.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       foursite-wordpress-promotion
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
if ( defined( 'foursite_wordpress_promotion_VERSION' ) ) {
    error_log('fwpv already defined');
}
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'foursite_wordpress_promotion_VERSION', '1.0.22' );

// Gutenberg Block
function promotions_en_form_block() {
    register_block_type(__DIR__ . '/blocks/en-form');
}
add_action( 'init', 'promotions_en_form_block' );

function fwp_generate_en_form_shortcode($atts) {
    wp_enqueue_script('en-form-parent'); // Only load the script when the shortcode is used
    $shortcode_atts = shortcode_atts(
        array(
            'url' => '',
            'form-color' => '#f26722',
            'height' => '500px',
            'border-radius' => '5px',
            'loading-color' => '#E5E6E8',
            'bounce-color' => '#16233f',
            'append-url-params' => 'true',
            'frame-title' => ''
        ),
        $atts,
        'en-form'
    );

    // Extract the shortcode attributes
    $url = $shortcode_atts['url'];
    $form_color = $shortcode_atts['form-color'];
    $height = $shortcode_atts['height'];
    $border_radius = $shortcode_atts['border-radius'];
    $loading_color = $shortcode_atts['loading-color'];
    $bounce_color = $shortcode_atts['bounce-color'];
    $append_url_params = $shortcode_atts['append-url-params'];
    $frame_title = $shortcode_atts['frame-title'];

    // Generate the iframe shortcode string
    $shortcode = '<iframe class="promo-form-iframe" ';
    $shortcode .= 'data-src="' . esc_url($url) . '" ';
    $shortcode .= 'data-form_color="' . esc_attr($form_color) . '" ';
    $shortcode .= 'data-height="' . esc_attr($height) . '" ';
    $shortcode .= 'data-border_radius="' . esc_attr($border_radius) . '" ';
    $shortcode .= 'data-loading_color="' . esc_attr($loading_color) . '" ';
    $shortcode .= 'data-bounce_color="' . esc_attr($bounce_color) . '" ';
    $shortcode .= 'title="' . esc_attr($frame_title) . '" ';
    $shortcode .= 'data-append_url_params="' . esc_attr($append_url_params) . '"></iframe>';

    return $shortcode;
}
add_shortcode('en-form', 'fwp_generate_en_form_shortcode');

function promotions_en_form_wp_enqueue_scripts() {
    wp_register_script( 'en-form-parent', plugins_url( '/en-form/dist/en-form-parent.js', __FILE__ ), array(), foursite_wordpress_promotion_VERSION, 'all' );
}
add_action( 'wp_enqueue_scripts', 'promotions_en_form_wp_enqueue_scripts' );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-foursite-wordpress-promotion-activator.php
 */
function activate_foursite_wordpress_promotion() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-foursite-wordpress-promotion-activator.php';
    foursite_wordpress_promotion_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-foursite-wordpress-promotion-deactivator.php
 */
function deactivate_foursite_wordpress_promotion() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-foursite-wordpress-promotion-deactivator.php';
    foursite_wordpress_promotion_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_foursite_wordpress_promotion' );
register_deactivation_hook( __FILE__, 'deactivate_foursite_wordpress_promotion' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-foursite-wordpress-promotion.php';



function foursite_wordpress_promotion_schedule_initial_cron() {
    $tomorrow = new DateTime('tomorrow');
    $tomorrow->setTime(0,1);
    $result = wp_schedule_event($tomorrow->getTimestamp(), 'daily', 'fs_wp_promo_hook', [], true);
    if(is_wp_error($result)) {
        $messages = $result->getErrorMessages();
        //error_log('DEBUG: error scheduling event ' . print_r($messages,true));
    }
}

add_action('fs_wp_promo_hook', 'foursite_wordpress_promotion_cron_exec');
function foursite_wordpress_promotion_cron_exec() {
    //error_log('DEBUG: foursite_wordpress_promotion_cron_exec START');
    $today = date('Y-m-d');
    $current_schedule = get_option('fs_wp_promo_cron_schedules', []);
    $clear_cache = false;
    foreach($current_schedule as $post_id => $dates) {
        foreach($dates as $idx => $date) {
            if($date < $today) {
                $clear_cache = true;
                unset($current_schedule[$post_id][$idx]);
            }
        }
    }
    foreach($current_schedule as $post_id => $dates) {
        if(count($dates) == 0) {
            unset($current_schedule[$post_id]);
        }
    }
    if(count($current_schedule) == 0) {
        $next_scheduled_cron_run = wp_next_scheduled('fs_wp_promo_hook');
        if($next_scheduled_cron_run) {
            //error_log('DEBUG: unscheduling fs_wp_promo_hook');
            wp_unschedule_event($next_scheduled_cron_run, 'fs_wp_promo_hook');
        }
    }
    if($clear_cache) {
        //error_log('DEBUG: Triggering cloudflare cache clear.  Updating cache clear schedule: ' . print_r($current_schedule,true));
        do_action('engrid_fwp_clear_cloudflare_all');
        update_option('fs_wp_promo_cron_schedules', $current_schedule); 
    }
}

add_action('acf/save_post', 'foursite_wordpress_promotion_save_post');
function foursite_wordpress_promotion_save_post( $post_id ) {
    //error_log('DEBUG: foursite_wordpress_promotion_save_post start');
    // Detect when the options page for this plugin is being saved
    if($post_id == 'options' && isset($_POST['acf']['field_61f180fb94e9c'])) {
        // Clear the cloudflare caches. This requires both the
        // Foursite Wordpress Promotion Cloudflare Addon plugin and 
        // the Cloudflare plugin.
        do_action('engrid_fwp_clear_cloudflare_all');
        //error_log('DEBUG: foursite_wordpress_promotion_save_post Options updated. Clearing cache...');
    } else {        
        $post_type = get_post_type($post_id);
        if($post_type == 'wordpress_promotion') {
            //error_log('DEBUG: foursite_wordpress_promotion_save_post wordpress_promotion updated. Clearing cache...');
            foursite_wordpress_promotion_update_schedule($post_id);
        }
    }
}

function foursite_wordpress_promotion_update_schedule($post_id) {
    //error_log('DEBUG: foursite_wordpress_promotion_update_schedule start');
    $cron_clear_dates = [];
    $values = get_fields($post_id);
    $current_schedule = get_option('fs_wp_promo_cron_schedules', []);
    if(isset($current_schedule[$post_id])) {
        unset($current_schedule[$post_id]);
    }
    if($values['engrid_lightbox_display'] == 'scheduled') {
        $start_date = date('Y-m-d', strtotime($values['engrid_start_date']));
        $end_date = date('Y-m-d', strtotime($values['engrid_end_date']));
        $current_date = date('Y-m-d');
        if($start_date >= $current_date) {
            $cron_clear_dates[] = $start_date;
        }
        if($end_date >= $current_date && $start_date != $end_date) {
            $cron_clear_dates[] = $end_date;
        }
        if($end_date < $current_date) {
            //error_log('DEBUG: Past end date for scheduled promo detected; triggering cache clear.');
            do_action('engrid_fwp_clear_cloudflare_all');
        }
    }
    if(count($cron_clear_dates)) {
        $current_schedule[$post_id] = $cron_clear_dates;
    }
    //error_log('DEBUG: Setting promo cache clear schedule for ' . $post_id . ' : ' . print_r($current_schedule,true));
    update_option('fs_wp_promo_cron_schedules', $current_schedule);

    // set cron run for every day at midnight, if we have events to clear cache for; otherwise, remove the scheduled cron run
    $next_scheduled_cron_run = wp_next_scheduled('fs_wp_promo_hook');
    if(count($current_schedule)) {
        if(!$next_scheduled_cron_run) {
            //error_log('DEBUG: No scheduled cron run for fs_wp_promo_hook.  Initializing it, now.');
            foursite_wordpress_promotion_schedule_initial_cron();
        }
    } else if($next_scheduled_cron_run) {
        //error_log('DEBUG: No need for a scheduled cron run for fs_wp_promo_hook.  Removing it, now.');        
        wp_unschedule_event($next_scheduled_cron_run, 'fs_wp_promo_hook');
    }
}




/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_foursite_wordpress_promotion() {
    $plugin = new foursite_wordpress_promotion();
    $plugin->run();
}
run_foursite_wordpress_promotion();
