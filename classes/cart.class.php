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
		if( $_SERVER['HTTP_REFERER'] )
			$_SESSION['usces_previous_url'] = $_SERVER['HTTP_REFERER'];
			
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
				
			$_SESSION['usces_cart'][$this->serial]['quant'] = apply_filter('usces_filter_inCart_quant', $_SESSION['usces_cart'][$this->serial]['quant']);

		}
		
		
		if ( isset($_POST['skuPrice']) && $_POST['skuPrice'][$post_id][$sku] != '') {
		
			$price = $this->get_realprice($post_id, $sku, $_SESSION['usces_cart'][$this->serial]['quant']);
			$_SESSION['usces_cart'][$this->serial]['price'] = $price;
			
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
				if( isset($_POST['order_action']) ){
					$price = (int)$_POST['skuPrice'][$index][$post_id][$sku];
				}else{
					$price = $this->get_realprice($post_id, $sku, $_SESSION['usces_cart'][$this->serial]['quant']);
				}
				$_SESSION['usces_cart'][$this->serial]['price'] = $price;
			
			}
		}

//		ksort($_SESSION['usces_cart'], SORT_STRING);
	}
	
	// cinCart_upload ****************************************************************
	function inCart_advance( $index, $name, $key, $value ){
	
	
		$i = 0;
		foreach($_SESSION['usces_cart'] as $serial => $w){
			if($i == $index){
				$_SESSION['usces_cart'][$serial]['advance'][$name][$key] = $value;
			}
			$i++;
		}
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
		
		if(isset($_SESSION['usces_cart'][$this->serial]))
			unset($_SESSION['usces_cart'][$this->serial]);
			
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
			foreach( $_SESSION['usces_member'] as $key => $value )
				$_SESSION['usces_entry']['customer'][$key] = $value;
		} else if(isset($_POST['customer']))	{	
			foreach( $_POST['customer'] as $key => $value )
				$_SESSION['usces_entry']['customer'][$key] = wp_specialchars($value);
		}
		
		if(isset($_POST['delivery']))	{	
			foreach( $_POST['delivery'] as $key => $value )
				$_SESSION['usces_entry']['delivery'][$key] = wp_specialchars($value);
		}
		
		if(isset($_POST['customer']['delivery_flag']) && $_POST['customer']['delivery_flag'] == 0)	{	
			foreach( $_SESSION['usces_entry']['customer'] as $key => $value )
				$_SESSION['usces_entry']['delivery'][$key] = wp_specialchars($value);
		}

		if(isset($_POST['order']))	{	
			foreach( $_POST['order'] as $key => $value )
				$_SESSION['usces_entry']['order'][$key] = wp_specialchars($value);
		}
		
		if(isset($_POST['reserve'])) {
			foreach( $_POST['reserve'] as $key => $value )
				$_SESSION['usces_entry']['reserve'][$key] = wp_specialchars($value);
		}
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

		return $res;
		
	}
	// get realprice ***************************************************************
	function get_realprice($post_id, $sku, $quant) {
		global $usces;
		
		$skus = $usces->get_skus( $post_id, 'ARRAY_A' );
		if( !$skus[$sku]['gptekiyo'] ) return $skus[$sku]['price'];
		
		$GpN1 = $usces->getItemGpNum1($post_id);
		$GpN2 = $usces->getItemGpNum2($post_id);
		$GpN3 = $usces->getItemGpNum3($post_id);
		$GpD1 = $usces->getItemGpDis1($post_id);
		$GpD2 = $usces->getItemGpDis2($post_id);
		$GpD3 = $usces->getItemGpDis3($post_id);
	
		if( empty($GpN1) || empty($GpD1) ) {
		
				$realprice = $skus[$sku]['price'];
				
		}else if( (!empty($GpN1) && !empty($GpD1)) && (empty($GpN2) || empty($GpD2)) ) {
		
			if( $quant >= $GpN1 ) {
				$realprice = round($skus[$sku]['price'] * (100 - $GpD1) / 100);
			}else{
				$realprice = $skus[$sku]['price'];
			}
			
		}else if( (!empty($GpN1) && !empty($GpD1)) && (!empty($GpN2) && !empty($GpD2)) && (empty($GpN3) || empty($GpD3)) ) {
		
			if( $quant >= $GpN2 ) {
				$realprice = round($skus[$sku]['price'] * (100 - $GpD2) / 100);
			}else if( $quant >= $GpN1 && $quant < $GpN2 ) {
				$realprice = round($skus[$sku]['price'] * (100 - $GpD1) / 100);
			}else{
				$realprice = $skus[$sku]['price'];
			}
			
		}else if( (!empty($GpN1) && !empty($GpD1)) && (!empty($GpN2) && !empty($GpD2)) && (!empty($GpN3) && !empty($GpD3)) ) {
		
			if( $quant >= $GpN3 ) {
				$realprice = round($skus[$sku]['price'] * (100 - $GpD3) / 100);
			}else if( $quant >= $GpN2 && $quant < $GpN3 ) {
				$realprice = round($skus[$sku]['price'] * (100 - $GpD2) / 100);
			}else if( $quant >= $GpN1 && $quant < $GpN2 ) {
				$realprice = round($skus[$sku]['price'] * (100 - $GpD1) / 100);
			}else{
				$realprice = $skus[$sku]['price'];
			}
			
		}
		
		return $realprice;
	}
	
	function set_pre_order_id($id){
		$_SESSION['usces_entry']['reserve']['pre_order_id'] = $id;
	}
}
?>
