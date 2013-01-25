<?php
/* This file is not in use */
/* Remove this file from repo */

function ipv_msec_time() {

	list( $msec, $sec ) = explode(' ', microtime());

	$msec_str = number_format( $msec, 6 );

	return date('Y-m-d H:i:s', $sec ) . substr( $msec_str, 1 );


}

function ipv_log ( $msg ) { 

	// WTF
	$fp = fopen( '/tmp/ipv_log.txt', 'a' );

	$time_str = ipv_msec_time();

	if (flock($fp, LOCK_EX)) {
		fwrite($fp, "$time_str - ${msg}\n" );
		fflush($fp);         
	}
	flock($fp, LOCK_UN);

	fclose( $fp );
}

?>
