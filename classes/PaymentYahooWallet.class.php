<?php
/*
Yahoo Wallet Settlement module
Version: 1.0.0
Author: Collne Inc.

*/

class YAHOOWALLET_SETTLEMENT
{
	private $error_mes, $pay_method;
	
	public function __construct(){
	
		$this->pay_method = array(
			'acting_yahoo_wallet'
		);
	
		if( is_admin() ){
		
			add_action( 'usces_action_settlement_tab_title', array( $this, 'tab_title') );
			add_action( 'usces_action_settlement_tab_body', array( $this, 'tab_body') );
			add_action( 'usces_action_admin_settlement_update', array( $this, 'data_update') );
			add_filter( 'usces_filter_settle_info_field_keys', array( $this, 'settle_info_field_keys') );
			
		}else{
		
			add_filter( 'usces_filter_reg_orderdata_status', array( $this, 'reg_orderdata_status'), 10, 2 );
			add_action( 'usces_action_cartcompletion_page_body', array( $this, 'cartcompletion_page_body'), 10, 2 );
			add_action( 'init', array( $this, 'settlement_process') );
				


//			add_filter( 'usces_filter_confirm_inform', array( $this, 'confirm_inform'), 10, 5 );
//			add_filter( 'usces_purchase_check', array( $this, 'purchase'), 5 );
//			add_filter( 'usces_filter_check_acting_return_results', array( $this, 'acting_return') );
//			add_filter( 'usces_filter_check_acting_return_duplicate', array( $this, 'check_acting_return_duplicate'), 10, 2 );
//			add_filter( 'usces_filter_completion_settlement_message', array( $this, 'completion_settlement_message'), 10, 2 );
//			$this->noreceipt_status();
		}
	}

	/***************************************************************************/
	// 決済処理
	/***************************************************************************/
	public function settlement_process(){
		if( isset($_REQUEST['actkey']) && 'yahoo_wallet' == $_REQUEST['actkey'] ){
			$this->yahoo_settlement();
		}
		
		if( isset($_REQUEST['yahoo']) && 'conf' == $_REQUEST['yahoo'] ){
			$this->yahoo_responce();
		}
	}
	
	/***************************************************************************/
	// 未入金ステータス追加
	/***************************************************************************/
	public function reg_orderdata_status($status, $entry ){
		return 'noreceipt';
	}

	/***************************************************************************/
	// acting meta データ用キー
	/***************************************************************************/
	public function settle_info_field_keys( $keys ){
		$keys[] = 'yahoo_wallet_device';
		return $keys;
	}

	/***************************************************************************/
	// Welcart注文確定ページ
	/***************************************************************************/
	public function cartcompletion_page_body( $usces_entries, $usces_carts ){
		$payments = usces_get_payments_by_name($usces_entries['order']['payment_name']);
		if( 'acting_yahoo_wallet' != $payments['settlement'] )
			return;
			
		$query = array(
			'actkey' => 'yahoo_wallet',
			'id' => $usces_entries['order']['ID'],
			'wc_nonce' => wp_create_nonce( 'yahoo_wallet' ),
			'sett_nonce' => uniqid()
		);
		$sett_url = add_query_arg( $query, home_url());
		$message = '<div class="acting_message">
		<p>お支払い手続きに入ります。下記の「Yahoo!ウォレット決済手続きへ」をクリックしてください。<br />なおYahoo!ウォレット決済は、30分以内に「お支払い確定」を行わないと決済ができなくなりますのでご注意ください。<p>
		<p class="acting_link"><a href="' . $sett_url . '">Yahoo!ウォレット決済手続きへ</a><p>
		</div>';
		echo $message;
	}

	/***************************************************************************/
	// YahooリダイレクトURLの要求とリダイレクト
	/***************************************************************************/
	public function yahoo_settlement(){
		global $usces;
		
		$nonce = isset( $_GET['wc_nonce'] ) ? $_GET['wc_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'yahoo_wallet' ) )
			die('NG verify1');
		
		$order_id = isset( $_GET['id'] ) ? (int)$_GET['id'] : 0;
		if ( !$order_id )
			die('NG verify2');
		
		$sett_nonce = isset( $_GET['sett_nonce'] ) ? (int)$_GET['sett_nonce'] : 0;
		if ( !$sett_nonce )
			die('NG verify3');
		
		$first_nonce = $usces->get_order_meta_value('sett_nonce', $order_id);
		if( !$first_nonce && $first_nonce != $sett_nonce ){
			$usces->set_order_meta_value('sett_nonce', $sett_nonce, $order_id);
		}else{
			//die('NG verify4');
		}
		
		$options = get_option('usces');
		$order = $usces->get_order_data($order_id, 'direct' );
		if ( !$order )
			die('NG verify5');
		
		$order_done_url = home_url('/?yahoo=done');
		$date_time = date('Y-m-d\TH:i:s+09:00', current_time('timestamp')+1800);
		
		if( 'public' == $options['acting_settings']['yahoo']['ope'] ){
			$confirmations_url = str_replace( 'http://', 'https://', home_url('/?yahoo=conf') );
		}else{
			$confirmations_url = home_url('/?yahoo=conf');
		}
		
		$cart = unserialize($order['order_cart']);
		$shipping_charge = $order['order_shipping_charge'];
		$tax = $order['order_tax'];
		$usedpoint = $order['order_usedpoint'];
		$discount = $order['order_discount'];
		$cod_fee = $order['order_cod_fee'];

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<wallet_shopping_cart xmlns="urn:yahoo:jp:wallet">
		<xml_info>
		<version>1.0</version>
		</xml_info>
		<wallet_flow_support>
		<merchant_wallet_flow_support>
		<merch_id>' . $options['acting_settings']['yahoo']['merchant_id'] . '</merch_id>
		<merch_mgt_id>' . $order_id . '</merch_mgt_id>
		<ship_fee>' . usces_crform( $shipping_charge, false, false, 'return', false ) . '</ship_fee>
		<order_done_url>' . $order_done_url . '</order_done_url>
		<expire>' . $date_time . '</expire>';
		$xml .= '<order_confirmations>
		<order_confirmations_url>' . $confirmations_url . '</order_confirmations_url>
		</order_confirmations>
		';
		$xml .= '</merchant_wallet_flow_support>
		</wallet_flow_support>
		<shopping_cart>
		<items>
		';
		for( $i=0; $i<count($cart); $i++ ){
			$cart_row = $cart[$i];
			$post_id = $cart_row['post_id'];
			$sku = urldecode($cart_row['sku']);
			$quantity = $cart_row['quantity'];
			$options = $cart_row['options'];
			$itemCode = $usces->getItemCode($post_id);
			$itemName = $usces->getItemName($post_id);
			$cartItemName = $usces->getCartItemName($post_id, $sku);
			$cartItemName = str_replace('(', '（', $cartItemName);
			$cartItemName = str_replace(')', '）', $cartItemName);
			$cartItemName = str_replace('&', '＆', $cartItemName);
			$cartItemName = str_replace('!', '！', $cartItemName);
			$cartItemName = str_replace('<', '＜', $cartItemName);
			$cartItemName = str_replace('>', '＞', $cartItemName);
	//		$skus = $usces->get_skus($post_id, 'code');
	//		$cartItemName = $skus[$sku]['name'];
	//		usces_p($cartItemName);
			if( 200 < strlen($cartItemName) ){
				$cartItemName = substr($cartItemName, 0, 200) . '･･･';
			}
	//		usces_p($cartItemName);
	//		die();
			$skuPrice = $cart_row['price'];
			$xml .= '<item>
			<item_line_id>' . ($i+1) . '</item_line_id>
			<item_id>' . $itemCode . '</item_id>
			<item_name>' . $cartItemName . '</item_name>
			<item_price>' . usces_crform( $skuPrice, false, false, 'return', false ) . '</item_price>
			<item_qty>' . $quantity . '</item_qty>
			<item_tax_flg>0</item_tax_flg>
			<item_tax>0</item_tax>
			</item>
			';
		}
		$usedpoint = usces_crform( $order['order_usedpoint'], false, false, 'return', false );
		if( $usedpoint ){
			$i++;
			$xml .= '<item>
			<item_line_id>' . $i . '</item_line_id>
			<item_id>usedpoint</item_id>
			<item_name>ご利用ポイント</item_name>
			<item_price>' . ($usedpoint*(-1)) . '</item_price>
			<item_qty>1</item_qty>
			<item_tax_flg>0</item_tax_flg>
			<item_tax>0</item_tax>
			</item>
			';
		}
		$discount = usces_crform( $order['order_discount'], false, false, 'return', false );
		if( $discount ){
			$i++;
			$xml .= '<item>
			<item_line_id>' . $i . '</item_line_id>
			<item_id>discount</item_id>
			<item_name>お値引き</item_name>
			<item_price>' . $discount . '</item_price>
			<item_qty>1</item_qty>
			<item_tax_flg>0</item_tax_flg>
			<item_tax>0</item_tax>
			</item>
			';
		}
		$tax = usces_crform( $order['order_tax'], false, false, 'return', false );
		if( $tax ){
			$i++;
			$xml .= '<item>
			<item_line_id>' . $i . '</item_line_id>
			<item_id>tax</item_id>
			<item_name>消費税</item_name>
			<item_price>' . $tax . '</item_price>
			<item_qty>1</item_qty>
			<item_tax_flg>0</item_tax_flg>
			<item_tax>0</item_tax>
			</item>
			';
		}
		$cod_fee = usces_crform( $order['order_cod_fee'], false, false, 'return', false );
		if( $cod_fee ){
			$i++;
			$xml .= '<item>
			<item_line_id>' . $i . '</item_line_id>
			<item_id>cod_fee</item_id>
			<item_name>代引き手数料</item_name>
			<item_price>' . $cod_fee . '</item_price>
			<item_qty>1</item_qty>
			<item_tax_flg>0</item_tax_flg>
			<item_tax>0</item_tax>
			</item>
			';
		}
		
		$xml .= '</items>
		</shopping_cart>
		';
		
		$xml .= '</wallet_shopping_cart>';
		$res = $this->get_yahoo_xml( $xml );
		$responce = usces_xml2assoc($res);
		if( isset($responce['wallet_shopping_result']) ){
			$results = $responce['wallet_shopping_result'];
		}else{
			die('error');
		}
		if( 'true' != $results['result']['is_successful'] ){
			header( 'Content-Type: application/xml; charset=UTF-8');
			usces_p($results['error']['message']);
			usces_log('redirect_url : '.print_r($results['error']['code'], true), 'yahoo_error.log');
		}else{
			if( 'public' == $options['acting_settings']['yahoo']['ope'] ){
				$url = $results['redirect_order_url'];
			}else{
				$url = str_replace( 'https://', 'https://sandbox.', $results['redirect_order_url']);
			}
				
			wp_redirect($url);
		}
		die();
	}
	/***************************************************************************/
	// 認証付非同期通信
	/***************************************************************************/
	public function get_yahoo_xml( $paras ){
		$options = get_option('usces');
		$interface = parse_url($options['acting_settings']['yahoo']['send_url']);
		$header  = "POST " . $interface['path'] . " HTTP/1.1\r\n";
		$header .= "Host: " . $_SERVER['HTTP_HOST'] . "\r\n";
		$header .= "Authorization: Basic " . base64_encode($options['acting_settings']['yahoo']['merchant_id'] . ':' . $options['acting_settings']['yahoo']['merchant_key']) . "\r\n";
		$header .= "Accept: application/xml; charset=UTF-8\r\n";
		$header .= "Content-Type : application/xml; charset=UTF-8\r\n";
		$header .= "Content-Length: " . strlen($paras) . "\r\n";
		$header .= "Connection: close\r\n\r\n";
		$header .= $paras;
		$fp = fsockopen('ssl://'.$interface['host'],443,$errno,$errstr,30);
//		usces_log('header : '.print_r($header, true), 'acting_transaction.log');
		
		$xml = '';
		if ($fp){
			fwrite($fp, $header);
			while ( !feof($fp) ) {
				$xml .= fgets($fp, 1024);
			}
			fclose($fp);
		}
//		usces_log('get_return : '.print_r($xml, true), 'acting_transaction.log');
		
		return $xml;
	}
	/***************************************************************************/
	// Yahoo注文前確認API用レスポンス
	/***************************************************************************/
	public function yahoo_responce(){
		$options = get_option('usces');
		$flag = false;
		// リクエストヘッダ上のHTTP基本認証のユーザ名とパスワードを取得
		$user = $_SERVER['PHP_AUTH_USER'];
		$passwd = $_SERVER['PHP_AUTH_PW'];
		// HTTP基本認証を実施
		if($user !== $options['acting_settings']['yahoo']['merchant_id'] || $passwd !== $options['acting_settings']['yahoo']['merchant_key']) {
			// 認証に失敗
			header('WWW-Authenticate: Basic realm=""');
			header('Content-Type: application/xml; charset=UTF-8');
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}else{
			$flag = true;
		}
		
		// $HTTP_RAW_POST_DATAにアクセスし通知情報を取得する
		$xmlstr = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : file_get_contents("php://input");
		if(!$simplexml = new SimpleXMLElement($xmlstr)) {
			usces_log("failed to create SimpleXml object", 'acting_transaction.log');
			$flag = false;
		}else{
			global $usces, $wpdb;
			$order_id = (int)$simplexml->merch_mgt_id;
			$amount = (int)$simplexml->prices->total_price;
			$order = $usces->get_order_data($order_id, 'direct' );
			if ( !$order )
				$flag = false;
			
			$fulprice = $order['order_item_total_price'] - $order['order_usedpoint'] + $order['order_discount'] + $order['order_cod_fee'] + $order['order_shipping_charge'] + $order['order_tax'];
			if ( $fulprice != $amount )
				$flag = false;
				
			$meta_value = serialize( array( 'yahoo_wallet_device' => (int)$simplexml->device ) );
			$acting_meta = $usces->get_order_meta_value( 'acting_yahoo_wallet', $order_id );
			if( !$acting_meta ){
				usces_action_acting_getpoint( $order_id );
			}
			$usces->set_order_meta_value( 'acting_yahoo_wallet', $meta_value, $order_id );
			
			$table_name = $wpdb->prefix . "usces_order";
			$mquery = $wpdb->prepare("
			UPDATE $table_name SET order_status = 
			CASE 
				WHEN LOCATE('noreceipt', order_status) > 0 THEN REPLACE(order_status, 'noreceipt', 'receipted') 
				WHEN LOCATE('receipted', order_status) > 0 THEN order_status 
				ELSE CONCAT('receipted,', order_status ) 
			END 
			WHERE ID = %d", $order_id);
			$res = $wpdb->query( $mquery );
			
		}

		if( $flag ){
			header( 'Content-Type: application/xml; charset=UTF-8');
			header( 'HTTP/1.1 200 OK');
			header( 'Date: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); 
			header( 'Connection: close');
			$res = '<?xml version="1.0" encoding="UTF-8" ?>
			<order_confirmation_results xmlns="urn:yahoo:jp:wallet">
			<xml_info><version>1.0</version></xml_info>
			<result>
			<res_code>0</res_code>
			<details_url>' . home_url('/?yahoo=NG') . '</details_url>
			</result>
			</order_confirmation_results>';
			echo $res;
		}else{
			header('Content-Type: application/xml; charset=UTF-8');
			header('HTTP/1.1 503 Service Unavailable');
		}
		die();
	}



	/**********************************************
	* Settlement setting data update
	* @param  -
	* @return -
	***********************************************/
	public function data_update(){
		global $usces;
		
		if( 'yahoo' != $_POST['acting'])
			return;
	
		$this->error_mes = '';
		$options = get_option('usces');

		unset( $options['acting_settings']['yahoo'] );
		$options['acting_settings']['yahoo']['wallet_activate'] = isset($_POST['wallet_activate']) ? $_POST['wallet_activate'] : '';
		$options['acting_settings']['yahoo']['merchant_id'] = isset($_POST['merchant_id']) ? $_POST['merchant_id'] : '';
		$options['acting_settings']['yahoo']['merchant_key'] = isset($_POST['merchant_key']) ? $_POST['merchant_key'] : '';
		$options['acting_settings']['yahoo']['ope'] = isset($_POST['ope']) ? $_POST['ope'] : '';

		if( WCUtils::is_blank($_POST['merchant_id']) )
			$this->error_mes .= '※マーチャントIDを入力して下さい<br />';
		if( WCUtils::is_blank($_POST['merchant_key']) )
			$this->error_mes .= '※マーチャントキーを入力して下さい<br />';

		if( WCUtils::is_blank($this->error_mes) ){
			$usces->action_status = 'success';
			$usces->action_message = __('options are updated','usces');
			if( 'on' == $options['acting_settings']['yahoo']['wallet_activate'] ){
				$options['acting_settings']['yahoo']['send_url'] = 'https://api.settle.wallet.yahoo.co.jp/v1/redirect_url';
				$usces->payment_structure['acting_yahoo_wallet'] = 'ウォレット決済（Yahoo!ウォレット）';
			}else{
				unset($usces->payment_structure['acting_yahoo_wallet']);
			}
		}else{
			$usces->action_status = 'error';
			$usces->action_message = __('Data have deficiency.','usces');
			$options['acting_settings']['yahoo']['wallet_activate'] = 'off';
			unset($usces->payment_structure['acting_yahoo_wallet']);
		}
		ksort($usces->payment_structure);
		update_option('usces', $options);
		update_option('usces_payment_structure', $usces->payment_structure);
	}

	/**********************************************
	* Settlement setting page tab title
	* @param  -
	* @return -
	* @echo   str
	***********************************************/
	public function tab_title(){
		echo '<li><a href="#uscestabs_yahoo">Yahoo!ウォレット</a></li>';
	}

	/**********************************************
	* Settlement setting page tab body
	* @param  -
	* @return -
	* @echo   str
	***********************************************/
	public function tab_body(){
		global $usces;
		$opts = $usces->options['acting_settings'];
?>
	<div id="uscestabs_yahoo">
	<div class="settlement_service"><span class="service_title">Yahoo!ウォレット決済</span></div>

	<?php if( isset($_POST['acting']) && 'yahoo' == $_POST['acting'] ){ ?>
		<?php if( '' != $this->error_mes ){ ?>
		<div class="error_message"><?php echo $this->error_mes; ?></div>
		<?php }else if( isset($opts['yahoo']['activate']) && 'on' == $opts['yahoo']['activate'] ){ ?>
		<div class="message">十分にテストを行ってから運用してください。</div>
		<?php } ?>
	<?php } ?>
	<form action="" method="post" name="yahoo_form" id="yahoo_form">
		<table class="settle_table">
			<tr>
				<th>ウォレット決済の利用</th>
				<td><input name="wallet_activate" type="radio" id="wallet_activate_yahoo_1" value="on"<?php if( isset($opts['yahoo']['wallet_activate']) && $opts['yahoo']['wallet_activate'] == 'on' ) echo ' checked="checked"'; ?> /></td><td><label for="wallet_activate_yahoo_1">利用する</label></td>
				<td><input name="wallet_activate" type="radio" id="wallet_activate_yahoo_2" value="off"<?php if( isset($opts['yahoo']['wallet_activate']) && $opts['yahoo']['wallet_activate'] == 'off' ) echo ' checked="checked"'; ?> /></td><td><label for="wallet_activate_yahoo_2">利用しない</label></td>
				<td></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_merchant_id_yahoo');">マーチャントID</a></th>
				<td colspan="6"><input name="merchant_id" type="text" id="merchant_id_yahoo" value="<?php echo esc_html(isset($opts['yahoo']['merchant_id']) ? $opts['yahoo']['merchant_id'] : ''); ?>" size="20" maxlength="5" /></td>
				<td><div id="ex_merchant_id_yahoo" class="explanation"><?php _e('契約時にYahoo! JAPANから発行されるマーチャントID（半角数字）', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_merchant_key_yahoo');">マーチャントキー</a></th>
				<td colspan="6"><input name="merchant_key" type="password" id="merchant_key_yahoo" value="<?php echo esc_html(isset($opts['yahoo']['merchant_key']) ? $opts['yahoo']['merchant_key'] : ''); ?>" size="50" maxlength="40" /></td>
				<td><div id="ex_merchant_key_yahoo" class="explanation"><?php _e('契約時にYahoo! JAPANから発行される マーチャントキー（半角英数）', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_ope_yahoo');"><?php _e('Operation Environment', 'usces'); ?></a></th>
				<td><input name="ope" type="radio" id="ope_yahoo_1" value="test"<?php if( isset($opts['yahoo']['ope']) && $opts['yahoo']['ope'] == 'test' ) echo ' checked="checked"'; ?> /></td><td><label for="ope_yahoo_1">テスト環境</label></td>
				<td><input name="ope" type="radio" id="ope_yahoo_2" value="public"<?php if( isset($opts['yahoo']['ope']) && $opts['yahoo']['ope'] == 'public' ) echo ' checked="checked"'; ?> /></td><td><label for="ope_yahoo_2">本番環境</label></td>
				<td><div id="ex_ope_yahoo" class="explanation"><?php _e('動作環境を切り替えます。', 'usces'); ?></div></td>
			</tr>
		</table>
		<input name="acting" type="hidden" value="yahoo" />
		<input name="usces_option_update" type="submit" class="button" value="Yahoo!ウォレットの設定を更新する" />
		<?php wp_nonce_field( 'admin_settlement', 'wc_nonce' ); ?>
	</form>
	<div class="settle_exp">
		<p><strong>Yahoo!ウォレット決済</strong></p>
		<a href="http://wallet.yahoo.co.jp/about/" target="_blank">Yahoo!ウォレット決済の詳細はこちら 》</a>
		<p>　</p>
		<p>この決済は「外部リンク型」の決済システムです。</p>
		<p>「外部リンク型」とは、決済会社のページへ遷移してカード情報を入力する決済システムです。</p>
		<p>尚、本番環境では、正規SSL証明書のみでのSSL通信となりますのでご注意ください。</p>
	</div>
	</div><!--uscestabs_yahoo-->
<?php
	}



}
