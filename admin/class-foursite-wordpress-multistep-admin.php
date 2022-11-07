<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.4sitestudios.com
 * @since      1.0.0
 *
 * @package    foursite_wordpress_promotion
 * @subpackage foursite_wordpress_promotion/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    foursite_wordpress_promotion
 * @subpackage foursite_wordpress_promotion/admin
 * @author     Fernando Santos <fernando@4sitestudios.com>
 */
class Foursite_Wordpress_Promotion_Admin {

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
	 * @param      string    $foursite_wordpress_promotion       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $foursite_wordpress_promotion, $version ) {

		$this->foursite_wordpress_promotion = $foursite_wordpress_promotion;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in foursite_wordpress_promotion_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The foursite_wordpress_promotion_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->foursite_wordpress_promotion, plugin_dir_url( __FILE__ ) . 'css/foursite-wordpress-promotion-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in foursite_wordpress_promotion_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The foursite_wordpress_promotion_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->foursite_wordpress_promotion, plugin_dir_url( __FILE__ ) . 'js/foursite-wordpress-promotion-admin.js', array( 'jquery' ), $this->version, false );

	}

}
