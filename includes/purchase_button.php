<?php
if(isset($this))
	$usces = &$this;

$payments = usces_get_payments_by_name($usces_entries['order']['payment_name']);
$acting_flag = '';
$rand = sprintf('%010d', mt_rand(1, 9999999999));
$cart = $usces->cart->get_cart();

//$purchase_disabled = ( '' != $usces->error_message ) ? ' disabled="true"' : '';
$purchase_disabled = '';

if( 'acting' != substr($payments['settlement'], 0, 6) || 0 == $usces_entries['order']['total_full_price'] ){
	$purchase_html = '<form id="purchase_form" action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
		<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.apply_filters('usces_filter_confirm_prebutton_value', __('Back to payment method page.', 'usces')).'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' />
		<input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"' . apply_filters('usces_filter_confirm_nextbutton', NULL) . $purchase_disabled . ' /></div>';
	$html .= apply_filters('usces_filter_confirm_inform', $purchase_html, $payments, $acting_flag, $rand, $purchase_disabled);
	$html .= '</form>';
}else{
	$send_item_code = apply_filters('usces_filter_settlement_item_code', $usces->getItemCode($cart[0]['post_id']));
	$send_item_name = apply_filters('usces_filter_settlement_item_name', $usces->getItemName($cart[0]['post_id']));
	
	$scheme = ( $usces->use_ssl ) ? 'https://' : 'http://';
	
	$acting_flag = ( 'acting' == $payments['settlement'] ) ? $payments['module'] : $payments['settlement'];
	switch( $acting_flag ){
	
		case 'paypal.php':
			require_once($usces->options['settlement_path'] . "paypal.php");
//20110208ysk start
			$lc = ( isset($usces->options['system']['currency']) && !empty($usces->options['system']['currency']) ) ? $usces->options['system']['currency'] : '';
			$currency_code = $usces->get_currency_code();
			global $usces_settings;
			$country_num = $usces_settings['country_num'][$lc];
			$tel = ltrim(str_replace('-', '', $usces_entries['customer']['tel']), '0');
			$html .= '<form id="purchase_form" action="https://' . $usces_paypal_url . '/cgi-bin/webscr" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="' . esc_attr($usces_paypal_business) . '">
				<input type="hidden" name="custom" value="' . $usces->get_uscesid(false) . '">
				<input type="hidden" name="lc" value="'.$lc.'">
				<input type="hidden" name="charset" value="UTF-8">
				<input type="hidden" name="first_name" value="'.esc_attr($usces_entries['customer']['name2']).'">
				<input type="hidden" name="last_name" value="'.esc_attr($usces_entries['customer']['name1']).'">
				<input type="hidden" name="address1" value="'.esc_attr($usces_entries['customer']['address2']).'">
				<input type="hidden" name="address2" value="'.esc_attr($usces_entries['customer']['address3']).'">
				<input type="hidden" name="city" value="'.esc_attr($usces_entries['customer']['address1']).'">
				<input type="hidden" name="state" value="'.esc_attr($usces_entries['customer']['pref']).'">
				<input type="hidden" name="zip" value="'.esc_attr($usces_entries['customer']['zipcode']).'">
				<input type="hidden" name="night_phone_a" value="'.esc_attr($country_num).'">
				<input type="hidden" name="night_phone_b" value="'.esc_attr($tel).'">
				<input type="hidden" name="night_phone_c" value="">';
//20110208ysk end
			if( 1 < count($cart) ) {
				$html .= '<input type="hidden" name="item_name" value="' . esc_attr($send_item_name) . ' ' . __('Others', 'usces') . '">';
			}else{
				$html .= '<input type="hidden" name="item_name" value="' . esc_attr($send_item_name) . '">';
			}
			$html .= '<input type="hidden" name="item_number" value="">
				<input type="hidden" name="amount" value="' . usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false) . '">';
//20110208ysk start
			//if( USCES_JP ){
				//$html .= '<input type="hidden" name="currency_code" value="JPY">';
				$html .= '<input type="hidden" name="currency_code" value="'.$currency_code.'">';
			//}
			$html .= '<input type="hidden" name="no_note" value="1">';
//20110208ysk end
			$html .= '<input type="hidden" name="return" value="' . apply_filters('usces_paypal_return_url', (USCES_CART_URL . $usces->delim . 'acting=paypal&acting_return=1') ) . '">
				<input type="hidden" name="cancel_return" value="' . USCES_CART_URL . $usces->delim . 'confirm=1">
				<input type="hidden" name="notify_url" value="' . USCES_PAYPAL_NOTIFY_URL . '">
				<input type="hidden" name="button_subtype" value="products">
				<input type="hidden" name="tax_rate" value="0.000">
				<input type="hidden" name="shipping" value="0">
				<input type="hidden" name="bn" value="uscons_cart_WPS_JP">
				<div class="send"><input type="image" src="https://www.paypal.com/' . ( USCES_JP ? 'ja_JP/JP' : 'en_US' ) . '/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal"' . apply_filters('usces_filter_confirm_nextbutton', NULL) . $purchase_disabled . ' />
				<img alt="" border="0" src="https://www.paypal.com/' . ( USCES_JP ? 'ja_JP' : 'en_US' ) . '/i/scr/pixel.gif" width="1" height="1"></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled);
			$html .= '</form>';
			$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' /></div>';
			$html = apply_filters('usces_filter_confirm_inform_back', $html);
			$html .= '</form>';
			break;
			
		case 'epsilon.php':
			$member = $usces->get_member();
			$memid = empty($member['ID']) ? 99999999 : $member['ID'];
			$html .= '<form id="purchase_form" action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<input type="hidden" name="user_id" value="' . $memid . '">
				<input type="hidden" name="user_name" value="' . esc_attr($usces_entries['customer']['name1'] . ' ' . $usces_entries['customer']['name2']) . '">
				<input type="hidden" name="user_mail_add" value="' . esc_attr($usces_entries['customer']['mailaddress1']) . '">';
			if( 1 < count($cart) ) {
				$html .= '<input type="hidden" name="item_code" value="99999999">
					<input type="hidden" name="item_name" value="' . esc_attr(mb_substr($send_item_name, 0, 25, 'UTF-8')) . ' ' . __('Others', 'usces') . '">';
			}else{
				$html .= '<input type="hidden" name="item_code" value="' . esc_attr($send_item_code) . '">
					<input type="hidden" name="item_name" value="' . esc_attr(mb_substr($send_item_name, 0, 32, 'UTF-8')) . '">';
			}
			$html .= '<input type="hidden" name="item_price" value="' . usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false) . '">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' />
				<input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"' . apply_filters('usces_filter_confirm_nextbutton', NULL) . $purchase_disabled . ' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled);
			$html .= '</form>';
			break;
			
		case 'acting_zeus_card':
			$acting_opts = $usces->options['acting_settings']['zeus'];
			$usces->save_order_acting_data($rand);
			$html .= '<form id="purchase_form" action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">';
/*			$member = $usces->get_member();
			$pcid = $usces->get_member_meta_value('zeus_pcid', $member['ID']);
			$securecode = isset($_POST['securecode']) ? $_POST['securecode'] : '';
			//if( 2 == $acting_opts['security'] && 'on' == $acting_opts['quickcharge'] && $pcid == '8888888888888888' && $usces->is_member_logged_in() ){
			if( 'on' == $acting_opts['quickcharge'] && $pcid == '8888888888888888' && $usces->is_member_logged_in() ){
				$html .= '<input type="hidden" name="cardnumber" value="8888888888888888">';
				//$html .= '<input type="hidden" name="securecode" value="' . esc_attr($_POST['securecode']) . '">';//20121119ysk 0000620
				$html .= '<input type="hidden" name="securecode" value="' . esc_attr($securecode) . '">';
				$html .= '<input type="hidden" name="expyy" value="' . esc_attr($_POST['expyy']) . '">
					<input type="hidden" name="expmm" value="' . esc_attr($_POST['expmm']) . '">';
			}else{
				$html .= '<input type="hidden" name="cardnumber" value="' . esc_attr($_POST['cnum1']) . '">';
				if( 1 == $acting_opts['security'] ){
					$html .= '<input type="hidden" name="securecode" value="' . esc_attr($securecode) . '">';
				}
				$html .= '<input type="hidden" name="expyy" value="' . esc_attr($_POST['expyy']) . '">
					<input type="hidden" name="expmm" value="' . esc_attr($_POST['expmm']) . '">';
			}*/
			$mem_id = '';
			$pcid = NULL;
			$partofcard = NULL;
			if( $usces->is_member_logged_in() ){
				$member = $usces->get_member();
				$mem_id = $member['ID'];
				$pcid = $usces->get_member_meta_value( 'zeus_pcid', $member['ID'] );
				$partofcard = $usces->get_member_meta_value( 'zeus_partofcard', $member['ID'] );
			}
			if( 'on' == $acting_opts['quickcharge'] && $pcid != NULL && $partofcard != NULL ){
				$html .= '<input type="hidden" name="cardnumber" value="8888888888888888">';
			}else{
				$html .= '<input type="hidden" name="cardnumber" value="' . esc_attr($_POST['cnum1']) . '">';
			}
			if( 1 == $acting_opts['security'] ){
				$securecode = isset($_POST['securecode']) ? $_POST['securecode'] : '';
				$html .= '<input type="hidden" name="securecode" value="' . esc_attr($securecode) . '">';
			}
			$html .= '<input type="hidden" name="expyy" value="' . esc_attr($_POST['expyy']) . '">
				<input type="hidden" name="expmm" value="' . esc_attr($_POST['expmm']) . '">';
			$html .= '<input type="hidden" name="telno" value="' . esc_attr(str_replace('-', '', $usces_entries['customer']['tel'])) . '">
				<input type="hidden" name="email" value="' . esc_attr($usces_entries['customer']['mailaddress1']) . '">
				<input type="hidden" name="sendid" value="' . $mem_id . '">
				<input type="hidden" name="username" value="' . esc_attr($_POST['username_card']) . '">
				<input type="hidden" name="money" value="' . usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false) . '">
				<input type="hidden" name="sendpoint" value="' . $rand . '">
				<input type="hidden" name="printord" value="yes">';
			if( isset($usces_entries['order']['cbrand']) && isset($usces_entries['order']['howpay']) && WCUtils::is_zero($usces_entries['order']['howpay']) ){
				$html .= '<input type="hidden" name="howpay" value="' . $usces_entries['order']['howpay'] . '">';
				$html .= '<input type="hidden" name="cbrand" value="' . $usces_entries['order']['cbrand'] . '">';
				$div_name = 'div_' . $usces_entries['order']['cbrand'];
				$html .= '<input type="hidden" name="div" value="' . $usces_entries['order'][$div_name] . '">';
				$html .= '<input type="hidden" name="div_1" value="' . $usces_entries['order']['div_1'] . '">';
				$html .= '<input type="hidden" name="div_2" value="' . $usces_entries['order']['div_2'] . '">';
				$html .= '<input type="hidden" name="div_3" value="' . $usces_entries['order']['div_3'] . '">';
			}
			$html .= '
				<input type="hidden" name="cnum1" value="' . esc_attr($_POST['cnum1']) . '">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' />
				<input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"' . apply_filters('usces_filter_confirm_nextbutton', NULL) . $purchase_disabled . ' /></div>
				<input type="hidden" name="username_card" value="' . esc_attr($_POST['username_card']) . '">';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled);
			$html .= '</form>';
			break;
			
		case 'acting_zeus_conv':
			$member = $usces->get_member();
			$acting_opts = $usces->options['acting_settings']['zeus'];
			$usces->save_order_acting_data($rand);
			$pay_cvs = ( isset($usces_entries['order']['pay_cvs']) ) ? $usces_entries['order']['pay_cvs'] : '';
			$html .= '<form id="purchase_form" action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<input type="hidden" name="act" value="secure_order">
				<input type="hidden" name="money" value="' . usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false) . '">
				<input type="hidden" name="username" value="' . esc_attr($_POST['username_conv']) . '">
				<input type="hidden" name="telno" value="' . esc_attr(str_replace('-', '', $usces_entries['customer']['tel'])) . '">
				<input type="hidden" name="email" value="' . esc_attr($usces_entries['customer']['mailaddress1']) . '">
				<input type="hidden" name="pay_cvs" value="' . $pay_cvs . '">
				<input type="hidden" name="sendid" value="' . $member['ID'] . '">
				<input type="hidden" name="sendpoint" value="' . $rand . '">';
			$html .= '
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' />
				<input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"' . apply_filters('usces_filter_confirm_nextbutton', NULL) . $purchase_disabled . ' /></div>
				<input type="hidden" name="username_conv" value="' . esc_attr($_POST['username_conv']) . '">';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled);
			$html .= '</form>';
			break;
			
		case 'acting_zeus_bank':
			$member = $usces->get_member();
			$acting_opts = $usces->options['acting_settings']['zeus'];
			$usces->save_order_acting_data($rand);
			$html .= '<form id="purchase_form" action="' . $acting_opts['bank_url'] . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}" accept-charset="Shift_JIS">
				<input type="hidden" name="clientip" value="' . esc_attr($acting_opts['clientip_bank']) . '">
				<input type="hidden" name="act" value="order">
				<input type="hidden" name="money" value="' . usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false) . '">';
			if( isset($acting_opts['bank_ope']) && 'test' == $acting_opts['bank_ope'] ) {
				$html .= '<input type="hidden" name="username" value="' . esc_attr(trim($usces_entries['customer']['name3']) . trim($usces_entries['customer']['name4']) . '_' . $acting_opts['testid_bank']) . '">';
				$html .= '<input type="hidden" name="telno" value="99999999999">';
			}else{
				$html .= '<input type="hidden" name="username" value="' . esc_attr(trim($usces_entries['customer']['name3']) . trim($usces_entries['customer']['name4'])) . '">';
				$html .= '<input type="hidden" name="telno" value="' . esc_attr(str_replace('-', '', $usces_entries['customer']['tel'])) . '">';
			}
			$html .= '<input type="hidden" name="email" value="' . esc_attr($usces_entries['customer']['mailaddress1']) . '">
				<input type="hidden" name="sendid" value="' . $member['ID'] . '">
				<input type="hidden" name="sendpoint" value="' . $rand . '">
				<input type="hidden" name="siteurl" value="' . get_option('home') . '">
				<input type="hidden" name="sitestr" value="「' . esc_attr(get_option('blogname')) . '」トップページへ">
				';
			$html .= '<input type="hidden" name="dummy" value="&#65533;" />';
			$html .= '<div class="send"><input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"' . apply_filters('usces_filter_confirm_nextbutton', ' onClick="document.charset=\'Shift_JIS\';"') . $purchase_disabled . ' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled);
			$html .= '</form>';
			$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' /></div>';
			$html = apply_filters('usces_filter_confirm_inform_back', $html);
			$html .= '</form>'."\n";
			break;
			
		case 'acting_remise_card':
			$charging_type = $usces->getItemChargingType($cart[0]['post_id'], $cart);
			$frequency = $usces->getItemFrequency($cart[0]['post_id']);
			$chargingday = $usces->getItemChargingDay($cart[0]['post_id']);
			$acting_opts = $usces->options['acting_settings']['remise'];
			$usces->save_order_acting_data($rand);
			$member = $usces->get_member();
			$send_url = ('public' == $acting_opts['card_pc_ope']) ? $acting_opts['send_url_pc'] : $acting_opts['send_url_pc_test'];
			$html .= '<form id="purchase_form" name="purchase_form" action="' . $send_url . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}" accept-charset="Shift_JIS">
				<input type="hidden" name="SHOPCO" value="' . esc_attr($acting_opts['SHOPCO']) . '" />
				<input type="hidden" name="HOSTID" value="' . esc_attr($acting_opts['HOSTID']) . '" />
				<input type="hidden" name="REMARKS3" value="' . $acting_opts['REMARKS3'] . '" />
				<input type="hidden" name="S_TORIHIKI_NO" value="' . $rand . '" />
				<input type="hidden" name="JOB" value="' . apply_filters('usces_filter_remise_card_job', $acting_opts['card_jb']) . '" />
				<input type="hidden" name="MAIL" value="' . esc_attr($usces_entries['customer']['mailaddress1']) . '" />
				<input type="hidden" name="ITEM" value="' . apply_filters('usces_filter_remise_card_item', '0000120') . '" />
				<input type="hidden" name="TOTAL" value="' . usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false) . '" />
				<input type="hidden" name="AMOUNT" value="' . usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false) . '" />
				<input type="hidden" name="RETURL" value="' . USCES_CART_URL . $usces->delim . 'acting=remise_card&acting_return=1" />
				<input type="hidden" name="NG_RETURL" value="' . USCES_CART_URL . $usces->delim . 'acting=remise_card&acting_return=0" />
				<input type="hidden" name="EXITURL" value="' . USCES_CART_URL . $usces->delim . 'confirm=1" />
				';
			if( 'on' == $acting_opts['payquick'] && $usces->is_member_logged_in() ){
				$pcid = $usces->get_member_meta_value('remise_pcid', $member['ID']);
				$html .= '<input type="hidden" name="PAYQUICK" value="1">';
				if( $pcid != NULL )
					$html .= '<input type="hidden" name="PAYQUICKID" value="' . $pcid . '">';
			}
			if( 'on' == $acting_opts['howpay'] && isset($usces_entries['order']['div']) && '0' !== $usces_entries['order']['div'] && 'continue' != $charging_type ){
				$html .= '<input type="hidden" name="div" value="' . $usces_entries['order']['div'] . '">';
				switch( $usces_entries['order']['div'] ){
					case '1':
						$html .= '<input type="hidden" name="METHOD" value="61">';
						$html .= '<input type="hidden" name="PTIMES" value="2">';
						break;
					case '2':
						$html .= '<input type="hidden" name="METHOD" value="80">';
						break;
				}
			}else{
				$html .= '<input type="hidden" name="div" value="0">';
				$html .= '<input type="hidden" name="METHOD" value="10">';
			}
			if( 'continue' == $charging_type ){
				$nextdate = current_time('mysql');
				$html .= '
				<input type="hidden" name="AUTOCHARGE" value="1">
				<input type="hidden" name="AC_S_KAIIN_NO" value="' . $member['ID'] . '">
				<input type="hidden" name="AC_NAME" value="' . esc_attr($usces_entries['customer']['name1'].$usces_entries['customer']['name2']) . '">
				<input type="hidden" name="AC_KANA" value="' . esc_attr($usces_entries['customer']['name3'].$usces_entries['customer']['name4']) . '">
				<input type="hidden" name="AC_TEL" value="' . esc_attr(str_replace('-', '', mb_convert_kana($usces_entries['customer']['tel'], 'a', 'UTF-8'))) . '">
				<input type="hidden" name="AC_AMOUNT" value="' . $usces_entries['order']['total_full_price'] . '">
				<input type="hidden" name="AC_TOTAL" value="' . $usces_entries['order']['total_full_price'] . '">
				<input type="hidden" name="AC_NEXT_DATE" value="' . date('Ymd', dlseller_first_charging($cart[0]['post_id'], 'time')) . '">
				<input type="hidden" name="AC_INTERVAL" value="' . $frequency . 'M">
				';
			}
			$html .= '<input type="hidden" name="dummy" value="&#65533;" />';
			$html .= '<div class="send"><input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"' . apply_filters('usces_filter_confirm_nextbutton', ' onClick="document.charset=\'Shift_JIS\';"') . $purchase_disabled . ' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled);
			$html .= '</form>';
			$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' /></div>';
			$html = apply_filters('usces_filter_confirm_inform_back', $html);
			$html .= '</form>'."\n";
			break;
			
		case 'acting_remise_conv':
			if( function_exists('mb_strlen') ){
				$biko = ( 22 < mb_strlen($usces_entries['order']['note'])) ? (mb_substr($usces_entries['order']['note'], 0, 22).'...') : $usces_entries['order']['note'];
			}else{
				$biko = ( 44 < strlen($usces_entries['order']['note'])) ? (substr($usces_entries['order']['note'], 0, 44).'...') : $usces_entries['order']['note'];
			}
			$datestr = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
			$acting_opts = $usces->options['acting_settings']['remise'];
			$usces->save_order_acting_data($rand);
			$send_url = ('public' == $acting_opts['conv_pc_ope']) ? $acting_opts['send_url_cvs_pc'] : $acting_opts['send_url_cvs_pc_test'];
			$kana1 = ( !empty($usces_entries['customer']['name3']) ) ? $usces_entries['customer']['name3'] : '';
			if( !empty($kana1) ) {
				$kana1 = str_replace( "・", "", str_replace( "　", "", mb_convert_kana( $kana1, "KVC" ) ) );
				$kana1 = mb_substr( $kana1, 0, 20 );
				mb_regex_encoding( 'UTF-8' );
				if( !mb_ereg( "^[ァ-ヶー]+$", $kana1 ) ) $kana1 = '';
			}
			$kana2 = ( !empty($usces_entries['customer']['name4']) ) ? $usces_entries['customer']['name4'] : '';
			if( !empty($kana2) ) {
				$kana2 = str_replace( "・", "", str_replace( "　", "", mb_convert_kana( $kana2, "KVC" ) ) );
				$kana2 = mb_substr( $kana2, 0, 20 );
				mb_regex_encoding( 'UTF-8' );
				if( !mb_ereg( "^[ァ-ヶー]+$", $kana2 ) ) $kana2 = '';
			}
			$html .= '<form id="purchase_form" name="purchase_form" action="' . $send_url . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}" accept-charset="Shift_JIS">
				<input type="hidden" name="SHOPCO" value="' . esc_attr($acting_opts['SHOPCO']) . '" />
				<input type="hidden" name="HOSTID" value="' . esc_attr($acting_opts['HOSTID']) . '" />
				<input type="hidden" name="REMARKS3" value="' . $acting_opts['REMARKS3'] . '" />
				<input type="hidden" name="S_TORIHIKI_NO" value="' . $rand . '" />
				<input type="hidden" name="NAME1" value="' . esc_attr(mb_substr($usces_entries['customer']['name1'], 0, 20)) . '" />
				<input type="hidden" name="NAME2" value="' . esc_attr(mb_substr($usces_entries['customer']['name2'], 0, 20)) . '" />
				<input type="hidden" name="KANA1" value="' . esc_attr($kana1) . '" />
				<input type="hidden" name="KANA2" value="' . esc_attr($kana2) . '" />
				<input type="hidden" name="YUBIN1" value="' . esc_attr(substr(str_replace('-', '', $usces_entries['customer']['zipcode']), 0, 3)) . '" />
				<input type="hidden" name="YUBIN2" value="' . esc_attr(substr(str_replace('-', '', $usces_entries['customer']['zipcode']), 3, 4)) . '" />
				<input type="hidden" name="ADD1" value="' . esc_attr($usces_entries['customer']['pref'] . $usces_entries['customer']['address1']) . '" />
				<input type="hidden" name="ADD2" value="' . esc_attr($usces_entries['customer']['address2']) . '" />
				<input type="hidden" name="ADD3" value="' . esc_attr($usces_entries['customer']['address3']) . '" />
				<input type="hidden" name="TEL" value="' . esc_attr(str_replace('-', '', mb_convert_kana($usces_entries['customer']['tel'], 'a', 'UTF-8'))) . '" />
				<input type="hidden" name="MAIL" value="' . esc_attr($usces_entries['customer']['mailaddress1']) . '" />
				<input type="hidden" name="TOTAL" value="' . $usces_entries['order']['total_full_price'] . '" />
				<input type="hidden" name="TAX" value="" />
				<input type="hidden" name="S_PAYDATE" value="' . date('Ymd', mktime(0,0,0,substr($datestr, 5, 2),substr($datestr, 8, 2)+$acting_opts['S_PAYDATE'],substr($datestr, 0, 4))) . '" />
				<input type="hidden" name="SEIYAKUDATE" value="' . date('Ymd', mktime(0,0,0,substr($datestr, 5, 2),substr($datestr, 8, 2),substr($datestr, 0, 4))) . '" />
				<input type="hidden" name="BIKO" value="' . esc_attr($biko) . '" />
				';
			$mname_01 = '商品総額';
			$html .= '<input type="hidden" name="MNAME_01" value="' . $mname_01 . '" />
				<input type="hidden" name="MSUM_01" value="' . usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false) . '" />
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
			$html .= '<input type="hidden" name="RETURL" value="' . USCES_CART_URL . $usces->delim . 'acting=remise_conv&acting_return=1" />
				<input type="hidden" name="NG_RETURL" value="' . USCES_CART_URL . $usces->delim . 'acting=remise_conv&acting_return=0" />
				<input type="hidden" name="OPT" value="1" />
				<input type="hidden" name="EXITURL" value="' . USCES_CART_URL . $usces->delim . 'confirm=1" />
				';
			$html .= '
				<input type="hidden" name="dummy" value="&#65533;" />
				<div class="send"><input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"' . apply_filters('usces_filter_confirm_nextbutton', ' onClick="document.charset=\'Shift_JIS\';"') . $purchase_disabled . ' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled);
			$html .= '</form>';
			$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' /></div>';
			$html = apply_filters('usces_filter_confirm_inform_back', $html);
			$html .= '</form>'."\n";
			break;
			
//20101018ysk start
		case 'acting_jpayment_card'://クレジット決済(J-Payment)
			$acting_opts = $usces->options['acting_settings']['jpayment'];
			$usces->save_order_acting_data($rand);
//20120823ysk start 0000547
			$itemName = $usces->getItemName($cart[0]['post_id']);
			if(1 < count($cart)) $itemName .= ','.__('Others', 'usces');
			if(50 < mb_strlen($itemName)) $itemName = mb_substr($itemName, 0, 50).'...';
			$quantity = 0;
			foreach($cart as $cart_row) {
				$quantity += $cart_row['quantity'];
			}
			$desc = $itemName.' '.__('Quantity','usces').':'.$quantity;
//20120823ysk end
			$html .= '<form id="purchase_form" name="purchase_form" action="'.$acting_opts['send_url'].'" method="post" onKeyDown="if(event.keyCode == 13) {return false;}" >
				<input type="hidden" name="aid" value="'.$acting_opts['aid'].'" />
				<input type="hidden" name="cod" value="'.$rand.'" />
				<input type="hidden" name="jb" value="'.$acting_opts['card_jb'].'" />
				<input type="hidden" name="am" value="'.usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false).'" />
				<input type="hidden" name="tx" value="0" />
				<input type="hidden" name="sf" value="0" />
				<input type="hidden" name="pt" value="1" />
				<input type="hidden" name="inm" value="'.esc_attr($desc).'" />
				<input type="hidden" name="pn" value="'.esc_attr(str_replace('-', '', mb_convert_kana($usces_entries['customer']['tel'], 'a', 'UTF-8'))).'" />
				<input type="hidden" name="em" value="'.esc_attr($usces_entries['customer']['mailaddress1']).'" />
				<input type="hidden" name="acting" value="jpayment_card" />
				<input type="hidden" name="acting_return" value="1" />
				<input type="hidden" name="page_id" value="'.USCES_CART_NUMBER.'" />
				<input type="hidden" name="uscesid" value="' . $usces->get_uscesid(false) . '">
				';
			$html .= '<div class="send"><input name="purchase_jpayment" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"'.apply_filters('usces_filter_confirm_nextbutton', NULL).$purchase_disabled.' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled);
			$html .= '</form>';
			$html .= '<form action="'.USCES_CART_URL.'" method="post" onKeyDown="if(event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"'.apply_filters('usces_filter_confirm_prebutton', NULL).' /></div>';
			$html = apply_filters('usces_filter_confirm_inform_back', $html);
			$html .= '</form>'."\n";
			break;
		case 'acting_jpayment_conv'://コンビニ・ペーパーレス決済(J-Payment)
			$acting_opts = $usces->options['acting_settings']['jpayment'];
			$usces->save_order_acting_data($rand);
//20120823ysk start 0000547
			$itemName = $usces->getItemName($cart[0]['post_id']);
			if(1 < count($cart)) $itemName .= ','.__('Others', 'usces');
			if(50 < mb_strlen($itemName)) $itemName = mb_substr($itemName, 0, 50).'...';
			$quantity = 0;
			foreach($cart as $cart_row) {
				$quantity += $cart_row['quantity'];
			}
			$desc = $itemName.' '.__('Quantity','usces').':'.$quantity;
//20120823ysk end
			$html .= '<form id="purchase_form" name="purchase_form" action="'.$acting_opts['send_url'].'" method="post" onKeyDown="if(event.keyCode == 13) {return false;}" >
				<input type="hidden" name="aid" value="'.$acting_opts['aid'].'" />
				<input type="hidden" name="cod" value="'.$rand.'" />
				<input type="hidden" name="jb" value="CAPTURE" />
				<input type="hidden" name="am" value="'.usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false).'" />
				<input type="hidden" name="tx" value="0" />
				<input type="hidden" name="sf" value="0" />
				<input type="hidden" name="pt" value="2" />
				<input type="hidden" name="inm" value="'.esc_attr($desc).'" />
				<input type="hidden" name="pn" value="'.esc_attr(str_replace('-', '', mb_convert_kana($usces_entries['customer']['tel'], 'a', 'UTF-8'))).'" />
				<input type="hidden" name="em" value="'.esc_attr($usces_entries['customer']['mailaddress1']).'" />
				<input type="hidden" name="acting" value="jpayment_conv" />
				<input type="hidden" name="acting_return" value="1" />
				<input type="hidden" name="page_id" value="'.USCES_CART_NUMBER.'" />
				<input type="hidden" name="uscesid" value="' . $usces->get_uscesid(false) . '">
				';
			$html .= '<div class="send"><input name="purchase_jpayment" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"'.apply_filters('usces_filter_confirm_nextbutton', NULL).$purchase_disabled.' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled);
			$html .= '</form>';
			$html .= '<form action="'.USCES_CART_URL.'" method="post" onKeyDown="if(event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"'.apply_filters('usces_filter_confirm_prebutton', NULL).' /></div>';
			$html = apply_filters('usces_filter_confirm_inform_back', $html);
			$html .= '</form>'."\n";
			break;
		case 'acting_jpayment_bank'://バンクチェック決済(J-Payment)
			$acting_opts = $usces->options['acting_settings']['jpayment'];
			$usces->save_order_acting_data($rand);
//20120823ysk start 0000547
			$itemName = $usces->getItemName($cart[0]['post_id']);
			if(1 < count($cart)) $itemName .= ','.__('Others', 'usces');
			if(50 < mb_strlen($itemName)) $itemName = mb_substr($itemName, 0, 50).'...';
			$quantity = 0;
			foreach($cart as $cart_row) {
				$quantity += $cart_row['quantity'];
			}
			$desc = $itemName.' '.__('Quantity','usces').':'.$quantity;
//20120823ysk end
			$html .= '<form id="purchase_form" name="purchase_form" action="'.$acting_opts['send_url'].'" method="post" onKeyDown="if(event.keyCode == 13) {return false;}" >
				<input type="hidden" name="aid" value="'.$acting_opts['aid'].'" />
				<input type="hidden" name="cod" value="'.$rand.'" />
				<input type="hidden" name="jb" value="CAPTURE" />
				<input type="hidden" name="am" value="'.usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false).'" />
				<input type="hidden" name="tx" value="0" />
				<input type="hidden" name="sf" value="0" />
				<input type="hidden" name="pt" value="7" />
				<input type="hidden" name="inm" value="'.esc_attr($desc).'" />
				<input type="hidden" name="pn" value="'.esc_attr(str_replace('-', '', mb_convert_kana($usces_entries['customer']['tel'], 'a', 'UTF-8'))).'" />
				<input type="hidden" name="em" value="'.esc_attr($usces_entries['customer']['mailaddress1']).'" />
				<input type="hidden" name="acting" value="jpayment_bank" />
				<input type="hidden" name="acting_return" value="1" />
				<input type="hidden" name="page_id" value="'.USCES_CART_NUMBER.'" />
				<input type="hidden" name="uscesid" value="' . $usces->get_uscesid(false) . '">
				';
			$html .= '<div class="send"><input name="purchase_jpayment" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"'.apply_filters('usces_filter_confirm_nextbutton', NULL).$purchase_disabled.' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled);
			$html .= '</form>';
			$html .= '<form action="'.USCES_CART_URL.'" method="post" onKeyDown="if(event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"'.apply_filters('usces_filter_confirm_prebutton', NULL).' /></div>';
			$html = apply_filters('usces_filter_confirm_inform_back', $html);
			$html .= '</form>'."\n";
			break;
//20101018ysk end
//20110208ysk start
		case 'acting_paypal_ec'://PayPal(エクスプレス・チェックアウト)
			$acting_opts = $usces->options['acting_settings']['paypal'];
			$currency_code = $usces->get_currency_code();
			$html .= '<form id="purchase_form" action="'.USCES_CART_URL.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<input type="hidden" name="SOLUTIONTYPE" value="Sole">
				<input type="hidden" name="LANDINGPAGE" value="Billing">
				<input type="hidden" name="EMAIL" value="'.esc_attr( $usces_entries['customer']['mailaddress1'] ).'">
				<input type="hidden" name="PAYMENTREQUEST_0_CURRENCYCODE" value="'.$currency_code.'">';
			if( 'shipped' == $usces->getItemDivision( $cart[0]['post_id'] ) ) {
				$name = apply_filters( 'usces_filter_paypalec_shiptoname', esc_attr($usces_entries['delivery']['name2'].' '.$usces_entries['delivery']['name1']) );
				$address2 = apply_filters( 'usces_filter_paypalec_shiptostreet', esc_attr($usces_entries['delivery']['address2']) );
				$address3 = apply_filters( 'usces_filter_paypalec_shiptostreet2', esc_attr($usces_entries['delivery']['address3']) );
				$address1 = apply_filters( 'usces_filter_paypalec_shiptocity', esc_attr($usces_entries['delivery']['address1']) );
				$pref = apply_filters( 'usces_filter_paypalec_shiptostate', esc_attr($usces_entries['delivery']['pref']) );
				$country = ( !empty($usces_entries['delivery']['country']) ) ? $usces_entries['delivery']['country'] : usces_get_base_country();
				$country_code = apply_filters( 'usces_filter_paypalec_shiptocountrycode', $country );
				$zip = apply_filters( 'usces_filter_paypalec_shiptozip', $usces_entries['delivery']['zipcode'] );
				$tel = apply_filters( 'usces_filter_paypalec_shiptophonenum', ltrim(str_replace('-', '', $usces_entries['delivery']['tel']), '0') );
				$html .= '
				<input type="hidden" name="PAYMENTREQUEST_0_SHIPTONAME" value="'.$name.'">
				<input type="hidden" name="PAYMENTREQUEST_0_SHIPTOSTREET" value="'.$address2.'">
				<input type="hidden" name="PAYMENTREQUEST_0_SHIPTOSTREET2" value="'.$address3.'">
				<input type="hidden" name="PAYMENTREQUEST_0_SHIPTOCITY" value="'.$address1.'">
				<input type="hidden" name="PAYMENTREQUEST_0_SHIPTOSTATE" value="'.$pref.'">
				<input type="hidden" name="PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE" value="'.$country_code.'">
				<input type="hidden" name="PAYMENTREQUEST_0_SHIPTOZIP" value="'.$zip.'">
				<input type="hidden" name="PAYMENTREQUEST_0_SHIPTOPHONENUM" value="'.$tel.'">';
			}
//20120629ysk start 0000520
			if( 'shipped' != $usces->getItemDivision( $cart[0]['post_id'] ) ) {
				$html .= '<input type="hidden" name="NOSHIPPING" value="1">';
			}
//20120629ysk end
			$charging_type = $usces->getItemChargingType($cart[0]['post_id'], $cart);
			//$frequency = $usces->getItemFrequency($cart[0]['post_id']);
			if( 'continue' != $charging_type ) {
				//通常購入
//20110606ysk start
/*				$itemName = $usces->getItemName($cart[0]['post_id']);
				if(1 < count($cart)) $itemName .= ','.__('Others', 'usces');
				if(50 < mb_strlen($itemName)) $itemName = mb_substr($itemName, 0, 50).'...';
				$quantity = 0;
				foreach($cart as $cart_row) {
					$quantity += $cart_row['quantity'];
				}
				$desc = $itemName.' '.__('Quantity','usces').':'.$quantity;
				$html .= '<input type="hidden" name="PAYMENTREQUEST_0_DESC" value="'.esc_attr($desc).'">';
*/
				$item_total_price = 0;
				$i = 0;
				foreach( $cart as $cart_row ) {
					$html .= '
						<input type="hidden" name="L_PAYMENTREQUEST_0_NAME'.$i.'" value="'.esc_attr($usces->getItemName($cart_row['post_id'])).'">
						<input type="hidden" name="L_PAYMENTREQUEST_0_AMT'.$i.'" value="'.usces_crform($cart_row['price'], false, false, 'return', false).'">
						<input type="hidden" name="L_PAYMENTREQUEST_0_NUMBER'.$i.'" value="'.esc_attr($usces->getItemCode($cart_row['post_id'])).'">
						<input type="hidden" name="L_PAYMENTREQUEST_0_QTY'.$i.'" value="'.esc_attr($cart_row['quantity']).'">';
					$item_total_price += ( $cart_row['price'] * $cart_row['quantity'] );
					$i++;
				}
//20110606ysk end
				//if( !empty($usces_entries['order']['discount']) ) $html .= '<input type="hidden" name="PAYMENTREQUEST_0_SHIPDISCAMT" value="'.usces_crform($usces_entries['order']['discount'], false, false, 'return', false).'">';
				if( !empty($usces_entries['order']['discount']) ) {
					$html .= '
						<input type="hidden" name="L_PAYMENTREQUEST_0_NAME'.$i.'" value="'.esc_attr(__('Campaign disnount', 'usces')).'">
						<input type="hidden" name="L_PAYMENTREQUEST_0_AMT'.$i.'" value="'.usces_crform($usces_entries['order']['discount'], false, false, 'return', false).'">';
					$item_total_price += $usces_entries['order']['discount'];
					$i++;
				}
				if( !empty($usces_entries['order']['usedpoint']) ) {
					$html .= '
						<input type="hidden" name="L_PAYMENTREQUEST_0_NAME'.$i.'" value="'.esc_attr(__('Used points', 'usces')).'">
						<input type="hidden" name="L_PAYMENTREQUEST_0_AMT'.$i.'" value="'.usces_crform($usces_entries['order']['usedpoint']*(-1), false, false, 'return', false).'">';
					$item_total_price -= $usces_entries['order']['usedpoint'];
					$i++;
				}
				$html .= '
					<input type="hidden" name="PAYMENTREQUEST_0_ITEMAMT" value="'.usces_crform($item_total_price, false, false, 'return', false).'">
					<input type="hidden" name="PAYMENTREQUEST_0_SHIPPINGAMT" value="'.usces_crform($usces_entries['order']['shipping_charge'], false, false, 'return', false).'">
					<input type="hidden" name="PAYMENTREQUEST_0_AMT" value="'.usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false).'">
					';
				if( !empty($usces_entries['order']['cod_fee']) ) $html .= '<input type="hidden" name="PAYMENTREQUEST_0_HANDLINGAMT" value="'.usces_crform($usces_entries['order']['cod_fee'], false, false, 'return', false).'">';
				if( !empty($usces_entries['order']['tax']) ) $html .= '<input type="hidden" name="PAYMENTREQUEST_0_TAXAMT" value="'.usces_crform($usces_entries['order']['tax'], false, false, 'return', false).'">';
			} else {
				//定期支払い
				//$desc = usces_make_agreement_description($cart, $usces_entries['order']['total_items_price']);
				$desc = usces_make_agreement_description($cart, $usces_entries['order']['total_full_price']);//20111125ysk 0000320
				$html .= '<input type="hidden" name="L_BILLINGTYPE0" value="RecurringPayments">
					<input type="hidden" name="L_BILLINGAGREEMENTDESCRIPTION0" value="'.esc_attr($desc).'">
					<input type="hidden" name="AMT" value="0">';
			}
			if( !empty($acting_opts['logoimg']) ) $html .= '<input type="hidden" name="LOGOIMG" value="'.esc_attr($acting_opts['logoimg']).'">';
			//if( !empty($acting_opts['cartbordercolor']) and 'FFFFFF' != strtoupper($acting_opts['cartbordercolor']) ) $html .= '<input type="hidden" name="CARTBORDERCOLOR" value="'.esc_attr($acting_opts['cartbordercolor']).'">';
			if( !empty($acting_opts['cartbordercolor']) ) $html .= '<input type="hidden" name="CARTBORDERCOLOR" value="'.esc_attr($acting_opts['cartbordercolor']).'">';
			$html .= '<input type="hidden" name="purchase" value="acting_paypal_ec">';//20110502ysk
			$html .= '<div class="send"><input type="image" src="https://www.paypal.com/'.( USCES_JP ? 'ja_JP/JP' : 'en_US' ).'/i/btn/btn_xpressCheckout.gif" border="0" name="submit" value="submit" alt="PayPal"'.apply_filters('usces_filter_confirm_nextbutton', NULL).$purchase_disabled.' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled);
			$html .= '</form>';
			$html .= '<form action="'.USCES_CART_URL.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"'.apply_filters('usces_filter_confirm_prebutton', NULL).' /></div>';
			$html = apply_filters('usces_filter_confirm_inform_back', $html);
			$html .= '</form>';
			break;
//20110208ysk end
//20120413ysk start
		case 'acting_sbps_card':
		case 'acting_sbps_conv':
		case 'acting_sbps_payeasy':
		case 'acting_sbps_wallet':
		case 'acting_sbps_mobile':
			$charging_type = $usces->getItemChargingType($cart[0]['post_id']);
			$frequency = $usces->getItemFrequency($cart[0]['post_id']);
			$chargingday = $usces->getItemChargingDay($cart[0]['post_id']);
			$acting_opts = $usces->options['acting_settings']['sbps'];
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
			$sbps_cust_no = '';
			$sbps_payment_no = '';
			switch( $acting_flag ) {
			case 'acting_sbps_card':
				//if( 'on' == $acting_opts['cust'] ) {
				//	$sbps_cust_no = $usces->get_member_meta_value( 'sbps_cust_no', $member['ID'] );
				//	$sbps_payment_no = $usces->get_member_meta_value( 'sbps_payment_no', $member['ID'] );
				//}
				$pay_method = ( 'on' == $acting_opts['3d_secure'] ) ? "credit3d" : "credit";
				$acting = "sbps_card";
				$free_csv = "";
				break;
			case 'acting_sbps_conv':
				$pay_method = "webcvs";
				$acting = "sbps_conv";
				$free_csv = usces_set_free_csv( $usces_entries['customer'] );
				break;
			case 'acting_sbps_payeasy':
				$pay_method = "payeasy";
				$acting = "sbps_payeasy";
				$free_csv = usces_set_free_csv( $usces_entries['customer'] );
				break;
			case 'acting_sbps_wallet':
				$pay_method = "";
				if( 'on' == $acting_opts['wallet_yahoowallet'] ) $pay_method .= ",yahoowallet";
				if( 'on' == $acting_opts['wallet_rakuten'] ) $pay_method .= ",rakuten";
				if( 'on' == $acting_opts['wallet_paypal'] ) $pay_method .= ",paypal";
				if( 'on' == $acting_opts['wallet_netmile'] ) $pay_method .= ",netmile";
				if( 'on' == $acting_opts['wallet_alipay'] ) $pay_method .= ",alipay";
				$pay_method = ltrim( $pay_method, "," );
				$acting = "sbps_wallet";
				$free_csv = "";
				break;
			case 'acting_sbps_mobile':
				$pay_method = "";
				if( 'on' == $acting_opts['mobile_docomo'] ) $pay_method .= ",docomo";
				if( 'on' == $acting_opts['mobile_softbank'] ) $pay_method .= ",softbank";
				if( 'on' == $acting_opts['mobile_auone'] ) $pay_method .= ",auone";
				if( 'on' == $acting_opts['mobile_mysoftbank'] ) $pay_method .= ",mysoftbank";
				if( 'on' == $acting_opts['mobile_softbank2'] ) $pay_method .= ",softbank2";
				$pay_method = ltrim( $pay_method, "," );
				$acting = "sbps_mobile";
				$free_csv = "";
				break;
			}
			$item_id = $cart[0]['post_id'];
			$item_name = $usces->getItemName($cart[0]['post_id']);
			if(1 < count($cart)) $item_name .= ' '.__('Others', 'usces');
			if(36 < mb_strlen($item_name)) $item_name = mb_substr($item_name, 0, 36).'...';
			$item_name = esc_attr( $item_name );
			$amount = usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false);
			$pay_type = "0";
			$auto_charge_type = "";
			$service_type = "0";
			$div_settle = "";
			$last_charge_month = "";
			$camp_type = "";
			$terminal_type = "0";
			$success_url = USCES_CART_URL.$usces->delim."acting=".$acting."&acting_return=1";
			$cancel_url = USCES_CART_URL.$usces->delim."acting=".$acting."&confirm=1";
			$error_url = USCES_CART_URL.$usces->delim."acting=".$acting."&acting_return=0";
			$pagecon_url = USCES_CART_URL;
			$free1 = $acting_flag;
			$request_date = date('YmdHis', current_time('timestamp'));
			$limit_second = "600";
			$sps_hashcode = $pay_method.$acting_opts['merchant_id'].$acting_opts['service_id'].$cust_code.$sbps_cust_no.$sbps_payment_no.$rand.$item_id.$item_name.$amount.$pay_type.$auto_charge_type.$service_type.$div_settle.$last_charge_month.$camp_type.$terminal_type.$success_url.$cancel_url.$error_url.$pagecon_url.$free1.$free_csv.$request_date.$limit_second.$acting_opts['hash_key'];
			$sps_hashcode = sha1( $sps_hashcode );
			$html .= '<form id="purchase_form" name="purchase_form" action="'.$send_url.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}" accept-charset="Shift_JIS">
				<input type="hidden" name="pay_method" value="'.$pay_method.'" />
				<input type="hidden" name="merchant_id" value="'.$acting_opts['merchant_id'].'" />
				<input type="hidden" name="service_id" value="'.$acting_opts['service_id'].'" />
				<input type="hidden" name="cust_code" value="'.$cust_code.'" />
				<input type="hidden" name="sps_cust_no" value="'.$sbps_cust_no.'" />
				<input type="hidden" name="sps_payment_no" value="'.$sbps_payment_no.'" />
				<input type="hidden" name="order_id" value="'.$rand.'" />
				<input type="hidden" name="item_id" value="'.$item_id.'" />
				<input type="hidden" name="pay_item_id" value="" />
				<input type="hidden" name="item_name" value="'.$item_name.'" />
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
				';
			$html .= '<input type="hidden" name="dummy" value="&#65533;" />';
			$html .= '<div class="send"><input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.apply_filters('usces_filter_confirm_nextbutton_value', __('Checkout', 'usces')).'"' . apply_filters('usces_filter_confirm_nextbutton', ' onClick="document.charset=\'Shift_JIS\';"') . $purchase_disabled . ' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled);
			$html .= '</form>';
			$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' /></div>';
			$html = apply_filters('usces_filter_confirm_inform_back', $html);
			$html .= '</form>'."\n";
			break;
//20120413ysk end
//20120618ysk start
		case 'acting_telecom_card'://テレコムクレジット
			$acting_opts = $usces->options['acting_settings']['telecom'];
			$member = $usces->get_member();
			if( empty($member['ID']) ) {
				$memid = 99999999;
				$send_url = $acting_opts['send_url'];
			} else {
				$memid = $member['ID'];
				$send_url = ( 'on' == $acting_opts['oneclick'] ) ? $acting_opts['oneclick_send_url'] : $acting_opts['send_url'];
			}
			$money  = ( '$' == usces_get_cr_symbol() ) ? '$' : '';
			$money .= usces_crform( $usces_entries['order']['total_full_price'], false, false, 'return', false );
			$tel = str_replace('-', '', $usces_entries['customer']['tel']);
			$redirect_url = USCES_CART_URL.$usces->delim.'acting=telecom_card&acting_return=1&result=1';
			$redirect_back_url = USCES_CART_URL.$usces->delim.'confirm=1';
			$html .= '<form id="purchase_form" action="'.$send_url.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<input type="hidden" name="clientip" value="'.$acting_opts['clientip'].'">
				<input type="hidden" name="money" value="'.apply_filters( 'usces_filter_acting_amount', $money, $acting_flag ).'">
				<input type="hidden" name="sendid" value="'.$memid.'">
				<input type="hidden" name="usrtel" value="'.$tel.'">
				<input type="hidden" name="usrmail" value="'.esc_attr($usces_entries['customer']['mailaddress1']).'">
				<input type="hidden" name="redirect_url" value="'.$redirect_url.'">
				<input type="hidden" name="redirect_back_url" value="'.$redirect_back_url.'">
				<input type="hidden" name="option" value="'.$rand.'">
				';
			$html .= '<div class="send"><input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"'.apply_filters('usces_filter_confirm_nextbutton', NULL).$purchase_disabled.' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled);
			$html .= '</form>';
			$html .= '<form action="'.USCES_CART_URL.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"'.apply_filters('usces_filter_confirm_prebutton', NULL).' /></div>';
			$html = apply_filters('usces_filter_confirm_inform_back', $html);
			$html .= '</form>';
			break;
//20120618ysk end
//20121030ysk start
		case 'acting_telecom_edy'://Edy決済(テレコムクレジット)
			$acting_opts = $usces->options['acting_settings']['telecom'];
			$member = $usces->get_member();
			$memid = empty($member['ID']) ? 99999999 : $member['ID'];
			$money  = ( '$' == usces_get_cr_symbol() ) ? '$' : '';
			$money .= usces_crform( $usces_entries['order']['total_full_price'], false, false, 'return', false );
			$redirect_back_url = USCES_CART_URL.$usces->delim.'acting=telecom_edy&acting_return=1&reg_order=1';
			$html .= '<form id="purchase_form" action="'.USCES_CART_URL.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<input type="hidden" name="clientip" value="'.$acting_opts['clientip'].'">
				<input type="hidden" name="sendid" value="'.$memid.'">
				<input type="hidden" name="money" value="'.apply_filters( 'usces_filter_acting_amount', $money, $acting_flag ).'">
				<input type="hidden" name="redirect_back_url" value="'.$redirect_back_url.'">
				<input type="hidden" name="option" value="'.$rand.'">
				';
			$html .= '<div class="send"><input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"'.apply_filters('usces_filter_confirm_nextbutton', NULL).$purchase_disabled.' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled);
			$html .= '</form>';
			$html .= '<form action="'.USCES_CART_URL.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"'.apply_filters('usces_filter_confirm_prebutton', NULL).' /></div>';
			$html = apply_filters('usces_filter_confirm_inform_back', $html);
			$html .= '</form>';
			break;
//20121030ysk end
//20121206ysk start
		case 'acting_digitalcheck_card'://カード決済(ペイデザイン)
			$acting_opts = $usces->options['acting_settings']['digitalcheck'];
			$sid = uniqid();
			$usces->save_order_acting_data($sid);
			$member = $usces->get_member();
			if( 'on' == $acting_opts['card_user_id'] && $usces->is_member_logged_in() ) {
				$ip_user_id = $usces->get_member_meta_value( 'digitalcheck_ip_user_id', $member['ID'] );
				if( empty($ip_user_id) ) {
					$ip_user_id = $member['ID'];
					$send_url = $acting_opts['send_url_card'];
					$fuka = $acting_flag.$ip_user_id;
				} else {
					$send_url = USCES_CART_URL;
					$fuka = $acting_flag;
				}
			} else {
				$ip_user_id = false;
				$send_url = $acting_opts['send_url_card'];
				$fuka = $acting_flag;
			}
			$item_name = $usces->getItemName($cart[0]['post_id']);
			if( 1 < count($cart) ) $item_name .= ','.__('Others', 'usces');
			if( 46 < strlen($item_name) ) $item_name = mb_strimwidth( $item_name, 0, 50, '...' );
			$kakutei = ( empty($acting_opts['card_kakutei']) ) ? '0' : $acting_opts['card_kakutei'];
			$html .= '<form id="purchase_form" name="purchase_form" action="'.$send_url.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}" accept-charset="Shift_JIS">
				<input type="hidden" name="IP" value="'.$acting_opts['card_ip'].'" />
				<input type="hidden" name="SID" value="'.$sid.'" />
				<input type="hidden" name="N1" value="'.$item_name.'">
				<input type="hidden" name="K1" value="'.usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false).'">
				<input type="hidden" name="STORE" value="51" />
				<input type="hidden" name="KAKUTEI" value="'.$kakutei.'" />
				<input type="hidden" name="FUKA" value="'.$fuka.'" />
				<input type="hidden" name="NAME1" value="'.esc_attr(mb_strimwidth($usces_entries['customer']['name1'], 0, 20)).'" />
				<input type="hidden" name="NAME2" value="'.esc_attr(mb_strimwidth($usces_entries['customer']['name2'], 0, 20)).'" />
				<input type="hidden" name="KANA1" value="'.esc_attr(mb_strimwidth($usces_entries['customer']['name3'], 0, 20)).'" />
				<input type="hidden" name="KANA2" value="'.esc_attr(mb_strimwidth($usces_entries['customer']['name4'], 0, 20)).'" />
				<input type="hidden" name="YUBIN1" value="'.esc_attr(substr(str_replace('-', '', mb_convert_kana($usces_entries['customer']['zipcode'], 'a', 'UTF-8')), 0, 7)).'" />
				<input type="hidden" name="TEL" value="'.esc_attr(substr(str_replace('-', '', mb_convert_kana($usces_entries['customer']['tel'], 'a', 'UTF-8')), 0, 11)).'" />
				<input type="hidden" name="ADR1" value="'.esc_attr(mb_strimwidth($usces_entries['customer']['pref'].$usces_entries['customer']['address1'].$usces_entries['customer']['address2'], 0, 50)).'" />
				<input type="hidden" name="ADR2" value="'.esc_attr(mb_strimwidth($usces_entries['customer']['address3'], 0, 50)).'" />
				<input type="hidden" name="MAIL" value="'.esc_attr($usces_entries['customer']['mailaddress1']).'" />
				';
			if( $ip_user_id ) {
				$html .= '<input type="hidden" name="PASS" value="'.$acting_opts['card_pass'].'">
					<input type="hidden" name="IP_USER_ID" value="'.$ip_user_id.'">
					';
			}
			if( $usces->use_ssl ) {
				$ssl_url = $usces->options['ssl_url'].'/?page_id='.USCES_CART_NUMBER;
				$html .= '<input type="hidden" name="OKURL" value="'.$ssl_url.$usces->delim.'acting=digitalcheck_card&acting_return=1" />
					<input type="hidden" name="RT" value="'.$ssl_url.$usces->delim.'acting=digitalcheck_card&confirm=1" />
					';
			} else {
				$html .= '<input type="hidden" name="OKURL" value="'.USCES_CART_URL.$usces->delim.'acting=digitalcheck_card&acting_return=1" />
					<input type="hidden" name="RT" value="'.USCES_CART_URL.$usces->delim.'acting=digitalcheck_card&confirm=1" />
					';
			}
			$html .= '<input type="hidden" name="dummy" value="&#65533;" />';
			$html .= '<div class="send"><input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"'.apply_filters('usces_filter_confirm_nextbutton', ' onClick="document.charset=\'Shift_JIS\';"').$purchase_disabled.' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $sid, $purchase_disabled);
			$html .= '</form>';
			$html .= '<form action="'.USCES_CART_URL.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"'.apply_filters('usces_filter_confirm_prebutton', NULL).' /></div>';
			$html = apply_filters('usces_filter_confirm_inform_back', $html);
			$html .= '</form>'."\n";
			break;
		case 'acting_digitalcheck_conv'://コンビニ決済(ペイデザイン)
			$acting_opts = $usces->options['acting_settings']['digitalcheck'];
			$sid = uniqid();
			$usces->save_order_acting_data($sid);
			$item_name = $usces->getItemName($cart[0]['post_id']);
			if( 1 < count($cart) ) $item_name .= ','.__('Others', 'usces');
			if( 46 < strlen($item_name) ) $item_name = mb_strimwidth( $item_name, 0, 50, '...' );
			$today = date( 'Y-m-d', current_time('timestamp') );
			list( $year, $month, $day ) = explode( '-', $today );
			$kigen = date( 'Ymd', mktime(0, 0, 0, (int)$month, (int)$day + (int)$acting_opts['conv_kigen'], (int)$year) );
			$store = ( 1 < count($acting_opts['conv_store']) ) ? '99' : $acting_opts['conv_store'][0];
			//$html .= '<form id="purchase_form" name="purchase_form" action="'.$acting_opts['send_url_conv'].'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}" accept-charset="Shift_JIS">
			$html .= '<form id="purchase_form" name="purchase_form" action="'.USCES_CART_URL.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}" accept-charset="Shift_JIS">
				<input type="hidden" name="IP" value="'.$acting_opts['conv_ip'].'" />
				<input type="hidden" name="SID" value="'.$sid.'" />
				<input type="hidden" name="N1" value="'.esc_attr($item_name).'">
				<input type="hidden" name="K1" value="'.usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false).'">
				<input type="hidden" name="STORE" value="'.$store.'" />
				<input type="hidden" name="FUKA" value="'.$acting_flag.'" />
				<input type="hidden" name="KIGEN" value="'.$kigen.'" />
				<input type="hidden" name="NAME1" value="'.esc_attr(mb_strimwidth($usces_entries['customer']['name1'], 0, 20)).'" />
				<input type="hidden" name="NAME2" value="'.esc_attr(mb_strimwidth($usces_entries['customer']['name2'], 0, 20)).'" />
				<input type="hidden" name="KANA1" value="'.esc_attr(mb_strimwidth($usces_entries['customer']['name3'], 0, 20)).'" />
				<input type="hidden" name="KANA2" value="'.esc_attr(mb_strimwidth($usces_entries['customer']['name4'], 0, 20)).'" />
				<input type="hidden" name="YUBIN1" value="'.esc_attr(substr(str_replace('-', '', mb_convert_kana($usces_entries['customer']['zipcode'], 'a', 'UTF-8')), 0, 7)).'" />
				<input type="hidden" name="TEL" value="'.esc_attr(substr(str_replace('-', '', mb_convert_kana($usces_entries['customer']['tel'], 'a', 'UTF-8')), 0, 11)).'" />
				<input type="hidden" name="ADR1" value="'.esc_attr(mb_strimwidth($usces_entries['customer']['pref'].$usces_entries['customer']['address1'].$usces_entries['customer']['address2'], 0, 50)).'" />
				<input type="hidden" name="ADR2" value="'.esc_attr(mb_strimwidth($usces_entries['customer']['address3'], 0, 50)).'" />
				<input type="hidden" name="MAIL" value="'.esc_attr($usces_entries['customer']['mailaddress1']).'" />
				';
			if( $usces->use_ssl ) {
				$ssl_url = $usces->options['ssl_url'].'/?page_id='.USCES_CART_NUMBER;
				$html .= '<input type="hidden" name="OKURL" value="'.$ssl_url.$usces->delim.'acting=digitalcheck_conv&acting_return=1" />
					<input type="hidden" name="RT" value="'.$ssl_url.$usces->delim.'acting=digitalcheck_conv&confirm=1" />
					';
			} else {
				$html .= '<input type="hidden" name="OKURL" value="'.USCES_CART_URL.$usces->delim.'acting=digitalcheck_conv&acting_return=1" />
					<input type="hidden" name="RT" value="'.USCES_CART_URL.$usces->delim.'acting=digitalcheck_conv&confirm=1" />
					';
			}
			$html .= '<input type="hidden" name="dummy" value="&#65533;" />';
			$html .= '<div class="send"><input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"'.apply_filters('usces_filter_confirm_nextbutton', ' onClick="document.charset=\'Shift_JIS\';"').$purchase_disabled.' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $sid, $purchase_disabled);
			$html .= '</form>';
			$html .= '<form action="'.USCES_CART_URL.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"'.apply_filters('usces_filter_confirm_prebutton', NULL).' /></div>';
			$html = apply_filters('usces_filter_confirm_inform_back', $html);
			$html .= '</form>'."\n";
			break;
//20121206ysk end
//20130225ysk start
		case 'acting_mizuho_card'://カード決済(みずほファクター)
			$acting_opts = $usces->options['acting_settings']['mizuho'];
			$send_url = ( 'public' == $acting_opts['ope'] ) ? $acting_opts['send_url'] : $acting_opts['send_url_test'];
			$p_ver = '0200';
			$stdate = date( 'Ymd' );
			$stran = sprintf( '%06d', mt_rand(1, 999999) );
			$bkcode = 'bg01';
			$amount = apply_filters( 'usces_filter_acting_amount', usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false), $acting_flag );
			$schksum = $p_ver.$stdate.$stran.$bkcode.$acting_opts['shopid'].$acting_opts['cshopid'].$amount.$acting_opts['hash_pass'];
			$schksum = htmlspecialchars( md5( $schksum ) );
			$html .= '<form id="purchase_form" action="'.$send_url.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}" accept-charset="Shift_JIS">
				<input type="hidden" name="p_ver" value="'.$p_ver.'">
				<input type="hidden" name="stdate" value="'.$stdate.'">
				<input type="hidden" name="stran" value="'.$stran.'">
				<input type="hidden" name="bkcode" value="'.$bkcode.'">
				<input type="hidden" name="shopid" value="'.$acting_opts['shopid'].'">
				<input type="hidden" name="cshopid" value="'.$acting_opts['cshopid'].'">
				<input type="hidden" name="amount" value="'.$amount.'">
				<input type="hidden" name="schksum" value="'.$schksum.'">
				';
			$html .= '<input type="hidden" name="dummy" value="&#65533;" />';
			$html .= '<div class="send"><input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"'.apply_filters('usces_filter_confirm_nextbutton', ' onClick="document.charset=\'Shift_JIS\';"').$purchase_disabled.' /></div>';
			$html = apply_filters( 'usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled );
			$html .= '</form>';
			$html .= '<form action="'.USCES_CART_URL.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"'.apply_filters('usces_filter_confirm_prebutton', NULL).' /></div>';
			$html = apply_filters( 'usces_filter_confirm_inform_back', $html );
			$html .= '</form>';
			break;
		case 'acting_mizuho_conv1'://コンビニ・ウェルネット決済(みずほファクター)
		case 'acting_mizuho_conv2'://コンビニ・セブンイレブン決済(みずほファクター)
			$acting_opts = $usces->options['acting_settings']['mizuho'];
			$send_url = ( 'public' == $acting_opts['ope'] ) ? $acting_opts['send_url'] : $acting_opts['send_url_test'];
			$p_ver = '0200';
			$stdate = date( 'Ymd' );
			$stran = sprintf( '%06d', mt_rand(1, 999999) );
			$bkcode = 'cv0'.substr( $acting_flag, -1 );
			$amount = apply_filters( 'usces_filter_acting_amount', usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false), $acting_flag );
			$custmKanji = mb_strimwidth( $usces_entries['customer']['name1'].$usces_entries['customer']['name2'], 0, 40 );
			$mailaddr = esc_attr( $usces_entries['customer']['mailaddress1'] );
			$tel = str_replace( '-', '', $usces_entries['customer']['tel'] );
			$schksum = $p_ver.$stdate.$stran.$bkcode.$acting_opts['shopid'].$acting_opts['cshopid'].$amount.mb_convert_encoding($custmKanji, 'SJIS', 'UTF-8').$mailaddr.$tel.$acting_opts['hash_pass'];
			$schksum = htmlspecialchars( md5( $schksum ) );
			$html .= '<form id="purchase_form" action="'.$send_url.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<input type="hidden" name="p_ver" value="'.$p_ver.'">
				<input type="hidden" name="stdate" value="'.$stdate.'">
				<input type="hidden" name="stran" value="'.$stran.'">
				<input type="hidden" name="bkcode" value="'.$bkcode.'">
				<input type="hidden" name="shopid" value="'.$acting_opts['shopid'].'">
				<input type="hidden" name="cshopid" value="'.$acting_opts['cshopid'].'">
				<input type="hidden" name="amount" value="'.$amount.'">
				<input type="hidden" name="custmKanji" value="'.$custmKanji.'">
				<input type="hidden" name="mailaddr" value="'.$mailaddr.'">
				<input type="hidden" name="tel" value="'.$tel.'">
				<input type="hidden" name="schksum" value="'.$schksum.'">
				';
			$html .= '<input type="hidden" name="dummy" value="&#65533;" />';
			$html .= '<div class="send"><input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"'.apply_filters('usces_filter_confirm_nextbutton', ' onClick="document.charset=\'Shift_JIS\';"').$purchase_disabled.' /></div>';
			$html = apply_filters( 'usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled );
			$html .= '</form>';
			$html .= '<form action="'.USCES_CART_URL.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"'.apply_filters('usces_filter_confirm_prebutton', NULL).' /></div>';
			$html = apply_filters( 'usces_filter_confirm_inform_back', $html );
			$html .= '</form>';
			break;
//20130225ysk end
//20131220ysk start
		case 'acting_anotherlane_card'://カード決済(アナザーレーン)
			$usces->save_order_acting_data( $rand );
			$acting_opts = $usces->options['acting_settings']['anotherlane'];
			$amount = apply_filters( 'usces_filter_acting_amount', usces_crform( $usces_entries['order']['total_full_price'], false, false, 'return', false), $acting_flag );
			$note = ( 46 < mb_strlen($usces_entries['order']['note']) ) ? mb_substr( $usces_entries['order']['note'], 0, 46 ).'...' : $usces_entries['order']['note'];
			$html .= '<form id="purchase_form" name="purchase_form" action="'.$acting_opts['send_url'].'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}" accept-charset="UTF-8">
				<input type="hidden" name="SiteId" value="'.$acting_opts['siteid'].'">
				<input type="hidden" name="SitePass" value="'.$acting_opts['sitepass'].'">
				<input type="hidden" name="Amount" value="'.$amount.'">
				<input type="hidden" name="TransactionId" value="">
				<input type="hidden" name="zip" value="'.esc_attr( str_replace( '-', '', $usces_entries['customer']['zipcode'] ) ).'">
				<input type="hidden" name="capital" value="'.esc_attr( $usces_entries['customer']['pref'] ).'">
				<input type="hidden" name="adr1" value="'.esc_attr( $usces_entries['customer']['address1'] ).'">
				<input type="hidden" name="adr2" value="'.esc_attr( $usces_entries['customer']['address2'] ).'">
				<input type="hidden" name="adr3" value="'.esc_attr( $usces_entries['customer']['address3'] ).'">
				<input type="hidden" name="name" value="'.esc_attr( mb_substr( $usces_entries['customer']['name1'].$usces_entries['customer']['name2'], 0, 25 ) ).'" />
				<input type="hidden" name="tel" value="'.esc_attr( str_replace( '-', '', $usces_entries['customer']['tel'] ) ).'" />
				<input type="hidden" name="mail" value="'.esc_attr( $usces_entries['customer']['mailaddress1'] ).'">
				<input type="hidden" name="note" value="'.esc_attr( $note ).'">
				<input type="hidden" name="rand" value="'.$rand.'">
				';
			if( 'on' == $acting_opts['quickcharge'] && $usces->is_member_logged_in() ) {
				$member = $usces->get_member();
				$html .= '<input type="hidden" name="CustomerId" value="'.esc_attr($member['ID']).'">';
			}
			$html .= '<div class="send"><input name="purchase_ali" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"'.apply_filters('usces_filter_confirm_nextbutton', ' onClick="document.charset=\'Shift_JIS\';"').$purchase_disabled.' /></div>';
			$html = apply_filters( 'usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled );
			$html .= '</form>';
			$html .= '<form action="'.USCES_CART_URL.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"'.apply_filters('usces_filter_confirm_prebutton', NULL).' /></div>';
			$html = apply_filters( 'usces_filter_confirm_inform_back', $html );
			$html .= '</form>';
			break;
//20131220ysk end
//20140206ysk start
		case 'acting_veritrans_card'://カード決済(ベリトランス)
		case 'acting_veritrans_conv'://コンビニ決済(ベリトランス)
			$acting_opts = $usces->options['acting_settings']['veritrans'];
			$usces->save_order_acting_data( $rand );
			$settlement_type = ( 'acting_veritrans_card' == $acting_flag ) ? '01' : '02';
			$amount = apply_filters( 'usces_filter_acting_amount', usces_crform( $usces_entries['order']['total_full_price'], false, false, 'return', false), $acting_flag );
			$ctx = hash_init( 'sha512' );
			$str = $acting_opts['merchanthash'].",".$acting_opts['merchant_id'].",".$settlement_type.",".$rand.",".$amount;
			hash_update( $ctx, $str );
			$hash = hash_final( $ctx, true );
			$merchanthash = bin2hex( $hash );
			$html .= '<form id="purchase_form" name="purchase_form" action="'.USCES_CART_URL.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<input type="hidden" name="MERCHANTHASH" value="'.esc_attr( $merchanthash ).'">
				<input type="hidden" name="SETTLEMENT_TYPE" value="'.esc_attr( $settlement_type ).'">
				<input type="hidden" name="ORDER_ID" value="'.esc_attr( $rand ).'">
				<input type="hidden" name="AMOUNT" value="'.esc_attr( $amount ).'">
				';
			$html .= '<div class="send"><input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"'.apply_filters('usces_filter_confirm_nextbutton', '').$purchase_disabled.' /></div>';
			$html = apply_filters( 'usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled );
			$html .= '</form>';
			$html .= '<form action="'.USCES_CART_URL.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"'.apply_filters('usces_filter_confirm_prebutton', NULL).' /></div>';
			$html = apply_filters( 'usces_filter_confirm_inform_back', $html );
			$html .= '</form>';
			break;
//20140206ysk end

		default:
			$html .= '<form id="purchase_form" action="' . apply_filters('usces_filter_acting_url', USCES_CART_URL) . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" value="'.__('Back', 'usces').'"'.apply_filters('usces_filter_confirm_prebutton', NULL).' />
				<input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"'.$purchase_disabled.' /></div>';
			$html = apply_filters('usces_filter_confirm_inform', $html, $payments, $acting_flag, $rand, $purchase_disabled);
			$html .= '</form>';
	}
}
?>