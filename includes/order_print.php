<?PHP
require_once(USCES_PLUGIN_DIR.'/classes/fpdf/mbfpdi.php');
require_once(USCES_PLUGIN_DIR.'/classes/orderData.class.php');

//用紙サイズ(B5)
// MBfpdiクラスのインスタンス生成
if($usces->options['print_size'] == 'A4')
	$pdf = new MBfpdi('P', 'mm', array(201, 297));
else
	$pdf = new MBfpdi('P', 'mm', array(182, 257));

$usces_pdfo = new orderDataObject($_REQUEST['order_id']);

usces_pdf_out($pdf, $usces_pdfo);
die();

function usces_conv_euc($str){
	$str = str_replace(mb_convert_encoding('&yen;','UTF-8','HTML-ENTITIES'),'\\',$str);
	$str = mb_convert_encoding($str, 'EUC-JP', 'UTF-8');
	return $str;
}

function usces_pdf_out(&$pdf, $data){
	global $usces;
	
	//PDF出力基本設定
	//******************************************************

	$border = 0;//セルのボーダー初期値

	// EUC-JP -> Shift_JIS 自動変換有効
	//$GLOBALS['EUC2SJIS'] = true;

	// テンプレートファイル
	if($usces->options['print_size'] == 'A4')
		$tplfile = USCES_PLUGIN_DIR."/images/orderform_A4.pdf";
	else
		$tplfile = USCES_PLUGIN_DIR."/images/orderform_B5.pdf";


	$pagecount = $pdf->setSourceFile($tplfile);
	$tplidx = $pdf->ImportPage(1);
	$pdf->SetLeftMargin(0);
	$pdf->SetTopMargin(0);
	$pdf->addPage();
	//$pdf->useTemplate($tplidx);
	$pdf->AddMBFont(GOTHIC, 'EUC-JP');
	$pdf->AddMBFont(MINCHO, 'EUC-JP');

	// 文書情報設定
	$pdf->SetCreator('USCe-Shop');
	$pdf->SetAuthor('USconsort');
	switch ($_REQUEST['type'] ){
		case  'mitumori':
			$pdf->SetTitle('estimate');
			$filename = 'estimate_' . $data->order['ID'] . '.pdf';
			break;
		
		case 'nohin':
			$pdf->SetTitle('invoice');
			$filename = 'invoice_' . $data->order['ID'] . '.pdf';
			break;
		
		case 'receipt':
			$pdf->SetTitle('receipt');
			$filename = 'receipt_' . $data->order['ID'] . '.pdf';
			break;
		
		case 'bill':
			$pdf->SetTitle('bill');
			$filename = 'bill_' . $data->order['ID'] . '.pdf';
			break;
	}
	
	//表示モードを指定する。
	$pdf->SetDisplayMode('real', 'continuous');

	// 総ページ数のエイリアスを定義する。
	// エイリアスはドキュメントをクローズするときに置換する。
	// '{nb}' で総ページ数が得られる
	$pdf->AliasNbPages();

	//自動改ページモード
	$pdf->SetAutoPageBreak(true , 5);

	$pdf->SetFillColor(255, 255, 255);
	

	//**************************************************************
	$page  = 1;//ページ数の初期化

	//--------------------------------------------------------------
	usces_pdfSetHeader($pdf, $data, $page);
	//$pdf->SetDrawColor(255,0,0);
	$border = 0;

	$pdf->SetLeftMargin(19.8);
	$x = 15.8;
	$y = 101;
	$pdf->SetXY($x, $y);
	$next_y = $y;
	$line_x = array();
	for ( $index = 0; $index < count($data->cart); $index++ ) {
		 $cart_row = $data->cart[$index];
		//if ($cnt > $pageRec-1) {//ページが変わるときの処理
		if ( 190 < $next_y ) {//ページが変わるときの処理

			$pdf->addPage();
			//$pdf->useTemplate($tplidx);

			//-----------------------------------------------------
			usces_pdfSetHeader($pdf, $data, $page);

			$x = 15.8;
			$y = 101;
			$pdf->SetXY($x, $y);
			$next_y = $y;
		}

		//---------------------------------------------------------
		$post_id = $cart_row['post_id'];
		$cartItemName = $usces->getCartItemName($post_id, $cart_row['sku']);
		$optstr =  '';
		if( is_array($cart_row['options']) && count($cart_row['options']) > 0 ){
			foreach($cart_row['options'] as $key => $value){
				if( !empty($key) )
					$optstr .= esc_html($key) . ' = ' . esc_html(urldecode($value)) . "\n"; 
			}
		}

			$line_y[$index] = $next_y;

			list($fontsize, $lineheight, $linetop) = usces_set_font_size(10);
			$pdf->SetFont(GOTHIC, '', $fontsize);
			$pdf->SetXY($x-0.2, $line_y[$index]);
			$pdf->MultiCell(3.6, $lineheight, '*', $border, 'C');
			$pdf->SetXY($x+3.0, $line_y[$index]);
			$pdf->MultiCell(84.6, $lineheight, usces_conv_euc($cartItemName), $border, 'L');
			if( 'receipt' != $_REQUEST['type'] ){
				list($fontsize, $lineheight, $linetop) = usces_set_font_size(8);
				$pdf->SetFont(GOTHIC, '', $fontsize);
				$pdf->SetXY($x+6.0, $pdf->GetY()+$linetop);
				$pdf->MultiCell(81.6, $lineheight-0.2, usces_conv_euc($optstr), $border, 'L');
			}
						
			$next_y = $pdf->GetY()+2;
			list($fontsize, $lineheight, $linetop) = usces_set_font_size(10);
			$pdf->SetFont(GOTHIC, '', $fontsize);
			$pdf->SetXY($x+88.0, $line_y[$index]);
			$pdf->MultiCell(11.5, $lineheight, usces_conv_euc($cart_row['quantity']), $border, 'R');
			list($fontsize, $lineheight, $linetop) = usces_set_font_size(10);
			$pdf->SetFont(GOTHIC, '', $fontsize);
			$pdf->SetXY($x+99.6, $line_y[$index]);
			$pdf->MultiCell(11.5, $lineheight, usces_conv_euc($usces->getItemSkuUnit($post_id, $cart_row['sku'])), $border, 'C');
			$pdf->SetXY($x+111.5, $line_y[$index]);
			list($fontsize, $lineheight, $linetop) = usces_set_font_size(7);
			$pdf->SetFont(GOTHIC, '', $fontsize);
			$pdf->MultiCell(15.2, $lineheight, usces_conv_euc($usces->get_currency($cart_row['price'])), $border, 'R');
			$pdf->SetXY($x+126.9, $line_y[$index]);
			list($fontsize, $lineheight, $linetop) = usces_set_font_size(9);
			$pdf->SetFont(GOTHIC, '', $fontsize);
			$pdf->MultiCell(22.8, $lineheight, usces_conv_euc($usces->get_currency($cart_row['price']*$cart_row['quantity'])), $border, 'R');

			if( 190 < $next_y && 0 < $index ){
				$pdf->Rect($x, $line_y[$index]-0.4, 149.5, 197.4-$line_y[$index], 'F');

				$pdf->SetXY($x, 193);
				$pdf->MultiCell(88, $lineheight, usces_conv_euc(__('It continues to next.', 'usces')), $border, 'C');
				
				usces_pdfSetLine($pdf);
				usces_pdfSetFooter($pdf, $data);
				$index--;
				$page++;
			}
	}
	
	usces_pdfSetLine($pdf);
	usces_pdfSetFooter($pdf, $data);
	
	// Output
	//*****************************************************************
	header('Pragma:');
	header('Cache-Control: application/octet-stream');
	header("Content-type: application/pdf");
	header('Content-Length: '.strlen($pdf->buffer));
	$pdf->Output($filename, 'I');
}

//Header
function usces_pdfSetHeader($pdf, $data, $page) {
	global $usces;
	$border = 0;//border of cells
	
	switch ( $_REQUEST['type'] ){
		case  'mitumori':
			$title = __('Estimate', 'usces');
			$message = sprintf(__("Thank you for choosing '%s' we send you following estimate. ", 'usces'),
							apply_filters('usces_filter_publisher', get_option('blogname')));
			$message = apply_filters('usces_filter_pdf_estimate_message', $message, $data);
			$juchubi = __('Valid:7days', 'usces');
			$siharai = ' ';
			$sign_image = apply_filters('usces_filter_pdf_estimate_sign', NULL);
			$effective_date = date(__('M j, Y', 'usces', 'usces'), strtotime($data->order['date']));
			break;
		
		case 'nohin':
			$title = __('Invoice', 'usces');
			$message = sprintf(__("Thak you for choosing '%s'. We deliver your items as following.", 'usces'),
							apply_filters('usces_filter_publisher', get_option('blogname')));
			$message = apply_filters('usces_filter_pdf_invoice_message', $message, $data);
			$juchubi = __('date of receiving the order', 'usces').' : '.date(__('M j, Y', 'usces'), strtotime($data->order['date']));
			$siharai = __('payment division', 'usces').' : '.$data->order['payment_name'];
			$sign_image = apply_filters('usces_filter_pdf_invoice_sign', NULL);
			if( empty($data->order['delidue_date']) ){
				$effective_date = ' ';
			}else{
				$effective_date = date(__('M j, Y', 'usces'), strtotime($data->order['modified']));
			}
			break;
		
		case 'receipt':
			$title = __('Receipt', 'usces');
			$message = apply_filters('usces_filter_pdf_receipt_message', __("Your payment has been received.", 'usces'), $data);
			$juchubi = __('date of receiving the order', 'usces').' : '.date(__('M j, Y', 'usces'), strtotime($data->order['date']));
			$siharai = __('payment division', 'usces').' : '.$data->order['payment_name'];
			$sign_image = apply_filters('usces_filter_pdf_receipt_sign', NULL);
			$effective_date = date(__('M j, Y', 'usces'), strtotime($usces->get_order_meta_value('receipted_date', $data->order['ID'])));
			break;
		
		case 'bill':
			$title = __('Invoice', 'usces');
			$message = apply_filters('usces_filter_pdf_bill_message', __("Please remit payment at your earliest convenience.", 'usces'), $data);
			$juchubi = __('date of receiving the order', 'usces').' : '.date(__('M j, Y', 'usces'), strtotime($data->order['date']));
			$siharai = __('payment division', 'usces').' : '.$data->order['payment_name'];
			$sign_image = apply_filters('usces_filter_pdf_bill_sign', NULL);
			$effective_date = date(__('M j, Y', 'usces'), current_time('timestamp', 0));
			break;
	}
	

//	$pdf->Rect(14, 24, 152, 61, 'F');//Label field of customer
//	$pdf->Rect(14, 93, 153, 105, 'F');//Body field
	$pdf->SetLineWidth(0.4);
	$pdf->Line(65, 23, 110, 23);
	$pdf->SetLineWidth(0.1);
	$pdf->Line(135, 19, 165, 19);
	list($fontsize, $lineheight, $linetop) = usces_set_font_size(9);
	$pdf->SetFont(GOTHIC, '', $fontsize);
	$pdf->SetXY(136, 15.0);
	$pdf->Write(5, 'No.');
	
	// Title
	list($fontsize, $lineheight, $linetop) = usces_set_font_size(15);
	$pdf->SetFont(GOTHIC, '', $fontsize);
	$pdf->SetXY(64, 17);
	$pdf->MultiCell(45.5, $lineheight, usces_conv_euc($title), $border, 'C');
	
	// Date
	list($fontsize, $lineheight, $linetop) = usces_set_font_size(10);
	$pdf->SetFont(GOTHIC, '', $fontsize);
	$pdf->SetXY(64, 24.2);
	$pdf->MultiCell(45.5, $lineheight, usces_conv_euc($effective_date), $border, 'C');

	// Order No.
	$pdf->SetXY(142, 15.4);
	$pdf->MultiCell(24, $lineheight,  $data->order['ID'], $border, 'C');

	// Page No.
	list($fontsize, $lineheight, $linetop) = usces_set_font_size(13);
	$pdf->SetFont(GOTHIC, '', $fontsize);
	$pdf->SetXY(15.5, 15.4);
	$pdf->MultiCell(20, 7, $page.'/{nb}', 1, 'C');

	$width = 80;
	$leftside = 15;
	$pdf->SetLeftMargin($leftside);
	
	$person_honor = ( 'JP' == $usces->options['system']['currency'] ) ? "様" : '';
	$company_honor = ( 'JP' == $usces->options['system']['currency'] ) ? "御中" : '';
	$currency_post = ( 'JP' == $usces->options['system']['currency'] ) ? "-" : '';
	
	if( 'receipt' == $_REQUEST['type'] ){
		$top = 40;
		// Name of customer
		$company = $usces->get_order_meta_value('cscs_company', $data->order['ID']);
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(15);
		$pdf->SetFont(GOTHIC, '', $fontsize);
		$pdf->SetXY($leftside, $top);
		if( empty($company) ){
			$pdf->MultiCell($width, $lineheight, usces_conv_euc(usces_get_pdf_name( $data )), $border, 'L');
			$x = $leftside + $width;
			$y = $pdf->GetY() - $lineheight;
			$pdf->SetXY($x, $y);
			$pdf->Write($lineheight ,usces_conv_euc( apply_filters( 'usces_filters_pdf_person_honor', $person_honor) ));
		}else{
			$pdf->MultiCell($width, $lineheight, usces_conv_euc($company), $border, 'L');
			$x = $leftside + $width;
			$y = $pdf->GetY() - $lineheight;
			$pdf->SetXY($x, $y);
			$pdf->Write($lineheight ,usces_conv_euc( apply_filters( 'usces_filters_pdf_company_honor', $company_honor) ));
		}
		$y = $pdf->GetY() + $lineheight + $linetop;
		$pdf->SetLineWidth(0.1);
		$pdf->Line($leftside, $y, $leftside+$width+7, $y);
		
		//Total
		$y = $pdf->GetY() + $lineheight + 7;
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(20);
		$pdf->SetFont(GOTHIC, '', $fontsize);
		$pdf->SetXY($leftside+2, $y);
		$pdf->MultiCell($width, $lineheight+2, usces_conv_euc($usces->get_currency($data->order['total_full_price'], true, false) . apply_filters( 'usces_filters_pdf_currency_post', $currency_post)), 1, 'C');
		
		// Message
		$y = $pdf->GetY() + $lineheight;
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(9);
		$pdf->SetFont(GOTHIC, '', $fontsize);
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell($width+70, $lineheight, usces_conv_euc($message), $border, 'L');

		// Label
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(10);
		$pdf->SetFont(GOTHIC, '', $fontsize);
		$y = 89.7;
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell(75, $lineheight, usces_conv_euc(__('Statement', 'usces')), $border, 'L');
	}else{
		$top = 35;
		// Name of customer
		$company = $usces->get_order_meta_value('cscs_company', $data->order['ID']);
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(15);
		$pdf->SetFont(GOTHIC, '', $fontsize);
		$pdf->SetXY($leftside, $top);
		if( empty($company) ){
			$pdf->MultiCell($width, $lineheight, usces_conv_euc(usces_get_pdf_name( $data )), $border, 'L');
			$x = $leftside + $width;
			$y = $pdf->GetY() - $lineheight;
			$pdf->SetXY($x, $y);
			$pdf->Write($lineheight ,usces_conv_euc( apply_filters( 'usces_filters_pdf_person_honor', $person_honor) ));
			$y = $pdf->GetY() + $lineheight + $linetop + 2;
		}else{
			$pdf->MultiCell($width, $lineheight, usces_conv_euc($company), $border, 'L');
			$x = $leftside + $width;
			$y = $pdf->GetY() - $lineheight;
			$pdf->SetXY($x, $y);
			$pdf->Write($lineheight ,usces_conv_euc( apply_filters( 'usces_filters_pdf_company_honor', $company_honor) ));
			$y = $pdf->GetY() + $lineheight + $linetop;
			list($fontsize, $lineheight, $linetop) = usces_set_font_size(10);
			$pdf->SetFont(GOTHIC, '', $fontsize);
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell($width, $lineheight, usces_conv_euc(__("Attn", 'usces') . ' : ' . usces_get_pdf_name( $data ) . apply_filters( 'usces_filters_pdf_person_honor', $person_honor) ), $border, 'L');
			$y = $pdf->GetY() + $linetop + 2;
		}
	
		// Address
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(10);
		$pdf->SetFont(GOTHIC, '', $fontsize);
		
		usces_get_pdf_address(&$pdf, $data, $y, $linetop, $leftside, $width, $lineheight);
	
		$pdf->MultiCell($width, $lineheight, usces_conv_euc('TEL ' . $data->customer['tel']), $border, 'L');
	
		if( !empty($data->customer['fax']) ){
			$y = $pdf->GetY() + $linetop;
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell($width, $lineheight, usces_conv_euc('FAX ' . $data->customer['fax']), $border, 'L');
		}
	
		
		$y = $pdf->GetY() + $linetop;
		$pdf->SetLineWidth(0.1);
		$pdf->Line($leftside, $y, $leftside+$width+5, $y);
		
		
		// Message
		$y = 77;
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(9);
		$pdf->SetFont(GOTHIC, '', $fontsize);
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell($width+70, $lineheight, usces_conv_euc($message), $border, 'L');
	
		// Order date
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(10);
		$pdf->SetFont(GOTHIC, '', $fontsize);
		$y = 89.7;
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell(75, $lineheight, usces_conv_euc($juchubi), $border, 'L');
			
		// Payment method
		$pdf->SetXY($leftside+76, $y);
		$pdf->MultiCell(75, $lineheight, usces_conv_euc($siharai), $border, 'L');
	}
	
	// My company
	if( !empty($sign_image) ){
		$pdf->Image($sign_image, 140,40, 25, 25); 
	}
	$x = 110;
	$y = 45;
	$pdf->SetLeftMargin($x);
	list($fontsize, $lineheight, $linetop) = usces_set_font_size(11);
	$pdf->SetFont(GOTHIC, '', $fontsize);
	$pdf->SetXY($x, $y);
	$pdf->MultiCell(60, $lineheight, usces_conv_euc(get_option('blogname')), 0, 'L');
	list($fontsize, $lineheight, $linetop) = usces_set_font_size(10);
	$pdf->SetFont(GOTHIC, '', $fontsize);
	$pdf->MultiCell(60, $lineheight, usces_conv_euc($usces->options['company_name']), 0, 'L');
	usces_get_pdf_myaddress(&$pdf, $lineheight );
	$pdf->MultiCell(60, $lineheight, usces_conv_euc('TEL：'.$usces->options['tel_number']), 0, 'L');
	$pdf->MultiCell(60, $lineheight, usces_conv_euc('FAX：'.$usces->options['fax_number']), 0, 'L');
	
	

}
//Footer
function usces_pdfSetFooter($pdf, $data) {
	global $usces;
	$border = 0;


	list($fontsize, $lineheight, $linetop) = usces_set_font_size(9);
	$pdf->SetFont(GOTHIC, '', $fontsize);
	
	// Body label
	$pdf->SetXY(15.5, 94.9);
	$pdf->MultiCell(87.8, $lineheight, usces_conv_euc(__('item name','usces')), $border, 'C');
	$pdf->SetXY(103.7, 94.9);
	$pdf->MultiCell(11.4, $lineheight, usces_conv_euc(__('Quant','usces')), $border, 'C');
	$pdf->SetXY(115.8, 94.9);
	$pdf->MultiCell(11.0, $lineheight, usces_conv_euc(__('Unit', 'usces')), $border, 'C');
	$pdf->SetXY(127.2, 94.9);
	$pdf->MultiCell(15.0, $lineheight, usces_conv_euc(__('Price','usces')), $border, 'C');
	$pdf->SetXY(142.9, 94.9);
	$pdf->MultiCell(22.4, $lineheight, usces_conv_euc(__('Amount','usces').'('.usces_crcode('return').')'), $border, 'C');
	
	// Footer label
	$pdf->SetXY(104.3, 198.8);
	$pdf->MultiCell(37.7, $lineheight, usces_conv_euc(__('total items', 'usces')), $border, 'C');
	$pdf->SetXY(104.3, 204.8);
	$pdf->MultiCell(37.7, $lineheight, usces_conv_euc(__('Used points', 'usces')), $border, 'C');
	$pdf->SetXY(104.3, 210.8);
	$pdf->MultiCell(37.7, $lineheight, usces_conv_euc(__('Campaign disnount', 'usces')), $border, 'C');
	$pdf->SetXY(104.3, 216.7);
	$pdf->MultiCell(37.7, $lineheight, usces_conv_euc(__('Shipping', 'usces')), $border, 'C');
	$pdf->SetXY(104.3, 222.7);
	$pdf->MultiCell(37.7, $lineheight, usces_conv_euc(apply_filters('usces_filter_cod_label', __('COD fee', 'usces'))), $border, 'C');
	$pdf->SetXY(104.3, 228.6);
	$pdf->MultiCell(37.7, $lineheight, usces_conv_euc(__('consumption tax', 'usces')), $border, 'C');
	$pdf->SetXY(104.3, 235.8);
	$pdf->MultiCell(37.77, $lineheight, usces_conv_euc(__('Total Amount', 'usces')), $border, 'C');

	list($fontsize, $lineheight, $linetop) = usces_set_font_size(8);
	$pdf->SetFont(GOTHIC, '', $fontsize);
	// Footer value
	$pdf->SetXY(16.1, 198.8);
	$pdf->MultiCell(86.6, $lineheight, usces_conv_euc( apply_filters('usces_filter_pdf_note', $data->order['note'], $data, $_REQUEST['type'])), $border, 'J');
	list($fontsize, $lineheight, $linetop) = usces_set_font_size(9);
	$pdf->SetFont(GOTHIC, '', $fontsize);
	$pdf->SetXY(142.9, 198.8);
	$pdf->MultiCell(22.6, $lineheight, usces_conv_euc($usces->get_currency($data->order['item_total_price'])), $border, 'R');
	$pdf->SetXY(142.9, 204.8);
	$pdf->MultiCell(22.6, $lineheight, usces_conv_euc($usces->get_currency($data->order['usedpoint'])), $border, 'R');
	$pdf->SetXY(142.9, 210.8);
	$pdf->MultiCell(22.6, $lineheight, usces_conv_euc($usces->get_currency($data->order['discount'])), $border, 'R');
	$pdf->SetXY(142.9, 216.7);
	$pdf->MultiCell(22.6, $lineheight, usces_conv_euc($usces->get_currency($data->order['shipping_charge'])), $border, 'R');
	$pdf->SetXY(142.9, 222.7);
	$pdf->MultiCell(22.6, $lineheight, usces_conv_euc($usces->get_currency($data->order['cod_fee'])), $border, 'R');
	$pdf->SetXY(142.9, 228.6);
	$pdf->MultiCell(22.6, $lineheight, usces_conv_euc($usces->get_currency($data->order['tax'])), $border, 'R');
	$pdf->SetXY(142.9, 235.8);
	$pdf->MultiCell(22.67, $lineheight, usces_conv_euc($usces->get_currency($data->order['total_full_price'])), $border, 'R');

//	do_action( 'usces_action_order_print_footer', $pdf, $data);
}
//Line
function usces_pdfSetLine($pdf) {

	$pdf->Rect(14, 197.8, 153, 45, 'F');//Footer field
	$line_top = 93.5;
	$line_left = 15.4;
	$line_right = $line_left + 150.1;
	$line_bottom = $line_top + 147.9;
	$line_footertop = 197.5;
	
	// Horizontal lines
	$pdf->SetLineWidth(0.5);
	$pdf->Line($line_left, $line_top, $line_right, $line_top);
	$pdf->Line($line_left, $line_top+6.5, $line_right, $line_top+6.5);
	$pdf->Line($line_left, $line_top+104.0, $line_right, $line_top+104.0);
	$pdf->SetLineWidth(0.04);
	$pdf->Line(103.5, $line_footertop+5.9, $line_right, $line_footertop+5.9);
	$pdf->Line(103.5, $line_footertop+5.9*2, $line_right, $line_footertop+5.9*2);
	$pdf->Line(103.5, $line_footertop+5.9*3, $line_right, $line_footertop+5.9*3);
	$pdf->Line(103.5, $line_footertop+5.9*4, $line_right, $line_footertop+5.9*4);
	$pdf->SetLineWidth(0.5);
	$pdf->Line(103.5, $line_footertop+5.9*5, $line_right, $line_footertop+5.9*5);
	$pdf->Line(103.5, $line_footertop+5.9*6, $line_right, $line_footertop+5.9*6);
	$pdf->Line($line_left, $line_bottom, $line_right, $line_bottom);

	// Perpendicular lines
	$pdf->SetLineWidth(0.5);
	$pdf->Line($line_left, $line_top, $line_left, $line_bottom);
	$pdf->SetLineWidth(0.04);
	$pdf->Line(103.5, $line_top, 103.5, $line_footertop);
	$pdf->SetLineWidth(0.5);
	$pdf->Line(103.5, $line_footertop, 103.5, $line_bottom);
	$pdf->SetLineWidth(0.04);
	$pdf->Line(115.5, $line_top, 115.5, $line_footertop);
	$pdf->Line(127, $line_top, 127, $line_footertop);
	$pdf->Line(142.5, $line_top, 142.5, $line_bottom);
	$pdf->SetLineWidth(0.5);
	$pdf->Line($line_right, $line_top, $line_right, $line_bottom);
	
}
function usces_set_font_size( $size ){
	$lineheight = $size / 2.6;
	$linetop = $lineheight / 12;
	return array($size, $lineheight, $linetop);
}

function usces_get_pdf_name( $data ){
	global $usces, $usces_settings;
	$options = get_option('usces');
	$applyform = usces_get_apply_addressform($options['system']['addressform']);
	$name = '';
	switch ($applyform){
	case 'JP': 
		$name = $data->customer['name1'] . ' ' . $data->customer['name2'];
		break;
	case 'US':
	default:
		$name = $data->customer['name2'] . ' ' . $data->customer['name1'];
	}
	return $name;
}

function usces_get_pdf_address(&$pdf, $data, $y, $linetop, $leftside, $width, $lineheight){
	$options = get_option('usces');
	$applyform = usces_get_apply_addressform($options['system']['addressform']);
	$name = '';
	switch ($applyform){
	case 'JP': 
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell($width, $lineheight, usces_conv_euc(__("zip code", 'usces') . ' ' . $data->customer['zip']), $border, 'L');
		$pdf->MultiCell($width, $lineheight, usces_conv_euc($data->customer['pref'] . $data->customer['address1'] . $data->customer['address2']), $border, 'L');
	
		if( !empty($data->customer['address3']) ){
			$y = $pdf->GetY() + $linetop;
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell($width, $lineheight, usces_conv_euc($data->customer['address3']), $border, 'L');
		}
		break;
		
	case 'US':
	default:
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell($width, $lineheight, usces_conv_euc($data->customer['address2'] . ' ' . $data->customer['address3']), $border, 'L');

		$y = $pdf->GetY() + $linetop;
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell($width, $lineheight, usces_conv_euc($data->customer['address1'] . $data->customer['pref'] . $data->customer['country']), $border, 'L');

		$y = $pdf->GetY() + $linetop;
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell($width, $lineheight, usces_conv_euc(__("zip code", 'usces') . ' ' . $data->customer['zip']), $border, 'L');
		break;
	}
}

function usces_get_pdf_myaddress(&$pdf, $lineheight){
	global $usces;
	$options = get_option('usces');
	$applyform = usces_get_apply_addressform($options['system']['addressform']);
	$name = '';
	switch ($applyform){
	case 'JP': 
		$pdf->MultiCell(60, $lineheight, usces_conv_euc(__('zip code', 'usces').' '.$usces->options['zip_code']), 0, 'L');
		$pdf->MultiCell(60, $lineheight, usces_conv_euc($usces->options['address1']), 0, 'L');
		break;
		
	case 'US':
	default:
		$pdf->MultiCell(60, $lineheight, usces_conv_euc($usces->options['address1']), 0, 'L');
		$pdf->MultiCell(60, $lineheight, usces_conv_euc(__('zip code', 'usces').' '.$usces->options['zip_code']), 0, 'L');
		break;
	}
}
?>
