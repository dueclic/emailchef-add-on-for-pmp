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
	 * @var Emailchef_Add_On_For_Pmp_Api
	 */
	private $api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param Emailchef_Add_On_For_Pmp_Api $api New instance for Emailchef_Add_On_For_Pmp_Api class
	 * @param string $plugin_name The name of this plugin.
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
		register_setting( 'pmproecaddon_settings_group', 'pmproecaddon_settings' );

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
			'pmproecaddon_options',
			'pmproecaddon_pluginPage_section'
		);

		add_settings_field(
			'pmproecaddon_consumer_secret',
			__( 'Consumer Secret', 'emailchef-add-on-for-pmp' ),
			'sanitize_text_field',
			'pmproecaddon_options',
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

		$account = $this->api->set(
			$consumer_key,
			$consumer_secret
		)->account();

		if ( ! $account || ( isset( $account['status'] ) && $account['status'] === 'error' ) ) {
			update_option( 'pmproecaddon_plugin_user_enabled', 'no' );
			wp_send_json_error( [
				'message' => __( 'Login attempt unsuccessful. Ensure your API keys are entered correctly.', 'emailchef-add-on-for-pmp' )
			] );

		}

		update_option( 'pmproecaddon_consumer_key', $consumer_key );
		update_option( 'pmproecaddon_consumer_secret', $consumer_secret );
		update_option( 'pmproecaddon_plugin_user_enabled', 'yes' );
		wp_send_json_success( [] );
	}

	public function action_links_menu( $links ) {
		$new_links = array(
			'<a href="' . esc_url( admin_url( 'options-general.php?page=pmproecaddon_options' ) ) . '">' . esc_html__( 'Settings', 'emailchef-add-on-for-pmp' ) . '</a>',
		);

		return array_merge( $new_links, $links );
	}

	public function options_menu_page_callback() {

		$plugin_enabled = get_option( "pmproecaddon_plugin_user_enabled", "no" );

		if ( 'yes' !== $plugin_enabled ) {
			include_once plugin_dir_path( EMAILCHEF_ADD_ON_FOR_PMP_PATH ) . 'admin/partials/logged-out.php';
		} else {
			$api = $this->api;

			include_once plugin_dir_path( EMAILCHEF_ADD_ON_FOR_PMP_PATH ) . 'admin/partials/options.php';
		}

	}


	/*
		Add opt-in Lists to the user profile/edit user page.
	*/
	function user_custom_profile_fields( $user ) {

		if ( ! pmpro_hasMembershipLevel( null, $user->ID ) ) {
			return;
		}

		$current_membership = pmpro_getMembershipLevelForUser( $user->ID );

		if ( ! $current_membership ) {
			return;
		}

		$list_opt_in_audiences = get_option( 'pmproecaddon_plugin_list_opt_in_audiences', '' );

		if ( count( $list_opt_in_audiences ) > 0 ) {
			$lists = $this->api->lists();
			?>
            <h4><?php esc_html_e( 'Join our mailing list.', 'emailchef-add-on-for-pmp' ); ?></h4>

			<?php

			pmproecaddon_list_match_display(
				$lists,
				"opt_in_audiences",
				$list_opt_in_audiences
			);

		}

	}

	function user_custom_profile_update( $user_id ) {
		try {

			if ( ! pmpro_hasMembershipLevel( null, $user_id ) ) {
				return;
			}

			$current_membership = pmpro_getMembershipLevelForUser( $user_id);

			if ( ! $current_membership ) {
				return;
			}


			$user_email = get_userdata( $user_id )->user_email;

			$lists = $this->api->lists();

			$pmproecaddon_require_update_profile = get_option( 'pmproecaddon_require_update_profile', '' );

			if ( isset( $pmproecaddon_require_update_profile ) && $pmproecaddon_require_update_profile == "yes" ) {

				foreach ( $lists as $list ) {
					$name_checkbox = "opt_in_audiences_" . esc_html( str_replace( " ", "_", $list['name'] ) ) . "_checkbox";
					if ( isset( $_REQUEST[ $name_checkbox ] ) ) {
						$list_id = $list['id'];

						$first_name = sanitize_text_field( $_REQUEST["first_name"] );
						$last_name  = sanitize_text_field( $_REQUEST["last_name"] );

						$this->api->add_contact( $list_id, $user_email, $first_name, $last_name );

					}
				}
			}

		} catch ( Exception $e ) {
			echo $e->getMessage();
		}
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


	function save_options() {

		if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pmproecaddon-nonce'] ) ), 'pmproecaddon-nonce' )
		     && isset( $_POST['plugin_save'] )
		) {

			$list_config           = array();
			$list_opt_in_audiences = array();
			$list_nom_member       = array();
			$lists                 = $this->api->lists();

			if ( $lists != null ) {
				$membership_levels = pmpro_getAllLevels();
				if ( ! empty( $membership_levels ) ) {
					foreach ( $membership_levels as $membership_level ) {
						foreach ( $lists as $list ) {
							$checkbox_name = str_replace( " ", "_", $membership_level->name ) . '_' . str_replace( " ", "_", $list['name'] ) . '_checkbox';
							if ( isset( $_POST[ $checkbox_name ] ) ) {
								$list_config[ str_replace( " ", "_", $membership_level->name . "_" . str_replace( " ", "_", $list['name'] ) ) ] = sanitize_text_field( $_POST[ $checkbox_name ] );
							}
						}
					}
					update_option( 'pmproecaddon_plugin_list_config', $list_config );

					foreach ( $lists as $list ) {
						$checkbox_name = 'opt_in_audiences_' . str_replace( " ", "_", $list['name'] ) . '_checkbox';
						if ( isset( $_POST[ $checkbox_name ] ) ) {
							$list_nom_member[ "opt_in_audiences_" . str_replace( " ", "_", $list['name'] ) ] = sanitize_text_field( $_POST[ $checkbox_name ] );
						}
					}
					update_option( 'pmproecaddon_plugin_list_opt_in_audiences', $list_nom_member );

					foreach ( $lists as $list ) {
						$checkbox_name = 'non_member_audiences_' . str_replace( " ", "_", $list['name'] ) . '_checkbox';
						if ( isset( $_POST[ $checkbox_name ] ) ) {
							$list_opt_in_audiences[ "non_member_audiences_" . str_replace( " ", "_", $list['name'] ) ] = sanitize_text_field( $_POST[ $checkbox_name ] );
						}
					}
					update_option( 'pmproecaddon_plugin_list_non_member', $list_opt_in_audiences );

					if ( isset( $_POST['require_unsubscribe_on_level_select'] ) ) {
						$require_unsubscribe_on_leve_select = sanitize_text_field( $_POST['require_unsubscribe_on_level_select'] );
						update_option( 'pmproecaddon_require_unsuscribe_on_level', $require_unsubscribe_on_leve_select );
					}

					if ( isset( $_POST['require_update_profile_select'] ) ) {
						$require_update_profile_select = sanitize_text_field( $_POST['require_update_profile_select'] );
						update_option( 'pmproecaddon_require_update_profile', $require_update_profile_select );
					}
				} else {
					wp_safe_redirect( add_query_arg( 'pmproecaddon_msg', 'no_membership_levels', wp_get_referer() ) );
					exit;
				}
			}
			wp_safe_redirect( add_query_arg( 'pmproecaddon_msg', 'success', wp_get_referer() ) );
			exit;
		}
	}


	public
	function page_options_ajax_disconnect() {

		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['_pmproecaddon_nonce'] ), 'pmproecaddon_disconnect' ) ) {
			wp_send_json_error( [
				'message' => __( 'Invalid request', 'emailchef-add-on-for-pmp' )
			] );
		}


		deactivate_emailchef_add_on_for_pmp();

		wp_send_json_success( [
			'message' => __( 'Emailchef account successfully disconnected', 'emailchef-add-on-for-pmp' )
		] );


	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/emailchef-add-on-for-pmp-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'emailchefPMPI18n', [
			'disconnect_account_confirm' => __( 'Are you sure you want to disconnect your account?', 'emailchef-add-on-for-pmp' ),
			'login_correct'              => __( 'Login correct!', 'emailchef-add-on-for-pmp' ),
			'login_failed'               => __( 'Login failed!', 'emailchef-add-on-for-pmp' ),
		] );

	}

}
