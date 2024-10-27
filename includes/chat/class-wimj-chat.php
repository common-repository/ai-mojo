<?php
/**
 * Chat Core Class
 *
 * @class    WIMJ_CHAT
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_CHAT', false ) ) :

/**
 * Chat class.
 */
class WIMJ_CHAT {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_CHAT/';

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
		include_once __DIR__ . '/class-wimj-chat-data.php';

	}
	
} // end - WIMJ_CHAT

return new WIMJ_CHAT();

endif; // end - class_exists

