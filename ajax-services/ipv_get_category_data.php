<?php
/**
 *
 *   POST variable args
 *
 * 		n_days:		int		return last "n_days" worth of data
 * 		country:	string	restrict results to given country - if not "all"
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
	
	require_once( dirname( __FILE__ ) .  
		'/../cms-includes/ipv_colors.php' );
	
	ipv_db_connect();

	$q_days = intval( $_POST['n_days'] );
	$country = ipv_escape_string( $_POST['country'] );

	$max_categories = 4;

	if ( $country == 'all' ) $country_where = '';
	else $country_where = ' AND country=\'' . $country . '\' ';

	$disp_totals = array();
	$pie_data_disp = array();
	$pie_data_category = array();
	$pie_temp_category = array();

	// gather data for the pie charts.  We divide the chart into blacklist
	// vs IPV risk.  The risk part of the pie is then divided by factors
	// and the category factor is further subdivided.  In each case we
	// are only concerned about those requests that were rejected

	// initialize disp reason rows in case there is no data
	
	$labels = array( 
		'IPQ Score',
		'Country Blacklist', 
		'IP Blacklist', 
		'Allowed' 
	);

	$colors = array( 
		ipv_clr_ipq_block, 
		ipv_clr_ctry_blacklist, 
		ipv_clr_ip_blacklist, 	
		ipv_clr_allow
	);

	for ( $i = 0; $i < count( $labels ); $i++ ) {

		$pie_data_disp[] = array( 
			'label' => $labels[$i], 'data' => (int) 0, 'color' => $colors[$i] 
		);
		$disp_totals[$labels[$i]] = 0;
	}

	$ipv_request_detail_name = IPV_REQUEST_DETAIL;

	// first the disp reason
	$query = <<<EOQ
	SELECT ipv_int_disp_reason AS label, count(*) AS data
		FROM $ipv_request_detail_name
		WHERE ipv_int_disp=0 $country_where
		AND ipv_int_date > date_sub( curdate(), INTERVAL $q_days DAY )
		GROUP BY label 
	UNION
	SELECT 'Allowed' AS label, count(*) AS data
		FROM $ipv_request_detail_name WHERE ipv_int_disp=1 $country_where
		AND ipv_int_date > date_sub( curdate(), INTERVAL $q_days DAY )
EOQ;

	$q_result = ipv_db_query( $query );

	while ( $row = ipv_db_fetch_assoc( $q_result ) ) {

		$row['data'] = (int) $row['data'];

		$idx = array_search( $row['label'], $labels );
		$pie_data_disp[$idx]['data'] = $row['data'];
		$pie_data_disp[$idx]['label'] = 	
			'<div class="cat-legend-count">' . 
			number_format( $row['data'] ) . '</div> ' . $row['label'];

		$disp_totals[$row['label']] = $row['data'];

	}

	// now the factor reason
	$query = 'SELECT ipv_int_factor_name AS label, count(*) AS data ' .
		' FROM ' . $ipv_request_detail_name  .
		' WHERE ipv_int_disp=0 ' . $country_where . ' AND ' .
		' ipv_int_disp_reason=\'IPQ Score\' ' .
		' AND ipv_int_date > date_sub( curdate(), INTERVAL ' . $q_days . 
		' DAY ) GROUP BY label';

	$q_result = ipv_db_query( $query );

	while ( $row = ipv_db_fetch_assoc( $q_result ) ) {
		// skip the category factors we will count them in the next query
		if ( $row['label'] == 'ipviking_category_factor' ) continue;
		$row['data'] = (int)  $row['data'];
		$row['label'] = str_replace( '_', ' ', $row['label'] );
        $row['label'] = ucwords( $row['label'] );
		$row['label'] = utf8_encode( '<div class="cat-legend-count">' . 
			number_format( $row['data'] ) . '</div> ' . $row['label'] .
			'<span style="vertical-align:bottom" class="ipv-dynamic-cluetip" ' .
			'title="' . $row['label'] . '||' . 
			ipv_get_category_tooltip( $row['label'] ) . 
			'">&nbsp;[?]</span>' );
		$pie_temp_category[] = $row;
	}

	// and now add in the individual categories (skipped last time)
	$query = 'SELECT ipv_int_category_name AS label, count(*) AS data ' .
		' FROM ' . $ipv_request_detail_name .
		' WHERE ipv_int_disp=0 ' . $country_where .
		' AND ipv_int_factor_name=\'ipviking_category_factor\' ' .
		' AND ipv_int_disp_reason=\'IPQ Score\' ' .
		' AND ipv_int_date > date_sub( curdate(), INTERVAL ' . $q_days . 
		' DAY ) GROUP BY label';

	$q_result = ipv_db_query( $query );
	while ( $row = ipv_db_fetch_assoc( $q_result ) ) {
		$row['data'] = (int)  $row['data'];

		$row['label'] = utf8_encode( '<div class="cat-legend-count">' . 
			number_format( $row['data'] ) . '</div> ' . $row['label'] . 
			'<span style="vertical-align:bottom" class="ipv-dynamic-cluetip" ' .
			'title="' . $row['label'] . '||' . 
			ipv_get_category_tooltip( $row['label'] ) . 
			'">&nbsp;[?]</span>' );

		$pie_temp_category[] = $row;
	}

	// sort descending - we only return the 5 largest categories and 
	// everything else goes into "other"
	
	$counts = array();
	foreach ( $pie_temp_category as $key => $row ) {
		$counts[$key] = $row['data'];
	}
	array_multisort( $counts, SORT_DESC, $pie_temp_category );

	$pie_data_category = array();
	$cat_count  = count( $pie_temp_category );
	$pct_cutoff = 0.01 * $disp_totals['IPQ Score'];

	$other_created = false;

	for ( $i = 0; $i < $cat_count; $i++ ) {
	
		// all the data after the third, or with too low a % goes in "other"
		if ( ( $i > $max_categories - 2 ) 
			|| ( $pie_temp_category[$i]['data'] < $pct_cutoff ) ) 
		{
			if ( ! $other_created ) {
				$count = $pie_temp_category[$i]['data'];
				$pie_data_category[] = array( 
					'label'=> '<div class="cat-legend-count">' . 
					number_format( $count ) . '</div>Other', 'data'=> $count );
				$other_created = true;
				$other_index   = count( $pie_data_category ) - 1;
			}
			else {
				$count = $pie_data_category[$other_index]['data']
							+ $pie_temp_category[$i]['data'];
				$pie_data_category[$other_index] = array( 
					'label'=> '<div class="cat-legend-count">' . 
					number_format( $count ) . '</div>Other', 'data'=> $count );
			}
		}
		else {
			$pie_data_category[] = $pie_temp_category[$i];
		}

	}

	// now assign the colors
	for ( $i = 0; $i < count( $pie_data_category ); $i++ ) {
		$pie_data_category[$i]['color'] = $ipv_clr_threats[$i];
	}

	// get total requests and total blocked in default range
	$query = 'SELECT count(*) AS total_count, ' . 
		' sum( NOT ipv_int_disp ) AS block_count ' .
		' FROM ' . $ipv_request_detail_name . ' WHERE ' .
		' ipv_int_date > date_sub( curdate(), INTERVAL ' . $q_days . 
		" DAY ) $country_where ";

	$q_result = ipv_db_query( $query );
	$row = ipv_db_fetch_assoc( $q_result );

	$total_blocked = $row['block_count'];
	$total_requests = $row['total_count'];

	$data = array( 
		'disp_data' => $pie_data_disp, 
		'disp_totals' => $disp_totals, 
		'cat_data' => $pie_data_category, 
		'total_blocked' => $total_blocked, 
		'total_requests' => $total_requests
	);

	ipv_db_cleanup();

	echo json_encode( $data );
?>
