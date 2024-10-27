<?php
/**
 * Admin related functions
 *
 * @package  includes/admin
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/* ------------------------------------------------------------------------------- */

/**
 * get admin page url
 * 
 * @type view - which page to show
 * 
 * @return string
 */
function wimj_admin_get_page_url( $args = array() ) {

	$defaults = array(
		'page' => 'wpaimojo', 
		'view' => 'dashboard',
	);

	$instance = wp_parse_args( $args, $defaults );
	extract( $instance );

	$url 	= admin_url( 'admin.php' );
	$count	= 1;
	
	if ( !empty( $instance ) && is_array( $instance ) ) {
		foreach ( $instance as $key => $value ) {
			if ( !empty( $value ) ) {
				$url .= ( $count == 1 ? '?' : '&' ) . $key . '=' . $value;
				$count++;
			}				
		}
	}

	return apply_filters( 'wimj_admin_get_page_url' , esc_url( $url ) , $instance );
}