<?php 
$usces_members = $this->get_member();
$usces_member_history = $this->get_member_history($usces_members['ID']);

$html = '<div id="memberpages">

<div class="whitebox">
<div id="memberinfo">
<table>
<tr>
<th scope="row">会員番号</th>
<td class="num">' . $usces_members['ID'] . '</td>
<td rowspan="3">&nbsp;</td>
<th>入会日</th>
<td>' . mysql2date(__('Y/m/d'), $usces_members['registered']) . '</td>
</tr>
<tr>
<th scope="row">氏名</th>
<td>' . $usces_members['name1'] . '&nbsp;' . $usces_members['name2'] . '&nbsp;様</td>';

if(usces_is_membersystem_point()){
	$html .= '<th>現在のポイント</th>
		<td class="num">' . $usces_members['point'] . '</td>';
}else{
	$html .= '<th>&nbsp;</th>
	<td class="num">&nbsp;</td>';
}
$html .= '</tr>
	<tr>
	<th scope="row">メールアドレス</th>
	<td>' . $usces_members['mailaddress1'] . '</td>
	<th>&nbsp;</th>
	<td>&nbsp;</td>
	</tr>
	</table>
	　<br />　
	<a href="#edit">会員情報編集へ≫</a>
	<h3>購入履歴</h3>
	<table>';
if ( !count($usces_member_history) ) {
	$html .= '<tr>
	<td>現在購入履歴はございません。</td>
	</tr>';
}
foreach ( $usces_member_history as $umhs ) {
	$cart = $umhs['cart'];
	$html .= '<tr>
		<th class="historyrow">購入日</th>
		<th class="historyrow">購入金額</th>
		<th class="historyrow">使用ポイント</th>
		<th class="historyrow">特別割引</th>
		<th class="historyrow">送料</th>
		<th class="historyrow">代引き手数料</th>
		<th class="historyrow">消費税</th>
		<th class="historyrow">獲得ポイント</th>
		</tr>
		<tr>
		<td class="date">' . $umhs['date'] . '</td>
		<td class="rightnum">' . number_format($this->get_total_price($cart)-$umhs['usedpoint']+$umhs['discount']+$umhs['shipping_charge']+$umhs['cod_fee']+$umhs['tax']) . '</td>
		<td class="rightnum">' . number_format($umhs['usedpoint']) . '</td>
		<td class="rightnum">' . number_format($umhs['discount']) . '</td>
		<td class="rightnum">' . number_format($umhs['shipping_charge']) . '</td>
		<td class="rightnum">' . number_format($umhs['cod_fee']) . '</td>
		<td class="rightnum">' . number_format($umhs['tax']) . '</td>
		<td class="rightnum">' . number_format($umhs['getpoint']) . '</td>
		</tr>
		<tr>
		<td class="retail" colspan="8">
			<table id="retail_table">
			<tr>
			<th scope="row" class="num">No.</th>
			<th class="thumbnail">&nbsp;</th>
			<th>商品</th>
			<th class="price ">単価</th>
			<th class="quantity">数量</th>
			<th class="subtotal">金額</th>
			</tr>';
			
	for($i=0; $i<count($cart); $i++) { 
		$cart_row = $cart[$i];
		$post_id = $cart_row['post_id'];
		$sku = $cart_row['sku'];
		$quantity = $cart_row['quantity'];
		$options = $cart_row['options'];
		$itemCode = $this->getItemCode($post_id);
		$itemName = $this->getItemName($post_id);
		$skuPrice = $this->getItemPrice($post_id, $sku);
		$pictids = $this->get_pictids($itemCode);
		if (!empty($options)) {
			$optstr = implode(',', $options);
		} else { 
			$optstr =  '';
			$options =  array();
		}
			
		$html .= '<tr>
			<td>' . ($i + 1) . '</td>
			<td><a href="' . get_permalink($post_id) . '">' . wp_get_attachment_image( $pictids[0], array(60, 60), true ) . '</a></td>
			<td class="aleft"><a href="' . get_permalink($post_id) . '">' . $itemName . '&nbsp;' . $itemCode . '&nbsp;' . $sku . '<br />' . $optstr . '</a></td>
			<td class="rightnum">' . number_format($skuPrice) . '</td>
			<td class="rightnum">' . number_format($cart_row['quantity']) . '</td>
			<td class="rightnum">' . number_format($skuPrice * $cart_row['quantity']) . '</td>
			</tr>';
	}
	$html .= '</table>
		</td>
		</tr>';
}

$html .= '</table>

	<h3><a name="edit">会員情報編集</a></h3>
	<div class="error_message">' . $this->error_message . '</div>
	<form action="' . USCES_MEMBER_URL . '" method="post">
	<table class="customer_form">
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
	<tr>
	<th scope="row">パスワード</th>
	<td colspan="2"><input name="member[password1]" id="password1" type="password" value="' . $usces_members['password1'] . '" />
	※変更しない場合は空白のまま</td>
	</tr>
	<tr>
	<th scope="row">パスワード（確認用）</th>
	<td colspan="2"><input name="member[password2]" id="password2" type="password" value="' . $usces_members['password2'] . '" />
	※変更しない場合は空白のまま</td>
	</tr>
	</table>
	<input name="member_regmode" type="hidden" value="' . $member_regmode . '" />
	<div class="send"><input name="top" type="submit" value="トップページへ戻る" />
	<input name="editmember" type="submit" value="更新する" /></div>
	</form>
	</div>
	</div>
	</div>';
?>
