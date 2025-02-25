<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.emailchef.com
 * @since      1.0.0
 *
 * @package    Emailchef_Add_On_For_Pmp
 * @subpackage Emailchef_Add_On_For_Pmp/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Emailchef_Add_On_For_Pmp
 * @subpackage Emailchef_Add_On_For_Pmp/includes
 * @author     edisplayit <info@edisplay.it>
 */
class Emailchef_Add_On_For_Pmp_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		delete_option( 'pmproecaddon_plugin_user_enabled' );
		delete_option( 'pmproecaddon_settings' );
		delete_option( 'pmproecaddon_plugin_list_config' );
		delete_option( 'pmproecaddon_plugin_list_opt_in_audiences' );
		delete_option( 'pmproecaddon_plugin_list_non_member' );
		delete_option( 'pmproecaddon_require_unsuscribe_on_level' );
		delete_option( 'pmproecaddon_require_update_profile' );
	}

}
