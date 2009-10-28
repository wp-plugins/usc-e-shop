<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';

$management_status = get_option('usces_management_status');


$oa = 'editpost';

$ID = $_REQUEST['member_id'];

global $wpdb;

$tableName = $wpdb->prefix . "usces_member";
$query = $wpdb->prepare("SELECT * FROM $tableName WHERE ID = %d", $ID);
$data = $wpdb->get_row( $query, ARRAY_A );

$usces_member_history = $this->get_member_history($ID);

//$deli = unserialize($data['order_delivery']);
//$cart = unserialize($data['order_cart']);
//$condition = unserialize($data['order_condition']);
//$ordercheck = unserialize($data['order_check']);
//if( !is_array($ordercheck) ) $ordercheck = array();
//
//if($this->is_status('duringorder', $data['order_status']))
//	$taio = 'duringorder';
//else if($this->is_status('cancel', $data['order_status']))
//	$taio = 'cancel';
//else if($this->is_status('completion', $data['order_status']))
//	$taio = 'completion';
//else
//	$taio = 'new';
//	
//if($this->is_status('estimate', $data['order_status']))
//	$admin = 'estimate';
//else if($this->is_status('adminorder', $data['order_status']))
//	$admin = 'adminorder';
//else
//	$admin = '';
//
//if($this->is_status('noreceipt', $data['order_status']))
//	$receipt = 'noreceipt';
//else if($this->is_status('receipted', $data['order_status']))
//	$receipt = 'receipted';
//else
//	$receipt = '';

?>
<script type='text/javascript' src='<?php echo USCES_WP_PLUGIN_URL . '/usc-e-shop/js/jquery/jquery-1.3.2.min.js'; ?>'></script>
<script type='text/javascript' src='<?php echo USCES_WP_PLUGIN_URL . '/usc-e-shop/js/jquery/jquery-ui-1.7.1.custom.min.js'; ?>'></script>
<script type='text/javascript' src='<?php echo USCES_WP_PLUGIN_URL . '/usc-e-shop/js/jquery/bgiframe/jquery.bgiframe.min.js'; ?>'></script>
<script type="text/javascript">
jQuery(function($){
<?php if($status == 'success'){ ?>
			$("#anibox").animate({ backgroundColor: "#ECFFFF" }, 2000);
<?php }else if($status == 'caution'){ ?>
			$("#anibox").animate({ backgroundColor: "#FFF5CE" }, 2000);
<?php }else if($status == 'error'){ ?>
			$("#anibox").animate({ backgroundColor: "#FFE6E6" }, 2000);
<?php } ?>

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


jQuery(document).ready(function($){

});
</script>
<div class="wrap">
<div class="usces_admin">
<form action="<?php echo USCES_ADMIN_URL.'?page=usces_memberlist&member_action='.$oa; ?>" method="post" name="editpost">

<h2>Welcart Management 会員データ編集<?php //echo __('USC e-Shop Options','usces'); ?></h2>
<p class="version_info">Version <?php echo USCES_VERSION; ?></p>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>
<div class="ordernavi"><input name="upButton" class="upButton" type="submit" value="<?php echo '更新確定'; ?>" />値を変更した場合は必ず最後に「更新確定」ボタンを押してください。</div>
<div class="info_head">
<div class="error_message"><?php echo $this->error_message; ?></div>
<table>
<tr>
<td class="label">会員No</td><td class="col1"><div class="rod large short"><?php echo $data['ID']; ?></div></td>
<td class="col3 label">e-mail</td><td class="col2"><input name="mem_email" type="text" class="text long" value="<?php echo $data['mem_email']; ?>" /></td>
<td class="col3 label">郵便番号</td><td class="col2"><input name="mem_zip" type="text" class="text short" value="<?php echo $data['mem_zip']; ?>" /></td>
</tr>
<tr>
<td class="label">ランク</td><td class="col1"><select name="mem_status">
<?php 
	foreach ((array)$this->member_status as $rk => $rv) {
		$selected = ($rk == $data['mem_status']) ? ' selected="selected"' : '';
?>
    <option value="<?php echo $rk; ?>"<?php echo $selected; ?>><?php echo $rv; ?></option>
<?php } ?>
</select></td>
<td class="col3 label">氏名</td><td class="col2"><input name="mem_name1" type="text" class="text short" value="<?php echo $data['mem_name1']; ?>" /><input name="mem_name2" type="text" class="text short" value="<?php echo $data['mem_name2']; ?>" /></td>
<td class="col3 label">都道府県</td><td class="col2"><select name="mem_pref" class="select">
<?php
//	$prefs = get_option('usces_pref');
	$prefs = $this->options['province'];
foreach((array)$prefs as $value) {
	$selected = ($data['mem_pref'] == $value) ? ' selected="selected"' : '';
	echo "\t<option value='{$value}'{$selected}>{$value}</option>\n";
}
?>
</select></td></tr>
<tr>
<td class="label">保有PT</td><td class="col1"><input name="mem_point" type="text" class="text right short" value="<?php echo $data['mem_point']; ?>" /></td>
<td class="col3 label">フリガナ</td><td class="col2"><input name="mem_name3" type="text" class="text short" value="<?php echo $data['mem_name3']; ?>" /><input name="mem_name4" type="text" class="text short" value="<?php echo $data['mem_name4']; ?>" /></td>
<td class="col3 label">市区郡町村</td><td class="col2"><input name="mem_address1" type="text" class="text long" value="<?php echo $data['mem_address1']; ?>" /></td>
</tr>
<tr>
<td class="label">入会日</td><td class="col1"><div class="rod shortm"><?php echo substr($data['mem_registered'],0,4).'年'.substr($data['mem_registered'],5,2).'月'.substr($data['mem_registered'],8,2).'日'; ?></div></td>
<td class="col3 label">電話番号</td><td class="col2"><input name="mem_tel" type="text" class="text long" value="<?php echo $data['mem_tel']; ?>" /></td>
<td class="col3 label">番地</td><td class="col2"><input name="mem_address2" type="text" class="text long" value="<?php echo $data['mem_address2']; ?>" /></td>
</tr>
<tr>
<td colspan="2">&nbsp;</td>
<td class="col3 label">FAX番号</td><td class="col2"><input name="mem_fax" type="text" class="text long" value="<?php echo $data['mem_fax']; ?>" /></td>
<td class="col3 label">ビル名</td><td class="col2"><input name="mem_address3" type="text" class="text long" value="<?php echo $data['mem_address3']; ?>" /></td>
</tr>
</table>
</div>
<div id="member_history">
<table>
<?php if ( !count($usces_member_history) ) : ?>
<tr>
<td>現在購入履歴はございません。</td>
</tr>
<?php endif; ?>
<?php foreach ( (array)$usces_member_history as $umhs ) :	$cart = $umhs['cart']; ?>
<tr>
<th class="historyrow">購入日</th>
<th class="historyrow">購入金額</th>
<th class="historyrow">使用ポイント</th>
<th class="historyrow">特別割引</th>
<th class="historyrow">送料</th>
<th class="historyrow">代引き手数料</th>
<th class="historyrow">消費税</th>
<th class="historyrow">獲得ポイント</th>
</tr>
<tr>
<td><?php echo $umhs['date']; ?></td>
<td class="rightnum"><?php echo number_format($this->get_total_price($cart)-$umhs['usedpoint']+$umhs['discount']+$umhs['shipping_charge']+$umhs['cod_fee']+$umhs['tax']); ?></td>
<td class="rightnum"><?php echo number_format($umhs['usedpoint']); ?></td>
<td class="rightnum"><?php echo number_format($umhs['discount']); ?></td>
<td class="rightnum"><?php echo number_format($umhs['shipping_charge']); ?></td>
<td class="rightnum"><?php echo number_format($umhs['cod_fee']); ?></td>
<td class="rightnum"><?php echo number_format($umhs['tax']); ?></td>
<td class="rightnum"><?php echo number_format($umhs['getpoint']); ?></td>
</tr>
<tr>
<td class="retail" colspan="8">
	<table id="retail_table">
	<tr>
	<th scope="row" class="num"><?php echo __('No.','usces'); ?></th>
	<th class="thumbnail">&nbsp;</th>
	<th>商品</th>
	<th class="price ">単価</th>
	<th class="quantity">数量</th>
	<th class="subtotal">金額</th>
	</tr>
	<?php
	for($i=0; $i<count($cart); $i++) { 
	$cart_row = $cart[$i];
	$post_id = $cart_row['post_id'];
	$sku = $cart_row['sku'];
	$quantity = $cart_row['quantity'];
	$options = $cart_row['options'];
	$itemCode = $this->getItemCode($post_id);
	$itemName = $this->getItemName($post_id);
	$skuPrice = $this->getItemPrice($post_id, $sku);
	$pictids = $this->get_pictids($itemCode);
	if (!empty($options)) {
	$optstr = implode(',', $options);
	} else { 
	$optstr =  '';
	$options =  array();
	}
	
	?>
	<tr>
	<td><?php echo $i + 1; ?></td>
	<td><?php echo wp_get_attachment_image( $pictids[0], array(60, 60), true ); ?></td>
	<td class="aleft"><?php echo $itemName; ?>&nbsp;<?php echo $itemCode; ?>&nbsp;<?php echo $sku; ?><br /><?php echo $optstr; ?></td>
	<td class="rightnum"><?php echo number_format($skuPrice); ?></td>
	<td class="rightnum"><?php echo number_format($cart_row['quantity']); ?></td>
	<td class="rightnum"><?php echo number_format($skuPrice * $cart_row['quantity']); ?></td>
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

</form>

</div><!--usces_admin-->
</div><!--wrap-->
