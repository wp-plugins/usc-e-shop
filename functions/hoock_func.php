<?php
// Hoock functions
add_action('usces_construct', 'usces_action_acting_construct', 10);
add_action('usces_after_cart_instant', 'usces_action_acting_transaction', 10);

function usces_action_acting_construct(){

	if(isset($_POST['X-TRANID']) && !isset($_POST['OPT'])){//remise
		
		$rand = $_POST['X-S_TORIHIKI_NO'];
		$datas = usces_get_order_acting_data($rand);
		$_GET['uscesid'] = $datas['sesid'];
		if( empty($datas['sesid']) ){
			usces_log('remise construct : error1', 'acting_transaction.log');
		}else{
			usces_log('remise construct : '.$_POST['X-TRANID'], 'acting_transaction.log');
			usces_set_acting_notification_time( $rand );
		}
			
	}elseif( in_array($_SERVER['REMOTE_ADDR'], array('210.164.6.67', '202.221.139.50')) ){//zeus
		
		$rand = $_REQUEST['sendpoint'];
		$datas = usces_get_order_acting_data($rand);
		$_GET['uscesid'] = $datas['sesid'];
		if( empty($datas['sesid']) ){
			usces_log('zeus construct : error1', 'acting_transaction.log');
		}else{
			usces_log('zeus construct : '.$_REQUEST['sendpoint'], 'acting_transaction.log');
		}
	}
}

function usces_action_acting_transaction(){
	global $usces, $wpdb;
	
	//*** remise_card ***//
	if(isset($_POST['X-TRANID']) && !isset($_POST['OPT'])){
		foreach( $_POST as $key => $value ){
			$data[$key] = mb_convert_encoding($value, 'UTF-8', 'SJIS');
		}
		
//		$rand = $_POST['X-S_TORIHIKI_NO'];
//		if( empty($rand) ){
//			usces_log('remise card error1 : '.print_r($data, true), 'acting_transaction.log');
//			die('error1');
//		}else{
//			$res = usces_check_notification_time( $rand, 15 );
//			if( !$res )
//				die('error : time over');
//		}
//		
//		if( 0 !== (int)$_POST['X-ERRLEVEL'] ){
//			usces_log('remise card error2 : '.print_r($data, true), 'acting_transaction.log');
//			die('error2');
//		}
		
		if( '0000000' === substr($rand, 0, 7) ){//card up
			usces_log('remise card_update : '.print_r($data, true), 'acting_transaction.log');
			if( isset($_POST['X-EXPIRE']) ){
				$expire = substr($_POST['X-EXPIRE'], 0, 2) . '/' . substr($_POST['X-EXPIRE'], 2, 2);
				$usces->set_member_meta_value('limitofcard', $expire, $_POST['X-AC_S_KAIIN_NO']);
			}
			if( isset($_POST['X-PARTOFCARD']) )
				$usces->set_member_meta_value('partofcard', $_POST['X-PARTOFCARD'], $_POST['X-AC_S_KAIIN_NO']);
			
			die('<SDBKDATA>STATUS=800</SDBKDATA>');
		}
		
//20110203ysk start
//		$res = $usces->order_processing();
//		if( 'error' == $res ){
//			usces_log('remise card error3 : '.print_r($data, true), 'acting_transaction.log');
//			die('error3');
//		}else{
			if( isset($_POST['X-PAYQUICKID']) )
				$usces->set_member_meta_value('remise_pcid', $_POST['X-PAYQUICKID']);
			if( isset($_POST['X-AC_MEMBERID']) )
				$usces->set_member_meta_value('remise_memid', $_POST['X-AC_MEMBERID']);
			usces_log('remise card transaction : '.$_POST['X-TRANID'], 'acting_transaction.log');
			die('<SDBKDATA>STATUS=800</SDBKDATA>');
//		}
//20110203ysk end
		
	//*** remise_conv ***//
	}elseif( isset($_POST['S_TORIHIKI_NO']) && isset($_POST['REC_FLG']) ){
		foreach( $_POST as $key => $value ){
			$data[$key] = mb_convert_encoding($value, 'UTF-8', 'SJIS');
		}
		
		$table_name = $wpdb->prefix . "usces_order";
		$table_meta_name = $wpdb->prefix . "usces_order_meta";
		
		$mquery = $wpdb->prepare("SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'settlement_id', $_POST['S_TORIHIKI_NO']);
		$order_id = $wpdb->get_var( $mquery );
		if( $order_id == NULL ){
			usces_log('remise conv error1 : '.print_r($data, true), 'acting_transaction.log');
			die('error1');
		}
		
		$mquery = $wpdb->prepare("
				UPDATE $table_name SET order_status = 
				CASE 
					WHEN LOCATE('noreceipt', order_status) > 0 THEN REPLACE(order_status, 'noreceipt', 'receipted') 
					WHEN LOCATE('receipted', order_status) > 0 THEN order_status 
					ELSE CONCAT('receipted,', order_status ) 
				END 
				WHERE ID = %d", $order_id);
		$res = $wpdb->query( $mquery );
		if( $res === false ){
			usces_log('remise conv error2 : '.print_r($data, true), 'acting_transaction.log');
			die('error2');
		}
		
		$datastr = serialize( $data );
		$mquery = $wpdb->prepare(
					"UPDATE $table_meta_name SET meta_value = %s WHERE meta_key = %s AND order_id = %d", $datastr, 'settlement_id', $order_id);
		$res = $wpdb->query( $mquery );
		if( $res === false ){
			usces_log('remise conv error3 : '.print_r($data, true), 'acting_transaction.log');
			die('error3');
		}
		
		usces_action_acting_getpoint( $order_id );//20120306ysk 0000324

		//usces_send_receipted_mail( $order_id, 'remise_conv' );
		usces_log('remise conv transaction : '.$_POST['S_TORIHIKI_NO'], 'acting_transaction.log');
		die('<SDBKDATA>STATUS=800</SDBKDATA>');
	
	//*** zeus_card ***//
	}elseif( isset($_REQUEST['acting']) && 'zeus_card' == $_REQUEST['acting'] && isset($_REQUEST['result']) && isset($_REQUEST['ordd']) ){
		foreach( $_REQUEST as $key => $value ){
			$data[$key] = $value;
		}
usces_log('zeus card : '.print_r($data, true), 'acting_transaction.log');

		$acting_opts = $usces->options['acting_settings']['zeus'];

		$rand = $_GET['sendpoint'];
		if( empty($rand) ){
			usces_log('zeus card error1 : '.print_r($data, true), 'acting_transaction.log');
			die('error1');
		}
		
		if( 'OK' == $_REQUEST['result'] ){
			header("HTTP/1.0 200 OK");
			die('zeus');
		}else{
			usces_log('zeus card error3 : '.print_r($data, true), 'acting_transaction.log');
			header("HTTP/1.0 200 OK");
			die('error3');
		}
		
	//*** zeus_bank ***//
	}elseif( isset($_REQUEST['acting']) && 'zeus_bank' == $_REQUEST['acting'] && isset($_REQUEST['order_no']) && isset($_REQUEST['tracking_no']) ){
		foreach( $_REQUEST as $key => $value ){
			$data[$key] = $value;
		}

		$acting_opts = $usces->options['acting_settings']['zeus'];

		$table_name = $wpdb->prefix . "usces_order";
		$table_meta_name = $wpdb->prefix . "usces_order_meta";
		$mquery = $wpdb->prepare("SELECT order_id, meta_value FROM $table_meta_name WHERE meta_key = %s", 'acting_'.$_REQUEST['tracking_no']);
		$values = $wpdb->get_row( $mquery, ARRAY_A );
		if( $values == NULL ){
			
//20110203ysk start
		$res = $usces->order_processing();
		if( 'error' == $res ){
			usces_log('zeus bank error1 : '.print_r($data, true), 'acting_transaction.log');
			die('error1');
		}else{
			$order_id = $usces->cart->get_order_entry('ID');
			$value = serialize($_GET);
			$query = $wpdb->prepare("INSERT INTO $table_meta_name (order_id, meta_key, meta_value) VALUES (%d, %s, %s)", $order_id, 'acting_'.$_REQUEST['tracking_no'], $value);
			$res = $wpdb->query( $query );
			$usces->cart->crear_cart();
		}
//20110203ysk end

		}else{
		
			$value = unserialize($values['meta_value']);
			$status = ( '03' == $_REQUEST['status'] ) ? 'receipted,' : 'noreceipt,';
			$order_id = $values['order_id'];
			$add_point = true;//20120306ysk 0000324
			if( 'receipted,' == $status ){
				$mquery = $wpdb->prepare("
				UPDATE $table_name SET order_status = 
				CASE 
					WHEN LOCATE('noreceipt', order_status) > 0 THEN REPLACE(order_status, 'noreceipt', 'receipted') 
					WHEN LOCATE('receipted', order_status) > 0 THEN order_status 
					ELSE CONCAT('receipted,', order_status ) 
				END 
				WHERE ID = %d", $order_id);
			}else{
				$mquery = $wpdb->prepare("
				UPDATE $table_name SET order_status = 
				CASE 
					WHEN LOCATE('receipted', order_status) > 0 THEN REPLACE(order_status, 'receipted', 'noreceipt') 
					WHEN LOCATE('noreceipt', order_status) > 0 THEN order_status 
					ELSE CONCAT('noreceipt,', order_status ) 
				END 
				WHERE ID = %d", $order_id);
				$add_point = false;//20120306ysk 0000324
			}
			$res = $wpdb->query( $mquery );
			if(!$res){
				usces_log('zeus bank error2 : '.print_r($data, true), 'acting_transaction.log');
				die('error2');
			}
			
			$value = serialize($_GET);
			$mquery = $wpdb->prepare("UPDATE $table_meta_name SET meta_value = %s WHERE order_id = %d AND meta_key = %s", $value, $order_id, 'acting_'.$_REQUEST['tracking_no']);
			$res = $wpdb->query( $mquery );
			if(!$res){
				usces_log('zeus bank error3 : '.print_r($data, true), 'acting_transaction.log');
				die('error3');
			}
			usces_action_acting_getpoint( $order_id, $add_point );//20120306ysk 0000324
		}

		usces_log('zeus bank transaction : '.$_REQUEST['tracking_no'], 'acting_transaction.log');
		die('zeus');
		
	//*** zeus_conv ***//
	}elseif( isset($_REQUEST['acting']) && 'zeus_conv' == $_REQUEST['acting'] && isset($_REQUEST['status']) && isset($_REQUEST['sendpoint']) ){
		foreach( $_REQUEST as $key => $value ){
			$data[$key] = $value;
		}

		$acting_opts = $usces->options['acting_settings']['zeus'];

		$table_name = $wpdb->prefix . "usces_order";
		$table_meta_name = $wpdb->prefix . "usces_order_meta";
		$mquery = $wpdb->prepare("SELECT order_id, meta_value FROM $table_meta_name WHERE meta_key = %s", 'acting_'.$_REQUEST['sendpoint']);
		$values = $wpdb->get_row( $mquery, ARRAY_A );
		if( $values == NULL ){
			
//20110203ysk start
		$res = $usces->order_processing();
		if( 'error' == $res ){
			usces_log('zeus conv error1 : '.print_r($data, true), 'acting_transaction.log');
			die('error1');
		}else{
			$order_id = $usces->cart->get_order_entry('ID');
			$value = serialize($_GET);
			$query = $wpdb->prepare("INSERT INTO $table_meta_name (order_id, meta_key, meta_value) VALUES (%d, %s, %s)", $order_id, 'acting_'.$_REQUEST['sendpoint'], $value);
			$res = $wpdb->query( $query );
		}
//20110203ysk end


		}else{
		
			$value = unserialize($values['meta_value']);
			$status = ( '04' == $_REQUEST['status'] ) ? 'receipted,' : 'noreceipt,';
			$order_id = $values['order_id'];
			$add_point = true;//20120306ysk 0000324
			if( 'receipted,' == $status ){
				$mquery = $wpdb->prepare("
				UPDATE $table_name SET order_status = 
				CASE 
					WHEN LOCATE('noreceipt', order_status) > 0 THEN REPLACE(order_status, 'noreceipt', 'receipted') 
					WHEN LOCATE('receipted', order_status) > 0 THEN order_status 
					ELSE CONCAT('receipted,', order_status ) 
				END 
				WHERE ID = %d", $order_id);
			}else{
				$mquery = $wpdb->prepare("
				UPDATE $table_name SET order_status = 
				CASE 
					WHEN LOCATE('receipted', order_status) > 0 THEN REPLACE(order_status, 'receipted', 'noreceipt') 
					WHEN LOCATE('noreceipt', order_status) > 0 THEN order_status 
					ELSE CONCAT('noreceipt,', order_status ) 
				END 
				WHERE ID = %d", $order_id);
				$add_point = false;//20120306ysk 0000324
			}
			$res = $wpdb->query( $mquery );
			if(!$res){
				usces_log('zeus conv error2 : '.print_r($data, true), 'acting_transaction.log');
				die('error2');
			}
			foreach( $_GET as $key => $v ){
				$newvalue[$key] = mb_convert_encoding($v, 'UTF-8', 'SJIS');
			}
			$value = serialize($newvalue);
			$mquery = $wpdb->prepare("UPDATE $table_meta_name SET meta_value = %s WHERE order_id = %d AND meta_key = %s", $value, $order_id, 'acting_'.$_REQUEST['sendpoint']);
			$res = $wpdb->query( $mquery );
			if(!$res){
				usces_log('zeus conv error3 : '.print_r($data, true), 'acting_transaction.log');
				die('error3');
			}
			usces_action_acting_getpoint( $order_id, $add_point );//20120306ysk 0000324
		}

		usces_log('zeus conv transaction : '.$_REQUEST['sendpoint'], 'acting_transaction.log');
		die('zeus');
		
//20101018ysk start
	//*** jpayment_card ***//
	} elseif(isset($_REQUEST['acting']) && 'jpayment_card' == $_REQUEST['acting']) {
		//$args='';
		//foreach((array)$_GET as $key => $value) {
		//	$args.='&('.$key.')=('.$value.')';
		//}
		//usces_log('jpayment card : '.$args, 'acting_transaction.log');

	//*** jpayment_conv ***//
	} elseif(isset($_REQUEST['acting']) && 'jpayment_conv' == $_REQUEST['acting'] && isset($_GET['ap'])) {
		//$args='';
		//foreach((array)$_GET as $key => $value) {
		//	$args.='&('.$key.')=('.$value.')';
		//}
		//usces_log('jpayment conv : '.$args, 'acting_transaction.log');
		foreach( $_REQUEST as $key => $value ){
			$data[$key] = $value;
		}

		switch($_GET['ap']) {
		case 'CPL_PRE'://コンビニペーパーレス決済識別コード
			break;

		case 'CPL'://入金確定
			$table_name = $wpdb->prefix."usces_order";
			$table_meta_name = $wpdb->prefix."usces_order_meta";

			$query = $wpdb->prepare("SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'settlement_id', $_GET['cod']);
			$order_id = $wpdb->get_var($query);
			if($order_id == NULL) {
				usces_log('jpayment conv error1 : '.print_r($data, true), 'acting_transaction.log');
				die('error1');
			}

			$query = $wpdb->prepare("
				UPDATE $table_name SET order_status = 
				CASE 
					WHEN LOCATE('noreceipt', order_status) > 0 THEN REPLACE(order_status, 'noreceipt', 'receipted') 
					WHEN LOCATE('receipted', order_status) > 0 THEN order_status 
					ELSE CONCAT('receipted,', order_status ) 
				END 
				WHERE ID = %d", $order_id);
			$res = $wpdb->query($query);
			if($res === false) {
				usces_log('jpayment conv error2 : '.print_r($data, true), 'acting_transaction.log');
				die('error2');
			}

			foreach($_GET as $key => $value) {
				$data[$key] = mysql_real_escape_string($value);
			}
			$res = $usces->set_order_meta_value('acting_'.$_REQUEST['acting'], serialize($data), $order_id);
			if($res === false) {
				usces_log('jpayment conv error3 : '.print_r($data, true), 'acting_transaction.log');
				die('error3');
			}

			usces_action_acting_getpoint( $order_id );//20120306ysk 0000324

			usces_log('J-Payment conv transaction : '.$_GET['gid'], 'acting_transaction.log');
			die('J-Payment');
			break;

		case 'CVS_CAN'://入金取消
			$table_name = $wpdb->prefix."usces_order";
			$table_meta_name = $wpdb->prefix."usces_order_meta";

			$query = $wpdb->prepare("SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'settlement_id', $_GET['cod']);
			$order_id = $wpdb->get_var($query);
			if($order_id == NULL) {
				usces_log('jpayment conv error1 : '.print_r($data, true), 'acting_transaction.log');
				die('error1');
			}

			$query = $wpdb->prepare("
				UPDATE $table_name SET order_status = 
				CASE 
					WHEN LOCATE('receipted', order_status) > 0 THEN REPLACE(order_status, 'receipted', 'noreceipt') 
					WHEN LOCATE('noreceipt', order_status) > 0 THEN order_status  
					ELSE CONCAT('noreceipt,', order_status ) 
				END 
				WHERE ID = %d", $order_id);
			$res = $wpdb->query($query);
			if($res === false) {
				usces_log('jpayment conv error2 : '.print_r($data, true), 'acting_transaction.log');
				die('error2');
			}

			foreach($_GET as $key => $value) {
				$data[$key] = mysql_real_escape_string($value);
			}
			$res = $usces->set_order_meta_value('acting_'.$_REQUEST['acting'], serialize($data), $order_id);
			if($res === false) {
				usces_log('jpayment conv error3 : '.print_r($data, true), 'acting_transaction.log');
				die('error3');
			}

			usces_action_acting_getpoint( $order_id, false );//20120306ysk 0000324

			usces_log('J-Payment conv transaction : '.$_GET['gid'], 'acting_transaction.log');
			die('J-Payment');
			break;
		}

	//*** jpayment_bank ***//
	} elseif(isset($_REQUEST['acting']) && 'jpayment_bank' == $_REQUEST['acting']) {
		//$args='';
		//foreach((array)$_GET as $key => $value) {
		//	$args.='&('.$key.')=('.$value.')';
		//}
		//usces_log('jpayment bank : '.$args, 'acting_transaction.log');
		foreach( $_REQUEST as $key => $value ){
			$data[$key] = $value;
		}

		switch($_GET['ap']) {
		case 'BANK'://受付完了
			break;

		case 'BAN_SAL'://入金完了
			if($_GET['mf'] == '1') {//入金マッチングの場合
				$table_name = $wpdb->prefix."usces_order";
				$table_meta_name = $wpdb->prefix."usces_order_meta";

				$query = $wpdb->prepare("SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'settlement_id', $_GET['cod']);
				$order_id = $wpdb->get_var($query);
				if($order_id == NULL) {
					usces_log('jpayment bank error1 : '.print_r($data, true), 'acting_transaction.log');
					die('error1');
				}

				$query = $wpdb->prepare("
					UPDATE $table_name SET order_status = 
					CASE 
						WHEN LOCATE('noreceipt', order_status) > 0 THEN REPLACE(order_status, 'noreceipt', 'receipted') 
						WHEN LOCATE('receipted', order_status) > 0 THEN order_status 
						ELSE CONCAT('receipted,', order_status ) 
					END 
					WHERE ID = %d", $order_id);
				$res = $wpdb->query($query);
				if($res === false) {
					usces_log('jpayment bank error2 : '.print_r($data, true), 'acting_transaction.log');
					die('error2');
				}

				foreach($_GET as $key => $value) {
					$data[$key] = mysql_real_escape_string($value);
				}
				$res = $usces->set_order_meta_value('acting_'.$_REQUEST['acting'], serialize($data), $order_id);
				if($res === false) {
					usces_log('jpayment bank error3 : '.print_r($data, true), 'acting_transaction.log');
					die('error3');
				}

				usces_action_acting_getpoint( $order_id );//20120306ysk 0000324
			}

			usces_log('J-Payment bank transaction : '.$_REQUEST['gid'], 'acting_transaction.log');
			die('J-Payment');
			break;
		}
//20101018ysk end
	//*** epsilon ***//
	}elseif( !isset($_GET['acting_return']) && isset($_GET['trans_code']) && isset($_GET['user_id']) && isset($_GET['result']) && isset($_GET['order_number']) ){
		$query = 'trans_code=' . $_GET['trans_code'] . '&user_id=' . $_GET['user_id'] . '&result=' . $_GET['result'] . '&order_number=' . $_GET['order_number'];
		usces_log('epsilon (acting_transaction) : ' . $query, 'acting_transaction.log');
		$permalink_structure = get_option('permalink_structure');
		$delim = ( !$usces->use_ssl && $permalink_structure) ? '?' : '&';

		header('location: ' . USCES_CART_URL . $delim . 'acting=epsilon&acting_return=1&' . $query );
		exit;
//20110208ysk start
	} elseif( !isset($_GET['acting_return']) && (isset($_GET['acting']) && 'paypal_ipn' == $_GET['acting']) ) {
		foreach( $_REQUEST as $key => $value ){
			$data[$key] = $value;
		}
		usces_log('paypal_ipn in '.print_r($data,true), 'acting_transaction.log');
		require_once($usces->options['settlement_path'] . 'paypal.php');
		$ipn_res = paypal_ipn_check($usces_paypal_url);
		if( $ipn_res[0] === true ){
			$res = $usces->order_processing( $ipn_res );
			if( 'ordercompletion' == $res ){
				$usces->cart->crear_cart();
			}else{
				usces_log('paypal_ipn error : '.print_r($data, true), 'acting_transaction.log');
				die('error1');
			}
			do_action('usces_action_paypal_ipn', $res, $ipn_res);
		}
		usces_log('PayPal IPN transaction : '.$_REQUEST['txn_id'], 'acting_transaction.log');
		die('PayPal');
//20110523ysk start
	//*** paypal ipn ***//
	} elseif( isset($_GET['ipn_track_id']) ) {
		foreach( $_REQUEST as $key => $value ){
			$data[$key] = $value;
		}
		usces_log('paypal ipn : '.print_r($data, true), 'acting_transaction.log');
		die('PayPal');
//20110523ysk end
	}
}
?>
