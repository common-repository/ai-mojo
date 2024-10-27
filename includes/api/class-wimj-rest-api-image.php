<?php
/**
 * Image REST API Endpoints Class
 *
 * @class    WIMJ_REST_API_IMAGE_ENDPOINTS
 * @package  includes
 * @version  0.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_REST_API_IMAGE_ENDPOINTS', false ) ) :

/**
 * REST API endpoints class.
 */
class WIMJ_REST_API_IMAGE_ENDPOINTS extends WIMJ_REST_API_Core {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_REST_API_IMAGE_ENDPOINTS/';

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
        register_rest_route( 'wimj/v1', 'image_add_to_media_library', array(
            'methods' => 'POST',
            'callback' => array($this, 'image_add_to_media_library'),
            'permission_callback' => function ($request) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access('rest_api/image_add_to_media_library',$args) ? true : false );;
            },
        ) );

	}

    /**
	 * Add image to media library
	 */
	public function image_add_to_media_library( $request ) {

        // do nonce verification
        if ( ! wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => esc_html__( 'Bad API Request' , WIMJ_SLUG ) ], 400 );
        }

        // get body params
        $body_params = $request->get_json_params();
        
        // check for required data
        // if image_url not found
        if ( !( isset( $body_params['image_url'] ) && !empty( $body_params['image_url'] ) ) )
            return new WP_REST_Response( (object) [ 'code' => 'image_url_not_found', 'message' => esc_html__( 'Missing Image URL' , WAIG_SLUG ) ], 400 );

        // if image_name not found
        if ( !( isset( $body_params['image_name'] ) && !empty( $body_params['image_name'] ) ) )
            return new WP_REST_Response( (object) [ 'code' => 'image_name_not_found', 'message' => esc_html__( 'Missing Image Name' , WAIG_SLUG ) ], 400 );
        
        // Create the attachment
        $attachment = wimj_image_data()->upload_image( $body_params );
        if ( is_wp_error( $attachment ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'uploading_error', 'message' => $attachment->get_error_message() ], 400 );
        } else {
            return new WP_REST_Response( $attachment, 200 );
        }
	}

} // end - WIMJ_REST_API_IMAGE_ENDPOINTS

return new WIMJ_REST_API_IMAGE_ENDPOINTS();

endif; // end - class_exists

