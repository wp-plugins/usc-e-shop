<?php
/**
 * Template Name: One column, no sidebar
 *
 * A custom page template without sidebar.
 *
 * The "Template Name:" bit above allows this to be selectable
 * from a dropdown menu on the edit page screen.
 *
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Pop Cloud Blue Theme
 */

get_header(); ?>

<div id="content" class="one-colum">
<div class="catbox">

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

	<div class="post" id="<?php echo $post->post_name; ?>">
		<h1 class="entry-title"><?php the_title(); ?></h1>
		
		<div class="entry">
			<?php the_content(); ?>
		<?php wp_link_pages(array('before' => '<p><strong>' . __('Pages:', 'uscestheme') . '</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
		</div>
        <?php comments_template( '', true ); ?>
	</div>
	
<?php endwhile; endif; ?>
<?php edit_post_link( __( 'Edit', 'uscestheme' ), '<span class="edit-link">', '</span>' ); ?>
	
</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_footer(); ?>
