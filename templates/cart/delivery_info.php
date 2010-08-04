<?php 
$usces_entries = $this->cart->get_entry();
$usces_secure_link = get_option('usces_secure_link');
$html = '';

require_once( USCES_PLUGIN_DIR . "/includes/delivery_info_script.php");

$html .= '<div id="delivery-info">
	
	<div class="usccart_navi">
	<ol class="ucart">
	<li class="ucart">' . __('1.Cart','usces') . '</li>
	<li class="ucart">' . __('2.Customer Info','usces') . '</li>
	<li class="ucart usccart_delivery">' . __('3.Deli. & Pay.','usces') . '</li>
	<li class="ucart">' . __('4.Confirm','usces') . '</li>
	</ol>
	</div>';

$html .= '<div class="header_explanation">';
$header = '';
$html .= apply_filters('usces_filter_delivery_page_header', $header);
$html .= '</div>';
	
$html .= '<div class="error_message">' . $this->error_message . '</div>';

$html .= '<form action="' . USCES_CART_URL . '" method="post">';

	$html .= '<table class="customer_form">
		<tr>
		<th rowspan="2" scope="row">'.__('shipping address', 'usces').'</th>
		<td><input name="delivery[delivery_flag]" type="radio" id="delivery_flag1" onclick="document.getElementById(\'delivery_table\').style.display = \'none\';" value="0"';
	if($usces_entries['delivery']['delivery_flag'] == 0) {
		$html .= ' checked';
	}
	$html .= ' onKeyDown="if (event.keyCode == 13) {return false;}" /> <label for="delivery_flag1">'.__('same as customer information', 'usces').'</label></td>
		</tr>
		<tr>
		<td><input name="delivery[delivery_flag]" id="delivery_flag2" onclick="document.getElementById(\'delivery_table\').style.display = \'table\'" type="radio" value="1"';
	if($usces_entries['delivery']['delivery_flag'] == 1) {
		$html .= ' checked';
	}
	$html .= ' onKeyDown="if (event.keyCode == 13) {return false;}" /> <label for="delivery_flag2">'.__('Chose another shipping address.', 'usces').'</label></td>
		</tr>
		</table>
		<table class="customer_form" id="delivery_table">
		<tr class="inp1">
		<th width="127" scope="row"><em>*</em>'.__('Full name', 'usces').'</th>
		<td width="257">'.__('Familly name', 'usces').'<input name="delivery[name1]" id="name1" type="text" value="' . $usces_entries['delivery']['name1'] . '" onKeyDown="if (event.keyCode == 13) {return false;}" /></td>
		<td width="257">'.__('Given name', 'usces').'<input name="delivery[name2]" id="name2" type="text" value="' . $usces_entries['delivery']['name2'] . '" onKeyDown="if (event.keyCode == 13) {return false;}" /></td>
		</tr>';
	if( USCES_JP ){
		$html .= '<tr class="inp1">
		<th scope="row">'.__('furigana', 'usces').'</th>
		<td>'.__('Familly name', 'usces').'<input name="delivery[name3]" id="name3" type="text" value="' . $usces_entries['delivery']['name3'] . '" onKeyDown="if (event.keyCode == 13) {return false;}" /></td>
		<td>'.__('Given name', 'usces').'<input name="delivery[name4]" id="name4" type="text" value="' . $usces_entries['delivery']['name4'] . '" onKeyDown="if (event.keyCode == 13) {return false;}" /></td>
		</tr>';
	}
	$html .= '<tr>
		<th scope="row"><em>*</em>'.__('Zip/Postal Code', 'usces').'</th>
		<td colspan="2"><input name="delivery[zipcode]" id="zipcode" type="text" value="' . $usces_entries['delivery']['zipcode'] . '" onKeyDown="if (event.keyCode == 13) {return false;}" />100-1000</td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>'.__('Province', 'usces').'</th>
		<td colspan="2">' . usces_the_pref( 'delivery', 'return' ) . '</td>
		</tr>
		<tr class="inp2">
		<th scope="row"><em>*</em>'.__('city', 'usces').'</th>
		<td colspan="2"><input name="delivery[address1]" id="address1" type="text" value="' . $usces_entries['delivery']['address1'] . '" onKeyDown="if (event.keyCode == 13) {return false;}" />'.__('Kitakami Yokohama', 'usces').'</td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>'.__('numbers', 'usces').'</th>
		<td colspan="2"><input name="delivery[address2]" id="address2" type="text" value="' . $usces_entries['delivery']['address2'] . '" onKeyDown="if (event.keyCode == 13) {return false;}" />3-24-555</td>
		</tr>
		<tr>
		<th scope="row">'.__('building name', 'usces').'</th>
		<td colspan="2"><input name="delivery[address3]" id="address3" type="text" value="' . $usces_entries['delivery']['address3'] . '" onKeyDown="if (event.keyCode == 13) {return false;}" />'.__('tuhanbuild 4F', 'usces').'</td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>'.__('Phone number', 'usces').'</th>
		<td colspan="2"><input name="delivery[tel]" id="tel" type="text" value="' . $usces_entries['delivery']['tel'] . '" onKeyDown="if (event.keyCode == 13) {return false;}" />1000-10-1000</td>
		</tr>
		<tr>
		<th scope="row">'.__('FAX number', 'usces').'</th>
		<td colspan="2"><input name="delivery[fax]" id="fax" type="text" value="' . $usces_entries['delivery']['fax'] . '" onKeyDown="if (event.keyCode == 13) {return false;}" />1000-10-1000</td>
		</tr>
		</table>';
$html .= '<table class="customer_form" id="time">';
	$html .= '<tr>
		<th scope="row">'.__('shipping option', 'usces').'</th>
		<td colspan="2">' . usces_the_delivery_method( $usces_entries['order']['delivery_method'], 'return' ) . '</td>
		</tr>
		<tr>
		<th scope="row">'.__('Delivery Time', 'usces').'</th>
		<td colspan="2">' . usces_the_delivery_time( $usces_entries['order']['delivery_time'], 'return' ) . '</td>
		</tr>';
$html .= '<tr>
	<th scope="row"><em>*</em>'.__('payment method', 'usces').'</th>
	<td colspan="2">' . usces_the_payment_method( $usces_entries['order']['payment_name'], 'return' ) . '</td>
	</tr>
	</table>';
	
require_once( USCES_PLUGIN_DIR . "/includes/delivery_secure_form.php");
	
	
$html .= '<table class="customer_form" id="notes_table">
	<tr>
	<th scope="row">'.__('Notes', 'usces').'</th>
	<td colspan="2"><textarea name="order[note]" cols="" rows="" id="note">' . $usces_entries['order']['note'] . '</textarea></td>
	</tr>
	</table>

	<div class="send"><input name="order[cus_id]" type="hidden" value="' . $this->cus_id . '" />		
	<input name="backCustomer" type="submit" value="'.__('Back', 'usces').'" />&nbsp;&nbsp;
	<input name="confirm" type="submit" value="'.__(' Next ', 'usces').'" /></div>
	</form>';

$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_delivery_page_footer', $footer);
$html .= '</div>';

$html .= '</div>';
?>
