<?php

$member_compmode = $this->page;
$html = '<div id="memberpages">

<div class="post">';

if ( $member_compmode == 'newcompletion' ) {
	$html .= '<p>新規ご入会有難うございます。</p>';
}else if ( $member_compmode == 'editcompletion' ) {
	$html .= '<p>会員情報を更新いたしました。</p>';
}else if ( $member_compmode == 'lostcompletion' ) {
	$html .= '<p>メールを送信いたしました。</p>
		<p>メールの内容にしたがってパスワードを変更してください。</p>';
}else if ( $member_compmode == 'changepasscompletion' ) {
	$html .= '<p>パスワードを変更いたしました。</p>';
}

$html .= '<p><a href="' . USCES_MEMBER_URL . '">会員情報ページはこちら</a></p>
	<form action="' . get_option('home') . '" method="post">
	<div class="send"><input name="top" type="submit" value="トップページへ戻る" /></div>
	</form>
	</div>

	</div>';
?>
