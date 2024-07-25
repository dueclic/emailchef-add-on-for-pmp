[![Emailchef](https://github.com/dueclic/emailchef-add-on-for-pmp/blob/main/.wordpress-org/assets/banner-772x250.png?raw=true)
](https://www.emailchef.com)

Add users and members to Emailchef lists based on their membership level and allow members to opt-in to specific lists.

## Description

Subscribe WordPress users and members to your Emailchef lists.

This plugin offers extended functionality for [membership websites using the Paid Memberships Pro plugin](https://wordpress.org/plugins/paid-memberships-pro/) available for free in the WordPress plugin repository.

With Paid Memberships Pro installed, you can specify unique lists for each membership level, as well as opt-in lists that a member can join as part of checkout or by editing their user profile. By default, the integration will merge the user's email address and membership level information.

The settings page allows the site admin to specify which lists to assign users and members to plus additional features  you may wish to adjust. The first step is to connect your website to Emailchef using your email and password.

## Additional Settings

* **Non-member Lists:** These are the lists that users will be added to if they do not have a membership level. They will also be removed from these lists when they gain a membership level (assuming the lists are not also set in the “Membership Levels and Lists” option for their new level).
* **Opt-in Lists:** These are the lists that users will have the option to subscribe to during the PMPro checkout process. Users are later able to update their choice from their profile. Lists set as Opt-in Lists should not also be set as a Non-member Audience nor a Level Audience.
* **Unsubscribe on Level Change?:** If set to “No”, users will not be automatically unsubscribed from any lists when they lose a membership level. If set to “Yes (Only old level lists.)”, users will be unsubscribed from any level lists they are subscribed to when they lose that level, assuming that audience is not a Non-Member audience as well. If set to “Yes (Old level and opt-in lists.)”, users will also be unsubscribed from opt-in lists when they lose their membership level (though they can re-subscribe by updating the setting on their profile).
* **Update on Profile Save:** If set to “Yes”, PMPro will update Emailchef lists whenever a user’s profile page is saved. If set to “No”, PMPro will only update Emailchef when a user’s membership level is changed, email is changed, or chosen opt-in lists are changed.
* **Membership Levels and Lists:** These are the lists that users will automatically be subscribed to when they receive a membership level.

## Installation
This plugin works with and without Paid Memberships Pro installed.

### Download, Install and Activate!
1. Upload the `emailchef-add-on-for-pmp` directory to the `/wp-content/plugins/` directory of your site.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to Settings > PMPro Emailchef to proceed with setup.

## Configuration and Settings

**Enter your Emailchef email and password:**.

After entering your credentials user, continue with the setup by assigning User or Member Lists and reviewing the additional settings.

For full documentation on all settings, please visit the [Emailchef Integration Add On documentation page at Paid Memberships Pro]().

Several action and filter hooks are available for developers that need to customize specific aspects of the integration. [Please explore the plugin's action and filter hooks here]().

## Frequently Asked Questions

### I need help installing, configuring, or customizing the plugin.

Please visit [our support site at https://www.paidmembershipspro.com](http://www.paidmembershipspro.com/) for more documentation and our support forums.

## Screenshots

1. General Settings for plugin, including the non-member lists opt-in rules, and unsubscribe rules.
2. Specific settings for Membership Levels and Lists.

## Changelog

* 1.5.0 general i18n fixes
* 1.4.0 check PMPRO activation fix
* 1.3.0 i18n fixes
* 1.2.0 Small fixes
* 1.1.0 Added assets and screenshots
