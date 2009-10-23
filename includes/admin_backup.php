<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';
$mail_datas = $this->options['mail_data'];

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
});

function toggleVisibility(id) {
   var e = document.getElementById(id);
   if(e.style.display == 'block')
	  e.style.display = 'none';
   else
	  e.style.display = 'block';
}
</script>
<div class="wrap">
<div class="usces_admin">
<h2>Welcart Shop バックアップ<?php //echo __('USC e-Shop Options','usces'); ?></h2>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>

<table class="form_table">
	<tr>
		<form action="" method="post" name="option_form" id="option_form">
	    <th width="150">エクスポート</th>
	    <td>
		<input name="usces_export" type="submit" class="button" value="エクスポート開始" />
		</td>
		<td>&nbsp;</td>
		</form>
	</tr>
	<tr>
		<form action="" method="post" enctype="multipart/form-data" name="up_form" id="up_form">
	    <th width="150">インポート</th>
	    <td><input name="data" type="file" /></td>
		<td><input name="usces_import" type="submit" class="button" value="インポート開始" /></td>
		</form>
	</tr>
</table>

<div class="chui">
バックアップ機能は現在テスト中です。
</div>
</div><!--usces_admin-->
</div><!--wrap-->