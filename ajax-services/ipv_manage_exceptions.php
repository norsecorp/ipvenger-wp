<?php

/**
 *
 *	ipv_add_exception.php:  update blacklist/whitelist configuration
 *
 *  The following (string) arguments are required as post variables
 *
 *		action:  	<"add"|"delete"|"delete_mask"|"list">
 *		rule_type:  type of rule to handle <"whitelist"|"blacklist"|"risk">
 *		field:  	type from ipv_exception_type table (e.g. "ip", "country" )
 *
 *	For action="add", the following (string) argument is required
 *
 *		mask:  		mask to test against (e.g. IP address or country name
 *
 *	Additionally, action="add";rule_type="risk", requires
 *
 *		threshold:	IPQ cutoff for requests matching mask
 *
 *	For action="delete", the following (string) argument is required
 *
 *		list:		list of database keys to delete
 *
 *
 *	"Returns" the current rules of the specified $rule_type and $field, after
 *	updates resulting from the call, as a json encoded array of {key=,mask=}
 *	pairs, or {key=,mask=,threshold=} triples, in the case of rule_type="risk"
 *
*/

    /** Do not add any code before this include, which does security checks **/
    require( dirname( __FILE__ ) . '/ipv_prep_ajax.php' );

    require_once( dirname( __FILE__ ) .
        '/../cms-includes/ipv_cms_workarounds.php' );

	require_once( dirname( __FILE__ ) .
		'/../core-includes/ipv_config_utils.php' );

	if (!function_exists('__check_if_int')) {
	    function __check_if_int($val) {
	        if (preg_match('/\D+/', $val)) {
	            echo 'Invalid POST data';
	            exit;
	        }
	    }
	}

	// see if we are called as a add or delete action, if so do it

	$action 	= $_POST['action'];
	$rule_type 	= $_POST['rule_type'];
	$field 		= $_POST['field'];

	if ( $action == 'delete' ) {
		$key_list = $_POST['list'];
		if ( $key_list && is_array($key_list)) {
		    array_walk($key_list, '__check_if_int');
			foreach ( $key_list as $key )
				ipv_delete_by_key( $rule_type, $key );
		}
	}
	else if ( $action == 'delete_mask' ) {
		$mask = $_POST['mask'];
		ipv_delete_by_mask( $rule_type, $mask );
	}
	else if ( $action == 'add' ) {

		$mask  = $_POST['mask'];

		// "auto detect" mask type - currently the only wildcards
		// allowed are simplified regex strings limited to "*" wildcards,
		// so just see if the mask contains any *'s

		if ( strpos($mask, '*') === false ) {
			$mask_type='exact';
		}
		else {
			$mask_type='wildcard';
		}

		switch ( $rule_type ) {
			case 'whitelist':
				ipv_delete_by_mask( 'blacklist', $mask ); //just in case
				ipv_add_whitelist( $field, $mask, $mask_type );
				break;
			case 'blacklist':
				ipv_delete_by_mask( 'whitelist', $mask ); //just in case
				ipv_add_blacklist( $field, $mask, $mask_type );
				break;
		}
	}

	$rules = ipv_get_rules_array( $rule_type, $field );

	echo json_encode( $rules );

?>
