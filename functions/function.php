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
		$pictids = $usces->get_pictids($itemCode);
		if (!empty($options)) {
//			$optstr = implode(',', $options);
		} else { 
			$optstr =  '';
			$options =  array();
		}
		
		$meisai .= "------------------------------------------------------------------\r\n";
		$meisai .= "$cartItemName \r\n";
		if( is_array($options) && count($options) > 0 ){
			foreach($options as $key => $value){
				if( !empty($key) )
					$meisai .= $key . ' : ' . urldecode($value) . "\r\n"; 
			}
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
	$meisai .= "(" . __('Currency', 'usces') . ' : ' . usces_crcode('return') . ")\r\n\r\n";
	
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
		$msg_body .= __('ϔԍ', 'usces').' : '.$args['gid']."\r\n";
		$msg_body .= __('ϋz', 'usces').' : '.number_format($args['ta']).__('dollars','usces')."\r\n";
		$msg_body .= __('x', 'usces').' : '.usces_get_conv_name($args['cv'])."\r\n";
		$msg_body .= __('Rrjtԍ','usces').' : '.$args['no']."\r\n";
		if($args['cv'] != '030') {//t@~[}[gȊO
			$msg_body .= __('RrjtԍURL', 'usces').' : '.$args['cu']."\r\n";
		}
		$msg_body .= "\r\n------------------------------------------------------------------\r\n\r\n";
	} elseif($payment['settlement'] == 'acting_jpayment_bank') {
		$args = maybe_unserialize($usces->get_order_meta_value('settlement_args', $order_id));
		$msg_body .= __('ϔԍ', 'usces').' : '.$args['gid']."\r\n";
		$msg_body .= __('ϋz', 'usces').' : '.number_format($args['ta']).__('dollars','usces')."\r\n";
		$bank = explode('.', $args['bank']);
		$msg_body .= __('sR[h','usces').' : '.$bank[0]."\r\n";
		$msg_body .= __('s','usces').' : '.$bank[1]."\r\n";
		$msg_body .= __('xXR[h','usces').' : '.$bank[2]."\r\n";
		$msg_body .= __('xX','usces').' : '.$bank[3]."\r\n";
		$msg_body .= __('','usces').' : '.$bank[4]."\r\n";
		$msg_body .= __('ԍ','usces').' : '.$bank[5]."\r\n";
		$msg_body .= __('`','usces').' : '.$bank[6]."\r\n";
		$msg_body .= __('x','usces').' : '.substr($args['exp'], 0, 4).'N'.substr($args['exp'], 4, 2).''.substr($args['exp'], 6, 2)."\r\n";
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
		$pictids = $usces->get_pictids($itemCode);
		if (!empty($options)) {
//			$optstr = implode(',', $options);
		} else { 
			$optstr =  '';
			$options =  array();
		}
		
		$meisai .= "------------------------------------------------------------------\r\n";
		$meisai .= "$cartItemName \r\n";
		if( is_array($options) && count($options) > 0 ){
			foreach($options as $key => $value){
				$meisai .= $key . ' : ' . urldecode($value) . "\r\n"; 
			}
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
	$meisai .= "(" . __('Currency', 'uesces') . ' : ' . usces_crcode('return') . ")\r\n\r\n";

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
	$msg_body .= $payment['name']. "\r\n\r\n";
	if ( $payment['settlement'] == 'transferAdvance' || $payment['settlement'] == 'transferDeferred' ) {
		$transferee = __('Transfer','usces') . " : \r\n";
		$transferee .= $usces->options['transferee'] . "\r\n";
		$msg_body .= apply_filters('usces_filter_mail_transferee', $transferee);
		$msg_body .= "\r\n------------------------------------------------------------------\r\n\r\n";
//20101018ysk start
	} elseif($payment['settlement'] == 'acting_jpayment_conv') {
		$args = maybe_unserialize($usces->get_order_meta_value('settlement_args', $order_id));
		$msg_body .= __('ϔԍ', 'usces').' : '.$args['gid']."\r\n";
		$msg_body .= __('ϋz', 'usces').' : '.number_format($args['ta']).__('dollars','usces')."\r\n";
		$msg_body .= __('x', 'usces').' : '.usces_get_conv_name($args['cv'])."\r\n";
		$msg_body .= __('Rrjtԍ','usces').' : '.$args['no']."\r\n";
		if($args['cv'] != '030') {//t@~[}[gȊO
			$msg_body .= __('RrjtԍURL', 'usces').' : '.$args['cu']."\r\n";
		}
		$msg_body .= "\r\n------------------------------------------------------------------\r\n\r\n";
	} elseif($payment['settlement'] == 'acting_jpayment_bank') {
		$args = maybe_unserialize($usces->get_order_meta_value('settlement_args', $order_id));
		$msg_body .= __('ϔԍ', 'usces').' : '.$args['gid']."\r\n";
		$msg_body .= __('ϋz', 'usces').' : '.number_format($args['ta']).__('dollars','usces')."\r\n";
		$bank = explode('.', $args['bank']);
		$msg_body .= __('sR[h','usces').' : '.$bank[0]."\r\n";
		$msg_body .= __('s','usces').' : '.$bank[1]."\r\n";
		$msg_body .= __('xXR[h','usces').' : '.$bank[2]."\r\n";
		$msg_body .= __('xX','usces').' : '.$bank[3]."\r\n";
		$msg_body .= __('','usces').' : '.$bank[4]."\r\n";
		$msg_body .= __('ԍ','usces').' : '.$bank[5]."\r\n";
		$msg_body .= __('`','usces').' : '.$bank[6]."\r\n";
		$msg_body .= __('x','usces').' : '.substr($args['exp'], 0, 4).'N'.substr($args['exp'], 4, 2).''.substr($args['exp'], 6, 2)."\r\n";
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
	
	$chargings = $usces->getItemSkuChargingType($cart[0]['post_id'], $cart[0]['sku']);
	$charging_flag = (  0 < (int)$chargings ) ? true : false;

	$item_total_price = $usces->get_total_price( $cart );
	$member = $usces->get_member();
	$order_table_name = $wpdb->prefix . "usces_order";
	$order_table_meta_name = $wpdb->prefix . "usces_order_meta";
	$member_table_name = $wpdb->prefix . "usces_member";
	$set = $usces->getPayments( $entry['order']['payment_name'] );
	if( $charging_flag ){
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
	
//20110203ysk start
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
		if(!empty($trans_id)) {
			$usces->set_order_meta_value('trans_id', $trans_id, $order_id);
		}
//20110203ysk end

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
			$limitofcard = substr(get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 0, 2) . substr($_REQUEST['X-EXPIRE'], 2, 2) . '/' . substr($_REQUEST['X-EXPIRE'], 0, 2);
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
		
		$args = array('cart'=>$cart, 'entry'=>$entry, 'order_id'=>$order_id, 'member_id'=>$member['ID'], 'payments'=>$payments, 'charging_flag'=>$charging_flag);
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
	$set = $usces->getPayments( $_POST['order']['payment_name'] );
	$status = ( $set['settlement'] == 'transferAdvance' || $set['settlement'] == 'transferDeferred' ) ? 'noreceipt,' : '';
	$status .= ( $_POST['order']['taio'] != '' ) ? $_POST['order']['taio'].',' : '';
	$status .= $_POST['order']['admin'];
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
					$_POST['order']['note'], 
					$_POST['order']['delivery_method'], 
					$_POST['order']['delivery_time'], 
					$_POST['order']['payment_name'], 
					serialize($order_conditions), 
					$item_total_price, 
					$_POST['order']['getpoint'], 
					$_POST['order']['usedpoint'], 
					$_POST['order']['discount'], 
					$_POST['order']['shipping_charge'], 
					$_POST['order']['cod_fee'], 
					$_POST['order']['tax'], 
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
					$_POST['order']['note'], 
					$_POST['order']['delivery_method'], 
					$_POST['order']['delivery_date'], 
					$_POST['order']['delivery_time'], 
					$_POST['order']['payment_name'], 
					serialize($order_conditions), 
					$item_total_price, 
					$_POST['order']['getpoint'], 
					$_POST['order']['usedpoint'], 
					$_POST['order']['discount'], 
					$_POST['order']['shipping_charge'], 
					$_POST['order']['cod_fee'], 
					$_POST['order']['tax'], 
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
	$member_meta_table = $wpdb->prefix . "usces_member_meta";
	$ID = $_REQUEST['order_id'];
	
	$query = $wpdb->prepare("SELECT mem_id FROM $order_table WHERE ID = %d", $ID);
	$mem_id = $wpdb->get_var( $query );

	$query = $wpdb->prepare("DELETE FROM $order_table WHERE ID = %d", $ID);
	$res = $wpdb->query( $query );
	
	if($res){
		$query = $wpdb->prepare("DELETE FROM $order_meta_table WHERE order_id = %d", $ID);
		$wpdb->query( $query );
		$query = $wpdb->prepare("UPDATE $member_meta_table SET meta_value = %s WHERE member_id = %d AND meta_key = %s", '', $mem_id, 'continue_status');
		$wpdb->query( $query );
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
					$_POST['order']['note'], 
					$_POST['order']['delivery_method'], 
					$_POST['order']['delivery_time'], 
					$_POST['order']['payment_name'], 
					$item_total_price, 
					$_POST['order']['getpoint'], 
					$_POST['order']['usedpoint'], 
					$_POST['order']['discount'], 
					$_POST['order']['shipping_charge'], 
					$_POST['order']['cod_fee'], 
					$_POST['order']['tax'], 
					$order_modified, 
					$status,
					$_POST['order']['delidue_date'], 
					$ordercheck,
					$ID
				);
*/
	$query = $wpdb->prepare(
				"UPDATE $order_table_name SET 
					`order_email`=%s, `order_name1`=%s, `order_name2`=%s, `order_name3`=%s, `order_name4`=%s, 
					`order_zip`=%s, `order_pref`=%s, `order_address1`=%s, `order_address2`=%s, `order_address3`=%s, 
					`order_tel`=%s, `order_fax`=%s, `order_delivery`=%s, `order_cart`=%s, `order_note`=%s, 
					`order_delivery_method`=%d, `order_delivery_date`=%s, `order_delivery_time`=%s, `order_payment_name`=%s, `order_item_total_price`=%f, `order_getpoint`=%d, `order_usedpoint`=%d, 
					`order_discount`=%f, `order_shipping_charge`=%f, `order_cod_fee`=%f, `order_tax`=%f, `order_modified`=%s, 
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
					$_POST['order']['note'], 
					$_POST['order']['delivery_method'], 
					$_POST['order']['delivery_date'], 
					$_POST['order']['delivery_time'], 
					$_POST['order']['payment_name'], 
					$item_total_price, 
					$_POST['order']['getpoint'], 
					$_POST['order']['usedpoint'], 
					$_POST['order']['discount'], 
					$_POST['order']['shipping_charge'], 
					$_POST['order']['cod_fee'], 
					$_POST['order']['tax'], 
					$order_modified, 
					$status,
					$_POST['order']['delidue_date'], 
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
	<usces_pref><?php echo serialize(get_option('usces_pref')); ?></usces_pref>
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

	$parts = explode('<usces_pref>', $xml);
	if(count($parts) > 1){
		$parts = explode('</usces_pref>', $parts[1]);
		$opt = $parts[0];
		$option = unserialize($opt);
		update_option('usces_pref', $option);	
	}
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
//20101111ysk start
/*function usces_item_uploadcsv(){
	global $wpdb;
	
	$workfile = $_FILES["usces_upcsv"]["tmp_name"];
	$lines = array();
	$total_num = 0;
	$comp_num = 0;
	$err_num = 0;
	$min_field_num = 29;
	$log = '';
	$pre_code = '';
	$res = array();
	$date_pattern = "/(\d{4})-(\d{2}|\d)-(\d{2}|\d) (\d{2}):(\d{2}|\d):(\d{2}|\d)/";
	
	if ( !is_uploaded_file($workfile) ) {
		$res['status'] = 'error';
		$res['message'] = __('The file was not uploaded.', 'usces');
		return $res;
	}

	//check ext
	list($fname, $fext) = explode('.', $_FILES["usces_upcsv"]["name"], 2);
	if( $fext != 'csv' && $fext != 'zip' ) {
		$res['status'] = 'error';
		$res['message'] =  __('The file is not supported.', 'usces').$fname.'.'.$fext;
		return $res;
	}
	
	//zip unpack
	if($fext == 'zip'){
		
	
	
	//			$workfile = 
	}
	
	//log
	if ( ! ($fpi = fopen (USCES_PLUGIN_DIR.'/logs/itemcsv_log.txt', "w"))) {
		$res['status'] = 'error';
		$res['message'] = __('The log file was not prepared for.', 'usces');
		return $res;
	}
	//read data
	if ( ! ($fpo = fopen ($workfile, "r"))) {
		$res['status'] = 'error';
		$res['message'] = __('A file does not open.', 'usces').$fname.'.'.$fext;
		return $res;
	}
	
	while (! feof ($fpo)) {
		$temp = fgets ($fpo, 10240);
		if( 5 < strlen($temp) )
			$lines[] = $temp;
	}
	$total_num = count($lines);

	//data check & reg
	foreach($lines as $rows_num => $line){
		$datas = array();
		$logtemp = '';
		$datas = explode(',', $line);
//		if( $min_field_num > count($datas) || 0 < (count($datas) - $min_field_num) % 4 ){
		if( $min_field_num > count($datas) ){
			$err_num++;
			$logtemp .= "No." . ($rows_num+1) . "\t".__('The number of the columns is abnormal.', 'usces')."\r\n";
			$log .= $logtemp;
			continue;
		}
		foreach($datas as $key => $data){
			$data = trim(mb_convert_encoding($data, 'UTF-8', 'SJIS'));
			switch($key){
				case 0:
					if( 0 == strlen($data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('An item cord is non-input.', 'usces')."\r\n";
					break;
				case 1:
					if( 0 == strlen($data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('An item name is non-input.', 'usces')."\r\n";
					break;
				case 2:
					if( !preg_match("/^[0-9]+$/", $data) && 0 != strlen($data) ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the purchase limit number is abnormal.', 'usces')."\r\n";
					}
					break;
				case 3:
					if( !preg_match("/^[0-9]+$/", $data) ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the point rate is abnormal.', 'usces')."\r\n";
					}
					break;
				case 4:
					if( !preg_match("/^[0-9]+$/", $data) ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."1-".__('umerical value is abnormality.', 'usces')."\r\n";
					}
					break;
				case 5:
					if( !preg_match("/^[0-9]+$/", $data) || ( 0 < $datas[($key-1)] && 1 > $data ) ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."1-".__('rate is abnormal.', 'usces')."\r\n";
					}
					break;
				case 6:
					if( !preg_match("/^[0-9]+$/", $data) || ($datas[($key-2)] >= $data && 0 != $data) ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."2-".__('umerical value is abnormality.', 'usces')."\r\n";
					}
					break;
				case 7:
					if( !preg_match("/^[0-9]+$/", $data) || ( 0 < $datas[($key-1)] && 1 > $data ) ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."2-".__('rate is abnormal.', 'usces')."\r\n";
					}
					break;
				case 8:
					if( !preg_match("/^[0-9]+$/", $data) || ($datas[($key-2)] >= $data && 0 != $data) ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."3-".__('umerical value is abnormality.', 'usces')."\r\n";
					}
					break;
				case 9:
					if( !preg_match("/^[0-9]+$/", $data) || ( 0 < $datas[($key-1)] && 1 > $data ) ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."3-".__('rate is abnormal.', 'usces')."\r\n";
					}
					break;
				case 10:
					if( !preg_match("/^[0-9]+$/", $data) || 9 < $data ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the shipment day is abnormal.', 'usces')."\r\n";
					}
					break;
				case 11:
				case 12:
					break;
				case 13:
					if( !preg_match("/^[0-9]+$/", $data) || 1 < $data ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the postage individual charging is abnormal.', 'usces')."\r\n";
					}
					break;
				case 14:
				case 15:
				case 16:
					break;
				case 17:
					$array17 = array('publish', 'future', 'draft', 'pending');
					if( !in_array($data, $array17) || '' == $data ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the display status is abnormal.', 'usces')."\r\n";
					}
					break;
				case 18:
					if( 'future' == $datas[($key-1)] && ('' == $data || '0000-00-00 00:00:00' == $data) ){
						if( preg_match($date_pattern, $data, $match) ){
							if( checkdate($match[2], $match[3], $match[1]) && 
										(0 < $match[4] && 24 > $match[4]) && 
										(0 < $match[5] && 60 > $match[5]) && 
										(0 < $match[6] && 60 > $match[6]) ){
								$logtemp .= "";
							}else{
								$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the schedule is abnormal.', 'usces')."\r\n";
							}
								
						}else{
							$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the schedule is abnormal.', 'usces')."\r\n";
						}
					}else if( '' != $data && '0000-00-00 00:00:00' != $data ){
						if( !preg_match($date_pattern, $data, $match) || strtotime($data) === false || strtotime($data) == -1 )
							$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the schedule is abnormal.', 'usces')."\r\n";
					}
					break;
				case 19:
					if( 0 == strlen($data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A category is non-input.', 'usces')."\r\n";
					break;
				case 20:
					break;
				case 21:
					if( 0 == strlen($data) ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A SKU cord is non-input.', 'usces')."\r\n";
					}else if( $pre_code == $datas[0] ){
						$query = $wpdb->prepare("SELECT meta_id FROM $wpdb->postmeta 
												WHERE post_id = %d AND meta_key = %s", 
												$post_id, 
												'_isku_'.trim(mb_convert_encoding($datas[21], 'UTF-8', 'SJIS'))
								);
						$meta_id = $wpdb->get_var( $query );
						if($meta_id !== NULL)
							$logtemp .= "No." . ($rows_num+1) . "\t".__('A SKU cord repeats.', 'usces')."\r\n";
					}
					break;
				case 22:
				case 23:
					break;
				case 24:
					if( !preg_match("/^[0-9]+$/", $data) || 0 == strlen($data) ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the sale price is abnormal.', 'usces')."\r\n";
					}
					break;
				case 25:
					break;
				case 26:
					if( !preg_match("/^[0-9]+$/", $data) || 4 < $data ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the stock status is abnormal.', 'usces')."\r\n";
					}
					break;
				case 27:
					break;
				case 28:
					if( !preg_match("/^[0-9]+$/", $data) || 1 < $data ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('The value of the duties pack application is abnormal.', 'usces')."\r\n";
					}
					break;
			}
		}
		$opnum = ceil((count($datas) - $min_field_num) / 4);
		for($i=0; $i<$opnum; $i++){
			for($o=1; $o<=4; $o++){
				$key = ($min_field_num-1)+$o+($i*4);
				if( isset($datas[$key]) ){
					$value = trim($datas[$key]);
				}else{
					$value = NULL;
				}
				switch($o){
					case 1:
//						if( isset($datas[$key]) && 0 == strlen($datas[$key]) )
//							$logtemp .= "No." . ($rows_num+1) . "\t" . __(sprintf('Option name of No.%s option is non-input.', ($i+1)), 'usces')."\r\n";
						break;
					case 2:
						if( $value != NULL && (!preg_match("/^[0-9]+$/", $value) || 2 < (int)$value) ){
							$logtemp .= "No." . ($rows_num+1) . "\t" . __(sprintf('Option-entry-field of No.%s option is abnormal.', ($i+1)), 'usces')."\r\n";
						}
						break;
					case 3:
						if( $value != NULL && (!preg_match("/^[0-9]+$/", $value) || 1 < (int)$value) ){
							$logtemp .= "No." . ($rows_num+1) . "\t" . __(sprintf('Option-required-item of No.%s option is abnormal.', ($i+1)), 'usces')."\r\n";
						}
						break;
					case 4:
						if( ($value != NULL && $value == '') && (2 > $datas[($key-2)] && 0 < strlen($datas[($key-2)])) ){
							$logtemp .= "No." . ($rows_num+1) . "\t" . __(sprintf('Option-select of No.%s option is non-input.', ($i+1)), 'usces')."\r\n";
						}
						break;
				}
			}
		}
		if( 0 < strlen($logtemp) ){
			$err_num++;
			$log .= $logtemp;
			$pre_code = $datas[0];
			continue;
		}
		
		//wp_posts data reg;
		$wpdb->show_errors();
		$cdatas = array();
		$post_fields = array();
		$sku = array();
		$opt = array();
		$valstr = '';

		if( $pre_code != $datas[0] ){
		
			//add posts
			$query = "SHOW FIELDS FROM $wpdb->posts";
			$results = $wpdb->get_results( $query, ARRAY_A );
			foreach($results as $ind => $rows){
				$post_fields[] = $rows['Field'];
			}
			foreach($post_fields as $key){
				switch( $key ){
					case 'ID':
						break;
					case 'post_author':
						$cdatas[$key] = 1;
						break;
					case 'post_date':
					case 'post_modified':
						if( $datas[18] == '' || $datas[18] == '0000-00-00 00:00:00' ){
							$cdatas[$key] = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
						}else{
							$cdatas[$key] = $datas[18];
						}
						break;
					case 'post_date_gmt':
					case 'post_modified_gmt':
						if( $datas[18] == '' || $datas[18] == '0000-00-00 00:00:00' ){
							$cdatas[$key] = gmdate('Y-m-d H:i:s');
						}else{
							$cdatas[$key] = gmdate('Y-m-d H:i:s', strtotime($datas[18]));
						}
						break;
					case 'post_content':
						$cdatas[$key] = trim(mb_convert_encoding($datas[15], 'UTF-8', 'SJIS'));
						break;
					case 'post_title':
						$cdatas[$key] = trim(mb_convert_encoding($datas[14], 'UTF-8', 'SJIS'));
						break;
					case 'post_excerpt':
						$cdatas[$key] = trim(mb_convert_encoding($datas[16], 'UTF-8', 'SJIS'));
						break;
					case 'post_status':
						$cdatas[$key] = $datas[17];
						break;
					case 'comment_status':
					case 'ping_status':
						$cdatas[$key] = 'close';
						break;
					case 'post_password':
					case 'post_name':
					case 'to_ping':
					case 'pinged':
					case 'post_content_filtered':
					case 'guid':
						$cdatas[$key] = '';
						break;
					case 'post_parent':
					case 'menu_order':
					case 'comment_count':
						$cdatas[$key] = 0;
						break;
					case 'post_type':
						$cdatas[$key] = 'post';
						break;
					case 'post_mime_type':
						$cdatas[$key] = 'item';
						break;
					default:
						$cdatas[$key] = '';
				}
			}
			$wpdb->insert( $wpdb->posts, $cdatas );
			$post_id = $wpdb->insert_id;
			if( $post_id == NULL ){
				$err_num++;
				$log .= "No." . ($rows_num+1) . "\t".__('The data were not registered with a database.', 'usces')."\r\n";
				$pre_code = $datas[0];
				continue;
			}
			//add postmeta
			$itemDeliveryMethod = explode(';',  $datas[11]);
			$valstr .= '(' . $post_id . ", '_itemCode','" . mysql_real_escape_string(trim(mb_convert_encoding($datas[0], 'UTF-8', 'SJIS'))) . "'),";
			$valstr .= '(' . $post_id . ", '_itemName','" . mysql_real_escape_string(trim(mb_convert_encoding($datas[1], 'UTF-8', 'SJIS'))) . "'),";
			$valstr .= '(' . $post_id . ", '_itemRestriction','" . $datas[2] . "'),";
			$valstr .= '(' . $post_id . ", '_itemPointrate','" . $datas[3] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpNum1','" . $datas[4] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpDis1','" . $datas[5] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpNum2','" . $datas[6] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpDis2','" . $datas[7] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpDis3','" . $datas[8] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpNum3','" . $datas[9] . "'),";
			$valstr .= '(' . $post_id . ", '_itemShipping','" . $datas[10] . "'),";
			$valstr .= '(' . $post_id . ", '_itemDeliveryMethod','" . mysql_real_escape_string(serialize($itemDeliveryMethod)) . "'),";
			$valstr .= '(' . $post_id . ", '_itemShippingCharge','" . mysql_real_escape_string(trim(mb_convert_encoding($datas[12], 'UTF-8', 'SJIS'))) . "'),";
			$valstr .= '(' . $post_id . ", '_itemIndividualSCharge','" . $datas[13] . "'),";
			
			$meta_key = '_isku_' . trim(mb_convert_encoding($datas[21], 'UTF-8', 'SJIS'));
			$sku['cprice'] = $datas[23];
			$sku['price'] = $datas[24];
			$sku['zaikonum'] = $datas[25];
			$sku['zaiko'] = $datas[26];
			$sku['disp'] = trim(mb_convert_encoding($datas[22], 'UTF-8', 'SJIS'));
			$sku['unit'] = trim(mb_convert_encoding($datas[27], 'UTF-8', 'SJIS'));
			$sku['gptekiyo'] = $datas[28];
			$valstr .= '(' . $post_id . ", '".mysql_real_escape_string($meta_key)."', '" . mysql_real_escape_string(serialize($sku)) . "'),";
			
			for($i=0; $i<$opnum; $i++){
				$opflg = true;
				$opt = array();
				for($o=1; $o<=4; $o++){
					$key = ($min_field_num-1)+$o+($i*4);
//					if( !isset($datas[$key]) ){
//						break 2;
//					}
					if( $o === 1 && $datas[$key] == '' ){
						$opflg = false;
						break 1;
					}
					switch($o){
						case 1:
							$ometa_key = '_iopt_' . trim(mb_convert_encoding($datas[$key], 'UTF-8', 'SJIS'));
							break;
						case 2:
							$opt['means'] = (int)$datas[$key];
							break;
						case 3:
							$opt['essential'] = (int)$datas[$key];
							break;
						case 4:
							if( !empty($datas[$key]) ) {
								$opt['value'][0] = str_replace(';', "\n", trim(mb_convert_encoding($datas[$key], 'UTF-8', 'SJIS')));
							}else{
								$opt['value'][0] = "";
							}
							 
							break;
					}
				}
				if( $opflg == true )
					$valstr .= '(' . $post_id . ", '".mysql_real_escape_string($ometa_key)."', '" . mysql_real_escape_string(serialize($opt)) . "'),";
			}
//			print_r($valstr);
			$valstr = rtrim($valstr, ',');
			$query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES $valstr";
			$dbres = mysql_query($query) or die(mysql_error());
			
			//add term_relationships, edit term_taxonomy
			//category
			$categories = explode(';', $datas[19]);
			foreach((array)$categories as $category){
				$query = $wpdb->prepare("SELECT term_taxonomy_id FROM $wpdb->term_taxonomy 
										WHERE term_id = %d", $category);
				$term_taxonomy_id = $wpdb->get_var( $query );
				if($term_taxonomy_id == NULL) continue;

				$query = $wpdb->prepare("INSERT INTO $wpdb->term_relationships 
								(object_id, term_taxonomy_id, term_order) VALUES 
								(%d, %d, 0)", 
								$post_id, $term_taxonomy_id
						);
				$dbres = $wpdb->query($query);
				if( !$dbres ) continue;
				
				$query = $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->term_relationships 
										WHERE term_taxonomy_id = %d", $term_taxonomy_id);
				$tct = $wpdb->get_var( $query );
				
				$query = $wpdb->prepare("UPDATE $wpdb->term_taxonomy SET count = %d 
								WHERE term_taxonomy_id = %d", 
								$tct, $term_taxonomy_id
						);
				$dbres = $wpdb->query($query);
			}
			//tag
			$tags = explode(';', $datas[20]);
			foreach((array)$tags as $tag){
				$tag = trim($tag);
				if( $tag != '' ){
					$term_ids = wp_insert_term( $tag, 'post_tag' );

					if( !is_array($term_ids) ) continue;
					
					$query = $wpdb->prepare("INSERT INTO $wpdb->term_relationships 
									(object_id, term_taxonomy_id, term_order) VALUES 
									(%d, %d, 0)", 
									$post_id, $term_ids['term_id']
							);
					$dbres = $wpdb->query($query);
					
					$query = $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->term_relationships 
											WHERE term_taxonomy_id = %d", $term_taxonomy_id);
					$tct = $wpdb->get_var( $query );
					
					$query = $wpdb->prepare("UPDATE $wpdb->term_taxonomy SET count = %d 
									WHERE term_taxonomy_id = %d", 
									$tct, $term_taxonomy_id
							);
					$dbres = $wpdb->query($query);
				}
			}
			
			//edit posts
			$ids['ID'] = $post_id;
			$updatas['post_name'] = $post_id;
			$updatas['guid'] = get_option('home') . '?p=' . $post_id;
			$wpdb->update( $wpdb->posts, $updatas, $ids );
			
		}else{
			$valstr = '';
			$meta_key = '_isku_' . trim(mb_convert_encoding($datas[21], 'UTF-8', 'SJIS'));
			$sku['cprice'] = $datas[23];
			$sku['price'] = $datas[24];
			$sku['zaikonum'] = $datas[25];
			$sku['zaiko'] = $datas[26];
			$sku['disp'] = trim(mb_convert_encoding($datas[22], 'UTF-8', 'SJIS'));
			$sku['unit'] = trim(mb_convert_encoding($datas[27], 'UTF-8', 'SJIS'));
			$sku['gptekiyo'] = $datas[28];
			$valstr .= '(' . $post_id . ", '".mysql_real_escape_string($meta_key)."', '" . mysql_real_escape_string(serialize($sku)) . "'),";
			
			$valstr = rtrim($valstr, ',');
			$query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES $valstr";
			$dbres = mysql_query($query) or die(mysql_error());
		
		}
		
		
		$comp_num++;
		$pre_code = $datas[0];
	}
	
	flock($fpi, LOCK_EX);
	fputs($fpi, mb_convert_encoding($log, 'SJIS', 'UTF-8'));
	flock($fpi, LOCK_UN);
	fclose($fpo);
	fclose($fpi);

	$res['status'] = 'success';
	$res['message'] = __(sprintf('%2$s of %1$s lines registration completion, %3$s lines error.',$total_num,$comp_num,$err_num), 'usces');
	return $res;
}*/
function usces_item_uploadcsv(){
	require_once( USCES_PLUGIN_DIR . "/libs/excel_reader2.php" );
	global $wpdb, $usces;
	//$wpdb->show_errors();
	$res = $wpdb->query( 'SET SQL_BIG_SELECTS=1' );
	set_time_limit(1800);
	
	define('USCES_COL_ITEM_CODE', 0);
	define('USCES_COL_ITEM_NAME', 1);
	define('USCES_COL_ITEM_RESTRICTION', 2);
	define('USCES_COL_ITEM_POINTRATE', 3);
	define('USCES_COL_ITEM_GPNUM1', 4);
	define('USCES_COL_ITEM_GPDIS1', 5);
	define('USCES_COL_ITEM_GPNUM2', 6);
	define('USCES_COL_ITEM_GPDIS2', 7);
	define('USCES_COL_ITEM_GPNUM3', 8);
	define('USCES_COL_ITEM_GPDIS3', 9);
	if(defined('WCEX_DLSELLER')) {
		define('USCES_COL_DLSELLER_DIVISION', 10);
		define('USCES_COL_DLSELLER_VALIDITY', 11);
		define('USCES_COL_DLSELLER_PERIOD', 12);
		define('USCES_COL_DLSELLER_FILE', 13);
		define('USCES_COL_DLSELLER_DATE', 14);
		define('USCES_COL_DLSELLER_VERSION', 15);
		define('USCES_COL_DLSELLER_AUTHOR', 16);
		define('USCES_COL_DLSELLER_PURCHASES', 17);
		define('USCES_COL_DLSELLER_DOWNLOADS', 18);
		define('USCES_COL_POST_TITLE', 19);
		define('USCES_COL_POST_CONTENT', 20);
		define('USCES_COL_POST_EXCERPT', 21);
		define('USCES_COL_POST_STATUS', 22);
		define('USCES_COL_POST_MODIFIED', 23);
		define('USCES_COL_CATEGORY', 24);
		define('USCES_COL_POST_TAG', 25);
		define('USCES_COL_SKU_CODE', 26);
		define('USCES_COL_SKU_NAME', 27);
		define('USCES_COL_SKU_CPRICE', 28);
		define('USCES_COL_SKU_PRICE', 29);
		define('USCES_COL_SKU_ZAIKONUM', 30);
		define('USCES_COL_SKU_ZAIKO', 31);
		define('USCES_COL_SKU_UNIT', 32);
		define('USCES_COL_SKU_GPTEKIYO', 33);
		define('USCES_COL_SKU_CHARGINGTYPE', 34);
	} else {
		define('USCES_COL_ITEM_SHIPPING', 10);
		define('USCES_COL_ITEM_DELIVERYMETHOD', 11);
		define('USCES_COL_ITEM_SHIPPINGCHARGE', 12);
		define('USCES_COL_ITEM_INDIVIDUALSCHARGE', 13);
		define('USCES_COL_POST_TITLE', 14);
		define('USCES_COL_POST_CONTENT', 15);
		define('USCES_COL_POST_EXCERPT', 16);
		define('USCES_COL_POST_STATUS', 17);
		define('USCES_COL_POST_MODIFIED', 18);
		define('USCES_COL_CATEGORY', 19);
		define('USCES_COL_POST_TAG', 20);
		define('USCES_COL_SKU_CODE', 21);
		define('USCES_COL_SKU_NAME', 22);
		define('USCES_COL_SKU_CPRICE', 23);
		define('USCES_COL_SKU_PRICE', 24);
		define('USCES_COL_SKU_ZAIKONUM', 25);
		define('USCES_COL_SKU_ZAIKO', 26);
		define('USCES_COL_SKU_UNIT', 27);
		define('USCES_COL_SKU_GPTEKIYO', 28);
	}
	define('IDENTIFIER_OLE', pack("CCCCCCCC",0xd0,0xcf,0x11,0xe0,0xa1,0xb1,0x1a,0xe1));

	$workfile = $_FILES["usces_upcsv"]["tmp_name"];
	$lines = array();
	$total_num = 0;
	$comp_num = 0;
	$err_num = 0;
	$min_field_num = (defined('WCEX_DLSELLER')) ? 35 : 29;
	$log = '';
	$pre_code = '';
	$res = array();
	$date_pattern = "/(\d{4})-(\d{2}|\d)-(\d{2}|\d) (\d{2}):(\d{2}|\d):(\d{2}|\d)/";
	
	if ( !is_uploaded_file($workfile) ) {
		$res['status'] = 'error';
		$res['message'] = __('The file was not uploaded.', 'usces');
		return $res;
	}

	//check ext
	list($fname, $fext) = explode('.', $_FILES["usces_upcsv"]["name"], 2);
	if( $fext != 'csv' && $fext != 'xls' ) {
		$res['status'] = 'error';
		$res['message'] =  __('The file is not supported.', 'usces').$fname.'.'.$fext;
		return $res;
	}
	
	//log
	if ( ! ($fpi = fopen (USCES_PLUGIN_DIR.'/logs/itemcsv_log.txt', "w"))) {
		$res['status'] = 'error';
		$res['message'] = __('The log file was not prepared for.', 'usces');
		return $res;
	}
	//read data
	if ( ! ($fpo = fopen ($workfile, "r"))) {
		$res['status'] = 'error';
		$res['message'] = __('A file does not open.', 'usces').$fname.'.'.$fext;
		return $res;
	}
	
	$lines = array();
	$sp = ",";
	if('xls' === $fext) {
		$sp = "\t";
		$data = @file_get_contents($workfile);
		if (!$data) {
			$res['status'] = 'error';
			$res['message'] = __('A file does not open.', 'usces').$fname.'.'.$fext;
			return $res;
		}
		if(substr($data, 0, 8) != IDENTIFIER_OLE) {
			//$fext = 'tsv';
			//while (! feof ($fpo)) {
			//	$temp = fgets ($fpo, 10240);
			//	if( 5 < strlen($temp) )
			//		$lines[] = str_replace('"', '', $temp);
			//}
			//20101208ysk
			$res['status'] = 'error';
			$res['message'] = __('このファイルはExcelファイルでは有りません。', 'usces').$fname.'.'.$fext;
			return $res;
		} else {
			$excel = new Spreadsheet_Excel_Reader();
			$excel->read($workfile);
			$rows = $excel->rowcount();//最大行数
			$cols = $excel->colcount();//最大列数
			for($r = 1; $r <= $rows; $r++) {
				$line = '';
				for($c = 1; $c <= $cols; $c++) {
					$line .= mb_convert_encoding($excel->val($r, $c), "SJIS", "UTF-8").$sp;
				}
				$line = trim($line, $sp);
				$lines[] = $line;
			}
		}
	} else {
		while (! feof ($fpo)) {
			$temp = fgets ($fpo, 10240);
			if( 5 < strlen($temp) )
				$lines[] = $temp;
		}
	}
	$total_num = count($lines);

	//data check & reg
	foreach($lines as $rows_num => $line){
		$datas = array();
		$logtemp = '';
//20110201ysk start
		//$datas = explode($sp, $line);
		$d = explode($sp, $line);
		foreach($d as $key => $data) {
			$datas[$key] = trim($data, '"');
		}
//20110201ysk end
//		if( $min_field_num > count($datas) || 0 < (count($datas) - $min_field_num) % 4 ){
		if( $min_field_num > count($datas) ){
			$err_num++;
			$logtemp .= "No." . ($rows_num+1)." ".count($datas) . "\t".__('The number of the columns is abnormal.', 'usces')."\r\n";
			$log .= $logtemp;
			continue;
		}
		foreach($datas as $key => $data){
			$data = trim(mb_convert_encoding($data, 'UTF-8', 'SJIS'));
			switch($key){
				case USCES_COL_ITEM_CODE:
					if( 0 == strlen($data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('An item cord is non-input.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_NAME:
					if( 0 == strlen($data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('An item name is non-input.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_RESTRICTION:
					if( !preg_match("/^[0-9]+$/", $data) && 0 != strlen($data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the purchase limit number is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_POINTRATE:
					if( !preg_match("/^[0-9]+$/", $data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the point rate is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_GPNUM1:
					if( !preg_match("/^[0-9]+$/", $data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."1-".__('umerical value is abnormality.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_GPDIS1:
					if( !preg_match("/^[0-9]+$/", $data) || ( 0 < $datas[USCES_COL_ITEM_GPNUM1] && 1 > $data ) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."1-".__('rate is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_GPNUM2:
					if( !preg_match("/^[0-9]+$/", $data) || ($datas[USCES_COL_ITEM_GPNUM1] >= $data && 0 != $data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."2-".__('umerical value is abnormality.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_GPDIS2:
					if( !preg_match("/^[0-9]+$/", $data) || ( 0 < $datas[USCES_COL_ITEM_GPNUM2] && 1 > $data ) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."2-".__('rate is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_GPNUM3:
					if( !preg_match("/^[0-9]+$/", $data) || ($datas[USCES_COL_ITEM_GPNUM2] >= $data && 0 != $data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."3-".__('umerical value is abnormality.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_GPDIS3:
					if( !preg_match("/^[0-9]+$/", $data) || ( 0 < $datas[USCES_COL_ITEM_GPNUM3] && 1 > $data ) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."3-".__('rate is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_SHIPPING:
					if( !preg_match("/^[0-9]+$/", $data) || 9 < $data )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the shipment day is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_DELIVERYMETHOD:
				case USCES_COL_ITEM_SHIPPINGCHARGE:
					break;
				case USCES_COL_ITEM_INDIVIDUALSCHARGE:
					if( !preg_match("/^[0-9]+$/", $data) || 1 < $data )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the postage individual charging is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_DLSELLER_DIVISION:
					if(defined('WCEX_DLSELLER')) {
						$array_division = array('data', 'service');
						if( !in_array($data, $array_division) || '' == $data )
							$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the division is abnormal.', 'dlseller')."\r\n";
					}
					break;
				case USCES_COL_DLSELLER_VALIDITY:
					if(defined('WCEX_DLSELLER')) {
						if( 0 < strlen($data) and !preg_match("/^[0-9]+$/", $data) )
							$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the validity is abnormal.', 'dlseller')."\r\n";
					}
					break;
				case USCES_COL_DLSELLER_PERIOD:
					if(defined('WCEX_DLSELLER')) {
						if($datas[USCES_COL_DLSELLER_DIVISION] == 'service') {
							if( 0 < strlen($data) and !preg_match("/^[0-9]+$/", $data) )
								$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the period is abnormal.', 'dlseller')."\r\n";
						}
					}
					break;
				case USCES_COL_DLSELLER_FILE:
					if(defined('WCEX_DLSELLER')) {
						if($datas[USCES_COL_DLSELLER_DIVISION] == 'data') {
							if( 0 == strlen($data) )
								$logtemp .= "No." . ($rows_num+1) . "\t".__('A file name is non-input.', 'dlseller')."\r\n";
						}
					}
					break;
				case USCES_COL_DLSELLER_DATE:
					if(defined('WCEX_DLSELLER')) {
						if($datas[USCES_COL_DLSELLER_DIVISION] == 'data') {
						}
					}
					break;
				case USCES_COL_DLSELLER_VERSION:
					if(defined('WCEX_DLSELLER')) {
						if($datas[USCES_COL_DLSELLER_DIVISION] == 'data') {
						}
					}
					break;
				case USCES_COL_DLSELLER_AUTHOR:
					if(defined('WCEX_DLSELLER')) {
						if($datas[USCES_COL_DLSELLER_DIVISION] == 'data') {
						}
					}
					break;
				case USCES_COL_DLSELLER_PURCHASES:
				case USCES_COL_DLSELLER_DOWNLOADS:
					break;
				case USCES_COL_POST_TITLE:
				case USCES_COL_POST_CONTENT:
				case USCES_COL_POST_EXCERPT:
					break;
				case USCES_COL_POST_STATUS:
					$array17 = array('publish', 'future', 'draft', 'pending');
					if( !in_array($data, $array17) || '' == $data )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the display status is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_POST_MODIFIED:
					if( 'future' == $datas[USCES_COL_POST_STATUS] && ('' == $data || '0000-00-00 00:00:00' == $data) ){
						if( preg_match($date_pattern, $data, $match) ){
							if( checkdate($match[2], $match[3], $match[1]) && 
										(0 < $match[4] && 24 > $match[4]) && 
										(0 < $match[5] && 60 > $match[5]) && 
										(0 < $match[6] && 60 > $match[6]) ){
								$logtemp .= "";
							}else{
								$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the schedule is abnormal.', 'usces')."\r\n";
							}
							
						}else{
							$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the schedule is abnormal.', 'usces')."\r\n";
						}
					}else if( '' != $data && '0000-00-00 00:00:00' != $data ){
						//if( !preg_match($date_pattern, $data, $match) || strtotime($data) === false || strtotime($data) == -1 )
						//	$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the schedule is abnormal.', 'usces')."\r\n";
						if(preg_match("/^[0-9]+$/", substr($data,0,4))) {//先頭4桁が数値のみ
							if(strtotime($data) === false)
								$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the schedule is abnormal.', 'usces')."\r\n";
						} else {
							$datetime = explode(' ', $data);
							$date_str = usces_dates_interconv($datetime[0]).' '.$datetime[1];
							if(strtotime($date_str) === false)
								$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the schedule is abnormal.', 'usces')."\r\n";
						}
					}
					break;
				case USCES_COL_CATEGORY:
					if( 0 == strlen($data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A category is non-input.', 'usces')."\r\n";
					break;
				case USCES_COL_POST_TAG:
					break;
				case USCES_COL_SKU_CODE:
					if( 0 == strlen($data) ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A SKU cord is non-input.', 'usces')."\r\n";
					}else if( $pre_code == $datas[USCES_COL_ITEM_CODE] ){
						$query = $wpdb->prepare("SELECT meta_id FROM $wpdb->postmeta 
												WHERE post_id = %d AND meta_key = %s", 
												$post_id, 
												'_isku_'.trim(mb_convert_encoding($data, 'UTF-8', 'SJIS'))
								);
						$meta_id = $wpdb->get_var( $query );
						if($meta_id !== NULL)
							$logtemp .= "No." . ($rows_num+1) . "\t".__('A SKU cord repeats.', 'usces')."\r\n";
					}
					break;
				case USCES_COL_SKU_NAME:
//20110315ysk start
					break;
				case USCES_COL_SKU_CPRICE:
					if( 0 < strlen($data) and !preg_match("/^\d$|^\d+\.?\d+$/", $data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the price is abnormal.', 'usces')."\r\n";
					break;
//20110315ysk end
				case USCES_COL_SKU_PRICE:
//20110315ysk start
					//if( !preg_match("/^[0-9]+$/", $data) || 0 == strlen($data) )
					if( !preg_match("/^\d$|^\d+\.?\d+$/", $data) || 0 == strlen($data) )
//20110315ysk end
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the sale price is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_SKU_ZAIKONUM:
//20110315ysk start
					if( 0 < strlen($data) and !preg_match("/^[0-9]+$/", $data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the stock number is abnormal.', 'usces')."\r\n";
//20110315ysk end
					break;
				case USCES_COL_SKU_ZAIKO:
					if( !preg_match("/^[0-9]+$/", $data) || 4 < $data )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the stock status is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_SKU_UNIT:
					break;
				case USCES_COL_SKU_GPTEKIYO:
					if( !preg_match("/^[0-9]+$/", $data) || 1 < $data )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('The value of the duties pack application is abnormal.', 'usces')."\r\n";
					break;
			}
		}
		$opnum = ceil((count($datas) - $min_field_num) / 4);
		for($i=0; $i<$opnum; $i++){
			for($o=1; $o<=4; $o++){
				$key = ($min_field_num-1)+$o+($i*4);
				if( isset($datas[$key]) ){
					$value = trim($datas[$key]);
				}else{
					$value = NULL;
				}
				switch($o){
					case 1:
//						if( isset($datas[$key]) && 0 == strlen($datas[$key]) )
//							$logtemp .= "No." . ($rows_num+1) . "\t" . __(sprintf('Option name of No.%s option is non-input.', ($i+1)), 'usces')."\r\n";
						break;
					case 2:
						if( $value != NULL && ((0 != (int)$value) and (1 != (int)$value) and (2 != (int)$value) and (5 != (int)$value)) )
							$logtemp .= "No." . ($rows_num+1) . "\t" . __(sprintf('Option-entry-field of No.%s option is abnormal.', ($i+1)), 'usces')."\r\n";
						break;
					case 3:
						if( $value != NULL && (!preg_match("/^[0-9]+$/", $value) || 1 < (int)$value) )
							$logtemp .= "No." . ($rows_num+1) . "\t" . __(sprintf('Option-required-item of No.%s option is abnormal.', ($i+1)), 'usces')."\r\n";
						break;
					case 4:
						if( ($value != NULL && $value == '') && (2 > $datas[($key-2)] && 0 < strlen($datas[($key-2)])) )
							$logtemp .= "No." . ($rows_num+1) . "\t" . __(sprintf('Option-select of No.%s option is non-input.', ($i+1)), 'usces')."\r\n";
						break;
				}
			}
		}
		if( 0 < strlen($logtemp) ){
			$err_num++;
			$log .= $logtemp;
//20110315ysk start
			//$pre_code = $datas[USCES_COL_ITEM_CODE];
//20110315ysk end
			continue;
		}
		
		//wp_posts data reg;
		$cdatas = array();
		$post_fields = array();
		$sku = array();
		$opt = array();
		$valstr = '';

		$mode = 'add';
		if($pre_code != $datas[USCES_COL_ITEM_CODE]) {
//20101207ysk start
			//$post_id = $usces->get_postIDbyCode($datas[USCES_COL_ITEM_CODE]);
			$query = $wpdb->prepare("SELECT meta.post_id FROM $wpdb->postmeta AS meta 
				INNER JOIN $wpdb->posts AS post ON meta.post_id = post.ID AND post.post_status <> %s AND post.post_mime_type = 'item' 
				WHERE meta.meta_value = %s LIMIT 1", 'trash', trim(mb_convert_encoding($datas[USCES_COL_ITEM_CODE], 'UTF-8', 'SJIS')));
			$post_id = $wpdb->get_var( $query );
//20101207ysk end
			if(!empty($post_id)) $mode = 'upd';
		}

		if( $pre_code != $datas[USCES_COL_ITEM_CODE] ){
		
			//add posts
			$query = "SHOW FIELDS FROM $wpdb->posts";
			$results = $wpdb->get_results( $query, ARRAY_A );
			if($mode == 'add') {
				foreach($results as $ind => $rows){
					$post_fields[] = $rows['Field'];
				}
			} elseif($mode == 'upd') {
				$post_fields[] = 'post_modified';
				$post_fields[] = 'post_modified_gmt';
				$post_fields[] = 'post_content';
				$post_fields[] = 'post_title';
				$post_fields[] = 'post_excerpt';
				$post_fields[] = 'post_status';
			}
			foreach($post_fields as $key){
				switch( $key ){
					case 'ID':
						break;
					case 'post_author':
						$cdatas[$key] = 1;
						break;
					case 'post_date':
					case 'post_modified':
						$data = $datas[USCES_COL_POST_MODIFIED];
						if( $data == '' || $data == '0000-00-00 00:00:00' ){
							$cdatas[$key] = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
						}else{
							//$cdatas[$key] = $data;

							if(preg_match("/^[0-9]+$/", substr($data,0,4))) {//先頭4桁が数値のみ
								$cdatas[$key] = $data;
							} else {
								$datetime = explode(' ', $data);
								$date_str = usces_dates_interconv( $datetime[0] ).' '.$datetime[1];
								$cdatas[$key] = $date_str;
							}
						}
						break;
					case 'post_date_gmt':
					case 'post_modified_gmt':
						$data = $datas[USCES_COL_POST_MODIFIED];
						if( $data == '' || $data == '0000-00-00 00:00:00' ){
							$cdatas[$key] = gmdate('Y-m-d H:i:s');
						}else{
							//$cdatas[$key] = gmdate('Y-m-d H:i:s', strtotime($data));
							if(preg_match("/^[0-9]+$/", substr($data,0,4))) {//先頭4桁が数値のみ
								$cdatas[$key] = gmdate('Y-m-d H:i:s', strtotime($data));
							} else {
								$datetime = explode(' ', $data);
								$date_str = usces_dates_interconv( $datetime[0] ).' '.$datetime[1];
								$cdatas[$key] = gmdate('Y-m-d H:i:s', strtotime($date_str));
							}
						}
						break;
					case 'post_content':
						$cdatas[$key] = trim(mb_convert_encoding($datas[USCES_COL_POST_CONTENT], 'UTF-8', 'SJIS'));
						break;
					case 'post_title':
						$cdatas[$key] = trim(mb_convert_encoding($datas[USCES_COL_POST_TITLE], 'UTF-8', 'SJIS'));
						break;
					case 'post_excerpt':
						$cdatas[$key] = trim(mb_convert_encoding($datas[USCES_COL_POST_EXCERPT], 'UTF-8', 'SJIS'));
						break;
					case 'post_status':
						$cdatas[$key] = $datas[USCES_COL_POST_STATUS];
						break;
					case 'comment_status':
					case 'ping_status':
						$cdatas[$key] = 'close';
						break;
					case 'post_password':
					case 'post_name':
					case 'to_ping':
					case 'pinged':
					case 'post_content_filtered':
					case 'guid':
						$cdatas[$key] = '';
						break;
					case 'post_parent':
					case 'menu_order':
					case 'comment_count':
						$cdatas[$key] = 0;
						break;
					case 'post_type':
						$cdatas[$key] = 'post';
						break;
					case 'post_mime_type':
						$cdatas[$key] = 'item';
						break;
					default:
						$cdatas[$key] = '';
				}
			}
			if($mode == 'add') {
				$wpdb->insert( $wpdb->posts, $cdatas );
				$post_id = $wpdb->insert_id;
				if( $post_id == NULL ){
					$err_num++;
					$log .= "No." . ($rows_num+1) . "\t".__('The data were not registered with a database.', 'usces')."\r\n";
					$pre_code = $datas[USCES_COL_ITEM_CODE];
					continue;
				}
			} elseif($mode == 'upd') {
				$ids['ID'] = $post_id;
				$dbres = $wpdb->update( $wpdb->posts, $cdatas, $ids );
				if( $dbres === false ) {
					$err_num++;
					$log .= "No." . ($rows_num+1) . "\t".__('The data were not registered with a database.', 'usces')."\r\n";
					$pre_code = $datas[USCES_COL_ITEM_CODE];
					continue;
				}
				$query = $wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE post_id = %d", $post_id);
				$dbres = $wpdb->query( $query );
				if( $dbres === false ) {
					$err_num++;
					$log .= "No." . ($rows_num+1) . "\t".__('The data were not registered with a database.', 'usces')."\r\n";
					$pre_code = $datas[USCES_COL_ITEM_CODE];
					continue;
				}
				$query = $wpdb->prepare("DELETE FROM $wpdb->term_relationships WHERE object_id = %d", $post_id);
				$dbres = $wpdb->query( $query );
				if( $dbres === false ) {
					$err_num++;
					$log .= "No." . ($rows_num+1) . "\t".__('The data were not registered with a database.', 'usces')."\r\n";
					$pre_code = $datas[USCES_COL_ITEM_CODE];
					continue;
				}
				$query = "SELECT term_taxonomy_id, COUNT(*) AS ct FROM $wpdb->term_relationships GROUP BY term_taxonomy_id";
				$relation_data = $wpdb->get_results( $query, ARRAY_A );
				foreach((array)$relation_data as $relation_rows) {
					$term_taxonomy_ids['term_taxonomy_id'] = $relation_rows['term_taxonomy_id'];
					$term_taxonomy_updatas['count'] = $relation_rows['ct'];
					$dbres = $wpdb->update( $wpdb->term_taxonomy, $term_taxonomy_updatas, $term_taxonomy_ids );
					if( $dbres === false ) {
						$err_num++;
						$log .= "No." . ($rows_num+1) . "\t".__('The data were not registered with a database.', 'usces')."\r\n";
						$pre_code = $datas[USCES_COL_ITEM_CODE];
						continue;
					}
				}
			}

			//add postmeta
			$itemDeliveryMethod = explode(';',  $datas[USCES_COL_ITEM_DELIVERYMETHOD]);
			$valstr .= '(' . $post_id . ", '_itemCode','" . mysql_real_escape_string(trim(mb_convert_encoding($datas[USCES_COL_ITEM_CODE], 'UTF-8', 'SJIS'))) . "'),";
			$valstr .= '(' . $post_id . ", '_itemName','" . mysql_real_escape_string(trim(mb_convert_encoding($datas[USCES_COL_ITEM_NAME], 'UTF-8', 'SJIS'))) . "'),";
			$valstr .= '(' . $post_id . ", '_itemRestriction','" . $datas[USCES_COL_ITEM_RESTRICTION] . "'),";
			$valstr .= '(' . $post_id . ", '_itemPointrate','" . $datas[USCES_COL_ITEM_POINTRATE] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpNum1','" . $datas[USCES_COL_ITEM_GPNUM1] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpDis1','" . $datas[USCES_COL_ITEM_GPDIS1] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpNum2','" . $datas[USCES_COL_ITEM_GPNUM2] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpDis2','" . $datas[USCES_COL_ITEM_GPDIS2] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpNum3','" . $datas[USCES_COL_ITEM_GPNUM3] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpDis3','" . $datas[USCES_COL_ITEM_GPDIS3] . "'),";
			if(!defined('WCEX_DLSELLER')) {
				$valstr .= '(' . $post_id . ", '_itemShipping','" . $datas[USCES_COL_ITEM_SHIPPING] . "'),";
				$valstr .= '(' . $post_id . ", '_itemDeliveryMethod','" . mysql_real_escape_string(serialize($itemDeliveryMethod)) . "'),";
				$valstr .= '(' . $post_id . ", '_itemShippingCharge','" . mysql_real_escape_string(trim(mb_convert_encoding($datas[USCES_COL_ITEM_SHIPPINGCHARGE], 'UTF-8', 'SJIS'))) . "'),";
				$valstr .= '(' . $post_id . ", '_itemIndividualSCharge','" . $datas[USCES_COL_ITEM_INDIVIDUALSCHARGE] . "'),";
			}
			$meta_key = '_isku_' . trim(mb_convert_encoding($datas[USCES_COL_SKU_CODE], 'UTF-8', 'SJIS'));
			$sku['cprice'] = $datas[USCES_COL_SKU_CPRICE];
			$sku['price'] = $datas[USCES_COL_SKU_PRICE];
			$sku['zaikonum'] = $datas[USCES_COL_SKU_ZAIKONUM];
			$sku['zaiko'] = $datas[USCES_COL_SKU_ZAIKO];
			$sku['disp'] = trim(mb_convert_encoding($datas[USCES_COL_SKU_NAME], 'UTF-8', 'SJIS'));
			$sku['unit'] = trim(mb_convert_encoding($datas[USCES_COL_SKU_UNIT], 'UTF-8', 'SJIS'));
			$sku['gptekiyo'] = $datas[USCES_COL_SKU_GPTEKIYO];
			if(defined('WCEX_DLSELLER')) {
				$sku['charging_type'] = $datas[USCES_COL_SKU_CHARGINGTYPE];
			}
			$valstr .= '(' . $post_id . ", '".mysql_real_escape_string($meta_key)."', '" . mysql_real_escape_string(serialize($sku)) . "'),";
			
			for($i=0; $i<$opnum; $i++){
				$opflg = true;
				$opt = array();
				for($o=1; $o<=4; $o++){
					$key = ($min_field_num-1)+$o+($i*4);
//					if( !isset($datas[$key]) ){
//						break 2;
//					}
					if( $o === 1 && $datas[$key] == '' ){
						$opflg = false;
						break 1;
					}
					switch($o){
						case 1:
							$ometa_key = '_iopt_' . trim(mb_convert_encoding($datas[$key], 'UTF-8', 'SJIS'));
							break;
						case 2:
							$opt['means'] = (int)$datas[$key];
							break;
						case 3:
							$opt['essential'] = (int)$datas[$key];
							break;
						case 4:
							if( !empty($datas[$key]) ) {
								$opt['value'][0] = str_replace(';', "\n", trim(mb_convert_encoding($datas[$key], 'UTF-8', 'SJIS')));
							}else{
								$opt['value'][0] = "";
							}
							break;
					}
				}
				if( $opflg == true )
					$valstr .= '(' . $post_id . ", '".mysql_real_escape_string($ometa_key)."', '" . mysql_real_escape_string(maybe_serialize($opt)) . "'),";
			}
//			print_r($valstr);
			$valstr = rtrim($valstr, ',');
			$query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES $valstr";
			$dbres = mysql_query($query) or die(mysql_error());
			
			if(defined('WCEX_DLSELLER')) {
				if(isset($datas[USCES_COL_DLSELLER_DIVISION])) update_post_meta($post_id, '_dlseller_division', $datas[USCES_COL_DLSELLER_DIVISION]);
				if($datas[USCES_COL_DLSELLER_DIVISION] == 'data') {
					if(isset($datas[USCES_COL_DLSELLER_VALIDITY])) update_post_meta($post_id, '_dlseller_validity', $datas[USCES_COL_DLSELLER_VALIDITY]);
					if(isset($datas[USCES_COL_DLSELLER_FILE])) update_post_meta($post_id, '_dlseller_file', $datas[USCES_COL_DLSELLER_FILE]);
					if(isset($datas[USCES_COL_DLSELLER_DATE])) update_post_meta($post_id, '_dlseller_date', $datas[USCES_COL_DLSELLER_DATE]);
					if(isset($datas[USCES_COL_DLSELLER_VERSION])) update_post_meta($post_id, '_dlseller_version', $datas[USCES_COL_DLSELLER_VERSION]);
					if(isset($datas[USCES_COL_DLSELLER_AUTHOR])) update_post_meta($post_id, '_dlseller_author', $datas[USCES_COL_DLSELLER_AUTHOR]);
				} elseif($datas[USCES_COL_DLSELLER_DIVISION] == 'service') {
					if(isset($datas[USCES_COL_DLSELLER_VALIDITY])) update_post_meta($post_id, '_dlseller_validity', $datas[USCES_COL_DLSELLER_VALIDITY]);
					if(isset($datas[USCES_COL_DLSELLER_PERIOD])) update_post_meta($post_id, '_dlseller_period', $datas[USCES_COL_DLSELLER_PERIOD]);
				}
			}

			//add term_relationships, edit term_taxonomy
			//category
			$categories = explode(';', $datas[USCES_COL_CATEGORY]);
			foreach((array)$categories as $category){
				$query = $wpdb->prepare("SELECT term_taxonomy_id FROM $wpdb->term_taxonomy 
										WHERE term_id = %d", $category);
				$term_taxonomy_id = $wpdb->get_var( $query );
				if($term_taxonomy_id == NULL) continue;

				$query = $wpdb->prepare("INSERT INTO $wpdb->term_relationships 
								(object_id, term_taxonomy_id, term_order) VALUES 
								(%d, %d, 0)", 
								$post_id, $term_taxonomy_id
						);
				$dbres = $wpdb->query($query);
				if( !$dbres ) continue;
				
				$query = $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->term_relationships 
										WHERE term_taxonomy_id = %d", $term_taxonomy_id);
				$tct = $wpdb->get_var( $query );
				
				$query = $wpdb->prepare("UPDATE $wpdb->term_taxonomy SET count = %d 
								WHERE term_taxonomy_id = %d", 
								$tct, $term_taxonomy_id
						);
				$dbres = $wpdb->query($query);
			}
			//tag
			$tags = explode(';', $datas[USCES_COL_POST_TAG]);
			wp_set_object_terms($post_id, (array)$tags, 'post_tag');
			
			if($mode == 'add') {
				//edit posts
				$ids['ID'] = $post_id;
				$updatas['post_name'] = $post_id;
				$updatas['guid'] = get_option('home') . '?p=' . $post_id;
				$wpdb->update( $wpdb->posts, $updatas, $ids );
			}
			
		}else{
			$valstr = '';
			$meta_key = '_isku_' . trim(mb_convert_encoding($datas[USCES_COL_SKU_CODE], 'UTF-8', 'SJIS'));
			$sku['cprice'] = $datas[USCES_COL_SKU_CPRICE];
			$sku['price'] = $datas[USCES_COL_SKU_PRICE];
			$sku['zaikonum'] = $datas[USCES_COL_SKU_ZAIKONUM];
			$sku['zaiko'] = $datas[USCES_COL_SKU_ZAIKO];
			$sku['disp'] = trim(mb_convert_encoding($datas[USCES_COL_SKU_NAME], 'UTF-8', 'SJIS'));
			$sku['unit'] = trim(mb_convert_encoding($datas[USCES_COL_SKU_UNIT], 'UTF-8', 'SJIS'));
			$sku['gptekiyo'] = $datas[USCES_COL_SKU_GPTEKIYO];
			if(defined('WCEX_DLSELLER')) {
				$sku['charging_type'] = $datas[USCES_COL_SKU_CHARGINGTYPE];
			}
			$valstr .= '(' . $post_id . ", '".mysql_real_escape_string($meta_key)."', '" . mysql_real_escape_string(serialize($sku)) . "'),";
			
			$valstr = rtrim($valstr, ',');
			$query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES $valstr";
			$dbres = mysql_query($query) or die(mysql_error());
		
		}


		$comp_num++;
		$pre_code = $datas[USCES_COL_ITEM_CODE];
	}
	
	flock($fpi, LOCK_EX);
	fputs($fpi, mb_convert_encoding($log, 'SJIS', 'UTF-8'));
	flock($fpi, LOCK_UN);
	fclose($fpo);
	fclose($fpi);

	$res['status'] = 'success';
	$res['message'] = __(sprintf('%2$s of %1$s lines registration completion, %3$s lines error.',$total_num,$comp_num,$err_num), 'usces');
	return $res;
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

function usces_get_local_cerrency(){
	$locale = get_locale();
	switch( $locale ){
		case 'en':
		case 'en_US':
			$res =  'US';
			break;
		case 'ja':
		case 'ja_JP':
			$res =  'JP';
			break;
		default:
			$res =  'US';
	}
	return $res;
}

function usces_get_local_addressform(){
	$locale = get_locale();
	switch( $locale ){
		case 'en':
		case 'en_US':
			$res =  'US';
			break;
		case 'ja':
		case 'ja_JP':
			$res =  'JP';
			break;
		default:
			$res =  'US';
	}
	return $res;
}

function usces_get_local_target_market(){
	$locale = get_locale();
	switch( $locale ){
		case 'en':
		case 'en_US':
			$res =  'US';
			break;
		case 'ja':
		case 'ja_JP':
			$res =  'JP';
			break;
		default:
			$res =  'US';
	}
	return (array)$res;
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
			
			$formtag .= '<tr class="ttl"><td colspan="2"><h3>'.__('Shipping address information', 'usces').'</h3></td></tr>';
			
			//20100818ysk start
			$formtag .= usces_custom_field_info($data, 'delivery', 'name_pre', 'return');
			//20100818ysk end
			$formtag .= '<tr><th>'.__('Full name', 'usces').'</th><td>' . esc_html($values['delivery']['name1']) . ' ' . esc_html($values['delivery']['name2']) . '</td></tr>';
			$formtag .= '<tr><th>'.__('furigana', 'usces').'</th><td>' . esc_html($values['delivery']['name3']) . ' ' . esc_html($values['delivery']['name4']) . '</td></tr>';
			//20100818ysk start
			$formtag .= usces_custom_field_info($values, 'delivery', 'name_after', 'return');
			//20100818ysk end
			$formtag .= '
			<tr><th>'.__('Zip/Postal Code', 'usces').'</th><td>' . esc_html($values['delivery']['zipcode']) . '</td></tr>
			<tr><th>'.__('Country', 'usces').'</th><td>' . esc_html($usces_settings['country'][$values['delivery']['country']]) . '</td></tr>
			<tr><th>'.__('Province', 'usces').'</th><td>' . esc_html($values['delivery']['pref']) . '</td></tr>
			<tr><th>'.__('city', 'usces').'</th><td>' . esc_html($values['delivery']['address1']) . '</td></tr>
			<tr><th>'.__('numbers', 'usces').'</th><td>' . esc_html($values['delivery']['address2']) . '</td></tr>
			<tr><th>'.__('building name', 'usces').'</th><td>' . esc_html($values['delivery']['address3']) . '</td></tr>
			<tr><th>'.__('Phone number', 'usces').'</th><td>' . esc_html($values['delivery']['tel']) . '</td></tr>
			<tr><th>'.__('FAX number', 'usces').'</th><td>' . esc_html($values['delivery']['fax']) . '</td></tr>';
			//20100818ysk start
			$formtag .= usces_custom_field_info($data, 'delivery', 'fax_after', 'return');
			//20100818ysk end
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
			
			$formtag .= '<tr class="ttl"><td colspan="2"><h3>'.__('Shipping address information', 'usces').'</h3></td></tr>';
			
			//20100818ysk start
			$formtag .= usces_custom_field_info($data, 'delivery', 'name_pre', 'return');
			//20100818ysk end
			$formtag .= '<tr><th>'.__('Full name', 'usces').'</th><td>' . esc_html($values['delivery']['name2']) . ' ' . esc_html($values['delivery']['name1']) . '</td></tr>';
			//20100818ysk start
			$formtag .= usces_custom_field_info($data, 'delivery', 'name_after', 'return');
			//20100818ysk end
			$formtag .= '
			<tr><th>'.__('Address Line1', 'usces').'</th><td>' . esc_html($values['delivery']['address2']) . '</td></tr>
			<tr><th>'.__('Address Line2', 'usces').'</th><td>' . esc_html($values['delivery']['address3']) . '</td></tr>
			<tr><th>'.__('city', 'usces').'</th><td>' . esc_html($values['delivery']['address1']) . '</td></tr>
			<tr><th>'.__('State', 'usces').'</th><td>' . esc_html($values['delivery']['pref']) . '</td></tr>
			<tr><th>'.__('Country', 'usces').'</th><td>' . esc_html($usces_settings['country'][$values['delivery']['country']]) . '</td></tr>
			<tr><th>'.__('Zip', 'usces').'</th><td>' . esc_html($values['delivery']['zipcode']) . '</td></tr>
			<tr><th>'.__('Phone number', 'usces').'</th><td>' . esc_html($values['delivery']['tel']) . '</td></tr>
			<tr><th>'.__('FAX number', 'usces').'</th><td>' . esc_html($values['delivery']['fax']) . '</td></tr>';
			//20100818ysk start
			$formtag .= usces_custom_field_info($data, 'delivery', 'fax_after', 'return');
			//20100818ysk end
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
			<th width="127" scope="row"><em>*</em>'.__('Full name', 'usces').'</th>
			<td width="257">'.__('Familly name', 'usces').'<input name="' . $type . '[name1]" id="name1" type="text" value="' . esc_attr($values['name1']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" /></td>
			<td width="257">'.__('Given name', 'usces').'<input name="' . $type . '[name2]" id="name2" type="text" value="' . esc_attr($values['name2']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" /></td>
			</tr>';
			$formtag .= '<tr class="inp1">
			<th scope="row">'.__('furigana', 'usces').'</th>
			<td>'.__('Familly name', 'usces').'<input name="' . $type . '[name3]" id="name3" type="text" value="' . esc_attr($values['name3']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" /></td>
			<td>'.__('Given name', 'usces').'<input name="' . $type . '[name4]" id="name4" type="text" value="' . esc_attr($values['name4']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" /></td>
			</tr>';
			//20100818ysk start
			$formtag .= usces_custom_field_input($data, $type, 'name_after', 'return');
			//20100818ysk end
			$formtag .= '<tr>
			<th scope="row"><em>*</em>'.__('Zip/Postal Code', 'usces').'</th>
			<td colspan="2"><input name="' . $type . '[zipcode]" id="zipcode" type="text" value="' . esc_attr($values['zipcode']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />100-1000</td>
			</tr>
			<tr>
			<th scope="row"><em>*</em>' . __('Country', 'usces') . '</th>
			<td colspan="2">' . uesces_get_target_market_form( $type, $values['country'] ) . '</td>
			</tr>
			<tr>
			<th scope="row"><em>*</em>'.__('Province', 'usces').'</th>
			<td colspan="2">' . usces_pref_select( $type, $values ) . '</td>
			</tr>
			<tr class="inp2">
			<th scope="row"><em>*</em>'.__('city', 'usces').'</th>
			<td colspan="2"><input name="' . $type . '[address1]" id="address1" type="text" value="' . esc_attr($values['address1']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />'.__('Kitakami Yokohama', 'usces').'</td>
			</tr>
			<tr>
			<th scope="row"><em>*</em>'.__('numbers', 'usces').'</th>
			<td colspan="2"><input name="' . $type . '[address2]" id="address2" type="text" value="' . esc_attr($values['address2']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />3-24-555</td>
			</tr>
			<tr>
			<th scope="row">'.__('building name', 'usces').'</th>
			<td colspan="2"><input name="' . $type . '[address3]" id="address3" type="text" value="' . esc_attr($values['address3']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />'.__('tuhanbuild 4F', 'usces').'</td>
			</tr>
			<tr>
			<th scope="row"><em>*</em>'.__('Phone number', 'usces').'</th>
			<td colspan="2"><input name="' . $type . '[tel]" id="tel" type="text" value="' . esc_attr($values['tel']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />1000-10-1000</td>
			</tr>
			<tr>
			<th scope="row">'.__('FAX number', 'usces').'</th>
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
			<th scope="row"><em>*</em>' . __('Full name', 'usces') . '</th>
			<td>' . __('Given name', 'usces') . '<input name="' . $type . '[name2]" id="name2" type="text" value="' . esc_attr($values['name2']) . '" /></td>
			<td>' . __('Familly name', 'usces') . '<input name="' . $type . '[name1]" id="name1" type="text" value="' . esc_attr($values['name1']) . '" /></td>
			</tr>';
			//20100818ysk start
			$formtag .= usces_custom_field_input($data, $type, 'name_after', 'return');
			//20100818ysk end
			$formtag .= '
			<tr>
			<th scope="row"><em>*</em>' . __('Address Line1', 'usces') . '</th>
			<td colspan="2">' . __('Street address', 'usces') . '<br /><input name="' . $type . '[address2]" id="address2" type="text" value="' . esc_attr($values['address2']) . '" /></td>
			</tr>
			<tr>
			<th scope="row">' . __('Address Line2', 'usces') . '</th>
			<td colspan="2">' . __('Apartment, building, etc.', 'usces') . '<br /><input name="' . $type . '[address3]" id="address3" type="text" value="' . esc_attr($values['address3']) . '" /></td>
			</tr>
			<tr class="inp2">
			<th scope="row"><em>*</em>' . __('city', 'usces') . '</th>
			<td colspan="2"><input name="' . $type . '[address1]" id="address1" type="text" value="' . esc_attr($values['address1']) . '" /></td>
			</tr>
			<tr>
			<th scope="row"><em>*</em>' . __('State', 'usces') . '</th>
			<td colspan="2">' . usces_pref_select( $type, $values ) . '</td>
			</tr>
			<tr>
			<th scope="row"><em>*</em>' . __('Country', 'usces') . '</th>
			<td colspan="2">' . uesces_get_target_market_form( $type, $values['country'] ) . '</td>
			</tr>
			<tr>
			<th scope="row"><em>*</em>' . __('Zip', 'usces') . '</th>
			<td colspan="2"><input name="' . $type . '[zipcode]" id="zipcode" type="text" value="' . esc_attr($values['zipcode']) . '" /></td>
			</tr>
			<tr>
			<th scope="row"><em>*</em>' . __('Phone number', 'usces') . '</th>
			<td colspan="2"><input name="' . $type . '[tel]" id="tel" type="text" value="' . esc_attr($values['tel']) . '" /></td>
			</tr>
			<tr>
			<th scope="row">' . __('FAX number', 'usces') . '</th>
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
		$formtag .= usces_admin_custom_field_input($customdata, $type, 'name_pre');
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
		$formtag .= usces_admin_custom_field_input($customdata, $type, 'name_after');
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
		$formtag .= usces_admin_custom_field_input($customdata, $type, 'fax_after');
		//20100818ysk end
		break;
		
	case 'US':
	default:
		//20100818ysk start
		$formtag .= usces_admin_custom_field_input($customdata, $type, 'name_pre');
		//20100818ysk end
		$formtag .= '
		<tr>
			<td class="label">' . __('name', 'usces') . '</td>
			<td class="col2"><input name="' . $type . '[name2]" type="text" class="text short" value="' . esc_attr($values['name2']) . '" /><input name="' . $type . '[name1]" type="text" class="text short" value="' . esc_attr($values['name1']) . '" /></td>
		</tr>';
		//20100818ysk start
		$formtag .= usces_admin_custom_field_input($customdata, $type, 'name_after');
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
		$formtag .= usces_admin_custom_field_input($customdata, $type, 'fax_after');
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
	
	$country = empty($values['country']) ? usces_get_local_addressform() : $values['country'];
	$prefs = $usces_states[$country];
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
	$res .= '<option value="world_wide"' . ($selected == $key ? ' selected="selected"' : '') . '>World Wide' . "</option>\n";
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
		$pictids = $usces->get_pictids($itemCode);
		if ( empty($options) ) {
			$optstr =  '';
			$options =  array();
		}

		$res .= '<tr>
			<td>' . ($i + 1) . '</td>
			<td>';
			$cart_thumbnail = '<a href="' . get_permalink($post_id) . '">' . wp_get_attachment_image( $pictids[0], array(60, 60), true ) . '</a>';
			$res .= apply_filters('usces_filter_cart_thumbnail', $cart_thumbnail, $post_id, $pictids[0], $i);
			$res .= '</td><td class="aleft">' . esc_html($cartItemName) . '<br />';
		if( is_array($options) && count($options) > 0 ){
			foreach($options as $key => $value){
				$res .= esc_html($key) . ' : ' . nl2br(esc_html(urldecode($value))) . "<br />\n"; 
			}
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
			$res .= '<input name="itemOption[' . $i . '][' . $post_id . '][' . $sku . '][' . $key . ']" type="hidden" value="' . $value . '" />';
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
		$pictids = $usces->get_pictids($itemCode);
		if (empty($options)) {
			$optstr =  '';
			$options =  array();
		}
	
		 $res .= '<tr>
			<td>' . ($i + 1) . '</td>
			<td>';
		$cart_thumbnail = wp_get_attachment_image( $pictids[0], array(60, 60), true );
		 $res .= apply_filters('usces_filter_cart_thumbnail', $cart_thumbnail, $post_id, $pictids[0], $i);
		 $res .= '</td><td class="aleft">' . $cartItemName . '<br />';
		if( is_array($options) && count($options) > 0 ){
			foreach($options as $key => $value){
				 $res .= esc_html($key) . ' : ' . nl2br(esc_html(urldecode($value))) . "<br />\n"; 
			}
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

function usces_post_reg_orderdata(){
	global $usces, $wpdb;
	$entry = $usces->cart->get_entry();
	$acting = $_GET['acting'];
	$args = func_get_args();
	$order_id = $args[0];
	$results = $args[1];
	
	if( $order_id ){

		switch ( $acting ) {
			case 'epsilon':
				$trans_id = $_REQUEST['trans_code'];
				break;
			case 'paypal':
				$trans_id = $_REQUEST['txn_id'];
				break;
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
				$mquery = $wpdb->prepare("INSERT INTO $order_table_meta_name 
											( order_id, meta_key, meta_value ) VALUES 
											(%d, %s, %s)", 
											$order_id, 
											'acting_'.$_REQUEST['sendpoint'], 
											serialize($zeus_convs)
										);
				$wpdb->query( $mquery );
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
?>
