<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';

$opts = $this->options['acting_settings'];
//20110208ysk start
$openssl = extension_loaded('openssl');
$curl = extension_loaded('curl');
//20110208ysk end
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

	var $tabs = $('#uscestabs').tabs({
		cookie: {
			// store cookie for a day, without, it would be a session cookie
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
<div class="wrap">
<div class="usces_admin">
<h2><?php _e('Settlement Setting','usces'); ?></h2>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>

<div id="poststuff" class="metabox-holder">
<div class="postbox">
<h3 class="hndle"><span>決済サービス設定</span></h3>
<div class="inside">
<div id="uscestabs">

	<ul>
		<li><a href="#uscestabs_zeus">ゼウス</a></li>
		<li><a href="#uscestabs_remise">ルミーズ</a></li>
<!--20101018ysk start-->
		<li><a href="#uscestabs_jpayment">J-Payment</a></li>
<!--20101018ysk end-->
<!--20110208ysk start-->
		<li><a href="#uscestabs_paypal">PayPal</a></li>
<!--20110208ysk end-->
	</ul>


	<div id="uscestabs_zeus">
	<div class="settlement_service"><span class="service_title">ゼウス決済サービス</span></a></div>

	<?php if( isset($_POST['acting']) && 'zeus' == $_POST['acting'] ){ ?>	
		<?php if( 'on' == $opts['zeus']['activate'] ){ ?>	
		<div class="message">十分にテストを行ってから運用してください。</div>
		<?php }else if( '' != $mes ){ ?>
		<div class="error_message"><?php echo $mes; ?></div>
		<?php } ?>
	<?php } ?>
	<form action="" method="post" name="zeus_form" id="zeus_form">
		<table class="settle_table">
			<tr>
				<th>クレジットカード決済</th>
				<td><input name="card_activate" type="radio" id="card_activate_zeus_1" value="on"<?php if( $opts['zeus']['card_activate'] == 'on' ) echo ' checked="checked"' ?> /></td><td><label for="card_activate_zeus_1">利用する</label></td>
				<td><input name="card_activate" type="radio" id="card_activate_zeus_2" value="off"<?php if( $opts['zeus']['card_activate'] == 'off' ) echo ' checked="checked"' ?> /></td><td><label for="card_activate_zeus_2">利用しない</label></td>
				<td></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_clid_zeus');"><?php _e('カード決済IPコード', 'usces'); ?></a></th>
				<td colspan="4"><input name="clientip" type="text" id="clid_zeus" value="<?php echo esc_html($opts['zeus']['clientip']); ?>" size="40" /></td>
				<td><div id="ex_clid_zeus" class="explanation"><?php _e('契約時にZEUSから発行されるクレジットカード決済用のIPコード（半角数字）', 'usces'); ?></div></td>
			</tr>
<!-- ZEUS3D
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_authkey_zeus');"><?php _e('認証キー', 'usces'); ?></a></th>
				<td colspan="4"><input name="authkey" type="text" id="clid_zeus" value="<?php echo esc_html($opts['zeus']['authkey']); ?>" size="40" /></td>
				<td><div id="ex_authkey_zeus" class="explanation"><?php _e('契約時にZEUSから発行される認証キー（半角数字）', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_3dsecure_zeus');"><?php _e('3Dセキュア', 'usces'); ?></a></th>
				<td><input name="3dsecure" type="radio" id="3dsecure_zeus_1" value="on"<?php if( $opts['zeus']['3dsecure'] == 'on' ) echo ' checked="checked"' ?> /></td><td><label for="3dsecure_zeus_1">利用する</label></td>
				<td><input name="3dsecure" type="radio" id="3dsecure_zeus_2" value="off"<?php if( $opts['zeus']['3dsecure'] == 'off' ) echo ' checked="checked"' ?> /></td><td><label for="3dsecure_zeus_2">利用しない</label></td>
				<td><div id="ex_3dsecure_zeus" class="explanation"><?php _e('3Dセキュア(本人認証サービス)に対応しているクレジットカードをご利用される場合は、クレジットカードに記載されている情報に加え、「自分しか知らないパスワード」を合わせて認証することになります。<br />認証キーが必須となります。', 'usces'); ?></div></td>
			</tr>
ZEUS3D -->
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_quickcharge_zeus');">クイックチャージ</a></th>
				<td><input name="quickcharge" type="radio" id="quickcharge_zeus_1" value="on"<?php if( $opts['zeus']['quickcharge'] == 'on' ) echo ' checked="checked"' ?> /></td><td><label for="quickcharge_zeus_1">利用する</label></td>
				<td><input name="quickcharge" type="radio" id="quickcharge_zeus_2" value="off"<?php if( $opts['zeus']['quickcharge'] == 'off' ) echo ' checked="checked"' ?> /></td><td><label for="quickcharge_zeus_2">利用しない</label></td>
				<td><div id="ex_quickcharge_zeus" class="explanation"><?php _e('ログインして一度購入したメンバーは、次の購入時にはカード番号を入力する必要がなくなります。', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th>お客様の支払方法</th>
				<td><input name="howpay" type="radio" id="howpay_zeus_1" value="on"<?php if( $opts['zeus']['howpay'] == 'on' ) echo ' checked="checked"' ?> /></td><td><label for="howpay_zeus_1">分割払いに対応する</label></td>
				<td><input name="howpay" type="radio" id="howpay_zeus_2" value="off"<?php if( $opts['zeus']['howpay'] == 'off' ) echo ' checked="checked"' ?> /></td><td><label for="howpay_zeus_2">一括払いのみ</label></td>
				<td></td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_bank_zeus');">入金お任せサービス</a></th>
				<td><input name="bank_activate" type="radio" id="bank_activate_zeus_1" value="on"<?php if( $opts['zeus']['bank_activate'] == 'on' ) echo ' checked="checked"' ?> /></td><td><label for="bank_activate_zeus_1">利用する</label></td>
				<td><input name="bank_activate" type="radio" id="bank_activate_zeus_2" value="off"<?php if( $opts['zeus']['bank_activate'] == 'off' ) echo ' checked="checked"' ?> /></td><td><label for="bank_activate_zeus_2">利用しない</label></td>
				<td><div id="ex_bank_zeus" class="explanation"><?php _e('銀行振り込み支払いの自動照会機能です。振込みが有った場合、自動的に入金済みになり、入金確認メールが自動送信されます。', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_bank_clid_zeus');"><?php _e('入金お任せIPコード', 'usces'); ?></a></th>
				<td colspan="4"><input name="clientip_bank" type="text" id="bank_clid_zeus" value="<?php echo esc_html($opts['zeus']['clientip_bank']); ?>" size="40" /></td>
				<td><div id="ex_bank_clid_zeus" class="explanation"><?php _e('契約時にZEUSから発行される入金お任せサービス用のIPコード（半角数字）', 'usces'); ?></div></td>
			</tr>
<!--			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_bank_testid_zeus');"><?php _e('テストカード番号', 'usces'); ?></a></th>
				<td colspan="4"><input name="testid_bank" type="text" id="testid_bank_zeus" value="<?php echo esc_html($opts['zeus']['testid_bank']); ?>" size="40" /></td>
				<td><div id="ex_bank_testid_zeus" class="explanation"><?php _e('契約時にZEUSから発行される入金お任せサービス接続テストで必要なカード番号です。（半角数字）<br />本稼動の場合は空白にしてください。', 'usces'); ?></div></td>
			</tr>
-->
		</table>
		<table class="settle_table">
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_conv_zeus');">コンビニ決済サービス</a></th>
				<td><input name="conv_activate" type="radio" id="conv_activate_zeus_1" value="on"<?php if( $opts['zeus']['conv_activate'] == 'on' ) echo ' checked="checked"' ?> /></td><td><label for="conv_activate_zeus_1">利用する</label></td>
				<td><input name="conv_activate" type="radio" id="conv_activate_zeus_2" value="off"<?php if( $opts['zeus']['conv_activate'] == 'off' ) echo ' checked="checked"' ?> /></td><td><label for="conv_activate_zeus_2">利用しない</label></td>
				<td colspan="2"></td>
				<td><div id="ex_conv_zeus" class="explanation"><?php _e('コンビニ支払いができる決済サービスです。払い込みが有った場合、自動的に入金済みになります。', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_conv_clid_zeus');"><?php _e('コンビニ決済IPコード', 'usces'); ?></a></th>
				<td colspan="6"><input name="clientip_conv" type="text" id="conv_clid_zeus" value="<?php echo esc_html($opts['zeus']['clientip_conv']); ?>" size="40" /></td>
				<td><div id="ex_conv_clid_zeus" class="explanation"><?php _e('契約時にZEUSから発行されるコンビニ決済サービス用のIPコード（半角数字）', 'usces'); ?></div></td>
			</tr>
<!--			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_conv_testid_zeus');"><?php _e('テストカード番号', 'usces'); ?></a></th>
				<td colspan="6"><input name="testid_conv" type="text" id="testid_conv_zeus" value="<?php echo esc_html($opts['zeus']['testid_conv']); ?>" size="40" /></td>
				<td><div id="ex_conv_testid_zeus" class="explanation"><?php _e('契約時にZEUSから発行されるコンビニ決済サービス接続テストで必要なカード番号です。（半角数字）<br />本稼動の場合は空白にしてください。', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_conv_testtype_zeus');">テストタイプ</a></th>
				<td><input name="test_type" type="radio" id="conv_testtype_zeus_1" value="0"<?php if( $opts['zeus']['test_type_conv'] == '0' ) echo ' checked="checked"' ?> /></td><td><label for="conv_testtype_zeus_1">入金テスト無し</label></td>
				<td><input name="test_type" type="radio" id="conv_testtype_zeus_2" value="1"<?php if( $opts['zeus']['test_type_conv'] == '1' ) echo ' checked="checked"' ?> /></td><td><label for="conv_testtype_zeus_2">売上確定テスト</label></td>
				<td><input name="test_type" type="radio" id="conv_testtype_zeus_3" value="2"<?php if( $opts['zeus']['test_type_conv'] == '2' ) echo ' checked="checked"' ?> /></td><td><label for="conv_testtype_zeus_3">売上取消テスト</label></td>
				<td><div id="ex_conv_testtype_zeus" class="explanation"><?php _e('テスト環境でのテストタイプを指定します。テストカード番号が空白のときはこの項目は無効になります。', 'usces'); ?></div></td>
			</tr>
-->
		</table>
		<input name="conv_url" type="hidden" value="https://linkpt.cardservice.co.jp/cgi-bin/cvs.cgi" />
		<input name="bank_url" type="hidden" value="https://linkpt.cardservice.co.jp/cgi-bin/ebank.cgi" />
		<input name="card_url" type="hidden" value="https://linkpt.cardservice.co.jp/cgi-bin/secure.cgi" />
		<input name="card_secureurl" type="hidden" value="https://linkpt.cardservice.co.jp/cgi-bin/secure/api.cgi" />
		<input name="ipaddrs[]" type="hidden" value="210.164.6.67" />
		<input name="ipaddrs[]" type="hidden" value="202.221.139.50" />
		<input name="pay_cvs[D001]" type="hidden" value="セブンイレブン" />
		<input name="pay_cvs[D002]" type="hidden" value="ローソン" />
		<input name="pay_cvs[D030]" type="hidden" value="ファミリーマート" />
		<input name="pay_cvs[D040]" type="hidden" value="サークルKサンクス" />
		<input name="pay_cvs[D015]" type="hidden" value="セイコーマート" />
		<input name="acting" type="hidden" value="zeus" />
		<input name="usces_option_update" type="submit" class="button" value="ゼウスの設定を更新する" />
	</form>
	<div class="settle_exp">
		<p><strong>ゼウス決済サービス</strong></p>
		<a href="http://www.cardservice.co.jp/" target="_blank">ゼウス決済サービスの詳細はこちら 》</a>
		<p>　</p>
		<p>この決済は「埋め込み型」の決済システムです。</p>
		<p>「埋め込み型」とは、決済会社のページへは遷移せず、Welcart のページのみで完結する決済システムです。<br />
		デザインの統一されたスタイリッシュな決済が可能です。但し、カード番号を扱いますので専用SSLが必須となります。</p>
		<p>カード番号はZEUS のシステムに送信されるだけで、Welcart に記録は残しません。</p>
		<p>　</p>
		<p>* 3Dセキュアとは </p>
		<p>従来の「クレジットカード番号」と「有効期限」に加え、「自分しか知らないパスワード」を合わせて認証する仕組みで、<br />
		クレジットカード情報の盗用による「なりすまし」などの不正使用を未然に防止することができます。</p>
		<!--<p><strong>テスト稼動について</strong></p>
		<p>入金お任せ及びコンビニ決済のテスト稼動を行なう際は、”テストカード番号”の項目にゼウスから発行されるテストカード番号を入力して下さい。<br />
		これを入力することでWelcart は、名前の後ろにテストカード番号を自動的に付けるなどテストモードで動作します。通常の購入方法でテストができます。<br />
		また、この項目を空白にすることで本稼動となります。</p>-->
	</div>
	</div><!--uscestabs_zeus-->

	<div id="uscestabs_remise">
	<div class="settlement_service"><span class="service_title">ルミーズ決済サービス</span></div>

	<?php if( isset($_POST['acting']) && 'remise' == $_POST['acting'] ){ ?>	
		<?php if( 'on' == $opts['remise']['activate'] ){ ?>	
		<div class="message">十分にテストを行ってから運用してください。</div>
		<?php }else if( '' != $mes ){ ?>
		<div class="error_message"><?php echo $mes; ?></div>
		<?php } ?>
	<?php } ?>
	<form action="" method="post" name="remise_form" id="remise_form">
		<table class="settle_table">
<!--			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_plan_remise');"><?php _e('契約プラン', 'usces'); ?></a></th>
				<td>
				<select name="plan" id="plan_remise">
						<option value="0"<?php echo( ( '0' === $opts['remise']['plan']) ? ' selected="selected"' : '' ); ?>>-------------------------</option>
						<option value="1"<?php echo( ( '1' === $opts['remise']['plan']) ? ' selected="selected"' : '' ); ?>>スーパーバリュープラン</option>
						<option value="2"<?php echo( ( '2' === $opts['remise']['plan']) ? ' selected="selected"' : '' ); ?>>ライトプラン</option>
				</select>
				</td>
				<td><div id="ex_plan_remise" class="explanation"><?php _e('ルミーズと契約したサービスプランを選択してください。<br />契約が変更したい場合はルミーズへお問合せください。', 'usces'); ?></div></td>
			</tr>
-->			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_SHOPCO_remise');"><?php _e('加盟店コード', 'usces'); ?></a></th>
				<td><input name="SHOPCO" type="text" id="SHOPCO_remise" value="<?php echo esc_html($opts['remise']['SHOPCO']); ?>" size="20" maxlength="8" /></td>
				<td><div id="ex_SHOPCO_remise" class="explanation"><?php _e('契約時にルミーズから発行される加盟店コード（半角英数）', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_HOSTID_remise');"><?php _e('ホスト番号', 'usces'); ?></a></th>
				<td><input name="HOSTID" type="text" id="HOSTID_remise" value="<?php echo esc_html($opts['remise']['HOSTID']); ?>" size="20" maxlength="8" /></td>
				<td><div id="ex_HOSTID_remise" class="explanation"><?php _e('契約時にルミーズから割り当てられるホスト番号（半角数字）', 'usces'); ?></div></td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th>クレジットカード決済</th>
				<td><input name="card_activate" type="radio" id="card_activate_remise_1" value="on"<?php if( $opts['remise']['card_activate'] == 'on' ) echo ' checked="checked"' ?> /></td><td><label for="card_activate_remise_1">利用する</label></td>
				<td><input name="card_activate" type="radio" id="card_activate_remise_2" value="off"<?php if( $opts['remise']['card_activate'] == 'off' ) echo ' checked="checked"' ?> /></td><td><label for="card_activate_remise_2">利用しない</label></td>
				<td><div></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_card_jb_remise');">ジョブコード</a></th>
<!--			<td><input name="card_jb" type="radio" id="card_jb_remise_1" value="CHECK"<?php if( $opts['remise']['card_jb'] == 'CHECK' ) echo ' checked' ?> /></td><td><label for="card_jb_remise_1">有効性チェック</label></td>
-->				<td><input name="card_jb" type="radio" id="card_jb_remise_2" value="AUTH"<?php if( $opts['remise']['card_jb'] == 'AUTH' ) echo ' checked' ?> /></td><td><label for="card_jb_remise_2">仮売上処理</label></td>
				<td><input name="card_jb" type="radio" id="card_jb_remise_3" value="CAPTURE"<?php if( $opts['remise']['card_jb'] == 'CAPTURE' ) echo ' checked' ?> /></td><td><label for="card_jb_remise_3">売上処理</label></td>
				<td><div id="ex_card_jb_remise" class="explanation"><?php _e('決済の種類を指定します', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_payquick_remise');">ペイクイック機能</a></th>
				<td><input name="payquick" type="radio" id="payquick_remise_1" value="on"<?php if( $opts['remise']['payquick'] == 'on' ) echo ' checked="checked"' ?> /></td><td><label for="payquick_remise_1">利用する</label></td>
				<td><input name="payquick" type="radio" id="payquick_remise_2" value="off"<?php if( $opts['remise']['payquick'] == 'off' ) echo ' checked="checked"' ?> /></td><td><label for="payquick_remise_2">利用しない</label></td>
				<td><div id="ex_payquick_remise" class="explanation"><?php _e('Welcart の会員システムを利用している場合、会員に対して2回目以降の決済の際、クレジットカード番号、有効期限、名義人の入力が不要となります。<br />クレジットカード情報はWelcart では保存せず、「ルミーズ」のデータベースにて安全に保管されます。', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_howpay_remise');">お客様の支払方法</a></th>
				<td><input name="howpay" type="radio" id="howpay_remise_1" value="on"<?php if( $opts['remise']['howpay'] == 'on' ) echo ' checked="checked"' ?> /></td><td><label for="howpay_remise_1">分割払いに対応する</label></td>
				<td><input name="howpay" type="radio" id="howpay_remise_2" value="off"<?php if( $opts['remise']['howpay'] == 'off' ) echo ' checked="checked"' ?> /></td><td><label for="howpay_remise_2">一括払いのみ</label></td>
				<td><div id="ex_howpay_remise" class="explanation"><?php _e('「一括払い」以外をご利用の場合はルミーズ側の設定が必要となります。前もってルミーズにお問合せください。<br >「スーパーバリュープラン」の場合は「一括払いのみ」を選択してください。', 'usces'); ?></div></td>
			</tr>
			<?php if( defined('WCEX_DLSELLER') ): ?>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_continuation_remise');">自動継続課金</a></th>
				<td><input name="continuation" type="radio" id="continuation_remise_1" value="on"<?php if( $opts['remise']['continuation'] == 'on' ) echo ' checked="checked"' ?> /></td><td><label for="continuation_remise_1">利用する</label></td>
				<td><input name="continuation" type="radio" id="continuation_remise_2" value="off"<?php if( $opts['remise']['continuation'] == 'off' ) echo ' checked="checked"' ?> /></td><td><label for="continuation_remise_2">利用しない</label></td>
				<td><div id="ex_continuation_remise" class="explanation"><?php _e('定期的に発生する月会費などの煩わしい課金処理を完全に自動化することができる機能です。<br />詳しくは「ルミーズ」にお問合せください。', 'usces'); ?></div></td>
			</tr>
			<?php endif; ?>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_card_pc_ope_remise');">稼働環境(PC)</a></th>
				<td><input name="card_pc_ope" type="radio" id="card_pc_ope_remise_1" value="test"<?php if( $opts['remise']['card_pc_ope'] == 'test' ) echo ' checked="checked"' ?> /></td><td><label for="card_pc_ope_remise_1">テスト環境</label></td>
				<td><input name="card_pc_ope" type="radio" id="card_pc_ope_remise_2" value="public"<?php if( $opts['remise']['card_pc_ope'] == 'public' ) echo ' checked="checked"' ?> /></td><td><label for="card_pc_ope_remise_2">本番環境</label></td>
				<td><div id="ex_card_pc_ope_remise" class="explanation"><?php _e('動作環境を切り替えます', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_send_url_pc_remise');"><?php _e('本番URL(PC)', 'usces'); ?></a></th>
				<td colspan="4"><input name="send_url_pc" type="text" id="send_url_pc_remise" value="<?php echo esc_html($opts['remise']['send_url_pc']); ?>" size="40" /></td>
				<td><div id="ex_send_url_pc_remise" class="explanation"><?php _e('クレジットカード決済の本番環境で接続するURLを設定します。', 'usces'); ?></div></td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_conv_remise');">コンビニ・電子マネー決済</a></th>
				<td><input name="conv_activate" type="radio" id="conv_activate_remise_1" value="on"<?php if( $opts['remise']['conv_activate'] == 'on' ) echo ' checked="checked"' ?> /></td><td><label for="conv_activate_remise_1">利用する</label></td>
				<td><input name="conv_activate" type="radio" id="conv_activate_remise_2" value="off"<?php if( $opts['remise']['conv_activate'] == 'off' ) echo ' checked="checked"' ?> /></td><td><label for="conv_activate_remise_2">利用しない</label></td>
				<td><div id="ex_conv_remise" class="explanation"><?php _e('コンビニ・電子マネー決済を利用するかどうか', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_paydate_remise');"><?php _e('支払期限', 'usces'); ?></a></th>
				<td colspan="4"><input name="S_PAYDATE" type="text" id="S_PAYDATE_remise" value="<?php echo esc_html($opts['remise']['S_PAYDATE']); ?>" size="5" maxlength="3" />日</td>
				<td><div id="ex_paydate_remise" class="explanation"><?php _e('日数を設定します。（半角数字）', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_conv_pc_ope_remise');">稼働環境(PC)</a></th>
				<td><input name="conv_pc_ope" type="radio" id="conv_pc_ope_remise_1" value="test"<?php if( $opts['remise']['conv_pc_ope'] == 'test' ) echo ' checked="checked"' ?> /></td><td><label for="conv_pc_ope_remise_1">テスト環境</label></td>
				<td><input name="conv_pc_ope" type="radio" id="conv_pc_ope_remise_2" value="public"<?php if( $opts['remise']['conv_pc_ope'] == 'public' ) echo ' checked="checked"' ?> /></td><td><label for="conv_pc_ope_remise_2">本番環境</label></td>
				<td><div id="ex_conv_pc_ope_remise" class="explanation"><?php _e('動作環境を切り替えます', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_send_url_cvs_pc_remise');"><?php _e('本番URL(PC)', 'usces'); ?></a></th>
				<td colspan="4"><input name="send_url_cvs_pc" type="text" id="send_url_cvs_pc_remise" value="<?php echo esc_html($opts['remise']['send_url_cvs_pc']); ?>" size="40" /></td>
				<td><div id="ex_send_url_cvs_pc_remise" class="explanation"><?php _e('コンビニ・電子マネー決済の本番環境で接続するURLを設定します。', 'usces'); ?></div></td>
			</tr>
		</table>
		<input name="send_url_cvs_mbl" type="hidden" value="https://test.remise.jp/rpgw2/mbl/cvs/paycvs.aspx" />
		<input name="send_url_mbl" type="hidden" value="https://test.remise.jp/rpgw2/mbl/card/paycard.aspx" />
		<input name="send_url_cvs_mbl_test" type="hidden" value="https://test.remise.jp/rpgw2/mbl/cvs/paycvs.aspx" />
		<input name="send_url_cvs_pc_test" type="hidden" value="https://test.remise.jp/rpgw2/pc/cvs/paycvs.aspx" />
		<input name="send_url_mbl_test" type="hidden" value="https://test.remise.jp/rpgw2/mbl/card/paycard.aspx" />
		<input name="send_url_pc_test" type="hidden" value="https://test.remise.jp/rpgw2/pc/card/paycard.aspx" />
		<input name="REMARKS3" type="hidden" value="A0000875" />
		<input name="acting" type="hidden" value="remise" />
		<input name="usces_option_update" type="submit" class="button" value="ルミーズの設定を更新する" />
	</form>
	<div class="settle_exp">
		<p><strong>ルミーズ決済サービス</strong></p>
		<a href="http://www.remise.jp/" target="_blank">ルミーズ決済サービスの詳細はこちら 》</a>
		<p>　</p>
		<p>この決済は「外部リンク型」の決済システムです。</p>
		<p>「外部リンク型」とは、決済会社のページへは遷移してカード情報を入力する決済システムです。</p>
		<p>「自動継続課金」を利用するには「DL Seller」拡張プラグインのインストールが必要です。</p>
	</div>
	</div><!--uscestabs_remise-->

<!--20101018ysk start-->
	<div id="uscestabs_jpayment">
	<div class="settlement_service"><span class="service_title">J-Payment決済サービス</span></div>

	<?php if( isset($_POST['acting']) && 'jpayment' == $_POST['acting'] ){ ?>
		<?php if( 'on' == $opts['jpayment']['activate'] ){ ?>
		<div class="message">十分にテストを行ってから運用してください。</div>
		<?php }else if( '' != $mes ){ ?>
		<div class="error_message"><?php echo $mes; ?></div>
		<?php } ?>
	<?php } ?>
	<form action="" method="post" name="jpayment_form" id="jpayment_form">
		<table class="settle_table">
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_aid_jpayment');"><?php _e('店舗ID', 'usces'); ?></a></th>
				<td><input name="aid" type="text" id="aid_jpayment" value="<?php echo esc_html($opts['jpayment']['aid']); ?>" size="20" maxlength="6" /></td>
				<td><div id="ex_aid_jpayment" class="explanation"><?php _e('契約時にJ-Paymentから発行される店舗ID（半角数字）', 'usces'); ?></div></td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_card_jpayment');">クレジットカード決済</a></th>
				<td><input name="card_activate" type="radio" id="card_activate_jpayment_1" value="on"<?php if( $opts['jpayment']['card_activate'] == 'on' ) echo ' checked' ?> /></td><td><label for="card_activate_jpayment_1">利用する</label></td>
				<td><input name="card_activate" type="radio" id="card_activate_jpayment_2" value="off"<?php if( $opts['jpayment']['card_activate'] == 'off' ) echo ' checked' ?> /></td><td><label for="card_activate_jpayment_2">利用しない</label></td>
				<td><div id="ex_card_jpayment" class="explanation"><?php _e('クレジットカード決済を利用するかどうか<br />※自動継続課金には対応していません。', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_card_jb_jpayment');">ジョブタイプ</a></th>
<!--			<td><input name="card_jb" type="radio" id="card_jb_jpayment_1" value="CHECK"<?php if( $opts['jpayment']['card_jb'] == 'CHECK' ) echo ' checked' ?> /></td><td><label for="card_jb_jpayment_1">有効性チェック</label></td>
-->				<td><input name="card_jb" type="radio" id="card_jb_jpayment_2" value="AUTH"<?php if( $opts['jpayment']['card_jb'] == 'AUTH' ) echo ' checked' ?> /></td><td><label for="card_jb_jpayment_2">仮売上処理</label></td>
				<td><input name="card_jb" type="radio" id="card_jb_jpayment_3" value="CAPTURE"<?php if( $opts['jpayment']['card_jb'] == 'CAPTURE' ) echo ' checked' ?> /></td><td><label for="card_jb_jpayment_3">仮実同時売上処理</label></td>
				<td><div id="ex_card_jb_jpayment" class="explanation"><?php _e('決済の種類を指定します', 'usces'); ?></div></td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_conv_jpayment');">コンビニ決済</a></th>
				<td><input name="conv_activate" type="radio" id="conv_activate_jpayment_1" value="on"<?php if( $opts['jpayment']['conv_activate'] == 'on' ) echo ' checked' ?> /></td><td><label for="conv_activate_jpayment_1">利用する</label></td>
				<td><input name="conv_activate" type="radio" id="conv_activate_jpayment_2" value="off"<?php if( $opts['jpayment']['conv_activate'] == 'off' ) echo ' checked' ?> /></td><td><label for="conv_activate_jpayment_2">利用しない</label></td>
				<td><div id="ex_conv_jpayment" class="explanation"><?php _e('コンビニ（ペーパーレス）決済を利用するかどうか', 'usces'); ?></div></td>
			</tr>
		</table>
<!--
		<table class="settle_table">
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_webm_jpayment');">WebMoney決済</a></th>
				<td><input name="webm_activate" type="radio" id="webm_activate_jpayment_1" value="on"<?php if( $opts['jpayment']['webm_activate'] == 'on' ) echo ' checked' ?> /></td><td><label for="webm_activate_jpayment_1">利用する</label></td>
				<td><input name="webm_activate" type="radio" id="webm_activate_jpayment_2" value="off"<?php if( $opts['jpayment']['webm_activate'] == 'off' ) echo ' checked' ?> /></td><td><label for="webm_activate_jpayment_2">利用しない</label></td>
				<td><div></div></td><td><div></div></td>
				<td><div id="ex_webm_jpayment" class="explanation"><?php _e('電子マネー（WebMoney）決済を利用するかどうか', 'usces'); ?></div></td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_bitc_jpayment');">BitCash決済</a></th>
				<td><input name="bitc_activate" type="radio" id="bitc_activate_jpayment_1" value="on"<?php if( $opts['jpayment']['bitc_activate'] == 'on' ) echo ' checked' ?> /></td><td><label for="bitc_activate_jpayment_1">利用する</label></td>
				<td><input name="bitc_activate" type="radio" id="bitc_activate_jpayment_2" value="off"<?php if( $opts['jpayment']['bitc_activate'] == 'off' ) echo ' checked' ?> /></td><td><label for="bitc_activate_jpayment_2">利用しない</label></td>
				<td><div id="ex_bitc_jpayment" class="explanation"><?php _e('電子マネー（BitCash）決済を利用するかどうか', 'usces'); ?></div></td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_suica_jpayment');">モバイルSuica決済</a></th>
				<td><input name="suica_activate" type="radio" id="suica_activate_jpayment_1" value="on"<?php if( $opts['jpayment']['suica_activate'] == 'on' ) echo ' checked' ?> /></td><td><label for="suica_activate_jpayment_1">利用する</label></td>
				<td><input name="suica_activate" type="radio" id="suica_activate_jpayment_2" value="off"<?php if( $opts['jpayment']['suica_activate'] == 'off' ) echo ' checked' ?> /></td><td><label for="suica_activate_jpayment_2">利用しない</label></td>
				<td><div id="ex_suica_jpayment" class="explanation"><?php _e('電子マネー（モバイルSuica）決済を利用するかどうか', 'usces'); ?></div></td>
			</tr>
		</table>
-->
		<table class="settle_table">
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_bank_jpayment');">バンクチェック決済</a></th>
				<td><input name="bank_activate" type="radio" id="bank_activate_jpayment_1" value="on"<?php if( $opts['jpayment']['bank_activate'] == 'on' ) echo ' checked' ?> /></td><td><label for="bank_activate_jpayment_1">利用する</label></td>
				<td><input name="bank_activate" type="radio" id="bank_activate_jpayment_2" value="off"<?php if( $opts['jpayment']['bank_activate'] == 'off' ) echo ' checked' ?> /></td><td><label for="bank_activate_jpayment_2">利用しない</label></td>
				<td><div id="ex_bank_jpayment" class="explanation"><?php _e('バンクチェック決済を利用するかどうか', 'usces'); ?></div></td>
			</tr>
		</table>
		<input name="send_url" type="hidden" value="https://credit.j-payment.co.jp/gateway/payform.aspx" />
		<input name="acting" type="hidden" value="jpayment" />
		<input name="usces_option_update" type="submit" class="button" value="J-Paymentの設定を更新する" />
	</form>
	<div class="settle_exp">
		<p><strong>J-Payment決済サービス</strong></p>
		<a href="http://www.j-payment.co.jp/" target="_blank">J-Payment決済サービスの詳細はこちら 》</a>
		<p>　</p>
		<p>この決済は「外部リンク型」の決済システムです。</p>
		<p>「外部リンク型」とは、決済会社のページへ遷移してカード情報を入力する決済システムです。</p>
	</div>
	</div><!--uscestabs_jpayment-->
<!--20101018ysk end-->
<!--20110208ysk start-->
	<div id="uscestabs_paypal">
	<div class="settlement_service"><span class="service_title"><?php _e('PayPalエクスプレスチェックアウト決済サービス', 'usces'); ?></span></div>

	<?php if( isset($_POST['acting']) && 'paypal' == $_POST['acting'] ){ ?>
		<?php if( 'on' == $opts['paypal']['activate'] ){ ?>
		<div class="message"><?php _e('十分にテストを行ってから運用してください。', 'usces'); ?></div>
		<?php }else if( '' != $mes ){ ?>
		<div class="error_message"><?php echo $mes; ?></div>
		<?php } ?>
	<?php } ?>
	<form action="" method="post" name="paypal_form" id="paypal_form">
		<table class="settle_table">
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_ec_activate_paypal');"><?php _e('PayPal決済', 'usces'); ?></a></th>
				<td><input name="ec_activate" type="radio" id="ec_activate_paypal_1" value="on"<?php if( $opts['paypal']['ec_activate'] == 'on' ) echo ' checked' ?> /></td><td><label for="ec_activate_paypal_1"><?php _e('利用する', 'usces'); ?></label></td>
				<td><input name="ec_activate" type="radio" id="ec_activate_paypal_2" value="off"<?php if( $opts['paypal']['ec_activate'] == 'off' ) echo ' checked' ?> /></td><td><label for="ec_activate_paypal_2"><?php _e('利用しない', 'usces'); ?></label></td>
				<td><div id="ex_ec_activate_paypal" class="explanation"><?php _e('PayPal決済を利用するかどうかを選択します。', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_sandbox_paypal');"><?php _e('動作環境', 'usces'); ?></a></th>
				<td><input name="sandbox" type="radio" id="sandbox_paypal_1" value="1"<?php if( $opts['paypal']['sandbox'] == '1' ) echo ' checked' ?> /></td><td><label for="sandbox_paypal_1"><?php _e('テスト(Sandbox)', 'usces'); ?></label></td>
				<td><input name="sandbox" type="radio" id="sandbox_paypal_2" value="2"<?php if( $opts['paypal']['sandbox'] == '2' ) echo ' checked' ?> /></td><td><label for="sandbox_paypal_2"><?php _e('本稼動', 'usces'); ?></label></td>
				<td><div id="ex_sandbox_paypal" class="explanation"><?php _e('Sandbox を利用した決済テストの場合は「テスト(Sandbox)」を選択します。', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_user_paypal');"><?php _e('APIユーザー名', 'usces'); ?></a></th>
				<td colspan="4"><input name="user" type="text" id="user_paypal" value="<?php echo esc_html($opts['paypal']['user']); ?>" size="50" /></td>
				<td><div id="ex_user_paypal" class="explanation"><?php _e('API信用証明書のAPIユーザー名を入力します。Sandbox と本稼動では別のユーザー名となります。', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_pwd_paypal');"><?php _e('APIパスワード', 'usces'); ?></a></th>
				<td colspan="4"><input name="pwd" type="text" id="pwd_paypal" value="<?php echo esc_html($opts['paypal']['pwd']); ?>" size="50" /></td>
				<td><div id="ex_pwd_paypal" class="explanation"><?php _e('API信用証明書のAPIパスワードを入力します。Sandbox と本稼動では別のパスワードとなります。', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_signature_paypal');"><?php _e('署名', 'usces'); ?></a></th>
				<td colspan="4"><input name="signature" type="text" id="signature_paypal" value="<?php echo esc_html($opts['paypal']['signature']); ?>" size="50" /></td>
				<td><div id="ex_signature_paypal" class="explanation"><?php _e('PayPalから発行されるAPI信用証明書の署名を入力します。Sandbox と本稼動では別の署名となります。', 'usces'); ?></div></td>
			</tr>
<!--20110412ysk start-->
			<?php if( defined('WCEX_DLSELLER') ): ?>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_continuation_paypal');"><?php _e('定期支払い', 'usces'); ?></a></th>
				<td><input name="continuation" type="radio" id="continuation_paypal_1" value="on"<?php if( $opts['paypal']['continuation'] == 'on' ) echo ' checked' ?> /></td><td><label for="continuation_paypal_1"><?php _e('利用する', 'usces'); ?></label></td>
				<td><input name="continuation" type="radio" id="continuation_paypal_2" value="off"<?php if( $opts['paypal']['continuation'] == 'off' ) echo ' checked' ?> /></td><td><label for="continuation_paypal_2"><?php _e('利用しない', 'usces'); ?></label></td>
				<td><div id="ex_continuation_paypal" class="explanation"><?php _e('定期的に発生する月会費などの煩わしい課金処理を完全に自動化することができる機能です。<br />詳しくは「PayPal」にお問合せください。', 'usces'); ?></div></td>
			</tr>
			<?php endif; ?>
<!--20110412ysk end-->
		</table>
		<input name="acting" type="hidden" value="paypal" />
		<input name="usces_option_update" type="submit" class="button" value="<?php _e('PayPalの設定を更新する', 'usces'); ?>" />
	</form>
	<div class="settle_exp">
		<p><strong><?php _e('PayPalエクスプレスチェックアウト決済サービス', 'usces'); ?></strong></p>
		<a href="https://www.paypal.com/" target="_blank"><?php _e('PayPal決済サービスの詳細はこちら 》', 'usces'); ?></a>
		<p>　</p>
		<p><?php _e('この決済は、「エクスプレスチェックアウト」を使用しています。', 'usces'); ?></p>
		<p><?php _e('ご利用のサーバーに「OpenSSL」モジュールがインストールされていない場合、「エクスプレスチェックアウト」での決済はできません。', 'usces'); ?></p>
	</div>
	</div><!--uscestabs_paypal-->
<!--20110208ysk end-->

</div><!--uscestabs-->
</div><!--inside-->
</div><!--postbox-->
</div><!--poststuff-->

</div><!--usces_admin-->
</div><!--wrap-->