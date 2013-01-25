<?php
/**
 * function ipv_matches_mask - check if input string matches the mask
 *
 * mask_type must be "wildcard".  Future versions may support other types
 * e.g. 'ip_range'.  For mask_type "wildcard", the mask is a simplified 
 * regular expression - only "*" is supported and is converted to .*
 * 
 *  @param	string	$mask		mask to be "searched" against
 *  @param	string	$str		string to search
 *  @param	string	$mask_type	type of mask (e.g. "wildcard")
 *
 *  @return boolean 			true if match found, else false
*/

function matches_mask( $mask, $str, $mask_type ) {

	$result = false;

	switch ( $mask_type ) {
	
		case 'wildcard':

			// convert simplified mask into a regular expression
			$mask = '/^' . preg_quote( $mask ) . '/';

			// now put asterisks back in 
			$mask = str_replace( '\*', '.*', $mask );

			$result = ( preg_match( $mask, $str ) );

			break;

		default:
			print '$mask_type not implemented!';
			exit;
			break;
		

	}

	return $result;
	
}
?>
