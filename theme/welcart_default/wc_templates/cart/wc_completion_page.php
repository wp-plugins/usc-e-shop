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

<?php if (have_posts()) : usces_remove_filter(); ?>

<div class="post" id="<?php usces_page_name(); ?>">

<h1><?php _e('Completion', 'usces'); ?></h1>
<div class="entry">

<div id="cart_completion">
<h3><?php _e('It has been sent succesfully.', 'usces'); ?></h3>
	<div class="header_explanation">
	<?php $header = '<p>'.__('Thank you for shopping.', 'usces').'<br />'.__("If you have any questions, please contact us by 'Contact'.", 'usces').'</p>'; ?>
	<?php echo apply_filters('usces_filter_cartcompletion_page_header', $header, $usces_entries, $usces_carts)."\n"; ?>
	</div><!-- header_explanation -->

<?php usces_completion_settlement(); ?>

<?php do_action('usces_action_cartcompletion_page_body', $usces_entries, $usces_carts); ?>

	<div class="footer_explanation">
	<?php echo apply_filters('usces_filter_cartcompletion_page_footer', NULL, $usces_entries, $usces_carts); ?>
	</div><!-- footer_explanation -->

	<form action="<?php bloginfo('home'); ?>" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
	<div class="send"><input name="top" class="back_to_top_button" type="submit" value="<?php _e('Back to the top page.', 'usces'); ?>" /></div>
	</form>
<?php echo apply_filters('usces_filter_conversion_tracking', NULL, $usces_entries, $usces_carts); ?>

</div><!-- end of cart_completion -->

		</div><!-- end of entry -->
	</div><!-- end of post -->
<?php else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>
</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_footer(); ?>
