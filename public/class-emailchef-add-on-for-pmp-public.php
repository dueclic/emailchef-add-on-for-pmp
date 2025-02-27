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
	 * @var Emailchef_Add_On_For_Pmp_Api
	 */
	private $api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param Emailchef_Add_On_For_Pmp_Api $api New instance for Emailchef_Add_On_For_Pmp_Api class
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $api, $plugin_name, $version ) {

		$this->api         = $api;
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * @param array | WP_Error $response
	 *
	 * @void
	 */


	public function handle_api_response(
		$response
	) {
		$status_code = wp_remote_retrieve_response_code( $response );
		if ( $status_code === 401 ) {
			update_option( 'pmproecaddon_plugin_user_enabled', "no" );
		}

		do_action( "pmproecaddon_api_post_response", $response, $status_code );

	}


	/**
	 * Update Emailchef audiences when users checkout after usermeta is saved.
	 *
	 * @param int $user_id of user who checked out.
	 */
	function pmpro_checkout_emailchef_sync( $user_id ) {
		try {

			if ( ! pmpro_hasMembershipLevel( null, $user_id ) ) {
				return;
			}

			$current_membership = pmpro_getMembershipLevelForUser( $user_id );

			if ( ! $current_membership ) {
				return;
			}


			$level_name  = str_replace( " ", "_", $current_membership->name );
			$list_config = get_option( 'pmproecaddon_plugin_list_config', '' );

			$user = get_userdata( $user_id );

			$user_email = $user->user_email;
			$first_name = $user->first_name;
			$last_name  = $user->last_name;

			$lists = $this->api->lists();

			foreach ( $lists as $list ) {

				if ( isset( $list_config[ $level_name . "_" . str_replace( " ", "_", $list['name'] ) ] ) ) {
					$list_id = $list_config[ $level_name . "_" . str_replace( " ", "_", $list['name'] ) ];
					$this->api->add_contact( $list_id, $user_email, $first_name, $last_name );
				}
			}

		} catch ( Exception $e ) {
		}
	}

	function pmpro_additional_lists_on_checkout() {
		try {
			$list_opt_in_audiences = get_option( 'pmproecaddon_plugin_list_opt_in_audiences', '' );

			if ( count( $list_opt_in_audiences ) > 0 ) {
				$lists = $this->api->lists();
				?>
                <h3><?php esc_html_e( 'Join our mailing list.', 'emailchef-add-on-for-pmp' ); ?></h3>
				<?php
				pmproecaddon_list_match_display(
					$lists,
					"opt_in_audiences",
					$list_opt_in_audiences
				);
			}
		} catch ( Exception $e ) {

		}
	}

	/**
	 * Subscribe new members (PMPro) when their membership level changes
	 *
	 * @param $level_id (int) -- ID of pmpro membership level.
	 * @param $user_id (int) -- ID for user.
	 */
	function pmpro_after_change_membership_level( $level_id, $user_id ) {
		try {
			if ( pmpro_hasMembershipLevel( null, $user_id ) ) {
				$current_membership = pmpro_getMembershipLevelForUser( $user_id );

				if ( $current_membership ) {
					$level_name = $current_membership->name;

					$unsuscribe_option = get_option( 'pmproecaddon_require_unsuscribe_on_level', '' );

					if ( $unsuscribe_option == "yes_only_old_levels" ) {
						/*
						$list_config = get_option('pmproecaddon_plugin_list_config', '');

						$user_email = get_userdata($user_id)->user_email;
						$user_login = get_userdata($user_id)->user_login;


						foreach ($list_config as $list) {
							if (strpos(key($list), $level_name) !== 0) {
								$id_list = $list;
								pmproecaddon_delete_contact_ec($id_list,$user_email,$user_login);
							}
						}
						*/
					}

					if ( $unsuscribe_option == "yes_old_level" ) {

					}

					//update_option('pmproecaddon_plugin_message', "level_name: " . $level_name . ' user_id:' . $user_id . ' level_id' . $level_id);
				}
			}
		} catch ( Exception $e ) {
		}
	}

	function user_register( $user_id ) {
		try {

			if ( ! pmpro_hasMembershipLevel( null, $user_id ) ) {
				return;
			}

			$current_membership = pmpro_getMembershipLevelForUser( $user_id );

			if ( ! $current_membership ) {
				return;
			}

			$user = get_userdata( $user_id );

			$user_email = $user->user_email;
			$first_name = $user->first_name;
			$last_name  = $user->last_name;

			$lists = $this->api->lists();

			foreach ( $lists as $list ) {
				$name_checkbox = "opt_in_audiences_" . esc_html( str_replace( " ", "_", $list['name'] ) ) . "_checkbox";
				if ( isset( $_REQUEST[ $name_checkbox ] ) ) {
					$list_id = sanitize_text_field( $_REQUEST[ $name_checkbox ] );
					$this->api->add_contact( $list_id, $user_email, $first_name, $last_name );
				}
			}

		} catch ( Exception $e ) {
		}
	}

	function pmpro_after_checkout( $user_id ) {
		try {

			if ( ! pmpro_hasMembershipLevel( null, $user_id ) ) {
				return;
			}

			$current_membership = pmpro_getMembershipLevelForUser( $user_id );

			if ( ! $current_membership ) {
				return;
			}


			$level_name = $current_membership->name;

			$user = get_userdata( $user_id );

			$user_email = $user->user_email;
			$first_name = $user->first_name;
			$last_name  = $user->last_name;

			$lists = $this->api->lists();

			foreach ( $lists as $list ) {
				$name_checkbox = "opt_in_audiences_" . esc_html( str_replace( " ", "_", $list['name'] ) ) . "_checkbox";
				if ( isset( $_REQUEST[ $name_checkbox ] ) ) {
					$list_id = sanitize_text_field( $_REQUEST[ $name_checkbox ] );
					$this->api->add_contact( $list_id, $user_email, $first_name, $last_name );
				}
			}

		} catch ( Exception $e ) {
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public
	function enqueue_styles() {

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
	public
	function enqueue_scripts() {

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
