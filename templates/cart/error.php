<?php
$html = '<div id="error-page">

<h2>ERROR</h2>
<div class="post">
<p>'.__('Your order has not been completed', 'usces').'</p>
<p>(error ' . esc_html(urldecode($_REQUEST['acting_return'])) . ')</p>';

$html .= uesces_get_error_settlement( 'return' );

$html .= '</div>

</div>';
?>