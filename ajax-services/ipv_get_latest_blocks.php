<?php 
/**
 *
 *	get_latest_blocks.php:  ajax service script for IPV "recent block" list
 *
 *
 *	retrieve and return the most recent IP addresses and reasons for display 
 *	in blocked IP ticker.  return array will be ordered newest to oldest
 *
 *	default behavior (no POST data) is to retrieve the last ten denied 
 *	requests, JSON encoded as an array of IP's and reasons.  
 *
 *	additional options may be delivered as POST variables
 *
 *		min_key:   only search for items with a db key higher than this arg
 *		max_count: int - maximum number of requests to return
 *		
*/

    /** Do not add any code before this include, which does security checks **/
    require( dirname( __FILE__ ) . '/ipv_prep_ajax.php' );

    require_once( dirname( __FILE__ ) .
        '/../cms-includes/ipv_cms_workarounds.php' );

    require_once( dirname( __FILE__ ) .
        '/../cms-includes/ipv_db_utils.php' );

	ipv_db_connect();

	$result = array();
	$count = 0;

	// if we were given a minimum key id, check if there are any 'new' blocks
	if ( isset( $_POST['min_key'] ) ) {

		$min_key = intval( $_POST['min_key'] );

		$query = 'SELECT count(*) AS count FROM ' . IPV_REQUEST_DETAIL .
				 ' WHERE ipv_int_disp = 0 ' . 
				 ' AND ipv_int_request_id > \'' . $min_key . '\'';

		$q_result = ipv_db_query( $query );

		$row = ipv_db_fetch_assoc( $q_result );

		$count = (int) $row['count'];

		// if there are no new blocks, don't send updated data...
		if ( $count == 0 ) {
			$data = array( 'result' => $result, 'count' => $count );
			// json encode and return
			echo json_encode( $data );
			return;
		}

	}

	if ( isset( $_POST['max_count'] ) ) {
		$max_count = intval( $_POST['max_count'] );
	}
	else $max_count = 10;

	$query = 	'SELECT ipv_int_request_id AS id, ip, ' . 
				'DATE_FORMAT( ipv_int_time, \'%a, %H:%i %p\') as time, ' . 
				'ipv_int_time AS raw_time, ' . 
				'ipv_int_category_name AS category, ' . 
				'ipv_int_factor_name AS factor, ' . 
				'ipv_int_disp_reason AS disp_reason ' .
				'FROM ' . IPV_REQUEST_DETAIL .
				' WHERE ipv_int_disp = 0 ' .
				'ORDER BY ipv_int_request_id DESC LIMIT ' . $max_count;

	// now execute the query and parse the results into an array, newest first
	$q_result = ipv_db_query( $query );

	ipv_db_cleanup();

	while( $row = ipv_db_fetch_assoc( $q_result ) ) {

		// use the most granular "reason"

		if ( $row['disp_reason'] == 'IPQ Score' ) {
			if ( $row['factor'] == 'ipviking_category_factor' )
				$row['reason'] = strtolower( $row['category'] );
			else 
				$row['reason'] = str_replace( '_', ' ', $row['factor'] );
		}
		else { 
			$row['reason'] = $row['disp_reason'];
		}

		$row['reason'] = ucwords( $row['reason'] );
	
		$row['time_ago'] = ipv_time_ago( $row['raw_time'] );

		array_push( $result, $row );
	}

	$data = array( 'result' => $result, 'count' => $count );

	// json encode and return
	echo json_encode( $data );

?>
