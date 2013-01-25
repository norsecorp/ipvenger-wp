<?php 

/**
 *
 *	ipv_manage_msgs.php:  update/retrieve block messages
 *
 *  The following (string) argument is required as a post variable
 *
 *		type:  		type of message to handle <"general"|"proxy"|"botnet">
 *
 *	In addition, if "msg" is set, the database will be updated with the 
 * 	new message
 *
 *		msg:  		msg to store in database
 *		
 *	"Returns" the current message after any updates resulting from the call
 *  as a text string.
 *		
*/

    /** Do not add any code before this include, which does security checks **/
    require( dirname( __FILE__ ) . '/ipv_prep_ajax.php' );

    require_once( dirname( __FILE__ ) .
        '/../cms-includes/ipv_cms_workarounds.php' );

	require_once( dirname( __FILE__ ) .  
		'/../core-includes/ipv_config_utils.php' );

	// see if we are called as a add or delete action, if so do it 

	$type 	= $_POST['type'];

	if ( isset( $_POST['msg'] ) ) {
		$msg = $_POST['msg'];
		ipv_set_block_msg( $type, $msg );
	}

	ob_clean();

	echo ipv_get_block_msg( $type );

?>
