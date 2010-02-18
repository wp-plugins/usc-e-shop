<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';

require_once( USCES_PLUGIN_DIR . "/classes/dataList.class.php" );
global $wpdb;

$tableName = $wpdb->prefix . "usces_member";
$arr_column = array(
			__('membership number', 'usces') => 'ID', 
			__('name', 'usces') => 'name', 
			__('Address', 'usces') => 'address', 
			__('Phone number', 'usces') => 'tel', 
			__('e-mail', 'usces') => 'email', 
			__('Strated date', 'usces') => 'date', 
			__('current point', 'usces') => 'point');

$DT = new dataList($tableName, $arr_column);
$res = $DT->MakeTable();
$arr_search = $DT->GetSearchs();
//$arr_status = get_option('usces_management_status');
$arr_header = $DT->GetListheaders();
$dataTableNavigation = $DT->GetDataTableNavigation();
$rows = $DT->rows;
?>
<script type="text/javascript">
jQuery(function($){
<?php if($status == 'success'){ ?>
			$("#anibox").animate({ backgroundColor: "#ECFFFF" }, 2000);
<?php }else if($status == 'caution'){ ?>
			$("#anibox").animate({ backgroundColor: "#FFF5CE" }, 2000);
<?php }else if($status == 'error'){ ?>
			$("#anibox").animate({ backgroundColor: "#FFE6E6" }, 2000);
<?php } ?>

//	$("#aAdditionalURLs").click(function () {
//		$("#AdditionalURLs").toggle();
//	});

});

function toggleVisibility(id) {
	var e = document.getElementById(id);
	if(e.style.display == 'block') {
		e.style.display = 'none';
		document.getElementById("searchSwitchStatus").value = 'OFF';
	} else {
		e.style.display = 'block';
		document.getElementById("searchSwitchStatus").value = 'ON';
		document.getElementById("searchVisiLink").style.display = 'none';
	}
};

function deleteconfirm(member_id){
	if(confirm(<?php _e("'Are you sure of deleting your membership number ' + member_id + ' ?'", 'usces'); ?>)){
		return true;
	}else{
		return false;
	}
}

jQuery(document).ready(function($){
	$("table#mainDataTable tr:even").addClass("rowSelection_even");
	$("table#mainDataTable tr").hover(function() {
		$(this).addClass("rowSelection_hilight");
	},
	function() {
		$(this).removeClass("rowSelection_hilight");
	});
	if(	$("#searchSwitchStatus").val() == 'OFF'){
		$("#searchBox").css("display", "none");
		$("#searchVisiLink").html('<?php _e('Show the Operation field', 'usces'); ?>');
	} else {
		$("#searchBox").css("display", "block");
		$("#searchVisiLink").css("display", "none");
	}
		
});
</script>
<div class="wrap">
<div class="usces_admin">

<h2>Welcart Management <?php _e('List of Members','usces'); ?></h2>
<p class="version_info">Version <?php echo USCES_VERSION; ?></p>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>

<div id="datatable">
<div id="tablenavi"><?php echo $dataTableNavigation ?></div>

<div id="tablesearch">
<div id="searchBox">
	<form action="<?php echo USCES_ADMIN_URL.'?page=usces_memberlist'; ?>" method="post" name="tablesearch">
		<table id="search_table">
		<tr>
		<td><?php _e('search fields', 'usces'); ?></td>
		<td><select name="search[column]" class="searchselect">
		    <option value="none"> </option>
<?php foreach ((array)$arr_column as $key => $value):
		if($value == $arr_search['column']){
			$selected = ' selected="selected"';
		}else{
			$selected = '';
		}
?>
		    <option value="<?php echo $value ?>"<?php echo $selected ?>><?php echo $key ?></option>
<?php endforeach; ?>
    	</select></td>
		<td><?php _e('key words', 'usces'); ?></td>
		<td><input name="search[word]" type="text" value="<?php echo $arr_search['word'] ?>" class="searchword" maxlength="50" /></td>
		<td><input name="searchIn" type="submit" class="searchbutton" value="<?php _e('Search', 'usces'); ?>" />
		<input name="searchOut" type="submit" class="searchbutton" value="<?php _e('Cancellation', 'usces'); ?>" />
		<input name="searchSwitchStatus" id="searchSwitchStatus" type="hidden" value="<?php echo $DT->searchSwitchStatus; ?>" />
		</td>
		</tr>
		</table>
		
	</form>
</div>
</div>

<table id="mainDataTable" cellspacing="1">
	<tr>
<?php foreach ( (array)$arr_header as $value ) : ?>
		<th scope="col"><?php echo $value ?></th>
<?php endforeach; ?>
		<th scope="col">&nbsp;</th>
	</tr>
<?php foreach ( (array)$rows as $array ) : ?>
	<tr>
	<?php foreach ( (array)$array as $key => $value ) : ?>
		<?php if( $value == '' || $value == ' ' ) $value = '&nbsp;'; ?>
		<?php if( $key == 'ID' ): ?>
		<td><a href="<?php echo USCES_ADMIN_URL.'?page=usces_memberlist&member_action=edit&member_id=' . $value; ?>"><?php echo $value; ?></a></td>
		<?php elseif( $key == 'point' ): ?>
		<td class="right"><?php echo $value; ?></td>
		<?php else: ?>
		<td><?php echo $value; ?></td>
		<?php endif; ?>
<?php endforeach; ?>
	<td><a href="<?php echo USCES_ADMIN_URL.'?page=usces_memberlist&member_action=delete&member_id=' . $array['ID']; ?>" onclick="return deleteconfirm('<?php echo $array['ID']; ?>');"><span style="color:#FF0000; font-size:9px;"><?php _e('Delete', 'usces'); ?></span></a></td>
	</tr>
<?php endforeach; ?>
</table>

</div>
<!--<div class="chui">
<h3>受注詳細画面（作成中）について</h3>
<p>各行の受注番号をクリックすると受注詳細画面が表示されます。受注詳細画面では注文商品の追加、修正、削除など受注に関する全ての情報を編集することができま、問い合わせや電話での変更依頼に対応します。</p>
<p>「見積り」ステイタスを利用することで見積りをメール送信できます。見積書印刷でFAX対応も可能です。注文をいただいた場合は「受注」ステイタスに変更することで、見積りの内容がそのまま受注データとなります。</p>
<p>その他のステイタスには銀行振り込みの場合の「入金」ステイタス、発送完了した場合の「完了」、注文の「キャンセル」などがあり、各業務の終了後にステイタスを変更することを習慣付ければ、複数の担当者での業務もスムーズに行うことができます。</p>
</div>
-->
</div><!--usces_admin-->
</div><!--wrap-->
<script type="text/javascript">
//	rowSelection("mainDataTable");
</script>
