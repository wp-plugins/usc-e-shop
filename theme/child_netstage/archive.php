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
<h1 class="pagetitle"><?php printf(__('%s', 'usces'), single_cat_title('', false)); ?></h1>
<?php /* If this is a tag archive */ } elseif( is_tag() ) { ?>
<h1 class="pagetitle"><?php printf(__('Posts Tagged &#8216;%s&#8217;', 'usces'), single_tag_title('', false) ); ?></h1>
<?php /* If this is a daily archive */ } elseif (is_day()) { ?>
<h1 class="pagetitle"><?php printf(_c('Archive for %s|Daily archive page', 'usces'), get_the_time(__('Y/m/d'))); ?></h1>
<?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
<h1 class="pagetitle"><?php printf(_c('Archive for %s|Monthly archive page', 'kubrick'), get_the_time(__('Y/m/d'))); ?></h1>
<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
<h1 class="pagetitle"><?php printf(_c('Archive for %s|Yearly archive page', 'kubrick'), get_the_time(__('Y/m/d'))); ?></h1>
<?php /* If this is an author archive */ } elseif (is_author()) { ?>
<h1 class="pagetitle"><?php _e('Author Archive', 'kubrick'); ?></h1>
<?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
<h1 class="pagetitle"><?php _e('Blog Archives', 'kubrick'); ?></h1>
<?php } ?>
<div class="contbody">
<div class="catbox">
	<?php if (have_posts()) : ?>
	<div class="navigation clearfix">
		<div class="alignleft"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
		<div class="alignright"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
	</div>

	<div class="categoryarea clearfix">
	<?php $itemcount = 1; ?>
	<?php while (have_posts()) : the_post(); usces_the_item();?>
	<?php if($itemcount % 4 == 1) echo '<div class="thumnail_line clearfix">';?>
	<div <?php post_class(); ?>>
		<div class="thumbnail_box slim<?php if($itemcount % 4 == 0) echo " right";?>">
			<div class="thumtitle"><a href="<?php the_permalink() ?>" rel="bookmark"><?php usces_the_itemName(); ?></a></div>
			<div class="thumimg"><a href="<?php the_permalink() ?>"><?php usces_the_itemImage($number = 0, $width = 130, $height = 130 ); ?></a></div>
			<?php $content_summary = str_replace("\n", "", strip_tags($post->post_content)); ?>
			<div class="thumcomment">
				<ul class="listtag clearfix">
				<?php if(has_category('itemnew')): ?>
					<li><img height="20" width="60" alt="NEW" src="<?php bloginfo('stylesheet_directory'); ?>/images/common/tag_new_mini.png" /></li>
				<?php endif; ?>
				<?php if(has_category('itemreco')): ?>
					<li><img height="20" width="60" alt="オススメ" src="<?php bloginfo('stylesheet_directory'); ?>/images/common/tag_recommend_mini.png"></li>
				<?php endif; ?>
				<?php if(has_tag('fewer')): ?>
					<li><img height="20" width="60" alt="残りわずか" src="<?php bloginfo('stylesheet_directory'); ?>/images/common/tag_few_mini.png"></li>
				<?php endif; ?>
				<?php if(has_tag('limited')): ?>
					<li><img height="20" width="60" alt="限定品" src="<?php bloginfo('stylesheet_directory'); ?>/images/common/tag_limited_mini.png"></li>
				<?php endif; ?>
				</ul>
				<?php echo (mb_strlen($content_summary) > 27) ? mb_substr($content_summary, 0, 25) . "..." : $content_summary; ?>
			</div>
		<?php if (usces_is_skus()) : ?>
			<div class="price"><a href="<?php the_permalink() ?>"><?php _e('$', 'usces'); ?><?php usces_the_firstPrice(); ?><?php usces_guid_tax(); ?></a></div>
		<?php endif; ?>
		</div>
	</div>
	<?php if($itemcount % 4 == 0) echo '</div>';?>
	<?php $itemcount += 1; ?>
	<?php endwhile; ?>
	<?php if($itemcount % 4 != 1) echo '</div>';?>
	</div>

	<div class="navigation clearfix">
		<div class="alignleft"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
		<div class="alignright"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
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
</div><!-- end of conbody -->
</div><!-- end of content -->

<?php get_footer(); ?>