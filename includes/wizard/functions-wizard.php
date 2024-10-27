<?php
/**
 * Wizard related functions
 *
 * @package  includes
 * @version  0.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_wizard_get_key_data') ) :
/**
 * get wizard key data
 *
 * @return array
 */
function wimj_wizard_get_key_data( $keys = array(), $key = '' ) {
	$data = array();
	foreach( $keys as $item) {
		if ( $item['key'] === $key ) {
			$data = $item;
			break;
		}
	}
	return $data;
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_wizard_check_key_exists') ) :
/**
 * check if key exists
 *
 * @return bool
 */
function wimj_wizard_check_key_exists( $keys = array(), $key = '' ) {
	$valid = false;
	foreach( $keys as $item) {
		if ( $item['key'] === $key ) {
			$valid = true;
			break;
		}
	}
	return $valid;
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_wizard_get_meta_data') ) :
/**
 * get wizard meta data
 *
 * @return array
 */
function wimj_wizard_get_meta_data( $keys = array(), $wizard_id = '', $wizard = array() ) {
	$data = $wizard;
	foreach( $keys as $item) {
		switch( $item['key'] ) {
			default:
				$data[$item['key']] = wimj_get_meta( array( 'id' => $wizard_id, 'key' => $item['key'], 'default' => $item['value'] ));
				break;
		}
	}
	return $data;
}
endif;




/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_wizard_add_default_data') ) :
/**
 * add wizard default data
 *
 * @return array
 */
function wimj_wizard_add_default_data( $keys = array(), $wizard = array() ) {
	$data = $wizard;
	foreach( $keys as $item) {
		switch( $item['key'] ) {
			default:
				$data[$item['key']] = $item['value'];
				break;
		}
	}
	return $data;
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_wizard_update_current_step') ) :
/**
 * update wizard current step
 *
 * @return integer
 */
function wimj_wizard_update_current_step( $wizard_id, $new_step = 0 ) {
	$current_step = wimj_get_meta( array(
		'id' => $wizard_id,
		'key' => 'current_step',
		'default' => 0
	) );

	if ( $new_step > $current_step ) {
		$current_step = $new_step;
		update_post_meta( 
			$wizard_id,
			wpaimojo()->plugin_meta_prefix() . 'current_step',
			$current_step
		);
	}

	return $current_step;
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_wizard_update_status') ) :
/**
 * update wizard status
 *
 * @return integer|bool
 */
function wimj_wizard_update_status( $id, $status = 'setup' ) {
	return update_post_meta(
		$id, 
		wpaimojo()->plugin_meta_prefix() . 'status',
		esc_attr( $status )
	);
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_wizard_sanitize_data') ) :
/**
 * sanitize wizard data
 *
 * @return integer|string|array|object
 */
function wimj_wizard_sanitize_data( $item = array(), $data = array() ) {
	$value = '';
	switch( $item['type'] ) {
		case 'string':
			$value = ( isset( $data[ $item['key'] ] ) ? sanitize_textarea_field( $data[ $item['key'] ] ) : $item['value'] );
			break;
		case 'html':
			$value = ( isset( $data[ $item['key'] ] ) ? wp_kses_post( $data[ $item['key'] ] ) : $item['value'] );
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
			$value = ( isset( $data[ $item['key'] ] ) && !empty( $data[ $item['key'] ] ) && is_array( $data[ $item['key'] ]) ? $data[ $item['key'] ] : $item['value'] );
			break;
	}
	return $value;
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_wizard_update_results') ) :
/**
 * update wizard results
 *
 * @return integer|bool
 */
function wimj_wizard_update_results( $wizard_id, $name = '', $completions = array() ) {
	// get current results
	$results = wimj_get_meta( array( 'id' => $wizard_id, 'key' => $name . '_results', 'default' => array() ) );

	if ( !( !empty( $results ) && is_array( $results ) ) )
		$results = [];

	if ( !empty( $completions ) && is_array( $completions ) ) {
		foreach ($completions as $completion) {
			$results[] = $completion;
		}
	}
	return update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $name . '_results', $results );
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_wizard_add_content') ) :
/**
 * add content to wizard's contents
 *
 * @return integer|bool
 */
function wimj_wizard_add_content( $wizard_id, $content_type = '' , $content_id = '', $completions = array(), $content_key = 'content' ) {
	$done = false;
	// get current contents
	$contents = wimj_get_meta( array( 'id' => $wizard_id, 'key' => $content_type, 'default' => array() ) );

	if ( !( !empty( $contents ) && is_array( $contents ) ) )
		$contents = [];

	if ( !empty( $completions ) && is_array( $completions ) ) {
		$content = '';
		$prompts = array();
		$added_on = 0;
		$usage = array();
		$selected = 'no';
		foreach ($completions as $completion) {
			$content = $completion['text'];
			$prompts = ( isset( $completion['prompts'] ) && !empty( $completion['prompts'] ) && is_array( $completion['prompts'] ) ? $completion['prompts'] : array() );
			$added_on = $completion['added_on'];
			$usage = $completion['usage'];
		}
		// add to contents
		if ( !empty( $contents ) && is_array( $contents ) ) {
			foreach ( $contents as $key => $item) {
				if ( isset( $item['id'] ) && !empty( $item['id'] ) && $item['id'] === $content_id ) {
					$contents[$key]['status'] = 'completed';
					if ( isset( $content_key ) && !empty( $content_key ) && $content_key == 'content' ) {
						$contents[$key]['content'] = $content;
					} else if ( isset( $content_key ) && !empty( $content_key ) && $content_key == 'title' ) {
						$contents[$key]['title'] = $content;
					}
					$contents[$key]['added_on'] = $added_on;
					$contents[$key]['usage'] = $usage;
					// add to variations (if is empty)
					if ( !( isset( $contents[$key]['variations'] ) && !empty( $contents[$key]['variations'] ) && is_array( $contents[$key]['variations'] ) ) ) {
						$selected = 'yes';
						$contents[$key]['variations'] = array();
					}
					$contents[$key]['variations'][] = array(
						'id' => $content_id,
						'title' => ( isset( $content_key ) && !empty( $content_key ) && $content_key == 'title' ? $content : '' ),
						'content' => ( isset( $content_key ) && !empty( $content_key ) && $content_key == 'content' ? $content : '' ),
						'selected' => $selected,
						'prompts' => $prompts,
						'added_on' => $added_on,
						'usage' => $usage
					);
					$done = true;
					break;
				} // end - $item['id']
			} // end - $contents
		} // end - $contents
	} // end - $completions

	// update contents
	update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $content_type, $contents );
	return $done;
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_wizard_update_content') ) :
/**
 * update content in wizard's contents
 *
 * @return integer|bool
 */
function wimj_wizard_update_content( $wizard_id, $content_type = '' , $content_id = '', $content = '', $content_key = 'content', $variation_id = null ) {
	// get current contents
	$contents = wimj_get_meta( array( 'id' => $wizard_id, 'key' => $content_type, 'default' => array() ) );

	if ( !( !empty( $contents ) && is_array( $contents ) ) )
		$contents = [];

	// update contents
	if ( !empty( $contents ) && is_array( $contents ) ) {
		foreach ( $contents as $key => $item) {
			if ( isset( $item['id'] ) && !empty( $item['id'] ) && $item['id'] === $content_id ) {
				$contents[$key][$content_key] = $content;

				// update variant if needed
				if ( isset( $variation_id ) && !empty( $variation_id ) ) {
					foreach ( $contents[$key]['variations'] as $variation_key => $variation ) {
						if ( isset( $variation['id'] ) && !empty( $variation['id'] ) && $variation['id'] === $variation_id ) {
							$contents[$key]['variations'][$variation_key][$content_key] = $content;
							break;
						}
					}
				}
			} // end - $item['id']
		} // end - $contents
	} // end - $contents

	return update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $content_type, $contents );
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_wizard_add_contents_variations') ) :
/**
 * add wizard contents variations
 *
 * @return integer|bool
 */
function wimj_wizard_add_contents_variations( $wizard_id, $content_type = '' , $content_id = '', $completions = array(), $content_key = 'content', $auto_select = 'no' ) {
	// get current contents
	$contents = wimj_get_meta( array( 'id' => $wizard_id, 'key' => $content_type, 'default' => array() ) );

	if ( !( !empty( $contents ) && is_array( $contents ) ) )
		$contents = [];

	if ( !empty( $completions ) && is_array( $completions ) ) {
		$variaton_id = wimj_unique_id('vid-');
		$content = '';
		$prompts = array();
		$added_on = 0;
		$usage = array();
		foreach ($completions as $completion) {
			// $variaton_id = $completion['id'];
			$content = $completion['text'];
			$prompts = ( isset( $completion['prompts'] ) && !empty( $completion['prompts'] ) && is_array( $completion['prompts'] ) ? $completion['prompts'] : array() );
			$added_on = $completion['added_on'];
			$usage = $completion['usage'];
		}
		// add to contents
		if ( !empty( $contents ) && is_array( $contents ) ) {
			foreach ( $contents as $key => $item) {
				if ( isset( $item['id'] ) && !empty( $item['id'] ) && $item['id'] === $content_id ) {
					// add to variations (if is empty)
					if ( !( isset( $contents[$key]['variations'] ) && !empty( $contents[$key]['variations'] ) && is_array( $contents[$key]['variations'] ) ) ) {
						$contents[$key]['variations'] = array();
					}
					$contents[$key]['variations'][] = array(
						'id' => $variaton_id,
						'title' => ( isset( $content_key ) && !empty( $content_key ) && $content_key == 'title' ? $content : '' ),
						'content' => ( isset( $content_key ) && !empty( $content_key ) && $content_key == 'content' ? $content : '' ),
						'selected' => 'no',
						'prompts' => $prompts,
						'added_on' => $added_on,
						'usage' => $usage
					);

					// auto select if needed
					if ( isset( $auto_select ) && !empty( $auto_select ) && $auto_select == 'yes' ) {
						foreach ( $contents[$key]['variations'] as $variation_key => $variation ) {
							if ( isset( $variation['id'] ) && !empty( $variation['id'] ) && $variation['id'] === $variaton_id ) {
								$contents[$key]['variations'][$variation_key]['selected'] = 'yes';
								// update content
								$contents[$key][$content_key] = $content;
							} else {
								$contents[$key]['variations'][$variation_key]['selected'] = 'no';
							}
						}
					} // end - $auto_select
					break;
				} // end - $item['id']
			} // end - $contents
		} // end - $contents
	} // end - $completions
	return update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $content_type, $contents );
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_wizard_update_contents_variation') ) :
/**
 * update wizard contents variations
 *
 * @return integer|bool
 */
function wimj_wizard_update_contents_variation( $wizard_id, $content_type = '' , $content_id = '', $content = '', $content_key = 'content', $variation_id = '' ) {
	// get current contents
	$contents = wimj_get_meta( array( 'id' => $wizard_id, 'key' => $content_type, 'default' => array() ) );

	if ( !( !empty( $contents ) && is_array( $contents ) ) )
		$contents = [];

	// update contents
	if ( !empty( $contents ) && is_array( $contents ) ) {
		foreach ( $contents as $key => $item) {
			if ( isset( $item['id'] ) && !empty( $item['id'] ) && $item['id'] === $content_id ) {
				// update variant only
				if ( isset( $variation_id ) && !empty( $variation_id ) ) {
					foreach ( $contents[$key]['variations'] as $variation_key => $variation ) {
						if ( isset( $variation['id'] ) && !empty( $variation['id'] ) && $variation['id'] === $variation_id ) {
							$contents[$key]['variations'][$variation_key][$content_key] = $content;
							break;
						}
					}
				}
			} // end - $item['id']
		} // end - $contents
	} // end - $contents

	return update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $content_type, $contents );
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_wizard_select_contents_variations') ) :
/**
 * select wizard contents variations
 *
 * @return integer|bool
 */
function wimj_wizard_select_contents_variations( $wizard_id, $content_type = '' , $content_id = '', $variation_id = '', $content_key = 'content' ) {
	// get current contents
	$contents = wimj_get_meta( array( 'id' => $wizard_id, 'key' => $content_type, 'default' => array() ) );

	if ( !( !empty( $contents ) && is_array( $contents ) ) )
		$contents = [];

	// update contents
	if ( !empty( $contents ) && is_array( $contents ) ) {
		foreach ( $contents as $key => $item) {
			if ( isset( $item['id'] ) && !empty( $item['id'] ) && $item['id'] === $content_id ) {
				if ( isset( $contents[$key]['variations'] ) && !empty( $contents[$key]['variations'] ) && is_array( $contents[$key]['variations'] ) ) {
					foreach ($contents[$key]['variations'] as $vkey => $variation ) {
						if ( isset( $variation['id'] ) && !empty( $variation['id'] ) && $variation['id'] === $variation_id ) {
							$contents[$key]['variations'][$vkey]['selected'] = 'yes';
							// update content
							$contents[$key][$content_key] = $variation[$content_key];
						} else {
							$contents[$key]['variations'][$vkey]['selected'] = 'no';
						}
					}
				}
			} // end - $item['id']
		} // end - $contents
	} // end - $contents

	return update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $content_type, $contents );
}
endif;

	