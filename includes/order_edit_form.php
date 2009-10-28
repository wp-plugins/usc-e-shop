<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';

$management_status = get_option('usces_management_status');

if($order_action == 'new'){

	$oa = 'newpost';
	$taio = 'new';
	$admin = 'adminorder';
	$receipt = '';
	$ordercheck = array();
	$order_delivery_method = -1;

}else{

	$oa = 'editpost';

	$ID = $_REQUEST['order_id'];
	
	global $wpdb;
	
	$tableName = $wpdb->prefix . "usces_order";
	$query = $wpdb->prepare("SELECT * FROM $tableName WHERE ID = %d", $ID);
	$data = $wpdb->get_row( $query, ARRAY_A );

	$deli = unserialize($data['order_delivery']);
	$cart = unserialize($data['order_cart']);
	$condition = unserialize($data['order_condition']);
	$ordercheck = unserialize($data['order_check']);
	if( !is_array($ordercheck) ) $ordercheck = array();
	$order_delivery_method = $data['order_delivery_method'];

	if($this->is_status('duringorder', $data['order_status']))
		$taio = 'duringorder';
	else if($this->is_status('cancel', $data['order_status']))
		$taio = 'cancel';
	else if($this->is_status('completion', $data['order_status']))
		$taio = 'completion';
	else
		$taio = 'new';
		
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
	else
		$receipt = '';
}

?>
<script type='text/javascript' src='<?php echo USCES_WP_PLUGIN_URL . '/usc-e-shop/js/jquery/jquery-1.3.2.min.js'; ?>'></script>
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
	var selected_delivery_time = '<?php echo $data['order_delivery_time']; ?>';
	var delivery_time = [];
<?php foreach((array)$this->options['delivery_method'] as $dmid => $dm){	$lines = split("\n", $dm['time']); ?>
	delivery_time[<?php echo $dm['id']; ?>] = [];
	<?php foreach((array)$lines as $line){ 	if(trim($line) != ''){ ?>
	delivery_time[<?php echo $dm['id']; ?>].push("<?php echo trim($line); ?>");
	<?php } } ?>
<?php } ?>

	$("#order_payment_name").change(function () {
		if( $("select[name='order\[payment_name\]'] option:selected").val() == '銀行振り込み'){
			var label = '入金状況';
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
			'閉じる': function() {
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
			alert("「更新確定」ボタンを押して注文Noを確定して下さい。");
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
			'閉じる': function() {
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
		$("#sendmailsubject").val('<?php echo $this->options['mail_data']['title']['completionmail']; ?>');
		$('#mailChecked').val('completionmail');
		$('#mailSendDialog').dialog('option', 'title', '発送完了メール');
		$('#mailSendDialog').dialog('open');
	});
	$('#orderConfirmMail').click(function() {
		orderItem.getmailmessage('orderConfirmMail');
		$("#sendmailaddress").val($("input[name='customer\[mailaddress\]']").val());
		$("#sendmailname").val($("input[name='customer\[name1\]']").val()+$("input[name='customer\[name2\]']").val());
		$("#sendmailsubject").val('<?php echo $this->options['mail_data']['title']['ordermail']; ?>');
		$('#mailChecked').val('ordermail');
		$('#mailSendDialog').dialog('option', 'title', '注文確認メール');
		$('#mailSendDialog').dialog('open');
	});
	$('#changeConfirmMail').click(function() {
		orderItem.getmailmessage('changeConfirmMail');
		$("#sendmailaddress").val($("input[name='customer\[mailaddress\]']").val());
		$("#sendmailname").val($("input[name='customer\[name1\]']").val()+$("input[name='customer\[name2\]']").val());
		$("#sendmailsubject").val('<?php echo $this->options['mail_data']['title']['changemail']; ?>');
		$('#mailChecked').val('changemail');
		$('#mailSendDialog').dialog('option', 'title', '変更確認メール');
		$('#mailSendDialog').dialog('open');
	});
	$('#receiptConfirmMail').click(function() {
		orderItem.getmailmessage('receiptConfirmMail');
		$("#sendmailaddress").val($("input[name='customer\[mailaddress\]']").val());
		$("#sendmailname").val($("input[name='customer\[name1\]']").val()+$("input[name='customer\[name2\]']").val());
		$("#sendmailsubject").val('<?php echo $this->options['mail_data']['title']['receiptmail']; ?>');
		$('#mailChecked').val('receiptmail');
		$('#mailSendDialog').dialog('option', 'title', '入金確認メール');
		$('#mailSendDialog').dialog('open');
	});
	$('#mitumoriConfirmMail').click(function() {
		orderItem.getmailmessage('mitumoriConfirmMail');
		$("#sendmailaddress").val($("input[name='customer\[mailaddress\]']").val());
		$("#sendmailname").val($("input[name='customer\[name1\]']").val()+$("input[name='customer\[name2\]']").val());
		$("#sendmailsubject").val('<?php echo $this->options['mail_data']['title']['mitumorimail']; ?>');
		$('#mailChecked').val('mitumorimail');
		$('#mailSendDialog').dialog('option', 'title', '見積りメール');
		$('#mailSendDialog').dialog('open');
	});
	$('#cancelConfirmMail').click(function() {
		orderItem.getmailmessage('cancelConfirmMail');
		$("#sendmailaddress").val($("input[name='customer\[mailaddress\]']").val());
		$("#sendmailname").val($("input[name='customer\[name1\]']").val()+$("input[name='customer\[name2\]']").val());
		$("#sendmailsubject").val('<?php echo $this->options['mail_data']['title']['cancelmail']; ?>');
		$('#mailChecked').val('cancelmail');
		$('#mailSendDialog').dialog('option', 'title', 'キャンセルメール');
		$('#mailSendDialog').dialog('open');
	});
	$('#otherConfirmMail').click(function() {
		orderItem.getmailmessage('otherConfirmMail');
		$("#sendmailaddress").val($("input[name='customer\[mailaddress\]']").val());
		$("#sendmailname").val($("input[name='customer\[name1\]']").val()+$("input[name='customer\[name2\]']").val());
		$("#sendmailsubject").val('<?php echo $this->options['mail_data']['title']['othermail']; ?>');
		$('#mailChecked').val('othermail');
		$('#mailSendDialog').dialog('option', 'title', 'その他のメール');
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
			if(selected == -1){
				option += '<option value="指定しない">指定しない</option>' + "\n";
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
		
			var address = $("#sendmailaddress").val();
			var message = $("#sendmailmessage").val();
			var name = $("#sendmailname").val();
			var subject = $("#sendmailsubject").val();
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
					$('#mailSendAlert fieldset').html('<p>メールの送信が完了しました。</p>');
					$('#mailSendAlert').dialog('open');
					
				}else if(data == 'error'){
					$('#mailSendAlert').dialog('option', 'buttons', {
															'OK': function() {
																	$(this).dialog('close');
																}
															});
					$('#mailSendAlert fieldset').dialog('option', 'title', 'ERROR');
					$('#mailSendAlert fieldset').html('<p>メールは送信できませんでした。</p>');
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
				$('#mailSendAlert fieldset').html('<p>メールは送信できませんでした。</p>');
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
	printWin = window.open("<?php echo USCES_ADMIN_URL.'?page=usces_orderlist&order_action=pdfout&order_id='.$ID; ?>"+"&type="+type,"sub","left="+x+",top="+y+",width="+wx+",height="+wy+",scrollbars=yes");
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
	
	function delConfirm(){
		if(confirm('商品を削除します。よろしいですか？')){
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

<h2>Welcart Management 受注データ編集<?php //echo __('USC e-Shop Options','usces'); ?></h2>
<p class="version_info">Version <?php echo USCES_VERSION; ?></p>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>
<div class="mailVisiLink">
<a style="cursor:pointer;" id="mailVisiLink" onclick="toggleVisibility('mailBox');">メール・印刷フィールド表示</a><br /><a href="<?php if(isset($_REQUEST['usces_referer'])) echo $_REQUEST['usces_referer']; ?>">戻る</a>
</div>
<div class="ordernavi"><input name="upButton" class="upButton" type="submit" value="<?php echo '更新確定'; ?>" />値を変更した場合は必ず最後に「更新確定」ボタンを押してください。</div>
<div id="mailBox">
<table>
<tr>
<td><input name="check[ordermail]" type="checkbox" value="ordermail"<?php if(isset($ordercheck['ordermail'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="orderConfirmMail">注文確認メール</a></td>
<td><input name="check[changemail]" type="checkbox" value="changemail"<?php if(isset($ordercheck['changemail'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="changeConfirmMail">変更確認メール</a></td>
<td><input name="check[receiptmail]" type="checkbox" value="receiptmail"<?php if(isset($ordercheck['receiptmail'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="receiptConfirmMail">入金確認メール</a></td>
<td><input name="check[mitumorimail]" type="checkbox" value="mitumorimail"<?php if(isset($ordercheck['mitumorimail'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="mitumoriConfirmMail">見積りメール</a></td>
<td><input name="check[cancelmail]" type="checkbox" value="cancelmail"<?php if(isset($ordercheck['cancelmail'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="cancelConfirmMail">キャンセルメール</a></td>
<td><input name="check[othermail]" type="checkbox" value="othermail"<?php if(isset($ordercheck['othermail'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="otherConfirmMail">その他のメール</a></td>
</tr>
<tr>
<td><input name="check[completionmail]" type="checkbox" value="completionmail"<?php if(isset($ordercheck['completionmail'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="completionMail">発送完了メール</a></td>
<td><input name="check[mitumoriprint]" type="checkbox" value="mitumoriprint"<?php if(isset($ordercheck['mitumoriprint'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="mitumoriprint">見積書印刷</a></td>
<td><input name="check[nohinprint]" type="checkbox" value="nohinprint"<?php if(isset($ordercheck['nohinprint'])) echo ' checked="checked"' ; ?> /></td><td><a href="#" id="nohinprint">納品書印刷</a></td>
<td colspan="7"><span style="color:#CC3300">※変更がある場合は「更新確定」してから送信・印刷を行ってください。</span></td>
</tr>
</table>
</div>
<div class="info_head">
<table>
<td colspan="6" class="midasi0">受注明細</td>
</tr>
<tr>
<td class="label border">注文No</td><td class="col1 border"><div class="rod large short"><?php echo $data['ID']; ?></div></td>
<td class="col3 label border">注文日時</td><td class="col2 border"><div class="rod long"><?php echo $data['order_date']; ?></div></td>
<td class="label border">発送日</td><td class="border"><div class="rod long"><?php echo $data['order_modified']; ?>&nbsp;</div></td>
</tr>
<tr>
<td class="label">会員No</td><td class="col1"><div class="rod large short"><?php echo $data['mem_id']; ?></div></td>
<td class="col3 label">e-mail</td><td class="col2"><input name="customer[mailaddress]" type="text" class="text long" value="<?php echo $data['order_email']; ?>" /></td>
<td colspan="2" class="midasi1">発送先</td>
</tr>
<tr>
<td class="label">支払方法</td>
<td class="col1"><select name="order[payment_name]" id="order_payment_name">
    <option value="#none#">-選択-</option>
<?php 
if( $this->options['payment_method'] ) {
	foreach ((array)$this->options['payment_method'] as $payments) {
	if( $payments['name'] != '' ) {
		$selected = ($payments['name'] == $data['order_payment_name']) ? ' selected="selected"' : '';
?>
    <option value="<?php echo $payments['name']; ?>"<?php echo $selected; ?>><?php echo $payments['name']; ?></option>
<?php } } } ?>
</select></td>
<td class="col3 label">氏名</td>
<td class="col2"><input name="customer[name1]" type="text" class="text short" value="<?php echo $data['order_name1']; ?>" /><input name="customer[name2]" type="text" class="text short" value="<?php echo $data['order_name2']; ?>" /></td>
<td class="label deli">氏名</td>
<td class="deli"><input name="delivery[name1]" type="text" class="text short" value="<?php echo $deli['name1']; ?>" /><input name="delivery[name2]" type="text" class="text short" value="<?php echo $deli['name2']; ?>" /></td>
</tr>
<tr>
<td class="label">配送方法</td>
<td class="col1"><select name="order[delivery_method]" id="delivery_method_select">
	<option value="-1">指定しない</option>
<?php
foreach ((array)$this->options['delivery_method'] as $dkey => $delivery) {
	$selected = $order_delivery_method == $delivery['id'] ? ' selected="selected"' : '';
	echo "\t<option value='{$delivery['id']}'{$selected}>{$delivery['name']}</option>\n";
}
?>
</select></td>
<td class="col3 label">フリガナ</td>
<td class="col2"><input name="customer[name3]" type="text" class="text short" value="<?php echo $data['order_name3']; ?>" /><input name="customer[name4]" type="text" class="text short" value="<?php echo $data['order_name4']; ?>" /></td>
<td class="label deli">フリガナ</td>
<td class="deli"><input name="delivery[name3]" type="text" class="text short" value="<?php echo $deli['name3']; ?>" /><input name="delivery[name4]" type="text" class="text short" value="<?php echo $deli['name4']; ?>" /></td>
</tr>
<tr>
<td class="label">配送時間帯</td>
<td class="col1"><select name="order[delivery_time]" id="delivery_time_select">
	<option value='指定しない'>指定しない</option>
<?php
if( !$this->options['delivery_time'] == '' ) {
	$array = explode("\n", $this->options['delivery_time']);
	foreach ((array)$array as $delivery) {
		$delivery = trim($delivery);
		if( $delivery != '' ) {
			$selected = ($delivery == $value) ? ' selected="selected"' : '';
			echo "\t<option value='{$delivery}'{$selected}>{$delivery}</option>\n";
		}
	}
}
?>
</select></td>
<td class="col3 label">郵便番号</td>
<td class="col2"><input name="customer[zipcode]" type="text" class="text short" value="<?php echo $data['order_zip']; ?>" /></td>
<td class="label deli">郵便番号</td>
<td class="deli"><input name="delivery[zipcode]" type="text" class="text short" value="<?php echo $deli['zipcode']; ?>" /></td>
</tr>
<tr>
<td class="label">発送予定日</td>
<td class="col1"><select name="order[delidue_date]">
    <option value="#none#">通知しない</option>
<?php
for ($i=0; $i<50; $i++) {
	$date = date('Y	年m月d日', mktime(0,0,0,date('m'),date('d')+$i,date('Y')));
	$selected = ($data['delidue_date'] == $date) ? ' selected="selected"' : '';
	echo "\t<option value='{$date}'{$selected}>{$date}</option>\n";
}
?>
</select></td>
<td class="col3 label">都道府県</td>
<td class="col2"><select name="customer[pref]" class="select">
<?php
//	$prefs = get_option('usces_pref');
	$prefs = $this->options['province'];
foreach((array)$prefs as $value) {
	$selected = ($data['order_pref'] == $value) ? ' selected="selected"' : '';
	echo "\t<option value='{$value}'{$selected}>{$value}</option>\n";
}
?>
</select>
</td>
<td class="label deli">都道府県</td>
<td class="deli"><select name="delivery[pref]">
<?php
//	$prefs = get_option('usces_pref');
	$prefs = $this->options['province'];
foreach((array)$prefs as $value) {
	$selected = ($deli['pref'] == $value) ? ' selected="selected"' : '';
	echo "\t<option value='{$value}'{$selected}>{$value}</option>\n";
}
?>
</select>
</td>
</tr>
<tr>
<td colspan="2" class="midasi3">ステイタス</td>
<td class="col3 label">市区郡町村</td>
<td class="col2"><input name="customer[address1]" type="text" class="text long" value="<?php echo $data['order_address1']; ?>" /></td>
<td class="label deli">市区郡町村</td>
<td class="deli"><input name="delivery[address1]" type="text" class="text long" value="<?php echo $deli['address1']; ?>" /></td>
</tr>
<tr>
<td class="label status">対応状況</td>
<td class="col1 status">
<select name="order[taio]">
	<option value='#none#'>新規受付</option>";
	<option value='duringorder'<?php if($taio == 'duringorder'){ echo 'selected="selected"';} ?>><?php echo $management_status['duringorder']; ?></option>";
	<option value='cancel'<?php if($taio == 'cancel'){ echo 'selected="selected"';} ?>><?php echo $management_status['cancel']; ?></option>";
	<option value='completion'<?php if($taio == 'completion'){ echo 'selected="selected"';} ?>><?php echo $management_status['completion']; ?></option>";
</select>
</td>
<td class="col3 label">番地</td>
<td class="col2"><input name="customer[address2]" type="text" class="text long" value="<?php echo $data['order_address2']; ?>" /></td>
<td class="label deli">番地</td>
<td class="deli"><input name="delivery[address2]" type="text" class="text long" value="<?php echo $deli['address2']; ?>" /></td>
</tr>
<tr>
<td class="label status" id="receiptlabel"><?php if($receipt != ''){echo '入金状況';}else{echo '&nbsp';} ?></td>
<td class="col1 status" id="receiptbox">
<?php if($receipt != '') : ?>
<select name="order[receipt]">
	<option value='noreceipt'<?php if($receipt == 'noreceipt'){ echo ' selected="selected"';} ?>><?php echo $management_status['noreceipt']; ?></option>
	<option value='receipted'<?php if($receipt == 'receipted'){ echo ' selected="selected"';} ?>><?php echo $management_status['receipted']; ?></option>
</select>
<?php else : ?>
&nbsp
<?php endif; ?></td>
<td class="col3 label">ビル名</td>
<td class="col2"><input name="customer[address3]" type="text" class="text long" value="<?php echo $data['order_address3']; ?>" /></td>
<td class="label deli">ビル名</td>
<td class="deli"><input name="delivery[address3]" type="text" class="text long" value="<?php echo $deli['address3']; ?>" /></td>
</tr>
<tr>
<td class="label status"><?php if($admin != ''){echo '見積り受注';}else{echo '&nbsp';} ?></td>
<td class="col1 status">
<?php if($admin != '') : ?>
<select name="order[admin]">
	<option value='adminorder'<?php if($admin == 'adminorder'){ echo 'selected="selected"';} ?>><?php echo $management_status['adminorder']; ?></option>
	<option value='estimate'<?php if($admin == 'estimate'){ echo 'selected="selected"';} ?>><?php echo $management_status['estimate']; ?></option>
</select>
<?php else : ?>
&nbsp
<?php endif; ?></td>
<td class="col3 label">電話番号</td>
<td class="col2"><input name="customer[tel]" type="text" class="text long" value="<?php echo $data['order_tel']; ?>" /></td>
<td class="label deli">電話番号</td>
<td class="deli"><input name="delivery[tel]" type="text" class="text long" value="<?php echo $deli['tel']; ?>" /></td>
</tr>
<tr>
<td colspan="2" rowspan="2" class="status">
<div class="midasi2"><?php if($condition['display_mode'] == 'Usualsale'){echo '通常セール';}elseif($condition['display_mode'] == 'Promotionsale'){echo 'キャンペーンセール';} ?></div>
<div class="condition">
<?php if ( $condition['display_mode'] == 'Promotionsale' ) : ?>
<span>特典：</span><?php echo $condition["campaign_privilege"]; ?>（<?php if($condition["campaign_privilege"] == 'discount'){echo $condition["privilege_discount"].'%引き';}elseif($condition["campaign_privilege"] == 'point'){echo $condition["privilege_point"].'倍、会員のみ';} ?>）<br />
<span>対象：</span><?php if($condition["campaign_category"] == 0){echo 全商品;} else {echo get_cat_name($condition["campaign_category"]);} ?><br />
<?php endif; ?>
</div></td>
<td class="col3 label">FAX番号</td>
<td class="col2"><input name="customer[fax]" type="text" class="text long" value="<?php echo $data['order_fax']; ?>" /></td>
<td class="label deli border">FAX番号</td>
<td class="deli border"><input name="delivery[fax]" type="text" class="text long" value="<?php echo $deli['fax']; ?>" /></td>
</tr>
<tr>
<td class="col3 label">備考</td>
<td colspan="3" class="col2"><textarea name="order[note]"><?php echo $data['order_note']; ?></textarea></td>
</tr>
</table>
</div>
<div id="cart">
<table cellspacing="0" id="cart_table">
	<thead>
		<tr>
			<th colspan="5" class="aright">総合計金額<?php //echo __('Total price','usces'); ?></th>
			<th id="total_full_top" class="aright">&nbsp;</th>
			<th colspan="2">&nbsp;</th>
		</tr>
	<tr>
		<th scope="row" class="num"><?php echo __('No.','usces'); ?></th>
		<th class="thumbnail"> <?php //echo __('thumbnail','usces'); ?></th>
		<th>商品<?php //echo __('item','usces'); ?></th>
		<th class="price">単価<?php //echo __('price','usces'); ?></th>
		<th class="quantity">数量<?php //echo __('quantity','usces'); ?></th>
		<th class="subtotal">金額<?php //echo __('subtotal','usces'); ?></th>
		<th class="stock">現在庫<?php //echo __('stock','usces'); ?></th>
		<th class="action"><input name="addButton" id="addCartButton" class="addCartButton" type="button" value="商品追加" /></th>
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
		$itemCode = $this->getItemCode($post_id);
		$itemName = $this->getItemName($post_id);
		$skuPrice = $cart_row['price'];
		$stock = $this->getItemZaiko($post_id, $sku);
		$red = (in_array($stock, array('売切れ','入荷待ち','廃盤'))) ? 'class="signal_red"' : '';
		$pictids = $this->get_pictids($itemCode);
		if (!empty($options) && is_array($options)) {
			$optstr = implode(',', $options);
		} else { 
			$optstr =  '';
			$options =  array();
		}
			
?>
	<tr>
		<td><?php echo $i + 1; ?></td>
		<td><?php echo wp_get_attachment_image( $pictids[0], array(60, 60), true ); ?></td>
		<td class="aleft"><?php echo $itemName; ?>&nbsp;<?php echo $itemCode; ?>&nbsp;<?php echo $sku; ?><br /><?php echo $optstr; ?></td>
		<td><input name="skuPrice[<?php echo $i; ?>][<?php echo $post_id; ?>][<?php echo $sku; ?>]" class="text price" type="text" value="<?php echo $skuPrice; ?>" /></td>
		<td><input name="quant[<?php echo $i; ?>][<?php echo $post_id; ?>][<?php echo $sku; ?>]" class="text quantity" type="text" value="<?php echo $cart_row['quantity']; ?>" /></td>
		<td id="sub_total[<?php echo $i; ?>]" class="aright">&nbsp;</td>
		<td <?php echo $red ?>><?php echo $stock; ?></td>
		<td>
		<?php foreach((array)$options as $key => $value){ ?>
		<input name="optName[<?php echo $i; ?>][<?php echo $post_id; ?>][<?php echo $sku; ?>][<?php echo $key; ?>]" type="hidden" value="<?php echo $key; ?>" />
		<input name="itemOption[<?php echo $i; ?>][<?php echo $post_id; ?>][<?php echo $sku; ?>][<?php echo $key; ?>]" type="hidden" value="<?php echo $value; ?>" />
		<?php } ?>
		<input name="delButton[<?php echo $i; ?>][<?php echo $post_id; ?>][<?php echo $sku; ?>]" class="delCartButton" type="submit" value="削除" />
		</td>
	</tr>
<?php 
	}
?>
	</tbody>
		<tfoot>
		<tr>
			<th colspan="5" class="aright">商品合計<?php //echo __('Item total price','usces'); ?></th>
			<th id="item_total" class="aright">&nbsp;</th>
			<th colspan="2">&nbsp;</th>
		</tr>
		<tr>
			<td colspan="5" class="aright">使用ポイント<?php //echo __('Use point','usces'); ?></td>
			<td class="aright" style="color:#FF0000"><input name="order[usedpoint]" id="order_usedpoint" class="text price red" type="text" value="<?php if( !empty($data['order_usedpoint']) ) {echo $data['order_usedpoint']; } else { echo '0'; } ?>" /></td>
			<td>付与ポイント</td>
			<td class="aright" style="color:#FF0000"><input name="order[getpoint]" id="order_getpoint" class="text price" type="text" value="<?php if( !empty($data['order_getpoint']) ) {echo $data['order_getpoint']; } else { echo '0'; } ?>" /></td>
		</tr>
		<tr>
			<td colspan="5" class="aright">キャンペーン割引<?php //echo __('Descount','usces'); ?></td>
			<td class="aright" style="color:#FF0000"><input name="order[discount]" id="order_discount" class="text price" type="text" value="<?php if( !empty($data['order_discount']) ) { echo $data['order_discount']; } else { echo '0'; } ?>" /></td>
			<td colspan="2">※割引は-（マイナス）で入力します。&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5" class="aright">送料<?php //echo __('Delivery fee','usces'); ?></td>
			<td class="aright"><input name="order[shipping_charge]" id="order_shipping_charge" class="text price" type="text" value="<?php if( !empty($data['order_shipping_charge']) ) { echo $data['order_shipping_charge']; } else { echo '0'; } ?>" /></td>
			<td colspan="2">※自動計算されません。&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5" class="aright">代引手数料<?php //echo __('COD Fee','usces'); ?></td>
			<td class="aright"><input name="order[cod_fee]" id="order_cod_fee" class="text price" type="text" value="<?php if( !empty($data['order_cod_fee']) ) { echo $data['order_cod_fee']; } else { echo '0'; } ?>" /></td>
			<td colspan="2">※自動計算されません。&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5" class="aright">消費税<?php //echo __('Tax','usces'); ?></td>
			<td class="aright"><input name="order[tax]" id="order_tax" type="text" class="text price" value="<?php if( !empty($data['order_tax']) ) { echo $data['order_tax']; } else { echo '0'; } ?>" /></td>
			<td colspan="2">※自動計算されません。&nbsp;</td>
		</tr>
		<tr>
			<th colspan="5" class="aright">総合計金額<?php //echo __('Total price','usces'); ?></th>
			<th id="total_full" class="aright">&nbsp;</th>
			<th colspan="2">&nbsp;</th>
		</tr>
		</tfoot>
</table>
</div>
<div class="ordernavi"><input name="upButton2" class="upButton" type="submit" value="<?php echo '更新確定'; ?>" />値を変更した場合は必ず最後に「更新確定」ボタンを押してください。</div>

<input name="order_action" type="hidden" value="<?php echo $oa; ?>" />
<input name="order_id" id="order_id" type="hidden" value="<?php echo $data['ID']; ?>" />
<input name="old_getpoint" type="hidden" value="<?php echo $data['order_getpoint']; ?>" />
<input name="old_usedpoint" type="hidden" value="<?php echo $data['order_usedpoint']; ?>" />




<div id="addItemDialog" title="商品追加">
	<div id="order-response"></div>
	<fieldset>
	<div class="clearfix">
		<div class="dialogsearch">
		<p>商品コードを入力して「取得」ボタンを押して下さい。</p>
		<label for="name">商品コード</label>
		<input type="text" name="newitemcode" id="newitemcode" class="text" />
		<input name="getitem" type="button" value="取得" onclick="if( jQuery('#newitemcode').val() == '' ) return; orderItem.getitem();" />
		</div>
		<div id="newitemform">
		</div>
	</div>
	</fieldset>
</div>



<div id="mailSendDialog" title="">
	<div id="order-response"></div>
	<fieldset>
		<p>メールの内容を確認して「送信」ボタンを押して下さい。</p>
		<label>メールアドレス</label><input type="text" name="sendmailaddress" id="sendmailaddress" class="text" /><br />
		<label>お客様名</label><input type="text" name="sendmailname" id="sendmailname" class="text" /><br />
		<label>件名</label><input type="text" name="sendmailsubject" id="sendmailsubject" class="text" /><input name="sendmail" id="sendmail" type="button" value="送信" /><br />
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
