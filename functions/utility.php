<?php
// Utility.php

function usces_metakey_change(){
	global $wpdb;
	$rets = array();
	
	$tableName = $wpdb->prefix . "postmeta";
	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'iopt_', '_iopt_') WHERE meta_key LIKE 'iopt_%'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'isku_', '_isku_') WHERE meta_key LIKE 'isku_%'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemCode', '_itemCode') WHERE meta_key LIKE 'itemCode'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemName', '_itemName') WHERE meta_key LIKE 'itemName'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemRestriction', '_itemRestriction') WHERE meta_key LIKE 'itemRestriction'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemPointrate', '_itemPointrate') WHERE meta_key LIKE 'itemPointrate'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemGpNum1', '_itemGpNum1') WHERE meta_key LIKE 'itemGpNum1'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemGpDis1', '_itemGpDis1') WHERE meta_key LIKE 'itemGpDis1'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemGpNum2', '_itemGpNum2') WHERE meta_key LIKE 'itemGpNum2'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemGpDis2', '_itemGpDis2') WHERE meta_key LIKE 'itemGpDis2'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemGpDis3', '_itemGpDis3') WHERE meta_key LIKE 'itemGpDis3'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemGpNum3', '_itemGpNum3') WHERE meta_key LIKE 'itemGpNum3'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'temShipping', '_itemShipping') WHERE meta_key LIKE 'itemShipping'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemDeliveryMethod', '_itemDeliveryMethod') WHERE meta_key LIKE 'itemDeliveryMethod'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemShippingCharge', '_itemShippingCharge') WHERE meta_key LIKE 'itemShippingCharge'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemIndividualSCharge', '_itemIndividualSCharge') WHERE meta_key LIKE 'itemIndividualSCharge'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	return $rets;
}

function usces_log($log, $file){
	global $usces;
		
	$log = date('[Y-m-d H:i:s]', current_time('timestamp')) . "\t" . $log . "\n";
	$file_path = USCES_PLUGIN_DIR . '/logs/' . $file;
	$fp = fopen($file_path, 'a');
	fwrite($fp, $log);
	fclose($fp);
}


function usces_delivery_secure_check( $mes ){
	global $usces;
	$usces_secure_link = get_option('usces_secure_link');
	$paymod_id = '';
	
	foreach ( (array)$usces->options['payment_method'] as $id => $array ) {
		if( $array['name'] == $_POST['order']['payment_name']){
			$settlement = $array['settlement'];
			break;
		}
	}	

	switch( $settlement ){
		case 'acting_zeus_card':
			if ( strlen(trim($_POST["cnum1"])) != 4 || strlen(trim($_POST["cnum2"])) != 4 || strlen(trim($_POST["cnum3"])) != 4 || strlen(trim($_POST["cnum4"])) != 4 )
				$mes .= __('カード番号が不正です', 'usces') . "<br />";
			
			if ( '' == $_POST["expyy"] )
				$mes .= __('カードの有効年を選択してください', 'usces') . "<br />";
				
			if ( '' == $_POST["expmm"] )
				$mes .= __('カードの有効月を選択してください', 'usces') . "<br />";
				
			if ( '' == trim($_POST["username"]) )
				$mes .= __('カード名義を入力してください', 'usces') . "<br />";
				
			if ( '0' == $_POST["howpay"] && '' == $_POST["cbrand"] )
				$mes .= __('カードブランドを選択してください', 'usces') . "<br />";
				
			if ( 'zeus' != $_POST['acting'] )
				$mes .= __('カード決済データが不正です！', 'usces');
			 
			break;
	}
	
	return $mes;
}
?>