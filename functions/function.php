<?php

function usces_ajax_send_mail() {
	global $wpdb, $usces;
	
	$order_para = array(
			'to_name' => sprintf(__('Mr/Mrs %s', 'usces'), trim(urldecode($_POST['name']))),
			'to_address' => trim($_POST['mailaddress']), 
			'from_name' => get_option('blogname'), 
			'from_address' => $usces->options['sender_mail'], 
			'return_path' => $usces->options['error_mail'],
			'subject' => trim(urldecode($_POST['subject'])),
			'message' => trim(urldecode($_POST['message']))
			);
	
	$res = usces_send_mail( $order_para );
	if($res){
		$tableName = $wpdb->prefix . "usces_order";
		$order_id = $_POST['order_id'];
		$checked = $_POST['checked'];

		$query = $wpdb->prepare("SELECT `order_check` FROM $tableName WHERE ID = %d", $order_id);
		$res = $wpdb->get_var( $query );

		$checkfield = unserialize($res);
		if( !isset($checkfield[$checked]) ) $checkfield[$checked] = $checked;
		//$checkfield = 'OK';
		$query = $wpdb->prepare("UPDATE $tableName SET `order_check`=%s WHERE ID = %d", serialize($checkfield), $order_id);
		$wpdb->query( $query );

		$bcc_para = array(
				'to_name' => 'Shop Admin',
				'to_address' => $usces->options['sender_mail'], 
				'from_name' => 'Welcart Auto BCC', 
				'from_address' => 'Welcart', 
				'return_path' => $usces->options['error_mail'],
				'subject' => trim(urldecode($_POST['subject'])) . ' to ' . sprintf(__('Mr/Mrs %s', 'usces'), trim(urldecode($_POST['name']))),
				'message' => trim(urldecode($_POST['message']))
				);
		
		usces_send_mail( $bcc_para );

		return 'success';
	}else{
		return 'error';
	}
}

function usces_order_confirm_message($order_id) {
	global $usces, $wpdb;
	
	$tableName = $wpdb->prefix . "usces_order";
	$query = $wpdb->prepare("SELECT * FROM $tableName WHERE ID = %d", $order_id);
	$data = $wpdb->get_row( $query, ARRAY_A );
	$deli = unserialize($data['order_delivery']);
	$cart = unserialize($data['order_cart']);
	$condition = unserialize($data['order_condition']);

	$total_full_price = $data['order_item_total_price'] - $data['order_usedpoint'] + $data['order_discount'] + $data['order_shipping_charge'] + $data['order_cod_fee'] + $data['order_tax'];


	$mail_data = $usces->options['mail_data'];
	$payment = $usces->getPayments( $data['order_payment_name'] );
	$res = false;

	if($_POST['mode'] == 'mitumoriConfirmMail'){
		$msg_body = "\r\n\r\n\r\n" . __('Estimate','usces') . "\r\n";
		$msg_body .= "******************************************************************\r\n";
//20110118ysk start
		$msg_body .= usces_mail_custom_field_info( 'customer', 'name_pre', $order_id );
//20110118ysk end
		$msg_body .= __('Request of','usces') . " : " . sprintf(__('Mr/Mrs %s', 'usces'), ($data['order_name1'] . ' ' . $data['order_name2'])) . "\r\n";
//20110118ysk start
		$msg_body .= usces_mail_custom_field_info( 'customer', 'name_after', $order_id );
//20110118ysk end
		$msg_body .= __('estimate number','usces') . " : " . $order_id . "\r\n";
	}else{
		$msg_body = "\r\n\r\n\r\n" . __('** Article order contents **','usces') . "\r\n";
		$msg_body .= "******************************************************************\r\n";
		$msg_body .= apply_filters('usces_filter_order_confirm_mail_first', NULL, $data);
//20110118ysk start
		$msg_body .= usces_mail_custom_field_info( 'customer', 'name_pre', $order_id );
//20110118ysk end
		$msg_body .= __('Buyer','usces') . " : " . sprintf(__('Mr/Mrs %s', 'usces'), ($data['order_name1'] . ' ' . $data['order_name2'])) . "\r\n";
//20110118ysk start
		$msg_body .= usces_mail_custom_field_info( 'customer', 'name_after', $order_id );
//20110118ysk end
		$msg_body .= __('Order number','usces') . " : " . $order_id . "\r\n";
	}
	$msg_body .= __('Items','usces') . " : \r\n";

	$meisai = "";
	foreach ( (array)$cart as $cart_row ) {
		$post_id = $cart_row['post_id'];
		$sku = $cart_row['sku'];
		$quantity = $cart_row['quantity'];
		$options = $cart_row['options'];
		$itemCode = $usces->getItemCode($post_id);
		$itemName = $usces->getItemName($post_id);
		$cartItemName = $usces->getCartItemName($post_id, $sku);
		$skuPrice = $cart_row['price'];
//		$pictids = $usces->get_pictids($itemCode);
		if (!empty($options)) {
//			$optstr = implode(',', $options);
		} else { 
			$optstr =  '';
			$options =  array();
		}
		
		$meisai .= "------------------------------------------------------------------\r\n";
		$meisai .= "$cartItemName \r\n";
		if( is_array($options) && count($options) > 0 ){
			$optstr = '';
			foreach($options as $key => $value){
//20110629ysk start 0000190
				//if( !empty($key) )
				//	$meisai .= $key . ' : ' . urldecode($value) . "\r\n"; 
				if( !empty($key) ) {
					if(is_array($value)) {
						$c = '';
						$optstr .= $key . ' : ';
						foreach($value as $v) {
							$optstr .= $c.urldecode($v);
							$c = ', ';
						}
						$optstr .= "\r\n"; 
					} else {
						$optstr .= $key . ' : ' . urldecode($value) . "\r\n"; 
					}
				}
//20110629ysk end
			}
			$meisai .= apply_filters( 'usces_filter_option_adminmail', $optstr, $options);
		}
		$meisai .= __('Unit price','usces') . " ".usces_crform( $skuPrice, true, false, 'return' ) . __(' * ','usces') . $cart_row['quantity'] . "\r\n";
	}
	
	$meisai .= "=================================================================\r\n";
	$meisai .= __('total items','usces') . "    : " . usces_crform( $data['order_item_total_price'], true, false, 'return' ) . "\r\n";

	if ( $data['order_usedpoint'] != 0 )
		$meisai .= __('use of points','usces') . " : " . number_format($data['order_usedpoint']) . __('Points','usces') . "\r\n";
	if ( $data['order_discount'] != 0 )
		$meisai .= __('Special Price','usces') . "    : " . usces_crform( $data['order_discount'], true, false, 'return' ) . "\r\n";
	$meisai .= __('Shipping','usces') . "     : " . usces_crform( $data['order_shipping_charge'], true, false, 'return' ) . "\r\n";
	if ( $payment['settlement'] == 'COD' )
		$meisai .= apply_filters('usces_filter_cod_label', __('COD fee', 'usces')) . "  : " . usces_crform( $data['order_cod_fee'], true, false, 'return' ) . "\r\n";
	if ( !empty($usces->options['tax_rate']) )
		$meisai .= __('consumption tax','usces') . "    : " . usces_crform( $data['order_tax'], true, false, 'return' ) . "\r\n";
	$meisai .= "------------------------------------------------------------------\r\n";
	$meisai .= __('Payment amount','usces') . "  : " . usces_crform( $total_full_price, true, false, 'return' ) . "\r\n";
	$meisai .= "------------------------------------------------------------------\r\n";
	$meisai .= "(" . __('Currency', 'usces') . ' : ' . __(usces_crcode( 'return' ), 'usces') . ")\r\n\r\n";
	
	$msg_body .= apply_filters('usces_filter_order_confirm_mail_meisai', $meisai, $data);


	
	$msg_shipping .= __('** A shipping address **','usces') . "\r\n";
	$msg_shipping .= "******************************************************************\r\n";
	
	$msg_shipping .= uesces_get_mail_addressform( 'admin_mail', $deli, $order_id );

//20101208ysk start
	//$msg_shipping .= __('Delivery Time','usces') . " : " . $data['order_delivery_time'] . "\r\n";
	if ( $data['order_delidue_date'] == NULL || $data['order_delidue_date'] == '#none#' ) {
		$msg_shipping .= "\r\n";
	}else{
		$msg_shipping .= __('Shipping date', 'usces') . "  : " . $data['order_delidue_date'] . "\r\n";
		$msg_shipping .= __("* A shipment due date is a day to ship an article, and it's not the arrival day.", 'usces') . "\r\n";
		$msg_shipping .= "\r\n";
	}
	$deli_meth = (int)$data['order_delivery_method'];
	if( 0 <= $deli_meth ){
		$deli_index = $usces->get_delivery_method_index($deli_meth);
		$msg_shipping .= __('Delivery Method','usces') . " : " . $usces->options['delivery_method'][$deli_index]['name'] . "\r\n";
	}
	$msg_shipping .= __('Delivery date','usces') . " : " . $data['order_delivery_date'] . "\r\n";
	$msg_shipping .= __('Delivery Time','usces') . " : " . $data['order_delivery_time'] . "\r\n";
//20101208ysk end
	$msg_shipping .= "\r\n";
	$msg_body .= apply_filters('usces_filter_order_confirm_mail_shipping', $msg_shipping, $data);

//	$msg_body .= __('** For some region, to deliver the items in the morning is not possible.','usces') . "\r\n";
//	$msg_body .= __('** WE may not always be able to deliver the items on time which you desire.','usces') . " \r\n";
//	$msg_body .= "------------------------------------------------------------------\r\n\r\n";

	$msg_body .= __('** Payment method **','usces') . "\r\n";
	$msg_body .= "******************************************************************\r\n";
	$msg_body .= $payment['name']. "\r\n\r\n";
	if ( $payment['settlement'] == 'transferAdvance' || $payment['settlement'] == 'transferDeferred' ) {
		$transferee = __('Transfer','usces') . " : \r\n";
		$transferee .= $usces->options['transferee'] . "\r\n";
		$msg_body .= apply_filters('usces_filter_mail_transferee', $transferee);
		$msg_body .= "\r\n------------------------------------------------------------------\r\n\r\n";
//20101018ysk start
	} elseif($payment['settlement'] == 'acting_jpayment_conv') {
		$args = maybe_unserialize($usces->get_order_meta_value('settlement_args', $order_id));
		$msg_body .= __('決済番号', 'usces').' : '.$args['gid']."\r\n";
		$msg_body .= __('決済金額', 'usces').' : '.number_format($args['ta']).__('dollars','usces')."\r\n";
		$msg_body .= __('お支払先', 'usces').' : '.usces_get_conv_name($args['cv'])."\r\n";
		$msg_body .= __('コンビニ受付番号','usces').' : '.$args['no']."\r\n";
		if($args['cv'] != '030') {//ファミリーマート以外
			$msg_body .= __('コンビニ受付番号情報URL', 'usces').' : '.$args['cu']."\r\n";
		}
		$msg_body .= "\r\n------------------------------------------------------------------\r\n\r\n";
	} elseif($payment['settlement'] == 'acting_jpayment_bank') {
		$args = maybe_unserialize($usces->get_order_meta_value('settlement_args', $order_id));
		$msg_body .= __('決済番号', 'usces').' : '.$args['gid']."\r\n";
		$msg_body .= __('決済金額', 'usces').' : '.number_format($args['ta']).__('dollars','usces')."\r\n";
		$bank = explode('.', $args['bank']);
		$msg_body .= __('銀行コード','usces').' : '.$bank[0]."\r\n";
		$msg_body .= __('銀行名','usces').' : '.$bank[1]."\r\n";
		$msg_body .= __('支店コード','usces').' : '.$bank[2]."\r\n";
		$msg_body .= __('支店名','usces').' : '.$bank[3]."\r\n";
		$msg_body .= __('口座種別','usces').' : '.$bank[4]."\r\n";
		$msg_body .= __('口座番号','usces').' : '.$bank[5]."\r\n";
		$msg_body .= __('口座名義','usces').' : '.$bank[6]."\r\n";
		$msg_body .= __('支払期限','usces').' : '.substr($args['exp'], 0, 4).'年'.substr($args['exp'], 4, 2).'月'.substr($args['exp'], 6, 2)."日\r\n";
		$msg_body .= "\r\n------------------------------------------------------------------\r\n\r\n";
//20101018ysk end
	}
	
//20100818ysk start
	$msg_body .= usces_mail_custom_field_info( 'order', '', $order_id );
//20100818ysk end
	
	$msg_body .= "\r\n";
	$msg_body .= __('** Others / a demand **','usces') . "\r\n";
	$msg_body .= "******************************************************************\r\n";
	$msg_body .= $data['order_note'] . "\r\n\r\n";
//	$msg_body .= "------------------------------------------------------------------\r\n\r\n";
//	$msg_body .= "\r\n";

//	$msg_body .= __('Please inform it of any questions from [an inquiry].','usces') . "\r\n";
//	$msg_body .= "------------------------------------------------------------------\r\n\r\n";

	$msg_body .= apply_filters('usces_filter_order_confirm_mail_body', NULL, $data);

	switch ( $_POST['mode'] ) {
		case 'completionMail':
			$subject = $mail_data['title']['completionmail'];
			$message = $mail_data['header']['completionmail'] . $msg_body . $mail_data['footer']['completionmail'];
			break;
		case 'orderConfirmMail':
			$subject = $mail_data['title']['ordermail'];
			$message = $mail_data['header']['ordermail'] . $msg_body . $mail_data['footer']['ordermail'];
			break;
		case 'changeConfirmMail':
			$subject = $mail_data['title']['changemail'];
			$message = $mail_data['header']['changemail'] . $msg_body . $mail_data['footer']['changemail'];
			break;
		case 'receiptConfirmMail':
			$subject = $mail_data['title']['receiptmail'];
			$message = $mail_data['header']['receiptmail'] . $msg_body . $mail_data['footer']['receiptmail'];
			break;
		case 'mitumoriConfirmMail':
			$subject = $mail_data['title']['mitumorimail'];
			$message = $mail_data['header']['mitumorimail'] . $msg_body . $mail_data['footer']['mitumorimail'];
			break;
		case 'cancelConfirmMail':
			$subject = $mail_data['title']['cancelmail'];
			$message = $mail_data['header']['cancelmail'] . $msg_body . $mail_data['footer']['cancelmail'];
			break;
		case 'otherConfirmMail':
			$subject = $mail_data['title']['othermail'];
			$message = $mail_data['header']['othermail'] . $msg_body . $mail_data['footer']['othermail'];
			break;
	}
		
	return $message;

}

function usces_send_ordermail($order_id) {
	global $usces, $wpdb;
	
	$tableName = $wpdb->prefix . "usces_order";
	$query = $wpdb->prepare("SELECT * FROM $tableName WHERE ID = %d", $order_id);
	$data = $wpdb->get_row( $query, ARRAY_A );

	$cart = $usces->cart->get_cart();
	$entry = $usces->cart->get_entry();
	$mail_data = $usces->options['mail_data'];
	$payment = $usces->getPayments( $entry['order']['payment_name'] );
	$res = false;

	$msg_body = "\r\n\r\n\r\n" . __('** content of ordered items **','usces') . "\r\n";
	$msg_body .= "******************************************************************\r\n";
	$msg_body .= apply_filters('usces_filter_send_order_mail_first', NULL, $data);
//20110118ysk start
	$msg_body .= usces_mail_custom_field_info( 'customer', 'name_pre', $order_id );
//20110118ysk end
	$msg_body .= __('Buyer','usces') . " : " . sprintf(__('Mr/Mrs %s', 'usces'), ($entry['customer']['name1'] . ' ' . $entry['customer']['name2'])) . "\r\n";
//20110118ysk start
	$msg_body .= usces_mail_custom_field_info( 'customer', 'name_after', $order_id );
//20110118ysk end
	$msg_body .= __('Order number','usces') . " : " . $order_id . "\r\n";
	$msg_body .= __('Items','usces') . " : \r\n";
	
	$meisai = "";
	foreach ( $cart as $cart_row ) {
		$post_id = $cart_row['post_id'];
		$sku = $cart_row['sku'];
		$quantity = $cart_row['quantity'];
		$options = $cart_row['options'];
		$itemCode = $usces->getItemCode($post_id);
		$itemName = $usces->getItemName($post_id);
		$cartItemName = $usces->getCartItemName($post_id, $sku);
		$skuPrice = $cart_row['price'];
//		$pictids = $usces->get_pictids($itemCode);
		if (!empty($options)) {
//			$optstr = implode(',', $options);
		} else { 
			$optstr =  '';
			$options =  array();
		}
		
		$meisai .= "------------------------------------------------------------------\r\n";
		$meisai .= "$cartItemName \r\n";
		if( is_array($options) && count($options) > 0 ){
			$optstr = '';
			foreach($options as $key => $value){
//20110629ysk start 0000190
				//if( !empty($key) )
				//	$meisai .= $key . ' : ' . urldecode($value) . "\r\n"; 
				if( !empty($key) ) {
					if(is_array($value)) {
						$c = '';
						$optstr .= $key. ' : ';
						foreach($value as $v) {
							$optstr .= $c.urldecode($v);
							$c = ', ';
						}
						$optstr .= "\r\n"; 
					} else {
						$optstr .= $key . ' : ' . urldecode($value) . "\r\n"; 
					}
				}
//20110629ysk end
			}
			$meisai .= apply_filters( 'usces_filter_option_ordermail', $optstr, $options);
		}
		$meisai .= __('Unit price','usces') . " ".usces_crform( $skuPrice, true, false, 'return' ) . __(' * ','usces') . $cart_row['quantity'] . "\r\n";
	}
	$meisai .= "=================================================================\r\n";
	$meisai .= __('total items','usces') . "    : " . usces_crform( $entry['order']['total_items_price'], true, false, 'return' ) . "\r\n";

	if ( $entry['order']['usedpoint'] != 0 )
		$meisai .= __('use of points','usces') . " : " . number_format($entry['order']['usedpoint']) . __('Points','usces') . "\r\n";
	if ( $data['order_discount'] != 0 )
		$meisai .= __('Special Price','usces') . "    : " . usces_crform( $entry['order']['discount'], true, false, 'return' ) . "\r\n";
	$meisai .= __('Shipping','usces') . "     : " . usces_crform( $entry['order']['shipping_charge'], true, false, 'return' ) . "\r\n";
	if ( $payment['settlement'] == 'COD' )
		$meisai .= apply_filters('usces_filter_cod_label', __('COD fee', 'usces')) . "  : " . usces_crform( $entry['order']['cod_fee'], true, false, 'return' ) . "\r\n";
	if ( !empty($usces->options['tax_rate']) )
		$meisai .= __('consumption tax','usces') . "     : " . usces_crform( $entry['order']['tax'], true, false, 'return' ) . "\r\n";
	$meisai .= "------------------------------------------------------------------\r\n";
	$meisai .= __('Payment amount','usces') . "  : " . usces_crform( $entry['order']['total_full_price'], true, false, 'return' ) . "\r\n";
	$meisai .= "------------------------------------------------------------------\r\n";
	$meisai .= "(" . __('Currency', 'usces') . ' : ' . __(usces_crcode( 'return' ), 'usces') . ")\r\n\r\n";

	$msg_body .= apply_filters('usces_filter_send_order_mail_meisai', $meisai, $data);


	
	$msg_shipping .= __('** A shipping address **','usces') . "\r\n";
	$msg_shipping .= "******************************************************************\r\n";
	
	$msg_shipping .= uesces_get_mail_addressform( 'order_mail', $entry, $order_id );

//20101208ysk start
	//$msg_shipping .= __('Delivery Time','usces') . " : " . $entry['order']['delivery_time'] . "\r\n";
	$deli_meth = (int)$entry['order']['delivery_method'];
	if( 0 <= $deli_meth ){
		$deli_index = $usces->get_delivery_method_index($deli_meth);
		$msg_shipping .= __('Delivery Method','usces') . " : " . $usces->options['delivery_method'][$deli_index]['name'] . "\r\n";
	}
	$msg_shipping .= __('Delivery date','usces') . " : " . $entry['order']['delivery_date'] . "\r\n";
	$msg_shipping .= __('Delivery Time','usces') . " : " . $entry['order']['delivery_time'] . "\r\n";
//20101208ysk end
//	$msg_body .= "------------------------------------------------------------------\r\n";
//	$msg_body .= __('** For some region, to deliver the items in the morning is not possible.','usces') . "\r\n";
//	$msg_body .= " " . __('** WE may not always be able to deliver the items on time which you desire.','usces') . "\r\n";
//	$msg_body .= "------------------------------------------------------------------\r\n\r\n";
	$msg_shipping .= "\r\n";
	$msg_body .= apply_filters('usces_filter_send_order_mail_shipping', $msg_shipping, $data);

	$msg_body .= __('** Payment method **','usces') . "\r\n";
	$msg_body .= "******************************************************************\r\n";
	$msg_body .= $payment['name'] . usces_payment_detail($entry) . "\r\n\r\n";
	if ( $payment['settlement'] == 'transferAdvance' || $payment['settlement'] == 'transferDeferred' ) {
		$transferee = __('Transfer','usces') . " : \r\n";
		$transferee .= $usces->options['transferee'] . "\r\n";
		$msg_body .= apply_filters('usces_filter_mail_transferee', $transferee);
		$msg_body .= "\r\n------------------------------------------------------------------\r\n\r\n";
//20101018ysk start
	} elseif($payment['settlement'] == 'acting_jpayment_conv') {
		$args = maybe_unserialize($usces->get_order_meta_value('settlement_args', $order_id));
		$msg_body .= __('決済番号', 'usces').' : '.$args['gid']."\r\n";
		$msg_body .= __('決済金額', 'usces').' : '.number_format($args['ta']).__('dollars','usces')."\r\n";
		$msg_body .= __('お支払先', 'usces').' : '.usces_get_conv_name($args['cv'])."\r\n";
		$msg_body .= __('コンビニ受付番号','usces').' : '.$args['no']."\r\n";
		if($args['cv'] != '030') {//ファミリーマート以外
			$msg_body .= __('コンビニ受付番号情報URL', 'usces').' : '.$args['cu']."\r\n";
		}
		$msg_body .= "\r\n------------------------------------------------------------------\r\n\r\n";
	} elseif($payment['settlement'] == 'acting_jpayment_bank') {
		$args = maybe_unserialize($usces->get_order_meta_value('settlement_args', $order_id));
		$msg_body .= __('決済番号', 'usces').' : '.$args['gid']."\r\n";
		$msg_body .= __('決済金額', 'usces').' : '.number_format($args['ta']).__('dollars','usces')."\r\n";
		$bank = explode('.', $args['bank']);
		$msg_body .= __('銀行コード','usces').' : '.$bank[0]."\r\n";
		$msg_body .= __('銀行名','usces').' : '.$bank[1]."\r\n";
		$msg_body .= __('支店コード','usces').' : '.$bank[2]."\r\n";
		$msg_body .= __('支店名','usces').' : '.$bank[3]."\r\n";
		$msg_body .= __('口座種別','usces').' : '.$bank[4]."\r\n";
		$msg_body .= __('口座番号','usces').' : '.$bank[5]."\r\n";
		$msg_body .= __('口座名義','usces').' : '.$bank[6]."\r\n";
		$msg_body .= __('支払期限','usces').' : '.substr($args['exp'], 0, 4).'年'.substr($args['exp'], 4, 2).'月'.substr($args['exp'], 6, 2)."日\r\n";
		$msg_body .= "\r\n------------------------------------------------------------------\r\n\r\n";
//20101018ysk end
	}

//20100818ysk start
	$msg_body .= usces_mail_custom_field_info( 'order', '', $order_id );
//20100818ysk end

	$msg_body .= "\r\n";
	$msg_body .= __('** Others / a demand **','usces') . "\r\n";
	$msg_body .= "******************************************************************\r\n";
	$msg_body .= $entry['order']['note'] . "\r\n\r\n";
//	$msg_body .= "------------------------------------------------------------------\r\n\r\n";
//	$msg_body .= "\r\n";

//	$msg_body .= __('I will inform it of shipment completion by an email.','usces') . "\r\n";
//	$msg_body .= __('Please inform it of any questions from [an inquiry].','usces') . "\r\n";
//	$msg_body .= "------------------------------------------------------------------\r\n\r\n";

	$msg_body .= apply_filters('usces_filter_send_order_mail_body', NULL, $data);

	$subject = $mail_data['title']['thankyou'];
	$message = $mail_data['header']['thankyou'] . $msg_body . $mail_data['footer']['thankyou'];
//var_dump($msg_body);exit;
	$confirm_para = array(
			'to_name' => sprintf(__('Mr/Mrs %s', 'usces'), ($entry["customer"]["name1"] . ' ' . $entry["customer"]["name2"])),
			'to_address' => $entry['customer']['mailaddress1'], 
			'from_name' => get_bloginfo('name'),
			'from_address' => $usces->options['sender_mail'],
			'return_path' => $usces->options['error_mail'],
			'subject' => $subject,
			'message' => $message
			);

	if ( usces_send_mail( $confirm_para ) ) {
	
		$subject = $mail_data['title']['order'];
		$message = $mail_data['header']['order'] . $msg_body
		 . $mail_data['footer']['order']
		 . "\n----------------------------------------------------\n"
		 . "REMOTE_ADDR : " . $_SERVER['REMOTE_ADDR']
		 . "\n----------------------------------------------------\n";
		
		$order_para = array(
				'to_name' => __('An order email','usces'),
				'to_address' => $usces->options['order_mail'], 
				'from_name' => sprintf(__('Mr/Mrs %s', 'usces'), ($entry["customer"]["name1"] . ' ' . $entry["customer"]["name2"])),
				'from_address' => $entry['customer']['mailaddress1'],
				'return_path' => $usces->options['error_mail'],
				'subject' => $subject,
				'message' => $message
				);
		
		$res = usces_send_mail( $order_para );
	
	}
	
	return $res;

}


function usces_send_inquirymail() {
	global $usces;
	$res = false;
	$mail_data = $usces->options['mail_data'];
	$inq_name = trim($_POST["inq_name"]);
	$inq_contents = trim($_POST["inq_contents"]);
	$inq_mailaddress = trim($_POST["inq_mailaddress"]);
	$reserve = '';
	if(isset($_POST['reserve'])){
		foreach($_POST['reserve'] as $key => $value){
			$reserve .= $key . " : " . $value . "\r\n";
		}
	}

	$subject =  $mail_data['title']['inquiry'];
	$message  = apply_filters( 'usces_filter_inquiry_header', $mail_data['header']['inquiry'], $inq_name, $inq_mailaddress ) . "\r\n\r\n";
	$message .= apply_filters( 'usces_filter_inquiry_reserve', $reserve, $inq_name, $inq_mailaddress );
	$message .= apply_filters( 'usces_filter_inq_contents', $inq_contents, $inq_name, $inq_mailaddress ) . "\r\n\r\n";
	$message .= apply_filters( 'usces_filter_inq_footer', $mail_data['footer']['inquiry'], $inq_name, $inq_mailaddress );
	do_action( 'usces_action_presend_inquiry_mail', $message, $inq_name, $inq_mailaddress );
	
	$para1 = array(
			'to_name' => sprintf(__('Mr/Mrs %s', 'usces'), $inq_name),
			'to_address' => $inq_mailaddress, 
			'from_name' => get_bloginfo('name'),
			'from_address' => $usces->options['sender_mail'],
			'return_path' => $usces->options['error_mail'],
			'subject' => $subject,
			'message' => $message
			);
			
		$res0 = usces_send_mail( $para1 );
	if ( $res0 ) {
	
		$subject =  __('** An inquiry **','usces').'('.$inq_name.')';
		$message = $reserve . $_POST['inq_contents'] . "\r\n"
		 . "\n----------------------------------------------------\n"
		 . "REMOTE_ADDR : " . $_SERVER['REMOTE_ADDR']
		 . "\n----------------------------------------------------\n";
	
		$para2 = array(
				'to_name' => __('An inquiry email','usces'),
				'to_address' => $usces->options['inquiry_mail'], 
				'from_name' => sprintf(__('Mr/Mrs %s', 'usces'), $inq_name),
				'from_address' => $inq_mailaddress,
				'return_path' => $usces->options['error_mail'],
				'subject' => $subject,
				'message' => $message
				);
		sleep(1);
		$res = usces_send_mail( $para2 );
	
	}
	
	return $res;

}

function usces_send_regmembermail($user) {
	global $usces;
	$res = false;
	$mail_data = $usces->options['mail_data'];

	$subject =  $mail_data['title']['membercomp'];
	$message = $mail_data['header']['membercomp'] . $mail_data['footer']['membercomp'];
	$message = apply_filters('usces_filter_send_regmembermail_message', $message, $user);
	
	$name = trim($user['name1']) . trim($user['name2']);
	$mailaddress1 = trim($user['mailaddress1']);

	$para1 = array(
			'to_name' => sprintf(__('Mr/Mrs %s', 'usces'), $name),
			'to_address' => $mailaddress1, 
			'from_name' => get_bloginfo('name'),
			'from_address' => $usces->options['sender_mail'],
			'return_path' => $usces->options['error_mail'],
			'subject' => $subject,
			'message' => $message
			);

	$res = usces_send_mail( $para1 );
	
	return $res;

}

function usces_lostmail($url) {
	global $usces;
	$res = false;

	$mail_data = $usces->options['mail_data'];
	$subject = __('Change password','usces');
	$message = __('Please, click the following URL, and please change a password.','usces') . "\n\r\n\r\n\r"
			. $url . "\n\r\n\r\n\r"
			. "-----------------------------------------------------\n\r"
			. __('If you have not requested this email please kindly ignore and delete it.','usces') . "\n\r"
			. "-----------------------------------------------------\n\r\n\r\n\r"
			. apply_filters('usces_filter_lostmail_footer', $mail_data['footer']['othermail']);

	$para1 = array(
			'to_name' => sprintf(__('Mr/Mrs %s', 'usces'), $_SESSION["usces_lostmail"]),
			'to_address' => $_SESSION["usces_lostmail"], 
			'from_name' => get_bloginfo('name'),
			'from_address' => $usces->options['sender_mail'],
			'return_path' => $usces->options['error_mail'],
			'subject' => $subject,
			'message' => $message
			);

	$res = usces_send_mail( $para1 );
	
	if($res === false) {
		$usces->error_message = __('Error: I was not able to transmit an email.','usces');
		$page = 'lostmemberpassword';
	} else {
		$page = 'lostcompletion';
	}

	return $page;

}

//20100818ysk start
function usces_mail_custom_field_info( $custom_field, $position, $id ) {
	global $usces;

	$msg_body = '';
	switch($custom_field) {
	case 'order':
		$field = 'usces_custom_order_field';
		$cs = 'csod_';
		break;
	case 'customer':
		$field = 'usces_custom_customer_field';
		$cs = 'cscs_';
		break;
	case 'delivery':
		$field = 'usces_custom_delivery_field';
		$cs = 'csde_';
		break;
	case 'member':
		$field = 'usces_custom_member_field';
		$cs = 'csmb_';
		break;
	default:
		return $msg_body;
	}

	$meta = usces_has_custom_field_meta($custom_field);

	if(!empty($meta) and is_array($meta)) {
		$keys = array_keys($meta);
		switch($custom_field) {
		case 'order':
			$msg_body .= "\r\n";
			$msg_body .= "******************************************************************\r\n";
			foreach($keys as $key) {
				$value = maybe_unserialize($usces->get_order_meta_value($cs.$key, $id));
				if(is_array($value)) {
					$concatval = '';
					$c = '';
					foreach($value as $v) {
						$concatval .= $c.$v;
						$c = ', ';
					}
					$value = $concatval;
				}
				$msg_body .= $meta[$key]['name']."  : ".$value."\r\n";
			}
			$msg_body .= "******************************************************************\r\n";
			break;

		case 'customer':
		case 'delivery':
			foreach($keys as $key) {
				if($meta[$key]['position'] == $position) {
					$value = maybe_unserialize($usces->get_order_meta_value($cs.$key, $id));
					if(is_array($value)) {
						$concatval = '';
						$c = '';
						foreach($value as $v) {
							$concatval .= $c.$v;
							$c = ', ';
						}
						$value = $concatval;
					}
					$msg_body .= $meta[$key]['name']."  : ".$value."\r\n";
				}
			}
			break;

		case 'member':
			foreach($keys as $key) {
				if($meta[$key]['position'] == $position) {
					$value = maybe_unserialize($usces->get_member_meta_value($cs.$key, $id));
					if(is_array($value)) {
						$concatval = '';
						$c = '';
						foreach($value as $v) {
							$concatval .= $c.$v;
							$c = ', ';
						}
						$value = $concatval;
					}
					$msg_body .= $meta[$key]['name']."  : ".$value."\r\n";
				}
			}
			break;
		}
	}

	return $msg_body;
}
//20100818ysk end

//function usces_send_receipted_mail( $order_id, $acting ) {
//	global $usces;
//	$res = false;
//
//	$subject = __('Change password','usces');
//	$message = __('Please, click the following URL, and please change a password.','usces') . "\n\r\n\r\n\r"
//			. $url . "\n\r\n\r\n\r"
//			. "-----------------------------------------------------\n\r"
//			. __('I seem to have you cancel it when the body does not have memorizing to this email.','usces') . "\n\r"
//			. "-----------------------------------------------------\n\r\n\r\n\r"
//			. $usces->options['mail_data']['footer']['footerlogo'];
//
//	$para1 = array(
//			'to_name' => sprintf(__('Mr/Mrs %s', 'usces'), $_SESSION["usces_lostmail"]),
//			'to_address' => $_SESSION["usces_lostmail"], 
//			'from_name' => get_bloginfo('name'),
//			'from_address' => $usces->options['sender_mail'],
//			'return_path' => $usces->options['error_mail'],
//			'subject' => $subject,
//			'message' => $message
//			);
//
//	$res = usces_send_mail( $para1 );
//	
//	if($res === false) {
//		$usces->error_message = __('Error: I was not able to transmit an email.','usces');
//		$page = 'lostmemberpassword';
//	} else {
//		$page = 'lostcompletion';
//	}
//
//	return $page;
//
//}

function usces_send_mail( $para ) {
	global $usces;

	$header = "From: " . html_entity_decode($para['from_name'], ENT_QUOTES) . " <{$para['from_address']}>\r\n"
//			."To: " . mb_convert_encoding($para['to_name'], "SJIS") . " <{$para['to_address']}>\r\n"
			."Return-Path: {$para['return_path']}\r\n";

	$subject = html_entity_decode($para['subject'], ENT_QUOTES);
	$message = $para['message'];
	
	ini_set( "SMTP", "{$usces->options['smtp_hostname']}" );
	if( !ini_get( "smtp_port" ) ){
		ini_set( "smtp_port", 25 );
	}
	ini_set( "sendmail_from", "" );
	
	if( is_email( $para['to_address'] ) ){
		$res = @wp_mail( $para['to_address'] , $subject , $message, $header );
	}else{
		$res = false;
	}
	
	return $res;

}


function usces_reg_orderdata( $results = array() ) {
	global $wpdb, $usces;
//	$wpdb->show_errors();
	
	$options = get_option('usces');
	$cart = $usces->cart->get_cart();
	$entry = $usces->cart->get_entry();
	if( empty($cart) ){
		usces_log('reg_orderdata : Session is empty.', 'database_error.log');
		return 0;
	}
	
	$charging_type = $usces->getItemChargingType($cart[0]['post_id']);

	$item_total_price = $usces->get_total_price( $cart );
	$member = $usces->get_member();
	$order_table_name = $wpdb->prefix . "usces_order";
	$order_table_meta_name = $wpdb->prefix . "usces_order_meta";
	$member_table_name = $wpdb->prefix . "usces_member";
	$set = $usces->getPayments( $entry['order']['payment_name'] );
	if( 'continue' == $charging_type ){
		//$status = 'continuation';
		$order_modified = substr(get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 0, 10);
	}else{
//20101018ysk start
		//$status = ( $set['settlement'] == 'transferAdvance' || $set['settlement'] == 'transferDeferred' || $set['settlement'] == 'acting_remise_conv' || $set['settlement'] == 'acting_zeus_bank' || $set['settlement'] == 'acting_zeus_conv' ) ? 'noreceipt' : '';
		$status = ( $set['settlement'] == 'transferAdvance' || $set['settlement'] == 'transferDeferred' || $set['settlement'] == 'acting_remise_conv' || $set['settlement'] == 'acting_zeus_bank' || $set['settlement'] == 'acting_zeus_conv' || $set['settlement'] == 'acting_jpayment_conv' || $set['settlement'] == 'acting_jpayment_bank' ) ? 'noreceipt' : '';
//20101018ysk end
		$order_modified = NULL;
	}
	$payments = $usces->getPayments($entry['order']['payment_name']);
	if($results['payment_status'] != 'Completed' && $payments['module'] == 'paypal.php') $status = 'pending';
	
	if( (empty($entry['customer']['name1']) && empty($entry['customer']['name2'])) || empty($entry['customer']['mailaddress1']) || empty($entry) || empty($cart) ) return '1';
	
//20101208ysk start
/*
	$query = $wpdb->prepare(
				"INSERT INTO $order_table_name (
					`mem_id`, `order_email`, `order_name1`, `order_name2`, `order_name3`, `order_name4`, 
					`order_zip`, `order_pref`, `order_address1`, `order_address2`, `order_address3`, 
					`order_tel`, `order_fax`, `order_delivery`, `order_cart`, `order_note`, `order_delivery_method`, `order_delivery_time`, 
					`order_payment_name`, `order_condition`, `order_item_total_price`, `order_getpoint`, `order_usedpoint`, `order_discount`, 
					`order_shipping_charge`, `order_cod_fee`, `order_tax`, `order_date`, `order_modified`, `order_status`) 
				VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d, %s, %s, %s)", 
					$member['ID'], 
					$entry['customer']['mailaddress1'], 
					$entry['customer']['name1'], 
					$entry['customer']['name2'], 
					$entry['customer']['name3'], 
					$entry['customer']['name4'], 
					$entry['customer']['zipcode'], 
					$entry['customer']['pref'], 
					$entry['customer']['address1'], 
					$entry['customer']['address2'], 
					$entry['customer']['address3'], 
					$entry['customer']['tel'], 
					$entry['customer']['fax'], 
					serialize($entry['delivery']), 
					serialize($cart), 
					$entry['order']['note'], 
					$entry['order']['delivery_method'], 
					$entry['order']['delivery_time'], 
					$entry['order']['payment_name'], 
					serialize($entry['condition']), 
					$item_total_price, 
					$entry['order']['getpoint'], 
					$entry['order']['usedpoint'], 
					$entry['order']['discount'], 
					$entry['order']['shipping_charge'], 
					$entry['order']['cod_fee'], 
					$entry['order']['tax'], 
					get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 
					$order_modified, 
					$status
				);
*/
	$query = $wpdb->prepare(
				"INSERT INTO $order_table_name (
					`mem_id`, `order_email`, `order_name1`, `order_name2`, `order_name3`, `order_name4`, 
					`order_zip`, `order_pref`, `order_address1`, `order_address2`, `order_address3`, 
					`order_tel`, `order_fax`, `order_delivery`, `order_cart`, `order_note`, `order_delivery_method`, `order_delivery_date`, `order_delivery_time`, 
					`order_payment_name`, `order_condition`, `order_item_total_price`, `order_getpoint`, `order_usedpoint`, `order_discount`, 
					`order_shipping_charge`, `order_cod_fee`, `order_tax`, `order_date`, `order_modified`, `order_status`) 
				VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s, %s, %s, %s, %f, %d, %d, %f, %f, %f, %f, %s, %s, %s)", 
					$member['ID'], 
					$entry['customer']['mailaddress1'], 
					$entry['customer']['name1'], 
					$entry['customer']['name2'], 
					$entry['customer']['name3'], 
					$entry['customer']['name4'], 
					$entry['customer']['zipcode'], 
					$entry['customer']['pref'], 
					$entry['customer']['address1'], 
					$entry['customer']['address2'], 
					$entry['customer']['address3'], 
					$entry['customer']['tel'], 
					$entry['customer']['fax'], 
					serialize($entry['delivery']), 
					serialize($cart), 
					$entry['order']['note'], 
					$entry['order']['delivery_method'], 
					$entry['order']['delivery_date'], 
					$entry['order']['delivery_time'], 
					$entry['order']['payment_name'], 
					serialize($entry['condition']), 
					$item_total_price, 
					$entry['order']['getpoint'], 
					$entry['order']['usedpoint'], 
					$entry['order']['discount'], 
					$entry['order']['shipping_charge'], 
					$entry['order']['cod_fee'], 
					$entry['order']['tax'], 
					get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 
					$order_modified, 
					$status
				);
//20101208ysk end

	$res = $wpdb->query( $query );
	usces_log('reg_orderdata : ' . $wpdb->last_error, 'database_error.log');

	if( $res === false){
		$order_id = false;
	}else{
		$order_id = $wpdb->insert_id;
	}

	if ( !$order_id ) :
		
		usces_log('reg_error_entry : '.print_r($entry, true), 'acting_transaction.log');
		return false;
		
	else :
	
		$usces->cart->set_order_entry( array('ID' => $order_id) );
		$usces->set_order_meta_value('customer_country', $entry['customer']['country'], $order_id);
	
		if ( $member['ID'] && 'activate' == $options['membersystem_state'] && 'activate' == $options['membersystem_point'] ) {
		
			$mquery = $wpdb->prepare(
						"UPDATE $member_table_name SET mem_point = (mem_point + %d - %d) WHERE ID = %d", 
						$entry['order']['getpoint'], $entry['order']['usedpoint'], $member['ID']);
		
			$wpdb->query( $mquery );
			$mquery = $wpdb->prepare("SELECT mem_point FROM $member_table_name WHERE ID = %d", $member['ID']);
			$point = $wpdb->get_var( $mquery );
			$_SESSION['usces_member']['point'] = $point;
		}
	
		if ( !empty($entry['reserve']) ) {
			foreach ( $entry['reserve'] as $key => $value ) {
				if ( is_array($value) )
					 $value = serialize($value);
				$mquery = $wpdb->prepare("INSERT INTO $order_table_meta_name ( order_id, meta_key, meta_value ) 
											VALUES (%d, %s, %s)", $order_id, $key, $value);
				$wpdb->query( $mquery );
			}
		}
	
		if ( !preg_match('/pending|noreceipt/', $status) ) {
			$value = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
			$usces->set_order_meta_value('receipted_date', $value, $order_id);
		}
	
//20100818ysk start
		if( !empty($entry['custom_order']) ) {
			foreach( $entry['custom_order'] as $key => $value ) {
				$csod_key = 'csod_'.$key;
				if( is_array($value) ) 
					 $value = serialize($value);
				$usces->set_order_meta_value($csod_key, $value, $order_id);
			}
		}
		if( !empty($entry['custom_customer']) ) {
			foreach( $entry['custom_customer'] as $key => $value ) {
				$cscs_key = 'cscs_'.$key;
				if( is_array($value) ) 
					 $value = serialize($value);
				$usces->set_order_meta_value($cscs_key, $value, $order_id);
			}
		}
		if( !empty($entry['custom_delivery']) ) {
			foreach( $entry['custom_delivery'] as $key => $value ) {
				$csde_key = 'csde_'.$key;
				if( is_array($value) ) 
					 $value = serialize($value);
				$usces->set_order_meta_value($csde_key, $value, $order_id);
			}
		}
//20100818ysk end

		if ( isset($_REQUEST['X-S_TORIHIKI_NO']) ) {
			$mquery = $wpdb->prepare("INSERT INTO $order_table_meta_name ( order_id, meta_key, meta_value ) 
										VALUES (%d, %s, %s)", $order_id, 'settlement_id', $_REQUEST['X-S_TORIHIKI_NO']);
			$wpdb->query( $mquery );
			$limitofcard = substr($_REQUEST['X-EXPIRE'], 0, 2) . '/' . substr($_REQUEST['X-EXPIRE'], 2, 2);
			$usces->set_member_meta_value('partofcard', $_REQUEST['X-PARTOFCARD']);
			$usces->set_member_meta_value('limitofcard', $limitofcard);
			if ( isset($_REQUEST['X-AC_MEMBERID']) ) {
				$mquery = $wpdb->prepare("INSERT INTO $order_table_meta_name ( order_id, meta_key, meta_value ) 
											VALUES (%d, %s, %s)", $order_id, $_REQUEST['X-AC_MEMBERID'], 'continuation');
				$wpdb->query( $mquery );
				$usces->set_member_meta_value('continue_memberid', $_REQUEST['X-AC_MEMBERID']);
			}
		}
	
//20101018ysk start
		if(isset($_REQUEST['acting']) && ('jpayment_conv' == $_REQUEST['acting'] || 'jpayment_bank' == $_REQUEST['acting'])) {
			$usces->set_order_meta_value('settlement_id', $_GET['cod'], $order_id);
			foreach($_GET as $key => $value) {
				$data[$key] = mysql_real_escape_string($value);
			}
			$usces->set_order_meta_value('acting_'.$_REQUEST['acting'], serialize($data), $order_id);
		}
//20101018ysk end
		if( isset($_REQUEST['acting']) && isset($_REQUEST['acting_return']) && isset($_REQUEST['trans_code']) && 'epsilon' == $_REQUEST['acting'] ) {
			$usces->set_order_meta_value('settlement_id', $_GET['trans_code'], $order_id);
		}

		foreach($cart as $cartrow){
			$zaikonum = $usces->getItemZaikoNum( $cartrow['post_id'], $cartrow['sku'] );
			if($zaikonum == '') continue;
			$zaikonum = $zaikonum - $cartrow['quantity'];
			$usces->updateItemZaikoNum( $cartrow['post_id'], $cartrow['sku'], $zaikonum );
			if($zaikonum <= 0) $usces->updateItemZaiko( $cartrow['post_id'], $cartrow['sku'], 2 );
		}
		
		$args = array('cart'=>$cart, 'entry'=>$entry, 'order_id'=>$order_id, 'member_id'=>$member['ID'], 'payments'=>$payments, 'charging_type'=>$charging_type);
		do_action('usces_action_reg_orderdata', $args);
	
	endif;
	
	return $order_id;
	
}

function usces_new_orderdata() {
	global $wpdb, $usces;
	
	$usces->cart->crear_cart();
	$cart = $usces->cart->get_cart();
	$item_total_price = $usces->get_total_price( $cart );
	$entry = $usces->cart->get_entry();
	$member_id = $usces->get_memberid_by_email($_POST['customer']['mailaddress']);
	$order_table_name = $wpdb->prefix . "usces_order";
	$order_table_meta_name = $wpdb->prefix . "usces_order_meta";
	$member_table_name = $wpdb->prefix . "usces_member";
	$set = $usces->getPayments( $_POST['offer']['payment_name'] );
	//$status = ( $set['settlement'] == 'transferAdvance' || $set['settlement'] == 'transferDeferred' ) ? 'noreceipt,' : '';
	$status = 'noreceipt,';
	$status .= ( $_POST['offer']['taio'] != '' ) ? $_POST['offer']['taio'].',' : '';
	$status .= $_POST['offer']['admin'];
	$order_conditions = $usces->get_condition();

//20101208ysk start
/*
	$query = $wpdb->prepare(
				"INSERT INTO $order_table_name (
					`mem_id`, `order_email`, `order_name1`, `order_name2`, `order_name3`, `order_name4`, 
					`order_zip`, `order_pref`, `order_address1`, `order_address2`, `order_address3`, 
					`order_tel`, `order_fax`, `order_delivery`, `order_cart`, `order_note`, `order_delivery_method`, `order_delivery_time`, 
					`order_payment_name`, `order_condition`, `order_item_total_price`, `order_getpoint`, `order_usedpoint`, `order_discount`, 
					`order_shipping_charge`, `order_cod_fee`, `order_tax`, `order_date`, `order_modified`, `order_status`) 
				VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d, %s, %s, %s)", 
					$member['ID'], 
					$_POST['customer']['mailaddress'], 
					$_POST['customer']['name1'], 
					$_POST['customer']['name2'], 
					$_POST['customer']['name3'], 
					$_POST['customer']['name4'], 
					$_POST['customer']['zipcode'], 
					$_POST['customer']['pref'], 
					$_POST['customer']['address1'], 
					$_POST['customer']['address2'], 
					$_POST['customer']['address3'], 
					$_POST['customer']['tel'], 
					$_POST['customer']['fax'], 
					serialize($_POST['delivery']), 
					serialize($cart), 
					$_POST['offer']['note'], 
					$_POST['offer']['delivery_method'], 
					$_POST['offer']['delivery_time'], 
					$_POST['offer']['payment_name'], 
					serialize($order_conditions), 
					$item_total_price, 
					$_POST['offer']['getpoint'], 
					$_POST['offer']['usedpoint'], 
					$_POST['offer']['discount'], 
					$_POST['offer']['shipping_charge'], 
					$_POST['offer']['cod_fee'], 
					$_POST['offer']['tax'], 
					get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 
					null, 
					$status
				);
*/
	$query = $wpdb->prepare(
				"INSERT INTO $order_table_name (
					`mem_id`, `order_email`, `order_name1`, `order_name2`, `order_name3`, `order_name4`, 
					`order_zip`, `order_pref`, `order_address1`, `order_address2`, `order_address3`, 
					`order_tel`, `order_fax`, `order_delivery`, `order_cart`, `order_note`, `order_delivery_method`, `order_delivery_date`, `order_delivery_time`, 
					`order_payment_name`, `order_condition`, `order_item_total_price`, `order_getpoint`, `order_usedpoint`, `order_discount`, 
					`order_shipping_charge`, `order_cod_fee`, `order_tax`, `order_date`, `order_modified`, `order_status`) 
				VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s, %s, %s, %s, %f, %d, %d, %f, %f, %f, %f, %s, %s, %s)", 
					$member_id, 
					$_POST['customer']['mailaddress'], 
					$_POST['customer']['name1'], 
					$_POST['customer']['name2'], 
					$_POST['customer']['name3'], 
					$_POST['customer']['name4'], 
					$_POST['customer']['zipcode'], 
					$_POST['customer']['pref'], 
					$_POST['customer']['address1'], 
					$_POST['customer']['address2'], 
					$_POST['customer']['address3'], 
					$_POST['customer']['tel'], 
					$_POST['customer']['fax'], 
					serialize($_POST['delivery']), 
					serialize($cart), 
					$_POST['offer']['note'], 
					$_POST['offer']['delivery_method'], 
					$_POST['offer']['delivery_date'], 
					$_POST['offer']['delivery_time'], 
					$_POST['offer']['payment_name'], 
					serialize($order_conditions), 
					$item_total_price, 
					$_POST['offer']['getpoint'], 
					$_POST['offer']['usedpoint'], 
					$_POST['offer']['discount'], 
					$_POST['offer']['shipping_charge'], 
					$_POST['offer']['cod_fee'], 
					$_POST['offer']['tax'], 
					get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 
					null, 
					$status
				);
//20101208ysk end

	$res = $wpdb->query( $query );
//	$wpdb->print_error();
//	echo $query;
//	exit;
	$order_id = $wpdb->insert_id;
	$_REQUEST['order_id'] = $wpdb->insert_id;
//	if ( !$order_id ) :
//	
//		return false;
//		
//	else :
//	
//		if ( $member['ID'] ) {
//		
//			$mquery = $wpdb->prepare(
//						"UPDATE $member_table_name SET mem_point = (mem_point + %d - %d) WHERE ID = %d", 
//						$entry['order']['getpoint'], $entry['order']['usedpoint'], $member['ID']);
//		
//			$wpdb->query( $mquery );
//			$mquery = $wpdb->prepare("SELECT mem_point FROM $member_table_name WHERE ID = %d", $member['ID']);
//			$point = $wpdb->get_var( $mquery );
//			$_SESSION['usces_member']['point'] = $point;
//		}
//	
//		if ( !empty($entry['reserve']) ) {
//			foreach ( $entry['reserve'] as $key => $value ) {
//				if ( is_array($value) )
//					 $value = serialize($value);
//				$mquery = $wpdb->prepare("INSERT INTO $order_table_meta_name ( order_id, meta_key, meta_value ) 
//											VALUES (%d, %s, %s, %s)", $order_id, $key, $value);
//				$wpdb->query( $mquery );
//			}
//		}
//	
//	endif;
//	
//	//zaiko
//	foreach($cart as $cartrow){
//		$zaikonum = $usces->getItemZaikoNum( $cartrow['post_id'], $cartrow['sku'] );
//		if($zaikonum == '') continue;
//		$zaikonum = $zaikonum - $cartrow['quantity'];
//		$usces->updateItemZaikoNum( $cartrow['post_id'], $cartrow['sku'], $zaikonum );
//		if($zaikonum <= 0) $usces->updateItemZaiko( $cartrow['post_id'], $cartrow['sku'], 2 );
//	}
//20100818ysk start
	if( !$order_id ) :

		return false;

	else :
		$usces->set_order_meta_value('customer_country', $_POST['customer']['country'], $order_id);
	
		if( !empty($_POST['custom_order']) ) {
			foreach( $_POST['custom_order'] as $key => $value ) {
				$csod_key = 'csod_'.$key;
				if( is_array($value) ) 
					 $value = serialize($value);
				$usces->set_order_meta_value($csod_key, $value, $order_id);
			}
		}
		if( !empty($_POST['custom_customer']) ) {
			foreach( $_POST['custom_customer'] as $key => $value ) {
				$cscs_key = 'cscs_'.$key;
				if( is_array($value) ) 
					 $value = serialize($value);
				$usces->set_order_meta_value($cscs_key, $value, $order_id);
			}
		}
		if( !empty($_POST['custom_delivery']) ) {
			foreach( $_POST['custom_delivery'] as $key => $value ) {
				$csde_key = 'csde_'.$key;
				if( is_array($value) ) 
					 $value = serialize($value);
				$usces->set_order_meta_value($csde_key, $value, $order_id);
			}
		}
		if ( !preg_match('/pending|noreceipt/', $status) ) {
			$value = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
			$usces->set_order_meta_value('receipted_date', $value, $order_id);
		}
	endif;
//20100818ysk end
	
	$usces->cart->crear_cart();
	return $res;
	
}

function usces_delete_memberdata( $ID = 0 ) {
	global $wpdb, $usces;
	
	if( 0 === $ID ){
		if(!isset($_REQUEST['member_id']) || $_REQUEST['member_id'] == '')
			return 0;
		$ID = $_REQUEST['member_id'];
	}

	$member_table_name = $wpdb->prefix . "usces_member";
//20100818ysk start
	$member_table_meta_name = $wpdb->prefix . "usces_member_meta";
//20100818ysk end

	$query = $wpdb->prepare("DELETE FROM $member_table_name WHERE ID = %d", $ID);
	$res = $wpdb->query( $query );
//20100818ysk start
	if($res) {
		$query = $wpdb->prepare("DELETE FROM $member_table_meta_name WHERE member_id = %d", $ID);
		$wpdb->query( $query );
	}
//20100818ysk end
	
	return $res;
}

function usces_update_memberdata() {
	global $wpdb, $usces;
	
	$member_table_name = $wpdb->prefix . "usces_member";
//20100818ysk start
	$member_table_meta_name = $wpdb->prefix . "usces_member_meta";
//20100818ysk end

	$ID = (int)$_REQUEST['member_id'];

//$wpdb->show_errors();
	$query = $wpdb->prepare(
				"UPDATE $member_table_name SET 
					`mem_email`=%s, `mem_status`=%s, `mem_point`=%d, `mem_name1`=%s, `mem_name2`=%s, 
					`mem_name3`=%s, `mem_name4`=%s, `mem_zip`=%s, `mem_pref`=%s, `mem_address1`=%s, 
					`mem_address2`=%s, `mem_address3`=%s, `mem_tel`=%s, `mem_fax`=%s 
				WHERE ID = %d", 
					$_POST['member']['email'], 
					$_POST['member']['status'], 
					$_POST['member']['point'], 
					$_POST['member']['name1'], 
					$_POST['member']['name2'], 
					$_POST['member']['name3'], 
					$_POST['member']['name4'], 
					$_POST['member']['zipcode'], 
					$_POST['member']['pref'], 
					$_POST['member']['address1'], 
					$_POST['member']['address2'], 
					$_POST['member']['address3'], 
					$_POST['member']['tel'], 
					$_POST['member']['fax'], 
					$ID
				);

//20100818ysk start
	//$res = $wpdb->query( $query );
	$res[0] = $wpdb->query( $query );
	if(false === $res[0]) 
		return false;
		
	$usces->set_member_meta_value('customer_country', $_POST['member']['country'], $ID);
	$i = 1;
	if( !empty($_POST['custom_member']) ) {
		foreach( $_POST['custom_member'] as $key => $value ) {
			$csmb_key = 'csmb_'.$key;
			if( is_array($value) ) 
				 $value = serialize($value);
			$res[$i] = $usces->set_member_meta_value($csmb_key, $value, $ID);
			if(false === $res[$i]) 
				return false;
			$i++;
		}
	}
	
	$meta_keys = "'zeus_pcid', 'remise_pcid'";
	$query = $wpdb->prepare("DELETE FROM $member_meta_table WHERE member_id = %d AND meta_key IN( $meta_keys )", 
			$_POST['member_id'] 
			);
	$res[$i] = $wpdb->query( $query );
	
	$result = ( 0 < array_sum($res) ) ? 1 : 0;
	//return $res;
	return $result;
//20100818ysk end
		
}

function usces_delete_orderdata() {
	global $wpdb, $usces;
	if(!isset($_REQUEST['order_id']) || $_REQUEST['order_id'] == '') return 0;
	$order_table = $wpdb->prefix . "usces_order";
	$order_meta_table = $wpdb->prefix . "usces_order_meta";
	$ID = $_REQUEST['order_id'];
	
	$query = $wpdb->prepare("SELECT * FROM $order_table WHERE ID = %d", $ID);
	$order_data = $wpdb->get_results( $query );

	$query = $wpdb->prepare("DELETE FROM $order_table WHERE ID = %d", $ID);
	$res = $wpdb->query( $query );
	
	if($res){
		$query = $wpdb->prepare("DELETE FROM $order_meta_table WHERE order_id = %d", $ID);
		$wpdb->query( $query );
		
		do_action('usces_action_del_orderdata', $order_data);
	}
	
	return $res;
}

function usces_update_ordercart() {
	global $wpdb, $usces;
	if(!isset($_REQUEST['order_id']) || $_REQUEST['order_id'] == '') return 0;
	$order_table_name = $wpdb->prefix . "usces_order";
	$ID = $_REQUEST['order_id'];
	$usces->cart->crear_cart();
	$usces->cart->upCart();
	$cart = $usces->cart->get_cart();

	$query = $wpdb->prepare("UPDATE $order_table_name SET `order_cart`=%s WHERE ID = %d", serialize($cart), $ID);
	$res = $wpdb->query( $query );
	
	$usces->cart->crear_cart();
	return $res;
}

function usces_update_ordercheck() {
	global $wpdb, $usces;

	$tableName = $wpdb->prefix . "usces_order";
	$order_id = $_POST['order_id'];
	$checked = $_POST['checked'];

	$query = $wpdb->prepare("SELECT `order_check` FROM $tableName WHERE ID = %d", $order_id);
	$res = $wpdb->get_var( $query );

	$checkfield = unserialize($res);
	if( !isset($checkfield[$checked]) ) $checkfield[$checked] = $checked;
	//$checkfield = 'OK';
	$query = $wpdb->prepare("UPDATE $tableName SET `order_check`=%s WHERE ID = %d", serialize($checkfield), $order_id);
	$res = $wpdb->query( $query );
	
	if($res)
		return $checked;
	else
		return 'error';
}
function usces_update_orderdata() {
	global $wpdb, $usces;
	
	$order_table_name = $wpdb->prefix . "usces_order";
	$order_table_meta_name = $wpdb->prefix . "usces_order_meta";
	$member_table_name = $wpdb->prefix . "usces_member";

	$ID = $_REQUEST['order_id'];
	
	$query = $wpdb->prepare("SELECT `order_status` FROM $order_table_name WHERE ID = %d", $ID);
	$old_status = $wpdb->get_var( $query );
	
	$usces->cart->crear_cart();
	$usces->cart->upCart();
	if(isset($_POST['delButton'])) {
		$usces->cart->del_row();
		$indexs = array_keys($_POST['delButton']);
		$index = $indexs[0];
		do_action('usces_admin_delete_orderrow', $ID, $index );
	}
	$cart = $usces->cart->get_cart();
	$usces->cart->entry();
	$entry = $usces->cart->get_entry();

	$item_total_price = $usces->get_total_price( $cart );
	$set = $usces->getPayments( $entry['order']['payment_name'] );
	$taio = isset($entry['order']['taio']) ? $entry['order']['taio'] : '';
	$receipt = isset($entry['order']['receipt']) ? $entry['order']['receipt'] : '';
	$admin = isset($entry['order']['admin']) ? $entry['order']['admin'] : '';
	$status = $usces->make_status( $taio, $receipt, $admin );
	if( $taio == 'completion' || $taio == 'continuation' ){
		if( 'update' == $_POST['up_modified'] ){
			$order_modified =  substr(get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 0, 10);
		}else{
			$order_modified =  $_POST['modified'];
		}
	}else{
		$order_modified = '';
	}
	$ordercheck = isset($_POST['check']) ? serialize($_POST['check']) : '';
	$member_id = $usces->get_memberid_by_email($_POST['customer']['mailaddress']);
	
//$wpdb->show_errors();
//20101208ysk start
/*
	$query = $wpdb->prepare(
				"UPDATE $order_table_name SET 
					`order_email`=%s, `order_name1`=%s, `order_name2`=%s, `order_name3`=%s, `order_name4`=%s, 
					`order_zip`=%s, `order_pref`=%s, `order_address1`=%s, `order_address2`=%s, `order_address3`=%s, 
					`order_tel`=%s, `order_fax`=%s, `order_delivery`=%s, `order_cart`=%s, `order_note`=%s, 
					`order_delivery_method`=%d, `order_delivery_time`=%s, `order_payment_name`=%s, `order_item_total_price`=%d, `order_getpoint`=%d, `order_usedpoint`=%d, 
					`order_discount`=%d, `order_shipping_charge`=%d, `order_cod_fee`=%d, `order_tax`=%d, `order_modified`=%s, 
					`order_status`=%s, `order_delidue_date`=%s, `order_check`=%s 
				WHERE ID = %d", 
					$_POST['customer']['mailaddress'], 
					$_POST['customer']['name1'], 
					$_POST['customer']['name2'], 
					$_POST['customer']['name3'], 
					$_POST['customer']['name4'], 
					$_POST['customer']['zipcode'], 
					$_POST['customer']['pref'], 
					$_POST['customer']['address1'], 
					$_POST['customer']['address2'], 
					$_POST['customer']['address3'], 
					$_POST['customer']['tel'], 
					$_POST['customer']['fax'], 
					serialize($_POST['delivery']), 
					serialize($cart), 
					$_POST['offer']['note'], 
					$_POST['offer']['delivery_method'], 
					$_POST['offer']['delivery_time'], 
					$_POST['offer']['payment_name'], 
					$item_total_price, 
					$_POST['offer']['getpoint'], 
					$_POST['offer']['usedpoint'], 
					$_POST['offer']['discount'], 
					$_POST['offer']['shipping_charge'], 
					$_POST['offer']['cod_fee'], 
					$_POST['offer']['tax'], 
					$order_modified, 
					$status,
					$_POST['offer']['delidue_date'], 
					$ordercheck,
					$ID
				);
*/
	$query = $wpdb->prepare(
				"UPDATE $order_table_name SET 
					`mem_id`=%d, `order_email`=%s, `order_name1`=%s, `order_name2`=%s, `order_name3`=%s, `order_name4`=%s, 
					`order_zip`=%s, `order_pref`=%s, `order_address1`=%s, `order_address2`=%s, `order_address3`=%s, 
					`order_tel`=%s, `order_fax`=%s, `order_delivery`=%s, `order_cart`=%s, `order_note`=%s, 
					`order_delivery_method`=%d, `order_delivery_date`=%s, `order_delivery_time`=%s, `order_payment_name`=%s, `order_item_total_price`=%f, `order_getpoint`=%d, `order_usedpoint`=%d, 
					`order_discount`=%f, `order_shipping_charge`=%f, `order_cod_fee`=%f, `order_tax`=%f, `order_modified`=%s, 
					`order_status`=%s, `order_delidue_date`=%s, `order_check`=%s 
				WHERE ID = %d", 
					$member_id, 
					$_POST['customer']['mailaddress'], 
					$_POST['customer']['name1'], 
					$_POST['customer']['name2'], 
					$_POST['customer']['name3'], 
					$_POST['customer']['name4'], 
					$_POST['customer']['zipcode'], 
					$_POST['customer']['pref'], 
					$_POST['customer']['address1'], 
					$_POST['customer']['address2'], 
					$_POST['customer']['address3'], 
					$_POST['customer']['tel'], 
					$_POST['customer']['fax'], 
					serialize($_POST['delivery']), 
					serialize($cart), 
					$_POST['offer']['note'], 
					$_POST['offer']['delivery_method'], 
					$_POST['offer']['delivery_date'], 
					$_POST['offer']['delivery_time'], 
					$_POST['offer']['payment_name'], 
					$item_total_price, 
					$_POST['offer']['getpoint'], 
					$_POST['offer']['usedpoint'], 
					$_POST['offer']['discount'], 
					$_POST['offer']['shipping_charge'], 
					$_POST['offer']['cod_fee'], 
					$_POST['offer']['tax'], 
					$order_modified, 
					$status,
					$_POST['offer']['delidue_date'], 
					$ordercheck,
					$ID
				);
//20101208ysk end

//20100818ysk start
	//$res = $wpdb->query( $query );
	$res[0] = $wpdb->query( $query );
	if(false === $res[0]) 
		return false;
		
	$usces->set_order_meta_value('customer_country', $_POST['customer']['country'], $ID);

	$i = 1;
	if( !empty($_POST['custom_order']) ) {
		foreach( $_POST['custom_order'] as $key => $value ) {
			$csod_key = 'csod_'.$key;
			if( is_array($value) ) 
				 $value = serialize($value);
			$res[$i] = $usces->set_order_meta_value($csod_key, $value, $ID);
			if(false === $res[$i]) 
				return false;
			$i++;
		}
	}
	if( !empty($_POST['custom_customer']) ) {
		foreach( $_POST['custom_customer'] as $key => $value ) {
			$cscs_key = 'cscs_'.$key;
			if( is_array($value) ) 
				 $value = serialize($value);
			$res[$i] = $usces->set_order_meta_value($cscs_key, $value, $ID);
			if(false === $res[$i]) 
				return false;
			$i++;
		}
	}
	if( !empty($_POST['custom_delivery']) ) {
		foreach( $_POST['custom_delivery'] as $key => $value ) {
			$csde_key = 'csde_'.$key;
			if( is_array($value) ) 
				 $value = serialize($value);
			$res[$i] = $usces->set_order_meta_value($csde_key, $value, $ID);
			if(false === $res[$i]) 
				return false;
			$i++;
		}
	}
	$value = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
	if ( !preg_match('/pending|noreceipt/', $old_status) && preg_match('/pending|noreceipt/', $status) ) {
		$rquery = $wpdb->prepare("DELETE FROM $order_table_meta_name WHERE order_id = %d AND meta_key = %s", $ID, 'receipted_date');
		$wpdb->query( $rquery );
	}else if ( !preg_match('/pending|noreceipt/', $old_status) && !preg_match('/pending|noreceipt/', $status) ) {
		$query = $wpdb->prepare("SELECT order_id FROM $order_table_meta_name WHERE order_id = %d AND meta_key = %s", $ID, 'receipted_date');
		$varres = $wpdb->get_var( $query );
		if( empty($varres) ){
			$usces->set_order_meta_value('receipted_date', $value, $ID);
		}
	}else if ( preg_match('/pending|noreceipt/', $old_status) && !preg_match('/pending|noreceipt/', $status) ) {
		$usces->set_order_meta_value('receipted_date', $value, $ID);
	}

	$result = ( 0 < array_sum($res) ) ? 1 : 0;
//20100818ysk end

	$query = $wpdb->prepare("SELECT * FROM $order_table_name WHERE ID = %d", $ID);
	$new_orderdata = $wpdb->get_results( $query );

	do_action('usces_action_update_orderdata', $new_orderdata);
	$usces->cart->crear_cart();
	
return $result;
		
//	else :
//	
//		if ( $member['ID'] ) {
//		
//			$mquery = $wpdb->prepare(
//						"UPDATE $member_table_name SET mem_point = (mem_point + %d - %d) WHERE ID = %d", 
//						$entry['order']['getpoint'], $entry['order']['usedpoint'], $member['ID']);
//		
//			$wpdb->query( $mquery );
//			$mquery = $wpdb->prepare("SELECT mem_point FROM $member_table_name WHERE ID = %d", $member['ID']);
//			$point = $wpdb->get_var( $mquery );
//			$_SESSION['usces_member']['point'] = $point;
//		}
//	
//		if ( !empty($entry['reserve']) ) {
//			foreach ( $entry['reserve'] as $key => $value ) {
//				if ( is_array($value) )
//					 $value = serialize($value);
//				$mquery = $wpdb->prepare("INSERT INTO $order_table_meta_name ( order_id, meta_key, meta_value ) 
//											VALUES (%d, %s, %s, %s)", $order_id, $key, $value);
//				$wpdb->query( $mquery );
//			}
//		}
//	
//	endif;
//	
//	//zaiko
//	foreach($cart as $cartrow){
//		$zaikonum = $usces->getItemZaikoNum( $cartrow['post_id'], $cartrow['sku'] );
//		if($zaikonum == '') continue;
//		$zaikonum = $zaikonum - $cartrow['quantity'];
//		$usces->updateItemZaikoNum( $cartrow['post_id'], $cartrow['sku'], $zaikonum );
//		if($zaikonum <= 0) $usces->updateItemZaiko( $cartrow['post_id'], $cartrow['sku'], 2 );
//	}
//	
//	return $order_id;
	
}



function usces_export_xml() {
	$options = get_option('usces');
	echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?' . ">\n";
?>
	<usces><?php echo serialize($options); ?></usces>
	<usces_management_status><?php echo serialize(get_option('usces_management_status')); ?></usces_management_status>
	<usces_zaiko_status><?php echo serialize(get_option('usces_zaiko_status')); ?></usces_zaiko_status>
	<usces_customer_status><?php echo serialize(get_option('usces_customer_status')); ?></usces_customer_status>
	<usces_payment_structure><?php echo serialize(get_option('usces_payment_structure')); ?></usces_payment_structure>
	<usces_display_mode><?php echo serialize(get_option('usces_display_mode')); ?></usces_display_mode>
<!--20110331ysk start-->
<!--	<usces_pref><?php //echo serialize(get_option('usces_pref')); ?></usces_pref>-->
<!--20110331ysk end-->
	<usces_shipping_rule><?php echo serialize(get_option('usces_shipping_rule')); ?></usces_shipping_rule>

<?php
}

function usces_import_xml() {
	global $usces, $wpdb;
	
	if($_FILES['data']['error'] != 0) return false;
	if (!is_uploaded_file($_FILES['data']['tmp_name'])) return false;
	if(!ereg("^usces.+\.xml$",$_FILES['data']['name'])) return false;
	
	$fp = fopen ($_FILES['data']['tmp_name'], "r");
	$xml = '';
	while (!feof($fp)) {
		$xml .= fgets($fp, 1024);
	}
	fclose ($fp);
	
	$parts = explode('<usces>', $xml);
	if(count($parts) > 1){
		$parts = explode('</usces>', $parts[1]);
		$opt_usces = $parts[0];
		$usces->options = unserialize($opt_usces);
		update_option('usces', $usces->options);	
	}
	///////////////////////////////////////////////////////////////

	$parts = explode('<usces_management_status>', $xml);
	if(count($parts) > 1){
		$parts = explode('</usces_management_status>', $parts[1]);
		$opt = $parts[0];
		$option = unserialize($opt);
		update_option('usces_management_status', $option);	
	}

	$parts = explode('<usces_customer_status>', $xml);
	if(count($parts) > 1){
		$parts = explode('</usces_customer_status>', $parts[1]);
		$opt = $parts[0];
		$option = unserialize($opt);
		update_option('usces_customer_status', $option);	
	}
//20110331ysk start
/*	$parts = explode('<usces_pref>', $xml);
	if(count($parts) > 1){
		$parts = explode('</usces_pref>', $parts[1]);
		$opt = $parts[0];
		$option = unserialize($opt);
		update_option('usces_pref', $option);	
	}*/
//20110331ysk start
	///////////////////////////////////////////////////////////////
	
	$parts = explode('<usces_zaiko_status>', $xml);
	if(count($parts) > 1){
		$parts = explode('</usces_zaiko_status>', $parts[1]);
		$opt = $parts[0];
		$usces->zaiko_status = unserialize($opt);
		update_option('usces_zaiko_status', $usces->zaiko_status);	
	}

	$parts = explode('<usces_payment_structure>', $xml);
	if(count($parts) > 1){
		$parts = explode('</usces_payment_structure>', $parts[1]);
		$opt = $parts[0];
		$usces->payment_structure = unserialize($opt);
		update_option('usces_payment_structure', $usces->payment_structure);	
	}

	$parts = explode('<usces_display_mode>', $xml);
	if(count($parts) > 1){
		$parts = explode('</usces_display_mode>', $parts[1]);
		$opt = $parts[0];
		$usces->display_mode = unserialize($opt);
		update_option('usces_display_mode', $usces->display_mode);	
	}

	$parts = explode('<usces_shipping_rule>', $xml);
	if(count($parts) > 1){
		$parts = explode('</usces_shipping_rule>', $parts[1]);
		$opt = $parts[0];
		$usces->shipping_rule = unserialize($opt);
		update_option('usces_shipping_rule', $usces->shipping_rule);	
	}

	//category item post
	$slug = urlencode(__('Items','usces'));
	$query = $wpdb->prepare("SELECT tr.object_id FROM $wpdb->terms AS t 
								INNER JOIN $wpdb->term_relationships AS tr ON tr.term_taxonomy_id = t.term_id 
								WHERE t.slug = %s", $slug);
	$term_id = $wpdb->get_col( $query );
	$id_str = implode(',', $term_id);

	$query = "UPDATE $wpdb->posts SET post_mime_type = 'item' WHERE ID IN ({$id_str}) AND post_type = 'post'";
	$wpdb->query( $query );

	$query = "SELECT ID FROM $wpdb->posts WHERE post_mime_type = 'item'";
	$item_id = $wpdb->get_col( $query );

	foreach ( $item_id as $id ) {
	
		$query = $wpdb->prepare("SELECT meta_id, meta_value FROM $wpdb->postmeta 
									WHERE (SUBSTRING(meta_key,1,6) = '_iopt_' OR SUBSTRING(meta_key,1,6) = '_isku_') AND post_id = %d", $id);
		$metas = $wpdb->get_results( $query, ARRAY_A );

		if(!empty($metas)) {
			foreach ( $metas as $meta ) {
				$new_valu = unserialize($meta['meta_value']);
				$meta_id = $meta['meta_id'];
				$query = $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = %s WHERE meta_id = %d AND SUBSTRING(meta_value,1,2) = 's:'", 
										$new_valu, $meta_id);
				$wpdb->query( $query );
			
			}
		}
	}

	$query = $wpdb->prepare("SELECT meta_id, meta_value FROM $wpdb->postmeta 
								WHERE (SUBSTRING(meta_key,1,6) = '_iopt_' OR SUBSTRING(meta_key,1,6) = '_isku_') AND post_id = %d", USCES_CART_NUMBER);
	$metas = $wpdb->get_results( $query, ARRAY_A );

	if(!empty($metas)) {
		foreach ( $metas as $meta ) {
			$new_valu = unserialize($meta['meta_value']);
			$meta_id = $meta['meta_id'];
			$query = $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = %s WHERE meta_id = %d AND SUBSTRING(meta_value,1,2) = 's:'", 
									$new_valu, $meta_id);
			$wpdb->query( $query );
		
		}
	}
		
	$query = "SELECT name, term_id FROM $wpdb->terms WHERE slug = 'item'";
	$item_parent = $wpdb->get_row( $query, ARRAY_A );
	if(empty($item_parent) && $item_parent !== 0) return false;

	$query = $wpdb->prepare("SELECT term_id FROM $wpdb->terms WHERE name = %s AND term_id <> %d", $item_parent['name'], $item_parent['term_id']);
	$mis_id = $wpdb->get_var( $query );
	if($mis_id > 0){
		$query = $wpdb->prepare("SELECT object_id FROM $wpdb->term_relationships AS tr 
						INNER JOIN $wpdb->posts AS p ON p.ID = tr.object_id
						WHERE tr.term_taxonomy_id = %d AND p.post_mime_type = 'item'", $mis_id);
		$post_ids = $wpdb->get_col( $query );
		if(count($post_ids) > 0){
			foreach ( $post_ids as $id ) {
				$query = $wpdb->prepare("SELECT count(object_id) AS ct FROM $wpdb->term_relationships 
							WHERE object_id = %d AND term_taxonomy_id = %d", $id, $item_parent['term_id']);
				$ct = $wpdb->get_var( $query );
				if($ct > 0) continue;
				
				$query = $wpdb->prepare("INSERT INTO $wpdb->term_relationships 
							(object_id, term_taxonomy_id, term_order) VALUES (%d, %d, %d)", $id, $item_parent['term_id'], 0);
				$wpdb->query( $query );
			}
			$query = $wpdb->prepare("UPDATE $wpdb->term_taxonomy SET count = %d WHERE term_id = %d", 
										count($post_ids), $item_parent['term_id']);
			$wpdb->query( $query );
		}
	}
	
	$query = $wpdb->prepare("SELECT term_id FROM $wpdb->term_taxonomy WHERE parent = %d", $item_parent['term_id']);
	$item_childlen = $wpdb->get_col( $query );
	if(empty($item_childlen)) return false;

	foreach ( $item_childlen as $child_id ) {
		$query = $wpdb->prepare("SELECT name FROM $wpdb->terms WHERE term_id = %d", $child_id);
		$child_name = $wpdb->get_var( $query );
	
		$query = $wpdb->prepare("SELECT term_id FROM $wpdb->terms WHERE name = %s AND term_id <> %d", $child_name, $child_id);
		$mis_id = $wpdb->get_var( $query );
		if($mis_id > 0){
			$query = $wpdb->prepare("SELECT object_id FROM $wpdb->term_relationships AS tr 
							INNER JOIN $wpdb->posts AS p ON p.ID = tr.object_id
							WHERE tr.term_taxonomy_id = %d AND p.post_mime_type = 'item'", $mis_id);
			$post_ids = $wpdb->get_col( $query );
			if(count($post_ids) > 0){
				foreach ( $post_ids as $id ) {
					$query = $wpdb->prepare("SELECT count(object_id) AS ct FROM $wpdb->term_relationships 
								WHERE object_id = %d AND term_taxonomy_id = %d", $id, $child_id);
					$ct = $wpdb->get_var( $query );
					if($ct > 0) continue;
					
					$query = $wpdb->prepare("INSERT INTO $wpdb->term_relationships 
								(object_id, term_taxonomy_id, term_order) VALUES (%d, %d, %d)", $id, $child_id, 0);
					$wpdb->query( $query );
				}
				$query = $wpdb->prepare("UPDATE $wpdb->term_taxonomy SET count = %d WHERE term_id = %d", 
											count($post_ids), $child_id);
				$wpdb->query( $query );
			}
			
		} else {
			$query = $wpdb->prepare("UPDATE $wpdb->term_taxonomy SET count = 0 WHERE term_id = %d", $child_id);
			$wpdb->query( $query );
		}
	
	}
	

	$usces->action_message = count($post_ids);
	return false;
	
}

function usces_all_change_zaiko(&$obj) {
	global $wpdb;

	$ids = $_POST['listcheck'];
	$status = true;
	foreach ( (array)$ids as $post_id ):
		$query = $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND SUBSTRING(meta_key, 1, 6) = '_isku_'", $post_id);
		$metas = $wpdb->get_results( $query, ARRAY_A );
		if(!$metas) continue;
		foreach ( (array)$metas as $meta ) {
			$meta_id = $meta['meta_id'];
			$sku = unserialize($meta['meta_value']);
			$sku['zaiko'] = (int)$_POST['change']['word']['zaiko'];
			$skustr = serialize($sku);
			$query = $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = %s WHERE meta_id = %d", $skustr, $meta_id);
			$res = $wpdb->query( $query );
			if( $res === false ) {
				$status = false;
			}
		}
	endforeach;
	if ( true === $status ) {
		$obj->set_action_status('success', __('I completed collective operation.','usces'));
	} elseif ( false === $status ) {
		$obj->set_action_status('error', __('ERROR: I was not able to complete collective operation','usces'));
	} else {
		$obj->set_action_status('none', '');
	}
}

function usces_all_change_itemdisplay(&$obj){
	global $wpdb;

	$ids = $_POST['listcheck'];
	$post_status = $_POST['change']['word']['display_status'];
	$status = true;
	foreach ( (array)$ids as $post_id ):
		$query = $wpdb->prepare("UPDATE $wpdb->posts SET post_status = %s WHERE ID = %d", $post_status, $post_id);
		$res = $wpdb->query( $query );
		if( $res === false ) {
			$status = false;
		}
	endforeach;
	if ( true === $status ) {
		$obj->set_action_status('success', __('I completed collective operation.','usces'));
	} elseif ( false === $status ) {
		$obj->set_action_status('error', __('ERROR: I was not able to complete collective operation','usces'));
	} else {
		$obj->set_action_status('none', '');
	}
}

function usces_all_delete_itemdata(&$obj){
	global $wpdb;

	$ids = $_POST['listcheck'];
	$status = true;
	foreach ( (array)$ids as $post_id ){
		$query = $wpdb->prepare("DELETE FROM $wpdb->posts WHERE ID = %d", $post_id);
		$res = $wpdb->query( $query );
		if( $res !== false ) {
			$query = $wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE post_id = %d", $post_id);
			$res = $wpdb->query( $query );
			if( $res === false ) {
				$status = false;
			}
			$query = $wpdb->prepare("DELETE FROM $wpdb->term_relationships WHERE object_id = %d", $post_id);
			$res = $wpdb->query( $query );
			if( $res === false ) {
				$status = false;
			}
			$query = "SELECT term_taxonomy_id, COUNT(*) AS ct FROM $wpdb->term_relationships 
					GROUP BY term_taxonomy_id";
			$relation_data = $wpdb->get_results( $query, ARRAY_A);
			foreach((array)$relation_data as $rows){
				
				$term_ids['term_taxonomy_id'] = $rows['term_taxonomy_id'];
				$updatas['count'] = $rows['ct'];
				$wpdb->update( $wpdb->term_taxonomy, $updatas, $term_ids );
			}
		}
	}
	if ( true === $status ) {
		$obj->set_action_status('success', __('I completed collective operation.','usces'));
	} elseif ( false === $status ) {
		$obj->set_action_status('error', __('ERROR: I was not able to complete collective operation','usces'));
	} else {
		$obj->set_action_status('none', '');
	}
}

function usces_all_change_order_reciept(&$obj){
	global $wpdb;

	$tableName = $wpdb->prefix . "usces_order";
	$ids = $_POST['listcheck'];
	$status = true;
	foreach ( (array)$ids as $id ):
		$query = $wpdb->prepare("SELECT order_status FROM $tableName WHERE ID = %d", $id);
		$statusstr = $wpdb->get_var( $query );
		if(strpos($statusstr, 'noreceipt') === false && strpos($statusstr, 'receipted') === false) continue;
		if($_REQUEST['change']['word']['order_reciept'] == 'receipted') {
			if(strpos($statusstr, 'noreceipt') !== false)
				$statusstr = str_replace('noreceipt', 'receipted', $statusstr);
		}elseif($_REQUEST['change']['word']['order_reciept'] == 'noreceipt') {
			if(strpos($statusstr, 'receipted') !== false)
				$statusstr = str_replace('receipted', 'noreceipt', $statusstr);
		}
		$query = $wpdb->prepare("UPDATE $tableName SET order_status = %s WHERE ID = %d", $statusstr, $id);
		$res = $wpdb->query( $query );
		if( $res === false ) {
			$status = false;
		}
	endforeach;
	if ( true === $status ) {
		$obj->set_action_status('success', __('I completed collective operation.','usces'));
	} elseif ( false === $status ) {
		$obj->set_action_status('error', __('ERROR: I was not able to complete collective operation','usces'));
	} else {
		$obj->set_action_status('none', '');
	}
}

function usces_all_change_order_status(&$obj){
	global $wpdb;

	$tableName = $wpdb->prefix . "usces_order";
	$ids = $_POST['listcheck'];
	$status = true;
	foreach ( (array)$ids as $id ):
		$query = $wpdb->prepare("SELECT order_status FROM $tableName WHERE ID = %d", $id);
		$statusstr = $wpdb->get_var( $query );
		switch ($_REQUEST['change']['word']['order_status']) {
			case 'estimate':
				if(strpos($statusstr, 'adminorder') !== false) {
					$statusstr = str_replace('adminorder', 'estimate', $statusstr);
				}else if(strpos($statusstr, 'estimate') === false) {
					$statusstr .= 'estimate,';
				}
				break;
			case 'adminorder':
				if(strpos($statusstr, 'estimate') !== false) {
					$statusstr = str_replace('estimate', 'adminorder', $statusstr);
				}else if(strpos($statusstr, 'adminorder') === false) {
					$statusstr .= 'adminorder,';
				}
				break;
			case 'duringorder':
				if(strpos($statusstr, 'cancel') !== false) {
					$statusstr = str_replace('cancel', 'duringorder', $statusstr);
				}else if(strpos($statusstr, 'completion') !== false) {
					$statusstr = str_replace('completion', 'duringorder', $statusstr);
				}else if(strpos($statusstr, 'new') !== false) {
					$statusstr = str_replace('new', 'duringorder', $statusstr);
				}else if(strpos($statusstr, 'duringorder') === false) {
					$statusstr .= 'duringorder,';
				}
				break;
			case 'cancel':
				if(strpos($statusstr, 'completion') !== false) {
					$statusstr = str_replace('completion', 'cancel', $statusstr);
				}else if(strpos($statusstr, 'new') !== false) {
					$statusstr = str_replace('new', 'cancel', $statusstr);
				}else if(strpos($statusstr, 'duringorder') !== false) {
					$statusstr = str_replace('duringorder', 'cancel', $statusstr);
				}else if(strpos($statusstr, 'cancel') === false) {
					$statusstr .= 'cancel,';
				}
				break;
			case 'completion':
				if(strpos($statusstr, 'new') !== false) {
					$statusstr = str_replace('new', 'completion', $statusstr);
				}else if(strpos($statusstr, 'duringorder') !== false) {
					$statusstr = str_replace('duringorder', 'completion', $statusstr);
				}else if(strpos($statusstr, 'cancel') !== false) {
					$statusstr = str_replace('cancel', 'completion', $statusstr);
				}else if(strpos($statusstr, 'completion') === false) {
					$statusstr .= 'completion,';
				}
				break;
			case 'new':
				if(strpos($statusstr, 'duringorder') !== false) {
					$statusstr = str_replace('duringorder,', '', $statusstr);
				}else if(strpos($statusstr, 'completion') !== false) {
					$statusstr = str_replace('completion,', '', $statusstr);
				}else if(strpos($statusstr, 'cancel') !== false) {
					$statusstr = str_replace('cancel,', '', $statusstr);
				}
				break;
		}
		$query = $wpdb->prepare("UPDATE $tableName SET order_status = %s WHERE ID = %d", $statusstr, $id);
		$res = $wpdb->query( $query );
		if( $res === false ) {
			$status = false;
		}
	endforeach;
	if ( true === $status ) {
		$obj->set_action_status('success', __('I completed collective operation.','usces'));
	} elseif ( false === $status ) {
		$obj->set_action_status('error', __('ERROR: I was not able to complete collective operation','usces'));
	} else {
		$obj->set_action_status('none', '');
	}
}

function usces_all_delete_order_data(&$obj){
	global $wpdb;

	$tableName = $wpdb->prefix . "usces_order";
	$tableMetaName = $wpdb->prefix . "usces_order_meta";
	$ids = $_POST['listcheck'];
	$status = true;
	foreach ( (array)$ids as $id ):
		$query = $wpdb->prepare("DELETE FROM $tableName WHERE ID = %d", $id);
		$res = $wpdb->query( $query );
		if( $res === false ) {
			$status = false;
		}else{
			$metaquery = $wpdb->prepare("DELETE FROM $tableMetaName WHERE order_id = %d", $ID);
			$metares = $wpdb->query( $metaquery );
		}
	endforeach;
	if ( true === $status ) {
		$obj->set_action_status('success', __('I completed collective operation.','usces'));
	} elseif ( false === $status ) {
		$obj->set_action_status('error', __('ERROR: I was not able to complete collective operation','usces'));
	} else {
		$obj->set_action_status('none', '');
	}
}

function usces_check_acting_return() {
	global $usces;
	$entry = $usces->cart->get_entry();

	$acting = $_GET['acting'];
	$results = array();
	switch ( $acting ) {
		case 'epsilon':
			if(isset($_GET['duplicate']) && $_GET['duplicate'] == 1){
				$results[0] = 'duplicate';
			}else if(isset($_GET['result'])){
				$results[0] = (int)$_GET['result'];
				$results['reg_order'] = true;
			}else{
				$str = explode('?', $_GET['acting_return']);
				if(!isset($str[1])) {
					$results[0] = 0;
				}else{
					$para = parse_str($str[1]);
					$results[0] = isset($para['result']) ? (int)$para['result'] : 0;
					foreach($para as $key => $value){
						$results[$key] = $value;
					}
				}
			
			}
			$results['reg_order'] = true;
			break;
			
		case 'paypal':
			usces_log('paypal in ', 'acting_transaction.log');
			require_once($usces->options['settlement_path'] . "paypal.php");
			$results = paypal_check($usces_paypal_url);
			remove_action( 'wp_footer', array(&$usces, 'lastprocessing'));
			$results['reg_order'] = true;
			break;
			
		case 'zeus_card':
			$results = $_REQUEST;
			if( $_REQUEST['acting_return'] ){
				$results[0] = 1;
			}else{
				$results[0] = 0;
			}
			$results['reg_order'] = true;
			break;
			
		case 'zeus_conv':
			$results = $_GET;
			if( $_REQUEST['acting_return'] ){
				$results[0] = 1;
			}else{
				$results[0] = 0;
			}
			$results['reg_order'] = false;
			break;
			
		case 'remise_card':
			$results = $_POST;
			if( $_REQUEST['acting_return'] && '   ' == $_REQUEST['X-ERRCODE']){
				//usces_log('remise card entry data : '.print_r($entry, true), 'acting_transaction.log');
				$results[0] = 1;
			}else{
				$results[0] = 0;
			}
			$results['reg_order'] = false;
			break;
			
		case 'remise_conv':
			$results = $_GET;
			if( $_REQUEST['acting_return'] && isset($_REQUEST['X-JOB_ID']) && '0:0000' == $_REQUEST['X-R_CODE']){
				//usces_log('remise conv entry data : '.print_r($entry, true), 'acting_transaction.log');
				$results[0] = 1;
			}else{
				$results[0] = 0;
			}
			$results['reg_order'] = true;
			break;
			
//20101018ysk start
		case 'jpayment_card':
			$results = $_GET;
			if($_GET['rst'] == 2) {
				usces_log('jpayment card entry error : '.print_r($entry, true), 'acting_transaction.log');
			}
			$results[0] = ($_GET['rst'] == 1) ? 1 : 0;
			$results['reg_order'] = true;
			break;

		case 'jpayment_conv':
			$results = $_GET;
			if($_GET['rst'] == 2) {
				usces_log('jpayment conv entry error : '.print_r($entry, true), 'acting_transaction.log');
			}
			$results[0] = ($_GET['rst'] == 1 and $_GET['ap'] == 'CPL_PRE') ? 1 : 0;
			$results['reg_order'] = true;
			break;

		case 'jpayment_bank':
			$results = $_GET;
			if($_GET['rst'] == 2) {
				usces_log('jpayment bank entry error : '.print_r($entry, true), 'acting_transaction.log');
			}
			$results[0] = ($_GET['rst'] == 1) ? 1 : 0;
			$results['reg_order'] = true;
			break;
//20101018ysk end
//20110208ysk start
		case 'paypal_ec':
			$results = $_GET;

			//Build a second API request to PayPal, using the token as the ID to get the details on the payment authorization
		    $nvpstr = "&TOKEN=".urlencode($_REQUEST['token']);

			$usces->paypal->setMethod('GetExpressCheckoutDetails');
			$usces->paypal->setData($nvpstr);
			$res = $usces->paypal->doExpressCheckout();
			$resArray = $usces->paypal->getResponse();
			$ack = strtoupper($resArray["ACK"]);
			if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {
				$results[0] = 1;

			} else {
				//Display a user friendly Error on the page using any of the following error information returned by PayPal
				$ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
				$ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
				$ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
				$ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);
				usces_log('PayPal : GetExpressCheckoutDetails API call failed. Error Code:['.$ErrorCode.'] Error Severity Code:['.$ErrorSeverityCode.'] Short Error Message:'.$ErrorShortMsg.' Detailed Error Message:'.$ErrorLongMsg, 'acting_transaction.log');
				$results[0] = 0;
			}
			$results['reg_order'] = true;
			break;
//20110208ysk end

		default:
			$results = $_GET;
			if( $_REQUEST['result'] ){
				$results[0] = 1;
			}else{
				$results[0] = 0;
			}
//20110310ysk start
			//$results['reg_order'] = false;
			$results['reg_order'] = true;
//20110310ysk end
			break;
	}
	
	return $results;
}
//20110203ysk start
function usces_check_acting_return_duplicate( $results = array() ) {
	global $wpdb;
//		usces_log('$_REQUEST : '.print_r($_REQUEST,true), 'acting_transaction.log');
//		usces_log('$results : '.print_r($results,true), 'acting_transaction.log');

	switch($_GET['acting']) {
	case 'epsilon':
		$trans_id = $_REQUEST['trans_code'];
		break;
//20110208ysk start
	case 'paypal':
		$trans_id = $results['txn_id'];
		break;
//20110208ysk end
	case 'paypal_ipn':
		$trans_id = $_REQUEST['txn_id'];
		break;
//20110208ysk start
	case 'paypal_ec':
		$trans_id = $_REQUEST['token'];
		break;
//20110208ysk end
	case 'zeus_card':
		$trans_id = $_REQUEST['ordd'];
		break;
	case 'zeus_conv':
	case 'zeus_bank':
		$trans_id = $_REQUEST['order_no'];
		break;
	case 'remise_card':
		$trans_id = $_REQUEST['X-TRANID'];
		break;
	case 'remise_conv':
		$trans_id = $_REQUEST['X-JOB_ID'];
		break;
	case 'jpayment_card':
	case 'jpayment_conv':
	case 'jpayment_bank':
		$trans_id = $_REQUEST['gid'];
		break;
	default:
		$trans_id = '';
	}
	$table_meta_name = $wpdb->prefix.'usces_order_meta';
	$query = $wpdb->prepare("SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'trans_id', $trans_id);
	$order_id = $wpdb->get_var($query);
	return $order_id;
}
//20110203ysk end

function usces_item_dupricate($post_id){
	global $wpdb;
	if( empty($post_id) ) return;
	
	$query = $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d", $post_id);
	$post_data = $wpdb->get_row( $query, ARRAY_A );
	if(!$post_data) return;
	foreach($post_data as $key => $value){
		switch( $key ){
			case 'ID':
				break;
			case 'post_date':
			case 'post_modified':
				$datas[$key] = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
				break;
			case 'post_date_gmt':
			case 'post_modified_gmt':
				$datas[$key] = gmdate('Y-m-d H:i:s');
				break;
			case 'post_status':
				$datas[$key] = 'draft';
				break;
			case 'post_name':
			case 'guid':
				$datas[$key] = '';
				break;
			case 'post_parent':
			case 'comment_count':
				$datas[$key] = 0;
				break;
			default:
				$datas[$key] = $value;
		}
	}
	$wpdb->insert( $wpdb->posts, $datas );
	$ids['ID'] = $wpdb->insert_id;
	$updatas['post_name'] = $ids['ID'];
	$updatas['guid'] = get_option('home') . '?p=' . $ids['ID'];
	$wpdb->update( $wpdb->posts, $updatas, $ids );
	
	$newpost_id = $wpdb->insert_id;
	
	$query = $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d", $post_id);
	$meta_data = $wpdb->get_results( $query );
	if(!$meta_data) return;
	$valstr = '';
	foreach($meta_data as $data){
		
		$prefix = substr($data->meta_key, 0, 5);
		$prefix2 = substr($data->meta_key, 0, 11);
		
		if( $prefix == '_item' ){
		
			switch( $data->meta_key ){
				case '_itemCode':
					$value = $data->meta_value . '(copy)';
					break;
				default:
					$value = $data->meta_value;
			}
			$key = $data->meta_key;
			$valstr .= '(' . $newpost_id . ", '" . $key . "','" . $value . "'),";
		
		}else if( $prefix == '_isku' ){
		
			$value = $data->meta_value;
			$key = $data->meta_key . '(copy)';
			$valstr .= '(' . $newpost_id . ", '" . $key . "','" . $value . "'),";
		
		}else{
		
			$value = $data->meta_value;
			$key = $data->meta_key;
			$valstr .= '(' . $newpost_id . ", '" . $key . "','" . $value . "'),";
		
		}

//		if( $prefix == 'item' ){
//		
//			switch( $data->meta_key ){
//				case '_itemCode':
//					$value = $data->meta_value . '(copy)';
//					break;
//				default:
//					$value = $data->meta_value;
//			}
//			$key = $data->meta_key;
//			$valstr .= '(' . $newpost_id . ", '_usces_" . $key . "','" . $value . "'),";
//		
//		}else if( $prefix == '_isku' ){
//		
//			$value = $data->meta_value;
//			$key = $data->meta_key . '(copy)';
//			$valstr .= '(' . $newpost_id . ", '_usces_" . $key . "','" . $value . "'),";
//		
//		}else if( $prefix2 == '_usces_item' ){
//		
//			switch( $data->meta_key ){
//				case '_usces_itemCode':
//					$value = $data->meta_value . '(copy)';
//					break;
//				default:
//					$value = $data->meta_value;
//			}
//			$key = $data->meta_key;
//			$valstr .= '(' . $newpost_id . ", '" . $key . "','" . $value . "'),";
//		
//		}else if( $prefix == '_usces_isku' ){
//		
//			$value = $data->meta_value;
//			$key = $data->meta_key . '(copy)';
//			$valstr .= '(' . $newpost_id . ", '" . $key . "','" . $value . "'),";
//		
//		}
	}
	$valstr = rtrim($valstr, ',');
	$query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES $valstr";
	$res = mysql_query($query);
	if(!$res ) return;

	$query = $wpdb->prepare("SELECT * FROM $wpdb->term_relationships WHERE object_id = %d", $post_id);
	$relation_data = $wpdb->get_results( $query );
	if(!$relation_data) return;

	foreach($relation_data as $data){
		$query = $wpdb->prepare("INSERT INTO $wpdb->term_relationships 
						(object_id, term_taxonomy_id, term_order) VALUES 
						(%d, %d, 0)", 
						$newpost_id, $data->term_taxonomy_id
				);
		$res = mysql_query($query);
		if( !$res ) return;
		$query = $wpdb->prepare("UPDATE $wpdb->term_taxonomy SET count = count + 1 
						WHERE term_taxonomy_id = %d", 
						$data->term_taxonomy_id
				);
		$res = mysql_query($query);
		if( !$res ) return;
	}

	return $newpost_id;
}

//20111111ysk start
function usces_dates_interconv( $date_str ) {
	$base_struc = split('[/.-]', 'd/m/Y' );
	$date_str_parts = split('[/.-]', $date_str );
	$date_elements = array();

	$p_keys = array_keys( $base_struc );
	foreach( $p_keys as $p_key ) {
		if( !empty( $date_str_parts[$p_key] )) {
			$date_elements[$base_struc[$p_key]] = $date_str_parts[$p_key];
		} else {
			return false;
		}
	}
	$dummy_ts = mktime( 0,0,0, $date_elements['m'],$date_elements['d'],$date_elements['Y']);
	return date( 'Y-m-d', $dummy_ts );
}
//20101111ysk end

function usces_register_action($handle, $type, $key, $value, $function){
	global $usces_action;
	$usces_action[$handle] = array('type'=>$type, 'key'=>$key, 'value'=>$value, 'function'=>$function);
}

function usces_deregister_action($handle){
	global $usces_action;
	unset($usces_action[$handle]);
}

function usces_get_wcexp($post_id, $name=''){
	$wcexp = get_post_meta($post_id, '_wcexp', true);
	if( empty($name) ){
		$value = ( $wcexp !== false ) ? $wcexp : '';
	}else{
		$value = ($wcexp !== false && isset($wcexp[$name])) ? $wcexp[$name] : '';
	}
	return $value;
}

function usces_update_check_admin($result){
	$result = true;
	return $result;
}

function usces_setup_cod_ajax(){
	global $usces;
	$usces->options = get_option('usces');
	$message = '';
	
	$usces->options['cod_type'] = isset($_POST['cod_type']) ? $_POST['cod_type'] : 'fix';
	if( isset($_POST['cod_fee']) )
		$usces->options['cod_fee'] = (int)$_POST['cod_fee'];
		
	if( 'change' == $usces->options['cod_type'] ){
		if( isset($_POST['cod_first_amount']) ){
			$usces->options['cod_first_amount'] = (int)$_POST['cod_first_amount'];
			if( 0 === (int)$_POST['cod_first_amount'] )
				$message = __('There is the item where a value is dirty.', 'usces');
		}
		if( isset($_POST['cod_first_fee']) ){
			$usces->options['cod_first_fee'] = (int)$_POST['cod_first_fee'];
			if( 0 === (int)$_POST['cod_first_fee'] && '0' !== $_POST['cod_first_fee'])
				$message = __('There is the item where a value is dirty.', 'usces');
		}
		if( isset($_POST['cod_end_fee']) ){
			$usces->options['cod_end_fee'] = (int)$_POST['cod_end_fee'];
			if( 0 === (int)$_POST['cod_end_fee'] && '0' !== $_POST['cod_end_fee'] )
				$message = __('There is the item where a value is dirty.', 'usces');
		}
		
		unset($usces->options['cod_amounts'], $usces->options['cod_fees']);
		if( isset($_POST['cod_amounts']) ){
			for($i=0; $i<count((array)$_POST['cod_amounts']); $i++){
				$usces->options['cod_amounts'][$i] = (int)$_POST['cod_amounts'][$i];
				$usces->options['cod_fees'][$i] = (int)$_POST['cod_fees'][$i];
				if( 0 === (int)$_POST['cod_amounts'][$i] || (0 === (int)$_POST['cod_fees'][$i] && '0' !== $_POST['cod_fees'][$i]) )
					$message = __('There is the item where a value is dirty.', 'usces');
			}
		}
	}

	
	if( '' == $message ){
		$r = 'success';
		update_option('usces', $usces->options);
	}else{
		$r = '<span style="color:red;">' . $message . '</span>';
	}
	die( $r );
}

function usces_get_order_acting_data($rand){
	global $wpdb;

	$table_name = $wpdb->prefix . "usces_access";
	$query = $wpdb->prepare("SELECT acc_str1 AS sesid, acc_value AS order_data FROM $table_name WHERE acc_key = %s", $rand);
	$res = $wpdb->get_row($query, ARRAY_A);
	if($res == NULL){
		return false;
	}else{
		return $res;
	}
}

function usces_get_filename( $path ){
	$res = array();
	if ( $handle = opendir($path) ) {
		while (false !== ($file = readdir($handle))) {
			if( '.' != $file && '..' != $file )
				$res[] = $file;
		}
		closedir($handle);
	}
	return $res;	
}

function usces_locales(){
	$res = array();
	if ( $handle = opendir(USCES_PLUGIN_DIR.'/languages/') ) {
		while (false !== ($file = readdir($handle))) {
			if( '.' != $file && '..' != $file && preg_match('/^usces-(.+)\.mo$/', $file, $matches) ){
				$res[] = $matches[1];
			}
		}
		closedir($handle);
	}
	return $res;	
}

function usces_get_local_language(){
	$locale = get_locale();
	switch( $locale ){
		case '':
		case 'en':
		case 'en_US':
			$front_lang =  'en';
			break;
		case 'ja':
		case 'ja_JP':
			$front_lang =  'ja';
			break;
		default:
			$front_lang =  'others';
	}
	return $front_lang;
}

function usces_get_base_country(){
	global $usces_settings;
	$locale = get_locale();
	if( array_key_exists($locale, $usces_settings['lungage2country']) )
		return $usces_settings['lungage2country'][$locale];
	else
		return 'US';
}

function usces_get_local_addressform(){
	global $usces_settings;
	$base = usces_get_base_country();
	if( array_key_exists($base, $usces_settings['addressform']) )
		return $usces_settings['addressform'][$base];
	else
		return 'US';
}

function usces_get_local_target_market(){
	$base = usces_get_base_country();
	return (array)$base;
}

function usces_get_apply_addressform($country){
	global $usces_settings;
	return $usces_settings['addressform'][$country];
}

function usces_remove_filter(){
	global $usces, $post;

	if( is_single() && 'item' == $post->post_mime_type ) {
		remove_filter('the_content', array(&$usces, 'filter_itemPage'));

	}else if( $usces->is_cart_page($_SERVER['REQUEST_URI']) || $usces->is_inquiry_page($_SERVER['REQUEST_URI']) ){
		remove_action('the_post', array(&$usces, 'action_cartFilter'));
		remove_filter('the_title', array(&$usces, 'filter_cartTitle'),20);
		remove_filter('the_content', array(&$usces, 'filter_cartContent'),20);
		
	}else if( $usces->is_member_page($_SERVER['REQUEST_URI']) ){
		remove_action('the_post', array(&$usces, 'action_memberFilter'));
		remove_filter('the_title', array(&$usces, 'filter_memberTitle'),20);
		remove_filter('the_content', array(&$usces, 'filter_memberContent'),20);
	
	}else{
		remove_action('the_post', array(&$usces, 'goDefaultPage'));
	}
}

function usces_reset_filter(){
	global $usces, $post;

	if( is_single() && 'item' == $post->post_mime_type ) {
		add_filter('the_content', array(&$usces, 'filter_itemPage'));
		
	}else if( $usces->is_cart_page($_SERVER['REQUEST_URI']) || $usces->is_inquiry_page($_SERVER['REQUEST_URI']) ){
		add_action('the_post', array(&$usces, 'action_cartFilter'));
		add_filter('the_title', array(&$usces, 'filter_cartTitle'),20);
		add_filter('the_content', array(&$usces, 'filter_cartContent'),20);
		
	}else if( $usces->is_member_page($_SERVER['REQUEST_URI']) ){
		add_action('the_post', array(&$usces, 'action_memberFilter'));
		add_filter('the_title', array(&$usces, 'filter_memberTitle'),20);
		add_filter('the_content', array(&$usces, 'filter_memberContent'),20);
	
	}else{
		add_action('the_post', array(&$usces, 'goDefaultPage'));
	}
}


function usces_get_wcex(){
	if( defined('WCEX_DLSELLER_VERSION'))
		$wcex['DLSELLER'] = array('name'=>'Dl Seller', 'version'=>WCEX_DLSELLER_VERSION);
	if( defined('WCEX_ITEM_LIST_LAYOUT_VERSION'))
		$wcex['ITEM_LIST_LAYOUT'] = array('name'=>'Item List Layout', 'version'=>WCEX_ITEM_LIST_LAYOUT_VERSION);
	if( defined('WCEX_MOBILE_VERSION'))
		$wcex['MOBILE'] = array('name'=>'Mobile', 'version'=>WCEX_MOBILE_VERSION);
	if( defined('WCEX_MULTIPRICE_VERSION'))
		$wcex['MULTIPRICE'] = array('name'=>'Multi Price', 'version'=>WCEX_MULTIPRICE_VERSION);
	if( defined('WCEX_SLIDE_SHOWCASE_VERSION'))
		$wcex['SLIDE_SHOWCASE'] = array('name'=>'Slide Showcase', 'version'=>WCEX_SLIDE_SHOWCASE_VERSION);
	if( defined('WCEX_WIDGET_CART_VERSION'))
		$wcex['WIDGET_CART'] = array('name'=>'Widget Cart', 'version'=>WCEX_WIDGET_CART_VERSION);
		
	return $wcex;
}

function usces_trackPageview_cart($push){
	if(defined('USCES_KEY') && defined('USCES_MULTI') && true == USCES_MULTI){
		$push[] = "'_trackPageview','/" . USCES_KEY . "wc_cart'";
	}else{
		$push[] = "'_trackPageview','/wc_cart'";
	}
	return $push;
}

function usces_trackPageview_customer($push){
	if(defined('USCES_KEY') && defined('USCES_MULTI') && true == USCES_MULTI){
		$push[] = "'_trackPageview','/" . USCES_KEY . "wc_customer'";
	}else{
		$push[] = "'_trackPageview','/wc_customer'";
	}
	return $push;
}

function usces_trackPageview_delivery($push){
	if(defined('USCES_KEY') && defined('USCES_MULTI') && true == USCES_MULTI){
		$push[] = "'_trackPageview','/" . USCES_KEY . "wc_delivery'";
	}else{
		$push[] = "'_trackPageview','/wc_delivery'";
	}
	return $push;
}

function usces_trackPageview_confirm($push){
	if(defined('USCES_KEY') && defined('USCES_MULTI') && true == USCES_MULTI){
		$push[] = "'_trackPageview','/" . USCES_KEY . "wc_confirm'";
	}else{
		$push[] = "'_trackPageview','/wc_confirm'";
	}
	return $push;
}

function usces_trackPageview_ordercompletion($push){
	global $usces;
	$sesdata = $usces->cart->get_entry();
	$order_id = $sesdata['order']['ID'];
	$data = $usces->get_order_data($order_id, 'direct');
	$cart = unserialize($data['order_cart']);
	$total_price = $usces->get_total_price( $cart ) - $data['order_discount'];
	
	if(defined('USCES_KEY') && defined('USCES_MULTI') && true == USCES_MULTI){
		$push[] = "'_trackPageview','/" . USCES_KEY . "wc_ordercompletion'";
	}else{
		$push[] = "'_trackPageview','/wc_ordercompletion'";
	}
	$push[] = "'_addTrans', '" . $order_id . "', '" . get_bloginfo('name') . "', '" . $total_price . "', '" . $data['order_tax'] . "', '" . $data['order_shipping_charge'] . "', '" . $data['order_address1'].$data['order_address2'] . "', '" . $data['order_pref'] . "', '" . get_locale() . "'";
	for($i=0; $i<count($cart); $i++) { 
		$cart_row = $cart[$i];
		$post_id = $cart_row['post_id'];
		$sku = $cart_row['sku'];
		$quantity = $cart_row['quantity'];
//		$options = $cart_row['options'];
//		$advance = $usces->cart->wc_serialize($cart_row['advance']);
//		$itemCode = $usces->getItemCode($post_id);
		$itemName = $usces->getItemName($post_id);
//		$cartItemName = $usces->getCartItemName($post_id, $sku);
		$skuPrice = $cart_row['price'];
		$cats = $usces->get_item_cat_genre_ids( $post_id );
		if( is_array($cats) )
			sort($cats);
		$category = get_cat_name( $cats[0] );
		$push[] = "'_addItem', '" . $order_id . "', '" . $sku . "', '" . $itemName . "', '" . $category . "', '" . $skuPrice . "', '" . $quantity . "'";
	}
	$push[] = "'_trackTrans'";

	return $push;
}

function usces_trackPageview_error($push){
	if(defined('USCES_KEY') && defined('USCES_MULTI') && true == USCES_MULTI){
		$push[] = "'_trackPageview','/" . USCES_KEY . "wc_error'";
	}else{
		$push[] = "'_trackPageview','/wc_error'";
	}
	return $push;
}

function usces_trackPageview_member($push){
	if(defined('USCES_KEY') && defined('USCES_MULTI') && true == USCES_MULTI){
		$push[] = "'_trackPageview','/" . USCES_KEY . "wc_member'";
	}else{
		$push[] = "'_trackPageview','/wc_member'";
	}
	return $push;
}

function usces_trackPageview_login($push){
	if(defined('USCES_KEY') && defined('USCES_MULTI') && true == USCES_MULTI){
		$push[] = "'_trackPageview','/" . USCES_KEY . "wc_login'";
	}else{
		$push[] = "'_trackPageview','/wc_login'";
	}
	return $push;
}

function usces_trackPageview_editmemberform($push){
	if(defined('USCES_KEY') && defined('USCES_MULTI') && true == USCES_MULTI){
		$push[] = "'_trackPageview','/" . USCES_KEY . "wc_editmemberform'";
	}else{
		$push[] = "'_trackPageview','/wc_editmemberform'";
	}
	return $push;
}

function usces_trackPageview_newcompletion($push){
	if(defined('USCES_KEY') && defined('USCES_MULTI') && true == USCES_MULTI){
		$push[] = "'_trackPageview','/" . USCES_KEY . "wc_newcompletion'";
	}else{
		$push[] = "'_trackPageview','/wc_newcompletion'";
	}
	return $push;
}

function usces_trackPageview_newmemberform($push){
	if(defined('USCES_KEY') && defined('USCES_MULTI') && true == USCES_MULTI){
		$push[] = "'_trackPageview','/" . USCES_KEY . "wc_newmemberform'";
	}else{
		$push[] = "'_trackPageview','/wc_newmemberform'";
	}
	return $push;
}

function usces_trackPageview_deletemember($push){
	if(defined('USCES_KEY') && defined('USCES_MULTI') && true == USCES_MULTI){
		$push[] = "'_trackPageview','/" . USCES_KEY . "wc_deletemember'";
	}else{
		$push[] = "'_trackPageview','/wc_deletemember'";
	}
	return $push;
}

// uesces_addressform( $type, $data, $out = '' )
// $type = 'menber' or 'cuntomer' or 'delivery'
function uesces_addressform( $type, $data, $out = 'return' ){
	global $usces, $usces_settings;
	$options = get_option('usces');
	$applyform = usces_get_apply_addressform($options['system']['addressform']);
	$formtag = '';
	switch( $type ){
	case 'confirm':
	case 'member':
		$values =  $data;
		break;
	case 'customer':
	case 'delivery':
		$values = $data[$type];
		break;
	}
	$values['country'] = !empty($values['country']) ? $values['country'] : usces_get_local_addressform();
	
	if( 'confirm' == $type ){
	
		switch ($applyform){
		
		case 'JP':
			//20100818ysk start
			$formtag .= usces_custom_field_info($data, 'customer', 'name_pre', 'return');
			//20100818ysk end
			$formtag .= '<tr><th>'.__('Full name', 'usces').'</th><td>' . esc_html($values['customer']['name1']) . ' ' . esc_html($values['customer']['name2']) . '</td></tr>';
			$formtag .= '<tr><th>'.__('furigana', 'usces').'</th><td>' . esc_html($values['customer']['name3']) . ' ' . esc_html($values['customer']['name4']) . '</td></tr>';
			//20100818ysk start
			$formtag .= usces_custom_field_info($data, 'customer', 'name_after', 'return');
			//20100818ysk end
			$formtag .= '
			<tr><th>'.__('Zip/Postal Code', 'usces').'</th><td>' . esc_html($values['customer']['zipcode']) . '</td></tr>
			<tr><th>'.__('Country', 'usces').'</th><td>' . esc_html($usces_settings['country'][$values['customer']['country']]) . '</td></tr>
			<tr><th>'.__('Province', 'usces').'</th><td>' . esc_html($values['customer']['pref']) . '</td></tr>
			<tr><th>'.__('city', 'usces').'</th><td>' . esc_html($values['customer']['address1']) . '</td></tr>
			<tr><th>'.__('numbers', 'usces').'</th><td>' . esc_html($values['customer']['address2']) . '</td></tr>
			<tr><th>'.__('building name', 'usces').'</th><td>' . esc_html($values['customer']['address3']) . '</td></tr>
			<tr><th>'.__('Phone number', 'usces').'</th><td>' . esc_html($values['customer']['tel']) . '</td></tr>
			<tr><th>'.__('FAX number', 'usces').'</th><td>' . esc_html($values['customer']['fax']) . '</td></tr>';
			//20100818ysk start
			$formtag .= usces_custom_field_info($data, 'customer', 'fax_after', 'return');
			//20100818ysk end
			
			$shipping_address_info = '<tr class="ttl"><td colspan="2"><h3>'.__('Shipping address information', 'usces').'</h3></td></tr>';
			//20100818ysk start
			$shipping_address_info .= usces_custom_field_info($data, 'delivery', 'name_pre', 'return');
			//20100818ysk end
			$shipping_address_info .= '<tr><th>'.__('Full name', 'usces').'</th><td>' . esc_html($values['delivery']['name1']) . ' ' . esc_html($values['delivery']['name2']) . '</td></tr>';
			$shipping_address_info .= '<tr><th>'.__('furigana', 'usces').'</th><td>' . esc_html($values['delivery']['name3']) . ' ' . esc_html($values['delivery']['name4']) . '</td></tr>';
			//20100818ysk start
			$shipping_address_info .= usces_custom_field_info($values, 'delivery', 'name_after', 'return');
			//20100818ysk end
			$shipping_address_info .= '
			<tr><th>'.__('Zip/Postal Code', 'usces').'</th><td>' . esc_html($values['delivery']['zipcode']) . '</td></tr>
			<tr><th>'.__('Country', 'usces').'</th><td>' . esc_html($usces_settings['country'][$values['delivery']['country']]) . '</td></tr>
			<tr><th>'.__('Province', 'usces').'</th><td>' . esc_html($values['delivery']['pref']) . '</td></tr>
			<tr><th>'.__('city', 'usces').'</th><td>' . esc_html($values['delivery']['address1']) . '</td></tr>
			<tr><th>'.__('numbers', 'usces').'</th><td>' . esc_html($values['delivery']['address2']) . '</td></tr>
			<tr><th>'.__('building name', 'usces').'</th><td>' . esc_html($values['delivery']['address3']) . '</td></tr>
			<tr><th>'.__('Phone number', 'usces').'</th><td>' . esc_html($values['delivery']['tel']) . '</td></tr>
			<tr><th>'.__('FAX number', 'usces').'</th><td>' . esc_html($values['delivery']['fax']) . '</td></tr>';
			//20100818ysk start
			$shipping_address_info .= usces_custom_field_info($data, 'delivery', 'fax_after', 'return');
			//20100818ysk end
			$formtag .= apply_filters('usces_filter_shipping_address_info', $shipping_address_info);
			break;
			
		case 'US':
			//20100818ysk start
			$formtag .= usces_custom_field_info($data, 'customer', 'name_pre', 'return');
			//20100818ysk end
			$formtag .= '<tr><th>'.__('Full name', 'usces').'</th><td>' . esc_html($values['customer']['name2']) . ' ' . esc_html($values['customer']['name3']) . '</td></tr>';
			//20100818ysk start
			$formtag .= usces_custom_field_info($data, 'customer', 'name_after', 'return');
			//20100818ysk end
			$formtag .= '
			<tr><th>'.__('Address Line1', 'usces').'</th><td>' . esc_html($values['customer']['address2']) . '</td></tr>
			<tr><th>'.__('Address Line2', 'usces').'</th><td>' . esc_html($values['customer']['address3']) . '</td></tr>
			<tr><th>'.__('city', 'usces').'</th><td>' . esc_html($values['customer']['address1']) . '</td></tr>
			<tr><th>'.__('State', 'usces').'</th><td>' . esc_html($values['customer']['pref']) . '</td></tr>
			<tr><th>'.__('Country', 'usces').'</th><td>' . esc_html($usces_settings['country'][$values['customer']['country']]) . '</td></tr>
			<tr><th>'.__('Zip', 'usces').'</th><td>' . esc_html($values['customer']['zipcode']) . '</td></tr>
			<tr><th>'.__('Phone number', 'usces').'</th><td>' . esc_html($values['customer']['tel']) . '</td></tr>
			<tr><th>'.__('FAX number', 'usces').'</th><td>' . esc_html($values['customer']['fax']) . '</td></tr>';
			//20100818ysk start
			$formtag .= usces_custom_field_info($data, 'customer', 'fax_after', 'return');
			//20100818ysk end
			
			$shipping_address_info = '<tr class="ttl"><td colspan="2"><h3>'.__('Shipping address information', 'usces').'</h3></td></tr>';
			//20100818ysk start
			$shipping_address_info .= usces_custom_field_info($data, 'delivery', 'name_pre', 'return');
			//20100818ysk end
			$shipping_address_info .= '<tr><th>'.__('Full name', 'usces').'</th><td>' . esc_html($values['delivery']['name2']) . ' ' . esc_html($values['delivery']['name1']) . '</td></tr>';
			//20100818ysk start
			$shipping_address_info .= usces_custom_field_info($data, 'delivery', 'name_after', 'return');
			//20100818ysk end
			$shipping_address_info .= '
			<tr><th>'.__('Address Line1', 'usces').'</th><td>' . esc_html($values['delivery']['address2']) . '</td></tr>
			<tr><th>'.__('Address Line2', 'usces').'</th><td>' . esc_html($values['delivery']['address3']) . '</td></tr>
			<tr><th>'.__('city', 'usces').'</th><td>' . esc_html($values['delivery']['address1']) . '</td></tr>
			<tr><th>'.__('State', 'usces').'</th><td>' . esc_html($values['delivery']['pref']) . '</td></tr>
			<tr><th>'.__('Country', 'usces').'</th><td>' . esc_html($usces_settings['country'][$values['delivery']['country']]) . '</td></tr>
			<tr><th>'.__('Zip', 'usces').'</th><td>' . esc_html($values['delivery']['zipcode']) . '</td></tr>
			<tr><th>'.__('Phone number', 'usces').'</th><td>' . esc_html($values['delivery']['tel']) . '</td></tr>
			<tr><th>'.__('FAX number', 'usces').'</th><td>' . esc_html($values['delivery']['fax']) . '</td></tr>';
			//20100818ysk start
			$shipping_address_info .= usces_custom_field_info($data, 'delivery', 'fax_after', 'return');
			//20100818ysk end
			$formtag .= apply_filters('usces_filter_shipping_address_info', $shipping_address_info);
			break;
		}
		$res = apply_filters('usces_filter_apply_addressform_confirm', $formtag, $type, $data);
	
	}else{
	
		switch ($applyform){
		
		case 'JP':
			//20100818ysk start
			$formtag .= usces_custom_field_input($data, $type, 'name_pre', 'return');
			//20100818ysk end
			$formtag .= '<tr class="inp1">
			<th width="127" scope="row">' . usces_get_essential_mark('name1') . __('Full name', 'usces').'</th>
			<td width="257">'.__('Familly name', 'usces').'<input name="' . $type . '[name1]" id="name1" type="text" value="' . esc_attr($values['name1']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" /></td>
			<td width="257">'.__('Given name', 'usces').'<input name="' . $type . '[name2]" id="name2" type="text" value="' . esc_attr($values['name2']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" /></td>
			</tr>';
			$formtag .= '<tr class="inp1">
			<th scope="row">' . usces_get_essential_mark('name3').__('furigana', 'usces').'</th>
			<td>'.__('Familly name', 'usces').'<input name="' . $type . '[name3]" id="name3" type="text" value="' . esc_attr($values['name3']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" /></td>
			<td>'.__('Given name', 'usces').'<input name="' . $type . '[name4]" id="name4" type="text" value="' . esc_attr($values['name4']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" /></td>
			</tr>';
			//20100818ysk start
			$formtag .= usces_custom_field_input($data, $type, 'name_after', 'return');
			//20100818ysk end
			$formtag .= '<tr>
			<th scope="row">' . usces_get_essential_mark('zipcode').__('Zip/Postal Code', 'usces').'</th>
			<td colspan="2"><input name="' . $type . '[zipcode]" id="zipcode" type="text" value="' . esc_attr($values['zipcode']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />100-1000</td>
			</tr>
			<tr>
			<th scope="row">' . usces_get_essential_mark('country') . __('Country', 'usces') . '</th>
			<td colspan="2">' . uesces_get_target_market_form( $type, $values['country'] ) . '</td>
			</tr>
			<tr>
			<th scope="row">' . usces_get_essential_mark('states').__('Province', 'usces').'</th>
			<td colspan="2">' . usces_pref_select( $type, $values ) . '</td>
			</tr>
			<tr class="inp2">
			<th scope="row">' . usces_get_essential_mark('address1').__('city', 'usces').'</th>
			<td colspan="2"><input name="' . $type . '[address1]" id="address1" type="text" value="' . esc_attr($values['address1']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />'.__('Kitakami Yokohama', 'usces').'</td>
			</tr>
			<tr>
			<th scope="row">' . usces_get_essential_mark('address2').__('numbers', 'usces').'</th>
			<td colspan="2"><input name="' . $type . '[address2]" id="address2" type="text" value="' . esc_attr($values['address2']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />3-24-555</td>
			</tr>
			<tr>
			<th scope="row">' . usces_get_essential_mark('address3').__('building name', 'usces').'</th>
			<td colspan="2"><input name="' . $type . '[address3]" id="address3" type="text" value="' . esc_attr($values['address3']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />'.__('tuhanbuild 4F', 'usces').'</td>
			</tr>
			<tr>
			<th scope="row">' . usces_get_essential_mark('tel').__('Phone number', 'usces').'</th>
			<td colspan="2"><input name="' . $type . '[tel]" id="tel" type="text" value="' . esc_attr($values['tel']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />1000-10-1000</td>
			</tr>
			<tr>
			<th scope="row">' . usces_get_essential_mark('fax').__('FAX number', 'usces').'</th>
			<td colspan="2"><input name="' . $type . '[fax]" id="fax" type="text" value="' . esc_attr($values['fax']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />1000-10-1000</td>
			</tr>';
			//20100818ysk start
			$formtag .= usces_custom_field_input($data, $type, 'fax_after', 'return');
			//20100818ysk end
			break;
			
		case 'US':
			//20100818ysk start
			$formtag .= usces_custom_field_input($data, $type, 'name_pre', 'return');
			//20100818ysk end
			$formtag .= '<tr class="inp1">
			<th scope="row">' . usces_get_essential_mark('name1') . __('Full name', 'usces') . '</th>
			<td>' . __('Given name', 'usces') . '<input name="' . $type . '[name2]" id="name2" type="text" value="' . esc_attr($values['name2']) . '" /></td>
			<td>' . __('Familly name', 'usces') . '<input name="' . $type . '[name1]" id="name1" type="text" value="' . esc_attr($values['name1']) . '" /></td>
			</tr>';
			//20100818ysk start
			$formtag .= usces_custom_field_input($data, $type, 'name_after', 'return');
			//20100818ysk end
			$formtag .= '
			<tr>
			<th scope="row">' . usces_get_essential_mark('address2') . __('Address Line1', 'usces') . '</th>
			<td colspan="2">' . __('Street address', 'usces') . '<br /><input name="' . $type . '[address2]" id="address2" type="text" value="' . esc_attr($values['address2']) . '" /></td>
			</tr>
			<tr>
			<th scope="row">' . usces_get_essential_mark('address3') . __('Address Line2', 'usces') . '</th>
			<td colspan="2">' . __('Apartment, building, etc.', 'usces') . '<br /><input name="' . $type . '[address3]" id="address3" type="text" value="' . esc_attr($values['address3']) . '" /></td>
			</tr>
			<tr class="inp2">
			<th scope="row">' . usces_get_essential_mark('address1') . __('city', 'usces') . '</th>
			<td colspan="2"><input name="' . $type . '[address1]" id="address1" type="text" value="' . esc_attr($values['address1']) . '" /></td>
			</tr>
			<tr>
			<th scope="row">' . usces_get_essential_mark('states') . __('State', 'usces') . '</th>
			<td colspan="2">' . usces_pref_select( $type, $values ) . '</td>
			</tr>
			<tr>
			<th scope="row">' . usces_get_essential_mark('country') . __('Country', 'usces') . '</th>
			<td colspan="2">' . uesces_get_target_market_form( $type, $values['country'] ) . '</td>
			</tr>
			<tr>
			<th scope="row">' . usces_get_essential_mark('zipcode') . __('Zip', 'usces') . '</th>
			<td colspan="2"><input name="' . $type . '[zipcode]" id="zipcode" type="text" value="' . esc_attr($values['zipcode']) . '" /></td>
			</tr>
			<tr>
			<th scope="row">' . usces_get_essential_mark('tel') . __('Phone number', 'usces') . '</th>
			<td colspan="2"><input name="' . $type . '[tel]" id="tel" type="text" value="' . esc_attr($values['tel']) . '" /></td>
			</tr>
			<tr>
			<th scope="row">' . usces_get_essential_mark('fax') . __('FAX number', 'usces') . '</th>
			<td colspan="2"><input name="' . $type . '[fax]" id="fax" type="text" value="' . esc_attr($values['fax']) . '" /></td>
			</tr>';
			//20100818ysk start
			$formtag .= usces_custom_field_input($data, $type, 'fax_after', 'return');
			//20100818ysk end
			break;
		}
		$res = apply_filters('usces_filter_apply_addressform', $formtag, $type, $data);
	
	}

	if($out == 'return') {
		return $res;
	} else {
		echo $res;
	}
}

function usces_get_essential_mark( $type ){
	global $usces_essential_mark;
	do_action('usces_action_essential_mark');
	return $usces_essential_mark[$type];
}

function uesces_get_admin_addressform( $type, $data, $customdata, $out = 'return' ){
	global $usces, $usces_settings;
	$options = get_option('usces');
	$applyform = usces_get_apply_addressform($options['system']['addressform']);
	$formtag = '';
	switch( $type ){
	case 'member':
		$values = array('name1'=>$data['mem_name1'], 'name2'=>$data['mem_name2'], 'name3'=>$data['mem_name3'], 'name4'=>$data['mem_name4'], 'zipcode'=>$data['mem_zip'], 'address1'=>$data['mem_address1'], 'address2'=>$data['mem_address2'], 'address3'=>$data['mem_address3'], 'tel'=>$data['mem_tel'], 'fax'=>$data['mem_fax'], 'pref'=>$data['mem_pref']);
		$country = $usces->get_member_meta_value('customer_country', $data['ID']);
		$values['country'] = !empty($country) ? $country : usces_get_local_addressform();
		break;
	case 'customer':
		$values = array('name1'=>$data['order_name1'], 'name2'=>$data['order_name2'], 'name3'=>$data['order_name3'], 'name4'=>$data['order_name4'], 'zipcode'=>$data['order_zip'], 'address1'=>$data['order_address1'], 'address2'=>$data['order_address2'], 'address3'=>$data['order_address3'], 'tel'=>$data['order_tel'], 'fax'=>$data['order_fax'], 'pref'=>$data['order_pref']);
		$country = $usces->get_order_meta_value('customer_country', $data['ID']);
		$values['country'] = !empty($country) ? $country : usces_get_local_addressform();
		break;
	case 'delivery':
		$values = $data;
		break;
	}
	
	switch ($applyform){
	
	case 'JP': 
		//20100818ysk start
		$formtag .= usces_admin_custom_field_input($customdata, $type, 'name_pre', 'return');
		//20100818ysk end
		$formtag .= '
		<tr>
			<td class="label">' . __('name', 'usces') . '</td>
			<td class="col2"><input name="' . $type . '[name1]" type="text" class="text short" value="' . esc_attr($values['name1']) . '" /><input name="' . $type . '[name2]" type="text" class="text short" value="' . esc_attr($values['name2']) . '" /></td>
		</tr>
		<tr>
			<td class="label">' . __('furigana', 'usces') . '</td>
			<td class="col2"><input name="' . $type . '[name3]" type="text" class="text short" value="' . esc_attr($values['name3']) . '" /><input name="' . $type . '[name4]" type="text" class="text short" value="' . esc_attr($values['name4']) . '" /></td>
		</tr>';
		//20100818ysk start
		$formtag .= usces_admin_custom_field_input($customdata, $type, 'name_after', 'return');
		//20100818ysk end
		$formtag .= '
		<tr>
			<td class="label">' . __('Zip/Postal Code', 'usces') . '</td>
			<td class="col2"><input name="' . $type . '[zipcode]" type="text" class="text short" value="' . esc_attr($values['zipcode']) . '" /></td>
		</tr>
		<tr>
			<td class="label">' . __('Country', 'usces') . '</td>
			<td class="col2">' . uesces_get_target_market_form( $type, $values['country'] ) . '</td>
		</tr>
		<tr>
			<td class="label">' . __('Province', 'usces') . '</td>
			<td class="col2">';
			
		$formtag .= usces_pref_select( $type, $values );
			
		$formtag .= '</td>
		</tr>';
		$formtag .= '
		<tr>
			<td class="label">' . __('city', 'usces') . '</td>
			<td class="col2"><input name="' . $type . '[address1]" type="text" class="text long" value="' . esc_attr($values['address1']) . '" /></td>
		</tr>
		<tr>
			<td class="label">' . __('numbers', 'usces') . '</td>
			<td class="col2"><input name="' . $type . '[address2]" type="text" class="text long" value="' . esc_attr($values['address2']) . '" /></td>
		</tr>
		<tr>
			<td class="label">' . __('building name', 'usces') . '</td>
			<td class="col2"><input name="' . $type . '[address3]" type="text" class="text long" value="' . esc_attr($values['address3']) . '" /></td>
		</tr>
		<tr>
			<td class="label">' . __('Phone number', 'usces') . '</td>
			<td class="col2"><input name="' . $type . '[tel]" type="text" class="text long" value="' . esc_attr($values['tel']) . '" /></td>
		</tr>
		<tr>
			<td class="label">' . __('FAX number', 'usces') . '</td>
			<td class="col2"><input name="' . $type . '[fax]" type="text" class="text long" value="' . esc_attr($values['fax']) . '" /></td>
		</tr>';
		//20100818ysk start
		$formtag .= usces_admin_custom_field_input($customdata, $type, 'fax_after', 'return');
		//20100818ysk end
		break;
		
	case 'US':
	default:
		//20100818ysk start
		$formtag .= usces_admin_custom_field_input($customdata, $type, 'name_pre', 'return');
		//20100818ysk end
		$formtag .= '
		<tr>
			<td class="label">' . __('name', 'usces') . '</td>
			<td class="col2"><input name="' . $type . '[name2]" type="text" class="text short" value="' . esc_attr($values['name2']) . '" /><input name="' . $type . '[name1]" type="text" class="text short" value="' . esc_attr($values['name1']) . '" /></td>
		</tr>';
		//20100818ysk start
		$formtag .= usces_admin_custom_field_input($customdata, $type, 'name_after', 'return');
		//20100818ysk end
		$formtag .= '
		<tr>
			<td class="label">' . __('Address Line1', 'usces') . '</td>
			<td class="col2"><input name="' . $type . '[address2]" type="text" class="text long" value="' . esc_attr($values['address2']) . '" /></td>
		</tr>
		<tr>
			<td class="label">' . __('Address Line2', 'usces') . '</td>
			<td class="col2"><input name="' . $type . '[address3]" type="text" class="text long" value="' . esc_attr($values['address3']) . '" /></td>
		</tr>
		<tr>
			<td class="label">' . __('city', 'usces') . '</td>
			<td class="col2"><input name="' . $type . '[address1]" type="text" class="text long" value="' . esc_attr($values['address1']) . '" /></td>
		</tr>
		<tr>
			<td class="label">' . __('State', 'usces') . '</td>
			<td class="col2">';
			
		$formtag .= usces_pref_select( $type, $values );
			
		$formtag .= '</td>
		</tr>';
		$formtag .= '
		<tr>
			<td class="label">' . __('Country', 'usces') . '</td>
			<td class="col2">' . uesces_get_target_market_form( $type, $values['country'] ) . '</td>
		</tr>
		<tr>
			<td class="label">' . __('Zip', 'usces') . '</td>
			<td class="col2"><input name="' . $type . '[zipcode]" type="text" class="text short" value="' . esc_attr($values['zipcode']) . '" /></td>
		</tr>
		<tr>
			<td class="label">' . __('Phone number', 'usces') . '</td>
			<td class="col2"><input name="' . $type . '[tel]" type="text" class="text long" value="' . esc_attr($values['tel']) . '" /></td>
		</tr>
		<tr>
			<td class="label">' . __('FAX number', 'usces') . '</td>
			<td class="col2"><input name="' . $type . '[fax]" type="text" class="text long" value="' . esc_attr($values['fax']) . '" /></td>
		</tr>';
		//20100818ysk start
		$formtag .= usces_admin_custom_field_input($customdata, $type, 'fax_after', 'return');
		//20100818ysk end
		break;
	}
	$res = apply_filters('usces_filter_apply_admin_addressform', $formtag, $type, $data, $customdata);
	

	if($out == 'return') {
		return $res;
	} else {
		echo $res;
	}
}

function uesces_get_mail_addressform( $type, $data, $order_id, $out = 'return' ){
	global $usces, $usces_settings;
	$options = get_option('usces');
	$applyform = usces_get_apply_addressform($options['system']['addressform']);
	$formtag = '';
	switch( $type ){
	case 'admin_mail':
		$values = $data;
		$values['country'] = !empty($values['country']) ? $values['country'] : usces_get_local_addressform();
		break;
	case 'order_mail':
		$values = $data['delivery'];
		$values['country'] = !empty($values['country']) ? $values['country'] : usces_get_local_addressform();
		break;
	}
	
	switch ($applyform){
	
	case 'JP': 
		//20110118ysk start
		$formtag .= usces_mail_custom_field_info( 'delivery', 'name_pre', $order_id );
		//20110118ysk end
		$formtag .= __('A destination name','usces') . "    : " . sprintf(__('Mr/Mrs %s', 'usces'), ($values['name1'] . ' ' . $values['name2'])) . " \r\n";
		//20110118ysk start
		$formtag .= usces_mail_custom_field_info( 'delivery', 'name_after', $order_id );
		//20110118ysk end
		$formtag .= __('Country','usces') . "    : " . $usces_settings['country'][$values['country']] . "\r\n";
		$formtag .= __('Zip/Postal Code','usces') . "  : " . $values['zipcode'] . "\r\n";
		$formtag .= __('Address','usces') . "    : " . $values['pref'] . $values['address1'] . $values['address2'] . " " . $values['address3'] . "\r\n";
		$formtag .= __('Phone number','usces') . "  : " . $values['tel'] . "\r\n";
		$formtag .= __('FAX number','usces') . "  : " . $values['fax'] . "\r\n";
		//20110118ysk start
		$formtag .= usces_mail_custom_field_info( 'delivery', 'fax_after', $order_id );
		//20110118ysk end
		break;
		
	case 'US':
	default:
		//20110118ysk start
		$formtag .= usces_mail_custom_field_info( 'delivery', 'name_pre', $order_id );
		//20110118ysk end
		$formtag .= __('A destination name','usces') . "    : " . sprintf(__('Mr/Mrs %s', 'usces'), ($values['name2'] . ' ' . $values['name1'])) . " \r\n";
		//20110118ysk start
		$formtag .= usces_mail_custom_field_info( 'delivery', 'name_after', $order_id );
		//20110118ysk end
		$formtag .= __('Address','usces') . "    : " . $values['address2'] . " " . $values['address3'] . "\r\n";
		$formtag .= __('City','usces') . "    : " . $values['address1'] . "\r\n";
		$formtag .= __('State','usces') . "    : " . $values['pref'] . "\r\n";
		$formtag .= __('Country','usces') . "    : " . $usces_settings['country'][$values['country']] . "\r\n";
		$formtag .= __('Zip/Postal Code','usces') . "  : " . $values['zipcode'] . "\r\n";
		$formtag .= __('Phone number','usces') . "  : " . $values['tel'] . "\r\n";
		$formtag .= __('FAX number','usces') . "  : " . $values['fax'] . "\r\n";
		//20110118ysk start
		$formtag .= usces_mail_custom_field_info( 'delivery', 'fax_after', $order_id );
		//20110118ysk end
		break;
	}
	$res = apply_filters('usces_filter_apply_mail_addressform', $formtag, $type, $data, $order_id);
	

	if($out == 'return') {
		return $res;
	} else {
		echo $res;
	}
}

function uesces_get_target_market_form( $type, $selected, $out = 'return' ){
	global $usces_settings;
	$options = get_option('usces');
	$res = '<select name="' . $type . '[country]" id="' . $type . '_country">'."\n";
	foreach ( $usces_settings['country'] as $key => $value ){
		if( in_array($key, $options['system']['target_market']) )
			$res .= '<option value="' . $key . '"' . ($selected == $key ? ' selected="selected"' : '') . '>' . $value . "</option>\n";
	}
	$res .= '</select>'."\n";
	
	if($out == 'return') {
		return $res;
	} else {
		echo $res;
	}
}

function usces_pref_select( $type, $values, $out = 'return' ){
	global $usces, $usces_states;
	
//20110513ysk start
	//$country = empty($values['country']) ? usces_get_local_addressform() : $values['country'];
	$country = empty($values['country']) ? usces_get_base_country() : $values['country'];
	$options = get_option('usces');
	if( !in_array($country, $options['system']['target_market']) )
		$country = $options['system']['target_market'][0];
//20110513ysk end
//20110331ysk start
	//$prefs = $usces_states[$country];
	$prefs = get_usces_states($country);
//20110331ysk end
	$html = '<select name="' . esc_attr($type . '[pref]') . '" id="' . esc_attr($type) . '_pref" class="pref">';
	foreach((array)$prefs as $pref)
		$html .= "\t".'<option value="' . esc_attr($pref) . '"' . ($pref == $values['pref'] ? ' selected="selected"' : '') . '>' . esc_html($pref) . "</option>\n";
	$html .= "</select>\n";
	
	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

function usces_is_member_system(){
	global $usces;
	if($usces->options['membersystem_state'] == 'activate')
		return true;
	else
		return false;
}

function usces_is_member_system_point(){
	global $usces;
	if($usces->options['membersystem_point'] == 'activate')
		return true;
	else
		return false;
}

function usces_shipping_country_option( $selected ){
	global $usces_settings;
	$options = get_option('usces');
	$res = '';
	foreach ( $usces_settings['country'] as $key => $value ){
		if( in_array($key, $options['system']['target_market']) )
			$res .= '<option value="' . $key . '"' . ($selected == $key ? ' selected="selected"' : '') . '>' . $value . "</option>\n";
	}
//20110317ysk start
	//$res .= '<option value="world_wide"' . ($selected == $key ? ' selected="selected"' : '') . '>World Wide' . "</option>\n";
//20110317ysk end
	if($out == 'return') {
		return $res;
	} else {
		echo $res;
	}
}

function usces_get_cart_rows( $out = '' ) {
	global $usces;
	$cart = $usces->cart->get_cart();
	$usces_gp = 0;
	$res = '';
	
	for($i=0; $i<count($cart); $i++) { 
		$cart_row = $cart[$i];
		$post_id = $cart_row['post_id'];
		$sku = esc_attr($cart_row['sku']);
		$quantity = $cart_row['quantity'];
		$options = $cart_row['options'];
		$advance = $usces->cart->wc_serialize($cart_row['advance']);
		$itemCode = $usces->getItemCode($post_id);
		$itemName = $usces->getItemName($post_id);
		$cartItemName = $usces->getCartItemName($post_id, $cart_row['sku']);
		$itemRestriction = $usces->getItemRestriction($post_id);
		$skuPrice = $cart_row['price'];
		$skuZaikonum = $usces->getItemZaikonum($post_id, $cart_row['sku']);
		$stockid = $usces->getItemZaikoStatusId($post_id, $cart_row['sku']);
		$stock = $usces->getItemZaiko($post_id, $cart_row['sku']);
		$red = (in_array($stock, array(__('sellout','usces'), __('Out Of Stock','usces'), __('Out of print','usces')))) ? 'class="signal_red"' : '';
		$pictid = $usces->get_mainpictid($itemCode);
		if ( empty($options) ) {
			$optstr =  '';
			$options =  array();
		}

		$res .= '<tr>
			<td>' . ($i + 1) . '</td>
			<td>';
			$cart_thumbnail = '<a href="' . get_permalink($post_id) . '">' . wp_get_attachment_image( $pictid, array(60, 60), true ) . '</a>';
			$res .= apply_filters('usces_filter_cart_thumbnail', $cart_thumbnail, $post_id, $pictid, $i);
			$res .= '</td><td class="aleft">' . esc_html($cartItemName) . '<br />';
		if( is_array($options) && count($options) > 0 ){
			$optstr = '';
			foreach($options as $key => $value){
//20110629ysk start 0000190
				//if( !empty($key) )
				//	$res .= esc_html($key) . ' : ' . nl2br(esc_html(urldecode($value))) . "<br />\n"; 
				if( !empty($key) ) {
					if(is_array($value)) {
						$c = '';
						$optstr .= esc_html($key) . ' : '; 
						foreach($value as $v) {
							$optstr .= $c.esc_html(nl2br(esc_html(urldecode($v))));
							$c = ', ';
						}
						$optstr .= "<br />\n"; 
					} else {
						$optstr .= esc_html($key) . ' : ' . nl2br(esc_html(urldecode($value))) . "<br />\n"; 
					}
				}
//20110629ysk end
			}
			$res .= apply_filters( 'usces_filter_option_cart', $optstr, $options);
		}
		$res .= '</td>
			<td class="aright">';
		if( usces_is_gptekiyo($post_id, $cart_row['sku'], $quantity) ) {
			$usces_gp = 1;
			$Business_pack_mark = '<img src="' . get_template_directory_uri() . '/images/gp.gif" alt="' . __('Business package discount','usces') . '" /><br />';
			$res .= apply_filters('usces_filter_itemGpExp_cart_mark', $Business_pack_mark);
		}
		$res .= usces_crform($skuPrice, true, false, 'return') . '
			</td>
			<td><input name="quant[' . $i . '][' . $post_id . '][' . $sku . ']" class="quantity" type="text" value="' . esc_attr($cart_row['quantity']) . '" /></td>
			<td class="aright">' . usces_crform(($skuPrice * $cart_row['quantity']), true, false, 'return') . '</td>
			<td ' . $red . '>' . $stock . '</td>
			<td>';
		foreach($options as $key => $value){
//20110629ysk start 0000190
			//$res .= '<input name="itemOption[' . $i . '][' . $post_id . '][' . $sku . '][' . $key . ']" type="hidden" value="' . $value . '" />';
			if(is_array($value)) {
				foreach($value as $v) {
					$res .= '<input name="itemOption[' . $i . '][' . $post_id . '][' . $sku . '][' . $key . '][' . $v . ']" type="hidden" value="' . $v . '" />';
				}
			} else {
				$res .= '<input name="itemOption[' . $i . '][' . $post_id . '][' . $sku . '][' . $key . ']" type="hidden" value="' . $value . '" />';
			}
//20110629ysk end
		}
		$res .= '<input name="itemRestriction[' . $i . ']" type="hidden" value="' . $itemRestriction . '" />
			<input name="stockid[' . $i . ']" type="hidden" value="' . $stockid . '" />
			<input name="itempostid[' . $i . ']" type="hidden" value="' . $post_id . '" />
			<input name="itemsku[' . $i . ']" type="hidden" value="' . $sku . '" />
			<input name="zaikonum[' . $i . '][' . $post_id . '][' . $sku . ']" type="hidden" value="' . esc_attr($skuZaikonum) . '" />
			<input name="skuPrice[' . $i . '][' . $post_id . '][' . $sku . ']" type="hidden" value="' . esc_attr($skuPrice) . '" />
			<input name="advance[' . $i . '][' . $post_id . '][' . $sku . ']" type="hidden" value="' . esc_attr($advance) . '" />
			<input name="delButton[' . $i . '][' . $post_id . '][' . $sku . ']" class="delButton" type="submit" value="' . __('Delete','usces') . '" />
			</td>
		</tr>';
	}
	
	if($out == 'return'){
		return $res;
	}else{
		echo $res;
	}
}
function usces_get_confirm_rows( $out = '' ) {
	global $usces, $usces_members, $usces_entries;
//	$usces_members = $usces->get_member();
	$memid = ( empty($usces_members['ID']) ) ? 999999999 : $usces_members['ID'];
//	$usces_entries = $usces->cart->get_entry();
	$usces->set_cart_fees( $usces_members, $usces_entries );
	
	$cart = $usces->cart->get_cart();
	$res = '';
	for($i=0; $i<count($cart); $i++) { 
		$cart_row = $cart[$i];
		$post_id = $cart_row['post_id'];
		$sku = esc_attr($cart_row['sku']);
		$quantity = $cart_row['quantity'];
		$options = $cart_row['options'];
		$itemCode = $usces->getItemCode($post_id);
		$itemName = $usces->getItemName($post_id);
		$cartItemName = $usces->getCartItemName($post_id, $cart_row['sku']);
		$skuPrice = $cart_row['price'];
		$pictid = $usces->get_mainpictid($itemCode);
		if (empty($options)) {
			$optstr =  '';
			$options =  array();
		}
	
		 $res .= '<tr>
			<td>' . ($i + 1) . '</td>
			<td>';
		$cart_thumbnail = wp_get_attachment_image( $pictid, array(60, 60), true );
		 $res .= apply_filters('usces_filter_cart_thumbnail', $cart_thumbnail, $post_id, $pictid, $i);
		 $res .= '</td><td class="aleft">' . $cartItemName . '<br />';
		if( is_array($options) && count($options) > 0 ){
			$optstr = '';
			foreach($options as $key => $value){
//20110629ysk start 0000190
				//if( !empty($key) )
				//	 $res .= esc_html($key) . ' : ' . nl2br(esc_html(urldecode($value))) . "<br />\n"; 
				if( !empty($key) ) {
					if(is_array($value)) {
						$c = '';
						$optstr .= esc_html($key) . ' : '; 
						foreach($value as $v) {
							$optstr .= $c.esc_html(nl2br(esc_html(urldecode($v))));
							$c = ', ';
						}
						$optstr .= "<br />\n"; 
					} else {
						$optstr .= esc_html($key) . ' : ' . nl2br(esc_html(urldecode($value))) . "<br />\n"; 
					}
				}
//20110629ysk end
			}
			$res .= apply_filters( 'usces_filter_option_confirm', $optstr, $options);
		}
		 $res .= '</td>
			<td class="aright">' . usces_crform($skuPrice, true, false, 'return') . '</td>
			<td>' . $cart_row['quantity'] . '</td>
			<td class="aright">' . usces_crform(($skuPrice * $cart_row['quantity']), true, false, 'return') . '</td>
			<td>';
		 $res = apply_filters('usces_additional_confirm',  $res, array($i, $post_id, $cart_row['sku']));
		 $res .= '</td>
		</tr>';
	} 
	
	if($out == 'return'){
		return $res;
	}else{
		echo $res;
	}
}

function usces_get_cart_button( $out = '' ) {
	global $usces;
	$res = '';
	
	if($usces->use_js){
		$res .= '<input name="previous" type="button" id="previouscart" class="continue_shopping_button" value="' . __('continue shopping','usces') . '"' . apply_filters('usces_filter_cart_prebutton', ' onclick="uscesCart.previousCart();"') . ' />&nbsp;&nbsp;';
		if( usces_is_cart() ) {
			$res .= '<input name="customerinfo" type="submit" class="to_customerinfo_button" value="' . __(' Next ','usces') . '"' . apply_filters('usces_filter_cart_nextbutton', ' onclick="return uscesCart.cartNext();"') . ' />';
		}
	}else{
		$res .= '<a href="' . get_bloginfo('home') . '" class="continue_shopping_button">' . __('continue shopping','usces') . '</a>&nbsp;&nbsp;';
		if( usces_is_cart() ) {
			$res .= '<input name="customerinfo" type="submit" class="to_customerinfo_button" value="' . __(' Next ','usces') . '"' . apply_filters('usces_filter_cart_nextbutton', NULL) . ' />';
		}
	}
	
	if($out == 'return'){
		return $res;
	}else{
		echo $res;
	}
}

function usces_get_customer_button( $out = '' ) {
	global $usces, $member_regmode;
	$res = '';
	
	$res = '<input name="backCart" type="submit" class="back_cart_button" value="'.__('Back', 'usces').'" />&nbsp;&nbsp;';
	
	$button = '<input name="deliveryinfo" type="submit" class="to_deliveryinfo_button" value="'.__(' Next ', 'usces').'" />&nbsp;&nbsp;';
	$res .= apply_filters('usces_filter_customer_button', $button);
	
	if(usces_is_membersystem_state() && $member_regmode != 'editmemberfromcart' && usces_is_login() == false ){
		$res .= '<input name="reganddeliveryinfo" type="submit" class="to_reganddeliveryinfo_button" value="'.__('To the next while enrolling', 'usces').'"' . apply_filters('usces_filter_customerinfo_prebutton', NULL) . ' />';
	}elseif(usces_is_membersystem_state() && $member_regmode == 'editmemberfromcart' ){
		$res .= '<input name="reganddeliveryinfo" type="submit" class="to_reganddeliveryinfo_button" value="'.__('Revise member information, and to next', 'usces').'"' . apply_filters('usces_filter_customerinfo_nextbutton', NULL) . ' />';
	}
	
	if($out == 'return'){
		return $res;
	}else{
		echo $res;
	}
}

function usces_delivery_secure_form( $out = '' ) {
	global $usces, $usces_entries, $usces_carts;
	$thml = '';
	include( USCES_PLUGIN_DIR . "/includes/delivery_secure_form.php");

	if($out == 'return'){
		return $html;
	}else{
		echo $html;
	}
}

function usces_delivery_info_script( $out ='' ){
	global $usces, $usces_entries, $usces_carts;
	$thml = '';
	include( USCES_PLUGIN_DIR . "/includes/delivery_info_script.php");

	if($out == 'return'){
		return $html;
	}else{
		echo $html;
	}
}

function usces_get_entries(){
	global $usces, $usces_entries;
	$usces_entries = $usces->cart->get_entry();
}

function usces_get_carts(){
	global $usces, $usces_carts;
	$usces_carts = $usces->cart->get_cart();
}

function usces_get_members(){
	global $usces, $usces_members;
	$usces_members = $usces->get_member();
}

function usces_error_message( $out = ''){
	global $usces;
	
	if($out == 'return'){
		return $usces->error_message;
	}else{
		echo $usces->error_message;
	}
}

function usces_url( $type, $out = ''){
	global $usces;
	
	switch ( $type ){
		case 'cart':
			$url = USCES_CART_URL;
			break;
		case 'login':
			$url = USCES_LOGIN_URL;
			break;
		case 'member':
			$url = USCES_MEMBER_URL;
			break;
		case 'newmember':
			$url = USCES_NEWMEMBER_URL;
			break;
		case 'lostmemberpassword':
			$url = USCES_LOSTMEMBERPASSWORD_URL;
			break;
		case 'cartnonsession':
			$url = USCES_CART_NONSESSION_URL;
			break;
	}
	
	if($out == 'return'){
		return $url;
	}else{
		echo $url;
	}
}

function usces_total_price( $out = ''){
	global $usces;
	
	if($out == 'return'){
		return $usces->get_total_price();
	}else{
		echo $usces->get_total_price();
	}
}

function usces_completion_settlement( $out ='' ){
	global $usces;
	$html = '';
	require( USCES_PLUGIN_DIR . "/includes/completion_settlement.php");
	
	if($out == 'return'){
		return $html;
	}else{
		echo $html;
	}
}

function usces_purchase_button( $out ='' ){
	global $usces, $usces_entries;
	$html = '';
	include( USCES_PLUGIN_DIR . "/includes/purchase_button.php");

	if($out == 'return'){
		return $html;
	}else{
		echo $html;
	}
}

function usces_get_member_regmode(){
	global $member_regmode;
	$member_regmode = isset( $_SESSION['usces_entry']['member_regmode'] ) ? $_SESSION['usces_entry']['member_regmode'] : 'none'; 
}

function uesces_get_error_settlement( $out = '' ) {
	$res = '';
	if( isset($_REQUEST['acting']) && ('zeus_conv' == $_REQUEST['acting'] || 'zeus_card' == $_REQUEST['acting'] || 'zeus_bank' == $_REQUEST['acting'] ) ){ //ZEUS
		$res .= '<div class="support_box">ゼウス・カスタマーサポート(24時間365日)<br />
		電話番号：0570-02-3939(つながらないときは 03-4334-0500)<br />
		E-mail:support@cardservice.co.jp
		</div>'."\n";
	}
	
	if($out == 'return'){
		return $res;
	}else{
		echo $res;
	}
}

function usces_page_name( $out = '') {
	global $usces;
	$page = '';
	
	if( empty($usces->page) ){
		if( $usces->is_cart_page($_SERVER['REQUEST_URI']) ){
			$page = 'cart';
		}elseif( $usces->is_member_page($_SERVER['REQUEST_URI']) ){
			$page = 'member';
		}
	}else{
			$page = $usces->page;
	}
	
	if($out == 'return'){
		return $page;
	}else{
		echo $page;
	}
}

function usces_post_reg_orderdata($order_id, $results){
	global $usces, $wpdb;
	//$entry = $usces->cart->get_entry();//20110621ysk 0000184
	$acting = $_GET['acting'];
	$data = array();

	if( $order_id ){

		switch ( $acting ) {
			case 'epsilon':
				$trans_id = $_REQUEST['trans_code'];
				break;
			case 'paypal_ipn':
				$trans_id = $_REQUEST['txn_id'];
				break;
			case 'paypal':
				$trans_id = $results['txn_id'];
				break;
//20110208ysk start
			case 'paypal_ec':
				$trans_id = $_REQUEST['token'];
//20110621ysk start 0000184
/*
//20110412ysk start
				$cart = $usces->cart->get_cart();
				$post_id = $cart[0]['post_id'];
				$charging_type = $usces->getItemChargingType($post_id);
				if( 'continue' != $charging_type) {
					//通常購入
//20110412ysk end
					//Format the other parameters that were stored in the session from the previous calls
					$paymentAmount = usces_crform($entry['order']['total_full_price'], false, false, 'return', false);
					$token = urlencode($_REQUEST['token']);
					$paymentType = urlencode("Sale");
					$currencyCodeType = urlencode($usces->get_currency_code());
					$payerID = urlencode($_REQUEST['PayerID']);
					$serverName = urlencode($_SERVER['SERVER_NAME']);

					$nvpstr = '&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTACTION='.$paymentType.'&AMT='.$paymentAmount.'&CURRENCYCODE='.$currencyCodeType.'&IPADDRESS='.$serverName;

					$usces->paypal->setMethod('DoExpressCheckoutPayment');
					$usces->paypal->setData($nvpstr);
					$res = $usces->paypal->doExpressCheckout();
					$resArray = $usces->paypal->getResponse();
					$ack = strtoupper($resArray["ACK"]);
					if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {
						$transactionId = $resArray["TRANSACTIONID"]; // ' Unique transaction ID of the payment. Note:  If the PaymentAction of the request was Authorization or Order, this value is your AuthorizationID for use with the Authorization & Capture APIs. 
						$usces->set_order_meta_value('settlement_id', $transactionId, $order_id);

					} else {
						//Display a user friendly Error on the page using any of the following error information returned by PayPal
						$ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
						$ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
						$ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
						$ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);
						usces_log('PayPal : DoExpressCheckoutPayment API call failed. Error Code:['.$ErrorCode.'] Error Severity Code:['.$ErrorSeverityCode.'] Short Error Message:'.$ErrorShortMsg.' Detailed Error Message:'.$ErrorLongMsg, 'acting_transaction.log');
					}
//20110412ysk start
				} else {
					//定期支払い
					$paymentAmount = usces_crform($entry['order']['total_items_price'], false, false, 'return', false);
					$token = urlencode($_REQUEST['token']);
					$currencyCodeType = urlencode($usces->get_currency_code());
					$nextdate = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
					$profileStartDate = date('Y-m-d', mktime(0,0,0,substr($nextdate, 5, 2)+1,$usces->getItemChargingDay($post_id),substr($nextdate, 0, 4))).'T01:01:01Z';
					$billingPeriod = urlencode("Month");// or "Day", "Week", "SemiMonth", "Year"
					$billingFreq = urlencode($usces->getItemFrequency($post_id));
					//$totalbillingCycles = (empty($dlitem['dlseller_interval'])) ? '' : '&TOTALBILLINGCYCLES='.urlencode($dlitem['dlseller_interval']);
					$desc = urlencode(usces_make_agreement_description($cart, $entry['order']['total_items_price']));

					$nvpstr = '&TOKEN='.$token.'&AMT='.$paymentAmount.'&CURRENCYCODE='.$currencyCodeType.'&PROFILESTARTDATE='.$profileStartDate.'&BILLINGPERIOD='.$billingPeriod.'&BILLINGFREQUENCY='.$billingFreq.'&DESC='.$desc;

					$usces->paypal->setMethod('CreateRecurringPaymentsProfile');
					$usces->paypal->setData($nvpstr);
					$res = $usces->paypal->doExpressCheckout();
					$resArray = $usces->paypal->getResponse();
					$ack = strtoupper($resArray["ACK"]);
					if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {
						$profileid = $resArray["PROFILEID"];
						$usces->set_order_meta_value('profile_id', $profileid, $order_id);

					} else {
						//Display a user friendly Error on the page using any of the following error information returned by PayPal
						$ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
						$ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
						$ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
						$ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);
						usces_log('PayPal : CreateRecurringPaymentsProfile API call failed. Error Code:['.$ErrorCode.'] Error Severity Code:['.$ErrorSeverityCode.'] Short Error Message:'.$ErrorShortMsg.' Detailed Error Message:'.$ErrorLongMsg, 'acting_transaction.log');
					}
				}
//20110412ysk end
*/
				if(isset($results['settlement_id'])) 
					$usces->set_order_meta_value('settlement_id', $results['settlement_id'], $order_id);
				if(isset($results['profile_id'])) 
					$usces->set_order_meta_value('profile_id', $results['profile_id'], $order_id);
//20110621ysk end
				break;
//20110208ysk end
			case 'zeus_card':
				$trans_id = $_REQUEST['ordd'];
				foreach($_GET as $key => $value) {
					$data[$key] = mysql_real_escape_string($value);
				}
				$usces->set_order_meta_value('acting_'.$acting, serialize($data), $order_id);
				if( $usces->is_member_logged_in() )
					$usces->set_member_meta_value('zeus_pcid', '8888888888888888');
				usces_log('zeus card transaction : '.$_GET['sendpoint'], 'acting_transaction.log');
				break;
			case 'zeus_conv':
				$trans_id = $_REQUEST['order_no'];
				$zeus_convs = array(
									'acting' => 'zeus_conv',
									'pay_cvs' => $_REQUEST['pay_cvs'],
									'order_no' => $_REQUEST['order_no'],
									'money' => $_REQUEST['money'],
									'pay_no1' => $_REQUEST['pay_no1'],
									'pay_no2' => $_REQUEST['pay_no2'],
									'pay_limit' => $_REQUEST['pay_limit'],
									'status' => $_REQUEST['status'],
									'error_code' => $_REQUEST['error_code']
									);
				$usces->set_order_meta_value('acting_'.$_REQUEST['sendpoint'], serialize($zeus_convs), $order_id);
				break;
			case 'zeus_bank':
				$trans_id = $_REQUEST['order_no'];
				break;
			case 'remise_card':
				$trans_id = $_REQUEST['X-TRANID'];
				break;
			case 'remise_conv':
				$trans_id = $_REQUEST['X-JOB_ID'];
				break;
			case 'jpayment_card':
			case 'jpayment_conv':
			case 'jpayment_bank':
				$trans_id = $_REQUEST['gid'];
				break;
			default:
				$trans_id = '';
		}
	
		if(!empty($trans_id)) {
			$usces->set_order_meta_value('trans_id', $trans_id, $order_id);
		}
	}
	
}

//20110621ysk start 0000184
function usces_paypal_doecp( &$results ) {
	global $usces;
	$entry = $usces->cart->get_entry();

	$cart = $usces->cart->get_cart();
	$post_id = $cart[0]['post_id'];
	$charging_type = $usces->getItemChargingType($post_id);
	if( 'continue' != $charging_type) {
		//通常購入
		//Format the other parameters that were stored in the session from the previous calls
		$paymentAmount = usces_crform($entry['order']['total_full_price'], false, false, 'return', false);
		$token = urlencode($_REQUEST['token']);
		$paymentType = urlencode("Sale");
		$currencyCodeType = urlencode($usces->get_currency_code());
		$payerID = urlencode($_REQUEST['PayerID']);
		$serverName = urlencode($_SERVER['SERVER_NAME']);

		$nvpstr = '&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTACTION='.$paymentType.'&AMT='.$paymentAmount.'&CURRENCYCODE='.$currencyCodeType.'&IPADDRESS='.$serverName;

		$usces->paypal->setMethod('DoExpressCheckoutPayment');
		$usces->paypal->setData($nvpstr);
		$res = $usces->paypal->doExpressCheckout();
		$resArray = $usces->paypal->getResponse();
		$ack = strtoupper($resArray["ACK"]);
		if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {
			$transactionId = $resArray["TRANSACTIONID"]; // ' Unique transaction ID of the payment. Note:  If the PaymentAction of the request was Authorization or Order, this value is your AuthorizationID for use with the Authorization & Capture APIs. 
			//$usces->set_order_meta_value('settlement_id', $transactionId, $order_id);
			$results['settlement_id'] = $transactionId;

		} else {
			//Display a user friendly Error on the page using any of the following error information returned by PayPal
			$ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
			$ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
			$ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
			$ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);
			usces_log('PayPal : DoExpressCheckoutPayment API call failed. Error Code:['.$ErrorCode.'] Error Severity Code:['.$ErrorSeverityCode.'] Short Error Message:'.$ErrorShortMsg.' Detailed Error Message:'.$ErrorLongMsg, 'acting_transaction.log');
			return false;
		}

	} else {
		//定期支払い
		$paymentAmount = usces_crform($entry['order']['total_items_price'], false, false, 'return', false);
		$token = urlencode($_REQUEST['token']);
		$currencyCodeType = urlencode($usces->get_currency_code());
		$nextdate = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
		$profileStartDate = date('Y-m-d', mktime(0,0,0,substr($nextdate, 5, 2)+1,$usces->getItemChargingDay($post_id),substr($nextdate, 0, 4))).'T01:01:01Z';
		$billingPeriod = urlencode("Month");// or "Day", "Week", "SemiMonth", "Year"
		$billingFreq = urlencode($usces->getItemFrequency($post_id));
		//$totalbillingCycles = (empty($dlitem['dlseller_interval'])) ? '' : '&TOTALBILLINGCYCLES='.urlencode($dlitem['dlseller_interval']);
		$desc = urlencode(usces_make_agreement_description($cart, $entry['order']['total_items_price']));

		$nvpstr = '&TOKEN='.$token.'&AMT='.$paymentAmount.'&CURRENCYCODE='.$currencyCodeType.'&PROFILESTARTDATE='.$profileStartDate.'&BILLINGPERIOD='.$billingPeriod.'&BILLINGFREQUENCY='.$billingFreq.'&DESC='.$desc;

		$usces->paypal->setMethod('CreateRecurringPaymentsProfile');
		$usces->paypal->setData($nvpstr);
		$res = $usces->paypal->doExpressCheckout();
		$resArray = $usces->paypal->getResponse();
		$ack = strtoupper($resArray["ACK"]);
		if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {
			$profileid = $resArray["PROFILEID"];
			//$usces->set_order_meta_value('profile_id', $profileid, $order_id);
			$results['profile_id'] = $profileid;

		} else {
			//Display a user friendly Error on the page using any of the following error information returned by PayPal
			$ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
			$ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
			$ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
			$ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);
			usces_log('PayPal : CreateRecurringPaymentsProfile API call failed. Error Code:['.$ErrorCode.'] Error Severity Code:['.$ErrorSeverityCode.'] Short Error Message:'.$ErrorShortMsg.' Detailed Error Message:'.$ErrorLongMsg, 'acting_transaction.log');
			return false;
		}
	}

	return true;
}
//20110621ysk end
//20110421ysk start
function usces_make_agreement_description($cart, $amt) {
	global $usces;

	$cart_row = $cart[0];
	$quantity = $cart_row['quantity'];
	$itemName = $usces->getItemName($cart_row['post_id']);
	//if(1 < count($cart)) $itemName .= ','.__('Others', 'usces');
	if(50 < mb_strlen($itemName)) $itemName = mb_substr($itemName, 0, 50).'...';
	$amt = usces_crform($amt, true, false, 'return', true);
	$desc = $itemName.' '.__('Quantity','usces').':'.$quantity.' '.$amt;
	return($desc);
}
//20110421ysk end
?>
