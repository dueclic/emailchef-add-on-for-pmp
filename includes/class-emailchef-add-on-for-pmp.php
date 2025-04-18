<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.emailchef.com
 * @since      1.0.0
 *
 * @package    Emailchef_Add_On_For_Pmp
 * @subpackage Emailchef_Add_On_For_Pmp/includes
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
 * @package    Emailchef_Add_On_For_Pmp
 * @subpackage Emailchef_Add_On_For_Pmp/includes
 * @author     edisplayit <info@edisplay.it>
 */
class Emailchef_Add_On_For_Pmp {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Emailchef_Add_On_For_Pmp_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
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
		if ( defined( 'EMAILCHEF_ADD_ON_FOR_PMP_VERSION' ) ) {
			$this->version = EMAILCHEF_ADD_ON_FOR_PMP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'emailchef-add-on-for-pmp';

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
	 * - Emailchef_Add_On_For_Pmp_Loader. Orchestrates the hooks of the plugin.
	 * - Emailchef_Add_On_For_Pmp_i18n. Defines internationalization functionality.
	 * - Emailchef_Add_On_For_Pmp_Admin. Defines all hooks for the admin area.
	 * - Emailchef_Add_On_For_Pmp_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-emailchef-add-on-for-pmp-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-emailchef-add-on-for-pmp-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-emailchef-add-on-for-pmp-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-emailchef-add-on-for-pmp-public.php';

		$this->loader = new Emailchef_Add_On_For_Pmp_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Emailchef_Add_On_For_Pmp_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Emailchef_Add_On_For_Pmp_i18n();

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

		$plugin_admin = new Emailchef_Add_On_For_Pmp_Admin(
			new Emailchef_Add_On_For_Pmp_Api(
				get_option( 'pmproecaddon_consumer_key', '' ),
				get_option( 'pmproecaddon_consumer_secret', '' )
			),
			$this->get_plugin_name(),
			$this->get_version()
		);

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'options_menu_page' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'page_options_settings' );
		$this->loader->add_action( 'wp_ajax_emailchef-add-on-for-pmp_check_login', $plugin_admin, 'page_options_ajax_check_login' );
		$this->loader->add_action( 'wp_ajax_emailchef-add-on-for-pmp_disconnect', $plugin_admin, 'page_options_ajax_disconnect' );
		$this->loader->add_filter( 'plugin_action_links_' . plugin_basename( EMAILCHEF_ADD_ON_FOR_PMP_PATH ), $plugin_admin, 'action_links_menu' );

		$plugin_enabled = get_option( "pmproecaddon_plugin_user_enabled", "no" );

		if ( 'yes' === $plugin_enabled ) {
			$this->loader->add_action( 'admin_post_pmproecaddon_save_data', $plugin_admin, 'save_options' );
			$this->loader->add_action( 'show_user_profile', $plugin_admin, 'user_custom_profile_fields', 12 );
			$this->loader->add_action( 'edit_user_profile', $plugin_admin, 'user_custom_profile_fields', 12 );
			$this->loader->add_action( 'pmpro_show_user_profile', $plugin_admin, 'user_custom_profile_fields', 12 );
			$this->loader->add_action( 'personal_options_update', $plugin_admin, 'user_custom_profile_update' );
			$this->loader->add_action( 'edit_user_profile_update', $plugin_admin, 'user_custom_profile_update' );
			$this->loader->add_action( 'pmpro_personal_options_update', $plugin_admin, 'user_custom_profile_update' );
			$this->loader->add_action( 'pmpro_after_checkout', $plugin_admin, 'pmpro_checkout_emailchef_sync' );
			$this->loader->add_action( 'pmpro_checkout_after_tos_fields', $plugin_admin, 'pmpro_additional_lists_on_checkout' );
		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Emailchef_Add_On_For_Pmp_Public(
			new Emailchef_Add_On_For_Pmp_Api(
				get_option( 'pmproecaddon_consumer_key', '' ),
				get_option( 'pmproecaddon_consumer_secret', '' )
			),
			$this->get_plugin_name(),
			$this->get_version()
		);

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'pmproecaddon_api_response', $plugin_public, 'handle_api_response', 5 );

		$plugin_enabled = get_option( "pmproecaddon_plugin_user_enabled", "no" );

		if ( 'yes' === $plugin_enabled ) {
			$this->loader->add_action( 'pmpro_after_checkout', $plugin_public, 'pmpro_checkout_emailchef_sync' );
			$this->loader->add_action( 'pmpro_checkout_after_tos_fields', $plugin_public, 'pmpro_additional_lists_on_checkout' );
			// $this->loader->add_action( 'pmpro_after_change_membership_level', $plugin_public, 'pmpro_after_change_membership_level', 15, 2 );
			$this->loader->add_action( 'pmpro_after_checkout', $plugin_public, 'pmpro_after_checkout', 15 );
			$this->loader->add_action( 'user_register', $plugin_public, 'user_register' );
		}

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
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Emailchef_Add_On_For_Pmp_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

}
