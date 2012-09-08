<?php
function usces_filter_get_post_metadata( $null, $object_id, $meta_key, $single){
	global $wpdb;
	$query = $wpdb->prepare("SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s", $object_id, $meta_key);
	$metas = $wpdb->get_col($query);
	if ( !empty($metas) ) {
			return array_map('maybe_unserialize', $metas);
	}

	if ($single)
		return '';
	else
		return array();
}

function usces_action_reg_orderdata( $args ){
	global $wpdb, $usces;
	$options = get_option('usces');
	extract($args);

	/*  Register decorated order id ***************************************************/
	$olimit = 0;
	if( ! $options['system']['dec_orderID_flag'] ){
		$dec_order_id = str_pad($order_id, $options['system']['dec_orderID_digit'], "0", STR_PAD_LEFT);
	}else{
		$otable = $wpdb->prefix . 'usces_order_meta';
		while( $ukey = usces_get_key( $options['system']['dec_orderID_digit'] ) ){
			$ores = $wpdb->get_var($wpdb->prepare("SELECT meta_key FROM $otable WHERE meta_key = %s AND meta_value = %s LIMIT 1", 'dec_order_id', $ukey));
			if( !$ores || 100 < $olimit )
				break;
			$olimit++;
		}
		$dec_order_id = $ukey;
	}
	$dec_order_id = apply_filters( 'usces_filter_dec_order_id_prefix', $options['system']['dec_orderID_prefix'], $args ) . apply_filters( 'usces_filter_dec_order_id', $dec_order_id, $args );
	
	if( 100 < $olimit ){
		$usces->set_order_meta_value('dec_order_id', uniqid(), $order_id);
	}else{
		$usces->set_order_meta_value('dec_order_id', $dec_order_id, $order_id);
	}
	unset($dec_order_id, $otable, $olimit, $ukey, $ores);
	/***********************************************************************************/
}

function usces_action_ogp_meta(){
	global $usces, $post;
	if( !$usces->is_item($post) || !is_single() )
		return;
		
	$item = $usces->get_item( $post->ID );
	$pictid = $usces->get_mainpictid($item['itemCode']);
	$image_info = wp_get_attachment_image_src( $pictid, 'thumbnail' );

	$ogs['title'] = $item['itemName'];
	$ogs['type'] = 'produnct';
	$ogs['description'] = get_the_title($post->ID);
	$ogs['url'] = get_permalink($post->ID);
	$ogs['image'] = $image_info[0];
	$ogs['site_name'] = get_option('blogname');
	$ogs = apply_filters( 'usces_filter_ogp_meta', $ogs, $post->ID );
	
	foreach( $ogs as $key => $value ){
		echo "\n" . '<meta property="og:' . $key . '" content="' . $value . '">';
	}

}

?>