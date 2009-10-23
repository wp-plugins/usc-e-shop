<?php
/*****************************************************************************************************/
// 契約番号(8桁) オンライン登録時に発行された契約番号を入力してください。
$contract_code = "00000000";

// 決済区分 (使用したい決済方法を指定してください。登録時に申し込まれていない決済方法は指定できません。)
$st_code = '10000-0000-00000';   // 指定方法はCGI設定マニュアルの「決済区分について」を参照してください。

// 課金区分 (1:一回のみ 2～10:月次課金)
$mission_code = 1;

// 処理区分 (1:初回課金 2:登録済み課金 3:登録のみ 4:登録変更 8:月次課金解除 9:退会)
$process_code = 1;

// インターフェイスURL（初期値はテスト環境用URL。本稼動の際は本番環境用URLに変更が必要。）
$interface_url = 'https://beta.epsilon.jp/cgi-bin/order/receive_order3.cgi';
/*******************************************************************************************************/

$order_number = rand(0,9999999999);
$memo1 = "";
$memo2 = "";
$item_code = $_POST['item_code'];
$item_name = $_POST['item_name'];
$item_price = $_POST['item_price'];
$user_id = $_POST['user_id'];
$user_name = $_POST['user_name'];
$user_mail_add = $_POST['user_mail_add'];
$changeflag = 0;
$interface = parse_url($interface_url);

$vars ="contract_code=$contract_code&user_id=$user_id&user_name=$user_name&user_mail_add=$user_mail_add&item_code=$item_code&item_name=$item_name&order_number=$order_number&st_code=$st_code&mission_code=$mission_code&item_price=$item_price&process_code=$process_code&xml=1";
$header = "POST " . $interface_url . " HTTP/1.1\r\n";
$header .= "Host: www.usconsort.com\r\n";
$header .= "User-Agent: PHP Script\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($vars) . "\r\n";
$header .= "Connection: close\r\n\r\n";
$header .= $vars;
$fp = fsockopen($interface['host'],80,$errno,$errstr,30);

if ($fp){
	fwrite($fp, $header);
	while ( !feof($fp) ) {
		$scr = fgets($fp, 1024);
		preg_match_all("/<result\s(.*)\s\/>/", $scr, $match, PREG_SET_ORDER);
	
		if(!empty($match[0][1])){
			list($key, $value) = explode('=', $match[0][1]);
			$datas[$key] = mb_convert_encoding(urldecode(trim($value, '"')), "UTF-8", "auto");
		}
	}
	fclose($fp);

	if($datas['result'] == 1){
		header("Location: " . $datas['redirect']);
		exit;
	}else{
		$error = $datas['err_code'] . "'" . $datas['err_detail'] . "'";
		header("Location: " . USCES_CART_URL.'&acting=epsilon&acting_return=' . $error);
		exit;
	}
}else{
	header("Location: " . USCES_CART_URL.'&acting=epsilon&acting_return=1');
	exit;
}
?>
