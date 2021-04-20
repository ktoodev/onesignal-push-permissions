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
	 * Check if a given meta box exists (so we can confirm it's been registered/removed)
	 */
	private function meta_box_exists ($box_name, $screen = 'post') {
		global $wp_meta_boxes;

		return array_key_exists ($box_name, $wp_meta_boxes[ $screen ]['side']['default']) && !empty ($wp_meta_boxes[ $screen ]['side']['default'][$box_name]);
	}

	/**
	 * Register a fake meta box to stand in for the one from the official OneSignal plugin
	 */
	private function meta_box_setup () {
		// add a fake meta box with the same name as the OneSignal one and check it exists
		\add_meta_box( 'onesignal_notif_on_post', 'OneSignal Push Notifications', '__return_false', $current_screen = 'post', 'side' );
		$this->assertTrue ($this->meta_box_exists('onesignal_notif_on_post'));

		\set_current_screen('post');
	}


	/**
	 * Test that users without adequate permissions will NOT see the push notification meta box from OneSignal
	 */
	public function test_send_push_no_access () {

		// ensure that the test role does not have the necessary permission to see the meta box
		$this->role->remove_cap( PUSH_CAPABILITY);
		\wp_set_current_user( $this->current_user );

		$this->meta_box_setup();

		// the function to test - this function should remove the meta box for this user
		check_meta_box_permissions();

		// assert that the meta box has in fact been removed
		$this->assertFalse ($this->meta_box_exists('onesignal_notif_on_post'), 'Meta box is shown for users WITHOUT adequate permissions');
	}


	/**
	 * Test that users with the necessary permission DO see the push notification meta box from OneSignal
	 */
	public function test_send_push_access () {

		// add the necessary capability to use push notifications
		$this->role->add_cap( PUSH_CAPABILITY, true );
		\wp_set_current_user( $this->current_user );

		$this->meta_box_setup();

		// the function to test - this function should NOT remove the meta box for this user
		check_meta_box_permissions();

		// assert that the meta box has not been removed 
		$this->assertTrue ($this->meta_box_exists('onesignal_notif_on_post'), 'Meta box not shown users with adequate permissions');
	}
}
