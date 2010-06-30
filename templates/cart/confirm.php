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
$html .= '<div class="error_message">' . $this->error_message . '</div>';

$html .= '<div id="cart">
<table cellspacing="0" id="cart_table">
		<thead>
		<tr>
			<th scope="row" class="num">' . __('No.','usces') . '</th>
			<th class="thumbnail">&nbsp;&nbsp;</th>
			<th>' . __('Items','usces') . '</th>
			<th class="price">' . __('Unit price','usces') . '</th>
			<th class="quantity">'.__('Quantity', 'usces').'</th>
			<th class="subtotal">'.__('Amount', 'usces').'</th>
			<th class="action"></th>
		</tr>
		</thead>
		<tbody>';

$member = $this->get_member();
$memid = ( empty($member['ID']) ) ? 999999999 : $member['ID'];
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
	$cartItemName = $this->getCartItemName($post_id, $sku);
	$skuPrice = $cart_row['price'];
	$pictids = $this->get_pictids($itemCode);
	if (!empty($options)) {
//		$optstr = implode(',', $options);
	} else { 
		$optstr =  '';
		$options =  array();
	}

	$html .= '<tr>
		<td>' . ($i + 1) . '</td>
		<td>';
	$cart_thumbnail = wp_get_attachment_image( $pictids[0], array(60, 60), true );
	$html .= apply_filters('usces_filter_cart_thumbnail', $cart_thumbnail, $post_id, $pictids[0], $i);
	$html .= '</td><td class="aleft">' . $cartItemName . '<br />';
	if( is_array($options) && count($options) > 0 ){
		foreach($options as $key => $value){
			$html .= htmlspecialchars($key) . ' : ' . htmlspecialchars($value) . "<br />\n"; 
		}
	}
	$html .= '</td>
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
		<th colspan="5" class="aright">'.__('total items', 'usces').'</th>
		<th class="aright">' . number_format($usces_entries['order']['total_items_price']) . '</th>
		<th>&nbsp;</th>
	</tr>';
if( $this->options['membersystem_state'] == 'activate' &&  $this->options['membersystem_point'] == 'activate' && !empty($usces_entries['order']['usedpoint']) ) {
	$html .= '<tr>
		<td colspan="5" class="aright">'.__('Used points', 'usces').'</td>
		<td class="aright" style="color:#FF0000">' . number_format($usces_entries['order']['usedpoint']) . '</td>
		<td>&nbsp;</td>
	</tr>';
}
if( !empty($usces_entries['order']['discount']) ) {
	$html .= '<tr>
		<td colspan="5" class="aright">'.apply_filters('usces_confirm_discount_label', __('Campaign disnount', 'usces')).'</td>
		<td class="aright" style="color:#FF0000">' . number_format($usces_entries['order']['discount']) . '</td>
		<td>&nbsp;</td>
	</tr>';
}
$html .= '<tr>
	<td colspan="5" class="aright">'.__('Shipping', 'usces').'</td>
	<td class="aright">' . number_format($usces_entries['order']['shipping_charge']) . '</td>
	<td>&nbsp;</td>
	</tr>';
if( !empty($usces_entries['order']['cod_fee']) ) {
	$html .= '<tr>
		<td colspan="5" class="aright">'.__('COD fee', 'usces').'</td>
		<td class="aright">' . number_format($usces_entries['order']['cod_fee']) . '</td>
		<td>&nbsp;</td>
	</tr>';
}
if( !empty($usces_entries['order']['tax']) ) {
	$html .= '<tr>
		<td colspan="5" class="aright">'.__('consumption tax', 'usces').'</td>
		<td class="aright">' . number_format($usces_entries['order']['tax']) . '</td>
		<td>&nbsp;</td>
	</tr>';
}
$html .= '<tr>
	<th colspan="5" class="aright">'.__('Total Amount', 'usces').'</th>
	<th class="aright">' . number_format($usces_entries['order']['total_full_price']) . '</th>
	<th>&nbsp;</th>
	</tr>
	</tfoot>
	</table>';
if( $this->options['membersystem_state'] == 'activate' &&  $this->options['membersystem_point'] == 'activate' &&  $this->is_member_logged_in() ) {
	$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
		<div class="error_message">' . $this->error_message . '</div>
		<table cellspacing="0" id="point_table">
		<tr>
		<td>'.__('The current point', 'usces').'</td>
		<td><span class="point">' . $member['point'] . '</span>pt</td>
		</tr>
		<tr>
		<td>'.__('Points you are using here', 'usces').'</td>
		<td><input name="order[usedpoint]" class="used_point" type="text" value="' . $usces_entries['order']['usedpoint'] . '" />pt</td>
		</tr>
		<tr>
		<td colspan="2"><input name="use_point" type="submit" value="'.__('Use the points', 'usces').'" /></td>
		</tr>
	</table>';
	$html = apply_filters('usces_filter_confirm_point_inform', $html);
	$html .= '</form>';
}
 
$html .= '</div>
	<table id="confirm_table">
	<tr class="ttl">
	<td colspan="2"><h3>'.__('Customer Information', 'usces').'</h3></td>
	</tr>
	<tr>
	<th>'.__('e-mail adress', 'usces').'</th>
	<td>' . $usces_entries['customer']['mailaddress1'] . '</td>
	</tr>
	<tr class="bdc">
	<th>'.__('Full name', 'usces').'</th>
	<td>' . $usces_entries['customer']['name1'] . ' ' . $usces_entries['customer']['name2'] . '</td>
	</tr>';
if( USCES_JP ){
	$html .= '<tr>
	<th>'.__('furigana', 'usces').'</th>
	<td>' . $usces_entries['customer']['name3'] . ' ' . $usces_entries['customer']['name4'] . '</td>
	</tr>';
}
$html .= '<tr class="bdc">
	<th>'.__('Zip/Postal Code', 'usces').'</th>
	<td>' . $usces_entries['customer']['zipcode'] . '</td>
	</tr>
	<tr>
	<th>'.__('Province', 'usces').'</th>
	<td>' . $usces_entries['customer']['pref'] . '</td>
	</tr>
	<tr class="bdc">
	<th>'.__('city', 'usces').'</th>
	<td>' . $usces_entries['customer']['address1'] . '</td>
	</tr>
	<tr>
	<th>'.__('numbers', 'usces').'</th>
	<td>' . $usces_entries['customer']['address2'] . '</td>
	</tr>
	<tr class="bdc">
	<th>'.__('building name', 'usces').'</th>
	<td>' . $usces_entries['customer']['address3'] . '</td>
	</tr>
	<tr>
	<th>'.__('Phone number', 'usces').'</th>
	<td>' . $usces_entries['customer']['tel'] . '</td>
	</tr>
	<tr class="bdc">
	<th>'.__('FAX number', 'usces').'</th>
	<td>' . $usces_entries['customer']['fax'] . '</td>
	</tr>
	<tr class="ttl">';
$html .= '<td colspan="2"><h3>'.__('Shipping address information', 'usces').'</h3></td>
	</tr>
	<tr>
	<th>'.__('Full name', 'usces').'</th><td>' . $usces_entries['delivery']['name1'] . ' ' . $usces_entries['delivery']['name2'] . '</td>
	</tr>';
if( USCES_JP ){
	$html .= '<tr class="bdc">
	<th>'.__('furigana', 'usces').'</th><td>' . $usces_entries['delivery']['name3'] . ' ' . $usces_entries['delivery']['name4'] . '</td>
	</tr>';
}
$html .= '<tr>
	<th>'.__('Zip/Postal Code', 'usces').'</th><td>' . $usces_entries['delivery']['zipcode'] . '</td>
	</tr>
	<tr class="bdc">
	<th>'.__('Province', 'usces').'</th><td>' . $usces_entries['delivery']['pref'] . '</td>
	</tr>
	<tr>
	<th>'.__('city', 'usces').'</th><td>' . $usces_entries['delivery']['address1'] . '</td>
	</tr>
	<tr class="bdc">
	<th>'.__('numbers', 'usces').'</th><td>' . $usces_entries['delivery']['address2'] . '</td>
	</tr>
	<tr>
	<th>'.__('building name', 'usces').'</th><td>' . $usces_entries['delivery']['address3'] . '</td>
	</tr>
	<tr class="bdc">
	<th>'.__('Phone number', 'usces').'</th><td>' . $usces_entries['delivery']['tel'] . '</td>
	</tr>
	<tr>
	<th>'.__('FAX number', 'usces').'</th><td>' . $usces_entries['delivery']['fax'] . '</td>
	</tr>
	<tr>';
$html .= '<td class="ttl" colspan="2"><h3>'.__('Others', 'usces').'</h3></td>
	</tr>';
$html .= '<tr>
	<th>'.__('shipping option', 'usces').'</th><td>' . usces_delivery_method_name( $usces_entries['order']['delivery_method'], 'return' ) . '</td>
	</tr>
	<tr class="bdc">
	<th>'.__('Delivery Time', 'usces').'</th><td>' . $usces_entries['order']['delivery_time'] . '</td>
	</tr>';
$html .= '<tr>
	<th>'.__('payment method', 'usces').'</th><td>' . $usces_entries['order']['payment_name'] . '</td>
	</tr>
	<tr class="bdc">
	<th>'.__('Notes', 'usces').'</th><td>' . nl2br($usces_entries['order']['note']) . '</td>
	</tr>
	</table>';

$payments = usces_get_payments_by_name($usces_entries['order']['payment_name']);
if( 'acting' != $payments['settlement']  || 0 == $usces_entries['order']['total_full_price'] ){
	$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
		<div class="send"><input name="backDelivery" type="submit" value="'.__('Back to payment method page.', 'usces').'" />&nbsp;&nbsp;
		<input name="purchase" type="submit" value="'.__('Checkout', 'usces').'" /></div>';
}else{
	//$notify_url = urlencode(USCES_CART_URL . '&purchase');
	$send_item_code = $this->getItemCode($cart[0]['post_id']);
	$send_item_name = $this->getItemName($cart[0]['post_id']);
	if( count($cart) > 1 ) $send_item_name .= ' '.__('Others', 'usces');
	switch($payments['module']){
		case 'paypal.php':
			require_once($this->options['settlement_path'] . "paypal.php");
			$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" value="'.__('Back', 'usces').'" />&nbsp;&nbsp;</div>';
			$html = apply_filters('usces_filter_confirm_inform', $html);
			$html .= '</form>
				<form action="https://' . $usces_paypal_url . '/cgi-bin/webscr" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="' . $usces_paypal_business . '">
				<input type="hidden" name="custom" value="' . $this->get_uscesid() . '">
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
				<input type="hidden" name="notify_url" value="' . USCES_CART_URL . '&acting_return=paypal_ipn&usces=' . $this->get_uscesid() . '">
				<input type="hidden" name="button_subtype" value="products">
				<input type="hidden" name="tax_rate" value="0.000">
				<input type="hidden" name="shipping" value="0">
				<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHostedGuest">
				<div class="send"><input type="image" src="https://www.paypal.com/ja_JP/JP/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal">
				<img alt="" border="0" src="https://www.paypal.com/ja_JP/i/scr/pixel.gif" width="1" height="1"></div>';
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
				<div class="send"><input name="backDelivery" type="submit" value="'.__('Back', 'usces').'" />&nbsp;&nbsp;
				<input name="purchase" type="submit" value="'.__('Checkout', 'usces').'" /></div>';
			break;
		default:
			$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<div class="send"><input name="backDelivery" type="submit" value="'.__('Back', 'usces').'" />&nbsp;&nbsp;
				<input name="purchase" type="submit" value="'.__('Checkout', 'usces').'" /></div>';
	}
}

$html = apply_filters('usces_filter_confirm_inform', $html);
$html .= '</form><div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_confirm_page_footer', $footer);
$html .= '</div>';

$html .= '</div>';
?>
