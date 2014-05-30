<?php
//*****************************************************************************
//cart.class.php
//*****************************************************************************
class usces_cart {

	var $serial;

	function usces_cart() {
	
		if ( !isset($_SESSION['usces_cart']) ) {
			$_SESSION['usces_cart'] = array();
			$_SESSION['usces_entry'] = array();
		}
		
	}
	// into Cart ***************************************************************
	function inCart() {
		global $usces;
		$_POST = $usces->stripslashes_deep_post($_POST);
		
		if( $_SERVER['HTTP_REFERER'] ){
			$_SESSION['usces_previous_url'] = $_SERVER['HTTP_REFERER'];
		}else{
			$_SESSION['usces_previous_url'] = str_replace('https://', 'http://', get_home_url()).'/';
		}
			
		$ids = array_keys($_POST['inCart']);
		$post_id = $ids[0];
		
		$skus = array_keys($_POST['inCart'][$post_id]);
		$sku = $skus[0];
			
		$this->in_serialize($post_id, $sku);
		if( !isset($_SESSION['usces_cart'][$this->serial]['quant']) ){
			$_SESSION['usces_cart'][$this->serial]['quant'] = 0;
		}
		
		if ( isset($_POST['quant'][$post_id][$sku]) && !WCUtils::is_blank($_POST['quant'][$post_id][$sku]) ) {
		
			$post_quant = (int)$_POST['quant'][$post_id][$sku];
			$_SESSION['usces_cart'][$this->serial]['quant'] = apply_filters('usces_filter_post_quant', $post_quant, $_SESSION['usces_cart'][$this->serial]['quant']);
			
		} else {
		
			if ( isset($_SESSION['usces_cart'][$this->serial]) )
				$_SESSION['usces_cart'][$this->serial]['quant'] = apply_filters('usces_filter_post_quant', 1, $_SESSION['usces_cart'][$this->serial]['quant']);
			else
				$_SESSION['usces_cart'][$this->serial]['quant'] = 1;
				
			$_SESSION['usces_cart'][$this->serial]['quant'] = apply_filters('usces_filter_inCart_quant', $_SESSION['usces_cart'][$this->serial]['quant']);

		}
		
		
		if ( isset($_POST['skuPrice']) && !WCUtils::is_blank($_POST['skuPrice'][$post_id][$sku]) ) {
			$price = $this->get_realprice($post_id, $sku, $_SESSION['usces_cart'][$this->serial]['quant']);
			$price = apply_filters('usces_filter_inCart_price', $price, $this->serial);
			$_SESSION['usces_cart'][$this->serial]['price'] = $price;
		}
		
		if ( isset($_POST['advance']) ) {
			$_SESSION['usces_cart'][$this->serial]['advance'] = $this->wc_serialize($_POST['advance']);
		}
		
		do_action('usces_action_after_inCart', $this->serial);
	}

	// update Cart ***************************************************************
	function upCart() {
	
		if(!isset($_POST['quant'])) return false;
		
		global $usces;
		$_POST = $usces->stripslashes_deep_post($_POST);

		foreach($_POST['quant'] as $index => $vs){
			
			if( !is_array($vs) )
				break;
				
			$ids = array_keys($vs);
			$post_id = $ids[0];
			
			$skus = array_keys($vs[$post_id]);
			$sku = $skus[0];
			
			$this->up_serialize($index, $post_id, $sku);
		
			if ( !WCUtils::is_blank($_POST['quant'][$index][$post_id][$sku]) ) {
		
				$_SESSION['usces_cart'][$this->serial]['quant'] = (int)$_POST['quant'][$index][$post_id][$sku];
				$_SESSION['usces_cart'][$this->serial]['advance'] = isset($_POST['advance'][$index][$post_id][$sku]) ? $this->wc_unserialize($_POST['advance'][$index][$post_id][$sku]) : array();
				if( isset($_POST['order_action']) ){
					$price = (int)$_POST['skuPrice'][$index][$post_id][$sku];
				}else{
					$price = $this->get_realprice($post_id, $sku, $_SESSION['usces_cart'][$this->serial]['quant']);
					$price = apply_filters('usces_filter_upCart_price', $price, $this->serial, $index);
				}
				$_SESSION['usces_cart'][$this->serial]['price'] = $price;
			
			}
		}

		unset( $_SESSION['usces_entry']['order']['usedpoint'] );
		do_action('usces_action_after_upCart');
	}
	
	// inCart_advance ****************************************************************
	function inCart_advance( $serial, $name, $key, $value ){
		$_SESSION['usces_cart'][$serial]['advance'][$name][$key] = $value;
	}

	// serialize ****************************************************************
	function wc_serialize( $value ){
		$out = NULL;
		if( !empty($value) )
//			$out = urlencode(serialize($value));
			$out = serialize($value);
		return $out;
	}

	function wc_unserialize( $str ){
		$out = array();
		if( !empty($str) )
//			$out = unserialize(urldecode($str));
			$out = unserialize($str);
		return $out;
	}

	// get data by index ****************************************************************
//	function get_row($index) {
//
//		if ( !isset($_SESSION['usces_cart']['post_id'][$index]) ) {
//			return false;
//
//		} else {
//			$post_id = $_SESSION['usces_cart']['post_id'][$index];
//			$item_quantity = $_SESSION['usces_cart']['item_quantity'][$index];
//
//			$rows = compact('post_id', 'item_quantity');
//			return $rows;
//		}
//	}

	// delete data by index ***************************************************************
	function del_row() {

		$indexs = array_keys($_POST['delButton']);
		$index = $indexs[0];
		$ids = array_keys($_POST['delButton'][$index]);
		$post_id = $ids[0];
		$skus = array_keys($_POST['delButton'][$index][$post_id]);
		$sku = $skus[0];
		
		$this->up_serialize($index, $post_id, $sku);
		do_action('usces_cart_del_row', $index);
		
		if(isset($_SESSION['usces_cart'][$this->serial]))
			unset($_SESSION['usces_cart'][$this->serial]);
			
		unset( $_SESSION['usces_entry']['order']['usedpoint'] );
		do_action('usces_action_after_cart_del_row', $index);
	}

	// number of data in cart ***********************************************************
	function num_row() {
		if( !isset($_SESSION['usces_cart']) )
			return false;
			
		$num = count($_SESSION['usces_cart']);
		
		if( $num > 0 ) {
			return $num;
		} else {
			return false;
		}
	}

	// clear cart ****************************************************************
	function crear_cart() {
			$_SESSION['usces_cart'] = array();
			$_SESSION['usces_entry'] = array();
	}
	
	// cart associative array ***********************************************************
	function get_cart() {
	
		if(!isset($_SESSION['usces_cart'])) return false;
		
		$rows = array();
		
		$i = 0;
		foreach($_SESSION['usces_cart'] as $serial => $qua) { 
			$array = $this->key_unserialize($serial);

			$rows[$i] = $array;
			
			$i++;
		}

		return $rows;
	}
	
	// insert serialize **************************************************************
	function in_serialize($id, $sku){
	
		global $usces;
		$_POST = $usces->stripslashes_deep_post($_POST);

		if(isset($_POST['itemOption'])){
			foreach( $_POST['itemOption'][$id][$sku] as $key => $value ){
//20110629ysk start 0000190
				//$pots[$key] = urlencode($value);
				if( is_array($value) ) {
					foreach( $value as $k => $v ) {
						$pots[$key][urlencode(trim($v))] = urlencode(trim($v));
					}
				} else {
					$pots[$key] = urlencode($value);
				}
//20110629ysk end
			}
			$sels[$id][$sku] = $pots;
		}else{
			$sels[$id][$sku] = 0;
		}
		$sels = apply_filters('usces_filter_in_serialize', $sels, $id, $sku);
		$this->serial = serialize($sels);
	}

	// update serialize **************************************************************
	function up_serialize($index, $id, $sku){
	
		global $usces;
		$_POST = $usces->stripslashes_deep_post($_POST);

		if(isset($_POST['itemOption'][$index])){
			foreach( $_POST['itemOption'][$index][$id][$sku] as $key => $value ){
//20110629ysk start 0000190
				//$pots[$key] = $value;
				if( is_array($value) ) {
					foreach( $value as $k => $v ) {
						$pots[$key][$v] = $v;
					}
				} else {
					$pots[$key] = $value;
				}
//20110629ysk end
			}
			$sels[$id][$sku] = $pots;
		}else{
			$sels[$id][$sku] = 0;
		}
		$sels = apply_filters('usces_filter_up_serialize', $sels, $index, $id, $sku);
		$this->serial = serialize($sels);
	}

	// key unserialize **************************************************************
	function key_unserialize($serial){

		$array = unserialize($serial);
		$ids = array_keys($array);
		$skus = array_keys($array[$ids[0]]);

		$row['serial'] = $serial;
		$row['post_id'] = $ids[0];
		$row['sku'] = $skus[0];
		$row['options'] = apply_filters('usces_filter_key_unserialize_options', $array[$ids[0]][$skus[0]], $ids[0], $skus[0]);
		$row['price'] = isset($_SESSION['usces_cart'][$serial]['price']) ? $_SESSION['usces_cart'][$serial]['price'] : 0;
		$row['quantity'] = $_SESSION['usces_cart'][$serial]['quant'];
		$row['advance'] = isset($_SESSION['usces_cart'][$serial]['advance']) ? $_SESSION['usces_cart'][$serial]['advance'] : array();
		
		return $row;
	}

	// is order condition ***************************************************************
	function is_order_condition() {
		if ( isset($_SESSION['usces_entry']['condition']) )
			return true;
		else
			return false;
	}
	
	// set order condition ***************************************************************
	function set_order_condition( $conditions ) {
		foreach( $conditions as $key => $value )
		$_SESSION['usces_entry']['condition'][$key] = $value;
	}
	
	// get order condition ***************************************************************
	function get_order_condition() {
		if(isset($_SESSION['usces_entry']['condition']))
			return $_SESSION['usces_entry']['condition'];
		else
			return NULL;
	}
	
	// entry information ***************************************************************
	function set_order_entry( $array ) {
		foreach ( $array as $key => $value )
			$_SESSION['usces_entry']['order'][$key] = $value;
	}
//20110203ysk start
	// get entry information ***************************************************************
	function get_order_entry( $key ) {
		return $_SESSION['usces_entry']['order'][$key];
	}
//20110203ysk end
	// entry information ***************************************************************
	function entry() {
		global $usces;
		$_POST = $usces->stripslashes_deep_post($_POST);

		if(isset($_SESSION['usces_member']['ID']) && !empty($_SESSION['usces_member']['ID'])) {
//20110126ysk start
			if($usces->page !== 'confirm') {
				foreach( $_SESSION['usces_member'] as $key => $value ){
//20100818ysk start
					if($key === 'custom_member') {
//20101008ysk start
						unset($_SESSION['usces_entry']['custom_member']);
//20101008ysk end
						foreach( $_SESSION['usces_member']['custom_member'] as $mbkey => $mbvalue ) {
							if(empty($_SESSION['usces_entry']['custom_customer'][$mbkey])) {
								if( is_array($mbvalue) ) {
									foreach( $mbvalue as $k => $v ) {
										$_SESSION['usces_entry']['custom_customer'][$mbkey][$v] = $v;
									}
								} else {
									$_SESSION['usces_entry']['custom_customer'][$mbkey] = $mbvalue;
								}
							}
						}
					} else {
						//if(empty($_SESSION['usces_entry']['customer'][$key])) {
							if( 'country' == $key && empty($value) ){
								$_SESSION['usces_entry']['customer'][$key] = usces_get_base_country();//20110513ysk
							}else{
								$_SESSION['usces_entry']['customer'][$key] = trim($value);
							}
						//}
					}
//20100818ysk end
				}
			}
		}
		//} else if(isset($_POST['customer']))	{	
		if(isset($_POST['customer']))	{	
			foreach( $_POST['customer'] as $key => $value ){
				if( 'country' == $key && empty($value) ){
					$_SESSION['usces_entry']['customer'][$key] = usces_get_base_country();//20110513ysk
				}else{
					$_SESSION['usces_entry']['customer'][$key] = trim($value);
				}
			}
		}
//20110126ysk end
		
		if(isset($_POST['delivery']))	{	
			foreach( $_POST['delivery'] as $key => $value )
				if( 'country' == $key && empty($value) ){
					$_SESSION['usces_entry']['delivery'][$key] = usces_get_base_country();//20110513ysk
				}else{
					$_SESSION['usces_entry']['delivery'][$key] = trim($value);
				}
		}
		
		if(isset($_POST['delivery']['delivery_flag']) && $_POST['delivery']['delivery_flag'] == 0)	{	
			foreach( $_SESSION['usces_entry']['customer'] as $key => $value )
				if( 'country' == $key && empty($value) ){
					$_SESSION['usces_entry']['delivery'][$key] = usces_get_base_country();//20110513ysk
				}else{
					$_SESSION['usces_entry']['delivery'][$key] = trim($value);
				}
		}

		if(isset($_POST['offer']))	{	
			foreach( $_POST['offer'] as $key => $value )
				$_SESSION['usces_entry']['order'][$key] = trim($value);
		}
		
		if(isset($_POST['reserve'])) {
			foreach( $_POST['reserve'] as $key => $value )
				$_SESSION['usces_entry']['reserve'][$key] = trim($value);
		}
//20100809ysk start
		if(isset($_POST['custom_order'])) {
//20101008ysk start
			unset($_SESSION['usces_entry']['custom_order']);
//20101008ysk end
			foreach( $_POST['custom_order'] as $key => $value )
				if ( is_array($value) ) {
					foreach( $value as $k => $v ) {
						$_SESSION['usces_entry']['custom_order'][$key][trim($v)] = trim($v);
					}
				} else {
					$_SESSION['usces_entry']['custom_order'][$key] = trim($value);
				}
		}
//20100809ysk end
//20110106ysk start
		if( isset($_SESSION['usces_entry']['delivery']['delivery_flag']) && $_SESSION['usces_entry']['delivery']['delivery_flag'] == 0 ) {//20131009ysk
			$this->set_custom_customer_delivery();
		}
//20110106ysk end
//20100818ysk start
		if(isset($_POST['custom_customer'])) {
//20101008ysk start
			unset($_SESSION['usces_entry']['custom_customer']);
//20101008ysk end
			foreach( $_POST['custom_customer'] as $key => $value )
				if ( is_array($value) ) {
					foreach( $value as $k => $v ) {
						$_SESSION['usces_entry']['custom_customer'][$key][trim($v)] = trim($v);
					}
				} else {
					$_SESSION['usces_entry']['custom_customer'][$key] = trim($value);
				}
		}
		if(isset($_POST['custom_delivery'])) {
//20101008ysk start
			unset($_SESSION['usces_entry']['custom_delivery']);
//20101008ysk end
			foreach( $_POST['custom_delivery'] as $key => $value )
				if ( is_array($value) ) {
					foreach( $value as $k => $v ) {
						$_SESSION['usces_entry']['custom_delivery'][$key][trim($v)] = trim($v);
					}
				} else {
					$_SESSION['usces_entry']['custom_delivery'][$key] = trim($value);
				}
		}
//20110106ysk start
		if(isset($_POST['delivery']['delivery_flag']) && $_POST['delivery']['delivery_flag'] == 0) {
			$this->set_custom_customer_delivery();
		}
//20110106ysk end
	}

	// get entry information ***************************************************************
	function get_entry() {
	
		$res['customer'] = array(
								'mailaddress1' => '', 
								'mailaddress2' => '', 
								'password1' => '', 
								'password2' => '', 
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
								'pref' => ''
							 );
		if(isset($_SESSION['usces_entry']['customer'])){
			foreach((array)$_SESSION['usces_entry']['customer'] as $key => $val){
				$res['customer'][$key] = $val;
			}
		}

		$res['delivery'] = array(
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
								'delivery_flag' => ''
							 );
		if(isset($_SESSION['usces_entry']['delivery'])){
			foreach((array)$_SESSION['usces_entry']['delivery'] as $key => $val){
				$res['delivery'][$key] = $val;
			}
		}
			
		$res['order'] = array(
							'usedpoint' => '', 
							'total_items_price' => '', 
							'discount' => '', 
							'shipping_charge' => '', 
							'cod_fee' => '',
							'shipping_charge' => '',
							'payment_name' => '',
							'delivery_method' => '',
							'delivery_date' => '',
							'delivery_time' => '',
							'total_full_price' => '',
							'note' => '',
							'tax' => '',
							'payment_name' => '',
							'delidue_date' => ''
						 );
		if(isset($_SESSION['usces_entry']['order'])){
			foreach((array)$_SESSION['usces_entry']['order'] as $key => $val){
				$res['order'][$key] = $val;
			}
		}
			
		if(isset($_SESSION['usces_entry']['reserve']))
			$res['reserve'] = $_SESSION['usces_entry']['reserve'];
		else
			$res['reserve'] = NULL;
		
		if(isset($_SESSION['usces_entry']['condition']))
			$res['condition'] = $_SESSION['usces_entry']['condition'];
		else
			$res['condition'] = NULL;

//20100809ysk start
		if(isset($_SESSION['usces_entry']['custom_order']))
			$res['custom_order'] = $_SESSION['usces_entry']['custom_order'];
		else
			$res['custom_order'] = NULL;
//20100809ysk end
//20100818ysk start
		if(isset($_SESSION['usces_entry']['custom_customer']))
			$res['custom_customer'] = $_SESSION['usces_entry']['custom_customer'];
		else
			$res['custom_customer'] = NULL;
		if(isset($_SESSION['usces_entry']['custom_delivery']))
			$res['custom_delivery'] = $_SESSION['usces_entry']['custom_delivery'];
		else
			$res['custom_delivery'] = NULL;
//20100818ysk end
		return $res;
		
	}
	// get realprice ***************************************************************
	function get_realprice($post_id, $sku, $quant, $price = NULL) {
		global $usces;
		$sku = urldecode($sku);
		$skus = $usces->get_skus( $post_id, 'code' );
		
		if($price === NULL) {
			$p = $skus[$sku]['price'];
		} else {
			$p = $price;
		}
//20110905ysk start 0000251
		$p = apply_filters('usces_filter_realprice', $p, $this->serial);
//20110905ysk end
		if( !$skus[$sku]['gp'] ) return $p;
		
		$realprice = usces_get_gp_price($post_id, $p, $quant);
		
		return $realprice;
	}
	
	function set_pre_order_id($id){
		$_SESSION['usces_entry']['reserve']['pre_order_id'] = $id;
	}

//20110106ysk start
	function set_custom_customer_delivery() {
		if(isset($_SESSION['usces_entry']['custom_customer'])) {
			$delivery = array();
			$csde_meta = usces_has_custom_field_meta('delivery');
			if(!empty($csde_meta) and is_array($csde_meta)) {
				foreach($csde_meta as $key => $entry) {
					$delivery[$key] = $key;
				}
			}
			foreach( $_SESSION['usces_entry']['custom_customer'] as $mbkey => $mbvalue ) {
				if(array_key_exists($mbkey, $delivery)) {
//20110126ysk start
					if(empty($_SESSION['usces_entry']['custom_delivery'][$mbkey])) {
						if( is_array($mbvalue) ) {
							foreach( $mbvalue as $k => $v ) {
								$_SESSION['usces_entry']['custom_delivery'][$mbkey][$v] = $v;
							}
						} else {
							$_SESSION['usces_entry']['custom_delivery'][$mbkey] = $mbvalue;
						}
					}
//20110126ysk end
				}
			}
		}
	}
//20110106ysk end
}
?>
