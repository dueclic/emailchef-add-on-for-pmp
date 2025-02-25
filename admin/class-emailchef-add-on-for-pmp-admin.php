<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.emailchef.com
 * @since      1.0.0
 *
 * @package    Emailchef_Add_On_For_Pmp
 * @subpackage Emailchef_Add_On_For_Pmp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Emailchef_Add_On_For_Pmp
 * @subpackage Emailchef_Add_On_For_Pmp/admin
 * @author     edisplayit <info@edisplay.it>
 */
class Emailchef_Add_On_For_Pmp_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

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
		 * defined in Emailchef_Add_On_For_Pmp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Emailchef_Add_On_For_Pmp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/emailchef-add-on-for-pmp-admin.css', array(), $this->version, 'all' );

	}

	public function options_menu_page() {

		add_options_page(
			'PMPro Emailchef Options'
			, __( 'PMPro Emailchef', 'emailchef-add-on-for-pmp' ),
			'manage_options', 'pmproecaddon_options',
			[ $this, 'options_menu_page_callback' ]
		);
	}

	public function page_options_settings() {
		register_setting( 'pluginPage', 'pmproecaddon_settings' );

		add_settings_section(
			'pmproecaddon_pluginPage_section',
			__( 'Account details', 'emailchef-add-on-for-pmp' ),
			[],
			'pluginPage'
		);

		add_settings_field(
			'pmproecaddon_consumer_key',
			__( 'Consumer Key', 'emailchef-add-on-for-pmp' ),
			'sanitize_text_field',
			'pluginPage',
			'pmproecaddon_pluginPage_section'
		);

		add_settings_field(
			'pmproecaddon_consumer_secret',
			__( 'Consumer Secret', 'emailchef-add-on-for-pmp' ),
			'sanitize_text_field',
			'pluginPage',
			'pmproecaddon_pluginPage_section'
		);
	}

	public function page_options_ajax_check_login() {

		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['_pmproecaddon_nonce'] ), 'emailchef-add-on-for-pmp_check_login' ) ) {
			wp_send_json_error( [
				'message' => __( 'Invalid request', 'emailchef-add-on-for-pmp' )
			] );
		}

		$consumer_key    = sanitize_text_field( $_POST['consumer_key'] );
		$consumer_secret = sanitize_text_field( $_POST['consumer_secret'] );

		$emailchefApi = new Emailchef_Add_On_For_Pmp_Api(
			$consumer_key,
			$consumer_secret
		);

		$account = $emailchefApi->account();

		if ( !$account || (isset( $account['status'] ) && $account['status'] === 'error') ) {

			update_option( 'pmproecaddon_plugin_user_enabled', 'no' );
			wp_send_json_error( [
				'message' => __( 'Login attempt unsuccessful. Ensure your API keys are entered correctly.', 'emailchef-add-on-for-pmp' )
			] );

		}
		update_option( 'pmproecaddon_plugin_user_enabled', 'yes' );
		wp_send_json_success( [] );
	}

	public function options_menu_page_callback() {

		$plugin_enabled = get_option("pmproecaddon_plugin_user_enabled", "no");

		if ( 'yes' !== $plugin_enabled ) {
			include_once plugin_dir_path( EMAILCHEF_ADD_ON_FOR_PMP_PATH ) . 'admin/partials/logged-out.php';
		} else {
			include_once plugin_dir_path( EMAILCHEF_ADD_ON_FOR_PMP_PATH ) . 'admin/partials/options.php';
		}

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
		 * defined in Emailchef_Add_On_For_Pmp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Emailchef_Add_On_For_Pmp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/emailchef-add-on-for-pmp-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'emailchefPMPI18n', [
			'disconnect_account_confirm' => __( 'Are you sure you want to disconnect your account?', 'emailchef-add-on-for-pmp' ),
			'login_correct'              => __( 'Login correct!', 'emailchef-add-on-for-pmp' ),
			'login_failed'               => __( 'Login failed!', 'emailchef-add-on-for-pmp' ),
		] );

	}

}
