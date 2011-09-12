<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Pop Cloud Blue Theme
 */
get_header();
?>

<div id="content">

<div class="center">
<div class="catbox">

<div id="frame">
	<h1 class="title"><?php esc_html_e( single_cat_title('', false) ); ?></h1>

<?php if( usces_is_cat_of_item($wp_query->query_vars['cat']) ) : //商品カテゴリーの場合(Welcart0.6以降) ?>

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

<?php else : //商品以外のカテゴリーの場合 ?>

	<?php if (have_posts()) : //記事が有ったら ?>
	
	<div class="navigation clearfix">
		<div style="float:right;"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
		<div style="float:left;"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
	</div>
	
	<?php while (have_posts()) : the_post(); ?>
	<div class="archive">
	<div <?php post_class(); ?>>
		<h2 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>

		<div class="entry-meta">
			<?php welcart_posted_on(); ?>
		</div><!-- .entry-meta -->

		<div class="entry">
			<?php the_excerpt() ?>
		</div>

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
	</div>
	</div><!-- end of archive -->
	<?php endwhile; ?>
	
	<div class="navigation clearfix">
		<div style="float:right;"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
		<div style="float:left;"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
	</div>

	<?php else : //記事が無かったら ?>
	
	<div id="post-0" class="post no-results not-found">
	
		<h2 class="entry-title"><?php _e( 'Nothing Found', 'uscestheme' ); ?></h2>
		
		<div class="entry-content">
			<p><?php _e( 'Sorry, but nothing matched your search criteria. Please try again with some different keywords.', 'uscestheme' ); ?></p>
			
			<?php get_search_form(); ?>
		</div><!-- .entry-content -->
		
	</div><!-- #post-0 -->
		
	<?php endif; ?>
	
<?php endif; ?>

	<div class="bottom"></div>
</div><!-- end of frame -->

</div><!-- end of catbox -->
</div><!-- end of center -->

</div><!-- end of content -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>