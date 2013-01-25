<?php
	
	// only execute when called from wordpress
	if ( !defined ( 'WP_UNINSTALL_PLUGIN' ) ) exit();

	require_once( dirname( __FILE__ ) .
		"/cms-includes/ipv_db_utils.php" );

	// drop ipv tables
	ipv_db_drop_tables();

?>
