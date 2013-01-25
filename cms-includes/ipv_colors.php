<?php 

	/* color definitions that aren't styled via CSS go here */

	// range colors for coloring avg IPQ on the maps 

	define( 'ipv_clr_range_green', '#00A6E4' );
	define( 'ipv_clr_range_yellow', '#EA7C1E' );
	define( 'ipv_clr_range_orange', '#B81600' );
	define( 'ipv_clr_range_red', '#5C0700' );

	// graph colors for request disposition type

	define( 'ipv_clr_allow', '#00ADDE' );
	define( 'ipv_clr_ip_blacklist', '#999999' );
	define( 'ipv_clr_ctry_blacklist', '#F6821F' );
	define( 'ipv_clr_ipq_block', '#CC1A00' );

	define( 'ipv_clr_country_pct_block', '#F79622' );
	define( 'ipv_clr_country_avg_ipq', '#CC1A00' );
	
	// array of graph colors for dynamic assignment to category types

	$ipv_clr_threats = 
		Array( '#660000', '#CC0000', '#FF5500', '#FF9933' );

?>
