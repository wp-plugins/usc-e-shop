<?php
function usces_states_form_js(){
	global $usces;
	
	$js = '';
	if( $usces->use_js 
			&& ((  (is_page(USCES_MEMBER_NUMBER) || $usces->is_member_page($_SERVER['REQUEST_URI'])) && ((true === $usces->is_member_logged_in() && WCUtils::is_blank($usces->page)) || 'member' == $usces->page || 'editmemberform' == $usces->page || 'newmemberform' == $usces->page)  )
			|| (  (is_page(USCES_CART_NUMBER) || $usces->is_cart_page($_SERVER['REQUEST_URI'])) && ('customer' == $usces->page || 'delivery' == $usces->page)  ) 
			)) {
			
		$js .= '<script type="text/javascript">
		(function($) {
		uscesForm = {
			settings: {
				url: uscesL10n.ajaxurl,
				type: "POST",
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
				s.url = "' . USCES_SSL_URL . '/";
				s.data = "usces_ajax_action=change_states&country=" + country;
				s.success = function(data, dataType){
					if( "error" == data ){
						alert("error");
					}else{
						$("select#" + type + "_pref").html( data );
						if( customercountry == country && "customer" == type ){
							$("#" + type + "_pref").attr({selectedIndex:customerstate});
						}else if( deliverycountry == country && "delivery" == type ){
							$("#" + type + "_pref").attr({selectedIndex:deliverystate});
						}else if( customercountry == country && "member" == type ){
							$("#" + type + "_pref").attr({selectedIndex:customerstate});
						}
					}
				};
				s.error = function(msg){
					alert("error");
				};
				$.ajax( s );
				return false;
			}
		};';
		
		if( 'customer' == $usces->page ){
	
			$js .= 'var customerstate = $("#customer_pref").get(0).selectedIndex;
			var customercountry = $("#customer_country").val();
			var deliverystate = "";
			var deliverycountry = "";
			var memberstate = "";
			var membercountry = "";
			$("#customer_country").change(function () {
				var country = $("#customer_country option:selected").val();
				uscesForm.changeStates( country, "customer" ); 
			});';
			
		}elseif( 'delivery' == $usces->page ){
			
			$js .= 'var customerstate = "";
			var customercountry = "";
			var deliverystate = $("#delivery_pref").get(0).selectedIndex;
			var deliverycountry = $("#delivery_country").val();
			var memberstate = "";
			var membercountry = "";
			$("#delivery_country").change(function () {
				var country = $("#delivery_country option:selected").val();
				uscesForm.changeStates( country, "delivery" ); 
			});';
			
		}elseif( (true === $usces->is_member_logged_in() && WCUtils::is_blank($usces->page)) || (true === $usces->is_member_logged_in() && 'member' == $usces->page) || 'editmemberform' == $usces->page || 'newmemberform' == $usces->page ){
			
			$js .= 'var customerstate = "";
			var customercountry = "";
			var deliverystate = "";
			var deliverycountry = "";
			var memberstate = $("#member_pref").get(0).selectedIndex;
			var membercountry = $("#member_country").val();
			$("#member_country").change(function () {
				var country = $("#member_country option:selected").val();
				uscesForm.changeStates( country, "member" ); 
			});';
		}
		$js .= '})(jQuery);
			</script>';
	}
	
	echo apply_filters('usces_filter_states_form_js', $js);
}

function usces_get_pointreduction($currency){
	global $usces, $usces_settings;

	$form = $usces_settings['currency'][$currency];
	if( 2 == $form[1] ){
		$reduction = 0.01;
	}else{
		$reduction = 1;
	}
	$reduction = apply_filters('usces_filter_pointreduction', $reduction);
	return $reduction;
}

function usces_zeus_3dsecure_enrol(){
	global $usces;
	
	$acting_opts = $usces->options['acting_settings']['zeus'];

	$member = $usces->get_member();
	$pcid = $usces->get_member_meta_value('zeus_pcid', $member['ID']);

	$data = array();

	//if( 2 == $acting_opts['security'] && 'on' == $acting_opts['quickcharge'] && $pcid == '8888888888888888' && $usces->is_member_logged_in() ){
	if( 'on' == $acting_opts['quickcharge'] && $pcid == '8888888888888888' && $usces->is_member_logged_in() ){
		$data['authentication']['clientip'] = $acting_opts['clientip'];
		$data['authentication']['key'] = $acting_opts['authkey'];
		$data['card']['history']['key'] = 'sendid';
		$data['card']['history']['action'] = 'send_email';
		$data['card']['cvv'] = $_POST['securecode'];
		$data['payment']['amount'] = $_POST['money'];
		if( isset($_POST['howpay']) && WCUtils::is_zero($_POST['howpay']) ){	
			$data['payment']['count'] = $_POST['div'];
		}else{
			$data['payment']['count'] = '01';
		}
		$data['user']['telno'] = str_replace('-', '', $_POST['telno']);
		$data['user']['email'] = $_POST['email'];
		$data['uniq_key']['sendid'] = $_POST['sendid'];
		$data['uniq_key']['sendpoint'] = $_POST['sendpoint'];
	
	}else{	
		$data['authentication']['clientip'] = $acting_opts['clientip'];
		$data['authentication']['key'] = $acting_opts['authkey'];
		$data['card']['number'] = $_POST['cardnumber'];
		$data['card']['expires']['year'] = (int)$_POST['expyy'];
		$data['card']['expires']['month'] = (int)$_POST['expmm'];
		if( 1 == $acting_opts['security'] ){
			$data['card']['cvv'] = $_POST['securecode'];
		}
		$data['card']['name'] = $_POST['username'];
		$data['payment']['amount'] = $_POST['money'];
		if( isset($_POST['howpay']) && WCUtils::is_zero($_POST['howpay']) ){	
			$data['payment']['count'] = $_POST['div'];
		}else{
			$data['payment']['count'] = '01';
		}
		$data['user']['telno'] = str_replace('-', '', $_POST['telno']);
		$data['user']['email'] = $_POST['email'];
		$data['uniq_key']['sendid'] = $_POST['sendid'];
		$data['uniq_key']['sendpoint'] = $_POST['sendpoint'];
	}
		
	$EnrolReq = '<?xml version="1.0" encoding="utf-8"?>';
	$EnrolReq .= '<request service="secure_link_3d" action="enroll">';
	$EnrolReq .= usces_assoc2xml($data); 
	$EnrolReq .= '</request>';

	usces_log('EnrolReq : ' . print_r($EnrolRes, true), 'acting_transaction.log');

	$xml = usces_get_xml($acting_opts['card_secureurl'], $EnrolReq);
	if ( empty($xml) ){
		usces_log('zeus : EnrolRes Error', 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=EnrolReq&code=0');
		exit;
	}

	$EnrolRes = usces_xml2assoc($xml);
	usces_log('EnrolRes : ' . print_r($EnrolRes, true), 'acting_transaction.log');
	
	if( 'outside' == $EnrolRes['response']['result']['status'] ){
		
		usces_log('EnrolRes : outside', 'acting_transaction.log');
		usces_auth_order_acting_data($_POST['sendpoint']);
		
		$data = array();
		$data['xid'] = $EnrolRes['response']['xid'];//$_REQUEST['MD'];
		$PayReq = '<?xml version="1.0" encoding="utf-8" ?>';
		$PayReq .= '<request service="secure_link_3d" action="payment">';
		$PayReq .= usces_assoc2xml($data);
		$PayReq .= '</request>';
	
	//usces_log('zeus : PayReq1'.print_r($PayReq, true), 'acting_transaction.log');
		$xml = usces_get_xml($acting_opts['card_secureurl'], $PayReq);
	//usces_log('zeus : PayReq2'.print_r($xml, true), 'acting_transaction.log');
		if ( empty($xml) ){
			usces_log('zeus : PayRes Error', 'acting_transaction.log');
			header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=PayRes&code=0');
			exit;
		}


		$PayRes = usces_xml2assoc($xml);
		usces_log('usces_zeus_3dsecure_enrol : PayRes '.print_r($PayRes, true), 'acting_transaction.log');
		
		if( 'success' != $PayRes['response']['result']['status'] ){
			usces_log('zeus bad status : status=' . $PayRes['response']['result']['status'] . ' code=' . $PayRes['response']['result']['code'], 'acting_transaction.log');
			header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=' . $PayRes['response']['result']['status'] . '&code=' . $PayRes['response']['result']['code']);
			exit;
		}else{
			//usces_log('zeus : PayRes '.print_r($PayRes, true), 'acting_transaction.log');
			header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=1&zeussuffix=' . $PayRes['response']['card']['number']['suffix'] . '&zeusyear=' . $PayRes['response']['card']['expires']['year'] . '&zeusmonth=' . $PayRes['response']['card']['expires']['month'] . '&zeusordd=' . $PayRes['response']['order_number'] . '&wctid=' . $_POST['sendpoint']);
			exit;
		}
		exit;
			
	}elseif( 'success' == $EnrolRes['response']['result']['status'] ){
	
		usces_log('EnrolRes : success', 'acting_transaction.log');
		usces_auth_order_acting_data($_POST['sendpoint']);
		
		?>
		<form name="zeus" action="<?php echo $EnrolRes['response']['redirection']['acs_url']; ?>" method="post">
		<input name="MD" type="hidden" value="<?php echo $EnrolRes['response']['xid']; ?>" />
		<input name="PaReq" type="hidden" value="<?php echo $EnrolRes['response']['redirection']['PaReq']; ?>" />
		<input name="TermUrl" type="hidden" value="<?php echo USCES_CART_URL . $usces->delim . 'purchase=1&PaRes=1&sendpoint=' . $_POST['sendpoint']; ?>" />
		</form>
		<script type="text/javascript">document.zeus.submit();</script>
		<?php
	
		exit;
	
	}else{
		
		usces_log('zeus bad status : status=' . $EnrolRes['response']['result']['status'] . ' code=' . $EnrolRes['response']['result']['code'], 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=' . $EnrolRes['response']['result']['status'] . '&code=' . $EnrolRes['response']['result']['code']);
		exit;
	}

}

function usces_zeus_3dsecure_auth(){
	global $usces;
	
	$acting_opts = $usces->options['acting_settings']['zeus'];

	$data = array();
	$data['xid'] = $_REQUEST['MD'];
	$data['PaRes'] = $_REQUEST['PaRes'];
	$AuthReq = '<?xml version="1.0" encoding="utf-8" ?>';
	$AuthReq .= '<request service="secure_link_3d" action="authentication">';
	$AuthReq .= usces_assoc2xml($data); 
	$AuthReq .= '</request>';

	$xml = usces_get_xml($acting_opts['card_secureurl'], $AuthReq);
	if ( strpos($xml, 'Invalid') ){
		usces_log('zeus : AuthReq Error'.print_r($xml, true), 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=AuthReq&code=0');
		exit;
	}
	//usces_log('xml : '.print_r($xml, true), 'acting_transaction.log');
	
	$AuthRes = usces_xml2assoc($xml); 
	usces_log('usces_zeus_3dsecure_auth : AuthRes '.print_r($AuthRes, true), 'acting_transaction.log');
	
	if( 'success' != $AuthRes['response']['result']['status'] ){
		usces_log('zeus bad status : status=' . $AuthRes['response']['result']['status'] . ' code=' . $AuthRes['response']['result']['code'], 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=' . $AuthRes['response']['result']['status'] . '&code=' . $AuthRes['response']['result']['code']);
		exit;
	}
	
	
	$data = array();
	$data['xid'] = $_REQUEST['MD'];
	$PayReq = '<?xml version="1.0" encoding="utf-8" ?>';
	$PayReq .= '<request service="secure_link_3d" action="payment">';
	$PayReq .= usces_assoc2xml($data); 
	$PayReq .= '</request>';

//usces_log('zeus : PayReq1'.print_r($PayReq, true), 'acting_transaction.log');
	$xml = usces_get_xml($acting_opts['card_secureurl'], $PayReq);
//usces_log('zeus : PayReq2'.print_r($xml, true), 'acting_transaction.log');
	if ( empty($xml) ){
		usces_log('zeus : PayReq Error', 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=PayRes&code=0');
		exit;
	}
	
	$PayRes = usces_xml2assoc($xml);
	usces_log('usces_zeus_3dsecure_auth : PayRes '.print_r($PayRes, true), 'acting_transaction.log');
	
	if( 'success' != $PayRes['response']['result']['status'] ){
		usces_log('zeus bad status : status=' . $PayRes['response']['result']['status'] . ' code=' . $PayRes['response']['result']['code'], 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=' . $PayRes['response']['result']['status'] . '&code=' . $PayRes['response']['result']['code']);
		exit;
	}else{
		//usces_log('zeus : PayRes '.print_r($PayRes, true), 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=1&zeussuffix=' . $PayRes['response']['card']['number']['suffix'] . '&zeusyear=' . $PayRes['response']['card']['expires']['year'] . '&zeusmonth=' . $PayRes['response']['card']['expires']['month'] . '&zeusordd=' . $PayRes['response']['order_number'] . '&wctid=' . $_REQUEST['sendpoint']);
		exit;
	}
	exit;
}

function usces_zeus_secure_payreq(){
	global $usces;
	
	$acting_opts = $usces->options['acting_settings']['zeus'];

	$member = $usces->get_member();
	$pcid = $usces->get_member_meta_value('zeus_pcid', $member['ID']);

	$data = array();

	//if( 2 == $acting_opts['security'] && 'on' == $acting_opts['quickcharge'] && $pcid == '8888888888888888' && $usces->is_member_logged_in() ){
	if( 'on' == $acting_opts['quickcharge'] && $pcid == '8888888888888888' && $usces->is_member_logged_in() ){
		$data['authentication']['clientip'] = $acting_opts['clientip'];
		$data['authentication']['key'] = $acting_opts['authkey'];
		$data['card']['history']['key'] = 'sendid';
		$data['card']['history']['action'] = 'send_email';
		if( 1 == $acting_opts['security'] ){
			$data['card']['cvv'] = $_POST['securecode'];
		}
		$data['payment']['amount'] = $_POST['money'];
		if( isset($_POST['howpay']) && WCUtils::is_zero($_POST['howpay']) ){	
			$data['payment']['count'] = $_POST['div'];
		}else{
			$data['payment']['count'] = '01';
		}
		$data['user']['telno'] = str_replace('-', '', $_POST['telno']);
		$data['user']['email'] = $_POST['email'];
		$data['uniq_key']['sendid'] = $_POST['sendid'];
		$data['uniq_key']['sendpoint'] = $_POST['sendpoint'];
	
	}else{	
		$data['authentication']['clientip'] = $acting_opts['clientip'];
		$data['authentication']['key'] = $acting_opts['authkey'];
		$data['card']['number'] = $_POST['cardnumber'];
		$data['card']['expires']['year'] = (int)$_POST['expyy'];
		$data['card']['expires']['month'] = (int)$_POST['expmm'];
		if( 1 == $acting_opts['security'] ){
			$data['card']['cvv'] = $_POST['securecode'];
		}
		$data['card']['name'] = $_POST['username'];
		$data['payment']['amount'] = $_POST['money'];
		if( isset($_POST['howpay']) && WCUtils::is_zero($_POST['howpay']) ){	
			$data['payment']['count'] = $_POST['div'];
		}else{
			$data['payment']['count'] = '01';
		}
		$data['user']['telno'] = str_replace('-', '', $_POST['telno']);
		$data['user']['email'] = $_POST['email'];
		$data['uniq_key']['sendid'] = $_POST['sendid'];
		$data['uniq_key']['sendpoint'] = $_POST['sendpoint'];
	}

	$PayReq = '<?xml version="1.0" encoding="utf-8" ?>';
	$PayReq .= '<request service="secure_link" action="payment">';
	$PayReq .= usces_assoc2xml($data); 
	$PayReq .= '</request>';

	$xml = usces_get_xml($acting_opts['card_secureurl'], $PayReq);
	if ( empty($xml) ){
		usces_log('zeus : PayReq Error', 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=PayReq&code=0');
		exit;
	}

	usces_auth_order_acting_data($_POST['sendpoint']);

	$PayRes = usces_xml2assoc($xml);
	usces_log('usces_zeus_secure_payreq : PayRes'.print_r($PayRes, true), 'acting_transaction.log');
	
	if( 'success' == $PayRes['response']['result']['status'] ){
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=1&zeussuffix=' . $PayRes['response']['card']['number']['suffix'] . '&zeusyear=' . $PayRes['response']['card']['expires']['year'] . '&zeusmonth=' . $PayRes['response']['card']['expires']['month'] . '&zeusordd=' . $PayRes['response']['order_number'] . '&wctid=' . $_POST['sendpoint']);
		exit;
	}else{
		usces_log('zeus bad status : status=' . $PayRes['response']['result']['status'] . ' code=' . $PayRes['response']['result']['code'], 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=' . $PayRes['response']['result']['status'] . '&code=' . $PayRes['response']['result']['code']);
		exit;
	}
}

function usces_xml2assoc($xml) {
    $arr = array();
    if (!preg_match_all('|\<\s*?(\w+).*?\>(.*)\<\/\s*\\1.*?\>|s', $xml, $m)) return $xml;
    if (is_array($m[1]))
        for ($i = 0;$i < sizeof($m[1]); $i++) $arr[$m[1][$i]] = usces_xml2assoc($m[2][$i]);
    else $arr[$m[1]] = usces_xml2assoc($m[2]);

    return $arr;
}
function usces_assoc2xml($prm_array){
	$xml = '';
	if(is_array($prm_array)){
	$i=0;
		foreach ($prm_array as $index => $element){ 
			if(is_array($element)){ 
				$acts = explode('_', $index, 3);
				if( 2 < count($acts) && 'history' == $acts[0] && 'action' == $acts[1] ){
					$xml .= '<history action="' . $acts[2] . '">'; 
					$xml .= usces_assoc2xml($element); 
					$xml .= '</history>'; 
				}else{
					$xml .= '<' . $index . '>'; 
					$xml .= usces_assoc2xml($element); 
					$xml .= '</' . $index . '>'; 
				}
			}else{ 
				$xml .= '<' . $index . '>' . $element . '</' . $index . '>'; 
			}
			if($i>500) break;
		} 
	} 
	return $xml;
}
function usces_get_xml($url, $paras){
	$interface = parse_url($url);
	$header = "POST " . $interface['path'] . " HTTP/1.1\r\n";
	$header .= "Host: " . $_SERVER['HTTP_HOST'] . "\r\n";
	$header .= "User-Agent: PHP Script\r\n";
	$header .= "Content-Type: text/xml\r\n";
	$header .= "Content-Length: " . strlen($paras) . "\r\n";
	$header .= "Connection: close\r\n\r\n";
	$header .= $paras;
	$fp = fsockopen('ssl://'.$interface['host'],443,$errno,$errstr,30);
	//usces_log('header : '.print_r($header, true), 'acting_transaction.log');
	
	$xml = '';
	if ($fp){
		fwrite($fp, $header);
		while ( !feof($fp) ) {
			$xml .= fgets($fp, 1024);
		}
		fclose($fp);
	}
	//usces_log('get_return : '.print_r($xml, true), 'acting_transaction.log');
	
	return $xml;
}

//function admin_prodauct_meta_box(){
//	$wp_version = get_bloginfo('version');
//	if (version_compare($wp_version, '3.4-beta3', '<'))
//		return;
//	
//	if ( 'usces_itemedit' == $_GET['page'] && !isset($_GET['action']))
//		return;
//	
//
//}

function admin_prodauct_current_screen(){
	global $current_screen, $post;


	
	$wp_version = get_bloginfo('version');
	if (version_compare($wp_version, '3.4-beta3', '<'))
		return;
	
	if ( !(isset($_GET['page']) && (('usces_itemedit' == $_GET['page'] && isset($_GET['action'])) || 'usces_itemnew' == $_GET['page'])) )
		return;
	
	if ( isset( $_GET['post'] ) )
		$post_id = $post_ID = (int) $_GET['post'];
	elseif ( isset( $_POST['post_ID'] ) )
		$post_id = $post_ID = (int) $_POST['post_ID'];
	else
		$post_id = $post_ID = 0;

	$post_type = 'post';
	$post_type_object = get_post_type_object( $post_type );

	if ( $post_id ){
		$post = get_post( $post_id );
	}else{
		$post = get_default_post_to_edit( $post_type, true );
		$post_ID = $post->ID;
	}

	require_once(USCES_PLUGIN_DIR.'/includes/meta-boxes.php');

	add_meta_box('submitdiv', __('Publish'), 'post_submit_meta_box', $post_type, 'side', 'core');

	// all taxonomies
	foreach ( get_object_taxonomies($post_type) as $tax_name ) {
		$taxonomy = get_taxonomy($tax_name);
		if ( ! $taxonomy->show_ui )
			continue;
	
		$label = $taxonomy->labels->name;
	
		if ( !is_taxonomy_hierarchical($tax_name) )
			add_meta_box('tagsdiv-' . $tax_name, $label, 'post_tags_meta_box', $post_type, 'side', 'core');
		else
			add_meta_box($tax_name . 'div', $label, 'post_categories_meta_box', $post_type, 'side', 'core', array( 'taxonomy' => $tax_name, 'descendants_and_self' => USCES_ITEM_CAT_PARENT_ID ));
	}
	
	if ( post_type_supports($post_type, 'page-attributes') )
		add_meta_box('pageparentdiv', 'page' == $post_type ? __('Page Attributes') : __('Attributes'), 'page_attributes_meta_box', $post_type, 'side', 'core');
	
	if ( current_theme_supports( 'post-thumbnails', $post_type ) && post_type_supports($post_type, 'thumbnail') )
		add_meta_box('postimagediv', __('Featured Image'), 'post_thumbnail_meta_box', $post_type, 'side', 'low');
	
	if ( post_type_supports($post_type, 'excerpt') )
		add_meta_box('postexcerpt', __('Excerpt'), 'post_excerpt_meta_box', $post_type, 'normal', 'core');
	
	if ( post_type_supports($post_type, 'trackbacks') )
		add_meta_box('trackbacksdiv', __('Send Trackbacks'), 'post_trackback_meta_box', $post_type, 'normal', 'core');
	
	if ( post_type_supports($post_type, 'custom-fields') )
		add_meta_box('postcustom', __('Custom Fields'), 'post_custom_meta_box', $post_type, 'normal', 'core');
	
	//do_action('dbx_post_advanced');
	if ( post_type_supports($post_type, 'comments') )
		add_meta_box('commentstatusdiv', __('Discussion'), 'post_comment_status_meta_box', $post_type, 'normal', 'core');
	
	if ( (isset($post->post_status) && ('publish' == $post->post_status || 'private' == $post->post_status) ) && post_type_supports($post_type, 'comments') )
		add_meta_box('commentsdiv', __('Comments'), 'post_comment_meta_box', $post_type, 'normal', 'core');
	
	if ( !( (isset( $post->post_status ) && 'pending' == $post->post_status) && !current_user_can( $post_type_object->cap->publish_posts ) ) )
		add_meta_box('slugdiv', __('Slug'), 'post_slug_meta_box', $post_type, 'normal', 'core');
	
	if ( post_type_supports($post_type, 'author') ) {
		if ( version_compare($wp_version, '3.1', '>=') ){
			if ( is_super_admin() || current_user_can( $post_type_object->cap->edit_others_posts ) )
				add_meta_box('authordiv', __('Author'), 'post_author_meta_box', $post_type, 'normal', 'core');
		}else{
			$authors = get_editable_user_ids( $current_user->id ); // TODO: ROLE SYSTEM
			if ( isset($post->post_author) && $post->post_author && !in_array($post->post_author, $authors) )
				$authors[] = $post->post_author;
			if ( ( $authors && count( $authors ) > 1 ) || is_super_admin() )
				add_meta_box('authordiv', __('Author'), 'post_author_meta_box', $post_type, 'normal', 'core');
		}
	}
	
	if ( post_type_supports($post_type, 'revisions') && 0 < $post_ID && wp_get_post_revisions( $post_ID ) )
		add_meta_box('revisionsdiv', __('Revisions'), 'post_revisions_meta_box', $post_type, 'normal', 'core');
	
	//add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );


	$current_screen->base = $post_type;
	$current_screen->id = $post_type;
	$current_screen->post_type = $post_type;
	//usces_p($current_screen);

}

function admin_prodauct_header(){

	$wp_version = get_bloginfo('version');
	if (version_compare($wp_version, '3.4-beta3', '<'))
		return;
	
	if ( isset($_REQUEST['action'])){
	
		$suport_display = '<p>'.__('Product registration documentation','usces').'<br /><a href="http://www.welcart.com/documents/manual-2/%E6%96%B0%E8%A6%8F%E5%95%86%E5%93%81%E8%BF%BD%E5%8A%A0" target="_new">'.__('Product editing screen','usces').'</a></p>';
	
		get_current_screen()->add_help_tab( array(
			'id'      => 'suport-display',
			'title'   => 'Documents',
			'content' => $suport_display,
		) );
//	
//		$title_and_editor  = '<p>' . __('<strong>Title</strong> - Enter a title for your post. After you enter a title, you&#8217;ll see the permalink below, which you can edit.') . '</p>';
//		$title_and_editor .= '<p>' . __('<strong>Post editor</strong> - Enter the text for your post. There are two modes of editing: Visual and HTML. Choose the mode by clicking on the appropriate tab. Visual mode gives you a WYSIWYG editor. Click the last icon in the row to get a second row of controls. The HTML mode allows you to enter raw HTML along with your post text. You can insert media files by clicking the icons above the post editor and following the directions. You can go to the distraction-free writing screen via the Fullscreen icon in Visual mode (second to last in the top row) or the Fullscreen button in HTML mode (last in the row). Once there, you can make buttons visible by hovering over the top area. Exit Fullscreen back to the regular post editor.') . '</p>';
//	
//		get_current_screen()->add_help_tab( array(
//			'id'      => 'title-post-editor',
//			'title'   => __('Title and Post Editor'),
//			'content' => $title_and_editor,
//		) );
//	
//		$publish_box = '<p>' . __('<strong>Publish</strong> - You can set the terms of publishing your post in the Publish box. For Status, Visibility, and Publish (immediately), click on the Edit link to reveal more options. Visibility includes options for password-protecting a post or making it stay at the top of your blog indefinitely (sticky). Publish (immediately) allows you to set a future or past date and time, so you can schedule a post to be published in the future or backdate a post.') . '</p>';
//	
//		if ( current_theme_supports( 'post-formats' ) && post_type_supports( 'post', 'post-formats' ) ) {
//			$publish_box .= '<p>' . __( '<strong>Post Format</strong> - This designates how your theme will display a specific post. For example, you could have a <em>standard</em> blog post with a title and paragraphs, or a short <em>aside</em> that omits the title and contains a short text blurb. Please refer to the Codex for <a href="http://codex.wordpress.org/Post_Formats#Supported_Formats">descriptions of each post format</a>. Your theme could enable all or some of 10 possible formats.' ) . '</p>';
//		}
//	
//		if ( current_theme_supports( 'post-thumbnails' ) && post_type_supports( 'post', 'thumbnail' ) ) {
//			$publish_box .= '<p>' . __('<strong>Featured Image</strong> - This allows you to associate an image with your post without inserting it. This is usually useful only if your theme makes use of the featured image as a post thumbnail on the home page, a custom header, etc.') . '</p>';
//		}
//	
//		get_current_screen()->add_help_tab( array(
//			'id'      => 'publish-box',
//			'title'   => __('Publish Box'),
//			'content' => $publish_box,
//		) );
//	
//		$discussion_settings  = '<p>' . __('<strong>Send Trackbacks</strong> - Trackbacks are a way to notify legacy blog systems that you&#8217;ve linked to them. Enter the URL(s) you want to send trackbacks. If you link to other WordPress sites they&#8217;ll be notified automatically using pingbacks, and this field is unnecessary.') . '</p>';
//		$discussion_settings .= '<p>' . __('<strong>Discussion</strong> - You can turn comments and pings on or off, and if there are comments on the post, you can see them here and moderate them.') . '</p>';
//	
//		get_current_screen()->add_help_tab( array(
//			'id'      => 'discussion-settings',
//			'title'   => __('Discussion Settings'),
//			'content' => $discussion_settings,
//		) );
//	
//		get_current_screen()->set_help_sidebar(
//				'<p>' . sprintf(__('You can also create posts with the <a href="%s">Press This bookmarklet</a>.'), 'options-writing.php') . '</p>' .
//				'<p><strong>' . __('For more information:') . '</strong></p>' .
//				'<p>' . __('<a href="http://codex.wordpress.org/Posts_Add_New_Screen" target="_blank">Documentation on Writing and Editing Posts</a>') . '</p>' .
//				'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
//		);
	}else{
		
	}
}

function admin_new_prodauct_header(){

	$wp_version = get_bloginfo('version');
	if (version_compare($wp_version, '3.4-beta3', '<'))
		return;
	
	$customize_display = '<p>' . __('The title field and the big Post Editing Area are fixed in place, but you can reposition all the other boxes using drag and drop, and can minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to unhide more boxes (Excerpt, Send Trackbacks, Custom Fields, Discussion, Slug, Author) or to choose a 1- or 2-column layout for this screen.') . '</p>';

	get_current_screen()->add_help_tab( array(
		'id'      => 'customize-display',
		'title'   => __('Customizing This Display'),
		'content' => $customize_display,
	) );
//
//	$title_and_editor  = '<p>' . __('<strong>Title</strong> - Enter a title for your post. After you enter a title, you&#8217;ll see the permalink below, which you can edit.') . '</p>';
//	$title_and_editor .= '<p>' . __('<strong>Post editor</strong> - Enter the text for your post. There are two modes of editing: Visual and HTML. Choose the mode by clicking on the appropriate tab. Visual mode gives you a WYSIWYG editor. Click the last icon in the row to get a second row of controls. The HTML mode allows you to enter raw HTML along with your post text. You can insert media files by clicking the icons above the post editor and following the directions. You can go to the distraction-free writing screen via the Fullscreen icon in Visual mode (second to last in the top row) or the Fullscreen button in HTML mode (last in the row). Once there, you can make buttons visible by hovering over the top area. Exit Fullscreen back to the regular post editor.') . '</p>';
//
//	get_current_screen()->add_help_tab( array(
//		'id'      => 'title-post-editor',
//		'title'   => __('Title and Post Editor'),
//		'content' => $title_and_editor,
//	) );
//
//	$publish_box = '<p>' . __('<strong>Publish</strong> - You can set the terms of publishing your post in the Publish box. For Status, Visibility, and Publish (immediately), click on the Edit link to reveal more options. Visibility includes options for password-protecting a post or making it stay at the top of your blog indefinitely (sticky). Publish (immediately) allows you to set a future or past date and time, so you can schedule a post to be published in the future or backdate a post.') . '</p>';
//
//	if ( current_theme_supports( 'post-formats' ) && post_type_supports( 'post', 'post-formats' ) ) {
//		$publish_box .= '<p>' . __( '<strong>Post Format</strong> - This designates how your theme will display a specific post. For example, you could have a <em>standard</em> blog post with a title and paragraphs, or a short <em>aside</em> that omits the title and contains a short text blurb. Please refer to the Codex for <a href="http://codex.wordpress.org/Post_Formats#Supported_Formats">descriptions of each post format</a>. Your theme could enable all or some of 10 possible formats.' ) . '</p>';
//	}
//
//	if ( current_theme_supports( 'post-thumbnails' ) && post_type_supports( 'post', 'thumbnail' ) ) {
//		$publish_box .= '<p>' . __('<strong>Featured Image</strong> - This allows you to associate an image with your post without inserting it. This is usually useful only if your theme makes use of the featured image as a post thumbnail on the home page, a custom header, etc.') . '</p>';
//	}
//
//	get_current_screen()->add_help_tab( array(
//		'id'      => 'publish-box',
//		'title'   => __('Publish Box'),
//		'content' => $publish_box,
//	) );
//
//	$discussion_settings  = '<p>' . __('<strong>Send Trackbacks</strong> - Trackbacks are a way to notify legacy blog systems that you&#8217;ve linked to them. Enter the URL(s) you want to send trackbacks. If you link to other WordPress sites they&#8217;ll be notified automatically using pingbacks, and this field is unnecessary.') . '</p>';
//	$discussion_settings .= '<p>' . __('<strong>Discussion</strong> - You can turn comments and pings on or off, and if there are comments on the post, you can see them here and moderate them.') . '</p>';
//
//	get_current_screen()->add_help_tab( array(
//		'id'      => 'discussion-settings',
//		'title'   => __('Discussion Settings'),
//		'content' => $discussion_settings,
//	) );
//
//	get_current_screen()->set_help_sidebar(
//			'<p>' . sprintf(__('You can also create posts with the <a href="%s">Press This bookmarklet</a>.'), 'options-writing.php') . '</p>' .
//			'<p><strong>' . __('For more information:') . '</strong></p>' .
//			'<p>' . __('<a href="http://codex.wordpress.org/Posts_Add_New_Screen" target="_blank">Documentation on Writing and Editing Posts</a>') . '</p>' .
//			'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
//	);
}

function usces_clear_quickcharge( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'usces_member_meta';
//$wpdb->show_errors();
	$query = $wpdb->prepare( "DELETE FROM $table_name WHERE meta_key = %s", $id );
	$res = $wpdb->query( $query );

	return $res;
}

function usces_get_order_number( $page ) {
	if( empty($page) ) return '';

	$log = explode( "\r\n", $page );
	$ordd = '';
	foreach( (array)$log as $line ) {
		//if( false !== strpos( $line, 'Success_order' ) ) {
		if( false !== strpos( $line, 'ordd' ) ) {
			//list( $status, $ordd ) = explode( "\n", $line );
			list( $status, $ordd ) = explode( "=", $line );
		}
	}
	return $ordd;
}

function usces_get_err_code( $page ) {
	if( empty($page) ) return '';

	$log = explode( "\r\n", $page );
	$err_code = '';
	foreach( (array)$log as $line ) {
		if( false !== strpos( $line, 'err_code' ) ) {
			list( $name, $err_code ) = explode( "=", $line );
		}
	}
	return $err_code;
}

?>
