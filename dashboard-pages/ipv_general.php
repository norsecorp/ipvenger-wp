<?php
    if ( ! isset( $_SESSION['ipv_is_admin'] ) || ! $_SESSION['ipv_is_admin'] )
        die( 'Unauthorized' );
?>

<div><h2 class=dummy></h2></div> <!-- hack for Wordpress nag positioning -->

<div id="ipv">

<?php
	require_once( $GLOBALS['ipv_core_includes'] .
		'/ipv_config_utils.php' );

	require_once( $GLOBALS['ipv_core_includes'] .
		'/ipv_api_key.php' );

	$api_is_valid = ipv_api_keymaster( $ipv_api_key );

?>

<script type="text/javascript">

	jQuery(document).ajaxComplete( ipv_handle_timeout);

	var custom_unselected_html = 
		"You may also choose a custom IPQ threshold under " + 
		'<a href="<?php
            $page = plugin_basename($parent_slug);
            echo admin_url("admin.php?page=${page}__adv_settings" );
        ?>">' + 
		"Advanced Settings</a>.";

	var custom_selected_html =
		"<strong>You have chosen a custom IPQ threshold under " + 
		'<a href="<?php
            $page = plugin_basename($parent_slug);
            echo admin_url("admin.php?page=${page}__adv_settings" );
        ?>">' + 
		"Advanced Settings</a>.</strong>";

	var preview_url = "<?php echo $GLOBALS['ipv_dashboard_home'] 
			. '/ipv_block_demo.php';?>";

	var ipv_auth_token = "<?php echo $_SESSION['ipv_auth_token']; ?>";

	var ipv_status = "<?php 
		if ( isset( $_SESSION['ipv_status'] ) ) 
			echo $_SESSION['ipv_status']; 
		else
			if ( array_key_exists( 'ipv_status', $_SESSION ) ) 
				echo "NULL";
			else
				echo "UNINITIALIZED" ?>";

	var ipv_register_globals = <?php 
		if ( ini_get( 'register_globals' ) ) echo 'true';
		else echo 'false'; ?>;

	var ipv_key_is_valid = <?php echo json_encode( $api_is_valid ) ?>;	

	var ipv_ajax_home =
		<?php echo json_encode( $GLOBALS['ipv_ajax_home'] );?>;

	function ipv_show_preview( type ) {

		msg = escape( jQuery( "#ipv_" + type + "_msg" ).val() );

		window.open( preview_url + '?type=' + type + '&msg=' + 
			encodeURIComponent( msg ) );

	}


	function ipv_block_divs( valid ) {

		if ( valid ) {
			jQuery( ".ipv_enable_with_key" ).unblock();
		}
		else {
			jQuery( ".ipv_enable_with_key" ).block(
				{ 
					message: null,
					overlayCSS: { 
						backgroundColor: '#fff', 
						cursor: 'not-allowed', 
						opacity: 0.5 },
					fadeIn: 0,
					fadeOut: 0
				});
		}
	}

	var verb = "is";

	function ipv_set_website_info( name, thresh, desc, detail ) { 
		if ( name == "eCommerce" ) ar = "an ";
		else ar = "a ";

		jQuery('#ipv-website-info').html( 
			"You have " + ar + "<b>" + name + "</b>. " + 
			"You will block traffic with an IPQ score of " + 
			'<span id="ipv-threshold-description">' 	
				+ thresh + " and higher.</span>" 
		);
	
		jQuery('#ipv-website-info-detail').html( detail );
	}

	function ipv_update_appeals() {
		
		var disable_appeals = false;

		var cb = jQuery( "#ipv-appeal-checkbox" );

		if ( cb.is( ":checked" ) ) disable_appeals = true;

		if ( disable_appeals ) {

			if ( ! confirm( "Are you sure you want to disallow " + 
					"appeals by blocked users?" ) ) 
			{
				cb.attr('checked', false );
				return;
			}

		}
		else {

			if ( ! confirm( "Are you sure you want resume " + 
						"normal appeal processing?" ) ) 
			{
				cb.attr('checked', true );
				return;
			}

		}

		jQuery.post( ipv_ajax_home + "/ipv_update_appeals.php",
			{
				ipv_auth_token: ipv_auth_token,
				disable_appeals: disable_appeals
			}
		);
	}

	function ipv_update_email() {

		var email_report, is_custom, notification_address;

		jQuery( "#ipv-email-button" ).attr('disabled', 'disabled');

		email_report = jQuery( "#ipv-email-report" ).is( ":checked" );
	
		if ( jQuery( "#ipv-email-custom" ).is( ":checked" ) ) {
			is_custom = true;
			notification_address = jQuery( "#ipv-custom-email-input" ).val();
		}
		else {
			is_custom = false;
			notification_address = 
				"<?php echo $GLOBALS['ipv_admin_e_mail']; ?>";
		}

		jQuery.post( ipv_ajax_home + "/ipv_update_email.php",
			{
				ipv_auth_token: ipv_auth_token,
				email_report: email_report, 
				notification_address: notification_address,
				is_custom: is_custom
			},
			function( data ) {
				if ( data == "invalid email" ) {
					alert( "Please provide a valid email address. " );
					jQuery( "#ipv-email-button" ).removeAttr('disabled');
				}
				else {
					jQuery( "#ipv-email-button" ).val("Email Settings Saved");
				}
			}
		);

	}

	function ipv_email_button_enable() {

		jQuery( "#ipv-email-button" ).val("Save Email Settings");
		jQuery( "#ipv-email-button" ).removeAttr('disabled');

	}

	function ipv_save_button_enable( button_id ) {

		jQuery( button_id ).removeAttr('disabled');
		jQuery( button_id ).val("SAVE");

	}

	function ipv_save_button_disable( button_id ) {

		jQuery( button_id ).val("Saved");
		jQuery( button_id ).attr('disabled', 'disabled');

	}

	function ipv_update_website( apply_update ) {

		if ( apply_update ) {
			jQuery( "#ipv-custom-text" ).html( custom_unselected_html );
			verb = "is";
		}
		else verb = "will be";

		jQuery( "#ipv-site-type-"+site_type ).css('background-color', 'white');

		if ( apply_update ) ipv_save_button_disable( "#ipv-site-type-button" );
		else ipv_save_button_enable( "#ipv-site-type-button" );

		site_type = jQuery('input[name=ipv_website_type]:checked').val();

		jQuery( "#ipv-site-type-" + site_type ).css(
			'background-color', '#E5F7FD');

		jQuery.post( ipv_ajax_home + "/ipv_update_site_type.php",
			{ipv_auth_token: ipv_auth_token, 	
				site_type: site_type, apply_update: apply_update},
			function( data ) {
				ipv_set_website_info( 
					data.long_name, 
					data.ipq_thresh, 
					data.ipq_desc, 	
					data.ipq_detail );
			}, 
			"json"
		);

	}

	function ipv_prep_custom_email( use_custom ) {

		email_text = jQuery( "#ipv-custom-email-input" );

		if ( use_custom ) {
			email_text.removeAttr('disabled');
			if ( email_text.val() == "Email Address" ) email_text.val( "" );
			email_text.focus();
		}
		else { 
			email_text.attr('disabled', 'disabled');
		}

		ipv_email_button_enable();

	}

	generic_error_msg = 
		"<p>IPVenger session error.  Please clear your browser cache and " +
		"log in again.  If this error persists " +
		"please <a target='new' " + 
		"href='http://support.ipvenger.com'>Visit Support</a> " +
		"for assistance.</p>";

	register_globals_msg = 
		"<p>IPVenger is unable to track critical data about your session " +
		"because the php configuration parameter <em>register_globals</em> " + 
		"is set to <em>On</em>.  It is strongly recommended that you disable " +
		"this setting, as it presents a security risk.  To do so, set " +
		"<em>register_globals = Off</em> in your php.ini file.</p>"+

		"<p>If you must leave <em>register_globals</em> enabled, you " + 
		"will need to manually edit your WordPress installation. "+
		"To do so, edit the file wp-includes/load.php, found in your " +
		"WordPress installation directory, and add 'ipv_status' " +
		"and 'ipv_create_time' to the $no_unset array definition " +
		"found in the function wp_unregister_GLOBALS()</p>" + 
	
		"If after completing one of the options above you continue to see "+
		"this message, please <a target='new' " + 
		"href='http://support.ipvenger.com'>Visit Support</a> </p>";

	wp_config_msg = 

		"<p>IPVenger could not update wp-config.php.  This is most likely " +
		"because your web server does not have permission to write to this " +
		"file.</p> " +

		"<p>To complete the installation and activate IPVenger security you " +
		"must update the file manually.  Open your wp-config.php file and " +
		"locate the line that says:</p>" + 

		"<pre>" + 
		"&quot;/* That's all, stop editing! Happy blogging. */&quot;" +
		"</pre>" + 
		
		"<p>Above this line, insert the following text, exactly as " +
		"displayed below, then reload this page to continue.</p>" + 

		"<pre>" +
		"/*** BEGIN IPVENGER CODE BLOCK ***/<br>" +
		"<br>" +
		"$validate_include = dirname(__FILE__) .  '/wp-content/plugins' ." + 
		"<br>" +
		"	'/ipvenger/core-includes/ipv_validate.php';" + 
		"<br>" +
		"<br>" +
		"if ( file_exists ( $validate_include ) ) {" +
		"<br>" +
		"<br>" +
		"	require_once( $validate_include );" +
		"<br>" +
		"	ipv_gatekeeper();" +
		"<br>" +
		"<br>" +
		"}" +
		"<br>" +
		"<br>" +
		"/*** END IPVENGER CODE BLOCK ***/" +
		"</pre>" + 
		"<p>If you continue to see this error after making this change, " +
		"please <a target='new' " + 
		"href='http://support.ipvenger.com'>Visit Support</a> " + 
		"for assistance</p>" ;

	function ipv_installation_failure( msg ) {

		jQuery( "#license-settings-title" ).html(
			"<strong>Activation Error</strong>" );

		jQuery( "#license-status-text" ).html( msg );

		jQuery( "#ipv-activate-button").hide();
		jQuery( "#license-input-container").hide();

		jQuery( "#ipv-license-background-div" ).css("height", "auto");
		jQuery( "#ipv-license-background-div" ).addClass("key-invalid");

		ipv_key_is_valid = false;		

	}

	function ipv_is_api_key_valid() {

		jQuery.post( ipv_ajax_home + "/ipv_is_key_valid.php",
			{ipv_auth_token: ipv_auth_token},
			function( data ) {
				act_button = jQuery( "#ipv-activate-button");
				if ( data.api_valid == 1 ) {
					act_button.attr( "disabled", "disabled" );
					act_button.val( "ACTIVATED" );
					jQuery( "#ipv-license-background-div" )
						.addClass("key-valid").removeClass("key-invalid");
					if ( ! ipv_key_is_valid ) {
						// refresh admin menu
						window.location.reload();
					}
					ipv_key_is_valid = true;		
				}	
				else {
					act_button.removeAttr( "disabled" );
					act_button.val( "ACTIVATE" );
					jQuery( "#ipv-license-background-div" )
						.addClass("key-invalid").removeClass("key-valid");
					if ( ipv_key_is_valid ) {
						// refresh admin menu
						window.location.reload();
					}
					ipv_key_is_valid = false;		
				}
				jQuery( "#license-status-text" ).html( data.reason_text );
			},
			"json"
		);
	}

	function ipv_validate_key(key)
	{

		jQuery.blockUI(
			{ 
				message: null,
				overlayCSS: { 
					cursor: 'wait', 
					opacity: 0.4 
				},
				fadeIn: 0,
				fadeOut: 0
			});

		jQuery.post( ipv_ajax_home + "/ipv_validate_key.php",
			{ipv_auth_token: ipv_auth_token, key: key},
			function( data ) {
				ipv_is_api_key_valid();
			}
		);

		jQuery( document ).ajaxStop( function() {
				jQuery.unblockUI();
				ipv_block_divs( ipv_key_is_valid );
			}
		);

	}

	jQuery(document).ready( function () {

		if ( ( ipv_status == "" ) || ( ipv_status == "NULL" ) )
			if ( ipv_register_globals )	
				ipv_installation_failure( register_globals_msg );
			else
				ipv_installation_failure( generic_error_msg );
		else 
			if ( ipv_status == "UNINITIALIZED" ) 
				ipv_installation_failure( wp_config_msg );
			else 
				ipv_is_api_key_valid();


		ipv_block_divs( ipv_key_is_valid ); 
	
		jQuery(".ipv-title-cluetip").cluetip( { 
			splitTitle: '|', 
			cluetipClass: 'default', 
			activation: 'click',
			showTitle: true,
			sticky: true,
			closePosition: 'title'
		});

		jQuery( "#ipv-api-key" ).focus( function() {
			var act_button = jQuery( "#ipv-activate-button");
			act_button.removeAttr( "disabled" );
			if ( act_button.val() == "ACTIVATED" ) act_button.val( "CHANGE" );
		});

		// now remove the titles so they don't show on hover
		jQuery(".ipv-title-cluetip").removeAttr('title');

	} );

</script>

<?php require_once( 'ipv_banner.php' ); ?>

<div id="ipv-license-background-div" 
		class="general ipvmain license-box">
	<div class="license-settings-text">
		<div id="license-settings-title"></div>
		<div class="text" id="license-status-text">
			Enter the key that was sent to you by email to begin protecting
			your site from dangerous IPs.  To get a product license, visit the 
			IPVenger website.
		</div>
	</div>
	<div id="license-input-container">
		<div style="float:left">
		<input type="text" id="ipv-api-key" 
			value="<?php echo $ipv_api_key ?>"
		/>
		</div>
		<div style="float:left">
		<input type="button" class="ipv-secondary"
			id="ipv-activate-button"
			onclick="ipv_validate_key(
				document.getElementById( 'ipv-api-key' ).value)" 
			value="ACTIVATE"
		 />
		</div>
	</div>
</div>

<div class="general ipvmain ipv_enable_with_key">
	<div class="frame-header">
		<img src= "<?php
				echo plugins_url( 'images', dirname( __FILE__ ) )
			?>/hdr-text/security_settings.png">
	</div>
<div>
	<div id="ipv-settings-text">
		<h3>Recommended IPVenger Settings</h3>
		<p id="ipv-website-info">
			You have an eCommerce website.  
			Your recommended IPQ threshold will be set to 
			<span id="ipv-threshold-description">high</span>.
		</p>
		<div id="ipv-website-info-detail">
			You will block traffic that is shown to have
			<ul>
			<li> significant risk characteristics seen in past 24 hours</li>
			<li> significant prior risky characteristics seen</li>
			<li> significant IP address irregularities seen</li>
			</ul>
		</div>
	</div>

	<p id="ipv-site-type-header">
		The main function of my website is (choose one):
		<img class="ipv-title-cluetip" src=
		"<?php
			echo plugins_url( 'images', dirname( __FILE__ ) )
		?>/tooltip_question.png" alt="?"
		class="ipv-title-cluetip"
		title="Website Function||Choose the one option that best describes your website.  The site type determines the maximum IPQ score that should be deemed an &quot;acceptable risk&quot;.|The IPQ threshold assigned to each site type is based on many factors, among them how attractive sites of this type are to attackers, and how likely an attack is to succeed if access to the site is allowed.|These factors are weighed against the relative cost and inconvenience of inadvertently blocking a legitimate user."
		>
	</p>

	<div id="ipv-site-type-container">
		<div id="ipv-site-type-default" class="ipv-site-type-option">
		<input type="radio" name="ipv_website_type" value="default"
			onclick="ipv_update_website( false )" >
			<b>Default</b> - recommended security level appropriate for most
			typical websites 
		</div>
		<div id="ipv-site-type-marketing" class="ipv-site-type-option">
		<input type="radio" name="ipv_website_type" value="marketing"
			onclick="ipv_update_website( false )" >
			<b>Marketing</b> - consumer education and 
			marketing with no sensitive corporate or consumer data 
		</div>
		<div id="ipv-site-type-blog" class="ipv-site-type-option">
		<input type="radio" name="ipv_website_type" value="blog"
			onclick="ipv_update_website( false )" >
			<b>Blog</b> - a personal blog that allows user comments
		</div>
		<div id="ipv-site-type-webapp" class="ipv-site-type-option">
		<input type="radio" name="ipv_website_type" value="webapp"
			onclick="ipv_update_website( false )" >
			<b>Web App</b> - a software-as-a-service (SaaS) product 
			that stores account information 
		</div>
		<div id="ipv-site-type-corporate" class="ipv-site-type-option">
		<input type="radio" name="ipv_website_type" value="corporate"
			onclick="ipv_update_website( false )" >
			<b>Corporate</b> - a high traffic, high visibility 
			site that supports a widely recognized brand
		</div>
		<div id="ipv-site-type-social" class="ipv-site-type-option">
		<input type="radio" name="ipv_website_type" value="social"
			onclick="ipv_update_website( false )" >
			<b>Social Platform</b> - a social networking 
			site or web forum with multiple users 
		</div>
		<div id="ipv-site-type-ecommerce" class="ipv-site-type-option">
		<input type="radio" name="ipv_website_type" value="ecommerce"
			onclick="ipv_update_website( false )" >
			<b>eCommerce</b> - processing payments, purchases, or 
			transactions containing sensitive data
		</div>
		<div id="ipv-site-type-custom" class="ipv-site-type-option">
			<span id="ipv-custom-text"></span> 
		</div>
	</div>
	
	<div id="ipv-site-type-buttons">

		<input type="button" class="ipv-primary" value="SAVE"
			id="ipv-site-type-button"
			disabled="disabled"
			onclick="ipv_update_website( true )">

	</div>
	
</div>
</div>

<script type="text/javascript">

	var site_type;

	<?php

		$rc = ipv_get_site_type( 
			$short_name, $long_name, $ipq_thresh, $ipq_desc, $ipq_detail  );
	
		echo "var rc = \"$rc\";";
		echo "var short_name = \"$short_name\";";
		echo "var long_name = \"$long_name\";";
		echo "var ipq_thresh = \"$ipq_thresh\";";
		echo "var ipq_desc = \"$ipq_desc\";";
		echo "var ipq_detail = \"$ipq_detail\";";

	?>

	// set the correct explanatory text
	ipv_set_website_info( long_name, ipq_thresh, ipq_desc, ipq_detail );

	site_type = short_name;

    var radios = jQuery('input:radio[name=ipv_website_type]');
		
	if ( short_name != "custom" ) {
		radios.filter('[value=' + short_name + ']').attr('checked', true);
		jQuery( "#ipv-custom-text" ).html( custom_unselected_html );
	}
	else 
		jQuery( "#ipv-custom-text" ).html( custom_selected_html );

	jQuery( "#ipv-site-type-"+short_name ).css(
		'background-color', '#E5F7FD');

	function ipv_update_message( type, action ) {
		/* args: 
		 *		string action 		one of "Save" or "Cancel", 
		 *		string type 		one of "general", "proxy", "botnet"
		*/

		textField  = document.getElementById( "ipv_" + type + "_msg" );
		buttonId   = "#ipv_save_" + type;

		var postArray = {ipv_auth_token: ipv_auth_token, type: type};

		// only for save actually send the message default behavior is "cancel"
		if ( action == "SAVE" ) postArray["msg"] = textField.value;

		jQuery.post( ipv_ajax_home + "/ipv_manage_msgs.php",
			postArray,
			function( data ) {
				textField.value = data; 
				if ( action == "SAVE" ) ipv_save_button_disable( buttonId );
			}
		);

	}

</script>

<div class="general ipvmain ipv_enable_with_key">

	<div class="frame-header">
		<img src= "<?php
				echo plugins_url( 'images', dirname( __FILE__ ) )
			?>/hdr-text/message_settings.png">
	</div>

	<div id="ipv-message-edit-container">
	<table><tr>
		<td>
			<h2>General Message
			<img style="padding-left:10px;vertical-align:text-top" src=
			"<?php
				echo plugins_url( 'images', dirname( __FILE__ ) )
			?>/tooltip_question.png" alt="?"
			class="ipv-title-cluetip"
			title="General Message||When IPVenger blocks a user from accessing your site, they are shown an error page.|You can customize what the blocked user sees by modifying this HTML.  This is the message that most blocked users will see."
			>
			</h2>
			The message that you want to display to blocked IP traffic on 
			the IPVenger landing page:<p>
			<textarea onclick="ipv_save_button_enable( '#ipv_save_general' )"
				id="ipv_general_msg" rows="7" cols="40" style="width:100%">
<?php echo ipv_get_block_msg( "general" ); ?></textarea>
			<div style="float:right;">
				<input type="button" class="ipv-secondary"
					onclick='ipv_update_message( "general", this.value )'
					id="ipv_edit_general" value="Cancel" >
				<input type="button" class="ipv-secondary"
					onclick="ipv_show_preview( 'general' )"
					id="ipv_preview_general" value="Preview" >
				<input type="button" class="ipv-primary"
					onclick='ipv_update_message( "general", this.value )'
					id="ipv_save_general" value="SAVE">
			</div>
		</td>
		<td>
			<h2>Proxy Message
			<img style="padding-left:10px;vertical-align:text-top" src=
			"<?php
				echo plugins_url( 'images', dirname( __FILE__ ) )
			?>/tooltip_question.png" alt="?"
			class="ipv-title-cluetip"
			title="Proxy Message||A proxy server acts as an intermediary between a user and the Internet. Proxies are often used by schools and companies to block access to undesirable sites.  However, they can also be used by attackers to hide their identity.| This message is shown to blocked users who are behind a proxy.  You can customize what the blocked user sees by modifying this HTML. "
			>
			</h2>
			The message that you want to display to a blocked IP that is using 
			a proxy:<p>
			<textarea onclick="ipv_save_button_enable( '#ipv_save_proxy' )"
				id="ipv_proxy_msg" rows="7" cols="40" style="width:100%">
<?php echo ipv_get_block_msg( "proxy" ); ?></textarea>
			<div style="float:right;">
				<input type="button" class="ipv-secondary"
					onclick='ipv_update_message( "proxy", this.value )'
					id="ipv_edit_proxy" value="Cancel">
				<input type="button" class="ipv-secondary"
					onclick="ipv_show_preview( 'proxy' )"
					id="ipv_preview_proxy" value="Preview" >
				<input type="button" class="ipv-primary"
					onclick='ipv_update_message( "proxy", this.value )'
					id="ipv_save_proxy" value="SAVE">
			</div>
		</td>
		<td>
			<h2>Botnet Message
			<img style="padding-left:10px;vertical-align:text-top" src=
			"<?php
				echo plugins_url( 'images', dirname( __FILE__ ) )
			?>/tooltip_question.png" alt="?"
			class="ipv-title-cluetip"
			title="Botnet Message||A Botnet is a collection of computers infected with malicious software that may be under the control of an attacker.| In most cases, the legitimate owner of the computer is unaware that their computer is part of a Botnet.  | Requests from computers that are part of a Botnet are extremely dangerous and will generally be blocked by IPVenger.| This message is shown to blocked users who are part of a Botnet.  You can customize what the blocked user sees by modifying this HTML. " 
			> 
			</h2>
			The message that you want to display to a blocked IP that
			is a member of a Botnet:<p>
			<textarea onclick="ipv_save_button_enable( '#ipv_save_botnet' )"
				id="ipv_botnet_msg" rows="7" cols="40" style="width:100%">
<?php echo ipv_get_block_msg( "botnet" ); ?></textarea>
			<div style="float:right;">
				<input type="button" class="ipv-secondary"
					onclick='ipv_update_message( "botnet", this.value )'
					id="ipv_edit_botnet" value="Cancel">
				<input type="button" class="ipv-secondary"
					onclick="ipv_show_preview( 'botnet' )"
					id="ipv_preview_botnet" value="Preview" >
				<input type="button" class="ipv-primary"
					onclick='ipv_update_message( "botnet", this.value )'
					id="ipv_save_botnet" value="SAVE">
			</div>
		</td>
	</tr></table>
	</div>
</div>

<div class="general ipvmain ipv_enable_with_key">
	<div class="frame-header">
		<img src= "<?php
				echo plugins_url( 'images', dirname( __FILE__ ) )
			?>/hdr-text/appeal_settings.png">
	</div>
	<div>
		<div>

			<div id="ipv-email-appeal-settings">
				<h2> Blocked user appeal notification settings </h2>
				<p>When a visitor to your site is blocked, they can appeal
				the block and gain access to your site for 48 hours by 
				providing their email address and entering a CAPTCHA.</p>

				<p>An email will be sent to the address 
				you choose, giving you the opportunity to whitelist
				or blacklist the IP in question.</p>

				<p>You can disable the ability for users to appeal the 
				block by checking the box below.</p>

				<input id="ipv-appeal-checkbox" type="checkbox"
					<?php 
						if ( ! ipv_appeals_are_enabled() ) echo ' checked '; 
					?>
					onclick="ipv_update_appeals()">
				<span id="ipv-appeal-checkbox-text"> 
				Do not allow blocked users to appeal.</span>

				<img style="padding-left:10px;vertical-align:text-top" src=
				"<?php
					echo plugins_url( 'images', dirname( __FILE__ ) )
				?>/tooltip_question.png" alt="?"
				class="ipv-title-cluetip"
				title="Disabling Appeals||By default, a blocked user can gain temporary access to your site by providing a valid e-mail address and responding to a CAPTCHA.|This allows legitimate human users to access your site even though they may have a high IPQ score, while still stopping most automated attackers (Bots)|Checking this box will turn off the ability for a user to appeal.  This will increase security but may also block some legitimate users from accessing the site.">

			</div>
		</div>
		<div id="ipv-email-right-column">

				<div id="ipv-email-option-wrapper">
					<br><strong>Notify me via:</strong><br>
					<div id="ipv-email-appeal-options">
						<input type="radio" id="ipv-email-site-admin" 
							name="email_notify_group"
							onclick="ipv_prep_custom_email( false )"
							<?php if ( ! ipv_email_is_custom() )
								echo ' checked '; ?>
						/>
						<?php echo $GLOBALS['ipv_admin_e_mail']; ?> 
						(Wordpress Site Administrator) 
						<p>
						<input type="radio" id="ipv-email-custom" 
							name="email_notify_group"
							<?php if ( ipv_email_is_custom() )
								echo ' checked '; ?>
							onclick="ipv_prep_custom_email( true )"
						/>

						Custom email 
						<div style="padding-left:10px">
							<input type="text" size="28" 
								onclick="ipv_email_button_enable()"

								id="ipv-custom-email-input"
						
								<?php 
									if ( ipv_email_is_custom() ) {
										echo 'value = "' . 
											ipv_get_admin_email()
											. '" ';
									}
									else {
										echo 'value = "Email Address" ';
										echo 'disabled = "disabled"';
									}
								?>
							/>
							<div id="ipv-email-button-container">
								<input type="button" class="ipv-primary" 
									value="Save Email Settings"
									id="ipv-email-button"
									onclick="ipv_update_email()" />
							</div>
						</div>
					</div>
				</div>
<!-- not yet implemented

			<div id="ipv-email-report-settings">
				<h2>Email Report</h2>
				<div>
				<input type="checkbox" id="ipv-email-report" 
        			onclick="ipv_email_button_enable()"

					value="email_summary" 
				<?php if ( ipv_receives_reports() ) echo ' checked '; ?>
				/>
				Send a weekly summary report to my email, along with other 	
				news and helpful articles from the IPVenger team
				</div>
			</div>

-->
		</div>
	</div>
</div>

</div>
