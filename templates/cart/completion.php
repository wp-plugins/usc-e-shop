<?php

add_filter('usces_filter_conversion_tracking', 'usces_test_conversion', 10, 3);
function usces_test_conversion(){
	$script = '<!-- Google Code for &#36092;&#20837; Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1007005965;
var google_conversion_language = "ja";
var google_conversion_format = "2";
var google_conversion_color = "ffffff";
var google_conversion_label = "iSVACIP24wEQjeKW4AM";
var google_conversion_value = 0;
if (3000) {
  google_conversion_value = 3000;
}
/* ]]> */
</script>
<script type="text/javascript" src="https://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="https://www.googleadservices.com/pagead/conversion/1007005965/?value=3000&amp;label=iSVACIP24wEQjeKW4AM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>';
	return $script;
}


$entry = $this->cart->get_entry();
$cart = $this->cart->get_cart();
$html = '';

$html .= '<h3>'.__('It has been sent succesfully.', 'usces').'</h3>'."\n";
$html .= '<div class="post">'."\n";
$html .= '<div class="header_explanation">'."\n";
$header = '<p>'.__('Thank you for shopping.', 'usces').'<br />'.__("If you have any questions, please contact us by 'Contact'.", 'usces').'</p>';
$html .= apply_filters('usces_filter_cartcompletion_page_header', $header, $entry, $cart)."\n";
$html .= '</div><!-- header_explanation -->'."\n";

require_once( USCES_PLUGIN_DIR . "/includes/completion_settlement.php");

$html .= apply_filters('usces_filter_cartcompletion_page_body', NULL, $entry, $cart)."\n";

$html .= '<div class="footer_explanation">'."\n";
$footer = '';
$html .= apply_filters('usces_filter_cartcompletion_page_footer', $footer, $entry, $cart)."\n";
$html .= '</div><!-- footer_explanation -->'."\n";

$html .= '<form action="' . get_option('home') . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">'."\n";
$html .= '<div class="send"><input name="top" class="back_to_top_button" type="submit" value="'.__('Back to the top page.', 'usces').'" /></div>'."\n";
$html .= '</form>'."\n";

$html .= '</div><!-- post -->'."\n";
$html .= apply_filters('usces_filter_conversion_tracking', NULL, $entry, $cart)."\n";
?>
