<?php
/**
 * ipv_prep_ajax.php
 *
 * Perform security checks and other housekeeping needed by ajax services
 * Every ajax service should include this before any other processing
 *
*/

	// make sure we are called by a valid, admin level user and have the 
	// correct session authentication (anti csrf) token

	session_start();
	
	if ( ! isset( $_SESSION['ipv_is_admin'] ) 	|| 
		 ! isset( $_SESSION['ipv_auth_token'] ) || 
		 ! isset( $_POST['ipv_auth_token'] ) 	|| 
		 ! $_SESSION['ipv_is_admin'] 			|| 
		 ! ( $_SESSION['ipv_auth_token'] == $_POST['ipv_auth_token' ] ) )
	{
		$err_string = 'Unauthorized  ipv_is_admin: ';

		if ( isset( $_SESSION['ipv_is_admin' ] ) ) $err_string .= "true, ";
		else $err_string .="false, ";

		$err_string .="ipv_auth_token: ";

		if ( isset( $_SESSION['ipv_auth_token' ] ) ) 
			$err_string .= $_SESSION['ipv_auth_token'] . ".";
		else $err_string .="UNINITIALIZED.";

		die( $err_string );	
	}

	/* load any cms specific workarounds necessary to separate ajax out */
	/* from the CMS loop */
	require( dirname( __FILE__ ) . '/../cms-includes/ipv_cms_workarounds.php' );

?>
