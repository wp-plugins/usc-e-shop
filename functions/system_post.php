<?php
/**
 * system_option
 */
function usces_add_system_option( $option_name, $newvalue ){
	global $usces;
	$option_value = get_option($option_name);

	if( !empty($option_value) ){
		$option_num = count($option_value);
		$unique = true;
		foreach( (array)$option_value as $value ){
			if( $value['name'] == $newvalue['name'] )
				$unique = false;
			if( !isset($value['sort']) )
				$sortnull = false;
			$sort[] = $value['sort'];
		}
		if( !$unique )
			return -1;
		
		rsort($sort);
		$next_number = $sort[0] + 1;
		$unique_sort = array_unique($sort, SORT_REGULAR);
		if( $option_num != count($unique_sort) || $option_num != $next_number || !$sortnull){
			//To repair the sort data
			for( $i=0; $i<$option_num; $i++ ){
				$option_value[$i]['sort'] = $i;
			}
		}
	}
	$newvalue['sort'] = !empty($option_num) ? $option_num : 0;
	$option_value[] = $newvalue;
	update_option($option_name, $option_value);
	krsort($option_value);
	$key_arr = each($option_value);
	$last_index = $key_arr['key'];
	
	return $last_index;
}

function usces_update_system_option( $option_name, $index, $newvalue ){
	global $usces;
	$option_value = get_option($option_name);

	if( !empty($option_value) ){
		$unique = true;
		foreach( (array)$option_value as $value ){
			if( $value['name'] == $newvalue['name'] ){
				$unique = false;
				break;
			}
		}
		if( !$unique )
			return -1;
			
		$option_value[$index] = $newvalue;
		update_option($option_name, $option_value);
		$lid = $index;
	}else{
		$lid = -1;
	}
	
	return $lid;
}
function usces_get_system_option( $option_name, $keyflag = 'sort' ) {
	$sysopts = array();
	$option_value = get_option($option_name);

	if( !is_array($option_value) ) return $sysopts;
	
	foreach( $option_value as $id => $value ){
		$key = isset($value[$keyflag]) ? $value[$keyflag] : $value['sort'];
		switch( $option_name ){
			case 'usces_payment_method':
				$sysopts[$key] = array(
									'id' => $id,
									'name' => $value['name'],
									'explanation' => $value['explanation'],
									'settlement' => $value['settlement'],
									'module' => $value['module'],
									'sort' => $value['sort']
								);
			break;
		}
	}
	ksort($sysopts);

	return $sysopts;
}

function usces_sort_system_option( $option_name, $idstr ) {
	global $usces;
	$option_value = get_option($option_name);

	if( !empty($option_value) ){
		$ids = explode(',', $idstr);
		$c = 0;
		foreach( (array)$ids as $id ){
//			usces_log('ids : '.$id.$c, 'acting_transaction.log');
			$option_value[$id]['sort'] = $c;
			$c++;
//			usces_log('options : '.print_r($option_value,true), 'acting_transaction.log');
		}
		update_option( $option_name, $option_value );
	}
	return;
}

function usces_del_system_option( $option_name, $id ) {
	global $usces;
	$option_value = get_option($option_name);

	if( !empty($option_value) && isset($option_value[$id])){
		unset($option_value[$id]);
		$c = 0;
		foreach( (array)$option_value as $key => $value ){
			$option_value[$key]['sort'] = $c;
			$c++;
		}
		update_option( $option_name, $option_value );
	}
	return;
}

/**
 * payment_list
 */
function payment_list( $option_value ) {
	// Exit if no meta
	if ( empty($option_value) ) {
		?>
		<table id="payment-table" class="list" style="display: none;">
			<thead>
			<tr>
				<th class="hanldh">　</th>
				<th class="left"><?php _e('A payment method name','usces'); ?></th>
				<th><?php _e('explanation','usces'); ?></th>
				<th><?php _e('Type of payment','usces'); ?></th>
				<th><?php _e('Payment module','usces'); ?></th>
			</tr>
			</thead>
			<tbody id="payment-list">
			<tr><td></td><td></td><td></td></tr>
			</tbody>
		</table>
		<?php
	}else{
		?>
		<table id="payment-table" class="list">
			<thead>
			<tr>
				<th class="hanldh">　</th>
				<th class="paymentname"><?php _e('A payment method name','usces') ?></th>
				<th class="paymentexplanation"><?php _e('explanation','usces') ?></th>
				<th class="paymentsettlement"><?php _e('Type of payment','usces') ?></th>
				<th class="paymentmodule"><?php _e('Payment module','usces') ?></th>
			</tr>
			</thead>
			<tbody id="payment-list">
		<?php
			foreach ( $option_value as $value )
				echo _payment_list_row( $value );
		?>
			</tbody>
		</table>
		<div id="payment_ajax-response"></div>
		<?php
	}
}

/**
 * payment_list_row
 */
function _payment_list_row( $value ) {
	global $usces;
	$r = '';
	$style = '';

	if ( empty($value) ) return;
	$id = (int) $value['id'];
	$name = esc_attr($value['name']);
	$explanation = esc_attr($value['explanation']);
	$settlement = $value['settlement'];
	$module = esc_attr($value['module']);
	$sort = (int) $value['sort'];

	ob_start();
	?>
	<tr class='metastuffrow'>
	<td colspan='5'>
		<table id='payment-<?php echo $id; ?>' class='metastufftable'>
			<tr>
				<th class='handlb' rowspan='2'>　</th>
				<td class='paymentname'><div><input name='payment[<?php echo $id; ?>][name]' id='payment[<?php echo $id; ?>][name]' class='metaboxfield' type='text' value='<?php echo $name; ?>' /></div></td>
				<td class='paymentexplanation'><textarea name='payment[<?php echo $id; ?>][explanation]' id='payment[<?php echo $id; ?>][explanation]' class='metaboxfield'><?php echo $explanation; ?></textarea></td>
				<td class='paymentsettlement'>
					<select name='payment[<?php echo $id; ?>][settlement]' id='payment[<?php echo $id; ?>][settlement]' class='metaboxfield'>
					<?php
					foreach ($usces->payment_structure as $psk => $psv){
						$selected = ($psk == $settlement) ? ' selected="selected"' : '';
					?>
						<option value='<?php echo $psk; ?>'<?php echo $selected; ?>><?php echo esc_html($psv); ?></option>
					<?php
					}
					?>
					</select>
				</td>
				<td class='paymentmodule'><div><input name='payment[<?php echo $id; ?>][module]' id='payment[<?php echo $id; ?>][module]' class='metaboxfield' type='text' value='<?php echo $module; ?>' /></div></td>
			</tr>
			<tr>
				<td colspan='4' class='submittd'>
					<div id='paymentsubmit-<?php echo $id; ?>' class='submit'>
						<input name='deletepayment' id='deletepayment[<?php echo $id; ?>]' type='button' value='<?php esc_attr_e(__( 'Delete' )); ?>' onclick="payment.post('del', <?php echo $id; ?>);" />
						<input name='updatepayment' id='updatepayment[<?php echo $id; ?>]' type='button' value='<?php esc_attr_e(__( 'Update' )); ?>' onclick="payment.post('update', <?php echo $id; ?>);" />
						<input name='payment[<?php echo $id; ?>][sort]' id='payment[<?php echo $id; ?>][sort]' type='hidden' value='<?php echo $sort; ?>' />
					</div>
					<div id='payment_loading-<?php echo $id; ?>' class='meta_submit_loading'></div>
				</td>
			</tr>
		</table>
	</td>
	</tr>
	<?php
	$r = ob_get_contents();
	ob_end_clean();
	return $r;
}
/**
 * payment_form
 */
function payment_form() {
	global $usces;
	?>
	<p><strong><?php _e('Add a new method forpayment ','usces') ?> : </strong></p>
	<table id="newmeta2">
		<thead>
		<tr>
			<th class="left"><?php _e('A payment method name','usces') ?></th>
			<th><?php _e('explanation','usces') ?></th>
			<th><?php _e('Type of payment','usces') ?></th>
			<th><?php _e('Payment module','usces') ?></th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td class='paymentname'><input type="text" id="newname" name="newname" class="metaboxfield" tabindex="7" value="" /></td>
			<td class='paymentexplanation'><textarea id="newexplanation" name="newexplanation" class='metaboxfield'></textarea></td>
			<td class='paymentsettlement'>
				<select name="newsettlement" id="newsettlement" class='metaboxfield'>
			<?php foreach ($usces->payment_structure as $psk => $psv) { ?>
					<option value="<?php echo esc_attr($psk); ?>"><?php echo esc_html($psv); ?></option>
			<?php } ?>
				</select>
			</td>
			<td class='paymentmodule'><input type="text" id="newmodule" name="newmodule" class="metaboxfield" tabindex="9" value="" /></td>
		</tr>
		
		<tr>
			<td colspan="4" class="submittd">
			<div id='newpaymentsubmit' class='submit'><input name="add_payment" type="button" id="add_payment" tabindex="9" value="<?php _e('Add a new method forpayment ','usces') ?>" onclick="payment.post('add', 0);" /></div>
			<div id="newpayment_loading" class="meta_submit_loading"></div>
			</td>
		</tr>
		</tbody>
	</table>
	<?php 
}


function payment_ajax()
{
	global $usces;

	if( $_POST['action'] != 'payment_ajax' ) die(0);
	
	if(isset($_POST['update'])){
		$id = up_payment_method();
		
	}else if(isset($_POST['delete'])){
		$id = del_payment_method();
		
	}else if(isset($_POST['sort'])){
		$res = sort_payment_method();
		
	}else{
		$id = add_payment_method();
		
	}

	$option_value = usces_get_system_option('usces_payment_method', 'sort');
	$r = '';
	foreach ( $option_value as $value )
		$r .= _payment_list_row( $value );
		
	$res = $r . '#usces#' . $id;
	
	die( $res );
} 
/**
 * add_payment
 */
function add_payment_method() {

	$newvalue['name'] = isset($_POST['newname']) ? trim( $_POST['newname'] ) : '';
	$newvalue['explanation'] = isset($_POST['newexplanation']) ? trim( $_POST['newexplanation'] ) : '';
	$newvalue['settlement'] = isset($_POST['newsettlement']) ? $_POST['newsettlement'] : '';
	$newvalue['module'] = isset($_POST['newmodule']) ? trim( $_POST['newmodule'] ) : '';
	
	if ( !empty( $newvalue['name'] ) ) {
		$lid = usces_add_system_option( 'usces_payment_method', $newvalue );
		
		return $lid;
	}
	return false;
} // add_meta
/**
 * update_payment
 */

function up_payment_method() {
	global $usces;
	
	$value = array();
	//$id = isset($_POST['id']) ? (int)$_POST['id'] : '';
	$id = $_POST['id'];
	$value['name'] = isset($_POST['name']) ? trim( $_POST['name'] ) : '';
	$value['explanation'] = isset($_POST['explanation']) ? trim( $_POST['explanation'] ) : '';
	$value['settlement'] = isset($_POST['settlement']) ? $_POST['settlement'] : '';
	$value['module'] = isset($_POST['module']) ? trim( $_POST['module'] ) : '';
	$value['sort'] = isset($_POST['sort']) ?(int)$_POST['sort'] : 0;

	if ( !empty( $value['name'] ) && $id != '') {

		$id = usces_update_system_option( 'usces_payment_method', $id, $value );
		
		return $id;
	}
	return -2;
} // add_meta

/**
 * delete_payment
 */
function del_payment_method() {
	global $usces;
	
	$id = $_POST['id'];

	if ( $id != '' ) {

		usces_del_system_option( 'usces_payment_method', $id );
		
		return $id;
	}
	return false;
} 

function sort_payment_method(){

	return usces_sort_system_option( 'usces_payment_method', $_POST['idstr'] );
}

function add_delivery_method() {
	$options = get_option('usces');
	$name = trim($_POST['name']);
	foreach((array)$options['delivery_method'] as $deli){
		$ids[] = (int)$deli['id'];
	}
	if(isset($ids)){
		rsort($ids);
		$newid = $ids[0]+1;
	}else{
		$newid = 0;
	}
	$index = isset($options['delivery_method']) ? count($options['delivery_method']) : 0;
	$options['delivery_method'][$index]['id'] = $newid;
	$options['delivery_method'][$index]['name'] = $name;
	$options['delivery_method'][$index]['time'] = str_replace("\r\n", "\n", $_POST['time']);
	$options['delivery_method'][$index]['time'] = str_replace("\r", "\n", $options['delivery_method'][$index]['time']);
	$options['delivery_method'][$index]['charge'] = (int)$_POST['charge'];
//20101208ysk start
	$options['delivery_method'][$index]['days'] = (int)$_POST['days'];
//20101208ysk end
//20101119ysk start
	$options['delivery_method'][$index]['nocod'] = $_POST['nocod'];
//20101119ysk end
//20110317ysk start
	$options['delivery_method'][$index]['intl'] = $_POST['intl'];
//20110317ysk end
	update_option('usces', $options);
	
//20101208ysk start
//20101119ysk start
	//$res = $newid . '#usces#' . $name . '#usces#' . $options['delivery_method'][$index]['time'] . '#usces#' . $options['delivery_method'][$index]['charge'];
	//$res = $newid . '#usces#' . $name . '#usces#' . $options['delivery_method'][$index]['time'] . '#usces#' . $options['delivery_method'][$index]['charge'] . '#usces#' . $options['delivery_method'][$index]['nocod'];
//20101119ysk end
//20110317ysk start
	//$res = $newid . '#usces#' . $name . '#usces#' . $options['delivery_method'][$index]['time'] . '#usces#' . $options['delivery_method'][$index]['charge'] . '#usces#' . $options['delivery_method'][$index]['days'] . '#usces#' . $options['delivery_method'][$index]['nocod'];
	$res = $newid . '#usces#' . $name . '#usces#' . $options['delivery_method'][$index]['time'] . '#usces#' . $options['delivery_method'][$index]['charge'] . '#usces#' . $options['delivery_method'][$index]['days'] . '#usces#' . $options['delivery_method'][$index]['nocod'] . '#usces#' . $options['delivery_method'][$index]['intl'];
//20110317ysk end
//20101208ysk end
	return $res;
}

function update_delivery_method() {
	$options = get_option('usces');
	$name = trim($_POST['name']);
	$id = (int)$_POST['id'];
	$charge = (int)$_POST['charge'];
	for($i=0; $i<count($options['delivery_method']); $i++){
		if($options['delivery_method'][$i]['id'] === $id){
			$index = $i;
		}
	}
	$options['delivery_method'][$index]['name'] = $name;
	$options['delivery_method'][$index]['charge'] = $charge;
	$options['delivery_method'][$index]['time'] = str_replace("\r\n", "\n", $_POST['time']);
	$options['delivery_method'][$index]['time'] = str_replace("\r", "\n", $options['delivery_method'][$index]['time']);
//20101208ysk start
	$options['delivery_method'][$index]['days'] = (int)$_POST['days'];
//20101208ysk end
//20101119ysk start
	$options['delivery_method'][$index]['nocod'] = $_POST['nocod'];
//20101119ysk end
//20110317ysk start
	$options['delivery_method'][$index]['intl'] = $_POST['intl'];
//20110317ysk end
	update_option('usces', $options);
	
//20101208ysk start
//20101119ysk start
	//$res = $id . '#usces#' . $name . '#usces#' . $options['delivery_method'][$index]['time'] . '#usces#' . $options['delivery_method'][$index]['charge'];
	//$res = $id . '#usces#' . $name . '#usces#' . $options['delivery_method'][$index]['time'] . '#usces#' . $options['delivery_method'][$index]['charge'] . '#usces#' . $options['delivery_method'][$index]['nocod'];
//20101119ysk end
//20110317ysk start
	//$res = $id . '#usces#' . $name . '#usces#' . $options['delivery_method'][$index]['time'] . '#usces#' . $options['delivery_method'][$index]['charge'] . '#usces#' . $options['delivery_method'][$index]['days'] . '#usces#' . $options['delivery_method'][$index]['nocod'];
	$res = $id . '#usces#' . $name . '#usces#' . $options['delivery_method'][$index]['time'] . '#usces#' . $options['delivery_method'][$index]['charge'] . '#usces#' . $options['delivery_method'][$index]['days'] . '#usces#' . $options['delivery_method'][$index]['nocod'] . '#usces#' . $options['delivery_method'][$index]['intl'];
//20110317ysk end
//20101208ysk end
	return $res;
}

function delete_delivery_method() {
	$options = get_option('usces');
	$id = (int)$_POST['id'];
	for($i=0; $i<count($options['delivery_method']); $i++){
		if($options['delivery_method'][$i]['id'] === $id){
			$index = $i;
		}
	}
	array_splice($options['delivery_method'], $index, 1);
	update_option('usces', $options);
	
	$res = $id . '#usces#0';
	return $res;
}

function moveup_delivery_method() {
	$options = get_option('usces');
	$selected_id = (int)$_POST['id'];
	$ct = count($options['delivery_method']);
	for($i=0; $i<$ct; $i++){
		if($options['delivery_method'][$i]['id'] === $selected_id){
			$index = $i;
		}
	}
	if($index !== 0) {
		$from_index = $index;
		$to_index = $index - 1;
		$from_dm = $options['delivery_method'][$from_index];
		$to_dm = $options['delivery_method'][$to_index];
		for($i=0; $i<$ct; $i++){
			if($i === $to_index){
				$options['delivery_method'][$i] = $from_dm;
			}else if($i === $from_index){
				$options['delivery_method'][$i] = $to_dm;
			}
		}
		update_option('usces', $options);
	}
	
	$id = '';
	$name = '';
	$charge = '';
	$time = '';
//20101208ysk start
	$days = '';
//20101208ysk end
//20101119ysk start
	$nocod = '';
//20101119ysk end
//20110317ysk start
	$intl = '';
//20110317ysk end
	for($i=0; $i<$ct; $i++){
		$id .= $options['delivery_method'][$i]['id'] . ',';
		$name .= $options['delivery_method'][$i]['name'] . ',';
		$charge .= $options['delivery_method'][$i]['charge'] . ',';
		$time .= $options['delivery_method'][$i]['time'] . ',';
//20101208ysk start
		$days .= $options['delivery_method'][$i]['days'] . ',';
//20101208ysk end
//20101119ysk start
		$nocod .= $options['delivery_method'][$i]['nocod'] . ',';
//20101119ysk end
//20110317ysk start
		$intl .= $options['delivery_method'][$i]['intl'] . ',';
//20110317ysk end
	}
	$id = rtrim($id,',');
	$name = rtrim($name,',');
	$charge = rtrim($charge,',');
	$time = rtrim($time,',');
//20101208ysk start
	$days = rtrim($days,',');
//20101208ysk end
//20101119ysk start
	$nocod = rtrim($nocod,',');
//20101119ysk end
//20110317ysk start
	$intl = rtrim($intl,',');
//20110317ysk end
	
//20101208ysk start
//20101119ysk start
	//$res = $id . '#usces#' . $name . '#usces#' . $time . '#usces#' . $charge . '#usces#' . $selected_id;
	//$res = $id . '#usces#' . $name . '#usces#' . $time . '#usces#' . $charge . '#usces#' . $nocod . '#usces#' . $selected_id;
//20101119ysk end
//20110317ysk start
	//$res = $id . '#usces#' . $name . '#usces#' . $time . '#usces#' . $charge . '#usces#' . $days . '#usces#' . $nocod . '#usces#' . $selected_id;
	$res = $id . '#usces#' . $name . '#usces#' . $time . '#usces#' . $charge . '#usces#' . $days . '#usces#' . $nocod . '#usces#' . $intl . '#usces#' . $selected_id;
//20110317ysk end
//20101208ysk end
	return $res;
}

function movedown_delivery_method() {
	$options = get_option('usces');
	$selected_id = (int)$_POST['id'];
	$ct = count($options['delivery_method']);
	for($i=0; $i<$ct; $i++){
		if($options['delivery_method'][$i]['id'] === $selected_id){
			$index = $i;
		}
	}
	if($index < $ct-1) {
		$from_index = $index;
		$to_index = $index + 1;
		$from_dm = $options['delivery_method'][$from_index];
		$to_dm = $options['delivery_method'][$to_index];
		for($i=0; $i<$ct; $i++){
			if($i === $to_index){
				$options['delivery_method'][$i] = $from_dm;
			}else if($i === $from_index){
				$options['delivery_method'][$i] = $to_dm;
			}
		}
		update_option('usces', $options);
	}
	
	$id = '';
	$name = '';
	$charge = '';
	$time = '';
//20101208ysk start
	$days = '';
//20101208ysk end
//20101119ysk start
	$nocod = '';
//20101119ysk end
//20110317ysk start
	$intl = '';
//20110317ysk end
	for($i=0; $i<$ct; $i++){
		$id .= $options['delivery_method'][$i]['id'] . ',';
		$name .= $options['delivery_method'][$i]['name'] . ',';
		$charge .= $options['delivery_method'][$i]['charge'] . ',';
		$time .= $options['delivery_method'][$i]['time'] . ',';
//20101208ysk start
		$days .= $options['delivery_method'][$i]['days'] . ',';
//20101208ysk end
//20101119ysk start
		$nocod .= $options['delivery_method'][$i]['nocod'] . ',';
//20101119ysk end
//20110317ysk start
		$intl .= $options['delivery_method'][$i]['intl'] . ',';
//20110317ysk end
	}
	$id = rtrim($id,',');
	$name = rtrim($name,',');
	$charge = rtrim($charge,',');
	$time = rtrim($time,',');
//20101208ysk start
	$days = rtrim($days,',');
//20101208ysk end
//20101119ysk start
	$nocod = rtrim($nocod,',');
//20101119ysk end
//20110317ysk start
	$intl = rtrim($intl,',');
//20110317ysk end
	
//20101208ysk start
//20101119ysk start
	//$res = $id . '#usces#' . $name . '#usces#' . $time . '#usces#' . $charge . '#usces#' . $selected_id;
	//$res = $id . '#usces#' . $name . '#usces#' . $time . '#usces#' . $charge . '#usces#' . $nocod . '#usces#' . $selected_id;
//20101119ysk end
//20110317ysk start
	//$res = $id . '#usces#' . $name . '#usces#' . $time . '#usces#' . $charge . '#usces#' . $days . '#usces#' . $nocod . '#usces#' . $selected_id;
	$res = $id . '#usces#' . $name . '#usces#' . $time . '#usces#' . $charge . '#usces#' . $days . '#usces#' . $nocod . '#usces#' . $intl . '#usces#' . $selected_id;
//20110317ysk end
//20101208ysk end
	return $res;
}

function add_shipping_charge() {
	global $usces;

	$options = get_option('usces');
	$name = trim($_POST['name']);
//20110317ysk start
	$country = trim($_POST['country']);
//20110317ysk end
	$value = $_POST['value'];
	foreach((array)$options['shipping_charge'] as $charge){
		$ids[] = (int)$charge['id'];
	}
	if(isset($ids)){
		rsort($ids);
		$newid = $ids[0]+1;
	}else{
		$newid = 0;
	}
	$index = isset($options['shipping_charge']) ? count($options['shipping_charge']) : 0;
//	$prefs = get_option('usces_pref');
//20110317ysk start
	//$prefs = $usces->options['province'];
//20110331ysk start
	//$prefs = $usces_states[$country];
	$prefs = get_usces_states($country);
//20110331ysk end
//20110317ysk end
	array_shift($prefs);

	$options['shipping_charge'][$index]['id'] = $newid;
	$options['shipping_charge'][$index]['name'] = $name;
//20110317ysk start
	$options['shipping_charge'][$index]['country'] = $country;
//20110317ysk end
	$options['shipping_charge'][$index]["value"] = array();
	for($i=0; $i<count($prefs); $i++){
		$pref = $prefs[$i];
		$options['shipping_charge'][$index]['value'][$pref] = (int)$value[$i];
	}
	update_option('usces', $options);

	$valuestr = implode(',', $options['shipping_charge'][$index]['value']);
//20110317ysk start
	//$res = $newid . '#usces#' . $name . '#usces#' . $valuestr;
	$res = $newid . '#usces#' . $name . '#usces#' . $country . '#usces#' . $valuestr;
//20110317ysk end
	return $res;
}

function update_shipping_charge() {
	global $usces;

	$options = get_option('usces');
	$name = trim($_POST['name']);
//20110317ysk start
	$country = trim($_POST['country']);
//20110317ysk end
	$value = $_POST['value'];
	$id = (int)$_POST['id'];
//	$prefs = get_option('usces_pref');
//20110317ysk start
	//$prefs = $usces->options['province'];
//20110331ysk start
	//$prefs = $usces_states[$country];
	$prefs = get_usces_states($country);
//20110331ysk end
//20110317ysk end
	array_shift($prefs);

	for($i=0; $i<count($options['shipping_charge']); $i++){
		if($options['shipping_charge'][$i]['id'] === $id){
			$index = $i;
		}
	}
	$options['shipping_charge'][$index]["name"] = $name;
//20110317ysk start
	$options['shipping_charge'][$index]['country'] = $country;
//20110317ysk end
	$options['shipping_charge'][$index]["value"] = array();
	for($i=0; $i<count($prefs); $i++){
		$pref = $prefs[$i];
		$options['shipping_charge'][$index]["value"][$pref] = (int)$value[$i];
	}
	update_option('usces', $options);

	$valuestr = implode(',', $options['shipping_charge'][$index]["value"]);
//20110317ysk start
	//$res = $id . '#usces#' . $name . '#usces#' . $valuestr;
	$res = $id . '#usces#' . $name . '#usces#' . $country . '#usces#' . $valuestr;
//20110317ysk end
	return $res;
}

function delete_shipping_charge() {
	$options = get_option('usces');
	$id = (int)$_POST['id'];
	for($i=0; $i<count($options['shipping_charge']); $i++){
		if($options['shipping_charge'][$i]['id'] === $id){
			$index = $i;
		}
	}
	array_splice($options['shipping_charge'], $index, 1);
	update_option('usces', $options);
	
	$res = $id . '#usces#0';
	return $res;
}
//20101208ysk start
function add_delivery_days() {
	global $usces;

	$options = get_option('usces');
	$name = trim($_POST['name']);
//20110317ysk start
	$country = trim($_POST['country']);
//20110317ysk end
	$value = $_POST['value'];
	foreach((array)$options['delivery_days'] as $charge){
		$ids[] = (int)$charge['id'];
	}
	if(isset($ids)){
		rsort($ids);
		$newid = $ids[0]+1;
	}else{
		$newid = 0;
	}
	$index = isset($options['delivery_days']) ? count($options['delivery_days']) : 0;
//20110317ysk start
	//$prefs = $usces->options['province'];
//20110331ysk start
	//$prefs = $usces_states[$country];
	$prefs = get_usces_states($country);
//20110331ysk end
//20110317ysk end
	array_shift($prefs);

	$options['delivery_days'][$index]['id'] = $newid;
	$options['delivery_days'][$index]['name'] = $name;
//20110317ysk start
	$options['delivery_days'][$index]['country'] = $country;
//20110317ysk end
	for($i=0; $i<count($prefs); $i++){
		$pref = $prefs[$i];
		$options['delivery_days'][$index]['value'][$pref] = (int)$value[$i];
	}
	update_option('usces', $options);

	$valuestr = implode(',', $options['delivery_days'][$index]['value']);
//20110317ysk start
	//$res = $newid . '#usces#' . $name . '#usces#' . $valuestr;
	$res = $newid . '#usces#' . $name . '#usces#' . $country . '#usces#' . $valuestr;
//20110317ysk end
	return $res;
}

function update_delivery_days() {
	global $usces;

	$options = get_option('usces');
	$name = trim($_POST['name']);
//20110317ysk start
	$country = trim($_POST['country']);
//20110317ysk end
	$value = $_POST['value'];
	$id = (int)$_POST['id'];
//20110317ysk start
	//$prefs = $usces->options['province'];
//20110331ysk start
	//$prefs = $usces_states[$country];
	$prefs = get_usces_states($country);
//20110331ysk end
//20110317ysk end
	array_shift($prefs);

	for($i=0; $i<count($options['delivery_days']); $i++){
		if($options['delivery_days'][$i]['id'] === $id){
			$index = $i;
		}
	}
	$options['delivery_days'][$index]['name'] = $name;
//20110317ysk start
	$options['delivery_days'][$index]['country'] = $country;
//20110317ysk end
	for($i=0; $i<count($prefs); $i++){
		$pref = $prefs[$i];
		$options['delivery_days'][$index]['value'][$pref] = (int)$value[$i];
	}
	update_option('usces', $options);

	$valuestr = implode(',', $options['delivery_days'][$index]['value']);
//20110317ysk start
	//$res = $id . '#usces#' . $name . '#usces#' . $valuestr;
	$res = $id . '#usces#' . $name . '#usces#' . $country . '#usces#' . $valuestr;
//20110317ysk end
	return $res;
}

function delete_delivery_days() {
	$options = get_option('usces');
	$id = (int)$_POST['id'];
	for($i=0; $i<count($options['delivery_days']); $i++){
		if($options['delivery_days'][$i]['id'] === $id){
			$index = $i;
		}
	}
	array_splice($options['delivery_days'], $index, 1);
	update_option('usces', $options);
	
	$res = $id . '#usces#0';
	return $res;
}

/************************************************************************************************/
function shop_options_ajax()
{
	global $usces;

	if( $_POST['action'] != 'shop_options_ajax' ) die(0);
	
	switch ($_POST['mode']) {
		case 'add_delivery_method':
			$res = add_delivery_method();
			break;
		case 'update_delivery_method':
			$res = update_delivery_method();
			break;
		case 'delete_delivery_method':
			$res = delete_delivery_method();
			break;
		case 'moveup_delivery_method':
			$res = moveup_delivery_method();
			break;
		case 'movedown_delivery_method':
			$res = movedown_delivery_method();
			break;
		case 'add_shipping_charge':
			$res = add_shipping_charge();
			break;
		case 'update_shipping_charge':
			$res = update_shipping_charge();
			break;
		case 'delete_shipping_charge':
			$res = delete_shipping_charge();
			break;
//20101208ysk start
		case 'add_delivery_days':
			$res = add_delivery_days();
			break;
		case 'update_delivery_days':
			$res = update_delivery_days();
			break;
		case 'delete_delivery_days':
			$res = delete_delivery_days();
			break;
//20101208ysk end
	}
	
	die( $res );
} 

?>