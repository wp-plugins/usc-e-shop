<?php
$html = '<div id="error-page">

<h2>ERROE</h2>
<div class="post">
<p>'.__('Your order has not been completed', 'usces').'</p>
<p>(error ' . urldecode($_REQUEST['acting_return']) . ')</p>';

if( isset($_REQUEST['acting']) && ('zeus_conv' == $_REQUEST['acting'] || 'zeus_card' == $_REQUEST['acting'] || 'zeus_bank' == $_REQUEST['acting'] ) ){ //ZEUS
	$html .= '<div class="support_box">ゼウス・カスタマーサポート(24時間365日)<br />
	電話番号：0570-02-3939(つながらないときは 03-4334-0500)<br />
	E-mail:support@cardservice.co.jp
	</div>'."\n";
}

$html .= '</div>

</div>';
?>