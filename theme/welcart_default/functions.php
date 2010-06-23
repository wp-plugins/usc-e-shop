<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
if ( function_exists('register_sidebar') ) {
	register_sidebar(array(
		'name' => 'leftbar',
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<div class="widget_title">',
		'after_title' => '</div>',
	));
	register_sidebar(array(
		'name' => 'rightbar',
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<div class="widget_title">',
		'after_title' => '</div>',
	));
}

add_action( 'after_setup_theme', 'welcart_setup' );
if ( ! function_exists( 'welcart_setup' ) ):
function welcart_setup() {
	
	// wp_nav_menu() 
	register_nav_menus( array(
		'header' => __('Header Navigation', 'usces' ),
		'footer' => __('Footer Navigation', 'usces' ),
	) );
}
endif;

function welcart_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'welcart_page_menu_args' );
?>
