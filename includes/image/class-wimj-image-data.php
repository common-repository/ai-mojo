<?php
/**
 * Imafe Data class
 *
 * @class    WIMJ_Image_Data
 * @package  includes
 * @version  0.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WIMJ_Image_Data', false ) ) :

/**
 * Imafe Data class.
 */
class WIMJ_Image_Data {

	/**
	 * filter hook
	 *
	 * @var string
	 */
	private $_hook_prefix = 'WIMJ_Image_Data/';

	/**
	 * upload image to media library by url
	 */
	public function upload_image( $atts = array(
        'image_url' => '',
        'image_name' => '',
        'image_caption' => '',
        'image_description' => ''
    ) ) {

        $upload_dir       = wp_upload_dir(); // Set upload folder
        $image_data       = file_get_contents( $atts['image_url'] ); // Get image data
        $unique_file_name = wp_unique_filename( $upload_dir['path'], $atts['image_name'] ); // Generate unique name
        $filename         = basename( $unique_file_name ); // Create image file name

        // Check folder permission and define file location
        if ( wp_mkdir_p( $upload_dir['path'] ) ) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        // Create the image  file on the server
        file_put_contents( $file, $image_data );
        
        // Check image file type
        $wp_filetype = wp_check_filetype( $filename, null );
        
        // Set attachment data
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name( $filename ),
            'post_status'    => 'inherit',
            'post_excerpt' => ( !empty( $atts['image_caption'] ) ? sanitize_text_field( $atts['image_caption'] ) : '' ),
            'post_content' => ( !empty( $atts['image_caption'] ) ? sanitize_text_field( $atts['image_description'] ) : '' ),
        );
        
        // Create the attachment
        $attachment_id = wp_insert_attachment( $attachment, $file, ( isset( $atts['post_id'] ) ? $atts['post_id'] : null ), true );

        if ( is_wp_error( $attachment_id ) ) {
            return apply_filters( $this->_hook_prefix . 'upload_image' , $attachment_id, $atts );
        } else {
             // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
            if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
            }
    
            // Generate the metadata for the attachment, and update the database record.
            $attachment_data = wp_generate_attachment_metadata( $attachment_id, $file );
            wp_update_attachment_metadata( $attachment_id, $attachment_data );

            $attachment = wp_get_attachment_image_src($attachment_id, 'full');

            return apply_filters( $this->_hook_prefix . 'upload_image' , (object) array( 
                'id' => $attachment_id,
                'url' => $attachment[0],
                'width' => $attachment[1],
                'height' => $attachment[2],
                'metadata' => $attachment_data
            ), $atts );
        }

	}
	
} // end - WIMJ_Image_Data

/**
 * Get WIMJ_Image_Data instance
 *
 * @return object
 */
function wimj_image_data() {
	global $wimj_image_data;
	if ( !empty( $wimj_image_data ) ) {
		return $wimj_image_data;
	} else {
		$wimj_image_data = new WIMJ_Image_Data();
		return $wimj_image_data;
	}
}

endif; // end - class_exists

