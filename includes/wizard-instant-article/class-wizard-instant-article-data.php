<?php
/**
 * Wizard Instant Article Data class
 *
 * @class    WIMJ_WIA_Data
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_WIA_Data', false ) ) :

/**
 * Wizard Instant Article Data class.
 */
class WIMJ_WIA_Data {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_WIA_Data/';

	/**
	 * return default keys
	 */
	public function keys() {
		return apply_filters( $this->_hook_prefix . 'keys', array(
            array( 'key' => 'topic', 'value' => '' , 'type' => 'string' ),
            array( 'key' => 'model', 'value' => '' , 'type' => 'string' ),
            array( 'key' => 'language', 'value' => 'ENGLISH' , 'type' => 'string' ),
            array( 'key' => 'title', 'value' => '' , 'type' => 'string' ),
            array( 'key' => 'title_selected_id', 'value' => '' , 'type' => 'string' ),
            array( 'key' => 'title_selected_text', 'value' => '' , 'type' => 'string' ),
            array( 'key' => 'title_results', 'value' => array() , 'type' => 'array' ),
            array( 'key' => 'description', 'value' => '' , 'type' => 'string' ),
            array( 'key' => 'description_selected_id', 'value' => '' , 'type' => 'string' ),
            array( 'key' => 'description_selected_text', 'value' => '' , 'type' => 'string' ),
            array( 'key' => 'description_results', 'value' => array() , 'type' => 'array' ),
            array( 'key' => 'outlines', 'value' => '' , 'type' => 'string' ),
            array( 'key' => 'outlines_selected_id', 'value' => '' , 'type' => 'string' ),
            array( 'key' => 'outlines_selected_text', 'value' => '' , 'type' => 'string' ),
            array( 'key' => 'outlines_results', 'value' => array() , 'type' => 'array' ),
            array( 'key' => 'contents_introduction', 'value' => array() , 'type' => 'array' ),
            array( 'key' => 'contents_outlines', 'value' => array() , 'type' => 'array' ),
            array( 'key' => 'contents_conclusion', 'value' => array() , 'type' => 'array' ),
        ));
	}

    /**
	 * get key data
	 */
	public function get_key_data( $key = '' ) {
        return wimj_wizard_get_key_data( $this->keys(), $key );
	}

    /**
	 * check if is valid key
	 */
	public function check_key( $key = '' ) {
        return wimj_wizard_check_key_exists( $this->keys(), $key );
	}

    /**
	 * return steps
	 */
	public function steps() {
		return apply_filters( $this->_hook_prefix . 'steps', array(
            'topic',
            'title',
            'description',
            'outlines',
            'review',
            'generation'
        ));
	}

	/**
	 * add default data into wizard
	 */
	public function add_default_data( $wizard = array() ) {
        return apply_filters( $this->_hook_prefix . 'add_default_data', wimj_wizard_add_default_data( $this->keys(), $wizard ), $wizard );
	}

	/**
	 * get meta data for wizard
	 */
	public function get_meta_data( $wizard_id, $wizard = array() ) {
        return apply_filters( $this->_hook_prefix . 'get_meta_data', wimj_wizard_get_meta_data( $this->keys(), $wizard_id, $wizard ), $wizard_id, $wizard );
	}

	/**
	 * trigger generation
     *
	 */
	public function trigger( $wizard_id , $params = array() ) {
        // validate data provided
        $validation = $this->_validate_data( $wizard_id, $params );
        if ( $validation && isset( $validation['status'] ) && $validation['status'] == 'error' && isset( $validation['message'] ) ) {
            return apply_filters( $this->_hook_prefix . 'trigger', array(
                'status' => 'error',
                'message' => $validation['message'],
            ) , $params );
        }

        $name = $params['name'];
        // update current step
        $current_step = wimj_wizard_update_current_step( $wizard_id, isset( $params['current_step'] ) && !empty( $params['current_step'] ) ? $params['current_step'] : 0 );
        $generate_data = $params['generate_data'];

        // update wizard title (if needed)
        wp_update_post( array(
            'ID' => $wizard_id,
            'post_title' => sanitize_text_field( $params['wizard_title'] )
        ) );

        // update wizard data
        $this->_update_step_data( $wizard_id, $name, $params );

        // generate completions        
        if ( $name !== 'review' && $name !== 'generation' ) {
            // get completions
            $completions = $this->_get_completions( $wizard_id, $name, $generate_data );

            // if completions returned not array, return as error
            if ( isset( $completions['status'] ) && !empty( $completions['status'] ) && $completions['status'] == 'error' ) {
                return apply_filters( $this->_hook_prefix . 'trigger_error', array(
                    'status' => 'error',
                    'message' => ( isset( $completions['message'] ) && !empty( $completions['message'] ) ? $completions['message'] : esc_html__( 'Error occurred while getting completions.', WIMJ_SLUG ) ),
                ) , $wizard_id, $params );
            }
    
            // add completions to results
            $this->_update_results( $wizard_id, $name , $completions );
        }

        // setup for generation
        if ( $name === 'generation' ) {
            $this->_setup_generation( $wizard_id );
        }

        return apply_filters( $this->_hook_prefix . 'trigger', array(
            'status' => 'success',
            'message' => ''
        ) , $params );
	}

    /**
	 * update wizard status
	 */
	public function update_status( $id, $status = 'setup' ) {
        return apply_filters( $this->_hook_prefix . 'update_status', wimj_wizard_update_status( $id, $status ), $id, $status );
	}

	/**
	 * add wizard contents
	 */
	public function add_contents( $wizard_id, $type = '', $content_type = '' , $content_id = '', $completions = array(), $content_key = 'content' ) {
        $done = false;
		// make sure key is valid
        if ( $this->check_key( $content_type ) ) {
            // add contents
            $done = wimj_wizard_add_content( $wizard_id, $content_type, $content_id, $completions, $content_key );

            // update status if is conclusion step
            if ( $done && $content_type === 'contents_conclusion' ) {
                $this->update_status( $wizard_id, 'completed' );
            }
        }
        return $done;
	}

    /**
	 * update wizard content
	 */
	public function update_content( $wizard_id, $type = '', $content_type = '' , $content_id = '', $content = '', $content_key = 'content', $variation_id = null ) {
        $done = false;
		// make sure key is valid
        if ( $this->check_key( $content_type ) ) {
            // update content
            $done = wimj_wizard_update_content( $wizard_id, $content_type, $content_id, $content, $content_key, $variation_id );
        }
        return $done;
	}

    /**
	 * add wizard contents variations
	 */
	public function add_contents_variations( $wizard_id, $type = '', $content_type = '' , $content_id = '', $completions = array(), $content_key = 'content', $auto_select = 'no' ) {
        $done = false;
		// make sure key is valid
        if ( $this->check_key( $content_type ) ) {
            // add content variations
            $done = wimj_wizard_add_contents_variations( $wizard_id, $content_type, $content_id, $completions, $content_key, $auto_select );
        }
        return $done;
	}

    /**
	 * update wizard content
	 */
	public function update_content_variation( $wizard_id, $type = '', $content_type = '' , $content_id = '', $content = '', $content_key = 'content', $variation_id = null ) {
        $done = false;
		// make sure key is valid
        if ( $this->check_key( $content_type ) ) {
            // update content
            $done = wimj_wizard_update_contents_variation( $wizard_id, $content_type, $content_id, $content, $content_key, $variation_id );
        }
        return $done;
	}

    /**
	 * select content variations
	 */
	public function select_content_variation( $wizard_id, $type = '', $content_type = '' , $content_id = '', $variation_id = '', $content_key = 'content' ) {
        $done = false;
		// make sure key is valid
        if ( $this->check_key( $content_type ) ) {
            $done = wimj_wizard_select_contents_variations( $wizard_id, $content_type, $content_id, $variation_id, $content_key );
        }
        return $done;
	}


	/**
	 * do error check on data provided
	 */
	private function _validate_data( $wizard_id , $params = array() ) {

        if ( !( isset( $params['name'] ) && in_array( $params['name'], $this->steps() ) ) )
            return array( 'status' => 'error', 'message' => esc_html__( 'Invalid Step ID provided', WIMJ_SLUG ) );

        if ( !( isset( $params['generate_data'] ) && !empty( $params['generate_data'] ) ) )
            return array( 'status' => 'error', 'message' => esc_html__( 'Invalid API Data Provided', WIMJ_SLUG ) );

        // do error check on step related data
        switch( $params['name'] ) {
            case 'title':
                if ( !( isset( $params['topic'] ) && !empty( $params['topic'] ) ) )
                    return array( 'status' => 'error', 'message' => esc_html__( 'Please insert a valid topic.', WIMJ_SLUG ) );
                break;
            case 'description':
                if ( !( isset( $params['title'] ) && !empty( $params['title'] ) ) )
                    return array( 'status' => 'error', 'message' => esc_html__( 'Please choose a valid article title.', WIMJ_SLUG ) );
                break;
            case 'outlines':
                if ( !( isset( $params['description'] ) && !empty( $params['description'] ) ) )
                    return array( 'status' => 'error', 'message' => esc_html__( 'Please choose a valid article description.', WIMJ_SLUG ) );
                break;
            case 'review':
                if ( !( isset( $params['outlines'] ) && !empty( $params['outlines'] ) ) )
                    return array( 'status' => 'error', 'message' => esc_html__( 'Please choose a valid article outline.', WIMJ_SLUG ) );
                break;
        }

        return false;
	}

	/**
	 * update step data to wizard
	 */
	private function _update_step_data( $wizard_id , $name = '' , $params = array() ) {
        switch( $name ) {
            case 'title':
                $topic = $this->get_key_data( 'topic' ); 
                update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $topic['key'], $this->_sanitize_data( $topic, $params ) );
                $model = $this->get_key_data( 'model' ); 
                update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $model['key'], $this->_sanitize_data( $model, $params ) );
                $language = $this->get_key_data( 'language' ); 
                update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $language['key'], $this->_sanitize_data( $language, $params ) );
                break;
            case 'description':
                $title = $this->get_key_data( 'title' ); 
                update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $title['key'], $this->_sanitize_data( $title, $params ) );
                $title_selected_id = $this->get_key_data( 'title_selected_id' ); 
                update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $title_selected_id['key'], $this->_sanitize_data( $title_selected_id, $params ) );
                $title_selected_text = $this->get_key_data( 'title_selected_text' ); 
                update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $title_selected_text['key'], $this->_sanitize_data( $title_selected_text, $params ) );
                break;
            case 'outlines':
                $description = $this->get_key_data( 'description' ); 
                update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $description['key'], $this->_sanitize_data( $description, $params ) );
                $description_selected_id = $this->get_key_data( 'description_selected_id' ); 
                update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $description_selected_id['key'], $this->_sanitize_data( $description_selected_id, $params ) );
                $description_selected_text = $this->get_key_data( 'description_selected_text' ); 
                update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $description_selected_text['key'], $this->_sanitize_data( $description_selected_text, $params ) );
                break;
            case 'review':
                $outlines = $this->get_key_data( 'outlines' ); 
                update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $outlines['key'], $this->_sanitize_data( $outlines, $params ) );
                $outlines_selected_id = $this->get_key_data( 'outlines_selected_id' ); 
                update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $outlines_selected_id['key'], $this->_sanitize_data( $outlines_selected_id, $params ) );
                $outlines_selected_text = $this->get_key_data( 'outlines_selected_text' ); 
                update_post_meta( $wizard_id, wpaimojo()->plugin_meta_prefix() . $outlines_selected_text['key'], $this->_sanitize_data( $outlines_selected_text, $params ) );
                break;
        }
	}

	/**
	 * sanitize data based on key
	 */
	private function _sanitize_data( $item = array(), $data = array() ) {
        $value = '';
        switch( $item['type'] ) {
            default:
                $value = wimj_wizard_sanitize_data( $item, $data );
                break;
        }
        return apply_filters( $this->_hook_prefix . '_sanitize_data', $value , $item, $data );
	}

	/**
	 * get completions from GPT-3
	 */
	private function _get_completions( $wizard_id, $name = '', $generate_data = array() ) {
        $completions = array();
        $local_time  = current_datetime();
        $current_time = $local_time->getTimestamp() + $local_time->getOffset();

        // generate completions
        $results = wimj_content_generation()->completion( $generate_data );

        // return error if found
        if ( isset( $results['status'] ) && !empty( $results['status'] ) && $results['status'] == 'error' )
            return apply_filters( $this->_hook_prefix . '_get_completions_error', $results, $wizard_id, $name, $generate_data );

        // compile completions
        if ( !empty( $results ) && is_array( $results ) ) {
            switch( $name ) {
                case 'title':
                    $index = 1;
                    $cid = '';
                    $titles = array();
                    $usage = [];
                    foreach ($results as $result) {
                        // break $result->text into array
                        $titles = explode( "\n", $result->text );
                        $cid = $result->id;
                        if ( isset( $result->usage ) && !empty( $result->usage ) ) {
                            $usage = $result->usage;
                        }  
                    }
                    if ( !empty( $titles ) && is_array( $titles ) ) {
                        foreach ($titles as $title) {
                            $cleanTitle = preg_replace( '/[[0-9]+\.\s|\n|\t|\-\s|\*\s|\#\s]/', '', $title );
                            $completions[] = array(
                                'id' => str_replace( '__1', '', $cid ) . '__' . $index,
                                'text' => trim( $cleanTitle ),
                                'added_on' => $current_time,
                                'usage' => $usage
                            );
                            $index++;
                        }
                    }
                    break;
                case 'description':
                case 'outlines':
                    foreach ($results as $result) {
                        $completions[] = $result;
                    }
                    break;
            }
        } // end - $results

        return apply_filters( $this->_hook_prefix . '_get_completions', $completions, $wizard_id, $name, $generate_data );
	}

    /**
	 * update results to wizard
	 */
	private function _update_results( $wizard_id, $name = '', $completions = array() ) {
        return wimj_wizard_update_results( $wizard_id, $name, $completions );
	}

    /**
	 * setup generation
	 */
	private function _setup_generation( $wizard_id ) {
        $wizard = $this->get_meta_data( $wizard_id );

        // change status to process
        $this->update_status( $wizard_id, 'process' );

        // intro
        $this->_setup_generation_intro( $wizard_id, $wizard );

        // outlines
        $this->_setup_generation_outlines( $wizard_id, $wizard );

        // conclusion
        $this->_setup_generation_conclusion( $wizard_id, $wizard );
	}

    /**
	 * setup generation for intro
	 */
	private function _setup_generation_intro( $wizard_id, $wizard = array() ) {
        $id = wimj_unique_id( 'cid-' );
        update_post_meta(
            $wizard_id, 
            wpaimojo()->plugin_meta_prefix() . 'contents_introduction',
            array(
                array(
                    'id' => $id,
                    'status' => 'process',
                    'type' => 'introduction',
                    'title' => '',
                    'content' => '',
                    'variations' => array(),
                )
            )
        );
	}

    /**
	 * setup generation for outlines
	 */
	private function _setup_generation_outlines( $wizard_id, $wizard = array() ) {
        // compile outlines
        $contents = array();
        $count = 1;
        // split outlines by line
        $outlines = explode( "\n", $wizard['outlines'] );
        if ( isset( $outlines ) && !empty( $outlines ) && is_array( $outlines ) ) {
            foreach ($outlines as $item) {
                // remove the dots, bullets, numbers, and dashes
                $itemClean = preg_replace( '/[[0-9]+\.\s|\n|\t|\-\s|\*\s|\#\s]/', '', $item );
                $id = wimj_unique_id( 'cid-' );
                $contents[] = array(
                    'id' => $id,
                    'status' => 'process',
                    'type' => 'outlines',
                    'number' => $count,
                    'title' => trim( $itemClean ),
                    'content' => '',
                    'variations' => array(),
                );
                $count++;
            }
        }
        update_post_meta(
            $wizard_id, 
            wpaimojo()->plugin_meta_prefix() . 'contents_outlines',
            $contents
        );
	}

    /**
	 * setup generation for conclusion
	 */
	private function _setup_generation_conclusion( $wizard_id, $wizard = array() ) {
        $id = wimj_unique_id( 'cid-' );
        update_post_meta(
            $wizard_id, 
            wpaimojo()->plugin_meta_prefix() . 'contents_conclusion',
            array(
                array(
                    'id' => $id,
                    'status' => 'process',
                    'type' => 'conclusion',
                    'title' => '',
                    'content' => '',
                    'variations' => array(),
                )
            )
        );
	}
	
} // end - WIMJ_WIA_Data

/**
 * Get WIMJ_WIA_Data instance
 *
 * @return object
 */
function wimj_wia() {
	global $wimj_wia;
	if ( !empty( $wimj_wia ) ) {
		return $wimj_wia;
	} else {
		$wimj_wia = new WIMJ_WIA_Data();
		return $wimj_wia;
	}
}

endif; // end - class_exists