<?php
$html = '<div id="memberpages">

<div class="whitebox">';

$html .= '<div class="header_explanation">';
$header = '';
$html .= apply_filters('usces_filter_login_page_header', $header);
$html .= '</div>';

if ( usces_is_error_message() ) {
	$html .= '<div class="error_message">' . $this->error_message . '</div>';
}
$html .= '<div class="loginbox">
<form name="loginform" id="loginform" action="' . apply_filters('usces_filter_login_form_action', USCES_MEMBER_URL) . '" method="post">
<p>
<label>' . __('e-mail adress', 'usces') . '<br />
<input type="text" name="loginmail" id="loginmail" class="loginmail" value="' . esc_attr(usces_remembername('return')) . '" size="20" /></label>
</p>
<p>
<label>' . __('password', 'usces') . '<br />
<input type="password" name="loginpass" id="loginpass" class="loginpass" size="20" /></label>
</p>
<p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" /> ' . __('memorize login information', 'usces') . '</label></p>
<p class="submit">';
$loginbutton = '<input type="submit" name="member_login" id="member_login" value="' . __('Log-in', 'usces') . '" />';
$html .= apply_filters('usces_filter_login_button', $loginbutton);
$html .= '</p>';
$html = apply_filters('usces_filter_login_inform', $html);
$html .= '</form>

<p id="nav">
<a href="' . USCES_LOSTMEMBERPASSWORD_URL . '" title="' . __('Did you forget your password?', 'usces') . '">' . __('Did you forget your password?', 'usces') . '</a>
</p>
<p id="nav">';
if ( ! usces_is_login() ) {
	$html .= '<a href="' . USCES_NEWMEMBER_URL . apply_filters('usces_filter_newmember_urlquery', NULL) . '" title="' . __('New enrollment for membership.', 'usces') . '">' . __('New enrollment for membership.', 'usces') . '</a>';
}
$html .= '</p>

</div>';


$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_login_page_footer', $footer);
$html .= '</div>';
	

$html .= '</div>

</div>

<script type="text/javascript">';
if ( $usces_is_login ) {
	$html .= 'setTimeout( function(){ try{
		d = document.getElementById(\'loginpass\');
		d.value = \'\';
		d.focus();
		} catch(e){}
		}, 200);';
} else {
	$html .= 'try{document.getElementById(\'loginmail\').focus();}catch(e){}';
}
$html .= '</script>';
?>
