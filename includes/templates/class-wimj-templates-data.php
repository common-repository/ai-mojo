<?php
/**
 * Template Data class
 *
 * @class    WIMJ_Templates_Data
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_Templates_Data', false ) ) :

/**
 * Template Data class.
 */
class WIMJ_Templates_Data {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_Templates_Data/';

	/**
	 * Post type name.
	 *
	 * @var string
	 */
	private $post_type = 'wimj-templates';

	/**
	 * meta field prefix.
	 *
	 * @var string
	 */
	private $meta_field_prefix = 'field_';

	/**
	 * return default keys
	 */
	public function keys() {
		return apply_filters( $this->_hook_prefix . 'keys', array(
            array( 'key' => 'name', 'value' => esc_html__( 'New Template' , WIMJ_SLUG ) , 'type' => 'string' ),
            array( 'key' => 'desc', 'value' => '' , 'type' => 'string' ),
			array( 'key' => 'category', 'value' => '' , 'type' => 'string' ),
			array( 'key' => 'mode', 'value' => 'complete' , 'type' => 'string' ),
            array( 'key' => 'engine', 'value' => '' , 'type' => 'string' ),
			array( 'key' => 'model', 'value' => '' , 'type' => 'string' ),
			array( 'key' => 'prompt', 'value' => '' , 'type' => 'string' ),
			array( 'key' => 'instructions', 'value' => '' , 'type' => 'string' ),
			array( 'key' => 'token', 'value' => 64 , 'type' => 'number' ),
			array( 'key' => 'temp', 'value' => 0.7 , 'type' => 'float' ),
			array( 'key' => 'top_p', 'value' => 1 , 'type' => 'float' ),
			array( 'key' => 'frequency_penalty', 'value' => 0 , 'type' => 'float' ),
			array( 'key' => 'presence_penalty', 'value' => 0 , 'type' => 'float' ),
			array( 'key' => 'stop', 'value' => array() , 'type' => 'array' ),
			array( 'key' => 'input_fields', 'value' => array(
				array( 'id' => 'INPUT', 'label' => __( 'Input', WIMJ_SLUG ), 'example' => '' )
			) , 'type' => 'array' ),
			array( 'key' => 'result_format', 'value' => 'completion' , 'type' => 'string' ),
			array( 'key' => 'result_prepend_text', 'value' => '' , 'type' => 'string' ),
			array( 'key' => 'customisable_options', 'value' => array(), 'type' => 'array' ),
        ));
	}

	/**
	 * Retrieve template list
	 */
	public function list( $params = array() ) {
        $list = array();
		$args = wp_parse_args( $params, array(
			'post_status' => 'publish',
			'post_type' => $this->post_type,
			'posts_per_page' => 10,
			'paged' => 1
		) );

		$query = new WP_Query( $args );
		if ($query && $query->have_posts()) : 
			while ($query->have_posts()) : 
				$query->the_post();
    			$item = $this->_compile_template_data( get_the_ID() );
    			if ( $item )
    				$list[] = (object) $item;
    		endwhile; 
    	endif;
		wp_reset_postdata();

        return array(
			'list' => $list,
			'post_count' => ( isset( $query->post_count ) ? $query->post_count : 0 ),
			'found_posts' => ( isset( $query->found_posts ) ? $query->found_posts : 0 ),
			'max_num_pages' => ( isset( $query->max_num_pages ) ? $query->max_num_pages : 0 )
		);
	}

	/**
	 * Retrieve template props
	 */
	public function get_template_props() {
        $props = array(
			
        );

        return (object) $props;
	}

	/**
	 * Retrieve template data
	 */
	public function get( $tid = '' ) {
        $template = array();
		// if id exists, override the template value
        if ( !empty( $tid ) ) {
            $post = get_post( $tid );
            if ( isset( $post ) && !is_wp_error( $post ) && $post && $post->post_type == $this->post_type ) {
				$template = $this->_compile_template_data( $post->ID );
            } // end - $post
        }
		// if template is empty, add default value
		if ( empty( $template ) ) {
			$template = $this->_get_default_template_object();
		}
        return (object) $template;
	}

	/**
	 * post template data
	 */
	public function post( $data ) {
		// check if data exists
		if ( isset( $data['id'] ) && !empty( $data['id'] ) ) {
			$post = get_post( $data['id'] );
		}
		// if exists, trigger update
		if ( isset( $post ) && !is_wp_error( $post ) && $post && $post->post_type == $this->post_type ) {
			return $this->_update( $post->ID, $data );
		} else {
			return $this->_create( $data );
		}
	}

	/**
	 * delete template
	 */
	public function delete( $tid ) {
		// check if data exists
		if ( isset( $tid ) && !empty( $tid ) ) {
			$post = get_post( $tid );
		}

		// if exists, trigger update
		if ( isset( $post ) && !is_wp_error( $post ) && $post && $post->post_type == $this->post_type ) {
			$status = wp_delete_post( $post->ID );
			if ( $status ) {
				return (object) array( 'status' => 'done' );
			} else {
				return (object) array( 'error' => esc_html__( 'Unable to delete template', WIMJ_SLUG ) );
			}
		} else {
			return (object) array( 'error' => esc_html__( 'Invalid ID', WIMJ_SLUG ) );
		}
	}

	/**
	 * create new template
	 */
	private function _create( $data ) {
		$id = wp_insert_post( array(
            'post_title'    => ( isset( $data['name'] ) && !empty( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '' ),
            'post_type'     => $this->post_type,
            'post_status'   => 'publish',
        ), true );
        if ( is_wp_error( $id ) ) {
            return (object) array( 'error' => $id->get_error_message() );
        } else {
			// if is copy from another template,
			if ( isset( $data['copy_template_id'] ) && !empty( $data['copy_template_id'] ) ) {
				$copied_template = get_post( $data['copy_template_id'] );
				if ( isset( $copied_template ) && !is_wp_error( $copied_template ) && $copied_template && $copied_template->post_type == $this->post_type ) {
					$copied_data = $this->_build_template_object( $copied_template->ID );
				}
				if ( isset( $copied_data ) && !empty( $copied_data ) ) {
					// use the new name & desc
					$copied_data['name'] = $data['name'];
					$copied_data['desc'] = $data['desc'];
					return $this->_update( $id, $copied_data );
				} 
			}
			return $this->_update( $id, $data );
        } // end - id
	}

	/**
	 * update template data
	 */
	private function _update( $id, $data ) {
		// update meta data
		foreach( $this->keys() as $item) {
			$value = '';
			switch( $item['type'] ) {
				case 'string':
					$value = ( isset( $data[ $item['key'] ] ) ? sanitize_textarea_field( $data[ $item['key'] ] ) : $item['value'] );
					break;
				case 'number':
					$value = ( isset( $data[ $item['key'] ] ) ? intval( $data[ $item['key'] ] ) : $item['value'] );
					break;
				case 'float':
					$value = ( isset( $data[ $item['key'] ] ) ? floatVal( $data[ $item['key'] ] ) : $item['value'] );
					break;
				case 'object':
					$value = ( isset( $data[ $item['key'] ] ) && !empty( $data[ $item['key'] ] ) && is_object( $data[ $item['key'] ]) ? $data[ $item['key'] ] : $item['value'] );
					break;
				case 'array':
					if ( $item['key'] === 'stop' ) {
						$value = array();
						if ( isset( $data[ $item['key'] ] ) && !empty( $data[ $item['key'] ] ) && is_array( $data[ $item['key'] ]) ) {
							foreach( $data[ $item['key'] ] as $stop ) {
								$value[] = wp_slash( $stop );
							}
						}
					} else {
						$value = ( isset( $data[ $item['key'] ] ) && !empty( $data[ $item['key'] ] ) && is_array( $data[ $item['key'] ]) ? $data[ $item['key'] ] : $item['value'] );
					}
					break;
			}
			// update post meta
			$meta_key = $this->meta_field_prefix . $item['key'];
			update_post_meta( $id, wpaimojo()->plugin_meta_prefix() . $meta_key, $value );

			// update post title
			if ( $item['key'] == 'name' ) {
				wp_update_post( array(
					'ID' => $id,
					'post_title' => sanitize_text_field( $value )
				) );
			}
		}	
		return $this->get( $id );
	}

	/**
	 * compile template data
	 */
	private function _compile_template_data( $id ) {
		$template = array(
			'id' => $id
		);
		return $this->_build_template_object( $id , $template );
	}

	/**
	 * build template data based on keys
	 */
	private function _build_template_object( $id, $template = array() ) {
		foreach( $this->keys() as $item) {
			$meta_key = $this->meta_field_prefix . $item['key'];
			switch( $item['key'] ) {
				case 'token':
					$template[$item['key']] = intval( wimj_get_meta( array( 'id' => $id, 'key' => $meta_key, 'default' => $item['value'] )) );
					break;
				case 'temp':
				case 'top_p':
				case 'frequency_penalty':
				case 'presence_penalty':
					$template[$item['key']] = floatval( wimj_get_meta( array( 'id' => $id, 'key' => $meta_key, 'default' => $item['value'] )) );
					break;
				case 'customisable_options':
					$template[$item['key']] = wimj_get_meta( array( 'id' => $id, 'key' => $meta_key, 'default' => $item['value'] ));
					// setup default value if not set
					if ( isset( $template[$item['key']] ) && !is_array( $template[$item['key']] ) ) {
						$template[$item['key']] = array( 'token' );
					}
					break;
				default:
					$template[$item['key']] = wimj_get_meta( array( 'id' => $id, 'key' => $meta_key, 'default' => $item['value'] ));
					break;
			}
		}
		return $template;
	}

	/**
	 * build default template data based on keys
	 */
	private function _get_default_template_object() {
		$template = array();
		foreach( $this->keys() as $item) {
			switch( $item['key'] ) {
				default:
					$template[$item['key']] = $item['value'];
					break;
			}
		}
		return $template;
	}

	/**
	 * sample func
	 */
	public function sample_func() {


	}
	
} // end - WIMJ_Templates_Data

/**
 * Get WIMJ_Templates_Data instance
 *
 * @return object
 */
function wimj_templates_data() {
	global $wimj_templates_data;
	if ( !empty( $wimj_templates_data ) ) {
		return $wimj_templates_data;
	} else {
		$wimj_templates_data = new WIMJ_Templates_Data();
		return $wimj_templates_data;
	}
}

endif; // end - class_exists

