<?php
$class = ' class="bdc"';
$meta = has_custom_order_meta();
foreach($meta as $key => $entry) {
	$name = $entry['name'];
	$means = $entry['means'];
	$essential = $entry['essential'];

$html .= '<tr'.$class.'>
	<th>'.$name.'</th>
	<td>';
	switch($means) {
	case 0://シングルセレクト
	case 2://テキスト
	case 3://ラジオボタン
		$html .= $usces_entries['custom_order'][$key];
		break;
	case 1://マルチセレクト
	case 4://チェックボックス
		if(is_array($usces_entries['custom_order'][$key])) {
			$c = '';
			foreach($usces_entries['custom_order'][$key] as $v) {
				$html .= $c.$v;
				$c = ', ';
			}
		} else {
			$html .= $usces_entries['custom_order'][$key];
		}
		break;
	}
$html .= '
	</td>
	</tr>';

	$class = ($class == '') ? ' class="bdc"' : '';
}

$html .= '<tr'.$class.'>
	<th>'.__('Notes', 'usces').'</th><td>' . nl2br($usces_entries['order']['note']) . '</td>
	</tr>';
?>
