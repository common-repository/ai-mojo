<?php

/**
 * Settings REST API Endpoints Class
 *
 * @class    WIMJ_REST_API_SETTINGS_ENDPOINTS
 * @package  includes
 * @version  0.0.1
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}


if ( !class_exists( 'WIMJ_REST_API_SETTINGS_ENDPOINTS', false ) ) {
    /**
     * REST API endpoints class.
     */
    class WIMJ_REST_API_SETTINGS_ENDPOINTS extends WIMJ_REST_API_Core
    {
        /**
         * filter hook
         *
         * @var string
         */
        private  $_hook_prefix = 'WIMJ_REST_API_SETTINGS_ENDPOINTS/' ;
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
            register_rest_route( 'wimj/v1', 'get_settings', array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_settings' ),
                'permission_callback' => function ( $request ) {
                $args = $request->get_query_params();
                return ( wimj_who_can_access( 'rest_api/get_settings', $args ) ? true : false );
            },
            ) );
            register_rest_route( 'wimj/v1', 'update_getting_started_wizard', array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'update_getting_started_wizard' ),
                'permission_callback' => function ( $request ) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access( 'rest_api/update_getting_started_wizard', $args ) ? true : false );
            },
            ) );
            register_rest_route( 'wimj/v1', 'update_wizard_settings', array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'update_wizard_settings' ),
                'permission_callback' => function ( $request ) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access( 'rest_api/update_wizard_settings', $args ) ? true : false );
            },
            ) );
        }
        
        /**
         * get plugin settings
         */
        public function get_settings( $request )
        {
            // do nonce verification
            if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            return new WP_REST_Response( (object) $this->_get_settings(), 200 );
        }
        
        /**
         * update getting started settings
         */
        public function update_getting_started_wizard( $request )
        {
            // do nonce verification
            if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            $error = false;
            // get body params
            $body_params = $request->get_json_params();
            // check for error
            if ( !(isset( $body_params['ai_engine'] ) && !empty($body_params['ai_engine'])) ) {
                $error = esc_html__( 'Missing AI Engine', WIMJ_SLUG );
            }
            if ( !(isset( $body_params['ai_api_location'] ) && !empty($body_params['ai_api_location'])) ) {
                $error = esc_html__( 'Missing API location', WIMJ_SLUG );
            }
            if ( isset( $body_params['ai_api_location'] ) && $body_params['ai_api_location'] === 'database' && !(isset( $body_params['ai_api_key'] ) && !empty($body_params['ai_api_key'])) ) {
                $error = esc_html__( 'Missing API key', WIMJ_SLUG );
            }
            if ( $error && !empty($error) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => $error,
                ], 403 );
            }
            // update wizard
            $status = wimj_admin_settings()->update_wizard( array(
                'api_engine'   => esc_attr( $body_params['ai_engine'] ),
                'api_location' => esc_attr( $body_params['ai_api_location'] ),
                'api_key'      => ( !empty($body_params['ai_api_key']) ? esc_attr( $body_params['ai_api_key'] ) : '' ),
            ) );
            
            if ( $status ) {
                return new WP_REST_Response( (object) array(
                    'status' => 'updated',
                ), 200 );
            } else {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            
            // end - $results->status
        }
        
        /**
         * update wizard settings
         */
        public function update_wizard_settings( $request )
        {
            // do nonce verification
            if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            $error = false;
            // get body params
            $body_params = $request->get_json_params();
            // update wizard
            $status = wimj_admin_settings()->update_wizard_settings( $body_params );
            
            if ( $status ) {
                return new WP_REST_Response( (object) $this->_get_settings(), 200 );
            } else {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            
            // end - $results->status
        }
        
        /**
         * get settings
         */
        private function _get_settings()
        {
            // get plugin option
            $settings = wimj_admin_settings()->get_option();
            // add templates
            $settings['templates'] = wimj_templates_data()->list( array(
                'posts_per_page' => 9999,
                'paged'          => 1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ) );
            // add wizard settings
            $settings['wizard_settings'] = wimj_admin_settings()->get_wizard_settings();
            return $settings;
        }
    
    }
    // end - WIMJ_REST_API_SETTINGS_ENDPOINTS
    return new WIMJ_REST_API_SETTINGS_ENDPOINTS();
}

// end - class_exists