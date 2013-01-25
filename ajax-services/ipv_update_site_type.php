<?php 

/**
 *
 *	ipv_update_site_type.php:  update general settings site type
 *
 *  Single POST variable arg, the new short type
 *
 * 		site_type:		string, e.g. "ecommerce", "webapp", etc.
 * 		apply_update:	boolean, update the database only if "true"
 * 		ipq:			float, (optional) update database record for this 
 *						site type to the specified IPQ
 *
*/

    /** Do not add any code before this include, which does security checks **/
    require( dirname( __FILE__ ) . '/ipv_prep_ajax.php' );

    require_once( dirname( __FILE__ ) .
        '/../cms-includes/ipv_cms_workarounds.php' );

	require_once( dirname( __FILE__ ) .  
		'/../core-includes/ipv_config_utils.php' );

	$new_type = $_POST['site_type'];

	// if IPQ specified update that first so returned site info is correct
    if ( isset( $_POST['ipq'] ) ) {
        ipv_update_site_ipq( $new_type, $_POST['ipq'] );
    }

	ipv_get_site_type_by_name( 
		$new_type, $long_name, $ipq_thresh, $ipq_desc, $text );

	if ( $_POST['apply_update'] == 'true' ) {

		ipv_set_site_type( $new_type );
		ipv_set_default_risk( $ipq_thresh );
	
	}

	$data = array( 
			'long_name' => $long_name, 
			'ipq_desc' => $ipq_desc,
			'ipq_thresh' => $ipq_thresh,
			'ipq_detail' => $text );

	echo json_encode( $data );
?>
