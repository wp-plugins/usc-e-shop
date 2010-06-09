<?php
/**
 * <meta content="charset=UTF-8">
 * @package WordPress
 * @subpackage Welcart Default Theme
 */
?>
<!-- begin left sidebar -->
<div id="leftbar" class="sidebar">
<ul>
<?php 	/* Widgetized sidebar, if you have the plugin installed. */
	if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
	<li id="welcart_search-3" class="widget widget_welcart_search">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/search.png" alt="<?php _e('keyword search','usces') ?>" /><?php _e('keyword search','usces') ?></div>
		<ul class="welcart_search_body welcart_widget_body">
			<li>
			<form method="get" id="searchform" action="<?php bloginfo('home'); ?>" >
			<input type="text" value="" name="s" id="s" class="searchtext" /><input type="submit" id="searchsubmit" value="<?php _e('Search','usces') ?>" />
			<div><a href="<?php echo USCES_CART_URL; ?>&page=search_item"><?php _e('An article category keyword search','usces') ?>&gt;</a></div>
			</form>
			</li>
		</ul>
	</li>
	<li id="welcart_category-3" class="widget widget_welcart_category">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/category.png" alt="<?php _e('Item Category','usces') ?>" /><?php _e('Item Category','usces') ?></div>
		<ul class="welcart_widget_body">
		<?php $cats = get_category_by_slug('itemgenre'); ?>
		<?php wp_list_categories('orderby=id&use_desc_for_title=0&child_of='.$cats->term_id.'&title_li='); ?>
 		</ul>
	</li>
	<li id="welcart_post-3" class="widget widget_welcart_post">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/post.png" alt="<?php _e('Information','usces') ?>" /><?php _e('Information','usces') ?></div>
		<ul class="welcart_widget_body">
		<?php usces_list_post(__('Uncategorized'),3); ?>
		</ul>
	</li>
	<li id="welcart_calendar-3" class="widget widget_welcart_calendar">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/calendar.png" alt="<?php _e('Business Calendar','usces') ?>" /><?php _e('Business Calendar','usces') ?></div>
		<ul class="welcart_calendar_body welcart_widget_body"><li>
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
	<li id="welcart_login-3" class="widget widget_welcart_login">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/login.png" alt="<?php _e('Log-in','usces') ?>" /><?php _e('Log-in','usces') ?></div>
		<ul class="welcart_login_body welcart_widget_body"><li>
			<div class="loginbox">
			<?php if ( ! usces_is_login() ) { ?>
				<form name="loginwidget" id="loginform" action="<?php echo USCES_MEMBER_URL; ?>" method="post">
				<p>
				<label><?php _e('e-mail adress','usces') ?><br />
				<input type="text" name="loginmail" id="loginmail" class="loginmail" value="<?php echo usces_remembername('return'); ?>" size="20" tabindex="10" /></label><br />
				<label><?php _e('password','usces') ?><br />
				<input type="password" name="loginpass" id="loginpass" class="loginpass" value="<?php echo usces_rememberpass('return'); ?>" size="20" tabindex="20" /></label><br />
				<label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90"<?php echo usces_remembercheck('return'); ?> /> <?php _e('memorize login information','usces') ?></label></p>
				<p class="submit">
				<input type="submit" name="member_login" id="member_login" value="<?php _e('Log-in','usces') ?>" tabindex="100" />
				</p>
				</form>
				<a href="<?php echo USCES_MEMBER_URL; ?>&page=lostmemberpassword" title="<?php _e('Pssword Lost and Found','usces') ?>"><?php _e('Did you forget your password?','usces') ?></a><br />
				<a href="<?php echo USCES_MEMBER_URL; ?>&page=newmember" title="<?php _e('New enrollment for membership.','usces') ?>"><?php _e('New enrollment for membership.','usces') ?></a>
			<?php }else{ ?>
				<?php printf(__('Mr/Mrs %s', 'usces'), usces_the_member_name()); ?><br />
				<?php echo usces_loginout(); ?><br />
				<a href="<?php echo USCES_MEMBER_URL; ?>"><?php _e('Membership information','usces') ?></a>
			<?php } ?>
			</div>		
		</li>
		</ul>
	</li>
	<li id="welcart_featured-3" class="widget widget_welcart_featured">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/osusume.png" alt="<?php _e('Items recommended','usces') ?>" /><?php _e('Items recommended','usces') ?></div>
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
	<li id="welcart_bestseller-3" class="widget widget_welcart_bestseller">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/bestseller.png" alt="<?php _e('best seller','usces') ?>" width="24" height="24" /><?php _e('best seller','usces') ?></div>
		<?php usces_list_bestseller(10); ?>
	</li>
	<li id="welcart_page-2" class="widget widget_welcart_page">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/page.png" alt="<?php _e('page','usces') ?>" /><?php _e('page','usces') ?></div>					  
		<ul class="ucart_widget_body"> 
		<?php wp_list_pages('title_li=') ; ?>
		</ul> 
	
	</li>
<?php endif; ?>
</ul>

</div>
<!-- end right sidebar -->
