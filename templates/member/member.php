<?php 
$usces_members = $this->get_member();
$usces_member_history = $this->get_member_history($usces_members['ID']);
$colspan = usces_is_membersystem_point() ? 9 : 7;

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
<th scope="row">' . __('Full name', 'usces') . '</th>
<td>' . sprintf(__('Mr/Mrs %s', 'usces'), esc_html($usces_members['name1'] . ' ' . $usces_members['name2'])) . '</td>';

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
	<td>' . esc_html($usces_members['mailaddress1']) . '</td>
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

$html .= '<h3>' . __('Purchase history', 'usces') . '</h3>';
$html .= '<div class="currency_code">' . __('Currency','usces') . ' : ' . __(usces_crcode( 'return' ), 'usces') . '</div>';

$html .= usces_member_history('return');	

$html .= '<h3><a name="edit"></a>' . __('Member information editing', 'usces') . '</h3>
	<div class="error_message">' . $this->error_message . '</div>
	<form action="' . USCES_MEMBER_URL . '#edit" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
	<table class="customer_form">';
	
$html .= uesces_addressform( 'member', $usces_members );

$html .= '<tr>
	<th scope="row">' . __('e-mail adress', 'usces') . '</th>
	<td colspan="2"><input name="member[mailaddress1]" id="mailaddress1" type="text" value="' . esc_attr($usces_members['mailaddress1']) . '" /></td>
	</tr>
	<tr>
	<th scope="row">' . __('password', 'usces') . '</th>
	<td colspan="2"><input name="member[password1]" id="password1" type="password" value="' . esc_attr($usces_members['password1']) . '" />
	' . __('Leave it blank in case of no change.', 'usces') . '</td>
	</tr>
	<tr>
	<th scope="row">' . __('Password (confirm)', 'usces') . '</th>
	<td colspan="2"><input name="member[password2]" id="password2" type="password" value="' . esc_attr($usces_members['password2']) . '" />
	' . __('Leave it blank in case of no change.', 'usces') . '</td>
	</tr>';
	
	
	
	
$html .= '</table>
	<input name="member_regmode" type="hidden" value="editmemberform" />
	<input name="member_id" type="hidden" value="' . $usces_members['ID'] . '" />
	<div class="send">
	<input name="top" type="button" value="' . __('Back to the top page.', 'usces') . '" onclick="location.href=\'' . get_option('home') . '\'" />
	<input name="editmember" type="submit" value="' . __('update it', 'usces') . '" />
	<input name="deletemember" type="submit" value="' . __('delete it', 'usces') . '" onclick="return confirm(\'' . __('All information about the member is deleted. Are you all right?', 'usces') . '\');" /></div>
	</form>';
	

$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_memberinfo_page_footer', $footer);
$html .= '</div>';
	
$html .= '</div>
	</div>
	</div>';
?>
