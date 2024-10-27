<?php
/**
 * Wizard Core Class
 *
 * @class    WIMJ_WIZARD
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_WIZARD', false ) ) :

/**
 * Wizard class.
 */
class WIMJ_WIZARD {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_WIZARD/';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// include all related files
		add_action( 'init', array( $this, 'includes' ), 0 );
	}

	/**
	 * includes all api files
	 */
	public function includes() {
		// classes
		include_once __DIR__ . '/class-wizard-setup.php';
		include_once __DIR__ . '/class-wizard-data.php';

	}
	
} // end - WIMJ_WIZARD

return new WIMJ_WIZARD();

endif; // end - class_exists

