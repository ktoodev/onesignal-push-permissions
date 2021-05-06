<?php
/**
 * A settings page under OneSignal to set which user roles have the capability to send push notifications
 */

namespace OnesignalPushPermissions;

new Permissions_Admin_Page();

/**
 * Add a settings page to the OneSignal settings area to administer tags
*/
class Permissions_Admin_Page {

    /**
     * Hook for the sub page
     */
    private $hook;

    /**
     * Capability to administer tags
     */
    private $capability = 'manage_options';


    /**
     * Construct the page
     */
    function __construct () {
        \add_action( 'admin_menu', array ($this, 'permissions_admin_page' ), 25);
        \add_action ('admin_post_push_notifications_save_permissions', array ($this, 'save_permissions'));
    }


    /**
     * Add a page for setting push permissions under the OneSignal page
     */
    function permissions_admin_page() {
        $this->hook = add_submenu_page(
            'onesignal-push',               // parent
            'Push notification permissions',           // title
            'Permissions',                // menu title
            $this->capability,               // capability
            'push-notification-permissions',  // slug
            array ($this, 'permissions_admin_content')       // content
        );
    }


    /**
     * Output the content for the permissions admin page
     */
    function permissions_admin_content () {

        // exit if the user has insufficient permissions
        if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'onesignal-push-permissions' ) );
		}

        // output page content
        ?>
        <h1 class="title"><?php esc_html_e('Push settings', 'onesignal-push-permissions'); ?></h1>

        <p><?php esc_html_e('All users with roles checked below will be able to send push notifications from the post editing screen. Users without any of the selected roles will not be able to send push notifications from WordPress using the OneSignal plugin.', 'onesignal-push-permissions'); ?></p>
        <p><?php esc_html_e('These settings do not control who can send notifications from outside WordPress (e.g., the OneSignal dashboard).', 'onesignal-push-permissions'); ?></p>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

            <input type="hidden" name="action" value="push_notifications_save_permissions">


            <table class="form-table" role="presentation">

                <tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e('Allow to send push notifications', 'onesignal-push-permissions'); ?></th>
                        <td>
                            <fieldset>
                                <?php
                                // output a checkbox for each role
                                $all_roles = wp_roles();
                                foreach ($all_roles->role_names as $slug => $name):
                                    $checked = $all_roles->role_objects[$slug]->has_cap (PUSH_CAPABILITY) ? ' checked ' : '';
                                    ?>
                                    <label><input type="checkbox" name="onesignal-push-notification-roles[]" value="<?php echo $slug; ?>" <?php echo $checked; ?>><?php echo $name; ?></label><br />
                                <? endforeach; ?>
                            </fieldset>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php \wp_nonce_field( 'save_push_permissions_options', 'push_permissions_admin_nonce' ); ?>
            <?php \submit_button( __( 'Save push permissions', 'onesignal-push-permissions' ), 'primary' ); ?>

        </form>
        <?php
    }


    /**
     * Saves permission settings
     */
    function save_permissions () {

        // check user capability
        if ( ! \current_user_can( $this->capability ) ) {
            \wp_die( \esc_html__( 'You do not have sufficient permissions to access this page.', 'onesignal-push-permissions' ) );
        }

        // check nonce
        if ( ! isset( $_POST['push_permissions_admin_nonce'] ) || ! \wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_POST['push_permissions_admin_nonce'] ) ), 'save_push_permissions_options' ) ) {
            \wp_die( \esc_html__('You are not authorized to perform that action', 'onesignal-push-permissions' ) );
        }

        // if notification roles were sent
        $new_roles = array();
        if (isset ($_POST['onesignal-push-notification-roles']) && is_array  ($_POST['onesignal-push-notification-roles']) ) {
          $new_roles = $_POST['onesignal-push-notification-roles'];
        }

        // check all roles
        foreach (\wp_roles()->role_objects as $slug => $role) {
            // if the role was saved from the form, add the push capability to it
            if (in_array ($slug, $new_roles)) {
                $role->add_cap( PUSH_CAPABILITY, true );
            }
            // otherwise remove the push capability
            else {
                $role->remove_cap( PUSH_CAPABILITY );
            }
        }

        return \wp_safe_redirect (\admin_url ('admin.php?page=push-notification-permissions&saved=1'));
    }
}
