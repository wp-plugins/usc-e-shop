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
888
<?php
	/* Queue the first post, that way we know
	 * what date we're dealing with (if that is the case).
	 *
	 * We reset this later so we can run the loop
	 * properly with a call to rewind_posts().
	 */
	if ( have_posts() )
		the_post();
?>

<h1 class="title">
<?php if (is_category()) { ?>
	<?php printf( __( 'Category Archives: %s', 'uscestheme' ), '<span>' . single_cat_title( '', false ) . '</span>' ); ?>
	
<?php } elseif( is_tag() ) { ?>
	<?php printf( __( 'Tag Archives: %s', 'uscestheme' ), '<span>' . single_tag_title( '', false ) . '</span>' ); ?>
	
<?php } elseif (is_day()) { ?>
	<?php printf( __( 'Daily Archives: <span>%s</span>', 'uscestheme' ), get_the_date('Y年n月j日') ); ?>
	
<?php } elseif (is_month()) { ?>
	<?php printf( __( 'Monthly Archives: <span>%s</span>', 'uscestheme' ), get_the_date('Y年n月') ); ?>
	
<?php } elseif (is_year()) { ?>
	<?php printf( __( 'Yearly Archives: <span>%s</span>', 'uscestheme' ), get_the_date('Y年') ); ?>
	
<?php } elseif (is_author()) { ?>
	<?php printf( __( 'Author Archives: %s', 'uscestheme' ), "<span class='vcard'><a class='url fn n' href='" . get_author_posts_url( get_the_author_meta( 'ID' ) ) . "' title='" . esc_attr( get_the_author() ) . "' rel='me'>" . get_the_author() . "</a></span>" ); ?>
	
<?php } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
	<?php _e( 'Blog Archives', 'uscestheme' ); ?>
	
<?php } ?>
</h1>

<?php
	/* Since we called the_post() above, we need to
	 * rewind the loop back to the beginning that way
	 * we can run the loop properly, in full.
	 */
	rewind_posts();
?>

	<?php /* Display navigation to next/previous pages when applicable */ ?>
	<?php if ( $wp_query->max_num_pages > 1 ) : ?>
		<div id="nav-above" class="navigation">
			<div class="nav-previous"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
			<div class="nav-next"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
		</div><!-- #nav-above -->
	<?php endif; ?>
		
	<?php /* If there are no posts to display, such as an empty archive page */ ?>
	<?php if ( ! have_posts() ) : ?>
		<div id="post-0" class="post error404 not-found">
			<h2 class="entry-title"><?php _e( 'Not Found', 'uscestheme' ); ?></h2>
			<div class="entry-content">
				<p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'uscestheme' ); ?></p>
				<?php get_search_form(); ?>
			</div><!-- .entry-content -->
		</div><!-- #post-0 -->
	<?php endif; ?>
	
	<?php /* Start the Loop.*/ ?>
	<?php while (have_posts()) : the_post(); ?>
	
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'uscestheme' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
	
			<div class="entry-meta">
				<?php welcart_posted_on(); ?>
			</div><!-- .entry-meta -->
	
	<?php if ( is_archive() ) : // Only display excerpts for archives. ?>
			<div class="entry-summary">
				<?php the_excerpt(); ?>
			</div><!-- .entry-summary -->
	<?php else : ?>
			<div class="entry-content">
				<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'uscestheme' ) ); ?>
				<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'uscestheme' ), 'after' => '</div>' ) ); ?>
			</div><!-- .entry-content -->
	<?php endif; ?>
	
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
		</div><!-- #post-## -->
	
		<?php comments_template( '', true ); ?>
	
	<?php endwhile; // End the loop. Whew. ?>
	
	<?php /* Display navigation to next/previous pages when applicable */ ?>
	<?php if (  $wp_query->max_num_pages > 1 ) : ?>
		<div id="nav-below" class="navigation">
			<div class="nav-previous"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
			<div class="nav-next"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
		</div><!-- #nav-below -->
	<?php endif; ?>

</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>