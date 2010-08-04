<?php
// Hoock functions
add_action('usces_construct', 'usces_action_acting_construct', 10);
add_action('usces_after_cart_instant', 'usces_action_acting_transaction', 10);

function usces_action_acting_construct(){
	global $usces;
	
	if(isset($_POST['X-TRANID']) && !isset($_POST['OPT'])){
		if( 'remise payment gateway2.4.2.1' != $_SERVER['HTTP_USER_AGENT'] )
			die(0);
		
		$rand = $_POST['X-S_TORIHIKI_NO'];
		$datas = usces_get_order_acting_data($rand);
		$_GET['usces'] = $datas['sesid'];
		//usces_log('remise : session_id1:'.$datas['sesid'], 'acting_transaction.log');
	}
}

function usces_action_acting_transaction(){
	global $usces;
	
	if(isset($_POST['X-TRANID']) && !isset($_POST['OPT'])){
		if( 'remise payment gateway2.4.2.1' != $_SERVER['HTTP_USER_AGENT'] )
			die(0);

		usces_log('remise access code : '.$_POST['X-R_CODE'], 'acting_transaction.log');
		
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
			$usces->set_payquickid('remise_pcid', $_POST['X-PAYQUICKID']);
			$mail_res = usces_send_ordermail( $order_id );
			die('<SDBKDATA>STATUS=800</SDBKDATA>');
		}
	}
}
?>