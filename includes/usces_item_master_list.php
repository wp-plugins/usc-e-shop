<?php
require_once( USCES_PLUGIN_DIR . "/classes/itemList.class.php" );
global $wpdb, $post;
$wpdb->show_errors();

$tableName = $wpdb->posts;
if( USCES_MYSQL_VERSION >= 5 ){
	$arr_column = array(
				__('item code', 'usces') => 'item_code', 
				__('item name', 'usces') => 'item_name', 
				__('SKU code', 'usces') => 'sku_key', 
				apply_filters('usces_filter_sellingprice_label', __('selling price', 'usces'), NULL, NULL) => 'price', 
				__('stock', 'usces') => 'zaiko_num', 
				__('stock status', 'usces') => 'zaiko', 
				__('Categories', 'usces') => 'category', 
				__('display status', 'usces') => 'display_status');
} else {
	$arr_column = array(
				__('item code', 'usces') => 'item_code', 
				__('page title', 'usces') => 'post_title', 
				__('SKU code', 'usces') => 'sku_key', 
				apply_filters('usces_filter_sellingprice_label', __('selling price', 'usces'), NULL, NULL) => 'price', 
				__('stock', 'usces') => 'zaiko_num', 
				__('stock status', 'usces') => 'zaiko', 
				__('Categories', 'usces') => 'category', 
				__('display status', 'usces') => 'display_status');
}

$DT = new dataList($tableName, $arr_column);
$res = $DT->MakeTable();
$arr_search = $DT->GetSearchs();
$arr_status = get_option('usces_management_status');
$arr_header = $DT->GetListheaders();
$dataTableNavigation = $DT->GetDataTableNavigation();
$rows = $DT->rows;
$zaiko_status = get_option('usces_zaiko_status');
$status = isset($_REQUEST['usces_status']) ? $_REQUEST['usces_status'] : $DT->get_action_status();
$message = isset($_REQUEST['usces_message']) ? urldecode($_REQUEST['usces_message']) : $DT->get_action_message();
$curent_url = urlencode(USCES_ADMIN_URL . '?' . $_SERVER['QUERY_STRING']);
//20101111ysk start
$usces_opt_item = get_option('usces_opt_item');
//20101111ysk end
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

	$("#aAdditionalURLs").click(function () {
		$("#AdditionalURLs").toggle();
	});
	
	$("input[name='allcheck']").click(function () {
		if( $(this).attr("checked") ){
			$("input[name*='listcheck']").attr({checked: true});
		}else{
			$("input[name*='listcheck']").attr({checked: false});
		}
	});
	
	$("#searchselect").change(function () {
		operation.change_search_field();
	});

	$("#changeselect").change(function () {
		operation.change_collective_field();
	});

	$("#collective_change").click(function () {
		if( $("input[name*='listcheck']:checked").length == 0 ) {
			alert("<?php _e('no items are selected', 'usces'); ?>");
			$("#itemlistaction").val('');
			return false;
		}
		var coll = $("#changeselect").val();
		var mes = '';
		if( coll == 'zaiko' ){
			mes = <?php echo sprintf(__("'Stock status of items which you have checked will be changed into ' + %s + ' Stock status of each SKU will be all shown as ' + %s + ' Do you agree with this oparation?'", 'usces'),
							'$("select\[name=\"change\[word\]\[zaiko\]\"\] option:selected").html()',
							'$("select\[name=\"change\[word\]\[zaiko\]\"\] option:selected").html()'); ?>;
		}else if( coll == 'display_status' ){
			mes = <?php echo sprintf(__("'Are you sure of changing all the items you have checked in to ' + %s + ' ?'", 'usces'),
							'$("select\[name=\"change\[word\]\[display_status\]\"\] option:selected").html()'); ?>;
		}else if(coll == 'delete'){
			mes = <?php _e("'Are you sure of deleting all the items you have checked in bulk?'", 'usces'); ?>;
		}else{
			$("#itemlistaction").val('');
			return false;
		}
		if( !confirm(mes) ){
			$("#itemlistaction").val('');
			return false;
		}
		$("#itemlistaction").val('collective');
		return true;
	});

	operation = {
		change_search_field :function (){
		
			var label = '';
			var html = '';
			var column = $("#searchselect").val();
			
			if( column == 'item_name' ) {
				label = '<?php _e('key words', 'usces'); ?>';
				html = '<input name="search[word][item_name]" type="text" value="<?php if(isset($arr_search['word']['item_name'])) echo esc_attr($arr_search['word']['item_name']); ?>" class="searchword" maxlength="50" />';
			}else if( column == 'item_code' ) {
				label = '<?php _e('key words', 'usces'); ?>';
				html = '<input name="search[word][item_code]" type="text" value="<?php if(isset($arr_search['word']['item_code'])) echo esc_attr($arr_search['word']['item_code']); ?>" class="searchword" maxlength="50" />';
			}else if( column == 'post_title' ) {
				label = '<?php _e('key words', 'usces'); ?>';
				html = '<input name="search[word][post_title]" type="text" value="<?php if(isset($arr_search['word']['post_title'])) echo esc_attr($arr_search['word']['post_title']); ?>" class="searchword" maxlength="50" />';
			}else if( column == 'zaiko_num' ) {
				label = '';
				html = '';
			}else if( column == 'zaiko' ) {
				label = '';
				html = '<select name="search[word][zaiko]" class="searchselect">';
		<?php foreach($zaiko_status as $zkey => $zvalue){ 
				if(isset($arr_search['word']['zaiko']) && $zkey == $arr_search['word']['zaiko']){
					$zselected = ' selected="selected"';
				}else{
					$zselected = '';
				}
		?>
				html += '<option value="<?php echo esc_attr($zkey); ?>"<?php echo $zselected ?>><?php echo esc_html($zvalue); ?></option>';
		<?php } ?>
				html += '</select>';
			}else if( column == 'category' ) {
				label = '';
				html = '<select name="search[word][category]" class="searchselect">';
		<?php 
			$categories = get_categories(array('child_of' => USCES_ITEM_CAT_PARENT_ID));
			foreach($categories as $ckey => $cvalue){ 
				if(isset($arr_search['word']['category']) && $cvalue->term_id == $arr_search['word']['category']){
					$cselected = ' selected="selected"';
				}else{
					$cselected = '';
				}
		?>
				html += '<option value="<?php echo $cvalue->term_id; ?>"<?php echo $cselected ?>><?php echo esc_html($cvalue->name); ?></option>';
		<?php } ?>
				html += '</select>';
			}else if( column == 'display_status' ) {
				label = '';
				html = '<select name="search[word][display_status]" class="searchselect">';
				html += '<option value="publish"<?php if(isset($arr_search['word']['display_status']) && 'publish' == $arr_search['word']['display_status']) echo ' selected="selected"'; ?>><?php _e('Published', 'usces'); ?></option>';
				html += '<option value="future"<?php if(isset($arr_search['word']['display_status']) && 'future' == $arr_search['word']['display_status']) echo ' selected="selected"'; ?>><?php _e('Scheduled', 'usces'); ?></option>';
				html += '<option value="draft"<?php if(isset($arr_search['word']['display_status']) && 'draft' == $arr_search['word']['display_status']) echo ' selected="selected"'; ?>><?php _e('Draft', 'usces'); ?></option>';
				html += '<option value="pending"<?php if(isset($arr_search['word']['display_status']) && 'pending' == $arr_search['word']['display_status']) echo ' selected="selected"'; ?>><?php _e('Pending Review', 'usces'); ?></option>';
				html += '<option value="private"<?php if(isset($arr_search['word']['display_status']) && 'private' == $arr_search['word']['display_status']) echo ' selected="selected"'; ?>><?php _e('Closed', 'usces'); ?></option>';
				html += '<option value="trash"<?php if(isset($arr_search['word']['display_status']) && 'trash' == $arr_search['word']['display_status']) echo ' selected="selected"'; ?>><?php _e('Trash', 'usces'); ?></option>';
				html += '</select>';
			} 
			
			$("#searchlabel").html( label );
			$("#searchfield").html( html );
		
		}, 
		
		change_collective_field :function (){
		
			var label = '';
			var html = '';
			var column = $("#changeselect").val();
			
			if( column == 'zaiko' ) {
				label = '';
				html = '<select name="change[word][zaiko]" class="searchselect">';
		<?php foreach($zaiko_status as $zkey => $zvalue){ ?>
				html += '<option value="<?php echo $zkey; ?>"><?php echo esc_html($zvalue); ?></option>';
		<?php } ?>
				html += '</select>';
			}else if( column == 'display_status' ) {
				label = '';
				html = '<select name="change[word][display_status]" class="searchselect">';
				html += '<option value="publish"><?php _e('Published', 'usces'); ?></option>';
				html += '<option value="private"><?php _e('Closed', 'usces'); ?></option>';
				html += '</select>';
			}else if( column == 'delete' ) {
				label = '';
				html = '';
			} 
			
			$("#changelabel").html( label );
			$("#changefield").html( html );
		
		}
	};


	/******************************************************************/
	// ダイアログ生成
	/******************************************************************/
	$("#upload_dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 350,
		modal: true,
		buttons: {
			Cancel: function() {
				$(this).dialog('close');
			}
		},
		close: function() {
			$("#usces_upcsv").val('');
		}
	});
//20101111ysk start
	//$('#up_dlg').click(function() {
	$('#up_itemlist').click(function() {
//20101111ysk end
			$('#upload_dialog').dialog( 'option' , 'title' , '<?php _e('Collective registration item', 'usces'); ?>' );
			$('#upload_dialog').dialog( 'option' , 'width' , 500 );
			$('#dialogExp').html( '<?php _e('Upload prescribed CSV file and perform the collective registration of the article.<br />Please choose a file, and push the registration start.', 'usces'); ?>' );
			$('#upload_dialog').dialog( 'open' );
	});
//20101111ysk start
	$("#dlItemListDialog").dialog({
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
	$('#dl_item').click(function() {
//20120123ysk start 0000385
		//var args = "&search[column]="+$(':input[name="search[column]"]').val()
		//	+"&search[word]["+$("#searchselect").val()+"]="+$(':input[name="search[word]['+$("#searchselect").val()+']"]').val()
		//	+"&searchSwitchStatus="+$(':input[name="searchSwitchStatus"]').val()
		//	+"&ftype="+$(':input[name="ftype_item[]"]:checked').val();//20120123ysk 0000385
		var args = "&search[column]="+$(':input[name="search[column]"]').val()
			+"&search[word]["+$("#searchselect").val()+"]="+$(':input[name="search[word]['+$("#searchselect").val()+']"]').val()
			+"&searchSwitchStatus="+$(':input[name="searchSwitchStatus"]').val()
			+"&ftype=csv";
//20120123ysk end
		if($('#chk_header').attr('checked')) {
			args += '&chk_header=on';
		}
		location.href = "<?php echo USCES_ADMIN_URL; ?>?page=usces_itemedit&action=dlitemlist&noheader=true"+args<?php echo apply_filters( 'usces_filter_item_list_dlargs', '' ); ?>;
	});
	$('#dl_itemlist').click(function() {
		$('#dlItemListDialog').dialog('open');
	});
//20101111ysk end
<?php echo apply_filters( 'usces_filter_item_list_page_js', '' ); ?>
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

function deleteconfirm(item_id){
	if(confirm(<?php _e("'Are you sure of deleting the item, item code ' + item_id + ' ?'", 'usces'); ?>)){
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
	
	operation.change_search_field();
		
});
</script>
<div class="wrap">
<div class="usces_admin">
<form action="<?php echo USCES_ADMIN_URL.'?page=usces_itemedit'; ?>" method="post" name="tablesearch">
<h2>Welcart Shop <?php _e('Item list','usces'); ?></h2>
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
		<table id="search_table">
		<tr>
		<td><?php _e('search fields', 'usces'); ?></td>
		<td><select name="search[column]" class="searchselect" id="searchselect">
		    <option value="none"> </option>
<?php foreach ($arr_column as $key => $value):
		if($value == $arr_search['column']){
			$selected = ' selected="selected"';
		}else{
			$selected = '';
		}
		if( $value != 'sku_key' && $value != 'price' ) :
?>
	<?php if( $value == 'zaiko_num' ){ ?>
		    <option value="<?php echo esc_attr($value); ?>"<?php echo $selected ?>><?php _e('items without stock', 'usces'); ?></option>
	<?php }else if( USCES_MYSQL_VERSION < 5 && $value == 'item_code' ){ continue; ?>
	<?php }else{ ?>
		    <option value="<?php echo esc_attr($value); ?>"<?php echo $selected ?>><?php echo esc_html($key); ?></option>
	<?php } ?>
<?php endif; endforeach; ?>
    	</select></td>
		<td id="searchlabel"></td>
		<td id="searchfield"></td>
		<td><input name="searchIn" type="submit" class="searchbutton" value="<?php _e('Search', 'usces'); ?>" />
		<input name="searchOut" type="submit" class="searchbutton" value="<?php _e('Cancellation', 'usces'); ?>" />
		<input name="searchSwitchStatus" id="searchSwitchStatus" type="hidden" value="<?php echo $DT->searchSwitchStatus; ?>" />
		</td>
		</tr>
		</table>
		<table id="change_table">
		<tr>
		<td><?php _e('Oparation in bulk', 'usces'); ?></td>
		<td><select name="allchange[column]" class="searchselect" id="changeselect">
		    <option value="none"> </option>
		    <option value="zaiko"><?php _e('Changes in stock status', 'usces'); ?></option>
		    <option value="display_status"><?php _e('Edit the display status', 'usces'); ?></option>
		    <option value="delete"><?php _e('Delete in bulk', 'usces'); ?></option>
    	</select></td>
		<td id="changelabel"></td>
		<td id="changefield"></td>
		<td><input name="collective" type="submit" class="searchbutton" id="collective_change" value="<?php _e('start', 'usces'); ?>" />
<!--20101111ysk start-->
<!--	<a href="#" id="up_dlg"><?php _e('Collective registration item', 'usces'); ?></a>-->
		</td>
		</tr>
		</table>
		<table id="dl_list_table">
		<tr>
		<?php echo apply_filters( 'usces_filter_dl_item_list_table', '' ); ?>
		<td><input type="button" id="up_itemlist" class="searchbutton" value="<?php _e('Collective registration item', 'usces'); ?>" /></td>
		<td><input type="button" id="dl_itemlist" class="searchbutton" value="<?php _e('Download Item List', 'usces'); ?>" /></td>
		</tr>
		</table>
<!--20101111ysk end-->
		</td>
		</tr>
		</table>
		<input name="action" id="itemlistaction" type="hidden" />
</div>
</div>

<table id="mainDataTable" cellspacing="1">
	<tr>
		<th scope="col"><input name="allcheck" type="checkbox" value="" /></th>
		<th scope="col">&nbsp;</th>
<?php foreach ( (array)$arr_header as $key => $value ) : ?>
	<?php if ( $key == 'item_code' ) : ?>
			<th scope="col"><?php echo $value ?>&nbsp;/&nbsp;
	<?php elseif ( $key == 'item_name' || $key == 'post_title' ) : ?>
			<?php echo $value; ?></th>
	<?php elseif ( $key == 'price' ) : ?>
			<th scope="col"><?php echo $value; ?>(<?php usces_crcode(); ?>)</th>
	<?php else : ?>
			<th scope="col"><?php echo $value; ?></th>
	<?php endif; ?>
<?php endforeach; ?>
	</tr>
<?php foreach ( (array)$rows as $array ) :
		$pctid = (int)$this->get_mainpictid($array['item_code']); 
		$post = get_post($array['ID']);
		$array['sku'] = $skus = $this->get_skus( $array['ID'], 'sort' );
		$array['category'] = "";
		$array['post_status'] = $post->post_status;
?>
	<tr>
	<td width="20px" align="center"><input name="listcheck[]" type="checkbox" value="<?php echo (int)$array['ID']; ?>" /></td>
	<td width="50px"><a href="<?php echo USCES_ADMIN_URL.'?page=usces_itemedit&action=edit&post='.$array['ID'].'&usces_referer='.$curent_url; ?>" title="<?php echo esc_attr($array['item_name']); ?>"><?php echo wp_get_attachment_image( $pctid, array(50, 50), true ); ?></a></td>
	<?php foreach ( (array)$array as $key => $value ) : ?>
		<?php if( $key == 'item_code') : ?>
			<?php if( USCES_MYSQL_VERSION < 5 ){ $value = get_post_meta($array['ID'], '_itemCode', true); $array['item_code'] = $value; } ?>
			<td class="item">
			<?php if( $value != '' ) : ?> 
				<strong><?php echo esc_html($value); ?></strong>
			<?php else : ?> 
				&nbsp;
			<?php endif; ?>
			<br />
		<?php elseif( $key == 'item_name' ) : ?>
			<?php if( $value != '' ) : ?> 
				<strong><?php echo esc_html($value); ?></strong>
			<?php else : ?> 
				&nbsp;
			<?php endif; ?>
			<ul class="item_list_navi">
				<li><a href="<?php echo USCES_ADMIN_URL.'?page=usces_itemedit&action=edit&post='.$array['ID'].'&usces_referer='.$curent_url; ?>"><?php _e('edit', 'usces'); ?></a></li>
				<li>&nbsp;|&nbsp;</li>
				<!--<li><a href="<?php echo wp_nonce_url("post.php?action=delete&amp;post=".$array['ID'], 'delete-post_' . $array['ID']); ?>" onclick="return deleteconfirm('<?php echo esc_attr($array['item_code']); ?>');"><?php _e('Delete', 'usces'); ?></a></li>-->
<?php
			if ( current_user_can('delete_post', $post->ID) ) {
				if ( 'trash' == $post->post_status ){
					$actions['untrash'] = "<li><a title='" . esc_attr(__('Restore this post from the Trash')) . "' href='" . wp_nonce_url("post.php?action=untrash&amp;post=$post->ID", 'untrash-post_' . $post->ID) . "'>" . __('Restore') . "</a></li><li>&nbsp;|&nbsp;</li>";
					echo $actions['untrash'];
				}elseif ( EMPTY_TRASH_DAYS ){
					$actions['trash'] = "<li><a class='submitdelete' title='" . esc_attr(__('Move this post to the Trash')) . "' href='" . get_delete_post_link($post->ID) . "'>" . __('Trash') . "</a></li>";
					echo $actions['trash'];
				}
				if ( 'trash' == $post->post_status || !EMPTY_TRASH_DAYS ){
					$actions['delete'] = "<li><a class='submitdelete' title='" . esc_attr(__('Delete this post permanently')) . "' href='" . wp_nonce_url("post.php?action=delete&amp;post=$post->ID", 'delete-post_' . $post->ID) . "'>" . __('Delete Permanently') . "</a></li>";
					echo $actions['delete'];
				}
			}
?>
			</ul>
			</td>
			
		<?php elseif( $key == 'post_title' ) : ?>
			<?php if( $post->post_title != '' ) : ?> 
				<strong><?php echo esc_html($post->post_title); ?></strong>
			<?php else : ?> 
				&nbsp;
			<?php endif; ?>
			<ul class="item_list_navi">
				<li><a href="<?php echo USCES_ADMIN_URL.'?page=usces_itemedit&action=edit&post='.$array['ID'].'&usces_referer='.$curent_url; ?>"><?php _e('edit', 'usces'); ?></a></li>
				<li>&nbsp;|&nbsp;</li>
				<li><a href="<?php echo wp_nonce_url("post.php?action=delete&amp;post=".$array['ID'], 'delete-post_' . $array['ID']); ?>" onclick="return deleteconfirm('<?php echo esc_attr($array['item_code']); ?>');"><?php _e('Delete', 'usces'); ?></a></li>
			</ul>
			</td>
			
		<?php elseif( $key == 'sku' ): 	$no_sku = ( 0 === count($value) ) ? "&nbsp;" : ""; ?>
		
			<td class="sku">
			<?php $i=0; foreach((array)$value as $key => $sv) { $bgc = ($i%2 == 1) ? ' bgc1' : ' bgc2'; $i++; ?>
				<div class="skuline<?php echo $bgc; ?>"><?php echo esc_html($sv['code']); ?></div>
			<?php } echo $no_sku; ?>
			</td>
			<td class="price">
			<?php $i=0; foreach((array)$value as $key => $sv) { $bgc = ($i%2 == 1) ? ' bgc1' : ' bgc2'; $i++; ?>
				<div class="priceline<?php echo $bgc; ?>"><?php usces_crform( $sv['price'], true, false ); ?></div>
			<?php } echo $no_sku; ?>
			</td>
			<td class="zaikonum">
			<?php $i=0; foreach((array)$value as $key => $sv) { $bgc = ($i%2 == 1) ? ' bgc1' : ' bgc2'; $i++; ?>
				<div class="priceline<?php echo $bgc; ?>"><?php if( !WCUtils::is_blank($sv['stocknum']) ) esc_html_e($sv['stocknum']); ?></div>
			<?php } echo $no_sku; ?>
			</td>
			<td class="zaiko">
			<?php $i=0; foreach((array)$value as $key => $sv) { $zaikokey = $sv['stock']; $bgc = ($i%2 == 1) ? ' bgc1' : ' bgc2'; $i++; ?>
				<div class="zaikoline<?php echo $bgc; ?>"><?php echo esc_html($zaiko_status[$zaikokey]); ?></div>
			<?php } echo $no_sku; ?>
			</td>
		<?php elseif( $key == 'category' ) : ?>
			<td class="listcat">
			<?php
				$cat_ids = wp_get_post_categories($array['ID']);
				if ( !empty( $cat_ids ) ) {
					$out = array();
					foreach ( $cat_ids as $id )
						$out[] = get_cat_name($id);
						echo join( ', ', $out );
				} else {
					_e('Uncategorized');
				}
			?>
			</td>
		<?php elseif( $key == 'post_status' ): ?>
			<td><?php
			 		switch ($value){
						case 'publish':
							echo __('Published', 'usces');
							break;
						case 'future':
							echo __('Scheduled', 'usces');
							break;
						case 'draft':
							echo __('Draft', 'usces');
							break;
						case 'pending':
							echo __('Pending Review', 'usces');
							break;
						case 'trash':
							echo __('Trash', 'usces');
							break;
						default:
							echo __('Closed', 'usces');
					} ?><?php if( !empty( $post->post_password) ){echo '<br />'.__('Password protected');} ?></td>
		<?php endif; ?>
<?php endforeach; ?>
	</tr>
<?php endforeach; ?>
</table>

</div>
<?php echo apply_filters( 'usces_filter_item_list_footer', '' ); ?>
</form>
<div id="upload_dialog">
	<p id="dialogExp"></p>
	<form action="<?php echo USCES_ADMIN_URL; ?>" method="post" enctype="multipart/form-data" name="upform" id="upform">
	<input name="usces_upcsv" type="file" id="usces_upcsv" style="width:100%" />
<?php echo apply_filters( 'usces_filter_item_list_upload_dialog', '' ); ?>
	<input name="itemcsv" type="submit" id="upcsv" value="<?php _e('Registration start', 'usces'); ?>" />
	<input name="page" type="hidden" value="usces_itemedit" />
	<input name="action" type="hidden" value="itemcsv" />
	</form>
	<p><?php _e('Indication is updated after upload completion.', 'usces'); ?></p>
	<p><?php _e('Look at log to know the registration situation.(usc-e-shop/logs/itemcsv_log.txt)<br />The log is updated, overwrite, every upload.', 'usces'); ?></p>
</div>
<!--20101111ysk start-->
<div id="dlItemListDialog" title="<?php _e('Download Item List', 'usces'); ?>">
	<p><?php _e('Choose the file format, and push the download.', 'usces'); ?></p>
	<fieldset>
		<label for="chk_header"><input type="checkbox" class="check_item" id="chk_header" value="date"<?php if($usces_opt_item['chk_header'] == 1) echo ' checked'; ?> /><?php _e('To add a subject title at the first line','usces'); ?></label>
<?php echo apply_filters( 'usces_filter_item_list_download_dialog', '' ); ?>
<!--20120123ysk start 0000385-->
		<input type="button" id="dl_item" value="<?php _e('Download', 'usces'); ?>" />
<!--20120123ysk end-->
	</fieldset>
</div>
<!--20101111ysk end-->

</div><!--usces_admin-->
</div><!--wrap-->
[memory peak usage] <?php echo round(memory_get_peak_usage()/1048576, 1); ?>Mb