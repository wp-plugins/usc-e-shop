<?php
/**
 * @package Welcart
 * @subpackage uCart Default Theme
 */
?>
<!-- begin left sidebar -->
<div id="leftbar" class="sidebar">

<ul>
<?php 	/* Widgetized sidebar, if you have the plugin installed. */
	if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
	
	<li id="ucart_search-0" class="widget widget_ucart_search">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/find.png" alt="<?php _e('keyword search','usces') ?>" width="24" height="24" /><?php _e('keyword search','usces') ?></div>
		<ul class="ucart_widget_body"><li>
		<form method="get" id="searchform" action="<?php bloginfo('home'); ?>" >
		<input type="text" value="" name="s" id="s" class="searchtext" /><input type="submit" id="searchsubmit" />
		<div><a href="<?php echo USCES_CART_URL; ?>&page=search_item"><?php _e('An article category keyword search','usces') ?>&gt;</a></div>
		</form>
		</li></ul>
	</li>
	<li id="ucart_category-0" class="widget widget_ucart_category">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/category2.png" alt="<?php _e('Item Category','usces') ?>" width="24" height="24" /><?php _e('Item Category','usces') ?></div>
		<ul class="ucart_widget_body">
		<?php $cats = get_category_by_slug('itemgenre'); ?>
		<?php wp_list_categories('orderby=id&use_desc_for_title=0&child_of='.$cats->term_id.'&title_li='); ?>
 		</ul>
	</li>
	
	<li id="ucart_calendar-0" class="widget widget_ucart_calendar">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/calendar.png" alt="<?php _e('Business Calendar','usces') ?>" width="24" height="24" /><?php _e('Business Calendar','usces') ?></div>
		<ul class="ucart_widget_body"><li>
		<?php usces_the_calendar(); ?>
		</li></ul>
	</li>

<?php endif; ?>

</ul>

</div>
<!-- end left sidebar -->

<!-- begin right sidebar -->
<div id="rightbar" class="sidebar">

<ul>
<?php 	/* Widgetized sidebar, if you have the plugin installed. */
	if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar(2) ) : ?>
	
	<li id="ucart_featured-0" class="widget widget_ucart_featured">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/osusume.png" alt="<?php _e('Items recommended','usces') ?>" width="24" height="24" /><?php _e('Items recommended','usces') ?></div>
		<ul class="ucart_widget_body"><li>
			<?php
			$offset = usces_posts_random_offset(get_posts('category='.usces_get_cat_id( 'itemreco' )));
			$myposts = get_posts('numberposts=1&offset='.$offset.'&category='.usces_get_cat_id( 'itemreco' ));
			foreach($myposts as $post) : usces_the_item();
			?>
				<div class="thumimg"><a href="<?php the_permalink() ?>"><?php usces_the_itemImage($number = 0, $width = 150, $height = 150 ); ?></a></div>
				<div class="thumtitle"><a href="<?php the_permalink() ?>" rel="bookmark"><?php usces_the_itemName(); ?>&nbsp;(<?php usces_the_itemCode(); ?>)</a></div>
			<?php endforeach; ?>
		</li></ul>
 	</li>
	
	<li id="ucart_bestseller-0" class="widget widget_ucart_bestseller">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/best-seller.png" alt="<?php _e('best seller','usces') ?>" width="24" height="24" /><?php _e('best seller','usces') ?></div>
		<ul class="ucart_widget_body">
		<?php usces_list_bestseller(10); ?>
		</ul>
	</li>
 
</ul>

<?php endif; ?>

</div>
<!-- end right sidebar -->
