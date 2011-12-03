<?php
/*
 Functions that should be included first
*/
function usces_filter_locale($locale){
	if( !is_admin() 
		|| ( isset($_POST['action']) && isset($_POST['mode']) && 'order_item_ajax' == $_POST['action'] && in_array($_POST['mode'], array('completionMail', 'orderConfirmMail', 'changeConfirmMail', 'receiptConfirmMail', 'mitumoriConfirmMail', 'cancelConfirmMail', 'otherConfirmMail')))
		|| ( isset($_REQUEST['order_action']) && 'pdfout' == $_REQUEST['order_action'] )
	){
		$usces_options = get_option('usces');
		if( isset($usces_options['system']['front_lang']) && !empty($usces_options['system']['front_lang']) ){
			return $usces_options['system']['front_lang'];
		}else{
			return $locale;
		}
	}else{
		return $locale;
	}
}
?>