<?php
usces_the_item();

$html = '<div class="loopimg">
	<a href="' . get_permalink($post->ID) . '">' . usces_the_itemImage(0, 100, 100, $post, 'return') . '</a>
	</div>
	<div class="loopexp">
		<div class="field">' . $content . '</div>
	</div>
	';
$html = apply_filters( 'usces_filter_item_list_loopimg', $html, $content);
?>
