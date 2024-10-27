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

        register_rest_route( 'wimj/v1', 'templates', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_templates'),
            'permission_callback' => function ($request) {
                $args = $request->get_query_params();
                return ( wimj_who_can_access('rest_api/get_templates',$args) ? true : false );
            },
        ) );

        register_rest_route( 'wimj/v1', 'template', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_template'),
            'permission_callback' => function ($request) {
                $args = $request->get_query_params();
                return ( wimj_who_can_access('rest_api/get_template',$args) ? true : false );
            },
        ) );

        register_rest_route( 'wimj/v1', 'template', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_template'),
            'permission_callback' => function ($request) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access('rest_api/update_template',$args) ? true : false );
            },
        ) );

        register_rest_route( 'wimj/v1', 'template' , array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_template'),
            'permission_callback' => function ($request) {
                $args = $request->get_query_params();
                return ( wimj_who_can_access('rest_api/delete_template',$args) ? true : false );
            },
        ) );
        
	}

    /**
	 * get templates API
	 */
	public function get_templates( $request ) {

        // do nonce verification
        if ( ! wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => esc_html__( 'Bad API Request' , WIMJ_SLUG ) ], 400 );
        }

        // get params
        $params = $request->get_query_params();

        // set params
        $args = array();

		if ( isset( $params['posts_per_page'] ) ) {
			$args['posts_per_page'] = ( intval( $params['posts_per_page'] ) <= 100 ? intval( $params['posts_per_page'] ) : 100 );
		} // end - $args['posts_per_page']

		if ( isset( $params['paged'] ) ) {
			$args['paged'] = intval( $params['paged'] );
		} // end - $args['posts_per_page']

		if ( isset( $params['sort_by'] ) ) {
			switch( $params['sort_by'] ) {
                case 'date-desc':
                    $args['orderby'] = 'date';
                    $args['order'] = 'DESC';
                    break;
                case 'date-asc':
                    $args['orderby'] = 'date';
                    $args['order'] = 'ASC';
                    break;
                case 'name-desc':
                    $args['orderby'] = 'name';
                    $args['order'] = 'DESC';
                    break;
                case 'name-asc':
                    $args['orderby'] = 'name';
                    $args['order'] = 'ASC';
                    break;               
            }
		} // end - $args['posts_per_page']

        if ( isset( $params['searchterm'] ) && !empty( $params['searchterm'] ) ) {
            $args['s'] = esc_attr( $params['searchterm'] );
        } // end - $args['posts_per_page'] 

        $data = wimj_templates_data()->list( $args );

        return new WP_REST_Response( (object) array(
            'templates' => $data['list'],
            'query_props' => array(
                'post_count' => $data['post_count'],
                'found_posts' => $data['found_posts'],
                'max_num_pages' => $data['max_num_pages']
            ),
            'props' => wimj_templates_data()->get_template_props()
        ), 200 );
	}

    /**
	 * get template data
	 */
	public function get_template( $request ) {

        // do nonce verification
        if ( ! wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => esc_html__( 'Bad API Request' , WIMJ_SLUG ) ], 400 );
        }
        $params = $request->get_query_params();
        $data = array(
            'template' => wimj_templates_data()->get( ( isset( $params['tid'] ) ? $params['tid'] : '' ) ),
            'props' => wimj_templates_data()->get_template_props()
        );
        return new WP_REST_Response( (object) $data, 200 );
	}


    /**
	 * add new / update template
	 */
	public function update_template( $request ) {

        // do nonce verification
        if ( ! wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => esc_html__( 'Bad API Request' , WIMJ_SLUG ) ], 400 );
        }
        // get body params
        $body_params = $request->get_json_params();
        $data = wimj_templates_data()->post( $body_params );

        if ( isset( $data ) && isset( $data->error ) && !empty( $data->error ) )
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => $data->error ], 403 );

        return new WP_REST_Response( $data, 200 );
	}

    /**
	 * delete template
	 */
	public function delete_template( $request ) {

        // do nonce verification
        if ( ! wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' ) ) {
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => esc_html__( 'Bad API Request' , WIMJ_SLUG ) ], 400 );
        }
        // get query params
        $params = $request->get_query_params();
        $data = wimj_templates_data()->delete( ( isset( $params['tid'] ) ? $params['tid'] : '' ) );

        if ( isset( $data ) && isset( $data->error ) && !empty( $data->error ) )
            return new WP_REST_Response( (object) [ 'code' => 'bad_api_request', 'message' => $data->error ], 403 );

        return new WP_REST_Response( $data, 200 );
	}

} // end - WIMJ_REST_API_TEMPLATES_ENDPOINTS

return new WIMJ_REST_API_TEMPLATES_ENDPOINTS();

endif; // end - class_exists

