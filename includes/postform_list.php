<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';

require_once( USCES_PLUGIN_DIR . "/classes/postformList.class.php" );
global $wpdb;

$tableName = $wpdb->prefix . "usces_postform";
$arr_column = array(
			__('ID', 'usces') => 'ID', 
			__('date', 'usces') => 'date', 
			__('membership number', 'usces') => 'mem_id', 
			__('name', 'usces') => 'name', 
			__('エリア', 'usces') => 'area', 
			__('場所', 'usces') => 'location', 
			__('Points', 'usces') => 'points', 
			__('Processing', 'usces') => 'status');

$DT = new dataList($tableName, $arr_column);
$res = $DT->MakeTable();
$arr_search = $DT->GetSearchs();
$arr_header = $DT->GetListheaders();
$dataTableNavigation = $DT->GetDataTableNavigation();
$rows = $DT->rows;
$curent_url = urlencode(USCES_ADMIN_URL . '?' . $_SERVER['QUERY_STRING']);
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

function deleteconfirm(kpf_id){
	if(confirm(<?php _e("'Are you sure of deleting your membership number ' + kpf_id + ' ?'", 'usces'); ?>)){
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

<h2>Welcart Management <?php _e('釣果投稿リスト', 'usces'); ?></h2>
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
	<form action="<?php echo USCES_ADMIN_URL.'?page=usces_kpflist'; ?>" method="post" name="tablesearch">
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
		    <option value="<?php echo esc_attr($value); ?>"<?php echo $selected; ?>><?php echo esc_html($key); ?></option>
<?php endforeach; ?>
    	</select></td>
		<td><?php _e('key words', 'usces'); ?></td>
		<td><input name="search[word]" type="text" value="<?php echo esc_attr($arr_search['word']); ?>" class="searchword" maxlength="50" /></td>
		<td><input name="searchIn" type="submit" class="searchbutton" value="<?php _e('Search', 'usces'); ?>" />
		<input name="searchOut" type="submit" class="searchbutton" value="<?php _e('Cancellation', 'usces'); ?>" />
		<input name="searchSwitchStatus" id="searchSwitchStatus" type="hidden" value="<?php echo esc_attr($DT->searchSwitchStatus); ?>" />
		</td>
		</tr>
		</table>
		<table id="period_table">
		<tr>
		<td><?php _e('Period', 'usces'); ?></td>
		<td><select name="search[period]" class="searchselect">
<?php foreach ((array)$DT->arr_period as $key => $value):
		if($key == $arr_search['period']){
			$selected = ' selected="selected"';
		}else{
			$selected = '';
		}
?>
		    <option value="<?php echo esc_attr($key); ?>"<?php echo $selected ?>><?php echo esc_html($value); ?></option>
<?php endforeach; ?>
		</select></td>
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
		<td><a href="<?php echo USCES_ADMIN_URL.'?page=usces_kpflist&kpf_action=edit&kpf_id=' . $array['ID'] . '&usces_referer=' . $curent_url; ?>"><?php echo esc_html($value); ?></a></td>
		<?php elseif( $key == 'name' ): ?>
		<td>
		<?php
			$names = explode(' ', $value);
			usces_localized_name( $names[0], $names[1]);
		?>
		</td>
		<?php elseif( $key == 'point' ): ?>
		<td class="right"><?php echo $value; ?></td>
		<?php else: ?>
		<td><?php echo $value; ?></td>
		<?php endif; ?>
<?php endforeach; ?>
		<td><a href="<?php echo USCES_ADMIN_URL.'?page=usces_kpflist&kpf_action=delete&kpf_id=' . $array['ID']; ?>" onclick="return deleteconfirm('<?php echo $array['ID']; ?>');"><span style="color:#FF0000; font-size:9px;"><?php _e('Delete', 'usces'); ?></span></a></td>
	</tr>
<?php endforeach; ?>
</table>

</div>

</div><!--usces_admin-->
</div><!--wrap-->
