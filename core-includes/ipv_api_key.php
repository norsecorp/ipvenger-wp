<?php

/**
 * file ipv_api_key.php
 *  
 * This file contains API License key related utility routines 
 *
*/

require_once( dirname( __FILE__ ) .
    '/../cms-includes/ipv_db_utils.php' );

require_once( 'ipv_call_ipv_api.php' );

/**
 * function ipv_keymaster: check whether a valid API key is in the db
 * 
 * @parm	(out) $key is set to an empty string or the string currently in db
 * @return	true if API key has been validated and stored, otherwise false
 * 
 * 
*/
function ipv_api_keymaster( & $api_key ) {

    $q_result = ipv_db_query( 
		'SELECT api_key, api_valid FROM ' . IPV_GLOBAL_SETTINGS .
		' WHERE configuration_id = 1' 
	);
	
	if ( $q_result ) {
		extract( ipv_db_fetch_assoc( $q_result ) );
		if ( $api_valid == '1' ) return true;
	}

	return false; 	// API is either invalid or never stored
}

/**
 * function ipv_invalidate_api_key: mark current key invalid in database
 * 
*/
function ipv_invalidate_api_key( $code ) {

    ipv_db_connect();
	
	switch ( $code ) {
        case 400:   $reason = 'bad format';break;
        case 401:   $reason = 'unauthorized';break;
        case 402:   $reason = 'expired';break;
		default:    $reason	= 'unauthorized';
	}

    // and update the primary configuration
	$q_str = 'UPDATE ' . IPV_GLOBAL_SETTINGS .
        " SET api_valid=0, api_reason='$reason' WHERE configuration_id = 1 ";

    $q_result = ipv_db_query( $q_str );

    ipv_db_cleanup();
}

/**
 * function ipv_validate_api_key: mark current key invalid in database
 * 
*/
function ipv_validate_api_key() {

    ipv_db_connect();
	
    // and update the primary configuration
	$q_str = 'UPDATE ' . IPV_GLOBAL_SETTINGS .
        ' SET api_valid=1, api_reason=\'valid\' WHERE configuration_id = 1 ';

    $q_result = ipv_db_query( $q_str );

    ipv_db_cleanup();
}

/**
 * function ipv_api_key_validate: validate API key against IPV server
 *
 * there is no explicit "validate this api key" service, so we just make
 * a generic ip request and ensure that the http status code is not
 * 400 "not a key" or 401 "unauthorized"
 *
 * @param	string	$key	key to be validated
 *
 * @return  string	One of "valid", "unauthorized", "expired", "bad format", "request failed"
 *
*/
function ipv_api_key_validate( $key ) {

    ipv_db_connect();

    $q_result = ipv_db_query( 
		'SELECT ipv_server_url FROM ' . IPV_GLOBAL_SETTINGS );

    extract( ipv_db_fetch_assoc( $q_result ) );

	$key = ipv_escape_string( $key );

    ipv_call_ipv_api( $ipv_server_url, $key, '4.2.2.2', $status );

    switch ( $status ) {
		case 302:   
			$reason = 'valid';
			$valid  = 1;
			break;
        case 400:   
			$reason = 'bad format';
			$valid  = 0;
			break;
        case 401:   
			$reason = 'unauthorized';
			$valid  = 0;
			break;
        case 402:   
			$reason = 'expired';
			$valid  = 0;
			break;
		default: 	
			$reason = 'request failed:' . $status;
			$valid  = 0;
			break;
    }

    // and update the primary configuration

    $q_result = ipv_db_query( 'UPDATE ' . IPV_GLOBAL_SETTINGS .
        " SET api_key='$key', api_valid = $valid , api_reason ='$reason'" .
        ' WHERE configuration_id = 1 ' );

    ipv_db_cleanup();

    return $reason;

}

function ipv_api_get_reason_text( $reason ) {

	$text = '';

	/* if no $reason, get the most recent one from the database */
	if ( ! isset( $reason ) ) {

		$q_result = ipv_db_query( 
			'SELECT api_reason as reason  FROM ' . IPV_GLOBAL_SETTINGS );

		extract( ipv_db_fetch_assoc( $q_result ) );

	}

	switch ( $reason ) {
		case 'valid':
			$text = 'Product Key has been validated.<br>' .
					'IPVenger security is now active.';
			break;
		case 'never':
			$text = 'Enter the key that was sent to you by email to begin ' . 
					'protecting your site from dangerous IPs.  To get a ' . 
					'product license, <a target="new" href="http://ipvenger.com/plans-and-pricing">visit the IPVenger website</a>.';
			break;
		case 'unauthorized':
			$text = 'This Product key has not been authorized.<br>' .
					'Please check the key again or <a target="new" href="http://support.ipvenger.com">visit support</a>';
			break;
		case 'expired':
			$text = 'This Product Key is expired.<br>' .
					'Please check the key again or <a target="new" href="http://account.ipvenger.com">update your account</a>';
			break;
		case 'bad format':
			$text = 'This not a valid Product Key format.<br>' .
					'Please check the key again or <a target="new" href="http://support.ipvenger.com">visit support</a>';
			break;
		default:
			$text = 'Unable to connect to key validation service.<br>' .
					'Please try again later or contact customer support.';
			break;
	}

	return $text;
}

?>
