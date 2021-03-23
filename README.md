# OneSignal Push Permissions

Works with OneSignal to add settings for which WordPress user roles are allowed to send push notifications.

**Please note, this is still an early pre-release of this plugin, and it is still being tested. Features may change and break (or not work to begin with!) or it could conflict with your site in unexpected ways. Not intended for installation on production sites!**

## Install

The easiest way to install is to download [the latest release](https://github.com/ktoodev/onesignal-push-permissions/releases/).

## Setup 

The plugin extends the [official OneSignal plugin](https://wordpress.org/plugins/onesignal-free-web-push-notifications/) to add an interface for choosing which WordPress user roles can send push notifications. 

After activation, it adds a new "Permissions" admin page under the "OneSignal Push" menu. On that page is a list of user roles supported on the WordPress site where it is installed. Only users with roles selected on this settings page will be able to send notifications from within WordPress using the OneSignal plugin. 