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

<h1 class="cart_page_title"><?php _e('Error', 'usces'); ?></h1>
<div class="entry">
		
<div id="error-page">

<h2>ERROR</h2>
<div class="post">
<p><?php _e('Your order has not been completed', 'usces'); ?></p>
<p>(error <?php esc_html_e(urldecode($_REQUEST['acting_return'])); ?>)</p>

<?php uesces_get_error_settlement(); ?>

</div><!-- end of post -->

</div><!-- end of error-page -->

		</div><!-- end of entry -->
	</div><!-- end of post -->
<?php else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>
</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_sidebar( 'cartmember' ); ?>

<?php get_footer(); ?>
