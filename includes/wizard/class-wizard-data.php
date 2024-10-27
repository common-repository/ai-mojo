<?php

/**
 * WIzard Data class
 *
 * @class    WIMJ_Wizard_Data
 * @package  includes
 * @version  0.0.1
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}


if ( !class_exists( 'WIMJ_Wizard_Data', false ) ) {
    /**
     * Wizard Data class.
     */
    class WIMJ_Wizard_Data
    {
        /**
         * filter hook
         *
         * @var string
         */
        private  $_hook_prefix = 'WIMJ_Wizard_Data/' ;
        /**
         * Post type name.
         *
         * @var string
         */
        private  $post_type = 'wimj-wizard' ;
        /**
         * return default keys
         */
        public function keys()
        {
            return apply_filters( $this->_hook_prefix . 'keys', array(
                array(
                'key'   => 'wizard_title',
                'value' => '',
                'type'  => 'string',
            ),
                array(
                'key'   => 'status',
                'value' => 'setup',
                'type'  => 'string',
            ),
                array(
                'key'   => 'type',
                'value' => 'instant_article',
                'type'  => 'string',
            ),
                array(
                'key'   => 'current_step',
                'value' => 0,
                'type'  => 'number',
            ),
                array(
                'key'   => 'post_id',
                'value' => '',
                'type'  => 'string',
            )
            ) );
        }
        
        /**
         * Retrieve wizard list
         */
        public function list( $params = array() )
        {
            $list = array();
            $args = wp_parse_args( $params, array(
                'post_status'    => 'publish',
                'post_type'      => $this->post_type,
                'posts_per_page' => 10,
                'paged'          => 1,
            ) );
            $query = new WP_Query( $args );
            if ( $query && $query->have_posts() ) {
                while ( $query->have_posts() ) {
                    $query->the_post();
                    $item = $this->_compile_wizard_data( get_the_ID() );
                    if ( $item ) {
                        $list[] = (object) $item;
                    }
                }
            }
            wp_reset_postdata();
            return array(
                'list'          => $list,
                'post_count'    => ( isset( $query->post_count ) ? $query->post_count : 0 ),
                'found_posts'   => ( isset( $query->found_posts ) ? $query->found_posts : 0 ),
                'max_num_pages' => ( isset( $query->max_num_pages ) ? $query->max_num_pages : 0 ),
            );
        }
        
        /**
         * Retrieve wizard data
         */
        public function get( $wid = '', $type = 'instant_article' )
        {
            $wizard = array();
            // if id exists, override the wizard value
            
            if ( !empty($wid) ) {
                $post = get_post( $wid );
                if ( isset( $post ) && !is_wp_error( $post ) && $post && $post->post_type == $this->post_type ) {
                    $wizard = $this->_compile_wizard_data( $post->ID );
                }
                // end - $post
            }
            
            // if wizard is empty, add default value
            if ( empty($wizard) ) {
                $wizard = $this->_get_default_wizard_object( $type );
            }
            return (object) $wizard;
        }
        
        /**
         * delete wizard
         */
        public function delete( $wid )
        {
            // check if data exists
            if ( isset( $wid ) && !empty($wid) ) {
                $post = get_post( $wid );
            }
            // if exists, trigger update
            
            if ( isset( $post ) && !is_wp_error( $post ) && $post && $post->post_type == $this->post_type ) {
                $status = wp_delete_post( $post->ID );
                
                if ( $status ) {
                    return (object) array(
                        'status' => 'done',
                    );
                } else {
                    return (object) array(
                        'error' => esc_html__( 'Unable to delete Wizard', WIMJ_SLUG ),
                    );
                }
            
            } else {
                return (object) array(
                    'error' => esc_html__( 'Invalid ID', WIMJ_SLUG ),
                );
            }
        
        }
        
        /**
         * create new wizard
         */
        public function create( $data )
        {
            $id = wp_insert_post( array(
                'post_title'  => ( isset( $data['wizard_title'] ) && !empty($data['wizard_title']) ? sanitize_text_field( $data['wizard_title'] ) : '' ),
                'post_type'   => $this->post_type,
                'post_status' => 'publish',
            ), true );
            
            if ( is_wp_error( $id ) ) {
                return (object) array(
                    'status'  => 'error',
                    'message' => $id->get_error_message(),
                );
            } else {
                $this->update( $id, $data );
                return $id;
            }
            
            // end - id
        }
        
        /**
         * update wizard data
         */
        public function update( $id, $data )
        {
            // update meta data
            foreach ( $this->keys() as $item ) {
                $value = '';
                switch ( $item['type'] ) {
                    case 'string':
                        $value = ( isset( $data[$item['key']] ) ? sanitize_textarea_field( $data[$item['key']] ) : $item['value'] );
                        break;
                    case 'number':
                        $value = ( isset( $data[$item['key']] ) ? intval( $data[$item['key']] ) : $item['value'] );
                        break;
                    case 'float':
                        $value = ( isset( $data[$item['key']] ) ? floatVal( $data[$item['key']] ) : $item['value'] );
                        break;
                    case 'object':
                        $value = ( isset( $data[$item['key']] ) && !empty($data[$item['key']]) && is_object( $data[$item['key']] ) ? $data[$item['key']] : $item['value'] );
                        break;
                    case 'array':
                        
                        if ( $item['key'] === 'stop' ) {
                            $value = array();
                            if ( isset( $data[$item['key']] ) && !empty($data[$item['key']]) && is_array( $data[$item['key']] ) ) {
                                foreach ( $data[$item['key']] as $stop ) {
                                    $value[] = wp_slash( $stop );
                                }
                            }
                        } else {
                            $value = ( isset( $data[$item['key']] ) && !empty($data[$item['key']]) && is_array( $data[$item['key']] ) ? $data[$item['key']] : $item['value'] );
                        }
                        
                        break;
                }
                // update post meta
                update_post_meta( $id, wpaimojo()->plugin_meta_prefix() . $item['key'], $value );
                // update post title
                if ( $item['key'] == 'wizard_title' ) {
                    wp_update_post( array(
                        'ID'         => $id,
                        'post_title' => sanitize_text_field( $value ),
                    ) );
                }
            }
            return $this->get( $id );
        }
        
        /**
         * delete wizard
         */
        public function delete_wizard( $id )
        {
            $result = wp_delete_post( $id );
            
            if ( $result ) {
                return array(
                    'status' => 'success',
                );
            } else {
                return array(
                    'status'  => 'error',
                    'message' => esc_html__( 'Unable to delete Wizard', WIMJ_SLUG ),
                );
            }
            
            // end - id
        }
        
        /**
         * add wizard contents
         */
        public function add_contents(
            $id,
            $type = '',
            $content_type = '',
            $content_id = '',
            $completions = array(),
            $content_key = 'content'
        )
        {
            $valid = false;
            switch ( $type ) {
                case 'instant_article':
                    $valid = wimj_wia()->add_contents(
                        $id,
                        $type,
                        $content_type,
                        $content_id,
                        $completions,
                        $content_key
                    );
                    break;
                case 'rewrite_article':
                    break;
                case 'rewrite_article_v2':
                    break;
            }
            return $valid;
        }
        
        /**
         * update wizard content
         */
        public function update_content(
            $id,
            $type = '',
            $content_type = '',
            $content_id = '',
            $content = '',
            $content_key = 'content',
            $variation_id = null
        )
        {
            $valid = false;
            switch ( $type ) {
                case 'instant_article':
                    $valid = wimj_wia()->update_content(
                        $id,
                        $type,
                        $content_type,
                        $content_id,
                        $content,
                        $content_key,
                        $variation_id
                    );
                    break;
                case 'rewrite_article':
                    break;
                case 'rewrite_article_v2':
                    break;
            }
            return $valid;
        }
        
        /**
         * add contents variations
         */
        public function add_contents_variations(
            $id,
            $type = '',
            $content_type = '',
            $content_id = '',
            $completions = array(),
            $content_key = 'content',
            $auto_select = 'no'
        )
        {
            $valid = false;
            switch ( $type ) {
                case 'instant_article':
                    $valid = wimj_wia()->add_contents_variations(
                        $id,
                        $type,
                        $content_type,
                        $content_id,
                        $completions,
                        $content_key,
                        $auto_select
                    );
                    break;
                case 'rewrite_article':
                    break;
                case 'rewrite_article_v2':
                    break;
            }
            return $valid;
        }
        
        /**
         * update wizard content variation
         */
        public function update_content_variation(
            $id,
            $type = '',
            $content_type = '',
            $content_id = '',
            $content = '',
            $content_key = 'content',
            $variation_id = null
        )
        {
            $valid = false;
            switch ( $type ) {
                case 'instant_article':
                    $valid = wimj_wia()->update_content_variation(
                        $id,
                        $type,
                        $content_type,
                        $content_id,
                        $content,
                        $content_key,
                        $variation_id
                    );
                    break;
                case 'rewrite_article':
                    break;
                case 'rewrite_article_v2':
                    break;
            }
            return $valid;
        }
        
        /**
         * select contents variation
         */
        public function select_content_variation(
            $id,
            $type = '',
            $content_type = '',
            $content_id = '',
            $variation_id = '',
            $content_key = 'content'
        )
        {
            $valid = false;
            switch ( $type ) {
                case 'instant_article':
                    $valid = wimj_wia()->select_content_variation(
                        $id,
                        $type,
                        $content_type,
                        $content_id,
                        $variation_id,
                        $content_key
                    );
                    break;
                case 'rewrite_article':
                    break;
                case 'rewrite_article_v2':
                    break;
            }
            return $valid;
        }
        
        /**
         * create post
         */
        public function create_post( $wizard_id, $title = '', $content = '' )
        {
            $post_id = wp_insert_post( array(
                'post_title'   => sanitize_text_field( $title ),
                'post_content' => $content,
                'post_status'  => 'draft',
            ) );
            if ( is_wp_error( $post_id ) ) {
                return array(
                    'status'  => 'error',
                    'message' => $post_id->get_error_message(),
                );
            }
            // update post meta in wizard
            update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . 'post_id', $post_id );
            // add wizard_id to post
            update_post_meta( $post_id, wpaimojo()->plugin_meta_prefix() . 'wizard_id', $wizard_id );
            return $post_id;
        }
        
        /**
         * compile wizard data
         */
        private function _compile_wizard_data( $id )
        {
            $wizard = array(
                'id' => $id,
            );
            // add default post data
            $post = get_post( $id );
            if ( $post ) {
                $wizard['post_modified'] = get_post_timestamp( $id );
            }
            return $this->_build_wizard_object( $id, $wizard );
        }
        
        /**
         * build wizard data based on keys
         */
        private function _build_wizard_object( $id, $wizard = array() )
        {
            foreach ( $this->keys() as $item ) {
                switch ( $item['key'] ) {
                    default:
                        $wizard[$item['key']] = wimj_get_meta( array(
                            'id'      => $id,
                            'key'     => $item['key'],
                            'default' => $item['value'],
                        ) );
                        break;
                }
            }
            // add default value from specific type
            switch ( $wizard['type'] ) {
                case 'instant_article':
                    $wizard = wimj_wia()->get_meta_data( $id, $wizard );
                    break;
                case 'rewrite_article':
                    break;
                case 'rewrite_article_v2':
                    break;
            }
            return $wizard;
        }
        
        /**
         * build default wizard data based on keys
         */
        private function _get_default_wizard_object( $type = '' )
        {
            $wizard = array();
            foreach ( $this->keys() as $item ) {
                switch ( $item['key'] ) {
                    case 'type':
                        $wizard[$item['key']] = $type;
                        break;
                    default:
                        $wizard[$item['key']] = $item['value'];
                        break;
                }
            }
            // add default value from specific type
            switch ( $wizard['type'] ) {
                case 'instant_article':
                    $wizard = wimj_wia()->add_default_data( $wizard );
                    break;
                case 'rewrite_article':
                    break;
            }
            return $wizard;
        }
    
    }
    // end - WIMJ_Wizard_Data
    /**
     * Get WIMJ_Wizard_Data instance
     *
     * @return object
     */
    function wimj_wizard()
    {
        global  $wimj_wizard ;
        
        if ( !empty($wimj_wizard) ) {
            return $wimj_wizard;
        } else {
            $wimj_wizard = new WIMJ_Wizard_Data();
            return $wimj_wizard;
        }
    
    }

}

// end - class_exists