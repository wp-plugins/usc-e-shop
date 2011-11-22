<?php
/******************************************************/
// Joint Elements Customize
/******************************************************/

/* Admin Page Style ******************************************************/
add_action('admin_head', 'je_script');
function je_script(){
	if( 'usces_itemedit' == $_GET['page'] || 'usces_itemnew' == $_GET['page'] ){
	?>
<style type="text/css">
<!--
.sku_shortcode {
	background-color: #FFFFFF;
	padding-top: 3px;
	padding-right: 5px;
	padding-bottom: 3px;
	padding-left: 5px;
	border: 1px solid #CCCCCC;
}
.skuexp {
	line-height: 1.2em;
	color: #666666;
	letter-spacing: 1px;
	padding-top: 5px;
	padding-right: 10px;
	padding-bottom: 10px;
	padding-left: 10px;
	font-size: 12px;
	border: 1px solid #CCCCCC;
	background-color: #FFFFEE;
}
-->
</style>
	<?php
	}
}

/* init ******************************************************/
$customer_status = array(
					'0' => '無料会員',
					'1' => '有料会員',
					'2' => 'VIP会員',
					);
update_option('usces_customer_status',$customer_status);
$js_lank_ids = array(
					'free' => 0,
					'paid' => 1,
					'vip' => 2,
					);
$js_order_a = NULL;
$js_member_info = NULL;

/* Shortcode ******************************************************/
add_shortcode('member', 'je_member_shortcode');
add_shortcode('nonmember', 'je_nonmember_shortcode');
add_shortcode('cart_button', 'je_cart_button_shortcode');
//[member]～[/member]
function je_member_shortcode( $atts, $content = NULL ) {
	global $post, $usces;
	
	if ( je_is_authority() )
		return do_shortcode($content);
	else
		return '';
}
//[nonmember]～[/nonmember]
function je_nonmember_shortcode( $atts, $content = NULL ) {
	global $post, $usces;

	if ( !je_is_authority() )
 	   return do_shortcode($content);
	else
		return '';
}
//[cart_button code="" sku=""]
function je_cart_button_shortcode($atts) {
	global $usces;
	
	extract(shortcode_atts(array(
		'code' => '',
		'sku' => '',
	), $atts));

	$post_id = $usces->get_postIDbyCode( $code );
	$_skus = get_post_meta($post_id, ('_isku_'.$sku), true);
	if( empty($_skus) )
		return '該当する商品がありません。';
	
	$error_mes = $_SESSION['usces_singleitem']['error_message'][$post_id][$sku];
	$zaikonum = $_skus['zaikonum'];
	$zaiko_status = $_skus['zaiko'];
	$gptekiyo = $_skus['gptekiyo'];
	$skuPrice = $_skus['price'];
	$value = esc_attr(apply_filters( 'je_filter_incart_button_label', 'カートへ入れる'));
	$sku = esc_attr($sku);
	
	$html = "<form action=\"" . USCES_CART_URL . "\" method=\"post\">";
	$html .= "<input name=\"zaikonum[{$post_id}][{$sku}]\" type=\"hidden\" id=\"zaikonum[{$post_id}][{$sku}]\" value=\"{$zaikonum}\" />\n";
	$html .= "<input name=\"zaiko[{$post_id}][{$sku}]\" type=\"hidden\" id=\"zaiko[{$post_id}][{$sku}]\" value=\"{$zaiko_status}\" />\n";
	$html .= "<input name=\"gptekiyo[{$post_id}][{$sku}]\" type=\"hidden\" id=\"gptekiyo[{$post_id}][{$sku}]\" value=\"{$gptekiyo}\" />\n";
	$html .= "<input name=\"skuPrice[{$post_id}][{$sku}]\" type=\"hidden\" id=\"skuPrice[{$post_id}][{$sku}]\" value=\"{$skuPrice}\" />\n";
	$html .= "<a name=\"cart_button\"></a><input name=\"inCart[{$post_id}][{$sku}]\" type=\"submit\" id=\"inCart[{$post_id}][{$sku}]\" class=\"skubutton\" value=\"{$value}\" />";
	$html .= "<input name=\"usces_referer\" type=\"hidden\" value=\"" . $_SERVER['REQUEST_URI'] . "\" />\n";
	$html .= "</form>\n";
	$html .= "<div class=\"error_message\">" . $error_mes . "</div>";
	return $html;
}

/* SKU Shortcode Sample ******************************************************/
add_filter('usces_filter_sku_meta_row_advance', 'je_filter_sku_meta_row_advance', 10, 2);
function je_filter_sku_meta_row_advance($default_field, $entry){
	$itemcode = get_post_meta($entry['post_id'], '_itemCode', true);
	$skucode = substr($entry['meta_key'],6);
	$default_field = '　　カート投入ボタン・ショートコード ： <span class="sku_shortcode">[cart_button code="' . esc_html($itemcode) . '" sku="' . esc_html($skucode) . '"]</span>';
	return $default_field;
}

/* Add meta box for post ******************************************************/
add_action('admin_menu', 'je_meta_box');
add_action('save_post', 'je_save_postdata');
function je_meta_box(){
	if( 'usces_itemedit' == $_GET['page'] || 'usces_itemnew' == $_GET['page'] ){
		add_meta_box('je_meta_box','自動更新会員ランク','je_add_meta_box','post', 'normal', 'high');
	}else{
		add_meta_box('je_meta_box','ページの閲覧設定','je_add_meta_box','post', 'normal', 'high');
		add_meta_box('je_meta_box','ページの閲覧設定','je_add_meta_box','page', 'normal', 'high');
	}
}
function je_add_meta_box($post, $box){
	if( 'usces_itemedit' == $_GET['page'] || 'usces_itemnew' == $_GET['page'] ){

		$item_member_lank = get_post_meta( $post->ID, '_je_item_member_lank', true );
	?>
	<input type="hidden" name="je_nonce" id="je_nonce" value="<?php echo wp_create_nonce( plugin_basename(__FILE__) ); ?>" />
	<table>
		<tr>
		<th nowrap="nowrap">対応する会員ランク</th>
		<td>
		<select name="je_item_member_lank">
				<option value="none"<?php echo ( 'none' == $item_member_lank ? 'selected="selected"' : ''); ?>>非対応･･･会員ランクの自動更新は行なわない</option>
				<option value="paid"<?php echo ( 'paid' == $item_member_lank ? 'selected="selected"' : ''); ?>>有料会員･･･この商品を購入すると有料会員に更新される</option>
				<option value="vip"<?php echo ( 'vip' == $item_member_lank ? 'selected="selected"' : ''); ?>>VIP会員･･･この商品を購入するとVIP会員に更新される</option>
		</select>
		</td>
		</tr>
	</table>
	<?php
	
	}else{

		$permission = get_post_meta( $post->ID, '_je_permission', true );
	?>
	<input type="hidden" name="je_nonce" id="je_nonce" value="<?php echo wp_create_nonce( plugin_basename(__FILE__) ); ?>" />
	<table>
		<tr>
		<th nowrap="nowrap">許可する会員ランク</th>
		<td>
		<select name="je_permission">
				<option value="allow"<?php echo ( 'allow' == $permission ? 'selected="selected"' : ''); ?>>全て許可･･･会員による表示制限を行なわない</option>
				<option value="free"<?php echo ( 'free' == $permission ? 'selected="selected"' : ''); ?>>無料会員･･･無料・有料・VIPの3つの会員が閲覧できる</option>
				<option value="paid"<?php echo ( 'paid' == $permission ? 'selected="selected"' : ''); ?>>有料会員･･･有料およびVIP会員のみが閲覧できる</option>
				<option value="vip"<?php echo ( 'vip' == $permission ? 'selected="selected"' : ''); ?>>VIP会員･･･VIP会員のみが閲覧できる</option>
		</select>
		</td>
		<!--<td>隠蔽する記事は、ショートコード[member]～[/member]で括られた部分となります。<br />それ以外の記事は会員ランクに関係なく常に表示されます。</td>-->
		</tr>
	</table>
	<?php
	}
}
function je_save_postdata( $post_id ){
	if ( !wp_verify_nonce( $_POST['je_nonce'], plugin_basename(__FILE__) ))
		return $post_id;
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
		return $post_id;
	
	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
		return $post_id;
	} else {
		if ( !current_user_can( 'edit_post', $post_id ) )
		return $post_id;
	}
	
	if( isset($_POST['je_item_member_lank']) ){
		$metadata = $_POST['je_item_member_lank'];
		update_post_meta($post_id, '_je_item_member_lank', $metadata);
	}elseif( isset($_POST['je_permission']) ){
		$metadata = $_POST['je_permission'];
		update_post_meta($post_id, '_je_permission', $metadata);
	}
	
	return false;
}

/* TinyMCE Custom Button ******************************************************/
add_action('init', 'je_TinyMCE_addbuttons');
function je_TinyMCE_addbuttons() {
   if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
     return;
 
   // Add only in Rich Editor mode
   if ( get_user_option('rich_editing') == 'true') {
     add_filter("mce_external_plugins", "je_add_tinymce_plugin");
     add_filter('mce_buttons', 'je_register_tinymce_button');
   }
}
 
function je_register_tinymce_button($buttons) {
   array_push($buttons, "separator", "jemember", "jecart");
   return $buttons;
}
 
// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
function je_add_tinymce_plugin($plugin_array) {
   $plugin_array['JeButtons'] = USCES_PLUGIN_URL.'/js/editor_plugin.js';
   return $plugin_array;
}

function onAdminFooter(){
    if( strpos( $_SERVER[ "REQUEST_URI" ], "post.php"     ) ||
        strpos( $_SERVER[ "REQUEST_URI" ], "post-new.php" ) ||
        strpos( $_SERVER[ "REQUEST_URI" ], "page-new.php" ) ||
        strpos( $_SERVER[ "REQUEST_URI" ], "page.php"     )
	){
        echo '<script type="text/javascript" src="' . USCES_PLUGIN_URL . '/js/quicktag.js"></script>';
    }
}
 
if( is_admin() ){
    add_filter( "admin_footer", "onAdminFooter" );
}

/* Lank Auto Change ******************************************************/
add_action('usces_post_reg_orderdata', 'je_post_reg_orderdata', 10, 2);
function je_post_reg_orderdata( $order_id, $results ){
	global $wpdb, $usces, $js_lank_ids;

	if( !$order_id )
		return;

	$cart = $usces->cart->get_cart();
	$entry = $usces->cart->get_entry();
	if( empty($cart) )
		return;
	
	$usces->get_current_member();
	$member_id = $usces->current_member['id'];
	if( !$member_id )
		return;
		
	$set = $usces->getPayments( $entry['order']['payment_name'] );
	$status = ( $set['settlement'] == 'transferAdvance' || $set['settlement'] == 'transferDeferred' || $set['settlement'] == 'acting_remise_conv' || $set['settlement'] == 'acting_zeus_bank' || $set['settlement'] == 'acting_zeus_conv' || $set['settlement'] == 'acting_jpayment_conv' || $set['settlement'] == 'acting_jpayment_bank' ) ? 'noreceipt' : '';
	if( 'noreceipt' == $status )
		return;
		
	foreach ( (array)$cart as $cart_row ) {
		$post_id = $cart_row['post_id'];
		$_je_item_member_lank = get_post_meta($post_id, '_je_item_member_lank', true);
		$item_member_lanks[] = $js_lank_ids[$_je_item_member_lank];
	}
	rsort($item_member_lanks);
	
	$item_member_lank = (int)$item_member_lanks[0];
	$member_info = $usces->get_member_info( $member_id );
	$member_lank = (int)$member_info['mem_status'];
	if( $member_lank >= $item_member_lank )
		return;
		

	$member_table_name = $wpdb->prefix . "usces_member";
	$mquery = $wpdb->prepare("UPDATE $member_table_name SET mem_status = %d WHERE ID = %d", $item_member_lank, $member_id);
	$wpdb->query( $mquery );
}

add_action('usces_pre_update_orderdata', 'je_pre_update_orderdata', 10);
function je_pre_update_orderdata( $order_id ){
	global $usces, $js_order_a;
	$js_order_a = $usces->get_order_data($order_id, 'direct');
}
add_action('usces_after_update_orderdata', 'je_after_update_orderdata', 10, 2);
function je_after_update_orderdata( $order_id, $res ){
	global $usces, $js_order_a;
	
	if( !$res )
		return;
		
	$js_order_b = $usces->get_order_data($order_id, 'direct');
	$set = $usces->getPayments( $js_order_b['order_payment_name'] );
	$status = ( $set['settlement'] == 'transferAdvance' || $set['settlement'] == 'transferDeferred' || $set['settlement'] == 'acting_remise_conv' || $set['settlement'] == 'acting_zeus_bank' || $set['settlement'] == 'acting_zeus_conv' || $set['settlement'] == 'acting_jpayment_conv' || $set['settlement'] == 'acting_jpayment_bank' ) ? 'noreceipt' : '';
	$t1 = $usces->is_status('cancel', $js_order_a['order_status']) ? false : true;
	$t2 = $usces->is_status('cancel', $js_order_b['order_status']) ? false : true;
	$n1 = $usces->is_status('receipted', $js_order_a['order_status']);
	$n2 = $usces->is_status('receipted', $js_order_b['order_status']);
	if( 'noreceipt' == $status ){
		$pre = ( $t1 && $n1 ) ? true : false;
		$after = ( $t2 && $n2 ) ? true : false;
	}else{
		$pre = ( $t1 ) ? true : false;
		$after = ( $t2 ) ? true : false;
	}
	

	if( $pre && !$after )
		je_change_lank_Invalid( $js_order_b );		
		
	elseif( !$pre && $after )
		je_change_lank_Valid( $js_order_b );
	

}
add_action('usces_pre_delete_orderdata', 'je_pre_delete_orderdata', 10);
function je_pre_delete_orderdata( $order_id ){
	global $usces, $js_order_a;
	$js_order_a = $usces->get_order_data($order_id, 'direct');
}
add_action('usces_after_delete_orderdata', 'je_after_delete_orderdata', 10, 2);
function je_after_delete_orderdata( $order_id, $res ){
	global $js_order_a;
	
	if( $res )
		je_change_lank_Invalid( $js_order_a );		
}
add_action('usces_action_collective_order_reciept', 'je_action_collective_order', 10);
add_action('usces_action_collective_order_status', 'je_action_collective_order', 10);
add_action('usces_action_collective_order_delete', 'je_action_collective_order', 10);
function je_action_collective_order(){
		je_change_lank_collective();		
}

/* Step Mail  ******************************************************/
add_action('usces_action_member_registered', 'je_action_member_registered');
function je_action_member_registered($member){
	$smops = get_option('usces_stp_mail_opts');
	
	$linkage_neo = isset($smops['neo']['linkage']) ? $smops['neo']['linkage'] : 0;
	$key_neo = isset($smops['neo']['selected_id']) ? $smops['neo']['selected_id'] : '';
	$params_neo = isset($smops['neo']['params']) ? $smops['neo']['params'] : array();
	$neo = isset($params_neo[$key_neo]) ? $params_neo[$key_neo] : array('ml_id'=>NULL);
	
	$linkage_proste = isset($smops['proste']['linkage']) ? $smops['proste']['linkage'] : 0;
	$key_proste = isset($smops['proste']['selected_id']) ? $smops['proste']['selected_id'] : '';
	$params_proste = isset($smops['proste']['params']) ? $smops['proste']['params'] : array();
	$proste = isset($params_proste[$key_proste]) ? $params_proste[$key_proste] : array('ml_id'=>NULL);
	
	if( $linkage_neo && !empty($neo['ml_id'])){
		$data = je_make_add_data( 'neo', $member, $smops );
		je_send_data( $data );
	}
	if( $linkage_proste && !empty($proste['ml_id'])){
		$data = je_make_add_data( 'proste', $member, $smops );
		je_send_data( $data );
	}
}
add_action('usces_action_pre_delete_memberdata', 'je_action_pre_delete_memberdata');
function je_action_pre_delete_memberdata($mem_id){
	global $js_member_info, $usces;
	$js_member_info = $usces->get_member_info($mem_id);
}
add_action('usces_action_post_delete_memberdata', 'je_action_post_delete_memberdata', 10, 2);
function je_action_post_delete_memberdata($res, $mem_id){
	global $js_member_info;
	if( !$res ){
		$js_member_info = NULL;
		return;
	}
	
	$smops = get_option('usces_stp_mail_opts');
	$linkage_neo = isset($smops['neo']['linkage']) ? (int)$smops['neo']['linkage'] : 0;
	$linkage_proste = isset($smops['proste']['linkage']) ? (int)$smops['proste']['linkage'] : 0;
	
	if( $linkage_neo ){
		$data = je_make_del_data( 'neo', $js_member_info, $smops );
		je_send_data( $data );
	}
	if( $linkage_proste ){
		$data = je_make_del_data( 'proste', $js_member_info, $smops );
		je_send_data( $data );
	}
	
	$js_member_info = NULL;
}
add_action('init', 'je_step_mail_enqueue_script');
function je_step_mail_enqueue_script() {
	if( isset($_REQUEST['page']) && 'step_mail' == $_REQUEST['page'] ){
		wp_enqueue_script('jquery-ui-tabs', array('jquery-ui-core'));
		$jquery_cookieUrl = USCES_FRONT_PLUGIN_URL.'/js/jquery.cookie.js';
		wp_enqueue_script('jquery-cookie', $jquery_cookieUrl, array('jquery'), '1.0');
	}
}
add_action('usces_action_shop_admin_menue', 'je_add_shop_admin_menue');
function je_add_shop_admin_menue(){
	add_submenu_page(USCES_PLUGIN_BASENAME, 'ステップメール連携', 'ステップメール連携', 6, 'step_mail', 'step_mail_admin_page');
}
function step_mail_admin_page() {
	global $usces;

	$usces->action_status = 'none';
	$usces->action_message = '';
	$smops = get_option('usces_stp_mail_opts');	

	//neo
	if( isset($_POST['stepmail_login_neo']) ){
	
		$login_url_neo = trim($_POST['login_url_neo']);
		if( empty($login_url_neo) ){
			$usces->action_status = 'error';
			$usces->action_message .= 'ログインURLを入力して下さい。';
		}else{
			$smops['neo']['login_url'] = $login_url_neo;
		}
		
		$login_pass_neo = $_POST['login_pass_neo'];
		if( empty($login_pass_neo) ){
			$usces->action_status = 'error';
			$usces->action_message .= 'パスワードを入力して下さい。';
		}else{
			$smops['neo']['login_pass'] = $login_pass_neo;
		}

	}elseif( isset($_POST['ml_setup_neo']) ){

		$smops['neo']['linkage'] = (int)$_POST['linkage_neo'];
		
		if( 'new_ml' == $_POST['melmaga_neo'] ){
			$key = count((array)$smops['neo']['params']);
		}else{
			$key = (int)$_POST['melmaga_neo'];
		}
		
		$ml_name = trim($_POST['ml_name']);
		if( empty($ml_name) ){
			$usces->action_status = 'error';
			$usces->action_message .= 'メルマガ名称を入力して下さい。';
		}else{
			$smops['neo']['params'][$key]['ml_name'] = $ml_name;
		}
		
		$ml_id = trim($_POST['ml_id']);
		if( empty($ml_id) ){
			$usces->action_status = 'error';
			$usces->action_message .= '登録先メルマガIDを入力して下さい。';
		}else{
			$smops['neo']['params'][$key]['ml_id'] = $ml_id;
		}
		
		$smops['neo']['params'][$key]['ml_memo'] = trim($_POST['ml_memo']);
//		$smops['neo']['params'][$key]['ml_encode'] = $_POST['ml_encode'];
		$smops['neo']['params'][$key]['ml_toadmin'] = (int)$_POST['ml_toadmin'];
		$smops['neo']['params'][$key]['ml_tocustomer'] = (int)$_POST['ml_tocustomer'];
		
		$smops['neo']['params'][$key]['ml_user_name'] = isset($_POST['ml_user_name']) ? 1 : 0;
		$smops['neo']['params'][$key]['ml_user_mail'] = isset($_POST['ml_user_mail']) ? 1 : 0;
		$smops['neo']['params'][$key]['ml_user_zip'] = isset($_POST['ml_user_zip']) ? 1 : 0;
		$smops['neo']['params'][$key]['ml_user_pref'] = isset($_POST['ml_user_pref']) ? 1 : 0;
		$smops['neo']['params'][$key]['ml_user_addr'] = isset($_POST['ml_user_addr']) ? 1 : 0;
//		$smops['neo']['params'][$key]['ml_user_cus1'] = isset($_POST['ml_user_cus1']) ? 1 : 0;
//		$smops['neo']['params'][$key]['ml_user_cus2'] = isset($_POST['ml_user_cus2']) ? 1 : 0;
//		$smops['neo']['params'][$key]['ml_user_cus3'] = isset($_POST['ml_user_cus3']) ? 1 : 0;
//		$smops['neo']['params'][$key]['ml_user_cus4'] = isset($_POST['ml_user_cus4']) ? 1 : 0;
//		$smops['neo']['params'][$key]['ml_user_cus5'] = isset($_POST['ml_user_cus5']) ? 1 : 0;
		
		$smops['neo']['selected_id'] = $key;
		
	}elseif( isset($_POST['ml_release_neo']) ){

		if( 'new_ml' != $_POST['melmaga_neo'] ){
			$key = (int)$_POST['melmaga_neo'];
			unset($smops['neo']['params'][$key]);
		}
		
	//proste
	}elseif( isset($_POST['stepmail_login_proste']) ){
	
		$login_url_proste = trim($_POST['login_url_proste']);
		if( empty($login_url_proste) ){
			$usces->action_status = 'error';
			$usces->action_message .= 'ログインURLを入力して下さい。';
		}else{
			$smops['proste']['login_url'] = $login_url_proste;
		}
		
		$login_id_proste = $_POST['login_id_proste'];
		if( empty($login_id_proste) ){
			$usces->action_status = 'error';
			$usces->action_message .= 'プロステIDを入力して下さい。';
		}else{
			$smops['proste']['login_id'] = $login_id_proste;
		}
		
		$login_pass_proste = $_POST['login_pass_proste'];
		if( empty($login_pass_proste) ){
			$usces->action_status = 'error';
			$usces->action_message .= 'パスワードを入力して下さい。';
		}else{
			$smops['proste']['login_pass'] = $login_pass_proste;
		}

	}elseif( isset($_POST['ml_setup_proste']) ){

		$smops['proste']['linkage'] = (int)$_POST['linkage_proste'];
		
		if( 'new_ml' == $_POST['melmaga_proste'] ){
			$key = count((array)$smops['proste']['params']);
		}else{
			$key = (int)$_POST['melmaga_proste'];
		}
		
		$ml_name = trim($_POST['ml_name']);
		if( empty($ml_name) ){
			$usces->action_status = 'error';
			$usces->action_message .= 'プラン名称を入力して下さい。';
		}else{
			$smops['proste']['params'][$key]['ml_name'] = $ml_name;
		}
		
		$ml_id = trim($_POST['ml_id']);
		if( empty($ml_id) ){
			$usces->action_status = 'error';
			$usces->action_message .= '登録先プランIDを入力して下さい。';
		}else{
			$smops['proste']['params'][$key]['ml_id'] = $ml_id;
		}
		
		$ml_memo = $_POST['ml_memo'];
		if( empty($ml_id) ){
			$usces->action_status = 'error';
			$usces->action_message .= '秘密キーを入力して下さい。';
		}else{
			$smops['proste']['params'][$key]['ml_memo'] = $ml_memo;
		}
		
//		$smops['proste']['params'][$key]['ml_encode'] = $_POST['ml_encode'];
		$smops['proste']['params'][$key]['ml_toadmin'] = (int)$_POST['ml_toadmin'];
		$smops['proste']['params'][$key]['ml_tocustomer'] = (int)$_POST['ml_tocustomer'];
		
		$smops['proste']['params'][$key]['ml_user_name1'] = isset($_POST['ml_user_name1']) ? 1 : 1;
		$smops['proste']['params'][$key]['ml_user_name2'] = isset($_POST['ml_user_name2']) ? 1 : 0;
		$smops['proste']['params'][$key]['ml_user_mail'] = isset($_POST['ml_user_mail']) ? 1 : 1;
		$smops['proste']['params'][$key]['ml_user_zip'] = isset($_POST['ml_user_zip']) ? 1 : 0;
		$smops['proste']['params'][$key]['ml_user_pref'] = isset($_POST['ml_user_pref']) ? 1 : 0;
		$smops['proste']['params'][$key]['ml_user_addr1'] = isset($_POST['ml_user_addr1']) ? 1 : 0;
		$smops['proste']['params'][$key]['ml_user_addr2'] = isset($_POST['ml_user_addr2']) ? 1 : 0;
		$smops['proste']['params'][$key]['ml_user_cus1'] = isset($_POST['ml_user_cus1']) ? 1 : 0;
		$smops['proste']['params'][$key]['ml_user_cus2'] = isset($_POST['ml_user_cus2']) ? 1 : 0;
		$smops['proste']['params'][$key]['ml_user_cus3'] = isset($_POST['ml_user_cus3']) ? 1 : 0;
		$smops['proste']['params'][$key]['ml_user_cus4'] = isset($_POST['ml_user_cus4']) ? 1 : 0;
		$smops['proste']['params'][$key]['ml_user_cus5'] = isset($_POST['ml_user_cus5']) ? 1 : 0;
		
		$smops['proste']['selected_id'] = $key;
		
	}elseif( isset($_POST['ml_release_proste']) ){

		if( 'new_ml' != $_POST['melmaga_proste'] ){
			$key = (int)$_POST['melmaga_proste'];
			unset($smops['proste']['params'][$key]);
		}
	}
	
	if( empty($usces->action_message) )
		update_option('usces_stp_mail_opts', $smops);	
	
	require_once(USCES_PLUGIN_DIR . '/includes/admin_step_mail.php');	
}

/* Remove Upgrade Messages ******************************************************/
add_filter('site_option__site_transient_update_plugins', 'usces_filter_hide_update_notice');
function usces_filter_hide_update_notice($data) {
    if (isset($data->response['usc-e-shop/usc-e-shop.php'])) {
        unset($data->response['usc-e-shop/usc-e-shop.php']);
    }
}

/* JE Functions ******************************************************/
function je_is_authority(){
	global $usces, $post, $js_lank_ids;
	
	$permission = get_post_meta($post->ID, '_je_permission', true);
	$permission_lank = $js_lank_ids[$permission];
//usces_log('js_lank_ids : '.print_r($js_lank_ids, true), 'acting_transaction.log');
//usces_log('permission_lank : '.print_r($permission_lank, true), 'acting_transaction.log');
	
	if ( $usces->is_member_logged_in() ){
		$usces->get_current_member();
		$member_info = $usces->get_member_info( $usces->current_member['id'] );
		$member_lank = (int)$member_info['mem_status'];
//usces_log('current_member : '.print_r($usces->current_member, true), 'acting_transaction.log');
//usces_log('member_info : '.print_r($member_info, true), 'acting_transaction.log');
		
	}else{
		$member_lank = NULL;
	}
	if( NULL === $permission_lank ){
		return false;
	}else{
		if( NULL === $member_lank ){
			return false;
		}elseif($permission_lank <= $member_lank){
			return true;
		}else{
			return false;
		}
	}
}

function je_change_lank_Valid( $order_data ){
	global $wpdb, $usces, $js_lank_ids;
	$item_member_lanks = array(0);

	$cart = unserialize($order_data['order_cart']);
	if( empty($cart) )
		return;
	
	$member_id = $order_data['mem_id'];
	if( !$member_id )
		return;
		
	foreach ( (array)$cart as $cart_row ) {
		$post_id = $cart_row['post_id'];
		$_je_item_member_lank = get_post_meta($post_id, '_je_item_member_lank', true);
		$item_member_lanks[] = $js_lank_ids[$_je_item_member_lank];
	}
	rsort($item_member_lanks);
	
	$item_member_lank = (int)$item_member_lanks[0];
	$member_info = $usces->get_member_info( $member_id );
	$member_lank = (int)$member_info['mem_status'];

	if( $member_lank >= $item_member_lank )
		return;
		
	$member_table_name = $wpdb->prefix . "usces_member";
	$mquery = $wpdb->prepare("UPDATE $member_table_name SET mem_status = %d WHERE ID = %d", $item_member_lank, $member_id);
	$wpdb->query( $mquery );
}

function je_change_lank_Invalid( $order_data ){
	global $wpdb, $usces, $js_lank_ids;
	$item_member_lanks = array(0);

	$member_id = $order_data['mem_id'];
	if( !$member_id )
		return;

	$history = $usces->get_member_history($member_id);
//	if( !$history )
//		return;

	foreach ( (array)$history as $umhs ) {
		$cart = $umhs['cart'];
		if( empty($cart) )
			continue;
			
		$set = $usces->getPayments( $umhs['payment_name'] );
		$status = ( $set['settlement'] == 'transferAdvance' || $set['settlement'] == 'transferDeferred' || $set['settlement'] == 'acting_remise_conv' || $set['settlement'] == 'acting_zeus_bank' || $set['settlement'] == 'acting_zeus_conv' || $set['settlement'] == 'acting_jpayment_conv' || $set['settlement'] == 'acting_jpayment_bank' ) ? 'noreceipt' : '';
		$taiou = $usces->is_status('cancel', $umhs['order_status']) ? false : true;
		$receipt = $usces->is_status('receipted', $umhs['order_status']);

		if( 'noreceipt' == $status ){
			if( !($taiou && $receipt) ){
				continue;
			}
		}else{
			if( !$taiou ){
				continue;
			}
		}

		foreach ( (array)$cart as $cart_row ) {
			$post_id = $cart_row['post_id'];
			$_je_item_member_lank = get_post_meta($post_id, '_je_item_member_lank', true);
			$item_member_lanks[] = $js_lank_ids[$_je_item_member_lank];
		}
	}

	rsort($item_member_lanks);

	$item_member_lank = (int)$item_member_lanks[0];
		
	$member_table_name = $wpdb->prefix . "usces_member";
	$mquery = $wpdb->prepare("UPDATE $member_table_name SET mem_status = %d WHERE ID = %d", $item_member_lank, $member_id);
	$wpdb->query( $mquery );
}

function je_change_lank_collective(){
	global $wpdb, $usces, $js_lank_ids;
	$item_member_lanks = array(0);
	
	$member_table_name = $wpdb->prefix . "usces_member";
	$ids = $wpdb->get_col( "SELECT ID FROM $member_table_name" );
	if( !$ids )
		return;

	foreach( (array)$ids as $member_id ){
		$history = $usces->get_member_history($member_id);
		if( !$history )
			continue;

		foreach ( (array)$history as $umhs ) {
			$cart = $umhs['cart'];
			if( empty($cart) )
				continue;
				
			$set = $usces->getPayments( $umhs['payment_name'] );
			$status = ( $set['settlement'] == 'transferAdvance' || $set['settlement'] == 'transferDeferred' || $set['settlement'] == 'acting_remise_conv' || $set['settlement'] == 'acting_zeus_bank' || $set['settlement'] == 'acting_zeus_conv' || $set['settlement'] == 'acting_jpayment_conv' || $set['settlement'] == 'acting_jpayment_bank' ) ? 'noreceipt' : '';
			$taiou = $usces->is_status('cancel', $umhs['order_status']) ? false : true;
			$receipt = $usces->is_status('receipted', $umhs['order_status']);
	
			if( 'noreceipt' == $status ){
				if( !($taiou && $receipt) ){
					continue;
				}
			}else{
				if( !$taiou ){
					continue;
				}
			}

			foreach ( (array)$cart as $cart_row ) {
				$post_id = $cart_row['post_id'];
				$_je_item_member_lank = get_post_meta($post_id, '_je_item_member_lank', true);
				$item_member_lanks[] = $js_lank_ids[$_je_item_member_lank];
			}
		}
	
		rsort($item_member_lanks);
	
		$item_member_lank = (int)$item_member_lanks[0];
			
		$mquery = $wpdb->prepare("UPDATE $member_table_name SET mem_status = %d WHERE ID = %d", $item_member_lank, $member_id);
		$wpdb->query( $mquery );
	}
}

function je_make_add_data( $type, $member, $smops ){
	global $usces;

	$query = '';
	$url = '';
	$data =array();
	
	switch( $type ){
	case 'neo':
		$url = isset($smops['neo']['login_url']) ? 'http://' . $smops['neo']['login_url'] . '/usrctrl.php' : '';
		$url_parts = parse_url($url);
		$key_neo = isset($smops['neo']['selected_id']) ? $smops['neo']['selected_id'] : '';
		$params_neo = isset($smops['neo']['params']) ? $smops['neo']['params'] : array();
		$neo = isset($params_neo[$key_neo]) ? $params_neo[$key_neo] : array();
		$query .= 'mag_id=' . urlencode($neo['ml_id']);
		$query .= '&act=add';
		$query .= '&charaset=u';// . $neo['ml_encode'];
		$query .= '&email=' . urlencode($member['mailaddress1']);
		$query .= ( $neo['ml_user_name'] ) ? '&name=' . urlencode($member['name1'] . $member['name2']) : '';
		$query .= ( $neo['ml_user_zip'] ) ? '&zip=' . urlencode($member['zipcode']) : '';
		$query .= ( $neo['ml_user_pref'] ) ? '&pref=' . urlencode($member['pref']) : '';
		$query .= ( $neo['ml_user_addr'] ) ? '&address=' . urlencode($member['address1'] . $member['address2'] . $member['address3']) : '';
		$query .= '&attr1=';
		$query .= '&type=form';
		$header = "POST " . $url_parts['path'] . " HTTP/1.1\r\n";
		$header .= "Host: " . $url_parts['host'] . "\r\n";
		$header .= "User-Agent: PHP Script\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($query) . "\r\n";
		$header .= "Connection: close\r\n\r\n";
		$header .= $query;
		break;
	
	case 'proste':
		$url = isset($smops['proste']['login_url']) ? 'http://' . $smops['proste']['login_url'] . '/maction.php' : '';
		$url_parts = parse_url($url);
		$key_proste = isset($smops['proste']['selected_id']) ? $smops['proste']['selected_id'] : '';
		$params_proste = isset($smops['proste']['params']) ? $smops['proste']['params'] : array();
		$proste = isset($params_proste[$key_proste]) ? $params_proste[$key_proste] : array();

		//usces_log('name1 : '.$member['name1'].$member['name2'], 'acting_transaction.log');
		$query .= 'nm=' . urlencode(mb_convert_encoding($member['name1'], 'EUC', 'UTF8'));
		$query .= ( $proste['ml_user_name2'] ) ? '&nm2=' . urlencode(mb_convert_encoding($member['name2'], 'EUC', 'UTF8')) : '';
		$query .= '&ml=' . urlencode($member['mailaddress1']);
		$query .= '&act=a';
		$query .= '&pn=' . urlencode($proste['ml_id']);
		$query .= ( $proste['ml_user_zip'] ) ? '&zp=' . urlencode($member['zipcode']) : '';
		$query .= ( $proste['ml_user_pref'] ) ? '&pf=' . urlencode(mb_convert_encoding($member['pref'], 'EUC', 'UTF8')) : '';
		$query .= ( $proste['ml_user_addr1'] ) ? '&ad1=' . urlencode(mb_convert_encoding($member['address1'] . $member['address2'], 'EUC', 'UTF8')) : '';
		$query .= ( $proste['ml_user_addr2'] ) ? '&ad2=' . urlencode(mb_convert_encoding($member['address3'], 'EUC', 'UTF8')) : '';
		if( isset( $_POST['custom_member'] ) && is_array( $_POST['custom_member'] ) ){
			$mi = 1;
			$csmb_meta = usces_has_custom_field_meta( 'member' );
			foreach( $csmb_meta as $cmfkey => $cmfvalue ){
				if( isset($_POST['custom_member'][$cmfkey]) ){
					$query .= ( $proste['ml_user_cus'.$mi] ) ? '&c'.$mi.'='.urlencode(mb_convert_encoding($cmfvalue['name'].':'.esc_html($_POST['custom_member'][$cmfkey]), 'EUC', 'UTF8')) : '';
				}
				$mi++;
				if( 5 < $mi )
					break;
			}
		}
		$query .= '&ky=' . $proste['ml_memo'];
		$query .= '&cd=u';// . $proste['ml_encode'];
		$query .= '&toad=' . $proste['ml_toadmin'];
		$query .= '&tocus=' . $proste['ml_tocustomer'];
		$query .= '&rz=30';
		$query .= '&tmsp=' . time();
		$header = "GET " . $url_parts['path'] . '?' . $query . " HTTP/1.1\r\n";
		$header .= "Host: " . $url_parts['host'] . "\r\n";
		$header .= "Accept: text/html\r\n";
		$header .= "Connection: close\r\n\r\n";
		break;
	}
	$data = compact('url_parts', 'header');
	return $data;
}

function je_make_del_data( $type, $member, $smops ){
	global $usces;
	
	$query = '';
	$url = '';
	$data =array();
	
	switch( $type ){
	case 'neo':
		$url = isset($smops['neo']['login_url']) ? 'http://' . $smops['neo']['login_url'] . '/usrctrl.php' : '';
		$url_parts = parse_url($url);
		$key_neo = isset($smops['neo']['selected_id']) ? $smops['neo']['selected_id'] : '';
		$params_neo = isset($smops['neo']['params']) ? $smops['neo']['params'] : array();
		$neo = isset($params_neo[$key_neo]) ? $params_neo[$key_neo] : array();
		$query .= 'mag_id=' . urlencode($neo['ml_id']);
		$query .= '&act=del';
		$query .= '&charaset=u';// . $neo['ml_encode'];
		$query .= '&email=' . $member['mem_email'];
		$query .= '&type=dairi';
		$header = "POST " . $url_parts['path'] . " HTTP/1.1\r\n";
		$header .= "Host: " . $url_parts['host'] . "\r\n";
		$header .= "User-Agent: PHP Script\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($query) . "\r\n";
		$header .= "Connection: close\r\n\r\n";
		$header .= $query;
		break;
	
	case 'proste':
		$url = isset($smops['proste']['login_url']) ? 'http://' . $smops['proste']['login_url'] . '/maction.php' : '';
		$url_parts = parse_url($url);
		$key_proste = isset($smops['proste']['selected_id']) ? $smops['proste']['selected_id'] : '';
		$params_proste = isset($smops['proste']['params']) ? $smops['proste']['params'] : array();
		$proste = isset($params_proste[$key_proste]) ? $params_proste[$key_proste] : array();

		$query .= 'nm=' . urlencode($member['mem_name1']);
		$query .= ( $proste['ml_user_name2'] ) ? '&nm2=' . urlencode($member['mem_name2']) : '';
		$query .= '&ml=' . urlencode($member['mem_email']);
		$query .= '&act=d';
		$query .= '&pn=' . urlencode($proste['ml_id']);
		$query .= '&ky=' . $proste['ml_memo'];
		$query .= '&cd=u';// . $proste['ml_encode'];
		$query .= '&toad=' . $proste['ml_toadmin'];
		$query .= '&tocus=' . $proste['ml_tocustomer'];
		$header = "GET " . $url_parts['path'] . '?' . $query . " HTTP/1.1\r\n";
		$header .= "Host: " . $url_parts['host'] . "\r\n";
		$header .= "Connection: close\r\n\r\n";
		break;
	}
	$data = compact('url_parts', 'header');
	return $data;
}

function je_send_data( $data ){
	//usces_log('data : '.print_r($data,true), 'step_mail.log');
	$fp = fsockopen($data['url_parts']['host'], 80, $errno, $errstr, 30);
	$return = '';
	$second_return = '';
	$second = false;
	if ($fp){
		fwrite($fp, $data['header']);
		while ( !feof($fp) ) {
			$scr = fgets($fp, 1024);
			$return .= $scr;
			if( strpos($scr, '以下のメルマガを購読解除してよいですか') ){
				$second = 'neo';
			}
		}
		fclose($fp);
		if( 'neo' == $second ){
			preg_match('/mag_id\" value=\"(\d+)\"/s', $return, $id_match);
			preg_match('/email\" value=\"([^\"]+)\"/s', $return, $mail_match);
			$fp = fsockopen($data['url_parts']['host'], 80, $errno, $errstr, 30);
			$query .= 'mag_id=' . $id_match[1];
			$query .= '&act=del';
			$query .= '&del_check=1';
			$query .= '&email=' . $mail_match[1];
			$query .= '&hash=';
			$header = "POST " . $data['url_parts']['path'] . " HTTP/1.1\r\n";
			$header .= "Host: " . $data['url_parts']['host'] . "\r\n";
			$header .= "User-Agent: PHP Script\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($query) . "\r\n";
			$header .= "Connection: close\r\n\r\n";
			$header .= $query;
			fwrite($fp, $header);
			while ( !feof($fp) ) {
				$scr = fgets($fp, 1024);
				$second_return .= $scr;
			}
			fclose($fp);
		}
	}else{
		usces_log('send_data : error ' . print_r($data, true), 'step_mail.log');
	}
}
?>