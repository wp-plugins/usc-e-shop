<?php
$html .= '
<script type="text/javascript">
	var selected_delivery_method = \'\';
	var selected_delivery_time = \'\';

	jQuery(function($){
		';

if($usces_entries['order']['delivery_method'] === NULL){
	$default_deli = $this->get_available_delivery_method();
	$html .= 'selected_delivery_method = \'' . $default_deli[0] . '\';';
}else{
	$html .= 'selected_delivery_method = \'' . $usces_entries['order']['delivery_method'] . '\';';
}
$html .= 'selected_delivery_time = \'' . $usces_entries['order']['delivery_time'] . '\';
		var delivery_time = [];delivery_time[0] = [];';

foreach($this->options['delivery_method'] as $dmid => $dm){
	$lines = explode("\n", $dm['time']);
	$html .= 'delivery_time[' . $dm['id'] . '] = [];';
	foreach((array)$lines as $line){
		if(trim($line) != ''){
			$html .= 'delivery_time[' . $dm['id'] . '].push("' . trim($line) . '");';
		}
	}
}

$payments_str = '';
$payments_arr = array();
foreach ( (array)$this->options['payment_method'] as $array ) {
	switch( $array['settlement'] ){
		case 'acting_zeus_card':
			$paymod_base = 'zeus';
			if( 'on' == $this->options['acting_settings'][$paymod_base]['card_activate'] 
				&& 'on' == $this->options['acting_settings'][$paymod_base]['activate'] ){
			
				$payments_str .= "'" . $array['name'] . "': '" . $paymod_base . "', ";
				$payments_arr[] = $paymod_base;
			}
			break;
		case 'acting_zeus_conv':
			$paymod_base = 'zeus';
			if( 'on' == $this->options['acting_settings'][$paymod_base]['conv_activate'] 
				&& 'on' == $this->options['acting_settings'][$paymod_base]['activate'] ){
			
				$payments_str .= "'" . $array['name'] . "': '" . $paymod_base . "_conv', ";
				$payments_arr[] = $paymod_base.'_conv';
			}
			break;
		case 'acting_remise_card':
			$paymod_base = 'remise';
			if( 'on' == $this->options['acting_settings'][$paymod_base]['card_activate'] 
				&& 'on' == $this->options['acting_settings'][$paymod_base]['howpay'] 
				&& 'on' == $this->options['acting_settings'][$paymod_base]['activate'] ){
			
				$payments_str .= "'" . $array['name'] . "': '" . $paymod_base . "', ";
				$payments_arr[] = $paymod_base;
			}
			break;
	}
}
$payments_str = rtrim($payments_str, ', ');

$html .= "
		var uscesPaymod = { " . $payments_str . " };

		$(\"input[name='order\\[payment_name\\]']\").click(function() {";

foreach($payments_arr as $pm ){
	$html .= "
			$(\"#" . $pm . "\").css({\"display\": \"none\"});\n";
}

$html .= "
			var chk_pay = $(\"input[name='order\\[payment_name\\]']:checked\").val();
			if( uscesPaymod[chk_pay] != '' ){
				$(\"#\" + uscesPaymod[chk_pay]).css({\"display\": \"table\"});
			}
		});
			
		$('#delivery_method_select').change(function() {
			orderfunc.make_delivery_time(($('#delivery_method_select option:selected').val()-0));
		});
			
		orderfunc = {
			make_delivery_time : function(selected) {
				var option = '';
				if(delivery_time[selected] != undefined){
					for(var i=0; i<delivery_time[selected].length; i++){
						if( delivery_time[selected][i] == selected_delivery_time ) {
							option += '<option value=\"' + delivery_time[selected][i] + '\" selected=\"selected\">' + delivery_time[selected][i] + '</option>';
						}else{
							option += '<option value=\"' + delivery_time[selected][i] + '\">' + delivery_time[selected][i] + '</option>';
						}
					}
				}
				if(option == ''){
					option = '<option value=\"" . __('There is not a choice.', 'usces') . "\">' + '" . __('There is not a choice.', 'usces') . "' + '</option>';
				}
				$(\"#delivery_time_select\").html(option);
			}
		};
	";

if($usces_entries['delivery']['delivery_flag'] == 0) {
	$html .= "
		$(\"#delivery_table\").css({display: \"none\"});\n";
}

$html .= "
		orderfunc.make_delivery_time(selected_delivery_method);\n";

foreach($payments_arr as $pn => $pm ){
	$html .= "
		$(\"#" . $pm . "\").css({\"display\": \"none\"});\n";
	
	switch( $pm ){
		case 'zeus':
			if('on' == $this->options['acting_settings'][$pm]['howpay']){
				$html .= "
				$(\"input[name='howpay']\").change(function() {
					if( '' != $(\"select[name='cbrand'] option:selected\").val() ){
						$(\"#div_" . $pm . "\").css({\"display\": \"\"});
					}
					if( '1' == $(\"input[name='howpay']:checked\").val() ){
						$(\"#cbrand_" . $pm . "\").css({\"display\": \"none\"});
						$(\"#div_" . $pm . "\").css({\"display\": \"none\"});
					}else{
						$(\"#cbrand_" . $pm . "\").css({\"display\": \"\"});
					}
				});
		
				$(\"select[name='cbrand']\").change(function() {
					$(\"#div_" . $pm . "\").css({\"display\": \"\"});
					if( '1' == $(\"select[name='cbrand'] option:selected\").val() ){
						$(\"#brand1\").css({\"display\": \"\"});
						$(\"#brand2\").css({\"display\": \"none\"});
						$(\"#brand3\").css({\"display\": \"none\"});
					}else if( '2' == $(\"select[name='cbrand'] option:selected\").val() ){
						$(\"#brand1\").css({\"display\": \"none\"});
						$(\"#brand2\").css({\"display\": \"\"});
						$(\"#brand3\").css({\"display\": \"none\"});
					}else if( '3' == $(\"select[name='cbrand'] option:selected\").val() ){
						$(\"#brand1\").css({\"display\": \"none\"});
						$(\"#brand2\").css({\"display\": \"none\"});
						$(\"#brand3\").css({\"display\": \"\"});
					}else{
						$(\"#brand1\").css({\"display\": \"none\"});
						$(\"#brand2\").css({\"display\": \"none\"});
						$(\"#brand3\").css({\"display\": \"none\"});
					}
				});
		
				if( '1' == $(\"input[name='howpay']:checked\").val() ){
					$(\"#cbrand_" . $pm . "\").css({\"display\": \"none\"});
					$(\"#div_" . $pm . "\").css({\"display\": \"none\"});
				}else{
					$(\"#cbrand_" . $pm . "\").css({\"display\": \"\"});
					$(\"#div_" . $pm . "\").css({\"display\": \"\"});
				}				

				if( '1' == $(\"select[name='cbrand'] option:selected\").val() ){
					$(\"#brand1\").css({\"display\": \"\"});
					$(\"#brand2\").css({\"display\": \"none\"});
					$(\"#brand3\").css({\"display\": \"none\"});
				}else if( '2' == $(\"select[name='cbrand'] option:selected\").val() ){
					$(\"#brand1\").css({\"display\": \"none\"});
					$(\"#brand2\").css({\"display\": \"\"});
					$(\"#brand3\").css({\"display\": \"none\"});
				}else if( '3' == $(\"select[name='cbrand'] option:selected\").val() ){
					$(\"#brand1\").css({\"display\": \"none\"});
					$(\"#brand2\").css({\"display\": \"none\"});
					$(\"#brand3\").css({\"display\": \"\"});
				}else{
					$(\"#brand1\").css({\"display\": \"none\"});
					$(\"#brand2\").css({\"display\": \"none\"});
					$(\"#brand3\").css({\"display\": \"none\"});
				}

				\n";
			}
			break;
		
	}	
}

$html .= "
		ch_pay = $(\"input[name='order\\[payment_name\\]']:checked\").val();
		if( uscesPaymod[ch_pay] != '' ){
			$(\"#\" + uscesPaymod[ch_pay]).css({\"display\": \"table\"});
		}";
	
$html .= "
	});
</script>
";
?>
