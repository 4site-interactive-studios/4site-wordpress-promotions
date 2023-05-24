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
 * Version:           1.0.10
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
define( 'foursite_wordpress_promotion_VERSION', '1.0.10' );

// Gutenberg Block
function promotions_en_multistep_block() {
	register_block_type(__DIR__ . '/blocks/en-multistep');
}
add_action( 'init', 'promotions_en_multistep_block' );

function generate_en_multistep_shortcode($atts) {
	wp_enqueue_script('donation-multistep-parent'); // Only load the script when the shortcode is used
    $shortcode_atts = shortcode_atts(
        array(
            'url' => '',
            'form-color' => '#f26722',
            'height' => '500px',
            'border-radius' => '5px',
            'loading-color' => '#E5E6E8',
            'bounce-color' => '#16233f',
            'append-url-params' => 'true',
        ),
        $atts,
        'en-multistep'
    );

    // Extract the shortcode attributes
    $url = $shortcode_atts['url'];
    $form_color = $shortcode_atts['form-color'];
    $height = $shortcode_atts['height'];
    $border_radius = $shortcode_atts['border-radius'];
    $loading_color = $shortcode_atts['loading-color'];
    $bounce_color = $shortcode_atts['bounce-color'];
    $append_url_params = $shortcode_atts['append-url-params'];

    // Generate the iframe shortcode string
    $shortcode = '<iframe id="promo-multistep-iframe" ';
    $shortcode .= 'data-src="' . esc_url($url) . '" ';
    $shortcode .= 'data-form_color="' . esc_attr($form_color) . '" ';
    $shortcode .= 'data-height="' . esc_attr($height) . '" ';
    $shortcode .= 'data-border_radius="' . esc_attr($border_radius) . '" ';
    $shortcode .= 'data-loading_color="' . esc_attr($loading_color) . '" ';
    $shortcode .= 'data-bounce_color="' . esc_attr($bounce_color) . '" ';
    $shortcode .= 'data-append_url_params="' . esc_attr($append_url_params) . '"></iframe>';

    return $shortcode;
}
add_shortcode('en-multistep', 'generate_en_multistep_shortcode');

function promotions_multistep_wp_enqueue_scripts() {
    wp_register_script( 'donation-multistep-parent', plugins_url( '/public/js/donation-multistep-parent.js', __FILE__ ), array(), foursite_wordpress_promotion_VERSION, 'all' );
}
add_action( 'wp_enqueue_scripts', 'promotions_multistep_wp_enqueue_scripts' );


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
