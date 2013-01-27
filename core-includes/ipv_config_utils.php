<?php

/**
 * file ipv_config_utils.php
 *
 *
 * This file contains utility routines for accessing the ipvenger configuration
 * settings.
 *
*/

require_once( dirname( __FILE__ ) .  '/../cms-includes/ipv_db_utils.php' );
require_once( 'ipv_matches_mask.php' );

/**
 * function ipv_time_ago - convert a unix timestamp into e.g. "xx hours ago"
 *
*/
function ipv_time_ago( $time ) {

	// note that "weeks" is enough - no data is older than 30 days

    $periods	= array('sec', 'min', 'hour', 'day', 'week');
    $lengths	= array('60','60','24','7','4.35' );

    $utime		= strtotime($time);

    if(empty($utime)) return 'Unknown';

	$diff     = time() - $utime;

    for ($j = 0; $diff >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $diff /= $lengths[$j];
    }

    $diff = round($diff);

    if($diff != 1) $periods[$j].= 's';

    return "$diff $periods[$j]";
}

/**
 * function ipv_get_default_risk: retrieve default threat IPQ threshold
 *
 * @return  float     IPQ threshold to identify threat, -1 on error
 *
*/
function ipv_get_default_risk( ) {

	ipv_db_connect();

	$q_result = ipv_db_query(
		'SELECT  default_risk_threshold FROM ' . IPV_GLOBAL_SETTINGS .
		' WHERE configuration_id = 1 ');

	if ( ! $q_result ) return -1;

	$row = ipv_db_fetch_assoc( $q_result );

	return $row['default_risk_threshold'];

	ipv_db_cleanup();

}

/**
 * function ipv_update_site_ipq the ipq score for "custom" site
 * type
 *
 * @param	float	$ipq 	the new ipq threshold
*/
function ipv_update_site_ipq( $site, $ipq ) {

	ipv_db_connect();

	$ipq = ipv_escape_string( $ipq );
	$site = ipv_escape_string( $site );

	$q_result = ipv_db_query(
		'UPDATE ' . IPV_SITE_TYPE . " SET ipq_level = '$ipq' " .
		"WHERE type_short_name = '$site'");

	ipv_db_cleanup();
}

/**
 * function ipv_get_site_type_by_name: retrieve basic site type data
 *
 * @param	string	$short_name	(input)  key name, e.g. "social"
 * @param	string	$display_name 	(output) text name, e.g. "Social Platform"
 * @param	float	$ipq_level 		(output) default ipq threshold for type
 * @param	float	$ipq_level_desc (output) description, e.g. "medium high"
 * @param	float	$desc_text 		(output) descriptive text for this type
 *
 * @return  boolean  		true on success, false on error
 *
*/
function ipv_get_site_type_by_name(
	$short_name, &$display_name, &$ipq_level, &$ipq_level_desc, &$desc_text  )
{

	ipv_db_connect();

	$short_name = ipv_escape_string( $short_name );

	$q_result = ipv_db_query(
		'SELECT  ' .
		'type_display_name AS display_name, ' .
		'ipq_level, ipq_level_desc, ' .
		'type_descriptive_text AS desc_text ' .
		'FROM '  . IPV_SITE_TYPE .
		" WHERE type_short_name = '$short_name'");

	if ( ! $q_result ) return false;

	$row = ipv_db_fetch_assoc( $q_result );

	$display_name = $row['display_name'];
	$ipq_level = $row['ipq_level'];
	$ipq_level_desc = $row['ipq_level_desc'];
	$desc_text = $row['desc_text'];

	return true;

	ipv_db_cleanup();

}

/**
 * function ipv_get_site_type: retrieve basic site type data
 *
 * Note that all parameters are output references
 *
 * @param	string	$short_namea	(output) key name, e.g. "social"
 * @param	string	$display_name 	(output) text name, e.g. "Social Platform"
 * @param	float	$ipq_level 		(output) default ipq threshold for type
 * @param	float	$ipq_level_desc (output) description, e.g. "medium high"
 * @param	float	$desc_text 		(output) descriptive text for this type
 *
 * @return  boolean  		true on success, false on error
 *
*/
function ipv_get_site_type(
	&$short_name, &$display_name, &$ipq_level, &$ipq_level_desc, &$desc_text  )
{

	ipv_db_connect();

	$q_result = ipv_db_query(
		'SELECT  site_type AS short_name ' .
		' FROM ' . IPV_GLOBAL_SETTINGS .
		' WHERE configuration_id = 1 ');

	if ( ! $q_result ) return false;

	$row = ipv_db_fetch_assoc( $q_result );

	$short_name = $row['short_name'];

	$rc = ipv_get_site_type_by_name( $short_name,
		$display_name, $ipq_level, $ipq_level_desc, $desc_text  );

	return $rc;

	ipv_db_cleanup();

}

/**
 * function ipv_set_site_type: set the website type
 *
 * @param  float	$site_type	short name for website type, e.g. "ecommerce"
 *
 * @return boolean	true on success, else false
 *
*/
function ipv_set_site_type( $site_type ) {

	ipv_db_connect();

	$site_type = ipv_escape_string( $site_type );

	$q_result = ipv_db_query( 'UPDATE ' . IPV_GLOBAL_SETTINGS .
        " SET site_type='$site_type' " .
        ' WHERE configuration_id = 1 ' );

	ipv_db_cleanup();

	return $q_result;
}

/**
 * function ipv_set_default_risk: set default threat IPQ threshold
 *
 * @param  float	$risk	IPQ threshold use by default to identify threat
 *
 * @return boolean	true on success, else false (
 *
*/
function ipv_set_default_risk( $risk ) {

	ipv_db_connect();

	$risk = ipv_escape_string( $risk );

	$q_result = ipv_db_query( 'UPDATE ' . IPV_GLOBAL_SETTINGS .
        " SET default_risk_threshold='$risk' " .
        ' WHERE configuration_id = 1 ' );

	ipv_invalidate_cache();

	ipv_db_cleanup();

	return $q_result;
}

/**
 * function ipv_get_rules_array: get rules data for given field and type
 *
 * @param  	string			$rule_type	type of rule whitelist|blacklist
 * @param  	string			$field		field from ipv_exception_types
 * 										db table (e.g. "ip", "country", etc)
 *
 * @return  array{string}	array of rules info
 *
*/
function ipv_get_rules_array( $rule_type, $field ) {

	ipv_db_connect();

	$result = array();

	$field 		= ipv_escape_string( $field );

	if ( $rule_type == 'whitelist' ) $action_code="allow";
	else $action_code="deny";

	$query = 'SELECT id, mask FROM ' . IPV_EXCEPTION . ' WHERE ' .
		" action='$action_code' and excp_type = '$field'" .
		' ORDER BY mask ';

	$q_result = ipv_db_query( $query );

	if ( $q_result !== false ) {

		while ( $row = ipv_db_fetch_assoc( $q_result ) ) {

			extract( $row );

			array_push( $result, array( 'key'=>$id, 'mask'=>$mask ) );

		}

	}

	ipv_db_cleanup();

	return $result;
}

/**
 * function ipv_add_whitelist: whitelist an item
 *
 * @param	string	$type		type of whitelist entry (e.g. "ip")
 * @param	string	$mask		value against which to test candidates
 * @param	string	$mask_type	type of match, "wildcard" | "exact"
 *
 * @return  float   return code from lower level add function
 *
*/
function ipv_add_whitelist( $type, $mask, $mask_type ) {
	return ipv_add_exception( $type, $mask, $mask_type, 'allow' );
}

/**
 * function ipv_add_blacklist: blacklist an item
 *
 * @param	string	$type		type of blacklist entry (e.g. "ip")
 * @param	string	$mask		value against which to test candidates
 * @param	string	$mask_type	type of match, "wildcard" | "exact"
 *
 * @return  float   return code from lower level add function
 *
*/
function ipv_add_blacklist( $type, $mask, $mask_type ) {
	return ipv_add_exception( $type, $mask, $mask_type, 'deny' );
}

/**
 * function ipv_add_exception:
 *
 * Exception rules any rules that result in a blacklist/whitelist situation
 *
 * @param	string	$type		type of entry (e.g. "ip")
 * @param	string	$mask		value against which to test candidates
 * @param	string	$mask_type	type of match, "wildcard" | "exact"
 * @param	string	$action		dispostion: { "allow" | "deny" }
 *
 * @return  float   return code from lower level add function
 *
*/
function ipv_add_exception( $field_type, $mask, $mask_type, $action ) {

		ipv_db_connect();

		$table		= IPV_EXCEPTION;
		$mask 		= ipv_escape_string( $mask );
		$mask_type	= ipv_escape_string( $mask_type );
		$field_type = ipv_escape_string( $field_type );
		$action		= ipv_escape_string( $action );

		$query_str = "INSERT INTO $table VALUES " .
			"( NULL, '$action', '$mask', '$mask_type', '$field_type')";

		$q_result = ipv_db_query( $query_str );

		// for exact match ip rules, delete any pending appeals
		if ( ( $mask_type == 'exact' ) && ( $field_type == 'ip' ) ) {
			ipv_db_query(
				'DELETE FROM ' . IPV_APPEAL . " WHERE ip = '$mask'"
			);
		}

		ipv_invalidate_cache();

		ipv_db_cleanup();

		return $q_result;
}

/**
 * function ipv_delete_exception:
 *
 * Delete exception matching given parameters from the database - note that
 * there can only ever be one of "allow" or "deny" for a given type and mask.
 *
 * @param	string	$type		type of entry (e.g. "ip")
 * @param	string	$mask		value against which to test candidates
 *
 * @return  float   return code from lower level delete function
 *
*/
function ipv_delete_exception( $type, $mask ) {

		ipv_db_connect();

		$mask  = ipv_escape_string( $mask );
		$type  = ipv_escape_string( $type );

		$q_result = ipv_db_query( 'DELETE FROM $table ' .
			"WHERE mask='$mask' AND excp_type='$type'" );

		ipv_invalidate_cache();

		ipv_db_cleanup();

}

/**
 * function ipv_delete_by_mask: delete rule by internal database key
 *
 * @param	string	$type		type of rule e.g. blacklist/whitelist
 * @param	string	$mask		mask text
 *
*/
function ipv_delete_by_mask ( $type, $mask ) {

	ipv_db_connect();

	$table = IPV_EXCEPTION;

	$mask = ipv_escape_string( $mask );

	ipv_db_query( "DELETE FROM $table WHERE mask = '$mask' " );

	ipv_invalidate_cache();

	ipv_db_cleanup();

}

/**
 * function ipv_delete_by_key: delete rule by internal database key
 *
 * @param	string	$type		type of rule e.g. blacklist/whitelist/risk
 * @param	string	$key		rule id (primary key)
 *
*/
function ipv_delete_by_key ( $type, $key ) {

	ipv_db_connect();

	$table = IPV_EXCEPTION;

	$key = ipv_escape_string( $key );

	ipv_db_query( "DELETE FROM $table WHERE id = '$key' " );

	ipv_invalidate_cache();

	ipv_db_cleanup();

}

/**
 * function ipv_get_block_msg: get message shown to blocked users
 *
 * @param  	string	$type	One of "general", "botnet" or "proxy", e.g.
 *
 * @return  string  the message
 *
*/
function ipv_get_block_msg( $type ) {

	// need to explicitly generate col name to protect against sql injection

	ipv_db_connect();

	$type = ipv_escape_string ( $type );

	if ( $type == "botnet" ) $colname = "block_msg_botnet";
	else if ( $type == "proxy" )  $colname = "block_msg_proxy";
		 else $colname = "block_msg_general";

	$q_result = ipv_db_query(
		"SELECT $colname FROM " . IPV_GLOBAL_SETTINGS .
		' WHERE configuration_id = 1 ' );

	if ( ! $q_result ) return -1;

	$row = ipv_db_fetch_assoc( $q_result );

	ipv_db_cleanup();

	return $row[$colname];

}

/**
 * function ipv_set_block_msg: set message shown to blocked users
 *
 * @param  	string	$type	One of "general", "botnet" or "proxy", e.g.
 * @param  	string	$msg	The new message
 *
 * @return boolean	true on success, else false (
 *
*/
function ipv_set_block_msg( $type, $msg ) {

	ipv_db_connect();

	$msg  = ipv_escape_string( $msg );
	$type = ipv_escape_string( $type );

	if ( $type == "botnet" ) $colname = "block_msg_botnet";
	else if ( $type == "proxy" )  $colname = "block_msg_proxy";
		 else $colname = "block_msg_general";

	$q_result = ipv_db_query( 'UPDATE ' . IPV_GLOBAL_SETTINGS .
        " SET $colname='$msg' " .
        ' WHERE configuration_id = 1 ' );

	ipv_db_cleanup();

	return $q_result;
}

function ipv_get_logo_url() {

	ipv_db_connect();

	$q_result = ipv_db_query(
		' SELECT logo_url FROM ' . IPV_GLOBAL_SETTINGS .
		' WHERE configuration_id = 1 ' );

	$row = ipv_db_fetch_assoc( $q_result );

    ipv_db_cleanup();

	return $row['logo_url'];

}

/**
 * function to put basic blog information into the database so it can
 * be accessed by routines (especially block_message()) that operate
 * outside of the wordpress loop
 */
function ipv_set_blog_info(
	$ipcc, $stylesheet, $logo, $name, $description, $block_path )
{

	ipv_db_connect();

	$ipcc 	 	 = ipv_escape_string( $ipcc );
	$stylesheet  = ipv_escape_string( $stylesheet );
	$logo 		 = ipv_escape_string( $logo );
	$name 	 	 = ipv_escape_string( $name );
	$description = ipv_escape_string( $description );
	$block_path  = ipv_escape_string( $block_path );

	$q_result = ipv_db_query(
		' UPDATE ' . IPV_GLOBAL_SETTINGS . ' SET ' .
		" ipcc_url = '$ipcc'," .
		" stylesheet_url = '$stylesheet'," .
		" logo_url = '$logo'," .
		" blog_name = '$name'," .
		" blog_description = '$description'," .
		" block_path = '$block_path'" .
		' WHERE configuration_id = 1 ' );

    ipv_db_cleanup();

	return $q_result;

}

function ipv_get_stylesheet_url() {

	ipv_db_connect();

	$q_result = ipv_db_query(
		' SELECT stylesheet_url FROM ' . IPV_GLOBAL_SETTINGS .
		' WHERE configuration_id = 1 ' );

	$row = ipv_db_fetch_assoc( $q_result );

    ipv_db_cleanup();

	return $row['stylesheet_url'];

}

function ipv_get_ipcc_url() {

	ipv_db_connect();

	$q_result = ipv_db_query(
		' SELECT ipcc_url FROM ' . IPV_GLOBAL_SETTINGS .
		' WHERE configuration_id = 1 ' );

	$row = ipv_db_fetch_assoc( $q_result );

    ipv_db_cleanup();

	return $row['ipcc_url'];

}

function ipv_get_blog_name() {

	ipv_db_connect();

	$q_result = ipv_db_query(
		' SELECT blog_name FROM ' . IPV_GLOBAL_SETTINGS .
		' WHERE configuration_id = 1 ' );

	$row = ipv_db_fetch_assoc( $q_result );

    ipv_db_cleanup();

	return $row['blog_name'];

}

function ipv_get_blog_description() {

	ipv_db_connect();

	$q_result = ipv_db_query(
		' SELECT blog_description FROM ' . IPV_GLOBAL_SETTINGS .
		' WHERE configuration_id = 1 ' );

	$row = ipv_db_fetch_assoc( $q_result );

    ipv_db_cleanup();

	return $row['blog_description'];

}

function ipv_get_admin_email() {

	ipv_db_connect();

	$q_result = ipv_db_query(
		' SELECT notification_email FROM ' . IPV_GLOBAL_SETTINGS .
		' WHERE configuration_id = 1 ' );

	$row = ipv_db_fetch_assoc( $q_result );

    ipv_db_cleanup();

	return $row['notification_email'];


}

function ipv_get_block_path() {

	ipv_db_connect();

	$q_result = ipv_db_query(
		' SELECT block_path FROM ' . IPV_GLOBAL_SETTINGS .
		' WHERE configuration_id = 1 ' );

	$row = ipv_db_fetch_assoc( $q_result );

    ipv_db_cleanup();

	return $row['block_path'];


}

function ipv_appeals_are_enabled() {

	ipv_db_connect();

	$q_result = ipv_db_query(
		' SELECT appeals_enabled FROM ' . IPV_GLOBAL_SETTINGS .
		' WHERE configuration_id = 1 ' );

	$row = ipv_db_fetch_assoc( $q_result );

    ipv_db_cleanup();

	return $row['appeals_enabled'];

}

function ipv_email_is_custom() {

	ipv_db_connect();

	$q_result = ipv_db_query(
		' SELECT notification_is_custom FROM ' . IPV_GLOBAL_SETTINGS .
		' WHERE configuration_id = 1 ' );

	$row = ipv_db_fetch_assoc( $q_result );

    ipv_db_cleanup();

	return $row['notification_is_custom'];

}

function ipv_receives_reports() {

	ipv_db_connect();

	$q_result = ipv_db_query(
		' SELECT receive_update_email FROM ' . IPV_GLOBAL_SETTINGS .
		' WHERE configuration_id = 1 ' );

	$row = ipv_db_fetch_assoc( $q_result );

    ipv_db_cleanup();

	return $row['receive_update_email'];

}

function ipv_set_default_email( $admin_email ) {

    ipv_db_connect();

	$admin_email = ipv_escape_string( $admin_email );

    $q_result = ipv_db_query( 'UPDATE ' . IPV_GLOBAL_SETTINGS .
        " SET notification_email='$admin_email' " .
        ' WHERE configuration_id = 1 ' );

    ipv_db_cleanup();

}

function ipv_activate_wp_config() {

	// make a backup
	$wp_config = dirname( __FILE__ ) . '/../../../../wp-config.php';
	$wp_backup = $wp_config . '.ipv.bak';

	clearstatcache(false, $wp_config);
		// only proceed if we can successfully make a backup copy
	if ( ! @copy ( $wp_config, $wp_backup ) ) return false;

	clearstatcache(false, $wp_backup);
	if ( ! file_exists ( $wp_backup ) ) return false;

	clearstatcache(false, $wp_backup);
	if ( ! @filesize( $wp_backup ) ) return false;

	clearstatcache(false, $wp_backup);
	if ( @filesize( $wp_backup ) != @filesize( $wp_config ) ) return false;

	clearstatcache(false, $wp_config);
	clearstatcache(false, $wp_backup);

	$ip_code = <<<EOC

/*** BEGIN IPVENGER CODE BLOCK ***/

\$validate_include = dirname(__FILE__) .  '/wp-content/plugins' .
        '/ipvenger/core-includes/ipv_validate.php';

if ( file_exists ( \$validate_include ) ) {

    require_once( \$validate_include );

    ipv_gatekeeper();

}

/*** END IPVENGER CODE BLOCK ***/

EOC;

	// in some unusual configurations, wp-config is read-only yet we have
	// permission to chmod it and can avoid manual intervention by doing so
	if (!is_writable($wp_config)) {
	    $old_perms = fileperms( $wp_config );	// save original permissions
	    $new_perms = $old_perms | 0x0090;		// user/group writable
	    @chmod( $wp_config, $new_perms );		// make the change if possible
	    clearstatcache(false, $wp_config);
	}

	// read and write the wp-config looking for the end of customization
	// at which point, insert the activation block

	$in  = @fopen( $wp_backup, 'r');
	$out = @fopen( $wp_config, 'w');

	if ( ! ( $in && $out ) ) return false;

    while (($buffer = fgets($in, 4096)) !== false) {

		// delete any "old" venger code blocks
		if ( strpos( $buffer, 'BEGIN IPVENGER CODE BLOCK' ) !== FALSE ) {
			while (($buffer = fgets($in, 1023)) !== false) {
				if ( strpos( $buffer, 'END IPVENGER CODE BLOCK' ) !== FALSE ) {
					if ( $buffer !== false ) $buffer = fgets($in, 4096);
					break;
				}
			}
		}

		if ( strpos( $buffer, 'That\'s all, stop editing!' ) !== FALSE ) {
			fputs( $out, $ip_code );
		}
		fputs($out, $buffer);
    }
    if (!feof($in)) {
		return false;
    }
    fclose($in);
    fclose($out);

	// put the permissions back the way the user had them

	if ( isset($old_perms) ) @chmod( $wp_config, $old_perms );

	return true;
}

function ipv_deactivate_wp_config() {

	$wp_config = dirname( __FILE__ ) . '/../../../../wp-config.php';
	$wp_backup = $wp_config . '.ipv.bak';

	// in some unusual configurations, wp-config is read-only yet we have
	// permission to chmod it and can avoid manual intervention by doing so
	if (!is_writable($wp_config)) {
	    $old_perms = fileperms( $wp_config );	// save original permissions
	    $new_perms = $old_perms | 0x0090;		// user/group writable
	    @chmod( $wp_config, $new_perms );		// make the change if possible
	    clearstatcache(false, $wp_config);
	}

	if (( file_exists ( $wp_backup ) ) &&
		( @filesize( $wp_backup ) ) &&
		( @copy ( $wp_backup, $wp_config ) ) )
	{
			@unlink( $wp_backup );
	}
	else {
		trigger_error( 'Error updating wp-config.php.  You must ' .
		'remove the "IPVenger Code Block" from wp-config.php manually ' .
		'to deactivate IPVenger security.', E_USER_WARNING );
	}

	// put the permissions back the way the user had them

	if ( isset($old_perms) ) @chmod( $wp_config, $old_perms );

}

function ipv_plugin_set_active( $active ) {

	ipv_db_connect();

	if ( $active ) $state = 'true';
	else $state = 'false';

	$q_result = ipv_db_query(
		'UPDATE ' . IPV_GLOBAL_SETTINGS . " SET plugin_is_active = $state " .
		'WHERE configuration_id = 1');

	ipv_db_cleanup();

}

function ipv_plugin_is_active() {

	ipv_db_connect();

	$q_result = ipv_db_query(
		' SELECT plugin_is_active FROM ' . IPV_GLOBAL_SETTINGS .
		' WHERE configuration_id = 1 ' );

	if ( ! $q_result ) {
		ipv_db_cleanup();
		return false;
	}
	else {
		$row = ipv_db_fetch_assoc( $q_result );
		ipv_db_cleanup();
	}

	return $row['plugin_is_active'];

}

/**
 * function ipv_matches_exception: see whether the given string matches
 * an exception rule for implementing whitelist/blacklist lookup
 *
 * Note:  calling function must escape/clean all args for safe SQL
 *
 * @param	string  $action  	one of "deny" or "allow"
 * @param	string	$excp_type	type of entry, e.g. "ip" or "country"
 * @param	string	$mask		value against which to test candidates
 *
 * @return  float   return true if matches, false otherwise
 *
*/
function ipv_matches_excp( $action, $excp_type, $mask ) {

	ipv_db_connect();

    // strip quotes (values gotten from XML (e.g. country) are quoted
	$mask = trim( $mask, '"' );
	$mask = trim( $mask, "'" );

	$excp_type 	= ipv_escape_string( $excp_type );
	$mask 		= ipv_escape_string( $mask );
	$action 	= ipv_escape_string( $action );

	$q_str = 'SELECT action FROM ' . IPV_EXCEPTION . ' ' .
		"WHERE mask_type='exact' AND excp_type='$excp_type' " .
		"AND mask='$mask' AND action='$action'";

	$q_result = ipv_db_query( $q_str );

	if ( ( $q_result ) && ( ipv_db_num_rows( $q_result ) > 0 ) ) {
		return true;
	}

	// if the search is by IP, we also have to check the wildcard mask
	// which means pulling all the wildcard rules and checking individually
	if ( $excp_type == 'ip' ) {

		$q_str = 'SELECT mask FROM '. IPV_EXCEPTION . ' ' .
			'WHERE excp_type=\'ip\' AND mask_type=\'wildcard\' AND ' .
            "action = '$action' ";

        $q_result = ipv_db_query( $q_str );

        if ( $q_result ) {
            while ( $row = ipv_db_fetch_assoc( $q_result ) ) {
                if ( matches_mask(
					$row['mask'], trim( $mask, '"' ), 'wildcard' ) )
				{
					return true;
				}
			}
		}
	}

	ipv_db_cleanup();

	// if we got this far, no matching rule was not found
	return false;
}

function ipv_ip_is_blacklisted( $ip ) {
	return ipv_matches_excp( 'deny', 'ip', $ip );
}

function ipv_ip_is_whitelisted( $ip ) {
	return ipv_matches_excp( 'allow', 'ip', $ip );
}

function ipv_country_is_blacklisted( $country ) {
	return ipv_matches_excp( 'deny', 'country', $country );
}

/* warning:  for performance reason, $ip is not escaped, only call this */
/* function if you are in total control of the contents of $ip.  Note 	*/
/* that $_SERVER['REMOTE_ADDR'] is a safe source for an IP string 		*/

function ipv_ip_is_cached( $ip, &$status, &$id ) {

	$q_str = 'SELECT ipv_int_request_id, ipv_int_disp FROM ' .
		IPV_REQUEST_DETAIL . ' ' .
		"WHERE ip='$ip' " .
		'AND ipv_int_time > DATE_SUB(NOW(), INTERVAL 6 MINUTE) ' .
		'AND ipv_int_time > ' .
		'( SELECT ipv_cache_invalid_time FROM ' . IPV_CACHE . ' ) ' .
		'ORDER BY ipv_int_time DESC LIMIT 1';

	$q_result = ipv_db_query( $q_str );

	if ( ( $q_result ) && ( $row = ipv_db_fetch_assoc( $q_result ) ) )
	{
			$status = $row['ipv_int_disp'];
			$id = $row['ipv_int_request_id'];
			return true;
	}

	return false;

}

function ipv_get_category_tooltip( $id ) {

	ipv_db_connect();

	$id = ipv_escape_string( $id );

	$q_str = 'SELECT text FROM '. IPV_TOOLTIP . ' ' .
			 "WHERE id='$id' ";

	$q_result = ipv_db_query( $q_str );

	if ( ( $q_result ) && ( $row = ipv_db_fetch_assoc( $q_result ) ) )
	{
		$r = $row['text'];
	}
	else $r = "Sorry, no help text is available for this category.";

	ipv_db_cleanup();

	return $r;
}

function ipv_cache_last_invalidated() {

	$q_str = 'SELECT UNIX_TIMESTAMP( ipv_cache_invalid_time ) ' .
			 'as u_time FROM '. IPV_CACHE;

	$q_result = ipv_db_query( $q_str );

	if ( ( $q_result ) && ( $row = ipv_db_fetch_assoc( $q_result ) ) )
	{
		return $row['u_time'];
	}
	else return 0;

}

function ipv_invalidate_cache() {

	$q_str = 'UPDATE ' . IPV_CACHE . ' SET ipv_cache_invalid_time = now()';

	$q_result = ipv_db_query( $q_str );

}

?>
