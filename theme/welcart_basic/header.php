<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Pop Cloud Blue Theme
 */
 global $usces;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'uscestheme' ), max( $paged, $page ) );

	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php
	/* We add some JavaScript to pages with the comment form
	 * to support sites with threaded comments (when in use).
	 */
	if ( is_singular() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	/* Always have wp_head() just before the closing </head>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to add elements to <head> such
	 * as styles, scripts, and meta tags.
	 */
	wp_head();
?>
</head>

<body>
<div id="wrapping">

<div id="header">
	<div class="clearfix">
		<div class="siteurl">
			<?php $heading_tag = ( is_home() || is_front_page() ) ? 'h1' : 'div'; ?><<?php echo $heading_tag; ?> id="site-title" class="hometitle"><a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></<?php echo $heading_tag; ?>>
                <?php $heading_tag = ( is_home() || is_front_page() ) ? 'h2' : 'div'; ?><<?php echo $heading_tag; ?> id="site-description"><?php bloginfo( 'description' ); ?></<?php echo $heading_tag; ?>>
		</div>
		<div class="head_search">
			<form name="searchform" id="searchform" method="get" action="<?php bloginfo('home'); ?>"> 
			<input type="text" name="s" id="s" value="" /> 
			<input type="image" src="<?php bloginfo('template_url'); ?>/images/search_btn.gif" alt="<?php _e('Search','usces') ?>" name="searchsubmit" id="searchsubmit" /> 
			<a href="<?php echo USCES_CART_URL.$usces->delim; ?>page=search_item" title="<?php _e('An article category keyword search','usces') ?>" class="composition"></a>
			</form>		
		</div>
	</div>
	
	<?php
		if ( is_singular() &&
				has_post_thumbnail( $post->ID ) &&
				( /* $src, $width, $height */ $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'post-thumbnail' ) ) &&
				$image[1] >= HEADER_IMAGE_WIDTH ) :
			// Houston, we have a new header image!
			echo '<div class="header_image">' . get_the_post_thumbnail( $post->ID, 'post-thumbnail' ) . '</div>';
		else : ?>
			<div class="header_image"><img src="<?php header_image(); ?>" width="<?php echo HEADER_IMAGE_WIDTH; ?>" height="<?php echo HEADER_IMAGE_HEIGHT; ?>" alt="" /></div>
	<?php endif; ?>

	<?php if(function_exists('wp_nav_menu')): ?>
		<?php wp_nav_menu(array('menu_class' => 'mainnavi clearfix', 'theme_location' => 'header')); ?>
	<?php else: ?>
		<ul class="mainnavi clearfix">
			<li><a href="<?php bloginfo('url'); ?>/"><?php _e('top page','usces') ?></a></li>
			<?php wp_list_pages('title_li=&exclude=' . USCES_MEMBER_NUMBER . ',' . USCES_CART_NUMBER ); ?>
		</ul>
	<?php endif; ?>
	
</div><!-- end of header -->

<div id="main" class="clearfix">
<!-- end header -->
