<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.emailchef.com
 * @since             1.0.0
 * @package           Emailchef_Add_On_For_Pmp
 *
 * @wordpress-plugin
 * Plugin Name:       Emailchef Add On for Paid Memberships Pro
 * Plugin URI:        https://www.emailchef.com/wordpress-paid-memberships-pro-emailchef-add-on/
 * Description:       Sync your WordPress users and members with Emailchef lists.
 * Version:           1.8.0
 * Author:            edisplayit
 * Author URI:        https://www.emailchef.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       emailchef-add-on-for-pmp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'EMAILCHEF_ADD_ON_FOR_PMP_VERSION', '1.8.0' );
define( 'EMAILCHEF_ADD_ON_FOR_PMP_PATH', __FILE__ );

require_once plugin_dir_path(__FILE__) . 'includes/class-emailchef-add-on-for-pmp-api-base.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-emailchef-add-on-for-pmp-api.php';
require_once plugin_dir_path( __FILE__ ) . 'common-api.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-emailchef-add-on-for-pmp-activator.php
 */
function activate_emailchef_add_on_for_pmp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-emailchef-add-on-for-pmp-activator.php';
	Emailchef_Add_On_For_Pmp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-emailchef-add-on-for-pmp-deactivator.php
 */
function deactivate_emailchef_add_on_for_pmp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-emailchef-add-on-for-pmp-deactivator.php';
	Emailchef_Add_On_For_Pmp_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_emailchef_add_on_for_pmp' );
register_deactivation_hook( __FILE__, 'deactivate_emailchef_add_on_for_pmp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-emailchef-add-on-for-pmp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_emailchef_add_on_for_pmp() {

	$plugin = new Emailchef_Add_On_For_Pmp();
	$plugin->run();

}
run_emailchef_add_on_for_pmp();
