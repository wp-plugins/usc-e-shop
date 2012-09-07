<style type="text/css">
#p_bar {
	width: 0;
	padding: 0;
	margin: 0;
	border-spacing: 0;
	background-color: #0099CC;
}
#i_p_bar {
	color: #FFFFFF;
	text-align: right;
	font-weight: bold;
	height: 40px;
	font-size: 16px;
	padding-right: 10px;
}
#out_bar {
	width: 600px;
	border: 1px solid #CC9900;
	border-spacing: 0;
	background-color: #FFFFE8;
	margin: 0;
	padding-top: 0;
	padding-right: 0;
	padding-bottom: 10;
	padding-left: 0;
}
.under_p_bar {
	color: #565656;
	font-size: 16px;
	letter-spacing: 1px;
}
</style>
<?php
$status = isset($_REQUEST['usces_status']) ? $_REQUEST['usces_status'] : $DT->get_action_status();
$message = isset($_REQUEST['usces_message']) ? urldecode($_REQUEST['usces_message']) : $DT->get_action_message();
$curent_url = urlencode(USCES_ADMIN_URL . '?' . $_SERVER['QUERY_STRING']);
?>
<div class="wrap">
<div class="usces_admin">
<h2>Welcart Shop <?php _e('Item list','usces'); ?></h2>
<p class="version_info">Version <?php echo USCES_VERSION; ?></p>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>
<script type="text/javascript">
function changeMsg(msg) {
	jQuery("#msg").html(msg);
};
function setProgress(i ,all) {
	jQuery("#p_bar").css("width", ((i/all)*600)+'px');
	jQuery("#i_p_bar").html(Math.round(100*(i/all))+"%");
	jQuery("#rest").html(i + "/" + all + "件を処理完了");
};
</script>


<table id="out_bar" width="600">
<tbody><tr><td>

<table id="p_bar">
<tbody><tr><td id="i_p_bar">&nbsp;</td></tr>
</tbody></table>

</td></tr>
</tbody></table>
<span id="msg" class="under_p_bar">準備中 ...</span>
<span id="rest" class="under_p_bar"></span>


<div id="reg_work">
<?php usces_item_uploadcsv(); ?>
</div>
</div><!--usces_admin-->
</div><!--wrap-->
[memory peak usage] <?php echo round(memory_get_peak_usage()/1048576, 1); ?>Mb