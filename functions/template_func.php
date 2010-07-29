<?php
function usces_guid_tax() {
	global $usces;
	echo $usces->getGuidTax();
}

function usces_currency_symbol() {
	global $usces;
	echo $usces->getCurrencySymbol();
}

function usces_is_error_message() {
	global $usces;
	if ( $usces->error_message != '' )
		return true;
	else
		return false;
}

function usces_is_item() {
	global $usces, $post;
	if ( $post->post_mime_type == 'item' )
		return true;
	else
		return false;
}

function usces_the_itemCode( $out = '' ) {
	global $post;
	$post_id = $post->ID;

	$str = get_post_custom_values('_itemCode', $post_id);
	
	if( $out == 'return' ){
		return $str[0];
	}else{
		echo $str[0];
	}
}

function usces_the_itemName( $out = '', $post = NULL ) {
	if($post == NULL)
		global $post;
		
	$post_id = $post->ID;

	$str = get_post_custom_values('_itemName', $post_id);
	
	if( $out == 'return' ){
		return $str[0];
	}else{
		echo $str[0];
	}
}

function usces_the_point_rate( $out = '' ){
	global $post;
	$post_id = $post->ID;

	$str = get_post_custom_values('_itemPointrate', $post_id);
	$rate = (int)$str[0];
	
	if( $out == 'return' ){
		return $rate;
	}else{
		echo $rate;
	}
}

function usces_the_item(){
	global $usces, $post;
	$usces->itemskus = array();
	$usces->itemopts = array();
	$post_id = $post->ID;
	
	
	$fields = get_post_custom($post_id);
	foreach($fields as $key => $value){
		if( preg_match('/^_isku_/', $key, $match) ){
			$key = substr($key, 6);
			$values = maybe_unserialize($value[0]);
			$usces->itemskus[$key] = $values;
		} else if( preg_match('/^_iopt_/', $key, $match) ){
			$key = substr($key, 6);
			$values = maybe_unserialize($value[0]);
			$usces->itemopts[$key] = $values;
		}
	}
	//natcasesort($usces->itemskus);
	ksort($usces->itemskus, SORT_STRING);
	return;
}

function usces_get_itemMeta($metakey, $post_id, $out = ''){
	$str = get_post_custom_values($metakey, $post_id);
	$value = $str[0];
	
	if( $out == 'return' ){
		return $value;
	}else{
		echo $value;
	}
}

function usces_sku_num() {
	global $usces;
	
	return count($usces->itemskus);
}

function usces_is_skus() {
	global $usces;
	
	if( 0 < count($usces->itemskus) ){
		reset($usces->itemskus);
		$usces->itemsku = array();
		return true;
	}else{
		return false;
	}
}

function usces_have_skus() {
	global $usces;
	
	$usces->itemsku = each($usces->itemskus);
	if($usces->itemsku) {
		return true;
	} else {
		return false;
	}
}

function usces_the_itemSku() {
	global $usces;
	echo $usces->itemsku['key'];
}

function usces_the_itemPrice($flg = '') {
	global $usces;
	if($flg == 'return'){
		return $usces->itemsku['value']['price'];
	}else{
		echo number_format($usces->itemsku['value']['price']);
	}
}

function usces_the_itemCprice($flg = '') {
	global $usces;
	if($flg == 'return'){
		return $usces->itemsku['value']['cprice'];
	}else{
		echo number_format($usces->itemsku['value']['cprice']);
	}
}

function usces_the_itemZaiko( $out = '' ) {
	global $usces;
	$num = $usces->itemsku['value']['zaiko'];
	
	if( $out == 'return' ){
		return $usces->zaiko_status[$num];
	}else{
		echo $usces->zaiko_status[$num];
	}
}

function usces_the_itemZaikoNum( $out = '' ) {
	global $usces;
	$num = $usces->itemsku['value']['zaikonum'];
	
	if( $out == 'return' ){
		return $num;
	}else{
		echo $num;
	}
}

function usces_the_itemSkuDisp() {
	global $usces;
	echo $usces->itemsku['value']['disp'];
}

function usces_the_itemSkuUnit() {
	global $usces;
	echo $usces->itemsku['value']['unit'];
}

function usces_the_firstSku() {
	global $post, $usces;
	$post_id = $post->ID;
	
	
	$fields = $usces->get_skus( $post_id );
	
	echo $fields['key'][0];
}

function usces_the_firstPrice( $out = '', $post = NULL ) {
	global $usces;
	if($post == NULL)
		global $post;
	$post_id = $post->ID;
	
	
	$fields = $usces->get_skus( $post_id );
	
	if($out == 'return'){
		return number_format($fields['price'][0]);
	}else{
		echo number_format($fields['price'][0]);
	}
}

function usces_the_firstCprice() {
	global $post, $usces;
	$post_id = $post->ID;
	
	
	$fields = $usces->get_skus( $post_id );
	echo $fields['cprice'][0];
}

function usces_the_firstZaiko() {
	global $post, $usces;
	$post_id = $post->ID;
	
	
	$fields = $usces->get_skus( $post_id );
	
	echo $fields['zaiko'][0];
}

function usces_the_lastSku() {
	global $post, $usces;
	$post_id = $post->ID;
	
	
	$fields = $usces->get_skus( $post_id );
	
	echo end($fields['key']);
}

function usces_the_lastPrice() {
	global $post, $usces;
	$post_id = $post->ID;
	
	
	$fields = $usces->get_skus( $post_id );
	
	echo end($fields['price']);
}

function usces_the_lastZaiko() {
	global $post, $usces;
	$post_id = $post->ID;
	
	
	$fields = $usces->get_skus( $post_id );
	
	echo end($fields['zaiko']);
}

function usces_is_gptekiyo( $post_id, $sku, $quant ){
	global $usces;
	return $usces->is_gptekiyo( $post_id, $sku, $quant );
}
function usces_the_itemGpExp( $out = '' ) {
	global $post, $usces;
	$post_id = $post->ID;
	$sku = $usces->itemsku['key'];
	$GpN1 = $usces->getItemGpNum1($post_id);
	$GpN2 = $usces->getItemGpNum2($post_id);
	$GpN3 = $usces->getItemGpNum3($post_id);
	$GpD1 = $usces->getItemGpDis1($post_id);
	$GpD2 = $usces->getItemGpDis2($post_id);
	$GpD3 = $usces->getItemGpDis3($post_id);
	$unit = $usces->getItemSkuUnit($post_id, $sku);
	$price = $usces->getItemPrice($post_id, $sku);

	if( ($usces->itemsku['value']['gptekiyo'] == 0) || empty($GpN1) || empty($GpD1) ){
		return;
	}
	$html = "<dl class='itemGpExp'>\n<dt>" . __('Business package discount','usces') . "</dt>\n<dd>\n<ul>\n";
	if(!empty($GpN1) && !empty($GpD1)) {
		if(empty($GpN2) || empty($GpD2)) {
			$html .= "<li>";
			$html .= sprintf( __('<span class=%6$s>%5$s%1$s</span>%2$s par 1%3$s for more than %4$s%3$s', 'usces'),
						number_format(round($price * (100 - $GpD1) / 100)), 
						$usces->getGuidTax(),
						$unit,
						$GpN1, 
						__('$', 'usces'), 
						"'price'"
					);
			$html .= "</li>\n";
		} else {

			$html .= "<li>";
			$html .= sprintf( __('<span class=%7$s>%6$s%1$s</span>%2$s par 1%3$s for %4$s-%5$s%3$s', 'usces'),
						number_format(round($price * (100 - $GpD1) / 100)), 
						$usces->getGuidTax(),
						$unit,
						$GpN1, 
						$GpN2-1, 
						__('$', 'usces'), 
						"'price'"
					);
			$html .= "</li>\n";
			if(empty($GpN3) || empty($GpD3)) {
				//$html .=  "<li>" . $GpN2 . $unit . __('for more than ','usces') . "1" . $unit . __('par','usces') . "<span class='price'>" . __('$', 'usces') . number_format(round($price * (100 - $GpD2) / 100)) . $usces->getGuidTax() . "</span></li>\n";
				$html .= "<li>";
				$html .= sprintf( __('<span class=%6$s>%5$s%1$s</span>%2$s par 1%3$s for more than %4$s%3$s', 'usces'),
							number_format(round($price * (100 - $GpD2) / 100)), 
							$usces->getGuidTax(),
							$unit,
							$GpN2, 
							__('$', 'usces'), 
							"'price'"
						);
				$html .= "</li>\n";
			} else {
				$html .= "<li>";
				$html .= sprintf( __('<span class=%7$s>%6$s%1$s</span>%2$s par 1%3$s for %4$s-%5$s%3$s', 'usces'),
							number_format(round($price * (100 - $GpD2) / 100)), 
							$usces->getGuidTax(),
							$unit,
							$GpN2, 
							$GpN3-1, 
							__('$', 'usces'), 
							"'price'"
						);
				$html .= "</li>\n";
				$html .= "<li>";
				$html .= sprintf( __('<span class=%6$s>%5$s%1$s</span>%2$s par 1%3$s for more than %4$s%3$s', 'usces'),
							number_format(round($price * (100 - $GpD3) / 100)), 
							$usces->getGuidTax(),
							$unit,
							$GpN3, 
							__('$', 'usces'), 
							"'price'"
						);
				$html .= "</li>\n";
			}
		}
	}
	$html .= "</ul></dd></dl>";
		
	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

function usces_the_itemQuant( $out = '' ) {
	global $usces, $post;
	$post_id = $post->ID;
	$html = "<input name=\"quant[{$post_id}][{$usces->itemsku['key']}]\" type=\"text\" id=\"quant[{$post_id}][{$usces->itemsku['key']}]\" class=\"skuquantity\" value=\"1\" />";
		
	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

function usces_the_itemSkuButton($value, $type=0, $out = '') {
	global $usces, $post;
	$post_id = $post->ID;
	$sku = $usces->itemsku['key'];
	$zaikonum = $usces->itemsku['value']['zaikonum'];
	$num = $usces->itemsku['value']['zaiko'];
	$gptekiyo = $usces->itemsku['value']['gptekiyo'];
	$skuPrice = $usces->getItemPrice($post_id, $sku);
	
	if($type == 1)
		$type = 'button';
	else
		$type = 'submit';
		
	$html = "<input name=\"zaikonum[{$post_id}][{$sku}]\" type=\"hidden\" id=\"zaikonum[{$post_id}][{$sku}]\" value=\"{$zaikonum}\" />\n";
	$html .= "<input name=\"zaiko[{$post_id}][{$sku}]\" type=\"hidden\" id=\"zaiko[{$post_id}][{$sku}]\" value=\"{$num}\" />\n";
	$html .= "<input name=\"gptekiyo[{$post_id}][{$sku}]\" type=\"hidden\" id=\"gptekiyo[{$post_id}][{$sku}]\" value=\"{$gptekiyo}\" />\n";
	$html .= "<input name=\"skuPrice[{$post_id}][{$sku}]\" type=\"hidden\" id=\"skuPrice[{$post_id}][{$sku}]\" value=\"{$skuPrice}\" />\n";
	$html .= "<input name=\"inCart[{$post_id}][{$sku}]\" type=\"{$type}\" id=\"inCart[{$post_id}][{$sku}]\" class=\"skubutton\" value=\"{$value}\" onclick=\"return uscesCart.intoCart('{$post_id}','{$sku}')\" />";

	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

function usces_the_itemSkuTable($colum = '', $buttonValue = '' ) {
	global $post, $usces;
	
	if($colum = ''){
		$colum = 'sku = ' . __('size','usces') . ', price = ' . __('Price','usces') . ', zaiko = ' . __('stock','usces');
	}
	
	if($buttonValue = ''){
		$buttonValue = __('Add to Shopping Cart','usces');
	}
	
	$post_id = $post->ID;
	
	$cls = explode(',', $colum);
	foreach($cls as $val){
		list($subkey, $value) = explode('=', $val);
		$subkey = trim(strtolower($subkey));
		$value = trim(strtolower($value));
		if($value != 'null'){
			$colums[$subkey] = ($value == '') ? '&nbsp;' : $value;
		}
	}

	if(!$colums) return false;
	
	$fields = get_post_custom($post_id);
	$rows = array();
	foreach($fields as $key => $value){
		if( preg_match('/^_isku_/', $key, $match) ){
			$key = substr($key, 6);
			$values = maybe_unserialize($value[0]);
			$values['sku'] = $key;
			$values['zaiko'] = $usces->zaiko_status[$values['zaiko']];
			$rows[] = $values;
		}
	}
	if(!$rows) return false;
	//natcasesort($rows);
	ksort($rows, SORT_STRING);
		
	$html = "\n<table class=\"skutable\">\n";
	$html .= "\t<thead>\n";
	$html .= "\t\t<tr>\n";
	foreach ($colums as $label)
		$html .= "\t\t\t<th>" . $label . "</th>\n";
		if( $usces->options['insert_unit'] === false || $usces->options['insert_unit'] == 'plural' )
			$html .= "\t\t\t<th class='sku_skuquantity'>" . __('Quantity','usces') . "</th>\n";
		$html .= "\t\t\t<th class='sku_button'>&nbsp;</th>\n";
	$html .= "\t\t</tr>\n";
	$html .= "\t</thead>\n";
	$html .= "\t<tbody>\n";
	foreach ($rows as $values){
		$html .= "\t\t<tr>\n";
		foreach ($colums as $subkey => $label)
			$html .= "\t\t\t<td class='sku_{$subkey}'>" . $values[$subkey] . "</td>\n";
		if( $usces->options['insert_unit'] === false || $usces->options['insert_unit'] == 'plural' )
			$html .= "\t\t\t<td class='sku_skuquantity'><input name=\"quant[{$post_id}][{$values['sku']}]\" type=\"text\" id=\"quant[{$post_id}][{$values['sku']}]\" class=\"skuquantity\" value=\"\" /></td>\n";
		$html .= "\t\t\t<td class='sku_button'><input name=\"inCart[{$post_id}][{$values['sku']}]\" type=\"submit\" id=\"inCart[{$post_id}][{$values['sku']}]\" class=\"skubutton\" value=\"{$buttonValue}\" /></td>\n";
		$html .= "\t\t</tr>\n";
	}
	$html .= "\t</tbody>\n";
	$html .= "</table>\n";
	
	echo $html;
}

function usces_the_itemImage($number = 0, $width = 60, $height = 60, $post = '', $out = '' ) {
	global $usces;
	if($post == '') global $post;

	$post_id = $post->ID;
	
	$code =  get_post_custom_values('_itemCode', $post_id);
	if(!$code) return false;
	
	$name = get_post_custom_values('_itemName', $post_id);
	
	$pictids = $usces->get_pictids($code[0]);
	$html = wp_get_attachment_image( $pictids[$number], array($width, $height), false );//'<img src="#" height="60" width="60" alt="" />';
	$alt = 'alt="'.$code[0].'"';
	$alt = apply_filters('usces_filter_img_alt', $alt, $post_id);
	$html = preg_replace('/alt=\"[^\"]*\"/', $alt, $html);
	$title = 'title="'.$name[0].'"';
	$title = apply_filters('usces_filter_img_title', $title, $post_id);
	$html = preg_replace('/title=\"[^\"]+\"/', $title, $html);
	if($out == 'return'){
		return $html;
	}else{
		echo $html;
	}
}

function usces_the_itemImageURL($number = 0, $out = '' ) {
	global $post, $usces;
	$post_id = $post->ID;
	
	$code =  get_post_custom_values('_itemCode', $post_id);
	if(!$code) return false;
	$name = get_post_custom_values('_itemName', $post_id);
	$pictids = $usces->get_pictids($code[0]);
	$html = wp_get_attachment_url( $pictids[$number] );
	if($out == 'return'){
		return $html;
	}else{
		echo $html;
	}
}

function usces_the_itemImageCaption($number = 0, $post = '', $out = '' ) {
	global $usces;
	if($post == '') global $post;

	$post_id = $post->ID;
	
	$code =  get_post_custom_values('_itemCode', $post_id);
	if(!$code) return false;
	
	$name = get_post_custom_values('_itemName', $post_id);
	
	$pictids = $usces->get_pictids($code[0]);
	$attach_ob = get_post($pictids[$number]);
	$excerpt = esc_html($attach_ob->post_excerpt);

	if($out == 'return'){
		return $excerpt;
	}else{
		echo $excerpt;
	}
}

function usces_the_itemImageDescription($number = 0, $post = '', $out = '' ) {
	global $usces;
	if($post == '') global $post;

	$post_id = $post->ID;
	
	$code =  get_post_custom_values('_itemCode', $post_id);
	if(!$code) return false;
	
	$name = get_post_custom_values('_itemName', $post_id);
	
	$pictids = $usces->get_pictids($code[0]);
	$attach_ob = get_post($pictids[$number]);
	$excerpt = esc_html($attach_ob->post_content);

	if($out == 'return'){
		return $excerpt;
	}else{
		echo $excerpt;
	}
}

function usces_get_itemSubImageNums() {
	global $post, $usces;
	$post_id = $post->ID;
	$res = array();
	
	$code =  get_post_custom_values('_itemCode', $post_id);
	if(!$code) return false;
	$name = get_post_custom_values('_itemName', $post_id);
	$pictids = $usces->get_pictids($code[0]);
	for($i=1; $i<count($pictids); $i++){
		$res[] = $i;
	}
	return  $res;
}

function usces_is_options() {
	global $usces;
	
	if( 0 < count($usces->itemopts) ){
		reset($usces->itemopts);
		$usces->itemopt = array();
		return true;
	}else{
		return false;
	}
}

function usces_have_options() {
	global $usces;
	
	$usces->itemopt = each($usces->itemopts);
	if($usces->itemopt) {
		return true;
	} else {
		return false;
	}
}

function usces_getItemOptName() {
	global $usces;
	return $usces->itemopt['key'];
}

function usces_the_itemOptName() {
	global $usces;
	echo $usces->itemopt['key'];
}

function usces_the_itemOption( $name, $label = '#default#', $out = '' ) {
	global $post, $usces;
	$post_id = $post->ID;

	if($label == '#default#')
		$label = $name;
	$key = '_iopt_' . $name;
	$value = get_post_custom_values($key, $post_id);
	if(!$value) return false;
	$values = maybe_unserialize($value[0]);
	$means = (int)$values['means'][0];
	$essential = (int)$values['essential'][0];
	$html = '';
	$sku = $usces->itemsku['key'];
	
	if($means < 2){
		$selects = explode("\n", $values['value'][0]);
		$multiple = ($means === 0) ? '' : ' multiple';
		$html .= "\n<label for='itemOption[{$post_id}][{$sku}][{$name}]' class='iopt_label'>{$label}</label>\n";
		$html .= "\n<select name='itemOption[{$post_id}][{$sku}][{$name}]' id='itemOption[{$post_id}][{$sku}][{$name}]' class='iopt_select'{$multiple}>\n";
		if($essential == 1)
			$html .= "\t<option value='#NONE#' selected='selected'>" . __('Choose','usces') . "</option>\n";
		$i=0;
		foreach($selects as $v) {
			if($i == 0 && $essential == 0) 
				$selected = ' selected="selected"';
			else
				$selected = '';
			$html .= "\t<option value='{$v}'{$selected}>{$v}</option>\n";
			$i++;
		}
		$html .= "</select>\n";
	}else{
		$html .= "\n<input name='itemOption[{$post_id}][{$sku}][{$name}]' type='text' id='itemOption[{$post_id}][{$sku}][{$name}]' class='iopt_text' />\n";
	}
	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

function usces_the_cart() {
	global $usces;
	
	$usces->display_cart();
	
}

function usces_is_cart() {
	global $usces;
	
	if($usces->cart->num_row() > 0)
		if( apply_filters('usces_is_cart_check', true) ) {
			return true;
		}else{
			return false;
		}
	else
		return false;
		
}

function usces_is_category( $str ) {
	global $post;

	//if( $post->post_type != 'post' ) return false;
	
	$cat = get_the_category();
	$slugs = array();
	foreach($cat as $value){
		$slugs[] = $value->slug;
	}
	
	$str = utf8_uri_encode($str);
	
	if( in_array( $str, $slugs) )
		return true;
	else
		return false;
}

function usces_the_pref( $flag, $out = '' ){
	global $usces;
	
	$usces_members = $usces->get_member();
	$usces_entries = $usces->cart->get_entry();
	$name = $flag . '[pref]';
	$pref = $usces_entries[$flag]['pref'];
	if( 'member' == $flag)
		$pref = $usces_members['pref'];
	
	$html = "<select name='{$name}' id='pref' class='pref'>\n";
//	$prefs = get_option('usces_pref');
	$prefs = $usces->options['province'];
	foreach($prefs as $value) {
		$selected = ($pref == $value) ? ' selected="selected"' : '';
		$html .= "\t<option value='{$value}'{$selected}>{$value}</option>\n";
	}
	$html .= "</select>\n";
	
	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

function usces_the_company_name(){
	global $usces;
	echo $usces->options['company_name'];
}

function usces_the_zip_code(){
	global $usces;
	echo $usces->options['zip_code'];
}

function usces_the_address1(){
	global $usces;
	echo $usces->options['address1'];
}

function usces_the_address2(){
	global $usces;
	echo $usces->options['address2'];
}

function usces_the_tel_number(){
	global $usces;
	echo $usces->options['tel_number'];
}

function usces_the_fax_number(){
	global $usces;
	echo $usces->options['fax_number'];
}

function usces_the_inquiry_mail(){
	global $usces;
	echo $usces->options['inquiry_mail'];
}

function usces_the_postage_privilege(){
	global $usces;
	echo $usces->options['postage_privilege'];
}

function usces_the_start_point(){
	global $usces;
	echo $usces->options['start_point'];
}

function usces_point_rate( $post_id = NULL, $out = '' ){
	global $usces;
	if(  $post_id = NULL ){
		$rate = $usces->options['point_rate'];
	}else{
		$str = get_post_custom_values('_itemPointrate', $post_id);
		$rate = (int)$str[0];
	}
	if( $out == 'return' ){
		return $rate;
	}else{
		echo $rate;
	}
}

function usces_the_payment_method( $value = '', $out = '' ){
	global $usces;
	if( !$usces->options['payment_method'] ) return;

	$html = "<dl>\n";
	
	if( EX_DLSELLER === true ){
		foreach ($usces->options['payment_method'] as $id => $payments) {
			if( $payments['name'] != '' ) {
				$module = trim($payments['module']);
				$checked = ($payments['name'] == $value) ? ' checked' : '';
				if( (empty($module) || !file_exists($usces->options['settlement_path'] . $module)) && $payments['settlement'] == 'acting' ) {
					$checked = '';
					$html .= "\t".'<dt><label for="payment_name_' . $id . '"><input name="order[payment_name]" id="payment_name_' . $id . '" type="radio" value="'.$payments['name'].'"' . $checked . ' disabled onKeyDown="if (event.keyCode == 13) {return false;}" />'.$payments['name'].'</label> <b> (' . __('cannot use this payment method now.','usces') . ") </b></dt>\n";
				}elseif( $payments['settlement'] == 'acting' ){
					$html .= "\t".'<dt><label for="payment_name_' . $id . '"><input name="order[payment_name]" id="payment_name_' . $id . '" type="radio" value="'.$payments['name'].'"' . $checked . ' onKeyDown="if (event.keyCode == 13) {return false;}" />'.$payments['name']."</label></dt>\n";
				}
				$html .= "\t<dd>{$payments['explanation']}</dd>\n";
			}
		}
	}else{
		foreach ($usces->options['payment_method'] as $id => $payments) {
			if( $payments['name'] != '' ) {
				$module = trim($payments['module']);
				$checked = ($payments['name'] == $value) ? ' checked' : '';
				if( (empty($module) || !file_exists($usces->options['settlement_path'] . $module)) && $payments['settlement'] == 'acting' ) {
					$checked = '';
					$html .= "\t".'<dt><label for="payment_name_' . $id . '"><input name="order[payment_name]" id="payment_name_' . $id . '" type="radio" value="'.$payments['name'].'"' . $checked . ' disabled onKeyDown="if (event.keyCode == 13) {return false;}" />'.$payments['name']."</label> <b> (" . __('cannot use this payment method now.','usces') . ") </b></dt>\n";
				}else{
					$html .= "\t".'<dt><label for="payment_name_' . $id . '"><input name="order[payment_name]" id="payment_name_' . $id . '" type="radio" value="'.$payments['name'].'"' . $checked . ' onKeyDown="if (event.keyCode == 13) {return false;}" />'.$payments['name']."</label></dt>\n";
				}
				$html .= "\t<dd>{$payments['explanation']}</dd>\n";
			}
		}
	}

	$html .= "</dl>\n";
	
	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

function usces_get_payments_by_name( $name ){
	global $usces;
	if( !$usces->options['payment_method'] ) return false;
	
	foreach ($usces->options['payment_method'] as $id => $payments) {
		if( $payments['name'] == $name ) {
			return $payments;
		}
	}

	return false;
}

function usces_the_delivery_method( $value = '', $out = '' ){
	global $usces;
	$deli_id = $usces->get_available_delivery_method();
	$html = '<select name="order[delivery_method]"  id="delivery_method_select" class="delivery_time" onKeyDown="if (event.keyCode == 13) {return false;}">'."\n";
	foreach ($deli_id as $id) {
		$index = $usces->get_delivery_method_index($id);
		$selected = ($id == $value) ? ' selected="selected"' : '';
		$html .= "\t<option value='{$id}'{$selected}>{$usces->options['delivery_method'][$index]['name']}</option>\n";
	}

	$html .= "</select>\n";
	
	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

function usces_the_delivery_time( $value = '', $out = '' ){
	global $usces;
	//if( $usces->options['delivery_time'] == '' ) return;
	
	//$array = explode("\n", $usces->options['delivery_time']);
	$html = "<select name='order[delivery_time]' id='delivery_time_select' class='delivery_time'>\n";

	$html .= "</select>\n";
	
	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

function usces_the_campaign_schedule($flag, $kind){
	global $usces;
	$startdate = $usces->options['campaign_schedule']['start']['year'] . __('year','usces') . $usces->options['campaign_schedule']['start']['month'] . __('month','usces') . $usces->options['campaign_schedule']['start']['day'] . __('day','usces');
	$starttime = $usces->options['campaign_schedule']['start']['hour'] . __('hour','usces') . $usces->options['campaign_schedule']['start']['min'] . __('min','usces');
	$enddate = $usces->options['campaign_schedule']['end']['year'] . __('year','usces') . $usces->options['campaign_schedule']['end']['month'] . __('month','usces') . $usces->options['campaign_schedule']['end']['day'] . __('day','usces');
	$endtime = $usces->options['campaign_schedule']['end']['hour'] . __('hour','usces') . $usces->options['campaign_schedule']['end']['min'] . __('min','usces');
	if( 'start' == $flag ) {
		if( 'date' == $kind ) {
			echo $startdate;
		}elseif( 'datetime' == $kind ) {
			echo $startdate . ' ' . $starttime;
		}
	} elseif ( 'end' == $flag ) {
		if( 'date' == $kind ) {
			echo $enddate;
		}elseif( 'datetime' == $kind ) {
			echo $enddate . ' ' . $endtime;
		}
	}
}


function usces_the_confirm() {
	global $usces;
	
	$usces->display_cart_confirm();
}

function usces_the_inquiry_form() {
	global $usces;
	$error_message = '';
	if( isset($_POST['inq_name']) && '' != trim($_POST['inq_name']) ) {
		$inq_name = esc_html(trim($_POST['inq_name']));
	}else{
		$inq_name = '';
		if($usces->page == 'deficiency')
			$error_message .= __('Please input your name.', 'usces') . "<br />";
	}
	if( isset($_POST['inq_mailaddress']) && is_email(trim($_POST['inq_mailaddress'])) ) {
		$inq_mailaddress = wp_specialchars(trim($_POST['inq_mailaddress']));
	}elseif( isset($_POST['inq_mailaddress']) && !is_email(trim($_POST['inq_mailaddress'])) ) {
		$inq_mailaddress = esc_html(trim($_POST['inq_mailaddress']));
		if($usces->page == 'deficiency')
			$error_message .= __('E-mail address is not correct', 'usces') . "<br />";
	}else{
		$inq_mailaddress = '';
		if($usces->page == 'deficiency')
			$error_message .= __('Please input your e-mail address.', 'usces') . "<br />";
	}
	if( isset($_POST['inq_contents']) && '' != trim($_POST['inq_contents']) ) {
		$inq_contents = esc_html(trim($_POST['inq_contents']));
	}else{
		$inq_contents = '';
		if($usces->page == 'deficiency')
			$error_message .= __('Please input contents.', 'usces');
	}

	if($usces->page == 'inquiry_comp') :
?>
	<div class="inquiry_comp"><?php _e('sending completed','usces') ?></div>
	<div class="compbox"><?php _e('I send a reply email to a visitor. I ask in a few minutes to be able to have you refer in there being the fear that e-mail address is different again when the email from this shop does not arrive.','usces') ?></div>
<?php
	elseif($usces->page == 'inquiry_error') :
?>
	<div class="inquiry_comp"><?php _e('Failure in sending','usces') ?></div>
<?php 
	else :
?>
<?php if( !empty($error_message) ): ?>
<div class="error_message"><?php echo $error_message; ?></div>
<?php endif; ?>
<form name="inquiry_form" action="<?php //echo USCES_CART_URL; ?>" method="post">
<table border="0" cellpadding="0" cellspacing="0" class="inquiry_table">
<tr>
<th scope="row"><?php _e('Full name','usces') ?></th>
<td><input name="inq_name" type="text" class="inquiry_name" value="<?php echo $inq_name; ?>" /></td>
</tr>
<tr>
<th scope="row"><?php _e('e-mail adress','usces') ?></th>
<td><input name="inq_mailaddress" type="text" class="inquiry_mailaddress" value="<?php echo $inq_mailaddress; ?>" /></td>
</tr>
<tr>
<th scope="row"><?php _e('contents','usces') ?></th>
<td><textarea name="inq_contents" class="inquiry_contents"><?php echo $inq_contents; ?></textarea></td>
</tr>
</table>
<div class="send"><input name="inquiry_button" type="submit" value="<?php _e('Admit to send it with this information.','usces') ?>" /></div>
</form>
<?php
	endif;
}

function usces_get_cat_id( $slug ) {
	$cat = get_category_by_slug( $slug ); 
	return $cat->term_id; 
}

function usces_the_calendar() {
	global $usces;
	include (USCES_PLUGIN_DIR . '/includes/widget_calendar.php'); 
}

function usces_loginout() {
	global $usces;
	if ( !$usces->is_member_logged_in() )
		echo '<a href="' . USCES_MEMBER_URL . '&page=login">' . apply_filters('usces_filter_loginlink_label', __('Log-in','usces')) . '</a>';
	else
		echo '<a href="' . USCES_MEMBER_URL . '&page=logout">' . apply_filters('usces_filter_logoutlink_label', __('Log out','usces')) . '</a>';
}

function usces_is_login() {
	global $usces;
	return $usces->is_member_logged_in();
}

function usces_the_member_name() {
	global $usces;
	$usces->get_current_member();
	echo $usces->current_member['name'];
	
}
function usces_get_assistance_id_list($post_id) {
	global $usces;
	$names = $usces->get_tag_names($post_id);
	$list = '';
	foreach ( $names as $itemname )
		$list .= $usces->get_ID_byItemName($itemname, 'publish') . ',';
	
	$list = trim($list, ',');

	return $list;
}
function usces_remembername( $out = '' ){
	global $usces;
	$value = $usces->get_cookie();
	
	if( $out == 'return' ){
		if($value)
			return $value['name'];
		else
			return '';
	}else{
		if($value)
			echo $value['name'];
		else
			echo '';
	}
}
function usces_rememberpass( $out = '' ){
	global $usces;
	$value = $usces->get_cookie();
	
	if( $out == 'return' ){
		if($value)
			return $value['pass'];
		else
			return '';
	}else{
		if($value)
			echo $value['pass'];
		else
			echo '';
	}
}
function usces_remembercheck( $out = '' ){
	global $usces;
	$value = $usces->get_cookie();
	
	if( $out == 'return' ){
		if($value && $value['name'] != '')
			return ' checked="checked"';
		else
			return '';
	}else{
		if($value && $value['name'] != '')
			echo ' checked="checked"';
		else
			echo '';
	}
}
function usces_shippingchargeTR( $index='' ) {
	global $usces;
	if($index == ""){
		$index = 0;
	}
	$list = '';
	if( !isset($usces->options['shipping_charge'][$index]) ) return;
	$shipping_charge = $usces->options['shipping_charge'][$index];
	foreach ($shipping_charge['value'] as $pref => $value) {
		$list .= "<tr><th>" . $pref . "</th>\n";
		$list .= "<td class='rightnum'>" . number_format($value) . "</td>\n";
		$list .= "</tr>\n";
	}
	echo $list;
}
function usces_sc_shipping_charge() {
	global $usces;
	echo $usces->sc_shipping_charge();
}
function usces_sc_postage_privilege() {
	global $usces;
	echo $usces->sc_postage_privilege();
}
function usces_sc_payment_title() {
	global $usces;
	echo $usces->sc_payment_title();
}



function usces_posts_random_offset( $posts ){
	foreach( (array)$posts as $post ){
		$ids[] = $post->ID;
	}
	$ct = count($ids);
	$index = rand(0, ($ct-1));
	return $index;
}

function usces_get_category_link_by_slug( $slug ){
	$category = get_category_by_slug($slug); 
	echo get_category_link( $category->term_id );
}

function usces_get_page_ID_by_pname( $post_name, $return = 'echo' ){
	$page = get_page_by_path( $post_name );
	if($return == 'return')
		return $page->ID;
	else
		echo $page->ID;
}

function usces_list_bestseller($num, $days = ''){
	global $usces;
	$ids = $usces->get_bestseller_ids( $days );
	$htm = "";
	for($i=0; $i<$num; $i++){
		if(isset($ids[$i])){
			$post = get_post($ids[$i]);
			$disp_text = apply_filters('usces_widget_bestseller_auto_text', wp_specialchars($post->post_title), $ids[$i]);
			$list = "<li><a href='" . get_permalink($ids[$i]) . "'>" . $disp_text . "</a></li>\n";
			$htm .= apply_filters('usces_filter_bestseller', $list, $ids[$i], $i);
		}
	}
	echo $htm;
}

function usces_list_post( $slug, $rownum ){
	global $usces;
	
	$cat_id = usces_get_cat_id( $slug );
	$li = '';
	$infolist = get_posts('category='.$cat_id.'&numberposts='.$rownum.'&order=DESC&orderby=post_date');
	foreach ($infolist as $post) :
		$list = "<li>\n";
		$list .= "<div class='title'><a href='".get_permalink($post->ID)."'>" . $post->post_title . "</a></div>\n";
		$list .= "<p>" . $post->post_excerpt . "</p>\n";
		$list .= "</li>\n";
		$li .= apply_filters( 'usces_filter_widget_post', $list, $post, $slug);
	endforeach;
	echo $li;
}

function usces_categories_checkbox($output=''){
	global $usces;
	$htm = '';
	$retcats = usces_search_categories();
	$categories =  get_categories('child_of='.USCES_ITEM_CAT_PARENT_ID . "&hide_empty=0&orderby=ID"); 
	foreach ($categories as $cat) {
		$children =  get_categories('child_of='.$cat->term_id . "&hide_empty=0&orderby=" . $usces->options['fukugo_category_orderby'] . "&order=" . $usces->options['fukugo_category_order']);
		if(!empty($children)){
			$htm .= "<fieldset><legend>" . $cat->cat_name . "</legend><ul>\n";
			foreach ($children as $child) {
				$checked = in_array($child->term_id, $retcats) ? " checked='checked'" : "";
				$htm .= "<li><input name='category[".$child->term_id."]' type='checkbox' id='category[".$child->term_id."]' value='".$child->term_id."'".$checked." /><label for='category[".$child->term_id."]'>".$child->cat_name."</label></li>\n";
			}
			$htm .= "</ul></fieldset>\n";
		}
	}
	if($output == '' || $output == 'echo')
		echo $htm;
	else
		return $htm;
}

function usces_search_categories(){
	$cats = array();
	if(isset($_POST['category']))
		$cats = $_POST['category'];
	else
		$cats = array(USCES_ITEM_CAT_PARENT_ID);
	return $cats;
}

function usces_delivery_method_name( $id, $out = '' ){
	global $usces;
	
	if($id > -1){
		$id =$usces->get_delivery_method_index($id);
		$name = $usces->options['delivery_method'][$id]['name'];
	}else{		
		$name = __('No preference','usces');
	}
	
	if($out == 'return'){
		return $name;
	}else{
		echo $name;
	}
}

function usces_is_membersystem_state(){
	global $usces;

	if($usces->options['membersystem_state'] == 'activate') {
		return true;
	}else{
		return false;
	}
}

function usces_is_membersystem_point(){
	global $usces;

	if($usces->options['membersystem_point'] == 'activate') {
		return true;
	}else{
		return false;
	}
}

function usces_copyright(){
	global $usces;

	echo $usces->options['copyright'];
}

function usces_totalprice_in_cart(){
	global $usces;

	echo number_format($usces->get_total_price());
}

function usces_totalquantity_in_cart(){
	global $usces;

	echo number_format($usces->get_total_quantity());
}

function usces_get_page_mode(){
	global $usces;

	return $usces->page;
}

function usces_is_cat_of_item( $cat_id ){
	global $usces;
	$ids = $usces->get_item_cat_ids();
	$ids[] = USCES_ITEM_CAT_PARENT_ID;
	if(in_array($cat_id, $ids)){
		return true;
	}else{
		return false;
	}
}
?>
