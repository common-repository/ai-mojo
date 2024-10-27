<?php
/**
 * Settings REST API Endpoints Class
 *
 * @class    WIMJ_REST_API_NOTES_ENDPOINTS
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_REST_API_NOTES_ENDPOINTS', false ) ) :

/**
 * REST API endpoints class.
 */
class WIMJ_REST_API_NOTES_ENDPOINTS extends WIMJ_REST_API_Core {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_REST_API_NOTES_ENDPOINTS/';

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
        register_rest_route( 'wimj/v1', 'notes', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_notes'),
            'permission_callback' => function ($request) {
                $args = $request->get_query_params();
                return ( wimj_who_can_access('rest_api/get_notes',$args) ? true : false );;
            },
        ) );

        register_rest_route( 'wimj/v1', 'notes', array(
            'methods' => 'POST',
            'callback' => array($this, 'add_notes'),
            'permission_callback' => function ($request) {
                $args = $request->get_query_params();
                return ( wimj_who_can_access('rest_api/add_notes',$args) ? true : false );;
            },
        ) );

        register_rest_route( 'wimj/v1', 'notes', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_notes'),
            'permission_callback' => function ($request) {
                $args = $request->get_query_params();
                return ( wimj_who_can_access('rest_api/update_notes',$args) ? true : false );;
            },
        ) );
	}

    /**
	 * get notes
	 */
	public function get_notes( $request ) {
        // do nonce verification
        if ( ! wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => esc_html__( 'Bad API Request' , WIMJ_SLUG ) ], 400 );
        }

        $error = false;
        // get params
        $args = $request->get_query_params();
		
        // check for error
        if ( !( isset( $args['post_id'] ) && !empty( $args['post_id'] ) ) )
            $error = esc_html__( 'Missing Post ID' , WIMJ_SLUG );

        if ( $error && !empty( $error ) )
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => $error ], 403 );

        return new WP_REST_Response( $this->_get_notes_from_post_meta( $args['post_id'] ) , 200 );
	}

    /**
	 * add notes
	 */
	public function add_notes( $request ) {
        // do nonce verification
        if ( ! wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => esc_html__( 'Bad API Request' , WIMJ_SLUG ) ], 400 );
        }   

        $error = false;
        // get params
        $args = $request->get_query_params();
        // get body params
        $body_params = $request->get_json_params();

        // check for error
        if ( !( isset( $args['post_id'] ) && !empty( $args['post_id'] ) ) )
            $error = esc_html__( 'Missing Post ID' , WIMJ_SLUG );

        if ( $error && !empty( $error ) )
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => $error ], 403 );

        $notes = $this->_get_notes_from_post_meta( $args['post_id'] );
        $notes .= ( isset( $body_params['notes'] ) && !empty( $body_params['notes'] ) ? ( !empty($notes) ? "\n\n" : '' ) . $this->_sanitize_notes( $body_params['notes'] ) : '' ); 

        // update notes in meta field
        update_post_meta( $args['post_id'], wpaimojo()->plugin_meta_prefix() . 'has_notes', ( isset( $notes ) && !empty( $notes ) ? 'yes' : 'no' ) );
        update_post_meta( $args['post_id'], wpaimojo()->plugin_meta_prefix() . 'notes', ( isset( $notes ) && !empty( $notes ) ? $notes : '' ) );
        return new WP_REST_Response( $this->_get_notes_from_post_meta( $args['post_id'] ) , 200 );
	}

    /**
	 * update notes
	 */
	public function update_notes( $request ) {
        // do nonce verification
        if ( ! wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => esc_html__( 'Bad API Request' , WIMJ_SLUG ) ], 400 );
        }   

        $error = false;
        // get params
        $args = $request->get_query_params();
        // get body params
        $body_params = $request->get_json_params();
		
        // check for error
        if ( !( isset( $args['post_id'] ) && !empty( $args['post_id'] ) ) )
            $error = esc_html__( 'Missing Post ID' , WIMJ_SLUG );

        if ( $error && !empty( $error ) )
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => $error ], 403 );

        // update notes in meta field
        update_post_meta( $args['post_id'], wpaimojo()->plugin_meta_prefix() . 'has_notes', ( isset( $body_params['notes'] ) && !empty( $body_params['notes'] ) ? 'yes' : 'no' ) );
        update_post_meta( $args['post_id'], wpaimojo()->plugin_meta_prefix() . 'notes', ( isset( $body_params['notes'] ) && !empty( $body_params['notes'] ) ? $this->_sanitize_notes( $body_params['notes'] ) : '' ) );
        return new WP_REST_Response( $this->_get_notes_from_post_meta( $args['post_id'] ) , 200 );
	}

    /**
	 * get notes from post meta
	 */
	private function _get_notes_from_post_meta( $id ) {
        return wimj_get_meta( array(
            'id' => $id, 
            'key' => 'notes',
            'default' => '',
        ) );
	}

    /**
	 * kses string before store into database
	 */
	private function _sanitize_notes( $notes ) {
        return sanitize_textarea_field( $notes );
	}

} // end - WIMJ_REST_API_NOTES_ENDPOINTS

return new WIMJ_REST_API_NOTES_ENDPOINTS();

endif; // end - class_exists

