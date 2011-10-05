<?php
function usces_filter_get_post_metadata( $null, $object_id, $meta_key, $single){
	global $wpdb;
	$query = $wpdb->prepare("SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s", $object_id, $meta_key);
	$metas = $wpdb->get_col($query);
	if ( !empty($metas) ) {
		if ( $single )
			return maybe_unserialize( $metas[0] );
		else
			return array_map('maybe_unserialize', $metas);
	}

	if ($single)
		return '';
	else
		return array();
}

?>