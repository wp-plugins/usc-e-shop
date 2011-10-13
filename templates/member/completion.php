<?php

$member_compmode = $this->page;
$html = '<div id="memberpages">

<div class="post">';

$html .= '<div class="header_explanation">';
$header = '';
$html .= apply_filters('usces_filter_membercompletion_page_header', $header);
$html .= '</div>';


if ( $member_compmode == 'newcompletion' ) {
	$html .= '<p>' . __('Thank you in new membership.', 'usces') . '</p>';
}else if ( $member_compmode == 'editcompletion' ) {
	$html .= '<p>' . __('Membership information has been updated.', 'usces') . '</p>';
}else if ( $member_compmode == 'lostcompletion' ) {
	$html .= '<p>' . __('I transmitted an email.', 'usces') . '</p>
		<p>' . __('Chenge th epassword according to the e-mail.', 'usces') . '</p>';
}else if ( $member_compmode == 'changepasscompletion' ) {
	$html .= '<p>' . __('Password has been changed.', 'usces') . '</p>';
}


$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_membercompletion_page_footer', $footer);
$html .= '</div>';
	


$html .= '<p><a href="' . USCES_MEMBER_URL . '">' . __('to vist membership information page', 'usces') . '</a></p>
	<form action="' . get_option('home') . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
	<div class="send"><input name="top" type="submit" value="' . __('Back to the top page.', 'usces') . '" /></div>
	</form>
	</div>

	</div>';
//$html .= '<img src="http://www.joel-marketing.com/station/maction.php?mn=&ml=&act=a&pn=np_4Iz&ky=7474&cd=s&toad=1&tocus=1" width="0" height="0" />';
?>
