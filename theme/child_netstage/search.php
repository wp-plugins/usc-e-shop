<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
get_header();
?>

<div id="content">

<h1 class="serchresulttitle"><?php _e('Search Results', 'usces'); ?></h1>
<div class="contbody">
<div class="searchcond">
	<p class="catcond">商品カテゴリ：<?php echo 'シャフト'; ?></p>
	<p class="wordcond">キーワード：<?php echo wp_specialchars($s); ?></p>
</div>

<?php if (have_posts()) : ?>
	<div class="navigation clearfix">
		<div class="alignleft"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
		<div class="alignright"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
	</div>

	<div class="resultarea clearfix">
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

	<div class="navigation">
	<div class="alignleft"><?php previous_posts_link(__('&laquo; Previous article', 'usces')); ?></div>
	<div class="alignright"><?php next_posts_link(__('Next article &raquo;', 'usces')); ?></div>
	</div>
<?php else : ?>
	<p><?php echo __('No posts found.', 'usces'); ?></p>
<?php endif; ?>
</div><!-- end of contbodyt -->
</div><!-- end of content -->


<?php get_footer(); ?>
