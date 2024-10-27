<?php

/**
 * Freemius Init Functions
 *
 * @author 		WPAIMojo
 * @package 	includes
 * @since 		0.0.1
 *
 */

if ( !function_exists( 'wpaimojo_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wpaimojo_fs()
    {
        global  $wpaimojo_fs ;
        
        if ( !isset( $wpaimojo_fs ) ) {
            // Include Freemius SDK.
            require_once WIMJ_PATH . '/freemius/start.php';
            $wpaimojo_fs = fs_dynamic_init( array(
                'id'             => '8431',
                'slug'           => 'ai-mojo',
                'premium_slug'   => 'ai-mojo-pro',
                'type'           => 'plugin',
                'public_key'     => 'pk_dcfe754afdf66f6b3a3eb78f4c98d',
                'is_premium'     => false,
                'premium_suffix' => 'Pro',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                'slug'       => 'wpaimojo',
                'first-path' => 'admin.php?page=wpaimojo&view=getting-started',
                'contact'    => false,
                'support'    => false,
                'account'    => false,
            ),
                'is_live'        => true,
            ) );
        }
        
        return $wpaimojo_fs;
    }
    
    // Init Freemius.
    wpaimojo_fs();
    // Signal that SDK was initiated.
    do_action( 'wpaimojo_fs_loaded' );
}
