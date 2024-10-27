<?php
/**
 * Quick Access REST API Endpoints Class
 *
 * @class    WIMJ_REST_API_CHAT_SETTINGS
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_REST_API_CHAT_SETTINGS', false ) ) :

/**
 * REST API endpoints class.
 */
class WIMJ_REST_API_CHAT_SETTINGS extends WIMJ_REST_API_Core {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_REST_API_CHAT_SETTINGS/';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// add REST API end routes	
		add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
	}

	/**
	 * Register endpoints
	 */
	public function register_endpoints() {
        // Register route
        register_rest_route( 'wimj/v1', 'get_chat_personas', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_chat_personas'),
            'permission_callback' => function ($request) {
                $args = $request->get_query_params();
                return ( wimj_who_can_access('rest_api/get_chat_personas',$args) ? true : false );;
            },
        ) );

	}

    /**
	 * get chat personas
     */
	public function get_chat_personas( $request ) {
        // do nonce verification
        if ( ! wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => esc_html__( 'Bad API Request' , WIMJ_SLUG ) ], 400 );
        }        
        return new WP_REST_Response( WIMJ_Chat_Data::get_personas() , 200 );
	}

} // end - WIMJ_REST_API_CHAT_SETTINGS

return new WIMJ_REST_API_CHAT_SETTINGS();

endif; // end - class_exists

