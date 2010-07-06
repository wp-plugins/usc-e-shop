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
//	$enc = mb_detect_encoding($str);
//	if($enc != 'EUC-JP'){
		$str = mb_convert_encoding($str, 'EUC-JP', 'UTF-8');
//	}
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


	// テンプレートをインポート
	$pagecount = $pdf->setSourceFile($tplfile);
	$tplidx = $pdf->ImportPage(1);

	// 左マージンを10mmに設定
	$pdf->SetLeftMargin(0);

	// 上マージンを10mmに設定
	$pdf->SetTopMargin(0);

	// ページ追加
	$pdf->addPage();

	// テンプレートを使用
	$pdf->useTemplate($tplidx);

	// マルチバイトフォント登録
	$pdf->AddMBFont(GOTHIC, 'EUC-JP');
	$pdf->AddMBFont(MINCHO, 'EUC-JP');

	// 文書情報設定
	$pdf->SetCreator('USCe-Shop');
	$pdf->SetAuthor('USconsort');
	if($_REQUEST['type'] == 'mitumori')
		$pdf->SetTitle('mitumori');
	elseif($_REQUEST['type'] == 'nohin')
		$pdf->SetTitle('nohinsyo');

	//表示モードを指定する。
	$pdf->SetDisplayMode('real', 'continuous');

	// 総ページ数のエイリアスを定義する。
	// エイリアスはドキュメントをクローズするときに置換する。
	// '{nb}' で総ページ数が得られる
	$pdf->AliasNbPages();

	//自動改ページモード
	$pdf->SetAutoPageBreak(true , 5);

	$pdf->SetFillColor(255, 255, 255);

	//データセット
	//**************************************************************
	$cnt = 0;//ページ内のカウントの初期化
	$pg  = 1;//ページ数の初期化
	$pageRec = 10;//1ページに入るレコード数

	//ヘッダ出力
	//--------------------------------------------------------------
	usces_pdfSetHeader($pdf, $data);

	foreach ( $data->cart as $cart_row ) {

		if ($cnt > $pageRec-1) {//ページが変わるときの処理

			// ページ追加
			$pdf->addPage();
			// テンプレートを使用
			$pdf->useTemplate($tplidx);

			//ヘッダ出力
			//-----------------------------------------------------
			usces_pdfSetHeader($pdf, $data);

			$pg++;//ページナンバー更新
			$pageno++;//ページナンバー更新
			$cnt = 0;
		}

		//ボディ出力
		//---------------------------------------------------------
		$d = $cnt * 9.7;
	
		$post_id = $cart_row['post_id'];
		$cartItemName = $usces->getCartItemName($post_id, $cart_row['sku']);
		$optstr =  '';
		if( is_array($cart_row['options']) && count($cart_row['options']) > 0 ){
			foreach($cart_row['options'] as $key => $value){
				if( !empty($key) )
					$optstr .= $key . '=' . $value . ','; 
			}
		}
		$optstr = rtrim($optstr, ',');
		$HB = usces_conv_euc($cart_row['sku']);
		$HM = usces_conv_euc($cartItemName);
		//$optstr = implode($cart_row['options']);
		$OP = usces_conv_euc($optstr);
		$SR = number_format($cart_row['quantity']);
		$TI = usces_conv_euc($usces->getItemSkuUnit($post_id, $cart_row['sku']));
		$TK = number_format($cart_row['price']);
		$SK = number_format($cart_row['price']*$cart_row['quantity']);
		$HM1 = mb_substr($HM, 0, 18);
		$HM2 = mb_substr((mb_substr($HM, 18) . '/' . $OP), 0, 18);
		$HMm = $HM . usces_conv_euc('/') . $OP;
		if( strlen($HMm) <= 70 ){
			$HMsize = 9;
		}elseif( strlen($HMm) <= 80 ){
			$HMsize = 8;
		}elseif( strlen($HMm) <= 96 ){
			$HMsize = 7;
		}else{
			$HMsize = 6;
		}

		if( $usces->options['print_size'] == 'A4' ) {
			// 品番
			$pdf->SetFont(GOTHIC, '', 9);
			$pdf->SetXY(15.8, 100.5+$d);
			$pdf->Cell(10, 3.5, "$HB", $border, 0, 'L', 1);
	
			// 品名
			$pdf->SetFont(GOTHIC, '', $HMsize);
			$pdf->SetXY(40.6, 100.5+$d);
			$pdf->MultiCell(40, 3.5, "$HMm", $border, 'J');
	
			// 仕様
//			$pdf->SetFont(GOTHIC, '', 9);
//			$pdf->SetXY(40.6, 15.5+$d);
//			$pdf->Cell(40, 3.5, "$OP", $border, 0, 'L', 1);
	
			// 数量
			$pdf->SetFont(GOTHIC, '', 10);
			$pdf->SetXY(110, 100.3+$d);
			$pdf->Cell(4, 4.5, "$SR", $border, 0, 'R', 1);
	
			// 単位
			$pdf->SetFont(GOTHIC, '', 10);
			$pdf->SetXY(119, 100.5+$d);
			$pdf->Cell(3, 3.5, "$TI", $border, 0, 'C', 1);
	
			// 単価
			$pdf->SetFont(GOTHIC, '', 10);
			$pdf->SetXY(130.4, 100.3+$d);
			$pdf->Cell(10, 4.5, "$TK", $border, 0, 'R', 1);
	
			// 小計
			$pdf->SetFont(GOTHIC, '', 10);
			$pdf->SetXY(144.2, 100.3+$d);
			$pdf->Cell(20, 4.5, "$SK", $border, 0, 'R', 1);
		} else {
			// 品番
			$pdf->SetFont(GOTHIC, '', 9);
			$pdf->SetXY(15.8, 101.5+$d);
			$pdf->MultiCell(26, 3.3, "$HB", $border, 'J');
	
			// 品名
			$pdf->SetFont(GOTHIC, '', $HMsize);
			$pdf->SetXY(43.0, 101.5+$d);
			$pdf->MultiCell(59, 3.5, "$HMm", $border, 'J');
	
			// 仕様
//			$pdf->SetFont(GOTHIC, '', 9);
//			$pdf->SetXY(43.0, 105.5+$d);
//			$pdf->Cell(60, 3.5, "$OP", $border, 0, 'L', 1);
	
			// 数量
			$pdf->SetFont(GOTHIC, '', 10);
			$pdf->SetXY(104.5, 101.5+$d);
			$pdf->Cell(10, 3.5, "$SR", $border, 0, 'R', 1);
	
			// 単位
			$pdf->SetFont(GOTHIC, '', 8);
			$pdf->SetXY(115.6, 101.5+$d);
			$pdf->MultiCell(11, 3.5, "$TI", $border, 'C');
	
			// 単価
			$pdf->SetFont(GOTHIC, '', 9);
			$pdf->SetXY(127.5, 101.5+$d);
			$pdf->Cell(14.5, 3.5, "$TK", $border, 0, 'R', 1);
	
			// 小計
			$pdf->SetFont(GOTHIC, '', 10);
			$pdf->SetXY(143, 101.5+$d);
			$pdf->Cell(22, 3.5, "$SK", $border, 0, 'R', 1);
		}
		
		$cnt++;
	}

	//フッタデータ出力
	//------------------------------------------------------------
	// 商品合計
	$SKK = number_format($data->order['item_total_price']);
	$pdf->SetFont(GOTHIC, '', 10);
	$pdf->SetXY(143, 198.7);
	$pdf->Cell(22, 3.5, "$SKK", $border, 0, 'R', 1);

	// ポイント
	$NBK = number_format($data->order['usedpoint']);
	$pdf->SetFont(GOTHIC, '', 10);
	$pdf->SetXY(143, 204.6);
	$pdf->Cell(22, 3.5, "$NBK", $border, 0, 'R', 1);

	// 値引
	$NBK = number_format($data->order['discount']);
	$pdf->SetFont(GOTHIC, '', 10);
	$pdf->SetXY(143, 210.4);
	$pdf->Cell(22, 3.5, "$NBK", $border, 0, 'R', 1);

	// 送料
	$SOK = number_format($data->order['shipping_charge']);
	$pdf->SetFont(GOTHIC, '', 10);
	$pdf->SetXY(143, 216.2);
	$pdf->Cell(22, 3.5, "$SOK", $border, 0, 'R', 1);

	// 代引き手数料
	$TSK = number_format($data->order['cod_fee']);
	$pdf->SetFont(GOTHIC, '', 10);
	$pdf->SetXY(143, 222.1);
	$pdf->Cell(22, 3.5, "$TSK", $border, 0, 'R', 1);

	// 消費税
	$TXK = number_format($data->order['tax']);
	$pdf->SetFont(GOTHIC, '', 10);
	$pdf->SetXY(143, 227.9);
	$pdf->Cell(22, 3.5, "$TXK", $border, 0, 'R', 1);

	// 総合計
	$SGK = number_format($data->order['total_full_price']);
	$pdf->SetFont(GOTHIC, '', 12);
	$pdf->SetXY(142.5, 234.5);
	$pdf->Cell(23, 5, "$SGK", $border, 0, 'R', 1);

	// 備考
	$pdf->SetFont(GOTHIC, '', 9);
	$pdf->SetXY(15.8, 199);
	$pdf->MultiCell(99, 3.5, usces_conv_euc($data->order['note']), $border, 'J');

	// PDF出力
	//*****************************************************************
	header('Pragma:');
	header('Cache-Control: application/octet-stream');
	header("Content-type: application/pdf");
	header('Content-Length: '.strlen($pdf->buffer));
	$pdf->Output();
}

//ヘッダ出力
function usces_pdfSetHeader(&$pdf, $data) {
	global $usces;
	$border = 0;//セルのボーダー初期値

	if($_REQUEST['type'] == 'mitumori') {
		$title = __('Estimate', 'usces');
		$message = sprintf(__("Thank you for choosing '%s' we send you following estimate. ", 'usces'),
						apply_filters('usces_filter_publisher', get_option('blogname')));
		$juchubi = __('Valid:7days', 'usces');
		$siharai = ' ';
	} elseif($_REQUEST['type'] == 'nohin') {
		$title = __('Invoices', 'usces');
		$message = sprintf(__("Thak you for choosing '%s'. We deliver your items as following.", 'usces'),
						apply_filters('usces_filter_publisher', get_option('blogname')));
		$juchubi = __('date of receiving the order', 'usces').' : '.$data->order['date'];
		$siharai = __('payment division', 'usces').' : '.$data->order['payment_name'];
	}
	
	if( $usces->options['print_size'] == 'A4' ) {
		// タイトル
		$pdf->SetFont(GOTHIC, '', 13);
		$pdf->SetXY(65, 17);
		$pdf->Cell(43, 5.5, usces_conv_euc($title), $border, 0, 'C', 1);
	
		// 日付
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(15, 17);
		$pdf->Cell(30, 4, usces_conv_euc(date(__( 'M j, Y', 'usces' ))), $border, 0, 'L', 1);
	
		// 注文No
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(145, 15.4);
		$pdf->Cell(16, 3, $data->order['ID'], $border, 0, 'R', 1);
	
		// 会員番号
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(35, 24.5);
		$pdf->Cell(20, 3, $data->customer['mem_id'], $border, 0, 'L', 1);
	
		// 名前
		$pdf->SetFont(GOTHIC, '', 11);
		$pdf->SetXY(34, 29.5);
		$pdf->Cell(57.4, 5, usces_conv_euc($data->customer['name1'].$data->customer['name2']), $border, 0, 'L', 1);
	
		// 郵便番号
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(34, 36.5);
		$pdf->Cell(62.5, 3.5, usces_conv_euc($data->customer['zip'], $border, 0, 'L', 1));
	
		// 都道府県
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(34, 41);
		$pdf->Cell(62.5, 3.5, usces_conv_euc($data->customer['pref']), $border, 0 ,'L', 1);
	
		// 住所
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(34, 45.5);
		$pdf->Cell(62.5, 3.5, usces_conv_euc($data->customer['address1'].$data->customer['address2']), $border, 0, 'L', 1);
	
		// ビル
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(34, 50);
		$pdf->Cell(62.5, 3.5, usces_conv_euc($data->customer['address3']), $border, 0, 'L', 1);
	
		// 電話
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(34, 57);
		$pdf->Cell(62.5, 3.5, usces_conv_euc($data->customer['tel'], $border, 0, 'L', 1));
	
		// FAX
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(34, 63.7);
		$pdf->Cell(62.5, 3.5, usces_conv_euc($data->customer['fax'], $border, 0, 'L', 1));
	
		// E-mail
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(34, 70.4);
		$pdf->Cell(62.5, 3.5, usces_conv_euc($data->customer['email'], $border, 0, 'L', 1));
		
		// サイト名
		$pdf->SetFont(GOTHIC, '', 11);
		$pdf->SetXY(100, 36.5);
		$pdf->Cell(65, 5, usces_conv_euc(apply_filters('usces_filter_publisher', get_option('blogname'))), $border, 0, 'C', 1);
		
		// サイトURL
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(100, 41.5);
		$pdf->MultiCell(65, 3, usces_conv_euc(get_option('home')), $border, 'C');
		
		// 会社名
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(100, 48);
		$pdf->MultiCell(65, 3.5, usces_conv_euc($usces->options['company_name']), $border, 'C');
		
		// 会社郵便番号
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(105, 51.5);
		$pdf->Cell(60, 3.5, usces_conv_euc($usces->options['zip_code']), $border, 0, 'L', 1);
		
		// 会社住所1
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(105, 55);
		$pdf->Cell(60, 3.5, usces_conv_euc($usces->options['address1']), $border, 0, 'L', 1);
		
		// 会社住所2
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(105, 58.5);
		$pdf->Cell(60, 3.5, usces_conv_euc($usces->options['address2']), $border, 0, 'L', 1);
		
		// 会社電話番号
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(105, 62);
		$pdf->Cell(60, 3.5, usces_conv_euc('TEL：'.$usces->options['tel_number']), $border, 0, 'L', 1);
		
		// 会社FAX番号
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(105, 65.5);
		$pdf->Cell(60, 3.5, usces_conv_euc('FAX：'.$usces->options['fax_number']), $border, 0, 'L', 1);
		
		// メッセージ
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(15, 80);
		$pdf->MultiCell(150, 3.5, usces_conv_euc($message), $border, 'J');
		
		// 受注日
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(15.5, 89.7);
		$pdf->Cell(60, 3.5, usces_conv_euc($juchubi), $border, 0, 'L', 1);
		
		// 支払い区分
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(75.5, 89.7);
		$pdf->Cell(60, 3.5, usces_conv_euc($siharai), $border, 0, 'L', 1);
	} else {
		// タイトル
		$pdf->SetFont(GOTHIC, '', 13);
		$pdf->SetXY(65, 17);
		$pdf->Cell(43, 5.5, usces_conv_euc($title), $border, 0, 'C', 1);
	
		// 日付
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(15, 17);
		$pdf->Cell(30, 4, usces_conv_euc(date(__( 'M j, Y', 'usces' ))), $border, 0, 'L', 1);
	
		// 注文No
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(145, 15.4);
		$pdf->Cell(16, 3, $data->order['ID'], $border, 0, 'R', 1);
	
		// 会員番号
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(35, 24.5);
		$pdf->Cell(20, 3, $data->customer['mem_id'], $border, 0, 'L', 1);
	
		// 名前
		$pdf->SetFont(GOTHIC, '', 11);
		$pdf->SetXY(34, 29.5);
		$pdf->Cell(57.4, 5, usces_conv_euc($data->customer['name1'].$data->customer['name2']), $border, 0, 'L', 1);
	
		// 郵便番号
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(34, 36.5);
		$pdf->Cell(62.5, 3.5, usces_conv_euc($data->customer['zip'], $border, 0, 'L', 1));
	
		// 都道府県
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(34, 41);
		$pdf->Cell(62.5, 3.5, usces_conv_euc($data->customer['pref']), $border, 0 ,'L', 1);
	
		// 住所
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(34, 45.5);
		$pdf->Cell(62.5, 3.5, usces_conv_euc($data->customer['address1'].$data->customer['address2']), $border, 0, 'L', 1);
	
		// ビル
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(34, 50);
		$pdf->Cell(62.5, 3.5, usces_conv_euc($data->customer['address3']), $border, 0, 'L', 1);
	
		// 電話
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(34, 57);
		$pdf->Cell(62.5, 3.5, usces_conv_euc($data->customer['tel'], $border, 0, 'L', 1));
	
		// FAX
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(34, 63.7);
		$pdf->Cell(62.5, 3.5, usces_conv_euc($data->customer['fax'], $border, 0, 'L', 1));
	
		// E-mail
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(34, 70.4);
		$pdf->Cell(62.5, 3.5, usces_conv_euc($data->customer['email'], $border, 0, 'L', 1));
		
		// サイト名
		$pdf->SetFont(GOTHIC, '', 11);
		$pdf->SetXY(100, 36.5);
		$pdf->Cell(65, 5, usces_conv_euc(apply_filters('usces_filter_publisher', get_option('blogname'))), $border, 0, 'C', 1);
		
		// サイトURL
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(100, 41.5);
		$pdf->MultiCell(65, 3, usces_conv_euc(get_option('home')), $border, 'C');
		
		// 会社名
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(100, 48);
		$pdf->MultiCell(65, 3.5, usces_conv_euc($usces->options['company_name']), $border, 'C');
		
		// 会社郵便番号
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(105, 51.5);
		$pdf->Cell(60, 3.5, usces_conv_euc($usces->options['zip_code']), $border, 0, 'L', 1);
		
		// 会社住所1
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(105, 55);
		$pdf->Cell(60, 3.5, usces_conv_euc($usces->options['address1']), $border, 0, 'L', 1);
		
		// 会社住所2
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(105, 58.5);
		$pdf->Cell(60, 3.5, usces_conv_euc($usces->options['address2']), $border, 0, 'L', 1);
		
		// 会社電話番号
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(105, 62);
		$pdf->Cell(60, 3.5, usces_conv_euc('TEL：'.$usces->options['tel_number']), $border, 0, 'L', 1);
		
		// 会社FAX番号
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(105, 65.5);
		$pdf->Cell(60, 3.5, usces_conv_euc('FAX：'.$usces->options['fax_number']), $border, 0, 'L', 1);
		
		// メッセージ
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(15, 80);
		$pdf->MultiCell(150, 3.5, usces_conv_euc($message), $border, 'J');
		
		// 受注日
		$pdf->SetFont(GOTHIC, '', 10);
		$pdf->SetXY(15.5, 89.7);
		$pdf->Cell(60, 3.5, usces_conv_euc($juchubi), $border, 0, 'L', 1);
		
		// 支払い区分
		$pdf->SetFont(GOTHIC, '', 9);
		$pdf->SetXY(75.5, 89.7);
		$pdf->Cell(60, 3.5, usces_conv_euc($siharai), $border, 0, 'L', 1);
	}
}
?>
