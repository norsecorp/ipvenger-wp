<?php
    if ( ! isset( $_SESSION["ipv_is_admin"] ) || ! $_SESSION["ipv_is_admin"] )
        die( "Unauthorized" );
?>

<div><h2 class=dummy></h2></div> <!-- hack for Wordpress nag positioning -->

<div id="ipv">

<?php
   require_once( $GLOBALS['ipv_core_includes'] . 
		'/ipv_config_utils.php' );
?>

<script type="text/javascript">
	
	var ipv_ajax_home =
		<?php echo json_encode( $GLOBALS['ipv_ajax_home'] );?>;

	var ipv_auth_token = "<?php echo $_SESSION['ipv_auth_token']; ?>";

	function ipv_ip_is_valid( ip ) {
		var pattern = /^(([0-9\*]|[1-9][0-9\*]|1[0-9][0-9\*]|2[0-4][0-9\*]|25[0-5\*])\.){3}([0-9\*]|[1-9][0-9\*]|1[0-9][0-9\*]|2[0-4][0-9\*]|25[0-5\*])$/g;
		return pattern.test(ip);
	}

	function ipv_set_select_button( type ) {

		if ( jQuery( "#ipv-"+type+"-entries option:selected").length ) {
			jQuery( "#ipv-"+type+"-remove-button" ).removeAttr( "disabled" );
		}
		else {
			jQuery( "#ipv-"+type+"-remove-button" )
				.attr( "disabled", "disabled" );
		}
	}

	function ipv_validate_and_set_ip_button( ip, type ) {

		if ( ipv_ip_is_valid( ip ) )
			jQuery( "#ipv-ip-"+type+"-button" ).removeAttr( "disabled" );
		else
			jQuery( "#ipv-ip-"+type+"-button" ).attr( "disabled", "disabled" );
	}

    function ipv_add_excp( type, field, mask, output )
    {

		if ( field == 'ip' ) field_desc = "IPs";
		else field_desc = "countries"
	
		if ( mask == "" ) {
			alert( "Select one or more " + field_desc + 
					" to add to the " + type + "." )
			return;
		}

		if ( ! confirm( "Are you sure you want to " + type + 
				" all traffic from " + mask + "?" ) ) return;


        jQuery.post( ipv_ajax_home + "/ipv_manage_exceptions.php",
            { 	ipv_auth_token: ipv_auth_token, 
			 	action: "add", 
				rule_type:type, 
				field:field, 
				mask:mask 
			},
            function( data ) {

				var sel = jQuery("#" + output);
				sel.empty();

				for (var i=0; i<data.length; i++) {
				  sel.append(
					'<option value="' + data[i].key + '">' + 
					data[i].mask + '</option>');
				}

				if ( output == "ipv-country-blacklist-entries" ){

					var cc = ipv_codes_by_country(mask);
					if ( typeof cc !== 'undefined' ) 
						mapObject.regions[cc].element
							.setStyle( 'fill', ipv_blacklist_country_color );

				}

            }, 
			"json"
		);

    }

    function ipv_delete_excp( type, field, output )
    {
        var ipv_ajax_home =
            <?php echo json_encode( $GLOBALS['ipv_ajax_home'] );?>;

		// get all selects
		var keys = document.getElementById(output);
		
		list = []; 	// array of database keys associated with selected
		sel_keys = []; // array of selected keys
	
		// for each select, pull out the value and push it into 'arr'
		for(var i = 0; i < keys.length; i++) {	
			if ( keys[i].selected ) {
				list.push(keys[i].value);
				sel_keys.push( keys[i].text );
			}
		}

		if ( list.length == 0 ) { 
			if ( field == 'ip' ) field_desc = "IPs";
			else field_desc = "countries"
			alert( "Select one or more " + field_desc + 
					" to remove from the " + type + "." )
			return;
		}

        jQuery.post( ipv_ajax_home + "/ipv_manage_exceptions.php",
            {ipv_auth_token: ipv_auth_token, action: "delete", rule_type:type, 
				field:field, list:list },
            function( data ) {
				var sel = jQuery("#" + output);
				sel.empty();
				for (var i=0; i<data.length; i++) {
				  sel.append(
					'<option value="' + data[i].key + '">' + 
					data[i].mask + '</option>');
				}

				if ( output == "ipv-country-blacklist-entries" ){

					for ( i = 0; i < sel_keys.length; i++ ) {
				
						var cc = ipv_codes_by_country(sel_keys[i]);
						if ( typeof cc !== 'undefined' ) 
							mapObject.regions[cc].element
								.setStyle('fill', ipv_safe_country_color);

					}
				}
					
				sel.change();

            },
			"json"
		);

    }

	// the IPQ currently in the database - kept current here to avoid 
	// unnecessary ajax calls

	var stored_ipq_threshold = 
            <?php echo ipv_get_default_risk(); ?>;

    jQuery(function() {
        jQuery( "#ipv-ipq-slider" ).slider({
            range: "min",
            min: 0,
            max: 100,
            value: stored_ipq_threshold,
            step: 1,
			slide: function (event, ui) {
				m_val = ui.value;
				if (m_val < 0) {
					m_val = 0;
					jQuery(this).slider({ value: 0 });
				}
				jQuery(this).find("a:first").html(m_val);
				jQuery( "#ipv_slider_save" ).removeAttr('disabled');
				jQuery( "#ipv_slider_save" ).val("SAVE");
			}

        });
		jQuery("#ipv-ipq-slider" ).find("a:first").html( stored_ipq_threshold );

    });

	// store the ipq slider value in the database
    function ipv_apply_ipq() {

		var ipq = jQuery("#ipv-ipq-slider").find("a:first").html();

        jQuery.post( 
			ipv_ajax_home + "/ipv_update_ipq.php", 
			{ ipv_auth_token: ipv_auth_token, ipq: ipq }
        );

		// change the general settings "site type" to "custom"
        jQuery.post( ipv_ajax_home + "/ipv_update_site_type.php",
            {ipv_auth_token: ipv_auth_token, 
			site_type: "custom", apply_update: true, ipq: ipq},
            function( data ) {
				jQuery( "#ipv_slider_save" ).val("Saved");
				jQuery( "#ipv_slider_save" ).attr('disabled', 'disabled');
            }
        );

		stored_ipq_threshold = ipq;

	}

    function ipv_reset_ipq() {

		jQuery("#ipv-ipq-slider" ).find("a:first").html( stored_ipq_threshold );

		jQuery( "#ipv-ipq-slider").slider( 
			"option", "value", stored_ipq_threshold );

		jQuery( "#ipv_slider_save" ).attr('disabled', 'disabled');
	}

	function ipv_ipq_bump( inc ) {
		var ipq = jQuery("#ipv-ipq-slider" ).find("a:first").html();
		ipq = ( parseFloat( ipq ) + inc );
		if ( ( ipq < 0 ) || ( ipq > 100 ) ) return;
		jQuery( "#ipv-ipq-threshold").val( ipq );
		jQuery( "#ipv-ipq-slider" ).find("a:first").html( ipq );
		jQuery( "#ipv-ipq-slider").slider( "option", "value", ipq );
		jQuery( "#ipv_slider_save" ).removeAttr('disabled');
		jQuery( "#ipv_slider_save" ).val("SAVE");
	}

	function ipv_download() {

		var days = jQuery( "#ipv_date_range" ).val();

		window.location.href = 
			"<?php
				echo plugins_url( 'dashboard-pages', dirname( __FILE__ ) ) . 
					'/ipv_download_csv.php?days=';
			?>" + days;

	}

	var ipv_safe_country_color = "#A0A0A0";
	var ipv_blacklist_country_color = "#F00000";
	var ipv_hover_country_color = "#404040";
	var ipv_color_hold;

	var country_hover_ready = false;
	var country_ipqs;
	var country_pct_block;
	var country_labels;

	var mapObject;

	jQuery(function(){

		colors = Object();

		<?php

			$query = 'SELECT count(*) AS count FROM '  . IPV_REQUEST_DETAIL . 
				' WHERE ipv_int_disp = 0 ' .
				'AND ipv_int_date > date_sub(curdate(), INTERVAL 30 DAY)';


			ipv_db_connect();

			$q_result = ipv_db_query( $query );
			$row = ipv_db_fetch_assoc( $q_result );

			$total_blocked = $row['count'];

			$query = 'SELECT count(*) AS count FROM '  . IPV_REQUEST_DETAIL . 
				' WHERE ipv_int_date > date_sub(curdate(), INTERVAL 30 DAY)';

			$q_result = ipv_db_query( $query );
			$row = ipv_db_fetch_assoc( $q_result );

			$total_requests = $row['count'];

		?>

		// get country data for map hover (last 30 days)
        total_blocked = <?php echo json_encode( $total_blocked );?>;
        total_requests = <?php echo json_encode( $total_requests );?>;

		jQuery.post( ipv_ajax_home +
			"/ipv_get_country_data.php",
			{
				ipv_auth_token: ipv_auth_token,
				n_days: 30,
				sort: "block",
				total_blocked: total_blocked,
				total_requests: total_requests
			},
			function( data ) {
				country_ipqs = data["country_ipqs"];
				country_pct_block = data["country_pct_block"];
				country_labels = data["country_labels"];
				country_hover_ready = true;	
			},
			"json"
		);

        jQuery('#ipv-blacklist-map').vectorMap( {
			map: "world_en", 
			zoomOnScroll: false,
			zoomMax: 100,
            backgroundColor: "#FFFFFF",
			regionStyle: { 
				initial: { fill: ipv_safe_country_color, "stroke-width": 0.5 },
				hover:   { fill: ipv_hover_country_color } 
			},
			onRegionLabelShow:
				function( event, label, code ) {

					if( ! country_hover_ready ) return;

					name = label.text();
					var i = 0;
					var found = false;
					for (var i = 0; i < country_labels.length; i++ ) {
						if ( country_labels[i][1] == name ) {
							found = true;
							label.html( "<b>" + name +
								"</b><br>&nbsp;&nbsp;% of Blocks: " +
								country_pct_block[i][1].toFixed(1) +
								"<br>&nbsp;&nbsp;Avg IPQ: " +
								country_ipqs[i][1].toFixed(1)
							);
							break;
						}
					}
					if ( ! found ) {
						label.html( "<b>" + name + "<b><br>(no traffic)" );
					}
				}
        });

		mapObject = jQuery('#ipv-blacklist-map').vectorMap('get', 'mapObject');

		mapObject.reset();
		
		// set initial colors for blacklisted countries
		<?php

			$rules = ipv_get_rules_array( 'blacklist', 
				'country' );
			$len = count( $rules );
			for ($i = 0; $i < $len; $i++ ) {

				echo 'var cc = ipv_codes_by_country( "';
				echo $rules[$i]['mask'] . '");';
				echo 'if ( typeof cc !== "undefined" )';
				echo 'mapObject.regions[cc].element.setStyle("fill"';
				echo ',ipv_blacklist_country_color);';

			}
		?>

    });

	var last_cc = "none";

	function ipv_clear_country_selection() {
		jQuery( '#ipv-country-blacklist-entries' ).val(-1);

		mapObject.regionsColors = ipv_color_hold;

		if ( last_cc != "none" ) {
			mapObject.regions[last_cc].element.setStyle( 'stroke', '#FFF' );
			mapObject.reset();
			last_cc = "none";
		}

        jQuery("#ipv-country-blacklist-button").attr('disabled', 'disabled');
		jQuery('.ui-autocomplete-input').focus().val('');


	}

	function ipv_zoom_and_enable( country ) {
		ipv_zoom_to_country( country );
        jQuery("#ipv-country-blacklist-button").removeAttr('disabled');
	}

	function ipv_zoom_to_country( country ) {

		cc = ipv_codes_by_country( country );

		if ( last_cc != "none" ) 
			mapObject.regions[last_cc].element.setStyle( 'stroke', '#FFF' );

		if ( typeof cc === 'undefined' ) {
			mapObject.reset();
			last_cc = "none";
		}
		else {
			last_cc = cc;
			mapObject.regions[cc].element.setStyle( 'stroke', '#000' );
			mapObject.setFocus( cc, .80 );
		}

	}

	jQuery(document).ajaxComplete( ipv_handle_timeout);

	jQuery(document).ready( function() {

		jQuery("#ipv-country-blacklist-entries").change(function () {

			var selectCount = jQuery( 
				"#ipv-country-blacklist-entries option:selected").size();

			/* if nothing selected, bail */
			if ( selectCount == 0 ) return;

			/* get selected countries */
			selectionSet = jQuery( 
				"#ipv-country-blacklist-entries option:selected")

			/* if a single country is selected, pan and zoom */
			if ( selectionSet.length == 1 ) {
				ipv_zoom_to_country( selectionSet.text() );
			}
			/* otherwise if we are currently zoomed in, zoom out */
			else if ( last_cc != "none" ) {
				last_cc = "none";
				mapObject.reset();
			}
	
		});

		jQuery( "#ipv-country-blacklist-mask" ).combobox();

		var countries = ipv_country_names();

		countries.sort();
	
		for ( i = 0; i < countries.length; i++ ) {

			name = countries[i];

			// add this country to the search select
			jQuery('#ipv-country-blacklist-mask').append(
				'<option value="' + name + '">' + name + '</option>');

		}

		// set up tooltips
        jQuery(".ipv-title-cluetip").cluetip( {
            splitTitle: '|',
            cluetipClass: 'default',
            activation: 'click',
            showTitle: true,
            sticky: true,
            closePosition: 'title'
        });

        // now remove the titles so they don't show on hover
        jQuery(".ipv-title-cluetip").removeAttr('title');

		// attach realtime IP address validation handlers
		jQuery("#ipv-ip-whitelist-mask").bind(
			"propertychange keyup input paste", 
			function(event) { ipv_validate_and_set_ip_button( 
				jQuery( this ).val(), "whitelist" ); }
		);

		jQuery("#ipv-ip-blacklist-mask").bind(
			"propertychange keyup input paste", 
			function(event) { ipv_validate_and_set_ip_button( 
				jQuery( this ).val(), "blacklist" ); }
		);

		ipv_validate_and_set_ip_button( 	
			jQuery( "#ipv-ip-whitelist-mask" ).val(), "whitelist" );
		ipv_validate_and_set_ip_button( 
			jQuery( "#ipv-ip-blacklist-mask" ).val(), "blacklist" );

	});

</script>

<?php require_once( 'ipv_banner.php' ); ?>

<div class="advanced ipvmain">
<div class="frame-header">
	<img src= "<?php
			echo plugins_url( 'images', dirname( __FILE__ ) )
		?>/hdr-text/ipq_threshold.png">
</div>

<div>

<?php $rec_set_warning = 'Recommended Settings||The IPQ Threshold for this web site is based on the main function of the site set by the administrator under General Settings.  | The default settings for each site type are carefully calculated to maximize security while minimizing impact on legitimate users.  | Raising the blocking threshold for your site may allow dangerous IPs to access your site. Lowering the blocking threshold may restrict users that do not pose a significant risk.' ?>

		<div id="ipq-threshold-slide-container">
			<h2>
				Recommended Settings
				<img class="ipv-title-cluetip" src=
				"<?php
					echo plugins_url( 'images', dirname( __FILE__ ) ) 
				?>/tooltip_question.png" alt="?"
				class="ipv-title-cluetip"
				title="<?php echo $rec_set_warning; ?>"
				>
			</h2>
			Your recommended IPVenger IPQ threshold is shown below.  You may 
			change this setting, however, make sure that you <a
				class="ipv-title-cluetip" style="text-align:bottom"
				title="<?php echo $rec_set_warning; ?>">
			read this important notice first</a>

			<div id="ipq-threshold-slider">

				<button class="ipv-slider-button"
					id="ipv-bump-minus"
					onclick="ipv_ipq_bump( - 1 )">
				</button>

				<div id="ipv-ipq-slider"></div>

				<button class="ipv-slider-button"
					id="ipv-bump-plus"
					onclick="ipv_ipq_bump( 1 )">
				</button>

			</div>

			<div id="ipq-threshold-control-buttons">
				<input type="button" class="ipv-secondary" 
					id="ipv_slider_edit" onclick="ipv_reset_ipq()" 
					value="Reset">
				<input type="button" class="ipv-primary" 
					disabled="disabled"
					id="ipv_slider_save" onclick="ipv_apply_ipq()" value="SAVE">
			</div>

		</div>

		<div id="ipq-threshold-divider"
			style="background: url( '<?php
				echo plugins_url( 'images', dirname( __FILE__ ) )
			?>/ipq-threshold-divider.png' );">
			&nbsp;
		</div>

		<div id="ipq-threshold-text">
			<h2>IPQ Threat Threshold</h2>
			You have chosen 
			<?php

				$rc = ipv_get_site_type(
					$short_name, $long_name, $ipq_thresh, $ipq_desc, $desc_text  );

				print "<b>$long_name</b>.  Your security has been set to ";
				print "<b>$ipq_desc</b>.<p>";
			?>

			You may change the IPQ threshold using the slider,
			but it is important that you <a 
				class="ipv-title-cluetip" style="text-align:bottom"
				title="<?php echo $rec_set_warning; ?>"
			>read this first</a>.<p>
			
			To change the website type go to <a href="
			<?php 
				$page = plugin_basename($parent_slug);
				echo admin_url("admin.php?page=$page");
			?>">
			Security Settings</a> and select the site type from the pulldown menu.
		</div>

	</div>

</div>

<div class="advanced ipvmain" style="margin-top: 0">

<div class="frame-header">
	<img src= "<?php
			echo plugins_url( 'images', dirname( __FILE__ ) )
		?>/hdr-text/ip_address_wb.png">
</div>

<div>

<?php $ip_bl_warning = 'IP Whitelist/Blacklist||Blacklisting an IP will block all access attempts from that address, regardless of IPQ score.  An asterisk may be used as a wildcard character, for example 10.1.1.* would block the IPs 10.1.1.1, 10.1.1.2, etc.| IP Blacklists should be used with care.  Because some IP\'s are dynamic (the user behind the IP may change), blacklisting a single IP may not protect from future attacks.  In fact, an IP used by an attacker one day may belong to a legitimate user on another.'
?>
	<div class="warning-banner">
		<b>Warning:</b>  
		Make sure you read <a 
			class="ipv-title-cluetip"
			style="text-decoration:underline;vertical-align:baseline"
			title="<?php echo $ip_bl_warning;?>">this important notice</a> 
		before adding blacklist or whitelist entries.
		<img class="ipv-title-cluetip" src=
		"<?php
			echo plugins_url( 'images', dirname( __FILE__ ) ) 
		?>/tooltip_question.png" alt="?"
		class="ipv-title-cluetip"
		title="<?php echo $ip_bl_warning; ?>"
		>
	</div>

	<div class="blacklist-control-container">
		<div class="mask-input-container">
			<h3>Whitelist:</h3>
			<input type="text" class="mask-input" id="ipv-ip-whitelist-mask" />
			<button class="ipv-primary" 
					id="ipv-ip-whitelist-button"
					onclick="ipv_add_excp( 
					'whitelist', 
					'ip',
					document.getElementById( 'ipv-ip-whitelist-mask' ).value,
					'ipv-ip-whitelist-entries'
				)"
				disabled="disabled"
				>Whitelist
			</button>
		</div>
		<select id="ipv-ip-whitelist-entries" 
				class=fixedwidth multiple="multiple"
				onchange="ipv_set_select_button( 'ip-whitelist' )"
		>

			<?php
				$rules = ipv_get_rules_array( 'whitelist', 'ip' );
				$len = count( $rules );
				for ($i = 0; $i < $len; $i++ ) {
					echo '<option value="' . $rules[$i]['key'] . '">' . 
					$rules[$i]['mask'] . '</option>';
					
				}
			?>

		</select><br>

		<button id="ipv-ip-whitelist-remove-button" 
				class="ipv-primary"
				disabled="disabled"
				onclick="ipv_delete_excp(
					'whitelist', 
					'ip',
					'ipv-ip-whitelist-entries'
				)"
			>Remove Selected
		</button>
	</div>

	<div class="blacklist-control-container">
		<div class="mask-input-container">
			<h3>Blacklist:</h3>
			<input type="text" class="mask-input" id="ipv-ip-blacklist-mask" 
			/>
			<button class="ipv-primary"
					id="ipv-ip-blacklist-button"	
					onclick="ipv_add_excp( 
					'blacklist', 
					'ip',
					document.getElementById( 'ipv-ip-blacklist-mask' ).value,
					'ipv-ip-blacklist-entries'
				)" 
				disabled="disabled"
				>Blacklist
			</button>
		</div>

		<select id="ipv-ip-blacklist-entries"
				onchange="ipv_set_select_button( 'ip-blacklist' )"
				class=fixedwidth multiple="multiple">

			<?php
				$rules = ipv_get_rules_array( 'blacklist', 'ip' );
				$len = count( $rules );
				for ($i = 0; $i < $len; $i++ ) {
					echo '<option value="' . $rules[$i]['key'] . '">' . 
					$rules[$i]['mask'] . '</option>';
					
				}
			?>

		</select><br>

		<button id="ipv-ip-blacklist-remove-button" 
				class="ipv-primary"
				disabled="disabled"
				onclick="ipv_delete_excp(
					'blacklist', 
					'ip',
					'ipv-ip-blacklist-entries'
				)"
			>Remove Selected
		</button>
	</div>

</div>
</div>
<div class="advanced ipvmain">
<div class="frame-header">
	<img src= "<?php
			echo plugins_url( 'images', dirname( __FILE__ ) )
		?>/hdr-text/country_blacklist.png">
</div>

		<?php $ctry_bl_warning ='Country Blacklist||Blacklisting an entire country will block all access attempts from that country including any legitimate users.  Country blacklists should be used with extreme caution. | Related attacks are often seen to come from geographic areas that span political borders.  Because of this, blacklisting one country may have the effect of raising the IPQ score of requests from neighboring countries.'?>
	<div class="warning-banner">
		<b>Warning:</b>  
		Blacklisting a country may affect the behavior of IPVenger in ways you 
		do not anticipate.  Read <a 
			class="ipv-title-cluetip"
			style="text-decoration:underline;vertical-align:baseline"
			title="<?php echo $ctry_bl_warning;?>">this important notice</a> 
		first.

		<img class="ipv-title-cluetip" src=
		"<?php
			echo plugins_url( 'images', dirname( __FILE__ ) ) 
		?>/tooltip_question.png" alt="?"
		class="ipv-title-cluetip"
		title="<?php echo $ctry_bl_warning;?>"
		>
	</div>

	<div class="blacklist-control-container">
		<div class="mask-input-container">
			<select class="mask-input" 
				id="ipv-country-blacklist-mask" 	
				onchange="ipv_zoom_and_enable( this.value )">
			</select>
			<button class="ipv-primary"
				id="ipv-country-blacklist-button"
				class="country-blacklist"
				onclick="ipv_add_excp( 
					'blacklist', 
					'country',
					jQuery( '.ui-autocomplete-input' ).focus().val(),
					'ipv-country-blacklist-entries'
				)"
				disabled="diabled"
				>Blacklist
			</button>
		</div>
		<select id="ipv-country-blacklist-entries" 
			onchange="ipv_set_select_button( 'country-blacklist' )"
			class=fixedwidth multiple="multiple"
		>

			<?php
				$rules = ipv_get_rules_array( 'blacklist', 
					'country' );
				$len = count( $rules );
				for ($i = 0; $i < $len; $i++ ) {
					echo '<option value="' . $rules[$i]['key'] . '">' . 
					$rules[$i]['mask'] . '</option>';
					
				}
			?>

		</select><br>

		<button class="ipv-secondary"
					onclick="ipv_clear_country_selection()">
			Clear Selection
		</button>

		<button class="ipv-primary"
				id="ipv-country-blacklist-remove-button"
				disabled="disabled"
				onclick="ipv_delete_excp(
					'blacklist', 
					'country',
					'ipv-country-blacklist-entries'
			)" >
			Remove Selected
		</button>
	</div>

	<div id="ipv-blacklist-map"></div>

</div>

<div class="advanced ipvmain">

    <div class="frame-header">
		<img src= "<?php
				echo plugins_url( 'images', dirname( __FILE__ ) )
			?>/hdr-text/export_data.png">
	</div>

	<div id="ipv-reporting-text">

		<h2>
			Export Reporting Data
			<img style="padding-left:10px;vertical-align:text-top" src=
			"<?php
				echo plugins_url( 'images', dirname( __FILE__ ) ) 
			?>/tooltip_question.png" alt="?"
			class="ipv-title-cluetip"
			title="Export Data||Detailed traffic records for the last 30 days can be downloaded by administrators for archival or analysis at any time."
			>
		</h2>

		<div id="download-select-container">
			Export data for the
			<select style="width:150px" id="ipv_date_range">
				<option value="30">last 30 days</option>
				<option value="14">last 14 days</option>
				<option value="7">last 7 days</option>
			</select>
			in CSV file format.
		</div>

		<div id="download-button-container">
			<button class="ipv-primary" 
				onclick="ipv_download()">
				Download
			</button>
		</div>

	</div>
</div>

</div>
