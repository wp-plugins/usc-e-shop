<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
if(!defined('USCES_VERSION')) return;

/***********************************************************
* welcart_setup
***********************************************************/
add_action( 'after_setup_theme', 'welcart_setup' );
if ( ! function_exists( 'welcart_setup' ) ):
function welcart_setup() {
	
	register_nav_menus( array(
		'header' => __('Header Navigation', 'usces' ),
		'footer' => __('Footer Navigation', 'usces' ),
	) );
}
endif;

/***********************************************************
* welcart_page_menu_args
***********************************************************/
function welcart_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'welcart_page_menu_args' );

/***********************************************************
* sidebar
***********************************************************/
if ( function_exists('register_sidebar') ) {
	// Area 1, leftbar.
	register_sidebar(array(
		'name' => __( 'Left Sidebar Widget Area', 'uscestheme' ),
		'id' => 'leftsidebar-widget-area',
		'description' => __( 'left sidebar widget area', 'uscestheme' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<div class="widget_title">',
		'after_title' => '</div>',
	));
	// Area 2, rightbar.
	register_sidebar(array(
		'name' => __( 'Right Sidebar Widget Area', 'uscestheme' ),
		'id' => 'rightsidebar-widget-area',
		'description' => __( 'right sidebar widget area', 'uscestheme' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<div class="widget_title">',
		'after_title' => '</div>',
	));
	// Area 3, sidebar(cart or member).
	register_sidebar(array(
		'name' => __( 'Sidebar Widget Area(Cart or Member)', 'uscestheme' ),
		'id' => 'leftsidebar-onlycart-widget-area',
		'description' => __( 'sidebar widget area(cart or member)', 'uscestheme' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<div class="widget_title">',
		'after_title' => '</div>',
	));
}

/***********************************************************
* widget
***********************************************************/
add_filter('widget_categories_dropdown_args', 'welcart_categories_dropdown_args');
function welcart_categories_dropdown_args( $args ){
	global $usces;
	$ids = $usces->get_item_cat_ids();
	$ids[] = USCES_ITEM_CAT_PARENT_ID;
	$args['exclude'] = $ids;
	return $args;
}
add_filter('getarchives_where', 'welcart_getarchives_where');
function welcart_getarchives_where( $r ){
	$where = "WHERE post_type = 'post' AND post_status = 'publish' AND post_mime_type <> 'item' ";
	return $where;
}
add_filter('widget_tag_cloud_args', 'welcart_tag_cloud_args');
function welcart_tag_cloud_args( $args ){
	global $usces;
	if( 'category' == $args['taxonomy']){
		$ids = $usces->get_item_cat_ids();
		$ids[] = USCES_ITEM_CAT_PARENT_ID;
		$args['exclude'] = $ids;
	}else if( 'post_tag' == $args['taxonomy']){
		$ids = $usces->get_item_post_ids();
		$tobs = wp_get_object_terms($ids, 'post_tag');
		foreach( $tobs as $ob ){
			$tids[] = $ob->term_id;
		}
		$args['exclude'] = $tids;
	}
	return $args;
}

/***********************************************************
* excerpt
***********************************************************/
function welcart_assistance_excerpt_length( $length ) {
	return 10;
}
function welcart_assistance_excerpt_mblength( $length ) {
	return 40;
}

function welcart_excerpt_length( $length ) {
	return 40;
}
add_filter( 'excerpt_length', 'welcart_excerpt_length' );

function welcart_excerpt_bmlength( $length ) {
	return 110;
}
add_filter( 'excerpt_mblength', 'welcart_excerpt_bmlength' );

function welcart_continue_reading_link() {
	return ' <a href="'. get_permalink() . '">' . __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'uscestheme' ) . '</a>';
}

function welcart_auto_excerpt_more( $more ) {
	return ' &hellip;' . welcart_continue_reading_link();
}
add_filter( 'excerpt_more', 'welcart_auto_excerpt_more' );

function welcart_custom_excerpt_more( $output ) {
	if ( has_excerpt() && ! is_attachment() ) {
		$output .= welcart_continue_reading_link();
	}
	return $output;
}
add_filter( 'get_the_excerpt', 'welcart_custom_excerpt_more' );

/***********************************************************
* SSL
***********************************************************/
if( $usces->options['use_ssl'] ){
	add_action('init', 'usces_ob_start');
	function usces_ob_start(){
		global $usces;
		if( $usces->use_ssl && ($usces->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $usces->is_inquiry_page($_SERVER['REQUEST_URI'])) )
			ob_start('usces_ob_callback');
	}
	function usces_ob_callback($buffer){
		global $usces;
		$pattern = array(
			'|(<[^<]*)href=\"'.get_option('siteurl').'([^>]*)\.css([^>]*>)|', 
			'|(<[^<]*)src=\"'.get_option('siteurl').'([^>]*>)|'
		);
		$replacement = array(
			'${1}href="'.USCES_SSL_URL_ADMIN.'${2}.css${3}', 
			'${1}src="'.USCES_SSL_URL_ADMIN.'${2}'
		);
		$buffer = preg_replace($pattern, $replacement, $buffer);
		return $buffer;
	}
}

?>
