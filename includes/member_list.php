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

//20100908ysk start
$csmb_meta = usces_has_custom_field_meta('member');
$usces_opt_member = get_option('usces_opt_member');
$chk_mem = $usces_opt_member['chk_mem'];
//20100908ysk end
//20110411ysk start
$applyform = usces_get_apply_addressform($this->options['system']['addressform']);
//20110411ysk end
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
		
//20100908ysk start
	$("#dlMemberListDialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 400,
		width: 700,
		resizable: true,
		modal: true,
		buttons: {
			'<?php _e('close', 'usces'); ?>': function() {
				$(this).dialog('close');
			}
		},
		close: function() {
		}
	});
	$('#dl_mem').click(function() {
//20120123ysk start 0000385
		//var args = "&search[column]="+$(':input[name="search[column]"]').val()
		//	+"&search[word]="+$(':input[name="search[word]"]').val()
		//	+"&searchSwitchStatus="+$(':input[name="searchSwitchStatus"]').val()
		//	+"&ftype="+$(':input[name="ftype_mem[]"]:checked').val();
		var args = "&search[column]="+$(':input[name="search[column]"]').val()
			+"&search[word]="+$(':input[name="search[word]"]').val()
			+"&searchSwitchStatus="+$(':input[name="searchSwitchStatus"]').val()
			+"&ftype=csv";
//20120123ysk end
		$('*[class=check_member]').each(function(i) {
			if($(this).attr('checked')) {
				args += '&check['+$(this).val()+']=on';
			}
		});
		location.href = "<?php echo USCES_ADMIN_URL; ?>?page=usces_memberlist&member_action=dlmemberlist&noheader=true"+args+"&wc_nonce=<?php echo wp_create_nonce( 'dlmemberlist' ); ?>";
	});
	$('#dl_memberlist').click(function() {
		$('#dlMemberListDialog').dialog('open');
	});
//20100908ysk end
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
<!--20100908ysk start-->
		<table id="dl_list_table">
		<tr>
		<td><input type="button" id="dl_memberlist" class="searchbutton" value="<?php _e('Download Member List', 'usces'); ?>" /></td>
		</tr>
		</table>
<!--20100908ysk end-->
		
	</form>
</div>
</div>

<table id="mainDataTable" cellspacing="1">
	<tr>
<?php foreach ( (array)$arr_header as $value ) : ?>
		<th scope="col"><?php echo $value; ?></th>
<?php endforeach; ?>
		<th scope="col">&nbsp;</th>
	</tr>
<?php foreach ( (array)$rows as $array ) : ?>
	<tr>
	<?php foreach ( (array)$array as $key => $value ) : ?>
		<?php if( WCUtils::is_blank($value) ) $value = '&nbsp;'; ?>
		<?php if( $key == 'ID' ): ?>
		<td><a href="<?php echo USCES_ADMIN_URL.'?page=usces_memberlist&member_action=edit&member_id=' . esc_attr($value); ?>"><?php esc_html_e($value); ?></a></td>
		<?php elseif( $key == 'name' ): ?>
		<td>
		<?php
			$names = explode(' ', $value);
			usces_localized_name( esc_html($names[0]), esc_html($names[1]));
		?>
		</td>
		<?php elseif( $key == 'address' ): 
			$pos = strpos( $value, __('-- Select --','usces') );
			if( $pos !== false ) $value = '&nbsp;';
		?>
		<td><?php esc_html_e($value); ?></td>
		<?php elseif( $key == 'point' ): ?>
		<td class="right"><?php esc_html_e($value); ?></td>
		<?php else: ?>
		<td><?php esc_html_e($value); ?></td>
		<?php endif; ?>
<?php endforeach; ?>
	<td><a href="<?php echo USCES_ADMIN_URL.'?page=usces_memberlist&member_action=delete&member_id=' . $array['ID'] . '&wc_nonce=' . wp_create_nonce( 'delete_member' ); ?>" onclick="return deleteconfirm('<?php echo $array['ID']; ?>');"><span style="color:#FF0000; font-size:9px;"><?php _e('Delete', 'usces'); ?></span></a></td>
	</tr>
<?php endforeach; ?>
</table>

</div>
<!--20100908ysk start-->
<div id="dlMemberListDialog" title="<?php _e('Download Member List', 'usces'); ?>">
	<p><?php _e('Select the item you want, please press the download.', 'usces'); ?></p>
	<fieldset>
		<input type="button" id="dl_mem" value="<?php _e('Download', 'usces'); ?>" />
	</fieldset>
	<fieldset><legend><?php _e('Membership information', 'usces'); ?></legend>
		<label for="chk_mem[ID]"><input type="checkbox" class="check_member" id="chk_mem[ID]" value="ID" checked disabled /><?php _e('membership number', 'usces'); ?></label>
		<label for="chk_mem[email]"><input type="checkbox" class="check_member" id="chk_mem[email]" value="email"<?php usces_checked($chk_mem, 'email'); ?> /><?php _e('e-mail', 'usces'); ?></label>
<?php 
	if(!empty($csmb_meta)) {
		foreach($csmb_meta as $key => $entry) {
			if($entry['position'] == 'name_pre') {
//20110208ysk start
				$csmb_key = 'csmb_'.$key;
				//$checked = ($chk_mem[$entry['name']] == 1) ? ' checked' : '';
				//$checked = ($chk_mem[$csmb_key] == 1) ? ' checked' : '';
				$checked = usces_checked( $chk_mem, $csmb_key, 'return' );
				$name = $entry['name'];
				//echo '<label for="chk_mem['.$name.']"><input type="checkbox" class="check_member" id="chk_mem['.$name.']" value="'.$name.'"'.$checked.' />'.$name.'</label>';
				echo '<label for="chk_mem['.$csmb_key.']"><input type="checkbox" class="check_member" id="chk_mem['.esc_attr($csmb_key).']" value="'.esc_attr($csmb_key).'"'.$checked.' />'.esc_html($name).'</label>'."\n";//20111116ysk 0000302
//20110208ysk end
			}
		}
	}
//20110411ysk start
?>
		<label for="chk_mem[name]"><input type="checkbox" class="check_member" id="chk_mem[name]" value="name" checked disabled /><?php _e('name', 'usces'); ?></label>
<?php 
	switch($applyform) {
	case 'JP':
?>
		<label for="chk_mem[kana]"><input type="checkbox" class="check_member" id="chk_mem[kana]" value="kana"<?php usces_checked($chk_mem, 'kana'); ?> /><?php _e('furigana','usces'); ?></label>
<?php 
		break;
	}
//20110411ysk end

	if(!empty($csmb_meta)) {
		foreach($csmb_meta as $key => $entry) {
			if($entry['position'] == 'name_after') {
//20110208ysk start
				$csmb_key = 'csmb_'.$key;
				//$checked = ($chk_mem[$entry['name']] == 1) ? ' checked' : '';
				//$checked = ($chk_mem[$csmb_key] == 1) ? ' checked' : '';
				$checked = usces_checked( $chk_mem, $csmb_key, 'return' );
				$name = $entry['name'];
				//echo '<label for="chk_mem['.$name.']"><input type="checkbox" class="check_member" id="chk_mem['.$name.']" value="'.$name.'"'.$checked.' />'.$name.'</label>';
				echo '<label for="chk_mem['.esc_attr($csmb_key).']"><input type="checkbox" class="check_member" id="chk_mem['.esc_attr($csmb_key).']" value="'.esc_attr($csmb_key).'"'.$checked.' />'.esc_html($name).'</label>'."\n";//20111116ysk 0000302
//20110208ysk end
			}
		}
	}

//20110411ysk start
	switch($applyform) {
	case 'JP':
?>
		<label for="chk_mem[zip]"><input type="checkbox" class="check_member" id="chk_mem[zip]" value="zip"<?php usces_checked($chk_mem, 'zip'); ?> /><?php _e('Zip/Postal Code', 'usces'); ?></label>
		<label for="chk_mem[country]"><input type="checkbox" class="check_member" id="chk_mem[country]" value="country" checked disabled /><?php _e('Country', 'usces'); ?></label>
		<label for="chk_mem[pref]"><input type="checkbox" class="check_member" id="chk_mem[pref]" value="pref" checked disabled /><?php _e('Province', 'usces'); ?></label>
		<label for="chk_mem[address1]"><input type="checkbox" class="check_member" id="chk_mem[address1]" value="address1" checked disabled /><?php _e('city', 'usces'); ?></label>
		<label for="chk_mem[address2]"><input type="checkbox" class="check_member" id="chk_mem[address2]" value="address2" checked disabled /><?php _e('numbers', 'usces'); ?></label>
		<label for="chk_mem[address3]"><input type="checkbox" class="check_member" id="chk_mem[address3]" value="address3" checked disabled /><?php _e('building name', 'usces'); ?></label>
		<label for="chk_mem[tel]"><input type="checkbox" class="check_member" id="chk_mem[tel]" value="tel"<?php usces_checked($chk_mem, 'tel'); ?> /><?php _e('Phone number', 'usces'); ?></label>
		<label for="chk_mem[fax]"><input type="checkbox" class="check_member" id="chk_mem[fax]" value="fax"<?php usces_checked($chk_mem, 'fax'); ?> /><?php _e('FAX number', 'usces'); ?></label>
<?php 
		break;
	case 'US':
	default:
?>
		<label for="chk_mem[address2]"><input type="checkbox" class="check_member" id="chk_mem[address2]" value="address2" checked disabled /><?php _e('Address Line1', 'usces'); ?></label>
		<label for="chk_mem[address3]"><input type="checkbox" class="check_member" id="chk_mem[address3]" value="address3" checked disabled /><?php _e('Address Line2', 'usces'); ?></label>
		<label for="chk_mem[address1]"><input type="checkbox" class="check_member" id="chk_mem[address1]" value="address1" checked disabled /><?php _e('city', 'usces'); ?></label>
		<label for="chk_mem[pref]"><input type="checkbox" class="check_member" id="chk_mem[pref]" value="pref" checked disabled /><?php _e('State', 'usces'); ?></label>
		<label for="chk_mem[country]"><input type="checkbox" class="check_member" id="chk_mem[country]" value="country" checked disabled /><?php _e('Country', 'usces'); ?></label>
		<label for="chk_mem[zip]"><input type="checkbox" class="check_member" id="chk_mem[zip]" value="zip"<?php usces_checked($chk_mem, 'zip'); ?> /><?php _e('Zip', 'usces'); ?></label>
		<label for="chk_mem[tel]"><input type="checkbox" class="check_member" id="chk_mem[tel]" value="tel"<?php usces_checked($chk_mem, 'tel'); ?> /><?php _e('Phone number', 'usces'); ?></label>
		<label for="chk_mem[fax]"><input type="checkbox" class="check_member" id="chk_mem[fax]" value="fax"<?php usces_checked($chk_mem, 'fax'); ?> /><?php _e('FAX number', 'usces'); ?></label>
<?php 
		break;
	}
//20110411ysk end

	if(!empty($csmb_meta)) {
		foreach($csmb_meta as $key => $entry) {
			if($entry['position'] == 'fax_after') {
//20110208ysk start
				$csmb_key = 'csmb_'.$key;
				//$checked = ($chk_mem[$entry['name']] == 1) ? ' checked' : '';
				//$checked = ($chk_mem[$csmb_key] == 1) ? ' checked' : '';
				$checked = usces_checked( $chk_mem, $csmb_key, 'return' );
				$name = $entry['name'];
				//echo '<label for="chk_mem['.$name.']"><input type="checkbox" class="check_member" id="chk_mem['.$name.']" value="'.$name.'"'.$checked.' />'.$name.'</label>';
				echo '<label for="chk_mem['.esc_attr($csmb_key).']"><input type="checkbox" class="check_member" id="chk_mem['.esc_attr($csmb_key).']" value="'.esc_attr($csmb_key).'"'.$checked.' />'.esc_html($name).'</label>'."\n";//20111116ysk 0000302
//20110208ysk end
			}
		}
	}
?>
		<label for="chk_mem[date]"><input type="checkbox" class="check_member" id="chk_mem[date]" value="date"<?php usces_checked($chk_mem, 'date'); ?> /><?php _e('Strated date','usces'); ?></label>
		<label for="chk_mem[point]"><input type="checkbox" class="check_member" id="chk_mem[point]" value="point"<?php usces_checked($chk_mem, 'point'); ?> /><?php _e('current point','usces'); ?></label>
		<label for="chk_mem[rank]"><input type="checkbox" class="check_member" id="chk_mem[rank]" value="rank"<?php usces_checked($chk_mem, 'rank'); ?> /><?php _e('Rank', 'usces'); ?></label>
		<?php do_action( 'usces_action_chk_mem', $chk_mem ); ?>
	</fieldset>
</div>
<!--20100908ysk end-->
</div><!--usces_admin-->
</div><!--wrap-->
<script type="text/javascript">
//	rowSelection("mainDataTable");
</script>
