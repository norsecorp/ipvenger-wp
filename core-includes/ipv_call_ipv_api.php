<?php
//
// call the IPViking api via streams
//

require_once( dirname( __FILE__ ) .  '/../core-includes/ipv_api_key.php' );

function ipv_post( $url, $data, &$status_code, &$status_text )
{

	// we cannot rely on curl, nor on allow_url_fopen so use sockets
    // TODO - why not use parse_url? - jb

	$url_words = explode( '/', $url );
	$host = $url_words[2];
	$uri  = '/' . $url_words[3] . '/';
	$ferrno = $ferrstr = NULL;
	if ( WP_DEBUG ) {
	    $fp = fsockopen( $host, 80, $ferrno, $ferrstr, 3);
	    $status_code = $ferrno;
	    $status_text = $ferrstr;
	} else {
	    $fp = @fsockopen( $host, 80, $ferrno, $ferrstr, 3);
	}

	if ( ! $fp ) {
	    if ( !WP_DEBUG ) {
	        $status_code = 1000;
	        $status_text = 'Cannot connect to IPV API Server ' + $url;
	    }
		return null;
	}

	$content = http_build_query($data);

	fwrite($fp, "POST $uri HTTP/1.1\r\n");
	fwrite($fp, "Host: $host\r\n");
	fwrite($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
	fwrite($fp, "Content-Length: ".strlen( $content )."\r\n");
	fwrite($fp, "Connection: close\r\n");
	fwrite($fp, "\r\n");

	fwrite($fp, $content);

	// first line contains status
	$in = fgets( $fp, 1024 );
	$hdr_words = explode( ' ', $in );
	$status_code = $hdr_words[1];
	$status_text = $hdr_words[2];

	// skip the rest of the headers and the length
	while (!feof($fp)) {
		$in = fgets($fp, 1024);
		if ( strpos( $in, ':' ) === FALSE ) break;
	}

	$response = "";

	// read the response body and build our return string
	while (!feof($fp)) {
		$in = fgets($fp, 1024);
		if ( strpos( $in, '<' ) === FALSE ) continue;
		$response .= $in;
	}

	return $response;

}

function ipv_call_ipv_api( $url, $api_key, $ip, & $status  ) {

	$status = 999;

	if ( $api_key == '' ) return NULL;

	$result = ipv_post(
		$url,
		array (
			'apikey' 	=> $api_key,
			'method' 	=> 'ipq',
			'ip' 	 	=> $ip,
			'customID' 	=> $_SERVER['SERVER_NAME']
		),
		$status,
		$msg
	);

	// if status indicates a bad or expired API key, flag it invalid in the db

	if ( in_array( $status, array( 400, 401, 402 ) ) ) {
		ipv_invalidate_api_key( $status );
	}
	else if ( $status == 302 ) ipv_validate_api_key();

	// xml returned in body
	return $result;

}
?>
