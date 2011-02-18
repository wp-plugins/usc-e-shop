<?php
$applyform = usces_get_apply_addressform($this->options['system']['addressform']);
$formtag = '';
switch ($applyform){

	case 'JP':
		//20100818ysk start
		$formtag .= usces_custom_field_input($usces_entries, 'customer', 'name_pre', 'return');
		//20100818ysk end
		$formtag .= '<tr class="inp1">
		<th scope="row"><em>*</em>'.__('Full name', 'usces').'</th>
		<td>'.__('Familly name', 'usces').'<input name="customer[name1]" id="name1" type="text" value="' . esc_attr($usces_entries['customer']['name1']) . '" /></td>
		<td>'.__('Given name', 'usces').'<input name="customer[name2]" id="name2" type="text" value="' . esc_attr($usces_entries['customer']['name2']) . '" /></td>
		</tr>';
		$formtag .= '<tr class="inp1">
		<th scope="row">'.__('furigana', 'usces').'</th>
		<td>'.__('Familly name', 'usces').'<input name="customer[name3]" id="name3" type="text" value="' . esc_attr($usces_entries['customer']['name3']) . '" /></td>
		<td>'.__('Given name', 'usces').'<input name="customer[name4]" id="name4" type="text" value="' . esc_attr($usces_entries['customer']['name4']) . '" /></td>
		</tr>';
		//20100818ysk start
		$formtag .= usces_custom_field_input($usces_entries, 'customer', 'name_after', 'return');
		//20100818ysk end
		$formtag .= '<tr>
		<th scope="row"><em>*</em>'.__('Zip/Postal Code', 'usces').'</th>
		<td colspan="2"><input name="customer[zipcode]" id="zipcode" type="text" value="' . esc_attr($usces_entries['customer']['zipcode']) . '" />'.__('Example)', 'usces').'100-1000</td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>'.__('Province', 'usces').'</th>
		<td colspan="2">' . usces_the_pref( 'customer', 'return' ) . '</td>
		</tr>
		<tr class="inp2">
		<th scope="row"><em>*</em>'.__('city', 'usces').'</th>
		<td colspan="2"><input name="customer[address1]" id="address1" type="text" value="' . esc_attr($usces_entries['customer']['address1']) . '" />'.__('Example)', 'usces').__('Kitakami Yokohama', 'usces').'</td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>'.__('numbers', 'usces').'</th>
		<td colspan="2"><input name="customer[address2]" id="address2" type="text" value="' . esc_attr($usces_entries['customer']['address2']) . '" />'.__('Example)', 'usces').'3-24-555</td>
		</tr>
		<tr>
		<th scope="row">'.__('building name', 'usces').'</th>
		<td colspan="2"><input name="customer[address3]" id="address3" type="text" value="' . esc_attr($usces_entries['customer']['address3']) . '" />'.__('Example)', 'usces').__('tuhanbuild 4F', 'usces').'</td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>'.__('Phone number', 'usces').'</th>
		<td colspan="2"><input name="customer[tel]" id="tel" type="text" value="' . esc_attr($usces_entries['customer']['tel']) . '" />'.__('Example)', 'usces').'1000-10-1000</td>
		</tr>
		<tr>
		<th scope="row">'.__('FAX number', 'usces').'</th>
		<td colspan="2"><input name="customer[fax]" id="fax" type="text" value="' . esc_attr($usces_entries['customer']['fax']) . '" />'.__('Example)', 'usces').'1000-10-1000</td>
		</tr>';
		//20100818ysk start
		$formtag .= usces_custom_field_input($usces_entries, 'customer', 'fax_after', 'return');
		//20100818ysk end
		break;
		
	case 'US':
		//20100818ysk start
		$formtag .= usces_custom_field_input($usces_entries, 'customer', 'name_pre', 'return');
		//20100818ysk end
		$formtag .= '<tr class="inp1">
		<th scope="row"><em>*</em>' . __('Full name', 'usces') . '</th>
		<td>' . __('Given name', 'usces') . '<input name="customer[name2]" id="name2" type="text" value="' . esc_attr($usces_entries['customer']['name2']) . '" /></td>
		<td>' . __('Familly name', 'usces') . '<input name="customer[name1]" id="name1" type="text" value="' . esc_attr($usces_entries['customer']['name1']) . '" /></td>
		</tr>';
		//20100818ysk start
		$formtag .= usces_custom_field_input($usces_entries, 'customer', 'name_after', 'return');
		//20100818ysk end
		$formtag .= '
		<tr>
		<th scope="row"><em>*</em>' . __('Address Line1', 'usces') . '</th>
		<td colspan="2">' . __('Street address', 'usces') . '<br /><input name="customer[address2]" id="address2" type="text" value="' . esc_attr($usces_entries['customer']['address2']) . '" /></td>
		</tr>
		<tr>
		<th scope="row">' . __('Address Line2', 'usces') . '</th>
		<td colspan="2">' . __('Apartment, building, etc.', 'usces') . '<br /><input name="customer[address3]" id="address3" type="text" value="' . esc_attr($usces_entries['customer']['address3']) . '" /></td>
		</tr>
		<tr class="inp2">
		<th scope="row"><em>*</em>' . __('city', 'usces') . '</th>
		<td colspan="2"><input name="customer[address1]" id="address1" type="text" value="' . esc_attr($usces_entries['customer']['address1']) . '" /></td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>' . __('State', 'usces') . '</th>
		<td colspan="2">' . usces_the_pref( 'customer', 'return' ) . '</td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>' . __('Zip/Postal Code', 'usces') . '</th>
		<td colspan="2"><input name="customer[zipcode]" id="zipcode" type="text" value="' . esc_attr($usces_entries['customer']['zipcode']) . '" /></td>
		</tr>
		<tr>
		<th scope="row"><em>*</em>' . __('Phone number', 'usces') . '</th>
		<td colspan="2"><input name="customer[tel]" id="tel" type="text" value="' . esc_attr($usces_entries['customer']['tel']) . '" /></td>
		</tr>
		<tr>
		<th scope="row">' . __('FAX number', 'usces') . '</th>
		<td colspan="2"><input name="customer[fax]" id="fax" type="text" value="' . esc_attr($usces_entries['customer']['fax']) . '" /></td>
		</tr>';
		//20100818ysk start
		$formtag .= usces_custom_field_input($usces_entries['customer'], 'customer', 'fax_after', 'return');
		//20100818ysk end
		break;
}
$html .= apply_filters('usces_filter_apply_customer_form', $formtag, $usces_entries);
?>
