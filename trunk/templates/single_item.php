<?php
usces_the_item();

$html = '
	<div id="itempage">
	<form action="' . USCES_CART_URL . '" method="post">
	<div class="itemimg">
	<a href="' . usces_the_itemImageURL(0, 'return') . '"';

$html = apply_filters('usces_itemimg_anchor_rel', $html);

$html .= '>' . usces_the_itemImage(0, 200, 250, $post, 'return') . '</a>
	</div>';
	
if(usces_sku_num() === 1) { //SKUが1つの場合
	usces_have_skus();
	
	$html .= '<h3>' . usces_the_itemName( 'return' ) . '&nbsp;（' . usces_the_itemCode( 'return' ) . '）</h3>
		<div class="exp">
		<div class="field">';
	if( $this->itemsku['value']['cprice'] > 0 ){
		$html .= '<div class="field_name">' . __('List price', 'usces') . $this->getGuidTax() . '</div>
		<div class="field_cprice">' . __('$', 'usces') . number_format($this->itemsku['value']['cprice']) . '</div>';
	}
	$html .= '<div class="field_name">'.__('selling price', 'usces') . $this->getGuidTax() . '</div>
		<div class="field_price">' . __('$', 'usces') . number_format($this->itemsku['value']['price']) . '</div>
		</div>
		<div class="field">
		' . __('stock status', 'usces') . '：' . usces_the_itemZaiko('return') . '
		</div>
		
		' . $content . '
		</div>' . usces_the_itemGpExp('return') . '
		<div class="skuform" align="right">';
	if (usces_is_options()) {
		$html .= "<table class='item_option'><caption>".__('Please appoint an option.', 'usces')."</caption>\n";
		while (usces_have_options()) {
			$html .= "<tr><th>" . $this->itemopt['key'] . '</th><td>' . usces_the_itemOption(usces_getItemOptName(),'','return') . "</td></tr>\n";
		}
		$html .= "</table>\n";
	}
	$button = '<div style="margin-top:10px">'.__('Quantity', 'usces').usces_the_itemQuant('return') . $this->itemsku['value']['unit'] . usces_the_itemSkuButton(__('Add to Shopping Cart', 'usces'), 0, 'return') . '</div>';
	$html .= apply_filters('usces_filter_dlseller_button', $button);
	$html .= '</div>';
	
} elseif(usces_sku_num() > 1) { //SKUが複数の場合
	
	$html .= '<h3>' . usces_the_itemName( 'return' ) . '&nbsp;（' . usces_the_itemCode( 'return' ) . '）</h3>
		<div class="exp">' . $content . '</div>
		<div class="skuform">
		<table class="skumulti">
		<thead>
		<tr>
		<th rowspan="2" class="thborder">'.__('order number', 'usces').'</th>
		<th colspan="2">タイトル</th>';
	if( $this->itemsku['value']['cprice'] > 0 ){
		$html .= '<th colspan="2">('.__('List price', 'usces').')'.__('selling price', 'usces') . $this->getGuidTax() . '</th>';
	}else{
		$html .= '<th colspan="2">'.__('selling price', 'usces') . $this->getGuidTax() . '</th>';
	}
	$html .= '</tr>
		<tr>
		<th class="thborder">'.__('stock status', 'usces').'</th>
		<th class="thborder">'.__('Quantity', 'usces').'</th>
		<th class="thborder">'.__('unit', 'usces').'</th>
		<th class="thborder">&nbsp;</th>
		</tr>
		</thead>
		<tbody>';
	while (usces_have_skus()) {
		$html .= '<tr>
			<td rowspan="2">' . $this->itemsku['key'] . '</td>
			<td colspan="2" class="skudisp subborder">' . $this->itemsku['value']['disp'];
		if (usces_is_options()) {
			$html .= "<table class='item_option'><caption>".__('Please appoint an option.', 'usces')."</caption>\n";
			while (usces_have_options()) {
				$html .= "<tr><th>" . $this->itemopt['key'] . '</th><td>' . usces_the_itemOption(usces_getItemOptName(),'','return') . "</td></tr>\n";
			}
			$html .= "</table>\n";
//			while (usces_have_options()) {
//				$html .= '<br />' . usces_the_itemOption(usces_getItemOptName(),'', 'return');
//			}
		}
		$html .= '</td>
			<td colspan="2" class="subborder price">';
		if( $this->itemsku['value']['cprice'] > 0 ){
			$html .= '<span class="cprice">(' . __('$', 'usces') . number_format($this->itemsku['value']['cprice']) . ')</span>';
		}			
		$html .= '<span class="price">' . __('$', 'usces') . number_format($this->itemsku['value']['price']) . $this->getGuidTax() . '</span><br />' . usces_the_itemGpExp('return') . '</td>
			</tr>
			<tr>
			<td class="zaiko">' . usces_the_itemZaiko('return') . '</td>
			<td class="quant">' . usces_the_itemQuant('return') . '</td>
			<td class="unit">' . $this->itemsku['value']['unit'] . '</td>
			<td class="button">' . usces_the_itemSkuButton(__('Add to Shopping Cart', 'usces'), 0, 'return') . '</td>
			</tr>';
	}
	$html .= '</tbody>
		</table>
		</div>';
}
	
$html .= '<div class="itemsubimg">';
$imageid = usces_get_itemSubImageNums();
foreach ( $imageid as $id ) {
	$html .= '<a href="' . usces_the_itemImageURL($id, 'return') . '"';
	$html = apply_filters('usces_itemimg_anchor_rel', $html);
	$html .= '>' . usces_the_itemImage($id, 137, 200, $post, 'return') . '</a>';
}
$html .= '</div>';

if (usces_get_assistance_id_list($post->ID)) {
	$html .= '<div class="assistance_item">';
	$assistanceposts = get_posts('include='.usces_get_assistance_id_list($post->ID));
	if ($assistanceposts) {
		$html .= '<h3>' . usces_the_itemCode( 'return' ) . __('An article concerned', 'usces').'</h3>
			<ul class="clearfix">';
		foreach ($assistanceposts as $post) {
			setup_postdata($post);
			usces_the_item();
			$html .= '<li><div class="listbox clearfix">
				<div class="slit"><a href="' . get_permalink($post->ID) . '" rel="bookmark" title="' . $post->post_title . '">' . usces_the_itemImage(0, 100, 100, $post, 'return') . '</a></div>
				<div class="detail">
				<h4>' . usces_the_itemName('return') . '</h4>' . $post->post_excerpt . '
				<p>';
			if (usces_is_skus()) {
				$html .= '￥' . usces_the_firstPrice('return');
			}
			$html .= '<br />
				&raquo; <a href="' . get_permalink($post->ID) . '" rel="bookmark" title="' . $post->post_title . '">'.__('see the details', 'usces').'</a></p>
				</div>
				</div></li>';
		}
		$html .= '</ul>';
	}
	
	$html .= '</div>';
}

$html .= '
	</form>
	</div><!-- end of itemspage -->';
?>