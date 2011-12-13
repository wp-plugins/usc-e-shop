<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';
if( !empty($this->options['member_page_data']) ){
	$member_page_datas = stripslashes_deep($this->options['member_page_data']);
}else{
	$member_page_datas = array();
}
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

	//20100818ysk start
	var $tabs = $('#uscestabs_member').tabs({
		cookie: {
			// store cookie for a day, without, it would be a session cookie
			expires: 1
		}
	});

	customField = {
		settings: {
			url: uscesL10n.requestFile,
			type: 'POST',
			cache: false
		},

		//** Custom Member **
		addMember: function() {
			if($("#newcsmbkey").val() == '' || $("#newcsmbname").val() == '') return;

			var key = $("#newcsmbkey").val();
			var name = $("#newcsmbname").val();
			var value = $("#newcsmbvalue").val();
			var means = $("#newcsmbmeans").val();
			var essential = ($("input#newcsmbessential").attr("checked")) ? '1' : '0';
			var position = $("#newcsmbposition").val();

			var s = customField.settings;
			s.data = "action=custom_field_ajax&field=member&add=1&newkey="+key+"&newname="+name+"&newvalue="+value+"&newmeans="+means+"&newessential="+essential+"&newposition="+position;
			s.success = function(data, dataType) {
				if(data.length > 1) $("table#csmb-list-table").removeAttr("style");
				$("tbody#csmb-list").html(data);
				$("#newcsmbkey").val("");
				$("#newcsmbname").val("");
				$("#newcsmbvalue").val("");
				$("#newcsmbmeans").attr({selectedIndex: 0});
				$("#newcsmbessential").attr({checked: false});
			};
			s.error = function(msg) {
				$("#ajax-response-csmb").html(msg);
			};
			$.ajax(s);
			return false;
		},

		updMember: function(key) {
			var name = $(':input[name="csmb['+key+'][name]"]').val();
			var value = $(':input[name="csmb['+key+'][value]"]').val();
			var means = $(':input[name="csmb['+key+'][means]"]').val();
			var essential = ($(':input[name="csmb['+key+'][essential]"]').attr("checked")) ? '1' : '0';
			var position = $(':input[name="csmb['+key+'][position]"]').val();

			var s = customField.settings;
			s.data = "action=custom_field_ajax&field=member&update=1&key="+key+"&name="+name+"&value="+value+"&means="+means+"&essential="+essential+"&position="+position;
			s.success = function(data, dataType) {
				$("tbody#csmb-list").html(data);
			};
			s.error = function(msg) {
				$("#ajax-response-csmb").html(msg);
			};
			$.ajax(s);
			return false;
		},

		delMember: function(key) {
			var s = customField.settings;
			s.data = "action=custom_field_ajax&field=member&delete=1&key="+key;
			s.success = function(data, dataType) {
				$("tbody#csmb-list").html(data);
				if(data.length < 1) $("table#csmb-list-table").attr("style", "display: none");
			};
			s.error = function(msg) {
				$("#ajax-response-csmb").html(msg);
			};
			$.ajax(s);
			return false;
		}
	};
	//20100818ysk end

});

function toggleVisibility(id) {
   var e = document.getElementById(id);
   if(e.style.display == 'block')
	  e.style.display = 'none';
   else
	  e.style.display = 'block';
};
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
<input name="usces_option_update" type="submit" class="button" value="<?php _e('change decision','usces'); ?>" />
<div id="poststuff" class="metabox-holder">
<!--20100818ysk start-->
<div class="uscestabs" id="uscestabs_member">
	<ul>
		<li><a href="#member_page_setting_1"><?php _e('Explanation in Member page','usces'); ?></a></li>
		<li><a href="#member_page_setting_2"><?php _e('Custom member field','usces'); ?></a></li>
	</ul>

<div id="member_page_setting_1">
<!--20100818ysk end-->


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
	    <td><textarea name="footer[login]" id="footer[login]" class="mail_footer"><?php echo $member_page_datas['footer']['login']; ?></textarea></td>
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
	    <td><textarea name="footer[newmember]" id="footer[newmember]" class="mail_footer"><?php echo $member_page_datas['footer']['newmember']; ?></textarea></td>
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
	    <td><textarea name="footer[newpass]" id="footer[newpass]" class="mail_footer"><?php echo $member_page_datas['footer']['newpass']; ?></textarea></td>
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
	    <td><textarea name="footer[changepass]" id="footer[changepass]" class="mail_footer"><?php echo $member_page_datas['footer']['changepass']; ?></textarea></td>
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
	    <td><textarea name="footer[memberinfo]" id="footer[memberinfo]" class="mail_footer"><?php echo $member_page_datas['footer']['memberinfo']; ?></textarea></td>
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
	    <td><textarea name="footer[completion]" id="footer[completion]" class="mail_footer"><?php echo $member_page_datas['footer']['completion']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_completion_page" class="explanation"><?php _e('You can set additional explanation to insert in a completion page.','usces'); ?></div>
</div>
</div><!--postbox-->
<!--20100818ysk start-->
</div><!--member_page_setting_1-->
<?php
	$csmb_meta = usces_has_custom_field_meta('member');
	$csmb_display = (empty($csmb_meta)) ? ' style="display: none;"' : '';
	$csmb_means = get_option('usces_custom_member_select');
	$csmb_meansoption = '';
	foreach($csmb_means as $meankey => $meanvalue) {
		$csmb_meansoption .= '<option value="'.esc_attr($meankey).'">'.esc_html($meanvalue)."</option>\n";
	}
	$positions = get_option('usces_custom_field_position_select');
	$positionsoption = '';
	foreach($positions as $poskey => $posvalue) {
		$positionsoption .= '<option value="'.esc_attr($poskey).'">'.esc_html($posvalue)."</option>\n";
	}
?>
<div id="member_page_setting_2">
	<div class="postbox">
	<h3 class="hndle"><span><?php _e('Custom member field', 'usces'); ?><a style="cursor:pointer;" onclick="toggleVisibility('ex_custom_member');"><?php _e('(Explain)','usces'); ?></a></span></h3>
	<div class="inside">
	<div id="postoptcustomstuff"><div id="ajax-response-csmb"></div>
	<table id="csmb-list-table" class="list"<?php echo $csmb_display; ?>>
		<thead>
		<tr>
		<th class="left"><?php _e('key name','usces') ?></th>
		<th rowspan="2"><?php _e('selected amount','usces') ?></th>
		</tr>
		<tr>
		<th class="left"><?php _e('field name','usces') ?></th>
		</tr>
		</thead>
		<tbody id="csmb-list">
<?php
	if(is_array($csmb_meta)) {
		foreach($csmb_meta as $key => $entry) 
			echo _list_custom_member_meta_row($key, $entry);
	}
?>
		</tbody>
	</table>

	<p><strong><?php _e('Add a new custom member field','usces') ?> : </strong></p>
	<table id="newmeta2">
		<thead>
		<tr>
		<th class="left"><?php _e('key name','usces') ?></th>
		<th rowspan="2"><?php _e('selected amount','usces') ?></th>
		</tr>
		<tr>
		<th class="left"><?php _e('field name','usces') ?></th>
		</tr>
		</thead>

		<tbody>
		<tr>
		<td class='item-opt-key'>
		<input type="text" name="newcsmbkey" id="newcsmbkey" class="optname" value="" />
		<input type="text" name="newcsmbname" id="newcsmbname" class="optname" value="" />
		<div class="optcheck"><select name='newcsmbmeans' id='newcsmbmeans'><?php echo $csmb_meansoption; ?></select>
		<input type="checkbox" name="newcsmbessential" id="newcsmbessential" /><label for='newcsmbessential'><?php _e('Required','usces') ?></label>
		<select name='newcsmbposition' id='newcsmbposition'><?php echo $positionsoption; ?></select></div>
		</td>
		<td class='item-opt-value'><textarea name="newcsmbvalue" id="newcsmbvalue" class='optvalue'></textarea></td>
		</tr>

		<tr><td colspan="2" class="submit">
		<input type="button" name="add_csmb" id="add_csmb" value="<?php _e('Add custom member field','usces') ?>" onclick="customField.addMember();" />
		</td></tr>
		</tbody>
	</table>

	<hr size="1" color="#CCCCCC" />
	<div id="ex_custom_member" class="explanation"><?php _e("You can add an arbitrary field to the member information page.", 'usces'); ?></div>
	</div>
	</div>
	</div><!--postbox-->
</div><!--member_page_setting_2-->
</div><!--tabs-->
<!--20100818ysk end-->


</div><!--poststuff-->
<input name="usces_option_update" type="submit" class="button" value="<?php _e('change decision','usces'); ?>" />
</form>
</div><!--usces_admin-->
</div><!--wrap-->