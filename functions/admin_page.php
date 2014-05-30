<?php
function admin_prodauct_footer(){
	switch( $_GET['page'] ){
		case 'usces_itemedit':
			if( !isset($_GET['action']) || ( isset($_REQUEST['action']) && 'upload_register' == $_REQUEST['action'] ) ){
				break;
			}
		case 'usces_itemnew':
?>
<script type="text/javascript">
(function($) {
    var submit_event = true;
    // 下書き保存やプレビューの場合は必須チェックを行わない
    $('#post-preview, #save-post').click(function(){
		if(!$("#auto_draft").val())
	        submit_event = false;
        return true;
    });
    $('#post').submit(function(e){
 		$('form#post').attr('action', '');
		var mes = '';
		var itemCode = $("#itemCode").val();
		var itemName = $("#itemName").val();
		var itemsku = $("input[name^='itemsku\[']");
		var DeliveryMethod = $("input[name^='itemDeliveryMethod\[']");
 		var itemDivision = $("input[name='item_division']:checked").val();
       if (submit_event) {
			if ( 0 == DeliveryMethod.length && (!itemDivision || 'shipped' == itemDivision) ) {
				mes += "<?php _e("You can not choose the delivery method. Please complete the registration of shipping method than 'delivery setting' before product registration.", "usces"); ?><br />";
			}
			if ( "" == itemCode ) {
				mes += '<?php _e("Product code has not been entered.", "usces"); ?><br />';
				$("#itemCode").css({'background-color': '#FFA'}).click(function(){
					$(this).css({'background-color': '#FFF'});
				});
			}
//			if ( ! checkCode( itemCode ) ) {
//				mes += '商品コードは半角英数（-_を含む）で入力して下さい。<br />';
//				$("#itemCode").css({'background-color': '#FFA'}).click(function(){
//					$(this).css({'background-color': '#FFF'});
//				});
//			}
			if ( "" == itemName ) {
				mes += '<?php _e("Brand name has not been entered.", "usces"); ?><br />';
				$("#itemName").css({'background-color': '#FFA'}).click(function(){
					$(this).css({'background-color': '#FFF'});
				});
			}
			if ( 0 == itemsku.length ) {
				mes += '<?php _e("SKU is not registered.", "usces"); ?><br />';
				$("#newskuname").css({'background-color': '#FFA'}).click(function(){
					$(this).css({'background-color': '#FFF'});
				});
				$("#newskuprice").css({'background-color': '#FFA'}).click(function(){
					$(this).css({'background-color': '#FFF'});
				});
			}
			if ( '' != mes) {
				$("#major-publishing-actions").append('<div id="usces_mess"></div>');
				$('#ajax-loading').css({'visibility': 'hidden'});
				$('#draft-ajax-loading').css({'visibility': 'hidden'});
				$('#publish').removeClass('button-primary-disabled');
				$('#save-post').removeClass('button-disabled');
				$("#usces_mess").html(mes);
				return false;
			} else {
	            $('#usces_mess').fadeOut();
				return true;
			}
        } else {
            return true;
        }
    });
	
//	$('#postimagediv h3').html('<span>商品画像</span>');
	$('#itemName').blur( 
		function() { 
			if ( $("#itemName").val().length == 0 ) return;
			uscesItem.newdraft($('#itemName').val());
	});
	
	$( "#item-sku-list" ).sortable({
		//placeholder: "ui-state-highlight",
		handle : 'th',
		axis : 'y',
		cursor : "move",
		tolerance : "pointer",
		forceHelperSize : true,
		forcePlaceholderSize : true,
		revert : 300,
		opacity: 0.6,
		cancel: ":input,button",
		update : function(){
			var data=[];
			$("table","#item-sku-list").each(function(i,v){
				data.push($(this).attr('id'));
			});
			if( 1 < data.length ){
				itemSku.dosort(data.toString());
			}
		}
	});
	$( "#item-opt-list" ).sortable({
		//placeholder: "ui-state-highlight",
		handle : 'th',
		axis : 'y',
		cursor : "move",
		tolerance : "pointer",
		forceHelperSize : true,
		forcePlaceholderSize : true,
		revert : 300,
		opacity: 0.6,
		cancel: ":input,button",
		update : function(){
			var data=[];
			$("table","#item-opt-list").each(function(i,v){
				data.push($(this).attr('id'));
			});
			if( 1 < data.length ){
				itemOpt.dosort(data.toString());
			}
		}
	});
		
})(jQuery);
</script>
<?php
			break;
		case 'usces_initial':
?>
<script type="text/javascript">
(function($) {
	$('#option_form').submit(function(e) {
		var error = 0;

		if( "" == $("*[name='order_mail']").val() ) {
			error++;
			$("*[name='order_mail']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( "" == $("*[name='inquiry_mail']").val() ) {
			error++;
			$("*[name='inquiry_mail']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( "" == $("*[name='sender_mail']").val() ) {
			error++;
			$("*[name='sender_mail']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( "" == $("*[name='error_mail']").val() ) {
			error++;
			$("*[name='error_mail']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("*[name='point_num']").val() ) ) {
			error++;
			$("*[name='point_num']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("*[name='discount_num']").val() ) ) {
			error++;
			$("*[name='discount_num']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("*[name='postage_privilege']").val() ) ) {
			error++;
			$("*[name='postage_privilege']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("*[name='purchase_limit']").val() ) ) {
			error++;
			$("*[name='purchase_limit']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("*[name='tax_rate']").val() ) ) {
			error++;
			$("*[name='tax_rate']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("*[name='point_rate']").val() ) ) {
			error++;
			$("*[name='point_rate']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("*[name='start_point']").val() ) ) {
			error++;
			$("*[name='start_point']").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}

		if( 0 < error ) {
			$("#aniboxStatus").removeClass("none");
			$("#aniboxStatus").addClass("error");
			$("#info_image").attr("src", "<?php echo USCES_PLUGIN_URL; ?>/images/list_message_error.gif");
			$("#info_massage").html("<?php _e('Data have deficiency.','usces'); ?>");
			$("#anibox").animate({ backgroundColor: "#FFE6E6" }, 2000);
			return false;
		} else {
			return true;
		}
	});

	$( "#item-opt-list" ).sortable({
		//placeholder: "ui-state-highlight",
		handle : 'th',
		axis : 'y',
		cursor : "move",
		tolerance : "pointer",
		forceHelperSize : true,
		forcePlaceholderSize : true,
		revert : 300,
		opacity: 0.6,
		cancel: ":input,button",
		update : function(){
			var data=[];
			$("table","#item-opt-list").each(function(i,v){
				data.push($(this).attr('id'));
			});
			if( 1 < data.length ){
				itemOpt.dosort(data.toString());
			}
		}
	});
	
	$( "#payment-list" ).sortable({
		//placeholder: "ui-state-highlight",
		handle : 'th',
		axis : 'y',
		cursor : "move",
		tolerance : "pointer",
		forceHelperSize : true,
		forcePlaceholderSize : true,
		revert : 300,
		opacity: 0.6,
		cancel: ":input,button",
		update : function(){
			var data=[];
			$("table","#payment-list").each(function(i,v){
				data.push($(this).attr('id'));
			});
			if( 1 < data.length ){
				payment.dosort(data.toString());
			}
		}
	});
		
})(jQuery);
</script>
<?php
			break;
		case 'usces_cart':
?>
<script type="text/javascript">
(function($) {
	$('#option_form').submit(function(e) {
		var error = 0;

		if( !$("#indi_item_name").attr("checked") && 
			!$("#indi_item_code").attr("checked") && 
			!$("#indi_sku_name").attr("checked") && 
			!$("#indi_sku_code").attr("checked") ) {
			error++;
			$("#indi_item_name").parent().css({'background-color': '#FFA'}).click(function() {
				$("#indi_item_name").parent().css({'background-color': '#FFF'});
				$("#indi_item_code").parent().css({'background-color': '#FFF'});
				$("#indi_sku_name").parent().css({'background-color': '#FFF'});
				$("#indi_sku_code").parent().css({'background-color': '#FFF'});
			});
			$("#indi_item_code").parent().css({'background-color': '#FFA'}).click(function() {
				$("#indi_item_name").parent().css({'background-color': '#FFF'});
				$("#indi_item_code").parent().css({'background-color': '#FFF'});
				$("#indi_sku_name").parent().css({'background-color': '#FFF'});
				$("#indi_sku_code").parent().css({'background-color': '#FFF'});
			});
			$("#indi_sku_name").parent().css({'background-color': '#FFA'}).click(function() {
				$("#indi_item_name").parent().css({'background-color': '#FFF'});
				$("#indi_item_code").parent().css({'background-color': '#FFF'});
				$("#indi_sku_name").parent().css({'background-color': '#FFF'});
				$("#indi_sku_code").parent().css({'background-color': '#FFF'});
			});
			$("#indi_sku_code").parent().css({'background-color': '#FFA'}).click(function() {
				$("#indi_item_name").parent().css({'background-color': '#FFF'});
				$("#indi_item_code").parent().css({'background-color': '#FFF'});
				$("#indi_sku_name").parent().css({'background-color': '#FFF'});
				$("#indi_sku_code").parent().css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("#pos_item_name").val() ) ) {
			error++;
			$("#pos_item_name").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("#pos_item_code").val() ) ) {
			error++;
			$("#pos_item_code").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("#pos_sku_name").val() ) ) {
			error++;
			$("#pos_sku_name").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("#pos_sku_code").val() ) ) {
			error++;
			$("#pos_sku_code").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}

		if( 0 < error ) {
			$("#aniboxStatus").removeClass("none");
			$("#aniboxStatus").addClass("error");
			$("#info_image").attr("src", "<?php echo USCES_PLUGIN_URL; ?>/images/list_message_error.gif");
			$("#info_massage").html("<?php _e('Data have deficiency.','usces'); ?>");
			$("#anibox").animate({ backgroundColor: "#FFE6E6" }, 2000);
			if( $.fn.jquery < "1.10" ) {
				$('#uscestabs_cart').tabs("select", 0);
			} else {
				$('#uscestabs_cart').tabs("option", "active", 0);
			}
			return false;
		} else {
			return true;
		}
	});
})(jQuery);
</script>
<?php
			break;
	}
}

function admin_post_footer(){
	switch( $GLOBALS['hook_suffix'] ){
		case 'post.php':
		case 'post-new.php':
			$categories = get_categories( array('child_of' => USCES_ITEM_CAT_PARENT_ID) );
?>
<script type="text/javascript">
(function($) {
	$("#category-<?php echo USCES_ITEM_CAT_PARENT_ID ?>").remove();
	$("#popular-category-<?php echo USCES_ITEM_CAT_PARENT_ID ?>").remove();
	<?php
			foreach ( $categories as $category ){
	?>
	$("#popular-category-<?php echo $category->term_id ?>").remove();
	<?php
			}
	?>
})(jQuery);
</script>
<?php
			break;
	}
}
//function usces_action_transition_post_status( $new_status, $old_status, $post){
//	global $usces;
//	$itemCode  = trim($_POST['itemCode' ]);
//	//usces_log('postarr : '.print_r($post,true), 'acting_transaction.log');
//	if( 'publish' == $new_status && 'post' == $post->post_type && $res = usces_is_same_itemcode($post->ID, $itemCode)) {
//		$usces->action_message .= 'post_ID ';
//		foreach( $res as $postid )
//			$usces->action_message .= $postid . ', ';
//		$usces->action_message .= 'に同じ商品コードが登録されています。' . "<br />";
//	}
//}
//
//function usces_filter_redirect_post_location( $location, $post_id ){
//	global $usces;
////	usces_log('usces_filter_redirect_post_location : '.print_r($location,true), 'acting_transaction.log');
//	if( !empty($usces->action_message) )
//		$location = add_query_arg( 'usces_notice', urlencode($usces->action_message), $location );
//	return $location;
//}
//
//function usces_action_updated_messages(){
//	global $notice;
//	
//	if( isset($_GET['usces_notice']) ){
//		$notice = urldecode($_GET['usces_notice']) . $notice;
//	}
//}

function usces_item_duplicate($post_id){
	global $wpdb;

	if ( !current_user_can( 'edit_posts' ) )
		wp_die( __( 'Sorry, you do not have the right to access this site.' ) );

	if( empty($post_id) )
		wp_die( __( 'Data does not exist.', 'usces' ) );
	
	//if ( !$post_data = wp_get_single_post($post_id, ARRAY_A) )
	if ( !$post_data = get_post($post_id, ARRAY_A) )
		wp_die( __( 'Data does not exist.', 'usces' ) );

	$datas = array();
	foreach($post_data as $key => $value){
		switch( $key ){
			case 'ID':
				break;
			case 'post_date':
			case 'post_modified':
				//$datas[$key] = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
				break;
			case 'post_date_gmt':
			case 'post_modified_gmt':
				//$datas[$key] = gmdate('Y-m-d H:i:s');
				break;
			case 'post_status':
				$datas[$key] = 'draft';
				break;
			case 'post_name':
			case 'guid':
				//$datas[$key] = '';
				break;
			case 'menu_order':
			case 'post_parent':
			case 'comment_count':
				$datas[$key] = 0;
				break;
			default:
				$datas[$key] = $value;
		}
	}

	$datas['post_category'] = wp_get_post_categories( $post_id );
	
	$newpost_id = wp_insert_post( $datas );
	
	$query = $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d", $post_id);
	$meta_data = $wpdb->get_results( $query );
	if(!$meta_data) return;
	$valstr = '';
	foreach($meta_data as $data){
		
		$prefix = substr($data->meta_key, 0, 5);
		$prefix1 = substr($data->meta_key, 0, 1);
		$prefix2 = substr($data->meta_key, 0, 11);
		
		if( $prefix == '_item' ){
		
			switch( $data->meta_key ){
				case '_itemCode':
					$value = $data->meta_value . '(copy)';
					break;
				default:
					$value = $data->meta_value;
			}
			$key = $data->meta_key;
			//$valstr .= '(' . $newpost_id . ", '" . $wpdb->escape($key) . "','" . $wpdb->escape($value) . "'),";
			$valstr .= '(' . $newpost_id . ", '" . esc_sql($key) . "','" . esc_sql($value) . "'),";
		}else if( $prefix == '_isku' || $prefix == '_iopt' ){
		
			$value = $data->meta_value;
			$key = $data->meta_key;
			//$valstr .= '(' . $newpost_id . ", '" . $wpdb->escape($key) . "','" . $wpdb->escape($value) . "'),";
			$valstr .= '(' . $newpost_id . ", '" . esc_sql($key) . "','" . esc_sql($value) . "'),";
		}
		if( $prefix1 != '_' ){
		
			$value = $data->meta_value;
			$key = $data->meta_key;
			//$valstr .= '(' . $newpost_id . ", '" . $wpdb->escape($key) . "','" . $wpdb->escape($value) . "'),";
			$valstr .= '(' . $newpost_id . ", '" . esc_sql($key) . "','" . esc_sql($value) . "'),";
		}

	}
	$valstr = rtrim($valstr, ',');
	$query = $wpdb->prepare("INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES $valstr", $post_id);
	$res = $wpdb->query($query);
	if(!$res ) return;
	
	do_action('usces_action_item_dupricate', $post_id, $newpost_id);

	return $newpost_id;
}
function usces_item_dupricate($post_id){
	return usces_item_duplicate($post_id);
}

function usces_all_delete_itemdata(&$obj){
	global $wpdb;

	if ( !current_user_can( 'edit_posts' ) )
		wp_die( __( 'Sorry, you do not have the right to access this site.' ) );

	$ids = $_POST['listcheck'];
	$status = true;
	foreach ( (array)$ids as $post_id ){
		if ( !wp_delete_post($post_id, true) ){
			$status = false;
		}
	}
	if ( true === $status ) {
		$obj->set_action_status('success', __('I completed collective operation.','usces'));
	} elseif ( false === $status ) {
		$obj->set_action_status('error', __('ERROR: I was not able to complete collective operation','usces'));
	} else {
		$obj->set_action_status('none', '');
	}
}

function usces_typenow(){
	global $typenow;
	if( isset($_GET['page']) && ('usces_itemedit' == $_GET['page'] || 'usces_itemnew' == $_GET['page']) )
		$typenow = '';
		
}

function usces_admin_notices(){
	global $usces;
	if( 'on' == $usces->options['acting_settings']['zeus']['activate'] ){
		$options = get_option('usces');
		if(!isset($options['acting_settings']['zeus']['vercheck']) || '115' != $options['acting_settings']['zeus']['vercheck'] ){
			$msg = '<div class="error"><p>決済に「ゼウス」をご利用の場合は、<a href="' . site_url('/wp-admin/admin.php?page=usces_settlement') . '">「セキュリティーコード」の設定内容を確認</a>して更新ボタンを押して下さい。設定を更新するとこのメッセージは表示されなくなります。</p></div>';
			echo $msg;	
		}
	}
}

?>