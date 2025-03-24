[![Emailchef](https://github.com/dueclic/emailchef-add-on-for-pmp/blob/main/.wordpress-org/assets/banner-772x250.png?raw=true)
](https://www.emailchef.com)

Enhance your membership website's functionality with the Paid Memberships Pro plugin, and seamlessly subscribe WordPress users and members to your Emailchef lists.

## Description

Once Paid Memberships Pro is installed, you gain the ability to designate distinct lists for different membership levels, along with optional lists that members can join during checkout or through their user profile. By integrating seamlessly, the user’s email and membership level details are automatically merged.

On the settings page, site administrators can easily choose specific lists for users and members, in addition to fine-tuning other features to enhance the overall experience.

## Additional Settings

* **Non-member Lists:** These lists are designated for users without a membership level. Once users acquire a membership level, they will be automatically removed from these lists.
* **Opt-in Lists:** These lists represent the subscription options available to users during the PMPro checkout process. Users can modify their selections later via their profile. It's important to note that lists designated as Opt-in Lists should not be simultaneously categorized as Non-member Lists or Level Lists.
* **Unsubscribe on Level Change?:** When set to 'No', users will retain their subscriptions to all lists, even if they lose a membership level. Conversely, selecting 'Yes' means users will be automatically unsubscribed from any level-specific lists they belong to upon losing that level, provided those lists are not also classified as Non-Member lists.
* **Update on Profile Save:** Selecting 'Yes' enables PMPro to refresh Emailchef list information each time a user's profile page is saved. If 'No' is chosen, PMPro will update Emailchef lists only when there's a change in the user's membership level, email, or selected opt-in lists.
* **Membership Levels and Lists:** These are the lists that users will automatically be subscribed to when they receive a membership level.

## Installation
This plugin works with Paid Memberships Pro installed.

### Download, Install and Activate!
1. Upload the `emailchef-add-on-for-pmp` directory to the `/wp-content/plugins/` directory of your site.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to Settings > PMPro Emailchef to proceed with setup.

## Configuration and Settings

**Enter your Emailchef email and password:**.

After entering your credentials user, continue with the setup by assigning User or Member Lists and reviewing the additional settings.

## Frequently Asked Questions

### I need help installing, configuring, or customizing the plugin.

Please visit [our dedicated page](https://emailchef.com/wordpress-paid-memberships-pro-emailchef-add-on/) for more documentation.

## Screenshots

1. General Settings for plugin, including the non-member lists opt-in rules, and unsubscribe rules.
2. Specific settings for Membership Levels and Lists.

## Changelog

* 1.8.0 Disconnection bugfix
* 1.7.0 Use Emailchef API Keys
* 1.6.0 Extended compatibility to WP 6.7
* 1.5.0 general i18n fixes
* 1.4.0 check PMPRO activation fix
* 1.3.0 i18n fixes
* 1.2.0 Small fixes
* 1.1.0 Added assets and screenshots
