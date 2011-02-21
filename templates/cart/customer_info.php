<?php 
$usces_entries = $this->cart->get_entry();
$member_regmode = isset( $_SESSION['usces_entry']['member_regmode'] ) ? $_SESSION['usces_entry']['member_regmode'] : 'none';

$html = '<div id="customer-info">

<div class="usccart_navi">
<ol class="ucart">
<li class="ucart usccart">' . __('1.Cart','usces') . '</li>
<li class="ucart usccustomer usccart_customer">' . __('2.Customer Info','usces') . '</li>
<li class="ucart uscdelivery">' . __('3.Deli. & Pay.','usces') . '</li>
<li class="ucart uscconfirm">' . __('4.Confirm','usces') . '</li>
</ol>
</div>';

$html .= '<div class="header_explanation">';
$header = '';
$html .= apply_filters('usces_filter_customer_page_header', $header);
$html .= '</div>';

$html .= '<div class="error_message">' . $this->error_message . '</div>';

if(usces_is_membersystem_state()){
	$html .= '<h5>' . __('The member please enter at here.','usces') . '</h5>
	<form action="' . USCES_CART_URL . '" method="post" name="customer_loginform" onKeyDown="if (event.keyCode == 13) {return false;}">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="customer_form">
	<tr>
	<th scope="row">'.__('e-mail adress', 'usces').'</th>
	<td><input name="loginmail" id="mailaddress1" type="text" value="' . esc_attr($usces_entries['customer']['mailaddress1']) . '" /></td>
	</tr>
	<tr>
	<th scope="row">'.__('password', 'usces').'</th>
	<td><input name="loginpass" id="mailaddress1" type="password" value="" /></td>
	</tr>
	</table>
	<div class="send"><input name="customerlogin" type="submit" value="'.__(' Next ', 'usces').'" /></div>
	</form>
	<h5>' . __('The nonmember please enter at here.','usces') . '</h5>';
}

$html .= '<form action="' . USCES_CART_URL . '" method="post" name="customer_form" onKeyDown="if (event.keyCode == 13) {return false;}">
<table border="0" cellpadding="0" cellspacing="0" class="customer_form">
<tr>
<th scope="row"><em>*</em>'.__('e-mail adress', 'usces').'</th>';

$html .= '<td colspan="2"><input name="customer[mailaddress1]" id="mailaddress1" type="text" value="' . esc_attr($usces_entries['customer']['mailaddress1']) . '" /></td>
</tr>
<tr>
<th scope="row"><em>*</em>'.__('e-mail adress', 'usces').'('.__('Re-input', 'usces').')</th>
<td colspan="2"><input name="customer[mailaddress2]" id="mailaddress2" type="text" value="' . esc_attr($usces_entries['customer']['mailaddress2']) . '" /></td>
</tr>';

if(usces_is_membersystem_state()){
	$html .= '<tr><th scope="row">';
	if( $member_regmode == 'editmemberfromcart' ){
		$html .= '<em>*</em>';
	}
	$html .= __('password', 'usces').'</th>
	<td colspan="2"><input name="customer[password1]" style="width:100px" type="password" value="' . esc_attr($usces_entries['customer']['password1']) . '" />';
	if( $member_regmode != 'editmemberfromcart' ){
		$html .= __('When you enroll newly, please fill it out.', 'usces');
	}
	$html .= '</td></tr>';
	$html .= '<tr><th scope="row">';
	if( $member_regmode == 'editmemberfromcart' ){
		$html .= '<em>*</em>';
	}
	$html .= __('Password (confirm)', 'usces').'</th>
	<td colspan="2"><input name="customer[password2]" style="width:100px" type="password" value="' . esc_attr($usces_entries['customer']['password2']) . '" />';
	if( $member_regmode != 'editmemberfromcart' ){
		$html .= __('When you enroll newly, please fill it out.', 'usces');
	}
	$html .= '</td></tr>';
}

$html .= uesces_addressform( 'customer', $usces_entries );

$html .= '</table>
<input name="member_regmode" type="hidden" value="' . $member_regmode . '" />

<div class="send"><input name="backCart" type="submit" class="back_cart_button" value="'.__('Back', 'usces').'" />&nbsp;&nbsp;';

$button = '<input name="deliveryinfo" type="submit" class="to_deliveryinfo_button" value="'.__(' Next ', 'usces').'" />&nbsp;&nbsp;';
$html .= apply_filters('usces_filter_customer_button', $button);

if(usces_is_membersystem_state() && $member_regmode != 'editmemberfromcart' && usces_is_login() == false ){
	$html .= '<input name="reganddeliveryinfo" type="submit" class="to_reganddeliveryinfo_button" value="'.__('To the next while enrolling', 'usces').'"' . apply_filters('usces_filter_customerinfo_prebutton', NULL) . ' />';
}elseif(usces_is_membersystem_state() && $member_regmode == 'editmemberfromcart' ){
	$html .= '<input name="reganddeliveryinfo" type="submit" class="to_reganddeliveryinfo_button" value="'.__('Revise member information, and to next', 'usces').'"' . apply_filters('usces_filter_customerinfo_nextbutton', NULL) . ' />';
}

$html .= '</div>
</form>';

$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_customer_page_footer', $footer);
$html .= '</div>';

$html .= '</div>';
?>
