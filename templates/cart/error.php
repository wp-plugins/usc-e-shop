<?php
$html = '<div id="error-page">

<h2>ERROE</h2>
<div class="post">
<p>'.__('Your order has not been completed', 'usces').'</p>
<p>(error ' . urldecode($_REQUEST['acting_return']) . ')</p>

</div>

</div>';
?>
