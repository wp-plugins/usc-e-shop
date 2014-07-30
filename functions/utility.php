<?php
// Utility.php
function usces_upgrade_143(){
	global $wpdb, $usces;
	$upgrade = (int)get_option('usces_upgrade3');
	if( USCES_UP143 === $upgrade & USCES_UP143 )
		return false;
	
	$table = $wpdb->prefix . 'usces_ordercart';
	$wpdb->query( "ALTER TABLE $table CHANGE  `cprice`  `cprice` DECIMAL( 15, 2 ) NULL DEFAULT NULL" );
	$wpdb->query( "ALTER TABLE $table CHANGE  `price`  `price` DECIMAL( 15, 2 ) NULL DEFAULT NULL" );
	$wpdb->query( "ALTER TABLE $table CHANGE  `tax`  `tax` DECIMAL( 13, 2 ) NULL DEFAULT NULL" );

	$upgrade += USCES_UP143;
	update_option('usces_upgrade3', $upgrade);
}

function usces_upgrade_141(){
	global $usces;
	$upgrade = (int)get_option('usces_upgrade2');

	if( 3 > $upgrade || 8 <= $upgrade )
		return false;

	global $wpdb;
	$rets = array();
	
	$options = get_option('usces');
	if(empty($options['tax_rate'])){
		$options['tax_mode'] = 'include';
	}else{
		$options['tax_mode'] = 'exclude';
	}
	$options['tax_target'] = 'all';
	update_option('usces', $options);



	$order_table = $wpdb->prefix . "usces_order";
	$cart_table = $wpdb->prefix . "usces_ordercart";
	$cart_meta_table = $wpdb->prefix . "usces_ordercart_meta";

	$query = "SELECT ID, order_cart, order_condition FROM $order_table";
	$results = $wpdb->get_results( $query );
	if( $results ){
		foreach( $results as $order ){
			
			$query = $wpdb->prepare( "SELECT order_id FROM $cart_table WHERE order_id = %d", $order->ID );
			$order_id = $wpdb->get_var( $query );
			if( $order_id == $order->ID ) continue;

			$condition = maybe_unserialize($order->order_condition);
			if( !isset($condition['tax_mode']) ){
				$condition['tax_mode'] = $options['tax_mode'];
			}
			if( !isset($condition['tax_rate']) ){
				$condition['tax_rate'] = (int)$options['tax_rate'];
			}
			if( !isset($condition['tax_target']) ){
				$condition['tax_target'] = $options['tax_target'];
			}


			$cart = unserialize($order->order_cart);
			foreach( (array)$cart as $row_index => $value ){
				$item_code = get_post_meta( $value['post_id'], '_itemCode', true);
				$item_name = get_post_meta( $value['post_id'], '_itemName', true);
				$skus = $usces->get_skus($value['post_id'], 'code');
				$sku_code = urldecode($value['sku']);
				$sku_encoded = $value['sku'];
				$sku = isset($skus[$sku_code]) ? $skus[$sku_code] : array('name'=>'notfound', 'cprice'=>0, 'unit'=>'');
				if( empty($condition['tax_rate']) ){
					$tax = 0;
				
				}else{
					$tax = ($value['price'] * $value['quantity']) * $condition['tax_rate'] / 100;
					$cr = $usces->options['system']['currency'];
					$decimal = $usces_settings['currency'][$cr][1];
					$decipad = (int)str_pad( '1', $decimal+1, '0', STR_PAD_RIGHT );
					switch( $options['tax_method'] ){
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
				}
				$query = $wpdb->prepare("INSERT INTO $cart_table 
					(
					order_id, group_id, row_index, 
					post_id, item_code, item_name, 
					sku_code, sku_name, cprice, price, quantity, unit, 
					tax, destination_id, cart_serial 
					) VALUES (
					%d, %d, %d, 
					%d, %s, %s, 
					%s, %s, %f, %f, %d, %s, 
					%f, %d, %s 
					)", 
					$order->ID, 0, $row_index, 
					$value['post_id'], $item_code, $item_name, 
					$sku_code, $sku['name'], $sku['cprice'], $value['price'], $value['quantity'], $sku['unit'], 
					$tax, NULL, $value['serial']
				);
				$wpdb->query($query);
				
				$cart_id = $wpdb->insert_id ;
				if( $cart_id ){
					if($value['options']){
						foreach((array)$value['options'] as $okey => $ovalue){
							$okey = urldecode($okey);
							if( is_array($ovalue) ){
								$ovalue = serialize($ovalue);
							}else{
								$ovalue = urldecode($ovalue);
							}
							$oquery = $wpdb->prepare("INSERT INTO $cart_meta_table 
								( 
								cart_id, meta_type, meta_key, meta_value 
								) VALUES (
								%d, 'option', %s, %s
								)", 
								$cart_id, $okey, $ovalue
							);
							$wpdb->query($oquery);
						}
					}
					if( $value['advance'] ) {
						foreach( (array)$value['advance'] as $akey => $avalue ) {
							$advance = maybe_unserialize( $avalue );
							if( is_array($advance) ) {
								$post_id = $value['post_id'];
								if( is_array( $advance[$post_id][$sku_encoded] ) ) {
									$akeys = array_keys( $advance[$post_id][$sku_encoded] );
									foreach( (array)$akeys as $akey ) {
										$avalue = serialize( $advance[$post_id][$sku_encoded][$akey] );
										$aquery = $wpdb->prepare("INSERT INTO $cart_meta_table 
											( 
											cart_id, meta_type, meta_key, meta_value 
											) VALUES (
											%d, 'advance', %s, %s
											)", 
											$cart_id, $akey, $avalue
										);
										$wpdb->query( $aquery );
									}
								} else {
									$akeys = array_keys( $advance );
									$akey = ( empty($akeys[0]) ) ? 'advance' : $akeys[0];
									$avalue = serialize( $advance );
									$aquery = $wpdb->prepare("INSERT INTO $cart_meta_table 
										( 
										cart_id, meta_type, meta_key, meta_value 
										) VALUES (
										%d, 'advance', %s, %s
										)", 
										$cart_id, $akey, $avalue
									);
									$wpdb->query( $aquery );
								}
							} else {
								$avalue = urldecode( $avalue );
								$aquery = $wpdb->prepare("INSERT INTO $cart_meta_table 
									( 
									cart_id, meta_type, meta_key, meta_value 
									) VALUES (
									%d, 'advance', 'advance', %s
									)", 
									$cart_id, $avalue
								);
								$wpdb->query( $aquery );
							}
						}
					}
				}
			}
			
			$upquery = $wpdb->prepare("UPDATE $order_table SET order_condition = %s WHERE ID = %d", 
				serialize($condition), $order->ID 
			);
			$wpdb->query($upquery);
		}
	}
	usces_log('USCES_UP141 : Completed', 'db');
	$upgrade += USCES_UP141;
	update_option('usces_upgrade2', $upgrade);
}

function usces_upgrade_14(){
	global $usces;
	$upgrade = (int)get_option('usces_upgrade2');
	if( $upgrade & USCES_UP14 && 3 <= $upgrade )
		return false;

	global $wpdb;
	$rets = array();
	
	$options = get_option('usces');
	if(empty($options['tax_rate'])){
		$options['tax_mode'] = 'include';
	}else{
		$options['tax_mode'] = 'exclude';
	}
	$options['tax_target'] = 'all';
	update_option('usces', $options);



	$order_table = $wpdb->prefix . "usces_order";
	$cart_table = $wpdb->prefix . "usces_ordercart";
	$cart_meta_table = $wpdb->prefix . "usces_ordercart_meta";

	$query = "SELECT ID, order_cart, order_condition FROM $order_table";
	$results = $wpdb->get_results( $query );
	if( $results ){
		foreach( $results as $order ){
			$condition = maybe_unserialize($order->order_condition);
			if( !isset($condition['tax_mode']) ){
				$condition['tax_mode'] = $options['tax_mode'];
			}
			if( !isset($condition['tax_rate']) ){
				$condition['tax_rate'] = (int)$options['tax_rate'];
			}
			if( !isset($condition['tax_target']) ){
				$condition['tax_target'] = $options['tax_target'];
			}


			$cart = unserialize($order->order_cart);
			foreach( (array)$cart as $row_index => $value ){
				$item_code = get_post_meta( $value['post_id'], '_itemCode', true);
				$item_name = get_post_meta( $value['post_id'], '_itemName', true);
				$skus = $usces->get_skus($value['post_id'], 'code');
				$sku_code = urldecode($value['sku']);
				$sku_encoded = $value['sku'];
				$sku = isset($skus[$sku_code]) ? $skus[$sku_code] : array('name'=>'notfound', 'cprice'=>0, 'unit'=>'');
				if( empty($condition['tax_rate']) ){
					$tax = 0;
				
				}else{
					$tax = ($value['price'] * $value['quantity']) * $condition['tax_rate'] / 100;
					$cr = $usces->options['system']['currency'];
					$decimal = $usces_settings['currency'][$cr][1];
					$decipad = (int)str_pad( '1', $decimal+1, '0', STR_PAD_RIGHT );
					switch( $options['tax_method'] ){
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
				}
				$query = $wpdb->prepare("INSERT INTO $cart_table 
					(
					order_id, group_id, row_index, 
					post_id, item_code, item_name, 
					sku_code, sku_name, cprice, price, quantity, unit, 
					tax, destination_id, cart_serial 
					) VALUES (
					%d, %d, %d, 
					%d, %s, %s, 
					%s, %s, %f, %f, %d, %s, 
					%f, %d, %s 
					)", 
					$order->ID, 0, $row_index, 
					$value['post_id'], $item_code, $item_name, 
					$sku_code, $sku['name'], $sku['cprice'], $value['price'], $value['quantity'], $sku['unit'], 
					$tax, NULL, $value['serial']
				);
				$wpdb->query($query);
				
				$cart_id = $wpdb->insert_id ;
				if( $cart_id ){
					if($value['options']){
						foreach((array)$value['options'] as $okey => $ovalue){
							$okey = urldecode($okey);
							if( is_array($ovalue) ){
								$ovalue = serialize($ovalue);
							}else{
								$ovalue = urldecode($ovalue);
							}
							$oquery = $wpdb->prepare("INSERT INTO $cart_meta_table 
								( 
								cart_id, meta_type, meta_key, meta_value 
								) VALUES (
								%d, 'option', %s, %s
								)", 
								$cart_id, $okey, $ovalue
							);
							$wpdb->query($oquery);
						}
					}
					if( $value['advance'] ) {
						foreach( (array)$value['advance'] as $akey => $avalue ) {
							$advance = maybe_unserialize( $avalue );
							if( is_array($advance) ) {
								$post_id = $value['post_id'];
								if( is_array( $advance[$post_id][$sku_encoded] ) ) {
									$akeys = array_keys( $advance[$post_id][$sku_encoded] );
									foreach( (array)$akeys as $akey ) {
										$avalue = serialize( $advance[$post_id][$sku_encoded][$akey] );
										$aquery = $wpdb->prepare("INSERT INTO $cart_meta_table 
											( 
											cart_id, meta_type, meta_key, meta_value 
											) VALUES (
											%d, 'advance', %s, %s
											)", 
											$cart_id, $akey, $avalue
										);
										$wpdb->query( $aquery );
									}
								} else {
									$akeys = array_keys( $advance );
									$akey = ( empty($akeys[0]) ) ? 'advance' : $akeys[0];
									$avalue = serialize( $advance );
									$aquery = $wpdb->prepare("INSERT INTO $cart_meta_table 
										( 
										cart_id, meta_type, meta_key, meta_value 
										) VALUES (
										%d, 'advance', %s, %s
										)", 
										$cart_id, $akey, $avalue
									);
									$wpdb->query( $aquery );
								}
							} else {
								$avalue = urldecode( $avalue );
								$aquery = $wpdb->prepare("INSERT INTO $cart_meta_table 
									( 
									cart_id, meta_type, meta_key, meta_value 
									) VALUES (
									%d, 'advance', 'advance', %s
									)", 
									$cart_id, $avalue
								);
								$wpdb->query( $aquery );
							}
						}
					}
				}
			}
			
			$upquery = $wpdb->prepare("UPDATE $order_table SET order_condition = %s WHERE ID = %d", 
				serialize($condition), $order->ID 
			);
			$wpdb->query($upquery);
		}
	}
	usces_log('USCES_UP14 : Completed', 'db');
	$upgrade += USCES_UP14;
	update_option('usces_upgrade2', $upgrade);
}

function usces_upgrade_07(){
	$upgrade = (int)get_option('usces_upgrade');
	if( $upgrade & USCES_UP07 ) return false;

	global $wpdb;
	$rets = array();
	
	$tableName = $wpdb->prefix . "postmeta";
	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'iopt_', '_iopt_') WHERE meta_key LIKE 'iopt_%'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'isku_', '_isku_') WHERE meta_key LIKE 'isku_%'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemCode', '_itemCode') WHERE meta_key LIKE 'itemCode'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemName', '_itemName') WHERE meta_key LIKE 'itemName'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemRestriction', '_itemRestriction') WHERE meta_key LIKE 'itemRestriction'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemPointrate', '_itemPointrate') WHERE meta_key LIKE 'itemPointrate'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemGpNum1', '_itemGpNum1') WHERE meta_key LIKE 'itemGpNum1'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemGpDis1', '_itemGpDis1') WHERE meta_key LIKE 'itemGpDis1'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemGpNum2', '_itemGpNum2') WHERE meta_key LIKE 'itemGpNum2'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemGpDis2', '_itemGpDis2') WHERE meta_key LIKE 'itemGpDis2'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemGpDis3', '_itemGpDis3') WHERE meta_key LIKE 'itemGpDis3'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemGpNum3', '_itemGpNum3') WHERE meta_key LIKE 'itemGpNum3'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemShipping', '_itemShipping') WHERE meta_key LIKE 'itemShipping'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemDeliveryMethod', '_itemDeliveryMethod') WHERE meta_key LIKE 'itemDeliveryMethod'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemShippingCharge', '_itemShippingCharge') WHERE meta_key LIKE 'itemShippingCharge'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	$mquery = "UPDATE $tableName SET meta_key = REPLACE(meta_key, 'itemIndividualSCharge', '_itemIndividualSCharge') WHERE meta_key LIKE 'itemIndividualSCharge'";
	if( $wpdb->query( $mquery ) )
		$rets[] = 1;

	usces_log('USCES_UP07 : '.print_r($rets,true), 'db');
	$upgrade += USCES_UP07;
	update_option('usces_upgrade', $upgrade);
	return $rets;
}

function usces_upgrade_11(){
	$options = get_option('usces');
	$upgrade = (int)get_option('usces_upgrade');

	if( $upgrade & USCES_UP11 ) return false;

	global $wpdb;
	$rets = array();
	
	/* ITEM SKU DATA */
	$sort = (int)$options['system']['orderby_itemsku'];
	if( $sort ){
		$query = "SELECT * FROM $wpdb->postmeta WHERE meta_key <> '_isku_' AND meta_key LIKE '_isku_%' ORDER BY post_id, meta_key";
	}else{
		$query = "SELECT * FROM $wpdb->postmeta WHERE meta_key <> '_isku_' AND meta_key LIKE '_isku_%' ORDER BY post_id, meta_id";
	}
	$res = $wpdb->get_results($query, ARRAY_A);

	$conclusion = true;
	$pre_post_id = 0;
	$sort_id = 0;
	$check_code = array();
	$dep_num = array();
	foreach( (array)$res as $metarow ){
		$meta_value = unserialize($metarow['meta_value']);
		$newvalue = array();
		$newvalue['code'] = substr($metarow['meta_key'], 6);
		if( $pre_post_id == $metarow['post_id'] ){
			if( in_array( $newvalue['code'], $check_code ) ){
				$newvalue['code'] .= 'dupricate_' . (int)$dep_num[$newvalue['code']];
				$dep_num[$newvalue['code']]++;
			}
		}else{
			$check_code = array();
			$dep_num = array();
			$sort_id = 0;
		}
		foreach( (array)$meta_value as $k => $v ){
			switch ( $k ){
				case 'disp':
					$newvalue['name'] = $v;
					break;
				case 'cprice':
					$newvalue['cprice'] = $v;
					break;
				case 'price':
					$newvalue['price'] = $v;
					break;
				case 'unit':
					$newvalue['unit'] = $v;
					break;
				case 'zaikonum':
					$newvalue['stocknum'] = $v;
					break;
				case 'zaiko':
					$newvalue['stock'] = $v;
					break;
				case 'gptekiyo':
					$newvalue['gp'] = $v;
					break;
				case 'charging_type':
					break;
				default:
					$newvalue[$k] = $v;
			}
		}
		$newvalue['sort'] = $sort_id;
	
		$id = usces_add_sku($metarow['post_id'], $newvalue, false);
		$pre_post_id = $metarow['post_id'];
		$check_code[] = $newvalue['code'];
		$sort_id++;
		
		$res_key = $metarow['post_id'] . '_' . $newvalue['code'];
		if( $id ) {
			delete_post_meta($metarow['post_id'], $metarow['meta_key']);
			$rets['sku'][$res_key] = 1;
		}else{
			$rets['sku'][$res_key] = 0;
			usces_log('meta_id ' . $metarow['meta_id'] . ' : ' . __('This SKU-data has not been rebuilt.', 'usces'), 'database_error.log');
		}
	}
	
	/* ITEM OPTION DATA */
	$sort = (int)$options['system']['orderby_itemopt'];
	if( $sort ){
		$query = "SELECT * FROM $wpdb->postmeta WHERE meta_key <> '_iopt_' AND meta_key LIKE '_iopt_%' ORDER BY post_id, meta_key";
	}else{
		$query = "SELECT * FROM $wpdb->postmeta WHERE meta_key <> '_iopt_' AND meta_key LIKE '_iopt_%' ORDER BY post_id, meta_id";
	}
	$res = $wpdb->get_results($query, ARRAY_A);

	$conclusion = true;
	$pre_post_id = 0;
	$sort_id = 0;
	$check_code = array();
	$dep_num = array();
	foreach( (array)$res as $metarow ){
		$meta_value = unserialize($metarow['meta_value']);
		$newvalue = array();
		$newvalue['name'] = substr($metarow['meta_key'], 6);
		if( $pre_post_id == $metarow['post_id'] ){
			if( in_array( $newvalue['name'], $check_code ) ){
				$newvalue['name'] .= 'dupricate_' . (int)$dep_num[$newvalue['name']];
				$dep_num[$newvalue['name']]++;
			}
		}else{
			$check_code = array();
			$dep_num = array();
			$sort_id = 0;
		}
		foreach( (array)$meta_value as $k => $v ){
			switch ( $k ){
				case 'means':
					$newvalue['means'] = $v;
					break;
				case 'essential':
					$newvalue['essential'] = $v;
					break;
				case 'value':
					if( is_array($v) ){
						$nov = '';
						foreach((array)$v as $vs){
							if( !WCUtils::is_blank($vs) ) 
								//$nov .= str_replace('\\', '&yen;', trim( $vs )) . "\n";
								$nov .= $vs . "\n";
						}
						$newvalue['value'] =  trim($nov);
					}else{
						$newvalue['value'] = trim($v);
					}
					break;
				default:
					$newvalue[$k] = trim($v);
			}
		}
		$newvalue['sort'] = $sort_id;
	
		$id = usces_add_opt($metarow['post_id'], $newvalue, false);
		$pre_post_id = $metarow['post_id'];
		$check_code[] = $newvalue['name'];
		$sort_id++;
		
		$res_key = $metarow['post_id'] . '_' . $newvalue['name'];
		if( $id ) {
			delete_post_meta($metarow['post_id'], $metarow['meta_key']);
			$rets['opt'][$res_key] = 1;
		}else{
			$rets['opt'][$res_key] = 0;
			usces_log('meta_id ' . $metarow['meta_id'] . ' : ' . __('This Item-Option-data has not been rebuilt.', 'usces'), 'database_error.log');
		}
	}
	
	/* PAYMENT METHOD DATA */
	$payment = get_option('usces_payment_method');
//usces_log('payment : ' . print_r($payment,true), 'database_error.log');
	if( empty($payment) ) {
	
		$options = get_option('usces');
		$old_payment = isset($options['payment_method']) ? $options['payment_method'] : '';
	usces_log('old_payment : ' . print_r($old_payment,true), 'database_error.log');
		if( !empty($old_payment) && is_array($old_payment) ) {
			foreach( $old_payment as $key => $value ){
				$res_key = $key . '_' . $value['name'];
				$id = usces_add_system_option( 'usces_payment_method', $value );
				if( $id ) {
					$rets['payment'][$res_key] = 1;
				}else{
					$rets['payment'][$res_key] = 0;
					usces_log('payment_method ' . $value['name'] . ' : ' . __('This Payment-Method-data has not been rebuilt.', 'usces'), 'database_error.log');
				}
			}
		}
	}

	usces_log('USCES_UP11 : ' . print_r($rets,true), 'db');

	$upgrade += USCES_UP11;
	update_option('usces_upgrade', $upgrade);
	return $rets;
}

function usces_log($log, $file){
	global $usces;
	
	if( 'db' == $file ){
		
		global $wpdb;
		$table_name = $wpdb->prefix . 'usces_log';
		$query = $wpdb->prepare("INSERT INTO $table_name (datetime, log) VALUES(%s, %s)", current_time('mysql'), $log);
		$wpdb->query( $query );

	}else{
		
		$log = date('[Y-m-d H:i:s]', current_time('timestamp')) . "\t" . $log . "\n";
		$file_path = USCES_PLUGIN_DIR . '/logs/' . $file;
		if( is_dir($file_path) )
			return;
			
		$fp = fopen($file_path, 'a');
		if( false !== $fp ){
			fwrite($fp, $log);
			fclose($fp);
		}
	}
}


function usces_filter_delivery_secure_check( $mes ){
	global $usces;
	$usces_secure_link = get_option('usces_secure_link');
	$paymod_id = '';
	$settlement = '';
	$payments = usces_get_system_option( 'usces_payment_method', 'sort' );

	foreach ( (array)$payments as $id => $value ) {
		if( isset($_POST['offer']['payment_name']) && $value['name'] == $_POST['offer']['payment_name']){
			$settlement = $value['settlement'];
			break;
		}
	}	
	switch( $settlement ){
		case 'acting_zeus_card':
			$cnum1 = trim($_POST["cnum1"]);
			$securecode = (isset($_POST["securecode"])) ? trim($_POST["securecode"]) : '';
			if ( !is_numeric($cnum1) )
				$mes .= __('カード番号が不正です', 'usces') . "<br />";
			
			if ( isset($_POST["securecode"]) && !is_numeric($securecode) )
				$mes .= __('セキュリティコードが不正です', 'usces') . "<br />";
				
			if ( WCUtils::is_blank($_POST["expyy"]) )
				$mes .= __('カードの有効年を選択してください', 'usces') . "<br />";
				
			if ( WCUtils::is_blank($_POST["expmm"]) )
				$mes .= __('カードの有効月を選択してください', 'usces') . "<br />";
				
			if ( WCUtils::is_blank($_POST["username_card"]) )
				$mes .= __('カード名義を入力してください', 'usces') . "<br />";
				
			if ( isset($_POST["howpay"]) && 0 == $_POST["howpay"] && WCUtils::is_blank($_POST["cbrand"]) )
				$mes .= __('カードブランドを選択してください', 'usces') . "<br />";
				
			if ( 'zeus' != $_POST['acting'] )
				$mes .= __('カード決済データが不正です！', 'usces');
			
			break;

		case 'acting_zeus_conv':
			if( WCUtils::is_blank($_POST['username_conv']) ) {
				$mes .= "お名前を入力してください。<br />";
			} elseif( !preg_match( "/^[ァ-ヶー]+$/u", $_POST['username_conv'] ) ) {
				$mes .= "お名前は全角カタカナで入力してください。<br />";
			}
			break;
	}
	
	return $mes;
}

function usces_get_conv_name($code){
	switch($code){
		case 'D001':
			$name = 'セブン-イレブン';
			break;
		case '010'://20101018ysk
			$name = 'セブンイレブン';
			break;
		case 'D002':
		case '020'://20101018ysk
			$name = 'ローソン';
			break;
		case 'D015':
		case '760'://20101018ysk
			$name = 'セイコーマート';
			break;
		case 'D405':
			$name = 'ペイジー';
			break;
		case 'D003':
			$name = 'サンクス';
			break;
		case 'D004':
			$name = 'サークルK';
			break;
		case 'D040':
			$name = 'サークルKサンクス';
			break;
		case 'D005':
		case '080'://20101018ysk
		case 'D050':
			$name = 'ミニストップ';
			break;
		case 'D010':
		case 'D060':
			$name = 'デイリーヤマザキ';
			break;
		case 'D011':
			$name = 'ヤマザキデイリーストア';
			break;
		case 'D030':
		case '030'://20101018ysk
			$name = 'ファミリーマート';
			break;
		case 'D401':
			$name = 'CyberEdy';
			break;
		case 'D404':
			$name = '楽天銀行';
			break;
		case 'D406':
			$name = 'ジャパネット銀行';
			break;
		case 'D451':
			$name = 'ウェブマネー';
			break;
		case 'D452':
			$name = 'ビットキャッシュ';
			break;
		case 'P901':
			$name = 'コンビニ払込票';
			break;
		case 'P902':
			$name = 'コンビニ払込票（郵便振替対応）';
			break;
//20101018ysk start
		case '050':
			$name = 'デイリーヤマザキ・ヤマザキデイリーストア・タイムリー';
			break;
		case '060':
			$name = 'サークルK・サンクス';
			break;
		case '110':
			$name = 'am/pm';
			break;
//20101018ysk end
		default:
			$name = '';
	}
	return $name;
}

function usces_get_remise_conv_return($code){
	switch($code){
		case 'D001': //セブンイレブン
			$html = '<tr><th>' . __('払込番号','usces') . '</th><td>' . esc_html($_REQUEST["X-PAY_NO1"]) . "</td></tr>\n";
			$html .= '<tr><th>'.__('払込票のURL', 'usces').'</th><td><a href="'.esc_html($_REQUEST["X-PAY_NO2"]).'" target="_blank">'.esc_html($_REQUEST["X-PAY_NO2"])."</a></td></tr>\n";
			break;
		case 'D002': //ローソン
		case 'D015': //セイコーマート
		case 'D405': //ペイジー
			$html = '<tr><th>' . __('受付番号','usces') . '</th><td>' . esc_html($_REQUEST["X-PAY_NO1"]) . "</td></tr>\n";
			$html .= '<tr><th>'.__('支払方法案内URL', 'usces').'</th><td><a href="'.esc_html($_REQUEST["X-PAY_NO2"]).'" target="_blank">'.esc_html($_REQUEST["X-PAY_NO2"])."</a></td></tr>\n";
			break;
		case 'D003': //サンクス
		case 'D004': //サークルK
		case 'D005': //ミニストップ
		case 'D010': //デイリーヤマザキ
		case 'D011': //ヤマザキデイリーストア
			$html = '<tr><th>' . __('決済番号','usces') . '</th><td>' . esc_html($_REQUEST["X-PAY_NO1"]) . "</td></tr>\n";
			$html .= '<tr><th>'.__('支払方法案内URL', 'usces').'</th><td><a href="'.esc_html($_REQUEST["X-PAY_NO2"]).'" target="_blank">'.esc_html($_REQUEST["X-PAY_NO2"])."</a></td></tr>\n";
			break;
		case 'D030': //ファミリーマート
			$html = '<tr><th>' . __('コード','usces') . '</th><td>' . esc_html($_REQUEST["X-PAY_NO1"]) . "</td></tr>\n";
			$html .= '<tr><th>'.__('注文番号', 'usces').'</th><td>'.esc_html($_REQUEST["X-PAY_NO2"])."</td></tr>\n";
			break;
		case 'D401': //CyberEdy
		case 'D404': //楽天銀行
		case 'D406': //ジャパネット銀行
		case 'D451': //ウェブマネー
		case 'D452': //ビットキャッシュ
			$html = '<tr><th>' . __('受付番号','usces') . '</th><td>' . esc_html($_REQUEST["X-PAY_NO1"]) . "</td></tr>\n";
			$html .= '<tr><th>'.__('支払手続URL', 'usces').'</th><td><a href="'.esc_html($_REQUEST["X-PAY_NO2"]).'" target="_blank">'.esc_html($_REQUEST["X-PAY_NO2"])."</a></td></tr>\n";
			break;
		case 'P901': //コンビニ払込票
		case 'P902': //コンビニ払込票（郵便振替対応）
			$html = '<tr><th>' . __('受付番号','usces') . '</th><td>' . esc_html($_REQUEST["X-PAY_NO1"]) . "</td></tr>\n";
			break;
		default:
			$html = '';
	}
	return $html;
}

function usces_payment_detail($usces_entries){
	$payments = usces_get_payments_by_name( $usces_entries['order']['payment_name'] );
	$acting_flag = ( 'acting' == $payments['settlement'] ) ? $payments['module'] : $payments['settlement'];
	$str = '';
	switch( $acting_flag ){
		case 'paypal.php':
			break;
		
		case 'epsilon.php':
			break;
		
		case 'acting_zeus_card':
			if( !isset($usces_entries['order']['cbrand']) || (isset($usces_entries['order']['howpay']) && '1' === $usces_entries['order']['howpay']) ){
				$str = '　一括払い';
			}else{
				$div_name = 'div_' . $usces_entries['order']['cbrand'];
				switch($usces_entries['order'][$div_name]){
					case '01':
						$str = '　一括払い';
						break;
					case '99':
						$str = '　分割（リボ払い）';
						break;
					case '03':
						$str = '　分割（3回）';
						break;
					case '05':
						$str = '　分割（5回）';
						break;
					case '06':
						$str = '　分割（6回）';
						break;
					case '10':
						$str = '　分割（10回）';
						break;
					case '12':
						$str = '　分割（12回）';
						break;
					case '15':
						$str = '　分割（15回）';
						break;
					case '18':
						$str = '　分割（18回）';
						break;
					case '20':
						$str = '　分割（20回）';
						break;
					case '24':
						$str = '　分割（24回）';
						break;
				}
			}
			break;
		
		case 'acting_zeus_bank':
			break;
		
		case 'acting_zeus_conv':
			if( isset($usces_entries['order']['pay_cvs']) ) {
				$conv_name = usces_get_conv_name( $usces_entries['order']['pay_cvs'] );
				$str = ( '' != $conv_name ) ? '　（'.$conv_name.'）' : '';
			}
			break;
		
		case 'acting_remise_card':
			if( isset( $usces_entries['order']['div'] ) ){
				switch($usces_entries['order']['div']){
					case '0':
						$str = '　一括払い';
						break;
					case '1':
						$str = '　分割（2回）';
						break;
					case '2':
						$str = '　分割（リボ払い）';
						break;
				}
			}
			break;
		
		case 'acting_remise_conv':
			break;
	}
	
	$str = apply_filters('usces_filter_payment_detail', $str, $usces_entries);
	return $str;
}

//20100818ysk start
function usces_filter_delivery_check_custom_order( $mes ) {
	global $usces;

	$meta = usces_has_custom_field_meta('order');
//20140131ysk start 0000819
	if( is_array($meta) ) {
		unset( $_SESSION['usces_entry']['custom_order'] );
		if( isset($_POST['custom_order']) ) {
			foreach( $_POST['custom_order'] as $key => $value ) {
				if( is_array($value) ) {
					foreach( $value as $k => $v ) {
						$_SESSION['usces_entry']['custom_order'][$key][trim($v)] = trim($v);
					}
				} else {
					$_SESSION['usces_entry']['custom_order'][$key] = trim($value);
				}
			}
		}
	}
//20140131ysk end

	foreach($meta as $key => $entry) {
		$essential = $entry['essential'];
		if($essential == 1) {
			$name = $entry['name'];
			$means = $entry['means'];
			if($means == 2) {//Text
				if( WCUtils::is_blank($_POST['custom_order'][$key]) )
					$mes .= __($name.'を入力してください。', 'usces')."<br />";
			} else {
				if(!isset($_POST['custom_order'][$key]) or $_POST['custom_order'][$key] == "#NONE#")
					$mes .= __($name.'を選択してください。', 'usces')."<br />";
			}
		}
	}

	return $mes;
}

function usces_filter_customer_check_custom_customer( $mes ) {
	global $usces;

	$meta = usces_has_custom_field_meta('customer');
	foreach($meta as $key => $entry) {
		$essential = $entry['essential'];
		if($essential == 1) {
			$name = $entry['name'];
			$means = $entry['means'];
			if($means == 2) {//Text
				if( WCUtils::is_blank($_POST['custom_customer'][$key]) )
					$mes .= __($name.'を入力してください。', 'usces')."<br />";
			} else {
				if(!isset($_POST['custom_customer'][$key]) or $_POST['custom_customer'][$key] == "#NONE#")
					$mes .= __($name.'を選択してください。', 'usces')."<br />";
			}
		}
	}

	return $mes;
}

function usces_filter_delivery_check_custom_delivery( $mes ) {
	global $usces;

	if( isset($_POST['delivery']['delivery_flag']) && $_POST['delivery']['delivery_flag'] == '1' ) {
		$meta = usces_has_custom_field_meta('delivery');
		foreach($meta as $key => $entry) {
			$essential = $entry['essential'];
			if($essential == 1) {
				$name = $entry['name'];
				$means = $entry['means'];
				if($means == 2) {//Text
					if( WCUtils::is_blank($_POST['custom_delivery'][$key]) )
						$mes .= __($name.'を入力してください。', 'usces')."<br />";
				} else {
					if(!isset($_POST['custom_delivery'][$key]) or $_POST['custom_delivery'][$key] == "#NONE#")
						$mes .= __($name.'を選択してください。', 'usces')."<br />";
				}
			}
		}
	}

	return $mes;
}

function usces_filter_member_check_custom_member( $mes ) {
	global $usces;

	unset($_SESSION['usces_member']['custom_member']);
	if(isset($_POST['custom_member'])) {
		foreach( $_POST['custom_member'] as $key => $value )
			if( is_array($value) ) {
				foreach( $value as $k => $v ) 
					$_SESSION['usces_member']['custom_member'][$key][trim($v)] = trim($v);
			} else {
				$_SESSION['usces_member']['custom_member'][$key] = trim($value);
			}
	}

	$meta = usces_has_custom_field_meta('member');
	foreach($meta as $key => $entry) {
		$essential = $entry['essential'];
		if($essential == 1) {
			$name = $entry['name'];
			$means = $entry['means'];
			if($means == 2) {//Text
				if( WCUtils::is_blank($_POST['custom_member'][$key]) )
					$mes .= __($name.'を入力してください。', 'usces')."<br />";
			} else {
				if(!isset($_POST['custom_member'][$key]) or $_POST['custom_member'][$key] == "#NONE#")
					$mes .= __($name.'を選択してください。', 'usces')."<br />";
			}
		}
	}

	return $mes;
}
//20100818ysk end
function usces_dashboard_setup() {
	wp_add_dashboard_widget( 'usces_db_widget' , 'Welcart Information' , 'usces_db_widget');
}

function usces_admin_login_head() {
?>
<script type='text/javascript'>
(function($) {
	usces = {
		settings: {
			url: 'http://www.welcart.com/varch/varch.php',
			type: 'POST',
			cache: false,
			success: function(data, dataType){
			}, 
			error: function(msg){
			}
		},
		varch : function() {
			var s = usces.settings;
			s.data = "action=varch_ajax&ID=usces_varch&ver=" + <?php echo $_SERVER['HTTP_HOST']; ?>;
			$.ajax( s );
			return false;
		}
	};
	usces.varch();
})(jQuery);
</script>
<?php
}

//20100908ysk start
// member list download
function usces_download_member_list() {
	require_once( USCES_PLUGIN_DIR . "/classes/dataList.class.php" );
	global $wpdb, $usces;
//20110411ysk start
	global $usces_settings;
//20110411ysk end

	$ext = $_REQUEST['ftype'];
/*	if($ext == 'xls') {//HTML
		$table_h = "<table>";
		$table_f = "</table>";
		$tr_h = "<tr>";
		$tr_f = "</tr>";
		$th_h1 = "<th>";
		$th_h = "<th>";
		$th_f = "</th>";
		$td_h1 = "<td>";
		$td_h = "<td>";
		$td_f = "</td>";
		$lf = "\n";
*/	if($ext == 'xls') {//TSV
		$table_h = "";
		$table_f = "";
		$tr_h = "";
		$tr_f = "";
		$th_h1 = '"';
		$th_h = "\t".'"';
		$th_f = '"';
		$td_h1 = '"';
		$td_h = "\t".'"';
		$td_f = '"';
		$lf = "\n";
	} elseif($ext == 'csv') {//CSV
		$table_h = "";
		$table_f = "";
		$tr_h = "";
		$tr_f = "";
//20110201ysk start
		//$th_h1 = "";
		$th_h1 = '"';
		//$th_h = ",";
		$th_h = ',"';
		//$th_f = "";
		$th_f = '"';
		//$td_h1 = "";
		$td_h1 = '"';
		//$td_h = ",";
		$td_h = ',"';
		//$td_f = "";
		$td_f = '"';
//20110201ysk end
		$lf = "\n";
	} else {
		exit();
	}
	$csmb_meta = usces_has_custom_field_meta('member');
//20110411ysk start
	$applyform = usces_get_apply_addressform($usces->options['system']['addressform']);
//20110411ysk end

	//==========================================================================
	$usces_opt_member = get_option('usces_opt_member');
	if(!is_array($usces_opt_member)){
		$usces_opt_member = array();
	}
	$usces_opt_member['ftype_mem'] = $ext;
	$chk_mem = array();
	$chk_mem['ID'] = 1;
	$chk_mem['email'] = (isset($_REQUEST['check']['email'])) ? 1 : 0;
	if(!empty($csmb_meta)) {
		foreach($csmb_meta as $key => $entry) {
			if($entry['position'] == 'name_pre') {
				$name = $entry['name'];
//20110208ysk start
				$csmb_key = 'csmb_'.$key;
				//$chk_mem[$name] = (isset($_REQUEST['check'][$name])) ? 1 : 0;
				$chk_mem[$csmb_key] = (isset($_REQUEST['check'][$csmb_key])) ? 1 : 0;
//20110208ysk end
			}
		}
	}
	$chk_mem['name'] = 1;
//20110411ysk start
	if($applyform == 'JP') {
		$chk_mem['kana'] = (isset($_REQUEST['check']['kana'])) ? 1 : 0;
	}
//20110411ysk end
	if(!empty($csmb_meta)) {
		foreach($csmb_meta as $key => $entry) {
			if($entry['position'] == 'name_after') {
				$name = $entry['name'];
//20110208ysk start
				$csmb_key = 'csmb_'.$key;
				//$chk_mem[$name] = (isset($_REQUEST['check'][$name])) ? 1 : 0;
				$chk_mem[$csmb_key] = (isset($_REQUEST['check'][$csmb_key])) ? 1 : 0;
//20110208ysk end
			}
		}
	}
	$chk_mem['zip'] = (isset($_REQUEST['check']['zip'])) ? 1 : 0;
//20110411ysk start
	$chk_mem['country'] = 1;
//20110411ysk end
	$chk_mem['pref'] = 1;
	$chk_mem['address1'] = 1;
	$chk_mem['address2'] = 1;
	$chk_mem['address3'] = 1;
	$chk_mem['tel'] = (isset($_REQUEST['check']['tel'])) ? 1 : 0;
	$chk_mem['fax'] = (isset($_REQUEST['check']['fax'])) ? 1 : 0;
	if(!empty($csmb_meta)) {
		foreach($csmb_meta as $key => $entry) {
			if($entry['position'] == 'fax_after') {
				$name = $entry['name'];
//20110208ysk start
				$csmb_key = 'csmb_'.$key;
				//$chk_mem[$name] = (isset($_REQUEST['check'][$name])) ? 1 : 0;
				$chk_mem[$csmb_key] = (isset($_REQUEST['check'][$csmb_key])) ? 1 : 0;
//20110208ysk end
			}
		}
	}
	$chk_mem['date'] = (isset($_REQUEST['check']['date'])) ? 1 : 0;
	$chk_mem['point'] = (isset($_REQUEST['check']['point'])) ? 1 : 0;
	$chk_mem['rank'] = (isset($_REQUEST['check']['rank'])) ? 1 : 0;
	$usces_opt_member['chk_mem'] = apply_filters( 'usces_filter_chk_mem', $chk_mem );
	update_option('usces_opt_member', $usces_opt_member);
	//==========================================================================

	$_REQUEST['searchIn'] = "searchIn";
	$tableName = $wpdb->prefix."usces_member";
	$arr_column = array(
				__('membership number', 'usces') => 'ID', 
				__('name', 'usces') => 'name', 
				__('Address', 'usces') => 'address', 
				__('Phone number', 'usces') => 'tel', 
				__('e-mail', 'usces') => 'email', 
				__('Strated date', 'usces') => 'date', 
				__('current point', 'usces') => 'point');
	$DT = new dataList($tableName, $arr_column);
//20101202ysk start
	$DT->pageLimit = 'off';
//20101202ysk end
	$res = $DT->MakeTable();
	$rows = $DT->rows;

	//==========================================================================
	$line = $table_h;
	$line .= $tr_h;
	$line .= $th_h1.__('membership number', 'usces').$th_f;
	if(isset($_REQUEST['check']['email'])) $line .= $th_h.__('e-mail', 'usces').$th_f;
	if(!empty($csmb_meta)) {
		foreach($csmb_meta as $key => $entry) {
			if($entry['position'] == 'name_pre') {
				$name = $entry['name'];
//20110208ysk start
				$csmb_key = 'csmb_'.$key;
				//if(isset($_REQUEST['check'][$name])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
				if(isset($_REQUEST['check'][$csmb_key])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
//20110208ysk end
			}
		}
	}
	$line .= $th_h.__('name', 'usces').$th_f;
//20110411ysk start
	if($applyform == 'JP') {
		if(isset($_REQUEST['check']['kana'])) $line .= $th_h.__('furigana', 'usces').$th_f;
	}
//20110411ysk end
	if(!empty($csmb_meta)) {
		foreach($csmb_meta as $key => $entry) {
			if($entry['position'] == 'name_after') {
				$name = $entry['name'];
//20110208ysk start
				$csmb_key = 'csmb_'.$key;
				//if(isset($_REQUEST['check'][$name])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
				if(isset($_REQUEST['check'][$csmb_key])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
//20110208ysk end
			}
		}
	}
//20110411ysk start
	switch($applyform) {
	case 'JP':
		if(isset($_REQUEST['check']['zip'])) $line .= $th_h.__('Zip/Postal Code', 'usces').$th_f;
		$line .= $th_h.__('Country', 'usces').$th_f;
		$line .= $th_h.__('Province', 'usces').$th_f;
		$line .= $th_h.__('city', 'usces').$th_f;
		$line .= $th_h.__('numbers', 'usces').$th_f;
		$line .= $th_h.__('building name', 'usces').$th_f;
		if(isset($_REQUEST['check']['tel'])) $line .= $th_h.__('Phone number', 'usces').$th_f;
		if(isset($_REQUEST['check']['fax'])) $line .= $th_h.__('FAX number', 'usces').$th_f;
		break;
	case 'US':
	default:
		$line .= $th_h.__('Address Line1', 'usces').$th_f;
		$line .= $th_h.__('Address Line2', 'usces').$th_f;
		$line .= $th_h.__('city', 'usces').$th_f;
		$line .= $th_h.__('State', 'usces').$th_f;
		$line .= $th_h.__('Country', 'usces').$th_f;
		if(isset($_REQUEST['check']['zip'])) $line .= $th_h.__('Zip', 'usces').$th_f;
		if(isset($_REQUEST['check']['tel'])) $line .= $th_h.__('Phone number', 'usces').$th_f;
		if(isset($_REQUEST['check']['fax'])) $line .= $th_h.__('FAX number', 'usces').$th_f;
		break;
	}
//20110411ysk end
	if(!empty($csmb_meta)) {
		foreach($csmb_meta as $key => $entry) {
			if($entry['position'] == 'fax_after') {
				$name = $entry['name'];
//20110208ysk start
				$csmb_key = 'csmb_'.$key;
				//if(isset($_REQUEST['check'][$name])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
				if(isset($_REQUEST['check'][$csmb_key])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
//20110208ysk end
			}
		}
	}
	if(isset($_REQUEST['check']['date'])) $line .= $th_h.__('Strated date', 'usces').$th_f;
	if(isset($_REQUEST['check']['point'])) $line .= $th_h.__('current point', 'usces').$th_f;
	if(isset($_REQUEST['check']['rank'])) $line .= $th_h.__('Rank', 'usces').$th_f;
	$line .= apply_filters( 'usces_filter_chk_mem_label', NULL, $usces_opt_member, $rows );
	$line .= $tr_f.$lf;
	//==========================================================================
	foreach((array)$rows as $array) {
		$member_id = $array['ID'];
		$query = $wpdb->prepare("SELECT * FROM $tableName WHERE ID = %d", $member_id);
		$data = $wpdb->get_row( $query, ARRAY_A );

		$line .= $tr_h;
		$line .= $td_h1.$member_id.$td_f;
		if(isset($_REQUEST['check']['email'])) $line .= $td_h.usces_entity_decode($array['email'], $ext).$td_f;
		if(!empty($csmb_meta)) {
			foreach($csmb_meta as $key => $entry) {
				if($entry['position'] == 'name_pre') {
					$name = $entry['name'];
					$csmb_key = 'csmb_'.$key;
//20110208ysk start
					//if(isset($_REQUEST['check'][$name])) {
					if(isset($_REQUEST['check'][$csmb_key])) {
//20110208ysk end
						$value = maybe_unserialize($usces->get_member_meta_value($csmb_key, $member_id));
						if(empty($value)) {
							$value = '';
						} elseif(is_array($value)) {
							$concatval = '';
							$c = '';
							foreach($value as $v) {
								$concatval .= $c.$v;
								$c = ' ';
							}
							$value = $concatval;
						}
						$line .= $td_h.usces_entity_decode($value, $ext).$td_f;
					}
				}
			}
		}
//20110411ysk start
		switch($applyform) {
		case 'JP':
			$line .= $td_h.usces_entity_decode($data['mem_name1'].' '.$data['mem_name2'], $ext).$td_f;
			if(isset($_REQUEST['check']['kana'])) $line .= $td_h.usces_entity_decode($data['mem_name3'].' '.$data['mem_name4'], $ext).$td_f;
			break;
		case 'US':
		default:
			$line .= $td_h.usces_entity_decode($data['mem_name2'].' '.$data['mem_name1'], $ext).$td_f;
			break;
		}
//20110411ysk end
		if(!empty($csmb_meta)) {
			foreach($csmb_meta as $key => $entry) {
				if($entry['position'] == 'name_after') {
					$name = $entry['name'];
					$csmb_key = 'csmb_'.$key;
//20110208ysk start
					//if(isset($_REQUEST['check'][$name])) {
					if(isset($_REQUEST['check'][$csmb_key])) {
//20110208ysk end
						$value = maybe_unserialize($usces->get_member_meta_value($csmb_key, $member_id));
						if(empty($value)) {
							$value = '';
						} elseif(is_array($value)) {
							$concatval = '';
							$c = '';
							foreach($value as $v) {
								$concatval .= $c.$v;
								$c = ' ';
							}
							$value = $concatval;
						}
						$line .= $td_h.usces_entity_decode($value, $ext).$td_f;
					}
				}
			}
		}
//20110411ysk start
		$address_info = '';
		switch($applyform) {
		case 'JP':
			if(isset($_REQUEST['check']['zip'])) $address_info .= $td_h.usces_entity_decode($data['mem_zip'], $ext).$td_f;
			$address_info .= $td_h.$usces_settings['country'][$usces->get_member_meta_value('customer_country', $member_id)].$td_f;
			$address_info .= $td_h.usces_entity_decode($data['mem_pref'], $ext).$td_f;
			$address_info .= $td_h.usces_entity_decode($data['mem_address1'], $ext).$td_f;
			$address_info .= $td_h.usces_entity_decode($data['mem_address2'], $ext).$td_f;
			$address_info .= $td_h.usces_entity_decode($data['mem_address3'], $ext).$td_f;
			if(isset($_REQUEST['check']['tel'])) $address_info .= $td_h.usces_entity_decode($data['mem_tel'], $ext).$td_f;
			if(isset($_REQUEST['check']['fax'])) $address_info .= $td_h.usces_entity_decode($data['mem_fax'], $ext).$td_f;
			break;
		case 'US':
		default:
			$address_info .= $td_h.usces_entity_decode($data['mem_address2'], $ext).$td_f;
			$address_info .= $td_h.usces_entity_decode($data['mem_address3'], $ext).$td_f;
			$address_info .= $td_h.usces_entity_decode($data['mem_address1'], $ext).$td_f;
			$address_info .= $td_h.usces_entity_decode($data['mem_pref'], $ext).$td_f;
			$address_info .= $td_h.$usces_settings['country'][$usces->get_member_meta_value('customer_country', $member_id)].$td_f;
			if(isset($_REQUEST['check']['zip'])) $address_info .= $td_h.usces_entity_decode($data['mem_zip'], $ext).$td_f;
			if(isset($_REQUEST['check']['tel'])) $address_info .= $td_h.usces_entity_decode($data['mem_tel'], $ext).$td_f;
			if(isset($_REQUEST['check']['fax'])) $address_info .= $td_h.usces_entity_decode($data['mem_fax'], $ext).$td_f;
			break;
		}
		$address_info_args = compact( 'td_h', 'td_f', 'ext', 'member_id', 'applyform' );
		$line .= apply_filters( 'usces_filter_mem_csv_address_info', $address_info, $data, $address_info_args );
//20110411ysk end
		if(!empty($csmb_meta)) {
			foreach($csmb_meta as $key => $entry) {
				if($entry['position'] == 'fax_after') {
					$name = $entry['name'];
					$csmb_key = 'csmb_'.$key;
//20110208ysk start
					//if(isset($_REQUEST['check'][$name])) {
					if(isset($_REQUEST['check'][$csmb_key])) {
//20110208ysk end
						$value = maybe_unserialize($usces->get_member_meta_value($csmb_key, $member_id));
						if(empty($value)) {
							$value = '';
						} elseif(is_array($value)) {
							$concatval = '';
							$c = '';
							foreach($value as $v) {
								$concatval .= $c.$v;
								$c = ' ';
							}
							$value = $concatval;
						}
						$line .= $td_h.usces_entity_decode($value, $ext).$td_f;
					}
				}
			}
		}
		if(isset($_REQUEST['check']['date'])) $line .= $td_h.$data['mem_registered'].$td_f;
		if(isset($_REQUEST['check']['point'])) $line .= $td_h.$data['mem_point'].$td_f;
		if(isset($_REQUEST['check']['rank'])) {
			$rank = '';
			foreach((array)$usces->member_status as $rk => $rv) {
				if($rk == $data['mem_status']) {
					$rank = $rv;
					break;
				}
			}
			$line .= $td_h.$rank.$td_f;
		}
		$line .= apply_filters( 'usces_filter_chk_mem_data', NULL, $usces_opt_member, $member_id, $data );
		$line .= $tr_f.$lf;
	}
	$line .= $table_f.$lf;
	//==========================================================================

	if($ext == 'xls') {
		header("Content-Type: application/vnd.ms-excel; charset=Shift-JIS");
	} elseif($ext == 'csv') {
		header("Content-Type: application/octet-stream");
	}
	header("Content-Disposition: attachment; filename=usces_member_list.".$ext);
	mb_http_output('pass');
	print(mb_convert_encoding($line, "SJIS-win", "UTF-8"));
	exit();
}

// product list download
function usces_download_product_list() {
	require_once( USCES_PLUGIN_DIR . "/classes/orderList.class.php" );
	global $wpdb, $usces;

	$ext = $_REQUEST['ftype'];
/*	if($ext == 'xls') {//HTML
		$table_h = "<table>";
		$table_f = "</table>";
		$tr_h = "<tr>";
		$tr_f = "</tr>";
		$th_h1 = "<th>";
		$th_h = "<th>";
		$th_f = "</th>";
		$td_h1 = "<td>";
		$td_h = "<td>";
		$td_f = "</td>";
		$sp = ":";
		$nb = " ";
		$lf = "\n";
*/	if($ext == 'xls') {//TSV
		$table_h = "";
		$table_f = "";
		$tr_h = "";
		$tr_f = "";
		$th_h1 = '"';
		$th_h = "\t".'"';
		$th_f = '"';
		$td_h1 = '"';
		$td_h = "\t".'"';
		$td_f = '"';
		$sp = ":";
		$nb = "\n";
		$lf = "\n";
	} elseif($ext == 'csv') {//CSV
		$table_h = "";
		$table_f = "";
		$tr_h = "";
		$tr_f = "";
//20110201ysk start
		//$th_h1 = "";
		$th_h1 = '"';
		//$th_h = ",";
		$th_h = ',"';
		//$th_f = "";
		$th_f = '"';
		//$td_h1 = "";
		$td_h1 = '"';
		//$td_h = ",";
		$td_h = ',"';
		//$td_f = "";
		$td_f = '"';
//20110201ysk end
		$sp = ":";
		$nb = " ";
		$lf = "\n";
	} else {
		exit();
	}

	//==========================================================================
	$usces_opt_order = get_option('usces_opt_order');
	if(!is_array($usces_opt_order)){
		$usces_opt_order = array();
	}
	$usces_opt_order['ftype_pro'] = $ext;
	$chk_pro = array();
	$chk_pro['ID'] = 1;
	$chk_pro['deco_id'] = 1;
	$chk_pro['date'] = (isset($_REQUEST['check']['date'])) ? 1 : 0;
	$chk_pro['mem_id'] = (isset($_REQUEST['check']['mem_id'])) ? 1 : 0;
	$chk_pro['name'] = (isset($_REQUEST['check']['name'])) ? 1 : 0;
	$chk_pro['delivery_method'] = (isset($_REQUEST['check']['delivery_method'])) ? 1 : 0;
	$chk_pro['shipping_date'] = (isset($_REQUEST['check']['shipping_date'])) ? 1 : 0;
	$chk_pro['item_code'] = 1;
	$chk_pro['sku_code'] = 1;
	$chk_pro['item_name'] = (isset($_REQUEST['check']['item_name'])) ? 1 : 0;
	$chk_pro['sku_name'] = (isset($_REQUEST['check']['sku_name'])) ? 1 : 0;
	$chk_pro['options'] = (isset($_REQUEST['check']['options'])) ? 1 : 0;
	$chk_pro['quantity'] = 1;
	$chk_pro['price'] = 1;
	$chk_pro['unit'] = (isset($_REQUEST['check']['unit'])) ? 1 : 0;
	$usces_opt_order['chk_pro'] = apply_filters( 'usces_filter_chk_pro', $chk_pro );
	update_option('usces_opt_order', $usces_opt_order);
	//==========================================================================

	$_REQUEST['searchIn'] = "searchIn";
	$tableName = $wpdb->prefix."usces_order";
	$tableName2 = $wpdb->prefix."usces_ordercart";
	$tableName3 = $wpdb->prefix."usces_ordercart_meta";

	$arr_column = array(
				__('ID', 'usces') => 'ID', 
				__('Order number', 'usces') => 'deco_id', 
				__('date', 'usces') => 'date', 
				__('membership number', 'usces') => 'mem_id', 
				__('name', 'usces') => 'name', 
				__('Region', 'usces') => 'pref', 
				__('shipping option', 'usces') => 'delivery_method', 
				__('Amount', 'usces') => 'total_price', 
				__('payment method', 'usces') => 'payment_name', 
				__('transfer statement', 'usces') => 'receipt_status', 
				__('Processing', 'usces') => 'order_status', 
				__('shpping date', 'usces') => 'order_modified');
	$DT = new dataList($tableName, $arr_column);
//20101202ysk start
	$DT->pageLimit = 'off';
//20101202ysk end
	$res = $DT->MakeTable();
	$rows = $DT->rows;

	//==========================================================================
	$line = $table_h;
	$line .= $tr_h;
	$line .= $th_h1.__('ID', 'usces').$th_f;
	$line .= $th_h.__('Order number', 'usces').$th_f;
	if(isset($_REQUEST['check']['date'])) $line .= $th_h.__('order date', 'usces').$th_f;
	if(isset($_REQUEST['check']['mem_id'])) $line .= $th_h.__('membership number', 'usces').$th_f;
	if(isset($_REQUEST['check']['name'])) $line .= $th_h.__('name', 'usces').$th_f;
	if(isset($_REQUEST['check']['delivery_method'])) $line .= $th_h.__('shipping option', 'usces').$th_f;
	if(isset($_REQUEST['check']['shipping_date'])) $line .= $th_h.__('shpping date', 'usces').$th_f;
	$line .= apply_filters( 'usces_filter_chk_pro_label_head', NULL, $usces_opt_order, $rows);
	$line .= $th_h.__('item code', 'usces').$th_f;
	$line .= $th_h.__('SKU code', 'usces').$th_f;
	if(isset($_REQUEST['check']['item_name'])) $line .= $th_h.__('item name', 'usces').$th_f;
	if(isset($_REQUEST['check']['sku_name'])) $line .= $th_h.__('SKU display name ', 'usces').$th_f;
	if(isset($_REQUEST['check']['options'])) $line .= $th_h.__('options for items', 'usces').$th_f;
	$line .= $th_h.__('Quantity', 'usces').$th_f;
	$line .= $th_h.__('Unit price', 'usces').$th_f;
	if(isset($_REQUEST['check']['unit'])) $line .= $th_h.__('unit', 'usces').$th_f;
	$line .= apply_filters( 'usces_filter_chk_pro_label_detail', NULL, $usces_opt_order, $rows);
	$line .= $tr_f.$lf;
	//==========================================================================
	foreach((array)$rows as $array) {
		$order_id = $array['ID'];
		$query = $wpdb->prepare("SELECT * FROM $tableName2 WHERE order_id = %d", $order_id);
		$cart = $wpdb->get_results( $query, ARRAY_A );
		//$cart = stripslashes_deep(unserialize($data['order_cart']));
		//$cart = unserialize($data['order_cart']);
		//if(!empty($data)) {
		//	$data = stripslashes_deep($data);
		//}
		for($i = 0; $i < count($cart); $i++) {
			$cart_row = $cart[$i];
//			$post_id = $cart_row['post_id'];
//			$sku = urldecode($cart_row['sku']);
			$line .= $tr_h;
			$line .= $td_h1.$order_id.$td_f;
			$line .= $td_h.$array['deco_id'].$td_f;

			if(isset($_REQUEST['check']['date'])) $line .= $td_h.$array['date'].$td_f;
			if(isset($_REQUEST['check']['mem_id'])) $line .= $td_h.$array['mem_id'].$td_f;
			if(isset($_REQUEST['check']['name'])) $line .= $td_h.usces_entity_decode($array['name'], $ext).$td_f;
			if(isset($_REQUEST['check']['delivery_method'])) {
				$delivery_method = '';
				if(strtoupper($array['delivery_method']) == '#NONE#') {
					$delivery_method = __('No preference', 'usces');
				} else {
					foreach((array)$usces->options['delivery_method'] as $dkey => $delivery) {
						if($delivery['id'] == $array['delivery_method']) {
							$delivery_method = $delivery['name'];
							break;
						}
					}
				}
				$line .= $td_h.$delivery_method.$td_f;
			}
			if(isset($_REQUEST['check']['shipping_date'])) $line .= $td_h.$array['order_modified'].$td_f;
			$line .= apply_filters( 'usces_filter_chk_pro_data_head', NULL, $usces_opt_order, $array, $cart_row);

			$line .= $td_h.$cart_row['item_code'].$td_f;
			$line .= $td_h.$cart_row['sku_code'].$td_f;
			if(isset($_REQUEST['check']['item_name'])) $line .= $td_h.usces_entity_decode($cart_row['item_name'], $ext).$td_f;
			if(isset($_REQUEST['check']['sku_name'])) $line .= $td_h.usces_entity_decode($cart_row['sku_name'], $ext).$td_f;
			if(isset($_REQUEST['check']['options'])) {
				$query = $wpdb->prepare("SELECT * FROM $tableName3 WHERE cart_id = %d AND meta_type = %s", $cart_row['cart_id'], 'option' );
				$options = $wpdb->get_results( $query, ARRAY_A );
				$optstr = '';
				if(is_array($options) && count($options) > 0) {
					foreach((array)$options as $key => $value) {
						if(!empty($value['meta_key'])) {
							$optstr .= usces_entity_decode(urldecode($value['meta_key']).$sp.urldecode($value['meta_value']), $ext).$nb;
						}
					}
				}
				$line .= $td_h.$optstr.$td_f;
			}
			$line .= $td_h.$cart_row['quantity'].$td_f;
			$line .= $td_h.usces_crform($cart_row['price'], false, false, 'return', false).$td_f;
			if(isset($_REQUEST['check']['unit'])) $line .= $td_h.usces_entity_decode($cart_row['unit'], $ext).$td_f;
			$line .= apply_filters( 'usces_filter_chk_pro_data_detail', NULL, $usces_opt_order, $array, $cart_row);
			$line .= $tr_f.$lf;
		}
	}
	$line .= $table_f.$lf;
	//==========================================================================

	if($ext == 'xls') {
		header("Content-Type: application/vnd.ms-excel; charset=Shift-JIS");
	} elseif($ext == 'csv') {
		header("Content-Type: application/octet-stream");
	}
	header("Content-Disposition: attachment; filename=usces_product_list.".$ext);
	mb_http_output('pass');
	print(mb_convert_encoding($line, "SJIS-win", "UTF-8"));
	exit();
}

// order list download
function usces_download_order_list() {
	require_once( USCES_PLUGIN_DIR . "/classes/orderList.class.php" );
	global $wpdb, $usces;
//20110411ysk start
	global $usces_settings;
//20110411ysk end

	$ext = $_REQUEST['ftype'];
/*	if($ext == 'xls') {//HTML
		$table_h = "<table>";
		$table_f = "</table>";
		$tr_h = "<tr>";
		$tr_f = "</tr>";
		$th_h1 = "<th>";
		$th_h = "<th>";
		$th_f = "</th>";
		$td_h1 = "<td>";
		$td_h = "<td>";
		$td_f = "</td>";
		$sp = ":";
		$lf = "\n";
*/	if($ext == 'xls') {//TSV
		$table_h = "";
		$table_f = "";
		$tr_h = "";
		$tr_f = "";
		$th_h1 = '"';
		$th_h = "\t".'"';
		$th_f = '"';
		$td_h1 = '"';
		$td_h = "\t".'"';
		$td_f = '"';
		$sp = ":";
		$lf = "\n";
	} elseif($ext == 'csv') {//CSV
		$table_h = "";
		$table_f = "";
		$tr_h = "";
		$tr_f = "";
//20110201ysk start
		//$th_h1 = "";
		$th_h1 = '"';
		//$th_h = ",";
		$th_h = ',"';
		//$th_f = "";
		$th_f = '"';
		//$td_h1 = "";
		$td_h1 = '"';
		//$td_h = ",";
		$td_h = ',"';
		//$td_f = "";
		$td_f = '"';
//20110201ysk end
		$sp = ":";
		$lf = "\n";
	} else {
		exit();
	}
	$csod_meta = usces_has_custom_field_meta('order');
	$cscs_meta = usces_has_custom_field_meta('customer');
	$csde_meta = usces_has_custom_field_meta('delivery');
//20110411ysk start
	$applyform = usces_get_apply_addressform($usces->options['system']['addressform']);
//20110411ysk end

	//==========================================================================
	$usces_opt_order = get_option('usces_opt_order');
	if(!is_array($usces_opt_order)){
		$usces_opt_order = array();
	}
	$usces_opt_order['ftype_ord'] = $ext;
	$chk_ord = array();
	$chk_ord['ID'] = 1;
	$chk_ord['deco_id'] = 1;
	$chk_ord['date'] = 1;
	$chk_ord['mem_id'] = (isset($_REQUEST['check']['mem_id'])) ? 1 : 0;
	$chk_ord['email'] = (isset($_REQUEST['check']['email'])) ? 1 : 0;
	if(!empty($cscs_meta)) {
		foreach($cscs_meta as $key => $entry) {
			if($entry['position'] == 'name_pre') {
				$name = $entry['name'];
//20110208ysk start
				$cscs_key = 'cscs_'.$key;
				//$chk_ord[$name] = (isset($_REQUEST['check'][$name])) ? 1 : 0;
				$chk_ord[$cscs_key] = (isset($_REQUEST['check'][$cscs_key])) ? 1 : 0;
//20110208ysk end
			}
		}
	}
	$chk_ord['name'] = 1;
//20110411ysk start
	if($applyform == 'JP') {
		$chk_ord['kana'] = (isset($_REQUEST['check']['kana'])) ? 1 : 0;
	}
//20110411ysk end
	if(!empty($cscs_meta)) {
		foreach($cscs_meta as $key => $entry) {
			if($entry['position'] == 'name_after') {
				$name = $entry['name'];
//20110208ysk start
				$cscs_key = 'cscs_'.$key;
				//$chk_ord[$name] = (isset($_REQUEST['check'][$name])) ? 1 : 0;
				$chk_ord[$cscs_key] = (isset($_REQUEST['check'][$cscs_key])) ? 1 : 0;
//20110208ysk end
			}
		}
	}
	$chk_ord['zip'] = (isset($_REQUEST['check']['zip'])) ? 1 : 0;
//20110411ysk start
	$chk_ord['country'] = (isset($_REQUEST['check']['country'])) ? 1 : 0;
//20110411ysk end
	$chk_ord['pref'] = (isset($_REQUEST['check']['pref'])) ? 1 : 0;
	$chk_ord['address1'] = (isset($_REQUEST['check']['address1'])) ? 1 : 0;
	$chk_ord['address2'] = (isset($_REQUEST['check']['address2'])) ? 1 : 0;
	$chk_ord['address3'] = (isset($_REQUEST['check']['address3'])) ? 1 : 0;
	$chk_ord['tel'] = (isset($_REQUEST['check']['tel'])) ? 1 : 0;
	$chk_ord['fax'] = (isset($_REQUEST['check']['fax'])) ? 1 : 0;
	if(!empty($cscs_meta)) {
		foreach($cscs_meta as $key => $entry) {
			if($entry['position'] == 'fax_after') {
				$name = $entry['name'];
//20110208ysk start
				$cscs_key = 'cscs_'.$key;
				//$chk_ord[$name] = (isset($_REQUEST['check'][$name])) ? 1 : 0;
				$chk_ord[$cscs_key] = (isset($_REQUEST['check'][$cscs_key])) ? 1 : 0;
//20110208ysk end
			}
		}
	}
	//--------------------------------------------------------------------------
	if(!empty($csde_meta)) {
		foreach($csde_meta as $key => $entry) {
			if($entry['position'] == 'name_pre') {
				$name = $entry['name'];
//20110208ysk start
				$csde_key = 'csde_'.$key;
				//$chk_ord[$name] = (isset($_REQUEST['check'][$name])) ? 1 : 0;
				$chk_ord[$csde_key] = (isset($_REQUEST['check'][$csde_key])) ? 1 : 0;
//20110208ysk end
			}
		}
	}
	$chk_ord['delivery_name'] = (isset($_REQUEST['check']['delivery_name'])) ? 1 : 0;
//20110411ysk start
	if($applyform == 'JP') {
		$chk_ord['delivery_kana'] = (isset($_REQUEST['check']['delivery_kana'])) ? 1 : 0;
	}
//20110411ysk end
	if(!empty($csde_meta)) {
		foreach($csde_meta as $key => $entry) {
			if($entry['position'] == 'name_after') {
				$name = $entry['name'];
//20110208ysk start
				$csde_key = 'csde_'.$key;
				//$chk_ord[$name] = (isset($_REQUEST['check'][$name])) ? 1 : 0;
				$chk_ord[$csde_key] = (isset($_REQUEST['check'][$csde_key])) ? 1 : 0;
//20110208ysk end
			}
		}
	}
	$chk_ord['delivery_zip'] = (isset($_REQUEST['check']['delivery_zip'])) ? 1 : 0;
//20110411ysk start
	$chk_ord['delivery_country'] = (isset($_REQUEST['check']['delivery_country'])) ? 1 : 0;
//20110411ysk end
	$chk_ord['delivery_pref'] = (isset($_REQUEST['check']['delivery_pref'])) ? 1 : 0;
	$chk_ord['delivery_address1'] = (isset($_REQUEST['check']['delivery_address1'])) ? 1 : 0;
	$chk_ord['delivery_address2'] = (isset($_REQUEST['check']['delivery_address2'])) ? 1 : 0;
	$chk_ord['delivery_address3'] = (isset($_REQUEST['check']['delivery_address3'])) ? 1 : 0;
	$chk_ord['delivery_tel'] = (isset($_REQUEST['check']['delivery_tel'])) ? 1 : 0;
	$chk_ord['delivery_fax'] = (isset($_REQUEST['check']['delivery_fax'])) ? 1 : 0;
	if(!empty($csde_meta)) {
		foreach($csde_meta as $key => $entry) {
			if($entry['position'] == 'fax_after') {
				$name = $entry['name'];
//20110208ysk start
				$csde_key = 'csde_'.$key;
				//$chk_ord[$name] = (isset($_REQUEST['check'][$name])) ? 1 : 0;
				$chk_ord[$csde_key] = (isset($_REQUEST['check'][$csde_key])) ? 1 : 0;
//20110208ysk end
			}
		}
	}
	//--------------------------------------------------------------------------
	$chk_ord['shipping_date'] = (isset($_REQUEST['check']['shipping_date'])) ? 1 : 0;
	$chk_ord['peyment_method'] = (isset($_REQUEST['check']['peyment_method'])) ? 1 : 0;
	$chk_ord['delivery_method'] = (isset($_REQUEST['check']['delivery_method'])) ? 1 : 0;
//20101208ysk start
	$chk_ord['delivery_date'] = (isset($_REQUEST['check']['delivery_date'])) ? 1 : 0;
//20101208ysk end
	$chk_ord['delivery_time'] = (isset($_REQUEST['check']['delivery_time'])) ? 1 : 0;
	$chk_ord['delidue_date'] = (isset($_REQUEST['check']['delidue_date'])) ? 1 : 0;
	$chk_ord['status'] = (isset($_REQUEST['check']['status'])) ? 1 : 0;
	$chk_ord['total_amount'] = 1;
	$chk_ord['getpoint'] = (isset($_REQUEST['check']['getpoint'])) ? 1 : 0;
	$chk_ord['usedpoint'] = (isset($_REQUEST['check']['usedpoint'])) ? 1 : 0;
	$chk_ord['discount'] = 1;
	$chk_ord['shipping_charge'] = 1;
	$chk_ord['cod_fee'] = 1;
	$chk_ord['tax'] = 1;
	$chk_ord['note'] = (isset($_REQUEST['check']['note'])) ? 1 : 0;
	if(!empty($csod_meta)) {
		foreach($csod_meta as $key => $entry) {
			$name = $entry['name'];
//20110208ysk start
			$csod_key = 'csod_'.$key;
			//$chk_ord[$name] = (isset($_REQUEST['check'][$name])) ? 1 : 0;
			$chk_ord[$csod_key] = (isset($_REQUEST['check'][$csod_key])) ? 1 : 0;
//20110208ysk end
		}
	}
	$usces_opt_order['chk_ord'] = apply_filters( 'usces_filter_chk_ord', $chk_ord );
	update_option('usces_opt_order', $usces_opt_order);
	//==========================================================================

	if(isset($_REQUEST['check']['status'])) {
		//$usces_management_status = get_option('usces_management_status');
		$usces_management_status = apply_filters( 'usces_filter_management_status', get_option('usces_management_status') );//0000530
		$usces_management_status['new'] = __('new order', 'usces');
	}

	$_REQUEST['searchIn'] = "searchIn";
	$tableName = $wpdb->prefix."usces_order";
	$arr_column = array(
				__('ID', 'usces') => 'ID', 
				__('Order number', 'usces') => 'deco_id', 
				__('date', 'usces') => 'date', 
				__('membership number', 'usces') => 'mem_id', 
				__('name', 'usces') => 'name', 
				__('Region', 'usces') => 'pref', 
				__('shipping option', 'usces') => 'delivery_method', 
				__('Amount', 'usces') => 'total_price', 
				__('payment method', 'usces') => 'payment_name', 
				__('transfer statement', 'usces') => 'receipt_status', 
				__('Processing', 'usces') => 'order_status', 
				__('shpping date', 'usces') => 'order_modified');
	$DT = new dataList($tableName, $arr_column);
//20101202ysk start
	$DT->pageLimit = 'off';
//20101202ysk end
	$res = $DT->MakeTable();
	$rows = $DT->rows;

	//==========================================================================
	$line = $table_h;
	$line .= $tr_h;
	$line .= $th_h1.__('ID', 'usces').$th_f;
	$line .= $th_h.__('Order number', 'usces').$th_f;
	$line .= $th_h.__('order date', 'usces').$th_f;
	if(isset($_REQUEST['check']['mem_id'])) $line .= $th_h.__('membership number', 'usces').$th_f;
	if(isset($_REQUEST['check']['email'])) $line .= $th_h.__('e-mail', 'usces').$th_f;
	if(!empty($cscs_meta)) {
		foreach($cscs_meta as $key => $entry) {
			if($entry['position'] == 'name_pre') {
				$name = $entry['name'];
//20110208ysk start
				$cscs_key = 'cscs_'.$key;
				//if(isset($_REQUEST['check'][$name])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
				if(isset($_REQUEST['check'][$cscs_key])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
//20110208ysk end
			}
		}
	}
	$line .= $th_h.__('name', 'usces').$th_f;
//20110411ysk start
	if($applyform == 'JP') {
		if(isset($_REQUEST['check']['kana'])) $line .= $th_h.__('furigana', 'usces').$th_f;
	}
//20110411ysk end
	if(!empty($cscs_meta)) {
		foreach($cscs_meta as $key => $entry) {
			if($entry['position'] == 'name_after') {
				$name = $entry['name'];
//20110208ysk start
				$cscs_key = 'cscs_'.$key;
				//if(isset($_REQUEST['check'][$name])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
				if(isset($_REQUEST['check'][$cscs_key])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
//20110208ysk end
			}
		}
	}
//20110411ysk start
	switch($applyform) {
	case 'JP':
		if(isset($_REQUEST['check']['zip'])) $line .= $th_h.__('Zip/Postal Code', 'usces').$th_f;
		if(isset($_REQUEST['check']['country'])) $line .= $th_h.__('Country', 'usces').$th_f;
		if(isset($_REQUEST['check']['pref'])) $line .= $th_h.__('Province', 'usces').$th_f;
		if(isset($_REQUEST['check']['address1'])) $line .= $th_h.__('city', 'usces').$th_f;
		if(isset($_REQUEST['check']['address2'])) $line .= $th_h.__('numbers', 'usces').$th_f;
		if(isset($_REQUEST['check']['address3'])) $line .= $th_h.__('building name', 'usces').$th_f;
		if(isset($_REQUEST['check']['tel'])) $line .= $th_h.__('Phone number', 'usces').$th_f;
		if(isset($_REQUEST['check']['fax'])) $line .= $th_h.__('FAX number', 'usces').$th_f;
		break;
	case 'US':
	default:
		if(isset($_REQUEST['check']['address2'])) $line .= $th_h.__('Address Line1', 'usces').$th_f;
		if(isset($_REQUEST['check']['address3'])) $line .= $th_h.__('Address Line2', 'usces').$th_f;
		if(isset($_REQUEST['check']['address1'])) $line .= $th_h.__('city', 'usces').$th_f;
		if(isset($_REQUEST['check']['pref'])) $line .= $th_h.__('State', 'usces').$th_f;
		if(isset($_REQUEST['check']['country'])) $line .= $th_h.__('Country', 'usces').$th_f;
		if(isset($_REQUEST['check']['zip'])) $line .= $th_h.__('Zip', 'usces').$th_f;
		if(isset($_REQUEST['check']['tel'])) $line .= $th_h.__('Phone number', 'usces').$th_f;
		if(isset($_REQUEST['check']['fax'])) $line .= $th_h.__('FAX number', 'usces').$th_f;
		break;
	}
//20110411ysk end
	if(!empty($cscs_meta)) {
		foreach($cscs_meta as $key => $entry) {
			if($entry['position'] == 'fax_after') {
				$name = $entry['name'];
//20110208ysk start
				$cscs_key = 'cscs_'.$key;
				//if(isset($_REQUEST['check'][$name])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
				if(isset($_REQUEST['check'][$cscs_key])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
//20110208ysk end
			}
		}
	}
	$line .= apply_filters( 'usces_filter_chk_ord_label_customer', NULL, $usces_opt_order, $rows );

	//--------------------------------------------------------------------------
	if(!empty($csde_meta)) {
		foreach($csde_meta as $key => $entry) {
			if($entry['position'] == 'name_pre') {
				$name = $entry['name'];
//20110208ysk start
				$csde_key = 'csde_'.$key;
				//if(isset($_REQUEST['check'][$name])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
				if(isset($_REQUEST['check'][$csde_key])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
//20110208ysk end
			}
		}
	}
	if(isset($_REQUEST['check']['delivery_name'])) $line .= $th_h.__('Shipping Name', 'usces').$th_f;
//20110411ysk start
	if($applyform == 'JP') {
		if(isset($_REQUEST['check']['delivery_kana'])) $line .= $th_h.__('Shipping Furigana', 'usces').$th_f;
	}
//20110411ysk end
	if(!empty($csde_meta)) {
		foreach($csde_meta as $key => $entry) {
			if($entry['position'] == 'name_after') {
				$name = $entry['name'];
//20110208ysk start
				$csde_key = 'csde_'.$key;
				//if(isset($_REQUEST['check'][$name])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
				if(isset($_REQUEST['check'][$csde_key])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
//20110208ysk end
			}
		}
	}
//20110411ysk start
	switch($applyform) {
	case 'JP':
		if(isset($_REQUEST['check']['delivery_zip'])) $line .= $th_h.__('Shipping Zip', 'usces').$th_f;
		if(isset($_REQUEST['check']['delivery_country'])) $line .= $th_h.__('Shipping Country', 'usces').$th_f;
		if(isset($_REQUEST['check']['delivery_pref'])) $line .= $th_h.__('Shipping State', 'usces').$th_f;
		if(isset($_REQUEST['check']['delivery_address1'])) $line .= $th_h.__('Shipping City', 'usces').$th_f;
		if(isset($_REQUEST['check']['delivery_address2'])) $line .= $th_h.__('Shipping Address1', 'usces').$th_f;
		if(isset($_REQUEST['check']['delivery_address3'])) $line .= $th_h.__('Shipping Address2', 'usces').$th_f;
		if(isset($_REQUEST['check']['delivery_tel'])) $line .= $th_h.__('Shipping Phone', 'usces').$th_f;
		if(isset($_REQUEST['check']['delivery_fax'])) $line .= $th_h.__('Shipping FAX', 'usces').$th_f;
		break;
	case 'US':
	default:
		if(isset($_REQUEST['check']['delivery_address2'])) $line .= $th_h.__('Shipping Address1', 'usces').$th_f;
		if(isset($_REQUEST['check']['delivery_address3'])) $line .= $th_h.__('Shipping Address2', 'usces').$th_f;
		if(isset($_REQUEST['check']['delivery_address1'])) $line .= $th_h.__('Shipping City', 'usces').$th_f;
		if(isset($_REQUEST['check']['delivery_pref'])) $line .= $th_h.__('Shipping State', 'usces').$th_f;
		if(isset($_REQUEST['check']['delivery_country'])) $line .= $th_h.__('Shipping Country', 'usces').$th_f;
		if(isset($_REQUEST['check']['delivery_zip'])) $line .= $th_h.__('Shipping Zip', 'usces').$th_f;
		if(isset($_REQUEST['check']['delivery_tel'])) $line .= $th_h.__('Shipping Phone', 'usces').$th_f;
		if(isset($_REQUEST['check']['delivery_fax'])) $line .= $th_h.__('Shipping FAX', 'usces').$th_f;
		break;
	}
//20110411ysk end
	if(!empty($csde_meta)) {
		foreach($csde_meta as $key => $entry) {
			if($entry['position'] == 'fax_after') {
				$name = $entry['name'];
//20110208ysk start
				$csde_key = 'csde_'.$key;
				//if(isset($_REQUEST['check'][$name])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
				if(isset($_REQUEST['check'][$csde_key])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
//20110208ysk end
			}
		}
	}
	$line .= apply_filters( 'usces_filter_chk_ord_label_delivery', NULL, $usces_opt_order, $rows );
	//--------------------------------------------------------------------------
	if(isset($_REQUEST['check']['shipping_date'])) $line .= $th_h.__('shpping date', 'usces').$th_f;
	if(isset($_REQUEST['check']['peyment_method'])) $line .= $th_h.__('payment method', 'usces').$th_f;
	if(isset($_REQUEST['check']['delivery_method'])) $line .= $th_h.__('shipping option', 'usces').$th_f;
//20101208ysk start
	if(isset($_REQUEST['check']['delivery_date'])) $line .= $th_h.__('Delivery date', 'usces').$th_f;
//20101208ysk end
	if(isset($_REQUEST['check']['delivery_time'])) $line .= $th_h.__('delivery time', 'usces').$th_f;
	if(isset($_REQUEST['check']['delidue_date'])) $line .= $th_h.__('Shipping date', 'usces').$th_f;
	if(isset($_REQUEST['check']['status'])) $line .= $th_h.__('Status', 'usces').$th_f;
	$line .= $th_h.__('Total Amount', 'usces').$th_f;
	if(isset($_REQUEST['check']['getpoint'])) $line .= $th_h.__('granted points', 'usces').$th_f;
	if(isset($_REQUEST['check']['usedpoint'])) $line .= $th_h.__('Used points', 'usces').$th_f;
	$line .= $th_h.__('Disnount', 'usces').$th_f;
	$line .= $th_h.__('Shipping', 'usces').$th_f;
	$line .= $th_h.apply_filters('usces_filter_cod_label', __('COD fee', 'usces')).$th_f;
	$line .= $th_h.__('consumption tax', 'usces').$th_f;
	if(isset($_REQUEST['check']['note'])) $line .= $th_h.__('Notes', 'usces').$th_f;
	if(!empty($csod_meta)) {
		foreach($csod_meta as $key => $entry) {
			$name = $entry['name'];
//20110208ysk start
			$csod_key = 'csod_'.$key;
			//if(isset($_REQUEST['check'][$name])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
			if(isset($_REQUEST['check'][$csod_key])) $line .= $th_h.usces_entity_decode($name, $ext).$th_f;
//20110208ysk end
		}
	}
	$line .= apply_filters( 'usces_filter_chk_ord_label_order', NULL, $usces_opt_order, $rows );
	$line .= $tr_f.$lf;
	//==========================================================================
	foreach((array)$rows as $array) {
		$order_id = $array['ID'];
		$query = $wpdb->prepare("SELECT * FROM $tableName WHERE ID = %d", $order_id);
		$data = $wpdb->get_row( $query, ARRAY_A );
		//$deli = stripslashes_deep(unserialize($data['order_delivery']));
		$deli = unserialize($data['order_delivery']);
		//if(!empty($data)) {
		//	$data = stripslashes_deep($data);
		//}

		$line .= $tr_h;
		$line .= $td_h1.$order_id.$td_f;
		$line .= $td_h.usces_get_deco_order_id( $order_id ).$td_f;
		$line .= $td_h.$data['order_date'].$td_f;
		if(isset($_REQUEST['check']['mem_id'])) $line .= $td_h.$data['mem_id'].$td_f;
		if(isset($_REQUEST['check']['email'])) $line .= $td_h.usces_entity_decode($data['order_email'], $ext).$td_f;
		if(!empty($cscs_meta)) {
			foreach($cscs_meta as $key => $entry) {
				if($entry['position'] == 'name_pre') {
					$name = $entry['name'];
					$cscs_key = 'cscs_'.$key;
//20110208ysk start
					//if(isset($_REQUEST['check'][$name])) {
					if(isset($_REQUEST['check'][$cscs_key])) {
//20110208ysk end
						$value = maybe_unserialize($usces->get_order_meta_value($cscs_key, $order_id));
						if(empty($value)) {
							$value = '';
						} elseif(is_array($value)) {
							$concatval = '';
							$c = '';
							foreach($value as $v) {
								$concatval .= $c.$v;
								$c = ' ';
							}
							$value = $concatval;
						}
						$line .= $td_h.usces_entity_decode($value, $ext).$td_f;
					}
				}
			}
		}
//20110411ysk start
		switch($applyform) {
		case 'JP': 
			$line .= $td_h.usces_entity_decode($data['order_name1'].' '.$data['order_name2'], $ext).$td_f;
			if(isset($_REQUEST['check']['kana'])) $line .= $td_h.usces_entity_decode($data['order_name3'].' '.$data['order_name4'], $ext).$td_f;
			break;
		case 'US':
		default:
			$line .= $td_h.usces_entity_decode($data['order_name2'].' '.$data['order_name1'], $ext).$td_f;
			break;
		}
//20110411ysk end
		if(!empty($cscs_meta)) {
			foreach($cscs_meta as $key => $entry) {
				if($entry['position'] == 'name_after') {
					$name = $entry['name'];
					$cscs_key = 'cscs_'.$key;
//20110208ysk start
					//if(isset($_REQUEST['check'][$name])) {
					if(isset($_REQUEST['check'][$cscs_key])) {
//20110208ysk end
						$value = maybe_unserialize($usces->get_order_meta_value($cscs_key, $order_id));
						if(empty($value)) {
							$value = '';
						} elseif(is_array($value)) {
							$concatval = '';
							$c = '';
							foreach($value as $v) {
								$concatval .= $c.$v;
								$c = ' ';
							}
							$value = $concatval;
						}
						$line .= $td_h.usces_entity_decode($value, $ext).$td_f;
					}
				}
			}
		}
//20110411ysk start
		$address_info = '';
		switch($applyform) {
		case 'JP':
			if(isset($_REQUEST['check']['zip'])) $address_info .= $td_h.usces_entity_decode($data['order_zip'], $ext).$td_f;
			if(isset($_REQUEST['check']['country'])) $address_info .= $td_h.$usces_settings['country'][$usces->get_order_meta_value('customer_country', $order_id)].$td_f;
			if(isset($_REQUEST['check']['pref'])) $address_info .= $td_h.usces_entity_decode($data['order_pref'], $ext).$td_f;
			if(isset($_REQUEST['check']['address1'])) $address_info .= $td_h.usces_entity_decode($data['order_address1'], $ext).$td_f;
			if(isset($_REQUEST['check']['address2'])) $address_info .= $td_h.usces_entity_decode($data['order_address2'], $ext).$td_f;
			if(isset($_REQUEST['check']['address3'])) $address_info .= $td_h.usces_entity_decode($data['order_address3'], $ext).$td_f;
			if(isset($_REQUEST['check']['tel'])) $address_info .= $td_h.usces_entity_decode($data['order_tel'], $ext).$td_f;
			if(isset($_REQUEST['check']['fax'])) $address_info .= $td_h.usces_entity_decode($data['order_fax'], $ext).$td_f;
			break;
		case 'US':
		default:
			if(isset($_REQUEST['check']['address2'])) $address_info .= $td_h.usces_entity_decode($data['order_address2'], $ext).$td_f;
			if(isset($_REQUEST['check']['address3'])) $address_info .= $td_h.usces_entity_decode($data['order_address3'], $ext).$td_f;
			if(isset($_REQUEST['check']['address1'])) $address_info .= $td_h.usces_entity_decode($data['order_address1'], $ext).$td_f;
			if(isset($_REQUEST['check']['pref'])) $address_info .= $td_h.usces_entity_decode($data['order_pref'], $ext).$td_f;
			if(isset($_REQUEST['check']['country'])) $address_info .= $td_h.$usces_settings['country'][$usces->get_order_meta_value('customer_country', $order_id)].$td_f;
			if(isset($_REQUEST['check']['zip'])) $address_info .= $td_h.usces_entity_decode($data['order_zip'], $ext).$td_f;
			if(isset($_REQUEST['check']['tel'])) $address_info .= $td_h.usces_entity_decode($data['order_tel'], $ext).$td_f;
			if(isset($_REQUEST['check']['fax'])) $address_info .= $td_h.usces_entity_decode($data['order_fax'], $ext).$td_f;
			break;
		}
		$address_info_args = compact( 'td_h', 'td_f', 'ext', 'order_id', 'applyform' );
		$line .= apply_filters( 'usces_filter_ord_csv_address_info', $address_info, $data, $address_info_args );
//20110411ysk end
		if(!empty($cscs_meta)) {
			foreach($cscs_meta as $key => $entry) {
				if($entry['position'] == 'fax_after') {
					$name = $entry['name'];
					$cscs_key = 'cscs_'.$key;
//20110208ysk start
					//if(isset($_REQUEST['check'][$name])) {
					if(isset($_REQUEST['check'][$cscs_key])) {
//20110208ysk end
						$value = maybe_unserialize($usces->get_order_meta_value($cscs_key, $order_id));
						if(empty($value)) {
							$value = '';
						} elseif(is_array($value)) {
							$concatval = '';
							$c = '';
							foreach($value as $v) {
								$concatval .= $c.$v;
								$c = ' ';
							}
							$value = $concatval;
						}
						$line .= $td_h.usces_entity_decode($value, $ext).$td_f;
					}
				}
			}
		}
		$line .= apply_filters( 'usces_filter_chk_ord_data_customer', NULL, $usces_opt_order, $order_id, $data );
		//----------------------------------------------------------------------
		if(!empty($csde_meta)) {
			foreach($csde_meta as $key => $entry) {
				if($entry['position'] == 'name_pre') {
					$name = $entry['name'];
					$csde_key = 'csde_'.$key;
//20110208ysk start
					//if(isset($_REQUEST['check'][$name])) {
					if(isset($_REQUEST['check'][$csde_key])) {
//20110208ysk end
						$value = maybe_unserialize($usces->get_order_meta_value($csde_key, $order_id));
						if(empty($value)) {
							$value = '';
						} elseif(is_array($value)) {
							$concatval = '';
							$c = '';
							foreach($value as $v) {
								$concatval .= $c.$v;
								$c = ' ';
							}
							$value = $concatval;
						}
						$line .= $td_h.usces_entity_decode($value, $ext).$td_f;
					}
				}
			}
		}
//20110411ysk start
		switch($applyform) {
		case 'JP':
			if(isset($_REQUEST['check']['delivery_name'])) $line .= $td_h.usces_entity_decode($deli['name1'].' '.$deli['name2'], $ext).$td_f;
			if(isset($_REQUEST['check']['delivery_kana'])) $line .= $td_h.usces_entity_decode($deli['name3'].' '.$deli['name4'], $ext).$td_f;
			break;
		case 'US':
		default:
			if(isset($_REQUEST['check']['delivery_name'])) $line .= $td_h.usces_entity_decode($deli['name2'].' '.$deli['name1'], $ext).$td_f;
			break;
		}
//20110411ysk end
		if(!empty($csde_meta)) {
			foreach($csde_meta as $key => $entry) {
				if($entry['position'] == 'name_after') {
					$name = $entry['name']."</td>";
					$csde_key = 'csde_'.$key;
//20110208ysk start
					//if(isset($_REQUEST['check'][$name])) {
					if(isset($_REQUEST['check'][$csde_key])) {
//20110208ysk end
						$value = maybe_unserialize($usces->get_order_meta_value($csde_key, $order_id));
						if(empty($value)) {
							$value = '';
						} elseif(is_array($value)) {
							$concatval = '';
							$c = '';
							foreach($value as $v) {
								$concatval .= $c.$v;
								$c = ' ';
							}
							$value = $concatval;
						}
						$line .= $td_h.usces_entity_decode($value, $ext).$td_f;
					}
				}
			}
		}
//20110411ysk start
		$address_info = '';
		switch($applyform) {
		case 'JP':
			if(isset($_REQUEST['check']['delivery_zip'])) $address_info .= $td_h.usces_entity_decode($deli['zipcode'], $ext).$td_f;
			if(isset($_REQUEST['check']['delivery_country'])) $address_info .= $td_h.$usces_settings['country'][$deli['country']].$td_f;
			if(isset($_REQUEST['check']['delivery_pref'])) $address_info .= $td_h.usces_entity_decode($deli['pref'], $ext).$td_f;
			if(isset($_REQUEST['check']['delivery_address1'])) $address_info .= $td_h.usces_entity_decode($deli['address1'], $ext).$td_f;
			if(isset($_REQUEST['check']['delivery_address2'])) $address_info .= $td_h.usces_entity_decode($deli['address2'], $ext).$td_f;
			if(isset($_REQUEST['check']['delivery_address3'])) $address_info .= $td_h.usces_entity_decode($deli['address3'], $ext).$td_f;
			if(isset($_REQUEST['check']['delivery_tel'])) $address_info .= $td_h.usces_entity_decode($deli['tel'], $ext).$td_f;
			if(isset($_REQUEST['check']['delivery_fax'])) $address_info .= $td_h.usces_entity_decode($deli['fax'], $ext).$td_f;
			break;
		case 'US':
		default:
			if(isset($_REQUEST['check']['delivery_address2'])) $address_info .= $td_h.usces_entity_decode($deli['address2'], $ext).$td_f;
			if(isset($_REQUEST['check']['delivery_address3'])) $address_info .= $td_h.usces_entity_decode($deli['address3'], $ext).$td_f;
			if(isset($_REQUEST['check']['delivery_address1'])) $address_info .= $td_h.usces_entity_decode($deli['address1'], $ext).$td_f;
			if(isset($_REQUEST['check']['delivery_pref'])) $address_info .= $td_h.usces_entity_decode($deli['pref'], $ext).$td_f;
			if(isset($_REQUEST['check']['delivery_country'])) $address_info .= $td_h.$usces_settings['country'][$deli['country']].$td_f;
			if(isset($_REQUEST['check']['delivery_zip'])) $address_info .= $td_h.usces_entity_decode($deli['zipcode'], $ext).$td_f;
			if(isset($_REQUEST['check']['delivery_tel'])) $address_info .= $td_h.usces_entity_decode($deli['tel'], $ext).$td_f;
			if(isset($_REQUEST['check']['delivery_fax'])) $address_info .= $td_h.usces_entity_decode($deli['fax'], $ext).$td_f;
			break;
		}
		$line .= apply_filters( 'usces_filter_ord_csv_delivery_address_info', $address_info, $deli, $address_info_args );
//20110411ysk end
		if(!empty($csde_meta)) {
			foreach($csde_meta as $key => $entry) {
				if($entry['position'] == 'fax_after') {
					$name = $entry['name'];
					$csde_key = 'csde_'.$key;
//20110208ysk start
					//if(isset($_REQUEST['check'][$name])) {
					if(isset($_REQUEST['check'][$csde_key])) {
//20110208ysk end
						$value = maybe_unserialize($usces->get_order_meta_value($csde_key, $order_id));
						if(empty($value)) {
							$value = '';
						} elseif(is_array($value)) {
							$concatval = '';
							$c = '';
							foreach($value as $v) {
								$concatval .= $c.$v;
								$c = ' ';
							}
							$value = $concatval;
						}
						$line .= $td_h.usces_entity_decode($value, $ext).$td_f;
					}
				}
			}
		}
		$line .= apply_filters( 'usces_filter_chk_ord_data_delivery', NULL, $usces_opt_order, $order_id, $deli );
		//----------------------------------------------------------------------
		if(isset($_REQUEST['check']['shipping_date'])) $line .= $td_h.$data['order_modified'].$td_f;
		if(isset($_REQUEST['check']['peyment_method'])) $line .= $td_h.$data['order_payment_name'].$td_f;
		if(isset($_REQUEST['check']['delivery_method'])) {
			$delivery_method = '';
			if(strtoupper($data['order_delivery_method']) == '#NONE#') {
				$delivery_method = __('No preference', 'usces');
			} else {
				foreach((array)$usces->options['delivery_method'] as $dkey => $delivery) {
					if($delivery['id'] == $data['order_delivery_method']) {
						$delivery_method = $delivery['name'];
						break;
					}
				}
			}
			$line .= $td_h.$delivery_method.$td_f;
		}
//20101208ysk start
		if(isset($_REQUEST['check']['delivery_date'])) $line .= $td_h.$data['order_delivery_date'].$td_f;
//20101208ysk end
		if(isset($_REQUEST['check']['delivery_time'])) $line .= $td_h.$data['order_delivery_time'].$td_f;
		if(isset($_REQUEST['check']['delidue_date'])) {
			$order_delidue_date = (strtoupper($data['order_delidue_date']) == '#NONE#') ? '' : $data['order_delidue_date'];
			$line .= $td_h.$order_delidue_date.$td_f;
		}
		if(isset($_REQUEST['check']['status'])) {
			$order_status = explode(',', $data['order_status']);
			$status = '';
			foreach( (array)$order_status as $os) {
				if( isset($usces_management_status[$os]) ) 
					$status .= $usces_management_status[$os].$sp;
			}
			$line .= $td_h.trim($status, $sp).$td_f;
		}
		$line .= $td_h.usces_crform($array['total_price'], false, false, 'return', false).$td_f;
		if(isset($_REQUEST['check']['getpoint'])) $line .= $td_h.$data['order_getpoint'].$td_f;
		if(isset($_REQUEST['check']['usedpoint'])) $line .= $td_h.$data['order_usedpoint'].$td_f;
		$line .= $td_h.$data['order_discount'].$td_f;
		$line .= $td_h.$data['order_shipping_charge'].$td_f;
		$line .= $td_h.$data['order_cod_fee'].$td_f;
		$line .= $td_h.$data['order_tax'].$td_f;
		if(isset($_REQUEST['check']['note'])) $line .= $td_h.usces_entity_decode($data['order_note'], $ext).$td_f;
		if(!empty($csod_meta)) {
			foreach($csod_meta as $key => $entry) {
				$name = $entry['name'];
				$csod_key = 'csod_'.$key;
//20110208ysk start
				//if(isset($_REQUEST['check'][$name])) {
				if(isset($_REQUEST['check'][$csod_key])) {
//20110208ysk end
					$value = maybe_unserialize($usces->get_order_meta_value($csod_key, $order_id));
					if(empty($value)) {
						$value = '';
					} elseif(is_array($value)) {
						$concatval = '';
						$c = '';
						foreach($value as $v) {
							$concatval .= $c.$v;
							$c = ' ';
						}
						$value = $concatval;
					}
					$line .= $td_h.usces_entity_decode($value, $ext).$td_f;
				}
			}
		}
		$line .= apply_filters( 'usces_filter_chk_ord_data_order', NULL, $usces_opt_order, $order_id, $data );
		$line .= $tr_f.$lf;
	}
	$line .= $table_f.$lf;
	//==========================================================================

	if($ext == 'xls') {
		header("Content-Type: application/vnd.ms-excel; charset=Shift-JIS");
	} elseif($ext == 'csv') {
		header("Content-Type: application/octet-stream");
	}
	header("Content-Disposition: attachment; filename=usces_order_list.".$ext);
	mb_http_output('pass');
	print(mb_convert_encoding($line, "SJIS-win", "UTF-8"));
	exit();
}

function usces_entity_decode($str, $ftype) {
	$pos = strpos($str, '&');
	if($pos !== false)
		$str = htmlspecialchars_decode($str);
	
	if($ftype == 'csv')
		$str = str_replace('"', '""', $str);
		
		return $str;
}

function usces_is_entity($entity){
	$temp = substr($entity, 0, 1);
	$temp .= substr($entity, -1, 1);
	if ($temp != '&;')
		return false;
	else
		return true;
}

function usces_p( $var ){
	echo '<pre>' . print_r($var, true) . '</pre>';
}

function usces_get_key( $digit ){
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$max = strlen($chars) - 1;
	$str = '';
	for($i=0; $i<$digit; $i++){
		$str .= $chars[mt_rand(0, $max)];
	}
	return $str;
}

function usces_schedule_event(){
	
	$gmt_offset = get_option('gmt_offset');
	$now = current_time('timestamp', 0);
	$timestamp = mktime(3, 0, 0, date('n',$now), date('j',$now), date('Y',$now)) - ($gmt_offset * 3600);
	wp_schedule_event($timestamp, 'daily', 'wc_cron');

}

function usces_cron_do(){
	usces_clearup_lostkey();
	usces_clearup_acting_data();
}
