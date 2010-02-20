<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';

$divide_item = $this->options['divide_item'];
$itemimg_anchor_rel = $this->options['itemimg_anchor_rel'];
$fukugo_category_orderby = $this->options['fukugo_category_orderby'];
$fukugo_category_order = $this->options['fukugo_category_order'];
$usces_pref = empty($this->options['province']) ? array() : $this->options['province'];
$settlement_path = $this->options['settlement_path'];
$province = '';
for($i=1; $i<count($usces_pref); $i++){
	$province .= $usces_pref[$i] . "\n";
}
$province = trim($province);
$use_ssl = $this->options['use_ssl'];
$ssl_url = $this->options['ssl_url'];
$ssl_url_admin = $this->options['ssl_url_admin'];

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
<h2>Welcart Shop <?php _e('System Setting','usces'); ?></h2>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>
<form action="" method="post" name="option_form" id="option_form">
<input name="usces_option_update" type="submit" class="button" value="<?php _e('change decision','usces'); ?>" />
<div id="poststuff" class="metabox-holder">

<div class="postbox">
<h3 class="hndle"><span><?php _e('System Setting','usces'); ?></span></h3>
<div class="inside">
<table class="form_table">
	<tr height="50">
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_province');"><?php _e('Province', 'usces'); ?></a></th>
		<td width="150"><textarea name="province" cols="30" rows="10"><?php echo $province; ?></textarea></td>
	    <td><div id="ex_province" class="explanation"><?php _e('The district where sale is possible', 'usces'); ?>(<?php _e('Province', 'usces'); ?>) <?php _e('One line one by one.', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr height="50">
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_divide_item');"><?php _e('Display Modes','usces'); ?></a></th>
		<?php $checked = $divide_item == 1 ? ' checked="checked"' : ''; ?>
		<td width="10"><input name="divide_item" type="checkbox" id="divide_item" value="<?php echo $divide_item; ?>"<?php echo $checked; ?> /></td>
		<td width="300"><label for="divide_item"><?php _e('Not display an article in blog', 'usces'); ?></label></td>
	    <td><div id="ex_divide_item" class="explanation"><?php _e('In the case of the loop indication that plural contributions are displayed in a shop, you can be decided display or non-display the item.', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr height="50">
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_itemimg_anchor_rel');"><?php _e('rel attribute', 'usces'); ?></a></th>
		<td width="30">rel="</td>
		<td width="100"><input name="itemimg_anchor_rel" id="itemimg_anchor_rel" type="text" value="<?php echo $itemimg_anchor_rel; ?>" /></td>
		<td width="10">"</td>
	    <td><div id="ex_itemimg_anchor_rel" class="explanation"><?php _e('In item details page, you can appoint a rel attribute for anchor tag to display an image, sach as Lightbox plugin.', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr height="50">
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_fcat_orderby');"><?php _e('compound category sort item', 'usces'); ?></a></th>
		<td width="10"><select name="fukugo_category_orderby" id="fukugo_category_orderby">
		    <option value="ID"<?php if($fukugo_category_orderby == 'ID') echo ' selected="selected"'; ?>><?php _e('category ID', 'usces'); ?></option>
		    <option value="name"<?php if($fukugo_category_orderby == 'name') echo ' selected="selected"'; ?>><?php _e('category name', 'usces'); ?></option>
		</select></td>
	    <td><div id="ex_fcat_orderby" class="explanation"><?php _e('In a category to display in a compound category search page, you can choose an object to sort.', 'usces'); ?></div></td>
	</tr>
	<tr height="50">
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_fcat_order');"><?php _e('compound category sort order', 'usces'); ?></a></th>
		<td width="10"><select name="fukugo_category_order" id="fukugo_category_order">
		    <option value="ASC"<?php if($fukugo_category_order == 'ASC') echo ' selected="selected"'; ?>><?php _e('Ascending', 'usces'); ?></option>
		    <option value="DESC"<?php if($fukugo_category_order == 'DESC') echo ' selected="selected"'; ?>><?php _e('Descendin', 'usces'); ?></option>
		</select></td>
	    <td><div id="ex_fcat_order" class="explanation"><?php _e('In a category to display in a compound category search page, you can choose sort order.', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr height="50">
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_settlement_path');"><?php _e('settlement module path', 'usces'); ?></a></th>
		<td><input name="settlement_path" type="text" id="settlement_path" value="<?php echo $settlement_path; ?>" size="60" /></td>
	    <td><div id="ex_settlement_path" class="explanation"><?php _e('This is Field appointing the setting path of the settlement module. The initial value is a place same as a sample, but it is deleted at the time of automatic upgrading. Therefore you must arrange a module outside a plugin folder.', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr height="50">
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_use_ssl');"><?php _e('Use SSL','usces'); ?></a></th>
		<?php $checked = $use_ssl == 1 ? ' checked="checked"' : ''; ?>
		<td width="10"><input name="use_ssl" type="checkbox" id="use_ssl" value="<?php echo $use_ssl; ?>"<?php echo $checked; ?> /></td>
		<td width="300"><label for="use_ssl"><?php _e('Not display an article in blog', 'usces'); ?></label></td>
	    <td><div id="ex_use_ssl" class="explanation"><?php _e('In the case of the loop indication that plural contributions are displayed in a shop, you can be decided display or non-display the item.', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr height="50">
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_ssl_url_admin');"><?php _e('SSL URL', 'usces'); ?></a></th>
		<td><input name="ssl_url_admin" type="text" id="ssl_url_admin" value="<?php echo $ssl_url_admin; ?>" size="60" /></td>
	    <td><div id="ex_ssl_url_admin" class="explanation"><?php _e('This is Field appointing the setting path of the settlement module. The initial value is a place same as a sample, but it is deleted at the time of automatic upgrading. Therefore you must arrange a module outside a plugin folder.', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr height="50">
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_ssl_url');"><?php _e('SSL URL', 'usces'); ?></a></th>
		<td><input name="ssl_url" type="text" id="ssl_url" value="<?php echo $ssl_url; ?>" size="60" /></td>
	    <td><div id="ex_ssl_url" class="explanation"><?php _e('This is Field appointing the setting path of the settlement module. The initial value is a place same as a sample, but it is deleted at the time of automatic upgrading. Therefore you must arrange a module outside a plugin folder.', 'usces'); ?></div></td>
	</tr>
</table>
</div>
</div><!--postbox-->


</div><!--poststuff-->



<input name="usces_option_update" type="submit" class="button" value="<?php _e('change decision','usces'); ?>" />
<input type="hidden" id="post_ID" name="post_ID" value="<?php echo USCES_CART_NUMBER ?>" />
</form>
</div><!--usces_admin-->
</div><!--wrap-->