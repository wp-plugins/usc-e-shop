	<table cellspacing="0" id="cart_table">
		<thead>
		<tr>
			<th scope="row" class="num"><?php echo __('No.','usces'); ?></th>
			<th class="thumbnail">　<?php //echo __('thumbnail','usces'); ?></th>
			<th>商品<?php //echo __('item','usces'); ?></th>
			<th class="price">単価<?php //echo __('price','usces'); ?></th>
			<th class="quantity">数量<?php //echo __('quantity','usces'); ?></th>
			<th class="subtotal">金額<?php //echo __('subtotal','usces'); ?></th>
			<th class="action"></th>
		</tr>
		</thead>
		<tbody>
<?php
	$member = $this->get_member();
	$entries = $this->cart->get_entry();
	$this->set_cart_fees( $member, $entries );

	$cart = $this->cart->get_cart();

	
	for($i=0; $i<count($cart); $i++) : 
		$cart_row = $cart[$i];
		$post_id = $cart_row['post_id'];
		$sku = $cart_row['sku'];
		$quantity = $cart_row['quantity'];
		$options = $cart_row['options'];
		$itemCode = $this->getItemCode($post_id);
		$itemName = $this->getItemName($post_id);
		$skuPrice = $cart_row['price'];
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
			<td class="aright"><?php echo number_format($skuPrice); ?></td>
			<td><?php echo $cart_row['quantity']; ?></td>
			<td class="aright"><?php echo number_format($skuPrice * $cart_row['quantity']); ?></td>
			<td></td>
		</tr>
<?php 
	endfor;
?>
		</tbody>
		<tfoot>
		<tr>
			<th colspan="5" class="aright">商品合計<?php //echo __('Item total price','usces'); ?></th>
			<th class="aright"><?php echo number_format($entries['order']['total_items_price']); ?></th>
			<th>&nbsp;</th>
		</tr>
<?php if( $this->options['membersystem_state'] == 'activate' &&  $this->options['membersystem_point'] == 'activate' && !empty($entries['order']['usedpoint']) ) : ?>
		<tr>
			<td colspan="5" class="aright">使用ポイント<?php //echo __('Use point','usces'); ?></td>
			<td class="aright" style="color:#FF0000"><?php echo number_format($entries['order']['usedpoint']); ?></td>
			<td>&nbsp;</td>
		</tr>
<?php endif; ?>
<?php if( !empty($entries['order']['discount']) ) : ?>
		<tr>
			<td colspan="5" class="aright">キャンペーン割引<?php //echo __('Descount','usces'); ?></td>
			<td class="aright" style="color:#FF0000"><?php echo number_format($entries['order']['discount']); ?></td>
			<td>&nbsp;</td>
		</tr>
<?php endif; ?>
		<tr>
			<td colspan="5" class="aright">送料<?php //echo __('Delivery fee','usces'); ?></td>
			<td class="aright"><?php echo number_format($entries['order']['shipping_charge']); ?></td>
			<td>&nbsp;</td>
		</tr>
<?php if( !empty($entries['order']['cod_fee']) ) : ?>
		<tr>
			<td colspan="5" class="aright">代引手数料<?php //echo __('COD Fee','usces'); ?></td>
			<td class="aright"><?php echo number_format($entries['order']['cod_fee']); ?></td>
			<td>&nbsp;</td>
		</tr>
<?php
	endif;
?>
<?php if( !empty($entries['order']['tax']) ) : ?>
		<tr>
			<td colspan="5" class="aright">消費税<?php //echo __('Tax','usces'); ?></td>
			<td class="aright"><?php echo number_format($entries['order']['tax']); ?></td>
			<td>&nbsp;</td>
		</tr>
<?php
	endif;
?>
		<tr>
			<th colspan="5" class="aright">総合計金額<?php //echo __('Total price','usces'); ?></th>
			<th class="aright"><?php echo number_format($entries['order']['total_full_price']); ?></th>
			<th>&nbsp;</th>
		</tr>
		</tfoot>
	</table>
<?php if( $this->options['membersystem_state'] == 'activate' &&  $this->options['membersystem_point'] == 'activate' &&  $this->is_member_logged_in() ) : ?>
<form action="<?php echo USCES_CART_URL; ?>" method="post">
<div class="error_message"><?php echo $this->error_message; ?></div>
	<table cellspacing="0" id="point_table">
		<tr>
		<td>現在のポイント</td>
		<td><span class="point"><?php echo $member['point']; ?></span>pt</td>
		</tr>
		<tr>
		<td>利用するポイント</td>
		<td><input name="order[usedpoint]" class="used_point" type="text" value="<?php echo $entries['order']['usedpoint']; ?>" />pt</td>
		</tr>
		<tr>
		<td colspan="2"><input name="use_point" type="submit" value="ポイントを使用する" /></td>
		</tr>
	</table>
</form>
<?php endif; ?>