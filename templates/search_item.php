<?php
$uscpaged = isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;

$html = '<script type="text/javascript">
function usces_nextpage() {
	document.getElementById(\'usces_paged\').value = ' . ($uscpaged + 1) . ';
	document.searchindetail.submit();
}
function usces_prepage() {
	document.getElementById(\'usces_paged\').value = ' . ($uscpaged - 1) . ';
	document.searchindetail.submit();
}
function newsubmit() {
	document.getElementById(\'usces_paged\').value = 1;
}
</script>';

$html .= '<div id="searchbox">
<form name="searchindetail" action="' . USCES_CART_URL . '&page=search_item" method="post">
<div class="field">
<label class="outlabel">'.__('Categories', 'usces').' : AND検索</label>' . usces_categories_checkbox('return') . '
</div>
<input name="usces_search_button" type="submit" value="'.__('Search', 'usces').'" onclick="newsubmit()" />
<input name="paged" id="usces_paged" type="hidden" value="' . $uscpaged . '" />
<input name="usces_search" type="hidden" />
</form>';

if (isset($_REQUEST['usces_search'])) {
	$catresult = usces_search_categories(); 
	//$p = get_posts( array('category__and' => $catresult) );
	
	//query_posts( array('category__and' => $catresult, 'posts_per_page' => 10, 'paged' => $uscpaged) );
	$my_query = new WP_Query( array('category__and' => $catresult, 'posts_per_page' => 10, 'paged' => $uscpaged) );
	
	
	$html .= '<div class="title">'.__('Search results', 'usces').'</div>';
	
	if ($my_query->have_posts()) {
	
		$html .= '<div class="navigation">';
		if( $uscpaged > 1 ) {
			$html .= '<a style="cursor:pointer;" onclick="usces_prepage();">'.__('Next article &raquo;', 'usces').'</a>';
		}
		if( $uscpaged < $my_query->max_num_pages ) {
			$html .= '<a style="cursor:pointer;" onclick="usces_nextpage();">'.__('&laquo; Previous article', 'usces').'</a>';
		}
		$html .= '</div>
	
		<div class="searchitems">';
		
		while ($my_query->have_posts()) {
			$my_query->the_post();
			usces_the_item();
	
			$html .= '<div class="itemlist clearfix"><div class="loopimg">
				<a href="' . get_permalink($post->ID) . '">' . usces_the_itemImage(0, 100, 100, $post, 'return') . '</a>
				</div>
				<div class="loopexp">
					<div class="itemtitle"><a href="' . get_permalink($post->ID) . '">' . $post->post_title . '</a></div>
					<div class="field">' . $post->post_content . '</div>
				</div>
				</div>';
		}
		$html .= '</div><!-- searchitems -->';
		$html .= '<div class="navigation">';
		if( $uscpaged > 1 ) {
			$html .= '<a style="cursor:pointer;" onclick="usces_prepage();">'.__('Next article &raquo;', 'usces').'</a>';
		}
		if( $uscpaged < $my_query->max_num_pages ) {
			$html .= '<a style="cursor:pointer;" onclick="usces_nextpage();">'.__('&laquo; Previous article', 'usces').'</a>';
		}
		$html .= '</div>';
	}	
}
$html .= '</div>';
?>
