<?php

/**
 * AI Engines REST API Endpoints Class
 *
 * @class    WIMJ_REST_API_AIENGINES_ENDPOINTS
 * @package  includes
 * @version  0.0.1
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}


if ( !class_exists( 'WIMJ_REST_API_AIENGINES_ENDPOINTS', false ) ) {
    /**
     * REST API endpoints class.
     */
    class WIMJ_REST_API_AIENGINES_ENDPOINTS
    {
        /**
         * filter hook
         *
         * @var string
         */
        private  $_hook_prefix = 'WIMJ_REST_API_AIENGINES_ENDPOINTS/' ;
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
            register_rest_route( 'wimj/v1', 'ai_engines', array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_ai_engines' ),
                'permission_callback' => function ( $request ) {
                $args = $request->get_query_params();
                return ( wimj_who_can_access( 'rest_api/get_ai_engines', $args ) ? true : false );
            },
            ) );
            register_rest_route( 'wimj/v1', 'ai_engines', array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'update_ai_engines' ),
                'permission_callback' => function ( $request ) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access( 'rest_api/update_ai_engines', $args ) ? true : false );
            },
            ) );
        }
        
        /**
         * callback for AI engines data
         */
        public function get_ai_engines( $request )
        {
            // do nonce verification
            if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            return new WP_REST_Response( $this->_get_ai_engines(), 200 );
        }
        
        /**
         * update AI engines data
         */
        public function update_ai_engines( $request )
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
            $option = wimj_admin_settings()->get_option();
            $engines = wimj_ai_engines_options();
            // update engine specific option
            foreach ( $engines as $engine ) {
                update_option( 'wpaimojo_' . $engine . '_api', array(
                    'location' => ( isset( $body_params[$engine . '_api_location'] ) && !empty($body_params[$engine . '_api_location']) && ($body_params[$engine . '_api_location'] === 'database' || $body_params[$engine . '_api_location'] === 'config') ? $body_params[$engine . '_api_location'] : '' ),
                    'key'      => ( isset( $body_params[$engine . '_api_key'] ) && !empty($body_params[$engine . '_api_key']) ? esc_attr( $body_params[$engine . '_api_key'] ) : '' ),
                ) );
                // update plugin option
                $option['ai_engine_' . $engine] = ( isset( $body_params[$engine . '_api_enabled'] ) && !empty($body_params[$engine . '_api_enabled']) && $body_params[$engine . '_api_enabled'] === 'yes' ? 'yes' : 'no' );
                // update default model
                $option['ai_engine_' . $engine . '_default_model'] = ( isset( $body_params[$engine . '_api_default_model'] ) && !empty($body_params[$engine . '_api_default_model']) ? esc_attr( $body_params[$engine . '_api_default_model'] ) : '' );
            }
            // end - $engines
            // set default engine
            $option['ai_engine_default'] = ( isset( $body_params['default_engine'] ) && !empty($body_params['default_engine']) && ($body_params['default_engine'] === 'ai21' || $body_params['default_engine'] === 'openai') ? $body_params['default_engine'] : '' );
            // update option
            wimj_admin_settings()->update_option( $option );
            // update chat persona
            if ( isset( $body_params['openai_chat_personas'] ) ) {
                WIMJ_Chat_Data::update( $body_params['openai_chat_personas'] );
            }
            // end - openai_chat_personas
            return new WP_REST_Response( $this->_get_ai_engines(), 200 );
        }
        
        /**
         * get ai engines
         */
        private function _get_ai_engines()
        {
            $plugin_option = get_option( 'wpaimojo' );
            $data = array(
                'default_engine' => ( isset( $plugin_option['ai_engine_default'] ) && !empty($plugin_option['ai_engine_default']) && ($plugin_option['ai_engine_default'] === 'ai21' || $plugin_option['ai_engine_default'] === 'openai') ? $plugin_option['ai_engine_default'] : 'ai21' ),
            );
            // get available ai engines
            $engines = wimj_ai_engines_options();
            foreach ( $engines as $engine ) {
                // get engine option
                $option = get_option( 'wpaimojo_' . $engine . '_api' );
                // check if engine enabled
                $data[$engine . '_api_enabled'] = ( isset( $plugin_option['ai_engine_' . $engine] ) && !empty($plugin_option['ai_engine_' . $engine]) && $plugin_option['ai_engine_' . $engine] === 'yes' ? 'yes' : 'no' );
                // get default model
                $data[$engine . '_api_default_model'] = ( isset( $plugin_option['ai_engine_' . $engine . '_default_model'] ) && !empty($plugin_option['ai_engine_' . $engine . '_default_model']) ? esc_attr( $plugin_option['ai_engine_' . $engine . '_default_model'] ) : '' );
                // get engine location
                $data[$engine . '_api_location'] = ( isset( $option['location'] ) && !empty($option['location']) && ($option['location'] === 'database' || $option['location'] === 'config') ? $option['location'] : '' );
                // get engine api key
                $data[$engine . '_api_key'] = ( isset( $option['key'] ) && !empty($option['key']) ? esc_attr( $option['key'] ) : '' );
                // add openai chat personas
                if ( $engine === 'openai' ) {
                    $data[$engine . '_chat_personas'] = WIMJ_Chat_Data::get_personas();
                }
            }
            // end - $engines
            return (object) $data;
        }
    
    }
    // end - WIMJ_REST_API_AIENGINES_ENDPOINTS
    return new WIMJ_REST_API_AIENGINES_ENDPOINTS();
}

// end - class_exists