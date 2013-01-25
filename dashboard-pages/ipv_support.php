<?php
    if ( ! isset( $_SESSION['ipv_is_admin'] ) || ! $_SESSION['ipv_is_admin'] )
        die( 'Unauthorized' );
?>

<div><h2 class=dummy></h2></div> <!-- hack for Wordpress nag positioning -->

<div id="ipv">

	<style type="text/css">
	.support-content {
		padding: 20px 10px 10px 10px;
		font-size: 115%;
	}
	   
	 </style>

<?php
	require_once( $GLOBALS['ipv_core_includes'] . '/ipv_config_utils.php' );

	require_once( $GLOBALS['ipv_core_includes'] . '/ipv_api_key.php' );

	$api_is_valid = ipv_api_keymaster( $ipv_api_key );
?>

<script type="text/javascript">

	jQuery(document).ready( function () {

		jQuery(".ipv_title_cluetip").cluetip( { 
			splitTitle: '|', 
			cluetipClass: 'default', 
			activation: 'click',
			showTitle: true,
			sticky: true,
			closePosition: 'title'
		});

		// now remove the titles so they don't show on hover
		jQuery(".ipv_title_cluetip").removeAttr('title');

	} );

</script>

<?php require_once( 'ipv_banner.php' ); ?>

<div class="ipvmain">
	<div class="frame-header">Have Questions?</div>
	<div class="support-content">
		<p>If you're just starting out, read over our <a href="https://norsecorp.desk.com/customer/portal/articles/651558-installation-steps" target="_blank">Installation Instructions</a> and 
			<a href="https://norsecorp.desk.com/customer/portal/articles/651556-system-requirements" target="_blank">System Requirements</a>.</p>
		<p>If you want more information about how to use and configure the plugin, check out information on
			<a href="https://norsecorp.desk.com/customer/portal/topics/283560-advanced-settings/articles" target="_blank">Advanced Settings</a>
		</p>
			
	   <p>General questions? Take a look at other <a href="https://norsecorp.desk.com/" target="_blank">FAQs and support articles</a>.</p>
	   <p>Want to talk to us directly? <a href="https://norsecorp.desk.com/customer/portal/emails/new" target="_blank">Send us a question</a>, and we'll get back to you directly.</p>
	</div>	
	
</div>
