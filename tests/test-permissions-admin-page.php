<?php

namespace OnesignalPushPermissions;


/**
 * Test restrictions on sending push notifications
 */
class SettingsPage extends \WP_UnitTestCase {

	/**
	 * Test that the admin page does not show for users without permissions
	 *
	 * @expectedException WPDieException
	 */
	public function test_settings_page_no_access() {
		\wp_set_current_user( $this->factory->user->create( array( 'role' => 'editor' ) ) );
		Permissions_Admin_Page()->permissions_admin_content();
	}


	/**
	 * Test that the settings page does show for administrators
	 */
	public function test_settings_page_access() {
		$this->expectOutputRegex( '/<h1[^>]*>Push settings<\/h1>/' );
		\wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		Permissions_Admin_Page()->permissions_admin_content();
	}

	/**
	 * Test that users without permissions cannot save page
	 * 
	 * @expectedException WPDieException
	 * @expectedExceptionMessage You do not have sufficient permissions to access this page.
	 */
	public function test_save_permissions_no_access() {
		Permissions_Admin_Page()->save_permissions();
	}

	/**
	 * Test that editors cannot save page
	 * 
	 * @expectedException WPDieException
	 * @expectedExceptionMessage You do not have sufficient permissions to access this page.
	 */
	public function test_save_permissions_insufficient_access () {
		\wp_set_current_user( $this->factory->user->create( array( 'role' => 'editor' ) ) );
		Permissions_Admin_Page()->save_permissions();
	}

	/**
	 * Test that the page cannot be saved without a valid nonce
	 * 
	 * @expectedException WPDieException
	 * @expectedExceptionMessage You are not authorized to perform that action
	 */
	public function test_save_permissions_invalid_nonce () {
		\wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		Permissions_Admin_Page()->save_permissions();
	}
}
