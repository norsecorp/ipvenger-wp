<?php
/*
Plugin Name: IPVenger
Plugin URI: http://ipvenger.com/
Description: IPVenger website security - configuration and reporting
Version: 1.0.1
Author: NorseCorp
Author URI: http://ipvenger.com/
License: GPLv2
*/

/*
Copyright 2012 NorseCorp
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

// URL path variables for use in analytics html and scripts

$ipv_securimage_home = plugins_url( 'securimage', __FILE__ );
$ipv_jquery_home 	 = plugins_url( 'jquery', __FILE__ );
$ipv_css_home  		 = plugins_url( 'css', __FILE__ );
$ipv_core_home  	 = plugins_url( 'core-includes', __FILE__ );
$ipv_dashboard_home  = plugins_url( 'dashboard-pages', __FILE__ );
$ipv_ajax_home  	 = plugins_url( 'ajax-services', __FILE__ );

// relative path variables

$ipv_cms_includes  	 = plugin_dir_path( __FILE__ ) . 'cms-includes';
$ipv_core_includes 	 = plugin_dir_path( __FILE__ ) . 'core-includes';

// other misc variables
$ipv_admin_e_mail = get_settings('admin_email');

//custom updates/upgrades

$this_file = __FILE__;

require_once( plugin_dir_path( __FILE__ ) . 'core-includes/ipv_validate.php' );

require_once( plugin_dir_path( __FILE__ ) . 'core-includes/ipv_api_key.php' );

require_once( plugin_dir_path( __FILE__ ) .
	'core-includes/ipv_config_utils.php' );

require_once( plugin_dir_path( __FILE__ ) .
	'core-includes/ipv_captcha_utils.php' );

register_activation_hook( __FILE__, 'ipv_activate' );

add_action( 'ipv_cleanup_db', 'ipv_db_purge_expired' );

function ipv_activate() {

	// check/create database
	$table_count = ipv_db_count_tables();

	if ( $table_count === 0 )  {
		ipv_db_create_tables();
	}
	else {
		ipv_db_drop_static_tables();
		ipv_db_create_static_tables();
		ipv_db_update_schema();
	}

	// pregenerate some CAPTCHAs
	ipv_init_captcha_cache();

	// store the direct URL of the IP control center so we can point to it from
	// "outside" wordpress when sending emails from the appeal processor

	$dir = basename( dirname( __FILE__ ) );
	$page = basename( __FILE__ );

	$ipcc_url 		= admin_url("admin.php?page=$dir/${page}__ipcc" );
	$stylesheet_uri = get_stylesheet_uri();
	$logo_uri 		= get_header_image();
	$name 			= get_bloginfo( 'name' );
	$description 	= get_bloginfo( 'description' );
	$block_path	    = plugins_url( '' , __FILE__ );

	ipv_set_blog_info(
		$ipcc_url, $stylesheet_uri, $logo_uri, $name, $description, $block_path
	);

	ipv_set_default_email( get_settings('admin_email') );

	// update the database in case we have reactivated with a custom threshold
    ipv_update_site_ipq( "custom", ipv_get_default_risk() );

	// now activate ip viking protection by updating wp-config
	ipv_activate_wp_config();

	// schedule nightly database cleanup
	wp_schedule_event(
		current_time( 'timestamp' ), 'daily', 'ipv_cleanup_db' );

	// set the database flag
	ipv_plugin_set_active( true );

}

register_deactivation_hook( __FILE__, 'ipv_deactivate' );

function ipv_deactivate() {

	ipv_deactivate_wp_config();

	// set the database flag
	ipv_plugin_set_active( false );

}

/* store ajax authentication info in session, register scripts and styles */

add_action( 'admin_init', 'ipv_session_info' );

function ipv_session_info() {

	$_SESSION['ipv_is_admin'] = current_user_can( 'manage_options' );

	if ( ! isset( $_SESSION['ipv_auth_token'] ) ) {

		if ( function_exists( 'openssl_random_pseudo_bytes' ) )
			$auth_token = bin2hex( openssl_random_pseudo_bytes(128) );
		else
			$auth_token = sha1( uniqid( mt_rand() ) );

		$_SESSION['ipv_auth_token'] = $auth_token;

	}

	wp_register_script(
		'ipv-handle-timeout-script',
		$GLOBALS['ipv_dashboard_home'] . '/ipv_handle_timeout.js',
		false, '1.0', false
	);

	wp_register_script(
		'ipv-cluetip-script',
		$GLOBALS['ipv_jquery_home'] . '/jquery.cluetip.js',
		false, '1.0', false
	);

	wp_register_script(
		'ipv-blockui-script',
		$GLOBALS['ipv_jquery_home'] . '/jquery.blockUI.js',
		false, '1.0', false
	);

	wp_register_script(
		'ipv-combobox-script',
		$GLOBALS['ipv_jquery_home'] . '/jquery.combobox.js',
		array( 'jquery-ui-widget' ), '1.0', false
	);

	wp_register_script(
		'ipv-flot-script',
		$GLOBALS['ipv_jquery_home'] . '/jquery.flot.js',
		false, '1.0', false
	);

	wp_register_script(
		'ipv-flotpie-script',
		$GLOBALS['ipv_jquery_home'] . '/jquery.flot.pie.js',
		false, '1.0', false
	);

	wp_register_script(
		'ipv-flotorderbars-script',
		$GLOBALS['ipv_jquery_home'] . '/jquery.flot.orderBars.js',
		false, '1.0', false
	);

	wp_register_script(
		'ipv-flotstack-script',
		$GLOBALS['ipv_jquery_home'] . '/jquery.flot.stack.js',
		false, '1.0', false
	);

	wp_register_script(
		'ipv-tablesorter-script',
		$GLOBALS['ipv_jquery_home'] . '/jquery.tablesorter.min.js',
		false, '1.0', false
	);

	wp_register_script(
		'ipv-tablesorterwidgets-script',
		$GLOBALS['ipv_jquery_home'] . '/jquery.tablesorter.widgets.min.js',
		false, '1.0', false
	);

	wp_register_script(
		'ipv-fixedheader-script',
		$GLOBALS['ipv_jquery_home'] . '/jquery.fixedHeader.js',
		false, '1.0', false
	);

	wp_register_script(
		'ipv-jvectormap-script',
		$GLOBALS['ipv_jquery_home'] . '/jquery-jvectormap-1.0.min.js',
		false, '1.0', false
	);

	wp_register_script(
		'ipv-jvectormapworldenu-script',
		$GLOBALS['ipv_jquery_home'] . '/jquery-jvectormap-world-en.js',
		false, '1.0', false
	);

	wp_register_script(
		'ipv-excanvas',
		$GLOBALS['ipv_jquery_home'] . '/excanvas.compiled.js',
		false, '1.0', false
	);

	wp_register_script(
		'ipv-countrycodes-script',
		$GLOBALS['ipv_jquery_home'] . '/country_codes.js',
		false, '1.0', false
	);

	wp_register_script(
		'ipv-ipbwlist-script',
		$GLOBALS['ipv_dashboard_home'] . '/ipv_ip_bw_list.js',
		false, '1.0', false
	);

	wp_register_style(
		'ipv-style', $GLOBALS['ipv_css_home'] . '/style.css',
		false, '1.0', 'all'
	);

	wp_register_style(
		'ipv-jqueryui-style',
		$GLOBALS['ipv_jquery_home'] . '/jquery-ui-1.8.19.custom.css',
		false, '1.0', 'all'
	);

	wp_register_style(
		'ipv-cluetip-style',
		$GLOBALS['ipv_jquery_home'] . '/jquery.cluetip.css',
		false, '1.0', 'all'
	);

	wp_register_style(
		'ipv-jvectormap-style',
		$GLOBALS['ipv_jquery_home'] . '/jquery-jvectormap-1.0.css',
		false, '1.0', 'all'
	);

	wp_register_style(
		'ipv-combobox-style',
		$GLOBALS['ipv_jquery_home'] . '/jquery.combobox.css',
		false, '1.0', 'all'
	);

}

/* enqueue scripts and styles 								*/

add_action( 'admin_enqueue_scripts', 'ipv_enqueue_scripts' );

function ipv_enqueue_scripts( $hook ) {

	global $is_IE;

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'ipv-handle-timeout-script' );
	wp_enqueue_script( 'ipv-cluetip-script' );

	switch( $hook ) {
		case 'toplevel_page_ipvenger/ipvenger':
			wp_enqueue_script( 'ipv-blockui-script' );
			break;

		case 'ipvenger_page_ipvenger/ipvenger__analytics':

			if ( $is_IE ) {
				if ( ! function_exists( 'wp_check_browser_version' ) )
					include_once(ABSPATH . 'wp-admin/includes/dashboard.php');

				if (version_compare( intval($response['version']) , 9 ) < 0)
					 wp_enqueue_script( 'ipv-excanvas' );
			}

			wp_enqueue_script( 'jquery-ui-widget' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'ipv-combobox-script' );
			wp_enqueue_script( 'ipv-flot-script' );
			wp_enqueue_script( 'ipv-flotpie-script' );
			wp_enqueue_script( 'ipv-flotstack-script' );
			wp_enqueue_script( 'ipv-flotorderbars-script' );
			wp_enqueue_script( 'ipv-tablesorter-script' );
			wp_enqueue_script( 'ipv-tablesorterwidgets-script' );
			wp_enqueue_script( 'ipv-fixedheader-script' );
			wp_enqueue_script( 'ipv-jvectormap-script' );
			wp_enqueue_script( 'ipv-jvectormapworldenu-script' );
			wp_enqueue_script( 'ipv-countrycodes-script' );
			wp_enqueue_script( 'ipv-ipbwlist-script' );

			break;

		case 'ipvenger_page_ipvenger/ipvenger__ipcc':
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'ipv-ipbwlist-script' );
			break;

		case 'ipvenger_page_ipvenger/ipvenger__adv_settings':
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-widget' );
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'jquery-ui-button' );
			wp_enqueue_script( 'ipv-combobox-script' );
			wp_enqueue_script( 'ipv-jvectormap-script' );
			wp_enqueue_script( 'ipv-jvectormapworldenu-script' );
			wp_enqueue_script( 'ipv-countrycodes-script' );
			break;

		case 'ipvenger_page_ipvenger/ipvenger__support':
			break;
	}
}

function ipv_enqueue_styles() {

	wp_enqueue_style( 'ipv-style' );
	wp_enqueue_style( 'ipv-cluetip-style' );
	wp_enqueue_style( 'ipv-jqueryui-style' );
	wp_enqueue_style( 'ipv-jvectormap-style' );
	wp_enqueue_style( 'ipv-combobox-style' );

}

/*************************************************************************
 *
 * Configuration menus
 *
 ************************************************************************/

add_action( 'admin_menu', 'ipv_menu_main' );

function ipv_menu_main() {

	$page = add_menu_page(
	 	'IPVenger Dashboard', 'IPVenger',
		'manage_options',
		__FILE__,
		'ipv_general',
		plugins_url( '/images/favicon.ico', __FILE__ ) );

	add_action( 'admin_print_styles-' . $page, 'ipv_enqueue_styles' );

	$page = add_submenu_page(
		__FILE__,
	 	'IPVenger Settings', 'General Settings',
		'manage_options',
		__FILE__,
		'ipv_general' );

	add_action( 'admin_print_styles-' . $page, 'ipv_enqueue_styles' );

	require_once( $GLOBALS['ipv_core_includes'] .
		'/ipv_api_key.php' );

	$api_is_valid = ipv_api_keymaster( $ipv_api_key );

	if ( $api_is_valid )  {

        $page = add_submenu_page(
            __FILE__,
            'IPVenger Analytics', 'Analytics',
            'manage_options',
            __FILE__.'__analytics',
            'ipv_analytics' );
            //__FILE__.'__analytics',

		add_action( 'admin_print_styles-' . $page, 'ipv_enqueue_styles' );

        $page = add_submenu_page(
            __FILE__,
            'IPVenger IP Control Center ', 'IP Control Center',
            'manage_options',
            __FILE__.'__ipcc',
            'ipv_ipcc' );

		add_action( 'admin_print_styles-' . $page, 'ipv_enqueue_styles' );

        $page = add_submenu_page(
            __FILE__,
            'IPVenger Advanced Settings', 'Advanced Settings',
            'manage_options',
            __FILE__.'__adv_settings',
            'ipv_advanced' );

		add_action( 'admin_print_styles-' . $page, 'ipv_enqueue_styles' );

	}

/**
	$page = add_submenu_page(
		__FILE__,
	 	'IPVenger Support', 'Support',
		'manage_options',
		__FILE__.'__support',
		'ipv_support' );

		add_action( 'admin_print_styles-' . $page, 'ipv_enqueue_styles' );

**/

}

function ipv_analytics() {
		$parent_slug = __FILE__;
		require_once( plugin_dir_path( __FILE__ ) .
			'dashboard-pages/ipv_analytics.php' );
}

function ipv_ipcc() {
		$parent_slug = __FILE__;
		require_once( plugin_dir_path( __FILE__ ) .
			'dashboard-pages/ipv_ipcc.php' );
}

function ipv_general() {
		$parent_slug = __FILE__;
		require_once( plugin_dir_path( __FILE__ ) .
			'dashboard-pages/ipv_general.php' );
}

function ipv_advanced() {
		$parent_slug = __FILE__;
		require_once( plugin_dir_path( __FILE__ ) .
			'dashboard-pages/ipv_advanced.php' );
}

function ipv_support() {
	    $parent_slug = __FILE__;
	    require_once( plugin_dir_path( __FILE__ ) .
		    'dashboard-pages/ipv_support.php');
}
?>
