<?php
define('WELCART_PRODUCT', 'products');
define('WELCART_GENRE', 'product-genre');
define('WELCART_RELATED', 'product-related');
define('WELCART_TAG', 'product-tag');
/***********************************************************
* Custom Post Type
***********************************************************/
add_action('init', 'usces_products');
function usces_products() 
{
  $labels = array(
    'name' => 'Welcart 商品',
    'singular_name' => '商品一覧',
    'add_new' => '商品の新規追加',
    'add_new_item' => '商品の新規追加',
    'edit_item' => '商品の編集',
    'new_item' => '投稿',
    'view_item' => '商品を見る',
    'search_items' => 'タイトル・解説を検索',
    'not_found' =>  '商品が有りません',
    'not_found_in_trash' => 'ゴミ箱に商品は有りません', 
    'parent_item_colon' => '',
    'menu_name' => 'Welcart 商品'

  );
  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' => true,
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array('title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','revisions'), 
	'register_meta_box_cb' => 'add_meta_box_product'
  ); 
  register_post_type(WELCART_PRODUCT,$args);
}

/***********************************************************
* Custom Taxonomy
***********************************************************/
add_action( 'init', 'usces_create_taxonomies', 0 );
function usces_create_taxonomies() 
{
  $labels = array(
    'name' => '商品カテゴリー',
    'singular_name' => _x( 'Genre', 'taxonomy singular name' ),
    'search_items' =>  '商品カテゴリーを検索',
    'all_items' => '全ての商品カテゴリー',
    'parent_item' => __( 'Parent Genre' ),
    'parent_item_colon' => __( 'Parent Genre:' ),
    'edit_item' => __( 'Edit Genre' ), 
    'update_item' => __( 'Update Genre' ),
    'add_new_item' => '商品カテゴリーを追加',
    'new_item_name' => __( 'New Genre Name' ),
  ); 	

  register_taxonomy(WELCART_GENRE,array(WELCART_PRODUCT), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => WELCART_GENRE ),
  ));
  
  $labels = array(
    'name' => '関連商品',
    'singular_name' => '関連商品',
    'search_items' =>  __( 'Search Related' ),
    'popular_items' => __( 'Popular Related' ),
    'all_items' => __( 'All Related' ),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __( 'Edit Related' ), 
    'update_item' => __( 'Update Related' ),
    'add_new_item' => __( 'Add New Related' ),
    'new_item_name' => __( 'New Related Name' ),
    'separate_items_with_commas' => __( 'Separate Related with commas' ),
    'add_or_remove_items' => __( 'Add or remove Related' ),
    'choose_from_most_used' => __( 'Choose from the most used Related' )
  ); 

  register_taxonomy(WELCART_RELATED,WELCART_PRODUCT,array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => WELCART_RELATED ),
  ));

  $labels = array(
    'name' => __( 'Tag' ),
    'singular_name' => __( 'Tag' ),
    'search_items' =>  __( 'Search Tag' ),
    'popular_items' => __( 'Popular Tag' ),
    'all_items' => __( 'All Tag' ),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __( 'Edit Tag' ), 
    'update_item' => __( 'Update Tag' ),
    'add_new_item' => __( 'Add New Tag' ),
    'new_item_name' => __( 'New Tag Name' ),
    'separate_items_with_commas' => __( 'Separate writers with commas' ),
    'add_or_remove_items' => __( 'Add or remove tags' ),
    'choose_from_most_used' => __( 'Choose from the most used tags' )
  ); 

  register_taxonomy(WELCART_TAG,WELCART_PRODUCT,array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => WELCART_TAG ),
  ));
}


function check_custom_rules() {
    global $wp_rewrite, $add_cutom_post_rules;

    if ( ! $wp_rewrite->using_permalinks() ) { return; }
	$structure = get_option('permalink_structure');
	preg_match('/\/[^%]*\//', $structure, $matches);
	$pre = empty($matches[0]) ? '/' . WELCART_GENRE .'/' : $matches[0];
    $add_cutom_post_rules = array();
    $rule_templates = array(
        '/'                                                         => '',
        '/([0-9]{1,})/'                                             => '&p=$matches[1]',
        '/page/([0-9]{1,})/'                                        => '&paged=$matches[1]',
        '/date/([0-9]{4})/'                                         => '&year=$matches[1]',
        '/date/([0-9]{4})/page/([0-9]{1,})/'                        => '&year=$matches[1]&paged=$matches[2]',
        '/date/([0-9]{4})/([0-9]{2})/'                              => '&year=$matches[1]&monthnum=$matches[2]',
        '/date/([0-9]{4})/([0-9]{2})/page/([0-9]{1,})/'             => '&year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]',
        '/date/([0-9]{4})/([0-9]{2})/([0-9]{2})/'                   => '&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]',
        '/date/([0-9]{4})/([0-9]{2})/([0-9]{2})/page/([0-9]{1,})/'  => '&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]',
        $pre.'(\S*)'                                                => '&' . WELCART_GENRE . '=$matches[1]',
    );
    $post_types = get_post_types( array( 'public' => true, 'show_ui' => true ), false );

    if ( $post_types ) {
        foreach ( $post_types as $post_type_slug => $post_type ) {
            if ( ! isset( $post_type->_builtin ) || ! $post_type->_builtin ) {
                foreach ( $rule_templates as $regex => $rule ) {
                    $add_cutom_post_rules[ $post_type_slug . $regex . '?$' ] = $wp_rewrite->index . '?post_type=' . $post_type_slug . $rule;
                }
            }
        }
    }
 
    if ( $add_cutom_post_rules ) {
        $rules = $wp_rewrite->wp_rewrite_rules();
        foreach ( $add_cutom_post_rules as $regex => $rule ) {
            if ( ! isset( $rules[$regex] ) ) {
                $wp_rewrite->flush_rules();
                break;
            }
        }
    }
}
add_action( 'init', 'check_custom_rules' );
 
function add_custom_type_index_rules( $rules ) {
    global $add_cutom_post_rules;
    if ( $add_cutom_post_rules && is_array( $add_cutom_post_rules ) ) {
        $rules = array_merge( $add_cutom_post_rules, $rules );
    }
    return $rules;
}
add_filter( 'rewrite_rules_array', 'add_custom_type_index_rules' );

/***********************************************************
* Admin Products Edit Page
***********************************************************/
function add_meta_box_product($post){
	add_meta_box('meta_box_product_first_box','商品情報','add_field_product_first_box',WELCART_PRODUCT, 'normal', 'high');
	add_meta_box('meta_box_product_second_box','配送関連情報','add_field_product_second_box',WELCART_PRODUCT, 'normal', 'high');
	add_meta_box('itemsku','SKU情報','add_field_product_sku_box',WELCART_PRODUCT, 'normal', 'high');
	add_meta_box('itemoption','商品オプション情報','add_field_product_option_box',WELCART_PRODUCT, 'normal', 'high');
	add_meta_box('pagecontent_box','商品詳細ページ','add_field_product_pagecontent_box',WELCART_PRODUCT, 'normal', 'high');
	add_meta_box('meta_box_product_pict_box', __('Item image', 'usces'), 'add_field_product_pict_box', WELCART_PRODUCT, 'side', 'high');
}

function add_field_product_pagecontent_box($post, $box){
	echo '<div class="itempagetitle">商品詳細ページタイトル</div>';
	usces_add_product_post_title($post, $box);
	echo '<div class="itempagetitle">商品詳細本文</div>';
	usces_add_product_post_editor($post, $box);
}
function usces_add_product_post_title($post, $box){
	global $post_type;
	$post_type_object = get_post_type_object($post_type);
?>
<div id="titlediv">
<div id="titlewrap">
	<label class="hide-if-no-js" style="visibility:hidden" id="title-prompt-text" for="title"><?php echo apply_filters( 'enter_title_here', __( 'Enter title here' ), $post ); ?></label>
	<input type="text" name="post_title" size="30" tabindex="1" value="<?php echo esc_attr( htmlspecialchars( $post->post_title ) ); ?>" id="title" autocomplete="off" />
</div>
<div class="inside">
<?php
$sample_permalink_html = $post_type_object->public ? get_sample_permalink_html($post->ID) : '';
$shortlink = wp_get_shortlink($post->ID, 'post');
if ( !empty($shortlink) )
    $sample_permalink_html .= '<input id="shortlink" type="hidden" value="' . esc_attr($shortlink) . '" /><a href="#" class="button" onclick="prompt(&#39;URL:&#39;, jQuery(\'#shortlink\').val()); return false;">' . __('Get Shortlink') . '</a>';

if ( $post_type_object->public && ! ( 'pending' == $post->post_status && !current_user_can( $post_type_object->cap->publish_posts ) ) ) { ?>
	<div id="edit-slug-box">
	<?php
		if ( ! empty($post->ID) && ! empty($sample_permalink_html) && 'auto-draft' != $post->post_status )
			echo $sample_permalink_html;
	?>
	</div>
<?php
}
?>
</div>
<?php
wp_nonce_field( 'samplepermalink', 'samplepermalinknonce', false );
?>
</div>
<?php
}
function usces_add_product_post_editor($post, $box){
?>
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">

<?php the_editor($post->post_content); ?>

<table id="post-status-info" cellspacing="0"><tbody><tr>
	<td id="wp-word-count"><?php printf( __( 'Word count: %s' ), '<span class="word-count">0</span>' ); ?></td>
	<td class="autosave-info">
	<span class="autosave-message">&nbsp;</span>
<?php
	if ( 'auto-draft' != $post->post_status ) {
		echo '<span id="last-edit">';
		if ( $last_id = get_post_meta($post_ID, '_edit_last', true) ) {
			$last_user = get_userdata($last_id);
			printf(__('Last edited by %1$s on %2$s at %3$s'), esc_html( $last_user->display_name ), mysql2date(get_option('date_format'), $post->post_modified), mysql2date(get_option('time_format'), $post->post_modified));
		} else {
			printf(__('Last edited on %1$s at %2$s'), mysql2date(get_option('date_format'), $post->post_modified), mysql2date(get_option('time_format'), $post->post_modified));
		}
		echo '</span>';
	} ?>
	</td>
</tr></tbody></table>

</div>
<?php
}

function add_field_product_first_box($post, $box){

	$itemCode = get_post_meta( $post->ID, '_itemCode', true );
	$itemName = get_post_meta( $post->ID, '_itemName', true );
	$itemRestriction = get_post_meta( $post->ID, '_itemRestriction', true );
	$itemPointrate = get_post_meta( $post->ID, '_itemPointrate', true );
	$itemGpNum1 = get_post_meta( $post->ID, '_itemGpNum1', true );
	$itemGpNum2 = get_post_meta( $post->ID, '_itemGpNum2', true );
	$itemGpNum3 = get_post_meta( $post->ID, '_itemGpNum3', true );
	$itemGpDis1 = get_post_meta( $post->ID, '_itemGpDis1', true );
	$itemGpDis2 = get_post_meta( $post->ID, '_itemGpDis2', true );
	$itemGpDis3 = get_post_meta( $post->ID, '_itemGpDis3', true );

//	echo wp_nonce_field('my_area_meta', 'my_area_meta_nonce');

	?>
	<input type="hidden" name="usces_nonce" id="usces_nonce" value="<?php echo wp_create_nonce( plugin_basename(__FILE__) ); ?>" />
	<table class="iteminfo_table">
	<tr>
	<th><?php _e('item code', 'usces'); ?></th>
	<td><input type="text" name="_itemCode" id="itemCode" class="itemCode metaboxfield long" value="<?php esc_attr_e($itemCode); ?>" /></td>
	</tr>
	<tr>
	<th><?php _e('item name', 'usces'); ?></th>
	<td><input type="text" name="_itemName" id="itemName" class="itemName metaboxfield long" value="<?php echo esc_attr($itemName); ?>" /></td>
	</tr>
	<tr>
	<th><?php _e('Limited amount for purchase', 'usces'); ?></th>
	<td><?php printf(__('limit by%s%s%s', 'usces'), '<input type="text" name="_itemRestriction" id="itemRestriction" class="itemRestriction metaboxfield short" value="', esc_attr($itemRestriction), '" />'); ?></td>
	</tr>
	<tr>
	<th><?php _e('Percentage of points', 'usces'); ?></th>
	<td><input type="text" name="_itemPointrate" id="itemPointrate" class="itemPointrate metaboxfield short" value="<?php echo esc_attr($itemPointrate); ?>" />%<em>(<?php _e('Integer', 'usces'); ?>)</em></td>
	</tr>
	<tr>
	<th rowspan="3"><?php _e('Business package discount', 'usces'); ?></th>
	<td>1.<?php printf(__('in more than%s%s%s%s%s %s%s%s,', 'usces'), '<input type="text" name="', '_itemGpNum1', '" id="', 'itemGpNum1', '" class="itemPointrate metaboxfield short"', 'value="', esc_attr($itemGpNum1), '" />'); ?><input type="text" name="_itemGpDis1" id="itemGpDis1" class="itemPointrate metaboxfield short" value="<?php echo esc_attr($itemGpDis1); ?>" /><?php _e('%discount','usces'); ?>(<?php _e('Unit price','usces'); ?>)</td>
	</tr>
	<tr>
	<td>2.<?php printf(__('in more than%s%s%s%s%s %s%s%s,', 'usces'), '<input type="text" name="', '_itemGpNum2', '" id="', 'itemGpNum2', '" class="itemPointrate metaboxfield short"', 'value="', esc_attr($itemGpNum2), '" />'); ?><input type="text" name="_itemGpDis2" id="itemGpDis2" class="itemPointrate metaboxfield short" value="<?php echo esc_attr($itemGpDis2); ?>" /><?php _e('%discount','usces'); ?>(<?php _e('Unit price','usces'); ?>)</td>
	</tr>
	<tr>
	<td>3.<?php printf(__('in more than%s%s%s%s%s %s%s%s,', 'usces'), '<input type="text" name="', '_itemGpNum3', '" id="', 'itemGpNum3', '" class="itemPointrate metaboxfield short"', 'value="', esc_attr($itemGpNum3), '" />'); ?><input type="text" name="_itemGpDis3" id="itemGpDis3" class="itemPointrate metaboxfield short" value="<?php echo esc_attr($itemGpDis3); ?>" /><?php _e('%discount','usces'); ?>(<?php _e('Unit price','usces'); ?>)</td>
	</tr>
	<?php do_action('usces_item_master_first_section', NULL, $post, $box); ?>
	</table>
	<?php
}

function add_field_product_second_box($post, $box){
	global $usces;
	
	$itemShipping = get_post_meta($post->ID, '_itemShipping', true);
	$itemDeliveryMethod = get_post_meta($post->ID, '_itemDeliveryMethod', true);
	$itemShippingCharge = get_post_meta($post->ID, '_itemShippingCharge', true);
	$itemIndividualSCharge = get_post_meta($post->ID, '_itemIndividualSCharge', true);
	//$itemDeliveryMethod = unserialize($itemDeliveryMethod);
	?>
	<table class="iteminfo_table">
	<?php
	$second_section = '<tr class="shipped">
	<th>' . __('estimated shipping date', 'usces') . '</th>
	<td><select name="_itemShipping" id="itemShipping" class="itemShipping metaboxfield middle">';
	foreach( (array)$usces->shipping_rule as $key => $label){ 
		$selected = $key == $itemShipping ? ' selected="selected"' : '';
		$second_section .= '<option value="' . esc_attr($key) . '"' . $selected . '>' . esc_html($label) . '</option>';
	}
	$second_section .= '</select>
	</tr>
	<tr class="shipped">
	<th>' . __('shipping option','usces') . '</th>
	<td>';
	$delivery_methods = (array)$usces->options['delivery_method'];
	if( count($delivery_methods) === 0 ){
		$second_section .= __('* Please register an item, after you finished delivery setting!','usces');
	}else{
		foreach( $delivery_methods as $deli){
			$second_section .= '<label for="itemDeliveryMethod[' . $deli['id'] . ']" class="metaboxcheckfield short"><input name="_itemDeliveryMethod[' . $deli['id'] . ']" id="itemDeliveryMethod[' . $deli['id'] . ']" type="checkbox" class="metaboxcheckfield" value="' . esc_attr($deli['id']) . '"';
			if(in_array($deli['id'], (array)$itemDeliveryMethod)) {
				$second_section .= ' checked="checked"';
			}
			$second_section .= ' />' . esc_html($deli['name']) . '</label>';
		}
	}
	$second_section .= '</td>
	</tr>
	<tr class="shipped">
	<th>' . __('Shipping', 'usces') . '</th>
	<td><select name="_itemShippingCharge" id="itemShippingCharge" class="itemShippingCharge metaboxfield middle">';
	foreach( (array)$usces->options['shipping_charge'] as $cahrge){
		$selected = $cahrge['id'] == $itemShippingCharge ? ' selected="selected"' : '';
		$second_section .= '<option value="' . $cahrge['id'] . '"' . $selected . '>' . esc_html($cahrge['name']) . '</option>';
	}
	$second_section .= '</select>
	</tr>
	<tr class="shipped">
	<th>' . __('Postage individual charging', 'usces') . '</th>
	<td><input name="_itemIndividualSCharge" id="itemIndividualSCharge" type="checkbox" value="1"';
	if($itemIndividualSCharge){
		$second_section .= ' checked="checked"';
	}
	$second_section .= ' /></td>
	</tr>';
	$second_section = apply_filters('usces_item_master_second_section', $second_section, $post_ID);
	echo $second_section;
	?>
	</table>
	<?php
}

function add_field_product_sku_box($post) {
	$skus = usces_get_skus($post->ID);
	list_item_sku_meta($skus);
	item_sku_meta_form();
}

function add_field_product_option_box($post) {
	$opts = usces_get_opts($post->ID);
	list_item_option_meta($opts);
	item_option_meta_form();
}


add_filter('admin_post_thumbnail_html', 'usces_admin_post_thumbnail_html');
function usces_admin_post_thumbnail_html( $content ){
	global $wpdb, $post_type, $content_width, $_wp_additional_image_sizes, $post_ID;
	$ptype = get_post_type($post_ID);
	if( WELCART_PRODUCT != $ptype ) return $content;
	
	$item_picts = array();
	$item_thumbnails = array();
	$thumbnail_id = get_post_meta( $post_ID, '_thumbnail_id', true );
	$item_code = get_post_meta($post_ID, '_itemCode', true);
	if(!$item_code){
			$item_picts[0] = wp_get_attachment_image( NULL, array(260, 260), true );
	}else{
		if ( $thumbnail_id && get_post( $thumbnail_id ) ) {
			$item_picts[] = wp_get_attachment_image( $thumbnail_id, array(260, 260), true );
			$item_thumbnails[] = wp_get_attachment_image( $thumbnail_id, array(50, 50), true );
		}
		$codestr = $item_code;
		$query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND post_title LIKE %s AND post_type = 'attachment' ORDER BY post_title", $post_ID, $codestr);
		$main_pictids = $wpdb->get_col( $query );
		$codestr = $item_code . '__%';
		$query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND post_title LIKE %s AND post_type = 'attachment' ORDER BY post_title", $post_ID, $codestr);
		$sub_pictids = $wpdb->get_col( $query );
		$item_pictids = array_merge( (array)$main_pictids, (array)$sub_pictids );
		for($i=0; $i<count($item_pictids); $i++){
			if( $thumbnail_id == $item_pictids[$i] )
				continue;
			$item_picts[] = wp_get_attachment_image( $item_pictids[$i], array(260, 260), true );
			$item_thumbnails[] = wp_get_attachment_image( $item_pictids[$i], array(50, 50), true );
		}
	}
	$html = '';
	$html .= '<div class="item-main-pict">';
	$html .= '<div id="item-select-pict">';
	$html .= $item_picts[0];
	$html .= '</div>';
	
	$html .= '<div class="clearfix">';
	for($i=0; $i<count($item_thumbnails); $i++){
		$html .= '<div class="subpict"><a onclick=\'uscesItem.cahngepict("' . str_replace('"', '\"', $item_picts[$i]) . '");\'>' . $item_thumbnails[$i] . '</a></div>';
	}
	$html .= '</div></div>';
	$html .= '<p class="hide-if-no-js"><a title="' . esc_attr__( '商品画像の登録', 'usces' ) . '" href="' . esc_url( get_upload_iframe_src('image') ) . '" id="set-post-thumbnail" class="thickbox">' . esc_html__( '商品画像の登録', 'usces' ) . '</a></p>';
	return $html;
}

add_action('admin_head', 'usces_custompost_init');
function usces_custompost_init() {
	global $current_screen;
	if( WELCART_PRODUCT != $current_screen->id ) return;

	remove_post_type_support( WELCART_PRODUCT, 'title' );
	remove_post_type_support( WELCART_PRODUCT, 'editor' );
}


add_action('admin_footer-post.php', 'admin_enqueue_scripts_post');
add_action('admin_footer-post-new.php', 'admin_enqueue_scripts_post');
function admin_enqueue_scripts_post($hook_suffix){
	global $current_screen;
	if( WELCART_PRODUCT != $current_screen->id ) return;

	?>
<script type='text/javascript'> 
(function($) {
    var submit_event = true;
    // 下書き保存やプレビューの場合は必須チェックを行わない
    $('#post-preview, #save-post').click(function(){
        submit_event = false;
        return true;
    });
    $('#post').submit(function(e){
 		var mes = '';
        if (submit_event) {
			if ( "" == $("#itemCode").val() ) {
				mes += '商品コードが入力されていません。<br />';
				$("#itemCode").css({'background-color': '#FFA'}).click(function(){
					$(this).css({'background-color': '#FFF'});
				});
			}
			if ( '' != mes) {
				$("#major-publishing-actions").append('<div id="usces_ess"></div>');
				$('#ajax-loading').css({'visibility': 'hidden'});
				$('#draft-ajax-loading').css({'visibility': 'hidden'});
				$('#publish').removeClass('button-primary-disabled');
				$('#save-post').removeClass('button-disabled');
				$("#usces_ess").html(mes);
				return false;
			} else {
	            $('#usces_ess').fadeOut();
				return true;
			}
        } else {
            return true;
        }
    });
	
	$('#postimagediv h3').html('<span>商品画像</span>');
	$('#itemCode').blur( 
						function() { 
							if ( $("#itemCode").val().length == 0 ) return;
							uscesItem.newdraft($('#itemCode').val());
						});
	
	$( "#item-sku-list" ).sortable({
		//placeholder: "ui-state-highlight",
		handle : 'th',
		axis : 'y',
		cursor : "move",
		tolerance : "pointer",
		forceHelperSize : true,
		forcePlaceholderSize : true,
		revert : 300,
		opacity: 0.6,
		cancel: ":input,button",
		update : function(){
			var data=[];
			$("table","#item-sku-list").each(function(i,v){
				data.push($(this).attr('id'));
			});
			if( 1 < data.length ){
				itemSku.dosort(data.toString());
			}
		}
	});
	$( "#item-opt-list" ).sortable({
		//placeholder: "ui-state-highlight",
		handle : 'th',
		axis : 'y',
		cursor : "move",
		tolerance : "pointer",
		forceHelperSize : true,
		forcePlaceholderSize : true,
		revert : 300,
		opacity: 0.6,
		cancel: ":input,button",
		update : function(){
			var data=[];
			$("table","#item-opt-list").each(function(i,v){
				data.push($(this).attr('id'));
			});
			if( 1 < data.length ){
				itemOpt.dosort(data.toString());
			}
		}
	});
		
})(jQuery);

try{document.post.itemCode.focus();}catch(e){}

</script> 
	<?php
}

add_action('admin_head', 'usces_script');
function usces_script(){
	global $current_screen;
	if( WELCART_PRODUCT == $current_screen->id ){
		wp_enqueue_script('jquery-ui-sortable', array());
	}elseif( 'edit-' . WELCART_PRODUCT == $current_screen->id ){
	?>
<style type="text/css">
<!--
th.column-thumbnail,
.thumbnail {
	width: 90px;
}
th.column-itemName,
.itemName {
	width: 250px;
}
.itemCode {
	white-space: nowrap;
}
.itemShipping {
	/*width: 150px;*/
}
.itemDelivery {
	/*width: 150px;*/
}
.skuCode {
	white-space: nowrap;
}
.skuName {
	/*width: 150px;*/
}
th.column-skuCPrice,
.skuCPrice {
	white-space: nowrap;
	text-align: right;
}
th.column-skuPrice,
.skuPrice {
	white-space: nowrap;
	text-align: right;
}
th.column-skuUnit,
.skuUnit {
	white-space: nowrap;
	text-align: center;
}
th.column-skuStocknum,
.skuStocknum {
	white-space: nowrap;
	text-align: right;
	width: 60px;
}
th.column-skuStock,
.skuStock {
	white-space: nowrap;
	text-align: center;
	width: 60px;
}
th.column-skuGP,
.skuGP {
	text-align: center;
	width: 40px;
}
.taxonomies {
	/*width: 150px;*/
}
.skudiv {
	border-bottom: 1px dotted #CCCCCC;
}
#filterswitch {
	color: #2683AE;
	cursor: pointer;
}
-->
</style>
	<?php
	}
}
add_action('admin_footer-edit.php', 'usces_fooert_script_edit');
function usces_fooert_script_edit(){
	global $current_screen, $usces;
	if( 'edit-' . WELCART_PRODUCT == $current_screen->id ){
	?>
		<script type='text/javascript'> 
		(function($) {
		
			$("select[name='action']").append('<option value="itemPointrate"><?php _e('Limited amount for purchase', 'usces'); ?></option>' + 
				'<option value="itemRestriction"><?php _e('Percentage of points', 'usces'); ?></option>' + 
				'<option value="itemShipping"><?php _e('estimated shipping date', 'usces'); ?></option>' + 
				'<option value="itemDeliveryMethod"><?php _e('shipping option', 'usces'); ?></option>' + 
				'<option value=""><?php _e('Limited amount for purchase', 'usces'); ?></option>' + 
				'<option value="itemRestriction"><?php _e('Limited amount for purchase', 'usces'); ?></option>' + 
				'<option value="itemRestriction"><?php _e('Limited amount for purchase', 'usces'); ?></option>' + 
				'<option value="itemRestriction"><?php _e('Limited amount for purchase', 'usces'); ?></option>');
			
			$("select[name='action']").change(function(){
			
				if( ! $("body").is(":has('#mode_section')") ){
					$(this).after('<span id="mode_section"></span>');
				}
				
				if( 'itemPointrate' == $(this).val() ){
					$("#mode_section").html('<br /><input type="text" name="_itemPointrate" id="itemPointrate" class="itemPointrate short" value="">');
				}else if( 'itemRestriction' == $(this).val() ){
					$("#mode_section").html('<br /><input type="text" name="_itemRestriction" id="itemRestriction" class="itemRestriction short" value="">');
				}else if( 'itemShipping' == $(this).val() ){
					$("#mode_section").html('<br /><select name="_itemShipping" id="itemShipping" class="itemShipping"><?php foreach( (array)$usces->shipping_rule as $key => $label){ ?><option value="<?php esc_attr_e($key); ?>"><?php esc_html_e($label); ?></option><?php } ?></select>');
				}else if( 'itemDeliveryMethod' == $(this).val() ){
					$("#mode_section").html('<br /><?php 
						$delivery_methods = (array)$usces->options['delivery_method'];
						$second_section = '';
						if( count($delivery_methods) === 0 ){
							_e('* Please register an item, after you finished delivery setting!','usces');
						}else{
							foreach( $delivery_methods as $deli){
								$second_section .= '<label for="itemDeliveryMethod[' . $deli['id'] . ']" class="metaboxcheckfield short"><input name="_itemDeliveryMethod[' . $deli['id'] . ']" id="itemDeliveryMethod[' . $deli['id'] . ']" type="checkbox" class="metaboxcheckfield" value="' . esc_attr($deli['id']) . '"';
								if(in_array($deli['id'], (array)$itemDeliveryMethod)) {
									$second_section .= ' checked="checked"';
								}
								$second_section .= ' />' . esc_html($deli['name']) . '</label>';
							}
							echo $second_section;
						} ?>');
				}else{
					$("#mode_section").html('');
				}
			});
			
			$("#doaction").click(function(){
			
				if( $("input[name^='post']:checked").length == 0 ){
					alert("<?php _e('Choose the product.', 'usces'); ?>");
					return false;
				}
				
				var action = $("select[name='action']").val();
				if( 'itemPointrate' == action ){
					if( !confirm("選択された商品のポイント率を一括更新します。\n\nよろしいですか？") )
						return false;
				}else if( 'itemRestriction' == action ){
					if( !confirm("選択された商品の購入制限数を一括更新します。\n\nよろしいですか？") )
						return false;
				}else if( 'itemShipping' == action ){
					if( !confirm("選択された商品の発送日の目安を一括更新します。\n\nよろしいですか？") )
						return false;
				}else if( 'itemDeliveryMethod' == action ){
					if( !confirm("選択された商品の配送方法を一括更新します。\n\nよろしいですか？") )
						return false;
				}
			});
			
			$("div[class='alignleft actions']").eq(1).append('<span id="filterswitch">詳細表示</span>');
			var filter_detail = '<div><label for="itemname">商品名<input name="item_name" type="text" id="item_name" value="" /></label></div>';
			if( ! $("body").is(":has('#filter_fields')") ){
				$("#filterswitch").after('<span id="filter_fields"></span>');
			}
			$("#filterswitch").toggle(
				function () {
					$("#filterswitch").html('詳細非表示');
					$("#filter_fields").html(filter_detail);
				},
				function () {
					$("#filterswitch").html('詳細表示');
					$("#filter_fields").html('');
				}
			);
		})(jQuery);
		</script> 
	<?php
	}
}
/***********************************************************
* Save Product Data
***********************************************************/
add_action('save_post', 'save_usces_product');
function save_usces_product($post_id){

	if ( !wp_verify_nonce( $_POST['usces_nonce'], plugin_basename(__FILE__) )) {
		return $post_id;
	}
  
  	// 自動保存ルーチンかどうかチェック。そうだった場合はフォームを送信しない（何もしない）
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
		return $post_id;

	// パーミッションチェック
	if ( WELCART_PRODUCT == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_post', $post_id ) )
			return $post_id;
	} else {
			return $post_id;
	}

	// 承認ができたのでデータを探して保存
	$itemCode  = trim($_POST['_itemCode' ]);
	$itemName  = trim($_POST['_itemName' ]);
	$itemRestriction  = trim($_POST['_itemRestriction' ]);
	$itemPointrate  = trim($_POST['_itemPointrate' ]);
	$itemGpNum1  = trim($_POST['_itemGpNum1' ]);
	$itemGpNum2  = trim($_POST['_itemGpNum2' ]);
	$itemGpNum3  = trim($_POST['_itemGpNum3' ]);
	$itemGpDis1  = trim($_POST['_itemGpDis1' ]);
	$itemGpDis2  = trim($_POST['_itemGpDis2' ]);
	$itemGpDis3  = trim($_POST['_itemGpDis3' ]);
	$itemShipping = $_POST['_itemShipping' ];
	$itemDeliveryMethod = isset($_POST['_itemDeliveryMethod']) ? (array)$_POST['_itemDeliveryMethod'] : array();
	$itemShippingCharge = $_POST['_itemShippingCharge' ];
	$itemIndividualSCharge = isset($_POST['_itemIndividualSCharge']) ? 1 : 0;
	update_post_meta($post_id, '_itemCode', $itemCode);
	update_post_meta($post_id, '_itemName', $itemName);
	update_post_meta($post_id, '_itemRestriction', $itemRestriction);
	update_post_meta($post_id, '_itemPointrate', $itemPointrate);
	update_post_meta($post_id, '_itemGpNum1', $itemGpNum1);
	update_post_meta($post_id, '_itemGpNum2', $itemGpNum2);
	update_post_meta($post_id, '_itemGpNum3', $itemGpNum3);
	update_post_meta($post_id, '_itemGpDis1', $itemGpDis1);
	update_post_meta($post_id, '_itemGpDis2', $itemGpDis2);
	update_post_meta($post_id, '_itemGpDis3', $itemGpDis3);
	update_post_meta($post_id, '_itemShipping', $itemShipping);
	update_post_meta($post_id, '_itemDeliveryMethod', $itemDeliveryMethod);
	update_post_meta($post_id, '_itemShippingCharge', $itemShippingCharge);
	update_post_meta($post_id, '_itemIndividualSCharge', $itemIndividualSCharge);
}

/***********************************************************
* Admin Products List Page
***********************************************************/
add_filter( 'manage_posts_columns', 'usces_manage_posts_columns' );
function usces_manage_posts_columns($columns) {
	global $current_screen;
	if( 'edit-' . WELCART_PRODUCT != $current_screen->id ) 
		return $columns;
	//var_dump($columns);
	$columns = array( 
				'cb' => '<input type="checkbox">', 
				'thumbnail' => '画像', 
				'itemName' => '商品名',
				'itemCode' => '商品コード',
				'itemShipping' => '発送日',
				'itemDelivery' => '配送方法',
				'skuCode' => 'SKUコード',
				'skuName' => 'SKU名',
				'skuCPrice' => '通常価',
				'skuPrice' => '売価',
				'skuUnit' => '単位',
				'skuStocknum' => '在庫数',
				'skuStock' => '状態',
				'skuGP' => 'GP',
				'taxonomies' => 'カテゴリー',
				'date' => '日付',
				
				
				
				
				
				
				);
	return $columns;
}
//add_action( 'manage_edit-products_sortable_columns', 'usces_manage_edit_products_sortable_columns');
//function usces_manage_edit_products_sortable_columns($columns){
//	//$columns['itemName'] = '_itemName';
//	//var_dump($columns);
//	$columns['itemCode'] = '_itemCode';
//	return $columns;
//}
add_action( 'manage_posts_custom_column', 'usces_manage_posts_custom_column', 10, 2 );
function usces_manage_posts_custom_column($column_name, $post_id) {
	global $current_screen, $wp_list_table, $post, $usces;
	if( 'edit-' . WELCART_PRODUCT != $current_screen->id ) 
		return;

	$edit_link = get_edit_post_link( $post->ID );
	$title = _draft_or_post_title();
	$post_type_object = get_post_type_object( $post->post_type );
	$can_edit_post = current_user_can( $post_type_object->cap->edit_post, $post->ID );
	$mode = $_REQUEST['mode'];
	
	//$url = get_parmalink($post_id);
	switch( $column_name ){
		case 'thumbnail':
			echo '<a href="' . $edit_link . '" title="' . esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $item ) ) . '">' . get_the_post_thumbnail($post->ID, array(80,80), 'thumbnail') . '</a>';
			break;
		case 'itemName':
			$item = attribute_escape(get_post_meta($post->ID, '_itemName', true));
			if( $can_edit_post && $post->post_status != 'trash' ){
				$itemname = '<strong><a class="row-title" href="' . $edit_link . '" title="' . esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $item ) ) . '">' . $item . '</a></strong>';
			}else{
				 $itemname = '<strong>' . $item .'</strong>';
			}
			echo $itemname;
			_post_states( $post );
			if ( 'excerpt' == $mode ) {
				the_excerpt();
			}
			$actions = array();
			if ( $can_edit_post && 'trash' != $post->post_status ) {
				$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr( __( 'Edit this item' ) ) . '">' . __( 'Edit' ) . '</a>';
				$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr( __( 'Edit this item inline' ) ) . '">' . __( 'Quick&nbsp;Edit' ) . '</a>';
			}
			if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
				if ( 'trash' == $post->post_status )
					$actions['untrash'] = "<a title='" . esc_attr( __( 'Restore this item from the Trash' ) ) . "' href='" . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-' . $post->post_type . '_' . $post->ID ) . "'>" . __( 'Restore' ) . "</a>";
				elseif ( EMPTY_TRASH_DAYS )
					$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash' ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash' ) . "</a>";
				if ( 'trash' == $post->post_status || !EMPTY_TRASH_DAYS )
					$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently' ) ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . __( 'Delete Permanently' ) . "</a>";
			}
			if ( $post_type_object->public ) {
				if ( in_array( $post->post_status, array( 'pending', 'draft' ) ) ) {
					if ( $can_edit_post )
						$actions['view'] = '<a href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;' ), $title ) ) . '" rel="permalink">' . __( 'Preview' ) . '</a>';
				} elseif ( 'trash' != $post->post_status ) {
					$actions['view'] = '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $title ) ) . '" rel="permalink">' . __( 'View' ) . '</a>';
				}
			}

			$actions = apply_filters( is_post_type_hierarchical( $post->post_type ) ? 'page_row_actions' : 'post_row_actions', $actions, $post );
			echo $wp_list_table->row_actions( $actions );
			get_inline_data( $post );
			break;
		case 'itemCode':
			echo attribute_escape(get_post_meta($post->ID, '_itemCode', true));
			break;
		case 'itemShipping':
			$itemShipping = get_post_meta($post->ID, '_itemShipping', true);
			echo attribute_escape($usces->shipping_rule[$itemShipping]);
			break;
		case 'itemDelivery':
			$itemDeliveryMethod = get_post_meta($post->ID, '_itemDeliveryMethod', true);
			$method = '';
			$delivery_methods = (array)$usces->options['delivery_method'];
			foreach( $delivery_methods as $deli){
				if(in_array($deli['id'], (array)$itemDeliveryMethod)) {
					$method .= $deli['name'] . ',';
				}
			}
			$method = rtrim($method, ',');
			echo attribute_escape($method);
			break;
		case 'skuCode':
			$skus = usces_get_skus($post_id);
			$table = '';
			foreach( $skus as $sku ){
				$code = ('' == $sku['code']) ? '&nbsp;' : $sku['code'];
				$table .= '<div class="skudiv">' . attribute_escape($code) . '</div>';
			}
			echo $table;
			break;
		case 'skuName':
			$skus = usces_get_skus($post_id);
			$table = '';
			foreach( $skus as $sku ){
				$name = ('' == $sku['name']) ? '&nbsp;' : $sku['name'];
				$table .= '<div class="skudiv">' . attribute_escape($name) . '</div>';
			}
			echo $table;
			break;
		case 'skuCPrice':
			$skus = usces_get_skus($post_id);
			$table = '';
			foreach( $skus as $sku ){
				$cprice = ('' == $sku['cprice']) ? '&nbsp;' : usces_crform($sku['cprice'], true, false, 'return');
				$table .= '<div class="skudiv">' . attribute_escape($cprice) . '</div>';
			}
			echo $table;
			break;
		case 'skuPrice':
			$skus = usces_get_skus($post_id);
			$table = '';
			foreach( $skus as $sku ){
				$price = ('' == $sku['price']) ? '&nbsp;' : usces_crform($sku['price'], true, false, 'return');
				$table .= '<div class="skudiv">' . attribute_escape($price) . '</div>';
			}
			echo $table;
			break;
		case 'skuUnit':
			$skus = usces_get_skus($post_id);
			$table = '';
			foreach( $skus as $sku ){
				$unit = ('' == $sku['unit']) ? '&nbsp;' : $sku['unit'];
				$table .= '<div class="skudiv">' . attribute_escape($unit) . '</div>';
			}
			echo $table;
			break;
		case 'skuStocknum':
			$skus = usces_get_skus($post_id);
			$table = '';
			foreach( $skus as $sku ){
				$stocknum = ('' == $sku['stocknum']) ? '-' : $sku['stocknum'];
				$table .= '<div class="skudiv">' . attribute_escape($stocknum) . '</div>';
			}
			echo $table;
			break;
		case 'skuStock':
			$zaikoselectarray = get_option('usces_zaiko_status');
			$skus = usces_get_skus($post_id);
			$table = '';
			foreach( $skus as $sku ){
				$table .= '<div class="skudiv">' . attribute_escape($zaikoselectarray[$sku['stock']]) . '</div>';
			}
			echo $table;
			break;
		case 'skuGP':
			$skus = usces_get_skus($post_id);
			$table = '';
			foreach( $skus as $sku ){
				$gp = 0 == (int)$sku['gp'] ? '-' : '○';
				$table .= '<div class="skudiv">' . attribute_escape($gp) . '</div>';
			}
			echo $table;
			break;
		case 'taxonomies':
			$terms = get_the_terms( $post->ID, WELCART_GENRE );
			$cat = '';
			foreach( (array)$terms as $term ){
				$cat .= $term->name . ',';
			}
			$cat = rtrim($cat, ',');
			echo $cat;
			break;
		
	}
}
//add_action( 'views_edit-products', 'usces_views_edit_products');
//function usces_views_edit_products($views){
//	var_dump($views);
//	return $views;
//}
//add_action( 'bulk_actions-edit-products', 'usces_bulk_actions_edit_products');
//function usces_bulk_actions_edit_products($actions){
//	var_dump($actions);
//	return $actions;
//}
//add_action('admin_menu', 'push_menus');
//function push_menus () {
//    global $menu;
//	
//	$menu[26][2] = 'products_edit.php?post_type=products';
//	usces_p($menu);
/////    array_push($menu, $menu[10]);
////    unset($menu[10]);//メディア
//}

add_action('init', 'usces_admin_list_action');
function usces_admin_list_action(){
	if( isset($_REQUEST['action']) ){
		$action = $_REQUEST['action'];
	}elseif( isset($_REQUEST['action2']) ){
		$action = $_REQUEST['action2'];
	}
	if( -1 == $action )
		return;
	 
	if( isset($_REQUEST['post_type']) && WELCART_PRODUCT == $_REQUEST['post_type']  && 'list' == $_REQUEST['mode'] ){
		if( !wp_verify_nonce($_REQUEST['_wpnonce'],'bulk-posts') )
			return;
			
		$ids = (array)$_REQUEST['post'];
		if( empty($ids) ){
			die('<div id="message" style="background-color: #FFFFE0; margin: 20px; padding: 0 10px 0 10px; font-size: 12px; border: 1px solid #E6DB55; border-radius: 3px;"><p>商品が選択されていません。　<a href="'.$_REQUEST['_wp_http_referer'].'">戻る</a></p></div>');
		}
		switch($action){
			case 'category':
				if ( !is_object_in_taxonomy($_REQUEST['post_type'], WELCART_GENRE) )
					return;
				foreach( $ids as $post_id ){
						//wp_set_post_categories( $post_id, $post_category );
				
				}
				break;
			case 'itemShipping':
				foreach( $ids as $post_id ){
						//update itemShipping
				
				}
				break;
		}
	}
}
/*******************************************************************************************/
//update
add_action( 'admin_head', 'sswp_plugin_update_rows' );
function sswp_plugin_update_rows(){
	remove_action( "after_plugin_row_wcex_sitepositions/wcex_sitepositions.php", 'wp_plugin_update_row', 10, 2 );
}
add_filter('site_transient_update_plugins', 'usces_site_transient_update_plugins');
function usces_site_transient_update_plugins( $values ){
	global $current_screen;
//	if( 'update-core' == $current_screen->id )
//		return $values;

	$prugin = new StdClass();
	$prugin->id = 20000;
	$prugin->slug = 'wcex_sitepositions';
	$prugin->new_version = 2.0;
	$prugin->url = '';
	$prugin->package = '';
	$prugin->upgrade_notice = 'このプラグインは自動アップグレードに対応していません。welcart.com よりダウンロードして下さい。';
	$values->response['wcex_sitepositions/wcex_sitepositions.php'] = $prugin;
	//usces_p($values);
		return $values;
}
add_action( "after_plugin_row_wcex_sitepositions/wcex_sitepositions.php", 'usces_plugin_update_row', 9, 2 );
function usces_plugin_update_row( $file, $plugin_data ){
	$current = get_site_transient( 'update_plugins' );
	if ( !isset( $current->response[ $file ] ) )
		return false;

	$r = $current->response[ $file ];

	$plugins_allowedtags = array('a' => array('href' => array(),'title' => array()),'abbr' => array('title' => array()),'acronym' => array('title' => array()),'code' => array(),'em' => array(),'strong' => array());
	$plugin_name = wp_kses( $plugin_data['Name'], $plugins_allowedtags );

	$details_url = self_admin_url('plugin-install.php?tab=plugin-information&plugin=' . $r->slug . '&TB_iframe=true&width=600&height=800');

	$wp_list_table = _get_list_table('WP_Plugins_List_Table');

	if ( is_network_admin() || !is_multisite() ) {
		echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message">';

		if ( ! current_user_can('update_plugins') )
			printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a>.'), $plugin_name, esc_url($details_url), esc_attr($plugin_name), $r->new_version );
		else if ( empty($r->package) )
			echo 'アップグレードが可能です。';
			//printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a>. <em>Automatic update is unavailable for this plugin.</em>'), $plugin_name, esc_url($details_url), esc_attr($plugin_name), $r->new_version );
		else
			printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a> or <a href="%5$s">update automatically</a>.'), $plugin_name, esc_url($details_url), esc_attr($plugin_name), $r->new_version, wp_nonce_url( self_admin_url('update.php?action=upgrade-plugin&plugin=') . $file, 'upgrade-plugin_' . $file) );

		do_action( "in_plugin_update_message-$file", $plugin_data, $r );

		echo '</div></td></tr>';
	}
}

/***********************************************************
* Contextual Help
***********************************************************/
add_filter('contextual_help','custom_post_help');
function custom_post_help($help) {
	global $current_screen;
	echo $current_screen->id;
	switch( $current_screen->id ){
		case 'edit-' . WELCART_PRODUCT:
		?>
		
		<?php
		break;
		case 'edit-' . WELCART_PRODUCT:
		?>
		
		<?php
		break;
		case 'edit-' . WELCART_PRODUCT:
		?>
		
		<?php
		break;
		default:
			echo $help;
	}
}

/***********************************************************
* Product Widgets
***********************************************************/
add_filter( 'getarchives_where', 'usces_filter_getarchives_where', 10, 2);
function usces_filter_getarchives_where($w, $r ){
global $usces_product_flag;
	if( WELCART_PRODUCT != $r['post_type'] )
		return $w;
	
	$w = "WHERE post_type = '" . WELCART_PRODUCT . "' AND post_status = 'publish'";
	return $w;
}
add_filter( 'get_archives_link', 'usces_filter_get_archives_link');
function usces_filter_get_archives_link( $link_html ){
	global $usces_product_flag, $wp_rewrite;
	if( !$usces_product_flag )
		return $link_html;

	$home = get_option('home');
	$patternshome = str_replace('/', '\/', $home);
    if ( $wp_rewrite->using_permalinks() ){
		$patterns = array('/' . $patternshome . '(.*)(\/\d{4})/');
		$replacements = array($home.'/' . WELCART_PRODUCT . '/date$2');
		$link_html = preg_replace($patterns, $replacements, $link_html);
	}else{
		$link_html = preg_replace('/\?.*=\d+/', '$0&post_type=' . WELCART_PRODUCT, $link_html);
	}
	return $link_html;
}

add_filter('posts_where', 'usces_filter_posts_where' );
function usces_filter_posts_where( $where ){
	global $wpdb;
	if( is_search() && WELCART_PRODUCT == $_REQUEST['post_type'] ) {
		$where .= $wpdb->prepare(" OR (({$wpdb->postmeta}.meta_value LIKE %s) AND wp_posts.post_type = '" . WELCART_PRODUCT . "' AND (wp_posts.post_status = 'publish' OR wp_posts.post_status = 'private'))", '%'.$_REQUEST['s'].'%');
	}
	
	return $where;
}
add_filter('posts_join', 'usces_filter_posts_join' );
function usces_filter_posts_join( $join ){
	global $wpdb;
	
	//$table = 
	if( is_search() && WELCART_PRODUCT == $_REQUEST['post_type'] ) {
		$join .= " LEFT JOIN " . $wpdb->postmeta . " ON " . 
		$wpdb->posts . ".ID = " . $wpdb->postmeta . ".post_id";
	}
	return $join;
}

add_filter('usces_filter_widget_categories_dropdown_args', 'usces_filter_categories_args' );
add_filter('usces_filter_widget_categories_args', 'usces_filter_categories_args' );
function usces_filter_categories_args( $cat_args ){
	$cat_args['taxonomy'] = WELCART_GENRE;
	return $cat_args;
}

/***********************************************************
* Add Attachment
***********************************************************/
add_action('add_attachment', 'usces_add_attachment');
function usces_add_attachment($attachment_id){
	global $wpdb, $usces;
	$attachment = get_post($attachment_id);
	$title = $attachment->post_title;
	$item_id = $usces->get_postIDbyCode( $title );
	if( !empty($item_id) ){
		$attachment->post_parent = $item_id;
		update_post_meta($item_id, '_thumbnail_id', $attachment_id);
	}else{
		$tempcode = explode('__', $title);
		$item_id = $usces->get_postIDbyCode( $tempcode[0] );
		if( !empty($item_id) ){
			$attachment->post_parent = $item_id;
		}else{
			return;
		}
	}

	wp_update_post($attachment);
}

?>