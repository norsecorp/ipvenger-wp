<?php
/* 	CMS specific workarounds to be included by ajax routines that need to 
    exist "outside" the main presentation loop */

// redefine wordpress search path to local so we can dummy out
// wp-settings and avoid loading all of wordpress when we include
// wp-config

define( 'ABSPATH', dirname(__FILE__) . '/' );

define( 'IPV_IN_AJAX', true );
?>
