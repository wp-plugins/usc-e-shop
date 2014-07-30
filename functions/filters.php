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
		$sku_encoded = $value['sku'];
		$skucode = urldecode($value['sku']);
		$sku = $skus[$skucode];
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
			$skucode, $sku['name'], $sku['cprice'], $value['price'], $value['quantity'], 
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
			foreach( (array)$value['advance'] as $akey => $avalue ) {
				$advance = maybe_unserialize( $avalue );
				if( is_array($advance) ) {
					$post_id = $value['post_id'];
					if( is_array( $advance[$post_id][$sku_encoded] ) ) {
						$akeys = array_keys( $advance[$post_id][$sku_encoded] );
						foreach( (array)$akeys as $akey ) {
							$avalue = serialize( $advance[$post_id][$sku_encoded][$akey] );
							$aquery = $wpdb->prepare("INSERT INTO $cart_meta_table 
								( cart_id, meta_type, meta_key, meta_value ) VALUES ( %d, 'advance', %s, %s )", 
								$cart_id, $akey, $avalue
							);
							$wpdb->query( $aquery );
						}
					} else {
						$akeys = array_keys( $advance );
						$akey = ( empty($akeys[0]) ) ? 'advance' : $akeys[0];
						$avalue = serialize( $advance );
						$aquery = $wpdb->prepare("INSERT INTO $cart_meta_table 
							( cart_id, meta_type, meta_key, meta_value ) VALUES ( %d, 'advance', %s, %s )", 
							$cart_id, $akey, $avalue
						);
						$wpdb->query( $aquery );
					}
				} else {
					$avalue = urldecode( $avalue );
					$aquery = $wpdb->prepare("INSERT INTO $cart_meta_table 
						( cart_id, meta_type, meta_key, meta_value ) VALUES ( %d, 'advance', 'advance', %s )", 
						$cart_id, $avalue
					);
					$wpdb->query( $aquery );
				}
			}
		}

		do_action( 'usces_action_reg_ordercart_row', $cart_id, $row_index, $value, $args );
	}
}
function fiter_mainTitle($title, $sep){
	global $usces;
	
	if( empty($title) ){
		$newtitle = $title;
	}else{
		$title = trim( str_replace( $sep, '', $title ) );
		switch($usces->page){
			case 'cart':
				$newtitle = apply_filters('usces_filter_title_cart', __('In the cart', 'usces'));
				break;

			case 'customer':
				$newtitle = apply_filters('usces_filter_title_customer', __('Customer Information', 'usces'));
				break;

			case 'delivery':
				$newtitle = apply_filters('usces_filter_title_delivery', __('Shipping / Payment options', 'usces'));
				break;

			case 'confirm':
				$newtitle = apply_filters('usces_filter_title_confirm', __('Confirmation', 'usces'));
				break;

			case 'ordercompletion':
				$newtitle = apply_filters('usces_filter_title_ordercompletion', __('Order Complete', 'usces'));
				break;

			case 'error':
				$newtitle = apply_filters('usces_filter_title_error', __('Error', 'usces')); //new fitler name
				break;

			case 'search_item':
				$newtitle = apply_filters('usces_filter_title_search_item', __("'AND' search by categories", 'usces'));
				break;

			case 'maintenance':
				$newtitle = apply_filters('usces_filter_title_maintenance', __('Under Maintenance', 'usces'));
				break;

			case 'login':
				$newtitle = apply_filters('usces_filter_title_login', __('Log-in for members', 'usces'));
				break;

			case 'member':
				$newtitle = apply_filters('usces_filter_title_member', __('Membership information', 'usces'));
				break;

			case 'newmemberform':
				$newtitle = apply_filters('usces_filter_title_newmemberform', __('New enrollment form', 'usces'));
				break;

			case 'newcompletion':
				$newtitle = apply_filters('usces_filter_title_newcompletion', __('New enrollment complete', 'usces'));//new fitler name
				break;

			case 'editmemberform':
				$newtitle = apply_filters('usces_filter_title_editmemberform', __('Member information editing', 'usces'));//new fitler name
				break;

			case 'editcompletion':
				$newtitle = apply_filters('usces_filter_title_editcompletion', __('Membership information change is completed', 'usces'));//new fitler name
				break;

			case 'lostmemberpassword':
				$newtitle = apply_filters('usces_filter_title_lostmemberpassword', __('The new password acquisition', 'usces'));
				break;

			case 'lostcompletion':
				$newtitle = apply_filters('usces_filter_title_lostcompletion', __('New password procedures for obtaining complete', 'usces'));//new fitler name
				break;

			case 'changepassword':
				$newtitle = apply_filters('usces_filter_title_changepassword', __('Change password', 'usces'));
				break;

			case 'changepasscompletion':
				$newtitle = apply_filters('usces_filter_title_changepasscompletion', __('Password change is completed', 'usces'));
				break;

			default:
				$newtitle = apply_filters('usces_filter_title_main_default', $title);//new fitler name
		}
		$newtitle = $newtitle .' '.$sep.' ';
	}
	return $newtitle;
}

//Univarsal Analytics
function usces_Universal_trackPageview(){
	global $usces;

	switch($usces->page){
		case 'cart':
			$push = array();
			$push[] = "'page' : '/wc_cart'";
			break;

		case 'customer':
			$push = array();
			$push[] = "'page' : '/wc_customer'";
			break;

		case 'delivery':
			$push = array();
			$push[] = "'page' : '/wc_delivery'";
			break;

		case 'confirm':
			$push = array();
			$push[] = "'page' : '/wc_confirm'";
			break;

		case 'ordercompletion':
			$sesdata =  $usces->cart->get_entry();
			$order_id = $sesdata['order']['ID'];
			$data = $usces->get_order_data($order_id, 'direct');
			$cart = unserialize($data['order_cart']);
			$total_price = $usces->get_total_price( $cart ) + $data['order_discount'] - $data['order_usedpoint'];
			$push =array();
			$push[] = "'page' : '/wc_ordercompletion'";
			$push[] = "'require', 'ecommerce', 'ecommerce.js'";
			$push[] = "'ecommerce:addTransaction', { 
						id: '". $order_id ."', 
						affiliation: '". get_option('blogname') ."',
						revenue: '". $total_price ."',
						shipping: '". $data['order_shipping_charge'] ."',
						tax: '". $data['order_tax'] ."' }";
			for( $i=0; $i<count($cart); $i++ ){
				$cart_row = $cart[$i];
				$post_id  = $cart_row['post_id'];
				$sku = urldecode($cart_row['sku']);
				$quantity = $cart_row['quantity'];
				$itemName = $usces->getItemName($post_id);
				$skuPrice = $cart_row['price'];
				$cats = $usces->get_item_cat_genre_ids( $post_id );
				if( is_array($cats) )
					sort($cats);
				$category = ( isset($cats[0]) ) ? get_cat_name($cats[0]): '';
				
				$push[] = "'ecommerce:addItem', {
							id: '". $order_id ."',
							sku: '". $sku ."',
							name: '". $itemName."',
							category: '". $category."',
							price: '". $skuPrice."',
							quantity: '". $quantity."' }";
			}
			$push[] = "'ecommerce:send'";
			break;

		case 'error':
			$push = array();
			$push[] = "'page' : '/wc_error'";
			break;

		case 'search_item':
			$push = array();
			$push[] = "'page' : '/wc_search_item'";
			break;

		case 'maintenance':
			$push = array();
			$push[] = "'page' : '/wc_maintenance'";
			break;

		case 'login':
			$push = array();
			$push[] = "'page' : '/wc_login'";
			break;

		case 'member':
			$push = array();
			$push[] = "'page' : '/wc_member'";
			break;

		case 'newmemberform':
			$push = array();
			$push[] = "'page' : '/wc_newmemberform'";
			break;

		case 'newcompletion':
			$push = array();
			$push[] = "'page' : '/wc_newcompletion'";
			break;

		case 'editmemberform':
			$push = array();
			$push[] = "'page' : '/wc_editmemberform'";
			break;

		case 'editcompletion':
			$push = array();
			$push[] = "'page' : '/wc_editcompletion'";
			break;

		case 'lostmemberpassword':
			$push = array();
			$push[] = "'page' : '/wc_lostmemberpassword'";
			break;

		case 'lostcompletion':
			$push = array();
			$push[] = "'page' : '/wc_lostcompletion'";
			break;

		case 'changepassword':
			$push = array();
			$push[] = "'page' : '/wc_changepassword'";
			break;

		case 'changepasscompletion':
			$push = array();
			$push[] = "'page' : '/wc_changepasscompletion'";
			break;

		default:
			$push = array();
			break;
	}
	return $push;
}

//Classic Analytics
function usces_Classic_trackPageview(){
	global $usces;

	switch($usces->page){
		case 'cart':
			$push = array();
			$push = usces_trackPageview_cart($push);
			break;

		case 'customer':
			$push = array();
			$push = usces_trackPageview_customer($push);
			break;

		case 'delivery':
			$push = array();
			$push = usces_trackPageview_delivery($push);
			break;

		case 'confirm':
			$push = array();
			$push = usces_trackPageview_confirm($push);
			break;

		case 'ordercompletion':
			$push =array();
			$push = usces_trackPageview_ordercompletion($push);
			break;

		case 'error':
			$push = array();
			$push = usces_trackPageview_error($push);
			break;

		case 'login':
			$push = array();
			$push = usces_trackPageview_login($push);
			break;

		case 'member':
			$push = array();
			$push = usces_trackPageview_member($push);
			break;

		case 'newmemberform':
			$push = array();
			$push = usces_trackPageview_newmemberform($push);
			break;

		case 'newcompletion':
			$push = array();
			$push = usces_trackPageview_newcompletion($push);
			break;

		case 'editmemberform':
			$push = array();
			$push = usces_trackPageview_editmemberform($push);
			break;

		case 'search_item':
			$push = array();
			$push = usces_trackPageview_search_item($push);
			break;

		case 'maintenance':
		case 'editcompletion':
		case 'lostmemberpassword':
		case 'lostcompletion':
		case 'changepassword':
		case 'changepasscompletion':
		default:
			$push = array();
			break;
	}
	return $push;
}

function usces_use_point_nonce(){
	wp_nonce_field( 'use_point', 'wc_nonce');
}

function usces_post_member_nonce(){
	wp_nonce_field( 'post_member', 'wc_nonce');
}

function usces_order_memo_form_detail_top( $data, $csod_meta ){
	global $usces;

	$order_memo = '';
	if( !empty($data['ID']) ){
		$order_memo = $usces->get_order_meta_value('order_memo', $data['ID']);
	}
	$res = '<tr>
				<td class="label border">'. __('Administrator Note', 'usces') .'</td>
				<td colspan="5" class="col1 border memo">
					<textarea name="order_memo" class="order_memo">'.esc_html($order_memo).'</textarea>
				</td>
			</tr>';
	echo $res;
}

function usces_update_order_memo($new_orderdata){
	global $usces;

	$usces->set_order_meta_value('order_memo', $_POST['order_memo'], $new_orderdata->ID);
}

function usces_action_lostmail_inform(){
	$mem_mail = urldecode($_REQUEST['mem']);
	$lostkey = urldecode($_REQUEST['key']);
	$html = '
	<input type="hidden" name="lostmail" value="' . esc_attr($mem_mail) . '" />
	<input type="hidden" name="lostkey" value="' . esc_attr($lostkey) . '" />' . "\n";
	echo $html;
}
function usces_filter_lostmail_inform($html){
	$mem_mail = urldecode($_REQUEST['mem']);
	$lostkey = urldecode($_REQUEST['key']);
	$html .= '
	<input type="hidden" name="lostmail" value="' . esc_attr($mem_mail) . '" />
	<input type="hidden" name="lostkey" value="' . esc_attr($lostkey) . '" />' . "\n";
	return $html;
}