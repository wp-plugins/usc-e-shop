<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
get_header();
?>

<div id="content">
<div class="catbox">
	<?php if (have_posts()) : have_posts(); the_post(); ?>
	<div class="post" id="<?php echo $post->post_name; ?>">
		<h1><?php the_title(); ?></h1>
		<div class="entry">
		
<?php usces_remove_filter(); ?>
<?php usces_get_entries(); ?>
<div id="info-confirm">
	
	<div class="usccart_navi">
		<ol class="ucart">
		<li class="ucart usccart"><?php _e('1.Cart','usces'); ?></li>
		<li class="ucart usccustomer"><?php _e('2.Customer Info','usces'); ?></li>
		<li class="ucart uscdelivery"><?php _e('3.Deli. & Pay.','usces'); ?></li>
		<li class="ucart uscconfirm usccart_confirm"><?php _e('4.Confirm','usces'); ?></li>
		</ol>
	</div>

	<div class="header_explanation">
<?php echo apply_filters('usces_filter_confirm_page_header', NULL); ?>
	</div><!-- end of header_explanation -->

	<div class="error_message"><?php usces_error_message(); ?></div>
	<div id="cart">
		<div class="currency_code"><?php _e('Currency','usces'); ?> : <?php usces_crcode(); ?></div>
		<table cellspacing="0" id="cart_table">
			<thead>
			<tr>
				<th scope="row" class="num"><?php _e('No.','usces'); ?></th>
				<th class="thumbnail">&nbsp;&nbsp;</th>
				<th><?php _e('Items','usces'); ?></th>
				<th class="price"><?php _e('Unit price','usces'); ?></th>
				<th class="quantity"><?php _e('Quantity', 'usces'); ?></th>
				<th class="subtotal"><?php _e('Amount', 'usces'); ?></th>
				<th class="action"></th>
			</tr>
			</thead>
			<tbody>
		<?php usces_get_confirm_rows(); ?>
			</tbody>
			<tfoot>
			<tr>
				<th colspan="5" class="aright"><?php _e('total items', 'usces'); ?></th>
				<th class="aright"><?php usces_crform($usces_entries['order']['total_items_price'], true, false); ?></th>
				<th>&nbsp;</th>
			</tr>
<?php if( $this->options['membersystem_state'] == 'activate' &&  $this->options['membersystem_point'] == 'activate' && !empty($usces_entries['order']['usedpoint']) ) : ?>
			<tr>
				<td colspan="5" class="aright"><?php _e('Used points', 'usces'); ?></td>
				<td class="aright" style="color:#FF0000"><?php echo number_format($usces_entries['order']['usedpoint']); ?></td>
				<td>&nbsp;</td>
			</tr>
<?php endif; ?>
<?php if( !empty($usces_entries['order']['discount']) ) : ?>
			<tr>
				<td colspan="5" class="aright"><?php echo apply_filters('usces_confirm_discount_label', __('Campaign disnount', 'usces')); ?></td>
				<td class="aright" style="color:#FF0000"><?php usces_crform($usces_entries['order']['discount'], true, false); ?></td>
				<td>&nbsp;</td>
			</tr>
<?php endif; ?>
			<tr>
				<td colspan="5" class="aright"><?php _e('Shipping', 'usces'); ?></td>
				<td class="aright"><?php usces_crform($usces_entries['order']['shipping_charge'], true, false); ?></td>
				<td>&nbsp;</td>
			</tr>
<?php if( !empty($usces_entries['order']['cod_fee']) ) : ?>
			<tr>
				<td colspan="5" class="aright"><?php echo apply_filters('usces_filter_cod_label', __('COD fee', 'usces')); ?></td>
				<td class="aright"><?php usces_crform($usces_entries['order']['cod_fee'], true, false); ?></td>
				<td>&nbsp;</td>
			</tr>
<?php endif; ?>
<?php if( !empty($usces_entries['order']['tax']) ) : ?>
			<tr>
				<td colspan="5" class="aright"><?php _e('consumption tax', 'usces'); ?></td>
				<td class="aright"><?php usces_crform($usces_entries['order']['tax'], true, false); ?></td>
				<td>&nbsp;</td>
			</tr>
<?php endif; ?>
			<tr>
				<th colspan="5" class="aright"><?php _e('Total Amount', 'usces'); ?></th>
				<th class="aright"><?php usces_crform($usces_entries['order']['total_full_price'], true, false); ?></th>
				<th>&nbsp;</th>
			</tr>
			</tfoot>
		</table>
	
<?php if( $this->options['membersystem_state'] == 'activate' &&  $this->options['membersystem_point'] == 'activate' &&  $this->is_member_logged_in() ) : ?>
		<form action="<?php usces_url('cart'); ?>" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
		<div class="error_message"><?php echo $this->error_message; ?></div>
		<table cellspacing="0" id="point_table">
			<tr>
				<td><?php _e('The current point', 'usces'); ?></td>
				<td><span class="point"><?php echo $member['point']; ?></span>pt</td>
			</tr>
			<tr>
				<td><?php _e('Points you are using here', 'usces'); ?></td>
				<td><input name="order[usedpoint]" class="used_point" type="text" value="<?php echo esc_attr($usces_entries['order']['usedpoint']); ?>" />pt</td>
			</tr>
				<tr>
				<td colspan="2"><input name="use_point" type="submit" class="use_point_button" value="<?php _e('Use the points', 'usces'); ?>" /></td>
			</tr>
	</table>
	</form>
<?php endif; ?>
 
	</div>
	<table id="confirm_table">
		<tr class="ttl">
			<td colspan="2"><h3><?php _e('Customer Information', 'usces'); ?></h3></td>
		</tr>
		<tr>
			<th><?php _e('e-mail adress', 'usces'); ?></th>
			<td><?php echo esc_html($usces_entries['customer']['mailaddress1']); ?></td>
		</tr>
<?php uesces_addressform( 'confirm', $usces_entries, 'echo' ); ?>
		<tr>
			<td class="ttl" colspan="2"><h3><?php _e('Others', 'usces'); ?></h3></td>
		</tr>
		<tr>
			<th><?php _e('shipping option', 'usces'); ?></th><td><?php echo esc_html(usces_delivery_method_name( $usces_entries['order']['delivery_method'], 'return' )); ?></td>
		</tr>
		<tr>
			<th><?php _e('Delivery date', 'usces'); ?></th><td><?php echo esc_html($usces_entries['order']['delivery_date']); ?></td>
		</tr>
		<tr class="bdc">
			<th><?php _e('Delivery Time', 'usces'); ?></th><td><?php echo esc_html($usces_entries['order']['delivery_time']); ?></td>
		</tr>
		<tr>
			<th><?php _e('payment method', 'usces'); ?></th><td><?php echo esc_html($usces_entries['order']['payment_name'] . usces_payment_detail($usces_entries)); ?></td>
		</tr>
<?php usces_custom_field_info($usces_entries, 'order', ''); ?>
		<tr>
			<th><?php _e('Notes', 'usces'); ?></th><td><?php echo nl2br($usces_entries['order']['note']); ?></td>
		</tr>
	</table>

<?php require( USCES_PLUGIN_DIR . "/includes/purchase_button.php"); ?>

	<div class="footer_explanation">
<?php echo apply_filters('usces_filter_confirm_page_footer', NULL); ?>
	</div><!-- end of footer_explanation -->

</div><!-- end of info-confirm -->

		</div><!-- end of entry -->
	</div><!-- end of post -->
<?php else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>
</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_footer(); ?>
