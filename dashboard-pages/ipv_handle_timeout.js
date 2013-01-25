
// handle AJAX session timeout

function ipv_handle_timeout( e, xhr, settings ) {

	var resp, ct = xhr.getResponseHeader( "content-type" ) || "";

	if ( xhr.responseText.substring( 0, 12 ) == "Unauthorized" ) {

		if ( this.confirmPending ) return;

		this.confirmPending = true;

		if ( typeof window.ipv_timeout_callback == 'function' ) 
			ipv_timeout_callback();

		if ( confirm( "Your connection to the IPVenger server has timed out." + 
				"\nClick OK to reload the current page and reconnect." +
				"\n\n\n(Server returned " + xhr.responseText + ")"
			)
		) location.reload();

		this.confirmPending = false;


	}

}
