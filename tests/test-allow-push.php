<?php
/**
 * Class SampleTest
 *
 * @package Unit_Test_3
 */

namespace OnesignalPushPermissions;


/**
 * Test restrictions on sending push notifications
 */
class AllowPush extends \WP_UnitTestCase {

	/**
	 * A fake user role for testing
	 */
	protected $role;

	/**
	 * The current user
	 */
	protected $current_user;

	/**
	 * Test fields to filter for notification
	 */
	protected $notification_fields = array (
	  'external_id' => 'aaaaaaaa-1111-aaaa-1111-aaaaaaaaaaaa',
	  'app_id' => '',
	  'headings' =>
	  array (
	    'en' => 'Heading test',
	  ),
	  'included_segments' =>
	  array (
	    0 => 'All',
	  ),
	  'isAnyWeb' => true,
	  'url' => 'https://example.com/post-url',
	  'contents' =>
	  array (
	    'en' => 'Contents test',
	  ),
	);

	/**
	* Set up each test method
	*/
	public function setUp() {
		parent::setUp();

		// create a fake role for testing
		$this->role = \add_role ('test-custom-role', 'test-custom-role', array ( PUSH_CAPABILITY => false ));

		// create a test user
		$this->current_user = $this->factory->user->create( array( 'role' => 'test-custom-role' ) );
	}

	/**
	 * Clean up after each method
	 */
	public function tearDown () {
		remove_role ('test-custom-role');
		\wp_set_current_user( 0 );
	}




	/**
	 * Test that a push notification cannot be sent by a user without permission
	 */
	public function test_send_push_no_access () {
		// user lacks permission to send notifications
		$this->set_current_user (false);

		// the various permutations of field data we could get from OneSignal (no 'do_send_notification' field, the field set to true, the field set to false)
		$field_tests = array (
			$this->notification_fields,
			array_merge ($this->notification_fields, array ('do_send_notification' => true)),
			array_merge ($this->notification_fields, array ('do_send_notification' => false)),
		);

		// all permutations of field data should result in the 'do_send_notification' field set to false
		foreach ($field_tests as $fields) {
			$filtered_fields = check_push_permissions ($fields, null, null, null);

			$this->assertArrayHasKey ('do_send_notification', $filtered_fields);
			$this->assertFalse ($filtered_fields['do_send_notification']);
		}
	}

	/**
	 * Test that an otherwise-allowed push notification CAN be sent with adequate permissions
	 */
	public function test_send_allowed_push_access () {
		// user is allowed to send push notifications
		$this->set_current_user (true);

		// possible permutations of allowed fields from OneSignal ('do_send_notification' field is either not set or set to true)
		$field_tests = array (
			$this->notification_fields,
			array_merge ($this->notification_fields, array ('do_send_notification' => true)),
		);

		// the 'do_send_notification' field should be either missing or true for all permutations of fields from OneSignal
		foreach ($field_tests as $fields) {
			$filtered_fields = check_push_permissions ($fields, null, null, null);

			$this->assertTrue (!isset($filtered_fields['do_send_notification']) || $filtered_fields['do_send_notification']);
		}
	}

	/**
	 * Test that an otherwise-DENIED push notification will NOT be sent by a user with push permissions
	 */
	public function test_send_forbidden_push_access () {
		// user can send push notifications
		$this->set_current_user (true);

		// the field data from OneSignal is already set to not send push ('do_send_notification' is set to false)
		$forbidden_fields = array_merge ($this->notification_fields, array ('do_send_notification' => false));

		$filtered_fields = check_push_permissions ($forbidden_fields, null, null, null);

		// the 'do_send_notification' should still be set to false
		$this->assertArrayHasKey ('do_send_notification', $filtered_fields);
		$this->assertFalse ($filtered_fields['do_send_notification']);
	}


	/**
	 * Test that users without adequate permissions will NOT see the push notification meta box from OneSignal
	 */
	public function test_show_push_controls_no_access () {

		// ensure that the test role does not have the necessary permission to see the meta box
		$this->set_current_user (false);

		$this->meta_box_setup();

		// the function to test - this function should remove the meta box for this user
		check_meta_box_permissions();

		// assert that the meta box has in fact been removed
		$this->assertFalse ($this->meta_box_exists('onesignal_notif_on_post'), 'Meta box is shown for users WITHOUT adequate permissions');
	}


	/**
	 * Test that users with the necessary permission DO see the push notification meta box from OneSignal
	 */
	public function test_show_push_controls_access () {

		// add the necessary capability to use push notifications
		$this->set_current_user (true);

		$this->meta_box_setup();

		// the function to test - this function should NOT remove the meta box for this user
		check_meta_box_permissions();

		// assert that the meta box has not been removed
		$this->assertTrue ($this->meta_box_exists('onesignal_notif_on_post'), 'Meta box not shown users with adequate permissions');
	}


	/**
	 * Set a current user who can or cannot send notifications
	 */
	protected function set_current_user ($can_send_notifications) {
		if ($can_send_notifications) {
			$this->role->add_cap( PUSH_CAPABILITY, true );
		}
		else {
			$this->role->remove_cap( PUSH_CAPABILITY);
		}
		\wp_set_current_user( $this->current_user );
	}

	/**
	 * Check if a given meta box exists (so we can confirm it's been registered/removed)
	 */
	protected function meta_box_exists ($box_name, $screen = 'post') {
		global $wp_meta_boxes;

		return array_key_exists ($box_name, $wp_meta_boxes[ $screen ]['side']['default']) && !empty ($wp_meta_boxes[ $screen ]['side']['default'][$box_name]);
	}

	/**
	 * Register a fake meta box to stand in for the one from the official OneSignal plugin
	 */
	protected function meta_box_setup () {
		// add a fake meta box with the same name as the OneSignal one and check it exists
		\add_meta_box( 'onesignal_notif_on_post', 'OneSignal Push Notifications', '__return_false', $current_screen = 'post', 'side' );
		$this->assertTrue ($this->meta_box_exists('onesignal_notif_on_post'));

		\set_current_screen('post');
	}
}
