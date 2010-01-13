<?php
/**
 * @package WordPress
 * @subpackage uCart Default Theme
 */
get_header();
?>

<div class="center">
<div class="top_image"><img src="<?php bloginfo('template_url'); ?>/images/image_top.jpg" alt="<?php bloginfo('name'); ?>" width="560" height="240" /></div>
<div class="title"><?php _e('items recommended','usces') ?></div>
<div class="clearfix">


<?php //$paged = get_query_var('paged'); ?>
<?php //$posts_per_page = 8; ?>
<?php //$order = 'DESC'; ?>
<?php //query_posts('category_name=itemreco&status=post&paged=' . $paged . '&posts_per_page=' . $posts_per_page . '&order='. $order); ?>
<?php query_posts('category_name=itemreco&status=post'); ?>
<?php if (have_posts()) : while (have_posts()) : the_post(); usces_the_item(); ?>
<?php //remove_filter('the_excerpt', array($usces, 'filter_cartContent'), 20); ?>
<?php //remove_filter('the_content', array($usces, 'filter_cartContent'), 20); ?>
<div class="thumbnail_box">
	<div class="thumimg"><a href="<?php the_permalink() ?>"><?php usces_the_itemImage($number = 0, $width = 108, $height = 108 ); ?></a></div>
	<div class="thumtitle"><a href="<?php the_permalink() ?>" rel="bookmark"><?php usces_the_itemName(); ?>&nbsp;(<?php usces_the_itemCode(); ?>)</a></div>
<?php if (usces_is_skus()) : ?>
	<div class="price">&yen;<?php usces_the_firstPrice(); ?><?php usces_guid_tax(); ?></div>
<?php endif; ?>

</div>

<?php //comments_template(); // Get wp-comments.php template ?>

<?php endwhile; else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>
</div>

<?php //posts_nav_link(' &#8212; ', __('&laquo; 前のページ'), __('次のページ &raquo;')); ?>

</div>

<?php get_footer(); ?>
