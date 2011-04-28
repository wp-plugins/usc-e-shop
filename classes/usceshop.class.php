<?php
class usc_e_shop
{

	var $page;   //page action
	var $cart;          //cart object
	var $use_ssl;       //ssl flag
	var $action, $action_status, $error_status;
	var $action_message, $error_message;
	var $itemskus, $itemsku, $itemopts, $itemopt, $item;
	var $zaiko_status, $payment_structure, $display_mode, $shipping_rule;
	var $member_status;
	var $options;
	var $login_mail, $current_member, $member_form;
	var $payment_results, $log_flg, $delim, $use_js;

	function usc_e_shop()
	{

//20110331ysk start
		//global $post, $usces_settings;
		global $post, $usces_settings, $usces_states;
//20110331ysk end
		do_action('usces_construct');
		$this->usces_session_start();
		
		if ( !isset($_SESSION['usces_member']) ){
			$_SESSION['usces_member'] = array();
		}
		if ( is_admin() ){
			clean_term_cache( get_option('usces_item_cat_parent_id'), 'category' );
		}
		if(!isset($_SESSION['usces_checked_business_days'])) $this->update_business_days();
		$this->check_display_mode();
		
		$locales = usces_locales();
		foreach($locales as $l){
			$usces_settings['language'][$l] = $l;
		}
		$usces_settings['language']['others'] = __('Follow config.php', 'usces');
		
		$this->options = get_option('usces');
		if(!isset($this->options['smtp_hostname']) || empty($this->options['smtp_hostname'])){ $this->options['smtp_hostname'] = 'localhost';}
		if(!isset($this->options['divide_item'])) $this->options['divide_item'] = 0;
		if(!isset($this->options['fukugo_category_orderby'])) $this->options['fukugo_category_orderby'] = 'ID';
		if(!isset($this->options['fukugo_category_order'])) $this->options['fukugo_category_order'] = 'ASC';
//20110331ysk start
		//if(!isset($this->options['province'])) $this->options['province'] = get_option('usces_pref');
//20110331ysk end
		if(!isset($this->options['membersystem_state'])) $this->options['membersystem_state'] = 'activate';
		if(!isset($this->options['membersystem_point'])) $this->options['membersystem_point'] = 'activate';
		if(!isset($this->options['settlement_path'])) $this->options['settlement_path'] = USCES_PLUGIN_DIR . '/settlement/';
		if(!isset($this->options['use_ssl'])) $this->options['use_ssl'] = 0;
		if(!isset($this->options['point_coverage'])) $this->options['point_coverage'] = 0;
		if(!isset($this->options['use_javascript'])) $this->options['use_javascript'] = 1;
		if(!isset($this->options['system']['orderby_itemsku'])) $this->options['system']['orderby_itemsku'] = 0;
		if(!isset($this->options['system']['orderby_itemopt'])) $this->options['system']['orderby_itemopt'] = 0;
		if(!isset($this->options['system']['front_lang'])) $this->options['system']['front_lang'] = usces_get_local_language();
		if(!isset($this->options['system']['currency'])) $this->options['system']['currency'] = usces_get_base_country();
		if(!isset($this->options['system']['addressform'])) $this->options['system']['addressform'] = usces_get_local_addressform();
		if(!isset($this->options['system']['target_market'])) $this->options['system']['target_market'] = usces_get_local_target_market();
//20010420ysk start
		//if(!isset($this->options['system']['base_country'])) $this->options['system']['base_country'] = usces_get_base_country();
		$this->options['system']['base_country'] = usces_get_base_country();
//20010420ysk end
//20110331ysk start
		if(!isset($this->options['province'])) $this->options['province'][$this->options['system']['base_country']] = $usces_states[$this->options['system']['base_country']];
//20110331ysk end
		if(!isset($this->options['system']['pointreduction'])) $this->options['system']['pointreduction'] = usces_get_pointreduction($this->options['system']['currency']);
		if(!isset($this->options['indi_item_name'])){
			$this->options['indi_item_name']['item_name'] = 1;
			$this->options['indi_item_name']['item_code'] = 1;
			$this->options['indi_item_name']['sku_name'] = 1;
			$this->options['indi_item_name']['sku_code'] = 1;
			$this->options['pos_item_name']['item_name'] = 1;
			$this->options['pos_item_name']['item_code'] = 2;
			$this->options['pos_item_name']['sku_name'] = 3;
			$this->options['pos_item_name']['sku_code'] = 4;
		}
		update_option('usces', $this->options);

		$this->error_message = '';
		$this->login_mail = '';
		$this->get_current_member();
		$this->page = '';
		$this->payment_results = array();
		$this->use_js = $this->options['use_javascript'];

		//admin_ssl options
//		$this->use_ssl = get_option("admin_ssl_use_ssl") === "1" ? true : false;
//		$use_shared = get_option("admin_ssl_use_shared") === "1" && $this->use_ssl ? true : false;
//		$shared_url = get_option("admin_ssl_shared_url");
		
		$this->use_ssl = $this->options['use_ssl'];
//		if ( $use_shared ) {
//			$ssl_url = str_replace('/wp-admin/', '', $shared_url);
//		} else {
//			$ssl_url = str_replace('http://', 'https://', get_option('home'));
//		}
		define('USCES_CART_NUMBER', get_option('usces_cart_number'));
		define('USCES_MEMBER_NUMBER', get_option('usces_member_number'));

		if ( $this->use_ssl ) {
			$ssl_url = $this->options['ssl_url'];
			$ssl_url_admin = $this->options['ssl_url_admin'];
			if( $this->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $this->is_inquiry_page($_SERVER['REQUEST_URI']) ){
				define('USCES_FRONT_PLUGIN_URL', $ssl_url_admin . '/wp-content/plugins/' . USCES_PLUGIN_FOLDER);
			}else{
				define('USCES_FRONT_PLUGIN_URL', USCES_WP_CONTENT_URL . '/plugins/' . USCES_PLUGIN_FOLDER);
			}
			define('USCES_SSL_URL', $ssl_url);
			define('USCES_SSL_URL_ADMIN', $ssl_url_admin);
			define('USCES_COOKIEPATH', preg_replace('|https?://[^/]+|i', '', $ssl_url . '/' ) );
		}else{
			define('USCES_FRONT_PLUGIN_URL', USCES_WP_CONTENT_URL . '/plugins/' . USCES_PLUGIN_FOLDER);
			define('USCES_SSL_URL', get_option('home'));
			define('USCES_SSL_URL_ADMIN', get_option('siteurl'));
			define('USCES_COOKIEPATH', COOKIEPATH);
		}
//		if($this->use_ssl) {
//			define('USCES_CART_URL', $ssl_url . '/?page_id=' . USCES_CART_NUMBER . '&usces=' . $this->get_uscesid());
//			define('USCES_MEMBER_URL', $ssl_url . '/?page_id=' . USCES_MEMBER_NUMBER . '&usces=' . $this->get_uscesid());
//			define('USCES_INQUIRY_URL', $ssl_url . '/?page_id=' . $this->options['inquiry_id'] . '&usces=' . $this->get_uscesid());
//			add_filter('home_url', array($this, 'usces_ssl_page_link'));
//			add_filter('wp_get_attachment_url', array($this, 'usces_ssl_attachment_link'));
//			add_filter('stylesheet_directory_uri', array($this, 'usces_ssl_contents_link'));
//			add_filter('template_directory_uri', array($this, 'usces_ssl_contents_link'));
//			add_filter('script_loader_src', array($this, 'usces_ssl_script_link'));
//			add_filter('style_loader_src', array($this, 'usces_ssl_script_link'));
//		} else {
//			define('USCES_CART_URL', get_option('home') . '/?page_id=' . USCES_CART_NUMBER);
//			define('USCES_MEMBER_URL', get_option('home') . '/?page_id=' . USCES_MEMBER_NUMBER);
//			define('USCES_INQUIRY_URL', get_option('home') . '/?page_id=' . $this->options['inquiry_id']);
//		}
		define('USCES_ITEM_CAT_PARENT_ID', get_option('usces_item_cat_parent_id'));

		$this->zaiko_status = get_option('usces_zaiko_status');
		$this->member_status = get_option('usces_customer_status');
		$this->payment_structure = get_option('usces_payment_structure');
		$this->display_mode = get_option('usces_display_mode');
		define('USCES_MYSQL_VERSION', (int)substr(mysql_get_server_info(), 0, 1));
		define('USCES_JP', ('ja' == get_locale() ? true : false));
		
	}
	
	function get_default_post_to_edit30( $post_type = 'post', $create_in_db = false ) {
		global $wpdb;
	
		$post_title = '';
		if ( !empty( $_REQUEST['post_title'] ) )
			$post_title = esc_html( stripslashes( $_REQUEST['post_title'] ));
	
		$post_content = '';
		if ( !empty( $_REQUEST['content'] ) )
			$post_content = esc_html( stripslashes( $_REQUEST['content'] ));
	
		$post_excerpt = '';
		if ( !empty( $_REQUEST['excerpt'] ) )
			$post_excerpt = esc_html( stripslashes( $_REQUEST['excerpt'] ));
	
		if ( $create_in_db ) {
			// Cleanup old auto-drafts more than 7 days old
			$old_posts = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_status = 'auto-draft' AND DATE_SUB( NOW(), INTERVAL 7 DAY ) > post_date" );
			foreach ( (array) $old_posts as $delete )
				wp_delete_post( $delete, true ); // Force delete
			$post = get_post( wp_insert_post( array( 'post_title' => __( 'Auto Draft' ), 'post_type' => $post_type, 'post_status' => 'auto-draft' ) ) );
		} else {
			$post->ID = 0;
			$post->post_author = '';
			$post->post_date = '';
			$post->post_date_gmt = '';
			$post->post_password = '';
			$post->post_type = $post_type;
			$post->post_status = 'draft';
			$post->to_ping = '';
			$post->pinged = '';
			$post->comment_status = get_option( 'default_comment_status' );
			$post->ping_status = get_option( 'default_ping_status' );
			$post->post_pingback = get_option( 'default_pingback_flag' );
			$post->post_category = get_option( 'default_category' );
			$post->page_template = 'default';
			$post->post_parent = 0;
			$post->menu_order = 0;
		}
	
		$post->post_content = apply_filters( 'default_content', $post_content, $post );
		$post->post_title   = apply_filters( 'default_title',   $post_title, $post   );
		$post->post_excerpt = apply_filters( 'default_excerpt', $post_excerpt, $post );
		$post->post_name = '';
	
		return $post;
	}

	function get_default_post_to_edit() {
		global $post;
		
		$post_title = '';
		if ( !empty( $_REQUEST['post_title'] ) )
			$post_title = esc_html( stripslashes( $_REQUEST['post_title'] ));
	
		$post_content = '';
		if ( !empty( $_REQUEST['content'] ) )
			$post_content = esc_html( stripslashes( $_REQUEST['content'] ));
	
		$post_excerpt = '';
		if ( !empty( $_REQUEST['excerpt'] ) )
			$post_excerpt = esc_html( stripslashes( $_REQUEST['excerpt'] ));
	
		$post->ID = 0;
		$post->post_name = '';
		$post->post_author = '';
		$post->post_date = '';
		$post->post_date_gmt = '';
		$post->post_password = '';
		$post->post_status = 'draft';
		$post->post_type = 'post';
		$post->to_ping = '';
		$post->pinged = '';
		$post->comment_status = get_option( 'default_comment_status' );
		$post->ping_status = get_option( 'default_ping_status' );
		$post->post_pingback = get_option( 'default_pingback_flag' );
		$post->post_category = get_option( 'default_category' );
		$post->post_content = apply_filters( 'default_content', $post_content);
		$post->post_title = apply_filters( 'default_title', $post_title );
		$post->post_excerpt = apply_filters( 'default_excerpt', $post_excerpt);
		$post->page_template = 'default';
		$post->post_parent = 0;
		$post->menu_order = 0;
	
		return $post;
	}
	function is_cart_or_member_page($link)
	{
		$search = array(('page_id='.USCES_CART_NUMBER), '/usces-cart', ('page_id='.USCES_MEMBER_NUMBER), '/usces-member');
		$flag = false;
		foreach($search as $value){
			$parts = array();
			if( false !== strpos($link, $value) ){
				if( $value == ('page_id='.USCES_CART_NUMBER) ||  $value == ('page_id='.USCES_MEMBER_NUMBER) ){
					$parts = parse_url($link);
					parse_str($parts['query'], $query);
					if( $query['page_id'] == USCES_CART_NUMBER || $query['page_id'] == USCES_MEMBER_NUMBER ){
						$flag = true;
					}
				}else{
					$flag = true;
				}
			}
		}
		return $flag;
	}
	
	function is_cart_page($link)
	{
		$search = array(('page_id='.USCES_CART_NUMBER), '/usces-cart' );
		$flag = false;
		foreach($search as $value){
			if( false !== strpos($link, $value) ){
				if( $value == ('page_id='.USCES_CART_NUMBER) ){
					$parts = parse_url($link);
					parse_str($parts['query'], $query);
					if( $query['page_id'] == USCES_CART_NUMBER ){
						$flag = true;
					}
				}else{
					$flag = true;
				}
			}
		}
		return $flag;
	}
	
	function is_member_page($link)
	{
		$search = array(('page_id='.USCES_MEMBER_NUMBER), '/usces-member' );
		$flag = false;
		foreach($search as $value){
			if( false !== strpos($link, $value) ){
				if( $value == ('page_id='.USCES_MEMBER_NUMBER) ){
					$parts = parse_url($link);
					parse_str($parts['query'], $query);
					if( $query['page_id'] == USCES_MEMBER_NUMBER ){
						$flag = true;
					}
				}else{
					$flag = true;
				}
			}
		}
		return $flag;
	}
	
	function is_inquiry_page($link)
	{
		if( empty($this->options['inquiry_id']) )
			return false;
		
		$search = array(('page_id='.$this->options['inquiry_id']), '/usces-inquiry' );
		$flag = false;
		foreach($search as $value){
			if( false !== strpos($link, $value) ){
				if( $value == ('page_id='.$this->options['inquiry_id']) ){
					$parts = parse_url($link);
					parse_str($parts['query'], $query);
					if( $query['page_id'] == $this->options['inquiry_id'] ){
						$flag = true;
					}
				}else{
					$flag = true;
				}
			}
		}
		return $flag;
	}
	
	function usces_ssl_page_link($link)
	{
		$parts = parse_url($link);
		parse_str($parts['query'], $query);
		
		
		if( false !== strpos($link, '/usces-cart') || $query['page_id'] == USCES_CART_NUMBER ){
			$link = USCES_CART_URL;
			
		}elseif( false !== strpos($link, '/usces-member') || $query['page_id'] == USCES_MEMBER_NUMBER ){
			$link = USCES_MEMBER_URL;
		
		}elseif( !empty($this->options['inquiry_id']) && (false !== strpos($link, '/usces-inquiry') || $query['page_id'] == $this->options['inquiry_id']) ){
			$link = USCES_INQUIRY_URL;

		}else{
			$link = str_replace('https://', 'http://', $link);
			$link = apply_filters('usces_ssl_page_link', $link);
		
		}		
		return $link;
	}
	function usces_ssl_contents_link($link)
	{
		if( $this->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $this->is_inquiry_page($_SERVER['REQUEST_URI'])){
			$req = explode('/wp-content/',$link);
			$link = USCES_SSL_URL_ADMIN . '/wp-content/' . $req[1];
		}
		return $link;
	}

	function usces_ssl_attachment_link($link)
	{
		if( $this->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $this->is_inquiry_page($_SERVER['REQUEST_URI']) ){
			$link = str_replace(get_option('siteurl'), USCES_SSL_URL_ADMIN, $link);
		}
		return $link;
	}

	function usces_ssl_icon_dir_uri($uri)
	{
		if( $this->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $this->is_inquiry_page($_SERVER['REQUEST_URI']) ){
			$uri = USCES_SSL_URL_ADMIN. '/' . WPINC . '/images/crystal';
		}
		return $uri;
	}

	function usces_ssl_script_link($link)
	{
		if( $this->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $this->is_inquiry_page($_SERVER['REQUEST_URI']) ){
			if(strpos($link, '/wp-content/') !== false){
				$req = explode('/wp-content/',$link, 2);
				$link = USCES_SSL_URL_ADMIN . '/wp-content/' . $req[1];
			}else if(strpos($link, '/wp-includes/') !== false){
				$req = explode('/wp-includes/',$link, 2);
				$link = USCES_SSL_URL_ADMIN . '/wp-includes/' . $req[1];
			}else if(strpos($link, '/wp-admin/') !== false){
				$req = explode('/wp-admin/',$link, 2);
				$link = USCES_SSL_URL_ADMIN . '/wp-admin/' . $req[1];
			}
		}
		return $link;
	}

	function set_action_status($status, $message)
	{
		$this->action_status = $status;
		$this->action_message = $message;
	}


	/******************************************************************************/
	function add_pages() {

	
		add_object_page('Welcart Shop', 'Welcart Shop', 6, USCES_PLUGIN_BASENAME, array($this, 'admin_top_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('Home','usces'), __('Home','usces'), 6, USCES_PLUGIN_BASENAME, array($this, 'admin_top_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('Master Items','usces'), __('Master Items','usces'), 6, 'usces_itemedit', array($this, 'item_master_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('Add New Item','usces'), __('Add New Item','usces'), 6, 'usces_itemnew', array($this, 'item_master_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('General Setting','usces'), __('General Setting','usces'), 6, 'usces_initial', array($this, 'admin_setup_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('Business Days Setting','usces'), __('Business Days Setting','usces'), 6, 'usces_schedule', array($this, 'admin_schedule_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('Shipping Setting','usces'), __('Shipping Setting','usces'), 6, 'usces_delivery', array($this, 'admin_delivery_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('E-mail Setting','usces'), __('E-mail Setting','usces'), 6, 'usces_mail', array($this, 'admin_mail_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('Cart Page Setting','usces'), __('Cart Page Setting','usces'), 6, 'usces_cart', array($this, 'admin_cart_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('Member Page Setting','usces'), __('Member Page Setting','usces'), 6, 'usces_member', array($this, 'admin_member_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('System Setting','usces'), __('System Setting','usces'), 6, 'usces_system', array($this, 'admin_system_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('Settlement Setting','usces'), __('Settlement Setting','usces'), 10, 'usces_settlement', array($this, 'admin_settlement_page'));
		//add_submenu_page(USCES_PLUGIN_BASENAME, __('Backup','usces'), __('Backup','usces'), 6, 'usces_backup', array($this, 'admin_backup_page'));
		do_action('usces_action_shop_admin_menue');
		
		add_object_page('Welcart Management', 'Welcart Management', 6, 'usces_orderlist', array($this, 'order_list_page'));
		add_submenu_page('usces_orderlist', __('Order List','usces'), __('Order List','usces'), 6, 'usces_orderlist', array($this, 'order_list_page'));
		add_submenu_page('usces_orderlist', __('New Order or Estimate','usces'), __('New Order or Estimate','usces'), 6, 'usces_ordernew', array($this, 'order_list_page'));
		add_submenu_page('usces_orderlist', __('List of Members','usces'), __('List of Members','usces'), 6, 'usces_memberlist', array($this, 'member_list_page'));
		do_action('usces_action_management_admin_menue');
	}


	/* Item Master Page */
	function item_master_page() {
		global $wpdb, $wp_locale;
		global $wp_query;
		
		if(empty($this->action_message) || $this->action_message == '') {
			$this->action_status = 'none';
			$this->action_message = '';
		}
		
		if($_REQUEST['page'] == 'usces_itemnew'){
			$action = 'new';
		}else{
			$action = $_REQUEST['action'];
		}

		switch ( $action ) {
//20101111ysk start
			case 'dlitemlist':
				usces_download_item_list();
				break;
//20101111ysk end
			case 'delete':
			case 'new':
			case 'editpost':
			case 'edit':
				global $current_user;
				require_once(USCES_PLUGIN_DIR . '/includes/usces_item_master_edit.php');
				break;
			default:
				require_once(USCES_PLUGIN_DIR . '/includes/usces_item_master_list.php');
				break;
		}
	}
	
	/* order list page */
	function order_list_page() {

		if(empty($this->action_message) || $this->action_message == '') {
			$this->action_status = 'none';
			$this->action_message = '';
		}
		if($_REQUEST['page'] == 'usces_ordernew'){
			$order_action = 'new';
		}else{
			$order_action = $_REQUEST['order_action'];
		}
		switch ($order_action) {
//20100908ysk start
			case 'dlproductlist':
				usces_download_product_list();
				break;
			case 'dlorderlist':
				usces_download_order_list();
				break;
//20100908ysk end
//			case 'printpdf':
//				require_once(USCES_PLUGIN_DIR . '/includes/order_print.php');	
//				break;
			case 'editpost':
				do_action('usces_pre_update_orderdata', $_REQUEST['order_id']);
				$res = usces_update_orderdata();
				if ( 1 === $res ) {
					do_action('usces_after_update_orderdata', $_REQUEST['order_id']);
					$this->set_action_status('success', __('order date is updated','usces').' <a href="'.stripslashes( $_POST['usces_referer'] ).'">'.__('back to the summary','usces').'</a>');
				} elseif ( 0 === $res ) {
					$this->set_action_status('none', '');
				} else {
					$this->set_action_status('error', 'ERROR : '.__('failure in update','usces'));
				}
				require_once(USCES_PLUGIN_DIR . '/includes/order_edit_form.php');	
				break;
			case 'newpost':
				do_action('usces_pre_new_orderdata');
				$res = usces_new_orderdata();
				if ( 1 === $res ) {
					do_action('usces_after_new_orderdata');
					$this->set_action_status('success', __('New date is add','usces'));
				} elseif ( 0 === $res ) {
					$this->set_action_status('none', '');
				} else {
					$this->set_action_status('error', 'ERROR : '.__('failure in addition','usces'));
				}
				$_REQUEST['order_action'] = 'edit';
				$order_action = $_REQUEST['order_action'];
				require_once(USCES_PLUGIN_DIR . '/includes/order_edit_form.php');	
				break;
			case 'new':
			case 'edit':
				require_once(USCES_PLUGIN_DIR . '/includes/order_edit_form.php');	
				break;
			case 'delete':
				do_action('usces_pre_delete_orderdata', $_REQUEST['order_id']);
				$res = usces_delete_orderdata();
				if ( 1 === $res ) {
					do_action('usces_after_delete_orderdata', $_REQUEST['order_id']);
					$this->set_action_status('success', __('the order date is deleted','usces'));
				} elseif ( 0 === $res ) {
					$this->set_action_status('none', '');
				} else {
					$this->set_action_status('error', 'ERROR : '.__('failure in delete','usces'));
				}
			default:
				require_once(USCES_PLUGIN_DIR . '/includes/order_list.php');	
		}
	}
	
	/* member list page */
	function member_list_page() {

		if(empty($this->action_message) || $this->action_message == '') {
			$this->action_status = 'none';
			$this->action_message = '';
		}
		$member_action = $_REQUEST['member_action'];
		switch ($member_action) {
//20100908ysk start
			case 'dlmemberlist':
				usces_download_member_list();
				break;
//20100908ysk end
			case 'editpost':
				$this->error_message = $this->admin_member_check();
				if($this->error_message == ''){
					$res = usces_update_memberdata();
					if ( 1 === $res ) {
						$this->set_action_status('success', __('Membership information is updated','usces'));
					} elseif ( 0 === $res ) {
						$this->set_action_status('none', '');
					} else {
						$this->set_action_status('error', 'ERROR : '.__('failure in update','usces'));
					}
				}
				require_once(USCES_PLUGIN_DIR . '/includes/member_edit_form.php');	
				break;
			case 'edit':
				require_once(USCES_PLUGIN_DIR . '/includes/member_edit_form.php');	
				break;
			case 'delete':
				$res = usces_delete_memberdata();
				if ( 1 === $res ) {
					$this->set_action_status('success', __('The member data is deleted','usces'));
				} elseif ( 0 === $res ) {
					$this->set_action_status('none', '');
				} else {
					$this->set_action_status('error', 'ERROR : '.__('failure in delete','usces'));
				}
			default:
				require_once(USCES_PLUGIN_DIR . '/includes/member_list.php');	
		}

	}
	
	/* admin backup page */
	function admin_backup_page() {

		if(empty($this->action_message) || $this->action_message == '') {
			$this->action_status = 'none';
			$this->action_message = '';
		}
		require_once(USCES_PLUGIN_DIR . '/includes/admin_backup.php');	

	}
	
	/* Shop Top Page */
	function admin_top_page() {

		require_once(USCES_PLUGIN_DIR . '/includes/admin_top.php');	

	}
	
	/* Shop Setup Page */
	function admin_setup_page() {
	
		$this->options = get_option('usces');
		//$this->options = array();
		

		if(isset($_POST['usces_option_update'])) {
			$this->options['display_mode'] = isset($_POST['display_mode']) ? trim($_POST['display_mode']) : '';
			$this->options['campaign_category'] = empty($_POST['cat']) ? USCES_ITEM_CAT_PARENT_ID : $_POST['cat'];
			$this->options['campaign_privilege'] = isset($_POST['cat_privilege']) ? trim($_POST['cat_privilege']) : '';
			$this->options['privilege_point'] = isset($_POST['point_num']) ? (int)$_POST['point_num'] : '';
			$this->options['privilege_discount'] = isset($_POST['discount_num']) ? (int)$_POST['discount_num'] : '';
			$this->options['company_name'] = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
			$this->options['zip_code'] = isset($_POST['zip_code']) ? trim($_POST['zip_code']) : '';
			$this->options['address1'] = isset($_POST['address1']) ? trim($_POST['address1']) : '';
			$this->options['address2'] = isset($_POST['address2']) ? trim($_POST['address2']) : '';
			$this->options['tel_number'] = isset($_POST['tel_number']) ? trim($_POST['tel_number']) : '';
			$this->options['fax_number'] = isset($_POST['fax_number']) ? trim($_POST['fax_number']) : '';
			$this->options['order_mail'] = isset($_POST['order_mail']) ? trim($_POST['order_mail']) : '';
			$this->options['inquiry_mail'] = isset($_POST['inquiry_mail']) ? trim($_POST['inquiry_mail']) : '';
			$this->options['sender_mail'] = isset($_POST['sender_mail']) ? trim($_POST['sender_mail']) : '';
			$this->options['error_mail'] = isset($_POST['error_mail']) ? trim($_POST['error_mail']) : '';
			$this->options['postage_privilege'] = isset($_POST['postage_privilege']) ? trim($_POST['postage_privilege']) : '';
			$this->options['purchase_limit'] = isset($_POST['purchase_limit']) ? trim($_POST['purchase_limit']) : '';
			$this->options['point_rate'] = isset($_POST['point_rate']) ? (int)$_POST['point_rate'] : '';
			$this->options['start_point'] = isset($_POST['start_point']) ? (int)$_POST['start_point'] : '';
			$this->options['shipping_rule'] = isset($_POST['shipping_rule']) ? trim($_POST['shipping_rule']) : '';
			$this->options['tax_rate'] = isset($_POST['tax_rate']) ? (int)$_POST['tax_rate'] : '';
			$this->options['tax_method'] = isset($_POST['tax_method']) ? trim($_POST['tax_method']) : '';
	
			$this->options['cod_type'] = isset($this->options['cod_type']) ? $this->options['cod_type'] : 'fix';

			$this->options['transferee'] = isset($_POST['transferee']) ? trim($_POST['transferee']) : '';
			$this->options['copyright'] = isset($_POST['copyright']) ? trim($_POST['copyright']) : '';
			$this->options['membersystem_state'] = isset($_POST['membersystem_state']) ? trim($_POST['membersystem_state']) : '';
			$this->options['membersystem_point'] = isset($_POST['membersystem_point']) ? trim($_POST['membersystem_point']) : '';
			$this->options['point_coverage'] = isset($_POST['point_coverage']) ? (int)$_POST['point_coverage'] : 0;

			update_option('usces', $this->options);
			
			$this->action_status = 'success';
			$this->action_message = __('options are updated','usces');
		} else {
			$this->action_status = 'none';
			$this->action_message = '';
		}
		
		require_once(USCES_PLUGIN_DIR . '/includes/admin_setup.php');	

	}
	
	/* Shop Schedule Page */
	function admin_schedule_page() {

		$this->options = get_option('usces');

		if(isset($_POST['usces_option_update'])) {

			$this->options['campaign_schedule'] = isset($_POST['campaign_schedule']) ? $_POST['campaign_schedule'] : '0';
			if(isset($_POST['business_days'])) $this->options['business_days'] = $_POST['business_days'];



			update_option('usces', $this->options);
			
			$this->action_status = 'success';
			$this->action_message = __('options are updated','usces');
		} else {
			$this->action_status = 'none';
			$this->action_message = '';
		}
		
		require_once(USCES_PLUGIN_DIR . '/includes/admin_schedule.php');	

	}
	
	/* Shop Delivery Page */
	function admin_delivery_page() {
	
		$this->options = get_option('usces');

		if(isset($_POST['usces_option_update'])) {

			//$this->options['delivery_time'] = isset($_POST['delivery_time']) ? $_POST['delivery_time'] : '';
			//$this->options['shipping_charges'] = isset($_POST['shipping_charge']) ? $_POST['shipping_charge'] : '';

//20101208ysk start
			if(isset($_POST['delivery_time_limit'])) $this->options['delivery_time_limit'] = $_POST['delivery_time_limit'];
			if(isset($_POST['shortest_delivery_time'])) $this->options['shortest_delivery_time'] =  $_POST['shortest_delivery_time'];
			if(isset($_POST['delivery_after_days'])) $this->options['delivery_after_days'] =  $_POST['delivery_after_days'];
//20101208ysk end

			update_option('usces', $this->options);
			
			$this->action_status = 'success';
			$this->action_message = __('options are updated','usces');
		} else {
			$this->action_status = 'none';
			$this->action_message = '';
		}
		
		require_once(USCES_PLUGIN_DIR . '/includes/admin_delivery.php');	

	}
	
	/* Shop Mail Page */
	function admin_mail_page() {
	
		$this->options = get_option('usces');

		if(isset($_POST['usces_option_update'])) {
		
			$this->options['smtp_hostname'] = trim($_POST['smtp_hostname']);
		
			foreach ( $_POST['title'] as $key => $value ) {
				if( trim($value) == '' ) {
					$this->options['mail_data']['title'][$key] = $this->options['mail_default']['title'][$key];
				}else{
					$this->options['mail_data']['title'][$key] = trim($value);
				}
			}
			foreach ( $_POST['header'] as $key => $value ) {
				if( trim($value) == '' ) {
					$this->options['mail_data']['header'][$key] = $this->options['mail_default']['header'][$key];
				}else{
					$this->options['mail_data']['header'][$key] = $value;
				}
			}
			foreach ( $_POST['footer'] as $key => $value ) {
				if( trim($value) == '' ) {
					$this->options['mail_data']['footer'][$key] = $this->options['mail_default']['footer'][$key];
				}else{
					$this->options['mail_data']['footer'][$key] = $value;
				}
			}

			
			$this->action_status = 'success';
			$this->action_message = __('options are updated','usces');
			
		} else {
		
			foreach ( (array)$this->options['mail_data']['title'] as $key => $value ) {
				if( trim($value) == '' ) {
					$this->options['mail_data']['title'][$key] = $this->options['mail_default']['title'][$key];
				}
			}
			foreach ( (array)$this->options['mail_data']['header'] as $key => $value ) {
				if( trim($value) == '' ) {
					$this->options['mail_data']['header'][$key] = $this->options['mail_default']['header'][$key];
				}
			}
			foreach ( (array)$this->options['mail_data']['footer'] as $key => $value ) {
				if( trim($value) == '' ) {
					$this->options['mail_data']['footer'][$key] = $this->options['mail_default']['footer'][$key];
				}
			}

			$this->action_status = 'none';
			$this->action_message = '';
			
		}
	
		update_option('usces', $this->options);
		
		require_once(USCES_PLUGIN_DIR . '/includes/admin_mail.php');	

	}
	
	/* Admin Cart Page */
	function admin_cart_page() {

		$this->options = get_option('usces');

		if(isset($_POST['usces_option_update'])) {

			foreach ( $this->options['indi_item_name'] as $key => $value ) {
				$this->options['indi_item_name'][$key] = isset($_POST['indication'][$key]) ? 1 : 0;
			}
			foreach ( $_POST['position'] as $key => $value ) {
				$this->options['pos_item_name'][$key] = $value;
			}
			foreach ( $_POST['header'] as $key => $value ) {
				$this->options['cart_page_data']['header'][$key] = addslashes($value);
			}
			foreach ( $_POST['footer'] as $key => $value ) {
				$this->options['cart_page_data']['footer'][$key] = addslashes($value);
			}

			update_option('usces', $this->options);
			
			$this->action_status = 'success';
			$this->action_message = __('options are updated','usces');
		} else {

			$this->action_status = 'none';
			$this->action_message = '';
		}


		
		require_once(USCES_PLUGIN_DIR . '/includes/admin_cart.php');	

	}
	
	/* Admin Member Page */
	function admin_member_page() {

		$this->options = get_option('usces');

		if(isset($_POST['usces_option_update'])) {

			foreach ( $_POST['header'] as $key => $value ) {
				$this->options['member_page_data']['header'][$key] = $value;
			}
			foreach ( $_POST['footer'] as $key => $value ) {
				$this->options['member_page_data']['footer'][$key] = $value;
			}

			update_option('usces', $this->options);
			
			$this->action_status = 'success';
			$this->action_message = __('options are updated','usces');
		} else {

			$this->action_status = 'none';
			$this->action_message = '';
		}


		
		require_once(USCES_PLUGIN_DIR . '/includes/admin_member.php');	

	}
	
	
	/* Admin System Page */
	function admin_system_page() {
//20110331ysk start
		global $usces_states;
//20110331ysk end

		$this->options = get_option('usces');

		if(isset($_POST['usces_option_update'])) {
		
//20110331ysk start
/*			if($_POST['province'] != ''){
				$temp_pref = explode("\n", $_POST['province']);
				for($i=-1; $i<count($temp_pref); $i++){
					if($i == -1){
						$usces_pref[] = __('-- Select --','usces');
					}else{
						$usces_pref[] = trim($temp_pref[$i]);
					}
				}
			}else{
				$usces_pref = get_option('usces_pref');
			}

			$this->options['province'] = $usces_pref;*/
//20110331ysk end
			$this->options['divide_item'] = isset($_POST['divide_item']) ? 1 : 0;
			$this->options['itemimg_anchor_rel'] = isset($_POST['itemimg_anchor_rel']) ? trim($_POST['itemimg_anchor_rel']) : '';
			$this->options['fukugo_category_orderby'] = isset($_POST['fukugo_category_orderby']) ? $_POST['fukugo_category_orderby'] : '';
			$this->options['fukugo_category_order'] = isset($_POST['fukugo_category_order']) ? $_POST['fukugo_category_order'] : '';
			$this->options['settlement_path'] = isset($_POST['settlement_path']) ? stripslashes($_POST['settlement_path']) : '';
			if($this->options['settlement_path'] == '') $this->options['settlement_path'] = USCES_PLUGIN_DIR . '/settlement/';
			$sl = substr($this->options['settlement_path'], -1);
			if($sl != '/' && $sl != '\\') $this->options['settlement_path'] .= '/';
			$this->options['use_ssl'] = isset($_POST['use_ssl']) ? 1 : 0;
			$this->options['ssl_url'] = isset($_POST['ssl_url']) ? stripslashes(rtrim($_POST['ssl_url'], '/')) : '';
			$this->options['ssl_url_admin'] = isset($_POST['ssl_url_admin']) ? stripslashes(rtrim($_POST['ssl_url_admin'], '/')) : '';
			if( $this->options['ssl_url'] == '' || $this->options['ssl_url_admin'] == '' ) $this->options['use_ssl'] = 0;
			$this->options['inquiry_id'] = isset($_POST['inquiry_id']) ? esc_html(rtrim($_POST['inquiry_id'])) : '';
			$this->options['use_javascript'] = isset($_POST['use_javascript']) ? (int)$_POST['use_javascript'] : 1;
			$this->options['system']['front_lang'] = (isset($_POST['front_lang']) && 'others' != $_POST['front_lang']) ? $_POST['front_lang'] : usces_get_local_language();
			$this->options['system']['currency'] = (isset($_POST['currency']) && 'others' != $_POST['currency']) ? $_POST['currency'] : usces_get_base_country();
			$this->options['system']['addressform'] = (isset($_POST['addressform']) ) ? $_POST['addressform'] : usces_get_local_addressform();
			$this->options['system']['target_market'] = (isset($_POST['target_market']) ) ? $_POST['target_market'] : usces_get_local_target_market();
			$this->options['system']['orderby_itemsku'] = isset($_POST['orderby_itemsku']) ? (int)$_POST['orderby_itemsku'] : 0;
			$this->options['system']['orderby_itemopt'] = isset($_POST['orderby_itemopt']) ? (int)$_POST['orderby_itemopt'] : 0;
//20110331ysk start
			unset($this->options['province']);
			$action_status = '';
			foreach((array)$this->options['system']['target_market'] as $target_market) {
				$province = array();
				if(!empty($_POST['province_'.$target_market])) {
					$temp_pref = explode("\n", $_POST['province_'.$target_market]);
					$province[] = __('-- Select --', 'usces');
					for($i = 0; $i < count($temp_pref); $i++) {
						$province[] = trim($temp_pref[$i]);
					}
				} else {
					if(is_array($usces_states[$target_market])) {
						$province = $usces_states[$target_market];
					} else {
						$action_status = 'error';
					}
				}
				$this->options['province'][$target_market] = $province;
			}

			if($action_status != '') {
				$this->action_status = 'error';
				$this->action_message = __('データに不備が有ります','usces');
			} else {
				$this->action_status = 'success';
				$this->action_message = __('options are updated','usces');
			}
//20110331ysk end
		} else {

			if( !isset($this->options['province']) || $this->options['province'] == '' ){
//20110331ysk start
				//$this->options['province'] = get_option('usces_pref');
				$this->options['province'][$this->options['system']['base_country']] = $usces_states[$this->options['system']['base_country']];
//20110331ysk end
			}
			$this->action_status = 'none';
			$this->action_message = '';
		}

		update_option('usces', $this->options);

		
		require_once(USCES_PLUGIN_DIR . '/includes/admin_system.php');	

	}
	
	/* Settlement Setting Page */
	function admin_settlement_page() {
	
		$this->action_status = 'none';
		$this->action_message = '';

		$options = get_option('usces');

	
		if( isset($_POST['usces_option_update']) ) {
			$mes = '';
		
			switch( $_POST['acting'] ){
				case 'zeus':
					unset( $options['acting_settings']['zeus'] );
					$options['acting_settings']['zeus']['card_url'] = $_POST['card_url'];
					$options['acting_settings']['zeus']['ipaddrs'] = $_POST['ipaddrs'];
					$options['acting_settings']['zeus']['pay_cvs'] = $_POST['pay_cvs'];
					$options['acting_settings']['zeus']['card_activate'] = $_POST['card_activate'];
					$options['acting_settings']['zeus']['quickcharge'] = $_POST['quickcharge'];
					$options['acting_settings']['zeus']['clientip'] = trim($_POST['clientip']);
					$options['acting_settings']['zeus']['howpay'] = $_POST['howpay'];
					$options['acting_settings']['zeus']['bank_activate'] = $_POST['bank_activate'];
					$options['acting_settings']['zeus']['clientip_bank'] = trim($_POST['clientip_bank']);
					$options['acting_settings']['zeus']['testid_bank'] = trim($_POST['testid_bank']);
					$options['acting_settings']['zeus']['bank_url'] = $_POST['bank_url'];
					$options['acting_settings']['zeus']['conv_activate'] = $_POST['conv_activate'];
					$options['acting_settings']['zeus']['clientip_conv'] = trim($_POST['clientip_conv']);
					$options['acting_settings']['zeus']['testid_conv'] = trim($_POST['testid_conv']);
					$options['acting_settings']['zeus']['test_type_conv'] = ( '' == $_POST['testid_conv'] || !isset($_POST['test_type']) ) ? '0' : $_POST['test_type'];
					$options['acting_settings']['zeus']['conv_url'] = $_POST['conv_url'];

					if( '' == trim($_POST['clientip']) && 'on' == $_POST['card_activate'] )
						$mes .= '※カード決済IPコードを入力して下さい<br />';
					if( '' == trim($_POST['clientip_bank']) && 'on' == $_POST['bank_activate'] )
						$mes .= '※入金お任せIPコードを入力して下さい<br />';
					if( '' == trim($_POST['clientip_conv']) && 'on' == $_POST['conv_activate'] )
						$mes .= '※コンビニ決済IPコードを入力して下さい<br />';
					if( !isset($_POST['card_url']) || empty($_POST['card_url']) || !isset($_POST['ipaddrs']) || empty($_POST['ipaddrs']) || !isset($_POST['bank_url']) || empty($_POST['bank_url']) || !isset($_POST['conv_url']) || empty($_POST['conv_url']) )
						$mes .= '※設定が不正です！<br />';

					if( '' == $mes ){			
						$this->action_status = 'success';
						$this->action_message = __('options are updated','usces');
						$options['acting_settings']['zeus']['activate'] = 'on';
						if( 'on' == $options['acting_settings']['zeus']['card_activate'] ){
							$this->payment_structure['acting_zeus_card'] = 'カード決済（ZEUS）';
						}else{
							unset($this->payment_structure['acting_zeus_card']);
						}
						if( 'on' == $options['acting_settings']['zeus']['bank_activate'] ){
							$this->payment_structure['acting_zeus_bank'] = '入金お任せ（ZEUS）';
						}else{
							unset($this->payment_structure['acting_zeus_bank']);
						}
						if( 'on' == $options['acting_settings']['zeus']['conv_activate'] ){
							$this->payment_structure['acting_zeus_conv'] = 'コンビニ決済（ZEUS）';
						}else{
							unset($this->payment_structure['acting_zeus_bank']);
						}
					}else{
						$this->action_status = 'error';
						$this->action_message = __('データに不備が有ります','usces');
						$options['acting_settings']['zeus']['activate'] = 'off';
						unset($this->payment_structure['acting_zeus_card'], $this->payment_structure['acting_zeus_bank']);
					}
					ksort($this->payment_structure);
					update_option('usces_payment_structure',$this->payment_structure);
					break;
					
				case 'remise':
					unset( $options['acting_settings']['remise'] );
					$options['acting_settings']['remise']['plan'] = $_POST['plan'];
					$options['acting_settings']['remise']['SHOPCO'] = $_POST['SHOPCO'];
					$options['acting_settings']['remise']['HOSTID'] = $_POST['HOSTID'];
					$options['acting_settings']['remise']['card_activate'] = $_POST['card_activate'];
					$options['acting_settings']['remise']['card_pc_ope'] = $_POST['card_pc_ope'];
					$options['acting_settings']['remise']['payquick'] = $_POST['payquick'];
					$options['acting_settings']['remise']['howpay'] = $_POST['howpay'];
					$options['acting_settings']['remise']['continuation'] = $_POST['continuation'];
					$options['acting_settings']['remise']['conv_activate'] = $_POST['conv_activate'];
					$options['acting_settings']['remise']['conv_pc_ope'] = $_POST['conv_pc_ope'];
					$options['acting_settings']['remise']['S_PAYDATE'] = $_POST['S_PAYDATE'];
					$options['acting_settings']['remise']['send_url_mbl'] = $_POST['send_url_mbl'];
					$options['acting_settings']['remise']['send_url_pc'] = $_POST['send_url_pc'];
					$options['acting_settings']['remise']['send_url_cvs_mbl'] = $_POST['send_url_cvs_mbl'];
					$options['acting_settings']['remise']['send_url_cvs_pc'] = $_POST['send_url_cvs_pc'];
					$options['acting_settings']['remise']['send_url_mbl_test'] = $_POST['send_url_mbl_test'];
					$options['acting_settings']['remise']['send_url_pc_test'] = $_POST['send_url_pc_test'];
					$options['acting_settings']['remise']['send_url_cvs_mbl_test'] = $_POST['send_url_cvs_mbl_test'];
					$options['acting_settings']['remise']['send_url_cvs_pc_test'] = $_POST['send_url_cvs_pc_test'];
					$options['acting_settings']['remise']['REMARKS3'] = $_POST['REMARKS3'];

					if( '0' === $_POST['plan_remise'] )
						$mes .= '※サービスプランを選択してください<br />';
					if( '' == trim($_POST['SHOPCO']) )
						$mes .= '※加盟店コードを入力して下さい<br />';
					if( '' == trim($_POST['HOSTID']) )
						$mes .= '※ホスト番号を入力して下さい<br />';
					if( isset($_POST['conv_activate']) && 'on' == $_POST['conv_activate'] && empty($_POST['S_PAYDATE']) )
						$mes .= '※支払期限を入力して下さい<br />';
					if( isset($_POST['card_pc_ope']) && 'public' == $_POST['card_pc_ope'] && empty($_POST['send_url_pc']) )
						$mes .= '※クレジットカード決済の本番URLを入力して下さい<br />';
					if( isset($_POST['conv_pc_ope']) && 'public' == $_POST['conv_pc_ope'] && empty($_POST['send_url_cvs_pc']) )
						$mes .= '※コンビニ・電子マネー決済の本番URLを入力して下さい<br />';
					if( !isset($_POST['REMARKS3']) || empty($_POST['REMARKS3']) )
						$mes .= '※設定が不正です！<br />';

					if( '' == $mes ){			
						$this->zaction_status = 'success';
						$this->action_message = __('options are updated','usces');
						$options['acting_settings']['remise']['activate'] = 'on';
						if( 'on' == $options['acting_settings']['remise']['card_activate'] ){
							$this->payment_structure['acting_remise_card'] = 'カード決済（ルミーズ）';
						}else{
							unset($this->payment_structure['acting_remise_card']);
						}
						if( 'on' == $options['acting_settings']['remise']['conv_activate'] ){
							$this->payment_structure['acting_remise_conv'] = 'コンビニ決済（ルミーズ）';
						}else{
							unset($this->payment_structure['acting_remise_conv']);
						}

					}else{
						$this->action_status = 'error';
						$this->action_message = __('データに不備が有ります','usces');
						$options['acting_settings']['remise']['activate'] = 'off';
						unset($this->payment_structure['acting_remise_card']);
						unset($this->payment_structure['acting_remise_conv']);
					}
					ksort($this->payment_structure);
					update_option('usces_payment_structure',$this->payment_structure);
					break;
//20101018ysk start
				case 'jpayment':
					unset( $options['acting_settings']['jpayment'] );
					$options['acting_settings']['jpayment']['aid'] = $_POST['aid'];
					$options['acting_settings']['jpayment']['card_activate'] = $_POST['card_activate'];
					$options['acting_settings']['jpayment']['card_jb'] = $_POST['card_jb'];
					$options['acting_settings']['jpayment']['conv_activate'] = $_POST['conv_activate'];
					//$options['acting_settings']['jpayment']['webm_activate'] = $_POST['webm_activate'];
					//$options['acting_settings']['jpayment']['bitc_activate'] = $_POST['bitc_activate'];
					//$options['acting_settings']['jpayment']['suica_activate'] = $_POST['suica_activate'];
					$options['acting_settings']['jpayment']['bank_activate'] = $_POST['bank_activate'];
					$options['acting_settings']['jpayment']['send_url'] = $_POST['send_url'];

					if( '' == trim($_POST['aid']) )
						$mes .= '※店舗IDコードを入力して下さい<br />';
					if( isset($_POST['card_activate']) && 'on' == $_POST['card_activate'] && empty($_POST['card_jb']) )
						$mes .= '※ジョブタイプを指定して下さい<br />';

					if( '' == $mes ){
						$this->action_status = 'success';
						$this->action_message = __('options are updated','usces');
						$options['acting_settings']['jpayment']['activate'] = 'on';
						if( 'on' == $options['acting_settings']['jpayment']['card_activate'] ){
							$this->payment_structure['acting_jpayment_card'] = 'カード決済（J-Payment）';
						}else{
							unset($this->payment_structure['acting_jpayment_card']);
						}
						if( 'on' == $options['acting_settings']['jpayment']['conv_activate'] ){
							$this->payment_structure['acting_jpayment_conv'] = 'コンビニ決済（J-Payment）';
						}else{
							unset($this->payment_structure['acting_jpayment_conv']);
						}
						//if( 'on' == $options['acting_settings']['jpayment']['webm_activate'] ){
						//	$this->payment_structure['acting_jpayment_webm'] = 'WebMoney決済（J-Payment）';
						//}else{
						//	unset($this->payment_structure['acting_jpayment_webm']);
						//}
						//if( 'on' == $options['acting_settings']['jpayment']['bitc_activate'] ){
						//	$this->payment_structure['acting_jpayment_bitc'] = 'BitCash決済（J-Payment）';
						//}else{
						//	unset($this->payment_structure['acting_jpayment_bitc']);
						//}
						//if( 'on' == $options['acting_settings']['jpayment']['suica_activate'] ){
						//	$this->payment_structure['acting_jpayment_suica'] = 'モバイルSuica決済（J-Payment）';
						//}else{
						//	unset($this->payment_structure['acting_jpayment_suica']);
						//}
						if( 'on' == $options['acting_settings']['jpayment']['bank_activate'] ){
							$this->payment_structure['acting_jpayment_bank'] = 'バンクチェック決済（J-Payment）';
						}else{
							unset($this->payment_structure['acting_jpayment_bank']);
						}

					}else{
						$this->action_status = 'error';
						$this->action_message = __('データに不備が有ります', 'usces');
						$options['acting_settings']['jpayment']['activate'] = 'off';
						unset($this->payment_structure['acting_jpayment_card']);
						unset($this->payment_structure['acting_jpayment_conv']);
						//unset($this->payment_structure['acting_jpayment_webm']);
						//unset($this->payment_structure['acting_jpayment_bitc']);
						//unset($this->payment_structure['acting_jpayment_suica']);
						unset($this->payment_structure['acting_jpayment_bank']);
					}
					ksort($this->payment_structure);
					update_option('usces_payment_structure', $this->payment_structure);
					break;
//20101018ysk end
//20110208ysk start
				case 'paypal':
					unset( $options['acting_settings']['paypal'] );
					$options['acting_settings']['paypal']['ec_activate'] = $_POST['ec_activate'];
					$options['acting_settings']['paypal']['sandbox'] = $_POST['sandbox'];
					$options['acting_settings']['paypal']['user'] = $_POST['user'];
					$options['acting_settings']['paypal']['pwd'] = $_POST['pwd'];
					$options['acting_settings']['paypal']['signature'] = $_POST['signature'];
//20110412ysk start
					$options['acting_settings']['paypal']['continuation'] = $_POST['continuation'];
//20110412ysk end

					if( !isset($_POST['sandbox']) || empty($_POST['sandbox']) )
						$mes .= '※PayPalサーバーが不正です<br />';
					if( '' == trim($_POST['user']) )
						$mes .= '※APIユーザー名を入力して下さい<br />';
					if( '' == trim($_POST['pwd']) )
						$mes .= '※APIパスワードを入力して下さい<br />';
					if( '' == trim($_POST['signature']) )
						$mes .= '※署名を入力して下さい<br />';

					if( '' == $mes ){
						$this->action_status = 'success';
						$this->action_message = __('options are updated','usces');
						if($options['acting_settings']['paypal']['sandbox'] == 1) {
							$options['acting_settings']['paypal']['api_host'] = 'api-3t.sandbox.paypal.com';
							$options['acting_settings']['paypal']['api_endpoint'] = 'https://api-3t.sandbox.paypal.com/nvp';
							$options['acting_settings']['paypal']['paypal_url'] = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
						} else {
							$options['acting_settings']['paypal']['api_host'] = 'api-3t.paypal.com';
							$options['acting_settings']['paypal']['api_endpoint'] = 'https://api-3t.paypal.com/nvp';
							$options['acting_settings']['paypal']['paypal_url'] = 'https://www.paypal.com/cgi-bin/webscr';
						}
						$options['acting_settings']['paypal']['activate'] = 'on';
						if( 'on' == $options['acting_settings']['paypal']['ec_activate'] ){
							$this->payment_structure['acting_paypal_ec'] = 'PayPal決済';
						}else{
							unset($this->payment_structure['acting_paypal_ec']);
						}

					}else{
						$this->action_status = 'error';
						$this->action_message = __('データに不備が有ります', 'usces');
						$options['acting_settings']['paypal']['activate'] = 'off';
						unset($this->payment_structure['acting_paypal_ec']);
					}
					ksort($this->payment_structure);
					update_option('usces_payment_structure', $this->payment_structure);
					break;
//20110208ysk end
			}
			

			update_option('usces', $options);
		}
			
		
		$this->options = get_option('usces');
		require_once(USCES_PLUGIN_DIR . '/includes/admin_settlement.php');	
	}
	
	/********************************************************************************/
	function selected( $selected, $current) {
		if ( $selected == $current)
			echo ' selected="selected"';
	}
	/********************************************************************************/

	function get_request() {
		$host = $_SERVER['HTTP_HOST'];
		$uri = $_SERVER['REQUEST_URI'];
		$port = $_SERVER['REMOTE_PORT'];
		$scheme = ( $port == 443 ) ? 'https://' : 'http://';
		return $scheme . $host . $uri;
	}
	
	function redirect() {
	
		$redirect = '';

		$req = $_SERVER['QUERY_STRING'];
		$port = $_SERVER['SERVER_PORT'];
		
		$request = $this->get_request();
		
		$conjunction = ( empty($req) && (!strpos($request, USCES_CART_FOLDER, 1) && !strpos($request, USCES_MEMBER_FOLDER, 1)) ) ? '?' : '&';
		
		$sessid = $conjunction . 'uscesid=' . $this->get_uscesid();
	
		
		if( false === strpos($request, 'uscesid=') )
			$uri = $request . $sessid;
		else
			$uri = $request;
		

		if( $this->use_ssl ) {
		
			if ( '80' == $port && strpos($uri, USCES_CART_FOLDER, 1))
				$redirect = USCES_SSL_URL . '/?page_id=' . USCES_CART_NUMBER . $sessid;
		
			if ( '80' == $port && strpos($uri, USCES_MEMBER_FOLDER, 1))
				$redirect = USCES_SSL_URL . '/?page_id=' . USCES_MEMBER_NUMBER . $sessid;

			if ( '443' == $port && false === strpos($uri, 'wp-admin') && false === strpos($uri, 'wp-login.php') && false === strpos($uri, '?page_id=' . USCES_CART_NUMBER) && false === strpos($uri, '?page_id=' . USCES_MEMBER_NUMBER) && !strpos($uri, USCES_CART_FOLDER, 1) && !strpos($uri, USCES_MEMBER_FOLDER, 1) )
				$redirect = get_option('home');
		}

	
		if($redirect != '') {
			//wp_redirect($redirect);
			exit;
		}
	}

	function usces_session_start() {

		if(defined( 'USCES_KEY' )){
			session_name( USCES_KEY );
		}else{
			session_name();
		}
		if(isset($_GET['uscesid']) && ($_GET['uscesid'] != '')) {
			$sessid = $_GET['uscesid'];
			$sessid = $this->uscesdc($sessid);
			session_id($sessid);
		}
		//$timeout = 0;
		//$domain = $_SERVER['HTTP_HOST'];
		//@session_set_cookie_params($timeout, USCES_COOKIEPATH, $domain);
		@session_start();
		
	}
	
	function usces_cookie() {
		if( !isset($_SESSION['usces_cookieid']) ) {
			$cookie = $this->get_cookie();
			if( !isset($cookie['id']) || $cookie['id'] == '' ) {
				$values = array(
							'id' => md5(uniqid(rand(), true)),
							'name' => '',
							'rme' => ''
							);
				$this->set_cookie($values);
				$_SESSION['usces_cookieid'] = $values['id'];
				//$this->cnt_access('first');
			} else {
				$_SESSION['usces_cookieid'] = $cookie['id'];
				//$this->cnt_access();
			}
		}
	}
	function set_cookie($values){
		$value = serialize($values);
		$timeout = time()+7*86400;
		$domain = $_SERVER['HTTP_HOST'];
		$res = setcookie('usces_cookie', $value, $timeout, USCES_COOKIEPATH, $domain);
	}
	
	function get_cookie() {
		$values = unserialize(stripslashes($_COOKIE['usces_cookie']));
		return $values;
	}
	
	function get_access( $key, $type, $date ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "usces_access";

		$query = $wpdb->prepare("SELECT acc_value FROM $table_name WHERE acc_key = %s AND acc_type = %s AND acc_date = %s", $key, $type, $date);
		$value = $wpdb->get_var( $query );
		if( !$value ){
			$res = NULL;
		}else{
			$res = unserialize($value);
		}
		
		return $res;
	}
	
	function get_access_piriod( $key, $type, $startday, $endday ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "usces_access";
		//$wpdb->show_errors();
		$query = $wpdb->prepare("SELECT acc_type, acc_value, acc_date FROM $table_name WHERE acc_key = %s AND acc_type = %s AND (acc_date >= %s AND acc_date <= %s)", $key, $type, $startday, $endday);
		$res = $wpdb->get_results( $query, ARRAY_A );
		
		return $res;
	}
	
	function update_access( $array ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "usces_access";

		$query = $wpdb->prepare("SELECT ID FROM $table_name WHERE acc_key = %s AND acc_type = %s AND acc_date = %s", $array['acc_key'], $array['acc_type'], $array['acc_date']);
		$res = $wpdb->get_var( $query );
		//$wpdb->show_errors();
		if(empty($res)){
			$query = $wpdb->prepare("INSERT INTO $table_name (acc_key, acc_type, acc_value, acc_date) VALUES(%s, %s, %s, %s)", $array['acc_key'], $array['acc_type'], serialize($array['acc_value']), $array['acc_date']);
			$wpdb->query( $query );
		}else{
			$query = $wpdb->prepare("UPDATE $table_name SET acc_value = %s WHERE acc_key = %s AND acc_type = %s AND acc_date = %s", serialize($array['acc_value']), $array['acc_key'], $array['acc_type'], $array['acc_date']);
			$wpdb->query( $query );
		}
	}
	
	function get_uscesid( $flag = true) {

		$sessname = session_name();
		$sessid = isset($_REQUEST[$sessname]) ? $_REQUEST[$sessname] : session_id();
		$sessid = $this->uscescv($sessid, $flag);
		return $sessid;
	}
	
	function shop_head() {
		global $post;
		$this->item = $post;
		
		if( $this->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $this->is_inquiry_page($_SERVER['REQUEST_URI']) ){
			$css_url = USCES_FRONT_PLUGIN_URL . '/css/usces_cart.css';
		}else{
			$css_url = USCES_WP_CONTENT_URL . '/plugins/' . USCES_PLUGIN_FOLDER . '/css/usces_cart.css';
		}
		if( $this->is_cart_or_member_page($_SERVER['REQUEST_URI']) ){
			echo "	<meta name='robots' content='noindex,nofollow' />\n";
			wp_print_scripts( array( 'sack' )); 
		}
		echo '<link href="' . $css_url . '" rel="stylesheet" type="text/css" />';
		if( file_exists(get_stylesheet_directory() . '/usces_cart.css') ){
			echo '<link href="' . get_stylesheet_directory_uri() . '/usces_cart.css" rel="stylesheet" type="text/css" />';
		}
	}
	
	function shop_foot() {
		global $current_user;
		$item = $this->item;
		
		get_currentuserinfo();
		if( $this->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $this->is_inquiry_page($_SERVER['REQUEST_URI']) ){
			$javascript_url = USCES_FRONT_PLUGIN_URL . '/js/usces_cart.js';
		}else{
			$javascript_url = USCES_WP_CONTENT_URL . '/plugins/' . USCES_PLUGIN_FOLDER . '/js/usces_cart.js';
		}
		$this->member_name = ( is_user_logged_in() ) ? get_usermeta($current_user->ID,'first_name').get_usermeta($current_user->ID,'last_name') : '';
		$this->previous_url = isset($_SESSION['usces_previous_url']) ? $_SESSION['usces_previous_url'] : get_bloginfo('home');

//		usces_log('post_type : '.$item->post_mime_type, 'test.log');
//		usces_log('is_single : '.(is_single() ? 'true' : 'false'), 'test.log');

		if( $this->use_js ) : 

			$ioptkeys = $this->get_itemOptionKey( $item->ID );
			$mes_opts_str = "";
			$key_opts_str = "";
			$opt_means = "";
			$opt_esse = "";
			if($ioptkeys){
				foreach($ioptkeys as $key => $value){
					$optValues = $this->get_itemOptions( $value, $item->ID );
					if($optValues['means'] < 2){
						$mes_opts_str .= "'" . sprintf(__("Chose the %s", 'usces'), $value) . "',";
					}else{
						$mes_opts_str .= "'" . sprintf(__("Input the %s", 'usces'), $value) . "',";
					}
					$key_opts_str .= "'{$value}',";
					$opt_means .= "'{$optValues['means']}',";
					$opt_esse .= "'{$optValues['essential']}',";
				}
				$mes_opts_str = rtrim($mes_opts_str, ',');
				$key_opts_str = rtrim($key_opts_str, ',');
				$opt_means = rtrim($opt_means, ',');
				$opt_esse = rtrim($opt_esse, ',');
			}
			$itemRestriction = get_post_custom_values('_itemRestriction', $item->ID);
		
?>
		<script type='text/javascript'>
		/* <![CDATA[ */
			uscesL10n = {
				<?php echo apply_filters('usces_filter_uscesL10n', $uscesL10n, $item->ID); ?>
				'ajaxurl': "<?php echo USCES_SSL_URL_ADMIN; ?>/wp-admin/admin-ajax.php",
				'post_id': "<?php echo $item->ID; ?>",
				'cart_number': "<?php echo get_option('usces_cart_number'); ?>",
				'is_cart_row': <?php echo ( (0 < $this->cart->num_row()) ? 'true' : 'false'); ?>,
				'opt_esse': new Array( <?php echo $opt_esse; ?> ),
				'opt_means': new Array( <?php echo $opt_means; ?> ),
				'mes_opts': new Array( <?php echo $mes_opts_str; ?> ),
				'key_opts': new Array( <?php echo $key_opts_str; ?> ), 
				'previous_url': "<?php echo $this->previous_url; ?>", 
				'itemRestriction': "<?php echo $itemRestriction[0]; ?>"
			}
		/* ]]> */
		</script>
		<script type='text/javascript' src='<?php echo $javascript_url; ?>'></script>
		<?php endif; ?>
		<?php if( $this->use_js && (is_page(USCES_CART_NUMBER) || $this->is_cart_page($_SERVER['REQUEST_URI']) || ('item' == $item->post_mime_type)) ) : ?>
		<script type='text/javascript'>
		(function($) {
		uscesCart = {
			intoCart : function (post_id, sku) {
				
				var zaikonum = document.getElementById("zaikonum["+post_id+"]["+sku+"]").value;
				var zaiko = document.getElementById("zaiko["+post_id+"]["+sku+"]").value;
				if( (zaiko != '0' && zaiko != '1') ||  parseInt(zaikonum) == 0 ){
					alert('<?php _e('temporaly out of stock now', 'usces'); ?>');
					return false;
				}
				
				var mes = '';
				if(document.getElementById("quant["+post_id+"]["+sku+"]")){
					var quant = document.getElementById("quant["+post_id+"]["+sku+"]").value;
					if( quant == '0' || quant == '' || !(uscesCart.isNum(quant))){
						mes += "<?php _e('enter the correct amount', 'usces'); ?>\n";
					}
					var checknum = '';
					var checkmode = '';
					if( parseInt(uscesL10n.itemRestriction) <= parseInt(zaikonum) && uscesL10n.itemRestriction != '' && uscesL10n.itemRestriction != '0' && zaikonum != '' ) {
						checknum = uscesL10n.itemRestriction;
						checkmode ='rest';
					} else if( parseInt(uscesL10n.itemRestriction) > parseInt(zaikonum) && uscesL10n.itemRestriction != '' && uscesL10n.itemRestriction != '0' && zaikonum != '' ) {
						checknum = zaikonum;
						checkmode ='zaiko';
					} else if( (uscesL10n.itemRestriction == '' || uscesL10n.itemRestriction == '0') && zaikonum != '' ) {
						checknum = zaikonum;
						checkmode ='zaiko';
					} else if( uscesL10n.itemRestriction != '' && uscesL10n.itemRestriction != '0' && zaikonum == '' ) {
						checknum = uscesL10n.itemRestriction;
						checkmode ='rest';
					}
									
	
					if( parseInt(quant) > parseInt(checknum) && checknum != '' ){
							if(checkmode == 'rest'){
								mes += <?php _e("'This article is limited by '+checknum+' at a time.'", 'usces'); ?>+"\n";
							}else{
								mes += <?php _e("'Stock is remainder '+checknum+'.'", 'usces'); ?>+"\n";
							}
					}
				}
				for(i=0; i<uscesL10n.key_opts.length; i++){
					var skuob = document.getElementById("itemOption["+post_id+"]["+sku+"]["+uscesL10n.key_opts[i]+"]");
					if( uscesL10n.opt_esse[i] == '1' ){
						
						if( uscesL10n.opt_means[i] < 2 && skuob.value == '#NONE#' ){
							mes += uscesL10n.mes_opts[i]+"\n";
						}else if( uscesL10n.opt_means[i] >= 2 && skuob.value == '' ){
							mes += uscesL10n.mes_opts[i]+"\n";
						}
					}
				}
				
				<?php apply_filters( 'usces_filter_inCart_js_check', $item->ID ); ?>
				
				if( mes != '' ){
					alert( mes );
					return false;
				}else{
					<?php echo apply_filters('usces_filter_js_intoCart', "return true;\n", $item->ID, $this->itemsku['key']); ?>
				}
			},
			
			upCart : function () {
				
				var zaikoob = $("input[name*='zaikonum']");
				var quantob = $("input[name*='quant']");
				var postidob = $("input[name*='itempostid']");
				var skuob = $("input[name*='itemsku']");
				
				var zaikonum = '';
				var zaiko = '';
				var quant = '';
				var mes = '';
				var checknum = '';
				var post_id = '';
				var sku = '';
				var itemRestriction = '';
				
				var ct = zaikoob.length;
				for(var i=0; i< ct; i++){
					post_id = postidob[i].value;
					sku = skuob[i].value;
					itemRestriction = $("input[name='itemRestriction\[" + i + "\]']").val();
					zaikonum = $("input[name='zaikonum\[" + i + "\]\[" + post_id + "\]\[" + sku + "\]']").val();
			
					quant = $("input[name='quant\[" + i + "\]\[" + post_id + "\]\[" + sku + "\]']").val();
					if( $("input[name='quant\[" + i + "\]\[" + post_id + "\]\[" + sku + "\]']") ){
						if( quant == '0' || quant == '' || !(uscesCart.isNum(quant))){
							mes += <?php _e("'enter the correct amount for the No.' + (i+1) + ' item'", 'usces'); ?>+"\n";
						}
						var checknum = '';
						var checkmode = '';
						if( parseInt(itemRestriction) <= parseInt(zaikonum) && itemRestriction != '' && itemRestriction != '0' && zaikonum != '' ) {
							checknum = itemRestriction;
							checkmode ='rest';
						} else if( parseInt(itemRestriction) > parseInt(zaikonum) && itemRestriction != '' && itemRestriction != '0' && zaikonum != '' ) {
							checknum = zaikonum;
							checkmode ='zaiko';
						} else if( (itemRestriction == '' || itemRestriction == '0') && zaikonum != '' ) {
							checknum = zaikonum;
							checkmode ='zaiko';
						} else if( itemRestriction != '' && itemRestriction != '0' && zaikonum == '' ) {
							checknum = itemRestriction;
							checkmode ='rest';
						}
						if( parseInt(quant) > parseInt(checknum) && checknum != '' ){
							if(checkmode == 'rest'){
								mes += <?php _e("'This article is limited by '+checknum+' at a time for the No.' + (i+1) + ' item.'", 'usces'); ?>+"\n";
							}else{
								mes += <?php _e("'Stock of No.' + (i+1) + ' item is remainder '+checknum+'.'", 'usces'); ?>+"\n";
							}
						}
					}
				}
	
				if( mes != '' ){
					alert( mes );
					return false;
				}else{
					return true;
				}
			},
			
			cartNext : function () {
			
				var zaikoob = $("input[name*='zaikonum']");
				var quantob = $("input[name*='quant']");
				var postidob = $("input[name*='itempostid']");
				var skuob = $("input[name*='itemsku']");
				
				var zaikonum = '';
				var zaiko = '';
				var quant = '';
				var mes = '';
				var checknum = '';
				var post_id = '';
				var sku = '';
				var itemRestriction = '';
				
				var ct = zaikoob.length;
				for(var i=0; i< ct; i++){
					post_id = postidob[i].value;
					sku = skuob[i].value;
					itemRestriction = $("input[name='itemRestriction\[" + i + "\]']").val();
					zaikonum = $("input[name='zaikonum\[" + i + "\]\[" + post_id + "\]\[" + sku + "\]']").val();
			
					quant = $("input[name='quant\[" + i + "\]\[" + post_id + "\]\[" + sku + "\]']").val();
					if( $("input[name='quant\[" + i + "\]\[" + post_id + "\]\[" + sku + "\]']") ){
						if( quant == '0' || quant == '' || !(uscesCart.isNum(quant))){
							mes += <?php _e("'enter the correct amount for the No.' + (i+1) + ' item'", 'usces'); ?>+"\n";
						}
						var checknum = '';
						var checkmode = '';
						if( parseInt(itemRestriction) <= parseInt(zaikonum) && itemRestriction != '' && itemRestriction != '0' && zaikonum != '' ) {
							checknum = itemRestriction;
							checkmode ='rest';
						} else if( parseInt(itemRestriction) > parseInt(zaikonum) && itemRestriction != '' && itemRestriction != '0' && zaikonum != '' ) {
							checknum = zaikonum;
							checkmode ='zaiko';
						} else if( (itemRestriction == '' || itemRestriction == '0') && zaikonum != '' ) {
							checknum = zaikonum;
							checkmode ='zaiko';
						} else if( itemRestriction != '' && itemRestriction != '0' && zaikonum == '' ) {
							checknum = itemRestriction;
							checkmode ='rest';
						}

						if( parseInt(quant) > parseInt(checknum) && checknum != '' ){
							if(checkmode == 'rest'){
								mes += <?php _e("'This article is limited by '+checknum+' at a time for the No.' + (i+1) + ' item.'", 'usces'); ?>+"\n";
							}else{
								mes += <?php _e("'Stock of No.' + (i+1) + ' item is remainder '+checknum+'.'", 'usces'); ?>+"\n";
							}
						}
					}
				}
				if( mes != '' ){
					alert( mes );
					return false;
				}else{
					return true;
				}
			},
			
			previousCart : function () {
				location.href = uscesL10n.previous_url; 
			},
			
			settings: {
				url: uscesL10n.ajaxurl,
				type: 'POST',
				cache: false,
				success: function(data, dataType){
					//$("tbody#item-opt-list").html( data );
				}, 
				error: function(msg){
					//$("#ajax-response").html(msg);
				}
			},
			
			changeStates : function( country ) {
				var s = this.settings;
				s.data = "action=change_states_ajax&country=" + country;
				s.success = function(data, dataType){
					if( 'error' == data ){
						alert('error');
					}else{
						$("select#pref").html( data );
					}
				};
				s.error = function(msg){
					alert("error");
				};
				$.ajax( s );
				return false;
			},
			
			isNum : function (num) {
				if (num.match(/[^0-9]/g)) {
					return false;
				}
				return true;
			}
		};
		$("#country").change(function () {
			var country = $("#country option:selected").val();
			$("#newcharging_type option:selected").val()
			uscesCart.changeStates( country ); 
		});
			
		})(jQuery);
		</script>
		<?php endif; ?>
<?php
		usces_states_form_js();
	}
	
	function admin_head() {
		$payments_str = '';
		foreach ( (array)$this->options['payment_method'] as $id => $array ) {
			$payments_str .= "'" . $this->options['payment_method'][$id]['name'] . "': '" . $this->options['payment_method'][$id]['settlement'] . "', ";
		}
		$payments_str = rtrim($payments_str, ', ');
		$wcex_str = '';
		$wcex = usces_get_wcex();
		foreach ( (array)$wcex as $key => $values ) {
			$wcex_str .= "'" . $key . "-" . $values['version'] . "', ";
		}
		$wcex_str = rtrim($wcex_str, ', ');
		$theme = get_theme_data( get_stylesheet_directory().'/style.css' );
?>
		
		<link href="<?php echo USCES_PLUGIN_URL; ?>/css/admin_style.css" rel="stylesheet" type="text/css" media="all" />
		<script type='text/javascript'>
		/* <![CDATA[ */
			uscesL10n = {
				'requestFile': "<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php",
				'USCES_PLUGIN_URL': "<?php echo USCES_PLUGIN_URL; ?>",
				'version': "<?php echo USCES_VERSION; ?>", 
				'wcid': "<?php echo get_option('usces_wcid'); ?>", 
				'locale': '<?php echo get_locale(); ?>', 
				'cart_number': "<?php echo get_option('usces_cart_number'); ?>", 
				'purchase_limit': "<?php echo $this->options['purchase_limit']; ?>", 
				'point_rate': "<?php echo $this->options['point_rate']; ?>",
				'shipping_rule': "<?php echo $this->options['shipping_rule']; ?>", 
				'theme': "<?php echo $theme['Name'] . '-' . $theme['Version']; ?>", 
				'wcex': new Array( <?php echo $wcex_str; ?> ), 
				'now_loading': "<?php _e('now loading', 'usces'); ?>" 
			};
			uscesPayments = {<?php echo $payments_str; ?>};
		/* ]]> */
		</script>
		<script type='text/javascript' src='<?php echo USCES_PLUGIN_URL; ?>/js/usces_admin.js'></script>
<?php
		if($this->action_status == 'edit' || $this->action_status == 'editpost'){
?>
			<link rel='stylesheet' href='<?php echo get_option('siteurl'); ?>/wp-includes/js/thickbox/thickbox.css' type='text/css' media='all' />
<?php
		}
		if( isset($_REQUEST['page']) ){
			switch( $_REQUEST['page'] ){
				case 'usces_initial':
?>
					<script type='text/javascript'>
					/* <![CDATA[ */
						usces_ini = {
							'cod_type': "<?php if( 'change' == $this->options['cod_type'] ) {echo 'change';}else{echo 'fix';} ?>",
							'cod_type_fix': "<?php _e('Fixation C.O.D.', 'usces'); ?>",
							'cod_type_change': "<?php _e('Variable C.O.D.', 'usces'); ?>",
							'cod_unit': "<?php _e('dollars', 'usces'); ?>",
							'cod_failure': "<?php _e('failure in update', 'usces'); ?>",
							'cod_updated': "<?php _e('options are updated', 'usces'); ?>"
						};
/* ]]> */
					</script>
<?php
					break;
			}
		}
?>
<?php
		if( is_admin() && ('newpost' == $_GET['order_action'] 
							|| 'usces_ordernew' == $_GET['page'] 
							|| 'edit' == $_GET['order_action'] 
							|| 'editpost' == $_GET['order_action'] 
							|| 'edit' == $_GET['member_action'] 
							|| 'editpost' == $_GET['member_action']) ) :
			switch( $_GET['page'] ){
				case 'usces_ordernew':
				case 'usces_orderlist':
					$admin_page = 'order';
					break;
				case 'usces_memberlist':
					$admin_page = 'member';
					break;
			}
?>
		<script type='text/javascript'>
		jQuery(function($) {
		uscesForm = {
			settings: {
				url: uscesL10n.requestFile,
				type: 'POST',
				cache: false,
				success: function(data, dataType){
					//$("tbody#item-opt-list").html( data );
				}, 
				error: function(msg){
					//$("#ajax-response").html(msg);
				}
			},
			
			changeStates : function( country, type ) {
				var s = this.settings;
				s.data = "action=change_states_ajax&country=" + country;
				s.success = function(data, dataType){
					if( 'error' == data ){
						alert('error');
					}else{
						$("select#" + type + "_pref").html( data );
						if( customercountry == country && 'customer' == type ){
							$("#" + type + "_pref").attr({selectedIndex:customerstate});
						}else if( deliverycountry == country && 'delivery' == type ){
							$("#" + type + "_pref").attr({selectedIndex:deliverystate});
						}else if( customercountry == country && 'member' == type ){
							$("#" + type + "_pref").attr({selectedIndex:customerstate});
						}
					}
				};
				s.error = function(msg){
					alert("error");
				};
				$.ajax( s );
				return false;
			},
			
			isNum : function (num) {
				if (num.match(/[^0-9]/g)) {
					return false;
				}
				return true;
			}
		};
<?php
		if( 'order' == $admin_page ){
?>
		var customerstate = $("#customer_pref").get(0).selectedIndex;
		var customercountry = $("#customer_country").val();
		var deliverystate = $("#delivery_pref").get(0).selectedIndex;
		var deliverycountry = $("#delivery_country").val();
		
		$("#customer_country").change(function () {
			var country = $("#customer_country option:selected").val();
			uscesForm.changeStates( country, 'customer' ); 
		});
		$("#delivery_country").change(function () {
			var country = $("#delivery_country option:selected").val();
			uscesForm.changeStates( country, 'delivery' ); 
		});
<?php
		}else if( 'member' == $admin_page ){
?>
		var customerstate = $("#member_pref").get(0).selectedIndex;
		var customercountry = $("#member_country").val();
		var deliverystate = '';
		var deliverycountry = '';
		
		$("#member_country").change(function () {
			var country = $("#member_country option:selected").val();
			uscesForm.changeStates( country, 'member' ); 
		});
<?php
		}
?>
		});
		</script>
<?php
		endif;
}
	
	function main() {
		global $wpdb, $wp_locale, $wp_version, $post_ID;
		global $wp_query, $usces_action, $post, $action, $editing;
		
		update_option('usces_shipping_rule', apply_filters('usces_filter_shipping_rule', get_option('usces_shipping_rule')));
		$this->shipping_rule = get_option('usces_shipping_rule');


		if( isset($_POST) && 1 !== preg_match('/(?:plugin|theme)-editor\.php/', $_POST['_wp_http_referer']) ){
			$_POST = $this->stripslashes_deep_post($_POST);
		}
		
		$this->make_url();


		do_action('usces_main');
		$this->usces_cookie();
		$this->update_table();
		
//		if( 'customer' == $this->page ){
//			header("Pragma: private");
//			header("Cache-Control: private");
//		}else{
//			header("Pragma: no-cache");
//			header("Cache-Control: no-cache");
//		}
		
		
		//var_dump($_REQUEST);
		require_once(USCES_PLUGIN_DIR . '/classes/cart.class.php');
		$this->cart = new usces_cart();
		
		do_action('usces_after_cart_instant');
		
		if( isset($_REQUEST['page']) && $_REQUEST['page'] == 'usces_itemedit' && isset($_REQUEST['action']) && $_REQUEST['action'] == 'duplicate' ){
			$post_id = (int)$_GET['post'];
			$new_id = usces_item_dupricate($post_id);
			$ref = isset($_REQUEST['usces_referer']) ? urlencode($_REQUEST['usces_referer']) : '';
			$url = USCES_ADMIN_URL . '?page=usces_itemedit&action=edit&post=' . $new_id . '&usces_referer=' . $ref;
			wp_redirect($url);
			exit;
		}else if( isset($_REQUEST['page']) && $_REQUEST['page'] == 'usces_itemedit' && isset($_REQUEST['action']) && $_REQUEST['action'] == 'itemcsv' ){
			$res = usces_item_uploadcsv();
			$url = USCES_ADMIN_URL . '?page=usces_itemedit&usces_status=' . $res['status'] . '&usces_message=' . urlencode($res['message']);
			wp_redirect($url);
			exit;
		}
//20110208ysk start
		if('on' == $this->options['acting_settings']['paypal']['ec_activate']) {
			require_once(USCES_PLUGIN_DIR . '/classes/paymentPaypal.class.php');
			$this->paypal = new usces_paypal();
		}
//20110208ysk end
		
		$this->ad_controller();
		//$this->controller();
		

		
		if($_GET['page'] == 'usces_itemnew')
			$itemnew = 'new';
		
		wp_enqueue_script('jquery');
		
		if( isset($_REQUEST['page']) && ($_REQUEST['action'] == 'edit' || $itemnew == 'new' || $_REQUEST['action'] == 'editpost')) {
		
			if($_REQUEST['action'] != 'editpost' && $itemnew == 'new'){
				if ( version_compare($wp_version, '3.0-beta', '>') ){
					if ( !isset($_GET['post_type']) )
						$post_type = 'post';
					elseif ( in_array( $_GET['post_type'], get_post_types( array('public' => true ) ) ) )
						$post_type = $_GET['post_type'];
					else
						wp_die( __('Invalid post type') );
					$post_type_object = get_post_type_object($post_type);
					$editing = true;
//					if ( current_user_can($post_type_object->edit_type_cap) ) {
						$post = $this->get_default_post_to_edit30( $post_type, true );
						$post_ID = $post->ID;
//					}
					
				}else{
					$post = $this->get_default_post_to_edit();
				}
			}else{
				if ( version_compare($wp_version, '3.0-beta', '>') ){
					if ( isset($_GET['post']) )
						$post_id = (int) $_GET['post'];
					elseif ( isset($_POST['post_ID']) )
						$post_id = (int) $_POST['post_ID'];
					else
						$post_id = 0;
					$post_ID = $post_id;
					$post = null;
					$post_type_object = null;
					$post_type = null;
					if ( $post_id ) {
						$post = get_post($post_id);
						if ( $post ) {
							$post_type_object = get_post_type_object($post->post_type);
							if ( $post_type_object ) {
								$post_type = $post->post_type;
								$current_screen->post_type = $post->post_type;
								$current_screen->id = $current_screen->post_type;
							}
						}
					} elseif ( isset($_POST['post_type']) ) {
						$post_type_object = get_post_type_object($_POST['post_type']);
						if ( $post_type_object ) {
							$post_type = $post_type_object->name;
							$current_screen->post_type = $post_type;
							$current_screen->id = $current_screen->post_type;
						}
					}
					

//					$post = get_post( $post_id, OBJECT, 'edit' );
//					if ( $post->post_type == 'page' )
//						$post->page_template = get_post_meta( $id, '_wp_page_template', true );
						
				}else{
					if(isset($_GET['post'])){
						$post_ID =  (int) $_GET['post'];
						$post = get_post($post_ID);
					}else{
						$post_ID =  isset($_REQUEST['post_ID']) ? (int) $_REQUEST['post_ID'] : 0;
						if(!empty($post_ID))
							$post = get_post($post_ID);
					}
				}
//		global $wp_query, $usces_action, $post;

			}
			$editing = true;
			wp_enqueue_script('autosave');
			wp_enqueue_script('post');
			//if ( user_can_richedit() )
			wp_enqueue_script('editor');
			add_thickbox();
			wp_enqueue_script('media-upload');
			wp_enqueue_script('word-count');
			wp_enqueue_script( 'admin-comments' );
		
//			add_action( 'admin_head', 'wp_tiny_mce' );
			add_action( 'admin_print_footer_scripts', 'wp_tiny_mce', 25 );
			wp_enqueue_script('quicktags');

		}

		
		if( is_admin() && isset($_REQUEST['page']) ){
			switch( $_REQUEST['page'] ){
			
				case 'usces_initial':
					$js = USCES_FRONT_PLUGIN_URL.'/js/usces_initial.js';
					wp_enqueue_script('usces_initial.js', $js, array('jquery-ui-dialog'));
					break;
					
				case 'usces_settlement':
					wp_enqueue_script('jquery-ui-tabs', array('jquery-ui-core'));
					$jquery_cookieUrl = USCES_FRONT_PLUGIN_URL.'/js/jquery.cookie.js';
					wp_enqueue_script('jquery-cookie', $jquery_cookieUrl, array('jquery'), '1.0' );
//					$item_list_layoutUrl = USCES_FRONT_PLUGIN_URL.'/js/usces_dumy.js';
//					wp_enqueue_script('usces_dumy', $item_list_layoutUrl, array('jquery-ui-tabs'), '1.0' );
					break;
//20100809ysk start
				case 'usces_cart':
					wp_enqueue_script('jquery-ui-tabs', array('jquery-ui-core'));
					$jquery_cookieUrl = USCES_FRONT_PLUGIN_URL.'/js/jquery.cookie.js';
					wp_enqueue_script('jquery-cookie', $jquery_cookieUrl, array('jquery'), '1.0');
					break;
//20100809ysk end
//20100818ysk start
				case 'usces_member':
					wp_enqueue_script('jquery-ui-tabs', array('jquery-ui-core'));
					$jquery_cookieUrl = USCES_FRONT_PLUGIN_URL.'/js/jquery.cookie.js';
					wp_enqueue_script('jquery-cookie', $jquery_cookieUrl, array('jquery'), '1.0');
					break;
//20100818ysk end
//20100908ysk start
				case 'usces_orderlist':
				case 'usces_ordernew':
					wp_enqueue_script('jquery-ui-dialog');
					break;
				case 'usces_memberlist':
					wp_enqueue_script('jquery-ui-dialog');
					break;
//20100908ysk end
//20101111ysk start
				case 'usces_itemedit':
					wp_enqueue_script('jquery-ui-dialog');
					break;
//20101111ysk end
//20101208ysk start
				case 'usces_delivery':
					wp_enqueue_script('jquery-ui-tabs', array('jquery-ui-core'));
					$jquery_cookieUrl = USCES_FRONT_PLUGIN_URL.'/js/jquery.cookie.js';
					wp_enqueue_script('jquery-cookie', $jquery_cookieUrl, array('jquery'), '1.0');
					break;
//20101208ysk end
//20110331ysk start
				case 'usces_system':
					wp_enqueue_script('jquery-ui-tabs', array('jquery-ui-core'));
					$jquery_cookieUrl = USCES_FRONT_PLUGIN_URL.'/js/jquery.cookie.js';
					wp_enqueue_script('jquery-cookie', $jquery_cookieUrl, array('jquery'), '1.0');
					break;
//20110331ysk end
			}
		}

		if( isset($_REQUEST['order_action']) && $_REQUEST['order_action'] == 'pdfout' ){
			$this->get_current_member();
			$mid = $this->current_member['id'];
			$oid = $_GET['order_id'];
			if( !is_user_logged_in() && !$this->is_order($mid, $oid) )
				die('No permission');
			require_once( apply_filters('usces_filter_orderpdf_path', USCES_PLUGIN_DIR . '/includes/order_print.php') );
		}
		
	}
	
	function stripslashes_deep_post( $array ){
		$res = array();
		foreach( $array as $key => $value ){
			$key = stripslashes($key);
			if( is_array($value) ){
				$value = $this->stripslashes_deep_post( $value );
			}else{
				$value = stripslashes($value);
			}
			$res[$key] = $value;
		}
		return $res;
	}
	
	function make_url(){
	
		$permalink_structure = get_option('permalink_structure');
		if($this->use_ssl) {
			if( $permalink_structure ){
				$this->delim = '&';
				$home_perse = parse_url(get_option('home'));
				$home_path = $home_perse['host'].$home_perse['path'];
				$ssl_perse = parse_url($this->options['ssl_url']);
				$ssl_path = $ssl_perse['host'].$ssl_perse['path'];
				if( $home_perse['path'] != $ssl_perse['path'] ){
					define('USCES_CUSTOMER_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_CART_NUMBER . '&customerinfo=1&uscesid=' . $this->get_uscesid());
					define('USCES_CART_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_CART_NUMBER . '&uscesid=' . $this->get_uscesid());
					define('USCES_LOSTMEMBERPASSWORD_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid() . '&page=lostmemberpassword');
					define('USCES_NEWMEMBER_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid() . '&page=newmember');
					define('USCES_LOGIN_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid() . '&page=login');
					define('USCES_LOGOUT_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid() . '&page=logout');
					define('USCES_MEMBER_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid());
					define('USCES_INQUIRY_URL', $this->options['ssl_url'] . '/index.php?page_id=' . $this->options['inquiry_id'] . '&uscesid=' . $this->get_uscesid());
//20110208ysk start
					//define('USCES_PAYPAL_NOTIFY_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_CART_NUMBER . '&acting_return=paypal_ipn&uscesid=' . $this->get_uscesid(false));
					define('USCES_PAYPAL_NOTIFY_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_CART_NUMBER . '&acting=paypal_ipn&uscesid=' . $this->get_uscesid(false));
//20110208ysk end
				}else{
					$ssl_plink_cart = str_replace('http://','https://', str_replace( $home_path, $ssl_path, get_page_link(USCES_CART_NUMBER) ));
					$ssl_plink_member = str_replace('http://','https://', str_replace( $home_path, $ssl_path, get_page_link(USCES_MEMBER_NUMBER) ));
					$ssl_plink_inquiry = str_replace('http://','https://', str_replace( $home_path, $ssl_path, get_page_link($this->options['inquiry_id']) ));
					define('USCES_CUSTOMER_URL', $ssl_plink_cart . '?uscesid=' . $this->get_uscesid() . '&customerinfo=1');
					define('USCES_CART_URL', $ssl_plink_cart . '?uscesid=' . $this->get_uscesid());
					define('USCES_LOSTMEMBERPASSWORD_URL', $ssl_plink_member . '?uscesid=' . $this->get_uscesid() . '&page=lostmemberpassword');
					define('USCES_NEWMEMBER_URL', $ssl_plink_member  . '?uscesid=' . $this->get_uscesid(). '&page=newmember');
					define('USCES_LOGIN_URL', $ssl_plink_member . '?uscesid=' . $this->get_uscesid() . '&page=login');
					define('USCES_LOGOUT_URL', $ssl_plink_member . '?uscesid=' . $this->get_uscesid() . '&page=logout');
					define('USCES_MEMBER_URL', $ssl_plink_member . '?uscesid=' . $this->get_uscesid());
					define('USCES_INQUIRY_URL', $ssl_plink_inquiry . '?uscesid=' . $this->get_uscesid());
//20110208ysk start
					//define('USCES_PAYPAL_NOTIFY_URL', $ssl_plink_cart . '?acting_return=paypal_ipn&uscesid=' . $this->get_uscesid(false));
					define('USCES_PAYPAL_NOTIFY_URL', $ssl_plink_cart . '?acting=paypal_ipn&uscesid=' . $this->get_uscesid(false));
//20110208ysk end
				}
			}else{
				$this->delim = '&';
				define('USCES_CUSTOMER_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_CART_NUMBER . '&customerinfo=1&uscesid=' . $this->get_uscesid());
				define('USCES_CART_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_CART_NUMBER . '&uscesid=' . $this->get_uscesid());
				define('USCES_LOSTMEMBERPASSWORD_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid() . '&page=lostmemberpassword');
				define('USCES_NEWMEMBER_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid() . '&page=newmember');
				define('USCES_LOGIN_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid() . '&page=login');
				define('USCES_LOGOUT_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid() . '&page=logout');
				define('USCES_MEMBER_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid());
				define('USCES_INQUIRY_URL', $this->options['ssl_url'] . '/?page_id=' . $this->options['inquiry_id'] . '&uscesid=' . $this->get_uscesid());
//20110208ysk start
				//define('USCES_PAYPAL_NOTIFY_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_CART_NUMBER . '&acting_return=paypal_ipn&uscesid=' . $this->get_uscesid(false));
				define('USCES_PAYPAL_NOTIFY_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_CART_NUMBER . '&acting=paypal_ipn&uscesid=' . $this->get_uscesid(false));
//20110208ysk end
			}
			add_filter('home_url', array($this, 'usces_ssl_page_link'));
			add_filter('wp_get_attachment_url', array($this, 'usces_ssl_attachment_link'));
			add_filter('icon_dir_uri', array($this, 'usces_ssl_icon_dir_uri'));
			add_filter('stylesheet_directory_uri', array($this, 'usces_ssl_contents_link'));
			add_filter('template_directory_uri', array($this, 'usces_ssl_contents_link'));
			add_filter('script_loader_src', array($this, 'usces_ssl_script_link'));
			add_filter('style_loader_src', array($this, 'usces_ssl_script_link'));
		} else {
			if( $permalink_structure ){
				$this->delim = '?';
				define('USCES_CUSTOMER_URL', get_page_link(USCES_CART_NUMBER) . '?customerinfo=1');
				define('USCES_CART_URL', get_page_link(USCES_CART_NUMBER));
				define('USCES_LOSTMEMBERPASSWORD_URL', get_page_link(USCES_MEMBER_NUMBER) . '?page=lostmemberpassword');
				define('USCES_NEWMEMBER_URL', get_page_link(USCES_MEMBER_NUMBER) . '?page=newmember');
				define('USCES_LOGIN_URL', get_page_link(USCES_MEMBER_NUMBER) . '?page=login');
				define('USCES_LOGOUT_URL', get_page_link(USCES_MEMBER_NUMBER) . '?page=logout');
				define('USCES_MEMBER_URL', get_page_link(USCES_MEMBER_NUMBER));
				define('USCES_INQUIRY_URL', get_page_link($this->options['inquiry_id']));
//20110208ysk start
				//define('USCES_PAYPAL_NOTIFY_URL', get_page_link(USCES_CART_NUMBER) . '?acting_return=paypal_ipn&uscesid=' . $this->get_uscesid(false));
				define('USCES_PAYPAL_NOTIFY_URL', get_page_link(USCES_CART_NUMBER) . '?acting=paypal_ipn&uscesid=' . $this->get_uscesid(false));
//20110208ysk end
			}else{
				$this->delim = '&';
				define('USCES_CUSTOMER_URL', get_option('home') . '/?page_id=' . USCES_CART_NUMBER . '&customerinfo=1');
				define('USCES_CART_URL', get_option('home') . '/?page_id=' . USCES_CART_NUMBER);
				define('USCES_LOSTMEMBERPASSWORD_URL', get_option('home') . '/?page_id=' . USCES_MEMBER_NUMBER . '&page=lostmemberpassword');
				define('USCES_NEWMEMBER_URL', get_option('home') . '/?page_id=' . USCES_MEMBER_NUMBER . '&page=newmember');
				define('USCES_LOGIN_URL', get_option('home') . '/?page_id=' . USCES_MEMBER_NUMBER . '&page=login');
				define('USCES_LOGOUT_URL', get_option('home') . '/?page_id=' . USCES_MEMBER_NUMBER . '&page=logout');
				define('USCES_MEMBER_URL', get_option('home') . '/?page_id=' . USCES_MEMBER_NUMBER);
				define('USCES_INQUIRY_URL', get_option('home') . '/?page_id=' . $this->options['inquiry_id']);
//20110208ysk start
				//define('USCES_PAYPAL_NOTIFY_URL', get_option('home') . '/?page_id=' . USCES_CART_NUMBER . '&acting_return=paypal_ipn&uscesid=' . $this->get_uscesid(false));
				define('USCES_PAYPAL_NOTIFY_URL', get_option('home') . '/?page_id=' . USCES_CART_NUMBER . '&acting=paypal_ipn&uscesid=' . $this->get_uscesid(false));
//20110208ysk end
			}
		}
	}
	
	function regist_action(){
		usces_register_action('inCart', 'post', 'inCart', NULL, 'inCart');
		usces_register_action('upButton', 'post', 'upButton', NULL, 'upButton');
		usces_register_action('delButton', 'post', 'delButton', NULL, 'delButton');
		usces_register_action('backCart', 'post', 'backCart', NULL, 'backCart');
		usces_register_action('customerinfo', 'request', 'customerinfo', NULL, 'customerinfo');
		usces_register_action('backCustomer', 'post', 'backCustomer', NULL, 'backCustomer');
		usces_register_action('customerlogin', 'post', 'customerlogin', NULL, 'customerlogin');
		usces_register_action('reganddeliveryinfo', 'post', 'reganddeliveryinfo', NULL, 'reganddeliveryinfo');
		usces_register_action('deliveryinfo', 'post', 'deliveryinfo', NULL, 'deliveryinfo');
		usces_register_action('backDelivery', 'post', 'backDelivery', NULL, 'backDelivery');
		usces_register_action('confirm', 'request', 'confirm', NULL, 'confirm');
		usces_register_action('use_point', 'post', 'use_point', NULL, 'use_point');
		usces_register_action('backConfirm', 'post', 'backConfirm', NULL, 'backConfirm');
		usces_register_action('purchase', 'request', 'purchase', NULL, 'purchase');
		usces_register_action('acting_return', 'request', 'acting_return', NULL, 'acting_return');
		usces_register_action('settlement_epsilon', 'request', 'settlement', 'epsilon', 'settlement_epsilon');
		usces_register_action('inquiry_button', 'post', 'inquiry_button', NULL, 'inquiry_button');
		usces_register_action('member_login', 'request', 'member_login', NULL, 'member_login_page');
		usces_register_action('regmember', 'request', 'regmember', NULL, 'regmember');
		usces_register_action('editmember', 'request', 'editmember', NULL, 'editmember');
		usces_register_action('deletemember', 'request', 'deletemember', NULL, 'deletemember');
		usces_register_action('page_login', 'get', 'page', 'login', 'member_login_page');
		usces_register_action('page_logout', 'get', 'page', 'logout', 'page_logout');
		usces_register_action('page_lostmemberpassword', 'get', 'page', 'lostmemberpassword', 'page_lostmemberpassword');
		usces_register_action('lostpassword', 'request', 'lostpassword', NULL, 'lostpassword');
		usces_register_action('uscesmode_changepassword', 'request', 'uscesmode', 'changepassword', 'uscesmode_changepassword');
		usces_register_action('changepassword', 'request', 'changepassword', NULL, 'changepassword_page');
		usces_register_action('page_newmember', 'get', 'page', 'newmember', 'page_newmember');
		usces_register_action('usces_export', 'post', 'usces_export', NULL, 'usces_export');
		usces_register_action('usces_import', 'post', 'usces_import', NULL, 'usces_import');
		usces_register_action('page_search_item', 'get', 'page', 'search_item', 'page_search_item');
		usces_register_action('front_ajax', 'post', 'usces_ajax_action', NULL, 'front_ajax');
	}

	function ad_controller(){
		global $usces_action;
		ksort($usces_action);
		if($this->is_maintenance()){
			$this->maintenance();
		}else{
			$action_array = array('inCart', 'upButton', 'delButton', 'backCart', 'customerinfo', 'backCustomer', 
			'customerlogin', 'reganddeliveryinfo', 'deliveryinfo', 'backDelivery', 'confirm', 'use_point', 
			'backConfirm', 'purchase', 'acting_return', 'settlement_epsilon', 'inquiry_button', 'member_login', 
			'regmember', 'editmember', 'deletemember', 'page_login', 'page_logout', 'page_lostmemberpassword', 'lostpassword', 
			'uscesmode_changepassword', 'changepassword', 'page_newmember', 'usces_export', 'usces_import', 
			'page_search_item', 'front_ajax');
			$flg = 0;
			$res = true;
			foreach( $usces_action as $handle => $action ){
				extract($action);
				switch($type){
					case 'post':
						if( empty($value) ){
							if( isset($_POST[$key]) ){
								if(in_array($handle, $action_array)){
									$res = call_user_func(array($this, $function));
								}else{
									$res = call_user_func($function);
								}
								$flg = 1;
							}
						}else{
							if( isset($_POST[$key]) && $_POST[$key] == $value ){
								if(in_array($handle, $action_array)){
									$res = call_user_func(array($this, $function));
								}else{
									$res = call_user_func($function);
								}
								$flg = 1;
							}
						}
						break;
					case 'get':
						if( empty($value) ){
							if( isset($_GET[$key]) ){
								if(in_array($handle, $action_array)){
									$res = call_user_func(array($this, $function));
								}else{
									$res = call_user_func($function);
								}
								$flg = 1;
							}
						}else{
							if( isset($_GET[$key]) && $_GET[$key] == $value ){
								if(in_array($handle, $action_array)){
									$res = call_user_func(array($this, $function));
								}else{
									$res = call_user_func($function);
								}
								$flg = 1;
							}
						}
						break;
					case 'request':
						if( empty($value) ){
							if( isset($_REQUEST[$key]) ){
								if(in_array($handle, $action_array)){
									$res = call_user_func(array($this, $function));
								}else{
									$res = call_user_func($function);
								}
								$flg = 1;
							}
						}else{
							if( isset($_REQUEST[$key]) && $_REQUEST[$key] == $value ){
								if(in_array($handle, $action_array)){
									$res = call_user_func(array($this, $function));
								}else{
									$res = call_user_func($function);
								}
								$flg = 1;
							}
						}
						break;
				}
				if( ! $res ) break;
			}
			if( !$flg ) $this->default_page();
		}
	}

	//action function------------------------------------------------------------
	function front_ajax(){
		switch ($_POST['usces_ajax_action']){
			case 'change_states':
				change_states_ajax();
				break;
		}
		do_action('usces_front_ajax');
	}
	
	function maintenance(){
		$this->page = 'maintenance';
		add_action('the_post', array($this, 'action_cartFilter'));
	}

	function inCart(){
		global $wp_query;
		$this->page = 'cart';
		$this->incart_check();
		$this->cart->inCart();
		add_action('the_post', array($this, 'action_cartFilter'));
		add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_cart');
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function upButton(){
		global $wp_query;
		$this->page = 'cart';
		$this->cart->upCart();
		$this->error_message = $this->zaiko_check();
		add_action('the_post', array($this, 'action_cartFilter'));
		add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_cart');
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function delButton(){
		global $wp_query;
		$this->page = 'cart';
		$this->cart->del_row();
		add_action('the_post', array($this, 'action_cartFilter'));
		add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_cart');
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function backCart(){
		global $wp_query;
		$this->page = 'cart';
		add_action('the_post', array($this, 'action_cartFilter'));
		add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_cart');
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function customerinfo(){
		global $wp_query;
		if( false === $this->cart->num_row() ){
			header('location: ' . get_option('home'));
			exit;
		}
		$this->cart->entry();
		$this->error_message = $this->zaiko_check();
		if($this->error_message == ''){
			if($this->is_member_logged_in()){
//20100818ysk start
				//$this->page = 'delivery';
				$this->error_message = has_custom_customer_field_essential();
				$this->page = ($this->error_message == '') ? 'delivery' : 'customer';
//20100818ysk end
				add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_delivery');
			}else{
				$this->page = 'customer';
				add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_customer');
			}
		}else{
			$this->page = 'cart';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_cart');
		}
		if ( !$this->cart->is_order_condition() ) {
			$order_conditions = $this->get_condition();
			$this->cart->set_order_condition($order_conditions);
		}
		add_action('the_post', array($this, 'action_cartFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function backCustomer(){
		global $wp_query;
		if( false === $this->cart->num_row() ){
			header('location: ' . get_option('home'));
			exit;
		}
		$this->page = 'customer';
		add_action('the_post', array($this, 'action_cartFilter'));
		add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_customer');
		add_action('template_redirect', array($this, 'template_redirect'));
//		$this->cart->entry();
//		$this->error_message = $this->delivery_check();
//		$this->page = ($this->error_message == '') ? 'customer' : 'delivery';
	}
	
	function customerlogin(){
		global $wp_query;
		if( false === $this->cart->num_row() ){
			header('location: ' . get_option('home'));
			exit;
		}
//20100818ysk start
		//$this->cart->entry();
		//$this->page = ($this->member_login() == 'member') ? 'delivery' : 'customer';
		if($this->member_login() == 'member') {
			$this->cart->entry();
			$this->error_message = has_custom_customer_field_essential();
			if($this->error_message == ''){
				$this->page = 'delivery';
				add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_delivery');
			}else{
				$this->page = 'customer';
				add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_customer');
			}
		} else {
			$this->cart->entry();
			$this->page = 'customer';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_customer');
		}
//20100818ysk end
		add_action('the_post', array($this, 'action_cartFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function reganddeliveryinfo(){
		global $wp_query;
		if( false === $this->cart->num_row() ){
			header('location: ' . get_option('home'));
			exit;
		}
		$this->cart->entry();
		$_POST['member_regmode'] = 'newmemberfromcart';

		if( $this->regist_member() == 'newcompletion' ){
			$this->page = 'delivery';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_delivery');
		}else{
			$this->page = 'customer';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_customer');
		}
		add_action('the_post', array($this, 'action_cartFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function deliveryinfo(){
		global $wp_query;
		if( false === $this->cart->num_row() ){
			header('location: ' . get_option('home'));
			exit;
		}
		$this->cart->entry();
		$this->error_message = $this->customer_check();

		if( $this->error_message == '' ){
			$this->page = 'delivery';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_delivery');
		}else{
			$this->page = 'customer';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_customer');
		}
		add_action('the_post', array($this, 'action_cartFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function backDelivery(){
		global $wp_query;
		if( false === $this->cart->num_row() ){
			header('location: ' . get_option('home'));
			exit;
		}
		$this->page = 'delivery';
		add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_delivery');
		add_action('the_post', array($this, 'action_cartFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function confirm(){
		global $wp_query;
		if( false === $this->cart->num_row() ){
			header('location: ' . get_option('home'));
			exit;
		}

		$this->cart->entry();
		$this->set_reserve_pre_order_id();
		if(isset($_POST['confirm'])){
			$this->error_message = $this->delivery_check();
		}
		$this->page = ($this->error_message == '') ? 'confirm' : 'delivery';
		if( $this->error_message == '' ){
			$this->page = 'confirm';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_confirm');
		}else{
			$this->page = 'delivery';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_delivery');
		}
		add_action('the_post', array($this, 'action_cartFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function use_point(){
		global $wp_query;
		$this->cart->entry();
		$this->error_message = $this->point_check( $this->cart->get_entry() );
		$this->page = 'confirm';
		add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_confirm');
		add_action('the_post', array($this, 'action_cartFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function backConfirm(){
		global $wp_query;
		if( false === $this->cart->num_row() ){
			header('location: ' . get_option('home'));
			exit;
		}
		$this->page = 'confirm';
		add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_confirm');
		add_action('the_post', array($this, 'action_cartFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function purchase(){
		global $wp_query;
		if( false === $this->cart->num_row() ){
			header('location: ' . get_option('home'));
			exit;
		}

		if( !apply_filters('usces_purchase_check', true) ) return;
		
		do_action('usces_purchase_validate');
		$entry = $this->cart->get_entry();
		$this->error_message = $this->zaiko_check();
		if($this->error_message == '' && 0 < $this->cart->num_row()){
			$payments = $this->getPayments( $entry['order']['payment_name'] );
			if( substr($payments['settlement'], 0, 6) == 'acting' && $entry['order']['total_full_price'] > 0 ){
				$acting_flg = ( 'acting' == $payments['settlement'] ) ? $payments['module'] : $payments['settlement'];
				$query = '';
				foreach($_POST as $key => $value){
					if($key != 'purchase')
						$query .= '&' . $key . '=' . urlencode(maybe_serialize($value));
				}
				$actinc_status = $this->acting_processing($acting_flg, $query);
			}
			
			if($actinc_status == 'error'){
				$this->page = 'error';
				add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_error');
			}else{
				$res = $this->order_processing();
				if( 'ordercompletion' == $res ){
					$this->page = 'ordercompletion';
					add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_ordercompletion');
				}else{
					$this->page = 'error';
					add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_error');
				}
			}
		}else{
			$this->page = 'cart';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_cart');
		}
		add_action('the_post', array($this, 'action_cartFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function acting_return(){
		global $wp_query;
		$entry = $this->cart->get_entry();
		
//20110208ysk start
/*		if( 'paypal_ipn' == $_REQUEST['acting_return'] ){
			usces_log('paypal_ipn in ', 'acting_transaction.log');
			require_once($this->options['settlement_path'] . 'paypal.php');
			$ipn_res = paypal_ipn_check($usces_paypal_url);
			if( $ipn_res[0] === true ){
				$res = $this->order_processing( $ipn_res );
				if( 'ordercompletion' == $res ){
					$this->cart->crear_cart();
				}else{
					usces_log('paypal_ipn regorder error (acting_return) : '.print_r($entry, true), 'acting_transaction.log');
				}
			}
			exit;
		}*/
//20110208ysk end
		if( false === $this->cart->num_row() && ('paypal' != $_GET['acting'] && 1 !== (int)$_GET['acting_return']) ){
			header('location: ' . get_option('home'));
			exit;
		}
		
		$this->payment_results = usces_check_acting_return();

		if(  isset($this->payment_results[0]) && $this->payment_results[0] === 'duplicate' ){
		
			header('location: ' . get_option('home'));
			exit;
			
		}else if( isset($this->payment_results[0]) && $this->payment_results[0] ){//result OK
		
			if( ! $this->payment_results['reg_order'] ){//without Registration Order
				$this->page = 'ordercompletion';
				add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_ordercompletion');
				
			}else{
				$res = $this->order_processing( $this->payment_results );
				
				if( 'ordercompletion' == $res ){
					$this->page = 'ordercompletion';
					add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_ordercompletion');
				}else{
					$this->page = 'error';
					add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_error');
				}
			}
			
		}else{//result NG
			$this->page = 'error';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_error');
		}
		
		add_action('the_post', array($this, 'action_cartFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function settlement_epsilon(){
		global $wp_query;
		require_once($this->options['settlement_path'] . 'epsilon.php');	
	}
	
	function inquiry_button(){
		if( isset($_POST['inq_name']) && '' != trim($_POST['inq_name']) && isset($_POST['inq_mailaddress']) && is_email( trim($_POST['inq_mailaddress']) ) && '' != trim($_POST['inq_contents']) ){
			$res = $this->inquiry_processing();
		}else{
			$res = 'deficiency';
		}
		
		$this->page = $res;
	}
	
	function member_login_page(){
		global $wp_query;
		$res = $this->member_login();
		if( 'member' == $res ){
			$this->page = 'member';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_member');
		}elseif( 'login' == $res ){
			$this->page = 'login';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_login');
		}
		add_action('the_post', array($this, 'action_memberFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function regmember(){
		global $wp_query;
		$res = $this->regist_member();
		if( 'editmemberform' == $res ){
			$this->page = 'editmemberform';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_editmemberform');
		}elseif( 'newcompletion' == $res ){
			$this->page = 'newcompletion';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_newcompletion');
		}else{
			$this->page = $res;
		}
		add_action('the_post', array($this, 'action_memberFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function editmember(){
		global $wp_query;
		$res = $this->regist_member();
		if( 'editmemberform' == $res ){
			$this->page = 'editmemberform';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_editmemberform');
		}elseif( 'newcompletion' == $res ){
			$this->page = 'newcompletion';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_newcompletion');
		}else{
			$this->page = $res;
		}
		add_action('the_post', array($this, 'action_memberFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function deletemember(){
		$res = $this->delete_member();
		if( $res ){
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_deletemember');
			$this->member_logout();
		}else{
			$this->page = 'editmemberform';
			add_action('the_post', array($this, 'action_memberFilter'));
		}
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function page_logout(){
		global $wp_query;
		$this->member_logout();
		add_action('the_post', array($this, 'action_memberFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function page_lostmemberpassword(){
		global $wp_query;
		$this->page = 'lostmemberpassword';
		add_action('the_post', array($this, 'action_memberFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function lostpassword(){
		global $wp_query;
		$this->error_message = $this->lostpass_mailaddcheck();
		if ( $this->error_message != '' ) {
			$this->page = 'lostmemberpassword';
		} else {
			$res = $this->lostmail();
			$this->page = $res;//'lostcompletion';
		}
		add_action('the_post', array($this, 'action_memberFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function uscesmode_changepassword(){
		global $wp_query;
		$this->page = 'changepassword';
		add_action('the_post', array($this, 'action_memberFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function changepassword_page(){
		global $wp_query;
		$this->error_message = $this->changepass_check();
		if ( $this->error_message != '' ) {
			$this->page = 'changepassword';
		} else {
			$res = $this->changepassword();
			$this->page = $res;//'changepasscompletion';
		}
		add_action('the_post', array($this, 'action_memberFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function page_newmember(){
		global $wp_query;
		$this->page = 'newmemberform';
		add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_newmemberform');
		add_action('the_post', array($this, 'action_memberFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function usces_export(){
		$this->export();
	}
	
	function usces_import(){
		$this->import();
	}
	
	function page_search_item(){
		global $wp_query;
		$this->page = 'search_item';
		//add_action('template_redirect', array($this, 'action_search_item'));
		add_action('the_post', array($this, 'action_cartFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function default_page(){
		global $wp_query;
		add_action('the_post', array($this, 'goDefaultPage'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	//--------------------------------------------------------------------------------------
	
	
	function goDefaultPage(){
		global $post;
		
		if( $post->ID == USCES_CART_NUMBER ) {
		
			$this->page = 'cart';
			add_filter('the_content', array($this, 'filter_cartContent'),20);

		}else if( $post->ID == USCES_MEMBER_NUMBER ) {
		
			$this->page = 'member';
			add_filter('the_content', array($this, 'filter_memberContent'),20);
		
		}else if( !is_singular() ) {
			$this->page = 'wp_search';
			add_filter('the_excerpt', array($this, 'filter_cartContent'),20);
			add_filter('the_content', array($this, 'filter_cartContent'),20);

		}else{
			add_filter('the_content', array(&$this, 'filter_itemPage'));

		}
	}
	
	function template_redirect () {
		global $post, $usces_entries, $usces_carts, $usces_members;
		
		if( apply_filters('usces_action_template_redirect', false) ) return;

		if( is_single() && 'item' == $post->post_mime_type ) {
			if( file_exists(get_stylesheet_directory() . '/wc_templates/wc_item_single.php') ){
				include(get_stylesheet_directory() . '/wc_templates/wc_item_single.php');
				exit;
			}
		}elseif( ('search_item' == $_REQUEST['page'] || 'usces_search' == $_REQUEST['page']) && $this->is_cart_page($_SERVER['REQUEST_URI']) ){
			if( file_exists(get_stylesheet_directory() . '/wc_templates/wc_search_page.php') ){
				include(get_stylesheet_directory() . '/wc_templates/wc_search_page.php');
				exit;
			}
			
		}else if( $this->is_cart_page($_SERVER['REQUEST_URI']) ){
			switch( $this->page ){
				case 'customer':
					if( file_exists(get_stylesheet_directory() . '/wc_templates/cart/wc_customer_page.php') ){
						usces_get_entries();
						usces_get_member_regmode();
						include(get_stylesheet_directory() . '/wc_templates/cart/wc_customer_page.php');
						exit;
					}
				case 'delivery':
					if( file_exists(get_stylesheet_directory() . '/wc_templates/cart/wc_delivery_page.php') ){
						usces_get_entries();
						include(get_stylesheet_directory() . '/wc_templates/cart/wc_delivery_page.php');
						exit;
					}
				case 'confirm':
					if( file_exists(get_stylesheet_directory() . '/wc_templates/cart/wc_confirm_page.php') ){
						usces_get_entries();
						usces_get_carts();
						usces_get_members();
						include(get_stylesheet_directory() . '/wc_templates/cart/wc_confirm_page.php');
						exit;
					}
				case 'ordercompletion':
					if( file_exists(get_stylesheet_directory() . '/wc_templates/cart/wc_completion_page.php') ){
						usces_get_entries();
						usces_get_carts();
						include(get_stylesheet_directory() . '/wc_templates/cart/wc_completion_page.php');
						exit;
					}
				case 'error':
					if( file_exists(get_stylesheet_directory() . '/wc_templates/cart/wc_cart_error_page.php') ){
						include(get_stylesheet_directory() . '/wc_templates/cart/wc_cart_error_page.php');
						exit;
					}
				case 'cart':
				default:
					if( file_exists(get_stylesheet_directory() . '/wc_templates/cart/wc_cart_page.php') ){
						include(get_stylesheet_directory() . '/wc_templates/cart/wc_cart_page.php');
						exit;
					}
			
			}
		}else if($this->is_inquiry_page($_SERVER['REQUEST_URI']) ){

		}else if( $this->is_member_page($_SERVER['REQUEST_URI']) ){
			if($this->options['membersystem_state'] != 'activate') return;
			
			if( $this->is_member_logged_in() ) {
				$member_regmode = 'editmemberform';
				if( file_exists(get_stylesheet_directory() . '/wc_templates/member/wc_member_page.php') ){
					include(get_stylesheet_directory() . '/wc_templates/member/wc_member_page.php');
					exit;
				}
			
			} else {
			
				switch( $this->page ){
					case 'login':
						if( file_exists(get_stylesheet_directory() . '/wc_templates/member/wc_login_page.php') ){
							include(get_stylesheet_directory() . '/wc_templates/member/wc_login_page.php');
							exit;
						}
					case 'newmemberform':
						if( file_exists(get_stylesheet_directory() . '/wc_templates/member/wc_new_member_page.php') ){
							$member_regmode = 'newmemberform';
							include(get_stylesheet_directory() . '/wc_templates/member/wc_new_member_page.php');
							exit;
						}
					case 'lostmemberpassword':
						if( file_exists(get_stylesheet_directory() . '/wc_templates/member/wc_lostpassword_page.php') ){
							include(get_stylesheet_directory() . '/wc_templates/member/wc_lostpassword_page.php');
							exit;
						}
					case 'changepassword':
						if( file_exists(get_stylesheet_directory() . '/wc_templates/member/wc_completion_page.php') ){
							include(get_stylesheet_directory() . '/wc_templates/member/wc_completion_page.php');
							exit;
						}
					case 'newcompletion':
					case 'editcompletion':
					case 'lostcompletion':
					case 'changepasscompletion':
						if( file_exists(get_stylesheet_directory() . '/wc_templates/member/wc_member_completion_page.php') ){
							include(get_stylesheet_directory() . '/wc_templates/member/wc_member_completion_page.php');
							exit;
						}
					default:
						if( file_exists(get_stylesheet_directory() . '/wc_templates/member/wc_login_page.php') ){
							include(get_stylesheet_directory() . '/wc_templates/member/wc_login_page.php');
							exit;
						}
				}
			}

		}else{
//			remove_action('the_post', array(&$this, 'goDefaultPage'));
		}
	}
	
	function import() {
		$res = usces_import_xml();
		if ( $res === false ) :
			$this->action_status = 'error';
			//$this->action_message = __('Import was not completed.', 'usces');
		else :
			$this->action_status = 'success';
			$this->action_message = __('Import is cmpleted', 'usces');
		endif;
		
//		require_once(USCES_PLUGIN_DIR . '/includes/admin_backup.php');	
	}

	function export() {
		$filename = 'usces.' . substr(get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 0, 10) . '.xml';
	
		header('Content-Description: File Transfer');
		header("Content-Disposition: attachment; filename=$filename");
		header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);

		usces_export_xml();
		die();
	
	}


	function changepassword() {
		global $wpdb;

		if ( !isset($_SESSION['usces_lostmail']) ) :
			$this->error_message = __('Failed in update due to time-out', 'usces');
			return 'login';
		else :
		
			$member_table = $wpdb->prefix . "usces_member";
			
			$query = $wpdb->prepare("UPDATE $member_table SET mem_pass = %s WHERE mem_email = %s", 
							md5(trim($_POST['loginpass1'])), $_SESSION['usces_lostmail']);
			$res = $wpdb->query( $query );
			//$res = $wpdb->last_results;

			if ( $res === false ) :
				$this->error_message = __('Error: failure in updating password', 'usces');
				return 'login';
			else :
				return 'changepasscompletion';
			endif;

		endif;
	}
	
	function lostmail() {
	
		$_SESSION['usces_lostmail'] = trim($_POST['loginmail']);
		$id = session_id();
		$uri = USCES_MEMBER_URL . $this->delim . 'uscesmode=changepassword';
		$res = usces_lostmail($uri);
		return $res;
	
	}
	
	function regist_member() {
		global $wpdb;
		
		$member = $this->get_member();
		$mode = $_POST['member_regmode'];
		$member_table = $wpdb->prefix . "usces_member";
		$member_meta_table = $wpdb->prefix . "usces_member_meta";
			
		$error_mes = ( $_POST['member_regmode'] == 'newmemberfromcart' ) ? $this->member_check_fromcart() : $this->member_check();
		
		if ( $error_mes != '' ) {
		
			$this->error_message = $error_mes;
			return $mode;
			
		} elseif ( $_POST['member_regmode'] == 'editmemberform' ) {
	
		$query = $wpdb->prepare("SELECT mem_pass FROM $member_table WHERE ID = %d", $_POST['member_id']);
		$pass = $wpdb->get_var( $query );

		$password = ( !empty($_POST['member']['password1']) && trim($_POST['member']['password1']) == trim($_POST['member']['password2']) ) ? md5(trim($_POST['member']['password1'])) : $pass;
		$query = $wpdb->prepare("UPDATE $member_table SET 
				mem_pass = %s, mem_name1 = %s, mem_name2 = %s, mem_name3 = %s, mem_name4 = %s, 
				mem_zip = %s, mem_pref = %s, mem_address1 = %s, mem_address2 = %s, 
				mem_address3 = %s, mem_tel = %s, mem_fax = %s, mem_email = %s WHERE ID = %d", 
				$password, 
				trim($_POST['member']['name1']), 
				trim($_POST['member']['name2']), 
				trim($_POST['member']['name3']), 
				trim($_POST['member']['name4']), 
				trim($_POST['member']['zipcode']), 
				trim($_POST['member']['pref']), 
				trim($_POST['member']['address1']), 
				trim($_POST['member']['address2']), 
				trim($_POST['member']['address3']), 
				trim($_POST['member']['tel']), 
				trim($_POST['member']['fax']), 
				trim($_POST['member']['mailaddress1']), 
				$_POST['member_id'] 
				);
			$res = $wpdb->query( $query );
			if( $res !== false ){
				$this->set_member_meta_value('customer_country', $_POST['member']['country'], $_POST['member_id']);
//20100818ysk start
				$res = $this->reg_custom_member($_POST['member_id']);
//20100818ysk end
				$meta_keys = "'zeus_pcid', 'remise_pcid'";
				$query = $wpdb->prepare("DELETE FROM $member_meta_table WHERE member_id = %d AND meta_key IN( $meta_keys )", 
						$_POST['member_id'] 
						);
				$res = $wpdb->query( $query );
			}
			
			$this->get_current_member();
			return 'editmemberform';
			
		} elseif ( $_POST['member_regmode'] == 'newmemberform' ) {

			$query = $wpdb->prepare("SELECT ID FROM $member_table WHERE mem_email = %s", trim($_POST['member']['mailaddress1']));
			$id = $wpdb->get_var( $query );
			if ( !empty($id) ) {
				$this->error_message = __('This e-mail address has been already registered.', 'usces');
				return $mode;
			} else {
			
				$point = $this->options['start_point'];
				$pass = md5(trim($_POST['member']['password1']));
		    	$query = $wpdb->prepare("INSERT INTO $member_table 
						(mem_email, mem_pass, mem_status, mem_cookie, mem_point, 
						mem_name1, mem_name2, mem_name3, mem_name4, mem_zip, mem_pref, 
						mem_address1, mem_address2, mem_address3, mem_tel, mem_fax, 
						mem_delivery_flag, mem_delivery, mem_registered, mem_nicename) 
						VALUES (%s, %s, %d, %s, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s, %s, %s)", 
						trim($_POST['member']['mailaddress1']), 
						$pass, 
						0,
						"",
						$point,
						trim($_POST['member']['name1']), 
						trim($_POST['member']['name2']), 
						trim($_POST['member']['name3']), 
						trim($_POST['member']['name4']), 
						trim($_POST['member']['zipcode']), 
						trim($_POST['member']['pref']), 
						trim($_POST['member']['address1']), 
						trim($_POST['member']['address2']), 
						trim($_POST['member']['address3']), 
						trim($_POST['member']['tel']), 
						trim($_POST['member']['fax']), 
						'',
						'',
						get_date_from_gmt(gmdate('Y-m-d H:i:s', time())),
						'');
				$res = $wpdb->query( $query );
			
				//$_SESSION['usces_member']['ID'] = $wpdb->insert_id;
				//$this->get_current_member();
				if($res !== false) {
					$user = $_POST['member'];
					$user['ID'] = $wpdb->insert_id;
					$this->set_member_meta_value('customer_country', $_POST['member']['country'], $user['ID']);
//20100818ysk start
					$res = $this->reg_custom_member($wpdb->insert_id);
//20100818ysk end
					$mser = usces_send_regmembermail($user);
				}
				
				return 'newcompletion';
			}
			
		} elseif ( $_POST['member_regmode'] == 'newmemberfromcart' ) {

			$query = $wpdb->prepare("SELECT ID FROM $member_table WHERE mem_email = %s", trim($_POST['customer']['mailaddress1']));
			$id = $wpdb->get_var( $query );
			if ( !empty($id) ) {
				$this->error_message = __('This e-mail address has been already registered.', 'usces');
				return $mode;
			} else {
			
				$point = $this->options['start_point'];
				$pass = md5(trim($_POST['customer']['password1']));
		    	$query = $wpdb->prepare("INSERT INTO $member_table 
						(mem_email, mem_pass, mem_status, mem_cookie, mem_point, 
						mem_name1, mem_name2, mem_name3, mem_name4, mem_zip, mem_pref, 
						mem_address1, mem_address2, mem_address3, mem_tel, mem_fax, 
						mem_delivery_flag, mem_delivery, mem_registered, mem_nicename) 
						VALUES (%s, %s, %d, %s, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s, %s, %s)", 
						trim($_POST['customer']['mailaddress1']), 
						$pass, 
						0,
						"",
						$point,
						trim($_POST['customer']['name1']), 
						trim($_POST['customer']['name2']), 
						trim($_POST['customer']['name3']), 
						trim($_POST['customer']['name4']), 
						trim($_POST['customer']['zipcode']), 
						trim($_POST['customer']['pref']), 
						trim($_POST['customer']['address1']), 
						trim($_POST['customer']['address2']), 
						trim($_POST['customer']['address3']), 
						trim($_POST['customer']['tel']), 
						trim($_POST['customer']['fax']), 
						'',
						'',
						get_date_from_gmt(gmdate('Y-m-d H:i:s', time())),
						'');
				$res = $wpdb->query( $query );
				
				//$_SESSION['usces_member']['ID'] = $wpdb->insert_id;
				//$this->get_current_member();
				if($res !== false) {
					$this->set_member_meta_value('customer_country', $_POST['member']['country'], $wpdb->insert_id);
//20100818ysk start
					$res = $this->reg_custom_member($wpdb->insert_id);
//20100818ysk end
					//usces_send_regmembermail();
					$user = $_POST['customer'];
					$mser = usces_send_regmembermail($user);
					$_POST['loginmail'] = trim($_POST['customer']['mailaddress1']);
					$_POST['loginpass'] = trim($_POST['customer']['password1']);
					if( $this->member_login() == 'member' ){
						$_SESSION['usces_entry']['member_regmode'] = 'editmemberfromcart';
						return 'newcompletion';
					}
				}
				
				return false;
			}
		}
	}

	function delete_member() {
		if( ! $this->is_member_logged_in() )
				return false;
		$mem = $this->get_member();
		if( ! $mem['ID'] )
				return false;

		$res = usces_delete_memberdata( $mem['ID'] );

		return $res;
	}

	function is_member_logged_in( $id = false ) {
		if( $id === false ){
			if( isset($_SESSION['usces_member']['ID']) )
				return true;
			else
				return false;
		}else{
			if( isset($_SESSION['usces_member']['ID']) && $_SESSION['usces_member']['ID'] == $id )
				return true;
			else
				return false;
		}
	}

	function is_member($email) {
		global $wpdb;
		
		$member_table = $wpdb->prefix . "usces_member";
		$query = $wpdb->prepare("SELECT mem_email FROM $member_table WHERE mem_email = %s", $email);
		$member = $wpdb->get_row( $query, ARRAY_A );
		if ( empty($member) ) {
			return false;
		}else{
			return true;
		}
	}

	function member_login() {
		global $wpdb;
		
		$cookie = $this->get_cookie();

		
		if ( $cookie['rme'] == 'forever' && !isset($_POST['rememberme']) && !isset($_POST['loginmail'])) {
			$email = $cookie['name'];
			$member_table = $wpdb->prefix . "usces_member";
	
			$query = $wpdb->prepare("SELECT ID FROM $member_table WHERE mem_email = %s", $email);
			$id = $wpdb->get_var( $query );
			
			if ( !$id ) {
				$this->current_member['email'] = htmlspecialchars($email);
				$this->error_message = __('<b>Error:</b> E-mail address is not correct.', 'usces');
				return 'login';
			} else {
				$query = $wpdb->prepare("SELECT * FROM $member_table WHERE mem_email = %s", $email);
				$member = $wpdb->get_row( $query, ARRAY_A );
				if ( empty($member) ) {
					$this->current_member['email'] = htmlspecialchars($email);
					$this->error_message = __('<b>Error:</b> Password is not correct.', 'usces');
					return 'login';
				} else {
					$_SESSION['usces_member']['ID'] = $member['ID'];
					$_SESSION['usces_member']['mailaddress1'] = $member['mem_email'];
					$_SESSION['usces_member']['mailaddress2'] = $member['mem_email'];
					$_SESSION['usces_member']['point'] = $member['mem_point'];
					$_SESSION['usces_member']['name1'] = $member['mem_name1'];
					$_SESSION['usces_member']['name2'] = $member['mem_name2'];
					$_SESSION['usces_member']['name3'] = $member['mem_name3'];
					$_SESSION['usces_member']['name4'] = $member['mem_name4'];
					$_SESSION['usces_member']['zipcode'] = $member['mem_zip'];
					$_SESSION['usces_member']['pref'] = $member['mem_pref'];
					$_SESSION['usces_member']['address1'] = $member['mem_address1'];
					$_SESSION['usces_member']['address2'] = $member['mem_address2'];
					$_SESSION['usces_member']['address3'] = $member['mem_address3'];
					$_SESSION['usces_member']['tel'] = $member['mem_tel'];
					$_SESSION['usces_member']['fax'] = $member['mem_fax'];
					$_SESSION['usces_member']['delivery_flag'] = $member['mem_delivery_flag'];
					$_SESSION['usces_member']['delivery'] = !empty($member['mem_delivery']) ? unserialize($member['mem_delivery']) : '';
					$_SESSION['usces_member']['registered'] = $member['mem_registered'];
					$_SESSION['usces_member']['nicename'] = $member['mem_nicename'];
					$_SESSION['usces_member']['country'] = $this->get_member_meta_value('customer_country', $member['ID']);
//20100818ysk start
					$this->set_session_custom_member($member['ID']);
//20100818ysk end
					$this->get_current_member();
					
					return 'member';
				}
			}
		} else if ( $_POST['loginmail'] == '' && $_POST['loginpass'] == '' && $cookie['rme'] != 'forever' ) {
			return 'login';
		} else if ( $_POST['loginpass'] == '' && $cookie['rme'] != 'forever' ) {
			$this->current_member['email'] = trim($_POST['loginmail']);
			$this->error_message = __('<b>Error:</b> Enter the password.', 'usces');
			return 'login';
		} else {
			$email = trim($_POST['loginmail']);
			$pass = md5(trim($_POST['loginpass']));
			$member_table = $wpdb->prefix . "usces_member";
	
			$query = $wpdb->prepare("SELECT ID FROM $member_table WHERE mem_email = %s", $email);
			$id = $wpdb->get_var( $query );
			
			if ( !$id ) {
				$this->current_member['email'] = htmlspecialchars($email);
				$this->error_message = __('<b>Error:</b> E-mail address is not correct.', 'usces');
				return 'login';
			} else {
				$query = $wpdb->prepare("SELECT * FROM $member_table WHERE mem_email = %s AND mem_pass = %s", $email, $pass);
				$member = $wpdb->get_row( $query, ARRAY_A );
				if ( empty($member) ) {
					$this->current_member['email'] = htmlspecialchars($email);
					$this->error_message = __('<b>Error:</b> Password is not correct.', 'usces');
					return 'login';
				} else {
					$_SESSION['usces_member']['ID'] = $member['ID'];
					$_SESSION['usces_member']['mailaddress1'] = $member['mem_email'];
					$_SESSION['usces_member']['mailaddress2'] = $member['mem_email'];
					$_SESSION['usces_member']['point'] = $member['mem_point'];
					$_SESSION['usces_member']['name1'] = $member['mem_name1'];
					$_SESSION['usces_member']['name2'] = $member['mem_name2'];
					$_SESSION['usces_member']['name3'] = $member['mem_name3'];
					$_SESSION['usces_member']['name4'] = $member['mem_name4'];
					$_SESSION['usces_member']['zipcode'] = $member['mem_zip'];
					$_SESSION['usces_member']['pref'] = $member['mem_pref'];
					$_SESSION['usces_member']['address1'] = $member['mem_address1'];
					$_SESSION['usces_member']['address2'] = $member['mem_address2'];
					$_SESSION['usces_member']['address3'] = $member['mem_address3'];
					$_SESSION['usces_member']['tel'] = $member['mem_tel'];
					$_SESSION['usces_member']['fax'] = $member['mem_fax'];
					$_SESSION['usces_member']['delivery_flag'] = $member['mem_delivery_flag'];
					$_SESSION['usces_member']['delivery'] = !empty($member['mem_delivery']) ? unserialize($member['mem_delivery']) : '';
					$_SESSION['usces_member']['registered'] = $member['mem_registered'];
					$_SESSION['usces_member']['nicename'] = $member['mem_nicename'];
					$_SESSION['usces_member']['country'] = $this->get_member_meta_value('customer_country', $member['ID']);
//20100818ysk start
					$this->set_session_custom_member($member['ID']);
//20100818ysk end
					$this->get_current_member();
					
					if( isset($_POST['rememberme']) ){
						$cookie['name'] = $email;
						$cookie['rme'] = 'forever';
						$this->set_cookie($cookie);
					}else{
						$cookie['name'] = '';
						$cookie['rme'] = '';
						$this->set_cookie($cookie);
					}
					return 'member';
				}
			}
		}
	}

	function member_just_login($email, $pass) {
		global $wpdb;
		$pass = md5($pass);
		$member_table = $wpdb->prefix . "usces_member";

		$query = $wpdb->prepare("SELECT * FROM $member_table WHERE mem_email = %s AND mem_pass = %s", $email, $pass);
		$member = $wpdb->get_row( $query, ARRAY_A );
		if ( empty($member) ) {
			$this->current_member['email'] = htmlspecialchars($email);
			$this->error_message = __('<b>Error:</b> Password is not correct.', 'usces');
			return 'login';
		} else {
			$_SESSION['usces_member']['ID'] = $member['ID'];
			$_SESSION['usces_member']['mailaddress1'] = $member['mem_email'];
			$_SESSION['usces_member']['mailaddress2'] = $member['mem_email'];
			$_SESSION['usces_member']['point'] = $member['mem_point'];
			$_SESSION['usces_member']['name1'] = $member['mem_name1'];
			$_SESSION['usces_member']['name2'] = $member['mem_name2'];
			$_SESSION['usces_member']['name3'] = $member['mem_name3'];
			$_SESSION['usces_member']['name4'] = $member['mem_name4'];
			$_SESSION['usces_member']['zipcode'] = $member['mem_zip'];
			$_SESSION['usces_member']['pref'] = $member['mem_pref'];
			$_SESSION['usces_member']['address1'] = $member['mem_address1'];
			$_SESSION['usces_member']['address2'] = $member['mem_address2'];
			$_SESSION['usces_member']['address3'] = $member['mem_address3'];
			$_SESSION['usces_member']['tel'] = $member['mem_tel'];
			$_SESSION['usces_member']['fax'] = $member['mem_fax'];
			$_SESSION['usces_member']['delivery_flag'] = $member['mem_delivery_flag'];
			$_SESSION['usces_member']['delivery'] = !empty($member['mem_delivery']) ? unserialize($member['mem_delivery']) : '';
			$_SESSION['usces_member']['registered'] = $member['mem_registered'];
			$_SESSION['usces_member']['nicename'] = $member['mem_nicename'];
			$_SESSION['usces_member']['country'] = $this->get_member_meta_value('customer_country', $member['ID']);
//20100818ysk start
			$this->set_session_custom_member($member['ID']);
//20100818ysk end
			$this->get_current_member();
			
//			$cookie = $this->get_cookie();
//			if(isset($_POST['rememberme']) && $cookie){
//				$cookie['name'] = $email;
//				$cookie['pass'] = trim($_POST['loginpass']);
//				$this->set_cookie($cookie);
//			}else{
//				$cookie['name'] = '';
//				$cookie['pass'] = '';
//				$this->set_cookie($cookie);
//			}
			return 'member';
		}
	}

	function member_logout() {
		unset($_SESSION['usces_member'], $_SESSION['usces_entry']);
		do_action('usces_action_member_logout');
		wp_redirect(get_option('home'));
		exit;
	}
	
	function get_current_member() {
		
		if ( isset($_SESSION['usces_member']['ID']) ) {
			$this->current_member['id'] = $_SESSION['usces_member']['ID'];
			$this->current_member['name'] = $_SESSION['usces_member']['name1'] . ' ' . $_SESSION['usces_member']['name2'];
		} else {
			$this->current_member['id'] = 0;
			$this->current_member['name'] = __('guest', 'usces');
		}
	}

	function get_member() {
		foreach ( $_SESSION['usces_member'] as $key => $value ) {
//20100818ysk start
			if(is_array($_SESSION['usces_member'][$key])) 
				$res[$key] = stripslashes_deep($value);
			else
//20100818ysk end
				$res[$key] = stripslashes($value);
		}
		return $res;
	}

	function get_member_info( $mid ) {
		global $wpdb;
		
		if( !current_user_can('activate_plugins') ) return array();
		
		$table = $wpdb->prefix . "usces_member";
		$query = $wpdb->prepare("SELECT * FROM $table WHERE ID = %d", $mid);
		$datas = $wpdb->get_results( $query, ARRAY_A );
		$infos = $datas[0];
		
		$table = $wpdb->prefix . "usces_member_meta";
		$query = $wpdb->prepare("SELECT meta_key, meta_value FROM $table WHERE member_id = %d", $mid);
		$metas = $wpdb->get_results( $query, ARRAY_A );
		
		foreach( $metas as $meta ){
			$infos[$meta['meta_key']] = maybe_unserialize($meta['meta_value']);
		}
		return $infos;
	}

	function is_order($mid, $oid) {
		global $wpdb;
		
		$table = $wpdb->prefix . "usces_order";
		$query = $wpdb->prepare("SELECT ID FROM $table WHERE ID = %d AND mem_id = %d", $oid, $mid);
		$mem_id = $wpdb->get_var( $query );
		if ( empty($mem_id) ) {
			return false;
		}else{
			return true;
		}
	}

	function is_purchased_item($mid, $post_id) {
		global $wpdb;
		$res = false;
		
		$history = $this->get_member_history($mid);
		foreach ( $history as $umhs ) {
			$cart = $umhs['cart'];
			$status = $umhs['order_status'];
			for($i=0; $i<count($cart); $i++) { 
				$cart_row = $cart[$i];
				if($cart_row['post_id'] == $post_id && ('noreceipt' != $status && 'pending' != $status) ){
					$res = true;
					break 2;
				}elseif($cart_row['post_id'] == $post_id && ('noreceipt' == $status || 'pending' == $status) ){
					$res = 'noreceipt';
					break 2;
				}
			}
		
		}
			return $res;
	}
	
	function get_order_data($order_id, $mode = '' ) {
		global $wpdb, $usces;
		$order_table = $wpdb->prefix . "usces_order";
	
		$query = $wpdb->prepare("SELECT * FROM $order_table WHERE ID = %d", $order_id);

		if( 'direct' == $mode ){
			$value = $wpdb->get_row( $query, ARRAY_A );
			return $value;
		}

		$value = $wpdb->get_row( $query );
	
		if( $value == NULL ) {
			return false;
		}else{
			$res =array();
		}
		if(strpos($value->order_status, 'cancel') !== false || strpos($value->order_status, 'estimate') !== false){
			return false;
		}
		
		$res = array(
					'ID' => $value->ID,
					'cart' => unserialize($value->order_cart),
					'condition' => unserialize($value->order_condition),
					'getpoint' => $value->order_getpoint,
					'usedpoint' => $value->order_usedpoint,
					'discount' => $value->order_discount,
					'payment_name' => $value->order_payment_name,
					'shipping_charge' => $value->order_shipping_charge,
					'cod_fee' => $value->order_cod_fee,
					'tax' => $value->order_tax,
					'end_price' => $value->order_item_total_price - ($value->order_getpoint*$usces->options['system']['pointreduction']) - $value->order_discount + $value->order_shipping_charge + $value->order_cod_fee + $value->order_tax,
					'status' => $value->order_tax,
					'date' => mysql2date(__('Y/m/d'), $value->order_date),
					'modified' => mysql2date(__('Y/m/d'), $value->order_modified)
					);

		return $res;
	}

	function get_orderIDs_by_postID($mem_id, $post_id) {
		global $wpdb;
		$order_table = $wpdb->prefix . "usces_order";
	
		$query = $wpdb->prepare("SELECT ID, order_cart, order_status FROM $order_table WHERE mem_id = %d ORDER BY order_modified DESC, order_date DESC", $mem_id);
		$rows = $wpdb->get_query( $query, ARRAY_A );
	
		if( $value == NULL ) {
			return false;
		}else{
			foreach($rows as $row){
				if(strpos($row['order_status'], 'cancel') !== false || strpos($row['order_status'], 'estimate') !== false){
					continue;
				}else{
					$carts = unserialize($row['order_cart']);
					foreach($carts as $cart){
						if( $post_id == $cart['post_id'] ){
							$res[] = $row['ID'];
							break;
						}
					}
				}
			}
		}
		return $res;
	}

	function incart_check() {
		$mes = array();
		
		$ids = array_keys($_POST['inCart']);
		$post_id = $ids[0];
		$skus = array_keys($_POST['inCart'][$post_id]);
		$sku = $skus[0];
		$quant = isset($_POST['quant'][$post_id][$sku]) ? (int)$_POST['quant'][$post_id][$sku] : 1;
		$stock = $this->getItemZaikoNum($post_id, $sku);
		$zaiko_id = (int)$this->getItemZaikoStatusId($post_id, $sku);
		$itemRestriction = get_post_custom_values('_itemRestriction', $post_id);

		if( 1 > $quant ){
			$mes[$post_id][$sku] = __('enter the correct amount', 'usces') . "<br />";
		}else if( $quant > (int)$itemRestriction[0] && '' != $itemRestriction[0] && '0' != $itemRestriction[0] ){
			$mes[$post_id][$sku] = sprintf(__("This article is limited by %d at a time.", 'usces'), $itemRestriction[0]) . "<br />";
		}else if( $quant > (int)$stock && '' != $stock ){
			$mes[$post_id][$sku] = __('Sorry, stock is insufficient.', 'usces') . ' ' . __('Current stock', 'usces') . $stock . "<br />";
		}else if( 1 < $zaiko_id ){
			$mes[$post_id][$sku] = __('Sorry, this item is sold out.', 'usces') . "<br />";
		}
		
		
		
		$ioptkeys = $this->get_itemOptionKey( $post_id );
		//if($ioptkeys && isset($_POST['itemOption'][$post_id][$sku])){
		if($ioptkeys){
			foreach($ioptkeys as $key => $value){
				$optValues = $this->get_itemOptions( $value, $post_id );
				if( 2 > $optValues['means'] ){ //case of select
					if( $optValues['essential'] && '#NONE#' == $_POST['itemOption'][$post_id][$sku][$value] ){
						$mes[$post_id][$sku] .= sprintf(__("Chose the %s", 'usces'), $value) . "<br />";
					}
				}else{ //case of text
					if( $optValues['essential'] && '' == trim($_POST['itemOption'][$post_id][$sku][$value]) ){
						$mes[$post_id][$sku] .= sprintf(__("Input the %s", 'usces'), $value) . "<br />";
					}
				}
			}
		}
		foreach( (array)$mes[$post_id] as $skukey => $skuvalue ){
			$mes[$post_id][$skukey] = rtrim($skuvalue, "<br />");
		}
		
		if( !empty($mes) ){
			$_SESSION['usces_singleitem']['itemOption'] = $_POST['itemOption'];
			$_SESSION['usces_singleitem']['quant'] = $_POST['quant'];
			$_SESSION['usces_singleitem']['error_message'] = $mes;
			$parse_url = parse_url(get_bloginfo('home'));
			header('location: ' . $parse_url['scheme'] . '://' . $parse_url['host'] . $_POST['usces_referer'] . '#cart_button');
			exit;
		}
	}
	
	function zaiko_check() {
		$mes = '';
		$cart = $this->cart->get_cart();

		for($i=0; $i<count($cart); $i++) { 
			$cart_row = $cart[$i];
			$post_id = $cart_row['post_id'];
			$sku = $cart_row['sku'];
			
			$quant = ( isset($_POST['quant']) ) ? trim($_POST['quant'][$i][$post_id][$sku]) : $cart_row['quantity'];
			//$zaiko_status = $this->getItemZaiko($post_id, $sku);
			$zaiko_id = (int)$this->getItemZaikoStatusId($post_id, $sku);
			$stock = $this->getItemZaikoNum($post_id, $sku);
			$itemRestriction = get_post_custom_values('_itemRestriction', $post_id);
			
			//$red = (in_array($zaiko_status, array(__('Sold Out', 'usces'), __('Out Of Stock', 'usces'), __('Out of print', 'usces')))) ? 'red' : '';

			if( 1 > (int)$quant ){
				$mes .= sprintf(__("Enter the correct amount for the No.%d item.", 'usces'), ($i+1)) . "<br />";
			}else if( 1 < $zaiko_id || (0 == $stock && '' != $stock) ){
				$mes .= sprintf(__('Sorry, No.%d item is sold out.', 'usces'), ($i+1)) . "<br />";
			}else if( $quant > (int)$itemRestriction[0] && '' != $itemRestriction[0] && '0' != $itemRestriction[0] ){
				$mes .= sprintf(__('This article is limited by %1$d at a time for the No.%2$d item.', 'usces'), $itemRestriction[0], ($i+1)) . "<br />";
			}else if( $quant > (int)$stock && '' != $stock ){
				$mes .= sprintf(__('Stock of No.%1$d item is remainder %2$d.', 'usces'), ($i+1), $stock) . "<br />";
			}
		}
		return $mes;	
	}
	
	function member_check() {
		$mes = '';
		foreach ( $_POST['member'] as $key => $vlue ) {
			$_SESSION['usces_member'][$key] = trim($vlue);
		}
		if ( $_POST['member_regmode'] == 'editmemberform' ) {
			if ( (trim($_POST['member']['password1']) != '' || trim($_POST['member']['password2']) != '') && trim($_POST['member']['password1']) != trim($_POST['member']['password2']) )
				$mes .= __('Password is not correct.', 'usces') . "<br />";
			if ( !is_email($_POST['member']['mailaddress1']) || trim($_POST['member']['mailaddress1']) == '' )
				$mes .= __('e-mail address is not correct', 'usces') . "<br />";
				
		} else {
			if ( trim($_POST['member']['password1']) == '' || trim($_POST['member']['password2']) == '' || trim($_POST['member']['password1']) != trim($_POST['member']['password2']) )
				$mes .= __('Password is not correct.', 'usces') . "<br />";
			if ( !is_email($_POST['member']['mailaddress1']) || trim($_POST['member']['mailaddress1']) == '' || trim($_POST['member']['mailaddress2']) == '' || trim($_POST['member']['mailaddress1']) != trim($_POST['member']['mailaddress2']) )
				$mes .= __('e-mail address is not correct', 'usces') . "<br />";
			
		}
		if ( trim($_POST["member"]["name1"]) == "" )
			$mes .= __('Name is not correct', 'usces');
//		if ( trim($_POST["member"]["name3"]) == "" && USCES_JP )
//			$mes .= __('Invalid CANNAT pretend.', 'usces') . "<br />";
		if ( trim($_POST["member"]["zipcode"]) == "" )
			$mes .= __('postal code is not correct', 'usces') . "<br />";
		if ( $_POST["member"]["pref"] == __('-- Select --', 'usces') )
			$mes .= __('enter the prefecture', 'usces') . "<br />";
		if ( trim($_POST["member"]["address1"]) == "" )
			$mes .= __('enter the city name', 'usces') . "<br />";
		if ( trim($_POST["member"]["address2"]) == "" )
			$mes .= __('enter house numbers', 'usces') . "<br />";
		if ( trim($_POST["member"]["tel"]) == "" )
			$mes .= __('enter phone numbers', 'usces') . "<br />";
			
		$mes = apply_filters('usces_filter_member_check', $mes);
	
		return $mes;
	}

	function member_check_fromcart() {
		$mes = '';
		if ( trim($_POST['customer']['password1']) == '' || trim($_POST['customer']['password2']) == '' || trim($_POST['customer']['password1']) != trim($_POST['customer']['password2']) )
			$mes .= __('Password is not correct.', 'usces') . "<br />";
		if ( !is_email($_POST['customer']['mailaddress1']) || trim($_POST['customer']['mailaddress1']) == '' || trim($_POST['customer']['mailaddress2']) == '' || trim($_POST['customer']['mailaddress1']) != trim($_POST['customer']['mailaddress2']) )
			$mes .= __('e-mail address is not correct', 'usces') . "<br />";
		if ( trim($_POST["customer"]["name1"]) == "" )
			$mes .= __('Name is not correct', 'usces');
//		if ( trim($_POST["customer"]["name3"]) == "" && USCES_JP )
//			$mes .= __('Invalid CANNAT pretend.', 'usces') . "<br />";
		if ( trim($_POST["customer"]["zipcode"]) == "" )
			$mes .= __('postal code is not correct', 'usces') . "<br />";
		if ( $_POST["customer"]["pref"] == __('-- Select --', 'usces') )
			$mes .= __('enter the prefecture', 'usces') . "<br />";
		if ( trim($_POST["customer"]["address1"]) == "" )
			$mes .= __('enter the city name', 'usces') . "<br />";
		if ( trim($_POST["customer"]["address2"]) == "" )
			$mes .= __('enter house numbers', 'usces') . "<br />";
		if ( trim($_POST["customer"]["tel"]) == "" )
			$mes .= __('enter phone numbers', 'usces') . "<br />";
	
		$mes = apply_filters('usces_filter_member_check_fromcart', $mes);

		return $mes;
	}

	function admin_member_check() {
		$mes = '';
		if ( !is_email( trim($_POST['member']["email"]) ) )
			$mes .= __('e-mail address is not correct', 'usces') . "<br />";
		if ( trim($_POST['member']["name1"]) == "" )
			$mes .= __('Name is not correct', 'usces') . "<br />";
//		if ( trim($_POST["mem_name3"]) == "" && USCES_JP )
//			$mes .= __('Invalid CANNAT pretend.', 'usces') . "<br />";
		if ( trim($_POST['member']["zipcode"]) == "" )
			$mes .= __('postal code is not correct', 'usces') . "<br />";
		if ( $_POST['member']["pref"] == __('-- Select --', 'usces') )
			$mes .= __('enter the prefecture', 'usces') . "<br />";
		if ( trim($_POST['member']["address1"]) == "" )
			$mes .= __('enter the city name', 'usces') . "<br />";
		if ( trim($_POST['member']["address2"]) == "" )
			$mes .= __('enter house numbers', 'usces') . "<br />";
		if ( trim($_POST['member']["tel"]) == "" )
			$mes .= __('enter phone numbers', 'usces') . "<br />";
	
		return $mes;
	}

	function customer_check() {
		$mes = '';
		if ( !is_email($_POST['customer']['mailaddress1']) || trim($_POST['customer']['mailaddress1']) == '' || trim($_POST['customer']['mailaddress2']) == '' || trim($_POST['customer']['mailaddress1']) != trim($_POST['customer']['mailaddress2']) )
			$mes .= __('e-mail address is not correct', 'usces') . "<br />";
		if ( trim($_POST["customer"]["name1"]) == "" )
			$mes .= __('Name is not correct', 'usces');
//		if ( trim($_POST["customer"]["name3"]) == "" && USCES_JP )
//			$mes .= __('Invalid CANNAT pretend.', 'usces') . "<br />";
		if ( trim($_POST["customer"]["zipcode"]) == "" )
			$mes .= __('postal code is not correct', 'usces') . "<br />";
		if ( $_POST["customer"]["pref"] == __('-- Select --', 'usces') )
			$mes .= __('enter the prefecture', 'usces') . "<br />";
		if ( trim($_POST["customer"]["address1"]) == "" )
			$mes .= __('enter the city name', 'usces') . "<br />";
		if ( trim($_POST["customer"]["address2"]) == "" )
			$mes .= __('enter house numbers', 'usces') . "<br />";
		if ( trim($_POST["customer"]["tel"]) == "" )
			$mes .= __('enter phone numbers', 'usces') . "<br />";
	
		$mes = apply_filters('usces_filter_customer_check', $mes);

		return $mes;
	}

	function delivery_check() {
		$mes = '';
		if ( $_POST['delivery']['delivery_flag'] == '1' ) {
			if ( trim($_POST["delivery"]["name1"]) == "" )
				$mes .= __('Name is not correct', 'usces');
//			if ( trim($_POST["delivery"]["name3"]) == "" && USCES_JP )
//				$mes .= __('Invalid CANNAT pretend.', 'usces') . "<br />";
			if ( trim($_POST["delivery"]["zipcode"]) == "" )
				$mes .= __('postal code is not correct', 'usces') . "<br />";
			if ( $_POST["delivery"]["pref"] == __('-- Select --', 'usces') )
				$mes .= __('enter the prefecture', 'usces') . "<br />";
			if ( trim($_POST["delivery"]["address1"]) == "" )
				$mes .= __('enter the city name', 'usces') . "<br />";
			if ( trim($_POST["delivery"]["address2"]) == "" )
				$mes .= __('enter house numbers', 'usces') . "<br />";
			if ( trim($_POST["delivery"]["tel"]) == "" )
				$mes .= __('enter phone numbers', 'usces') . "<br />";
		}
		if ( !isset($_POST['offer']['delivery_method']) || (empty($_POST['offer']['delivery_method']) && $_POST['offer']['delivery_method'] != 0) )
			$mes .= __('chose one from delivery method.', 'usces') . "<br />";
		if ( !isset($_POST['offer']['payment_name']) )
			$mes .= __('chose one from payment options.', 'usces') . "<br />";
//20101119ysk start
		if(isset($_POST['offer']['delivery_method']) and isset($_POST['offer']['payment_name'])) {
			$d_method_index = $this->get_delivery_method_index((int)$_POST['offer']['delivery_method']);
			if($this->options['delivery_method'][$d_method_index]['nocod'] == '1') {
				$payments = $this->getPayments($_POST['offer']['payment_name']);
				if('COD' == $payments['settlement'])
					$mes .= __('COD is not available.', 'usces') . "<br />";
			}
		}
//20101119ysk end
//20110317ysk start
		if(isset($_POST['offer']['delivery_method'])) {
			$d_method_index = $this->get_delivery_method_index((int)$_POST['offer']['delivery_method']);
			$country = $_POST["delivery"]["country"];
			//$local_country = usces_get_local_addressform();
			$local_country = usces_get_base_country();
			if($country == $local_country) {
				if($this->options['delivery_method'][$d_method_index]['intl'] == '1') {
					$mes .= __('配送方法が誤っています。国際便は指定できません。', 'usces') . "<br />";
				}
			} else {
				if($this->options['delivery_method'][$d_method_index]['intl'] == '0') {
					$mes .= __('配送方法が誤っています。国際便を指定してください。', 'usces') . "<br />";
				}
			}
		}
//20110317ysk end
	
		$mes = apply_filters('usces_filter_delivery_check', $mes);

		return $mes;
	}

	function point_check( $entries ) {
		$member = $this->get_member();
		$this->set_cart_fees( $member, &$entries );
//var_dump($entries);
		$mes = '';
		if ( trim($_POST['offer']["usedpoint"]) == "" || !(int)$_POST['offer']["usedpoint"] || (int)$_POST['offer']["usedpoint"] < 0 ) {
			$mes .= __('Invalid value. Please enter in the numbers.', 'usces') . "<br />";
		} else {
			if ( trim($_POST['offer']["usedpoint"]) > $member['point'] ){
				$mes .= __('You have exceeded the maximum available.', 'usces') . "max".$member['point']."pt<br />";
				$_POST['offer']["usedpoint"] = 0;
				$array = array(
						'usedpoint' => 0
						);
				$this->cart->set_order_entry( $array );
			}elseif($this->options['point_coverage'] && trim($_POST['offer']["usedpoint"]) > ($entries['order']['total_items_price'] + $entries['order']['discount'] + $entries['order']['shipping_charge'] + $entries['order']['cod_fee'])){ 
				$mes .= __('You have exceeded the maximum available.', 'usces') . "max".($entries['order']['total_items_price'] + $entries['order']['discount'] + $entries['order']['shipping_charge'] + $entries['order']['cod_fee'])."pt<br />";
				$_POST['offer']["usedpoint"] = 0;
				$array = array(
						'usedpoint' => 0
						);
				$this->cart->set_order_entry( $array );
			}elseif(!$this->options['point_coverage'] && trim($_POST['offer']["usedpoint"]) > ($entries['order']['total_items_price'] + $entries['order']['discount'])){
				$mes .= __('You have exceeded the maximum available.', 'usces') . "max".($entries['order']['total_items_price'] + $entries['order']['discount'])."pt<br />";
				$_POST['offer']["usedpoint"] = 0;
				$array = array(
						'usedpoint' => 0
						);
				$this->cart->set_order_entry( $array );
			}
		}
		return $mes;
	}

	function lostpass_mailaddcheck() {
		$mes = '';
		if ( !is_email($_POST['loginmail']) || trim($_POST['loginmail']) == '' ) {
			$mes .= __('e-mail address is not correct', 'usces') . "<br />";
		}elseif( !$this->is_member($_POST['loginmail']) ){
			$mes .= __('It is the e-mail address that there is not.', 'usces') . "<br />";
		}

		return $mes;
	}

	function changepass_check() {
		$mes = '';
		if ( trim($_POST['loginpass1']) == '' || trim($_POST['loginpass2']) == '' || (trim($_POST['loginpass1']) != trim($_POST['loginpass2'])))
			$mes .= __('Password is not correct.', 'usces') . "<br />";

		return $mes;
	}

	function get_page() {
		return $this->page;
	}
	
	function check_display_mode() {
		$options = get_option('usces');
		if($options['display_mode'] == 'Maintenancemode') return;
		
		$start = $options['campaign_schedule']['start'];
		$end = $options['campaign_schedule']['end'];
		$starttime = mktime($start['hour'], $start['min'], 0, $start['month'], $start['day'], $start['year']);
		$endtime = mktime($end['hour'], $end['min'], 0, $end['month'], $end['day'], $end['year']);
		$current_time = current_time('timestamp');

		if( ($current_time >= $starttime) && ($current_time <= $endtime) )
			$options['display_mode'] = 'Promotionsale';
		else
			$options['display_mode'] = 'Usualsale';
		
		update_option('usces', $options);
		
	}
	
	function update_business_days() {
		$options = get_option('usces');
		$datetimestr = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
		$dhour = (int)substr($datetimestr, 11, 2);
		$dminute = (int)substr($datetimestr, 14, 2);
		$dsecond = (int)substr($datetimestr, 17, 2);
		$dmonth = (int)substr($datetimestr, 5, 2);
		$dday = (int)substr($datetimestr, 8, 2);
		$dyear = (int)substr($datetimestr, 0, 4);
		$dtimestamp = mktime($dhour, $dminute, $dsecond, $dmonth, $dday, $dyear);
		$datenow = getdate($dtimestamp);
		list($year, $mon, $mday) = getBeforeMonth($datenow['year'], $datenow['mon'], $datenow['mday'], 1);
		
		if(isset($options['business_days'][$year][$mon][1]))
			unset($options['business_days'][$year][$mon]);
		
		for($i=0; $i<3; $i++){
			list($year, $mon, $mday) = getAfterMonth($datenow['year'], $datenow['mon'], $datenow['mday'], $i);
			$last = getLastDay($year, $mon);
			for($j=1; $j<=$last; $j++){
				if(!isset($options['business_days'][$year][$mon][$j]))
					$options['business_days'][$year][$mon][$j] = 1;
			}
		}

		update_option('usces', $options);

		$_SESSION['usces_checked_business_days'] = '';
	}
	 
	function display_cart() { 
		if($this->cart->num_row() > 0) {
			include (USCES_PLUGIN_DIR . '/includes/cart_table.php');
		} else {
			echo "<div class='no_cart'>" . __('There are no items in your cart.', 'usces') . "</div>\n";
		}
	}

	function display_cart_confirm() { 
		if($this->cart->num_row() > 0) {
			include (USCES_PLUGIN_DIR . '/includes/cart_confirm.php');
		} else {
			echo "<div class='no_cart'>" . __('There are no items in your cart.', 'usces') . "</div>\n";
		}
	}

	function set_initial()
	{
		$rets = usces_metakey_change();
		
		$this->set_default_theme();
		$this->set_default_page();
		$this->set_default_categories();
		$this->create_table();
		$this->update_table();

	}
	
	function create_table()
	{
		global $wpdb;
		
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";
		
		$access_table = $wpdb->prefix . "usces_access";
		$member_table = $wpdb->prefix . "usces_member";
		$member_meta_table = $wpdb->prefix . "usces_member_meta";
		$order_table = $wpdb->prefix . "usces_order";
		$order_meta_table = $wpdb->prefix . "usces_order_meta";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		if($wpdb->get_var("show tables like '$member_table'") != $member_table) {
		
			$sql = "CREATE TABLE " . $access_table . " (
				ID BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				acc_key VARCHAR( 20 ) NOT NULL ,
				acc_type VARCHAR( 20 ) NULL ,
				acc_value LONGTEXT NULL ,
				acc_date DATE NOT NULL DEFAULT '0000-00-00',
				acc_num1 INT( 11 ) NOT NULL DEFAULT 0,
				acc_num2 INT( 11 ) NOT NULL DEFAULT 0,
				acc_str1 VARCHAR( 200 ) NULL ,
				acc_str2 VARCHAR( 200 ) NULL ,
				KEY acc_key ( acc_key ),  
				KEY acc_type ( acc_type ),  
				KEY acc_date ( acc_date )  
				) ENGINE = MYISAM AUTO_INCREMENT=0 $charset_collate;";
		
			dbDelta($sql);
			add_option("usces_db_access", USCES_DB_ACCESS);
		}
		if($wpdb->get_var("show tables like '$member_table'") != $member_table) {
		
			$sql = "CREATE TABLE " . $member_table . " (
				ID BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				mem_email VARCHAR( 100 ) NOT NULL ,
				mem_pass VARCHAR( 64 ) NOT NULL ,
				mem_status INT( 11 ) NOT NULL DEFAULT '0',
				mem_cookie VARCHAR( 13 ) NULL ,
				mem_point INT( 11 ) NOT NULL DEFAULT '0',
				mem_name1 VARCHAR( 100 ) NOT NULL ,
				mem_name2 VARCHAR( 100 ) NULL ,
				mem_name3 VARCHAR( 100 ) NULL ,
				mem_name4 VARCHAR( 100 ) NULL ,
				mem_zip VARCHAR( 50 ) NULL ,
				mem_pref VARCHAR( 100 ) NOT NULL ,
				mem_address1 VARCHAR( 100 ) NOT NULL ,
				mem_address2 VARCHAR( 100 ) NULL ,
				mem_address3 VARCHAR( 100 ) NULL ,
				mem_tel VARCHAR( 100 ) NOT NULL ,
				mem_fax VARCHAR( 100 ) NULL ,
				mem_delivery_flag TINYINT ( 1 ) NULL ,
				mem_delivery LONGTEXT,
				mem_registered DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				mem_nicename VARCHAR( 50 ) NULL ,
				KEY mem_email ( mem_email ) ,  
				KEY mem_pass ( mem_pass )  
				) ENGINE = MYISAM AUTO_INCREMENT=1000 $charset_collate;";
		
			dbDelta($sql);
			add_option("usces_db_member", USCES_DB_MEMBER);
		}
		if($wpdb->get_var("show tables like '$member_meta_table'") != $member_meta_table) {
		
			$sql = "CREATE TABLE " . $member_meta_table . " (
				mmeta_id bigint(20) NOT NULL auto_increment,
				member_id bigint(20) NOT NULL default '0',
				meta_key varchar(255) default NULL,
				meta_value longtext,
				PRIMARY KEY  (mmeta_id),
				KEY order_id (member_id),
				KEY meta_key (meta_key)
				) ENGINE = MYISAM $charset_collate;";
		
			dbDelta($sql);
			add_option("usces_db_member_meta", USCES_DB_MEMBER_META);
		}
		if($wpdb->get_var("show tables like '$order_table'") != $order_table) {
		
			$sql = "CREATE TABLE " . $order_table . " (
				ID BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				mem_id BIGINT( 20 ) UNSIGNED NULL ,
				order_email VARCHAR( 100 ) NOT NULL ,
				order_name1 VARCHAR( 100 ) NOT NULL ,
				order_name2 VARCHAR( 100 ) NULL ,
				order_name3 VARCHAR( 100 ) NULL ,
				order_name4 VARCHAR( 100 ) NULL ,
				order_zip VARCHAR( 50 ) NULL ,
				order_pref VARCHAR( 100 ) NOT NULL ,
				order_address1 VARCHAR( 100 ) NOT NULL ,
				order_address2 VARCHAR( 100 ) NULL ,
				order_address3 VARCHAR( 100 ) NULL ,
				order_tel VARCHAR( 100 ) NOT NULL ,
				order_fax VARCHAR( 100 ) NULL ,
				order_delivery LONGTEXT,
				order_cart LONGTEXT,
				order_note TEXT,
				order_delivery_time VARCHAR( 100 ) NOT NULL ,
				order_payment_name VARCHAR( 100 ) NOT NULL ,
				order_condition TEXT,
				order_item_total_price DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
				order_getpoint INT( 10 ) NOT NULL DEFAULT '0',
				order_usedpoint INT( 10 ) NOT NULL DEFAULT '0',
				order_discount DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
				order_shipping_charge DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
				order_cod_fee DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
				order_tax DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
				order_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				order_modified VARCHAR( 20 ) NULL ,
				order_status VARCHAR( 255 ) NULL ,
				order_check VARCHAR( 255 ) NULL ,
				order_delidue_date VARCHAR( 30 ) NULL ,
				order_delivery_method INT( 10 ) NOT NULL DEFAULT -1,
				order_delivery_date VARCHAR( 100 ) NULL,
				KEY order_email ( order_email ) ,  
				KEY order_name1 ( order_name1 ) ,  
				KEY order_name2 ( order_name2 ) ,  
				KEY order_pref ( order_pref ) ,  
				KEY order_address1 ( order_address1 ) ,  
				KEY order_tel ( order_tel ) ,  
				KEY order_date ( order_date )  
				) ENGINE = MYISAM AUTO_INCREMENT=1000 $charset_collate;";
		
			dbDelta($sql);
			add_option("usces_db_order", USCES_DB_ORDER);
		}
		if($wpdb->get_var("show tables like '$order_meta_table'") != $order_meta_table) {
		
			$sql = "CREATE TABLE " . $order_meta_table . " (
				ometa_id bigint(20) NOT NULL auto_increment,
				order_id bigint(20) NOT NULL default '0',
				meta_key varchar(255) default NULL,
				meta_value longtext,
				PRIMARY KEY  (ometa_id),
				KEY order_id (order_id),
				KEY meta_key (meta_key)
				) ENGINE = MYISAM $charset_collate;";
		
			dbDelta($sql);
			add_option("usces_db_order_meta", USCES_DB_ORDER_META);
		}

	}
	
	function update_table()
	{
		global $wpdb;
		$access_table = $wpdb->prefix . "usces_access";
		$member_table = $wpdb->prefix . "usces_member";
		$member_meta_table = $wpdb->prefix . "usces_member_meta";
		$order_table = $wpdb->prefix . "usces_order";
		$order_meta_table = $wpdb->prefix . "usces_order_meta";
		
		$access_ver = get_option( "usces_db_access" );
		$member_ver = get_option( "usces_db_member" );
		$member_meta_ver = get_option( "usces_db_member_meta" );
		$order_ver = get_option( "usces_db_order" );
		$order_meta_ver = get_option( "usces_db_order_meta" );
		
		if( $access_ver != USCES_DB_ACCESS ) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			$sql = "CREATE TABLE " . $access_table . " (
				ID BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				acc_key VARCHAR( 20 ) NOT NULL ,
				acc_type VARCHAR( 20 ) NULL ,
				acc_value LONGTEXT NULL ,
				acc_date DATE NOT NULL DEFAULT '0000-00-00',
				acc_num1 INT( 11 ) NOT NULL DEFAULT 0,
				acc_num2 INT( 11 ) NOT NULL DEFAULT 0,
				acc_str1 VARCHAR( 200 ) NULL ,
				acc_str2 VARCHAR( 200 ) NULL ,
				KEY acc_key ( acc_key ),  
				KEY acc_type ( acc_type ),  
				KEY acc_date ( acc_date )  
				) ENGINE = MYISAM;";
			
			dbDelta($sql);
			update_option( "usces_db_access", USCES_DB_ACCESS );
		}
		if( $member_ver != USCES_DB_MEMBER ) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			$sql = "CREATE TABLE " . $member_table . " (
				ID BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				mem_email VARCHAR( 100 ) NOT NULL ,
				mem_pass VARCHAR( 64 ) NOT NULL ,
				mem_status INT( 11 ) NOT NULL DEFAULT '0',
				mem_cookie VARCHAR( 13 ) NULL ,
				mem_point INT( 11 ) NOT NULL DEFAULT '0',
				mem_name1 VARCHAR( 100 ) NOT NULL ,
				mem_name2 VARCHAR( 100 ) NULL ,
				mem_name3 VARCHAR( 100 ) NULL ,
				mem_name4 VARCHAR( 100 ) NULL ,
				mem_zip VARCHAR( 50 ) NULL ,
				mem_pref VARCHAR( 100 ) NOT NULL ,
				mem_address1 VARCHAR( 100 ) NOT NULL ,
				mem_address2 VARCHAR( 100 ) NULL ,
				mem_address3 VARCHAR( 100 ) NULL ,
				mem_tel VARCHAR( 100 ) NOT NULL ,
				mem_fax VARCHAR( 100 ) NULL ,
				mem_delivery_flag TINYINT ( 1 ) NULL ,
				mem_delivery LONGTEXT,
				mem_registered DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				mem_nicename VARCHAR( 50 ) NULL ,
				KEY mem_email ( mem_email ) ,  
				KEY mem_pass ( mem_pass )  
				) ENGINE = MYISAM;";
			
			dbDelta($sql);
			update_option( "usces_db_member", USCES_DB_MEMBER );
		}
		if( $member_meta_ver != USCES_DB_MEMBER_META ) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			$sql = "CREATE TABLE " . $member_meta_table . " (
				mmeta_id bigint(20) NOT NULL auto_increment,
				member_id bigint(20) NOT NULL default '0',
				meta_key varchar(255) default NULL,
				meta_value longtext,
				PRIMARY KEY  (mmeta_id),
				KEY order_id (member_id),
				KEY meta_key (meta_key)
				) ENGINE = MYISAM;";
		
			dbDelta($sql);
			update_option("usces_db_member_meta", USCES_DB_MEMBER_META);
		}
		if( $order_ver != USCES_DB_ORDER ) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			$sql = "CREATE TABLE " . $order_table . " (
				ID BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				mem_id BIGINT( 20 ) UNSIGNED NULL ,
				order_email VARCHAR( 100 ) NOT NULL ,
				order_name1 VARCHAR( 100 ) NOT NULL ,
				order_name2 VARCHAR( 100 ) NULL ,
				order_name3 VARCHAR( 100 ) NULL ,
				order_name4 VARCHAR( 100 ) NULL ,
				order_zip VARCHAR( 50 ) NULL ,
				order_pref VARCHAR( 100 ) NOT NULL ,
				order_address1 VARCHAR( 100 ) NOT NULL ,
				order_address2 VARCHAR( 100 ) NULL ,
				order_address3 VARCHAR( 100 ) NULL ,
				order_tel VARCHAR( 100 ) NOT NULL ,
				order_fax VARCHAR( 100 ) NULL ,
				order_delivery LONGTEXT,
				order_cart LONGTEXT,
				order_note TEXT,
				order_delivery_time VARCHAR( 100 ) NOT NULL ,
				order_payment_name VARCHAR( 100 ) NOT NULL ,
				order_condition TEXT,
				order_item_total_price DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
				order_getpoint INT( 10 ) NOT NULL DEFAULT '0',
				order_usedpoint INT( 10 ) NOT NULL DEFAULT '0',
				order_discount DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
				order_shipping_charge DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
				order_cod_fee DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
				order_tax DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
				order_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				order_modified VARCHAR( 20 ) NULL ,
				order_status VARCHAR( 255 ) NULL ,
				order_check VARCHAR( 255 ) NULL ,
				order_delidue_date VARCHAR( 30 ) NULL ,
				order_delivery_method INT( 10 ) NOT NULL DEFAULT -1,
				order_delivery_date VARCHAR( 100 ) NULL,
				KEY order_email ( order_email ) ,  
				KEY order_name1 ( order_name1 ) ,  
				KEY order_name2 ( order_name2 ) ,  
				KEY order_pref ( order_pref ) ,  
				KEY order_address1 ( order_address1 ) ,  
				KEY order_tel ( order_tel ) ,  
				KEY order_date ( order_date ) 
				) ENGINE = MYISAM;";
		
			dbDelta($sql);
			update_option("usces_db_order", USCES_DB_ORDER);
		}
		if( $order_meta_ver != USCES_DB_ORDER_META ) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			$sql = "CREATE TABLE " . $order_meta_table . " (
				ometa_id bigint(20) NOT NULL auto_increment,
				order_id bigint(20) NOT NULL default '0',
				meta_key varchar(255) default NULL,
				meta_value longtext,
				PRIMARY KEY  (ometa_id),
				KEY order_id (order_id),
				KEY meta_key (meta_key)
				) ENGINE = MYISAM;";
		
			dbDelta($sql);
			update_option("usces_db_order_meta", USCES_DB_ORDER_META);
		}
	}
	
	function set_default_theme()
	{
		$themepath = USCES_WP_CONTENT_DIR.'/themes/welcart_default';
		$resourcepath = USCES_WP_CONTENT_DIR.'/plugins/usc-e-shop/theme/welcart_default';
		if( file_exists($themepath) ) return false;
		if(!file_exists($resourcepath) ) return false;
		
		mkdir($themepath);
		$this->dir_copy($resourcepath, $themepath);
	
	}
	
	function dir_copy($source, $dest){
		if ($res = opendir($source)) {
			while (($file = readdir($res)) !== false) {
				$sorce_path = $source . '/' . $file;
				$dest_path = $dest . '/' . $file;
				$filetype = @filetype($sorce_path);
				if( $filetype == 'file' ) {
					copy($sorce_path, $dest_path);
				}elseif( $filetype == 'dir' && $file != '..' && $file != '.' ){
					mkdir($dest_path);
					$this->dir_copy($sorce_path, $dest_path);
				}
			}
			closedir($res);
		}
	}

	function set_default_page()
	{
		global $wpdb;
		$datetime = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
		$datetime_gmt = gmdate('Y-m-d H:i:s', time());

		//cart_page
		$query = $wpdb->prepare("SELECT ID from $wpdb->posts where post_name = %s", USCES_CART_FOLDER);
		$cart_number = $wpdb->get_var( $query );
		if( $cart_number === NULL ) {
			$query = $wpdb->prepare("INSERT INTO $wpdb->posts 
				(post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, 
				comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, 
				post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)
				VALUES (%d, %s, %s, %s, %s, %s, %s, 
				%s, %s, %s, %s, %s, %s, %s, %s, 
				%s, %d, %s, %d, %s, %s, %d)", 
				1, $datetime, $datetime_gmt, '', __('Cart', 'usces'), '', 'publish', 
				'closed', 'closed', '', USCES_CART_FOLDER, '', '', $datetime, $datetime_gmt, 
				'', 0, '', 0, 'page', '', 0);
			$wpdb->query($query);
			$cart_number = $wpdb->insert_id;
			if( $cart_number !== NULL ) {
				$query = $wpdb->prepare("INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES (%d, %s, %s)", 
					$cart_number, '_wp_page_template', 'uscescart.php');
				$wpdb->query($query);
			}
		}
		update_option('usces_cart_number', $cart_number);
		
		//member_page
		$query = $wpdb->prepare("SELECT ID from $wpdb->posts where post_name = %s", USCES_MEMBER_FOLDER);
		$member_number = $wpdb->get_var( $query );
		if( $member_number === NULL ) {
			$query = $wpdb->prepare("INSERT INTO $wpdb->posts 
				(post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, 
				comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, 
				post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)
				VALUES (%d, %s, %s, %s, %s, %s, %s, 
				%s, %s, %s, %s, %s, %s, %s, %s, 
				%s, %d, %s, %d, %s, %s, %d)", 
				1, $datetime, $datetime_gmt, '', __('Membership', 'usces'), '', 'publish', 
				'closed', 'closed', '', USCES_MEMBER_FOLDER, '', '', $datetime, $datetime_gmt, 
				'', 0, '', 0, 'page', '', 0);
			$wpdb->query($query);
			$member_number = $wpdb->insert_id;
			if( $member_number !== NULL ) {
				$query = $wpdb->prepare("INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES (%d, %s, %s)", 
					$member_number, '_wp_page_template', 'uscesmember.php');
				$wpdb->query($query);
			}
		}
		update_option('usces_member_number', $member_number);
		
	}
	
	function set_default_categories()
	{
		global $wpdb;
		
		
		//$wpdb->show_errors();

		//item_parent
//		$query = "SELECT term_id FROM $wpdb->terms WHERE slug = 'item'";
//		$item_parent = $wpdb->get_var( $query );
//		if($item_parent === NULL) {
//			$query = $wpdb->prepare("INSERT INTO $wpdb->terms (name, slug, term_group) VALUES (%s, %s, %d)", 
//				__('Items', 'usces'), 'item', 0);
//			$wpdb->query($query);
//			$item_parent = $wpdb->insert_id;
//			if( $item_parent !== NULL ) {
//				$query = $wpdb->prepare("INSERT INTO $wpdb->term_taxonomy (term_id, taxonomy, description, parent, count) 
//					VALUES (%d, %s, %s, %d, %d)", $item_parent, 'category', '', 0, 0);
//				$wpdb->query($query);
//			}
//		}
//		update_option('usces_item_cat_parent_id', $item_parent);
		$idObj = get_category_by_slug('item'); 
		$item_cat_id = $idObj->term_id;
		if( !$item_cat_id ) {
			$item_cat = array('cat_name' => __('Items', 'usces'), 'category_description' => '', 'category_nicename' => 'item', 'category_parent' => 0);
			$item_cat_id = wp_insert_category($item_cat);	
		}
		update_option('usces_item_cat_parent_id', $item_cat_id);	
		$ids[] = $item_cat_id;
		//item_reco
//		$query = "SELECT term_id FROM $wpdb->terms WHERE slug = 'itemreco'";
//		$item_id = $wpdb->get_var( $query );
//		if($item_id === NULL) {
//			$query = $wpdb->prepare("INSERT INTO $wpdb->terms (name, slug, term_group) VALUES (%s, %s, %d)", 
//				__('Items recommended', 'usces'), 'itemreco', 0);
//			$wpdb->query($query);
//			$item_id = $wpdb->insert_id;
//			if( $item_id !== NULL ) {
//				$query = $wpdb->prepare("INSERT INTO $wpdb->term_taxonomy (term_id, taxonomy, description, parent, count) 
//					VALUES (%d, %s, %s, %d, %d)", $item_id, 'category', '', $item_parent, 0);
//				$wpdb->query($query);
//			}
//		}
		$idObj = get_category_by_slug('itemreco'); 
		$itemreco_id = $idObj->term_id;
		if( !$itemreco_id ) {
			$itemreco_cat = array('cat_name' => __('Items recommended', 'usces'), 'category_description' => '', 'category_nicename' => 'itemreco', 'category_parent' => $item_cat_id);
			$itemreco_cat_id = wp_insert_category($itemreco_cat);	
			$ids[] = $itemreco_cat_id;
		}

		//item_new
//		$query = "SELECT term_id FROM $wpdb->terms WHERE slug = 'itemnew'";
//		$item_id = $wpdb->get_var( $query );
//		if($item_id === NULL) {
//			$query = $wpdb->prepare("INSERT INTO $wpdb->terms (name, slug, term_group) VALUES (%s, %s, %d)", 
//				__('New items', 'usces'), 'itemnew', 0);
//			$wpdb->query($query);
//			$item_id = $wpdb->insert_id;
//			if( $item_id !== NULL ) {
//				$query = $wpdb->prepare("INSERT INTO $wpdb->term_taxonomy (term_id, taxonomy, description, parent, count) 
//					VALUES (%d, %s, %s, %d, %d)", $item_id, 'category', '', $item_parent, 0);
//				$wpdb->query($query);
//			}
//		}
		$idObj = get_category_by_slug('itemnew'); 
		$itemnew_id = $idObj->term_id;
		if( !$itemnew_id ) {
			$itemnew_cat = array('cat_name' => __('New items', 'usces'), 'category_description' => '', 'category_nicename' => 'itemnew', 'category_parent' => $item_cat_id);
			$itemnew_cat_id = wp_insert_category($itemnew_cat);	
			$ids[] = $itemnew_cat_id;
		}

		//item_category
//		$query = "SELECT term_id FROM $wpdb->terms WHERE slug = 'itemgenre'";
//		$item_id = $wpdb->get_var( $query );
//		if($item_id === NULL) {
//			$query = $wpdb->prepare("INSERT INTO $wpdb->terms (name, slug, term_group) VALUES (%s, %s, %d)", 
//				__('Item genre', 'usces'), 'itemgenre', 0);
//			$wpdb->query($query);
//			$item_id = $wpdb->insert_id;
//			if( $item_id !== NULL ) {
//				$query = $wpdb->prepare("INSERT INTO $wpdb->term_taxonomy (term_id, taxonomy, description, parent, count) 
//					VALUES (%d, %s, %s, %d, %d)", $item_id, 'category', '', $item_parent, 0);
//				$wpdb->query($query);
//			}
//		}
		$idObj = get_category_by_slug('itemgenre'); 
		$itemgenre_id = $idObj->term_id;
		if( !$itemgenre_id ) {
			$itemgenre_cat = array('cat_name' => __('Item genre', 'usces'), 'category_description' => '', 'category_nicename' => 'itemgenre', 'category_parent' => $item_cat_id);
			$itemgenre_cat_id = wp_insert_category($itemgenre_cat);	
			$ids[] = $itemgenre_cat_id;
		}
		
//		$children = array();
//		$terms = get_terms('category', array('get' => 'all', 'orderby' => 'id', 'fields' => 'id=>parent'));
//
//		foreach ( $terms as $term_id => $parent ) {
//			if ( $parent > 0 )
//				$children[$parent][] = $term_id;
//		}
//		update_option("category_children", $children);
	}

	function get_item_cat_ids(){
		$args = array('child_of' => USCES_ITEM_CAT_PARENT_ID, 'hide_empty' => 0, 'hierarchical' => 0);
		$categories = get_categories( $args );
		foreach($categories as $category){
			$ids[] = $category->term_id;
		}
		return $ids;
	}
	
	function get_item_post_ids(){
		global $wpdb;
		$query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_mime_type = %s", 'item');
		$ids = $wpdb->get_col( $query );

		return $ids;
	}
	
	function get_item_cat_genre_ids( $post_id ){
		$ids = array();
		$all_ids = array();
		$genre = get_category_by_slug( 'itemgenre' );
		$genre_id = $genre->term_id;
		$args = array('child_of' => $genre_id, 'hide_empty' => 0, 'hierarchical' => 0);
		$categories = get_categories( $args );
		foreach($categories as $category){
			$ids[] = $category->term_id;
		}
		$allcats = get_the_category( $post_id );
		foreach($allcats as $cat){
			$all_ids[] = $cat->term_id;
		}
		//$all_ids = 
		$results = array_intersect($ids, $all_ids);
		
		return $results;
	}
	
	function set_item_mime($post_id, $str)
	{
		global $wpdb;
		if($str == '') return;
		
		$query = $wpdb->prepare("UPDATE $wpdb->posts SET post_mime_type = %s WHERE ID = %s", $str, $post_id);
		$results = $wpdb->query( $query );
		return $results;
	}
	
	function isAdnminSSL()
	{
		$plugins = get_option('active_plugins');
		foreach($plugins as $plugin) {
			if( false !== strpos($plugin, USCES_ADMIN_SSL_BASE_NAME) )
				return true;
		}
		return false;
	}
	
	function getGuidTax() {
		if ( (int)$this->options['tax_rate'] > 0 )
			return '<em class="tax">'.__('(Excl. Tax)', 'usces').'</em>';
		else
			return '<em class="tax">'.__('(Incl. Tax)', 'usces').'</em>';
	}

	function getItemCode($post_id) {
		$str = get_post_custom_values('_itemCode', $post_id);
		return $str[0];
	}
	
	function getItemName($post_id) {
		$str = get_post_custom_values('_itemName', $post_id);
		return $str[0];
	}
	
	function getItemRestriction($post_id) {
		$str = get_post_custom_values('_itemRestriction', $post_id);
		return $str[0];
	}
	
	function getItemPointrate($post_id) {
		$str = get_post_custom_values('_itemPointrate', $post_id);
		return $str[0];
	}
	
	function getItemShipping($post_id) {
		$str = get_post_custom_values('_itemShipping', $post_id);
		return $str[0];
	}
	
	function getItemShippingCharge($post_id) {
		$str = get_post_custom_values('_itemShippingCharge', $post_id);
		return (int)$str[0];
	}
	
	function getItemDeliveryMethod($post_id) {
		$str = get_post_custom_values('_itemDeliveryMethod', $post_id);
		return unserialize($str[0]);
	}
	
	function getItemIndividualSCharge($post_id) {
		$str = get_post_custom_values('_itemIndividualSCharge', $post_id);
		return $str[0];
	}
	
	function getItemGpNum1($post_id) {
		$str = get_post_custom_values('_itemGpNum1', $post_id);
		return $str[0];
	}
	
	function getItemGpNum2($post_id) {
		$str = get_post_custom_values('_itemGpNum2', $post_id);
		return $str[0];
	}
	
	function getItemGpNum3($post_id) {
		$str = get_post_custom_values('_itemGpNum3', $post_id);
		return $str[0];
	}
	
	function getItemGpDis1($post_id) {
		$str = get_post_custom_values('_itemGpDis1', $post_id);
		return $str[0];
	}
	
	function getItemGpDis2($post_id) {
		$str = get_post_custom_values('_itemGpDis2', $post_id);
		return $str[0];
	}
	
	function getItemGpDis3($post_id) {
		$str = get_post_custom_values('_itemGpDis3', $post_id);
		return $str[0];
	}
	
	function getItemSku($post_id, $index = '') {
		$fields = get_post_custom($post_id);
		foreach((array)$fields as $key => $value){
			if( preg_match('/^_isku_/', $key, $match) ){
				$key = substr($key, 6);
				$skus[] = $key;
			}
		}
		if(!$skus) return false;
		if($index == ''){
			return $skus;
		}else if(isset($skus[$index])){
			return $skus[$index];
		}else{
			return false;
		}
	}
	
	function getItemPrice($post_id, $skukey = '') {
		$fields = get_post_custom($post_id);
		foreach((array)$fields as $key => $value){
			if( preg_match('/^_isku_/', $key, $match) ){
				$key = substr($key, 6);
				$values = maybe_unserialize($value[0]);
				$skus[$key] = (float)str_replace(',', '', $values['price']);
			}
		}
		if(!$skus) return false;
		if($skukey == ''){
			return $skus;
		}else if(isset($skus[$skukey])){
			return $skus[$skukey];
		}else{
			return false;
		}
	}
	
	function getItemDiscount($post_id, $skukey = '') {
		$display_mode = $this->options['display_mode'];
		$fields = get_post_custom($post_id);
		foreach((array)$fields as $key => $value){
			if( preg_match('/^_isku_/', $key, $match) ){
				$key = substr($key, 6);
				$values = maybe_unserialize($value[0]);
				$price = (float)str_replace(',', '', $values['price']);
				if ( $display_mode == 'Promotionsale' ) {
					if ( $this->options['campaign_privilege'] == 'discount' ){
						if( 0 === (int)$this->options['campaign_category'] || in_category((int)$this->options['campaign_category'], $post_id) ){
							$discount = $price * $this->options['privilege_discount'] / 100;
						}else{
							$discount = 0;
						}
					}else if ( $this->options['campaign_privilege'] == 'point' ){
						$discount = 0;
					}
				}
		
				$discount = ceil($discount);
				$skus[$key] = $discount;
			}
		}
		if(!$skus) return false;
		if($skukey == ''){
			return $skus;
		}else if(isset($skus[$skukey])){
			return $skus[$skukey];
		}else{
			return false;
		}
	}
	
	function getItemZaiko($post_id, $skukey = '') {
		$fields = get_post_custom($post_id);
		foreach((array)$fields as $key => $value){
			if( preg_match('/^_isku_/', $key, $match) ){
				$key = substr($key, 6);
				$values = maybe_unserialize($value[0]);
				$num = $values['zaiko'];
				$skus[$key] = $this->zaiko_status[$num];
			}
		}
		if(!$skus) return false;
		if($skukey == ''){
			return $skus;
		}else if(isset($skus[$skukey])){
			return $skus[$skukey];
		}else{
			return false;
		}
	}
	
	function getItemZaikoStatusId($post_id, $skukey = '') {
		$fields = get_post_custom($post_id);
		foreach((array)$fields as $key => $value){
			if( preg_match('/^_isku_/', $key, $match) ){
				$key = substr($key, 6);
				$values = maybe_unserialize($value[0]);
				$num = $values['zaiko'];
				$skus[$key] = $num;
			}
		}
		if(!$skus) return false;
		if($skukey == ''){
			return $skus;
		}else if(isset($skus[$skukey])){
			return $skus[$skukey];
		}else{
			return false;
		}
	}
	
	function updateItemZaiko($post_id, $skukey, $status) {
		$fields = get_post_custom($post_id);
		foreach((array)$fields as $key => $value){
			$turekey = '_isku_'.$skukey;
			if( $key == $turekey ){
				$values = maybe_unserialize($value[0]);
				$values['zaiko'] = $status;
				update_post_meta($post_id, $turekey, $values);
				return;
			}
		}
	}
	
	function getItemZaikoNum($post_id, $skukey = '') {
		$fields = get_post_custom($post_id);
		foreach((array)$fields as $key => $value){
			if( preg_match('/^_isku_/', $key, $match) ){
				$key = substr($key, 6);
				$values = maybe_unserialize($value[0]);
				$skus[$key] = $values['zaikonum'];
			}
		}
		if(!$skus) return false;
		if($skukey == ''){
			return $skus;
		}else if(isset($skus[$skukey])){
			return $skus[$skukey];
		}else{
			return false;
		}
	}
	
	function updateItemZaikoNum($post_id, $skukey, $num) {
		$fields = get_post_custom($post_id);
		foreach((array)$fields as $key => $value){
			$turekey = '_isku_'.$skukey;
			if( $key == $turekey ){
				$values = maybe_unserialize($value[0]);
				$values['zaikonum'] = $num;
				update_post_meta($post_id, $turekey, $values);
				return;
			}
		}
	}
	
	function getItemSkuDisp($post_id, $skukey = '') {
		$fields = get_post_custom($post_id);
		foreach((array)$fields as $key => $value){
			if( preg_match('/^_isku_/', $key, $match) ){
				$key = substr($key, 6);
				$values = maybe_unserialize($value[0]);
				$skus[$key] = $values['disp'];
			}
		}
		if(!$skus) return false;
		if($skukey == ''){
			return $skus;
		}else if(isset($skus[$skukey])){
			return $skus[$skukey];
		}else{
			return false;
		}
	}
	
	function getItemSkuChargingType($post_id, $skukey = '') {
		$fields = get_post_custom($post_id);
		$skus = array();
		foreach((array)$fields as $key => $value){
			if( preg_match('/^_isku_/', $key, $match) ){
				$key = substr($key, 6);
				$values = maybe_unserialize($value[0]);
				if( isset($values['charging_type']) && "undefined" != $values['charging_type'] && !empty($values['charging_type']) ){ 
					$skus[$key] = $values['charging_type'];
				}else{
					continue;
				}
			}
		}
		if( empty($skus) ) return false;
		if($skukey == ''){
			return $skus;
		}elseif( isset($skus[$skukey]) ){
			return $skus[$skukey];
		}else{
			return false;
		}
	}
	
	function getItemSkuUnit($post_id, $skukey = '') {
		$fields = get_post_custom($post_id);
		foreach((array)$fields as $key => $value){
			if( preg_match('/^_isku_/', $key, $match) ){
				$key = substr($key, 6);
				$values = maybe_unserialize($value[0]);
				$skus[$key] = $values['unit'];
			}
		}
		if(!$skus) return false;
		if($skukey == ''){
			return $skus;
		}else if(isset($skus[$skukey])){
			return $skus[$skukey];
		}else{
			return false;
		}
	}
	
	function get_item( $post_id ) {
		$usces_item['post_id'] = $post_id;
		$usces_item['itemCode'] = $this->getItemCode($post_id);
		$usces_item['itemName'] = $this->getItemName($post_id);
		
		$fields = get_post_custom($post_id);
		foreach((array)$fields as $key => $value){
			if( preg_match('/^_isku_/', $key, $match) ){
				$key = substr($key, 6);
				$values = maybe_unserialize($value[0]);
				$usces_item['skuCodes'][] = $key;
				$usces_item['skuValues'][] = $values;
			}
		}
		
		$usces_item = apply_filters('usces_filter_get_item', $usces_item, $post_id);
		
		return $usces_item;
	}

	function get_itemOptionKey( $post_id ) {
		$custom_field_keys = get_post_custom_keys( $post_id );
		if(empty($custom_field_keys)) return;
		
		foreach ( (array)$custom_field_keys as $key => $value ) {
			if ( '_iopt_' == substr($value,0 , 6) )
				$res[] = substr($value, 6);
		}
		if($res)
			natcasesort($res);
		return $res;
	}
	
	function get_itemOptions( $key, $post_id ) {
		$metakey = '_iopt_' . $key;
		$values = get_post_custom_values( $metakey, $post_id );
		if(empty($values)) return;

		return unserialize($values[0]);
	}
	
	function get_postIDbyCode( $itemcode ) {
		global $wpdb;
		
		$codestr = $itemcode;
		$query = $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_value = %s", $codestr);
		$res = $wpdb->get_var( $query );
		return $res;
	}

	function get_pictids($item_code) {
		global $wpdb;
		
		$codestr = $item_code.'%';
		$query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title LIKE %s AND post_type = 'attachment' ORDER BY post_title", $codestr);
		$results = $wpdb->get_col( $query );
		return $results;
	}
	
	function get_skus( $post_id, $output='' ) {
		$fields = get_post_custom($post_id);
		if( !is_array($fields)) $fields = array();
		ksort($fields);
		foreach($fields as $k => $v){
			if( preg_match('/^_isku_/', $k, $match) ){
				$values = maybe_unserialize($v[0]);
				$key[] = substr($k, 6);
				$cprice[] = $values['cprice'];
				$price[] = $values['price'];
				$zaiko[] = $values['zaiko'];
				$zaikonum[] = $values['zaikonum'];
				$disp[] = $values['disp'];
				$unit[] = $values['unit'];
				$gptekiyo[] = $values['gptekiyo'];
				
				$res[substr($k, 6)]['cprice'] = $values['cprice'];
				$res[substr($k, 6)]['price'] = $values['price'];
				$res[substr($k, 6)]['zaiko'] = $values['zaiko'];
				$res[substr($k, 6)]['zaikonum'] = $values['zaikonum'];
				$res[substr($k, 6)]['disp'] = $values['disp'];
				$res[substr($k, 6)]['unit'] = $values['unit'];
				$res[substr($k, 6)]['gptekiyo'] = $values['gptekiyo'];
			}
		}
		if($output == 'ARRAY_A'){
			return $res;
		}else{
			return compact('key', 'cprice', 'price', 'zaiko', 'zaikonum', 'disp', 'unit', 'gptekiyo' );
		}
	}
	
	function is_item( $post ) {
	
//		$catids = wp_get_post_categories($post_id);
//		
//		$res = '';
//		foreach($catids as $id){
//			$cat = get_category($id);
//			if( $cat->slug == 'item' || $cat->parent == USCES_ITEM_CAT_PARENT_ID )
//				$res = 'ok';
//		}
		
		if( $post->post_mime_type == 'item' )
			return true;
		else
			return false;
	}
	
	function getItemIds( $end_type ) {
		global $wpdb;
		if( 'front' == $end_type )
			$query = $wpdb->prepare("SELECT ID  FROM $wpdb->posts WHERE post_status = %s AND post_mime_type = %s", 'publish', 'item');
		if( 'back' == $end_type )
			$query = $wpdb->prepare("SELECT ID  FROM $wpdb->posts WHERE post_mime_type = %s", 'item');
		$ids = $wpdb->get_col( $query );
		if( empty($ids) ) $ids = array();
		return $ids;
	}
	
	function getNotItemIds() {
		global $wpdb;
		$query = $wpdb->prepare("SELECT ID  FROM $wpdb->posts WHERE post_status = %s AND post_mime_type <> %s", 'publish', 'item');
		$ids = $wpdb->get_col( $query );
		if( empty($ids) ) $ids = array();
		return $ids;
	}
	
	function getPaymentMethod( $name ) {
		$res = array();
		$payments = $this->options['payment_method'];
		foreach ( (array)$payments as $payment ) {
			if($name = $payment['name']) {
				$res = $payment;
				break;
			}
		}
		return 	$res;
	}
	
	function order_processing( $results = array() ) {
		do_action('usces_pre_reg_orderdata');
		//db(function.php)
//20110203ysk start
		$res = usces_check_acting_return_duplicate( $results );
		if($res != NULL) {
			usces_log('order processing duplicate : acting='.$_REQUEST['acting'].', order_id='.$res, 'acting_transaction.log');
			return 'ordercompletion';
		}
		if(isset($_REQUEST['acting']) && ('jpayment_card' == $_REQUEST['acting'] || 'jpayment_conv' == $_REQUEST['acting'] || 'jpayment_bank' == $_REQUEST['acting'])) {
			usces_log($_REQUEST['acting'].' transaction : '.$_REQUEST['gid'], 'acting_transaction.log');//OK
		}
//20110203ysk end
		$order_id = usces_reg_orderdata( $results );
		do_action('usces_post_reg_orderdata', $order_id, $results);
		
		if ( $order_id ) {
			//mail(function.php)
			$mail_res = usces_send_ordermail( $order_id );
			return 'ordercompletion';
		
		} else {
			return 'error';
		}
	
	}

	function acting_processing($acting_flg, $query) {
		$entry = $this->cart->get_entry();
		
		$acting_flg = trim($acting_flg);
		//$usces_entries = $this->cart->get_entry();

		if( empty($acting_flg) ) return 'error';
		
		
		//include(USCES_PLUGIN_DIR . '/settlement/' . $acting_flg);
		if($acting_flg == 'paypal.php'){
//			if( !file_exists($this->options['settlement_path'] . $acting_flg) )
//				return 'error';
//				
//			require_once($this->options['settlement_path'] . "paypal.php");
//			paypal_submit();
			
		}else if($acting_flg == 'epsilon.php'){
			if( !file_exists($this->options['settlement_path'] . $acting_flg) )
				return 'error';

			if ( $this->use_ssl ) {
				$redirect = str_replace('http://', 'https://', USCES_CART_URL);
			}else{
				$redirect = USCES_CART_URL;
			}
			usces_log('epsilon card entry data (acting_processing) : '.print_r($entry, true), 'acting_transaction.log');
			$query .= '&settlement=epsilon&redirect_url=' . urlencode($redirect);
			$query = $this->delim . ltrim($query, '&');
			header("location: " . $redirect . $query);
			exit;
			
		}else if($acting_flg == 'acting_zeus_card'){
		
			$acting_opts = $this->options['acting_settings']['zeus'];
			$interface = parse_url($acting_opts['card_url']);


			$vars = 'send=mall';
			$vars .= '&clientip=' . $acting_opts['clientip'];
			$vars .= '&cardnumber=' . $_POST['cardnumber'];
			$vars .= '&expyy=' . substr($_POST['expyy'], 2);
			$vars .= '&expmm=' . $_POST['expmm'];
			$vars .= '&telno=' . str_replace('-', '', $_POST['telno']);
			$vars .= '&email=' . $_POST['email'];
			$vars .= '&sendid=' . $_POST['sendid'];
			$vars .= '&username=' . $_POST['username'];
			$vars .= '&money=' . $_POST['money'];
			$vars .= '&sendpoint=' . $_POST['sendpoint'];
			$vars .= '&printord=' . $_POST['printord'];
			if( isset($_POST['howpay']) && '0' === $_POST['howpay'] ){	
				$vars .= '&div=' . $_POST['div'];
			}


			$header = "POST " . $interface['path'] . " HTTP/1.1\r\n";
			$header .= "Host: " . $_SERVER['HTTP_HOST'] . "\r\n";
			//$header .= "Host: usctest.securesites.com\r\n";
			$header .= "User-Agent: PHP Script\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($vars) . "\r\n";
			$header .= "Connection: close\r\n\r\n";
			$header .= $vars;
			$fp = fsockopen('ssl://'.$interface['host'],443,$errno,$errstr,30);
			
			if ($fp){
				fwrite($fp, $header);
				while ( !feof($fp) ) {
					$scr = fgets($fp, 1024);
					$page .= $scr;
				}
				fclose($fp);

				if( false !== strpos( $page, 'Success_order') ){
					usces_log('zeus card entry data (acting_processing) : '.print_r($entry, true), 'acting_transaction.log');
					header("Location: " . USCES_CART_URL . $this->delim . 'acting=zeus_card&acting_return=1');
					exit;
				}else{
					header("Location: " . USCES_CART_URL . $this->delim . 'acting=zeus_card&acting_return=0');
					exit;
				}
			}else{
				header("Location: " . USCES_CART_URL . $this->delim . 'acting=zeus_card&acting_return=0');
			}
			exit;

		}else if($acting_flg == 'acting_zeus_conv'){
		
			$acting_opts = $this->options['acting_settings']['zeus'];
			$interface = parse_url($acting_opts['conv_url']);


			$vars .= 'clientip=' . $acting_opts['clientip_conv'];
			$vars .= '&act=' . $_POST['act'];
			$vars .= '&money=' . $_POST['money'];
			$vars .= '&username=' . mb_convert_encoding($_POST['username'], 'SJIS', 'UTF-8');
			$vars .= '&telno=' . str_replace('-', '', $_POST['telno']);
			$vars .= '&email=' . $_POST['email'];
			$vars .= '&pay_cvs=' . $_POST['pay_cvs'];
			$vars .= '&sendid=' . $_POST['sendid'];
			$vars .= '&sendpoint=' . $_POST['sendpoint'];
			if( '' != $acting_opts['testid_conv'] ){	
				$vars .= '&testid=' . $acting_opts['testid_conv'];
				$vars .= '&test_type=' . $acting_opts['test_type_conv'];
			}


			$header = "POST " . $interface['path'] . " HTTP/1.1\r\n";
			$header .= "Host: " . $_SERVER['HTTP_HOST'] . "\r\n";
			$header .= "User-Agent: PHP Script\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($vars) . "\r\n";
			$header .= "Connection: close\r\n\r\n";
			$header .= $vars;
			$fp = fsockopen('ssl://'.$interface['host'],443,$errno,$errstr,30);
			
			if ($fp){
				fwrite($fp, $header);
				while ( !feof($fp) ) {
					$scr = fgets($fp, 1024);
					$page .= $scr;
					if( false !== strpos( $scr, 'order_no') )
						$qstr .= trim($scr) . '&';
					if( false !== strpos( $scr, 'pay_no1') )
						$qstr .= trim($scr) . '&';
					if( false !== strpos( $scr, 'pay_no2') )
						$qstr .= trim($scr) . '&';
					if( false !== strpos( $scr, 'pay_limit') )
						$qstr .= trim($scr) . '&';
					if( false !== strpos( $scr, 'pay_url') )
						$qstr .= trim($scr) . '&';
					if( false !== strpos( $scr, 'error_code') )
						$qstr .= trim($scr) . '&';
					if( false !== strpos( $scr, 'sendpoint') )
						$qstr .= trim($scr) . '&';
				}
				$qstr .= 'pay_cvs=' . $_POST['pay_cvs'];
				fclose($fp);
				//usces_log('zeus page : '.$page, 'acting_transaction.log');

				if( false !== strpos( $page, 'Success_order') ){
					usces_log('zeus conv entry data (acting_processing) : '.print_r($entry, true), 'acting_transaction.log');
					header("Location: " . USCES_CART_URL . $this->delim . 'acting=zeus_conv&acting_return=1&' . $qstr);
					exit;
				}else{
					usces_log('zeus data NG : '.$page, 'acting_transaction.log');
					header("Location: " . USCES_CART_URL . $this->delim . 'acting=zeus_conv&acting_return=0');
					exit;
				}
			}else{
				usces_log('zeus : sockopen NG', 'acting_transaction.log');
				header("Location: " . USCES_CART_URL . $this->delim . 'acting=zeus_conv&acting_return=0');
			}
			exit;

//20110208ysk start
		}else if($acting_flg == 'acting_paypal_ec') {
			$acting_opts = $this->options['acting_settings']['paypal'];

			$nvpstr  = $query;
			$nvpstr .= '&CURRENCYCODE='.$this->get_currency_code();
			//$nvpstr .= '&ADDROVERRIDE=1';
			$nvpstr .= '&PAYMENTACTION=Sale';

			//The returnURL is the location where buyers return to when a payment has been succesfully authorized.
			$nvpstr .= '&RETURNURL='.urlencode(USCES_CART_URL.$this->delim.'acting=paypal_ec&acting_return=1&uscesid='.$this->get_uscesid(false));

			//The cancelURL is the location buyers are sent to when they hit the cancel button during authorization of payment during the PayPal flow
			$nvpstr .= '&CANCELURL='.urlencode(USCES_CART_URL.$this->delim.'confirm=1');

			$nvpstr .= '&NOTIFYURL='.urlencode(USCES_PAYPAL_NOTIFY_URL);

			$this->paypal->setMethod('SetExpressCheckout');
			$this->paypal->setData($nvpstr);
			$res = $this->paypal->doExpressCheckout();
			$resArray = $this->paypal->getResponse();
			$ack = strtoupper($resArray["ACK"]);
			if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {
				$token = urldecode($resArray["TOKEN"]);
				$payPalURL = $acting_opts['paypal_url'].'?cmd=_express-checkout&token='.$token.'&useraction=commit';
				header("Location: ".$payPalURL);

			} else {
				//Display a user friendly Error on the page using any of the following error information returned by PayPal
				$ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
				$ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
				$ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
				$ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);
				usces_log('PayPal : SetExpressCheckout API call failed. Error Code:['.$ErrorCode.'] Error Severity Code:['.$ErrorSeverityCode.'] Short Error Message:'.$ErrorShortMsg.' Detailed Error Message:'.$ErrorLongMsg, 'acting_transaction.log');
				header("Location: ".USCES_CART_URL.$this->delim.'acting=paypal_ec&acting_return=0');
			}
			exit;
//20110208ysk end
		}
	}

	function inquiry_processing() {
	
		$mail_res = usces_send_inquirymail();
		
		if ( $mail_res )
			return 'inquiry_comp';
		else
			return 'inquiry_error';
	}
	
	function lastprocessing() {
		
		if ( $this->page == 'ordercompletion' )
			$this->cart->crear_cart();
			
		unset($_SESSION['usces_singleitem']);

	}
	
	function is_item_zaiko( $post_id, $sku ){
		$status_num = (int)$this->getItemZaikoStatusId($post_id, $sku);
		$zaiko_num = trim($this->getItemZaikoNum($post_id, $sku));

		if( false !== $zaiko_num 
			&& ( 0 < (int)$zaiko_num || '' == $zaiko_num ) 
			&& false !== $status_num 
			&& 2 > $status_num 
		){
			return true;
		}else{
			return false;
		}
	}

	// function for the cart ***********************************************************
	function get_total_price( $cart = array() ) {
		if( empty($cart) )
			$cart = $this->cart->get_cart();
	
		$total_price = 0;

		if( !empty($cart) ) {
			for($i=0; $i<count($cart); $i++) { 
				$quantity = $cart[$i]['quantity'];
				$skuPrice = $cart[$i]['price'];
				
				$total_price += ($skuPrice * $quantity);
			}
		}
		return $total_price;
	}
	
	function get_total_quantity( $cart = array() ) {
		if( empty($cart) )
			$cart = $this->cart->get_cart();
	
		$total_quantity = 0;

		if( !empty($cart) ) {
			for($i=0; $i<count($cart); $i++) { 
				$total_quantity += $cart[$i]['quantity'];
			}
		}
		return $total_quantity;
	}
	
	function get_order_point( $mem_id = '', $display_mode = '', $cart = array() ) {
		if( $mem_id == '' || $this->options['membersystem_state'] == 'deactivate' || $this->options['membersystem_point'] == 'deactivate') return 0;
		
		if ( empty($cart) )
			$cart = $this->cart->get_cart();
		
		if ( empty($display_mode) )
			$display_mode = $this->options['display_mode'];
		
		$point = 0;
		$total = $this->get_total_price( $cart );
		if ( $display_mode == 'Promotionsale' ) {
			if ( $this->options['campaign_privilege'] == 'discount' ) {
				$point = 0;
			} elseif ( $this->options['campaign_privilege'] == 'point' ) {
				foreach ( $cart as $rows ) {
					$rate = get_post_custom_values('_itemPointrate', $rows['post_id']);
					//$price = $this->getItemPrice($rows['post_id'], $rows['sku']) * $rows['quantity'];
					$price = $rows['price'] * $rows['quantity'];
					$cats = $this->get_post_term_ids($rows['post_id'], 'category');
					if ( in_array($this->options['campaign_category'], $cats) )
						$point += $price * $rate[0] / 100 * $this->options['privilege_point'];
					else
						$point += $price * $rate[0] / 100;
				}
			}
		} else {
			foreach ( $cart as $rows ) {
				$rate = get_post_custom_values('_itemPointrate', $rows['post_id']);
				//$price = $this->getItemPrice($rows['post_id'], $rows['sku']) * $rows['quantity'];
				$price = $rows['price'] * $rows['quantity'];
				$point += $price * $rate[0] / 100;
			}
		}
	
		return ceil($point);
	}
	
	function get_order_discount( $display_mode = '', $cart = array() ) {
		if ( empty($cart) )
			$cart = $this->cart->get_cart();
		
		if ( empty($display_mode) )
			$display_mode = $this->options['display_mode'];
		
		$discount = 0;
		$total = $this->get_total_price( $cart );
		if ( $display_mode == 'Promotionsale' ) {
			if ( $this->options['campaign_privilege'] == 'discount' ){
				if( 0 === (int)$this->options['campaign_category'] ){
					$discount = $total * $this->options['privilege_discount'] / 100;
				}else{
					foreach($cart as $cart_row){
						if( in_category((int)$this->options['campaign_category'], $cart_row['post_id']) ){
							$discount += $cart_row['price'] * $cart_row['quantity'] * $this->options['privilege_discount'] / 100;
						}
					}
				}
			}else if ( $this->options['campaign_privilege'] == 'point' ){
				$discount = 0;
			}
		}

		$discount = ceil($discount * -1);
		$discount = apply_filters('usces_order_discount', $discount, $cart);
		return $discount;
	} 

	function getShippingCharge( $pref, $cart = array(), $entry = array() ) {
		if( empty($cart) )
			$cart = $this->cart->get_cart();
		if( empty($entry) )
			$entry = $this->cart->get_entry();
			
		$d_method_id = $entry['order']['delivery_method'];
		$d_method_index = $this->get_delivery_method_index($d_method_id);
		
		$fixed_charge_id = $this->options['delivery_method'][$d_method_index]['charge'];
		$individual_quant = 0;
		$total_quant = 0;
		$charges = array();
		$individual_charges = array();
		
		foreach ( $cart as $rows ) {
		
			if( -1 == $fixed_charge_id ){
				$s_charge_id = $this->getItemShippingCharge($rows['post_id']);
				$s_charge_index = $this->get_shipping_charge_index($s_charge_id);
				$charge = $this->options['shipping_charge'][$s_charge_index]['value'][$pref];
			}else{
			
				$s_charge_index = $this->get_shipping_charge_index($fixed_charge_id);
				$charge = $this->options['shipping_charge'][$s_charge_index]['value'][$pref];
			}

			
			if($this->getItemIndividualSCharge($rows['post_id'])){
				$individual_quant += $rows['quantity'];
				$individual_charges[] = $rows['quantity'] * $charge;
			}else{
				$charges[] = $charge;
			}
			$total_quant += $rows['quantity'];
		}

		if( count($charges) > 0 ){
			rsort($charges);
			$max_charge = $charges[0];
			$charge = $max_charge + array_sum($individual_charges);
		}else{
			$charge = array_sum($individual_charges);
		}
		
		$charge = apply_filters('usces_filter_getShippingCharge', $charge, $cart, $entry);
		
		return $charge;
	}
	
	function getCODFee($payment_name, $amount_by_cod) {
		$payments = $this->getPayments($payment_name);
		if( 'COD' != $payments['settlement'] ){
			$fee = 0;
		
		}else if( 'change' != $this->options['cod_type'] ){
			$fee = $this->options['cod_fee'];
		
		}else{	
			$price = $amount_by_cod + $this->getTax( $amount_by_cod );
			if( $price <= $this->options['cod_first_amount'] ){
				$fee = $this->options['cod_first_fee'];
			
			}else if( $price >= $this->options['cod_end_amount'] ){
				$fee = $this->options['cod_end_fee'];
			
			}else if( isset($this->options['cod_amounts']) ){
				$last = count( $this->options['cod_amounts'] ) - 1;
				if( $price > $this->options['cod_amounts'][$last] ){
					$fee = $this->options['cod_end_fee'];
					
				}else{
					$fee = 0;
					foreach( $this->options['cod_amounts'] as $key => $value ){
						if( $price <= $value ){
							$fee = $this->options['cod_fees'][$key];
							break;
						}
					}
				}
			}
		}
			
		$fee = apply_filters('usces_filter_getCODFee', $fee, $payment_name, $amount_by_cod);
		return $fee;
	}
	
	function getTax( $total ) {
		if( empty($this->options['tax_rate']) )
			return 0;

		if( $this->options['tax_method'] == 'cutting' )
			$tax = floor($total * $this->options['tax_rate'] / 100);
		elseif($this->options['tax_method'] == 'bring')
			$tax = ceil($total * $this->options['tax_rate'] / 100);
		elseif($this->options['tax_method'] == 'rounding')
			$tax = round($total * $this->options['tax_rate'] / 100);

		return $tax;
	}
	
	function set_cart_fees( $member, &$entries ) {
		$total_items_price = $this->get_total_price();
		if ( empty($this->options['postage_privilege']) || $total_items_price < $this->options['postage_privilege'] ) {
			$shipping_charge = $this->getShippingCharge( $entries['delivery']['pref'] );
		} else {
			$shipping_charge = 0;
		}
		$payments = $this->getPayments( $entries['order']['payment_name'] );
		$discount = $this->get_order_discount();
		$use_point = $entries['order']['usedpoint'];
		$amount_by_cod = $total_items_price - $use_point + $discount + $shipping_charge;
		$cod_fee = $this->getCODFee($entries['order']['payment_name'], $amount_by_cod);
		$total_price = $total_items_price - $use_point + $discount + $shipping_charge + $cod_fee;
		$total_price = apply_filters('usces_filter_set_cart_fees_total_price', $total_price);
		$tax = $this->getTax( $total_price );
		$total_full_price = $total_price + $tax;
		$get_point = $this->get_order_point( $member['ID'] );
		if(0 < (int)$use_point){
			$get_point = ceil( $get_point - ($get_point * $use_point / $total_items_price) );
			if(0 > $get_point)
				$get_point = 0;
		}

		$array = array(
				'total_items_price' => $total_items_price,
				'total_price' => $total_price,
				'total_full_price' => $total_full_price,
				'getpoint' => $get_point,
				'usedpoint' => $use_point,
				'discount' => $discount,
				'shipping_charge' => $shipping_charge,
				'cod_fee' => $cod_fee,
				'tax' => $tax
				);
		$this->cart->set_order_entry( $array );
		$entries = $this->cart->get_entry();
//var_dump($entries);
	}
	
	function getPayments( $payment_name ) {
		foreach ( (array)$this->options['payment_method'] as $id => $array ) {
			if ( $this->options['payment_method'][$id]['name'] == $payment_name )
				break;
		}

		return $this->options['payment_method'][$id];
	}

	function is_maintenance() {
		if ( $this->options['display_mode'] == 'Maintenancemode' )
			return true;
		else
			return false;
	}

//	function maintenance_mode() {
//		if ( $this->is_maintenance() && !is_user_logged_in() && (!strstr($_SERVER['REQUEST_URI'], 'wp-admin') || !strstr($_SERVER['REQUEST_URI'], 'wp-login') ) ) {
//			include ( TEMPLATEPATH . '/maintenance.php ');
//			exit;
//		} elseif ( isset($_GET['uscesmode']) && $_GET['uscesmode'] == 'lostpassword' ) {
//			$error = isset($_GET['error']) ? '?error='.$_GET['error'] : '';
//			include ( TEMPLATEPATH . '/member/changepassword.php ');
//			exit;
//		}
//	}
	
	function get_member_history($mem_id) {
		global $wpdb;
		$order_table = $wpdb->prefix . "usces_order";
	
		$query = $wpdb->prepare("SELECT ID, order_cart, order_condition, order_date, order_usedpoint, order_getpoint, 
								order_discount, order_shipping_charge, order_cod_fee, order_tax, order_status 
							FROM $order_table WHERE mem_id = %d ORDER BY order_date DESC", $mem_id);
		$results = $wpdb->get_results( $query );
	
		$i=0;
		$res = array();
		foreach ( $results as $value ) {
			if(strpos($value->order_status, 'cancel') === false && strpos($value->order_status, 'estimate') === false){
		
				$res[] = array(
							'ID' => $value->ID,
							'cart' => unserialize($value->order_cart),
							'condition' => unserialize($value->order_condition),
							'getpoint' => $value->order_getpoint,
							'usedpoint' => $value->order_usedpoint,
							'discount' => $value->order_discount,
							'shipping_charge' => $value->order_shipping_charge,
							'cod_fee' => $value->order_cod_fee,
							'tax' => $value->order_tax,
							'order_status' => $value->order_status,
							'date' => mysql2date(__('Y/m/d'), $value->order_date),
							'order_date' => $value->order_date
							);
				$i++;
			
			}
		}

		return $res;
	
	}
	
	function get_post_term_ids( $post_id, $taxonomy ){
		global $wpdb;
		$query = $wpdb->prepare("SELECT tt.term_id  FROM $wpdb->term_relationships AS tr 
									INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id 
									WHERE tt.taxonomy = %s AND tr.object_id = %d", $taxonomy, $post_id);
		$ids = $wpdb->get_col( $query );

		return $ids;
	
	}

	function get_tag_names($post_id) {
		global $wpdb;
		$tag = 'post_tag';
		$query = $wpdb->prepare("SELECT t.name  FROM $wpdb->term_relationships AS tr 
									INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id 
									INNER JOIN $wpdb->terms AS t ON t.term_id = tt.term_id 
									WHERE tt.taxonomy = %s AND tr.object_id = %d", $tag, $post_id);
		$names = $wpdb->get_col( $query );

		return $names;
	
	}
	
	function get_ID_byItemName($itemname, $status = 'publish') {
		global $wpdb;
		$meta_key = '_itemCode';
		$query = $wpdb->prepare("SELECT p.ID  FROM $wpdb->posts AS p 
									INNER JOIN $wpdb->postmeta AS pm ON p.ID = pm.post_id 
									WHERE p.post_status = %s AND pm.meta_key = %s AND meta_value = %s ", $status, $meta_key, $itemname);
		$id = $wpdb->get_var( $query );

//		$wpdb->show_errors(); 
//		$wpdb->print_error();

		return $id;
	
	}

	function uscescv( $sessid, $flag ) {
	
		$chars = '';
		$i=0;
		$h=0;
		while($h<strlen($sessid)){
			if(0 == $i % 3){
				$chars .= substr($i, -1);
			}else{
				$chars .= substr($sessid, $h, 1);
				$h++;
			}
			$i++;
		}
//		if( $flag ){
//			$sessid = $chars . '_' . $_SERVER['REMOTE_ADDR'];
//		}else{
			$sessid = $chars . '_acting';
//			$sessid = $chars . apply_filters('usces_sessid_flag', '_acting');
//		}
		$sessid = urlencode(base64_encode($sessid));
		return $sessid;
	}
	
	function uscesdc( $sessid ) {
		$sessid = base64_decode(urldecode($sessid));
		list($sess, $addr) = explode('_', $sessid);
//		if( $addr != $_SERVER['REMOTE_ADDR'] && $addr != 'acting' && $addr != 'mobile' ) {
//			$sessid = '';
//			return;
//		}
		$chars = '';
		$h=0;
		while($h<strlen($sess)){
			if(0 != $h % 3){
				$chars .= substr($sess, $h, 1);
			}
			$h++;
		}
		//var_dump($chars);
		$sessid = $chars;
		
		return $sessid;
		
	}

	function get_visiter( $period ) {
		global $wpdb;
		$datestr = substr(get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 0, 10);
		$yearstr = substr($datestr, 0, 4);
		$monthstr = substr($datestr, 5, 2);
		$daystr = substr($datestr, 8, 2);
		if($period == 'today') {
			$date = $datestr;
			$today = $datestr;
		}else if($period == 'thismonth') {
			$date = date('Y-m-01');
			$today = $datestr;
		}else if($period == 'lastyear') {
			$date = date('Y-m-01', mktime(0, 0, 0, (int)$monthstr, 1, (int)$yearstr-1));
			$today = date('Y-m-01', mktime(0, 0, 0, (int)$monthstr, (int)$daystr, (int)$yearstr-1));
		}
		$table_name = $wpdb->prefix . 'usces_access';
		
		$query = $wpdb->prepare("SELECT SUM(acc_num1) AS ct1, SUM(acc_num2) AS ct2 FROM $table_name WHERE acc_date >= %s AND acc_date <= %s", $date, $today);
		$res = $wpdb->get_row($query, ARRAY_A);
		
		if( $res == NULL )
			return 0;
		else
			return $res['ct1']+$res['ct2'];
	}

	function get_fvisiter( $period ) {
		global $wpdb;
		$datestr = substr(get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 0, 10);
		$yearstr = substr($datestr, 0, 4);
		$monthstr = substr($datestr, 5, 2);
		$daystr = substr($datestr, 8, 2);
		if($period == 'today') {
			$date = $datestr;
			$today = $datestr;
		}else if($period == 'thismonth') {
			$date = date('Y-m-01');
			$today = $datestr;
		}else if($period == 'lastyear') {
			$date = date('Y-m-01', mktime(0, 0, 0, (int)$monthstr, 1, (int)$yearstr-1));
			$today = date('Y-m-01', mktime(0, 0, 0, (int)$monthstr, (int)$daystr, (int)$yearstr-1));
		}
		$table_name = $wpdb->prefix . 'usces_access';
		
		$query = $wpdb->prepare("SELECT SUM(acc_num2) AS ct FROM $table_name WHERE acc_date >= %s AND acc_date <= %s", $date, $today);
		$res = $wpdb->get_var($query);
		
		if( $res == NULL )
			return 0;
		else
			return $res;
	}
	
	function get_order_num( $period ) {
		global $wpdb;
		$datestr = substr(get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 0, 10);
		$yearstr = substr($datestr, 0, 4);
		$monthstr = substr($datestr, 5, 2);
		$daystr = substr($datestr, 8, 2);
		if($period == 'today') {
			$date = date('Y-m-d 00:00:00', current_time('timestamp'));
			$today = date('Y-m-d 23:59:59', current_time('timestamp'));
		}else if($period == 'thismonth') {
			$date = date('Y-m-01 00:00:00', current_time('timestamp'));
			$today = date('Y-m-d 23:59:59', current_time('timestamp'));
		}else if($period == 'lastyear') {
			$date = date('Y-m-01 00:00:00', mktime(0, 0, 0, (int)$monthstr, 1, (int)$yearstr-1));
			$today = date('Y-m-d 23:59:59', mktime(0, 0, 0, (int)$monthstr+1, 0, (int)$yearstr-1));
		}
		$table_name = $wpdb->prefix . 'usces_order';
		
		$query = $wpdb->prepare("SELECT COUNT(ID) AS ct FROM $table_name WHERE order_date >= %s AND order_date <= %s", $date, $today);
		$res = $wpdb->get_var($query);
		
		if( $res == NULL )
			return 0;
		else
			return $res;
	}

	function get_order_amount( $period ) {
		global $wpdb;
		$datestr = substr(get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 0, 10);
		$yearstr = substr($datestr, 0, 4);
		$monthstr = substr($datestr, 5, 2);
		$daystr = substr($datestr, 8, 2);
		if($period == 'today') {
			$date = date('Y-m-d 00:00:00', current_time('timestamp'));
			$today = date('Y-m-d 23:59:59', current_time('timestamp'));
		}else if($period == 'thismonth') {
			$date = date('Y-m-01 00:00:00', current_time('timestamp'));
			$today = date('Y-m-d 23:59:59', current_time('timestamp'));
		}else if($period == 'lastyear') {
			$date = date('Y-m-01 00:00:00', mktime(0, 0, 0, (int)$monthstr, 1, (int)$yearstr-1));
			$today = date('Y-m-d 23:59:59', mktime(0, 0, 0, (int)$monthstr+1, 0, (int)$yearstr-1));
		}
		$table_name = $wpdb->prefix . 'usces_order';
		
		$query = $wpdb->prepare("SELECT 
									SUM(order_item_total_price) AS price, 
									SUM(order_usedpoint) AS point, 
									SUM(order_discount) AS discount, 
									SUM(order_shipping_charge) AS shipping, 
									SUM(order_cod_fee) AS cod, 
									SUM(order_tax) AS tax 
								 FROM $table_name WHERE order_date >= %s AND order_date <= %s", $date, $today);
		$res = $wpdb->get_row($query, ARRAY_A);
		
		if( $res == NULL )
			return 0;
		else
			return $res['price'] - $res['point'] + $res['discount'] + $res['shipping'] + $res['cod'] + $res['tax'];
	}

	function get_items_skus() {
		global $wpdb;
		
		$query = $wpdb->prepare("SELECT ID, meta_key, meta_value FROM {$wpdb->posts} 
									INNER JOIN {$wpdb->postmeta} ON ID = post_id AND SUBSTRING(meta_key, 1, 6) = %s 
									WHERE post_mime_type = %s AND post_status = %s 
									ORDER BY ID, meta_key", 
									'_isku_', 'item', 'publish');
		$res = $wpdb->get_results($query, ARRAY_A);
		
		$sku = array();
		$status = array();
		foreach((array)$res as $key => $value){
			$sku['data'][$key]['ID'] = $value['ID'];
			$sku['data'][$key]['code'] = $this->getItemCode($value['ID']);
			$sku['data'][$key]['name'] = $this->getItemName($value['ID']);
			$sku['data'][$key]['sku'] = substr($value['meta_key'], 6);
			$sku['data'][$key]['num'] = $this->getItemZaikoNum($value['ID'], $sku['data'][$key]['sku']);
			$status[] = $this->getItemZaiko($value['ID'], $sku['data'][$key]['sku']);
		}
		$sku['count'] = array_count_values($status);
		return $sku;
	}

	function is_status($need, $str){
		$array = explode(',', $str);
		return in_array($need, $array);
	}
	
	function make_status( $taio='', $receipt='', $admin='' ){
		$str = '';
		if($taio != '' && $taio != '#none#')
		 	$str .= $taio . ',';
		if($receipt != '' && $receipt != '#none#')
		 	$str .= $receipt . ',';
		if($admin != '' && $admin != '#none#')
		 	$str .= $admin . ',';
		return $str;
	}
	
	function get_memberid_by_email($email){
		return;
	}
	
	function get_condition(){
		$order_conditions = array(
		'display_mode' => $this->options['display_mode'],
		'campaign_privilege' => $this->options['campaign_privilege'],
		'privilege_point' => $this->options['privilege_point'],
		'privilege_discount' => $this->options['privilege_discount']);
		return $order_conditions;
	}
	
	function get_bestseller_ids( $days = "" ){
		global $wpdb;
		$datestr = substr(get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 0, 10);
		$yearstr = substr($datestr, 0, 4);
		$monthstr = substr($datestr, 5, 2);
		$daystr = substr($datestr, 8, 2);
		$res = array();
		$order_table_name = $wpdb->prefix . "usces_order";
		$where = "";
		if($days != ''){
			$order_date = date('Y-m-d H:i:s', mktime(0, 0, 0, (int)$monthstr, ((int)$daystr-$days), (int)$yearstr));
			$where = " WHERE order_date >= '{$order_date}'";
		}
		$query = "SELECT order_cart FROM {$order_table_name}" . $where;
		$dbres = $wpdb->get_col($query);
		if(!$dbres) return false;
		
		foreach((array)$dbres as $carts){
			$rows = unserialize($carts);
			foreach((array)$rows as $carts){
				$id = $carts['post_id'];
				$qu = $carts['quantity'];
				if(array_key_exists($id, $res)){
					$res[$id] = $res[$id] + $qu;
				}else{
					$res[$id] = $qu;
				}
			}
		}
		arsort($res);
		$results = array_keys($res);
		return $results;
	}
	
	function get_items_num(){
		global $wpdb;
		$query = $wpdb->prepare("SELECT COUNT(ID) AS ct FROM {$wpdb->posts} WHERE post_mime_type = %s AND post_status = %s", 
								'item', 'publish');
		$res = $wpdb->get_var($query);

		return $res;
	}
	
	function is_gptekiyo( $post_id, $sku, $quant ) {
		$skus = $this->get_skus( $post_id, 'ARRAY_A' );
		if( !$skus[$sku]['gptekiyo'] ) return false;

		$GpN1 = $this->getItemGpNum1($post_id);
		$GpN2 = $this->getItemGpNum2($post_id);
		$GpN3 = $this->getItemGpNum3($post_id);
	
		if( empty($GpN1) ) {
		
				return false;
				
		}else if( !empty($GpN1) && empty($GpN2) ) {
		
			if( $quant >= $GpN1 ) {
				return true;
			}else{
				return false;
			}
			
		}else if( !empty($GpN1) && !empty($GpN2) && empty($GpN3) ) {
		
			if( $quant >= $GpN2 ) {
				return true;
			}else if( $quant >= $GpN1 && $quant < $GpN2 ) {
				return true;
			}else{
				return false;
			}
			
		}else if( !empty($GpN1) && !empty($GpN2) && !empty($GpN3) ) {
		
			if( $quant >= $GpN3 ) {
				return true;
			}else if( $quant >= $GpN2 && $quant < $GpN3 ) {
				return true;
			}else if( $quant >= $GpN1 && $quant < $GpN2 ) {
				return true;
			}else{
				return false;
			}
		}
	}
	
	function get_available_delivery_method() { 
		if($this->cart->num_row() > 0) {
			$cart = $this->cart->get_cart();
			$before_deli = array();
			$intersect = array();
			foreach($cart as $key => $row){
				$deli = $this->getItemDeliveryMethod($row['post_id']);
				if(!is_array($deli)) {
					return array();
				}
				if( $key === 0 ){
					$intersect = $deli;
				}
				$intersect = array_intersect($deli, $intersect);
				$before_deli = $deli;
				foreach($deli as $value){
					$integration[] = $value;
				}
			}
			$integration = array_unique($integration);
			foreach($integration as $id){
				$index = $this->get_delivery_method_index($id);
				$temp[$index] = $id;
			}
			ksort($temp);
			if(!$intersect){
				$deli = array();
				$deli[0] = (int)$temp[0];
				return $deli;
			}else{
				return $intersect;
			}
		}
		return array();
	}

	function get_delivery_method_index($id) {
		$index = false; 
		for($i=0; $i<count($this->options['delivery_method']); $i++){
			if( $this->options['delivery_method'][$i]['id'] === (int)$id ){
				$index = $i;
			}
		}
		if($index === false)
			return -1;
		else
			return $index;
	}

	function get_shipping_charge_index($id) {
		$index = false; 
		for($i=0; $i<count($this->options['shipping_charge']); $i++){
			if( $this->options['shipping_charge'][$i]['id'] === $id ){
				$index = $i;
			}
		}
		if($index === false)
			return -1;
		else
			return $index;
	}
	
	function get_initial_data($xml){
		$buf = file_get_contents($xml);
		preg_match_all('@<page>.*?<post_title>(.*?)</post_title>.*?<post_status>(.*?)</post_status>.*?<post_name>(.*?)</post_name>.*?<post_content>(.*?)</post_content>.*?</page>@s', $buf, $match, PREG_SET_ORDER);
		return $match;
	}

	function getCurrencySymbol(){
		global $usces_settings;
		$cr = $this->options['system']['currency'];
		list($code, $decimal, $point, $seperator, $symbol) = $usces_settings['currency'][$cr];
		return $symbol;
	}

	function getCartItemName($post_id, $sku){
		$name_arr = array();
		$name_str = '';
		
		foreach($this->options['indi_item_name'] as $key => $value){
			if($value){
				$pos = (int)$this->options['pos_item_name'][$key];
				$ind = ($pos === 0) ? 'A' : $pos;
				switch($key){
					case 'item_name':
						$name_arr[$ind][$key] = $this->getItemName($post_id);
						break;
					case 'item_code':
						$name_arr[$ind][$key] = $this->getItemCode($post_id);
						break;
					case 'sku_name':
						$name_arr[$ind][$key] = $this->getItemSkuDisp($post_id, $sku);
						break;
					case 'sku_code':
						$name_arr[$ind][$key] = $sku;
						break;
				}
			}
			
		}
		ksort($name_arr);
		foreach($name_arr as $vals){
			foreach($vals as $key => $value){
			
				$name_str .= $value . ' ';
			}
		}
		
		$name_str = apply_filters('usces_admin_order_item_name_filter', $name_str);
		
		return trim($name_str);
	}
	
	function set_reserve_pre_order_id(){
		$entry = $this->cart->get_entry();
		$id = ( isset($entry['reserve']['pre_order_id']) && !empty($entry['reserve']['pre_order_id']) ) ? $entry['reserve']['pre_order_id'] : uniqid('');
		$this->cart->set_pre_order_id($id);
	}

	function get_current_pre_order_id(){
		$entry = $this->cart->get_entry();
		$id = ( isset($entry['reserve']['pre_order_id']) && !empty($entry['reserve']['pre_order_id']) ) ? $entry['reserve']['pre_order_id'] : NULL;
		return $id;
	}

	function get_reserve($order_id, $key){
		global $wpdb;
		$order_meta_table_name = $wpdb->prefix . "usces_order_meta";
		$query = $wpdb->prepare("SELECT meta_value FROM $order_meta_table_name WHERE order_id = %d AND meta_key = %s", 
								$order_id, $key);
		$res = $wpdb->get_var($query);
		return $res;
	}

//20100818ysk start
//20100816ysk start
	//function get_order_meta($order_id, $key) {
	function get_order_meta_value($key, $order_id) {
		global $wpdb;
		$order_meta_table_name = $wpdb->prefix . "usces_order_meta";
		$query = $wpdb->prepare("SELECT meta_value FROM $order_meta_table_name WHERE order_id = %d AND meta_key = %s", 
								$order_id, $key);
		$res = $wpdb->get_var($query);
		return $res;
	}
//20100816ysk end
	function set_order_meta_value($key, $meta_value, $order_id) {
		global $wpdb;

		//if( empty($meta_value) ) return;
		if( empty($order_id) ) return;

		$table_name = $wpdb->prefix . "usces_order_meta";
		$query = $wpdb->prepare("SELECT count(*) FROM $table_name WHERE order_id = %d AND meta_key = %s", 
								$order_id, $key);
		$res = $wpdb->get_var($query);
		if(0 < $res) {
			$query = $wpdb->prepare("UPDATE $table_name SET meta_value = %s WHERE order_id = %d AND meta_key = %s", 
									$meta_value, 
									$order_id, 
									$key
									);
			$res2 = $wpdb->query($query);
		} else {
			$query = $wpdb->prepare("INSERT INTO  $table_name (order_id, meta_key, meta_value) 
									VALUES(%d, %s, %s)", 
									$order_id, 
									$key, 
									$meta_value
									);
			$res2 = $wpdb->query($query);
		}
		return $res2;
	}

	function set_session_custom_member($member_id) {
		unset($_SESSION['usces_member']['custom_member']);
		$meta = usces_has_custom_field_meta('member');
		if(is_array($meta)) {
			$keys = array_keys($meta);
			foreach($keys as $key) {
				$csmb_key = 'csmb_'.$key;
				$_SESSION['usces_member']['custom_member'][$key] = maybe_unserialize($this->get_member_meta_value($csmb_key, $member_id));
			}
		}
	}

	function reg_custom_member($member_id) {
		if( !empty($_POST['custom_member']) ) {
			foreach( $_POST['custom_member'] as $key => $value ) {
				$csmb_key = 'csmb_'.$key;
				if( is_array($value) ) 
					 $value = serialize($value);
				$res = $this->set_member_meta_value($csmb_key, $value, $member_id);
				if(false === $res) 
					return false;
			}
		}
	}
//20100818ysk end

	function save_order_acting_data($rand){
		global $wpdb;
		$data = serialize(array( 'cart' => $this->cart->get_cart(), 'entry' => $this->cart->get_entry() ));
		$table_name = $wpdb->prefix . "usces_access";
		$query = $wpdb->prepare("INSERT INTO  $table_name (acc_type, acc_str1, acc_date, acc_key, acc_value) 
								VALUES(%s, %s, now(), %s, %s)", 
								'acting_data', 
								$this->get_uscesid(false), 
								$rand, 
								$data								
								);
		$res = $wpdb->query($query);
		return $res;
	}

//20100818ysk start
	function set_member_meta_value($key, $meta_value, $member_id = ''){
		global $wpdb;

		//if( empty($meta_value) ) return;
		if('' === $member_id) {
			if( !$this->is_member_logged_in() ) return;
			$member = $this->get_member();
			$member_id = $member['ID'];
		}

		$table_name = $wpdb->prefix . "usces_member_meta";
		//$query = $wpdb->prepare("SELECT meta_value FROM $table_name WHERE member_id = %d AND meta_key = %s", 
		$query = $wpdb->prepare("SELECT count(*) FROM $table_name WHERE member_id = %d AND meta_key = %s", 
								$member_id, $key);
		$res = $wpdb->get_var($query);
		//if($res != NULL){
		if(0 < $res){
			$query = $wpdb->prepare("UPDATE $table_name SET meta_value = %s WHERE member_id = %d AND meta_key = %s", 
									$meta_value, 
									$member_id, 
									$key
									);
			$res2 = $wpdb->query($query);
		}else{
			$query = $wpdb->prepare("INSERT INTO  $table_name (member_id, meta_key, meta_value) 
									VALUES(%d, %s, %s)", 
									$member_id, 
									$key, 
									$meta_value
									);
			$res2 = $wpdb->query($query);
		}
		return $res2;
	}
//20100818ysk end

	function get_member_meta_value($key, $member_id){
		global $wpdb;
		$table_name = $wpdb->prefix . "usces_member_meta";
		$query = $wpdb->prepare("SELECT meta_value FROM $table_name WHERE member_id = %d AND meta_key = %s", 
								$member_id, $key);
		$res = $wpdb->get_var($query);
		return $res;
	}
	
	function del_member_meta($key, $member_id){
		global $wpdb;
		$table_name = $wpdb->prefix . "usces_member_meta";
		$query = $wpdb->prepare("DELETE FROM $table_name WHERE member_id = %d AND meta_key = %s", 
								$member_id, $key);
		$res = $wpdb->query($query);
		return $res;
	}
	
	function get_member_meta($member_id){
		global $wpdb;
		$table_name = $wpdb->prefix . "usces_member_meta";
		$query = $wpdb->prepare("SELECT * FROM $table_name WHERE member_id = %d AND meta_key <> 'customer_country' AND meta_key NOT LIKE %s", $member_id, 'csmb_%');
		$res = $wpdb->get_results($query, ARRAY_A);
		return $res;
	}
	
	function get_settle_info_field( $order_id ){
		global $wpdb;
		$fields = array();
		$table_name = $wpdb->prefix . "usces_order_meta";
		$query = $wpdb->prepare("SELECT meta_key, meta_value FROM $table_name WHERE order_id = %d AND (meta_key LIKE %s OR meta_key = %s)", 
								$order_id, 'acting_%', 'settlement_id');
		$res = $wpdb->get_results($query, ARRAY_A);
		if( !$res )
			return $fields;
			
		foreach( $res as $value ){
			if( 'settlement_id' == $value['meta_key'] ){
				$meta_values = maybe_unserialize($value['meta_value']);
				if( is_array($meta_values) ){
					foreach( $meta_values as $key => $meta_value ){
						$fields[$key] = $meta_value;
					}
				}else{
					$fields['settlement_id'] = $meta_values;
				}
			}elseif( 'acting_' == substr($value['meta_key'], 0, 7) ){
				$meta_values = unserialize($value['meta_value']);
				foreach( $meta_values as $key => $meta_value ){
					$fields[$key] = $meta_value;
				}
			}
		}
		return $fields;
	}
	
	function get_post_custom($post_id, $orderby='meta_id', $order='ASC'){
		global $wpdb;
		$table = $wpdb->prefix . "postmeta";
		$meta_list = $wpdb->get_results( $wpdb->prepare("SELECT meta_key, meta_value FROM $table WHERE post_id = %d ORDER BY $orderby $order",
			$post_id), ARRAY_A );
			
		if ( !empty($meta_list) ) {
			foreach ( $meta_list as $metarow) {
				$mkey = $metarow['meta_key'];
				$mval = $metarow['meta_value'];
				$res[$mkey][] = $mval;
			}
		}
		return $res;
	}
	
	function get_currency($amount, $symbol_pre = false, $symbol_post = false, $seperator_flag = true ){
		global $usces_settings;
		$cr = $this->options['system']['currency'];
		list($code, $decimal, $point, $seperator, $symbol) = $usces_settings['currency'][$cr];
		if( !$seperator_flag ){
			$seperator = '';
		}
		$price = number_format($amount, $decimal, $point, $seperator);

		if( $symbol_pre )
			$price = ( usces_is_entity($symbol) ? mb_convert_encoding($symbol, 'UTF-8', 'HTML-ENTITIES') : $symbol ) . $price;
			
		if( $symbol_post )
			$price = $price . __($code, 'usces');
			
		return $price;
	}
	
	function get_currency_code(){
		global $usces_settings;
		$cr = $this->options['system']['currency'];
		list($code, $decimal, $point, $seperator, $symbol) = $usces_settings['currency'][$cr];
		return $code;
	}
	
	//shortcode-----------------------------------------------------------------------------
	function sc_company_name() {
		return htmlspecialchars($this->options['company_name']);
	}
	function sc_zip_code() {
		return htmlspecialchars($this->options['zip_code']);
	}
	function sc_address1() {
		return htmlspecialchars($this->options['address1']);
	}
	function sc_address2() {
		return htmlspecialchars($this->options['address2']);
	}
	function sc_tel_number() {
		return htmlspecialchars($this->options['tel_number']);
	}
	function sc_fax_number() {
		return htmlspecialchars($this->options['fax_number']);
	}
	function sc_inquiry_mail() {
		return htmlspecialchars($this->options['inquiry_mail']);
	}
	function sc_payment() {
		$payments = $this->options['payment_method'];
		$htm = "<ul>\n";
		foreach ( (array)$payments as $payment ) {
			$htm .= "<li>" . htmlspecialchars($payment['name']) . "<br />\n";
			$htm .= nl2br(htmlspecialchars($payment['explanation'])) . "</li>\n";
		}
		$htm .= "</ul>\n";
		return $htm;
	}
	function sc_payment_title() {
		$payments = $this->options['payment_method'];
		$htm = "<ul>\n";
		foreach ( (array)$payments as $payment ) {
			$htm .= "<li>" . esc_html($payment['name']) . "</li>\n";
		}
		$htm .= "</ul>\n";
		return $htm;
	}
	function sc_cod_fee() {
		return number_format($this->options['cod_fee']);
	}
	function sc_start_point() {
		return number_format($this->options['start_point']);
	}
	function sc_postage_privilege() {
		if(empty($this->options['postage_privilege'])) 
			return;
		return number_format($this->options['postage_privilege']);
	}
	function sc_shipping_charge() {
		$arr = array();
		foreach ( (array)$this->options['shipping_charge'] as $charges ) {
			foreach ( (array)$charges['value'] as $value ) {
				$arr[] = $value;
			}
		}
		sort($arr);
		$min = $arr[0];
		rsort($arr);
		$max = $arr[0];
		if($min == $max){
			$res = number_format($min);
		}else{
			$res = number_format($min) . __(' - ', 'usces') . number_format($max);
		}
		return $res;
	}
	function sc_site_url() {
		return get_option('home');
	}
	function sc_button_to_cart($atts) {
		extract(shortcode_atts(array(
			'item' => '',
			'sku' => '',
			'value' => __('to the cart', 'usces'),
		), $atts));
	
		$post_id = $this->get_ID_byItemName($item);
		if( ! $this->is_item_zaiko( $post_id, $sku ) ){
			return '<div class="button_status">' . __('Sold Out', 'usces') . '</div>';
		}
		
		$datas = $this->get_skus( $post_id, 'ARRAY_A' );
	
		$zaikonum = $datas[$sku]['zaikonum'];
		$zaiko = $datas[$sku]['zaiko'];
		$gptekiyo = $datas[$sku]['gptekiyo'];
		$skuPrice = $datas[$sku]['price'];
		
		$html = "<form action=\"" . USCES_CART_URL . "\" method=\"post\">\n";
		$html .= "<input name=\"zaikonum[{$post_id}][{$sku}]\" type=\"hidden\" id=\"zaikonum[{$post_id}][{$sku}]\" value=\"{$zaikonum}\" />\n";
		$html .= "<input name=\"zaiko[{$post_id}][{$sku}]\" type=\"hidden\" id=\"zaiko[{$post_id}][{$sku}]\" value=\"{$zaiko}\" />\n";
		$html .= "<input name=\"gptekiyo[{$post_id}][{$sku}]\" type=\"hidden\" id=\"gptekiyo[{$post_id}][{$sku}]\" value=\"{$gptekiyo}\" />\n";
		$html .= "<input name=\"skuPrice[{$post_id}][{$sku}]\" type=\"hidden\" id=\"skuPrice[{$post_id}][{$sku}]\" value=\"{$skuPrice}\" />\n";
		$html .= "<input name=\"inCart[{$post_id}][{$sku}]\" type=\"submit\" id=\"inCart[{$post_id}][{$sku}]\" class=\"skubutton\" value=\"{$value}\" />";
		$html .= "<input name=\"usces_referer\" type=\"hidden\" value=\"" . $_SERVER['REQUEST_URI'] . "\" />\n";
		$html = apply_filters('usces_filter_single_item_inform', $html);
		$html .= "</form>";
		$html .= '<div class="error_message">' . usces_singleitem_error_message($post_id, $sku, 'return') . '</div>'."\n";
		
		return $html;
	}

	function filter_itemPage($content){
		global $post;

		if( ($post->post_mime_type != 'item' || !is_single()) ) return $content;
		if( post_password_required($post) ) return $content;
		
		$temp_path = apply_filters('usces_template_path_single_item', USCES_PLUGIN_DIR . '/templates/single_item.php');
		include( $temp_path );
		
		$content = apply_filters('usces_filter_itemPage', $html, $post->ID);

		return $content;
	}

	function filter_cartContent($content) {
		global $post;
		
		switch($this->page){
			case 'cart':
				$temp_path = apply_filters('usces_template_path_cart', USCES_PLUGIN_DIR . '/templates/cart/cart.php');
				include( $temp_path );
				break;
			case 'customer':
				$temp_path = apply_filters('usces_template_path_customer', USCES_PLUGIN_DIR . '/templates/cart/customer_info.php');
				include( $temp_path );
				break;
			case 'delivery':
				$temp_path = apply_filters('usces_template_path_delivery', USCES_PLUGIN_DIR . '/templates/cart/delivery_info.php');
				include( $temp_path );
				break;
			case 'confirm':
				$temp_path = apply_filters('usces_template_path_confirm', USCES_PLUGIN_DIR . '/templates/cart/confirm.php');
				include( $temp_path );
				break;
			case 'ordercompletion':
				$temp_path = apply_filters('usces_template_path_ordercompletion', USCES_PLUGIN_DIR . '/templates/cart/completion.php');
				include( $temp_path );
				break;
			case 'error':
				$temp_path = apply_filters('usces_template_path_carterror', USCES_PLUGIN_DIR . '/templates/cart/error.php');
				include( $temp_path );
				break;
			case 'maintenance':
				$temp_path = apply_filters('usces_template_path_maintenance', USCES_PLUGIN_DIR . '/templates/cart/maintenance.php');
				include( $temp_path );
				break;
			case 'search_item':
				$temp_path = apply_filters('usces_template_path_search_item', USCES_PLUGIN_DIR . '/templates/search_item.php');
				include( $temp_path );
				break;
			case 'wp_search':
				if($post->post_mime_type == 'item'){
					$temp_path = apply_filters('usces_template_path_wp_search', USCES_PLUGIN_DIR . '/templates/wp_search_item.php');
					include( $temp_path );
				}else{
					$html = $content;
				}
				break;
			default:
				$html = $content;
		}
		
		if( $this->use_ssl && ($this->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $this->is_inquiry_page($_SERVER['REQUEST_URI'])) )
			$html = str_replace('src="'.get_option('siteurl'), 'src="'.USCES_SSL_URL_ADMIN, $html);

		$html = apply_filters('usces_filter_cartContent', $html);

		$content = $html;
		
		remove_filter('the_title', array($this, 'filter_cartTitle'));

		return $content;
	}

	function filter_cartTitle($title) {

		if( $title == 'Cart' || $title == __('Cart', 'usces') ){
			switch($this->page){
				case 'cart':
					$newtitle = apply_filters('usces_filter_title_cart', __('In the cart', 'usces'));
					break;
				case 'customer':
					$newtitle = apply_filters('usces_filter_title_customer', __('Customer Information', 'usces'));
					break;
				case 'delivery':
					$newtitle = apply_filters('usces_filter_title_delivery', __('Shipping / Payment options', 'usces'));
					break;
				case 'confirm':
					$newtitle = apply_filters('usces_filter_title_confirm', __('Confirmation', 'usces'));
					break;
				case 'ordercompletion':
					$newtitle = apply_filters('usces_filter_title_ordercompletion', __('Completion', 'usces'));
					break;
				case 'error':
					$newtitle = apply_filters('usces_filter_title_carterror', __('Error', 'usces'));
					break;
				case 'search_item':
					$newtitle = apply_filters('usces_filter_title_search_item', __("'AND' search by categories", 'usces'));
					break;
				case 'maintenance':
					$newtitle = apply_filters('usces_filter_title_maintenance', __('Under Maintenance', 'usces'));
					break;
				case 'login':
					$newtitle = apply_filters('usces_filter_title_login', __('Log-in for members', 'usces'));
					break;
				default:
					$newtitle = apply_filters('usces_filter_title_cart_default', $title);
			}
		}else{
			$newtitle = $title;
		}
	
		$newtitle = apply_filters('usces_filter_cartTitle', $newtitle);
		return $newtitle;
	}
	
	function action_cartFilter(){
		add_filter('the_title', array($this, 'filter_cartTitle'),20);
		add_filter('the_content', array($this, 'filter_cartContent'),20);
	}
		
	function action_search_item(){
		include(TEMPLATEPATH . '/page.php');
		exit;
	}
		
	function filter_memberContent($content) {
		global $post;
		
		if($this->options['membersystem_state'] == 'activate'){
		
			if( $this->is_member_logged_in() ) {
			
				$member_regmode = 'editmemberform';
				$temp_path = apply_filters('usces_template_path_member', USCES_PLUGIN_DIR . '/templates/member/member.php');
				include( $temp_path );
			
			} else {
			
				switch($this->page){
					case 'login':
						$temp_path = apply_filters('usces_template_path_login', USCES_PLUGIN_DIR . '/templates/member/login.php');
						include( $temp_path );
						break;
					case 'lostmemberpassword':
						$temp_path = apply_filters('usces_template_path_lostpassword', USCES_PLUGIN_DIR . '/templates/member/lostpassword.php');
						include( $temp_path );
						break;
					case 'changepassword':
						$temp_path = apply_filters('usces_template_path_changepassword', USCES_PLUGIN_DIR . '/templates/member/changepassword.php');
						include( $temp_path );
						break;
					case 'newcompletion':
					case 'editcompletion':
					case 'lostcompletion':
					case 'changepasscompletion':
						$temp_path = apply_filters('usces_template_path_membercompletion', USCES_PLUGIN_DIR . '/templates/member/completion.php');
						include( $temp_path );
						break;
					case 'newmemberform':
						$member_form_title = apply_filters('usces_filter_title_newmemberform', __('New enrollment form', 'usces'));
						$member_regmode = 'newmemberform';
						$temp_path = apply_filters('usces_template_path_member_form', USCES_PLUGIN_DIR . '/templates/member/member_form.php');
						include( $temp_path );
						break;
					default:
						$temp_path = apply_filters('usces_template_path_login', USCES_PLUGIN_DIR . '/templates/member/login.php');
						include( $temp_path );
				}
			
			}
		}else{
			$html .= "<p>只今会員サービスは提供いたしておりません。</p>";
		}
		
		$content = $html;
		
		remove_filter('the_title', array($this, 'filter_memberTitle'));

		return $content;
	}

	function filter_memberTitle($title) {

		if( $this->options['membersystem_state'] == 'activate' && ($title == 'Member' || $title == __('Membership', 'usces')) ){
			switch($this->page){
				case 'login':
					$newtitle = apply_filters('usces_filter_title_login', __('Log-in for members', 'usces'));
					break;
				case 'newmemberform':
					$newtitle = apply_filters('usces_filter_title_newmemberform', __('New enrollment form', 'usces'));
					break;
				case 'lostmemberpassword':
					$newtitle = apply_filters('usces_filter_title_lostmemberpassword', __('The new password acquisition', 'usces'));
					break;
				case 'changepassword':
					$newtitle = apply_filters('usces_filter_title_changepassword', __('Change password', 'usces'));
					break;
				case 'newcompletion':
				case 'editcompletion':
				case 'lostcompletion':
				case 'changepasscompletion':
					$newtitle = apply_filters('usces_filter_title_changepasscompletion', __('Completion', 'usces'));
					break;
				case 'error':
					$newtitle = apply_filters('usces_filter_title_membererror', __('Error', 'usces'));
					break;
				default:
					$newtitle = apply_filters('usces_filter_title_member_default', $title);
			}
		}else{
			$newtitle = $title;
		}
	
		$newtitle = apply_filters('usces_filter_memberTitle', $newtitle);
		return $newtitle;
	}
	
	function action_memberFilter(){
		add_filter('the_title', array($this, 'filter_memberTitle'),20);
		add_filter('the_content', array($this, 'filter_memberContent'),20);
	}

	function filter_usces_cart_css(){
		$path = get_stylesheet_directory_uri() . '/usces_cart.css';
		return $path;
	}
	
	function filter_divide_item(){
		global $wp_query;


		if( $this->options['divide_item'] && !is_category() && !is_search() && !is_singular() && !is_admin() ){
			$ids = $this->getItemIds( 'front' );
			$wp_query->query_vars['post__not_in'] = $ids;
			
		}
		if( is_admin() ){
			$ids = $this->getItemIds( 'back' );
			//$wp_query->query_vars['category__not_in'] = array(USCES_ITEM_CAT_PARENT_ID); 
			$wp_query->query_vars['post__not_in'] = $ids;
		}
		do_action( 'usces_action_divide_item');
	}

	function load_upload_template(){
		$post_id = $_POST['post_id'];
		$file = 'upload_template01.php';
		include(TEMPLATEPATH . '/' . $file);
		exit;
	}
		
	function filter_itemimg_anchor_rel($html){
	
		if( is_single() ){
			$str = ' rel="' . $this->options['itemimg_anchor_rel'] . '"';
		}else{
			$str = '';
		}
		return $html . $str;
	}
	
	function filter_permalink( $link ) {
		
		if(false !== strpos('?page_id=4', $link) || false !== strpos('?page_id=3', $link) || false !== strpos('usces-cart', $link) || false !== strpos('usces-member', $link) )
			$link = str_replace('http://', 'https://', $link);
	
		return $link;
	}

	function filter_cart_page_header($html){
		if( !empty($this->options['cart_page_data']['header']['cart']) ){
			$html = $this->options['cart_page_data']['header']['cart'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_cart_page_footer($html){
		if( !empty($this->options['cart_page_data']['footer']['cart']) ){
			$html = $this->options['cart_page_data']['footer']['cart'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_customer_page_header($html){
		if( !empty($this->options['cart_page_data']['header']['customer']) ){
			$html = $this->options['cart_page_data']['header']['customer'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_customer_page_footer($html){
		if( !empty($this->options['cart_page_data']['footer']['customer']) ){
			$html = $this->options['cart_page_data']['footer']['customer'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_delivery_page_header($html){
		if( !empty($this->options['cart_page_data']['header']['delivery']) ){
			$html = $this->options['cart_page_data']['header']['delivery'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_delivery_page_footer($html){
		if( !empty($this->options['cart_page_data']['footer']['delivery']) ){
			$html = $this->options['cart_page_data']['footer']['delivery'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_confirm_page_header($html){
		if( !empty($this->options['cart_page_data']['header']['confirm']) ){
			$html = $this->options['cart_page_data']['header']['confirm'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_confirm_page_footer($html){
		if( !empty($this->options['cart_page_data']['footer']['confirm']) ){
			$html = $this->options['cart_page_data']['footer']['confirm'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_cartcompletion_page_header($html){
		if( !empty($this->options['cart_page_data']['header']['completion']) ){
			$html = $this->options['cart_page_data']['header']['completion'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_cartcompletion_page_footer($html){
		if( !empty($this->options['cart_page_data']['footer']['completion']) ){
			$html = $this->options['cart_page_data']['footer']['completion'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_login_page_header($html){
		if( !empty($this->options['member_page_data']['header']['login']) ){
			$html = $this->options['member_page_data']['header']['login'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_login_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['login']) ){
			$html = $this->options['member_page_data']['footer']['login'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_newmember_page_header($html){
		if( !empty($this->options['member_page_data']['header']['newmember']) ){
			$html = $this->options['member_page_data']['header']['newmember'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_newmember_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['newmember']) ){
			$html = $this->options['member_page_data']['footer']['newmember'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_newpass_page_header($html){
		if( !empty($this->options['member_page_data']['header']['newpass']) ){
			$html = $this->options['member_page_data']['header']['newpass'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_newpass_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['newpass']) ){
			$html = $this->options['member_page_data']['footer']['newpass'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_changepass_page_header($html){
		if( !empty($this->options['member_page_data']['header']['changepass']) ){
			$html = $this->options['member_page_data']['header']['changepass'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_changepass_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['changepass']) ){
			$html = $this->options['member_page_data']['footer']['changepass'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_memberinfo_page_header($html){
		if( !empty($this->options['member_page_data']['header']['memberinfo']) ){
			$html = $this->options['member_page_data']['header']['memberinfo'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_memberinfo_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['memberinfo']) ){
			$html = $this->options['member_page_data']['footer']['memberinfo'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_membercompletion_page_header($html){
		if( !empty($this->options['member_page_data']['header']['completion']) ){
			$html = $this->options['member_page_data']['header']['completion'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function filter_membercompletion_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['completion']) ){
			$html = $this->options['member_page_data']['footer']['completion'];
		}
		return do_shortcode( stripslashes($html) );
	}
	
	function action_cart_page_header(){
		if( !empty($this->options['cart_page_data']['header']['cart']) ){
			$html = $this->options['cart_page_data']['header']['cart'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_cart_page_footer(){
		if( !empty($this->options['cart_page_data']['footer']['cart']) ){
			$html = $this->options['cart_page_data']['footer']['cart'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_customer_page_header(){
		if( !empty($this->options['cart_page_data']['header']['customer']) ){
			$html = $this->options['cart_page_data']['header']['customer'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_customer_page_footer(){
		if( !empty($this->options['cart_page_data']['footer']['customer']) ){
			$html = $this->options['cart_page_data']['footer']['customer'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_delivery_page_header(){
		if( !empty($this->options['cart_page_data']['header']['delivery']) ){
			$html = $this->options['cart_page_data']['header']['delivery'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_delivery_page_footer(){
		if( !empty($this->options['cart_page_data']['footer']['delivery']) ){
			$html = $this->options['cart_page_data']['footer']['delivery'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_confirm_page_header(){
		if( !empty($this->options['cart_page_data']['header']['confirm']) ){
			$html = $this->options['cart_page_data']['header']['confirm'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_confirm_page_footer(){
		if( !empty($this->options['cart_page_data']['footer']['confirm']) ){
			$html = $this->options['cart_page_data']['footer']['confirm'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_cartcompletion_page_header(){
		if( !empty($this->options['cart_page_data']['header']['completion']) ){
			$html = $this->options['cart_page_data']['header']['completion'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_cartcompletion_page_footer(){
		if( !empty($this->options['cart_page_data']['footer']['completion']) ){
			$html = $this->options['cart_page_data']['footer']['completion'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_login_page_header(){
		if( !empty($this->options['member_page_data']['header']['login']) ){
			$html = $this->options['member_page_data']['header']['login'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_login_page_footer(){
		if( !empty($this->options['member_page_data']['footer']['login']) ){
			$html = $this->options['member_page_data']['footer']['login'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_newmember_page_header(){
		if( !empty($this->options['member_page_data']['header']['newmember']) ){
			$html = $this->options['member_page_data']['header']['newmember'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_newmember_page_footer(){
		if( !empty($this->options['member_page_data']['footer']['newmember']) ){
			$html = $this->options['member_page_data']['footer']['newmember'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_newpass_page_header(){
		if( !empty($this->options['member_page_data']['header']['newpass']) ){
			$html = $this->options['member_page_data']['header']['newpass'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_newpass_page_footer(){
		if( !empty($this->options['member_page_data']['footer']['newpass']) ){
			$html = $this->options['member_page_data']['footer']['newpass'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_changepass_page_header(){
		if( !empty($this->options['member_page_data']['header']['changepass']) ){
			$html = $this->options['member_page_data']['header']['changepass'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_changepass_page_footer(){
		if( !empty($this->options['member_page_data']['footer']['changepass']) ){
			$html = $this->options['member_page_data']['footer']['changepass'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_memberinfo_page_header(){
		if( !empty($this->options['member_page_data']['header']['memberinfo']) ){
			$html = $this->options['member_page_data']['header']['memberinfo'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_memberinfo_page_footer(){
		if( !empty($this->options['member_page_data']['footer']['memberinfo']) ){
			$html = $this->options['member_page_data']['footer']['memberinfo'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_membercompletion_page_header(){
		if( !empty($this->options['member_page_data']['header']['completion']) ){
			$html = $this->options['member_page_data']['header']['completion'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
	function action_membercompletion_page_footer(){
		if( !empty($this->options['member_page_data']['footer']['completion']) ){
			$html = $this->options['member_page_data']['footer']['completion'];
			echo do_shortcode( stripslashes($html) );
		}
	}
	
}
?>
