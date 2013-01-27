<?php 

/**
 *
 *	lookup_ip.php:  ajax service script for IP history lookup
 *
 *	retrieve most recent general information and also request history
 *	associated with the given IP, and and print html suitable for display 
 *	(e.g. by alert());) 
 *
 *	Takes a single arg as POST variable:
 *
 *		ip:  ip address to look up
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

	ini_set( 'mysql.trace_mode', '0' );

	ipv_db_connect();

	if ( ! isset( $_POST['ip'] ) ) {
		print 'Please specify an IP address';
		return;
	}

	$ip = ipv_escape_string( $_POST['ip'] );

    if ( isset( $_POST['max_recs'] ) ) {
        $max_recs = intval( $_POST['max_recs'] );
    }
    else $max_recs = 20;

    if ( isset( $_POST['is_followup'] ) ) {
        $is_followup = true;
    }
	else $is_followup = false;

	if ( $ip == '' ) {
		print 'Please specify an IP address';
		return;
	}
	
	if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ){
		print $ip . ' is not a valid IP address';
		return;
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

	$blacklisted = in_array( $ip, $blacklisted_ips );
	$whitelisted = in_array( $ip, $whitelisted_ips );

	$ipv_request_detail_name = IPV_REQUEST_DETAIL;

	$query = <<<EOQ
		SELECT  SQL_CALC_FOUND_ROWS
				ipv_int_request_id AS id,
				ip,
				risk_factor AS ipq,
				country,
				city,
				organization AS org,
				ipv_int_factor_name AS factor,
				ipv_int_category_name AS category,
				if ( ipv_int_disp = 0, 'rejected', 'allowed' ) AS disp,
				ipv_int_disp_reason AS disp_reason,
                DATE_FORMAT( ipv_int_time,
                    '%b %d %H:%i:%s' ) as date
		FROM $ipv_request_detail_name
		WHERE ip='$ip'
		ORDER BY ipv_int_time DESC
		LIMIT $max_recs
EOQ;

        $q_result = ipv_db_query( $query );

		if ( ($row_count = ipv_db_num_rows( $q_result ) ) == 0 ) {
			print 'No requests found for ' . $ip;
			return;
		}

		// get first row and print the general info and table headers
		$row = ipv_db_fetch_assoc( $q_result );

        $sel_p = ' ';
        $sel_b = ' ';
        $sel_a = ' ';

        if ( $blacklisted ) $sel_b = 'selected="selected"';
        else if( $whitelisted ) $sel_a = 'selected="selected"';
        else $sel_p = 'selected="selected"';


		echo <<<EOT

		<div id="ipv-ip-lookup-header-container">

			<div id="ipv-ip-lookup-header">
				Country: ${row['country']}<br>
				City: ${row['city']}<br>
				Organization: ${row['org']}<br>
				Records found: $row_count<br>
			</div>

			<div id="ipv-ip-lookup-select">
				Protection<br>
				<select onchange='ipv_ip_bw_list( "$ip",
					(this.options[selectedIndex].text), ipv_ip_bw_action )'>
				  <option $sel_p value="protect">Protected</option>
				  <option $sel_b value="block">Blacklisted</option>
				  <option $sel_a value="allow">Whitelisted</option>
				</select>
			</div>

		</div>

		<div id="ip-detail-table-container">

        <table id="ip-detail-table" class="widefat">
		<thead>
        <tr>
            <th id="ipv_lookup_timestamp">Timestamp</th>
            <th>IPQ</th>
            <th>Disposition</th>
            <th>Reason</th>
            <th>Category</th>
        </tr>
        </thead>

        <tbody>
EOT;

		// output the detail records as table rows
		
		$odd = true;

		do {

			if ( $odd ) {
				$class = 'class="odd"';
				$odd = false;
			}
			else {
				$class = 'class="even"';
				$odd = true;
			}

			print "<tr $class>";
			print "<td>${row['date']}</td>";
			print "<td>${row['ipq']}</td>";
			print "<td>${row['disp']}</td>";
			print "<td>${row['disp_reason']}</td>";

			if ( $row['factor'] == 'ipviking_category_factor' ) 
				print "<td>${row['category']}</td>";
			else 
				print "<td>${row['factor']}</td>";
			print '</tr>';
		}
		while( $row = ipv_db_fetch_assoc( $q_result ) );

		echo '</tbody>';
		echo '</table>';
		echo '</div>';

		// count total number of rows and offer "more data" if applicable
		$q_result = ipv_db_query( "SELECT FOUND_ROWS() AS total_recs" );
		$row = ipv_db_fetch_assoc( $q_result );

		$total_recs = $row['total_recs'];

		if ( $is_followup || ( $total_recs > $max_recs ) ) {
			echo '<input type="button" id="ip-lookup-more-button" ' .
				 'style="display:block;margin:5px auto;text-align: center" ' .
				 'class="ipv-secondary"' . 
				 'value="Show more results" ';

			/* if user clicked "show more" and we now have all, disable */
			if ( $is_followup && ( $total_recs <= $max_recs ) ) {
				echo 'disabled = "disabled" ';
			}

			echo 'onclick="ipv_add_ip_records(' . "'$ip'" . ')">';
		}	

		ipv_db_cleanup();
?>
