<?php
$html = '<div id="inside-cart">

<div class="usccart_navi">
<ol class="ucart">
<li class="ucart usccart_cart">' . __('1.Cart','usces') . '</li>
<li class="ucart">' . __('2.Customer Info','usces') . '</li>
<li class="ucart">' . __('3.Deli. & Pay.','usces') . '</li>
<li class="ucart">' . __('4.Confirm','usces') . '</li>
</ol>
</div>';

$html .= '<div class="header_explanation">';
$header = '';
$html .= apply_filters('usces_filter_cart_page_header', $header);
$html .= '</div>';

$html .= '<div class="error_message">' . $usces->error_message . '</div>

<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">';

if($this->cart->num_row() > 0) {
	
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
			<th class="subtotal">' . __('Amount','usces') . $this->getGuidTax() . '</th>
			<th class="stock">' . __('stock status','usces') . '</th>
			<th class="action">　</th>
		</tr>
		</thead>
		<tbody>';
	$cart = $this->cart->get_cart();
	$usces_gp = 0;
	for($i=0; $i<count($cart); $i++) { 
		$cart_row = $cart[$i];
		$post_id = $cart_row['post_id'];
		$sku = $cart_row['sku'];
		$quantity = $cart_row['quantity'];
		$options = $cart_row['options'];
		$itemCode = $this->getItemCode($post_id);
		$itemName = $this->getItemName($post_id);
		$itemRestriction = $this->getItemRestriction($post_id);
		$skuPrice = $cart_row['price'];
		$skuZaikonum = $this->getItemZaikonum($post_id, $sku);
		$stockid = $this->getItemZaikoStatusId($post_id, $sku);
		$stock = $this->getItemZaiko($post_id, $sku);
		$red = (in_array($stock, array(__('sellout','usces'), __('Temporarily out of stock','usces'), __('Out of print','usces')))) ? 'class="signal_red"' : '';
		$pictids = $this->get_pictids($itemCode);
		if (!empty($options)) {
//			$optstr = implode(',', $options);
		} else { 
			$optstr =  '';
			$options =  array();
		}
				
		$html .= '<tr>
			<td>' . ($i + 1) . '</td>
			<td>' . wp_get_attachment_image( $pictids[0], array(60, 60), true ) . '</td>
			<td class="aleft">' . $itemName . '&nbsp;' . $itemCode . '&nbsp;' . $sku . '<br />';
		foreach((array)$options as $key => $value){
			$html .= htmlspecialchars($key) . ' : ' . htmlspecialchars($value) . "<br />\n"; 
		}
		$html .= '</td>
			<td class="aright">';
		if( usces_is_gptekiyo($post_id, $sku, $quantity) ) {
			$usces_gp = 1;
			$html .= '<img src="' . get_template_directory_uri() . '/images/gp.gif" alt="' . __('Business package discount','usces') . '" /><br />';
		}
		$html .= number_format($skuPrice) . '
			</td>
			<td><input name="quant[' . $i . '][' . $post_id . '][' . $sku . ']" class="quantity" type="text" value="' . $cart_row['quantity'] . '" /></td>
			<td class="aright">' . number_format($skuPrice * $cart_row['quantity']) . '</td>
			<td ' . $red . '>' . $stock . '</td>
			<td>';
		foreach($options as $key => $value){
			$html .= '<input name="itemOption[' . $i . '][' . $post_id . '][' . $sku . '][' . $key . ']" type="hidden" value="' . $value . '" />';
		}
		$html .= '<input name="itemRestriction[' . $i . ']" type="hidden" value="' . $itemRestriction . '" />
			<input name="stockid[' . $i . ']" type="hidden" value="' . $stockid . '" />
			<input name="itempostid[' . $i . ']" type="hidden" value="' . $post_id . '" />
			<input name="itemsku[' . $i . ']" type="hidden" value="' . $sku . '" />
			<input name="zaikonum[' . $i . '][' . $post_id . '][' . $sku . ']" type="hidden" value="' . $skuZaikonum . '" />
			<input name="skuPrice[' . $i . '][' . $post_id . '][' . $sku . ']" type="hidden" value="' . $skuPrice . '" />
			<input name="delButton[' . $i . '][' . $post_id . '][' . $sku . ']" class="delButton" type="submit" value="' . __('Delete','usces') . '" />
			</td>
		</tr>';
	}

	$html .= '</tbody>
		<tfoot>
		<tr>
			<th colspan="5" scope="row" class="aright">' . __('total items','usces') . $this->getGuidTax() . '</th>
			<th class="aright">' . number_format($this->get_total_price()) . '</th>
			<th colspan="2">&nbsp;</th>
		</tr>
		</tfoot>
	</table>';
	if( $usces_gp ) {
		$html .= '<img src="' . get_template_directory_uri() . '/images/gp.gif" alt="' . __('Business package discount','usces') . '" /><br />' . __('The price with this mark applys to Business pack discount.','usces');
	}
	$html .= '</div>';

} else {
	$html .= '<div class="no_cart">' . __('There is no items in your cart.','usces') . '</div>';
}

$html .= $content;

$html .= '<div class="send">
	<input name="previous" type="button" id="previouscart" onclick="uscesCart.previousCart();" value="' . __('continue shopping','usces') . '" />&nbsp;&nbsp;';
if( usces_is_cart() ) {
	$html .= '<input name="customerinfo" type="submit" value="' . __(' Next ','usces') . '" />';
}
$html .= '</div>
	</form>';

$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_cart_page_footer', $footer);
$html .= '</div>';
	
$html .= '</div>';
?>
