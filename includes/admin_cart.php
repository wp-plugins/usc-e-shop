<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';
$cart_page_datas = stripslashes_deep($this->options['cart_page_data']);
if(empty($cart_page_datas))
	$cart_page_datas = array();
$indi_item_name = $this->options['indi_item_name'];
$pos_item_name = $this->options['pos_item_name'];
foreach( (array)$indi_item_name as $key => $value){
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

	//20100809ysk start
	var $tabs = $('#uscestabs').tabs({
		cookie: {
			// store cookie for a day, without, it would be a session cookie
			expires: 1
		}
	});

	customOrder = {
		settings: {
			url: uscesL10n.requestFile,
			type: 'POST',
			cache: false,
			success: function(data, dataType) {
				$("tbody#item-opt-list").html(data);
			},
			error: function(msg) {
				$("#ajax-response").html(msg);
			}
		},

		add: function() {
			if($("#newcsodkey").val() == '' || $("#newcsodname").val() == '') return;

			var key = $("#newcsodkey").val();
			var name = $("#newcsodname").val();
			var value = $("#newcsodvalue").val();
			var means = $("#newcsodmeans").val();
			var essential = ($("input#newcsodessential").attr("checked")) ? '1' : '0';

			var s = customOrder.settings;
			s.data = "action=custom_order_ajax&add=1&newcsodkey="+key+"&newcsodname="+name+"&newcsodvalue="+value+"&newcsodmeans="+means+"&newcsodessential="+essential;
			s.success = function(data, dataType) {
				$("table#optlist-table").removeAttr("style");
				$("tbody#item-opt-list").html(data);
				$("#newcsodkey").val("");
				$("#newcsodname").val("");
				$("#newcsodvalue").val("");
				$("#newcsodmeans").attr({selectedIndex: 0});
				$("#newcsodessential").attr({checked: false});
			};
			$.ajax(s);
			return false;
		},

		upd: function(key) {
			var name = $(':input[name="csod['+key+'][name]"]').val();
			var value = $(':input[name="csod['+key+'][value]"]').val();
			var means = $(':input[name="csod['+key+'][means]"]').val();
			var essential = ($(':input[name="csod['+key+'][essential]"]').attr("checked")) ? '1' : '0';

			var s = customOrder.settings;
			s.data = "action=custom_order_ajax&update=1&csodkey="+key+"&csodname="+name+"&csodvalue="+value+"&csodmeans="+means+"&csodessential="+essential;
			$.ajax(s);
			return false;
		},

		del: function(key) {
			var s = customOrder.settings;
			s.data = "action=custom_order_ajax&delete=1&csodkey="+key;
			$.ajax(s);
			return false;
		}
	};
	//20100809ysk end

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

<!--20100809ysk start-->
<div id="uscestabs">
	<ul>
		<li><a href="#cart_page_setting_1"><?php _e('Rule of the column for a item name','usces'); ?></a></li>
		<li><a href="#cart_page_setting_2"><?php _e('Explanation in a Cart page','usces'); ?></a></li>
		<li><a href="#cart_page_setting_3"><?php _e('custom order','usces'); ?></a></li>
	</ul>

<div id="cart_page_setting_1">
<!--20100809ysk end-->

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

<!--20100809ysk start-->
</div><!--cart_page_setting_1-->

<div id="cart_page_setting_2">
<!--20100809ysk end-->

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

<!--20100809ysk start-->
</div><!--cart_page_setting_2-->

<?php
	$meta = has_custom_order_meta();
	$display = (empty($meta)) ? ' style="display: none;"' : '';
	$means = get_option('usces_custom_order_select');
	$meansoption = '';
	foreach($means as $meankey => $meanvalue) {
		$meansoption .= '<option value="'.$meankey.'">'.$meanvalue."</option>\n";
	}
?>
<div id="cart_page_setting_3">
	<div class="postbox">
	<h3 class="hndle"><span><?php _e('Custom Order Options', 'usces'); ?><a style="cursor:pointer;" onclick="toggleVisibility('ex_custom_order');"><?php _e('(Explain)','usces'); ?></a></span></h3>
	<div class="inside">
	<div id="postoptcustomstuff"><div id="ajax-response"></div>
	<table id="optlist-table" class="list"<?php echo $display; ?>>
		<thead>
		<tr>
		<th class="left"><?php _e('key name','usces') ?></th>
		<th rowspan="2"><?php _e('selected amount','usces') ?></th>
		</tr>
		<tr>
		<th class="left"><?php _e('field name','usces') ?></th>
		</tr>
		</thead>
		<tbody id="item-opt-list">
<?php
	if(empty($meta)) {
?>
			<tr><td></td></tr>
<?php
	} else {
		foreach($meta as $key => $entry) 
			echo _list_custom_order_meta_row($key, $entry);
	}
?>
		</tbody>
	</table>

	<p><strong><?php _e('Add a new custom order option','usces') ?> : </strong></p>
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
		<input type="text" name="newcsodkey" id="newcsodkey" class="optname" value="" />
		<input type="text" name="newcsodname" id="newcsodname" class="optname" value="" />
		<div class="optcheck"><select name='newcsodmeans' id='newcsodmeans'><?php echo $meansoption; ?></select>
		<input type="checkbox" name="newcsodessential" id="newcsodessential" /><label for='newcsodessential'><?php _e('Required','usces') ?></label></div>
		</td>
		<td class='item-opt-value'><textarea name="newcsodvalue" id="newcsodvalue" class='optvalue'></textarea></td>
		</tr>

		<tr><td colspan="2" class="submit">
		<input type="button" name="add_custom_order" id="add_custom_order" value="<?php _e('Add custom order options','usces') ?>" onclick="customOrder.add();" />
		</td></tr>
		</tbody>
	</table>

	<hr size="1" color="#CCCCCC" />
	<div id="ex_custom_order" class="explanation"><?php _e("Conditions which will be selected at the purchase. You can use the options which you hve registered here, as an option in the master items.", 'usces'); ?></div>
	</div>
	</div>
	</div><!--postbox-->
</div><!--cart_page_setting_3-->
</div><!--tabs-->
<!--20100809ysk end-->

</div><!--poststuff-->
<input name="usces_option_update" type="submit" class="button" value="<?php _e('change decision','usces'); ?>" />
</form>
</div><!--usces_admin-->
</div><!--wrap-->