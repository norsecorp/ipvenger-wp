<?php 

/**
 *
 *	ipv_is_key_valid.php:  ajax service script to see if api is valid
 * 
 *  returns JSON encoded object with two members:
 * 	api_valid: 	 	bool 
 *  reason_text:	message to display to user
 *
*/
    /** Do not add any code before this include, which does security checks **/
    require( dirname( __FILE__ ) . '/ipv_prep_ajax.php' );

	require_once( dirname( __FILE__ ) .
		'/../cms-includes/ipv_cms_workarounds.php' );

	require_once( dirname( __FILE__ ) .  
		'/../cms-includes/ipv_db_utils.php' );

	require_once( dirname( __FILE__ ) .  
		'/../core-includes/ipv_api_key.php' );

	ipv_db_connect();

    $q_result = ipv_db_query(
        'SELECT api_valid, api_reason from ' . IPV_GLOBAL_SETTINGS .
        ' WHERE configuration_id = 1'
    );

    if ( $q_result ) {

        extract( ipv_db_fetch_assoc( $q_result ) );

		$text = ipv_api_get_reason_text( $api_reason );

		$return = array( 'api_valid' => $api_valid, 'reason_text' => $text );		
		echo json_encode( $return );
    }

	ipv_db_cleanup();

?>
