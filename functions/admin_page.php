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

        return true;
    });
    $('#post').submit(function(e){
			$('form#post').attr('action', '');
    });
		
})(jQuery);
</script>
<?php
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

function usces_typenow(){
	global $typenow;
	if( isset($_GET['page']) && ('usces_itemedit' == $_GET['page'] || 'usces_itemnew' == $_GET['page']) )
		$typenow = '';
}

?>