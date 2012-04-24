<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<title><?php
		/*
		 * Print the <title> tag based on what is being viewed.
		 */
		global $page, $paged;
	
		wp_title( '|', true, 'right' );
	
		// Add the blog name.
		bloginfo( 'name' );
	
		// Add the blog description for the home/front page.
		$site_description = get_option( 'blogdescription' );
		if ( $site_description && ( is_home() || is_front_page() ) )
			echo " | $site_description";
	
		// Add a page number if necessary:
		if ( $paged >= 2 || $page >= 2 )
			echo ' | ' . sprintf( __( 'Page %s', 'twentyten' ), max( $paged, $page ) );
	
		?></title>

	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<?php wp_get_archives('type=monthly&format=link'); ?>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="wrap">
<div id="header">
	<?php $heading_tag = ( is_home() || is_front_page() ) ? 'h1' : 'div'; ?>
	<<?php echo $heading_tag; ?> id="site-title"><a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></<?php echo $heading_tag; ?>>
	<p class="discprition"><?php bloginfo('description'); ?></p>
	
	<?php if(function_exists('wp_nav_menu')): ?>
		<?php wp_nav_menu(array('menu_class' => 'mainnavi clearfix', 'theme_location' => 'header')); ?>
	<?php else: ?>
		<ul class="mainnavi clearfix">
			<li><a href="<?php echo home_url( '/' ); ?>/"><?php _e('top page','usces') ?></a></li>
			<?php wp_list_pages('title_li=&exclude=' . USCES_MEMBER_NUMBER . ',' . USCES_CART_NUMBER ); ?>
		</ul>
	<?php endif; ?>
	
	<?php if(usces_is_membersystem_state() || usces_is_cart()): ?>
	<ul class="subnavi clearfix">
		<?php if(usces_is_membersystem_state()): ?>
		<li><?php if(usces_is_login()){printf(__('Mr/Mrs %s', 'usces'), usces_the_member_name('return'));}else{echo 'guest';} ?></li>
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
<!-- end header -->