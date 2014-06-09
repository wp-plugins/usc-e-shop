<?php
require_once( USCES_PLUGIN_DIR . "/classes/orderList.class.php" );
global $wpdb;

$tableName = $wpdb->prefix . "usces_order";
$arr_column = array(
			__('ID', 'usces') => 'ID', 
			__('Order number', 'usces') => 'deco_id', 
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
$arr_column = apply_filters( 'usces_filter_order_list_column', $arr_column );

$DT = new dataList($tableName, $arr_column);
$res = $DT->MakeTable();
$arr_search = $DT->GetSearchs();
$arr_status = apply_filters( 'usces_filter_management_status', get_option('usces_management_status') );
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
$payment_name = array();
$payments = usces_get_system_option( 'usces_payment_method', 'sort' );
foreach ( (array)$payments as $id => $array ) {
	$payment_name[$id] = $array['name'];
}
foreach((array)$arr_status as $key => $value){
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
$chk_pro = ( isset($usces_opt_order['chk_pro']) ) ? $usces_opt_order['chk_pro'] : array();
$chk_ord = ( isset($usces_opt_order['chk_ord']) ) ? $usces_opt_order['chk_ord'] : array();
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

	$("#searchselectsku").change(function () {
		operation.change_search_sku_field();
	});

	$("#changeselect").change(function () {
		operation.change_collective_field();
	});

	$("#collective_change").click(function () {
		if( $("#changeselect option:selected").val() == 'none' ) {
			$("#orderlistaction").val('');
			return false;
		}
		if( $("input[name*='listcheck']:checked").length == 0 ) {
			alert("<?php _e('Choose the data.', 'usces'); ?>");
			$("#orderlistaction").val('');
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
		//}else{
		//	$("#orderlistaction").val('');
		//	return false;
		}
		if( mes != '' ) {
			if( !confirm(mes) ){
				$("#orderlistaction").val('');
				return false;
			}
		}
		<?php do_action( 'usces_action_order_list_collective_change_js' ); ?>
		$("#orderlistaction").val('collective');
		//return true;
		$('#form_tablesearch').submit();
	});

	operation = {
		change_search_field :function (){
		
			var label = '';
			var html = '';
			var column = $("#searchselect").val();
			
			if( column == 'ID' ) {
				label = '<?php _e('key words', 'usces'); ?>';
				html = '<input name="search[word][ID]" type="text" value="<?php echo esc_attr(isset($arr_search['word']['ID']) ? $arr_search['word']['ID'] : ''); ?>" class="searchword" maxlength="50" />';
			}else if( column == 'deco_id' ) {
				label = '<?php _e('key words', 'usces'); ?>';
				html = '<input name="search[word][deco_id]" type="text" value="<?php echo esc_attr(isset($arr_search['word']['deco_id']) ? $arr_search['word']['deco_id'] : ''); ?>" class="searchword" maxlength="50" />';
			}else if( column == 'date' ) {
				label = '<?php _e('key words', 'usces'); ?>';
				html = '<input name="search[word][date]" type="text" value="<?php echo esc_attr(isset($arr_search['word']['date']) ? $arr_search['word']['date'] : ''); ?>" class="searchword" maxlength="50" />';
			}else if( column == 'mem_id' ) {
				label = '<?php _e('key words', 'usces'); ?>';
				html = '<input name="search[word][mem_id]" type="text" value="<?php echo esc_attr(isset($arr_search['word']['mem_id']) ? $arr_search['word']['mem_id'] : ''); ?>" class="searchword" maxlength="50" />';
			}else if( column == 'name' ) {
				label = '<?php _e('key words', 'usces'); ?>';
				html = '<input name="search[word][name]" type="text" value="<?php echo esc_attr(isset($arr_search['word']['name']) ? $arr_search['word']['name'] : ''); ?>" class="searchword" maxlength="50" />';
			}else if( column == 'order_modified' ) {
				label = '<?php _e('key words', 'usces'); ?>';
				html = '<input name="search[word][order_modified]" type="text" value="<?php echo esc_attr(isset($arr_search['word']['order_modified']) ? $arr_search['word']['order_modified'] : ''); ?>" class="searchword" maxlength="50" />';
			}else if( column == 'pref' ) {
				label = '';
				html = '<select name="search[word][pref]" class="searchselect">';
		<?php foreach((array)$pref as $pkey => $pvalue){ 
				if( isset($arr_search['word']['pref']) && $pvalue == $arr_search['word']['pref']){
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
				if( isset($arr_search['word']['delivery_method']) && $dvalue['id'] == $arr_search['word']['delivery_method']){
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
				if( isset($arr_search['word']['payment_name']) && $pnvalue == $arr_search['word']['payment_name']){
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
				if( isset($arr_search['word']['receipt_status']) && $rvalue == $arr_search['word']['receipt_status']){
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
				if( isset($arr_search['word']['order_status']) && $ovalue == $arr_search['word']['order_status']){
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
		
		change_search_sku_field :function (){
		
			var label = '';
			var html = '';
			var column = $("#searchselectsku").val();
			
			if( column == 'item_code' ) {
				label = '<?php _e('key words', 'usces'); ?>';
				html = '<input name="search[skuword][item_code]" type="text" value="<?php echo esc_attr(isset($arr_search['skuword']['item_code']) ? $arr_search['skuword']['item_code'] : ''); ?>" class="searchword" maxlength="50" />';
			}else if( column == 'item_name' ) {
				label = '<?php _e('key words', 'usces'); ?>';
				html = '<input name="search[skuword][item_name]" type="text" value="<?php echo esc_attr(isset($arr_search['skuword']['item_name']) ? $arr_search['skuword']['item_name'] : ''); ?>" class="searchword" maxlength="50" />';
			}
			
			$("#searchlabelsku").html( label );
			$("#searchfieldsku").html( html );
		
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
				html = '<select name="change[word][order_status]" class="ksearchselect">';
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
<?php echo apply_filters('usces_filter_order_list_page_js', ''); ?>
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
	operation.change_search_sku_field();
	
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
//20120123ysk start 0000385
		//var args = "&searchf[column]="+$(':input[name="search[column]"]').val()
		//	+"&search[word]["+$("#searchselect").val()+"]="+$(':input[name="search[word]['+$("#searchselect").val()+']"]').val()
		//	+"&search[period]="+$(':input[name="search[period]"]').val()
		//	+"&searchSwitchStatus="+$(':input[name="searchSwitchStatus"]').val()
		//	+"&ftype="+$(':input[name="ftype_pro[]"]:checked').val();
		var args = "&search[column]="+$(':input[name="search[column]"]').val()
			+"&search[sku]="+$(':input[name="search[sku]"]').val()
			+"&search[word]["+$("#searchselect").val()+"]="+$(':input[name="search[word]['+$("#searchselect").val()+']"]').val()
			+"&search[skuword]["+$("#searchselectsku").val()+"]="+$(':input[name="search[skuword]['+$("#searchselectsku").val()+']"]').val()
			+"&search[period]="+$(':input[name="search[period]"]').val()
			+"&searchSwitchStatus="+$(':input[name="searchSwitchStatus"]').val()
			+"&ftype=csv";
//20120123ysk end
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
//20120123ysk start 0000385
		//var args = "&search[column]="+$(':input[name="search[column]"]').val()
		//	+"&search[word]["+$("#searchselect").val()+"]="+$(':input[name="search[word]['+$("#searchselect").val()+']"]').val()
		//	+"&search[period]="+$(':input[name="search[period]"]').val()
		//	+"&searchSwitchStatus="+$(':input[name="searchSwitchStatus"]').val()
		//	+"&ftype="+$(':input[name="ftype_ord[]"]:checked').val();
		var args = "&search[column]="+$(':input[name="search[column]"]').val()
			+"&search[sku]="+$(':input[name="search[sku]"]').val()
			+"&search[word]["+$("#searchselect").val()+"]="+$(':input[name="search[word]['+$("#searchselect").val()+']"]').val()
			+"&search[skuword]["+$("#searchselectsku").val()+"]="+$(':input[name="search[skuword]['+$("#searchselectsku").val()+']"]').val()
			+"&search[period]="+$(':input[name="search[period]"]').val()
			+"&searchSwitchStatus="+$(':input[name="searchSwitchStatus"]').val()
			+"&ftype=csv";
//20120123ysk end
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
<?php do_action('usces_action_order_list_document_ready_js'); ?>
//20100908ysk end
});
</script>

<div class="wrap">
<div class="usces_admin">
<form action="<?php echo USCES_ADMIN_URL.'?page=usces_orderlist'; ?>" method="post" name="tablesearch" id="form_tablesearch">

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
		<td rowspan="2"><input name="searchIn" type="submit" class="searchbutton" value="<?php _e('Search', 'usces'); ?>" />
		<input name="searchOut" type="submit" class="searchbutton" value="<?php _e('Cancellation', 'usces'); ?>" />
		<input name="searchSwitchStatus" id="searchSwitchStatus" type="hidden" value="<?php echo esc_attr($DT->searchSwitchStatus); ?>" />
		</td>
		</tr>
		<td><?php _e('search fields', 'usces'); ?></td>
		<td><select name="search[sku]" class="searchselect" id="searchselectsku">
		    <option value="none"> </option>
			<option value="item_code"<?php echo ( 'item_code' == $arr_search['sku'] ? ' selected="selected"' : '') ?>><?php  _e('item code', 'usces'); ?></option>
			<option value="item_name"<?php echo ( 'item_name' == $arr_search['sku'] ? ' selected="selected"' : '') ?>><?php  _e('item name', 'usces'); ?></option>
			</select>
		</td>
		<td id="searchlabelsku"></td>
		<td id="searchfieldsku"></td>
		<tr>
		</tr>
		</table>
		<table id="period_table">
		<tr>
		<?php echo apply_filters( 'usces_filter_order_list_period_table', '' ); ?>
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
			<?php echo apply_filters( 'usces_filter_allchange_column', '' ); ?>
    	</select></td>
		<td id="changelabel"></td>
		<td id="changefield"></td>
		<td><input name="collective_change" type="button" class="searchbutton" id="collective_change" value="<?php _e('start', 'usces'); ?>" />
		</td>
		</tr>
		</table>
		<input name="collective" id="orderlistaction" type="hidden" />
<!--20100908ysk start-->
		<table id="dl_list_table">
		<tr>
		<?php echo apply_filters('usces_filter_dl_list_table', ''); ?>
		<td><input type="button" id="dl_productlist" class="searchbutton" value="<?php _e('Download Product List', 'usces'); ?>" /></td>
		<td><input type="button" id="dl_orderlist" class="searchbutton" value="<?php _e('Download Order List', 'usces'); ?>" /></td>
		</tr>
		</table>
<!--20100908ysk end-->
</div>
<?php do_action( 'usces_action_order_list_searchbox' ); ?>
</div>

<table id="mainDataTable" cellspacing="1">
<?php
//20120612ysk start 0000501
	$list_header = '<th scope="col"><input name="allcheck" type="checkbox" value="" /></th>';
	foreach( (array)$arr_header as $value ) {
		$list_header .= '<th scope="col">'.$value.'</th>';
	}
	$list_header .= '<th scope="col">&nbsp;</th>';
//20120612ysk end
?>
	<tr>
		<?php echo apply_filters('usces_filter_order_list_header', $list_header, $arr_header);//20120612ysk 0000501 ?>
	</tr>
<?php foreach ( (array)$rows as $array ) : ?>
<?php
//20120612ysk start 0000501
		$list_detail = '<td align="center"><input name="listcheck[]" type="checkbox" value="'.$array['ID'].'" /></td>';
		foreach( (array)$array as $key => $value ) {
			if( WCUtils::is_blank($value) ) $value = '&nbsp;';
			if( $key === 'ID' || $key === 'deco_id' ) {
				$list_detail .= '<td><a href="'.USCES_ADMIN_URL.'?page=usces_orderlist&order_action=edit&order_id='.$array['ID'].'&usces_referer='.$curent_url.'&wc_nonce='.wp_create_nonce( 'order_list' ).'">'.esc_html($value).'</a></td>';
			} elseif( $key === 'date' ) {
				$list_detail .= '<td>'.esc_html($value).'</td>';
			} elseif( $key === 'mem_id' ) {
				if( WCUtils::is_zero($value) ) $value = '&nbsp;';
				$list_detail .= '<td>'.esc_html($value).'</td>';
			} elseif( $key === 'name' ) {
				switch( $applyform ) {
				case 'JP': 
					$list_detail .= '<td>'.esc_html($value).'</td>';
					break;
				case 'US':
				default:
					$names = explode(' ', $value);
					$list_detail .= '<td>'.esc_html($names[1].' '.$names[0]).'</td>';
				}
			} elseif( $key === 'pref' ) {
				if( $value == __('-- Select --','usces') ) {
					$list_detail .= '<td>&nbsp;</td>';
				} else {
					$list_detail .= '<td>'.esc_html($value).'</td>';
				}
			} elseif( $key === 'delivery_method' ) {
				if( -1 != $value ) {
					$delivery_method_index = $this->get_delivery_method_index($value);
					$value = ( isset( $this->options['delivery_method'][$delivery_method_index]['name'] ) ) ? $this->options['delivery_method'][$delivery_method_index]['name'] : '&nbsp;';
				} else {
					$value = '&nbsp;';
				}
				$list_detail .= '<td class="green">'.esc_html($value).'</td>';
			} elseif( $key === 'total_price' ) {
				$list_detail .= '<td class="price">'.usces_crform( $value, true, false, 'return' ).'</td>';
			} elseif( $key === 'payment_name' ) {
				if( $value == '#none#' ) {
					$list_detail .= '<td>&nbsp;</td>';
				} else {
					$list_detail .= '<td>'.esc_html($value).'</td>';
				}
			} elseif( $key === 'receipt_status' ) {
				if( $value == __('unpaid', 'usces') ) {
					$list_detail .= '<td class="red">'.esc_html($value).'</td>';
				} elseif( $value == 'Pending' ) {
					$list_detail .= '<td class="red">'.esc_html($value).'</td>';
				} elseif( $value == __('payment confirmed', 'usces') ) {
					$list_detail .= '<td class="green">'.esc_html($value).'</td>';
				} else {
					$list_detail .= '<td>'.esc_html($value).'</td>';
				}
			} elseif( $key === 'order_status' ) {
				if( $value == __('It has sent it out.', 'usces') ) {
					$list_detail .= '<td class="green">'.esc_html($value).'</td>';
				} else {
					$list_detail .= '<td>'.esc_html($value).'</td>';
				}
			} elseif( $key === 'order_modified' ) {
				$list_detail .= '<td>'.esc_html($value).'</td>';
			}
		}
		$list_detail .= '<td><a href="'.USCES_ADMIN_URL.'?page=usces_orderlist&order_action=delete&order_id='.$array['ID'].'&wc_nonce='.wp_create_nonce( 'order_list' ).'" onclick="return deleteconfirm(\''.$array['ID'].'\');"><span style="color:#FF0000; font-size:9px;">'.__('Delete', 'usces').'</span></a></td>';
//20120612ysk end
?>
	<tr<?php echo apply_filters('usces_filter_order_list_detail_trclass', '', $array); ?>>
		<?php echo apply_filters('usces_filter_order_list_detail', $list_detail, $array, $curent_url);//20120612ysk 0000501 ?>
	</tr>
<?php endforeach; ?>
</table>

</div>
[memory peak usage] <?php echo round(memory_get_peak_usage()/1048576, 1); ?>Mb

<!--20100908ysk start-->
<div id="dlProductListDialog" title="<?php _e('Download Product List', 'usces'); ?>">
	<p><?php _e('Select the item you want, please press the download.', 'usces'); ?></p>
	<fieldset>
		<input type="button" id="dl_pro" value="<?php _e('Download', 'usces'); ?>" />
	</fieldset>
	<fieldset><legend><?php _e('Header Information', 'usces'); ?></legend>
		<label for="chk_pro[ID]"><input type="checkbox" class="check_product" id="chk_pro[ID]" value="ID" checked disabled /><?php _e('ID', 'usces'); ?></label>
		<label for="chk_pro[deco_id]"><input type="checkbox" class="check_product" id="chk_pro[deco_id]" value="deco_id" checked disabled /><?php _e('order number', 'usces'); ?></label>
		<label for="chk_pro[date]"><input type="checkbox" class="check_product" id="chk_pro[date]" value="date"<?php usces_checked($chk_pro, 'date'); ?> /><?php _e('order date', 'usces'); ?></label>
		<label for="chk_pro[mem_id]"><input type="checkbox" class="check_product" id="chk_pro[mem_id]" value="mem_id"<?php usces_checked($chk_pro, 'mem_id'); ?> /><?php _e('membership number', 'usces'); ?></label>
		<label for="chk_pro[name]"><input type="checkbox" class="check_product" id="chk_pro[name]" value="name"<?php usces_checked($chk_pro, 'name'); ?> /><?php _e('name', 'usces'); ?></label>
		<label for="chk_pro[delivery_method]"><input type="checkbox" class="check_product" id="chk_pro[delivery_method]" value="delivery_method"<?php usces_checked($chk_pro, 'delivery_method'); ?> /><?php _e('shipping option','usces'); ?></label>
		<label for="chk_pro[shipping_date]"><input type="checkbox" class="check_product" id="chk_pro[shipping_date]" value="shipping_date"<?php usces_checked($chk_pro, 'shipping_date'); ?> /><?php _e('shpping date', 'usces'); ?></label>
		<?php do_action( 'usces_action_chk_pro_head', $chk_pro ); ?>
	</fieldset>
	<fieldset><legend><?php _e('Product Information', 'usces'); ?></legend>
		<label for="chk_pro[item_code]"><input type="checkbox" class="check_product" id="chk_pro[item_code]" value="item_code" checked disabled /><?php _e('item code', 'usces'); ?></label>
		<label for="chk_pro[sku_code]"><input type="checkbox" class="check_product" id="chk_pro[sku_code]" value="sku_code" checked disabled /><?php _e('SKU code', 'usces'); ?></label>
		<label for="chk_pro[item_name]"><input type="checkbox" class="check_product" id="chk_pro[item_name]" value="item_name"<?php usces_checked($chk_pro, 'item_name'); ?> /><?php _e('item name', 'usces'); ?></label>
		<label for="chk_pro[sku_name]"><input type="checkbox" class="check_product" id="chk_pro[sku_name]" value="sku_name"<?php usces_checked($chk_pro, 'sku_name'); ?> /><?php _e('SKU display name ', 'usces'); ?></label>
		<label for="chk_pro[options]"><input type="checkbox" class="check_product" id="chk_pro[options]" value="options"<?php usces_checked($chk_pro, 'options'); ?> /><?php _e('options for items', 'usces'); ?></label>
		<label for="chk_pro[quantity]"><input type="checkbox" class="check_product" id="chk_pro[quantity]" value="quantity" checked disabled /><?php _e('Quantity','usces'); ?></label>
		<label for="chk_pro[price]"><input type="checkbox" class="check_product" id="chk_pro[price]" value="price" checked disabled /><?php _e('Unit price','usces'); ?></label>
		<label for="chk_pro[unit]"><input type="checkbox" class="check_product" id="chk_pro[unit]" value="unit"<?php usces_checked($chk_pro, 'unit'); ?> /><?php _e('unit', 'usces'); ?></label>
		<?php do_action( 'usces_action_chk_pro_detail', $chk_pro ); ?>
	</fieldset>
</div>
<div id="dlOrderListDialog" title="<?php _e('Download Order List', 'usces'); ?>">
	<p><?php _e('Select the item you want, please press the download.', 'usces'); ?></p>
	<fieldset>
		<input type="button" id="dl_ord" value="<?php _e('Download', 'usces'); ?>" />
	</fieldset>
	<fieldset><legend><?php _e('Customer Information', 'usces'); ?></legend>
		<label for="chk_ord[ID]"><input type="checkbox" class="check_order" id="chk_ord[ID]" value="ID" checked disabled /><?php _e('ID', 'usces'); ?></label>
		<label for="chk_ord[deco_id]"><input type="checkbox" class="check_order" id="chk_ord[deco_id]" value="deco_id" checked disabled /><?php _e('Order number', 'usces'); ?></label>
		<label for="chk_ord[date]"><input type="checkbox" class="check_order" id="chk_ord[date]" value="date" checked disabled /><?php _e('order date', 'usces'); ?></label>
		<label for="chk_ord[mem_id]"><input type="checkbox" class="check_order" id="chk_ord[mem_id]" value="mem_id"<?php usces_checked($chk_ord, 'mem_id'); ?> /><?php _e('membership number', 'usces'); ?></label>
		<label for="chk_ord[email]"><input type="checkbox" class="check_order" id="chk_ord[email]" value="email"<?php usces_checked($chk_ord, 'email'); ?> /><?php _e('e-mail', 'usces'); ?></label>
<?php 
	if(!empty($cscs_meta)) {
		foreach($cscs_meta as $key => $entry) {
			if($entry['position'] == 'name_pre') {
//20110208ysk start
				$cscs_key = 'cscs_'.$key;
				//$checked = ($chk_ord[$entry['name']] == 1) ? ' checked' : '';
				//$checked = ($chk_ord[$cscs_key] == 1) ? ' checked' : '';
				$checked = usces_checked( $chk_ord, $cscs_key, 'return' );
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
		<label for="chk_ord[kana]"><input type="checkbox" class="check_order" id="chk_ord[kana]" value="kana"<?php usces_checked($chk_ord, 'kana'); ?> /><?php _e('furigana', 'usces'); ?></label>
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
				//$checked = ($chk_ord[$cscs_key] == 1) ? ' checked' : '';
				$checked = usces_checked( $chk_ord, $cscs_key, 'return' );
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
		<label for="chk_ord[zip]"><input type="checkbox" class="check_order" id="chk_ord[zip]" value="zip"<?php usces_checked($chk_ord, 'zip'); ?> /><?php _e('Zip/Postal Code', 'usces'); ?></label>
		<label for="chk_ord[country]"><input type="checkbox" class="check_order" id="chk_ord[country]" value="country"<?php usces_checked($chk_ord, 'country'); ?> /><?php _e('Country', 'usces'); ?></label>
		<label for="chk_ord[pref]"><input type="checkbox" class="check_order" id="chk_ord[pref]" value="pref"<?php usces_checked($chk_ord, 'pref'); ?> /><?php _e('Province', 'usces'); ?></label>
		<label for="chk_ord[address1]"><input type="checkbox" class="check_order" id="chk_ord[address1]" value="address1"<?php usces_checked($chk_ord, 'address1'); ?> /><?php _e('city', 'usces'); ?></label>
		<label for="chk_ord[address2]"><input type="checkbox" class="check_order" id="chk_ord[address2]" value="address2"<?php usces_checked($chk_ord, 'address2'); ?> /><?php _e('numbers', 'usces'); ?></label>
		<label for="chk_ord[address3]"><input type="checkbox" class="check_order" id="chk_ord[address3]" value="address3"<?php usces_checked($chk_ord, 'address3'); ?> /><?php _e('building name', 'usces'); ?></label>
		<label for="chk_ord[tel]"><input type="checkbox" class="check_order" id="chk_ord[tel]" value="tel"<?php usces_checked($chk_ord, 'tel'); ?> /><?php _e('Phone number', 'usces'); ?></label>
		<label for="chk_ord[fax]"><input type="checkbox" class="check_order" id="chk_ord[fax]" value="fax"<?php usces_checked($chk_ord, 'fax'); ?> /><?php _e('FAX number', 'usces'); ?></label>
<?php 
		break;
	case 'US':
	default:
?>
		<label for="chk_ord[address2]"><input type="checkbox" class="check_order" id="chk_ord[address2]" value="address2"<?php usces_checked($chk_ord, 'address2'); ?> /><?php _e('Address Line1', 'usces'); ?></label>
		<label for="chk_ord[address3]"><input type="checkbox" class="check_order" id="chk_ord[address3]" value="address3"<?php usces_checked($chk_ord, 'address3'); ?> /><?php _e('Address Line2', 'usces'); ?></label>
		<label for="chk_ord[address1]"><input type="checkbox" class="check_order" id="chk_ord[address1]" value="address1"<?php usces_checked($chk_ord, 'address1'); ?> /><?php _e('city', 'usces'); ?></label>
		<label for="chk_ord[pref]"><input type="checkbox" class="check_order" id="chk_ord[pref]" value="pref"<?php usces_checked($chk_ord, 'pref'); ?> /><?php _e('State', 'usces'); ?></label>
		<label for="chk_ord[country]"><input type="checkbox" class="check_order" id="chk_ord[country]" value="country"<?php usces_checked($chk_ord, 'country'); ?> /><?php _e('Country', 'usces'); ?></label>
		<label for="chk_ord[zip]"><input type="checkbox" class="check_order" id="chk_ord[zip]" value="zip"<?php usces_checked($chk_ord, 'zip'); ?> /><?php _e('Zip', 'usces'); ?></label>
		<label for="chk_ord[tel]"><input type="checkbox" class="check_order" id="chk_ord[tel]" value="tel"<?php usces_checked($chk_ord, 'tel'); ?> /><?php _e('Phone number', 'usces'); ?></label>
		<label for="chk_ord[fax]"><input type="checkbox" class="check_order" id="chk_ord[fax]" value="fax"<?php usces_checked($chk_ord, 'fax'); ?> /><?php _e('FAX number', 'usces'); ?></label>
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
				//$checked = ($chk_ord[$cscs_key] == 1) ? ' checked' : '';
				$checked = usces_checked( $chk_ord, $cscs_key, 'return' );
				$name = esc_attr($entry['name']);
				//echo '<label for="chk_ord['.$name.']"><input type="checkbox" class="check_order" id="chk_ord['.$name.']" value="'.$name.'"'.$checked.' />'.$name.'</label>';
				echo '<label for="chk_ord['.$cscs_key.']"><input type="checkbox" class="check_order" id="chk_ord['.$cscs_key.']" value="'.$cscs_key.'"'.$checked.' />'.$name.'</label>'."\n";//20111116ysk 0000302
//20110208ysk end
			}
		}
	}
?>
		<?php do_action( 'usces_action_chk_ord_customer', $chk_ord ); ?>
	</fieldset>
	<fieldset><legend><?php _e('Shipping address information', 'usces'); ?></legend>
<?php 
	if(!empty($csde_meta)) {
		foreach($csde_meta as $key => $entry) {
			if($entry['position'] == 'name_pre') {
//20110208ysk start
				$csde_key = 'csde_'.$key;
				//$checked = ($chk_ord[$entry['name']] == 1) ? ' checked' : '';
				//$checked = ($chk_ord[$csde_key] == 1) ? ' checked' : '';
				$checked = usces_checked( $chk_ord, $csde_key, 'return' );
				$name = esc_attr($entry['name']);
				//echo '<label for="chk_ord['.$name.']"><input type="checkbox" class="check_order" id="chk_ord['.$name.']" value="'.$name.'"'.$checked.' />'.$name.'</label>';
				echo '<label for="chk_ord['.$csde_key.']"><input type="checkbox" class="check_order" id="chk_ord['.$csde_key.']" value="'.$csde_key.'"'.$checked.' />'.$name.'</label>'."\n";//20111116ysk 0000302
//20110208ysk end
			}
		}
	}
//20110411ysk start
?>
		<label for="chk_ord[delivery_name]"><input type="checkbox" class="check_order" id="chk_ord[delivery_name]" value="delivery_name"<?php usces_checked($chk_ord, 'delivery_name'); ?> /><?php _e('name', 'usces'); ?></label>
<?php 
	switch($applyform) {
	case 'JP':
?>
		<label for="chk_ord[delivery_kana]"><input type="checkbox" class="check_order" id="chk_ord[delivery_kana]" value="delivery_kana"<?php usces_checked($chk_ord, 'delivery_kana'); ?> /><?php _e('furigana', 'usces'); ?></label>
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
				//$checked = ($chk_ord[$csde_key] == 1) ? ' checked' : '';
				$checked = usces_checked( $chk_ord, $csde_key, 'return' );
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
		<label for="chk_ord[delivery_zip]"><input type="checkbox" class="check_order" id="chk_ord[delivery_zip]" value="delivery_zip"<?php usces_checked($chk_ord, 'delivery_zip'); ?> /><?php _e('Zip/Postal Code', 'usces'); ?></label>
		<label for="chk_ord[delivery_country]"><input type="checkbox" class="check_order" id="chk_ord[delivery_country]" value="delivery_country"<?php usces_checked($chk_ord, 'delivery_country'); ?> /><?php _e('Country', 'usces'); ?></label>
		<label for="chk_ord[delivery_pref]"><input type="checkbox" class="check_order" id="chk_ord[delivery_pref]" value="delivery_pref"<?php usces_checked($chk_ord, 'delivery_pref'); ?> /><?php _e('Province', 'usces'); ?></label>
		<label for="chk_ord[delivery_address1]"><input type="checkbox" class="check_order" id="chk_ord[delivery_address1]" value="delivery_address1"<?php usces_checked($chk_ord, 'delivery_address1'); ?> /><?php _e('city', 'usces'); ?></label>
		<label for="chk_ord[delivery_address2]"><input type="checkbox" class="check_order" id="chk_ord[delivery_address2]" value="delivery_address2"<?php usces_checked($chk_ord, 'delivery_address2'); ?> /><?php _e('numbers', 'usces'); ?></label>
		<label for="chk_ord[delivery_address3]"><input type="checkbox" class="check_order" id="chk_ord[delivery_address3]" value="delivery_address3"<?php usces_checked($chk_ord, 'delivery_address3'); ?> /><?php _e('building name', 'usces'); ?></label>
		<label for="chk_ord[delivery_tel]"><input type="checkbox" class="check_order" id="chk_ord[delivery_tel]" value="delivery_tel"<?php usces_checked($chk_ord, 'delivery_tel'); ?> /><?php _e('Phone number', 'usces'); ?></label>
		<label for="chk_ord[delivery_fax]"><input type="checkbox" class="check_order" id="chk_ord[delivery_fax]" value="delivery_fax"<?php usces_checked($chk_ord, 'delivery_fax'); ?> /><?php _e('FAX number', 'usces'); ?></label>
<?php 
		break;
	case 'US':
	default:
?>
		<label for="chk_ord[delivery_address2]"><input type="checkbox" class="check_order" id="chk_ord[delivery_address2]" value="delivery_address2"<?php usces_checked($chk_ord, 'delivery_address2'); ?> /><?php _e('Address Line1', 'usces'); ?></label>
		<label for="chk_ord[delivery_address3]"><input type="checkbox" class="check_order" id="chk_ord[delivery_address3]" value="delivery_address3"<?php usces_checked($chk_ord, 'delivery_address3'); ?> /><?php _e('Address Line2', 'usces'); ?></label>
		<label for="chk_ord[delivery_address1]"><input type="checkbox" class="check_order" id="chk_ord[delivery_address1]" value="delivery_address1"<?php usces_checked($chk_ord, 'delivery_address1'); ?> /><?php _e('city', 'usces'); ?></label>
		<label for="chk_ord[delivery_pref]"><input type="checkbox" class="check_order" id="chk_ord[delivery_pref]" value="delivery_pref"<?php usces_checked($chk_ord, 'delivery_pref'); ?> /><?php _e('State', 'usces'); ?></label>
		<label for="chk_ord[delivery_country]"><input type="checkbox" class="check_order" id="chk_ord[delivery_country]" value="delivery_country"<?php usces_checked($chk_ord, 'delivery_country'); ?> /><?php _e('Country', 'usces'); ?></label>
		<label for="chk_ord[delivery_zip]"><input type="checkbox" class="check_order" id="chk_ord[delivery_zip]" value="delivery_zip"<?php usces_checked($chk_ord, 'delivery_zip'); ?> /><?php _e('Zip', 'usces'); ?></label>
		<label for="chk_ord[delivery_tel]"><input type="checkbox" class="check_order" id="chk_ord[delivery_tel]" value="delivery_tel"<?php usces_checked($chk_ord, 'delivery_tel'); ?> /><?php _e('Phone number', 'usces'); ?></label>
		<label for="chk_ord[delivery_fax]"><input type="checkbox" class="check_order" id="chk_ord[delivery_fax]" value="delivery_fax"<?php usces_checked($chk_ord, 'delivery_fax'); ?> /><?php _e('FAX number', 'usces'); ?></label>
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
				//$checked = ($chk_ord[$csde_key] == 1) ? ' checked' : '';
				$checked = usces_checked( $chk_ord, $csde_key, 'return' );
				$name = esc_attr($entry['name']);
				//echo '<label for="chk_ord['.$name.']"><input type="checkbox" class="check_order" id="chk_ord['.$name.']" value="'.$name.'"'.$checked.' />'.$name.'</label>';
				echo '<label for="chk_ord['.$csde_key.']"><input type="checkbox" class="check_order" id="chk_ord['.$csde_key.']" value="'.$csde_key.'"'.$checked.' />'.$name.'</label>'."\n";//20111116ysk 0000302
//20110208ysk end
			}
		}
	}
?>
		<?php do_action( 'usces_action_chk_ord_delivery', $chk_ord ); ?>
	</fieldset>
	<fieldset><legend><?php _e('Order Infomation', 'usces'); ?></legend>
		<label for="chk_ord[shipping_date]"><input type="checkbox" class="check_order" id="chk_ord[shipping_date]" value="shipping_date"<?php usces_checked($chk_ord, 'shipping_date'); ?> /><?php _e('shpping date', 'usces'); ?></label>
		<label for="chk_ord[peyment_method]"><input type="checkbox" class="check_order" id="chk_ord[peyment_method]" value="peyment_method"<?php usces_checked($chk_ord, 'peyment_method'); ?> /><?php _e('payment method','usces'); ?></label>
		<label for="chk_ord[delivery_method]"><input type="checkbox" class="check_order" id="chk_ord[delivery_method]" value="delivery_method"<?php usces_checked($chk_ord, 'delivery_method'); ?> /><?php _e('shipping option','usces'); ?></label>
<!--20101208ysk start-->
		<label for="chk_ord[delivery_date]"><input type="checkbox" class="check_order" id="chk_ord[delivery_date]" value="delivery_date"<?php usces_checked($chk_ord, 'delivery_date'); ?> /><?php _e('Delivery date','usces'); ?></label>
<!--20101208ysk end-->
		<label for="chk_ord[delivery_time]"><input type="checkbox" class="check_order" id="chk_ord[delivery_time]" value="delivery_time"<?php usces_checked($chk_ord, 'delivery_time'); ?> /><?php _e('delivery time','usces'); ?></label>
		<label for="chk_ord[delidue_date]"><input type="checkbox" class="check_order" id="chk_ord[delidue_date]" value="delidue_date"<?php usces_checked($chk_ord, 'delidue_date'); ?> /><?php _e('Shipping date', 'usces'); ?></label>
		<label for="chk_ord[status]"><input type="checkbox" class="check_order" id="chk_ord[status]" value="status"<?php usces_checked($chk_ord, 'status'); ?> /><?php _e('Status', 'usces'); ?></label>
		<label for="chk_ord[total_amount]"><input type="checkbox" class="check_order" id="chk_ord[total_amount]" value="total_amount" checked disabled /><?php _e('Total Amount', 'usces'); ?></label>
		<label for="chk_ord[getpoint]"><input type="checkbox" class="check_order" id="chk_ord[getpoint]" value="getpoint"<?php usces_checked($chk_ord, 'getpoint'); ?> /><?php _e('granted points', 'usces'); ?></label>
		<label for="chk_ord[usedpoint]"><input type="checkbox" class="check_order" id="chk_ord[usedpoint]" value="usedpoint"<?php usces_checked($chk_ord, 'usedpoint'); ?> /><?php _e('Used points', 'usces'); ?></label>
		<label for="chk_ord[discount]"><input type="checkbox" class="check_order" id="chk_ord[discount]" value="discount" checked disabled /><?php _e('Disnount', 'usces'); ?></label>
		<label for="chk_ord[shipping_charge]"><input type="checkbox" class="check_order" id="chk_ord[shipping_charge]" value="shipping_charge" checked disabled /><?php _e('Shipping', 'usces'); ?></label>
		<label for="chk_ord[cod_fee]"><input type="checkbox" class="check_order" id="chk_ord[cod_fee]" value="cod_fee" checked disabled /><?php echo apply_filters('usces_filter_cod_label', __('COD fee', 'usces')); ?></label>
		<label for="chk_ord[tax]"><input type="checkbox" class="check_order" id="chk_ord[tax]" value="tax" checked disabled /><?php _e('consumption tax', 'usces'); ?></label>
		<label for="chk_ord[note]"><input type="checkbox" class="check_order" id="chk_ord[note]" value="note"<?php usces_checked($chk_ord, 'note'); ?> /><?php _e('Notes', 'usces'); ?></label>
<?php 
	if(!empty($csod_meta)) {
		foreach($csod_meta as $key => $entry) {
//20110208ysk start
			$csod_key = 'csod_'.$key;
			//$checked = ($chk_ord[$entry['name']] == 1) ? ' checked' : '';
			//$checked = (isset($chk_ord[$csod_key]) && $chk_ord[$csod_key] == 1) ? ' checked' : '';
			$checked = usces_checked( $chk_ord, $csod_key, 'return' );
			$name = esc_attr($entry['name']);
			//echo '<label for="chk_ord['.$name.']"><input type="checkbox" class="check_order" id="chk_ord['.$name.']" value="'.$name.'"'.$checked.' />'.$name.'</label>';
			echo '<label for="chk_ord['.$csod_key.']"><input type="checkbox" class="check_order" id="chk_ord['.$csod_key.']" value="'.$csod_key.'"'.$checked.' />'.$name.'</label>'."\n";//20111116ysk 0000302
//20110208ysk end
		}
	}
?>
		<?php do_action( 'usces_action_chk_ord_order', $chk_ord ); ?>
	</fieldset>
</div>
<!--20100908ysk end-->
<?php echo apply_filters('usces_filter_order_list_footer', '');//20120612ysk 0000501 ?>

<!--<div class="chui">
<h3>受注詳細画面（作成中）について</h3>
<p>各行の受注番号をクリックすると受注詳細画面が表示されます。受注詳細画面では注文商品の追加、修正、削除など受注に関する全ての情報を編集することができま、問い合わせや電話での変更依頼に対応します。</p>
<p>「見積り」ステイタスを利用することで見積りをメール送信できます。見積書印刷でFAX対応も可能です。注文をいただいた場合は「受注」ステイタスに変更することで、見積りの内容がそのまま受注データとなります。</p>
<p>その他のステイタスには銀行振り込みの場合の「入金」ステイタス、発送完了した場合の「完了」、注文の「キャンセル」などがあり、各業務の終了後にステイタスを変更することを習慣付ければ、複数の担当者での業務もスムーズに行うことができます。</p>
</div>
-->
<?php wp_nonce_field( 'order_list', 'wc_nonce' ); ?>
</form>
<?php do_action( 'usces_action_order_list_footer' ); ?>
</div><!--usces_admin-->
</div><!--wrap-->
<script type="text/javascript">
//	rowSelection("mainDataTable");
</script>
