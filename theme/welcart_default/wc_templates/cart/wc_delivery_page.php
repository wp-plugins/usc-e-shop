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
<?php usces_delivery_info_script(); ?>

<div id="delivery-info">
	
	<div class="usccart_navi">
		<ol class="ucart">
		<li class="ucart usccart"><?php _e('1.Cart','usces'); ?></li>
		<li class="ucart usccustomer"><?php _e('2.Customer Info','usces'); ?></li>
		<li class="ucart uscdelivery usccart_delivery"><?php _e('3.Deli. & Pay.','usces'); ?></li>
		<li class="ucart uscconfirm"><?php _e('4.Confirm','usces'); ?></li>
		</ol>
	</div>
	<div class="header_explanation">
<?php echo apply_filters('usces_filter_delivery_page_header', $header); ?>
	</div>
	
	<div class="error_message"><?php usces_error_message(); ?></div>
	<form action="<?php usces_url('cart'); ?>" method="post">
	<table class="customer_form">
		<tr>
			<th rowspan="2" scope="row"><?php _e('shipping address', 'usces'); ?></th>
			<td><input name="delivery[delivery_flag]" type="radio" id="delivery_flag1" onclick="document.getElementById('delivery_table').style.display = 'none';" value="0"<?php if($usces_entries['delivery']['delivery_flag'] == 0) echo ' checked'; ?> onKeyDown="if (event.keyCode == 13) {return false;}" /> <label for="delivery_flag1"><?php _e('same as customer information', 'usces'); ?></label></td>
		</tr>
		<tr>
			<td><input name="delivery[delivery_flag]" id="delivery_flag2" onclick="document.getElementById('delivery_table').style.display = 'block'" type="radio" value="1"<?php if($usces_entries['delivery']['delivery_flag'] == 1) echo ' checked'; ?> onKeyDown="if (event.keyCode == 13) {return false;}" /> <label for="delivery_flag2"><?php _e('Chose another shipping address.', 'usces'); ?></label></td>
		</tr>
	</table>
	<table class="customer_form" id="delivery_table">
<?php echo uesces_addressform( 'delivery', $usces_entries ); ?>
	</table>
	<table class="customer_form" id="time">
		<tr>
			<th scope="row"><?php _e('shipping option', 'usces'); ?></th>
			<td colspan="2"><?php usces_the_delivery_method( $usces_entries['order']['delivery_method']); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php _e('Delivery date', 'usces'); ?></th>
			<td colspan="2"><?php usces_the_delivery_date( $usces_entries['order']['delivery_date']); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php _e('Delivery Time', 'usces'); ?></th>
			<td colspan="2"><?php usces_the_delivery_time( $usces_entries['order']['delivery_time']); ?></td>
		</tr>
		<tr>
			<th scope="row"><em>*</em><?php _e('payment method', 'usces'); ?></th>
			<td colspan="2"><?php usces_the_payment_method( $usces_entries['order']['payment_name']); ?></td>
		</tr>
	</table>

<?php usces_delivery_secure_form(); ?>

<?php $meta = usces_has_custom_field_meta('order'); ?>
<?php if(!empty($meta) and is_array($meta)) : ?>
	<table class="customer_form" id="custom_order">
	<?php usces_custom_field_input($usces_entries, 'order', ''); ?>
	</table>
<?php endif; ?>

<?php $entry_order_note = empty($usces_entries['order']['note']) ? apply_filters('usces_filter_default_order_note', NULL) : $usces_entries['order']['note']; ?>
	<table class="customer_form" id="notes_table">
		<tr>
			<th scope="row"><?php _e('Notes', 'usces'); ?></th>
			<td colspan="2"><textarea name="order[note]" id="note" class="notes"><?php echo esc_html($entry_order_note); ?></textarea></td>
		</tr>
	</table>

	<div class="send"><input name="order[cus_id]" type="hidden" value="<?php echo $this->cus_id; ?>" />		
	<input name="backCustomer" type="submit" class="back_to_customer_button" value="<?php _e('Back', 'usces'); ?>"<?php echo apply_filters('usces_filter_deliveryinfo_prebutton', NULL); ?> />&nbsp;&nbsp;
	<input name="confirm" type="submit" class="to_confirm_button" value="<?php _e(' Next ', 'usces'); ?>"<?php echo apply_filters('usces_filter_deliveryinfo_nextbutton', NULL); ?> /></div>
	</form>

	<div class="footer_explanation">
<?php echo apply_filters('usces_filter_delivery_page_footer', $footer); ?>
	</div>
</div>

		</div><!-- end of entry -->
	</div><!-- end of post -->
<?php else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>
</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_footer(); ?>
