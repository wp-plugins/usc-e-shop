<?php
global $usces_entries, $usces_carts;
usces_get_entries();
usces_get_carts();

$html = '';

$html .= '<h3>'.__('It has been sent succesfully.', 'usces').'</h3>'."\n";
$html .= '<div class="post">'."\n";
$html .= '<div class="header_explanation">'."\n";
$header = '<p>'.__('Thank you for shopping.', 'usces').'<br />'.__("If you have any questions, please contact us by 'Contact'.", 'usces').'</p>';
$html .= apply_filters('usces_filter_cartcompletion_page_header', $header, $usces_entries, $usces_carts)."\n";
$html .= '</div><!-- header_explanation -->'."\n";

require( USCES_PLUGIN_DIR . "/includes/completion_settlement.php");

$html .= apply_filters('usces_filter_cartcompletion_page_body', NULL, $usces_entries, $usces_carts)."\n";

$html .= '<div class="footer_explanation">'."\n";
$footer = '';
$html .= apply_filters('usces_filter_cartcompletion_page_footer', $footer, $usces_entries, $usces_carts)."\n";
$html .= '</div><!-- footer_explanation -->'."\n";

$html .= '<form action="' . get_option('home') . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">'."\n";
$html .= '<div class="send"><input name="top" class="back_to_top_button" type="submit" value="'.__('Back to the top page.', 'usces').'" /></div>'."\n";
$html .= '</form>'."\n";

$html .= '</div><!-- post -->'."\n";
$html .= apply_filters('usces_filter_conversion_tracking', NULL, $usces_entries, $usces_carts)."\n";
?>
