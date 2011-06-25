<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
if(!defined('USCES_VERSION')) return;

define('NS_ITEM_SET', 'dummySet');

/***********************************************************
* NET STAGE
***********************************************************/
function ntstg_list_post( $slug, $rownum ){
global $usces;

	$cat_id = usces_get_cat_id( $slug );
	$li = '';
	$infolist = get_posts('category='.$cat_id.'&numberposts='.$rownum.'&order=DESC&orderby=post_date');
	foreach ($infolist as $post) :
		$list = "<li class='clearfix'>\n";
		$list .= "<div class='image'><img src='" . get_stylesheet_directory_uri() . "/images/home/dummy.jpg' width='60' height='60' alt='dummy' /></div>\n";
		$list .= "<div class='post'>\n";
		$list .= "<div class='date'>" . vsprintf("%d.%02d.%02d", sscanf($post->post_date, "%d-%d-%d")) . "</div>\n";
		$list .= "<div class='title'><a href='".get_permalink($post->ID)."'>" . esc_html($post->post_title) . "</a></div>\n";
		$summary = str_replace("\n", "", strip_tags($post->post_content));
		if(mb_strlen($summary) > 42) $summary = mb_substr($summary, 0, 41). "...";
		$list .= "<p>" . $summary . "</p>\n";
		$list .= "</div>\n";
		$list .= "</li>\n";
		$li .= apply_filters( 'usces_filter_widget_post', $list, $post, $slug);
	endforeach;
	echo $li;
}
function ntstg_the_itemSkuButton($value, $type=0, $out = '') {
	global $usces, $post;
	$post_id = $post->ID;
	$zaikonum = $usces->itemsku['value']['zaikonum'];
	$zaiko_status = $usces->itemsku['value']['zaiko'];
	$gptekiyo = $usces->itemsku['value']['gptekiyo'];
	$skuPrice = $usces->getItemPrice($post_id, $usces->itemsku['key']);
	$value = esc_attr(apply_filters( 'usces_filter_incart_button_label', $value));
	$sku = esc_attr($usces->itemsku['key']);

	if($type == 1)
		$type = 'button';
	else
		$type = 'image';

	$html = "<input name=\"zaikonum[{$post_id}][{$sku}]\" type=\"hidden\" id=\"zaikonum[{$post_id}][{$sku}]\" value=\"{$zaikonum}\" />\n";
	$html .= "<input name=\"zaiko[{$post_id}][{$sku}]\" type=\"hidden\" id=\"zaiko[{$post_id}][{$sku}]\" value=\"{$zaiko_status}\" />\n";
	$html .= "<input name=\"gptekiyo[{$post_id}][{$sku}]\" type=\"hidden\" id=\"gptekiyo[{$post_id}][{$sku}]\" value=\"{$gptekiyo}\" />\n";
	$html .= "<input name=\"skuPrice[{$post_id}][{$sku}]\" type=\"hidden\" id=\"skuPrice[{$post_id}][{$sku}]\" value=\"{$skuPrice}\" />\n";
	if( $usces->use_js ){
		$html .= "<input name=\"inCart[{$post_id}][{$sku}]\" type=\"{$type}\" src=\"" . get_stylesheet_directory_uri() . "/images/item/btn_addcart.png\" alt=\"カートに入れる\" id=\"inCart[{$post_id}][{$sku}]\" class=\"skubutton\" value=\"{$value}\" onclick=\"return uscesCart.intoCart('{$post_id}','{$sku}')\" />";
	}else{
		$html .= "<a name=\"cart_button\"></a><input name=\"inCart[{$post_id}][{$sku}]\" type=\"{$type}\" id=\"inCart[{$post_id}][{$sku}]\" class=\"skubutton\" value=\"{$value}\" />";
		$html .= "<input name=\"usces_referer\" type=\"hidden\" value=\"" . $_SERVER['REQUEST_URI'] . "\" />\n";
	}

	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}
function ntstg_assistance_item($post_id, $title ){
	if (usces_get_assistance_id_list($post_id)) :
		$assistanceposts = new wp_query( array('post__in'=>usces_get_assistance_ids($post_id)) );
		if($assistanceposts->have_posts()) :
		add_filter( 'excerpt_length', 'welcart_assistance_excerpt_length' );
		add_filter( 'excerpt_mblength', 'welcart_assistance_excerpt_mblength' );
?>
	<div class="assistance_item">
		<h2 class="titlebar"><?php echo $title; ?></h2>
<?php
		$itemcount = 1;
		while ($assistanceposts->have_posts()) :
			$assistanceposts->the_post();
			//update_post_caches($posts);
			usces_remove_filter();
			usces_the_item();
			if($itemcount % 4 == 1) echo '<div class="thumnail_line clearfix">';?>
			<div <?php post_class(); ?>>
			<div class="thumbnail_box slim<?php if($itemcount % 4 == 0) echo " right";?>">
			<div class="thumtitle"><a href="<?php the_permalink() ?>" rel="bookmark"><?php usces_the_itemName(); ?></a></div>
			<div class="thumimg"><a href="<?php the_permalink() ?>"><?php usces_the_itemImage($number = 0, $width = 130, $height = 130 ); ?></a></div>

			<div class="thumcomment">
				<ul class="listtag clearfix">
				<?php if(has_category('itemnew')): ?>
					<li class="tag_new"><img height="20" width="60" alt="NEW" src="<?php echo get_stylesheet_directory_uri() ?>/images/common/tag_new_mini.png"></li>
				<?php endif; ?>
				<?php if(has_category('itemreco')): ?>
					<li class="tag_recommend"><img height="20" width="60" alt="オススメ" src="<?php echo get_stylesheet_directory_uri() ?>/images/common/tag_recommend_mini.png"></li>
				<?php endif; ?>
				<?php if(has_tag('fewer')): ?>
					<li class="tag_few"><img height="20" width="60" alt="残りわずか" src="<?php echo get_stylesheet_directory_uri() ?>/images/common/tag_few_mini.png"></li>
				<?php endif; ?>
				<?php if(has_tag('limited')): ?>
					<li class="tag_limited"><img height="20" width="60" alt="限定品" src="<?php echo get_stylesheet_directory_uri() ?>/images/common/tag_limited_mini.png"></li>
				<?php endif; ?>
				</ul>
				<?php
					$content_summary = str_replace("\n", "", strip_tags($assistanceposts->post->post_content));
					echo (mb_strlen($content_summary) > 27) ? mb_substr($content_summary, 0, 25) . "..." : $content_summary;
				?>
			</div>
		<?php if (usces_is_skus()) : ?>
			<div class="price"><a href="<?php the_permalink() ?>"><?php _e('$', 'usces'); ?><?php usces_the_firstPrice(); ?><?php usces_guid_tax(); ?></a></div>
		<?php endif; ?>
		</div>
	</div>
	<?php if($itemcount % 4 == 0) echo '</div>';?>
	<?php $itemcount += 1; ?>
	<?php endwhile; ?>
	<?php if($itemcount % 4 != 1) echo '</div>';?>
	</div><!-- end of assistance_item -->
<?php
		wp_reset_query();
		usces_reset_filter();
		remove_filter( 'excerpt_length', 'welcart_assistance_excerpt_length' );
		remove_filter( 'excerpt_mblength', 'welcart_assistance_excerpt_mblength' );
		endif;
	endif;
}

function ntstg_reshaft_slideshow(){
global $usces;
	$title = array(
		1 => '商品到着',
		2 => 'お客様要望確認',
		3 => '準備',
		4 => 'ヘッド抜き',
		5 => 'ヘッドクリーニング',
		6 => 'シャフト塗装剥がし',
		7 => 'ソケット選択・装着',
		8 => 'バランス・長さ調整',
		9 => '仮組み',
		10 => 'プロコース・オプション/差し方角度調整',
		11 => 'プロコース・オプション/振動数計測, スパイン調整',
		12 => 'プロコース・オプション/センターフレックス計測, スパイン調整',
		13 => '糊付け',
		14 => '装着',
		15 => '乾燥',
		16 => 'ソケット削り',
		17 => 'プロコース・オプション/下巻きテープ調整',
		18 => 'グリップ装着',
		19 => '清掃・スペック計測',
		20 => '完成・発送',
	);
	$description = array(
		1 => '当店にクラブを送って頂きます。梱包はお客様御自身でお願いします。送料はお客様の御負担となりますので、元払いにて発送して下さい。',
		2 => '事前に送付頂いた「リシャフト専用注文フォーム」の内容を確認致します。',
		3 => 'シャフト・グリップ・ソケット等パーツが全て揃い次第作業を開始します。',
		4 => 'ソケットを軽く熱し、ずらします。ヘッドに濡れた雑巾を巻き、ヘッド、シャフトになるべく熱が加わらないようにシャフトを抜きます。シャフトに合わせて２台のマシーンを使用します。',
		5 => '糊が残っていると接着が悪くなりますので、ホーゼル内をリーマー等を用いクリーニングします。',
		6 => 'シャフトの差込分、先端の塗装を剥がします。通常は１．５インチ位のクラブが多いでが、テーラーメイド、ブリヂストン等には、差込が浅かったり、深かったりするクラブもございます。差込を間違えると、フレックスが変わってしまいます。',
		7 => 'ソケットはなるべく純正に近いものを選択し、糊付けして装着します。尚、特殊なソケットを使用されている場合には、デザインは異なりますが、サイズが合う物を使用させて頂きます。',
		8 => '長めにカットし、バランスを確認しながら序々に長さを合わせていきます。この際、御希望の長さではバランスが出ない場合がございます。その場合には、一度お客様にご連絡し、対応方法を確認させて頂きます。',
		9 => '組み上がりの長さ、バランスを計測し確認致します。その際、ホーゼルとシャフトの隙間の大きさによっては、アルミ板、銅版、セル管、カーボン板等を使用し調整します。',
		10 => 'オープン、クローズ、アップライト、フラット等差し方を調整します。この作業はホーゼルの大きさ、シャフトの太さによっては調整が不可能な場合がございます。又、この調整を行った場合にはソケットが斜め向きに仕上がります。',
		11 => '振動数計を用い、シャフトの背骨を探し、綺麗に縦に振れる位置にシャフトを装着します。クラブを構えた際に、なるべくシャフト・ロゴが見えない位置で探します。※センターフレックス計測スパイン調整との併用は出来ません。',
		12 => 'センターフレックス計にて最も硬い部分を数値で計測し、装着します。※振動数計スパイン調整との併用は出来ません。',
		13 => 'シャフト、ホーゼル内等、接着する部分に糊付けします。ホーゼルとシャフトの隙間が狭い場合には、糊にガラス粉を混ぜ調整します。シャフト内にはなるべく糊が入らないように注意します。',
		14 => 'シャフト、ヘッドを装着し、溢れた糊を拭き取ります。通常はシャフト、ヘッドが真っ直ぐになるよう装着しますが、プロコース・オプションの差し方角度調整はこの時点で、角度を調整します。',
		15 => 'アルコールでクラブを拭き、糊など汚れが付いていないか確認し、クラブが静止出来る状態にして、乾燥させます。',
		16 => '完全に接着後、ソケットをホーゼルの大きさに合わせて紙ヤスリ等で削り、仕上げにアセトンで拭き取り光沢を出します。',
		17 => 'グリップの下巻きテープを巻きます。通常は縦１回巻きます。プロコース・オプションの下巻きテープ調整の場合には、テープの厚み、巻き方、枚数など御希望の巻き方を致します。',
		18 => 'グリップを装着します。バックライン無の場合には、グリップのロゴ向きをご指定下さい。',
		19 => 'クラブ全体を清掃させて頂きます。希望者には長さ、総重量、バランス、振動数を計測し、スペックシールをシャフトのグリップ側、裏に貼らせて頂きます。',
		20 => '全ての作業が終了後、クラブ、抜いたシャフト等を梱包し発送致します。メールにて連絡させていただますので、到着まで今しばらくお待ち下さい。',
	);
	?>
    <div id="reshaft_box">
	<?php for ($i = 1; $i <= 20; $i++) :?>
		<div class="section">
			<div class="hp-highlight" style="background:url(<?php bloginfo('stylesheet_directory'); ?>/images/home/reshaft/reshaft_<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>.jpg) no-repeat 0 0">
				<div class="feature-headline">
					<h4><?php echo $title[$i]; ?></h4>
					<p><?php //echo $description[$i] ?></p>
				</div>
			</div>
		</div>
	<?php endfor; ?>
	</div>
<?php
}
/**********************************************************/
// Net Stage フィルター
add_filter('usces_filter_management_status', 'NS_filter_management_status');
function NS_filter_management_status($status){
	$status['work'] = '作業中';
	return $status;
}

// Net Stage テンプレートタグ
/**********************************************************
 * Explanation	: Page ID 取得
 * UpDate		: 2011.06.25
 * Return		: ID
 **********************************************************/
function NS_get_page_id_by_slug( $slug ){
	global $wpdb;
	$ID = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_status = 'publish'", $slug));
	return $ID;
}

/**********************************************************
 * Explanation	: メーカ名表示
 * UpDate		: 2011.05.31
 * Echo			: strings
 **********************************************************/
//function NS_teh_item_maker(){
function NS_the_item_maker(){
	$maker = '';
	$termID = get_cat_ID( 'メーカー' );
	$taxonomyName = "category";
	$termchildren = get_term_children( $termID, $taxonomyName );
	foreach ($termchildren as $child) {
		if(in_category($child)){
			$term = get_term_by( 'id', $child, $taxonomyName );
			$maker = $term->name;
		}
	}
	
	echo esc_html($maker);
}
/**********************************************************
 * Explanation	: セールタグ表示
 * UpDate		: 2011.06.01
 * Echo			: html
 **********************************************************/
function NS_the_salse_tag(){
	//global $post, $usces;
	global $usces;
	$sale_id = usces_get_cat_id( 'itemsale' );
	if(in_category($sale_id))
		$tag = '<img src="' . get_bloginfo('stylesheet_directory') . '/images/item/tag_sale.png" alt="SALE" width="70" height="30" />';
	else
		$tag = '';
		
	echo $tag;
}
/**********************************************************
 * Explanation	: 4つの商品タグ表示
 * UpDate		: 2011.06.01
 * Echo			: html
 **********************************************************/
//function NS_the_fantastic4(){
function NS_the_fantastic4( $post = '' ){
	//global $post, $usces;
	global $usces;
	if($post == '') global $post;
	
	$termIDs = array(
		'new' => usces_get_cat_id( 'itemnew' ), 
		'zaiko' => NULL, 
		'reco' => usces_get_cat_id( 'itemreco' ), 
		'limit' => usces_get_cat_id( 'itemlimited' )
		);
	$taxonomyName = "category";

	$tag = '<ul class="clearfix">'. "\n";
	foreach ($termIDs as $key => $value) {
		switch( $key ){
			case 'zaiko':
				$zsids = $usces->getItemZaikoStatusId($post->ID);
				if( in_array(1, $zsids) )
					$tag .= '<li><img src="' . get_bloginfo('stylesheet_directory') . '/images/item/tag_few.png" alt="残りわずか" width="60" height="20" /></li>' . "\n";
				else
					$tag .= '<li><img src="' . get_bloginfo('stylesheet_directory') . '/images/item/tag_few2.png" alt="残りわずか" width="60" height="20" /></li>' . "\n";
				break;
			case 'new':
				if(in_category($value))
					$tag .= '<li><img src="' . get_bloginfo('stylesheet_directory') . '/images/item/tag_new.png" alt="NEW" width="60" height="20" /></li>' . "\n";
				else
					$tag .= '<li><img src="' . get_bloginfo('stylesheet_directory') . '/images/item/tag_new2.png" alt="NEW" width="60" height="20" /></li>' . "\n";
				break;
			case 'reco':
				if(in_category($value))
					$tag .= '<li><img src="' . get_bloginfo('stylesheet_directory') . '/images/item/tag_recommend.png" alt="おすすめ" width="60" height="20" /></li>' . "\n";
				else
					$tag .= '<li><img src="' . get_bloginfo('stylesheet_directory') . '/images/item/tag_recommend2.png" alt="おすすめ" width="60" height="20" /></li>' . "\n";
				break;
			case 'limit':
				if(in_category($value))
					$tag .= '<li><img src="' . get_bloginfo('stylesheet_directory') . '/images/item/tag_limited.png" alt="限定品" width="60" height="20" /></li>' . "\n";
				else
					$tag .= '<li><img src="' . get_bloginfo('stylesheet_directory') . '/images/item/tag_limited2.png" alt="限定品" width="60" height="20" /></li>' . "\n";
				break;
		}
	}
	$tag .= '</ul>'. "\n";
	
	echo $tag;
}
/**********************************************************
 * Explanation	: 商品解説2の表示
 * UpDate		: 2011.06.01
 * Echo			: strings
 **********************************************************/
//function NS_the_item_explanation( $part ){
function NS_the_item_explanation( $part, $post = '' ){
	//global $post, $usces;
	global $usces;
	if($post == '') global $post;
	$exp = '';
	switch( $part ){
		case 2:
			$exps = get_post_meta($post->ID, 'item_exp2', true);
			break;
		case 3:
			$exps = get_post_meta($post->ID, 'item_exp3', true);
			break;
		case 4:
			$exps = get_post_meta($post->ID, 'item_exp4', true);
			break;
	}
	
	//echo $exps;
	$content = apply_filters('NS_filter_item_explanation', $exps);
	echo $content;
}
add_filter( 'NS_filter_item_explanation', 'wptexturize'        , 10);
add_filter( 'NS_filter_item_explanation', 'convert_smilies'    , 10);
add_filter( 'NS_filter_item_explanation', 'convert_chars'      , 10);
add_filter( 'NS_filter_item_explanation', 'wpautop'            , 10);
add_filter( 'NS_filter_item_explanation', 'shortcode_unautop'  , 10);
add_filter( 'NS_filter_item_explanation', 'prepend_attachment' , 10);
add_filter( 'NS_filter_item_explanation', 'do_shortcode'       , 11);
/**********************************************************
 * Explanation	: 商品価格幅の表示
 * UpDate		: 2011.06.01
 * Echo			: strings
 **********************************************************/
//function NS_the_item_pricesCr(){
function NS_the_item_pricesCr( $post = '' ){
	//global $post, $usces;
	global $usces;
	if($post == '') global $post;
	$prices = $usces->getItemPrice($post->ID);
	sort($prices);
	$first = $prices[0];
	rsort($prices);
	$last = $prices[0];
	if( $first == $last ){
		$str = usces_crform( $first, true, false, 'return' );
	}else{
		$str = usces_crform( $first, true, false, 'return' ) . '～' . usces_crform( $last, true, false, 'return' );
	}
	echo $str;
}
/**********************************************************
 * Explanation	: スターの表示
 * UpDate		: 2011.06.02
 * Echo			: strings
 **********************************************************/
//function NS_the_item_star(){
function NS_the_item_star( $post = '' ){
	//global $post;
	if($post == '') global $post;
	$str = '';
	$star = (int)get_post_meta($post->ID, '_itemStar', true);
	if( !$star )
		return;
	
	$s = 0;
	for( $i=1; $i<=5; $i++ ){
		$str .= ( $s < $star ) ? '★' : '☆';
		$s++;
	}

	echo $str;
}
/**********************************************************
 * Explanation	: 生産国の表示
 * UpDate		: 2011.06.02
 * Echo			: strings
 **********************************************************/
//function NS_the_item_country(){
function NS_the_item_country( $post = '' ){
	//global $post, $usces_settings;
	global $usces_settings;
	if($post == '') global $post;
	$country = get_post_meta($post->ID, '_itemCountry', true);
	$str = empty($country) ? '' : ('生産国：'.$usces_settings['country'][$country]);
	echo $str;
}
/**********************************************************
 * Explanation	: 規格一覧の表示
 * UpDate		: 2011.06.02
 * Echo			: html
 **********************************************************/
//function NS_the_sku_list(){
function NS_the_sku_list( $post = '' ){
	//global $post, $usces;
	global $usces;
	if($post == '') global $post;
	//if( !NS_have_sku_option() ) return;
	if( !NS_have_sku_option($post) ) return;
	$zaiko_status = get_option('usces_zaiko_status');
	//$sku_options = NS_get_sku_option();
	$sku_options = NS_get_sku_option($post->ID);
	$html = '<table class="spec_list">'."\n";
	$html .= '<tr>'."\n";
	$html .= '<th>品番</th>';
	foreach( $sku_options as $key => $value ){
		$html .= '<th>' . $key . '</th>';
		$opts[] = $key;
	}
	$html .= '<th>価格</th>';
	$html .= '<th>在庫数</th>'."\n";
	$html .= '<tr>'."\n";
	$i = 0;
	foreach( $usces->itemskus as $skucode => $skus ){
		$trclass = ( 0 === ($i % 2) ) ? 'odd' : 'even';
		$html .= '<tr class="' . $trclass . '">'."\n";
		$html .= '<td>' . $skucode . '</td>';
		foreach( $opts as $key ){
			$html .= '<td>' . $skus['option'][$key] . '</td>';
		}
		$html .= '<td>' . usces_crform($skus['price'], true, false, 'return') . '</td>';
		$html .= '<td>' . ( '' == $skus['zaikonum'] ? $zaiko_status[$skus['zaiko']] : $skus['zaikonum']) . '</td>';
		$html .= '</tr>'."\n";
		$i++;
	}
	$html .= '</table>'."\n";
	echo $html;
}
/**********************************************************
 * Explanation	: 規格選択対象品かどうか
 * UpDate		: 2011.06.02
 * Return		: boolean
 **********************************************************/
//function NS_have_sku_option(){
function NS_have_sku_option( $post = '' ){
	//$sku_options = NS_get_sku_option();
	$sku_options = NS_get_sku_option($post->ID);

	if( empty($sku_options) )
		return false;
	else
		return true;
		
}
/**********************************************************
 * Explanation	: SKU_OPTION 取得
 * UpDate		: 2011.06.03
 * Return		: array
 **********************************************************/
function NS_get_sku_option( $post_id = '' ){
	if( '' == $post_id ){
		global $post;
		$post_id = $post->ID;
	}
	global $usces;
	$res = array();
	$optorderby = $usces->options['system']['orderby_itemopt'] ? 'meta_id' : 'meta_key';
	//$optfields = $usces->get_post_custom($post->ID, $optorderby);
	$optfields = $usces->get_post_custom($post_id, $optorderby);
	foreach((array)$optfields as $key => $value){
		if( preg_match('/^_iopt_/', $key, $match) ){
			$key = substr($key, 6);
			$opts = maybe_unserialize($value[0]);
			if( isset($opts['sku']) && 1 === (int)$opts['sku']){
				$res[$key]['means'] = $opts['means'];
				$res[$key]['essential'] = $opts['essential'];
				$res[$key]['sku'] = $opts['sku'];
				$res[$key]['value'] = explode("\n", $opts['value'][0]);
			}
		}
	}
	return $res;
}
/**********************************************************
 * Explanation	: 規格選択初期フィールド
 * UpDate		: 2011.06.03
 * Echo			: html
 **********************************************************/
//function NS_sku_option_field(){
function NS_sku_option_field( $post = '' ){
	//global $post, $usces;
	global $wpdb, $usces;
	if($post == '') global $post;
	//if( !NS_have_sku_option() ) return;
	if( !NS_have_sku_option($post) ) return;

	//$sku_options = NS_get_sku_option();
	$sku_options = NS_get_sku_option($post->ID);
	$html = '';
	$i = 1;
	$skucnt = 0;
	foreach( $sku_options as $key => $values ){
		if( 0 == $values['means'] ){
			//$html .= '<div id= "opt' . $i . '" class="sku_option_select">';
			$html .= '<div class="sku_option_select">';
			$html .= '<label class="sku_option_label">' . $key . ':</label>';
			//$html .= '<select name="opt' . $i . '" class="sku_option_select_field">';
			$html .= '<select name="opt'.$key.'" id= "opt'.$i.'" class="sku_option_select_field">';
			$html .= '<option value="">選択してください</option>';
			//foreach( (array)$values['value'] as $val ){
			//	$html .= '<option value="' . $val . '">' . $val . '</option>';
			//}
			if($i == 1) {
				$skuvalue = array();
				$orderby = $usces->options['system']['orderby_itemsku'] ? 'meta_id' : 'meta_key';
				$res = $wpdb->get_results( $wpdb->prepare("SELECT meta_key, meta_value, meta_id, post_id
						FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE '%s' 
						ORDER BY {$orderby}", $post->ID, '_isku_%'), ARRAY_A );
				foreach( $res as $row ) {
					if( is_serialized( $row['meta_value'] )) $row['meta_value'] = maybe_unserialize( $row['meta_value'] );
					$skuvalue[] = $row['meta_value']['option'][$key];
				}
				$skuvalue = array_unique($skuvalue);
				foreach( $skuvalue as $val ){
					$html .= '<option value="' . $val . '">' . $val . '</option>';
				}
			}
			$html .= '</select>';
			$html .= '</div>'."\n";
			$skucnt++;
		}elseif( 2 == $values['means'] ){
			//$html .= '<div id= "opt' . $i . '" class="sku_option_text">';
			$html .= '<div class="sku_option_text">';
			$html .= '<label class="sku_option_label">' . $key . ':</label>';
			//$html .= '<input name="opt' . $i . '" type="text" value="" readonly="true" class="sku_option_text_field" />';
			$html .= '<input name="opt'.$key.'" id= "opt'.$i.'" type="text" value="" readonly="true" class="sku_option_text_field" />';
			$html .= '</div>'."\n";
		}
		$i++;
	}
	$html .= '<input type="hidden" id="sku" value="" />'."\n";
	$html .= '<input type="hidden" id="skucnt" value="'.$skucnt.'" />'."\n";
	echo $html;
}

//function NS_the_itemOption( $name, $label = '#default#', $out = '' ) {
function NS_the_itemOption( $name, $label = '#default#', $post = '', $out = '' ) {
	//global $post, $usces;
	global $usces;
	if($post == '') global $post;
	$post_id = $post->ID;
	$session_value = isset( $_SESSION['usces_singleitem']['itemOption'][$post_id][$usces->itemsku['key']][$name] ) ? $_SESSION['usces_singleitem']['itemOption'][$post_id][$usces->itemsku['key']][$name] : NULL;
	
	if($label == '#default#')
		$label = $name;
	$key = '_iopt_' . $name;
	$value = get_post_custom_values($key, $post_id);
	if(!$value) return false;
	$values = maybe_unserialize($value[0]);
	$means = (int)$values['means'];
	$essential = (int)$values['essential'];

	$html = '';
	$name = esc_attr($name);
	$label = esc_attr($label);
	switch($means) {
	case 0://Single-select
	case 1://Multi-select
		$selects = explode("\n", $values['value'][0]);
		$multiple = ($means === 0) ? '' : ' multiple';
		$html .= "\n<label for='iopt{$name}' class='iopt_label'>{$label}</label>\n";
		$html .= "\n<select name='iopt{$name}' id='iopt{$name}' class='iopt_select'{$multiple} onKeyDown=\"if (event.keyCode == 13) {return false;}\">\n";
		if($essential == 1){
			if(  '#NONE#' == $session_value || NULL == $session_value ) 
				$selected = ' selected="selected"';
			else
				$selected = '';
			$html .= "\t<option value='#NONE#'{$selected}>" . __('Choose','usces') . "</option>\n";
		}
		$i=0;
		foreach($selects as $v) {
			if( ($i == 0 && $essential == 0 && NULL == $session_value) || esc_attr($v) == $session_value ) 
				$selected = ' selected="selected"';
			else
				$selected = '';
			$html .= "\t<option value='" . esc_attr($v) . "'{$selected}>" . esc_html($v) . "</option>\n";
			$i++;
		}
		$html .= "</select>\n";
		break;
	case 2://Text
		$html .= "\n<input name='iopt{$name}' type='text' id='iopt{$name}' class='iopt_text' onKeyDown=\"if (event.keyCode == 13) {return false;}\" value=\"" . esc_attr($session_value) . "\" />\n";
		break;
	case 5://Text-area
		$html .= "\n<textarea name='iopt{$name}' id='iopt{$name}' class='iopt_textarea' />" . esc_attr($session_value) . "</textarea>\n";
		break;
	}
	
	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

//function NS_the_itemQuant( $out = '' ) {
function NS_the_itemQuant( $post = '', $out = '' ) {
	//global $usces, $post;
	global $usces;
	if($post == '') global $post;
	$post_id = $post->ID;
	$value = isset( $_SESSION['usces_singleitem']['quant'][$post_id][$usces->itemsku['key']] ) ? $_SESSION['usces_singleitem']['quant'][$post_id][$usces->itemsku['key']] : 1;
	$html = "<input name=\"qnt\" type=\"text\" id=\"qnt\" class=\"skuquantity\" value=\"" . $value . "\" onKeyDown=\"if (event.keyCode == 13) {return false;}\" />";
		
	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

//function NS_the_itemSkuButton($value, $type=0, $out = '') {
function NS_the_itemSkuButton($value, $type=0, $post = '', $out = '') {
	//global $usces, $post;
	global $usces;
	if($post == '') global $post;
	$post_id = $post->ID;

	if($type == 1)
		$type = 'button';
	else
		$type = 'image';

	if( $usces->use_js ){
		$html .= "<input name=\"inCart\" type=\"{$type}\" src=\"" . get_stylesheet_directory_uri() . "/images/item/btn_addcart2.png\" alt=\"カートに入れる\" id=\"inCart\" class=\"skubutton\" value=\"{$value}\" disabled />";
	}else{
		$html .= "<a name=\"cart_button\"></a><input name=\"inCart\" type=\"{$type}\" id=\"inCart\" class=\"skubutton\" value=\"{$value}\" disabled />";
		$html .= "<input name=\"usces_referer\" type=\"hidden\" value=\"" . $_SERVER['REQUEST_URI'] . "\" />\n";
	}

	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

function NS_get_itemOptions( $key, $post_id ) {
	$metakey = '_iopt_' . $key;
	$values = get_post_custom_values( $metakey, $post_id );
	if(empty($values)) return NULL;

	$val = ( is_serialized( $values[0] )) ? unserialize( $values[0] ) : $values[0];
	if( $val['sku'] != 1 ) return $val; else return NULL;
}

add_action('usces_front_ajax', 'change_sku_option_ajax');
function change_sku_option_ajax() {
	global $wpdb, $usces;
	$post_id = $_POST['post_id'];
	$key = $_POST['key'];
	$value = $_POST['value'];
	$index = $_POST['index'];
	$skukey = isset($_POST['skukey']) ? explode("#usces#", trim($_POST['skukey'])) : array();//SKUオプション(KEY)
	$skuoption = isset($_POST['skuoption']) ? explode("#usces#", trim($_POST['skuoption'])) : array();//SKUオプション(VALUE)
	$skucnt = count($skukey);
	$nextskukey = $_POST['nextskukey'];
	$sku = array();
	$nextskuvalue = array();
	$optkey = array();
	$optvalue = array();
	$skuprice = '';
	$zaikonum = 0;
	$html = '';
	$msg = '';
	$set = isset($_POST['set']) ? $_POST['set'] : '';
	$type = isset($_POST['type']) ? $_POST['type'] : '';
	$nextaction = isset($_POST['nextaction']) ? $_POST['nextaction'] : '';

	$orderby = $usces->options['system']['orderby_itemsku'] ? 'meta_id' : 'meta_key';
	$res = $wpdb->get_results( $wpdb->prepare("SELECT meta_key, meta_value, meta_id, post_id
			FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE '%s' 
			ORDER BY {$orderby}", $post_id, '_isku_%'), ARRAY_A );
	foreach( $res as $row ) {
		if( is_serialized( $row['meta_value'] )) $row['meta_value'] = maybe_unserialize( $row['meta_value'] );
		$chk = 0;
		for($i = 0; $i < $skucnt; $i++) {
			if($row['meta_value']['option'][$skukey[$i]] != $skuoption[$i]) {
				$chk = 1;
				break;
			}
		}
		if($chk == 0 and $row['meta_value']['option'][$key] == $value) {
			$sku[] = esc_attr(substr($row['meta_key'],6));
			if($nextskukey != '') $nextskuvalue[] = esc_attr($row['meta_value']['option'][$nextskukey]);
		}
	}

	$sku = array_unique($sku);
	$nextskuvalue = array_unique($nextskuvalue);

	if(0 == count($sku)) {
		$msg = esc_attr('ご選択戴きました商品は未だ登録されていません。');
	} else {
		if(count($sku) == 1 and !empty($sku[0])) {
			foreach( $res as $row ) {
				if($row['meta_key'] == '_isku_'.$sku[0]) {
					if( is_serialized( $row['meta_value'] )) $row['meta_value'] = maybe_unserialize( $row['meta_value'] );
					foreach( $row['meta_value']['option'] as $k => $v ) {
						$optkey[] = esc_attr($k);
						$optvalue[] = esc_attr($v);
					}
					$skuprice = esc_attr(usces_crform($row['meta_value']['price'], true, false, 'return'));
					$zaiko_num = trim($row['meta_value']['zaikonum']);
					$status_num = $row['meta_value']['zaiko'];
					if( false !== $zaiko_num 
						&& ( 0 < (int)$zaiko_num || '' == $zaiko_num ) 
						&& 2 > $status_num 
					){
						if($set == 1) {
							$html  = "<input name=\"".$nextaction."\" type=\"submit\" class=\"select_item_button\" value=\"　\" onclick=\"return uscesCart.intoCart('".$post_id."','".$sku[0]."')\" />\n";
							$html .= "<input name=\"".$type."_post_id\" type=\"hidden\" value=\"".$post_id."\" />\n";
							$html .= "<input name=\"".$type."_sku\" type=\"hidden\" value=\"".$sku[0]."\" />\n";
							$html .= "<input name=\"".$type."_price\" type=\"hidden\" value=\"".$row['meta_value']['price']."\" />\n";
						} else {
							$html  = "<input name=\"zaikonum[".$post_id."][".$sku[0]."]\" type=\"hidden\" id=\"zaikonum[".$post_id."][".$sku[0]."]\" value=\"".$row['meta_value']['zaikonum']."\" />\n";
							$html .= "<input name=\"zaiko[".$post_id."][".$sku[0]."]\" type=\"hidden\" id=\"zaiko[".$post_id."][".$sku[0]."]\" value=\"".$row['meta_value']['zaiko']."\" />\n";
							$html .= "<input name=\"skuPrice[".$post_id."][".$sku[0]."]\" type=\"hidden\" id=\"skuPrice[".$post_id."][".$sku[0]."]\" value=\"".$row['meta_value']['price']."\" />\n";
							if( $usces->use_js ){
								$html .= "<input name=\"inCart[".$post_id."][".$sku[0]."]\" type=\"image\" src=\"" . get_stylesheet_directory_uri() . "/images/item/btn_addcart.png\" alt=\"カートに入れる\" id=\"inCart[".$post_id."][".$sku[0]."]\" class=\"skubutton\" value=\"カートに入れる\" onclick=\"return uscesCart.intoCart('".$post_id."','".$sku[0]."')\" />";
							}else{
								$html .= "<a name=\"cart_button\"></a><input name=\"inCart[".$post_id."][".$sku[0]."]\" type=\"image\" id=\"inCart[".$post_id."][".$sku[0]."]\" class=\"skubutton\" value=\"カートに入れる\" />";
								$html .= "<input name=\"usces_referer\" type=\"hidden\" value=\"" . $_SERVER['REQUEST_URI'] . "\" />\n";
							}
						}
					} else {
						$msg = esc_attr('大変申し訳ございません。ご選択いただきました商品は、只今在庫切れとなっております。');
					}
					break;
				}
			}
		}
	}
	if($html == '' and $set != 1) $html = NS_the_itemSkuButton(__('Add to Shopping Cart', 'usces'), 0, '', 'return');
	
	die(implode("#ns#", $sku)."#usces#".implode("#ns#", $nextskuvalue)."#usces#".implode("#ns#", $optkey)."#usces#".implode("#ns#", $optvalue)."#usces#".$skuprice."#usces#".$zaikonum."#usces#".$html."#usces#".$msg);
}


//function usces_all_change_order_reciept(&$obj){
//	global $wpdb;
//
//	$tableName = $wpdb->prefix . "usces_order";
//	$ids = $_POST['listcheck'];
//	$status = true;
//	foreach ( (array)$ids as $id ):
//		$query = $wpdb->prepare("SELECT order_status FROM $tableName WHERE ID = %d", $id);
//		$statusstr = $wpdb->get_var( $query );
//		if(strpos($statusstr, 'noreceipt') === false && strpos($statusstr, 'receipted') === false) continue;
//		if($_REQUEST['change']['word']['order_reciept'] == 'receipted') {
//			if(strpos($statusstr, 'noreceipt') !== false)
//				$statusstr = str_replace('noreceipt', 'receipted', $statusstr);
//		}elseif($_REQUEST['change']['word']['order_reciept'] == 'noreceipt') {
//			if(strpos($statusstr, 'receipted') !== false)
//				$statusstr = str_replace('receipted', 'noreceipt', $statusstr);
//		}
//		$query = $wpdb->prepare("UPDATE $tableName SET order_status = %s WHERE ID = %d", $statusstr, $id);
//		$res = $wpdb->query( $query );
//		if( $res === false ) {
//			$status = false;
//		}
//	endforeach;
//	if ( true === $status ) {
//		$obj->set_action_status('success', __('I completed collective operation.','usces'));
//	} elseif ( false === $status ) {
//		$obj->set_action_status('error', __('ERROR: I was not able to complete collective operation','usces'));
//	} else {
//		$obj->set_action_status('none', '');
//	}
//}

/**********************************************************
 * Explanation	: B2用CSV出力
 * UpDate		: 2011.06.27
 * exit			: csv
 **********************************************************/
if( 'dlB2list' == $_REQUEST['order_action'] 
	&& 'usces_orderlist' == $_REQUEST['page'] ){
		NS_download_B2_list();
}
//if( 'dlB2list' == $_REQUEST['allchange[column]'] ){
//		NS_download_B2_list();
//}
function NS_download_B2_list(){
	require_once( USCES_PLUGIN_DIR . "/classes/orderList.class.php" );
	global $wpdb, $usces, $usces_settings;
	
	$usces_option = get_option('usces');

	//==========================================================================

	if( isset($_REQUEST['list_id']) && !empty($_REQUEST['list_id']) ) {
		$ids = trim($_REQUEST['list_id'], ',');
		if( empty($ids) ) return false;
	}else{
		return false;
	}

	$tableName = $wpdb->prefix . "usces_order";
	$query = $wpdb->prepare("SELECT * FROM $tableName WHERE ID IN (%s)", $ids);
	$rows = $wpdb->get_results( $query, ARRAY_A  );

	$line = '';
	//==========================================================================
	//header
	
/* 01 */	$line .= '"お客様管理番号(注文番号)",';
/* 02 */	$line .= '"送り状種類",';
/* 03 */	$line .= '"空欄",';
/* 04 */	$line .= '"空欄",';
/* 05 */	$line .= '"出荷予定日",';
/* 06 */	$line .= '"お届け予定日(空欄)",';
/* 07 */	$line .= '"配達時間帯(空欄)",';
/* 08 */	$line .= '"お届け先コード",';
/* 09 */	$line .= '"お届け先電話番号",';
/* 10 */	$line .= '"お届け先電話番号枝",';
/* 11 */	$line .= '"お届け先郵便番号",';
/* 12 */	$line .= '"お届け先住所",';
/* 13 */	$line .= '"お届け先建物名",';
/* 14 */	$line .= '"お届け先会社・部門１",';
/* 15 */	$line .= '"お届け先会社・部門２",';
/* 16 */	$line .= '"お届け先名",';
/* 17 */	$line .= '"お届け先名略称カナ",';
/* 18 */	$line .= '"空欄",';
/* 19 */	$line .= '"ご依頼主コード",';
/* 20 */	$line .= '"ご依頼主電話番号",';
/* 21 */	$line .= '"ご依頼主電話番号枝",';
/* 22 */	$line .= '"ご依頼主郵便番号",';
/* 23 */	$line .= '"ご依頼主住所",';
/* 24 */	$line .= '"ご依頼主建物名",';
/* 25 */	$line .= '"ご依頼主名",';
/* 26 */	$line .= '"ご依頼主名略称カナ",';
/* 27 */	$line .= '"品名コード１",';
/* 28 */	$line .= '"品名１",';
/* 29 */	$line .= '"品名コード２",';
/* 30 */	$line .= '"品名２",';
/* 31 */	$line .= '"荷扱い１",';
/* 32 */	$line .= '"荷扱い２",';
/* 33 */	$line .= '"記事",';
/* 34 */	$line .= '"コレクト代金引換額",';
/* 35 */	$line .= '"コレクト内消費税",';
/* 36 */	$line .= '"営業所止置き",';
/* 37 */	$line .= '"営業所コード",';
/* 38 */	$line .= '"発行枚数",';
/* 39 */	$line .= '"個数口枠の印字",';
/* 40 */	$line .= '"ご請求先顧客コード",';
/* 41 */	$line .= '"ご請求先分類コード",';
/* 42 */	$line .= '"運賃管理番号"';
			$line .= "\n";
	
	
	
	//==========================================================================
	//body
	
	foreach((array)$rows as $data) {
		$deli = unserialize($data['order_delivery']);
		$cart = unserialize($data['order_cart']);
		$row_num = count($cart);

		$first_code = get_post_meta($cart[0]['post_id'], '_itemCode', true);
		$first_name = get_post_meta($cart[0]['post_id'], '_itemName', true);
		if( 2 === $row_num ){
			$second_code = get_post_meta($cart[1]['post_id'], '_itemCode', true);
			$second_name = get_post_meta($cart[1]['post_id'], '_itemName', true);
		}elseif( 2 < $row_num ){
			$second_code = get_post_meta($cart[1]['post_id'], '_itemCode', true);
			$second_name = get_post_meta($cart[1]['post_id'], '_itemName', true) . '　その他';
		}else{
			$second_code = "";
			$second_name = "";
		}
		
		
/* 01 */	$line .= '"' . $data['ID'] . '",';
/* 02 */	$line .= '"0",';
/* 03 */	$line .= ',';
/* 04 */	$line .= ',';
/* 05 */	$line .= '"' . date('Ymd', current_time('timestamp')) . '",';
/* 06 */	$line .= ',';
/* 07 */	$line .= ',';
/* 08 */	$line .= ',';
/* 09 */	$line .= '"' . str_replace('-', '', $deli['order_tel']) . '",';
/* 10 */	$line .= ',';
/* 11 */	$line .= '"' . str_replace('-', '', $deli['zipcode']) . '",';
/* 12 */	$line .= '"' . str_replace('"', '""', $deli['pref'] . $deli['address1'] . $deli['address2']) . '",';
/* 13 */	$line .= '"' . str_replace('"', '""', $deli['address3']) . '",';
/* 14 */	$line .= ',';
/* 15 */	$line .= ',';
/* 16 */	$line .= '"' . str_replace('"', '""', $deli['name1'] . $deli['name2']) . '",';
/* 17 */	$line .= ',';
/* 18 */	$line .= ',';
/* 19 */	$line .= ',';
/* 20 */	$line .= '"' . str_replace('-', '', $usces_option['tel_number']) . '",';
/* 21 */	$line .= ',';
/* 22 */	$line .= '"' . str_replace('-', '', $usces_option['zip_code']) . '",';
/* 23 */	$line .= '"' . str_replace('"', '""', $usces_option['address1']) . '",';
/* 24 */	$line .= '"' . str_replace('"', '""', $usces_option['address2']) . '",';
/* 25 */	$line .= '"' . str_replace('"', '""', $usces_option['company_name']) . '",';
/* 26 */	$line .= ',';
/* 27 */	$line .= '"' . str_replace('"', '""', $first_code) . '",';
/* 28 */	$line .= '"' . str_replace('"', '""', $first_name) . '",';
/* 29 */	$line .= '"' . str_replace('"', '""', $second_code) . '",';
/* 30 */	$line .= '"' . str_replace('"', '""', $second_name) . '",';
/* 31 */	$line .= ',';
/* 32 */	$line .= ',';
/* 33 */	$line .= ',';
/* 34 */	$line .= ',';
/* 35 */	$line .= ',';
/* 36 */	$line .= ',';
/* 37 */	$line .= ',';
/* 38 */	$line .= '"1",';
/* 39 */	$line .= '"1",';
/* 40 */	$line .= '"' . str_replace('"', '""', $usces_option['b2_bill_code']) . '",';
/* 41 */	$line .= '"' . str_replace('"', '""', $usces_option['b2_div_code']) . '",';
/* 42 */	$line .= '"' . str_replace('"', '""', $usces_option['b2_admin_id']) . '"';
			$line .= "\n";
		
	}	
		
	//==========================================================================

	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=b2_list.csv");
	mb_http_output('pass');
	print(mb_convert_encoding($line, "SJIS-win", "UTF-8"));
	exit();

}
function NS_the_shipment_aim( $post = '', $out = '' ) {
	if($post == '') global $post;
	$post_id = $post->ID;

	$str = get_post_custom_values('_itemShipping', $post_id);
	$no = (int)$str[0];
	if( 0 === $no ) return;
	
	$rules = get_option('usces_shipping_rule');
	
	if( $out == 'return' ){
		return $rules[$no];
	}else{
		echo esc_html($rules[$no]);
	}
}

function NS_the_itemCode( $post = '', $out = '' ) {
	if($post == '') global $post;
	$post_id = $post->ID;

	$str = get_post_custom_values('_itemCode', $post_id);
	
	if( $out == 'return' ){
		return $str[0];
	}else{
		echo esc_html($str[0]);
	}
}

function NS_the_item( $post = '' ) {
	global $usces;
	if($post == '') global $post;
	$usces->itemskus = array();
	$usces->itemopts = array();
	$post_id = $post->ID;
	
	$skuorderby = $usces->options['system']['orderby_itemsku'] ? 'meta_id' : 'meta_key';
	$skufields = $usces->get_post_custom($post_id, $skuorderby);
	$optorderby = $usces->options['system']['orderby_itemopt'] ? 'meta_id' : 'meta_key';
	$optfields = $usces->get_post_custom($post_id, $optorderby);
	foreach((array)$skufields as $key => $value){
		if( preg_match('/^_isku_/', $key, $match) ){
			$key = substr($key, 6);
			$values = maybe_unserialize($value[0]);
			$usces->itemskus[$key] = $values;
		}
	}
	foreach((array)$optfields as $key => $value){
		if( preg_match('/^_iopt_/', $key, $match) ){
			$key = substr($key, 6);
			$values = maybe_unserialize($value[0]);
			//NS Customize
			if( !isset( $values['sku'] ) || 1 != $values['sku'] )
				$usces->itemopts[$key] = $values;
		}
	}
	//var_dump($fields);
	//natcasesort($usces->itemskus);
	//ksort($usces->itemskus, SORT_STRING);
	//ksort($usces->itemopts, SORT_STRING);
	return;
}

function NS_get_itemSubImageNums( $post = '' ) {
	global $usces;
	if($post == '') global $post;
	$post_id = $post->ID;
	$res = array();
	
	$code =  get_post_custom_values('_itemCode', $post_id);
	if(!$code) return false;
	$name = get_post_custom_values('_itemName', $post_id);
	$pictids = $usces->get_pictids($code[0]);
	for($i=1; $i<count($pictids); $i++){
		$res[] = $i;
	}
	return  $res;
}

function NS_get_cart( $cart ) {
	global $usces;
	$rows = array();
	$set_post_id = $usces->get_postIDbyCode( NS_ITEM_SET );

	$i = 0;
	foreach($cart as $key => $row) {
		if($row['post_id'] == $set_post_id) {
			$serial = $row['serial'];
			$quantity = $row['quantity'];
			$advance = $usces->cart->wc_unserialize($row['advance']);
			$options = $advance[$row['post_id']][$row['sku']];

			$post_id = $row['options']['set_head'];
			if($post_id < 0) {
				$advance['mochi_head']['mochi_head_sku'] = $options['set_head_options'];
			} else {
				$sku = $row['options']['set_head_sku'];
				$r = array();
				$r['serial'] = $serial;
				$r['post_id'] = $post_id;
				$r['sku'] = $sku;
				$r['options'] = (!empty($options['set_head_options'])) ? $options['set_head_options'] : array();
				$r['price'] = usces_get_item_price($post_id, $sku);
				$r['quantity'] = $quantity;
				$r['advance'] = array();
				$rows[$i] = $r;
				$i++;
			}

			$post_id = $row['options']['set_shuft'];
			if($post_id < 0) {
				$advance['mochi_shuft']['mochi_shuft_sku'] = $options['set_shuft_options'];
			} else {
				$sku = $row['options']['set_shuft_sku'];
				$r = array();
				$r['serial'] = $serial;
				$r['post_id'] = $post_id;
				$r['sku'] = $sku;
				$r['options'] = (!empty($options['set_shuft_options'])) ? $options['set_shuft_options'] : array();
				$r['price'] = usces_get_item_price($post_id, $sku);
				$r['quantity'] = $quantity;
				$r['advance'] = array();
				$rows[$i] = $r;
				$i++;
			}

			$post_id = $row['options']['set_grip'];
			if($post_id < 0) {
				$advance['mochi_grip']['mochi_grip_sku'] = $options['set_grip_options'];
			} else {
				$sku = $row['options']['set_grip_sku'];
				$r = array();
				$r['serial'] = $serial;
				$r['post_id'] = $post_id;
				$r['sku'] = $sku;
				$r['options'] = (!empty($options['set_grip_options'])) ? $options['set_grip_options'] : array();
				$r['price'] = usces_get_item_price($post_id, $sku);
				$r['quantity'] = $quantity;
				$r['advance'] = array();
				$rows[$i] = $r;
				$i++;
			}

			$row['price'] = usces_get_item_price($row['post_id'], $row['sku']);
			$row['advance'] = $usces->cart->wc_serialize($advance);
			$rows[$i] = $row;
			$i++;

		} else {
			$rows[$i] = $row;
			$i++;
		}
	}

	return $rows;
}

function usces_get_itemImage( $post_id, $number = 0, $width = 60, $height = 60 ) {
	global $usces;

	$code =  get_post_custom_values('_itemCode', $post_id);
	if(!$code) return false;
	
	$name = get_post_custom_values('_itemName', $post_id);
	
	$pictids = $usces->get_pictids($code[0]);
	$html = wp_get_attachment_image( $pictids[$number], array($width, $height), false );

	return $html;
}

function usces_get_item_price($post_id, $sku){
	global $usces;
	$field = get_post_meta($post_id, '_isku_'.$sku, true);
	return $field['price'];
}

function usces_get_item_cprice($post_id, $sku){
	global $usces;
	$field = get_post_meta($post_id, '_isku_'.$sku, true);
	return $field['cprice'];
}

?>