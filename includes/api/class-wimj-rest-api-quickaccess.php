<?php
/**
 * Quick Access REST API Endpoints Class
 *
 * @class    WIMJ_REST_API_QUICKACCESS
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_REST_API_QUICKACCESS', false ) ) :

/**
 * REST API endpoints class.
 */
class WIMJ_REST_API_QUICKACCESS extends WIMJ_REST_API_Core {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_REST_API_QUICKACCESS/';

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
        register_rest_route( 'wimj/v1', 'get_quick_access', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_quick_access'),
            'permission_callback' => function ($request) {
                $args = $request->get_query_params();
                return ( wimj_who_can_access('rest_api/get_quick_access',$args) ? true : false );;
            },
        ) );

		register_rest_route( 'wimj/v1', 'update_quick_access', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_quick_access'),
            'permission_callback' => function ($request) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access('rest_api/update_quick_access',$args) ? true : false );;
            },
        ) );

	}

    /**
	 * get quick access items
	 */
	public function get_quick_access( $request ) {
        // do nonce verification
        if ( ! wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => esc_html__( 'Bad API Request' , WIMJ_SLUG ) ], 400 );
        }        
        return new WP_REST_Response( $this->_get_user_settings() , 200 );
	}

	/**
	 * update quick access items
	 */
	public function update_quick_access( $request ) {

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

        $status = $this->_update_user_settings( $body_params['list'] );
        return new WP_REST_Response( $this->_get_user_settings() , 200 );
	}

	/**
	 * get user settings
	 */
	private function _get_user_settings() {
        $user = wp_get_current_user();
        $settings = get_user_meta( 
            $user->ID,
            wpaimojo()->plugin_meta_prefix() . 'quick_access', 
            true 
        );
        
        // use default options if empty
		if ( !( isset( $settings ) && !empty( $settings ) && is_array( $settings ) ) ) {
            $settings = apply_filters( 'wimj_default_quick_access' , array(
                array(
                    'id' => 'article_ideas',
                    'type' => 'base',
                ),
                array(
                    'id' => 'article_outline',
                    'type' => 'base',
                ),
                array(
                    'id' => 'bullet_points_to_content',
                    'type' => 'base',
                ),
                array(
                    'id' => 'content_expander',
                    'type' => 'base',
                ),
                array(
                    'id' => 'content_improver',
                    'type' => 'base',
                ),
                array(
                    'id' => 'content_paraphraser',
                    'type' => 'base',
                )
            ), $user );
        }
		
		return $settings;
	}

    /**
     * Update user settings
     */
    private function _update_user_settings( $data = array() ) {
        $user = wp_get_current_user();
        $list = array();

        if ( $this->_is_data_exists( $data ) ) {
            foreach ($data as $template) {
                if ( isset( $template['id'] ) && !empty( $template['id'] ) && isset( $template['type'] ) && (
                    $template['type'] === 'base' ||
                    $template['type'] === 'custom'
                ) ) {
                    $list[] = array(
                        'id' => esc_attr( $template['id'] ),
                        'type' => $template['type']
                    );
                } // $template['id']
            } // end - foreach $data
        } // end - $data

        return update_user_meta( 
            $user->ID, 
            wpaimojo()->plugin_meta_prefix() . 'quick_access', 
            $list
        );
    }

    /**
     * Check if data exists or not
     */
    private function _is_data_exists( $data = array() ) {
        return isset( $data ) && !empty( $data ) && is_array( $data );
    }

} // end - WIMJ_REST_API_QUICKACCESS

return new WIMJ_REST_API_QUICKACCESS();

endif; // end - class_exists

