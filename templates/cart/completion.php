<?php
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

$html .= '<div class="footer_explanation">'."\n";
$footer = '';
$html .= apply_filters('usces_filter_cartcompletion_page_footer', $footer, $entry, $cart)."\n";
$html .= '</div><!-- footer_explanation -->'."\n";

$html .= '<form action="' . get_option('home') . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">'."\n";
$html .= '<div class="send"><input name="top" type="submit" value="'.__('Back to the top page.', 'usces').'" /></div>'."\n";
$html .= '</form>'."\n";

$html .= '</div><!-- post -->'."\n";
?>
