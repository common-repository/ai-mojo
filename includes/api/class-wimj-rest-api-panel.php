<?php
/**
 * Quick Access REST API Endpoints Class
 *
 * @class    WIMJ_REST_API_PANEL_SETTINGS
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_REST_API_PANEL_SETTINGS', false ) ) :

/**
 * REST API endpoints class.
 */
class WIMJ_REST_API_PANEL_SETTINGS extends WIMJ_REST_API_Core {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_REST_API_PANEL_SETTINGS/';

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
        register_rest_route( 'wimj/v1', 'get_panel_settings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_panel_settings'),
            'permission_callback' => function ($request) {
                $args = $request->get_query_params();
                return ( wimj_who_can_access('rest_api/get_panel_settings',$args) ? true : false );;
            },
        ) );

		register_rest_route( 'wimj/v1', 'update_panel_settings', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_panel_settings'),
            'permission_callback' => function ($request) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access('rest_api/update_panel_settings',$args) ? true : false );;
            },
        ) );

	}

    /**
	 * get panel settings
     */
	public function get_panel_settings( $request ) {
        // do nonce verification
        if ( ! wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => esc_html__( 'Bad API Request' , WIMJ_SLUG ) ], 400 );
        }        
        return new WP_REST_Response( $this->_get_settings() , 200 );
	}

	/**
	 * update panel settings
	 */
	public function update_panel_settings( $request ) {

        // do nonce verification
        if ( ! wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => esc_html__( 'Bad API Request' , WIMJ_SLUG ) ], 400 );
        }

        $error = false;
        // get body params
        $body_params = $request->get_json_params();
        // check for error
        if ( $error && !empty( $error ) )
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => $error ], 403 );

        $status = $this->_update_settings( $body_params );
        return new WP_REST_Response( $this->_get_settings() , 200 );
	}

	/**
	 * get panel settings
	 */
	private function _get_settings() {
        $user = wp_get_current_user();
        $settings = get_user_meta( 
            $user->ID,
            wpaimojo()->plugin_meta_prefix() . 'panel_settings', 
            true 
        );
        
        // use default options if empty
		if ( !( isset( $settings ) && !empty( $settings ) && is_array( $settings ) ) ) {
            $settings = $this->_get_default_settings($user);
        }
		
		return $settings;
	}

    /**
     * Update panel settings
     */
    private function _update_settings( $data = array() ) {
        $user = wp_get_current_user();
        $settings = get_user_meta( 
            $user->ID,
            wpaimojo()->plugin_meta_prefix() . 'panel_settings', 
            true 
        );

        if ( !( isset( $settings ) && !empty( $settings ) && is_array( $settings ) ) ) {
            $settings = $this->_get_default_settings($user);
        }

        // update menu settings
        if ( isset( $data['menus'] ) && $this->_is_data_exists( $data['menus'] ) ) {
            // $data to update only the show/hide status and order of the menu
            foreach ( $data['menus'] as $key => $value ) {
                foreach ( $settings['menus'] as $k => $v ) {
                    if ( $v['id'] == $value['id'] ) {
                        $settings['menus'][$k]['show'] = $value['show'];
                        $settings['menus'][$k]['order'] = $value['order'];
                    }
                }
            }
        }

        return update_user_meta( 
            $user->ID, 
            wpaimojo()->plugin_meta_prefix() . 'panel_settings', 
            $settings
        );
    }

    /**
     * Check if data exists or not
     */
    private function _is_data_exists( $data = array() ) {
        return isset( $data ) && !empty( $data ) && is_array( $data );
    }

    /**
     * Get default settings
     */
    private function _get_default_settings($user) {
        return apply_filters( 'wimj_default_panel_settings' , array(
            'menus' => array(
                array(
                    'id' => 'dashboard',
                    'show' => 'yes',
                    'hideable' => 'no',
                    'order' => 1
                ),
                array(
                    'id' => 'template',
                    'show' => 'yes',
                    'hideable' => 'yes',
                    'order' => 2
                ),
                array(
                    'id' => 'results',
                    'show' => 'yes',
                    'hideable' => 'no',
                    'order' => 3
                ),
                array(
                    'id' => 'playground',
                    'show' => 'yes',
                    'hideable' => 'yes',
                    'order' => 4
                ),
                array(
                    'id' => 'wizard',
                    'show' => 'yes',
                    'hideable' => 'yes',
                    'order' => 5
                ),
                array(
                    'id' => 'notes',
                    'show' => 'yes',
                    'hideable' => 'yes',
                    'order' => 6
                ),
                array(
                    'id' => 'dalle',
                    'show' => 'yes',
                    'hideable' => 'yes',
                    'order' => 7
                ),
                array(
                    'id' => 'chat',
                    'show' => 'yes',
                    'hideable' => 'yes',
                    'order' => 8
                )
            )
        ), $user );
    }

} // end - WIMJ_REST_API_PANEL_SETTINGS

return new WIMJ_REST_API_PANEL_SETTINGS();

endif; // end - class_exists

