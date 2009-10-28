<div id="error"><?= $error_mes ?></div>
<table border="1" cellpadding="3">
    <tr>
        <th scope="row">メールアドレス</th>
        <td><input name="mail1" type="text" id="mail1" onblur="mChk(this.value)" value="<?= $mail1 ?>" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row">メールアドレス（再入力）</th>
        <td><input name="mail2" type="text" id="mail2" value="<?= $mail2 ?>" onpaste="return false" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row">お名前</th>
        <td><span class="label2">姓</span><input name="lastName" type="text" id="lastName" value="<?= $lastName ?>" /></td>
        <td><span class="label2">名</span><input name="firstName" type="text" id="firstName" value="<?= $firstName ?>" /></td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row">フリガナ</th>
        <td><span class="label2">姓</span><input name="lNameyomi" type="text" id="lNameyomi" value="<?= $lNameyomi ?>" /></td>
        <td><span class="label2">名</span><input name="fNameyomi" type="text" id="fNameyomi" value="<?= $fNameyomi ?>" /></td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row">郵便番号</th>
        <td><input name="post1" type="text" id="post1" value="<?= $post1 ?>" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row">都道府県</th>
        <td><select name="pref" id="pref">
<?php	for ($i = 0; $i < count($lib_KenName); $i++) ?>
			<option value="<?= $i ?>" <?php if($pref == $i){echo 'selected="selected"';} ?>><?= $lib_KenName[$i] ?></option>
		</select></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row">市区郡町村</th>
        <td><input name="city" type="text" id="city" value="<?= $city ?>" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row">番地</th>
        <td><input name="number" type="text" id="number" value="<?= $number ?>" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row">マンション･ビル名</th>
        <td><input name="bldg" type="text" id="bldg" value="<?= $bldg ?>" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row">電話番号</th>
        <td><input name="tel1" type="text" id="tel1" value="<?= $tel1 ?>" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row">FAX番号</th>
        <td><input name="fax1" type="text" id="fax1" value="<?= $fax1 ?>" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
</table>





<fieldset>
<div id="exp"><img src="../image/listmark-1.gif" alt="listmark1" width="8" height="8" />このマークは必須項目です。<br />
メールアドレスの間違いが多くなっております。再度ご確認ください。</div>
<label class="label-m" for="mail1">メールアドレス</label>
<span class="control"><input name="mail1" type="text" id="mail1" onblur="mChk(this.value)" value="<?= $mail1 ?>" />
</span>
<label class="label-m" for="mail2">メールアドレス（再入力）</label>
<span class="control"><input name="mail2" type="text" id="mail2" value="<?= $mail2 ?>" onpaste="return false" />
</span>
<label class="label-m" for="lastName">お名前</label>
<span class="control">
<span class="label2">姓</span><input name="lastName" type="text" id="lastName" value="<?= $lastName ?>" />
<span class="label2">名</span><input name="firstName" type="text" id="firstName" value="<?= $firstName ?>" />
</span>
<label class="label-m" for="lNameyomi">フリガナ</label>
<span class="control">
<span class="label2">姓</span><input name="lNameyomi" type="text" id="lNameyomi" value="<?= $lNameyomi ?>" />
<span class="label2">名</span><input name="fNameyomi" type="text" id="fNameyomi" value="<?= $fNameyomi ?>" />
</span>
<label class="label-m" for="post1">郵便番号</label>
<span class="control">
<span class="label2">〒</span><input name="post1" type="text" id="post1" value="<?= $post1 ?>" />
<span class="label2">－</span><input name="post2" type="text" id="post2" value="<?= $post2 ?>" /><input name="cmdPost" type="button" id="cmdPost" onClick="onClick_cmdPostSearch('');" value="住所検索" tabindex="13">
</span>
<label class="label-m" for="pref">都道府県</label>
<span class="control">
<select name="pref" id="pref">
	<?php
for ($i = 0; $i < count($lib_KenName); $i++) {
?>
	<option value="<?= $i ?>" <?php if($pref == $i){echo 'selected="selected"';} ?>>
	<?= $lib_KenName[$i] ?>
	</option>
	<?php
}
?>
</select><input name="cmdAddressSearch" type="button" id="cmdPost" onClick="onClick_cmdAddressSearch('');" value="〒番号検索" />
</span>
<label class="label-m" for="city">市区郡町村</label>
<span class="control"><input name="city" type="text" id="city" value="<?= $city ?>" /></span>
<label class="label-m" for="number">番地</label>
<span class="control"><input name="number" type="text" id="number" value="<?= $number ?>" /></span>
<label class="label" for="bldg">マンション･ビル名</label>
<span class="control"><input name="bldg" type="text" id="bldg" value="<?= $bldg ?>" /></span>
<label class="label-m" for="tel1">電話番号</label>
<span class="control">
<input name="tel1" type="text" id="tel1" value="<?= $tel1 ?>" /><span class="label2">－</span>
<input name="tel2" type="text" id="tel2" value="<?= $tel2 ?>" /><span class="label2">－</span>
<input name="tel3" type="text" id="tel3" value="<?= $tel3 ?>" />
</span>
<label class="label" for="fax1">FAX番号</label>
<span class="control">
<input name="fax1" type="text" id="fax1" value="<?= $fax1 ?>" /><span class="label2">－</span>
<input name="fax2" type="text" id="fax2" value="<?= $fax2 ?>" /><span class="label2">－</span>
<input name="fax3" type="text" id="fax3" value="<?= $fax3 ?>" />
</span>
</fieldset>
