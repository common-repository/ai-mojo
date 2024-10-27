<?php
/**
 * Setup Templates class
 *
 * @class    WIMJ_Templates_Setup
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_Templates_Setup', false ) ) :

/**
 * Setup Templates class.
 */
class WIMJ_Templates_Setup {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_Templates_Setup/';


	/**
	 * Constructor.
	 */
	public function __construct() {
		// register templates as custom post type
		$this->register_cpt();
	}

	/**
	 * Register custom post type for templates
	 */
	public function register_cpt() {

		// register templates as cpt
		register_post_type( 'wimj-templates' , apply_filters( $this->_hook_prefix . 'register_cpt', array(
			'labels'             => array(
				'name'               => esc_html__( 'Templates', WIMJ_SLUG ),
				'singular_name'      => esc_html__( 'Template', WIMJ_SLUG ),
				'menu_name'          => esc_html__( 'Template', WIMJ_SLUG ),
				'all_items'          => esc_html__( 'Templates', WIMJ_SLUG ),
				'add_new'            => esc_html__( 'Add New Template', WIMJ_SLUG ),
				'add_new_item'       => esc_html__( 'Add New Template', WIMJ_SLUG ),
				'edit_item'          => esc_html__( 'Edit Template', WIMJ_SLUG ),
				'edit'               => esc_html__( 'Edit', WIMJ_SLUG ),
				'new_item'           => esc_html__( 'New Template', WIMJ_SLUG ),
				'view_item'          => esc_html__( 'View Template', WIMJ_SLUG ),
				'search_items'       => esc_html__( 'Search Templates', WIMJ_SLUG ),
				'not_found'          => esc_html__( 'No Templates Found', WIMJ_SLUG ),
				'not_found_in_trash' => esc_html__( 'No Templates found in Trash', WIMJ_SLUG ),
				'view'               => esc_html__( 'View Template', WIMJ_SLUG )
			),
			'public'             => false,
			'show_ui'            => false,
			'capability_type'    => 'post',
			'hierarchical' => false,
			'rewrite' => false,
			'supports' => array( 'title' ), 
			'query_var' => false,
			'can_export' => true,
			'show_in_nav_menus' => false
		) ) );

		// register template categories as taxonomies
		if ( ! taxonomy_exists('wimj-template-categories') ) {

			register_taxonomy( 'wimj-template-categories', 'wimj-templates' , array(
				'labels' => array(
					'name' => esc_html__( 'Template Categories', WIMJ_SLUG ),  
					'singular_name'  => esc_html__( 'Template Category', WIMJ_SLUG ),  
					'search_items' => sprintf( esc_html__( 'Search %s', WIMJ_SLUG ) , esc_html__( 'Template Categories', WIMJ_SLUG ) ),  
					'popular_items' => sprintf( esc_html__( 'Popular %s', WIMJ_SLUG ) , esc_html__( 'Template Categories', WIMJ_SLUG ) ),  
					'all_items' => sprintf( esc_html__( 'All %s', WIMJ_SLUG ) , esc_html__( 'Template Categories', WIMJ_SLUG ) ),  
					'parent_item' => sprintf( esc_html__( 'Parent %s', WIMJ_SLUG ) , esc_html__( 'Template Category', WIMJ_SLUG ) ),  
					'edit_item' => sprintf( esc_html__( 'Edit %s', WIMJ_SLUG ) , esc_html__( 'Template Category', WIMJ_SLUG ) ),  
					'update_item' => sprintf( esc_html__( 'Update %s', WIMJ_SLUG ) , esc_html__( 'Template Category', WIMJ_SLUG ) ),  
					'add_new_item' => sprintf( esc_html__( 'Add New %s', WIMJ_SLUG ) , esc_html__( 'Template Category', WIMJ_SLUG ) ),  
					'new_item_name' => sprintf( esc_html__( 'New %s', WIMJ_SLUG ) , esc_html__( 'Template Category', WIMJ_SLUG ) ),  
					'separate_items_with_commas' => sprintf( esc_html__( 'Separate %s with commas', WIMJ_SLUG ) , esc_html__( 'Template Categories', WIMJ_SLUG ) ),  
					'add_or_remove_items' => sprintf( esc_html__( 'Add or remove %s', WIMJ_SLUG ) , esc_html__( 'Template Categories', WIMJ_SLUG ) ),  
					'choose_from_most_used' => sprintf( esc_html__( 'Choose from most used %s', WIMJ_SLUG ) , esc_html__( 'Template Categories', WIMJ_SLUG ) ) 
				),  
				'public'                        => false,  
				'hierarchical'                  => true,  
				'show_ui'                       => false,  
				'show_in_nav_menus'             => false,  
				'query_var'                     => true,
			) );

		} // end - taxonomy_exists('wimj-template-categories')
	}

	/**
	 * sample func
	 */
	public function sample_func() {
		


	}
	
} // end - WIMJ_Templates_Setup

return new WIMJ_Templates_Setup();

endif; // end - class_exists

