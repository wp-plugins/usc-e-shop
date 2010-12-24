<?php
/*
 Functions that should be included first
*/
function usces_filter_locale($locale){
	if(is_admin()){
		return $locale;
	}else{
		$usces_options = get_option('usces');
		if( isset($usces_options['system']['front_lang']) && !empty($usces_options['system']['front_lang']) ){
			return $usces_options['system']['front_lang'];
		}else{
			return $locale;
		}
	}
}
?>