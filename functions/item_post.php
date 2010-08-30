<?php
/**
 * item option
 */
function has_item_option_meta( $postid ) {
	global $wpdb;

	return $wpdb->get_results( $wpdb->prepare("SELECT meta_key, meta_value, meta_id, post_id
			FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE '%s' 
			ORDER BY meta_key", $postid, '_iopt_%'), ARRAY_A );

}

/**
 * item sku
 */
function has_item_sku_meta( $postid ) {
	global $wpdb;

	return $wpdb->get_results( $wpdb->prepare("SELECT meta_key, meta_value, meta_id, post_id
			FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE '%s' 
			ORDER BY meta_key", $postid, '_isku_%'), ARRAY_A );

}

/**
 * list_item_option
 */
function list_item_option_meta( $meta ) {
	// Exit if no meta
	if ( ! $meta ) {
		echo '
<table id="optlist-table" class="list" style="display: none;">
	<thead>
	<tr>
		<th class="left">' . __('option name','usces') . '</th>
		<th>' . __('selected amount','usces') . '</th>
	</tr>
	</thead>
	<tbody id="item-opt-list">
	<tr><td></td></tr>
	</tbody>
</table>'; //TBODY needed for list-manipulation JS
		return;
	}
?>
<table id="optlist-table" class="list">
	<thead>
	<tr>
		<th class="left"><?php _e('option name','usces') ?></th>
		<th><?php _e('selected amount','usces') ?></th>
	</tr>
	</thead>
	<tbody id="item-opt-list">
<?php
	foreach ( $meta as $entry )
		echo _list_item_option_meta_row( $entry );
?>
	</tbody>
</table>
<?php
}

/**
 * list_item_sku
 */
function list_item_sku_meta( $meta ) {
	// Exit if no meta
	if ( ! $meta ) {
		echo '
<table id="skulist-table" class="list" style="display: none;">
	<thead>
	<tr>
		<th>' . __('SKU code','usces') . '</th>
		<th>' . apply_filters('usces_filter_listprice_label', __('normal price','usces'), NULL, NULL) . '</th>
		<th>' . apply_filters('usces_filter_sellingprice_label', __('Sale price','usces'), NULL, NULL) . '</th>
		<th>' . __('stock','usces') . '</th>
		<th>' . __('stock status', 'usces') . '</th>
	</tr>
	</thead>
	<tbody id="item-sku-list">
	<tr><td></td><td></td><td></td><td></td><td></td></tr>
	</tbody>
</table>'; //TBODY needed for list-manipulation JS
		return;
	}
?>
<table id="skulist-table" class="list">
	<thead>
	<tr>
		<th class="left"><?php _e('SKU code','usces'); ?></th>
		<th><?php echo apply_filters('usces_filter_listprice_label', __('normal price','usces'), NULL, NULL); ?></th>
		<th><?php echo apply_filters('usces_filter_sellingprice_label', __('Sale price','usces'), NULL, NULL); ?></th>
		<th><?php _e('stock','usces'); ?></th>
		<th><?php _e('stock status','usces'); ?></th>
	</tr>
	<tr>
		<th><?php _e('SKU display name ','usces'); ?></th>
		<th><?php _e('unit','usces'); ?></th>
		<th colspan="2"><?php echo ( defined('WCEX_DLSELLER') ) ? __('Charging type','usces') : ''; ?> </th>
		<th><?php _e('Apply business package','usces'); ?></th>
	</tr>
	</thead>
	<tbody id="item-sku-list">
<?php
	foreach ( $meta as $entry )
		echo _list_item_sku_meta_row( $entry );
?>
	</tbody>
</table>
<?php
}


/**
 * payment_list
 */
function payment_list( $meta ) {
	// Exit if no meta
	if ( ! $meta ) {
		echo '
<table id="payment-table" class="list" style="display: none;">
	<thead>
	<tr>
		<th class="left">' . __('A payment method name','usces') . '</th>
		<th>' . __('explanation','usces') . '</th>
		<th>' . __('Type of payment','usces') . '</th>
		<th>' . __('Payment module','usces') . '</th>
	</tr>
	</thead>
	<tbody id="payment-list">
	<tr><td></td><td></td><td></td></tr>
	</tbody>
</table>'; //TBODY needed for list-manipulation JS
		return;
	}
?>
<table id="payment-table" class="list">
	<thead>
	<tr>
		<th class="left"><?php _e('A payment method name','usces') ?></th>
		<th><?php _e('explanation','usces') ?></th>
		<th><?php _e('Type of payment','usces') ?></th>
		<th><?php _e('Payment module','usces') ?></th>
	</tr>
	</thead>
	<tbody id="payment-list">
<?php
	foreach ( $meta as $key => $entry )
		echo _payment_list_row( $key, $entry );
?>
	</tbody>
</table>
<?php
}

/**
 * option meta row
 */
function _list_item_option_meta_row( $entry ) {
	$r = '';
	$style = '';
	$means = get_option('usces_item_option_select');

	if ( is_serialized( $entry['meta_value'] ) ) {
		$entry['meta_value'] = maybe_unserialize( $entry['meta_value'] );
	} else {
		return;
	}
	
	$readonly = " readonly='true'";
	$key = attribute_escape(substr($entry['meta_key'],6));
	$meansoption = '';
	foreach($means as $meankey => $meanvalue){
		if($meankey == $entry['meta_value']['means']) {
			$selected = ' selected="selected"';
		}else{
			$selected = '';
		}
		$meansoption .= '<option value="' . $meankey . '"' . $selected . '>' . $meanvalue . "</option>\n";
	}
	$essential = $entry['meta_value']['essential'] == 1 ? " checked='checked'" : "";
	$value = '';
	if(is_array($entry['meta_value']['value'])){
		foreach($entry['meta_value']['value'] as $k => $v){
			$value .= htmlspecialchars($v) . "\n";
		}
	}
	$value = trim($value);
	$id = (int) $entry['meta_id'];

	$r .= "\n\t<tr id='itemopt-{$id}' class='{$style}'>";
	$r .= "\n\t\t<td class='left'><div><input name='itemopt[{$id}][key]' id='itemopt[{$id}][key]' class='optname' type='text' size='20' value='{$key}'{$readonly} /></div>";
	$r .= "\n\t\t<div class='optcheck'><select name='itemopt[{$id}][means]' id='itemopt[{$id}][means]'>" . $meansoption . "</select>\n";
	$r .= "<input name='itemopt[{$id}][essential]' id='itemopt[{$id}][essential]' type='checkbox' value='1'{$essential} /><label for='itemopt[{$id}][essential]'>" . __('Required','usces') . "</label></div>";
	$r .= "\n\t\t<div class='submit'><input name='deleteitemopt[{$id}]' id='deleteitemopt[{$id}]' type='button' value='".attribute_escape(__( 'Delete' ))."' onclick='if( jQuery(\"#post_ID\").val() < 0 ) return; itemOpt.post(\"deleteitemopt\", {$id});' />";
	$r .= "\n\t\t<input name='updateitemopt' id='updateitemopt[{$id}]' type='button' value='".attribute_escape(__( 'Update' ))."' onclick='if( jQuery(\"#post_ID\").val() < 0 ) return; itemOpt.post(\"updateitemopt\", {$id});' /></div>";
	$r .= "</td>";

	$r .= "\n\t\t<td class='item-opt-value'><textarea name='itemopt[{$id}][value]' id='itemopt[{$id}][value]' class='optvalue'>{$value}</textarea></td>\n\t</tr>";
	return $r;
}

/**
 * sku meta row
 */
function _list_item_sku_meta_row( $entry ) {
	$r = '';
	$style = '';

	if ( is_serialized( $entry['meta_value'] ) ) {
		$entry['meta_value'] = maybe_unserialize( $entry['meta_value'] );
	} else {
		return;
	}
	$readonly = "";
	$key = attribute_escape(substr($entry['meta_key'],6));
	$cprice = $entry['meta_value']['cprice'];
	$price = $entry['meta_value']['price'];
	$zaikonum = $entry['meta_value']['zaikonum'];
	$zaiko = $entry['meta_value']['zaiko'];
	$skudisp = $entry['meta_value']['disp'];
	$skuunit = $entry['meta_value']['unit'];
	$skugptekiyo = $entry['meta_value']['gptekiyo'];
	$charging_type = $entry['meta_value']['charging_type'];
	$id = (int) $entry['meta_id'];
	$zaikoselectarray = get_option('usces_zaiko_status');
	if( defined('WCEX_DLSELLER') ){
		$advance_field = '
	<select id="itemsku[' . $id . '][charging_type]" name="itemsku[' . $id . '][charging_type]" class="charging_type">
		<option value=""' . ( (0 === (int)$charging_type) ? ' selected="selected"' : '' ) . '>' . __('一括課金（即日）','usces') . '</option>
		<option value="1"' . ( (1 === (int)$charging_type) ? ' selected="selected"' : '' ) . '>' . __('月次課金（翌月1日）','usces') . '</option>
	</select>';

	}else{
		$advance_field = '';
	}
	
	$r .= "\n\t<tr id='itemsku-{$id}' class='{$style}'>";
	$r .= "\n\t\t<td class='item-sku-key'><input name='itemsku[{$id}][key]' id='itemsku[{$id}][key]' class='skuname' type='text' value='{$key}'{$readonly} /></td>";
	$r .= "\n\t\t<td class='item-sku-cprice'><input name='itemsku[{$id}][cprice]' id='itemsku[{$id}][cprice]' class='skuprice' type='text' value='{$cprice}' /></td>";
	$r .= "\n\t\t<td class='item-sku-price'><input name='itemsku[{$id}][price]' id='itemsku[{$id}][price]' class='skuprice' type='text' value='{$price}' /></td>";
	$r .= "\n\t\t<td class='item-sku-zaikonum'><input name='itemsku[{$id}][zaikonum]' id='itemsku[{$id}][zaikonum]' class='skuzaikonum' type='text' value='{$zaikonum}' /></td>";
	$r .= "\n\t\t<td class='item-sku-zaiko'><select id='itemsku[{$id}][zaiko]' name='itemsku[{$id}][zaiko]' class='skuzaiko'>";
	for ( $i=0; $i<count($zaikoselectarray); $i++ ) {
		$selected = ( $i == $zaiko ) ? " selected='selected'" : '';
		$r .= "\n\t\t\t<option value='{$i}'{$selected}>{$zaikoselectarray[$i]}</option>";
	}
	$r .= "\n\t\t</select></td>";
	$r .= "\n\t</tr>";
	$r .= "\n\t<tr>";
	$r .= "\n\t\t<td class='item-sku-key rowbottom'><input name='itemsku[{$id}][skudisp]' id='itemsku[{$id}][skudisp]' class='skudisp' type='text' value='{$skudisp}' />";
	$r .= "<div class='submit'><input name='deleteitemsku[{$id}]' id='deleteitemsku[{$id}]' type='button' value='".attribute_escape(__( 'Delete' ))."' onclick='if( jQuery(\"#post_ID\").val() < 0 ) return; itemSku.post(\"deleteitemsku\", {$id});' />";
	$r .= "<input name='updateitemsku' id='updateitemsku[{$id}]' type='button' value='".attribute_escape(__( 'Update' ))."' onclick='if( jQuery(\"#post_ID\").val() < 0 ) return; itemSku.post(\"updateitemsku\", {$id});' /></div>";
	$r .= "</td>";

	$r .= "\n\t\t<td class='item-sku-cprice rowbottom'><input name='itemsku[{$id}][skuunit]' id='itemsku[{$id}][skuunit]' class='skuunit' type='text' value='{$skuunit}' /></td>";
	$r .= "\n\t\t<td colspan='2' class='item-sku-price rowbottom'>" . $advance_field . "</td>";
	$r .= "\n\t\t<td class='item-sku-zaiko rowbottom'><select id='itemsku[{$id}][skugptekiyo]' name='itemsku[{$id}][skugptekiyo]' class='skuzaiko'>";
	$r .= "\n\t\t\t<option value='0'";
	$r .= ($skugptekiyo == 0) ? " selected='selected'" : "";
	$r .= ">" . __('Not apply','usces') . "</option>";
	$r .= "\n\t\t\t<option value='1'";
	$r .= ($skugptekiyo == 1) ? " selected='selected'" : "";
	$r .= ">" . __('Apply','usces') . "</option>";
	$r .= "\n\t\t</select></td>";
	$r .= "\n\t</tr>";
	return $r;
}

/**
 * payment_list_row
 */
function _payment_list_row( $id, $payments ) {
	global $usces;
	$r = '';
	$style = '';

	if ( !$payments ) return;
	
	$name = $payments['name'];
	$explanation = $payments['explanation'];
	$settlement = $payments['settlement'];
	$module = $payments['module'];

	$r .= "\n\t<tr>";
	$r .= "\n\t\t<td class='paymentname'><div><input name='payment[{$id}][name]' id='payment[{$id}][name]' class='paymentname' type='text' value='{$name}' /></div>";
	$r .= "\n\t\t<div class='submit'><input name='deletepayment' id='deletepayment[{$id}]' type='button' value='".attribute_escape(__( 'Delete' ))."' onclick='payment.post(\"del\", {$id});' />";
	$r .= "\n\t\t<input name='updatepayment' id='updatepayment[{$id}]' type='button' value='".attribute_escape(__( 'Update' ))."' onclick='payment.post(\"update\", {$id});' /></div>";
	$r .= "</td>";
	$r .= "\n\t\t<td class='paymentexplanation'><textarea name='payment[{$id}][explanation]' id='payment[{$id}][explanation]' class='paymentexplanation'>{$explanation}</textarea></td>";
	$r .= "\n\t\t<td class='paymentsettlement'><select name='payment[{$id}][settlement]' id='payment[{$id}][settlement]' class='paymentsettlement'>";
	foreach ($usces->payment_structure as $psk => $psv){
		$selected = ($psk == $settlement) ? ' selected="selected"' : '';
		$r .= "\n\t\t<option value='{$psk}'{$selected}>{$psv}</option>";
	}
	$r .= "\n\t\t</select></td>";
	$r .= "\n\t\t<td class='paymentmodule'><div><input name='payment[{$id}][module]' id='payment[{$id}][module]' class='paymentmodule' type='text' value='{$module}' /></div></td>";
	$r .= "\n\t</tr>";
	return $r;
}

/**
 * common_option_meta_form
 */
function common_option_meta_form() {
	$means = get_option('usces_item_option_select');
	$meansoption = '';
	foreach($means as $meankey => $meanvalue){
		$meansoption .= '<option value="' . $meankey . '">' . $meanvalue . "</option>\n";
	}
?>
<p><strong><?php _e('Add a new option','usces') ?> : </strong></p>
<table id="newmeta2">
<thead>
<tr>
<th class="left"><label for="metakeyselect"><?php _e('option name','usces') ?></label></th>
<th><label for="metavalue"><?php _e('selected amount','usces') ?></label></th>
</tr>
</thead>

<tbody>
<tr>
<td class='item-opt-key'>
<input type="text" id="newoptname" name="newoptname" class="optname" tabindex="7" value="" />
<div class="optcheck"><select name='newoptmeans' id='newoptmeans'><?php echo $meansoption; ?></select>
<input name="newoptessential" type="checkbox" id="newoptessential" /><label for='newoptessential'><?php _e('Required','usces') ?></label></div>
</td>
<td class='item-opt-value'><textarea id="newoptvalue" name="newoptvalue" class='optvalue'></textarea></td>
</tr>

<tr><td colspan="2" class="submit">
<input name="add_comopt" type="button" id="add_comopt" tabindex="9" value="<?php _e('Add common options','usces') ?>" onclick="itemOpt.post('addcommonopt', 0);" />
</td></tr>
</tbody>
</table>
<?php 

}

/**
 * item_option_meta_form
 */
function item_option_meta_form() {
	global $wpdb;
	$usces_options = get_option('usces');
	$limit = (int) apply_filters( 'postmeta_form_limit', 30 );
	$cart_number = (int)get_option('usces_cart_number');
	$keys = $wpdb->get_col( "
		SELECT meta_key
		FROM $wpdb->postmeta
		WHERE meta_key LIKE '_iopt_%' AND post_id = $cart_number 
		ORDER BY meta_key ASC
		LIMIT $limit" );
	$means = get_option('usces_item_option_select');
	$meansoption = '';
	foreach($means as $meankey => $meanvalue){
		$meansoption .= '<option value="' . $meankey . '">' . $meanvalue . "</option>\n";
	}
?>
<p><strong><?php _e('Applicable product options','usces') ?> : </strong></p>
<table id="newmeta2">
<thead>
<tr>
<th class="left"><label for="metakeyselect"><?php _e('option name','usces') ?></label></th>
<th><label for="metavalue"><?php _e('selected amount','usces') ?></label></th>
</tr>
</thead>

<tbody>
<tr>
<td class='item-opt-key'>
<?php if ( $keys ) { ?>
<select id="optkeyselect" name="optkeyselect" class="optkeyselect" tabindex="7" onchange="if( jQuery('#post_ID').val() < 0 ) return; itemOpt.post('keyselect', this.value);">
<option value="#NONE#"><?php _e( '- Select -' ); ?></option>
<?php

	foreach ( $keys as $key ) {
		$key = attribute_escape( substr($key, 6) );
		echo "\n<option value='$key'>$key</option>";
	}
?>
</select>
<input type="text" id="newoptname" name="newoptname" class="hide-if-js optname" value="" />
<div class="optcheck"><select name='newoptmeans' id='newoptmeans'><?php echo $meansoption; ?></select>
<input name="newoptessential" type="checkbox" id="newoptessential" /><label for='newoptessential'><?php _e('Required','usces') ?></label></div>
<!--<a href="#postcustomstuff" class="hide-if-no-js" onClick="jQuery('#newoptname, #optkeyselect, #enternew, #cancelnew').toggle();return false;">
<span id="enternew"><?php _e('Enter new'); ?></span>
<span id="cancelnew" class="hidden"><?php _e('Cancel'); ?></span></a>
--><?php } else { ?>
<!--<input type="text" id="newoptname" name="newoptname" class="item-opt-key" tabindex="7" value="" />
<input name="newoptmeans" type="checkbox" id="newoptmeans" class="item-opt-means" /><label for='newoptmeans'><?php _e('Multi-select','usces') ?></label></div>
-->
<?php _e('Please create a common option.','usces') ?>
<?php } ?>
</td>
<td class='item-opt-value'><textarea id="newoptvalue" name="newoptvalue" class='optvalue'></textarea></td>
</tr>

<tr><td colspan="2" class="submit">
<input name="add_itemopt" type="button" id="add_itemopt" tabindex="9" value="<?php _e('Apply an option','usces') ?>" onclick="if( jQuery('#post_ID').val() < 0 ) return; itemOpt.post('additemopt', 0);" />
</td></tr>
</tbody>
</table>
<?php 

}

/**
 * item_sku_meta_form
 */
function item_sku_meta_form() {
	global $wpdb;

	$limit = (int) apply_filters( 'postmeta_form_limit', 30 );
	$keys = $wpdb->get_col( "
		SELECT meta_key
		FROM $wpdb->postmeta
		WHERE meta_key LIKE '_isku_%' 
		GROUP BY meta_key 
		LIMIT $limit" );
?>
<p><strong><?php _e('Add new SKU','usces') ?> : </strong></p>
<table id="newsku">
<thead>
<tr>
	<th class="left"><?php _e('SKU code','usces') ?></th>
	<th><?php echo apply_filters('usces_filter_listprice_label', __('normal price','usces'), NULL, NULL); ?></th>
	<th><?php echo apply_filters('usces_filter_sellingprice_label', __('Sale price','usces'), NULL, NULL); ?></th>
	<th><?php _e('stock','usces') ?></th>
	<th><?php _e('stock status','usces') ?></th>
</tr>
<tr>
	<th><?php _e('SKU display name ','usces') ?></th>
	<th><?php _e('unit','usces') ?></th>
	<th colspan="2"><?php echo ( defined('WCEX_DLSELLER') ) ? __('Charging type','usces') : ''; ?> </th>
	<th><?php _e('Apply business package','usces') ?></th>
</tr>
</thead>

<tbody>
<tr>
<td id="newskuleft" class='item-sku-key'>
<input type="text" id="newskuname" name="newskuname" class="newskuname"value="" />
</td>
<td class='item-sku-cprice'><input type="text" id="newskucprice" name="newskucprice" class='newskuprice' /></td>
<td class='item-sku-price'><input type="text" id="newskuprice" name="newskuprice" class='newskuprice' /></td>
<td class='item-sku-zaikonum'><input type="text" id="newskuzaikonum" name="newskuzaikonum" class='newskuzaikonum' /></td>
<td class='item-sku-zaiko'>
<select id="newskuzaikoselect" name="newskuzaikoselect" class="newskuzaikoselect">
<?php
	$zaikoselectarray = get_option('usces_zaiko_status');
	foreach ( $zaikoselectarray as $v => $l ) {
		echo "\n<option value='{$v}'>$l</option>";
	}
?>
</select>
</td>
</tr>
<tr>
<td class='item-sku-key'><input type="text" id="newskudisp" name="newskudisp" class="newskudisp"value="" /></td>
<td class='item-sku-cprice'><input type="text" id="newskuunit" name="newskuunit" class='newskuunit' /></td>
<td class='item-sku-price'>
<?php if( defined('WCEX_DLSELLER') ): ?>
	<select id="newcharging_type" name="newcharging_type" class="newcharging_type">
		<option value=""><?php _e('一括課金（即日）','usces'); ?></option>
		<option value="1"><?php _e('月次課金（翌月1日）','usces'); ?></option>
		<option value="2"><?php _e('年次課金（翌月1日）','usces'); ?></option>
	</select>
<?php endif; ?>
</td>
<td class='item-sku-zaikonum'></td>
<td class='item-sku-zaiko'>
<select id="newskugptekiyo" name="newskugptekiyo" class="newskugptekiyo">
    <option value="0"><?php _e('Not apply','usces') ?></option>
    <option value="1"><?php _e('Apply','usces') ?></option>
</select>
</td>
</tr>

<tr>
<td colspan="5" class="submit">
<input name="add_itemsku" type="button" id="add_itemsku" tabindex="9" value="<?php _e('Add SKU','usces') ?>" onclick="if( jQuery('#post_ID').val() < 0 ) return; itemSku.post('additemsku', 0);" />
</td></tr>
</tbody>
</table>
<?php 

}

/**
 * payment_form
 */
function payment_form() {
	global $usces;
?>
<p><strong><?php _e('Add a new method forpayment ','usces') ?> : </strong></p>
<table id="newmeta2">
<thead>
<tr>
<th class="left"><?php _e('A payment method name','usces') ?></th>
<th><?php _e('explanation','usces') ?></th>
<th><?php _e('Type of payment','usces') ?></th>
<th><?php _e('Payment module','usces') ?></th>
</tr>
</thead>

<tbody>
<tr>
<td class='paymentname'><input type="text" id="newname" name="newname" class="paymentname" tabindex="7" value="" /></td>
<td class='paymentexplanation'><textarea id="newexplanation" name="newexplanation" class='paymentexplanation'></textarea></td>
<td class='paymentsettlement'>
	<select name="newsettlement" id="newsettlement" class='paymentsettlement'>
<?php foreach ($usces->payment_structure as $psk => $psv) { ?>
		<option value="<?php echo $psk; ?>"><?php echo $psv; ?></option>
<?php } ?>
	</select>
</td>
<td class='paymentmodule'><input type="text" id="newmodule" name="newmodule" class="paymentmodule" tabindex="9" value="" /></td>
</tr>

<tr><td colspan="2" class="submit">
<input name="add_payment" type="button" id="add_payment" tabindex="9" value="<?php _e('Add a new method forpayment ','usces') ?>" onclick="payment.post('add', 0);" />
</td></tr>
</tbody>
</table>
<?php 

}

//
// Post Meta
//

/**
 * add_item_option_meta
 */
function add_item_option_meta( $post_ID ) {
	global $wpdb;
	
	$post_ID = (int) $post_ID;
	$value = array();
	$protected = array( '#NONE#', '_wp_attached_file', '_wp_attachment_metadata', '_wp_old_slug', '_wp_page_template' );

	$newoptname = isset($_POST['newoptname']) ? stripslashes( trim( $_POST['newoptname'] ) ) : '';
	$newoptmeans = isset($_POST['newoptmeans']) ? $_POST['newoptmeans']: 0;
	$newoptessential = isset($_POST['newoptessential']) ? $_POST['newoptessential']: 0;

	if($newoptmeans == 0 || $newoptmeans == 1){
		$newoptvalue = isset($_POST['newoptvalue']) ? explode('\n', stripslashes( $_POST['newoptvalue'] ) ) : '';
		foreach((array)$newoptvalue as $v){
			if(trim( $v ) != '') 
				$nov[] = trim( $v );
		}
	}else{
		$newoptvalue = isset($_POST['newoptvalue']) ? stripslashes( $_POST['newoptvalue'] ) : '';
		$nov = $newoptvalue;
	}

	if ( ($newoptmeans >= 2 || '0' === $newoptvalue || !empty ( $newoptvalue )) && !empty ( $newoptname) ) {
		// We have a key/value pair. If both the select and the
		// input for the key have data, the input takes precedence:

		if ( $newoptname )
			$metakey = $newoptname; // default

		if ( in_array($metakey, $protected) )
			return false;

		wp_cache_delete($post_ID, 'post_meta');
		
		$metakey = '_iopt_' . $metakey;
		$value['means'] = $newoptmeans;
		$value['essential'] = $newoptessential;
		$value['value'] = $nov;
		$unique = true;
		
		add_post_meta($post_ID, $metakey, $value, $unique);
		
		return $status;
	}
	return false;
} // add_meta

/**
 * add_item_option_meta
 */
function add_item_sku_meta( $post_ID ) {
	global $wpdb;
	
	$post_ID = (int) $post_ID;
	$value = array();
	$protected = array( '_wp_attached_file', '_wp_attachment_metadata', '_wp_old_slug', '_wp_page_template' );

	$newskuname = isset($_POST['newskuname']) ? stripslashes( trim( $_POST['newskuname'] ) ) : '';
	$newskucprice = isset($_POST['newskucprice']) ? stripslashes( $_POST['newskucprice'] ): '';
	$newskuprice = isset($_POST['newskuprice']) ? stripslashes( $_POST['newskuprice'] ): '';
	$newskuzaikonum = isset($_POST['newskuzaikonum']) ? stripslashes( $_POST['newskuzaikonum'] ): '';
	$newskuzaikoselect = isset($_POST['newskuzaikoselect']) ? $_POST['newskuzaikoselect'] : '';
	$newskudisp = isset($_POST['newskudisp']) ? stripslashes( trim( $_POST['newskudisp'] ) ) : '';
	$newskuunit = isset($_POST['newskuunit']) ? stripslashes( trim( $_POST['newskuunit'] ) ) : '';
	$newskugptekiyo = isset($_POST['newskugptekiyo']) ? $_POST['newskugptekiyo'] : '';
	$newcharging_type = isset($_POST['newcharging_type']) ? $_POST['newcharging_type'] : '';


	if ( $newskuname != '' && $newskuprice != '' && $newskuzaikoselect != '') {
		// We have a key/value pair. If both the select and the
		// input for the key have data, the input takes precedence:

		$metakey = $newskuname; // default

		if ( in_array($metakey, $protected) )
			return false;

		wp_cache_delete($post_ID, 'post_meta');
		
		$metakey = '_isku_' . $metakey;
		$value['cprice'] = $newskucprice;
		$value['price'] = $newskuprice;
		$value['zaikonum'] = $newskuzaikonum;
		$value['zaiko'] = $newskuzaikoselect;
		$value['disp'] = $newskudisp;
		$value['unit'] = $newskuunit;
		$value['gptekiyo'] = $newskugptekiyo;
		$value['charging_type'] = $newcharging_type;
		$unique = true;

		add_post_meta($post_ID, $metakey, $value, $unique);
	
//		$id = $wpdb->get_var( $wpdb->prepare("SELECT meta_id FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1", $post_ID, $metakey) );
//
//		if(!$id){
//			$wpdb->query( $wpdb->prepare("INSERT INTO $wpdb->postmeta (post_id,meta_key,meta_value ) VALUES (%d, %s, '%s')", $post_ID, $metakey, $valueserialized) );
//			$id = $wpdb->insert_id;
//		}
		/*else{
			$wpdb->query( $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = %s WHERE post_id = %s AND meta_key = %s", $valueserialized, $post_ID, $metakey) );
		}*/
		return $id;
	}
	return false;
} // add_meta


/**
 * add_payment
 */
function add_payment_method() {
	global $usces;
	
	$protected = array( '#NONE#', '_wp_attached_file', '_wp_attachment_metadata', '_wp_old_slug', '_wp_page_template' );

	$newname = isset($_POST['newname']) ? stripslashes( trim( $_POST['newname'] ) ) : '';
	$newexplanation = isset($_POST['newexplanation']) ? stripslashes( trim( $_POST['newexplanation'] ) ) : '';
	$newsettlement = isset($_POST['newsettlement']) ? $_POST['newsettlement'] : '';
	$newmodule = isset($_POST['newmodule']) ? stripslashes( trim( $_POST['newmodule'] ) ) : '';

	if ( !empty( $newname) ) {
		// We have a key/value pair. If both the select and the
		// input for the key have data, the input takes precedence:

		$usces->options = get_option('usces');
		
		$lid = isset($usces->options['payment_method']) ? count($usces->options['payment_method']) : 0;
		$usces->options['payment_method'][$lid]['name'] = $newname;
		$usces->options['payment_method'][$lid]['explanation'] = $newexplanation;
		$usces->options['payment_method'][$lid]['settlement'] = $newsettlement;
		$usces->options['payment_method'][$lid]['module'] = $newmodule;
		
		update_option('usces', $usces->options);
		
		return;
	}
	return false;
} // add_meta


/**
 * up_item_option_meta
 */
function up_item_option_meta( $post_ID ) {
	global $wpdb;
	
	$post_ID = (int) $post_ID;
	$value = array();
	$protected = array( '_wp_attached_file', '_wp_attachment_metadata', '_wp_old_slug', '_wp_page_template' );

	$optmetaid = isset($_POST['optmetaid']) ? (int)$_POST['optmetaid'] : '';
	$optmeans = isset($_POST['optmeans']) ? $_POST['optmeans']: 0;
	$optessential = isset($_POST['optessential']) ? $_POST['optessential']: 0;

	if($optmeans == 0 || $optmeans == 1){
		$optvalue = isset($_POST['optvalue']) ? explode('\n', stripslashes( $_POST['optvalue'] ) ) : '';
		foreach((array)$optvalue as $v){
			if(trim( $v ) != '') 
				$nov[] = trim( $v );
		}
	}else{
		$optvalue = isset($_POST['optvalue']) ? stripslashes( $_POST['optvalue'] ) : '';
		$nov = $optvalue;
	}

	$value['means'] = $optmeans;
	$value['essential'] = $optessential;
	$value['value'] = $nov;
	$valueserialized = maybe_serialize($value);

	if ( $optmeans >= 2 || '0' === $optvalue || !empty ( $optvalue ) ) {
		// We have a key/value pair. If both the select and the
		// input for the key have data, the input takes precedence:


		wp_cache_delete($post_ID, 'post_meta');
		
		
		$res = $wpdb->query( $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = %s WHERE meta_id = %d", $valueserialized, $optmetaid) );
		return $res;
	}
	return false;
} // update_meta

/**
 * up_item_sku_meta
 */
function up_item_sku_meta( $post_ID ) {
	global $wpdb;
	
	$post_ID = (int) $post_ID;
	$value = array();
	$protected = array( '_wp_attached_file', '_wp_attachment_metadata', '_wp_old_slug', '_wp_page_template' );

	$skuname = isset($_POST['skuname']) ? stripslashes( $_POST['skuname'] ) : '';
	$skumetaid = isset($_POST['skumetaid']) ? (int)$_POST['skumetaid'] : '';
	$skucprice = isset($_POST['skucprice']) ? stripslashes( $_POST['skucprice'] ): 0;
	$skuprice = isset($_POST['skuprice']) ? stripslashes( $_POST['skuprice'] ): 0;
	$skuzaikonum = isset($_POST['skuzaikonum']) ? stripslashes( $_POST['skuzaikonum'] ): 0;
	$skuzaiko = isset($_POST['skuzaiko']) ? (int)$_POST['skuzaiko'] : '';
	$skudisp = isset($_POST['skudisp']) ? stripslashes( $_POST['skudisp'] ): '';
	$skuunit = isset($_POST['skuunit']) ? stripslashes( $_POST['skuunit'] ): '';
	$skugptekiyo = isset($_POST['skugptekiyo']) ? (int)$_POST['skugptekiyo'] : 0;
	$charging_type = isset($_POST['charging_type']) ? (int)$_POST['charging_type'] : 0;

	$value['cprice'] = $skucprice;
	$value['price'] = $skuprice;
	$value['zaikonum'] = $skuzaikonum;
	$value['zaiko'] = $skuzaiko;
	$value['disp'] = $skudisp;
	$value['unit'] = $skuunit;
	$value['gptekiyo'] = $skugptekiyo;
	$value['charging_type'] = $charging_type;
	$valueserialized = maybe_serialize($value);

	if ( $skumetaid != '' && $skuname != '' ) {

		wp_cache_delete($post_ID, 'post_meta');
		
		$metakey = '_isku_' . $skuname;
		
		$res = $wpdb->query( $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_key = %s, meta_value = %s WHERE meta_id = %d", $metakey, $valueserialized, $skumetaid) );
		return $res;
	}
	return false;
} // update_meta


/**
 * update_payment
 */
function up_payment_method() {
	global $usces;
	
	$protected = array( '_wp_attached_file', '_wp_attachment_metadata', '_wp_old_slug', '_wp_page_template' );

	//$id = isset($_POST['id']) ? (int)$_POST['id'] : '';
	$id = $_POST['id'];
	$name = isset($_POST['name']) ? stripslashes( trim( $_POST['name'] ) ) : '';
	$explanation = isset($_POST['explanation']) ? stripslashes( trim( $_POST['explanation'] ) ) : '';
	$settlement = isset($_POST['settlement']) ? $_POST['settlement'] : '';
	$module = isset($_POST['module']) ? stripslashes( trim( $_POST['module'] ) ) : '';

	if ( !empty( $name) && $id != '') {
		// We have a key/value pair. If both the select and the
		// input for the key have data, the input takes precedence:

		$usces->options = get_option('usces');
		
		$usces->options['payment_method'][$id]['name'] = $name;
		$usces->options['payment_method'][$id]['explanation'] = $explanation;
		$usces->options['payment_method'][$id]['settlement'] = $settlement;
		$usces->options['payment_method'][$id]['module'] = $module;
		
		update_option('usces', $usces->options);
		
		return;
	}
	return false;
} // add_meta

/**
 * del_item_option_meta
 */
function del_item_option_meta( $post_ID ) {
	global $wpdb;
	
	$post_ID = (int) $post_ID;
	$optmetaid = isset($_POST['optmetaid']) ? (int)$_POST['optmetaid'] : '';

	wp_cache_delete($post_ID, 'post_meta');
		
	$res = $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_id = %d", $optmetaid) );
	return $res;
} // delete_meta

/**
 * del_item_sku_meta
 */
function del_item_sku_meta( $post_ID ) {
	global $wpdb;
	
	$post_ID = (int) $post_ID;
	$skumetaid = isset($_POST['skumetaid']) ? (int)$_POST['skumetaid'] : '';

	wp_cache_delete($post_ID, 'post_meta');
		
	$res = $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_id = %d", $skumetaid) );
	return $res;
} // delete_meta


/**
 * delete_payment
 */
function del_payment_method() {
	global $usces;
	
	$protected = array( '#NONE#', '_wp_attached_file', '_wp_attachment_metadata', '_wp_old_slug', '_wp_page_template' );

	//$id = (isset($_POST['id']) && (int)$_POST['id'] >= 0 ) ? (int)$_POST['id'] : '';
	$id = $_POST['id'];

	if ( $id != '' ) {
		// We have a key/value pair. If both the select and the
		// input for the key have data, the input takes precedence:

		$usces->options = get_option('usces');
		
		array_splice($usces->options['payment_method'], $id, 1);
		
		update_option('usces', $usces->options);
		
		return;
	}
	return false;
} // add_meta

function select_common_option( $post_ID ) {
	global $wpdb;
	
	$key = isset($_POST['key']) ? '_iopt_' . stripslashes( $_POST['key'] ) : '';
	if(!$post_ID || !$key) return ;
	
	$meta_value = $wpdb->get_var( $wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %s AND meta_key = '%s' ORDER BY meta_id", $post_ID, $key) );
	if ( is_serialized( $meta_value ) ) {
		$array = maybe_unserialize( $meta_value );
	} else {
		return;
	}
	
	$means = $array['means'];
	$essential = $array['essential'];
	$value = '';
	if($means < 2){
		foreach($array['value'] as $k => $v){
			$value .= htmlspecialchars($v) . "\n";
		}
	}else{
			$value .= htmlspecialchars($array['value']) . "\n";
	}
	$res = $means . $essential . $value;
	return $res;
} // select_common_option

function select_item_sku( $post_ID ) {
	global $wpdb;
	
	$key = isset($_POST['key']) ? '_isku_' . stripslashes( $_POST['key'] ) : '';
	if(!$key) return ;
	
	$meta_value = $wpdb->get_var( $wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '%s' GROUP BY meta_key LIMIT 1", $key) );
	if ( is_serialized( $meta_value ) ) {
		$array = maybe_unserialize( $meta_value );
	} else {
		return;
	}
	
	$cprice = $array['cprice'];
	$price = $array['price'];
	return $price.'#usces#'.$cprice;
} // select_item_sku


/**
 * item sku list
 */
function has_item_sku_list() {
	global $wpdb;

	$meta = $wpdb->get_col( $wpdb->prepare("SELECT meta_key 
			FROM $wpdb->postmeta WHERE meta_key LIKE '%s' 
			GROUP BY meta_key", '_isku_%'));
			
	$r = "\t<option value='#NONE#'>" . __('- Select --','usces') . "</option>\n";
	foreach ( $meta as $key ){
		$key = substr($key, 6);
		$r .= "\t<option value='{$key}'>{$key}</option>\n";
	}
	
	return $r;
}

function add_delivery_method() {
	$options = get_option('usces');
	$name = htmlspecialchars($_POST['name']);
	foreach((array)$options['delivery_method'] as $deli){
		$ids[] = (int)$deli['id'];
	}
	if(isset($ids)){
		rsort($ids);
		$newid = $ids[0]+1;
	}else{
		$newid = 0;
	}
	$index = isset($options['delivery_method']) ? count($options['delivery_method']) : 0;
	$options['delivery_method'][$index]['id'] = $newid;
	$options['delivery_method'][$index]['name'] = $name;
	$options['delivery_method'][$index]['time'] = str_replace("\r\n", "\n", $_POST['time']);
	$options['delivery_method'][$index]['time'] = str_replace("\r", "\n", $options['delivery_method'][$index]['time']);
	$options['delivery_method'][$index]['charge'] = (int)$_POST['charge'];
	update_option('usces', $options);
	
	$res = $newid . '#usces#' . $name . '#usces#' . $options['delivery_method'][$index]['time'] . '#usces#' . $options['delivery_method'][$index]['charge'];
	return $res;
}

function update_delivery_method() {
	$options = get_option('usces');
	$name = $_POST['name'];
	$id = (int)$_POST['id'];
	$charge = (int)$_POST['charge'];
	for($i=0; $i<count($options['delivery_method']); $i++){
		if($options['delivery_method'][$i]['id'] === $id){
			$index = $i;
		}
	}
	$options['delivery_method'][$index]['name'] = $name;
	$options['delivery_method'][$index]['charge'] = $charge;
	$options['delivery_method'][$index]['time'] = str_replace("\r\n", "\n", $_POST['time']);
	$options['delivery_method'][$index]['time'] = str_replace("\r", "\n", $options['delivery_method'][$index]['time']);
	update_option('usces', $options);
	
	$res = $id . '#usces#' . $name . '#usces#' . $options['delivery_method'][$index]['time'] . '#usces#' . $options['delivery_method'][$index]['charge'];
	return $res;
}

function delete_delivery_method() {
	$options = get_option('usces');
	$id = (int)$_POST['id'];
	for($i=0; $i<count($options['delivery_method']); $i++){
		if($options['delivery_method'][$i]['id'] === $id){
			$index = $i;
		}
	}
	array_splice($options['delivery_method'], $index, 1);
	update_option('usces', $options);
	
	$res = $id . '#usces#0';
	return $res;
}

function moveup_delivery_method() {
	$options = get_option('usces');
	$selected_id = (int)$_POST['id'];
	$ct = count($options['delivery_method']);
	for($i=0; $i<$ct; $i++){
		if($options['delivery_method'][$i]['id'] === $selected_id){
			$index = $i;
		}
	}
	if($index !== 0) {
		$from_index = $index;
		$to_index = $index - 1;
		$from_dm = $options['delivery_method'][$from_index];
		$to_dm = $options['delivery_method'][$to_index];
		for($i=0; $i<$ct; $i++){
			if($i === $to_index){
				$options['delivery_method'][$i] = $from_dm;
			}else if($i === $from_index){
				$options['delivery_method'][$i] = $to_dm;
			}
		}
		update_option('usces', $options);
	}
	
	$id = '';
	$name = '';
	$charge = '';
	$time = '';
	for($i=0; $i<$ct; $i++){
		$id .= $options['delivery_method'][$i]['id'] . ',';
		$name .= $options['delivery_method'][$i]['name'] . ',';
		$charge .= $options['delivery_method'][$i]['charge'] . ',';
		$time .= $options['delivery_method'][$i]['time'] . ',';
	}
	$id = rtrim($id,',');
	$name = rtrim($name,',');
	$charge = rtrim($charge,',');
	$time = rtrim($time,',');

	
	$res = $id . '#usces#' . $name . '#usces#' . $time . '#usces#' . $charge . '#usces#' . $selected_id;
	return $res;
}

function movedown_delivery_method() {
	$options = get_option('usces');
	$selected_id = (int)$_POST['id'];
	$ct = count($options['delivery_method']);
	for($i=0; $i<$ct; $i++){
		if($options['delivery_method'][$i]['id'] === $selected_id){
			$index = $i;
		}
	}
	if($index < $ct-1) {
		$from_index = $index;
		$to_index = $index + 1;
		$from_dm = $options['delivery_method'][$from_index];
		$to_dm = $options['delivery_method'][$to_index];
		for($i=0; $i<$ct; $i++){
			if($i === $to_index){
				$options['delivery_method'][$i] = $from_dm;
			}else if($i === $from_index){
				$options['delivery_method'][$i] = $to_dm;
			}
		}
		update_option('usces', $options);
	}
	
	$id = '';
	$name = '';
	$charge = '';
	$time = '';
	for($i=0; $i<$ct; $i++){
		$id .= $options['delivery_method'][$i]['id'] . ',';
		$name .= $options['delivery_method'][$i]['name'] . ',';
		$charge .= $options['delivery_method'][$i]['charge'] . ',';
		$time .= $options['delivery_method'][$i]['time'] . ',';
	}
	$id = rtrim($id,',');
	$name = rtrim($name,',');
	$charge = rtrim($charge,',');
	$time = rtrim($time,',');

	
	$res = $id . '#usces#' . $name . '#usces#' . $time . '#usces#' . $charge . '#usces#' . $selected_id;
	return $res;
}

function add_shipping_charge() {
	global $usces;

	$options = get_option('usces');
	$name = htmlspecialchars($_POST['name']);
	$value = $_POST['value'];
	foreach((array)$options['shipping_charge'] as $charge){
		$ids[] = (int)$charge['id'];
	}
	if(isset($ids)){
		rsort($ids);
		$newid = $ids[0]+1;
	}else{
		$newid = 0;
	}
	$index = isset($options['shipping_charge']) ? count($options['shipping_charge']) : 0;
//	$prefs = get_option('usces_pref');
	$prefs = $usces->options['province'];
	array_shift($prefs);

	$options['shipping_charge'][$index]['id'] = $newid;
	$options['shipping_charge'][$index]['name'] = $name;
	for($i=0; $i<count($prefs); $i++){
		$pref = $prefs[$i];
		$options['shipping_charge'][$index]['value'][$pref] = (int)$value[$i];
	}
	update_option('usces', $options);

	$valuestr = implode(',', $options['shipping_charge'][$index]['value']);
	$res = $newid . '#usces#' . $name . '#usces#' . $valuestr;
	return $res;
}

function update_shipping_charge() {
	global $usces;
	$options = get_option('usces');
	$name = htmlspecialchars($_POST['name']);
	$value = $_POST['value'];
	$id = (int)$_POST['id'];
//	$prefs = get_option('usces_pref');
	$prefs = $usces->options['province'];
	array_shift($prefs);

	for($i=0; $i<count($options['shipping_charge']); $i++){
		if($options['shipping_charge'][$i]['id'] === $id){
			$index = $i;
		}
	}
	$options['shipping_charge'][$index]["name"] = $name;
	for($i=0; $i<count($prefs); $i++){
		$pref = $prefs[$i];
		$options['shipping_charge'][$index]["value"][$pref] = (int)$value[$i];
	}
	update_option('usces', $options);

	$valuestr = implode(',', $options['shipping_charge'][$index]["value"]);
	$res = $id . '#usces#' . $name . '#usces#' . $valuestr;
	return $res;
}

function delete_shipping_charge() {
	$options = get_option('usces');
	$id = (int)$_POST['id'];
	for($i=0; $i<count($options['shipping_charge']); $i++){
		if($options['shipping_charge'][$i]['id'] === $id){
			$index = $i;
		}
	}
	array_splice($options['shipping_charge'], $index, 1);
	update_option('usces', $options);
	
	$res = $id . '#usces#0';
	return $res;
}

/************************************************************************************************/
function shop_options_ajax()
{
	global $usces;

	if( $_POST['action'] != 'shop_options_ajax' ) die(0);
	
	switch ($_POST['mode']) {
		case 'add_delivery_method':
			$res = add_delivery_method();
			break;
		case 'update_delivery_method':
			$res = update_delivery_method();
			break;
		case 'delete_delivery_method':
			$res = delete_delivery_method();
			break;
		case 'moveup_delivery_method':
			$res = moveup_delivery_method();
			break;
		case 'movedown_delivery_method':
			$res = movedown_delivery_method();
			break;
		case 'add_shipping_charge':
			$res = add_shipping_charge();
			break;
		case 'update_shipping_charge':
			$res = update_shipping_charge();
			break;
		case 'delete_shipping_charge':
			$res = delete_shipping_charge();
			break;
	}
	
	die( $res );
} 

function payment_ajax()
{
	global $usces;

	if( $_POST['action'] != 'payment_ajax' ) die(0);
	
	if(isset($_POST['update'])){
		$res = up_payment_method();
		
	}else if(isset($_POST['delete'])){
		$res = del_payment_method();
		
	}else{
		$res = add_payment_method();
		
	}
		
	$meta = $usces->options['payment_method'];
	
	$r = '';
	foreach ( $meta as $key => $entry )
		$r .= _payment_list_row( $key, $entry );
	
	die( $r );
} 

function order_item2cart_ajax()
{

	if( $_POST['action'] != 'order_item2cart_ajax' ) die(0);
	
	$res = order_item2cart();
	
	if( $res === false )  die(0);
		
	//REGEX BUG: but it'll return info
	// Compose JavaScript for return
	die( $res );
} 

function order_item_ajax()
{

	if( $_POST['action'] != 'order_item_ajax' ) die(0);
	
	switch ( $_POST['mode'] ) {
		case 'completionMail':
		case 'orderConfirmMail':
		case 'changeConfirmMail':
		case 'receiptConfirmMail':
		case 'mitumoriConfirmMail':
		case 'cancelConfirmMail':
		case 'otherConfirmMail':
			$res = usces_order_confirm_message( $_POST['order_id'] );
			break;
		case 'sendmail':
			$res = usces_ajax_send_mail();
			break;
		case 'get_order_item':
			$res = get_order_item( $_POST['itemcode'] );
			break;
		case 'ordercheckpost':
			$res = usces_update_ordercheck();
			break;
	}
	
	if( $res === false )  die(0);
		
	//REGEX BUG: but it'll return info
	// Compose JavaScript for return
	die( $res );
} 

/**
 * order Item html
 */
function order_item2cart() {
	global $usces;

	if( $_POST['action'] != 'order_item2cart_ajax' ) die(0);

	$res = usces_update_ordercart();

	if( $res === false )  die(0);
	if( $res === 0 )  die('nodata');
		
	die( $res );
}

function get_order_item( $item_code ) {
	global $usces;
	
	$post_id = $usces->get_postIDbyCode( $item_code );
	if( $post_id == NULL ) return false;
	
	$pict_id = $usces->get_pictids( $item_code );
	$pict_link = wp_get_attachment_link($pict_id[0], array(200, 100), false);
	preg_match("/^\<a .+\>(\<img .+\/\>)\<\/a\>$/", $pict_link, $match);
	$pict = $match[1];
	$skus = $usces->get_skus( $post_id );
	$optkeys = $usces->get_itemOptionKey( $post_id );
	$itemName = $usces->getItemName($post_id);
	
	$r = '';
	$r .= $pict . "\n";
	$r .= "<h3>" . $itemName . "</h3>\n";
	$r .= "<div class='skuform'>\n";


	$r .= "<table class='skumulti'>\n";
	$r .= "<thead>\n";
	$r .= "<tr>\n";
	$r .= "<th>" . __('order number','usces') . "</th>\n";
	$r .= "<th>" . __('title','usces') . "</th>\n";
	$usces_listprice = __('List price', 'usces') . $usces->getGuidTax();
	$r .= "<th>" . apply_filters('usces_filter_listprice_label', $usces_listprice, __('List price', 'usces'), $usces->getGuidTax()) . "</th>\n";
	$usces_sellingprice = __('Sale price','usces') . $usces->getGuidTax();
	$r .= "<th>" . apply_filters('usces_filter_sellingprice_label', $usces_sellingprice, __('Sale price', 'usces'), $usces->getGuidTax()) . "</th>\n";
	$r .= "<th>" . __('stock','usces') . "</th>\n";
	$r .= "<th>" . __('stock','usces') . "</th>\n";
	$r .= "<th>" . __('unit','usces') . "</th>\n";
	$r .= "<th>&nbsp;</th>\n";
	$r .= "</tr>\n";
	$r .= "</thead>\n";
	$r .= "<tbody>\n";
	for ($i=0; $i<count($skus['key']); $i++) :
		$sku = $skus['key'][$i];
		$cprice = $skus['cprice'][$i];
		$price = $skus['price'][$i];
		$zaiko = $usces->zaiko_status[$skus['zaiko'][$i]];
		$zaikonum = $skus['zaikonum'][$i];
		$disp = $skus['disp'][$i];
		$unit = $skus['unit'][$i];
		$gptekiyo = $skus['gptekiyo'][$i];
		$r .= "<tr>\n";
		$r .= "<td rowspan='2'>" . $sku . "</td>\n";
		$r .= "<td>" . $disp . "</td>\n";
		$r .= "<td><span class='cprice'>" . __('$', 'usces') . $cprice . "</span></td>\n";
		$r .= "<td><span class='price'>" . __('$', 'usces') . $price . "</span></td>\n";
		$r .= "<td>" . $zaiko . "</td>\n";
		$r .= "<td>" . $zaikonum . "</td>\n";
//			$r .= "<td>" . usces_the_itemQuant() . "</td>\n";
		$r .= "<td>" . $unit . "</td>\n";
		$r .= "<td>\n";
	$r .= "<input name=\"itemNEWName[{$post_id}][{$sku}]\" type=\"hidden\" id=\"itemNEWName[{$post_id}][{$sku}]\" value=\"{$itemName}\" />\n";
	$r .= "<input name=\"itemNEWCode[{$post_id}][{$sku}]\" type=\"hidden\" id=\"itemNEWCode[{$post_id}][{$sku}]\" value=\"{$item_code}\" />\n";
	$r .= "<input name=\"skuNEWName[{$post_id}][{$sku}]\" type=\"hidden\" id=\"skuNEWName[{$post_id}][{$sku}]\" value=\"{$sku}\" />\n";
	$r .= "<input name=\"skuNEWCprice[{$post_id}][{$sku}]\" type=\"hidden\" id=\"skuNEWCprice[{$post_id}][{$sku}]\" value=\"{$cprice}\" />\n";
	$r .= "<input name=\"skuNEWDisp[{$post_id}][{$sku}]\" type=\"hidden\" id=\"skuNEWDisp[{$post_id}][{$sku}]\" value=\"{$disp}\" />\n";
	$r .= "<input name=\"zaikoNEWnum[{$post_id}][{$sku}]\" type=\"hidden\" id=\"zaikoNEWnum[{$post_id}][{$sku}]\" value=\"{$zaikonum}\" />\n";
	$r .= "<input name=\"zaiNEWko[{$post_id}][{$sku}]\" type=\"hidden\" id=\"zaiNEWko[{$post_id}][{$sku}]\" value=\"{$zaiko}\" />\n";
	$r .= "<input name=\"uniNEWt[{$post_id}][{$sku}]\" type=\"hidden\" id=\"uniNEWt[{$post_id}][{$sku}]\" value=\"{$unit}\" />\n";
	$r .= "<input name=\"gpNEWtekiyo[{$post_id}][{$sku}]\" type=\"hidden\" id=\"gpNEWtekiyo[{$post_id}][{$sku}]\" value=\"{$gptekiyo}\" />\n";
	$r .= "<input name=\"skuNEWPrice[{$post_id}][{$sku}]\" type=\"hidden\" id=\"skuNEWPrice[{$post_id}][{$sku}]\" value=\"{$price}\" />\n";
	$r .= "<input name=\"inNEWCart[{$post_id}][{$sku}]\" type=\"button\" id=\"inNEWCart[{$post_id}][{$sku}]\" class=\"skubutton\" value=\"" . __('Add to Whish List','usces') . "\" onclick=\"orderItem.add2cart('{$post_id}', '{$sku}');\" />";
		$r .= "</td>\n";
		$r .= "</tr>\n";
	$r .= "<tr>\n";
	if ( 0 < count($optkeys) ) :
		$r .= "<td colspan='7'>\n";
		foreach ($optkeys as $optkey) :
			
			$key = '_iopt_' . $optkey;
			$value = get_post_custom_values($key, $post_id);
			if(!$value) continue;
			$values = maybe_unserialize($value[0]);
			$means = (int)$values['means'][0];
			$essential = (int)$values['essential'][0];
			$selects = explode("\n", $values['value'][0]);
			$multiple = ($means === 0) ? '' : ' multiple';
			
			$r .= "\n<label for='itemNEWOption[{$post_id}][{$sku}][{$optkey}]' class='iopt_label'>{$optkey}</label>\n";
			$r .= "\n<select name='itemNEWOption[{$post_id}][{$sku}][{$optkey}]' id='itemNEWOption[{$post_id}][{$sku}][{$optkey}]' class='iopt_select'{$multiple}>\n";
			if($essential == 1)
				$r .= "\t<option value='#NONE#' selected='selected'>" . __('Choose','usces') . "</option>\n";
			$i=0;
			foreach($selects as $v) {
				if($i == 0 && $essential == 0) 
					$selected = ' selected="selected"';
				else
					$selected = '';
				$r .= "\t<option value='{$v}'{$selected}>{$v}</option>\n";
				$i++;
			}
			$r .= "</select>\n";
			$r .= "<input name=\"optNEWName[{$post_id}][{$sku}][{$optkey}]\" type=\"hidden\" id=\"optNEWName[{$post_id}][{$sku}][{$optkey}]\" value=\"{$optkey}\" />\n";
			
		endforeach;
		$r .= "</td>\n";
	endif;
	$r .= "</tr>\n";
	endfor;
	$r .= "</tbody>\n";
	$r .= "</table>\n";


	$r .= "</div>\n";

	return $r;
}

function item_option_ajax()
{

	if( $_POST['action'] != 'item_option_ajax' ) die(0);
	
	if(isset($_POST['update'])){
		$res = up_item_option_meta( $_POST['ID'] );
		
	}else if(isset($_POST['delete'])){
		$res = del_item_option_meta( $_POST['ID'] );
		
	}else if(isset($_POST['select'])){
		$res = select_common_option( $_POST['ID'] );
		die( $res );
		
	}else{
		$res = add_item_option_meta( $_POST['ID'] );
		
	}
		
	$meta = has_item_option_meta( $_POST['ID'] );
	
	$r = '';
	foreach ( $meta as $entry )
		$r .= _list_item_option_meta_row( $entry );
	
	//REGEX BUG: but it'll return info
	// Compose JavaScript for return
	die( $r );
} 

function item_sku_ajax()
{

	if( $_POST['action'] != 'item_sku_ajax' ) die(0);
	
	if(isset($_POST['update'])){
		$res = up_item_sku_meta( $_POST['ID'] );
		
	}else if(isset($_POST['delete'])){
		$res = del_item_sku_meta( $_POST['ID'] );
		
	}else if(isset($_POST['select'])){
		$res = select_item_sku( $_POST['ID'] );
		die( $res );
		
	}else{
		$res = add_item_sku_meta( $_POST['ID'] );
		
	}
		
	$meta = has_item_sku_meta( $_POST['ID'] );
	
	$r = '';
	foreach ( (array)$meta as $entry )
		$r .= _list_item_sku_meta_row( $entry );
	
	$list = has_item_sku_list();
	
	$res = $r . '#usces#' . $list;
	
	die( $res );
}

function item_save_metadata() {

	global $usces;
	
	$post_id = $_POST['post_ID'];
	if( $post_id < 0 ) return $post_id;

//  if ( !wp_verify_nonce( $_POST['itemName_nonce'], 'itemName_nonce' ) 
//  	|| !wp_verify_nonce( $_POST['itemCode_nonce'], 'itemCode_nonce' )) {
//      return $post_id;
//  }

  if ( 'page' == $_POST['post_type'] ) {
      return $post_id;
  } else {
    if ( !current_user_can( 'edit_post', $post_id ))
      return $post_id;
  }

	if(isset($_POST['itemName'])){
		$itemName = wp_specialchars($_POST['itemName']);
		update_post_meta($post_id, '_itemName', $itemName);
		$usces->set_item_mime($post_id, 'item');
	}
	if(isset($_POST['itemCode'])){
		$itemCode = wp_specialchars($_POST['itemCode']);
		update_post_meta($post_id, '_itemCode', $itemCode);
	}
	if(isset($_POST['itemRestriction'])){
		$itemRestriction = wp_specialchars($_POST['itemRestriction']);
		update_post_meta($post_id, '_itemRestriction', $itemRestriction);
	}
	if(isset($_POST['itemPointrate'])){
		$itemPointrate = (int)$_POST['itemPointrate'];
		update_post_meta($post_id, '_itemPointrate', $itemPointrate);
	}
	if(isset($_POST['itemGpNum1'])){
		$itemGpNum1 = (int)$_POST['itemGpNum1'];
		update_post_meta($post_id, '_itemGpNum1', $itemGpNum1);
	}
	if(isset($_POST['itemGpNum2'])){
		$itemGpNum2 = (int)$_POST['itemGpNum2'];
		update_post_meta($post_id, '_itemGpNum2', $itemGpNum2);
	}
	if(isset($_POST['itemGpNum3'])){
		$itemGpNum3 = (int)$_POST['itemGpNum3'];
		update_post_meta($post_id, '_itemGpNum3', $itemGpNum3);
	}
	if(isset($_POST['itemGpDis1'])){
		$itemGpDis1 = (int)$_POST['itemGpDis1'];
		update_post_meta($post_id, '_itemGpDis1', $itemGpDis1);
	}
	if(isset($_POST['itemGpDis2'])){
		$itemGpDis2 = (int)$_POST['itemGpDis2'];
		update_post_meta($post_id, '_itemGpDis2', $itemGpDis2);
	}
	if(isset($_POST['itemGpDis3'])){
		$itemGpDis3 = (int)$_POST['itemGpDis3'];
		update_post_meta($post_id, '_itemGpDis3', $itemGpDis3);
	}
	
	if(isset($_POST['itemShipping'])){
		$itemShipping = wp_specialchars($_POST['itemShipping']);
		update_post_meta($post_id, '_itemShipping', $itemShipping);
	}
	if(isset($_POST['itemDeliveryMethod'])){
		$itemDeliveryMethod = array();
		foreach( (array)$_POST["itemDeliveryMethod"] as $dmid){ 
				$itemDeliveryMethod[] = $dmid;
		} 
		update_post_meta($post_id, '_itemDeliveryMethod', $itemDeliveryMethod);
	}
	if(isset($_POST['itemShippingCharge'])){
		$itemShippingCharge = wp_specialchars($_POST['itemShippingCharge']);
		update_post_meta($post_id, '_itemShippingCharge', $itemShippingCharge);
	}
	$itemIndividualSCharge = isset($_POST['itemIndividualSCharge']) ? 1 : 0;
	update_post_meta($post_id, '_itemIndividualSCharge', $itemIndividualSCharge);
	
	if(isset($_POST['wcexp'])){
		$wcexp = serialize($_POST['wcexp']);
		update_post_meta($post_id, '_wcexp', $wcexp);
	}

	
   return ;
}

function usces_link_replace($para) {
	//$str = 'admin.php?page=' . USCES_PLUGIN_BASENAME . '&';
	$str = 'admin.php?page=usces_itemedit&';
	$url = preg_replace('|post\.php\?|i', $str, $para);
	return $url;

}

function usces_count_posts( $type = 'post', $perm = '' ) {
	global $wpdb;

	$user = wp_get_current_user();

	$cache_key = $type;

	$query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s AND post_mime_type = 'item'";
	//$query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s";
	if ( 'readable' == $perm && is_user_logged_in() ) {
		if ( !current_user_can("read_private_{$type}s") ) {
			$cache_key .= '_' . $perm . '_' . $user->ID;
			$query .= " AND (post_status != 'private' OR ( post_author = '$user->ID' AND post_status = 'private' ))";
		}
	}
	$query .= ' GROUP BY post_status';

	$count = wp_cache_get($cache_key, 'counts');
	if ( false !== $count )
	//	return $count;

	$count = $wpdb->get_results( $wpdb->prepare( $query, $type ), ARRAY_A );

	$stats = array( );
	foreach( (array) $count as $row_num => $row ) {
		$stats[$row['post_status']] = $row['num_posts'];
	}

	$stats = (object) $stats;
	wp_cache_set($cache_key, $stats, 'counts');

	return $stats;
}

//20100809ysk start
/**
 * custom order meta row
 */
function _list_custom_order_meta_row($key, $entry) {
	$r = '';
	$style = '';

	$name = $entry['name'];
	$means = get_option('usces_custom_order_select');
	$meansoption = '';
	foreach($means as $meankey => $meanvalue) {
		if($meankey == $entry['means']) {
			$selected = ' selected="selected"';
		} else {
			$selected = '';
		}
		$meansoption .= '<option value="'.$meankey.'"'.$selected.'>'.$meanvalue."</option>\n";
	}
	$essential = $entry['essential'] == 1 ? " checked='checked'" : "";
	$value = '';
	if(is_array($entry['value'])) {
		foreach($entry['value'] as $k => $v) {
			$value .= htmlspecialchars($v)."\n";
		}
	}
	$value = trim($value);

	$r .= "\n\t<tr id='csod-{$key}' class='{$style}'>";
	$r .= "\n\t\t<td class='left'><div><input type='text' name='csod[{$key}][key]' id='csod[{$key}][key]' class='optname' size='20' value='{$key}' readonly /></div>";
	$r .= "\n\t\t<div><input type='text' name='csod[{$key}][name]' id='csod[{$key}][name]' class='optname' size='20' value='{$name}' /></div>";
	$r .= "\n\t\t<div class='optcheck'><select name='csod[{$key}][means]' id='csod[{$key}][means]'>".$meansoption."</select>\n";
	$r .= "<input type='checkbox' name='csod[{$key}][essential]' id='csod[{$key}][essential]' value='1'{$essential} /><label for='csod[{$key}][essential]'>".__('Required','usces')."</label></div>";
//20100818ysk start
	//$r .= "\n\t\t<div class='submit'><input type='button' name='deletecsod[{$key}]' id='deletecsod[{$key}]' value='".attribute_escape(__( 'Delete' ))."' onclick='customOrder.del(\"{$key}\");' />";
	//$r .= "\n\t\t<input type='button' name='updatecsod' id='updatecsod[{$key}]' value='".attribute_escape(__( 'Update' ))."' onclick='customOrder.upd(\"{$key}\");' /></div>";
	$r .= "\n\t\t<div class='submit'><input type='button' name='del_csod[{$key}]' id='del_csod[{$key}]' value='".attribute_escape(__( 'Delete' ))."' onclick='customField.delOrder(\"{$key}\");' />";
	$r .= "\n\t\t<input type='button' name='upd_csod[{$key}]' id='upd_csod[{$key}]' value='".attribute_escape(__( 'Update' ))."' onclick='customField.updOrder(\"{$key}\");' /></div>";
//20100818ysk end
	$r .= "</td>";
	$r .= "\n\t\t<td class='item-opt-value'><textarea name='csod[{$key}][value]' id='csod[{$key}][value]' class='optvalue'>{$value}</textarea></td>\n\t</tr>";
	return $r;
}
//20100809ysk end

//20100818ysk start
/**
 * has custom field meta
 */
function usces_has_custom_field_meta($fieldname) {
	switch($fieldname) {
	case 'order':
		$field = 'usces_custom_order_field';
		break;
	case 'customer':
		$field = 'usces_custom_customer_field';
		break;
	case 'delivery':
		$field = 'usces_custom_delivery_field';
		break;
	case 'member':
		$field = 'usces_custom_member_field';
		break;
	default:
		return array();
	}
	$fields = get_option($fieldname);
	$meta = ($fields) ? unserialize($fields) : array();
	return $meta;
}

/**
 * custom field ajax
 */
function custom_field_ajax() {

	if($_POST['action'] != 'custom_field_ajax') die(0);
	switch($_POST['field']) {
	case 'order':
		$field = 'usces_custom_order_field';
		break;
	case 'customer':
		$field = 'usces_custom_customer_field';
		break;
	case 'delivery':
		$field = 'usces_custom_delivery_field';
		break;
	case 'member':
		$field = 'usces_custom_member_field';
		break;
	default:
		die(0);
	}

	$meta = usces_has_custom_field_meta($field);

	if(isset($_POST['add'])) {
		$newkey = isset($_POST['newkey']) ? stripslashes(trim($_POST['newkey'])) : '';
		$newname = isset($_POST['newname']) ? stripslashes(trim($_POST['newname'])) : '';
		$newmeans = isset($_POST['newmeans']) ? $_POST['newmeans'] : 0;
		$newessential = isset($_POST['newessential']) ? $_POST['newessential'] : 0;
		$newposition = isset($_POST['newposition']) ? $_POST['newposition'] : '';

		if($newmeans == 2) {//Text
			$newvalue = isset($_POST['newvalue']) ? stripslashes($_POST['newvalue']) : '';
			$nv = $newvalue;

		} else {
			$newvalue = isset($_POST['newvalue']) ? explode('\n', stripslashes($_POST['newvalue'])) : '';
			foreach((array)$newvalue as $v) {
				if(trim($v) != '') 
					$nv[] = trim($v);
			}
		}

		if(!array_key_exists($newkey, $meta)) {
			if(($newmeans >= 2 || '0' === $newvalue || !empty($newvalue)) && !empty($newkey) && !empty($newname)) {
				$meta[$newkey]['name'] = $newname;
				$meta[$newkey]['means'] = $newmeans;
				$meta[$newkey]['essential'] = $newessential;
				$meta[$newkey]['value'] = $nv;
				if($newposition != '') $meta[$newkey]['position'] = $newposition;
				update_option($field, serialize($meta));
			}
		}

	} elseif(isset($_POST['update'])) {
		$key = isset($_POST['key']) ? stripslashes(trim($_POST['key'])) : '';
		$name = isset($_POST['name']) ? stripslashes(trim($_POST['name'])) : '';
		$means = isset($_POST['means']) ? $_POST['means'] : 0;
		$essential = isset($_POST['essential']) ? $_POST['essential'] : 0;
		$position = isset($_POST['position']) ? $_POST['position'] : '';

		if($means == 2) {//Text
			$value = isset($_POST['value']) ? stripslashes($_POST['value']) : '';
			$nv = $value;

		} else {
			$value = isset($_POST['value']) ? explode('\n', stripslashes($_POST['value'])) : '';
			foreach((array)$value as $v) {
				if(trim($v) != '') 
					$nv[] = trim($v);
			}
		}

		if($means >= 2 || '0' === $value || !empty($value)) {
			$meta[$key]['name'] = $name;
			$meta[$key]['means'] = $means;
			$meta[$key]['essential'] = $essential;
			$meta[$key]['value'] = $nv;
			if($position != '') $meta[$key]['position'] = $position;
			update_option($field, serialize($meta));
		}

	} elseif(isset($_POST['delete'])) {
		$key = isset($_POST['key']) ? $_POST['key'] : '';
		unset($meta[$key]);
		update_option($field, serialize($meta));
	}

	$r = '';
	switch($_POST['field']) {
	case 'order':
		foreach($meta as $key => $entry) 
			$r .= _list_custom_order_meta_row($key, $entry);
		break;
	case 'customer':
		foreach($meta as $key => $entry) 
			$r .= _list_custom_customer_meta_row($key, $entry);
		break;
	case 'delivery':
		foreach($meta as $key => $entry) 
			$r .= _list_custom_delivery_meta_row($key, $entry);
		break;
	case 'member':
		foreach($meta as $key => $entry) 
			$r .= _list_custom_member_meta_row($key, $entry);
		break;
	}

	//REGEX BUG: but it'll return info
	// Compose JavaScript for return
	die($r);
}

/**
 * list custom customer meta row
 */
function _list_custom_customer_meta_row($key, $entry) {
	$r = '';
	$style = '';

	$name = $entry['name'];
	$means = get_option('usces_custom_customer_select');
	$meansoption = '';
	foreach($means as $meankey => $meanvalue) {
		$selected = ($meankey == $entry['means']) ? " selected='selected'" : "";
		$meansoption .= "<option value='".$meankey."'".$selected.">".$meanvalue."</option>\n";
	}
	$essential = $entry['essential'] == 1 ? " checked='checked'" : "";
	$value = '';
	if(is_array($entry['value'])) {
		foreach($entry['value'] as $k => $v) {
			$value .= htmlspecialchars($v)."\n";
		}
	}
	$value = trim($value);
	$positions = get_option('usces_custom_field_position_select');
	$positionsoption = '';
	foreach($positions as $poskey => $posvalue) {
		$selected = ($poskey == $entry['position']) ? " selected='selected'" : "";
		$positionsoption .= "<option value='".$poskey."'".$selected.">".$posvalue."</option>\n";
	}

	$r .= "\n\t<tr id='cscs-{$key}' class='{$style}'>";
	$r .= "\n\t\t<td class='left'><div><input type='text' name='cscs[{$key}][key]' id='cscs[{$key}][key]' class='optname' size='20' value='{$key}' readonly /></div>";
	$r .= "\n\t\t<div><input type='text' name='cscs[{$key}][name]' id='cscs[{$key}][name]' class='optname' size='20' value='{$name}' /></div>";
	$r .= "\n\t\t<div class='optcheck'><select name='cscs[{$key}][means]' id='cscs[{$key}][means]'>".$meansoption."</select>\n";
	$r .= "<input type='checkbox' name='cscs[{$key}][essential]' id='cscs[{$key}][essential]' value='1'{$essential} /><label for='cscs[{$key}][essential]'>".__('Required','usces')."</label>\n";
	$r .= "<select name='cscs[{$key}][position]' id='cscs[{$key}][position]'>".$positionsoption."</select></div>";
	$r .= "\n\t\t<div class='submit'><input type='button' name='del_cscs[{$key}]' id='del_cscs[{$key}]' value='".attribute_escape(__( 'Delete' ))."' onclick='customField.delCustomer(\"{$key}\");' />";
	$r .= "\n\t\t<input type='button' name='upd_cscs[{$key}]' id='upd_cscs[{$key}]' value='".attribute_escape(__( 'Update' ))."' onclick='customField.updCustomer(\"{$key}\");' /></div>";
	$r .= "</td>";
	$r .= "\n\t\t<td class='item-opt-value'><textarea name='cscs[{$key}][value]' id='cscs[{$key}][value]' class='optvalue'>{$value}</textarea></td>\n\t</tr>";
	return $r;
}

/**
 * list custom delivery meta row
 */
function _list_custom_delivery_meta_row($key, $entry) {
	$r = '';
	$style = '';

	$name = $entry['name'];
	$means = get_option('usces_custom_delivery_select');
	$meansoption = '';
	foreach($means as $meankey => $meanvalue) {
		$selected = ($meankey == $entry['means']) ? " selected='selected'" : "";
		$meansoption .= "<option value='".$meankey."'".$selected.">".$meanvalue."</option>\n";
	}
	$essential = $entry['essential'] == 1 ? " checked='checked'" : "";
	$value = '';
	if(is_array($entry['value'])) {
		foreach($entry['value'] as $k => $v) {
			$value .= htmlspecialchars($v)."\n";
		}
	}
	$value = trim($value);
	$positions = get_option('usces_custom_field_position_select');
	$positionsoption = '';
	foreach($positions as $poskey => $posvalue) {
		$selected = ($poskey == $entry['position']) ? " selected='selected'" : "";
		$positionsoption .= "<option value='".$poskey."'".$selected.">".$posvalue."</option>\n";
	}

	$r .= "\n\t<tr id='csde-{$key}' class='{$style}'>";
	$r .= "\n\t\t<td class='left'><div><input type='text' name='csde[{$key}][key]' id='csde[{$key}][key]' class='optname' size='20' value='{$key}' readonly /></div>";
	$r .= "\n\t\t<div><input type='text' name='csde[{$key}][name]' id='csde[{$key}][name]' class='optname' size='20' value='{$name}' /></div>";
	$r .= "\n\t\t<div class='optcheck'><select name='csde[{$key}][means]' id='csde[{$key}][means]'>".$meansoption."</select>\n";
	$r .= "<input type='checkbox' name='csde[{$key}][essential]' id='csde[{$key}][essential]' value='1'{$essential} /><label for='csde[{$key}][essential]'>".__('Required','usces')."</label>\n";
	$r .= "<select name='csde[{$key}][position]' id='csde[{$key}][position]'>".$positionsoption."</select></div>";
	$r .= "\n\t\t<div class='submit'><input type='button' name='del_csde[{$key}]' id='del_csde[{$key}]' value='".attribute_escape(__( 'Delete' ))."' onclick='customField.delDelivery(\"{$key}\");' />";
	$r .= "\n\t\t<input type='button' name='upd_csde[{$key}]' id='upd_csde[{$key}]' value='".attribute_escape(__( 'Update' ))."' onclick='customField.updDelivery(\"{$key}\");' /></div>";
	$r .= "</td>";
	$r .= "\n\t\t<td class='item-opt-value'><textarea name='csde[{$key}][value]' id='csde[{$key}][value]' class='optvalue'>{$value}</textarea></td>\n\t</tr>";
	return $r;
}

/**
 * list custom member meta row
 */
function _list_custom_member_meta_row($key, $entry) {
	$r = '';
	$style = '';

	$name = $entry['name'];
	$means = get_option('usces_custom_member_select');
	$meansoption = '';
	foreach($means as $meankey => $meanvalue) {
		$selected = ($meankey == $entry['means']) ? " selected='selected'" : "";
		$meansoption .= "<option value='".$meankey."'".$selected.">".$meanvalue."</option>\n";
	}
	$essential = $entry['essential'] == 1 ? " checked='checked'" : "";
	$value = '';
	if(is_array($entry['value'])) {
		foreach($entry['value'] as $k => $v) {
			$value .= htmlspecialchars($v)."\n";
		}
	}
	$value = trim($value);
	$positions = get_option('usces_custom_field_position_select');
	$positionsoption = '';
	foreach($positions as $poskey => $posvalue) {
		$selected = ($poskey == $entry['position']) ? " selected='selected'" : "";
		$positionsoption .= "<option value='".$poskey."'".$selected.">".$posvalue."</option>\n";
	}

	$r .= "\n\t<tr id='csmb-{$key}' class='{$style}'>";
	$r .= "\n\t\t<td class='left'><div><input type='text' name='csmb[{$key}][key]' id='csmb[{$key}][key]' class='optname' size='20' value='{$key}' readonly /></div>";
	$r .= "\n\t\t<div><input type='text' name='csmb[{$key}][name]' id='csmb[{$key}][name]' class='optname' size='20' value='{$name}' /></div>";
	$r .= "\n\t\t<div class='optcheck'><select name='csmb[{$key}][means]' id='csmb[{$key}][means]'>".$meansoption."</select>\n";
	$r .= "<input type='checkbox' name='csmb[{$key}][essential]' id='csmb[{$key}][essential]' value='1'{$essential} /><label for='csmb[{$key}][essential]'>".__('Required','usces')."</label>\n";
	$r .= "<select name='csmb[{$key}][position]' id='csmb[{$key}][position]'>".$positionsoption."</select></div>";
	$r .= "\n\t\t<div class='submit'><input type='button' name='del_csmb[{$key}]' id='del_csmb[{$key}]' value='".attribute_escape(__( 'Delete' ))."' onclick='customField.delMember(\"{$key}\");' />";
	$r .= "\n\t\t<input type='button' name='upd_csmb[{$key}]' id='upd_csmb[{$key}]' value='".attribute_escape(__( 'Update' ))."' onclick='customField.updMember(\"{$key}\");' /></div>";
	$r .= "</td>";
	$r .= "\n\t\t<td class='item-opt-value'><textarea name='csmb[{$key}][value]' id='csmb[{$key}][value]' class='optvalue'>{$value}</textarea></td>\n\t</tr>";
	return $r;
}
//20100818ysk end

?>
