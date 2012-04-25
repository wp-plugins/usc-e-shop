<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';

$management_status = get_option('usces_management_status');

$oa = 'editpost';

$ID = $_REQUEST['kpf_id'];
global $wpdb, $usces_settings;
$tableName = $wpdb->prefix . "usces_postform";
$query = $wpdb->prepare("SELECT * FROM $tableName WHERE ID = %d", $ID);
$data = $wpdb->get_row( $query, ARRAY_A );
$member = $this->get_member_info( $data['mem_id'] );
$customer_country = (!empty($usces_settings['country'][$member['customer_country']])) ? $usces_settings['country'][$member['customer_country']] : '';
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

	$(".num").bind("change", function(){ usces_check_num($(this)); });

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

	$('#pointConfirmMail').click(function() {
		orderItem.getmailmessage('pointConfirmMail');
		$("#sendmailaddress").val("<?php echo esc_attr($member['mem_email']); ?>");
		$("#sendmailname").val("<?php echo esc_attr($member['mem_name1']); ?><?php echo esc_attr($member['mem_name2']); ?>");
		$("#sendmailsubject").val("<?php echo esc_js($this->options['mail_data']['title']['othermail']); ?>");
		$('#mailSendDialog').dialog('option', 'title', "<?php _e('釣果投稿ポイント付与メール', 'usces'); ?>");
		$('#mailSendDialog').dialog('open');
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

	uscesMail = {
		sendmail : function() {
			if($("#sendmailaddress").val() == "") return;

			var address = encodeURIComponent($("#sendmailaddress").val());
			var message = encodeURIComponent($("#sendmailmessage").val());
			var name = encodeURIComponent($("#sendmailname").val());
			var subject = encodeURIComponent($("#sendmailsubject").val());

			var s = uscesMail.settings;
			s.data = "action=order_item_ajax&mode=sendmail&mailaddress=" + address + "&message=" + message + "&name=" + name + "&subject=" + subject;
			s.success = function(data, dataType){
				//if(data == 'success') {
				if(data.indexOf('success')) {
					$('#mailSendAlert').dialog('option', 'buttons', {
															'OK': function() {
																	$(this).dialog('close');
																	$('#mailSendDialog').dialog('close');
																}
															});
					$('#mailSendAlert fieldset').dialog('option', 'title', 'SUCCESS');
					$('#mailSendAlert fieldset').html('<p><?php _e('E-mail has been sent.', 'usces'); ?></p>');
					$('#mailSendAlert').dialog('open');
					
				//}else if(data == 'error'){
				}else if(data.indexOf('error')){
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

	$('form').submit(function() {
		var error = 0;

		if( "" == $("input[name='member\[email\]']").val() ) {
			error++;
			$("input[name='member\[email\]']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("input[name='member\[point\]']").val() ) ) {
			error++;
			$("input[name='member\[point\]']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}

		if( 0 < error ) {
			$("#aniboxStatus").removeClass("none");
			$("#aniboxStatus").addClass("error");
			$("#info_image").attr("src", "<?php echo USCES_PLUGIN_URL; ?>/images/list_message_error.gif");
			$("#info_massage").html("データに不備があります");
			$("#anibox").animate({ backgroundColor: "#FFE6E6" }, 2000);
			return false;
		} else {
			return true;
		}
	});
});
</script>
<div class="wrap">
<div class="usces_admin">
<form action="<?php echo USCES_ADMIN_URL.'?page=usces_kpflist&kpf_action='.$oa; ?>" method="post" name="editpost">

<h2>Welcart Management <?php _e('釣果投稿データ','usces'); ?></h2>
<p class="version_info">Version <?php echo USCES_VERSION; ?></p>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img id="info_image" src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>
<div class="mailVisiLink">
<a href="<?php if(isset($_REQUEST['usces_referer'])) echo $_REQUEST['usces_referer']; ?>"><?php _e('Back', 'usces'); ?></a>
</div>
<div class="ordernavi"><input name="upButton" class="upButton" type="submit" value="<?php _e('change decision', 'usces'); ?>" /><?php _e("When you change amount, please click 'Edit' before you finish your process.", 'usces'); ?></div>
<div class="info_head post_form_admin">
<div class="error_message"><?php echo $this->error_message; ?></div>
<table class="mem_wrap">
<tr>
<td class="label"><?php _e('ID', 'usces'); ?></td>
<td class="col1"><div class="rod large short"><?php echo esc_html($data['ID']); ?></div></td>
<td colspan="2" rowspan="6" class="mem_col2">
<table class="mem_info">
	<tr>
		<td class="label"><?php _e('membership number', 'usces'); ?></td>
		<td class="col2"><?php echo esc_attr($data['mem_id']); ?></td>
	</tr>
	<tr>
		<td class="label">e-mail</td>
		<td class="col2"><?php echo esc_attr($member['mem_email']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('name', 'usces'); ?></td>
		<td class="col2"><?php echo esc_attr($member['mem_name1']); ?><?php echo esc_attr($member['mem_name2']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('furigana', 'usces'); ?></td>
		<td class="col2"><?php echo esc_attr($member['mem_name3']); ?><?php echo esc_attr($member['mem_name4']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('ハンドルネーム', 'usces'); ?></td>
		<td class="col2"><?php echo esc_attr($data['kpf_handle']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('Zip/Postal Code', 'usces'); ?></td>
		<td class="col2"><?php echo esc_attr($member['mem_zip']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('Country', 'usces'); ?></td>
		<td class="col2"><?php echo esc_attr($customer_country); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('Province', 'usces'); ?></td>
		<td class="col2"><?php echo esc_attr($member['mem_pref']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('city', 'usces'); ?></td>
		<td class="col2"><?php echo esc_attr($member['mem_address1']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('numbers', 'usces'); ?></td>
		<td class="col2"><?php echo esc_attr($member['mem_address2']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('building name', 'usces'); ?></td>
		<td class="col2"><?php echo esc_attr($member['mem_address3']); ?></td>
	</tr>
</table>
</td>
<td colspan="2" rowspan="6" class="mem_col3">
<table class="mem_info">
	<tr>
		<td class="label"><?php _e('投稿日', 'usces'); ?></td>
		<td class="col1"><?php echo esc_html(sprintf(__('%2$s %3$s, %1$s', 'usces'),substr($data['kpf_date'],0,4),substr($data['kpf_date'],5,2),substr($data['kpf_date'],8,2))); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('釣行エリア', 'usces'); ?></td>
		<td class="col1"><?php echo esc_html($data['kpf_area']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('釣行場所', 'usces'); ?></td>
		<td class="col1"><?php echo esc_html($data['kpf_location']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('釣行日', 'usces'); ?></td>
		<td class="col1"><?php echo esc_html($data['kpf_fishingdate']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('天気', 'usces'); ?></td>
		<td class="col1"><?php echo esc_html($data['kpf_weather']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('気温', 'usces'); ?></td>
		<td class="col1"><?php echo esc_html($data['kpf_temperature']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('潮', 'usces'); ?></td>
		<td class="col1"><?php echo esc_html($data['kpf_tide']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('時間帯', 'usces'); ?></td>
		<td class="col1"><?php echo esc_html($data['kpf_timezone']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('釣り方', 'usces'); ?></td>
		<td class="col1"><?php echo esc_html($data['kpf_style']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('釣果', 'usces'); ?></td>
		<td class="col1"><?php echo my_change_br($data['kpf_fishing']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('使用タックル', 'usces'); ?></td>
		<td class="col1"><?php echo my_change_br($data['kpf_usetackle']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('釣行レポート', 'usces'); ?></td>
		<td class="col1"><?php echo my_change_br($data['kpf_comment']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('釣果画像１', 'usces'); ?></td>
		<td class="col1"><?php echo esc_html($data['kpf_image1']); ?></td>
	</tr>
	<tr>
		<td class="label"><?php _e('釣果画像２', 'usces'); ?></td>
		<td class="col1"><?php echo esc_html($data['kpf_image2']); ?></td>
	</tr>
</table>
</td>
</tr>
<tr>
	<td class="label"><?php _e('current point', 'usces'); ?></td>
	<td class="col1"><div class="rod large short"><?php echo esc_html($member['mem_point']); ?></div></td>
</tr>
<tr>
	<td class="label"><?php _e('Points', 'usces'); ?></td>
	<td class="col1"><input name="kpf_point" type="text" class="text right short num" value="<?php echo esc_html($data['kpf_point']); ?>" /></td>
</tr>
<tr>
	<td class="label"><?php _e('Processing', 'usces'); ?></td>
<?php
	$selected0 = ($data['kpf_status'] == '未対応') ? ' selected' : '';
	$selected1 = ($data['kpf_status'] == '対応済') ? ' selected' : '';
?>
	<td class="col1"><select name="kpf_status"><option value="未対応"<?php echo $selected0 ?>>未対応</option><option value="対応済"<?php echo $selected1 ?>>対応済</option></select></td>
</tr>
<tr>
	<td class="label"><?php _e('Notes', 'usces'); ?></td>
	<td class="col1"><input name="kpf_note" type="text" class="text long" value="<?php echo esc_html($data['kpf_note']); ?>" /></td>
</tr>
<tr>
	<td class="label"></td>
	<td class="col1"><a href="#" id="pointConfirmMail">釣果投稿ポイント付与メール</a></td>
</tr>
</table>
</div>
<input name="kpf_action" type="hidden" value="<?php echo $oa; ?>" />
<input name="kpf_id" id="kpf_id" type="hidden" value="<?php echo $data['ID']; ?>" />
<input name="kpf_point_before" id="kpf_point_before" type="hidden" value="<?php echo $data['kpf_point']; ?>" />
<input name="mem_id" id="mem_id" type="hidden" value="<?php echo $data['mem_id']; ?>" />
<input name="usces_referer" type="hidden" id="usces_referer" value="<?php if(isset($_REQUEST['usces_referer'])) echo $_REQUEST['usces_referer']; ?>" />

<div id="mailSendDialog" title="">
	<div id="order-response"></div>
	<fieldset>
		<p><?php _e("Check the mail and click 'send'", 'usces'); ?></p>
		<label><?php _e('e-mail adress', 'usces'); ?></label><input type="text" name="sendmailaddress" id="sendmailaddress" class="text" /><br />
		<label><?php _e('Client name', 'usces'); ?></label><input type="text" name="sendmailname" id="sendmailname" class="text" /><br />
		<label><?php _e('subject', 'usces'); ?></label><input type="text" name="sendmailsubject" id="sendmailsubject" class="text" /><input name="sendmail" id="sendmail" type="button" value="<?php _e('send', 'usces'); ?>" /><br />
		<textarea name="sendmailmessage" id="sendmailmessage"></textarea>
	</fieldset>
</div>

<div id="mailSendAlert" title="">
	<div id="order-response"></div>
	<fieldset>
	</fieldset>
</div>

</form>

</div><!--usces_admin-->
</div><!--wrap-->
