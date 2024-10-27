<?php
/**
 * sample functions
 *
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_sample_function') ) :
/**
 * Sample_functions
 *
 * @return string
 */
function wimj_sample_function() {

	$output = '';

	return apply_filters( 'wimj_sample_function', $output );
}
endif;