<?php
usces_the_item();

$html = '<div id="itempage">'."\n";
$html .= '<form action="' . USCES_CART_URL . '" method="post">'."\n";

$html .= '<div class="itemimg">'."\n";
$html .= '<a href="' . usces_the_itemImageURL(0, 'return') . '"';
$html = apply_filters('usces_itemimg_anchor_rel', $html);
$html .= '>';
$itemImage = usces_the_itemImage(0, 200, 250, $post, 'return');
$html .= apply_filters('usces_filter_the_itemImage', $itemImage, $post);
$html .= '</a>'."\n";
$html .= '</div>'."\n";
	
if(usces_sku_num() === 1) { //1SKU
	usces_have_skus();
	
	$html .= '<h3>' . esc_html(usces_the_itemName( 'return' )) . '&nbsp; (' . esc_html(usces_the_itemCode( 'return' )) . ') </h3>'."\n";
	$html .= '<div class="exp">'."\n";
	$html .= '<div class="field">'."\n";
	if( $this->itemsku['value']['cprice'] > 0 ){
		$usces_listprice = __('List price', 'usces') . $this->getGuidTax();
		$html .= '<div class="field_name">' . apply_filters('usces_filter_listprice_label', $usces_listprice, __('List price', 'usces'), $this->getGuidTax()) . '</div>'."\n";
		$html .= '<div class="field_cprice">' . __('$', 'usces') . number_format($this->itemsku['value']['cprice']) . '</div>';
	}
	$usces_sellingprice = __('selling price', 'usces') . $this->getGuidTax();
	$html .= '<div class="field_name">' . apply_filters('usces_filter_sellingprice_label', $usces_sellingprice, __('selling price', 'usces'), $this->getGuidTax()) . '</div>'."\n";
	$html .= '<div class="field_price">' . __('$', 'usces') . number_format($this->itemsku['value']['price']) . '</div>'."\n";
	$html .= '</div>'."\n";
	$singlestock = '<div class="field">' . __('stock status', 'usces') . ' : ' . esc_html(usces_the_itemZaiko('return')) . '</div>'."\n";
	$html .= apply_filters('single_item_stock_field', $singlestock);
	$item_custom = usces_get_item_custom( $post->ID, 'list', 'return' );
	if($item_custom){
		$html .= '<div class="field">'."\n";
		$html .= $item_custom;
		$html .= '</div>'."\n";
	}
		
	$html .= $content."\n";
	$html .= '</div><!-- end of exp -->'."\n";
	$html .= usces_the_itemGpExp('return');
	$html .= '<div class="skuform" align="right">'."\n";
	if (usces_is_options()) {
		$html .= "<table class='item_option'><caption>" . apply_filters('usces_filter_single_item_options_caption', __('Please appoint an option.', 'usces'), $post) . "</caption>\n";
		while (usces_have_options()) {
			$opttr = "<tr><th>" . esc_html($this->itemopt['key']) . '</th><td>' . usces_the_itemOption(usces_getItemOptName(),'','return') . "</td></tr>";
			$html .= apply_filters('usces_filter_singleitem_option', $opttr, $this->itemopt['key'], usces_getItemOptName()) . "\n";
		}
		$html .= "</table>\n";
	}
	if( !usces_have_zaiko() ){
		$html .= '<div class="zaiko_status">' . apply_filters('usces_filters_single_sku_zaiko_message', __('Sold Out', 'usces')) . '</div>'."\n";
	}else{
		$html .= '<div style="margin-top:10px">'.__('Quantity', 'usces').usces_the_itemQuant('return') . esc_html($this->itemsku['value']['unit']) . usces_the_itemSkuButton(__('Add to Shopping Cart', 'usces'), 0, 'return') . '</div>'."\n";
		$html .= '<div class="error_message">' . usces_singleitem_error_message($post->ID, $this->itemsku['key'], 'return') . '</div>'."\n";
	}

	$html .= '</div><!-- end of skuform -->'."\n";
	$html .= apply_filters('single_item_single_sku_after_field', NULL);
	
} elseif(usces_sku_num() > 1) { //some SKU
	usces_have_skus();
	$html .= '<h3>' . usces_the_itemName( 'return' ) . '&nbsp; (' . usces_the_itemCode( 'return' ) . ') </h3>'."\n";
	$html .= '<div class="exp">'."\n";
	$html .= $content."\n";
	$item_custom = usces_get_item_custom( $post->ID, 'list', 'return' );
	if($item_custom){
		$html .= '<div class="field">'."\n";
		$html .= $item_custom;
		$html .= '</div>'."\n";
	}
	$html .= '</div>'."\n";
	
	$html .= '<div class="skuform">'."\n";
	$html .= '<table class="skumulti">'."\n";
	$html .= '<thead>'."\n";
	$html .= '<tr>'."\n";
	$html .= '<th rowspan="2" class="thborder">'.__('order number', 'usces').'</th>'."\n";
	$html .= '<th colspan="2">'.__('Title', 'usces').'</th>'."\n";
	if( $this->itemsku['value']['cprice'] > 0 ){
		$usces_bothprice = '('.__('List price', 'usces').')'.__('selling price', 'usces') . $this->getGuidTax();
		$html .= '<th colspan="2">'.apply_filters('usces_filter_bothprice_label', $usces_bothprice, __('List price', 'usces'), __('selling price', 'usces'), $this->getGuidTax()) . '</th>'."\n";
	}else{
		$usces_sellingprice = __('selling price', 'usces') . $this->getGuidTax();
		$html .= '<th colspan="2">'.apply_filters('usces_filter_sellingprice_label', $usces_sellingprice, __('selling price', 'usces'), $this->getGuidTax()) . '</th>'."\n";
	}
	$html .= '</tr>'."\n";
	$html .= '<tr>'."\n";
	$html .= '<th class="thborder">'.__('stock status', 'usces').'</th>'."\n";
	$html .= '<th class="thborder">'.__('Quantity', 'usces').'</th>'."\n";
	$html .= '<th class="thborder">'.__('unit', 'usces').'</th>'."\n";
	$html .= '<th class="thborder">&nbsp;</th>'."\n";
	$html .= '</tr>'."\n";
	$html .= '</thead>'."\n";
	$html .= '<tbody>'."\n";
	do {
		$html .= '<tr>'."\n";
		$html .= '<td rowspan="2">' . esc_html($this->itemsku['key']) . '</td>'."\n";
		$html .= '<td colspan="2" class="skudisp subborder">' . apply_filters('usces_filter_singleitem_skudisp', esc_html($this->itemsku['value']['disp']))."\n";
		if (usces_is_options()) {
			$html .= "<table class='item_option'><caption>" . apply_filters('usces_filter_single_item_options_caption', __('Please appoint an option.', 'usces'), $post) . "</caption>\n";
			while (usces_have_options()) {
				$opttr = "<tr><th>" . esc_html($this->itemopt['key']) . '</th><td>' . usces_the_itemOption(usces_getItemOptName(),'','return') . "</td></tr>";
				$html .= apply_filters('usces_filter_singleitem_option', $opttr, $this->itemopt['key'], usces_getItemOptName()) . "\n";
			}
			$html .= "</table>\n";
//			while (usces_have_options()) {
//				$html .= '<br />' . usces_the_itemOption(usces_getItemOptName(),'', 'return');
//			}
		}
		$html .= '</td>'."\n";
		$html .= '<td colspan="2" class="subborder price">'."\n";
		if( $this->itemsku['value']['cprice'] > 0 ){
			$html .= '<span class="cprice">(' . __('$', 'usces') . number_format($this->itemsku['value']['cprice']) . ')</span>'."\n";
		}			
		$html .= '<span class="price">' . __('$', 'usces') . number_format($this->itemsku['value']['price']) . '</span><br />' . usces_the_itemGpExp('return') . '</td>'."\n";
		$html .= '</tr>'."\n";
		$html .= '<tr>'."\n";
		$html .= '<td class="zaiko">' . usces_the_itemZaiko('return') . '</td>'."\n";
		$html .= '<td class="quant">' . usces_the_itemQuant('return') . '</td>'."\n";
		$html .= '<td class="unit">' . $this->itemsku['value']['unit'] . '</td>'."\n";
		$html .= '<td class="button">' . usces_the_itemSkuButton(__('Add to Shopping Cart', 'usces'), 0, 'return') . '</td>'."\n";
		$html .= '</tr>'."\n";
		$html .= '<tr><td colspan="5" class="error_message">' . usces_singleitem_error_message($post->ID, $this->itemsku['key'], 'return') . '</td></tr>'."\n";

	} while (usces_have_skus());
	$html .= '</tbody>'."\n";
	$html .= '</table>'."\n";
	$html .= '</div><!-- end of skuform -->'."\n";
	$html .= apply_filters('single_item_multi_sku_after_field', NULL);
}
	
$html .= '<div class="itemsubimg">'."\n";
$imageid = usces_get_itemSubImageNums();
foreach ( $imageid as $id ) {
	$html .= '<a href="' . usces_the_itemImageURL($id, 'return') . '"';
	$html = apply_filters('usces_itemimg_anchor_rel', $html);
	$html .= '>';
	$itemImage = usces_the_itemImage($id, 137, 200, $post, 'return');
	$html .= apply_filters('usces_filter_the_SubImage', $itemImage, $post, $id);
	$html .= '</a>'."\n";
}
$html .= '</div><!-- end of itemsubimg -->'."\n";

if (usces_get_assistance_id_list($post->ID)) {
	$org_opst = $post;
	$html .= '<div class="assistance_item">'."\n";
	$assistanceposts = get_posts('include='.usces_get_assistance_id_list($post->ID));
	if ($assistanceposts) {
		$assistance_item_title = '<h3>' . usces_the_itemCode( 'return' ) . __('An article concerned', 'usces').'</h3>'."\n";
		$html .= apply_filters('usces_assistance_item_title', $assistance_item_title);
		$html .= '<ul class="clearfix">'."\n";
		foreach ($assistanceposts as $post) {
			setup_postdata($post);
			usces_the_item();
			$html .= '<li><div class="listbox clearfix">'."\n";
			$html .= '<div class="slit"><a href="' . get_permalink($post->ID) . '" rel="bookmark" title="' . esc_attr($post->post_title) . '">' . usces_the_itemImage(0, 100, 100, $post, 'return') . '</a></div>'."\n";
			$html .= '<div class="detail">'."\n";
			$html .= '<h4>' . usces_the_itemName('return') . '</h4>'."\n";
			$html .= $post->post_excerpt;
			$html .= '<p>'."\n";
			if (usces_is_skus()) {
				$html .= __('$', 'usces') . usces_the_firstPrice('return');
			}
			$html .= '<br />'."\n";
			$html .= '&raquo; <a href="' . get_permalink($post->ID) . '" rel="bookmark" title="' . esc_attr($post->post_title) . '">'.__('see the details', 'usces').'</a></p>'."\n";
			$html .= '</div>'."\n";
			$html .= '</div>'."\n";
			$html .= '</li>'."\n";
		}
		$html .= '</ul>'."\n";
	}
	
	$html .= '</div><!-- end of assistance_item -->'."\n";
	$post = $org_opst;
	setup_postdata($post);
}

$html = apply_filters('usces_filter_single_item_inform', $html);
$html .= '</form>'."\n";

$html .= '</div><!-- end of itemspage -->'."\n";
?>