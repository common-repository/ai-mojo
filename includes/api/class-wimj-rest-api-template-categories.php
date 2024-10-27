<?php
/**
 * AI Engines REST API Endpoints Class
 *
 * @class    WIMJ_REST_API_TEMPLATES_ENDPOINTS
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_REST_API_TEMPLATES_ENDPOINTS', false ) ) :

/**
 * REST API endpoints class.
 */
class WIMJ_REST_API_TEMPLATES_ENDPOINTS {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_REST_API_TEMPLATES_ENDPOINTS/';

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

        register_rest_route( 'wimj/v1', 'template_categories', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_template_categories'),
            'permission_callback' => function ($request) {
                $args = $request->get_query_params();
                return ( wimj_who_can_access('rest_api/get_template_categories',$args) ? true : false );
            },
        ) );

        register_rest_route( 'wimj/v1', 'template_category', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_template_category'),
            'permission_callback' => function ($request) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access('rest_api/update_template_category',$args) ? true : false );
            },
        ) );

        register_rest_route( 'wimj/v1', 'template_category' , array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_template_category'),
            'permission_callback' => function ($request) {
                $args = $request->get_query_params();
                return ( wimj_who_can_access('rest_api/delete_template_category',$args) ? true : false );
            },
        ) );
        
	}

    /**
	 * get template categories API
	 */
	public function get_template_categories( $request ) {

        // do nonce verification
        if ( ! wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => esc_html__( 'Bad API Request' , WIMJ_SLUG ) ], 400 );
        }

        // get params
        // $params = $request->get_query_params();

        $list = wimj_template_categories_data()->list();

        if ( is_wp_error( $list ) ) {
            if ( isset( $list->error ) && !empty( $list->error ) ) {
                return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => $list->error ], 403 );
            } else {
                return new WP_REST_Response( (object) [ 'code' => 'unknown_error', 'message' => esc_html__( 'Unknown error occurred' , WIMJ_SLUG ) ], 403 );
            }
        } // end - is_wp_error

        return new WP_REST_Response( ( $list && !empty( $list ) && is_array( $list ) ? $list : array() ), 200 );
	}

    /**
	 * add new / update template category
	 */
	public function update_template_category( $request ) {

        // do nonce verification
        if ( ! wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => esc_html__( 'Bad API Request' , WIMJ_SLUG ) ], 400 );
        }
        // get body params
        $body_params = $request->get_json_params();
        $data = wimj_template_categories_data()->post( $body_params );

        if ( isset( $data ) && isset( $data->error ) && !empty( $data->error ) )
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => $data->error ], 403 );

        return new WP_REST_Response( $data, 200 );
	}

    /**
	 * delete template
	 */
	public function delete_template_category( $request ) {

        // do nonce verification
        if ( ! wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => esc_html__( 'Bad API Request' , WIMJ_SLUG ) ], 400 );
        }
        // get query params
        $params = $request->get_query_params();
        $data = wimj_template_categories_data()->delete( ( isset( $params['tid'] ) ? $params['tid'] : '' ) );

        if ( isset( $data ) && isset( $data->error ) && !empty( $data->error ) )
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => $data->error ], 403 );

        return new WP_REST_Response( $data, 200 );
	}

} // end - WIMJ_REST_API_TEMPLATES_ENDPOINTS

return new WIMJ_REST_API_TEMPLATES_ENDPOINTS();

endif; // end - class_exists

