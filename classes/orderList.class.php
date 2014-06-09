<?php
class dataList
{
	var $table;			//テーブル名
	var $rows;			//データ
	var $action;		//アクション
	var $startRow;		//表示開始行番号
	var $maxRow;		//最大表示行数
	var $currentPage;	//現在のページNo
	var $firstPage;		//最初のページNo
	var $previousPage;	//前のページNo
	var $nextPage;		//次のページNo
	var $lastPage;		//最終ページNo
	var $naviMaxButton;	//ページネーション・ナビのボタンの数
	var $dataTableNavigation;	//ナヴィゲーションhtmlコード
	var $arr_period;	//表示データ期間
	var $arr_search;	//サーチ条件
	var $searchSql;		//簡易絞込みSQL
	var $searchSkuSql;	//SKU絞り込み
	var $searchSwitchStatus;	//サーチ表示スイッチ
	var $columns;		//データカラム
	var $sortColumn;	//現在ソート中のフィールド
	var $sortOldColumn;
	var $sortSwitchs;	//各フィールド毎の昇順降順スイッチ
	var $userHeaderNames;	//ユーザー指定のヘッダ名
	var $action_status, $action_message;
//20101202ysk start
	var $pageLimit;		//ページ制限
//20101202ysk end
	var $management_status;	//処理ステータス
	var $selectSql;
	var $joinTableSql;

	//Constructor
	function dataList($tableName, $arr_column)
	{
		$this->table = $tableName;
		$this->columns = $arr_column;
		$this->rows = array();

		$this->maxRow = 30;
		$this->naviMaxButton = 11;
		$this->firstPage = 1;
		$this->action_status = 'none';
		$this->action_message = '';
//20101202ysk start
		$this->pageLimit = 'on';
//20101202ysk end
		$this->selectSql = '';
		$this->joinTableSql = '';

		$this->SetParamByQuery();

		$arr_period = array(__('This month', 'usces'), __('Last month', 'usces'), __('The past one week', 'usces'), __('Last 30 days', 'usces'), __('Last 90days', 'usces'), __('All', 'usces'));
		$this->arr_period = apply_filters( 'usces_filter_order_list_arr_period', $arr_period, $this );

		$management_status = array(
			'duringorder' => __('temporaly out of stock', 'usces'),
			'cancel' => __('Cancel', 'usces'),
			'completion' => __('It has sent it out.', 'usces'),
			'estimate' => __('An estimate', 'usces'),
			'adminorder' => __('Management of Note', 'usces'),
			'continuation' => __('Continuation', 'usces'),
			'termination' => __('Termination', 'usces')
			);
		$this->management_status = apply_filters( 'usces_filter_management_status', $management_status, $this );

		$this->SetSelects();
		$this->SetJoinTables();
	}

	function SetSelects()
	{
		$status_sql = '';
		foreach( $this->management_status as $status_key => $status_name ) {
			$status_sql .= " WHEN LOCATE('".$status_key."', order_status) > 0 THEN '".$status_name."'";
		}

		$select = array(
			"ID", 
			"meta.meta_value AS deco_id", 
			"DATE_FORMAT(order_date, '%Y-%m-%d %H:%i') AS date", 
			"mem_id", 
			"CONCAT(order_name1, ' ', order_name2) AS name", 
			"order_pref AS pref", 
			"order_delivery_method AS delivery_method", 
			"(order_item_total_price - order_usedpoint + order_discount + order_shipping_charge + order_cod_fee + order_tax) AS total_price", 
			"order_payment_name AS payment_name", 
			"CASE WHEN LOCATE('noreceipt', order_status) > 0 THEN '".__('unpaid', 'usces')."' 
				 WHEN LOCATE('receipted', order_status) > 0 THEN '".__('payment confirmed', 'usces')."' 
				 WHEN LOCATE('pending', order_status) > 0 THEN '".__('Pending', 'usces')."' 
				 ELSE '&nbsp;' 
			END AS receipt_status", 
			"CASE {$status_sql} 
				 ELSE '".__('new order', 'usces')."' 
			END AS order_status", 
			"order_modified"
		);
		$this->selectSql = apply_filters( 'usces_filter_order_list_sql_select', $select, $status_sql, $this );
	}

	function SetJoinTables()
	{
		global $wpdb;
		$meta_table = $wpdb->prefix.'usces_order_meta';
		$ordercart_table = $wpdb->prefix.'usces_ordercart';
		$ordercartmeta_table = $wpdb->prefix.'usces_ordercart_meta';
		$join_table = array(
			" LEFT JOIN {$meta_table} AS meta ON ID = meta.order_id AND meta.meta_key = 'dec_order_id'"." \n",
			" LEFT JOIN {$ordercart_table} AS cart ON ID = cart.order_id"." \n"
		);
		$this->joinTableSql = apply_filters( 'usces_filter_order_list_sql_jointable', $join_table, $meta_table, $this );
	}

	function MakeTable()
	{
		$this->SetParam();

		switch ($this->action){

			case 'searchIn':
				$this->SearchIn();
				$res = $this->GetRows();
				break;

			case 'searchOut':
				$this->SearchOut();
				$res = $this->GetRows();
				break;

			case 'changeSort':
				$res = $this->GetRows();
				break;

			case 'changePage':
				$res = $this->GetRows();
				break;

			case 'collective_order_reciept':
				check_admin_referer( 'order_list', 'wc_nonce' );
				usces_all_change_order_reciept($this);
				$res = $this->GetRows();
				break;

			case 'collective_order_status':
				check_admin_referer( 'order_list', 'wc_nonce' );
				usces_all_change_order_status($this);
				$res = $this->GetRows();
				break;

			case 'collective_delete':
				check_admin_referer( 'order_list', 'wc_nonce' );
				usces_all_delete_order_data($this);
				$this->SetTotalRow();
				$res = $this->GetRows();
				break;

			case 'refresh':
			default:
				$this->SetDefaultParam();
				$res = $this->GetRows();
				break;
		}

		$this->SetNavi();
		$this->SetHeaders();
		$this->SetSESSION();

		if($res){

			return TRUE;

		}else{
			return FALSE;
		}
	}

	//DefaultParam
	function SetDefaultParam()
	{
		unset($_SESSION[$this->table]);
		$this->startRow = 0;
		$this->currentPage = 1;
		if(isset($_SESSION[$this->table]['arr_search'])){
			$this->arr_search = $_SESSION[$this->table]['arr_search'];
		}else{
			$arr_search = array( 'period'=>'3', 'column'=>'', 'word'=>'', 'sku'=>'', 'skuword'=>'' );
			$this->arr_search = apply_filters( 'usces_filter_order_list_arr_search', $arr_search, $this );
		}
		if(isset($_SESSION[$this->table]['searchSwitchStatus'])){
			$this->searchSwitchStatus = $_SESSION[$this->table]['searchSwitchStatus'];
		}else{
			$this->searchSwitchStatus = 'OFF';
		}
		$this->searchSql = '';
		$this->searchSkuSql = '';
		$this->sortColumn = 'ID';
		foreach($this->columns as $value ){
			$this->sortSwitchs[$value] = 'DESC';
		}

		$this->SetTotalRow();
		//$this->SetSelectedRow();
	}

	function SetParam()
	{
		$this->startRow = ($this->currentPage-1) * $this->maxRow;
	}

	function SetParamByQuery()
	{
		if(isset($_REQUEST['changePage'])){

			$this->action = 'changePage';
			$this->currentPage = $_REQUEST['changePage'];
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->searchSwitchStatus = $_SESSION[$this->table]['searchSwitchStatus'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchSkuSql = $_SESSION[$this->table]['searchSkuSql'];
			$this->arr_search = $_SESSION[$this->table]['arr_search'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];

		}else if(isset($_REQUEST['changeSort'])){

			$this->action = 'changeSort';
			$this->sortOldColumn = $this->sortColumn;
			$this->sortColumn = $_REQUEST['changeSort'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->sortSwitchs[$this->sortColumn] = $_REQUEST['switch'];
			$this->currentPage = $_SESSION[$this->table]['currentPage'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchSkuSql = $_SESSION[$this->table]['searchSkuSql'];
			$this->arr_search = $_SESSION[$this->table]['arr_search'];
			$this->searchSwitchStatus = $_SESSION[$this->table]['searchSwitchStatus'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];

		} else if(isset($_REQUEST['searchIn'])){

			$this->action = 'searchIn';
			$this->arr_search['column'] = isset($_REQUEST['search']['column']) ? $_REQUEST['search']['column'] : '';
			$this->arr_search['sku'] = isset($_REQUEST['search']['sku']) ? $_REQUEST['search']['sku'] : '';
			$this->arr_search['word'] = isset($_REQUEST['search']['word']) ? $_REQUEST['search']['word'] : '';
			$this->arr_search['skuword'] = isset($_REQUEST['search']['skuword']) ? $_REQUEST['search']['skuword'] : '';
			$this->arr_search['period'] = isset($_REQUEST['search']['period']) ? (int)$_REQUEST['search']['period'] : 0;
			$this->searchSwitchStatus = isset($_REQUEST['searchSwitchStatus']) ? $_REQUEST['searchSwitchStatus'] : '';
			$this->currentPage = 1;
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];

		}else if(isset($_REQUEST['searchOut'])){

			$this->action = 'searchOut';
			$this->arr_search['column'] = '';
			$this->arr_search['word'] = '';
			$this->arr_search['sku'] = '';
			$this->arr_search['skuword'] = '';
			$this->arr_search['period'] = $_SESSION[$this->table]['arr_search']['period'];
			$this->searchSwitchStatus = isset($_REQUEST['searchSwitchStatus']) ? $_REQUEST['searchSwitchStatus'] : '';
			$this->currentPage = 1;
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];

		}else if(isset($_REQUEST['refresh'])){

			$this->action = 'refresh';
			$this->currentPage = $_SESSION[$this->table]['currentPage'];
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchSkuSql = $_SESSION[$this->table]['searchSkuSql'];
			$this->arr_search = $_SESSION[$this->table]['arr_search'];
			$this->searchSwitchStatus = $_SESSION[$this->table]['searchSwitchStatus'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];

		}else if(isset($_REQUEST['collective'])){

			$this->action = 'collective_' . $_POST['allchange']['column'];
			$this->currentPage = $_SESSION[$this->table]['currentPage'];
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchSkuSql = $_SESSION[$this->table]['searchSkuSql'];
			$this->arr_search = $_SESSION[$this->table]['arr_search'];
			$this->searchSwitchStatus = $_SESSION[$this->table]['searchSwitchStatus'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];

		}else{

			$this->action = 'default';
		}
	}

	//GetRows
	function GetRows()
	{
		global $wpdb;
		$where = $this->GetWhere();
		$order = ' ORDER BY `' . $this->sortColumn . '` ' . $this->sortSwitchs[$this->sortColumn];
		$order = apply_filters( 'usces_filter_order_list_get_orderby', $order, $this );

		$select = '';
		foreach( $this->selectSql as $value ) {
			$select .= $value.", ";
		}
		$select = rtrim( $select, ", " );
		$select .= ", item_name, item_code";
		$query = apply_filters( 'usces_filter_order_list_select', $select, $this);
		$join_table = '';
		foreach( $this->joinTableSql as $value ) {
			$join_table .= $value;
		}
		$query = "SELECT ".$select." \n"."FROM {$this->table} "."\n".$join_table.$where."\n".$order;
		$query = apply_filters( 'usces_filter_order_list_get_rows', $query, $this);
//usces_p($query);
		$wpdb->show_errors();

		$rows = $wpdb->get_results($query, ARRAY_A);
		$this->selectedRow = count($rows);
		if($this->pageLimit == 'off') {
			$this->rows = (array)$rows;
		} else {
			$this->rows = array_slice((array)$rows, $this->startRow, $this->maxRow);
		}

		return $this->rows;
	}

	function SetTotalRow()
	{
		global $wpdb;
		$query = "SELECT COUNT(ID) AS ct FROM {$this->table}".apply_filters( 'usces_filter_order_list_sql_where', '', $this );
		$query = apply_filters( 'usces_filter_order_list_set_total_row', $query, $this);
		$res = $wpdb->get_var($query);
		$this->totalRow = $res;
	}

	function GetWhere()
	{
		$str = '';
		$thismonth = date('Y-m-01 00:00:00');
		$lastmonth = date('Y-m-01 00:00:00', mktime(0, 0, 0, date('m')-1, 1, date('Y')));
		$lastweek = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), date('d')-7, date('Y')));
		$last30 = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), date('d')-30, date('Y')));
		$last90 = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), date('d')-90, date('Y')));
		switch ( $this->arr_search['period'] ) {
			case 0:
				$where = " WHERE order_date >= '{$thismonth}' ";
				break;
			case 1:
				$where = " WHERE order_date >= '{$lastmonth}' AND order_date < '{$thismonth}' ";
				break;
			case 2:
				$where = " WHERE order_date >= '{$lastweek}' ";
				break;
			case 3:
				$where = " WHERE order_date >= '{$last30}' ";
				break;
			case 4:
				$where = " WHERE order_date >= '{$last90}' ";
				break;
			case 5:
				$where = "";
				break;
		}
		if( !WCUtils::is_blank($where) ){
			if( !WCUtils::is_blank($this->searchSkuSql) ){
				$where .= ' AND ' . $this->searchSkuSql;
			}
		}else{
			if( !WCUtils::is_blank($this->searchSkuSql) ){
				$where = ' WHERE ' . $this->searchSkuSql;
			}
		}
		$str = apply_filters( 'usces_filter_order_list_sql_where', $where, $this );
		
		$str .= " \n" . " GROUP BY `ID` ";
		
		$having = '';
		if( !WCUtils::is_blank($this->searchSql) ){
			$having = ' HAVING ' . $this->searchSql;
		}
		$having = apply_filters( 'usces_filter_order_list_sql_having', $having, $this );
		
		if( !WCUtils::is_blank($having) ){
			$str .= $having;
		}

		return apply_filters( 'usces_filter_order_list_get_where', $str, $this );
	}

	function SearchIn()
	{
		switch ($this->arr_search['column']) {
			case 'ID':
				$column = 'ID';
				$this->searchSql = $column . ' = ' . (int)$this->arr_search['word']['ID'];
				break;
			case 'deco_id':
				$column = 'deco_id';
				$this->searchSql = $column . ' LIKE '."'%" . esc_sql($this->arr_search['word']['deco_id']) . "%'";
				break;
			case 'date':
				$column = 'date';
				$this->searchSql = $column . ' LIKE '."'%" . esc_sql($this->arr_search['word']['date']) . "%'";
				break;
			case 'mem_id':
				$column = 'mem_id';
				$this->searchSql = $column . ' = ' . (int)$this->arr_search['word']['mem_id'];
				break;
			case 'name':
				$column = 'name';
				$this->searchSql = $column . ' LIKE '."'%" . esc_sql($this->arr_search['word']['name']) . "%'";
				break;
			case 'order_modified':
				$column = 'order_modified';
				$this->searchSql = $column . ' LIKE '."'%" . esc_sql($this->arr_search['word']['order_modified']) . "%'";
				break;
			case 'pref':
				$column = 'pref';
				$this->searchSql = $column . " = '" . esc_sql($this->arr_search['word']['pref']) . "'";
				break;
			case 'delivery_method':
				$column = 'delivery_method';
				$this->searchSql = $column . " = '" . esc_sql($this->arr_search['word']['delivery_method']) . "'";
				break;
			case 'payment_name':
				$column = 'payment_name';
				$this->searchSql = $column . " = '" . esc_sql($this->arr_search['word']['payment_name']) . "'";
				break;
			case 'receipt_status':
				$column = 'receipt_status';
				$this->searchSql = $column . " = '" . esc_sql($this->arr_search['word']['receipt_status']) . "'";
				break;
			case 'order_status':
				$column = 'order_status';
				$this->searchSql = $column . " = '" . esc_sql($this->arr_search['word']['order_status']) . "'";
				break;
		}
		switch ($this->arr_search['sku']) {
			case 'item_code':
				$column = 'item_code';
				$this->searchSkuSql = $column . ' LIKE '."'%" . esc_sql($this->arr_search['skuword']['item_code']) . "%'";
				break;
			case 'item_name':
				$column = 'item_name';
				$this->searchSkuSql = $column . ' LIKE '."'%" . esc_sql($this->arr_search['skuword']['item_name']) . "%'";
				break;
		}
	}

	function SearchOut()
	{
		$this->searchSql = '';
		$this->searchSkuSql = '';
	}

	function SetNavi()
	{
		$this->lastPage = ceil($this->selectedRow / $this->maxRow);
		$this->previousPage = ($this->currentPage - 1 == 0) ? 1 : $this->currentPage - 1;
		$this->nextPage = ($this->currentPage + 1 > $this->lastPage) ? $this->lastPage : $this->currentPage + 1;

		for($i=0; $i<$this->naviMaxButton; $i++){
			if($i > $this->lastPage-1) break;
			if($this->lastPage <= $this->naviMaxButton) {
				$box[] = $i+1;
			}else{
				if($this->currentPage <= 6) {
					$label = $i + 1;
					$box[] = $label;
				}else{
					$label = $i + 1 + $this->currentPage - 6;
					$box[] = $label;
					if($label == $this->lastPage) break;
				}
			}
		}

		$html = '';
		$html .= '<ul class="clearfix">'."\n";
		$html .= '<li class="rowsnum">' . $this->selectedRow . ' / ' . $this->totalRow . ' ' . __('cases', 'usces') . '</li>' . "\n";
		if(($this->currentPage == 1) || ($this->selectedRow == 0)){
			$html .= '<li class="navigationStr">first&lt;&lt;</li>' . "\n";
			$html .= '<li class="navigationStr">prev&lt;</li>'."\n";
		}else{
			$html .= '<li class="navigationStr"><a href="' . site_url() . '/wp-admin/admin.php?page=usces_orderlist&changePage=1">first&lt;&lt;</a></li>' . "\n";
			$html .= '<li class="navigationStr"><a href="' . site_url() . '/wp-admin/admin.php?page=usces_orderlist&changePage=' . $this->previousPage . '">prev&lt;</a></li>'."\n";
		}
		if($this->selectedRow > 0) {
			for($i=0; $i<count($box); $i++){
				if($box[$i] == $this->currentPage){
					$html .= '<li class="navigationButtonSelected">' . $box[$i] . '</li>'."\n";
				}else{
					$html .= '<li class="navigationButton"><a href="' . site_url() . '/wp-admin/admin.php?page=usces_orderlist&changePage=' . $box[$i] . '">' . $box[$i] . '</a></li>'."\n";
				}
			}
		}
		if(($this->currentPage == $this->lastPage) || ($this->selectedRow == 0)){
			$html .= '<li class="navigationStr">&gt;next</li>'."\n";
			$html .= '<li class="navigationStr">&gt;&gt;last</li>'."\n";
		}else{
			$html .= '<li class="navigationStr"><a href="' . site_url() . '/wp-admin/admin.php?page=usces_orderlist&changePage=' . $this->nextPage . '">&gt;next</a></li>'."\n";
			$html .= '<li class="navigationStr"><a href="' . site_url() . '/wp-admin/admin.php?page=usces_orderlist&changePage=' . $this->lastPage . '">&gt;&gt;last</a></li>'."\n";
		}
		if($this->searchSwitchStatus == 'OFF'){
			$html .= '<li class="rowsnum"><a style="cursor:pointer;" id="searchVisiLink" onclick="toggleVisibility(\'searchBox\');">' . __('Show the Operation field', 'usces') . '</a>'."\n";
		}else{
			$html .= '<li class="rowsnum"><a style="cursor:pointer;" id="searchVisiLink" onclick="toggleVisibility(\'searchBox\');">' . __('hide the Operation field', 'usces') . '</a>'."\n";
		}

		$html .= '<li class="refresh"><a href="' . site_url() . '/wp-admin/admin.php?page=usces_orderlist&refresh">' . __('updates it to latest information', 'usces') . '</a></li>' . "\n";
		$html .= '</ul>'."\n";

		$this->dataTableNavigation = $html;
	}

	function SetSESSION()
	{
		$_SESSION[$this->table]['startRow'] = $this->startRow;		//表示開始行番号
		$_SESSION[$this->table]['sortColumn'] = $this->sortColumn;	//現在ソート中のフィールド
		$_SESSION[$this->table]['totalRow'] = $this->totalRow;		//全行数
		$_SESSION[$this->table]['selectedRow'] = $this->selectedRow;	//絞り込まれた行数
		$_SESSION[$this->table]['currentPage'] = $this->currentPage;	//現在のページNo
		$_SESSION[$this->table]['previousPage'] = $this->previousPage;	//前のページNo
		$_SESSION[$this->table]['nextPage'] = $this->nextPage;		//次のページNo
		$_SESSION[$this->table]['lastPage'] = $this->lastPage;		//最終ページNo
		$_SESSION[$this->table]['userHeaderNames'] = $this->userHeaderNames;//全てのフィールド
		$_SESSION[$this->table]['headers'] = $this->headers;//表示するヘッダ文字列
		$_SESSION[$this->table]['rows'] = $this->rows;			//表示する行オブジェクト
		$_SESSION[$this->table]['sortSwitchs'] = $this->sortSwitchs;	//各フィールド毎の昇順降順スイッチ
		$_SESSION[$this->table]['dataTableNavigation'] = $this->dataTableNavigation;
		$_SESSION[$this->table]['searchSql'] = $this->searchSql;
		$_SESSION[$this->table]['searchSkuSql'] = $this->searchSkuSql;
 		$_SESSION[$this->table]['arr_search'] = $this->arr_search;
		$_SESSION[$this->table]['searchSwitchStatus'] = $this->searchSwitchStatus;
		do_action( 'usces_action_order_list_set_session', $this );
	}

	function SetHeaders()
	{
		foreach ($this->columns as $key => $value){
			if($value == $this->sortColumn){
				if($this->sortSwitchs[$value] == 'ASC'){
					$str = __('[ASC]', 'usces');
					$switch = 'DESC';
				}else{
					$str = __('[DESC]', 'usces');
					$switch = 'ASC';
				}
				$this->headers[$value] = '<a href="' . site_url() . '/wp-admin/admin.php?page=usces_orderlist&changeSort=' . $value . '&switch=' . $switch . '"><span class="sortcolumn">' . $key . ' ' . $str . '</span></a>';
			}else{
				$switch = $this->sortSwitchs[$value];
				$this->headers[$value] = '<a href="' . site_url() . '/wp-admin/admin.php?page=usces_orderlist&changeSort=' . $value . '&switch=' . $switch . '"><span>' . $key . '</span></a>';
			}
		}
		//$this->headers = array_keys($this->columns);
	}

	function GetSearchs()
	{
		return $this->arr_search;
	}

	function GetListheaders()
	{
		return $this->headers;
	}

	function GetDataTableNavigation()
	{
		return $this->dataTableNavigation;
	}

	function set_action_status($status, $message)
	{
		$this->action_status = $status;
		$this->action_message = $message;
	}

	function get_action_status()
	{
		return $this->action_status;
	}

	function get_action_message()
	{
		return $this->action_message;
	}
}


?>