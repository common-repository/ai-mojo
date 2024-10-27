<?php
/**
 * Content Generation class
 *
 * @class    WIMJ_Content_Generation
 * @package  includes
 * @version  0.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_Content_Generation', false ) ) :

/**
 * Content Generation class.
 */
class WIMJ_Content_Generation {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_Content_Generation/';


	/**
	 * generate completion
	 */
	public function completion( $data = array() ) {

		$api_key = $this->_get_api_key( $data );
        $api_url = $this->_get_api_url( $data );
        $api_data = $this->_compile_api_data( $data );

        // check for error
        if ( !( isset( $api_key ) && !empty( $api_key ) ) )
			return array( 'status' => 'error', 'message' => esc_html__( 'Missing API key' , WIMJ_SLUG ) );

        if ( !( isset( $api_url ) && !empty( $api_url ) ) )
			return array( 'status' => 'error', 'message' => esc_html__( 'Missing API URL' , WIMJ_SLUG ) );

        $results = wimj_rest_api_external_trigger( $api_url, $api_data, 'POST', array(
            'Content-Type: application/json',
			'Authorization: Bearer ' . $api_key
        ) );

        $results = $this->_check_error_by_engine( $results, $data['engine'] );

        if ( isset( $results['status'] ) && !empty( $results['status'] ) && $results['status'] == 'error' ) {
            return apply_filters( $this->_hook_prefix . 'completion_error', array(
                'status' => 'error',
                'message' => ( isset( $results['message'] ) && !empty( $results['message'] ) ? $results['message'] : esc_html__( 'Error occurred while getting completions.', WIMJ_SLUG ) ),
            ) , $data );
        }

		return $this->_render_results_by_engine( $results, $data['engine'], $data );
	}

	/**
	 * retrieve API key
	 */
	private function _get_api_key( $data = array() ) {
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

	/**
	 * render results based on engine
	 */
	private function _render_results_by_engine( $results, $engine = 'openai', $data = array() ) {
        $list = array();
        $count = 0;
		$local_time  = current_datetime();
        $current_time = $local_time->getTimestamp() + $local_time->getOffset();
        switch( $engine ) {
            case 'ai21':
                if ( isset( $results['completions'] ) && !empty( $results['completions'] ) && is_array( $results['completions'] ) ) {
                    foreach( $results['completions'] as $result ) {
                        $count++;
                        if ( isset( $result['data'] ) && isset( $result['data']['text'] ) && !empty( $result['data']['text'] ) ) {
                            $list[] = (object) array(
                                'id' => $results['id'] . '__' . $count,
                                'text' => $this->_filter_completion_text( $result['data']['text'], $data['stop'], $data ),
								'added_on' => $current_time
                            );
                        } // end - $result['data']['text']
                    }
                } // end - $results['completions']
                break;
            case 'openai':
                if ( isset( $data['mode'] ) && !empty($data['mode']) && (
                    $data['mode'] === 'dalle_generations' || 
                    $data['mode'] === 'dalle_edits' || 
                    $data['mode'] === 'dalle_variations'
                ) ) {
                    if ( isset( $results['data'] ) && !empty( $results['data'] ) && is_array( $results['data'] ) ) {
                        foreach( $results['data'] as $result ) {
                            $count++;
                            if ( isset( $result['url'] ) && !empty( $result['url'] ) ) {
                                $list[] = (object) array(
                                    'id' => wimj_unique_id('dalle_') . '__' . $count,
                                    'url' => $result['url'],
                                    'added_on' => $results['created'] ? $results['created'] : $current_time,
                                );
                            } // end - $result['text']
                        }
                    } // end - $results['completions']
                } else if ( isset( $data['mode'] ) && !empty($data['mode']) && $data['mode'] === 'chat' ) {
                    if ( isset( $results['choices'] ) && !empty( $results['choices'] ) && is_array( $results['choices'] ) ) {
                        foreach( $results['choices'] as $result ) {
                            $count++;
                            if ( isset( $result['message']['content'] ) && !empty( $result['message']['content'] ) ) {
                                $list[] = (object) array(
                                    'id' => $results['id'] . '__' . $count,
                                    'text' => $result['message']['content'],
                                    'added_on' => $results['created'] ? $results['created'] : $current_time,
                                    'usage' => ( isset( $results['usage'] ) && !empty( $results['usage'] ) ? $results['usage'] : [] )
                                );
                            } // end - $result['text']
                        }
                    } // end - $results['completions']
                } else {
                    if ( isset( $results['choices'] ) && !empty( $results['choices'] ) && is_array( $results['choices'] ) ) {
                        foreach( $results['choices'] as $result ) {
                            $count++;
                            if ( isset( $result['text'] ) && !empty( $result['text'] ) ) {
                                $list[] = (object) array(
                                    'id' => $results['id'] . '__' . $count,
                                    'text' => ( isset( $data['mode'] ) && !empty($data['mode']) && $data['mode'] === 'insert' ? $result['text'] : $this->_filter_completion_text( $result['text'], $data['stop'], $data ) ),
                                    'added_on' => $results['created'] ? $results['created'] : $current_time,
                                    'usage' => ( isset( $results['usage'] ) && !empty( $results['usage'] ) ? $results['usage'] : [] )
                                );
                            } // end - $result['text']
                        }
                    } // end - $results['completions']
                }
                break;    
        }

        return $list;
	}

    /**
	 * filter completions text
	 */
	private function _filter_completion_text( $text = '', $filters = array(), $data = array() ) {
        $text = str_replace("<|endoftext|>","", $text );
        if ( !empty( $filters ) && is_array( $filters ) ) {
            foreach( $filters as $filter ) {
                $text = str_replace($filter,"", $text );
            }
        } // end - $filters
        return ( isset( $data['start_text'] ) && !empty( $data['start_text'] ) ? esc_attr( $data['start_text'] ) : '' ) . trim( $text );
	}

    /**
	 * compile API form data
	 */
	private function _compile_api_data( $data ) {
        $api_data = array();
        if ( isset( $data['engine'] ) && !empty( $data['engine'] ) ) {
            if ( 'ai21' === $data['engine'] ) {
                $api_data = $this->_compile_ai21_data( $data );
            } else if ( 'openai' === $data['engine'] ) {
                $api_data = $this->_compile_openai_data( $data );
            }
        } // end - $data['engine']
        return $api_data;
	}

    /**
	 * compile API form data for AI21
	 */
	private function _compile_ai21_data( $data ) {
        $api_data = array();
        // prompt
        if ( isset( $data['prompt'] ) && !empty( $data['prompt'] ) ) {
            $api_data['prompt'] = $data['prompt'] ;
        }
        // token
        if ( isset( $data['token'] ) ) {
            $api_data['maxTokens'] = $data['token'];
        }
        // temp
        if ( isset( $data['temp'] ) ) {
            $api_data['temperature'] = $data['temp'] ;
        }
        // top_p
        if ( isset( $data['top_p'] ) ) {
            $api_data['topP'] = $data['top_p'] ;
        }
        // stop
        if ( isset( $data['stop'] ) && !empty( $data['stop'] ) ) {
            $api_data['stopSequences'] = $data['stop'] ;
        }

        return apply_filters( $this->_hook_prefix . '_compile_ai21_data' , $api_data , $data );
	}

    /**
	 * compile API form data for OpenAI
	 */
	private function _compile_openai_data( $data ) {
        $mode = isset( $data['mode'] ) && !empty( $data['mode'] ) ? $data['mode'] : 'complete' ;
        $is_insert_mode = isset( $data['mode'] ) && !empty( $data['mode'] ) && $data['mode'] === 'insert' ? true : false ;
        $api_data = array();

        switch( $mode ) {
            case 'insert':
                // prompt
                if ( isset( $data['prompt'] ) && !empty( $data['prompt'] ) ) {
                    $api_data['prompt'] = $data['prompt'] ;
                }
                // suffix
                if ( isset( $data['suffix'] ) && !empty( $data['suffix'] ) ) {
                    $api_data['suffix'] = $data['suffix'] ;
                }
                // temp
                if ( isset( $data['temp'] ) ) {
                    $api_data['temperature'] = $data['temp'] ;
                }
                // top_p
                if ( isset( $data['top_p'] ) ) {
                    $api_data['top_p'] = $data['top_p'] ;
                }
                // token
                if ( isset( $data['token'] ) ) {
                    $api_data['max_tokens'] = $data['token'];
                }
                // presence_penalty
                if ( isset( $data['presence_penalty'] ) ) {
                    $api_data['presence_penalty'] = $data['presence_penalty'] ;
                }
                // frequency_penalty
                if ( isset( $data['frequency_penalty'] ) ) {
                    $api_data['frequency_penalty'] = $data['frequency_penalty'] ;
                }
                // stop
                if ( isset( $data['stop'] ) && !empty( $data['stop'] ) ) {
                    $api_data['stop'] = $data['stop'] ;
                }
                break;
            case 'edit':
                // input
                if ( isset( $data['prompt'] ) && !empty( $data['prompt'] ) ) {
                    $api_data['input'] = $data['prompt'] ;
                }
                // instructions
                if ( isset( $data['instructions'] ) && !empty( $data['instructions'] ) ) {
                    $api_data['instruction'] = $data['instructions'] ;
                }
                // temp
                if ( isset( $data['temp'] ) ) {
                    $api_data['temperature'] = $data['temp'] ;
                }
                // top_p
                if ( isset( $data['top_p'] ) ) {
                    $api_data['top_p'] = $data['top_p'] ;
                }
                break;
            case 'dalle_generations':
                // prompt
                if ( isset( $data['prompt'] ) && !empty( $data['prompt'] ) ) {
                    $api_data['prompt'] = $data['prompt'] ;
                }
                // num_results
                if ( isset( $data['n'] ) ) {
                    $api_data['n'] = $data['n'] ;
                }
                // size
                if ( isset( $data['size'] ) ) {
                    $api_data['size'] = $data['size'] ;
                }
                // response_format
                $api_data['response_format'] = 'url' ;
                break;
            case 'dalle_edits':
                // image
                if ( isset( $data['image'] ) && !empty( $data['image'] ) ) {
                    $api_data['image'] = $data['image'] ;
                }
                // image
                if ( isset( $data['mask'] ) && !empty( $data['mask'] ) ) {
                    $api_data['mask'] = $data['mask'] ;
                }
                // prompt
                if ( isset( $data['prompt'] ) && !empty( $data['prompt'] ) ) {
                    $api_data['prompt'] = $data['prompt'] ;
                }
                // num_results
                if ( isset( $data['n'] ) ) {
                    $api_data['n'] = $data['n'] ;
                }
                // size
                if ( isset( $data['size'] ) ) {
                    $api_data['size'] = $data['size'] ;
                }
                // response_format
                $api_data['response_format'] = 'url' ;
                break;
            case 'dalle_variations':
                // image
                if ( isset( $data['image'] ) && !empty( $data['image'] ) ) {
                    $api_data['image'] = $data['image'] ;
                }
                // num_results
                if ( isset( $data['n'] ) ) {
                    $api_data['n'] = $data['n'] ;
                }
                // size
                if ( isset( $data['size'] ) ) {
                    $api_data['size'] = $data['size'] ;
                }
                // response_format
                $api_data['response_format'] = 'url' ;
                break;
            case 'chat':
                // model
                if ( isset( $data['model'] ) && !empty( $data['model'] ) ) {
                    $api_data['model'] = $data['model'] ;
                }
                // messages
                if ( isset( $data['messages'] ) && !empty( $data['messages'] ) && is_array( $data['messages'] ) ) {
                    $api_data['messages'] = $data['messages'] ;
                }
                // temp
                if ( isset( $data['temp'] ) ) {
                    $api_data['temperature'] = $data['temp'] ;
                }
                // top_p
                if ( isset( $data['top_p'] ) ) {
                    $api_data['top_p'] = $data['top_p'] ;
                }
                // token
                if ( isset( $data['token'] ) ) {
                    $api_data['max_tokens'] = $data['token'];
                }

                // presence_penalty
                if ( isset( $data['presence_penalty'] ) ) {
                    $api_data['presence_penalty'] = $data['presence_penalty'] ;
                }
                // frequency_penalty
                if ( isset( $data['frequency_penalty'] ) ) {
                    $api_data['frequency_penalty'] = $data['frequency_penalty'] ;
                }
                // stop
                if ( isset( $data['stop'] ) && !empty( $data['stop'] ) ) {
                    $api_data['stop'] = $data['stop'] ;
                }
                break;
            default:
                // prompt
                if ( isset( $data['prompt'] ) && !empty( $data['prompt'] ) ) {
                    $api_data['prompt'] = $data['prompt'] ;
                }
                // model
                if ( isset( $data['model'] ) && !empty( $data['model'] ) && !in_array( $data['model'] , wimj_openai_models() ) ) {
                    $api_data['model'] = $data['model'] ;
                }
                // temp
                if ( isset( $data['temp'] ) ) {
                    $api_data['temperature'] = $data['temp'] ;
                }
                // top_p
                if ( isset( $data['top_p'] ) ) {
                    $api_data['top_p'] = $data['top_p'] ;
                }
                // token
                if ( isset( $data['token'] ) ) {
                    $api_data['max_tokens'] = $data['token'];
                }

                // presence_penalty
                if ( isset( $data['presence_penalty'] ) ) {
                    $api_data['presence_penalty'] = $data['presence_penalty'] ;
                }
                // frequency_penalty
                if ( isset( $data['frequency_penalty'] ) ) {
                    $api_data['frequency_penalty'] = $data['frequency_penalty'] ;
                }
                // n
                if ( isset( $data['n'] ) && !empty( $data['n'] ) ) {
                    $api_data['n'] = intval( $data['n'] );
                }
                // stop
                if ( isset( $data['stop'] ) && !empty( $data['stop'] ) ) {
                    $api_data['stop'] = $data['stop'] ;
                }
                break;
        }

        return apply_filters( $this->_hook_prefix . '_compile_openai_data' , $api_data , $data );
	}

    /**
	 * get API url
	 */
	private function _get_api_url( $data ) {
        $url = '';
        if ( isset( $data['engine'] ) && !empty( $data['engine'] ) ) {
            if ( 'ai21' === $data['engine'] ) {
                $url = 'https://api.ai21.com/studio/v1/' . ( isset( $data['model'] ) && !empty( $data['model'] ) ? $data['model'] : '' ) . '/complete';
            } else if ( 'openai' === $data['engine'] ) {
                if ( isset( $data['mode'] ) && !empty( $data['mode'] ) && $data['mode'] === 'edit' ) {
                    $url = 'https://api.openai.com/v1/engines/' . ( isset( $data['model'] ) && !empty( $data['model'] ) ? $data['model'] : '' ) . '/edits';
                } else if ( isset( $data['mode'] ) && !empty( $data['mode'] ) && $data['mode'] === 'chat' ) {
                    $url = 'https://api.openai.com/v1/chat/completions';
                } else if ( isset( $data['mode'] ) && !empty( $data['mode'] ) && (
                    $data['mode'] === 'dalle_generations' || 
                    $data['mode'] === 'dalle_edits' || 
                    $data['mode'] === 'dalle_variations'
                ) ) {
                    // remove dalle_ from mode
                    $url = 'https://api.openai.com/v1/images/' . str_replace( 'dalle_' , '' , $data['mode'] );
                } else {
                    if ( in_array( $data['model'] , wimj_openai_models() ) ) {
                        $url = 'https://api.openai.com/v1/engines/' . ( isset( $data['model'] ) && !empty( $data['model'] ) ? $data['model'] : '' ) . '/completions';
                    } else {
                        $url = 'https://api.openai.com/v1/completions';
                    }
                }
            }
        } // end - $data['engine']
        return $url;
	}

    /**
	 * check for API error by engine
	 */
	private function _check_error_by_engine( $results, $engine = 'openai' ) {
        $error = null;
        switch( $engine ) {
            case 'ai21':
                if ( isset( $results['detail'] ) && !empty( $results['detail'] ) ) {
                    if ( is_array( $results['detail'] ) ) {
                        foreach( $results['detail'] as $err ) {
                            if ( isset( $err['msg'] ) && !empty( $err['msg'] ) ) {
                                $error = array(
                                    'status' => 'error',
                                    'message' => esc_attr( $err['msg'] )
                                );
                            }
                        }
                    } else {
                        $error = array(
                            'status' => 'error',
                            'message' => esc_attr( $results['detail'] )
                        );
                    } // end - $results['detail']
                } // end - $results['detail']
                break;
            case 'openai':
                if ( isset( $results['error'] ) && !empty( $results['error'] ) ) {
                    $error = array(
                        'status' => 'error',
                        'message' => ( isset( $results['error']['message'] ) && !empty( $results['error']['message'] ) ? esc_attr( $results['error']['message'] ) : __( 'Unknown API Error' , WIMJ_SLUG ) )
                    );
                } // end - $results['error']
                break;    
        }
        return ( isset( $error ) && !empty( $error ) ? $error : $results );
	}
	
} // end - WIMJ_Content_Generation

/**
 * Get WIMJ_Content_Generation instance
 *
 * @return object
 */
function wimj_content_generation() {
	global $wimj_content_generation;
	if ( !empty( $wimj_content_generation ) ) {
		return $wimj_content_generation;
	} else {
		$wimj_content_generation = new WIMJ_Content_Generation();
		return $wimj_content_generation;
	}
}

endif; // end - class_exists