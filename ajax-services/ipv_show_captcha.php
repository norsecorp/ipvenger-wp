<?php

	require_once( '../cms-includes/ipv_cms_workarounds.php' );
	require_once( '../core-includes/ipv_captcha_utils.php' );

	/** return the next captcha from the cache for the given IP **/

	$ip = $_SERVER['REMOTE_ADDR'];

	ipv_get_next_captcha( $ip, $captcha_image_data, $captcha_response );

	session_start();
	$_SESSION['ipv_captcha_text'] = $captcha_response;

	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
	header('Content-Type: image/png');

	echo base64_decode( $captcha_image_data );

?>
