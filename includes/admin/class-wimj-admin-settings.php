<?php
/**
 * class for AI generated contents
 *
 * @class    WIMJ_Admin_Settings
 * @package  includes/admin
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_Admin_Settings', false ) && ! function_exists( 'wimj_admin_settings' ) ) :

/**
 * Admin Settings class.
 */
class WIMJ_Admin_Settings {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_Admin_Settings/';

	/**
	 * update data in gettting started wizard
     * 
     * @var $data - array
	 */
	public function update_wizard( $data = array() ) {
		$option = $this->get_option();
		$ai_engines = wimj_ai_engines_options();
        $option['wizard'] = 'completed';
        if ( isset( $data['api_engine'] ) && !empty( $data['api_engine'] ) && in_array( $data['api_engine'] , $ai_engines ) ) {
            $option['ai_engine_' . $data['api_engine']] = 'yes';
            $this->_update_ai_engine( $data['api_engine'], $data );
        } // end - $data['api_engine']
		// update option
        $this->update_option($option);
		return true;
	}

	/**
	 * check if is wizard completed
	 */
	public function is_wizard_completed() {
        $option = $this->get_option();
        return ( isset( $option['wizard'] ) && !empty( $option['wizard'] ) && $option['wizard'] === 'completed' ? true : false );
	}

	/**
	 * get option
     * 
     * @return array
	 */
	public function get_option() {
        $option = get_option( wpaimojo()->plugin_options() );
        if ( !( !empty( $option ) && is_array( $option ) ) ) {
			$option = array();
		}
        return $option;
	}

	/**
	 * update option
	 */
	public function update_option( $option ) {
        return update_option( wpaimojo()->plugin_options(), $option );
	}

	/**
	 * get wizard settings
	 */
	public function get_wizard_settings() {
		$wizards = wimj_wizards_options();
		$settings = array();
		foreach ( $wizards as $wizard ) {
			$settings[$wizard] = get_option( wpaimojo()->plugin_options() . '_wizard_' . $wizard, array() );
		}
		return $settings;
	}

	/**
	 * update wizard settings
	 */
	public function update_wizard_settings( $data = array() ) {
		$wizards = wimj_wizards_options();
		foreach ( $wizards as $wizard ) {
			if ( isset( $data[ $wizard ] ) ) {
				$this->_update_wizard_settings( $wizard, $data[ $wizard ] );
			}
		}
        return true;
	}

	/**
	 * update wizard settings
	 */
	private function _update_wizard_settings( $wizard = '', $data = array() ) {
        return update_option( wpaimojo()->plugin_options() . '_wizard_' . $wizard , array(
			'default_model' => ( isset( $data['default_model'] ) && !empty( $data['default_model'] ) ? esc_attr( $data['default_model'] ) : 'text-curie-001' ),
			'default_language' => ( isset( $data['default_language'] ) && !empty( $data['default_language'] ) ? esc_attr( $data['default_language'] ) : 'ENGLISH' ),
		) );
	}

	/**
	 * update ai engine
	 */
	private function _update_ai_engine( $engine = '' , $data = array() ) {
        return update_option( wpaimojo()->plugin_options() . '_' . $engine . '_api' , array(
            'location' => ( isset( $data['api_location'] ) && !empty( $data['api_location'] ) && ( $data['api_location'] === 'database' || $data['api_location'] === 'config' || $data['api_location'] === 'disabled' ) ? $data['api_location'] : '' ),
            'key' => ( isset( $data['api_key'] ) && !empty( $data['api_key'] ) ? $data['api_key'] : '' ),
        ) );
	}

	/**
	 * sample func
	 */
	public function sample_func() {


	}
	
} // end - WIMJ_Admin_Settings

/**
 * Get WIMJ_Admin_Settings instance
 *
 * @return object
 */
function wimj_admin_settings() {
	global $wimj_admin_settings;
	if ( !empty( $wimj_admin_settings ) ) {
		return $wimj_admin_settings;
	} else {
		$wimj_admin_settings = new WIMJ_Admin_Settings();
		return $wimj_admin_settings;
	}
}

endif; // end - class_exists

