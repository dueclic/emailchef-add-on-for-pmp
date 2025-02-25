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

$account = $api->account();
$lists   = $api->lists( array(
	'hidden'    => '0',
	'limit'     => '999',
	'offset'    => '0',
	'orderby'   => 'cd',
	'ordertype' => 'd',
) );

$list_opt_in_audiences  = get_option( 'pmproecaddon_plugin_list_opt_in_audiences', '' );
$list_non_member        = get_option( 'pmproecaddon_plugin_list_non_member', '' );
$unsubscribe_on_level   = get_option( 'pmproecaddon_require_unsuscribe_on_level', '' );
$require_update_profile = get_option( 'pmproecaddon_require_update_profile', '' );


$pmproecaddon_msg = sanitize_text_field( wp_unslash( $_GET['pmproecaddon_msg'] ) );

?>

    <div class="ecf-main-container">
        <div class="ecf-main-account">
            <div class="ecf-forms-logo">
                <img src="<?php echo plugins_url( '/admin/img/logo-compact.svg', EMAILCHEF_PLUGIN_FILE_PATH ); ?>"
                     alt="">
                <div class="ecf-account-status">
                    <div><?php _e( "Account connected", "emailchef-add-on-for-pmp" ); ?></div>
                    <div class="ecf-account-connected"></div>
                </div>
            </div>
            <div class="ecf-account-info">
                <span class="flex-grow-1 truncate"
                      title="<?php echo $account['email']; ?>"><strong><?php echo $account['email']; ?></strong></span>
                <span>
                <a id="emailchef-disconnect" data-nonce="<?php echo wp_create_nonce( 'pmproecaddon_disconnect' ); ?>"
                   class="ecf-account-disconnect"
                   title="<?php _e( "Disconnect account", "emailchef-add-on-for-pmp" ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path
                                d="M280 24c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 240c0 13.3 10.7 24 24 24s24-10.7 24-24l0-240zM134.2 107.3c10.7-7.9 12.9-22.9 5.1-33.6s-22.9-12.9-33.6-5.1C46.5 112.3 8 182.7 8 262C8 394.6 115.5 502 248 502s240-107.5 240-240c0-79.3-38.5-149.7-97.8-193.3c-10.7-7.9-25.7-5.6-33.6 5.1s-5.6 25.7 5.1 33.6c47.5 35 78.2 91.2 78.2 154.7c0 106-86 192-192 192S56 368 56 262c0-63.4 30.7-119.7 78.2-154.7z"></path></svg>
                </a>
            </span>
            </div>
        </div>
        <div class="ecf-main-content">
            <div class="ecf-main-forms">
                <div class="wrap pmproecaddon-options">

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
                        <h2><?php esc_html_e( 'General configuration', 'emailchef-add-on-for-pmp' ); ?></h2>
                        <table border="0">
                            <tr>
                                <td>
                                    <label for="nonmember_audiences"><?php esc_html_e( 'Non-member Lists', 'emailchef-add-on-for-pmp' ); ?></label>
                                </td>
                                <td>


	                                <?php
	                                pmproecaddon_list_match_display(
		                                $lists,
		                                "non_member_audiences",
		                                $list_non_member
	                                )
	                                ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="optin_audiences"><?php esc_html_e( 'Opt-in Lists', 'emailchef-add-on-for-pmp' ); ?></label>
                                </td>
                                <td>

                                    <?php
                                    pmproecaddon_list_match_display(
	                                    $lists,
	                                    "opt_in_audiences",
	                                    $list_opt_in_audiences
                                    )
                                    ?>
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
                                    <select class="dropdown" id="update_profile_save"
                                            name="require_update_profile_select">
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
                                        <td>
										<?php pmproecaddon_list_match_display(
											$lists,
											$subscription->name,
											get_option( 'pmproecaddon_plugin_list_config', '' )
										); ?>
                                        </td>
                                    </tr>
								<?php endforeach; ?>
                            </table>
							<?php
						} else {
							echo '<p>' . esc_html__( 'No available membership levels found in PMPro.', 'emailchef-add-on-for-pmp' ) . '</p>';
						}
						?>
                        <br>
						<?php wp_nonce_field( 'pmproecaddon-nonce', 'pmproecaddon-nonce' ); ?>
                        <input type="hidden" name="action" value="pmproecaddon_save_data">
                        <input type="submit" name="plugin_save" class="button button-primary"
                               value="<?php esc_attr_e( 'Save settings', 'emailchef-add-on-for-pmp' ); ?>">
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
