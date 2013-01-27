<?php

/**
 *
 *	ipv_get_country_data.php:  country specific block and threat data
 *
 *  Requires two POST variable args
 *
 * 		n_days:			int		return last "n_days" worth of data
 * 		total_blocked:	int		total blocks for percent by country calculation
 *
*/

    /** Do not add any code before this include, which does security checks **/
    require( dirname( __FILE__ ) . '/ipv_prep_ajax.php' );

    require_once( dirname( __FILE__ ) .
        '/../cms-includes/ipv_cms_workarounds.php' );

	require_once( dirname( __FILE__ ) .
		'/../core-includes/ipv_config_utils.php' );

	require_once( dirname( __FILE__ ) .
		'/../cms-includes/ipv_db_utils.php' );

	if((isset($_POST['total_blocked']) && preg_match('/[^\d\.]+/', $_POST['total_blocked'])) || (isset($_POST['total_requests']) && preg_match('/[^\d\.]+/', $_POST['total_requests']))) {
	    echo 'Invalid POST data';
	    exit;
	}

	ipv_db_connect();

	$country_labels = array();
	$country_ipqs = array();
	$country_block = array();
	$country_total = array();
	$country_pct_block = array();
	$country_pct_total = array();

	$q_days = intval( $_POST['n_days'] );
	$sort = $_POST['sort'];
	$total_blocked = $_POST['total_blocked'];
	$total_requests = $_POST['total_requests'];

	if ( $sort == 'block' ) $order = 'data2';
	else $order = 'data1';

	// the country bar graph and map data
	$query = 'SELECT country AS label, avg(risk_factor) AS data1,  ' .
		' sum( NOT ipv_int_disp ) AS data2, ' .
		' count( * ) AS data3 ' .
		' FROM ' . IPV_REQUEST_DETAIL .
		' WHERE ' .
		' ipv_int_date > date_sub( curdate(), INTERVAL ' . $q_days . ' DAY )' .
		' GROUP BY label ORDER BY ' . $order . ' DESC ' ;

	$q_result = ipv_db_query( $query );
	$i = 0;
	while ( $row = ipv_db_fetch_assoc( $q_result ) ) {

		// throw it out if if there is no block data
		if ( $row['data2'] == 0 ) continue;

		$label = $row['label'];
		if ( $label == '-' ) $label = 'Unknown Country';
		$country_labels[] = array( $i, $label );
		$country_ipqs[] = array( $i, (float) $row['data1'] );
		$country_block[] = $row['data2'];
		$country_total[] = $row['data3'];
		$country_pct_block[] = array( $i,
			100.0 * (float) $row['data2'] / (float) $total_blocked );
		$country_pct_total[] = array( $i,
			100.0 * (float) $row['data3'] / (float) $total_requests );
		$i++;
	}

	$data = array(
		'country_labels' => $country_labels,
		'country_ipqs' => $country_ipqs,
		'country_block' => $country_block,
		'country_total' => $country_total,
		'country_pct_block' => $country_pct_block,
		'country_pct_total' => $country_pct_total
	);

	ipv_db_cleanup();

	echo json_encode( $data );
?>
