<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
get_header();
?>

<div id="content" class="two-column">

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<h1 class="pagetitle"><?php the_title(); ?></h1>

<div class="catbox">
<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
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
	</div>

	<?php comments_template( '', true ); ?>

<?php endif; ?>
</div>
</div><!-- end of catbox -->


<?php endwhile; else: ?>
<div class="catbox">
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
</div><!-- end of catbox -->
<?php endif; ?>

<?php posts_nav_link(' &#8212; ', __('&laquo; Newer Posts'), __('Older Posts &raquo;')); ?>

</div><!-- end of content -->

<?php get_sidebar( 'other' ); ?>

<?php get_footer(); ?>
