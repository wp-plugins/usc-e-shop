<?php
// Hoock functions
add_action('usces_construct', 'usces_action_acting_construct', 10);
add_action('usces_after_cart_instant', 'usces_action_acting_transaction', 10);

function usces_action_acting_construct(){

	if(isset($_POST['X-TRANID']) && !isset($_POST['OPT'])){//remise
		if( 'remise payment gateway2.4.2.1' != $_SERVER['HTTP_USER_AGENT'] )
			die(0);
		
		$rand = $_POST['X-S_TORIHIKI_NO'];
		$datas = usces_get_order_acting_data($rand);
		$_GET['usces'] = $datas['sesid'];
		
	}elseif( in_array($_SERVER['REMOTE_ADDR'], array('210.164.6.67', '202.221.139.50')) ){//zeus
		
		if( !isset($_REQUEST['sendpoint']) )
			return;
			
		$rand = $_REQUEST['sendpoint'];
		$datas = usces_get_order_acting_data($rand);
		$_GET['usces'] = $datas['sesid'];
		usces_log('zeus : session_id1:'.$datas['sesid'], 'acting_transaction.log');

	}
}

function usces_action_acting_transaction(){
	global $usces, $wpdb;
	
	//*** remise_card ***//
	if(isset($_POST['X-TRANID']) && !isset($_POST['OPT'])){
		if( 'remise payment gateway2.4.2.1' != $_SERVER['HTTP_USER_AGENT'] )
			die(0);

		
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
		usces_log('remise HTTP_USER_AGENT : ' . $_SERVER['HTTP_USER_AGENT'], 'acting_transaction.log');
		if( 'remise payment gateway2.4.2.1' != $_SERVER['HTTP_USER_AGENT'] )
			die(0);
			
		usces_log('remise conv-info : ' . $_POST['S_TORIHIKI_NO'], 'acting_transaction.log');
		
		$table_name = $wpdb->prefix . "usces_order";
		$table_meta_name = $wpdb->prefix . "usces_order_meta";
		
		$mquery = $wpdb->prepare("SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'settlement_id', $_POST['S_TORIHIKI_NO']);
		$order_id = $wpdb->get_var( $mquery );
		
		$mquery = $wpdb->prepare(
					"UPDATE $table_name SET order_status = %s WHERE ID = %d", 'receipted,', $order_id);
		$wpdb->query( $mquery );
		
		$data = serialize( $_POST );
		$mquery = $wpdb->prepare(
					"UPDATE $table_meta_name SET meta_value = %s WHERE meta_key = %d AND order_id = %s", $data, 'settlement_id,', $order_id);
		$wpdb->query( $mquery );
		
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
		$mquery = $wpdb->prepare("SELECT order_id, meta_value FROM $table_meta_name WHERE meta_key = %s", 'zeus_'.$_REQUEST['tracking_no']);
		$values = $wpdb->get_row( $mquery, ARRAY_A );
		if( $value == NULL ){
			
			$order_id = usces_reg_orderdata();
			if( !$order_id )
				usces_log('zeus : Failure reg order data', 'acting_transaction.log');

			$value = serialize($_GET);
			$query = $wpdb->prepare("INSERT INTO $table_meta_name (order_id, meta_key, meta_value) VALUES (%d, %s, %s)", $order_id, 'zeus_'.$_REQUEST['tracking_no'], $value);
			$res = $wpdb->query( $query );
			$usces->cart->crear_cart();


		}else{
		
			$value = unserialize($values['meta_value']);
			$status = ( '03' == $value['status'] ) ? 'receipted,' : 'noreceipt,';
			$order_id = $values['order_id'];
			$mquery = $wpdb->prepare("UPDATE $table_name SET order_status = %s WHERE ID = %d", $status, $order_id);
			$res = $wpdb->query( $mquery );
			if(!$res)
				usces_log('zeus db : error', 'acting_transaction.log');
		}

		die('zeus');
	}
}
?>
