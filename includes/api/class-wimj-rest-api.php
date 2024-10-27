<?php

/**
 * REST API Core Class
 *
 * @class    WIMJ_REST_API
 * @package  includes
 * @version  0.0.1
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}


if ( !class_exists( 'WIMJ_REST_API', false ) ) {
    /**
     * REST API class.
     */
    class WIMJ_REST_API
    {
        /**
         * filter hook
         *
         * @var string
         */
        private  $_hook_prefix = 'WIMJ_REST_API/' ;
        /**
         * Constructor.
         */
        public function __construct()
        {
            // include all related files
            add_action( 'init', array( $this, 'includes' ), 0 );
        }
        
        /**
         * includes all api files
         */
        public function includes()
        {
            // functions
            include_once __DIR__ . '/wimj-rest-api-functions.php';
            // classes
            include_once __DIR__ . '/class-wimj-rest-api-core.php';
            include_once __DIR__ . '/class-wimj-rest-api-wizard.php';
            include_once __DIR__ . '/class-wimj-rest-api-settings.php';
            include_once __DIR__ . '/class-wimj-rest-api-custom_models.php';
            include_once __DIR__ . '/class-wimj-rest-api-aiengines.php';
            include_once __DIR__ . '/class-wimj-rest-api-generations.php';
            include_once __DIR__ . '/class-wimj-rest-api-templates.php';
            include_once __DIR__ . '/class-wimj-rest-api-notes.php';
            include_once __DIR__ . '/class-wimj-rest-api-wizard.php';
            include_once __DIR__ . '/class-wimj-rest-api-image.php';
            include_once __DIR__ . '/class-wimj-rest-api-quickaccess.php';
            include_once __DIR__ . '/class-wimj-rest-api-chat.php';
            include_once __DIR__ . '/class-wimj-rest-api-panel.php';
        }
    
    }
    // end - WIMJ_REST_API
    return new WIMJ_REST_API();
}

// end - class_exists