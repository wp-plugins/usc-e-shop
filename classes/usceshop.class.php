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
	var $options, $mail_para;
	var $login_mail, $current_member, $member_form;
	var $payment_results, $log_flg, $delim, $use_js;
	var $user_level;

	function usc_e_shop()
	{
		global $wpdb, $post, $usces_settings, $usces_states;

		usces_add_role();

		do_action('usces_construct');
		add_action('after_setup_theme', array(&$this, 'usces_session_start'));

		if ( is_admin() ){
			clean_term_cache( get_option('usces_item_cat_parent_id'), 'category' );
		}
		
		$locales = usces_locales();
		foreach($locales as $l){
			$usces_settings['language'][$l] = $l;
		}
		$usces_settings['language']['others'] = __('Follow config.php', 'usces');
		
		$this->options = get_option('usces');
		if(!isset($this->options['smtp_hostname']) || empty($this->options['smtp_hostname'])){ $this->options['smtp_hostname'] = 'localhost';}
		if(!isset($this->options['delivery_method']) || !is_array($this->options['delivery_method'])) $this->options['delivery_method'] = array();
		if(!isset($this->options['shipping_charge']) || !is_array($this->options['shipping_charge'])) $this->options['shipping_charge'] = array();
		if(!isset($this->options['membersystem_state'])) $this->options['membersystem_state'] = 'activate';
		if(!isset($this->options['membersystem_point'])) $this->options['membersystem_point'] = 'activate';
		if(!isset($this->options['use_ssl'])) $this->options['use_ssl'] = 0;
		if(!isset($this->options['point_coverage'])) $this->options['point_coverage'] = 0;
		if(!isset($this->options['use_javascript'])) $this->options['use_javascript'] = 1;
		if(!isset($this->options['privilege_discount'])) $this->options['privilege_discount'] = '';
		if(!isset($this->options['privilege_point'])) $this->options['privilege_point'] = '';
		if(!isset($this->options['campaign_privilege'])) $this->options['campaign_privilege'] = '';
		if(!isset($this->options['campaign_category'])) $this->options['campaign_category'] = 0;
		if(!isset($this->options['campaign_schedule']['start'])) $this->options['campaign_schedule']['start'] = array();
		if(!isset($this->options['campaign_schedule']['end'])) $this->options['campaign_schedule']['end'] = array();
		if(!isset($this->options['acting_settings']['paypal']['ec_activate'])) $this->options['acting_settings']['paypal']['ec_activate'] = '';
		if(!isset($this->options['purchase_limit'])) $this->options['purchase_limit'] = '';
		if(!isset($this->options['point_rate'])) $this->options['point_rate'] = '';
		if(!isset($this->options['shipping_rule'])) $this->options['shipping_rule'] = '';
		if(!isset($this->options['company_name'])) $this->options['company_name'] = '';
		if(!isset($this->options['address1'])) $this->options['address1'] = '';
		if(!isset($this->options['address2'])) $this->options['address2'] = '';
		if(!isset($this->options['zip_code'])) $this->options['zip_code'] = '';
		if(!isset($this->options['tel_number'])) $this->options['tel_number'] = '';
		if(!isset($this->options['fax_number'])) $this->options['fax_number'] = '';
		if(!isset($this->options['order_mail'])) $this->options['order_mail'] = '';
		if(!isset($this->options['inquiry_mail'])) $this->options['inquiry_mail'] = '';
		if(!isset($this->options['sender_mail'])) $this->options['sender_mail'] = '';
		if(!isset($this->options['error_mail'])) $this->options['error_mail'] = '';
		if(!isset($this->options['copyright'])) $this->options['copyright'] = '';
		if(!isset($this->options['postage_privilege'])) $this->options['postage_privilege'] = '';
		if(!isset($this->options['shipping_rule'])) $this->options['shipping_rule'] = '';
		if(!isset($this->options['tax_rate'])){
			$this->options['tax_rate'] = '';
			$this->options['tax_method'] = 'cutting';
			$this->options['tax_mode'] = 'include';
			$this->options['tax_target'] = 'products';
		}else{
			if(!isset($this->options['tax_mode'])) $this->options['tax_mode'] = empty($this->options['tax_rate']) ? 'include' : 'exclude';
			if(!isset($this->options['tax_target'])) $this->options['tax_target'] = 'all';
		}
		if(!isset($this->options['transferee'])) $this->options['transferee'] = '';
		if(!isset($this->options['membersystem_state'])) $this->options['membersystem_state'] = 'activate';
		if(!isset($this->options['membersystem_point'])) $this->options['membersystem_point'] = '';
		if(!isset($this->options['point_rate'])) $this->options['point_rate'] = '';
		if(!isset($this->options['start_point'])) $this->options['start_point'] = '';
		if(!isset($this->options['point_coverage'])) $this->options['point_coverage'] = 1;
		if(!isset($this->options['point_assign'])) $this->options['point_assign'] = 1;//20120919ysk 0000573
		if(!isset($this->options['cod_type'])) $this->options['cod_type'] = 'fix';
		if(!isset($this->options['newmem_admin_mail'])) $this->options['newmem_admin_mail'] = 0;
		if(!isset($this->options['delmem_admin_mail'])) $this->options['delmem_admin_mail'] = 1;
		if(!isset($this->options['delmem_customer_mail'])) $this->options['delmem_customer_mail'] = 1;
		if(!isset($this->options['mail_data']['title'])) $this->options['mail_data']['title'] = array('thankyou'=>'','order'=>'','inquiry'=>'','returninq'=>'','membercomp'=>'','completionmail'=>'', 'ordermail'=>'','changemail'=>'','receiptmail'=>'','mitumorimail'=>'','cancelmail'=>'','othermail'=>'');
		if(!isset($this->options['mail_data']['header'])) $this->options['mail_data']['header'] = array('thankyou'=>'','order'=>'','inquiry'=>'','returninq'=>'','membercomp'=>'','completionmail'=>'', 'ordermail'=>'','changemail'=>'','receiptmail'=>'','mitumorimail'=>'','cancelmail'=>'','othermail'=>'');
		if(!isset($this->options['mail_data']['footer'])) $this->options['mail_data']['footer'] = array('thankyou'=>'','order'=>'','inquiry'=>'','returninq'=>'','membercomp'=>'','completionmail'=>'', 'ordermail'=>'','changemail'=>'','receiptmail'=>'','mitumorimail'=>'','cancelmail'=>'','othermail'=>'');
		if(!isset($this->options['cart_page_data']['header'])) $this->options['cart_page_data']['header'] = array('cart'=>'','customer'=>'','delivery'=>'','confirm'=>'','completion'=>'');
		if(!isset($this->options['cart_page_data']['footer'])) $this->options['cart_page_data']['footer'] = array('cart'=>'','customer'=>'','delivery'=>'','confirm'=>'','completion'=>'');
		if(!isset($this->options['member_page_data']['header'])) $this->options['member_page_data']['header'] = array('login'=>'','newmember'=>'','newpass'=>'','changepass'=>'','memberinfo'=>'','completion'=>'');
		if(!isset($this->options['member_page_data']['footer'])) $this->options['member_page_data']['footer'] = array('login'=>'','newmember'=>'','newpass'=>'','changepass'=>'','memberinfo'=>'','completion'=>'');

		if(!isset($this->options['shortest_delivery_time'])) $this->options['shortest_delivery_time'] = '0';
		if(!isset($this->options['delivery_after_days'])) $this->options['delivery_after_days'] = 15;
		if(!isset($this->options['delivery_days'])) $this->options['delivery_days'] = array();
		if(!isset($this->options['delivery_time_limit']['hour'])) $this->options['delivery_time_limit']['hour'] = '00';
		if(!isset($this->options['delivery_time_limit']['min'])) $this->options['delivery_time_limit']['min'] = '00';

		if(!isset($this->options['divide_item'])) $this->options['divide_item'] = 0;
		if(!isset($this->options['itemimg_anchor_rel'])) $this->options['itemimg_anchor_rel'] = '';
		if(!isset($this->options['fukugo_category_orderby'])) $this->options['fukugo_category_orderby'] = 'ID';
		if(!isset($this->options['fukugo_category_order'])) $this->options['fukugo_category_order'] = 'ASC';
		if(!isset($this->options['settlement_path'])) $this->options['settlement_path'] = USCES_PLUGIN_DIR . '/settlement/';
		if(!isset($this->options['logs_path'])) $this->options['logs_path'] = '';
		if(!isset($this->options['use_ssl'])) $this->options['use_ssl'] = 0;
		if(!isset($this->options['ssl_url'])) $this->options['ssl_url'] = '';
		if(!isset($this->options['ssl_url_admin'])) $this->options['ssl_url_admin'] = '';
		if(!isset($this->options['inquiry_id'])) $this->options['inquiry_id'] = '';
		if(!isset($this->options['system']['orderby_itemsku'])) $this->options['system']['orderby_itemsku'] = 0;
		if(!isset($this->options['system']['orderby_itemopt'])) $this->options['system']['orderby_itemopt'] = 0;
		if(!isset($this->options['system']['front_lang'])) $this->options['system']['front_lang'] = usces_get_local_language();
		if(!isset($this->options['system']['currency'])) $this->options['system']['currency'] = usces_get_base_country();
		if(!isset($this->options['system']['addressform'])) $this->options['system']['addressform'] = usces_get_local_addressform();
		if(!isset($this->options['system']['target_market'])) $this->options['system']['target_market'] = usces_get_local_target_market();
		if(!isset($this->options['system']['no_cart_css'])) $this->options['system']['no_cart_css'] = 0;
		if(!isset($this->options['system']['dec_orderID_flag'])) $this->options['system']['dec_orderID_flag'] = 0;
		if(!isset($this->options['system']['dec_orderID_prefix'])) $this->options['system']['dec_orderID_prefix'] = '';
		if(!isset($this->options['system']['dec_orderID_digit'])) $this->options['system']['dec_orderID_digit'] = 8;
		if(!isset($this->options['system']['subimage_rule'])) $this->options['system']['subimage_rule'] = 1;
		if(!isset($this->options['system']['pdf_delivery'])) $this->options['system']['pdf_delivery'] = 0;
		if(!isset($this->options['system']['member_pass_rule_min']) || empty($this->options['system']['member_pass_rule_min'])) $this->options['system']['member_pass_rule_min'] = 6;
		if(!isset($this->options['system']['member_pass_rule_max']) || empty($this->options['system']['member_pass_rule_max'])) $this->options['system']['member_pass_rule_max'] = '';
		if(!isset($this->options['system']['csv_encode_type'])) $this->options['system']['csv_encode_type'] = 0;
		if(!isset($this->options['acting_settings']['zeus'])) $this->options['acting_settings']['zeus'] = array('activate'=>'','card_activate'=>'','clientip'=>'','authkey'=>'','connection'=>'','3dsecure'=>'','security'=>'','quickcharge'=>'', 'howpay'=>'','bank_activate'=>'','clientip_bank'=>'','testid_bank'=>'','conv_activate'=>'','clientip_conv'=>'','testid_conv'=>'','test_type_conv'=>'');
		if(!isset($this->options['acting_settings']['zeus']['connection'])) $this->options['acting_settings']['zeus']['connection'] = '1';
		if(!isset($this->options['acting_settings']['zeus']['3dsecur'])) $this->options['acting_settings']['zeus']['3dsecur'] = '2';
		if(!isset($this->options['acting_settings']['zeus']['security'])) $this->options['acting_settings']['zeus']['security'] = '2';
		if(!isset($this->options['acting_settings']['remise'])) $this->options['acting_settings']['remise'] = array('activate'=>'','plan'=>'','SHOPCO'=>'','HOSTID'=>'','card_activate'=>'','card_jb'=>'', 'payquick'=>'','howpay'=>'','continuation'=>'','card_pc_ope'=>'','send_url_pc'=>'','conv_activate'=>'','S_PAYDATE'=>'','conv_pc_ope'=>'','send_url_cvs_pc'=>'');
		if(!isset($this->options['acting_settings']['jpayment'])) $this->options['acting_settings']['jpayment'] = array('activate'=>'','aid'=>'','card_activate'=>'','card_jb'=>'','conv_activate'=>'','webm_activate'=>'', 'bitc_activate'=>'','suica_activate'=>'','bank_activate'=>'');
		if(!isset($this->options['acting_settings']['paypal'])) $this->options['acting_settings']['paypal'] = array('activate'=>'','ec_activate'=>'','sandbox'=>'','user'=>'','pwd'=>'','signature'=>'', 'continuation'=>'');

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

		$this->check_display_mode();
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
				define('USCES_COOKIEPATH', preg_replace('|https?://[^/]+|i', '', $ssl_url . '/' ) );
			}else{
				define('USCES_FRONT_PLUGIN_URL', USCES_WP_CONTENT_URL . '/plugins/' . USCES_PLUGIN_FOLDER);
				define('USCES_COOKIEPATH', COOKIEPATH);
			}
			define('USCES_SSL_URL', $ssl_url);
			define('USCES_SSL_URL_ADMIN', $ssl_url_admin);
		}else{
			define('USCES_FRONT_PLUGIN_URL', USCES_WP_CONTENT_URL . '/plugins/' . USCES_PLUGIN_FOLDER);
			define('USCES_SSL_URL', get_option('home'));
			define('USCES_SSL_URL_ADMIN', site_url());
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
		define('USCES_MYSQL_VERSION', $wpdb->db_version());
		define('USCES_JP', ('ja' === get_locale() ? true : false));
		
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
		
		if( isset($parts['query']) ){
			parse_str($parts['query'], $query);
		}
		
		if( false !== strpos($link, '/usces-cart') || (isset( $query['page_id']) && $query['page_id'] == USCES_CART_NUMBER) ){
			$link = USCES_CART_URL;
			
		}elseif( false !== strpos($link, '/usces-member') || (isset( $query['page_id']) && $query['page_id'] == USCES_MEMBER_NUMBER) ){
			$link = USCES_MEMBER_URL;
		
		}elseif( !empty($this->options['inquiry_id']) && (false !== strpos($link, '/usces-inquiry') || (isset( $query['page_id']) && $query['page_id'] == $this->options['inquiry_id'])) ){
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
		}else{
			$link = apply_filters('usces_ssl_contents_link', $link);
		}
		return $link;
	}

	function ssl_admin_ajax_url(){
		$path = '/wp-admin/admin-ajax.php';
		if( $this->use_ssl && ($this->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $this->is_inquiry_page($_SERVER['REQUEST_URI'])) ){
			$link = USCES_SSL_URL_ADMIN . '/wp-admin/admin-ajax.php';
		}else{
			$link = site_url( $path );
		}
		$link = apply_filters('ssl_admin_ajax_url', $link);
		return $link;
	}

	function usces_ssl_attachment_link($link)
	{
		if( $this->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $this->is_inquiry_page($_SERVER['REQUEST_URI']) ){
			$link = str_replace(site_url(), USCES_SSL_URL_ADMIN, $link);
		}else{
			$link = apply_filters('usces_ssl_attachment_link', $link);
		}
		return $link;
	}

	function usces_ssl_icon_dir_uri($uri)
	{
		if( $this->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $this->is_inquiry_page($_SERVER['REQUEST_URI']) ){
			$uri = USCES_SSL_URL_ADMIN. '/' . WPINC . '/images/crystal';
		}else{
			$link = apply_filters('usces_ssl_icon_dir_uri', $uri);
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
		}else{
			$link = apply_filters('usces_ssl_script_link', $link);
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
	
		add_object_page('Welcart Shop', 'Welcart Shop', 'level_2', USCES_PLUGIN_BASENAME, array($this, 'admin_top_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('Home','usces'), __('Home','usces'), 'level_2', USCES_PLUGIN_BASENAME, array($this, 'admin_top_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('Master Items','usces'), __('Master Items','usces'), 'level_2', 'usces_itemedit', array($this, 'item_master_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('Add New Item','usces'), __('Add New Item','usces'), 'level_2', 'usces_itemnew', array($this, 'item_master_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('General Setting','usces'), __('General Setting','usces'), 'level_6', 'usces_initial', array($this, 'admin_setup_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('Business Days Setting','usces'), __('Business Days Setting','usces'), 'level_6', 'usces_schedule', array($this, 'admin_schedule_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('Shipping Setting','usces'), __('Shipping Setting','usces'), 'level_6', 'usces_delivery', array($this, 'admin_delivery_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('E-mail Setting','usces'), __('E-mail Setting','usces'), 'level_6', 'usces_mail', array($this, 'admin_mail_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('Cart Page Setting','usces'), __('Cart Page Setting','usces'), 'level_6', 'usces_cart', array($this, 'admin_cart_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('Member Page Setting','usces'), __('Member Page Setting','usces'), 'level_6', 'usces_member', array($this, 'admin_member_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('System Setting','usces'), __('System Setting','usces'), 'administrator', 'usces_system', array($this, 'admin_system_page'));
		add_submenu_page(USCES_PLUGIN_BASENAME, __('Settlement Setting','usces'), __('Settlement Setting','usces'), 'administrator', 'usces_settlement', array($this, 'admin_settlement_page'));
		//add_submenu_page(USCES_PLUGIN_BASENAME, __('Backup','usces'), __('Backup','usces'), 'level_6', 'usces_backup', array($this, 'admin_backup_page'));
		do_action('usces_action_shop_admin_menue');
		
		add_object_page('Welcart Management', 'Welcart Management', 'level_5', 'usces_orderlist', array($this, 'order_list_page'));
		add_submenu_page('usces_orderlist', __('Order List','usces'), __('Order List','usces'), 'level_5', 'usces_orderlist', array($this, 'order_list_page'));
		add_submenu_page('usces_orderlist', __('New Order or Estimate','usces'), __('New Order or Estimate','usces'), 'level_5', 'usces_ordernew', array($this, 'order_list_page'));
		add_submenu_page('usces_orderlist', __('List of Members','usces'), __('List of Members','usces'), 'level_5', 'usces_memberlist', array($this, 'member_list_page'));
		add_submenu_page('usces_orderlist', __('New Membership Registration','usces'), __('New Membership Registration','usces'), 'level_5', 'usces_membernew', array($this, 'member_list_page'));
		do_action('usces_action_management_admin_menue');
	}


	/* Item Master Page */
	function item_master_page() {
		global $wpdb, $wp_locale;
		global $wp_query;
		
		if(empty($this->action_message) || WCUtils::is_blank($this->action_message) ) {
			$this->action_status = 'none';
			$this->action_message = '';
		}
		
		if($_REQUEST['page'] == 'usces_itemnew'){
			$action = 'new';
		}else{
			$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
		}
		do_action( 'usces_action_item_master_page', $action );
		switch ( $action ) {
			case 'dlitemlist':
				usces_download_item_list();
				break;
			case 'upload_register':
				require_once(USCES_PLUGIN_DIR . '/includes/usces_item_master_upload_register.php');
				break;
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
		$order_edit_form = apply_filters( 'usces_admin_order_edit_form', USCES_PLUGIN_DIR . '/includes/order_edit_form.php' );
		$order_list = apply_filters( 'usces_admin_order_list', USCES_PLUGIN_DIR . '/includes/order_list.php' );

		if(empty($this->action_message) || WCUtils::is_blank($this->action_message) ) {
			$this->action_status = 'none';
			$this->action_message = '';
		}
		if($_REQUEST['page'] == 'usces_ordernew'){
			$order_action = 'new';
		}else{
			$order_action = isset($_REQUEST['order_action']) ? $_REQUEST['order_action'] : '';
		}
		do_action('usces_action_order_list_page', $order_action);
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
				check_admin_referer( 'order_edit', 'wc_nonce' );
				do_action('usces_pre_update_orderdata', $_REQUEST['order_id']);
				$res = usces_update_orderdata();
				if ( 1 === $res ) {
					$this->set_action_status('success', __('order date is updated','usces').' <a href="'.stripslashes( $_POST['usces_referer'] ).'">'.__('back to the summary','usces').'</a>');
				} elseif ( 0 === $res ) {
					$this->set_action_status('none', '');
				} else {
					$this->set_action_status('error', 'ERROR : '.__('failure in update','usces'));
				}
				do_action('usces_after_update_orderdata', $_REQUEST['order_id'], $res);
				require_once($order_edit_form);	
				break;
			case 'newpost':
				check_admin_referer( 'order_edit', 'wc_nonce' );
				do_action('usces_pre_new_orderdata');
				$res = usces_new_orderdata();
				if ( 1 === $res ) {
					$this->set_action_status('success', __('New date is add','usces'));
				} elseif ( 0 === $res ) {
					$this->set_action_status('none', '');
				} else {
					$this->set_action_status('error', 'ERROR : '.__('failure in addition','usces'));
				}
				do_action('usces_after_new_orderdata', $res);
				$_REQUEST['order_action'] = 'edit';
				$order_action = $_REQUEST['order_action'];
				require_once($order_edit_form);	
				break;
			case 'new':
			case 'edit':
				require_once($order_edit_form);
				break;
			case 'delete':
				check_admin_referer( 'order_list', 'wc_nonce' );
				do_action('usces_pre_delete_orderdata', $_REQUEST['order_id']);
				$res = usces_delete_orderdata();
				if ( 1 === $res ) {
					$this->set_action_status('success', __('the order date is deleted','usces'));
				} elseif ( 0 === $res ) {
					$this->set_action_status('none', '');
				} else {
					$this->set_action_status('error', 'ERROR : '.__('failure in delete','usces'));
				}
				do_action('usces_after_delete_orderdata', $_REQUEST['order_id'], $res);
			default:
				require_once($order_list);
		}
	}
	
	/* member list page */
	function member_list_page() {
		$member_edit_form = apply_filters( 'usces_admin_member_edit_form', USCES_PLUGIN_DIR . '/includes/member_edit_form.php' );
		$member_list = apply_filters( 'usces_admin_member_list', USCES_PLUGIN_DIR . '/includes/member_list.php' );

		if(empty($this->action_message) || WCUtils::is_blank($this->action_message) ) {
			$this->action_status = 'none';
			$this->action_message = '';
		}
		if( $_REQUEST['page'] == 'usces_membernew' && !isset($_REQUEST['member_action']) ){
			$member_action = 'new';
		}elseif( $_REQUEST['page'] == 'usces_membernew' && isset($_REQUEST['member_action']) ){
			$member_action = 'newpost';
		}else{
			$member_action = isset($_REQUEST['member_action']) ? $_REQUEST['member_action'] : '';
		}
		switch ($member_action) {
//			case 'upmemberlist':
//				usces_upload_member_list();
//				break;
//20100908ysk start
			case 'dlmemberlist':
				usces_download_member_list();
				break;
//20100908ysk end
			case 'editpost':
				check_admin_referer( 'post_member', 'wc_nonce');

				$this->error_message = $this->admin_member_check();
				if( WCUtils::is_blank($this->error_message) ){
					$res = usces_update_memberdata();
					if ( 1 === $res ) {
						$this->set_action_status('success', __('Membership information is updated','usces'));
					} elseif ( 0 === $res ) {
						$this->set_action_status('none', '');
					} else {
						$this->set_action_status('error', 'ERROR : '.__('failure in update','usces'));
					}
				}
				require_once($member_edit_form);	
				break;
			case 'newpost':
				check_admin_referer( 'post_member', 'wc_nonce');

				$this->error_message = $this->admin_new_member_check();
				if( WCUtils::is_blank($this->error_message) ){
					$res = usces_new_memberdata();
					if ( 1 === $res ) {
						$this->set_action_status('success', __('New member registration is complete.', 'usces'));
						$_REQUEST['member_action'] = 'edit';
						$member_action = $_REQUEST['member_action'];
					} elseif ( 0 === $res ) {
						$this->set_action_status('none', '');
					} else {
						$this->set_action_status('error', 'ERROR : '.__('Failed to new member registration.','usces'));
					}
				}
				require_once($member_edit_form);
				break;

			case 'new':
			case 'edit':
				require_once($member_edit_form);	
				break;
			case 'delete':
				check_admin_referer( 'delete_member', 'wc_nonce');
					
				$res = usces_delete_memberdata();
				if ( 1 === $res ) {
					$this->set_action_status('success', __('The member data is deleted','usces'));
				} elseif ( 0 === $res ) {
					$this->set_action_status('none', '');
				} else {
					$this->set_action_status('error', 'ERROR : '.__('failure in delete','usces'));
				}
			default:
				require_once($member_list);	
		}

	}
	
	/* admin backup page */
	function admin_backup_page() {

		if(empty($this->action_message) || WCUtils::is_blank($this->action_message) ) {
			$this->action_status = 'none';
			$this->action_message = '';
		}
		require_once(USCES_PLUGIN_DIR . '/includes/admin_backup.php');	

	}
	
	/* Shop Top Page */
	function admin_top_page() {
		$path = apply_filters( 'usces_filter_admin_top_page', USCES_PLUGIN_DIR . '/includes/admin_top.php');
		require_once($path);	

	}
	
	/* Shop Setup Page */
	function admin_setup_page() {
		$this->options = get_option('usces');
		//$this->options = array();
		if(isset($_POST['usces_option_update'])) {

			check_admin_referer('admin_setup', 'wc_nonce');

			$_POST = $this->stripslashes_deep_post($_POST);
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
			$this->options['point_rate'] = isset($_POST['point_rate']) ? (int)$_POST['point_rate'] : 1;
			$this->options['start_point'] = isset($_POST['start_point']) ? (int)$_POST['start_point'] : '';
			$this->options['shipping_rule'] = isset($_POST['shipping_rule']) ? trim($_POST['shipping_rule']) : '';
			$this->options['tax_mode'] = isset($_POST['tax_mode']) ? trim($_POST['tax_mode']) : 'include';
			$this->options['tax_target'] = isset($_POST['tax_target']) ? trim($_POST['tax_target']) : 'products';
			$this->options['tax_rate'] = isset($_POST['tax_rate']) ? (int)$_POST['tax_rate'] : '';
			$this->options['tax_method'] = isset($_POST['tax_method']) ? trim($_POST['tax_method']) : '';
			$this->options['cod_type'] = isset($this->options['cod_type']) ? $this->options['cod_type'] : 'fix';
			$this->options['transferee'] = isset($_POST['transferee']) ? trim($_POST['transferee']) : '';
			$this->options['copyright'] = isset($_POST['copyright']) ? trim($_POST['copyright']) : '';
			$this->options['membersystem_state'] = isset($_POST['membersystem_state']) ? trim($_POST['membersystem_state']) : '';
			$this->options['membersystem_point'] = isset($_POST['membersystem_point']) ? trim($_POST['membersystem_point']) : '';
			$this->options['point_coverage'] = isset($_POST['point_coverage']) ? (int)$_POST['point_coverage'] : 0;
			$this->options['point_assign'] = isset($_POST['point_assign']) ? (int)$_POST['point_assign'] : 1;//20120919ysk 0000573

			$this->options = apply_filters( 'usces_filter_admin_setup_options', $this->options );

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

			check_admin_referer( 'admin_schedule', 'wc_nonce' );

			$_POST = $this->stripslashes_deep_post($_POST);

			$this->options['campaign_schedule'] = isset($_POST['campaign_schedule']) ? $_POST['campaign_schedule'] : '0';
			if(isset($_POST['business_days']))
				$this->options['business_days'] = $_POST['business_days'];

			update_option('usces', $this->options);
			
			do_action( 'usces_action_admin_schedule_update' );
			
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

			check_admin_referer( 'admin_delivery', 'wc_nonce' );

			$_POST = $this->stripslashes_deep_post($_POST);

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
		global $allowedposttags;
	
		$this->options = get_option('usces');

		if(isset($_POST['usces_option_update'])) {

			check_admin_referer( 'admin_mail', 'wc_nonce' );

			$_POST = $this->stripslashes_deep_post($_POST);
		
			$this->options['smtp_hostname'] = trim($_POST['smtp_hostname']);
			$this->options['newmem_admin_mail'] = (int)$_POST['newmem_admin_mail'];
			$this->options['delmem_admin_mail'] = (int)$_POST['delmem_admin_mail'];
			$this->options['delmem_customer_mail'] = (int)$_POST['delmem_customer_mail'];
		
			foreach ( $_POST['title'] as $key => $value ) {
				if( WCUtils::is_blank($value) ) {
					$this->options['mail_data']['title'][$key] = $this->options['mail_default']['title'][$key];
				}else{
					$this->options['mail_data']['title'][$key] = trim($value);
				}
			}
			foreach ( $_POST['header'] as $key => $value ) {
				if( WCUtils::is_blank($value) ) {
					$this->options['mail_data']['header'][$key] = $this->options['mail_default']['header'][$key];
				}else{
					$this->options['mail_data']['header'][$key] = $value;
				}
			}
			foreach ( $_POST['footer'] as $key => $value ) {
				if( WCUtils::is_blank($value) ) {
					$this->options['mail_data']['footer'][$key] = $this->options['mail_default']['footer'][$key];
				}else{
					$this->options['mail_data']['footer'][$key] = $value;
				}
			}

			
			$this->action_status = 'success';
			$this->action_message = __('options are updated','usces');
			
		} else {
		
			foreach ( (array)$this->options['mail_data']['title'] as $key => $value ) {
				if( WCUtils::is_blank($value) ) {
					$this->options['mail_data']['title'][$key] = $this->options['mail_default']['title'][$key];
				}
			}
			foreach ( (array)$this->options['mail_data']['header'] as $key => $value ) {
				if( WCUtils::is_blank($value) ) {
					$this->options['mail_data']['header'][$key] = $this->options['mail_default']['header'][$key];
				}
			}
			foreach ( (array)$this->options['mail_data']['footer'] as $key => $value ) {
				if( WCUtils::is_blank($value) ) {
					$this->options['mail_data']['footer'][$key] = $this->options['mail_default']['footer'][$key];
				}
			}

			$this->action_status = 'none';
			$this->action_message = '';
			
		}
	
		update_option('usces', $this->options);
		do_action( 'usces_action_admin_mail_page' );
		
		require_once(USCES_PLUGIN_DIR . '/includes/admin_mail.php');

	}
	
	/* Admin Cart Page */
	function admin_cart_page() {
		global $allowedposttags;
		$this->options = get_option('usces');

		if(isset($_POST['usces_option_update'])) {

			check_admin_referer( 'admin_cart', 'wc_nonce' );

			$_POST = $this->stripslashes_deep_post($_POST);

			foreach ( $this->options['indi_item_name'] as $key => $value ) {
				$this->options['indi_item_name'][$key] = isset($_POST['indication'][$key]) ? 1 : 0;
			}
			foreach ( $_POST['position'] as $key => $value ) {
				$this->options['pos_item_name'][$key] = $value;
			}
			foreach ( $_POST['header'] as $key => $value ) {
				$this->options['cart_page_data']['header'][$key] = addslashes(wp_kses($value, $allowedposttags));
			}
			foreach ( $_POST['footer'] as $key => $value ) {
				$this->options['cart_page_data']['footer'][$key] = addslashes(wp_kses($value, $allowedposttags));
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

			check_admin_referer( 'admin_member', 'wc_nonce' );

			$_POST = $this->stripslashes_deep_post($_POST);

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
		$action_status = '';//20120511ysk 0000470

		$this->options = get_option('usces');

		if(isset($_POST['usces_option_update'])) {

			check_admin_referer( 'admin_system', 'wc_nonce' );

			$_POST = $this->stripslashes_deep_post($_POST);

			$this->options['divide_item'] = isset($_POST['divide_item']) ? 1 : 0;
			$this->options['itemimg_anchor_rel'] = isset($_POST['itemimg_anchor_rel']) ? trim($_POST['itemimg_anchor_rel']) : '';
			$this->options['fukugo_category_orderby'] = isset($_POST['fukugo_category_orderby']) ? $_POST['fukugo_category_orderby'] : '';
			$this->options['fukugo_category_order'] = isset($_POST['fukugo_category_order']) ? $_POST['fukugo_category_order'] : '';
			$this->options['settlement_path'] = isset($_POST['settlement_path']) ? $_POST['settlement_path'] : '';
			if( WCUtils::is_blank($this->options['settlement_path']) ) $this->options['settlement_path'] = USCES_PLUGIN_DIR . '/settlement/';
			$sl = substr($this->options['settlement_path'], -1);
			if($sl != '/' && $sl != '\\') $this->options['settlement_path'] .= '/';
			$this->options['logs_path'] = isset($_POST['logs_path']) ? $_POST['logs_path'] : '';
			if( !WCUtils::is_blank($this->options['logs_path']) ){
				$sl = substr($this->options['logs_path'], -1);
				if($sl == '/' || $sl == '\\') $this->options['logs_path'] = substr($this->options['logs_path'], 0, -1);
			}
			$this->options['use_ssl'] = isset($_POST['use_ssl']) ? 1 : 0;
			$this->options['ssl_url'] = isset($_POST['ssl_url']) ? rtrim($_POST['ssl_url'], '/') : '';
			$this->options['ssl_url_admin'] = isset($_POST['ssl_url_admin']) ? rtrim($_POST['ssl_url_admin'], '/') : '';
			if( WCUtils::is_blank($this->options['ssl_url']) || WCUtils::is_blank($this->options['ssl_url_admin']) ) $this->options['use_ssl'] = 0;
			$this->options['inquiry_id'] = isset($_POST['inquiry_id']) ? esc_html(rtrim($_POST['inquiry_id'])) : '';
			$this->options['use_javascript'] = isset($_POST['use_javascript']) ? (int)$_POST['use_javascript'] : 1;
			$this->options['system']['front_lang'] = (isset($_POST['front_lang']) && 'others' != $_POST['front_lang']) ? $_POST['front_lang'] : usces_get_local_language();
			$this->options['system']['currency'] = (isset($_POST['currency']) && 'others' != $_POST['currency']) ? $_POST['currency'] : usces_get_base_country();
			$this->options['system']['addressform'] = (isset($_POST['addressform']) ) ? $_POST['addressform'] : usces_get_local_addressform();
			$this->options['system']['target_market'] = (isset($_POST['target_market']) ) ? $_POST['target_market'] : usces_get_local_target_market();
			$this->options['system']['no_cart_css'] = isset($_POST['no_cart_css']) ? 1 : 0;
			$this->options['system']['dec_orderID_flag'] = isset($_POST['dec_orderID_flag']) ? (int)$_POST['dec_orderID_flag'] : 0;
			$this->options['system']['dec_orderID_prefix'] = isset($_POST['dec_orderID_prefix']) ? esc_html(rtrim($_POST['dec_orderID_prefix'])) : '';
			$this->options['system']['pdf_delivery'] = isset($_POST['pdf_delivery']) ? (int)$_POST['pdf_delivery'] : 0;
			$this->options['system']['member_pass_rule_min'] = isset($_POST['member_pass_rule_min']) ? (int)$_POST['member_pass_rule_min'] : 6;
			$this->options['system']['member_pass_rule_max'] = isset($_POST['member_pass_rule_max']) && !empty($_POST['member_pass_rule_max']) ? (int)$_POST['member_pass_rule_max'] : '';
			$this->options['system']['csv_encode_type'] = isset($_POST['csv_encode_type']) ? (int)$_POST['csv_encode_type'] : 0;

			if( isset($_POST['dec_orderID_digit']) ){
				$dec_orderID_digit = (int)rtrim($_POST['dec_orderID_digit']);
				if( 6 > $dec_orderID_digit ){
					$this->options['system']['dec_orderID_digit'] = 6;
				}else{
					$this->options['system']['dec_orderID_digit'] = $dec_orderID_digit;
				}
			}else{
				$this->options['system']['dec_orderID_digit'] = 6;
			}
			$this->options['system']['subimage_rule'] = isset($_POST['subimage_rule']) ? (int)$_POST['subimage_rule'] : 0;
//20110331ysk start
			unset($this->options['province']);
			//$action_status = '';//20120511ysk 0000470
			foreach((array)$this->options['system']['target_market'] as $target_market) {
				$province = array();
				if(!empty($_POST['province_'.$target_market])) {
					$temp_pref = explode("\n", $_POST['province_'.$target_market]);
//20120123ysk start 0000386
					//$province[] = __('-- Select --', 'usces');
					$province[] = '-- Select --';
//20120123ysk end
//20120725ysk start 0000537
					//for($i = 0; $i < count($temp_pref); $i++) {
						//$province[] = trim($temp_pref[$i]);
					foreach( $temp_pref as $pref ) {
						if( !WCUtils::is_blank($pref) ) 
							$province[] = trim($pref);
					}
					if( 1 == count($province) ) 
						$action_status = 'error';
//20120725ysk end
				} else {
					if(isset($usces_states[$target_market]) && is_array($usces_states[$target_market])) {
						$province = $usces_states[$target_market];
					} else {
						$action_status = 'error';
					}
				}
				$this->options['province'][$target_market] = $province;
			}

			if($action_status != '') {
				$this->action_status = 'error';
				$this->action_message = __('Data have deficiency.','usces');
			} else {
				$this->action_status = 'success';
				$this->action_message = __('options are updated','usces');
			}
//20110331ysk end
		} else {

			if( !isset($this->options['province']) || empty($this->options['province']) ){
//20110331ysk start
				//$this->options['province'] = get_option('usces_pref');
				$this->options['province'][$this->options['system']['base_country']] = $usces_states[$this->options['system']['base_country']];
//20110331ysk end
			}
			$this->action_status = 'none';
			$this->action_message = '';
		}

		if($action_status != 'error') //20120511ysk 0000470
		update_option('usces', $this->options);

		require_once(USCES_PLUGIN_DIR . '/includes/admin_system.php');

	}
	
	/* Settlement Setting Page */
	function admin_settlement_page() {

		$this->action_status = 'none';
		$this->action_message = '';

		$options = get_option('usces');

		if( isset($_POST['usces_option_update']) ) {

			check_admin_referer( 'admin_settlement', 'wc_nonce' );

			$_POST = $this->stripslashes_deep_post($_POST);
			$mes = '';

			switch( $_POST['acting'] ){
				case 'zeus':
					unset( $options['acting_settings']['zeus'] );
					$options['acting_settings']['zeus']['card_url'] = isset($_POST['card_url']) ? $_POST['card_url'] : '';
					$options['acting_settings']['zeus']['card_secureurl'] = isset($_POST['card_secureurl']) ? $_POST['card_secureurl'] : '';
					$options['acting_settings']['zeus']['ipaddrs'] = isset($_POST['ipaddrs']) ? $_POST['ipaddrs'] : '';
					$options['acting_settings']['zeus']['pay_cvs'] = isset($_POST['pay_cvs']) ? $_POST['pay_cvs'] : array();
					$options['acting_settings']['zeus']['card_activate'] = isset($_POST['card_activate']) ? $_POST['card_activate'] : '';
					$options['acting_settings']['zeus']['connection'] = isset($_POST['connection']) ? $_POST['connection'] : 1;
					$options['acting_settings']['zeus']['3dsecur'] = isset($_POST['3dsecur']) ? $_POST['3dsecur'] : 2;
					$options['acting_settings']['zeus']['security'] = isset($_POST['security']) ? $_POST['security'] : 2;
					if( isset($_POST['authkey']) ){
						$options['acting_settings']['zeus']['authkey'] = $_POST['authkey'];
					}
//					if( 1 == $options['acting_settings']['zeus']['security'] ){
//						$options['acting_settings']['zeus']['quickcharge'] = '2';
//					}else{
						$options['acting_settings']['zeus']['quickcharge'] = isset($_POST['quickcharge']) ? $_POST['quickcharge'] : '';
//					}
					$options['acting_settings']['zeus']['batch'] = isset($_POST['batch']) ? $_POST['batch'] : '';
					$options['acting_settings']['zeus']['clientip'] = isset($_POST['clientip']) ? trim($_POST['clientip']) : '';
					$options['acting_settings']['zeus']['howpay'] = isset($_POST['howpay']) ? $_POST['howpay'] : '';
					$options['acting_settings']['zeus']['bank_activate'] = isset($_POST['bank_activate']) ? $_POST['bank_activate'] : '';
					$options['acting_settings']['zeus']['bank_ope'] = isset($_POST['bank_ope']) ? $_POST['bank_ope'] : '';
					$options['acting_settings']['zeus']['clientip_bank'] = isset($_POST['clientip_bank']) ? trim($_POST['clientip_bank']) : '';
					$options['acting_settings']['zeus']['testid_bank'] = isset($_POST['testid_bank']) ? trim($_POST['testid_bank']) : '';
					$options['acting_settings']['zeus']['bank_url'] = isset($_POST['bank_url']) ? $_POST['bank_url'] : '';
					$options['acting_settings']['zeus']['conv_activate'] = isset($_POST['conv_activate']) ? $_POST['conv_activate'] : '';
					$options['acting_settings']['zeus']['conv_ope'] = isset($_POST['conv_ope']) ? $_POST['conv_ope'] : '';
					$options['acting_settings']['zeus']['clientip_conv'] = isset($_POST['clientip_conv']) ? trim($_POST['clientip_conv']) : '';
					$options['acting_settings']['zeus']['testid_conv'] = isset($_POST['testid_conv']) ? trim($_POST['testid_conv']) : '';
					$options['acting_settings']['zeus']['test_type_conv'] = ( (isset($_POST['testid_conv']) && WCUtils::is_blank($_POST['testid_conv'])) || !isset($_POST['test_type']) ) ? 0 : $_POST['test_type'];
					$options['acting_settings']['zeus']['conv_url'] = isset($_POST['conv_url']) ? $_POST['conv_url'] : '';

					if( WCUtils::is_blank($_POST['clientip']) && isset($_POST['card_activate']) && 'on' == $_POST['card_activate'])
						$mes .= '※カード決済IPコードを入力して下さい<br />';
					if( isset($_POST['authkey']) && WCUtils::is_blank($_POST['authkey']) && isset($_POST['security']) && 3 == $_POST['security'])
						$mes .= '※認証キーを入力して下さい<br />';
					if( WCUtils::is_blank($_POST['clientip_bank']) && isset($_POST['bank_activate']) && 'on' == $_POST['bank_activate'] )
						$mes .= '※入金お任せIPコードを入力して下さい<br />';
					if( WCUtils::is_blank($_POST['testid_bank']) && isset($_POST['bank_ope']) && 'test' == $_POST['bank_ope'] )
						$mes .= '※入金お任せテストIDを入力して下さい<br />';
					if( isset($_POST['conv_activate']) && 'on' == $_POST['conv_activate'] ) {
						if( WCUtils::is_blank($_POST['clientip_conv']) )
							$mes .= '※コンビニ決済IPコードを入力して下さい<br />';
						if( WCUtils::is_blank($_POST['testid_conv']) && isset($_POST['conv_ope']) && 'test' == $_POST['conv_ope'] )
							$mes .= '※コンビニ決済テストIDを入力して下さい<br />';
						if( empty($_POST['pay_cvs']) )
							$mes .= '※コンビニ種類を選択して下さい<br />';
					}
					if( isset($_POST['batch']) && 'on' == $_POST['batch'] ) {
						if( isset($_POST['quickcharge']) && 'on' == $_POST['quickcharge'] ) {
						} else {
							$mes .= '※バッチ処理を利用する場合は、クイックチャージを利用するにして下さい<br />';
							$options['acting_settings']['zeus']['quickcharge'] = 'on';
						}
					}
					if( !isset($_POST['card_url']) || empty($_POST['card_url']) || !isset($_POST['ipaddrs']) || empty($_POST['ipaddrs']) || !isset($_POST['bank_url']) || empty($_POST['bank_url']) || !isset($_POST['conv_url']) || empty($_POST['conv_url']) )
						$mes .= '※設定が不正です！<br />';

					if( WCUtils::is_blank($mes) ){
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
							unset($this->payment_structure['acting_zeus_conv']);
						}
						$options['acting_settings']['zeus']['vercheck'] = '115';
						update_option( 'usces', $options );
						if( 'on' != $options['acting_settings']['zeus']['quickcharge'] ) {
							usces_clear_quickcharge( 'zeus_pcid' );
						}
					}else{
						$this->action_status = 'error';
						$this->action_message = __('Data have deficiency.','usces');
						$options['acting_settings']['zeus']['activate'] = 'off';
						unset($this->payment_structure['acting_zeus_card'], $this->payment_structure['acting_zeus_bank'], $this->payment_structure['acting_zeus_conv']);
					}
					ksort($this->payment_structure);
					update_option('usces_payment_structure',$this->payment_structure);
					break;

				case 'remise':
					unset( $options['acting_settings']['remise'] );
					$options['acting_settings']['remise']['plan'] = isset($_POST['plan']) ? $_POST['plan'] : '';
					$options['acting_settings']['remise']['SHOPCO'] = isset($_POST['SHOPCO']) ? $_POST['SHOPCO'] : '';
					$options['acting_settings']['remise']['HOSTID'] = isset($_POST['HOSTID']) ? $_POST['HOSTID'] : '';
					$options['acting_settings']['remise']['card_activate'] = isset($_POST['card_activate']) ? $_POST['card_activate'] : '';
					$options['acting_settings']['remise']['card_jb'] = isset($_POST['card_jb']) ? $_POST['card_jb'] : '';
					$options['acting_settings']['remise']['card_pc_ope'] = isset($_POST['card_pc_ope']) ? $_POST['card_pc_ope'] : '';
					$options['acting_settings']['remise']['payquick'] = isset($_POST['payquick']) ? $_POST['payquick'] : '';
					$options['acting_settings']['remise']['howpay'] = isset($_POST['howpay']) ? $_POST['howpay'] : '';
					$options['acting_settings']['remise']['continuation'] = isset($_POST['continuation']) ? $_POST['continuation'] : '';
					$options['acting_settings']['remise']['conv_activate'] = isset($_POST['conv_activate']) ? $_POST['conv_activate'] : '';
					$options['acting_settings']['remise']['conv_pc_ope'] = isset($_POST['conv_pc_ope']) ? $_POST['conv_pc_ope'] : '';
					$options['acting_settings']['remise']['S_PAYDATE'] = isset($_POST['S_PAYDATE']) ? $_POST['S_PAYDATE'] : '';
					$options['acting_settings']['remise']['send_url_mbl'] = isset($_POST['send_url_mbl']) ? $_POST['send_url_mbl'] : '';
					$options['acting_settings']['remise']['send_url_pc'] = isset($_POST['send_url_pc']) ? $_POST['send_url_pc'] : '';
					$options['acting_settings']['remise']['send_url_cvs_mbl'] = isset($_POST['send_url_cvs_mbl']) ? $_POST['send_url_cvs_mbl'] : '';
					$options['acting_settings']['remise']['send_url_cvs_pc'] = isset($_POST['send_url_cvs_pc']) ? $_POST['send_url_cvs_pc'] : '';
					$options['acting_settings']['remise']['send_url_mbl_test'] = isset($_POST['send_url_mbl_test']) ? $_POST['send_url_mbl_test'] : '';
					$options['acting_settings']['remise']['send_url_pc_test'] = isset($_POST['send_url_pc_test']) ? $_POST['send_url_pc_test'] : '';
					$options['acting_settings']['remise']['send_url_cvs_mbl_test'] = isset($_POST['send_url_cvs_mbl_test']) ? $_POST['send_url_cvs_mbl_test'] : '';
					$options['acting_settings']['remise']['send_url_cvs_pc_test'] = isset($_POST['send_url_cvs_pc_test']) ? $_POST['send_url_cvs_pc_test'] : '';
					$options['acting_settings']['remise']['REMARKS3'] = isset($_POST['REMARKS3']) ? $_POST['REMARKS3'] : '';

					if( isset($_POST['plan_remise']) && WCUtils::is_zero($_POST['plan_remise']) )
						$mes .= '※サービスプランを選択してください<br />';
					if( WCUtils::is_blank($_POST['SHOPCO']) )
						$mes .= '※加盟店コードを入力して下さい<br />';
					if( WCUtils::is_blank($_POST['HOSTID']) )
						$mes .= '※ホスト番号を入力して下さい<br />';
					if( isset($_POST['conv_activate']) && 'on' == $_POST['conv_activate'] && empty($_POST['S_PAYDATE']) )
						$mes .= '※支払期限を入力して下さい<br />';
					if( isset($_POST['card_pc_ope']) && 'public' == $_POST['card_pc_ope'] && empty($_POST['send_url_pc']) )
						$mes .= '※クレジットカード決済の本番URLを入力して下さい<br />';
					if( isset($_POST['conv_pc_ope']) && 'public' == $_POST['conv_pc_ope'] && empty($_POST['send_url_cvs_pc']) )
						$mes .= '※コンビニ・電子マネー決済の本番URLを入力して下さい<br />';
					if( !isset($_POST['REMARKS3']) || empty($_POST['REMARKS3']) )
						$mes .= '※設定が不正です！<br />';

					if( WCUtils::is_blank($mes) ){
						//$this->zaction_status = 'success';
						$this->action_status = 'success';
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
						update_option('usces', $options);
						if( 'on' != $options['acting_settings']['remise']['payquick'] ) {
							usces_clear_quickcharge( 'remise_pcid' );
						}

					}else{
						$this->action_status = 'error';
						$this->action_message = __('Data have deficiency.','usces');
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
					$options['acting_settings']['jpayment']['aid'] = isset($_POST['aid']) ? $_POST['aid'] : '';
					$options['acting_settings']['jpayment']['card_activate'] = isset($_POST['card_activate']) ? $_POST['card_activate'] : '';
					$options['acting_settings']['jpayment']['card_jb'] = isset($_POST['card_jb']) ? $_POST['card_jb'] : '';
					$options['acting_settings']['jpayment']['conv_activate'] = isset($_POST['conv_activate']) ? $_POST['conv_activate'] : '';
					//$options['acting_settings']['jpayment']['webm_activate'] = isset($_POST['webm_activate']) ? $_POST['webm_activate'] : '';
					//$options['acting_settings']['jpayment']['bitc_activate'] = isset($_POST['bitc_activate']) ? $_POST['bitc_activate'] : '';
					//$options['acting_settings']['jpayment']['suica_activate'] = isset($_POST['suica_activate']) ? $_POST['suica_activate'] : '';
					$options['acting_settings']['jpayment']['bank_activate'] = isset($_POST['bank_activate']) ? $_POST['bank_activate'] : '';
					$options['acting_settings']['jpayment']['send_url'] = isset($_POST['send_url']) ? $_POST['send_url'] : '';

					if( WCUtils::is_blank($_POST['aid']) )
						$mes .= '※店舗IDコードを入力して下さい<br />';
					if( isset($_POST['card_activate']) && 'on' == $_POST['card_activate'] && empty($_POST['card_jb']) )
						$mes .= '※ジョブタイプを指定して下さい<br />';

					if( WCUtils::is_blank($mes) ){
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
						update_option('usces', $options);
					}else{
						$this->action_status = 'error';
						$this->action_message = __('Data have deficiency.', 'usces');
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
					$options['acting_settings']['paypal']['ec_activate'] = isset($_POST['ec_activate']) ? $_POST['ec_activate'] : '';
					$options['acting_settings']['paypal']['sandbox'] = isset($_POST['sandbox']) ? $_POST['sandbox'] : '';
					$options['acting_settings']['paypal']['user'] = isset($_POST['user']) ? $_POST['user'] : '';
					$options['acting_settings']['paypal']['pwd'] = isset($_POST['pwd']) ? $_POST['pwd'] : '';
					$options['acting_settings']['paypal']['signature'] = isset($_POST['signature']) ? $_POST['signature'] : '';
//20110412ysk start
					$options['acting_settings']['paypal']['continuation'] = isset($_POST['continuation']) ? $_POST['continuation'] : '';
//20110412ysk end
					$options['acting_settings']['paypal']['logoimg'] = isset($_POST['logoimg']) ? $_POST['logoimg'] : '';
					$options['acting_settings']['paypal']['set_cartbordercolor'] = isset($_POST['set_cartbordercolor']) ? $_POST['set_cartbordercolor'] : 'off';
					$options['acting_settings']['paypal']['cartbordercolor'] = ( 'on' == $options['acting_settings']['paypal']['set_cartbordercolor'] ) ? $_POST['cartbordercolor'] : '';

					if( !isset($_POST['sandbox']) || empty($_POST['sandbox']) )
						$mes .= '※動作環境が不正です<br />';
					if( WCUtils::is_blank($_POST['user']) )
						$mes .= '※APIユーザー名を入力して下さい<br />';
					if( WCUtils::is_blank($_POST['pwd']) )
						$mes .= '※APIパスワードを入力して下さい<br />';
					if( WCUtils::is_blank($_POST['signature']) )
						$mes .= '※署名を入力して下さい<br />';

					if( WCUtils::is_blank($mes) ){
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
						update_option('usces', $options);
					}else{
						$this->action_status = 'error';
						$this->action_message = __('Data have deficiency.', 'usces');
						$options['acting_settings']['paypal']['activate'] = 'off';
						unset($this->payment_structure['acting_paypal_ec']);
					}
					ksort($this->payment_structure);
					update_option('usces_payment_structure', $this->payment_structure);
					break;
//20110208ysk end
//20120413ysk start
				case 'sbps':
					unset( $options['acting_settings']['sbps'] );
					$options['acting_settings']['sbps']['merchant_id'] = isset($_POST['merchant_id']) ? $_POST['merchant_id'] : '';
					$options['acting_settings']['sbps']['service_id'] = isset($_POST['service_id']) ? $_POST['service_id'] : '';
					$options['acting_settings']['sbps']['hash_key'] = isset($_POST['hash_key']) ? $_POST['hash_key'] : '';
					$options['acting_settings']['sbps']['ope'] = isset($_POST['ope']) ? $_POST['ope'] : '';
					//$options['acting_settings']['sbps']['send_url'] = isset($_POST['send_url']) ? $_POST['send_url'] : '';
					$options['acting_settings']['sbps']['send_url_check'] = isset($_POST['send_url_check']) ? $_POST['send_url_check'] : '';
					$options['acting_settings']['sbps']['send_url_test'] = isset($_POST['send_url_test']) ? $_POST['send_url_test'] : '';
					$options['acting_settings']['sbps']['card_activate'] = isset($_POST['card_activate']) ? $_POST['card_activate'] : '';
					$options['acting_settings']['sbps']['3d_secure'] = isset($_POST['3d_secure']) ? $_POST['3d_secure'] : '';
					$options['acting_settings']['sbps']['cust'] = isset($_POST['cust']) ? $_POST['cust'] : '';
					$options['acting_settings']['sbps']['continuation'] = isset($_POST['continuation']) ? $_POST['continuation'] : '';
					$options['acting_settings']['sbps']['conv_activate'] = isset($_POST['conv_activate']) ? $_POST['conv_activate'] : '';
					$options['acting_settings']['sbps']['payeasy_activate'] = isset($_POST['payeasy_activate']) ? $_POST['payeasy_activate'] : '';
					$options['acting_settings']['sbps']['wallet_yahoowallet'] = isset($_POST['wallet_yahoowallet']) ? $_POST['wallet_yahoowallet'] : '';
					$options['acting_settings']['sbps']['wallet_rakuten'] = isset($_POST['wallet_rakuten']) ? $_POST['wallet_rakuten'] : '';
					$options['acting_settings']['sbps']['wallet_paypal'] = isset($_POST['wallet_paypal']) ? $_POST['wallet_paypal'] : '';
					$options['acting_settings']['sbps']['wallet_netmile'] = isset($_POST['wallet_netmile']) ? $_POST['wallet_netmile'] : '';
					$options['acting_settings']['sbps']['wallet_alipay'] = isset($_POST['wallet_alipay']) ? $_POST['wallet_alipay'] : '';
					$options['acting_settings']['sbps']['mobile_docomo'] = isset($_POST['mobile_docomo']) ? $_POST['mobile_docomo'] : '';
					$options['acting_settings']['sbps']['mobile_softbank'] = isset($_POST['mobile_softbank']) ? $_POST['mobile_softbank'] : '';
					$options['acting_settings']['sbps']['mobile_auone'] = isset($_POST['mobile_auone']) ? $_POST['mobile_auone'] : '';
					$options['acting_settings']['sbps']['mobile_mysoftbank'] = isset($_POST['mobile_mysoftbank']) ? $_POST['mobile_mysoftbank'] : '';
					$options['acting_settings']['sbps']['mobile_softbank2'] = isset($_POST['mobile_softbank2']) ? $_POST['mobile_softbank2'] : '';

					if( WCUtils::is_blank($_POST['merchant_id']) )
						$mes .= '※マーチャントIDを入力して下さい<br />';
					if( WCUtils::is_blank($_POST['service_id']) )
						$mes .= '※サービスIDを入力して下さい<br />';
					if( WCUtils::is_blank($_POST['hash_key']) )
						$mes .= '※Hash KEYを入力して下さい<br />';
					//if( isset($_POST['ope']) && 'public' == $_POST['ope'] && empty($_POST['send_url']) )
					//	$mes .= '※本番URLを入力して下さい<br />';

					if( WCUtils::is_blank($mes) ){
						$this->action_status = 'success';
						$this->action_message = __('options are updated','usces');
						$options['acting_settings']['sbps']['activate'] = 'on';
						if( isset($_POST['ope']) && 'public' == $_POST['ope'] ) {
							$options['acting_settings']['sbps']['send_url'] = 'https://fep.sps-system.com/f01/FepBuyInfoReceive.do';
						}
						if( 'on' == $options['acting_settings']['sbps']['card_activate'] ){
							$this->payment_structure['acting_sbps_card'] = 'カード決済（ソフトバンク・ペイメント）';
						}else{
							unset($this->payment_structure['acting_sbps_card']);
						}
						if( 'on' == $options['acting_settings']['sbps']['conv_activate'] ){
							$this->payment_structure['acting_sbps_conv'] = 'コンビニ決済（ソフトバンク・ペイメント）';
						}else{
							unset($this->payment_structure['acting_sbps_conv']);
						}
						if( 'on' == $options['acting_settings']['sbps']['payeasy_activate'] ){
							$this->payment_structure['acting_sbps_payeasy'] = 'ペイジー決済（ソフトバンク・ペイメント）';
						}else{
							unset($this->payment_structure['acting_sbps_payeasy']);
						}
						if( ( 'on' == $options['acting_settings']['sbps']['wallet_yahoowallet'] ) || 
							( 'on' == $options['acting_settings']['sbps']['wallet_rakuten'] ) || 
							( 'on' == $options['acting_settings']['sbps']['wallet_paypal'] ) || 
							( 'on' == $options['acting_settings']['sbps']['wallet_netmile'] ) || 
							( 'on' == $options['acting_settings']['sbps']['wallet_alipay'] ) ) {
							$options['acting_settings']['sbps']['wallet_activate'] = 'on';
						} else {
							$options['acting_settings']['sbps']['wallet_activate'] = 'off';
						}
						if( 'on' == $options['acting_settings']['sbps']['wallet_activate'] ){
							$this->payment_structure['acting_sbps_wallet'] = 'ウォレット決済（ソフトバンク・ペイメント）';
						}else{
							unset($this->payment_structure['acting_sbps_wallet']);
						}
						if( ( 'on' == $options['acting_settings']['sbps']['mobile_docomo'] ) || 
							( 'on' == $options['acting_settings']['sbps']['mobile_softbank'] ) || 
							( 'on' == $options['acting_settings']['sbps']['mobile_auone'] ) || 
							( 'on' == $options['acting_settings']['sbps']['mobile_mysoftbank'] ) || 
							( 'on' == $options['acting_settings']['sbps']['mobile_softbank2'] ) ) {
							$options['acting_settings']['sbps']['mobile_activate'] = 'on';
						} else {
							$options['acting_settings']['sbps']['mobile_activate'] = 'off';
						}
						if( 'on' == $options['acting_settings']['sbps']['mobile_activate'] ){
							$this->payment_structure['acting_sbps_mobile'] = '携帯キャリア決済（ソフトバンク・ペイメント）';
						}else{
							unset($this->payment_structure['acting_sbps_mobile']);
						}
						update_option('usces', $options);
					}else{
						$this->action_status = 'error';
						$this->action_message = __('Data have deficiency.','usces');
						$options['acting_settings']['sbps']['activate'] = 'off';
						unset($this->payment_structure['acting_sbps_card']);
						unset($this->payment_structure['acting_sbps_conv']);
						unset($this->payment_structure['acting_sbps_payeasy']);
						unset($this->payment_structure['acting_sbps_wallet']);
						unset($this->payment_structure['acting_sbps_mobile']);
					}
					ksort($this->payment_structure);
					update_option('usces_payment_structure', $this->payment_structure);
					break;
//20120413ysk end
//20120618ysk start
				case 'telecom':
					unset( $options['acting_settings']['telecom'] );
					$options['acting_settings']['telecom']['clientip'] = isset($_POST['clientip']) ? $_POST['clientip'] : '';
					$options['acting_settings']['telecom']['stype'] = isset($_POST['stype']) ? $_POST['stype'] : '';
					$options['acting_settings']['telecom']['card_activate'] = isset($_POST['card_activate']) ? $_POST['card_activate'] : '';
					$options['acting_settings']['telecom']['oneclick'] = isset($_POST['oneclick']) ? $_POST['oneclick'] : '';
					$options['acting_settings']['telecom']['edy_activate'] = isset($_POST['edy_activate']) ? $_POST['edy_activate'] : '';

					if( WCUtils::is_blank($_POST['clientip']) )
						$mes .= '※クライアントIPを入力して下さい<br />';
					if( WCUtils::is_blank($_POST['stype']) )
						$mes .= '※決済タイプを入力して下さい<br />';

					if( WCUtils::is_blank($mes) ){
						$this->action_status = 'success';
						$this->action_message = __('options are updated','usces');
						$options['acting_settings']['telecom']['activate'] = 'on';
						if( 'on' == $options['acting_settings']['telecom']['card_activate'] ){
							if( 'E' == $options['acting_settings']['telecom']['stype'][0] ) {
								$options['acting_settings']['telecom']['send_url'] = "https://www.credit-cgiserver.com/inetcredit/secure/order.pl";
							} else {
								$options['acting_settings']['telecom']['send_url'] = "https://secure.telecomcredit.co.jp/inetcredit/secure/order.pl";
							}
							if( 'on' == $options['acting_settings']['telecom']['oneclick'] ){
								$options['acting_settings']['telecom']['oneclick_send_url'] = "https://secure.telecomcredit.co.jp/inetcredit/secure/one-click-order.pl";
							}
							$this->payment_structure['acting_telecom_card'] = 'カード決済（テレコムクレジット）';
						}else{
							unset($this->payment_structure['acting_telecom_card']);
						}
						if( 'on' == $options['acting_settings']['telecom']['edy_activate'] ){
							$options['acting_settings']['telecom']['send_url_edy'] = "https://secure.telecomcredit.co.jp/payment/edy/order.pl";
							$this->payment_structure['acting_telecom_edy'] = 'Edy決済（テレコムクレジット）';
						}else{
							unset($this->payment_structure['acting_telecom_edy']);
						}
						update_option('usces', $options);
					}else{
						$this->action_status = 'error';
						$this->action_message = __('Data have deficiency.','usces');
						$options['acting_settings']['telecom']['activate'] = 'off';
						unset($this->payment_structure['acting_telecom_card']);
						unset($this->payment_structure['acting_telecom_edy']);
					}
					ksort($this->payment_structure);
					update_option('usces_payment_structure', $this->payment_structure);
					break;
//20120618ysk end
//20121206ysk start
				case 'digitalcheck':
					unset( $options['acting_settings']['digitalcheck'] );
					$options['acting_settings']['digitalcheck']['card_activate'] = isset($_POST['card_activate']) ? $_POST['card_activate'] : '';
					$options['acting_settings']['digitalcheck']['card_ip'] = isset($_POST['card_ip']) ? $_POST['card_ip'] : '';
					$options['acting_settings']['digitalcheck']['card_pass'] = isset($_POST['card_pass']) ? $_POST['card_pass'] : '';
					$options['acting_settings']['digitalcheck']['card_kakutei'] = isset($_POST['card_kakutei']) ? $_POST['card_kakutei'] : '';
					$options['acting_settings']['digitalcheck']['card_user_id'] = isset($_POST['card_user_id']) ? $_POST['card_user_id'] : '';
					$options['acting_settings']['digitalcheck']['conv_activate'] = isset($_POST['conv_activate']) ? $_POST['conv_activate'] : '';
					$options['acting_settings']['digitalcheck']['conv_ip'] = isset($_POST['conv_ip']) ? $_POST['conv_ip'] : '';
					$options['acting_settings']['digitalcheck']['conv_store'] = isset($_POST['conv_store']) ? $_POST['conv_store'] : array();
					$options['acting_settings']['digitalcheck']['conv_kigen'] = isset($_POST['conv_kigen']) ? $_POST['conv_kigen'] : '14';

					if( 'on' == $options['acting_settings']['digitalcheck']['card_activate'] ) {
						if( '' == trim($_POST['card_ip']) )
							$mes .= '※加盟店コードを入力して下さい<br />';
						if( 'on' == $options['acting_settings']['digitalcheck']['card_user_id'] ) {
							if( '' == trim($_POST['card_pass']) )
								$mes .= '※加盟店パスワードを入力して下さい<br />';
						}
					}
					if( 'on' == $options['acting_settings']['digitalcheck']['conv_activate'] ) {
						if( '' == trim($_POST['conv_ip']) )
							$mes .= '※加盟店コードを入力して下さい<br />';
						if( empty($_POST['conv_store']) )
							$mes .= '※利用コンビニを選択して下さい<br />';
					}

					if( '' == $mes ){
						$this->action_status = 'success';
						$this->action_message = __('options are updated','usces');
						$options['acting_settings']['digitalcheck']['activate'] = 'on';
						if( 'on' == $options['acting_settings']['digitalcheck']['card_activate'] ){
							$options['acting_settings']['digitalcheck']['send_url_card'] = "https://www.paydesign.jp/settle/settle3/bp3.dll";
							if( 'on' == $options['acting_settings']['digitalcheck']['card_user_id'] ) {
								$options['acting_settings']['digitalcheck']['send_url_user_id'] = "https://www.paydesign.jp/settle/settlex/credit2.dll";
							} else {
								$options['acting_settings']['digitalcheck']['send_url_user_id'] = "";
							}
							$this->payment_structure['acting_digitalcheck_card'] = 'カード決済（ペイデザイン）';
						}else{
							unset($this->payment_structure['acting_digitalcheck_card']);
						}
						if( 'on' == $options['acting_settings']['digitalcheck']['conv_activate'] ){
							$options['acting_settings']['digitalcheck']['send_url_conv'] = "https://www.paydesign.jp/settle/settle3/bp3.dll";
							$this->payment_structure['acting_digitalcheck_conv'] = 'コンビニ決済（ペイデザイン）';
						}else{
							unset($this->payment_structure['acting_digitalcheck_conv']);
						}
						update_option('usces', $options);
						if( 'on' != $options['acting_settings']['digitalcheck']['card_user_id'] ) {
							usces_clear_quickcharge( 'digitalcheck_ip_user_id' );
						}
					}else{
						$this->action_status = 'error';
						$this->action_message = __('Data have deficiency.','usces');
						$options['acting_settings']['digitalcheck']['activate'] = 'off';
						unset($this->payment_structure['acting_digitalcheck_card']);
						unset($this->payment_structure['acting_digitalcheck_conv']);
					}
					ksort($this->payment_structure);
					update_option('usces_payment_structure', $this->payment_structure);
					break;
//20121206ysk end
//20130225ysk start
				case 'mizuho':
					unset( $options['acting_settings']['mizuho'] );
					$options['acting_settings']['mizuho']['shopid'] = isset($_POST['shopid']) ? $_POST['shopid'] : '';
					$options['acting_settings']['mizuho']['cshopid'] = isset($_POST['cshopid']) ? $_POST['cshopid'] : '';
					$options['acting_settings']['mizuho']['hash_pass'] = isset($_POST['hash_pass']) ? $_POST['hash_pass'] : '';
					$options['acting_settings']['mizuho']['ope'] = isset($_POST['ope']) ? $_POST['ope'] : '';
					$options['acting_settings']['mizuho']['send_url'] = isset($_POST['send_url']) ? $_POST['send_url'] : '';
					$options['acting_settings']['mizuho']['send_url_mbl'] = isset($_POST['send_url_mbl']) ? $_POST['send_url_mbl'] : '';
					$options['acting_settings']['mizuho']['card_activate'] = isset($_POST['card_activate']) ? $_POST['card_activate'] : '';
					$options['acting_settings']['mizuho']['conv1_activate'] = isset($_POST['conv1_activate']) ? $_POST['conv1_activate'] : '';
					$options['acting_settings']['mizuho']['conv2_activate'] = isset($_POST['conv2_activate']) ? $_POST['conv2_activate'] : '';

					//if( 'on' == $options['acting_settings']['mizuho']['card_activate'] or 'on' == $options['acting_settings']['mizuho']['conv_activate'] ) {
						if( '' == trim($_POST['shopid']) )
							$mes .= '※加盟店コードを入力して下さい<br />';
						if( '' == trim($_POST['cshopid']) )
							$mes .= '※加盟店サブコードを入力して下さい<br />';
						if( '' == trim($_POST['hash_pass']) )
							$mes .= '※ハッシュ用パスワードを入力して下さい<br />';
					//}
					if( isset($_POST['ope']) && 'public' == $_POST['ope'] && empty($_POST['send_url']) )
						$mes .= '※本番URLを入力して下さい<br />';
					if( defined('WCEX_MOBILE') and isset($_POST['ope']) && 'public' == $_POST['ope'] && empty($_POST['send_url_mbl']) )
						$mes .= '※本番URL(携帯)を入力して下さい<br />';

					if( '' == $mes ) {
						$this->action_status = 'success';
						$this->action_message = __('options are updated','usces');
						$options['acting_settings']['mizuho']['activate'] = 'on';
						$options['acting_settings']['mizuho']['send_url_test'] = "https://210.161.141.207/mltbank/MBWebFrontPayment";
						if( defined('WCEX_MOBILE') ) $options['acting_settings']['mizuho']['send_url_mbl_test'] = "https://210.161.141.207/mltbank/iMBWebFrontPayment";
						if( 'on' == $options['acting_settings']['mizuho']['card_activate'] ) {
							$this->payment_structure['acting_mizuho_card'] = 'カード決済（みずほファクター）';
						} else {
							unset($this->payment_structure['acting_mizuho_card']);
						}
						if( 'on' == $options['acting_settings']['mizuho']['conv1_activate'] ) {
							$this->payment_structure['acting_mizuho_conv1'] = 'コンビニ・ウェルネット決済（みずほファクター）';
						} else {
							unset($this->payment_structure['acting_mizuho_conv1']);
						}
						if( 'on' == $options['acting_settings']['mizuho']['conv2_activate'] ) {
							$this->payment_structure['acting_mizuho_conv2'] = 'コンビニ・セブンイレブン決済（みずほファクター）';
						} else {
							unset($this->payment_structure['acting_mizuho_conv2']);
						}
						update_option('usces', $options);
					} else {
						$this->action_status = 'error';
						$this->action_message = __('Data have deficiency.','usces');
						$options['acting_settings']['mizuho']['activate'] = 'off';
						unset($this->payment_structure['acting_mizuho_card']);
						unset($this->payment_structure['acting_mizuho_conv1']);
						unset($this->payment_structure['acting_mizuho_conv2']);
					}
					ksort($this->payment_structure);
					update_option('usces_payment_structure', $this->payment_structure);
					break;
//20130225ysk end
//20131220ysk start
				case 'anotherlane':
					unset( $options['acting_settings']['anotherlane'] );
					$options['acting_settings']['anotherlane']['siteid'] = isset($_POST['siteid']) ? $_POST['siteid'] : '';
					$options['acting_settings']['anotherlane']['sitepass'] = isset($_POST['sitepass']) ? $_POST['sitepass'] : '';
					$options['acting_settings']['anotherlane']['quickcharge'] = isset($_POST['quickcharge']) ? $_POST['quickcharge'] : '';
					$options['acting_settings']['anotherlane']['card_activate'] = isset($_POST['card_activate']) ? $_POST['card_activate'] : '';

					if( WCUtils::is_blank($_POST['siteid']) )
						$mes .= '※サイトIDを入力して下さい<br />';
					if( WCUtils::is_blank($_POST['sitepass']) )
						$mes .= '※サイトパスワードを入力して下さい<br />';

					if( WCUtils::is_blank($mes) ) {
						$this->action_status = 'success';
						$this->action_message = __('options are updated','usces');
						$options['acting_settings']['anotherlane']['activate'] = 'on';
						if( 'on' == $options['acting_settings']['anotherlane']['card_activate'] ) {
							$options['acting_settings']['anotherlane']['send_url'] = "https://credit.alij.ne.jp/service/credit/input.html";
							$this->payment_structure['acting_anotherlane_card'] = 'カード決済（アナザーレーン）';
						} else {
							unset( $this->payment_structure['acting_anotherlane_card'] );
						}
						update_option( 'usces', $options );
					} else {
						$this->action_status = 'error';
						$this->action_message = __('Data have deficiency.','usces');
						$options['acting_settings']['anotherlane']['activate'] = 'off';
						unset( $this->payment_structure['acting_anotherlane_card'] );
					}
					ksort( $this->payment_structure );
					update_option( 'usces_payment_structure', $this->payment_structure );
					break;
//20131220ysk end
//20140206ysk start
				case 'veritrans':
					unset( $options['acting_settings']['veritrans'] );
					$options['acting_settings']['veritrans']['merchant_id'] = isset($_POST['merchant_id']) ? $_POST['merchant_id'] : '';
					$options['acting_settings']['veritrans']['merchanthash'] = isset($_POST['merchanthash']) ? $_POST['merchanthash'] : '';
					$options['acting_settings']['veritrans']['ope'] = isset($_POST['ope']) ? $_POST['ope'] : '';
					$options['acting_settings']['veritrans']['card_activate'] = isset($_POST['card_activate']) ? $_POST['card_activate'] : '';
					$options['acting_settings']['veritrans']['card_capture_flag'] = isset($_POST['card_capture_flag']) ? $_POST['card_capture_flag'] : '';
					$options['acting_settings']['veritrans']['conv_activate'] = isset($_POST['conv_activate']) ? $_POST['conv_activate'] : '';

					if( WCUtils::is_blank($options['acting_settings']['veritrans']['merchant_id']) )
						$mes .= '※マーチャントIDを入力して下さい<br />';
					if( WCUtils::is_blank($options['acting_settings']['veritrans']['merchanthash']) )
						$mes .= '※マーチャントハッシュキーを入力して下さい<br />';
					if( WCUtils::is_blank($options['acting_settings']['veritrans']['ope']) )
						$mes .= '※稼働環境を選択して下さい<br />';
					if( 'on' == $options['acting_settings']['veritrans']['card_activate'] ) {
						if( WCUtils::is_blank($options['acting_settings']['veritrans']['card_capture_flag']) )
							$mes .= '※カード売上フラグを選択して下さい<br />';
					}

					if( WCUtils::is_blank($mes) ) {
						$this->action_status = 'success';
						$this->action_message = __('options are updated','usces');
						$options['acting_settings']['veritrans']['activate'] = 'on';
						$options['acting_settings']['veritrans']['regist_url'] = "https://air.veritrans.co.jp/web/commodityRegist.action";
						$options['acting_settings']['veritrans']['payment_url'] = "https://air.veritrans.co.jp/web/paymentStart.action";
						if( 'on' == $options['acting_settings']['veritrans']['card_activate'] ) {
							$this->payment_structure['acting_veritrans_card'] = 'カード決済（ベリトランス）';
						} else {
							unset($this->payment_structure['acting_veritrans_card']);
						}
						if( 'on' == $options['acting_settings']['veritrans']['conv_activate'] ) {
							$this->payment_structure['acting_veritrans_conv'] = 'コンビニ決済（ベリトランス）';
						} else {
							unset($this->payment_structure['acting_veritrans_conv']);
						}
						update_option('usces', $options);
					} else {
						$this->action_status = 'error';
						$this->action_message = __('Data have deficiency.','usces');
						$options['acting_settings']['veritrans']['activate'] = 'off';
						unset($this->payment_structure['acting_veritrans_card']);
						unset($this->payment_structure['acting_veritrans_conv']);
					}
					ksort($this->payment_structure);
					update_option('usces_payment_structure', $this->payment_structure);
					break;
//20140206ysk end
			}

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

	function usces_session_start() {
		$options = get_option('usces');
		if( !isset($options['usces_key']) || empty($options['usces_key']) ){
			$options['usces_key'] =  uniqid('uk');
			update_option('usces', $options);
		}

		if(defined( 'USCES_KEY' )){
			if( is_admin() || preg_match('/\/wp-login\.php/', $_SERVER['REQUEST_URI']) ){
				session_name( 'adm'.USCES_KEY );
			}else{
				session_name( USCES_KEY );
			}
		}else{
			if( is_admin() || preg_match('/\/wp-login\.php/', $_SERVER['REQUEST_URI']) ){
				session_name( 'adm'.$options['usces_key'] );
			}else{
				session_name( $options['usces_key'] );
			}
		}
		if(isset($_GET['uscesid']) && !WCUtils::is_blank($_GET['uscesid'])) {
			$sessid = $_GET['uscesid'];
			$sessid = $this->uscesdc($sessid);
			session_id($sessid);
		}
		
		@session_start();
		
//20111222ysk start 0000367
		//if ( !isset($_SESSION['usces_member']) ){
		if ( !isset($_SESSION['usces_member']) || $options['membersystem_state'] != 'activate' ){
//20111222ysk end
			$_SESSION['usces_member'] = array();
		}

		if(!isset($_SESSION['usces_checked_business_days']))
			$this->update_business_days();
	}
	
	function usces_cookie() {
		if(is_admin()) return;

		$actionflag = false;
		$sess = NULL;
		$addr = NULL;
		$rckid = NULL;
		$none = NULL;
		$cookie = $this->get_cookie();
		
		if( isset($_GET['uscesid']) && !WCUtils::is_blank($_GET['uscesid']) ){
			$sessid = base64_decode(urldecode($_GET['uscesid']));
			list($sess, $addr, $rckid, $none) = explode('_', $sessid, 4);
		}
		if('acting' == $addr) return;

		if( apply_filters( 'usces_filter_cookie', false) ) return;
		
		//There is a need to consider.
		return;

		if( $this->use_ssl && ($this->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $this->is_inquiry_page($_SERVER['REQUEST_URI']))){
			
			$refer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL;
			$sslid = isset($cookie['sslid']) ? $cookie['sslid'] : NULL;
			$option = get_option('usces');
			$parsed = parse_url(get_option('home'));
			$home = $parsed['host'] . (isset($parsed['path']) ? $parsed['path'] : '');
			$parsed = parse_url($option['ssl_url']);
			$sslhome = $parsed['host'] . (isset($parsed['path']) ? $parsed['path'] : '');

//usces_log('refer : '.$refer, 'acting_transaction.log');
//	usces_log('sslid : '.$sslid, 'acting_transaction.log');
//	usces_log('rckid : '.$rckid, 'acting_transaction.log');
//	usces_log('usces_cookieid : '.$_SESSION['usces_cookieid'], 'acting_transaction.log');
//	usces_log('request : '.print_r($_SERVER['REQUEST_URI'],true), 'acting_transaction.log');

			if( empty($refer) || (false === strpos($refer, $home) && false === strpos($refer, $sslhome)) ){
				if( !empty($sslid) && !empty($rckid) && $sslid === $rckid ){
					$actionflag = true;
				}else{
					$actionflag = false;
				}
			}else{
				if( !empty($sslid) && $sslid !== $rckid ){
					$actionflag = false;
				}else{
					$actionflag = true;
				}
			}
			
				
			if( $actionflag ){
				$values = array(
							'id' => $rckid,
							'sslid' => $rckid,
							'name' => '',
							'rme' => ''
							);
				if( 'acting' !== $rckid ){
					$this->set_cookie($values);
				}
			}else{
				if( 'acting' !== $rckid ){
					unset($_SESSION['usces_member'], $_SESSION['usces_cart'], $_SESSION['usces_entry'] );
					wp_redirect( 'http://'.$home );
				}
			}
		}else{
			if( !isset($cookie['id']) || WCUtils::is_blank($cookie['id']) ) {
				$values = array(
							'id' => md5(uniqid(rand(), true)),
							'name' => '',
							'rme' => ''
							);
				$this->set_cookie($values);
				$_SESSION['usces_cookieid'] = $values['id'];
			} else {
				if( !isset($_SESSION['usces_cookieid']) || $_SESSION['usces_cookieid'] != $cookie['id'])
					$_SESSION['usces_cookieid'] = $cookie['id'];
			}
			
			$actionflag = true;
		}
		
	}

	function set_cookie($values){
		$value = serialize($values);
		$timeout = time()+7*86400;
		$domain = $_SERVER['SERVER_NAME'];
		$res = setcookie('usces_cookie', $value, $timeout, USCES_COOKIEPATH, $domain);
	}
	
	function get_cookie() {
		$values = isset($_COOKIE['usces_cookie']) ? unserialize(stripslashes($_COOKIE['usces_cookie'])) : NULL;
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
		$sessid = session_id();
		$sessid = $this->uscescv($sessid, $flag);
		return $sessid;
	}
	
	function shop_head() {
		global $post;
		$this->item = $post;
		$no_cart_css = isset($this->options['system']['no_cart_css']) ? $this->options['system']['no_cart_css'] : 0;
		
		if( !is_admin() ){
			$default_css = USCES_FRONT_PLUGIN_URL . '/css/usces_default.css';
			echo '<link href="' . $default_css . '" rel="stylesheet" type="text/css" />';
		}
		if( $this->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $this->is_inquiry_page($_SERVER['REQUEST_URI']) ){
			$css_url = USCES_FRONT_PLUGIN_URL . '/css/usces_cart.css';
		}else{
			$css_url = USCES_WP_CONTENT_URL . '/plugins/' . USCES_PLUGIN_FOLDER . '/css/usces_cart.css';
		}
		if( $this->is_cart_or_member_page($_SERVER['REQUEST_URI']) ){
			echo "	<meta name='robots' content='noindex,nofollow' />\n";
			wp_print_scripts( array( 'sack' )); 
		}
		if( !$no_cart_css ){
			echo '<link href="' . $css_url . '" rel="stylesheet" type="text/css" />';
		}
		if( file_exists(get_stylesheet_directory() . '/usces_cart.css') ){
			echo '<link href="' . get_stylesheet_directory_uri() . '/usces_cart.css" rel="stylesheet" type="text/css" />';
		}
	}
	
	function shop_foot() {
		global $current_user;
		$item = $this->item;
		if( empty($item) ){
			$item->ID = 0;
			$item->post_mime_type = '';
		}
		get_currentuserinfo();
		if( $this->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $this->is_inquiry_page($_SERVER['REQUEST_URI']) ){
			$javascript_url = USCES_FRONT_PLUGIN_URL . '/js/usces_cart.js';
		}else{
			$javascript_url = USCES_WP_CONTENT_URL . '/plugins/' . USCES_PLUGIN_FOLDER . '/js/usces_cart.js';
		}
//		$this->member_name = ( is_user_logged_in() ) ? esc_js(get_user_meta($current_user->ID,'first_name').get_user_meta($current_user->ID,'last_name')) : '';
		$this->previous_url = isset($_SESSION['usces_previous_url']) ? $_SESSION['usces_previous_url'] : get_home_url();

//		usces_log('post_type : '.$item->post_mime_type, 'test.log');
//		usces_log('is_single : '.(is_single() ? 'true' : 'false'), 'test.log');
		
		if( $this->use_js && empty($this->item) && !is_admin() ) : 
		?>
		<script type='text/javascript'>
		/* <![CDATA[ */
			uscesL10n = {
				<?php echo apply_filters('usces_filter_uscesL10n', NULL, $item->ID); ?>
				'ajaxurl': "<?php echo $this->ssl_admin_ajax_url(); ?>"
			}
		/* ]]> */
		</script>
		<?php elseif( $this->use_js && !empty($this->item) ) : 

			$ioptkeys = $this->get_itemOptionKey( $item->ID );
			$mes_opts_str = "";
			$key_opts_str = "";
			$opt_means = "";
			$opt_esse = "";
			if($ioptkeys){
				foreach($ioptkeys as $key => $value){
					$optValues = $this->get_itemOptions( $value, $item->ID );
					if($optValues['means'] < 2){
						$mes_opts_str .= "'" . sprintf(__("Chose the %s", 'usces'), esc_js($value)) . "',";
					}else{
						$mes_opts_str .= "'" . sprintf(__("Input the %s", 'usces'), esc_js($value)) . "',";
					}
					$key_opts_str .= "'" . urlencode(esc_js($value)) . "',";
					$opt_means .= "'" . esc_js($optValues['means']) . "',";
					$opt_esse .= "'" . esc_js($optValues['essential']) . "',";
				}
				$mes_opts_str = rtrim($mes_opts_str, ',');
				$key_opts_str = rtrim($key_opts_str, ',');
				$opt_means = rtrim($opt_means, ',');
				$opt_esse = rtrim($opt_esse, ',');
			}
			//$itemRestriction = get_post_custom_values('_itemRestriction', $item->ID);
			$itemRestriction = get_post_meta($item->ID, '_itemRestriction', true);
		
		?>
		<script type='text/javascript'>
		/* <![CDATA[ */
			uscesL10n = {
				<?php echo apply_filters('usces_filter_uscesL10n', NULL, $item->ID); ?>
				'ajaxurl': "<?php echo $this->ssl_admin_ajax_url(); ?>",
				'post_id': "<?php echo $item->ID; ?>",
				'cart_number': "<?php echo get_option('usces_cart_number'); ?>",
				'is_cart_row': <?php echo ( (0 < $this->cart->num_row()) ? 'true' : 'false'); ?>,
				'opt_esse': new Array( <?php echo $opt_esse; ?> ),
				'opt_means': new Array( <?php echo $opt_means; ?> ),
				'mes_opts': new Array( <?php echo $mes_opts_str; ?> ),
				'key_opts': new Array( <?php echo $key_opts_str; ?> ), 
				'previous_url': "<?php echo $this->previous_url; ?>", 
				'itemRestriction': "<?php echo $itemRestriction; ?>"
			}
		/* ]]> */
		</script>
		<script type='text/javascript' src='<?php echo $javascript_url; ?>'></script>
		<?php endif; ?>
		<?php ob_start(); ?>
		<?php if( $this->use_js && (is_page(USCES_CART_NUMBER) || $this->is_cart_page($_SERVER['REQUEST_URI']) || (is_singular() && 'item' == $item->post_mime_type)) ) : ?>
		<script type='text/javascript'>
		(function($) {
		uscesCart = {
			intoCart : function (post_id, sku) {
				
				var zaikonum = document.getElementById("zaikonum["+post_id+"]["+sku+"]").value;
				var zaiko = document.getElementById("zaiko["+post_id+"]["+sku+"]").value;
				if( <?php echo apply_filters( 'usces_intoCart_zaiko_check_js', "(zaiko != '0' && zaiko != '1') ||  parseInt(zaikonum) == 0" ); ?> ){
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
					if( uscesL10n.opt_esse[i] == '1' && null != skuob ){
						
						if( uscesL10n.opt_means[i] < 2 && skuob.value == '#NONE#' ){
							mes += uscesL10n.mes_opts[i]+"\n";
						}else if( uscesL10n.opt_means[i] >= 2 && skuob.value == '' ){
							mes += uscesL10n.mes_opts[i]+"\n";
						}
					}
				}
				
				<?php apply_filters( 'usces_filter_inCart_js_check', $item->ID ); //Unavailable ?>
				<?php do_action( 'usces_action_inCart_js_check', $item->ID ); ?>
				
				if( mes != '' ){
					alert( mes );
					return false;
				}else{
					<?php echo apply_filters('usces_filter_js_intoCart', "return true;\n", $item->ID, NULL); ?>
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
				
				<?php apply_filters( 'usces_filter_upCart_js_check', $item->ID ); //Unavailable ?>
				<?php do_action( 'usces_action_upCart_js_check', $item->ID ); ?>
				
				if( mes != '' ){
					alert( mes );
					return false;
				}else{
					<?php echo apply_filters('usces_filter_js_upCart', "return true;\n", $item->ID, NULL); ?>
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
			},
			purchase : 0
		};
		$("#country").change(function () {
			var country = $("#country option:selected").val();
			$("#newcharging_type option:selected").val()
			uscesCart.changeStates( country ); 
		});
		$("#purchase_form").submit(function () {
			if( 0 == uscesCart.purchase ){
				uscesCart.purchase = 1;
				return true;
			}else{ 
				$("#purchase_button").attr("disabled", "disabled");
				$("#back_button").attr("disabled", "disabled");
				return false;
			}
		});
			
		})(jQuery);
		</script>
		<?php endif; ?>
<?php
		usces_states_form_js();
		$js = apply_filters( 'usces_filter_shop_foot_js', ob_get_contents() );
		ob_end_clean();
		echo $js;

	}
	
	function admin_head() {
		global $wp_version;
		$payments_str = '';
		$payments = usces_get_system_option( 'usces_payment_method', 'sort' );
		foreach ( (array)$payments as $id => $array ) {
			$payments_str .= "'" . esc_js($array['name']) . "': '" . esc_js($array['settlement']) . "', ";
		}
		$payments_str .= "'" . __('Transfer (prepayment)', 'usces') . "': 'transferAdvance', ";
		$payments_str .= "'" . __('Transfer (postpay)', 'usces') . "': 'transferDeferred', ";
		$payments_str .= "'" . __('COD', 'usces') . "': 'COD', ";
		$payments_str = rtrim($payments_str, ', ');
		$wcex_str = '';
		$wcex = usces_get_wcex();
		foreach ( (array)$wcex as $key => $values ) {
			$wcex_str .= "'" . esc_js($key) . "-" . esc_js($values['version']) . "', ";
		}
		$wcex_str = rtrim($wcex_str, ', ');
		if ( version_compare($wp_version, '3.4', '>=') ){
			$theme_ob = wp_get_theme();
			$theme['Name'] = esc_js($theme_ob->get('Name'));
			$theme['Version'] = esc_js($theme_ob->get('Version'));
		}else{
			$theme = get_theme_data( get_stylesheet_directory().'/style.css' );//20120618ysk
		}
?>
		
		<link href="<?php echo USCES_PLUGIN_URL; ?>/css/admin_style.css" rel="stylesheet" type="text/css" media="all" />
		<script type='text/javascript'>
		/* <![CDATA[ */
			uscesL10n = {
				<?php echo apply_filters('usces_filter_admin_uscesL10n', NULL ); ?>
				'requestFile': "<?php echo site_url(); ?>/wp-admin/admin-ajax.php",
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
			<link rel='stylesheet' href='<?php echo site_url(); ?>/wp-includes/js/thickbox/thickbox.css' type='text/css' media='all' />
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
							'cod_type_fix': "<?php echo esc_js(__('Fixation C.O.D.', 'usces')); ?>",
							'cod_type_change': "<?php echo esc_js(__('Variable C.O.D.', 'usces')); ?>",
							'cod_unit': "<?php echo esc_js(__('dollars', 'usces')); ?>",
							'cod_failure': "<?php echo esc_js(__('failure in update', 'usces')); ?>",
							'cod_updated': "<?php echo esc_js(__('options are updated', 'usces')); ?>",
							'cod_limit': "<?php echo esc_js(__('A value of the amount of upper limit is dirty.', 'usces')); ?>"
						};
/* ]]> */
					</script>
<?php
					break;
				case 'usces_itemnew':
				case 'usces_itemedit':
?>
					<style type="text/css">
					<!--
					#usces_mess {
						color: #FF0000;
						font-weight: bold;
					}
					-->
					</style>
<?php
					break;
			}
		}
?>
<?php
		if( is_admin() && ( (isset($_GET['order_action']) && 'newpost' == $_GET['order_action']) 
							|| (isset($_GET['page']) && 'usces_ordernew' == $_GET['page']) 
							|| (isset($_GET['order_action']) && 'edit' == $_GET['order_action']) 
							|| (isset($_GET['order_action']) && 'editpost' == $_GET['order_action']) 
							|| (isset($_GET['member_action']) && 'edit' == $_GET['member_action']) 
							|| (isset($_GET['member_action']) && 'editpost' == $_GET['member_action'])) ) :
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
		if( undefined != $("#customer_pref").get(0) && undefined != $("#delivery_country").get(0) ) {
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
		}
<?php
		}else if( 'member' == $admin_page ){
?>
		if( undefined != $("#member_pref").get(0) ) {
			var customerstate = $("#member_pref").get(0).selectedIndex;
			var customercountry = $("#member_country").val();
			var deliverystate = '';
			var deliverycountry = '';
			
			$("#member_country").change(function () {
				var country = $("#member_country option:selected").val();
				uscesForm.changeStates( country, 'member' ); 
			});
		}
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

		if( !is_admin() ){
			$this->usces_cookie();
		}else{
			$this->user_level = usces_get_admin_user_level();
		}
		$this->make_url();


		do_action('usces_main');
		$this->update_table();
	
		
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
			$filename = usces_item_uploadcsv();
			$url = USCES_ADMIN_URL . '?page=usces_itemedit&usces_status=none&usces_message=&action=upload_register&regfile='.$filename;
			wp_redirect($url);
			exit;
		//member_up
//		}else if( isset($_REQUEST['page']) && $_REQUEST['page'] == 'usces_memberlist' && isset($_REQUEST['action']) && $_REQUEST['action'] == 'membercsv'){
//			$filename = usces_upload_member_list();
//			$url = USCES_ADMIN_URL . '?page=usces_memberlist&usces_status=none&usces_message=&member_action=upload_register&regfile='.$filename;
//			wp_redirect($url);
//			exit;
		}
//20110208ysk start
		if( isset($this->options['acting_settings']['paypal']) and 'on' == $this->options['acting_settings']['paypal']['ec_activate'] ) {
			require_once( USCES_PLUGIN_DIR.'/classes/paymentPaypal.class.php' );
			$this->paypal = new usces_paypal();
		}
//20110208ysk end
		
		$this->ad_controller();
		//$this->controller();
		

		
		if( isset($_GET['page']) && $_GET['page'] == 'usces_itemnew'){
			$itemnew = 'new';
		}else{
			$itemnew = '';
		}
		
		wp_enqueue_script('jquery');
		
		if( is_admin() && isset($_REQUEST['page']) && ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') || $itemnew == 'new' || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'editpost'))) {
		
			if(isset($_REQUEST['action']) && $_REQUEST['action'] != 'editpost' && $itemnew == 'new'){
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
								if( !isset($current_screen) ){
									$current_screen = new stdClass();
								}
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
			//wp_enqueue_script('editor');
			add_thickbox();
			wp_enqueue_script('media-upload');
			wp_enqueue_script('word-count');
			wp_enqueue_script( 'admin-comments' );
		
			if ( version_compare($wp_version, '3.3', '<') )
				add_action( 'admin_print_footer_scripts', 'wp_tiny_mce', 25 );
			wp_enqueue_script('quicktags');

		}

		
		if( is_admin() && isset($_REQUEST['page']) ){
		
			wp_enqueue_script('jquery-color');
			
			switch( $_REQUEST['page'] ){
			
				case 'usces_initial':
					$js = USCES_FRONT_PLUGIN_URL.'/js/usces_initial.js';
					wp_enqueue_script('usces_initial.js', $js, array('jquery-ui-dialog', 'jquery-ui-sortable'));
					//wp_enqueue_script('jquery-ui-sortable');
					break;
				case 'usces_settlement':
					wp_enqueue_script('jquery-ui-tabs', array('jquery-ui-core'));
					$jquery_cookieUrl = USCES_FRONT_PLUGIN_URL.'/js/jquery/jquery.cookie.js';
					wp_enqueue_script( 'jquery-cookie', $jquery_cookieUrl, array('jquery') );
					$jquery_colorUrl = USCES_FRONT_PLUGIN_URL.'/js/jquery/color/jscolor.js';
					wp_enqueue_script( 'jquery-jscolor', $jquery_colorUrl, array('jquery-color') );
					break;
				case 'usces_cart':
					wp_enqueue_script('jquery-ui-tabs', array('jquery-ui-core'));
					$jquery_cookieUrl = USCES_FRONT_PLUGIN_URL.'/js/jquery/jquery.cookie.js';
					wp_enqueue_script( 'jquery-cookie', $jquery_cookieUrl, array('jquery') );
					break;
				case 'usces_member':
					wp_enqueue_script('jquery-ui-tabs', array('jquery-ui-core'));
					$jquery_cookieUrl = USCES_FRONT_PLUGIN_URL.'/js/jquery/jquery.cookie.js';
					wp_enqueue_script( 'jquery-cookie', $jquery_cookieUrl, array('jquery') );
					break;
				case 'usces_orderlist':
				case 'usces_ordernew':
					wp_enqueue_script('jquery-ui-dialog');
					break;
				case 'usces_memberlist':
					wp_enqueue_script('jquery-ui-dialog');
					break;
				case 'usces_itemnew':
					wp_enqueue_script('jquery-ui-sortable');
					break;
				case 'usces_itemedit':
					if( isset($_REQUEST['action']) && 'upload_register' == $_REQUEST['action'] ){
						@ob_end_clean();
						ob_start();
					}else{
						wp_enqueue_script('jquery-ui-sortable');
						wp_enqueue_script('jquery-ui-dialog');
					}
					break;
				case 'usces_delivery':
					wp_enqueue_script('jquery-ui-tabs', array('jquery-ui-core'));
					$jquery_cookieUrl = USCES_FRONT_PLUGIN_URL.'/js/jquery/jquery.cookie.js';
					wp_enqueue_script( 'jquery-cookie', $jquery_cookieUrl, array('jquery') );
					break;
				case 'usces_system':
					wp_enqueue_script('jquery-ui-tabs', array('jquery-ui-core'));
					$jquery_cookieUrl = USCES_FRONT_PLUGIN_URL.'/js/jquery/jquery.cookie.js';
					wp_enqueue_script( 'jquery-cookie', $jquery_cookieUrl, array('jquery') );
					break;
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
		
		do_action( 'usces_after_main' );
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
//usces_log('use_ssl : '.$this->use_ssl, 'acting_transaction.log');
		if($this->use_ssl) {
			if( $permalink_structure ){
				$this->delim = '&';
				$home_perse = parse_url(get_option('home'));
				$home_perse_path = isset($home_perse['path']) ? $home_perse['path'] : '';
				$home_path = $home_perse['host'].$home_perse_path;
				$ssl_perse = parse_url($this->options['ssl_url']);
				$ssl_perse_path = isset($ssl_perse['path']) ? $ssl_perse['path'] : '';
				$ssl_path = $ssl_perse['host'].$ssl_perse_path;
				if( $home_perse_path != $ssl_perse_path ){
					if( ! defined('USCES_CUSTOMER_URL') )
						define('USCES_CUSTOMER_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_CART_NUMBER . '&customerinfo=1&uscesid=' . $this->get_uscesid());
					if( ! defined('USCES_CART_URL') )
						define('USCES_CART_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_CART_NUMBER . '&uscesid=' . $this->get_uscesid());
					if( ! defined('USCES_LOSTMEMBERPASSWORD_URL') )
						define('USCES_LOSTMEMBERPASSWORD_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid() . '&page=lostmemberpassword');
					if( ! defined('USCES_NEWMEMBER_URL') )
						define('USCES_NEWMEMBER_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid() . '&page=newmember');
					if( ! defined('USCES_LOGIN_URL') )
						define('USCES_LOGIN_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid() . '&page=login');
					if( ! defined('USCES_LOGOUT_URL') )
						define('USCES_LOGOUT_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid() . '&page=logout');
					if( ! defined('USCES_MEMBER_URL') )
						define('USCES_MEMBER_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid());
					$inquiry_url = empty( $this->options['inquiry_id'] ) ? '' : $this->options['ssl_url'] . '/index.php?page_id=' . $this->options['inquiry_id'] . '&uscesid=' . $this->get_uscesid();
					if( ! defined('USCES_INQUIRY_URL') )
						define('USCES_INQUIRY_URL', $inquiry_url);
					if( ! defined('USCES_CART_NONSESSION_URL') )
						define('USCES_CART_NONSESSION_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_CART_NUMBER);
//20110208ysk start
					//define('USCES_PAYPAL_NOTIFY_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_CART_NUMBER . '&acting_return=paypal_ipn&uscesid=' . $this->get_uscesid(false));
					if( ! defined('USCES_PAYPAL_NOTIFY_URL') )
						define('USCES_PAYPAL_NOTIFY_URL', $this->options['ssl_url'] . '/index.php?page_id=' . USCES_CART_NUMBER . '&acting=paypal_ipn&uscesid=' . $this->get_uscesid(false));
//20110208ysk end
				}else{
					$ssl_plink_cart = str_replace('http://','https://', str_replace( $home_path, $ssl_path, get_page_link(USCES_CART_NUMBER) ));
					$ssl_plink_member = str_replace('http://','https://', str_replace( $home_path, $ssl_path, get_page_link(USCES_MEMBER_NUMBER) ));
					if( ! defined('USCES_CUSTOMER_URL') )
						define('USCES_CUSTOMER_URL', $ssl_plink_cart . '?uscesid=' . $this->get_uscesid() . '&customerinfo=1');
					if( ! defined('USCES_CART_URL') )
						define('USCES_CART_URL', $ssl_plink_cart . '?uscesid=' . $this->get_uscesid());
					if( ! defined('USCES_LOSTMEMBERPASSWORD_URL') )
						define('USCES_LOSTMEMBERPASSWORD_URL', $ssl_plink_member . '?uscesid=' . $this->get_uscesid() . '&page=lostmemberpassword');
					if( ! defined('USCES_NEWMEMBER_URL') )
						define('USCES_NEWMEMBER_URL', $ssl_plink_member  . '?uscesid=' . $this->get_uscesid(). '&page=newmember');
					if( ! defined('USCES_LOGIN_URL') )
						define('USCES_LOGIN_URL', $ssl_plink_member . '?uscesid=' . $this->get_uscesid() . '&page=login');
					if( ! defined('USCES_LOGOUT_URL') )
						define('USCES_LOGOUT_URL', $ssl_plink_member . '?uscesid=' . $this->get_uscesid() . '&page=logout');
					if( ! defined('USCES_MEMBER_URL') )
						define('USCES_MEMBER_URL', $ssl_plink_member . '?uscesid=' . $this->get_uscesid());
					if( !isset($this->options['inquiry_id']) || !( (int)$this->options['inquiry_id'] ) ){
						$inquiry_url = get_home_url();
					}else{
						$ssl_plink_inquiry = str_replace('http://','https://', str_replace( $home_path, $ssl_path, get_page_link($this->options['inquiry_id']) ));
						$inquiry_url = empty( $this->options['inquiry_id'] ) ? '' : $ssl_plink_inquiry . '?uscesid=' . $this->get_uscesid();
					}
					if( ! defined('USCES_INQUIRY_URL') )
						define('USCES_INQUIRY_URL', $inquiry_url);
					if( ! defined('USCES_CART_NONSESSION_URL') )
						define('USCES_CART_NONSESSION_URL', $ssl_plink_cart);
//20110208ysk start
					//define('USCES_PAYPAL_NOTIFY_URL', $ssl_plink_cart . '?acting_return=paypal_ipn&uscesid=' . $this->get_uscesid(false));
					if( ! defined('USCES_PAYPAL_NOTIFY_URL') )
						define('USCES_PAYPAL_NOTIFY_URL', $ssl_plink_cart . '?acting=paypal_ipn&uscesid=' . $this->get_uscesid(false));
//20110208ysk end
				}
			}else{
				$this->delim = '&';
				if( ! defined('USCES_CUSTOMER_URL') )
					define('USCES_CUSTOMER_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_CART_NUMBER . '&customerinfo=1&uscesid=' . $this->get_uscesid());
				if( ! defined('USCES_CART_URL') )
					define('USCES_CART_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_CART_NUMBER . '&uscesid=' . $this->get_uscesid());
				if( ! defined('USCES_LOSTMEMBERPASSWORD_URL') )
					define('USCES_LOSTMEMBERPASSWORD_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid() . '&page=lostmemberpassword');
				if( ! defined('USCES_NEWMEMBER_URL') )
					define('USCES_NEWMEMBER_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid() . '&page=newmember');
				if( ! defined('USCES_LOGIN_URL') )
					define('USCES_LOGIN_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid() . '&page=login');
				if( ! defined('USCES_LOGOUT_URL') )
					define('USCES_LOGOUT_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid() . '&page=logout');
				if( ! defined('USCES_MEMBER_URL') )
					define('USCES_MEMBER_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_MEMBER_NUMBER . '&uscesid=' . $this->get_uscesid());
				$inquiry_url = empty( $this->options['inquiry_id'] ) ? '' : $this->options['ssl_url'] . '/?page_id=' . $this->options['inquiry_id'] . '&uscesid=' . $this->get_uscesid();
				if( ! defined('USCES_INQUIRY_URL') )
					define('USCES_INQUIRY_URL', $inquiry_url);
				if( ! defined('USCES_CART_NONSESSION_URL') )
					define('USCES_CART_NONSESSION_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_CART_NUMBER);
//20110208ysk start
				//define('USCES_PAYPAL_NOTIFY_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_CART_NUMBER . '&acting_return=paypal_ipn&uscesid=' . $this->get_uscesid(false));
				if( ! defined('USCES_PAYPAL_NOTIFY_URL') )
					define('USCES_PAYPAL_NOTIFY_URL', $this->options['ssl_url'] . '/?page_id=' . USCES_CART_NUMBER . '&acting=paypal_ipn&uscesid=' . $this->get_uscesid(false));
//20110208ysk end
			}
			if( !is_admin() ){
				add_filter('home_url', array($this, 'usces_ssl_page_link'));
				add_filter('wp_get_attachment_url', array($this, 'usces_ssl_attachment_link'));
				add_filter('icon_dir_uri', array($this, 'usces_ssl_icon_dir_uri'));
				add_filter('stylesheet_directory_uri', array($this, 'usces_ssl_contents_link'));
				add_filter('template_directory_uri', array($this, 'usces_ssl_contents_link'));
				add_filter('script_loader_src', array($this, 'usces_ssl_script_link'));
				add_filter('style_loader_src', array($this, 'usces_ssl_script_link'));
			}
		} else {
			if( $permalink_structure ){
				$this->delim = '?';
				if( ! defined('USCES_CUSTOMER_URL') )
					define('USCES_CUSTOMER_URL', get_page_link(USCES_CART_NUMBER) . '?customerinfo=1');
				if( ! defined('USCES_CART_URL') )
					define('USCES_CART_URL', get_page_link(USCES_CART_NUMBER));
				if( ! defined('USCES_LOSTMEMBERPASSWORD_URL') )
					define('USCES_LOSTMEMBERPASSWORD_URL', get_page_link(USCES_MEMBER_NUMBER) . '?page=lostmemberpassword');
				if( ! defined('USCES_NEWMEMBER_URL') )
					define('USCES_NEWMEMBER_URL', get_page_link(USCES_MEMBER_NUMBER) . '?page=newmember');
				if( ! defined('USCES_LOGIN_URL') )
					define('USCES_LOGIN_URL', get_page_link(USCES_MEMBER_NUMBER) . '?page=login');
				if( ! defined('USCES_LOGOUT_URL') )
					define('USCES_LOGOUT_URL', get_page_link(USCES_MEMBER_NUMBER) . '?page=logout');
				if( ! defined('USCES_MEMBER_URL') )
					define('USCES_MEMBER_URL', get_page_link(USCES_MEMBER_NUMBER));
				$inquiry_url = ( !isset( $this->options['inquiry_id'] ) || !( (int)$this->options['inquiry_id'] )) ? get_home_url() : get_page_link($this->options['inquiry_id']);
				if( ! defined('USCES_INQUIRY_URL') )
					define('USCES_INQUIRY_URL', $inquiry_url);
				if( ! defined('USCES_CART_NONSESSION_URL') )
					define('USCES_CART_NONSESSION_URL', get_page_link(USCES_CART_NUMBER));
//20110208ysk start
				//define('USCES_PAYPAL_NOTIFY_URL', get_page_link(USCES_CART_NUMBER) . '?acting_return=paypal_ipn&uscesid=' . $this->get_uscesid(false));
				if( ! defined('USCES_PAYPAL_NOTIFY_URL') )
					define('USCES_PAYPAL_NOTIFY_URL', get_page_link(USCES_CART_NUMBER) . '?acting=paypal_ipn&uscesid=' . $this->get_uscesid(false));
//20110208ysk end
			}else{
				$this->delim = '&';
				if( ! defined('USCES_CUSTOMER_URL') )
					define('USCES_CUSTOMER_URL', get_option('home') . '/?page_id=' . USCES_CART_NUMBER . '&customerinfo=1');
				if( ! defined('USCES_CART_URL') )
					define('USCES_CART_URL', get_option('home') . '/?page_id=' . USCES_CART_NUMBER);
				if( ! defined('USCES_LOSTMEMBERPASSWORD_URL') )
					define('USCES_LOSTMEMBERPASSWORD_URL', get_option('home') . '/?page_id=' . USCES_MEMBER_NUMBER . '&page=lostmemberpassword');
				if( ! defined('USCES_NEWMEMBER_URL') )
					define('USCES_NEWMEMBER_URL', get_option('home') . '/?page_id=' . USCES_MEMBER_NUMBER . '&page=newmember');
				if( ! defined('USCES_LOGIN_URL') )
					define('USCES_LOGIN_URL', get_option('home') . '/?page_id=' . USCES_MEMBER_NUMBER . '&page=login');
				if( ! defined('USCES_LOGOUT_URL') )
					define('USCES_LOGOUT_URL', get_option('home') . '/?page_id=' . USCES_MEMBER_NUMBER . '&page=logout');
				if( ! defined('USCES_CART_NONSESSION_URL') )
					define('USCES_CART_NONSESSION_URL', get_option('home') . '/?page_id=' . USCES_MEMBER_NUMBER . '&page=logout');
				if( ! defined('USCES_MEMBER_URL') )
					define('USCES_MEMBER_URL', get_option('home') . '/?page_id=' . USCES_MEMBER_NUMBER);
				$inquiry_url = empty( $this->options['inquiry_id'] ) ? '' : get_option('home') . '/?page_id=' . $this->options['inquiry_id'];
				if( ! defined('USCES_INQUIRY_URL') )
					define('USCES_INQUIRY_URL', $inquiry_url);
				if( ! defined('USCES_CART_NONSESSION_URL') )
					define('USCES_CART_NONSESSION_URL', get_option('home') . '/?page_id=' . USCES_CART_NUMBER);
//20110208ysk start
				//define('USCES_PAYPAL_NOTIFY_URL', get_option('home') . '/?page_id=' . USCES_CART_NUMBER . '&acting_return=paypal_ipn&uscesid=' . $this->get_uscesid(false));
				if( ! defined('USCES_PAYPAL_NOTIFY_URL') )
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
		//if($this->is_maintenance()){
		if($this->is_maintenance() and !is_user_logged_in()){//0000651
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
		do_action( 'usces_action_customerinfo' );
		$this->cart->entry();
		$this->error_message = $this->zaiko_check();
		$this->error_message = apply_filters( 'usces_filter_cart_check', $this->error_message );
		if( WCUtils::is_blank($this->error_message) ){
			if($this->is_member_logged_in()){
//20100818ysk start
				//$this->page = 'delivery';
				$this->error_message = has_custom_customer_field_essential();
				$this->page = ( WCUtils::is_blank($this->error_message) ) ? 'delivery' : 'customer';
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
		$this->page = apply_filters( 'usces_filter_backCustomer_page', 'customer' );
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
			if( WCUtils::is_blank($this->error_message) ){
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
//20110715ysk start 0000203
		//$_POST['member_regmode'] = 'newmemberfromcart';
		if(empty($_POST['member_regmode']) or $_POST['member_regmode'] != 'editmemberfromcart') $_POST['member_regmode'] = 'newmemberfromcart';
//20110715ysk end

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

		if( WCUtils::is_blank($this->error_message) ){
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
		global $wpdb, $wp_query;
		if( false === $this->cart->num_row() ){
			header('location: ' . get_option('home'));
			exit;
		}

		$this->cart->entry();
		$this->error_message = $this->zaiko_check();
		if( $this->error_message != '' ){
			$this->page = 'cart';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_cart');
			add_action('the_post', array($this, 'action_cartFilter'));
			add_action('template_redirect', array($this, 'template_redirect'));
			return;
		}

		$this->set_reserve_pre_order_id();
		if(isset($_POST['confirm'])){
			$this->error_message = $this->delivery_check();
		}
		$this->page = ( WCUtils::is_blank($this->error_message) ) ? 'confirm' : 'delivery';
		if( WCUtils::is_blank($this->error_message) ){
//20120919ysk start 0000573
			if( usces_is_member_system() && usces_is_member_system_point() && $this->is_member_logged_in() ) {
				$member_table = $wpdb->prefix."usces_member";
				$query = $wpdb->prepare("SELECT mem_point FROM $member_table WHERE ID = %d", $_SESSION['usces_member']['ID']);
				$mem_point = $wpdb->get_var( $query );
				$_SESSION['usces_member']['point'] = $mem_point;
			}
//20120919ysk end
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
		if( !isset($_REQUEST['wc_nonce']) || !wp_verify_nonce($_REQUEST['wc_nonce'], 'use_point') )
			die('Security check1');
			
		$this->error_message = $this->point_check( $this->cart->get_entry() );
		if( empty($this->error_message) )
			$this->cart->entry();
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
		if( WCUtils::is_blank($this->error_message) && 0 < $this->cart->num_row()){
			$acting_status = '';
			$payments = $this->getPayments( $entry['order']['payment_name'] );
			if( substr($payments['settlement'], 0, 6) == 'acting' && $entry['order']['total_full_price'] > 0 ){
				$acting_flg = ( 'acting' == $payments['settlement'] ) ? $payments['module'] : $payments['settlement'];
				//$query = '';
				//foreach($_POST as $key => $value){
				//	if($key != 'purchase')
				//		$query .= '&' . $key . '=' . urlencode(maybe_serialize($value));
				//}
				unset( $_POST['purchase'] );
				$query = '&'.http_build_query( $_POST );
				$acting_status = $this->acting_processing($acting_flg, $query);
			}
			
			if($acting_status == 'error'){
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
		if( isset($_GET['acting']) and 'anotherlane_card' != $_GET['acting'] ) {//20131220ysk
		if( false === $this->cart->num_row() && ('paypal' != $_GET['acting'] && 1 !== (int)$_GET['acting_return']) ){
			header('location: ' . get_option('home'));
			exit;
		}
		}//20131220ysk
		
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
					if( isset($_REQUEST['wctid']) ){
						usces_ordered_acting_data($_REQUEST['wctid']);
					}
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
		if( (isset($_POST['kakuninyou']) && empty($_POST['kakuninyou'])) && isset($_POST['inq_name']) && !WCUtils::is_blank($_POST['inq_name']) && isset($_POST['inq_mailaddress']) && is_email( trim($_POST['inq_mailaddress']) ) && !WCUtils::is_blank($_POST['inq_contents']) ){
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
			do_action('usces_action_member_logined');
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_member');
		}elseif( 'login' == $res ){
			$this->page = 'login';
			add_filter('yoast-ga-push-after-pageview', 'usces_trackPageview_login');
		}
		add_action('the_post', array($this, 'action_memberFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function regmember(){
		$nonce = isset( $_REQUEST['wc_nonce'] ) ? $_REQUEST['wc_nonce'] : '';
		if( !wp_verify_nonce( $nonce, 'post_member' ) )
			die('Security check2');
			
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
		$nonce = isset( $_REQUEST['wc_nonce'] ) ? $_REQUEST['wc_nonce'] : '';
		if( !wp_verify_nonce( $nonce, 'post_member' ) )
			die('Security check3');
			
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
		$nonce = isset( $_REQUEST['wc_nonce'] ) ? $_REQUEST['wc_nonce'] : '';
		if( !wp_verify_nonce( $nonce, 'post_member' ) )
			die('Security check4');
			
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
		$nonce = isset( $_REQUEST['wc_nonce'] ) ? $_REQUEST['wc_nonce'] : '';
		if( !wp_verify_nonce( $nonce, 'post_member' ) )
			die('Security check5');
			
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
		
		if( !isset( $_REQUEST['mem']) || !isset( $_REQUEST['key']) )
			die('Invalid request 1');
			
		$mem_mail = urldecode($_REQUEST['mem']);
		$lostkey = urldecode($_REQUEST['key']);
		$res = usces_check_lostkey($mem_mail, $lostkey);
		if( empty($res) )
			die('Invalid request 2');
		
		
		$this->page = 'changepassword';
		add_action('the_post', array($this, 'action_memberFilter'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}
	
	function changepassword_page(){
		$nonce = isset( $_REQUEST['wc_nonce'] ) ? $_REQUEST['wc_nonce'] : '';
		if( !wp_verify_nonce( $nonce, 'post_member' ) )
			die('Security check6');
			
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
		global $post, $usces_entries, $usces_carts, $usces_members, $usces_gp, $member_regmode;

		if( apply_filters('usces_action_template_redirect', false) ) return;//Deprecated
		if( apply_filters('usces_filter_template_redirect', false) ) return;

		if( is_single() && 'item' == $post->post_mime_type ) {
			if( file_exists(get_stylesheet_directory() . '/wc_templates/wc_item_single.php') ){
				if( !post_password_required($post) ){
					include(get_stylesheet_directory() . '/wc_templates/wc_item_single.php');
					exit;
				}
			}
		}elseif( isset($_REQUEST['page']) && ('search_item' == $_REQUEST['page'] || 'usces_search' == $_REQUEST['page']) && $this->is_cart_page($_SERVER['REQUEST_URI']) ){
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
					break;
				case 'delivery':
					if( file_exists(get_stylesheet_directory() . '/wc_templates/cart/wc_delivery_page.php') ){
						usces_get_entries();
						usces_get_carts();
						include(get_stylesheet_directory() . '/wc_templates/cart/wc_delivery_page.php');
						exit;
					}
					break;
				case 'confirm':
					if( file_exists(get_stylesheet_directory() . '/wc_templates/cart/wc_confirm_page.php') ){
						usces_get_entries();
						usces_get_carts();
						usces_get_members();
						include(get_stylesheet_directory() . '/wc_templates/cart/wc_confirm_page.php');
						exit;
					}
					break;
				case 'ordercompletion':
					if( file_exists(get_stylesheet_directory() . '/wc_templates/cart/wc_completion_page.php') ){
						usces_get_entries();
						usces_get_carts();
						include(get_stylesheet_directory() . '/wc_templates/cart/wc_completion_page.php');
						exit;
					}
					break;
				case 'error':
					if( file_exists(get_stylesheet_directory() . '/wc_templates/cart/wc_cart_error_page.php') ){
						include(get_stylesheet_directory() . '/wc_templates/cart/wc_cart_error_page.php');
						exit;
					}
					break;
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
						break;
					case 'newmemberform':
						if( file_exists(get_stylesheet_directory() . '/wc_templates/member/wc_new_member_page.php') ){
							$member_regmode = 'newmemberform';
							include(get_stylesheet_directory() . '/wc_templates/member/wc_new_member_page.php');
							exit;
						}
						break;
					case 'lostmemberpassword':
						if( file_exists(get_stylesheet_directory() . '/wc_templates/member/wc_lostpassword_page.php') ){
							include(get_stylesheet_directory() . '/wc_templates/member/wc_lostpassword_page.php');
							exit;
						}
						break;
					case 'changepassword':
						if( file_exists(get_stylesheet_directory() . '/wc_templates/member/wc_changepassword_page.php') ){
							include(get_stylesheet_directory() . '/wc_templates/member/wc_changepassword_page.php');
							exit;
						}
						break;
					case 'newcompletion':
					case 'editcompletion':
					case 'lostcompletion':
					case 'changepasscompletion':
						if( file_exists(get_stylesheet_directory() . '/wc_templates/member/wc_member_completion_page.php') ){
							include(get_stylesheet_directory() . '/wc_templates/member/wc_member_completion_page.php');
							exit;
						}
						break;
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

		$lostmail = $_POST['lostmail'];
		$lost_key = $_POST['lostkey'];
		
		$member_table = $wpdb->prefix . "usces_member";
		
		$query = $wpdb->prepare("UPDATE $member_table SET mem_pass = %s WHERE mem_email = %s", 
						md5(trim($_POST['loginpass1'])), $lostmail);
		$res = $wpdb->query( $query );
		//$res = $wpdb->last_results;

		if ( $res === false ) {
			$this->error_message = __('Error: failure in updating password', 'usces');
			return 'login';
		}else {
			usces_remove_lostmail_key( $lostmail, $lost_key );
			return 'changepasscompletion';
		}

	}
	
	function lostmail() {
		$delim = apply_filters( 'usces_filter_delim', $this->delim );
	
		$lostmail = trim($_POST['loginmail']);
		$lost_key = usces_make_lost_key();
		usces_store_lostmail_key( $lostmail, $lost_key );
		
		$uri = USCES_MEMBER_URL . $delim . 'uscesmode=changepassword&mem=' . urlencode($lostmail) . '&key=' . urlencode($lost_key);
		
		$res = usces_lostmail($uri);
		return $res;
	
	}

	function regist_member() {
		global $wpdb;
		$_POST = $this->stripslashes_deep_post($_POST);

		$member = $this->get_member();
		$mode = $_POST['member_regmode'];
		$member_table = $wpdb->prefix . "usces_member";
		$member_meta_table = $wpdb->prefix . "usces_member_meta";

//20110715ysk start 0000203
		//$error_mes = ( $_POST['member_regmode'] == 'newmemberfromcart' ) ? $this->member_check_fromcart() : $this->member_check();
		$error_mes = ( $_POST['member_regmode'] == 'newmemberfromcart' or $_POST['member_regmode'] == 'editmemberfromcart' ) ? $this->member_check_fromcart() : $this->member_check();
//20110715ysk end

		if ( $error_mes != '' ) {

			$this->error_message = $error_mes;
			return $mode;

		} elseif ( $_POST['member_regmode'] == 'editmemberform' ) {
			
			$this->get_current_member();
			$mem_id = $this->current_member['id'];
			do_action('usces_action_pre_edit_memberdata', $_POST['member'], $mem_id);

//20130802ysk start 0000741
			$query = $wpdb->prepare("SELECT ID FROM $member_table WHERE mem_email = %s", trim($_POST['member']['mailaddress1']));
			$id = $wpdb->get_var( $query );
			if ( !empty($id) and $id != $mem_id ) {
				$this->error_message = __('This e-mail address has been already registered.', 'usces');
				return $mode;
			}
//20130802ysk end

			$query = $wpdb->prepare("SELECT mem_pass FROM $member_table WHERE ID = %d", $mem_id);
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
					$mem_id 
					);
			$res = $wpdb->query( $query );

			if( $res !== false ){
				$this->set_member_meta_value('customer_country', $_POST['member']['country'], $mem_id);
//20100818ysk start
				$res = $this->reg_custom_member($mem_id);
//20100818ysk end
				do_action('usces_action_edit_memberdata', $_POST['member'], $mem_id);
				//$meta_keys = apply_filters( 'usces_filter_delete_member_pcid', "'zeus_pcid', 'remise_pcid', 'digitalcheck_ip_user_id'" );
				$meta_keys = apply_filters( 'usces_filter_delete_member_pcid', "'remise_pcid', 'digitalcheck_ip_user_id'" );
				$query = $wpdb->prepare("DELETE FROM $member_meta_table WHERE member_id = %d AND meta_key IN( $meta_keys )", 
						$mem_id 
						);
				$res = $wpdb->query( $query );

				$this->get_current_member();
				return 'editmemberform';

			} else {
				$this->error_message = __('Error:failure in update', 'usces');
				return $mode;
			}

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
//20110714ysk start 0000207
//20100818ysk start
					//$res = $this->reg_custom_member($wpdb->insert_id);
					$res = $this->reg_custom_member($user['ID']);
//20100818ysk end
//20110714ysk end
					$mser = usces_send_regmembermail($user);

					do_action('usces_action_member_registered', $_POST['member'], $user['ID']);

					return 'newcompletion';

				} else {
					$this->error_message = __('Error:failure in update', 'usces');
					return $mode;
				}
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
//20110714ysk start 0000207
					$member_id = $wpdb->insert_id;
					//$this->set_member_meta_value('customer_country', $_POST['member']['country'], $wpdb->insert_id);
					$this->set_member_meta_value('customer_country', $_POST['customer']['country'], $member_id);
//20100818ysk start
					//$res = $this->reg_custom_member($wpdb->insert_id);
					$res = $this->reg_custom_member($member_id);
//20100818ysk end
//20110714ysk end
					//usces_send_regmembermail();
					do_action('usces_action_member_registered', $_POST['customer'], $member_id);
					$user = $_POST['customer'];
					$user['ID'] = $member_id;
					$mser = usces_send_regmembermail($user);
					$_POST['loginmail'] = trim($_POST['customer']['mailaddress1']);
					$_POST['loginpass'] = trim($_POST['customer']['password1']);
					if( $this->member_login() == 'member' ){
						$_SESSION['usces_entry']['member_regmode'] = 'editmemberfromcart';
						return 'newcompletion';
					}

				} else {
					$this->error_message = __('Error:failure in update', 'usces');
					return $mode;
				}
			}

//20110715ysk start 0000203
		} elseif ( $_POST['member_regmode'] == 'editmemberfromcart' ) {

			$this->get_current_member();
			$mem_id = $this->current_member['id'];
			do_action('usces_action_pre_edit_memberdata', $_POST['customer'], $mem_id);

			$query = $wpdb->prepare("SELECT mem_pass FROM $member_table WHERE ID = %d", $mem_id);
			$pass = $wpdb->get_var( $query );

			$password = ( !empty($_POST['customer']['password1']) && trim($_POST['customer']['password1']) == trim($_POST['customer']['password2']) ) ? md5(trim($_POST['customer']['password1'])) : $pass;
			$query = $wpdb->prepare("UPDATE $member_table SET 
					mem_pass = %s, mem_name1 = %s, mem_name2 = %s, mem_name3 = %s, mem_name4 = %s, 
					mem_zip = %s, mem_pref = %s, mem_address1 = %s, mem_address2 = %s, 
					mem_address3 = %s, mem_tel = %s, mem_fax = %s, mem_email = %s WHERE ID = %d", 
					$password, 
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
					trim($_POST['customer']['mailaddress1']), 
					$mem_id 
					);
			$res = $wpdb->query( $query );
			if( $res !== false ){
				$this->set_member_meta_value('customer_country', $_POST['customer']['country'], $mem_id);
				$res = $this->reg_custom_member($mem_id);
				do_action('usces_action_edit_memberdata', $_POST['customer'], $mem_id);
				unset($_SESSION['usces_member']);
				$this->member_just_login(trim($_POST['customer']['mailaddress1']), trim($_POST['customer']['password1']));
				return 'newcompletion';

			} else {
				$this->error_message = __('Error:failure in update', 'usces');
				return $mode;
			}
//20110715ysk end
		}
	}

	function delete_member() {
		if( ! $this->is_member_logged_in() )
				return false;
		$mem = $this->get_member();
		if( ! $mem['ID'] )
				return false;

		$res = usces_delete_memberdata( $mem['ID'] );
		if( $res ) {
			usces_send_delmembermail( $mem );
		}

		return $res;
	}

	function is_member_logged_in( $id = false ) {
		if( $id === false ){
			if( !empty($_SESSION['usces_member']['ID']) )
				return true;
			else
				return false;
		}else{
			if( !empty($_SESSION['usces_member']['ID']) && $_SESSION['usces_member']['ID'] == $id )
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
		$_POST = $this->stripslashes_deep_post($_POST);
		
		$cookie = $this->get_cookie();

		
		if ( isset($cookie['rme']) && $cookie['rme'] == 'forever' && !isset($_POST['rememberme']) && !isset($_POST['loginmail'])) {
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
					$_SESSION['usces_member']['status'] = $member['mem_status'];
//20100818ysk start
					$this->set_session_custom_member($member['ID']);
//20100818ysk end
					$this->get_current_member();
					
					do_action( 'usces_action_after_login' );
					return apply_filters( 'usces_filter_member_login', 'member', $member );
				}
			}
		} else if ( isset($_POST['loginmail']) && WCUtils::is_blank($_POST['loginmail']) && isset($_POST['loginpass']) && WCUtils::is_blank($_POST['loginpass']) && isset($cookie['rme']) && $cookie['rme'] != 'forever' ) {
			return 'login';
		} else if ( isset($_POST['loginmail']) && WCUtils::is_blank($_POST['loginpass']) && isset($cookie['rme']) && $cookie['rme'] != 'forever' ) {
			$this->current_member['email'] = trim($_POST['loginmail']);
			$this->error_message = __('<b>Error:</b> Enter the password.', 'usces');
			return 'login';
		} else if ( !isset($_POST['loginmail']) ){
			return 'login';
		} else {
			$email = isset($_POST['loginmail']) ? trim($_POST['loginmail']) : '';
			$pass = isset($_POST['loginpass']) ? md5(trim($_POST['loginpass'])) : '';
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
					$_SESSION['usces_member']['status'] = $member['mem_status'];
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
					
					do_action( 'usces_action_after_login' );
					return apply_filters( 'usces_filter_member_login', 'member', $member );
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
			$_SESSION['usces_member']['status'] = $member['mem_status'];
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

			do_action( 'usces_action_after_login' );
			return apply_filters( 'usces_filter_member_login', 'member', $member );
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
			$this->current_member['name'] = usces_localized_name( $_SESSION['usces_member']['name1'], $_SESSION['usces_member']['name2'], 'return');
		} else {
			$this->current_member['id'] = 0;
			$this->current_member['name'] = __('guest', 'usces');
		}
	}

	function get_member() {
		$res = array(
					'ID' => '', 
					'registered' => '', 
					'mailaddress1' => '', 
					'mailaddress2' => '', 
					'password1' => '', 
					'password2' => '', 
					'point' => '', 
					'name1' => '', 
					'name2' => '', 
					'name3' => '', 
					'name4' => '', 
					'zipcode' => '',
					'address1' => '',
					'address2' => '',
					'address3' => '',
					'tel' => '',
					'fax' => '',
					'country' => '',
					'pref' => '',
					'status' => ''
				 );
		if(!empty($_SESSION['usces_member'])) {
			foreach ( $_SESSION['usces_member'] as $key => $value ) {
	//20100818ysk start
				if(is_array($_SESSION['usces_member'][$key])) 
					$res[$key] = stripslashes_deep($value);
				else
	//20100818ysk end
					$res[$key] = stripslashes($value);
			}
		}
		return $res;
	}

	function get_member_info( $mid ) {
		global $wpdb;
		$infos = array();
		
		//if( !current_user_can('activate_plugins') ) return array();
		
		$table = $wpdb->prefix . "usces_member";
		$query = $wpdb->prepare("SELECT * FROM $table WHERE ID = %d", $mid);
		$datas = $wpdb->get_results( $query, ARRAY_A );
		if( $datas ) {
			$infos = $datas[0];
			
			$table = $wpdb->prefix . "usces_member_meta";
			$query = $wpdb->prepare("SELECT meta_key, meta_value FROM $table WHERE member_id = %d", $mid);
			$metas = $wpdb->get_results( $query, ARRAY_A );
			
			foreach( $metas as $meta ){
				$infos[$meta['meta_key']] = maybe_unserialize($meta['meta_value']);
			}
		}
		return $infos;
	}

	function set_member_info( $data=array(), $mid ) {
		global $wpdb;
		
		$table = $wpdb->prefix . "usces_member";
		$res = $wpdb->update( $table, $data, array('ID'=>$mid), NULL, array('%d'));
		return $res;
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

	function is_purchased_item($mid, $post_id, $sku = NULL) {
		global $wpdb;
		$res = false;
		
		$history = $this->get_member_history($mid);
		foreach ( $history as $umhs ) {
			$cart = $umhs['cart'];
			$status = $umhs['order_status'];
			for($i=0; $i<count($cart); $i++) { 
				$cart_row = $cart[$i];
				$sku_code = urldecode($cart_row['sku']);
				if( empty($sku) ){
					if( $cart_row['post_id'] == $post_id && (false === strpos($status, 'noreceipt') && false === strpos($status, 'pending')) ){
						$res = true;
						break 2;
					}elseif( $cart_row['post_id'] == $post_id && (false !== strpos($status, 'noreceipt') || false !== strpos($status, 'pending')) ){
						$res = 'noreceipt';
						break 2;
					}
				}else{
					if( $cart_row['post_id'] == $post_id && $sku_code == $sku && (false === strpos($status, 'noreceipt') && false === strpos($status, 'pending')) ){
						$res = true;
						break 2;
					}elseif( $cart_row['post_id'] == $post_id && $sku_code == $sku && (false !== strpos($status, 'noreceipt') || false !== strpos($status, 'pending')) ){
						$res = 'noreceipt';
						break 2;
					}
				}
			}
		
		}
			return $res;
	}
	
	function get_order_data($order_id, $mode = '' ) {
		global $wpdb;
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
					'mem_id' => $value->mem_id,
					'cart' => unserialize($value->order_cart),
					'condition' => unserialize($value->order_condition),
					'getpoint' => $value->order_getpoint,
					'usedpoint' => $value->order_usedpoint,
					'discount' => $value->order_discount,
					'payment_name' => $value->order_payment_name,
					'shipping_charge' => $value->order_shipping_charge,
					'cod_fee' => $value->order_cod_fee,
					'tax' => $value->order_tax,
					'end_price' => $value->order_item_total_price - $value->order_usedpoint + $value->order_discount + $value->order_shipping_charge + $value->order_cod_fee + $value->order_tax,
					'status' => $value->order_status,
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
		//$_POST = $this->stripslashes_deep_post($_POST);
		$mes = array();

		$ids = array_keys($_POST['inCart']);
		$post_id = $ids[0];
		$skus = array_keys($_POST['inCart'][$post_id]);
		$sku = $skus[0];
		$quant = isset($_POST['quant'][$post_id][$sku]) ? (int)$_POST['quant'][$post_id][$sku] : 1;
		$stock = $this->getItemZaikoNum($post_id, $sku);
		$zaiko_id = (int)$this->getItemZaikoStatusId($post_id, $sku);
		$itemRestriction = get_post_meta($post_id, '_itemRestriction',true );

		if( 1 > $quant ){
			$mes[$post_id][$sku] = __('enter the correct amount', 'usces') . "<br />";
		}else if( $quant > (int)$itemRestriction && !WCUtils::is_blank($itemRestriction) && !WCUtils::is_zero($itemRestriction) ){
			$mes[$post_id][$sku] = sprintf(__("This article is limited by %d at a time.", 'usces'), $itemRestriction) . "<br />";
		}else if( $quant > (int)$stock && !WCUtils::is_blank($stock) ){
			$mes[$post_id][$sku] = __('Sorry, stock is insufficient.', 'usces') . ' ' . __('Current stock', 'usces') . $stock . "<br />";
		}else if( 1 < $zaiko_id ){
			$mes[$post_id][$sku] = __('Sorry, this item is sold out.', 'usces') . "<br />";
		}

		$ioptkeys = $this->get_itemOptionKey( $post_id, true );
		if($ioptkeys){
			foreach($ioptkeys as $key => $value){
				$optValues = $this->get_itemOptions( urldecode($value), $post_id );
				if( 0 == $optValues['means'] ){ //case of select
					if( $optValues['essential'] && '#NONE#' == $_POST['itemOption'][$post_id][$sku][$value] ){
						$mes[$post_id][$sku] .= sprintf(__("Chose the %s", 'usces'), urldecode($value)) . "<br />";
					}
				}elseif( 1 == $optValues['means'] ){ //case of multiselect
					if( $optValues['essential'] ){
						$mselect = 0;
						foreach((array)$_POST['itemOption'][$post_id][$sku][$value] as $mvalue){
							if(!empty($mvalue) and '#NONE#' != $mvalue) $mselect++;
						}
						if( $mselect == 0 ){
							$mes[$post_id][$sku] .= sprintf(__("Chose the %s", 'usces'), urldecode($value)) . "<br />";
						}
					}
				}else{ //case of text
					if( $optValues['essential'] && WCUtils::is_blank($_POST['itemOption'][$post_id][$sku][$value]) ){
						$mes[$post_id][$sku] .= sprintf(__("Input the %s", 'usces'), urldecode($value)) . "<br />";
					}
				}
			}
		}

		$mes = apply_filters('usces_filter_incart_check', $mes, $post_id, $sku);

		if( isset($mes[$post_id]) && is_array($mes[$post_id]) ){
			foreach( $mes[$post_id] as $skukey => $skuvalue ){
				$mes[$post_id][$skukey] = rtrim($skuvalue, "<br />");
			}
		}

		if( !empty($mes) ){
			$_SESSION['usces_singleitem']['itemOption'] = $_POST['itemOption'];
			$_SESSION['usces_singleitem']['quant'] = $_POST['quant'];
			$_SESSION['usces_singleitem']['error_message'] = $mes;
			if( false === strpos($_POST['usces_referer'], 'http') ){
				$parse_url = parse_url(get_home_url());
				header('location: ' . $parse_url['scheme'] . '://' . $parse_url['host'] . $_POST['usces_referer'] . '#cart_button');
			}else{
				header('location: ' . $_POST['usces_referer'] . '#cart_button');
			}
			exit;
		}
		
		do_action('usces_action_incart_checked', $mes, $post_id, $sku);
	}
	
	function zaiko_check() {
		$mes = '';
		$cart = $this->cart->get_cart();
		$stocks = array();
		
		for($i=0; $i<count($cart); $i++) { 
			$cart_row = $cart[$i];
			$post_id = $cart_row['post_id'];
			$sku = $cart_row['sku'];
			$sku_code = urldecode($cart_row['sku']);
			
			$quant = ( isset($_POST['quant']) ) ? trim($_POST['quant'][$i][$post_id][$sku]) : $cart_row['quantity'];
			//$zaiko_status = $this->getItemZaiko($post_id, $sku);
			$zaiko_id = (int)$this->getItemZaikoStatusId($post_id, $sku_code);
			$stock = $this->getItemZaikoNum($post_id, $sku_code);
			if( !isset($stocks[$post_id][$sku]) ){
				if( !WCUtils::is_blank($stock) ){
					$stocks[$post_id][$sku] = $stock;
				}else{
					$stocks[$post_id][$sku] = NULL;
				}
			}
			$checkstock = $stocks[$post_id][$sku];
			$stocks[$post_id][$sku] = $stocks[$post_id][$sku] - $quant;
			$itemRestriction = get_post_meta($post_id, '_itemRestriction', true);

			//$red = (in_array($zaiko_status, array(__('Sold Out', 'usces'), __('Out Of Stock', 'usces'), __('Out of print', 'usces')))) ? 'red' : '';

			if( 1 > (int)$quant ){
				$mes .= sprintf(__("Enter the correct amount for the No.%d item.", 'usces'), ($i+1)) . "<br />";
			}else if( 1 < $zaiko_id || WCUtils::is_zero($stock) ){
				$mes .= sprintf(__('Sorry, No.%d item is sold out.', 'usces'), ($i+1)) . "<br />";
			}else if( $quant > (int)$itemRestriction && !WCUtils::is_blank($itemRestriction) && !WCUtils::is_zero($itemRestriction) ){
				$mes .= sprintf(__('This article is limited by %1$d at a time for the No.%2$d item.', 'usces'), $itemRestriction, ($i+1)) . "<br />";
			}else if( 0 > $stocks[$post_id][$sku] && !WCUtils::is_blank($stock) ){
				$mes .= sprintf(__('Stock of No.%1$d item is remainder %2$d.', 'usces'), ($i+1), $checkstock) . "<br />";
			}
		}
		$mes = apply_filters('usces_filter_zaiko_check', $mes, $cart);
		return $mes;	
	}

	function member_check() {
		$mes = '';
		foreach ( $_POST['member'] as $key => $vlue ) {
			$_SESSION['usces_member'][$key] = trim($vlue);
		}
		//0000526
		$member_pass_rule_min = $this->options['system']['member_pass_rule_min'];
		$member_pass_rule_max = $this->options['system']['member_pass_rule_max'];

		if ( !WCUtils::is_blank($_POST['member']['password1']) || !WCUtils::is_blank($_POST['member']['password2']) ){
			if( !empty( $member_pass_rule_max ) ){
				if( $member_pass_rule_min > strlen( trim($_POST['member']['password1']) ) || strlen( trim($_POST['member']['password1']) ) > $member_pass_rule_max ){
					$mes .= sprintf(__('Please enter %2$s characters a minimum of %1$s characters and a maximum password.', 'usces'), $member_pass_rule_min, $member_pass_rule_max ) . "<br />";
				}
			}else{
				if( $member_pass_rule_min > strlen( trim($_POST['member']['password1']) ) ){
					$mes .= sprintf(__('Please enter at least %s characters password.', 'usces'), $member_pass_rule_min) . "<br />";
				}
			}
		}
		if ( $_POST['member_regmode'] == 'editmemberform' ) {
			if ( (!WCUtils::is_blank($_POST['member']['password1']) || !WCUtils::is_blank($_POST['member']['password2']) ) && trim($_POST['member']['password1']) != trim($_POST['member']['password2']) )
				$mes .= __('Password is not correct.', 'usces') . "<br />";

			if ( !is_email($_POST['member']['mailaddress1']) || WCUtils::is_blank($_POST['member']['mailaddress1']) )
				$mes .= __('e-mail address is not correct', 'usces') . "<br />";
				
		} else {
			if ( WCUtils::is_blank($_POST['member']['password1']) || WCUtils::is_blank($_POST['member']['password2']) || trim($_POST['member']['password1']) != trim($_POST['member']['password2']) )
				$mes .= __('Password is not correct.', 'usces') . "<br />";
			if ( !is_email($_POST['member']['mailaddress1']) || WCUtils::is_blank($_POST['member']['mailaddress1']) || WCUtils::is_blank($_POST['member']['mailaddress2']) || trim($_POST['member']['mailaddress1']) != trim($_POST['member']['mailaddress2']) )
				$mes .= __('e-mail address is not correct', 'usces') . "<br />";
			
		}
		if ( WCUtils::is_blank($_POST["member"]["name1"]) )
			$mes .= __('Name is not correct', 'usces') . "<br />";//20111116ysk 0000299
//		if ( trim($_POST["member"]["name3"]) == "" && USCES_JP )
//			$mes .= __('Invalid CANNAT pretend.', 'usces') . "<br />";
		if ( WCUtils::is_blank($_POST["member"]["zipcode"]) )
			$mes .= __('postal code is not correct', 'usces') . "<br />";
		if ( $_POST["member"]["pref"] == __('-- Select --', 'usces') || $_POST["member"]["pref"] == '-- Select --' )
			$mes .= __('enter the prefecture', 'usces') . "<br />";
		if ( WCUtils::is_blank($_POST["member"]["address1"]) )
			$mes .= __('enter the city name', 'usces') . "<br />";
		if ( WCUtils::is_blank($_POST["member"]["address2"]) )
			$mes .= __('enter house numbers', 'usces') . "<br />";
		if ( WCUtils::is_blank($_POST["member"]["tel"]) )
			$mes .= __('enter phone numbers', 'usces') . "<br />";
		if( !WCUtils::is_blank($_POST['member']["tel"]) && preg_match("/[^\d-]/", trim($_POST["member"]["tel"])) )
			$mes .= __('Please input a phone number with a half size number.', 'usces') . "<br />";
			
		$mes = apply_filters('usces_filter_member_check', $mes);
	
		return $mes;
	}

	function member_check_fromcart() {
		$mes = '';
		//0000526
		$member_pass_rule_min = $this->options['system']['member_pass_rule_min'];
		$member_pass_rule_max = $this->options['system']['member_pass_rule_max'];

		if ( !WCUtils::is_blank($_POST['customer']['password1']) || !WCUtils::is_blank($_POST['customer']['password2']) ){
			if( !empty( $member_pass_rule_max ) ){
				if( $member_pass_rule_min > strlen( trim($_POST['customer']['password1']) ) || strlen( trim($_POST['customer']['password1']) ) > $member_pass_rule_max )
					$mes .= sprintf(__('Please enter %2$s characters a minimum of %1$s characters and a maximum password.', 'usces'), $member_pass_rule_min, $member_pass_rule_max ) . "<br />";
			}else{
				if( $member_pass_rule_min > strlen( trim($_POST['customer']['password1']) ) )
					$mes .= sprintf(__('Please enter at least %s characters password.', 'usces'), $member_pass_rule_min) . "<br />";
			}
		}
		if ( WCUtils::is_blank($_POST['customer']['password1']) || WCUtils::is_blank($_POST['customer']['password2']) || trim($_POST['customer']['password1']) != trim($_POST['customer']['password2']) )
			$mes .= __('Password is not correct.', 'usces') . "<br />";
		if ( !is_email($_POST['customer']['mailaddress1']) || WCUtils::is_blank($_POST['customer']['mailaddress1']) || WCUtils::is_blank($_POST['customer']['mailaddress2']) || trim($_POST['customer']['mailaddress1']) != trim($_POST['customer']['mailaddress2']) )
			$mes .= __('e-mail address is not correct', 'usces') . "<br />";
		if ( WCUtils::is_blank($_POST["customer"]["name1"]) )
			$mes .= __('Name is not correct', 'usces') . "<br />";//20111116ysk 0000299
//		if ( trim($_POST["customer"]["name3"]) == "" && USCES_JP )
//			$mes .= __('Invalid CANNAT pretend.', 'usces') . "<br />";
		if ( WCUtils::is_blank($_POST["customer"]["zipcode"]) )
			$mes .= __('postal code is not correct', 'usces') . "<br />";
		if ( $_POST["customer"]["pref"] == __('-- Select --', 'usces') || $_POST["customer"]["pref"] == '-- Select --' )
			$mes .= __('enter the prefecture', 'usces') . "<br />";
		if ( WCUtils::is_blank($_POST["customer"]["address1"]) )
			$mes .= __('enter the city name', 'usces') . "<br />";
		if ( WCUtils::is_blank($_POST["customer"]["address2"]) )
			$mes .= __('enter house numbers', 'usces') . "<br />";
		if ( WCUtils::is_blank($_POST["customer"]["tel"]) )
			$mes .= __('enter phone numbers', 'usces') . "<br />";
		if( !WCUtils::is_blank($_POST['customer']["tel"]) && preg_match("/[^\d-]/", trim($_POST["customer"]["tel"])) )
			$mes .= __('Please input a phone number with a half size number.', 'usces') . "<br />";
	
		$mes = apply_filters('usces_filter_member_check_fromcart', $mes);

		return $mes;
	}

	function admin_member_check() {
		global $wpdb;
		$mes = '';
		if ( !is_email( trim($_POST['member']["email"]) ) ){
			$mes .= __('e-mail address is not correct', 'usces') . "<br />";
		}else{
			$member_table = $wpdb->prefix . "usces_member";
			$mem_email = $wpdb->get_var( $wpdb->prepare("SELECT mem_email FROM $member_table WHERE ID = %d LIMIT 1", trim($_POST['member_id'])) );
			if( trim($_POST['member']["email"]) != $mem_email ){
				$mem_ID = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $member_table WHERE mem_email = %s LIMIT 1", trim($_POST['member']["email"])) );
				if( !empty($mem_ID) )
					$mes .= __('This e-mail address has been already registered.', 'usces') . "<br />";
			}
		}
		if ( WCUtils::is_blank($_POST['member']["name1"]) )
			$mes .= __('Name is not correct', 'usces') . "<br />";
//		if ( trim($_POST["mem_name3"]) == "" && USCES_JP )
//			$mes .= __('Invalid CANNAT pretend.', 'usces') . "<br />";
//		if ( trim($_POST['member']["zipcode"]) == "" )
//			$mes .= __('postal code is not correct', 'usces') . "<br />";
//		if ( $_POST['member']["pref"] == __('-- Select --', 'usces') )
//			$mes .= __('enter the prefecture', 'usces') . "<br />";
//		if ( trim($_POST['member']["address1"]) == "" )
//			$mes .= __('enter the city name', 'usces') . "<br />";
//		if ( trim($_POST['member']["address2"]) == "" )
//			$mes .= __('enter house numbers', 'usces') . "<br />";
//		if ( trim($_POST['member']["tel"]) == "" )
//			$mes .= __('enter phone numbers', 'usces') . "<br />";
		if( !WCUtils::is_blank($_POST['member']["tel"]) && preg_match("/[^\d-]/", trim($_POST["member"]["tel"])) )
			$mes .= __('Please input a phone number with a half size number.', 'usces') . "<br />";

		$mes = apply_filters('usces_filter_admin_member_check', $mes);

		return $mes;
	}

	function customer_check() {
		$mes = '';
		if ( !is_email($_POST['customer']['mailaddress1']) || WCUtils::is_blank($_POST['customer']['mailaddress1']) || WCUtils::is_blank($_POST['customer']['mailaddress2']) || trim($_POST['customer']['mailaddress1']) != trim($_POST['customer']['mailaddress2']) )
			$mes .= __('e-mail address is not correct', 'usces') . "<br />";
		if ( WCUtils::is_blank($_POST["customer"]["name1"]) )
			$mes .= __('Name is not correct', 'usces') . "<br />";//20111116ysk 0000299
//		if ( trim($_POST["customer"]["name3"]) == "" && USCES_JP )
//			$mes .= __('Invalid CANNAT pretend.', 'usces') . "<br />";
		if ( WCUtils::is_blank($_POST["customer"]["zipcode"]) )
			$mes .= __('postal code is not correct', 'usces') . "<br />";
		if ( $_POST["customer"]["pref"] == __('-- Select --', 'usces') || $_POST["customer"]["pref"] == '-- Select --' )
			$mes .= __('enter the prefecture', 'usces') . "<br />";
		if ( WCUtils::is_blank($_POST["customer"]["address1"]) )
			$mes .= __('enter the city name', 'usces') . "<br />";
		if ( WCUtils::is_blank($_POST["customer"]["address2"]) )
			$mes .= __('enter house numbers', 'usces') . "<br />";
		if ( WCUtils::is_blank($_POST["customer"]["tel"]) )
			$mes .= __('enter phone numbers', 'usces') . "<br />";
		if( !WCUtils::is_blank($_POST['customer']["tel"]) && preg_match("/[^\d-]/", trim($_POST["customer"]["tel"])) )
			$mes .= __('Please input a phone number with a half size number.', 'usces') . "<br />";
	
		$mes = apply_filters('usces_filter_customer_check', $mes);

		return $mes;
	}

	function admin_new_member_check() {
		global $wpdb;

		$member_pass_rule_min = $this->options['system']['member_pass_rule_min'];
		$member_pass_rule_max = $this->options['system']['member_pass_rule_max'];
		$mes = '';

		if ( !WCUtils::is_blank( $_POST['member']['password'] ) ){
			if( !empty( $member_pass_rule_max ) ){
				if( $member_pass_rule_min > strlen( trim($_POST['member']['password']) ) || strlen( trim($_POST['member']['password']) ) > $member_pass_rule_max ){
					$mes .= sprintf(__('Please enter %2$s characters a minimum of %1$s characters and a maximum password.', 'usces'), $member_pass_rule_min, $member_pass_rule_max ) . "<br />";
				}
			}else{
				if( $member_pass_rule_min > strlen( trim($_POST['member']['password']) ) ){
					$mes .= sprintf(__('Please enter at least %s characters password.', 'usces'), $member_pass_rule_min) . "<br />";
				}
			}
		}
		if ( !is_email( trim($_POST['member']["email"]) ) ){
			$mes .= __('e-mail address is not correct', 'usces') . "<br />";
		}else{
			$member_table = $wpdb->prefix . "usces_member";
			$mem_email = $wpdb->get_var( $wpdb->prepare("SELECT mem_email FROM $member_table WHERE ID = %d LIMIT 1", trim($_POST['member_id'])) );
			if( trim($_POST['member']["email"]) != $mem_email ){
				$mem_ID = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $member_table WHERE mem_email = %s LIMIT 1", trim($_POST['member']["email"])) );
				if( !empty($mem_ID) )
					$mes .= __('This e-mail address has been already registered.', 'usces') . "<br />";
			}
		}
		if ( WCUtils::is_blank($_POST['member']["name1"]) )
			$mes .= __('Name is not correct', 'usces') . "<br />";
//		if ( trim($_POST["mem_name3"]) == "" && USCES_JP )
//			$mes .= __('Invalid CANNAT pretend.', 'usces') . "<br />";
//		if ( trim($_POST['member']["zipcode"]) == "" )
//			$mes .= __('postal code is not correct', 'usces') . "<br />";
//		if ( $_POST['member']["pref"] == __('-- Select --', 'usces') )
//			$mes .= __('enter the prefecture', 'usces') . "<br />";
//		if ( trim($_POST['member']["address1"]) == "" )
//			$mes .= __('enter the city name', 'usces') . "<br />";
//		if ( trim($_POST['member']["address2"]) == "" )
//			$mes .= __('enter house numbers', 'usces') . "<br />";
//		if ( trim($_POST['member']["tel"]) == "" )
//			$mes .= __('enter phone numbers', 'usces') . "<br />";
		if( !WCUtils::is_blank($_POST['member']["tel"]) && preg_match("/[^\d-]/", trim($_POST["member"]["tel"])) )
			$mes .= __('Please input a phone number with a half size number.', 'usces') . "<br />";

		$mes = apply_filters('usces_filter_admin_member_check', $mes);

		return $mes;
	}

	function delivery_check() {
		$mes = '';
		if ( isset($_POST['delivery']['delivery_flag']) && $_POST['delivery']['delivery_flag'] == 1 ) {
			if ( WCUtils::is_blank($_POST["delivery"]["name1"]) )
				$mes .= __('Name is not correct', 'usces') . "<br />";//20111116ysk 0000299
//			if ( trim($_POST["delivery"]["name3"]) == "" && USCES_JP )
//				$mes .= __('Invalid CANNAT pretend.', 'usces') . "<br />";
			if ( WCUtils::is_blank($_POST["delivery"]["zipcode"]) )
				$mes .= __('postal code is not correct', 'usces') . "<br />";
			if ( $_POST["delivery"]["pref"] == __('-- Select --', 'usces') || $_POST["delivery"]["pref"] == '-- Select --' )
				$mes .= __('enter the prefecture', 'usces') . "<br />";
			if ( WCUtils::is_blank($_POST["delivery"]["address1"]) )
				$mes .= __('enter the city name', 'usces') . "<br />";
			if ( WCUtils::is_blank($_POST["delivery"]["address2"]) )
				$mes .= __('enter house numbers', 'usces') . "<br />";
			if ( WCUtils::is_blank($_POST["delivery"]["tel"]) )
				$mes .= __('enter phone numbers', 'usces') . "<br />";
		}
		if ( !isset($_POST['offer']['delivery_method']) || (empty($_POST['offer']['delivery_method']) && !WCUtils::is_zero($_POST['offer']['delivery_method'])) )
			$mes .= __('chose one from delivery method.', 'usces') . "<br />";
		if ( !isset($_POST['offer']['payment_name']) ){
			$mes .= __('chose one from payment options.', 'usces') . "<br />";
		}else{
			$payments = $this->getPayments($_POST['offer']['payment_name']);
			if('COD' == $payments['settlement']){
				$total_items_price = $this->get_total_price();
				$usces_entries = $this->cart->get_entry();
				$materials = array(
					'total_items_price' => $usces_entries['order']['total_items_price'],
					'discount' => $usces_entries['order']['discount'],
					'shipping_charge' => $usces_entries['order']['shipping_charge'],
					'cod_fee' => $usces_entries['order']['cod_fee'],
				);
				$tax = $this->getTax( $total_items_price, $materials );
				$total_items_price = $total_items_price + $tax;
				$cod_limit_amount = ( isset($this->options['cod_limit_amount']) && 0 < (int)$this->options['cod_limit_amount'] ) ? $this->options['cod_limit_amount'] : 0;
				if( 0 < $cod_limit_amount && $total_items_price > $cod_limit_amount )
					$mes .= sprintf(__('A total products amount of money surpasses the upper limit(%s) that I can purchase in C.O.D.', 'usces'), usces_crform($this->options['cod_limit_amount'], true, false, 'return')) . "<br />";
			}
		}
//20101119ysk start
		if(isset($_POST['offer']['delivery_method']) and isset($_POST['offer']['payment_name'])) {
			$d_method_index = $this->get_delivery_method_index((int)$_POST['offer']['delivery_method']);
			if($this->options['delivery_method'][$d_method_index]['nocod'] == 1) {
				$payments = $this->getPayments($_POST['offer']['payment_name']);
				if('COD' == $payments['settlement'])
					$mes .= __('COD is not available.', 'usces') . "<br />";
			}
		}
//20101119ysk end
//20110317ysk start
		if(isset($_POST['offer']['delivery_method'])) {
			$d_method_index = $this->get_delivery_method_index((int)$_POST['offer']['delivery_method']);
			$country = $_SESSION['usces_entry']["delivery"]["country"];
			$local_country = usces_get_base_country();
			if($country == $local_country) {
				if($this->options['delivery_method'][$d_method_index]['intl'] == 1) {
					$mes .= __('Delivery method is incorrect. Can not specify an international flight.', 'usces') . "<br />";
				}
			} else {
				if( WCUtils::is_zero($this->options['delivery_method'][$d_method_index]['intl']) ) {
					$mes .= __('Delivery method is incorrect. Specify the international flights.', 'usces') . "<br />";
				}
			}
		}
//20110317ysk end
	
		$mes = apply_filters('usces_filter_delivery_check', $mes);

		return $mes;
	}

	function point_check( $entries ) {
		$member = $this->get_member();
		$this->set_cart_fees( $member, $entries );
		$mes = '';
		if( isset($_POST['offer']["usedpoint"]) ) {
			if ( WCUtils::is_blank($_POST['offer']["usedpoint"]) || !preg_match("/^[0-9]+$/", $_POST['offer']["usedpoint"]) || (int)$_POST['offer']["usedpoint"] < 0 ) {
				$mes .= __('Invalid value. Please enter in the numbers.', 'usces') . "<br />";
			} else {
/*				if ( trim($_POST['offer']["usedpoint"]) > $member['point'] ){
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
*/				if( trim($_POST['offer']["usedpoint"]) > $member['point'] ) {
					$mes .= __('You have exceeded the maximum available.', 'usces')."max".$member['point']."pt<br />";
				} elseif( $this->options['point_coverage'] && trim($_POST['offer']["usedpoint"]) > ($entries['order']['total_items_price'] + $entries['order']['discount'] + $entries['order']['shipping_charge'] + $entries['order']['cod_fee']) ) {
					$mes .= __('You have exceeded the maximum available.', 'usces')."max".($entries['order']['total_items_price'] + $entries['order']['discount'] + $entries['order']['shipping_charge'] + $entries['order']['cod_fee'])."pt<br />";
				} elseif( !$this->options['point_coverage'] && trim($_POST['offer']["usedpoint"]) > ($entries['order']['total_items_price'] + $entries['order']['discount']) ) {
					$mes .= __('You have exceeded the maximum available.', 'usces')."max".($entries['order']['total_items_price'] + $entries['order']['discount'])."pt<br />";
				}
				$mes = apply_filters( 'usces_filter_point_check', $mes );
				if( '' != $mes ) {
					$_POST['offer']["usedpoint"] = 0;
					$array = array(
						'usedpoint' => 0
					);
					$this->cart->set_order_entry( $array );
				}
			}
		}
		$mes = apply_filters('usces_filter_point_check_last', $mes);
		return $mes;
	}

	function lostpass_mailaddcheck() {
		$mes = '';
		if ( !is_email($_POST['loginmail']) || WCUtils::is_blank($_POST['loginmail']) ) {
			$mes .= __('e-mail address is not correct', 'usces') . "<br />";
		}elseif( !$this->is_member($_POST['loginmail']) ){
			$mes .= __('It is the e-mail address that there is not.', 'usces') . "<br />";
		}

		return $mes;
	}

	function changepass_check() {
		$mes = '';
		if ( WCUtils::is_blank($_POST['loginpass1']) || WCUtils::is_blank($_POST['loginpass2']) || (trim($_POST['loginpass1']) != trim($_POST['loginpass2'])))
			$mes .= __('Password is not correct.', 'usces') . "<br />";

		return $mes;
	}

	function get_page() {
		return $this->page;
	}
	
	function check_display_mode() {
		$options = get_option('usces');
		if( isset($options['display_mode']) && $options['display_mode'] == 'Maintenancemode' ) return;
		
		$start['hour'] = empty($options['campaign_schedule']['start']['hour']) ? 0 : $options['campaign_schedule']['start']['hour'];
		$start['min'] = empty($options['campaign_schedule']['start']['min']) ? 0 : $options['campaign_schedule']['start']['min'];
		$start['month'] = empty($options['campaign_schedule']['start']['month']) ? 0 : $options['campaign_schedule']['start']['month'];
		$start['day'] = empty($options['campaign_schedule']['start']['day']) ? 0 : $options['campaign_schedule']['start']['day'];
		$start['year'] = empty($options['campaign_schedule']['start']['year']) ? 0 : $options['campaign_schedule']['start']['year'];
		$end['hour'] = empty($options['campaign_schedule']['end']['hour']) ? 0 : $options['campaign_schedule']['end']['hour'];
		$end['min'] = empty($options['campaign_schedule']['end']['min']) ? 0 : $options['campaign_schedule']['end']['min'];
		$end['month'] = empty($options['campaign_schedule']['end']['month']) ? 0 : $options['campaign_schedule']['end']['month'];
		$end['day'] = empty($options['campaign_schedule']['end']['day']) ? 0 : $options['campaign_schedule']['end']['day'];
		$end['year'] = empty($options['campaign_schedule']['end']['year']) ? 0 : $options['campaign_schedule']['end']['year'];
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
		//list($year, $mon, $mday) = getBeforeMonth($datenow['year'], $datenow['mon'], $datenow['mday'], 1);
		list($year, $mon, $mday) = getBeforeMonth($datenow['year'], $datenow['mon'], 1, 1);
		
		if(isset($options['business_days'][$year][$mon][1]))
			unset($options['business_days'][$year][$mon]);
		
		for($i=0; $i<12; $i++){
			//list($year, $mon, $mday) = getAfterMonth($datenow['year'], $datenow['mon'], $datenow['mday'], $i);
			list($year, $mon, $mday) = getAfterMonth($datenow['year'], $datenow['mon'], 1, $i);
			$last = getLastDay($year, $mon);
			for($j=1; $j<=$last; $j++){
				if(!isset($options['business_days'][$year][$mon][$j]))
					$options['business_days'][$year][$mon][$j] = 1;
			}
		}
		update_option('usces', $options);
		$this->options = get_option('usces');
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

	function set_initial() {
		
		$this->set_default_theme();
		$this->set_default_page();
		$this->set_default_categories();
		$this->create_table();
		$this->update_table();
		$rets07 = usces_upgrade_07();
		$rets11 = usces_upgrade_11();
		$rets14 = usces_upgrade_14();
		$rets141 = usces_upgrade_141();
		$rets143 = usces_upgrade_143();
		$this->update_options();//20120710ysk 0000472
		usces_schedule_event();
	}

	function deactivate() {
		wp_clear_scheduled_hook('wc_cron');
	}
	
	function create_table() {
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
		$ordercart_table = $wpdb->prefix . "usces_ordercart";
		$ordercart_meta_table = $wpdb->prefix . "usces_ordercart_meta";
		$log_table = $wpdb->prefix . "usces_log";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		if($wpdb->get_var("show tables like '$access_table'") != $access_table) {
		
			$sql = "CREATE TABLE " . $access_table . " (
				ID BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				acc_key VARCHAR( 50 ) NOT NULL ,
				acc_type VARCHAR( 50 ) NULL ,
				acc_value LONGTEXT NULL ,
				acc_date DATE NOT NULL DEFAULT '0000-00-00',
				acc_num1 INT( 11 ) NOT NULL DEFAULT 0,
				acc_num2 INT( 11 ) NOT NULL DEFAULT 0,
				acc_str1 VARCHAR( 200 ) NULL ,
				acc_str2 VARCHAR( 200 ) NULL ,
				KEY acc_key ( acc_key ),  
				KEY acc_type ( acc_type ),  
				KEY acc_date ( acc_date ), 
				KEY acc_num1 ( acc_num1 ), 
				KEY acc_num2 ( acc_num2 )  
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
				order_check TEXT NULL ,
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

		if($wpdb->get_var("show tables like '$ordercart_table'") != $ordercart_table) {
		
			$sql = "CREATE TABLE " . $ordercart_table . " (
				`cart_id` bigint( 20  )  unsigned NOT  NULL  AUTO_INCREMENT ,
				`order_id` bigint( 20  )  NOT  NULL ,
				`group_id` int( 3  )  NOT  NULL DEFAULT  '0',
				`row_index` int( 3  )  NOT  NULL ,
				`post_id` bigint( 20  )  NOT  NULL ,
				`item_code` varchar( 100  )  NOT  NULL ,
				`item_name` varchar( 250  )  NOT  NULL ,
				`cprice` decimal( 12, 0  )  DEFAULT NULL ,
				`sku_code` varchar( 100  )  NOT  NULL ,
				`sku_name` varchar( 250  )  DEFAULT NULL ,
				`price` decimal( 12, 0  )  NOT  NULL ,
				`quantity` float NOT  NULL ,
				`unit` varchar( 50  )  DEFAULT NULL ,
				`tax` decimal( 10, 0  )  DEFAULT NULL ,
				`destination_id` int( 10  )  DEFAULT NULL ,
				`cart_serial` text,
				PRIMARY  KEY (  `cart_id`  ) ,
				UNIQUE  KEY  `row` (  `row_index` ,  `destination_id` ,  `order_id`  ) ,
				KEY  `order_id` (  `order_id`  ) ,
				KEY  `post_id` (  `post_id`  ) ,
				KEY  `item_code` (  `item_code`  ) ,
				KEY  `item_name` (  `item_name`  ) ,
				KEY  `sku_code` (  `sku_code`  ) ,
				KEY  `sku_name` (  `sku_name`  ) 
				) ENGINE = MYISAM AUTO_INCREMENT=1000 $charset_collate;";
		
			dbDelta($sql);
			add_option("usces_db_ordercart", USCES_DB_ORDERCART);
		}
		if($wpdb->get_var("show tables like '$ordercart_meta_table'") != $ordercart_meta_table) {
		
			$sql = "CREATE TABLE " . $ordercart_meta_table . " (
				`cartmeta_id` bigint( 20  )  NOT  NULL  AUTO_INCREMENT ,
				`cart_id` bigint( 20  )  NOT  NULL DEFAULT  '0',
				`meta_type` varchar( 100  )  NOT  NULL ,
				`meta_key` varchar( 255  )  DEFAULT NULL ,
				`meta_value` longtext,
				PRIMARY  KEY (  `cartmeta_id`  ) ,
				KEY  `cart_id` (  `cart_id`  ) ,
				KEY  `meta_key` (  `meta_key`  ) 
				) ENGINE = MYISAM $charset_collate;";
		
			dbDelta($sql);
			add_option("usces_db_ordercart_meta", USCES_DB_ORDERCART_META);
		}
		if($wpdb->get_var("show tables like '$log_table'") != $log_table) {
		
			$sql = "CREATE TABLE " . $log_table . " (
				`ID` bigint( 20  )  NOT  NULL  AUTO_INCREMENT ,
				`datetime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				`log`  LONGTEXT NULL ,
				PRIMARY  KEY (  `ID`  ) ,
				KEY  `datetime` (  `datetime`  )  
				) ENGINE = MYISAM $charset_collate;";
		
			dbDelta($sql);
			add_option("usces_db_log", USCES_DB_LOG);
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
		$ordercart_table = $wpdb->prefix . "usces_ordercart";
		$ordercart_meta_table = $wpdb->prefix . "usces_ordercart_meta";
		$log_table = $wpdb->prefix . "usces_log";
		
		$access_ver = get_option( "usces_db_access" );
		$member_ver = get_option( "usces_db_member" );
		$member_meta_ver = get_option( "usces_db_member_meta" );
		$order_ver = get_option( "usces_db_order" );
		$order_meta_ver = get_option( "usces_db_order_meta" );
		$ordercart_ver = get_option( "usces_db_ordercart" );
		$ordercart_meta_ver = get_option( "usces_db_ordercart_meta" );
		$log_ver = get_option( "usces_db_log" );
		
		if( $access_ver != USCES_DB_ACCESS ) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			$sql = "CREATE TABLE " . $access_table . " (
				ID BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				acc_key VARCHAR( 50 ) NOT NULL ,
				acc_type VARCHAR( 50 ) NULL ,
				acc_value LONGTEXT NULL ,
				acc_date DATE NOT NULL DEFAULT '0000-00-00',
				acc_num1 INT( 11 ) NOT NULL DEFAULT 0,
				acc_num2 INT( 11 ) NOT NULL DEFAULT 0,
				acc_str1 VARCHAR( 200 ) NULL ,
				acc_str2 VARCHAR( 200 ) NULL ,
				KEY acc_key ( acc_key ),  
				KEY acc_type ( acc_type ),  
				KEY acc_date ( acc_date ), 
				KEY acc_num1 ( acc_num1 ), 
				KEY acc_num2 ( acc_num2 )  
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
				order_check TEXT NULL ,
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
		if( $ordercart_ver != USCES_DB_ORDERCART ) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			$sql = "CREATE TABLE " . $ordercart_table . " (
				`cart_id` bigint( 20  )  unsigned NOT  NULL  AUTO_INCREMENT ,
				`order_id` bigint( 20  )  NOT  NULL ,
				`group_id` int( 3  )  NOT  NULL DEFAULT  '0',
				`row_index` int( 3  )  NOT  NULL ,
				`post_id` bigint( 20  )  NOT  NULL ,
				`item_code` varchar( 100  )  NOT  NULL ,
				`item_name` varchar( 250  )  NOT  NULL ,
				`cprice` decimal( 12, 0  )  DEFAULT NULL ,
				`sku_code` varchar( 100  )  NOT  NULL ,
				`sku_name` varchar( 250  )  DEFAULT NULL ,
				`price` decimal( 12, 0  )  NOT  NULL ,
				`quantity` float NOT  NULL ,
				`unit` varchar( 50  )  DEFAULT NULL ,
				`tax` decimal( 10, 0  )  DEFAULT NULL ,
				`destination_id` int( 10  )  DEFAULT NULL ,
				`cart_serial` text,
				PRIMARY  KEY (  `cart_id`  ) ,
				UNIQUE  KEY  `row` (  `row_index` ,  `destination_id` ,  `order_id`  ) ,
				KEY  `order_id` (  `order_id`  ) ,
				KEY  `post_id` (  `post_id`  ) ,
				KEY  `item_code` (  `item_code`  ) ,
				KEY  `item_name` (  `item_name`  ) ,
				KEY  `sku_code` (  `sku_code`  ) ,
				KEY  `sku_name` (  `sku_name`  ) 
				) ENGINE = MYISAM;";
		
			dbDelta($sql);
			update_option("usces_db_ordercart", USCES_DB_ORDERCART);
		}
		if( $ordercart_meta_ver != USCES_DB_ORDERCART_META ) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			$sql = "CREATE TABLE " . $ordercart_meta_table . " (
				`cartmeta_id` bigint( 20  )  NOT  NULL  AUTO_INCREMENT ,
				`cart_id` bigint( 20  )  NOT  NULL DEFAULT  '0',
				`meta_type` varchar( 100  )  NOT  NULL ,
				`meta_key` varchar( 255  )  DEFAULT NULL ,
				`meta_value` longtext,
				PRIMARY  KEY (  `cartmeta_id`  ) ,
				KEY  `cart_id` (  `cart_id`  ) ,
				KEY  `meta_key` (  `meta_key`  ) 
				) ENGINE = MYISAM;";
		
			dbDelta($sql);
			update_option("usces_db_ordercart_meta", USCES_DB_ORDERCART_META);
		}
		if( $log_ver != USCES_DB_LOG ) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			$sql = "CREATE TABLE " . $log_table . " (
				`ID` bigint( 20  )  NOT  NULL  AUTO_INCREMENT ,
				`datetime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				`log`  LONGTEXT NULL ,
				PRIMARY  KEY (  `ID`  ) ,
				KEY  `datetime` (  `datetime`  )  
				) ENGINE = MYISAM;";
		
			dbDelta($sql);
			update_option("usces_db_log", USCES_DB_LOG);
		}
	}
	
	function set_default_theme()
	{
		$themepath = USCES_WP_CONTENT_DIR.'/themes/welcart_default';
		$resourcepath = USCES_WP_CONTENT_DIR.'/plugins/usc-e-shop/theme/welcart_default';
		if( file_exists($themepath) ) return false;
		if(!file_exists($resourcepath) ) return false;
		
		mkdir($themepath, 0775);
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
		
		$idObj = get_category_by_slug('item'); 
		if( empty($idObj) ) {
			$item_cat = array('cat_name' => __('Items', 'usces'), 'category_description' => '', 'category_nicename' => 'item', 'category_parent' => 0);
			$item_cat_id = wp_insert_category($item_cat);	
			update_option('usces_item_cat_parent_id', $item_cat_id);	
		}

		$idObj = get_category_by_slug('itemreco'); 
		if( empty($idObj) && isset($item_cat_id) ) {
			$itemreco_cat = array('cat_name' => __('Items recommended', 'usces'), 'category_description' => '', 'category_nicename' => 'itemreco', 'category_parent' => $item_cat_id);
			$itemreco_cat_id = wp_insert_category($itemreco_cat);	
		}

		$idObj = get_category_by_slug('itemnew'); 
		if( empty($idObj) && isset($item_cat_id) ) {
			$itemnew_cat = array('cat_name' => __('New items', 'usces'), 'category_description' => '', 'category_nicename' => 'itemnew', 'category_parent' => $item_cat_id);
			$itemnew_cat_id = wp_insert_category($itemnew_cat);	
		}

		$idObj = get_category_by_slug('itemgenre'); 
		if( empty($idObj) && isset($item_cat_id) ) {
			$itemgenre_cat = array('cat_name' => __('Item genre', 'usces'), 'category_description' => '', 'category_nicename' => 'itemgenre', 'category_parent' => $item_cat_id);
			$itemgenre_cat_id = wp_insert_category($itemgenre_cat);	
		}
	}
//20120710ysk start 0000472
	function update_options() {
		$target_market = $this->options['system']['target_market'];

		$update_shipping_charge = false;
		$shipping_charge = isset($this->options['shipping_charge']) ? $this->options['shipping_charge'] : array();
		foreach( (array)$target_market as $tm ) {
			for( $i = 0; $i < count($shipping_charge); $i++ ) {
				if( isset($shipping_charge[$i]['country']) and $shipping_charge[$i]['country'] == $tm ) {
					foreach( $shipping_charge[$i]['value'] as $pref => $value ) {
						$shipping_charge[$i][$tm][$pref] = $value;
					}
					unset($shipping_charge[$i]['country']);
					unset($shipping_charge[$i]['value']);
					$update_shipping_charge = true;
				}
			}
		}
		if( $update_shipping_charge ) $this->options['shipping_charge'] = $shipping_charge;

		$update_delivery_days = false;
		$delivery_days = isset($this->options['delivery_days']) ? $this->options['delivery_days'] : array();
		foreach( (array)$target_market as $tm ) {
			for( $i = 0; $i < count($delivery_days); $i++ ) {
				if( isset($delivery_days[$i]['country']) and $delivery_days[$i]['country'] == $tm ) {
					foreach( $delivery_days[$i]['value'] as $pref => $value ) {
						$delivery_days[$i][$tm][$pref] = $value;
					}
					unset($delivery_days[$i]['country']);
					unset($delivery_days[$i]['value']);
					$update_delivery_days = true;
				}
			}
		}
		if( $update_delivery_days ) $this->options['delivery_days'] = $delivery_days;

//20140131ysk start
		$update_acting_settings_sbps = false;
		if( isset($this->options['acting_settings']['sbps']['card_activate']) and 'on' == $this->options['acting_settings']['sbps']['card_activate'] ) {
			if( empty($this->options['acting_settings']['sbps']['send_url']) or 'https://fep.sps-system.com/f01/FepBuyInfoReceive.do' != $this->options['acting_settings']['sbps']['send_url'] ) {
				$this->options['acting_settings']['sbps']['send_url'] = 'https://fep.sps-system.com/f01/FepBuyInfoReceive.do';
				$update_acting_settings_sbps = true;
			}
		}

		$update_acting_settings_paydesign = false;
		if( isset($this->options['acting_settings']['digitalcheck']['card_activate']) and 'on' == $this->options['acting_settings']['digitalcheck']['card_activate'] ) {
			$pos = strpos( $this->options['acting_settings']['digitalcheck']['send_url_card'], 'paydesign' );
			if( $pos === false ) {
				$this->options['acting_settings']['digitalcheck']['send_url_card'] = "https://www.paydesign.jp/settle/settle3/bp3.dll";
				$this->payment_structure['acting_digitalcheck_card'] = 'カード決済（ペイデザイン）';
				$update_acting_settings_paydesign = true;
			}
			if( isset($this->options['acting_settings']['digitalcheck']['card_user_id']) and 'on' == $this->options['acting_settings']['digitalcheck']['card_user_id'] ) {
				$pos = strpos( $this->options['acting_settings']['digitalcheck']['send_url_user_id'], 'paydesign' );
				if( $pos === false ) {
					$this->options['acting_settings']['digitalcheck']['send_url_user_id'] = "https://www.paydesign.jp/settle/settlex/credit2.dll";
					$update_acting_settings_paydesign = true;
				}
			}
		}
		if( isset($this->options['acting_settings']['digitalcheck']['conv_activate']) and 'on' == $this->options['acting_settings']['digitalcheck']['conv_activate'] ) {
			$pos = strpos( $this->options['acting_settings']['digitalcheck']['send_url_conv'], 'paydesign' );
			if( $pos === false ) {
				$this->options['acting_settings']['digitalcheck']['send_url_conv'] = "https://www.paydesign.jp/settle/settle3/bp3.dll";
				$this->payment_structure['acting_digitalcheck_conv'] = 'コンビニ決済（ペイデザイン）';
				$update_acting_settings_paydesign = true;
			}
		}

		if( $update_shipping_charge or $update_delivery_days or $update_acting_settings_sbps or $update_acting_settings_paydesign ) 
			update_option( 'usces', $this->options );

		if( $update_acting_settings_paydesign ) 
			update_option( 'usces_payment_structure', $this->payment_structure );
//20140131ysk end
	}
//20120710ysk end
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
		if( WCUtils::is_blank($str) ) return;
		
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
		$tax_rate = (int)$this->options['tax_rate'];
		
		if( isset($this->options['tax_mode']) ){
			if ( 'exclude' == $this->options['tax_mode'] )
				$str = '<em class="tax">'.__('(Excl. Tax)', 'usces').'</em>';
			else
				$str = '<em class="tax">'.__('(Incl. Tax)', 'usces').'</em>';
		}else{
			if ( 0 < $tax_rate )
				$str = '<em class="tax">'.__('(Excl. Tax)', 'usces').'</em>';
			else
				$str = '<em class="tax">'.__('(Incl. Tax)', 'usces').'</em>';
		}
			
		return apply_filters('usces_filter_tax_guid', $str, $tax_rate);
	}

	function getItemCode($post_id) {
		$str = get_post_meta($post_id, '_itemCode', true);
		return $str;
	}
	
	function getItemName($post_id) {
		$str = get_post_meta($post_id, '_itemName', true);
		return $str;
	}
	
	function getItemRestriction($post_id) {
		$str = get_post_meta($post_id, '_itemRestriction', true);
		return $str;
	}
	
	function getItemPointrate($post_id) {
		$str = get_post_meta($post_id, '_itemPointrate', true);
		return $str;
	}
	
	function getItemShipping($post_id) {
		$str = get_post_meta($post_id, '_itemShipping', true);
		return $str;
	}
	
	function getItemShippingCharge($post_id) {
		$str = get_post_meta($post_id, '_itemShippingCharge', true);
		return (float)$str;
	}
	
	function getItemDeliveryMethod($post_id) {
		$str = get_post_meta($post_id, '_itemDeliveryMethod', true);
		if( empty($str) )
			return array();
		else
			return $str;
	}
	
	function getItemIndividualSCharge($post_id) {
		$str = get_post_meta($post_id, '_itemIndividualSCharge', true);
		return $str;
	}
	
	function getItemGpNum1($post_id) {
		$str = get_post_meta($post_id, '_itemGpNum1', true);
		return $str;
	}
	
	function getItemGpNum2($post_id) {
		$str = get_post_meta($post_id, '_itemGpNum2', true);
		return $str;
	}
	
	function getItemGpNum3($post_id) {
		$str = get_post_meta($post_id, '_itemGpNum3', true);
		return $str;
	}
	
	function getItemGpDis1($post_id) {
		$str = get_post_meta($post_id, '_itemGpDis1', true);
		return $str;
	}
	
	function getItemGpDis2($post_id) {
		$str = get_post_meta($post_id, '_itemGpDis2', true);
		return $str;
	}
	
	function getItemGpDis3($post_id) {
		$str = get_post_meta($post_id, '_itemGpDis3', true);
		return $str;
	}
	
	function getItemSku($post_id, $index = '') {
		$array = array();
		$skus = $this->get_skus($post_id, 'sort');
		foreach((array)$skus as $sku){
			$array[] = $sku['code'];
		}
		if(!$array) return false;
		if($index == ''){
			return $array;
		}else if(isset($array[$index])){
			return $array[$index];
		}else{
			return false;
		}
	}
	
	function getItemPrice($post_id, $skukey = '') {
		$array = array();
		$skus = $this->get_skus($post_id, 'code');
		foreach((array)$skus as $key => $sku){
			$array[$key] = (float)str_replace('.', '', $sku['price']);
		}
		if(!$array) return false;
		if($skukey == ''){
			return $array;
		}else if(isset($array[$skukey])){
			return $array[$skukey];
		}else{
			return false;
		}
	}
	
	function getItemDiscount($post_id, $skukey = '') {
		$display_mode = $this->options['display_mode'];
		$array = array();
		$skus = $this->get_skus($post_id, 'code');
		foreach((array)$skus as $key => $sku){
			$price = (float)str_replace(',', '', $sku['price']);
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
			$array[$key] = $discount;
		}
		if(!$array) return false;
		if($skukey == ''){
			return $array;
		}else if(isset($array[$skukey])){
			return $array[$skukey];
		}else{
			return false;
		}
	}
	
	function getItemZaiko($post_id, $skukey = '') {
		$array = array();
		$skus = $this->get_skus($post_id, 'code');
		foreach((array)$skus as $key => $sku){
			$num = $sku['stock'];
			$array[$key] = $this->zaiko_status[$num];
		}
		if(!$array) return false;
		if($skukey == ''){
			return $array;
		}else if(isset($array[$skukey])){
			return $array[$skukey];
		}else{
			return false;
		}
	}
	
	function getItemZaikoStatusId($post_id, $skukey = '') {
		$array = array();
		$skus = $this->get_skus($post_id, 'code');
		foreach((array)$skus as $key => $sku){
			$num = $sku['stock'];
			$array[$key] = $num;
		}
		if(!$array) return false;
		if($skukey == ''){
			return $array;
		}else if(isset($array[$skukey])){
			return $array[$skukey];
		}else{
			return false;
		}
	}
	
	function updateItemZaiko($post_id, $skucode, $value) {
		$res = usces_update_sku( $post_id, $skucode, 'stock', $value );
		if( !$res ){
			return false;
		}else{
			return true;
		}
	}
	
	function getItemZaikoNum($post_id, $skukey = '') {
		$array = array();
		$skus = $this->get_skus($post_id, 'code');
		foreach((array)$skus as $key => $sku){
			$array[$key] = $sku['stocknum'];
		}
		if(!$array) return false;
		if($skukey == ''){
			return $array;
		}else if(isset($array[$skukey])){
			return $array[$skukey];
		}else{
			return false;
		}
	}
	
	function updateItemZaikoNum($post_id, $skucode, $value) {
		$res = usces_update_sku( $post_id, $skucode, 'stocknum', $value );
		if( !$res ){
			return false;
		}else{
			return true;
		}
	}
//20120629ysk start 0000520
	function getItemDivision( $post_id ){
		if( usces_is_item($post_id) ){
			$item_division = get_post_meta($post_id, '_item_division', true);
			$division = empty($item_division) ? 'shipped' : $item_division;
		}else{
			$division = NULL;
		}
		return $division;
	}
//20120629ysk end
	function getItemChargingType( $post_id, $cart = array() ){
		if( usces_is_item($post_id) ){
			$charging = get_post_meta($post_id, '_item_charging_type', true);
			//if( !defined('WCEX_DLSELLER') )
			if( !defined('WCEX_DLSELLER') and !defined('WCEX_AUTO_DELIVERY') )
				$charging = NULL;
		}else{
			$charging = NULL;
		}
		switch( $charging ){
			case 0:
				$type = 'once';
				break;
			case 1:
				$type = 'continue';
				break;
			case 2:
				$type = 'regular';
				if( !empty($cart) ) {
					if( empty($cart['advance']) ) {
						$type = 'once';
					} else {
						if( is_array($cart['advance']) and array_key_exists( 'regular', $cart['advance'] ) ) {
							$regular = maybe_unserialize( $cart['advance']['regular'] );
						} else {
							$advance = $this->cart->wc_unserialize( $cart['advance'] );
							$sku = urldecode( $cart['sku'] );
							$sku_encoded = $cart['sku'];
							$regular = $advance[$post_id][$sku_encoded]['regular'];
						}
						$unit = isset( $regular['unit'] ) ? $regular['unit'] : '';
						$interval = isset( $regular['interval'] ) ? (int)$regular['interval'] : 0;
						if( empty($unit) or 1 > $interval ) //通常課金扱い
							$type = 'once';
					}
				}
				break;
			default:
				$type = NULL;
		}
		return $type;
	}
	
	function getItemFrequency( $post_id ){
		$frequency = get_post_meta($post_id, '_item_frequency',true );
		return $frequency;
	}
	
	function getItemChargingDay( $post_id ){
		$array = get_post_meta($post_id, '_item_chargingday',true );
		$day = (int)$array;
		$chargingday = empty($day) ? 1 : $day;
		return $chargingday;
	}
	
	function getItemSkuDisp($post_id, $skukey = '') {
		$array = array();
		$skus = $this->get_skus($post_id, 'code');
		foreach((array)$skus as $key => $sku){
			$array[$key] = $sku['name'];
		}
		if(!$array) return false;
		if($skukey == ''){
			return $array;
		}else if(isset($array[$skukey])){
			return $array[$skukey];
		}else{
			return false;
		}
	}
	
//	function getItemSkuChargingType($post_id, $skukey = '') {
//		$fields = get_post_custom($post_id);
//		$skus = array();
//		foreach((array)$fields as $key => $value){
//			if( preg_match('/^_isku_/', $key, $match) ){
//				$key = substr($key, 6);
//				$values = maybe_unserialize($value[0]);
//				if( isset($values['charging_type']) && "undefined" != $values['charging_type'] && !empty($values['charging_type']) ){ 
//					$skus[$key] = $values['charging_type'];
//				}else{
//					continue;
//				}
//			}
//		}
//		if( empty($skus) ) return false;
//		if($skukey == ''){
//			return $skus;
//		}elseif( isset($skus[$skukey]) ){
//			return $skus[$skukey];
//		}else{
//			return false;
//		}
//	}
	
	function getItemSkuUnit($post_id, $skukey = '') {
		$array = array();
		$skus = $this->get_skus($post_id, 'code');
		foreach((array)$skus as $key => $sku){
			$array[$key] = $sku['unit'];
		}
		if(!$array) return false;
		if($skukey == ''){
			return $array;
		}else if(isset($array[$skukey])){
			return $array[$skukey];
		}else{
			return false;
		}
	}
	
	function get_item( $post_id ) {
		$usces_item['post_id'] = $post_id;
		$usces_item['itemCode'] = $this->getItemCode($post_id);
		$usces_item['itemName'] = $this->getItemName($post_id);
		
		$skus = $this->get_skus($post_id, 'code');
		foreach((array)$skus as $key => $sku){
			$usces_item['skuCodes'][] = $key;
			$usces_item['skuValues'][] = $sku;
		}
		
		$usces_item = apply_filters('usces_filter_get_item', $usces_item, $post_id);
		
		return $usces_item;
	}

	function get_itemOptionKey( $post_id, $enc = false ) {
		$opts = usces_get_opts( $post_id );
		if(empty($opts)) return;
		
		$res = array();
		foreach ( (array)$opts as $opt ) {
			if( $enc )
				$res[] = urlencode($opt['name']);
			else
				$res[] = $opt['name'];
		}
		return $res;
	}
	
	function get_itemOptions( $key, $post_id ) {
		$opts = usces_get_opts( $post_id, 'name' );
		if(empty($opts[$key]))
			return;
		else
			return $opts[$key];
	}
	
	function get_postIDbyCode( $itemcode ) {
		global $wpdb;
		
		$query = $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s LIMIT 1", '_itemCode', $itemcode);
		$res = $wpdb->get_var( $query );
		return $res;
	}

	function get_pictids($item_code) {
		global $wpdb;
		
		if( empty($item_code) )
			return false;
		
		if( !$this->options['system']['subimage_rule'] ){
			$codestr = $item_code.'%';
			$query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title LIKE %s AND post_title <> %s AND post_type = 'attachment' ORDER BY post_title", $codestr, $item_code);
		}else{
			$codestr = $item_code.'--%';
			$query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title LIKE %s AND post_type = 'attachment' ORDER BY post_title", $codestr);
		}
		$results = $wpdb->get_col( $query );
		return $results;
	}
	
	function get_mainpictid($item_code) {
		global $wpdb;
		$query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'attachment' LIMIT 1", $item_code);
		$id = $wpdb->get_var( $query );
		$id = apply_filters( 'usces_filter_get_mainpictid', $id, $item_code );
		return $id;
	}
	
	function get_skus( $post_id, $keyflag = 'sort' ) {
		if( !$post_id )
			return array();
			
		$skus = array();
		$metas = usces_get_post_meta($post_id, '_isku_');
		if( empty($metas) ) return $skus;
		
		foreach( $metas as $rows ){
			$values = unserialize($rows['meta_value']);
			$key = isset($values[$keyflag]) ? $values[$keyflag] : $values['sort'];
			$skus[$key] = array(
								'meta_id' => $rows['meta_id'],
								'code' => $values['code'],
								'name' => $values['name'],
								'cprice' => $values['cprice'],
								'price' => $values['price'],
								'unit' => $values['unit'],
								'stocknum' => $values['stocknum'],
								'stock' => $values['stock'],
								'gp' => $values['gp'],
								'sort' => $values['sort'],
								'advance' => isset($values['advance']) ? $values['advance'] : ''
							);
		}
		$skus = apply_filters( 'usces_filter_get_skus', $skus, $post_id, $keyflag );
		ksort($skus);
	
		return $skus;
	}
	
	function is_item( $post ) {
	
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
//20110621ysk start 0000184
		if(isset($_REQUEST['acting']) && ('paypal_ec' == $_REQUEST['acting'])) {
			if( !usces_paypal_doecp( $results ) )
				return 'error';
		}
//20110621ysk end
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
		global $wpdb;
		$entry = $this->cart->get_entry();
		$delim = apply_filters( 'usces_filter_delim', $this->delim );
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
			$query = $delim . ltrim($query, '&');
			header("location: " . $redirect . $query);
			exit;
			
		}else if($acting_flg == 'acting_zeus_card' && 2 == $this->options['acting_settings']['zeus']['connection'] ){
			if( 1 == $this->options['acting_settings']['zeus']['3dsecur'] ){
				if( !isset($_REQUEST['PaRes'])){
	
					usces_log('zeus card 3dsecure entry data (acting_processing) : '.print_r($entry, true), 'acting_transaction.log');
					usces_zeus_3dsecure_enrol();
					
				}else{
		
					usces_log('zeus card 3dsecure : auth', 'acting_transaction.log');
					usces_zeus_3dsecure_auth();
				}
			}else{
				usces_log('zeus card no3d entry data (acting_processing) : '.print_r($entry, true), 'acting_transaction.log');
				$res = usces_zeus_secure_payreq();
				return $res;
			}
		}else if($acting_flg == 'acting_zeus_card' && 1 == $this->options['acting_settings']['zeus']['connection'] ){
		
			$acting_opts = $this->options['acting_settings']['zeus'];
			$interface = parse_url($acting_opts['card_url']);

			usces_log('zeus card securelink entry data (acting_processing) : '.print_r($entry, true), 'acting_transaction.log');

			$vars = 'send=mall';
			$vars .= '&clientip=' . $acting_opts['clientip'];
			$vars .= '&cardnumber=' . $_POST['cardnumber'];
			if( 1 == $this->options['acting_settings']['zeus']['security'] ){
				$vars .= '&seccode=' . $_POST['securecode'];
			}
			$vars .= '&expyy=' . substr($_POST['expyy'], 2);
			$vars .= '&expmm=' . $_POST['expmm'];
			$vars .= '&telno=' . str_replace('-', '', $_POST['telno']);
			$vars .= '&email=' . $_POST['email'];
			$vars .= '&sendid=' . $_POST['sendid'];
			$vars .= '&username=' . $_POST['username'];
			$vars .= '&money=' . $_POST['money'];
			$vars .= '&sendpoint=' . $_POST['sendpoint'];
			$vars .= '&printord=' . $_POST['printord'];
			$vars .= '&return_value=yes';
			if( isset($_POST['howpay']) && WCUtils::is_zero($_POST['howpay']) ){
				$vars .= '&div=' . $_POST['div'];
			}

			//usces_log('zeus card *****vars : '.print_r($vars, true), 'acting_transaction.log');

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

			//usces_log('zeus card *****2 : '.print_r($page, true), 'acting_transaction.log');
				if( false !== strpos( $page, 'Success_order') ){
					usces_auth_order_acting_data($_POST['sendpoint']);
					usces_log('zeus card : Success_order ', 'acting_transaction.log');
					$ordd = usces_get_order_number( $page );
//20120904ysk start 0000541
					if ( !isset($_POST['cbrand']) || (isset($_POST['howpay']) && 1 == $_POST['howpay']) ) {
						$args = '';
					} else {
						$div = 'div_'.$_POST['cbrand'];
						$args = '&cbrand='.$_POST['cbrand'].'&howpay='.$_POST['howpay'].'&'.$div.'='.$_POST[$div];
					}
					$args .= '&order_number='.$ordd.'&wctid='.$_POST['sendpoint'];
					header("Location: " . USCES_CART_URL . $delim . 'acting=zeus_card&acting_return=1'.$args);
//20120904ysk end
					exit;
				}else{
					$err_code = usces_get_err_code( $page );
					usces_log('zeus card : Certification Error : '.$err_code, 'acting_transaction.log');
					header("Location: " . USCES_CART_URL . $delim . 'acting=zeus_card&acting_return=0&err_code='.substr( $err_code, -3 ) );
					exit;
				}
			}else{
				usces_log('zeus card : Socket Error', 'acting_transaction.log');
				header("Location: " . USCES_CART_URL . $delim . 'acting=zeus_card&acting_return=0');
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
			if( isset($acting_opts['conv_ope']) && 'test' == $acting_opts['conv_ope'] ) {
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
//usces_log('zeus conv header : '.print_r($header, true), 'acting_transaction.log');
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
				$qstr .= 'pay_cvs=' . $_POST['pay_cvs'].'&wctid='.$_POST['sendpoint'];
				fclose($fp);
				//usces_log('zeus page : '.$page, 'acting_transaction.log');

				if( false !== strpos( $page, 'Success_order') ){
					usces_log('zeus conv entry data (acting_processing) : '.print_r($entry, true), 'acting_transaction.log');
					header("Location: " . USCES_CART_URL . $delim . 'acting=zeus_conv&acting_return=1&' . $qstr);
					exit;
				}else{
					usces_log('zeus data NG : '.$page, 'acting_transaction.log');
					header("Location: " . USCES_CART_URL . $delim . 'acting=zeus_conv&acting_return=0');
					exit;
				}
			}else{
				usces_log('zeus : sockopen NG', 'acting_transaction.log');
				header("Location: " . USCES_CART_URL . $delim . 'acting=zeus_conv&acting_return=0');
			}
			exit;

//20110208ysk start
		}else if($acting_flg == 'acting_paypal_ec') {
			$acting_opts = $this->options['acting_settings']['paypal'];
			$currency_code = $this->get_currency_code();
			$addroverride = '1';
			if( isset( $_POST['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] ) ) {
				if( 'US' == $_POST['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] or 'CA' == $_POST['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] ) $addroverride = '0';
			} else {
				$addroverride = '0';
			}

			$nvpstr  = $query;
			$nvpstr .= '&SESSION_ID='.$currency_code;
			$nvpstr .= '&CURRENCYCODE='.$currency_code;
			$nvpstr .= '&ADDROVERRIDE='.$addroverride;
			$nvpstr .= '&PAYMENTACTION=' . apply_filters('usces_filter_paypal_ec_paymentaction', 'Sale');

			//The returnURL is the location where buyers return to when a payment has been succesfully authorized.
			$nvpstr .= '&RETURNURL='.urlencode(USCES_CART_URL.$delim.'acting=paypal_ec&acting_return=1');

			//The cancelURL is the location buyers are sent to when they hit the cancel button during authorization of payment during the PayPal flow
			$nvpstr .= '&CANCELURL='.apply_filters( 'usces_filter_paypal_ec_cancelurl', urlencode(USCES_CART_URL.$delim.'confirm=1'), $query );

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
				header("Location: ".USCES_CART_URL.$delim.'acting=paypal_ec&acting_return=0');
			}
			exit;
//20110208ysk end
//20121030ysk start
		} elseif( $acting_flg == 'acting_telecom_edy' ) {
			$table_meta_name = $wpdb->prefix."usces_order_meta";
			$value = array();
			$value['usces_cart'] = $_SESSION['usces_cart'];
			$value['usces_entry'] = $_SESSION['usces_entry'];
			$value['usces_member'] = $_SESSION['usces_member'];
			$mvalue = serialize( $value );
			$mquery = $wpdb->prepare( "INSERT INTO $table_meta_name (order_id, meta_key, meta_value) VALUES (%d, %s, %s)", $_POST['option'], $_POST['option'], $mvalue );
			$res = $wpdb->query( $mquery );

			unset( $_SESSION['usces_cart'] );
			unset( $_SESSION['usces_entry'] );

			$acting_opts = $this->options['acting_settings']['telecom'];
			header( "location: ".$acting_opts['send_url_edy'].'?acting=telecom_edy'.$query );
			exit;
//20121030ysk end
//20121206ysk start
		} else if( $acting_flg == 'acting_digitalcheck_card' ) {
			$acting_opts = $this->options['acting_settings']['digitalcheck'];
			$interface = parse_url($acting_opts['send_url_user_id']);
			$kakutei = ( empty($acting_opts['card_kakutei']) ) ? '0' : $acting_opts['card_kakutei'];

			$vars  = 'IP='.$acting_opts['card_ip'];
			$vars .= '&PASS='.$acting_opts['card_pass'];
			$vars .= '&IP_USER_ID='.$_POST['IP_USER_ID'];
			$vars .= '&SID='.$_POST['SID'];
			$vars .= '&STORE=51';
			$vars .= '&N1='.$_POST['N1'];
			$vars .= '&K1='.$_POST['K1'];
			$vars .= '&KAKUTEI='.$kakutei;
			$vars .= '&FUKA='.$acting_flg;

			$header  = "POST ".$interface['path']." HTTP/1.1\r\n";
			$header .= "Host: ".$_SERVER['HTTP_HOST']."\r\n";
			$header .= "User-Agent: PHP Script\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: ".strlen($vars)."\r\n";
			$header .= "Connection: close\r\n\r\n";
			$header .= $vars;
			$fp = fsockopen('ssl://'.$interface['host'],443,$errno,$errstr,30);

			if( $fp ) {
				fwrite( $fp, $header );
				$page = '';
				while( !feof($fp) ) {
					$line = fgets( $fp, 1024 );
					if( strcmp($line, "\r\n") == 0 ) {
						$headerdone = true;
					} elseif( $headerdone ) {
						$page .= $line;
					}
				}
				fclose($fp);
				$lines = explode("\n", $page);
				if( false !== strpos( $lines[0], 'OK') ) {
					usces_log('digitalcheck card entry data (acting_processing) : '.print_r($entry, true), 'acting_transaction.log');
					//$args = '&SID='.$lines[1];
					$args = '&SID='.$_POST['SID'].'&FUKA='.$acting_flg;
					header( "location: ".USCES_CART_URL.$delim.'acting=digitalcheck_card&acting_return=1'.$args );
				} else {
					usces_log('digitalcheck card : Certification Error : '.$page, 'acting_transaction.log');
					header( "location: ".USCES_CART_URL.$delim.'acting=digitalcheck_card&acting_return=0' );
				}
			} else {
				usces_log('digitalcheck card : Socket Error', 'acting_transaction.log');
				header( "location: ".USCES_CART_URL.$delim.'acting=digitalcheck_card&acting_return=0' );
			}
			exit;
		} else if( $acting_flg == 'acting_digitalcheck_conv' ) {
			if( isset($_REQUEST['STORE']) and '99' != $_REQUEST['STORE'] ) {
				$res = $this->order_processing();
				if( 'ordercompletion' == $res ) {
					$table_meta_name = $wpdb->prefix."usces_order_meta";
					$mquery = $wpdb->prepare("SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'SID', $_REQUEST['SID'] );
					$order_id = $wpdb->get_var($mquery);
					if( $order_id ) {
						$data = array( "settltment_status" => __("Failure",'usces'), "settltment_errmsg" => __("Settlement was not completed.",'usces') );
						$this->set_order_meta_value( 'acting_digitalcheck_conv', serialize( $data ), $order_id );
					}
					$this->cart->crear_cart();
					$acting_opts = $this->options['acting_settings']['digitalcheck'];
					header( "location: ".$acting_opts['send_url_conv'].'?acting=digitalcheck_conv'.$query );
					exit;
				} else {
					usces_log('digitalcheck conv : order processing error', 'acting_transaction.log');
					header( "location: ".USCES_CART_URL.$delim.'acting=digitalcheck_conv&acting_return=0' );
				}
			} else {
				$acting_opts = $this->options['acting_settings']['digitalcheck'];
				header( "location: ".$acting_opts['send_url_conv'].'?acting=digitalcheck_conv'.$query );
				exit;
			}
//20121206ysk end
//20140206ysk start
		} else if( $acting_flg == 'acting_veritrans_card' or $acting_flg == 'acting_veritrans_conv' ) {
			$acting_opts = $this->options['acting_settings']['veritrans'];
			$acting = substr( $acting_flg, 7 );
			$dummy_payment_flag = ( 'public' == $acting_opts['ope'] ) ? '0' : '1';
			$order_id = isset( $_POST['ORDER_ID'] ) ? $_POST['ORDER_ID'] : '';
			$url = parse_url( $acting_opts['regist_url'] );
			$path = empty($url['path']) ? '/' : $url['path'];

			$postdata = $query;
			if( 'acting_veritrans_card' == $acting_flg ) {
				$card_capture_flag = ( 'capture' == $acting_opts['card_capture_flag'] ) ? '1' : '0';
				$postdata .= '&CARD_CAPTURE_FLAG='.$card_capture_flag;
			}
			if( 'acting_veritrans_conv' == $acting_flg ) {
				$postdata .= '&NAME1='.urlencode( mb_substr( mb_convert_kana( $entry['customer']['name1'], 'ASKV', 'UTF-8' ), 0, 10 ) );
				$postdata .= '&NAME2='.urlencode( mb_substr( mb_convert_kana( $entry['customer']['name2'], 'ASKV', 'UTF-8' ), 0, 10 ) );
				if( !empty($entry['customer']['name3']) ) {
					$kana1 = mb_substr( mb_convert_kana( $entry['customer']['name3'], 'ASKV', 'UTF-8' ), 0, 10 );
					mb_regex_encoding( 'UTF-8' );
					if( mb_ereg("^[ア-ン゛゜ァ-ォャ-ョー]+$", $kana1) ) 
						$postdata .= '&KANA1='.urlencode( $kana1 );
				}
				if( !empty($entry['customer']['name4']) ) {
					$kana2 = mb_substr( mb_convert_kana( $entry['customer']['name4'], 'ASKV', 'UTF-8' ), 0, 10 );
					mb_regex_encoding( 'UTF-8' );
					if( mb_ereg("^[ア-ン゛゜ァ-ォャ-ョー]+$", $kana2) ) 
						$postdata .= '&KANA2='.urlencode( $kana2 );
				}
				$postdata .= '&TELEPHONE_NO='.str_replace( '-', '', $entry['customer']['tel'] );
			}
			$postdata .= '&MERCHANT_ID='.$acting_opts['merchant_id'];
			$postdata .= '&SESSION_ID='.session_id();
			$postdata .= '&FINISH_PAYMENT_RETURN_URL='.urlencode( USCES_CART_URL.$delim.'acting='.$acting.'&acting_return=1&result=1' );
			$postdata .= '&UNFINISH_PAYMENT_RETURN_URL='.urlencode( USCES_CART_URL.$delim.'acting='.$acting.'&confirm=1' );
			$postdata .= '&ERROR_PAYMENT_RETURN_URL='.urlencode( USCES_CART_URL.$delim.'acting='.$acting.'&acting_return=0' );
			$postdata .= '&FINISH_PAYMENT_ACCESS_URL='.urlencode( USCES_CART_URL.$delim.'acting='.$acting );
			$postdata .= '&DUMMY_PAYMENT_FLAG='.$dummy_payment_flag;

			$postlength = strlen( $postdata );

			$request  = "POST ".$path." HTTP/1.1"."\r\n";
			$request .= "Host: ".$url['host']."\r\n";
			$request .= "User-Agent: HttpRequest Powered by ".phpversion()."\r\n";
			$request .= "Connection: close"."\r\n";
			$request .= "Accept-Language: ja"."\r\n";
			$request .= "Content-Type: application/x-www-form-urlencoded"."\r\n";
			$request .= "Content-Length: ".$postlength."\r\n\r\n";
			$request .= $postdata;

			$code = 0;
			$resBody = "";
			$con = @fsockopen( 'ssl://'.$url['host'], 443 );

			if( $con ) {
				$ret = fwrite( $con, $request );
				if( $ret == strlen($request) ) {
					$res = $this->readResponse( $con );
//usces_log( print_r($res,true), "veritrans.log" );
					$code = $res['Code'];
					$resBody = $res['Body'];

				} else {
					usces_log( 'Veritrans Write NG: Sent:'.strlen($request).' Send:'.$ret, "acting_transaction.log" );
				}
				fclose( $con );

			} else {
				usces_log( "Veritrans Connect NG: ".$url['host'], "acting_transaction.log" );
				header( "location: ".USCES_CART_URL.$delim.'acting='.$acting_flg.'&acting_return=0' );
				exit;
			}

			// 正常終了（200 OK）が返ったか
			if( intval($code) == 200 ) {
				$merchantKey = null;
				$browserKey = null;
				$scd = null;
				$error_message = null;

				// レスポンスデータからマーチャントキー、ブラウザキーを取得
				$bodyLine = explode( "\n", $resBody );
				foreach( $bodyLine as $line ) {
					if( preg_match( '/^MERCHANT_ENCRYPTION_KEY=(.+)/', $line, $match ) ) {
						$merchantKey = $match[1];
					} elseif( preg_match( '/^BROWSER_ENCRYPTION_KEY=(.+)/', $line, $match ) ) {
						$browserKey = $match[1];
					} elseif( preg_match('/^SCD=(.+)/', $line, $match ) ) {
						$scd = $match[1];
					} elseif( preg_match( '/^ERROR_MESSAGE=(.+)/', $line, $match ) ) {
						$error_message = $match[1];
					}
				}

				// 両方取れたらOK。SCDはカードでセキュリティコードが入力された場合のみ
				if( !is_null($merchantKey) && !is_null($browserKey) ) {
					$getdata  = '?MERCHANT_ID='.$acting_opts['merchant_id'];
					$getdata .= '&ORDER_ID='.$order_id;
					$getdata .= '&BROWSER_ENCRYPTION_KEY='.urlencode($browserKey);
					header( "location: ".$acting_opts['payment_url'].$getdata );

				} else {
					if( !is_null($error_message) ) 
						usces_log( "Veritrans AWeb:".$error_message, "acting_transaction.log" );
					header( "location: ".USCES_CART_URL.$delim.'acting='.$acting_flg.'&acting_return=0' );
				}

			} else {
				usces_log( "Veritrans Response NG: ".$resBody, "acting_transaction.log" );
				header( "location: ".USCES_CART_URL.$delim.'acting='.$acting_flg.'&acting_return=0' );
			}
			exit;
//20140206ysk end
		}
		do_action('usces_action_acting_processing', $acting_flg, $query);
	}

	private function readResponse( $fp ) {
		$res = array( 'Status'=>'', 'Version'=>'', 'Code'=>0, 'Message'=>'', 'Headers'=>array(), 'Body'=>'' );

		// HTTPステータスの読み込み
		$line = $this->readLine( $fp );
		if( preg_match( '/^(HTTP\/1\.[0-9x]+)\s+([0-9]+)\s+(.+)/i', $line, $match ) == 0 ) {
			return $res;
		}
		$res['Status'] = $line;
		$res['Version'] = $match[1];
		$res['Code'] = $match[2];
		$res['Message'] = $match[3];

		// レスポンスヘッダの読み込み
		while( !feof($fp) ) {
			$line = $this->readLine( $fp );
			if( $line != '' ) {
				list( $hname, $hvalue ) = explode( ':', $line, 2 );
				$res['Headers'][strtolower($hname)] = ltrim($hvalue);
			} else {
				break;
			}
		}
		// レスポンスデータの読み込み
		while( !feof($fp) ) {
			$data = $this->readLine( $fp )."\n";
			if( '' == $data ) {
				break;
			}
			$res['Body'] .= $data;
		}
		return $res;
	}

	private function readLine( $fp ) {
		if( !$fp ) {
			return '';
		}
		// レスポンスデータを受信
		$line = null;
		while( !feof($fp) ) {
			$line .= @fgets( $fp, 4096 );
			if( substr($line, -1) == "\n" ) {
				return rtrim( $line, "\r\n" );
			}
		}
		return $line;
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
			
		do_action( 'usces_action_lastprocessing' );
			
		unset($_SESSION['usces_singleitem']);

	}
	
	function is_item_zaiko( $post_id, $sku ){
		$status_num = (int)$this->getItemZaikoStatusId($post_id, $sku);
		$zaiko_num = $this->getItemZaikoNum($post_id, $sku);

		if( false !== $zaiko_num 
			&& ( 0 < (int)$zaiko_num || WCUtils::is_blank($zaiko_num) ) 
			&& false !== $status_num 
			&& 2 > $status_num 
		){
			$res = true;
		}else{
			$res = false;
		}
	
		return apply_filters( 'usces_is_item_zaiko', $res, $post_id, $sku, $status_num, $zaiko_num );

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
		return apply_filters( 'usces_filter_get_total_price', $total_price, $cart);
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
				foreach ( $cart as $rows ) {
					$cats = $this->get_post_term_ids($rows['post_id'], 'category');
					if ( !in_array($this->options['campaign_category'], $cats) ){
						$rate = get_post_meta($rows['post_id'], '_itemPointrate', true);
						$price = $rows['price'] * $rows['quantity'];
						$point += $price * $rate / 100;
					}
				}
			} elseif ( $this->options['campaign_privilege'] == 'point' ) {
				foreach ( $cart as $rows ) {
					$rate = get_post_meta($rows['post_id'], '_itemPointrate', true);
					//$price = $this->getItemPrice($rows['post_id'], $rows['sku']) * $rows['quantity'];
					$price = $rows['price'] * $rows['quantity'];
					$cats = $this->get_post_term_ids($rows['post_id'], 'category');
					if ( in_array($this->options['campaign_category'], $cats) )
						$point += $price * $rate / 100 * $this->options['privilege_point'];
					else
						$point += $price * $rate / 100;
				}
			}
		} else {
			foreach ( $cart as $rows ) {
				$rate = get_post_meta($rows['post_id'], '_itemPointrate', true);
				//$price = $this->getItemPrice($rows['post_id'], $rows['sku']) * $rows['quantity'];
				$price = $rows['price'] * $rows['quantity'];
				$point += $price * $rate / 100;
			}
		}

//20130425ysk start 0000699
		if( 0 < $point ) $point = ceil( $point );

		$entry = $this->cart->get_entry();
		$use_point = isset( $entry['order']['usedpoint'] ) ? (int)$entry['order']['usedpoint'] : 0;
		if( 0 < $use_point ) {
			$point = ceil( $point - ( $point * $use_point / $total ) );
			if( 0 > $point )
				$point = 0;
		}

		return apply_filters( 'usces_filter_get_order_point', $point, $mem_id, $display_mode, $cart );
//20130425ysk end
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
		if( function_exists('dlseller_have_shipped') && !dlseller_have_shipped() ){
			$charge = 0;
			$charge = apply_filters('usces_filter_getShippingCharge', $charge, $cart, $entry);
			return $charge;
		}
		
		//配送方法ID
		$d_method_id = $entry['order']['delivery_method'];
		//配送方法index
		$d_method_index = $this->get_delivery_method_index($d_method_id);
		//送料ID
		$fixed_charge_id = ( isset($this->options['delivery_method'][$d_method_index]['charge']) ) ? $this->options['delivery_method'][$d_method_index]['charge'] : -1;
		$individual_quant = 0;
		$total_quant = 0;
		$charges = array();
		$individual_charges = array();
		$country = (isset($entry['delivery']['country']) && !empty($entry['delivery']['country'])) ? $entry['delivery']['country'] : $entry['customer']['country'];//20120710ysk 0000472
		
		foreach ( $cart as $rows ) {
		
			if( -1 == $fixed_charge_id ){
				//商品送料ID
				$s_charge_id = $this->getItemShippingCharge($rows['post_id']);
				//商品送料index
				$s_charge_index = $this->get_shipping_charge_index($s_charge_id);
//20120710ysk start 0000472
				//$charge = isset($this->options['shipping_charge'][$s_charge_index]['value'][$pref]) ? $this->options['shipping_charge'][$s_charge_index]['value'][$pref] : 0;
				$charge = isset($this->options['shipping_charge'][$s_charge_index][$country][$pref]) ? $this->options['shipping_charge'][$s_charge_index][$country][$pref] : 0;
//20120710ysk end
			}else{
			
				$s_charge_index = $this->get_shipping_charge_index($fixed_charge_id);
//20120710ysk start 0000472
				//$charge = isset($this->options['shipping_charge'][$s_charge_index]['value'][$pref]) ? $this->options['shipping_charge'][$s_charge_index]['value'][$pref] : 0;
				$charge = isset($this->options['shipping_charge'][$s_charge_index][$country][$pref]) ? $this->options['shipping_charge'][$s_charge_index][$country][$pref] : 0;
//20120710ysk end
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
		global $usces_entries;

		$payments = $this->getPayments($payment_name);
		if( 'COD' != $payments['settlement'] ){
			$fee = 0;
		
		}else if( 'change' != $this->options['cod_type'] ){
			//$fee = isset($this->options['cod_fee']) ? $this->options['cod_fee'] : '';
			$fee = isset($this->options['cod_fee']) ? $this->options['cod_fee'] : 0;
		
		}else{
			$materials = array(
				'total_items_price' => $usces_entries['order']['total_items_price'],
				'discount' => $usces_entries['order']['discount'],
				'shipping_charge' => $usces_entries['order']['shipping_charge'],
				'cod_fee' => $usces_entries['order']['cod_fee'],
			);
			$price = $amount_by_cod + $this->getTax( $amount_by_cod, $materials );
			if( $price <= $this->options['cod_first_amount'] ){
				$fee = $this->options['cod_first_fee'];
			
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
			}else{
				$fee = $this->options['cod_end_fee'];
			}
		}
		$fee = apply_filters('usces_filter_getCODFee', $fee, $payment_name, $amount_by_cod);
		return $fee;
	}

	function getTax( $total, $materials = array() ) {
		global $usces_settings;

		if( empty($this->options['tax_rate']) )
			return 0;

		if( empty($materials) ) {

			if( 'include' == $this->options['tax_mode'] ) {
				$tax = $total * $this->options['tax_rate'] / ( 100 + $this->options['tax_rate'] );
			} else {
				$tax = $total * $this->options['tax_rate'] / 100;
			}

		} else {
			if( 'include' == $this->options['tax_mode'] )
				return 0;

			extract($materials);//need( 'total_items_price', 'shipping_charge', 'discount', 'cod_fee', 'use_point' ) 

			if( 'products' == $this->options['tax_target'] ){
				$total = $total_items_price + $discount;
			}else{
				$total = $total_items_price + $discount + $shipping_charge + $cod_fee;
			}
			$total = apply_filters( 'usces_filter_getTax_total', $total, $materials);

			$tax = $total * $this->options['tax_rate'] / 100;
		}

		$cr = $this->options['system']['currency'];
		$decimal = $usces_settings['currency'][$cr][1];
		$decipad = (int)str_pad( '1', $decimal+1, '0', STR_PAD_RIGHT );
		switch( $this->options['tax_method'] ){
			case 'cutting':
				$tax = floor($tax*$decipad)/$decipad;
				break;
			case 'bring':
				$tax = ceil($tax*$decipad)/$decipad;
				break;
			case 'rounding':
				if( 0 < $decimal ){
					$tax = round($tax, (int)$decimal);
				}else{
					$tax = round($tax);
				}
				break;
		}

		return $tax;
	}

	function set_cart_fees( $member, $entries ) {
		$carts = $this->cart->get_cart();
		$total_items_price = $this->get_total_price();
		if ( empty($this->options['postage_privilege']) || $total_items_price < $this->options['postage_privilege'] ) {
			$shipping_charge = $this->getShippingCharge( $entries['delivery']['pref'] );
		} else {
			$shipping_charge = 0;
		}
		$shipping_charge = apply_filters('usces_filter_set_cart_fees_shipping_charge', $shipping_charge, $carts, $entries);
		$payments = $this->getPayments( $entries['order']['payment_name'] );
		$discount = $this->get_order_discount();
		$use_point = $entries['order']['usedpoint'];
		$amount_by_cod = $total_items_price - $use_point + $discount + $shipping_charge;
		$amount_by_cod = apply_filters('usces_filter_set_cart_fees_amount_by_cod', $amount_by_cod, $entries, $total_items_price, $use_point, $discount, $shipping_charge);
		$cod_fee = $this->getCODFee($entries['order']['payment_name'], $amount_by_cod);
		$cod_fee = apply_filters('usces_filter_set_cart_fees_cod', $cod_fee, $entries, $total_items_price, $use_point, $discount, $shipping_charge);
		$total_price = $total_items_price - $use_point + $discount + $shipping_charge + $cod_fee;
		$total_price = apply_filters('usces_filter_set_cart_fees_total_price', $total_price, $total_items_price, $use_point, $discount, $shipping_charge, $cod_fee);
		$materials = compact( 'member', 'entries', 'carts', 'total_items_price', 'shipping_charge', 'payments', 'discount', 'cod_fee', 'use_point', 'discount' );
		$tax = $this->getTax( $total_price, $materials );
		$total_full_price = $total_price + ( 'exclude' == $this->options['tax_mode'] ? $tax : 0 );
		$total_full_price = apply_filters('usces_filter_set_cart_fees_total_full_price', $total_full_price, $total_items_price, $use_point, $discount, $shipping_charge, $cod_fee);
		$get_point = $this->get_order_point( $member['ID'] );
//20130425ysk start 0000699
		//if(0 < (int)$use_point){
		//	$get_point = ceil( $get_point - ($get_point * $use_point / $total_items_price) );
		//	if(0 > $get_point)
		//		$get_point = 0;
		//}
//20130425ysk end

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
		//$entries = $this->cart->get_entry();
//var_dump($entries);
	}
	
	function getPayments( $payment_name ) {
		if( '#none#' == $payment_name )
			return NULL;
			
		$payments = usces_get_system_option( 'usces_payment_method', 'name' );
		if( isset($payments[$payment_name]) )
			return $payments[$payment_name];
		return NULL;
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
		
		$query = $wpdb->prepare("SELECT * FROM $order_table WHERE mem_id = %d ORDER BY order_date DESC", $mem_id);
		$query = apply_filters( 'usces_filter_member_history_query', $query, $mem_id );
//		$query = $wpdb->prepare("SELECT ID, order_cart, order_condition, order_date, order_usedpoint, order_getpoint, 
//								order_discount, order_shipping_charge, order_cod_fee, order_tax, order_status 
//							FROM $order_table WHERE mem_id = %d ORDER BY order_date DESC", $mem_id);
		$results = $wpdb->get_results( $query );
	
		$i=0;
		$res = array();
		
		if( is_user_logged_in() && is_admin() ){

			foreach ( $results as $value ) {
				
				$res[] = array(
							'ID' => $value->ID,
//							'cart' => unserialize($value->order_cart),
							'cart' => usces_get_ordercartdata( $value->ID ),
							'condition' => unserialize($value->order_condition),
							'getpoint' => $value->order_getpoint,
							'usedpoint' => $value->order_usedpoint,
							'discount' => $value->order_discount,
							'shipping_charge' => $value->order_shipping_charge,
							'payment_name' => $value->order_payment_name,
							'cod_fee' => $value->order_cod_fee,
							'tax' => $value->order_tax,
							'order_status' => $value->order_status,
							'date' => mysql2date(__('Y/m/d'), $value->order_date),
							'order_date' => $value->order_date
							);
				$i++;
			}
			
		}elseif( !is_admin() ){
		
			foreach ( $results as $value ) {
				
				if( strpos($value->order_status, 'cancel') === false && strpos($value->order_status, 'estimate') === false ){
					$res[] = array(
								'ID' => $value->ID,
	//							'cart' => unserialize($value->order_cart),
								'cart' => usces_get_ordercartdata( $value->ID ),
								'condition' => unserialize($value->order_condition),
								'getpoint' => $value->order_getpoint,
								'usedpoint' => $value->order_usedpoint,
								'discount' => $value->order_discount,
								'shipping_charge' => $value->order_shipping_charge,
								'payment_name' => $value->order_payment_name,
								'cod_fee' => $value->order_cod_fee,
								'tax' => $value->order_tax,
								'order_status' => $value->order_status,
								'date' => mysql2date(__('Y/m/d'), $value->order_date),
								'order_date' => $value->order_date
								);
					$i++;
				}
			
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

		return apply_filters('usces_filter_get_tag_names', $names, $post_id);
	
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
		$usces_cookie = $this->get_cookie();
		if( isset($usces_cookie['id']) && !empty($usces_cookie['id']) ){
			$cid = $usces_cookie['id'];
		}elseif( isset($_SESSION['usces_cookieid']) && !empty($_SESSION['usces_cookieid']) ){
			$cid = $_SESSION['usces_cookieid'];
		}else{
			$cid = 0;
		}
		while($h<strlen($sessid)){
			if(0 == $i % 3){
				$chars .= substr($i, -1);
			}else{
				$chars .= substr($sessid, $h, 1);
				$h++;
			}
			$i++;
		}
		if( $flag ){
			$postfix = ( isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) ) ? $_SERVER['REMOTE_ADDR'] : 'REMOTE_ADDR';
			$postfix = apply_filters('usces_sessid_force', $postfix);
			$sessid = $chars . '_' . $postfix . '_' . $cid . '_A';
		}else{
			$sessid = $chars . '_' . apply_filters('usces_sessid_flag', 'acting') . '_' . $cid . '_A';
		}
		$sessid = urlencode(base64_encode($sessid));
//usces_log('sessid2 : '.$sessid, 'acting_transaction.log');

		return $sessid;
	}
	
	function uscesdc( $sessid ) {
//		$usces_cookie = $this->get_cookie();
//		if( $this->use_ssl && ($this->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $this->is_inquiry_page($_SERVER['REQUEST_URI']))){
//			if( isset($usces_cookie['sslid']) && !empty($usces_cookie['sslid']) ){
//				$cid = $usces_cookie['sslid'];
//			}else{
//				$cid = 0;
//			}
//		}else{
//			if( isset($usces_cookie['id']) && !empty($usces_cookie['id']) ){
//				$cid = $usces_cookie['id'];
//			}else{
//				$cid = 0;
//			}
//		}
		$sessid = base64_decode(urldecode($sessid));
		list($sess, $addr, $cookieid, $none) = explode('_', $sessid, 4);
		$postfix = ( isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) ) ? $_SERVER['REMOTE_ADDR'] : 'REMOTE_ADDR';
		$postfix = apply_filters('usces_sessid_force', $postfix);
//usces_log('cid : '.$cid, 'acting_transaction.log');
//usces_log('cookieid : '.$cookieid, 'acting_transaction.log');
//usces_log('postfix : '.$postfix, 'acting_transaction.log');
//usces_log('addr : '.$addr, 'acting_transaction.log');
		if( 'acting' !== $addr && 'mobile' !== $addr && $postfix !== $addr ) {
			$sessid = '';
			return NULL;
		}
		$chars = '';
		$h=0;
		while($h<strlen($sess)){
			if(0 != $h % 3){
				$chars .= substr($sess, $h, 1);
			}
			$h++;
		}
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
		
		$query = $wpdb->prepare("SELECT COUNT(ID) AS ct FROM $table_name WHERE order_date >= %s AND order_date <= %s AND 0 = LOCATE(%s, order_status) AND 0 = LOCATE(%s, order_status)", $date, $today, 'cancel', 'estimate');
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
								 FROM $table_name WHERE order_date >= %s AND order_date <= %s AND 0 = LOCATE(%s, order_status) AND 0 = LOCATE(%s, order_status)", $date, $today, 'cancel', 'estimate');
		$res = $wpdb->get_row($query, ARRAY_A);
		
		if( $res == NULL )
			return 0;
		else
			return $res['price'] - $res['point'] + $res['discount'] + $res['shipping'] + $res['cod'] + $res['tax'];
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
		global $wpdb;
		$table_name = $wpdb->prefix . "usces_member";
		$query = $wpdb->prepare("SELECT ID FROM $table_name WHERE mem_email = %s", $email);
		$res = $wpdb->get_var($query);
		return $res;
	}
	
	function get_condition(){
//20120807ysk start 0000544
		$order_conditions = array(
		'display_mode' => $this->options['display_mode'],
		'campaign_privilege' => $this->options['campaign_privilege'],
		'campaign_category' => $this->options['campaign_category'],
		'privilege_point' => $this->options['privilege_point'],
		'privilege_discount' => $this->options['privilege_discount'],
		'tax_mode' => $this->options['tax_mode'],
		'tax_target' => $this->options['tax_target'],
		'tax_rate' => $this->options['tax_rate'],
		'tax_method' => $this->options['tax_method'],
		'membersystem_state' => $this->options['membersystem_state'],
		'membersystem_point' => $this->options['membersystem_point'],
		);
		return $order_conditions;
//20120807ysk end
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
		if( empty($days) ){
			$days = 30;
		}
		$order_date = date('Y-m-d H:i:s', mktime(0, 0, 0, (int)$monthstr, ((int)$daystr-$days), (int)$yearstr));
		$where = " WHERE order_date >= '{$order_date}'";
		$query = "SELECT order_cart FROM {$order_table_name}" . $where;
		$dbres = $wpdb->get_col($query);
		if(!$dbres) return false;
		
		foreach((array)$dbres as $carts){
			$rows = unserialize($carts);
			foreach((array)$rows as $carts){
				if( 'publish' != get_post_status($carts['post_id']) )
					continue;
					
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
		$query = $wpdb->prepare("SELECT COUNT(ID) AS ct FROM {$wpdb->posts} 
								WHERE post_mime_type = %s AND post_type = %s AND post_status <> %s", 
								'item', 'post', 'trush');
		$res = $wpdb->get_var($query);

		return $res;
	}
	
	function is_gptekiyo( $post_id, $sku, $quant ) {
		$skus = $this->get_skus( $post_id, 'code' );
		if( !isset($skus[$sku]['gp']) || !$skus[$sku]['gp'] ) return false;

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
			$integration = array();
			$temp = array();
			$in = 0;
			foreach($cart as $key => $row){
				$deli = $this->getItemDeliveryMethod($row['post_id']);
				//usces_p($deli);
				if( empty($deli))
					continue;

			//usces_log('deli : '.print_r($deli, true), 'acting_transaction.log');
				if( 0 === $in ){
					$intersect = $deli;
				}
				$intersect = array_intersect($deli, $intersect);
				$before_deli = $deli;
				foreach($deli as $value){
					$integration[] = $value;
				}
				$in++;
			}
			$integration = array_unique($integration);
			foreach($integration as $id){
				$index = $this->get_delivery_method_index($id);
				$temp[$index] = $id;
			}
			ksort($temp);
//20120820ysk start 0000546
			//$force = array($temp[0]);
			$force = array(array_shift($temp));
//20120820ysk end
			
			if( empty($intersect) ){
				return $force;
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
//20120710ysk start 0000472
			//if( $this->options['shipping_charge'][$i]['id'] === $id ){
			if( (int)$this->options['shipping_charge'][$i]['id'] == (int)$id ){
//20120710ysk end
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
		
		$name_str = apply_filters('usces_admin_order_item_name_filter', $name_str, $post_id, $sku);
		
		return trim($name_str);
	}
	
	function getCartItemName_byOrder($cart_row){
		$name_arr = array();
		$name_str = '';
		
		foreach($this->options['indi_item_name'] as $key => $value){
			if($value){
				$pos = (int)$this->options['pos_item_name'][$key];
				$ind = ($pos === 0) ? 'A' : $pos;
				switch($key){
					case 'item_name':
						$name_arr[$ind][$key] = $cart_row['item_name'];
						break;
					case 'item_code':
						$name_arr[$ind][$key] = $cart_row['item_code'];
						break;
					case 'sku_name':
						$name_arr[$ind][$key] = $cart_row['sku_name'];
						break;
					case 'sku_code':
						$name_arr[$ind][$key] = $cart_row['sku_code'];
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
		
		$name_str = apply_filters('usces_filter_item_mame_by_order', $name_str, $cart_row);
		
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
//20130524ysk start
	function del_order_meta( $key, $order_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix."usces_order_meta";
		$query = $wpdb->prepare( "DELETE FROM $table_name WHERE order_id = %d AND meta_key = %s", $order_id, $key );
		$res = $wpdb->query( $query );
		return $res;
	}
//20130524ysk end
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
//20130524ysk start 0000712
		$csmb_meta = usces_has_custom_field_meta( 'member' );
		if( is_array($csmb_meta) ) {
			foreach( $csmb_meta as $key => $entry ) {
				if( '4' == $entry['means'] ) {
					$this->del_member_meta( 'csmb_'.$key, $member_id );
				}
			}
		}
//20130524ysk end
		if( !empty($_POST['custom_member']) ) {
			foreach( $_POST['custom_member'] as $key => $value ) {
				$csmb_key = 'csmb_'.$key;
				if( is_array($value) ) 
					 $value = serialize($value);
				$res = $this->set_member_meta_value($csmb_key, $value, $member_id);
				if(false === $res) 
					return false;
			}
		}elseif( isset($_POST['custom_customer']) ){
			foreach( $_POST['custom_customer'] as $key => $value ) {
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
		if( WCUtils::is_blank($member_id) ) {
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
		$query = $wpdb->prepare("SELECT meta_key, meta_value FROM $table_name WHERE order_id = %d AND (meta_key LIKE %s OR meta_key = %s OR meta_key = %s OR meta_key = %s OR meta_key = %s OR meta_key = %s)", 
								$order_id, 'acting_%', 'settlement_id', 'order_number', 'res_tracking_id', 'SID', 'TransactionId');
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
			}elseif( 'order_number' == $value['meta_key'] ){
				$fields['order_number'] = $value['meta_value'];
			}elseif( 'res_tracking_id' == $value['meta_key'] ){
				$fields['res_tracking_id'] = $value['meta_value'];
			}elseif( 'SID' == $value['meta_key'] ){
				$fields['SID'] = $value['meta_value'];
			}elseif( 'TransactionId' == $value['meta_key'] ){
				$fields['TransactionId'] = $value['meta_value'];
			}elseif( 'acting_' == substr($value['meta_key'], 0, 7) ){
				$meta_values = maybe_unserialize($value['meta_value']);
				if(is_array($meta_values)){
					foreach( $meta_values as $key => $meta_value ){
						$fields[$key] = $meta_value;
					}
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
	
	function get_post_user_custom($post_id, $orderby='meta_id', $order='ASC'){
		global $wpdb;
		$res = array();
		$table = $wpdb->prefix . "postmeta";
		$meta_list = $wpdb->get_results( $wpdb->prepare("SELECT meta_key, meta_value FROM $table WHERE post_id = %d ORDER BY $orderby $order",
			$post_id), ARRAY_A );
			
		if ( !empty($meta_list) ) {
			foreach ( $meta_list as $metarow) {
				if( 0 === strpos($metarow['meta_key'], '_') )
					continue;
					
				$mkey = $metarow['meta_key'];
				$mval = $metarow['meta_value'];
				if( array_key_exists($mkey, $res) ){
					$cval = $res[$mkey];
					$cval = (array)$cval;
					$cval[] = $mval;
					$res[$mkey] = $cval;
				}else{
					$res[$mkey] = $mval;
				}
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
		$price = number_format((double)$amount, $decimal, $point, $seperator);//0000652

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
		$payments = usces_get_system_option( 'usces_payment_method', 'sort' );
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
//20120710ysk start 0000472
		$entry = $this->cart->get_entry();
		$country = (isset($entry['delivery']['country']) && !empty($entry['delivery']['country'])) ? $entry['delivery']['country'] : $entry['customer']['country'];//20120710ysk 0000472
//20120710ysk end
		$arr = array();
		foreach ( (array)$this->options['shipping_charge'] as $charges ) {
//20120710ysk start 0000472
			//foreach ( (array)$charges['value'] as $value ) {
			foreach ( (array)$charges[$country] as $value ) {
//20120710ysk end
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
			'force' => 0,
			'quant' => 0,
			'opt' => 1,
		), $atts));
	
		$post_id = $this->get_ID_byItemName($item);
		$datas = $this->get_skus( $post_id, 'code' );
		$zaikonum = $datas[$sku]['stocknum'];
		$zaiko = $datas[$sku]['stock'];
		$gptekiyo = $datas[$sku]['gp'];
		$skuPrice = $datas[$sku]['price'];
		$sku_enc = urlencode($sku);
		$mats = compact('item','sku','value','force','quant','post_id','datas','zaikonum','zaiko','gptekiyo','skuPrice','sku_enc');
		if( ! $this->is_item_zaiko( $post_id, $sku ) ){
			return '<div class="button_status">' . esc_html($this->zaiko_status[$zaiko]) . '</div>';
		}
		
		$html = "<form action=\"" . USCES_CART_URL . "\" method=\"post\">\n";
		$html .= "<input name=\"zaikonum[{$post_id}][{$sku_enc}]\" type=\"hidden\" id=\"zaikonum[{$post_id}][{$sku_enc}]\" value=\"{$zaikonum}\" />\n";
		$html .= "<input name=\"zaiko[{$post_id}][{$sku_enc}]\" type=\"hidden\" id=\"zaiko[{$post_id}][{$sku_enc}]\" value=\"{$zaiko}\" />\n";
		$html .= "<input name=\"gptekiyo[{$post_id}][{$sku_enc}]\" type=\"hidden\" id=\"gptekiyo[{$post_id}][{$sku_enc}]\" value=\"{$gptekiyo}\" />\n";
		$html .= "<input name=\"skuPrice[{$post_id}][{$sku_enc}]\" type=\"hidden\" id=\"skuPrice[{$post_id}][{$sku_enc}]\" value=\"{$skuPrice}\" />\n";
		if( 1 == $opt ){
			$html .= usces_item_option_fileds( $post_id, $sku, 1, 'return' );
		}elseif( 2 == $opt ){
			$html .= usces_item_option_fileds( $post_id, $sku, 0, 'return' );
		}
		if( $quant ){
			$quant_field = "<input name=\"quant[{$post_id}][" . $sku_enc . "]\" type=\"text\" id=\"quant[{$post_id}][" . $sku_enc . "]\" class=\"skuquantity\" value=\"\" onKeyDown=\"if (event.keyCode == 13) {return false;}\" />";
			$html .= apply_filters('usces_filter_sc_itemQuant', $quant_field, $mats);
		}
		$html .= "<input name=\"inCart[{$post_id}][{$sku_enc}]\" type=\"submit\" id=\"inCart[{$post_id}][{$sku_enc}]\" class=\"skubutton\" value=\"{$value}\" " . apply_filters('usces_filter_direct_intocart_button', NULL, $post_id, $sku, $force, $options) . " />";
		$html .= "<input name=\"usces_referer\" type=\"hidden\" value=\"" . $_SERVER['REQUEST_URI'] . "\" />\n";
		if( $force )
			$html .= "<input name=\"usces_force\" type=\"hidden\" value=\"incart\" />\n";
		$html = apply_filters('usces_filter_single_item_inform', $html);
		$html .= "</form>";
		$html .= '<div class="error_message">' . usces_singleitem_error_message($post_id, $sku, 'return') . '</div>'."\n";
		
		return $html;
	}

	function filter_itemPage($content){
		global $post;
		$html = '';

		if( ($post->post_mime_type != 'item' || !is_single()) ) return $content;
		if( post_password_required($post) ) return $content;
		
		$temp_path = apply_filters('usces_template_path_single_item', USCES_PLUGIN_DIR . '/templates/single_item.php');
		include( $temp_path );
		
		$content = apply_filters('usces_filter_itemPage', $html, $post->ID);

		return $content;
	}

	function filter_cartContent($content) {
		global $post;
		$html = '';
		
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
			$html = str_replace('src="'.site_url(), 'src="'.USCES_SSL_URL_ADMIN, $html);

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
		$html = '';
		
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
			$html .= "<p>".__('Member Services is not running currently.','usces')."</p>";
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


		if( ($this->options['divide_item'] && !is_category() && !is_search() && !is_singular() && !is_admin()) ){
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
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_cart_page_footer($html){
		if( !empty($this->options['cart_page_data']['footer']['cart']) ){
			$html = $this->options['cart_page_data']['footer']['cart'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_customer_page_header($html){
		if( !empty($this->options['cart_page_data']['header']['customer']) ){
			$html = $this->options['cart_page_data']['header']['customer'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_customer_page_footer($html){
		if( !empty($this->options['cart_page_data']['footer']['customer']) ){
			$html = $this->options['cart_page_data']['footer']['customer'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_delivery_page_header($html){
		if( !empty($this->options['cart_page_data']['header']['delivery']) ){
			$html = $this->options['cart_page_data']['header']['delivery'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_delivery_page_footer($html){
		if( !empty($this->options['cart_page_data']['footer']['delivery']) ){
			$html = $this->options['cart_page_data']['footer']['delivery'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_confirm_page_header($html){
		if( !empty($this->options['cart_page_data']['header']['confirm']) ){
			$html = $this->options['cart_page_data']['header']['confirm'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_confirm_page_footer($html){
		if( !empty($this->options['cart_page_data']['footer']['confirm']) ){
			$html = $this->options['cart_page_data']['footer']['confirm'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_cartcompletion_page_header($html){
		if( !empty($this->options['cart_page_data']['header']['completion']) ){
			$html = $this->options['cart_page_data']['header']['completion'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_cartcompletion_page_footer($html){
		if( !empty($this->options['cart_page_data']['footer']['completion']) ){
			$html = $this->options['cart_page_data']['footer']['completion'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_login_page_header($html){
		if( !empty($this->options['member_page_data']['header']['login']) ){
			$html = $this->options['member_page_data']['header']['login'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_login_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['login']) ){
			$html = $this->options['member_page_data']['footer']['login'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_newmember_page_header($html){
		if( !empty($this->options['member_page_data']['header']['newmember']) ){
			$html = $this->options['member_page_data']['header']['newmember'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_newmember_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['newmember']) ){
			$html = $this->options['member_page_data']['footer']['newmember'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_newpass_page_header($html){
		if( !empty($this->options['member_page_data']['header']['newpass']) ){
			$html = $this->options['member_page_data']['header']['newpass'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_newpass_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['newpass']) ){
			$html = $this->options['member_page_data']['footer']['newpass'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_changepass_page_header($html){
		if( !empty($this->options['member_page_data']['header']['changepass']) ){
			$html = $this->options['member_page_data']['header']['changepass'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_changepass_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['changepass']) ){
			$html = $this->options['member_page_data']['footer']['changepass'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_memberinfo_page_header($html){
		if( !empty($this->options['member_page_data']['header']['memberinfo']) ){
			$html = $this->options['member_page_data']['header']['memberinfo'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_memberinfo_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['memberinfo']) ){
			$html = $this->options['member_page_data']['footer']['memberinfo'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_membercompletion_page_header($html){
		if( !empty($this->options['member_page_data']['header']['completion']) ){
			$html = $this->options['member_page_data']['header']['completion'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function filter_membercompletion_page_footer($html){
		if( !empty($this->options['member_page_data']['footer']['completion']) ){
			$html = $this->options['member_page_data']['footer']['completion'];
		}
		return do_shortcode( stripslashes(nl2br($html)) );
	}
	
	function action_cart_page_header(){
		if( !empty($this->options['cart_page_data']['header']['cart']) ){
			$html = $this->options['cart_page_data']['header']['cart'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_cart_page_footer(){
		if( !empty($this->options['cart_page_data']['footer']['cart']) ){
			$html = $this->options['cart_page_data']['footer']['cart'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_customer_page_header(){
		if( !empty($this->options['cart_page_data']['header']['customer']) ){
			$html = $this->options['cart_page_data']['header']['customer'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_customer_page_footer(){
		if( !empty($this->options['cart_page_data']['footer']['customer']) ){
			$html = $this->options['cart_page_data']['footer']['customer'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_delivery_page_header(){
		if( !empty($this->options['cart_page_data']['header']['delivery']) ){
			$html = $this->options['cart_page_data']['header']['delivery'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_delivery_page_footer(){
		if( !empty($this->options['cart_page_data']['footer']['delivery']) ){
			$html = $this->options['cart_page_data']['footer']['delivery'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_confirm_page_header(){
		if( !empty($this->options['cart_page_data']['header']['confirm']) ){
			$html = $this->options['cart_page_data']['header']['confirm'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_confirm_page_footer(){
		if( !empty($this->options['cart_page_data']['footer']['confirm']) ){
			$html = $this->options['cart_page_data']['footer']['confirm'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_cartcompletion_page_header(){
		if( !empty($this->options['cart_page_data']['header']['completion']) ){
			$html = $this->options['cart_page_data']['header']['completion'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_cartcompletion_page_footer(){
		if( !empty($this->options['cart_page_data']['footer']['completion']) ){
			$html = $this->options['cart_page_data']['footer']['completion'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_login_page_header(){
		if( !empty($this->options['member_page_data']['header']['login']) ){
			$html = $this->options['member_page_data']['header']['login'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_login_page_footer(){
		if( !empty($this->options['member_page_data']['footer']['login']) ){
			$html = $this->options['member_page_data']['footer']['login'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_newmember_page_header(){
		if( !empty($this->options['member_page_data']['header']['newmember']) ){
			$html = $this->options['member_page_data']['header']['newmember'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_newmember_page_footer(){
		if( !empty($this->options['member_page_data']['footer']['newmember']) ){
			$html = $this->options['member_page_data']['footer']['newmember'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_newpass_page_header(){
		if( !empty($this->options['member_page_data']['header']['newpass']) ){
			$html = $this->options['member_page_data']['header']['newpass'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_newpass_page_footer(){
		if( !empty($this->options['member_page_data']['footer']['newpass']) ){
			$html = $this->options['member_page_data']['footer']['newpass'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_changepass_page_header(){
		if( !empty($this->options['member_page_data']['header']['changepass']) ){
			$html = $this->options['member_page_data']['header']['changepass'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_changepass_page_footer(){
		if( !empty($this->options['member_page_data']['footer']['changepass']) ){
			$html = $this->options['member_page_data']['footer']['changepass'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_memberinfo_page_header(){
		if( !empty($this->options['member_page_data']['header']['memberinfo']) ){
			$html = $this->options['member_page_data']['header']['memberinfo'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_memberinfo_page_footer(){
		if( !empty($this->options['member_page_data']['footer']['memberinfo']) ){
			$html = $this->options['member_page_data']['footer']['memberinfo'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_membercompletion_page_header(){
		if( !empty($this->options['member_page_data']['header']['completion']) ){
			$html = $this->options['member_page_data']['header']['completion'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}
	
	function action_membercompletion_page_footer(){
		if( !empty($this->options['member_page_data']['footer']['completion']) ){
			$html = $this->options['member_page_data']['footer']['completion'];
			echo do_shortcode( stripslashes(nl2br($html)) );
		}
	}	
}


