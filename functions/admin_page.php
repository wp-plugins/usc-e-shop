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
 		var mes = '';
		var itemCode = $("#itemCode").val();
		var itemName = $("#itemName").val();
		var itemsku = $("input[name^='itemsku\[']");
		var DeliveryMethod = $("input[name^='itemDeliveryMethod\[']");
        if (submit_event) {
			if ( 0 == DeliveryMethod.length ) {
				mes += '配送方法が選択できません。商品登録を行う前に「配送設定」より配送方法の登録を済ませてください。<br />';
			}
			if ( "" == itemCode ) {
				mes += '商品コードが入力されていません。<br />';
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
				mes += '商品名が入力されていません。<br />';
				$("#itemName").css({'background-color': '#FFA'}).click(function(){
					$(this).css({'background-color': '#FFF'});
				});
			}
			if ( 0 == itemsku.length ) {
				mes += 'SKUが登録されていません。<br />';
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
		var status = 'normal';

		if( "" == $("*[name='order_mail']").val() ) {
			status = 'error';
			$("*[name='order_mail']").css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( "" == $("*[name='inquiry_mail']").val() ) {
			status = 'error';
			$("*[name='inquiry_mail']").css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( "" == $("*[name='sender_mail']").val() ) {
			status = 'error';
			$("*[name='sender_mail']").css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( "" == $("*[name='error_mail']").val() ) {
			status = 'error';
			$("*[name='error_mail']").css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("*[name='point_num']").val() ) ) {
			status = 'error';
			$("*[name='point_num']").css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("*[name='discount_num']").val() ) ) {
			status = 'error';
			$("*[name='discount_num']").css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("*[name='postage_privilege']").val() ) ) {
			status = 'error';
			$("*[name='postage_privilege']").css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("*[name='purchase_limit']").val() ) ) {
			status = 'error';
			$("*[name='purchase_limit']").css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("*[name='tax_rate']").val() ) ) {
			status = 'error';
			$("*[name='tax_rate']").css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("*[name='point_rate']").val() ) ) {
			status = 'error';
			$("*[name='point_rate']").css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("*[name='start_point']").val() ) ) {
			status = 'error';
			$("*[name='start_point']").css({'background-color': '#FFA'}).click(function(){
				$(this).css({'background-color': '#FFF'});
			});
		}

		if( status != 'normal' ) {
			$("#aniboxStatus").removeClass("none");
			$("#aniboxStatus").addClass("error");
			$("#info_image").attr("src", "<?php echo USCES_PLUGIN_URL; ?>/images/list_message_error.gif");
			$("#info_massage").html("データに不備があります");
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

function usces_item_dupricate($post_id){
	global $wpdb;

	if ( !current_user_can( 'edit_posts' ) )
		wp_die( __( 'Sorry, you do not have the right to access this site.' ) );

	if( empty($post_id) )
		wp_die( __( 'データが存在しません。', 'usces' ) );
	
	if ( !$post_data = wp_get_single_post($post_id, ARRAY_A) )
		wp_die( __( 'データが存在しません。', 'usces' ) );

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
			$valstr .= '(' . $newpost_id . ", '" . $key . "','" . $value . "'),";
		
		}else if( $prefix == '_isku' || $prefix == '_iopt' ){
		
			$value = $data->meta_value;
			$key = $data->meta_key;
			$valstr .= '(' . $newpost_id . ", '" . $key . "','" . $value . "'),";
		
		}

	}
	$valstr = rtrim($valstr, ',');
	$query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES $valstr";
	$res = mysql_query($query);
	if(!$res ) return;

	return $newpost_id;
}

function usces_all_delete_itemdata(&$obj){
	global $wpdb;

	if ( !current_user_can( 'edit_posts' ) )
		wp_die( __( 'Sorry, you do not have the right to access this site.' ) );

	$ids = $_POST['listcheck'];
	$status = true;
	foreach ( (array)$ids as $post_id ){
		if ( !wp_delete_post($post_id, true) )
			$status = false;
//		$query = $wpdb->prepare("DELETE FROM $wpdb->posts WHERE ID = %d", $post_id);
//		$res = $wpdb->query( $query );
//		if( $res !== false ) {
//			$query = $wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE post_id = %d", $post_id);
//			$res = $wpdb->query( $query );
//			if( $res === false ) {
//				$status = false;
//			}
//			$query = $wpdb->prepare("DELETE FROM $wpdb->term_relationships WHERE object_id = %d", $post_id);
//			$res = $wpdb->query( $query );
//			if( $res === false ) {
//				$status = false;
//			}
//			$query = "SELECT term_taxonomy_id, COUNT(*) AS ct FROM $wpdb->term_relationships 
//					GROUP BY term_taxonomy_id";
//			$relation_data = $wpdb->get_results( $query, ARRAY_A);
//			foreach((array)$relation_data as $rows){
//				
//				$term_ids['term_taxonomy_id'] = $rows['term_taxonomy_id'];
//				$updatas['count'] = $rows['ct'];
//				$wpdb->update( $wpdb->term_taxonomy, $updatas, $term_ids );
//			}
//		}

	}
	if ( true === $status ) {
		$obj->set_action_status('success', __('I completed collective operation.','usces'));
	} elseif ( false === $status ) {
		$obj->set_action_status('error', __('ERROR: I was not able to complete collective operation','usces'));
	} else {
		$obj->set_action_status('none', '');
	}
}

?>