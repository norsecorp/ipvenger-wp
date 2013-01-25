
    // blacklist, whitelist or clear the given ip

    function ipv_ip_bw_list( ip, text, action ) {
	
		var confirmed = false;

		if ( typeof action == "undefined" ) action = function() {};

        if ( text == "Blacklisted" ) 
		{
			if ( confirm( "Are you sure you want to block all traffic from " +
                ip + "?" ) ) {
				jQuery.post( ipv_ajax_home +
					"/ipv_manage_exceptions.php",
					{
						ipv_auth_token: ipv_auth_token, 
						rule_type: "blacklist",
						field: "ip",
						action: "add",
						mask: ip
					 },
					function( data ) {
						action();
					}
				);
				confirmed = true;
			}
        }
        else if ( text == "Whitelisted" ) 
		{
        	if ( confirm( "Are you sure you want to allow all traffic from " +
                ip + ", regardless of IPQ Score?" ) )
			{
				jQuery.post( ipv_ajax_home +
					"/ipv_manage_exceptions.php",
					{
						ipv_auth_token: ipv_auth_token,
						rule_type: "whitelist",
						field: "ip",
						action: "add",
						mask: ip
					 },
					function( data ) {
						action();
					}
				);
				confirmed = true;
			}
		}
        else { 

			if (  confirm( "Are you sure you want to resume normal " +
				  "IPVenger processing for " + ip + "?" ) )
			{
				jQuery.post( ipv_ajax_home +
					"/ipv_manage_exceptions.php",
					{
						ipv_auth_token: ipv_auth_token,
						rule_type: "whitelist",
						field: "ip",
						action: "delete_mask",
						mask: ip
					 },
					function( data ) {
						action();
					}
				);
				jQuery.post( ipv_ajax_home +
					"/ipv_manage_exceptions.php",
					{
						ipv_auth_token: ipv_auth_token,
						rule_type: "blacklist",
						field: "ip",
						action: "delete_mask",
						mask: ip
					 },
					function( data ) {
						action();
					}
				);
				confirmed = true;
			}

		}

		// if user cancelled, reset to orginal states
		if ( ! confirmed ) {
			action();
		}

    }
