<?php
$sendout = usces_get_send_out_date();

if(isset($this))
	$usces = &$this;

$html = '
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
	var add_shipping = new Array();//発送日目安

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

	jQuery(function($){';

//選択可能な配送方法
$default_deli = array_values(apply_filters('usces_filter_get_available_delivery_method', $usces->get_available_delivery_method()));
if( !isset($usces_entries['order']['delivery_method']) || '' == $usces_entries['order']['delivery_method'] ){
	$selected_delivery_method = $default_deli[0];
}else{
	$selected_delivery_method = $usces_entries['order']['delivery_method'];
}
$html .= '
		selected_delivery_method = \'' . $selected_delivery_method . '\';';
if(isset($usces_entries['order']['delivery_date'])) {
	$html .= '
		selected_delivery_date = \''.$usces_entries['order']['delivery_date'].'\';';
}

//カートに入っている商品の発送日目安
$shipping = 0;
$cart = $usces->cart->get_cart();
for($i = 0; $i < count($cart); $i++) {
	$cart_row = $cart[$i];
	$post_id = $cart_row['post_id'];
	$itemShipping = $usces->getItemShipping($post_id);
	if($itemShipping == 0 or $itemShipping == 9) {
		$shipping = 0;
		break;
	}
	if($shipping < $itemShipping) $shipping = $itemShipping;
}
$html .= '
		var shipping = '.$shipping.';';
//配送業務締時間
$hour = (!empty($usces->options['delivery_time_limit']['hour'])) ? $usces->options['delivery_time_limit']['hour'] : '00';
$min = (!empty($usces->options['delivery_time_limit']['min'])) ? $usces->options['delivery_time_limit']['min'] : '00';
$html .= '
		var delivery_time_limit_hour = "'.$hour.'";';
$html .= '
		var delivery_time_limit_min = "'.$min.'";';
//最短宅配時間帯
$html .= '
		var shortest_delivery_time = '.(int)$usces->options['shortest_delivery_time'].';';
//配送希望日を何日後まで表示するか
$delivery_after_days = (!empty($usces->options['delivery_after_days'])) ? (int)$usces->options['delivery_after_days'] : 15;
$html .= '
		var delivery_after_days = '.$delivery_after_days.';';
//配送先県(customer)
$html .= '
		var customer_pref = "'.esc_js($usces_entries['customer']['pref']).'";';
//配送先県(customer/delivery)
$delivery_pref = (isset($usces_entries['delivery']['pref']) && !empty($usces_entries['delivery']['pref'])) ? $usces_entries['delivery']['pref'] : $usces_entries['customer']['pref'];
$html .= '
		var delivery_pref = "'.esc_js($delivery_pref).'";';
$delivery_country = (isset($usces_entries['delivery']['country']) && !empty($usces_entries['delivery']['country'])) ? $usces_entries['delivery']['country'] : $usces_entries['customer']['country'];
$html .= '
		var delivery_country = "'.esc_js($delivery_country).'";';
//選択可能な配送方法に設定されている配達日数
$html_days = 'var delivery_days = [];';
foreach((array)$default_deli as $id) {
	$index = $usces->get_delivery_method_index($id);
	if(0 <= $index) {
		$html_days .= 'delivery_days['.$id.'] = [];';
		$html_days .= 'delivery_days['.$id.'].push("'.$usces->options['delivery_method'][$index]['days'].'");';
	}
}
//配達日数に設定されている県毎の日数
$target_market = ( isset($usces->options['system']['target_market']) && !empty($usces->options['system']['target_market']) ) ? $usces->options['system']['target_market'] : usces_get_local_target_market();
foreach((array)$target_market as $tm) {
	$prefs[$tm] = get_usces_states($tm);
	array_shift($prefs[$tm]);
}
$delivery_days = $usces->options['delivery_days'];
$html_days .= 'var delivery_days_value = [];';
foreach((array)$default_deli as $key => $id) {
	$index = $usces->get_delivery_method_index($id);
	if(0 <= $index) {
		$days = (int)$usces->options['delivery_method'][$index]['days'];
		if(0 <= $days) {
			for($i = 0; $i < count((array)$delivery_days); $i++) {
				if((int)$delivery_days[$i]['id'] == $days) {
					$html_days .= 'delivery_days_value['.$days.'] = [];';
					foreach( (array)$target_market as $tm ) {
						$html_days .= 'delivery_days_value['.$days.']["'.$tm.'"] = [];';
						foreach( (array)$prefs[$tm] as $pref) {
							$pref = esc_js($pref);
							$html_days .= 'delivery_days_value['.$days.']["'.$tm.'"]["'.$pref.'"] = [];';
							if( isset($delivery_days[$i][$tm][$pref]) )
							$html_days .= 'delivery_days_value['.$days.']["'.$tm.'"]["'.$pref.'"].push("'.(int)$delivery_days[$i][$tm][$pref].'");';
						}
					}
				}
			}
		}
	}
}
$html .= apply_filters('usces_filter_delivery_days_value', $html_days, $cart, $default_deli, $prefs);
$business_days = 0;
list($yy, $mm, $dd) = getToday();
$business = (isset($usces->options['business_days'][$yy][$mm][$dd])) ? $usces->options['business_days'][$yy][$mm][$dd] : 1;
while($business != 1) {
	$business_days++;
	list($yy, $mm, $dd) = getNextDay($yy, $mm, $dd);
	$business = $usces->options['business_days'][$yy][$mm][$dd];
}
$html .= 'var business_days = '.$business_days.';';

$html .= 'selected_delivery_time = \'' . esc_js($usces_entries['order']['delivery_time']) . '\';
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
$payments = usces_get_system_option( 'usces_payment_method', 'sort' );
$payments = apply_filters( 'usces_filter_available_payment_method', $payments );
foreach ( (array)$payments as $array ) {
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

$cart_delivery_script = "
			var chk_pay = $(\"input[name='offer\\[payment_name\\]']:checked\").val();
			if( uscesPaymod[chk_pay] != '' ){
				$(\"#\" + uscesPaymod[chk_pay]).css({\"display\": \"table\"});
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
		
		$('#delivery_flag2').click(function() {
			if($('#delivery_flag2').attr('checked') && undefined != $('#delivery_pref').get(0) && 0 < $('#delivery_pref').get(0).selectedIndex) {
				delivery_pref = $('#delivery_pref').val();
				orderfunc.make_delivery_date(($('#delivery_method_select option:selected').val()-0));
			}
		});
		
		$('#delivery_pref').change(function() {
			if($('#delivery_flag2').attr('checked') && undefined != $('#delivery_pref').get(0) && 0 < $('#delivery_pref').get(0).selectedIndex) {
				delivery_pref = $('#delivery_pref').val();
				orderfunc.make_delivery_date(($('#delivery_method_select option:selected').val()-0));
			}
		});
		orderfunc = {
			make_delivery_date : function(selected) {
				var option = '';
				var message = '';
				if(delivery_days[selected] != undefined && 0 <= delivery_days[selected]) {
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
							if(delivery_days_value[delivery_days[selected]][delivery_country][delivery_pref] != undefined) {
								date = addDate(date[\"year\"], date[\"month\"], date[\"day\"], delivery_days_value[delivery_days[selected]][delivery_country][delivery_pref]);
							}
						}
						//最短配送時間帯メッセージ
						var date_str = date[\"year\"]+\"-\"+date[\"month\"]+\"-\"+date[\"day\"];
						switch(shortest_delivery_time) {
						case 0://指定しない 20110106ysk -> 20111128ysk DEL
							//message = " . __("'最短 ' + date_str + ' からご指定いただけます。'", 'usces') . ";
							break;
						case 1://午前着可
							message = " . __("'最短 ' + date_str + ' の午前中からご指定いただけます。'", 'usces') . ";
							break;
						case 2://午前着不可
							message = " . __("'最短 ' + date_str + ' の午後からご指定いただけます。'", 'usces') . ";
							break;
						}";
$delivery_after_days_script = "
						option += '<option value=\"".__('No preference', 'usces')."\">".__('No preference', 'usces')."</option>';
						for(var i = 0; i < delivery_after_days; i++) {
							date_str = date[\"year\"]+\"-\"+date[\"month\"]+\"-\"+date[\"day\"];
							if(date_str == selected_delivery_date) {
								option += '<option value=\"' + date_str + '\" selected>' + date_str + '</option>';
							} else {
								option += '<option value=\"' + date_str + '\">' + date_str + '</option>';
							}
							date = addDate(date[\"year\"], date[\"month\"], date[\"day\"], 1);
						}";
$cart_delivery_script .= apply_filters( 'usces_delivery_after_days_script', $delivery_after_days_script );
$cart_delivery_script .= "
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

if($usces_entries['delivery']['delivery_flag'] != 1) {
	$cart_delivery_script .= "
		$(\"#delivery_table\").css({display: \"none\"});\n";
}

$cart_delivery_script .= "
		orderfunc.make_delivery_date(selected_delivery_method);\n";

$cart_delivery_script .= "
		orderfunc.make_delivery_time(selected_delivery_method);\n";

$html .= apply_filters( 'usces_filter_cart_delivery_script', $cart_delivery_script, $usces_entries, $sendout );

foreach($payments_arr as $pn => $pm ){
	$html .= "
		$(\"#" . $pm . "\").css({\"display\": \"none\"});\n";

	switch( $pm ){
		case 'zeus':
			if('on' == $usces->options['acting_settings'][$pm]['howpay']){
				$html .= "
				$(\"input[name='offer\[howpay\]']\").change(function() {
					if( '' != $(\"select[name='offer\[cbrand\]'] option:selected\").val() ){
						$(\"#div_" . $pm . "\").css({\"display\": \"\"});
					}
					if( '1' == $(\"input[name='offer\[howpay\]']:checked\").val() ){
						$(\"#cbrand_" . $pm . "\").css({\"display\": \"none\"});
						$(\"#div_" . $pm . "\").css({\"display\": \"none\"});
					}else{
						$(\"#cbrand_" . $pm . "\").css({\"display\": \"\"});
					}
				});

				$(\"select[name='offer\[cbrand\]']\").change(function() {
					$(\"#div_" . $pm . "\").css({\"display\": \"\"});
					if( '1' == $(\"select[name='offer\[cbrand\]'] option:selected\").val() ){
						$(\"#brand1\").css({\"display\": \"\"});
						$(\"#brand2\").css({\"display\": \"none\"});
						$(\"#brand3\").css({\"display\": \"none\"});
					}else if( '2' == $(\"select[name='offer\[cbrand\]'] option:selected\").val() ){
						$(\"#brand1\").css({\"display\": \"none\"});
						$(\"#brand2\").css({\"display\": \"\"});
						$(\"#brand3\").css({\"display\": \"none\"});
					}else if( '3' == $(\"select[name='offer\[cbrand\]'] option:selected\").val() ){
						$(\"#brand1\").css({\"display\": \"none\"});
						$(\"#brand2\").css({\"display\": \"none\"});
						$(\"#brand3\").css({\"display\": \"\"});
					}else{
						$(\"#brand1\").css({\"display\": \"none\"});
						$(\"#brand2\").css({\"display\": \"none\"});
						$(\"#brand3\").css({\"display\": \"none\"});
					}
				});

				if( '1' == $(\"input[name='offer\[howpay\]']:checked\").val() ){
					$(\"#cbrand_" . $pm . "\").css({\"display\": \"none\"});
					$(\"#div_" . $pm . "\").css({\"display\": \"none\"});
				}else{
					$(\"#cbrand_" . $pm . "\").css({\"display\": \"\"});
					$(\"#div_" . $pm . "\").css({\"display\": \"\"});
				}

				if( '1' == $(\"select[name='offer\[cbrand\]'] option:selected\").val() ){
					$(\"#brand1\").css({\"display\": \"\"});
					$(\"#brand2\").css({\"display\": \"none\"});
					$(\"#brand3\").css({\"display\": \"none\"});
				}else if( '2' == $(\"select[name='offer\[cbrand\]'] option:selected\").val() ){
					$(\"#brand1\").css({\"display\": \"none\"});
					$(\"#brand2\").css({\"display\": \"\"});
					$(\"#brand3\").css({\"display\": \"none\"});
				}else if( '3' == $(\"select[name='offer\[cbrand\]'] option:selected\").val() ){
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
