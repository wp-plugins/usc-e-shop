<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';
$cart_page_datas = $this->options['cart_page_data'];
$indi_item_name = $this->options['indi_item_name'];
$pos_item_name = $this->options['pos_item_name'];
foreach($indi_item_name as $key => $value){
	$checked_item_name[$key] = $indi_item_name[$key] == 1 ? ' checked="checked"' : ''; 
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
<h2>Welcart Shop <?php _e('Cart Page Setting','usces'); ?></h2>
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
<h3 class="hndle"><span><?php _e('Rule of the column for a item name','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_item_indication');"><?php _e('(Explain)','usces'); ?></a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><?php _e('Indication of item name','usces'); ?></th>
	    <td><input name="indication[item_name]" type="checkbox" id="indi_item_name" value="<?php echo $indi_item_name['item_name']; ?>"<?php echo $checked_item_name['item_name']; ?> /></td>
	    <th><?php _e('Position of item name','usces'); ?></th>
		<td><input name="position[item_name]" type="text" id="pos_item_name" value="<?php echo $pos_item_name['item_name']; ?>" />(<?php _e('numeric','usces'); ?>)</td>
	</tr>
	<tr>
	    <th><?php _e('Indication of item code','usces'); ?></th>
	    <td><input name="indication[item_code]" type="checkbox" id="indi_item_code" value="<?php echo $indi_item_name['item_code']; ?>"<?php echo $checked_item_name['item_code']; ?> /></td>
	    <th><?php _e('Position of item code','usces'); ?></th>
		<td><input name="position[item_code]" type="text" id="pos_item_code" value="<?php echo $pos_item_name['item_code']; ?>" />(<?php _e('numeric','usces'); ?>)</td>
	</tr>
	<tr>
	    <th><?php _e('Indication of SKU name','usces'); ?></th>
	    <td><input name="indication[sku_name]" type="checkbox" id="indi_sku_name" value="<?php echo $indi_item_name['sku_name']; ?>"<?php echo $checked_item_name['sku_name']; ?> /></td>
	    <th><?php _e('Position of SKU name','usces'); ?></th>
		<td><input name="position[sku_name]" type="text" id="pos_sku_name" value="<?php echo $pos_item_name['sku_name']; ?>" />(<?php _e('numeric','usces'); ?>)</td>
	</tr>
	<tr>
	    <th><?php _e('Indication of SKU code','usces'); ?></th>
	    <td><input name="indication[sku_code]" type="checkbox" id="indi_sku_code" value="<?php echo $indi_item_name['sku_code']; ?>"<?php echo $checked_item_name['sku_code']; ?> /></td>
	    <th><?php _e('Position of SKU code','usces'); ?></th>
		<td><input name="position[sku_code]" type="text" id="pos_sku_code" value="<?php echo $pos_item_name['sku_code']; ?>" />(<?php _e('numeric','usces'); ?>)</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_item_indication" class="explanation"><?php _e('You can appoint indication, non-indication, sort of the item name to show the cart.<br />This rule is applied as brand names such as a cart page, contents confirmation page, a member information purchase history, an email, a written estimate, the statement of delivery.','usces'); ?></div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('Explanation in a Cart page','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_cart_page');"><?php _e('(Explain)','usces'); ?></a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><?php _e('header','usces'); ?></th>
	    <td><textarea name="header[cart]" id="header[cart]" class="mail_header"><?php echo $cart_page_datas['header']['cart']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th><?php _e('footer','usces'); ?></th>
	    <td><textarea name="footer[cart]" id="footer[cart]" class="mail_footer"><?php echo $cart_page_datas['footer']['cart']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_cart_page" class="explanation"><?php _e('You can set additional explanation to insert in a cart page.','usces'); ?></div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('Explanation in a Customer Info page','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_customer_page');"><?php _e('(Explain)','usces'); ?></a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><?php _e('header','usces'); ?></th>
	    <td><textarea name="header[customer]" id="header[customer]" class="mail_header"><?php echo $cart_page_datas['header']['customer']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th><?php _e('footer','usces'); ?></th>
	    <td><textarea name="footer[customer]" id="footer[customer]" class="mail_footer"><?php echo $cart_page_datas['footer']['customer']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_customer_page" class="explanation"><?php _e('You can set additional explanation to insert in a customer information page.','usces'); ?></div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('Explanation in Delivery and Payment method page','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_delivery_page');"><?php _e('(Explain)','usces'); ?></a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><?php _e('header','usces'); ?></th>
	    <td><textarea name="header[delivery]" id="header[delivery]" class="mail_header"><?php echo $cart_page_datas['header']['delivery']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th><?php _e('footer','usces'); ?></th>
	    <td><textarea name="footer[delivery]" id="footer[delivery]" class="mail_footer"><?php echo $cart_page_datas['footer']['delivery']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_delivery_page" class="explanation"><?php _e('You can set additional explanation to insert in a delivery and a payment method page.','usces'); ?></div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('Explanation in a Confirm page','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_confirm_page');"><?php _e('(Explain)','usces'); ?></a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><?php _e('header','usces'); ?></th>
	    <td><textarea name="header[confirm]" id="header[confirm]" class="mail_header"><?php echo $cart_page_datas['header']['confirm']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th><?php _e('footer','usces'); ?></th>
	    <td><textarea name="footer[confirm]" id="footer[confirm]" class="mail_footer"><?php echo $cart_page_datas['footer']['confirm']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_confirm_page" class="explanation"><?php _e('You can set additional explanation to insert in a confirm page.','usces'); ?></div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('Explanation in a Completion page','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_completion_page');"><?php _e('(Explain)','usces'); ?></a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><?php _e('header','usces'); ?></th>
	    <td><textarea name="header[completion]" id="header[completion]" class="mail_header"><?php echo $cart_page_datas['header']['completion']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th><?php _e('footer','usces'); ?></th>
	    <td><textarea name="footer[completion]" id="footer[completion]" class="mail_footer"><?php echo $cart_page_datas['footer']['completion']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_completion_page" class="explanation"><?php _e('You can set additional explanation to insert in a completion page.','usces'); ?></div>
</div>
</div><!--postbox-->


</div><!--poststuff-->
<input name="usces_option_update" type="submit" class="button" value="<?php _e('change decision','usces'); ?>" />
</form>
</div><!--usces_admin-->
</div><!--wrap-->