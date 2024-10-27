<?php
/**
 * API related functions
 *
 * @package  includes
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_rest_api_external_trigger') ) :
/**
 * Trigger external api
 *
 * @return string
 */
function wimj_rest_api_external_trigger( $api_url = '', $formData = null, $method = 'POST', $headers = array() ) {
	$option = wimj_admin_settings()->get_option();
	$postfields = json_encode($formData);
	$curl = curl_init();
	$curl_props = [
		CURLOPT_URL => $api_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_SSL_VERIFYPEER => wimj_rest_api_to_verify_ssl(),
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => $method
	];

	if ( isset( $formData ) && !empty( $formData ) ) {
		$curl_props[ CURLOPT_POSTFIELDS ] = json_encode($formData);
	}

	if ( !empty( $headers ) ) {
		$curl_props[ CURLOPT_HTTPHEADER ] = $headers;
	}

	curl_setopt_array($curl, $curl_props);

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
		return array( 'code' => 'api_error', 'message' => esc_html__( "Error #:" , WIMJ_SLUG ) . $err );
	} else {
		return json_decode( $response, true );
	}
}
endif;

/* ------------------------------------------------------------------------------- */

if ( ! function_exists('wimj_rest_api_to_verify_ssl') ) :
/**
 * check if need to verify SSL when trigger external API
 *
 * @return string
 */
function wimj_rest_api_to_verify_ssl() {
	$verify = false;
	if ( is_ssl() ) {
		$verify = true;
		$option = wimj_admin_settings()->get_option();
		if ( isset( $option['settings_disable_ssl_verifypeer'] ) && !empty( $option['settings_disable_ssl_verifypeer'] ) && $option['settings_disable_ssl_verifypeer'] === 'yes' ) {
			$verify = false;
		} // end -$option['settings_disable_ssl_verifypeer']
	} // end - is_ssl
	return apply_filters( 'wimj_rest_api_to_verify_ssl', $verify );
}
endif;