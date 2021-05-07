<?php
/**
 * Plugin Name: Onesignal Push Permissions
 * Description: Add permissions controls for who can send push notifications
 * Version: 0.1
 * Author: David Purdy, KTOO
 * Author URI: https://www.ktoo.org
 * Text Domain: onesignal-push-permissions
 */

namespace OnesignalPushPermissions;
 
define (__NAMESPACE__ . '\PLUGIN_DIR', __DIR__);
define (__NAMESPACE__ . '\PLUGIN_FILE', __FILE__);


define (__NAMESPACE__ . '\PUSH_CAPABILITY', 'send_push_notifications');

require_once (PLUGIN_DIR . '/includes/class-permissions-admin-page.php');


/**
 * Check user permissions before sending notification and stop notification from sending if the current user is not authorized
 */
function check_push_permissions ($fields, $new_status, $old_status, $post) {
    if ( ! \current_user_can (PUSH_CAPABILITY)) {
        $fields['do_send_notification'] = false;
    }

    return $fields;
}
add_filter('onesignal_send_notification', __NAMESPACE__ . '\check_push_permissions', 10, 4);

/**
 * Removes the OneSignal meta box if the current user is not allowed to send notifications
 */
function check_meta_box_permissions () {
    if ( ! \current_user_can ( PUSH_CAPABILITY ) && function_exists('get_current_screen')) {
        \remove_meta_box ('onesignal_notif_on_post', get_current_screen(), 'side');
    }
}
add_action ( 'add_meta_boxes', __NAMESPACE__ . '\check_meta_box_permissions', 20);