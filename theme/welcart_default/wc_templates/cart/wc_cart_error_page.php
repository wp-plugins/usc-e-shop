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

<h1><?php _e('Error', 'usces'); ?></h1>
<div class="entry">
		
<div id="error-page">

<h2>ERROE</h2>
<div class="post">
<p><?php _e('Your order has not been completed', 'usces'); ?></p>
<p>(error <?php echo urldecode($_REQUEST['acting_return']); ?>)</p>

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

<?php get_footer(); ?>
