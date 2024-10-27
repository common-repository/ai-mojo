<?php
/**
 * Image class
 *
 * @class    WIMJ_IMAGE
 * @package  includes
 * @version  0.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_IMAGE', false ) ) :

/**
 * Image class.
 */
class WIMJ_IMAGE {

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
		include_once __DIR__ . '/class-wimj-image-data.php';
	}
	
} // end - WIMJ_IMAGE

return new WIMJ_IMAGE();

endif; // end - class_exists

