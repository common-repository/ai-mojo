<?php
/**
 * sample class
 *
 * @class    WIMJ_Sample
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_Sample', false ) ) :

/**
 * Admin sample class.
 */
class WIMJ_Sample {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_Sample/';

	/**
	 * sample func
	 */
	public function sample_func() {
		// include_once __DIR__ . '/wc-admin-functions.php';


	}
	
} // end - WIMJ_Sample

endif; // end - class_exists

