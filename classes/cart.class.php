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
	
		if( $_SERVER['HTTP_REFERER'] ){
			$_SESSION['usces_previous_url'] = $_SERVER['HTTP_REFERER'];
		}else{
			$_SESSION['usces_previous_url'] = str_replace('https://', 'http://', get_bloginfo('home')).'/';
		}
			
		$ids = array_keys($_POST['inCart']);
		$post_id = $ids[0];
		
		$skus = array_keys($_POST['inCart'][$post_id]);
		$sku = $skus[0];
			
		$this->in_serialize($post_id, $sku);
		
		if ( isset($_POST['quant']) && $_POST['quant'][$post_id][$sku] != '') {
		
			$_SESSION['usces_cart'][$this->serial]['quant'] += (int)$_POST['quant'][$post_id][$sku];
			
		} else {
		
			if ( isset($_SESSION['usces_cart'][$this->serial]) )
				$_SESSION['usces_cart'][$this->serial]['quant'] += 1;
			else
				$_SESSION['usces_cart'][$this->serial]['quant'] = 1;
				
			$_SESSION['usces_cart'][$this->serial]['quant'] = apply_filters('usces_filter_inCart_quant', $_SESSION['usces_cart'][$this->serial]['quant']);

		}
		
		
		if ( isset($_POST['skuPrice']) && $_POST['skuPrice'][$post_id][$sku] != '') {
			$price = $this->get_realprice($post_id, $sku, $_SESSION['usces_cart'][$this->serial]['quant']);
			$price = apply_filters('usces_filter_inCart_price', $price, $this->serial);
			$_SESSION['usces_cart'][$this->serial]['price'] = $price;
		}
		
		if ( isset($_POST['advance']) ) {
			$_SESSION['usces_cart'][$this->serial]['advance'] = $this->wc_serialize($_POST['advance']);
		}
		
//		ksort($_SESSION['usces_cart'], SORT_STRING);
	}

	// update Cart ***************************************************************
	function upCart() {
	
		if(!isset($_POST['quant'])) return false;
		
		foreach($_POST['quant'] as $index => $vs){

			$ids = array_keys($vs);
			$post_id = $ids[0];
			
			$skus = array_keys($vs[$post_id]);
			$sku = $skus[0];
			
			$this->up_serialize($index, $post_id, $sku);
		
			if ( $_POST['quant'][$index][$post_id][$sku] != '') {
		
				$_SESSION['usces_cart'][$this->serial]['quant'] = (int)$_POST['quant'][$index][$post_id][$sku];
				$_SESSION['usces_cart'][$this->serial]['advance'] = $this->wc_unserialize($_POST['advance'][$index][$post_id][$sku]);
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
	}
	
	// inCart_advance ****************************************************************
	function inCart_advance( $serial, $name, $key, $value ){
		$_SESSION['usces_cart'][$serial]['advance'][$name][$key] = $value;
	}

	// serialize ****************************************************************
	function wc_serialize( $value ){
		$out = NULL;
		if( !empty($value) )
			$out = urlencode(serialize($value));
		return $out;
	}

	function wc_unserialize( $str ){
		$out = array();
		if( !empty($str) )
			$out = unserialize(urldecode($str));
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
	}

	// number of data in cart ***********************************************************
	function num_row() {
	
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
	
		if(isset($_POST['itemOption']))
			$sels[$id][$sku] = $_POST['itemOption'][$id][$sku];
		else
			$sels[$id][$sku] = 0;
			
		$this->serial = serialize($sels);
	}

	// update serialize **************************************************************
	function up_serialize($index, $id, $sku){
	
		if(isset($_POST['itemOption'][$index]))
			$sels[$id][$sku] = $_POST['itemOption'][$index][$id][$sku];
		else
			$sels[$id][$sku] = 0;
			
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
		$row['options'] = $array[$ids[0]][$skus[0]];
		$row['price'] = $_SESSION['usces_cart'][$serial]['price'];
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
	// entry information ***************************************************************
	function entry() {
	
		if(isset($_SESSION['usces_member']['ID']) && !empty($_SESSION['usces_member']['ID'])) {
			foreach( $_SESSION['usces_member'] as $key => $value ){
//20100818ysk start
				if($key === 'custom_member') {
//20101008ysk start
					unset($_SESSION['usces_entry']['custom_member']);
//20101008ysk end
					foreach( $_SESSION['usces_member']['custom_member'] as $mbkey => $mbvalue ) {
						if( is_array($mbvalue) ) {
							foreach( $mbvalue as $k => $v ) {
								$_SESSION['usces_entry']['custom_customer'][$mbkey][$v] = $v;
							}
						} else {
							$_SESSION['usces_entry']['custom_customer'][$mbkey] = $mbvalue;
						}
					}
				} else {
					$_SESSION['usces_entry']['customer'][$key] = $value;
				}
//20100818ysk end
			}
		} else if(isset($_POST['customer']))	{	
			foreach( $_POST['customer'] as $key => $value )
				$_SESSION['usces_entry']['customer'][$key] = trim($value);
		}
		
		if(isset($_POST['delivery']))	{	
			foreach( $_POST['delivery'] as $key => $value )
				$_SESSION['usces_entry']['delivery'][$key] = trim($value);
		}
		
		if(isset($_POST['delivery']['delivery_flag']) && $_POST['delivery']['delivery_flag'] == 0)	{	
			foreach( $_SESSION['usces_entry']['customer'] as $key => $value )
				$_SESSION['usces_entry']['delivery'][$key] = trim($value);
		}

		if(isset($_POST['order']))	{	
			foreach( $_POST['order'] as $key => $value )
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
		$this->set_custom_customer_delivery();
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
	
		if(isset($_SESSION['usces_entry']['customer']))
			$res['customer'] = $_SESSION['usces_entry']['customer'];
		else
			$res['customer'] = NULL;
			
		if(isset($_SESSION['usces_entry']['delivery']))
			$res['delivery'] = $_SESSION['usces_entry']['delivery'];
		else
			$res['delivery'] = NULL;
			
		if(isset($_SESSION['usces_entry']['order']))
			$res['order'] = $_SESSION['usces_entry']['order'];
		else
			$res['order'] = NULL;
			
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
		
		$skus = $usces->get_skus( $post_id, 'ARRAY_A' );
		
		if($price === NULL) {
			$p = $skus[$sku]['price'];
		} else {
			$p = $price;
		}
		
		if( !$skus[$sku]['gptekiyo'] ) return $p;
		
		$GpN1 = $usces->getItemGpNum1($post_id);
		$GpN2 = $usces->getItemGpNum2($post_id);
		$GpN3 = $usces->getItemGpNum3($post_id);
		$GpD1 = $usces->getItemGpDis1($post_id);
		$GpD2 = $usces->getItemGpDis2($post_id);
		$GpD3 = $usces->getItemGpDis3($post_id);
	
		if( empty($GpN1) || empty($GpD1) ) {
		
				$realprice = $p;
				
		}else if( (!empty($GpN1) && !empty($GpD1)) && (empty($GpN2) || empty($GpD2)) ) {
		
			if( $quant >= $GpN1 ) {
				$realprice = round($p * (100 - $GpD1) / 100);
			}else{
				$realprice = $p;
			}
			
		}else if( (!empty($GpN1) && !empty($GpD1)) && (!empty($GpN2) && !empty($GpD2)) && (empty($GpN3) || empty($GpD3)) ) {
		
			if( $quant >= $GpN2 ) {
				$realprice = round($p * (100 - $GpD2) / 100);
			}else if( $quant >= $GpN1 && $quant < $GpN2 ) {
				$realprice = round($p * (100 - $GpD1) / 100);
			}else{
				$realprice = $p;
			}
			
		}else if( (!empty($GpN1) && !empty($GpD1)) && (!empty($GpN2) && !empty($GpD2)) && (!empty($GpN3) && !empty($GpD3)) ) {
		
			if( $quant >= $GpN3 ) {
				$realprice = round($p * (100 - $GpD3) / 100);
			}else if( $quant >= $GpN2 && $quant < $GpN3 ) {
				$realprice = round($p * (100 - $GpD2) / 100);
			}else if( $quant >= $GpN1 && $quant < $GpN2 ) {
				$realprice = round($p * (100 - $GpD1) / 100);
			}else{
				$realprice = $p;
			}
			
		}
		
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
					if( is_array($mbvalue) ) {
						foreach( $mbvalue as $k => $v ) {
							$_SESSION['usces_entry']['custom_delivery'][$mbkey][$v] = $v;
						}
					} else {
						$_SESSION['usces_entry']['custom_delivery'][$mbkey] = $mbvalue;
					}
				}
			}
		}
	}
//20110106ysk end
}
?>
