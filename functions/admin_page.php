<?php
function admin_prodauct_footer(){
	switch( $_REQUEST['page'] ){
		case 'usces_itemedit':
			if( !isset($_REQUEST['action']) ){
				break;
			}
		case 'usces_itemnew':
?>
<script type="text/javascript">
(function($) {
    var submit_event = true;
    // 下書き保存やプレビューの場合は必須チェックを行わない
    $('#post-preview, #save-post').click(function(){
        submit_event = false;
        return true;
    });
    $('#post').submit(function(e){
 		var mes = '';
		var itemCode = $("#itemCode").val();
		var itemName = $("#itemName").val();
		var itemsku = $("input[name^='itemsku\[']");
        if (submit_event) {
//			if ( "" == itemCode ) {
//				mes += '商品コードが入力されていません。<br />';
//				$("#itemCode").css({'background-color': '#FFA'}).click(function(){
//					$(this).css({'background-color': '#FFF'});
//				});
//			}
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
				$("#major-publishing-actions").append('<div id="usces_ess"></div>');
				$('#ajax-loading').css({'visibility': 'hidden'});
				$('#draft-ajax-loading').css({'visibility': 'hidden'});
				$('#publish').removeClass('button-primary-disabled');
				$('#save-post').removeClass('button-disabled');
				$("#usces_ess").html(mes);
				return false;
			} else {
	            $('#usces_ess').fadeOut();
				return true;
			}
        } else {
            return true;
        }
    });
	
//	$('#postimagediv h3').html('<span>商品画像</span>');
	$('#itemCode').blur( 
		function() { 
			if ( $("#itemCode").val().length == 0 ) return;
			uscesItem.newdraft($('#itemCode').val());
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
	}
}

function usces_action_transition_post_status( $new_status, $old_status, $post){
	global $usces;
	$itemCode  = trim($_POST['itemCode' ]);
	//usces_log('postarr : '.print_r($post,true), 'acting_transaction.log');
	if( 'publish' == $new_status && 'post' == $post->post_type && $res = usces_is_same_itemcode($post->ID, $itemCode)) {
		$usces->action_message .= 'post_ID ';
		foreach( $res as $postid )
			$usces->action_message .= $postid . ', ';
		$usces->action_message .= 'に同じ商品コードが登録されています。' . "<br />";
	}
}

function usces_filter_redirect_post_location( $location, $post_id ){
	global $usces;
//	usces_log('usces_filter_redirect_post_location : '.print_r($location,true), 'acting_transaction.log');
	if( !empty($usces->action_message) )
		$location = add_query_arg( 'usces_notice', urlencode($usces->action_message), $location );
	return $location;
}

function usces_action_updated_messages(){
	global $notice;
	
	if( isset($_GET['usces_notice']) ){
		$notice = urldecode($_GET['usces_notice']) . $notice;
	}
}

?>