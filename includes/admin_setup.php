<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';
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
<h2>Welcart Shop 基本設定<?php //echo __('USC e-Shop Options','usces'); ?></h2>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>
<form action="" method="post" name="option_form" id="option_form">
<input name="usces_option_update" type="submit" class="button" value="設定を更新" />
<div id="poststuff" class="metabox-holder">

<div class="postbox">
<h3 class="hndle"><span>営業設定</span><a style="cursor:pointer;" onclick="toggleVisibility('business_setting');">（説明）</a></h3>
<div class="inside">
<table class="form_table">
<?php 
	if($this->display_mode) :
?>
	<tr height="50">
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_display_mode');">表示モード</a></th>
<?php 
		foreach( (array)$this->display_mode as $key => $label ) { 
			if($key == 'Promotionsale')
				continue;
			$checked = $this->options['display_mode'] == $key ? ' checked="checked"' : '';
?>
		<td width="10"><input name="display_mode" type="radio" id="<?php echo $key; ?>" value="<?php echo $key; ?>"<?php echo $checked; ?> /></td>
		<td width="100"><label for="<?php echo $key; ?>"><?php echo $label; ?></label></td>
<?php
		}
?>
	    <td><div id="ex_display_mode" class="explanation">
<strong>・通常営業</strong> --- 通常の表示<br />
<strong>・キャンペーン</strong> --- キャンペーンモードの表示<br />
<strong>・メンテナンス</strong> --- メンテナンスページを表示。ログインした管理者は通常表示でページを確認できます。
</div>
</td>
	</tr>
<?php endif; ?>
</table>
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_cat');">キャンペーン対象</a></th>
	    <td>
<?php 
	//$dropdown_options = array('show_option_all' => __('View all categories'), 'hide_empty' => 0, 'hierarchical' => 1, 'show_count' => 0, 'orderby' => 'name', 'child_of' => USCES_ITEM_CAT_PARENT_ID, 'selected' => $this->options['campaign_category']);
	$dropdown_options = array('show_option_all' => '全商品', 'hide_empty' => 0, 'hierarchical' => 1, 'show_count' => 1, 'orderby' => 'name', 'child_of' => USCES_ITEM_CAT_PARENT_ID, 'selected' => $this->options['campaign_category']);
	wp_dropdown_categories($dropdown_options);
?>
		</td>
		<td><div id="ex_cat" class="explanation">キャンペーンモードの際、特典を付ける対象のカテゴリ。商品に対して「キャンペーン」カテゴリを設置しておくことによって自由にキャンペーン対象商品を選択できる。</div></td>
	</tr>
</table>
</table>
<table class="form_table">
	<tr>
	    <th rowspan="2"><a style="cursor:pointer;" onclick="toggleVisibility('ex_cat_privilege');">キャンペーン特典</a></th>
	    <td><input name="cat_privilege" type="radio" id="privilege_point" value="point"<?php if($this->options['campaign_privilege'] == 'point') echo 'checked="checked"'; ?> /></td><td><label for="privilege_point">ポイント</label></td><td><input name="point_num" type="text" class="short_str" value="<?php echo $this->options['privilege_point']; ?>" />倍</td>
		<td rowspan="2"><div id="ex_cat_privilege" class="explanation">「ポイント」は会員のみの特典でポイント率の倍率を指定。<br />「割引」は全購入者が対象で値引率を指定。</div></td>
	</tr>
	<tr>
	    <td><input name="cat_privilege" type="radio" id="privilege_discount" value="discount"<?php if($this->options['campaign_privilege'] == 'discount') echo 'checked="checked"'; ?> /></td><td><label for="privilege_discount">割引</label></td><td><input name="discount_num" type="text" class="short_str" value="<?php echo $this->options['privilege_discount']; ?>" />%</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="business_setting" class="explanation">ショップの表示モードや運営方法などの設定</div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span>ショップ設定</span><a style="cursor:pointer;" onclick="toggleVisibility('shop_setting');">（説明）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_company_name');">会社名</a></th>
	    <td><input name="company_name" type="text" class="long_str" value="<?php echo $this->options['company_name']; ?>" /></td>
		<td><div id="ex_company_name" class="explanation">法人の場合は記入してください。</div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_zip_code');">郵便番号</a></th>
	    <td><input name="zip_code" type="text" class="short_str" value="<?php echo $this->options['zip_code']; ?>" /></td>
		<td><div id="ex_zip_code" class="explanation">例）100-1001</div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_address1');">住所1</a></th>
	    <td><input name="address1" type="text" class="long_str" value="<?php echo $this->options['address1']; ?>" /></td>
		<td><div id="ex_address1" class="explanation">例）東京都渋谷区通販町1035</div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_address2');">住所2</a></th>
	    <td><input name="address2" type="text" class="long_str" value="<?php echo $this->options['address2']; ?>" /></td>
		<td><div id="ex_address2" class="explanation">例）通販ビル4F</div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_tel_number');">電話番号</a></th>
	    <td><input name="tel_number" type="text" class="long_str" value="<?php echo $this->options['tel_number']; ?>" /></td>
		<td><div id="ex_tel_number" class="explanation">例）100-100-10000</div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_fax_number');">FAX番号</a></th>
	    <td><input name="fax_number" type="text" class="long_str" value="<?php echo $this->options['fax_number']; ?>" /></td>
		<td><div id="ex_fax_number" class="explanation">例）100-100-10000</div></td>
	</tr>
	<tr>
	    <th><em>＊ </em><a style="cursor:pointer;" onclick="toggleVisibility('ex_order_mail');">受注用メールアドレス</a></th>
	    <td><input name="order_mail" type="text" class="long_str" value="<?php echo $this->options['order_mail']; ?>" /></td>
		<td><div id="ex_order_mail" class="explanation"><em>【必須】</em>注文内容を受け取るための管理者のメールアドレス。</div></td>
	</tr>
	<tr>
	    <th><em>＊ </em><a style="cursor:pointer;" onclick="toggleVisibility('ex_inquiry_mail');">問合せメールアドレス</a></th>
	    <td><input name="inquiry_mail" type="text" class="long_str" value="<?php echo $this->options['inquiry_mail']; ?>" /></td>
		<td><div id="ex_inquiry_mail" class="explanation"><em>【必須】</em>問い合わせ内容を受け取るための管理者のメールアドレス。</div></td>
	</tr>
	<tr>
	    <th><em>＊ </em><a style="cursor:pointer;" onclick="toggleVisibility('ex_sender_mail');">送信元メールアドレス</a></th>
	    <td><input name="sender_mail" type="text" class="long_str" value="<?php echo $this->options['sender_mail']; ?>" /></td>
		<td><div id="ex_sender_mail" class="explanation"><em>【必須】</em>購入者にサンキューメールを送る際の送信者アドレス。</div></td>
	</tr>
	<tr>
	    <th><em>＊ </em><a style="cursor:pointer;" onclick="toggleVisibility('ex_error_mail');">エラーメールアドレス</a></th>
	    <td><input name="error_mail" type="text" class="long_str" value="<?php echo $this->options['error_mail']; ?>" /></td>
		<td><div id="ex_error_mail" class="explanation"><em>【必須】</em>メールが不達の場合のエラーメールの送信先。</div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_copyright');">コピーライト</a></th>
	    <td><input name="copyright" type="text" class="long_str" value="<?php echo $this->options['copyright']; ?>" /></td>
		<td><div id="ex_copyright" class="explanation">例）Copyright(c) 2009 Welcart.inc All Rights Reserved.</div></td>
	</tr>
</table>
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_postage_privilege');">送料無料条件</a></th>
	    <td><input name="postage_privilege" type="text" class="short_str" value="<?php echo $this->options['postage_privilege']; ?>" />以上</td>
		<td><div id="ex_postage_privilege" class="explanation">送料無料となる合計購入金額。必要ない場合は空白</div></td>
	</tr>
</table>
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_purchase_limit');">購入制限数初期値</a></th>
	    <td><input name="purchase_limit" type="text" class="short_str" value="<?php echo $this->options['purchase_limit']; ?>" />個まで</td>
		<td><div id="ex_purchase_limit" class="explanation">商品登録時の初期値。必要ない場合は空白</div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_rule');">発送日の初期値</a></th>
	    <td><select name="shipping_rule" class="short_select">
<?php foreach( (array)$this->shipping_rule as $key => $label){ $selected = $key == $this->options['shipping_rule'] ? ' selected="selected"' : ''; ?>
	<option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
<?php } ?>
</select></td>
		<td><div id="ex_shipping_rule" class="explanation">商品登録時の初期値。必要ない場合は選択しない</div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_tax_rate');">消費税率</a></th>
	    <td><input name="tax_rate" type="text" class="short_str" value="<?php echo $this->options['tax_rate']; ?>" />%</td>
		<td><div id="ex_tax_rate" class="explanation">内税の場合は空白</div></td>
	</tr>
</table>
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_tax_method');">税計算方法</a></th>
	    <td width="10"><input name="tax_method" id="tax_method_cutting" type="radio" value="cutting"<?php if($this->options['tax_method'] == 'cutting') echo 'checked="checked"'; ?> /></td><td width="60"><label for="tax_method_cutting">切捨て</label></td>
	    <td width="10"><input name="tax_method" id="tax_method_bring" type="radio" value="bring"<?php if($this->options['tax_method'] == 'bring') echo 'checked="checked"'; ?> /></td><td width="60"><label for="tax_method_bring">切上げ</label></td>
	    <td width="10"><input name="tax_method" id="tax_method_rounding" type="radio" value="rounding"<?php if($this->options['tax_method'] == 'rounding') echo 'checked="checked"'; ?> /></td><td width="60"><label for="tax_method_rounding">四捨五入</label></td>
		<td><div id="ex_tax_method" class="explanation">内税の場合は空白</div></td>
	</tr>
</table>
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_cod_fee');">代引き手数料</a></th>
	    <td><input name="cod_fee" type="text" class="short_str" value="<?php echo $this->options['cod_fee']; ?>" />円</td>
		<td><div id="ex_cod_fee" class="explanation">代引き払い時の手数料。必要のない場合は空白</div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_transferee');">振込先口座情報</a></th>
	    <td><textarea name="transferee" class="long_txt"><?php echo $this->options['transferee']; ?></textarea></td>
		<td><div id="ex_transferee" class="explanation">振込み払い時の振込先。改行して自由に入力できます。この内容がメールに記載されます。</div></td>
	</tr>
</table>
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_membersystem_state');">会員システム</a></th>
	    <td width="10"><input name="membersystem_state" id="membersystem_state_activate" type="radio" value="activate"<?php if($this->options['membersystem_state'] == 'activate') echo 'checked="checked"'; ?> /></td><td width="60"><label for="membersystem_state_activate">利用する</label></td>
	    <td width="10"><input name="membersystem_state" id="membersystem_state_deactivate" type="radio" value="deactivate"<?php if($this->options['membersystem_state'] == 'deactivate') echo 'checked="checked"'; ?> /></td><td width="60"><label for="membersystem_state_deactivate">利用しない</label></td>
		<td><div id="ex_membersystem_state" class="explanation">会員（メンバー）システムを利用するか否か。</div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_membersystem_point');">会員ポイント</a></th>
	    <td width="10"><input name="membersystem_point" id="membersystem_point_activate" type="radio" value="activate"<?php if($this->options['membersystem_point'] == 'activate') echo 'checked="checked"'; ?> /></td><td width="60"><label for="membersystem_point_activate">付与する</label></td>
	    <td width="10"><input name="membersystem_point" id="membersystem_point_deactivate" type="radio" value="deactivate"<?php if($this->options['membersystem_point'] == 'deactivate') echo 'checked="checked"'; ?> /></td><td width="60"><label for="membersystem_point_deactivate">付与しない</label></td>
		<td><div id="ex_membersystem_point" class="explanation">会員システムを利用した場合の、ポイント付与機能を利用するか否か。</div></td>
	</tr>
</table>
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_point_rate');">ポイント率初期値</a></th>
	    <td><input name="point_rate" type="text" class="short_str" value="<?php echo $this->options['point_rate']; ?>" />%</td>
		<td><div id="ex_point_rate" class="explanation">商品登録時の初期値。必要ない場合は空白</div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_start_point');">会員登録時ポイント</a></th>
	    <td><input name="start_point" type="text" class="short_str" value="<?php echo $this->options['start_point']; ?>" />pt</td>
		<td><div id="ex_start_point" class="explanation">初回会員登録時に付与されるポイント</div></td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="shop_setting" class="explanation">ショップの初期設定</div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span>支払方法</span></h3>
<div class="inside">
	<div id="postpayment"><div id="payment-response"></div>
<?php
//	$option = get_option('usces');
//	$option['payment_method'] = array();
//	update_option('usces', $option);
//	$this->options = get_option('usces');
	$metadata = $this->options['payment_method'];
	payment_list($metadata);
	payment_form();
?>
<hr size="1" color="#CCCCCC" />
<div id="Commonoption" class="explanation"><em>【必須】</em>取り扱い可能な支払方法</div>
</div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span>共通オプション</span></h3>
<div class="inside">
	<div id="postoptcustomstuff"><div id="ajax-response"></div>
<?php
	$metadata = has_item_option_meta(USCES_CART_NUMBER);
	list_item_option_meta($metadata);
	common_option_meta_form();
?>
<hr size="1" color="#CCCCCC" />
<div id="Commonoption" class="explanation">購入の際に選択される条件。ここで登録されたオプションが商品マスターのオプションとして利用できます。</div>
</div>
</div>
</div><!--postbox-->

</div><!--poststuff-->



<input name="usces_option_update" type="submit" class="button" value="設定を更新" />
<input type="hidden" id="post_ID" name="post_ID" value="<?php echo USCES_CART_NUMBER ?>" />
</form>
</div><!--usces_admin-->
</div><!--wrap-->