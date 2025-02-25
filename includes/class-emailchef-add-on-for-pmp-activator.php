<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.emailchef.com
 * @since      1.0.0
 *
 * @package    Emailchef_Add_On_For_Pmp
 * @subpackage Emailchef_Add_On_For_Pmp/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Emailchef_Add_On_For_Pmp
 * @subpackage Emailchef_Add_On_For_Pmp/includes
 * @author     edisplayit <info@edisplay.it>
 */
class Emailchef_Add_On_For_Pmp_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if ( ! function_exists( 'pmpro_hasMembershipLevel' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			/* translators: %s: plugins page */
			wp_die( sprintf(__( 'This plugin requires Paid Memberships Pro. <a href="%s">Please go back to the Plugins page</a>.', 'emailchef-add-on-for-pmp' ), esc_url( admin_url( 'plugins.php' ) ) ) );
		}
	}

}
