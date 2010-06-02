<?php
/**
 * <meta content="charset=UTF-8">
 * @package WordPress
 * @subpackage Welcart Default Theme
 */
get_header();
?>

<div id="content">
<div class="catbox">
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div class="post" id="<?php echo $post->post_name; ?>">
		<h2><?php the_title(); ?></h2>
		<div class="entry">
		<?php the_content(); ?>
		<?php wp_link_pages(array('before' => '<p><strong>' . __('Pages:', 'kubrick') . '</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
		</div>
	</div>
	<?php endwhile; endif; ?>
	<?php edit_post_link(__('Edit this entry.', 'kubrick'), '<p>', '</p>'); ?>
</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_footer(); ?>
