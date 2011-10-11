<?php
function usces_guid_tax( $out = '' ){
	global $usces;

	if( $out == 'return' ){
		return $usces->getGuidTax();
	}else{
		echo $usces->getGuidTax();
	}
}

function usces_currency_symbol( $out = '' ) {
	global $usces;

	if( $out == 'return' ){
		return $usces->getCurrencySymbol();
	}else{
		echo esc_html($usces->getCurrencySymbol());
	}
}

function usces_is_error_message() {
	global $usces;
	if ( $usces->error_message != '' )
		return true;
	else
		return false;
}

function usces_is_item( $post_id = NULL ) {
	if( NULL == $post_id ){
		global $post;
	}else{
		$post = get_post($post_id);
	}
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
		echo esc_html($str[0]);
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
		echo esc_html($str[0]);
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
		echo esc_html($rate);
	}
}

function usces_the_shipment_aim( $out = '' ){
	global $post;
	$post_id = $post->ID;

	$str = get_post_custom_values('_itemShipping', $post_id);
	$no = (int)$str[0];
	if( 0 === $no ) return;
	
	$rules = get_option('usces_shipping_rule');
	
	if( $out == 'return' ){
		return $rules[$no];
	}else{
		echo esc_html($rules[$no]);
	}
}

function usces_the_item(){
	global $usces, $post;
	$usces->itemskus = array();
	$usces->itemopts = array();
	$post_id = $post->ID;
	
	$skuorderby = $usces->options['system']['orderby_itemsku'] ? 'meta_id' : 'meta_key';
	$skufields = $usces->get_post_custom($post_id, $skuorderby);
	$optorderby = $usces->options['system']['orderby_itemopt'] ? 'meta_id' : 'meta_key';
	$optfields = $usces->get_post_custom($post_id, $optorderby);
	foreach((array)$skufields as $key => $value){
		if( preg_match('/^_isku_/', $key, $match) ){
			$key = substr($key, 6);
			$values = maybe_unserialize($value[0]);
			$usces->itemskus[$key] = $values;
		}
	}
	foreach((array)$optfields as $key => $value){
		if( preg_match('/^_iopt_/', $key, $match) ){
			$key = substr($key, 6);
			$values = maybe_unserialize($value[0]);
			$usces->itemopts[$key] = $values;
		}
	}
	//var_dump($fields);
	//natcasesort($usces->itemskus);
	//ksort($usces->itemskus, SORT_STRING);
	//ksort($usces->itemopts, SORT_STRING);
	return;
}

function usces_get_itemMeta($metakey, $post_id, $out = ''){
	$str = get_post_custom_values($metakey, $post_id);
	$value = $str[0];
	
	if( $out == 'return' ){
		return $value;
	}else{
		echo esc_html($value);
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

function usces_the_itemSku($out = '') {
	global $usces;

	if($out == 'return'){
		return $usces->itemsku['key'];
	}else{
		echo esc_attr($usces->itemsku['key']);
	}
}

function usces_the_itemPrice($out = '') {
	global $usces;
	if($out == 'return'){
		return $usces->itemsku['value']['price'];
	}else{
		echo number_format($usces->itemsku['value']['price']);
	}
}

function usces_the_itemCprice($out = '') {
	global $usces;
	if($out == 'return'){
		return $usces->itemsku['value']['cprice'];
	}else{
		echo number_format($usces->itemsku['value']['cprice']);
	}
}

function usces_the_itemPriceCr($out = '') {
	global $usces;
	$res = esc_html($usces->get_currency($usces->itemsku['value']['price'], true, false ));

	if($out == 'return'){
		return $res;
	}else{
		echo $res;
	}
}

function usces_the_itemCpriceCr($out = '') {
	global $usces;
	$res = esc_html($usces->get_currency($usces->itemsku['value']['cprice'], true, false ));

	if($out == 'return'){
		return $res;
	}else{
		echo $res;
	}
}

function usces_crcode( $out = '' ) {
	global $usces;
	$res = esc_html($usces->get_currency_code());
	
	if($out == 'return'){
		return $res;
	}else{
		echo __($res, 'usces');
	}
}

function usces_crsymbol( $out = '', $js = NULL ) {
	global $usces;
	$res = $usces->getCurrencySymbol();
	if( 'js' == $js && '&yen;' == $res ){
		$res = mb_convert_encoding($res, 'UTF-8', 'HTML-ENTITIES');
	}
	
	if($out == 'return'){
		return $res;
	}else{
		echo esc_html($res);
	}
}

function usces_the_itemZaiko( $out = '' ) {
	global $usces;
	$num = (int)$usces->itemsku['value']['zaiko'];
	
	if( 0 === $num && 0 === (int)$usces->itemsku['value']['zaikonum'] && '' != $usces->itemsku['value']['zaikonum'] ){
		$res = $usces->zaiko_status[2];
	}else{
		$res = $usces->zaiko_status[$num];
	}
	
	if( $out == 'return' ){
		return $res;
	}else{
		echo esc_html($res);
	}
}

function usces_the_itemZaikoNum( $out = '' ) {
	global $usces;
	$num = $usces->itemsku['value']['zaikonum'];
	
	if( $out == 'return' ){
		return $num;
	}else{
		echo number_format($num);
	}
}

function usces_the_itemSkuDisp( $out = '' ) {
	global $usces;
	
	if( $out == 'return' ){
		return $usces->itemsku['value']['disp'];
	}else{
		echo esc_html($usces->itemsku['value']['disp']);
	}
}

function usces_the_itemSkuUnit( $out = '' ) {
	global $usces;
	
	if( $out == 'return' ){
		return $usces->itemsku['value']['unit'];
	}else{
		echo esc_html($usces->itemsku['value']['unit']);
	}
}

function usces_the_firstSku( $out = '' ) {
	global $post, $usces;
	$post_id = $post->ID;
	
	
	$fields = $usces->get_skus( $post_id );
	

	if($out == 'return'){
		return $fields['key'][0];
	}else{
		echo esc_html($fields['key'][0]);
	}
}

function usces_the_firstPrice( $out = '', $post = NULL ) {
	global $usces;
	if($post == NULL)
		global $post;
	$post_id = $post->ID;
	
	$fields = $usces->get_skus( $post_id );
	
	if($out == 'return'){
		return $fields['price'][0];
	}else{
		echo number_format($fields['price'][0]);
	}
}

function usces_the_firstCprice( $out = '', $post = NULL ) {
	global $usces;
	if($post == NULL)
		global $post;
	$post_id = $post->ID;
	
	$fields = $usces->get_skus( $post_id );
	
	if($out == 'return'){
		return $fields['cprice'][0];
	}else{
		echo number_format($fields['cprice'][0]);
	}
}

function usces_the_firstPriceCr( $out = '', $post = NULL ) {
	global $usces;
	if($post == NULL)
		global $post;
	$post_id = $post->ID;
	
	$fields = $usces->get_skus( $post_id );
	$res = esc_html($usces->get_currency($fields['price'][0], true, false ));

	if($out == 'return'){
		return $res;
	}else{
		echo $res;
	}
}

function usces_the_firstCpriceCr( $out = '', $post = NULL ) {
	global $usces;
	if($post == NULL)
		global $post;
	$post_id = $post->ID;
	
	$fields = $usces->get_skus( $post_id );
	$res = esc_html($usces->get_currency($fields['cprice'][0], true, false ));

	if($out == 'return'){
		return $res;
	}else{
		echo $res;
	}
}

function usces_the_firstZaiko() {
	global $post, $usces;
	$post_id = $post->ID;
	
	
	$fields = $usces->get_skus( $post_id );
	
	echo esc_html($fields['zaiko'][0]);
}

function usces_the_lastSku() {
	global $post, $usces;
	$post_id = $post->ID;
	
	
	$fields = $usces->get_skus( $post_id );
	
	echo esc_html(end($fields['key']));
}

function usces_the_lastPrice() {
	global $post, $usces;
	$post_id = $post->ID;
	
	
	$fields = $usces->get_skus( $post_id );
	
	echo number_format(end($fields['price']));
}

function usces_the_lastZaiko() {
	global $post, $usces;
	$post_id = $post->ID;
	
	
	$fields = $usces->get_skus( $post_id );
	
	echo esc_html(end($fields['zaiko']));
}

function usces_have_zaiko(){
	global $post, $usces;
	return $usces->is_item_zaiko( $post->ID, $usces->itemsku['key'] );
}

function usces_have_zaiko_anyone( $post_id = NULL ){
	 global $post, $usces;
	if( NULL == $post_id ) $post_id = $post->ID;
	
	$skus = $usces->get_skus($post_id, 'ARRAY_A');
	$status = false;
	foreach($skus as $value){
		if( ('' == $value['zaikonum'] || 0 < (int)$value['zaikonum']) && 2 > (int)$value['zaiko']) {
			$status = true;
			break;
		}
	}
	return $status;
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
	$html = "<dl class='itemGpExp'>\n<dt>" . apply_filters( 'usces_filter_itemGpExp_title', __('Business package discount','usces')) . "</dt>\n<dd>\n<ul>\n";
	if(!empty($GpN1) && !empty($GpD1)) {
		if(empty($GpN2) || empty($GpD2)) {
			$html .= "<li>";
			$html .= sprintf( __('<span class=%6$s>%5$s%1$s</span>%2$s par 1%3$s for more than %4$s%3$s', 'usces'),
						number_format(round($price * (100 - $GpD1) / 100)), 
						$usces->getGuidTax(),
						esc_html($unit),
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
						esc_html($unit),
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
							esc_html($unit),
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
							esc_html($unit),
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
							esc_html($unit),
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
	$value = isset( $_SESSION['usces_singleitem']['quant'][$post_id][$usces->itemsku['key']] ) ? $_SESSION['usces_singleitem']['quant'][$post_id][$usces->itemsku['key']] : 1;
	$quant = "<input name=\"quant[{$post_id}][" . esc_attr($usces->itemsku['key']) . "]\" type=\"text\" id=\"quant[{$post_id}][" . esc_attr($usces->itemsku['key']) . "]\" class=\"skuquantity\" value=\"" . $value . "\" onKeyDown=\"if (event.keyCode == 13) {return false;}\" />";
	$html = apply_filters('usces_filter_the_itemQuant', $quant, $post);
		
	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

function usces_the_itemSkuButton($value, $type=0, $out = '') {
	global $usces, $post;
	$post_id = $post->ID;
	$zaikonum = $usces->itemsku['value']['zaikonum'];
	$zaiko_status = $usces->itemsku['value']['zaiko'];
	$gptekiyo = $usces->itemsku['value']['gptekiyo'];
	$skuPrice = $usces->getItemPrice($post_id, $usces->itemsku['key']);
	$value = esc_attr(apply_filters( 'usces_filter_incart_button_label', $value));
	$sku = esc_attr($usces->itemsku['key']);
	
	if($type == 1)
		$type = 'button';
	else
		$type = 'submit';
		
	$html = "<input name=\"zaikonum[{$post_id}][{$sku}]\" type=\"hidden\" id=\"zaikonum[{$post_id}][{$sku}]\" value=\"{$zaikonum}\" />\n";
	$html .= "<input name=\"zaiko[{$post_id}][{$sku}]\" type=\"hidden\" id=\"zaiko[{$post_id}][{$sku}]\" value=\"{$zaiko_status}\" />\n";
	$html .= "<input name=\"gptekiyo[{$post_id}][{$sku}]\" type=\"hidden\" id=\"gptekiyo[{$post_id}][{$sku}]\" value=\"{$gptekiyo}\" />\n";
	$html .= "<input name=\"skuPrice[{$post_id}][{$sku}]\" type=\"hidden\" id=\"skuPrice[{$post_id}][{$sku}]\" value=\"{$skuPrice}\" />\n";
	if( $usces->use_js ){
		$html .= "<input name=\"inCart[{$post_id}][{$sku}]\" type=\"{$type}\" id=\"inCart[{$post_id}][{$sku}]\" class=\"skubutton\" value=\"{$value}\" onclick=\"return uscesCart.intoCart('{$post_id}','{$sku}')\" />";
	}else{
		$html .= "<a name=\"cart_button\"></a><input name=\"inCart[{$post_id}][{$sku}]\" type=\"{$type}\" id=\"inCart[{$post_id}][{$sku}]\" class=\"skubutton\" value=\"{$value}\" />";
		$html .= "<input name=\"usces_referer\" type=\"hidden\" value=\"" . $_SERVER['REQUEST_URI'] . "\" />\n";
	}

	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

function usces_direct_intoCart($post_id, $sku, $force=false, $value=NULL, $options=NULL, $out = '') {
	global $usces;
	if( empty($value) )
		$value = __('Add To Cart', 'usces');
	$datas = $usces->get_skus( $post_id, 'ARRAY_A' );
	$zaikonum = $datas[$sku]['zaikonum'];
	$zaiko = $datas[$sku]['zaiko'];
	$gptekiyo = $datas[$sku]['gptekiyo'];
	$skuPrice = $datas[$sku]['price'];
	$sku = esc_attr($sku);

	$html = "<form action=\"" . USCES_CART_URL . "\" method=\"post\" name=\"" . $post_id."-". $sku . "\">\n";
	$html .= "<input name=\"zaikonum[{$post_id}][{$sku}]\" type=\"hidden\" id=\"zaikonum[{$post_id}][{$sku}]\" value=\"{$zaikonum}\" />\n";
	$html .= "<input name=\"zaiko[{$post_id}][{$sku}]\" type=\"hidden\" id=\"zaiko[{$post_id}][{$sku}]\" value=\"{$zaiko}\" />\n";
	$html .= "<input name=\"gptekiyo[{$post_id}][{$sku}]\" type=\"hidden\" id=\"gptekiyo[{$post_id}][{$sku}]\" value=\"{$gptekiyo}\" />\n";
	$html .= "<input name=\"skuPrice[{$post_id}][{$sku}]\" type=\"hidden\" id=\"skuPrice[{$post_id}][{$sku}]\" value=\"{$skuPrice}\" />\n";
	$html .= "<a name=\"cart_button\"></a><input name=\"inCart[{$post_id}][{$sku}]\" type=\"submit\" id=\"inCart[{$post_id}][{$sku}]\" class=\"skubutton\" value=\"{$value}\" />";
	$html .= "<input name=\"usces_referer\" type=\"hidden\" value=\"" . $_SERVER['REQUEST_URI'] . "\" />\n";
	if( $force )
		$html .= "<input name=\"usces_force\" type=\"hidden\" value=\"incart\" />\n";
	$html = apply_filters('usces_filter_single_item_inform', $html);
	$html .= "</form>";
	$html .= '<div class="direct_error_message">' . usces_singleitem_error_message($post_id, $sku, 'return') . '</div>'."\n";

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
		$html .= "\t\t\t<th>" . esc_html($label) . "</th>\n";
		if( $usces->options['insert_unit'] === false || $usces->options['insert_unit'] == 'plural' )
			$html .= "\t\t\t<th class='sku_skuquantity'>" . __('Quantity','usces') . "</th>\n";
		$html .= "\t\t\t<th class='sku_button'>&nbsp;</th>\n";
	$html .= "\t\t</tr>\n";
	$html .= "\t</thead>\n";
	$html .= "\t<tbody>\n";
	foreach ($rows as $values){
		$html .= "\t\t<tr>\n";
		foreach ($colums as $subkey => $label)
			$html .= "\t\t\t<td class='sku_{$subkey}'>" . esc_html($values[$subkey]) . "</td>\n";
		if( $usces->options['insert_unit'] === false || $usces->options['insert_unit'] == 'plural' )
			$html .= "\t\t\t<td class='sku_skuquantity'><input name=\"quant[{$post_id}][" . esc_attr($values['sku']) . "]\" type=\"text\" id=\"quant[{$post_id}][" . esc_attr($values['sku']) . "]\" class=\"skuquantity\" value=\"\" /></td>\n";
		$html .= "\t\t\t<td class='sku_button'><input name=\"inCart[{$post_id}][" . esc_attr($values['sku']) . "]\" type=\"submit\" id=\"inCart[{$post_id}][" . esc_attr($values['sku']) . "]\" class=\"skubutton\" value=\"" . esc_attr($buttonValue) . "\" /></td>\n";
		$html .= "\t\t</tr>\n";
	}
	$html .= "\t</tbody>\n";
	$html .= "</table>\n";
	
	echo $html;
}

function usces_the_itemImage($number = 0, $width = 60, $height = 60, $post = '', $out = '', $media = 'item' ) {
	global $usces;
	if($post == '') global $post;

	$post_id = $post->ID;
	
	$code =  get_post_custom_values('_itemCode', $post_id);
	if(!$code) return false;
	
	$name = get_post_custom_values('_itemName', $post_id);
	
	if( 0 == $number ){
		$pictid = $usces->get_mainpictid($code[0]);
		$html = wp_get_attachment_image( $pictid, array($width, $height), true );//'<img src="#" height="60" width="60" alt="" />';
		if( 'item' == $media ){
			$alt = 'alt="'.esc_attr($code[0]).'"';
			$alt = apply_filters('usces_filter_img_alt', $alt, $post_id, $pictid);
			$html = preg_replace('/alt=\"[^\"]*\"/', $alt, $html);
			$title = 'title="'.esc_attr($name[0]).'"';
			$title = apply_filters('usces_filter_img_title', $title, $post_id, $pictid);
			$html = preg_replace('/title=\"[^\"]+\"/', $title, $html);
		}
	}else{
		$pictids = $usces->get_pictids($code[0]);
		$html = wp_get_attachment_image( $pictids[$number], array($width, $height), false );//'<img src="#" height="60" width="60" alt="" />';
		if( 'item' == $media ){
			$alt = 'alt="'.esc_attr($code[0]).'"';
			$alt = apply_filters('usces_filter_img_alt', $alt, $post_id, $pictids[$number]);
			$html = preg_replace('/alt=\"[^\"]*\"/', $alt, $html);
			$title = 'title="'.esc_attr($name[0]).'"';
			$title = apply_filters('usces_filter_img_title', $title, $post_id, $pictids[$number]);
			$html = preg_replace('/title=\"[^\"]+\"/', $title, $html);
		}
	}
	if($out == 'return'){
		return $html;
	}else{
		echo $html;
	}
}

function usces_the_itemImageURL($number = 0, $out = '', $post = '' ) {
	global $usces;
	if($post == '') global $post;
	$post_id = $post->ID;
	
	$code =  get_post_custom_values('_itemCode', $post_id);
	if(!$code) return false;
	$name = get_post_custom_values('_itemName', $post_id);
	if( 0 == $number ){
		$pictid = $usces->get_mainpictid($code[0]);
		$html = wp_get_attachment_url( $pictid );
	}else{
		$pictids = $usces->get_pictids($code[0]);
		$html = wp_get_attachment_url( $pictids[$number] );
	}
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

	if( 0 == $number ){
		$pictid = $usces->get_mainpictid($code[0]);
		$attach_ob = get_post($pictid);
	}else{
		$pictids = $usces->get_pictids($code[0]);
		$attach_ob = get_post($pictids[$number]);
	}
	$excerpt = $attach_ob->post_excerpt;

	if($out == 'return'){
		return $excerpt;
	}else{
		echo esc_html($excerpt);
	}
}

function usces_the_itemImageDescription($number = 0, $post = '', $out = '' ) {
	global $usces;
	if($post == '') global $post;

	$post_id = $post->ID;
	
	$code =  get_post_custom_values('_itemCode', $post_id);
	if(!$code) return false;
	
	$name = get_post_custom_values('_itemName', $post_id);
	
	if( 0 == $number ){
		$pictid = $usces->get_mainpictid($code[0]);
		$attach_ob = get_post($pictid);
	}else{
		$pictids = $usces->get_pictids($code[0]);
		$attach_ob = get_post($pictids[$number]);
	}
	$excerpt = $attach_ob->post_content;

	if($out == 'return'){
		return $excerpt;
	}else{
		echo esc_html($excerpt);
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

function usces_the_itemOptName($out = '') {
	global $usces;

	if($out == 'return'){
		return $usces->itemopt['key'];
	}else{
		echo esc_html($usces->itemopt['key']);
	}
}

function usces_the_itemOption( $name, $label = '#default#', $out = '' ) {
	global $post, $usces;
	$post_id = $post->ID;
	$session_value = isset( $_SESSION['usces_singleitem']['itemOption'][$post_id][$usces->itemsku['key']][$name] ) ? $_SESSION['usces_singleitem']['itemOption'][$post_id][$usces->itemsku['key']][$name] : NULL;
	
	if($label == '#default#')
		$label = $name;
	$key = '_iopt_' . $name;
	$value = get_post_custom_values($key, $post_id);
	if(!$value) return false;
	$values = maybe_unserialize($value[0]);
	$means = (int)$values['means'];
	$essential = (int)$values['essential'];

	$html = '';
	$sku = esc_attr($usces->itemsku['key']);
	$name = esc_attr($name);
	$label = esc_attr($label);
//20110715ysk start 0000208
	$html .= "\n<label for='itemOption[{$post_id}][{$sku}][{$name}]' class='iopt_label'>{$label}</label>\n";
//20110715ysk end
//20100914ysk start
	//if($means < 2){
	switch($means) {
	case 0://Single-select
	case 1://Multi-select
//20100914ysk end
		$selects = explode("\n", $values['value'][0]);
		$multiple = ($means === 0) ? '' : ' multiple';
		$multiple_array = ($means == 0) ? '' : '[]';//20110629ysk 0000190
		//$html .= "\n<label for='itemOption[{$post_id}][{$sku}][{$name}]' class='iopt_label'>{$label}</label>\n";
//20110629ysk start 0000190
		//$html .= "\n<select name='itemOption[{$post_id}][{$sku}][{$name}]' id='itemOption[{$post_id}][{$sku}][{$name}]' class='iopt_select'{$multiple} onKeyDown=\"if (event.keyCode == 13) {return false;}\">\n";
		$html .= "\n<select name='itemOption[{$post_id}][{$sku}][{$name}]{$multiple_array}' id='itemOption[{$post_id}][{$sku}][{$name}]' class='iopt_select'{$multiple} onKeyDown=\"if (event.keyCode == 13) {return false;}\">\n";
//20110629ysk end
		if($essential == 1){
			if(  '#NONE#' == $session_value || NULL == $session_value ) 
				$selected = ' selected="selected"';
			else
				$selected = '';
			$html .= "\t<option value='#NONE#'{$selected}>" . __('Choose','usces') . "</option>\n";
		}
		$i=0;
		foreach($selects as $v) {
			if( ($i == 0 && $essential == 0 && NULL == $session_value) || esc_attr($v) == $session_value ) 
				$selected = ' selected="selected"';
			else
				$selected = '';
			$html .= "\t<option value='" . esc_attr($v) . "'{$selected}>" . esc_html($v) . "</option>\n";
			$i++;
		}
		$html .= "</select>\n";
//20100914ysk start
	//}else{
		break;
	case 2://Text
//20100914ysk end
		$html .= "\n<input name='itemOption[{$post_id}][{$sku}][{$name}]' type='text' id='itemOption[{$post_id}][{$sku}][{$name}]' class='iopt_text' onKeyDown=\"if (event.keyCode == 13) {return false;}\" value=\"" . esc_attr($session_value) . "\" />\n";
//20100914ysk start
		break;
	case 5://Text-area
		$html .= "\n<textarea name='itemOption[{$post_id}][{$sku}][{$name}]' id='itemOption[{$post_id}][{$sku}][{$name}]' class='iopt_textarea' />" . esc_attr($session_value) . "</textarea>\n";
		break;
//20100914ysk end
	}
	
	$html = apply_filters('usces_filter_the_itemOption', $html, $values, $name, $label, $post_id, $sku);
	
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
	$name = esc_attr($flag) . '[pref]';
	$pref = $usces_entries[$flag]['pref'];
	if( 'member' == $flag)
		$pref = $usces_members['pref'];
	
	$html = "<select name='" . esc_attr($name) . "' id='pref' class='pref'>\n";
//	$prefs = get_option('usces_pref');
//20110331ysk start
	//$prefs = $usces->options['province'];
	$prefs = get_usces_states(usces_get_local_addressform());
//20110331ysk end
	foreach($prefs as $value) {
		$selected = ($pref == $value) ? ' selected="selected"' : '';
		$html .= "\t<option value='" . esc_attr($value) . "'{$selected}>" . esc_html($value) . "</option>\n";
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
	echo esc_html($usces->options['company_name']);
}

function usces_the_zip_code(){
	global $usces;
	echo esc_html($usces->options['zip_code']);
}

function usces_the_address1(){
	global $usces;
	echo esc_html($usces->options['address1']);
}

function usces_the_address2(){
	global $usces;
	echo esc_html($usces->options['address2']);
}

function usces_the_tel_number(){
	global $usces;
	echo esc_html($usces->options['tel_number']);
}

function usces_the_fax_number(){
	global $usces;
	echo esc_html($usces->options['fax_number']);
}

function usces_the_inquiry_mail(){
	global $usces;
	echo esc_html($usces->options['inquiry_mail']);
}

function usces_the_postage_privilege(){
	global $usces;
	echo esc_html($usces->options['postage_privilege']);
}

function usces_the_start_point(){
	global $usces;
	echo esc_html($usces->options['start_point']);
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
	
	$cart = $usces->cart->get_cart();
	$charging_type = $usces->getItemChargingType($cart[0]['post_id']);
	$html = "<dl>\n";
	$list = '';
	$payment_ct = count($usces->options['payment_method']);
	foreach ($usces->options['payment_method'] as $id => $payments) {
		if( 'continue' == $charging_type ){
			//if( 'acting' != substr($payments['settlement'], 0, 6) )
//20110412ysk start
			if( 'acting_remise_card' != $payments['settlement'] && 'acting_paypal_ec' != $payments['settlement']) {
				$payment_ct--;
				continue;
			}
			//if( 'on' !== $usces->options['acting_settings']['remise']['continuation'] && 'acting_remise_card' == $payments['settlement'])
			//	continue;
			if( 'on' !== $usces->options['acting_settings']['remise']['continuation'] && 'acting_remise_card' == $payments['settlement']) {
				$payment_ct--;
				continue;
			} elseif( 'on' !== $usces->options['acting_settings']['paypal']['continuation'] && 'acting_paypal_ec' == $payments['settlement']) {
				$payment_ct--;
				continue;
			}
//20110412ysk end
		}
		if( $payments['name'] != '' ) {
			$module = trim($payments['module']);
			if( '' != $value ){
				$checked = ($payments['name'] == $value) ? ' checked' : '';
			}else if( 1 === $payment_ct ){
				$checked = ' checked';
			}else{
				$checked = '';
			}
			if( (empty($module) || !file_exists($usces->options['settlement_path'] . $module)) && $payments['settlement'] == 'acting' ) {
				$checked = '';
				$list .= "\t".'<dt><label for="payment_name_' . $id . '"><input name="offer[payment_name]" id="payment_name_' . $id . '" type="radio" value="'.esc_attr($payments['name']).'"' . $checked . ' disabled onKeyDown="if (event.keyCode == 13) {return false;}" />'.esc_attr($payments['name'])."</label> <b> (" . __('cannot use this payment method now.','usces') . ") </b></dt>\n";
			}else{
				$list .= "\t".'<dt><label for="payment_name_' . $id . '"><input name="offer[payment_name]" id="payment_name_' . $id . '" type="radio" value="'.esc_attr($payments['name']).'"' . $checked . ' onKeyDown="if (event.keyCode == 13) {return false;}" />'.esc_attr($payments['name'])."</label></dt>\n";
			}
			$list .= "\t<dd>{$payments['explanation']}</dd>\n";
		}
	}

	$html .= $list . "</dl>\n";
	
	if( empty($list) )
		$html = __('Not yet ready for the payment method. Please refer to a manager.', 'usces')."\n";
	
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
	$html = '<select name="offer[delivery_method]"  id="delivery_method_select" class="delivery_time" onKeyDown="if (event.keyCode == 13) {return false;}">'."\n";
	foreach ($deli_id as $id) {
		$index = $usces->get_delivery_method_index($id);
		$selected = ($id == $value) ? ' selected="selected"' : '';
		$html .= "\t<option value='{$id}'{$selected}>" . esc_html($usces->options['delivery_method'][$index]['name']) . "</option>\n";
	}

	$html .= "</select>\n";
	
	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}
//20101208ysk start
function usces_the_delivery_date( $value = '', $out = '' ){
	global $usces;

	$html = "<select name='offer[delivery_date]' id='delivery_date_select' class='delivery_date'>\n";
	$html .= "</select>\n";

	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}
//20101208ysk end
function usces_the_delivery_time( $value = '', $out = '' ){
	global $usces;
	//if( $usces->options['delivery_time'] == '' ) return;
	
	//$array = explode("\n", $usces->options['delivery_time']);
//20101208ysk start
	//$html = "<select name='offer[delivery_time]' id='delivery_time_select' class='delivery_time'>\n";
	$html = "<div id='delivery_time_limit_message'></div>\n";
	$html .= "<select name='offer[delivery_time]' id='delivery_time_select' class='delivery_time'>\n";
//20101208ysk end

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
			echo esc_html($startdate);
		}elseif( 'datetime' == $kind ) {
			echo esc_html($startdate . ' ' . $starttime);
		}
	} elseif ( 'end' == $flag ) {
		if( 'date' == $kind ) {
			echo esc_html($enddate);
		}elseif( 'datetime' == $kind ) {
			echo esc_html($enddate . ' ' . $endtime);
		}
	}
}


function usces_the_confirm() {
	global $usces;
	
	$usces->display_cart_confirm();
}

function usces_inquiry_condition() {
	global $error_message, $reserve, $inq_name, $inq_mailaddress, $inq_contents;
	require(USCES_PLUGIN_DIR.'/includes/inquiry_condition.php');
}

function usces_the_inquiry_form() {
	global $usces;
	$error_message = '';
	if( isset($_POST['inq_name']) && '' != trim($_POST['inq_name']) ) {
		$inq_name = trim($_POST['inq_name']);
	}else{
		$inq_name = '';
		if($usces->page == 'deficiency')
			$error_message .= __('Please input your name.', 'usces') . "<br />";
	}
	if( isset($_POST['inq_mailaddress']) && is_email(trim($_POST['inq_mailaddress'])) ) {
		$inq_mailaddress = trim($_POST['inq_mailaddress']);
	}elseif( isset($_POST['inq_mailaddress']) && !is_email(trim($_POST['inq_mailaddress'])) ) {
		$inq_mailaddress = trim($_POST['inq_mailaddress']);
		if($usces->page == 'deficiency')
			$error_message .= __('E-mail address is not correct', 'usces') . "<br />";
	}else{
		$inq_mailaddress = '';
		if($usces->page == 'deficiency')
			$error_message .= __('Please input your e-mail address.', 'usces') . "<br />";
	}
	if( isset($_POST['inq_contents']) && '' != trim($_POST['inq_contents']) ) {
		$inq_contents = trim($_POST['inq_contents']);
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
<td><input name="inq_name" type="text" class="inquiry_name" value="<?php echo esc_attr($inq_name); ?>" /></td>
</tr>
<tr>
<th scope="row"><?php _e('e-mail adress','usces') ?></th>
<td><input name="inq_mailaddress" type="text" class="inquiry_mailaddress" value="<?php echo esc_attr($inq_mailaddress); ?>" /></td>
</tr>
<tr>
<th scope="row"><?php _e('contents','usces') ?></th>
<td><textarea name="inq_contents" class="inquiry_contents"><?php echo esc_attr($inq_contents); ?></textarea></td>
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
		echo '<a href="' . USCES_LOGIN_URL . '" class="usces_login_a">' . apply_filters('usces_filter_loginlink_label', __('Log-in','usces')) . '</a>';
	else
		echo '<a href="' . USCES_LOGOUT_URL . '" class="usces_logout_a">' . apply_filters('usces_filter_logoutlink_label', __('Log out','usces')) . '</a>';
}

function usces_is_login() {
	global $usces;
	
	if( false === $usces->is_member_logged_in() )
		$res = false;
	else
		$res = true;
		
	return $res;
}

function usces_the_member_name( $out = '') {
	global $usces;
	$usces->get_current_member();
	$res = esc_html($usces->current_member['name']);
	if( $out == 'return' ){
		return $res;
	}else{
		echo $res;
	}
	
}

function usces_the_member_point( $out = '' ) {
	global $usces;
	
	if( !$usces->is_member_logged_in() ) return;
	
	$member = $usces->get_member();
	if( $out == 'return' ){
		return $member['point'];
	}else{
		echo number_format($member['point']);
	}
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
function usces_get_assistance_ids($post_id) {
	global $usces;
	$names = $usces->get_tag_names($post_id);
	$ids = array();
	foreach ( $names as $itemname )
		$ids[] = $usces->get_ID_byItemName($itemname, 'publish');

	return $ids;
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
			echo esc_html($value['name']);
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
			echo esc_html($value['pass']);
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
		$list .= "<tr><th>" . esc_html($pref) . "</th>\n";
		$list .= "<td class='rightnum'>" . number_format($value) . "</td>\n";
		$list .= "</tr>\n";
	}
	echo $list;
}
function usces_sc_shipping_charge() {
	global $usces;
	echo esc_html($usces->sc_shipping_charge());
}
function usces_sc_postage_privilege() {
	global $usces;
	echo esc_html($usces->sc_postage_privilege());
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
			$disp_text = apply_filters('usces_widget_bestseller_auto_text', esc_html($post->post_title), $ids[$i]);
			$list = "<li><a href='" . get_permalink($ids[$i]) . "'>" . $disp_text . "</a></li>\n";
			$htm .= apply_filters('usces_filter_bestseller', $list, $ids[$i], $i);
		}
	}
	echo $htm;
}

function usces_list_post( $slug, $rownum, $widget_id=NULL ){
	global $usces, $post;
	usces_remove_filter();
	
	$li = '';
	$infolist = new wp_query( array('category_name'=>$slug, 'post_status'=>'publish', 'posts_per_page'=>$rownum, 'order'=>'DESC', 'orderby'=>'date') );
	if( NULL != $widget_id && $infolist->have_posts() ){
		remove_filter( 'excerpt_length', 'welcart_excerpt_length' );
		remove_filter( 'excerpt_mblength', 'welcart_excerpt_mblength' );
		remove_filter( 'excerpt_more', 'welcart_auto_excerpt_more' );
		if( function_exists('welcart_widget_post_excerpt_length_'.$widget_id) )
			add_filter( 'excerpt_length', 'welcart_widget_post_excerpt_length_'.$widget_id );
		if( function_exists('welcart_widget_post_excerpt_mblength_'.$widget_id) )
			add_filter( 'excerpt_mblength', 'welcart_widget_post_excerpt_mblength_'.$widget_id );
	}
	while ($infolist->have_posts()) {
		$infolist->the_post();
		$list = "<li>\n";
		$list .= "<div class='title'><a href='" . get_permalink($post->ID) . "'>" . get_the_title() . "</a></div>\n";
		$list .= "<p>" . get_the_excerpt() . "</p>\n";
		$list .= "</li>\n";
		$li .= apply_filters( 'usces_filter_widget_post', $list, $post, $slug);
	}
	wp_reset_query();
	usces_reset_filter();
	if( NULL != $widget_id && $infolist->have_posts() ){
		add_filter( 'excerpt_length', 'welcart_excerpt_length' );
		add_filter( 'excerpt_mblength', 'welcart_excerpt_mblength' );
		add_filter( 'excerpt_more', 'welcart_auto_excerpt_more' );
	}
	echo $li;
}

function usces_categories_checkbox($output=''){
	global $usces;
	$htm = '';
	$retcats = usces_search_categories();
	$parent_id = apply_filters('usces_search_categories_checkbox_parent', USCES_ITEM_CAT_PARENT_ID);
	$categories =  get_categories('child_of='.$parent_id . "&hide_empty=0&orderby=ID"); 
	foreach ($categories as $cat) {
		$children =  get_categories('child_of='.$cat->term_id . "&hide_empty=0&orderby=" . $usces->options['fukugo_category_orderby'] . "&order=" . $usces->options['fukugo_category_order']);
		if(!empty($children)){
			$htm .= "<fieldset class='catfield-" . $cat->term_id . "'><legend>" . $cat->cat_name . "</legend><ul>\n";
			foreach ($children as $child) {
				$checked = in_array($child->term_id, $retcats) ? " checked='checked'" : "";
				$htm .= "<li><input name='category[".$child->term_id."]' type='checkbox' id='category[".$child->term_id."]' value='".$child->term_id."'".$checked." /><label for='category[".$child->term_id."]' class='catlabel-" . $child->term_id . "'>".esc_html($child->cat_name)."</label></li>\n";
			}
			$htm .= "</ul></fieldset>\n";
		}
	}
	$htm = apply_filters('usces_filter_categories_checkbox', $htm, $categories);
	
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
		echo esc_html($name);
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

	echo esc_html($usces->options['copyright']);
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

function usces_get_item_custom( $post_id, $type = 'list', $out = '' ){
	global $usces;
	$cfields = $usces->get_post_custom($post_id);
	switch( $type ){
		case 'list':
			$list = '';
			$html = '<ul class="item_custom_field">'."\n";
			foreach( $cfields as $key => $value ){
				if( 'wccs_' == substr($key, 0, 5) ){
					$list .= '<li>' . esc_html(substr($key, 5)) . ' : ' . nl2br(esc_html($value[0])) . '</li>'."\n";
				}
			}
			if(empty($list)){
				$html = '';
			}else{
				$html .= $list . '</ul>'."\n";
			}
			break;

		case 'table':
			$list = '';
			$html = '<table class="item_custom_field">'."\n";
			foreach($cfields as $key => $value){
				if( 'wccs_' == substr($key, 0, 5) ){
					$list .= '<tr><th>' . esc_html(substr($key, 5)) . '</th><td>' . nl2br(esc_html($value[0])) . '</td></tr>'."\n";
				}
			}
			if(empty($list)){
				$html = '';
			}else{
				$html .= $list . '</table>'."\n";
			}
			break;

		case 'notag':
			$list = '';
			foreach($cfields as $key => $value){
				if( 'wccs_' == substr($key, 0, 5) ){
					$list .= esc_html(substr($key, 5)) . ' : ' . nl2br(esc_html($value[0])) . "\r\n";
				}
			}
			if(empty($list)){
				$html = '';
			}else{
				$html = $list;
			}
			break;
	}
	$html = apply_filters( 'usces_filter_item_custom', $html, $post_id);
	
	if( 'return' == $out){
		return $html;
	}else{
		echo $html;
	}
}

function usces_settle_info_field( $order_id, $type='nl', $out='echo' ){
	global $usces;
	$str = '';
	$fields = $usces->get_settle_info_field( $order_id );
//20101018ysk start
	$acting = $fields['acting'];
	foreach($fields as $key => $value){
		//if( 'acting' == $key )
		//	$acting = $value;
			
		//if( !in_array($key, array(
		//						'order_no','tracking_no','status','error_message','money',
		//						'pay_cvs', 'pay_no1', 'pay_no2', 'pay_limit', 'error_code',
		//						'settlement_id','RECDATE','JOB_ID','S_TORIHIKI_NO','TOTAL','CENDATE')) ){
		if( !in_array($key, array(
								'acting','order_no','tracking_no','status','error_message','money',
								'pay_cvs', 'pay_no1', 'pay_no2', 'pay_limit', 'error_code',
								'settlement_id','RECDATE','JOB_ID','S_TORIHIKI_NO','TOTAL','CENDATE',
								'gid', 'rst', 'ap', 'ec', 'god', 'ta', 'cv', 'no', 'cu', 'mf', 'nk', 'nkd', 'bank', 'exp')) ){
//20101018ysk end
			continue;
		}

		switch($acting){
			case 'zeus_bank':
				if( 'status' == $key){
					if( '01' == $value ){
						$value = '受付中';
					}elseif( '02' == $value ){
						$value = '未入金';
					}elseif( '03' == $value ){
						$value = '入金済';
					}elseif( '04' == $value ){
						$value = 'エラー';
					}elseif( '05' == $value ){
						$value = '入金失敗';
					}
				}elseif( 'error_message' == $key){
					if( '0002' == $value ){
						$value = '入金不足';
					}elseif( '0003' == $value ){
						$value = '過剰入金';
					}
				}
				break;
			case 'zeus_conv':
				if( 'pay_cvs' == $key){
					if( 'D001' == $value ){
						$value = 'セブンイレブン';
					}elseif( 'D002' == $value ){
						$value = 'ローソン';
					}elseif( 'D030' == $value ){
						$value = 'ファミリーマート';
					}elseif( 'D040' == $value ){
						$value = 'サークルKサンクス';
					}elseif( 'D015' == $value ){
						$value = 'セイコーマート';
					}
				}elseif( 'status' == $key){
					if( '01' == $value ){
						$value = '未入金';
					}elseif( '02' == $value ){
						$value = '申込エラー';
					}elseif( '03' == $value ){
						$value = '期日切';
					}elseif( '04' == $value ){
						$value = '入金済';
					}elseif( '05' == $value ){
						$value = '売上確定';
					}elseif( '06' == $value ){
						$value = '入金取消';
					}elseif( '11' == $value ){
						$value = 'キャンセル後入金';
					}elseif( '12' == $value ){
						$value = 'キャンセル後売上';
					}elseif( '13' == $value ){
						$value = 'キャンセル後取消';
					}
				}elseif( 'pay_limit' == $key){
					$value = substr($value, 0, 4).'年' . substr($value, 4, 2).'月' . substr($value, 6, 2).'日';
				}
				break;
//20101018ysk start
			case 'jpayment_conv':
				switch($key) {
				case 'rst':
					switch($value) {
					case '1':
						$value = 'OK'; break;
					case '2':
						$value = 'NG'; break;
					}
					break;
				case 'ap':
					switch($value) {
					case 'CPL_PRE':
						$value = 'コンビニペーパーレス決済識別コード'; break;
					case 'CPL':
						$value = '入金確定'; break;
					case 'CVS_CAN':
						$value = '入金取消'; break;
					}
					break;
				case 'cv':
					$value = esc_html(usces_get_conv_name($value));
					break;
				case 'mf':
				case 'nk':
				case 'nkd':
				case 'bank':
				case 'exp':
					continue;
					break;
				}
				break;

			case 'jpayment_bank':
				switch($key) {
				case 'rst':
					switch($value) {
					case '1':
						$value = 'OK'; break;
					case '2':
						$value = 'NG'; break;
					}
					break;
				case 'ap':
					switch($value) {
					case 'BANK':
						$value = '受付完了'; break;
					case 'BAN_SAL':
						$value = '入金完了'; break;
					}
					break;
				case 'mf':
					switch($value) {
					case '1':
						$value = 'マッチ'; break;
					case '2':
						$value = '過少'; break;
					case '3':
						$value = '過剰'; break;
					}
					break;
				case 'nkd':
					$value = substr($value, 0, 4).'年'.substr($value, 4, 2).'月'.substr($value, 6, 2).'日';
					break;
				case 'exp':
					$value = substr($value, 0, 4).'年'.substr($value, 4, 2).'月'.substr($value, 6, 2).'日';
					break;
				case 'cv':
				case 'no':
				case 'cu':
					continue;
					break;
				}
				break;
//20101018ysk end
		}
		switch($type){
			case 'nl':
				$str .= $key . ' : ' . $value . "<br />\n";
				break;
				
			case 'tr':
				$str .= '<tr><td class="label">' . $key . '</td><td>' . $value . "</td></tr>\n";
				break;
				
			case 'li':
				$str .= '<li>' . $key . ' : ' . $value . "</li>\n";
				break;
		}
	}
	if( 'return' == $out){
		return $str;
	}else{
		echo $str;
	}
}

//20100818ysk start
function usces_custom_field_input( $data, $custom_field, $position, $out = '' ) {

	$html = '';
	switch($custom_field) {
	case 'order':
		$label = 'custom_order';
		$field = 'usces_custom_order_field';
		break;
	case 'customer':
		$label = 'custom_customer';
		$field = 'usces_custom_customer_field';
		break;
	case 'delivery':
		$label = 'custom_delivery';
		$field = 'usces_custom_delivery_field';
		break;
	case 'member':
		$label = 'custom_member';
		$field = 'usces_custom_member_field';
		break;
	default:
		return;
	}

	$meta = usces_has_custom_field_meta($custom_field);

	if(!empty($meta) and is_array($meta)) {
		foreach($meta as $key => $entry) {
			if($custom_field == 'order' or $entry['position'] == $position) {
				$name = $entry['name'];
				$means = $entry['means'];
				$essential = $entry['essential'];
				$value = '';
				if(is_array($entry['value'])) {
					foreach($entry['value'] as $k => $v) {
						$value .= $v."\n";
					}
				}
				$value = trim($value);

				$e = ($essential == 1) ? '<em>' . __('*', 'usces') . '</em>' : '';
				$html .= '
					<tr>
					<th scope="row">'.$e.esc_html($name).apply_filters('usces_filter_custom_field_input_label', NULL, $key, $entry).'</th>';
				switch($means) {
					case 0://シングルセレクト
					case 1://マルチセレクト
						$selects = explode("\n", $value);
						$multiple = ($means == 0) ? '' : ' multiple';
						$multiple_array = ($means == 0) ? '' : '[]';
						$html .= '
							<td colspan="2">
							<select name="'.$label.'['.esc_attr($key).']'.$multiple_array.'" class="iopt_select"'.$multiple.'>';
						if($essential == 1) 
							$html .= '
								<option value="#NONE#">'.__('Choose','usces').'</option>';
						foreach($selects as $v) {
							$selected = ($data[$label][$key] == $v) ? ' selected' : '';
							$html .= '
								<option value="'.esc_attr($v).'"'.$selected.'>'.esc_html($v).'</option>';
						}
						$html .= '
							</select>';
						break;
					case 2://テキスト
						$html .= '
							<td colspan="2"><input type="text" name="'.$label.'['.esc_attr($key).']" size="30" value="'.esc_attr($data[$label][$key]).'" />';
						break;
					case 3://ラジオボタン
						$selects = explode("\n", $value);
						$html .= '
							<td colspan="2">';
						foreach($selects as $v) {
							$checked = ($data[$label][$key] == $v) ? ' checked' : '';
							$html .= '
							<input type="radio" name="'.$label.'['.esc_attr($key).']" value="'.esc_attr($v).'"'.$checked.'><label for="'.$label.'['.esc_attr($key).']['.esc_attr($v).']" class="iopt_label">'.esc_html($v).'</label>';
						}
						break;
					case 4://チェックボックス
						$selects = explode("\n", $value);
						$html .= '
							<td colspan="2">';
						foreach($selects as $v) {
							if(is_array($data[$label][$key])) {
								$checked = (array_key_exists($v, $data[$label][$key])) ? ' checked' : '';
							} else {
								$checked = ($data[$label][$key] == $v) ? ' checked' : '';
							}
							$html .= '
							<input type="checkbox" name="'.$label.'['.esc_attr($key).']['.esc_attr($v).']" value="'.esc_attr($v).'"'.$checked.'><label for="'.$label.'['.esc_attr($key).']['.esc_attr($v).']" class="iopt_label">'.esc_html($v).'</label>';
						}
						break;
				}
				$html .= apply_filters('usces_filter_custom_field_input_value', NULL, $key, $entry).'</td>';
				$html .= '
					</tr>';
			}
		}
	}
	
	$html = apply_filters('usces_filter_custom_field_input', $html, $data, $custom_field, $position);

	if($out == 'return') {
		return $html;
	} else {
		echo $html;
	}
}

function usces_custom_field_info( $data, $custom_field, $position, $out = '' ) {

	$html = '';
	switch($custom_field) {
	case 'order':
		$label = 'custom_order';
		$field = 'usces_custom_order_field';
		break;
	case 'customer':
		$label = 'custom_customer';
		$field = 'usces_custom_customer_field';
		break;
	case 'delivery':
		$label = 'custom_delivery';
		$field = 'usces_custom_delivery_field';
		break;
	case 'member':
		$label = 'custom_member';
		$field = 'usces_custom_member_field';
		break;
	default:
		return;
	}

	$meta = usces_has_custom_field_meta($custom_field);

	if(!empty($meta) and is_array($meta)) {
		foreach($meta as $key => $entry) {
			if($custom_field == 'order' or $entry['position'] == $position) {
				$name = $entry['name'];
				$means = $entry['means'];

				$html .= '<tr>
					<th>'.esc_html($name).'</th>
					<td>';
					switch($means) {
					case 0://シングルセレクト
					case 2://テキスト
					case 3://ラジオボタン
						$html .= esc_html($data[$label][$key]);
						break;
					case 1://マルチセレクト
					case 4://チェックボックス
						if(is_array($data[$label][$key])) {
							$c = '';
							foreach($data[$label][$key] as $v) {
								$html .= $c.esc_html($v);
								$c = ', ';
							}
						} else {
							$html .= esc_html($data[$label][$key]);
						}
						break;
					}
				$html .= '
					</td>
					</tr>';
			}
		}
	}

	$html = apply_filters('usces_filter_custom_field_info', $html, $data, $custom_field, $position);

	if($out == 'return') {
		return $html;
	} else {
		echo $html;
	}
}

function usces_admin_custom_field_input( $meta, $custom_field, $position, $out = '' ) {

	$html = '';
	switch($custom_field) {
	case 'order':
		$label = 'custom_order';
		$class = '';
		break;
	case 'customer':
		$label = 'custom_customer';
		$class = ' class="col2"';
		break;
	case 'delivery':
		$label = 'custom_delivery';
		$class = ' class="col3"';
		break;
	case 'member':
		$label = 'custom_member';
		$class = '';
		break;
	default:
		return;
	}


	if(!empty($meta) and is_array($meta)) {
		foreach($meta as $key => $entry) {
			if($custom_field == 'order' or $entry['position'] == $position) {
				$name = $entry['name'];
				$means = $entry['means'];
				$essential = $entry['essential'];
				$value = '';
				if(is_array($entry['value'])) {
					foreach($entry['value'] as $k => $v) {
						$value .= $v."\n";
					}
				}
				$value = trim($value);
				$data = $entry['data'];

				$html .= '
					<tr>
					<td class="label">'.esc_html($name).'</td>';
				switch($means) {
				case 0://シングルセレクト
				case 1://マルチセレクト
					$selects = explode("\n", $value);
					$multiple = ($means == 0) ? '' : ' multiple';
					$multiple_array = ($means == 0) ? '' : '[]';
					$html .= '
						<td'.$class.'>
						<select name="'.$label.'['.esc_attr($key).']'.$multiple_array.'" class="iopt_select"'.$multiple.'>';
					if($essential == 1) 
						$html .= '
							<option value="#NONE#">'.__('Choose','usces').'</option>';
					foreach($selects as $v) {
						$selected = ($data == $v) ? ' selected' : '';
						$html .= '
							<option value="'.esc_attr($v).'"'.$selected.'>'.esc_html($v).'</option>';
					}
					$html .= '
						</select></td>';
					break;
				case 2://テキスト
					$html .= '
						<td'.$class.'><input type="text" name="'.$label.'['.esc_attr($key).']" size="30" value="'.esc_attr($data).'" /></td>';
					break;
				case 3://ラジオボタン
					$selects = explode("\n", $value);
					$html .= '
						<td'.$class.'>';
					foreach($selects as $v) {
						$checked = ($data == $v) ? ' checked' : '';
						$html .= '
						<input type="radio" name="'.$label.'['.esc_attr($key).']" value="'.esc_attr($v).'"'.$checked.'><label for="'.$label.'['.esc_attr($key).']['.esc_attr($v).']" class="iopt_label">'.esc_html($v).'</label>';
					}
					$html .= '
						</td>';
					break;
				case 4://チェックボックス
					$selects = explode("\n", $value);
					$html .= '
						<td'.$class.'>';
					foreach($selects as $v) {
						if(is_array($data)) {
							$checked = (array_key_exists($v, $data)) ? ' checked' : '';
						} else {
							$checked = ($data == $v) ? ' checked' : '';
						}
						$html .= '
						<input type="checkbox" name="'.$label.'['.esc_attr($key).']['.esc_attr($v).']" value="'.esc_attr($v).'"'.$checked.'><label for="'.$label.'['.esc_attr($key).']['.esc_attr($v).']" class="iopt_label">'.esc_html($v).'</label>';
					}
					$html .= '
						</td>';
					break;
				}
				$html .= '
					</tr>';
			}
		}
	}

	if($out == 'return') {
		return $html;
	} else {
		echo $html;
	}
}

function has_custom_customer_field_essential() {

	$mes = '';
	$essential = array();

	$csmb_meta = usces_has_custom_field_meta('member');
	if(!empty($csmb_meta) and is_array($csmb_meta)) {
		foreach($csmb_meta as $key => $entry) {
			if($entry['essential'] == 1) {
				$essential[$key] = $key;
			}
		}
	}
	if(!empty($essential)) {
		$cscs_meta = usces_has_custom_field_meta('customer');
		if(!empty($cscs_meta) and is_array($cscs_meta)) {
			foreach($cscs_meta as $key => $entry) {
				if($entry['essential'] == 1) {
					if(!array_key_exists($key, $essential)) {
						if($entry['means'] == 2) {//Text
							$mes .= __(esc_html($entry['name']).'を入力してください。', 'usces')."<br />";
						} else {
							$mes .= __(esc_html($entry['name']).'を選択してください。', 'usces')."<br />";
						}
					}
				}
			}
		}
	}
	return $mes;
}
//20100818ysk end

function usces_order_discount( $out = '' ){
	global $usces;
	$res = abs($usces->get_order_discount());
	
	if($out == 'return') {
		return $res;
	} else {
		echo number_format($res);
	}
}

function usces_item_discount( $out = '', $post_id = '', $sku = '' ){
	global $usces, $post;
	
	if( '' == $post_id )
		$post_id = $post->ID;
	if( '' == $sku )
		$sku = $usces->itemsku['key'];
		
	$res = $usces->getItemDiscount($post_id, $sku);
	
	if($out == 'return') {
		return $res;
	} else {
		echo number_format($res);
	}
}

function usces_singleitem_error_message($post_id, $skukey, $out = ''){
	if($out == 'return') {
		return $_SESSION['usces_singleitem']['error_message'][$post_id][$skukey];
	} else {
		echo $_SESSION['usces_singleitem']['error_message'][$post_id][$skukey];
	}
}

function usces_crform( $float, $symbol_pre = true, $symbol_post = true, $out = '', $seperator_flag = true ) {
	global $usces;
	$price = esc_html($usces->get_currency($float, $symbol_pre, $symbol_post, $seperator_flag ));
	$res = apply_filters('usces_filter_crform', $price, $float);
	
	if($out == 'return'){
		return $res;
	}else{
		echo $res;
	}
}

function usces_memberinfo( $key, $out = '' ){
	global $usces;
	$info = $usces->get_member();

	if( empty($key) ) return $info;
	
	switch ($key){
		case 'registered':
			$res = mysql2date(__('Mj, Y', 'usces'), $info['registered']);
			break;
		default:
			$res = $info[$key];
	}
	
	if($out == 'return'){
		return $res;
	}else{
		echo esc_html($res);
	}
}

function usces_localized_name( $Familly_name, $Given_name, $out = '' ){
	global $usces_settings, $usces;
	
	$options = get_option('usces');
	$form = $options['system']['addressform'];
	if( $usces_settings['nameform'][$form] ){
		$res = $Given_name . ' ' . $Familly_name;
	}else{
		$res = $Familly_name . ' ' . $Given_name;
	}
	
	if($out == 'return'){
		return $res;
	}else{
		echo esc_html($res);
	}
}

function usces_member_history(){
	global $usces;
	
	$usces_members = $usces->get_member();
	$usces_member_history = $usces->get_member_history($usces_members['ID']);
	$colspan = usces_is_membersystem_point() ? 9 : 7;

	$html .= '<table>';
	if ( !count($usces_member_history) ) {
		$html .= '<tr>
		<td>' . __('There is no purchase history for this moment.', 'usces') . '</td>
		</tr>';
	}
	foreach ( $usces_member_history as $umhs ) {
		$cart = $umhs['cart'];
		$html .= '<tr>
			<th class="historyrow">' . __('Order number', 'usces') . '</th>
			<th class="historyrow">' . __('Purchase date', 'usces') . '</th>
			<th class="historyrow">' . __('Purchase price', 'usces') . '</th>';
		if( usces_is_membersystem_point() ){
			$html .= '<th class="historyrow">' . __('Used points', 'usces') . '</th>';
		}
		$html .= '<th class="historyrow">' . __('Special Price', 'usces') . '</th>
			<th class="historyrow">' . __('Shipping', 'usces') . '</th>
			<th class="historyrow">' . __('C.O.D', 'usces') . '</th>
			<th class="historyrow">' . __('consumption tax', 'usces') . '</th>';
		if( usces_is_membersystem_point() ){
			$html .= '<th class="historyrow">' . __('Acquired points', 'usces') . '</th>';
		}
		$html .= '</tr>
			<tr>
			<td class="rightnum">' . $umhs['ID'] . '</td>
			<td class="date">' . $umhs['date'] . '</td>
			<td class="rightnum">' . usces_crform(($usces->get_total_price($cart)-$umhs['usedpoint']+$umhs['discount']+$umhs['shipping_charge']+$umhs['cod_fee']+$umhs['tax']), true, false, 'return') . '</td>';
		if( usces_is_membersystem_point() ){
			$html .= '<td class="rightnum">' . number_format($umhs['usedpoint']) . '</td>';
		}
		$html .= '<td class="rightnum">' . usces_crform($umhs['discount'], true, false, 'return') . '</td>
			<td class="rightnum">' . usces_crform($umhs['shipping_charge'], true, false, 'return') . '</td>
			<td class="rightnum">' . usces_crform($umhs['cod_fee'], true, false, 'return') . '</td>
			<td class="rightnum">' . usces_crform($umhs['tax'], true, false, 'return') . '</td>';
		if( usces_is_membersystem_point() ){
			$html .= '<td class="rightnum">' . number_format($umhs['getpoint']) . '</td>';
		}
		$html .= '</tr>';
		$html .= apply_filters('usces_filter_member_history_header', NULL, $umhs);
		$html .= '<tr>
			<td class="retail" colspan="' . $colspan . '">
				<table id="retail_table_' . $umhs['ID'] . '" class="retail">';
		$history_cart_head = '<tr>
				<th scope="row" class="num">No.</th>
				<th class="thumbnail">&nbsp;</th>
				<th>' . __('Items', 'usces') . '</th>
				<th class="price ">' . __('Unit price', 'usces') . '</th>
				<th class="quantity">' . __('Quantity', 'usces') . '</th>
				<th class="subtotal">' . __('Amount', 'usces') . '</th>
				</tr>';
		$html .= apply_filters('usces_filter_history_cart_head', $history_cart_head, $umhs);
				
		for($i=0; $i<count($cart); $i++) { 
			$cart_row = $cart[$i];
			$post_id = $cart_row['post_id'];
			$sku = $cart_row['sku'];
			$quantity = $cart_row['quantity'];
			$options = $cart_row['options'];
			$itemCode = $usces->getItemCode($post_id);
			$itemName = $usces->getItemName($post_id);
			$cartItemName = $usces->getCartItemName($post_id, $sku);
			//$skuPrice = $usces->getItemPrice($post_id, $sku);
			$skuPrice = $cart_row['price'];
			$pictid = $usces->get_mainpictid($itemCode);
			$optstr =  '';
			if( is_array($options) && count($options) > 0 ){
				$optstr = '';
				foreach($options as $key => $value){
//20110629ysk start 0000190
					//f( !empty($key) )
					//	$optstr .= esc_html($key) . ' : ' . nl2br(esc_html(urldecode($value))) . "<br />\n"; 
					if( !empty($key) ) {
						if(is_array($value)) {
							$c = '';
							$optstr .= esc_html($key) . ' : '; 
							foreach($value as $v) {
								$optstr .= $c.esc_html(nl2br(esc_html(urldecode($v))));
								$c = ', ';
							}
							$optstr .= "<br />\n"; 
						} else {
							$optstr .= esc_html($key) . ' : ' . nl2br(esc_html(urldecode($value))) . "<br />\n"; 
						}
					}
//20110629ysk end
				}
				$optstr = apply_filters( 'usces_filter_option_history', $optstr, $options);
			}
				
			$history_cart_row = '<tr>
				<td>' . ($i + 1) . '</td>
				<td><a href="' . get_permalink($post_id) . '">' . wp_get_attachment_image( $pictid, array(60, 60), true ) . '</a></td>
				<td class="aleft"><a href="' . get_permalink($post_id) . '">' . esc_html($cartItemName) . '<br />' . $optstr . '</a>' . apply_filters('usces_filter_history_item_name', NULL, $umhs, $cart_row, $i) . '</td>
				<td class="rightnum">' . usces_crform($skuPrice, true, false, 'return') . '</td>
				<td class="rightnum">' . number_format($cart_row['quantity']) . '</td>
				<td class="rightnum">' . usces_crform($skuPrice * $cart_row['quantity'], true, false, 'return') . '</td>
				</tr>';
			$html .= apply_filters('usces_filter_history_cart_row', $history_cart_row, $umhs, $cart_row, $i);
		}
		$html .= '</table>
			</td>
			</tr>';
	}
	
	$html .= '</table>';

	echo $html;
}

function usces_newmember_button($member_regmode){
	$html = '<input name="member_regmode" type="hidden" value="' . $member_regmode . '" />';
	$newmemberbutton = '<input name="regmember" type="submit" value="' . __('transmit a message', 'usces') . '" />';
	$html .= apply_filters('usces_filter_newmember_button', $newmemberbutton);
	echo $html;
}

function usces_login_button(){
	$loginbutton = '<input type="submit" name="member_login" id="member_login" class="member_login_button" value="' . __('Log-in', 'usces') . '" />';
	$html .= apply_filters('usces_filter_login_button', $loginbutton);
	echo $html;
}

function usces_assistance_item($post_id, $title ){
	if (usces_get_assistance_id_list($post_id)) :
		$assistanceposts = new wp_query( array('post__in'=>usces_get_assistance_ids($post_id)) );
		if($assistanceposts->have_posts()) :
		add_filter( 'excerpt_length', 'welcart_assistance_excerpt_length' );
		add_filter( 'excerpt_mblength', 'welcart_assistance_excerpt_mblength' );
?>
	<div class="assistance_item">
		<h3><?php echo $title; ?></h3>
		<ul class="clearfix">
<?php
		while ($assistanceposts->have_posts()) :
			$assistanceposts->the_post();
			//update_post_caches($posts); 
			usces_remove_filter();
			usces_the_item();
			ob_start();
?>
			<li>
			<div class="listbox clearfix">
				<div class="slit">
					<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>"><?php usces_the_itemImage(0, 100, 100, $post); ?></a>
				</div>
				<div class="detail">
					<h4><?php usces_the_itemName(); ?></h4>
					<?php the_excerpt(); ?>
					<p>
				<?php if (usces_is_skus()) : ?>
					<?php _e('$', 'usces'); ?><?php usces_the_firstPrice(); ?>
				<?php endif; ?>
					<br />
					&raquo;<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>"><?php _e('see the details', 'usces'); ?></a>
					</p>
				</div>
			</div>
			</li>
		<?php
			$list = ob_get_contents();
			ob_end_clean();
			echo apply_filters('usces_filter_assistance_item_list', $list, $post);
		 endwhile; ?>
		
		</ul>
	</div><!-- end of assistance_item -->
<?php 
		wp_reset_query();
		usces_reset_filter();
		remove_filter( 'excerpt_length', 'welcart_assistance_excerpt_length' );
		remove_filter( 'excerpt_mblength', 'welcart_assistance_excerpt_mblength' );
		endif;
	endif;
}
?>
