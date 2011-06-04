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
			
		}elseif( (true === $usces->is_member_logged_in() && '' == $usces->page) || 'member' == $usces->page || 'editmemberform' == $usces->page || 'newmemberform' == $usces->page ){
			
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

	$data = array();
	$data['authentication']['clientip'] = $acting_opts['clientip'];
	$data['authentication']['key'] = '356a192b7913b04c54574d18c28d46e6395428ab';
	$data['card']['number'] = $_POST['cardnumber'];
	$data['card']['expires']['year'] = substr($_POST['expyy'], 2);
	$data['card']['expires']['month'] = $_POST['expmm'];
	$data['card']['cvv'] = $_POST['securecode'];
	$data['card']['name'] = $_POST['username'];
	$data['payment']['amount'] = $_POST['money'];
	if( isset($_POST['howpay']) && '0' === $_POST['howpay'] ){	
		$data['payment']['count'] = $_POST['div'];
	}else{
		$data['payment']['count'] = 1;
	}
	$data['user']['telno'] = str_replace('-', '', $_POST['telno']);
	$data['user']['email'] = $_POST['email'];
	$data['uniq_key']['sendid'] = $_POST['sendid'];
	$data['uniq_key']['sendpoint'] = $_POST['sendpoint'];
	
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

	$xml = usces_get_xml($acting_opts['card3d_url'], $EnrolReq);
	if ( empty($xml) ){
		usces_log('zeus : EnrolRes Error', 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=EnrolReq&code=0');
		exit;
	}
//usces_log('zeus xml : ' . $xml, 'acting_transaction.log');

	$EnrolRes = usces_xml2assoc($xml); 
//usces_log('zeus xml : ' . print_r($EnrolRes, true), 'acting_transaction.log');
	if( 'success' != $EnrolRes['response']['result']['status'] ){
		usces_log('zeus bad status : status=' . $EnrolRes['response']['result']['status'] . ' code=' . $EnrolRes['response']['result']['code'], 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=' . $EnrolRes['response']['result']['status'] . '&code=' . $EnrolRes['response']['result']['code']);
		exit;
	}

	$data = array();
	$data['MD'] = base64_encode($EnrolRes['xid']);
	$data['PaReq'] = $EnrolRes['redirection']['PaReq'];
	$data['TermUrl'] = USCES_CART_URL . $usces->delim . 'purchase=1';
	$PaReq = '<?xml version="1.0" encoding="utf-8" ?>';
	$PaReq .= '<request service="secure_link_3d" action="enroll">';
	$PaReq .= usces_assoc2xml($data); 
	$PaReq .= '</request>';

	$xml = usces_get_xml($EnrolRes['redirection']['acs_url'], $PaReq);
	if ( empty($xml) ){
		usces_log('zeus : PaRes Error', 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=PaRes&code=0');
		exit;
	}
	
//	$PaRes = usces_xml2assoc($xml); 
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
	$data['MD'] = $_REQUEST['xid'];
	$data['PaRes'] = $_REQUEST['PaRes'];
	$AuthReq = '<?xml version="1.0" encoding="utf-8" ?>';
	$AuthReq .= '<request service="secure_link_3d" action="enroll">';
	$AuthReq .= usces_assoc2xml($data); 
	$AuthReq .= '</request>';

	$xml = usces_get_xml($acting_opts['card_url'], $AuthReq);
	if ( empty($xml) ){
		usces_log('zeus : AuthReq Error', 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=AuthReq&code=0');
		exit;
	}
	
	$AuthRes = usces_xml2assoc($xml); 
	if( 'success' != $AuthRes['response']['result']['status'] ){
		usces_log('zeus bad status : status=' . $AuthRes['response']['result']['status'] . ' code=' . $AuthRes['response']['result']['code'], 'acting_transaction.log');
		header("Location: " . USCES_CART_URL . $usces->delim . 'acting=zeus_card&acting_return=0&status=' . $AuthRes['response']['result']['status'] . '&code=' . $AuthRes['response']['result']['code']);
		exit;
	}
	
	
	$data = array();
	$data['xid'] = $_REQUEST['xid'];
	$PayReq = '<?xml version="1.0" encoding="utf-8" ?>';
	$PayReq .= '<request service="secure_link_3d" action="enroll">';
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
	
	$xml = '';
	if ($fp){
		fwrite($fp, $header);
		while ( !feof($fp) ) {
			$xml .= fgets($fp, 1024);
		}
		fclose($fp);
	}
	
	return $xml;
}

?>