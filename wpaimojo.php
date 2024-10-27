<?php /*
Plugin Name: AI Mojo
Version: 0.9.2
Plugin URI: https://www.wpaimojo.com/
Description: ChatGPT / GPT-3 Playground for WordPress
Author: WPAIMojo
Author URI: https://www.wpaimojo.com

WordPress - 
Requires at least: 5.0.0
Tested up to: 6.4.1
Stable tag: 0.9.2

Text Domain: wpaimojo
Domain Path: /lang
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Plugin slug (for translation)
 **/

if(!defined('WIMJ_SLUG')) define( 'WIMJ_SLUG', 'wpaimojo' );

/**
 * Plugin version
 **/
if(!defined('WIMJ_VERSION')) define( 'WIMJ_VERSION', '0.9.2' );

/**
 * Plugin path
 **/
if(!defined('WIMJ_PATH')) define( 'WIMJ_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Plugin url
 **/
if(!defined('WIMJ_URL')) define( 'WIMJ_URL', plugin_dir_url( __FILE__ ) );


if ( !function_exists( 'wpaimojo_init' ) && ! function_exists( 'wpaimojo_fs' ) ) :

/**
 * Load plugin core class file
 */
require_once ( 'includes/freemius.php' );
require_once ( 'includes/wimj-functions.php' );
require_once ( 'includes/init.php' );

/**
 * Init core class
 *
 */
function wpaimojo_init() {

	global $wpaimojo;

	// Instantiate Plugin
	$wpaimojo = WPAIMOJO::get_instance();

	// Localization
	load_plugin_textdomain( WIMJ_SLUG , false , dirname( plugin_basename( __FILE__ ) ) . '/lang' );

}

add_action( 'plugins_loaded' , 'wpaimojo_init' );

/**
 * Get ai mojo instance
 *
 * @return object
 */
function wpaimojo() {
	global $wpaimojo;
	if ( !empty( $wpaimojo ) ) {
		return $wpaimojo;
	} else {
		$wpaimojo = WPAIMOJO::get_instance();
		return $wpaimojo;
	}
}

endif;