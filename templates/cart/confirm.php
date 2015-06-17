<?php
global $usces_entries, $usces_carts, $usces_members;
usces_get_members();
usces_get_entries();
usces_get_carts();
$html = '<div id="info-confirm">
	
	<div class="usccart_navi">
	<ol class="ucart">
	<li class="ucart usccart">' . __('1.Cart','usces') . '</li>
	<li class="ucart usccustomer">' . __('2.Customer Info','usces') . '</li>
	<li class="ucart uscdelivery">' . __('3.Deli. & Pay.','usces') . '</li>
	<li class="ucart uscconfirm usccart_confirm">' . __('4.Confirm','usces') . '</li>
	</ol>
	</div>';

$html .= '<div class="header_explanation">';
$header = '';
$html .= apply_filters('usces_filter_confirm_page_header', $header);
$html .= '</div>';
$html .= '<div class="error_message">' . $this->error_message . '</div>';

$confirm_table_head = '<div id="cart">
<div class="currency_code">' . __('Currency','usces') . ' : ' . __(usces_crcode( 'return' ), 'usces') . '</div>
<table cellspacing="0" id="cart_table">
		<thead>
		<tr>
			<th scope="row" class="num">' . __('No.','usces') . '</th>
			<th class="thumbnail">&nbsp;&nbsp;</th>
			<th class="productname">' . __('Items','usces') . '</th>
			<th class="unitprice">' . __('Unit price','usces') . '</th>
			<th class="quantity">'.__('Quantity', 'usces').'</th>
			<th class="subtotal">'.__('Amount', 'usces').'</th>
			<th class="action"></th>
		</tr>
		</thead>
		<tbody>';
$html .= apply_filters( 'usces_filter_confirm_table_head', $confirm_table_head );

$member = $this->get_member();

$html .= usces_get_confirm_rows('return');

$confirm_table_footer = '</tbody>
	<tfoot>
	<tr class="total_items_price">
		<th colspan="5" class="aright">'.__('total items', 'usces').'</th>
		<th class="aright">' . usces_crform($usces_entries['order']['total_items_price'], true, false, 'return') . '</th>
		<th>&nbsp;</th>
	</tr>';
if( $this->options['membersystem_state'] == 'activate' &&  $this->options['membersystem_point'] == 'activate' && !empty($usces_entries['order']['usedpoint']) ) {
	$confirm_table_footer .= '<tr class="usedpoint">
		<td colspan="5" class="aright">'.__('Used points', 'usces').'</td>
		<td class="aright" style="color:#FF0000">' . number_format($usces_entries['order']['usedpoint']) . '</td>
		<td>&nbsp;</td>
	</tr>';
}
if( !empty($usces_entries['order']['discount']) ) {
	$confirm_table_footer .= '<tr class="discount">
		<td colspan="5" class="aright">'.apply_filters('usces_confirm_discount_label', __('Campaign disnount', 'usces')).'</td>
		<td class="aright" style="color:#FF0000">' . usces_crform($usces_entries['order']['discount'], true, false, 'return') . '</td>
		<td>&nbsp;</td>
	</tr>';
}
if( !empty($usces_entries['order']['tax']) && 'products' == usces_get_tax_target() ) {
	$confirm_table_footer .= '<tr class="tax">
		<td colspan="5" class="aright">'.__('consumption tax', 'usces').'</td>
		<td class="aright">' . usces_crform($usces_entries['order']['tax'], true, false, 'return') . '</td>
		<td>&nbsp;</td>
	</tr>';
}
$confirm_table_footer .= '<tr class="shipping_charge">
	<td colspan="5" class="aright">'.__('Shipping', 'usces').'</td>
	<td class="aright">' . usces_crform($usces_entries['order']['shipping_charge'], true, false, 'return') . '</td>
	<td>&nbsp;</td>
	</tr>';
if( !empty($usces_entries['order']['cod_fee']) ) {
	$confirm_table_footer .= '<tr class="cod_fee">
		<td colspan="5" class="aright">'.apply_filters('usces_filter_cod_label', __('COD fee', 'usces')).'</td>
		<td class="aright">' . usces_crform($usces_entries['order']['cod_fee'], true, false, 'return') . '</td>
		<td>&nbsp;</td>
	</tr>';
}
if( !empty($usces_entries['order']['tax']) && 'all' == usces_get_tax_target() ) {
	$confirm_table_footer .= '<tr class="tax">
		<td colspan="5" class="aright">'.__('consumption tax', 'usces').'</td>
		<td class="aright">' . usces_crform($usces_entries['order']['tax'], true, false, 'return') . '</td>
		<td>&nbsp;</td>
	</tr>';
}
$confirm_table_footer .= '<tr class="total_full_price">
	<th colspan="5" class="aright">'.__('Total Amount', 'usces').'</th>
	<th class="aright">' . usces_crform($usces_entries['order']['total_full_price'], true, false, 'return') . '</th>
	<th>&nbsp;</th>
	</tr>
	</tfoot>
	</table>';
$html .= apply_filters( 'usces_filter_confirm_table_footer', $confirm_table_footer );
	
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
		<td><input name="offer[usedpoint]" class="used_point" type="text" value="' . esc_attr($usces_entries['order']['usedpoint']) . '" />pt</td>
		</tr>
		<tr>
		<td colspan="2"><input name="use_point" type="submit" class="use_point_button" value="'.__('Use the points', 'usces').'" /></td>
		</tr>
	</table>';
	$html = apply_filters('usces_filter_confirm_point_inform', $html);
	$html .= wp_nonce_field( 'use_point', 'wc_nonce', true, false );
	$html .= '</form>';
}
$html .= apply_filters('usces_filter_confirm_after_form', NULL);
$html .= '</div>
	<table id="confirm_table">
	<tr class="ttl">
	<td colspan="2"><h3>'.__('Customer Information', 'usces').'</h3></td>
	</tr>
	<tr>
	<th>'.__('e-mail adress', 'usces').'</th>
	<td>' . esc_html($usces_entries['customer']['mailaddress1']) . '</td>
	</tr>';
	
$html .= uesces_addressform( 'confirm', $usces_entries );
	
$html .= '<tr>';
$html .= '<td class="ttl" colspan="2"><h3>'.__('Others', 'usces').'</h3></td>
	</tr>';
//20101208ysk start
$shipping_info = '<tr>
	<th>'.__('shipping option', 'usces').'</th><td>' . esc_html(usces_delivery_method_name( $usces_entries['order']['delivery_method'], 'return' )) . '</td>
	</tr>
	<tr>
	<th>'.__('Delivery date', 'usces').'</th><td>' . esc_html($usces_entries['order']['delivery_date']) . '</td>
	</tr>
	<tr class="bdc">
	<th>'.__('Delivery Time', 'usces').'</th><td>' . esc_html($usces_entries['order']['delivery_time']) . '</td>
	</tr>';
$html .= apply_filters('usces_filter_confirm_shipping_info', $shipping_info);
//20101208ysk end

$html .= '<tr>
	<th>'.__('payment method', 'usces').'</th><td>' . esc_html($usces_entries['order']['payment_name'] . usces_payment_detail($usces_entries)) . '</td>
	</tr>';
//20100818ysk start
//require_once( USCES_PLUGIN_DIR . "/includes/confirm_custom_order_form.php");
$html .= usces_custom_field_info($usces_entries, 'order', '', 'return');
$html .= '<tr>
	<th>'.__('Notes', 'usces').'</th><td>' . nl2br(esc_html($usces_entries['order']['note'])) . '</td>
	</tr>';
//20100818ysk end
$html .= '</table>';

require( USCES_PLUGIN_DIR . "/includes/purchase_button.php");


$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_confirm_page_footer', $footer);
$html .= '</div>';

$html .= '</div>';
?>
