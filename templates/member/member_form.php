<?php
$usces_members = $this->get_member();

$html = '<div id="memberpages">

<div id="newmember">

<ul>
<li>この新規入会フォームより送信いただく、個人情報の取り扱いにつきましては細心の注意を払っております。</li>
<li>お預かりしたお客様の情報は本人様へのお問い合わせ内容についてのご返答や情報のご提供の目的であり、他の目的に使用することはございません。</li>
<li><strong>*</strong>印の項目に於いて、<strong>必須</strong>となっております。漏れなくご記入ください。</li>
<li><strong>英数字は半角</strong>での記入をお願いいたします。</li>
</ul>

<div class="error_message">' . $this->error_message . '</div>
<form action="' . USCES_MEMBER_URL . '" method="post">
<table border="0" cellpadding="0" cellspacing="0" class="customer_form">
<tr>
<th scope="row"><em>*</em>メールアドレス</th>
<td colspan="2"><input name="member[mailaddress1]" id="mailaddress1" type="text" value="' . $usces_members['mailaddress1'] . '" /></td>
</tr>
<tr>
<th scope="row"><em>*</em>メールアドレス（確認用）</th>
<td colspan="2"><input name="member[mailaddress2]" id="mailaddress2" type="text" value="' . $usces_members['mailaddress2'] . '" /></td>
</tr>
<tr>
<th scope="row"><em>*</em>パスワード</th>
<td colspan="2"><input name="member[password1]" id="password1" type="password" value="' . $usces_members['password1'] . '" /></td>
</tr>
<tr>
<th scope="row"><em>*</em>パスワード（確認用）</th>
<td colspan="2"><input name="member[password2]" id="password2" type="password" value="' . $usces_members['password2'] . '" /></td>
</tr>
<tr class="inp1">
<th scope="row"><em>*</em>お名前</th>
<td>姓<input name="member[name1]" id="name1" type="text" value="' . $usces_members['name1'] . '" /></td>
<td>名<input name="member[name2]" id="name2" type="text" value="' . $usces_members['name2'] . '" /></td>
</tr>
<tr class="inp1">
<th scope="row"><em>*</em>フリガナ</th>
<td>姓<input name="member[name3]" id="name3" type="text" value="' . $usces_members['name3'] . '" /></td>
<td>名<input name="member[name4]" id="name4" type="text" value="' . $usces_members['name4'] . '" /></td>
</tr>
<tr>
<th scope="row"><em>*</em>郵便番号</th>
<td colspan="2"><input name="member[zipcode]" id="zipcode" type="text" value="' . $usces_members['zipcode'] . '" />例）100-1000</td>
</tr>
<tr>
<th scope="row"><em>*</em>都道府県</th>
<td colspan="2">' . usces_the_pref( 'member', 'return' ) . '</td>
</tr>
<tr class="inp2">
<th scope="row"><em>*</em>市区郡町村</th>
<td colspan="2"><input name="member[address1]" id="address1" type="text" value="' . $usces_members['address1'] . '" />例）横浜市上北町</td>
</tr>
<tr>
<th scope="row"><em>*</em>番地</th>
<td colspan="2"><input name="member[address2]" id="address2" type="text" value="' . $usces_members['address2'] . '" />例）3-24-555</td>
</tr>
<tr>
<th scope="row">マンション･ビル名</th>
<td colspan="2"><input name="member[address3]" id="address3" type="text" value="' . $usces_members['address3'] . '" />例）通販ビル4F</td>
</tr>
<tr>
<th scope="row"><em>*</em>電話番号</th>
<td colspan="2"><input name="member[tel]" id="tel" type="text" value="' . $usces_members['tel'] . '" />例）1000-10-1000</td>
</tr>
<tr>
<th scope="row">FAX番号</th>
<td colspan="2"><input name="member[fax]" id="fax" type="text" value="' . $usces_members['fax'] . '" />例）1000-10-1000</td>
</tr>
</table>
<input name="member_regmode" type="hidden" value="' . $member_regmode . '" />
<div class="send"><input name="regmember" type="submit" value="送信する" /></div>
</form>
</div>

</div>';
?>
