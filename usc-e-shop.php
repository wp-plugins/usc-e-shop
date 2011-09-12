<?php
/*
Plugin Name: Welcart e-Commerce
Plugin URI: http://www.welcart.com/
Description: Welcart builds the management system with a net shop on Wordpress.
Version: 1.2-beta.r1107081
Author: USconsort
Author URI: http://www.uscons.co.jp/
*/
define('USCES_VERSION', '1.2-beta.r1107081');
define('USCES_DB_ACCESS', '1.4');
define('USCES_DB_MEMBER', '1.1');
define('USCES_DB_MEMBER_META', '1.1');
define('USCES_DB_ORDER', '1.9');
define('USCES_DB_ORDER_META', '1.2');

define('USCES_WP_CONTENT_DIR', WP_CONTENT_DIR);
define('USCES_WP_CONTENT_URL', WP_CONTENT_URL);
define('USCES_WP_PLUGIN_DIR', WP_PLUGIN_DIR);
define('USCES_WP_PLUGIN_URL', WP_PLUGIN_URL);

define('USCES_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)));
define('USCES_PLUGIN_URL', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)));
define('USCES_PLUGIN_FOLDER', dirname(plugin_basename(__FILE__)));
define('USCES_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('USCES_CART_FOLDER', 'usces-cart');
define('USCES_MEMBER_FOLDER', 'usces-member');
define('USCES_ADMIN_SSL_BASE_NAME', 'admin-ssl.php');
define('USCES_ADMIN_URL', get_option('siteurl') . '/wp-admin/admin.php');

global $usces_settings, $usces_states;
require_once(USCES_PLUGIN_DIR."/functions/included_first.php");
add_filter( 'locale', 'usces_filter_locale' );
load_plugin_textdomain('usces', USCES_PLUGIN_DIR.'/languages', USCES_PLUGIN_FOLDER.'/languages');

require_once(USCES_PLUGIN_DIR."/functions/redirect.php");
require_once(USCES_PLUGIN_DIR."/includes/initial.php");
require_once(USCES_PLUGIN_DIR.'/functions/define_function.php');
require_once(USCES_PLUGIN_DIR."/functions/calendar-com.php");
require_once(USCES_PLUGIN_DIR."/functions/utility.php");
require_once(USCES_PLUGIN_DIR."/functions/item_post.php");
require_once(USCES_PLUGIN_DIR."/functions/function.php");
require_once(USCES_PLUGIN_DIR."/functions/shortcode.php");
require_once(USCES_PLUGIN_DIR."/classes/usceshop.class.php");
require_once(USCES_PLUGIN_DIR."/functions/hoock_func.php");
require_once(USCES_PLUGIN_DIR."/classes/httpRequest.class.php");
require_once(USCES_PLUGIN_DIR."/functions/admin_func.php");
require_once(USCES_PLUGIN_DIR."/includes/default_widgets.php");
require_once(USCES_PLUGIN_DIR."/functions/new_function.php");

global $usces;
$usces = new usc_e_shop();
$usces->regist_action();

require_once(USCES_PLUGIN_DIR."/functions/template_func.php");

register_activation_hook( __FILE__, array(&$usces, 'set_initial') );
//add_action('activate_' . plugin_basename(__FILE__), array(&$usces, 'set_initial'));

require_once(USCES_PLUGIN_DIR."/includes/default_filters.php");

?>
