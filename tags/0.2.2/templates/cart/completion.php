<?php
$html = '<h2>送信が完了しました</h2>
<div class="post">';

$html .= '<div class="header_explanation">';
$header = '<p>お買い上げありがとうございました。<br />ご不明な点などがございましたら、お問合せよりご連絡ください。</p>';
$html .= apply_filters('usces_filter_cartcompletion_page_header', $header);
$html .= '</div>';

$html .= '<form action="' . get_option('home') . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
<div class="send"><input name="top" type="submit" value="トップページへ戻る" /></div>
</form>';

$html .= '<div class="footer_explanation">';
$footer = '';
$html .= apply_filters('usces_filter_cartcompletion_page_footer', $footer);
$html .= '</div>';

$html .= '</div>';
?>
