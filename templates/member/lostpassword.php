<?php
$html = '<div id="memberpages">

<div class="whitebox">';

$html .= '<div class="header_explanation">';
$header = '';
$html .= apply_filters('usces_filter_newpass_page_header', $header);
$html .= '</div>';


if ( usces_is_error_message() ) {
	$html .= '<div class="error_message">' . $this->error_message . '</div>';
}
$html .= '<div class="loginbox">
<form name="loginform" id="loginform" action="' . USCES_MEMBER_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
<p>
<label>' . __('e-mail adress', 'usces') . '<br />
<input type="text" name="loginmail" id="loginmail" class="loginmail" value="' . esc_attr($this->current_member['email']) . '" size="20" /></label>
</p>
<p class="submit">
<input type="submit" name="lostpassword" id="member_login" value="' . __('Obtain new password', 'usces') . '" />
</p>
</form>
<div>' . __('Change your password by following the instruction in this mail.', 'usces') . '</div>
<p id="nav">';

if ( ! usces_is_login() ) {
	$html .= '<a href="' . USCES_LOGIN_URL . '" title="' . __('Log-in', 'usces') . '">' . __('Log-in', 'usces') . '</a>';
}
$html .= '</p>

</div>';


$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_newpass_page_footer', $footer);
$html .= '</div>';
	

$html .= '</div>

</div>

<script type="text/javascript">
try{document.getElementById(\'loginmail\').focus();}catch(e){}
</script>';
?>
