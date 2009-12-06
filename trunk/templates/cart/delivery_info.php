<?php 
$usces_entries = $this->cart->get_entry();
$html = '';

if( EX_DLSELLER !== true ){
	if($usces_entries['customer']['delivery_flag'] == 0) {
		
		$html .= '<script type="text/javascript" src="' . USCES_WP_PLUGIN_URL . '/usc-e-shop/js/jquery/jquery-1.3.2.min.js"></script>
			<script type="text/javascript">
			var selected_delivery_method = \'\';
			var selected_delivery_time = \'\';
		
			jQuery(function($){';
		if($usces_entries['order']['delivery_method'] === NULL){
			$default_deli = $this->get_available_delivery_method();
			$html .= 'selected_delivery_method = \'' . $default_deli[0] . '\';';
		}else{
			$html .= 'selected_delivery_method = \'' . $usces_entries['order']['delivery_method'] . '\';';
		}
		$html .= 'selected_delivery_time = \'' . $usces_entries['order']['delivery_time'] . '\';
			var delivery_time = [];';
		foreach($this->options['delivery_method'] as $dmid => $dm){
			$lines = split("\n", $dm['time']);
			$html .= 'delivery_time[' . $dm['id'] . '] = [];';
			foreach((array)$lines as $line){
				if(trim($line) != ''){
					$html .= 'delivery_time[' . $dm['id'] . '].push("' . trim($line) . '");';
				}
			}
		}
		$html .= "
		$('#delivery_method_select').change(function() {
			orderfunc.make_delivery_time(($('#delivery_method_select option:selected').val()-0));
			});
			
			orderfunc = {
				make_delivery_time : function(selected) {
					var option = '';
					for(var i=0; i<delivery_time[selected].length; i++){
						if( delivery_time[selected][i] == selected_delivery_time ) {
							option += '<option value=\"' + delivery_time[selected][i] + '\" selected=\"selected\">' + delivery_time[selected][i] + '</option>';
						}else{
							option += '<option value=\"' + delivery_time[selected][i] + '\">' + delivery_time[selected][i] + '</option>';
						}
					}
					$(\"#delivery_time_select\").html(option);
				}
			};
		});
		
		$(document).ready(function(){
			$(\"#delivery_table\").css({display: \"none\"});
			orderfunc.make_delivery_time(selected_delivery_method);
		});
		</script>";
	}
}
$html .= '<div id="delivery-info">
	
	<div class="usccart_navi">
	<ol class="ucart">
	<li class="ucart">' . __('1.Cart','usces') . '</li>
	<li class="ucart">' . __('2.Customer Info','usces') . '</li>
	<li class="ucart usccart_delivery">' . __('3.Deli. & Pay.','usces') . '</li>
	<li class="ucart">' . __('4.Confirm','usces') . '</li>
	</ol>
	</div>';

$html .= '<div class="header_explanation">';
$header = '';
$html .= apply_filters('usces_filter_delivery_page_header', $header);
$html .= '</div>';
	
$html .= '<div class="error_message">' . $this->error_message . '</div>';

$html .= '<form action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">';

if( EX_DLSELLER !== true ){
	$html .= '<table class="customer_form">
		<tr>
		<th rowspan="2" scope="row">発送先</th>
		<td><input name="customer[delivery_flag]" type="radio" id="delivery_flag1" onclick="document.getElementById(\'delivery_table\').style.display = \'none\';" value="0"';
	if($usces_entries['customer']['delivery_flag'] == 0) {
		$html .= ' checked';
	}
	$html .= ' /> <label for="delivery_flag1">お客様情報と同じ</label></td>
		</tr>
		<tr>
		<td><input name="customer[delivery_flag]" id="delivery_flag2" onclick="document.getElementById(\'delivery_table\').style.display = \'inline\'" type="radio" value="1"';
	if($usces_entries['customer']['delivery_flag'] == 1) {
		$html .= ' checked';
	}
	$html .= '/> <label for="delivery_flag2">別の発送先を指定する</label></td>
		</tr>
		</table>
		<table class="customer_form" id="delivery_table">
		<tr class="inp1">
		<th width="127" scope="row"><em>*</em>お名前</th>
		<td width="257">姓<input name="delivery[name1]" id="name1" type="text" value="' . $usces_entries['delivery']['name1'] . '" /></td>
		<td width="257">名<input name="delivery[name2]" id="name2" type="text" value="' . $usces_entries['delivery']['name2'] . '" /></td>
		</tr>
		<tr class="inp1">
		<th scope="row"><em>*</em>フリガナ</th>
		<td>姓<input name="delivery[name3]" id="name3" type="text" value="' . $usces_entries['delivery']['name3'] . '" /></td>
		<td>名<input name="delivery[name4]" id="name4" type="text" value="' . $usces_entries['delivery']['name4'] . '" /></td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>郵便番号</th>
		<td colspan="2"><input name="delivery[zipcode]" id="zipcode" type="text" value="' . $usces_entries['delivery']['zipcode'] . '" />例）100-1000</td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>都道府県</th>
		<td colspan="2">' . usces_the_pref( 'delivery', 'return' ) . '</td>
		</tr>
		<tr class="inp2">
		<th scope="row"><em>*</em>市区郡町村</th>
		<td colspan="2"><input name="delivery[address1]" id="address1" type="text" value="' . $usces_entries['delivery']['address1'] . '" />例）横浜市上北町</td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>番地</th>
		<td colspan="2"><input name="delivery[address2]" id="address2" type="text" value="' . $usces_entries['delivery']['address2'] . '" />例）3-24-555</td>
		</tr>
		<tr>
		<th scope="row">マンション･ビル名</th>
		<td colspan="2"><input name="delivery[address3]" id="address3" type="text" value="' . $usces_entries['delivery']['address3'] . '" />例）通販ビル4F</td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>電話番号</th>
		<td colspan="2"><input name="delivery[tel]" id="tel" type="text" value="' . $usces_entries['delivery']['tel'] . '" />例）1000-10-1000</td>
		</tr>
		<tr>
		<th scope="row">FAX番号</th>
		<td colspan="2"><input name="delivery[fax]" id="fax" type="text" value="' . $usces_entries['delivery']['fax'] . '" />例）1000-10-1000</td>
		</tr>
		</table>';
}
$html .= '<table class="customer_form" id="time">';
if( EX_DLSELLER !== true ){
	$html .= '<tr>
		<th scope="row">配送方法</th>
		<td colspan="2">' . usces_the_delivery_method( $usces_entries['order']['delivery_method'], 'return' ) . '</td>
		</tr>
		<tr>
		<th scope="row">配送希望時間帯</th>
		<td colspan="2">' . usces_the_delivery_time( $usces_entries['order']['delivery_time'], 'return' ) . '</td>
		</tr>';
}
$html .= '<tr>
	<th scope="row"><em>*</em>お支払方法</th>
	<td colspan="2">' . usces_the_payment_method( $usces_entries['order']['payment_name'], 'return' ) . '</td>
	</tr>
	<tr>
	<th scope="row">備考</th>
	<td colspan="2"><textarea name="order[note]" cols="" rows="" id="note">' . $usces_entries['order']['note'] . '</textarea></td>
	</tr>
	</table>
	
	<div class="send"><input name="order[cus_id]" type="hidden" value="' . $this->cus_id . '" />		
	<input name="backCustomer" type="submit" value="　　戻　る　　" />&nbsp;&nbsp;
	<input name="confirm" type="submit" value="　　次　へ　　" /></div>
	</form>';

$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_delivery_page_footer', $footer);
$html .= '</div>';

$html .= '</div>';
?>
