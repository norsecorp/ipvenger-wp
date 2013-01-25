<?php 

/**
 *
 *	ipv_update_email.php:  update appeal settings
 *
 *  One POST variable args
 *
 * 		disable_appeals:		boolean 	don't allow blocked users to appeal
 *
*/

    /** Do not add any code before this include, which does security checks **/
    require( dirname( __FILE__ ) . '/ipv_prep_ajax.php' );

    require_once( dirname( __FILE__ ) .
        '/../cms-includes/ipv_cms_workarounds.php' );

    require_once( dirname( __FILE__ ) .
        '/../cms-includes/ipv_db_utils.php' );

	require_once( dirname( __FILE__ ) .  
		'/../core-includes/ipv_config_utils.php' );

	ipv_db_connect();

	if ( $_POST['disable_appeals'] == "true" ) {
		ipv_db_query( 'UPDATE ' . IPV_GLOBAL_SETTINGS .
			' SET appeals_enabled = FALSE WHERE configuration_id = 1 ' );
	}
	else {
		ipv_db_query( 'UPDATE ' . IPV_GLOBAL_SETTINGS .
			' SET appeals_enabled = TRUE WHERE configuration_id = 1 ' );
	}

	ipv_db_cleanup();

?>
