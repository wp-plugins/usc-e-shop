<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
get_header();
?>

<div id="content">

<h2 class="pagetitle"><?php _e('Search Results', 'usces'); ?></h2>

<div class="catbox">

<?php if (have_posts()) : ?>

	
	<div class="navigation">
	<div class="alignleft"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
	<div class="alignright"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
	</div>
	
	<?php while (have_posts()) : the_post(); ?>
	<div <?php post_class(); ?>>
	<h3 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h3>
	<div class="entry clearfix">
	<?php if(!usces_is_item()): ?>
	<p><small><?php the_date('Y/n/j'); ?></small></p>
	<?php endif; ?>
	<?php the_content() ?>
	</div>
	
	</div>
	
	<?php endwhile; ?>
	
	<div class="navigation">
	<div class="alignleft"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
	<div class="alignright"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
	</div>

<?php else : ?>

	<p><?php echo __('No posts found.', 'usces'); ?></p>
	
<?php endif; ?>

</div><!-- end of catbox -->
</div><!-- end of content -->


<?php get_footer(); ?>
