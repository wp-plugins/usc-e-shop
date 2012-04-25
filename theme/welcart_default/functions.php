<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
if(!defined('USCES_VERSION')) return;

/***********************************************************
* welcart_setup
***********************************************************/
add_action( 'after_setup_theme', 'welcart_setup' );
if ( ! function_exists( 'welcart_setup' ) ):
function welcart_setup() {
	
	load_theme_textdomain( 'uscestheme', TEMPLATEPATH . '/languages' );
	
	register_nav_menus( array(
		'header' => __('Header Navigation', 'usces' ),
		'footer' => __('Footer Navigation', 'usces' ),
	) );
}
endif;

/***********************************************************
* welcart_page_menu_args
***********************************************************/
function welcart_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'welcart_page_menu_args' );

/***********************************************************
* sidebar
***********************************************************/
if ( function_exists('register_sidebar') ) {
	// Area 1, HomeLeft.
	register_sidebar(array(
		'name' => __( 'Home Left', 'uscestheme' ),
		'id' => 'homeleft-widget-area',
		'description' => __( 'home left sidebar widget area', 'uscestheme' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<div class="widget_title">',
		'after_title' => '</div>',
	));
	// Area 2, HomeRight.
	register_sidebar(array(
		'name' => __( 'Home Right', 'uscestheme' ),
		'id' => 'homeright-widget-area',
		'description' => __( 'home right sidebar widget area', 'uscestheme' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<div class="widget_title">',
		'after_title' => '</div>',
	));
	// Area 3, OtherLeft.
	register_sidebar(array(
		'name' => __( 'Other Left', 'uscestheme' ),
		'id' => 'otherleft-widget-area',
		'description' => __( 'other left sidebar widget area', 'uscestheme' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<div class="widget_title">',
		'after_title' => '</div>',
	));
	// Area 4, CartMemberLeft.
	register_sidebar(array(
		'name' => __( 'CartMemberLeft', 'uscestheme' ),
		'id' => 'cartmemberleft-widget-area',
		'description' => __( 'cart or member left sidebar widget area', 'uscestheme' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<div class="widget_title">',
		'after_title' => '</div>',
	));
}

/***********************************************************
* widget
***********************************************************/
add_filter('widget_categories_dropdown_args', 'welcart_categories_args');
add_filter('widget_categories_args', 'welcart_categories_args');
function welcart_categories_args( $args ){
	global $usces;
	$ids = $usces->get_item_cat_ids();
	$ids[] = USCES_ITEM_CAT_PARENT_ID;
	$args['exclude'] = $ids;
	return $args;
}
add_filter('getarchives_where', 'welcart_getarchives_where');
function welcart_getarchives_where( $r ){
	$where = "WHERE post_type = 'post' AND post_status = 'publish' AND post_mime_type <> 'item' ";
	return $where;
}
add_filter('widget_tag_cloud_args', 'welcart_tag_cloud_args');
function welcart_tag_cloud_args( $args ){
	global $usces;
	if( 'category' == $args['taxonomy']){
		$ids = $usces->get_item_cat_ids();
		$ids[] = USCES_ITEM_CAT_PARENT_ID;
		$args['exclude'] = $ids;
	}else if( 'post_tag' == $args['taxonomy']){
		$ids = $usces->get_item_post_ids();
		$tobs = wp_get_object_terms($ids, 'post_tag');
		foreach( $tobs as $ob ){
			$tids[] = $ob->term_id;
		}
		$args['exclude'] = $tids;
	}
	return $args;
}

/***********************************************************
* excerpt
***********************************************************/
if ( ! function_exists( 'welcart_assistance_excerpt_length' ) ) {
	function welcart_assistance_excerpt_length( $length ) {
		return 10;
	}
}

if ( ! function_exists( 'welcart_assistance_excerpt_mblength' ) ) {
	function welcart_assistance_excerpt_mblength( $length ) {
		return 40;
	}
}

if ( ! function_exists( 'welcart_excerpt_length' ) ) {
	function welcart_excerpt_length( $length ) {
		return 40;
	}
}
add_filter( 'excerpt_length', 'welcart_excerpt_length' );

if ( ! function_exists( 'welcart_excerpt_mblength' ) ) {
	function welcart_excerpt_mblength( $length ) {
		return 110;
	}
}
add_filter( 'excerpt_mblength', 'welcart_excerpt_mblength' );

if ( ! function_exists( 'welcart_continue_reading_link' ) ) {
	function welcart_continue_reading_link() {
		return ' <a href="'. get_permalink() . '">' . __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'uscestheme' ) . '</a>';
	}
}

if ( ! function_exists( 'welcart_auto_excerpt_more' ) ) {
	function welcart_auto_excerpt_more( $more ) {
		return ' &hellip;' . welcart_continue_reading_link();
	}
}
add_filter( 'excerpt_more', 'welcart_auto_excerpt_more' );

if ( ! function_exists( 'welcart_custom_excerpt_more' ) ) {
	function welcart_custom_excerpt_more( $output ) {
		if ( has_excerpt() && ! is_attachment() ) {
			$output .= welcart_continue_reading_link();
		}
		return $output;
	}
}
add_filter( 'get_the_excerpt', 'welcart_custom_excerpt_more' );

/***********************************************************
* SSL
***********************************************************/
if( $usces->options['use_ssl'] ){
	add_action('init', 'usces_ob_start');
	function usces_ob_start(){
		global $usces;
		if( $usces->use_ssl && ($usces->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $usces->is_inquiry_page($_SERVER['REQUEST_URI'])) )
			ob_start('usces_ob_callback');
	}
	if ( ! function_exists( 'usces_ob_callback' ) ) {
		function usces_ob_callback($buffer){
			global $usces;
			$pattern = array(
				'|(<[^<]*)href=\"'.get_option('siteurl').'([^>]*)\.css([^>]*>)|', 
				'|(<[^<]*)src=\"'.get_option('siteurl').'([^>]*>)|'
			);
			$replacement = array(
				'${1}href="'.USCES_SSL_URL_ADMIN.'${2}.css${3}', 
				'${1}src="'.USCES_SSL_URL_ADMIN.'${2}'
			);
			$buffer = preg_replace($pattern, $replacement, $buffer);
			return $buffer;
		}
	}
}

//kanpari start
/***********************************************************
* Initial setting
***********************************************************/
//define('KANPARI_TOKOFORM', 3223);//釣果投稿フォームのpost_id
define('KANPARI_TOKOFORM', 76);//釣果投稿フォームのpost_id
$kanpari_area = array(
	1 => "大阪",
	2 => "兵庫（瀬戸内海）",
	3 => "兵庫（日本海）",
	4 => "和歌山",
	5 => "京都",
	6 => "福井",
	7 => "淡路"
	);
$kanpari_area_tag = array(
	1 => "osaka",
	2 => "hyogo-setonaikai",
	3 => "hyogo-nihonkai",
	4 => "wakayama",
	5 => "kyoto",
	6 => "fukui",
	7 => "awaji"
	);
$kanpari_location[1] = array( "選択してください",
	"大阪市エリア　淀川河口",
	"大阪市エリア　舞州",
	"大阪市エリア　大阪南港海釣り公園",
	"大阪市エリア　大阪南港",
	"高石・泉大津エリア　汐見埠頭",
	"高石・泉大津エリア　助松埠頭",
	"高石・泉大津エリア　高砂埋立地",
	"岸和田・貝塚エリア　貝塚",
	"岸和田・貝塚エリア　岸和田1",
	"岸和田・貝塚エリア　岸和田2",
	"泉佐野エリア　岡田浦漁港",
	"泉佐野エリア　田尻漁港",
	"泉佐野エリア　りんくう",
	"泉佐野エリア　佐野漁港",
	"泉佐野エリア　泉佐野",
	"泉南・阪南エリア　せんなん里海公園",
	"泉南・阪南エリア　箱作",
	"泉南・阪南エリア　鳥取ノ荘",
	"泉南・阪南エリア　尾崎",
	"泉南・阪南エリア　樽井",
	"岬町エリア　小島",
	"岬町エリア　とっとパーク小島",
	"岬町エリア　谷川",
	"岬町エリア　深日",
	"岬町エリア　大阪ゴルフ場裏",
	"岬町エリア　淡輪",
	"岬町エリア　淡輪ヨットハーバー"
	);
$kanpari_location[2] = array( "選択してください",
	"尼崎～芦屋エリア　尼崎フェニックス～釣り公園",
	"尼崎～芦屋エリア　武庫川河口",
	"尼崎～芦屋エリア　西宮浜",
	"尼崎～芦屋エリア　南芦屋浜",
	"尼崎～芦屋エリア　武庫川尻一文字",
	"神戸東部エリア　神戸７防",
	"神戸東部エリア　神戸港4,5,6,8防",
	"神戸東部エリア　神戸空港",
	"神戸西部エリア　須磨1",
	"神戸西部エリア　須磨2",
	"神戸西部エリア　神戸市立須磨海づり公園",
	"神戸西部エリア　塩屋海岸",
	"神戸西部エリア　神戸市立平磯海づり公園",
	"神戸西部エリア　垂水漁港",
	"神戸西部エリア　アジュール舞子～西舞子",
	"明石東部エリア　大蔵海岸",
	"明石東部エリア　明石港",
	"明石東部エリア　新浜漁港",
	"明石東部エリア　林崎漁港",
	"明石東部エリア　松江～藤江",
	"明石東部エリア　江井ヶ島",
	"明石西部エリア　魚住漁港",
	"明石西部エリア　東二見",
	"明石西部エリア　東二見人口島",
	"播磨・高砂エリア　本荘人口島",
	"播磨・高砂エリア　加古川河口一文字～神鋼ケーソン",
	"播磨・高砂エリア　高砂港",
	"播磨・高砂エリア　伊保港",
	"姫路東部エリア　大塩漁港",
	"姫路東部エリア　的形",
	"姫路東部エリア　姫路市立遊魚センター",
	"姫路東部エリア　木場漁港",
	"姫路西部エリア　妻鹿（白浜）漁港",
	"姫路西部エリア　妻鹿沖波止",
	"姫路西部エリア　飾磨～広畑",
	"姫路西部エリア　網干浜北東面",
	"姫路西部エリア　中川、揖保川",
	"たつの・相生エリア　岩見港",
	"たつの・相生エリア　室津港",
	"たつの・相生エリア　鰯浜漁港",
	"たつの・相生エリア　野瀬埠頭",
	"たつの・相生エリア　壺根漁港",
	"赤穂市エリア　坂越漁港",
	"赤穂市エリア　坂越",
	"赤穂市エリア　松ノ鼻",
	"赤穂市エリア　福浦",
	"赤穂市エリア　古池",
	"家島エリア　家島"
	);
$kanpari_location[3] = array( "選択してください",
	"豊岡エリア　田結漁港",
	"豊岡エリア　津居山港",
	"豊岡エリア　竹野港",
	"香美エリア　柴山港",
	"香美エリア　香住東港",
	"香美エリア　香住西港",
	"香美エリア　下浜港",
	"香美エリア　余部",
	"新温泉エリア　三尾",
	"新温泉エリア　浜坂港",
	"新温泉エリア　諸寄港",
	"新温泉エリア　居組港"
	);
$kanpari_location[4] = array( "選択してください",
	"和歌山市北部エリア　城ヶ崎 ",
	"和歌山市北部エリア　加太漁港 ",
	"和歌山市北部エリア　磯ノ浦 ",
	"和歌山市北部エリア　和歌山北港魚つり公園 ",
	"和歌山市北部エリア　紀ノ川河口",
	"和歌山市南部エリア　雑賀崎 ",
	"和歌山市南部エリア　田野漁港",
	"和歌山市南部エリア　和歌浦漁港 ",
	"和歌山市南部エリア　和歌川河口",
	"和歌山市南部エリア　和歌山マリーナシティ ",
	"海南エリア　戸坂・塩津漁港 ",
	"海南エリア　つり公園シモツピアーランド",
	"海南エリア　牛ヶ首周辺 ",
	"有田エリア　沖ノ島 地ノ島 ",
	"有田エリア　有田川一文字 ",
	"有田エリア　矢ひつ漁港 ",
	"有田エリア　逢井漁港 ",
	"有田エリア　千田漁港",
	"湯浅・広川エリア　田村漁港 ",
	"湯浅・広川エリア　栖原漁港",
	"湯浅・広川エリア　湯浅広港",
	"湯浅・広川エリア　唐尾漁港 ",
	"由良北部エリア　三尾川漁港 ",
	"由良北部エリア　衣奈漁港 ",
	"由良北部エリア　戸津井・小引漁港 ",
	"由良南部エリア　大引漁港 ",
	"由良南部エリア　神谷漁港 ",
	"由良南部エリア　由良海つり公園 ",
	"由良南部エリア　造船所・年金波止",
	"由良南部エリア　網代新波止",
	"日高エリア　柏漁港 ",
	"日高エリア　小杭・方杭漁港",
	"日高エリア　小浦漁港 ",
	"日高エリア　比井漁港 ",
	"日高エリア　産湯漁港",
	"日高エリア　阿尾漁港 ",
	"日高エリア　田杭漁港",
	"美浜エリア　三尾漁港",
	"美浜エリア　潮吹岩",
	"美浜エリア　煙樹ヶ浜",
	"美浜エリア　濱ノ瀬漁港",
	"御坊エリア　南塩谷 ",
	"御坊エリア　祓井戸漁港 ",
	"御坊エリア　野島・加尾漁港",
	"御坊エリア　上野漁港 ",
	"御坊エリア　楠井漁港 ",
	"印南エリア　津井の波止 ",
	"印南エリア　印南港 ",
	"印南エリア　切目川河口",
	"みなべエリア　岩代 ",
	"みなべエリア　千里浜  ",
	"みなべエリア　南部 ",
	"みなべエリア　堺・一本松漁港 ",
	"田辺エリア　芳養漁港 ",
	"田辺エリア　目良漁港 ",
	"田辺エリア　田辺・戎漁港 ",
	"田辺エリア　磯間港 ",
	"田辺エリア　跡之浦港",
	"白浜北部エリア　東白浜 ",
	"白浜北部エリア　田辺沖磯",
	"白浜北部エリア　白浜周辺 ",
	"白浜北部エリア　富田川河口",
	"白浜北部エリア　見草漁港",
	"白浜北部エリア　朝来帰港",
	"白浜南部エリア　市江港",
	"白浜南部エリア　村島磯",
	"白浜南部エリア　日置川河口",
	"白浜南部エリア　伊古木漁港",
	"すさみエリア　すさみ港",
	"すさみエリア　口和深",
	"すさみエリア　くろしお牧場周辺",
	"すさみエリア　見老津漁港",
	"すさみエリア　江住漁港",
	"串本西部エリア　和深港",
	"串本西部エリア　安指漁港",
	"串本西部エリア　田子の浦",
	"串本西部エリア　田並漁港",
	"串本西部エリア　有田漁港",
	"串本東部エリア　袋港 上浦漁港",
	"串本東部エリア　萩尾",
	"串本東部エリア　串本港",
	"串本東部エリア　橋杭海水浴場",
	"串本東部エリア　古座川河口",
	"串本東部エリア　紀伊大島",
	"串本東部エリア　田原港",
	"那智勝浦エリア　浦神湾",
	"那智勝浦エリア　粉白",
	"那智勝浦エリア　太地港",
	"那智勝浦エリア　太地くじら浜公園",
	"那智勝浦エリア　勝浦港",
	"那智勝浦エリア　那智堤防",
	"那智勝浦エリア　宇久井港",
	"新宮エリア　新宮港",
	"新宮エリア　熊野川河口"
	);
$kanpari_location[5] = array( "選択してください",
	"京丹後西部エリア　旭",
	"京丹後西部エリア　久美浜",
	"京丹後西部エリア　箱石",
	"京丹後西部エリア　夕日港",
	"京丹後中部エリア　浅茂川",
	"京丹後中部エリア　琴引浜",
	"京丹後中部エリア　砂方",
	"京丹後中部エリア　間人",
	"京丹後東部エリア　竹野",
	"京丹後東部エリア　犬ヶ崎",
	"京丹後東部エリア　平海水浴場",
	"京丹後東部エリア　久僧・中浜",
	"伊根エリア　蒲入",
	"伊根エリア　本庄浜",
	"伊根エリア　新井崎",
	"伊根エリア　伊根1",
	"伊根エリア　伊根2",
	"伊根エリア　伊根3",
	"宮津西部エリア　養老",
	"宮津西部エリア　長江",
	"宮津西部エリア　日置",
	"宮津西部エリア　江尻",
	"宮津西部エリア　天橋立",
	"宮津西部エリア　獅子",
	"宮津東部エリア　矢原・田井",
	"宮津東部エリア　夢の浜",
	"宮津東部エリア　島陰",
	"宮津東部エリア　海洋つり場",
	"宮津東部エリア　中津",
	"宮津東部エリア　由良川河口",
	"舞鶴西部エリア　戸島",
	"舞鶴西部エリア　白杉",
	"舞鶴西部エリア　青井",
	"舞鶴西部エリア　大君",
	"舞鶴西部エリア　喜多",
	"舞鶴東部エリア　白灯台",
	"舞鶴東部エリア　三本松鼻",
	"舞鶴東部エリア　千歳",
	"舞鶴東部エリア　小橋",
	"舞鶴東部エリア　野原",
	"舞鶴東部エリア　田井",
	"舞鶴東部エリア　水ヶ浦"
	);
$kanpari_location[6] = array( "選択してください",
	"高浜北部エリア　上瀬",
	"高浜北部エリア　日引",
	"高浜北部エリア　宮尾",
	"高浜北部エリア　音海大波止",
	"高浜北部エリア　音海学校裏",
	"高浜南部エリア　小黒飯～難波江",
	"高浜南部エリア　西三松",
	"高浜南部エリア　高浜",
	"高浜南部エリア　和田",
	"おおいエリア　赤礁",
	"おおいエリア　袖ヶ浜",
	"おおいエリア　犬見～本郷",
	"小浜エリア　鯉川",
	"小浜エリア　小浜港",
	"小浜エリア　宇久",
	"小浜エリア　阿納～犬熊",
	"小浜エリア　志積～矢代",
	"小浜エリア　田烏～釣姫",
	"若狭エリア　食見",
	"若狭エリア　世久見",
	"若狭エリア　塩坂越",
	"若狭エリア　遊子",
	"若狭エリア　小川",
	"若狭エリア　神子",
	"若狭エリア　常神",
	"美浜エリア　日向",
	"美浜エリア　早瀬",
	"美浜エリア　久々子",
	"美浜エリア　坂尻",
	"美浜エリア　菅浜",
	"美浜エリア　水晶浜",
	"美浜エリア　丹生",
	"敦賀西部エリア　白木",
	"敦賀西部エリア　立石",
	"敦賀西部エリア　浦底",
	"敦賀西部エリア　手の浦",
	"敦賀西部エリア　沓～名子",
	"敦賀東部エリア　気比の松原",
	"敦賀東部エリア　敦賀港",
	"敦賀東部エリア　敦賀新港",
	"敦賀東部エリア　赤崎"
	);
$kanpari_location[7] = array( "選択してください",
	"松帆～岩屋エリア　松帆の浦",
	"松帆～岩屋エリア　松帆",
	"松帆～岩屋エリア　岩屋",
	"松帆～岩屋エリア　大和島周辺",
	"鵜崎～浦エリア　翼港",
	"鵜崎～浦エリア　大磯",
	"鵜崎～浦エリア　浦",
	"鵜崎～浦エリア　浦サンビーチ",
	"久留麻～釜口エリア　久留間",
	"久留麻～釜口エリア　仮屋",
	"久留麻～釜口エリア　野田",
	"佐野～塩尾エリア　佐野",
	"佐野～塩尾エリア　生穂",
	"佐野～塩尾エリア　志筑",
	"佐野～塩尾エリア　おのころ愛ランド周辺",
	"佐野～塩尾エリア　塩尾",
	"安乎～洲本エリア　平安浦",
	"安乎～洲本エリア　厚浜",
	"安乎～洲本エリア　水の大師",
	"安乎～洲本エリア　炬口",
	"安乎～洲本エリア　洲本",
	"安乎～洲本エリア　古茂江",
	"由良エリア　新川口",
	"由良エリア　由良",
	"由良エリア　生石崎",
	"灘エリア　黒岩",
	"灘エリア　城方",
	"灘エリア　土生",
	"灘エリア　沼島",
	"仁頃～田尻エリア　仁頃",
	"仁頃～田尻エリア　丸田",
	"仁頃～田尻エリア　阿万川尻",
	"仁頃～田尻エリア　吹上浜",
	"仁頃～田尻エリア　押登岬",
	"仁頃～田尻エリア　田尻",
	"福良～丸山エリア　福良湾",
	"福良～丸山エリア　鳥取",
	"福良～丸山エリア　伊毘",
	"福良～丸山エリア　阿那賀",
	"福良～丸山エリア　木場",
	"福良～丸山エリア　丸山",
	"津井～船瀬エリア　津井",
	"津井～船瀬エリア　湊",
	"津井～船瀬エリア　慶野松原",
	"津井～船瀬エリア　五色の浜",
	"津井～船瀬エリア　鳥飼",
	"津井～船瀬エリア　船瀬",
	"都志～明神エリア　都志",
	"都志～明神エリア　五斗崎",
	"都志～明神エリア　明神",
	"江井～尾崎エリア　江井",
	"江井～尾崎エリア　多賀",
	"江井～尾崎エリア　群家",
	"江井～尾崎エリア　尾崎",
	"江井～尾崎エリア　枯木",
	"室津～富島エリア　室津",
	"室津～富島エリア　育波",
	"室津～富島エリア　斗の内",
	"室津～富島エリア　富島",
	"蟇浦～江崎エリア　蟇浦",
	"蟇浦～江崎エリア　大石",
	"蟇浦～江崎エリア　平林",
	"蟇浦～江崎エリア　江崎"
	);
$kanpari_weather = array( "選択してください", "晴れ", "曇り", "雨", "雪" );
$kanpari_temperature = array( "選択してください", "暑い", "やや暑い", "快適", "やや寒い", "寒い" );
$kanpari_tide = array( "選択してください", "大潮", "中潮", "小潮", "長潮", "若潮" );
$kanpari_timezone = array( "選択してください", "朝", "昼", "夕方", "夜" );

/***********************************************************
* form
***********************************************************/
add_action('template_redirect', 'my_template_redirect');
function my_template_redirect(){
	global $post, $usces;
	if( !is_page() || KANPARI_TOKOFORM != $post->ID )
		return;

	if( !usces_is_login() ) {
		$tokoform = 'postform_top.php';
		if( file_exists(get_stylesheet_directory() . '/wc_templates/member/wc_login_page.php') ){
			include(get_stylesheet_directory() . '/wc_templates/member/wc_login_page.php');
			exit;
		}
	}

	$template_dir = get_stylesheet_directory(). '/';
	if( !file_exists($template_dir . 'postform_top.php') 
		|| !file_exists($template_dir . 'postform.php')
		|| !file_exists($template_dir . 'postform_confirm.php')
		|| !file_exists($template_dir . 'postform_complete.php') )
		return;

	$data = array();
	$area = isset($_REQUEST['area']) ? $_REQUEST['area'] : '';
	$action = isset($_REQUEST['entry_action']) ? $_REQUEST['entry_action'] : '';
	$error_message = my_check_post( $data, $action );

	switch( $action ){
		case 'confirm':
			if( $error_message ){
				include($template_dir . 'postform.php');
			}else{
				if( !my_is_iphone() ) my_file_uploads( $data );
				include($template_dir . 'postform_confirm.php');
			}
			exit;
			break;
		case 'send':
			if( $error_message ){
				include($template_dir . 'postform.php');
			}else{
				if( my_sendmail( $data ) ){
					my_reg_postform( $data );
					include($template_dir . 'postform_complete.php');
				}else{
					include($template_dir . 'postform_senderror.php');
				}
			}
			exit;
			break;
		case 'edit':
		case 'form':
			$member = $usces->get_member();
			if( empty($data['name1']) ) $data['name1'] = $member['name1'];
			if( empty($data['name2']) ) $data['name2'] = $member['name2'];
			if( empty($data['email']) ) $data['email'] = $member['mailaddress1'];
			if( empty($data['zipcode']) ) $data['zipcode'] = $member['zipcode'];
			if( empty($data['pref']) ) $data['pref'] = $member['pref'];
			if( empty($data['address1']) ) $data['address1'] = $member['address1'];
			if( empty($data['address2']) ) $data['address2'] = $member['address2'];
			if( empty($data['address3']) ) $data['address3'] = $member['address3'];
			include($template_dir . 'postform.php');
			exit;
			break;
		default:
			$error_message = '';
			include($template_dir . 'postform_top.php');
			exit;
	}
}

function my_check_post( &$data, $action ){
	$message = '';
	$pre = "<li>";
	$end = "</li>\n";

	$data['name1'] = isset($_POST['name1']) ? trim($_POST['name1']) : '';
	$data['name2'] = isset($_POST['name2']) ? trim($_POST['name2']) : '';
	$data['handle'] = isset($_POST['handle']) ? trim($_POST['handle']) : '';
	$data['email'] = isset($_POST['email']) ? trim($_POST['email']) : '';
	$data['zipcode'] = isset($_POST['zipcode']) ? trim($_POST['zipcode']) : '';
	$data['pref'] = isset($_POST['pref']) ? trim($_POST['pref']) : '';
	$data['address1'] = isset($_POST['address1']) ? trim($_POST['address1']) : '';
	$data['address2'] = isset($_POST['address2']) ? trim($_POST['address2']) : '';
	$data['address3'] = isset($_POST['address3']) ? trim($_POST['address3']) : '';
	$data['area'] = isset($_POST['area']) ? trim($_POST['area']) : '';
	$data['location'] = isset($_POST['location']) ? trim($_POST['location']) : '';
	$data['fishingdate'] = isset($_POST['fishingdate']) ? trim($_POST['fishingdate']) : '';
	$data['weather'] = isset($_POST['weather']) ? trim($_POST['weather']) : '';
	$data['temperature'] = isset($_POST['temperature']) ? trim($_POST['temperature']) : '';
	$data['tide'] = isset($_POST['tide']) ? trim($_POST['tide']) : '';
	$data['timezone'] = isset($_POST['timezone']) ? trim($_POST['timezone']) : '';
	$data['style'] = isset($_POST['style']) ? trim($_POST['style']) : '';
	$data['fishing'] = isset($_POST['fishing']) ? trim($_POST['fishing']) : '';
	$data['usetackle'] = isset($_POST['usetackle']) ? trim($_POST['usetackle']) : '';
	$data['comment'] = isset($_POST['comment']) ? trim($_POST['comment']) : '';
	if( my_is_iphone() ) {
		$data['image1'] = '';
		$data['image2'] = '';
	} else {
		if( $action == 'confirm' ) {
			$data['image1'] = isset($_FILES['image1']['name']) ? trim($_FILES['image1']['name']) : '';
			$data['image2'] = isset($_FILES['image2']['name']) ? trim($_FILES['image2']['name']) : '';
		} else {
			$data['image1'] = isset($_POST['image1']) ? trim($_POST['image1']) : '';
			$data['image2'] = isset($_POST['image2']) ? trim($_POST['image2']) : '';
		}
	}

	if( $action == 'confirm' ) {
		if( '' == $data['name1'] || '' == $data['name2'] ){
			$message .= $pre.'お名前を入力してください。'.$end;
		}
		if( empty( $data['email'] ) ){
			$message .= $pre.'メールアドレスを入力してください。'.$end;
		}else if( !is_email( $data['email'] ) ){
			$message .= $pre.'メールアドレスが不正です。'.$end;
		}
		if( empty( $data['zipcode'] ) ){
			$message .= $pre.'郵便番号を入力してください。'.$end;
		}else if( !preg_match('/^[0-9]{3}\-[0-9]{4}$/', $data['zipcode']) ){
			$message .= $pre.'郵便番号が不正です。'.$end;
		}
		if( '--選択--' == $data['pref'] ){
			$message .= $pre.'都道府県を選択してください。'.$end;
		}
		if( '' == $data['address1'] ){
			$message .= $pre.'市区町村以下を入力してください。'.$end;
		}
		if( '' == $data['address2'] ){
			$message .= $pre.'番地を入力してください。'.$end;
		}
		if( '選択してください' == $data['location'] ){
			$message .= $pre.'釣行場所を選択してください。'.$end;
		}
		if( '' == $data['fishingdate'] ){
			$message .= $pre.'釣行日を入力してください。'.$end;
		}
		if( '選択してください' == $data['weather'] ){
			$message .= $pre.'天気を選択してください。'.$end;
		}
		if( '選択してください' == $data['temperature'] ){
			$message .= $pre.'気温を選択してください。'.$end;
		}
		if( '選択してください' == $data['timezone'] ){
			$message .= $pre.'時間帯を選択してください。'.$end;
		}
		if( '' == $data['style'] ){
			$message .= $pre.'釣り方を入力してください。'.$end;
		}
		if( '' == $data['fishing'] ){
			$message .= $pre.'釣果を入力してください。'.$end;
		}
		if( '' == $data['comment'] ){
			$message .= $pre.'釣行レポートを入力してください。'.$end;
		}
		if( !my_is_iphone() ) {
			if( '' == $data['image1'] ){
				$message .= $pre.'釣果画像01を選択してください。'.$end;
			}else if( !my_image_type_check( $_FILES['image1']['name'] ) ) {
				$message .= $pre.'釣果画像01が不正です。指定できる画像の種類は『JPG』『GIF』『PNG』のみです。'.$end;
			}else if( !my_image_size_check( $_FILES['image1']['name'], $_FILES['image1']['size'] ) ) {
				$message .= $pre.'釣果画像01が大きすぎます。4MBまでの画像を指定してください。'.$end;
			}
			if( '' == $data['image2'] ){
				$message .= $pre.'釣果画像02を選択してください。'.$end;
			}else if( !my_image_type_check( $_FILES['image2']['name'] ) ) {
				$message .= $pre.'釣果画像02が不正です。指定できる画像の種類は『JPG』『GIF』『PNG』のみです。'.$end;
			}else if( !my_image_size_check( $_FILES['image2']['name'], $_FILES['image2']['size'] ) ) {
				$message .= $pre.'釣果画像02が大きすぎます。4MBまでの画像を指定してください。'.$end;
			}
			if( '' != $data['image1'] && '' == $data['image2'] && $data['image1'] == $data['image2'] ){
				$message .= $pre.'釣果画像01と釣果画像02は別の画像を選択してください。'.$end;
			}
		}
	}
	return $message;
}

function my_change_br( $str ) {

	$text = htmlspecialchars( $str );
	if( get_magic_quotes_gpc() ) {
		$text = stripslashes( $text );
	}
	$text = nl2br($text);
	return $text;
}

function my_image_type_check( $name ) {
	$res = true;
	$allowedExtensions = array("jpg","jpeg","gif","png");
	if( $name > '') {
		$res = in_array(end(explode(".", strtolower($name))), $allowedExtensions);
	}
	return $res;
}

function my_image_size_check( $name, $size ) {
	$res = true;
	if( $name > '') {
		if( $size > (4*1024*1024) )
			$res = false;
    }
	return $res;
}

function my_file_uploads( &$data ) {
	global $usces;

	$member = $usces->get_member();
	$uploads_dir = WP_CONTENT_DIR.'/uploads/kanpari/'.$member['ID'];
	$uploads_url = WP_CONTENT_URL.'/uploads/kanpari/'.$member['ID'];
	if( !is_dir($uploads_dir) ) {
		mkdir($uploads_dir);
	}

	if( isset($_FILES["image1"]["tmp_name"]) ) {
		$tmp_name1 = $_FILES["image1"]["tmp_name"];
		$name1 = $uploads_dir.'/'.$_FILES["image1"]["name"];
		$name1_url = $uploads_url.'/'.$_FILES["image1"]["name"];
		@move_uploaded_file($tmp_name1, $name1);
		@chmod( $name1, 0400 );
		$data['display_image1'] = '<img src="'.$name1_url.'" alt="'.esc_html($data['image1']).'" height="100" />';
	}

	if( isset($_FILES["image2"]["tmp_name"]) ) {
		$tmp_name2 = $_FILES["image2"]["tmp_name"];
		$name2 = $uploads_dir.'/'.$_FILES["image2"]["name"];
		$name2_url = $uploads_url.'/'.$_FILES["image2"]["name"];
		@move_uploaded_file($tmp_name2, $name2);
		@chmod( $name2, 0400 );
		$data['display_image2'] = '<img src="'.$name2_url.'" alt="'.esc_html($data['image2']).'" height="100" />';
	}
}

function my_sendmail( $data ){
	global $usces;

	$name = $data['name1'].$data['name2'];
	$mail_data = $usces->options['mail_data'];
	$member = $usces->get_member();

	$res = false;
	$mes_head  = "このメールは自動送信されています。" ."\r\n";
	$mes_head .= "-------------------------------------------------------" ."\r\n\r\n";
	$mes_head .= $name . " 様" ."\r\n\r\n";
	$mes_head .= "この度は、釣果投稿ありがとうございます。" ."\r\n\r\n\r\n";
	$mes_head .= "下記の内容で受け付けました。" ."\r\n";
	$mes_body  = my_get_mes_body($data);
	$mes_foot  = '改めてメールでご連絡いたしますのでしばらくお待ちください。' ."\r\n\r\n";
	$mes_foot .= '今後とも'.$usces->options['company_name'].'をよろしくお願い申し上げます。' ."\r\n\r\n\r\n";
	$mes_foot .= '※このメールにお心当たりのないお客様は、お手数ですが' ."\r\n";
	$mes_foot .= '下記のお問合せ先よりご連絡下さいますようお願い致します。' ."\r\n\r\n\r\n";
	$mes_foot .= $mail_data['footer']['othermail'];//メール設定→その他のフッタ

	$entry_name = $name;
	$entry_mailaddress = $data['email'];
	$subject2applicant = "(".$member['ID'].")".$name."様からの釣果投稿完了の確認";//会員向け件名

	$admin_name = $usces->options['company_name'];
	$admin_mailaddress = $usces->options['sender_mail'];
	$subject2admin = "(".$member['ID'].")".$name."様からの釣果投稿通知";//管理者向け件名

	$para2applicant = array(
			'to_name' => $entry_name,
			'to_address' => $entry_mailaddress, 
			'from_name' => $admin_name,
			'from_address' => $admin_mailaddress,
			'return_path' => $admin_mailaddress,
			'subject' => $subject2applicant,
			'message' => $mes_head . $mes_body . $mes_foot
			);
	$entry_res = my_send_mail( $para2applicant );

	if ( $entry_res ) {
		$mes_head = $name . " 様より釣果投稿がありました。" ."\r\n\r\n";
		$para2admin = array(
				'to_name' => $admin_name,
				'to_address' => $admin_mailaddress, 
				'from_name' => $entry_name . ' 様',
				'from_address' => $entry_mailaddress,
				'return_path' => $admin_mailaddress,
				'subject' => $subject2admin,
				'message' => $mes_head . $mes_body . $mes_foot
				);
		$member = $usces->get_member();
		if( my_is_iphone() ) {
			$attachments = array();
		} else {
			$uploads_dir = WP_CONTENT_DIR.'/uploads/kanpari/'.$member['ID'];
			$attachments = array( $uploads_dir.'/'.$data['image1'], $uploads_dir.'/'.$data['image2'] );
		}
		sleep(1);
		$res = my_send_mail( $para2admin, $attachments );
		if( $res ) {
			my_remove_dir( $uploads_dir, true );
		}
	}

	return $res;
}

function my_remove_dir($dir, $deleteMe) {
	if( !$dh = @opendir($dir) ) return;
	while( false !== ($obj = readdir($dh)) ) {
		if( $obj == '.' || $obj == '..' ) continue;
		if( !@unlink($dir.'/'.$obj) ) my_remove_dir( $dir.'/'.$obj, true );
	}

	closedir($dh);
	if($deleteMe) {
		@rmdir($dir);
	}
}

function my_reg_postform( $data ){
	global $wpdb, $usces;
	global $kanpari_area;

	$member = $usces->get_member();
	$table_name = $wpdb->prefix."usces_postform";
	$query = $wpdb->prepare(
		"INSERT INTO $table_name (
			`mem_id`, `kpf_date`, `kpf_handle`, `kpf_area`, `kpf_location`, `kpf_fishingdate`, `kpf_weather`, 
			`kpf_temperature`, `kpf_tide`, `kpf_timezone`, `kpf_style`, `kpf_fishing`, 
			`kpf_usetackle`, `kpf_comment`, `kpf_image1`, `kpf_image2`, `kpf_status` ) 
		VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", 
			$member['ID'], 
			get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 
			$data['handle'], 
			$kanpari_area[$data['area']], 
			$data['location'], 
			$data['fishingdate'], 
			$data['weather'], 
			$data['temperature'], 
			$data['tide'], 
			$data['timezone'], 
			$data['style'], 
			$data['fishing'], 
			$data['usetackle'], 
			$data['comment'], 
			$data['image1'], 
			$data['image2'], 
			"未対応"
		);
	$res = $wpdb->query( $query );
	return $res;
}

function my_send_mail( $para, $attachments = array() ) {
	//$from_name = mb_encode_mimeheader(mb_convert_encoding($para['from_name'], "JIS", "UTF8"));
	//$from = $from_name . " <{$para['from_address']}>";
	$from = htmlspecialchars(html_entity_decode($para['from_name'], ENT_QUOTES)) . " <{$para['from_address']}>";
	$header = "From: " . $from . "\r\n";
	$header .= "Return-Path: {$para['return_path']}\r\n";

	$subject = html_entity_decode($para['subject'], ENT_QUOTES);
	$message = $para['message'];

	$mails = explode( ',', $para['to_address'] );
	$to_mailes = array();
	foreach( $mails as $mail ){
		if( is_email( trim($mail) ) ){
			$to_mailes[] = $mail;
		}
	}
	if( !empty( $to_mailes ) ){
		$res = @wp_mail( $to_mailes, $subject, $message, $header, $attachments );
	}else{
		$res = false;
	}
	return $res;
}

function my_get_mes_body($data){
	global $kanpari_area;

	$mes = "=========================================" ."\r\n";
	$mes .= "[       お名前       ]  " . $data['name1'].$data['name2'] ."\r\n\r\n";
	$mes .= "[   ハンドルネーム   ]  " . $data['handle'] ."\r\n\r\n";
	$mes .= "[   メールアドレス   ]  " . $data['email'] ."\r\n\r\n";
	$mes .= "[      郵便番号      ]  " . $data['zipcode'] ."\r\n\r\n";
	$mes .= "[      都道府県      ]  " . $data['pref'] ."\r\n\r\n";
	$mes .= "[     市区郡町村     ]  " . $data['address1'] ."\r\n\r\n";
	$mes .= "[        番地        ]  " . $data['address2'] ."\r\n\r\n";
	$mes .= "[ マンション・ビル名 ]  " . $data['address3'] . "\r\n\r\n";
	$mes .= "[     釣行エリア     ]  " . $kanpari_area[$data['area']] . "\r\n\r\n";
	$mes .= "[      釣行場所      ]  " . $data['location'] . "\r\n\r\n";
	$mes .= "[       釣行日       ]  " . $data['fishingdate'] . "\r\n\r\n";
	$mes .= "[        天気        ]  " . $data['weather'] ."\r\n\r\n";
	$mes .= "[        気温        ]  " . $data['temperature'] ."\r\n\r\n";
	$mes .= "[         潮         ]  " . $data['tide'] ."\r\n\r\n";
	$mes .= "[       時間帯       ]  " . $data['timezone'] ."\r\n\r\n";
	$mes .= "[       釣り方       ]  " . $data['style'] ."\r\n\r\n";
	$mes .= "[        釣果        ]  " . $data['fishing'] ."\r\n\r\n";
	$mes .= "[    使用タックル    ]  " . $data['usetackle'] ."\r\n\r\n";
	$mes .= "[    釣行レポート    ]  " . $data['comment'] ."\r\n\r\n";
	if( my_is_iphone() ) {
		$mes .= "[      釣果画像      ]  ※iPhoneのため別送信\r\n\r\n";
	} else {
		$mes .= "[     釣果画像01     ]  " . $data['image1'] ."\r\n\r\n";
		$mes .= "[     釣果画像02     ]  " . $data['image2'] ."\r\n\r\n";
	}
	$mes .= "=========================================" ."\r\n\r\n\r\n";

	return $mes;
}

function usces_update_postformdata() {
	global $wpdb;

	$table_name = $wpdb->prefix."usces_postform";
	$ID = (int)$_REQUEST['kpf_id'];
	$query = $wpdb->prepare(
		"UPDATE $table_name SET kpf_point = %d, kpf_status = %s, kpf_note = %s WHERE ID = %d", 
			(int)$_POST['kpf_point'], 
			$_POST['kpf_status'], 
			$_POST['kpf_note'], 
			$ID
		);
	$res = $wpdb->query( $query );

	if( $res ) {
		if( (int)$_POST['kpf_point'] > 0 && (int)$_POST['kpf_point'] != (int)$_POST['kpf_point_before'] ) {
			$member_table_name = $wpdb->prefix."usces_member";
			$mquery = $wpdb->prepare(
				"UPDATE $member_table_name SET mem_point = (mem_point + %d) WHERE ID = %d", 
				(int)$_POST['kpf_point'], 
				$_POST['mem_id']
			);
			$res = $wpdb->query( $mquery );
		}
	}

	return $res;
}

function usces_delete_postformdata( $ID = 0 ) {
	global $wpdb, $usces;

	if( 0 === $ID ) {
		if( !isset($_REQUEST['kpf_id']) || $_REQUEST['kpf_id'] == '' )
			return 0;
		$ID = $_REQUEST['kpf_id'];
	}

	$table_name = $wpdb->prefix."usces_postform";
	$query = $wpdb->prepare( "DELETE FROM $table_name WHERE ID = %d", $ID );
	$res = $wpdb->query( $query );

	return $res;
}

function my_is_iphone() {
	return preg_match( '/iphone/i', $_SERVER['HTTP_USER_AGENT'] ) ||
		preg_match( '/ipad/i', $_SERVER['HTTP_USER_AGENT'] );
}
//kanpari end
?>
