<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
get_header();
?>

<div id="content" class="two-column">

<?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
<?php /* If this is a category archive */ if (is_category()) { ?>
<h1 class="pagetitle"><?php printf( __( 'Category Archives: %s', 'uscestheme' ), '<span>' . single_cat_title( '', false ) . '</span>' ); ?></h1>
<?php /* If this is a tag archive */ } elseif( is_tag() ) { ?>
<h1 class="pagetitle"><?php printf( __( 'Tag Archives: %s', 'uscestheme' ), '<span>' . single_tag_title( '', false ) . '</span>' ); ?></h1>
<?php /* If this is a daily archive */ } elseif (is_day()) { ?>
<h1 class="pagetitle"><?php printf( __( 'Daily Archives: <span>%s</span>', 'uscestheme' ), get_the_time(__('Y/m/d'))); ?></h1>
<?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
<h1 class="pagetitle"><?php printf( __( 'Monthly Archives: <span>%s</span>', 'uscestheme' ), get_the_time(__('F, Y'))); ?></h1>
<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
<h1 class="pagetitle"><?php printf( __( 'Yearly Archives: <span>%s</span>', 'uscestheme' ), get_the_time(__('Y'))); ?></h1>
<?php /* If this is an author archive */ } elseif (is_author()) { ?>
<h1 class="pagetitle"><?php printf( __( 'Author Archives: %s', 'uscestheme' ), "<span class='vcard'><a class='url fn n' href='" . get_author_posts_url( get_the_author_meta( 'ID' ) ) . "' title='" . esc_attr( get_the_author() ) . "' rel='me'>" . get_the_author() . "</a></span>" ); ?></h1>
<?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
<h1 class="pagetitle"><?php _e( 'Blog Archives', 'uscestheme' ); ?></h1>
<?php } ?>

<div class="catbox">
	<?php if (have_posts()) : ?>
	<div class="navigation clearfix">
		<div class="alignright"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'uscestheme' ) ); ?></div>
		<div class="alignleft"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'uscestheme' ) ); ?></div>
	</div>
	
	<?php while (have_posts()) : the_post(); ?>
	<div <?php post_class(); ?>>
		<h2 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'uscestheme' ), the_title_attribute( 'echo=0' ) ); ?>"><?php the_title(); ?></a></h2>
		<div class="entry clearfix">
			<?php if(!usces_is_item()): ?>
			<p><small><?php the_date('Y/n/j'); ?></small></p>
			<?php endif; ?>
			<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'uscestheme' ) ) ?>
		</div>
	</div><!-- end of post -->
	<?php endwhile; ?>
	
	<div class="navigation clearfix">
		<div class="alignright"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'uscestheme' ) ); ?></div>
		<div class="alignleft"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'uscestheme' ) ); ?></div>
	</div>

<?php else : ?>

	<div id="post-0" class="post error404 not-found">
		<h2 class="entry-title"><?php _e( 'Not Found', 'uscestheme' ); ?></h2>
		<div class="entry-content">
			<p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'uscestheme' ); ?></p>
			<?php get_search_form(); ?>
		</div><!-- .entry-content -->
	</div><!-- #post-0 -->
	
<?php endif; ?>

</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_sidebar( 'other' ); ?>

<?php get_footer(); ?>