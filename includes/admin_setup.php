<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';
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
<h2>Welcart Shop <?php _e('General Setting','usces'); ?></h2>
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
<h3 class="hndle"><span><?php _e('Business settings','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('business_setting');"> (<?php _e('explanation', 'usces'); ?>) </a></h3>
<div class="inside">
<table class="form_table">
<?php 
	if($this->display_mode) :
?>
	<tr height="50">
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_display_mode');"><?php _e('Display Modes','usces'); ?></a></th>
<?php 
		foreach( (array)$this->display_mode as $key => $label ) { 
			if($key == 'Promotionsale')
				continue;
			$checked = $this->options['display_mode'] == $key ? ' checked="checked"' : '';
?>
		<td width="10"><input name="display_mode" type="radio" id="<?php echo $key; ?>" value="<?php echo $key; ?>"<?php echo $checked; ?> /></td>
		<td width="100"><label for="<?php echo $key; ?>"><?php echo $label; ?></label></td>
<?php
		}
?>
	    <td><div id="ex_display_mode" class="explanation">
<?php _e('<strong>Normal operating</strong> -Normal display', 'usces'); ?><br />
<?php _e('<strong>Campaign</strong>--Showing the campaige mode', 'usces'); ?><br />
<?php _e('<strong>Maintenance</strong> ---Showing  maintenance page. Administrater is able to see the page with normal display.', 'usces'); ?></div>
</td>
	</tr>
<?php endif; ?>
</table>
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_cat');"><?php _e('Campaign target items', 'usces'); ?></a></th>
	    <td>
<?php 
	//$dropdown_options = array('show_option_all' => __('View all categories'), 'hide_empty' => 0, 'hierarchical' => 1, 'show_count' => 0, 'orderby' => 'name', 'child_of' => USCES_ITEM_CAT_PARENT_ID, 'selected' => $this->options['campaign_category']);
	$dropdown_options = array('show_option_all' => __('all the items', 'usces'), 'hide_empty' => 0, 'hierarchical' => 1, 'show_count' => 1, 'orderby' => 'name', 'child_of' => USCES_ITEM_CAT_PARENT_ID, 'selected' => $this->options['campaign_category']);
	wp_dropdown_categories($dropdown_options);
?>
		</td>
		<td><div id="ex_cat" class="explanation"><?php _e("This is a category of the objects to add a privilege to in the case of a campaign mode. You can choose a campaign object article freely by installing a 'campaign' category for an article.", 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr>
	    <th rowspan="2"><a style="cursor:pointer;" onclick="toggleVisibility('ex_cat_privilege');"><?php _e('Campaign Award', 'usces'); ?></a></th>
	    <td><input name="cat_privilege" type="radio" id="privilege_point" value="point"<?php if($this->options['campaign_privilege'] == 'point') echo 'checked="checked"'; ?> /></td><td><label for="privilege_point"><?php _e('Points', 'usces'); ?></label></td><td><input name="point_num" type="text" class="short_str" value="<?php echo esc_attr($this->options['privilege_point']); ?>" /><?php _e('times', 'usces'); ?></td>
		<td rowspan="2"><div id="ex_cat_privilege" class="explanation"><?php _e("'Points' award applies only for monmbers. You specify the ratio of rate points. <br />'Discount'is specified in the discount rate applies to all buyers.", 'usces'); ?></div></td>
	</tr>
	<tr>
	    <td><input name="cat_privilege" type="radio" id="privilege_discount" value="discount"<?php if($this->options['campaign_privilege'] == 'discount') echo 'checked="checked"'; ?> /></td><td><label for="privilege_discount"><?php _e('Disnount', 'usces'); ?></label></td><td><input name="discount_num" type="text" class="short_str" value="<?php echo esc_attr($this->options['privilege_discount']); ?>" />%</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="business_setting" class="explanation"><?php _e('Configuration of management and display mode of shop.', 'usces'); ?></div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('Shop setting', 'usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('shop_setting');"> (<?php _e('explanation', 'usces'); ?>) </a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_company_name');"><?php _e('The company name', 'usces'); ?></a></th>
	    <td><input name="company_name" type="text" class="long_str" value="<?php echo esc_attr($this->options['company_name']); ?>" /></td>
		<td><div id="ex_company_name" class="explanation"><?php _e('Fill out this if you are a corporation.', 'usces'); ?></div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_zip_code');"><?php _e('Zip/Postal Code', 'usces'); ?></a></th>
	    <td><input name="zip_code" type="text" class="short_str" value="<?php echo esc_attr($this->options['zip_code']); ?>" /></td>
		<td><div id="ex_zip_code" class="explanation"><?php _e('Example)', 'usces'); ?>100-1001</div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_address1');"><?php _e('Address', 'usces'); ?>1</a></th>
	    <td><input name="address1" type="text" class="long_str" value="<?php echo esc_attr($this->options['address1']); ?>" /></td>
		<td><div id="ex_address1" class="explanation"><?php _e('Example)', 'usces'); ?></div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_address2');"><?php _e('Address', 'usces'); ?>2</a></th>
	    <td><input name="address2" type="text" class="long_str" value="<?php echo esc_attr($this->options['address2']); ?>" /></td>
		<td><div id="ex_address2" class="explanation"><?php _e('Example)', 'usces'); ?></div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_tel_number');"><?php _e('Phone number', 'usces'); ?></a></th>
	    <td><input name="tel_number" type="text" class="long_str" value="<?php echo esc_attr($this->options['tel_number']); ?>" /></td>
		<td><div id="ex_tel_number" class="explanation"><?php _e('Example)', 'usces'); ?>100-100-10000</div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_fax_number');"><?php _e('FAX number', 'usces'); ?></a></th>
	    <td><input name="fax_number" type="text" class="long_str" value="<?php echo esc_attr($this->options['fax_number']); ?>" /></td>
		<td><div id="ex_fax_number" class="explanation"><?php _e('Example)', 'usces'); ?>100-100-10000</div></td>
	</tr>
	<tr>
	    <th><em><?php _e('*', 'usces'); ?> </em><a style="cursor:pointer;" onclick="toggleVisibility('ex_order_mail');"><?php _e('E-mail address for ordering', 'usces'); ?></a></th>
	    <td><input name="order_mail" type="text" class="long_str" value="<?php echo esc_attr($this->options['order_mail']); ?>" /></td>
		<td><div id="ex_order_mail" class="explanation"><?php _e("<em>[Required]</em> the administrator's e-mail address to receive the order", 'usces'); ?></div></td>
	</tr>
	<tr>
	    <th><em><?php _e('*', 'usces'); ?> </em><a style="cursor:pointer;" onclick="toggleVisibility('ex_inquiry_mail');"><?php _e('Contact e-mail address', 'usces'); ?></a></th>
	    <td><input name="inquiry_mail" type="text" class="long_str" value="<?php echo esc_attr($this->options['inquiry_mail']); ?>" /></td>
		<td><div id="ex_inquiry_mail" class="explanation"><?php _e("<em>[Required]</em> the administrator's e-mail address to receive the contact meils.", 'usces'); ?></div></td>
	</tr>
	<tr>
	    <th><em><?php _e('*', 'usces'); ?> </em><a style="cursor:pointer;" onclick="toggleVisibility('ex_sender_mail');"><?php _e("Sender's e-mail address", 'usces'); ?></a></th>
	    <td><input name="sender_mail" type="text" class="long_str" value="<?php echo esc_attr($this->options['sender_mail']); ?>" /></td>
		<td><div id="ex_sender_mail" class="explanation"><?php _e("<em>[Required]</em> the sender's e-mail address to send 'thank you e-mail' to cuscomers.", 'usces'); ?></div></td>
	</tr>
	<tr>
	    <th><em><?php _e('*', 'usces'); ?> </em><a style="cursor:pointer;" onclick="toggleVisibility('ex_error_mail');"><?php _e('Address for Error mail', 'usces'); ?></a></th>
	    <td><input name="error_mail" type="text" class="long_str" value="<?php echo esc_attr($this->options['error_mail']); ?>" /></td>
		<td><div id="ex_error_mail" class="explanation"><?php _e("<em>[Required]</em>The transmission of a message ahead of the error email.", 'usces'); ?></div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_copyright');"><?php _e('copy rights', 'usces'); ?></a></th>
	    <td><input name="copyright" type="text" class="long_str" value="<?php echo esc_attr($this->options['copyright']); ?>" /></td>
		<td><div id="ex_copyright" class="explanation"><?php _e('Example)', 'usces'); ?>Copyright(c) 2009 Welcart.inc All Rights Reserved.</div></td>
	</tr>
</table>
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_postage_privilege');"><?php _e('Conditions for free shipping', 'usces'); ?></a></th>
	    <td><input name="postage_privilege" type="text" class="short_str" value="<?php echo esc_attr($this->options['postage_privilege']); ?>" /><?php _e('Above', 'usces'); ?></td>
		<td><div id="ex_postage_privilege" class="explanation"><?php _e('Total purchase prise to have free shipping. Leave it blank if it is not necessary.', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_purchase_limit');"><?php _e('default limitation number of purchase', 'usces'); ?></a></th>
	    <td><input name="purchase_limit" type="text" class="short_str" value="<?php echo esc_attr($this->options['purchase_limit']); ?>" /><?php _e('maximum amount', 'usces'); ?></td>
		<td><div id="ex_purchase_limit" class="explanation"><?php _e('initial value at registration of items. Leave it blank if it is not necessary.', 'usces'); ?></div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_rule');"><?php _e('initial value of date of sending out.', 'usces'); ?></a></th>
	    <td><select name="shipping_rule" class="short_select">
<?php foreach( (array)$this->shipping_rule as $key => $label){ $selected = $key == $this->options['shipping_rule'] ? ' selected="selected"' : ''; ?>
	<option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
<?php } ?>
</select></td>
		<td><div id="ex_shipping_rule" class="explanation"><?php _e('initial value at registration of items. Do not chose this if it does not apply to you.', 'usces'); ?></div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_tax_rate');"><?php _e('Percentage of Consumption tax', 'usces'); ?></a></th>
	    <td><input name="tax_rate" type="text" class="short_str" value="<?php echo esc_attr($this->options['tax_rate']); ?>" />%</td>
		<td><div id="ex_tax_rate" class="explanation"><?php _e('Leave it as blank if tax is included', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_tax_method');"><?php _e('method of Calculation of the tax', 'usces'); ?></a></th>
	    <td width="10"><input name="tax_method" id="tax_method_cutting" type="radio" value="cutting"<?php if($this->options['tax_method'] == 'cutting') echo 'checked="checked"'; ?> /></td><td width="60"><label for="tax_method_cutting"><?php _e('drop fractions', 'usces'); ?></label></td>
	    <td width="10"><input name="tax_method" id="tax_method_bring" type="radio" value="bring"<?php if($this->options['tax_method'] == 'bring') echo 'checked="checked"'; ?> /></td><td width="60"><label for="tax_method_bring"><?php _e('raise to a unit', 'usces'); ?></label></td>
	    <td width="10"><input name="tax_method" id="tax_method_rounding" type="radio" value="rounding"<?php if($this->options['tax_method'] == 'rounding') echo 'checked="checked"'; ?> /></td><td width="60"><label for="tax_method_rounding"><?php _e('round up numbers of five and above and round down anything under', 'usces'); ?></label></td>
		<td><div id="ex_tax_method" class="explanation"><?php _e('Leave it as blank if tax is included', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_cod_fee');"><?php _e('C.O.D', 'usces'); ?></a></th>
	    <td id="cod_type_field"></td><td><input type="button" name="button_cod_detail" value="<?php _e('Detailed setting', 'usces'); ?>" id="detailed_setting" /></td>
		<td><div id="ex_cod_fee" class="explanation"><?php _e('Cost for C.O.D. leave it blank if it in not necessary.', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_transferee');"><?php _e('Account information for transfer', 'usces'); ?></a></th>
	    <td><textarea name="transferee" class="long_txt"><?php echo esc_html($this->options['transferee']); ?></textarea></td>
		<td><div id="ex_transferee" class="explanation"><?php _e('The acount number for bank transfer. You can enter the information as you wish.  The contents will apear in the mail.', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_membersystem_state');"><?php _e('membership syetem', 'usces'); ?></a></th>
	    <td width="10"><input name="membersystem_state" id="membersystem_state_activate" type="radio" value="activate"<?php if($this->options['membersystem_state'] == 'activate') echo 'checked="checked"'; ?> /></td><td width="60"><label for="membersystem_state_activate"><?php _e('to use', 'usces'); ?></label></td>
	    <td width="10"><input name="membersystem_state" id="membersystem_state_deactivate" type="radio" value="deactivate"<?php if($this->options['membersystem_state'] == 'deactivate') echo 'checked="checked"'; ?> /></td><td width="60"><label for="membersystem_state_deactivate"><?php _e('not to use', 'usces'); ?></label></td>
		<td><div id="ex_membersystem_state" class="explanation"><?php _e('Would you like to use membership system or not?', 'usces'); ?></div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_membersystem_point');"><?php _e('membership points', 'usces'); ?></a></th>
	    <td width="10"><input name="membersystem_point" id="membersystem_point_activate" type="radio" value="activate"<?php if($this->options['membersystem_point'] == 'activate') echo 'checked="checked"'; ?> /></td><td width="60"><label for="membersystem_point_activate"><?php _e('to grant', 'usces'); ?></label></td>
	    <td width="10"><input name="membersystem_point" id="membersystem_point_deactivate" type="radio" value="deactivate"<?php if($this->options['membersystem_point'] == 'deactivate') echo 'checked="checked"'; ?> /></td><td width="60"><label for="membersystem_point_deactivate"><?php _e('not to grant', 'usces'); ?></label></td>
		<td><div id="ex_membersystem_point" class="explanation"><?php _e("Would you like to use 'points granting syetem' when you choose to use membership system?", 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_point_rate');"><?php _e('The initial value of point rate', 'usces'); ?></a></th>
	    <td colspan="2"><input name="point_rate" type="text" class="short_str" value="<?php echo esc_attr($this->options['point_rate']); ?>" />%</td>
		<td><div id="ex_point_rate" class="explanation"><?php _e('initial value at registration of items. Leave it blank if it is not necessary.', 'usces'); ?></div></td>
	</tr>
	<tr>
	    <th><a style="cursor:pointer;" onclick="toggleVisibility('ex_start_point');"><?php _e('Point at the registration of membership', 'usces'); ?></a></th>
	    <td colspan="2"><input name="start_point" type="text" class="short_str" value="<?php echo esc_attr($this->options['start_point']); ?>" />pt</td>
		<td><div id="ex_start_point" class="explanation"><?php _e('Points granted  at the first membersip registration.', 'usces'); ?></div></td>
	</tr>
	<tr>
	    <th rowspan="2"><a style="cursor:pointer;" onclick="toggleVisibility('ex_point_coverage');"><?php _e('Areas of Point Redemption', 'usces'); ?></a></th>
	    <td><input name="point_coverage" type="radio" id="point_coverage0" value="0"<?php if( !$this->options['point_coverage'] ) echo 'checked="checked"'; ?> /></td><td width="220"><label for="point_coverage0"><?php _e('Limited Only to Total Merchandise Price', 'usces'); ?></label></td>
		<td rowspan="2"><div id="ex_point_coverage" class="explanation"><?php _e('お客様が利用できるポイントの適用範囲を選択します。<br />初期値は「商品合計金額のみに制限」されています。「商品合計額及び手数料などにも適用」を選択すると送料や代引き手数料もポイントで支払う事ができるようになります。', 'usces'); ?></div></td>
	</tr>
	<tr>
	    <td><input name="point_coverage" type="radio" id="point_coverage1" value="1"<?php if( $this->options['point_coverage'] ) echo 'checked="checked"'; ?> /></td><td width="220"><label for="point_coverage1"><?php _e('Applicable to Total Merchandise Price and Handling Fee', 'usces'); ?></label></td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="shop_setting" class="explanation"><?php _e('the initial rate of the shop', 'usces'); ?></div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('payment method', 'usces'); ?></span></h3>
<div class="inside">
	<div id="postpayment"><div id="payment-response"></div>
<?php
//	$option = get_option('usces');
//	$option['payment_method'] = array();
//	update_option('usces', $option);
//	$this->options = get_option('usces');
	$metadata = $this->options['payment_method'];
	payment_list($metadata);
	payment_form();
?>
<hr size="1" color="#CCCCCC" />
<div id="Commonoption" class="explanation"><?php _e('<em>[Required]</em>possible payment method', 'usces'); ?></div>
</div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('Common Options', 'usces'); ?></span></h3>
<div class="inside">
	<div id="postoptcustomstuff"><div id="ajax-response"></div>
<?php
	$metadata = has_item_option_meta(USCES_CART_NUMBER);
	list_item_option_meta($metadata);
	common_option_meta_form();
?>
<hr size="1" color="#CCCCCC" />
<div id="Commonoption" class="explanation"><?php _e("Conditions which will be selected at the purchase. You can use the options which you hve registered here, as an option in the master items.", 'usces'); ?></div>
</div>
</div>
</div><!--postbox-->

</div><!--poststuff-->



<input name="usces_option_update" type="submit" class="button" value="<?php _e('change decision','usces'); ?>" />
<input type="hidden" id="post_ID" name="post_ID" value="<?php echo USCES_CART_NUMBER ?>" />


<?php
if( 'change' == $this->options['cod_type'] ) {
	$cod_type = 'change';
}else{
	$cod_type = 'fix';
}
?>
<div id="cod_dialog" title="<?php _e('C.O.D. Detailed setting', 'usces'); ?>"> 
	<p id="cod-response"><?php _e('Please be settled for a fee in spite of Update.', 'usces'); ?></p> 
 
	<fieldset> 
	<table id="cod_type_table">
		<tr><th><?php _e('Type of the fee', 'usces'); ?></th><td class="radio"><input name="cod_type" id="cod_type_fix" type="radio" value="fix"<?php if( 'fix' == $cod_type) echo ' checked="checked"'; ?> /></td><td><label for="cod_type_fix"><?php _e('Fixation C.O.D.', 'usces'); ?></label></td><td class="radio"><input name="cod_type" id="cod_type_change" type="radio" value="change"<?php if( 'change' == $cod_type) echo ' checked="checked"'; ?> /></td><td><label for="cod_type_change"><?php _e('Variable C.O.D.', 'usces'); ?></label></td></tr>
	</table>
	<table id="cod_fix_table">
		<tr><th><?php _e('Fee', 'usces'); ?></th><td><input name="cod_fee" type="text" class="short_str ui-widget-content ui-corner-all" value="<?php echo $this->options['cod_fee']; ?>" /><?php usces_crcode(); ?></td></tr>
	</table>
	<div id="cod_change_table">
	<input name="addrow" id="add_row" type="button" value="<?php _e('Add row', 'usces'); ?>" /><input name="delrow" type="button" id="del_row" value="<?php _e('Delete row', 'usces'); ?>" />
	<table>
		<thead>
			<tr><th colspan="3"><?php _e('A purchase amount', 'usces'); ?>(<?php usces_crcode(); ?>)</th><th><?php _e('Fee', 'usces'); ?>(<?php usces_crcode(); ?>)</th></tr>
			<tr><td class="cod_f">0</td><td class="cod_m">～</td><td class="cod_e"><input name="cod_first_amount" type="text" class="short_str ui-widget-content ui-corner-all" value="<?php echo esc_attr($this->options['cod_first_amount']); ?>" /></td><td class="cod_cod"><input name="cod_first_fee" type="text" class="short_str" value="<?php echo esc_attr($this->options['cod_first_fee']); ?>" /></td></tr>
		</thead>
		<tbody id="cod_change_field">
<?php
	if( isset($this->options['cod_amounts']) && isset($this->options['cod_fees']) ){
		foreach ( (array)$this->options['cod_amounts'] as $key => $value ){
?>
			<tr id="tr_<?php echo esc_attr($key); ?>"><td class="cod_f"><span id="amount_<?php echo esc_attr($key); ?>"><?php if( $key === 0 ){echo ($this->options['cod_first_amount'] + 1);}else{echo ($this->options['cod_amounts'][($key-1)] + 1);} ?></span></td><td class="cod_m">～</td><td class="cod_e"><input name="cod_amounts[<?php echo esc_attr($key); ?>]" type="text" class="short_str ui-widget-content ui-corner-all" value="<?php  echo esc_attr($value); ?>" /></td><td class="cod_cod"><input name="cod_fees[<?php echo esc_attr($key); ?>]" type="text" class="short_str" value="<?php echo esc_attr($this->options['cod_fees'][$key]); ?>" /></td></tr>
<?php			
		} 
	}
	if( !isset($this->options['cod_amounts']) || empty($this->options['cod_amounts']) ){
		$end_amount = $this->options['cod_first_amount'] + 1;
	}else{
		$cod_last = count($this->options['cod_amounts']) - 1;
		$end_amount = $this->options['cod_amounts'][$cod_last] + 1;
	}
?>
		</tbody>
		<tfoot>
			<tr><td class="cod_f"><span id="end_amount"><?php echo esc_attr($end_amount); ?></span></td><td class="cod_m">～</td><td>&nbsp;</td><td class="cod_cod"><input name="cod_end_fee" type="text" class="short_str" value="<?php echo esc_attr($this->options['cod_end_fee']); ?>" /></td></tr>
		</tfoot>
	</table>
	</div>
	</fieldset> 
</div> 


</form>
</div><!--usces_admin-->
</div><!--wrap-->

 