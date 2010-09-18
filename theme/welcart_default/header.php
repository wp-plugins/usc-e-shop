<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>

	<style type="text/css" media="screen">
		@import url( <?php bloginfo('stylesheet_url'); ?> );
	</style>

	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
	<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php bloginfo('atom_url'); ?>" />

	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php wp_get_archives('type=monthly&format=link'); ?>
	<?php wp_head(); ?>
</head>

<body>
<div id="wrap">
<div id="header">
	<h1><a href="<?php bloginfo('url'); ?>/"><?php bloginfo('name'); ?></a></h1>
	<p class="discprition"><?php bloginfo('description'); ?></p>
	
	<?php if(function_exists('wp_nav_menu')): ?>
		<?php wp_nav_menu(array('menu_class' => 'mainnavi clearfix', 'theme_location' => 'header')); ?>
	<?php else: ?>
		<ul class="mainnavi clearfix">
			<li><a href="<?php bloginfo('url'); ?>/"><?php _e('top page','usces') ?></a></li>
			<?php wp_list_pages('title_li=&exclude=' . USCES_MEMBER_NUMBER . ',' . USCES_CART_NUMBER ); ?>
		</ul>
	<?php endif; ?>
	
	<?php if(usces_is_membersystem_state() || usces_is_cart()): ?>
	<ul class="subnavi clearfix">
		<?php if(usces_is_membersystem_state()): ?>
		<li><?php if(usces_is_login()){printf(__('Mr/Mrs %s', 'usces'), usces_the_member_name());}else{echo 'guest';} ?></li>
		<li><?php usces_loginout(); ?></li>
		<?php if(usces_is_login()): ?>
		<li><a href="<?php echo USCES_MEMBER_URL; ?>"><?php _e('Membership information','usces') ?></a></li>
		<?php endif; ?>
		<?php endif; ?>
		<?php if(usces_is_cart()): ?>
		<li><a href="<?php echo USCES_CART_URL; ?>"><?php _e('Cart','usces') ?></a></li>
		<li><a href="<?php echo USCES_CUSTOMER_URL; ?>"><?php _e('Proceed to checkout','usces') ?></a></li>
		<?php endif; ?>
	</ul>
	<?php endif; ?>
</div><!-- end of header -->

<div id="main" class="clearfix">
<?php get_sidebar(); ?>
<!-- end header -->
