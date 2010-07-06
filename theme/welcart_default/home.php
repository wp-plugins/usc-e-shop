<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
get_header();
?>
<div id="content">
	<div class="top_image"><img src="<?php bloginfo('template_url'); ?>/images/image_top.jpg" alt="<?php bloginfo('name'); ?>" width="560" height="300" /></div>
	<div class="title"><?php _e('Items recommended','usces') ?></div>
	<div class="clearfix">
	
	<?php query_posts('category_name=itemreco&post_status=publish&post_mime=item'); ?>
	<?php if (have_posts()) : while (have_posts()) : the_post(); usces_the_item(); ?>
	<div class="thumbnail_box">
		<div class="thumimg"><a href="<?php the_permalink() ?>"><?php usces_the_itemImage($number = 0, $width = 108, $height = 108 ); ?></a></div>
		<div class="thumtitle"><a href="<?php the_permalink() ?>" rel="bookmark"><?php usces_the_itemName(); ?>&nbsp;(<?php usces_the_itemCode(); ?>)</a></div>
	<?php if (usces_is_skus()) : ?>
		<div class="price"><?php _e('$', 'usces'); ?><?php usces_the_firstPrice(); ?><?php usces_guid_tax(); ?></div>
	<?php endif; ?>
	
	</div>
	
	<?php endwhile; else: ?>
	<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
	<?php endif; ?>
	</div>
</div><!-- end of content -->

<?php get_footer(); ?>
