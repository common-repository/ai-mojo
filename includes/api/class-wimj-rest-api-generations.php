<?php
/**
 * Content Generation REST API Endpoints Class
 *
 * @class    WIMJ_REST_API_GENERATIONS_ENDPOINTS
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_REST_API_GENERATIONS_ENDPOINTS', false ) ) :

/**
 * REST API endpoints class.
 */
class WIMJ_REST_API_GENERATIONS_ENDPOINTS extends WIMJ_REST_API_Core {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_REST_API_GENERATIONS_ENDPOINTS/';

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
        register_rest_route( 'wimj/v1', 'content_generation', array(
            'methods' => 'POST',
            'callback' => array($this, 'do_generation'),
            'permission_callback' => function ($request) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access('rest_api/do_generation',$args) ? true : false );;
            },
        ) );

	}

    /**
	 * Do Content Generation
	 */
	public function do_generation( $request ) {

        // do nonce verification
        if ( ! wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => esc_html__( 'Bad API Request' , WIMJ_SLUG ) ], 400 );
        }

        // get body params
        $body_params = $request->get_json_params();
        $completions = wimj_content_generation()->completion( $body_params );

        if ( isset( $completions['status'] ) && !empty( $completions['status'] ) && $completions['status'] == 'error' && isset( $completions['message'] ) && !empty( $completions['message'] ) ) {
            return new WP_REST_Response( (object) [ 'code' => $completions['status'], 'message' => $completions['message'] ], 400 );
        } else {
            // return completion
            return new WP_REST_Response( $completions, 200 );
        }
	}

} // end - WIMJ_REST_API_GENERATIONS_ENDPOINTS

return new WIMJ_REST_API_GENERATIONS_ENDPOINTS();

endif; // end - class_exists

