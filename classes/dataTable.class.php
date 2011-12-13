<?php
class dataTable
{
	var $dbCon;			//データベース接続リソース
	var $table;			//テーブル名
	var $optionTableName;	//オプションテーブル名
	var $errorMes;		//エラーメッセージ
	var $action;		//アクション
	var $maxRow;		//最大表示行数
	var $startRow;		//表示開始行番号
	var $sortColumn;	//現在ソート中のフィールド
	var $sortOldColumn;	//比較用
	var $listPatternId;	//絞込みlistPatternID
	var $listPatternRows;	//listPatternオブジェクト
	var $listPatternTableName;	//listPatternテーブル名
	var $listPatternNavigation;	//ナヴィゲーションhtmlコード
	var $listPatternSelect;	//セレクトhtmlコード
	var $totalRow;		//全行数
	var $selectedRow;	//絞り込まれた行数
	var $currentPage;	//現在のページNo
	var $firstPage;		//最初のページNo
	var $previousPage;	//前のページNo
	var $nextPage;		//次のページNo
	var $lastPage;		//最終ページNo
	var $naviMaxButton;	//ページネーション・ナビのボタンの数
	var $displayColumns;//表示するフィールド
	var $defaultColumns;//全てのフィールド
	var $userHeaderNames;	//ユーザー指定のヘッダ名
	var $columnTypes;	//フィールドのデータタイプ
	var $valueLabels;	//選択タイプ用ヴァリュー
	var $headers;		//表示するヘッダ文字列
	var $rows;			//メインテーブルオブジェクト
	var $sortSwitchs;	//各フィールド毎の昇順降順スイッチ
	var $dataTableNavigation;	//ナヴィゲーションhtmlコード
	var $expiration;	//Cookie有効期限
	var $editMemberId;	//編集対象のメンバーID
	var $searchs;		//簡易絞込み対象フィールド、式、キーワード
	var $searchSql;		//簡易絞込みSQL
	var $searchSwitchStatus;	//サーチ表示スイッチ
	var $useListPattern;//ボタン表示フラグ
	var $useEditField;	//ボタン表示フラグ
	var $useUpload;		//ボタン表示フラグ
	var $useDownload;	//ボタン表示フラグ
	var $useAllDelete;	//ボタン表示フラグ
	var $useBackButton;	//ボタン表示フラグ
	var $useNewButton;	//ボタン表示フラグ
	var $useDoubleClickEdit;	//ボタン表示フラグ
	var $editListPatternName;		//パターンリスト編集用
	var $editListPatternShikis;		//パターンリスト編集用
	var $editListPatternFieldNames;	//パターンリスト編集用
	var $editListPatternFieldDatas;	//パターンリスト編集用
	var $tableValue;	//一時データ
	var $overlap;	//sendMail 重複許可
	
	//Constructor
	function dataTable($dbConnection, $databaseName, $tableName)
	{
		//@session_start();

		$this->dbCon = $dbConnection;
		mysql_select_db($databaseName, $dbConnection);
		$this->table = $tableName;
		$this->optionTableName = 'datatable_options';
		$this->optionFlag = TRUE;

		$this->listPatternTableName = '';
		$this->useListPattern = '';
		$this->useEditField = '';
		$this->useUpload = '';
		$this->useDownload = '';
		$this->useAllDelete = '';
		$this->useBackButton = '';
		$this->useNewButton = '';
		$this->useDoubleClickEdit = '';
		
		$this->overlap = '';
		
		$this->maxRow = 20;
		$this->naviMaxButton = 11;
		$this->firstPage = 1;
		$this->expiration = time()+31536000;
		
		$this->SetParamByQuery();
		
	}
	
	//Controller
	function MakeTable()
	{
		switch ($this->action){
			case 'default':
				$this->SetDefaultParam();
				$res = $this->GetRows();
				break;
			case 'changeDispColumn':
				$this->SetParam();
				$res = $this->GetRows();
				break;
			case 'changePage':
				$this->SetParam();
				$res = $this->GetRows();
				break;
			case 'changeSort':
				$this->SetParam();
				$res = $this->GetRows();
				break;
			case 'changeListPattern':
				$this->SetParam();
				$this->SetTotalRow();
				$this->SetSelectedRow();
				$res = $this->GetRows();
				break;
			case 'deletListMember':
				$this->SetParam();
				$status = $this->DeleteRows();
				$this->SetTotalRow();
				$this->SetSelectedRow();
				$res = $this->GetRows();
				break;
			case 'searchIn':
				$this->SetParam();
				$status = $this->SearchIn();
				$this->SetTotalRow();
				$this->SetSelectedRow();
				$res = $this->GetRows();
				break;
			case 'searchOut':
				$this->SetParam();
				$status = $this->SearchOut();
				$this->SetTotalRow();
				$this->SetSelectedRow();
				$res = $this->GetRows();
				break;
			case 'uploadCSV':
				$this->SetParam();
				$status = $this->upload();
				$this->SetTotalRow();
				$this->SetSelectedRow();
				$res = $this->GetRows();
				break;
			case 'downloadCSV':
				$this->SetParam();
				$this->DownloadCSV();
				exit;
				break;
			case 'refresh':
				$this->GetListPatternRows($this->listPatternTableName);
				$this->SetParam();
				$this->SetSelectedRow();
				$res = $this->GetRows();
				break;
			case 'editMember':
				$this->SetParam();
				$status = $this->UpdateMember();
				$this->SetSelectedRow();
				$res = $this->GetRows();
				break;
			case 'addMember':
				$this->SetParam();
				$status = $this->InsertMember();
				$this->SetTotalRow();
				$this->SetSelectedRow();
				$res = $this->GetRows();
				break;
			case 'getMemberData':
				$this->SetParam();
				$this->GetMemberData($this->editMemberId);
				exit;
				break;
			case 'changeSearchSwitch':
				$this->ChangeSearchSwitchStatus();
				exit;
				break;
			case 'changeListPatternAjax':
				$this->ChangeListPatternAjax();
				exit;
				break;
			case 'addListPattern':
				$this->SetParam();
				$status = $this->AddPatternList();
				$this->SetTotalRow();
				$this->SetSelectedRow();
				$res = $this->GetRows();
				break;
			case 'rowButtonDelete':
				$this->SetParam();
				$status = $this->RowButtonDelete();
				$this->SetTotalRow();
				$this->SetSelectedRow();
				$res = $this->GetRows();
				break;
		}
		
		$this->SetListPatternNavi();
		$this->SetNavi();
		$this->SetHeaders();
		
		if($res){
		
			$this->SetSESSION();
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
		$this->searchs = array('column'=>'', 'shiki'=>'', 'word'=>'');
		if(isset($_SESSION[$this->table]['searchSwitchStatus'])){
			$this->searchSwitchStatus = $_SESSION[$this->table]['searchSwitchStatus'];
		}else{
			$this->searchSwitchStatus = 'OFF';
		}
		$this->GetListPatternRows($this->listPatternTableName);
		$this->searchSql =  '';
		$this->SetDefaultColumns();
		
		
		//リストパターン
		if(isset($_COOKIE[$this->table.'listPatternId']) && $this->listPatternTableName != ''){
			$this->listPatternId = $_COOKIE[$this->table.'listPatternId'];
		}else if($this->listPatternTableName != ''){
			$this->listPatternId =1;
			setcookie($this->table.'listPatternId', $this->listPatternId, $this->expiration);
		}
//		if($optionValue = $this->GetOptions($this->table, 'listPatternId')){
//			$this->listPatternId = $optionValue;
//		}else{
//			$this->listPatternId =1;
//			$this->SetOptions($this->table, 'listPatternId', $this->listPatternId);
//		}
		//ヘッダ表示文字列
//		if(isset($_COOKIE[$this->table.'userHeaderNames'])){
//			$this->userHeaderNames = $this->GetCookieArray($this->table.'userHeaderNames');
//		}else{
//			for($i=0; $i<count($this->defaultColumns); $i++){
//				$headerKey = $this->defaultColumns[$i];
//				$this->userHeaderNames[$headerKey] = $this->defaultColumns[$i];
//			}
//			$this->SetCookieArray($this->table.'userHeaderNames', $this->userHeaderNames);
//		}
		if($optionValue = $this->GetOptions($this->table, 'userHeaderNames')){
			$this->userHeaderNames = $optionValue;
		}else{
			for($i=0; $i<count($this->defaultColumns); $i++){
				$headerKey = $this->defaultColumns[$i];
				$this->userHeaderNames[$headerKey] = $this->defaultColumns[$i];
			}
			$this->SetOptions($this->table, 'userHeaderNames', $this->userHeaderNames);
		}
		//表示フィールド
//		if(isset($_COOKIE[$this->table.'displayColumns'])){
//			$this->displayColumns = $this->GetCookieArray($this->table.'displayColumns');
//		}else{
//			$this->displayColumns = $this->defaultColumns;
//			$this->SetCookieArray($this->table.'displayColumns', $this->displayColumns);
//		}
		if($optionValue = $this->GetOptions($this->table, 'displayColumns')){
			$this->displayColumns = $optionValue;
		}else{
			$this->displayColumns = $this->defaultColumns;
			$this->SetOptions($this->table, 'displayColumns', $this->displayColumns);
		}
		//フィールドのデータタイプ
//		if(isset($_COOKIE[$this->table.'columnTypes'])){
//			$this->columnTypes = $this->GetCookieArray($this->table.'columnTypes');
//		}else{
//			for($i=1; $i<count($this->defaultColumns); $i++){
//				$headerKey = $this->defaultColumns[$i];
//				$this->columnTypes[$headerKey] = 0;
//			}
//			$this->SetCookieArray($this->table.'columnTypes', $this->columnTypes);
//		}
		if($optionValue = $this->GetOptions($this->table, 'columnTypes')){
			$this->columnTypes = $optionValue;
		}else{
			for($i=1; $i<count($this->defaultColumns); $i++){
				$headerKey = $this->defaultColumns[$i];
				$this->columnTypes[$headerKey] = 0;
			}
			$this->SetOptions($this->table, 'columnTypes', $this->columnTypes);
		}
		//選択タイプ用ヴァリュー
//		if(isset($_COOKIE[$this->table.'valueLabels'])){
//			$this->valueLabels = $this->GetCookieArray($this->table.'valueLabels');
//		}else{
//			for($i=1; $i<count($this->defaultColumns); $i++){
//				$headerKey = $this->defaultColumns[$i];
//				$this->valueLabels[$headerKey] = '';
//			}
//			$this->SetCookieArray($this->table.'valueLabels', $this->valueLabels);
//		}
		if($optionValue = $this->GetOptions($this->table, 'valueLabels')){
			$this->valueLabels = $optionValue;
		}else{
			for($i=1; $i<count($this->defaultColumns); $i++){
				$headerKey = $this->defaultColumns[$i];
				$this->valueLabels[$headerKey] = '';
			}
			$this->SetOptions($this->table, 'valueLabels', $this->valueLabels);
		}
		
	
		//トータル行数、デフォルトフィールド名
		$this->SetTotalRow();
		$this->SetSelectedRow();

	}
	
	//Param
	function SetParam()
	{
		$this->startRow = ($this->currentPage-1) * $this->maxRow;
		//$this->totalRow = $_SESSION[$this->table]['totalRow'];
		$this->defaultColumns = $_SESSION[$this->table]['defaultColumns'];
		$this->listPatternRows = $_SESSION[$this->table]['listPatternRows'];
		$this->searchSwitchStatus = $_SESSION[$this->table]['searchSwitchStatus'];
	}
	
	//Query
	function SetParamByQuery()
	{
	
		if(isset($_REQUEST['changeDispColumn'])){
		
			$this->action = 'changeDispColumn';
			$this->displayColumns = $_POST['checkedColumns'];
			$this->SetOptions($this->table, 'displayColumns', $this->displayColumns);
			$getnames = $_POST['userHeaderNames'];
			$coltypes = $_POST['columnTypes'];
			$vallabels = $_POST['valueLabels'];
			$this->SetDefaultColumns();
			for($i=0; $i< count($this->defaultColumns); $i++){
				$headerKey = $this->defaultColumns[$i];
				if($getnames[$i] == ''){
					$this->userHeaderNames[$headerKey] = $this->defaultColumns[$i];
				}else{
					$this->userHeaderNames[$headerKey] = htmlspecialchars($getnames[$i]);
				}
			}
			$this->SetOptions($this->table, 'userHeaderNames', $this->userHeaderNames);
			for($i=1; $i< count($this->defaultColumns); $i++){
				$headerKey = $this->defaultColumns[$i];
				if(($coltypes[$i-1] == 1 || $coltypes[$i-1] == 2) && $vallabels[$i-1] == ''){
					$valstr = $this->GetSelectData($headerKey);
					$this->valueLabels[$headerKey] = $valstr;
				}else{
					$this->valueLabels[$headerKey] = htmlspecialchars($vallabels[$i-1]);
				}
				$this->columnTypes[$headerKey] = htmlspecialchars($coltypes[$i-1]);
			}
			$this->SetOptions($this->table, 'columnTypes', $this->columnTypes);
			$this->SetOptions($this->table, 'valueLabels', $this->valueLabels);

			$this->currentPage = $_SESSION[$this->table]['currentPage'];
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchs = $_SESSION[$this->table]['searchs'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];

			
		}else if(isset($_REQUEST['changePage'])){
		
			$this->action = 'changePage';
			$this->currentPage = $_REQUEST['changePage'];
			
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchs = $_SESSION[$this->table]['searchs'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];
			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
		}else if(isset($_REQUEST['changeSort'])){
		
			$this->action = 'changeSort';
			$this->sortOldColumn = $this->sortColumn;
			$this->sortColumn = $_REQUEST['changeSort'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->sortSwitchs[$this->sortColumn] = $_REQUEST['switch'];
			
			$this->currentPage = $_SESSION[$this->table]['currentPage'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchs = $_SESSION[$this->table]['searchs'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];
			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
		}else if(isset($_REQUEST['searchIn'])){
		
			$this->action = 'searchIn';
			$this->searchs['column'] = $_REQUEST['column'];
			$this->searchs['shiki'] = intval($_REQUEST['shiki']);
			$this->searchs['word'] = $_REQUEST['word'];
			
			$this->currentPage = 1;
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
		}else if(isset($_REQUEST['searchOut'])){
		
			$this->action = 'searchOut';
			$this->searchs['column'] = '';
			$this->searchs['shiki'] = '';
			$this->searchs['word'] = '';
			
			$this->currentPage = 1;
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
		}else if(isset($_REQUEST['changeListPattern'])){
		
			$this->action = 'changeListPattern';
			$this->listPatternId = $_REQUEST['changeListPattern'];
			setcookie($this->table.'listPatternId', $this->listPatternId, $this->expiration);;
			
			$this->currentPage = 1;
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchs = $_SESSION[$this->table]['searchs'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
		}else if(isset($_POST['tableAction']) && ($_POST['tableAction'] == 'deletListMember')){
		
			$this->action = 'deletListMember';
			
			$this->currentPage = 1;
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchs = $_SESSION[$this->table]['searchs'];
			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
		}else if(isset($_REQUEST['uploadCSV'])){
		
			$this->action = 'uploadCSV';
			
			$this->currentPage = $_SESSION[$this->table]['currentPage'];
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchs = $_SESSION[$this->table]['searchs'];
			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
		}else if(isset($_REQUEST['tableAction']) && ($_POST['tableAction'] == 'downloadCSV')){
		
			$this->action = 'downloadCSV';
			
			$this->currentPage = $_SESSION[$this->table]['currentPage'];
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchs = $_SESSION[$this->table]['searchs'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];
			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
		}else if(isset($_REQUEST['refresh'])){
		
			$this->action = 'refresh';

			$this->currentPage = $_SESSION[$this->table]['currentPage'];
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchs = $_SESSION[$this->table]['searchs'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];
			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
		}else if(isset($_REQUEST['editMember']) && ($_POST['editMember'] == 'edit')){
		
			$this->action = 'editMember';
			$this->editMemberId = $_POST['memberId'];
			$this->currentPage = $_SESSION[$this->table]['currentPage'];
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchs = $_SESSION[$this->table]['searchs'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
		}else if(isset($_REQUEST['editMember']) && ($_POST['editMember'] == 'add')){
		
			$this->action = 'addMember';
			$this->editMemberId = $_POST['memberId'];
			$this->currentPage = $_SESSION[$this->table]['currentPage'];
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchs = $_SESSION[$this->table]['searchs'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
		}else if(isset($_REQUEST['getMemberData'])){
		
			$this->action = 'getMemberData';
			$this->editMemberId = $_REQUEST['getMemberData'];

			$this->currentPage = $_SESSION[$this->table]['currentPage'];
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchs = $_SESSION[$this->table]['searchs'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];
			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
		}else if(isset($_REQUEST['editListPattern'])){
			
			if($_REQUEST['editListPattern'] == 'add'){
				$this->action = 'addListPattern';
			}
			$this->editListPatternName = $_POST['patternName'];
			$this->editListPatternShikis = $_POST['shikis'];
			$this->editListPatternFieldNames = $_POST['fieldNames'];
			$this->editListPatternFieldDatas = $_POST['fieldDatas'];

			$this->currentPage = $_SESSION[$this->table]['currentPage'];
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchs = $_SESSION[$this->table]['searchs'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];
			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
		}else if(isset($_REQUEST['tableAction']) && ($_POST['tableAction'] == 'rowButtonDelete')){
			
			$this->action = 'rowButtonDelete';
			$this->tableValue = $_POST['tableValue'];

			$this->currentPage = $_SESSION[$this->table]['currentPage'];
			$this->sortColumn = $_SESSION[$this->table]['sortColumn'];
			$this->sortSwitchs = $_SESSION[$this->table]['sortSwitchs'];
			$this->userHeaderNames = $_SESSION[$this->table]['userHeaderNames'];
			$this->listPatternId = $_SESSION[$this->table]['listPatternId'];
			$this->displayColumns = $_SESSION[$this->table]['displayColumns'];
			$this->searchSql = $_SESSION[$this->table]['searchSql'];
			$this->searchs = $_SESSION[$this->table]['searchs'];
			$this->totalRow = $_SESSION[$this->table]['totalRow'];
			$this->selectedRow = $_SESSION[$this->table]['selectedRow'];
			$this->columnTypes = $_SESSION[$this->table]['columnTypes'];
			$this->valueLabels = $_SESSION[$this->table]['valueLabels'];
			
		}else if(isset($_REQUEST['changeSearchSwitch'])){
		
			$this->action = 'changeSearchSwitch';
			$this->searchSwitchStatus = $_REQUEST['changeSearchSwitch'];

		}else if(isset($_REQUEST['changeListPatternAjax'])){
		
			$this->action = 'changeListPatternAjax';
			$this->listPatternId = $_REQUEST['changeListPatternAjax'];
			if(isset($_REQUEST['overlap'])){
				$this->overlap = $_REQUEST['overlap'];
			}else{
				$this->overlap = '';
			}

		}else{
		
			$this->action = 'default';
		}
	}
	
	function SetListPatternNavi()
	{
		if($this->listPatternTableName == ''){
			$this->listPatternNavigation = '';
			$this->listPatternSelect = '';
			return;
		}
		$sel = '';
		$str = '';
		$str .= '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" name="listpattern" id="listpattern" >' . "\n";
		$str .= __('Pattern List', 'usces');
		$sel .= '<select name="changeListPattern" id="listPattern" onchange="onChange_listPattern()">' . "\n";
		for($i=0; $i<count($this->listPatternRows); $i++){
			if($this->listPatternRows[$i]['id'] == $this->listPatternId){
				$selected = ' selected="selected"';
			}else{
				$selected = '';
			}
			$sel .= '<option value="' . $this->listPatternRows[$i]['id'] . '"' . $selected . '>' . $this->listPatternRows[$i]['name'] . '</option>' . "\n";
		}
		$sel .= '</select>' . "\n";
		$str .= $sel;
		$str .= '</form>' . "\n";
		$this->listPatternNavigation = $str;
		$this->listPatternSelect = $sel;
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
		$html .= '<li class="rowsnum">' . $this->selectedRow . ' / ' . $this->totalRow . ' '.__('cases', 'usces').'</li>' . "\n";
		if(($this->currentPage == 1) || ($this->selectedRow == 0)){
			$html .= '<li class="navigationStr">first&lt;&lt;</li>' . "\n";
			$html .= '<li class="navigationStr">prev&lt;</li>'."\n";
		}else{
			$html .= '<li class="navigationStr"><a href="' . $_SERVER['PHP_SELF'] . '?changePage=1">first&lt;&lt;</a></li>' . "\n";
			$html .= '<li class="navigationStr"><a href="' . $_SERVER['PHP_SELF'] . '?changePage=' . $this->previousPage . '">prev&lt;</a></li>'."\n";
		}
		if($this->selectedRow > 0) {
			for($i=0; $i<count($box); $i++){
				if($box[$i] == $this->currentPage){
					$html .= '<li class="navigationButtonSelected">' . $box[$i] . '</li>'."\n";
				}else{
					$html .= '<li class="navigationButton"><a href="' . $_SERVER['PHP_SELF'] . '?changePage=' . $box[$i] . '">' . $box[$i] . '</a></li>'."\n";
				}
			}
		}
		if(($this->currentPage == $this->lastPage) || ($this->selectedRow == 0)){
			$html .= '<li class="navigationStr">&gt;next</li>'."\n";
			$html .= '<li class="navigationStr">&gt;&gt;last</li>'."\n";
		}else{
			$html .= '<li class="navigationStr"><a href="' . $_SERVER['PHP_SELF'] . '?changePage=' . $this->nextPage . '">&gt;next</a></li>'."\n";
			$html .= '<li class="navigationStr"><a href="' . $_SERVER['PHP_SELF'] . '?changePage=' . $this->lastPage . '">&gt;&gt;last</a></li>'."\n";
		}
		if($this->searchSwitchStatus == 'OFF'){
			$html .= '<li class="rowsnum"><a href="#" id="searchSwitch">' . __('Display Search', 'usces') . '</a>'."\n";
		}else{
			$html .= '<li class="rowsnum"><a href="#" id="searchSwitch">' . __('Hide Search', 'usces') . '</a>'."\n";
		}

		$html .= '<li class="refresh"><a href="' . $_SERVER['PHP_SELF'] . '?refresh">' . __('updates it to latest information', 'usces') . '</a></li>' . "\n";
		$html .= '</ul>'."\n";

		$this->dataTableNavigation = $html;
	}

	function SetHeaders()
	{
		for($i=0; $i<count($this->displayColumns); $i++){
			//$userHaederKey = array_search($this->displayColumns[$i], $this->defaultColumns);
			$userHeaderKey = $this->displayColumns[$i];
			if($this->displayColumns[$i] == $this->sortColumn){
				if($this->sortSwitchs[$this->sortColumn] == 'ASC'){
					$str = __('[ASC]', 'usces');
					$switch = 'DESC';
				}else{
					$str = __('[DESC]', 'usces');
					$switch = 'ASC';
				}
				$this->headers[$i] = '<a href="' . $_SERVER['PHP_SELF'] . '?changeSort=' . $this->displayColumns[$i] . '&switch=' . $switch . '"><span class="sortcolumn">' . $this->userHeaderNames[$userHeaderKey] . ' ' . $str . '</span></a>';
			}else{
				$cname = $this->displayColumns[$i];
				$switch = $this->sortSwitchs[$cname];
				$this->headers[$i] = '<a href="' . $_SERVER['PHP_SELF'] . '?changeSort=' . $this->displayColumns[$i] . '&switch=' . $switch . '"><span>' . $this->userHeaderNames[$userHeaderKey] . '</span></a>';
			}
		}
//			echo 'SetHeaders:' . "<br>";
//var_dump($this->userHeaderNames);
	}
		
	function SetSESSION()
	{
	
//		var $dbCon;			//データベース接続リソース
//		var $table;			//テーブル名
//		var $errorMes;		//エラーメッセージ
//		var $action;		//アクション
//		var $maxRow;		//最大表示行数
		$_SESSION[$this->table]['startRow'] = $this->startRow;		//表示開始行番号
		$_SESSION[$this->table]['sortColumn'] = $this->sortColumn;	//現在ソート中のフィールド
//		var $sortNewColumn;	//新たにソートリクエストがあったフィールド
		$_SESSION[$this->table]['totalRow'] = $this->totalRow;		//全行数
		$_SESSION[$this->table]['selectedRow'] = $this->selectedRow;	//絞り込まれた行数
		$_SESSION[$this->table]['currentPage'] = $this->currentPage;	//現在のページNo
//		var $firstPage;		//最初のページNo
		$_SESSION[$this->table]['previousPage'] = $this->previousPage;	//前のページNo
		$_SESSION[$this->table]['nextPage'] = $this->nextPage;		//次のページNo
		$_SESSION[$this->table]['lastPage'] = $this->lastPage;		//最終ページNo
//		var $naviMaxButton;	//ページネーション・ナビのボタンの数
		$_SESSION[$this->table]['displayColumns'] = $this->displayColumns;//実際に表示するフィールド
		$_SESSION[$this->table]['defaultColumns'] = $this->defaultColumns;//全てのフィールド
		$_SESSION[$this->table]['userHeaderNames'] = $this->userHeaderNames;//全てのフィールド
		$_SESSION[$this->table]['headers'] = $this->headers;//表示するヘッダ文字列
		$_SESSION[$this->table]['rows'] = $this->rows;			//表示する行オブジェクト
		$_SESSION[$this->table]['sortSwitchs'] = $this->sortSwitchs;	//各フィールド毎の昇順降順スイッチ
		$_SESSION[$this->table]['dataTableNavigation'] = $this->dataTableNavigation;	
		$_SESSION[$this->table]['listPatternId'] = $this->listPatternId;	//絞込みSQL文（listPatternテーブルより取得）
		$_SESSION[$this->table]['listPatternRows'] = $this->listPatternRows;	
		$_SESSION[$this->table]['searchSql'] = $this->searchSql;
		$_SESSION[$this->table]['listPatternNavigation'] = $this->listPatternNavigation;
		$_SESSION[$this->table]['listPatternSelect'] = $this->listPatternSelect;
 		$_SESSION[$this->table]['searchs'] = $this->searchs;
		$_SESSION[$this->table]['searchSwitchStatus'] = $this->searchSwitchStatus;
		$_SESSION[$this->table]['searchSql'] = $this->searchSql;
		$_SESSION[$this->table]['useListPattern'] = $this->useListPattern;
		$_SESSION[$this->table]['useEditField'] = $this->useEditField;
		$_SESSION[$this->table]['useUpload'] = $this->useUpload;
		$_SESSION[$this->table]['useDownload'] = $this->useDownload;
		$_SESSION[$this->table]['useAllDelete'] = $this->useAllDelete;
		$_SESSION[$this->table]['useBackButton'] = $this->useBackButton;
		$_SESSION[$this->table]['useNewButton'] = $this->useNewButton;
		$_SESSION[$this->table]['useDoubleClickEdit'] = $this->useDoubleClickEdit;
		$_SESSION[$this->table]['columnTypes'] = $this->columnTypes;
		$_SESSION[$this->table]['valueLabels'] = $this->valueLabels;
	}
	
	//GetRows
	function GetRows()
	{
		$table = '`' . $this->table . '`';
		$where = $this->GetWhere($this->listPatternTableName);
//		if($where == '' && $this->searchSql != ''){
//			$where = ' WHERE ' . $this->searchSql;
//		}else if($where != '' && $this->searchSql != ''){
//			$where .= ' AND ' . $this->searchSql;
//		}
		$order = ' ORDER BY `' . $this->sortColumn . '` ' . $this->sortSwitchs[$this->sortColumn];
		$limit = ' LIMIT ' . $this->startRow . ', ' . $this->maxRow;
		
		$query = sprintf("SELECT * FROM %s%s%s%s", 
						mysql_real_escape_string($table), 
						$where, 
						mysql_real_escape_string($order), 
						mysql_real_escape_string($limit) 
						);
		$res = mysql_query($query, $this->dbCon);
		if($res){
			while($rows = mysql_fetch_assoc($res)){
				foreach($rows as $key=>$value){
					if($value == ''){
						$rows[$key] = '&nbsp;';
					}
				}
				
				$this->rows[] = $rows;
			}
			
			$this->errorMes = '';

			return TRUE;
			
		}else{
		
			$this->errorMes = 'ERROR : ' . mysql_error() . 'QUERY : ' . $query;
			return FALSE;
		}
	}
	
	//DeleteRows
	function DeleteRows()
	{
		$table = '`' . $this->table . '`';
		$where = $this->GetWhere($this->listPatternTableName);
		
		$query = sprintf("DELETE FROM %s%s", 
						mysql_real_escape_string($table), 
						$where 
						);
		$res = mysql_query($query, $this->dbCon);
		if($res){
			
			
			$this->errorMes = '';
			return TRUE;
			
		}else{
		
			$this->errorMes = 'ERROR : ' . mysql_error() . 'QUERY : ' . $query;
			return FALSE;
		}
	}
	//DeleteButtonRow
	function RowButtonDelete()
	{
		$table = '`' . $this->table . '`';
		
		$query = sprintf("DELETE FROM %s WHERE id = %s LIMIT 1", 
						mysql_real_escape_string($table), 
						"'" . mysql_real_escape_string($this->tableValue) . "'" 
						);
		$res = mysql_query($query, $this->dbCon);
		if($res){
			$this->errorMes = '';
			return TRUE;
		}else{
			$this->errorMes = 'ERROR : ' . mysql_error() . 'QUERY : ' . $query;
			return FALSE;
		}
	}
	//UpdateMember
	function UpdateMember()
	{
		$fieldDatas = $_POST['fieldDatas'];	
		$fieldNames = $_POST['fieldNames'];
		$str = '';
		for($i=1; $i<count($fieldNames); $i++){
			if($fieldDatas[$i] == '' || $fieldDatas[$i] == '&nbsp;'){
				$str .= $fieldNames[$i] . " = DEFAULT, ";
			}else{
				$str .= $fieldNames[$i] . " = '" . mysql_real_escape_string($fieldDatas[$i]) . "', ";
			}
		}
		$str = rtrim($str, ", ");
		$query = sprintf("UPDATE `%s` SET %s WHERE id = %d", 
						mysql_real_escape_string($this->table), 
						$str, 
						$this->editMemberId
						);
		$res = mysql_query($query, $this->dbCon);
		if($res){
			
			$this->errorMes = '';
			return TRUE;
			
		}else{
		
			$this->errorMes = 'ERROR : ' . mysql_error() . 'QUERY : ' . $query;
						echo $this->errorMes;
						exit;
			return FALSE;
		}
	}
	
	//InsertMember
	function InsertMember()
	{
		$fieldDatas = $_POST['fieldDatas'];	
		$fieldNames = $_POST['fieldNames'];
		$str = '';
		for($i=1; $i<count($fieldNames); $i++){
			if($fieldDatas[$i] == '' || $fieldDatas[$i] == '&nbsp;'){
				$str .= "DEFAULT, ";
			}else{
				$str .= "'" . mysql_real_escape_string($fieldDatas[$i]) . "', ";
			}
		}
		$str = rtrim($str, ", ");
		$query = sprintf("INSERT INTO `%s` VALUES ( NULL, %s )", 
						mysql_real_escape_string($this->table), 
						$str
						);
		$res = mysql_query($query, $this->dbCon);
		if($res){
			
			$this->errorMes = '';
			return TRUE;
			
		}else{
		
			$this->errorMes = 'ERROR : ' . mysql_error() . 'QUERY : ' . $query;
						echo $this->errorMes;
						exit;
			return FALSE;
		}
	}
	
	//Uploader
	function upload()
	{
		foreach ($_FILES as $key => $file) {
			$filename = $file['tmp_name'];
		}
		if(!is_uploaded_file($filename)){
			$this->errorMes = "Possible file upload attack: filename '". $filename . "'.";
			return FALSE;
		}
		$fp = fopen($filename, 'r');
		$values = "";
		while(!feof($fp)){
			$line = fgets($fp);
			if(trim($line) == '') break;
			$rows = explode(',', $line);
			if(count($rows) != 15){
				break;
				$this->errorMes = 'ERROR : ' . __('CSV format is invalid!', 'usces');
				return FALSE;
			}
			$values .= "(NULL,";
			foreach($rows as $data){
				if(trim($data) == ''){
					$values .= "DEFAULT,";
				}else{
					$values .= "'" . mysql_real_escape_string(mb_convert_encoding($data,'EUC-JP', 'auto')) . "',";
				}
			}
			$values = trim($values, ',') . "),";
		}
		$values = trim($values, ',');
		
		$query = sprintf("INSERT INTO `%s` VALUES %s", $this->table, $values);
		$res = mysql_query($query, $this->dbCon);
		if($res){
			$this->errorMes = '';
			return TRUE;
		}else{
			$this->errorMes = 'ERROR : ' . mysql_error() . 'QUERY : ' . $query;
			return FALSE;
		}
	}
	
	//DownloadCSV
	function DownloadCSV()
	{
		$table = '`' . $this->table . '`';
		$where = $this->GetWhere($this->listPatternTableName);
//		if($where == '' && $this->searchSql != ''){
//			$where = ' WHERE ' . $this->searchSql;
//		}else if($where != '' && $this->searchSql != ''){
//			$where .= ' AND ' . $this->searchSql;
//		}
		$order = ' ORDER BY `' . $this->sortColumn . '` ' . $this->sortSwitchs[$this->sortColumn];
		
		$query = sprintf("SELECT * FROM %s%s%s", 
						mysql_real_escape_string($table), 
						$where, 
						mysql_real_escape_string($order)
						);
		$res = mysql_query($query, $this->dbCon);
		if($res){
		
			$data = '';
			while($rows = mysql_fetch_assoc($res)){
				foreach($rows as $key=>$value){
					$data .= $value . ',';
				}
				$data = rtrim($data, ',') . "\n";
			}
			$this->errorMes = '';
			
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=listData.csv");
			print(mb_convert_encoding($data,"SJIS","EUC-JP"));
			

		}else{
		
			$this->errorMes = 'ERROR : ' . mysql_error() . 'QUERY : ' . $query;
			echo $this->errorMes;
		}
		
	}
	
	function GetWhere($table)
	{
		$str = '';
		
		if($table != ''){
		
			$query = sprintf("SELECT * FROM `%s` WHERE id = %d", $table, $this->listPatternId);
			$res = mysql_query($query, $this->dbCon);
			if(!$res){
			
				$this->DispError('ERROR : ' . mysql_error() . 'QUERY : ' . $query);
				return FALSE;
				
			}else{
			
				$rows = mysql_fetch_assoc($res);
				$num = mysql_num_rows($res);
				
				if($num == 0){
					$this->listPatternId = 1;
					$_SESSION[$this->table]['listPatternId'] = $this->listPatternId;
					setcookie($this->table.'listPatternId', $this->listPatternId, $this->expiration);
					$str = '';
				}else if($rows['pattern'] == ''){
					$str = '';
				}else{
					$str = ' WHERE ' . $rows['pattern'];
				}
			}
		}
	
		if($str == '' && $this->searchSql != ''){
			$str = ' WHERE ' . $this->searchSql;
		}else if($str != '' && $this->searchSql != ''){
			$str .= ' AND ' . $this->searchSql;
		}
		return $str;
	}
	
	function SearchIn()
	{
		$column = '`' .  $this->searchs['column'] . '`';
		switch ($this->searchs['shiki']){
			case 1:
				$shiki = ' = ';
				$word = "'" . mysql_real_escape_string($this->searchs['word']) . "'";
				break;
			case 2:
				$shiki = ' LIKE ';
				$word = "'%" . mysql_real_escape_string($this->searchs['word']) . "%'";
				break;
			case 3:
				$shiki = ' <> ';
				$word = "'" . mysql_real_escape_string($this->searchs['word']) . "'";
				break;
			case 4:
				$shiki = ' NOT LIKE ';
				$word = "'%" . mysql_real_escape_string($this->searchs['word']) . "%'";
				break;
		}
		$this->searchSql = $column . $shiki . $word;
	}

	function SearchOut()
	{
		$this->searchSql = '';
	}

	function GetErrorMes()
	{
		return $this->errorMes;
	}


	function DispError($mes)
	{
		echo $mes;
		exit;
	}
	
	function UseListPattern($table)
	{
		$this->listPatternTableName = $table;
		$this->useListPattern = 1;
	}
	function UseEditField()
	{
		$this->useEditField = 1;
	}
	function UseUpload()
	{
		$this->useUpload = 1;
	}
	function UseDownload()
	{
		$this->useDownload = 1;
	}
	function UseAllDelete()
	{
		$this->useAllDelete = 1;
	}
	function UseBackButton($previous)
	{
		$this->useBackButton = $previous;
	}
	function UseNewButton()
	{
		$this->useNewButton = 1;
	}
	function UseDoubleClickEdit()
	{
		$this->useDoubleClickEdit = 1;
	}
	
	//ListPatternOBJ
	function GetListPatternRows($table)
	{
		if($table == ''){
			$this->listPatternId = '';
			$this->listPatternRows = '';	
			return;
		}
		
		$query = sprintf("SELECT * FROM `%s` ORDER BY id", $table);
		$res = mysql_query($query, $this->dbCon);
		if(!$res){
		
			$this->DispError('ERROR : ' . mysql_error() . 'QUERY : ' . $query);
			return FALSE;
			
		}else{
		
			while($rows = mysql_fetch_assoc($res)){
				$this->listPatternRows[] = $rows;
			}
			$_SESSION[$this->table]['listPatternRows'] = $this->listPatternRows;	
			return TRUE;
		}
	}
	
	function SetTotalRow()
	{
		
		$query = sprintf("SELECT * FROM `%s`", $this->table);
		$res = mysql_query($query, $this->dbCon);
		if(!$res){
		
			$this->DispError('ERROR : ' . mysql_error() . 'QUERY : ' . $query);
			
		}else{
		
			$this->totalRow = mysql_num_rows($res);
			
		}
		
	}
	function SetDefaultColumns()
	{
		$this->defaultColumns = array();
		
		$query = sprintf("SELECT * FROM `%s`", $this->table);
		$res = mysql_query($query, $this->dbCon);
		if(!$res){
		
			$this->DispError('ERROR : ' . mysql_error() . 'QUERY : ' . $query);
			
		}else{
		
			$length = mysql_num_fields($res);
			for($i=0; $i<$length; $i++){
				$name = mysql_field_name($res, $i);
				$this->defaultColumns[] = $name;
				$this->sortSwitchs[$name] = 'ASC';
			}
			$this->sortColumn = $this->defaultColumns[0];
		}
		
	}
	
	function SetSelectedRow()
	{
		//絞込み後行数
		$where = $this->GetWhere($this->listPatternTableName);
		if($this->overlap == 'DISALLOW'){
			$group = ' GROUP BY mailaddress';
		}else{
			$group = '';
		}
		$query = sprintf("SELECT * FROM `%s`%s%s", $this->table, $where,$group);
		$res = mysql_query($query, $this->dbCon);
		if(!$res){
		
			$this->DispError('ERROR : ' . mysql_error() . 'QUERY : ' . $query);
			return FALSE;
			
		}else{
		
			$num = mysql_num_rows($res);
			$this->selectedRow =  $num;
		}
	}
	
	function GetSelectData($headerKey)
	{
		$query = sprintf("SELECT `%s` FROM `%s` GROUP BY `%s`", $headerKey, $this->table, $headerKey);
		$res = mysql_query($query, $this->dbCon);
		if(!$res){
		
			$this->DispError('ERROR : ' . mysql_error() . 'QUERY : ' . $query);
			return FALSE;
			
		}else{
			$str = '';
			while($rows = mysql_fetch_assoc($res)){
				$str .= $rows[$headerKey] . ':';
			}
			$str = rtrim($str, ':');
			return $str;
		}
	}
	
	
	function SetSortStatus($column, $switch)
	{
		$this->sortColumn = $column;
		$this->sortSwitchs[$column] = $switch;
		$this->rows = array();
		$this->GetRows();
		$this->SetSession();
	}
	
	function SetMaxRow($num)
	{
		$this->maxRow = $num;
		$this->rows = array();
		$this->GetRows();
		$this->SetSession();
	}
	
	function SetCookieArray($name, $values)
	{
		$valuestr = '';
		foreach($values as $key=>$str){
			$valuestr .= $key . '=' . $str . ',';
		}
		$valuestr = trim($valuestr, ',');
//			echo '<br>setcookie:' . "<br>";
//var_dump($values);
//var_dump($valuestr);
		setcookie($name, $valuestr, $this->expiration);
	}
	
	function GetCookieArray($name)
	{
		if(!isset($_COOKIE[$name])) return FALSE;
		
		$names = explode(',', $_COOKIE[$name]);
		foreach($names as $str){
			$parts = explode('=', $str);
			$key = $parts[0];
			if(isset($parts[1])){
				$value = $parts[1];
			}else{
				$value = '';
			}
			$arrays[$key] = $value;
		}
		return $arrays;
	}
	
	function SetOptions($tableName, $optionName, $value)
	{
		if(is_array($value)){
			$str_value = '';
			foreach($value as $key=>$str){
				$str_value .= $key . '=' . $str . ',';
			}
			$str_value = trim($str_value, ',');
		}else{
			$str_value = trim($value);
		}
		$query = sprintf("REPLACE INTO `%s` VALUES ('%s','%s','%s')", 
							$this->optionTableName, 
							$tableName,
							$optionName,
							mysql_real_escape_string($str_value)
						);
		$res = mysql_query($query, $this->dbCon);
		if(!$res){
		
			$this->DispError('ERROR : ' . mysql_error() . 'QUERY : ' . $query);
			return FALSE;
			
		}else{
//			if(mysql_affected_rows() == 0){
//				$query = sprintf("INSERT INTO `%s` VALUES ('%s','%s','%s')", 
//									$this->optionTableName, 
//									$tableName,
//									$optionName,
//									mysql_real_escape_string($str_value)
//								);
//				$res = mysql_query($query, $this->dbCon);
//				if(!$res){
//				
//					$this->DispError('ERROR : ' . mysql_error() . 'QUERY : ' . $query);
//					return FALSE;
//				
//				}else{
//					
//					return TRUE;
//					
//				}
//
//			}else{
			
				return TRUE;
				
//			}
		}
	}
	
	function GetOptions($tableName, $optionName)
	{
		$query = sprintf("SELECT * FROM `%s` WHERE option_name = '%s' AND table_name = '%s'", 
							$this->optionTableName, 
							$optionName,
							$tableName
						);
		$res = mysql_query($query, $this->dbCon);
		if(!$res){
		
			$this->DispError('ERROR : ' . mysql_error() . 'QUERY : ' . $query);
			return FALSE;
			
		}else{
			$rows = mysql_fetch_assoc($res);
			$str_value = $rows['value'];
			if($str_value == ''){
				return FALSE;
			}else{
				
				if(strpos($str_value, ',') === FALSE){
					$return_value = $array_value;
				}else{
					$array_value = explode(',', $str_value);
					foreach($array_value as $str){
						$parts = explode('=', $str);
						$key = $parts[0];
						if(isset($parts[1])){
							$value = $parts[1];
						}else{
							$value = '';
						}
						$return_value[$key] = $value;
					}
				}
				return $return_value;
			}
		}
	}
	
	//パターンリスト追加
	function AddPatternList()
	{
		$flag = '';
		for($i=0; $i<count($this->editListPatternFieldDatas); $i++){
			$flag .= $this->editListPatternFieldDatas[$i];
		}
		if(!$flag) return FALSE;

		$pattern = $this->GetPattern();
		$query = sprintf("INSERT INTO `%s` (`%s`,`%s`) VALUES ('%s','%s')", 
							$this->table,
							'name',
							'pattern',
							mysql_real_escape_string($this->editListPatternName),
							mysql_real_escape_string($pattern)
						 );
		$res = mysql_query($query, $this->dbCon);
		if(!$res){
		
			$this->DispError('ERROR : ' . mysql_error() . 'QUERY : ' . $query);
			return FALSE;
			
		}else{
			return TRUE;
		}
	}
	function GetPattern()
	{
		$str = '';
		for($i=0; $i<count($this->editListPatternFieldDatas); $i++){
			switch ($this->editListPatternShikis[$i]){
				case 1:
					$shiki = ' = ';
					$word = "'" . mysql_real_escape_string($this->editListPatternFieldDatas[$i]) . "'";
					break;
				case 2:
					$shiki = ' LIKE ';
					$word = "'%" . mysql_real_escape_string($this->editListPatternFieldDatas[$i]) . "%'";
					break;
				case 3:
					$shiki = ' <> ';
					$word = "'" . mysql_real_escape_string($this->editListPatternFieldDatas[$i]) . "'";
					break;
				case 4:
					$shiki = ' NOT LIKE ';
					$word = "'%" . mysql_real_escape_string($this->editListPatternFieldDatas[$i]) . "%'";
					break;
			}
			if($this->editListPatternFieldDatas[$i] != ''){
				$str .= "`" . $this->editListPatternFieldNames[$i] . "`" . $shiki . $word . " AND ";
			}
		}
		$str = rtrim($str,' AND ');
		return $str;
	}
	
	
	
	//AJAX
	function GetMemberData($id)
	{
		$data = '';

		$query = sprintf("SELECT * FROM `%s` WHERE id = %d", $this->table, $id);
		$res = mysql_query($query, $this->dbCon);
		if(!$res){
		
			$this->DispError('ERROR : ' . mysql_error() . 'QUERY : ' . $query);
			$data .= '<Result>ERROR</Result>';
			
		}else{
		
			$rows = mysql_fetch_assoc($res);
			$data .= '<Count>'.count($rows).'</Count>';
			$data .= '<Item>';
			foreach($rows as $value){
				if($value == ''){
					$data .= '<info>undefineinfodata</info>';
				}else{
					$data .= '<info>'.$value.'</info>';
				}
			}
			$data .= '</Item>';

			$this->sendXML($data);
		}
	}
	function ChangeSearchSwitchStatus()
	{
		$_SESSION[$this->table]['searchSwitchStatus'] = $this->searchSwitchStatus;
		$data = '<Status>' . $this->searchSwitchStatus . '</Status>';
		$this->sendXML($data);
	}
	function ChangeListPatternAjax()
	{
		$this->SetSelectedRow();
		setcookie($this->table.'listPatternId', $this->listPatternId, $this->expiration);
		$_SESSION[$this->table]['listPatternId'] = $this->listPatternId;
		$_SESSION[$this->table]['selectedRow'] = $this->selectedRow;
		$data = '<Status>' . $this->selectedRow . '</Status>';
		$this->sendXML($data);
	}


	function sendXML($data) {
		//$data = htmlspecialchars($data, ENT_QUOTES);
		header("Content-type:text/xml;charset=euc-jp");
		echo '<?xml version="1.0" encoding="euc-jp"?>';
		//echo '<error>'.$msg.'</error>'."\n";
		echo '<ResultSet>';
		echo $data;
		echo '</ResultSet>';
		exit;
	}
}
?>
