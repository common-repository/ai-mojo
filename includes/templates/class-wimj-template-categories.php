<?php
/**
 * Template Data class
 *
 * @class    WIMJ_Template_Categories
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_Template_Categories', false ) ) :

/**
 * Template Data class.
 */
class WIMJ_Template_Categories {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_Template_Categories/';

	/**
	 * Post type name.
	 *
	 * @var string
	 */
	private $taxonomy = 'wimj-template-categories';


	/**
	 * Retrieve template categories
	 */
	public function list( $params = array(), $output = 'objects' ) {
		$args = wp_parse_args( $params, array(
			'name' => $this->taxonomy
		) );
		return get_taxonomies( $args, $output );
	}

	/**
	 * Retrieve template category data
	 */
	public function get( $id = '' ) {
        $category = array();
		// if id exists, override the category value
        if ( !empty( $id ) ) {
            $term = get_term( $id );
            if ( isset( $term ) && !is_wp_error( $term ) && $term && $term->taxonomy == $this->taxonomy ) {
				$category = $this->_compile_category_data( $term->ID, $term );
            } // end - $post
        }
        return (object) $category;
	}

	/**
	 * post template category data
	 */
	public function post( $data ) {
		// check if data exists
		if ( isset( $data['id'] ) && !empty( $data['id'] ) ) {
			$term = get_term( $data['id'] );
		}
		// if exists, trigger update
		if ( isset( $term ) && !is_wp_error( $term ) && $term && $term->taxonomy == $this->taxonomy ) {
			return $this->_update( $term->ID, $data );
		} else {
			return $this->_create( $data );
		}
	}

	/**
	 * delete template category data
	 */
	public function delete( $id ) {
		// check if data exists
		if ( isset( $id ) && !empty( $id ) ) {
			$term = get_term( $id );
		}

		// if exists, trigger update
		if ( isset( $term ) && !is_wp_error( $term ) && $term && $term->post_type == $this->taxonomy ) {
			$status = wp_delete_term( $term->ID, $this->taxonomy );
			if ( $status ) {
				return (object) array( 'status' => 'done' );
			} else {
				return (object) array( 'error' => esc_html__( 'Unable to delete template category', WIMJ_SLUG ) );
			}
		} else {
			return (object) array( 'error' => esc_html__( 'Invalid ID', WIMJ_SLUG ) );
		}
	}

	/**
	 * create new template category
	 */
	private function _create( $data ) {
		$id = wp_insert_term( isset( $data['name'] ) && !empty( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '' , $this->taxonomy );
        if ( is_wp_error( $id ) ) {
            return (object) array( 'error' => $id->get_error_message() );
        } else {
			return $this->get( $id );
        } // end - id
	}

	/**
	 * update template category data
	 */
	private function _update( $id, $data ) {
		wp_update_term( $id, $this->taxonomy, array(
			'name' => isset( $data['name'] ) && !empty( $data['name'] ) ? sanitize_text_field( $data['name'] ) : ''
		) );
		return $this->get( $id );
	}

	/**
	 * compile category data
	 */
	private function _compile_category_data( $id, $term ) {
		$category = array(
			'id' => $id,
			'name' => $term->name,
		);
		return $category;
	}

	/**
	 * sample func
	 */
	public function sample_func() {


	}
	
} // end - WIMJ_Template_Categories

/**
 * Get WIMJ_Template_Categories instance
 *
 * @return object
 */
function wimj_template_categories() {
	global $wimj_template_categories;
	if ( !empty( $wimj_template_categories ) ) {
		return $wimj_template_categories;
	} else {
		$wimj_template_categories = new WIMJ_Template_Categories();
		return $wimj_template_categories;
	}
}

endif; // end - class_exists

