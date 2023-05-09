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
 * Version:           1.0.8
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
define( 'foursite_wordpress_promotion_VERSION', '1.0.9' );

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