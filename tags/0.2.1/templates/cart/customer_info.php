<?php 
$usces_entries = $this->cart->get_entry();
$member_regmode = isset( $_SESSION['usces_entry']['member_regmode'] ) ? $_SESSION['usces_entry']['member_regmode'] : 'none';

$html = '<div id="customer-info">

<div class="usccart_navi">
<ol class="ucart">
<li class="ucart">' . __('1.Cart','usces') . '</li>
<li class="ucart usccart_customer">' . __('2.Customer Info','usces') . '</li>
<li class="ucart">' . __('3.Deli. & Pay.','usces') . '</li>
<li class="ucart">' . __('4.Confirm','usces') . '</li>
</ol>
</div>';

$html .= '<div class="header_explanation">';
$header = '';
$html .= apply_filters('usces_filter_customer_page_header', $header);
$html .= '</div>';

$html .= '<div class="error_message">' . $this->error_message . '</div>';

if(usces_is_membersystem_state()){
	$html .= '<h5>会員の方はこちら▼</h5>
	<form action="' . USCES_CART_URL . '" method="post" name="customer_loginform">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="customer_form">
	<tr>
	<th scope="row">メールアドレス</th>
	<td><input name="loginmail" id="mailaddress1" type="text" value="' . $usces_entries['customer']['mailaddress1'] . '" /></td>
	</tr>
	<tr>
	<th scope="row">パスワード</th>
	<td><input name="loginpass" id="mailaddress1" type="password" value="" /></td>
	</tr>
	</table>
	<div class="send"><input name="customerlogin" type="submit" value="　　次　へ　　" /></div>
	</form>
	<h5>会員ではない方はこちら▼</h5>';
}

$html .= '<form action="' . USCES_CART_URL . '" method="post" name="customer_form">
<table border="0" cellpadding="0" cellspacing="0" class="customer_form">
<tr>
<th scope="row"><em>*</em>メールアドレス</th>';

$html .= '<td colspan="2"><input name="customer[mailaddress1]" id="mailaddress1" type="text" value="' . $usces_entries['customer']['mailaddress1'] . '" /></td>
</tr>
<tr>
<th scope="row"><em>*</em>メールアドレス（再入力）</th>
<td colspan="2"><input name="customer[mailaddress2]" id="mailaddress2" type="text" value="' . $usces_entries['customer']['mailaddress2'] . '" /></td>
</tr>';

if(usces_is_membersystem_state()){
	$html .= '<tr><th scope="row">';
	if( $member_regmode == 'editmemberfromcart' ){
		$html .= '<em>*</em>';
	}
	$html .= 'パスワード</th>
	<td colspan="2"><input name="customer[password1]" style="width:100px" type="password" value="' . $usces_entries['customer']['password1'] . '" />';
	if( $member_regmode != 'editmemberfromcart' ){
		$html .= '新規会員登録する場合にご記入ください。';
	}
	$html .= '</td></tr>';
	$html .= '<tr><th scope="row">';
	if( $member_regmode == 'editmemberfromcart' ){
		$html .= '<em>*</em>';
	}
	$html .= 'パスワード（再入力）</th>
	<td colspan="2"><input name="customer[password2]" style="width:100px" type="password" value="' . $usces_entries['customer']['password2'] . '" />';
	if( $member_regmode != 'editmemberfromcart' ){
		$html .= '新規会員登録する場合にご記入ください。';
	}
	$html .= '</td></tr>';
}
$html .= '<tr class="inp1">
<th scope="row"><em>*</em>お名前</th>
<td>姓<input name="customer[name1]" id="name1" type="text" value="' . $usces_entries['customer']['name1'] . '" /></td>
<td>名<input name="customer[name2]" id="name2" type="text" value="' . $usces_entries['customer']['name2'] . '" /></td>
</tr>
<tr class="inp1">
<th scope="row"><em>*</em>フリガナ</th>
<td>姓<input name="customer[name3]" id="name3" type="text" value="' . $usces_entries['customer']['name3'] . '" /></td>
<td>名<input name="customer[name4]" id="name4" type="text" value="' . $usces_entries['customer']['name4'] . '" /></td>
</tr>
<tr>
<th scope="row"><em>*</em>郵便番号</th>
<td colspan="2"><input name="customer[zipcode]" id="zipcode" type="text" value="' . $usces_entries['customer']['zipcode'] . '" />例）100-1000</td>
</tr>
<tr>
<th scope="row"><em>*</em>都道府県</th>
<td colspan="2">' . usces_the_pref( 'customer', 'return' ) . '</td>
</tr>
<tr class="inp2">
<th scope="row"><em>*</em>市区郡町村</th>
<td colspan="2"><input name="customer[address1]" id="address1" type="text" value="' . $usces_entries['customer']['address1'] . '" />例）横浜市上北町</td>
</tr>
<tr>
<th scope="row"><em>*</em>番地</th>
<td colspan="2"><input name="customer[address2]" id="address2" type="text" value="' . $usces_entries['customer']['address2'] . '" />例）3-24-555</td>
</tr>
<tr>
<th scope="row">マンション･ビル名</th>
<td colspan="2"><input name="customer[address3]" id="address3" type="text" value="' . $usces_entries['customer']['address3'] . '" />例）通販ビル4F</td>
</tr>
<tr>
<th scope="row"><em>*</em>電話番号</th>
<td colspan="2"><input name="customer[tel]" id="tel" type="text" value="' . $usces_entries['customer']['tel'] . '" />例）1000-10-1000</td>
</tr>
<tr>
<th scope="row">FAX番号</th>
<td colspan="2"><input name="customer[fax]" id="fax" type="text" value="' . $usces_entries['customer']['fax'] . '" />例）1000-10-1000</td>
</tr>
</table>
<input name="member_regmode" type="hidden" value="' . $member_regmode . '" />

<div class="send"><input name="backCart" type="submit" value="　　戻　る　　" />&nbsp;&nbsp;';

$button = '<input name="deliveryinfo" type="submit" value="　　次　へ　　" />&nbsp;&nbsp;';
$html .= apply_filters('usces_filter_customer_button', $button);

if(usces_is_membersystem_state() && $member_regmode != 'editmemberfromcart' && usces_is_login() == false ){
	$html .= '<input name="reganddeliveryinfo" type="submit" value="会員登録しながら次へ" />';
}elseif(usces_is_membersystem_state() && $member_regmode == 'editmemberfromcart' ){
	$html .= '<input name="reganddeliveryinfo" type="submit" value="会員情報を修正して次へ" />';
}

$html .= '</div>
</form>';

$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_customer_page_footer', $footer);
$html .= '</div>';

$html .= '</div>';
?>
