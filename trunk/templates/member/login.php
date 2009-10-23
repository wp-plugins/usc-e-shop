<?php
$html = '<div id="memberpages">

<div class="whitebox">';

if ( usces_is_error_message() ) {
	$html .= '<div class="error_message">' . $this->error_message . '</div>';
}
$html .= '<div class="loginbox">
<form name="loginform" id="loginform" action="' . USCES_MEMBER_URL . '" method="post">
<p>
<label>メールアドレス<br />
<input type="text" name="loginmail" id="loginmail" class="loginmail" value="' . usces_remembername('return') . '" size="20" tabindex="10" /></label>
</p>
<p>
<label>パスワード<br />
<input type="password" name="loginpass" id="loginpass" class="loginpass" value="' . usces_rememberpass('return') . '" size="20" tabindex="20" /></label>
</p>
<p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90"' . usces_remembercheck('return') . ' /> ログイン情報を記憶</label></p>
<p class="submit">
<input type="submit" name="member_login" id="member_login" value="ログイン" tabindex="100" />
</p>
</form>

<p id="nav">
<a href="' . USCES_MEMBER_URL . '&page=lostmemberpassword" title="パスワード紛失取り扱い">パスワードをお忘れですか？</a>
</p>
<p id="nav">';
if ( ! usces_is_login() ) {
	$html .= '<a href="' . USCES_MEMBER_URL . '&page=newmember" title="新規ご入会はこちら">新規ご入会はこちら</a>';
}
$html .= '</p>

</div>
</div>

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
