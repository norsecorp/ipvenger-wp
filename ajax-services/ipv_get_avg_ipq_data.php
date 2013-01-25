<?php 

/**
 *
 *	ipv_get_avg_ipq_data.php:  return data on avg ipq score by day
 *
 *  Single POST variable arg, the number of days back to go
 *
 * 		n_days:	int		return last "n_days" worth of data
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
	
	ipv_db_connect();

	$q_days = intval( $_POST['n_days'] );

	$ipq_labels = array();
	$cn_bl_labels = array();
	$ip_bl_labels = array();
	$ipq_blocks = array(); 
	$cn_bl_blocks = array();
	$ip_bl_blocks = array();

	$query = 'SELECT date_format( ipv_int_date, \'%c/%e\' ) AS label, ' .
		' count(*) AS data FROM ' . IPV_REQUEST_DETAIL . ' WHERE ' .
		' ipv_int_date > date_sub( curdate(), INTERVAL ' . $q_days . ' DAY )' .
		' AND ipv_int_disp = 0 AND ipv_int_disp_reason=\'IPQ Score\' ' .
		' GROUP BY ipv_int_date ORDER BY ipv_int_date';

	$q_result = ipv_db_query( $query );

	$i = 0;
	while ( $row = ipv_db_fetch_assoc( $q_result ) ) {
		$ipq_labels[] = array( $i, $row['label'] );
		$ipq_blocks[] = array( $i, (float) $row['data'] );
		$i++;
	}

	$query = 'SELECT date_format( ipv_int_date, \'%c/%e\' ) AS label, ' .
		' count(*) AS data FROM ' . IPV_REQUEST_DETAIL . ' WHERE ' .
		' ipv_int_date > date_sub( curdate(), INTERVAL ' . $q_days . ' DAY )' .
		' AND ipv_int_disp = 0 AND ipv_int_disp_reason=\'Country Blacklist\' ' .
		' GROUP BY ipv_int_date ORDER BY ipv_int_date';

	$q_result = ipv_db_query( $query );

	$i = 0;
	while ( $row = ipv_db_fetch_assoc( $q_result ) ) {
		$cn_bl_labels[] = array( $i, $row['label'] );
		$cn_bl_blocks[] = array( $i, (float) $row['data'] );
		$i++;
	}

	$query = 'SELECT date_format( ipv_int_date, \'%c/%e\' ) AS label, ' .
		' count(*) AS data FROM ' . IPV_REQUEST_DETAIL . ' WHERE ' .
		' ipv_int_date > date_sub( curdate(), INTERVAL ' . $q_days . ' DAY )' .
		' AND ipv_int_disp = 0 AND ipv_int_disp_reason=\'IP Blacklist\' ' .
		' GROUP BY ipv_int_date ORDER BY ipv_int_date';

	$q_result = ipv_db_query( $query );

	$i = 0;
	while ( $row = ipv_db_fetch_assoc( $q_result ) ) {
		$ip_bl_labels[] = array( $i, $row['label'] );
		$ip_bl_blocks[] = array( $i, (float) $row['data'] );
		$i++;
	}

	$data = array(  'ipq_labels' => $ipq_labels, 
					'cn_bl_labels' => $cn_bl_labels, 
					'ip_bl_labels' => $ip_bl_labels, 
					'ipq_blocks' => $ipq_blocks, 
					'cn_bl_blocks' => $cn_bl_blocks, 
					'ip_bl_blocks' => $ip_bl_blocks );

	ipv_db_cleanup();

	echo json_encode( $data );
?>
