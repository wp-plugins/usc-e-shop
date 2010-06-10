<?php
$usces_members = $this->get_member();

$html = '<div id="memberpages">

<div id="newmember">';



$html .= '<div class="header_explanation">';
$header = '<ul>
<li>' . __('All your personal information  will be protected and handled with carefull attention.', 'usces') . '</li>
<li>' . __('Your information is entrusted to us for the purpose of providing information and respond to your requests, but to be used for any other purpose. More information, please visit our Privacy  Notice.', 'usces') . '</li>
<li>' . __('The items marked with *, are mandatory. Please complete.', 'usces') . '</li>
<li>' . __('Please use Alphanumeric characters for numbers.', 'usces') . '</li>
</ul>';
$html .= apply_filters('usces_filter_newmember_page_header', $header);
$html .= '</div>';


$html .= '<div class="error_message">' . $this->error_message . '</div>
<form action="' . apply_filters('usces_filter_newmember_form_action', USCES_MEMBER_URL) . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
<table border="0" cellpadding="0" cellspacing="0" class="customer_form">
<tr>
<th scope="row"><em>*</em>' . __('e-mail adress', 'usces') . '</th>
<td colspan="2"><input name="member[mailaddress1]" id="mailaddress1" type="text" value="' . $usces_members['mailaddress1'] . '" /></td>
</tr>
<tr>
<th scope="row"><em>*</em>' . __('E-mail address (for verification)', 'usces') . '</th>
<td colspan="2"><input name="member[mailaddress2]" id="mailaddress2" type="text" value="' . $usces_members['mailaddress2'] . '" /></td>
</tr>
<tr>
<th scope="row"><em>*</em>' . __('password', 'usces') . '</th>
<td colspan="2"><input name="member[password1]" id="password1" type="password" value="' . $usces_members['password1'] . '" /></td>
</tr>
<tr>
<th scope="row"><em>*</em>' . __('Password (confirm)', 'usces') . '</th>
<td colspan="2"><input name="member[password2]" id="password2" type="password" value="' . $usces_members['password2'] . '" /></td>
</tr>
<tr class="inp1">
<th scope="row"><em>*</em>' . __('Full name', 'usces') . '</th>
<td>' . __('Familly name', 'usces') . '<input name="member[name1]" id="name1" type="text" value="' . $usces_members['name1'] . '" /></td>
<td>' . __('Given name', 'usces') . '<input name="member[name2]" id="name2" type="text" value="' . $usces_members['name2'] . '" /></td>
</tr>';
if( USCES_JP ){
	$html .= '<tr class="inp1">
	<th scope="row">' . __('furigana', 'usces') . '</th>
	<td>' . __('Familly name', 'usces') . '<input name="member[name3]" id="name3" type="text" value="' . $usces_members['name3'] . '" /></td>
	<td>' . __('Given name', 'usces') . '<input name="member[name4]" id="name4" type="text" value="' . $usces_members['name4'] . '" /></td>
	</tr>';
}
$html .= '<tr>
<th scope="row"><em>*</em>' . __('Zip/Postal Code', 'usces') . '</th>
<td colspan="2"><input name="member[zipcode]" id="zipcode" type="text" value="' . $usces_members['zipcode'] . '" />100-1000</td>
</tr>
<tr>
<th scope="row"><em>*</em>' . __('Province', 'usces') . '</th>
<td colspan="2">' . usces_the_pref( 'member', 'return' ) . '</td>
</tr>
<tr class="inp2">
<th scope="row"><em>*</em>' . __('city', 'usces') . '</th>
<td colspan="2"><input name="member[address1]" id="address1" type="text" value="' . $usces_members['address1'] . '" />' . __('Kitakami Yokohama', 'usces') . '</td>
</tr>
<tr>
<th scope="row"><em>*</em>' . __('numbers', 'usces') . '</th>
<td colspan="2"><input name="member[address2]" id="address2" type="text" value="' . $usces_members['address2'] . '" />3-24-555</td>
</tr>
<tr>
<th scope="row">' . __('building name', 'usces') . '</th>
<td colspan="2"><input name="member[address3]" id="address3" type="text" value="' . $usces_members['address3'] . '" />' . __('tuhanbuild 4F', 'usces') . '</td>
</tr>
<tr>
<th scope="row"><em>*</em>' . __('Phone number', 'usces') . '</th>
<td colspan="2"><input name="member[tel]" id="tel" type="text" value="' . $usces_members['tel'] . '" />1000-10-1000</td>
</tr>
<tr>
<th scope="row">' . __('FAX number', 'usces') . '</th>
<td colspan="2"><input name="member[fax]" id="fax" type="text" value="' . $usces_members['fax'] . '" />1000-10-1000</td>
</tr>
</table>
<input name="member_regmode" type="hidden" value="' . $member_regmode . '" /><div class="send">';
$newmemberbutton = '<input name="regmember" type="submit" value="' . __('transmit a message', 'usces') . '" />';
$html .= apply_filters('usces_filter_newmember_button', $newmemberbutton);
$html .= '</div>';
$html = apply_filters('usces_filter_newmember_inform', $html);
$html .= '</form>';


$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_newmember_page_footer', $footer);
$html .= '</div>';
	

$html .= '</div>

</div>';
?>
