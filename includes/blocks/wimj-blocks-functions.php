<?php
/**
 * Blocks Related functions
 *
 * @package  includes/blocks
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Sample_functions
 *
 * @return string
 */
function wimj_sample_function() {

    $output = '';

	return apply_filters( 'wimj_sample_function', $output );
}