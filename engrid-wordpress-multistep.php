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
 * @package           Engrid_Wordpress_Multistep
 *
 * @wordpress-plugin
 * Plugin Name:       ENgrid Wordpress Multistep
 * Plugin URI:        https://www.4sitestudios.com/engrid-wordpress-multistep/
 * Description:       Add Engaging Networks Multistep Form to your WordPress site.
 * Version:           1.0.4
 * Author:            4Site Studios
 * Author URI:        https://www.4sitestudios.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       engrid-wordpress-multistep
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ENGRID_WORDPRESS_MULTISTEP_VERSION', '1.0.4' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-engrid-wordpress-multistep-activator.php
 */
function activate_engrid_wordpress_multistep() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-engrid-wordpress-multistep-activator.php';
	Engrid_Wordpress_Multistep_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-engrid-wordpress-multistep-deactivator.php
 */
function deactivate_engrid_wordpress_multistep() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-engrid-wordpress-multistep-deactivator.php';
	Engrid_Wordpress_Multistep_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_engrid_wordpress_multistep' );
register_deactivation_hook( __FILE__, 'deactivate_engrid_wordpress_multistep' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-engrid-wordpress-multistep.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_engrid_wordpress_multistep() {

	$plugin = new Engrid_Wordpress_Multistep();
	$plugin->run();

}
run_engrid_wordpress_multistep();
