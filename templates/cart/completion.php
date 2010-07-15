<?php
global $usces;
$entry = $usces->cart->get_entry();
$cart = $usces->cart->get_cart();

$html = '<h2>'.__('It has been sent succesfully.', 'usces').'</h2>
<div class="post">';

if(isset($this->payment_results['payment_status'])){
	$html .= '<div id="status_table"><h5>PayPal</h5>
		<table>';
	$html .= '<tr><th>'.__('Purchase date', 'usces').'</th><td>' . $this->payment_results['payment_date'] . "</td></tr>\n";
	$html .= '<tr><th>'.__('Status', 'usces').'</th><td>' . $this->payment_results['payment_status'] . "</td></tr>\n";
	$html .= '<tr><th>'.__('Full name', 'usces').'</th><td>' . $this->payment_results['first_name'] . $this->payment_results['last_name'] . "</td></tr>\n";
	$html .= '<tr><th>'.__('e-mail', 'usces').'</th><td>' . $this->payment_results['payer_email'] . "</td></tr>\n";
	$html .= '<tr><th>' . __('Items','usces') . '</th><td>' . $this->payment_results['item_name'] . "</td></tr>\n";
	$html .= '<tr><th>'.__('Payment amount', 'usces').'</th><td>' . $this->payment_results['mc_gross'] . "</td></tr>\n";
	$html .= '</table>';
	
	if($this->payment_results['payment_status'] != 'Completed'){
		$html .= __('<p>The settlement is not completed.<br />Please remit the price from the PayPal Maia count page.After receipt of money confirmation, I will prepare for the article shipment.</p>', 'usces') . "\n";
	}
	$html .= "</div>\n";
//	foreach($this->payment_results as $kye => $value){
//		if($kye == 'custom') urldecode($value);
//		$html .= $kye . ' = ' . $value . "<br />\n";
//	}
}
$html .= '<div class="header_explanation">';
$header = '<p>'.__('Thank you for shopping.', 'usces').'<br />'.__("If you have any questions, please contact us by 'Contact'.", 'usces').'</p>';
$html .= apply_filters('usces_filter_cartcompletion_page_header', $header, $entry, $cart);
$html .= '</div>';

$html .= '<form action="' . get_option('home') . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
<div class="send"><input name="top" type="submit" value="'.__('Back to the top page.', 'usces').'" /></div>
</form>';

$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_cartcompletion_page_footer', $footer, $entry, $cart);
$html .= '</div>';

$html .= '</div>';
?>
