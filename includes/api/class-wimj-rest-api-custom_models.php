<?php

/**
 * Settings REST API Endpoints Class
 *
 * @class    WIMJ_REST_API_CUSTOM_MODELS_ENDPOINTS
 * @package  includes
 * @version  0.0.1
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}


if ( !class_exists( 'WIMJ_REST_API_CUSTOM_MODELS_ENDPOINTS', false ) ) {
    /**
     * REST API endpoints class.
     */
    class WIMJ_REST_API_CUSTOM_MODELS_ENDPOINTS extends WIMJ_REST_API_Core
    {
        /**
         * filter hook
         *
         * @var string
         */
        private  $_hook_prefix = 'WIMJ_REST_API_CUSTOM_MODELS_ENDPOINTS/' ;
        /**
         * Constructor.
         */
        public function __construct()
        {
            // add REST API end routes
            add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
        }
        
        /**
         * Register endpoints
         */
        public function register_endpoints()
        {
            // Register route
            register_rest_route( 'wimj/v1', 'get_custom_models', array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_custom_models' ),
                'permission_callback' => function ( $request ) {
                $args = $request->get_query_params();
                return ( wimj_who_can_access( 'rest_api/get_custom_models', $args ) ? true : false );
            },
            ) );
        }
        
        /**
         * get plugin settings
         */
        public function get_custom_models( $request )
        {
            // do nonce verification
            if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            // get plugin option
            $custom_models = array();
            // pass through filter hook
            $custom_models = apply_filters( $this->_hook_prefix . 'get_custom_models', $custom_models );
            return new WP_REST_Response( (object) $custom_models, 200 );
        }
    
    }
    // end - WIMJ_REST_API_CUSTOM_MODELS_ENDPOINTS
    return new WIMJ_REST_API_CUSTOM_MODELS_ENDPOINTS();
}

// end - class_exists