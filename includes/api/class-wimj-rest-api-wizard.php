<?php

/**
 * Wizard REST API Endpoints Class
 *
 * @class    WIMJ_REST_API_WIZARD_ENDPOINTS
 * @package  includes
 * @version  0.5.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}


if ( !class_exists( 'WIMJ_REST_API_WIZARD_ENDPOINTS', false ) ) {
    /**
     * REST API endpoints class.
     */
    class WIMJ_REST_API_WIZARD_ENDPOINTS
    {
        /**
         * filter hook
         *
         * @var string
         */
        private  $_hook_prefix = 'WIMJ_REST_API_WIZARD_ENDPOINTS/' ;
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
            register_rest_route( 'wimj/v1', 'get_wizards', array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_wizards' ),
                'permission_callback' => function ( $request ) {
                $args = $request->get_query_params();
                return ( wimj_who_can_access( 'rest_api/get_wizards', $args ) ? true : false );
            },
            ) );
            // Register route
            register_rest_route( 'wimj/v1', 'get_wizard', array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_wizard' ),
                'permission_callback' => function ( $request ) {
                $args = $request->get_query_params();
                return ( wimj_who_can_access( 'rest_api/get_wizard', $args ) ? true : false );
            },
            ) );
            register_rest_route( 'wimj/v1', 'update_wizard', array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'update_wizard' ),
                'permission_callback' => function ( $request ) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access( 'rest_api/update_wizard', $args ) ? true : false );
            },
            ) );
            register_rest_route( 'wimj/v1', 'delete_wizard', array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'delete_wizard' ),
                'permission_callback' => function ( $request ) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access( 'rest_api/delete_wizard', $args ) ? true : false );
            },
            ) );
            register_rest_route( 'wimj/v1', 'add_wizard_contents', array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'add_wizard_contents' ),
                'permission_callback' => function ( $request ) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access( 'rest_api/add_wizard_contents', $args ) ? true : false );
            },
            ) );
            register_rest_route( 'wimj/v1', 'update_wizard_content', array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'update_wizard_content' ),
                'permission_callback' => function ( $request ) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access( 'rest_api/update_wizard_content', $args ) ? true : false );
            },
            ) );
            register_rest_route( 'wimj/v1', 'add_wizard_contents_variations', array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'add_wizard_contents_variations' ),
                'permission_callback' => function ( $request ) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access( 'rest_api/add_wizard_contents_variations', $args ) ? true : false );
            },
            ) );
            register_rest_route( 'wimj/v1', 'update_wizard_contents_variation', array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'update_wizard_contents_variation' ),
                'permission_callback' => function ( $request ) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access( 'rest_api/update_wizard_contents_variation', $args ) ? true : false );
            },
            ) );
            register_rest_route( 'wimj/v1', 'select_wizard_content_variation', array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'select_wizard_content_variation' ),
                'permission_callback' => function ( $request ) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access( 'rest_api/select_wizard_content_variation', $args ) ? true : false );
            },
            ) );
            register_rest_route( 'wimj/v1', 'wizard_contents_create_post', array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'wizard_contents_create_post' ),
                'permission_callback' => function ( $request ) {
                $args = $request->get_json_params();
                return ( wimj_who_can_access( 'rest_api/wizard_contents_create_post', $args ) ? true : false );
            },
            ) );
        }
        
        /**
         * get wizards
         */
        public function get_wizards( $request )
        {
            // do nonce verification
            if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            // get params
            $params = $request->get_query_params();
            // set params
            $args = array();
            if ( isset( $params['posts_per_page'] ) ) {
                $args['posts_per_page'] = ( intval( $params['posts_per_page'] ) <= 100 ? intval( $params['posts_per_page'] ) : 100 );
            }
            // end - $args['posts_per_page']
            if ( isset( $params['paged'] ) ) {
                $args['paged'] = intval( $params['paged'] );
            }
            // end - $args['posts_per_page']
            if ( isset( $params['sort_by'] ) ) {
                switch ( $params['sort_by'] ) {
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
            }
            // end - $args['posts_per_page']
            if ( isset( $params['searchterm'] ) && !empty($params['searchterm']) ) {
                $args['s'] = esc_attr( $params['searchterm'] );
            }
            // end - $args['posts_per_page']
            if ( isset( $params['status'] ) ) {
                switch ( $params['status'] ) {
                    case 'setup':
                        $args['meta_key'] = wpaimojo()->plugin_meta_prefix() . 'status';
                        $args['meta_value'] = 'setup';
                        break;
                    case 'process':
                        $args['meta_key'] = wpaimojo()->plugin_meta_prefix() . 'status';
                        $args['meta_value'] = 'process';
                        break;
                    case 'completed':
                        $args['meta_key'] = wpaimojo()->plugin_meta_prefix() . 'status';
                        $args['meta_value'] = 'completed';
                        break;
                }
            }
            // end - $args['posts_per_page']
            $data = wimj_wizard()->list( $args );
            return new WP_REST_Response( (object) array(
                'list'        => $data['list'],
                'query_props' => array(
                'post_count'    => $data['post_count'],
                'found_posts'   => $data['found_posts'],
                'max_num_pages' => $data['max_num_pages'],
            ),
            ), 200 );
        }
        
        /**
         * get wizard
         */
        public function get_wizard( $request )
        {
            // do nonce verification
            if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            // get params
            $params = $request->get_query_params();
            $wizard_id = ( isset( $params['id'] ) && !empty($params['id']) ? $params['id'] : false );
            $wizard_type = ( isset( $params['type'] ) && !empty($params['type']) ? $params['type'] : false );
            // get wizard data
            $data = wimj_wizard()->get( $wizard_id, $wizard_type );
            return new WP_REST_Response( (object) $data, 200 );
        }
        
        /**
         * update wizard
         */
        public function update_wizard( $request )
        {
            // do nonce verification
            if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            // get body params
            $body_params = $request->get_json_params();
            $wizard_id = ( isset( $body_params['id'] ) && !empty($body_params['id']) ? $body_params['id'] : false );
            $wizard_name = ( isset( $body_params['name'] ) && !empty($body_params['name']) ? $body_params['name'] : false );
            $wizard_type = ( isset( $body_params['type'] ) && !empty($body_params['type']) ? $body_params['type'] : 'instant_article' );
            // if no id, create one
            
            if ( empty($wizard_id) ) {
                $wizard_id = wimj_wizard()->create( $body_params );
                if ( isset( $wizard_id ) && isset( $wizard_id['status'] ) && $wizard_id['status'] == 'error' ) {
                    return new WP_REST_Response( (object) [
                        'code'    => 'bad_api_request',
                        'message' => ( isset( $wizard_id['message'] ) && !empty($wizard_id['message']) ? $wizard_id['message'] : esc_html__( 'Bad API Request', WIMJ_SLUG ) ),
                    ], 400 );
                }
            }
            
            switch ( $wizard_type ) {
                case 'instant_article':
                    $result = wimj_wia()->trigger( $wizard_id, $body_params );
                    break;
                case 'rewrite_article':
                    $result = array(
                        'status'  => 'error',
                        'message' => esc_html__( 'Invalid wizard type.', WIMJ_SLUG ),
                    );
                    break;
                case 'rewrite_article_v2':
                    $result = array(
                        'status'  => 'error',
                        'message' => esc_html__( 'Invalid wizard type.', WIMJ_SLUG ),
                    );
                    break;
                default:
                    $result = array(
                        'status'  => 'error',
                        'message' => esc_html__( 'Invalid wizard type.', WIMJ_SLUG ),
                    );
                    break;
            }
            // if error found
            if ( isset( $result ) && isset( $result['status'] ) && $result['status'] == 'error' ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => ( isset( $result['message'] ) && !empty($result['message']) ? $result['message'] : esc_html__( 'Bad API Request', WIMJ_SLUG ) ),
                ], 400 );
            }
            // return newly updated wizard
            return new WP_REST_Response( (object) wimj_wizard()->get( $wizard_id, $wizard_type ), 200 );
        }
        
        /**
         * delete wizard
         */
        public function delete_wizard( $request )
        {
            // do nonce verification
            if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            // get body params
            $body_params = $request->get_json_params();
            $wizard_id = ( isset( $body_params['id'] ) && !empty($body_params['id']) ? $body_params['id'] : false );
            // if no id
            if ( empty($wizard_id) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_wizard_id',
                    'message' => esc_html__( 'Missing Wizard ID', WIMJ_SLUG ),
                ], 400 );
            }
            // delete wizard
            $result = wimj_wizard()->delete_wizard( $wizard_id );
            // if error found
            if ( isset( $result ) && isset( $result['status'] ) && $result['status'] == 'error' ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => ( isset( $result['message'] ) && !empty($result['message']) ? $result['message'] : esc_html__( 'Bad API Request', WIMJ_SLUG ) ),
                ], 400 );
            }
            // return newly updated wizard
            return new WP_REST_Response( (object) array(
                'status' => 'success',
            ), 200 );
        }
        
        /**
         * add wizard contents
         */
        public function add_wizard_contents( $request )
        {
            // do nonce verification
            if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            // get body params
            $body_params = $request->get_json_params();
            if ( !(isset( $body_params['content_id'] ) && !empty($body_params['content_id'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_content_id',
                    'message' => esc_html__( 'Missing Content ID', WIMJ_SLUG ),
                ], 400 );
            }
            if ( !(isset( $body_params['content_type'] ) && !empty($body_params['content_type'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_content_type',
                    'message' => esc_html__( 'Missing Content Type', WIMJ_SLUG ),
                ], 400 );
            }
            if ( !(isset( $body_params['wizard_id'] ) && !empty($body_params['wizard_id'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_wizard_id',
                    'message' => esc_html__( 'Missing Wizard ID', WIMJ_SLUG ),
                ], 400 );
            }
            $wizard_id = $body_params['wizard_id'];
            // get wizard
            $wizard = wimj_wizard()->get( $wizard_id );
            if ( !(isset( $wizard->id ) && !empty($wizard->id) && $wizard->id === $wizard_id) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'invalid_wizard_id',
                    'message' => esc_html__( 'Invalid Wizard ID', WIMJ_SLUG ),
                ], 400 );
            }
            if ( !(isset( $body_params['completions'] ) && !empty($body_params['completions'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_completions',
                    'message' => esc_html__( 'Missing Completion Data', WIMJ_SLUG ),
                ], 400 );
            }
            // update completion to wizard
            $status = wimj_wizard()->add_contents(
                $wizard_id,
                $wizard->type,
                $body_params['content_type'],
                $body_params['content_id'],
                $body_params['completions'],
                ( isset( $body_params['content_key'] ) && !empty($body_params['content_key']) ? $body_params['content_key'] : 'content' )
            );
            // return newly updated wizard
            return new WP_REST_Response( wimj_wizard()->get( $wizard_id ), 200 );
        }
        
        /**
         * update wizard content
         */
        public function update_wizard_content( $request )
        {
            // do nonce verification
            if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            // get body params
            $body_params = $request->get_json_params();
            if ( !(isset( $body_params['content_id'] ) && !empty($body_params['content_id'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_content_id',
                    'message' => esc_html__( 'Missing Content ID', WIMJ_SLUG ),
                ], 400 );
            }
            if ( !(isset( $body_params['content_type'] ) && !empty($body_params['content_type'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_content_type',
                    'message' => esc_html__( 'Missing Content Type', WIMJ_SLUG ),
                ], 400 );
            }
            if ( !(isset( $body_params['wizard_id'] ) && !empty($body_params['wizard_id'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_wizard_id',
                    'message' => esc_html__( 'Missing Wizard ID', WIMJ_SLUG ),
                ], 400 );
            }
            $wizard_id = $body_params['wizard_id'];
            // get wizard
            $wizard = wimj_wizard()->get( $wizard_id );
            if ( !(isset( $wizard->id ) && !empty($wizard->id) && $wizard->id === $wizard_id) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'invalid_wizard_id',
                    'message' => esc_html__( 'Invalid Wizard ID', WIMJ_SLUG ),
                ], 400 );
            }
            if ( !(isset( $body_params['content'] ) && !empty($body_params['content'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_content',
                    'message' => esc_html__( 'Missing Content Data', WIMJ_SLUG ),
                ], 400 );
            }
            // update content to contents
            $status = wimj_wizard()->update_content(
                $wizard_id,
                $wizard->type,
                $body_params['content_type'],
                $body_params['content_id'],
                $body_params['content'],
                ( isset( $body_params['content_key'] ) && !empty($body_params['content_key']) ? $body_params['content_key'] : 'content' ),
                ( isset( $body_params['variation_id'] ) && !empty($body_params['variation_id']) ? $body_params['variation_id'] : null )
            );
            // return newly updated wizard
            return new WP_REST_Response( wimj_wizard()->get( $wizard_id ), 200 );
        }
        
        /**
         * add contents' variations
         */
        public function add_wizard_contents_variations( $request )
        {
            // do nonce verification
            if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            // get body params
            $body_params = $request->get_json_params();
            if ( !(isset( $body_params['content_id'] ) && !empty($body_params['content_id'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_content_id',
                    'message' => esc_html__( 'Missing Content ID', WIMJ_SLUG ),
                ], 400 );
            }
            if ( !(isset( $body_params['content_type'] ) && !empty($body_params['content_type'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_content_type',
                    'message' => esc_html__( 'Missing Content Type', WIMJ_SLUG ),
                ], 400 );
            }
            if ( !(isset( $body_params['wizard_id'] ) && !empty($body_params['wizard_id'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_wizard_id',
                    'message' => esc_html__( 'Missing Wizard ID', WIMJ_SLUG ),
                ], 400 );
            }
            $wizard_id = $body_params['wizard_id'];
            // get wizard
            $wizard = wimj_wizard()->get( $wizard_id );
            if ( !(isset( $wizard->id ) && !empty($wizard->id) && $wizard->id === $wizard_id) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'invalid_wizard_id',
                    'message' => esc_html__( 'Invalid Wizard ID', WIMJ_SLUG ),
                ], 400 );
            }
            if ( !(isset( $body_params['completions'] ) && !empty($body_params['completions'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_completions',
                    'message' => esc_html__( 'Missing Completion Data', WIMJ_SLUG ),
                ], 400 );
            }
            // add variations to wizard
            $status = wimj_wizard()->add_contents_variations(
                $wizard_id,
                $wizard->type,
                $body_params['content_type'],
                $body_params['content_id'],
                $body_params['completions'],
                ( isset( $body_params['content_key'] ) && !empty($body_params['content_key']) ? $body_params['content_key'] : 'content' ),
                ( isset( $body_params['auto_select'] ) && !empty($body_params['auto_select']) && $body_params['auto_select'] == 'yes' ? 'yes' : 'no' )
            );
            // return newly updated wizard
            return new WP_REST_Response( wimj_wizard()->get( $wizard_id ), 200 );
        }
        
        /**
         * update wizard content variation
         */
        public function update_wizard_contents_variation( $request )
        {
            // do nonce verification
            if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            // get body params
            $body_params = $request->get_json_params();
            if ( !(isset( $body_params['content_id'] ) && !empty($body_params['content_id'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_content_id',
                    'message' => esc_html__( 'Missing Content ID', WIMJ_SLUG ),
                ], 400 );
            }
            if ( !(isset( $body_params['content_type'] ) && !empty($body_params['content_type'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_content_type',
                    'message' => esc_html__( 'Missing Content Type', WIMJ_SLUG ),
                ], 400 );
            }
            if ( !(isset( $body_params['wizard_id'] ) && !empty($body_params['wizard_id'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_wizard_id',
                    'message' => esc_html__( 'Missing Wizard ID', WIMJ_SLUG ),
                ], 400 );
            }
            $wizard_id = $body_params['wizard_id'];
            // get wizard
            $wizard = wimj_wizard()->get( $wizard_id );
            if ( !(isset( $wizard->id ) && !empty($wizard->id) && $wizard->id === $wizard_id) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'invalid_wizard_id',
                    'message' => esc_html__( 'Invalid Wizard ID', WIMJ_SLUG ),
                ], 400 );
            }
            if ( !(isset( $body_params['variation_id'] ) && !empty($body_params['variation_id'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_variation_id',
                    'message' => esc_html__( 'Missing Variation ID', WIMJ_SLUG ),
                ], 400 );
            }
            if ( !(isset( $body_params['content'] ) && !empty($body_params['content'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_content',
                    'message' => esc_html__( 'Missing Content Data', WIMJ_SLUG ),
                ], 400 );
            }
            // update content to contents
            $status = wimj_wizard()->update_content_variation(
                $wizard_id,
                $wizard->type,
                $body_params['content_type'],
                $body_params['content_id'],
                $body_params['content'],
                ( isset( $body_params['content_key'] ) && !empty($body_params['content_key']) ? $body_params['content_key'] : 'content' ),
                $body_params['variation_id']
            );
            // return newly updated wizard
            return new WP_REST_Response( wimj_wizard()->get( $wizard_id ), 200 );
        }
        
        /**
         * select contents variation
         */
        public function select_wizard_content_variation( $request )
        {
            // do nonce verification
            if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            // get body params
            $body_params = $request->get_json_params();
            if ( !(isset( $body_params['content_id'] ) && !empty($body_params['content_id'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_content_id',
                    'message' => esc_html__( 'Missing Content ID', WIMJ_SLUG ),
                ], 400 );
            }
            if ( !(isset( $body_params['content_type'] ) && !empty($body_params['content_type'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_content_type',
                    'message' => esc_html__( 'Missing Content Type', WIMJ_SLUG ),
                ], 400 );
            }
            if ( !(isset( $body_params['wizard_id'] ) && !empty($body_params['wizard_id'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_wizard_id',
                    'message' => esc_html__( 'Missing Wizard ID', WIMJ_SLUG ),
                ], 400 );
            }
            $wizard_id = $body_params['wizard_id'];
            // get wizard
            $wizard = wimj_wizard()->get( $wizard_id );
            if ( !(isset( $wizard->id ) && !empty($wizard->id) && $wizard->id === $wizard_id) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'invalid_wizard_id',
                    'message' => esc_html__( 'Invalid Wizard ID', WIMJ_SLUG ),
                ], 400 );
            }
            if ( !(isset( $body_params['variation_id'] ) && !empty($body_params['variation_id'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_variation_id',
                    'message' => esc_html__( 'Missing Variation ID', WIMJ_SLUG ),
                ], 400 );
            }
            // update contents to wizard
            $status = wimj_wizard()->select_content_variation(
                $wizard_id,
                $wizard->type,
                $body_params['content_type'],
                $body_params['content_id'],
                $body_params['variation_id'],
                ( isset( $body_params['content_key'] ) && !empty($body_params['content_key']) ? $body_params['content_key'] : 'content' )
            );
            // return newly updated wizard
            return new WP_REST_Response( wimj_wizard()->get( $wizard_id ), 200 );
        }
        
        /**
         * create post
         */
        public function wizard_contents_create_post( $request )
        {
            // do nonce verification
            if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => esc_html__( 'Bad API Request', WIMJ_SLUG ),
                ], 400 );
            }
            // get body params
            $body_params = $request->get_json_params();
            if ( !(isset( $body_params['wizard_id'] ) && !empty($body_params['wizard_id'])) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'missing_wizard_id',
                    'message' => esc_html__( 'Missing Wizard ID', WIMJ_SLUG ),
                ], 400 );
            }
            $wizard_id = $body_params['wizard_id'];
            // get wizard
            $wizard = wimj_wizard()->get( $wizard_id );
            if ( !(isset( $wizard->id ) && !empty($wizard->id) && $wizard->id === $wizard_id) ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'invalid_wizard_id',
                    'message' => esc_html__( 'Invalid Wizard ID', WIMJ_SLUG ),
                ], 400 );
            }
            // update contents to wizard
            $result = wimj_wizard()->create_post( $wizard_id, $body_params['title'], $body_params['content'] );
            // if error found
            if ( isset( $result ) && isset( $result['status'] ) && $result['status'] == 'error' ) {
                return new WP_REST_Response( (object) [
                    'code'    => 'bad_api_request',
                    'message' => ( isset( $result['message'] ) && !empty($result['message']) ? $result['message'] : esc_html__( 'Bad API Request', WIMJ_SLUG ) ),
                ], 400 );
            }
            // return newly updated wizard
            return new WP_REST_Response( wimj_wizard()->get( $wizard_id ), 200 );
        }
    
    }
    // end - WIMJ_REST_API_WIZARD_ENDPOINTS
    return new WIMJ_REST_API_WIZARD_ENDPOINTS();
}

// end - class_exists