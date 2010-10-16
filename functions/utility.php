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

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemShipping', '_itemShipping') WHERE meta_key LIKE 'itemShipping'";
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


function usces_filter_delivery_secure_check( $mes ){
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

function usces_get_conv_name($code){
	switch($code){
		case 'D001':
			$name = 'セブンイレブン';
			break;
		case 'D002':
			$name = 'ローソン';
			break;
		case 'D015':
			$name = 'セイコーマート';
			break;
		case 'D405':
			$name = 'ペイジー';
			break;
		case 'D003':
			$name = 'サンクス';
			break;
		case 'D004':
			$name = 'サークルK';
			break;
		case 'D005':
			$name = 'ミニストップ';
			break;
		case 'D010':
			$name = 'デイリーヤマザキ';
			break;
		case 'D011':
			$name = 'ヤマザキデイリーストア';
			break;
		case 'D030':
			$name = 'ファミリーマート';
			break;
		case 'D401':
			$name = 'CyberEdy';
			break;
		case 'D404':
			$name = '楽天銀行';
			break;
		case 'D406':
			$name = 'ジャパネット銀行';
			break;
		case 'D451':
			$name = 'ウェブマネー';
			break;
		case 'D452':
			$name = 'ビットキャッシュ';
			break;
		case 'P901':
			$name = 'コンビニ払込票';
			break;
		case 'P902':
			$name = 'コンビニ払込票（郵便振替対応）';
			break;
		default:
			$name = '';
	}
	return $name;
}

function usces_payment_detail($usces_entries){
	$payments = usces_get_payments_by_name( $usces_entries['order']['payment_name'] );
	$acting_flag = ( 'acting' == $payments['settlement'] ) ? $payments['module'] : $payments['settlement'];
	$str = '';
	switch( $acting_flag ){
		case 'paypal.php':
			break;
		
		case 'epsilon.php':
			break;
		
		case 'acting_zeus_card':
			$div_name = 'div_' . $_POST['cbrand'];
			if( isset($_POST['howpay']) && '1' === $_POST['howpay'] ){
				$str = '　一括払い';
			}else{
				switch($_POST[$div_name]){
					case '01':
						$str = '　一括払い';
						break;
					case '99':
						$str = '　分割（リボ払い）';
						break;
					case '03':
						$str = '　分割（3回）';
						break;
					case '05':
						$str = '　分割（5回）';
						break;
					case '06':
						$str = '　分割（6回）';
						break;
					case '10':
						$str = '　分割（10回）';
						break;
					case '12':
						$str = '　分割（12回）';
						break;
					case '15':
						$str = '　分割（15回）';
						break;
					case '18':
						$str = '　分割（18回）';
						break;
					case '20':
						$str = '　分割（20回）';
						break;
					case '24':
						$str = '　分割（24回）';
						break;
				}
			}
			break;
		
		case 'acting_zeus_bank':
			break;
		
		case 'acting_remise_card':
			if( isset( $_POST['div'] ) ){
				switch($_POST['div']){
					case '0':
						$str = '　一括払い';
						break;
					case '1':
						$str = '　分割（2回）';
						break;
					case '2':
						$str = '　分割（リボ払い）';
						break;
				}
			}
			break;
		
		case 'acting_remise_conv':
			break;
	}
	return $str;
}

//20100818ysk start
function usces_filter_delivery_check_custom_order( $mes ) {
	global $usces;

	$meta = usces_has_custom_field_meta('order');
	foreach($meta as $key => $entry) {
		$essential = $entry['essential'];
		if($essential == 1) {
			$name = $entry['name'];
			$means = $entry['means'];
			if($means == 2) {//Text
				if(trim($_POST['custom_order'][$key]) == "")
					$mes .= __($name.'を入力してください。', 'usces')."<br />";
			} else {
				if(!isset($_POST['custom_order'][$key]) or $_POST['custom_order'][$key] == "#NONE#")
					$mes .= __($name.'を選択してください。', 'usces')."<br />";
			}
		}
	}

	return $mes;
}

function usces_filter_customer_check_custom_customer( $mes ) {
	global $usces;

	$meta = usces_has_custom_field_meta('customer');
	foreach($meta as $key => $entry) {
		$essential = $entry['essential'];
		if($essential == 1) {
			$name = $entry['name'];
			$means = $entry['means'];
			if($means == 2) {//Text
				if(trim($_POST['custom_customer'][$key]) == "")
					$mes .= __($name.'を入力してください。', 'usces')."<br />";
			} else {
				if(!isset($_POST['custom_customer'][$key]) or $_POST['custom_customer'][$key] == "#NONE#")
					$mes .= __($name.'を選択してください。', 'usces')."<br />";
			}
		}
	}

	return $mes;
}

function usces_filter_delivery_check_custom_delivery( $mes ) {
	global $usces;

	if( $_POST['delivery']['delivery_flag'] == '1' ) {
		$meta = usces_has_custom_field_meta('delivery');
		foreach($meta as $key => $entry) {
			$essential = $entry['essential'];
			if($essential == 1) {
				$name = $entry['name'];
				$means = $entry['means'];
				if($means == 2) {//Text
					if(trim($_POST['custom_delivery'][$key]) == "")
						$mes .= __($name.'を入力してください。', 'usces')."<br />";
				} else {
					if(!isset($_POST['custom_delivery'][$key]) or $_POST['custom_delivery'][$key] == "#NONE#")
						$mes .= __($name.'を選択してください。', 'usces')."<br />";
				}
			}
		}
	}

	return $mes;
}

function usces_filter_member_check_custom_member( $mes ) {
	global $usces;

	unset($_SESSION['usces_member']['custom_member']);
	if(isset($_POST['custom_member'])) {
		foreach( $_POST['custom_member'] as $key => $value )
			if( is_array($value) ) {
				foreach( $value as $k => $v ) 
					$_SESSION['usces_member']['custom_member'][$key][trim($v)] = trim($v);
			} else {
				$_SESSION['usces_member']['custom_member'][$key] = trim($value);
			}
	}

	$meta = usces_has_custom_field_meta('member');
	foreach($meta as $key => $entry) {
		$essential = $entry['essential'];
		if($essential == 1) {
			$name = $entry['name'];
			$means = $entry['means'];
			if($means == 2) {//Text
				if(trim($_POST['custom_member'][$key]) == "")
					$mes .= __($name.'を入力してください。', 'usces')."<br />";
			} else {
				if(!isset($_POST['custom_member'][$key]) or $_POST['custom_member'][$key] == "#NONE#")
					$mes .= __($name.'を選択してください。', 'usces')."<br />";
			}
		}
	}

	return $mes;
}
//20100818ysk end
function usces_dashboard_setup() {
	wp_add_dashboard_widget( 'usces_db_widget' , 'Welcart Information' , 'usces_db_widget');
}

function usces_admin_login_head() {
?>
<script type='text/javascript'>
(function($) {
	usces = {
		settings: {
			url: 'http://www.welcart.com/varch/varch.php',
			type: 'POST',
			cache: false,
			success: function(data, dataType){
			}, 
			error: function(msg){
			}
		},
		varch : function() {
			var s = usces.settings;
			s.data = "action=varch_ajax&ID=usces_varch&ver=" + <?php echo $_SERVER['HTTP_HOST']; ?>;
			$.ajax( s );
			return false;
		}
	};
	usces.varch();
})(jQuery);
</script>
<?php
}
?>