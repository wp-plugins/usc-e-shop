<?php

class usc_e_shop
{

	var $page;   //page action
	var $cart;          //cart object
	var $use_ssl;       //ssl flag
	var $action_status;
	var $action_message, $error_message;
	var $itemskus, $itemsku, $itemopts, $itemopt;
	var $zaiko_status, $payment_structure, $display_mode, $shipping_rule, $shipping_charge_structure;
	var $member_status;
	var $options;
	var $login_mail, $current_member, $member_form;
	var $payment_results, $log_flg;

	function usc_e_shop()
	{
	
		$this->usces_session_start();

		if ( !isset($_SESSION['usces_member']) )
			$_SESSION['usces_member'] = array();

		$this->previous_url = isset($_SESSION['usces_previous_url']) ? $_SESSION['usces_previous_url'] : '';
		if(!isset($_SESSION['usces_checked_business_days'])) $this->update_business_days();
		$this->check_display_mode();
		
		$this->options = get_option('usces');
		if(!isset($this->options['smtp_hostname']) || empty($this->options['smtp_hostname'])){ $this->options['smtp_hostname'] = 'localhost';}
		if(!isset($this->options['divide_item'])) $this->options['divide_item'] = 0;
		if(!isset($this->options['fukugo_category_orderby'])) $this->options['fukugo_category_orderby'] = 'ID';
		if(!isset($this->options['fukugo_category_order'])) $this->options['fukugo_category_order'] = 'ASC';
		if(!isset($this->options['province'])) $this->options['province'] = get_option('usces_pref');
		if(!isset($this->options['membersystem_state'])) $this->options['membersystem_state'] = 'activate';
		if(!isset($this->options['membersystem_point'])) $this->options['membersystem_point'] = 'activate';
		if(!isset($this->options['settlement_path'])) $this->options['settlement_path'] = USCES_PLUGIN_DIR . '/settlement/';
		update_option('usces', $this->options);

		$this->error_message = '';
		$this->login_mail = '';
		$this->get_current_member();
		$this->page = '';
		$this->payment_results = array();

		//admin_ssl options
		$this->use_ssl = get_option("admin_ssl_use_ssl") === "1" ? true : false;
		$use_shared = get_option("admin_ssl_use_shared") === "1" && $this->use_ssl ? true : false;
		$shared_url = get_option("admin_ssl_shared_url");
		
		if ( $use_shared ) {
			$ssl_url = str_replace('/wp-admin/', '', $shared_url);
		} else {
			$ssl_url = str_replace('http://', 'https://', get_option('home'));
		}
		define('USCES_CART_NUMBER', get_option('usces_cart_number'));
		define('USCES_MEMBER_NUMBER', get_option('usces_member_number'));
		if($this->use_ssl) {
			define('USCES_CART_URL', get_option('home') . '/?page_id=' . USCES_CART_NUMBER . '&usces=' . $this->get_uscesid());
			define('USCES_MEMBER_URL', get_option('home') . '/?page_id=' . USCES_MEMBER_NUMBER . '&usces=' . $this->get_uscesid());
		} else {
			define('USCES_CART_URL', get_option('home') . '/?page_id=' . USCES_CART_NUMBER);
			define('USCES_MEMBER_URL', get_option('home') . '/?page_id=' . USCES_MEMBER_NUMBER);
		}
		define('USCES_ITEM_CAT_PARENT_ID', get_option('usces_item_cat_parent_id'));
		define('USCES_SSL_URL', $ssl_url);
		
		$this->zaiko_status = get_option('usces_zaiko_status');
		$this->member_status = get_option('usces_customer_status');
		$this->payment_structure = get_option('usces_payment_structure');
		$this->display_mode = get_option('usces_display_mode');
		$this->shipping_rule = get_option('usces_shipping_rule');
		//$this->shipping_charge_structure = get_option('shipping_charge_structure');
		define('USCES_MYSQL_VERSION', (int)substr(mysql_get_server_info(), 0, 1));

		
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
		//add_submenu_page(USCES_PLUGIN_BASENAME, __('Backup','usces'), __('Backup','usces'), 6, 'usces_backup', array($this, 'admin_backup_page'));
		
		add_object_page('Welcart Management', 'Welcart Management', 6, 'usces_orderlist', array($this, 'order_list_page'));
		add_submenu_page('usces_orderlist', __('Order List','usces'), __('Order List','usces'), 6, 'usces_orderlist', array($this, 'order_list_page'));
		add_submenu_page('usces_orderlist', __('New Order or Estimate','usces'), __('New Order or Estimate','usces'), 6, 'usces_ordernew', array($this, 'order_list_page'));
		add_submenu_page('usces_orderlist', __('List of Members','usces'), __('List of Members','usces'), 6, 'usces_memberlist', array($this, 'member_list_page'));
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
			case 'printpdf':
				require_once(USCES_PLUGIN_DIR . '/includes/order_print.php');	
				break;
			case 'editpost':
				$res = usces_update_orderdata();
				if ( 1 === $res ) {
					$this->set_action_status('success', __('order date is updated','usces').' <a href="'.stripslashes( $_POST['usces_referer'] ).'">'.__('back to the summary','usces').'</a>');
				} elseif ( 0 === $res ) {
					$this->set_action_status('none', '');
				} else {
					$this->set_action_status('error', 'ERROR：'.__('failure in update','usces'));
				}
				require_once(USCES_PLUGIN_DIR . '/includes/order_edit_form.php');	
				break;
			case 'newpost':
				$res = usces_new_orderdata();
				if ( 1 === $res ) {
					$this->set_action_status('success', __('New date is add','usces'));
				} elseif ( 0 === $res ) {
					$this->set_action_status('none', '');
				} else {
					$this->set_action_status('error', 'ERROR：'.__('failure in addition','usces'));
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
				$res = usces_delete_orderdata();
				if ( 1 === $res ) {
					$this->set_action_status('success', __('the order date is deleted','usces'));
				} elseif ( 0 === $res ) {
					$this->set_action_status('none', '');
				} else {
					$this->set_action_status('error', 'ERROR：'.__('failure in delete','usces'));
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
			case 'editpost':
				$this->error_message = $this->admin_member_check();
				if($this->error_message == ''){
					$res = usces_update_memberdata();
					if ( 1 === $res ) {
						$this->set_action_status('success', __('Membership information is updated','usces'));
					} elseif ( 0 === $res ) {
						$this->set_action_status('none', '');
					} else {
						$this->set_action_status('error', 'ERROR：'.__('failure in update','usces'));
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
					$this->set_action_status('success', __('the order date is deleted','usces'));
				} elseif ( 0 === $res ) {
					$this->set_action_status('none', '');
				} else {
					$this->set_action_status('error', 'ERROR：'.__('failure in delete','usces'));
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
	
	/* Shop Setup Page */
	function admin_top_page() {

		require_once(USCES_PLUGIN_DIR . '/includes/admin_top.php');	

	}
	
	/* Shop Setup Page */
	function admin_setup_page() {
	
		$this->options = get_option('usces');
		//$this->options = array();

		if(isset($_POST['usces_option_update'])) {
			$this->options['display_mode'] = isset($_POST['display_mode']) ? wp_specialchars($_POST['display_mode']) : '';
			$this->options['campaign_category'] = isset($_POST['cat']) ? $_POST['cat'] : '0';
			$this->options['campaign_privilege'] = isset($_POST['cat_privilege']) ? wp_specialchars($_POST['cat_privilege']) : '';
			$this->options['privilege_point'] = isset($_POST['point_num']) ? (int)$_POST['point_num'] : '';
			$this->options['privilege_discount'] = isset($_POST['discount_num']) ? (int)$_POST['discount_num'] : '';
			$this->options['company_name'] = isset($_POST['company_name']) ? wp_specialchars($_POST['company_name']) : '';
			$this->options['zip_code'] = isset($_POST['zip_code']) ? wp_specialchars($_POST['zip_code']) : '';
			$this->options['address1'] = isset($_POST['address1']) ? wp_specialchars($_POST['address1']) : '';
			$this->options['address2'] = isset($_POST['address2']) ? wp_specialchars($_POST['address2']) : '';
			$this->options['tel_number'] = isset($_POST['tel_number']) ? wp_specialchars($_POST['tel_number']) : '';
			$this->options['fax_number'] = isset($_POST['fax_number']) ? wp_specialchars($_POST['fax_number']) : '';
			$this->options['order_mail'] = isset($_POST['order_mail']) ? wp_specialchars($_POST['order_mail']) : '';
			$this->options['inquiry_mail'] = isset($_POST['inquiry_mail']) ? wp_specialchars($_POST['inquiry_mail']) : '';
			$this->options['sender_mail'] = isset($_POST['sender_mail']) ? wp_specialchars($_POST['sender_mail']) : '';
			$this->options['error_mail'] = isset($_POST['error_mail']) ? wp_specialchars($_POST['error_mail']) : '';
			$this->options['postage_privilege'] = isset($_POST['postage_privilege']) ? wp_specialchars($_POST['postage_privilege']) : '';
			$this->options['purchase_limit'] = isset($_POST['purchase_limit']) ? wp_specialchars($_POST['purchase_limit']) : '';
			$this->options['point_rate'] = isset($_POST['point_rate']) ? (int)$_POST['point_rate'] : '';
			$this->options['start_point'] = isset($_POST['start_point']) ? (int)$_POST['start_point'] : '';
			$this->options['shipping_rule'] = isset($_POST['shipping_rule']) ? wp_specialchars($_POST['shipping_rule']) : '';
			$this->options['tax_rate'] = isset($_POST['tax_rate']) ? (int)$_POST['tax_rate'] : '';
			$this->options['tax_method'] = isset($_POST['tax_method']) ? wp_specialchars($_POST['tax_method']) : '';
			$this->options['cod_fee'] = isset($_POST['cod_fee']) ? wp_specialchars($_POST['cod_fee']) : '';
			$this->options['transferee'] = isset($_POST['transferee']) ? wp_specialchars($_POST['transferee']) : '';
			$this->options['copyright'] = isset($_POST['copyright']) ? wp_specialchars($_POST['copyright']) : '';
			$this->options['membersystem_state'] = isset($_POST['membersystem_state']) ? wp_specialchars($_POST['membersystem_state']) : '';
			$this->options['membersystem_point'] = isset($_POST['membersystem_point']) ? wp_specialchars($_POST['membersystem_point']) : '';

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
		
			$this->options['smtp_hostname'] = wp_specialchars(trim($_POST['smtp_hostname']));
		
			foreach ( $_POST['title'] as $key => $value ) {
				if( trim($value) == '' ) {
					$this->options['mail_data']['title'][$key] = $this->options['mail_default']['title'][$key];
				}else{
					$this->options['mail_data']['title'][$key] = wp_specialchars($value);
				}
			}
			foreach ( $_POST['header'] as $key => $value ) {
				if( trim($value) == '' ) {
					$this->options['mail_data']['header'][$key] = $this->options['mail_default']['header'][$key];
				}else{
					$this->options['mail_data']['header'][$key] = wp_specialchars($value);
				}
			}
			foreach ( $_POST['footer'] as $key => $value ) {
				if( trim($value) == '' ) {
					$this->options['mail_data']['footer'][$key] = $this->options['mail_default']['footer'][$key];
				}else{
					$this->options['mail_data']['footer'][$key] = wp_specialchars($value);
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

			foreach ( $_POST['header'] as $key => $value ) {
				$this->options['cart_page_data']['header'][$key] = $value;
			}
			foreach ( $_POST['footer'] as $key => $value ) {
				$this->options['cart_page_data']['footer'][$key] = $value;
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

		$this->options = get_option('usces');

		if(isset($_POST['usces_option_update'])) {
		
			if($_POST['province'] != ''){
				$temp_pref = explode("\n", $_POST['province']);
				for($i=-1; $i<count($temp_pref); $i++){
					if($i == -1){
						$usces_pref[] = '-選択-';
					}else{
						$usces_pref[] = wp_specialchars(trim($temp_pref[$i]));
					}
				}
			}else{
				$usces_pref = get_option('usces_pref');
			}

			$this->options['province'] = $usces_pref;
			$this->options['divide_item'] = isset($_POST['divide_item']) ? 1 : 0;
			$this->options['itemimg_anchor_rel'] = isset($_POST['itemimg_anchor_rel']) ? wp_specialchars(trim($_POST['itemimg_anchor_rel'])) : '';
			$this->options['fukugo_category_orderby'] = isset($_POST['fukugo_category_orderby']) ? $_POST['fukugo_category_orderby'] : '';
			$this->options['fukugo_category_order'] = isset($_POST['fukugo_category_order']) ? $_POST['fukugo_category_order'] : '';
			$this->options['settlement_path'] = isset($_POST['settlement_path']) ? stripslashes($_POST['settlement_path']) : '';
			if($this->options['settlement_path'] == '') $this->options['settlement_path'] = USCES_PLUGIN_DIR . '/settlement/';
			$sl = substr($this->options['settlement_path'], -1);
			if($sl != '/' && $sl != '\\') $this->options['settlement_path'] .= '/';

			
			$this->action_status = 'success';
			$this->action_message = __('options are updated','usces');
		} else {

			if( !isset($this->options['province']) || $this->options['province'] == '' ){
				$this->options['province'] = get_option('usces_pref');
			}
			$this->action_status = 'none';
			$this->action_message = '';
		}

		update_option('usces', $this->options);

		
		require_once(USCES_PLUGIN_DIR . '/includes/admin_system.php');	

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
		
		$sessid = $conjunction . 'usces=' . $this->get_uscesid();
	
		
		if( false === strpos($request, 'usces=') )
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

		if(isset($_GET['usces']) && ($_GET['usces'] != '')) {
			$sessid = $_GET['usces'];
			//$this->uscesdc($sessid);
			session_id($sessid);
		}
		@session_start();
		
	}
	
	function usces_cookie() {
		if( !isset($_SESSION['usces_cookieid']) ) {
			$cookie = $this->get_cookie();
			if( !isset($cookie['id']) || $cookie['id'] == '' ) {
				$values = array(
							'id' => md5(uniqid(rand(), true)),
							'name' => '',
							'pass' => ''
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
		$timeout = time()+365*86400;
		$domain = $_SERVER['HTTP_HOST'];
		$res = setcookie('usces_cookie', $value, $timeout, '/', $domain);
	}
	
	function get_cookie() {
		$values = unserialize(stripslashes($_COOKIE['usces_cookie']));
		return $values;
	}
	
	function cnt_access( $flag = '' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "usces_access";

		$query = $wpdb->prepare("SELECT ID FROM $table_name WHERE acc_date = %s", date('Y-m-d'));
		$res = $wpdb->get_var( $query );
		$wpdb->show_errors();
		if(empty($res)){
			if( $flag == '' ){
				$query = $wpdb->prepare("INSERT INTO $table_name (acc_type, acc_num1, acc_num2, acc_str1, acc_str2, acc_date) VALUES(%s, %d, %d, %s, %s, %s)", 'visiter', 1, 0, NULL, NULL, date('Y-m-d'));
				$wpdb->query( $query );
			}elseif( $flag == 'first' ){
				$query = $wpdb->prepare("INSERT INTO $table_name (acc_type, acc_num1, acc_num2, acc_str1, acc_str2, acc_date) VALUES(%s, %d, %d, %s, %s, %s)", 'visiter', 0, 1, NULL, NULL, date('Y-m-d'));
				$wpdb->query( $query );
			}
		}else{
			if( $flag == '' ){
				$query = $wpdb->prepare("UPDATE $table_name SET acc_num1 = acc_num1 + 1 WHERE acc_date = %s", date('Y-m-d'));
				$wpdb->query( $query );
			}elseif( $flag == 'first' ){
				$query = $wpdb->prepare("UPDATE $table_name SET acc_num2 = acc_num2 + 1 WHERE acc_date = %s", date('Y-m-d'));
				$wpdb->query( $query );
			}
		}
	}
	
	function get_uscesid() {

		$sessname = session_name();
		$sessid = isset($_REQUEST[$sessname]) ? $_REQUEST[$sessname] : session_id();
		//$this->uscescv($sessid);
		return $sessid;
	}
	
	function shop_head() {
		global $post, $current_user;
		get_currentuserinfo();
		
		$css_url = USCES_PLUGIN_URL . '/css/usces_cart.css';
		$this->member_name = ( is_user_logged_in() ) ? get_usermeta($current_user->ID,'first_name').get_usermeta($current_user->ID,'last_name') : '';
		?>

		<link href="<?php echo $css_url; ?>" rel="stylesheet" type="text/css" />
	<?php if( file_exists(get_stylesheet_directory() . '/usces_cart.css') ){ ?>
		<link href="<?php echo get_stylesheet_directory_uri(); ?>/usces_cart.css" rel="stylesheet" type="text/css" />
	<?php } ?>
		<?php 
		if(isset($post)) : 
		
			$javascript_url = USCES_PLUGIN_URL . '/js/usces_cart.js';
			$ioptkeys = $this->get_itemOptionKey( $post->ID );
			$mes_opts_str = "";
			$key_opts_str = "";
			if($ioptkeys){
				foreach($ioptkeys as $key => $value){
					$optValues = $this->get_itemOptions( $value, $post->ID );
					if($optValues['means'] < 2){
						$mes_opts_str .= "'{$value}を選択してください。',";
					}else{
						$mes_opts_str .= "'{$value}を入力してください。',";
					}
					$key_opts_str .= "'{$value}',";
				}
				$mes_opts_str = rtrim($mes_opts_str, ',');
				$key_opts_str = rtrim($key_opts_str, ',');
			}
			$itemRestriction = get_post_custom_values('itemRestriction', $post->ID);
		
		?>
		<script type='text/javascript'>
		/* <![CDATA[ */
			uscesL10n = {
				post_id: "<?php echo $post->ID; ?>",
				cart_number: "<?php echo get_option('usces_cart_number'); ?>",
				mes_opts: new Array( <?php echo $mes_opts_str; ?> ),
				key_opts: new Array( <?php echo $key_opts_str; ?> ), 
				previous_url: "<?php if(isset($_SESSION['usces_previous_url'])) echo $_SESSION['usces_previous_url']; ?>", 
				itemRestriction: "<?php echo $itemRestriction[0]; ?>"
			}
		/* ]]> */
		</script>
		<?php endif; ?>
		<script type='text/javascript' src='<?php echo get_option('siteurl') . '/wp-includes/js/jquery/jquery.js'; ?>'></script>
		<script type='text/javascript' src='<?php echo $javascript_url; ?>'></script>
<?php
	}
	
	function admin_head() {
?>
		
		<link href="<?php echo USCES_PLUGIN_URL; ?>/css/admin_style.css" rel="stylesheet" type="text/css" media="all" />
		<script type='text/javascript'>
		/* <![CDATA[ */
			uscesL10n = {
				requestFile: "<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php",
				cart_number: "<?php echo get_option('usces_cart_number'); ?>", 
				purchase_limit: "<?php echo $this->options['purchase_limit']; ?>", 
				point_rate: "<?php echo $this->options['point_rate']; ?>",
				shipping_rule: "<?php echo $this->options['shipping_rule']; ?>" 
			}
		/* ]]> */
		</script>
		<script type='text/javascript' src='<?php echo USCES_PLUGIN_URL; ?>/js/usces_admin.js'></script>
		
	<?php if($this->action_status == 'edit' || $this->action_status == 'editpost'){ ?>
			<link rel='stylesheet' href='<?php echo get_option('siteurl'); ?>/wp-includes/js/thickbox/thickbox.css' type='text/css' media='all' />
<?php
		}
	}
	
	function main() {
	
		//$this->redirect();
		$this->usces_cookie();
		$this->update_table();
		
		//var_dump($_REQUEST);
		require_once(USCES_PLUGIN_DIR . '/classes/cart.class.php');
		$this->cart = new usces_cart();
		
		$this->controller();
		
		if( isset($_REQUEST['page']) && $_REQUEST['page'] == 'usces_itemedit' && isset($_REQUEST['action']) && $_REQUEST['action'] == 'duplicate' ){
			$post_id = (int)$_GET['post'];
			$new_id = usces_item_dupricate($post_id);
			$ref = isset($_REQUEST['usces_referer']) ? urlencode($_REQUEST['usces_referer']) : '';
			$url = USCES_ADMIN_URL . '?page=usces_itemedit&action=edit&post=' . $new_id . '&usces_referer=' . $ref;
			wp_redirect($url);
			exit;
		}


		if($_REQUEST['page'] == 'usces_itemnew')
			$_REQUEST['action'] = 'new';
		
		if( isset($_REQUEST['page']) && ($_REQUEST['action'] == 'edit' || $_REQUEST['action'] == 'new' || $_REQUEST['action'] == 'editpost')) {
		
			wp_enqueue_script('post');
			//if ( user_can_richedit() )
			wp_enqueue_script('editor');
			add_thickbox();
			wp_enqueue_script('media-upload');
			wp_enqueue_script('word-count');
			wp_enqueue_script( 'admin-comments' );
			wp_enqueue_script('autosave');
		
			//add_action( 'admin_head', 'wp_tiny_mce' );
			add_action( 'admin_print_footer_scripts', 'wp_tiny_mce', 25 );
			wp_enqueue_script('quicktags');

		}

		if( isset($_REQUEST['order_action']) && $_REQUEST['order_action'] == 'pdfout' ){
			require_once(USCES_PLUGIN_DIR . '/includes/order_print.php');
		}
		
	}

	function controller() {
		global $wp_query;

		if($this->is_maintenance()){

			$this->page = 'maintenance';
			add_action('the_post', array($this, 'action_cartFilter'));
		}else if(isset($_POST['inCart'])) {

//			if( EX_DLSELLER === true ){
//				dlseller_controller();
//			}else{
				$this->page = 'cart';
				$this->cart->inCart();
				add_action('the_post', array($this, 'action_cartFilter'));
//			}
			
		}else if(isset($_POST['upButton'])) {
		
			$this->page = 'cart';
			$this->cart->upCart();
			add_action('the_post', array($this, 'action_cartFilter'));
			
		}else if(isset($_POST['delButton'])) {
		
			$this->page = 'cart';
			$this->cart->del_row();
			add_action('the_post', array($this, 'action_cartFilter'));
			
		}else if(isset($_POST['backCart'])) {
		
			$this->page = 'cart';
			add_action('the_post', array($this, 'action_cartFilter'));
		
		}else if(isset($_REQUEST['customerinfo'])) {

			if( false === $this->cart->num_row() ){
				header('location: ' . get_option('home'));
				exit;
			}
			$this->cart->entry();
			$this->error_message = $this->zaiko_check();
			if($this->error_message == ''){
				if($this->is_member_logged_in()){
					$this->page = 'delivery';
				}else{
					$this->page = 'customer';
				}
			}else{
				$this->page = 'cart';
			}
			if ( !$this->cart->is_order_condition() ) {
				$order_conditions = $this->get_condition();
				$this->cart->set_order_condition($order_conditions);
			}
			add_action('the_post', array($this, 'action_cartFilter'));

		}else if(isset($_POST['backCustomer'])) {
		
			if( false === $this->cart->num_row() ){
				header('location: ' . get_option('home'));
				exit;
			}
			$this->page = 'customer';
			add_action('the_post', array($this, 'action_cartFilter'));
//			$this->cart->entry();
//			$this->error_message = $this->delivery_check();
//			$this->page = ($this->error_message == '') ? 'customer' : 'delivery';
		
		}else if(isset($_POST['customerlogin'])) {

			if( false === $this->cart->num_row() ){
				header('location: ' . get_option('home'));
				exit;
			}
			$this->cart->entry();
			$this->page = ($this->member_login() == 'member') ? 'delivery' : 'customer';
			add_action('the_post', array($this, 'action_cartFilter'));
		
		}else if(isset($_POST['reganddeliveryinfo'])) {

			if( false === $this->cart->num_row() ){
				header('location: ' . get_option('home'));
				exit;
			}
			$this->cart->entry();
			$_POST['member_regmode'] = 'newmemberfromcart';
			$this->page = ( $this->regist_member() == 'newcompletion' ) ? 'delivery' : 'customer';
			add_action('the_post', array($this, 'action_cartFilter'));
		
		}else if(isset($_POST['deliveryinfo'])) {

			if( false === $this->cart->num_row() ){
				header('location: ' . get_option('home'));
				exit;
			}
			$this->cart->entry();
			$this->error_message = $this->customer_check();
			$this->page = ($this->error_message == '') ? 'delivery' : 'customer';
			add_action('the_post', array($this, 'action_cartFilter'));
		
		}else if(isset($_POST['backDelivery'])) {
		
			if( false === $this->cart->num_row() ){
				header('location: ' . get_option('home'));
				exit;
			}
			$this->page = 'delivery';
			add_action('the_post', array($this, 'action_cartFilter'));
//			$this->cart->entry();
		
		}else if(isset($_REQUEST['confirm'])) {
		
			if( false === $this->cart->num_row() ){
				header('location: ' . get_option('home'));
				exit;
			}
			
			$this->cart->entry();
			if(isset($_POST['confirm'])){
				$this->error_message = $this->delivery_check();
			}
			$this->page = ($this->error_message == '') ? 'confirm' : 'delivery';
			add_action('the_post', array($this, 'action_cartFilter'));
		
		}else if(isset($_POST['use_point'])) {
		
			$this->error_message = $this->point_check();
			$this->cart->entry();
			$this->page = 'confirm';
			add_action('the_post', array($this, 'action_cartFilter'));
		
		}else if(isset($_POST['backConfirm'])) {
		
			if( false === $this->cart->num_row() ){
				header('location: ' . get_option('home'));
				exit;
			}
			$this->page = 'confirm';
			add_action('the_post', array($this, 'action_cartFilter'));
		
		}else if(isset($_REQUEST['purchase'])) {
		
			if( false === $this->cart->num_row() ){
				header('location: ' . get_option('home'));
				exit;
			}
			$entry = $this->cart->get_entry();
			$this->error_message = $this->zaiko_check();
			if($this->error_message == '' && 0 < $this->cart->num_row()){
				$payments = $this->getPayments( $entry['order']['payment_name'] );
				if( $payments['settlement'] == 'acting' && $entry['order']['total_full_price'] > 0 ){
					$query = '';
					foreach($_POST as $key => $value){
						if($key != 'purchase')
							$query .= '&' . $key . '=' . urlencode($value);
					}
					$actinc_status = $this->acting_processing($payments['module'], $query);
				}
				
				if($actinc_status == 'error'){
					$this->page = 'error';
				}else{
					$res = $this->order_processing();
					$this->page = $res;
				}
			}else{
				$this->page = 'cart';
			}
			add_action('the_post', array($this, 'action_cartFilter'));
		
		}else if(isset($_REQUEST['acting_return'])) {
		
			if( 'paypal_ipn' == $_REQUEST['acting_return'] ){
				require_once($this->options['settlement_path'] . 'paypal.php');
				$ipn_res = paypal_ipn_check($usces_paypal_url);
				if( $ipn_res[0] === true ){
					$res = $this->order_processing( $ipn_res );
				}
				exit;
			}
			if( false === $this->cart->num_row() ){
				header('location: ' . get_option('home'));
				exit;
			}
			$this->payment_results = usces_check_acting_return();

			if(  isset($this->payment_results[0]) && $this->payment_results[0] === 'duplicate' ){
				header('location: ' . get_option('home'));
				exit;
			}else if( isset($this->payment_results[0]) && $this->payment_results[0] ){
				if( isset($this->payment_results['payment_status']) ){
					$this->page = 'ordercompletion';
				}else{
					$res = $this->order_processing( $this->payment_results );
					$this->page = $res;
				}
			}else{
				$this->page = 'error';
			}
			add_action('the_post', array($this, 'action_cartFilter'));
		
		}else if(isset($_REQUEST['settlement']) && $_REQUEST['settlement'] == 'epsilon') {
			require_once($this->options['settlement_path'] . 'epsilon.php');	
			
			
		
		}else if(isset($_POST['inquiry_button'])) {

			$res = $this->inquiry_processing();
			$this->page = $res;
		
		}else if(isset($_REQUEST['member_login'])) {
			
			$res = $this->member_login();
			$this->page = $res;
			add_action('the_post', array($this, 'action_memberFilter'));
		
		}else if(isset($_REQUEST['regmember'])) {

			$res = $this->regist_member();
			$this->page = $res;
			add_action('the_post', array($this, 'action_memberFilter'));
		
		}else if(isset($_REQUEST['editmember'])) {

			$res = $this->regist_member();
			$this->page = $res;
			add_action('the_post', array($this, 'action_memberFilter'));
		
		}else if( isset($_GET['page']) && $_GET['page'] == 'logout' ) {

			$this->member_logout();
			add_action('the_post', array($this, 'action_memberFilter'));
		
		}else if( isset($_GET['page']) && $_GET['page'] == 'lostmemberpassword' ) {

			$this->page = 'lostmemberpassword';
			add_action('the_post', array($this, 'action_memberFilter'));
		
		}else if( isset($_REQUEST['lostpassword']) ) {

			$this->error_message = $this->lostpass_mailaddcheck();
			if ( $this->error_message != '' ) {
				$this->page = 'lostmemberpassword';
			} else {
				$res = $this->lostmail();
				$this->page = $res;//'lostcompletion';
			}
			add_action('the_post', array($this, 'action_memberFilter'));
		
		}else if( isset($_REQUEST['uscesmode']) && $_REQUEST['uscesmode'] == 'changepassword') {

			$this->page = 'changepassword';
			add_action('the_post', array($this, 'action_memberFilter'));
		
		}else if( isset($_REQUEST['changepassword']) ) {

			$this->error_message = $this->changepass_check();
			if ( $this->error_message != '' ) {
				$this->page = 'changepassword';
			} else {
				$res = $this->changepassword();
				$this->page = $res;//'changepasscompletion';
			}
			add_action('the_post', array($this, 'action_memberFilter'));
		
		}else if( isset($_GET['page']) && $_GET['page'] == 'newmember') {

			$this->page = 'newmemberform';
			add_action('the_post', array($this, 'action_memberFilter'));
		
		}else if( isset($_POST['usces_export']) ) {

			$this->export();

		}else if( isset($_POST['usces_import']) ) {

			$this->import();

		}else if( isset($_REQUEST['page']) && $_REQUEST['page'] == 'search_item') {

			$this->page = 'search_item';
			add_action('template_redirect', array($this, 'action_search_item'));
			add_action('the_post', array($this, 'action_cartFilter'));
			
		}else if( isset($_REQUEST['usces_upload_page']) ) {

			add_action('template_redirect', array($this, 'load_upload_template'));
			
		}else if( isset($_REQUEST['usces_upload']) ) {
			
			if(function_exists('usces_upload')) usces_upload();
				
			add_action('template_redirect', array($this, 'load_upload_template'));
			
		}else{

			add_action('the_post', array($this, 'goDefaultPage'));
			
		}
	}
	
	function goDefaultPage(){
		global $post;
		
		if( $post->ID == USCES_CART_NUMBER ) {
		
			$this->page = 'cart';
			add_filter('the_content', array($this, 'filter_cartContent'),20);

		}else if( $post->ID == USCES_MEMBER_NUMBER ) {
		
			$this->page = 'member';
			add_filter('the_content', array($this, 'filter_memberContent'),20);
		
		}/*else if( is_category() ) {
		
//			$this->page = 'category_item';
//			add_filter('the_content', array($this, 'filter_cartContent'),20);
		
		}*/else if( !is_singular() ) {
			$this->page = 'wp_search';
			add_filter('the_excerpt', array($this, 'filter_cartContent'),20);
			add_filter('the_content', array($this, 'filter_cartContent'),20);
		}
	}
	
	function import() {
		$res = usces_import_xml();
		if ( $res === false ) :
			$this->action_status = 'error';
			//$this->action_message = 'エラー：インポートが完了しませんでした。';
		else :
			$this->action_status = 'success';
			$this->action_message = 'インポートが完了しました。';
		endif;
		
//		require_once(USCES_PLUGIN_DIR . '/includes/admin_backup.php');	
	}

	function export() {
		$filename = 'usces.' . date('Y-m-d') . '.xml';
	
		header('Content-Description: File Transfer');
		header("Content-Disposition: attachment; filename=$filename");
		header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);

		usces_export_xml();
		die();
	
	}

	function changepassword() {
		global $wpdb;

		if ( !isset($_SESSION['usces_lostmail']) ) :
			$this->error_message = 'タイムアウトのためパスワードを更新できませんでした。';
			return 'login';
		else :
		
			$member_table = $wpdb->prefix . "usces_member";
			
			$query = $wpdb->prepare("UPDATE $member_table SET mem_pass = %s WHERE mem_email = %s", 
							md5(trim($_POST['loginpass1'])), $_SESSION['usces_lostmail']);
			$res = $wpdb->query( $query );
			//$res = $wpdb->last_results;

			if ( $res === false ) :
				$this->error_message = 'エラー：パスワードを更新できませんでした。';
				return 'login';
			else :
				return 'changepasscompletion';
			endif;

		endif;
	}
	
	function lostmail() {
	
		$_SESSION['usces_lostmail'] = wp_specialchars(trim($_POST['loginmail']));
		$id = session_id();
		$uri = get_option('home') . '/usces-member?uscesmode=changepassword&usces=' . $id;
		$res = usces_lostmail($uri);
		return $res;
	
	}
	
	function regist_member() {
		global $wpdb;
		
		$member = $this->get_member();
		$mode = $_POST['member_regmode'];
		$member_table = $wpdb->prefix . "usces_member";
			
		$error_mes = ( $_POST['member_regmode'] == 'newmemberfromcart' ) ? $this->member_check_fromcart() : $this->member_check();
		
		if ( $error_mes != '' ) {
		
			$this->error_message = $error_mes;
			return $mode;
			
		} elseif ( $_POST['member_regmode'] == 'editmemberform' ) {
		
			$query = $wpdb->prepare("SELECT ID FROM $member_table WHERE mem_email = %s AND ID <> %d", 
						trim($_POST['member']['mailaddress1']), $_POST['member_id']
					);
			$id = $wpdb->get_var( $query );
			if ( !empty($id) ) {
				$this->error_message = 'このメールアドレスは既に登録されています。';
				return $mode;
			} else {
			
				$password = ( !empty($_POST['member']['password1']) && trim($_POST['member']['password1']) == trim($_POST['member']['password1']) ) ? md5(trim($_POST['member']['password1'])) : $pass;
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
				
				$this->get_current_member();
				return 'editmemberform';
			}
			
		} elseif ( $_POST['member_regmode'] == 'newmemberform' ) {

			$query = $wpdb->prepare("SELECT ID FROM $member_table WHERE mem_email = %s", trim($_POST['member']['mailaddress1']));
			$id = $wpdb->get_var( $query );
			if ( !empty($id) ) {
				$this->error_message = 'このメールアドレスは既に登録されています。';
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
						date('Y-m-d H:i:s'),
						'');
				$res = $wpdb->query( $query );
				
				//$_SESSION['usces_member']['ID'] = $wpdb->insert_id;
				//$this->get_current_member();
				if($res !== false) usces_send_regmembermail();
				return 'newcompletion';
			}
			
		} elseif ( $_POST['member_regmode'] == 'newmemberfromcart' ) {

			$query = $wpdb->prepare("SELECT ID FROM $member_table WHERE mem_email = %s", trim($_POST['customer']['mailaddress1']));
			$id = $wpdb->get_var( $query );
			if ( !empty($id) ) {
				$this->error_message = 'このメールアドレスは既に登録されています。';
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
						date('Y-m-d H:i:s'),
						'');
				$res = $wpdb->query( $query );
				
				//$_SESSION['usces_member']['ID'] = $wpdb->insert_id;
				//$this->get_current_member();
				if( $res ) {
					//usces_send_regmembermail();
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

	function is_member_logged_in() {
		if( isset($_SESSION['usces_member']['ID']) )
			return true;
		else
			return false;
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
		
		if ( $_POST['loginmail'] == '' && $_POST['loginpass'] == '' ) {
			return 'login';
		} else if ( $_POST['loginpass'] == '' ) {
			$this->current_member['email'] = wp_specialchars(trim($_POST['loginmail']));
			$this->error_message = '<b>エラー:</b> パスワードを入力してください。';
			return 'login';
		} else {
			$email = trim($_POST['loginmail']);
			$pass = md5(trim($_POST['loginpass']));
			$member_table = $wpdb->prefix . "usces_member";
	
			$query = $wpdb->prepare("SELECT ID FROM $member_table WHERE mem_email = %s", $email);
			$id = $wpdb->get_var( $query );
			
			if ( !$id ) {
				$this->current_member['email'] = htmlspecialchars($email);
				$this->error_message = '<b>エラー:</b>  メールアドレスが違います。';
				return 'login';
			} else {
				$query = $wpdb->prepare("SELECT * FROM $member_table WHERE mem_email = %s AND mem_pass = %s", $email, $pass);
				$member = $wpdb->get_row( $query, ARRAY_A );
				if ( empty($member) ) {
					$this->current_member['email'] = htmlspecialchars($email);
					$this->error_message = '<b>エラー:</b>  パスワードが違います。';
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
					$this->get_current_member();
					
					$cookie = $this->get_cookie();
					if(isset($_POST['rememberme']) && $cookie){
						$cookie['name'] = $email;
						$cookie['pass'] = trim($_POST['loginpass']);
						$this->set_cookie($cookie);
					}else{
						$cookie['name'] = '';
						$cookie['pass'] = '';
						$this->set_cookie($cookie);
					}
					return 'member';
				}
			}
		}
	}

	function member_logout() {
		unset($_SESSION['usces_member']);
		wp_redirect(get_option('home'));
		exit;
	}
	
	function get_current_member() {
		
		if ( isset($_SESSION['usces_member']['ID']) ) {
			$this->current_member['id'] = $_SESSION['usces_member']['ID'];
			$this->current_member['name'] = $_SESSION['usces_member']['name1'] . ' ' . $_SESSION['usces_member']['name2'];
		} else {
			$this->current_member['id'] = 0;
			$this->current_member['name'] = 'ゲスト';
		}
	}

	function get_member() {
		foreach ( $_SESSION['usces_member'] as $key => $vlue ) {
			$res[$key] = htmlspecialchars($vlue);
		}
		return $res;
	}

	function zaiko_check() {
		$red = '';
		$cart = $this->cart->get_cart();
		for($i=0; $i<count($cart); $i++) { 
			$cart_row = $cart[$i];
			$post_id = $cart_row['post_id'];
			$sku = $cart_row['sku'];
			$stock = $this->getItemZaiko($post_id, $sku);
			$red = (in_array($stock, array('売切れ','入荷待ち','廃盤'))) ? 'red' : '';
		}
		$mes = $red == '' ? '' : '恐れ入りますが、商品が売り切れました。';
		return $mes;	
	}
	
	function member_check() {
		$mes = '';
		foreach ( $_POST['member'] as $key => $vlue ) {
			$_SESSION['usces_member'][$key] = trim($vlue);
		}
		if ( $_POST['member_regmode'] == 'editmemberform' ) {
			if ( (trim($_POST['member']['password1']) != '' || trim($_POST['member']['password2']) != '') && trim($_POST['member']['password1']) != trim($_POST['member']['password2']) )
				$mes .= "パスワードが不正です。<br />";
			if ( !strstr($_POST['member']['mailaddress1'], '@') || trim($_POST['member']['mailaddress1']) == '' )
				$mes .= "メールアドレスが不正です。<br />";
				
		} else {
			if ( trim($_POST['member']['password1']) == '' || trim($_POST['member']['password2']) == '' || trim($_POST['member']['password1']) != trim($_POST['member']['password2']) )
				$mes .= "パスワードが不正です。<br />";
			if ( !strstr($_POST['member']['mailaddress1'], '@') || trim($_POST['member']['mailaddress1']) == '' || trim($_POST['member']['mailaddress2']) == '' || trim($_POST['member']['mailaddress1']) != trim($_POST['member']['mailaddress2']) )
				$mes .= "メールアドレスが不正です。<br />";
			
		}
		if ( trim($_POST["member"]["name1"]) == "" )
			$mes .= "名前が不正です。";
		if ( trim($_POST["member"]["name3"]) == "" )
			$mes .= "フリカナが不正です。<br />";
		if ( trim($_POST["member"]["zipcode"]) == "" )
			$mes .= "郵便番号が不正です。<br />";
		if ( $_POST["member"]["pref"] == "-選択-" )
			$mes .= "都道府県を選択してください。<br />";
		if ( trim($_POST["member"]["address1"]) == "" )
			$mes .= "市区郡町村を入力してください。<br />";
		if ( trim($_POST["member"]["address2"]) == "" )
			$mes .= "番地を入力してください。<br />";
		if ( trim($_POST["member"]["tel"]) == "" )
			$mes .= "電話番号を入力してください。<br />";
	
		return $mes;
	}

	function member_check_fromcart() {
		$mes = '';
		if ( trim($_POST['customer']['password1']) == '' || trim($_POST['customer']['password2']) == '' || trim($_POST['customer']['password1']) != trim($_POST['customer']['password2']) )
			$mes .= "パスワードが不正です。<br />";
		if ( !strstr($_POST['customer']['mailaddress1'], '@') || trim($_POST['customer']['mailaddress1']) == '' || trim($_POST['customer']['mailaddress2']) == '' || trim($_POST['customer']['mailaddress1']) != trim($_POST['customer']['mailaddress2']) )
			$mes .= "メールアドレスが不正です。<br />";
		if ( trim($_POST["customer"]["name1"]) == "" )
			$mes .= "名前が不正です。";
		if ( trim($_POST["customer"]["name3"]) == "" )
			$mes .= "フリカナが不正です。<br />";
		if ( trim($_POST["customer"]["zipcode"]) == "" )
			$mes .= "郵便番号が不正です。<br />";
		if ( $_POST["customer"]["pref"] == "-選択-" )
			$mes .= "都道府県を選択してください。<br />";
		if ( trim($_POST["customer"]["address1"]) == "" )
			$mes .= "市区郡町村を入力してください。<br />";
		if ( trim($_POST["customer"]["address2"]) == "" )
			$mes .= "番地を入力してください。<br />";
		if ( trim($_POST["customer"]["tel"]) == "" )
			$mes .= "電話番号を入力してください。<br />";
	
		return $mes;
	}

	function admin_member_check() {
		$mes = '';
		if ( !is_email( trim($_POST["mem_email"]) ) )
			$mes .= "メールアドレスが不正です。<br />";
		if ( trim($_POST["mem_name1"]) == "" )
			$mes .= "名前が不正です。<br />";
		if ( trim($_POST["mem_name3"]) == "" )
			$mes .= "フリカナが不正です。<br />";
		if ( trim($_POST["mem_zip"]) == "" )
			$mes .= "郵便番号が不正です。<br />";
		if ( $_POST["mem_pref"] == "-選択-" )
			$mes .= "都道府県を選択してください。<br />";
		if ( trim($_POST["mem_address1"]) == "" )
			$mes .= "市区郡町村を入力してください。<br />";
		if ( trim($_POST["mem_address2"]) == "" )
			$mes .= "番地を入力してください。<br />";
		if ( trim($_POST["mem_tel"]) == "" )
			$mes .= "電話番号を入力してください。<br />";
	
		return $mes;
	}

	function customer_check() {
		$mes = '';
		if ( !strstr($_POST['customer']['mailaddress1'], '@') || trim($_POST['customer']['mailaddress1']) == '' || trim($_POST['customer']['mailaddress2']) == '' || trim($_POST['customer']['mailaddress1']) != trim($_POST['customer']['mailaddress2']) )
			$mes .= "メールアドレスが不正です。<br />";
		if ( trim($_POST["customer"]["name1"]) == "" )
			$mes .= "名前が不正です。";
		if ( trim($_POST["customer"]["name3"]) == "" )
			$mes .= "フリカナが不正です。<br />";
		if ( trim($_POST["customer"]["zipcode"]) == "" )
			$mes .= "郵便番号が不正です。<br />";
		if ( $_POST["customer"]["pref"] == "-選択-" )
			$mes .= "都道府県を選択してください。<br />";
		if ( trim($_POST["customer"]["address1"]) == "" )
			$mes .= "市区郡町村を入力してください。<br />";
		if ( trim($_POST["customer"]["address2"]) == "" )
			$mes .= "番地を入力してください。<br />";
		if ( trim($_POST["customer"]["tel"]) == "" )
			$mes .= "電話番号を入力してください。<br />";
	
		return $mes;
	}

	function delivery_check() {
		$mes = '';
		if ( $_POST['customer']['delivery_flag'] == '1' ) {
			if ( trim($_POST["delivery"]["name1"]) == "" )
				$mes .= "1名前が不正です。";
			if ( trim($_POST["delivery"]["name3"]) == "" )
				$mes .= "フリカナが不正です。<br />";
			if ( trim($_POST["delivery"]["zipcode"]) == "" )
				$mes .= "郵便番号が不正です。<br />";
			if ( $_POST["delivery"]["pref"] == "-選択-" )
				$mes .= "都道府県を選択してください。<br />";
			if ( trim($_POST["delivery"]["address1"]) == "" )
				$mes .= "市区郡町村を入力してください。<br />";
			if ( trim($_POST["delivery"]["address2"]) == "" )
				$mes .= "番地を入力してください。<br />";
			if ( trim($_POST["delivery"]["tel"]) == "" )
				$mes .= "電話番号を入力してください。<br />";
		}
		if ( !isset($_POST['order']['payment_name']) )
			$mes .= "支払方法を選択してください。<br />";
	
		return $mes;
	}

	function point_check() {
		$member = $this->get_member();
		$this->set_cart_fees( $member, &$entries );
		$mes = '';
		if ( trim($_POST['order']["usedpoint"]) == "" || !(int)$_POST['order']["usedpoint"] || (int)$_POST['order']["usedpoint"] < 0 ) {
			$mes .= "値が不正です。半角数字で入力して下さい。<br />";
		} elseif ( trim($_POST['order']["usedpoint"]) > $member['point'] || trim($_POST['order']["usedpoint"]) > $entries['order']['total_price']) {
			$mes .= "利用できる上限値を超えています。<br />";
			$_POST['order']["usedpoint"] = 0;
		}

		return $mes;
	}

	function lostpass_mailaddcheck() {
		$mes = '';
		if ( !strstr($_POST['loginmail'], '@') || trim($_POST['loginmail']) == '' ) {
			$mes .= "メールアドレスが不正です。<br />";
		}elseif( !$this->is_member($_POST['loginmail']) ){
			$mes .= "存在しないメールアドレスです。<br />";
		}

		return $mes;
	}

	function changepass_check() {
		$mes = '';
		if ( trim($_POST['loginpass1']) == '' || trim($_POST['loginpass2']) == '' || (trim($_POST['loginpass1']) != trim($_POST['loginpass2'])))
			$mes .= "パスワードが不正です。<br />";

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

		if( (time() >= $starttime) && (time() <= $endtime) )
			$options['display_mode'] = 'Promotionsale';
		else
			$options['display_mode'] = 'Usualsale';
		
		update_option('usces', $options);
	
	}
	
	function update_business_days() {
		$options = get_option('usces');
		$datenow = getdate();
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
			echo "<div class='no_cart'>只今、カートに商品はございません。</div>\n";
		}
	}

	function display_cart_confirm() { 
		if($this->cart->num_row() > 0) {
			include (USCES_PLUGIN_DIR . '/includes/cart_confirm.php');
		} else {
			echo "<div class='no_cart'>只今、カートに商品はございません。</div>\n";
		}
	}

	//
	function set_initial()
	{
		$this->set_default_theme();
		$this->set_default_page();
		$this->set_default_categories();
		$this->create_table();
		$this->update_table();
	}
	
	function create_table()
	{
		global $wpdb;
		
		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}
		
		$access_table = $wpdb->prefix . "usces_access";
		$member_table = $wpdb->prefix . "usces_member";
		$order_table = $wpdb->prefix . "usces_order";
		$order_meta_table = $wpdb->prefix . "usces_order_meta";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		if($wpdb->get_var("show tables like '$member_table'") != $member_table) {
		
			$sql = "CREATE TABLE " . $access_table . " (
				ID BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				acc_type VARCHAR( 20 ) NOT NULL ,
				acc_num1 INT( 11 ) NOT NULL DEFAULT '0',
				acc_num2 INT( 11 ) NOT NULL DEFAULT '0',
				acc_str1 VARCHAR( 100 ) NULL ,
				acc_str2 VARCHAR( 100 ) NULL ,
				acc_date DATE NOT NULL DEFAULT '0000-00-00',
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
				order_item_total_price INT( 10 ) NOT NULL DEFAULT '0',
				order_getpoint INT( 10 ) NOT NULL DEFAULT '0',
				order_usedpoint INT( 10 ) NOT NULL DEFAULT '0',
				order_discount INT( 10 ) NOT NULL DEFAULT '0',
				order_shipping_charge INT( 10 ) NOT NULL DEFAULT '0',
				order_cod_fee INT( 10 ) NOT NULL DEFAULT '0',
				order_tax INT( 10 ) NOT NULL DEFAULT '0',
				order_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				order_modified VARCHAR( 20 ) NULL ,
				order_status VARCHAR( 255 ) NULL ,
				order_check VARCHAR( 255 ) NULL ,
				order_delivery_method INT( 10 ) NOT NULL DEFAULT -1,
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
		$order_table = $wpdb->prefix . "usces_order";
		$order_meta_table = $wpdb->prefix . "usces_order_meta";
		
		$access_ver = get_option( "usces_db_access" );
		$member_ver = get_option( "usces_db_member" );
		$order_ver = get_option( "usces_db_order" );
		$order_meta_ver = get_option( "usces_db_order_meta" );
		
		if( $access_ver != USCES_DB_ACCESS ) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			$sql = "CREATE TABLE " . $access_table . " (
				ID BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				acc_type VARCHAR( 20 ) NOT NULL ,
				acc_num1 INT( 11 ) NOT NULL DEFAULT '0',
				acc_num2 INT( 11 ) NOT NULL DEFAULT '0',
				acc_str1 VARCHAR( 100 ) NULL ,
				acc_str2 VARCHAR( 100 ) NULL ,
				acc_date DATE NOT NULL DEFAULT '0000-00-00',
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
				) ENGINE = MYISAM ;";
			
			dbDelta($sql);
			update_option( "usces_db_member", USCES_DB_MEMBER );
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
				order_item_total_price INT( 10 ) NOT NULL DEFAULT '0',
				order_getpoint INT( 10 ) NOT NULL DEFAULT '0',
				order_usedpoint INT( 10 ) NOT NULL DEFAULT '0',
				order_discount INT( 10 ) NOT NULL DEFAULT '0',
				order_shipping_charge INT( 10 ) NOT NULL DEFAULT '0',
				order_cod_fee INT( 10 ) NOT NULL DEFAULT '0',
				order_tax INT( 10 ) NOT NULL DEFAULT '0',
				order_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				order_modified VARCHAR( 20 ) NULL ,
				order_status VARCHAR( 255 ) NULL ,
				order_check VARCHAR( 255 ) NULL ,
				order_delivery_method INT( 10 ) NOT NULL DEFAULT -1,
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
				) ENGINE = MYISAM';";
		
			dbDelta($sql);
			update_option("usces_db_order_meta", USCES_DB_ORDER_META);
		}
	}
	
	function set_default_theme()
	{
		$themepath = USCES_WP_CONTENT_DIR.'/themes/ucart_default';
		$resourcepath = USCES_WP_CONTENT_DIR.'/plugins/usc-e-shop/theme/ucart_default';
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
		$datetime = date('Y-m-d H:i:s');
		$datetime_gmt = date('Y-m-d H:i:s', time()-32400);

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
				1, $datetime, $datetime_gmt, '', 'カート', '', 'publish', 
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
				1, $datetime, $datetime_gmt, '', 'メンバー', '', 'publish', 
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
		
		//footernavi page
/*		$footernaviid = usces_get_page_ID_by_pname( 'usces-footernavi', 'return' );
		if( $footernaviid === NULL ) {
			$query = $wpdb->prepare("INSERT INTO $wpdb->posts 
				(post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, 
				comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, 
				post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)
				VALUES (%d, %s, %s, %s, %s, %s, %s, 
				%s, %s, %s, %s, %s, %s, %s, %s, 
				%s, %d, %s, %d, %s, %s, %d)", 
				1, $datetime, $datetime_gmt, '', 'フッタナビ用ダミーページ', '', 'publish', 
				'closed', 'closed', '', 'usces-footernavi', '', '', $datetime, $datetime_gmt, 
				'', 0, '', 0, 'page', '', 0);
			$wpdb->query($query);
			$ser_id = $wpdb->insert_id;
			if( $ser_id !== NULL ) {
				$xml = USCES_PLUGIN_DIR . '/includes/initial_data.xml';
				$match = $this->get_initial_data($xml);
				foreach($match as $data){
					$title = $data[1];
					$status = $data[2];
					$name = $data[3];
					$content = $data[4];
					if( $name == 'usces-privacy' || $name == 'usces-company' || $name == 'usces-law' ) {
						$query2 = $wpdb->prepare("INSERT INTO $wpdb->posts 
							(post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, 
							comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, 
							post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)
							VALUES (%d, %s, %s, %s, %s, %s, %s, 
							%s, %s, %s, %s, %s, %s, %s, %s, 
							%s, %d, %s, %d, %s, %s, %d)", 
							1, $datetime, $datetime_gmt, $content, $title, '', $status, 
							'closed', 'closed', '', $name, '', '', $datetime, $datetime_gmt, 
							'', $ser_id, '', 0, 'page', '', 0);
						$wpdb->query($query2);
					}
				}
			}
		}
		
		//mainnavi page
		$mainnaviid = usces_get_page_ID_by_pname( 'usces-mainnavi', 'return' );
		if( $mainnaviid === NULL ) {
			$query = $wpdb->prepare("INSERT INTO $wpdb->posts 
				(post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, 
				comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, 
				post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)
				VALUES (%d, %s, %s, %s, %s, %s, %s, 
				%s, %s, %s, %s, %s, %s, %s, %s, 
				%s, %d, %s, %d, %s, %s, %d)", 
				1, $datetime, $datetime_gmt, '', 'メインナビ用ダミーページ', '', 'publish', 
				'closed', 'closed', '', 'usces-mainnavi', '', '', $datetime, $datetime_gmt, 
				'', 0, '', 0, 'page', '', 0);
			$wpdb->query($query);
			$ser_id = $wpdb->insert_id;
			if( $ser_id !== NULL ) {
				$xml = USCES_PLUGIN_DIR . '/includes/initial_data.xml';
				$match = $this->get_initial_data($xml);
				foreach($match as $data){
					$title = $data[1];
					$status = $data[2];
					$name = $data[3];
					$content = $data[4];
					if( $name == 'usces-inquiry' || $name == 'usces-guid' ) {
						$query2 = $wpdb->prepare("INSERT INTO $wpdb->posts 
							(post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, 
							comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, 
							post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)
							VALUES (%d, %s, %s, %s, %s, %s, %s, 
							%s, %s, %s, %s, %s, %s, %s, %s, 
							%s, %d, %s, %d, %s, %s, %d)", 
							1, $datetime, $datetime_gmt, $content, $title, '', $status, 
							'closed', 'closed', '', $name, '', '', $datetime, $datetime_gmt, 
							'', $ser_id, '', 0, 'page', '', 0);
						$wpdb->query($query2);
						$meta_id = $wpdb->insert_id;
						if( $meta_id !== NULL && $name == 'usces-inquiry' ) {
							$query3 = $wpdb->prepare("INSERT INTO $wpdb->postmeta 
								(post_id, meta_key, meta_value) VALUES (%d, %s, %s)", 
								$meta_id, '_wp_page_template', 'inquiry.php');
							$wpdb->query($query3);
						}
					}
				}
			}
		}
		
		//search in detail page
		$searchid = usces_get_page_ID_by_pname( 'usces-search-in-detail', 'return' );
		if( $searchid === NULL ) {
			$query = $wpdb->prepare("INSERT INTO $wpdb->posts 
				(post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, 
				comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, 
				post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)
				VALUES (%d, %s, %s, %s, %s, %s, %s, 
				%s, %s, %s, %s, %s, %s, %s, %s, 
				%s, %d, %s, %d, %s, %s, %d)", 
				1, $datetime, $datetime_gmt, '', 'Search in detail', '', 'publish', 
				'closed', 'closed', '', 'usces-search-in-detail', '', '', $datetime, $datetime_gmt, 
				'', 0, '', 0, 'page', '', 0);
			$wpdb->query($query);
			$ser_id = $wpdb->insert_id;
			if( $ser_id !== NULL ) {
				$query2 = $wpdb->prepare("INSERT INTO $wpdb->postmeta 
					(post_id, meta_key, meta_value) VALUES (%d, %s, %s)", 
					$ser_id, '_wp_page_template', 'uscesearch.php');
				$wpdb->query($query2);
			}
		}
*/		
		
	}
	
	function set_default_categories()
	{
		global $wpdb;
		
		
		//$wpdb->show_errors();

		//item_parent
		$query = "SELECT term_id FROM $wpdb->terms WHERE slug = 'item'";
		$item_parent = $wpdb->get_var( $query );
		if($item_parent === NULL) {
			$query = $wpdb->prepare("INSERT INTO $wpdb->terms (name, slug, term_group) VALUES (%s, %s, %d)", 
				'商品', 'item', 0);
			$wpdb->query($query);
			$item_parent = $wpdb->insert_id;
			if( $item_parent !== NULL ) {
				$query = $wpdb->prepare("INSERT INTO $wpdb->term_taxonomy (term_id, taxonomy, description, parent, count) 
					VALUES (%d, %s, %s, %d, %d)", $item_parent, 'category', '', 0, 0);
				$wpdb->query($query);
			}
		}
		update_option('usces_item_cat_parent_id', $item_parent);

		//item_reco
		$query = "SELECT term_id FROM $wpdb->terms WHERE slug = 'itemreco'";
		$item_id = $wpdb->get_var( $query );
		if($item_id === NULL) {
			$query = $wpdb->prepare("INSERT INTO $wpdb->terms (name, slug, term_group) VALUES (%s, %s, %d)", 
				'お勧め商品', 'itemreco', 0);
			$wpdb->query($query);
			$item_id = $wpdb->insert_id;
			if( $item_id !== NULL ) {
				$query = $wpdb->prepare("INSERT INTO $wpdb->term_taxonomy (term_id, taxonomy, description, parent, count) 
					VALUES (%d, %s, %s, %d, %d)", $item_id, 'category', '', $item_parent, 0);
				$wpdb->query($query);
			}
		}

		//item_new
		$query = "SELECT term_id FROM $wpdb->terms WHERE slug = 'itemnew'";
		$item_id = $wpdb->get_var( $query );
		if($item_id === NULL) {
			$query = $wpdb->prepare("INSERT INTO $wpdb->terms (name, slug, term_group) VALUES (%s, %s, %d)", 
				'新着商品', 'itemnew', 0);
			$wpdb->query($query);
			$item_id = $wpdb->insert_id;
			if( $item_id !== NULL ) {
				$query = $wpdb->prepare("INSERT INTO $wpdb->term_taxonomy (term_id, taxonomy, description, parent, count) 
					VALUES (%d, %s, %s, %d, %d)", $item_id, 'category', '', $item_parent, 0);
				$wpdb->query($query);
			}
		}

		//item_category
		$query = "SELECT term_id FROM $wpdb->terms WHERE slug = 'itemgenre'";
		$item_id = $wpdb->get_var( $query );
		if($item_id === NULL) {
			$query = $wpdb->prepare("INSERT INTO $wpdb->terms (name, slug, term_group) VALUES (%s, %s, %d)", 
				'商品ジャンル', 'itemgenre', 0);
			$wpdb->query($query);
			$item_id = $wpdb->insert_id;
			if( $item_id !== NULL ) {
				$query = $wpdb->prepare("INSERT INTO $wpdb->term_taxonomy (term_id, taxonomy, description, parent, count) 
					VALUES (%d, %s, %s, %d, %d)", $item_id, 'category', '', $item_parent, 0);
				$wpdb->query($query);
			}
		}

		//item_discount
/*		$query = "SELECT term_id FROM $wpdb->terms WHERE slug = 'itemdiscount'";
		$item_id = $wpdb->get_var( $query );
		if($item_id === NULL) {
			$query = $wpdb->prepare("INSERT INTO $wpdb->terms (name, slug, term_group) VALUES (%s, %s, %d)", 
				'特価品', 'itemdiscount', 0);
			$wpdb->query($query);
			$item_id = $wpdb->insert_id;
			if( $item_id !== NULL ) {
				$query = $wpdb->prepare("INSERT INTO $wpdb->term_taxonomy (term_id, taxonomy, description, parent, count) 
					VALUES (%d, %s, %s, %d, %d)", $item_id, 'category', '', $item_parent, 0);
				$wpdb->query($query);
			}
		}

		//news
		$query = "SELECT term_id FROM $wpdb->terms WHERE slug = 'news'";
		$item_id = $wpdb->get_var( $query );
		if($item_id === NULL) {
			$query = $wpdb->prepare("INSERT INTO $wpdb->terms (name, slug, term_group) VALUES (%s, %s, %d)", 
				'お知らせ', 'news', 0);
			$wpdb->query($query);
			$item_id = $wpdb->insert_id;
			if( $item_id !== NULL ) {
				$query = $wpdb->prepare("INSERT INTO $wpdb->term_taxonomy (term_id, taxonomy, description, parent, count) 
					VALUES (%d, %s, %s, %d, %d)", $item_id, 'category', '', 0, 0);
				$wpdb->query($query);
			}
		}

		//blog
		$query = "SELECT term_id FROM $wpdb->terms WHERE slug = 'blog'";
		$item_id = $wpdb->get_var( $query );
		if($item_id === NULL) {
			$query = $wpdb->prepare("INSERT INTO $wpdb->terms (name, slug, term_group) VALUES (%s, %s, %d)", 
				'ブログ', 'blog', 0);
			$wpdb->query($query);
			$item_id = $wpdb->insert_id;
			if( $item_id !== NULL ) {
				$query = $wpdb->prepare("INSERT INTO $wpdb->term_taxonomy (term_id, taxonomy, description, parent, count) 
					VALUES (%d, %s, %s, %d, %d)", $item_id, 'category', '', 0, 0);
				$wpdb->query($query);
			}
		}
*/
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
			if( strpos($plugin, USCES_ADMIN_SSL_BASE_NAME) )
				return true;
		}
		return false;
	}
	
	function getGuidTax() {
		if ( (int)$this->options['tax_rate'] > 0 )
			return '<em class="tax">（税別）</em>';
		else
			return '<em class="tax">（税込）</em>';
	}

	function getItemCode($post_id) {
		$str = get_post_custom_values('itemCode', $post_id);
		return $str[0];
	}
	
	function getItemName($post_id) {
		$str = get_post_custom_values('itemName', $post_id);
		return $str[0];
	}
	
	function getItemRestriction($post_id) {
		$str = get_post_custom_values('itemRestriction', $post_id);
		return $str[0];
	}
	
	function getItemPointrate($post_id) {
		$str = get_post_custom_values('itemPointrate', $post_id);
		return $str[0];
	}
	
	function getItemShipping($post_id) {
		$str = get_post_custom_values('itemShipping', $post_id);
		return $str[0];
	}
	
	function getItemShippingCharge($post_id) {
		$str = get_post_custom_values('itemShippingCharge', $post_id);
		return (int)$str[0];
	}
	
	function getItemDeliveryMethod($post_id) {
		$str = get_post_custom_values('itemDeliveryMethod', $post_id);
		return unserialize($str[0]);
	}
	
	function getItemIndividualSCharge($post_id) {
		$str = get_post_custom_values('itemIndividualSCharge', $post_id);
		return $str[0];
	}
	
	function getItemGpNum1($post_id) {
		$str = get_post_custom_values('itemGpNum1', $post_id);
		return $str[0];
	}
	
	function getItemGpNum2($post_id) {
		$str = get_post_custom_values('itemGpNum2', $post_id);
		return $str[0];
	}
	
	function getItemGpNum3($post_id) {
		$str = get_post_custom_values('itemGpNum3', $post_id);
		return $str[0];
	}
	
	function getItemGpDis1($post_id) {
		$str = get_post_custom_values('itemGpDis1', $post_id);
		return $str[0];
	}
	
	function getItemGpDis2($post_id) {
		$str = get_post_custom_values('itemGpDis2', $post_id);
		return $str[0];
	}
	
	function getItemGpDis3($post_id) {
		$str = get_post_custom_values('itemGpDis3', $post_id);
		return $str[0];
	}
	
	function getItemSku($post_id, $index = '') {
		$fields = get_post_custom($post_id);
		foreach($fields as $key => $value){
			if( preg_match('/^isku_/', $key, $match) ){
				$key = substr($key, 5);
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
		foreach($fields as $key => $value){
			if( preg_match('/^isku_/', $key, $match) ){
				$key = substr($key, 5);
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
	
	function getItemZaiko($post_id, $skukey = '') {
		$fields = get_post_custom($post_id);
		foreach((array)$fields as $key => $value){
			if( preg_match('/^isku_/', $key, $match) ){
				$key = substr($key, 5);
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
			if( preg_match('/^isku_/', $key, $match) ){
				$key = substr($key, 5);
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
		foreach($fields as $key => $value){
			$turekey = 'isku_'.$skukey;
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
		foreach($fields as $key => $value){
			if( preg_match('/^isku_/', $key, $match) ){
				$key = substr($key, 5);
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
		foreach($fields as $key => $value){
			$turekey = 'isku_'.$skukey;
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
		foreach($fields as $key => $value){
			if( preg_match('/^isku_/', $key, $match) ){
				$key = substr($key, 5);
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
	
	function getItemSkuUnit($post_id, $skukey = '') {
		$fields = get_post_custom($post_id);
		foreach($fields as $key => $value){
			if( preg_match('/^isku_/', $key, $match) ){
				$key = substr($key, 5);
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
	
	function get_itemOptionKey( $post_id ) {
		$custom_field_keys = get_post_custom_keys( $post_id );
		if(empty($custom_field_keys)) return;
		
		foreach ( $custom_field_keys as $key => $value ) {
			if ( 'iopt_' == substr($value,0 , 5) )
				$res[] = substr($value, 5);
		}
		if($res)
			natcasesort($res);
		return $res;
	}
	
	function get_itemOptions( $key, $post_id ) {
		$metakey = 'iopt_' . $key;
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
		ksort($fields);
		foreach($fields as $k => $v){
			if( preg_match('/^isku_/', $k, $match) ){
				$values = maybe_unserialize($v[0]);
				$key[] = substr($k, 5);
				$cprice[] = $values['cprice'];
				$price[] = $values['price'];
				$zaiko[] = $values['zaiko'];
				$zaikonum[] = $values['zaikonum'];
				$disp[] = $values['disp'];
				$unit[] = $values['unit'];
				$gptekiyo[] = $values['gptekiyo'];
				
				$res[substr($k, 5)]['cprice'] = $values['cprice'];
				$res[substr($k, 5)]['price'] = $values['price'];
				$res[substr($k, 5)]['zaiko'] = $values['zaiko'];
				$res[substr($k, 5)]['zaikonum'] = $values['zaikonum'];
				$res[substr($k, 5)]['disp'] = $values['disp'];
				$res[substr($k, 5)]['unit'] = $values['unit'];
				$res[substr($k, 5)]['gptekiyo'] = $values['gptekiyo'];
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
	
	function getItemIds() {
		global $wpdb;
		$query = $wpdb->prepare("SELECT ID  FROM $wpdb->posts WHERE post_mime_type = %s", 'item');
		$ids = $wpdb->get_col( $query );
		if( empty($ids) ) $ids = array();
		return $ids;
	}
	
	function getPaymentMethod( $name ) {
		$res = array();
		$payments = $this->options['payment_method'];
		foreach ( $payments as $payment ) {
			if($name = $payment['name']) {
				$res = $payment;
				break;
			}
		}
		return 	$res;
	}
	
	function order_processing( $results = array() ) {
		
		//データベース登録(function.php)
		$order_id = usces_reg_orderdata( $results );
		//var_dump($order_id);exit;
		if ( $order_id ) {
			//メール送信処理(function.php)
			$mail_res = usces_send_ordermail( $order_id );
			return 'ordercompletion';
		
		} else {
			return 'error';
		}
	
	}

	function acting_processing($module, $query) {

		$module = trim($module);
		//$usces_entries = $this->cart->get_entry();

		if( empty($module) || !file_exists($this->options['settlement_path'] . $module) ) return 'error';
		
		
		//include(USCES_PLUGIN_DIR . '/settlement/' . $module);
		if($module == 'paypal.php'){
			require_once($this->options['settlement_path'] . "paypal.php");
			paypal_submit();
		}else if($module == 'epsilon.php'){
			if ( $this->use_ssl ) {
				$redirect = str_replace('http://', 'https://', USCES_CART_URL);
			}else{
				$redirect = USCES_CART_URL;
			}
			$query .= '&settlement=epsilon&redirect_url=' . urlencode($redirect);
			header("location: " . $redirect . $query);
			exit;
		}
	}

	function inquiry_processing() {
	
		$mail_res = usces_send_inquirymail();
		
		if ( $mail_res )
			return 'inquiry_comp';
		else
			return 'inquiry_error';
	}
	
//	function widget_usces_register() {
//		if ( function_exists('register_sidebar_widget') )
//			register_sidebar_widget('usces カレンダー', array($this, 'usces_calendar'));	
//	
//	}
//	
//	function usces_calendar() {
//	
//	}
	
	function lastprocessing() {
		
		if ( $this->page == 'ordercompletion' )
			$this->cart->crear_cart();

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
					$rate = get_post_custom_values('itemPointrate', $rows['post_id']);
					$price = $this->getItemPrice($rows['post_id'], $rows['sku']) * $rows['quantity'];
					$cats = $this->get_post_term_ids($rows['post_id'], 'category');
					if ( in_array($this->options['campaign_category'], $cats) )
						$point += $price * $rate[0] / 100 * $this->options['privilege_point'];
					else
						$point += $price * $rate[0] / 100;
				}
			}
		} else {
			foreach ( $cart as $rows ) {
				$rate = get_post_custom_values('itemPointrate', $rows['post_id']);
				$price = $this->getItemPrice($rows['post_id'], $rows['sku']) * $rows['quantity'];
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
			if ( $this->options['campaign_privilege'] == 'discount' )
				$discount = $total * $this->options['privilege_discount'] / 100;
			elseif ( $this->options['campaign_privilege'] == 'point' )
				$discount = 0;
		}

		return ceil($discount * -1);
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
		
		foreach ( $cart as $rows ) {
			$s_charge_id = $this->getItemShippingCharge($rows['post_id']);
			$s_charge_index = $this->get_shipping_charge_index($s_charge_id);
			$charge = $this->options['shipping_charge'][$s_charge_index]['value'][$pref];
			if($this->getItemIndividualSCharge($rows['post_id'])){
				$individual_quant += $rows['quantity'];
				$individual_charge += $rows['quantity'] * $charge;
			}else{
				$charges[] = $charge;
			}
			$total_quant += $rows['quantity'];
		}

		if( $fixed_charge_id >= 0 ){
			$fix_charge_index = $this->get_shipping_charge_index($fixed_charge_id);
			$fix_charge = $this->options['shipping_charge'][$fix_charge_index]['value'][$pref];
			if( $total_quant > $individual_quant ){
				$charge = $fix_charge + $fix_charge * $individual_quant;
			}else{
				$charge = $fix_charge * $individual_quant;
			}
		
		}else{
			if( count($charges) > 0 ){
				rsort($charges);
				$max_charge = $charges[0];
				$charge = $max_charge + $individual_charge;
			}else{
				$charge = $individual_charge;
			}
		
		}
		
		return $charge;

	}
	
	function getCODFee($payment_name) {
		$payments = $this->getPayments($payment_name);
		$fee = $payments['settlement'] == 'COD' ? $this->options['cod_fee'] : 0;

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
		$cod_fee = $this->getCODFee($entries['order']['payment_name']);
		$get_point = $this->get_order_point( $member['ID'] );
		$use_point = $entries['order']['usedpoint'];
		$discount = $this->get_order_discount();
		$total_price = $total_items_price - $use_point + $discount + $shipping_charge + $cod_fee;
		$tax = $this->getTax( $total_price );
		$total_full_price = $total_price + $tax;

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
	
		$query = $wpdb->prepare("SELECT order_cart, order_condition, order_date, order_usedpoint, order_getpoint, 
								order_discount, order_shipping_charge, order_cod_fee, order_tax, order_status 
							FROM $order_table WHERE mem_id = %d ORDER BY order_date DESC", $mem_id);
		$results = $wpdb->get_results( $query );
	
		$i=0;
		$res = array();
		foreach ( $results as $value ) {
			if(strpos($value->order_status, 'cancel') === false && strpos($value->order_status, 'estimate') === false){
		
				$res[] = array(
							'cart' => unserialize($value->order_cart),
							'condition' => unserialize($value->order_condition),
							'getpoint' => $value->order_getpoint,
							'usedpoint' => $value->order_usedpoint,
							'discount' => $value->order_discount,
							'shipping_charge' => $value->order_shipping_charge,
							'cod_fee' => $value->order_cod_fee,
							'tax' => $value->order_tax,
							'date' => mysql2date(__('Y/m/d'), $value->order_date)
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
		$meta_key = 'itemCode';
		$query = $wpdb->prepare("SELECT p.ID  FROM $wpdb->posts AS p 
									INNER JOIN $wpdb->postmeta AS pm ON p.ID = pm.post_id 
									WHERE p.post_status = %s AND pm.meta_key = %s AND meta_value = %s ", $status, $meta_key, $itemname);
		$id = $wpdb->get_var( $query );

//		$wpdb->show_errors(); 
//		$wpdb->print_error();

		return $id;
	
	}
	
	function uscescv( &$sessid ) {
		
		$chars = '';
		$i=0;
		$h=0;
		while($h<strlen($sessid)){
			if(0 == $i % 3){
				$chars .= base_convert($i, 10, 36);
			}else{
				$chars .= substr($sessid, $h, 1);
				$h++;
			}
			$i++;
		}
		$sessid = $chars;
		//var_dump($sessid);
	}
	
	function uscesdc( &$sessid ) {
		$chars = '';
		$h=0;
		while($h<strlen($sessid)){
			if(0 != $i % 3){
				$chars .= substr($sessid, $h, 1);
			}
			$h++;
		}
		$sessid = $chars;
		
		//var_dump($sessid);
	}

	function get_visiter( $period ) {
		global $wpdb;
		if($period == 'today') {
			$date = date('Y-m-d');
			$today = date('Y-m-d');
		}else if($period == 'thismonth') {
			$date = date('Y-m-01');
			$today = date('Y-m-d');
		}else if($period == 'lastyear') {
			$date = date('Y-m-01', mktime(0, 0, 0, date('m'), 1, date('Y')-1));
			$today = date('Y-m-01', mktime(0, 0, 0, date('m'), date('d'), date('Y')-1));
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
		if($period == 'today') {
			$date = date('Y-m-d');
			$today = date('Y-m-d');
		}else if($period == 'thismonth') {
			$date = date('Y-m-01');
			$today = date('Y-m-d');
		}else if($period == 'lastyear') {
			$date = date('Y-m-01', mktime(0, 0, 0, date('m'), 1, date('Y')-1));
			$today = date('Y-m-01', mktime(0, 0, 0, date('m'), date('d'), date('Y')-1));
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
		if($period == 'today') {
			$date = date('Y-m-d 00:00:00');
			$today = date('Y-m-d 23:59:59');
		}else if($period == 'thismonth') {
			$date = date('Y-m-01 00:00:00');
			$today = date('Y-m-d 23:59:59');
		}else if($period == 'lastyear') {
			$date = date('Y-m-01 00:00:00', mktime(0, 0, 0, date('m'), 1, date('Y')-1));
			$today = date('Y-m-01 23:59:59', mktime(0, 0, 0, date('m'), date('d'), date('Y')-1));
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
		if($period == 'today') {
			$date = date('Y-m-d 00:00:00');
			$today = date('Y-m-d 23:59:59');
		}else if($period == 'thismonth') {
			$date = date('Y-m-01 00:00:00');
			$today = date('Y-m-d 23:59:59');
		}else if($period == 'lastyear') {
			$date = date('Y-m-01 00:00:00', mktime(0, 0, 0, date('m'), 1, date('Y')-1));
			$today = date('Y-m-01 23:59:59', mktime(0, 0, 0, date('m'), date('d'), date('Y')-1));
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
									INNER JOIN {$wpdb->postmeta} ON ID = post_id AND SUBSTRING(meta_key, 1, 5) = %s 
									WHERE post_mime_type = %s AND post_status = %s 
									ORDER BY ID, meta_key", 
									'isku_', 'item', 'publish');
		$res = $wpdb->get_results($query, ARRAY_A);
		
		$sku = array();
		$status = array();
		foreach((array)$res as $key => $value){
			$sku['data'][$key]['ID'] = $value['ID'];
			$sku['data'][$key]['code'] = $this->getItemCode($value['ID']);
			$sku['data'][$key]['name'] = $this->getItemName($value['ID']);
			$sku['data'][$key]['sku'] = substr($value['meta_key'], 5);
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
		$res = array();
		$order_table_name = $wpdb->prefix . "usces_order";
		$where = "";
		if($days != ''){
			$order_date = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), (date('d')-$days), date('Y')));
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

	//shortcode---------------------------------------
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
			$htm .= "<li>" . htmlspecialchars($payment['name']) . "</li>\n";
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
			$res = number_format($min) . '～' . number_format($max);
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
			'value' => 'カートへ',
		), $atts));
	
		$post_id = $this->get_ID_byItemName($item);
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
		$html .= "<input name=\"inCart[{$post_id}][{$sku}]\" type=\"submit\" id=\"inCart[{$post_id}][{$sku}]\" class=\"skubutton\" value=\"{$value}\" onclick=\"return uscesCart.intoCart('{$post_id}','{$sku}')\" />";
		$html .= "</form>";
	
		return $html;
	}

	function filter_itemPage($content){
		global $post;
		if($post->post_mime_type != 'item' || !is_single()) return $content;
		
		include( USCES_PLUGIN_DIR . '/templates/single_item.php' );
		$content = $html;

		return $content;
	}

	function filter_cartContent($content) {
		global $post;
		
		switch($this->page){
			case 'cart':
				include( USCES_PLUGIN_DIR . '/templates/cart/cart.php' );
				break;
			case 'customer':
				include( USCES_PLUGIN_DIR . '/templates/cart/customer_info.php' );
				break;
			case 'delivery':
				include( USCES_PLUGIN_DIR . '/templates/cart/delivery_info.php' );
				break;
			case 'confirm':
				include( USCES_PLUGIN_DIR . '/templates/cart/confirm.php' );
				break;
			case 'ordercompletion':
				include( USCES_PLUGIN_DIR . '/templates/cart/completion.php' );
				break;
			case 'error':
				include( USCES_PLUGIN_DIR . '/templates/cart/error.php' );
				break;
			case 'maintenance':
				include( USCES_PLUGIN_DIR . '/templates/cart/maintenance.php' );
				break;
			case 'search_item':
				include( USCES_PLUGIN_DIR . '/templates/search_item.php' );
				break;
			case 'wp_search':
				if($post->post_mime_type == 'item'){
					include( USCES_PLUGIN_DIR . '/templates/wp_search_item.php' );
				}else{
					$html = $content;
				}
				break;
			default:
				$html = $content;
		}

		$content = $html;
		
		remove_filter('the_title', array($this, 'filter_cartTitle'));

		return $content;
	}

	function filter_cartTitle($title) {

		if( $title == 'Cart' || $title == 'カート' ){
			switch($this->page){
				case 'cart':
					$newtitle = 'カート' ;
					break;
				case 'customer':
					$newtitle = 'お客様情報';
					break;
				case 'delivery':
					$newtitle = '配送・支払方法';
					break;
				case 'confirm':
					$newtitle = '確認';
					break;
				case 'ordercompletion':
					$newtitle = '完了';
					break;
				case 'error':
					$newtitle = 'エラー';
					break;
				case 'search_item':
					$newtitle = '商品カテゴリー複合検索';
					break;
				case 'maintenance':
					$newtitle = 'メンテナンス中';
					break;
				default:
					$newtitle = $title;
			}
		}else{
			$newtitle = $title;
		}
	
		return $newtitle;
	}
	
	function action_cartFilter(){
		add_filter('the_title', array($this, 'filter_cartTitle'));
		add_filter('the_content', array($this, 'filter_cartContent'),21);
	}
		
	function action_search_item(){
		include(TEMPLATEPATH . '/page.php');
		exit;
	}
		
	function filter_memberContent($content) {
		global $post;
		
		if( $this->is_member_logged_in() ) {
		
			$member_regmode = 'editmemberform';
			include( USCES_PLUGIN_DIR . '/templates/member/member.php' );
		
		} else {
		
			switch($this->page){
				case 'login':
					include( USCES_PLUGIN_DIR . '/templates/member/login.php' );
					break;
				case 'lostmemberpassword':
					include( USCES_PLUGIN_DIR . '/templates/member/lostpassword.php' );
					break;
				case 'changepassword':
					include( USCES_PLUGIN_DIR . '/templates/member/changepassword.php' );
					break;
				case 'newcompletion':
				case 'editcompletion':
				case 'lostcompletion':
				case 'changepasscompletion':
					include( USCES_PLUGIN_DIR . '/templates/member/completion.php' );
					break;
				case 'newmemberform':
					$member_form_title = '新規入会フォーム';
					$member_regmode = 'newmemberform';
					include( USCES_PLUGIN_DIR . '/templates/member/member_form.php' );
					break;
				default:
					include( USCES_PLUGIN_DIR . '/templates/member/login.php' );
			}
		
		}
		
		$content = $html;
		
		remove_filter('the_title', array($this, 'filter_memberTitle'));

		return $content;
	}

	function filter_memberTitle($title) {

		if( $title == 'Member' || $title == 'メンバー' ){
			switch($this->page){
				case 'login':
					$newtitle = '会員ログイン';
					break;
				case 'newmemberform':
					$newtitle = '新規入会フォーム';
					break;
				case 'lostmemberpassword':
					$newtitle = '新パスワード取得';
					break;
				case 'changepassword':
					$newtitle = 'パスワード変更';
					break;
				case 'newcompletion':
				case 'editcompletion':
				case 'lostcompletion':
				case 'changepasscompletion':
					$newtitle = '完了';
					break;
				case 'error':
					$newtitle = 'エラー';
					break;
				default:
					$newtitle = $title;
			}
		}else{
			$newtitle = $title;
		}
	
		return $newtitle;
	}
	
	function action_memberFilter(){
		add_filter('the_title', array($this, 'filter_memberTitle'));
		add_filter('the_content', array($this, 'filter_memberContent'),20);
	}

	function filter_usces_cart_css(){
		$path = get_stylesheet_directory_uri() . '/usces_cart.css';
		return $path;
	}
	
	function filter_divide_item(){
		global $wp_query;

		$ids = $this->getItemIds();

		if( $usces->options['divide_item'] && !is_category() && !is_search() && !is_singular() && !is_admin() ){
			$wp_query->query_vars['post__not_in'] = $ids; 
		}
		if( is_admin() ){
			//$wp_query->query_vars['category__not_in'] = array(USCES_ITEM_CAT_PARENT_ID); 
			$wp_query->query_vars['post__not_in'] = $ids;
		}
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
		
		if(strpos('?page_id=4', $link) || strpos('?page_id=3', $link) || strpos('usces-cart', $link) || strpos('usces-member', $link) )
			$link = str_replace('http://', 'https://', $link);
	
		return $link;
	}

	function filter_cart_page_header($html){
		if( !empty($this->options['cart_page_data']['header']['cart']) ){
			$html = $this->options['cart_page_data']['header']['cart'];
		}
		return $html;
	}
	
	function filter_cart_page_footer($html){
		if( !empty($this->options['cart_page_data']['footer']['cart']) ){
			$html = $this->options['cart_page_data']['footer']['cart'];
		}
		return $html;
	}
	
	function filter_customer_page_header($html){
		if( !empty($this->options['cart_page_data']['header']['customer']) ){
			$html = $this->options['cart_page_data']['header']['customer'];
		}
		return $html;
	}
	
	function filter_customer_page_footer($html){
		if( !empty($this->options['cart_page_data']['footer']['customer']) ){
			$html = $this->options['cart_page_data']['footer']['customer'];
		}
		return $html;
	}
	
	function filter_delivery_page_header($html){
		if( !empty($this->options['cart_page_data']['header']['delivery']) ){
			$html = $this->options['cart_page_data']['header']['delivery'];
		}
		return $html;
	}
	
	function filter_delivery_page_footer($html){
		if( !empty($this->options['cart_page_data']['footer']['delivery']) ){
			$html = $this->options['cart_page_data']['footer']['delivery'];
		}
		return $html;
	}
	
	function filter_confirm_page_header($html){
		if( !empty($this->options['cart_page_data']['header']['confirm']) ){
			$html = $this->options['cart_page_data']['header']['confirm'];
		}
		return $html;
	}
	
	function filter_confirm_page_footer($html){
		if( !empty($this->options['cart_page_data']['footer']['confirm']) ){
			$html = $this->options['cart_page_data']['footer']['confirm'];
		}
		return $html;
	}
	
	function filter_cartcompletion_page_header($html){
		if( !empty($this->options['cart_page_data']['header']['completion']) ){
			$html = $this->options['cart_page_data']['header']['completion'];
		}
		return $html;
	}
	
	function filter_cartcompletion_page_footer($html){
		if( !empty($this->options['cart_page_data']['footer']['completion']) ){
			$html = $this->options['cart_page_data']['footer']['completion'];
		}
		return $html;
	}
	
	function filter_login_page_header($html){
		if( !empty($this->options['member_page_data']['header']['login']) ){
			$html = $this->options['member_page_data']['header']['login'];
		}
		return $html;
	}
	
	function filter_login_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['login']) ){
			$html = $this->options['member_page_data']['footer']['login'];
		}
		return $html;
	}
	
	function filter_newmember_page_header($html){
		if( !empty($this->options['member_page_data']['header']['newmember']) ){
			$html = $this->options['member_page_data']['header']['newmember'];
		}
		return $html;
	}
	
	function filter_newmember_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['newmember']) ){
			$html = $this->options['member_page_data']['footer']['newmember'];
		}
		return $html;
	}
	
	function filter_newpass_page_header($html){
		if( !empty($this->options['member_page_data']['header']['newpass']) ){
			$html = $this->options['member_page_data']['header']['newpass'];
		}
		return $html;
	}
	
	function filter_newpass_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['newpass']) ){
			$html = $this->options['member_page_data']['footer']['newpass'];
		}
		return $html;
	}
	
	function filter_changepass_page_header($html){
		if( !empty($this->options['member_page_data']['header']['changepass']) ){
			$html = $this->options['member_page_data']['header']['changepass'];
		}
		return $html;
	}
	
	function filter_changepass_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['changepass']) ){
			$html = $this->options['member_page_data']['footer']['changepass'];
		}
		return $html;
	}
	
	function filter_memberinfo_page_header($html){
		if( !empty($this->options['member_page_data']['header']['memberinfo']) ){
			$html = $this->options['member_page_data']['header']['memberinfo'];
		}
		return $html;
	}
	
	function filter_memberinfo_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['memberinfo']) ){
			$html = $this->options['member_page_data']['footer']['memberinfo'];
		}
		return $html;
	}
	
	function filter_membercompletion_page_header($html){
		if( !empty($this->options['member_page_data']['header']['completion']) ){
			$html = $this->options['member_page_data']['header']['completion'];
		}
		return $html;
	}
	
	function filter_membercompletion_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['completion']) ){
			$html = $this->options['member_page_data']['footer']['completion'];
		}
		return $html;
	}
	
}
?>
