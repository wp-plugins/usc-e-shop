<?php

function usces_ajax_send_mail() {
	global $wpdb, $usces;
	
	$order_para = array(
			'to_name' => sprintf(__('Mr/Mrs %s', 'usces'), trim(urldecode($_POST['name']))),
			'to_address' => trim($_POST['mailaddress']), 
			'from_name' => get_option('blogname'), 
			'from_address' => $usces->options['order_mail'], 
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
		$msg_body .= __('Request of','usces') . " : " . sprintf(__('Mr/Mrs %s', 'usces'), ($data['order_name1'] . ' ' . $data['order_name2'])) . "\r\n";
		$msg_body .= __('estimate number','usces') . " : " . $order_id . "\r\n";
	}else{
		$msg_body = "\r\n\r\n\r\n" . __('** Article order contents **','usces') . "\r\n";
		$msg_body .= "******************************************************************\r\n";
		$msg_body .= apply_filters('usces_filter_order_confirm_mail_first', NULL, $data);
		$msg_body .= __('Buyer','usces') . " : " . sprintf(__('Mr/Mrs %s', 'usces'), ($data['order_name1'] . ' ' . $data['order_name2'])) . "\r\n";
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
				$meisai .= $key . ' : ' . $value . "\r\n"; 
			}
		}
		$meisai .= __('Unit price','usces') . " ".number_format($skuPrice) . __(' * ','usces') . $cart_row['quantity'] . "\r\n";
	}
	
	$meisai .= "=================================================================\r\n";
	$meisai .= __('total items','usces') . "    : " . number_format($data['order_item_total_price']) . __('dollars','usces') . "\r\n";

	if ( $data['order_usedpoint'] != 0 )
		$meisai .= __('use of points','usces') . " : " . number_format($data['order_usedpoint']) . __('Points','usces') . "\r\n";
	if ( $data['order_discount'] != 0 )
		$meisai .= __('Special Price','usces') . "    : " . number_format($data['order_discount']) . __('dollars','usces') . "\r\n";
	$meisai .= __('Shipping','usces') . "     : " . number_format($data['order_shipping_charge']) . __('dollars','usces') . "\r\n";
	if ( $payment['settlement'] == 'COD' )
		$meisai .= __('C.O.D','usces') . "  : " . number_format($data['order_cod_fee']) . __('dollars','usces') . "\r\n";
	if ( !empty($usces->options['tax_rate']) )
		$meisai .= __('consumption tax','usces') . "    : " . number_format($data['order_tax']) . __('dollars','usces') . "\r\n";
	$meisai .= "------------------------------------------------------------------\r\n";
	$meisai .= __('Payment amount','usces') . "  : " . number_format($total_full_price) . __('dollars','usces') . "\r\n";
	$meisai .= "------------------------------------------------------------------\r\n\r\n";

	$msg_body .= apply_filters('usces_filter_order_confirm_mail_meisai', $meisai, $data);


	
	$msg_shipping .= __('** A shipping address **','usces') . "\r\n";
	$msg_shipping .= "******************************************************************\r\n";
	$msg_shipping .= __('A destination name','usces') . "    : " . sprintf(__('Mr/Mrs %s', 'usces'), ($deli['name1'] . ' ' . $deli['name2'])) . " \r\n";
	$msg_shipping .= __('Zip/Postal Code','usces') . "  : " . $deli['zipcode'] . "\r\n";
	$msg_shipping .= __('Address','usces') . "    : " . $deli['pref'] . $deli['address1'] . $deli['address2'] . " " . $deli['address3'] . "\r\n";
	$msg_shipping .= __('Phone number','usces') . "  : " . $deli['tel'] . "\r\n";

	$msg_shipping .= __('Delivery Time','usces') . " : " . $data['order_delivery_time'] . "\r\n";
	if ( $data['order_delidue_date'] == NULL || $data['order_delidue_date'] == '#none#' ) {
		$msg_shipping .= "\r\n";
	}else{
		$msg_shipping .= __('Shipping date', 'usces') . "  : " . $data['order_delidue_date'] . "\r\n";
		$msg_shipping .= __("* A shipment due date is a day to ship an article, and it's not the arrival day.", 'usces') . "\r\n";
		$msg_shipping .= "\r\n";
	}
	$deli_meth = (int)$data['order_delivery_method'];
	if( $deli_meth > 0 ){
		$deli_index = $usces->get_delivery_method_index($deli_meth);
		$msg_shipping .= __('Delivery Method','usces') . " : " . $usces->options['delivery_method'][$deli_index]['name'] . "\r\n";
	}
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
			$message = $mail_data['header']['receiptmail'] . $mail_data['footer']['receiptmail'];
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
	$msg_body .= __('Buyer','usces') . " : " . sprintf(__('Mr/Mrs %s', 'usces'), ($entry['customer']['name1'] . ' ' . $entry['customer']['name2'])) . "\r\n";
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
				$meisai .= $key . ' : ' . $value . "\r\n"; 
			}
		}
		$meisai .= __('Unit price','usces') . " ".number_format($skuPrice)." " . __('dollars','usces') . __(' * ','usces') . $cart_row['quantity'] . "\r\n";
	}
	$meisai .= "=================================================================\r\n";
	$meisai .= __('total items','usces') . "    : " . number_format($entry['order']['total_items_price']) . __('dollars','usces') . "\r\n";

	if ( $entry['order']['usedpoint'] != 0 )
		$meisai .= __('use of points','usces') . " : " . number_format($entry['order']['usedpoint']) . __('Points','usces') . "\r\n";
	if ( $data['order_discount'] != 0 )
		$meisai .= __('Special Price','usces') . "    : " . number_format($entry['order']['discount']) . __('dollars','usces') . "\r\n";
	$meisai .= __('Shipping','usces') . "     : " . number_format($entry['order']['shipping_charge']) . __('dollars','usces') . "\r\n";
	if ( $payment['settlement'] == 'COD' )
		$meisai .= __('C.O.D','usces') . "  : " . number_format($entry['order']['cod_fee']) . __('dollars','usces') . "\r\n";
	if ( !empty($usces->options['tax_rate']) )
		$meisai .= __('consumption tax','usces') . "     : " . number_format($entry['order']['tax']) . __('dollars','usces') . "\r\n";
	$meisai .= "------------------------------------------------------------------\r\n";
	$meisai .= __('Payment amount','usces') . "  : " . number_format($entry['order']['total_full_price']) . __('dollars','usces') . "\r\n";
	$meisai .= "------------------------------------------------------------------\r\n\r\n";

	$msg_body .= apply_filters('usces_filter_send_order_mail_meisai', $meisai, $data);


	
	$msg_shipping .= __('** A shipping address **','usces') . "\r\n";
	$msg_shipping .= "******************************************************************\r\n";
	$msg_shipping .= __('A destination name','usces') . "    : " . sprintf(__('Mr/Mrs %s', 'usces'), ($entry['delivery']['name1'] . ' ' . $entry['delivery']['name2'])) . "\r\n";
	$msg_shipping .= __('Zip/Postal Code','usces') . "  : " . $entry['delivery']['zipcode'] . "\r\n";
	$msg_shipping .= __('Address','usces') . "    : " . $entry['delivery']['pref'] . $entry['delivery']['address1'] . $entry['delivery']['address2'] . " " . $entry['delivery']['address3'] . "\r\n";
	$msg_shipping .= __('Phone number','usces') . "  : " . $entry['delivery']['tel'] . "\r\n";

	$msg_shipping .= __('Delivery Time','usces') . " : " . $entry['order']['delivery_time'] . "\r\n";
	$deli_meth = (int)$entry['order']['delivery_method'];
	if( $deli_meth > 0 ){
		$deli_index = $usces->get_delivery_method_index($deli_meth);
		$msg_shipping .= __('Delivery Method','usces') . " : " . $usces->options['delivery_method'][$deli_index]['name'] . "\r\n";
	}
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
	$message = $mail_data['header']['inquiry'] . "\r\n\r\n" . $reserve . $inq_contents . "\r\n\r\n" . $mail_data['footer']['inquiry'];

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
			. __('I seem to have you cancel it when the body does not have memorizing to this email.','usces') . "\n\r"
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

	$header = "From: " . $para['from_name'] . " <{$para['from_address']}>\r\n"
//			."To: " . mb_convert_encoding($para['to_name'], "SJIS") . " <{$para['to_address']}>\r\n"
			."Return-Path: {$para['return_path']}\r\n";

	$subject = $para['subject'];
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
	
	$cart = $usces->cart->get_cart();
	$entry = $usces->cart->get_entry();
	if( empty($cart) )
		return 0;
	
	
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

	$res = $wpdb->query( $query );
		//usces_log('res : '.$res, 'acting_transaction.log');
//$wpdb->print_error();
//	echo $query;
//	exit;
	if( $res === false){
		$order_id = false;
	}else{
		$order_id = $wpdb->insert_id;
	}

	if ( !$order_id ) :
	
		return false;
		
	else :
	
		$usces->cart->set_order_entry( array('ID' => $order_id) );
	
		if ( $member['ID'] ) {
		
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
	
		if ( 'zeus_conv' == $usces->payment_results['acting'] ) {
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
			$mquery = $wpdb->prepare("INSERT INTO $order_table_meta_name ( order_id, meta_key, meta_value ) 
										VALUES (%d, %s, %s)", $order_id, 'acting_'.$_REQUEST['sendpoint'], serialize($zeus_convs) );
			$wpdb->query( $mquery );
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
					$_POST['mem_email'], 
					$_POST['mem_status'], 
					$_POST['mem_point'], 
					$_POST['mem_name1'], 
					$_POST['mem_name2'], 
					$_POST['mem_name3'], 
					$_POST['mem_name4'], 
					$_POST['mem_zip'], 
					$_POST['mem_pref'], 
					$_POST['mem_address1'], 
					$_POST['mem_address2'], 
					$_POST['mem_address3'], 
					$_POST['mem_tel'], 
					$_POST['mem_fax'], 
					$ID
				);

//20100818ysk start
	//$res = $wpdb->query( $query );
	$res[0] = $wpdb->query( $query );
	if(false === $res[0]) 
		return false;
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

//20100818ysk start
	//$res = $wpdb->query( $query );
	$res[0] = $wpdb->query( $query );
	if(false === $res[0]) 
		return false;
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

	$acting = $_GET['acting'];
	$results = array();
	switch ( $acting ) {
		case 'epsilon':
			if(isset($_GET['duplicate']) && $_GET['duplicate'] == 1){
				$results[0] = 'duplicate';
			}else if(isset($_GET['result'])){
				$results[0] = (int)$_GET['result'];
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
		break;
		
		case 'paypal':
			require_once($usces->options['settlement_path'] . "paypal.php");
			$results = paypal_check($usces_paypal_url);
	
			break;
			
		case 'zeus_card':
			$results = $_POST;
			if( $_REQUEST['acting_return'] ){
				$results[0] = 1;
			}else{
				$results[0] = 0;
			}
			$results['payment_status'] = 1;
			break;
			
		case 'zeus_conv':
			$results = $_GET;
			if( $_REQUEST['acting_return'] ){
				$results[0] = 1;
			}else{
				$results[0] = 0;
			}
			$results['payment_status'] = 1;
			break;
			
		case 'remise_card':
			$results = $_POST;
			if( $_REQUEST['acting_return'] && '   ' == $_REQUEST['X-ERRCODE']){
				$results[0] = 1;
			}else{
				$results[0] = 0;
			}
			$results['payment_status'] = 1;
			break;
			
		case 'remise_conv':
			$results = $_GET;
			if( $_REQUEST['acting_return'] && isset($_REQUEST['X-JOB_ID']) && '0:0000' == $_REQUEST['X-R_CODE']){
				$results[0] = 1;
			}else{
				$results[0] = 0;
			}
			break;
			
//20101018ysk start
		case 'jpayment_card':
			$results = $_GET;
			$results[0] = ($_GET['rst'] == 1) ? 1 : 0;
			break;

		case 'jpayment_conv':
			$results = $_GET;
			$results[0] = ($_GET['rst'] == 1 and $_GET['ap'] == 'CPL_PRE') ? 1 : 0;
			break;

		case 'jpayment_webm':
			$results = $_GET;
			$results[0] = ($_GET['rst'] == 1) ? 1 : 0;
			break;

		case 'jpayment_bitc':
			$results = $_GET;
			$results[0] = ($_GET['rst'] == 1) ? 1 : 0;
			break;

		case 'jpayment_bank':
			$results = $_GET;
			$results[0] = ($_GET['rst'] == 1) ? 1 : 0;
			break;
//20101018ysk end

		default:
			$results = $_GET;
			if( $_REQUEST['result'] ){
				$results[0] = 1;
			}else{
				$results[0] = 0;
			}
			$results['payment_status'] = 1;
			break;
	}
	
	return $results;
}

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

function usces_item_uploadcsv(){
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
}

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

function usces_trackPageview_cart($push){
	$push[] = "'_trackPageview','/wc_cart'";
	return $push;
}

function usces_trackPageview_customer($push){
	$push[] = "'_trackPageview','/wc_customer'";
	return $push;
}

function usces_trackPageview_delivery($push){
	$push[] = "'_trackPageview','/wc_delivery'";
	return $push;
}

function usces_trackPageview_confirm($push){
	$push[] = "'_trackPageview','/wc_confirm'";
	return $push;
}

function usces_trackPageview_ordercompletion($push){
	global $usces;
	$sesdata = $usces->cart->get_entry();
	$order_id = $sesdata['order']['ID'];
	$data = $usces->get_order_data($order_id, 'direct');
	$cart = unserialize($data['order_cart']);
	$total_price = $usces->get_total_price( $cart ) - $data['order_discount'];
	
	$push[] = "'_trackPageview','/wc_ordercompletion'";
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
		sort($cats);
		$category = get_cat_name( $cats[0] );
		$push[] = "'_addItem', '" . $order_id . "', '" . $sku . "', '" . $itemName . "', '" . $category . "', '" . $skuPrice . "', '" . $quantity . "'";
	}
	$push[] = "'_trackTrans'";

	return $push;
}

function usces_trackPageview_error($push){
	$push[] = "'_trackPageview','/wc_error'";
	return $push;
}

function usces_trackPageview_member($push){
	$push[] = "'_trackPageview','/wc_member'";
	return $push;
}

function usces_trackPageview_login($push){
	$push[] = "'_trackPageview','/wc_login'";
	return $push;
}

function usces_trackPageview_editmemberform($push){
	$push[] = "'_trackPageview','/wc_editmemberform'";
	return $push;
}

function usces_trackPageview_newcompletion($push){
	$push[] = "'_trackPageview','/wc_newcompletion'";
	return $push;
}

function usces_trackPageview_newmemberform($push){
	$push[] = "'_trackPageview','/wc_newmemberform'";
	return $push;
}

function usces_trackPageview_deletemember($push){
	$push[] = "'_trackPageview','/wc_deletemember'";
	return $push;
}

?>
