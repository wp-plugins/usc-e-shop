<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Pop Cloud Blue Theme
 */
get_header();
?>
<div id="content">
		
	<?php if ( ! dynamic_sidebar( 'home-widget-area' ) ) : ?>
	<div id="top_reco">
		<h3 class="title"><?php _e('Items recommended','usces') ?></h3>
		<ul class="clearfix">
		<?php $reco_ob = new wp_query(array(WELCART_GENRE=>'reco', 'posts_per_page'=>10, 'post_status'=>'publish')); ?>
		<?php if ($reco_ob->have_posts()) : while ($reco_ob->have_posts()) : $reco_ob->the_post(); usces_the_item(); ?>
			<li class="thumbnail_box">
				<div class="thumimg"><a href="<?php the_permalink() ?>"><?php the_post_thumbnail(array(108,108));//usces_the_itemImage($number = 0, $width = 108, $height = 108 ); ?></a></div>
				<div class="thumtitle"><a href="<?php the_permalink() ?>"><?php usces_the_itemName(); ?>&nbsp;(<?php usces_the_itemCode(); ?>)</a></div>
			<?php if (usces_have_zaiko_anyone()) : ?>
				<div class="price"><?php usces_crform( usces_the_firstPrice('return'), true, false ); ?></div>
			<?php else: ?>
				<div class="status"><?php _e('Sold Out', 'usces'); ?></div>
			<?php endif; ?>
			</li>
		<?php endwhile; else: ?>
		<li id="nothing"><?php _e('Sorry, no posts matched your criteria.'); ?></li>
		<?php endif; wp_reset_query(); ?>
		</ul>
	</div>
	<?php endif; ?>
    
<?php get_sidebar('content'); ?>

</div><!-- end of content -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
