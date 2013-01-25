<?php

/**
 * function ipv_validate_email - syntax and mx record check on 
 * 
 * @param email string - email address to validate 
 * 
*/
function ipv_validate_email( $email ) {

    /* validate email format and DNS domain record */

    $email_valid = false;
	
    // BAD for earlier PHP versions exploits    
    if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) )
    {
        $domain = substr($email, strrpos( $email, '@' ) + 1);

		// Windows servers with php < 5.3.0 don't support checkdnsrr
		if ( function_exists( "checkdnsrr" ) ) {
			if ( (checkdnsrr( $domain,'MX') || checkdnsrr($domain, 'A') ) ) {
				$email_valid = true;
			}
		}
		else $email_valid = true;
    }

	return $email_valid;

}

?>
