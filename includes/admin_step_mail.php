<?php
global $usces_settings;
$status = empty($usces->action_status) ? 'none' : $usces->action_status;
$message = $usces->action_message;
$usces->action_status = 'none';
$usces->action_message = '';

$smops = get_option('usces_stp_mail_opts');

$login_url_neo = isset($smops['neo']['login_url']) ? $smops['neo']['login_url'] : '';
$login_pass_neo = isset($smops['neo']['login_pass']) ? $smops['neo']['login_pass'] : '';
$linkage_neo = isset($smops['neo']['linkage']) ? $smops['neo']['linkage'] : '';
$key_neo = isset($smops['neo']['selected_id']) ? $smops['neo']['selected_id'] : '';
$params_neo = isset($smops['neo']['params']) ? $smops['neo']['params'] : array();
$neo = isset($params_neo[$key_neo]) ? $params_neo[$key_neo] : array();

$login_url_proste = isset($smops['proste']['login_url']) ? $smops['proste']['login_url'] : '';
$login_id_proste = isset($smops['proste']['login_id']) ? $smops['proste']['login_id'] : '';
$login_pass_proste = isset($smops['proste']['login_pass']) ? $smops['proste']['login_pass'] : '';
$linkage_proste = isset($smops['proste']['linkage']) ? $smops['proste']['linkage'] : '';
$key_proste = isset($smops['proste']['selected_id']) ? $smops['proste']['selected_id'] : '';
$params_proste = isset($smops['proste']['params']) ? $smops['proste']['params'] : array();
$proste = isset($params_proste[$key_proste]) ? $params_proste[$key_proste] : array();

?>
<script type="text/javascript">
jQuery(function($){
<?php if($status == 'success'){ ?>
			$("#anibox").animate({ backgroundColor: "#ECFFFF" }, 2000);
<?php }else if($status == 'caution'){ ?>
			$("#anibox").animate({ backgroundColor: "#FFF5CE" }, 2000);
<?php }else if($status == 'error'){ ?>
			$("#anibox").animate({ backgroundColor: "#FFE6E6" }, 2000);
<?php } ?>

	$("#aAdditionalURLs").click(function () {
		$("#AdditionalURLs").toggle();
	});

	$("#ml_release_neo").click(function () {
		if( 'new_ml' == $("#melmaga_neo").val() )
			return false;
			
		if( !confirm('メルマガ 「' + $("#melmaga_neo option:selected").text() + '」 を削除します。よろしいですか？') )
			return false;
	});

	$("#ml_release_proste").click(function () {
		if( 'new_ml' == $("#melmaga_proste").val() )
			return false;
			
		if( !confirm('メルマガ 「' + $("#melmaga_proste option:selected").text() + '」 を削除します。よろしいですか？') )
			return false;
	});

	$("#melmaga_neo").change(function () {
		if( 'new_ml' == $("#melmaga_neo").val() ){
			$("#ml_name_neo").val('');
			$("#ml_id_neo").val('');
			$("#ml_memo_neo").val('');
//			$("#ml_encode_neo").val('u');
			$("#ml_toadmin_neo").val(1);
			$("#ml_tocustomer_neo").val(1);
			$("#ml_user_name_neo").attr({checked: false});
			$("#ml_user_mail_neo").attr({checked: false});
			$("#ml_user_zip_neo").attr({checked: false});
			$("#ml_user_pref_neo").attr({checked: false});
			$("#ml_user_addr_neo").attr({checked: false});
//			$("#ml_user_cus1_neo").attr({checked: false});
//			$("#ml_user_cus2_neo").attr({checked: false});
//			$("#ml_user_cus3_neo").attr({checked: false});
//			$("#ml_user_cus4_neo").attr({checked: false});
//			$("#ml_user_cus5_neo").attr({checked: false});
		}
<?php foreach( $params_neo as $key => $neo ){ ?>
		else if( <?php echo $key; ?> == $("#melmaga_neo").val() ){
			$("#ml_name_neo").val('<?php echo $neo['ml_name']; ?>');
			$("#ml_id_neo").val('<?php echo $neo['ml_id']; ?>');
			$("#ml_memo_neo").val('<?php echo $neo['ml_memo']; ?>');
//			$("#ml_encode_neo").val('<?php echo $neo['ml_encode']; ?>');
			$("#ml_toadmin_neo").val('<?php echo $neo['ml_toadmin']; ?>');
			$("#ml_tocustomer_neo").val('<?php echo $neo['ml_tocustomer']; ?>');
			$("#ml_user_name_neo").attr({checked: <?php echo ($neo['ml_user_name'] ? 'true' : 'false'); ?>});
			$("#ml_user_mail_neo").attr({checked: <?php echo ($neo['ml_user_mail'] ? 'true' : 'false'); ?>});
			$("#ml_user_zip_neo").attr({checked: <?php echo ($neo['ml_user_zip'] ? 'true' : 'false'); ?>});
			$("#ml_user_pref_neo").attr({checked: <?php echo ($neo['ml_user_pref'] ? 'true' : 'false'); ?>});
			$("#ml_user_addr_neo").attr({checked: <?php echo ($neo['ml_user_addr'] ? 'true' : 'false'); ?>});
//			$("#ml_user_cus1_neo").attr({checked: <?php echo ($neo['ml_user_cus1'] ? 'true' : 'false'); ?>});
//			$("#ml_user_cus2_neo").attr({checked: <?php echo ($neo['ml_user_cus2'] ? 'true' : 'false'); ?>});
//			$("#ml_user_cus3_neo").attr({checked: <?php echo ($neo['ml_user_cus3'] ? 'true' : 'false'); ?>});
//			$("#ml_user_cus4_neo").attr({checked: <?php echo ($neo['ml_user_cus4'] ? 'true' : 'false'); ?>});
//			$("#ml_user_cus5_neo").attr({checked: <?php echo ($neo['ml_user_cus5'] ? 'true' : 'false'); ?>});
		}
<?php } ?>		
	});

	$("#melmaga_proste").change(function () {
		if( 'new_ml' == $("#melmaga_proste").val() ){
			$("#ml_name_proste").val('');
			$("#ml_id_proste").val('');
			$("#ml_memo_proste").val('');
//			$("#ml_encode_proste").val('u');
			$("#ml_toadmin_proste").val(1);
			$("#ml_tocustomer_proste").val(1);
			$("#ml_user_name_proste").attr({checked: false});
			$("#ml_user_mail_proste").attr({checked: false});
			$("#ml_user_zip_proste").attr({checked: false});
			$("#ml_user_pref_proste").attr({checked: false});
			$("#ml_user_addr1_proste").attr({checked: false});
			$("#ml_user_addr2_proste").attr({checked: false});
			$("#ml_user_cus1_proste").attr({checked: false});
			$("#ml_user_cus2_proste").attr({checked: false});
			$("#ml_user_cus3_proste").attr({checked: false});
			$("#ml_user_cus4_proste").attr({checked: false});
			$("#ml_user_cus5_proste").attr({checked: false});
		}
<?php foreach( $params_proste as $key => $proste ){ ?>
		else if( <?php echo $key; ?> == $("#melmaga_proste").val() ){
			$("#ml_name_proste").val('<?php echo $proste['ml_name']; ?>');
			$("#ml_id_proste").val('<?php echo $proste['ml_id']; ?>');
			$("#ml_memo_proste").val('<?php echo $proste['ml_memo']; ?>');
//			$("#ml_encode_proste").val('<?php echo $proste['ml_encode']; ?>');
			$("#ml_toadmin_proste").val('<?php echo $proste['ml_toadmin']; ?>');
			$("#ml_tocustomer_proste").val('<?php echo $proste['ml_tocustomer']; ?>');
			$("#ml_user_name_proste").attr({checked: <?php echo ($proste['ml_user_name'] ? 'true' : 'false'); ?>});
			$("#ml_user_mail_proste").attr({checked: <?php echo ($proste['ml_user_mail'] ? 'true' : 'false'); ?>});
			$("#ml_user_zip_proste").attr({checked: <?php echo ($proste['ml_user_zip'] ? 'true' : 'false'); ?>});
			$("#ml_user_pref_proste").attr({checked: <?php echo ($proste['ml_user_pref'] ? 'true' : 'false'); ?>});
			$("#ml_user_addr1_proste").attr({checked: <?php echo ($proste['ml_user_addr1'] ? 'true' : 'false'); ?>});
			$("#ml_user_addr2_proste").attr({checked: <?php echo ($proste['ml_user_addr2'] ? 'true' : 'false'); ?>});
			$("#ml_user_cus1_proste").attr({checked: <?php echo ($proste['ml_user_cus1'] ? 'true' : 'false'); ?>});
			$("#ml_user_cus2_proste").attr({checked: <?php echo ($proste['ml_user_cus2'] ? 'true' : 'false'); ?>});
			$("#ml_user_cus3_proste").attr({checked: <?php echo ($proste['ml_user_cus3'] ? 'true' : 'false'); ?>});
			$("#ml_user_cus4_proste").attr({checked: <?php echo ($proste['ml_user_cus4'] ? 'true' : 'false'); ?>});
			$("#ml_user_cus5_proste").attr({checked: <?php echo ($proste['ml_user_cus5'] ? 'true' : 'false'); ?>});
		}
<?php } ?>		
	});

	var $tabs = $('#uscestabs_system').tabs({
		cookie: {
			expires: 1
		}
	});
});

function toggleVisibility(id) {
   var e = document.getElementById(id);
   if(e.style.display == 'block')
	  e.style.display = 'none';
   else
	  e.style.display = 'block';
}
</script>
<style type="text/css">
<!--
.step_mail_button {
	margin-bottom: 15px;
}
.midasi {
	padding-top: 10px;
	padding-right: 10px;
	padding-bottom: 0px;
	padding-left: 10px;
}
.midasi span {
	color: #333333;
	background-color: #E4E4E4;
	font-size: 1.2em;
	padding-top: 2px;
	padding-right: 20px;
	padding-bottom: 2px;
	padding-left: 40px;
	background-image: url(<?php echo USCES_PLUGIN_URL; ?>/images/stepmail_listbutton.jpg);
	background-repeat: no-repeat;
}
.midasi p {
	padding: 10px;
}
.midasi em {
	color: #FF0000;
}
.step_table{
	margin-left: 25px;
	margin-bottom: 10px;
	margin-top: 0px;
}
.step_table caption {
	font-size: 1.1em;
	text-align: left;
	font-weight: bold;
	color: #666666;
}
.step_table caption span.mark {
	font-size: 1.8em;
}
.step_table th {
	font-weight: normal;
	color: #666666;
	width: 170px;
	text-align: left;
	text-indent: 18px;
	height: 28px;
}

.stepmail_login_button{
	margin-top: 10px;
	margin-bottom: 15px;
	margin-left: 30px;
}
.stepmail_set_button{
	margin-left: 20px;
}

-->
</style>

<div class="wrap">
<div class="usces_admin">
<h2>Welcart Shop ステップメール連携</h2>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>
<div id="poststuff" class="metabox-holder">

<div class="uscestabs" id="uscestabs_system">
	<ul>
		<li><a href="#step_mail_neo">NEO</a></li>
		<li><a href="#step_mail_proste">プロステ</a></li>
	</ul>
	
	<div id="step_mail_neo">
		<a href="http://<?php echo $login_url_neo; ?>/ctrl.php" target="_blank"><img class="step_mail_button" src="<?php echo USCES_PLUGIN_URL; ?>/images/neo_button.jpg" alt="neoの管理画面へ移動する" /></a>
		<div class="postbox">
			<h3 class="hndle"><span>ログイン設定</span></h3>
			<div class="inside">
				<div class="midasi">
				<span>neo のログイン情報の設定を行ないます。</span>
				<p>neo のログイン情報は、全メルマガ共通設定になります。</p>
				</div>
				<form action="" method="post" name="option_form" id="option_form">
				<table class="step_table">
					<caption align="top"><span class="mark">■</span> neo ログイン情報</caption>
					<tr>
						<th>ログインURL</th>
						<td>http://<input name="login_url_neo" type="text" id="login_url_neo" value="<?php echo esc_attr($login_url_neo); ?>" size="50" />/ctrl.php</td>
					</tr>
					<tr>
						<th>パスワード</th>
						<td><input name="login_pass_neo" type="password" id="login_pass_neo" value="<?php echo $login_pass_neo; ?>" /></td>
					</tr>
				</table>
				<input name="stepmail_login_neo" type="submit" class="button stepmail_login_button" value="　設定する　" />
				</form>
			</div>
		</div><!--postbox-->
	
		<div class="postbox">
			<h3 class="hndle"><span>メルマガ設定</span></h3>
			<div class="inside">
				<div class="midasi">
				<span>neo で作成したメルマガとの関連付けを行ないます。</span>
				<p>
				<em>メルマガを新しく追加する場合</em> ： プルダウンから「新規追加」を選び、各項目を入力した上で「設定する」ボタンを押します。<br />
				<em>設定済みのメルマガを修正する場合</em> ： プルダウンから該当するメルマガを選び、各項目を修正した上で「設定する」ボタンを押します。<br />
				<em>メルマガを削除したい場合</em> ： プルダウンから該当するメルマガ名を選び「削除する」ボタンを押します。<br />
				</p>
				</div>
				<form action="" method="post" name="option_form" id="option_form">
				<table class="step_table">
					<caption align="top"><span class="mark">■</span> 新規会員登録時の連携</caption>
					<tr>
						<th>&nbsp;</th>
						<td width="10"><input name="linkage_neo" id="linkage_neo1" type="radio" value="1"<?php if($linkage_neo === 1) echo 'checked="checked"'; ?> /></td><td width="70"><label for="linkage_neo1">有効</label></td>
						<td width="10"><input name="linkage_neo" id="linkage_neo0" type="radio" value="0"<?php if($linkage_neo === 0) echo 'checked="checked"'; ?> /></td><td width="70"><label for="linkage_neo0">無効</label></td>
					</tr>
				</table>
				<table class="step_table">
					<caption align="top"><span class="mark">■</span> メルマガ選択</caption>
					<tr>
						<th>&nbsp;</th>
						<td>
						<select name="melmaga_neo" id="melmaga_neo">
							<option value="new_ml">新規追加</option>
							<?php
							foreach( $params_neo as $key => $param ){
								$selected = ( $key_neo == $key ) ? ' selected="selected"' : '';
							?>
							<option value="<?php echo $key; ?>"<?php echo ($key_neo == $key ? ' selected="selected"' : ''); ?>><?php esc_html_e($param['ml_name']); ?></option>
							<?php } ?>
						</select>
						</td>
						<td><input name="ml_setup_neo" id="ml_setup_neo" type="submit" class="button stepmail_set_button" value="　設定する　" /></td>
						<td><input name="ml_release_neo" id="ml_release_neo" type="submit" class="button stepmail_set_button" value="　削除する　" /></td>
					</tr>
				</table>
				<table class="step_table">
					<caption align="top"><span class="mark">■</span> メルマガ項目設定</caption>
					<tr>
						<th>メルマガ名称</th>
						<td><input name="ml_name" type="text" id="ml_name_neo" value="<?php esc_attr_e($neo['ml_name']); ?>" size="40" /></td>
					</tr>
					<tr>
						<th>登録先メルマガID</th>
						<td><input name="ml_id" type="text" id="ml_id_neo" value="<?php esc_attr_e($neo['ml_id']); ?>" size="40" /></td>
					</tr>
					<tr>
						<th>登録メモ</th>
						<td><input name="ml_memo" type="text" id="ml_memo_neo" value="<?php esc_attr_e($neo['ml_memo']); ?>" size="40" /></td>
					</tr>
<!--					<tr>
						<th>文字コード</th>
						<td>
						<select name="ml_encode" id="ml_encode_neo">
							<option value="u"<?php echo ('u' == $neo['ml_encode'] ? ' selected="selected"' : ''); ?>>UTF-8</option>
							<option value="e"<?php echo ('e' == $neo['ml_encode'] ? ' selected="selected"' : ''); ?>>EUC</option>
							<option value="s"<?php echo ('s' == $neo['ml_encode'] ? ' selected="selected"' : ''); ?>>Shift-JIS</option>
							<option value="j"<?php echo ('j' == $neo['ml_encode'] ? ' selected="selected"' : ''); ?>>JIS</option>
							<option value="i"<?php echo ('i' == $neo['ml_encode'] ? ' selected="selected"' : ''); ?>>iso-8859-1</option>
						</select>
						</td>
					</tr>
-->
					<tr>
						<th>管理人宛登録通知メール</th>
						<td>
						<select name="ml_toadmin" id="ml_toadmin_neo">
							<option value="1"<?php echo ('1' == $neo['ml_toadmin'] ? ' selected="selected"' : ''); ?>>送信する</option>
							<option value="0"<?php echo ('0' == $neo['ml_toadmin'] ? ' selected="selected"' : ''); ?>>送信しない</option>
						</select>
						</td>
					</tr>
					<tr>
						<th>登録者宛登録通知メール</th>
						<td>
						<select name="ml_tocustomer" id="ml_tocustomer_neo">
							<option value="1"<?php echo ('1' == $neo['ml_tocustomer'] ? ' selected="selected"' : ''); ?>>送信する</option>
							<option value="0"<?php echo ('0' == $neo['ml_tocustomer'] ? ' selected="selected"' : ''); ?>>送信しない</option>
						</select>
						</td>
					</tr>
				</table>
				<table class="step_table">
					<caption align="top"><span class="mark">■</span> 登録者フォームの項目設定</caption>
					<tr>
						<th>名前</th>
						<td width="50"><input name="ml_user_name" type="checkbox" id="ml_user_name_neo" value="1"<?php echo (1 == $neo['ml_user_name'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>メールアドレス</th>
						<td width="50"><input name="ml_user_mail" type="checkbox" id="ml_user_mail_neo" value="1"<?php echo (1 == $neo['ml_user_mail'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>郵便番号</th>
						<td width="50"><input name="ml_user_zip" type="checkbox" id="ml_user_zip_neo" value="1"<?php echo (1 == $neo['ml_user_zip'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>都道府県</th>
						<td width="50"><input name="ml_user_pref" type="checkbox" id="ml_user_pref_neo" value="1"<?php echo (1 == $neo['ml_user_pref'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>住所</th>
						<td width="50"><input name="ml_user_addr" type="checkbox" id="ml_user_addr_neo" value="1"<?php echo (1 == $neo['ml_user_addr'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
<!--					<tr>
						<th>カスタム1</th>
						<td width="50"><input name="ml_user_cus1" type="checkbox" id="ml_user_cus1_neo" value="1"<?php echo (1 == $neo['ml_user_cus1'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>カスタム2</th>
						<td width="50"><input name="ml_user_cus2" type="checkbox" id="ml_user_cus2_neo" value="1"<?php echo (1 == $neo['ml_user_cus2'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>カスタム3</th>
						<td width="50"><input name="ml_user_cus3" type="checkbox" id="ml_user_cus3_neo" value="1"<?php echo (1 == $neo['ml_user_cus3'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>カスタム4</th>
						<td width="50"><input name="ml_user_cus4" type="checkbox" id="ml_user_cus4_neo" value="1"<?php echo (1 == $neo['ml_user_cus4'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>カスタム5</th>
						<td width="50"><input name="ml_user_cus5" type="checkbox" id="ml_user_cus5_neo" value="1"<?php echo (1 == $neo['ml_user_cus5'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
-->
				</table>
				</form>
			</div>
		</div><!--postbox-->
	</div><!--step_mail_neo-->

	<div id="step_mail_proste">
		<a href="http://<?php echo $login_id_proste; ?>:<?php echo $login_pass_proste; ?>@<?php echo $login_url_proste; ?>/admin/" target="_blank"><img class="step_mail_button" src="<?php echo USCES_PLUGIN_URL; ?>/images/proste_button.jpg" alt="プロステの管理画面へ移動する" /></a>
		<div class="postbox">
			<h3 class="hndle"><span>ログイン設定</span></h3>
			<div class="inside">
				<div class="midasi">
				<span>プロステのログイン情報の設定を行ないます。</span>
				<p>プロステのログイン情報は、全メルマガ共通設定になります。</p>
				</div>
				<form action="" method="post" name="option_form" id="option_form">
				<table class="step_table">
					<caption align="top"><span class="mark">■</span> プロステ ログイン情報</caption>
					<tr>
						<th>ログインURL</th>
						<td>http://<input name="login_url_proste" type="text" id="login_url_proste" value="<?php echo esc_attr($login_url_proste); ?>" size="50" />/admin/</td>
					</tr>
					<tr>
						<th>プロステID</th>
						<td><input name="login_id_proste" type="text" id="login_id_proste" value="<?php echo esc_attr($login_id_proste); ?>" /></td>
					</tr>
					<tr>
						<th>パスワード</th>
						<td><input name="login_pass_proste" type="password" id="login_pass_proste" value="<?php echo $login_pass_proste; ?>" /></td>
					</tr>
				</table>
				<input name="stepmail_login_proste" type="submit" class="button stepmail_login_button" value="　設定する　" />
				</form>
			</div>
		</div><!--postbox-->
	
		<div class="postbox">
			<h3 class="hndle"><span>メルマガ設定</span></h3>
			<div class="inside">
				<div class="midasi">
				<span>プロステで作成したメルマガとの関連付けを行ないます。</span>
				<p>
				<em>メルマガを新しく追加する場合</em> ： プルダウンから「新規追加」を選び、各項目を入力した上で「設定する」ボタンを押します。<br />
				<em>設定済みのメルマガを修正する場合</em> ： プルダウンから該当するメルマガを選び、各項目を修正した上で「設定する」ボタンを押します。<br />
				<em>メルマガを削除したい場合</em> ： プルダウンから該当するメルマガ名を選び「削除する」ボタンを押します。<br />
				</p>
				</div>
				<form action="" method="post" name="option_form" id="option_form">
				<table class="step_table">
					<caption align="top"><span class="mark">■</span> 新規会員登録時の連携</caption>
					<tr>
						<th>&nbsp;</th>
						<td width="10"><input name="linkage_proste" id="linkage_proste1" type="radio" value="1"<?php if($linkage_proste === 1) echo 'checked="checked"'; ?> /></td><td width="70"><label for="linkage_proste1">有効</label></td>
						<td width="10"><input name="linkage_proste" id="linkage_proste0" type="radio" value="0"<?php if($linkage_proste === 0) echo 'checked="checked"'; ?> /></td><td width="70"><label for="linkage_proste0">無効</label></td>
					</tr>
				</table>
				<table class="step_table">
					<caption align="top"><span class="mark">■</span> プラン選択</caption>
					<tr>
						<th>&nbsp;</th>
						<td>
						<select name="melmaga_proste" id="melmaga_proste">
							<option value="new_ml">新規追加</option>
							<?php
							foreach( $params_proste as $key => $param ){
								$selected = ( $key_proste == $key ) ? ' selected="selected"' : '';
							?>
							<option value="<?php echo $key; ?>"<?php echo ($key_proste == $key ? ' selected="selected"' : ''); ?>><?php esc_html_e($param['ml_name']); ?></option>
							<?php } ?>
						</select>
						</td>
						<td><input name="ml_setup_proste" id="ml_setup_proste" type="submit" class="button stepmail_set_button" value="　設定する　" /></td>
						<td><input name="ml_release_proste" id="ml_release_proste" type="submit" class="button stepmail_set_button" value="　削除する　" /></td>
					</tr>
				</table>
				<table class="step_table">
					<caption align="top"><span class="mark">■</span> プラン項目設定</caption>
					<tr>
						<th>プラン名称</th>
						<td><input name="ml_name" type="text" id="ml_name_proste" value="<?php esc_attr_e($proste['ml_name']); ?>" size="40" /></td>
					</tr>
					<tr>
						<th>登録先プランID</th>
						<td><input name="ml_id" type="text" id="ml_id_proste" value="<?php esc_attr_e($proste['ml_id']); ?>" size="40" /></td>
					</tr>
					<tr>
						<th>秘密キー</th>
						<td><input name="ml_memo" type="password" id="ml_memo_proste" value="<?php esc_attr_e($proste['ml_memo']); ?>" size="40" /></td>
					</tr>
<!--					<tr>
						<th>サンクスページの文字コード</th>
						<td>
						<select name="ml_encode" id="ml_encode_proste">
							<option value="u"<?php echo ('u' == $proste['ml_encode'] ? ' selected="selected"' : ''); ?>>UTF-8</option>
							<option value="e"<?php echo ('e' == $proste['ml_encode'] ? ' selected="selected"' : ''); ?>>EUC</option>
							<option value="s"<?php echo ('s' == $proste['ml_encode'] ? ' selected="selected"' : ''); ?>>Shift-JIS</option>
							<option value="j"<?php echo ('j' == $proste['ml_encode'] ? ' selected="selected"' : ''); ?>>JIS</option>
							<option value="i"<?php echo ('i' == $proste['ml_encode'] ? ' selected="selected"' : ''); ?>>iso-8859-1</option>
						</select>
						</td>
					</tr>
-->					
					<tr>
						<th>管理人宛登録通知メール</th>
						<td>
						<select name="ml_toadmin" id="ml_toadmin_proste">
							<option value="1"<?php echo ('1' == $proste['ml_toadmin'] ? ' selected="selected"' : ''); ?>>送信する</option>
							<option value="0"<?php echo ('0' == $proste['ml_toadmin'] ? ' selected="selected"' : ''); ?>>送信しない</option>
						</select>
						</td>
					</tr>
					<tr>
						<th>登録者宛登録通知メール</th>
						<td>
						<select name="ml_tocustomer" id="ml_tocustomer_proste">
							<option value="1"<?php echo ('1' == $proste['ml_tocustomer'] ? ' selected="selected"' : ''); ?>>送信する</option>
							<option value="0"<?php echo ('0' == $proste['ml_tocustomer'] ? ' selected="selected"' : ''); ?>>送信しない</option>
						</select>
						</td>
					</tr>
				</table>
				<table class="step_table">
					<caption align="top"><span class="mark">■</span> 登録フォームの項目設定</caption>
					<tr>
						<th>名前（姓）</th>
						<td width="100"><input name="ml_user_name1" type="checkbox" id="ml_user_name_proste1" value="1" checked="checked" />（必須）</td>
					</tr>
					<tr>
						<th>名前（名）</th>
						<td width="100"><input name="ml_user_name2" type="checkbox" id="ml_user_name_proste2" value="1"<?php echo (1 == $proste['ml_user_name2'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>メールアドレス</th>
						<td width="100"><input name="ml_user_mail" type="checkbox" id="ml_user_mail_proste" value="1" checked="checked" />（必須）</td>
					</tr>
					<tr>
						<th>郵便番号</th>
						<td width="100"><input name="ml_user_zip" type="checkbox" id="ml_user_zip_proste" value="1"<?php echo (1 == $proste['ml_user_zip'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>都道府県</th>
						<td width="100"><input name="ml_user_pref" type="checkbox" id="ml_user_pref_proste" value="1"<?php echo (1 == $proste['ml_user_pref'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>住所1</th>
						<td width="100"><input name="ml_user_addr1" type="checkbox" id="ml_user_addr1_proste" value="1"<?php echo (1 == $proste['ml_user_addr1'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>住所2</th>
						<td width="100"><input name="ml_user_addr2" type="checkbox" id="ml_user_addr2_proste" value="1"<?php echo (1 == $proste['ml_user_addr2'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>カスタム1</th>
						<td width="100"><input name="ml_user_cus1" type="checkbox" id="ml_user_cus1_proste" value="1"<?php echo (1 == $proste['ml_user_cus1'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>カスタム2</th>
						<td width="100"><input name="ml_user_cus2" type="checkbox" id="ml_user_cus2_proste" value="1"<?php echo (1 == $proste['ml_user_cus2'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>カスタム3</th>
						<td width="100"><input name="ml_user_cus3" type="checkbox" id="ml_user_cus3_proste" value="1"<?php echo (1 == $proste['ml_user_cus3'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>カスタム4</th>
						<td width="100"><input name="ml_user_cus4" type="checkbox" id="ml_user_cus4_proste" value="1"<?php echo (1 == $proste['ml_user_cus4'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
					<tr>
						<th>カスタム5</th>
						<td width="100"><input name="ml_user_cus5" type="checkbox" id="ml_user_cus5_proste" value="1"<?php echo (1 == $proste['ml_user_cus5'] ? ' checked="checked"' : ''); ?> /></td>
					</tr>
				</table>
				</form>
			</div>
		</div><!--postbox-->
	</div><!--step_mail_proste-->
</div><!--uscestabs_system-->
<!--20110331ysk end-->
</div><!--poststuff-->



</div><!--usces_admin-->
</div><!--wrap-->