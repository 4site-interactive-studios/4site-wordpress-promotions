<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.4sitestudios.com
 * @since      1.0.0
 *
 * @package    Engrid_Wordpress_Multistep
 * @subpackage Engrid_Wordpress_Multistep/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Engrid_Wordpress_Multistep
 * @subpackage Engrid_Wordpress_Multistep/includes
 * @author     Fernando Santos <fernando@4sitestudios.com>
 */
class Engrid_Wordpress_Multistep {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Engrid_Wordpress_Multistep_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $engrid_wordpress_multistep    The string used to uniquely identify this plugin.
	 */
	protected $engrid_wordpress_multistep;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ENGRID_WORDPRESS_MULTISTEP_VERSION' ) ) {
			$this->version = ENGRID_WORDPRESS_MULTISTEP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->engrid_wordpress_multistep = 'engrid-wordpress-multistep';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Engrid_Wordpress_Multistep_Loader. Orchestrates the hooks of the plugin.
	 * - Engrid_Wordpress_Multistep_i18n. Defines internationalization functionality.
	 * - Engrid_Wordpress_Multistep_Admin. Defines all hooks for the admin area.
	 * - Engrid_Wordpress_Multistep_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		add_filter('acf/settings/load_json', 'engrid_wordpress_multistep_json_load_point');
		function engrid_wordpress_multistep_json_load_point( $paths ) {        			
			$paths[] = plugin_dir_path( dirname( __FILE__ ) ) . 'acf-json';
			return $paths;
		}


		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-engrid-wordpress-multistep-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-engrid-wordpress-multistep-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-engrid-wordpress-multistep-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-engrid-wordpress-multistep-public.php';

		$this->loader = new Engrid_Wordpress_Multistep_Loader();
		add_action('acf/save_post', 'engrid_wordpress_multistep_save_post');
		function engrid_wordpress_multistep_save_post( $post_id ) {
			// Detect when the options page for this plugin is being saved
			if($post_id == 'options' && isset($_POST['acf']['field_61f180fb94e9c'])) {
				// Clear the cloudflare caches. This requires both the
				// ENgrid Wordpress Multistep Cloudflare Addon plugin and 
				// the Cloudflare plugin.
				do_action('engrid_wpm_clear_cloudflare_all');
			}
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/options.php';

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Engrid_Wordpress_Multistep_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Engrid_Wordpress_Multistep_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Engrid_Wordpress_Multistep_Admin( $this->get_engrid_wordpress_multistep(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Engrid_Wordpress_Multistep_Public( $this->get_engrid_wordpress_multistep(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_engrid_wordpress_multistep() {
		return $this->engrid_wordpress_multistep;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Engrid_Wordpress_Multistep_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
