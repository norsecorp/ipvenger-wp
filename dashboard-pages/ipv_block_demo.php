<?php 

/**
 *
 *	ipv_get_block_html.php:  display sample html for a blocked page
 *
 *  One GET variable args
 *
 * 		type	string		type of block (general, proxy, botnet)
 *
*/

	session_start();

    if ( ! isset( $_SESSION['ipv_is_admin'] ) || ! $_SESSION['ipv_is_admin'] )
        die( 'Unauthorized' );

    require_once( dirname( __FILE__ ) .
        '/../cms-includes/ipv_block_page.php' );

	ipv_echo_block( 0, $_GET['type'], 'IPQ Score', '192.0.0.1', 'ipv_demo', $_GET['msg'] );

?>
