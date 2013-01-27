<?php 

/**
 *
 *	ipv_update_email.php:  update email settings
 *
 *  Two POST variable args
 *
 * 		email_report:			boolean 	send marketing update to admin?
 * 		notification_address:	string 		admin email address
 * 		is_custom:				boolean		if false, email is CMS default
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

	require_once( dirname( __FILE__ ) .  
		'/../core-includes/ipv_validate_email.php' );

	ipv_db_connect();

	if ( $_POST['email_report'] == "true" ) $email_report = "true";
	else $email_report = "false";

	if ( $_POST['is_custom'] == "true" ) $is_custom = "true";
	else $is_custom = "false";

	$notification_address = 
		ipv_escape_string( $_POST[ 'notification_address' ] );

	if ( ! ipv_validate_email( $notification_address ) ) {
		echo 'invalid email';
		return;
	}

    $q_result = ipv_db_query( 'UPDATE ' . IPV_GLOBAL_SETTINGS .
        ' SET notification_email=\'' . $notification_address . '\', ' .
        ' 	  receive_update_email=' . $email_report . ', ' .
        ' 	  notification_is_custom=' . $is_custom . ' ' .
        ' WHERE configuration_id = 1 ' );

	ipv_db_cleanup();

?>
