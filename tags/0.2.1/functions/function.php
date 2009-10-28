<?php

function usces_ajax_send_mail() {
	global $wpdb, $usces;
	
	$order_para = array(
			'to_name' => $_POST['name'] . __('Mr/Mrs','usces'),
			'to_address' => $_POST['mailaddress'], 
			'from_name' => get_option('blogname'), 
			'from_address' => $usces->options['order_mail'], 
			'return_path' => $usces->options['error_mail'],
			'subject' => $_POST['subject'],
			'message' => $_POST['message']
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


//	$cart = $usces->cart->get_cart();
//	$entry = $usces->cart->get_entry();
	$mail_data = $usces->options['mail_data'];
	$payment = $usces->getPayments( $data['order_payment_name'] );
	$res = false;

	if($_POST['mode'] == 'mitumoriConfirmMail'){
		$msg_body = "\r\n\r\n\r\n" . __('Estimate','usces') . "\r\n";
		$msg_body .= "******************************************************************\r\n";
		$msg_body .= __('Request of','usces') . "：　" . $data['order_name1'] . ' ' . $data['order_name2'] . ' ' . __('Mr/Mrs','usces') . "\r\n";
		$msg_body .= __('estimate number','usces') . "：" . $order_id . "\r\n";
	}else{
		$msg_body = "\r\n\r\n\r\n" . __('** Article order contents **','usces') . "\r\n";
		$msg_body .= "******************************************************************\r\n";
		$msg_body .= __('Buyer','usces') . "：　" . $data['order_name1'] . ' ' . $data['order_name2'] . ' ' . __('Mr/Mrs','usces') . "\r\n";
		$msg_body .= __('Order number','usces') . "：" . $order_id . "\r\n";
	}
	$msg_body .= __('items','usces') . "　：\r\n";

	foreach ( $cart as $cart_row ) {
		$post_id = $cart_row['post_id'];
		$sku = $cart_row['sku'];
		$quantity = $cart_row['quantity'];
		$options = $cart_row['options'];
		$itemCode = $usces->getItemCode($post_id);
		$itemName = $usces->getItemName($post_id);
		$skuPrice = $cart_row['price'];
		$pictids = $usces->get_pictids($itemCode);
		if (!empty($options)) {
			$optstr = implode(',', $options);
		} else { 
			$optstr =  '';
			$options =  array();
		}
		$msg_body .= "------------------------------------------------------------------\r\n";
		$msg_body .= "$itemName $itemCode $sku \r\n";
		if($optstr != '')
			$msg_body .= "$optstr\r\n";
		$msg_body .= __('Unit price','usces') . " ".number_format($skuPrice) . " × " . $cart_row['quantity'] . "\r\n";
	}
	
	$msg_body .= "=================================================================\r\n";
	$msg_body .= __('total items','usces') . "　　　　：" . number_format($data['order_item_total_price']) . __('yen','usces') . "\r\n";
	if ( $data['order_usedpoint'] != 0 )
		$msg_body .= __('use of points','usces') . "　：" . number_format($data['order_usedpoint']) . __('Point','usces') . "\r\n";
	$msg_body .= __('Special discount','usces') . "　　　　：" . number_format($data['order_discount']) . __('yen','usces') . "\r\n";
	$msg_body .= __('Shipping','usces') . "　　　　　：" . number_format($data['order_shipping_charge']) . __('yen','usces') . "\r\n";
	if ( $payment['settlement'] == 'COD' )
		$msg_body .= __('C.O.D','usces') . "　　：" . number_format($data['order_cod_fee']) . __('yen','usces') . "\r\n";
	if ( !empty($usces->options['tax_rate']) )
		$msg_body .= __('consumption tax','usces') . "　　　　　：" . number_format($data['order_tax']) . __('yen','usces') . "\r\n";
	$msg_body .= "------------------------------------------------------------------\r\n";
	$msg_body .= __('Payment amount','usces') . "　　：" . number_format($total_full_price) . __('yen','usces') . "\r\n";
	$msg_body .= "------------------------------------------------------------------\r\n\r\n";
	
	$msg_body .= __('** A shipping address **','usces') . "\r\n";
	$msg_body .= "******************************************************************\r\n";
	$msg_body .= __('A destination name','usces') . "　　　　：" . $deli['name1'] . $deli['name2'] . __('Mr/Mrs','usces') . "　\r\n";
	$msg_body .= __('Zip/Postal Code','usces') . "　　：" . $deli['zipcode'] . "\r\n";
	$msg_body .= __('An address','usces') . "　　　　：" . $deli['pref'] . $deli['address1'] . $deli['address2'] . "　" . $deli['address3'] . "\r\n";
	$msg_body .= __('Phone number','usces') . "　　：" . $deli['tel'] . "\r\n";

	$msg_body .= __('Delivery Time','usces') . "：" . $data['order_delivery_time'] . "\r\n";
	$msg_body .= "------------------------------------------------------------------\r\n";
	$msg_body .= __('** For some region, to deliver the items in the morning is not possible.','usces') . "\r\n";
	$msg_body .= __('** WE may not always be able to deliver the items on time which you desire.','usces') . "　\r\n";
	$msg_body .= "------------------------------------------------------------------\r\n\r\n";

	$msg_body .= __('** Payment method **','usces') . "\r\n";
	$msg_body .= "******************************************************************\r\n";
	$msg_body .= $payment['name']. "\r\n\r\n";
	if ( $payment['settlement'] == 'transferAdvance' || $payment['settlement'] == 'transferDeferred' ) {
		$msg_body .= __('Transfer','usces') . "　：\r\n";
		$msg_body .= $usces->options['transferee'] . "\r\n";
		$msg_body .= "------------------------------------------------------------------\r\n\r\n";
	}
	$msg_body .= "\r\n";
	$msg_body .= __('** Others / a demand **','usces') . "\r\n";
	$msg_body .= "******************************************************************\r\n";
	$msg_body .= $data['order_note'] . "\r\n\r\n";
	$msg_body .= "------------------------------------------------------------------\r\n\r\n";
	$msg_body .= "\r\n";

	$msg_body .= __('Please inform it of any questions from [an inquiry].','usces') . "\r\n";
	$msg_body .= "------------------------------------------------------------------\r\n\r\n";

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
	global $usces;
	$cart = $usces->cart->get_cart();
	$entry = $usces->cart->get_entry();
	$mail_data = $usces->options['mail_data'];
	$payment = $usces->getPayments( $entry['order']['payment_name'] );
	$res = false;

	$msg_body = "\r\n\r\n\r\n" . __('** content of ordered items **','usces') . "\r\n";
	$msg_body .= "******************************************************************\r\n";
	$msg_body .= __('Buyer','usces') . "：　" . $entry['customer']['name1'] . ' ' . $entry['customer']['name2'] . ' ' . __('Mr/Mrs','usces') . "\r\n";
	$msg_body .= __('Order number','usces') . "：" . $order_id . "\r\n";
	$msg_body .= __('items','usces') . "　：\r\n";
	foreach ( $cart as $cart_row ) {
		$post_id = $cart_row['post_id'];
		$sku = $cart_row['sku'];
		$quantity = $cart_row['quantity'];
		$options = $cart_row['options'];
		$itemCode = $usces->getItemCode($post_id);
		$itemName = $usces->getItemName($post_id);
		$skuPrice = $cart_row['price'];
		$pictids = $usces->get_pictids($itemCode);
		if (!empty($options)) {
			$optstr = implode(',', $options);
		} else { 
			$optstr =  '';
			$options =  array();
		}
		$msg_body .= "------------------------------------------------------------------\r\n";
		$msg_body .= "$itemName $itemCode $sku \r\n";
		if($optstr != '')
			$msg_body .= "$optstr\r\n";
		$msg_body .= __('Unit price','usces') . " ".number_format($skuPrice)." " . __('yen','usces') . " × " . $cart_row['quantity'] . "\r\n";
	}
	$msg_body .= "=================================================================\r\n";
	$msg_body .= __('total items','usces') . "　　　　：" . number_format($entry['order']['total_items_price']) . __('yen','usces') . "\r\n";
	if ( $entry['order']['usedpoint'] != 0 )
		$msg_body .= __('use of points','usces') . "　：" . number_format($entry['order']['usedpoint']) . __('Point','usces') . "\r\n";
	$msg_body .= __('Special discount','usces') . "　　　　：" . number_format($entry['order']['discount']) . __('yen','usces') . "\r\n";
	$msg_body .= __('Shipping','usces') . "　　　　　：" . number_format($entry['order']['shipping_charge']) . __('yen','usces') . "\r\n";
	if ( $payment['settlement'] == 'COD' )
		$msg_body .= __('C.O.D','usces') . "　　：" . number_format($entry['order']['cod_fee']) . __('yen','usces') . "\r\n";
	if ( !empty($usces->options['tax_rate']) )
		$msg_body .= __('consumption tax','usces') . "　　　　　：" . number_format($entry['order']['tax']) . __('yen','usces') . "\r\n";
	$msg_body .= "------------------------------------------------------------------\r\n";
	$msg_body .= __('Payment amount','usces') . "　　：" . number_format($entry['order']['total_full_price']) . __('yen','usces') . "\r\n";
	$msg_body .= "------------------------------------------------------------------\r\n\r\n";
	
	$msg_body .= __('** A shipping address **','usces') . "\r\n";
	$msg_body .= "******************************************************************\r\n";
	$msg_body .= __('A destination name','usces') . "　　　　：" . $entry['delivery']['name1'] . $entry['delivery']['name2'] . "　" . __('Mr/Mrs','usces') . "\r\n";
	$msg_body .= __('Zip/Postal Code','usces') . "　　：" . $entry['delivery']['zipcode'] . "\r\n";
	$msg_body .= __('An address','usces') . "　　　　：" . $entry['delivery']['pref'] . $entry['delivery']['address1'] . $entry['delivery']['address2'] . "　" . $entry['delivery']['address3'] . "\r\n";
	$msg_body .= __('Phone number','usces') . "　　：" . $entry['delivery']['tel'] . "\r\n";

	$msg_body .= __('Delivery Time','usces') . "：" . $entry['order']['delivery_time'] . "\r\n";
	$msg_body .= "------------------------------------------------------------------\r\n";
	$msg_body .= __('** For some region, to deliver the items in the morning is not possible.','usces') . "\r\n";
	$msg_body .= "　" . __('** WE may not always be able to deliver the items on time which you desire.','usces') . "\r\n";
	$msg_body .= "------------------------------------------------------------------\r\n\r\n";

	$msg_body .= __('** Payment method **','usces') . "\r\n";
	$msg_body .= "******************************************************************\r\n";
	$msg_body .= $payment['name']. "\r\n\r\n";
	if ( $payment['settlement'] == 'transferAdvance' || $payment['settlement'] == 'transferDeferred' ) {
		$msg_body .= __('Transfer','usces') . "　：\r\n";
		$msg_body .= $usces->options['transferee'] . "\r\n";
		$msg_body .= "------------------------------------------------------------------\r\n\r\n";
	}
	$msg_body .= "\r\n";
	$msg_body .= __('** Others / a demand **','usces') . "\r\n";
	$msg_body .= "******************************************************************\r\n";
	$msg_body .= $entry['order']['note'] . "\r\n\r\n";
	$msg_body .= "------------------------------------------------------------------\r\n\r\n";
	$msg_body .= "\r\n";

	$msg_body .= __('I will inform it of shipment completion by an email.','usces') . "\r\n";
	$msg_body .= __('Please inform it of any questions from [an inquiry].','usces') . "\r\n";
	$msg_body .= "------------------------------------------------------------------\r\n\r\n";

	$subject = $mail_data['title']['thankyou'];
	$message = $mail_data['header']['thankyou'] . $msg_body . $mail_data['footer']['thankyou'];
//var_dump($msg_body);exit;
	$confirm_para = array(
			'to_name' => $entry["customer"]["name1"] . ' ' . $entry["customer"]["name2"] . '様',
			'to_address' => $entry['customer']['mailaddress1'], 
			'from_name' => get_bloginfo('name'),
			'from_address' => $usces->options['sender_mail'],
			'return_path' => $usces->options['error_mail'],
			'subject' => $subject,
			'message' => $message
			);

	if ( usces_send_mail( $confirm_para ) ) {
	
		$subject = $mail_data['title']['order'];
		$message = $mail_data['header']['order'] . $msg_body . $mail_data['footer']['order'];
		
		$order_para = array(
				'to_name' => __('An order email','usces'),
				'to_address' => $usces->options['order_mail'], 
				'from_name' => $entry["customer"]["name1"] . ' ' . $entry["customer"]["name2"] . '様',
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
	$inq_name = wp_specialchars(trim($_POST["inq_name"]));
	$inq_contents = wp_specialchars(trim($_POST["inq_contents"]));
	$inq_mailaddress = wp_specialchars(trim($_POST["inq_mailaddress"]));

	$subject =  $mail_data['title']['inquiry'];
	$message = $mail_data['header']['inquiry'] . "\r\n\r\n" . $inq_contents . "\r\n\r\n" . $mail_data['footer']['inquiry'];

	$para1 = array(
			'to_name' => $inq_name . __('Mr/Mrs','usces'),
			'to_address' => $inq_mailaddress, 
			'from_name' => get_bloginfo('name'),
			'from_address' => $usces->options['sender_mail'],
			'return_path' => $usces->options['error_mail'],
			'subject' => $subject,
			'message' => $message
			);
			
		$res0 = usces_send_mail( $para1 );
	if ( $res0 ) {
	
		$subject =  __('** An inquiry **','usces');
		$message = $_POST['inq_contents'];
	
		$para2 = array(
				'to_name' => __('An inquiry email','usces'),
				'to_address' => $usces->options['inquiry_mail'], 
				'from_name' => $inq_name . __('Mr/Mrs','usces'),
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

function usces_send_regmembermail() {
	global $usces;
	$res = false;
	$mail_data = $usces->options['mail_data'];

	$subject =  $mail_data['title']['membercomp'];
	$message = $mail_data['header']['membercomp'] . $mail_data['footer']['membercomp'];
	$name = wp_specialchars(trim($_POST['member']['name1'])) . wp_specialchars(trim($_POST['member']['name2']));
	$mailaddress1 = wp_specialchars(trim($_POST['member']['mailaddress1']));

	$para1 = array(
			'to_name' => $name . __('Mr/Mrs','usces'),
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

	$subject = __('** A password change **','usces');
	$message = __('Please, click the following URL, and please change a password.','usces') . "\n\r\n\r\n\r"
			. $url . "\n\r\n\r\n\r"
			. "-----------------------------------------------------\n\r"
			. __('I seem to have you cancel it when the body does not have memorizing to this email.','usces') . "\n\r"
			. "-----------------------------------------------------\n\r\n\r\n\r"
			. $usces->options['mail_data']['footer']['footerlogo'];

	$para1 = array(
			'to_name' => $_SESSION["usces_lostmail"] . __('Mr/Mrs','usces'),
			'to_address' => $_SESSION["usces_lostmail"], 
			'from_name' => get_bloginfo('name'),
			'from_address' => $usces->options['sender_mail'],
			'return_path' => $usces->options['error_mail'],
			'subject' => $subject,
			'message' => $message
			);

	$res = usces_send_mail( $para1 );
	
	if($res === false) {
		$usces->error_message = __('Error： I was not able to transmit an email.','usces');
		$page = 'lostmemberpassword';
	} else {
		$page = 'lostcompletion';
	}

	return $page;

}

function usces_send_mail( $para ) {
	global $usces;

	$header = "From: " . $para['from_name'] . " <{$para['from_address']}>\r\n"
//			."To: " . mb_convert_encoding($para['to_name'], "SJIS") . " <{$para['to_address']}>\r\n"
			."Return-Path: {$para['return_path']}\r\n";

	$subject = $para['subject'];
	$message = $para['message'];
	
	ini_set( "SMTP", "{$usces->options['smtp_hostname']}" );
	ini_set( "smtp_port", 25 );
	ini_set( "sendmail_from", "" );
	
	// 送信実行
	$res = @wp_mail( $para['to_address'] , $subject , $message, $header );
	
	return $res;

}


function usces_reg_orderdata( $results = array() ) {
	global $wpdb, $usces;
	//$wpdb->show_errors();
	
	$cart = $usces->cart->get_cart();
	$item_total_price = $usces->get_total_price( $cart );
	$entry = $usces->cart->get_entry();
	$member = $usces->get_member();
	$order_table_name = $wpdb->prefix . "usces_order";
	$order_table_meta_name = $wpdb->prefix . "usces_order_meta";
	$member_table_name = $wpdb->prefix . "usces_member";
	$set = $usces->getPayments( $entry['order']['payment_name'] );
	$status = ( $set['settlement'] == 'transferAdvance' || $set['settlement'] == 'transferDeferred' ) ? 'noreceipt' : '';
	
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
					date('Y-m-d H:i:s'), 
					null, 
					$status
				);

	$wpdb->query( $query );
//	$wpdb->print_error();
//	echo $query;
//	exit;
	$order_id = $wpdb->insert_id;
	
	if ( !$order_id ) :
	
		return false;
		
	else :
	
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
											VALUES (%d, %s, %s, %s)", $order_id, $key, $value);
				$wpdb->query( $mquery );
			}
		}
	
	endif;
	
	foreach($cart as $cartrow){
		$zaikonum = $usces->getItemZaikoNum( $cartrow['post_id'], $cartrow['sku'] );
		if($zaikonum == '') continue;
		$zaikonum = $zaikonum - $cartrow['quantity'];
		$usces->updateItemZaikoNum( $cartrow['post_id'], $cartrow['sku'], $zaikonum );
		if($zaikonum <= 0) $usces->updateItemZaiko( $cartrow['post_id'], $cartrow['sku'], 2 );
	}
	
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
					date('Y-m-d H:i:s'), 
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
//	//在庫引き落とし
//	foreach($cart as $cartrow){
//		$zaikonum = $usces->getItemZaikoNum( $cartrow['post_id'], $cartrow['sku'] );
//		if($zaikonum == '') continue;
//		$zaikonum = $zaikonum - $cartrow['quantity'];
//		$usces->updateItemZaikoNum( $cartrow['post_id'], $cartrow['sku'], $zaikonum );
//		if($zaikonum <= 0) $usces->updateItemZaiko( $cartrow['post_id'], $cartrow['sku'], 2 );
//	}
	
	$usces->cart->crear_cart();
	return $res;
	
}

function usces_delete_memberdata() {
	global $wpdb, $usces;
	if(!isset($_REQUEST['member_id']) || $_REQUEST['member_id'] == '') return 0;
	$member_table_name = $wpdb->prefix . "usces_member";
	$ID = $_REQUEST['member_id'];

	$query = $wpdb->prepare("DELETE FROM $member_table_name WHERE ID = %d", $ID);
	$res = $wpdb->query( $query );
	
	return $res;
}

function usces_update_memberdata() {
	global $wpdb, $usces;
	
	$member_table_name = $wpdb->prefix . "usces_member";

	$ID = (int)$_REQUEST['member_id'];

$wpdb->show_errors();
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

	$res = $wpdb->query( $query );

	
	return $res;
		
}

function usces_delete_orderdata() {
	global $wpdb, $usces;
	if(!isset($_REQUEST['order_id']) || $_REQUEST['order_id'] == '') return 0;
	$order_table_name = $wpdb->prefix . "usces_order";
	$ID = $_REQUEST['order_id'];

	$query = $wpdb->prepare("DELETE FROM $order_table_name WHERE ID = %d", $ID);
	$res = $wpdb->query( $query );
	
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
	$order_modified = $taio == 'completion' ? date('Y-m-d') : '';
	$ordercheck = isset($_POST['check']) ? serialize($_POST['check']) : '';
	
//$wpdb->show_errors();
	$query = $wpdb->prepare(
				"UPDATE $order_table_name SET 
					`order_email`=%s, `order_name1`=%s, `order_name2`=%s, `order_name3`=%s, `order_name4`=%s, 
					`order_zip`=%s, `order_pref`=%s, `order_address1`=%s, `order_address2`=%s, `order_address3`=%s, 
					`order_tel`=%s, `order_fax`=%s, `order_delivery`=%s, `order_cart`=%s, `order_note`=%s, 
					`order_delivery_method`=%d, `order_delivery_time`=%s, `order_payment_name`=%s, `order_item_total_price`=%d, `order_getpoint`=%d, `order_usedpoint`=%d, 
					`order_discount`=%d, `order_shipping_charge`=%d, `order_cod_fee`=%d, `order_tax`=%d, `order_modified`=%s, 
					`order_status`=%s, `order_check`=%s 
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
					$ordercheck,
					$ID
				);

	$res = $wpdb->query( $query );

	$usces->cart->crear_cart();
	
return $res;
		
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
//	//在庫引き落とし
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
	<usces_zaiko_status<?php echo serialize(get_option('usces_zaiko_status')); ?></usces_zaiko_status>
	<usces_customer_status><?php echo serialize(get_option('usces_customer_status')); ?></usces_customer_status>
	<usces_payment_structure><?php echo serialize(get_option('usces_payment_structure')); ?></usces_payment_structure>
	<usces_display_mode><?php echo serialize(get_option('usces_display_mode')); ?></usces_display_mode>
	<usces_pref><?php echo serialize(get_option('usces_pref')); ?></usces_pref>
	<usces_shipping_rule><?php echo serialize(get_option('usces_shipping_rule')); ?></usces_shipping_rule>
	<shipping_charge_structure><?php echo serialize(get_option('shipping_charge_structure')); ?></shipping_charge_structure>

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

	$parts = explode('<shipping_charge_structure>', $xml);
	if(count($parts) > 1){
		$parts = explode('</shipping_charge_structure>', $parts[1]);
		$opt = $parts[0];
		$usces->shipping_charge_structure = unserialize($opt);
		update_option('shipping_charge_structure', $usces->shipping_charge_structure);	
	}

	
	//category item post
	$slug = urlencode(__('items','usces'));
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
									WHERE (SUBSTRING(meta_key,1,5) = 'iopt_' OR SUBSTRING(meta_key,1,5) = 'isku_') AND post_id = %d", $id);
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
								WHERE (SUBSTRING(meta_key,1,5) = 'iopt_' OR SUBSTRING(meta_key,1,5) = 'isku_') AND post_id = %d", USCES_CART_NUMBER);
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
		$query = $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND SUBSTRING(meta_key, 1, 5) = 'isku_'", $post_id);
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
		$obj->set_action_status('error', __('ERROR： I was not able to complete collective operation','usces'));
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
		$obj->set_action_status('error', __('ERROR： I was not able to complete collective operation','usces'));
	} else {
		$obj->set_action_status('none', '');
	}
}

function usces_all_delete_itemdata(&$obj){
	global $wpdb;

	$ids = $_POST['listcheck'];
	$status = true;
	foreach ( (array)$ids as $post_id ):
		$query = $wpdb->prepare("DELETE FROM $wpdb->posts WHERE ID = %d", $post_id);
		$res = $wpdb->query( $query );
		if( $res === false ) {
			$status = false;
		}
	endforeach;
	if ( true === $status ) {
		$obj->set_action_status('success', __('I completed collective operation.','usces'));
	} elseif ( false === $status ) {
		$obj->set_action_status('error', __('ERROR： I was not able to complete collective operation','usces'));
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
		$obj->set_action_status('error', __('ERROR： I was not able to complete collective operation','usces'));
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
		$obj->set_action_status('error', __('ERROR： I was not able to complete collective operation','usces'));
	} else {
		$obj->set_action_status('none', '');
	}
}

function usces_all_delete_order_data(&$obj){
	global $wpdb;

	$tableName = $wpdb->prefix . "usces_order";
	$ids = $_POST['listcheck'];
	$status = true;
	foreach ( (array)$ids as $id ):
		$query = $wpdb->prepare("DELETE FROM $tableName WHERE ID = %d", $id);
		$res = $wpdb->query( $query );
		if( $res === false ) {
			$status = false;
		}
	endforeach;
	if ( true === $status ) {
		$obj->set_action_status('success', __('I completed collective operation.','usces'));
	} elseif ( false === $status ) {
		$obj->set_action_status('error', __('ERROR： I was not able to complete collective operation','usces'));
	} else {
		$obj->set_action_status('none', '');
	}
}

function usces_check_acting_return() {
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
	}
	
	return $results;
}
?>
