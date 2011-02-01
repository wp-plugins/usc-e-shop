<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';

$management_status = apply_filters( 'usces_filter_management_status', get_option('usces_management_status') );

if($order_action == 'new'){

	$oa = 'newpost';
	$taio = 'new';
	$admin = 'adminorder';
	$receipt = '';
	$ordercheck = array();
	$order_delivery_method = -1;
//20100818ysk start
	$csod_meta = usces_has_custom_field_meta('order');
	$cscs_meta = usces_has_custom_field_meta('customer');
	$csde_meta = usces_has_custom_field_meta('delivery');
//20100818ysk end

}else{

	$oa = 'editpost';

	$order_id = $_REQUEST['order_id'];
	
	global $wpdb;
	
	$tableName = $wpdb->prefix . "usces_order";
	$query = $wpdb->prepare("SELECT * FROM $tableName WHERE ID = %d", $order_id);
	$data = $wpdb->get_row( $query, ARRAY_A );

	$deli = stripslashes_deep(unserialize($data['order_delivery']));
	$cart = stripslashes_deep(unserialize($data['order_cart']));
	$condition = stripslashes_deep(unserialize($data['order_condition']));
	$ordercheck = stripslashes_deep(unserialize($data['order_check']));
	if( !is_array($ordercheck) ) $ordercheck = array();
	$order_delivery_method = $data['order_delivery_method'];
	
	if( !empty($data) ){
		$data = stripslashes_deep($data);
	}
	foreach ($management_status as $status_key => $status_name){
		if( in_array($status_key, array('noreceipt','receipted','pending', 'estimate', 'adminorder')) )
			continue;
			
		if($this->is_status($status_key, $data['order_status'])){
			$taio = $status_key;
			break;
		}else{
			$taio = 'new';
		}
	}

	if($this->is_status('estimate', $data['order_status']))
		$admin = 'estimate';
	else if($this->is_status('adminorder', $data['order_status']))
		$admin = 'adminorder';
	else
		$admin = '';
	
	if($this->is_status('noreceipt', $data['order_status']))
		$receipt = 'noreceipt';
	else if($this->is_status('receipted', $data['order_status']))
		$receipt = 'receipted';
	else if($this->is_status('pending', $data['order_status']))
		$receipt = 'pending';
	else
		$receipt = '';
		
//20100818ysk start
	$csod_meta = usces_has_custom_field_meta('order');
	if(is_array($csod_meta)) {
		$keys = array_keys($csod_meta);
		foreach($keys as $key) {
			$csod_key = 'csod_'.$key;
			$csod_meta[$key]['data'] = maybe_unserialize($this->get_order_meta_value($csod_key, $order_id));
		}
	}
	$cscs_meta = usces_has_custom_field_meta('customer');
	if(is_array($cscs_meta)) {
		$keys = array_keys($cscs_meta);
		foreach($keys as $key) {
			$cscs_key = 'cscs_'.$key;
			$cscs_meta[$key]['data'] = maybe_unserialize($this->get_order_meta_value($cscs_key, $order_id));
		}
	}
	$csde_meta = usces_has_custom_field_meta('delivery');
	if(is_array($csde_meta)) {
		$keys = array_keys($csde_meta);
		foreach($keys as $key) {
			$csde_key = 'csde_'.$key;
			$csde_meta[$key]['data'] = maybe_unserialize($this->get_order_meta_value($csde_key, $order_id));
		}
	}
//20100818ysk end
}

?>
<script type='text/javascript' src='<?php echo USCES_WP_PLUGIN_URL . '/usc-e-shop/js/jquery/jquery-ui-1.7.1.custom.min.js'; ?>'></script>
<script type='text/javascript' src='<?php echo USCES_WP_PLUGIN_URL . '/usc-e-shop/js/jquery/bgiframe/jquery.bgiframe.min.js'; ?>'></script>
<script type="text/javascript">
jQuery(function($){
<?php if($status == 'success'){ ?>
			$("#anibox").animate({ backgroundColor: "#ECFFFF" }, 2000);
<?php }else if($status == 'caution'){ ?>
			$("#anibox").animate({ backgroundColor: "#FFF5CE" }, 2000);
<?php }else if($status == 'error'){ ?>
			$("#anibox").animate({ backgroundColor: "#FFE6E6" }, 2000);
<?php } ?>
	var selected_delivery_time = '<?php esc_html_e($data['order_delivery_time']); ?>';
	var delivery_time = [];
<?php foreach((array)$this->options['delivery_method'] as $dmid => $dm){	$lines = split("\n", $dm['time']); ?>
	delivery_time[<?php echo $dm['id']; ?>] = [];
	<?php foreach((array)$lines as $line){ 	if(trim($line) != ''){ ?>
	delivery_time[<?php echo $dm['id']; ?>].push("<?php echo trim($line); ?>");
	<?php } } ?>
<?php } ?>

	$("#order_payment_name").change(function () {
		var pay_name = $("select[name='order\[payment_name\]'] option:selected").val();
//20101018ysk start
		//if( uscesPayments[pay_name] == 'transferAdvance' || uscesPayments[pay_name] == 'transferDeferred'){
		if( uscesPayments[pay_name] == 'transferAdvance' || uscesPayments[pay_name] == 'transferDeferred' || uscesPayments[pay_name] == 'acting_remise_conv' || uscesPayments[pay_name] == 'acting_zeus_bank' || uscesPayments[pay_name] == 'acting_zeus_conv' || uscesPayments[pay_name] == 'acting_jpayment_conv' || uscesPayments[pay_name] == 'acting_jpayment_bank'){
//20101018ysk end
			var label = '<?php _e('transfer statement', 'usces'); ?>';
			var html = "<select name='order[receipt]'>\n";
			html += "<option value='noreceipt'><?php echo $management_status['noreceipt']; ?></option>\n";
			html += "<option value='receipted'><?php echo $management_status['receipted']; ?></option>\n";
			html += "</select>\n";
			$("#receiptlabel").html(label);
			$("#receiptbox").html(html);
		}else{
			$("#receiptlabel").html('');
			$("#receiptbox").html('');
		}
	});
	
	$("#addItemDialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 500,
		width: 700,
		resizable: true,
		modal: true,
		buttons: {
			'<?php _e('close', 'usces'); ?>': function() {
				$(this).dialog('close');
			}
		},
		close: function() {
			$("#newitemform").html( "" );
			$('#newitemcode').val('');
		}
	});
	$('#addCartButton').click(function() {
		if($("input[name='order_id']").val() == ''){
			alert("<?php _e("Push 'change decision' button, to be settled with an order number.", 'usces'); ?>");
			return;
		}
		$('#addItemDialog').dialog('open');
	});
	$('#delivery_method_select').change(function() {
		orderfunc.make_delivery_time($('#delivery_method_select option:selected').val());
	});
	
	$("#mailSendDialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 650,
		width: 700,
		resizable: true,
		modal: true,
		buttons: {
			'<?php _e('close', 'usces'); ?>': function() {
				$(this).dialog('close');
			}
		},
		close: function() {
			$("#sendmailmessage").html( "" );
			$('#sendmailaddress').val('');
		}
	});
	$('#completionMail').click(function() {
		orderItem.getmailmessage('completionMail');
		$("#sendmailaddress").val($("input[name='customer\[mailaddress\]']").val());
		$("#sendmailname").val($("input[name='customer\[name1\]']").val()+$("input[name='customer\[name2\]']").val());
		$("#sendmailsubject").val('<?php echo esc_js($this->options['mail_data']['title']['completionmail']); ?>');
		$('#mailChecked').val('completionmail');
		$('#mailSendDialog').dialog('option', 'title', '<?php _e('Mail for Shipping', 'usces'); ?>');
		$('#mailSendDialog').dialog('open');
	});
	$('#orderConfirmMail').click(function() {
		orderItem.getmailmessage('orderConfirmMail');
		$("#sendmailaddress").val($("input[name='customer\[mailaddress\]']").val());
		$("#sendmailname").val($("input[name='customer\[name1\]']").val()+$("input[name='customer\[name2\]']").val());
		$("#sendmailsubject").val('<?php echo esc_js($this->options['mail_data']['title']['ordermail']); ?>');
		$('#mailChecked').val('ordermail');
		$('#mailSendDialog').dialog('option', 'title', '<?php _e('Mail for confirmation of order', 'usces'); ?>');
		$('#mailSendDialog').dialog('open');
	});
	$('#changeConfirmMail').click(function() {
		orderItem.getmailmessage('changeConfirmMail');
		$("#sendmailaddress").val($("input[name='customer\[mailaddress\]']").val());
		$("#sendmailname").val($("input[name='customer\[name1\]']").val()+$("input[name='customer\[name2\]']").val());
		$("#sendmailsubject").val('<?php echo esc_js($this->options['mail_data']['title']['changemail']); ?>');
		$('#mailChecked').val('changemail');
		$('#mailSendDialog').dialog('option', 'title', '<?php _e('Mail for confiemation of change', 'usces'); ?>');
		$('#mailSendDialog').dialog('open');
	});
	$('#receiptConfirmMail').click(function() {
		orderItem.getmailmessage('receiptConfirmMail');
		$("#sendmailaddress").val($("input[name='customer\[mailaddress\]']").val());
		$("#sendmailname").val($("input[name='customer\[name1\]']").val()+$("input[name='customer\[name2\]']").val());
		$("#sendmailsubject").val('<?php echo esc_js($this->options['mail_data']['title']['receiptmail']); ?>');
		$('#mailChecked').val('receiptmail');
		$('#mailSendDialog').dialog('option', 'title', '<?php _e('Mail for confirmation of transter', 'usces'); ?>');
		$('#mailSendDialog').dialog('open');
	});
	$('#mitumoriConfirmMail').click(function() {
		orderItem.getmailmessage('mitumoriConfirmMail');
		$("#sendmailaddress").val($("input[name='customer\[mailaddress\]']").val());
		$("#sendmailname").val($("input[name='customer\[name1\]']").val()+$("input[name='customer\[name2\]']").val());
		$("#sendmailsubject").val('<?php echo esc_js($this->options['mail_data']['title']['mitumorimail']); ?>');
		$('#mailChecked').val('mitumorimail');
		$('#mailSendDialog').dialog('option', 'title', '<?php _e('estimate mail', 'usces'); ?>');
		$('#mailSendDialog').dialog('open');
	});
	$('#cancelConfirmMail').click(function() {
		orderItem.getmailmessage('cancelConfirmMail');
		$("#sendmailaddress").val($("input[name='customer\[mailaddress\]']").val());
		$("#sendmailname").val($("input[name='customer\[name1\]']").val()+$("input[name='customer\[name2\]']").val());
		$("#sendmailsubject").val('<?php echo esc_js($this->options['mail_data']['title']['cancelmail']); ?>');
		$('#mailChecked').val('cancelmail');
		$('#mailSendDialog').dialog('option', 'title', '<?php _e('Cancelling mail', 'usces'); ?>');
		$('#mailSendDialog').dialog('open');
	});
	$('#otherConfirmMail').click(function() {
		orderItem.getmailmessage('otherConfirmMail');
		$("#sendmailaddress").val($("input[name='customer\[mailaddress\]']").val());
		$("#sendmailname").val($("input[name='customer\[name1\]']").val()+$("input[name='customer\[name2\]']").val());
		$("#sendmailsubject").val('<?php echo esc_js($this->options['mail_data']['title']['othermail']); ?>');
		$('#mailChecked').val('othermail');
		$('#mailSendDialog').dialog('option', 'title', '<?php _e('Other mail', 'usces'); ?>');
		$('#mailSendDialog').dialog('open');
	});
	
	$('#mitumoriprint').click(function() {
		pdfWindow('mitumori');
		uscesMail.ordercheckpost('mitumoriprint');
	});
	$('#nohinprint').click(function() {
		pdfWindow('nohin');
		uscesMail.ordercheckpost('nohinprint');
	});
	$("#mailSendAlert").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 200,
		width: 200,
		resizable: false,
		modal: false
	});
	$("#sendmail").click(function() {
		uscesMail.sendmail();
	});
	
//	$('#addItemDialog').bind("ajaxComplete", function(){
//  		$('#addItemDialog').dialog('close');
//	});
	orderfunc = {
		sumPrice : function() {
			var p = $("input[name*='skuPrice']");
			var q = $("input[name*='quant']");
			var t = $("td[id*='sub_total']");
			var db = $("input[name*='delButton']");
			var price = [];
			var quant = [];
			var sub_total = 0;
			var total_full = 0;
			for( var i = 0; i < p.length; i++) {
				v = $(p[i]).val() * $(q[i]).val();
				$(t[i]).html(addComma(v+''));
				sub_total += v;
			}
			$("#item_total").html(addComma(sub_total+''));
			var order_usedpoint = $("#order_usedpoint").val()*1;
			var order_discount = $("#order_discount").val()*1;
			var order_shipping_charge = $("#order_shipping_charge").val()*1;
			var order_cod_fee = $("#order_cod_fee").val()*1;
			var order_tax = $("#order_tax").val()*1;
			total_full = sub_total - order_usedpoint + order_discount + order_shipping_charge + order_cod_fee + order_tax;
			$("#total_full").html(addComma(total_full+''));
			$("#total_full_top").html(addComma(total_full+''));
		},
		
		make_delivery_time : function(selected) {
			var option = '';
			if(selected == -1 || delivery_time[selected] == undefined){
				option += '<option value="<?php _e('Non-request', 'usces'); ?>"><?php _e('Non-request', 'usces'); ?></option>' + "\n";
			}else{
				for(var i=0; i<delivery_time[selected].length; i++){
					if( delivery_time[selected][i] == selected_delivery_time ) {
						option += '<option value="' + delivery_time[selected][i] + '" selected="selected">' + delivery_time[selected][i] + '</option>' + "\n";
					}else{
						option += '<option value="' + delivery_time[selected][i] + '">' + delivery_time[selected][i] + '</option>' + "\n";
					}
				}
			}
			$("#delivery_time_select").html(option);
			
		}
	};
	
	uscesMail = {
		sendmail : function() {
			if($("#sendmailaddress").val() == "") return;
		
			var address = encodeURIComponent($("#sendmailaddress").val());
			var message = encodeURIComponent($("#sendmailmessage").val());
			var name = encodeURIComponent($("#sendmailname").val());
			var subject = encodeURIComponent($("#sendmailsubject").val());
			var order_id = $("#order_id").val();
			var checked = $("#mailChecked").val();
			
			var s = uscesMail.settings;
			s.data = "action=order_item_ajax&mode=sendmail&mailaddress=" + address + "&message=" + message + "&name=" + name + "&subject=" + subject + "&order_id=" + order_id + "&checked=" + checked;
			s.success = function(data, dataType){
				if(data == 'success') {
					if(checked == 'completionmail'){
						$("input[name='check\[completionmail\]']").attr({checked: true});
					}else if(checked == 'ordermail'){
						$("input[name='check\[ordermail\]']").attr({checked: true});
					}else if(checked == 'changemail'){
						$("input[name='check\[changemail\]']").attr({checked: true});
					}else if(checked == 'receiptmail'){
						$("input[name='check\[receiptmail\]']").attr({checked: true});
					}else if(checked == 'mitumorimail'){
						$("input[name='check\[mitumorimail\]']").attr({checked: true});
					}else if(checked == 'cancelmail'){
						$("input[name='check\[cancelmail\]']").attr({checked: true});
					}else if(checked == 'othermail'){
						$("input[name='check\[othermail\]']").attr({checked: true});
					}
					
					$('#mailSendAlert').dialog('option', 'buttons', {
															'OK': function() {
																	$(this).dialog('close');
																	$('#mailSendDialog').dialog('close');
																}
															});
					$('#mailSendAlert fieldset').dialog('option', 'title', 'SUCCESS');
					$('#mailSendAlert fieldset').html('<p><?php _e('E-mail has been sent.', 'usces'); ?></p>');
					$('#mailSendAlert').dialog('open');
					
				}else if(data == 'error'){
					$('#mailSendAlert').dialog('option', 'buttons', {
															'OK': function() {
																	$(this).dialog('close');
																}
															});
					$('#mailSendAlert fieldset').dialog('option', 'title', 'ERROR');
					$('#mailSendAlert fieldset').html('<p><?php _e('Failure in sending e-mails.', 'usces'); ?></p>');
					$('#mailSendAlert').dialog('open');
				}
			};
			s.error = function(data, dataType){
				$('#mailSendAlert').dialog('option', 'buttons', {
														'OK': function() {
																$(this).dialog('close');
															}
														});
				$('#mailSendAlert fieldset').dialog('option', 'title', 'ERROR');
				$('#mailSendAlert fieldset').html('<p><?php _e('Failure in sending e-mails.', 'usces'); ?></p>');
				$('#mailSendAlert').dialog('open');
			};
			$.ajax( s );
			return false;
		},
		ordercheckpost : function( checked ) {
			var p = uscesMail.settings;
			var order_id = $("#order_id").val();
			p.url = uscesL10n.requestFile;
			p.data = "action=order_item_ajax&mode=ordercheckpost&order_id=" + order_id + "&checked=" + checked;
			p.success = function(data, dataType){
				if(data == 'mitumoriprint'){
					$("input[name='check\[mitumoriprint\]']").attr({checked: true});
				}else if(data == 'nohinprint'){
					$("input[name='check\[nohinprint\]']").attr({checked: true});
				}
			};
			p.error = function(data, dataType){
				//$("#ajax-response").html(msg);
			};
			$.ajax( p );
			return false;
		},
		settings: {
			url: uscesL10n.requestFile,
			type: 'POST',
			cache: false,
			success: function(data, dataType){
			}, 
			error: function(msg){
				//$("#ajax-response").html(msg);
			}
		}
	};
});

function toggleVisibility(id) {
	var e = document.getElementById(id);
	if(e.style.display == 'block') {
		e.style.display = 'none';
		//document.getElementById("searchSwitchStatus").value = 'OFF';
	} else {
		e.style.display = 'block';
		//document.getElementById("searchSwitchStatus").value = 'ON';
		//document.getElementById("searchVisiLink").style.display = 'none';
	}
};

function addComma(str)
{
	cnt = 0;
	n   = "";
	for (i=str.length-1; i>=0; i--)
	{
		n = str.charAt(i) + n;
		cnt++;
		if (((cnt % 3) == 0) && (i != 0)) n = ","+n;
	}
	return n;
};

function pdfWindow( type ) {
	var wx = 800;
	var wy = 900;
	var x = (screen.width- wx) / 2;
	var y = (screen.height - wy) / 2;
	x = 0;
	y = 0;
	printWin = window.open("<?php echo USCES_ADMIN_URL.'?page=usces_orderlist&order_action=pdfout&order_id='.$order_id; ?>"+"&type="+type,"sub","left="+x+",top="+y+",width="+wx+",height="+wy+",scrollbars=yes");
}

jQuery(document).ready(function($){
	var p = $("input[name*='skuPrice']");
	var q = $("input[name*='quant']");
	var t = $("td[id*='sub_total']");
	var db = $("input[name*='delButton']");

	orderfunc.sumPrice();
	
	for( var i = 0; i < p.length; i++) {
		$(p[i]).bind("change", function(){ orderfunc.sumPrice(); });
		$(q[i]).bind("change", function(){ orderfunc.sumPrice(); });
		$(db[i]).bind("click", function(){ return delConfirm(); });
	}
	$("#order_usedpoint").bind("change", function(){ orderfunc.sumPrice(); });
	$("#order_discount").bind("change", function(){ orderfunc.sumPrice(); });
	$("#order_shipping_charge").bind("change", function(){ orderfunc.sumPrice(); });
	$("#order_cod_fee").bind("change", function(){ orderfunc.sumPrice(); });
	$("#order_tax").bind("change", function(){ orderfunc.sumPrice(); });
	$("input[name*='upButton']").click(function(){
		if( ('completion' == $("#order_taio option:selected").val() || 'continuation' == $("#order_taio option:selected").val()) && '<?php echo substr(get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 0, 10); ?>' != $('#modified').val() ){
			if( confirm("<?php _e("更新日を今日の日付に変更しますか？", 'usces'); ?>\n<?php _e("更新日日を変更せずに更新する場合はキャンセルを押してください。", 'usces'); ?>") ){
				$('#up_modified').val('update');
			}else{
				$('#up_modified').val('');
			}
		}
		return true;
	});
	
	function delConfirm(){
		if(confirm('<?php _e('Are you sure of deleting items?', 'usces'); ?>')){
			return true;
		}else{
			return false;
		}
	}
	
	orderfunc.make_delivery_time(<?php echo $order_delivery_method; ?>);
});
</script>
<div class="wrap">
<div class="usces_admin">
<form action="<?php echo USCES_ADMIN_URL.'?page=usces_orderlist&order_action='.$oa; ?>" method="post" name="editpost">

<h2>Welcart Management <?php _e('Edit order data','usces'); ?></h2>
<p class="version_info">Version <?php echo USCES_VERSION; ?></p>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>
<div class="mailVisiLink">
<a style="cursor:pointer;" id="mailVisiLink" onclick="toggleVisibility('mailBox');"><?php _e('show the mail/print field', 'usces'); ?></a><br /><a href="<?php if(isset($_REQUEST['usces_referer'])) echo $_REQUEST['usces_referer']; ?>"><?php _e('Back', 'usces'); ?></a>
</div>
<div class="ordernavi"><input name="upButton" class="upButton" type="submit" value="<?php _e('change decision', 'usces'); ?>" /><?php _e("When you change amount, please click 'Edit' before you finish your process.", 'usces'); ?></div>
<div id="mailBox">
<table>
<tr>
<td><input name="check[ordermail]" type="checkbox" value="ordermail"<?php if(isset($ordercheck['ordermail'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="orderConfirmMail"><?php _e('Mail for confirmation of order', 'usces'); ?></a></td>
<td><input name="check[changemail]" type="checkbox" value="changemail"<?php if(isset($ordercheck['changemail'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="changeConfirmMail"><?php _e('Mail for confiemation of change', 'usces'); ?></a></td>
<td><input name="check[receiptmail]" type="checkbox" value="receiptmail"<?php if(isset($ordercheck['receiptmail'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="receiptConfirmMail"><?php _e('Mail for confirmation of transter', 'usces'); ?></a></td>
<td><input name="check[mitumorimail]" type="checkbox" value="mitumorimail"<?php if(isset($ordercheck['mitumorimail'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="mitumoriConfirmMail"><?php _e('estimate mail', 'usces'); ?></a></td>
<td><input name="check[cancelmail]" type="checkbox" value="cancelmail"<?php if(isset($ordercheck['cancelmail'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="cancelConfirmMail"><?php _e('Cancelling mail', 'usces'); ?></a></td>
<td><input name="check[othermail]" type="checkbox" value="othermail"<?php if(isset($ordercheck['othermail'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="otherConfirmMail"><?php _e('Other mail', 'usces'); ?></a></td>
</tr>
<tr>
<td><input name="check[completionmail]" type="checkbox" value="completionmail"<?php if(isset($ordercheck['completionmail'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="completionMail"><?php _e('Mail for Shipping', 'usces'); ?></a></td>
<td><input name="check[mitumoriprint]" type="checkbox" value="mitumoriprint"<?php if(isset($ordercheck['mitumoriprint'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="mitumoriprint"><?php _e('print out the estimate', 'usces'); ?></a></td>
<td><input name="check[nohinprint]" type="checkbox" value="nohinprint"<?php if(isset($ordercheck['nohinprint'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="nohinprint"><?php _e('print the invoice', 'usces'); ?></a></td>
<td colspan="7"><span style="color:#CC3300"><?php _e("When there is any change, please press the 'change decision' before you send or print.", 'usces'); ?></span></td>
</tr>
</table>
</div>
<div class="info_head">
<table>
<tr>
<td colspan="6" class="midasi0"><?php _e('order details', 'usces'); ?></td>
</tr>
<tr>
<td class="label border"><?php _e('Order number', 'usces'); ?></td><td class="col1 border"><div class="rod large short"><?php esc_html_e($data['ID']); ?></div></td>
<td class="col3 label border"><?php _e('order date', 'usces'); ?></td><td class="col2 border"><div class="rod long"><?php esc_html_e($data['order_date']); ?></div></td>
<td class="label border"><?php echo apply_filters('usces_filter_admin_modified_label', __('shpping date', 'usces') ); ?></td><td class="border"><div id="order_modified" class="rod long"><?php esc_html_e($data['order_modified']); ?></div></td>
</tr>
<tr>
<td class="label"><?php _e('membership number', 'usces'); ?></td><td class="col1"><div class="rod large short"><?php esc_html_e($data['mem_id']); ?></div></td>
<td colspan="2" rowspan="9" class="wrap_td">
	<table border="0" cellspacing="0" class="cus_info">
    <tr>
        <td class="label">e-mail</td>
        <td class="col2"><input name="customer[mailaddress]" type="text" class="text long" value="<?php echo esc_attr($data['order_email']); ?>" /></td>
    </tr>
	<?php
//20100818ysk start
	usces_admin_custom_field_input($cscs_meta, 'customer', 'name_pre');
//20100818ysk end
	?>
    <tr>
        <td class="label"><?php _e('name', 'usces'); ?> </td>
        <td class="col2"><input name="customer[name1]" type="text" class="text short" value="<?php echo esc_attr($data['order_name1']); ?>" /><input name="customer[name2]" type="text" class="text short" value="<?php echo esc_attr($data['order_name2']); ?>" /></td>
    </tr>
    <tr>
        <td class="label"><?php _e('furigana', 'usces'); ?></td>
        <td class="col2"><input name="customer[name3]" type="text" class="text short" value="<?php echo esc_attr($data['order_name3']); ?>" /><input name="customer[name4]" type="text" class="text short" value="<?php echo esc_attr($data['order_name4']); ?>" /></td>
    </tr>
	<?php
//20100818ysk start
	usces_admin_custom_field_input($cscs_meta, 'customer', 'name_after');
//20100818ysk end
	?>
    <tr>
        <td class="label"><?php _e('Zip/Postal Code', 'usces'); ?></td>
        <td class="col2"><input name="customer[zipcode]" type="text" class="text short" value="<?php echo esc_attr($data['order_zip']); ?>" /></td>
    </tr>
    <tr>
        <td class="label"><?php _e('Province', 'usces'); ?></td>
        <td class="col2"><select name="customer[pref]" class="select">
        <?php
//	$prefs = get_option('usces_pref');
	$prefs = $this->options['province'];
foreach((array)$prefs as $value) {
	$selected = ($data['order_pref'] == $value) ? ' selected="selected"' : '';
	echo "\t<option value='" . esc_attr($value) . "'{$selected}>" . esc_html($value) . "</option>\n";
}
?>
        </select></td>
    </tr>
    <tr>
        <td class="label"><?php _e('city', 'usces'); ?></td>
        <td class="col2"><input name="customer[address1]" type="text" class="text long" value="<?php echo esc_attr($data['order_address1']); ?>" /></td>
    </tr>
    <tr>
        <td class="label"><?php _e('numbers', 'usces'); ?></td>
        <td class="col2"><input name="customer[address2]" type="text" class="text long" value="<?php echo esc_attr($data['order_address2']); ?>" /></td>
    </tr>
    <tr>
        <td class="label"><?php _e('building name', 'usces'); ?></td>
        <td class="col2"><input name="customer[address3]" type="text" class="text long" value="<?php echo esc_attr($data['order_address3']); ?>" /></td>
    </tr>
    <tr>
        <td class="label"><?php _e('Phone number', 'usces'); ?></td>
        <td class="col2"><input name="customer[tel]" type="text" class="text long" value="<?php echo esc_attr($data['order_tel']); ?>" /></td>
    </tr>
    <tr>
        <td class="label"><?php _e('FAX number', 'usces'); ?></td>
        <td class="col2"><input name="customer[fax]" type="text" class="text long" value="<?php echo esc_attr($data['order_fax']); ?>" /></td>
    </tr>
	<?php
//20100818ysk start
	usces_admin_custom_field_input($cscs_meta, 'customer', 'fax_after');
//20100818ysk end
	?>
</table></td>
<td colspan="2" class="midasi1"><?php _e('shipping address', 'usces'); ?></td>
</tr>
<tr>
<td class="label"><?php _e('payment method', 'usces'); ?></td>
<td class="col1"><select name="order[payment_name]" id="order_payment_name">
    <option value="#none#"><?php _e('-- Select --', 'usces'); ?></option>
<?php 
if( $this->options['payment_method'] ) {
	foreach ((array)$this->options['payment_method'] as $payments) {
	if( $payments['name'] != '' ) {
		$selected = ($payments['name'] == $data['order_payment_name']) ? ' selected="selected"' : '';
?>
    <option value="<?php echo esc_attr($payments['name']); ?>"<?php echo $selected; ?>><?php echo esc_attr($payments['name']); ?></option>
<?php } } } ?>
</select></td>
<td colspan="2" rowspan="8" class="wrap_td">
<table border="0" cellspacing="0" class="deli_info">
	<?php
//20100818ysk start
	usces_admin_custom_field_input($csde_meta, 'delivery', 'name_pre');
//20100818ysk end
	?>
    <tr>
        <td class="label"><?php _e('name', 'usces'); ?></td>
        <td class="col3"><input name="delivery[name1]" type="text" class="text short" value="<?php echo esc_attr($deli['name1']); ?>" />    <input name="delivery[name2]" type="text" class="text short" value="<?php echo esc_attr($deli['name2']); ?>" /></td>
    </tr>
    <tr>
        <td class="label"><?php _e('furigana', 'usces'); ?></td>
        <td class="col3"><input name="delivery[name3]" type="text" class="text short" value="<?php echo esc_attr($deli['name3']); ?>" />    <input name="delivery[name4]" type="text" class="text short" value="<?php echo esc_attr($deli['name4']); ?>" /></td>
    </tr>
	<?php
//20100818ysk start
	usces_admin_custom_field_input($csde_meta, 'delivery', 'name_after');
//20100818ysk end
	?>
    <tr>
        <td class="label"><?php _e('Zip/Postal Code', 'usces'); ?></td>
        <td class="col3"><input name="delivery[zipcode]" type="text" class="text short" value="<?php echo esc_attr($deli['zipcode']); ?>" /></td>
    </tr>
    <tr>
        <td class="label"><?php _e('Province', 'usces'); ?></td>
        <td class="col3"><select name="delivery[pref]">
    <?php
//	$prefs = get_option('usces_pref');
	$prefs = $this->options['province'];
foreach((array)$prefs as $value) {
	$selected = ($deli['pref'] == $value) ? ' selected="selected"' : '';
	echo "\t<option value='" . esc_attr($value) . "'{$selected}>" . esc_html($value) . "</option>\n";
}
?>
    </select></td>
    </tr>
    <tr>
        <td class="label"><?php _e('city', 'usces'); ?></td>
        <td class="col3"><input name="delivery[address1]" type="text" class="text long" value="<?php echo esc_attr($deli['address1']); ?>" /></td>
    </tr>
    <tr>
        <td class="label"><?php _e('numbers', 'usces'); ?></td>
        <td class="col3"><input name="delivery[address2]" type="text" class="text long" value="<?php echo esc_attr($deli['address2']); ?>" /></td>
    </tr>
    <tr>
        <td class="label"><?php _e('building name', 'usces'); ?></td>
        <td class="col3"><input name="delivery[address3]" type="text" class="text long" value="<?php echo esc_attr($deli['address3']); ?>" /></td>
    </tr>
    <tr>
        <td class="label"><?php _e('Phone number', 'usces'); ?></td>
        <td class="col3"><input name="delivery[tel]" type="text" class="text long" value="<?php echo esc_attr($deli['tel']); ?>" /></td>
    </tr>
    <tr>
        <td class="label"><?php _e('FAX number', 'usces'); ?></td>
        <td class="col3"><input name="delivery[fax]" type="text" class="text long" value="<?php echo esc_attr($deli['fax']); ?>" /></td>
    </tr>
	<?php
//20100818ysk start
	usces_admin_custom_field_input($csde_meta, 'delivery', 'fax_after');
//20100818ysk end
	?>
</table></td>
</tr>
<tr>
<td class="label"><?php _e('shipping option','usces'); ?></td>
<td class="col1"><select name="order[delivery_method]" id="delivery_method_select">
	<option value="-1"><?php _e('Non-request', 'usces'); ?></option>
<?php
foreach ((array)$this->options['delivery_method'] as $dkey => $delivery) {
	$selected = $order_delivery_method == $delivery['id'] ? ' selected="selected"' : '';
	echo "\t<option value='" . esc_attr($delivery['id']) . "'{$selected}>" . esc_attr($delivery['name']) . "</option>\n";
}
?>
</select></td>
<?php if( USCES_JP ): ?>
</tr>
<?php endif; ?>
<!--20101208ysk start-->
<tr>
<td class="label"><?php _e('Delivery date', 'usces'); ?></td>
<td class="col1"><select name="order[delivery_date]" id="delivery_date_select">
	<option value='<?php _e('Non-request', 'usces'); ?>'><?php _e('Non-request', 'usces'); ?></option>
<?php
$data_order_date = explode(" ", $data['order_date']);
$order_date = explode("-", $data_order_date[0]);
for($i = 0; $i < 50; $i++) {
	$date = date(__( 'M j, Y', 'usces' ), mktime(0,0,0,$order_date[1],$order_date[2]+$i,$order_date[0]));
	$selected = ($data['order_delivery_date'] == $date) ? ' selected="selected"' : '';
	echo "\t<option value='{$date}'{$selected}>{$date}</option>\n";
}
?>
</select></td>
</tr>
<!--20101208ysk end-->
<tr>
<td class="label"><?php _e('delivery time', 'usces'); ?></td>
<td class="col1"><select name="order[delivery_time]" id="delivery_time_select">
	<option value='<?php _e('Non-request', 'usces'); ?>'><?php _e('Non-request', 'usces'); ?></option>
<?php
if( !$this->options['delivery_time'] == '' ) {
	$array = explode("\n", $this->options['delivery_time']);
	foreach ((array)$array as $delivery) {
		$delivery = trim($delivery);
		if( $delivery != '' ) {
			$selected = ($delivery == $value) ? ' selected="selected"' : '';
			echo "\t<option value='" . esc_attr($delivery) . "'{$selected}>" . esc_html($delivery) . "</option>\n";
		}
	}
}
?>
</select></td>
</tr>
<tr>
<td class="label"><?php _e('Shipping date', 'usces'); ?></td>
<td class="col1"><select name="order[delidue_date]">
    <option value="#none#"><?php _e('Not notified', 'usces'); ?></option>
<?php
for ($i=0; $i<50; $i++) {
	$date = date(__( 'M j, Y', 'usces' ), mktime(0,0,0,date('m'),date('d')+$i,date('Y')));
	$selected = ($data['order_delidue_date'] == $date) ? ' selected="selected"' : '';
	echo "\t<option value='{$date}'{$selected}>{$date}</option>\n";
}
?>
</select></td>
</tr>
<tr>
<td colspan="2" class="midasi3"><?php _e('Status', 'usces'); ?></td>
</tr>
<tr>
<td class="label status"><?php _e('The correspondence situation', 'usces'); ?></td>
<td class="col1 status">
<select name="order[taio]" id="order_taio">
	<option value='#none#'><?php _e('new order', 'usces'); ?></option>
<?php 
	foreach ($management_status as $status_key => $status_name){
		if( in_array($status_key, array('noreceipt','receipted','pending', 'estimate', 'adminorder')) )
			continue;
?>
	<option value="<?php echo $status_key; ?>"<?php if($taio == $status_key){ echo ' selected="selected"';} ?>><?php echo $status_name; ?></option>
<?php 
	}
?>
<!--	<option value='cancel'<?php if($taio == 'cancel'){ echo 'selected="selected"';} ?>><?php echo $management_status['cancel']; ?></option>
	<option value='completion'<?php if($taio == 'completion'){ echo 'selected="selected"';} ?>><?php echo $management_status['completion']; ?></option>
	<option value='continuation'<?php if($taio == 'continuation'){ echo 'selected="selected"';} ?>><?php echo $management_status['continuation']; ?></option>
	<option value='termination'<?php if($taio == 'termination'){ echo 'selected="selected"';} ?>><?php echo $management_status['termination']; ?></option>
--></select></td>
</tr>
<tr>
<td class="label status" id="receiptlabel"><?php if($receipt != ''){echo __('transfer statement', 'usces');}else{echo '&nbsp';} ?></td>
<td class="col1 status" id="receiptbox">
<?php if($receipt != '') : ?>
<select name="order[receipt]">
	<option value='noreceipt'<?php if($receipt == 'noreceipt'){ echo ' selected="selected"';} ?>><?php echo $management_status['noreceipt']; ?></option>
	<option value='receipted'<?php if($receipt == 'receipted'){ echo ' selected="selected"';} ?>><?php echo $management_status['receipted']; ?></option>
	<option value='pending'<?php if($receipt == 'pending'){ echo ' selected="selected"';} ?>><?php echo $management_status['pending']; ?></option>
</select>
<?php else : ?>
&nbsp
<?php endif; ?></td>
</tr>
<tr>
<td class="label status"><?php if($admin != ''){echo __('estimate order', 'usces');}else{echo '&nbsp';} ?></td>
<td class="col1 status">
<?php if($admin != '') : ?>
<select name="order[admin]">
	<option value='adminorder'<?php if($admin == 'adminorder'){ echo 'selected="selected"';} ?>><?php echo $management_status['adminorder']; ?></option>
	<option value='estimate'<?php if($admin == 'estimate'){ echo 'selected="selected"';} ?>><?php echo $management_status['estimate']; ?></option>
</select>
<?php else : ?>
&nbsp
<?php endif; ?></td>
</tr>
<tr>
<td colspan="2" class="status">
<div class="midasi2"><?php if($condition['display_mode'] == 'Usualsale'){echo __('normal sale', 'usces');}elseif($condition['display_mode'] == 'Promotionsale'){echo __('Sale Campaign', 'usces');} ?></div>
<div class="condition">
<?php if ( $condition['display_mode'] == 'Promotionsale' ) : ?>
<span><?php _e('Special Benefits', 'usces'); ?> : </span><?php echo $condition["campaign_privilege"]; ?> (<?php if($condition["campaign_privilege"] == 'discount'){echo esc_html($condition["privilege_discount"]).__('% Discount', 'usces');}elseif($condition["campaign_privilege"] == 'point'){echo esc_html($condition["privilege_point"]).__(" times (limited to members)", 'usces');} ?>) <br />
<span><?php _e('applied material', 'usces'); ?> : </span><?php if($condition["campaign_category"] == 0){echo __('all the items', 'usces');} else {echo esc_html(get_cat_name($condition["campaign_category"]));} ?><br />
<?php endif; ?>
</div></td>
<td class="label"><?php _e('Notes', 'usces'); ?></td>
<td colspan="3"><textarea name="order[note]"><?php echo esc_attr($data['order_note']); ?></textarea></td>
</tr>
</table>
</div>
<?php //if( $this->has_order_custom_info( $order_id ) ): ?>
<div class="info_head">
<table>
<tr>
<td class="midasi0">Order Custom Field</td>
<td class="midasi0">Settlement Information</td>
</tr>
<tr>
<td class="wrap_td">
<table class="order_custom_wrap">
<tr>
<?php
//20100818ysk start
usces_admin_custom_field_input($csod_meta, 'order', '');
//20100818ysk end
?>
</tr>

</table>
</td>
<td class="wrap_td">
<table class="settle_info_wrap">
<?php usces_settle_info_field( $order_id, 'tr' ); ?>
</table>
</td>
</tr>
</table>
</div>
<?php //endif; ?>
<div id="cart">
<table cellspacing="0" id="cart_table">
	<thead>
		<tr>
			<th colspan="5" class="aright"><?php _e('Total Amount','usces'); ?></th>
			<th id="total_full_top" class="aright">&nbsp;</th>
			<th colspan="2">&nbsp;</th>
		</tr>
	<tr>
		<th scope="row" class="num"><?php echo __('No.','usces'); ?></th>
		<th class="thumbnail"> <?php //echo __('thumbnail','usces'); ?></th>
		<th><?php _e('Items','usces'); ?></th>
		<th class="price"><?php _e('Unit price','usces'); ?></th>
		<th class="quantity"><?php _e('Quantity','usces'); ?></th>
		<th class="subtotal"><?php _e('Amount','usces'); ?></th>
		<th class="stock"><?php _e('Current stock', 'usces'); ?></th>
		<th class="action"><input name="addButton" id="addCartButton" class="addCartButton" type="button" value="<?php _e('Add item', 'usces'); ?>" /></th>
	</tr>
	</thead>
	<tbody id="orderitemlist">
<?php
	for($i=0; $i<count($cart); $i++) { 
		$cart_row = $cart[$i];
		$post_id = $cart_row['post_id'];
		$sku = $cart_row['sku'];
		$quantity = $cart_row['quantity'];
		$options = $cart_row['options'];
		$advance = $this->cart->wc_serialize($cart_row['advance']);
		$itemCode = $this->getItemCode($post_id);
		$itemName = $this->getItemName($post_id);
		$cartItemName = $this->getCartItemName($post_id, $sku);
		$skuPrice = $cart_row['price'];
		$stock = $this->getItemZaiko($post_id, $sku);
		$red = (in_array($stock, array(__('Sold Out', 'usces'), __('Out Of Stock', 'usces'), __('Out of print', 'usces')))) ? 'class="signal_red"' : '';
		$pictids = $this->get_pictids($itemCode);
		$optstr =  '';
		if( is_array($options) && count($options) > 0 ){
			foreach($options as $key => $value){
				if( !empty($key) )
					$optstr .= esc_html($key) . ' : ' . esc_html($value) . "<br />\n"; 
			}
		}
			
?>
	<tr>
		<td><?php echo $i + 1; ?></td>
		<td><?php echo wp_get_attachment_image( $pictids[0], array(60, 60), true ); ?></td>
		<td class="aleft"><?php echo esc_html($cartItemName); ?><?php do_action('usces_admin_order_item_name', $order_id, $i); ?><br /><?php echo $optstr; ?></td>
		<td><input name="skuPrice[<?php echo $i; ?>][<?php echo $post_id; ?>][<?php echo esc_attr($sku); ?>]" class="text price" type="text" value="<?php echo esc_attr($skuPrice); ?>" /></td>
		<td><input name="quant[<?php echo $i; ?>][<?php echo $post_id; ?>][<?php echo esc_attr($sku); ?>]" class="text quantity" type="text" value="<?php echo esc_attr($cart_row['quantity']); ?>" /></td>
		<td id="sub_total[<?php echo $i; ?>]" class="aright">&nbsp;</td>
		<td <?php echo $red ?>><?php echo esc_html($stock); ?></td>
		<td>
		<?php foreach((array)$options as $key => $value){ ?>
		<input name="optName[<?php echo $i; ?>][<?php echo $post_id; ?>][<?php echo esc_attr($sku); ?>][<?php echo esc_attr($key); ?>]" type="hidden" value="<?php echo esc_attr($key); ?>" />
		<input name="itemOption[<?php echo $i; ?>][<?php echo $post_id; ?>][<?php echo esc_attr($sku); ?>][<?php echo esc_attr($key); ?>]" type="hidden" value="<?php echo esc_attr($value); ?>" />
		<?php } ?>
		<input name="advance[<?php echo $i; ?>][<?php echo $post_id; ?>][<?php echo esc_attr($sku); ?>]" type="hidden" value="<?php echo esc_attr($advance); ?>" />
		<input name="delButton[<?php echo $i; ?>][<?php echo $post_id; ?>][<?php echo esc_attr($sku); ?>]" class="delCartButton" type="submit" value="<?php _e('Delete', 'usces'); ?>" />
		<?php do_action('usces_admin_order_cart_button', $order_id, $i); ?>
		</td>
	</tr>
<?php 
	}
?>
	</tbody>
		<tfoot>
		<tr>
			<th colspan="5" class="aright"><?php _e('total items','usces'); ?></th>
			<th id="item_total" class="aright">&nbsp;</th>
			<th colspan="2">&nbsp;</th>
		</tr>
		<tr>
			<td colspan="5" class="aright"><?php _e('Used points','usces'); ?></td>
			<td class="aright" style="color:#FF0000"><input name="order[usedpoint]" id="order_usedpoint" class="text price red" type="text" value="<?php if( !empty($data['order_usedpoint']) ) {echo esc_attr($data['order_usedpoint']); } else { echo '0'; } ?>" /></td>
			<td><?php _e('granted points', 'usces'); ?></td>
			<td class="aright" style="color:#FF0000"><input name="order[getpoint]" id="order_getpoint" class="text price" type="text" value="<?php if( !empty($data['order_getpoint']) ) {echo esc_attr($data['order_getpoint']); } else { echo '0'; } ?>" /></td>
		</tr>
		<tr>
			<td colspan="5" class="aright"><?php _e('Campaign disnount', 'usces'); ?></td>
			<td class="aright" style="color:#FF0000"><input name="order[discount]" id="order_discount" class="text price" type="text" value="<?php if( !empty($data['order_discount']) ) { echo esc_attr($data['order_discount']); } else { echo '0'; } ?>" /></td>
			<td colspan="2"><?php _e('Discounted amount should be shown by -(Minus)', 'usces'); ?>&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5" class="aright"><?php _e('Shipping', 'usces'); ?></td>
			<td class="aright"><input name="order[shipping_charge]" id="order_shipping_charge" class="text price" type="text" value="<?php if( !empty($data['order_shipping_charge']) ) { echo esc_attr($data['order_shipping_charge']); } else { echo '0'; } ?>" /></td>
			<td colspan="2"><?php _e('It will be not caluculated automatically.', 'usces'); ?>&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5" class="aright"><?php _e('COD fee','usces'); ?></td>
			<td class="aright"><input name="order[cod_fee]" id="order_cod_fee" class="text price" type="text" value="<?php if( !empty($data['order_cod_fee']) ) { echo esc_attr($data['order_cod_fee']); } else { echo '0'; } ?>" /></td>
			<td colspan="2"><?php _e('It will be not caluculated automatically.', 'usces'); ?>&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5" class="aright"><?php _e('consumption tax', 'usces'); ?></td>
			<td class="aright"><input name="order[tax]" id="order_tax" type="text" class="text price" value="<?php if( !empty($data['order_tax']) ) { echo esc_attr($data['order_tax']); } else { echo '0'; } ?>" /></td>
			<td colspan="2"><?php _e('It will be not caluculated automatically.', 'usces'); ?>&nbsp;</td>
		</tr>
		<tr>
			<th colspan="5" class="aright"><?php _e('Total Amount','usces'); ?></th>
			<th id="total_full" class="aright">&nbsp;</th>
			<th colspan="2">&nbsp;</th>
		</tr>
		</tfoot>
</table>
</div>
<div class="ordernavi"><input name="upButton2" class="upButton" type="submit" value="<?php _e('change decision', 'usces'); ?>" /><?php _e("When you change amount, please click 'Edit' before you finish your process.", 'usces'); ?></div>

<input name="order_action" type="hidden" value="<?php echo $oa; ?>" />
<input name="order_id" id="order_id" type="hidden" value="<?php echo esc_attr($data['ID']); ?>" />
<input name="old_getpoint" type="hidden" value="<?php echo esc_attr($data['order_getpoint']); ?>" />
<input name="old_usedpoint" type="hidden" value="<?php echo esc_attr($data['order_usedpoint']); ?>" />
<input name="up_modified" id="up_modified" type="hidden" value="" />
<input name="modified" id="modified" type="hidden" value="<?php echo esc_attr($data['order_modified']); ?>" />




<div id="addItemDialog" title="<?php _e('Add item', 'usces'); ?>">
	<div id="order-response"></div>
	<fieldset>
	<div class="clearfix">
		<div class="dialogsearch">
		<p><?php _e("Enter the item code, then press 'Obtain'", 'usces'); ?></p>
		<label for="name"><?php _e('item code', 'usces'); ?></label>
		<input type="text" name="newitemcode" id="newitemcode" class="text" />
		<input name="getitem" type="button" value="<?php _e('Obtain', 'usces'); ?>" onclick="if( jQuery('#newitemcode').val() == '' ) return; orderItem.getitem();" />
		</div>
		<div id="newitemform">
		</div>
	</div>
	</fieldset>
</div>



<div id="mailSendDialog" title="">
	<div id="order-response"></div>
	<fieldset>
		<p><?php _e("Check the mail and click 'send'", 'usces'); ?></p>
		<label><?php _e('e-mail adress', 'usces'); ?></label><input type="text" name="sendmailaddress" id="sendmailaddress" class="text" /><br />
		<label><?php _e('Client name', 'usces'); ?></label><input type="text" name="sendmailname" id="sendmailname" class="text" /><br />
		<label><?php _e('subject', 'usces'); ?></label><input type="text" name="sendmailsubject" id="sendmailsubject" class="text" /><input name="sendmail" id="sendmail" type="button" value="<?php _e('send', 'usces'); ?>" /><br />
		<textarea name="sendmailmessage" id="sendmailmessage" cols="" rows=""></textarea>
		<input name="mailChecked" id="mailChecked" type="hidden" />
	</fieldset>
</div>

<div id="mailSendAlert" title="">
	<div id="order-response"></div>
	<fieldset>
	</fieldset>
</div>
<input name="usces_referer" type="hidden" id="usces_referer" value="<?php if(isset($_REQUEST['usces_referer'])) echo $_REQUEST['usces_referer']; ?>" />
</form>

</div><!--usces_admin-->
</div><!--wrap-->
<script type="text/javascript">
//	rowSelection("mainDataTable");
</script>
