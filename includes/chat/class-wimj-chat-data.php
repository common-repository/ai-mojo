<?php
/**
 * Chat Data class
 *
 * @class    WIMJ_Chat_Data
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_Chat_Data', false ) ) :

/**
 * Wizard Data class.
 */
class WIMJ_Chat_Data {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private static $_hook_prefix = 'WIMJ_Chat_Data/';

	/**
	 * Retrieve all the chat personas
	 */
	public static function get_personas() {
		$list = self::_get_personas_settings();
		if ( !empty( $list ) && is_array( $list ) ) {
			foreach ( $list as $key => $value ) {
				$list[$key]['persona'] = isset( $value['messages'][0]['content'] ) ? $value['messages'][0]['content'] : '';
			}
		}
        return apply_filters( self::$_hook_prefix . 'get_personas', $list );
	}

	/**
	 * Update chat personas
	 */
	public static function update( $data = array() ) 
	{
		$list = array();
		if ( !empty( $data ) && is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				// setup messages
				$messages = array(
					array(
						'role' => 'system',
						'content' => $value['persona']
					)
				);
				// setup predefined messages
				$messages_count = 1;
				$predefined_messages = array();
				if ( !empty( $value['predefined_messages'] ) && is_array( $value['predefined_messages'] ) ) {
					foreach ( $value['predefined_messages'] as $k => $v ) {
						if ( isset( $v['role'] ) && (
							$v['role'] === 'user' || 
							$v['role'] === 'assistant'
						) && ( isset( $v['content'] ) && !empty( $v['content'] ) ) ) {
							$message = array(
								'role' => $v['role'],
								'content' => $v['content'],
								'visible' => isset( $v['visible'] ) && $v['visible'] === 'no' ? 'no' : 'yes'
							);
							$messages[] = $message;

							// add id to $message
							$message['id'] = $messages_count;
							$predefined_messages[] = $message;
							$messages_count++;
						} // end - $v
					}
				}
				$list[] = array(
					'id' => esc_attr( $value['id'] ),
					'name' => esc_attr( $value['name'] ),
					'model' => self::_get_model_by_id( isset( $value['model'] ) ? $value['model'] : '' ),
					'messages' => $messages,
					'predefined_messages' => $predefined_messages,
					'temp' => isset( $value['temp'] ) ? floatval( $value['temp'] ) : 0.7,
					'top_p' => isset( $value['top_p'] ) ? floatval( $value['top_p'] ) : 1,
					'token' => isset( $value['token'] ) ? intval( $value['token'] ) : 700,
					'frequency_penalty' => isset( $value['frequency_penalty'] ) ? floatval( $value['frequency_penalty'] ) : 0,
					'presence_penalty' => isset( $value['presence_penalty'] ) ? floatval( $value['presence_penalty'] ) : 0,
					'stop' => !empty( $value['stop'] ) && is_array( $value['stop'] ) ? $value['stop'] : array(),
					'default_persona' => $value['default_persona'] && $value['default_persona'] === 'yes' ? 'yes' : 'no'
				);
			}
		}
		self::_update_personas_settings( $list );
	}

	/** 
	 * Get model by id
	 */
	private static function _get_model_by_id( $id = '' ) {
		$list = self::_get_supported_models();
		if ( !empty( $list ) && is_array( $list ) ) {
			foreach ( $list as $model_id ) {
				if ( $model_id === $id ) {
					return $model_id;
				}
			}
		}
		return 'gpt-3.5-turbo';
	}

	/**
	 * Get personas from options
	 */
	private static function _get_personas_settings() {
		return get_option( wpaimojo()->plugin_options() . 'chat_personas', self::_get_default_personas() );
	}

	/**
	 * Update personas settings
	 */
	private static function _update_personas_settings( $data = array() ) {
		return update_option( wpaimojo()->plugin_options() . 'chat_personas', $data );
	}

	/**
	 * Get default chat personas
	 */
	private static function _get_default_personas() {
		return array(
			array(
				'id' => 'chatgpt',
				'name' => 'ChatGPT',
				'model' => 'gpt-3.5-turbo',
				'messages' => array(
					array(
						'role' => 'system',
						'content' => sprintf( esc_html__( 'You are ChatGPT, a large language model trained by OpenAI. Answer as concisely as possible. Knowledge cutoff: %s Current date: %s' , WIMJ_SLUG ), 'Sept 2021', date('Y-m-d') )
					)
				),
				'temp' => 0.9,
				'top_p' => 1,
				'token' => 600,
				'frequency_penalty' => 0,
				'presence_penalty' => 0,
				'stop' => array( '========' ),
				'default_persona' => 'yes'
			)
		);
	}

	/**
	 * Get default supporteed models
	 */
	private static function _get_supported_models() {
		return [
			'gpt-3.5-turbo',
			'gpt-3.5-turbo-0613',
			'gpt-3.5-turbo-16k',
			'gpt-3.5-turbo-16k-0613',
			'gpt-3.5-turbo-1106',
			'gpt-4',
			'gpt-4-0613',
			'gpt-4-1106-preview'
		];
	}


} // end - WIMJ_Chat_Data

endif; // end - class_exists