<?php
/*
DSK Settlement module
Version: 1.0.0
Author: Collne Inc.
*/

class DSK_SETTLEMENT
{
	private $error_mes, $pay_method;
	
	public function __construct(){
	
		$this->pay_method = array(
			'acting_dsk_card',
			'acting_dsk_conv',
			'acting_dsk_payeasy'
		);
	
		if( is_admin() ){
		
			add_action( 'usces_action_settlement_tab_title', array( $this, 'tab_title') );
			add_action( 'usces_action_settlement_tab_body', array( $this, 'tab_body') );
			add_action( 'usces_action_admin_settlement_update', array( $this, 'data_update') );
			
		}else{
		
			add_filter( 'usces_filter_confirm_inform', array( $this, 'confirm_inform'), 10, 5 );
			add_filter( 'usces_purchase_check', array( $this, 'purchase'), 5 );
			add_filter( 'usces_filter_check_acting_return_results', array( $this, 'acting_return') );
			add_filter( 'usces_filter_check_acting_return_duplicate', array( $this, 'check_acting_return_duplicate'), 10, 2 );
			add_filter( 'usces_filter_completion_settlement_message', array( $this, 'completion_settlement_message'), 10, 2 );
			$this->noreceipt_status();
		}
	}

	/**********************************************
	* usces_filter_noreceipt_status
	* @param  $actings
	* @return array $actings
	***********************************************/
	public function noreceipt_status(){
		$noreceipt_status = get_option( 'usces_noreceipt_status' );
		$noreceipt_status[] = 'acting_dsk_conv';
		$noreceipt_status[] = 'acting_dsk_payeasy';
		update_option( 'usces_noreceipt_status', $noreceipt_status );
	}

	/**********************************************
	* usces_filter_completion_settlement_message
	* @param  $html, $usces_entries
	* @return str $html
	***********************************************/
	public function completion_settlement_message( $html, $usces_entries ){
			
		if( isset($_REQUEST['acting']) && ( 'dsk_conv' == $_REQUEST['acting'] || 'dsk_payeasy' == $_REQUEST['acting'] ) ){ 
			$title = ( 'dsk_conv' == $_REQUEST['acting'] ) ? 'コンビニ決済' : 'ペイジー決済';
			$html .= '<div id="status_table"><h5>DSKペイメント・'.$title.'</h5>'."\n";
			$html .= '<p>「お支払いのご案内」は、' . esc_html($usces_entries['customer']['mailaddress1']) . '　宛にメールさせていただいております。</p>'."\n";
			$html .= "</div>\n";
		}
		return $html;
	}

	/**********************************************
	* usces_filter_check_acting_return_duplicate
	* @param  $tid, $results
	* @return str RandId
	***********************************************/
	public function check_acting_return_duplicate( $tid, $results ){
			
		if( isset($results['acting']) && in_array( 'acting_'.$results['acting'], $this->pay_method) )
			return $_REQUEST['order_id'];
		else	
			return $tid;
	}

	/**********************************************
	* Acting return
	* @param  $results
	* @return array
	***********************************************/
	public function acting_return( $results ){
	
		if( !in_array( 'acting_'.$results['acting'], $this->pay_method) )
			return $results;
		
		if( isset($_REQUEST['cancel']) ) {
			$results[0] = 0;
			$results['reg_order'] = false;

		} else {
			if( isset($_REQUEST['res_result']) and 'OK' == $_REQUEST['res_result'] ) {
				$results[0] = 1;
			} else {
				//usces_log($acting.' error : '.print_r($_REQUEST,true), 'acting_transaction.log');
				$results[0] = 0;
			}
			$results['reg_order'] = false;
		}
		
		return $results;
	}

	/**********************************************
	* Order Purchase
	* @param  no use
	* @return allways false
	***********************************************/
	public function purchase( $nouse ){
		global $usces;
		
		$usces_entries = $usces->cart->get_entry();
		$cart = $usces->cart->get_cart();
		if( !$usces_entries || !$cart)
			wp_redirect(USCES_CART_URL);
			
		$payments = usces_get_payments_by_name($usces_entries['order']['payment_name']);
		$acting_flag = ( 'acting' == $payments['settlement'] ) ? $payments['module'] : $payments['settlement'];
		if( !in_array($acting_flag, $this->pay_method) )
			return true;

		if( !wp_verify_nonce( $_REQUEST['_nonce'], $acting_flag ) )
			wp_redirect(USCES_CART_URL);
		
		$rand = $_REQUEST['rand'];
		$html = '';
		$charging_type = $usces->getItemChargingType($cart[0]['post_id']);
		$frequency = $usces->getItemFrequency($cart[0]['post_id']);
		$chargingday = $usces->getItemChargingDay($cart[0]['post_id']);
		$acting_opts = $usces->options['acting_settings']['dsk'];
		$usces->save_order_acting_data($rand);
		$member = $usces->get_member();
		$cust_code = ( empty($member['ID']) ) ? str_replace('-', '', mb_convert_kana($usces_entries['customer']['tel'], 'a', 'UTF-8')) : $member['ID'];
		if( 'public' == $acting_opts['ope'] ) {
			$send_url = $acting_opts['send_url'];
		} elseif( 'test' == $acting_opts['ope'] ) {
			$send_url = $acting_opts['send_url_test'];
		} else {
			$send_url = $acting_opts['send_url_check'];
		}
		$dsk_cust_no = '';
		$dsk_payment_no = '';
		switch( $acting_flag ) {
		case 'acting_dsk_card':
			$pay_method = ( 'on' == $acting_opts['3d_secure'] ) ? "credit3d" : "credit";
			$acting = "dsk_card";
			$free_csv = "";
			break;
		case 'acting_dsk_conv':
			$pay_method = "webcvs";
			$acting = "dsk_conv";
			$free_csv = usces_set_free_csv( $usces_entries['customer'] );
			break;
		case 'acting_dsk_payeasy':
			$pay_method = "payeasy";
			$acting = "dsk_payeasy";
			$free_csv = usces_set_free_csv( $usces_entries['customer'] );
			break;
		}
		$item_id = $cart[0]['post_id'];
		$item_name = $usces->getItemName($cart[0]['post_id']);
		if(1 < count($cart)) $item_name .= ' '.__('Others', 'usces');
		if(36 < mb_strlen($item_name)) $item_name = mb_substr($item_name, 0, 30).'...';
//		$item_name = mb_convert_encoding($item_name, 'SJIS', 'UTF-8');
		$amount = usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false);
		$pay_type = "0";
		$auto_charge_type = "";
		$service_type = "0";
		$div_settle = "";
		$last_charge_month = "";
		$camp_type = "";
		$terminal_type = "0";
		$success_url = USCES_CART_URL.$usces->delim."acting=".$acting."&acting_return=1&result=1";
		$cancel_url = USCES_CART_URL.$usces->delim."acting=".$acting."&confirm=1";
		$error_url = USCES_CART_URL.$usces->delim."acting=".$acting."&acting_return=0";
		$pagecon_url = USCES_CART_URL;
		$free1 = $acting_flag;
		$request_date = date('YmdHis', current_time('timestamp'));
		$limit_second = "600";
		$sps_hashcode = $pay_method.$acting_opts['merchant_id'].$acting_opts['service_id'].$cust_code.$dsk_cust_no.$dsk_payment_no.$rand.$item_id.$item_name.$amount.$pay_type.$auto_charge_type.$service_type.$div_settle.$last_charge_month.$camp_type.$terminal_type.$success_url.$cancel_url.$error_url.$pagecon_url.$free1.$free_csv.$request_date.$limit_second.$acting_opts['hash_key'];
		$sps_hashcode = sha1( $sps_hashcode );
		$html .= '<HTML>
			<HEAD>
			<META http-equiv="Content-Type" content="text/html; charset=shift_jis">
			</HEAD>
			<BODY>
			<form id="purchase_form" name="purchase_form" action="'.$send_url.'" method="post" accept-charset="Shift_JIS">
			<input type="hidden" name="pay_method" value="'.$pay_method.'" />
			<input type="hidden" name="merchant_id" value="'.$acting_opts['merchant_id'].'" />
			<input type="hidden" name="service_id" value="'.$acting_opts['service_id'].'" />
			<input type="hidden" name="cust_code" value="'.$cust_code.'" />
			<input type="hidden" name="sps_cust_no" value="'.$dsk_cust_no.'" />
			<input type="hidden" name="sps_payment_no" value="'.$dsk_payment_no.'" />
			<input type="hidden" name="order_id" value="'.$rand.'" />
			<input type="hidden" name="item_id" value="'.$item_id.'" />
			<input type="hidden" name="pay_item_id" value="" />
			<input type="hidden" name="item_name" value="'.mb_convert_encoding($item_name, 'SJIS', 'UTF-8').'" />
			<input type="hidden" name="tax" value="" />
			<input type="hidden" name="amount" value="'.$amount.'" />
			<input type="hidden" name="pay_type" value="'.$pay_type.'" />
			<input type="hidden" name="auto_charge_type" value="'.$auto_charge_type.'" />
			<input type="hidden" name="service_type" value="'.$service_type.'" />
			<input type="hidden" name="div_settle" value="'.$div_settle.'" />
			<input type="hidden" name="last_charge_month" value="'.$last_charge_month.'" />
			<input type="hidden" name="camp_type" value="'.$camp_type.'" />
			<input type="hidden" name="terminal_type" value="'.$terminal_type.'" />
			<input type="hidden" name="success_url" value="'.$success_url.'" />
			<input type="hidden" name="cancel_url" value="'.$cancel_url.'" />
			<input type="hidden" name="error_url" value="'.$error_url.'" />
			<input type="hidden" name="pagecon_url" value="'.$pagecon_url.'" />
			<input type="hidden" name="free1" value="'.$free1.'" />
			<input type="hidden" name="free2" value="" />
			<input type="hidden" name="free3" value="" />
			<input type="hidden" name="free_csv" value="'.$free_csv.'" />
			<input type="hidden" name="request_date" value="'.$request_date.'" />
			<input type="hidden" name="limit_second" value="'.$limit_second.'" />
			<input type="hidden" name="sps_hashcode" value="'.$sps_hashcode.'" />
			<input type="hidden" name="dummy" value="&#65533;" />
			</form>
			<script type="text/javascript">
				document.purchase_form.submit();
			</script>
			</BODY>
			</HTML>';

		echo $html;


//		usces_p($usces_entries);
//		usces_p($_POST);
		exit;
		return false;
	}
	
	/**********************************************
	* Order purchase form
	* @param  $html, $payments, $acting_flag, $rand, $purchase_disabled
	* @return form str
	***********************************************/
	public function confirm_inform( $html, $payments, $acting_flag, $rand, $purchase_disabled ){
		if( in_array($acting_flag, $this->pay_method) ){
			$html = '<form id="purchase_form" action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" value="'.__('Back', 'usces').'"'.apply_filters('usces_filter_confirm_prebutton', NULL).' />
				<input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"'.$purchase_disabled.' /></div>
				<input type="hidden" name="rand" value="'.$rand.'">
				<input type="hidden" name="_nonce" value="'.wp_create_nonce( $acting_flag ).'">' . "\n";
		}
		return $html;
	}

	/**********************************************
	* Settlement setting data update
	* @param  -
	* @return -
	***********************************************/
	public function data_update(){
		global $usces;
		
		if( 'dsk' != $_POST['acting'])
			return;
	
		$this->error_mes = '';
		$options = get_option('usces');

		unset( $options['acting_settings']['dsk'] );
		$options['acting_settings']['dsk']['merchant_id'] = isset($_POST['merchant_id']) ? $_POST['merchant_id'] : '';
		$options['acting_settings']['dsk']['service_id'] = isset($_POST['service_id']) ? $_POST['service_id'] : '';
		$options['acting_settings']['dsk']['hash_key'] = isset($_POST['hash_key']) ? $_POST['hash_key'] : '';
		$options['acting_settings']['dsk']['ope'] = isset($_POST['ope']) ? $_POST['ope'] : '';
		$options['acting_settings']['dsk']['send_url_check'] = isset($_POST['send_url_check']) ? $_POST['send_url_check'] : '';
		$options['acting_settings']['dsk']['send_url_test'] = isset($_POST['send_url_test']) ? $_POST['send_url_test'] : '';
		$options['acting_settings']['dsk']['card_activate'] = isset($_POST['card_activate']) ? $_POST['card_activate'] : '';
		$options['acting_settings']['dsk']['3d_secure'] = isset($_POST['3d_secure']) ? $_POST['3d_secure'] : '';
		$options['acting_settings']['dsk']['cust'] = isset($_POST['cust']) ? $_POST['cust'] : '';
		$options['acting_settings']['dsk']['continuation'] = isset($_POST['continuation']) ? $_POST['continuation'] : '';
		$options['acting_settings']['dsk']['conv_activate'] = isset($_POST['conv_activate']) ? $_POST['conv_activate'] : '';
		$options['acting_settings']['dsk']['payeasy_activate'] = isset($_POST['payeasy_activate']) ? $_POST['payeasy_activate'] : '';

		if( WCUtils::is_blank($_POST['merchant_id']) )
			$this->error_mes .= '※マーチャントIDを入力して下さい<br />';
		if( WCUtils::is_blank($_POST['service_id']) )
			$this->error_mes .= '※サービスIDを入力して下さい<br />';
		if( WCUtils::is_blank($_POST['hash_key']) )
			$this->error_mes .= '※Hash KEYを入力して下さい<br />';

		if( WCUtils::is_blank($this->error_mes) ){
			$usces->action_status = 'success';
			$usces->action_message = __('options are updated','usces');
			$options['acting_settings']['dsk']['activate'] = 'on';
			if( isset($_POST['ope']) && 'public' == $_POST['ope'] ) {
				$options['acting_settings']['dsk']['send_url'] = 'https://fep.sps-system.com/f01/FepBuyInfoReceive.do';
			}
			if( 'on' == $options['acting_settings']['dsk']['card_activate'] ){
				$usces->payment_structure['acting_dsk_card'] = 'カード決済（DSKペイメント）';
			}else{
				unset($usces->payment_structure['acting_dsk_card']);
			}
			if( 'on' == $options['acting_settings']['dsk']['conv_activate'] ){
				$usces->payment_structure['acting_dsk_conv'] = 'コンビニ決済（DSKペイメント）';
			}else{
				unset($usces->payment_structure['acting_dsk_conv']);
			}
			if( 'on' == $options['acting_settings']['dsk']['payeasy_activate'] ){
				$usces->payment_structure['acting_dsk_payeasy'] = 'ペイジー決済（DSKペイメント）';
			}else{
				unset($usces->payment_structure['acting_dsk_payeasy']);
			}
		}else{
			$usces->action_status = 'error';
			$usces->action_message = __('Data have deficiency.','usces');
			$options['acting_settings']['dsk']['activate'] = 'off';
			unset($usces->payment_structure['acting_dsk_card']);
			unset($usces->payment_structure['acting_dsk_conv']);
			unset($usces->payment_structure['acting_dsk_payeasy']);
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
		echo '<li><a href="#uscestabs_dsk">DSKペイメント</a></li>';
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
	<div id="uscestabs_dsk">
	<div class="settlement_service"><span class="service_title">DSK・ペイメント・サービス</span></div>

	<?php if( isset($_POST['acting']) && 'dsk' == $_POST['acting'] ){ ?>
		<?php if( '' != $this->error_mes ){ ?>
		<div class="error_message"><?php echo $this->error_mes; ?></div>
		<?php }else if( isset($opts['dsk']['activate']) && 'on' == $opts['dsk']['activate'] ){ ?>
		<div class="message">十分にテストを行ってから運用してください。</div>
		<?php } ?>
	<?php } ?>
	<form action="" method="post" name="dsk_form" id="dsk_form">
		<table class="settle_table">
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_merchant_id_dsk');">マーチャントID</a></th>
				<td colspan="6"><input name="merchant_id" type="text" id="merchant_id_dsk" value="<?php echo esc_html(isset($opts['dsk']['merchant_id']) ? $opts['dsk']['merchant_id'] : ''); ?>" size="20" maxlength="5" /></td>
				<td><div id="ex_merchant_id_dsk" class="explanation"><?php _e('契約時にDSKペイメント・サービスから発行されるマーチャントID（半角数字）', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_service_id_dsk');">サービスID</a></th>
				<td colspan="6"><input name="service_id" type="text" id="service_id_dsk" value="<?php echo esc_html(isset($opts['dsk']['service_id']) ? $opts['dsk']['service_id'] : ''); ?>" size="20" maxlength="3" /></td>
				<td><div id="ex_service_id_dsk" class="explanation"><?php _e('契約時にDSKペイメント・サービスから発行されるサービスID（半角数字）', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_hash_key_dsk');">Hash KEY</a></th>
				<td colspan="6"><input name="hash_key" type="text" id="hash_key_dsk" value="<?php echo esc_html(isset($opts['dsk']['hash_key']) ? $opts['dsk']['hash_key'] : ''); ?>" size="50" maxlength="40" /></td>
				<td><div id="ex_hash_key_dsk" class="explanation"><?php _e('契約時にDSKペイメント・サービスから発行される Hash KEY（半角英数）', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_ope_dsk');"><?php _e('Operation Environment', 'usces'); ?></a></th>
				<td><input name="ope" type="radio" id="ope_dsk_1" value="check"<?php if( isset($opts['dsk']['ope']) && $opts['dsk']['ope'] == 'check' ) echo ' checked="checked"'; ?> /></td><td><label for="ope_dsk_1">接続支援サイト</label></td>
				<td><input name="ope" type="radio" id="ope_dsk_2" value="test"<?php if( isset($opts['dsk']['ope']) && $opts['dsk']['ope'] == 'test' ) echo ' checked="checked"'; ?> /></td><td><label for="ope_dsk_2">テスト環境</label></td>
				<td><input name="ope" type="radio" id="ope_dsk_3" value="public"<?php if( isset($opts['dsk']['ope']) && $opts['dsk']['ope'] == 'public' ) echo ' checked="checked"'; ?> /></td><td><label for="ope_dsk_3">本番環境</label></td>
				<td><div id="ex_ope_dsk" class="explanation"><?php _e('動作環境を切り替えます。', 'usces'); ?></div></td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th>クレジットカード決済</th>
				<td><input name="card_activate" type="radio" id="card_activate_dsk_1" value="on"<?php if( isset($opts['dsk']['card_activate']) && $opts['dsk']['card_activate'] == 'on' ) echo ' checked="checked"'; ?> /></td><td><label for="card_activate_dsk_1">利用する</label></td>
				<td><input name="card_activate" type="radio" id="card_activate_dsk_2" value="off"<?php if( isset($opts['dsk']['card_activate']) && $opts['dsk']['card_activate'] == 'off' ) echo ' checked="checked"'; ?> /></td><td><label for="card_activate_dsk_2">利用しない</label></td>
				<td></td>
			</tr>
			<tr>
				<th>3Dセキュア</th>
				<td><input name="3d_secure" type="radio" id="3d_secure_dsk_1" value="on"<?php if( isset($opts['dsk']['3d_secure']) && $opts['dsk']['3d_secure'] == 'on' ) echo ' checked="checked"'; ?> /></td><td><label for="3d_secure_dsk_1">利用する</label></td>
				<td><input name="3d_secure" type="radio" id="3d_secure_dsk_2" value="off"<?php if( isset($opts['dsk']['3d_secure']) && $opts['dsk']['3d_secure'] == 'off' ) echo ' checked="checked"'; ?> /></td><td><label for="3d_secure_dsk_2">利用しない</label></td>
				<td></td>
			</tr>
			<?php if( defined('WCEX_DLSELLER') ): ?>
<!--			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_continuation_dsk');">簡易継続課金</a></th>
				<td><input name="continuation" type="radio" id="continuation_dsk_1" value="on"<?php if( isset($opts['dsk']['continuation']) && $opts['dsk']['continuation'] == 'on' ) echo ' checked="checked"'; ?> /></td><td><label for="continuation_dsk_1">利用する</label></td>
				<td><input name="continuation" type="radio" id="continuation_dsk_2" value="off"<?php if( isset($opts['dsk']['continuation']) && $opts['dsk']['continuation'] == 'off' ) echo ' checked="checked"'; ?> /></td><td><label for="continuation_dsk_2">利用しない</label></td>
				<td><div id="ex_continuation_dsk" class="explanation"><?php _e('定期的に発生する月会費などの煩わしい課金処理を完全に自動化することができる機能です。<br />詳しくはDSKペイメント・サービスにお問合せください。', 'usces'); ?></div></td>
			</tr>
-->			<?php endif; ?>
		</table>
		<table class="settle_table">
			<tr>
				<th>WEBコンビニ決済</th>
				<td><input name="conv_activate" type="radio" id="conv_activate_dsk_1" value="on"<?php if( isset($opts['dsk']['conv_activate']) && $opts['dsk']['conv_activate'] == 'on' ) echo ' checked="checked"'; ?> /></td><td><label for="conv_activate_dsk_1">利用する</label></td>
				<td><input name="conv_activate" type="radio" id="conv_activate_dsk_2" value="off"<?php if( isset($opts['dsk']['conv_activate']) && $opts['dsk']['conv_activate'] == 'off' ) echo ' checked="checked"'; ?> /></td><td><label for="conv_activate_dsk_2">利用しない</label></td>
				<td></td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th>Pay-easy（ペイジー）決済</th>
				<td><input name="payeasy_activate" type="radio" id="payeasy_activate_dsk_1" value="on"<?php if( isset($opts['dsk']['payeasy_activate']) && $opts['dsk']['payeasy_activate'] == 'on' ) echo ' checked="checked"'; ?> /></td><td><label for="payeasy_activate_dsk_1">利用する</label></td>
				<td><input name="payeasy_activate" type="radio" id="payeasy_activate_dsk_2" value="off"<?php if( isset($opts['dsk']['payeasy_activate']) && $opts['dsk']['payeasy_activate'] == 'off' ) echo ' checked="checked"'; ?> /></td><td><label for="payeasy_activate_dsk_2">利用しない</label></td>
				<td></td>
			</tr>
		</table>
		</table>
		<input name="send_url_check" type="hidden" value="https://stbfep.sps-system.com/Extra/BuyRequestAction.do" />
		<input name="send_url_test" type="hidden" value="https://stbfep.sps-system.com/f01/FepBuyInfoReceive.do" />
		<input name="acting" type="hidden" value="dsk" />
		<input name="usces_option_update" type="submit" class="button" value="DSKペイメントの設定を更新する" />
		<?php wp_nonce_field( 'admin_settlement', 'wc_nonce' ); ?>
	</form>
	<div class="settle_exp">
		<p><strong>DSKペイメント・サービス</strong></p>
		<!--<a href="http://www.welcart.com/wc-settlement/dsk_guide/" target="_blank">DSKペイメント・サービスの詳細はこちら 》</a>-->
		<p>　</p>
		<p>この決済は「外部リンク型」の決済システムです。</p>
		<p>「外部リンク型」とは、決済会社のページへ遷移してカード情報を入力する決済システムです。</p>
		<!--<p>「簡易継続課金」を利用するには「DL Seller」拡張プラグインのインストールが必要です。</p>-->
		<p>尚、本番環境では、正規SSL証明書のみでのSSL通信となりますのでご注意ください。</p>
	</div>
	</div><!--uscestabs_dsk-->
<?php
	}


}

