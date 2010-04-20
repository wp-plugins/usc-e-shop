<?php
/**
 * @package Welcart
 * @subpackage uCart Default Theme
 */
?>
<?php get_header(); ?>

<div class="center">

<h2 class="pagetitle"><?php _e('Search Results', 'kubrick'); ?></h2>

<div class="catbox">

<?php if (have_posts()) : ?>

	
	<div class="navigation">
	<div class="alignleft"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
	<div class="alignright"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
	</div>
	
	<?php while (have_posts()) : the_post(); ?>
	<div <?php post_class(); ?>>
	<h3 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf(__('Permanent Link to %s', 'kubrick'), the_title_attribute('echo=0')); ?>"><?php the_title(); ?></a></h3>
	<div class="entry clearfix">
	<?php if(!usces_is_item()): ?>
	<p><small><?php the_date('Y/n/j'); ?></small></p>
	<?php endif; ?>
	<?php the_content() ?>
	</div>
	
	<!--<p class="postmetadata"><?php the_tags(__('Tags:', 'kubrick'), ', ', '<br />'); ?> <?php printf(__('Posted in %s', 'kubrick'), get_the_category_list(', ')); ?> | <?php edit_post_link(__('Edit', 'kubrick'), '', ' | '); ?>  <?php comments_popup_link(__('No Comments &#187;', 'kubrick'), __('1 Comment &#187;', 'kubrick'), __('% Comments &#187;', 'kubrick'), '', __('Comments Closed', 'kubrick') ); ?></p>-->
	</div>
	
	<?php endwhile; ?>
	
	<div class="navigation">
	<div class="alignleft"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
	<div class="alignright"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
	</div>

<?php else : ?>

	<p><?php echo __('No posts found.', 'kubrick'); ?></p>
	
<?php endif; ?>

</div><!-- end of catbox -->
</div><!-- end of center -->


<?php get_footer(); ?>
