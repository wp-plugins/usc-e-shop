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
	var $searchSwitchStatus;	//サーチ表示スイッチ
	var $columns;		//データカラム
	var $sortColumn;	//現在ソート中のフィールド
	var $sortOldColumn;
	var $sortSwitchs;	//各フィールド毎の昇順降順スイッチ
	var $userHeaderNames;	//ユーザー指定のヘッダ名
//20101202ysk start
	var $pageLimit;		//ページ制限
//20101202ysk end
	
	//Constructor
	function dataList($tableName, $arr_column)
	{

		$this->table = $tableName;
		$this->columns = $arr_column;
		$this->rows = array();

		$this->maxRow = 30;
		$this->naviMaxButton = 11;
		$this->firstPage = 1;
//20101202ysk start
		$this->pageLimit = 'on';
//20101202ysk end

		$this->SetParamByQuery();

		$this->arr_period = array(__('This month', 'usces'), __('Last month', 'usces'), __('The past one week', 'usces'), __('Last 30 days', 'usces'), __('Last 90days', 'usces'), __('All', 'usces'));


	}

	function MakeTable()
	{

		$this->SetParam();
		
		switch ($this->action){
		
			case 'searchIn':
				$this->SearchIn();
				$this->SetSelectedRow();
				$res = $this->GetRows();
				break;
				
			case 'searchOut':
				$this->SearchOut();
				$this->SetSelectedRow();
				$res = $this->GetRows();
				break;
			
			case 'changeSort':
				$res = $this->GetRows();
				break;
			
			case 'changePage':
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
			$this->arr_search = array('period'=>'3', 'column'=>'', 'word'=>'');
		}
		if(isset($_SESSION[$this->table]['searchSwitchStatus'])){
			$this->searchSwitchStatus = $_SESSION[$this->table]['searchSwitchStatus'];
		}else{
			$this->searchSwitchStatus = 'OFF';
		}
		$this->searchSql =  '';
		$this->sortColumn = 'ID';
		foreach($this->columns as $value ){
			$this->sortSwitchs[$value] = 'DESC';
		}
//		$this->SetDefaultColumns();
		
		
//		if($optionValue = $this->GetOptions($this->table, 'userHeaderNames')){
//			$this->userHeaderNames = $optionValue;
//		}else{
//			for($i=0; $i<count($this->defaultColumns); $i++){
//				$headerKey = $this->defaultColumns[$i];
//				$this->userHeaderNames[$headerKey] = $this->defaultColumns[$i];
//			}
//			$this->SetOptions($this->table, 'userHeaderNames', $this->userHeaderNames);
//		}
//		if($optionValue = $this->GetOptions($this->table, 'displayColumns')){
//			$this->displayColumns = $optionValue;
//		}else{
//			$this->displayColumns = $this->defaultColumns;
//			$this->SetOptions($this->table, 'displayColumns', $this->displayColumns);
//		}
//		//フィールドのデータタイプ
//		if($optionValue = $this->GetOptions($this->table, 'columnTypes')){
//			$this->columnTypes = $optionValue;
//		}else{
//			for($i=1; $i<count($this->defaultColumns); $i++){
//				$headerKey = $this->defaultColumns[$i];
//				$this->columnTypes[$headerKey] = 0;
//			}
//			$this->SetOptions($this->table, 'columnTypes', $this->columnTypes);
//		}
//		//選択タイプ用ヴァリュー
//		if($optionValue = $this->GetOptions($this->table, 'valueLabels')){
//			$this->valueLabels = $optionValue;
//		}else{
//			for($i=1; $i<count($this->defaultColumns); $i++){
//				$headerKey = $this->defaultColumns[$i];
//				$this->valueLabels[$headerKey] = '';
//			}
//			$this->SetOptions($this->table, 'valueLabels', $this->valueLabels);
//		}
		
	
		$this->SetTotalRow();
		$this->SetSelectedRow();

	}
	
	function SetParam()
	{
		$this->startRow = ($this->currentPage-1) * $this->maxRow;
		//$this->totalRow = $_SESSION[$this->table]['totalRow'];
//		$this->defaultColumns = $_SESSION[$this->table]['defaultColumns'];
//		$this->listPatternRows = $_SESSION[$this->table]['listPatternRows'];
//		$this->searchSwitchStatus = $_SESSION[$this->table]['searchSwitchStatus'];
	}
	
	function SetParamByQuery()
	{
	
//		if(isset($_REQUEST['changeDispColumn'])){
//		
//			$this->action = 'changeDispColumn';
//			$this->displayColumns = $_POST['checkedColumns'];
//			$this->SetOptions($this->table, 'displayColumns', $this->displayColumns);
//			$getnames = $_POST['userHeaderNames'];
//			$coltypes = $_POST['columnTypes'];
//			$vallabels = $_POST['valueLabels'];
//			$this->SetDefaultColumns();
//			for($i=0; $i< count($this->defaultColumns); $i++){
//				$headerKey = $this->defaultColumns[$i];
//				if($getnames[$i] == ''){
//					$this->userHeaderNames[$headerKey] = $this->defaultColumns[$i];
//				}else{
//					$this->userHeaderNames[$headerKey] = htmlspecialchars($getnames[$i]);
//				}
//			}
//			$this->SetOptions($this->table, 'userHeaderNames', $this->userHeaderNames);
//			for($i=1; $i< count($this->defaultColumns); $i++){
//				$headerKey = $this->defaultColumns[$i];
//				if(($coltypes[$i-1] == 1 || $coltypes[$i-1] == 2) && $vallabels[$i-1] == ''){
//					$valstr = $this->GetSelectData($headerKey);
//					$this->valueLabels[$headerKey] = $valstr;
//				}else{
//					$this->valueLabels[$headerKey] = htmlspecialchars($vallabels[$i-1]);
//				}
//				$this->columnTypes[$headerKey] = htmlspecialchars($coltypes[$i-1]);
//			}
//			$this->SetOptions($this->table, 'columnTypes', $this->columnTypes);
//			$this->SetOptions($this->table, 'valueLabels', $this->valueLabels);
//
//			$this->currentPage = $_SESSION[$this->table]['currentPage'];
//			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
//			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
//			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
//			$this->searchSql = $_SESSION[$this->table]['searchSql'];
//			$this->searchs = $_SESSION[$this->table]['searchs'];
//			$this->totalRow = $_SESSION[$this->table]['totalRow'];
//			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];
//
//			
		if(isset($_REQUEST['changePage'])){
		
			$this->action = 'changePage';
			$this->currentPage = $_REQUEST['changePage'];
			
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->searchSwitchStatus = $_SESSION[$this->table]['searchSwitchStatus'];
//			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
//			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
//			$this->arr_search = $_SESSION[$this->table]['searchs'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];
//			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
//			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
		}else if(isset($_REQUEST['changeSort'])){
		
			$this->action = 'changeSort';
			$this->sortOldColumn = $this->sortColumn;
			$this->sortColumn = $_REQUEST['changeSort'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->sortSwitchs[$this->sortColumn] = $_REQUEST['switch'];
			
			$this->currentPage = $_SESSION[$this->table]['currentPage'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
//			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
//			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->arr_search = $_SESSION[$this->table]['arr_search'];
			$this->searchSwitchStatus = $_SESSION[$this->table]['searchSwitchStatus'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];
//			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
//			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
		} else if(isset($_REQUEST['searchIn'])){
		
			$this->action = 'searchIn';
			$this->arr_search['column'] = $_REQUEST['search']['column'];
			$this->arr_search['word'] = $_REQUEST['search']['word'];
			$this->arr_search['period'] = isset($_REQUEST['search']['period']) ? intval($_REQUEST['search']['period']) : 0;
			$this->searchSwitchStatus = $_REQUEST['searchSwitchStatus'];
			
			$this->currentPage = 1;
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
//			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
//			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
//			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
//			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
//			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
//			
		}else if(isset($_REQUEST['searchOut'])){
		
			$this->action = 'searchOut';
			$this->arr_search['column'] = '';
			$this->arr_search['word'] = '';
			$this->arr_search['period'] = $_SESSION[$this->table]['arr_search']['period'];
			$this->searchSwitchStatus = $_REQUEST['searchSwitchStatus'];
			
			$this->currentPage = 1;
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
//			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
//			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
//			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
//			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
//			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
//		}else if(isset($_REQUEST['changeListPattern'])){
//		
//			$this->action = 'changeListPattern';
//			$this->listPatternId = $_REQUEST['changeListPattern'];
//			setcookie($this->table.'listPatternId', $this->listPatternId, $this->expiration);;
//			
//			$this->currentPage = 1;
//			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
//			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
//			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
//			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
//			$this->searchSql = $_SESSION[$this->table]['searchSql'];
//			$this->searchs = $_SESSION[$this->table]['searchs'];
//			$this->totalRow = $_SESSION[$this->table]['totalRow'];
//			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
//			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
//			
//		}else if(isset($_POST['tableAction']) && ($_POST['tableAction'] == 'deletListMember')){
//		
//			$this->action = 'deletListMember';
//			
//			$this->currentPage = 1;
//			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
//			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
//			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
//			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
//			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
//			$this->searchSql = $_SESSION[$this->table]['searchSql'];
//			$this->searchs = $_SESSION[$this->table]['searchs'];
//			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
//			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
//			
//		}else if(isset($_REQUEST['uploadCSV'])){
//		
//			$this->action = 'uploadCSV';
//			
//			$this->currentPage = $_SESSION[$this->table]['currentPage'];
//			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
//			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
//			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
//			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
//			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
//			$this->searchSql = $_SESSION[$this->table]['searchSql'];
//			$this->searchs = $_SESSION[$this->table]['searchs'];
//			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
//			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
//			
//		}else if(isset($_REQUEST['tableAction']) && ($_POST['tableAction'] == 'downloadCSV')){
//		
//			$this->action = 'downloadCSV';
//			
//			$this->currentPage = $_SESSION[$this->table]['currentPage'];
//			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
//			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
//			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
//			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
//			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
//			$this->searchSql = $_SESSION[$this->table]['searchSql'];
//			$this->searchs = $_SESSION[$this->table]['searchs'];
//			$this->totalRow = $_SESSION[$this->table]['totalRow'];
//			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];
//			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
//			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
//			
		}else if(isset($_REQUEST['refresh'])){
		
			$this->action = 'refresh';

			$this->currentPage = $_SESSION[$this->table]['currentPage'];
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
//			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
//			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->arr_search = $_SESSION[$this->table]['arr_search'];
			$this->searchSwitchStatus = $_SESSION[$this->table]['searchSwitchStatus'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];
//			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
//			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
//		}else if(isset($_REQUEST['editMember']) && ($_POST['editMember'] == 'edit')){
//		
//			$this->action = 'editMember';
//			$this->editMemberId = $_POST['memberId'];
//			$this->currentPage = $_SESSION[$this->table]['currentPage'];
//			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
//			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
//			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
//			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
//			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
//			$this->searchSql = $_SESSION[$this->table]['searchSql'];
//			$this->searchs = $_SESSION[$this->table]['searchs'];
//			$this->totalRow = $_SESSION[$this->table]['totalRow'];
//			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
//			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
//			
//		}else if(isset($_REQUEST['editMember']) && ($_POST['editMember'] == 'add')){
//		
//			$this->action = 'addMember';
//			$this->editMemberId = $_POST['memberId'];
//			$this->currentPage = $_SESSION[$this->table]['currentPage'];
//			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
//			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
//			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
//			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
//			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
//			$this->searchSql = $_SESSION[$this->table]['searchSql'];
//			$this->searchs = $_SESSION[$this->table]['searchs'];
//			$this->totalRow = $_SESSION[$this->table]['totalRow'];
//			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
//			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
//			
//		}else if(isset($_REQUEST['getMemberData'])){
//		
//			$this->action = 'getMemberData';
//			$this->editMemberId = $_REQUEST['getMemberData'];
//
//			$this->currentPage = $_SESSION[$this->table]['currentPage'];
//			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
//			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
//			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
//			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
//			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
//			$this->searchSql = $_SESSION[$this->table]['searchSql'];
//			$this->searchs = $_SESSION[$this->table]['searchs'];
//			$this->totalRow = $_SESSION[$this->table]['totalRow'];
//			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];
//			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
//			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
//			
//		}else if(isset($_REQUEST['editListPattern'])){
//			
//			if($_REQUEST['editListPattern'] == 'add'){
//				$this->action = 'addListPattern';
//			}
//			$this->editListPatternName = $_POST['patternName'];
//			$this->editListPatternShikis = $_POST['shikis'];
//			$this->editListPatternFieldNames = $_POST['fieldNames'];
//			$this->editListPatternFieldDatas = $_POST['fieldDatas'];
//
//			$this->currentPage = $_SESSION[$this->table]['currentPage'];
//			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
//			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
//			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
//			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
//			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
//			$this->searchSql = $_SESSION[$this->table]['searchSql'];
//			$this->searchs = $_SESSION[$this->table]['searchs'];
//			$this->totalRow = $_SESSION[$this->table]['totalRow'];
//			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];
//			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
//			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
//			
//		}else if(isset($_REQUEST['tableAction']) && ($_POST['tableAction'] == 'rowButtonDelete')){
//			
//			$this->action = 'rowButtonDelete';
//			$this->tableValue = $_POST['tableValue'];
//
//			$this->currentPage = $_SESSION[$this->table]['currentPage'];
//			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
//			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
//			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
//			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
//			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
//			$this->searchSql = $_SESSION[$this->table]['searchSql'];
//			$this->searchs = $_SESSION[$this->table]['searchs'];
//			$this->totalRow = $_SESSION[$this->table]['totalRow'];
//			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];
//			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
//			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
//			
//		}else if(isset($_REQUEST['changeSearchSwitch'])){
//		
//			$this->action = 'changeSearchSwitch';
//			$this->searchSwitchStatus = $_REQUEST['changeSearchSwitch'];
//
//		}else if(isset($_REQUEST['changeListPatternAjax'])){
//		
//			$this->action = 'changeListPatternAjax';
//			$this->listPatternId = $_REQUEST['changeListPatternAjax'];
//			if(isset($_REQUEST['overlap'])){
//				$this->overlap = $_REQUEST['overlap'];
//			}else{
//				$this->overlap = '';
//			}
//
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
//20101202ysk start
		if($this->pageLimit == 'on') {
//20101202ysk end
			$limit = ' LIMIT ' . $this->startRow . ', ' . $this->maxRow;
//20101202ysk start
		}else{
			$limit = '';
		}
//20101202ysk end
			
		$query = $wpdb->prepare("SELECT ID, CONCAT(mem_name1, ' ', mem_name2) AS name, 
						CONCAT(mem_pref, mem_address1, mem_address2, ' ', mem_address3) AS address, 
						mem_tel AS tel, mem_email AS email, DATE_FORMAT(mem_registered, %s) AS date, 
						mem_point AS point 
					FROM {$this->table}",
					'%Y-%m-%d %H:%i');
					
		$query .= $where . $order . $limit;
		//var_dump($query);
					
		$this->rows = $wpdb->get_results($query, ARRAY_A);
		return $this->rows;
	}
	
	function SetTotalRow()
	{
		global $wpdb;
		$query = "SELECT COUNT(ID) AS ct FROM {$this->table}";
		$res = $wpdb->get_var($query);
		$this->totalRow = $res;
	}
	
	function SetSelectedRow()
	{
		global $wpdb;
		$where = $this->GetWhere();
		$query = $wpdb->prepare("SELECT ID, CONCAT(mem_name1, ' ', mem_name2) AS name, 
						CONCAT(mem_pref, mem_address1, mem_address2, ' ', mem_address3) AS address, 
						mem_tel AS tel, mem_email AS email, DATE_FORMAT(mem_registered, %s) AS date, 
						mem_point AS point 
					FROM {$this->table}",
					'%Y-%m-%d %H:%i');
					
		$query .= $where;
		$rows = $wpdb->get_results($query, ARRAY_A);
		$this->selectedRow = count($rows);
		
	}
	
	function GetWhere()
	{
		$str = '';
		$thismonth = date('Y-m-01 00:00:00');
		$lastmonth = date('Y-m-01 00:00:00', mktime(0, 0, 0, date('m')-1, 1, date('Y')));
		$lastweek = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), date('d')-7, date('Y')));
		$last30 = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), date('d')-30, date('Y')));
		$last90 = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), date('d')-90, date('Y')));
//		switch ( $this->arr_search['period'] ) {
//			case 0:
//				$str = " WHERE order_date >= '{$thismonth}'";
//				break;
//			case 1:
//				$str = " WHERE order_date >= '{$lastmonth}' AND order_date < '{$thismonth}'";
//				break;
//			case 2:
//				$str = " WHERE order_date >= '{$lastweek}'";
//				break;
//			case 3:
//				$str = " WHERE order_date >= '{$last30}'";
//				break;
//			case 4:
//				$str = " WHERE order_date >= '{$last90}'";
//				break;
//			case 5:
//				$str = "";
//				break;
//		}
				
		if( WCUtils::is_blank($str) && !WCUtils::is_blank($this->searchSql) ){
			$str = ' HAVING ' . $this->searchSql;
		}else if($str != '' && $this->searchSql != ''){
			$str .= ' HAVING ' . $this->searchSql;
		}
		return $str;
	}
	
	function SearchIn()
	{
		if($this->arr_search['column'] == 'none' || WCUtils::is_blank($this->arr_search['column']) || WCUtils::is_blank($this->arr_search['word']) )
			return;//$this->searchSql = $sql;
		else
			$this->searchSql = '`' .  $this->arr_search['column'] . '` LIKE '."'%" . esc_sql($this->arr_search['word']) . "%'";
	}

	function SearchOut()
	{
		$this->searchSql = '';
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
			$html .= '<li class="navigationStr"><a href="' . site_url() . '/wp-admin/admin.php?page=usces_memberlist&changePage=1">first&lt;&lt;</a></li>' . "\n";
			$html .= '<li class="navigationStr"><a href="' . site_url() . '/wp-admin/admin.php?page=usces_memberlist&changePage=' . $this->previousPage . '">prev&lt;</a></li>'."\n";
		}
		if($this->selectedRow > 0) {
			for($i=0; $i<count($box); $i++){
				if($box[$i] == $this->currentPage){
					$html .= '<li class="navigationButtonSelected">' . $box[$i] . '</li>'."\n";
				}else{
					$html .= '<li class="navigationButton"><a href="' . site_url() . '/wp-admin/admin.php?page=usces_memberlist&changePage=' . $box[$i] . '">' . $box[$i] . '</a></li>'."\n";
				}
			}
		}
		if(($this->currentPage == $this->lastPage) || ($this->selectedRow == 0)){
			$html .= '<li class="navigationStr">&gt;next</li>'."\n";
			$html .= '<li class="navigationStr">&gt;&gt;last</li>'."\n";
		}else{
			$html .= '<li class="navigationStr"><a href="' . site_url() . '/wp-admin/admin.php?page=usces_memberlist&changePage=' . $this->nextPage . '">&gt;next</a></li>'."\n";
			$html .= '<li class="navigationStr"><a href="' . site_url() . '/wp-admin/admin.php?page=usces_memberlist&changePage=' . $this->lastPage . '">&gt;&gt;last</a></li>'."\n";
		}
		if($this->searchSwitchStatus == 'OFF'){
			$html .= '<li class="rowsnum"><a style="cursor:pointer;" id="searchVisiLink" onclick="toggleVisibility(\'searchBox\');">' . __('Show the Operation field', 'usces') . '</a>'."\n";
		}else{
			$html .= '<li class="rowsnum"><a style="cursor:pointer;" id="searchVisiLink" onclick="toggleVisibility(\'searchBox\');">' . __('hide the Operation field', 'usces') . '</a>'."\n";
		}

		$html .= '<li class="refresh"><a href="' . site_url() . '/wp-admin/admin.php?page=usces_memberlist&refresh">' . __('updates it to latest information', 'usces') . '</a></li>' . "\n";
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
//		$_SESSION[$this->table]['displayColumns'] = $this->displayColumns;//実際に表示するフィールド
//		$_SESSION[$this->table]['defaultColumns'] = $this->defaultColumns;//全てのフィールド
		$_SESSION[$this->table]['userHeaderNames'] = $this->userHeaderNames;//全てのフィールド
		$_SESSION[$this->table]['headers'] = $this->headers;//表示するヘッダ文字列
		$_SESSION[$this->table]['rows'] = $this->rows;			//表示する行オブジェクト
		$_SESSION[$this->table]['sortSwitchs'] = $this->sortSwitchs;	//各フィールド毎の昇順降順スイッチ
		$_SESSION[$this->table]['dataTableNavigation'] = $this->dataTableNavigation;	
//		$_SESSION[$this->table]['listPatternId'] = $this->listPatternId;	//絞込みSQL文（listPatternテーブルより取得）
//		$_SESSION[$this->table]['listPatternRows'] = $this->listPatternRows;	
		$_SESSION[$this->table]['searchSql'] = $this->searchSql;
//		$_SESSION[$this->table]['listPatternNavigation'] = $this->listPatternNavigation;
//		$_SESSION[$this->table]['listPatternSelect'] = $this->listPatternSelect;
 		$_SESSION[$this->table]['arr_search'] = $this->arr_search;
		$_SESSION[$this->table]['searchSwitchStatus'] = $this->searchSwitchStatus;
//		$_SESSION[$this->table]['useListPattern'] = $this->useListPattern;
//		$_SESSION[$this->table]['useEditField'] = $this->useEditField;
//		$_SESSION[$this->table]['useUpload'] = $this->useUpload;
//		$_SESSION[$this->table]['useDownload'] = $this->useDownload;
//		$_SESSION[$this->table]['useAllDelete'] = $this->useAllDelete;
//		$_SESSION[$this->table]['useBackButton'] = $this->useBackButton;
//		$_SESSION[$this->table]['useNewButton'] = $this->useNewButton;
//		$_SESSION[$this->table]['useDoubleClickEdit'] = $this->useDoubleClickEdit;
//		$_SESSION[$this->table]['columnTypes'] = $this->columnTypes;
//		$_SESSION[$this->table]['valueLabels'] = $this->valueLabels;
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
				$this->headers[$value] = '<a href="' . site_url() . '/wp-admin/admin.php?page=usces_memberlist&changeSort=' . $value . '&switch=' . $switch . '"><span class="sortcolumn">' . $key . ' ' . $str . '</span></a>';
			}else{
				$switch = $this->sortSwitchs[$value];
				$this->headers[$value] = '<a href="' . site_url() . '/wp-admin/admin.php?page=usces_memberlist&changeSort=' . $value . '&switch=' . $switch . '"><span>' . $key . '</span></a>';
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
	
}


?>