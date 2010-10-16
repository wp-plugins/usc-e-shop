	<table cellspacing="0" id="cart_table">
		<thead>
		<tr>
			<th scope="row" class="num"><?php echo __('No.','usces'); ?></th>
			<th class="thumbnail">　<?php //echo __('thumbnail','usces'); ?></th>
			<th><?php _e('Items','usces'); ?></th>
			<th class="price"><?php _e('Unit price','usces'); ?></th>
			<th class="quantity"><?php _e('Quantity','usces'); ?></th>
			<th class="subtotal"><?php _e('Amount','usces'); ?></th>
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
		$cartItemName = $this->getCartItemName($post_id, $sku);
		$skuPrice = $cart_row['price'];
		$pictids = $this->get_pictids($itemCode);
		$optstr =  '';
		foreach((array)$options as $key => $value){
			$optstr .= esc_html($key) . ' : ' . esc_html($value) . "<br />\n"; 
		}
?>
		<tr>
			<td><?php echo $i + 1; ?></td>
			<td><?php echo wp_get_attachment_image( $pictids[0], array(60, 60), true ); ?></td>
			<td class="aleft"><?php echo esc_html($cartItemName); ?><br /><?php echo $optstr; ?></td>
			<td class="aright"><?php echo number_format($skuPrice); ?></td>
			<td><?php echo esc_html($cart_row['quantity']); ?></td>
			<td class="aright"><?php echo number_format($skuPrice * $cart_row['quantity']); ?></td>
			<td></td>
		</tr>
<?php 
	endfor;
?>
		</tbody>
		<tfoot>
		<tr>
			<th colspan="5" class="aright"><?php _e('total items','usces'); ?></th>
			<th class="aright"><?php echo number_format($entries['order']['total_items_price']); ?></th>
			<th>&nbsp;</th>
		</tr>
<?php if( $this->options['membersystem_state'] == 'activate' &&  $this->options['membersystem_point'] == 'activate' && !empty($entries['order']['usedpoint']) ) : ?>
		<tr>
			<td colspan="5" class="aright"><?php _e('Used points','usces'); ?></td>
			<td class="aright" style="color:#FF0000"><?php echo number_format($entries['order']['usedpoint']); ?></td>
			<td>&nbsp;</td>
		</tr>
<?php endif; ?>
<?php if( !empty($entries['order']['discount']) ) : ?>
		<tr>
			<td colspan="5" class="aright"><?php _e('Campaign disnount', 'usces'); ?></td>
			<td class="aright" style="color:#FF0000"><?php echo number_format($entries['order']['discount']); ?></td>
			<td>&nbsp;</td>
		</tr>
<?php endif; ?>
		<tr>
			<td colspan="5" class="aright"><?php _e('Shipping', 'usces'); ?></td>
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
			<td colspan="5" class="aright"><?php _e('consumption tax', 'usces'); ?></td>
			<td class="aright"><?php echo number_format($entries['order']['tax']); ?></td>
			<td>&nbsp;</td>
		</tr>
<?php
	endif;
?>
		<tr>
			<th colspan="5" class="aright"><?php _e('Total Amount','usces'); ?></th>
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
		<td><?php _e('The current point', 'usces'); ?></td>
		<td><span class="point"><?php echo esc_html($member['point']); ?></span>pt</td>
		</tr>
		<tr>
		<td><?php _e('Points you are using here', 'usces'); ?></td>
		<td><input name="order[usedpoint]" class="used_point" type="text" value="<?php echo esc_attr($entries['order']['usedpoint']); ?>" />pt</td>
		</tr>
		<tr>
		<td colspan="2"><input name="use_point" type="submit" value="<?php _e('Use the points', 'usces'); ?>" /></td>
		</tr>
	</table>
</form>
<?php endif; ?>