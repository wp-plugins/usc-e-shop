<?php
/**
 * @package WordPress
 * @subpackage uCart Default Theme
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
?>
