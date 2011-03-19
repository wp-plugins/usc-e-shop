<?php
$html = '<div id="inside-cart">

<div class="usccart_navi">
<ol class="ucart">
<li class="ucart usccart usccart_cart">' . __('1.Cart','usces') . '</li>
<li class="ucart usccustomer">' . __('2.Customer Info','usces') . '</li>
<li class="ucart uscdelivery">' . __('3.Deli. & Pay.','usces') . '</li>
<li class="ucart uscconfirm">' . __('4.Confirm','usces') . '</li>
</ol>
</div>';

$html .= '<div class="header_explanation">';
$header = '';
$html .= apply_filters('usces_filter_cart_page_header', $header);
$html .= '</div>';

$html .= '<div class="error_message">' . $this->error_message . '</div>

<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">';

if( usces_is_cart() ) {
	
	$html .= '<div id="cart">';
	
	$button = '<div class="upbutton">' . __('Press the `update` button when you change the amount of items.','usces') . '<input name="upButton" type="submit" value="' . __('Quantity renewal','usces') . '" onclick="return uscesCart.upCart()"  /></div>';
	$html .= apply_filters('usces_filter_cart_upbutton', $button);
	
	$html .= '<table cellspacing="0" id="cart_table">
		<thead>
		<tr>
			<th scope="row" class="num">No.</th>
			<th class="thumbnail"> </th>
			<th>' . __('item name','usces') . '</th>
			<th class="quantity">' . __('Unit price','usces') . '</th>
			<th class="quantity">' . __('Quantity','usces') . '</th>
			<th class="subtotal">' . __('Amount','usces') . usces_guid_tax('return') . '</th>
			<th class="stock">' . __('stock status','usces') . '</th>
			<th class="action"> </th>
		</tr>
		</thead>
		<tbody>';
	$cart = $this->cart->get_cart();
	$usces_gp = 0;
	for($i=0; $i<count($cart); $i++) { 
		$cart_row = $cart[$i];
		$post_id = $cart_row['post_id'];
		$sku = esc_attr($cart_row['sku']);
		$quantity = $cart_row['quantity'];
		$options = $cart_row['options'];
		$advance = $this->cart->wc_serialize($cart_row['advance']);
		$itemCode = $this->getItemCode($post_id);
		$itemName = $this->getItemName($post_id);
		$cartItemName = $this->getCartItemName($post_id, $cart_row['sku']);
		$itemRestriction = $this->getItemRestriction($post_id);
		$skuPrice = $cart_row['price'];
		$skuZaikonum = $this->getItemZaikonum($post_id, $cart_row['sku']);
		$stockid = $this->getItemZaikoStatusId($post_id, $cart_row['sku']);
		$stock = $this->getItemZaiko($post_id, $cart_row['sku']);
		$red = (in_array($stock, array(__('sellout','usces'), __('Out Of Stock','usces'), __('Out of print','usces')))) ? 'class="signal_red"' : '';
		$pictids = $this->get_pictids($itemCode);
		if (!empty($options)) {
//			$optstr = implode(',', $options);
		} else { 
			$optstr =  '';
			$options =  array();
		}
				
		$html .= '<tr>
			<td>' . ($i + 1) . '</td>
			<td>';
			$cart_thumbnail = '<a href="' . get_permalink($post_id) . '">' . wp_get_attachment_image( $pictids[0], array(60, 60), true ) . '</a>';
			$html .= apply_filters('usces_filter_cart_thumbnail', $cart_thumbnail, $post_id, $pictids[0], $i);
			$html .= '</td><td class="aleft">' . esc_html($cartItemName) . '<br />';
		if( is_array($options) && count($options) > 0 ){
			foreach($options as $key => $value){
				if( !empty($key) )
					$html .= esc_html($key) . ' : ' . nl2br(esc_html(urldecode($value))) . "<br />\n"; 
			}
		}
		$html .= '</td>
			<td class="aright">';
		if( usces_is_gptekiyo($post_id, $cart_row['sku'], $quantity) ) {
			$usces_gp = 1;
			$Business_pack_mark = '<img src="' . get_template_directory_uri() . '/images/gp.gif" alt="' . __('Business package discount','usces') . '" /><br />';
			$html .= apply_filters('usces_filter_itemGpExp_cart_mark', $Business_pack_mark);
		}
		$html .= usces_crform($skuPrice, true, false, 'return') . '
			</td>
			<td><input name="quant[' . $i . '][' . $post_id . '][' . $sku . ']" class="quantity" type="text" value="' . esc_attr($cart_row['quantity']) . '" /></td>
			<td class="aright">' . usces_crform(($skuPrice * $cart_row['quantity']), true, false, 'return') . '</td>
			<td ' . $red . '>' . $stock . '</td>
			<td>';
		foreach($options as $key => $value){
			$html .= '<input name="itemOption[' . $i . '][' . $post_id . '][' . $sku . '][' . $key . ']" type="hidden" value="' . $value . '" />';
		}
		$html .= '<input name="itemRestriction[' . $i . ']" type="hidden" value="' . $itemRestriction . '" />
			<input name="stockid[' . $i . ']" type="hidden" value="' . $stockid . '" />
			<input name="itempostid[' . $i . ']" type="hidden" value="' . $post_id . '" />
			<input name="itemsku[' . $i . ']" type="hidden" value="' . $sku . '" />
			<input name="zaikonum[' . $i . '][' . $post_id . '][' . $sku . ']" type="hidden" value="' . esc_attr($skuZaikonum) . '" />
			<input name="skuPrice[' . $i . '][' . $post_id . '][' . $sku . ']" type="hidden" value="' . esc_attr($skuPrice) . '" />
			<input name="advance[' . $i . '][' . $post_id . '][' . $sku . ']" type="hidden" value="' . esc_attr($advance) . '" />
			<input name="delButton[' . $i . '][' . $post_id . '][' . $sku . ']" class="delButton" type="submit" value="' . __('Delete','usces') . '" />
			</td>
		</tr>';
	}

	$html .= '</tbody>
		<tfoot>
		<tr>
			<th colspan="5" scope="row" class="aright">' . __('total items','usces') . usces_guid_tax('return') . '</th>
			<th class="aright">' . usces_crform($this->get_total_price(), true, false, 'return') . '</th>
			<th colspan="2">&nbsp;</th>
		</tr>
		</tfoot>
	</table>
	<div class="currency_code">' . __('Currency','usces') . ' : ' . __(usces_crcode( 'return' ), 'usces') . '</div>';
	if( $usces_gp ) {
		$Business_pack_discount = '<img src="' . get_template_directory_uri() . '/images/gp.gif" alt="' . __('Business package discount','usces') . '" /><br />' . __('The price with this mark applys to Business pack discount.','usces');
		$html .= apply_filters('usces_filter_itemGpExp_cart_message', $Business_pack_discount);
	}
	$html .= '</div>';

} else {
	$html .= '<div class="no_cart">' . __('There are no items in your cart.','usces') . '</div>';
}

$html .= $content;

$html .= '<div class="send">';
if($this->use_js){
	$html .= '<input name="previous" type="button" id="previouscart" class="continue_shopping_button" value="' . __('continue shopping','usces') . '"' . apply_filters('usces_filter_cart_prebutton', ' onclick="uscesCart.previousCart();"') . ' />&nbsp;&nbsp;';
	if( usces_is_cart() ) {
		$html .= '<input name="customerinfo" type="submit" class="to_customerinfo_button" value="' . __(' Next ','usces') . '"' . apply_filters('usces_filter_cart_nextbutton', ' onclick="return uscesCart.cartNext();"') . ' />';
	}
}else{
	$html .= '<a href="' . get_bloginfo('home') . '" class="continue_shopping_button">' . __('continue shopping','usces') . '</a>&nbsp;&nbsp;';
	if( usces_is_cart() ) {
		$html .= '<input name="customerinfo" type="submit" class="to_customerinfo_button" value="' . __(' Next ','usces') . '"' . apply_filters('usces_filter_cart_nextbutton', NULL) . ' />';
	}
}
$html .= '</div>';
$html = apply_filters('usces_filter_cart_inform', $html);
$html .= '</form>';

$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_cart_page_footer', $footer);
$html .= '</div>';
	
$html .= '</div>';
?>
