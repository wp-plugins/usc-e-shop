<?php
//20101208ysk start
/*
$html .= '
<script type="text/javascript">
	var selected_delivery_method = \'\';
	var selected_delivery_time = \'\';

	jQuery(function($){
		';
*/
$sendout = usces_get_send_out_date();

if(isset($this))
	$usces = &$this;
	
//$shipping_indication = apply_filters('usces_filter_shipping_indication', $usces->options['usces_shipping_indication']);

$html .= '
<script type="text/javascript">
	//1桁の数字を0埋めで2桁にする
	var toDoubleDigits = function(num) {
		num += "";
		if(num.length === 1) num = "0".concat(num);
		return num;
	};
	var selected_delivery_method = \'\';
	var selected_delivery_date = \'\';
	var selected_delivery_time = \'\';	
	var add_shipping = new Array();//発送日目安';
//$c = '';
//foreach($shipping_indication as $value){
//	$html .= $c.$value;
//	$c = ',';
//}
$html .= '
	function addDate(year, month, day, add) {
		var date = new Date(Number(year), (Number(month) - 1), Number(day));
		var baseSec = date.getTime();
		var addSec = Number(add) * 86400000;
		var targetSec = baseSec + addSec;
		date.setTime(targetSec);

		var yy = date.getFullYear() + "";
		var mm = toDoubleDigits(date.getMonth() + 1);
		var dd = toDoubleDigits(date.getDate());

		var newdate = new Array();
		newdate["year"] = yy;
		newdate["month"] = mm;
		newdate["day"] = dd;
		return(newdate);
	}

	jQuery(function($){
		';

//選択可能な配送方法
$default_deli = array_values($usces->get_available_delivery_method());
if($usces_entries['order']['delivery_method'] === NULL){
	//$default_deli = $usces->get_available_delivery_method();
	//$html .= 'selected_delivery_method = \'' . $default_deli[0] . '\';';
	$selected_delivery_method = $default_deli[0];
}else{
	//$html .= 'selected_delivery_method = \'' . $usces_entries['order']['delivery_method'] . '\';';
	$selected_delivery_method = $usces_entries['order']['delivery_method'];
}
$html .= 'selected_delivery_method = \'' . $selected_delivery_method . '\';';
if(isset($usces_entries['order']['delivery_date'])) {
	$html .= 'selected_delivery_date = \''.$usces_entries['order']['delivery_date'].'\';';
}

//カートに入っている商品の発送日目安
$shipping = 0;
$cart = $usces->cart->get_cart();
for($i = 0; $i < count($cart); $i++) {
	$cart_row = $cart[$i];
	$post_id = $cart_row['post_id'];
	$itemShipping = $usces->getItemShipping($post_id);
//20110606ysk start
	if($itemShipping == 0 or $itemShipping == 9) {
		$shipping = 0;
		break;
	}
	if($shipping < $itemShipping) $shipping = $itemShipping;
//20110606ysk end
}
$html .= 'var shipping = '.$shipping.';';
//配送業務締時間
//20110422ysk start
$hour = (!empty($usces->options['delivery_time_limit']['hour'])) ? $usces->options['delivery_time_limit']['hour'] : '00';
$min = (!empty($usces->options['delivery_time_limit']['min'])) ? $usces->options['delivery_time_limit']['min'] : '00';
$html .= 'var delivery_time_limit_hour = "'.$hour.'";';
$html .= 'var delivery_time_limit_min = "'.$min.'";';
//20110422ysk end
//最短宅配時間帯
$html .= 'var shortest_delivery_time = '.(int)$usces->options['shortest_delivery_time'].';';
//配送希望日を何日後まで表示するか
$delivery_after_days = (!empty($usces->options['delivery_after_days'])) ? (int)$usces->options['delivery_after_days'] : 15;
$html .= 'var delivery_after_days = '.$delivery_after_days.';';
//配送先県(customer)
$html .= 'var customer_pref = "'.$usces_entries['customer']['pref'].'";';
//配送先県(customer/delivery)
$delivery_pref = isset($usces_entries['delivery']['pref']) ? $usces_entries['delivery']['pref'] : $usces_entries['customer']['pref'];
$html .= 'var delivery_pref = "'.$delivery_pref.'";';
//選択可能な配送方法に設定されている配達日数
$html .= 'var delivery_days = [];';
foreach((array)$default_deli as $id) {
	$index = $usces->get_delivery_method_index($id);
	if(0 <= $index) {
		$html .= 'delivery_days['.$id.'] = [];';
		$html .= 'delivery_days['.$id.'].push("'.$usces->options['delivery_method'][$index]['days'].'");';
	}
}
//配達日数に設定されている県毎の日数
//20110317ysk start
//$prefs = $usces->options['province'];
//array_shift($prefs);
//global $usces_states;
$target_market = ( isset($usces->options['system']['target_market']) && !empty($usces->options['system']['target_market']) ) ? $usces->options['system']['target_market'] : usces_get_local_target_market();
foreach((array)$target_market as $tm) {
//20110331ysk start
	//$prefs[$tm] = $usces_states[$tm];
	$prefs[$tm] = get_usces_states($tm);
//20110331ysk end
	array_shift($prefs[$tm]);
}
//20110317ysk end
$delivery_days = $usces->options['delivery_days'];
$html .= 'var delivery_days_value = [];';
foreach((array)$default_deli as $key => $id) {
	$index = $usces->get_delivery_method_index($id);
	if(0 <= $index) {
		$days = (int)$usces->options['delivery_method'][$index]['days'];
		if(0 <= $days) {
			for($i = 0; $i < count((array)$delivery_days); $i++) {
				if((int)$delivery_days[$i]['id'] == $days) {
					$html .= 'delivery_days_value['.$days.'] = [];';
//20110317ysk start
					$country = $usces->options['delivery_days'][$i]['country'];
					//foreach((array)$prefs as $pref) {
					foreach((array)$prefs[$country] as $pref) {
//20110317ysk end
						$html .= 'delivery_days_value['.$days.']["'.$pref.'"] = [];';
						$html .= 'delivery_days_value['.$days.']["'.$pref.'"].push("'.(int)$delivery_days[$i]['value'][$pref].'");';
					}
				}
			}
		}
	}
}
//20101208ysk end
//20110131ysk start
$business_days = 0;
list($yy, $mm, $dd) = getToday();
//20110228ysk start
//$business = $this->options['business_days'][$yy][$mm][$dd];
$business = (isset($usces->options['business_days'][$yy][$mm][$dd])) ? $usces->options['business_days'][$yy][$mm][$dd] : 1;
//20110228ysk end
while($business != 1) {
	$business_days++;
	list($yy, $mm, $dd) = getNextDay($yy, $mm, $dd);
	$business = $usces->options['business_days'][$yy][$mm][$dd];
}
$html .= 'var business_days = '.$business_days.';';
//20110131ysk end

$html .= 'selected_delivery_time = \'' . $usces_entries['order']['delivery_time'] . '\';
		var delivery_time = [];delivery_time[0] = [];';

foreach((array)$usces->options['delivery_method'] as $dmid => $dm){
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
foreach ( (array)$usces->options['payment_method'] as $array ) {
	switch( $array['settlement'] ){
		case 'acting_zeus_card':
			$paymod_base = 'zeus';
			if( 'on' == $usces->options['acting_settings'][$paymod_base]['card_activate'] 
				&& 'on' == $usces->options['acting_settings'][$paymod_base]['activate'] ){
			
				$payments_str .= "'" . $array['name'] . "': '" . $paymod_base . "', ";
				$payments_arr[] = $paymod_base;
			}
			break;
		case 'acting_zeus_conv':
			$paymod_base = 'zeus';
			if( 'on' == $usces->options['acting_settings'][$paymod_base]['conv_activate'] 
				&& 'on' == $usces->options['acting_settings'][$paymod_base]['activate'] ){
			
				$payments_str .= "'" . $array['name'] . "': '" . $paymod_base . "_conv', ";
				$payments_arr[] = $paymod_base.'_conv';
			}
			break;
		case 'acting_remise_card':
			$paymod_base = 'remise';
			if( 'on' == $usces->options['acting_settings'][$paymod_base]['card_activate'] 
				&& 'on' == $usces->options['acting_settings'][$paymod_base]['howpay'] 
				&& 'on' == $usces->options['acting_settings'][$paymod_base]['activate'] ){
			
				$payments_str .= "'" . $array['name'] . "': '" . $paymod_base . "', ";
				$payments_arr[] = $paymod_base;
			}
			break;
	}
}
$payments_str = rtrim($payments_str, ', ');

$html .= "
		var uscesPaymod = { " . $payments_str . " };

		$(\"input[name='offer\\[payment_name\\]']\").click(function() {";

foreach($payments_arr as $pm ){
	$html .= "
			$(\"#" . $pm . "\").css({\"display\": \"none\"});\n";
}

//20101208ysk start
/*
$html .= "
			var chk_pay = $(\"input[name='offer\\[payment_name\\]']:checked\").val();
			if( uscesPaymod[chk_pay] != '' ){
				$(\"#\" + uscesPaymod[chk_pay]).css({\"display\": \"block\"});
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
*/
$html .= "
			var chk_pay = $(\"input[name='offer\\[payment_name\\]']:checked\").val();
			if( uscesPaymod[chk_pay] != '' ){
				$(\"#\" + uscesPaymod[chk_pay]).css({\"display\": \"\"});
			}
		});
		
		$('#delivery_method_select').change(function() {
			orderfunc.make_delivery_date(($('#delivery_method_select option:selected').val()-0));
			orderfunc.make_delivery_time(($('#delivery_method_select option:selected').val()-0));
		});
		
		$('#delivery_flag1').click(function() {
			if(customer_pref != delivery_pref) {
				delivery_pref = customer_pref;
				orderfunc.make_delivery_date(($('#delivery_method_select option:selected').val()-0));
			}
		});
		
//20110317ysk start
		$('#delivery_flag2').click(function() {
			//if($('#delivery_flag2').attr('checked') && 0 < $('#pref').attr('selectedIndex')) {
//20110722ysk start 0000210
			//if($('#delivery_flag2').attr('checked') && 0 < $('#delivery_pref').attr('selectedIndex')) {
			if($('#delivery_flag2').attr('checked') && 0 < $('#delivery_pref').get(0).selectedIndex) {
//20110722ysk end
				//delivery_pref = $('#pref').val();
				delivery_pref = $('#delivery_pref').val();
				orderfunc.make_delivery_date(($('#delivery_method_select option:selected').val()-0));
			}
		});
		
		//$('#pref').change(function() {
		$('#delivery_pref').change(function() {
			//if($('#delivery_flag2').attr('checked') && 0 < $('#pref').attr('selectedIndex')) {
//20110722ysk start 0000210
			//if($('#delivery_flag2').attr('checked') && 0 < $('#delivery_pref').attr('selectedIndex')) {
			if($('#delivery_flag2').attr('checked') && 0 < $('#delivery_pref').get(0).selectedIndex) {
//20110722ysk end
				//delivery_pref = $('#pref').val();
				delivery_pref = $('#delivery_pref').val();
				orderfunc.make_delivery_date(($('#delivery_method_select option:selected').val()-0));
			}
		});
//20110317ysk end
		
		orderfunc = {
			make_delivery_date : function(selected) {
				var option = '';
				var message = '';
//20110606ysk start
				//if(delivery_days[selected] != undefined && 0 < delivery_days[selected].length) {
				if(delivery_days[selected] != undefined && 0 <= delivery_days[selected]) {
//20110606ysk end
					switch(shipping) {
					case 0://指定なし
					case 9://商品入荷後
						break;
					default:
						var date = new Array();
						date['year'] = '" . $sendout['sendout_date']['year'] . "';
						date['month'] = '" . $sendout['sendout_date']['month'] . "';
						date['day'] = '" . $sendout['sendout_date']['day'] . "';
						//配達日数加算
						if(delivery_days_value[delivery_days[selected]] != undefined) {
							if(delivery_days_value[delivery_days[selected]][delivery_pref] != undefined) {
								date = addDate(date[\"year\"], date[\"month\"], date[\"day\"], delivery_days_value[delivery_days[selected]][delivery_pref]);
							}
						}
						//最短配送時間帯メッセージ
						var date_str = date[\"year\"]+\"-\"+date[\"month\"]+\"-\"+date[\"day\"];
						switch(shortest_delivery_time) {
						case 0://指定しない 20110106ysk
							message = " . __("'最短 ' + date_str + ' からご指定いただけます。'", 'usces') . ";
							break;
						case 1://午前着可
							message = " . __("'最短 ' + date_str + ' の午前中からご指定いただけます。'", 'usces') . ";
							break;
						case 2://午前着不可
							message = " . __("'最短 ' + date_str + ' の午後からご指定いただけます。'", 'usces') . ";
							break;
						}
//20110126ysk start
						//option += '<option value=\"0\">".__('No preference', 'usces')."</option>';
						option += '<option value=\"".__('No preference', 'usces')."\">".__('No preference', 'usces')."</option>';
//20110126ysk end
						for(var i = 0; i < delivery_after_days; i++) {
							date_str = date[\"year\"]+\"-\"+date[\"month\"]+\"-\"+date[\"day\"];
							if(date_str == selected_delivery_date) {
								option += '<option value=\"' + date_str + '\" selected>' + date_str + '</option>';
							} else {
								option += '<option value=\"' + date_str + '\">' + date_str + '</option>';
							}
							date = addDate(date[\"year\"], date[\"month\"], date[\"day\"], 1);
						}
						break;
					}
				}
				if(option == '') {
					option = '<option value=\"".__('There is not a choice.', 'usces')."\">' + '".__('There is not a choice.', 'usces')."' + '</option>';
				}
				$(\"#delivery_date_select\").html(option);
				$(\"#delivery_time_limit_message\").html(message);
			},
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
//20101208ysk end

if($usces_entries['delivery']['delivery_flag'] == 0) {
	$html .= "
		$(\"#delivery_table\").css({display: \"none\"});\n";
}

//20101208ysk start
$html .= "
		orderfunc.make_delivery_date(selected_delivery_method);\n";
//20101208ysk end

$html .= "
		orderfunc.make_delivery_time(selected_delivery_method);\n";

foreach($payments_arr as $pn => $pm ){
	$html .= "
		$(\"#" . $pm . "\").css({\"display\": \"none\"});\n";
	
	switch( $pm ){
		case 'zeus':
			if('on' == $usces->options['acting_settings'][$pm]['howpay']){
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
		ch_pay = $(\"input[name='offer\\[payment_name\\]']:checked\").val();
		if( uscesPaymod[ch_pay] != '' ){
			$(\"#\" + uscesPaymod[ch_pay]).css({\"display\": \"\"});
		}";
	
$html .= "
	});
</script>
";
?>
