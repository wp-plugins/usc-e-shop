<?php
function usces_redirect(){
	if ( isset($_POST['wp-preview']) && 'dopreview' == $_POST['wp-preview'] ){
		$action = 'preview';
	}
			
	switch( $action ){
		case 'preview':
			check_admin_referer( 'autosave', 'autosavenonce' );
		
			$url = post_preview();
		
			wp_redirect($url);
			exit();
			break;
	}
}

?>
