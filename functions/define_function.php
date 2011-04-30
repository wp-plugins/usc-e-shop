<?php
function usces_define_functions(){

//20101111ysk start
if( !function_exists('usces_item_uploadcsv') ):
function usces_item_uploadcsv(){
	require_once( USCES_PLUGIN_DIR . "/libs/excel_reader2.php" );
	global $wpdb, $usces;
	//$wpdb->show_errors();
	$res = $wpdb->query( 'SET SQL_BIG_SELECTS=1' );
	set_time_limit(1800);
	
	define('USCES_COL_ITEM_CODE', 0);
	define('USCES_COL_ITEM_NAME', 1);
	define('USCES_COL_ITEM_RESTRICTION', 2);
	define('USCES_COL_ITEM_POINTRATE', 3);
	define('USCES_COL_ITEM_GPNUM1', 4);
	define('USCES_COL_ITEM_GPDIS1', 5);
	define('USCES_COL_ITEM_GPNUM2', 6);
	define('USCES_COL_ITEM_GPDIS2', 7);
	define('USCES_COL_ITEM_GPNUM3', 8);
	define('USCES_COL_ITEM_GPDIS3', 9);
	define('USCES_COL_ITEM_SHIPPING', 10);
	define('USCES_COL_ITEM_DELIVERYMETHOD', 11);
	define('USCES_COL_ITEM_SHIPPINGCHARGE', 12);
	define('USCES_COL_ITEM_INDIVIDUALSCHARGE', 13);
	define('USCES_COL_POST_TITLE', 14);
	define('USCES_COL_POST_CONTENT', 15);
	define('USCES_COL_POST_EXCERPT', 16);
	define('USCES_COL_POST_STATUS', 17);
	define('USCES_COL_POST_MODIFIED', 18);
	define('USCES_COL_CATEGORY', 19);
	define('USCES_COL_POST_TAG', 20);
	define('USCES_COL_SKU_CODE', 21);
	define('USCES_COL_SKU_NAME', 22);
	define('USCES_COL_SKU_CPRICE', 23);
	define('USCES_COL_SKU_PRICE', 24);
	define('USCES_COL_SKU_ZAIKONUM', 25);
	define('USCES_COL_SKU_ZAIKO', 26);
	define('USCES_COL_SKU_UNIT', 27);
	define('USCES_COL_SKU_GPTEKIYO', 28);
	define('IDENTIFIER_OLE', pack("CCCCCCCC",0xd0,0xcf,0x11,0xe0,0xa1,0xb1,0x1a,0xe1));

	$workfile = $_FILES["usces_upcsv"]["tmp_name"];
	$lines = array();
	$total_num = 0;
	$comp_num = 0;
	$err_num = 0;
	$min_field_num = 29;
	$log = '';
	$pre_code = '';
	$res = array();
	$date_pattern = "/(\d{4})-(\d{2}|\d)-(\d{2}|\d) (\d{2}):(\d{2}|\d):(\d{2}|\d)/";
	
	if ( !is_uploaded_file($workfile) ) {
		$res['status'] = 'error';
		$res['message'] = __('The file was not uploaded.', 'usces');
		return $res;
	}

	//check ext
	list($fname, $fext) = explode('.', $_FILES["usces_upcsv"]["name"], 2);
	if( $fext != 'csv' && $fext != 'xls' ) {
		$res['status'] = 'error';
		$res['message'] =  __('The file is not supported.', 'usces').$fname.'.'.$fext;
		return $res;
	}
	
	//log
	if ( ! ($fpi = fopen (USCES_PLUGIN_DIR.'/logs/itemcsv_log.txt', "w"))) {
		$res['status'] = 'error';
		$res['message'] = __('The log file was not prepared for.', 'usces');
		return $res;
	}
	//read data
	if ( ! ($fpo = fopen ($workfile, "r"))) {
		$res['status'] = 'error';
		$res['message'] = __('A file does not open.', 'usces').$fname.'.'.$fext;
		return $res;
	}
	
	$lines = array();
	$sp = ",";
	if('xls' === $fext) {
		$sp = "\t";
		$data = @file_get_contents($workfile);
		if (!$data) {
			$res['status'] = 'error';
			$res['message'] = __('A file does not open.', 'usces').$fname.'.'.$fext;
			return $res;
		}
		if(substr($data, 0, 8) != IDENTIFIER_OLE) {
			//$fext = 'tsv';
			//while (! feof ($fpo)) {
			//	$temp = fgets ($fpo, 10240);
			//	if( 5 < strlen($temp) )
			//		$lines[] = str_replace('"', '', $temp);
			//}
			//20101208ysk
			$res['status'] = 'error';
			$res['message'] = __('このファイルはExcelファイルでは有りません。', 'usces').$fname.'.'.$fext;
			return $res;
		} else {
			$excel = new Spreadsheet_Excel_Reader();
			$excel->read($workfile);
			$rows = $excel->rowcount();//最大行数
			$cols = $excel->colcount();//最大列数
			for($r = 1; $r <= $rows; $r++) {
				$line = '';
				for($c = 1; $c <= $cols; $c++) {
					$line .= mb_convert_encoding($excel->val($r, $c), "SJIS", "UTF-8").$sp;
				}
				$line = trim($line, $sp);
				$lines[] = $line;
			}
		}
	} else {
		while (! feof ($fpo)) {
			$temp = fgets ($fpo, 10240);
			if( 5 < strlen($temp) )
				$lines[] = $temp;
		}
	}
	$total_num = count($lines);

	//data check & reg
	foreach($lines as $rows_num => $line){
		$datas = array();
		$logtemp = '';
//20110201ysk start
		//$datas = explode($sp, $line);
		$d = explode($sp, $line);
		foreach($d as $key => $data) {
			$datas[$key] = trim($data, '"');
		}
//20110201ysk end
//		if( $min_field_num > count($datas) || 0 < (count($datas) - $min_field_num) % 4 ){
		if( $min_field_num > count($datas) ){
			$err_num++;
			$logtemp .= "No." . ($rows_num+1)." ".count($datas) . "\t".__('The number of the columns is abnormal.', 'usces')."\r\n";
			$log .= $logtemp;
			continue;
		}
		foreach($datas as $key => $data){
			$data = trim(mb_convert_encoding($data, 'UTF-8', 'SJIS'));
			switch($key){
				case USCES_COL_ITEM_CODE:
					if( 0 == strlen($data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('An item cord is non-input.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_NAME:
					if( 0 == strlen($data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('An item name is non-input.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_RESTRICTION:
					if( !preg_match("/^[0-9]+$/", $data) && 0 != strlen($data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the purchase limit number is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_POINTRATE:
					if( !preg_match("/^[0-9]+$/", $data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the point rate is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_GPNUM1:
					if( !preg_match("/^[0-9]+$/", $data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."1-".__('umerical value is abnormality.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_GPDIS1:
					if( !preg_match("/^[0-9]+$/", $data) || ( 0 < $datas[USCES_COL_ITEM_GPNUM1] && 1 > $data ) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."1-".__('rate is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_GPNUM2:
					if( !preg_match("/^[0-9]+$/", $data) || ($datas[USCES_COL_ITEM_GPNUM1] >= $data && 0 != $data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."2-".__('umerical value is abnormality.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_GPDIS2:
					if( !preg_match("/^[0-9]+$/", $data) || ( 0 < $datas[USCES_COL_ITEM_GPNUM2] && 1 > $data ) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."2-".__('rate is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_GPNUM3:
					if( !preg_match("/^[0-9]+$/", $data) || ($datas[USCES_COL_ITEM_GPNUM2] >= $data && 0 != $data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."3-".__('umerical value is abnormality.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_GPDIS3:
					if( !preg_match("/^[0-9]+$/", $data) || ( 0 < $datas[USCES_COL_ITEM_GPNUM3] && 1 > $data ) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('Business package discount', 'usces')."3-".__('rate is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_SHIPPING:
					if( !preg_match("/^[0-9]+$/", $data) || 9 < $data )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the shipment day is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_ITEM_DELIVERYMETHOD:
				case USCES_COL_ITEM_SHIPPINGCHARGE:
					break;
				case USCES_COL_ITEM_INDIVIDUALSCHARGE:
					if( !preg_match("/^[0-9]+$/", $data) || 1 < $data )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the postage individual charging is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_POST_TITLE:
				case USCES_COL_POST_CONTENT:
				case USCES_COL_POST_EXCERPT:
					break;
				case USCES_COL_POST_STATUS:
//20110421ysk start
					//$array17 = array('publish', 'future', 'draft', 'pending');
					$array17 = array('publish', 'future', 'draft', 'pending', 'private');
//20110421ysk end
					if( !in_array($data, $array17) || '' == $data )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the display status is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_POST_MODIFIED:
					if( 'future' == $datas[USCES_COL_POST_STATUS] && ('' == $data || '0000-00-00 00:00:00' == $data) ){
						if( preg_match($date_pattern, $data, $match) ){
							if( checkdate($match[2], $match[3], $match[1]) && 
										(0 < $match[4] && 24 > $match[4]) && 
										(0 < $match[5] && 60 > $match[5]) && 
										(0 < $match[6] && 60 > $match[6]) ){
								$logtemp .= "";
							}else{
								$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the schedule is abnormal.', 'usces')."\r\n";
							}
							
						}else{
							$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the schedule is abnormal.', 'usces')."\r\n";
						}
					}else if( '' != $data && '0000-00-00 00:00:00' != $data ){
						//if( !preg_match($date_pattern, $data, $match) || strtotime($data) === false || strtotime($data) == -1 )
						//	$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the schedule is abnormal.', 'usces')."\r\n";
						if(preg_match("/^[0-9]+$/", substr($data,0,4))) {//先頭4桁が数値のみ
							if(strtotime($data) === false)
								$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the schedule is abnormal.', 'usces')."\r\n";
						} else {
							$datetime = explode(' ', $data);
							$date_str = usces_dates_interconv($datetime[0]).' '.$datetime[1];
							if(strtotime($date_str) === false)
								$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the schedule is abnormal.', 'usces')."\r\n";
						}
					}
					break;
				case USCES_COL_CATEGORY:
					if( 0 == strlen($data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A category is non-input.', 'usces')."\r\n";
					break;
				case USCES_COL_POST_TAG:
					break;
				case USCES_COL_SKU_CODE:
					if( 0 == strlen($data) ){
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A SKU cord is non-input.', 'usces')."\r\n";
					}else if( $pre_code == $datas[USCES_COL_ITEM_CODE] ){
						$query = $wpdb->prepare("SELECT meta_id FROM $wpdb->postmeta 
												WHERE post_id = %d AND meta_key = %s", 
												$post_id, 
												'_isku_'.trim(mb_convert_encoding($data, 'UTF-8', 'SJIS'))
								);
						$meta_id = $wpdb->get_var( $query );
						if($meta_id !== NULL)
							$logtemp .= "No." . ($rows_num+1) . "\t".__('A SKU cord repeats.', 'usces')."\r\n";
					}
					break;
				case USCES_COL_SKU_NAME:
//20110315ysk start
					break;
				case USCES_COL_SKU_CPRICE:
					if( 0 < strlen($data) and !preg_match("/^\d$|^\d+\.?\d+$/", $data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the price is abnormal.', 'usces')."\r\n";
					break;
//20110315ysk end
				case USCES_COL_SKU_PRICE:
//20110315ysk start
					//if( !preg_match("/^[0-9]+$/", $data) || 0 == strlen($data) )
					if( !preg_match("/^\d$|^\d+\.?\d+$/", $data) || 0 == strlen($data) )
//20110315ysk end
						$logtemp .= "No." . ($rows_num+1) . "\t".__('通常価格の値が異常です', 'usces')."\r\n";
					break;
				case USCES_COL_SKU_ZAIKONUM:
//20110315ysk start
					if( 0 < strlen($data) and !preg_match("/^[0-9]+$/", $data) )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('在庫数の値が異常です', 'usces')."\r\n";
//20110315ysk end
					break;
				case USCES_COL_SKU_ZAIKO:
					if( !preg_match("/^[0-9]+$/", $data) || 4 < $data )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('A value of the stock status is abnormal.', 'usces')."\r\n";
					break;
				case USCES_COL_SKU_UNIT:
					break;
				case USCES_COL_SKU_GPTEKIYO:
					if( !preg_match("/^[0-9]+$/", $data) || 1 < $data )
						$logtemp .= "No." . ($rows_num+1) . "\t".__('The value of the duties pack application is abnormal.', 'usces')."\r\n";
					break;
			}
		}
		$opnum = ceil((count($datas) - $min_field_num) / 4);
		for($i=0; $i<$opnum; $i++){
			for($o=1; $o<=4; $o++){
				$key = ($min_field_num-1)+$o+($i*4);
				if( isset($datas[$key]) ){
					$value = trim($datas[$key]);
				}else{
					$value = NULL;
				}
				switch($o){
					case 1:
//						if( isset($datas[$key]) && 0 == strlen($datas[$key]) )
//							$logtemp .= "No." . ($rows_num+1) . "\t" . __(sprintf('Option name of No.%s option is non-input.', ($i+1)), 'usces')."\r\n";
						break;
					case 2:
						if( $value != NULL && ((0 != (int)$value) and (1 != (int)$value) and (2 != (int)$value) and (5 != (int)$value)) )
							$logtemp .= "No." . ($rows_num+1) . "\t" . __(sprintf('Option-entry-field of No.%s option is abnormal.', ($i+1)), 'usces')."\r\n";
						break;
					case 3:
						if( $value != NULL && (!preg_match("/^[0-9]+$/", $value) || 1 < (int)$value) )
							$logtemp .= "No." . ($rows_num+1) . "\t" . __(sprintf('Option-required-item of No.%s option is abnormal.', ($i+1)), 'usces')."\r\n";
						break;
					case 4:
						if( ($value != NULL && $value == '') && (2 > $datas[($key-2)] && 0 < strlen($datas[($key-2)])) )
							$logtemp .= "No." . ($rows_num+1) . "\t" . __(sprintf('Option-select of No.%s option is non-input.', ($i+1)), 'usces')."\r\n";
						break;
				}
			}
		}
		if( 0 < strlen($logtemp) ){
			$err_num++;
			$log .= $logtemp;
//20110315ysk start
			//$pre_code = $datas[USCES_COL_ITEM_CODE];
//20110315ysk end
			continue;
		}
		
		//wp_posts data reg;
		$cdatas = array();
		$post_fields = array();
		$sku = array();
		$opt = array();
		$valstr = '';

		$mode = 'add';
		if($pre_code != $datas[USCES_COL_ITEM_CODE]) {
//20101207ysk start
			//$post_id = $usces->get_postIDbyCode($datas[USCES_COL_ITEM_CODE]);
			$query = $wpdb->prepare("SELECT meta.post_id FROM $wpdb->postmeta AS meta 
				INNER JOIN $wpdb->posts AS post ON meta.post_id = post.ID AND post.post_status <> 'trash' AND post.post_mime_type = 'item' 
				WHERE meta.meta_value = %s LIMIT 1", trim(mb_convert_encoding($datas[USCES_COL_ITEM_CODE], 'UTF-8', 'SJIS')));
			$post_id = $wpdb->get_var( $query );
//20101207ysk end
			if(!empty($post_id)) $mode = 'upd';
		}

		if( $pre_code != $datas[USCES_COL_ITEM_CODE] ){
		
			//add posts
			$query = "SHOW FIELDS FROM $wpdb->posts";
			$results = $wpdb->get_results( $query, ARRAY_A );
			if($mode == 'add') {
				foreach($results as $ind => $rows){
					$post_fields[] = $rows['Field'];
				}
			} elseif($mode == 'upd') {
				$post_fields[] = 'post_modified';
				$post_fields[] = 'post_modified_gmt';
				$post_fields[] = 'post_content';
				$post_fields[] = 'post_title';
				$post_fields[] = 'post_excerpt';
				$post_fields[] = 'post_status';
			}
			foreach($post_fields as $key){
				switch( $key ){
					case 'ID':
						break;
					case 'post_author':
						$cdatas[$key] = 1;
						break;
					case 'post_date':
					case 'post_modified':
						$data = $datas[USCES_COL_POST_MODIFIED];
						if( $data == '' || $data == '0000-00-00 00:00:00' ){
							$cdatas[$key] = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
						}else{
							//$cdatas[$key] = $data;

							if(preg_match("/^[0-9]+$/", substr($data,0,4))) {//先頭4桁が数値のみ
								$cdatas[$key] = $data;
							} else {
								$datetime = explode(' ', $data);
								$date_str = usces_dates_interconv( $datetime[0] ).' '.$datetime[1];
								$cdatas[$key] = $date_str;
							}
						}
						break;
					case 'post_date_gmt':
					case 'post_modified_gmt':
						$data = $datas[USCES_COL_POST_MODIFIED];
						if( $data == '' || $data == '0000-00-00 00:00:00' ){
							$cdatas[$key] = gmdate('Y-m-d H:i:s');
						}else{
							//$cdatas[$key] = gmdate('Y-m-d H:i:s', strtotime($data));
							if(preg_match("/^[0-9]+$/", substr($data,0,4))) {//先頭4桁が数値のみ
								$cdatas[$key] = gmdate('Y-m-d H:i:s', strtotime($data));
							} else {
								$datetime = explode(' ', $data);
								$date_str = usces_dates_interconv( $datetime[0] ).' '.$datetime[1];
								$cdatas[$key] = gmdate('Y-m-d H:i:s', strtotime($date_str));
							}
						}
						break;
					case 'post_content':
						$cdatas[$key] = trim(mb_convert_encoding($datas[USCES_COL_POST_CONTENT], 'UTF-8', 'SJIS'));
						break;
					case 'post_title':
						$cdatas[$key] = trim(mb_convert_encoding($datas[USCES_COL_POST_TITLE], 'UTF-8', 'SJIS'));
						break;
					case 'post_excerpt':
						$cdatas[$key] = trim(mb_convert_encoding($datas[USCES_COL_POST_EXCERPT], 'UTF-8', 'SJIS'));
						break;
					case 'post_status':
						$cdatas[$key] = $datas[USCES_COL_POST_STATUS];
						break;
					case 'comment_status':
					case 'ping_status':
						$cdatas[$key] = 'close';
						break;
					case 'post_password':
					case 'post_name':
					case 'to_ping':
					case 'pinged':
					case 'post_content_filtered':
					case 'guid':
						$cdatas[$key] = '';
						break;
					case 'post_parent':
					case 'menu_order':
					case 'comment_count':
						$cdatas[$key] = 0;
						break;
					case 'post_type':
						$cdatas[$key] = 'post';
						break;
					case 'post_mime_type':
						$cdatas[$key] = 'item';
						break;
					default:
						$cdatas[$key] = '';
				}
			}
			if($mode == 'add') {
				$wpdb->insert( $wpdb->posts, $cdatas );
				$post_id = $wpdb->insert_id;
				if( $post_id == NULL ){
					$err_num++;
					$log .= "No." . ($rows_num+1) . "\t".__('The data were not registered with a database.', 'usces')."\r\n";
					$pre_code = $datas[USCES_COL_ITEM_CODE];
					continue;
				}
			} elseif($mode == 'upd') {
				$ids['ID'] = $post_id;
				$dbres = $wpdb->update( $wpdb->posts, $cdatas, $ids );
				if( $dbres === false ) {
					$err_num++;
					$log .= "No." . ($rows_num+1) . "\t".__('The data were not registered with a database.', 'usces')."\r\n";
					$pre_code = $datas[USCES_COL_ITEM_CODE];
					continue;
				}
				$query = $wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE post_id = %d", $post_id);
				$dbres = $wpdb->query( $query );
				if( $dbres === false ) {
					$err_num++;
					$log .= "No." . ($rows_num+1) . "\t".__('The data were not registered with a database.', 'usces')."\r\n";
					$pre_code = $datas[USCES_COL_ITEM_CODE];
					continue;
				}
				$query = $wpdb->prepare("DELETE FROM $wpdb->term_relationships WHERE object_id = %d", $post_id);
				$dbres = $wpdb->query( $query );
				if( $dbres === false ) {
					$err_num++;
					$log .= "No." . ($rows_num+1) . "\t".__('The data were not registered with a database.', 'usces')."\r\n";
					$pre_code = $datas[USCES_COL_ITEM_CODE];
					continue;
				}
				$query = "SELECT term_taxonomy_id, COUNT(*) AS ct FROM $wpdb->term_relationships GROUP BY term_taxonomy_id";
				$relation_data = $wpdb->get_results( $query, ARRAY_A );
				foreach((array)$relation_data as $relation_rows) {
					$term_taxonomy_ids['term_taxonomy_id'] = $relation_rows['term_taxonomy_id'];
					$term_taxonomy_updatas['count'] = $relation_rows['ct'];
					$dbres = $wpdb->update( $wpdb->term_taxonomy, $term_taxonomy_updatas, $term_taxonomy_ids );
					if( $dbres === false ) {
						$err_num++;
						$log .= "No." . ($rows_num+1) . "\t".__('The data were not registered with a database.', 'usces')."\r\n";
						$pre_code = $datas[USCES_COL_ITEM_CODE];
						continue;
					}
				}
			}

			//add postmeta
			$itemDeliveryMethod = explode(';',  $datas[USCES_COL_ITEM_DELIVERYMETHOD]);
			$valstr .= '(' . $post_id . ", '_itemCode','" . mysql_real_escape_string(trim(mb_convert_encoding($datas[USCES_COL_ITEM_CODE], 'UTF-8', 'SJIS'))) . "'),";
			$valstr .= '(' . $post_id . ", '_itemName','" . mysql_real_escape_string(trim(mb_convert_encoding($datas[USCES_COL_ITEM_NAME], 'UTF-8', 'SJIS'))) . "'),";
			$valstr .= '(' . $post_id . ", '_itemRestriction','" . $datas[USCES_COL_ITEM_RESTRICTION] . "'),";
			$valstr .= '(' . $post_id . ", '_itemPointrate','" . $datas[USCES_COL_ITEM_POINTRATE] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpNum1','" . $datas[USCES_COL_ITEM_GPNUM1] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpDis1','" . $datas[USCES_COL_ITEM_GPDIS1] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpNum2','" . $datas[USCES_COL_ITEM_GPNUM2] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpDis2','" . $datas[USCES_COL_ITEM_GPDIS2] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpNum3','" . $datas[USCES_COL_ITEM_GPNUM3] . "'),";
			$valstr .= '(' . $post_id . ", '_itemGpDis3','" . $datas[USCES_COL_ITEM_GPDIS3] . "'),";
			$meta_key = '_isku_' . trim(mb_convert_encoding($datas[USCES_COL_SKU_CODE], 'UTF-8', 'SJIS'));
			$sku['cprice'] = $datas[USCES_COL_SKU_CPRICE];
			$sku['price'] = $datas[USCES_COL_SKU_PRICE];
			$sku['zaikonum'] = $datas[USCES_COL_SKU_ZAIKONUM];
			$sku['zaiko'] = $datas[USCES_COL_SKU_ZAIKO];
			$sku['disp'] = trim(mb_convert_encoding($datas[USCES_COL_SKU_NAME], 'UTF-8', 'SJIS'));
			$sku['unit'] = trim(mb_convert_encoding($datas[USCES_COL_SKU_UNIT], 'UTF-8', 'SJIS'));
			$sku['gptekiyo'] = $datas[USCES_COL_SKU_GPTEKIYO];
			$valstr .= '(' . $post_id . ", '".mysql_real_escape_string($meta_key)."', '" . mysql_real_escape_string(serialize($sku)) . "'),";
			
			for($i=0; $i<$opnum; $i++){
				$opflg = true;
				$opt = array();
				for($o=1; $o<=4; $o++){
					$key = ($min_field_num-1)+$o+($i*4);
//					if( !isset($datas[$key]) ){
//						break 2;
//					}
					if( $o === 1 && $datas[$key] == '' ){
						$opflg = false;
						break 1;
					}
					switch($o){
						case 1:
							$ometa_key = '_iopt_' . trim(mb_convert_encoding($datas[$key], 'UTF-8', 'SJIS'));
							break;
						case 2:
							$opt['means'] = (int)$datas[$key];
							break;
						case 3:
							$opt['essential'] = (int)$datas[$key];
							break;
						case 4:
							if( !empty($datas[$key]) ) {
								$opt['value'][0] = str_replace(';', "\n", trim(mb_convert_encoding($datas[$key], 'UTF-8', 'SJIS')));
							}else{
								$opt['value'][0] = "";
							}
							break;
					}
				}
				if( $opflg == true )
					$valstr .= '(' . $post_id . ", '".mysql_real_escape_string($ometa_key)."', '" . mysql_real_escape_string(maybe_serialize($opt)) . "'),";
			}
//			print_r($valstr);
			$valstr = rtrim($valstr, ',');
			$query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES $valstr";
			$dbres = mysql_query($query) or die(mysql_error());

			//add term_relationships, edit term_taxonomy
			//category
			$categories = explode(';', $datas[USCES_COL_CATEGORY]);
			foreach((array)$categories as $category){
				$query = $wpdb->prepare("SELECT term_taxonomy_id FROM $wpdb->term_taxonomy 
										WHERE term_id = %d", $category);
				$term_taxonomy_id = $wpdb->get_var( $query );
				if($term_taxonomy_id == NULL) continue;

				$query = $wpdb->prepare("INSERT INTO $wpdb->term_relationships 
								(object_id, term_taxonomy_id, term_order) VALUES 
								(%d, %d, 0)", 
								$post_id, $term_taxonomy_id
						);
				$dbres = $wpdb->query($query);
				if( !$dbres ) continue;
				
				$query = $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->term_relationships 
										WHERE term_taxonomy_id = %d", $term_taxonomy_id);
				$tct = $wpdb->get_var( $query );
				
				$query = $wpdb->prepare("UPDATE $wpdb->term_taxonomy SET count = %d 
								WHERE term_taxonomy_id = %d", 
								$tct, $term_taxonomy_id
						);
				$dbres = $wpdb->query($query);
			}
			//tag
			$tags = explode(';', trim(mb_convert_encoding($datas[USCES_COL_POST_TAG], 'UTF-8', 'SJIS')));
			wp_set_object_terms($post_id, (array)$tags, 'post_tag');
			
			if($mode == 'add') {
				//edit posts
				$ids['ID'] = $post_id;
				$updatas['post_name'] = $post_id;
				$updatas['guid'] = get_option('home') . '?p=' . $post_id;
				$wpdb->update( $wpdb->posts, $updatas, $ids );
			}
			
		}else{
			$valstr = '';
			$meta_key = '_isku_' . trim(mb_convert_encoding($datas[USCES_COL_SKU_CODE], 'UTF-8', 'SJIS'));
			$sku['cprice'] = $datas[USCES_COL_SKU_CPRICE];
			$sku['price'] = $datas[USCES_COL_SKU_PRICE];
			$sku['zaikonum'] = $datas[USCES_COL_SKU_ZAIKONUM];
			$sku['zaiko'] = $datas[USCES_COL_SKU_ZAIKO];
			$sku['disp'] = trim(mb_convert_encoding($datas[USCES_COL_SKU_NAME], 'UTF-8', 'SJIS'));
			$sku['unit'] = trim(mb_convert_encoding($datas[USCES_COL_SKU_UNIT], 'UTF-8', 'SJIS'));
			$sku['gptekiyo'] = $datas[USCES_COL_SKU_GPTEKIYO];
			$valstr .= '(' . $post_id . ", '".mysql_real_escape_string($meta_key)."', '" . mysql_real_escape_string(serialize($sku)) . "'),";
			
			$valstr = rtrim($valstr, ',');
			$query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES $valstr";
			$dbres = mysql_query($query) or die(mysql_error());
		
		}


		$comp_num++;
		$pre_code = $datas[USCES_COL_ITEM_CODE];
	}
	
	flock($fpi, LOCK_EX);
	fputs($fpi, mb_convert_encoding($log, 'SJIS', 'UTF-8'));
	flock($fpi, LOCK_UN);
	fclose($fpo);
	fclose($fpi);

	$res['status'] = 'success';
	$res['message'] = __(sprintf('%2$s of %1$s lines registration completion, %3$s lines error.',$total_num,$comp_num,$err_num), 'usces');
	return $res;
}
endif;

//20101111ysk start
// item list download
if( !function_exists('usces_download_item_list') ):
function usces_download_item_list() {
	require_once( USCES_PLUGIN_DIR . "/classes/itemList.class.php" );
	global $wpdb, $usces;

	$ext = $_REQUEST['ftype'];
	if($ext == 'xls') {//TSV
		$table_h = "";
		$table_f = "";
		$tr_h = "";
		$tr_f = "";
		$th_h1 = '"';
		$th_h = "\t".'"';
		$th_f = '"';
		$td_h1 = '"';
		$td_h = "\t".'"';
		$td_f = '"';
		$sp = ";";
		$lf = "\n";
	} elseif($ext == 'csv') {//CSV
		$table_h = "";
		$table_f = "";
		$tr_h = "";
		$tr_f = "";
//20110201ysk start
		//$th_h1 = "";
		$th_h1 = '"';
		//$th_h = ",";
		$th_h = ',"';
		//$th_f = "";
		$th_f = '"';
		//$td_h1 = "";
		$td_h1 = '"';
		//$td_h = ",";
		$td_h = ',"';
		//$td_f = "";
		$td_f = '"';
//20110201ysk end
		$sp = ";";
		$lf = "\n";
	} else {
		exit();
	}

	//==========================================================================
	$usces_opt_item = unserialize(get_option('usces_opt_item'));
	$usces_opt_item['chk_header'] = (isset($_REQUEST['chk_header'])) ? 1 : 0;
	$usces_opt_item['ftype_item'] = $ext;
	update_option('usces_opt_item', serialize($usces_opt_item));
	//==========================================================================

	$tableName = $wpdb->posts;
	if( USCES_MYSQL_VERSION >= 5 ) {
		$arr_column = array(
					__('item code', 'usces') => 'item_code', 
					__('item name', 'usces') => 'item_name', 
					__('SKU code', 'usces') => 'sku_key', 
					__('selling price', 'usces') => 'price', 
					__('stock', 'usces') => 'zaiko_num', 
					__('stock status', 'usces') => 'zaiko', 
					__('Categories', 'usces') => 'category', 
					__('display status', 'usces') => 'display_status');
	} else {
		$arr_column = array(
					__('item code', 'usces') => 'item_code', 
					__('page title', 'usces') => 'post_title', 
					__('SKU code', 'usces') => 'sku_key', 
					__('selling price', 'usces') => 'price', 
					__('stock', 'usces') => 'zaiko_num', 
					__('stock status', 'usces') => 'zaiko', 
					__('Categories', 'usces') => 'category', 
					__('display status', 'usces') => 'display_status');
	}

	$DT = new dataList($tableName, $arr_column);
//20101202ysk start
	$DT->pageLimit = 'off';
//20101202ysk end
	$res = $DT->MakeTable();
	$rows = $DT->rows;

	//==========================================================================
	$line = $table_h;
	if($usces_opt_item['chk_header'] == 1) {
		$line .= $tr_h;
		$line .= $th_h1.__('item code', 'usces').$th_f;
		$line .= $th_h.__('item name', 'usces').$th_f;
		$line .= $th_h.__('Limited amount for purchase', 'usces').$th_f;
		$line .= $th_h.__('Percentage of points', 'usces').$th_f;
		$line .= $th_h.__('Business package discount', 'usces').'1-'.__('num', 'usces').$th_f.$th_h.__('Business package discount', 'usces').'1-'.__('rate', 'usces').$th_f;
		$line .= $th_h.__('Business package discount', 'usces').'2-'.__('num', 'usces').$th_f.$th_h.__('Business package discount', 'usces').'2-'.__('rate', 'usces').$th_f;
		$line .= $th_h.__('Business package discount', 'usces').'3-'.__('num', 'usces').$th_f.$th_h.__('Business package discount', 'usces').'3-'.__('rate', 'usces').$th_f;
		$line .= $th_h.__('estimated shipping date', 'usces').$th_f;
		$line .= $th_h.__('shipping option', 'usces').$th_f;
		$line .= $th_h.__('Shipping', 'usces').$th_f;
		$line .= $th_h.__('Postage individual charging', 'usces').$th_f;
		$line .= $th_h.__('Title', 'usces').$th_f;
		$line .= $th_h.__('explanation', 'usces').$th_f;
		$line .= $th_h.__('excerpt', 'usces').$th_f;
		$line .= $th_h.__('display status', 'usces').$th_f;
		$line .= $th_h.__('Publish on:').$th_f;
		$line .= $th_h.__('Categories', 'usces').$th_f;
		$line .= $th_h.__('tag', 'usces').$th_f;
		$line .= $th_h.__('SKU code', 'usces').$th_f;
		$line .= $th_h.__('SKU display name ', 'usces').$th_f;
		$line .= $th_h.__('normal price', 'usces').$th_f;
		$line .= $th_h.__('Sale price', 'usces').$th_f;
		$line .= $th_h.__('stock', 'usces').$th_f;
		$line .= $th_h.__('stock status', 'usces').$th_f;
		$line .= $th_h.__('unit', 'usces').$th_f;
		$line .= $th_h.__('Apply business package', 'usces').$th_f;
		$line .= $th_h.__('option name', 'usces').$th_f.$th_h.__('Field type', 'usces').$th_f.$th_h.__('Required', 'usces').$th_f.$th_h.__('selected amount', 'usces').$th_f;
		$line .= $tr_f.$lf;
	}
	//==========================================================================
	foreach((array)$rows as $array) {
		$post_id = $array['ID'];
		$post = get_post($post_id);

		$line_item = $td_h1.$usces->getItemCode($post_id).$td_f;
		$line_item .= $td_h.usces_entity_decode($usces->getItemName($post_id), $ext).$td_f;
		$line_item .= $td_h.$usces->getItemRestriction($post_id).$td_f;
		$line_item .= $td_h.$usces->getItemPointrate($post_id).$td_f;
		$line_item .= $td_h.$usces->getItemGpNum1($post_id).$td_f.$td_h.$usces->getItemGpDis1($post_id).$td_f;
		$line_item .= $td_h.$usces->getItemGpNum2($post_id).$td_f.$td_h.$usces->getItemGpDis2($post_id).$td_f;
		$line_item .= $td_h.$usces->getItemGpNum3($post_id).$td_f.$td_h.$usces->getItemGpDis3($post_id).$td_f;
		$line_item .= $td_h.$usces->getItemShipping($post_id).$td_f;

		$delivery_method = '';
		$itemDeliveryMethod = $usces->getItemDeliveryMethod($post_id);
		foreach((array)$itemDeliveryMethod as $k => $v) {
			$delivery_method .= $v.$sp;
		}
		$delivery_method = rtrim($delivery_method, $sp);
		$line_item .= $td_h.$delivery_method.$td_f;

		$line_item .= $td_h.$usces->getItemShippingCharge($post_id).$td_f;
		$line_item .= $td_h.$usces->getItemIndividualSCharge($post_id).$td_f;

		$line_item .= $td_h.usces_entity_decode($post->post_title, $ext).$td_f;
		$line_item .= $td_h.usces_entity_decode($post->post_content, $ext).$td_f;
		$line_item .= $td_h.usces_entity_decode($post->post_excerpt, $ext).$td_f;
		$line_item .= $td_h.$array['post_status'].$td_f;
		//if($ext == 'xls') {//TSV
		//	$line_item .= "\t".'="'.$post->post_modified.'"';
		//} elseif($ext == 'csv') {//CSV
		//	$line_item .= ",".'="'.$post->post_modified.'"';
		//}
//20110421ysk start
		//$line_item .= $td_h.$post->post_modified.$td_f;
		$line_item .= $td_h.$post->post_date.$td_f;
//20110421ysk end

		$category = '';
		$cat_ids = wp_get_post_categories($post_id);
		if(!empty($cat_ids)) {
			foreach($cat_ids as $id) {
				$category .= $id.$sp;
			}
			$category = rtrim($category, $sp);
		}
		$line_item .= $td_h.$category.$td_f;

		$tag = '';
		$tags_ob = wp_get_object_terms($post_id, 'post_tag');
		foreach($tags_ob as $ob) {
			$tag .= $ob->name.$sp;
		}
		$tag = rtrim($tag, $sp);
		$line_item .= $td_h.$tag.$td_f;

		$line_options = '';
		$option_meta = has_item_option_meta($post_id);
		foreach($option_meta as $option) {
			$option_value = maybe_unserialize($option['meta_value']);
			$value = '';
			if(is_array($option_value['value'])) {
				foreach($option_value['value'] as $k => $v) {
					$values = explode("\n", $v);
					foreach($values as $val) {
						$value .= $val.$sp;
					}
				}
				$value = rtrim($value, $sp);
			} else {
				$value = $option_value['value'];
			}

			$line_options .= $td_h.usces_entity_decode(substr($option['meta_key'], 6), $ext).$td_f;
			$line_options .= $td_h.$option_value['means'].$td_f;
			$line_options .= $td_h.$option_value['essential'].$td_f;
			$line_options .= $td_h.usces_entity_decode($value, $ext).$td_f;
		}

		$sku_meta = has_item_sku_meta($post_id);
		foreach($sku_meta as $sku) {
			$sku_value = maybe_unserialize($sku['meta_value']);

			$line_sku = $td_h.substr($sku['meta_key'], 6).$td_f;
			$line_sku .= $td_h.usces_entity_decode($sku_value['disp'], $ext).$td_f;
			$line_sku .= $td_h.$sku_value['cprice'].$td_f;
			$line_sku .= $td_h.$sku_value['price'].$td_f;
			$line_sku .= $td_h.$sku_value['zaikonum'].$td_f;
			$line_sku .= $td_h.$sku_value['zaiko'].$td_f;
			$line_sku .= $td_h.usces_entity_decode($sku_value['unit'], $ext).$td_f;
			$line_sku .= $td_h.$sku_value['gptekiyo'].$td_f;

			$line .= $tr_h.$line_item.$line_sku.$line_options.$tr_f.$lf;
		}
	}
	$line .= $table_f.$lf;
	//==========================================================================

	if($ext == 'xls') {
		header("Content-Type: application/vnd.ms-excel; charset=Shift-JIS");
	} elseif($ext == 'csv') {
		header("Content-Type: application/octet-stream");
	}
	header("Content-Disposition: attachment; filename=usces_item_list.".$ext);
	mb_http_output('pass');
	print(mb_convert_encoding($line, "SJIS-win", "UTF-8"));
	exit();
}
endif;
//20101111ysk end

}
?>