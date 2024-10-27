<?php
/**
 * REST API Core Class
 *
 * @class    WIMJ_REST_API_Core
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_REST_API_Core', false ) ) :

/**
 * REST API Core class.
 */
class WIMJ_REST_API_Core {

    /**
	 * retrieve API key
	 */
	public function get_api_key( $data = array() ) {
        $key = '';
        if ( isset( $data['engine'] ) && !empty( $data['engine'] ) ) {
            $option = get_option( 'wpaimojo_' . $data['engine'] . '_api' );
            if ( isset( $option['location'] ) && !empty( $option['location'] ) ) {
                if ( 'database' === $option['location'] ) {
                    $key = ( isset( $option['key'] ) && !empty( $option['key'] ) ? $option['key'] : '' );
                } else if ( 'config' === $option['location'] ) {
                    if ( 'ai21' === $data['engine'] ) {
                        $key = ( defined('WPAIMOJO_AI21_API_KEY') ? WPAIMOJO_AI21_API_KEY : '' );
                    } else if ( 'openai' === $data['engine'] ) {
                        $key = ( defined('WPAIMOJO_OPENAI_API_KEY') ? WPAIMOJO_OPENAI_API_KEY : '' );
                    }
                } // end - $option['location']
            } // end - $option['location']
        } // end - $data['engine']
        return $key;
	}
	
} // end - WIMJ_REST_API_Core

endif; // end - class_exists

