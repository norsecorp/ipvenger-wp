<?php 

/**
 *
 *	ipv_download_csv.php:  download ipv_request_details table as CSV
 *
 *  Single GET variable arg, the number of days to go back
 *
 * 		days:	int number of days to "go back"
 *
*/

	session_start();

	if ( ! isset( $_SESSION['ipv_is_admin'] ) || ! $_SESSION['ipv_is_admin'] )
		die( 'Unauthorized' );   

	require_once( dirname( __FILE__ ) .  
		'/../cms-includes/ipv_db_utils.php' );

	$outstream = fopen('php://output','w');

	header('Content-type: text/csv');
	header('Content-disposition: attachment; filename=ipv_request_data.csv');

	/* first get the columns */

	$ipv_request_detail_name =  IPV_REQUEST_DETAIL;
	$q_str = <<<EOQ
	SELECT column_name
	  FROM INFORMATION_SCHEMA.COLUMNS
	  WHERE table_name = '$ipv_request_detail_name'
	  ORDER BY ordinal_position
EOQ;

	ipv_db_connect();

    $q_result = ipv_db_query( $q_str );

	$row = array();

	while ( $col = ipv_db_fetch_row( $q_result )  ) {
		$row[] = $col[0];
	}
	fputcsv($outstream, $row, ',', '"');  

	$date_limit = '';
	if ( isset( $_GET['days'] ) ) {
		$days = intval( $_GET['days'] );
		$date_limit = 
			"WHERE ipv_int_date > date_sub( curdate(), INTERVAL $days DAY ) ";
	}

	$q_date_limit = ipv_escape_string( $date_limit );

	$q_str = ' SELECT * FROM ' . IPV_REQUEST_DETAIL . " $q_date_limit ";

    $q_result = ipv_db_query( $q_str );

	$rowcount = ipv_db_num_rows( $q_result );
	$bytes = $rowcount * 200;

	$nrows = 0;
	while ( $row = ipv_db_fetch_row( $q_result )  ) {
		$nrows++;
		fputcsv($outstream, $row, ',', '"');  
	}

	fclose($outstream); 

	exit();

?>
