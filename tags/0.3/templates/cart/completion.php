<?php
$html = '<h2>送信が完了しました</h2>
<div class="post">';

if(isset($this->payment_results['payment_status'])){
	$html .= '<div id="status_table"><h5>PayPal</h5>
		<table>';
	$html .= '<tr><th>購入日時</th><td>' . $this->payment_results['payment_date'] . "</td></tr>\n";
	$html .= '<tr><th>ステイタス</th><td>' . $this->payment_results['payment_status'] . "</td></tr>\n";
	$html .= '<tr><th>お名前</th><td>' . $this->payment_results['first_name'] . $this->payment_results['last_name'] . "</td></tr>\n";
	$html .= '<tr><th>Eメール</th><td>' . $this->payment_results['payer_email'] . "</td></tr>\n";
	$html .= '<tr><th>商品</th><td>' . $this->payment_results['item_name'] . "</td></tr>\n";
	$html .= '<tr><th>お支払い金額</th><td>' . $this->payment_results['mc_gross'] . "</td></tr>\n";
	$html .= '</table>';
	
	if($this->payment_results['payment_status'] != 'Completed'){
		$html .= "<p>お取引は完了しておりません。<br />PayPalマイアカウント･ページから代金をご送金ください。ご入金確認後、商品発送の準備をさせていただきます。</p>\n";
	}
	$html .= "</div>\n";
//	foreach($this->payment_results as $kye => $value){
//		if($kye == 'custom') urldecode($value);
//		$html .= $kye . ' = ' . $value . "<br />\n";
//	}
}
$html .= '<div class="header_explanation">';
$header = '<p>お買い上げありがとうございました。<br />ご不明な点などがございましたら、お問合せよりご連絡ください。</p>';
$html .= apply_filters('usces_filter_cartcompletion_page_header', $header);
$html .= '</div>';

$html .= '<form action="' . get_option('home') . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
<div class="send"><input name="top" type="submit" value="トップページへ戻る" /></div>
</form>';

$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_cartcompletion_page_footer', $footer);
$html .= '</div>';

$html .= '</div>';
?>
