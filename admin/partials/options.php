<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.emailchef.com
 * @since      1.0.0
 *
 * @package    Emailchef_Add_On_For_Pmp
 * @subpackage Emailchef_Add_On_For_Pmp/admin/partials
 */

$pmproecaddon_msg = sanitize_text_field( wp_unslash( $_GET['pmproecaddon_msg'] ) );

?>
    <div class="wrap pmproecaddon-options">

        <img src="<?php echo plugins_url( 'img/logo.png', __FILE__ ); ?>" alt="Emailchef">

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


        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
              class="pmproecaddon-mt-20">
            <input type="hidden" name="action" value="pmproecaddon_reset_options">
			<?php wp_nonce_field( 'pmproecaddon-reset-nonce', 'pmproecaddon-reset-nonce' ); ?>
            <input type="submit" name="plugin_reset" class="button button-secondary"
                   value="<?php esc_attr_e( 'Reset settings', 'emailchef-add-on-for-pmp' ); ?>"
                   onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to reset all the settings?', 'emailchef-add-on-for-pmp' ); ?>');">
        </form>
    </div>
<?php
