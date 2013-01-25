<?php
    if ( ! isset( $_SESSION['ipv_is_admin'] ) || ! $_SESSION['ipv_is_admin'] )
        die( 'Unauthorized' );
?>

<div><h2 class=dummy></h2></div> <!-- hack for Wordpress nag positioning -->

<div id="ipv">

<style type="text/css">
	.hide_if_empty
	{ 
		display:none;	
	}
</style>

<?php

	require_once(
	 "${GLOBALS['ipv_cms_includes']}/ipv_colors.php" );

	require_once(
	 "${GLOBALS['ipv_cms_includes']}/ipv_db_utils.php" );

	require_once( 
	 "${GLOBALS['ipv_core_includes']}/ipv_config_utils.php" );

	require_once( 
	 "${GLOBALS['ipv_core_includes']}/ipv_get_summary_data.php" );
?>

<!-- dummy div for ajax dialog -->
<div id="ipv-ip-dialog" style="display:none;"></div>

<!-- country popup div -->
<div class="ipv" id="ipv-country-dialog" style="display:none">

	<div id="ipv-ipq-map-container"> 

		<div id="ipv-ipq-map"></div>

		<div class="legend">
			&nbsp;
			<label class="legend-label">Average IPQ:</label>
			<div class="legend-color">
			<div class="legend-color-inner" 
				style="background:<?php echo ipv_clr_range_green ?>;">
				&nbsp;
			</div>
			</div>
			<label>0 - 33</label>
			<div class="legend-color">
			<div class="legend-color-inner" 
				style="background:<?php echo ipv_clr_range_yellow ?>;">
				&nbsp;
			</div>
			</div>
			<label>34 - 47</label>
			<div class="legend-color">
			<div class="legend-color-inner" 
				style="background:<?php echo ipv_clr_range_orange ?>;">
				&nbsp;
			</div>
			</div>
			<label>48 - 89</label>
			<div class="legend-color">
			<div class="legend-color-inner" 
				style="background:<?php echo ipv_clr_range_red ?>;">
				&nbsp;
			</div>
			</div>
			<label>90 - 100</label>
		</div>

	</div>
	
	<div id="ipv-country-text"></div>

	<div id="ipv-country-detail-pie"
			style="background: url( '<?php
				echo plugins_url( 'images', dirname( __FILE__ ) )
			?>/ctry_pie_bg.png' ) no-repeat;">
		<div id="ipv-country-disp-container">
			<div class="country-legend-container">
				<div class="ipv-country-pie-text">
					Reasons they were blocked
				</div>
				<div id="country-disp-legend" class="ipv-country-legend"></div>
			</div>
			<div id="country-disp-pie" class="ipv-country-pie"></div>
		</div>

		<div id="ipv-country-cat-container">
			<div class="country-legend-container">
				<div id="country-cat-pie-text" class="ipv-country-pie-text">
					Main threat categories:
				</div>
				<div id="country-cat-legend" class="ipv-country-legend"></div>
			</div>
			<div id="country-cat-pie" class="ipv-country-pie"></div> 
		</div>

	</div>

</div>

<?php 
	ipv_get_summary_data( array(7,14,30), $total_blocked, $total_requests ); 
?>	

<script type="text/javascript">

	// overlay graph backdrop with loading gifs until ajax completes
	var disp_pie_spinner = "/spin-white.gif";
	var cat_pie_spinner = "/spin-white.gif";

	function display_pacifiers() {

		jQuery( "#ipv-country-bar" ).html( 
			'<img style="margin-left:353px;margin-top:130px;" src= "' + 
			'<?php echo plugins_url( 'images', dirname( __FILE__ ) ) ?>' + 
			'/spin-white.gif" alt="Loading...">'
		);

		jQuery( "#ipv-daily-risk" ).html( 
			'<img style="margin-left:311px;margin-top:85px;" src= "' + 
			'<?php echo plugins_url( 'images', dirname( __FILE__ ) ) ?>' + 
			'/spin-white.gif" alt="Loading...">'
		);

		jQuery( "#disp-pie" ).html( 
			'<img style="margin-left:91px;margin-top:91px;" src= "' + 
			'<?php echo plugins_url( 'images', dirname( __FILE__ ) ) ?>' + 
			disp_pie_spinner + '" alt="Loading...">'
		);

		jQuery( "#disp-legend" ).hide();

		jQuery( "#cat-pie" ).html( 
			'<img style="margin-left:54px;margin-top:53px;" src= "' + 
			'<?php echo plugins_url( 'images', dirname( __FILE__ ) ) ?>' + 
			cat_pie_spinner + '" alt="Loading...">'
		);

		jQuery( "#cat-pie-legend" ).html( "" );

	}

	display_pacifiers();
 
	var ipv_ip_bw_action = function(){};  // used by IP Lookup 

	var ipv_ajax_home =
		<?php echo json_encode( $GLOBALS['ipv_ajax_home'] );?>;

		var ipv_auth_token = "<?php echo $_SESSION['ipv_auth_token']; ?>";

	var ip_bl_color = "<?php echo ipv_clr_ip_blacklist ?>";
	var cn_bl_color = "<?php echo ipv_clr_ctry_blacklist ?>";
	var ipq_color 	= "<?php echo ipv_clr_ipq_block ?>";

	// retrieve detailed information on the given ip and show in overlay

	function ipv_add_ip_records(ip)
	{

		ip_recs += 10;

		jQuery.post( ipv_ajax_home + "/ipv_lookup_ip.php",
			{
				ipv_auth_token: ipv_auth_token, 
				ip: ip, 
				max_recs: ip_recs,
				is_followup: true
			},
			function( data ) {
				jQuery( "#ipv-ip-dialog" ).dialog(
					'option', 'title', 'IP Lookup - ' + ip );
				jQuery( "#ipv-ip-dialog" ).html(data);
				jQuery( "#ipv-ip-dialog" ).dialog('open');
			}
		);

	}

	function ipv_lookup_ip(ip)
	{

		ip_recs = 10;
	
        jQuery.post( ipv_ajax_home + "/ipv_lookup_ip.php",
			{ipv_auth_token: ipv_auth_token, ip: ip, max_recs: ip_recs},
			function( data ) {
				jQuery( "#ipv-ip-dialog" ).dialog(
					'option', 'title', 'IP Lookup - ' + ip );
				jQuery( "#ipv-ip-dialog" ).html(data);
				jQuery( "#ipv-ip-dialog" ).dialog('open');
			}
		);
	}

	var selected_ip;
	var mapObject;

	function showTooltip(x, y, contents) {
		jQuery('<div id="tooltip">' + contents + '</div>').css( {
			position: 'absolute',
			display: 'none',
			top: y + 5,
			left: x + 5,
			border: '1px solid #bcb',
			padding: '2px',
			'background-color': '#ede',
			opacity: 0.90,
			'z-index': '10000'
		}).appendTo("body").fadeIn(200);
	}

	var previousPoint = null;

	function pie_hover( event, pos, item ) {

		if (item) {
			if (previousPoint != item.series.angle) {
				previousPoint = item.series.angle;
				jQuery("#tooltip").remove();
				y = parseFloat( item.series.percent ).toFixed(1);
				showTooltip(pos.pageX, pos.pageY-30, y + " %" );
			}
		}
		else {
		
			// workaround for flot pie bug that sometimes fails to 
			// unhighlight a slice by creating a phony mousemove outside
			// the pie
			if ( pos.pageX != 1000 ) {
				var e = jQuery.Event('mousemove');
				e.pageX = 1000; 
				e.pageY = 1000;
				jQuery( '#' + event.target.id + ' canvas:first').trigger(e); 
			}
			jQuery("#tooltip").remove();
			previousPoint = null;
		}

	}

	var pie_settings = { 
		series: { 
			pie: { 
				show: true,
				radius: 0.95
			},
		},
		grid: {
			hoverable: true
		}	
	};

	var ipv_plot;
	var ipv_load_country_table;
	var ipv_load_country_bar;
	var ipv_sort_country_bar;

	var monthNames = [ "January", "February", "March", "April", "May", 
		"June", "July", "August", "September", "October", "November", 
		"December" ];

	var currentTime = new Date();
	var month = monthNames[ currentTime.getMonth() ];
	var day = currentTime.getDate();
	var year = currentTime.getFullYear();

	current_date = month + " " + day + ", " + year;

	var blacklisted_countries;

	table_load_html = "<tr>";

	table_load_html += 
		'<td class="country-table-pacifier">' + 
		'</td>'; 

	table_load_html += "</tr>";

	pie_bg_url = '<?php echo plugins_url( 'images', dirname( __FILE__ ) ) ?>' + 				 '/pie_bg.png';

	pie_nd_url = '<?php echo plugins_url( 'images', dirname( __FILE__ ) ) ?>' + 				 '/pie_bg_nd.png';


	jQuery(document).ajaxComplete( ipv_handle_timeout );

	jQuery( function () {

		total_blocked =  <?php echo json_encode( $total_blocked );?>;
		total_requests = <?php echo json_encode( $total_requests );?>;


		var bar_settings = {
			grid:	{ hoverable: true },
			xaxis: 	{ tickLength: 0 },
			legend: { show: false }
		};

		function stack_hover(event, pos, item) {

			if (item) {
				if (previousPoint != item.datapoint) {
					previousPoint = item.datapoint;
					jQuery("#tooltip").remove();
					y = (item.datapoint[1]-item.datapoint[2]).toFixed(0);
					showTooltip(
						pos.pageX,pos.pageY-30, y + " blocked " );
				}
			}
			else {
				jQuery("#tooltip").remove();
				previousPoint = null;
			}
		}

		function bar_hover(event, pos, item) {

			if (item) {
				if (previousPoint != item.dataIndex) {
					previousPoint = item.dataIndex;
					jQuery("#tooltip").remove();
					y = item.datapoint[1].toFixed(1);
					if ( item.series.label == pct_block_label ) 
						showTooltip(
							pos.pageX, pos.pageY-30,y + "% of blocks" );
					else
						showTooltip(
							pos.pageX,pos.pageY-30, "Avg IPQ: " + y );
				}
			}
			else {
				jQuery("#tooltip").remove();
				previousPoint = null;
			}
		}

		blacklisted_countries = 
			<?php
				$rules_array = ipv_get_rules_array( 
					'blacklist', 'country' );

				$blacklisted_countries = array();

				foreach ( $rules_array as $r ) {
					$blacklisted_countries[] = $r['mask'];
				}

				echo json_encode( $blacklisted_countries );
			?>

		var country_ipqs;
		var country_pct_block;
		var country_pct_total;
		var country_labels;
		var country_block;
	
		ipv_load_country_table = function ipv_load_country_table() 
		{

			// populate the country table 

			var table_html = "";

			var country_names = new Array();

			for (i = 0; i < country_labels.length; i++ ) {

				var cnt = country_block[i];

				// don't show countries with no blocked IPs
				if ( cnt == 0 ) continue;

				name = country_labels[i][1];
				country_names[i] = name;

				if ( ( country_table_filter != "All" ) && 
				     ( name != country_table_filter ) ) continue;

				ipq  = country_ipqs[i][1].toFixed(1);
				pct_block  = country_pct_block[i][1].toFixed(1);
				pct_total  = country_pct_total[i][1].toFixed(1);

				blacklisted = ( jQuery.inArray ( 
					name, blacklisted_countries ) != -1 );

				sel_p = " ";
				sel_b = " ";

				if ( blacklisted ) {
					sel_b = 'selected="selected"';
					sort_string = "blocked";
				}
				else {
					sel_p = 'selected="selected"';
					sort_string = "protected";
				}

				table_html += 
					'<tr>' + 
					'<td class="first">' + 
						'<span class="pseudo-link"' +
						   ' onclick="ipv_zoom(' + "'" + name +  "'" + 
						   ', ' + ipq + ')">' + 
						   name + 
						'</span>' + 
					'</td>' + 
					'<td class="middle">' + pct_block + '</td>' +
					'<td class="middle">' + pct_total + '</td>' +
					'<td class="middle">' + ipq + '</td>' + 
					'<td class="last">' +
						'<div class="country-bl-action-wrapper">' +
						'<select class="country-bl-action" ' + 
						'onchange=' +
						'"ipv_country_bw_list( ' + "'" + name + "', " + 
						'(this.options[selectedIndex].text) )" >' + 
						'<option style="display:none">' + 
						sort_string + '</option>' + 
						'<option ' + sel_p + '>Protected</option>' + 
						'<option ' + sel_b + '>Blacklisted</option>' + 
						'</select>' + 
						'</div>' +
					'</td>' + 
					'</tr>';

			}

			jQuery("#ipv-country-table-body").html( table_html );

			jQuery(	"#ipv-country-table" ).trigger("update", [true]);

			// clear the country search
			jQuery('#ipv_country_search').find('option').remove();

			country_names.sort();

			jQuery( "#ipv_country_search" ).combobox();

			jQuery('#ipv_country_search').append(
				'<option value="All">All</option>');

			for ( i = 0; i < country_names.length; i++ ) {

				name = country_names[i];
			
				// add this country to the search select
				jQuery('#ipv_country_search').append(
					'<option value="' + name + '">' + name + '</option>');
			}

			jQuery("#ipv-country-table").tablesorter(
				{ widgets: ['zebra'] } ).bind('sortEnd', function(){
				jQuery("#myTable").trigger("applyWidgets"); 
			});

			

		}

		var sort = "block";

		ipv_load_country_bar = function ipv_load_country_bar( reload_table ) 
		{

			// populate country bar graph and set up map colors

			if ( reload_table ) 
				jQuery( "#ipv-country-table-body" ).html( table_load_html );

			jQuery.post( ipv_ajax_home + 
				"/ipv_get_country_data.php",
				{
					ipv_auth_token: ipv_auth_token,
					n_days: n_days, 
					sort: sort,
					total_blocked: total_blocked[n_days],
					total_requests: total_requests[n_days]
				},
				function( data ) {

					country_ipqs = data["country_ipqs"];
					country_pct_block = data["country_pct_block"];
					country_pct_total = data["country_pct_total"];
					country_labels = data["country_labels"];
					country_block = data["country_block"];

					// the bar chart (just use the first 20 countries by ipq)
					country_data1  = country_pct_block.slice( 0, 15 );
					country_data2  = country_ipqs.slice( 0, 15 );
					country_ticks = country_labels.slice( 0, 15 );
		

					bar_settings.xaxis.ticks = country_ticks;
					bar_settings.xaxis.labelWidth = 100;
					bar_settings.yaxis = {};
					bar_settings.legend.show = false;

					var country_bar_data = [
						{
							label: pct_block_label,
							color: "<?php echo ipv_clr_country_pct_block?>",
							data: country_data1,
							bars: {
								show:true, 
								barWidth:0.25, 
								fill:.75,
								lineWidth:1,
								order: 1 
							}
						},
						{
							label: "Average IPQ",
							color: "<?php echo ipv_clr_country_avg_ipq?>",
							data: country_data2,
							bars: {
								show:true, 
								barWidth:0.25, 
								fill:.75,
								lineWidth:1,
								order: 2 
							}
						}
						];

					jQuery.plot(jQuery("#ipv-country-bar"), 
						country_bar_data, bar_settings );

					jQuery("#ipv-country-bar").bind("plothover", bar_hover );

					// set country colors by ipq score on a red-green gradient
					// note we have to explicitly set every country for 
					// jvectormap 
			
					var colors = ipv_clear_colors( "#A0A0A0" );
					
					if ( typeof mapObject === 'undefined' ) {
						jQuery('#ipv-ipq-map').vectorMap( {
							map: 'world_en',
							zoomOnScroll:false,
							zoomMax: 100,
							backgroundColor: "#F3F3F3",
							regionStyle: { 
								initial: { 
									"stroke-width": 0.25,
									stroke: "#F3F3F3",
									fill: "#A0A0A0"
								},
							},
							onRegionLabelShow:
								function( event, label, code ) {
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
	
						mapObject =  jQuery('#ipv-ipq-map').vectorMap(
							'get', 'mapObject');

					}

					for (var i = 0; i < country_labels.length; i++ ) {

						var ipq 		 = country_ipqs[i][1];
						var country_name = country_labels[i][1];
						var country_code = ipv_codes_by_country( country_name );

						// we may have some "bad" country names - skip them
						if (typeof country_code === 'undefined') continue;

						var red_gradient, green_gradient;

						if 		( ipq < 34 ) 
							color = "<?php echo ipv_clr_range_green ?>";
						else if ( ipq < 48 ) 
							color = "<?php echo ipv_clr_range_yellow ?>";
						else if ( ipq < 90 ) 
							color = "<?php echo ipv_clr_range_orange ?>";
						else color = "<?php echo ipv_clr_range_red ?>";

						mapObject.regions[country_code].element
							.setStyle( 'fill', color );

					}

					if ( reload_table ) ipv_load_country_table();

				},
				"json"
			);

		}	

		ipv_sort_country_bar = function( new_sort ) {
			if ( new_sort == 'block' ) {
				jQuery("#ipv-country-sort-ipq").css("font-weight", "normal");
				jQuery("#ipv-country-sort-block").css("font-weight", "bold");
			}
			else {
				jQuery("#ipv-country-sort-ipq").css("font-weight", "bold");
				jQuery("#ipv-country-sort-block").css("font-weight", "normal");
			}
			
			sort = new_sort;	
			ipv_load_country_bar( false );	
		}	

		ipv_plot = function() {

			n_days = jQuery("#ipv-date-range-select option:selected").val(); 

			startTime = new Date();
			startTime.setDate( currentTime.getDate() - n_days );
			month = monthNames[ startTime.getMonth() ];
			day = startTime.getDate()
			year = startTime.getFullYear()
			start_date = month + " " + day + ", " + year;

			date_range_display = start_date + " - " + current_date;

			jQuery( "#ipv-date-range-display").html( date_range_display );

			display_pacifiers();

			// only show the graphs if there is data 
			if ( total_blocked[n_days] > 0 ) {
				jQuery('.hide_if_empty').show();
				jQuery('#ipv-no-data-overlay').css( "visibility", "hidden" );
			}
			else {
				jQuery('.hide_if_empty').hide();
				jQuery('#ipv-no-data-overlay').css( "visibility", "visible" );
			}

			jQuery( '#ipv-summary' ).show();
			
			// populate the avg ipq by day bar graph

			jQuery.post( ipv_ajax_home + 
				"/ipv_get_avg_ipq_data.php",
				{ipv_auth_token: ipv_auth_token, n_days: n_days},
				function( data ) {

					// to ensure we get one bar per day, insert a 
					// blank record when no data found

					var labels = new Array();

					var ipq_blocks 		= new Array();
					var cn_bl_blocks	= new Array();
					var ip_bl_blocks   	= new Array();

					var ipq_idx = 0;
					var cn_bl_idx = 0;
					var ip_bl_idx = 0;

					for ( i = 0; i < n_days; i++ ) {
	
						var ipq_src_lbl  = data["ipq_labels"];
						var cn_bl_src_lbl  = data["cn_bl_labels"];
						var ip_bl_src_lbl  = data["ip_bl_labels"];

						var d = new Date();
						d.setDate( currentTime.getDate() - n_days + i + 1 );
						month = d.getMonth() + 1;
						day = d.getDate()
						date = month + "/" + day;
						
						if ( ( n_days == 30 ) && ( i % 2 == 1 ) )
							labels.push( [ i, "" ] );
						else
							labels.push( [ i, date ] );

						if ( ipq_idx < ipq_src_lbl.length && 
							ipq_src_lbl[ipq_idx][1] == date ) 
						{
							ipq_blocks.push(
								[i, data["ipq_blocks"][ipq_idx++][1]]);
						}	
						else
							ipq_blocks.push( [ i, 0 ] );

						if ( cn_bl_idx < cn_bl_src_lbl.length && 
							cn_bl_src_lbl[cn_bl_idx][1] == date ) 
						{
							cn_bl_blocks.push(
								[i, data["cn_bl_blocks"][cn_bl_idx++][1]]);
						}	
						else
							cn_bl_blocks.push( [ i, 0 ] );

						if ( ip_bl_idx < ip_bl_src_lbl.length && 
							ip_bl_src_lbl[ip_bl_idx][1] == date ) 
						{
							ip_bl_blocks.push(
								[i, data["ip_bl_blocks"][ip_bl_idx++][1]]);
						}	
						else
							ip_bl_blocks.push( [ i, 0 ] );

					}

					bar_settings.xaxis.ticks = labels;

					var avg_bar_data = 
						[ 	{ data: cn_bl_blocks, color: cn_bl_color }, 
							{ data: ipq_blocks, color: ipq_color }, 
							{ data: ip_bl_blocks, color: ip_bl_color } ];

					bar_settings.series = {
							 stack:true,
							 bars: {
								show:true, 
								align:'center', 
								barWidth:0.7, 
								lineWidth:0., 
								fill:.75 },
							};

					bar_settings.xaxis.rotateTicks = '0';
					bar_settings.grid.borderWidth = 0;

					jQuery.plot(jQuery("#ipv-daily-risk"), 
						avg_bar_data, bar_settings );

					bar_settings.series = {};

					jQuery("#ipv-daily-risk").bind("plothover", stack_hover );

				},
				"json"
			);

			pct_block_label = "% of blocked requests";

			// populate the disposition and category pie charts 

			jQuery.post( ipv_ajax_home + 
				"/ipv_get_category_data.php",
				{
					ipv_auth_token: ipv_auth_token, 
					n_days: n_days, 	
					country: "all"
				},
				function( data ) {

					// populate the summary data

					var visit_str = total_requests[n_days]
							.toString()
							.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");

					var blocked_str = total_blocked[n_days]
							.toString()
							.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");

					
					var block_pct;

					if ( total_requests[n_days] ==  0 ) block_pct_str = " ";
					else {
						block_pct_str = " (" + 
							Math.round( total_blocked[n_days] * 100 / 
								total_requests[n_days] ) + "%) ";
					}
						  

					Math.round( total_blocked[n_days] * 100 / 
						total_requests[n_days] );
	
					jQuery( "#ipv-summary-total" ).html( 
						"<span class='subdued-text'>" + 
						"IPVenger has blocked " + 
						"</span>" + 
						blocked_str + " IPs" + block_pct_str + 
						"<span class='subdued-text'>" + 
						"out of " + 
						"</span>" + 
						visit_str + " visits " + 
						"<span class='subdued-text'>" + 
						"to your website. " + 
						"</span>" );

					disp_totals = data["disp_totals"];

					pie_settings.legend = { show:false };

					pie_settings.series.pie.stroke = { width: 3 };
					pie_settings.series.pie.label = {show: false};

					jQuery.plot(jQuery("#disp-pie"), 
						data["disp_data"], pie_settings );

					jQuery( "#disp-legend-visits" )
						.html( 
						total_requests[n_days]
						.toString()
						.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,")
						+ " Visitors" ); 

					jQuery( "#disp-legend-visits" )
						.css( "color", "#999999" );

					jQuery( "#disp-legend-ipq-count" )
						.html( disp_totals["IPQ Score"] 
						.toString()
						.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"));

					jQuery( "#disp-legend-ipq" )
						.css( "color", "#FFFFFF" );

					jQuery( "#disp-legend-country-count" )
						.html( disp_totals["Country Blacklist"]
						.toString()
						.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"));

					jQuery( "#disp-legend-country" )
						.css( "color", "<?php echo ipv_clr_ctry_blacklist?>" );

					jQuery( "#disp-legend-ip-count" )
						.html( disp_totals["IP Blacklist"]
						.toString()
						.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"));

					jQuery( "#disp-legend-ip" )
						.css( "color", "<?php echo ipv_clr_ip_blacklist?>" );

					jQuery( "#disp-legend-accept-count" )
						.html( disp_totals["Allowed"]
						.toString()
						.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,") );

					jQuery( "#disp-legend-accept" )
						.css( "color", "<?php echo ipv_clr_allow?>" );

					jQuery( "#disp-legend" ).show();

					jQuery("#disp-pie").bind( "plothover", pie_hover );

					if ( data["cat_data"].length > 0 ) {

						jQuery("#ipv-summary").css(
							"background-image", "url('" + pie_bg_url + "')" );

						disp_pie_spinner = "/spin-dark-grey.gif";
						cat_pie_spinner = "/spin-medium-grey.gif";

						pie_settings.series.pie.stroke = { width: 2 };

						pie_settings.legend = { 
							labelFormatter: function(label, series) {
								return "<div style='color:" + series.color + 	
								"'>" + label + "</div>";
							  },
							container: "#cat-pie-legend", 
							backgroundOpacity: 0.0 
						};

						// since there may be many different categories, with 
						// just a few blocks, combine when less than 1% of total

						jQuery.plot(jQuery("#cat-pie"), 
							data["cat_data"], pie_settings );

						jQuery("#cat-pie").bind( "plothover", pie_hover );

						// trigger the dynamic tooltips
						jQuery(".ipv-dynamic-cluetip").cluetip( {
							splitTitle: '|',
							cluetipClass: 'default',
							activation: 'click',
							showTitle: true,
							sticky: true,
							closePosition: 'title'
						});

						// now remove the titles so they don't show on hover
						jQuery(".ipv-dynamic-cluetip").removeAttr('title');

					}
					else { 
						jQuery("#ipv-summary").css(
							"background-image", "url('" + pie_nd_url + "')" );

						disp_pie_spinner = "/spin-dark-grey.gif";
						cat_pie_spinner = "/spin-light-grey.gif";

						jQuery( "#cat-pie" ).html( 
						 '<p>' + 
			'No visitors exceeded your IPQ threshold during this date range.' +
						 '</p>');
					}

					if (total_blocked[n_days] > 0) ipv_load_country_bar(true);

				},
				"json"
			);

		}

		// prep the dialogs

		n_days = jQuery("#ipv-date-range-select option:selected").val(); 

		jQuery("#ipv-ip-dialog").dialog( {
			dialogClass: 'wp-dialog ipv',
			autoOpen: false,
			resizable: false,
			title:'IP Lookup',
			width: 'auto',
			position: { my: 'top', at: 'top', offset: '0 30' },
			modal: 1
		} );

		jQuery("#ipv-country-dialog").dialog( {
			dialogClass: 'ipv-dialog ipv',
			autoOpen: false,
			resizable: false,
			title:'Country Details',
			width: 'auto',
			position: { my: 'top', at: 'top', offset: '0 30' },
			modal: 1
		} );
	
		ipv_plot();

		jQuery("#ipv-country-table").tablesorter(
				{ widgets: ['zebra'] } );

		jQuery("#ipv-country-table").fixheadertable( 
			{ height: 200 ,
			  colratio: new Array( 235, 120, 120, 120, 145 )
			} );

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

		ipv_check_blocks( true );

	} );

	var country_table_filter = "All";
	var last_cc = "none";

	// create country detail overlay for the given country name 
	// (average ipq for the country must be provided in argument ipq)

	function ipv_zoom( name, ipq ) {
		
		cc = ipv_codes_by_country( name );

		// populate the disposition and category pie charts 

		if ( name == "Unknown Country" ) lookup_name = "-";
		else lookup_name = name;

		jQuery.post( ipv_ajax_home + 
			"/ipv_get_category_data.php",
			{ipv_auth_token: ipv_auth_token, n_days: n_days, 
			country: lookup_name},
			function( data ) {

				jQuery( "#ipv-country-text" ).html(
					data["total_blocked"] + 
					" IPs out of " + 
					data["total_requests"] + 
					" requests were blocked from " + 
					date_range_display + "<br>Traffic from " + name + 
					" has an average IPQ score of " + ipq + "." );
			
				if ( data["disp_data"].length > 0 ) {

					pie_settings.legend = { 
						container: "#country-disp-legend", 
						labelFormatter: function(label, series) {
							return "<div style='color:" + series.color + 	
							"'>" + label + "</div>";
						  },
						backgroundOpacity: 0.0 };

					jQuery.plot(jQuery("#country-disp-pie"), 
						data["disp_data"], pie_settings );

					jQuery("#country-disp-pie").bind( "plothover", pie_hover );

					if ( data["cat_data"].length > 0 ) {

						jQuery('#ipv-country-detail-pie').css(
							'background-image', 'url(' + 
							'<?php echo plugins_url( 
								'images', dirname( __FILE__ ) ) ?>' +
							'/ctry_pie_bg.png )');

						pie_settings.legend = { 
							labelFormatter: function(label, series) {
								return "<div style='color:" + series.color + 	
								"'>" + label + "</div>";
							  },
							container: "#country-cat-legend", 
							backgroundOpacity: 0.0 };

						jQuery.plot(jQuery("#country-cat-pie"), 
							data["cat_data"], pie_settings );

						jQuery("#country-cat-pie").bind( 
							"plothover", pie_hover );

						jQuery( "#country-cat-pie" ).show();
						jQuery( "#country-cat-legend" ).show();
						jQuery( "#country-cat-pie-text" ).show();

					}
					else {

						jQuery('#ipv-country-detail-pie').css(
							'background-image', 'url(' + 
							'<?php echo plugins_url( 
								'images', dirname( __FILE__ ) ) ?>' +
							'/ctry_pie_bg_disp_only.png )');

						jQuery( "#country-cat-pie" ).hide();
						jQuery( "#country-cat-legend" ).hide();
						jQuery( "#country-cat-pie-text" ).hide();
					}

					// trigger the dynamic tooltips
					jQuery(".ipv-dynamic-cluetip").cluetip( {
						splitTitle: '|',
						cluetipClass: 'default',
						activation: 'click',
						showTitle: true,
						sticky: true,
						closePosition: 'title',
						onShow: function(e) {
							jQuery("#cluetip").css(
								'z-index',jQuery.ui.dialog.maxZ+1); 
								return true;
							}
					});

					// now remove the titles so they don't show on hover
					jQuery(".ipv-dynamic-cluetip").removeAttr('title');

					jQuery( "#ipv-country-detail-pie" ).show();
				}
				else jQuery( "#ipv-country-detail-pie" ).hide();

				jQuery( "#ipv-country-dialog" ).dialog( 
					'option', 'title', "Country Details: " + 
					'<span id="ipv-country-name">' + name + '</span>'
				);

				jQuery('#ipv-country-dialog').dialog('open');

				jQuery( "#ipv-country-dialog" ).dialog( 'option', 'position', 
						{ my: 'top', at: 'top', offset: '0 30' } );

				if ( last_cc != "none" ) 
					mapObject.regions[last_cc].element.setStyle(
						'stroke', '#F3F3F3' );

				if (typeof cc === 'undefined') {
					mapObject.reset();
					last_cc = "none";
				}
				else {
					last_cc = cc;
					mapObject.setFocus( cc, .80 );
					mapObject.regions[cc].element.setStyle(
						'stroke', '#000' );
				}

			},
			"json"
		);
	}	

	var last_id = 0;
	var display_data = ["", "", "", "", "", "", "", "", "", "",
						"", "", "", "", "", "", "", "", "", ""];
	var data_rows = 20;

	var is_running = true;
	var next_timeout;
	var new_blocks = 0;
	var timeout, new_html = "";

	var update_graphs = false;

	// if ajax has timed out stop trying to update the ticker
	function ipv_timeout_callback() {
		if ( is_running ) {
			is_running = false;
			clearTimeout( next_timeout );
		}
	}

	// display the latest blocks in the ticker
	function ipv_update_ticker( update_graphs ) {

		jQuery( '#ipv-ticker' ).html( new_html );

		new_blocks = 0;

		jQuery( '#ipv-ticker-slidedown' ).slideUp('slow');

		jQuery( '#ipv-ticker-button' ).prop( 'value', "VIEW " + new_blocks
			+ " NEW BLOCKS" );

		// get updated summary data then refresh plots to reflect new blocks
		jQuery.post( 
			ipv_ajax_home + "/ipv_get_request_data.php",
			{	ipv_auth_token: ipv_auth_token,
				day_array: new Array( 7, 14, 30 )
			},
			function( data ) {
				total_blocked = data.total_blocked;
				total_requests = data.total_requests;
				if ( update_graphs ) ipv_plot();
			},
			"json"	
		)

	}


	// update local record of the latest blocks
	function ipv_check_blocks( update_blocks ) {

		jQuery.post( 
			ipv_ajax_home + "/ipv_get_latest_blocks.php",
			{ipv_auth_token: ipv_auth_token,min_key:last_id, max_count:data_rows},
			function( data ) {

				var ret_array = data["result"];

				if ( ret_array.length > 0 ) {

					new_blocks += data["count"];

					for ( i = data_rows - 1; i >= data.length; i-- ) {
						display_data[i] = display_data[i-data.length];
					}

					var i = 0;

					for ( var j = 0; j < ret_array.length; j++ ) {

						display_data[i++] =
							'<div class="ticker-row">' +
							'<div class="ip pseudo-link"><a onclick="ipv_lookup_ip(' + 
							"'" + ret_array[j].ip + "'" + ')">' +
							ret_array[j].ip + "</a></div>" + 
							'<div class="date">' + ret_array[j].time_ago + 
							'</div>' +
							'<div class="reason">' + ret_array[j].reason + 
							'</div></div>';
					}

					last_id = ret_array[0].id;

					new_html = "";
					for ( var i = 0; i < display_data.length; i++ ) {
						if ( display_data[i] != "" ) {
							new_html += display_data[i];
						}
					}

					// update the button text
					jQuery( '#ipv-ticker-button' ).prop( 'value', "VIEW "
						+ new_blocks
						+ ( new_blocks == 1 ? " NEW BLOCK" : " NEW BLOCKS" ) );

					if ( update_blocks ) ipv_update_ticker( false );
					else jQuery( '#ipv-ticker-slidedown' ).slideDown('slow');

				}
			},
			"json"
		);

		if ( is_running ) timeout = 
			next_timeout = setTimeout( "ipv_check_blocks( false )", 10000 );

	}

	// filter country table results to the single country "name" or reset
	// and show all countries if name is "All"

	function ipv_filter_country( name ) { 
		country_table_filter = name;
		ipv_load_country_table();
		if ( name == "All" ) 
			jQuery( "#ipv_reset_button" ).attr('disabled', 'disabled');
		else
			jQuery( "#ipv_reset_button" ).removeAttr('disabled');
			
	}

	// show all countries in the country table and reset the filter to "All"

	function ipv_reset_filter() {
		jQuery( ".ui-autocomplete-input" ).focus().val('');
		ipv_filter_country( 'All' );
		jQuery( "#ipv_reset_button" ).attr('disabled', 'disabled');
	}

	// either blacklist or "un" blacklist the given country

	function ipv_country_bw_list( name, text ) {
		

		var confirmed = false;

		if ( text == "Blacklisted" ) {
			if (
				confirm( "Are you sure you want to block all traffic from " + 
				name + "?" ) ) 
			{
				jq_opts = { 
					ipv_auth_token: ipv_auth_token,
					rule_type: "blacklist", 
					field: "country",
					action: "add",
					mask: name
				 }

				blacklisted_countries.push( name );

				confirmed = true;
			} 	
		}
		else if ( 
			confirm( "Are you sure you want to stop blocking all " + 
				"traffic from " + name + "?" ) ) 
		{
			jq_opts = { 
				ipv_auth_token: ipv_auth_token,
				rule_type: "blacklist", 
				field: "country",
				action: "delete_mask",
				mask: name
			}

			blacklisted_countries = 
				jQuery.grep(blacklisted_countries, 
				function (val) { return val != name ; });

			confirmed = true;
		}

		if ( confirmed ) {
			jQuery.post( ipv_ajax_home + 
				"/ipv_manage_exceptions.php", 
				jq_opts,  
				function( data ) {
					ipv_load_country_table();
				}
			);
		}
		else ipv_load_country_table();

	}

</script>

<?php require_once( 'ipv_banner.php' ); ?>

<div class="analytics ipvmain">

	<div class="frame-header">
    <img src= "<?php
            echo plugins_url( 'images', dirname( __FILE__ ) )
        ?>/hdr-text/analytics.png">
	</div>

	<div id="analytics" style="float:left">

		<div id="ipv-date-range" class="analytics-pane">
			<div style="float:left" class="date-picker">
				View report for
				<select id="ipv-date-range-select" onchange="ipv_plot()">
					<option value="30">Last 30 days</option>
					<option value="14">Last 14 days</option>
					<option value="7" selected="selected">Last 7 days</option>
				</select>
			</div>
			<div id="ipv-date-range-display"></div>
		</div>

		<div class="graph-title" id="ipv-summary-total"></div>
	
		<div class="analytics-pane" id="ipv-summary"
			style="display:none;background-repeat: no-repeat" >

			<div id="disp-pie-container">
				<div id="disp-pie-wrapper">

					<div id="disp-pie"></div>

					<div id="disp-legend">
						<div id="disp-legend-visits"></div>
					
						<div id="disp-legend-ipq"> 	
							<div id="disp-legend-ipq-count" 	
								class="disp-legend-count"></div>
							<div class="disp-legend-label">
								Failed IPQ Score
								<span style="vertical-align:bottom" class="ipv-title-cluetip" title="IPQ Score||These requests were rejected because they came from IP Addresses with IPQ scores higher than the maximum IPQ Threshold allowed for your site at the time of the request.">&nbsp;[?]</span>
							</div>
						</div>
						
						<div id="disp-legend-country"> 	
							<div id="disp-legend-country-count" 	
								class="disp-legend-count"></div>
							<div class="disp-legend-label">
								Country blocked
								<span style="vertical-align:bottom" class="ipv-title-cluetip" title="Country Blocked||These visitors attempted to access your site from a country that you had explicitly blacklisted at the time of the request.">&nbsp;[?]</span>
							</div>
						</div>
						
						<div id="disp-legend-ip"> 	
							<div id="disp-legend-ip-count" 	
								class="disp-legend-count"></div>
							<div class="disp-legend-label">
								IP Blacklisted
								<span style="veritcal-align:bottom" class="ipv-title-cluetip" title="IP Blacklisted||These visitors attempted to access your site from an IP address that you had explicitly blacklisted at the time of the request.">&nbsp;[?]</span>
							</div>
						</div>
						
						<div id="disp-legend-accept"> 	
							<div id="disp-legend-accept-count" 	
								class="disp-legend-count"></div>
							<div class="disp-legend-label">
								Accepted Traffic
							</div>
						</div>
					</div>
				</div>

				<div id="disp-pie-text">
					<div class="pie-text-header">
						<img src= "<?php
								echo plugins_url( 'images', dirname( __FILE__ ) )
							?>/tooltip_question.png" alt="?" 
							class="ipv-title-cluetip"
							title="Block Reasons||After IPVenger determines whether to allow or reject a request to your site, it stores the &quot;disposition&quot; of that request for future analysis.| Blocked requests are always assigned one of three dispositions; IPQ Score, IP Blacklist or Country Blacklist.  | If the disposition for a request is &quot;IPQ Score&quot;, this means that the request was rejected because it came from an IP Address with an IPQ score higher than the maximum allowed for your site.| The &quot;Blacklist&quot; dispositions are assigned to requests from IP addresses or countries the user has explicitly blocked.| This chart shows the proportion of these dispositions across the entire specified date range."
						>
						The reason they were blocked
					</div>
					<div class="pie-text-detail">
The pie chart breaks down all visitors to your site, according to the action 
taking by IPVenger.  The bar graph below shows rejected requests by type on a 
daily basis.
					</div>
				</div>
			</div>

			<div id="cat-pie-container">
				<div id="cat-pie-wrapper">
					<div id="cat-pie"></div> 
					<div id="cat-pie-legend-container">	
						<div id="cat-pie-legend-title">IPQ Breakdown</div>
						<div id="cat-pie-legend"></div>
					</div>
				</div>
				<div id="cat-pie-text">
					<div class="pie-text-header"> 
						<img src= "<?php
								echo plugins_url( 'images', dirname( __FILE__ ) )
							?>/tooltip_question.png" alt="?" 
							class="ipv-title-cluetip"
							title="Threat Categories||Although hundreds of individual data points contribute to the IPQ Score, it is useful to see an overview of why an IP Address has been blocked.  | When IPVenger blocks an IP address from accessing your site, it stores the name of the single data point that had the biggest negative effect on the IPQ score.  This is the &quot;Category&quot; associated with the request.| This pie chart shows the proportion of each of the categories for requests that were blocked during the indicated date range.  Categories that represent less than 1% of the total blocks are summarized as &quot;Other&quot;.  | Detailed information on each of the category types can be found on the IPVenger website."
						>
						The main threat categories 
					</div>
					<div class="pie-text-detail">
This chart applies to requests that where rejected due to a high IPQ 
score.  It shows the main reason that the IPQ score was too high.
					</div>
				</div>
			</div>
		</div>

		<div class="analytics-pane" style="height:355px">
			<div class="graph-title">
				Daily blocks by type:	
				<img src=
					"<?php
						echo plugins_url( 'images', dirname( __FILE__ ) )
					?>/tooltip_question.png" alt="?" 
					class="ipv-title-cluetip"
					title="Daily Blocks||The height of each bar represents the total number of requests that were blocked on a particular day.  Each bar is subdivided to show the number of blocks for each reason (IPQ Score, IP Blacklist and IPQ Score)."
				>
			</div>
			<div style="position:relative">
				<div id="ipv-daily-y-label" class="ipv-rotate"># blocked</div>
				<div>

					<div id="ipv-no-data-overlay"></div>

					<div id="ipv-daily-container">
						<div class="fullwidth-bar" id="ipv-daily-risk">
						</div>

						<div class="legend">
							<div class="legend-color">
							<div class="legend-color-inner"
								style="background:<?php echo ipv_clr_ipq_block ?>;">&nbsp;</div>
							</div>
							<label>Failed IPQ Score</label>
							<div class="legend-color">
							<div class="legend-color-inner"
								style="background:<?php echo ipv_clr_ctry_blacklist ?>;">
								&nbsp;
							</div>
							</div>
							<label>Country blocked</label>
							<div class="legend-color">
							<div class="legend-color-inner"
								style="background:<?php echo ipv_clr_ip_blacklist ?>;">&nbsp;
							</div>
							</div>
							<label>IP blacklisted</label>
						</div>
						<div id="ipv-full-list-link-container" style="clear:both">
						<a class=ipv-link-button href="<?php
							$page = plugin_basename($parent_slug);
							echo admin_url("admin.php?page=${page}__ipcc" );
						?>">
							See the full list of IP addresses blocked</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="analytics-pane hide_if_empty">
			<div id="ipv-country-title">
				Threats by Country of Origin
				<img src= "<?php
						echo plugins_url( 'images', dirname( __FILE__ ) )
					?>/tooltip_question.png" alt="?"
					class="ipv-title-cluetip"
					title="Threats by Country||For each country, the first bar shows the percentage of all  blocked requests that originated from that country.  The second bar shows the average IPQ score of all requests for the given country (both blocked and allowed).| Data for at most fifteen countries is shown in descending order based on the sort option you select.  Data for all countries that have generated traffic to your website is displayed in the table below the graph."
				>
			</div>

			<div id="ipv-country-bar-container">

				<div id="ipv-country-sort-container">

					Sort by &nbsp; |
					<span id="ipv-country-sort-block" 
						style="font-weight:bold;color:<?php echo ipv_clr_country_pct_block?>"
						onclick="ipv_sort_country_bar( 'block' )">
							% Blocked by Country
					</span>
					|
					<span id="ipv-country-sort-ipq" 
						style="color:<?php echo ipv_clr_country_avg_ipq?>"
						onclick="ipv_sort_country_bar( 'ipq' )">
							Average IPQ Score
					</span>
				</div>

				<div class="fullwidth-bar" id="ipv-country-bar"></div>

			</div>

		</div>

		<div class="analytics-pane hide_if_empty">
	
			<div id="ipv-country-filter-container">
				<div class="gui-widget" style="float:left">
					Country Name
					<select id="ipv_country_search" 
						onchange="ipv_filter_country( this.value )">
					</select>
				</div>
				<input type="button" class="ipv-secondary"
					id="ipv_reset_button" 
					onclick="ipv_reset_filter()" 
					value="Show All"
					disabled='disabled'>
			</div>

			<div id="ipv-country-table-container">

				<table id="ipv-country-table" cellspacing="0">
					<thead>
						<tr>
							<th class="header first">COUNTRY OF ORIGIN</th>
							<th class="header middle">% ALL BLOCKS</th>
							<th class="header middle">% All TRAFFIC</th>
							<th class="header middle">AVG IPQ</th>
							<th class="header last">PROTECTION</th>
						</tr>
					</thead>
					<tbody id="ipv-country-table-body"></tbody>
				</table>

			</div>

			<div id="ipv-country-list-link-container" style="clear:both">
				<a class=ipv-link-button href="<?php
					$page = plugin_basename($parent_slug);
					echo admin_url(
						"admin.php?page=${page}__adv_settings" );
				?>">
					View/Edit Blacklist with Advanced Settings</a>
			</div>

		</div>

	</div>

	<div class="ip-ticker">
		<div class="ipv-ticker-bookend">Latest IPs Blocked:</div>
		<div id="ipv-ticker-slidedown" style="display:none">
			<div class="ipv-ticker-divider"
				style="background: url( '<?php
					echo plugins_url( 'images', dirname( __FILE__ ) )
				?>/ticker-divider.png' ) no-repeat;">
			</div>
			<input type="button" id="ipv-ticker-button" 
				onclick="ipv_update_ticker( true )" 
				value="VIEW 0 NEW BLOCKS">
		</div>
		<div id="ipv-ticker-container">
			<div id="ipv-ticker" ></div>
		</div>
		<div class="ipv-ticker-bookend" id="ipv-ticker-footer">
			<a href="<?php
				$page = plugin_basename($parent_slug);
				echo admin_url("admin.php?page=${page}__ipcc" );
			?>"> View all blocks </a>
		</div>
	</div>

</div> <!-- ipvmain -->

</div> <!-- wrap ipv -->
 
