<?php
add_action( 'init', array(&$usces, 'main'), 10);
add_action( 'admin_init', 'usces_redirect', 10);
add_action( 'admin_menu', array(&$usces, 'add_pages'));
add_action( 'admin_head', array(&$usces, 'admin_head'));
add_action( 'wp_head', array(&$usces, 'shop_head'));
add_action( 'wp_footer', array(&$usces, 'shop_foot'));
add_action( 'wp_footer', array(&$usces, 'lastprocessing'));
//add_action('wp_dashboard_setup', 'usces_dashboard_setup' );	
//add_action( 'login_head', 'usces_admin_login_head' );
//add_action('restrict_manage_posts', array(&$usces, 'postfilter'));

add_action('save_post', 'item_save_metadata');
add_action( 'wp_ajax_order_item2cart_ajax', 'order_item2cart_ajax' );
add_action( 'wp_ajax_order_item_ajax', 'order_item_ajax' );
add_action( 'wp_ajax_payment_ajax', 'payment_ajax' );
add_action( 'wp_ajax_common_option_ajax', 'common_option_ajax' );
add_action( 'wp_ajax_item_option_ajax', 'item_option_ajax' );
add_action( 'wp_ajax_item_sku_ajax', 'item_sku_ajax' );
add_action( 'wp_ajax_shop_options_ajax', 'shop_options_ajax' );
add_action( 'wp_ajax_setup_cod_ajax', 'usces_setup_cod_ajax' );
add_action( 'wp_ajax_change_states_ajax', 'change_states_ajax' );
add_action( 'wp_ajax_change_sku_option_ajax', 'change_sku_option_ajax' );
add_action( 'wp_ajax_getinfo_ajax', 'usces_getinfo_ajax' );
//20100809ysk start
add_action( 'wp_ajax_custom_field_ajax', 'custom_field_ajax' );
//20100809ysk end
//20110331ysk start
add_action( 'wp_ajax_target_market_ajax', 'target_market_ajax' );
//20110331ysk end

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

add_shortcode('direct_intoCart', 'sc_direct_intoCart');


if (version_compare($wp_version, '2.8', '>=')){
	require_once(USCES_PLUGIN_DIR."/widgets/usces_category.php");
	require_once(USCES_PLUGIN_DIR."/widgets/usces_bestseller.php");
	require_once(USCES_PLUGIN_DIR."/widgets/usces_calendar.php");
	require_once(USCES_PLUGIN_DIR."/widgets/usces_search.php");
	require_once(USCES_PLUGIN_DIR."/widgets/usces_featured.php");
	require_once(USCES_PLUGIN_DIR."/widgets/usces_page.php");
	require_once(USCES_PLUGIN_DIR."/widgets/usces_post.php");
	require_once(USCES_PLUGIN_DIR."/widgets/usces_login.php");
	require_once(USCES_PLUGIN_DIR."/widgets/usces_blog_calendar.php");
	require_once(USCES_PLUGIN_DIR."/widgets/usces_recent_posts.php");
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_category");'));
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_bestseller");'));
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_calendar");'));
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_search");'));
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_featured");'));
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_page");'));
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_post");'));
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_login");'));
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_Blog_Calendar");'));
	add_action('widgets_init', create_function('', 'return register_widget("Welcart_Recent_Posts");'));
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

add_action('usces_action_cart_page_header', array(&$usces, 'action_cart_page_header'));
add_action('usces_action_cart_page_footer', array(&$usces, 'action_cart_page_footer'));
add_action('usces_action_customer_page_header', array(&$usces, 'action_customer_page_header'));
add_action('usces_action_customer_page_footer', array(&$usces, 'action_customer_page_footer'));
add_action('usces_action_delivery_page_header', array(&$usces, 'action_delivery_page_header'));
add_action('usces_action_delivery_page_footer', array(&$usces, 'action_delivery_page_footer'));
add_action('usces_action_confirm_page_header', array(&$usces, 'action_confirm_page_header'));
add_action('usces_action_confirm_page_footer', array(&$usces, 'action_confirm_page_footer'));
add_action('usces_action_cartcompletion_page_header', array(&$usces, 'action_cartcompletion_page_header'));
add_action('usces_action_cartcompletion_page_footer', array(&$usces, 'action_cartcompletion_page_footer'));
add_action('usces_action_login_page_header', array(&$usces, 'action_login_page_header'));
add_action('usces_action_login_page_footer', array(&$usces, 'action_login_page_footer'));
add_action('usces_action_newmember_page_header', array(&$usces, 'action_newmember_page_header'));
add_action('usces_action_newmember_page_footer', array(&$usces, 'action_newmember_page_footer'));
add_action('usces_action_newpass_page_header', array(&$usces, 'action_newpass_page_header'));
add_action('usces_action_newpass_page_footer', array(&$usces, 'action_newpass_page_footer'));
add_action('usces_action_changepass_page_header', array(&$usces, 'action_changepass_page_header'));
add_action('usces_action_changepass_page_footer', array(&$usces, 'action_changepass_page_footer'));
add_action('usces_action_memberinfo_page_header', array(&$usces, 'action_memberinfo_page_header'));
add_action('usces_action_memberinfo_page_footer', array(&$usces, 'action_memberinfo_page_footer'));
add_action('usces_action_membercompletion_page_header', array(&$usces, 'action_membercompletion_page_header'));
add_action('usces_action_membercompletion_page_footer', array(&$usces, 'action_membercompletion_page_footer'));

add_action('usces_main', 'usces_define_functions', 10);


if( $usces->options['itemimg_anchor_rel'] )
	add_filter('usces_itemimg_anchor_rel', array(&$usces, 'filter_itemimg_anchor_rel'));
	
add_action('pre_get_posts', array(&$usces, 'filter_divide_item'));
add_action('usces_post_reg_orderdata', 'usces_post_reg_orderdata', 10, 2);



add_filter('usces_filter_delivery_check', 'usces_filter_delivery_secure_check', 9);


//20100818ysk start
add_filter('usces_filter_customer_check', 'usces_filter_customer_check_custom_customer', 10);
add_filter('usces_filter_delivery_check', 'usces_filter_delivery_check_custom_delivery', 10);
//20100818ysk end
add_filter('usces_filter_delivery_check', 'usces_filter_delivery_secure_check', 9);
//20100809ysk start
add_filter('usces_filter_delivery_check', 'usces_filter_delivery_check_custom_order', 10);
//20100809ysk end
//20100818ysk start
add_filter('usces_filter_member_check', 'usces_filter_member_check_custom_member', 10);
add_filter('usces_filter_member_check_fromcart', 'usces_filter_customer_check_custom_customer', 10);
//20100818ysk end



?>
