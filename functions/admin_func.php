<?php
function usces_states_form_js(){
	global $usces;
	
	$js = '';
	if( $usces->use_js 
			&& ((  (is_page(USCES_MEMBER_NUMBER) || $usces->is_member_page($_SERVER['REQUEST_URI'])) && ((true === $usces->is_member_logged_in() && '' == $usces->page) || 'member' == $usces->page || 'editmemberform' == $usces->page || 'newmemberform' == $usces->page)  )
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
			
		}elseif( (true === $usces->is_member_logged_in() && '' == $usces->page) || (true === $usces->is_member_logged_in() && 'member' == $usces->page) || 'editmemberform' == $usces->page || 'newmemberform' == $usces->page ){
			
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

	if( 'on' == $acting_opts['quickcharge'] && $pcid == '8888888888888888' && $usces->is_member_logged_in() ){
		$data['authentication']['clientip'] = $acting_opts['clientip'];
		$data['authentication']['key'] = $acting_opts['authkey'];
		$data['card']['history']['key'] = $_POST['sendid'];
		$data['card']['history']['action'] = 'send_email';
		$data['card']['cvv'] = $_POST['securecode'];
		$data['payment']['amount'] = $_POST['money'];
		if( isset($_POST['howpay']) && '0' === $_POST['howpay'] ){	
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
		$data['card']['cvv'] = $_POST['securecode'];
		$data['card']['name'] = $_POST['username'];
		$data['payment']['amount'] = $_POST['money'];
		if( isset($_POST['howpay']) && '0' === $_POST['howpay'] ){	
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

//$assoc = usces_xml2assoc($EnrolReq); 
//echo '<pre>';
//print_r($assoc);
//echo '</pre>';
//
//
//$xml = usces_assoc2xml($assoc); 
//echo '<pre>';
//print_r($xml);
//echo '</pre>';

	$xml = usces_get_xml($acting_opts['card_secureurl'], $EnrolReq);
	if ( empty($xml) ){
		usces_log('zeus : EnrolRes Error', 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=EnrolReq&code=0');
		exit;
	}
//usces_log('zeus xml : ' . $xml, 'acting_transaction.log');

	$EnrolRes = usces_xml2assoc($xml); 
//usces_log('EnrolRes : ' . print_r($EnrolRes, true), 'acting_transaction.log');
	if( 'success' != $EnrolRes['response']['result']['status'] ){
		usces_log('zeus bad status : status=' . $EnrolRes['response']['result']['status'] . ' code=' . $EnrolRes['response']['result']['code'], 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=' . $EnrolRes['response']['result']['status'] . '&code=' . $EnrolRes['response']['result']['code']);
		exit;
	}

	$data = array();
	$data['MD'] = $EnrolRes['response']['xid'];
	$data['PaReq'] = $EnrolRes['response']['redirection']['PaReq'];
	$data['TermUrl'] = USCES_CART_URL . $usces->delim . 'purchase=1&PaRes=1';
	$PaReq = http_build_query($data);
	$PaReq = 'MD='.$EnrolRes['response']['xid'].'&PaReq='.$EnrolRes['response']['redirection']['PaReq'].'&TermUrl='.USCES_CART_URL . $usces->delim . 'purchase=1&PaRes=1';
/*	$PaReq = '<?xml version="1.0" encoding="utf-8" ?>';
	$PaReq .= '<request service="secure_link_3d" action="enroll">';
	$PaReq .= usces_assoc2xml($data); 
	$PaReq .= '</request>';
*/
?>
<form name="zeus" action="<?php echo $EnrolRes['response']['redirection']['acs_url']; ?>" method="post">
<input name="MD" type="hidden" value="<?php echo $EnrolRes['response']['xid']; ?>" />
<input name="PaReq" type="hidden" value="<?php echo $EnrolRes['response']['redirection']['PaReq']; ?>" />
<input name="TermUrl" type="hidden" value="<?php echo USCES_CART_URL . $usces->delim . 'purchase=1&PaRes=1'; ?>" />
</form>
<script type="text/javascript">document.zeus.submit();</script>
<?php
/*	$xml = usces_get_xml($EnrolRes['response']['redirection']['acs_url'], $PaReq);
	if ( empty($xml) ){
		usces_log('zeus : PaRes Error', 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=PaRes&code=0');
		exit;
	}
	print($xml);
*///	$PaRes = usces_xml2assoc($xml); 
//	if( 'success' != $PaRes['response']['result']['status'] ){
//		usces_log('zeus bad status : status=' . $PaRes['response']['result']['status'] . ' code=' . $PaRes['response']['result']['code'], 'acting_transaction.log');
//		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=' . $PaRes['response']['result']['status'] . '&code=' . $PaRes['response']['result']['code']);
//		exit;
//	}
	exit;
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

	$xml = usces_get_xml($acting_opts['card_url'], $AuthReq);
	if ( strpos($xml, 'Invalid') ){
		usces_log('zeus : AuthReq Error'.print_r($xml, true), 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=AuthReq&code=0');
		exit;
	}
	//usces_log('xml : '.print_r($xml, true), 'acting_transaction.log');
	
	$AuthRes = usces_xml2assoc($xml); 
	usces_log('AuthRes : '.print_r($AuthRes, true), 'acting_transaction.log');
	if( 'success' != $AuthRes['request']['result']['status'] ){
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

	$xml = usces_get_xml($acting_opts['card_url'], $PayReq);
	if ( empty($xml) ){
		usces_log('zeus : PayRes Error', 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=PayRes&code=0');
		exit;
	}
	
	$PayRes = usces_xml2assoc($xml); 
	if( 'success' != $PayRes['response']['result']['status'] ){
		usces_log('zeus bad status : status=' . $PayRes['response']['result']['status'] . ' code=' . $PayRes['response']['result']['code'], 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=' . $PayRes['response']['result']['status'] . '&code=' . $PayRes['response']['result']['code']);
		exit;
	}else{
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=1');
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

	if( 'on' == $acting_opts['quickcharge'] && $pcid == '8888888888888888' && $usces->is_member_logged_in() ){
		$data['authentication']['clientip'] = $acting_opts['clientip'];
		$data['authentication']['key'] = $acting_opts['authkey'];
		$data['card']['history']['key'] = $_POST['sendid'];
		$data['card']['history']['action'] = 'send_email';
		$data['card']['cvv'] = $_POST['securecode'];
		$data['payment']['amount'] = $_POST['money'];
		if( isset($_POST['howpay']) && '0' === $_POST['howpay'] ){	
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
		$data['card']['cvv'] = $_POST['securecode'];
		$data['card']['name'] = $_POST['username'];
		$data['payment']['amount'] = $_POST['money'];
		if( isset($_POST['howpay']) && '0' === $_POST['howpay'] ){	
			$data['payment']['count'] = $_POST['div'];
		}else{
			$data['payment']['count'] = '01';
		}
		$data['user']['telno'] = str_replace('-', '', $_POST['telno']);
		$data['user']['email'] = $_POST['email'];
		$data['uniq_key']['sendid'] = $_POST['sendid'];
		$data['uniq_key']['sendpoint'] = $_POST['sendpoint'];
	}
		
	$PayReq = '<?xml version="1.0" encoding="utf-8"?>';
	$PayReq .= '<request service="secure_link_3d" action="enroll">';
	$PayReq .= usces_assoc2xml($data); 
	$PayReq .= '</request>';

	$xml = usces_get_xml($acting_opts['card_secureurl'], $PayReq);
	if ( empty($xml) ){
		usces_log('zeus : EnrolRes Error', 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=PayReq&code=0');
		exit;
	}

	$PayRes = usces_xml2assoc($xml); 
//usces_log('zeus xml : ' . print_r($EnrolRes, true), 'acting_transaction.log');
	if( 'success' == $PayRes['response']['result']['status'] ){
		return 'success';
	}else{
		usces_log('zeus bad status : status=' . $PayRes['response']['result']['status'] . ' code=' . $PayRes['response']['result']['code'], 'acting_transaction.log');
		return 'error';
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
				$xml .= '<' . $index . '>'; 
				$xml .= usces_assoc2xml($element); 
				$xml .= '</' . $index . '>'; 
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

?>