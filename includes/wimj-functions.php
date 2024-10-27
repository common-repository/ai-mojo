<?php
/**
 * Plugin functions
 *
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_get_meta') ) :
/**
 * Get meta value
 *
 * @param array $args = array (
 * 		@type int id - post ID
 * 		@type string key - meta key
 * 		@type mixed default - default value
 * 		@type bool single - whether to return only single result
 * 		@type string prefix - meta key prefix
 * )
 * @return mixed
 */
function wimj_get_meta( $args = array() ) {

	$defaults = array(
		'id' => null, 
		'key' => null,
		'default' => '',
		'single' => true,
		'prefix' => wpaimojo()->plugin_meta_prefix(),
		'esc' => null,
	);

	$instance = wp_parse_args( $args, $defaults );
	extract( $instance );

	if ( is_null( $id ) || is_null( $key ) )
		return;

	$value = get_post_meta( $id , $prefix . $key , $single );

	if ( isset( $value ) )
		$return = $value;
	else
		$return = $default;

	if ( !is_null( $esc ) ) {
		if ( $esc == 'attr' )
			$return = esc_attr( $return ); 
		elseif ( $esc == 'url' )
			$return = esc_url( $return ); 
	}

	return apply_filters( 'wimj_get_meta' , $return , $instance );
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_who_can_access') ) :
/**
 * 'Who can access' function
 *
 * @return string
 */
function wimj_who_can_access( $screen = null, $args = null ) {
	switch( $screen ) {
		case 'settings_page':
			return apply_filters( 'wimj_who_can_access', 'manage_options', $screen, $args );
		case 'blocks_scripts':
		default:
			return apply_filters( 'wimj_who_can_access', ( current_user_can( 'manage_options' ) ? true : false ), $screen, $args );
	}
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_ai_engines_options') ) :
/**
 * available AI engines
 *
 * @return string
 */
function wimj_ai_engines_options() {
	return apply_filters( 'wimj_ai_engines_options', array( 'ai21', 'openai' ) );
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_openai_models') ) :
/**
 * available OpenAI models
 *
 * @return string
 */
function wimj_openai_models() {
	return apply_filters( 'wimj_openai_models', array( 
		'text-davinci-insert-002',
		'text-davinci-insert-001',
		'text-davinci-edit-001',
		'davinci',
		'curie',
		'babbage',
		'ada',
		'davinci-instruct-beta-v3',
		'curie-instruct-beta-v2',
		'babbage-instruct-beta',
		'ada-instruct-beta',
		'text-davinci-002',
		'text-davinci-001',
		'text-curie-001',
		'text-babbage-001',
		'text-ada-001',
		// 'davinci-codex',
		// 'cushman-codex'
	) );
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_openai_chat_models') ) :
/**
 * available OpenAI models
 *
 * @return string
 */
function wimj_openai_chat_models() {
	return apply_filters( 'wimj_openai_chat_models', array( 
		'gpt-3.5-turbo',
		'gpt-3.5-turbo-0301',
	) );
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_wizards_options') ) :
/**
 * available wizards
 *
 * @return string
 */
function wimj_wizards_options() {
	return apply_filters( 'wimj_wizards_options', array( 
		'instant_article',
		'rewrite_article',
		'rewrite_article_v2'
	) );
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_unique_id') ) :
/**
 * Generate unique ID
 *
 * @return string
 */
function wimj_unique_id( $prefix = '' ) {
	$local_time  = current_datetime();
	$current_time = $local_time->getTimestamp() + $local_time->getOffset();
	return apply_filters( 'wimj_unique_id', $prefix . $current_time . rand( 1000, 9999 ), $prefix );
}
endif;