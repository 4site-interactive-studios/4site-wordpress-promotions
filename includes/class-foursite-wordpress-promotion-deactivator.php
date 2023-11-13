<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.4sitestudios.com
 * @since      1.0.0
 *
 * @package    Foursite_Wordpress_Promotion
 * @subpackage Foursite_Wordpress_Promotion/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Foursite_Wordpress_Promotion
 * @subpackage Foursite_Wordpress_Promotion/includes
 * @author     Fernando Santos <fernando@4sitestudios.com>
 */
class Foursite_Wordpress_Promotion_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		$next_scheduled_cron_run = wp_next_scheduled('fs_wp_promo_hook');
		if($next_scheduled_cron_run) {
			wp_unschedule_event($next_scheduled_cron_run, 'fs_wp_promo_hook');
		}
	}

}
