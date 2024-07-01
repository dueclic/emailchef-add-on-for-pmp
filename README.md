=== Emailchef Add On for Paid Memberships Pro ===
Contributors: edisplayit, dueclic
Donate link: https://www.dueclic.com
Tags: paid memberships pro, pmpro, emaiclhef, email marketing
Requires at least: 6.0
Tested up to: 6.5
Stable tag: 1.0
Requires PHP: 7.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Plugin URI: https://emailchef.com/it/add-on-emailchef-per-wordpress-paid-memberships-pro/

Add users and members to EmailChef audiences based on their membership level and allow members to opt-in to specific audiences.

== Description ==

Subscribe WordPress users and members to your EmailChef audiences.

This plugin offers extended functionality for [membership websites using the Paid Memberships Pro plugin](https://wordpress.org/plugins/paid-memberships-pro/) available for free in the WordPress plugin repository.

With Paid Memberships Pro installed, you can specify unique audiences for each membership level, as well as opt-in audiences that a member can join as part of checkout or by editing their user profile. By default, the integration will merge the user's email address and membership level information.

The settings page allows the site admin to specify which audience lists to assign users and members to plus additional features  you may wish to adjust. The first step is to connect your website to EmailChef using your email and password.

= Additional Settings =

* **Non-member Audiences:** These are the audiences that users will be added to if they do not have a membership level. They will also be removed from these audiences when they gain a membership level (assuming the audiences are not also set in the “Membership Levels and Audiences” option for their new level).
* **Opt-in Audiences:** These are the audiences that users will have the option to subscribe to during the PMPro checkout process. Users are later able to update their choice from their profile. Audiences set as Opt-in Audiences should not also be set as a Non-member Audience nor a Level Audience.
* **Unsubscribe on Level Change?:** If set to “No”, users will not be automatically unsubscribed from any audiences when they lose a membership level. If set to “Yes (Only old level audiences.)”, users will be unsubscribed from any level audiences they are subscribed to when they lose that level, assuming that audience is not a Non-Member audience as well. If set to “Yes (Old level and opt-in audiences.)”, users will also be unsubscribed from opt-in audiences when they lose their membership level (though they can re-subscribe by updating the setting on their profile).
* **Update on Profile Save:** If set to “Yes”, PMPro will update EmailChef audiences whenever a user’s profile page is saved. If set to “No”, PMPro will only update EmailChef when a user’s membership level is changed, email is changed, or chosen opt-in audiences are changed.
* **Log API Calls?:** If set to “Yes”, API calls to EmailChef will be logged in the `/pmpro-emailchef/logs` folder.
* **Membership Levels and Audiences:** These are the audiences that users will automatically be subscribed to when they receive a membership level.

== Installation ==
This plugin works with and without Paid Memberships Pro installed.

= Download, Install and Activate! =
1. Upload the `emailchef-add-on-for-pmp` directory to the `/wp-content/plugins/` directory of your site.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Navigate to Settings > PMPro EmailChef to proceed with setup.

= Configuration and Settings =

**Enter your EmailChef email and password:**.

After entering your credentials user, continue with the setup by assigning User or Member Audiences and reviewing the additional settings.

For full documentation on all settings, please visit the [EmailChef Integration Add On documentation page at Paid Memberships Pro]().

Several action and filter hooks are available for developers that need to customize specific aspects of the integration. [Please explore the plugin's action and filter hooks here]().

== Frequently Asked Questions ==

= I need help installing, configuring, or customizing the plugin. =

Please visit [our support site at https://www.paidmembershipspro.com](http://www.paidmembershipspro.com/) for more documentation and our support forums.

== Screenshots ==

1. General Settings for plugin, including the non-member audiences opt-in rules, and unsubscribe rules.
2. Specific settings for Membership Levels and Audiences.

== Changelog ==
= .1.1.0 =
* First logged release with a readme.

== Upgrade Notice ==
= 1.1.0 =
* Incorporation of readme file
