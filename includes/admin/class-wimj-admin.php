<?php
/**
 * Admin Class
 *
 * @class    WIMJ_Admin
 * @package  includes/admin
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Admin core class.
 */
class WIMJ_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ), 0 );
	}

	/**
	 * includes all admin files
	 */
	public function includes() {
		// functions
		include_once __DIR__ . '/wimj-admin-functions.php';
		
		// classes
		include_once __DIR__ . '/class-wimj-admin-settings.php';
		
		if ( is_admin() ) {
			include_once __DIR__ . '/class-wimj-admin-settings-page.php';
		} // end - is_admin

	}
}

return new WIMJ_Admin();