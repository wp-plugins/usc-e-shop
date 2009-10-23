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
<input type="text" name="loginmail" id="loginmail" class="loginmail" value="' . $this->current_member['email'] . '" size="20" tabindex="10" /></label>
</p>
<p class="submit">
<input type="submit" name="lostpassword" id="member_login" value="新しいパスワードを取得" tabindex="100" />
</p>
</form>
<div>メールを送信いたしますので、その指示に従ってパスワードを変更していただくようお願いいたします。</div>
<p id="nav">';

if ( ! usces_is_login() ) {
	$html .= '<a href="' . USCES_MEMBER_URL . '&page=login" title="ログイン">ログイン</a>';
}
$html .= '</p>

</div>
</div>

</div>

<script type="text/javascript">
try{document.getElementById(\'loginmail\').focus();}catch(e){}
</script>';
?>
