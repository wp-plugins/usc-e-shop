<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
get_header();
?>

<div id="content" class="two-column">
<div class="catbox">

<?php if (have_posts()) : usces_remove_filter(); ?>

<div class="post" id="wc_<?php usces_page_name(); ?>">

<h1 class="cart_page_title"><?php _e('Confirmation', 'usces'); ?></h1>
<div class="entry">
		
<div id="info-confirm">
	
	<div class="usccart_navi">
		<ol class="ucart">
		<li class="ucart usccart"><?php _e('1.Cart','usces'); ?></li>
		<li class="ucart usccustomer"><?php _e('Log-in for members','usces'); ?></li>
		<li class="ucart uscdelivery"><?php _e('3.Deli. & Pay.','usces'); ?></li>
		<li class="ucart uscconfirm usccart_confirm"><?php _e('4.Confirm','usces'); ?></li>
		</ol>
	</div>

	<div class="header_explanation">
<?php do_action('usces_action_confirm_page_header'); ?>
	</div><!-- end of header_explanation -->

	<div class="error_message"><?php usces_error_message(); ?></div>
	<div id="cart">
		<table cellspacing="0" id="cart_table">
			<thead>
			<tr>
				<th scope="row" class="num"><?php _e('No.','usces'); ?></th>
				<th class="thumbnail">&nbsp;&nbsp;</th>
				<th><?php _e('Items','usces'); ?></th>
				<th class="price"><?php _e('Unit price','usces'); ?></th>
				<th class="quantity"><?php _e('Quantity', 'usces'); ?></th>
				<th class="subtotal"><?php _e('Points', 'usces'); ?></th>
				<th class="action"></th>
			</tr>
			</thead>
			<tbody>
		<?php usces_get_confirm_rows(); ?>
			</tbody>
			<tfoot>
<?php
			$point = ( empty($_SESSION['usces_member']['point'])) ? 0 : $_SESSION['usces_member']['point'];
			$rem_point = $point - $usces_entries['order']['total_items_price'];
?>
			<tr>
				<th colspan="5" class="aright"><?php _e('Used points', 'usces'); ?></th>
				<th class="aright"><?php usces_crform($usces_entries['order']['total_items_price'], false, true); ?></th>
				<th>&nbsp;</th>
			</tr>
			<tr>
				<td colspan="5" class="aright"><?php _e('ポイント残高','usces'); ?></td>
				<td class="aright"><?php usces_crform($rem_point, false, true); ?></td>
				<td>&nbsp;</td>
			</tr>
			</tfoot>
		</table>
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
			<th><?php _e('Notes', 'usces'); ?></th><td><?php echo nl2br(esc_html($usces_entries['order']['note'])); ?></td>
		</tr>
	</table>

<?php usces_purchase_button(); ?>

	<div class="footer_explanation">
<?php do_action('usces_action_confirm_page_footer'); ?>
	</div><!-- end of footer_explanation -->

</div><!-- end of info-confirm -->

		</div><!-- end of entry -->
	</div><!-- end of post -->
<?php else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>
</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_sidebar( 'cartmember' ); ?>

<?php get_footer(); ?>
