<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Pop Cloud Blue Theme
 */
?>
<!-- begin sidebar -->
<div id="sidebar" class="sidebar">

<ul id="sidebar-in">
<?php if ( ! dynamic_sidebar( 'primary-widget-area' ) ) : ?>

	<!-- widget_welcart_category -->
	<li id="welcart_category-3" class="widget widget_welcart_category">
		<div class="widget_title"><?php _e('Item Category','usces') ?></div>
		<ul class="welcart_widget_body">
		<?php $cats = get_category_by_slug('itemgenre'); ?>
		<?php wp_list_categories('orderby=id&use_desc_for_title=0&child_of='.$cats->term_id.'&title_li='); ?>
 		</ul>
	</li>
	
	<!-- widget_welcart_login -->
	<?php if(usces_is_membersystem_state() || usces_is_cart()): ?>
	<li id="welcart_login-3" class="widget widget_welcart_login">
		<div class="widget_title"><?php _e('Log-in','usces') ?></div>
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
	<?php endif; ?>

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
	
	<!-- welcart_calendar -->
	<li id="welcart_calendar-3" class="widget widget_welcart_calendar">
		<div class="widget_title"><?php _e('Business Calendar','usces') ?></div>
		<ul class="welcart_calendar_body welcart_widget_body"><li>
		<?php usces_the_calendar(); ?>
		</li></ul>
	</li>
<?php endif; ?>
</ul>

</div>
<!-- end sidebar -->
