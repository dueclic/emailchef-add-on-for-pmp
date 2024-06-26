<?php
/**
 * @link              https://emailchef.com/
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name: Emailchef Add On for Paid Memberships Pro
 * Plugin URI:        http://emailchef.com/
 * Description: Sync your WordPress users and members with Emaiclhef audiences.
 * Version: 1.0.0
 * Author: dueclic
 * Author URI:        https://www.dueclic.com
 * Text Domain: emailchef-add-on-for-pmp
 * Domain Path:       /languages
 * Requires at least: 4.7
 * Requires PHP: 7.0
 * License: GPLv2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PMPROECADDON_DIR', dirname( __FILE__ ) );

include( "includes/api_call.php" );

/**
 * Add checkbox for plugin
 *
 * @param $name (string) - The existing link array
 *
 */
function pmproecaddon_list_ec_plugin_display( $name ) {
	echo '<td>';
	echo '<div class="checkbox-container">';

	$list_data   = get_option( 'pmproecaddon_list_data', '' );
	$list_config = get_option( 'pmproecaddon_plugin_list_config', '' );

	foreach ( $list_data as $list ) {
		$name_checkbox = $name . '_' . esc_html( str_replace( " ", "_", $list['name'] ) );
		echo '<label class="checkbox-item">';
		if ( isset( $list_config[ $name_checkbox ] ) && $list_config[ $name_checkbox ] == $list['id'] ) {
			echo '<input style="margin-left: 5px" type="checkbox" name="' . esc_html( $name ) . '_' . esc_html( str_replace( " ", "_", $list['name'] ) ) . '_checkbox" value="' . esc_html( $list['id'] ) . '" checked> ' . esc_html( $list['name'] );
		} else {
			echo '<input style="margin-left: 5px" type="checkbox" name="' . esc_html( $name ) . '_' . esc_html( str_replace( " ", "_", $list['name'] ) ) . '_checkbox" value="' . esc_html( $list['id'] ) . '"> ' . esc_html( $list['name'] );
		}
		echo '</label>';
	}
	echo '</div>';
	echo '</td>';
}

/* MENU */
/**
 * Add the admin options page
 *
 *
 */
function pmproecaddon_admin_add_page() {
	add_options_page( 'PMPro Emailchef Options', 'PMPro Emailchef', 'manage_options', 'pmproecaddon_options', 'pmproecaddon_options_page' );
}

add_action( 'admin_menu', 'pmproecaddon_admin_add_page' );

/**
 * Add menu to plugin settings
 *
 *
 */
function pmproecaddon_menu( $links ) {
	$new_links = array(
		'<a href="' . get_admin_url( null, 'options-general.php?page=pmproecaddon_options' ) . '">' . __( 'Settings', 'emailchef-add-on-for-pmp' ) . '</a>',
	);

	return array_merge( $new_links, $links );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pmproecaddon_menu' );

/**
 * Add options to settings menu
 *
 *
 */
function pmproecaddon_options_page() {
	if ( isset( $_POST['plugin_save'] ) && ( ! isset( $_POST['pmproecaddon-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pmproecaddon-nonce'] ) ), 'pmproecaddon-nonce' ) ) ) {
		// Guardar la configuración cuando se envía el formulario
		$user_ec     = sanitize_text_field( $_POST['user_ec'] );
		$password_ec = sanitize_text_field( $_POST['pass_ec'] );
		update_option( 'pmproecaddon_plugin_user_ec', $user_ec );
		update_option( 'pmproecaddon_plugin_pass_ec', $password_ec );

		$list_config           = array();
		$list_opt_in_audiences = array();
		$list_nom_member       = array();
		$list_data             = get_option( 'pmproecaddon_list_data', '' );

		if ( $list_data != null ) {
			$subscriptions = pmpro_getAllLevels();
			if ( ! empty( $subscriptions ) ) {
				foreach ( $subscriptions as $subscription ) {
					foreach ( $list_data as $list ) {
						if ( isset( $_POST[ str_replace( " ", "_", $subscription->name ) . '_' . str_replace( " ", "_", $list['name'] ) . '_checkbox' ] ) ) {
							$list_config[ str_replace( " ", "_", $subscription->name . "_" . str_replace( " ", "_", $list['name'] ) ) ] = sanitize_text_field( $_POST[ str_replace( " ", "_", $subscription->name ) . '_' . str_replace( " ", "_", $list['name'] ) . '_checkbox' ] );
						}
					}
				}
				update_option( 'pmproecaddon_plugin_list_config', $list_config );

				foreach ( $list_data as $list ) {
					if ( isset( $_POST[ 'opt_in_audiences_' . str_replace( " ", "_", $list['name'] ) . '_checkbox' ] ) ) {
						$list_nom_member[ "opt_in_audiences_" . str_replace( " ", "_", $list['name'] ) ] = sanitize_text_field( $_POST[ 'opt_in_audiences_' . str_replace( " ", "_", $list['name'] ) . '_checkbox' ] );
					}
				}
				update_option( 'pmproecaddon_plugin_list_opt_in_audiences', $list_nom_member );

				foreach ( $list_data as $list ) {
					if ( isset( $_POST[ 'nom_member_audiences_' . str_replace( " ", "_", $list['name'] ) . '_checkbox' ] ) ) {
						$list_opt_in_audiences[ "nom_member_audiences_" . str_replace( " ", "_", $list['name'] ) ] = sanitize_text_field( $_POST[ 'nom_member_audiences_' . str_replace( " ", "_", $list['name'] ) . '_checkbox' ] );
					}
				}

				update_option( 'pmproecaddon_plugin_list_nom_member', $list_opt_in_audiences );

				if ( isset( $_POST['require_unsubscribe_on_level_select'] ) ) {
					$require_unsubscribe_on_leve_select = sanitize_text_field( $_POST['require_unsubscribe_on_level_select'] );
					update_option( 'pmproecaddon_require_unsuscribe_on_level', $require_unsubscribe_on_leve_select );
				}

				if ( isset( $_POST['require_update_profile_select'] ) ) {
					$require_update_profile_select = sanitize_text_field( $_POST['require_update_profile_select'] );
					update_option( 'pmproecaddon_require_update_profile', $require_update_profile_select );
				}
			} else {
				echo '<p>No PMPro subscriptions found.</p>';
			}
		}
		//$require_double_opt_in =  sanitize_text_field($_POST["require_double_select"]);
		//update_option('pmproecaddon_require_double_opt_in', $require_double_opt_in);
		echo '<div class="updated"><p>Configuration saved successfully.</p></div>';
	}

	$user_ec     = get_option( 'pmproecaddon_plugin_user_ec', '' );
	$password_ec = get_option( 'pmproecaddon_plugin_pass_ec', '' );

	echo '<style>';
	echo '.checkbox-container {width: 300px;max-height: 100px;border: 1px solid #ccc;overflow-y: auto;background-color:white }';
	echo '.checkbox-item {display: block; margin: 5px 0;}';
	echo '</style>';

	echo '<div class="wrap">';

	echo '<h1>EmailChef Integration Options and Settings</h1>';

	echo '<h2>Subscribe users to one or more EmailChef audiences when they sign up for your site.</h2>';
	echo '<label>If you have Paid Membership Pro installed, you can subscribe members to one or more Emailchef audiences baased on their membership level or specify "Opt-in Audiences" that members can select at membership checkout.</label>';
	echo '<form method="post" action="">';
	echo '<h2>Login Emailchef</h2>';
	echo '<table border="0">';
	echo '<tr>';
	echo '<td><label>User</label></td>';
	echo '<td><input style="width:300px;margin-left: 25px" type="text" id="user_ec" name="user_ec" value="' . esc_attr( $user_ec ) . '"></td>';
	echo '</tr>';

	echo '<tr>';
	echo '<td><label>Password</label></td>';
	echo '<td><input style="width:300px;margin-left: 25px" type="password" id="pass_ec" name="pass_ec" value="' . esc_attr( $password_ec ) . '"></td>';

	echo '</tr>';
	echo '</table>';

	echo '</br>';

	if ( $user_ec != "" && $password_ec != "" ) {
		pmproecaddon_load_list_ec();
		$list_data              = get_option( 'pmproecaddon_list_data', '' );
		$list_opt_in_audiences  = get_option( 'pmproecaddon_plugin_list_opt_in_audiences', '' );
		$list_nom_member        = get_option( 'pmproecaddon_plugin_list_nom_member', '' );
		$unsubscribe_on_level   = get_option( 'pmproecaddon_require_unsuscribe_on_level', '' );
		$require_update_profile = get_option( 'pmproecaddon_require_update_profile', '' );

		echo '<h2>General configuration</h2>';
		echo '<table border="0">';
		echo '<tr>';
		echo '<td><label>Nom-member Audiences</label></td>';
		echo '<td>';
		echo '<div class="checkbox-container">';
		foreach ( $list_data as $list ) {
			$name_checkbox = "nom_member_audiences_" . esc_html( str_replace( " ", "_", $list['name'] ) );
			echo '<label class="checkbox-item">';
			if ( isset( $list_nom_member[ $name_checkbox ] ) && $list_nom_member[ $name_checkbox ] == $list['id'] ) {
				echo '<input style="margin-left: 5px" type="checkbox" name="nom_member_audiences_' . esc_html( str_replace( " ", "_", $list['name'] ) ) . '_checkbox" value="' . esc_html( $list['id'] ) . ' " checked> ' . esc_html( $list['name'] );
			} else {
				echo '<input style="margin-left: 5px" type="checkbox" name="nom_member_audiences_' . esc_html( str_replace( " ", "_", $list['name'] ) ) . '_checkbox" value="' . esc_html( $list['id'] ) . '"> ' . esc_html( $list['name'] );
			}
			echo '</label>';
		}
		echo '</div>';
		echo '</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td><label>Opt-in Audiences</label></td>';
		echo '<td>';
		echo '<div class="checkbox-container">';
		foreach ( $list_data as $list ) {
			$name_checkbox = "opt_in_audiences_" . esc_html( str_replace( " ", "_", $list['name'] ) );
			echo '<label class="checkbox-item">';
			if ( isset( $list_opt_in_audiences[ $name_checkbox ] ) && $list_opt_in_audiences[ $name_checkbox ] == $list['id'] ) {
				echo '<input style="margin-left: 5px"  type="checkbox" name="opt_in_audiences_' . esc_html( str_replace( " ", "_", $list['name'] ) ) . '_checkbox" value="' . esc_html( $list['id'] ) . ' " checked> ' . esc_html( $list['name'] );
			} else {
				echo '<input style="margin-left: 5px" type="checkbox" name="opt_in_audiences_' . esc_html( str_replace( " ", "_", $list['name'] ) ) . '_checkbox" value="' . esc_html( $list['id'] ) . '"> ' . esc_html( $list['name'] );
			}
			echo '</label>';
		}
		echo '</div>';
		echo '</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td><label>Unsubscribe on Level Change?</label></td>';
		echo '<td>';
		echo '<select style="width:200px" name="require_unsubscribe_on_level_select">';
		echo '<option value="yes_only_old_levels" ' . ( $unsubscribe_on_level == "yes_only_old_levels" ? 'selected' : '' ) . ' >Yes (Only old level audiences.)</option>';
		echo '<option value="yes_old_level" ' . ( $unsubscribe_on_level == "yes_old_level" ? 'selected' : '' ) . ' >Yes (Old level and opt-in audiences.)</option>';
		echo '<option value="no" ' . ( ! isset( $unsubscribe_on_level ) || $unsubscribe_on_level == "no" ? 'selected' : '' ) . ' >No</option>';
		echo '</select>';
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td></td>';
		echo '<td>';
		echo '<label>Recommended: Yes. However, if you manage multiple audiences in EmailChef unsubscribed from other audiences when they register on your site.</label>';
		echo '</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td><label>Update on Profile Save?</label></td>';
		echo '<td>';
		echo '<select style="width:200px" name="require_update_profile_select">';
		echo '<option value="yes" ' . ( $require_update_profile == "yes" ? 'selected' : '' ) . '>Yes</option>';
		echo '<option value="no" ' . ( ! isset( $require_update_profile ) || $require_update_profile == "no" ? 'selected' : '' ) . ' >No</option>';
		echo '</select>';
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td></td>';
		echo '<td>';
		echo "<label>Choosing 'No' will still update EmailChef when user's level is change, email is.</label>";
		echo '</td>';
		echo '</tr>';


		echo '</table>';

		$subscriptions = pmpro_getAllLevels();

		if ( ! empty( $subscriptions ) ) {
			echo '<h2>Membership Levels and Audiences</h2>';
			echo '<p>PMPro is installed.</p>';
			echo '<p>For each level below, choose the audience(s) that a new user should be subscribed to when they register.</p>';
			echo '<table border="0">';

			foreach ( $subscriptions as $subscription ) {
				echo '<tr>';
				echo '<td><label>' . esc_html( $subscription->name ) . '</label></td>';
				pmproecaddon_list_ec_plugin_display( str_replace( " ", "_", $subscription->name ) );
				echo '</tr>';
			}
			echo '</table>';
		} else {
			echo '<p>No PMPro subscriptions found.</p>';
		}
	}
	echo '</br>';
	wp_nonce_field( 'pmproecaddon-nonce', 'nonce' );
	echo '<input type="submit" name="plugin_save" class="button button-primary" value="Save Settings">';
	echo '<input style="margin-left: 10px" type="submit" name="plugin_refresh" class="button button-primary" value="Refresh">';
	echo '</div>';
	echo '</form>';
}


/** ACTION */
/**
 * Update Emailchef audiences when users checkout after usermeta is saved.
 *
 * @param int $user_id of user who checked out.
 */
function pmproecaddon_capture_subscription_pmpro( $user_id ) {
	try {
		if ( pmpro_hasMembershipLevel( null, $user_id ) ) {
			$current_membership = pmpro_getMembershipLevelForUser( $user_id );

			if ( $current_membership ) {
				$level_name  = str_replace( " ", "_", $current_membership->name );
				$list_config = get_option( 'pmproecaddon_plugin_list_config', '' );
				$user_email  = get_userdata( $user_id )->user_email;
				$user_login  = get_userdata( $user_id )->user_login;

				$list_data = get_option( 'pmproecaddon_list_data', '' );

				update_option( 'pmproecaddon_plugin_message', "LEVEL NAME: " . $level_name );;

				foreach ( $list_data as $list ) {


					if ( isset( $list_config[ $level_name . "_" . str_replace( " ", "_", $list['name'] ) ] ) ) {
						$id_list = $list_config[ $level_name . "_" . str_replace( " ", "_", $list['name'] ) ];
						update_option( 'pmproecaddon_plugin_message', "ID: " . $id_list );;

						pmproecaddon_suscribe_contact_ec( $id_list, $user_email, $user_login );
					}
				}
			} else {
				update_option( 'pmproecaddon_plugin_message', "current_membership: I don't enter" );
			}
		}
	} catch ( Exception $e ) {
		pmproecaddon_enqueue_script( $e->getMessage() );
	}
}

add_action( 'pmpro_after_checkout', 'pmproecaddon_capture_subscription_pmpro' );


/**
 * Dispaly additional opt-in list fields on checkout
 */
function pmproecaddon_additional_lists_on_checkout() {
	try {
		$list_opt_in_audiences = get_option( 'pmproecaddon_plugin_list_opt_in_audiences', '' );

		if ( count( $list_opt_in_audiences ) > 0 ) {
			$list_data = get_option( 'pmproecaddon_list_data', '' );
			echo '<h3>Join our mailing list.</h3>';

			echo '<div class="checkbox-container">';
			foreach ( $list_data as $list ) {
				$name_checkbox = "opt_in_audiences_" . esc_html( str_replace( " ", "_", $list['name'] ) );
				echo '<label class="checkbox-item">';
				if ( isset( $list_opt_in_audiences[ $name_checkbox ] ) && $list_opt_in_audiences[ $name_checkbox ] == $list['id'] ) {
					echo '<input type="checkbox" name="opt_in_audiences_' . esc_html( str_replace( " ", "_", $list['name'] ) ) . '_checkbox" value="' . esc_html( $list['id'] ) . ' "> ' . esc_html( $list['name'] );
				}
				echo '</label>';
			}
			echo '</div>';
		}
	} catch ( Exception $e ) {
		pmproecaddon_enqueue_script( $e->getMessage() );
	}
}

add_action( 'pmpro_checkout_after_tos_fields', 'pmproecaddon_additional_lists_on_checkout' );

/**
 * Update Emailchef opt-in audiences when users checkout after usermeta is saved.
 *
 * @param int $user_id of user who checked out.
 */
function pmproecaddon_pmpro_after_checkout( $user_id ) {
	try {
		if ( pmpro_hasMembershipLevel( null, $user_id ) ) {
			$current_membership = pmpro_getMembershipLevelForUser( $user_id );

			if ( $current_membership ) {
				$level_name  = $current_membership->name;
				$list_config = get_option( 'pmproecaddon_plugin_list_config', '' );
				$user_email  = get_userdata( $user_id )->user_email;
				$user_login  = get_userdata( $user_id )->user_login;

				$list_data = get_option( 'pmproecaddon_list_data', '' );

				foreach ( $list_data as $list ) {
					$name_checkbox = "opt_in_audiences_" . esc_html( str_replace( " ", "_", $list['name'] ) ) . "_checkbox";
					if ( isset( $_REQUEST[ $name_checkbox ] ) ) {
						$id_list = sanitize_text_field( $_REQUEST[ $name_checkbox ] );
						pmproecaddon_suscribe_contact_ec( $id_list, $user_email, $user_login );
					}
				}
			} else {
				update_option( 'pmproecaddon_plugin_message', "current_membership: I don't enter" );
			}
		}
	} catch ( Exception $e ) {
		pmproecaddon_enqueue_script( $e->getMessage() );
	}
}

add_action( 'pmpro_after_checkout', 'pmproecaddon_pmpro_after_checkout', 15 );

/*
	Add opt-in Lists to the user profile/edit user page.
*/
function pmproecaddon_add_custom_user_profile_fields( $user ) {
	try {
		$list_opt_in_audiences = get_option( 'pmproecaddon_plugin_list_opt_in_audiences', '' );

		if ( count( $list_opt_in_audiences ) > 0 ) {
			$list_data = get_option( 'pmproecaddon_list_data', '' );
			echo '<b><h4>Join our mailing list.</h4></b>';

			echo '<div class="checkbox-container">';
			foreach ( $list_data as $list ) {
				$name_checkbox = "opt_in_audiences_" . esc_html( str_replace( " ", "_", $list['name'] ) );
				echo '<label class="checkbox-item">';
				if ( isset( $list_opt_in_audiences[ $name_checkbox ] ) && $list_opt_in_audiences[ $name_checkbox ] == $list['id'] ) {
					echo '<input type="checkbox" name="opt_in_audiences_' . esc_html( str_replace( " ", "_", $list['name'] ) ) . '_checkbox" value="' . esc_html( $list['id'] ) . ' "> ' . esc_html( $list['name'] );
				}
				echo '</label>';
			}
			echo '</div>';
		}
	} catch ( Exception $e ) {
		pmproecaddon_enqueue_script( $e->getMessage() );
	}
}

add_action( 'show_user_profile', 'pmproecaddon_add_custom_user_profile_fields', 12 );
add_action( 'edit_user_profile', 'pmproecaddon_add_custom_user_profile_fields', 12 );
add_action( 'pmpro_show_user_profile', 'pmproecaddon_add_custom_user_profile_fields', 12 );

// Saving additional lists on profile save.
function pmproecaddon_save_custom_user_profile_fields( $user_id ) {
	try {
		if ( pmpro_hasMembershipLevel( null, $user_id ) ) {
			$current_membership = pmpro_getMembershipLevelForUser( $user_id );

			if ( $current_membership ) {
				$level_name  = $current_membership->name;
				$list_config = get_option( 'pmproecaddon_plugin_list_config', '' );
				$user_email  = get_userdata( $user_id )->user_email;
				$user_login  = get_userdata( $user_id )->user_login;

				$list_data = get_option( 'pmproecaddon_list_data', '' );

				foreach ( $list_data as $list ) {
					$name_checkbox = "opt_in_audiences_" . esc_html( str_replace( " ", "_", $list['name'] ) ) . "_checkbox";
					if ( isset( $_REQUEST[ $name_checkbox ] ) ) {
						$id_list = sanitize_text_field( $_REQUEST[ $name_checkbox ] );
						pmproecaddon_suscribe_contact_ec( $id_list, $user_email, $user_login );
					}
				}

				$pmproecaddon_require_update_profile = get_option( 'pmproecaddon_require_update_profile', '' );

				if ( isset( $pmproecaddon_require_update_profile ) && $pmproecaddon_require_update_profile == "yes" ) {
					foreach ( $list_data as $list ) {
						$id_list = $list['id'];

						$first_name = sanitize_text_field( $_REQUEST["first_name"] );
						$last_name  = sanitize_text_field( $_REQUEST["last_name"] );

						pmproecaddon_update_contact( $id_list, $user_email, $user_login, $first_name, $last_name );
					}
				}
			} else {
				update_option( 'pmproecaddon_plugin_message', "current_membership: I don't enter" );
			}
		}
	} catch ( Exception $e ) {
		pmproecaddon_enqueue_script( $e->getMessage() );
	}
}

add_action( 'personal_options_update', 'pmproecaddon_save_custom_user_profile_fields' );
add_action( 'edit_user_profile_update', 'pmproecaddon_save_custom_user_profile_fields' );
add_action( 'pmpro_personal_options_update', 'pmproecaddon_save_custom_user_profile_fields' );

/**
 * Subscribe users to lists when they register.
 *
 * @param int $user_id that was registered.
 */
function pmproecaddon_user_register( $user_id ) {
	try {
		if ( pmpro_hasMembershipLevel( null, $user_id ) ) {
			$current_membership = pmpro_getMembershipLevelForUser( $user_id );

			if ( $current_membership ) {
				$level_name  = $current_membership->name;
				$list_config = get_option( 'pmproecaddon_plugin_list_config', '' );
				$user_email  = get_userdata( $user_id )->user_email;
				$user_login  = get_userdata( $user_id )->user_login;

				$list_data = get_option( 'pmproecaddon_list_data', '' );

				foreach ( $list_data as $list ) {
					$name_checkbox = "opt_in_audiences_" . esc_html( str_replace( " ", "_", $list['name'] ) ) . "_checkbox";
					if ( isset( $_REQUEST[ $name_checkbox ] ) ) {
						$id_list = sanitize_text_field( $_REQUEST[ $name_checkbox ] );
						pmproecaddon_suscribe_contact_ec( $id_list, $user_email, $user_login );
					}
				}
			} else {
				update_option( 'pmproecaddon_plugin_message', "current_membership: I don't enter" );
			}
		}
	} catch ( Exception $e ) {
		pmproecaddon_enqueue_script( $e->getMessage() );
	}
}

add_action( 'user_register', 'pmproecaddon_user_register' );

/**
 * Subscribe new members (PMPro) when their membership level changes
 *
 * @param $level_id (int) -- ID of pmpro membership level.
 * @param $user_id (int) -- ID for user.
 */
function pmproecaddon_pmpro_after_change_membership_level( $level_id, $user_id ) {
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
		pmproecaddon_enqueue_script( $e->getMessage() );
	}
}

add_action( 'pmpro_after_change_membership_level', 'pmproecaddon_pmpro_after_change_membership_level', 15, 2 );

function pmproecaddon_enqueue_script( $text ) {
	if ( $text != "" ) {
		$js_url = plugins_url( '/js/pmproecaddon_script.js', __FILE__ );

		wp_enqueue_script( 'pmproecaddon_script', $js_url, array(), '1.0', true );

		$message = 'ERROR:' . $text;

		wp_localize_script( 'pmproecaddon_script', 'plugin_params', array(
			'message' => $message
		) );
	}
}

add_action( 'wp_enqueue_scripts', 'pmproecaddon_enqueue_script' );

function pmproecaddon_check_pmpro() {
	if ( ! function_exists( 'pmpro_hasMembershipLevel' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( 'This plugin requires Paid Memberships Pro. <a href="' . admin_url( 'plugins.php' ) . '">Please back to Plugins page</a>.' );
	}
}

// Usa l'hook di attivazione per il tuo plugin
register_activation_hook( __FILE__, 'pmproecaddon_check_pmpro' );

function pmproecaddon_load_textdomain() {
	load_plugin_textdomain( 'emailchef-add-on-for-pmp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'pmproecaddon_load_textdomain' );
