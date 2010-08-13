<?php
		$html .= '
		<table class="customer_form" id="custom_order">';

	$meta = has_custom_order_meta();
	foreach($meta as $key => $entry) {

		$name = $entry['name'];
		$means = $entry['means'];
		$essential = $entry['essential'];
		$value = '';
		if(is_array($entry['value'])) {
			foreach($entry['value'] as $k => $v) {
				$value .= htmlspecialchars($v)."\n";
			}
		}
		$value = trim($value);

		$e = ($essential == 1) ? '<em>*</em>' : '';
		$html .= '
			<tr>
			<th scope="row">'.$e.$name.'</th>';
		switch($means) {
		case 0://シングルセレクト
		case 1://マルチセレクト
			$selects = explode("\n", $value);
			$multiple = ($means == 0) ? '' : ' multiple';
			$multiple_array = ($means == 0) ? '' : '[]';
			$html .= '
				<td colspan="2">
				<select name="custom_order['.$key.']'.$multiple_array.'" class="iopt_select"'.$multiple.'>';
			if($essential == 1) 
				$html .= '
					<option value="#NONE#">'.__('Choose','usces').'</option>';
			foreach($selects as $v) {
				$selected = ($usces_entries['custom_order'][$key] == $v) ? ' selected' : '';
				$html .= '
					<option value="'.$v.'"'.$selected.'>'.$v.'</option>';
			}
			$html .= '
				</select></td>';
			break;
		case 2://テキスト
			$html .= '
				<td colspan="2"><input type="text" name="custom_order['.$key.']" size="30" value="'.$usces_entries['custom_order'][$key].'" /></td>';
			break;
		case 3://ラジオボタン
			$selects = explode("\n", $value);
			$html .= '
				<td colspan="2">';
			foreach($selects as $v) {
				$checked = ($usces_entries['custom_order'][$key] == $v) ? ' checked' : '';
				$html .= '
				<input type="radio" name="custom_order['.$key.']" id="custom_order['.$key.']['.$v.']" value="'.$v.'"'.$checked.'><label for="custom_order['.$key.']['.$v.']" class="iopt_label">'.$v.'</label>';
			}
			$html .= '
				</td>';
			break;
		case 4://チェックボックス
			$selects = explode("\n", $value);
			$html .= '
				<td colspan="2">';
			foreach($selects as $v) {
				if(is_array($usces_entries['custom_order'][$key])) {
					$checked = (array_key_exists($v, $usces_entries['custom_order'][$key])) ? ' checked' : '';
				} else {
					$checked = ($usces_entries['custom_order'][$key] == $v) ? ' checked' : '';
				}
				$html .= '
				<input type="checkbox" name="custom_order['.$key.'][]" id="custom_order['.$key.']['.$v.']" value="'.$v.'"'.$checked.'><label for="custom_order['.$key.']['.$v.']" class="iopt_label">'.$v.'</label>';
			}
			$html .= '
				</td>';
			break;
		}
		$html .= '
			</tr>';
	}

		$html .= '
		</table>';
?>
