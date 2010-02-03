<?php
$html = '<div id="memberpages">';

$html .= '<div class="header_explanation">';
$header = '';
$html .= apply_filters('usces_filter_changepass_page_header', $header);
$html .= '</div>';


if($this->error_message != '') {
	$html .= '<div class="error_message">' . $this->error_message . '</div>';
}
$html .= '<div class="loginbox">
<form name="loginform" id="loginform" action="' . get_option('home') . '/usces-member" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
	<p>
		<label>' . __('password', 'usces') . '<br />
		<input type="password" name="loginpass1" id="loginpass1" class="loginpass" value="" size="20" tabindex="20" /></label>
	</p>
	<p>
		<label>' . __('Password (confirm)', 'usces') . '<br />
		<input type="password" name="loginpass2" id="loginpass2" class="loginpass" value="" size="20" tabindex="20" /></label>
	</p>
	<p class="submit">
		<input type="submit" name="changepassword" id="member_login" value="' . __('Register', 'usces') . '" tabindex="100" />
	</p>
</form>
</div>';

$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_changepass_page_footer', $footer);
$html .= '</div>';
	

$html .= '</div>

<script type="text/javascript">
try{document.getElementById(\'loginpass1\').focus();}catch(e){}
</script>';
?>
