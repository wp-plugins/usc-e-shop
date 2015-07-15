<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';

//$management_status = get_option('usces_management_status');

if($member_action == 'new'){
	$page = 'usces_membernew';
	$oa = 'newpost';
	$ID = NULL;
	$member_metas = array();
	$data = array(
			'ID' =>'',
			'mem_email' => ( isset($_POST['member']['email']) ) ? $_POST['member']['email'] : '',
			'mem_pass' => ( isset($_POST['member']['password']) ) ? $_POST['member']['password'] : '',
			'mem_status' => ( isset($_POST['member']['status']) ) ? $_POST['member']['status'] : 0,
			'mem_cookie' => '',
			'mem_point' => ( isset($_POST['member']['point']) ) ? $_POST['member']['point'] : 0,
			'mem_name1' => ( isset($_POST['member']['name1']) ) ? $_POST['member']['name1'] : '',
			'mem_name2' => ( isset($_POST['member']['name2']) ) ? $_POST['member']['name2'] : '',
			'mem_name3' => ( isset($_POST['member']['name3']) ) ? $_POST['member']['name3'] : '',
			'mem_name4' => ( isset($_POST['member']['name4']) ) ? $_POST['member']['name4'] : '',
			'mem_zip' => ( isset($_POST['member']['zipcode']) ) ? $_POST['member']['zipcode'] : '',
			'mem_pref' => ( isset($_POST['member']['pref']) ) ? $_POST['member']['pref'] : '',
			'mem_address1' => ( isset($_POST['member']['address1']) ) ? $_POST['member']['address1'] : '',
			'mem_address2' => ( isset($_POST['member']['address2']) ) ? $_POST['member']['address2'] : '',
			'mem_address3' => ( isset($_POST['member']['address3']) ) ? $_POST['member']['address3'] : '',
			'mem_tel' => ( isset($_POST['member']['tel']) ) ? $_POST['member']['tel'] : '',
			'mem_fax' => ( isset($_POST['member']['fax']) ) ? $_POST['member']['fax'] : '',
			'mem_delivery_flag' => '',
			'mem_delivery' => '',
			'mem_registered' => '',
			'mem_nicename' => ''
			);

	$usces_member_history = array();

	$csmb_meta = usces_has_custom_field_meta('member');
	if(is_array($csmb_meta)) {
		$keys = array_keys($csmb_meta);
		foreach($keys as $key) {
			$csmb_key = 'csmb_'.$key;
			$csmb_meta[$key]['data'] = $_POST['custom_member'][$key];
		}
	}
}else{
	$page = 'usces_memberlist';
	$oa = 'editpost';
	$ID = $_REQUEST['member_id'];
	$member_metas = $this->get_member_meta($ID);
	ksort($member_metas);
	global $wpdb;

	$tableName = $wpdb->prefix . "usces_member";
	$query = $wpdb->prepare("SELECT * FROM $tableName WHERE ID = %d", $ID);
	$data = $wpdb->get_row( $query, ARRAY_A );

	$usces_member_history = $this->get_member_history($ID);
	//20100818ysk start
	$csmb_meta = usces_has_custom_field_meta('member');
	if(is_array($csmb_meta)) {
		$keys = array_keys($csmb_meta);
		foreach($keys as $key) {
			$csmb_key = 'csmb_'.$key;
			$csmb_meta[$key]['data'] = maybe_unserialize($this->get_member_meta_value($csmb_key, $ID));
		}
	}
	//20100818ysk end
}

if( usces_is_member_system() ){
	$colspan = 8;
}else{
	$colspan = 6;
}

$mem_registered = ( !empty($data['mem_registered']) ) ? sprintf(__('%2$s %3$s, %1$s', 'usces'),substr($data['mem_registered'],0,4),substr($data['mem_registered'],5,2),substr($data['mem_registered'],8,2)) : '';

$curent_url = urlencode( USCES_ADMIN_URL.'?'.$_SERVER['QUERY_STRING'] );
?>
<script type="text/javascript">
jQuery(function($){
<?php if($status == 'success'){ ?>
	$("#anibox").animate({ backgroundColor: "#ECFFFF" }, 2000);
<?php }else if($status == 'caution'){ ?>
	$("#anibox").animate({ backgroundColor: "#FFF5CE" }, 2000);
<?php }else if($status == 'error'){ ?>
	$("#anibox").animate({ backgroundColor: "#FFE6E6" }, 2000);
<?php } ?>
	var default_pre = '<?php _e('-- Select --', 'usces'); ?>';
	$(".num").bind("change", function(){ usces_check_num($(this)); });
});

function addComma(str)
{
	cnt = 0;
	n   = "";
	for (i=str.length-1; i>=0; i--)
	{
		n = str.charAt(i) + n;
		cnt++;
		if (((cnt % 3) == 0) && (i != 0)) n = ","+n;
	}
	return n;
};
</script>
<div class="wrap">
<div class="usces_admin">
<form action="<?php echo USCES_ADMIN_URL.'?page='.$page.'&member_action='.$oa; ?>" method="post" name="editpost">
<?php if( $member_action == 'new' ) : ?>
	<h2>Welcart Management <?php _e('New Membership Registration','usces'); ?></h2>
<?php else : ?>
	<h2>Welcart Management <?php _e('Edit membership data','usces'); ?></h2>
<?php endif;?>

<p class="version_info">Version <?php echo USCES_VERSION; ?></p>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img id="info_image" src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>
<div class="ordernavi"><input name="upButton" class="upButton" type="submit" value="<?php _e('change decision', 'usces'); ?>" /><?php _e("When you change amount, please click 'Edit' before you finish your process.", 'usces'); ?></div>
<div class="info_head">
<table class="mem_wrap">
<tr>
<td class="label"><?php _e('membership number', 'usces'); ?></td><td class="col1"><div class="rod large short"><?php echo esc_html($data['ID']); ?></div></td>
<td colspan="2" rowspan="5" class="mem_col2">
<table class="mem_info">
		<tr>
			<td class="label">e-mail</td>
			<td><input name="member[email]" type="text" class="text long" value="<?php echo esc_attr($data['mem_email']); ?>" /></td>
		</tr>
<?php if( $member_action == 'new' ) : ?>
		<tr>
			<td class="label"><?php _e('password', 'usces'); ?></td>
			<td><input name="member[password]" type="text" class="text" value="<?php echo esc_attr($data['mem_pass']); ?>" autocomplete="off" /></td>
		</tr>
<?php endif; ?>
<?php echo uesces_get_admin_addressform( 'member', $data, $csmb_meta ); ?>
</table>
</td>
<td colspan="2" rowspan="5" class="mem_col3">
<table class="mem_info">
<?php
	if( 0 < count($member_metas) ) :
		$cardinfo = array( 'zeus'=>array(), 'remise'=>array(), 'digitalcheck'=>array() );
		foreach( $member_metas as $value ) {
			if( in_array( $value['meta_key'], array( 'zeus_pcid', 'zeus_partofcard', 'zeus_limitofcard' ) ) ) {
				$cardinfo['zeus'][$value['meta_key']] = $value['meta_value'];
			} elseif( in_array( $value['meta_key'], array( 'remise_pcid', 'partofcard', 'limitofcard', 'remise_memid' ) ) ) {
				$cardinfo['remise'][$value['meta_key']] = $value['meta_value'];
			} elseif( in_array( $value['meta_key'], array( 'digitalcheck_ip_user_id' ) ) ) {
				$cardinfo['digitalcheck'][$value['meta_key']] = $value['meta_value'];
			}
		}
		if( 0 < count($cardinfo['zeus']) ) :
			foreach( $cardinfo['zeus'] as $key => $value ) :
				if( $key != 'zeus_pcid' ) :
					if( $key == 'zeus_partofcard' ) $label = __('下4桁','usces');
					elseif( $key == 'zeus_limitofcard' ) $label = __('有効期限','usces');
					else $label = $key; ?>
		<tr>
			<td class="label"><?php echo esc_html($label); ?></td>
			<td><div class="rod_left shortm"><?php echo esc_html($value); ?></div></td>
		</tr>
<?php			endif;
			endforeach;
			if( array_key_exists( 'zeus_pcid', $cardinfo['zeus'] ) ) : ?>
		<tr>
			<td class="label">クイックチャージ</td>
			<td><div class="rod_left shortm"><?php _e('登録あり','usces'); ?></div></td>
		</tr>
<?php			if( defined('WCEX_AUTO_DELIVERY') and !wcad_have_member_regular_order( $ID ) ) : ?>
		<tr>
			<td class="label"><input type="checkbox" name="zeus_pcid" value="delete"></td>
			<td>クイックチャージを解除する</td>
		</tr>
<?php			endif;
			endif;
		endif;
		if( 0 < count($cardinfo['remise']) ) :
			foreach( $cardinfo['remise'] as $key => $value ) :
				if( $key != 'remise_pcid' ) :
					if( $key == 'partofcard' ) $label = __('下4桁','usces');
					elseif( $key == 'limitofcard' ) $label = __('有効期限','usces');
					elseif( $key == 'remise_memid' ) $label = __('メンバーID','usces');
					else $label = $key; ?>
		<tr>
			<td class="label"><?php echo esc_html($label); ?></td>
			<td><div class="rod_left shortm"><?php echo esc_html($value); ?></div></td>
		</tr>
<?php			endif;
			endforeach;
			if( array_key_exists( 'remise_pcid', $cardinfo['remise'] ) ) : ?>
		<tr>
			<td class="label">ペイクイック</td>
			<td><div class="rod_left shortm"><?php _e('登録あり','usces'); ?></div></td>
		</tr>
		<tr>
			<td class="label"><input type="checkbox" name="remise_pcid" value="delete"></td>
			<td>ペイクイックを解除する</td>
		</tr>
<?php		endif;
		endif;
		if( 0 < count($cardinfo['digitalcheck']) ) :
			foreach( $cardinfo['digitalcheck'] as $key => $value ) :
				if( $key != 'digitalcheck_ip_user_id' ) : ?>
		<tr>
			<td class="label"><?php echo esc_html($key); ?></td>
			<td><div class="rod_left shortm"><?php echo esc_html($value); ?></div></td>
		</tr>
<?php			endif;
			endforeach;
			if( array_key_exists( 'digitalcheck_ip_user_id', $cardinfo['digitalcheck'] ) ) : ?>
		<tr>
			<td class="label">ユーザID決済</td>
			<td><div class="rod_left shortm"><?php _e('登録あり','usces'); ?></div></td>
		</tr>
		<tr>
			<td class="label"><input type="checkbox" name="digitalcheck_ip_user_id" value="delete"></td>
			<td>ユーザID決済を解除する</td>
		</tr>
<?php		endif;
		endif;
	endif;
?>
</table>


</td>
	</tr>
<tr>
<td class="label"><?php _e('Rank', 'usces'); ?></td><td class="col1"><select name="member[status]">
<?php foreach ((array)$this->member_status as $rk => $rv) :
		$selected = ($rk == $data['mem_status']) ? ' selected="selected"' : ''; ?>
    <option value="<?php echo esc_attr($rk); ?>"<?php echo $selected; ?>><?php echo esc_html($rv); ?></option>
<?php endforeach; ?>
</select></td>
</tr>
<tr>
<td class="label"><?php _e('current point', 'usces'); ?></td><td class="col1"><input name="member[point]" type="text" class="text right short num" value="<?php echo esc_html($data['mem_point']); ?>" /></td>
</tr>
<tr>
<td class="label"><?php _e('Strated date', 'usces'); ?></td><td class="col1"><div class="rod shortm"><?php echo esc_html($mem_registered); ?></div></td>
</tr>
<tr>
<td colspan="2"><?php do_action( 'usces_action_member_edit_form_left_blank', $ID ); ?></td>
</tr>
</table>
</div>
<div id="member_history">
<table>
<?php if ( !count($usces_member_history) ) : ?>
<tr>
<td><?php _e('There is no purchase history for this moment.', 'usces'); ?></td>
</tr>
<?php endif; ?>
<?php foreach ( (array)$usces_member_history as $umhs ) :	$cart = $umhs['cart']; ?>
<?php $order_id = $umhs['ID']; ?>
<tr>
<th class="historyrow"><?php _e('Purchase date', 'usces'); ?></th>
<th class="historyrow"><?php _e('Order number', 'usces'); ?></th>
<th class="historyrow"><?php _e('Purchase price', 'usces'); ?></th>
<th class="historyrow"><?php _e('Used points','usces'); ?></th>
<th class="historyrow"><?php echo apply_filters( 'usces_member_discount_label', __('Special Price', 'usces'), $umhs['ID'] ); ?></th>
<th class="historyrow"><?php _e('Shipping', 'usces'); ?></th>
<th class="historyrow"><?php _e('C.O.D', 'usces'); ?></th>
<th class="historyrow"><?php _e('consumption tax', 'usces'); ?></th>
<th class="historyrow"><?php _e('Acquired points', 'usces'); ?></th>
</tr>
<tr>
<td><?php echo $umhs['date']; ?></td>
<td><a href="<?php echo USCES_ADMIN_URL; ?>?page=usces_orderlist&order_action=edit&order_id=<?php echo $order_id; ?>&usces_referer=<?php echo esc_url($curent_url); ?>"><?php echo usces_get_deco_order_id( $order_id ); ?></a></td>
<td class="rightnum"><?php usces_crform( $this->get_total_price($cart)-$umhs['usedpoint']+$umhs['discount']+$umhs['shipping_charge']+$umhs['cod_fee']+$umhs['tax'], true, false ); ?></td>
<td class="rightnum"><?php echo number_format($umhs['usedpoint']); ?></td>
<td class="rightnum"><?php usces_crform( $umhs['discount'], true, false ); ?></td>
<td class="rightnum"><?php usces_crform( $umhs['shipping_charge'], true, false ); ?></td>
<td class="rightnum"><?php usces_crform( $umhs['cod_fee'], true, false ); ?></td>
<td class="rightnum"><?php usces_crform( $umhs['tax'], true, false ); ?></td>
<td class="rightnum"><?php echo number_format($umhs['getpoint']); ?></td>
</tr>
<tr>
<td class="retail" colspan="9">
	<table id="retail_table">
	<tr>
	<th scope="row" class="num"><?php echo __('No.','usces'); ?></th>
	<th class="thumbnail">&nbsp;</th>
	<th><?php _e('Items','usces'); ?></th>
	<th class="price "><?php _e('Unit price','usces'); ?>(<?php usces_crcode(); ?>)</th>
	<th class="quantity"><?php _e('Quantity','usces'); ?></th>
	<th class="subtotal"><?php _e('Amount','usces'); ?>(<?php usces_crcode(); ?>)</th>
	</tr>
	<?php
	for($i=0; $i<count($cart); $i++) { 
		$cart_row = $cart[$i];
		$ordercart_id = $cart_row['cart_id'];
		$post_id = $cart_row['post_id'];
		$sku = urldecode($cart_row['sku']);
		$quantity = $cart_row['quantity'];
		$options = $cart_row['options'];
		//$advance = $this->cart->wc_serialize($cart_row['advance']);
		$advance = usces_get_ordercart_meta( 'advance', $ordercart_id );
//		$itemCode = $this->getItemCode($post_id);
//		$itemName = $this->getItemName($post_id);
		$itemCode = $cart_row['item_code'];
		$itemName = $cart_row['item_name'];
//		$cartItemName = $this->getCartItemName($post_id, $sku);
		$cartItemName = $this->getCartItemName_byOrder($cart_row);
		//$skuPrice = $this->getItemPrice($post_id, $sku);
		$skuPrice = $cart_row['price'];
		$pictid = (int)$this->get_mainpictid($itemCode);
		$optstr =  '';
		foreach((array)$options as $key => $value){
//20110629ysk start 0000190
			//if( !empty($key) )
			//	$optstr .= esc_html($key) . ' : ' . nl2br(esc_html(urldecode($value))) . "<br />\n"; 
			if( !empty($key) ) {
				$key = urldecode($key);
				$value = maybe_unserialize($value);
				if(is_array($value)) {
					$c = '';
					$optstr .= esc_html($key) . ' : '; 
					foreach($value as $v) {
						$optstr .= $c.nl2br(esc_html(urldecode($v)));
						$c = ', ';
					}
					$optstr .= "<br />\n"; 
				} else {
					$optstr .= esc_html($key) . ' : ' . nl2br(esc_html(urldecode($value))) . "<br />\n"; 
				}
			}
//20110629ysk end
		}
		$materials = compact( 'i', 'cart_row', 'post_id', 'sku', 'quantity', 'options', 'advance', 
						'itemCode', 'itemName', 'cartItemName', 'skuPrice', 'pictid', 'order_id' );
		$optstr = apply_filters( 'usces_filter_member_edit_form_row', $optstr, $cart, $materials );
	?>
	<tr>
	<td><?php echo $i + 1; ?></td>
	<td><?php echo wp_get_attachment_image( $pictid, array(60, 60), true ); ?></td>
	<td class="aleft"><?php echo esc_html($cartItemName); ?><br /><?php echo $optstr; ?></td>
	<td class="rightnum"><?php usces_crform( $skuPrice, true, false ); ?></td>
	<td class="rightnum"><?php echo number_format($cart_row['quantity']); ?></td>
	<td class="rightnum"><?php usces_crform( $skuPrice * $cart_row['quantity'], true, false ); ?></td>
	</tr>
	<?php 
	}
	?>
	</table>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
<input name="member_action" type="hidden" value="<?php echo $oa; ?>" />
<input name="member_id" id="member_id" type="hidden" value="<?php echo $data['ID']; ?>" />


<div id="mailSendAlert" title="">
	<div id="order-response"></div>
	<fieldset>
	</fieldset>
</div>
<?php wp_nonce_field( 'post_member', 'wc_nonce' ); ?>
</form>

</div><!--usces_admin-->
</div><!--wrap-->
