<?php
if( isset($this->payment_results['X-TRANID']) ){ //remise_card

		
}elseif( isset($_REQUEST['acting']) && 'remise_conv' == $_REQUEST['acting'] ){ //remise_conv

	$html .= '<div id="status_table"><h5>ルミーズ・コンビニ決済</h5>'."\n";
	$html .= '<table>'."\n";
	$html .= '<tr><th>'.__('ご請求番号', 'usces').'</th><td>' . $_REQUEST["X-S_TORIHIKI_NO"] . "</td></tr>\n";
	$html .= '<tr><th>'.__('ご請求合計金額', 'usces').'</th><td>' . $_REQUEST["X-TOTAL"] . "</td></tr>\n";
	$html .= '<tr><th>'.__('お支払期限', 'usces').'</th><td>' . substr($_REQUEST["X-PAYDATE"], 0, 4).'年' . substr($_REQUEST["X-PAYDATE"], 4, 2).'月' . substr($_REQUEST["X-PAYDATE"], 6, 2).'日' . "(期限を過ぎますとお支払ができません)</td></tr>\n";
	$html .= '<tr><th>'.__('お支払先', 'usces').'</th><td>' . usces_get_conv_name('D001') . "</td></tr>\n";
	$html .= '<tr><th>' . __('受付番号','usces') . '</th><td>' . $_REQUEST["X-PAY_NO1"] . "</td></tr>\n";
	$html .= '<tr><th>'.__('払込票URL', 'usces').'</th><td><a href="'.$_REQUEST["X-PAY_NO2"].'" target="_blank">'.$_REQUEST["X-PAY_NO2"]."</a></td></tr>\n";
	$html .= '</table>'."\n";
	$html .= '<p>「お支払いのご案内」は、' . $entry['customer']['mailaddress1'] . '　宛にメールさせて頂いております。</p>'."\n";
	$html .= "</div>\n";
		
}elseif( isset($this->payment_results['mc_gross']) ){ //PayPal

	$html .= '<div id="status_table"><h5>PayPal</h5>'."\n";
	$html .= '<table>'."\n";
	$html .= '<tr><th>'.__('Purchase date', 'usces').'</th><td>' . $this->payment_results['payment_date'] . "</td></tr>\n";
	$html .= '<tr><th>'.__('Status', 'usces').'</th><td>' . $this->payment_results['payment_status'] . "</td></tr>\n";
	$html .= '<tr><th>'.__('Full name', 'usces').'</th><td>' . $this->payment_results['first_name'] . $this->payment_results['last_name'] . "</td></tr>\n";
	$html .= '<tr><th>'.__('e-mail', 'usces').'</th><td>' . $this->payment_results['payer_email'] . "</td></tr>\n";
	$html .= '<tr><th>' . __('Items','usces') . '</th><td>' . $this->payment_results['item_name'] . "</td></tr>\n";
	$html .= '<tr><th>'.__('Payment amount', 'usces').'</th><td>' . $this->payment_results['mc_gross'] . "</td></tr>\n";
	$html .= '</table>';
	
	if( $this->payment_results['payment_status'] != 'Completed' ){
		$html .= __('<p>The settlement is not completed.<br />Please remit the price from the PayPal Maia count page.After receipt of money confirmation, I will prepare for the article shipment.</p>', 'usces') . "\n";
	}
	$html .= "</div>\n";
}
?>
f<a href="sfh" target="_blank">ghfghf</a>