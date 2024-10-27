<?php
/**
 * templates class
 *
 * @class    WIMJ_Templates
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_Templates', false ) ) :

/**
 * templates class.
 */
class WIMJ_Templates {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ), 0 );
	}
	/**
	 * sample func
	 */
	public function includes() {
		// classes
		include_once __DIR__ . '/class-wimj-templates-setup.php';
		include_once __DIR__ . '/class-wimj-templates-data.php';
		// include_once __DIR__ . '/class-wimj-template-categories.php';
	}
	
} // end - WIMJ_Templates

return new WIMJ_Templates();

endif; // end - class_exists

