<?php

/**
 *
 *	ipv_get_summary_data.php:  get total blocks and total requests by day
 *
*/
function ipv_get_summary_data( $day_array, &$total_blocked, &$total_requests ) {

    ipv_db_connect();

    $i = 0;

    // get total number blocked and requests for each allowed date range

    foreach ( $day_array as $n_days ) {

        $q_days = intval( $n_days );

        $query = 'SELECT count(*) AS count FROM '  . IPV_REQUEST_DETAIL .
            ' WHERE ipv_int_disp = 0 ' .
            "AND ipv_int_date > date_sub(curdate(), INTERVAL $q_days DAY)";

        ipv_db_connect();

        $q_result = ipv_db_query( $query );
        $row = ipv_db_fetch_assoc( $q_result );

        $total_blocked[$n_days] = $row['count'];

        $query = 'SELECT count(*) AS count FROM '  . IPV_REQUEST_DETAIL .
            " WHERE ipv_int_date > date_sub(curdate(), INTERVAL $q_days DAY)";

        $q_result = ipv_db_query( $query );
        $row = ipv_db_fetch_assoc( $q_result );

        $total_requests[$n_days] = $row['count'];

    }

    ipv_db_cleanup();

}
?>
