<?php
if(isset($this))
	$usces = &$this;

$payments = usces_get_system_option( 'usces_payment_method', 'sort' );
$payments = apply_filters( 'usces_filter_available_payment_method', $payments );
foreach ( (array)$payments as $id => $array ) {
	if( !empty( $array['settlement'] ) ){

		switch( $array['settlement'] ){
			case 'acting_zeus_card':
				$paymod_id = 'zeus';

				if( 'on' != $usces->options['acting_settings'][$paymod_id]['card_activate'] 
					|| 'on' != $usces->options['acting_settings'][$paymod_id]['activate'] )
					continue;

				$cnum1 = isset( $_POST['cnum1'] ) ? esc_html($_POST['cnum1']) : '';
				$cnum2 = isset( $_POST['cnum2'] ) ? esc_html($_POST['cnum2']) : '';
				$cnum3 = isset( $_POST['cnum3'] ) ? esc_html($_POST['cnum3']) : '';
				$cnum4 = isset( $_POST['cnum4'] ) ? esc_html($_POST['cnum4']) : '';
				$securecode = isset( $_POST['securecode'] ) ? esc_html($_POST['securecode']) : '';
				$expyy = isset( $_POST['expyy'] ) ? esc_html($_POST['expyy']) : '';
				$expmm = isset( $_POST['expmm'] ) ? esc_html($_POST['expmm']) : '';
				$username = isset( $_POST['username'] ) ? esc_html($_POST['username']) : '';
				$howpay = isset( $_POST['howpay'] ) ? esc_html($_POST['howpay']) : '1';
				$cbrand = isset( $_POST['cbrand'] ) ? esc_html($_POST['cbrand']) : '';
				$div = isset( $_POST['div'] ) ? esc_html($_POST['div']) : '';

				$html .= '<input type="hidden" name="acting" value="zeus">'."\n";
				$html .= '<table class="customer_form" id="' . $paymod_id . '">'."\n";

				$pcid = NULL;
				if( $usces->is_member_logged_in() ){
					$member = $usces->get_member();
					if( !isset($_GET['re-enter']) ) {
						$pcid = $usces->get_member_meta_value( 'zeus_pcid', $member['ID'] );
					}
				}
				//if( 2 == $usces->options['acting_settings'][$paymod_id]['security'] && 'on' == $usces->options['acting_settings'][$paymod_id]['quickcharge'] && $pcid != NULL ){
				if( 'on' == $usces->options['acting_settings'][$paymod_id]['quickcharge'] && $pcid != NULL ){
					$html .= '<input name="cnum1" type="hidden" value="8888888888888888" />
					<input name="expyy" type="hidden" value="2010" />
					<input name="expmm" type="hidden" value="01" />
					<input name="username" type="hidden" value="QUICKCHARGE" />';

				}else{
					$html .= '<tr>
						<th scope="row">'.__('カード番号', 'usces').'<input name="acting" type="hidden" value="zeus" /></th>
						<td colspan="2"><input name="cnum1" type="text" size="16" value="' . esc_attr($cnum1) . '" />(半角数字のみ)</td>
						</tr>';
					if( 1 == $usces->options['acting_settings'][$paymod_id]['security'] ){
						$html .= '<tr>
						<th scope="row">'.__('セキュリティコード', 'usces').'</th>
						<td colspan="2"><input name="securecode" type="text" size="6" value="' . esc_attr($securecode) . '" />(半角数字のみ)</td>
						</tr>';
					}
					$html .= '<tr>
						<th scope="row">'.__('カード有効期限', 'usces').'</th>
						<td colspan="2">
						<select name="expmm">
							<option value=""' . (empty($expmm) ? ' selected="selected"' : '') . '>----</option>
							<option value="01"' . (('01' === $expmm) ? ' selected="selected"' : '') . '> 1</option>
							<option value="02"' . (('02' === $expmm) ? ' selected="selected"' : '') . '> 2</option>
							<option value="03"' . (('03' === $expmm) ? ' selected="selected"' : '') . '> 3</option>
							<option value="04"' . (('04' === $expmm) ? ' selected="selected"' : '') . '> 4</option>
							<option value="05"' . (('05' === $expmm) ? ' selected="selected"' : '') . '> 5</option>
							<option value="06"' . (('06' === $expmm) ? ' selected="selected"' : '') . '> 6</option>
							<option value="07"' . (('07' === $expmm) ? ' selected="selected"' : '') . '> 7</option>
							<option value="08"' . (('08' === $expmm) ? ' selected="selected"' : '') . '> 8</option>
							<option value="09"' . (('09' === $expmm) ? ' selected="selected"' : '') . '> 9</option>
							<option value="10"' . (('10' === $expmm) ? ' selected="selected"' : '') . '>10</option>
							<option value="11"' . (('11' === $expmm) ? ' selected="selected"' : '') . '>11</option>
							<option value="12"' . (('12' === $expmm) ? ' selected="selected"' : '') . '>12</option>
						</select>月&nbsp;<select name="expyy">
							<option value=""' . (empty($expyy) ? ' selected="selected"' : '') . '>------</option>
						';
					for($i=0; $i<10; $i++){
						$year = date('Y') - 1 + $i;
						$html .= '<option value="' . $year . '"' . (($year == $expyy) ? ' selected="selected"' : '') . '>' . $year . '</option>';
					}
					$html .= '
						</select>年</td>
						</tr>
						<tr>
						<th scope="row">'.__('カード名義', 'usces').'</th>
						<td colspan="2"><input name="username" type="text" size="30" value="' . esc_attr($username) . '" />(半角英字)</td>
						</tr>';
				}

				$html_howpay = '';
				if( 'on' == $usces->options['acting_settings'][$paymod_id]['howpay'] ){

				$html_howpay .= '
					<tr>
					<th scope="row">'.__('支払方法', 'usces').'</th>
					<td><input name="howpay" type="radio" value="1" id="howdiv1"' . (('1' === $howpay) ? ' checked' : '') . ' /><label for="howdiv1">一括払い</label></td>
					<td><input name="howpay" type="radio" value="0" id="howdiv2"' . (('0' === $howpay) ? ' checked' : '') . ' /><label for="howdiv2">分割払い</label></td>
					</tr>
					<tr id="cbrand_zeus">
					<th scope="row">'.__('カードブランド', 'usces').'</th>
					<td colspan="2">
					<select name="cbrand">
						<option value=""' . ((WCUtils::is_blank($cbrand)) ? ' selected="selected"' : '') . '>--------</option>
						<option value="1"' . (('1' === $cbrand) ? ' selected="selected"' : '') . '>JCB</option>
						<option value="1"' . (('1' === $cbrand) ? ' selected="selected"' : '') . '>VISA</option>
						<option value="1"' . (('1' === $cbrand) ? ' selected="selected"' : '') . '>MASTER</option>
						<option value="2"' . (('2' === $cbrand) ? ' selected="selected"' : '') . '>DINERS</option>
						<option value="3"' . (('3' === $cbrand) ? ' selected="selected"' : '') . '>AMEX</option>
					</select>
					</td>
					</tr>
					<tr id="div_zeus">
					<th scope="row">'.__('分割回数', 'usces').'</th>
					<td colspan="2">
					<select name="div_1" id="brand1">
						<option value="01"' . (('01' === $cbrand) ? ' selected="selected"' : '') . '>一括払い</option>
						<option value="99"' . (('99' === $cbrand) ? ' selected="selected"' : '') . '>リボ払い</option>
						<option value="03"' . (('03' === $cbrand) ? ' selected="selected"' : '') . '>3回</option>
						<option value="05"' . (('05' === $cbrand) ? ' selected="selected"' : '') . '>5回</option>
						<option value="06"' . (('06' === $cbrand) ? ' selected="selected"' : '') . '>6回</option>
						<option value="10"' . (('10' === $cbrand) ? ' selected="selected"' : '') . '>10回</option>
						<option value="12"' . (('12' === $cbrand) ? ' selected="selected"' : '') . '>12回</option>
						<option value="15"' . (('15' === $cbrand) ? ' selected="selected"' : '') . '>15回</option>
						<option value="18"' . (('18' === $cbrand) ? ' selected="selected"' : '') . '>18回</option>
						<option value="20"' . (('20' === $cbrand) ? ' selected="selected"' : '') . '>20回</option>
						<option value="24"' . (('24' === $cbrand) ? ' selected="selected"' : '') . '>24回</option>
					</select>
					<select name="div_2" id="brand2">
						<option value="01"' . (('01' === $cbrand) ? ' selected="selected"' : '') . '>一括払い</option>
						<option value="99"' . (('99' === $cbrand) ? ' selected="selected"' : '') . '>リボ払い</option>
					</select>
					<select name="div_3" id="brand3">
						<option value="01"' . (('01' === $cbrand) ? ' selected="selected"' : '') . '>一括払いのみ</option>
					</select>
					</td>
					</tr>
					';
				}
				$html .= apply_filters( 'usces_filter_delivery_secure_form_howpay', $html_howpay );
				$html .= '
				</table>';
				break;

			case 'acting_zeus_conv':
				$paymod_id = 'zeus';

				if( 'on' != $usces->options['acting_settings'][$paymod_id]['conv_activate'] 
					|| 'on' != $usces->options['acting_settings'][$paymod_id]['activate'] )
					continue;

				$pay_cvs = isset( $_POST['pay_cvs'] ) ? esc_html($_POST['pay_cvs']) : 'D001';
				$entry = $usces->cart->get_entry();
				$username = isset( $_POST['username'] ) ? esc_html($_POST['username']) : $entry['customer']['name3'].$entry['customer']['name4'];

				$html .= '
				<table class="customer_form" id="' . $paymod_id . '_conv">
					<tr>
					<th scope="row">'.__('お支払いに利用するコンビニ', 'usces').'</th>
					<td colspan="2">
					<select name="pay_cvs" id="pay_cvs_zeus">
						<option value="D001"' . (('D001' == $pay_cvs) ? ' selected="selected"' : '') . '>セブンイレブン</option>
						<option value="D002"' . (('D002' == $pay_cvs) ? ' selected="selected"' : '') . '>ローソン</option>
						<option value="D030"' . (('D030' == $pay_cvs) ? ' selected="selected"' : '') . '>ファミリーマート</option>
						<option value="D040"' . (('D040' == $pay_cvs) ? ' selected="selected"' : '') . '>サークルKサンクス</option>
						<option value="D015"' . (('D015' == $pay_cvs) ? ' selected="selected"' : '') . '>セイコーマート</option>
					</select>
					</td>
					</tr>
					<tr>
					<th scope="row"><em>＊</em>'.__('お名前', 'usces').'</th>
					<td colspan="2"><input name="username" type="text" size="30" value="'.esc_attr($username).'" />(全角カナ)</td>
					</tr>
				</table>';
				break;

			case 'acting_remise_card':
				$paymod_id = 'remise';
				$charging_type = $usces->getItemChargingType($usces_carts[0]['post_id'], $usces_carts);

				if( 'on' != $usces->options['acting_settings'][$paymod_id]['card_activate'] 
					|| 'on' != $usces->options['acting_settings'][$paymod_id]['howpay'] 
					|| 'on' != $usces->options['acting_settings'][$paymod_id]['activate']
					|| ('continue' == $charging_type || 'regular' == $charging_type) ){
					continue;
				}

				$div = isset( $_POST['div'] ) ? esc_html($_POST['div']) : '0';

				$html .= '
				<table class="customer_form" id="' . $paymod_id . '">
					<tr>
					<th scope="row">'.__('支払方法', 'usces').'</th>
					<td colspan="2">
					<select name="div" id="div_remise">
						<option value="0"' . (('0' === $div) ? ' selected="selected"' : '') . '>　一括払い</option>
						<option value="1"' . (('1' === $div) ? ' selected="selected"' : '') . '>　2回払い</option>
						<option value="2"' . (('2' === $div) ? ' selected="selected"' : '') . '>　リボ払い</option>
					</select>
					</td>
					</tr>
				</table>';
				break;
		}
	}
}
?>
