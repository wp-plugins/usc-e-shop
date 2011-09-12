<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Pop Cloud Blue Theme
 * 
 * Only Cart page and Member page are displayed. 
 */
?>
<div id="sidebar" class="sidebar">

<ul id="sidebar-in">
<?php if ( ! dynamic_sidebar( 'cartmemberleft-widget-area' ) ) : ?>

	<!-- widget_welcart_category -->
	<li id="welcart_category-3" class="widget widget_welcart_category">
		<div class="widget_title"><?php _e('Item Category','usces') ?></div>
		<ul class="welcart_widget_body">
		<?php $cats = get_category_by_slug('itemgenre'); ?>
		<?php wp_list_categories('orderby=id&use_desc_for_title=0&child_of='.$cats->term_id.'&title_li='); ?>
 		</ul>
	</li>
	
	<!-- welcart_featured -->
	<li id="welcart_featured-3" class="widget widget_welcart_featured">
		<div class="widget_title"><?php _e('Items recommended','usces') ?></div>
		<ul class="welcart_featured_body welcart_widget_body">
			<li>
			<?php
			$offset = usces_posts_random_offset(get_posts('category='.usces_get_cat_id( 'itemreco' )));
			$myposts = get_posts('numberposts=1&offset='.$offset.'&category='.usces_get_cat_id( 'itemreco' ));
			foreach($myposts as $post) : usces_the_item();
			?>
				<div class="thumimg"><a href="<?php the_permalink() ?>"><?php usces_the_itemImage($number = 0, $width = 150, $height = 150 ); ?></a></div>
				<div class="thumtitle"><a href="<?php the_permalink() ?>" rel="bookmark"><?php usces_the_itemName(); ?>&nbsp;(<?php usces_the_itemCode(); ?>)</a></div>
			<?php endforeach; ?>
			</li>
		</ul>
 	</li>

<?php endif; ?>
</ul>

</div>