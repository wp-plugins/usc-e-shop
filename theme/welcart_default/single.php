<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
get_header();

get_sidebar();
?>

<div id="content" class="three-column">
<div class="catbox">

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>


<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
<h1><?php the_title(); ?></h1>
<?php if(!usces_is_item()): ?>
<?php the_date('','<span class="storydate">','</span>'); ?>
<div class="storymeta"><?php _e("Filed under:"); ?> <?php the_category(',') ?> &#8212; <?php the_tags(__('Tags: '), ', ', ' &#8212; '); ?> <?php the_author() ?> @ <?php the_time() ?> <?php edit_post_link(__('Edit This')); ?></div>
<?php endif; ?>
	<div class="storycontent">
		<?php the_content(__('(more...)')); ?>
	</div>

<?php if(!usces_is_item()): ?>
	<div class="feedback">
		<?php wp_link_pages(); ?>
		<?php comments_popup_link(__('Comments (0)'), __('Comments (1)'), __('Comments (%)')); ?>
	</div>
<?php endif; ?>

</div>


<?php endwhile; else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>

<?php posts_nav_link(' &#8212; ', __('&laquo; Newer Posts'), __('Older Posts &raquo;')); ?>

</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_footer(); ?>
