<?php
/*
イプシロン決済モジュール（クレジットカードのみ）

【手順1】イプシロン・システム情報登録（テスト環境）
・オーダー情報発信元ホスト情報：ショップのドメイン若しくはIPアドレス
・決済完了後のリダイレクト先：ショップトップページのURL/?page_id=カートページのＩＤ&acting=epsilon&acting_return=1
　SSLを利用している場合はhttp//をhttps://にする。また共用SSLの場合はそのURLを指定。
・[戻る]ボタンの戻り先URL：ショップトップページのURL/?page_id=カートページのＩＤ&confirm=0
　ショップトップページのURLは上記と同じ
・エラー発生時の戻り先URL：ショップトップページのURL/?page_id=カートページのＩＤ&acting=epsilon&acting_return=0
　ショップトップページのURLは上記と同じ
・タイムアウト情報送信先URL：ショップトップページのURL/?page_id=カートページのＩＤ&acting=epsilon&acting_return=0
　ショップトップページのURLは上記と同じ

※本番環境ではテスト環境と同じものを設定します。


【手順2】このファイルを編集
・$contract_code ：契約番号を入力
・$interface_url ：本稼動の場合は本番環境用URLに変更
・編集後はepsilon.php のファイル名で任意の場所（※）に保存

※テストの時はepsilon_sample.php が有った場所に保存して構いません。そのまま使用できます。
　しかし、この場所はプラグインのアップグレードの際に削除されてしまいます。
　実際の設置場所はplugins フォルダの外にすることをお勧めします。
　また、その際はWelcart管理画面・システム設定ページにてモジュールの設置場所を指定してください。


【手順3】Welcart 管理画面の基本設定ページにて新しい支払方法を追加
・支払方法名：ショップに表示される支払方法名（必須）
・説明：ショップに表示される支払方法の説明
・決済種別：「代行業者決済」を選択
・決済モジュール：「epsilon.php」と記入
・「新しい支払方法を追加」ボタンを押して追加を確定

*/
/*****************************************************************************************************/
// 契約番号(8桁) オンライン登録時に発行された契約番号を入力してください。
$contract_code = "00000000";

// 決済区分 (現在クレジットカード決済のみの対応となっています。それ以外の決済区分は選択できません。)
$st_code = '10000-0000-00000';   // 指定方法はCGI設定マニュアルの「決済区分について」を参照してください。

// 課金区分 (現在一回払いのみの対応となっています。それ以外の課金区分は選択できません。)
$mission_code = 1;

// 処理区分 (現在初回課金のみの対応となっています。それ以外の処理区分は選択できません。)
$process_code = 1;

// インターフェイスURL（初期値はテスト環境用URL。本稼動の際は本番環境用URLに変更が必要。）
$interface_url = 'https://beta.epsilon.jp/cgi-bin/order/receive_order3.cgi';
/*******************************************************************************************************/

$order_number = rand(0,9999999999);
$memo1 = "";
$memo2 = "";
$redirect = urldecode($_GET['redirect_url']);
$url = parse_url($redirect);
$item_code = $_GET['item_code'];
$item_name = mb_convert_encoding(urldecode($_GET['item_name']), 'EUC-JP', 'UTF-8');
$item_price = $_GET['item_price'];
$user_id = $_GET['user_id'];
$user_name = mb_convert_encoding(urldecode($_GET['user_name']), 'EUC-JP', 'UTF-8');
$user_mail_add = urldecode($_GET['user_mail_add']);
$changeflag = 0;
$interface = parse_url($interface_url);

$vars ="contract_code=$contract_code&user_id=$user_id&user_name=$user_name&user_mail_add=$user_mail_add&item_code=$item_code&item_name=$item_name&order_number=$order_number&st_code=$st_code&mission_code=$mission_code&item_price=$item_price&process_code=$process_code&xml=1";
$header = "POST " . $interface_url . " HTTP/1.1\r\n";
$header .= "Host: " . $url['host'] . "\r\n";
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
	
	if((int)$datas['result'] === 1){
		header("Location: " . $datas['redirect']);
		exit;
	}else{
		$error = $datas['err_code'] . "'" . $datas['err_detail'] . "'";
		header("Location: " . $redirect . "&acting=epsilon&acting_return=" . urlencode($error));
		exit;
	}
}else{
		header("Location: " . $redirect . "&acting=epsilon&acting_return=0");
	exit;
}
?>
