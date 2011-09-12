<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Pop Cloud Blue Theme
 */
get_header();
?>

<div id="content">
<div class="catbox">
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div class="post" id="<?php echo $post->post_name; ?>">
		<?php if ( is_front_page() ) { ?>
			<h2 class="entry-title"><?php the_title(); ?></h2>
		<?php } else { ?>
			<h1 class="entry-title"><?php the_title(); ?></h1>
		<?php } ?>
		<div class="entry">
		<?php the_content(); ?>
		<?php wp_link_pages(array('before' => '<p><strong>' . __('Pages:', 'uscestheme') . '</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
		</div>
		<?php comments_template( '', true ); ?>
	</div>
	<?php endwhile; endif; ?>
	<?php edit_post_link(__('Edit', 'uscestheme'), '<p>', '</p>'); ?>
</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
