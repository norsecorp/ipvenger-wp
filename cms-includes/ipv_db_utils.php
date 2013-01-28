<?php

/*
 * *********  Wordpress Version **********
 *
 * database connection and cleanup functions
 *
 * It would be preferable to wrap the wordpress database (wpdb) library,
 * but to include these libraries without loading all of wordpress
 * requires an excessive number of workarounds.  For this reason this
 * is just a wrapper of the ordinary mysql_ library.
 *
 * If at some point WP provides a clean separation of db API from presentation
 * loop this should be revisited.
 *
*/

require_once( dirname( __FILE__ ) . '/../../../../wp-config.php' );

// support wordpress table prefixing by computing "real" table names
// be sure any changes here are carried down into ipv_db_count_tables

global $table_prefix, $ipvdb;

// these tables contain user data which must be migrated release to release

define( 'IPV_EXCEPTION',  		$table_prefix . 'ipv_exception' );
define( 'IPV_GLOBAL_SETTINGS', 	$table_prefix . 'ipv_global_settings' );
define( 'IPV_REQUEST_DETAIL',  	$table_prefix . 'ipv_request_detail' );
define( 'IPV_APPEAL',  			$table_prefix . 'ipv_appeal' );

// these tables are all static or transient data and are dropped/created
// at each plugin activation to facilitate maintenance

define( 'IPV_SITE_TYPE',  		$table_prefix . 'ipv_site_type' );
define( 'IPV_TOOLTIP',  		$table_prefix . 'ipv_tooltip' );
define( 'IPV_CACHE',  			$table_prefix . 'ipv_cache' );
define( 'IPV_CAPTCHA_SERVED', 	$table_prefix . 'ipv_captcha_served' );
define( 'IPV_CAPTCHA_CACHE', 	$table_prefix . 'ipv_captcha_cache' );

function ipv_db_connect() {

	global $table_prefix, $ipvdb;

	if (!$ipvdb || !is_resource($ipvdb) || !mysql_ping($ipvdb)) {

	    $ipvdb = NULL;

	    // we already have the connection info defined in wp-config
	    if ( WP_DEBUG ) {
	        $ipvdb = mysql_connect( DB_HOST, DB_USER, DB_PASSWORD, defined( 'MYSQL_NEW_LINK' ) ? MYSQL_NEW_LINK : true, defined( 'MYSQL_CLIENT_FLAGS' ) ? MYSQL_CLIENT_FLAGS : 0 );
	    } else {
	        $ipvdb = @mysql_connect( DB_HOST, DB_USER, DB_PASSWORD, defined( 'MYSQL_NEW_LINK' ) ? MYSQL_NEW_LINK : true, defined( 'MYSQL_CLIENT_FLAGS' ) ? MYSQL_CLIENT_FLAGS : 0 );
	    }

	    if ( function_exists( 'mysql_set_charset' ) ) {
	        mysql_set_charset( defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8', $ipvdb );
	    } else {
	        ipv_db_query(sprintf('SET NAMES %s COLLATE %s', defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8', defined( 'DB_COLLATE' ) ? DB_COLLATE : 'utf8_general_ci'));
	        ipv_db_query(sprintf('SET CHARACTER SET %s', defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8'));
	    }
	}

	mysql_select_db( DB_NAME, $ipvdb );

}

function ipv_escape_string( $string ) {

    global $ipvdb;

    if (!$ipvdb || !is_resource($ipvdb)) {
        ipv_db_connect();
    }

    return mysql_real_escape_string($string, $ipvdb);
}


function ipv_db_cleanup() {

	// wordpress and php take care of this

}

function ipv_db_query( $query ) {

    global $ipvdb;

    if (!$ipvdb || !is_resource($ipvdb)) {
        ipv_db_connect();
    }

    return mysql_query( $query , $ipvdb );

}

function ipv_insert_id() {

    global $ipvdb;

    return mysql_insert_id( $ipvdb );

}

function ipv_db_num_rows( $result ) {

    return mysql_num_rows( $result );

}

function ipv_db_fetch_assoc( $result ) {

    return mysql_fetch_assoc( $result );

}

function ipv_db_fetch_row( $result ) {

    return mysql_fetch_row( $result );

}


/** Everything from this point on lives in Wordpress, so we can use wpdb **/

// return number of tables
function ipv_db_count_tables() {

	global $wpdb;

	$pfx = $wpdb->base_prefix;

	$count = $wpdb->query(
		'SELECT table_name FROM INFORMATION_SCHEMA.TABLES ' .
 		'WHERE table_schema = \'' . DB_NAME . '\' AND ' .
 		'table_name LIKE \'' . $pfx . 'ipv_%\''

	);


	return $count;

}

/**
 *  Called by nightly cleanup to delete request detail records over 30 days
 *  old and appeal records over 48 hours old
*/

function ipv_db_purge_expired() {

	global $wpdb;

	// purge request details more than 30 days old
	$wpdb->query(
		'DELETE FROM ' . IPV_REQUEST_DETAIL .
		' WHERE ipv_int_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY)'
	);

	// purge appeal requests more than 48 hours old
	$wpdb->query(
		'DELETE FROM ' . IPV_APPEAL .
		' WHERE timestamp < (NOW() - INTERVAL 48 HOUR)'
	);

}

function ipv_db_drop_tables() {

	global $wpdb;

	$query = 'DROP TABLE ' .
			IPV_EXCEPTION . ', ' .
			IPV_GLOBAL_SETTINGS . ', ' .
			IPV_REQUEST_DETAIL . ', ' .
			IPV_APPEAL;

	$wpdb->query( $query );

	ipv_db_drop_static_tables();

}

function ipv_db_create_tables() {

	global $wpdb;

	// redefine constants as variables for convenience in heredocs

	$ipv_exception_name 		= IPV_EXCEPTION;
	$ipv_global_settings_name 	= IPV_GLOBAL_SETTINGS;
	$ipv_request_detail_name 	= IPV_REQUEST_DETAIL;
	$ipv_appeal_name 			= IPV_APPEAL;

	$err_text = 'Database creation failed';

	$query = <<<EOQ
	# these are the exceptions (initially, actions are either allow (whitelist)
	# or deny (blacklist).  We have separate subcategories for exact match and
	# for wildcard, since wildcard elements must be processed individually while
	# exact matches can be searched using SQL LIKE.  we expect the number of
	# exceptions to be small enough to put all in one table

	# mask and type together constitute a unique rule i.e. we enforce that for
	# a given field (for example) we cannot have both a whitelist and blacklist
	# defined by the same mask

	create table $ipv_exception_name (
		id int key not null auto_increment,
		action enum( 'allow', 'deny' ),
		mask varchar(200) not null,
		mask_type enum( 'exact', 'wildcard', 'ip_range' ),
		excp_type varchar(55),
		unique key ( mask, excp_type )
	);
EOQ;

	if ( $wpdb->query( $query ) === FALSE ) {
		trigger_error( $err_text, E_USER_ERROR );
	}

	$query = <<<EOQ
	# user appeals

	create table $ipv_appeal_name (
		appeal_id 	int key not null auto_increment,
		timestamp 	timestamp,
		ip 			varchar(16),
		request_id 	bigint,
		email	  	varchar(128)
	);
EOQ;

	if ( $wpdb->query( $query ) === FALSE ) {
		trigger_error( $err_text, E_USER_ERROR );
	}

	$query = <<<EOQ
	# global configuration settings
	create table $ipv_global_settings_name (
		configuration_id int primary key not null auto_increment,
		default_risk_threshold float,
		plugin_is_active bool,
		site_type varchar(32),
		api_key varchar(255),
		api_valid bool,
		api_reason varchar(32),
		api_valid_as_of timestamp,
		receive_update_email bool,
		notification_email varchar(320),
		notification_is_custom bool,
		ipcc_url varchar(1024),
		block_path varchar(1024),
		logo_url varchar(1024),
		stylesheet_url varchar(1024),
		blog_name varchar(1024),
		blog_description varchar(1024),
		detail_record_retention_days int,
		ipv_server_url varchar(255),
		block_msg_general varchar( 4096 ),
		block_msg_proxy varchar( 4096 ),
		block_msg_botnet varchar( 4096 ),
		appeals_enabled bool
	);
EOQ;

	if ( $wpdb->query( $query ) === FALSE ) {
		trigger_error( $err_text, E_USER_ERROR );
	}

	$query = <<<EOQ
	insert into $ipv_global_settings_name values (
		null,
		72,
		false,
		'default',
		'',
		false,
		'never',
		NULL,
		true,
		NULL,
		false,
		NULL,
		NULL,
		NULL,
		NULL,
		NULL,
		NULL,
		30,
		'http://us.api.ipviking.com/api/',
		'<h1>Forbidden</h1><p>Your access to this website has been blocked because your computer or the network from which you are connecting has been assessed as high risk and a potential security threat. If you believe this to be an error, please use the form below to request temporary site access.</p>',
		'<h1>Forbidden</h1><p>Your access to this website has been blocked because you are accessing the Internet through an anonymizing proxy that has been identified as a high risk and a potential security threat. If you believe this to be an error, please use the form below to request temporary site access.</p>',
		'<h1>Forbidden</h1><p>Your access to this website has been blocked because we have detected high risk and potentially malicious network traffic originating from your computer or the network from which you are connecting.If you believe this to be an error, please use the form below to verify your identity and request temporary site access.</p>',
		TRUE
		);
EOQ;

	if ( $wpdb->query( $query ) === FALSE ) {
		trigger_error( $err_text, E_USER_ERROR );
	}

	$query = <<<EOQ
	# detail reporting table - raw dump of all IPV risk data. IMPORTANT -
	# any "internal use" records should start ipv_int as the other columns
	# are automatically extracted as keywords for parsing the response xml
	create table $ipv_request_detail_name (
		ipv_int_request_id bigint key not null auto_increment,
		ipv_int_time timestamp,
		ipv_int_date date,
		ip varchar(16),
		risk_factor float,
		risk_color varchar(16),
		risk_name varchar(16),
		risk_desc varchar(32),
		timestamp timestamp,
		factor_entries smallint,
		autonomous_system_number int,
		autonomous_system_name varchar(255),
		country varchar(255),
		country_code varchar(32),
		region varchar(32),
		region_code varchar(32),
		city varchar(128),
		latitude float,
		longtitude float,
		internet_service_provider varchar(255),
		organization varchar(255),
		country_risk_factor float,
		region_risk_factor float,
		ip_resolve_factor float,
		asn_record_factor float,
		asn_threat_factor float,
		bgp_delegation_factor float,
		iana_allocation_factor float,
		ipviking_personal_factor float,
		ipviking_category_factor float,
		ipviking_geofilter_factor float,
		ipviking_geofilter_rule float,
		data_age_factor float,
		search_volume_factor float,
		ipv_int_category_name varchar(128),
		ipv_int_category_id smallint,
		ipv_int_factor_name varchar(128),
		ipv_int_disp bool,
		ipv_int_disp_reason varchar(40)
	);
EOQ;

	if ( $wpdb->query( $query ) === FALSE ) {
		trigger_error( $err_text, E_USER_ERROR );
	}

	/* create time index to support fast IP cacheing direct from this table */
	$query =
		"create index time_index on $ipv_request_detail_name ( ipv_int_time )";

	if ( $wpdb->query( $query ) === FALSE ) {
		trigger_error( $err_text, E_USER_ERROR );
	}

	ipv_db_create_static_tables();

}

/* new plugin version schema updates to tables containing user data */

function ipv_db_update_schema() {

	global $wpdb;

	// nothing to do yet

}

/* broken out as separate functions so tables containing "static" data 	*/
/* can be automatically added/deleted/modified with plugin updates 	 	*/

function ipv_db_drop_static_tables() {

	global $wpdb;

	$wpdb->query( 'DROP TABLE ' . IPV_TOOLTIP );
	$wpdb->query( 'DROP TABLE ' . IPV_SITE_TYPE );
	$wpdb->query( 'DROP TABLE ' . IPV_CACHE );
	$wpdb->query( 'DROP TABLE ' . IPV_CAPTCHA_SERVED );
	$wpdb->query( 'DROP TABLE ' . IPV_CAPTCHA_CACHE );

}

/* broken out as separate functions so tables containing "static" data 	*/
/* can be automatically added/deleted/modified with plugin updates 	 	*/

function ipv_db_create_static_tables() {

	global $wpdb;

	$ipv_cache_name 			= IPV_CACHE;
	$ipv_captcha_served_name	= IPV_CAPTCHA_SERVED;
	$ipv_captcha_cache_name		= IPV_CAPTCHA_CACHE;
	$ipv_site_type_name 		= IPV_SITE_TYPE;
	$ipv_tooltip_name 			= IPV_TOOLTIP;

	$query = <<<EOQ

	create table $ipv_cache_name (
		ipv_cache_invalid_time timestamp,
		ipv_captcha_count int,
		ipv_captcha_last int,
		ipv_captcha_first int
	);
EOQ;

	if ( $wpdb->query( $query ) === FALSE ) {
		trigger_error( $err_text, E_USER_ERROR );
	}

	$query = "insert into $ipv_cache_name values ( now(), 100, -1, -1 )";
	if ( $wpdb->query( $query ) === FALSE ) {
		trigger_error( $err_text, E_USER_ERROR );
	}

	$query = <<<EOQ

	create table $ipv_captcha_cache_name (
		id			int,
		captcha		blob,
		response	varchar(32)
	);
EOQ;

	if ( $wpdb->query( $query ) === FALSE ) {
		trigger_error( $err_text, E_USER_ERROR );
	}

	$query = <<<EOQ
	create table $ipv_captcha_served_name (
		ip		varchar(16),
		id		int,
	    primary key (ip)
	);
EOQ;

	if ( $wpdb->query( $query ) === FALSE ) {
		trigger_error( $err_text, E_USER_ERROR );
	}

	$query = <<<EOQ

	create table $ipv_site_type_name (
		type_short_name varchar(32),
		type_display_name varchar(64),
		ipq_level float,
		ipq_level_desc varchar(32),
		type_descriptive_text varchar(255)
	);
EOQ;

	if ( $wpdb->query( $query ) === FALSE ) {
		trigger_error( $err_text, E_USER_ERROR );
	}

	$query = <<<EOQ
	insert into $ipv_site_type_name values
		( 'default', 'Default Site', 42, 'medium-high',
		  "You will block traffic that is shown to have <ul> <li> significant risk characteristics seen in past 24 hours</li> <li> significant prior risky characteristics seen</li> <li> significant IP address irregularities seen</li> </ul>"
		),
		( 'custom', 'Custom Site', 50, 'custom', ""
		),
		( 'ecommerce', 'eCommerce Store', 33, 'high',
		  "You will block traffic that is shown to have <ul> <li> some risky characteristics seen in past 24 hours</li> <li> some prior risky characteristics seen</li> <li> some IP address irregularities seen</li> </ul>"
		),
		( 'social', 'Social Platform', 52, 'medium',
		  "You will block traffic that is shown to have <ul> <li> extreme risk indicated due to risk category activities seen in past 24 hours</li> <li> extreme prior risk category activities seen</li> <li> extreme IP address risk behavior seen</li> </ul>"
		),
		( 'corporate', 'Corporate Site', 50, 'medium',
		  "You will block traffic that is shown to have <ul> <li> extreme risk indicated due to risk category activities seen in past 24 hours</li> <li> extreme prior risk category activities seen</li> <li> extreme IP address risk behavior seen</li> </ul>"
		),
		( 'webapp', 'Web Application', 40, 'medium-high',
		  "You will block traffic that is shown to have <ul> <li> significant risk characteristics seen in past 24 hours</li> <li> significant prior risky characteristics seen</li> <li> significant IP address irregularities seen</li> </ul>"
		),
		( 'blog', 'Blog', 48, 'medium',
		  "You will block traffic that is shown to have <ul> <li> extreme risk indicated due to risk category activities seen in past 24 hours</li> <li> extreme prior risk category activities seen</li> <li> extreme IP address risk behavior seen</li> </ul>"
		),
		( 'marketing', 'Marketing Site', 54, 'medium',
		  "You will block traffic that is shown to have <ul> <li> extreme risk indicated due to risk category activities seen in past 24 hours</li> <li> extreme prior risk category activities seen</li> <li> extreme IP address risk behavior seen</li> </ul>"
		)
EOQ;

	if ( $wpdb->query( $query ) === FALSE ) {
		trigger_error( $err_text, E_USER_ERROR );
	}

	$query = <<<EOQ
	# category and factor help text for ajax tooltips
	create table $ipv_tooltip_name (
		id 		varchar(128),
		text	varchar(1024)
	);
EOQ;

	if ( $wpdb->query( $query ) === FALSE ) {
		trigger_error( $err_text, E_USER_ERROR );
	}

	$query = <<<EOQ
	insert into $ipv_tooltip_name values
		( 'Country Risk Factor', 'Relative risk factor associated with the country of record for this IP' ),
		( 'Region Risk Factor', 'Relative risk factor associated with the region from which this IP originates.' ),
		( 'IP Resolve Factor', 'Relative risk factor associated with the domain name and other “reverse lookup” information associated with this IP, both current and historical' ),
		( 'ASN Record Factor', ' For routing purposes, each IP on the Internet is assigned to one of thousands of “autonomous systems” (AS), each with a unique AS Number (ASN).   This factor might appear when the ASN does not match a valid autonomous system, suggesting a forged request.' ),
		( 'ASN Threat Factor', 'For routing purposes, each IP on the Internet is assigned to one of thousands of “autonomous systems” (AS), each with a unique AS Number (ASN).   This factor appears when the entire ASN that this IP belongs to is commonly associated with high-risk activity.' ),
		( 'BGP Delegation Factor', 'BGP (Border Gateway Protocol) is the routing protocol used by the core Internet.  BGP “delegates” routing requests for the specified IP to the Autonomous System to which the IP is assigned.  ' ),
		( 'IANA Allocation Factor', 'IANA (Internet Assigned Numbers Authority) is responsible for allocating IP addresses to Regional Internet Registries (RIR’s), which, in turn, allocate IP addresses to ISP’s and end users.  This factor may appear if an IP has not been allocated, and thus is not a legitimately assigned address.' ),
		( 'IPViking Category Factor', 'This factor appears when the IP has exhibited one or more specific types of risky behavior' ),
		( 'Bogon Unadv', ' A “bogus” IP address that has been assigned by the IANA or a Regional Internet Registry, but has not yet been advertised in the BGP routing tables, and is thus not reachable from the global Internet.' ),
		( 'Bogon Unass', 'A “bogus” IP address that has not been assigned by the IANA or a Regional Internet Registry  to an ISP or end user.' ),
		( 'Proxy', 'An IP address associated with an anonymizer or other Proxy service that indicates likely malicious behavior' ),
		( 'Botnet', 'The IP address has demonstrated behavior consistent with a computer that has been infected by malicious software and is under the control of an attacker.  In most cases the owner of the computer is unaware that it is part of a Botnet.' ),
		( 'Other', 'There are many other categories that may contribute to a high IPQ score, including Child Pornography, CyberTerrorism, Identity Theft, Drugs, Espionage, etc.  ' )
EOQ;

	if ( $wpdb->query( $query ) === FALSE ) {
		trigger_error( $err_text, E_USER_ERROR );
	}

}

?>
