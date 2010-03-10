<div id="error"><?php echo $error_mes ?></div>
<table border="1" cellpadding="3">
    <tr>
        <th scope="row"><?php _e('e-mail adress', 'usces'); ?></th>
        <td><input name="mail1" type="text" id="mail1" onblur="mChk(this.value)" value="<?= $mail1 ?>" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row"><?php _e('e-mail adress', 'usces'); ?>(<?php _e('Re-input', 'usces'); ?>)</th>
        <td><input name="mail2" type="text" id="mail2" value="<?= $mail2 ?>" onpaste="return false" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row"><?php _e('Full name', 'usces'); ?></th>
        <td><span class="label2"><?php _e('Familly name', 'usces'); ?></span><input name="lastName" type="text" id="lastName" value="<?= $lastName ?>" /></td>
        <td><span class="label2"><?php _e('Given name', 'usces'); ?></span><input name="firstName" type="text" id="firstName" value="<?= $firstName ?>" /></td>
        <td>&nbsp;</td>
    </tr>
	<?php if( USCES_JP ): ?>
    <tr>
        <th scope="row"><?php _e('furigana', 'usces'); ?></th>
        <td><span class="label2"><?php _e('Familly name', 'usces'); ?></span><input name="lNameyomi" type="text" id="lNameyomi" value="<?= $lNameyomi ?>" /></td>
        <td><span class="label2"><?php _e('Given name', 'usces'); ?></span><input name="fNameyomi" type="text" id="fNameyomi" value="<?= $fNameyomi ?>" /></td>
        <td>&nbsp;</td>
    </tr>
 	<?php endif; ?>
   <tr>
        <th scope="row"><?php _e('Zip/Postal Code', 'usces'); ?></th>
        <td><input name="post1" type="text" id="post1" value="<?= $post1 ?>" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row"><?php _e('Province', 'usces'); ?></th>
        <td><select name="pref" id="pref">
<?php	for ($i = 0; $i < count($lib_KenName); $i++) ?>
			<option value="<?= $i ?>" <?php if($pref == $i){echo 'selected="selected"';} ?>><?= $lib_KenName[$i] ?></option>
		</select></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row"><?php _e('city', 'usces'); ?></th>
        <td><input name="city" type="text" id="city" value="<?= $city ?>" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row"><?php _e('numbers', 'usces'); ?></th>
        <td><input name="number" type="text" id="number" value="<?= $number ?>" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row"><?php _e('building name', 'usces'); ?></th>
        <td><input name="bldg" type="text" id="bldg" value="<?= $bldg ?>" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row"><?php _e('Phone number', 'usces'); ?></th>
        <td><input name="tel1" type="text" id="tel1" value="<?= $tel1 ?>" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <th scope="row"><?php _e('FAX number', 'usces'); ?></th>
        <td><input name="fax1" type="text" id="fax1" value="<?= $fax1 ?>" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
</table>





<fieldset>
<div id="exp"><img src="../image/listmark-1.gif" alt="listmark1" width="8" height="8" /><?php _e('This mark shows the required items.', 'usces'); ?><br />
<?php _e('Please make sure that your e-mail address is correct.', 'usces'); ?></div>
<label class="label-m" for="mail1"><?php _e('e-mail adress', 'usces'); ?></label>
<span class="control"><input name="mail1" type="text" id="mail1" onblur="mChk(this.value)" value="<?= $mail1 ?>" />
</span>
<label class="label-m" for="mail2"><?php _e('e-mail adress', 'usces'); ?>(<?php _e('Re-input', 'usces'); ?>)</label>
<span class="control"><input name="mail2" type="text" id="mail2" value="<?= $mail2 ?>" onpaste="return false" />
</span>
<label class="label-m" for="lastName"><?php _e('Full name', 'usces'); ?></label>
<span class="control">
<span class="label2"><?php _e('Familly name', 'usces'); ?></span><input name="lastName" type="text" id="lastName" value="<?= $lastName ?>" />
<span class="label2"><?php _e('Given name', 'usces'); ?></span><input name="firstName" type="text" id="firstName" value="<?= $firstName ?>" />
</span>
<?php if( USCES_JP ): ?>
<label class="label-m" for="lNameyomi"><?php _e('furigana', 'usces'); ?></label>
<span class="control">
<span class="label2"><?php _e('Familly name', 'usces'); ?></span><input name="lNameyomi" type="text" id="lNameyomi" value="<?= $lNameyomi ?>" />
<span class="label2"><?php _e('Given name', 'usces'); ?></span><input name="fNameyomi" type="text" id="fNameyomi" value="<?= $fNameyomi ?>" />
</span>
<?php endif; ?>
<label class="label-m" for="post1"><?php _e('Zip/Postal Code', 'usces'); ?></label>
<span class="control">
<span class="label2"><?php _e('zip code', 'usces'); ?></span><input name="post1" type="text" id="post1" value="<?= $post1 ?>" />
<span class="label2">－</span><input name="post2" type="text" id="post2" value="<?= $post2 ?>" /><input name="cmdPost" type="button" id="cmdPost" onClick="onClick_cmdPostSearch('');" value="住所検索" tabindex="13">
</span>
<label class="label-m" for="pref"><?php _e('Province', 'usces'); ?></label>
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
<label class="label-m" for="city"><?php _e('city', 'usces'); ?></label>
<span class="control"><input name="city" type="text" id="city" value="<?= $city ?>" /></span>
<label class="label-m" for="number"><?php _e('numbers', 'usces'); ?></label>
<span class="control"><input name="number" type="text" id="number" value="<?= $number ?>" /></span>
<label class="label" for="bldg"><?php _e('building name', 'usces'); ?></label>
<span class="control"><input name="bldg" type="text" id="bldg" value="<?= $bldg ?>" /></span>
<label class="label-m" for="tel1"><?php _e('Phone number', 'usces'); ?></label>
<span class="control">
<input name="tel1" type="text" id="tel1" value="<?= $tel1 ?>" /><span class="label2">－</span>
<input name="tel2" type="text" id="tel2" value="<?= $tel2 ?>" /><span class="label2">－</span>
<input name="tel3" type="text" id="tel3" value="<?= $tel3 ?>" />
</span>
<label class="label" for="fax1"><?php _e('FAX number', 'usces'); ?></label>
<span class="control">
<input name="fax1" type="text" id="fax1" value="<?= $fax1 ?>" /><span class="label2">－</span>
<input name="fax2" type="text" id="fax2" value="<?= $fax2 ?>" /><span class="label2">－</span>
<input name="fax3" type="text" id="fax3" value="<?= $fax3 ?>" />
</span>
</fieldset>
