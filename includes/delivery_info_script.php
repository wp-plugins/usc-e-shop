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
	var add_shipping = new Array(0, 0, 2, 3, 5, 6, 7, 14, 21, 0);//発送日目安

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
$default_deli = array_values($this->get_available_delivery_method());
if($usces_entries['order']['delivery_method'] === NULL){
	//$default_deli = $this->get_available_delivery_method();
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
$cart = $this->cart->get_cart();
for($i = 0; $i < count($cart); $i++) {
	$cart_row = $cart[$i];
	$post_id = $cart_row['post_id'];
	$itemShipping = $this->getItemShipping($post_id);
	if($shipping < $itemShipping) $shipping = $itemShipping;
}
$html .= 'var shipping = '.$shipping.';';
//配送業務締時間
$html .= 'var delivery_time_limit_hour = "'.$this->options['delivery_time_limit']['hour'].'";';
$html .= 'var delivery_time_limit_min = "'.$this->options['delivery_time_limit']['min'].'";';
//最短宅配時間帯
$html .= 'var shortest_delivery_time = '.(int)$this->options['shortest_delivery_time'].';';
//配送希望日を何日後まで表示するか
$delivery_after_days = (empty($this->options['delivery_after_days'])) ? 15 : (int)$this->options['delivery_after_days'];
$html .= 'var delivery_after_days = '.$delivery_after_days.';';
//配送先県(customer)
$html .= 'var customer_pref = "'.$usces_entries['customer']['pref'].'";';
//配送先県(customer/delivery)
$delivery_pref = isset($usces_entries['delivery']['pref']) ? $usces_entries['delivery']['pref'] : $usces_entries['customer']['pref'];
$html .= 'var delivery_pref = "'.$delivery_pref.'";';
//選択可能な配送方法に設定されている配達日数
$html .= 'var delivery_days = [];';
foreach((array)$default_deli as $id) {
	$index = $this->get_delivery_method_index($id);
	if(0 <= $index) {
		$html .= 'delivery_days['.$id.'] = [];';
		$html .= 'delivery_days['.$id.'].push("'.$this->options['delivery_method'][$index]['days'].'");';
	}
}
//配達日数に設定されている県毎の日数
$prefs = $this->options['province'];
array_shift($prefs);
$delivery_days = $this->options['delivery_days'];
$html .= 'var delivery_days_value = [];';
foreach((array)$default_deli as $key => $id) {
	$index = $this->get_delivery_method_index($id);
	if(0 <= $index) {
		$days = (int)$this->options['delivery_method'][$index]['days'];
		if(0 <= $days) {
			for($i = 0; $i < count((array)$delivery_days); $i++) {
				if((int)$delivery_days[$i]['id'] == $days) {
					$html .= 'delivery_days_value['.$days.'] = [];';
					foreach((array)$prefs as $pref) {
						$html .= 'delivery_days_value['.$days.']["'.$pref.'"] = [];';
						$html .= 'delivery_days_value['.$days.']["'.$pref.'"].push("'.(int)$delivery_days[$i]['value'][$pref].'");';
					}
				}
			}
		}
	}
}
//20101208ysk end


$html .= 'selected_delivery_time = \'' . $usces_entries['order']['delivery_time'] . '\';
		var delivery_time = [];delivery_time[0] = [];';

foreach((array)$this->options['delivery_method'] as $dmid => $dm){
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

//20101208ysk start
/*
$html .= "
			var chk_pay = $(\"input[name='order\\[payment_name\\]']:checked\").val();
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
			var chk_pay = $(\"input[name='order\\[payment_name\\]']:checked\").val();
			if( uscesPaymod[chk_pay] != '' ){
				$(\"#\" + uscesPaymod[chk_pay]).css({\"display\": \"block\"});
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
			if($('#delivery_flag2').attr('checked') == true && 0 < $('#pref').attr('selectedIndex')) {
				delivery_pref = $('#pref').val();
				orderfunc.make_delivery_date(($('#delivery_method_select option:selected').val()-0));
			}
		});
		
		$('#pref').change(function() {
			if($('#delivery_flag2').attr('checked') == true && 0 < $('#pref').attr('selectedIndex')) {
				delivery_pref = $('#pref').val();
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
						var now = new Date();
						var date = new Array();
						date[\"year\"] = now.getFullYear() + \"\";
						date[\"month\"] = toDoubleDigits(now.getMonth() + 1);
						date[\"day\"] = toDoubleDigits(now.getDate());
						//配送業務締時間を超えていたら1日加算
						var hh = toDoubleDigits(now.getHours());
						var mm = toDoubleDigits(now.getMinutes());
						if(delivery_time_limit_hour+delivery_time_limit_min < hh+mm) {
							date = addDate(date[\"year\"], date[\"month\"], date[\"day\"], 1);
						}
						//発送日目安加算
						if(0 < add_shipping[shipping]) {
							date = addDate(date[\"year\"], date[\"month\"], date[\"day\"], add_shipping[shipping]);
						}
						//配達日数加算
						if(delivery_days_value[delivery_days[selected]][delivery_pref] != undefined) {
							date = addDate(date[\"year\"], date[\"month\"], date[\"day\"], delivery_days_value[delivery_days[selected]][delivery_pref]);
						}
						//最短配送時間帯メッセージ
						var date_str = date[\"year\"]+\"-\"+date[\"month\"]+\"-\"+date[\"day\"];
						switch(shortest_delivery_time) {
						case 0://指定しない 20110106ysk
							message = date_str+\"".__('からご指定できます。', 'usces')."\";
							break;
						case 1://午前着可
							message = date_str+\"".__('の午前中からご指定できます。', 'usces')."\";
							break;
						case 2://午前着不可
							message = date_str+\"".__('の午後からご指定できます。', 'usces')."\";
							break;
						}
						option += '<option value=\"0\">".__('No preference', 'usces')."</option>';
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
