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

<h1 class="pagetitle"><?php printf( __( 'Search Results for: %s', 'uscestheme' ), '<span>' . get_search_query() . '</span>' ); ?></h1>

<?php if (have_posts()) : ?>

	<div class="navigation clearfix">
		<div class="alignleft"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
		<div class="alignright"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
	</div>
	
	<?php while (have_posts()) : the_post(); ?>
	<div <?php post_class(); ?>>
	
		<h2 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'uscestheme' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
		
		<?php if(!usces_is_item()): ?>
		<div class="entry-meta">
			<?php welcart_posted_on(); ?>
		</div><!-- .entry-meta -->
		<?php endif; ?>

		<div class="entry clearfix">
			<?php the_excerpt(); ?>
		</div>
		
		<?php if(!usces_is_item()): ?>
		<div class="entry-utility">
			<?php if ( count( get_the_category() ) ) : ?>
				<span class="cat-links">
					<?php printf( __( '<span class="%1$s">Posted in</span> %2$s', 'uscestheme' ), 'entry-utility-prep entry-utility-prep-cat-links', get_the_category_list( ', ' ) ); ?>
				</span>
				<span class="meta-sep">|</span>
			<?php endif; ?>
			<?php
				$tags_list = get_the_tag_list( '', ', ' );
				if ( $tags_list ):
			?>
				<span class="tag-links">
					<?php printf( __( '<span class="%1$s">Tagged</span> %2$s', 'uscestheme' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?>
				</span>
				<span class="meta-sep">|</span>
			<?php endif; ?>
			<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'uscestheme' ), __( '1 Comment', 'uscestheme' ), __( '% Comments', 'uscestheme' ) ); ?></span>
			<?php edit_post_link( __( 'Edit', 'uscestheme' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
		</div><!-- .entry-utility -->
		<?php endif; ?>
		
	</div>
	
	<?php endwhile; ?>
	
	<div class="navigation clearfix">
		<div class="alignleft"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
		<div class="alignright"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
	</div>

<?php else : ?>

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
