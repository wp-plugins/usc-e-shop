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
			//usces_auth_order_acting_data($rand);
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

	} elseif( isset($_POST['res_result']) && isset($_POST['res_pay_method']) && isset($_POST['order_id']) ) {//SoftBankPayment

		$datas = usces_get_order_acting_data( $_POST['order_id'] );
		$_GET['uscesid'] = $datas['sesid'];
		if( empty($datas['sesid']) ) {
			usces_log('SoftBankPayment construct : error1', 'acting_transaction.log');
		} else {
			usces_log('SoftBankPayment construct : '.$_POST['order_id'], 'acting_transaction.log');
		}

	} elseif( isset($_POST['SID']) && isset($_POST['FUKA']) && isset($_POST['CVS']) ) {//digitalcheck_conv

		$datas = usces_get_order_acting_data( $_POST['SID'] );
		$_GET['uscesid'] = $datas['sesid'];
		if( empty($datas['sesid']) ) {
			usces_log('digitalcheck construct : error1', 'acting_transaction.log');
		} else {
			usces_log('digitalcheck construct : '.$_POST['SID'], 'acting_transaction.log');
		}

	} elseif( isset($_REQUEST['SiteId']) && isset($_REQUEST['rand']) ) {//AnotherLane

		$datas = usces_get_order_acting_data( $_REQUEST['rand'] );
		$_GET['uscesid'] = $datas['sesid'];
		if( empty($datas['sesid']) ) {
			usces_log('anotherlane construct : error1', 'acting_transaction.log');
		} else {
			usces_log('anotherlane construct : '.$_REQUEST['rand'], 'acting_transaction.log');
		}

	} elseif( isset($_POST['orderId']) and isset($_POST['merchantEncryptionKey']) ) {//Veritrans

		$datas = usces_get_order_acting_data( $_POST['orderId'] );
		$_GET['uscesid'] = $datas['sesid'];
		if( empty($datas['sesid']) ) {
			usces_log('Veritrans construct : error1', 'acting_transaction.log');
		} else {
			usces_log('Veritrans construct : '.$_POST['orderId'], 'acting_transaction.log');
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
		usces_log('remise acting_transaction : '.print_r($data, true), 'acting_transaction.log');
		
		$rand = $_POST['X-S_TORIHIKI_NO'];
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
			
//20120510ysk start
			//die('<SDBKDATA>STATUS=800</SDBKDATA>');
			$status = (isset($_POST['CARIER_TYPE'])) ? '900' : '800';
			die('<SDBKDATA>STATUS='.$status.'</SDBKDATA>');
//20120510ysk end
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
//20120511ysk start
			if( isset($_POST['X-EXPIRE']) ) {
				$limitofcard = substr($_POST['X-EXPIRE'], 0, 2) . '/' . substr($_POST['X-EXPIRE'], 2, 2);
				$usces->set_member_meta_value('limitofcard', $limitofcard);
			}
			if( isset($_POST['X-PARTOFCARD']) ) 
				$usces->set_member_meta_value('partofcard', $_POST['X-PARTOFCARD']);
//20120511ysk end
			usces_log('remise card transaction : '.$_POST['X-TRANID'], 'acting_transaction.log');
//20120510ysk start
			//die('<SDBKDATA>STATUS=800</SDBKDATA>');
			$status = (isset($_POST['CARIER_TYPE'])) ? '900' : '800';
			die('<SDBKDATA>STATUS='.$status.'</SDBKDATA>');
//20120510ysk end
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
		usces_log('zeus card cgi : '.print_r($data, true), 'acting_transaction.log');

		$rand = $_GET['sendpoint'];
		if( empty($rand) ){
			usces_log('zeus card error1 : '.print_r($data, true), 'acting_transaction.log');
			die('error1');
		}

		if( 'OK' == $_REQUEST['result'] ){
			$acting_opts = $usces->options['acting_settings']['zeus'];
			if( $usces->is_member_logged_in() ) {
				if( isset($_GET['cardnumber']) ) {
					$usces->set_member_meta_value( 'zeus_partofcard', $_GET['cardnumber'] );
					if( 'on' == $acting_opts['quickcharge'] ) {
						$usces->set_member_meta_value( 'zeus_pcid', '8888888888888888' );
					}
				}
			}
			header("HTTP/1.0 200 OK");
			die('zeus');

		}else{
			header("HTTP/1.0 200 OK");
			die('error3');
		}

	//*** zeus_bank ***//
	}elseif( isset($_REQUEST['acting']) && 'zeus_bank' == $_REQUEST['acting'] && isset($_REQUEST['order_no']) && isset($_REQUEST['tracking_no']) ){
		foreach( $_REQUEST as $key => $value ){
			$data[$key] = $value;
		}
		usces_log('zeus bank cgi data : '.print_r($data,true), 'acting_transaction.log');

//20130426ysk start 0000701
		if( '04' === $_REQUEST['status'] or '05' === $_REQUEST['status'] ) {
			usces_log( 'zeus bank error0 : status='.$_REQUEST['status'], 'acting_transaction.log' );
			die('error0');
		}
//20130426ysk end

		$acting_opts = $usces->options['acting_settings']['zeus'];

		$table_name = $wpdb->prefix . "usces_order";
		$table_meta_name = $wpdb->prefix . "usces_order_meta";
		$mquery = $wpdb->prepare("SELECT order_id, meta_value FROM $table_meta_name WHERE meta_key = %s", 'acting_'.$_REQUEST['tracking_no']);
		$values = $wpdb->get_row( $mquery, ARRAY_A );

		if( $values == NULL ){
			
//20110203ysk start
			$res = $usces->order_processing();
			if( 'error' == $res ){
				usces_log('zeus bank error1 : order_processing', 'acting_transaction.log');
				die('error1');
			}else{
				usces_log('zeus bank order : OK', 'acting_transaction.log');
				$order_id = $usces->cart->get_order_entry('ID');
				$value = serialize($_GET);
				$query = $wpdb->prepare("INSERT INTO $table_meta_name (order_id, meta_key, meta_value) VALUES (%d, %s, %s)", $order_id, 'acting_'.$_REQUEST['tracking_no'], $value);
				$res = $wpdb->query( $query );

				$mquery = $wpdb->prepare("SELECT order_id, meta_value FROM $table_meta_name WHERE meta_key = %s", 'acting_'.$_REQUEST['tracking_no']);
				$values = $wpdb->get_row( $mquery, ARRAY_A );
				$usces->cart->crear_cart();
			}
//20110203ysk end
		}
		
		//usces_log('zeus construct_values2 : '.print_r($values,true), 'acting_transaction.log');
		$value = unserialize($values['meta_value']);
		$status = ( '03' === $_REQUEST['status'] ) ? 'receipted,' : 'noreceipt,';
		$order_id = $values['order_id'];
		//$add_point = true;//20120306ysk 0000324
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
			//$add_point = false;//20120306ysk 0000324
		}
		$res = $wpdb->query( $mquery );
		if( false === $res ){
			//usces_log('zeus bank error2 : '.print_r($data, true), 'acting_transaction.log');
			usces_log('zeus bank error2 : update usces_order', 'acting_transaction.log');
			die('error2');
		}
		//usces_action_acting_getpoint( $order_id, $add_point );//20120306ysk 0000324
		if( '03' === $_REQUEST['status'] ) usces_action_acting_getpoint( $order_id );//20140130ysk

		$upvalue = array( 'acting' => $_GET['acting'], 'order_no' => $_GET['order_no'], 'tracking_no' => $_GET['tracking_no'], 'status' => $_GET['status'], 'error_message' => $_GET['error_message'], 'money' => $_GET['money'] );
		$mquery = $wpdb->prepare("UPDATE $table_meta_name SET meta_value = %s WHERE order_id = %d AND meta_key = %s", serialize($upvalue), $order_id, 'acting_'.$_REQUEST['tracking_no']);
	//usces_log('mquery2 : '.print_r($mquery, true), 'acting_transaction.log');
		$res = $wpdb->query( $mquery );
		if(!$res){
			//usces_log('zeus bank error3 : '.print_r($data, true), 'acting_transaction.log');
			usces_log('zeus bank error3 : update usces_order_meta', 'acting_transaction.log');
			die('error3');
		}

		usces_log('zeus bank transaction : '.$_REQUEST['tracking_no'], 'acting_transaction.log');
		die('zeus');
		
	//*** zeus_conv ***//
	}elseif( isset($_REQUEST['acting']) && 'zeus_conv' == $_REQUEST['acting'] && isset($_REQUEST['status']) && isset($_REQUEST['sendpoint']) ){
		foreach( $_REQUEST as $key => $value ){
			$data[$key] = $value;
		}
		usces_log('zeus conv cgi data : '.print_r($data,true), 'acting_transaction.log');

		$acting_opts = $usces->options['acting_settings']['zeus'];

		$table_name = $wpdb->prefix . "usces_order";
		$table_meta_name = $wpdb->prefix . "usces_order_meta";
		$mquery = $wpdb->prepare("SELECT order_id, meta_value FROM $table_meta_name WHERE meta_key = %s", 'acting_'.$_REQUEST['sendpoint']);
		$values = $wpdb->get_row( $mquery, ARRAY_A );
		if( $values == NULL ){
			
//20110203ysk start
/*			$res = $usces->order_processing();
			
			if( 'error' == $res ){
				usces_log('zeus conv error1 : '.print_r($data, true), 'acting_transaction.log');
				die('error1');
			}else{
				$order_id = $usces->cart->get_order_entry('ID');
				$value = serialize($_GET);
				$query = $wpdb->prepare("INSERT INTO $table_meta_name (order_id, meta_key, meta_value) VALUES (%d, %s, %s)", $order_id, 'acting_'.$_REQUEST['sendpoint'], $value);
				$res = $wpdb->query( $query );
				usces_log('zeus conv ordered : OK', 'acting_transaction.log');
			}*/
//20110203ysk end

		}else{
			if( '05' !== $_REQUEST['status'] ) {//20131128ysk 0000794
				$value = unserialize($values['meta_value']);
				$status = ( '04' === $_REQUEST['status'] ) ? 'receipted,' : 'noreceipt,';
				$order_id = $values['order_id'];
				//$add_point = true;//20120306ysk 0000324
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
					//$add_point = false;//20120306ysk 0000324
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
				//usces_action_acting_getpoint( $order_id, $add_point );//20120306ysk 0000324
				if( '04' === $_REQUEST['status'] ) usces_action_acting_getpoint( $order_id );//20140130ysk
			}
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
				$data[$key] = esc_sql($value);
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
				$data[$key] = esc_sql($value);
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
					$data[$key] = esc_sql($value);
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
	} elseif( isset($_REQUEST['ipn_track_id']) ) {//20131121ysk
		foreach( $_REQUEST as $key => $value ){
			$data[$key] = $value;
		}
		usces_log('paypal ipn : '.print_r($data, true), 'acting_transaction.log');
//20131121ysk start
		$table_name = $wpdb->prefix."usces_order";
		$table_meta_name = $wpdb->prefix."usces_order_meta";
		if( isset($_REQUEST['txn_id']) or isset($_REQUEST['recurring_payment_id']) ) {
			if( ( isset($_REQUEST['payment_status']) and 'Completed' == $_REQUEST['payment_status'] ) or 
				( isset($_REQUEST['profile_status']) and 'Active' == $_REQUEST['profile_status'] ) ) {
				$settlement_id = ( isset($_REQUEST['recurring_payment_id']) ) ? $_REQUEST['recurring_payment_id'] : $_REQUEST['txn_id'];
				$query = $wpdb->prepare( "SELECT ID, order_status FROM $table_name INNER JOIN $table_meta_name ON ID = order_id WHERE meta_key = %s AND meta_value = %s", 'settlement_id', $settlement_id );
				$order_data = $wpdb->get_row( $query, ARRAY_A );
				if( $order_data ) {
					if( $usces->is_status( 'pending', $order_data['order_status'] ) ) {
						$order_status = str_replace( 'pending', 'receipted', $order_data['order_status'] );
						$query = $wpdb->prepare( "UPDATE $table_name SET order_status = %s WHERE ID = %d", $order_status, $order_data['ID'] );
						$res = $wpdb->query( $query );
					}
					do_action( 'usces_action_paypal_ipn_status_completed', $order_data );
				}
			}
		}
//20131121ysk end
		die('PayPal');
//20110523ysk end
//20120413ysk start
	//*** SoftBankPayment ***//
	} elseif( !isset($_GET['acting_return']) && isset($_POST['res_result']) && isset($_POST['res_pay_method']) ) {
		//usces_log('SoftBankPayment : '.print_r($_REQUEST, true), 'acting_transaction.log');
		foreach( $_POST as $key => $value ) {
			$data[$key] = mb_convert_encoding($value, 'UTF-8', 'SJIS');
		}
		$acting = $data['free1'];

		switch( $data['res_result'] ) {
		case 'OK'://決済処理OK
			$table_meta_name = $wpdb->prefix."usces_order_meta";
			$query = $wpdb->prepare("SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'res_tracking_id', $data['res_tracking_id']);
			$order_id = $wpdb->get_var($query);
			if( !$order_id ) {

				$res = $usces->order_processing();
				if( 'ordercompletion' == $res ){
					//$usces->cart->crear_cart();
				} else {
					usces_log('SoftBankPayment '.$data['res_pay_method'].' order processing error : '.print_r($data, true), 'acting_transaction.log');
					die('NG,order processing error');
				}
			}

			//$acting_opts = $usces->options['acting_settings']['sbps'];
			//if( 'on' == $acting_opts['cust'] ) {
			//	$usces->set_member_meta_value( 'sbps_cust_no', $data['res_sps_cust_no'], $data['cust_code'] );
			//	$usces->set_member_meta_value( 'sbps_payment_no', $data['res_sps_payment_no'], $data['cust_code'] );
			//}
			usces_log('SoftBankPayment '.$data['res_pay_method'].' [OK] transaction : '.$data['res_tracking_id'], 'acting_transaction.log');
			die('OK,');
			break;

		case 'PY'://入金結果通知
			$table_name = $wpdb->prefix."usces_order";
			$table_meta_name = $wpdb->prefix."usces_order_meta";
			$query = $wpdb->prepare("SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'res_tracking_id', $data['res_tracking_id']);
			$order_id = $wpdb->get_var($query);
			if( $order_id == NULL ) {
				usces_log('SoftBankPayment '.$data['res_pay_method'].' [PY] error1 : '.print_r($data, true), 'acting_transaction.log');
				die('NG,order_id error');
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
			if( $res === false ) {
				usces_log('SoftBankPayment '.$data['res_pay_method'].' [PY] error2 : '.print_r($data, true), 'acting_transaction.log');
				die('NG,usces_order update error');
			}

			$res = $usces->set_order_meta_value($acting, serialize($data), $order_id);
			if( $res === false ) {
				usces_log('SoftBankPayment '.$data['res_pay_method'].' [PY] error3 : '.print_r($data, true), 'acting_transaction.log');
				die('NG,usces_order_meta update error');
			}

			usces_action_acting_getpoint( $order_id );

			usces_log('SoftBankPayment '.$data['res_pay_method'].' [PY] transaction : '.$order_id, 'acting_transaction.log');
			die('OK,');

		case 'CN'://期限切通知
			$table_name = $wpdb->prefix."usces_order";
			$table_meta_name = $wpdb->prefix."usces_order_meta";
			$query = $wpdb->prepare("SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'res_tracking_id', $data['res_tracking_id']);
			$order_id = $wpdb->get_var($query);
			if( $order_id == NULL ) {
				usces_log('SoftBankPayment '.$data['res_pay_method'].' [CN] error1 : '.print_r($data, true), 'acting_transaction.log');
				die('NG,order_id error');
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
			if( $res === false ) {
				usces_log('SoftBankPayment '.$data['res_pay_method'].' [CN] error2 : '.print_r($data, true), 'acting_transaction.log');
				die('NG,usces_order update error');
			}

			$res = $usces->set_order_meta_value($acting, serialize($data), $order_id);
			if( $res === false ) {
				usces_log('SoftBankPayment '.$data['res_pay_method'].' [CN] error3 : '.print_r($data, true), 'acting_transaction.log');
				die('NG,usces_order_meta update error');
			}

			//usces_action_acting_getpoint( $order_id, false );

			usces_log('SoftBankPayment '.$data['res_pay_method'].' [CN] transaction : '.$order_id, 'acting_transaction.log');
			die('OK,');

		default:
			usces_log('SoftBankPayment '.$data['res_pay_method'].' ['.$data['res_result'].'] : '.print_r($data,true), 'acting_transaction.log');
			die('OK,');
		}
//20120413ysk end
//20121030ysk start
	//*** telecom credit ***//
	} elseif( isset($_REQUEST['clientip']) && isset($_REQUEST['sendid']) && (isset($_REQUEST['acting']) && 'telecom_edy' == $_REQUEST['acting']) ) {
		foreach( $_REQUEST as $key => $value ) {
			$data[$key] = $value;
		}
		usces_log('telecom edy : '.print_r($data, true), 'acting_transaction.log');
		//die('SuccessOK');
	} elseif( isset($_REQUEST['clientip']) && isset($_REQUEST['sendid']) && isset($_REQUEST['edy']) ) {
		foreach( $_REQUEST as $key => $value ) {
			$data[$key] = $value;
		}
		if( $_REQUEST['rel'] === 'yes' && isset($_REQUEST['option']) ) {
			$table_meta_name = $wpdb->prefix."usces_order_meta";
			$mquery = $wpdb->prepare( "SELECT order_id, meta_value FROM $table_meta_name WHERE meta_key = %s", $_REQUEST['option'] );
			$mvalue = $wpdb->get_row( $mquery, ARRAY_A );
			$value = unserialize( $mvalue['meta_value'] );
			$_SESSION['usces_cart'] = $value['usces_cart'];
			$_SESSION['usces_entry'] = $value['usces_entry'];
			$_SESSION['usces_member'] = $value['usces_member'];
			$res = $usces->order_processing();
			if( 'ordercompletion' == $res ) {
				$query = $wpdb->prepare( "DELETE FROM $table_meta_name WHERE meta_key = %s", $_REQUEST['option'] );
				$res = $wpdb->query( $query );
				usces_log('telecom edy - Payment confirmation : '.print_r($data, true), 'acting_transaction.log');
			} else {
				usces_log('telecom edy - Error 1 : '.print_r($data, true), 'acting_transaction.log');
			}
		} else {
			usces_log('telecom edy - Error 2 : '.print_r($data, true), 'acting_transaction.log');
		}
		die('SuccessOK');
//20121030ysk end
//20120618ysk start
	//*** telecom credit ***//
	}elseif( isset($_REQUEST['clientip']) && isset($_REQUEST['sendid']) && isset($_REQUEST['rel']) ){
		foreach( $_REQUEST as $key => $value ){
			$data[$key] = $value;
		}
		usces_log('telecom card : '.print_r($data, true), 'acting_transaction.log');
		die('SuccessOK');
//20120618ysk end
//20121206ysk start
	//*** digitalcheck card ***//
	} elseif( isset($_REQUEST['SID']) && isset($_REQUEST['FUKA']) && substr($_REQUEST['FUKA'], 0, 24) == 'acting_digitalcheck_card' ) {
		foreach( $_REQUEST as $key => $value ) {
			$data[$key] = $value;
		}
		$sid = ( isset($data['SID']) ) ? $data['SID'] : '';
//usces_log('digitalcheck card : '.print_r($data, true), 'digitalcheck.log');

		if( isset($data['SEQ']) ) {
			$acting_opts = $usces->options['acting_settings']['digitalcheck'];
			$ip_user_id = substr($data['FUKA'], 24);
//usces_log('ip_user_id : '.$ip_user_id, 'digitalcheck.log');
			if( 'on' == $acting_opts['card_user_id'] and !empty($ip_user_id) ) {
				$member = $usces->get_member();
				$usces->set_member_meta_value( 'digitalcheck_ip_user_id', $ip_user_id, $member['ID'] );
			}

			$res = $usces->order_processing();
			if( 'ordercompletion' == $res ) {
				$order_id = $usces->cart->get_order_entry('ID');
				$usces->set_order_meta_value( 'acting_digitalcheck_card', serialize( $data ), $order_id );
			} else {
				usces_log('digitalcheck card : order processing error', 'acting_transaction.log');
			}

			header('content-type : text/plain;charset=Shift_JIS');
			die("0");
		}

	//*** digitalcheck conv ***//
	} elseif( isset($_REQUEST['SID']) && isset($_REQUEST['FUKA']) && substr($_REQUEST['FUKA'], 0, 24) == 'acting_digitalcheck_conv' ) {
		foreach( $_REQUEST as $key => $value ) {
			$data[$key] = $value;
		}
		$sid = ( isset($data['SID']) ) ? $data['SID'] : '';
//usces_log("digitalcheck conv : ".print_r($_REQUEST,true), 'digitalcheck.log');

		if( isset($data['SEQ']) ) {
			$table_name = $wpdb->prefix."usces_order";
			$table_meta_name = $wpdb->prefix."usces_order_meta";

			$query = $wpdb->prepare("SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'SID', $sid );
			$order_id = $wpdb->get_var( $query );
			if( $order_id == NULL ) {
				usces_log( 'digitalcheck conv error1 : '.print_r($data, true), 'acting_transaction.log' );
				header('content-type : text/plain;charset=Shift_JIS');
				die('9');
			}

			if( isset($data['CVS']) ) {//入金
				$mquery = $wpdb->prepare("
					UPDATE $table_name SET order_status = 
					CASE 
						WHEN LOCATE('noreceipt', order_status) > 0 THEN REPLACE(order_status, 'noreceipt', 'receipted') 
						WHEN LOCATE('receipted', order_status) > 0 THEN order_status 
						ELSE CONCAT('receipted,', order_status ) 
					END 
					WHERE ID = %d", $order_id );
				$res = $wpdb->query( $mquery );
				if( $res === false ) {
					usces_log('digitalcheck conv error2 : '.print_r($data, true), 'acting_transaction.log');
					header('content-type : text/plain;charset=Shift_JIS');
					die('9');
				}

				usces_action_acting_getpoint( $order_id );

			} else {//取消
				$mquery = $wpdb->prepare("
					UPDATE $table_name SET order_status = 
					CASE 
						WHEN LOCATE('receipted', order_status) > 0 THEN REPLACE(order_status, 'receipted', 'noreceipt') 
						WHEN LOCATE('noreceipt', order_status) > 0 THEN order_status 
						ELSE CONCAT('noreceipt,', order_status ) 
					END 
					WHERE ID = %d", $order_id );
				$res = $wpdb->query( $mquery );
				if( $res === false ) {
					usces_log('digitalcheck conv error3 : '.print_r($data, true), 'acting_transaction.log');
					header('content-type : text/plain;charset=Shift_JIS');
					die('9');
				}

				usces_action_acting_getpoint( $order_id, false );
			}

			$usces->set_order_meta_value( 'acting_digitalcheck_conv', serialize( $data ), $order_id );

			$dquery = $wpdb->prepare( "DELETE FROM $table_meta_name WHERE meta_key = %s", $sid );
			$res = $wpdb->query( $dquery );

			header('content-type : text/plain;charset=Shift_JIS');
			die("0");

		} else {
			if( isset($data['CVS']) and isset($data['SHNO']) ) {//決済
				$table_meta_name = $wpdb->prefix."usces_order_meta";
				$query = $wpdb->prepare("SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'SID', $sid );
				$order_id = $wpdb->get_var($query);
				if( $order_id ) {
					$usces->set_order_meta_value( 'acting_digitalcheck_conv', serialize( $data ), $order_id );

				} else {
					$res = $usces->order_processing();
					if( 'ordercompletion' == $res ) {
						$order_id = $usces->cart->get_order_entry('ID');
						$usces->set_order_meta_value( 'acting_digitalcheck_conv', serialize( $data ), $order_id );
					} else {
						usces_log('digitalcheck conv : order processing error', 'acting_transaction.log');
					}
				}
				header('content-type : text/plain;charset=Shift_JIS');
				die("0");

			} elseif( isset($data['purchase']) ) {

			} else {
				$permalink_structure = get_option('permalink_structure');
				$delim = ( !$usces->use_ssl && $permalink_structure ) ? '?' : '&';
				header( 'location: '.USCES_CART_URL.$delim.'acting=digitalcheck_conv&acting_return=1&SID='.$sid );
				exit;
			}
		}
//20121206ysk end
//20130225ysk start
	//*** mizuho card ***//
	} elseif( ( isset($_GET['p_ver']) && $_GET['p_ver'] == '0200' ) && ( isset($_GET['bkcode']) && $_GET['bkcode'] == 'bg01' ) ) {
		//usces_log('mizuho card : '.print_r($_GET,true),'mizuho.log');
		$stran = ( array_key_exists('stran', $_REQUEST) ) ? $_REQUEST['stran'] : '';
		$mbtran = ( array_key_exists('mbtran', $_REQUEST) ) ? $_REQUEST['mbtran'] : '';
		$rsltcd = ( array_key_exists('rsltcd', $_REQUEST) ) ? $_REQUEST['rsltcd'] : '';
		$rsltdcd = ( array_key_exists('rsltdcd', $_REQUEST) ) ? $_REQUEST['rsltdcd'] : '';
		$permalink_structure = get_option( 'permalink_structure' );
		$delim = ( !$usces->use_ssl && $permalink_structure ) ? '?' : '&';
		if( '108' == substr($rsltcd, 0, 3) or '208' == substr($rsltcd, 0, 3) or '308' == substr($rsltcd, 0, 3 ) ) {//キャンセル
			header( 'location: '.USCES_CART_URL.$delim.'confirm=1' );
		} elseif( '109' == substr($rsltcd, 0, 3) or '209' == substr($rsltcd, 0, 3) or '309' == substr($rsltcd, 0, 3) ) {//エラー
			usces_log( 'mizuho card : '.print_r( $_GET, true ), 'acting_transaction.log' );
			header( 'location: '.USCES_CART_URL.$delim.'acting=mizuho_card&acting_return=0&rsltdcd='.$rsltdcd );
		} else {
			header( 'location: '.USCES_CART_URL.$delim.'acting=mizuho_card&acting_return=1&stran='.$stran.'&mbtran='.$mbtran.'&rsltcd='.$rsltcd );
		}
		die();

	//*** mizuho conv ***//
	} elseif( ( isset($_GET['p_ver']) && $_GET['p_ver'] == '0200' ) && ( isset($_GET['bkcode']) && ( $_GET['bkcode'] == 'cv01' or $_GET['bkcode'] == 'cv02' ) ) ) {
		//usces_log('mizuho conv : '.print_r($_GET,true),'mizuho.log');
		$stran = ( array_key_exists('stran', $_REQUEST) ) ? $_REQUEST['stran'] : '';
		$mbtran = ( array_key_exists('mbtran', $_REQUEST) ) ? $_REQUEST['mbtran'] : '';
		$bktrans = ( array_key_exists('bktrans', $_REQUEST) ) ? $_REQUEST['bktrans'] : '';
		$tranid = ( array_key_exists('tranid', $_REQUEST) ) ? $_REQUEST['tranid'] : '';
		$tdate = ( array_key_exists('tdate', $_REQUEST) ) ? $_REQUEST['tdate'] : '';
		$rsltcd = ( array_key_exists('rsltcd', $_REQUEST) ) ? $_REQUEST['rsltcd'] : '';
		$permalink_structure = get_option( 'permalink_structure' );
		$delim = ( !$usces->use_ssl && $permalink_structure ) ? '?' : '&';
		if( '' != $tdate ) {//入金通知
			foreach( $_REQUEST as $key => $value ) {
				$data[$key] = $value;
			}
			if( $rsltcd == "0000000000000" ) {
				$table_name = $wpdb->prefix."usces_order";
				$table_meta_name = $wpdb->prefix."usces_order_meta";

				$query = $wpdb->prepare( "SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'stran', $stran );
				$order_id = $wpdb->get_var( $query );
				if( $order_id == NULL ) {
					usces_log( 'mizuho conv error1 : '.print_r($data, true), 'acting_transaction.log' );

				} else {
					$mquery = $wpdb->prepare("
						UPDATE $table_name SET order_status = 
						CASE 
							WHEN LOCATE('noreceipt', order_status) > 0 THEN REPLACE(order_status, 'noreceipt', 'receipted') 
							WHEN LOCATE('receipted', order_status) > 0 THEN order_status 
							ELSE CONCAT('receipted,', order_status ) 
						END 
						WHERE ID = %d", $order_id );
					$res = $wpdb->query( $mquery );
					if( $res === false ) {
						usces_log( 'mizuho conv error2 : '.print_r($data, true), 'acting_transaction.log' );

					} else {
						usces_action_acting_getpoint( $order_id );

						$usces->set_order_meta_value( 'acting_mizuho_conv', serialize($data), $order_id );

						$dquery = $wpdb->prepare( "DELETE FROM $table_meta_name WHERE meta_key = %s", $stran );
						$res = $wpdb->query( $dquery );
					}
				}

			} else {
				//header( 'location: '.USCES_CART_URL.$delim.'confirm=1' );
				usces_log( 'mizuho conv : '.print_r( $_GET, true ), 'acting_transaction.log' );
			}

		} elseif( '108' == substr($rsltcd, 0, 3) or '208' == substr($rsltcd, 0, 3) or '308' == substr($rsltcd, 0, 3) ) {//キャンセル
			header( 'location: '.USCES_CART_URL.$delim.'confirm=1' );
		} elseif( '109' == substr($rsltcd, 0, 3) or '209' == substr($rsltcd, 0, 3) or '309' == substr($rsltcd, 0, 3) ) {//エラー
			usces_log( 'mizuho conv : '.print_r( $_GET, true ), 'acting_transaction.log' );
			header( 'location: '.USCES_CART_URL.$delim.'acting=mizuho_card&acting_return=0' );
		} else {
			header( 'location: '.USCES_CART_URL.$delim.'acting=mizuho_conv&acting_return=1&stran='.$stran.'&mbtran='.$mbtran.'&bktrans='.$_GET['bktrans'].'&tranid='.$tranid.'&rsltcd='.$rsltcd );
		}
		die();
//20130225ysk end
//20131220ysk start
	//*** AnotherLane credit ***//
	} elseif( isset($_GET['SiteId']) && isset($_GET['TransactionId']) && isset($_GET['Result']) ) {
		$acting_opts = $usces->options['acting_settings']['anotherlane'];
		foreach( $_GET as $key => $value ) {
			$data[$key] = $value;
		}
		if( $acting_opts['siteid'] == $_GET['SiteId'] && 'OK' == $_GET['Result'] ) {
			$table_meta_name = $wpdb->prefix."usces_order_meta";
			$query = $wpdb->prepare( "SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'TransactionId', $data['TransactionId'] );
			$order_id = $wpdb->get_var( $query );
			if( !$order_id ) {
				$res = $usces->order_processing();
				if( 'ordercompletion' == $res ) {
					$usces->cart->crear_cart();
					usces_log( 'AnotherLane [OK] transaction : '.$data['TransactionId'], 'acting_transaction.log' );
				} else {
					usces_log( 'AnotherLane order processing error : '.print_r($data, true), 'acting_transaction.log' );
				}
			}
		}
		exit;

	} elseif( isset($_GET['ali_back']) && isset($_POST['SiteId']) && isset($_POST['Result']) ) {
		$acting_opts = $usces->options['acting_settings']['anotherlane'];
		if( $acting_opts['siteid'] == $_POST['SiteId'] && 'NG' == $_POST['Result'] ) {
			$permalink_structure = get_option( 'permalink_structure' );
			$delim = ( !$usces->use_ssl && $permalink_structure ) ? '?' : '&';
			header( 'location: '.USCES_CART_URL.$delim.'confirm=1' );
			exit;
		} elseif( $acting_opts['siteid'] == $_POST['SiteId'] && 'OK' == $_POST['Result'] ) {
			$permalink_structure = get_option( 'permalink_structure' );
			$delim = ( !$usces->use_ssl && $permalink_structure ) ? '?' : '&';
			header( 'location: '.USCES_CART_URL.$delim.'acting=anotherlane_card&acting_return=1' );
			exit;
		}
//20131220ysk end
//20140206ysk start
	//*** Veritrans card ***//
	} elseif( isset($_GET['acting']) and 'veritrans_card' == $_GET['acting'] and isset($_POST['orderId']) and isset($_POST['merchantEncryptionKey']) ) {
		if( isset($_POST['mStatus']) and 'success' == $_POST['mStatus'] ) {
			$table_meta_name = $wpdb->prefix."usces_order_meta";
			$query = $wpdb->prepare( "SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'orderId', $_POST['orderId'] );
			$order_id = $wpdb->get_var( $query );
			if( !$order_id ) {
				$res = $usces->order_processing();
				if( 'ordercompletion' == $res ) {
					do_action( 'usces_action_veritrans_card_completion' );
					$usces->cart->crear_cart();
					usces_log( 'Veritrans [OK] transaction : '.$_POST['orderId'], 'acting_transaction.log' );
				} else {
					usces_log( 'Veritrans order processing error : '.print_r( $_POST, true ), 'acting_transaction.log' );
				}
			}
		}
		exit;

	//*** Veritrans conv ***//
	} elseif( isset($_GET['acting']) and 'veritrans_conv' == $_GET['acting'] and isset($_POST['orderId']) and isset($_POST['merchantEncryptionKey']) ) {
		//決済結果通知
		if( isset($_POST['mStatus']) and 'success' == $_POST['mStatus'] ) {
			$table_meta_name = $wpdb->prefix."usces_order_meta";
			$query = $wpdb->prepare( "SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'orderId', $_POST['orderId'] );
			$order_id = $wpdb->get_var( $query );
			if( !$order_id ) {
				$res = $usces->order_processing();
				if( 'ordercompletion' == $res ) {
					do_action( 'usces_action_veritrans_conv_completion' );
					$usces->cart->crear_cart();
					usces_log( 'Veritrans [OK] transaction : '.$_POST['orderId'], 'acting_transaction.log' );
				} else {
					usces_log( 'Veritrans order processing error : '.print_r( $_POST, true ), 'acting_transaction.log' );
				}
			}
		}
		exit;

	} elseif( isset($_REQUEST['numberOfNotify']) and isset($_REQUEST['pushTime']) and isset($_REQUEST['pushId']) ) {
		//支払通知
		$data = array();
		foreach( $_REQUEST as $key => $value ) {
			$num = substr( $key, -4 );
			if( preg_match( "/^[0-9]+$/", $num ) ) {
				$nkey = substr( $key, 0, strpos( $key, $num ) );
				$data[$nkey][(int)$num] = $value;
			} else {
				$data[$key] = $value;
			}
		}

		$count = count( $data['orderId'] );
		if( 0 < $count ) {
			$table_name = $wpdb->prefix."usces_order";
			$table_meta_name = $wpdb->prefix."usces_order_meta";

			for( $i = 0; $i < $count; $i++ ) {
				if( isset( $data['orderId'][$i] ) ) {
					$sdata = array();
					$sdata['acting'] = 'veritrans_conv';
					$sdata['orderId'] = $data['orderId'][$i];
					$sdata['cvsType'] = isset( $data['cvsType'][$i] ) ? $data['cvsType'][$i] : '';
					$sdata['receiptNo'] = isset( $data['receiptNo'][$i] ) ? $data['receiptNo'][$i] : '';
					$sdata['receiptDate'] = isset( $data['receiptDate'][$i] ) ? $data['receiptDate'][$i] : '';
					$sdata['rcvAmount'] = isset( $data['rcvAmount'][$i] ) ? $data['rcvAmount'][$i] : '';
					$query = $wpdb->prepare( "SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'orderId', $sdata['orderId'] );
					$order_id = $wpdb->get_var( $query );
					if( $order_id == NULL ) {
						usces_log( 'Veritrans conv error1 : '.print_r( $sdata, true ), 'acting_transaction.log' );

					} else {
						$mquery = $wpdb->prepare("
							UPDATE $table_name SET order_status = 
							CASE 
								WHEN LOCATE('noreceipt', order_status) > 0 THEN REPLACE(order_status, 'noreceipt', 'receipted') 
								WHEN LOCATE('receipted', order_status) > 0 THEN order_status 
								ELSE CONCAT('receipted,', order_status ) 
							END 
							WHERE ID = %d", $order_id );
						$res = $wpdb->query( $mquery );
						if( $res === false ) {
							usces_log( 'Veritrans conv error2 : '.print_r( $sdata, true ), 'acting_transaction.log' );

						} else {
							usces_action_acting_getpoint( $order_id );

							$usces->set_order_meta_value( 'acting_veritrans_conv', serialize( $sdata ), $order_id );

							$dquery = $wpdb->prepare( "DELETE FROM $table_meta_name WHERE meta_key = %s", $sdata['orderId'] );
							$res = $wpdb->query( $dquery );
						}
					}
				}
			}
		}
		exit;
//20140206ysk end
	}
}
?>
