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
	
	<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
		<h1><?php the_title(); ?></h1>
		
		<?php if(!usces_is_item()): // 商品だったら除外 ?>
		<div class="entry-meta"><?php welcart_posted_on(); ?></div>
		<?php endif; ?>
		
		<div class="entry-content">
			<?php the_content(); ?>
			<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'uscestheme' ), 'after' => '</div>' ) ); ?>
		</div><!-- .entry-content -->
		
		<?php if(!usces_is_item()): // 商品だったら除外 ?>
		<div class="entry-utility">
			<?php welcart_posted_in(); ?>
			<?php edit_post_link( __( 'Edit', 'uscestheme' ), '<span class="edit-link">', '</span>' ); ?>
		</div><!-- .entry-utility -->
		
		<?php comments_template( '', true ); ?>
		<?php endif; ?>

	</div>
	
<?php endwhile; else: ?>
	<div id="post-0" class="post no-results not-found">
	
		<h2 class="entry-title"><?php _e( 'Nothing Found', 'uscestheme' ); ?></h2>
		
		<div class="entry-content">
			<p><?php _e( 'Sorry, but nothing matched your search criteria. Please try again with some different keywords.', 'uscestheme' ); ?></p>
			
			<?php get_search_form(); ?>
		</div><!-- .entry-content -->
		
	</div><!-- #post-0 -->
<?php endif; ?>

</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
