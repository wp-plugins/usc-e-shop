<?php
$html = '<div id="memberpages">';

if($this->error_message != '') {
	$html .= '<div class="error_message">' . $this->error_message . '</div>';
}
$html .= '<div class="loginbox">
<form name="loginform" id="loginform" action="' . get_option('home') . '/usces-member" method="post">
	<p>
		<label>パスワード<br />
		<input type="password" name="loginpass1" id="loginpass1" class="loginpass" value="" size="20" tabindex="20" /></label>
	</p>
	<p>
		<label>パスワード（確認用）<br />
		<input type="password" name="loginpass2" id="loginpass2" class="loginpass" value="" size="20" tabindex="20" /></label>
	</p>
	<p class="submit">
		<input type="submit" name="changepassword" id="member_login" value="登録" tabindex="100" />
	</p>
</form>
</div>

</div>

<script type="text/javascript">
try{document.getElementById(\'loginpass1\').focus();}catch(e){}
</script>';
?>
