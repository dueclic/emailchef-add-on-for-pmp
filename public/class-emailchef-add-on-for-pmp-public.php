<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.emailchef.com
 * @since      1.0.0
 *
 * @package    Emailchef_Add_On_For_Pmp
 * @subpackage Emailchef_Add_On_For_Pmp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Emailchef_Add_On_For_Pmp
 * @subpackage Emailchef_Add_On_For_Pmp/public
 * @author     edisplayit <info@edisplay.it>
 */
class Emailchef_Add_On_For_Pmp_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * @param array | WP_Error $response
	 *
	 * @void
	 */


	public function handle_api_response(
		$response
	){
		$status_code = wp_remote_retrieve_response_code( $response );
		if ( $status_code === 401 ) {
			update_option( 'pmproecaddon_plugin_user_enabled', "no" );
		}

		do_action( "pmproecaddon_api_post_response", $response, $status_code );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/emailchef-add-on-for-pmp-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/emailchef-add-on-for-pmp-public.js', array( 'jquery' ), $this->version, false );

	}

}
