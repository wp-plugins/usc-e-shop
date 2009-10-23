<div id="cart">
<div class="upbutton">数量を変更した場合は必ず更新ボタンを押してください。<input name="upButton" type="submit" value="<?php echo '数量更新'; ?>" onclick="return uscesCart.upCart()"  />
</div>
<table cellspacing="0" id="cart_table">
	<thead>
	<tr>
		<th scope="row" class="num"><?php echo __('No.','usces'); ?></th>
		<th class="thumbnail"> <?php //echo __('thumbnail','usces'); ?></th>
		<th>商品<?php //echo __('item','usces'); ?></th>
		<th class="price">単価<?php //echo __('price','usces'); ?></th>
		<th class="quantity">数量<?php //echo __('quantity','usces'); ?></th>
		<th class="subtotal">金額<?php //echo __('subtotal','usces'); ?></th>
		<th class="stock">在庫<?php //echo __('stock','usces'); ?></th>
		<th class="action">　<?php //echo __('action','usces'); ?></th>
	</tr>
	</thead>
	<tbody>
<?php
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
		//$skuPrice = $this->getItemPrice($post_id, $sku);
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
			
?>
	<tr>
		<td><?php echo $i + 1; ?></td>
		<td><?php echo wp_get_attachment_image( $pictids[0], array(60, 60), true ); ?></td>
		<td class="aleft"><?php echo $itemName; ?>&nbsp;<?php echo $itemCode; ?>&nbsp;<?php echo $sku; ?><br /><?php echo $optstr; ?></td>
		<td class="aright">
		<?php if( usces_is_gptekiyo($post_id, $sku, $quantity) ) : $usces_gp = 1; ?>
		<img src="<?php echo bloginfo('template_url') . '/images/gp.gif'; ?>" alt="業務パック割引" />
		<?php endif; ?>
		<?php echo number_format($skuPrice); ?>
		</td>
		<td><input name="quant[<?php echo $i; ?>][<?php echo $post_id; ?>][<?php echo $sku; ?>]" class="quantity" type="text" value="<?php echo $cart_row['quantity']; ?>" /></td>
		<td class="aright"><?php echo number_format($skuPrice * $cart_row['quantity']); ?></td>
		<td <?php echo $red ?>><?php echo $stock; ?></td>
		<td>
		<?php foreach($options as $key => $value){ ?>
		<input name="itemOption[<?php echo $i; ?>][<?php echo $post_id; ?>][<?php echo $sku; ?>][<?php echo $key; ?>]" type="hidden" value="<?php echo $value; ?>" />
		<?php } ?>
		<input name="itemRestriction[<?php echo $i; ?>]" type="hidden" value="<?php echo $itemRestriction; ?>" />
		<input name="stockid[<?php echo $i; ?>]" type="hidden" value="<?php echo $stockid; ?>" />
		<input name="itempostid[<?php echo $i; ?>]" type="hidden" value="<?php echo $post_id; ?>" />
		<input name="itemsku[<?php echo $i; ?>]" type="hidden" value="<?php echo $sku; ?>" />
		<input name="zaikonum[<?php echo $i; ?>][<?php echo $post_id; ?>][<?php echo $sku; ?>]" type="hidden" value="<?php echo $skuZaikonum; ?>" />
		<input name="skuPrice[<?php echo $i; ?>][<?php echo $post_id; ?>][<?php echo $sku; ?>]" type="hidden" value="<?php echo $skuPrice; ?>" />
		<input name="delButton[<?php echo $i; ?>][<?php echo $post_id; ?>][<?php echo $sku; ?>]" class="delButton" type="submit" value="<?php echo __('Delete','usces'); ?>" />
		</td>
	</tr>
<?php 
	}
?>
	</tbody>
	<tfoot>
	<tr>
		<th colspan="5" scope="row" class="aright">商品合計<?php //echo __('Items total price','usces'); ?></th>
		<th class="aright"><?php echo number_format($this->get_total_price()); ?></th>
		<th colspan="2">&nbsp;</th>
	</tr>
	</tfoot>
</table>
<?php if( $usces_gp ) : ?>
<img src="<?php echo bloginfo('template_url') . '/images/gp.gif'; ?>" alt="業務パック割引" />このマークがある価格は<strong>業務パック割引</strong>が適用されています。
<?php endif; ?>
</div>