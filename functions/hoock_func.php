<?php
// Hoock functions
add_action('usces_construct', 'usces_action_acting_construct', 10);
add_action('usces_after_cart_instant', 'usces_action_acting_transaction', 10);

function usces_action_acting_construct(){

	if(isset($_POST['X-TRANID']) && !isset($_POST['OPT'])){//remise
		usces_log('remise in : '.$_POST['X-TRANID'], 'acting_transaction.log');
		
		$rand = $_POST['X-S_TORIHIKI_NO'];
		$datas = usces_get_order_acting_data($rand);
		$_GET['uscesid'] = $datas['sesid'];
		//usces_log('sesid : '.$datas['sesid'], 'acting_transaction.log');
		
	}elseif( in_array($_SERVER['REMOTE_ADDR'], array('210.164.6.67', '202.221.139.50')) ){//zeus
		
		if( !isset($_REQUEST['sendpoint']) )
			return;
			
		$rand = $_REQUEST['sendpoint'];
		usces_log('zeus : sendpoint:'.$_REQUEST['sendpoint'], 'acting_transaction.log');
//		usces_log('zeus : acting:'.$_REQUEST['acting'], 'acting_transaction.log');
//		usces_log('zeus : tracking_no:'.$_REQUEST['tracking_no'], 'acting_transaction.log');
//		usces_log('zeus : order_no:'.$_REQUEST['order_no'], 'acting_transaction.log');
		$datas = usces_get_order_acting_data($rand);
		$_GET['uscesid'] = $datas['sesid'];
//		usces_log('zeus : session_id1:'.$datas['sesid'], 'acting_transaction.log');

	}
}

function usces_action_acting_transaction(){
	global $usces, $wpdb;
	
	//*** remise_card ***//
	if(isset($_POST['X-TRANID']) && !isset($_POST['OPT'])){
		
		$rand = $_POST['X-S_TORIHIKI_NO'];
		if( empty($rand) ){
			usces_log('remise : return error', 'acting_transaction.log');
			die('error');
		}
		
		$order_id = usces_reg_orderdata();
		if( !$order_id ){
			usces_log('remise : Failure reg order data', 'acting_transaction.log');
			die('error');
		}else{
			if( isset($_POST['X-PAYQUICKID']) )
				$usces->set_member_meta_value('remise_pcid', $_POST['X-PAYQUICKID']);
			if( isset($_POST['X-AC_MEMBERID']) )
				$usces->set_member_meta_value('remise_memid', $_POST['X-AC_MEMBERID']);
			$mail_res = usces_send_ordermail( $order_id );
			die('<SDBKDATA>STATUS=800</SDBKDATA>');
		}
		
	//*** remise_conv ***//
	}elseif( isset($_POST['S_TORIHIKI_NO']) && isset($_POST['REC_FLG']) ){
		//usces_log('remise conv-info : ' . $_POST['S_TORIHIKI_NO'], 'acting_transaction.log');
		
		$table_name = $wpdb->prefix . "usces_order";
		$table_meta_name = $wpdb->prefix . "usces_order_meta";
		
		$mquery = $wpdb->prepare("SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'settlement_id', $_POST['S_TORIHIKI_NO']);
		$order_id = $wpdb->get_var( $mquery );
		if( $order_id == NULL ){
			usces_log('remise conv : order_id error', 'acting_transaction.log');
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
		if( $res === false )
			usces_log('remise conv : order_update error', 'acting_transaction.log');
		
		foreach( $_POST as $key => $value ){
			$data[$key] = mb_convert_encoding($value, 'UTF-8', 'SJIS');
		}
		$datastr = serialize( $data );
		$mquery = $wpdb->prepare(
					"UPDATE $table_meta_name SET meta_value = %s WHERE meta_key = %s AND order_id = %d", $datastr, 'settlement_id', $order_id);
		$res = $wpdb->query( $mquery );
		if( $res === false ){
			usces_log('remise conv : ordermeta_update error', 'acting_transaction.log');
		}
		
		//usces_send_receipted_mail( $order_id, 'remise_conv' );
		die('<SDBKDATA>STATUS=800</SDBKDATA>');
	
	//*** zeus_card ***//
	}elseif( isset($_REQUEST['acting']) && 'zeus_card' == $_REQUEST['acting'] && isset($_REQUEST['result']) && isset($_REQUEST['ordd']) ){

		$acting_opts = $usces->options['acting_settings']['zeus'];

		$rand = $_GET['sendpoint'];
		if( empty($rand) ){
			usces_log('zeus : return error', 'acting_transaction.log');
			die('error');
		}
		
		if( 'OK' == $_REQUEST['result'] ){
			$order_id = usces_reg_orderdata();
			if( !$order_id ){
				usces_log('zeus : Failure reg order data', 'acting_transaction.log');
				header("HTTP/1.0 400");
				die('error');
			}else{
				if( $usces->is_member_logged_in() )
					$usces->set_member_meta_value('zeus_pcid', '8888888888888888');
				$mail_res = usces_send_ordermail( $order_id );
				header("HTTP/1.0 200 OK");
				die('zeus');
			}
		}else{
			usces_log('zeus result : NG', 'acting_transaction.log');
			header("HTTP/1.0 200 OK");
			die('zeus');
		}
		
	//*** zeus_bank ***//
	}elseif( isset($_REQUEST['acting']) && 'zeus_bank' == $_REQUEST['acting'] && isset($_REQUEST['order_no']) && isset($_REQUEST['tracking_no']) ){

		$acting_opts = $usces->options['acting_settings']['zeus'];
		usces_log('zeus status : '.$_REQUEST['status'], 'acting_transaction.log');

		$table_name = $wpdb->prefix . "usces_order";
		$table_meta_name = $wpdb->prefix . "usces_order_meta";
		$mquery = $wpdb->prepare("SELECT order_id, meta_value FROM $table_meta_name WHERE meta_key = %s", 'acting_'.$_REQUEST['tracking_no']);
		$values = $wpdb->get_row( $mquery, ARRAY_A );
		if( $values == NULL ){
			
			$order_id = usces_reg_orderdata();
				usces_log('zeus :order_id'.print_r($order_id, true), 'acting_transaction.log');
			if( !$order_id ){
				usces_log('zeus : Failure reg order data', 'acting_transaction.log');
			}else{
				$mail_res = usces_send_ordermail( $order_id );
				$value = serialize($_GET);
				$query = $wpdb->prepare("INSERT INTO $table_meta_name (order_id, meta_key, meta_value) VALUES (%d, %s, %s)", $order_id, 'acting_'.$_REQUEST['tracking_no'], $value);
				$res = $wpdb->query( $query );
				$usces->cart->crear_cart();
			}


		}else{
		
			$value = unserialize($values['meta_value']);
			$status = ( '03' == $_REQUEST['status'] ) ? 'receipted,' : 'noreceipt,';
			$order_id = $values['order_id'];
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
			}
			$res = $wpdb->query( $mquery );
			if(!$res)
				usces_log('zeus db_order : error', 'acting_transaction.log');
			
			$value = serialize($_GET);
			$mquery = $wpdb->prepare("UPDATE $table_meta_name SET meta_value = %s WHERE order_id = %d AND meta_key = %s", $value, $order_id, 'acting_'.$_REQUEST['tracking_no']);
			$res = $wpdb->query( $mquery );
			if(!$res)
				usces_log('zeus db_order_meta : error', 'acting_transaction.log');
		}

		die('zeus');
		
	//*** zeus_conv ***//
	}elseif( isset($_REQUEST['acting']) && 'zeus_conv' == $_REQUEST['acting'] && isset($_REQUEST['status']) && isset($_REQUEST['sendpoint']) ){

		$acting_opts = $usces->options['acting_settings']['zeus'];
		usces_log('zeus status : '.$_REQUEST['status'], 'acting_transaction.log');

		$table_name = $wpdb->prefix . "usces_order";
		$table_meta_name = $wpdb->prefix . "usces_order_meta";
		$mquery = $wpdb->prepare("SELECT order_id, meta_value FROM $table_meta_name WHERE meta_key = %s", 'acting_'.$_REQUEST['sendpoint']);
		$values = $wpdb->get_row( $mquery, ARRAY_A );
		if( $values == NULL ){
			
			$order_id = usces_reg_orderdata();
				usces_log('zeus :order_id'.print_r($order_id, true), 'acting_transaction.log');
			if( !$order_id ){
				usces_log('zeus : Failure reg order data', 'acting_transaction.log');
			}else{
				$mail_res = usces_send_ordermail( $order_id );
				$value = serialize($_GET);
				$query = $wpdb->prepare("INSERT INTO $table_meta_name (order_id, meta_key, meta_value) VALUES (%d, %s, %s)", $order_id, 'acting_'.$_REQUEST['sendpoint'], $value);
				$res = $wpdb->query( $query );
				//$usces->cart->crear_cart(); clear in front
			}


		}else{
		
			$value = unserialize($values['meta_value']);
			$status = ( '04' == $_REQUEST['status'] ) ? 'receipted,' : 'noreceipt,';
			$order_id = $values['order_id'];
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
			}
			$res = $wpdb->query( $mquery );
			if(!$res)
				usces_log('zeus db_order : error'.$order_id, 'acting_transaction.log');
			
			foreach( $_GET as $key => $v ){
				$newvalue[$key] = mb_convert_encoding($v, 'UTF-8', 'SJIS');
			}
			$value = serialize($newvalue);
			$mquery = $wpdb->prepare("UPDATE $table_meta_name SET meta_value = %s WHERE order_id = %d AND meta_key = %s", $value, $order_id, 'acting_'.$_REQUEST['sendpoint']);
			$res = $wpdb->query( $mquery );
			if(!$res)
				usces_log('zeus db_order_meta : error', 'acting_transaction.log');
		}

		die('zeus');
		
//20101018ysk start
	//*** jpayment_card ***//
	} elseif(isset($_REQUEST['acting']) && 'jpayment_card' == $_REQUEST['acting']) {
		$args='';
		foreach((array)$_GET as $key => $value) {
			$args.='&('.$key.')=('.$value.')';
		}
		usces_log('jpayment card : '.$args, 'acting_transaction.log');

	//*** jpayment_conv ***//
	} elseif(isset($_REQUEST['acting']) && 'jpayment_conv' == $_REQUEST['acting'] && isset($_GET['ap'])) {
		$args='';
		foreach((array)$_GET as $key => $value) {
			$args.='&('.$key.')=('.$value.')';
		}
		usces_log('jpayment conv : '.$args, 'acting_transaction.log');

		switch($_GET['ap']) {
		case 'CPL_PRE'://コンビニペーパーレス決済識別コード
			break;

		case 'CPL'://入金確定
			$table_name = $wpdb->prefix."usces_order";
			$table_meta_name = $wpdb->prefix."usces_order_meta";

			$query = $wpdb->prepare("SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'settlement_id', $_GET['cod']);
			$order_id = $wpdb->get_var($query);
			if($order_id == NULL) {
				usces_log('jpayment conv : order_id error', 'acting_transaction.log');
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
				usces_log('jpayment conv : order_update error', 'acting_transaction.log');
			}

			foreach($_GET as $key => $value) {
				$data[$key] = mysql_real_escape_string($value);
			}
			$res = $usces->set_order_meta_value('acting_'.$_REQUEST['acting'], serialize($data), $order_id);
			if($res === false) {
				usces_log('jpayment conv : ordermeta_update error', 'acting_transaction.log');
			}
			die('J-Payment');
			break;

		case 'CVS_CAN'://入金取消
			$table_name = $wpdb->prefix."usces_order";
			$table_meta_name = $wpdb->prefix."usces_order_meta";

			$query = $wpdb->prepare("SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'settlement_id', $_GET['cod']);
			$order_id = $wpdb->get_var($query);
			if($order_id == NULL) {
				usces_log('jpayment conv : order_id error', 'acting_transaction.log');
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
				usces_log('jpayment conv : order_update error', 'acting_transaction.log');
			}

			foreach($_GET as $key => $value) {
				$data[$key] = mysql_real_escape_string($value);
			}
			$res = $usces->set_order_meta_value('acting_'.$_REQUEST['acting'], serialize($data), $order_id);
			if($res === false) {
				usces_log('jpayment conv : ordermeta_update error', 'acting_transaction.log');
			}
			die('J-Payment');
			break;
		}

	//*** jpayment_webm ***//
	} elseif(isset($_REQUEST['acting']) && 'jpayment_webm' == $_REQUEST['acting']) {
		$args='';
		foreach((array)$_GET as $key => $value) {
			$args.='&('.$key.')=('.$value.')';
		}
		usces_log('jpayment webmoney : '.$args, 'acting_transaction.log');

	//*** jpayment_bitc ***//
	} elseif(isset($_REQUEST['acting']) && 'jpayment_bitc' == $_REQUEST['acting']) {
		$args='';
		foreach((array)$_GET as $key => $value) {
			$args.='&('.$key.')=('.$value.')';
		}
		usces_log('jpayment bitcash : '.$args, 'acting_transaction.log');

	//*** jpayment_bank ***//
	} elseif(isset($_REQUEST['acting']) && 'jpayment_bank' == $_REQUEST['acting']) {
		$args='';
		foreach((array)$_GET as $key => $value) {
			$args.='&('.$key.')=('.$value.')';
		}
		usces_log('jpayment bank : '.$args, 'acting_transaction.log');
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
					usces_log('jpayment bank : order_id error', 'acting_transaction.log');
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
					usces_log('jpayment bank : order_update error', 'acting_transaction.log');
				}

				foreach($_GET as $key => $value) {
					$data[$key] = mysql_real_escape_string($value);
				}
				$res = $usces->set_order_meta_value('acting_'.$_REQUEST['acting'], serialize($data), $order_id);
				if($res === false) {
					usces_log('jpayment bank : ordermeta_update error', 'acting_transaction.log');
				}
			}
			die('J-Payment');
			break;
		}
//20101018ysk end
	//*** epsilon ***//
	}elseif( isset($_GET['trans_code']) && isset($_GET['user_id']) && isset($_GET['result']) && isset($_GET['order_number']) ){
		$query = 'trans_code=' . $_GET['trans_code'] . '&user_id=' . $_GET['user_id'] . '&result=' . $_GET['result'] . '&order_number=' . $_GET['order_number'];
		usces_log('epsilon : ' . $query, 'acting_transaction.log');
		header('location: ' . get_option('home') . '?page_id=' . get_option('usces_cart_number') . '&acting=epsilon&acting_return=1&result=' . $_GET['result']);
		exit;
	}
}
?>
