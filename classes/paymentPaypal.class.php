<?php
class usces_paypal {

	var $options;

	var $API_UserName;
	var $API_Password;
	var $API_Signature;

	var $sBNCode;
	var $version;

	var $method;
	var $data;
	var $nvpreq;
	var $resArray;

	function usces_paypal() {
		$this->options = get_option('usces');
		$this->API_UserName = urlencode($this->options['acting_settings']['paypal']['user']);
		$this->API_Password = urlencode($this->options['acting_settings']['paypal']['pwd']);
		$this->API_Signature = urlencode($this->options['acting_settings']['paypal']['signature']);
		$this->sBNCode = urlencode("uscons_cart_EC_JP");
		$this->version = urlencode("66.0");//20110412ysk
		$this->method = '';
		$this->data = '';
		$this->nvpreq = '';
		$this->resArray = array();
	}

	function setMethod($method) {$this->method = $method;}
	function setData($data) {$this->data = $data;}
	function getResponse() {return $this->resArray;}

	function doExpressCheckout() {
		$status = true;

		$this->nvpreq = "METHOD=".$this->method
			."&VERSION=".$this->version
			."&USER=".$this->API_UserName
			."&PWD=".$this->API_Password
			."&SIGNATURE=".$this->API_Signature
			.$this->data
			."&BUTTONSOURCE=".$this->sBNCode;

		if(extension_loaded('curl')) {
			//setting the curl parameters.
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->options['acting_settings']['paypal']['api_endpoint']);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);

			//turning off the server and peer verification(TrustManager Concept).
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);

			//setting the nvpreq as POST FIELD to curl
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->nvpreq);

			//getting response from server
			$response = curl_exec($ch);

			if(curl_errno($ch)) {
				usces_log('PayPal : API call failed. curl_error_no:['.curl_errno($ch).'] curl_error_msg:'.curl_error($ch), 'acting_transaction.log');
				$status = false;

			} else {
				//closing the curl
				curl_close($ch);
			}

			$this->resArray = $this->deformatNVP($response);

		} else {
			//usces_log(urldecode($this->nvpreq), 'acting_transaction.log');
			$r = new usces_httpRequest($this->options['acting_settings']['paypal']['api_host'], '/nvp', 'POST', true);
			$result = $r->connect($this->nvpreq);
			if($result >= 400) {
				usces_log('PayPal : API call failed. result:['.$result.']', 'acting_transaction.log');
				$status = false;
			}

			$this->resArray = $this->deformatNVP($r->getContent());
		}
		return $status;
	}

	private function deformatNVP($nvpstr) {
		$intial = 0;
		$nvpArray = array();

		while(strlen($nvpstr)) {
			//postion of Key
			$keypos = strpos($nvpstr, '=');
			//position of value
			$valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);

			/*getting the Key and Value values and storing in a Associative Array*/
			$keyval = substr($nvpstr, $intial, $keypos);
			$valval = substr($nvpstr, $keypos+1, $valuepos-$keypos-1);
			//decoding the respose
			$nvpArray[urldecode($keyval)] = urldecode( $valval);
			$nvpstr = substr($nvpstr, $valuepos+1, strlen($nvpstr));
		}
		return $nvpArray;
	}
}
?>