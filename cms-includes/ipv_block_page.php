<?php

/** Wordpress version of IPV "Block" page generation utilities **/

require_once( 'ipv_cms_workarounds.php' );

require_once( dirname( __FILE__ ) .
	'/../core-includes/ipv_config_utils.php' );

/**
 * function ipv_echo_block() - echo the html for the block page
*/

function ipv_echo_block(
	$request_id,
	$msg_type,
	$disp,
	$ip,
	$return_to,
	$msg = NULL )
{

    echo ipv_get_block_header();

    echo '<div id="ipv-user-block-message">';

	if ( isset( $msg ) )
		echo urldecode( $msg );
	else
		echo ipv_get_block_msg( $msg_type );

    echo '</div>';

    if ( ( $disp != 'IP Blacklist' ) && ( ipv_appeals_are_enabled() ) ) {
        echo ipv_get_block_appeal( $request_id, $ip, $return_to );
    }

    echo ipv_get_block_footer();

}

/**
 * function ipv_set_block_header: get block message page header
 *
*/
function ipv_get_block_header() {

	$theme_stylesheet = ipv_get_stylesheet_url();
	$block_stylesheet = ipv_get_block_path() .
		'/css/block_page_style.css';
	$header_image = ipv_get_logo_url();

	$header_html = '';
	if ( $header_image !== '' ) {
		$header_html = "<img src=\"$header_image\">";
	}

	$blog_name = ipv_get_blog_name();
	$blog_description = ipv_get_blog_description();

	$hdr = <<<EOH
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<META http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>IPVenger Block</title>
</head>
<body>
	<link href= "$block_stylesheet"
		rel="stylesheet" type="text/css">


<div id="page" class="hfeed">

<header id="branding" role="banner">
	<hgroup>
		<h1 id="site-title"><a>$blog_name</a></h1>
		<h2 id="site-description">$blog_description</h2>
	</hgroup>
	$header_html
</header>

<div id="main" class="wrap ipv-block">
EOH;

	return $hdr;
}

/**
 * function ipv_get_block_footer: get block message page footer
 *
*/
function ipv_get_block_footer() {

	$trlr = <<<EOT
	<div class="protected-by">
		<h3>Protected by IPVenger</h3>
	</div>
</div>
</body>
</html>
EOT;

	return $trlr;
}

/**
 * function ipv_get_block_appeal: get block page appeal request html
 *
*/
function ipv_get_block_appeal( $request_id, $ip, $return_to ) {

	$ipv_block_path = ipv_get_block_path() . '/core-includes/';

	$captcha_show_url =
		$ipv_block_path . '../ajax-services/ipv_show_captcha.php';

	$post_url = $ipv_block_path . 'ipv_process_appeal.php';

	if ( ! isset( $return_to ) ) $return_to=$_SERVER['REQUEST_URI'];

	if ( $return_to == 'ipv_demo' ) $disable_text = ' disabled="disabled" ';

	$captcha = <<<EOC

	<div id="ipv-block-appeal-form">

	<p id="ipv-block-appeal-msg1">
		To appeal this block and gain temporary access to the site,
		please fill in the information below.
	</p>
	<p id="ipv-block-appeal-msg2">
	The website administrator will be notified of your appeal.
	</p>

	<form action="$post_url" method=post>
		<input type="hidden" name="request_id" value="$request_id" />
		<input type="hidden" name="ip" value="$ip" />
		<input type="hidden" name="return_to" value="$return_to" />
		<br>
		<div id="ipv-block-email-container">
			<span id="ipv-block-email-prompt">Your email address: </span>
			<input type="text" name="email" id="ipv-appeal-email" /><br>
		</div>

		<div id="ipv-block-captcha-container">

			<div id="ipv-block-captcha-image-container">
				<img id="ipv-block-captcha"
					src = "${captcha_show_url}?ip=$ip" />
			</div>

			<div id="ipv-captcha-controls">

				<span id="ipv-captcha-prompt"> Text shown in image:  </span>
				<input type="text" name="captcha_response"
					id="ipv-captcha-response" />
				<br>
				<a href="#"
					onclick="document.getElementById('ipv-block-captcha').src =
						'${captcha_show_url}?ip=$ip&dummy=' + Math.random();
						return false;">
					[ Try a Different Image ]
				</a>

			</div>

		</div>

		<div id="ipv-submit-container">
			<button type=submit id="ipv-submit-appeal" $disable_text>Appeal</button>
		</div>
	</div>

EOC;

	return $captcha;
}

?>
