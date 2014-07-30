<?php
global $wp_version;

add_action( 'init', array(&$usces, 'main'), 10);
add_action( 'admin_init', 'usces_redirect', 10);
add_action( 'admin_init', 'usces_typenow');
add_action( 'admin_notices', 'usces_admin_notices');

//add_action( 'admin_init', 'admin_prodauct_meta_box');
add_action( 'admin_menu', array(&$usces, 'add_pages'));
add_action( 'admin_head', array(&$usces, 'admin_head'));
add_action( 'admin_head-welcart-shop_page_usces_itemnew', 'admin_new_prodauct_header');
add_action( 'admin_head-welcart-shop_page_usces_itemedit', 'admin_prodauct_header');
add_action( 'current_screen', 'admin_prodauct_current_screen' );
add_action( 'wp_head', array(&$usces, 'shop_head'));
add_action( 'wp_head', 'usces_action_ogp_meta');
add_action( 'wp_footer', array(&$usces, 'shop_foot'));
add_action( 'wp_footer', array(&$usces, 'lastprocessing'));
add_action( 'wp_footer', 'usces_action_footer_comment');
add_action( 'admin_footer-welcart-shop_page_usces_itemnew', 'admin_prodauct_footer');
add_action( 'admin_footer-welcart-shop_page_usces_itemedit', 'admin_prodauct_footer');
add_action( 'admin_footer-welcart-shop_page_usces_initial', 'admin_prodauct_footer');
add_action( 'admin_footer-welcart-shop_page_usces_cart', 'admin_prodauct_footer');
add_action( 'admin_footer-post.php', 'admin_post_footer');
add_action( 'admin_footer-post-new.php', 'admin_post_footer');
add_action( 'wp_before_admin_bar_render', 'usces_itempage_admin_bar' );


//add_action( 'admin_head', 'wc_mkdir');


//add_action( 'transition_post_status', 'usces_action_transition_post_status', 10, 3);
//add_filter( 'redirect_post_location', 'usces_filter_redirect_post_location', 10, 2);
//add_action( 'dbx_post_advanced', 'usces_action_updated_messages');
//add_action('wp_dashboard_setup', 'usces_dashboard_setup' );	
//add_action( 'login_head', 'usces_admin_login_head' );
//add_action('restrict_manage_posts', array(&$usces, 'postfilter'));

add_action('save_post', 'item_save_metadata', 10, 2);
add_action( 'wp_ajax_order_item2cart_ajax', 'order_item2cart_ajax' );
add_action( 'wp_ajax_order_item_ajax', 'order_item_ajax' );
add_action( 'wp_ajax_payment_ajax', 'payment_ajax' );
add_action( 'wp_ajax_item_option_ajax', 'item_option_ajax' );
add_action( 'wp_ajax_item_sku_ajax', 'item_sku_ajax' );
add_action( 'wp_ajax_shop_options_ajax', 'shop_options_ajax' );
add_action( 'wp_ajax_setup_cod_ajax', 'usces_setup_cod_ajax' );
add_action( 'wp_ajax_change_states_ajax', 'change_states_ajax' );
add_action( 'wp_ajax_getinfo_ajax', 'usces_getinfo_ajax' );
//20100809ysk start
add_action( 'wp_ajax_custom_field_ajax', 'custom_field_ajax' );
//20100809ysk end
//20110331ysk start
add_action( 'wp_ajax_target_market_ajax', 'target_market_ajax' );
//20110331ysk end
//20120309ysk start 0000430
add_action( 'wp_ajax_usces_admin_ajax', 'usces_admin_ajax' );
//20120309ysk end

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
add_filter('usces_filter_confirm_inform', 'wc_purchase_nonce', 20, 5 );
add_filter('usces_filter_changepassword_inform', 'usces_filter_lostmail_inform' );

add_filter('usces_purchase_check', 'wc_purchase_nonce_check', 1 );

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
add_action('usces_action_reg_orderdata', 'usces_action_reg_orderdata');
add_action('usces_action_reg_orderdata', 'usces_reg_ordercartdata');
add_action('usces_action_reg_orderdata', 'usces_action_reg_orderdata_stocks');
add_action('usces_action_confirm_page_point_inform', 'usces_use_point_nonce' );
add_action('usces_action_newmember_page_inform', 'usces_post_member_nonce' );
add_action('usces_action_memberinfo_page_inform', 'usces_post_member_nonce' );
add_action('usces_action_newpass_page_inform', 'usces_post_member_nonce' );
add_action('usces_action_changepass_page_inform', 'usces_action_lostmail_inform' );
add_action('usces_action_changepass_page_inform', 'usces_post_member_nonce' );
add_action('usces_action_customer_page_inform', 'usces_post_member_nonce' );


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

add_action('usces_action_confirm_page_point_inform', 'usces_action_confirm_page_point_inform_zeus', 9);
add_filter('usces_filter_confirm_point_inform', 'usces_filter_confirm_point_inform_zeus', 9);

add_filter('wp_title', 'fiter_mainTitle', 10, 2);
add_filter('universal_ga_ecommerce_tracking', 'usces_Universal_trackPageview');
add_filter('classic_ga_ecommerce_tracking', 'usces_Classic_trackPageview');

add_action('usces_action_order_edit_form_detail_top', 'usces_order_memo_form_detail_top', 10, 2 );
add_action('usces_action_update_orderdata', 'usces_update_order_memo');

add_action('wc_cron', 'uscers_cron_do');

