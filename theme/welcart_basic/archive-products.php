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
archive-products.php
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

<?php if (have_posts()) : //商品が有ったら ?>

<div class="navigation clearfix">
	<div style="float:right;"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
	<div style="float:left;"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
</div>

<?php while (have_posts()) : the_post(); ?>
<div class="archive">
<div <?php post_class(); ?>>
	<h2 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>
	
	<div class="entry clearfix">
		<?php the_content() ?>
	</div>
</div>
</div><!-- end of archive -->
<?php endwhile; ?>

<div class="navigation clearfix">
	<div style="float:right;"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
	<div style="float:left;"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
</div>

<?php else : //商品が無かったら ?>

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