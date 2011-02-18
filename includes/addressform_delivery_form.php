<?php
$applyform = usces_get_apply_addressform($this->options['system']['addressform']);
$formtag = '';
switch ($applyform){

	case 'JP':
		//20100818ysk start
		$html .= usces_custom_field_input($usces_entries, 'delivery', 'name_pre', 'return');
		//20100818ysk end
		$html .= '<tr class="inp1">
		<th width="127" scope="row"><em>*</em>'.__('Full name', 'usces').'</th>
		<td width="257">'.__('Familly name', 'usces').'<input name="delivery[name1]" id="name1" type="text" value="' . esc_attr($usces_entries['delivery']['name1']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" /></td>
		<td width="257">'.__('Given name', 'usces').'<input name="delivery[name2]" id="name2" type="text" value="' . esc_attr($usces_entries['delivery']['name2']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" /></td>
		</tr>';
		$html .= '<tr class="inp1">
		<th scope="row">'.__('furigana', 'usces').'</th>
		<td>'.__('Familly name', 'usces').'<input name="delivery[name3]" id="name3" type="text" value="' . esc_attr($usces_entries['delivery']['name3']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" /></td>
		<td>'.__('Given name', 'usces').'<input name="delivery[name4]" id="name4" type="text" value="' . esc_attr($usces_entries['delivery']['name4']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" /></td>
		</tr>';
		//20100818ysk start
		$html .= usces_custom_field_input($usces_entries, 'delivery', 'name_after', 'return');
		//20100818ysk end
		$html .= '<tr>
		<th scope="row"><em>*</em>'.__('Zip/Postal Code', 'usces').'</th>
		<td colspan="2"><input name="delivery[zipcode]" id="zipcode" type="text" value="' . esc_attr($usces_entries['delivery']['zipcode']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />100-1000</td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>'.__('Province', 'usces').'</th>
		<td colspan="2">' . usces_the_pref( 'delivery', 'return' ) . '</td>
		</tr>
		<tr class="inp2">
		<th scope="row"><em>*</em>'.__('city', 'usces').'</th>
		<td colspan="2"><input name="delivery[address1]" id="address1" type="text" value="' . esc_attr($usces_entries['delivery']['address1']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />'.__('Kitakami Yokohama', 'usces').'</td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>'.__('numbers', 'usces').'</th>
		<td colspan="2"><input name="delivery[address2]" id="address2" type="text" value="' . esc_attr($usces_entries['delivery']['address2']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />3-24-555</td>
		</tr>
		<tr>
		<th scope="row">'.__('building name', 'usces').'</th>
		<td colspan="2"><input name="delivery[address3]" id="address3" type="text" value="' . esc_attr($usces_entries['delivery']['address3']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />'.__('tuhanbuild 4F', 'usces').'</td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>'.__('Phone number', 'usces').'</th>
		<td colspan="2"><input name="delivery[tel]" id="tel" type="text" value="' . esc_attr($usces_entries['delivery']['tel']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />1000-10-1000</td>
		</tr>
		<tr>
		<th scope="row">'.__('FAX number', 'usces').'</th>
		<td colspan="2"><input name="delivery[fax]" id="fax" type="text" value="' . esc_attr($usces_entries['delivery']['fax']) . '" onKeyDown="if (event.keyCode == 13) {return false;}" />1000-10-1000</td>
		</tr>';
		//20100818ysk start
		$html .= usces_custom_field_input($usces_entries, 'delivery', 'fax_after', 'return');
		//20100818ysk end
		break;
		
	case 'US':
		//20100818ysk start
		$formtag .= usces_custom_field_input($usces_entries, 'delivery', 'name_pre', 'return');
		//20100818ysk end
		$formtag .= '<tr class="inp1">
		<th scope="row"><em>*</em>' . __('Full name', 'usces') . '</th>
		<td>' . __('Given name', 'usces') . '<input name="delivery[name2]" id="name2" type="text" value="' . esc_attr($usces_entries['delivery']['name2']) . '" /></td>
		<td>' . __('Familly name', 'usces') . '<input name="delivery[name1]" id="name1" type="text" value="' . esc_attr($usces_entries['delivery']['name1']) . '" /></td>
		</tr>';
		//20100818ysk start
		$formtag .= usces_custom_field_input($usces_entries, 'delivery', 'name_after', 'return');
		//20100818ysk end
		$formtag .= '
		<tr>
		<th scope="row"><em>*</em>' . __('Address Line1', 'usces') . '</th>
		<td colspan="2">' . __('Street address', 'usces') . '<br /><input name="delivery[address2]" id="address2" type="text" value="' . esc_attr($usces_entries['delivery']['address2']) . '" /></td>
		</tr>
		<tr>
		<th scope="row">' . __('Address Line2', 'usces') . '</th>
		<td colspan="2">' . __('Apartment, building, etc.', 'usces') . '<br /><input name="delivery[address3]" id="address3" type="text" value="' . esc_attr($usces_entries['delivery']['address3']) . '" /></td>
		</tr>
		<tr class="inp2">
		<th scope="row"><em>*</em>' . __('city', 'usces') . '</th>
		<td colspan="2"><input name="delivery[address1]" id="address1" type="text" value="' . esc_attr($usces_entries['delivery']['address1']) . '" /></td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>' . __('State', 'usces') . '</th>
		<td colspan="2">' . usces_the_pref( 'delivery', 'return' ) . '</td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>' . __('Zip/Postal Code', 'usces') . '</th>
		<td colspan="2"><input name="delivery[zipcode]" id="zipcode" type="text" value="' . esc_attr($usces_entries['delivery']['zipcode']) . '" /></td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>' . __('Phone number', 'usces') . '</th>
		<td colspan="2"><input name="delivery[tel]" id="tel" type="text" value="' . esc_attr($usces_entries['delivery']['tel']) . '" /></td>
		</tr>
		<tr>
		<th scope="row">' . __('FAX number', 'usces') . '</th>
		<td colspan="2"><input name="delivery[fax]" id="fax" type="text" value="' . esc_attr($usces_entries['delivery']['fax']) . '" /></td>
		</tr>';
		//20100818ysk start
		$formtag .= usces_custom_field_input($usces_entries, 'delivery', 'fax_after', 'return');
		//20100818ysk end
		break;
}
$html .= apply_filters('usces_filter_apply_delivery_form', $formtag, $usces_entries);
?>
