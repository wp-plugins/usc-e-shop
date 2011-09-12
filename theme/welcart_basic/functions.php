<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Pop Cloud Blue Theme
 */
if(!defined('USCES_VERSION')) return;

/***********************************************************
* content_width
***********************************************************/
if ( ! isset( $content_width ) )
	$content_width = 704;

/***********************************************************
* welcart_setup
***********************************************************/
add_action( 'after_setup_theme', 'welcart_setup' );

if ( ! function_exists( 'welcart_setup' ) ):
function welcart_setup() {
		
	add_editor_style();

	add_theme_support( 'post-thumbnails' );

	//set_post_thumbnail_size( HEADER_IMAGE_WIDTH, HEADER_IMAGE_HEIGHT, true );

	add_theme_support( 'automatic-feed-links' );

	load_theme_textdomain( 'uscestheme', TEMPLATEPATH . '/languages' );
	
	add_custom_background();
	
	define( 'HEADER_TEXTCOLOR', '' );
	define( 'HEADER_IMAGE', '%s/images/headers/path.jpg' );
	define( 'HEADER_IMAGE_WIDTH', apply_filters( 'welcart_header_image_width', 940 ) );
	define( 'HEADER_IMAGE_HEIGHT', apply_filters( 'welcart_header_image_height', 198 ) );
	define( 'NO_HEADER_TEXT', true );
	
	add_custom_image_header( '' , 'welcart_admin_header_style' );

	register_default_headers( array(
		'berries' => array(
			'url' => '%s/images/headers/berries.jpg',
			'thumbnail_url' => '%s/images/headers/berries-thumbnail.jpg',
			'description' => __( 'Berries', 'twentyten' )
		),
		'cherryblossom' => array(
			'url' => '%s/images/headers/cherryblossoms.jpg',
			'thumbnail_url' => '%s/images/headers/cherryblossoms-thumbnail.jpg',
			'description' => __( 'Cherry Blossoms', 'twentyten' )
		),
		'concave' => array(
			'url' => '%s/images/headers/concave.jpg',
			'thumbnail_url' => '%s/images/headers/concave-thumbnail.jpg',
			'description' => __( 'Concave', 'twentyten' )
		),
		'fern' => array(
			'url' => '%s/images/headers/fern.jpg',
			'thumbnail_url' => '%s/images/headers/fern-thumbnail.jpg',
			'description' => __( 'Fern', 'twentyten' )
		),
		'forestfloor' => array(
			'url' => '%s/images/headers/forestfloor.jpg',
			'thumbnail_url' => '%s/images/headers/forestfloor-thumbnail.jpg',
			'description' => __( 'Forest Floor', 'twentyten' )
		),
		'inkwell' => array(
			'url' => '%s/images/headers/inkwell.jpg',
			'thumbnail_url' => '%s/images/headers/inkwell-thumbnail.jpg',
			'description' => __( 'Inkwell', 'twentyten' )
		),
		'path' => array(
			'url' => '%s/images/headers/path.jpg',
			'thumbnail_url' => '%s/images/headers/path-thumbnail.jpg',
			'description' => __( 'Path', 'twentyten' )
		),
		'sunset' => array(
			'url' => '%s/images/headers/sunset.jpg',
			'thumbnail_url' => '%s/images/headers/sunset-thumbnail.jpg',
			'description' => __( 'Sunset', 'twentyten' )
		)
	) );

	register_nav_menus( array(
		'header' => __('Header Navigation', 'usces' ),
		'footer' => __('Footer Navigation', 'usces' ),
	) );
}
endif;

/***********************************************************
* welcart_admin_header_style
***********************************************************/
if ( ! function_exists( 'welcart_admin_header_style' ) ) :
function welcart_admin_header_style() {
?>
<style type="text/css">
#headimg {
	border-bottom: 1px solid #000;
	border-top: 4px solid #000;
}
</style>
<?php
}
endif;

/***********************************************************
* wp_page_menu() : welcart_page_menu_args
***********************************************************/
function welcart_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'welcart_page_menu_args' );

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
* gallery_style
***********************************************************/
function welcart_remove_gallery_css( $css ) {
	return preg_replace( "#<style type='text/css'>(.*?)</style>#s", '', $css );
}
add_filter( 'gallery_style', 'welcart_remove_gallery_css' );

/***********************************************************
* comment
***********************************************************/
if ( ! function_exists( 'welcart_comment' ) ) :
function welcart_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case '' :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<div id="comment-<?php comment_ID(); ?>">
		<div class="comment-author vcard">
			<?php echo get_avatar( $comment, 40 ); ?>
			<?php printf( __( '%s <span class="says">says:</span>', 'uscestheme' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
		</div><!-- .comment-author .vcard -->
		<?php if ( $comment->comment_approved == '0' ) : ?>
			<em><?php _e( 'Your comment is awaiting moderation.', 'uscestheme' ); ?></em>
			<br />
		<?php endif; ?>

		<div class="comment-meta commentmetadata"><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
			<?php
				/* translators: 1: date, 2: time */
				printf( __( '%1$s at %2$s', 'uscestheme' ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(Edit)', 'uscestheme' ), ' ' );
			?>
		</div><!-- .comment-meta .commentmetadata -->

		<div class="comment-body"><?php comment_text(); ?></div>

		<div class="reply">
			<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
		</div><!-- .reply -->
	</div><!-- #comment-##  -->

	<?php
			break;
		case 'pingback'  :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'uscestheme' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __('(Edit)', 'uscestheme'), ' ' ); ?></p>
	<?php
			break;
	endswitch;
}
endif;

/***********************************************************
* widgets
***********************************************************/
function welcart_widgets_init() {
	// Home
	register_sidebar(array(
		'name' => __( 'Home Widget Area', 'uscestheme' ),
		'id' => 'home-widget-area',
		'description' => __( 'home widget area', 'uscestheme' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="title">',
		'after_title' => '</h3>',
	));

	// Area 1, sidebar.
	register_sidebar(array(
		'name' => __( 'Sidebar Widget Area', 'uscestheme' ),
		'id' => 'primary-widget-area',
		'description' => __( 'sidebar widget area', 'uscestheme' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<div class="widget_title">',
		'after_title' => '</div>',
	));

	// Area 2, sidebar-content-first.
	register_sidebar( array(
		'name' => __( 'First Content Widget Area', 'uscestheme' ),
		'id' => 'first-content-widget-area',
		'description' => __( 'The first content widget area', 'uscestheme' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<div class="widget_title">',
		'after_title' => '</div>',
	) );

	// Area 3, sidebar-content-second.
	register_sidebar( array(
		'name' => __( 'Second Content Widget Area', 'uscestheme' ),
		'id' => 'second-content-widget-area',
		'description' => __( 'The second content widget area', 'uscestheme' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<div class="widget_title">',
		'after_title' => '</div>',
	) );

	// Area 4, sidebar-content-third.
	register_sidebar( array(
		'name' => __( 'Third Content Widget Area', 'uscestheme' ),
		'id' => 'third-content-widget-area',
		'description' => __( 'The third content widget area', 'uscestheme' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<div class="widget_title">',
		'after_title' => '</div>',
	) );

	// Area 5, sidebar(cart or member).
	register_sidebar(array(
		'name' => __( 'Widget Area for Cart or Member', 'uscestheme' ),
		'id' => 'cartmemberleft-widget-area',
		'description' => __( 'widget area for cart or member', 'uscestheme' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<div class="widget_title">',
		'after_title' => '</div>',
	));
}
add_action( 'widgets_init', 'welcart_widgets_init' );

/***********************************************************
* welcart_remove_recent_comments_style
***********************************************************/
function welcart_remove_recent_comments_style() {
	global $wp_widget_factory;
	remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
}
add_action( 'widgets_init', 'welcart_remove_recent_comments_style' );

/***********************************************************
* welcart_posted_on
***********************************************************/
if ( ! function_exists( 'welcart_posted_on' ) ) :
function welcart_posted_on() {
	printf( __( '<span class="%1$s">Posted on</span> %2$s <span class="meta-sep">by</span> %3$s', 'uscestheme' ),
		'meta-prep meta-prep-author',
		sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
			get_permalink(),
			esc_attr( get_the_time() ),
			get_the_date()
		),
		sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
			get_author_posts_url( get_the_author_meta( 'ID' ) ),
			sprintf( esc_attr__( 'View all posts by %s', 'uscestheme' ), get_the_author() ),
			get_the_author()
		)
	);
}
endif;

/***********************************************************
* welcart_posted_in
***********************************************************/
if ( ! function_exists( 'welcart_posted_in' ) ) :
function welcart_posted_in() {
	$tag_list = get_the_tag_list( '', ', ' );
	if ( $tag_list ) {
		$posted_in = __( 'This entry was posted in %1$s and tagged %2$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'uscestheme' );
	} elseif ( is_object_in_taxonomy( get_post_type(), 'category' ) ) {
		$posted_in = __( 'This entry was posted in %1$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'uscestheme' );
	} else {
		$posted_in = __( 'Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'uscestheme' );
	}

	printf(
		$posted_in,
		get_the_category_list( ', ' ),
		$tag_list,
		get_permalink(),
		the_title_attribute( 'echo=0' )
	);
}
endif;

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

/***********************************************************
* Welcart Widget
***********************************************************/
//add_filter('widget_categories_dropdown_args', 'welcart_categories_dropdown_args');
//function welcart_categories_dropdown_args( $args ){
//	global $usces;
//	$ids = $usces->get_item_cat_ids();
//	$ids[] = USCES_ITEM_CAT_PARENT_ID;
//	$args['exclude'] = $ids;
//	return $args;
//}
//
//add_filter('getarchives_where', 'welcart_getarchives_where');
//function welcart_getarchives_where( $r ){
//	$where = "WHERE post_type = 'post' AND post_status = 'publish' AND post_mime_type <> 'item' ";
//	return $where;
//}
//
//add_filter('widget_tag_cloud_args', 'welcart_tag_cloud_args');
//function welcart_tag_cloud_args( $args ){
//	global $usces;
//	if( 'category' == $args['taxonomy']){
//		$ids = $usces->get_item_cat_ids();
//		$ids[] = USCES_ITEM_CAT_PARENT_ID;
//		$args['exclude'] = $ids;
//	}else if( 'post_tag' == $args['taxonomy']){
//		$ids = $usces->get_item_post_ids();
//		$tobs = wp_get_object_terms($ids, 'post_tag');
//		foreach( $tobs as $ob ){
//			$tids[] = $ob->term_id;
//		}
//		$args['exclude'] = $tids;
//	}
//	return $args;
//}

?>
