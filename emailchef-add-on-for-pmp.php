<?php
/**
 * Plugin Name: Emailchef Add On for Paid Memberships Pro
 * Plugin URI: https://emailchef.com/wordpress-paid-memberships-pro-emailchef-add-on/
 * Description: Sync your WordPress users and members with Emailchef lists.
 * Author: edisplayit
 * Author URI: https://www.emailchef.com
 * Version: 1.6.0
 * Text Domain: emailchef-add-on-for-pmp
 * Domain Path: /languages/
 * Requires at least: 6.0
 * Tested up to: 6.7
 * Requires PHP: 7.0
 * License: GPLv2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PMPROECADDON_DIR', dirname( __FILE__ ) );

require_once( "includes/api_call.php" );

/**
 * Add checkbox for plugin
 * Add checkbox for plugin
 *
 * @param $name (string) - The existing link array
 *
 */
function pmproecaddon_list_ec_plugin_display( $name ) {
	$list_data   = get_option( 'pmproecaddon_list_data', '' );
	$list_config = get_option( 'pmproecaddon_plugin_list_config', '' );
	?>
    <td>
        <div class="checkbox-container">
			<?php foreach ( $list_data as $list ) :
				$list_name = str_replace( " ", "_", $list['name'] );
				$name_checkbox = $name . '_' . $list_name . "_checkbox";
				$is_checked = isset( $list_config[ $name_checkbox ] ) && $list_config[ $name_checkbox ] == $list['id'];
				?>
                <label class="checkbox-item">
                    <input
                            class="input-checkbox"
                            type="checkbox"
                            name="<?php echo esc_attr( $name_checkbox ); ?>"
                            value="<?php echo esc_attr( $list['id'] ); ?>"
						<?php echo $is_checked ? 'checked' : ''; ?>
                    >
					<?php echo esc_html( $list['name'] ); ?>
                </label>
			<?php endforeach; ?>
        </div>
    </td>
	<?php
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
		'<a href="' . esc_url( admin_url( 'options-general.php?page=pmproecaddon_options' ) ) . '">' . esc_html__( 'Settings', 'emailchef-add-on-for-pmp' ) . '</a>',
	);

	return array_merge( $new_links, $links );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pmproecaddon_menu' );

add_action( 'admin_post_pmproecaddon_save_data', 'pmproecaddon_save_data' );

function pmproecaddon_reset_options() {
	if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pmproecaddon-reset-nonce'] ) ), 'pmproecaddon-reset-nonce' )
	     && isset( $_POST['plugin_reset'] )
	) {
		update_option( 'pmproecaddon_plugin_user_enabled', 'no' );
		delete_option( 'pmproecaddon_plugin_user_ec' );
		delete_option( 'pmproecaddon_plugin_pass_ec' );
		delete_option( 'pmproecaddon_plugin_list_config' );
		delete_option( 'pmproecaddon_plugin_list_opt_in_audiences' );
		delete_option( 'pmproecaddon_plugin_list_nom_member' );
		delete_option( 'pmproecaddon_require_unsuscribe_on_level' );
		delete_option( 'pmproecaddon_require_update_profile' );
		delete_option( 'pmproecaddon_list_data' ); // Assuming you want to reset this as well
		wp_safe_redirect( add_query_arg( 'pmproecaddon_msg', 'reset', wp_get_referer() ) );
		exit;
	}
}

add_action( 'admin_post_pmproecaddon_reset_options', 'pmproecaddon_reset_options' );

function pmproecaddon_save_data() {

	if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pmproecaddon-nonce'] ) ), 'pmproecaddon-nonce' )
	     && isset( $_POST['plugin_save'] )
	) {

		$user_ec     = sanitize_email( $_POST['user_ec'] );
		$password_ec = sanitize_text_field( $_POST['pass_ec'] );

		$logged_in = pmproecaddon_login( $user_ec, $password_ec );

		if ( ! $logged_in ) {
			update_option( 'pmproecaddon_plugin_user_enabled', 'no' );
			wp_safe_redirect( add_query_arg( 'pmproecaddon_msg', 'emailchef_credentials_wrong', wp_get_referer() ) );
			exit;
		}

		update_option( 'pmproecaddon_plugin_user_enabled', 'yes' );
		update_option( 'pmproecaddon_plugin_user_ec', $user_ec );
		update_option( 'pmproecaddon_plugin_pass_ec', $password_ec );

		$list_config           = array();
		$list_opt_in_audiences = array();
		$list_nom_member       = array();
		$list_data             = get_option( 'pmproecaddon_list_data', '' );

		if ( $list_data != null ) {
			$membership_levels = pmpro_getAllLevels();
			if ( ! empty( $membership_levels ) ) {
				foreach ( $membership_levels as $membership_level ) {
					foreach ( $list_data as $list ) {
						$checkbox_name = str_replace( " ", "_", $membership_level->name ) . '_' . str_replace( " ", "_", $list['name'] ) . '_checkbox';
						if ( isset( $_POST[ $checkbox_name ] ) ) {
							$list_config[ str_replace( " ", "_", $membership_level->name . "_" . str_replace( " ", "_", $list['name'] ) ) ] = sanitize_text_field( $_POST[ $checkbox_name ] );
						}
					}
				}
				update_option( 'pmproecaddon_plugin_list_config', $list_config );

				foreach ( $list_data as $list ) {
					$checkbox_name = 'opt_in_audiences_' . str_replace( " ", "_", $list['name'] ) . '_checkbox';
					if ( isset( $_POST[ $checkbox_name ] ) ) {
						$list_nom_member[ "opt_in_audiences_" . str_replace( " ", "_", $list['name'] ) ] = sanitize_text_field( $_POST[ $checkbox_name ] );
					}
				}
				update_option( 'pmproecaddon_plugin_list_opt_in_audiences', $list_nom_member );

				foreach ( $list_data as $list ) {
					$checkbox_name = 'nom_member_audiences_' . str_replace( " ", "_", $list['name'] ) . '_checkbox';
					if ( isset( $_POST[ $checkbox_name ] ) ) {
						$list_opt_in_audiences[ "nom_member_audiences_" . str_replace( " ", "_", $list['name'] ) ] = sanitize_text_field( $_POST[ $checkbox_name ] );
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
				wp_safe_redirect( add_query_arg( 'pmproecaddon_msg', 'no_membership_levels', wp_get_referer() ) );
				exit;
			}
		}
		wp_safe_redirect( add_query_arg( 'pmproecaddon_msg', 'success', wp_get_referer() ) );
		exit;
	}
}

/**
 * Add options to settings menu
 *
 *
 */
function pmproecaddon_options_page() {

	$user_ec     = get_option( 'pmproecaddon_plugin_user_ec', '' );
	$password_ec = get_option( 'pmproecaddon_plugin_pass_ec', '' );

	$pmproecaddon_msg = sanitize_text_field( wp_unslash( $_GET['pmproecaddon_msg'] ) );

	?>
    <div class="wrap pmproecaddon-options">

        <img src="<?php echo plugins_url('img/logo.png', __FILE__); ?>" alt="Emailchef">

        <h1><?php esc_html_e( 'Emailchef integration options and settings', 'emailchef-add-on-for-pmp' ); ?></h1>
        <h2><?php esc_html_e( 'Subscribe users to one or more Emailchef lists when they sign up for your site.', 'emailchef-add-on-for-pmp' ); ?></h2>
        <label><?php esc_html_e( 'If you have Paid Membership Pro installed, you can subscribe members to one or more Emailchef lists based on their membership level or specify "Opt-in Lists" that members can select at membership checkout.', 'emailchef-add-on-for-pmp' ); ?></label>

		<?php
		if ( ! empty( $pmproecaddon_msg ) ) {
			if ( 'success' === $pmproecaddon_msg ) {
				echo '<div class="updated"><p>' . esc_html__( 'Configuration saved successfully.', 'emailchef-add-on-for-pmp' ) . '</p></div>';
			} elseif ( 'reset' === $pmproecaddon_msg ) {
				echo '<div class="updated"><p>' . esc_html__( 'Configuration reset successfully.', 'emailchef-add-on-for-pmp' ) . '</p></div>';
			} elseif ( 'emailchef_credentials_wrong' === $pmproecaddon_msg ) {
				echo '<div class="error"><p>' . esc_html__( 'Emailchef credentials are wrong.', 'emailchef-add-on-for-pmp' ) . '</p></div>';
			} elseif ( 'no_membership_levels' === $pmproecaddon_msg ) {
				echo '<div class="error"><p>' . esc_html__( 'No available membership levels found in PMPro.', 'emailchef-add-on-for-pmp' ) . '</p></div>';
			}
		}
		?>

        <form method="post" action="<?php echo esc_url( admin_url( "admin-post.php" ) ); ?>">
            <h2><?php esc_html_e( 'Login Emailchef', 'emailchef-add-on-for-pmp' ); ?></h2>
            <table border="0">
                <tr>
                    <td><label for="user_ec"><?php esc_html_e( 'User', 'emailchef-add-on-for-pmp' ); ?></label></td>
                    <td><input class="input-text" type="text" id="user_ec" name="user_ec"
                               value="<?php echo esc_attr( $user_ec ); ?>"></td>
                </tr>
                <tr>
                    <td><label for="pass_ec"><?php esc_html_e( 'Password', 'emailchef-add-on-for-pmp' ); ?></label></td>
                    <td><input class="input-text" type="password" id="pass_ec" name="pass_ec"
                               value="<?php echo esc_attr( $password_ec ); ?>"></td>
                </tr>
            </table>
            <br>
			<?php
			if ( 'yes' === get_option( 'pmproecaddon_plugin_user_enabled', 'no' ) ) {
				pmproecaddon_load_list_ec();
				$list_data              = get_option( 'pmproecaddon_list_data', '' );
				$list_opt_in_audiences  = get_option( 'pmproecaddon_plugin_list_opt_in_audiences', '' );
				$list_nom_member        = get_option( 'pmproecaddon_plugin_list_nom_member', '' );
				$unsubscribe_on_level   = get_option( 'pmproecaddon_require_unsuscribe_on_level', '' );
				$require_update_profile = get_option( 'pmproecaddon_require_update_profile', '' );

				?>
                <h2><?php esc_html_e( 'General configuration', 'emailchef-add-on-for-pmp' ); ?></h2>
                <table border="0">
                    <tr>
                        <td>
                            <label for="nonmember_audiences"><?php esc_html_e( 'Non-member Lists', 'emailchef-add-on-for-pmp' ); ?></label>
                        </td>
                        <td>
                            <div class="checkbox-container">
								<?php foreach ( $list_data as $list ) :
									$list_name = str_replace( " ", "_", $list['name'] );
									$name_checkbox = "nom_member_audiences_" . $list_name . "_checkbox";
									$is_checked = isset( $list_nom_member[ $name_checkbox ] ) && $list_nom_member[ $name_checkbox ] == $list['id'];
									?>
                                    <label class="checkbox-item">
                                        <input id="nonmember_audiences" class="input-checkbox" type="checkbox"
                                               name="<?php echo esc_attr( $name_checkbox ); ?>"
                                               value="<?php echo esc_attr( $list['id'] ); ?>" <?php echo $is_checked ? 'checked' : ''; ?>>
										<?php echo esc_html( $list['name'] ); ?>
                                    </label>
								<?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="optin_audiences"><?php esc_html_e( 'Opt-in Lists', 'emailchef-add-on-for-pmp' ); ?></label>
                        </td>
                        <td>
                            <div class="checkbox-container">
								<?php foreach ( $list_data as $list ) :
									$list_name = str_replace( " ", "_", $list['name'] );
									$name_checkbox = "opt_in_audiences_" . $list_name . "_checkbox";
									$is_checked = isset( $list_opt_in_audiences[ $name_checkbox ] ) && $list_opt_in_audiences[ $name_checkbox ] == $list['id'];
									?>
                                    <label class="checkbox-item">
                                        <input id="optin_audiences" class="input-checkbox" type="checkbox"
                                               name="<?php echo esc_attr( $name_checkbox ); ?>"
                                               value="<?php echo esc_attr( $list['id'] ); ?>" <?php echo $is_checked ? 'checked' : ''; ?>>
										<?php echo esc_html( $list['name'] ); ?>
                                    </label>
								<?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="unsubscribe_on_level_change"><?php esc_html_e( 'Unsubscribe on Level Change?', 'emailchef-add-on-for-pmp' ); ?></label>
                        </td>
                        <td>
                            <select class="dropdown" id="unsubscribe_on_level_change"
                                    name="require_unsubscribe_on_level_select">
                                <option value="yes_only_old_levels" <?php selected( $unsubscribe_on_level, "yes_only_old_levels" ); ?>><?php esc_html_e( 'Yes (Only old level lists.)', 'emailchef-add-on-for-pmp' ); ?></option>
                                <option value="yes_old_level" <?php selected( $unsubscribe_on_level, "yes_old_level" ); ?>><?php esc_html_e( 'Yes (Old level and opt-in lists.)', 'emailchef-add-on-for-pmp' ); ?></option>
                                <option value="no" <?php selected( $unsubscribe_on_level, "no", true ); ?>><?php esc_html_e( 'No', 'emailchef-add-on-for-pmp' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <label><?php esc_html_e( 'Recommended: Yes. However, if you manage multiple lists in Emailchef, users will be unsubscribed from other lists when they register on your site.', 'emailchef-add-on-for-pmp' ); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="update_profile_save"><?php esc_html_e( 'Update on Profile Save?', 'emailchef-add-on-for-pmp' ); ?></label>
                        </td>
                        <td>
                            <select class="dropdown" id="update_profile_save" name="require_update_profile_select">
                                <option value="yes" <?php selected( $require_update_profile, "yes" ); ?>><?php esc_html_e( 'Yes', 'emailchef-add-on-for-pmp' ); ?></option>
                                <option value="no" <?php selected( $require_update_profile, "no", true ); ?>><?php esc_html_e( 'No', 'emailchef-add-on-for-pmp' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <label><?php esc_html_e( "Choosing 'No' will still update Emailchef when the user's level is changed.", 'emailchef-add-on-for-pmp' ); ?></label>
                        </td>
                    </tr>
                </table>

				<?php
				$subscriptions = pmpro_getAllLevels();
				if ( ! empty( $subscriptions ) ) {
					?>
                    <h2><?php esc_html_e( 'Membership Levels and Lists', 'emailchef-add-on-for-pmp' ); ?></h2>
                    <p><?php esc_html_e( 'PMPro is installed.', 'emailchef-add-on-for-pmp' ); ?></p>
                    <p><?php esc_html_e( 'For each level below, choose the list(s) that a new user should be subscribed to when they register.', 'emailchef-add-on-for-pmp' ); ?></p>
                    <table border="0">
						<?php foreach ( $subscriptions as $subscription ) : ?>
                            <tr>
                                <td><label><?php echo esc_html( $subscription->name ); ?></label></td>
								<?php pmproecaddon_list_ec_plugin_display( str_replace( " ", "_", $subscription->name ) ); ?>
                            </tr>
						<?php endforeach; ?>
                    </table>
					<?php
				} else {
					echo '<p>' . esc_html__( 'No available membership levels found in PMPro.', 'emailchef-add-on-for-pmp' ) . '</p>';
				}
			}
			?>
            <br>
			<?php wp_nonce_field( 'pmproecaddon-nonce', 'pmproecaddon-nonce' ); ?>
            <input type="hidden" name="action" value="pmproecaddon_save_data">
            <input type="submit" name="plugin_save" class="button button-primary"
                   value="<?php esc_attr_e( 'Save settings', 'emailchef-add-on-for-pmp' ); ?>">
        </form>


        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="pmproecaddon-mt-20">
            <input type="hidden" name="action" value="pmproecaddon_reset_options">
			<?php wp_nonce_field( 'pmproecaddon-reset-nonce', 'pmproecaddon-reset-nonce' ); ?>
            <input type="submit" name="plugin_reset" class="button button-secondary"
                   value="<?php esc_attr_e( 'Reset settings', 'emailchef-add-on-for-pmp' ); ?>"
                   onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to reset all the settings?', 'emailchef-add-on-for-pmp' ); ?>');">
        </form>
    </div>
	<?php
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
			?>
            <h3><?php esc_html_e( 'Join our mailing list.', 'emailchef-add-on-for-pmp' ); ?></h3>
            <div class="checkbox-container">
				<?php foreach ( $list_data as $list ) :
					$list_name = str_replace( " ", "_", $list['name'] );
					$name_checkbox = "opt_in_audiences_" . $list_name . "_checkbox";
					?>
                    <label class="checkbox-item">
						<?php if ( isset( $list_opt_in_audiences[ $name_checkbox ] ) && $list_opt_in_audiences[ $name_checkbox ] == $list['id'] ) : ?>
                            <input type="checkbox"
                                   name="<?php echo esc_attr( $name_checkbox ); ?>"
                                   value="<?php echo esc_attr( $list['id'] ); ?>">
							<?php echo esc_html( $list['name'] ); ?>
						<?php endif; ?>
                    </label>
				<?php endforeach; ?>
            </div>
			<?php
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
			?>
            <h4><?php esc_html_e( 'Join our mailing list.', 'emailchef-add-on-for-pmp' ); ?></h4>
            <div class="checkbox-container">
				<?php foreach ( $list_data as $list ) :
					$list_name = str_replace( " ", "_", $list['name'] );
					$name_checkbox = "opt_in_audiences_" . $list_name . "_checkbox";
					?>
                    <label class="checkbox-item">
						<?php if ( isset( $list_opt_in_audiences[ $name_checkbox ] ) && $list_opt_in_audiences[ $name_checkbox ] == $list['id'] ) : ?>
                            <input type="checkbox"
                                   name="<?php echo esc_attr( $name_checkbox ); ?>"
                                   value="<?php echo esc_attr( $list['id'] ); ?>">
							<?php echo esc_html( $list['name'] ); ?>
						<?php endif; ?>
                    </label>
				<?php endforeach; ?>
            </div>
			<?php
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

function pmproecaddon_admin_style() {
	wp_enqueue_style( 'admin_css', plugins_url( '/css/pmproecaddon.css', __FILE__ ), false, '1.0.0' );
}


add_action( 'admin_enqueue_scripts', 'pmproecaddon_admin_style' );

function pmproecaddon_check_pmpro() {
	if ( ! function_exists( 'pmpro_hasMembershipLevel' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		/* translators: %s: plugins page */
		wp_die( __( 'This plugin requires Paid Memberships Pro. <a href="%s">Please go back to the Plugins page</a>.', 'emailchef-add-on-for-pmp' ), esc_url( admin_url( 'plugins.php' ) ) );
	}
}


register_activation_hook( __FILE__, 'pmproecaddon_check_pmpro' );

function pmproecaddon_load_textdomain() {
	load_plugin_textdomain( 'emailchef-add-on-for-pmp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'pmproecaddon_load_textdomain' );
