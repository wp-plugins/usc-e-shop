<?php 
$usces_members = $this->get_member();
$usces_member_history = $this->get_member_history($usces_members['ID']);

$html = '<div id="memberpages">

<div class="whitebox">
<div id="memberinfo">
<table>
<tr>
<th scope="row">' . __('member number', 'usces') . '</th>
<td class="num">' . $usces_members['ID'] . '</td>
<td rowspan="3">&nbsp;</td>
<th>' . __('Strated date', 'usces') . '</th>
<td>' . mysql2date(__('Y/m/d'), $usces_members['registered']) . '</td>
</tr>
<tr>
<th scope="row">' . __('Strated date', 'usces') . '</th>
<td>' . $usces_members['name1'] . '&nbsp;' . $usces_members['name2'] . '&nbsp;' . __('Mr/Mrs', 'usces') . '</td>';

if(usces_is_membersystem_point()){
	$html .= '<th>' . __('The current point', 'usces') . '</th>
		<td class="num">' . $usces_members['point'] . '</td>';
}else{
	$html .= '<th>&nbsp;</th>
	<td class="num">&nbsp;</td>';
}
$html .= '</tr>
	<tr>
	<th scope="row">' . __('e-mail adress', 'usces') . '</th>
	<td>' . $usces_members['mailaddress1'] . '</td>
	<th>&nbsp;</th>
	<td>&nbsp;</td>
	</tr>
	</table>
	　<br />　
	<a href="#edit">' . __('To member information editing', 'usces') . '</a>';
	

$html .= '<div class="header_explanation">';
$header = '';
$html .= apply_filters('usces_filter_memberinfo_page_header', $header);
$html .= '</div>';

$html .= '<h3>' . __('Purchase history', 'usces') . '</h3>
	<table>';
	
	
if ( !count($usces_member_history) ) {
	$html .= '<tr>
	<td>' . __('There is no purchase history for this moment.', 'usces') . '</td>
	</tr>';
}
foreach ( $usces_member_history as $umhs ) {
	$cart = $umhs['cart'];
	$html .= '<tr>
		<th class="historyrow">' . __('Purchase date', 'usces') . '</th>
		<th class="historyrow">' . __('Purchase price', 'usces') . '</th>
		<th class="historyrow">' . __('Used points', 'usces') . '</th>
		<th class="historyrow">' . __('Special discount', 'usces') . '</th>
		<th class="historyrow">' . __('Shipping', 'usces') . '</th>
		<th class="historyrow">' . __('C.O.D', 'usces') . '</th>
		<th class="historyrow">' . __('Sales tax', 'usces') . '</th>
		<th class="historyrow">' . __('points', 'usces') . '</th>
		</tr>
		<tr>
		<td class="date">' . $umhs['date'] . '</td>
		<td class="rightnum">' . number_format($this->get_total_price($cart)-$umhs['usedpoint']+$umhs['discount']+$umhs['shipping_charge']+$umhs['cod_fee']+$umhs['tax']) . '</td>
		<td class="rightnum">' . number_format($umhs['usedpoint']) . '</td>
		<td class="rightnum">' . number_format($umhs['discount']) . '</td>
		<td class="rightnum">' . number_format($umhs['shipping_charge']) . '</td>
		<td class="rightnum">' . number_format($umhs['cod_fee']) . '</td>
		<td class="rightnum">' . number_format($umhs['tax']) . '</td>
		<td class="rightnum">' . number_format($umhs['getpoint']) . '</td>
		</tr>
		<tr>
		<td class="retail" colspan="8">
			<table id="retail_table">
			<tr>
			<th scope="row" class="num">No.</th>
			<th class="thumbnail">&nbsp;</th>
			<th>' . __('items', 'usces') . '</th>
			<th class="price ">' . __('Unit price', 'usces') . '</th>
			<th class="quantity">' . __('Quantity', 'usces') . '</th>
			<th class="subtotal">' . __('Amount', 'usces') . '</th>
			</tr>';
			
	for($i=0; $i<count($cart); $i++) { 
		$cart_row = $cart[$i];
		$post_id = $cart_row['post_id'];
		$sku = $cart_row['sku'];
		$quantity = $cart_row['quantity'];
		$options = $cart_row['options'];
		$itemCode = $this->getItemCode($post_id);
		$itemName = $this->getItemName($post_id);
		$skuPrice = $this->getItemPrice($post_id, $sku);
		$pictids = $this->get_pictids($itemCode);
		if (!empty($options)) {
			$optstr = implode(',', $options);
		} else { 
			$optstr =  '';
			$options =  array();
		}
			
		$html .= '<tr>
			<td>' . ($i + 1) . '</td>
			<td><a href="' . get_permalink($post_id) . '">' . wp_get_attachment_image( $pictids[0], array(60, 60), true ) . '</a></td>
			<td class="aleft"><a href="' . get_permalink($post_id) . '">' . $itemName . '&nbsp;' . $itemCode . '&nbsp;' . $sku . '<br />' . $optstr . '</a></td>
			<td class="rightnum">' . number_format($skuPrice) . '</td>
			<td class="rightnum">' . number_format($cart_row['quantity']) . '</td>
			<td class="rightnum">' . number_format($skuPrice * $cart_row['quantity']) . '</td>
			</tr>';
	}
	$html .= '</table>
		</td>
		</tr>';
}

$html .= '</table>

	<h3><a name="edit">' . __('Member information editing', 'usces') . '</a></h3>
	<div class="error_message">' . $this->error_message . '</div>
	<form action="' . USCES_MEMBER_URL . '" method="post">
	<table class="customer_form">
	<tr class="inp1">
	<th scope="row"><em>*</em>' . __('Full name', 'usces') . '</th>
	<td>' . __('Familly name', 'usces') . '<input name="member[name1]" id="name1" type="text" value="' . $usces_members['name1'] . '" /></td>
	<td>' . __('Given name', 'usces') . '<input name="member[name2]" id="name2" type="text" value="' . $usces_members['name2'] . '" /></td>
	</tr>
	<tr class="inp1">
	<th scope="row"><em>*</em>' . __('furigana', 'usces') . '</th>
	<td>' . __('Familly name', 'usces') . '<input name="member[name3]" id="name3" type="text" value="' . $usces_members['name3'] . '" /></td>
	<td>' . __('Given name', 'usces') . '<input name="member[name4]" id="name4" type="text" value="' . $usces_members['name4'] . '" /></td>
	</tr>
	<tr>
	<th scope="row"><em>*</em>' . __('Zip/Postal Code', 'usces') . '</th>
	<td colspan="2"><input name="member[zipcode]" id="zipcode" type="text" value="' . $usces_members['zipcode'] . '" />例）100-1000</td>
	</tr>
	<tr>
	<th scope="row"><em>*</em>' . __('Province', 'usces') . '</th>
	<td colspan="2">' . usces_the_pref( 'member', 'return' ) . '</td>
	</tr>
	<tr class="inp2">
	<th scope="row"><em>*</em>' . __('city', 'usces') . '</th>
	<td colspan="2"><input name="member[address1]" id="address1" type="text" value="' . $usces_members['address1'] . '" />例）横浜市上北町</td>
	</tr>
	<tr>
	<th scope="row"><em>*</em>' . __('numbers', 'usces') . '</th>
	<td colspan="2"><input name="member[address2]" id="address2" type="text" value="' . $usces_members['address2'] . '" />例）3-24-555</td>
	</tr>
	<tr>
	<th scope="row">' . __('building name', 'usces') . '</th>
	<td colspan="2"><input name="member[address3]" id="address3" type="text" value="' . $usces_members['address3'] . '" />例）通販ビル4F</td>
	</tr>
	<tr>
	<th scope="row"><em>*</em>' . __('Phone number', 'usces') . '</th>
	<td colspan="2"><input name="member[tel]" id="tel" type="text" value="' . $usces_members['tel'] . '" />例）1000-10-1000</td>
	</tr>
	<tr>
	<th scope="row">' . __('FAX number', 'usces') . '</th>
	<td colspan="2"><input name="member[fax]" id="fax" type="text" value="' . $usces_members['fax'] . '" />例）1000-10-1000</td>
	</tr>
	<tr>
	<th scope="row">' . __('password', 'usces') . '</th>
	<td colspan="2"><input name="member[password1]" id="password1" type="password" value="' . $usces_members['password1'] . '" />
	' . __('Leave it blank in case of no change.', 'usces') . '</td>
	</tr>
	<tr>
	<th scope="row">' . __('Password (confirm)', 'usces') . '</th>
	<td colspan="2"><input name="member[password2]" id="password2" type="password" value="' . $usces_members['password2'] . '" />
	' . __('Leave it blank in case of no change.', 'usces') . '</td>
	</tr>
	</table>
	<input name="member_regmode" type="hidden" value="' . $member_regmode . '" />
	<div class="send"><input name="top" type="submit" value="' . __('Back to the top page.', 'usces') . '" />
	<input name="editmember" type="submit" value="' . __('update it', 'usces') . '" /></div>
	</form>';
	

$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_memberinfo_page_footer', $footer);
$html .= '</div>';
	
$html .= '</div>
	</div>
	</div>';
?>
