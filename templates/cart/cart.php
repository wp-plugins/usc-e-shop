<?php
$html = '<div id="inside-cart">

<div class="usccart_navi">
<ol class="ucart">
<li class="ucart usccart_cart">１.カート</li>
<li class="ucart">２.お客様情報</li>
<li class="ucart">３.発送・支払方法</li>
<li class="ucart">４.内容確認</li>
</ol>
</div>


<div class="error_message">' . $usces->error_message . '</div>

<form action="' . USCES_CART_URL . '" method="post">';

if($this->cart->num_row() > 0) {
	
	$html .= '<div id="cart">';
	
	$button = '<div class="upbutton">数量を変更した場合は必ず更新ボタンを押してください。<input name="upButton" type="submit" value="数量更新" onclick="return uscesCart.upCart()"  /></div>';
	$html .= apply_filters('usces_filter_cart_upbutton', $button);
	
	$html .= '<table cellspacing="0" id="cart_table">
		<thead>
		<tr>
			<th scope="row" class="num">No.</th>
			<th class="thumbnail"> </th>
			<th>商品</th>
			<th class="quantity">単価</th>
			<th class="quantity">数量</th>
			<th class="subtotal">金額' . $this->getGuidTax() . '</th>
			<th class="stock">在庫</th>
			<th class="action">　</th>
		</tr>
		</thead>
		<tbody>';
	$cart = $this->cart->get_cart();
	$usces_gp = 0;
	for($i=0; $i<count($cart); $i++) { 
		$cart_row = $cart[$i];
		$post_id = $cart_row['post_id'];
		$sku = $cart_row['sku'];
		$quantity = $cart_row['quantity'];
		$options = $cart_row['options'];
		$itemCode = $this->getItemCode($post_id);
		$itemName = $this->getItemName($post_id);
		$itemRestriction = $this->getItemRestriction($post_id);
		$skuPrice = $cart_row['price'];
		$skuZaikonum = $this->getItemZaikonum($post_id, $sku);
		$stockid = $this->getItemZaikoStatusId($post_id, $sku);
		$stock = $this->getItemZaiko($post_id, $sku);
		$red = (in_array($stock, array('売切れ','入荷待ち','廃盤'))) ? 'class="signal_red"' : '';
		$pictids = $this->get_pictids($itemCode);
		if (!empty($options)) {
			$optstr = implode(',', $options);
		} else { 
			$optstr =  '';
			$options =  array();
		}
				
		$html .= '<tr>
			<td>' . ($i + 1) . '</td>
			<td>' . wp_get_attachment_image( $pictids[0], array(60, 60), true ) . '</td>
			<td class="aleft">' . $itemName . '&nbsp;' . $itemCode . '&nbsp;' . $sku . '<br />' . $optstr . '</td>
			<td class="aright">';
		if( usces_is_gptekiyo($post_id, $sku, $quantity) ) {
			$usces_gp = 1;
			$html .= '<img src="' . get_template_directory_uri() . '/images/gp.gif" alt="業務パック割引" /><br />';
		}
		$html .= number_format($skuPrice) . '
			</td>
			<td><input name="quant[' . $i . '][' . $post_id . '][' . $sku . ']" class="quantity" type="text" value="' . $cart_row['quantity'] . '" /></td>
			<td class="aright">' . number_format($skuPrice * $cart_row['quantity']) . '</td>
			<td ' . $red . '>' . $stock . '</td>
			<td>';
		foreach($options as $key => $value){
			$html .= '<input name="itemOption[' . $i . '][' . $post_id . '][' . $sku . '][' . $key . ']" type="hidden" value="' . $value . '" />';
		}
		$html .= '<input name="itemRestriction[' . $i . ']" type="hidden" value="' . $itemRestriction . '" />
			<input name="stockid[' . $i . ']" type="hidden" value="' . $stockid . '" />
			<input name="itempostid[' . $i . ']" type="hidden" value="' . $post_id . '" />
			<input name="itemsku[' . $i . ']" type="hidden" value="' . $sku . '" />
			<input name="zaikonum[' . $i . '][' . $post_id . '][' . $sku . ']" type="hidden" value="' . $skuZaikonum . '" />
			<input name="skuPrice[' . $i . '][' . $post_id . '][' . $sku . ']" type="hidden" value="' . $skuPrice . '" />
			<input name="delButton[' . $i . '][' . $post_id . '][' . $sku . ']" class="delButton" type="submit" value="' . __('Delete','usces') . '" />
			</td>
		</tr>';
	}

	$html .= '</tbody>
		<tfoot>
		<tr>
			<th colspan="5" scope="row" class="aright">商品合計' . $this->getGuidTax() . '</th>
			<th class="aright">' . number_format($this->get_total_price()) . '</th>
			<th colspan="2">&nbsp;</th>
		</tr>
		</tfoot>
	</table>';
	if( $usces_gp ) {
		$html .= '<img src="' . get_template_directory_uri() . '/images/gp.gif" alt="業務パック割引" /><br />このマークがある価格は<strong>業務パック割引</strong>が適用されています。';
	}
	$html .= '</div>';

} else {
	$html .= '<div class="no_cart">只今、カートに商品はございません。</div>';
}

$html .= $content;

$html .= '<div class="send">
	<input name="previous" type="button" id="previouscart" onclick="uscesCart.previousCart();" value="買い物を続ける" />&nbsp;&nbsp;';
if( usces_is_cart() ) {
	$html .= '<input name="customerinfo" type="submit" value="上記内容でお客様情報入力をする" />';
}
$html .= '</div>
	</form>
	
	</div>';
?>
