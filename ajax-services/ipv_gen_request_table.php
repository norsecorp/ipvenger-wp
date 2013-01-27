<?php
/**
 * service ipv_gen_request_table
 *
 * Build an HTML table containing detailed information about the most
 * recently blocked requests
 *
 * All parameters are optional and passed as POST variables
 *
 * @param   string		$start_date		("yyyy-mm-dd")
 * @param   string 		$end_date		("yyyy-mm-dd")
 * @param	string		$country_filter
 * @param	string		$disp_filter
 * @param	string		$category_filter
 *
 * @return  int     http status code of response from IP Viking
 *
*/

	/** Do not add any code before this include, which does security checks **/
	require( dirname( __FILE__ ) . '/ipv_prep_ajax.php' );

	require_once( dirname( __FILE__ ) .
		'/../cms-includes/ipv_cms_workarounds.php' );

	require_once( dirname( __FILE__ ) .
		'/../cms-includes/ipv_db_utils.php' );

	require_once( dirname( __FILE__ ) .
		'/../core-includes/ipv_config_utils.php' );

	ipv_db_connect();

	// build the filter WHERE clause modifier

	$filter_str = '';

	$ipv_appeal_name = IPV_APPEAL;

	$max_recs = 5;
	if ( isset( $_POST['max_recs'] ) ) {
		$max_recs = intval( $_POST['max_recs'] );
	}

	if ( isset( $_POST['start_date'] ) ) {
		$start_date = $_POST['start_date'];
		$start_date = ipv_escape_string( $start_date );
		$filter_str .= ' AND ipv_int_date >= \''. $start_date . '\' ';
	}

	if ( isset( $_POST['end_date'] ) ) {
		$end_date = $_POST['end_date'];
		$end_date = ipv_escape_string( $end_date );
		$filter_str .= ' AND ipv_int_date <= \'' . $end_date . '\' ';
	}

	if ( isset( $_POST['country_filter'] ) ) {
		$country_filter = $_POST['country_filter'];
		$country_filter = ipv_escape_string( $country_filter );
		$filter_str .= ' AND country=\'' . $country_filter . '\' ';
	}

	if ( isset( $_POST['appeal_only'] ) ) {
		$filter_str .= ' AND ' . $ipv_appeal_name . '.appeal_id IS NOT NULL ';
	}

	if ( isset( $_POST['disp_filter'] ) ) {
		$disp_filter = $_POST['disp_filter'];
		$disp_filter = ipv_escape_string( $disp_filter );
		$filter_str .= ' AND ipv_int_disp_reason=\'' . $disp_filter . '\' ';
	}

	if ( isset( $_POST['category_filter'] ) ) {
		$category_filter = $_POST['category_filter'];
		$category_filter = ipv_escape_string( $category_filter );
		$filter_str .= <<<EOQ
			AND ( 
					ipv_int_factor_name='$category_filter'
				OR
					( ipv_int_factor_name='ipviking_category_factor' AND
						ipv_int_category_name='$category_filter' )
				)
EOQ;
	}

	// get blacklist and whitelist rules for setting block/allow pulldown
	$rules_array = ipv_get_rules_array( 'blacklist', 'ip' );
	$blacklisted_ips = array();
	foreach ( $rules_array as $r ) {
		$blacklisted_ips[] = $r['mask'];
	}

	$rules_array = ipv_get_rules_array( 'whitelist', 'ip' );
	$whitelisted_ips = array();
	foreach ( $rules_array as $r ) {
		$whitelisted_ips[] = $r['mask'];
	}

	// note nested table is so that we can group by ip when the user 
	// is only looking for pending appeals

	$ipv_request_detail_name = IPV_REQUEST_DETAIL;

	$query = <<<EOQ
		SELECT 	${ipv_request_detail_name}.ipv_int_request_id AS id, 
			${ipv_request_detail_name}.ip, 
			DATE_FORMAT( ${ipv_request_detail_name}.ipv_int_time, 
				'%b %d %H:%i:%s' ) as date,
			${ipv_request_detail_name}.country,	
			${ipv_request_detail_name}.organization,	
			${ipv_request_detail_name}.risk_factor AS ipq,
			${ipv_request_detail_name}.ipv_int_disp_reason AS disp_reason,
			${ipv_request_detail_name}.ipv_int_factor_name AS primary_factor,
			${ipv_request_detail_name}.ipv_int_category_name AS primary_category,
			( ${ipv_appeal_name}.appeal_id IS NOT NULL ) AS appeal_pending
		FROM $ipv_request_detail_name
		LEFT OUTER JOIN $ipv_appeal_name ON ${ipv_request_detail_name}.ip = 
			${ipv_appeal_name}.ip
		WHERE ipv_int_disp = 0 $filter_str
		ORDER BY  
			${ipv_request_detail_name}.ipv_int_time DESC, 
			ipv_int_request_id  DESC
		LIMIT $max_recs 
EOQ;

	$q_result = ipv_db_query( $query );

	$odd = true;	// first row is "one" for odd/even

	while( $row = ipv_db_fetch_assoc( $q_result ) ) {

		if ( $odd ) {
			$class = 'class="odd"';
			$odd = false;
		}
		else {
			$class = 'class="even"';
			$odd = true;
		}

		$ip = $row['ip'];

		$blacklisted = in_array( $ip, $blacklisted_ips );
		$whitelisted = in_array( $ip, $whitelisted_ips );

		$sel_p = ' ';
		$sel_b = ' ';
		$sel_a = ' ';

		if ( $blacklisted ) $sel_b = 'selected="selected"';
		else if( $whitelisted ) $sel_a = 'selected="selected"';
		else $sel_p = 'selected="selected"';

		echo "<tr $class>";
		echo '<td><a onclick="ipv_lookup_ip(';
		echo "'$ip'";
		echo ')">';
		echo "$ip</a></td>";
		echo "<td>${row['date']}</td>";
		echo "<td>${row['country']}</td>";
		echo "<td>${row['organization']}</td>";
		echo "<td>${row['ipq']}</td>";
		echo "<td>${row['disp_reason']}</td>";

		if ( $row['primary_factor'] == 'ipviking_category_factor' )
			echo "<td>${row['primary_category']}</td>";
		else
			print "<td>${row['primary_factor']}</td>";

		echo '<td>';
		if ( $row['appeal_pending'] == 1 ) echo 'Y';
		echo '</td>';

		echo <<< EOS
			<td>
				<div class="country-bl-action-wrapper">
					<select class="country-bl-action" 
						onchange='ipv_ip_bw_list( "$ip",
							(this.options[selectedIndex].text),
							ipv_ip_bw_action )' >

					  <option $sel_p value="protect">Protected</option>
					  <option $sel_b value="block">Blacklisted</option>
					  <option $sel_a value="allow">Whitelisted</option>
					</select>
				</div>
			</td>
EOS;

		echo '</tr>';

	}

	ipv_db_cleanup();

?>	
