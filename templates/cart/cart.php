<?php
global $usces_gp;

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
	
	$cart_table_head = '<table cellspacing="0" id="cart_table">
		<thead>
		<tr>
			<th scope="row" class="num">No.</th>
			<th class="thumbnail"> </th>
			<th class="productname">' . __('item name','usces') . '</th>
			<th class="unitprice">' . __('Unit price','usces') . '</th>
			<th class="quantity">' . __('Quantity','usces') . '</th>
			<th class="subtotal">' . __('Amount','usces') . usces_guid_tax('return') . '</th>
			<th class="stock">' . __('stock status','usces') . '</th>
			<th class="action"> </th>
		</tr>
		</thead>
		<tbody>';
		
	$html .= apply_filters( 'usces_filter_cart_table_head', $cart_table_head );
	$html .= usces_get_cart_rows('return');
		
	$cart_table_footer = '</tbody>
		<tfoot>
		<tr>
			<th class="num">&nbsp;</th>
			<th class="thumbnail">&nbsp;</th>
			<th colspan="3" scope="row" class="aright">' . __('total items','usces') . usces_guid_tax('return') . '</th>
			<th class="aright subtotal">' . usces_crform($this->get_total_price(), true, false, 'return') . '</th>
			<th class="stock">&nbsp;</th>
			<th class="action">&nbsp;</th>
		</tr>
		</tfoot>
	</table>';
	$html .= apply_filters( 'usces_filter_cart_table_footer', $cart_table_footer );

	$after_table = '<div class="currency_code">' . __('Currency','usces') . ' : ' . __(usces_crcode( 'return' ), 'usces') . '</div>';
	$html .= apply_filters( 'usces_filter_after_cart_table', $after_table );
	if( isset($usces_gp) && $usces_gp ) {
		$gp_src = file_exists(get_template_directory() . '/images/gp.gif') ? get_template_directory_uri() . '/images/gp.gif' : USCES_PLUGIN_URL . '/images/gp.gif';
		$Business_pack_discount = '<div class="gp_exp"><img src="' . $gp_src . '" alt="' . __('Business package discount','usces') . '" /><br />' . __('The price with this mark applys to Business pack discount.','usces') . '</div>';
		$html .= apply_filters('usces_filter_itemGpExp_cart_message', $Business_pack_discount);
	}
	$html .= "</div><!-- end of cart -->\n";

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
	$html .= '<a href="' . get_home_url() . '" class="continue_shopping_button">' . __('continue shopping','usces') . '</a>&nbsp;&nbsp;';
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
