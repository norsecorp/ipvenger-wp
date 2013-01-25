<?php 

/**
 *
 *	ipv_update_ipq.php:  update default IPQ score
 *
 *  Single POST variable arg, the new IPQ score 
 *
 * 		ipq:	float, in range [0,100]
 *
*/
    /** Do not add any code before this include, which does security checks **/
    require( dirname( __FILE__ ) . '/ipv_prep_ajax.php' );

    require_once( dirname( __FILE__ ) .
        '/../cms-includes/ipv_cms_workarounds.php' );

	require_once( dirname( __FILE__ ) .  
		'/../core-includes/ipv_config_utils.php' );

	ipv_set_default_risk( $_POST['ipq'] );

?>
