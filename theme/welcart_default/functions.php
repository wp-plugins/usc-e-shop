<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
if(!defined('USCES_VERSION')) return;

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
?>
