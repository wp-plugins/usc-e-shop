<?php
/*
Plugin Name: Welcart e-Commerce
Plugin URI: http://www.welcart.com/
Description: Welcart builds the management system with a net shop on Wordpress.
Version: 0.4.4
Author: USconsort
Author URI: http://www.uscons.co.jp/
*/

define('USCES_VERSION', '0.4.4');
define('USCES_DB_ACCESS', '1.1');
define('USCES_DB_MEMBER', '1.1');
define('USCES_DB_ORDER', '1.7');
define('USCES_DB_ORDER_META', '1.1');

define('USCES_WP_CONTENT_DIR', ABSPATH . 'wp-content');
define('USCES_WP_CONTENT_URL', get_option('siteurl') . '/wp-content');

define('USCES_WP_PLUGIN_DIR', USCES_WP_CONTENT_DIR . '/plugins');
define('USCES_WP_PLUGIN_URL', USCES_WP_CONTENT_URL . '/plugins');

define('USCES_PLUGIN_DIR', USCES_WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)));
define('USCES_PLUGIN_URL', USCES_WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)));
define('USCES_PLUGIN_FOLDER', dirname(plugin_basename(__FILE__)));
define('USCES_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('USCES_CART_FOLDER', 'usces-cart');
define('USCES_MEMBER_FOLDER', 'usces-member');
define('USCES_ADMIN_SSL_BASE_NAME', 'admin-ssl.php');
define('USCES_ADMIN_URL', get_option('siteurl') . '/wp-admin/admin.php');
	
load_plugin_textdomain('usces', USCES_PLUGIN_DIR.'/languages', USCES_PLUGIN_FOLDER.'/languages');

require_once(USCES_PLUGIN_DIR."/includes/initial.php");
require_once(USCES_PLUGIN_DIR."/functions/calendar-com.php");
require_once(USCES_PLUGIN_DIR."/functions/item_post.php");
require_once(USCES_PLUGIN_DIR."/functions/function.php");
require_once(USCES_PLUGIN_DIR."/classes/usceshop.class.php");


$usces = new usc_e_shop();
$usces->regist_action();

require_once(USCES_PLUGIN_DIR."/functions/template_func.php");

add_action('activate_' . plugin_basename(__FILE__), array(&$usces, 'set_initial'));
add_action('init', array(&$usces, 'main'), 10);
add_action('admin_menu', array(&$usces, 'add_pages'));
add_action('admin_head', array(&$usces, 'admin_head'));
add_action('wp_head', array(&$usces, 'shop_head'));
add_action('wp_footer', array(&$usces, 'lastprocessing'));
//add_action('restrict_manage_posts', array(&$usces, 'postfilter'));

add_action('save_post', 'item_save_metadata');
add_action( 'wp_ajax_order_item2cart_ajax', 'order_item2cart_ajax' );
add_action( 'wp_ajax_order_item_ajax', 'order_item_ajax' );
add_action( 'wp_ajax_payment_ajax', 'payment_ajax' );
add_action( 'wp_ajax_item_option_ajax', 'item_option_ajax' );
add_action( 'wp_ajax_item_sku_ajax', 'item_sku_ajax' );
add_action( 'wp_ajax_shop_options_ajax', 'shop_options_ajax' );

//add_action('template_redirect', array(&$usces, 'maintenance_mode'));
add_shortcode('company_name', array(&$usces, 'sc_company_name'));
add_shortcode('zip_code', array(&$usces, 'sc_zip_code'));
add_shortcode('address1', array(&$usces, 'sc_address1'));
add_shortcode('address2', array(&$usces, 'sc_address2'));
add_shortcode('tel_number', array(&$usces, 'sc_tel_number'));
add_shortcode('fax_number', array(&$usces, 'sc_fax_number'));
add_shortcode('inquiry_mail', array(&$usces, 'sc_inquiry_mail'));
add_shortcode('payment', array(&$usces, 'sc_payment'));
add_shortcode('payment_title', array(&$usces, 'sc_payment_title'));
add_shortcode('cod_fee', array(&$usces, 'sc_cod_fee'));
add_shortcode('start_point', array(&$usces, 'sc_start_point'));
add_shortcode('postage_privilege', array(&$usces, 'sc_postage_privilege'));
add_shortcode('shipping_charge', array(&$usces, 'sc_shipping_charge'));
add_shortcode('site_url', array(&$usces, 'sc_site_url'));
add_shortcode('button_to_cart', array(&$usces, 'sc_button_to_cart'));

if (version_compare($wp_version, '2.8', '>=')){
	require_once(USCES_PLUGIN_DIR."/widgets/usces_category.php");
	require_once(USCES_PLUGIN_DIR."/widgets/usces_bestseller.php");
	require_once(USCES_PLUGIN_DIR."/widgets/usces_calendar.php");
	require_once(USCES_PLUGIN_DIR."/widgets/usces_search.php");
	require_once(USCES_PLUGIN_DIR."/widgets/usces_featured.php");
	require_once(USCES_PLUGIN_DIR."/widgets/usces_page.php");
	require_once(USCES_PLUGIN_DIR."/widgets/usces_post.php");
	require_once(USCES_PLUGIN_DIR."/widgets/usces_login.php");
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_category");'));
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_bestseller");'));
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_calendar");'));
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_search");'));
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_featured");'));
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_page");'));
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_post");'));
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_login");'));
}

add_filter('usces_filter_cart_page_header', array(&$usces, 'filter_cart_page_header'));
add_filter('usces_filter_cart_page_footer', array(&$usces, 'filter_cart_page_footer'));
add_filter('usces_filter_customer_page_header', array(&$usces, 'filter_customer_page_header'));
add_filter('usces_filter_customer_page_footer', array(&$usces, 'filter_customer_page_footer'));
add_filter('usces_filter_delivery_page_header', array(&$usces, 'filter_delivery_page_header'));
add_filter('usces_filter_delivery_page_footer', array(&$usces, 'filter_delivery_page_footer'));
add_filter('usces_filter_confirm_page_header', array(&$usces, 'filter_confirm_page_header'));
add_filter('usces_filter_confirm_page_footer', array(&$usces, 'filter_confirm_page_footer'));
add_filter('usces_filter_cartcompletion_page_header', array(&$usces, 'filter_cartcompletion_page_header'));
add_filter('usces_filter_cartcompletion_page_footer', array(&$usces, 'filter_cartcompletion_page_footer'));
add_filter('usces_filter_login_page_header', array(&$usces, 'filter_login_page_header'));
add_filter('usces_filter_login_page_footer', array(&$usces, 'filter_login_page_footer'));
add_filter('usces_filter_newmember_page_header', array(&$usces, 'filter_newmember_page_header'));
add_filter('usces_filter_newmember_page_footer', array(&$usces, 'filter_newmember_page_footer'));
add_filter('usces_filter_newpass_page_header', array(&$usces, 'filter_newpass_page_header'));
add_filter('usces_filter_newpass_page_footer', array(&$usces, 'filter_newpass_page_footer'));
add_filter('usces_filter_changepass_page_header', array(&$usces, 'filter_changepass_page_header'));
add_filter('usces_filter_changepass_page_footer', array(&$usces, 'filter_changepass_page_footer'));
add_filter('usces_filter_memberinfo_page_header', array(&$usces, 'filter_memberinfo_page_header'));
add_filter('usces_filter_memberinfo_page_footer', array(&$usces, 'filter_memberinfo_page_footer'));
add_filter('usces_filter_membercompletion_page_header', array(&$usces, 'filter_membercompletion_page_header'));
add_filter('usces_filter_membercompletion_page_footer', array(&$usces, 'filter_membercompletion_page_footer'));
add_filter('the_content', array(&$usces, 'filter_itemPage'));
//add_filter('post_link', array(&$usces, 'filter_permalink'));
//add_filter('page_link', array(&$usces, 'filter_permalink'));
if( $usces->options['itemimg_anchor_rel'] )
	add_filter('usces_itemimg_anchor_rel', array(&$usces, 'filter_itemimg_anchor_rel'));
	
add_action('pre_get_posts', array(&$usces, 'filter_divide_item'));

?>
