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

//function usces_cart_row_of_each_device( $row, $cart, $materials ){
//	extract($materials);
//	
//	$row = '';
//	if ( empty($options) ) {
//		$optstr =  '';
//		$options =  array();
//	}
//	$row .= '<tr>
//		<td>' . ($i + 1) . '</td>
//		<td>';
//		$cart_thumbnail = '<a href="' . get_permalink($post_id) . '">' . wp_get_attachment_image( $pictid, array(60, 60), true ) . '</a>';
//		$row .= apply_filters('usces_filter_cart_thumbnail', $cart_thumbnail, $post_id, $pictid, $i,$cart_row);
//		$row .= '</td><td class="aleft">' . esc_html($cartItemName) . '<br />';
//	if( is_array($options) && count($options) > 0 ){
//		$optstr = '';
//		foreach($options as $key => $value){
//			if( !empty($key) ) {
//				$key = urldecode($key);
//				if(is_array($value)) {
//					$c = '';
//					$optstr .= esc_html($key) . ' : '; 
//					foreach($value as $v) {
//						$optstr .= $c.nl2br(esc_html(urldecode($v)));
//						$c = ', ';
//					}
//					$optstr .= "<br />\n"; 
//				} else {
//					$optstr .= esc_html($key) . ' : ' . nl2br(esc_html(urldecode($value))) . "<br />\n"; 
//				}
//			}
//		}
//		$row .= apply_filters( 'usces_filter_option_cart', $optstr, $options);
//	}
//	$row .= '</td>
//		<td class="aright">';
//	if( usces_is_gptekiyo($post_id, $sku_code, $quantity) ) {
//		$usces_gp = 1;
//		$Business_pack_mark = '<img src="' . get_template_directory_uri() . '/images/gp.gif" alt="' . __('Business package discount','usces') . '" /><br />';
//		$row .= apply_filters('usces_filter_itemGpExp_cart_mark', $Business_pack_mark);
//	}
//	$row .= usces_crform($skuPrice, true, false, 'return') . '
//		</td>
//		<td><input name="quant[' . $i . '][' . $post_id . '][' . $sku . ']" class="quantity" type="text" value="' . esc_attr($cart_row['quantity']) . '" /></td>
//		<td class="aright">' . usces_crform(($skuPrice * $cart_row['quantity']), true, false, 'return') . '</td>
//		<td ' . $red . '>' . $stock . '</td>
//		<td>';
//	foreach($options as $key => $value){
//		if(is_array($value)) {
//			foreach($value as $v) {
//				$row .= '<input name="itemOption[' . $i . '][' . $post_id . '][' . $sku . '][' . $key . '][' . $v . ']" type="hidden" value="' . $v . '" />';
//			}
//		} else {
//			$row .= '<input name="itemOption[' . $i . '][' . $post_id . '][' . $sku . '][' . $key . ']" type="hidden" value="' . $value . '" />';
//		}
//	}
//	$row .= '<input name="itemRestriction[' . $i . ']" type="hidden" value="' . $itemRestriction . '" />
//		<input name="stockid[' . $i . ']" type="hidden" value="' . $stockid . '" />
//		<input name="itempostid[' . $i . ']" type="hidden" value="' . $post_id . '" />
//		<input name="itemsku[' . $i . ']" type="hidden" value="' . $sku . '" />
//		<input name="zaikonum[' . $i . '][' . $post_id . '][' . $sku . ']" type="hidden" value="' . esc_attr($skuZaikonum) . '" />
//		<input name="skuPrice[' . $i . '][' . $post_id . '][' . $sku . ']" type="hidden" value="' . esc_attr($skuPrice) . '" />
//		<input name="advance[' . $i . '][' . $post_id . '][' . $sku . ']" type="hidden" value="' . esc_attr($advance) . '" />
//		<input name="delButton[' . $i . '][' . $post_id . '][' . $sku . ']" class="delButton" type="submit" value="' . __('Delete','usces') . '" />
//		</td>
//	</tr>';
//}


?>