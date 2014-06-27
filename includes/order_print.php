<?php
global $usces;
require_once(USCES_PLUGIN_DIR.'/pdf/tcpdf/config/lang/jpn.php');
require_once(USCES_PLUGIN_DIR.'/pdf/tcpdf/tcpdf.php');
require_once(USCES_PLUGIN_DIR.'/pdf/fpdi/fpdi.php');
require_once( USCES_PLUGIN_DIR.'/classes/orderData.class.php');

define ( 'GOTHIC', 'msgothic' );

//用紙サイズ(B5)
// FPDIクラスのインスタンス生成
if(isset($usces->options['print_size']) && $usces->options['print_size'] == 'A4')
	$pdf = new FPDI('P', 'mm', 'A4', true, array(210, 297),'UTF-8');
else
	$pdf = new FPDI('P', 'mm', 'B5', true, array(182, 257),'UTF-8');

$usces_pdfo = new orderDataObject($_REQUEST['order_id']);
usces_pdf_out($pdf, $usces_pdfo);
die();

function usces_conv_euc($str){
	$str = apply_filters( 'usces_filter_pdf_conv_enc', $str);
	return $str;
}

function usces_pdf_out($pdf, $data){
	global $usces;

	$pdf->setPrintHeader( false );
	$pdf->setPrintFooter( false );

	//PDF出力基本設定
	//******************************************************

	$border = 0;//セルのボーダー初期値

	// テンプレートファイル
	if(isset($usces->options['print_size']) && $usces->options['print_size'] == 'A4')
		$tplfile = USCES_PLUGIN_DIR."/images/orderform_A4.pdf";
	else
		$tplfile = USCES_PLUGIN_DIR."/images/orderform_B5.pdf";

	$tplfile = apply_filters( 'usces_filter_pdf_template', $tplfile );

	$pagecount = $pdf->setSourceFile($tplfile);
	$tplidx = $pdf->importPage(1);	//kitamu ImportPage(1) -> importPage(1)に変更
	$pdf->SetLeftMargin(0);
	$pdf->SetTopMargin(0);
	$pdf->addPage();

//	$font = $pdf->addTTFfont( USCES_PLUGIN_DIR .'/pdf/tcpdf/fonts/add_font_name.php');
	$font = apply_filters( 'usces_filter_pdf_cfont', 'msgothic', $pdf );

	// 文書情報設定
	$pdf->SetCreator('Welcart');
	$pdf->SetAuthor('Collne Inc.');
	switch ($_REQUEST['type'] ){
		case  'mitumori':
			$pdf->SetTitle('estimate');
			$filename = 'estimate_' . usces_get_deco_order_id( $data->order['ID'] ) . '.pdf';
			break;

		case 'nohin':
			$pdf->SetTitle('invoice');
			$filename = 'invoice_' . usces_get_deco_order_id( $data->order['ID'] ) . '.pdf';
			break;

		case 'receipt':
			$pdf->SetTitle('receipt');
			$filename = 'receipt_' . usces_get_deco_order_id( $data->order['ID'] ) . '.pdf';
			break;

		case 'bill':
			$pdf->SetTitle('bill');
			$filename = 'bill_' . usces_get_deco_order_id( $data->order['ID'] ) . '.pdf';
			break;
	}

	//表示モードを指定する。
	$pdf->SetDisplayMode('real', 'continuous');

	// 総ページ数のエイリアスを定義する。
	// エイリアスはドキュメントをクローズするときに置換する。
	// '{nb}' で総ページ数が得られる

	//$pdf->AliasNbPages();		//20140107_kitamu getAliasNbPages()へ
	$pdf->getAliasNbPages();

	//自動改ページモード
	$pdf->SetAutoPageBreak(true , 5);

	$pdf->SetFillColor(255, 255, 255);

	//**************************************************************
	$page = 1;//ページ数の初期化

	//--------------------------------------------------------------
	usces_pdfSetHeader($pdf, $data, $page);
	//$pdf->SetDrawColor(255,0,0);
	$border = 0;

	$pdf->SetLeftMargin(19.8);
	$x = 15.8;
	$y = 101;
	$onep = apply_filters( 'usces_filter_pdf_page_height', 190 );
	$pdf->SetXY($x, $y);
	$next_y = $y;
	$line_x = array();
	$cart = usces_get_ordercartdata($data->order['ID']);

	for ( $index = 0; $index < count($cart); $index++ ) {
		 $cart_row = $cart[$index];
		//if ($cnt > $pageRec-1) {//ページが変わるときの処理
		if ( $onep < $next_y ) {//ページが変わるときの処理

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
		$sku = urldecode($cart_row['sku']);
		$cartItemName = $usces->getCartItemName($post_id, $sku);
		$optstr =  '';
		if( is_array($cart_row['options']) && count($cart_row['options']) > 0 ){
			foreach($cart_row['options'] as $key => $value){

				if( !empty($key) ) {
					$key = urldecode($key);
					if(is_array($value)) {
						$c = '';
						$optstr .= esc_html($key) . ' = ';
						foreach($value as $v) {
							$optstr .= $c.esc_html(urldecode($v));
							$c = ', ';
						}
						$optstr .= "\n";
					} else {
						$optstr .= esc_html($key) . ' = ' . esc_html(urldecode($value)) . "\n";
					}
				}
			}
			$optstr = apply_filters( 'usces_filter_option_pdf', $optstr, $cart_row['options']);
		}
		$optstr = apply_filters( 'usces_filter_all_option_pdf', $optstr, $cart_row['options'], $post_id, $sku, $cart_row['advance']);

		$line_y[$index] = $next_y;

		list($fontsize, $lineheight, $linetop) = usces_set_font_size(8);	//10->8
		$pdf->SetFont( $font, '', $fontsize);
		$pdf->SetXY($x-0.2, $line_y[$index]+0.8);		//kitamu +0.8
		$pdf->MultiCell(4, $lineheight, '*', $border, 'C');		//kitamu 3.6から4へ
		$pdf->SetXY($x+3.0, $line_y[$index]);
		$pdf->MultiCell(84.6, $lineheight, usces_conv_euc($cartItemName), $border, 'L');
		if( 'receipt' != $_REQUEST['type'] ){
			list($fontsize, $lineheight, $linetop) = usces_set_font_size(8);
			$pdf->SetFont($font, '', $fontsize);
			$pdf->SetXY($x+6.0, $pdf->GetY()+$linetop);
			$pdf->MultiCell(81.6, $lineheight-0.2, usces_conv_euc($optstr), $border, 'L');
		}

		$pdf_args = compact( 'page', 'x', 'y', 'onep', 'next_y', 'line_x', 'border', 'index', 'cart_row' );
		do_action( 'usces_action_order_print_cart_row', $pdf, $data, $pdf_args );

		$next_y = $pdf->GetY()+2;
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(7);	//kitamu 10->7
		$pdf->SetFont( $font, '', $fontsize);
		$pdf->SetXY($x+88.0, $line_y[$index]);
		$pdf->MultiCell(11.5, $lineheight, usces_conv_euc($cart_row['quantity']), $border, 'R');
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(7);	//kitamu 10->7
		$pdf->SetFont( $font, '', $fontsize);
		$pdf->SetXY($x+99.6, $line_y[$index]);
		$pdf->MultiCell(11.5, $lineheight, usces_conv_euc($usces->getItemSkuUnit($post_id, urldecode($cart_row['sku']))), $border, 'C');
		$pdf->SetXY($x+111.5, $line_y[$index]);
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(7);	//kitamu 7->7
		$pdf->SetFont( $font, '', $fontsize);
		$pdf->MultiCell(15.2, $lineheight, usces_conv_euc($usces->get_currency($cart_row['price'])), $border, 'R');
		$pdf->SetXY($x+126.9, $line_y[$index]);
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(7);	//kitamu 9->7
		$pdf->SetFont( $font, '', $fontsize);
		$pdf->MultiCell(22.8, $lineheight, apply_filters( 'usces_filter_cart_row_price_pdf', usces_conv_euc($usces->get_currency($cart_row['price']*$cart_row['quantity'])), $cart_row), $border, 'R');

		if( $onep < $next_y && 0 < $index ){
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

	@ob_end_clean();	//error表示を取り除く

	// Output
	//*****************************************************************
	$pdf->Output($filename, 'I');
}

//Header
function usces_pdfSetHeader($pdf, $data, $page) {
	global $usces;
	$border = 0;//border of cells

//	$font = $pdf->addTTFfont( USCES_PLUGIN_DIR .'/pdf/tcpdf/fonts/add_font_name.php');
	$font = apply_filters( 'usces_filter_pdf_cfont', 'msgothic', $pdf );

	switch ( $_REQUEST['type'] ){
		case  'mitumori':
			$title = apply_filters( 'usces_filter_pdf_estimate_title', __('Estimate', 'usces'), $data );
			$message = sprintf(__("Thank you for choosing '%s' we send you following estimate. ", 'usces'),
							apply_filters('usces_filter_publisher', get_option('blogname')));
			$message = apply_filters('usces_filter_pdf_estimate_message', $message, $data);
			$juchubi = apply_filters( 'usces_filter_pdf_estimate_validdays', __('Valid:7days', 'usces'), $data );
			$siharai = ' ';
			$sign_image = apply_filters('usces_filter_pdf_estimate_sign', NULL);
			$effective_date = date(__('M j, Y', 'usces'), strtotime($data->order['date']));
			break;

		case 'nohin':
			$title = apply_filters( 'usces_filter_pdf_invoice_title', __('Delivery Statement', 'usces'), $data );
			$message = sprintf(__("Thak you for choosing '%s'. We deliver your items as following.", 'usces'),
							apply_filters('usces_filter_publisher', get_option('blogname')));
			$message = apply_filters('usces_filter_pdf_invoice_message', $message, $data);
			$juchubi = __('date of receiving the order', 'usces').' : '.date(__('M j, Y', 'usces'), strtotime($data->order['date']));
			$siharai = __('payment division', 'usces').' : ' . apply_filters('usces_filter_pdf_payment_name', $data->order['payment_name'], $data);
			$sign_image = apply_filters('usces_filter_pdf_invoice_sign', NULL);

			if( !empty($data->order['delidue_date']) && '#none#' != $data->order['delidue_date'] ){
				$effective_date = date(__('M j, Y', 'usces'), strtotime($data->order['delidue_date']));
			}else{
				if( empty($data->order['modified']) )
					$effective_date = date(__('M j, Y', 'usces'), current_time('timestamp', 0));
				else
					$effective_date = date(__('M j, Y', 'usces'), strtotime($data->order['modified']));
			}

			break;

		case 'receipt':
			$title = apply_filters( 'usces_filter_pdf_receipt_title', __('Receipt', 'usces'), $data );
			$message = apply_filters('usces_filter_pdf_receipt_message', __("Your payment has been received.", 'usces'), $data);
			$juchubi = __('date of receiving the order', 'usces').' : '.date(__('M j, Y', 'usces'), strtotime($data->order['date']));
			$siharai = __('payment division', 'usces').' : ' . apply_filters('usces_filter_pdf_payment_name', $data->order['payment_name'], $data);
			$sign_image = apply_filters('usces_filter_pdf_receipt_sign', NULL);
			$receipted_date = $usces->get_order_meta_value('receipted_date', $data->order['ID']);
			if( empty($receipted_date) )
				$effective_date = date(__('M j, Y', 'usces'), current_time('timestamp', 0));
			else
				$effective_date = date(__('M j, Y', 'usces'), strtotime($receipted_date));
			break;

		case 'bill':
			$title = apply_filters( 'usces_filter_pdf_bill_title', __('Invoice', 'usces'), $data );
			$message = apply_filters('usces_filter_pdf_bill_message', __("Please remit payment at your earliest convenience.", 'usces'), $data);
			$juchubi = __('date of receiving the order', 'usces').' : '.date(__('M j, Y', 'usces'), strtotime($data->order['date']));
			$siharai = __('payment division', 'usces').' : ' . apply_filters('usces_filter_pdf_payment_name', $data->order['payment_name'], $data);
			$sign_image = apply_filters('usces_filter_pdf_bill_sign', NULL);
			$effective_date = date(__('M j, Y', 'usces'), current_time('timestamp', 0));
			break;
	}
	$effective_date = apply_filters('usces_filter_pdf_effective_date', $effective_date, $_REQUEST['type'], $data);

	$pdf->SetLineWidth(0.4);
	$pdf->Line(65, 23, 110, 23);
	$pdf->SetLineWidth(0.1);
	$pdf->Line(124, 19, 167, 19);
	list($fontsize, $lineheight, $linetop) = usces_set_font_size(9);
	$pdf->SetFont($font, '', $fontsize);
	$pdf->SetXY(125, 15.0);
	$pdf->Write(5, 'No.');

	// Title
	list($fontsize, $lineheight, $linetop) = usces_set_font_size(15);
	$pdf->SetFont($font, '', $fontsize);
	$pdf->SetXY(63, 16);
	$pdf->MultiCell(50, $lineheight, usces_conv_euc($title), $border, 'C');

	// Date
	list($fontsize, $lineheight, $linetop) = usces_set_font_size(9);
	$pdf->SetFont($font, '', $fontsize);
	$pdf->SetXY(64, 24.2);
	$pdf->MultiCell(45.5, $lineheight, usces_conv_euc($effective_date), $border, 'C');

	// Order No.
	$pdf->SetXY(131, 15);
	$pdf->MultiCell(36, $lineheight,  usces_get_deco_order_id( $data->order['ID'] ), $border, 'R');

	// Page No.
	list($fontsize, $lineheight, $linetop) = usces_set_font_size(13);
	$pdf->SetFont($font, '', $fontsize);
	$pdf->SetXY(15.5, 15.4);

	$pdf->Cell( 20, 7, ' ' . $page . '/ ' . $pdf->getAliasNbPages(), 1);

	$width = 80;
	$leftside = 15;
	$pdf->SetLeftMargin($leftside);

	$person_honor = ( 'JP' == $usces->options['system']['currency'] ) ? "様" : '';
	$company_honor = ( 'JP' == $usces->options['system']['currency'] ) ? "御中" : '';
	$currency_post = ( 'JP' == $usces->options['system']['currency'] ) ? "-" : '';

	if( 'receipt' == $_REQUEST['type'] ){
		$top = 40;

		$meta = usces_has_custom_field_meta('customer');
		$company = $usces->get_order_meta_value('cscs_company', $data->order['ID']);
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(12);
		$pdf->SetFont($font, '', $fontsize);
		$pdf->SetXY($leftside, $top);

		if( empty( $company ) || !isset( $meta['company'] ) ){
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
		$pdf->SetFont($font, '', $fontsize);
		$pdf->SetXY($leftside+2, $y);
		$pdf->MultiCell($width, $lineheight+2, usces_conv_euc($usces->get_currency($data->order['total_full_price'], true, false) . apply_filters( 'usces_filters_pdf_currency_post', $currency_post)), 1, 'C');

		// Message
		$y = $pdf->GetY() + $lineheight;
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(9);
		$pdf->SetFont($font, '', $fontsize);
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell($width+70, $lineheight, usces_conv_euc($message), $border, 'L');

		// Label
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(10);
		$pdf->SetFont($font, '', $fontsize);
		$y = 89;
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell(75, $lineheight, usces_conv_euc(__('Statement', 'usces')), $border, 'L');


	}elseif( 'nohin' == $_REQUEST['type'] ){
		//「配送先を宛名とする」
		if( $usces->options['system']['pdf_delivery'] == 1 ){
			$top = 30;

			$meta = usces_has_custom_field_meta('delivery');
			$deliveri_company = $usces->get_order_meta_value('csde_company', $data->order['ID']);
			list($fontsize, $lineheight, $linetop) = usces_set_font_size(12);
			$pdf->SetFont($font, '', $fontsize);
			$pdf->SetXY($leftside, $top);

			if( empty( $deliveri_company ) || !isset( $meta['company'] ) ){
				$pdf->MultiCell($width, $lineheight, usces_conv_euc(usces_get_pdf_shipping_name( $data )), $border, 'L');
				$x = $leftside + $width;
				$y = $pdf->GetY() - $lineheight;
				$pdf->SetXY($x, $y);
				$pdf->Write($lineheight, usces_conv_euc( apply_filters( 'usces_filters_pdf_person_honor', $person_honor) ));
				$y = $pdf->GetY() + $lineheight + $linetop + 2;
			}else{
				$pdf->MultiCell($width, $lineheight, usces_conv_euc($deliveri_company), $border, 'L');
				$x = $leftside + $width;
				$y = $pdf->GetY() - $lineheight;
				$pdf->SetXY($x, $y);
				$pdf->Write($lineheight ,usces_conv_euc( apply_filters( 'usces_filters_pdf_company_honor', $company_honor) ));
				$y = $pdf->GetY() + $lineheight + $linetop;
				list($fontsize, $lineheight, $linetop) = usces_set_font_size(8);
				$pdf->SetFont($font, '', $fontsize);
				$pdf->SetXY($leftside, $y);
				$pdf->MultiCell($width, $lineheight, usces_conv_euc(__("Attn", 'usces') . ' : ' . usces_get_pdf_shipping_name( $data ) . apply_filters( 'usces_filters_pdf_person_honor', $person_honor) ), $border, 'L');
				$y = $pdf->GetY() + $linetop + 2;
			}
			// Address
			list($fontsize, $lineheight, $linetop) = usces_set_font_size(8);
			$pdf->SetFont($font, '', $fontsize);

			usces_get_pdf_shipping_address($pdf, $data, $y, $linetop, $leftside, $width, $lineheight);

			$pdf->MultiCell($width, $lineheight, usces_conv_euc('TEL ' . $data->deliveri['tel']), $border, 'L');

			if( !empty($data->deliveri['fax']) ){
				$y = $pdf->GetY() + $linetop;
				$pdf->SetXY($leftside, $y);
				$pdf->MultiCell($width, $lineheight, usces_conv_euc('FAX ' . $data->deliveri['fax']), $border, 'L');
			}
		//「購入者情報を宛名とする」
		}else{
			$top = 30;

			$meta = usces_has_custom_field_meta('customer');
			$company = $usces->get_order_meta_value('cscs_company', $data->order['ID']);
			list($fontsize, $lineheight, $linetop) = usces_set_font_size(12);
			$pdf->SetFont($font, '', $fontsize);
			$pdf->SetXY($leftside, $top);

			if( empty( $company ) || !isset( $meta['company'] ) ){
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
				list($fontsize, $lineheight, $linetop) = usces_set_font_size(8);
				$pdf->SetFont($font, '', $fontsize);
				$pdf->SetXY($leftside, $y);
				$pdf->MultiCell($width, $lineheight, usces_conv_euc(__("Attn", 'usces') . ' : ' . usces_get_pdf_name( $data ) . apply_filters( 'usces_filters_pdf_person_honor', $person_honor) ), $border, 'L');
				$y = $pdf->GetY() + $linetop + 2;
			}
			// Address
			list($fontsize, $lineheight, $linetop) = usces_set_font_size(8);
			$pdf->SetFont($font, '', $fontsize);

			usces_get_pdf_address($pdf, $data, $y, $linetop, $leftside, $width, $lineheight);

			$pdf->MultiCell($width, $lineheight, usces_conv_euc('TEL ' . $data->customer['tel']), $border, 'L');

			if( !empty($data->customer['fax']) ){
				$y = $pdf->GetY() + $linetop;
				$pdf->SetXY($leftside, $y);
				$pdf->MultiCell($width, $lineheight, usces_conv_euc('FAX ' . $data->customer['fax']), $border, 'L');
			}
			//配送先情報
			$customer_name = trim( $data->customer['name1'] ) . trim( $data->customer['name2'] );
			$deliveri_name = trim( $data->deliveri['name1'] ) . trim( $data->deliveri['name2'] );
			$customer_zip = trim( $data->customer['zip'] );
			$deliveri_zip = trim( $data->deliveri['zipcode'] );
			$customer_address = trim( $data->customer['address1'] ) . trim( $data->customer['address2']) . trim( $data->customer['address3'] );
			$deliveri_address = trim( $data->deliveri['address1'] ) . trim( $data->deliveri['address2']) . trim( $data->deliveri['address3'] );

			//発送先が入力されているとき
			if( !empty($deliveri_address) ){
				//購入者と発送先情報が異なるとき
				if( $customer_name != $deliveri_name || $customer_zip != $deliveri_zip || $customer_address != $deliveri_address){
					// Line	
					$y = $pdf->GetY() + $linetop;
					$pdf->SetLineWidth(0.1);
					$pdf->Line( $leftside, $y, $leftside+$width+5, $y );

					//【配送先】タイトル
					list($fontsize, $lineheight, $linetop) = usces_set_font_size(8);	// 10->8
					$y = $pdf->GetY() + $linetop + 1;
					$pdf->SetFont($font, '', $fontsize);
					$pdf->SetXY($leftside, $y);
					$pdf->MultiCell($width, $lineheight, usces_conv_euc( __( "** A shipping address **", 'usces' ) ), $border, 'L');
					
					//配送先宛名
					$meta = usces_has_custom_field_meta('delivery');
					$deliveri_company = $usces->get_order_meta_value( 'csde_company', $data->order['ID'] );
					list($fontsize, $lineheight, $linetop) = usces_set_font_size(6);
					$y = $pdf->GetY() + $linetop;
					$pdf->SetFont($font, '', $fontsize);
					$pdf->SetXY($leftside, $y);
					if( empty( $deliveri_company ) || !isset( $meta['company'] ) ){
						$pdf->MultiCell($width, $lineheight, usces_conv_euc( usces_get_pdf_shipping_name( $data ) ), $border, 'L');
						$x = $leftside + $width;
						$y = $pdf->GetY() - $lineheight - $linetop;
						$pdf->SetXY($x, $y);
						$pdf->Write($lineheight ,usces_conv_euc( apply_filters( 'usces_filters_pdf_person_honor', $person_honor ) ));	//様
						$y = $pdf->GetY() + $lineheight + $linetop;
					}else{
						$pdf->MultiCell($width, $lineheight, usces_conv_euc($deliveri_company), $border, 'L');
						$x = $leftside + $width;
						$y = $pdf->GetY() - $lineheight;
						$pdf->SetXY($x, $y);
						$pdf->Write($lineheight, usces_conv_euc( apply_filters( 'usces_filters_pdf_company_honor', $company_honor ) ));	//御中
						$y = $pdf->GetY() + $lineheight + $linetop;
						list($fontsize, $lineheight, $linetop) = usces_set_font_size(6);
						$pdf->SetFont($font, '', $fontsize);
						$pdf->SetXY($leftside, $y);
						$pdf->MultiCell($width, $lineheight, usces_conv_euc(__("Attn", 'usces') . ' : ' . usces_get_pdf_shipping_name( $data ) . apply_filters( 'usces_filters_pdf_person_honor', $person_honor) ), $border, 'L');
						$y = $pdf->GetY() + $linetop;
					}
					//配送先住所
					list($fontsize, $lineheight, $linetop) = usces_set_font_size(6);
					$pdf->SetFont($font, '', $fontsize);
					usces_get_pdf_shipping_address($pdf, $data, $y, $linetop, $leftside, $width, $lineheight);
					
					//配送先電話番号
					$pdf->MultiCell($width, $lineheight, usces_conv_euc('TEL ' . $data->deliveri['tel']), $border, 'L');
				}
			}
		}
		$y = $pdf->GetY() + $linetop + 0.5;

		$pdf->SetLineWidth(0.1);
		$pdf->Line($leftside, $y, $leftside+$width+5, $y);

		// Message
		$y = 80;
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(9);
		$pdf->SetFont($font, '', $fontsize);
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell($width+70, $lineheight, usces_conv_euc($message), $border, 'L');

		// Order date
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(10);
		$pdf->SetFont($font, '', $fontsize);
		$y = 89;
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell(75, $lineheight, usces_conv_euc($juchubi), $border, 'L');

		// Payment method
		$pdf->SetXY($leftside+68, $y);
		$pdf->Cell(75, $lineheight, usces_conv_euc($siharai), $border, 1, 'L');

	}else{
		$top = 30;

		$meta = usces_has_custom_field_meta('customer');
		$company = $usces->get_order_meta_value('cscs_company', $data->order['ID']);
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(12);
		$pdf->SetFont($font, '', $fontsize);
		$pdf->SetXY($leftside, $top);

		if( empty( $company ) || !isset( $meta['company'] ) ){
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
			list($fontsize, $lineheight, $linetop) = usces_set_font_size(8);
			$pdf->SetFont($font, '', $fontsize);
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell($width, $lineheight, usces_conv_euc(__("Attn", 'usces') . ' : ' . usces_get_pdf_name( $data ) . apply_filters( 'usces_filters_pdf_person_honor', $person_honor) ), $border, 'L');
			$y = $pdf->GetY() + $linetop + 2;
		}
		// Address
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(8);
		$pdf->SetFont($font, '', $fontsize);

		usces_get_pdf_address($pdf, $data, $y, $linetop, $leftside, $width, $lineheight);

		$pdf->MultiCell($width, $lineheight, usces_conv_euc('TEL ' . $data->customer['tel']), $border, 'L');

		if( !empty($data->customer['fax']) ){
			$y = $pdf->GetY() + $linetop;
			$pdf->SetXY($leftside, $y);
			$pdf->MultiCell($width, $lineheight, usces_conv_euc('FAX ' . $data->customer['fax']), $border, 'L');
		}
				$y = $pdf->GetY() + $linetop + 0.5;

		$pdf->SetLineWidth(0.1);
		$pdf->Line($leftside, $y, $leftside+$width+5, $y);

		// Message
		$y = 80;
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(9);
		$pdf->SetFont($font, '', $fontsize);
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell($width+70, $lineheight, usces_conv_euc($message), $border, 'L');

		// Order date
		list($fontsize, $lineheight, $linetop) = usces_set_font_size(10);
		$pdf->SetFont($font, '', $fontsize);
		$y = 89;
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell(75, $lineheight, usces_conv_euc($juchubi), $border, 'L');

		// Payment method
		$pdf->SetXY($leftside+68, $y);
		$pdf->Cell(75, $lineheight, usces_conv_euc($siharai), $border, 1, 'L');
	}

	// My company
	if( !empty($sign_image) ){
		$sign_data = apply_filters( 'usces_filter_pdf_sign_data', array(140, 40, 25, 25));
		$pdf->Image($sign_image, $sign_data[0], $sign_data[1], $sign_data[2], $sign_data[3]);
	}
	$x = 110;
	$y = 45;
	$pdf->SetLeftMargin($x);
	list($fontsize, $lineheight, $linetop) = usces_set_font_size(9);
	$pdf->SetFont($font, '', $fontsize);
	$pdf->SetXY($x, $y);
	$pdf->MultiCell(60, $lineheight, usces_conv_euc(apply_filters('usces_filter_publisher', get_option('blogname'))), 0, 'L');
	list($fontsize, $lineheight, $linetop) = usces_set_font_size(8);
	$pdf->SetFont($font, '', $fontsize);
	$pdf->MultiCell(60, $lineheight, usces_conv_euc(apply_filters('usces_filter_pdf_mycompany', $usces->options['company_name'])), 0, 'L');
	usces_get_pdf_myaddress($pdf, $lineheight );
	$pdf->MultiCell(60, $lineheight, usces_conv_euc('TEL：'.$usces->options['tel_number']), 0, 'L');
	$pdf->MultiCell(60, $lineheight, usces_conv_euc('FAX：'.$usces->options['fax_number']), 0, 'L');
}

//Footer
function usces_pdfSetFooter($pdf, $data) {
	global $usces;

//	$font = $pdf->addTTFfont( USCES_PLUGIN_DIR .'/pdf/tcpdf/fonts/add_font_name.php');
	$font = apply_filters( 'usces_filter_pdf_cfont', 'msgothic', $pdf );

	$border = 0;
	list($fontsize, $lineheight, $linetop) = usces_set_font_size(9);
	$pdf->SetFont($font, '', $fontsize);

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
	$pdf->MultiCell(22.4, $lineheight, usces_conv_euc(__('Amount','usces').'('.__(usces_crcode( 'return' ), 'usces').')'), $border, 'C');

	// Footer label
	$labeldata = array(
		'order_condition' => $data->condition,
		'order_item_total_price' => $data->order['item_total_price'],
		'order_discount' => $data->order['discount'],
		'order_shipping_charge' => $data->order['shipping_charge'],
		'order_cod_fee' => $data->order['cod_fee'],
	);
	$pdf->SetXY(104.3, 198.8);
	$pdf->MultiCell(37.7, $lineheight, usces_conv_euc(__('total items', 'usces')), $border, 'C');
	$pdf->SetXY(104.3, 204.8);
	$pdf->MultiCell(37.7, $lineheight, usces_conv_euc(apply_filters('usces_filter_disnount_label', __('Campaign disnount', 'usces'))), $border, 'C');
	
	if( 'products' == usces_get_tax_target() ){
		$data_1 = apply_filters('usces_filter_tax_label', usces_tax_label( $labeldata, 'return' ));
		$data_2 = apply_filters('usces_filter_shipping_label', __('Shipping', 'usces'));
		$data_3 = apply_filters('usces_filter_cod_label', __('COD fee', 'usces'));
	}else{
		$data_1 = apply_filters('usces_filter_shipping_label', __('Shipping', 'usces'));
		$data_2 = apply_filters('usces_filter_cod_label', __('COD fee', 'usces'));
		$data_3 = apply_filters('usces_filter_tax_label', usces_tax_label( $labeldata, 'return' ));
	}
	
	$pdf->SetXY(104.3, 210.8);
	$pdf->MultiCell(37.7, $lineheight, usces_conv_euc($data_1), $border, 'C');
	$pdf->SetXY(104.3, 216.7);
	$pdf->MultiCell(37.7, $lineheight, usces_conv_euc($data_2), $border, 'C');
	$pdf->SetXY(104.3, 222.7);
	$pdf->MultiCell(37.7, $lineheight, usces_conv_euc($data_3), $border, 'C');
	$pdf->SetXY(104.3, 228.6);
	$pdf->MultiCell(37.7, $lineheight, usces_conv_euc(apply_filters('usces_filter_point_label', __('Used points', 'usces'))), $border, 'C');
	$pdf->SetXY(104.3, 235.8);
	$pdf->MultiCell(37.77, $lineheight, usces_conv_euc(__('Total Amount', 'usces')), $border, 'C');

	list($fontsize, $lineheight, $linetop) = usces_set_font_size(8);
	$pdf->SetFont($font, '', $fontsize);
	// Footer value
	$pdf->SetXY(16.1, 198.8);
	$pdf->MultiCell(86.6, $lineheight, usces_conv_euc( apply_filters('usces_filter_pdf_note', $data->order['note'], $data, $_REQUEST['type'])), $border, 'J');
	list($fontsize, $lineheight, $linetop) = usces_set_font_size(9);
	$pdf->SetFont($font, '', $fontsize);
	$pdf->SetXY(142.9, 198.8);
	$pdf->MultiCell(22.6, $lineheight, usces_conv_euc($usces->get_currency($data->order['item_total_price'])), $border, 'R');

	$materials = array(
		'total_items_price' => $data->order['item_total_price'],
		'discount' => $data->order['discount'],
		'shipping_charge' => $data->order['shipping_charge'],
		'cod_fee' => $data->order['cod_fee'],
	);
	if( 'include' == $usces->options['tax_mode'] ){
		$tax = '('.usces_internal_tax( $materials, 'return' ).')';
	}else{
		$tax = $usces->get_currency($data->order['tax']);
	}
	if( 'products' == usces_get_tax_target() ){
		$datav_1 = apply_filters('usces_filter_tax_vlue', $tax, $data);
		$datav_2 = apply_filters('usces_filter_shipping_vlue', $usces->get_currency($data->order['shipping_charge']));
		$datav_3 = apply_filters('usces_filter_cod_vlue', $usces->get_currency($data->order['cod_fee']));
	}else{
		$datav_1 = apply_filters('usces_filter_shipping_vlue', $usces->get_currency($data->order['shipping_charge']));
		$datav_2 = apply_filters('usces_filter_cod_vlue', $usces->get_currency($data->order['cod_fee']));
		$datav_3 = apply_filters('usces_filter_tax_vlue', $tax, $data);
	}

	$pdf->SetXY(142.9, 204.8);
	$pdf->MultiCell(22.6, $lineheight, usces_conv_euc(apply_filters('usces_filter_disnount_vlue', $usces->get_currency($data->order['discount']))), $border, 'R');
	$pdf->SetXY(142.9, 210.8);
	$pdf->MultiCell(22.6, $lineheight, usces_conv_euc($datav_1), $border, 'R');
	$pdf->SetXY(142.9, 216.7);
	$pdf->MultiCell(22.6, $lineheight, usces_conv_euc($datav_2), $border, 'R');
	$pdf->SetXY(142.9, 222.7);
	$pdf->MultiCell(22.6, $lineheight, usces_conv_euc($datav_3), $border, 'R');
	$pdf->SetXY(142.9, 228.6);
	$pdf->MultiCell(22.6, $lineheight, usces_conv_euc(apply_filters('usces_filter_point_vlue', $usces->get_currency($data->order['usedpoint']))), $border, 'R');
	$pdf->SetXY(142.9, 235.8);
	$pdf->MultiCell(22.67, $lineheight, usces_conv_euc($usces->get_currency($data->order['total_full_price'])), $border, 'R');

	do_action( 'usces_action_order_print_footer', $pdf, $data);
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

//20140107_kitamu_start 配送先の名前を取得
function usces_get_pdf_shipping_name( $data ){
	global $usces, $usces_settings;
	$options = get_option('usces');
	$applyform = usces_get_apply_addressform($options['system']['addressform']);
	$name = '';
	switch ($applyform){
	case 'JP': 
		$name = $data->deliveri['name1'] . ' ' . $data->deliveri['name2'];
		break;
	case 'US':
	default:
		$name = $data->deliveri['name2'] . ' ' . $data->deliveri['name1'];
	}

	return $name;
}
//20140107_kitamu_end

function usces_get_pdf_address($pdf, $data, $y, $linetop, $leftside, $width, $lineheight){
	$options = get_option('usces');
	$applyform = usces_get_apply_addressform($options['system']['addressform']);
	$name = '';
	$border = '';
	$pref = ( __( '-- Select --','usces') == $data->customer['pref'] ) ? '' : $data->customer['pref'];

	switch ($applyform){
	case 'JP': 
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell($width, $lineheight, usces_conv_euc(__("zip code", 'usces') . ' ' . $data->customer['zip']), $border, 'L');
		$pdf->MultiCell($width, $lineheight, usces_conv_euc($pref . $data->customer['address1'] . $data->customer['address2']) .' '. $data->customer['address3'], $border, 'L');
		break;

	case 'US':
	default:
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell($width, $lineheight, usces_conv_euc($data->customer['address2'] . ' ' . $data->customer['address3'] . ' ' . $data->customer['address1'] . ' ' . $pref . ' ' . $data->customer['country']), $border, 'L');

		$y = $pdf->GetY() + $linetop;
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell($width, $lineheight, usces_conv_euc(__("zip code", 'usces') . ' ' . $data->customer['zip']), $border, 'L');
		break;
	}
}

//20140107_kitamu_start 配送先が異なる場合の表示
function usces_get_pdf_shipping_address($pdf, $data, $y, $linetop, $leftside, $width, $lineheight){
	$options = get_option('usces');
	$applyform = usces_get_apply_addressform($options['system']['addressform']);
	$name = '';
	$border = '';
	$pref = ( __( '-- Select --','usces') == $data->deliveri['pref'] ) ? '' : $data->deliveri['pref'];

	switch ($applyform){
	case 'JP': 
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell($width, $lineheight, usces_conv_euc(__("zip code", 'usces') . ' ' . $data->deliveri['zipcode']), $border, 'L');
		$pdf->MultiCell($width, $lineheight, usces_conv_euc($pref . $data->deliveri['address1'] . $data->deliveri['address2'] .' '. $data->deliveri['address3']), $border, 'L');

		break;

	case 'US':
	default:
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell($width, $lineheight, usces_conv_euc($data->deliveri['address2'] . ' ' . $data->deliveri['address3'] . ' ' .  $data->deliveri['address1'] . ' ' . $pref . ' ' . $data->deliveri['country']), $border, 'L');

		$y = $pdf->GetY() + $linetop;
		$pdf->SetXY($leftside, $y);
		$pdf->MultiCell($width, $lineheight, usces_conv_euc(__("zip code", 'usces') . ' ' . $data->deliveri['zipcode']), $border, 'L');
		break;
	}
}

//20140107_kitamu_end 

function usces_get_pdf_myaddress($pdf, $lineheight){
	global $usces;
	$options = get_option('usces');
	$applyform = usces_get_apply_addressform($options['system']['addressform']);
	$name = '';
	switch ($applyform){
	case 'JP': 
		$address = ( empty($usces->options['address2']) ) ? $usces->options['address1'] : $usces->options['address1'] . "\n" . $usces->options['address2'];
		$pdf->MultiCell(60, $lineheight, usces_conv_euc(__('zip code', 'usces').' '.$usces->options['zip_code']), 0, 'L');
		$pdf->MultiCell(60, $lineheight, usces_conv_euc($address), 0, 'L');
		break;

	case 'US':
	default:
		$address = ( empty($usces->options['address2']) ) ? $usces->options['address1'] : $usces->options['address2'] . "\n" . $usces->options['address1'];
		$pdf->MultiCell(60, $lineheight, usces_conv_euc($address), 0, 'L');
		$pdf->MultiCell(60, $lineheight, usces_conv_euc(__('zip code', 'usces').' '.$usces->options['zip_code']), 0, 'L');
		break;
	}
}
?>
