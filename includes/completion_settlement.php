<?php
if( isset($this->payment_results['X-TRANID']) ){ //remise_card

		
}elseif( isset($_REQUEST['acting']) && 'remise_conv' == $_REQUEST['acting'] ){ //remise_conv

	$html .= '<div id="status_table"><h5>ルミーズ・コンビニ決済</h5>'."\n";
	$html .= '<table>'."\n";
	$html .= '<tr><th>'.__('ご請求番号', 'usces').'</th><td>' . esc_html($_REQUEST["X-S_TORIHIKI_NO"]) . "</td></tr>\n";
	$html .= '<tr><th>'.__('ご請求合計金額', 'usces').'</th><td>' . esc_html($_REQUEST["X-TOTAL"]) . "</td></tr>\n";
	$html .= '<tr><th>'.__('お支払期限', 'usces').'</th><td>' . esc_html(substr($_REQUEST["X-PAYDATE"], 0, 4).'年' . substr($_REQUEST["X-PAYDATE"], 4, 2).'月' . substr($_REQUEST["X-PAYDATE"], 6, 2).'日') . "(期限を過ぎますとお支払ができません)</td></tr>\n";
	$html .= '<tr><th>'.__('お支払先', 'usces').'</th><td>' . esc_html(usces_get_conv_name('D001')) . "</td></tr>\n";
	$html .= '<tr><th>' . __('受付番号','usces') . '</th><td>' . esc_html($_REQUEST["X-PAY_NO1"]) . "</td></tr>\n";
	$html .= '<tr><th>'.__('払込票URL', 'usces').'</th><td><a href="'.esc_html($_REQUEST["X-PAY_NO2"]).'" target="_blank">'.esc_html($_REQUEST["X-PAY_NO2"])."</a></td></tr>\n";
	$html .= '</table>'."\n";
	$html .= '<p>「お支払いのご案内」は、' . esc_html($entry['customer']['mailaddress1']) . '　宛にメールさせて頂いております。</p>'."\n";
	$html .= "</div>\n";
		
}elseif( isset($_REQUEST['acting']) && 'zeus_conv' == $_REQUEST['acting'] ){ //remise_conv

	$html .= '<div id="status_table"><h5>ゼウス・コンビニ決済</h5>'."\n";
	$html .= '<table>'."\n";
	$html .= '<tr><th>'.__('オーダー番号', 'usces').'</th><td>' . esc_html($this->payment_results['order_no']) . "</td></tr>\n";
	switch($this->payment_results['pay_cvs']){
		case 'D001':
			$html .= '<tr><th>'.__('払込表番号', 'usces').'</th><td>' . esc_html($this->payment_results['pay_no1']) . "</td></tr>\n";
			$html .= '<tr><th>' . __('URL','usces') . '</th><td><a href="'.esc_attr($this->payment_results['pay_url']).'" target="_blank">'.esc_html($this->payment_results['pay_url']) . '"</a></td></tr>'."\n";
			break;
		case 'D002':
			$html .= '<tr><th>'.__('お支払受付番号', 'usces').'</th><td>' . esc_html($this->payment_results['pay_no1']) . "</td></tr>\n";
			break;
		case 'D030':
			$html .= '<tr><th>'.__('注文番号', 'usces').'</th><td>' . esc_html($this->payment_results['pay_no1']) . "</td></tr>\n";
			$html .= '<tr><th>'.__('企業コード', 'usces').'</th><td>' . esc_html($this->payment_results['pay_no2']) . "</td></tr>\n";
			break;
		case 'D040':
			$html .= '<tr><th>'.__('ｵﾝﾗｲﾝ決済番号', 'usces').'</th><td>' . esc_html($this->payment_results['pay_no1']) . "</td></tr>\n";
			break;
		case 'D015':
			$html .= '<tr><th>'.__('お支払受付番号', 'usces').'</th><td>' . esc_html($this->payment_results['pay_no1']) . "</td></tr>\n";
			break;
	}
	$html .= '<tr><th>'.__('お支払期限', 'usces').'</th><td>' . esc_html(substr($this->payment_results['pay_limit'], 0, 4).'年' . substr($this->payment_results['pay_limit'], 4, 2).'月' . substr($this->payment_results['pay_limit'], 6, 2).'日') . "(期限を過ぎますとお支払ができません)</td></tr>\n";
	$html .= '<!-- <tr><th>'.__('エラーコード', 'usces').'</th><td>' . esc_html($this->payment_results['error_code']) . "</td></tr> -->\n";
	$html .= '</table>'."\n";
	$html .= '<p>「お支払いのご案内」は、' . esc_html($entry['customer']['mailaddress1']) . '　宛にメールさせて頂いております。</p>'."\n";
	$html .= "</div>\n";
		
}elseif( isset($this->payment_results['mc_gross']) ){ //PayPal

	$html .= '<div id="status_table"><h5>PayPal</h5>'."\n";
	$html .= '<table>'."\n";
	$html .= '<tr><th>'.__('Purchase date', 'usces').'</th><td>' . esc_html($this->payment_results['payment_date']) . "</td></tr>\n";
	$html .= '<tr><th>'.__('Status', 'usces').'</th><td>' . esc_html($this->payment_results['payment_status']) . "</td></tr>\n";
	$html .= '<tr><th>'.__('Full name', 'usces').'</th><td>' . esc_html($this->payment_results['first_name']) . esc_html($this->payment_results['last_name']) . "</td></tr>\n";
	$html .= '<tr><th>'.__('e-mail', 'usces').'</th><td>' . esc_html($this->payment_results['payer_email']) . "</td></tr>\n";
	$html .= '<tr><th>' . __('Items','usces') . '</th><td>' . esc_html($this->payment_results['item_name']) . "</td></tr>\n";
	$html .= '<tr><th>'.__('Payment amount', 'usces').'</th><td>' . esc_html($this->payment_results['mc_gross']) . "</td></tr>\n";
	$html .= '</table>';
	
	if( $this->payment_results['payment_status'] != 'Completed' ){
		$html .= __('<p>The settlement is not completed.<br />Please remit the price from the PayPal Maia count page.After receipt of money confirmation, I will prepare for the article shipment.</p>', 'usces') . "\n";
	}
	$html .= "</div>\n";
}
?>
