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

function usces_action_reg_orderdata_stocks($args){
	global $usces;
	extract($args);
	
	foreach($cart as $cartrow){
		$sku = urldecode($cartrow['sku']);
		$zaikonum = $usces->getItemZaikoNum( $cartrow['post_id'], $sku );
		if($zaikonum == '') continue;
		$zaikonum = $zaikonum - $cartrow['quantity'];
		$usces->updateItemZaikoNum( $cartrow['post_id'], $sku, $zaikonum );
		if($zaikonum <= 0) $usces->updateItemZaiko( $cartrow['post_id'], $sku, 2 );
	}
}

function usces_action_ogp_meta(){
	global $usces, $post;
	if( empty($post) || !$usces->is_item($post) || !is_single() )
		return;
		
	$item = $usces->get_item( $post->ID );
	$pictid = $usces->get_mainpictid($item['itemCode']);
	$image_info = wp_get_attachment_image_src( $pictid, 'thumbnail' );

	$ogs['title'] = $item['itemName'];
	$ogs['type'] = 'product';
	$ogs['description'] = strip_tags( get_the_title($post->ID) );
	$ogs['url'] = get_permalink($post->ID);
	$ogs['image'] = $image_info[0];
	$ogs['site_name'] = get_option('blogname');
	$ogs = apply_filters( 'usces_filter_ogp_meta', $ogs, $post->ID );
	
	foreach( $ogs as $key => $value ){
		echo "\n" . '<meta property="og:' . $key . '" content="' . $value . '">';
	}

}

function wc_purchase_nonce($html, $payments, $acting_flag, $rand, $purchase_disabled){
	if( strpos($html, 'wc_nonce') || !in_array( $payments['settlement'], array('COD', 'installment', 'transferAdvance', 'transferDeferred', 'acting_zeus_card')) )
		return $html;
	$wc_nonce = wp_create_nonce('wc_purchase_nonce');
	$html .= wp_nonce_field( 'wc_purchase_nonce', 'wc_nonce', false, false )."\n";
	return $html;
}

function wc_purchase_nonce_check(){
	global $usces;
	$entry = $usces->cart->get_entry();
	if( !isset($entry['order']['payment_name']) || empty($entry['order']['payment_name']) ){
		wp_redirect( home_url() );
		exit;	
	}
	
	$payments = usces_get_payments_by_name($entry['order']['payment_name']);
	if( !in_array( $payments['settlement'], array('COD', 'installment', 'transferAdvance', 'transferDeferred' )) )
		return true;
	
	$nonce = isset($_REQUEST['wc_nonce']) ? $_REQUEST['wc_nonce'] : '';
	if( wp_verify_nonce($nonce, 'wc_purchase_nonce') )
		return true;
		
	wp_redirect( home_url() );
	exit;	
}

function wc_mkdir(){
	global $usces;
	if( is_admin() && !WCUtils::is_blank($usces->options['logs_path']) && false !== strpos($_SERVER['SERVER_SOFTWARE'],'Apache')){
		$welcart_file_dir = $usces->options['logs_path'] . '/welcart';
		$logs_dir = $welcart_file_dir . '/logs';
		if( !file_exists($welcart_file_dir) ){
			$res = @mkdir($welcart_file_dir, 0700);
			if(!$res){
				$msg = '<div class="error"><p>下記のディレクトリーを、所有者：' . get_current_user() . '、パーミッション：700 で作成してください。 <br />' . $welcart_file_dir . '</p></div>';
				add_action('admin_notices', $c = create_function('', 'echo "' . addcslashes($msg,'"') . '";')); 
			}
		}
				$stat = stat($welcart_file_dir);
print_r($stat);
die();
		if( is_writable($welcart_file_dir) ){
			$res = @mkdir($logs_dir, 0700);
			if(!$res){
				$msg = '<div class="error"><p>下記のディレクトリーを、所有者：' . get_current_user() . '、パーミッション：700 で作成してください。 <br />' . $welcart_file_dir . '</p></div>';
				add_action('admin_notices', $c = create_function('', 'echo "' . addcslashes($msg,'"') . '";')); 
			}
		}
	}
}

function usces_reg_ordercartdata( $args ){
	global $usces, $wpdb;
	/*
	$args = array(
	'cart'=>$cart, 'entry'=>$entry, 'order_id'=>$order_id, 'member_id'=>$member['ID'], 
	'payments'=>$set, 'charging_type'=>$charging_type);
	*/
	extract($args);
	
	if( !$order_id )
		return;
	
	$cart_table = $wpdb->prefix . "usces_ordercart";
	$cart_meta_table = $wpdb->prefix . "usces_ordercart_meta";
	foreach( $cart as $row_index => $value ){
		$item_code = get_post_meta( $value['post_id'], '_itemCode', true);
		$item_name = get_post_meta( $value['post_id'], '_itemName', true);
		$skus = $usces->get_skus($value['post_id'], 'code');
		$sku = $skus[$value['sku']];
		if( empty($usces->option['tax_rate']) ){
			$tax = 0;
		
		}else{
			$tax = ($value['price'] * $value['quantity']) * $usces->options['tax_rate'] / 100;
			$cr = $usces->options['system']['currency'];
			$decimal = $usces_settings['currency'][$cr][1];
			$decipad = (int)str_pad( '1', $decimal+1, '0', STR_PAD_RIGHT );
			switch( $usces->options['tax_method'] ){
				case 'cutting':
					$tax = floor($tax*$decipad)/$decipad;
					break;
				case 'bring':
					$tax = ceil($tax*$decipad)/$decipad;
					break;
				case 'rounding':
					if( 0 < $decimal ){
						$tax = round($tax, (int)$decimal);
					}else{
						$tax = round($tax);
					}
					break;
			}
		}
		$query = $wpdb->prepare("INSERT INTO $cart_table 
			(
			order_id, row_index, post_id, item_code, item_name, 
			sku_code, sku_name, cprice, price, quantity, 
			unit, tax, destination_id, cart_serial 
			) VALUES (
			%d, %d, %d, %s, %s, 
			%s, %s, %f, %f, %d, 
			%s, %d, %d, %s 
			)", 
			$order_id, $row_index, $value['post_id'], $item_code, $item_name, 
			$value['sku'], $sku['name'], $sku['cprice'], $value['price'], $value['quantity'], 
			$sku['unit'], $tax, NULL, $value['serial']
		);
		$wpdb->query($query);
		
		$cart_id = $wpdb->insert_id ;
		if($value['options']){
			foreach((array)$value['options'] as $okey => $ovalue){
				$okey = urldecode($okey);
				if(is_array($ovalue)) {
					$temp = array();
					foreach( $ovalue as $k => $v ){
						$temp[$k] = urldecode($v);
					}
					$ovalue = serialize($temp);
				} else {
					$ovalue = urldecode($ovalue);
				}
				$oquery = $wpdb->prepare("INSERT INTO $cart_meta_table 
					( cart_id, meta_type, meta_key, meta_value ) VALUES (%d, %s, %s, %s)", 
					$cart_id, 'option', $okey, $ovalue
				);
				$wpdb->query($oquery);
			}
		}
		if( $value['advance'] ) {
			$aquery = $wpdb->prepare( "INSERT INTO $cart_meta_table 
				( cart_id, meta_type, meta_key, meta_value ) VALUES ( %d, %s, %s, %s )", 
				$cart_id, 'advance', 'advance', serialize($value['advance'])
			);
			$wpdb->query( $aquery );
		}

		do_action( 'usces_action_reg_ordercart_row', $cart_id, $row_index, $value, $args );
	}
}
