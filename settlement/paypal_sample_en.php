<?php
/*
PayPal Payment Module 

[Procedure 1] Setting Up My Account with PayPal 
My Account - Profile - Website Payment Preferences

 * Auto Return: On
 * Return URL : 'The URL of the shop top page'/index.php?page_id='The ID of the cart page'&acting=paypal&acting_return=1
  When You use SSL, please change 'http://' in 'https://'. In the case of common-use SSL, please make entry of common use SSL.
  You can confirm the ID of the cart page in Home of the Welcart admin panel.
 * Payment Data Transfer: On


My Account - Profile - Instant Payment Notification (IPN)

 * Notification URL
  'The URL of the shop top page'
 * IPN messages: Enabled
  I recommend "Enabled".


[Procedure 2] The editing of this file
 * $usces_paypal_business: Log-in Email
 * $usces_paypal_url: "www.paypal.com" or "www.sandbox.paypal.com"
 * $auth_token: The Identity Token that was published in a page of the Website Payment Preferences.
 * After editing, please save it by a file name of 'paypal.php' in the arbitrary place (note).

Note - At the time of testing, you may save it in the place where there was "epsilon_sample.php". You can just use it. However, this place is deleted in the case of the upgrading of the plug-in. Actually, you must do a setting place outside a "plugins" folder. On this occasion, please appoint the setting place of the module in "Welcart admin panel / a system setting page".


[Procedure 3] Add a new payment method in "Welcart admin panel / a General Setting page"
 * A payment method name: A payment method name displayed by a shop.(essential)
 * explanation : The explanation of a payment method displayed by a shop.
 * Type of payment: Choose "the representation supplier settlement".
 * Payment module: Fill it out with "paypal.php".
 * Push the button 'Add a new method for payment'.

*/
global $usces;
$usces->log_flg = 0;//0 : Not take log, 1 : take log
/*********************************************************************************/
	// Log-in Email
$usces_paypal_business = "*********@********.***";
	//PayPal URL
$usces_paypal_url = "www.paypal.com";
/*********************************************************************************/

function paypal_check($usces_paypal_url) {
/*********************************************************************************/
	//Identity Token
	$auth_token = "**********************************************************";
/*********************************************************************************/
	settlement_log('Start PDT');

	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-synch';
	
	$tx_token = $_GET['tx'];
	$req .= '&tx=' . $tx_token . '&at=' . $auth_token;
	
	// post back to PayPal system to validate
	$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$fp = fsockopen ('ssl://'.$usces_paypal_url,  443, $errno, $errstr, 30);
	$results = array();
	if (!$fp) {
		$results[0] = false;
		settlement_log('connection error PDT');
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
		$req .= '&' . $key . '=' . $value;
	}
	// post back to PayPal system to validate
	$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$fp = fsockopen ('ssl://'.$usces_paypal_url, 443, $errno, $errstr, 30);

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
