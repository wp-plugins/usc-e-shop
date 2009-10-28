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
		<div class="field">
		<div class="field_name">販売価格' . $this->getGuidTax() . '</div>
		<div class="field_price">&yen;' . number_format($this->itemsku['value']['price']) . '</div>
		</div>
		<div class="field">
		在庫：' . usces_the_itemZaiko('return') . '
		</div>
		
		' . $content . '
		</div>' . usces_the_itemGpExp('return') . '
		<div class="skuform" align="right">';
	if (usces_is_options()) {
		while (usces_have_options()) {
			$html .= $this->itemopt['key'] . usces_the_itemOption(usces_getItemOptName(),'','return');
		}
	}
	$button = '<div style="margin-top:10px">数量' . usces_the_itemQuant('return') . $this->itemsku['value']['unit'] . usces_the_itemSkuButton('カートへ入れる', 0, 'return') . '</div>';
	$html .= apply_filters('usces_filter_dlseller_button', $button);
	$html .= '</div>';
	
} elseif(usces_sku_num() > 1) { //SKUが複数の場合
	
	$html .= '<h3>' . usces_the_itemName( 'return' ) . '&nbsp;（' . usces_the_itemCode( 'return' ) . '）</h3>
		<div class="exp">' . $content . '</div>
		<div class="skuform">
		<table class="skumulti">
		<thead>
		<tr>
		<th rowspan="2" class="thborder">注文番号</th>
		<th colspan="2">タイトル</th>
		<th colspan="2">販売価格' . $this->getGuidTax() . '</th>
		</tr>
		<tr>
		<th class="thborder">在庫</th>
		<th class="thborder">数量</th>
		<th class="thborder">単位</th>
		<th class="thborder">&nbsp;</th>
		</tr>
		</thead>
		<tbody>';
	while (usces_have_skus()) {
		$html .= '<tr>
			<td rowspan="2">' . $this->itemsku['key'] . '</td>
			<td colspan="2" class="skudisp subborder">' . $this->itemsku['value']['disp'];
		if (usces_is_options()) {
			while (usces_have_options()) {
				$html .= '<br />' . usces_the_itemOption(usces_getItemOptName(),'', 'return');
			}
		}
		$html .= '</td>
			<td colspan="2" class="subborder price"><span class="price">&yen;' . number_format($this->itemsku['value']['price']) . $this->getGuidTax() . '</span><br />' . usces_the_itemGpExp('return') . '</td>
			</tr>
			<tr>
			<td class="zaiko">' . usces_the_itemZaiko('return') . '</td>
			<td class="quant">' . usces_the_itemQuant('return') . '</td>
			<td class="unit">' . $this->itemsku['value']['unit'] . '</td>
			<td class="button">' . usces_the_itemSkuButton('カートへ入れる', 0, 'return') . '</td>
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
		$html .= '<h3>' . usces_the_itemCode( 'return' ) . '専用オプション品</h3>
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
				&raquo; <a href="' . get_permalink($post->ID) . '" rel="bookmark" title="' . $post->post_title . '">詳細を見る</a></p>
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