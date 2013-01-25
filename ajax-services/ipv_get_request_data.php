<?php 

/**
 *
 *	ipv_get_request_data.php:  get summary data for given data ranges
 *
 *  Requires one POST variable arg
 *
 * 		day_array:		array of int	day counts to return data for, 
 *										relative to today, json encoded
 * 										(i.e. {7, 14, 30})
 * 
 *  Returns json encoded pair of arrays, each indexed by day_array elements
 *  {total_blocked, total_requests}
 *
*/

    /** Do not add any code before this include, which does security checks **/
    require( dirname( __FILE__ ) . '/ipv_prep_ajax.php' );

    require_once( dirname( __FILE__ ) .
        '/../cms-includes/ipv_cms_workarounds.php' );

	require_once( dirname( __FILE__ ) .  
		'/../core-includes/ipv_get_summary_data.php' );

	require_once( dirname( __FILE__ ) .  
		'/../cms-includes/ipv_db_utils.php' );

    $day_array = $_POST['day_array'];

	ipv_get_summary_data( $day_array, $total_blocked, $total_requests );

	$data = array( 
		'total_blocked' => $total_blocked, 
		'total_requests' => $total_requests
	);

	ipv_db_cleanup();

	echo json_encode( $data );
?>
