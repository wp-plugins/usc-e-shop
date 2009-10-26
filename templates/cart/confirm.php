<?php
$html = '<div id="info-confirm">
	
	<div class="usccart_navi">
	<ol class="ucart">
	<li class="ucart">' . __('1.Cart','usces') . '</li>
	<li class="ucart">' . __('2.Customer Info','usces') . '</li>
	<li class="ucart">' . __('3.Deli. & Pay.','usces') . '</li>
	<li class="ucart usccart_confirm">' . __('4.Confirm','usces') . '</li>
	</ol>
	</div>';

$html .= '<div class="header_explanation">';
$header = '';
$html .= apply_filters('usces_filter_confirm_page_header', $header);
$html .= '</div>';

$html .= '<div id="cart">
<table cellspacing="0" id="cart_table">
		<thead>
		<tr>
			<th scope="row" class="num">' . __('No.','usces') . '</th>
			<th class="thumbnail">　</th>
			<th>商品</th>
			<th class="price">単価</th>
			<th class="quantity">数量</th>
			<th class="subtotal">金額</th>
			<th class="action"></th>
		</tr>
		</thead>
		<tbody>';

$member = $this->get_member();
$usces_entries = $this->cart->get_entry();
$this->set_cart_fees( $member, $usces_entries );

$cart = $this->cart->get_cart();

for($i=0; $i<count($cart); $i++) { 
	$cart_row = $cart[$i];
	$post_id = $cart_row['post_id'];
	$sku = $cart_row['sku'];
	$quantity = $cart_row['quantity'];
	$options = $cart_row['options'];
	$itemCode = $this->getItemCode($post_id);
	$itemName = $this->getItemName($post_id);
	$skuPrice = $cart_row['price'];
	$pictids = $this->get_pictids($itemCode);
	if (!empty($options)) {
		$optstr = implode(',', $options);
	} else { 
		$optstr =  '';
		$options =  array();
	}

	$html .= '<tr>
		<td>' . ($i + 1) . '</td>
		<td>' . wp_get_attachment_image( $pictids[0], array(60, 60), true ) . '</td>
		<td class="aleft">' . $itemName . '&nbsp;' . $itemCode . '&nbsp;' . $sku . '<br />' . $optstr . '</td>
		<td class="aright">' . number_format($skuPrice) . '</td>
		<td>' . $cart_row['quantity'] . '</td>
		<td class="aright">' . number_format($skuPrice * $cart_row['quantity']) . '</td>
		<td>';
	$html = apply_filters('usces_additional_confirm', $html, array($i, $post_id, $sku));
	$html .= '</td>
	</tr>';
} 

$html .= '</tbody>
	<tfoot>
	<tr>
		<th colspan="5" class="aright">商品合計</th>
		<th class="aright">' . number_format($usces_entries['order']['total_items_price']) . '</th>
		<th>&nbsp;</th>
	</tr>';
if( $this->options['membersystem_state'] == 'activate' &&  $this->options['membersystem_point'] == 'activate' && !empty($usces_entries['order']['usedpoint']) ) {
	$html .= '<tr>
		<td colspan="5" class="aright">使用ポイント</td>
		<td class="aright" style="color:#FF0000">' . number_format($usces_entries['order']['usedpoint']) . '</td>
		<td>&nbsp;</td>
	</tr>';
}
if( !empty($usces_entries['order']['discount']) ) {
	$html .= '<tr>
		<td colspan="5" class="aright">キャンペーン割引</td>
		<td class="aright" style="color:#FF0000">' . number_format($usces_entries['order']['discount']) . '</td>
		<td>&nbsp;</td>
	</tr>';
}
$html .= '<tr>
	<td colspan="5" class="aright">送料</td>
	<td class="aright">' . number_format($usces_entries['order']['shipping_charge']) . '</td>
	<td>&nbsp;</td>
	</tr>';
if( !empty($usces_entries['order']['cod_fee']) ) {
	$html .= '<tr>
		<td colspan="5" class="aright">代引手数料</td>
		<td class="aright">' . number_format($usces_entries['order']['cod_fee']) . '</td>
		<td>&nbsp;</td>
	</tr>';
}
if( !empty($usces_entries['order']['tax']) ) {
	$html .= '<tr>
		<td colspan="5" class="aright">消費税</td>
		<td class="aright">' . number_format($usces_entries['order']['tax']) . '</td>
		<td>&nbsp;</td>
	</tr>';
}
$html .= '<tr>
	<th colspan="5" class="aright">総合計金額</th>
	<th class="aright">' . number_format($usces_entries['order']['total_full_price']) . '</th>
	<th>&nbsp;</th>
	</tr>
	</tfoot>
	</table>';
if( $this->options['membersystem_state'] == 'activate' &&  $this->options['membersystem_point'] == 'activate' &&  $this->is_member_logged_in() ) {
	$html .= '<form action="' . USCES_CART_URL . '" method="post">
		<div class="error_message">' . $this->error_message . '</div>
		<table cellspacing="0" id="point_table">
		<tr>
		<td>現在のポイント</td>
		<td><span class="point">' . $member['point'] . '</span>pt</td>
		</tr>
		<tr>
		<td>利用するポイント</td>
		<td><input name="order[usedpoint]" class="used_point" type="text" value="' . $usces_entries['order']['usedpoint'] . '" />pt</td>
		</tr>
		<tr>
		<td colspan="2"><input name="use_point" type="submit" value="ポイントを使用する" /></td>
		</tr>
	</table>
	</form>';
}
 
$html .= '</div>
	<table id="confirm_table">
	<tr class="ttl">
	<td colspan="2"><h3>お客様情報</h3></td>
	</tr>
	<tr>
	<th>メールアドレス</th>
	<td>' . $usces_entries['customer']['mailaddress1'] . '</td>
	</tr>
	<tr class="bdc">
	<th>お名前</th>
	<td>' . $usces_entries['customer']['name1'] . ' ' . $usces_entries['customer']['name2'] . '</td>
	</tr>
	<tr>
	<th>フリガナ</th>
	<td>' . $usces_entries['customer']['name3'] . ' ' . $usces_entries['customer']['name4'] . '</td>
	</tr>
	<tr class="bdc">
	<th>郵便番号</th>
	<td>' . $usces_entries['customer']['zipcode'] . '</td>
	</tr>
	<tr>
	<th>都道府県</th>
	<td>' . $usces_entries['customer']['pref'] . '</td>
	</tr>
	<tr class="bdc">
	<th>市区郡町村</th>
	<td>' . $usces_entries['customer']['address1'] . '</td>
	</tr>
	<tr>
	<th>番地</th>
	<td>' . $usces_entries['customer']['address2'] . '</td>
	</tr>
	<tr class="bdc">
	<th>マンション･ビル名</th>
	<td>' . $usces_entries['customer']['address3'] . '</td>
	</tr>
	<tr>
	<th>電話番号</th>
	<td>' . $usces_entries['customer']['tel'] . '</td>
	</tr>
	<tr class="bdc">
	<th>FAX番号</th>
	<td>' . $usces_entries['customer']['fax'] . '</td>
	</tr>
	<tr class="ttl">';
if( EX_DLSELLER !== true ){
	$html .= '<td colspan="2"><h3>配送先情報</h3></td>
		</tr>
		<tr>
		<th>お名前</th><td>' . $usces_entries['delivery']['name1'] . ' ' . $usces_entries['delivery']['name2'] . '</td>
		</tr>
		<tr class="bdc">
		<th>フリガナ</th><td>' . $usces_entries['delivery']['name3'] . ' ' . $usces_entries['delivery']['name4'] . '</td>
		</tr>
		<tr>
		<th>郵便番号</th><td>' . $usces_entries['delivery']['zipcode'] . '</td>
		</tr>
		<tr class="bdc">
		<th>都道府県</th><td>' . $usces_entries['delivery']['pref'] . '</td>
		</tr>
		<tr>
		<th>市区郡町村</th><td>' . $usces_entries['delivery']['address1'] . '</td>
		</tr>
		<tr class="bdc">
		<th>番地</th><td>' . $usces_entries['delivery']['address2'] . '</td>
		</tr>
		<tr>
		<th>マンション･ビル名</th><td>' . $usces_entries['delivery']['address3'] . '</td>
		</tr>
		<tr class="bdc">
		<th>電話番号</th><td>' . $usces_entries['delivery']['tel'] . '</td>
		</tr>
		<tr>
		<th>FAX番号</th><td>' . $usces_entries['delivery']['fax'] . '</td>
		</tr>
		<tr>';
}
$html .= '<td class="ttl" colspan="2"><h3>その他</h3></td>
	</tr>';
if( EX_DLSELLER !== true ){
	$html .= '<tr>
		<th>配送方法</th><td>' . usces_delivery_method_name( $usces_entries['order']['delivery_method'], 'return' ) . '</td>
		</tr>
		<tr>
		<th>配送希望時間帯</th><td>' . $usces_entries['order']['delivery_time'] . '</td>
		</tr>';
}	
$html .= '<tr class="bdc">
	<th>お支払方法</th><td>' . $usces_entries['order']['payment_name'] . '</td>
	</tr>
	<tr>
	<th>備考</th><td>' . nl2br($usces_entries['order']['note']) . '</td>
	</tr>
	</table>';

$payments = usces_get_payments_by_name($usces_entries['order']['payment_name']);
if( $payments['settlement'] != 'acting' ){
	$html .= '<form action="' . USCES_CART_URL . '" method="post">
		<div class="send"><input name="backDelivery" type="submit" value="お届けお支払方法入力に戻る" />&nbsp;&nbsp;
		<input name="purchase" type="submit" value="上記内容で注文する" /></div>
		</form>';
}else{
	//$notify_url = urlencode(USCES_CART_URL . '&purchase');
	$send_item_code = $this->getItemCode($cart[0]['post_id']);
	$send_item_name = $this->getItemName($cart[0]['post_id']);
	if( count($cart) > 1 ) $send_item_name .= ' その他';
	switch($payments['module']){
		case 'paypal.php':
			require_once(USCES_PLUGIN_DIR . "/settlement/paypal.php");
			$html .= '<form action="' . USCES_CART_URL . '" method="post">
				<div class="send"><input name="backDelivery" type="submit" value="　　戻　る　　" />&nbsp;&nbsp;</div>
				</form>
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="' . $usces_paypal_business . '">
				<input type="hidden" name="custom" value="' . $this->get_uscesid() . '">
				<input type="hidden" name="lc" value="JP">
				<input type="hidden" name="item_name" value="">
				<input type="hidden" name="item_number" value="">
				<input type="hidden" name="amount" value="' . $usces_entries['order']['total_full_price'] . '">
				<input type="hidden" name="currency_code" value="JPY">
				<input type="hidden" name="button_subtype" value="products">
				<input type="hidden" name="tax_rate" value="0.000">
				<input type="hidden" name="shipping" value="0">
				<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHostedGuest">
				<div class="send"><input type="image" src="https://www.paypal.com/ja_JP/JP/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - オンラインで安全・簡単にお支払い">
				<img alt="" border="0" src="https://www.paypal.com/ja_JP/i/scr/pixel.gif" width="1" height="1"></div>
				</form>';
			break;
		case 'epsilon.php':
			$html .= '<form action="' . USCES_CART_URL . '" method="post">
				<input type="hidden" name="user_id" value="' . $member['ID'] . '">
				<input type="hidden" name="user_name" value="' . $usces_entries['customer']['name1'] . ' ' . $usces_entries['customer']['name2'] . '">
				<input type="hidden" name="user_mail_add" value="' . $usces_entries['customer']['mailaddress1'] . '">';
			if( 1 < count($cart) ) {
				$html .= '<input type="hidden" name="item_code" value="99999999">
					<input type="hidden" name="item_name" value="' . $send_item_name . '、他">';
			}else{
				$html .= '<input type="hidden" name="item_code" value="' . $send_item_code . '">
					<input type="hidden" name="item_name" value="' . $send_item_name . '">';
			}
			$html .= '<input type="hidden" name="item_price" value="' . $usces_entries['order']['total_full_price'] . '">
				<div class="send"><input name="backDelivery" type="submit" value="　　戻　る　　" />&nbsp;&nbsp;
				<input name="purchase" type="submit" value="上記内容で注文する" /></div>
				</form>';
			break;
		default:
			$html .= '<form action="' . USCES_CART_URL . '" method="post">
				<div class="send"><input name="backDelivery" type="submit" value="　　戻　る　　" />&nbsp;&nbsp;
				<input name="purchase" type="submit" value="上記内容で注文する" /></div>
				</form>';
	}
}

$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_confirm_page_footer', $footer);
$html .= '</div>';

$html .= '</div>';
	
?>
