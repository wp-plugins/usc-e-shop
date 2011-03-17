<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
get_header();

get_sidebar( 'home' );
?>

<div id="content" class="three-column">
	<div class="top_image"><img src="<?php bloginfo('template_url'); ?>/images/image_top.jpg" alt="<?php bloginfo('name'); ?>" width="560" height="300" /></div>
	<div class="title"><?php _e('Items recommended','usces') ?></div>
	<div class="clearfix">
	
	<?php $reco_ob = new wp_query(array('category_name'=>'itemreco', 'posts_per_page'=>8, 'post_status'=>'publish')); ?>
	<?php if ($reco_ob->have_posts()) : while ($reco_ob->have_posts()) : $reco_ob->the_post(); usces_the_item(); ?>
	<div class="thumbnail_box">
		<div class="thumimg"><a href="<?php the_permalink() ?>"><?php usces_the_itemImage($number = 0, $width = 108, $height = 108 ); ?></a></div>
		<div class="thumtitle"><a href="<?php the_permalink() ?>" rel="bookmark"><?php usces_the_itemName(); ?>&nbsp;(<?php usces_the_itemCode(); ?>)</a></div>
	<?php if (usces_is_skus()) : ?>
		<div class="price"><?php _e('$', 'usces'); ?><?php usces_the_firstPrice(); ?><?php usces_guid_tax(); ?></div>
	<?php endif; ?>
	</div>
	
	<?php endwhile; else: ?>
	<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
	<?php endif; wp_reset_query(); ?>
	</div>
</div><!-- end of content -->

<?php get_footer(); ?>
