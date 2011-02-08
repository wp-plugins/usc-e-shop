<?php
/*
PayPal決済モジュール

【手順1】PayPalマイアカウントの設定

マイアカウント→個人設定→ウェブペイメントの設定

・自動復帰：オン
・復帰URL：ショップトップページのＵＲＬ/index.php?page_id=カートページのＩＤ&acting=paypal&acting_return=1
　SSLを利用している場合はhttp//をhttps://にする。また共用SSLの場合はそのURLを指定。
　カートページのＩＤはWelcart 管理画面のHome で確認できます。
・支払いデータ転送：オン


マイアカウント→個人設定→即時支払い通知の設定

・通知ＵＲＬ：ショップトップページのＵＲＬ
　ショップトップページのURLは上記と同じ
・ＩＰＮメッセージ：有効
　推奨は「有効」です。


マイアカウント→個人設定→言語のエンコード

・言語のエンコード：日本語
・エンコード方式：UTF-8（詳細オプション）
・いいえ。次のエンコード方式を使用します。：UTF-8（詳細オプション）
　



【手順2】このファイルを編集
・$usces_paypal_business ：登録メールアドレス
・$usces_paypal_url ：本稼動の場合は"www.paypal.com"。sandboxの場合は"www.sandbox.paypal.com"。
・$auth_token ： ウェブペイメントの設定で発行されたID トークン。
・編集後はpaypal.php のファイル名で任意の場所（※）に保存

※テストの時はepsilon_sample.php が有った場所に保存して構いません。そのまま使用できます。
　しかし、この場所はプラグインのアップグレードの際に削除されてしまいます。
　実際の設置場所はplugins フォルダの外にすることをお勧めします。
　また、その際はWelcart管理画面・システム設定ページにてモジュールの設置場所を指定してください。


【手順3】Welcart 管理画面の基本設定ページにて新しい支払方法を追加
・支払方法名：ショップに表示される支払方法名（必須）
・説明：ショップに表示される支払方法の説明
・決済種別：「代行業者決済」を選択
・決済モジュール：「paypal.php」と記入
・「新しい支払方法を追加」ボタンを押して追加を確定

*/
global $usces;
$usces->log_flg = 0;//0：ログを取らない、1：ログを取る
/*********************************************************************************/
	//登録メールアドレス
$usces_paypal_business = "*********@********.***";
	//PayPal URL
$usces_paypal_url = "www.paypal.com";
/*********************************************************************************/

function paypal_check($usces_paypal_url) {
/*********************************************************************************/
	//ID トークン
	$auth_token = "**********************************************************";
/*********************************************************************************/
	settlement_log('PDT開始');

	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-synch';
	
	$tx_token = $_GET['tx'];
	$req .= "&tx=$tx_token&at=$auth_token";
	
	// post back to PayPal system to validate
	$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$fp = fsockopen ($usces_paypal_url,  80, $errno, $errstr, 30);
	// If possible, securely post back to paypal using HTTPS
	// Your PHP server will need to be SSL enabled
	// $fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
	$results = array();
	if (!$fp) {
		$results[0] = false;
		settlement_log('PDT接続エラー');
	} else {
		fputs ($fp, $header . $req);
		// read the body data 
		$res = '';
		$headerdone = false;
		while (!feof($fp)) {
			$line = fgets ($fp, 1024);
			if (strcmp($line, "\r\n") == 0) {
				// read the header
				$headerdone = true;
			}else if ($headerdone){
				// header has been read. now read the contents
				$res .= $line;
			}
		}
	
		// parse the data
		$lines = explode("\n", $res);
		$keyarray = array();
		if (strcmp ($lines[0], "SUCCESS") == 0) {
			$results[0] = true;
			for ($i=1; $i<count($lines);$i++){
				list($key,$val) = explode("=", $lines[$i]);
				$results[urldecode($key)] = urldecode($val);
			}
			$ret = true;
			settlement_log('PDT[SUCCESS]');
		}else if (strcmp ($lines[0], "FAIL") == 0) {
			$results[0] = false;
			settlement_log(__('PDT Refusal', 'usces') . "\n\t\t\t" . __("PayPal gives back 'FAIL'. Please confirm setting.", 'usces'));
		}
	
		fclose ($fp);
	}
	return $results;
}

function paypal_ipn_check($usces_paypal_url) {
	settlement_log(__('IPN Start', 'usces'));
	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-validate';
	
	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$req .= "&$key=$value";
	}
	// post back to PayPal system to validate
	$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$fp = fsockopen ($usces_paypal_url, 80, $errno, $errstr, 30);

	$item_name = $_POST['item_name'];
	$item_number = $_POST['item_number'];
	$payment_status = $_POST['payment_status'];
	$payment_amount = $_POST['mc_gross'];
	$payment_currency = $_POST['mc_currency'];
	$txn_id = $_POST['txn_id'];
	$receiver_email = $_POST['receiver_email'];
	$payer_email = $_POST['payer_email'];

	$results = array();
	if (!$fp) {
		$results[0] = false;
		settlement_log(__('IPN Connection Error', 'usces'));
	} else {
		fputs ($fp, $header . $req);
		// read the body data 
		$res = '';
		$headerdone = false;
		while (!feof($fp)) {
			$line = fgets ($fp, 1024);
			if (strcmp($line, "\r\n") == 0) {
				// read the header
				$headerdone = true;
			}else if ($headerdone){
				// header has been read. now read the contents
				$res .= $line;
			}
		}
	
		// parse the data
		$lines = explode("\n", $res);
		$keyarray = array();
		if (strcmp ($lines[0], "VERIFIED") == 0) {
			$results[0] = true;
			$results['payment_status'] = $payment_status;
			$ret = true;
			settlement_log('IPN[SUCCESS]');
		}else if (strcmp ($lines[0], "FAIL") == 0) {
			$results[0] = false;
			settlement_log(__('IPN Refusal', 'usces') . "\n\t\t\t" . __("PayPal gives back 'FAIL'. Please confirm setting.", 'usces'));
		}
	
		fclose ($fp);
	}
	return $results;
}

function settlement_log($log){
	global $usces;
	if(!$usces->log_flg) return;
	
	$log = date('[Y-m-d H:i:s]', current_time('timestamp')) . "\t" . $log . "\n";
	$file = $usces->options['settlement_path'].'/paypal.log';
	$fp = fopen($file, 'a');
	fwrite($fp, $log);
	fclose($fp);
}
?>
