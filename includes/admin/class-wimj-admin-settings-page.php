<?php
/**
 * Settings page for the plugin
 *
 * @class    WIMJ_Admin_Settings_Page
 * @package  includes/admin
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_Admin_Settings_Page', false ) ) :

/**
 * Admin Settings Page contruct class.
 */
class WIMJ_Admin_Settings_Page {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_Admin_Settings_Page/';

	/**
	 * Constructor.
	 */
	public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page') , 15 );
		
	}

	/**
	 * add settings page
	 */
	public function add_settings_page() {
        add_menu_page( 		        	
			esc_html__( 'AI Mojo' , WIMJ_SLUG ) , // page_title
			esc_html__( 'AI Mojo' , WIMJ_SLUG ) , // menu_title
			wimj_who_can_access('settings_page') , // capability
			'wpaimojo', // menu_slug
			array( $this, 'render_settings_page'), // callback function
			'dashicons-games' // icon
		);
	}

	/**
	 * render settings page
	 */
	public function render_settings_page() {
		if ( !current_user_can( wimj_who_can_access('settings_page') ) )
			wp_die( esc_html__( 'Cheatin&#8217; uh?' ) );
        ?>
        <div id="wpaimojo">
			<!-- Put loading here -->
        </div><!-- #wpaimojo -->
        <?php
	}

	/**
	 * sample func
	 */
	public function sample_func() {
		


	}
	
} // end - WIMJ_Admin_Settings_Page

return new WIMJ_Admin_Settings_Page();

endif; // end - class_exists

