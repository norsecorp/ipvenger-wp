<?php

/**
 * function ipv_validate_email - syntax and mx record check on
 *
 * @param email string - email address to validate
 *
*/
function ipv_validate_email( $email ) {

    do {
        if (!$email) {
            break;
        }

        $atIndex = strrpos($email, "@");

        if (is_bool($atIndex) && !$atIndex) {
            break;
        } else {
            $domain = substr($email, $atIndex+1);
            $local = substr($email, 0, $atIndex);
        }

        $localLen = strlen($local);
        $domainLen = strlen($domain);

        if ($localLen < 1 || $localLen > 64) {
            break;
        } else if ($domainLen < 1 || $domainLen > 255) {
            break;
        }

        if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
            if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
                break;
            }
        }

        if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
            break;
        } else if (preg_match('/\\.\\./', $domain)) {
            break;
        } else if (function_exists( "checkdnsrr" ) && !(checkdnsrr($domain,"MX") || checkdnsrr($domain, "A"))) {
            break;
        }

        return true;

    } while (FALSE);

    return false;

}

?>