<?php
/******************************************************/
// Joint Elements Customize
/******************************************************/

/* Member Lank ******************************************************/
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

/* Change Member Lank ******************************************************/
add_action('usces_action_reg_orderdata', 'je_action_reg_orderdata');
function je_action_reg_orderdata($args){
	global $wpdb;
	list($cart, $entry, $order_id, $member_id, $payments, $charging_type, $receipt_status) = $args;
	
	//usces_log('je_action_reg_orderdata : ' . print_r($args, true), 'acting_transaction.log');

	if( empty($member_id) || 'noreceipt' == $receipt_status ) return;
	
	$lanks = array();
	for($i=0; $i<count($cart); $i++) { 
		$cart_row = $cart[$i];
		$post_id = $cart_row['post_id'];
		$lanks[] = (int)get_post_meta($post_id, 'member_lank', true);
	}
	rsort($lanks);
	$newlank = $lanks[0];
	
	$table = $wpdb->prefix . "usces_member";
	$query = $wpdb->prepare("SELECT mem_status FROM $table WHERE ID = %d", $member_id);
	$member_status = (int)$wpdb->get_var( $query );
	
	if( $newlank > $member_status ){
		$query = $wpdb->prepare("UPDATE $table SET mem_status = %d WHERE ID = %d", $newlank, $member_id);
		if( false === $wpdb->query($query) ){
			usces_log('Change member lank error : ' . $wpdb->last_error, 'acting_transaction.log');
		}
	}
}

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
	extract(shortcode_atts(array(
		'code' => '',
		'sku' => '',
	), $atts));

	return "code = {$code}";
}

/* JE Functions ******************************************************/
function je_is_authority(){
	global $usces, $post, $js_lank_ids;
	
	$permission = get_post_meta($post->ID, 'permission', true);
	$permission_lank = $js_lank_ids[$permission];
	
	if ( $usces->is_member_logged_in() ){
		$usces->get_current_member();
		$member_info = $usces->get_member_info( $usces->current_member['id'] );
		$member_lank = (int)$member_info['mem_status'];
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
?>