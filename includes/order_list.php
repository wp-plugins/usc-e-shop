<?php
require_once( USCES_PLUGIN_DIR . "/classes/orderList.class.php" );
global $wpdb;

$tableName = $wpdb->prefix . "usces_order";
$arr_column = array(
			__('Order number', 'usces') => 'ID', 
			__('date', 'usces') => 'date', 
			__('membership number', 'usces') => 'mem_id', 
			__('name', 'usces') => 'name', 
			__('Region', 'usces') => 'pref', 
			__('shipping option', 'usces') => 'delivery_method', 
			__('Amount', 'usces').'('.__(usces_crcode( 'return' ), 'usces').')' => 'total_price', 
			__('payment method', 'usces') => 'payment_name', 
			__('transfer statement', 'usces') => 'receipt_status', 
			__('Processing', 'usces') => 'order_status', 
			apply_filters('usces_filter_admin_modified_label', __('shpping date', 'usces') ) => 'order_modified');

$DT = new dataList($tableName, $arr_column);
$res = $DT->MakeTable();
$arr_search = $DT->GetSearchs();
$arr_status = get_option('usces_management_status');
$arr_header = $DT->GetListheaders();
$dataTableNavigation = $DT->GetDataTableNavigation();
$rows = $DT->rows;
$status = $DT->get_action_status();
$message = $DT->get_action_message();
//$pref = get_option('usces_pref');
//20110331ysk start
//$pref = $usces->options['province'];
$pref = array();
$target_market = $this->options['system']['target_market'];
foreach((array)$target_market as $country) {
	$prefs = get_usces_states($country);
	if(is_array($prefs) and 0 < count($prefs)) {
		$pos = strpos($prefs[0], '--');
		if($pos !== false) array_shift($prefs);
		foreach((array)$prefs as $state) {
			$pref[] = $state;
		}
	}
}
//20110331ysk end
foreach ( (array)$this->options['payment_method'] as $id => $array ) {
	$payment_name[$id] = $this->options['payment_method'][$id]['name'];
}
$ums = get_option('usces_management_status');
foreach((array)$ums as $key => $value){
	if($key == 'noreceipt' || $key == 'receipted' || $key == 'pending'){
		$receipt_status[$key] = $value;
	}else{
		$order_status[$key] = $value;
	}
}
$order_status['new'] = __('new order', 'usces');
$curent_url = urlencode(USCES_ADMIN_URL . '?' . $_SERVER['QUERY_STRING']);

//20100908ysk start
$csod_meta = usces_has_custom_field_meta('order');
$cscs_meta = usces_has_custom_field_meta('customer');
$csde_meta = usces_has_custom_field_meta('delivery');
$usces_opt_order = get_option('usces_opt_order');
$chk_pro = $usces_opt_order['chk_pro'];
$chk_ord = $usces_opt_order['chk_ord'];
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
			alert("<?php _e('Choose the data.', 'usces'); ?>");
			$("#oederlistaction").val('');
			return false;
		}
		var coll = $("#changeselect").val();
		var mes = '';
		if( coll == 'order_reciept' ){
			mes = <?php echo sprintf(__("'Transfer status of the items which you have checked will be changed in to ' + %s + '. %sDo you agree?'", 'usces'), 
							'$("select\[name=\"change\[word\]\[order_reciept\]\"\] option:selected").html()',
							'\n\n'); ?>;
		}else if( coll == 'order_status' ){
			mes = <?php echo sprintf(__("'Data status which you have cheked will be changed in to ' + %s + '. %sDo you agree?'", 'usces'), 
							'$("select\[name=\"change\[word\]\[order_status\]\"\] option:selected").html()',
							'\n\n'); ?>;
		}else if(coll == 'delete'){
			mes = '<?php _e('Are you sure of deleting all the checked data in bulk?', 'usces'); ?>';
		}else{
			$("#oederlistaction").val('');
			return false;
		}
		if( !confirm(mes) ){
			$("#oederlistaction").val('');
			return false;
		}
		$("#oederlistaction").val('collective');
		return true;
	});

	operation = {
		change_search_field :function (){
		
			var label = '';
			var html = '';
			var column = $("#searchselect").val();
			
			if( column == 'ID' ) {
				label = '<?php _e('key words', 'usces'); ?>';
				html = '<input name="search[word][ID]" type="text" value="<?php echo esc_attr($arr_search['word']['ID']); ?>" class="searchword" maxlength="50" />';
			}else if( column == 'date' ) {
				label = '<?php _e('key words', 'usces'); ?>';
				html = '<input name="search[word][date]" type="text" value="<?php echo esc_attr($arr_search['word']['date']); ?>" class="searchword" maxlength="50" />';
			}else if( column == 'mem_id' ) {
				label = '<?php _e('key words', 'usces'); ?>';
				html = '<input name="search[word][mem_id]" type="text" value="<?php echo esc_attr($arr_search['word']['mem_id']); ?>" class="searchword" maxlength="50" />';
			}else if( column == 'name' ) {
				label = '<?php _e('key words', 'usces'); ?>';
				html = '<input name="search[word][name]" type="text" value="<?php echo esc_attr($arr_search['word']['name']); ?>" class="searchword" maxlength="50" />';
			}else if( column == 'order_modified' ) {
				label = '<?php _e('key words', 'usces'); ?>';
				html = '<input name="search[word][order_modified]" type="text" value="<?php echo esc_attr($arr_search['word']['order_modified']); ?>" class="searchword" maxlength="50" />';
			}else if( column == 'pref' ) {
				label = '';
				html = '<select name="search[word][pref]" class="searchselect">';
		<?php foreach((array)$pref as $pkey => $pvalue){ 
				if($pvalue == $arr_search['word']['pref']){
					$pselected = ' selected="selected"';
				}else{
					$pselected = '';
				}
		?>
				html += '<option value="<?php echo esc_attr($pvalue); ?>"<?php echo $pselected ?>><?php echo esc_html($pvalue); ?></option>';
		<?php } ?>
				html += '</select>';
			}else if( column == 'delivery_method' ) {
				label = '';
				html = '<select name="search[word][delivery_method]" class="searchselect">';
		<?php foreach((array)$this->options['delivery_method'] as $dkey => $dvalue){ 
				if($dvalue['id'] == $arr_search['word']['delivery_method']){
					$dselected = ' selected="selected"';
				}else{
					$dselected = '';
				}
		?>
				html += '<option value="<?php echo esc_attr($dvalue['id']); ?>"<?php echo $dselected ?>><?php echo esc_html($dvalue['name']); ?></option>';
		<?php } ?>
				html += '</select>';
			}else if( column == 'payment_name' ) {
				label = '';
				html = '<select name="search[word][payment_name]" class="searchselect">';
		<?php foreach((array)$payment_name as $pnkey => $pnvalue){ 
				if($pnvalue == $arr_search['word']['payment_name']){
					$pnselected = ' selected="selected"';
				}else{
					$pnselected = '';
				}
		?>
				html += '<option value="<?php echo esc_attr($pnvalue); ?>"<?php echo $pnselected ?>><?php echo esc_html($pnvalue); ?></option>';
		<?php } ?>
				html += '</select>';
			}else if( column == 'receipt_status' ) {
				label = '';
				html = '<select name="search[word][receipt_status]" class="searchselect">';
		<?php foreach((array)$receipt_status as $rkey => $rvalue){ 
				if($rvalue == $arr_search['word']['receipt_status']){
					$rselected = ' selected="selected"';
				}else{
					$rselected = '';
				}
		?>
				html += '<option value="<?php echo esc_attr($rvalue); ?>"<?php echo $rselected ?>><?php echo esc_html($rvalue); ?></option>';
		<?php } ?>
				html += '</select>';
			}else if( column == 'order_status' ) {
				label = '';
				html = '<select name="search[word][order_status]" class="searchselect">';
		<?php foreach((array)$order_status as $okey => $ovalue){ 
				if($ovalue == $arr_search['word']['order_status']){
					$oselected = ' selected="selected"';
				}else{
					$oselected = '';
				}
		?>
				html += '<option value="<?php echo esc_attr($ovalue); ?>"<?php echo $oselected ?>><?php echo esc_html($ovalue); ?></option>';
		<?php } ?>
				html += '</select>';
			}
			
			$("#searchlabel").html( label );
			$("#searchfield").html( html );
		
		}, 
		
		change_collective_field :function (){
		
			var label = '';
			var html = '';
			var column = $("#changeselect").val();
			
			if( column == 'order_reciept' ) {
				label = '';
				html = '<select name="change[word][order_reciept]" class="searchselect">';
		<?php foreach((array)$receipt_status as $orkey => $orvalue){ ?>
				html += '<option value="<?php echo esc_attr($orkey); ?>"><?php echo esc_html($orvalue); ?></option>';
		<?php } ?>
				html += '</select>';
			}else if( column == 'order_status' ) {
				label = '';
				html = '<select name="change[word][order_status]" class="searchselect">';
		<?php foreach((array)$order_status as $oskey => $osvalue){ ?>
				html += '<option value="<?php echo esc_attr($oskey); ?>"><?php echo esc_html($osvalue); ?></option>';
		<?php } ?>
				html += '</select>';
			}else if( column == 'delete' ) {
				label = '';
				html = '';
			} 
			
			$("#changelabel").html( label );
			$("#changefield").html( html );
		
		}
	};

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

function deleteconfirm(order_id){
	if(confirm(<?php _e("'Are you sure of deleting an order number ' + order_id + ' ?'", 'usces'); ?>)){
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
	
//20100908ysk start
	$("#dlProductListDialog").dialog({
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
	$('#dl_pro').click(function() {
		var args = "&search[column]="+$(':input[name="search[column]"]').val()
			+"&search[word]["+$("#searchselect").val()+"]="+$(':input[name="search[word]['+$("#searchselect").val()+']"]').val()
			+"&search[period]="+$(':input[name="search[period]"]').val()
			+"&searchSwitchStatus="+$(':input[name="searchSwitchStatus"]').val()
			+"&ftype="+$(':input[name="ftype_pro[]"]:checked').val();
		$(".check_product").each(function(i) {
			if($(this).attr('checked')) {
				args += '&check['+$(this).val()+']=on';
			}
		});
		location.href = "<?php echo USCES_ADMIN_URL; ?>?page=usces_orderlist&order_action=dlproductlist&noheader=true"+args;
	});
	$('#dl_productlist').click(function() {
		$('#dlProductListDialog').dialog('open');
	});

	$("#dlOrderListDialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 600,
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
	$('#dl_ord').click(function() {
		var args = "&search[column]="+$(':input[name="search[column]"]').val()
			+"&search[word]["+$("#searchselect").val()+"]="+$(':input[name="search[word]['+$("#searchselect").val()+']"]').val()
			+"&search[period]="+$(':input[name="search[period]"]').val()
			+"&searchSwitchStatus="+$(':input[name="searchSwitchStatus"]').val()
			+"&ftype="+$(':input[name="ftype_ord[]"]:checked').val();
		$(".check_order").each(function(i) {
			if($(this).attr('checked')) {
				args += '&check['+$(this).val()+']=on';
			}
		});
		location.href = "<?php echo USCES_ADMIN_URL; ?>?page=usces_orderlist&order_action=dlorderlist&noheader=true"+args;
	});
	$('#dl_orderlist').click(function() {
		$('#dlOrderListDialog').dialog('open');
	});
//20100908ysk end
});
</script>

<div class="wrap">
<div class="usces_admin">
<form action="<?php echo USCES_ADMIN_URL.'?page=usces_orderlist'; ?>" method="post" name="tablesearch">

<h2>Welcart Management <?php _e('Order List','usces'); ?></h2>
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
<?php foreach ((array)$arr_column as $key => $value):
		if($value == $arr_search['column']){
			$selected = ' selected="selected"';
		}else{
			$selected = '';
		}
		if($value == 'total_price') continue;
?>
		    <option value="<?php echo esc_attr($value); ?>"<?php echo $selected ?>><?php echo esc_html($key); ?></option>
<?php endforeach; ?>
    	</select></td>
		<td id="searchlabel"></td>
		<td id="searchfield"></td>
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
		<table id="change_table">
		<tr>
		<td><?php _e('Oparation in bulk', 'usces'); ?></td>
		<td><select name="allchange[column]" class="searchselect" id="changeselect">
		    <option value="none"> </option>
		    <option value="order_reciept"><?php _e('Edit the receiving money status', 'usces'); ?></option>
		    <option value="order_status"><?php _e('Edit of status process', 'usces'); ?></option>
		    <option value="delete"><?php _e('Delete in bulk', 'usces'); ?></option>
    	</select></td>
		<td id="changelabel"></td>
		<td id="changefield"></td>
		<td><input name="collective" type="submit" class="searchbutton" id="collective_change" value="<?php _e('start', 'usces'); ?>" />
		</td>
		</tr>
		</table>
		<input name="action" id="oederlistaction" type="hidden" />
<!--20100908ysk start-->
		<table id="dl_list_table">
		<tr>
		<td><input type="button" id="dl_productlist" class="searchbutton" value="<?php _e('Download Product List', 'usces'); ?>" /></td>
		<td><input type="button" id="dl_orderlist" class="searchbutton" value="<?php _e('Download Order List', 'usces'); ?>" /></td>
		</tr>
		</table>
<!--20100908ysk end-->
</div>
</div>

<table id="mainDataTable" cellspacing="1">
	<tr>
		<th scope="col"><input name="allcheck" type="checkbox" value="" /></th>
<?php foreach ( (array)$arr_header as $value ) : ?>
		<th scope="col"><?php echo $value ?></th>
<?php endforeach; ?>
		<th scope="col">&nbsp;</th>
	</tr>
<?php foreach ( (array)$rows as $array ) : ?>
	<tr>
	<td><input name="listcheck[]" type="checkbox" value="<?php echo $array['ID']; ?>" /></td>
	<?php foreach ( (array)$array as $key => $value ) : ?>
		<?php if( $value == '' || $value == ' ' ) $value = '&nbsp;'; ?>
		<?php if( $key == 'ID' ): ?>
		<td><a href="<?php echo USCES_ADMIN_URL.'?page=usces_orderlist&order_action=edit&order_id=' . $value.'&usces_referer='.$curent_url; ?>"><?php echo esc_html($value); ?></a></td>
		<?php elseif( $key == 'name' ): ?>
		<td><?php
			$options = get_option('usces');
			$applyform = usces_get_apply_addressform($options['system']['addressform']);
			switch ($applyform){
			case 'JP': 
				esc_html_e($value);
				break;
			case 'US':
			default:
				$names = explode(' ', $value);
				esc_html_e($names[1].' '.$names[0]);
			}
		?></td>
		<?php elseif( $key == 'total_price' ): ?>
		<td class="price"><?php usces_crform( $value, true, false ); ?></td>
		<?php elseif( $key == 'receipt_status' && $value == __('unpaid', 'usces')): ?>
		<td class="red"><?php esc_html_e($value); ?></td>
		<?php elseif( $key == 'receipt_status' && $value == 'Pending'): ?>
		<td class="red"><?php esc_html_e($value); ?></td>
		<?php elseif( $key == 'receipt_status' && $value == __('payment confirmed', 'usces')): ?>
		<td class="green"><?php esc_html_e($value); ?></td>
		<?php elseif( $key == 'order_status' && $value == __('It has sent it out.', 'usces')): ?>
		<td class="green"><?php esc_html_e($value); ?></td>
		<?php elseif( $key == 'delivery_method'): ?>
		<td class="green"><?php $delivery_method_index = $this->get_delivery_method_index($value); echo esc_html($this->options['delivery_method'][$delivery_method_index]['name']); ?></td>
		<?php elseif( $key == 'payment_name' && $value == '#none#'): ?>
		<td>&nbsp;</td>
		<?php else: ?>
		<td><?php esc_html_e($value); ?></td>
		<?php endif; ?>
<?php endforeach; ?>
	<td><a href="<?php echo USCES_ADMIN_URL.'?page=usces_orderlist&order_action=delete&order_id=' . $array['ID']; ?>" onclick="return deleteconfirm('<?php echo $array['ID']; ?>');"><span style="color:#FF0000; font-size:9px;"><?php _e('Delete', 'usces'); ?></span></a></td>
	</tr>
<?php endforeach; ?>
</table>

</div>
<!--20100908ysk start-->
<div id="dlProductListDialog" title="<?php _e('Download Product List', 'usces'); ?>">
	<p><?php _e('出力したい項目を選択して、ダウンロードを押してください。', 'usces'); ?></p>
	<fieldset>
<?php 
	if($usces_opt_order['ftype_pro'] == 'xls') {
		$ftype_pro_xls = ' checked';
		$ftype_pro_csv = '';
	} elseif($usces_opt_order['ftype_pro'] == 'csv') {
		$ftype_pro_xls = '';
		$ftype_pro_csv = ' checked';
	} else {
		$ftype_pro_xls = ' checked';
		$ftype_pro_csv = '';
	}
?>
		<label for="ftype_pro_xls"><input type="radio" name="ftype_pro[]" id="ftype_pro_xls" value="xls"<?php echo $ftype_pro_xls; ?> disabled="disabled" /><?php _e('excel', 'usces'); ?></label>
		<label for="ftype_pro_csv"><input type="radio" name="ftype_pro[]" id="ftype_pro_csv" value="csv"<?php echo $ftype_pro_csv; ?> checked="checked" /><?php _e('csv', 'usces'); ?></label>
		<input type="button" id="dl_pro" value="<?php _e('Download', 'usces'); ?>" />
	</fieldset>
	<fieldset><legend><?php _e('Header Information', 'usces'); ?></legend>
		<label for="chk_pro[ID]"><input type="checkbox" class="check_product" id="chk_pro[ID]" value="ID" checked disabled /><?php _e('order number', 'usces'); ?></label>
		<label for="chk_pro[date]"><input type="checkbox" class="check_product" id="chk_pro[date]" value="date"<?php if($chk_pro['date'] == 1) echo ' checked'; ?> /><?php _e('order date', 'usces'); ?></label>
		<label for="chk_pro[mem_id]"><input type="checkbox" class="check_product" id="chk_pro[mem_id]" value="mem_id"<?php if($chk_pro['mem_id'] == 1) echo ' checked'; ?> /><?php _e('membership number', 'usces'); ?></label>
		<label for="chk_pro[name]"><input type="checkbox" class="check_product" id="chk_pro[name]" value="name"<?php if($chk_pro['name'] == 1) echo ' checked'; ?> /><?php _e('name', 'usces'); ?></label>
		<label for="chk_pro[delivery_method]"><input type="checkbox" class="check_product" id="chk_pro[delivery_method]" value="delivery_method"<?php if($chk_pro['delivery_method'] == 1) echo ' checked'; ?> /><?php _e('shipping option','usces'); ?></label>
		<label for="chk_pro[shipping_date]"><input type="checkbox" class="check_product" id="chk_pro[shipping_date]" value="shipping_date"<?php if($chk_pro['shipping_date'] == 1) echo ' checked'; ?> /><?php _e('shpping date', 'usces'); ?></label>
	</fieldset>
	<fieldset><legend><?php _e('Product Information', 'usces'); ?></legend>
		<label for="chk_pro[item_code]"><input type="checkbox" class="check_product" id="chk_pro[item_code]" value="item_code" checked disabled /><?php _e('item code', 'usces'); ?></label>
		<label for="chk_pro[sku_code]"><input type="checkbox" class="check_product" id="chk_pro[sku_code]" value="sku_code" checked disabled /><?php _e('SKU code', 'usces'); ?></label>
		<label for="chk_pro[item_name]"><input type="checkbox" class="check_product" id="chk_pro[item_name]" value="item_name"<?php if($chk_pro['item_name'] == 1) echo ' checked'; ?> /><?php _e('item name', 'usces'); ?></label>
		<label for="chk_pro[sku_name]"><input type="checkbox" class="check_product" id="chk_pro[sku_name]" value="sku_name"<?php if($chk_pro['sku_name'] == 1) echo ' checked'; ?> /><?php _e('SKU display name ', 'usces'); ?></label>
		<label for="chk_pro[options]"><input type="checkbox" class="check_product" id="chk_pro[options]" value="options"<?php if($chk_pro['options'] == 1) echo ' checked'; ?> /><?php _e('options for items', 'usces'); ?></label>
		<label for="chk_pro[quantity]"><input type="checkbox" class="check_product" id="chk_pro[quantity]" value="quantity" checked disabled /><?php _e('Quantity','usces'); ?></label>
		<label for="chk_pro[price]"><input type="checkbox" class="check_product" id="chk_pro[price]" value="price" checked disabled /><?php _e('Unit price','usces'); ?></label>
		<label for="chk_pro[unit]"><input type="checkbox" class="check_product" id="chk_pro[unit]" value="unit"<?php if($chk_pro['unit'] == 1) echo ' checked'; ?> /><?php _e('unit', 'usces'); ?></label>
	</fieldset>
</div>
<div id="dlOrderListDialog" title="<?php _e('Download Order List', 'usces'); ?>">
	<p><?php _e('出力したい項目を選択して、ダウンロードを押してください。', 'usces'); ?></p>
	<fieldset>
<?php 
	if($usces_opt_order['ftype_ord'] == 'xls') {
		$ftype_ord_xls = ' checked';
		$ftype_ord_csv = '';
	} elseif($usces_opt_order['ftype_ord'] == 'csv') {
		$ftype_ord_xls = '';
		$ftype_ord_csv = ' checked';
	} else {
		$ftype_ord_xls = ' checked';
		$ftype_ord_csv = '';
	}
?>
		<label for="ftype_ord_xls"><input type="radio" name="ftype_ord[]" id="ftype_ord_xls" value="xls"<?php echo $ftype_ord_xls; ?> disabled="disabled" /><?php _e('excel', 'usces'); ?></label>
		<label for="ftype_ord_csv"><input type="radio" name="ftype_ord[]" id="ftype_ord_csv" value="csv"<?php echo $ftype_ord_csv; ?> checked="checked" /><?php _e('csv', 'usces'); ?></label>
		<input type="button" id="dl_ord" value="<?php _e('Download', 'usces'); ?>" />
	</fieldset>
	<fieldset><legend><?php _e('Customer Information', 'usces'); ?></legend>
		<label for="chk_ord[ID]"><input type="checkbox" class="check_order" id="chk_ord[ID]" value="ID" checked disabled /><?php _e('Order number', 'usces'); ?></label>
		<label for="chk_ord[date]"><input type="checkbox" class="check_order" id="chk_ord[date]" value="date" checked disabled /><?php _e('order date', 'usces'); ?></label>
		<label for="chk_ord[mem_id]"><input type="checkbox" class="check_order" id="chk_ord[mem_id]" value="mem_id"<?php if($chk_ord['mem_id'] == 1) echo ' checked'; ?> /><?php _e('membership number', 'usces'); ?></label>
		<label for="chk_ord[email]"><input type="checkbox" class="check_order" id="chk_ord[email]" value="email"<?php if($chk_ord['email'] == 1) echo ' checked'; ?> /><?php _e('e-mail', 'usces'); ?></label>
<?php 
	if(!empty($cscs_meta)) {
		foreach($cscs_meta as $key => $entry) {
			if($entry['position'] == 'name_pre') {
//20110208ysk start
				$cscs_key = 'cscs_'.$key;
				//$checked = ($chk_ord[$entry['name']] == 1) ? ' checked' : '';
				$checked = ($chk_ord[$cscs_key] == 1) ? ' checked' : '';
				$name = esc_attr($entry['name']);
				//echo '<label for="chk_ord['.$name.']"><input type="checkbox" class="check_order" id="chk_ord['.$name.']" value="'.$name.'"'.$checked.' />'.$name.'</label>';
				echo '<label for="chk_ord['.$cscs_key.']"><input type="checkbox" class="check_order" id="chk_ord['.$cscs_key.']" value="'.$cscs_key.'"'.$checked.' />'.$name.'</label>'."\n";//20111116ysk 0000302
//20110208ysk end
			}
		}
	}
//20110411ysk start
?>
		<label for="chk_ord[name]"><input type="checkbox" class="check_order" id="chk_ord[name]" value="name" checked disabled /><?php _e('name', 'usces'); ?></label>
<?php 
	switch($applyform) {
	case 'JP':
?>
		<label for="chk_ord[kana]"><input type="checkbox" class="check_order" id="chk_ord[kana]" value="kana"<?php if($chk_ord['kana'] == 1) echo ' checked'; ?> /><?php _e('furigana', 'usces'); ?></label>
<?php 
		break;
	}
//20110411ysk end

	if(!empty($cscs_meta)) {
		foreach($cscs_meta as $key => $entry) {
			if($entry['position'] == 'name_after') {
//20110208ysk start
				$cscs_key = 'cscs_'.$key;
				//$checked = ($chk_ord[$entry['name']] == 1) ? ' checked' : '';
				$checked = ($chk_ord[$cscs_key] == 1) ? ' checked' : '';
				$name = esc_attr($entry['name']);
				//echo '<label for="chk_ord['.$name.']"><input type="checkbox" class="check_order" id="chk_ord['.$name.']" value="'.$name.'"'.$checked.' />'.$name.'</label>';
				echo '<label for="chk_ord['.$cscs_key.']"><input type="checkbox" class="check_order" id="chk_ord['.$cscs_key.']" value="'.$cscs_key.'"'.$checked.' />'.$name.'</label>'."\n";//20111116ysk 0000302
//20110208ysk end
			}
		}
	}

//20110411ysk start
	switch($applyform) {
	case 'JP':
?>
		<label for="chk_ord[zip]"><input type="checkbox" class="check_order" id="chk_ord[zip]" value="zip"<?php if($chk_ord['zip'] == 1) echo ' checked'; ?> /><?php _e('Zip/Postal Code', 'usces'); ?></label>
		<label for="chk_ord[country]"><input type="checkbox" class="check_order" id="chk_ord[country]" value="country"<?php if($chk_ord['country'] == 1) echo ' checked'; ?> /><?php _e('Country', 'usces'); ?></label>
		<label for="chk_ord[pref]"><input type="checkbox" class="check_order" id="chk_ord[pref]" value="pref"<?php if($chk_ord['pref'] == 1) echo ' checked'; ?> /><?php _e('Province', 'usces'); ?></label>
		<label for="chk_ord[address1]"><input type="checkbox" class="check_order" id="chk_ord[address1]" value="address1"<?php if($chk_ord['address1'] == 1) echo ' checked'; ?> /><?php _e('city', 'usces'); ?></label>
		<label for="chk_ord[address2]"><input type="checkbox" class="check_order" id="chk_ord[address2]" value="address2"<?php if($chk_ord['address2'] == 1) echo ' checked'; ?> /><?php _e('numbers', 'usces'); ?></label>
		<label for="chk_ord[address3]"><input type="checkbox" class="check_order" id="chk_ord[address3]" value="address3"<?php if($chk_ord['address3'] == 1) echo ' checked'; ?> /><?php _e('building name', 'usces'); ?></label>
		<label for="chk_ord[tel]"><input type="checkbox" class="check_order" id="chk_ord[tel]" value="tel"<?php if($chk_ord['tel'] == 1) echo ' checked'; ?> /><?php _e('Phone number', 'usces'); ?></label>
		<label for="chk_ord[fax]"><input type="checkbox" class="check_order" id="chk_ord[fax]" value="fax"<?php if($chk_ord['fax'] == 1) echo ' checked'; ?> /><?php _e('FAX number', 'usces'); ?></label>
<?php 
		break;
	case 'US':
	default:
?>
		<label for="chk_ord[address2]"><input type="checkbox" class="check_order" id="chk_ord[address2]" value="address2"<?php if($chk_ord['address2'] == 1) echo ' checked'; ?> /><?php _e('Address Line1', 'usces'); ?></label>
		<label for="chk_ord[address3]"><input type="checkbox" class="check_order" id="chk_ord[address3]" value="address3"<?php if($chk_ord['address3'] == 1) echo ' checked'; ?> /><?php _e('Address Line2', 'usces'); ?></label>
		<label for="chk_ord[address1]"><input type="checkbox" class="check_order" id="chk_ord[address1]" value="address1"<?php if($chk_ord['address1'] == 1) echo ' checked'; ?> /><?php _e('city', 'usces'); ?></label>
		<label for="chk_ord[pref]"><input type="checkbox" class="check_order" id="chk_ord[pref]" value="pref"<?php if($chk_ord['pref'] == 1) echo ' checked'; ?> /><?php _e('State', 'usces'); ?></label>
		<label for="chk_ord[country]"><input type="checkbox" class="check_order" id="chk_ord[country]" value="country"<?php if($chk_ord['country'] == 1) echo ' checked'; ?> /><?php _e('Country', 'usces'); ?></label>
		<label for="chk_ord[zip]"><input type="checkbox" class="check_order" id="chk_ord[zip]" value="zip"<?php if($chk_ord['zip'] == 1) echo ' checked'; ?> /><?php _e('Zip', 'usces'); ?></label>
		<label for="chk_ord[tel]"><input type="checkbox" class="check_order" id="chk_ord[tel]" value="tel"<?php if($chk_ord['tel'] == 1) echo ' checked'; ?> /><?php _e('Phone number', 'usces'); ?></label>
		<label for="chk_ord[fax]"><input type="checkbox" class="check_order" id="chk_ord[fax]" value="fax"<?php if($chk_ord['fax'] == 1) echo ' checked'; ?> /><?php _e('FAX number', 'usces'); ?></label>
<?php 
		break;
	}
//20110411ysk end

	if(!empty($cscs_meta)) {
		foreach($cscs_meta as $key => $entry) {
			if($entry['position'] == 'fax_after') {
//20110208ysk start
				$cscs_key = 'cscs_'.$key;
				//$checked = ($chk_ord[$entry['name']] == 1) ? ' checked' : '';
				$checked = ($chk_ord[$cscs_key] == 1) ? ' checked' : '';
				$name = esc_attr($entry['name']);
				//echo '<label for="chk_ord['.$name.']"><input type="checkbox" class="check_order" id="chk_ord['.$name.']" value="'.$name.'"'.$checked.' />'.$name.'</label>';
				echo '<label for="chk_ord['.$cscs_key.']"><input type="checkbox" class="check_order" id="chk_ord['.$cscs_key.']" value="'.$cscs_key.'"'.$checked.' />'.$name.'</label>'."\n";//20111116ysk 0000302
//20110208ysk end
			}
		}
	}
?>
	</fieldset>
	<fieldset><legend><?php _e('Shipping address information', 'usces'); ?></legend>
<?php 
	if(!empty($csde_meta)) {
		foreach($csde_meta as $key => $entry) {
			if($entry['position'] == 'name_pre') {
//20110208ysk start
				$csde_key = 'csde_'.$key;
				//$checked = ($chk_ord[$entry['name']] == 1) ? ' checked' : '';
				$checked = ($chk_ord[$csde_key] == 1) ? ' checked' : '';
				$name = esc_attr($entry['name']);
				//echo '<label for="chk_ord['.$name.']"><input type="checkbox" class="check_order" id="chk_ord['.$name.']" value="'.$name.'"'.$checked.' />'.$name.'</label>';
				echo '<label for="chk_ord['.$csde_key.']"><input type="checkbox" class="check_order" id="chk_ord['.$csde_key.']" value="'.$csde_key.'"'.$checked.' />'.$name.'</label>'."\n";//20111116ysk 0000302
//20110208ysk end
			}
		}
	}
//20110411ysk start
?>
		<label for="chk_ord[delivery_name]"><input type="checkbox" class="check_order" id="chk_ord[delivery_name]" value="delivery_name"<?php if($chk_ord['delivery_name'] == 1) echo ' checked'; ?> /><?php _e('name', 'usces'); ?></label>
<?php 
	switch($applyform) {
	case 'JP':
?>
		<label for="chk_ord[delivery_kana]"><input type="checkbox" class="check_order" id="chk_ord[delivery_kana]" value="delivery_kana"<?php if($chk_ord['delivery_kana'] == 1) echo ' checked'; ?> /><?php _e('furigana', 'usces'); ?></label>
<?php 
		break;
	}
//20110411ysk end

	if(!empty($csde_meta)) {
		foreach($csde_meta as $key => $entry) {
			if($entry['position'] == 'name_after') {
//20110208ysk start
				$csde_key = 'csde_'.$key;
				//$checked = ($chk_ord[$entry['name']] == 1) ? ' checked' : '';
				$checked = ($chk_ord[$csde_key] == 1) ? ' checked' : '';
				$name = esc_attr($entry['name']);
				//echo '<label for="chk_ord['.$name.']"><input type="checkbox" class="check_order" id="chk_ord['.$name.']" value="'.$name.'"'.$checked.' />'.$name.'</label>';
				echo '<label for="chk_ord['.$csde_key.']"><input type="checkbox" class="check_order" id="chk_ord['.$csde_key.']" value="'.$csde_key.'"'.$checked.' />'.$name.'</label>'."\n";//20111116ysk 0000302
//20110208ysk end
			}
		}
	}

//20110411ysk start
	switch($applyform) {
	case 'JP':
?>
		<label for="chk_ord[delivery_zip]"><input type="checkbox" class="check_order" id="chk_ord[delivery_zip]" value="delivery_zip"<?php if($chk_ord['delivery_zip'] == 1) echo ' checked'; ?> /><?php _e('Zip/Postal Code', 'usces'); ?></label>
		<label for="chk_ord[delivery_country]"><input type="checkbox" class="check_order" id="chk_ord[delivery_country]" value="delivery_country"<?php if($chk_ord['delivery_country'] == 1) echo ' checked'; ?> /><?php _e('Country', 'usces'); ?></label>
		<label for="chk_ord[delivery_pref]"><input type="checkbox" class="check_order" id="chk_ord[delivery_pref]" value="delivery_pref"<?php if($chk_ord['delivery_pref'] == 1) echo ' checked'; ?> /><?php _e('Province', 'usces'); ?></label>
		<label for="chk_ord[delivery_address1]"><input type="checkbox" class="check_order" id="chk_ord[delivery_address1]" value="delivery_address1"<?php if($chk_ord['delivery_address1'] == 1) echo ' checked'; ?> /><?php _e('city', 'usces'); ?></label>
		<label for="chk_ord[delivery_address2]"><input type="checkbox" class="check_order" id="chk_ord[delivery_address2]" value="delivery_address2"<?php if($chk_ord['delivery_address2'] == 1) echo ' checked'; ?> /><?php _e('numbers', 'usces'); ?></label>
		<label for="chk_ord[delivery_address3]"><input type="checkbox" class="check_order" id="chk_ord[delivery_address3]" value="delivery_address3"<?php if($chk_ord['delivery_address3'] == 1) echo ' checked'; ?> /><?php _e('building name', 'usces'); ?></label>
		<label for="chk_ord[delivery_tel]"><input type="checkbox" class="check_order" id="chk_ord[delivery_tel]" value="delivery_tel"<?php if($chk_ord['delivery_tel'] == 1) echo ' checked'; ?> /><?php _e('Phone number', 'usces'); ?></label>
		<label for="chk_ord[delivery_fax]"><input type="checkbox" class="check_order" id="chk_ord[delivery_fax]" value="delivery_fax"<?php if($chk_ord['delivery_fax'] == 1) echo ' checked'; ?> /><?php _e('FAX number', 'usces'); ?></label>
<?php 
		break;
	case 'US':
	default:
?>
		<label for="chk_ord[delivery_address2]"><input type="checkbox" class="check_order" id="chk_ord[delivery_address2]" value="delivery_address2"<?php if($chk_ord['delivery_address2'] == 1) echo ' checked'; ?> /><?php _e('Address Line1', 'usces'); ?></label>
		<label for="chk_ord[delivery_address3]"><input type="checkbox" class="check_order" id="chk_ord[delivery_address3]" value="delivery_address3"<?php if($chk_ord['delivery_address3'] == 1) echo ' checked'; ?> /><?php _e('Address Line2', 'usces'); ?></label>
		<label for="chk_ord[delivery_address1]"><input type="checkbox" class="check_order" id="chk_ord[delivery_address1]" value="delivery_address1"<?php if($chk_ord['delivery_address1'] == 1) echo ' checked'; ?> /><?php _e('city', 'usces'); ?></label>
		<label for="chk_ord[delivery_pref]"><input type="checkbox" class="check_order" id="chk_ord[delivery_pref]" value="delivery_pref"<?php if($chk_ord['delivery_pref'] == 1) echo ' checked'; ?> /><?php _e('State', 'usces'); ?></label>
		<label for="chk_ord[delivery_country]"><input type="checkbox" class="check_order" id="chk_ord[delivery_country]" value="delivery_country"<?php if($chk_ord['delivery_country'] == 1) echo ' checked'; ?> /><?php _e('Country', 'usces'); ?></label>
		<label for="chk_ord[delivery_zip]"><input type="checkbox" class="check_order" id="chk_ord[delivery_zip]" value="delivery_zip"<?php if($chk_ord['delivery_zip'] == 1) echo ' checked'; ?> /><?php _e('Zip', 'usces'); ?></label>
		<label for="chk_ord[delivery_tel]"><input type="checkbox" class="check_order" id="chk_ord[delivery_tel]" value="delivery_tel"<?php if($chk_ord['delivery_tel'] == 1) echo ' checked'; ?> /><?php _e('Phone number', 'usces'); ?></label>
		<label for="chk_ord[delivery_fax]"><input type="checkbox" class="check_order" id="chk_ord[delivery_fax]" value="delivery_fax"<?php if($chk_ord['delivery_fax'] == 1) echo ' checked'; ?> /><?php _e('FAX number', 'usces'); ?></label>
<?php 
		break;
	}
//20110411ysk end

	if(!empty($csde_meta)) {
		foreach($csde_meta as $key => $entry) {
			if($entry['position'] == 'fax_after') {
//20110208ysk start
				$csde_key = 'csde_'.$key;
				//$checked = ($chk_ord[$entry['name']] == 1) ? ' checked' : '';
				$checked = ($chk_ord[$csde_key] == 1) ? ' checked' : '';
				$name = esc_attr($entry['name']);
				//echo '<label for="chk_ord['.$name.']"><input type="checkbox" class="check_order" id="chk_ord['.$name.']" value="'.$name.'"'.$checked.' />'.$name.'</label>';
				echo '<label for="chk_ord['.$csde_key.']"><input type="checkbox" class="check_order" id="chk_ord['.$csde_key.']" value="'.$csde_key.'"'.$checked.' />'.$name.'</label>'."\n";//20111116ysk 0000302
//20110208ysk end
			}
		}
	}
?>
	</fieldset>
	<fieldset><legend><?php _e('Order Infomation', 'usces'); ?></legend>
		<label for="chk_ord[shipping_date]"><input type="checkbox" class="check_order" id="chk_ord[shipping_date]" value="shipping_date"<?php if($chk_ord['shipping_date'] == 1) echo ' checked'; ?> /><?php _e('shpping date', 'usces'); ?></label>
		<label for="chk_ord[peyment_method]"><input type="checkbox" class="check_order" id="chk_ord[peyment_method]" value="peyment_method"<?php if($chk_ord['peyment_method'] == 1) echo ' checked'; ?> /><?php _e('payment method','usces'); ?></label>
		<label for="chk_ord[delivery_method]"><input type="checkbox" class="check_order" id="chk_ord[delivery_method]" value="delivery_method"<?php if($chk_ord['delivery_method'] == 1) echo ' checked'; ?> /><?php _e('shipping option','usces'); ?></label>
<!--20101208ysk start-->
		<label for="chk_ord[delivery_date]"><input type="checkbox" class="check_order" id="chk_ord[delivery_date]" value="delivery_date"<?php if($chk_ord['delivery_date'] == 1) echo ' checked'; ?> /><?php _e('Delivery date','usces'); ?></label>
<!--20101208ysk end-->
		<label for="chk_ord[delivery_time]"><input type="checkbox" class="check_order" id="chk_ord[delivery_time]" value="delivery_time"<?php if($chk_ord['delivery_time'] == 1) echo ' checked'; ?> /><?php _e('delivery time','usces'); ?></label>
		<label for="chk_ord[delidue_date]"><input type="checkbox" class="check_order" id="chk_ord[delidue_date]" value="delidue_date"<?php if($chk_ord['delidue_date'] == 1) echo ' checked'; ?> /><?php _e('Shipping date', 'usces'); ?></label>
		<label for="chk_ord[status]"><input type="checkbox" class="check_order" id="chk_ord[status]" value="status"<?php if($chk_ord['status'] == 1) echo ' checked'; ?> /><?php _e('Status', 'usces'); ?></label>
		<label for="chk_ord[total_amount]"><input type="checkbox" class="check_order" id="chk_ord[total_amount]" value="total_amount" checked disabled /><?php _e('Total Amount', 'usces'); ?></label>
		<label for="chk_ord[usedpoint]"><input type="checkbox" class="check_order" id="chk_ord[usedpoint]" value="usedpoint"<?php if($chk_ord['usedpoint'] == 1) echo ' checked'; ?> /><?php _e('Used points', 'usces'); ?></label>
		<label for="chk_ord[discount]"><input type="checkbox" class="check_order" id="chk_ord[discount]" value="discount" checked disabled /><?php _e('Disnount', 'usces'); ?></label>
		<label for="chk_ord[shipping_charge]"><input type="checkbox" class="check_order" id="chk_ord[shipping_charge]" value="shipping_charge" checked disabled /><?php _e('Shipping', 'usces'); ?></label>
		<label for="chk_ord[cod_fee]"><input type="checkbox" class="check_order" id="chk_ord[cod_fee]" value="cod_fee" checked disabled /><?php echo apply_filters('usces_filter_cod_label', __('COD fee', 'usces')); ?></label>
		<label for="chk_ord[tax]"><input type="checkbox" class="check_order" id="chk_ord[tax]" value="tax" checked disabled /><?php _e('consumption tax', 'usces'); ?></label>
		<label for="chk_ord[note]"><input type="checkbox" class="check_order" id="chk_ord[note]" value="note"<?php if($chk_ord['note'] == 1) echo ' checked'; ?> /><?php _e('Notes', 'usces'); ?></label>
<?php 
	if(!empty($csod_meta)) {
		foreach($csod_meta as $key => $entry) {
//20110208ysk start
			$csod_key = 'csod_'.$key;
			//$checked = ($chk_ord[$entry['name']] == 1) ? ' checked' : '';
			$checked = ($chk_ord[$csod_key] == 1) ? ' checked' : '';
			$name = esc_attr($entry['name']);
			//echo '<label for="chk_ord['.$name.']"><input type="checkbox" class="check_order" id="chk_ord['.$name.']" value="'.$name.'"'.$checked.' />'.$name.'</label>';
			echo '<label for="chk_ord['.$csod_key.']"><input type="checkbox" class="check_order" id="chk_ord['.$csod_key.']" value="'.$csod_key.'"'.$checked.' />'.$name.'</label>'."\n";//20111116ysk 0000302
//20110208ysk end
		}
	}
?>
	</fieldset>
</div>
<!--20100908ysk end-->

<!--<div class="chui">
<h3>受注詳細画面（作成中）について</h3>
<p>各行の受注番号をクリックすると受注詳細画面が表示されます。受注詳細画面では注文商品の追加、修正、削除など受注に関する全ての情報を編集することができま、問い合わせや電話での変更依頼に対応します。</p>
<p>「見積り」ステイタスを利用することで見積りをメール送信できます。見積書印刷でFAX対応も可能です。注文をいただいた場合は「受注」ステイタスに変更することで、見積りの内容がそのまま受注データとなります。</p>
<p>その他のステイタスには銀行振り込みの場合の「入金」ステイタス、発送完了した場合の「完了」、注文の「キャンセル」などがあり、各業務の終了後にステイタスを変更することを習慣付ければ、複数の担当者での業務もスムーズに行うことができます。</p>
</div>
-->
</form>
</div><!--usces_admin-->
</div><!--wrap-->
<script type="text/javascript">
//	rowSelection("mainDataTable");
</script>
