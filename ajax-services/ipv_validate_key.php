<?php 

/**
 *
 *	ipv_validate_key.php:  ajax service script to validate api key
 *
 *	attempt to validate the given IPV api key, and print html suitable for 
 *  display (e.g. by alert());) 
 *
 *	Takes a single arg as POST variable:
 *
 *		key:  API key to validate
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

	if ( ! isset( $_POST['key'] ) ) {
		print 'Please specify a Product Key';
		return;
	}

	$key = $_POST['key'];

	if ( $key == '' ) {
		print 'Please specify a Product Key';
		return;
	}

	$rc = ipv_api_key_validate ( $key );

	print ipv_api_get_reason_text( $rc );

?>
