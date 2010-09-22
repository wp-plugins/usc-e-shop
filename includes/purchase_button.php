<?php
$payments = usces_get_payments_by_name($usces_entries['order']['payment_name']);
$rand = sprintf('%010d', mt_rand(1, 9999999999));


if( 'acting' != substr($payments['settlement'], 0, 6)  || 0 == $usces_entries['order']['total_full_price'] ){
	$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
		<div class="send"><input name="backDelivery" type="submit" value="'.__('Back to payment method page.', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' />&nbsp;&nbsp;
		<input name="purchase" type="submit" value="'.__('Checkout', 'usces').'"' . apply_filters('usces_filter_confirm_nextbutton', NULL) . ' /></div>';
	$html = apply_filters('usces_filter_confirm_inform', $html);
	$html .= '</form>';
}else{
	//$notify_url = urlencode(USCES_CART_URL . '&purchase');
	$send_item_code = $this->getItemCode($cart[0]['post_id']);
	$send_item_name = $this->getItemName($cart[0]['post_id']);
	if( count($cart) > 1 ) $send_item_name .= ' '.__('Others', 'usces');
	$scheme = ( $this->use_ssl ) ? 'https://' : 'http://';
	
	$acting_flag = ( 'acting' == $payments['settlement'] ) ? $payments['module'] : $payments['settlement'];
	switch( $acting_flag ){
	
		case 'paypal.php':
			require_once($this->options['settlement_path'] . "paypal.php");
			$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' />&nbsp;&nbsp;</div>';
			$html = apply_filters('usces_filter_confirm_inform', $html);
			$html .= '</form>
				<form action="https://' . $usces_paypal_url . '/cgi-bin/webscr" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="' . $usces_paypal_business . '">
				<input type="hidden" name="custom" value="' . $this->get_uscesid(false) . '">
				<input type="hidden" name="lc" value="JP">';
			if( 1 < count($cart) ) {
				$html .= '<input type="hidden" name="item_name" value="' . $send_item_name . __('and others', 'usces') . '">';
			}else{
				$html .= '<input type="hidden" name="item_name" value="' . $send_item_name . '">';
			}
			$html .= '<input type="hidden" name="item_number" value="">
				<input type="hidden" name="amount" value="' . $usces_entries['order']['total_full_price'] . '">
				<input type="hidden" name="currency_code" value="JPY">
				<input type="hidden" name="cancel_return" value="' . USCES_CART_URL . '&confirm">
				<input type="hidden" name="notify_url" value="' . USCES_CART_URL . '&acting_return=paypal_ipn&usces=' . $this->get_uscesid(false) . '">
				<input type="hidden" name="button_subtype" value="products">
				<input type="hidden" name="tax_rate" value="0.000">
				<input type="hidden" name="shipping" value="0">
				<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHostedGuest">
				<div class="send"><input type="image" src="https://www.paypal.com/' . ( USCES_JP ? 'ja_JP/JP' : 'en_US' ) . '/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal"' . apply_filters('usces_filter_confirm_nextbutton', NULL) . ' />
				<img alt="" border="0" src="https://www.paypal.com/' . ( USCES_JP ? 'ja_JP' : 'en_US' ) . '/i/scr/pixel.gif" width="1" height="1"></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html);
			$html .= '</form>';
			break;
			
		case 'epsilon.php':
			$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<input type="hidden" name="user_id" value="' . $memid . '">
				<input type="hidden" name="user_name" value="' . $usces_entries['customer']['name1'] . ' ' . $usces_entries['customer']['name2'] . '">
				<input type="hidden" name="user_mail_add" value="' . $usces_entries['customer']['mailaddress1'] . '">';
			if( 1 < count($cart) ) {
				$html .= '<input type="hidden" name="item_code" value="99999999">
					<input type="hidden" name="item_name" value="' . substr($send_item_name, 0, 50) . ' ' . __('and others', 'usces') . '">';
			}else{
				$html .= '<input type="hidden" name="item_code" value="' . $send_item_code . '">
					<input type="hidden" name="item_name" value="' . substr($send_item_name, 0, 64) . '">';
			}
			$html .= '<input type="hidden" name="item_price" value="' . $usces_entries['order']['total_full_price'] . '">
				<div class="send"><input name="backDelivery" type="submit" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' />&nbsp;&nbsp;
				<input name="purchase" type="submit" value="'.__('Checkout', 'usces').'"' . apply_filters('usces_filter_confirm_nextbutton', NULL) . ' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html);
			$html .= '</form>';
			break;
			
		case 'acting_zeus_card':
			$acting_opts = $this->options['acting_settings']['zeus'];
			$this->save_order_acting_data($rand);
			$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">';
			$member = $this->get_member();
			$pcid = $this->get_member_meta_value('zeus_pcid', $member['ID']);
			if( $pcid == '8888888888888888' && $this->is_member_logged_in() ){
				$html .= '<input type="hidden" name="cardnumber" value="8888888888888888">';
				$html .= '<input type="hidden" name="expyy" value="' . $_POST['expyy'] . '">
					<input type="hidden" name="expmm" value="' . $_POST['expmm'] . '">';
			}else{
				$html .= '<input type="hidden" name="cardnumber" value="' . esc_html($_POST['cnum1']) . esc_html($_POST['cnum2']) . esc_html($_POST['cnum3']) . esc_html($_POST['cnum4']) . '">';
				$html .= '<input type="hidden" name="expyy" value="' . $_POST['expyy'] . '">
					<input type="hidden" name="expmm" value="' . $_POST['expmm'] . '">';
			}
			$html .= '<input type="hidden" name="telno" value="' . str_replace('-', '', $usces_entries['customer']['tel']) . '">
				<input type="hidden" name="email" value="' . $usces_entries['customer']['mailaddress1'] . '">
				<input type="hidden" name="sendid" value="' . $memid . '">
				<input type="hidden" name="username" value="' . esc_html($_POST['username']) . '">
				<input type="hidden" name="money" value="' . $usces_entries['order']['total_full_price'] . '">
				<input type="hidden" name="sendpoint" value="' . $rand . '">
				<input type="hidden" name="printord" value="yes">';
			if( isset($_POST['howpay']) && '0' === $_POST['howpay'] ){	
				$html .= '<input type="hidden" name="howpay" value="' . $_POST['howpay'] . '">';
				$html .= '<input type="hidden" name="cbrand" value="' . $_POST['cbrand'] . '">';
				$div_name = 'div_' . $_POST['cbrand'];
				$html .= '<input type="hidden" name="div" value="' . $_POST[$div_name] . '">';
				$html .= '<input type="hidden" name="div_1" value="' . $_POST['div_1'] . '">';
				$html .= '<input type="hidden" name="div_2" value="' . $_POST['div_2'] . '">';
				$html .= '<input type="hidden" name="div_3" value="' . $_POST['div_3'] . '">';
			}
			$html .= '
				<input type="hidden" name="cnum1" value="' . esc_html($_POST['cnum1']) . '">
				<input type="hidden" name="cnum2" value="' . esc_html($_POST['cnum2']) . '">
				<input type="hidden" name="cnum3" value="' . esc_html($_POST['cnum3']) . '">
				<input type="hidden" name="cnum4" value="' . esc_html($_POST['cnum4']) . '">
				<div class="send"><input name="backDelivery" type="submit" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' />&nbsp;&nbsp;
				<input name="purchase" type="submit" value="'.__('Checkout', 'usces').'"' . apply_filters('usces_filter_confirm_nextbutton', NULL) . ' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html);
			$html .= '</form>';
			break;
			
		case 'acting_zeus_conv':
			$acting_opts = $this->options['acting_settings']['zeus'];
			$this->save_order_acting_data($rand);
			$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">';
			$html .= '
				<input type="hidden" name="act" value="secure_order">
				<input type="hidden" name="money" value="' . $usces_entries['order']['total_full_price'] . '">
				<input type="hidden" name="username" value="' . trim($usces_entries['customer']['name3']) . trim($usces_entries['customer']['name4']) . '">
				<input type="hidden" name="telno" value="' . str_replace('-', '', $usces_entries['customer']['tel']) . '">
				<input type="hidden" name="email" value="' . $usces_entries['customer']['mailaddress1'] . '">
				<input type="hidden" name="pay_cvs" value="' . $_POST['pay_cvs'] . '">
				<input type="hidden" name="sendid" value="' . $memid . '">
				<input type="hidden" name="sendpoint" value="' . $rand . '">';
			if( '' != $acting_opts['testid_conv'] ){	
				$html .= '<input type="hidden" name="testid" value="' . $acting_opts['testid_conv'] . '">';
				$html .= '<input type="hidden" name="test_type" value="' . $acting_opts['test_type_conv'] . '">';
			}
			$html .= '
				<div class="send"><input name="backDelivery" type="submit" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' />&nbsp;&nbsp;
				<input name="purchase" type="submit" value="'.__('Checkout', 'usces').'"' . apply_filters('usces_filter_confirm_nextbutton', NULL) . ' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html);
			$html .= '</form>';
			break;
			
		case 'acting_zeus_bank':
			$acting_opts = $this->options['acting_settings']['zeus'];
			$this->save_order_acting_data($rand);
			$html .= '<form action="' . $acting_opts['bank_url'] . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}" accept-charset="Shift_JIS">';
			$html .= '
				<input type="hidden" name="clientip" value="' . $acting_opts['clientip_bank'] . '">
				<input type="hidden" name="act" value="order">
				<input type="hidden" name="money" value="' . $usces_entries['order']['total_full_price'] . '">';
			if( '' != $acting_opts['testid_bank'] ){	
				$html .= '<input type="hidden" name="username" value="' . trim($usces_entries['customer']['name3']) . trim($usces_entries['customer']['name4']) . '_' . $acting_opts['testid_bank'] . '">';
				$html .= '<input type="hidden" name="telno" value="99999999999">';
			}else{
				$html .= '<input type="hidden" name="username" value="' . trim($usces_entries['customer']['name3']) . trim($usces_entries['customer']['name4']) . '">';
				$html .= '<input type="hidden" name="telno" value="' . str_replace('-', '', $usces_entries['customer']['tel']) . '">';
			}	
			$html .= '<input type="hidden" name="email" value="' . $usces_entries['customer']['mailaddress1'] . '">
				<input type="hidden" name="sendid" value="' . $memid . '">
				<input type="hidden" name="sendpoint" value="' . $rand . '">
				<input type="hidden" name="siteurl" value="' . get_option('home') . '">
				<input type="hidden" name="sitestr" value="「' . get_option('blogname') . '」トップページへ">
				';
			$html .= '<input type="hidden" name="dummy" value="&#65533;" />';
			$onclick = ' onClick="document.charset=\'Shift_JIS\'; document.purchase_form.submit();"';
			$html .= '<div class="send"><input name="purchase" type="submit" value="'.__('Checkout', 'usces').'"' . apply_filters('usces_filter_confirm_nextbutton', $onclick) . ' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html);
			$html .= '</form>';
			$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' />&nbsp;&nbsp;</div>';
			$html = apply_filters('usces_filter_confirm_inform', $html);
			$html .= '</form>'."\n";
			break;
			
		case 'acting_remise_card':
			$charging_type = $this->getItemSkuChargingType($cart[0]['post_id'], $cart[0]['sku']);
			$acting_opts = $this->options['acting_settings']['remise'];
			$this->save_order_acting_data($rand);
			$send_url = ('public' == $acting_opts['card_pc_ope']) ? $acting_opts['send_url_pc'] : $acting_opts['send_url_pc_test'];
			$html .= '<form name="purchase_form" action="' . $send_url . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}" accept-charset="Shift_JIS">
				<input type="hidden" name="SHOPCO" value="' . $acting_opts['SHOPCO'] . '" />
				<input type="hidden" name="HOSTID" value="' . $acting_opts['HOSTID'] . '" />
				<input type="hidden" name="REMARKS3" value="' . $acting_opts['REMARKS3'] . '" />
				<input type="hidden" name="S_TORIHIKI_NO" value="' . $rand . '" />
				<input type="hidden" name="JOB" value="CAPTURE" />
				<input type="hidden" name="MAIL" value="' . $usces_entries['customer']['mailaddress1'] . '" />
				<input type="hidden" name="ITEM" value="0000120" />
				<input type="hidden" name="TOTAL" value="' . $usces_entries['order']['total_full_price'] . '" />
				<input type="hidden" name="AMOUNT" value="' . $usces_entries['order']['total_full_price'] . '" />
				<input type="hidden" name="RETURL" value="' . USCES_CART_URL . '&acting=remise_card&acting_return=1" />
				<input type="hidden" name="NG_RETURL" value="' . USCES_CART_URL . '&acting=remise_card&acting_return=0" />
				<input type="hidden" name="EXITURL" value="' . USCES_CART_URL . '&confirm=1" />
				';
			if( 'on' == $acting_opts['payquick'] && $this->is_member_logged_in() ){
				$member = $this->get_member();
				$pcid = $this->get_member_meta_value('remise_pcid', $member['ID']);
				$html .= '<input type="hidden" name="PAYQUICK" value="1">';
				if( $pcid != NULL )
					$html .= '<input type="hidden" name="PAYQUICKID" value="' . $pcid . '">';
			}
			if( 'on' == $acting_opts['howpay'] && isset($_POST['div']) && '0' !== $_POST['div'] && 0 === (int)$charging_type ){	
				$html .= '<input type="hidden" name="div" value="' . $_POST['div'] . '">';
				switch( $_POST['div'] ){
					case '1':
						$html .= '<input type="hidden" name="METHOD" value="61">';
						$html .= '<input type="hidden" name="PTIMES" value="2">';
						break;
					case '2':
						$html .= '<input type="hidden" name="METHOD" value="80">';
						break;
				}
			}
			if( 0 !== (int)$charging_type ){	
				$nextdate = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
				$html .= '<input type="hidden" name="AUTOCHARGE" value="1">';
				$html .= '<input type="hidden" name="AC_S_KAIIN_NO" value="' . $member['ID'] . '">';
				$html .= '<input type="hidden" name="AC_NAME" value="' . $usces_entries['customer']['name1'].$usces_entries['customer']['name2'] . '">';
				$html .= '<input type="hidden" name="AC_KANA" value="' . $usces_entries['customer']['name3'].$usces_entries['customer']['name4'] . '">';
				$html .= '<input type="hidden" name="AC_TEL" value="' . str_replace('-', '', mb_convert_kana($usces_entries['customer']['tel'], 'a', 'UTF-8')) . '">';
				$html .= '<input type="hidden" name="AC_AMOUNT" value="' . $usces_entries['order']['total_full_price'] . '">';
				$html .= '<input type="hidden" name="AC_TOTAL" value="' . $usces_entries['order']['total_full_price'] . '">';
				switch( (int)$charging_type ){
					case '1':
						$html .= '<input type="hidden" name="AC_NEXT_DATE" value="' . date('Ymd', mktime(0,0,0,substr($nextdate, 5, 2)+1,1,substr($nextdate, 0, 4))) . '">';
						$html .= '<input type="hidden" name="AC_INTERVAL" value="1M">';
						break;
					case '2':
						$html .= '<input type="hidden" name="AC_NEXT_DATE" value="' . date('Ymd', mktime(0,0,0,substr($nextdate, 5, 2)+1,1,substr($nextdate, 0, 4))) . '">';
						$html .= '<input type="hidden" name="AC_INTERVAL" value="12M">';
						break;
				}
			}

			$html .= '<input type="hidden" name="dummy" value="&#65533;" />';
			$html .= '<div class="send"><input name="purchase" type="submit" value="'.__('Checkout', 'usces').'"' . apply_filters('usces_filter_confirm_nextbutton', ' onClick="document.charset=\'Shift_JIS\'; document.purchase_form.submit();"') . ' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html);
			$html .= '</form>';
			$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' />&nbsp;&nbsp;</div>';
			$html = apply_filters('usces_filter_confirm_inform', $html);
			$html .= '</form>'."\n";
			break;
			
		case 'acting_remise_conv':
			$datestr = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
			$acting_opts = $this->options['acting_settings']['remise'];
			$this->save_order_acting_data($rand);
			$send_url = ('public' == $acting_opts['conv_pc_ope']) ? $acting_opts['send_url_cvs_pc'] : $acting_opts['send_url_cvs_pc_test'];
			$html .= '<form name="purchase_form" action="' . $send_url . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}" accept-charset="Shift_JIS">
				<input type="hidden" name="SHOPCO" value="' . $acting_opts['SHOPCO'] . '" />
				<input type="hidden" name="HOSTID" value="' . $acting_opts['HOSTID'] . '" />
				<input type="hidden" name="REMARKS3" value="' . $acting_opts['REMARKS3'] . '" />
				<input type="hidden" name="S_TORIHIKI_NO" value="' . $rand . '" />
				<input type="hidden" name="NAME1" value="' . $usces_entries['customer']['name1'] . '" />
				<input type="hidden" name="NAME2" value="' . $usces_entries['customer']['name2'] . '" />
				<input type="hidden" name="KANA1" value="' . $usces_entries['customer']['name3'] . '" />
				<input type="hidden" name="KANA2" value="' . $usces_entries['customer']['name4'] . '" />
				<input type="hidden" name="YUBIN1" value="' . substr(str_replace('-', '', $usces_entries['customer']['zipcode']), 0, 3) . '" />
				<input type="hidden" name="YUBIN2" value="' . substr(str_replace('-', '', $usces_entries['customer']['zipcode']), 3, 4) . '" />
				<input type="hidden" name="ADD1" value="' . $usces_entries['customer']['pref'] . $usces_entries['customer']['address1'] . '" />
				<input type="hidden" name="ADD2" value="' . $usces_entries['customer']['address2'] . '" />
				<input type="hidden" name="ADD3" value="' . $usces_entries['customer']['address3'] . '" />
				<input type="hidden" name="TEL" value="' . str_replace('-', '', mb_convert_kana($usces_entries['customer']['tel'], 'a', 'UTF-8')) . '" />
				<input type="hidden" name="MAIL" value="' . $usces_entries['customer']['mailaddress1'] . '" />
				<input type="hidden" name="TOTAL" value="' . $usces_entries['order']['total_full_price'] . '" />
				<input type="hidden" name="TAX" value="" />
				<input type="hidden" name="S_PAYDATE" value="' . date('Ymd', mktime(0,0,0,substr($datestr, 5, 2),substr($datestr, 8, 2)+$acting_opts['S_PAYDATE'],substr($datestr, 0, 4))) . '" />
				<input type="hidden" name="SEIYAKUDATE" value="' . date('Ymd', mktime(0,0,0,substr($datestr, 5, 2),substr($datestr, 8, 2),substr($datestr, 0, 4))) . '" />
				<input type="hidden" name="BIKO" value="' . $usces_entries['order']['note'] . '" />
				
				';
			$mname_01 = '商品総額';
			$html .= '<input type="hidden" name="MNAME_01" value="' . $mname_01 . '" />
				<input type="hidden" name="MSUM_01" value="' . $usces_entries['order']['total_full_price'] . '" />
				<input type="hidden" name="MNAME_02" value="" />
				<input type="hidden" name="MSUM_02" value="0" />
				<input type="hidden" name="MNAME_03" value="" />
				<input type="hidden" name="MSUM_03" value="0" />
				<input type="hidden" name="MNAME_04" value="" />
				<input type="hidden" name="MSUM_04" value="0" />
				<input type="hidden" name="MNAME_05" value="" />
				<input type="hidden" name="MSUM_05" value="0" />
				<input type="hidden" name="MNAME_06" value="" />
				<input type="hidden" name="MSUM_06" value="0" />
				<input type="hidden" name="MNAME_07" value="" />
				<input type="hidden" name="MSUM_07" value="0" />
				';

			$html .= '<input type="hidden" name="RETURL" value="' . USCES_CART_URL . '&acting=remise_conv&acting_return=1" />
				<input type="hidden" name="NG_RETURL" value="' . USCES_CART_URL . '&acting=remise_conv&acting_return=0" />
				<input type="hidden" name="OPT" value="1" />
				<input type="hidden" name="EXITURL" value="' . USCES_CART_URL . '&confirm=1" />
				';
			$html .= '
				<input type="hidden" name="dummy" value="&#65533;" />
				<div class="send"><input name="purchase" type="submit" value="'.__('Checkout', 'usces').'"' . apply_filters('usces_filter_confirm_nextbutton', ' onClick="document.charset=\'Shift_JIS\'; document.purchase_form.submit();"') . ' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html);
			$html .= '</form>';
			$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' />&nbsp;&nbsp;</div>';
			$html = apply_filters('usces_filter_confirm_inform', $html);
			$html .= '</form>'."\n";
			break;
			
		default:
			$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' />&nbsp;&nbsp;
				<input name="purchase" type="submit" value="'.__('Checkout', 'usces').'" /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html);
			$html .= '</form>';
	}
}
?>
