<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
get_header();
?>

<div id="content">

<?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
<?php /* If this is a category archive */ if (is_category()) { ?>
<h2 class="pagetitle"><?php printf(__('%s', 'kubrick'), single_cat_title('', false)); ?></h2>
<?php /* If this is a tag archive */ } elseif( is_tag() ) { ?>
<h2 class="pagetitle"><?php printf(__('Posts Tagged &#8216;%s&#8217;', 'kubrick'), single_tag_title('', false) ); ?></h2>
<?php /* If this is a daily archive */ } elseif (is_day()) { ?>
<h2 class="pagetitle"><?php printf(_c('Archive for %s|Daily archive page', 'kubrick'), get_the_time(__('F jS, Y', 'kubrick'))); ?></h2>
<?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
<h2 class="pagetitle"><?php printf(_c('Archive for %s|Monthly archive page', 'kubrick'), get_the_time(__('F, Y', 'kubrick'))); ?></h2>
<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
<h2 class="pagetitle"><?php printf(_c('Archive for %s|Yearly archive page', 'kubrick'), get_the_time(__('Y', 'kubrick'))); ?></h2>
<?php /* If this is an author archive */ } elseif (is_author()) { ?>
<h2 class="pagetitle"><?php _e('Author Archive', 'kubrick'); ?></h2>
<?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
<h2 class="pagetitle"><?php _e('Blog Archives', 'kubrick'); ?></h2>
<?php } ?>

<div class="catbox">
	<?php if (have_posts()) : ?>
	<div class="navigation clearfix">
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
	</div><!-- end of post -->
	<?php endwhile; ?>
	
	<div class="navigation clearfix">
		<div class="alignleft"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
		<div class="alignright"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
	</div>

<?php else : ?>

	<?php if ( is_category() ) : // If this is a category archive ?>
	<p><?php echo single_cat_title('', false); ?><?php _e('in not yet registered', 'usces') ?></p>
	<?php elseif( is_date() ) : ?>
	<p><?php _e('Data for this date is not yet registered.', 'usces') ?></p>
	<?php elseif( is_author() ) : $userdata = get_userdatabylogin(get_query_var('author_name')); ?>
	<p><?php _e('Data by', 'usces') ?> <?php echo $userdata->display_name; ?> <?php _e('is not yet registered.', 'usces') ?></p>
	<?php else : ?>
	<p><?php echo __('No posts found.', 'kubrick'); ?></p>
	<?php endif; ?>
	
<?php endif; ?>

</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_footer(); ?>