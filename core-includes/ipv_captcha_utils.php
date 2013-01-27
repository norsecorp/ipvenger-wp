<?php

/**
 * file ipv_captcha_utils.php
 *  
 *
 * This file contains utility routines for CAPTCHA caching
 *
*/

require_once( dirname( __FILE__ ) .  '/../cms-includes/ipv_db_utils.php' );
require_once( dirname( __FILE__ ) .  '/../securimage/securimage_cache.php' );

function ipv_gen_captcha() {

	$img = new Securimage();

	//$img->ttf_file        = './Quiff.ttf';
	//$img->captcha_type    = Securimage::SI_CAPTCHA_MATHEMATIC; // show a simple math problem instead of text
	//$img->case_sensitive  = true;                              // true to use case sensitve codes - not recommended
	//$img->image_height    = 90;                                // width in pixels of the image
	//$img->image_width     = $img->image_height * M_E;          // a good formula for image size
	$img->perturbation    = .90;                               // 1.0 = high distortion, higher numbers = more distortion
	$img->image_bg_color  = new Securimage_Color("#CCCCCC");   // image background color
	$img->text_color      = new Securimage_Color("#333333");   // captcha text color
	//$img->num_lines       = 4;                                 // how many lines to draw over the image
	$img->line_color      = new Securimage_Color("#333333");   // color of lines over the image
	//$img->image_type      = SI_IMAGE_JPEG;                     // render as a jpeg image
	//$img->signature_color = new Securimage_Color(rand(0, 64),
	//                                            rand(64, 128),
	//                                             rand(128, 255));  // random signature color

	// see securimage.php for more options that can be set
	// generate image and insert via callback to ipv_insert_captcha()
	$img->show();  

}

/* called by securimage to insert the next desired captcha */
/* based on the current status of the cache */

function ipv_insert_captcha( $image_data, $response ) {

	ipv_db_connect();
	
	$count = ipv_get_captcha_cache_size();

	$image_data = ipv_escape_string( $image_data );

	$id = ( ipv_get_captcha_last() + 1 ) % $count;

	/* delete the old as we always want to overwrite anyway */
	$q_str = 'DELETE FROM ' . IPV_CAPTCHA_CACHE . 
			 " WHERE id = $id";

	$q_result = ipv_db_query( $q_str );

	/* insert the fresh captcha */
	$q_str = 'INSERT ' . IPV_CAPTCHA_CACHE . 
			 " VALUES ($id, '$image_data', '$response')";

	$q_result = ipv_db_query( $q_str );
		
	/* update head and tail pointers */
	ipv_set_captcha_last( $id );
	ipv_set_captcha_first( ( $id + 1 ) % $count );

	ipv_db_cleanup();

}

/* generate a whole new set of captchas */
function ipv_init_captcha_cache() {

	$count = ipv_get_captcha_cache_size();

	if ( $count > 0 ) {
		for ( $i = 0; $i < $count; $i++ ) {
			ipv_gen_captcha();
		}
		return $count;
    }
    else return 0;

}

/* how many to generate */
function ipv_get_captcha_cache_size() {

	ipv_db_connect();

	$q_str = 'SELECT ipv_captcha_count FROM ' . IPV_CACHE;
	$q_result = ipv_db_query( $q_str );
    if ( ( $q_result ) && ( $row = ipv_db_fetch_assoc( $q_result ) ) )
    {
        return $row['ipv_captcha_count'];
	}
	else return 0;

	ipv_db_cleanup();
}

function ipv_get_next_captcha_index( $ip ) {

	ipv_db_connect();

	$count = ipv_get_captcha_cache_size();
	/* EXPLOIT */
	$q_str = 'SELECT id FROM ' . IPV_CAPTCHA_SERVED . 
			 " WHERE ip = '$ip'";

	$q_result = ipv_db_query( $q_str );

    if ( ( $q_result ) && ( $row = ipv_db_fetch_assoc( $q_result ) ) ) {

		$next_idx = ( (int) $row['id'] + 1 ) % $count;

	}
	else $next_idx = ipv_get_captcha_first() + rand( 0, ( $count / 2 ) - 1);
	
	ipv_db_cleanup();

	return $next_idx;

}

function ipv_get_next_captcha( $ip, &$captcha, &$response ) {

	ipv_db_connect();
	
	$idx = ipv_get_next_captcha_index( $ip );

	// if we have hit the end, generate a new captcha
	if ( $idx == ipv_get_captcha_last() ) {
		ipv_gen_captcha();
	}

	$q_str = 'INSERT INTO ' . IPV_CAPTCHA_SERVED . " VALUES ('$ip', $idx) " . 
			 'ON DUPLICATE KEY UPDATE id = ' . $idx;

	$q_result = ipv_db_query( $q_str );

	$q_str = 'SELECT captcha, response FROM ' . IPV_CAPTCHA_CACHE . 
			 " WHERE id = $idx";

	$q_result = ipv_db_query( $q_str );

    if ( ( $q_result ) && ( $row = ipv_db_fetch_assoc( $q_result ) ) ) {
		$captcha 	= $row['captcha'];
		$response 	= $row['response'];
	}

	$captcha = base64_encode( $captcha );

	ipv_db_cleanup();

}

function ipv_get_captcha_last() {

	ipv_db_connect();

	$q_str = 'SELECT ipv_captcha_last FROM ' . IPV_CACHE;
	$q_result = ipv_db_query( $q_str );
    $row = ipv_db_fetch_assoc( $q_result );
	return $row['ipv_captcha_last'];

	ipv_db_cleanup();

}

function ipv_set_captcha_last( $id ) {

	ipv_db_connect();

	$q_str = 'UPDATE ' . IPV_CACHE . " SET ipv_captcha_last = $id";
	ipv_db_query( $q_str );

	ipv_db_cleanup();
}

function ipv_get_captcha_first() {

	ipv_db_connect();

	$q_str = 'SELECT ipv_captcha_first FROM ' . IPV_CACHE;
	$q_result = ipv_db_query( $q_str );
    $row = ipv_db_fetch_assoc( $q_result );
	return $row['ipv_captcha_first'];

	ipv_db_cleanup();
}

function ipv_set_captcha_first( $id ) {

	ipv_db_connect();

	$q_str = 'UPDATE ' . IPV_CACHE . " SET ipv_captcha_first = $id";
	ipv_db_query( $q_str );

	ipv_db_cleanup();

}

?>
