<?php
/*
Plugin Name: Welcart e-Commerce
Plugin URI: http://www.welcart.com/
Description: Welcart builds the management system with a net shop on Wordpress.
Version: 1.0-future.r1103071
Author: USconsort
Author URI: http://www.uscons.co.jp/
*/
//$wpdb->show_errors();
define('USCES_VERSION', '1.0.r1103071');
define('USCES_DB_ACCESS', '1.4');
define('USCES_DB_MEMBER', '1.1');
define('USCES_DB_MEMBER_META', '1.1');
define('USCES_DB_ORDER', '1.9');
define('USCES_DB_ORDER_META', '1.2');

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

require_once(USCES_PLUGIN_DIR."/functions/included_first.php");
add_filter( 'locale', 'usces_filter_locale' );
load_plugin_textdomain('usces', USCES_PLUGIN_DIR.'/languages', USCES_PLUGIN_FOLDER.'/languages');

require_once(USCES_PLUGIN_DIR."/functions/redirect.php");
require_once(USCES_PLUGIN_DIR."/includes/initial.php");
require_once(USCES_PLUGIN_DIR."/functions/calendar-com.php");
require_once(USCES_PLUGIN_DIR."/functions/utility.php");
require_once(USCES_PLUGIN_DIR."/functions/item_post.php");
require_once(USCES_PLUGIN_DIR."/functions/function.php");
require_once(USCES_PLUGIN_DIR."/functions/shortcode.php");
require_once(USCES_PLUGIN_DIR."/classes/usceshop.class.php");
require_once(USCES_PLUGIN_DIR."/functions/hoock_func.php");

global $usces;
$usces = new usc_e_shop();
$usces->regist_action();

require_once(USCES_PLUGIN_DIR."/functions/template_func.php");

register_activation_hook( __FILE__, array(&$usces, 'set_initial') );
//add_action('activate_' . plugin_basename(__FILE__), array(&$usces, 'set_initial'));

require_once(USCES_PLUGIN_DIR."/includes/default_filters.php");

?>
