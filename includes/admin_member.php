<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';
$member_page_datas = $this->options['member_page_data'];
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
<h2>Welcart Shop <?php _e('Member Page Setting','usces'); ?></h2>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>
<form action="" method="post" name="option_form" id="option_form">
<input name="usces_option_update" type="submit" class="button" value="<?php _e('edit the setting','usces'); ?>" />
<div id="poststuff" class="metabox-holder">

<div class="postbox">
<h3 class="hndle"><span><?php _e('Explanation in a Login page','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_login_page');"><?php _e('(Explain)','usces'); ?></a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><?php _e('header','usces'); ?></th>
	    <td><textarea name="header[login]" id="header[login]" class="mail_header"><?php echo $member_page_datas['header']['login']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th><?php _e('footer','usces'); ?></th>
	    <td><textarea name="footer[login]" id="footer[login]" class="mail_footer"/><?php echo $member_page_datas['footer']['login']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_login_page" class="explanation"><?php _e('You can set additional explanation to insert in a login page.','usces'); ?></div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('Explanation in a New Member page','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_newmember_page');"><?php _e('(Explain)','usces'); ?></a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><?php _e('header','usces'); ?></th>
	    <td><textarea name="header[newmember]" id="header[newmember]" class="mail_header"><?php echo $member_page_datas['header']['newmember']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th><?php _e('footer','usces'); ?></th>
	    <td><textarea name="footer[newmember]" id="footer[newmember]" class="mail_footer"/><?php echo $member_page_datas['footer']['newmember']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_newmember_page" class="explanation"><?php _e('You can set additional explanation to insert in a new member page.','usces'); ?></div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('Explanation in New Password page','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_newpass_page');"><?php _e('(Explain)','usces'); ?></a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><?php _e('header','usces'); ?></th>
	    <td><textarea name="header[newpass]" id="header[newpass]" class="mail_header"><?php echo $member_page_datas['header']['newpass']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th><?php _e('footer','usces'); ?></th>
	    <td><textarea name="footer[newpass]" id="footer[newpass]" class="mail_footer"/><?php echo $member_page_datas['footer']['newpass']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_newpass_page" class="explanation"><?php _e('You can set additional explanation to insert in a new password page.','usces'); ?></div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('Explanation in a Change Password page','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_changepass_page');"><?php _e('(Explain)','usces'); ?></a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><?php _e('header','usces'); ?></th>
	    <td><textarea name="header[changepass]" id="header[changepass]" class="mail_header"><?php echo $member_page_datas['header']['changepass']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th><?php _e('footer','usces'); ?></th>
	    <td><textarea name="footer[changepass]" id="footer[changepass]" class="mail_footer"/><?php echo $member_page_datas['footer']['changepass']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_changepass_page" class="explanation"><?php _e('You can set additional explanation to insert in a change password page.','usces'); ?></div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('Explanation in a Member Information page','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_memberinfo_page');"><?php _e('(Explain)','usces'); ?></a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><?php _e('header','usces'); ?></th>
	    <td><textarea name="header[memberinfo]" id="header[memberinfo]" class="mail_header"><?php echo $member_page_datas['header']['memberinfo']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th><?php _e('footer','usces'); ?></th>
	    <td><textarea name="footer[memberinfo]" id="footer[memberinfo]" class="mail_footer"/><?php echo $member_page_datas['footer']['memberinfo']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_memberinfo_page" class="explanation"><?php _e('You can set additional explanation to insert in a member information page.','usces'); ?></div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('Explanation in a Completion page','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_completion_page');"><?php _e('(Explain)','usces'); ?></a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><?php _e('header','usces'); ?></th>
	    <td><textarea name="header[completion]" id="header[completion]" class="mail_header"><?php echo $member_page_datas['header']['completion']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th><?php _e('footer','usces'); ?></th>
	    <td><textarea name="footer[completion]" id="footer[completion]" class="mail_footer"/><?php echo $member_page_datas['footer']['completion']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_completion_page" class="explanation"><?php _e('You can set additional explanation to insert in a completion page.','usces'); ?></div>
</div>
</div><!--postbox-->


</div><!--poststuff-->
<input name="usces_option_update" type="submit" class="button" value="<?php _e('edit the setting','usces'); ?>" />
</form>
</div><!--usces_admin-->
</div><!--wrap-->