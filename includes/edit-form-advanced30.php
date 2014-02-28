<?php
/**
 * Post advanced form for inclusion in the administration panels.
 *
 * @package WordPress
 * @subpackage Administration
 */
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

global $usces;
//echo $GLOBALS['hook_suffix'];
//wp_enqueue_script('post');
//
//if ( post_type_supports($post_type, 'editor') ) {
//	if ( user_can_richedit() )
//		wp_enqueue_script('editor');
//	wp_enqueue_script('word-count');
//}
//
//if ( post_type_supports($post_type, 'editor') || post_type_supports($post_type, 'thumbnail') ) {
//	add_thickbox();
//	wp_enqueue_script('media-upload');
//}

/**
 * Post ID global
 * @name $post_ID
 * @var int
 */
$post_ID = isset($post_ID) ? (int) $post_ID : 0;
$temp_ID = isset($temp_ID) ? (int) $temp_ID : 0;
$user_ID = isset($user_ID) ? (int) $user_ID : 0;
$action = isset($action) ? $action : '';

$messages = array();
$messages['post'] = array(
	 0 => '', // Unused. Messages start at index 1.
	 1 => sprintf( __('Post updated. <a href="%s">View post</a>'), esc_url( get_permalink($post_ID) ) ),
	 2 => __('Custom field updated.'),
	 3 => __('Custom field deleted.'),
	 4 => __('Post updated.'),
	/* translators: %s: date and time of the revision */
	 5 => isset($_GET['revision']) ? sprintf( __('Post restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	 6 => sprintf( __('Post published. <a href="%s">View post</a>'), esc_url( get_permalink($post_ID) ) ),
	 7 => __('Post saved.'),
	 8 => sprintf( __('Post submitted. <a target="_blank" href="%s">Preview post</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	 9 => sprintf( __('Post scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview post</a>'),
		// translators: Publish box date format, see http://php.net/date
		(isset($post->post_date) ? date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) : ''), esc_url( get_permalink($post_ID) ) ),
	10 => sprintf( __('Post draft updated. <a target="_blank" href="%s">Preview post</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
);
//$messages['page'] = array(
//	 0 => '', // Unused. Messages start at index 1.
//	 1 => sprintf( __('Page updated. <a href="%s">View page</a>'), esc_url( get_permalink($post_ID) ) ),
//	 2 => __('Custom field updated.'),
//	 3 => __('Custom field deleted.'),
//	 4 => __('Page updated.'),
//	 5 => isset($_GET['revision']) ? sprintf( __('Page restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
//	 6 => sprintf( __('Page published. <a href="%s">View page</a>'), esc_url( get_permalink($post_ID) ) ),
//	 7 => __('Page saved.'),
//	 8 => sprintf( __('Page submitted. <a target="_blank" href="%s">Preview page</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
//	 9 => sprintf( __('Page scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview page</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
//	10 => sprintf( __('Page draft updated. <a target="_blank" href="%s">Preview page</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
//);
//
$messages = apply_filters( 'post_updated_messages', $messages );

$message = false;
if ( isset($_GET['message']) ) {
	$_GET['message'] = absint( $_GET['message'] );
	if ( isset($messages[$post_type][$_GET['message']]) )
		$message = $messages[$post_type][$_GET['message']];
	elseif ( !isset($messages[$post_type]) && isset($messages['post'][$_GET['message']]) )
		$message = $messages['post'][$_GET['message']];
}

$notice = false;
$form_extra = '';
if ( isset($post->post_status) && 'auto-draft' == $post->post_status ) {
	if ( 'edit' == $action )
		$post->post_title = '';
	$autosave = false;
	$form_extra .= "<input type='hidden' id='auto_draft' name='auto_draft' value='1' />";
} else {
	$autosave = wp_get_post_autosave( $post_ID );
}

$form_action = 'editpost';
$nonce_action = 'update-' . $post_type . '_' . $post_ID;
$form_extra .= "<input type='hidden' id='post_ID' name='post_ID' value='" . esc_attr($post->ID) . "' />";

// Detect if there exists an autosave newer than the post and if that autosave is different than the post
if ( $autosave && mysql2date( 'U', $autosave->post_modified_gmt, false ) > mysql2date( 'U', $post->post_modified_gmt, false ) ) {
	foreach ( _wp_post_revision_fields() as $autosave_field => $_autosave_field ) {
		if ( normalize_whitespace( $autosave->$autosave_field ) != normalize_whitespace( $post->$autosave_field ) ) {
			$notice = sprintf( __( 'There is an autosave of this post that is more recent than the version below.  <a href="%s">View the autosave</a>' ), get_edit_post_link( $autosave->ID ) );
			break;
		}
	}
	unset($autosave_field, $_autosave_field);
}

$post_type_object = get_post_type_object($post_type);

// All meta boxes should be defined and added before the first do_meta_boxes() call (or potentially during the do_meta_boxes action).
require_once(USCES_PLUGIN_DIR.'/includes/meta-boxes.php');

add_meta_box('submitdiv', __('Publish'), 'post_submit_meta_box', $post_type, 'side', 'core');

// all taxonomies
foreach ( get_object_taxonomies($post_type) as $tax_name ) {
	$taxonomy = get_taxonomy($tax_name);
	if ( ! $taxonomy->show_ui )
		continue;

	$label = $taxonomy->labels->name;

	if ( !is_taxonomy_hierarchical($tax_name) )
		add_meta_box('tagsdiv-' . $tax_name, $label, 'post_tags_meta_box', $post_type, 'side', 'core');
	else
		add_meta_box($tax_name . 'div', $label, 'post_categories_meta_box', $post_type, 'side', 'core', array( 'taxonomy' => $tax_name, 'descendants_and_self' => USCES_ITEM_CAT_PARENT_ID ));
}

if ( post_type_supports($post_type, 'page-attributes') )
	add_meta_box('pageparentdiv', 'page' == $post_type ? __('Page Attributes') : __('Attributes'), 'page_attributes_meta_box', $post_type, 'side', 'core');

if ( current_theme_supports( 'post-thumbnails', $post_type ) && post_type_supports($post_type, 'thumbnail') )
	add_meta_box('postimagediv', __('Featured Image'), 'post_thumbnail_meta_box', $post_type, 'side', 'low');

if ( post_type_supports($post_type, 'excerpt') )
	add_meta_box('postexcerpt', __('Excerpt'), 'post_excerpt_meta_box', $post_type, 'normal', 'core');

if ( post_type_supports($post_type, 'trackbacks') )
	add_meta_box('trackbacksdiv', __('Send Trackbacks'), 'post_trackback_meta_box', $post_type, 'normal', 'core');

if ( post_type_supports($post_type, 'custom-fields') )
	add_meta_box('postcustom', __('Custom Fields'), 'post_custom_meta_box', $post_type, 'normal', 'core');

do_action('dbx_post_advanced');
if ( post_type_supports($post_type, 'comments') )
	add_meta_box('commentstatusdiv', __('Discussion'), 'post_comment_status_meta_box', $post_type, 'normal', 'core');

if ( (isset($post->post_status) && ('publish' == $post->post_status || 'private' == $post->post_status) ) && post_type_supports($post_type, 'comments') )
	add_meta_box('commentsdiv', __('Comments'), 'post_comment_meta_box', $post_type, 'normal', 'core');

if ( !( (isset( $post->post_status ) && 'pending' == $post->post_status) && !current_user_can( $post_type_object->cap->publish_posts ) ) )
	add_meta_box('slugdiv', __('Slug'), 'post_slug_meta_box', $post_type, 'normal', 'core');

if ( post_type_supports($post_type, 'author') ) {
	if ( version_compare($wp_version, '3.1', '>=') ){
		if ( is_super_admin() || current_user_can( $post_type_object->cap->edit_others_posts ) )
			add_meta_box('authordiv', __('Author'), 'post_author_meta_box', $post_type, 'normal', 'core');
	}else{
		$authors = get_editable_user_ids( $current_user->id ); // TODO: ROLE SYSTEM
		if ( isset($post->post_author) && $post->post_author && !in_array($post->post_author, $authors) )
			$authors[] = $post->post_author;
		if ( ( $authors && count( $authors ) > 1 ) || is_super_admin() )
			add_meta_box('authordiv', __('Author'), 'post_author_meta_box', $post_type, 'normal', 'core');
	}
}

if ( post_type_supports($post_type, 'revisions') && 0 < $post_ID && wp_get_post_revisions( $post_ID ) )
	add_meta_box('revisionsdiv', __('Revisions'), 'post_revisions_meta_box', $post_type, 'normal', 'core');


/****************************************************************************/
function post_item_pict_box($post) {
	global $usces, $current_screen;
	$item_picts = array();
	$item_sumnails = array();
	$post_id = isset($post->ID) ? $post->ID : 0;
	$item_code = get_post_meta($post_id, '_itemCode', true);
	
	if( !empty($item_code) ){
		$pictid = (int)$usces->get_mainpictid($item_code);
		$item_picts[] = wp_get_attachment_image( $pictid, array(260, 200), true );
		$item_sumnails[] = wp_get_attachment_image( $pictid, array(50, 50), true );
		$item_pictids = $usces->get_pictids($item_code);
		for($i=0; $i<count($item_pictids); $i++){
			$item_picts[] = wp_get_attachment_image( $item_pictids[$i], array(260, 200), true );
			$item_sumnails[] = wp_get_attachment_image( $item_pictids[$i], array(50, 50), true );
		}
	}
?>

	<div class="item-main-pict">
		<div id="item-select-pict">
<?php
	if($item_sumnails) {
		echo $item_picts[0];
	} else {
?>
	<!--<img src="#" width="260" height="200" alt="" />-->

<?php
	}
?>
		</div>
		<div class="clearfix">
	<?php for($i=0; $i<count($item_sumnails); $i++){ ?>
			<div class="subpict"><a onclick='uscesItem.cahngepict("<?php echo str_replace('"', '\"', $item_picts[$i]); ?>");'><?php echo $item_sumnails[$i]; ?></a></div>
	<?php } ?>
		</div>
	</div>
<?php
}
add_meta_box('item-main-pict', __('Item image', 'usces'), 'post_item_pict_box', $post_type, 'side', 'high');


do_action('add_meta_boxes', $post_type, $post);
do_action('add_meta_boxes_' . $post_type, $post);

do_action('do_meta_boxes', $post_type, 'normal', $post);
do_action('do_meta_boxes', $post_type, 'advanced', $post);
do_action('do_meta_boxes', $post_type, 'side', $post);

//if ( 'post' == $post_type ) {
//	add_contextual_help($current_screen,
//	'<p>' . __('The title field and the big Post Editing Area are fixed in place, but you can reposition all the other boxes that allow you to add metadata to your post using drag and drop, and can minimize or expand them by clicking the title bar of the box. You can also hide any of the boxes by using the Screen Options tab, where you can also choose a 1- or 2-column layout for this screen.') . '</p>' .
//	'<p>' . __('<strong>Title</strong> - Enter a title for your post. After you enter a title, you&#8217;ll see the permalink below, which you can edit.') . '</p>' .
//	'<p>' . __('<strong>Post editor</strong> - Enter the text for your post. There are two modes of editing: Visual and HTML. Choose the mode by clicking on the appropriate tab. Visual mode gives you a WYSIWYG editor. Click the last icon in the row to get a second row of controls. The HTML mode allows you to enter raw HTML along with your post text. You can insert media files by clicking the icons above the post editor and following the directions.') . '</p>' .
//	'<p>' . __('<strong>Publish</strong> - You can set the terms of publishing your post in the Publish box. For Status, Visibility, and Publish (immediately), click on the Edit link to reveal more options. Visibility includes options for password-protecting a post or making it stay at the top of your blog indefinitely (sticky). Publish (immediately) allows you to set a future or past date and time, so you can schedule a post to be published in the future or backdate a post.') . '</p>' .
//	'<p>' . __('<strong>Featured Image</strong> - This allows you to associate an image with your post without inserting it. This is usually useful only if your theme makes use of the featured image as a post thumbnail on the home page, a custom header, etc.') . '</p>' .
//	'<p>' . __("<strong>Send Trackbacks</strong> - Trackbacks are a way to notify legacy blog systems that you've linked to them. Enter the URL(s) you want to send trackbacks. If you link to other WordPress sites they&#8217;ll be notified automatically using pingbacks, and this field is unnecessary.") . '</p>' .
//	'<p>' . __('<strong>Discussion</strong> - You can turn comments and pings on or off, and if there are comments on the post, you can see them here and moderate them.') . '</p>' .
//	'<p>' . sprintf(__('You can also create posts with the <a href="%s">Press This bookmarklet</a>.'), 'options-writing.php') . '</p>' .
//	'<p><strong>' . __('For more information:') . '</strong></p>' .
//	'<p>' . __('<a href="http://codex.wordpress.org/Writing_Posts">Writing Posts Documentation</a>') . '</p>' .
//	'<p>' . __('<a href="http://wordpress.org/support/">Support Forums</a>') . '</p>'
//	);
//} elseif ( 'page' == $post_type ) {
//	add_contextual_help($current_screen, '<p>' . __('Pages are similar to Posts in that they have a title, body text, and associated metadata, but they are different in that they are not part of the chronological blog stream, kind of like permanent posts. Pages are not categorized or tagged, but can have a hierarchy. You can nest Pages under other Pages by making one the "Parent" of the other, creating a group of Pages.') . '</p>' .
//	'<p>' . __('Creating a Page is very similar to creating a Post, and the screens can be customized in the same way using drag and drop, the Screen Options tab, and expanding/collapsing boxes as you choose. The Page editor mostly works the same Post editor, but there are some Page-specific features in the Page Attributes box:') . '</p>' .
//	'<p>' . __('<strong>Parent</strong> - You can arrange your pages in hierarchies. For example, you could have an &#8220;About&#8221; page that has &#8220;Life Story&#8221; and &#8220;My Dog&#8221; pages under it. There are no limits to how many levels you can nest pages.') . '</p>' .
//	'<p>' . __('<strong>Template</strong> - Some themes have custom templates you can use for certain pages that might have additional features or custom layouts. If so, you&#8217;ll see them in this dropdown menu.') . '</p>' .
//	'<p>' . __('<strong>Order</strong> - Pages are usually ordered alphabetically, but you can put a number above to change the order pages appear in.') . '</p>' .
//	'<p><strong>' . __('For more information:') . '</strong></p>' .
//	'<p>' . __('<a href="http://codex.wordpress.org/Pages_Add_New_SubPanel">Page Creation Documentation</a>') . '</p>' .
//	'<p>' . __('<a href="http://wordpress.org/support/">Support Forums</a>') . '</p>'
//	);
//}

/* 30 ***************************************/
//require_once('./admin-header.php');

$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';
?>
<script type="text/javascript">

jQuery(function($){
<?php if($status == 'success'){ ?>
			$("#anibox").animate({ backgroundColor: "#ECFFFF" }, 2000);
<?php }else if($status == 'caution'){ ?>
			$("#anibox").animate({ backgroundColor: "#FFF5CE" }, 2000);
<?php }else if($status == 'error'){ ?>
			$("#anibox").animate({ backgroundColor: "#FFE6E6" }, 2000);
<?php } ?>
});
</script>

<div class="wrap">
<div class="usces_admin">
<h2><!--<img src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/usc-e-shop/images/easymoblog1.png" /> --><?php echo esc_html( $title ); ?></h2>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>
<form name="post" action="" method="post" id="post">
<?php

//if ( 0 == $post_ID)
//	wp_nonce_field('add-post');
//else
//	wp_nonce_field('update-post_' .  $post_ID);
	
$itemCode = get_post_meta($post_ID, '_itemCode', true);
$itemName = get_post_meta($post_ID, '_itemName', true);
$itemRestriction = get_post_meta($post_ID, '_itemRestriction', true);
$itemPointrate = get_post_meta($post_ID, '_itemPointrate', true);
$itemGpNum1 = get_post_meta($post_ID, '_itemGpNum1', true);
$itemGpNum2 = get_post_meta($post_ID, '_itemGpNum2', true);
$itemGpNum3 = get_post_meta($post_ID, '_itemGpNum3', true);
$itemGpDis1 = get_post_meta($post_ID, '_itemGpDis1', true);
$itemGpDis2 = get_post_meta($post_ID, '_itemGpDis2', true);
$itemGpDis3 = get_post_meta($post_ID, '_itemGpDis3', true);

$itemShipping = get_post_meta($post_ID, '_itemShipping', true);
$itemDeliveryMethod = get_post_meta($post_ID, '_itemDeliveryMethod', true);
$itemShippingCharge = get_post_meta($post_ID, '_itemShippingCharge', true);
$itemIndividualSCharge = get_post_meta($post_ID, '_itemIndividualSCharge', true);
/*************************************** 30 */
?>

<?php wp_nonce_field($nonce_action); ?>
<input type="hidden" name="usces_nonce" id="usces_nonce" value="<?php echo wp_create_nonce( 'usc-e-shop' ); ?>" />
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr($form_action) ?>" />
<input type="hidden" id="originalaction" name="originalaction" value="<?php echo esc_attr($form_action) ?>" />
<input type="hidden" id="post_author" name="post_author" value="<?php echo esc_attr( $post->post_author ); ?>" />
<input type="hidden" id="post_type" name="post_type" value="<?php echo esc_attr($post_type) ?>" />
<input type="hidden" id="original_post_status" name="original_post_status" value="<?php echo esc_attr($post->post_status) ?>" />
<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(stripslashes(wp_get_referer())); ?>" />

<input type="hidden" name="post_mime_type" value="item" />
<input type="hidden" name="page" value="usces_itemedit" />
<input name="usces_referer" type="hidden" id="usces_referer" value="<?php if(isset($_REQUEST['usces_referer'])) echo $_REQUEST['usces_referer']; ?>" />

<?php
if ( isset($post->post_status) && 'draft' != $post->post_status )
	wp_original_referer_field(true, 'previous');

echo $form_extra;

wp_nonce_field( 'autosave', 'autosavenonce', false );
wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
?>

<div id="refbutton"><a href="<?php echo USCES_ADMIN_URL . '?page=usces_itemedit&amp;action=duplicate&amp;post='.$post->ID.'&usces_referer='.(isset($_REQUEST['usces_referer']) ? urlencode($_REQUEST['usces_referer']) : ''); ?>">[<?php _e('make a copy', 'usces'); ?>]</a> <a href="<?php if(isset($_REQUEST['usces_referer'])) echo $_REQUEST['usces_referer']; ?>">[<?php _e('back to item list', 'usces'); ?>]</a></div>
<div id="poststuff" class="metabox-holder has-right-sidebar">
<div id="side-info-column" class="inner-sidebar">
<div id="item-main-pict"></div>

<?php
('page' == $post_type) ? do_action('submitpage_box') : do_action('submitpost_box');
$side_meta_boxes = do_meta_boxes($post_type, 'side', $post);
?>
</div>

<div id="post-body">
<div id="post-body-content">



<!--<div id="postitemcustomstuff">-->
<div id="meta_box_product_first_box" class="postbox " >
<div class="inside">
<table class="iteminfo_table">
<tr>
<th><?php _e('item code', 'usces'); ?></th>
<td><input type="text" name="itemCode" id="itemCode" class="itemCode" value="<?php echo esc_attr($itemCode); ?>" />
<input type="hidden" name="itemCode_nonce" id="itemCode_nonce" value="<?php echo wp_create_nonce( 'itemCode_nonce' ); ?>" /></td>
</tr>
<tr>
<th><?php _e('item name', 'usces'); ?></th>
<td><input type="text" name="itemName" id="itemName" class="itemName" value="<?php echo esc_attr($itemName); ?>" />
<input type="hidden" name="itemName_nonce" id="itemName_nonce" value="<?php echo wp_create_nonce( 'itemName_nonce' ); ?>" /></td>
</tr>
<tr>
<th><?php _e('Limited amount for purchase', 'usces'); ?></th>
<td><?php printf(__('limit by%s%s%s', 'usces'), '<input type="text" name="itemRestriction" id="itemRestriction" class="itemRestriction" value="', esc_attr($itemRestriction), '" />'); ?>
<input type="hidden" name="itemRestriction_nonce" id="itemRestriction_nonce" value="<?php echo wp_create_nonce( 'itemRestriction_nonce' ); ?>" /></td>
</tr>
<tr>
<th><?php _e('Percentage of points', 'usces'); ?></th>
<td><input type="text" name="itemPointrate" id="itemPointrate" class="itemPointrate" value="<?php echo esc_attr($itemPointrate); ?>" />%<em>(<?php _e('Integer', 'usces'); ?>)</em>
<input type="hidden" name="itemPointrate_nonce" id="itemPointrate_nonce" value="<?php echo wp_create_nonce( 'itemPointrate_nonce' ); ?>" /></td>
</tr>
<tr>
<?php
$gp_row = '<th rowspan="3">' . __('Business package discount', 'usces') . '</th>
<td>1.' . sprintf(__('in more than%s%s%s%s%s %s%s%s,', 'usces'), '<input type="text" name="', 'itemGpNum1', '" id="', 'itemGpNum1', '" class="itemPointrate"', 'value="', esc_attr($itemGpNum1), '" />') . '<input type="text" name="itemGpDis1" id="itemGpDis1" class="itemPointrate" value="' . esc_attr($itemGpDis1) . '" />' . __('%discount','usces') . '(' . __('Unit price','usces') . ')</td>
</tr>
<tr>
<td>2.' . sprintf(__('in more than%s%s%s%s%s %s%s%s,', 'usces'), '<input type="text" name="', 'itemGpNum2', '" id="', 'itemGpNum2', '" class="itemPointrate"', 'value="', esc_attr($itemGpNum2), '" />') . '<input type="text" name="itemGpDis2" id="itemGpDis2" class="itemPointrate" value="' . esc_attr($itemGpDis2) . '" />' . __('%discount','usces') . '(' . __('Unit price','usces') . ')</td>
</tr>
<tr>
<td>3.' . sprintf(__('in more than%s%s%s%s%s %s%s%s,', 'usces'), '<input type="text" name="', 'itemGpNum3', '" id="', 'itemGpNum3', '" class="itemPointrate"', 'value="', esc_attr($itemGpNum3), '" />') . '<input type="text" name="itemGpDis3" id="itemGpDis3" class="itemPointrate" value="' . esc_attr($itemGpDis3) . '" />' . __('%discount','usces') . '(' . __('Unit price','usces') . ')</td>
</tr>';
?>
<?php echo apply_filters('usces_item_master_gp_row', $gp_row, $post_ID); ?>
<?php apply_filters('usces_item_master_first_section', NULL, $post_ID); ?>
</table>
</div>
</div>
<?php do_action('usces_after_item_master_first_section', $post_ID); ?>

<!--<div id="postitemcustomstuff">-->
<div id="meta_box_product_second_box" class="postbox " >
<div class="inside">
<table class="iteminfo_table">
<?php
$second_section = '<tr class="shipped">
<th>' . __('estimated shipping date', 'usces') . '</th>
<td><select name="itemShipping" id="itemShipping" class="itemShipping">';
foreach( (array)$this->shipping_rule as $key => $label){ 
	$selected = $key == $itemShipping ? ' selected="selected"' : '';
	$second_section .= '<option value="' . esc_attr($key) . '"' . $selected . '>' . esc_html($label) . '</option>';
}
$second_section .= '</select>
<input type="hidden" name="itemShipping_nonce" id="itemShipping_nonce" value="' . wp_create_nonce( 'itemShipping_nonce' ) . '" /></td>
</tr>
<tr class="shipped">
<th>' . __('shipping option','usces') . '</th>
<td>';
$delivery_methods = $this->options['delivery_method'];
if( count($delivery_methods) === 0 ){
	$second_section .= __('* Please register an item, after you finished delivery setting!','usces');
}else{
	foreach( $delivery_methods as $deli){
		$second_section .= '<label for="itemDeliveryMethod[' . $deli['id'] . ']"><input name="itemDeliveryMethod[' . $deli['id'] . ']" id="itemDeliveryMethod[' . $deli['id'] . ']" type="checkbox" value="' . esc_attr($deli['id']) . '"';
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
<td><select name="itemShippingCharge" id="itemShippingCharge" class="itemShippingCharge">';
foreach( $this->options['shipping_charge'] as $cahrge){
	$selected = $cahrge['id'] == $itemShippingCharge ? ' selected="selected"' : '';
	$second_section .= '<option value="' . $cahrge['id'] . '"' . $selected . '>' . esc_html($cahrge['name']) . '</option>';
}
$second_section .= '</select>
<input type="hidden" name="itemShippingCharge_nonce" id="itemShippingCharge_nonce" value="' . wp_create_nonce( 'itemShippingCharge_nonce' ) . '" /></td>
</tr>
<tr class="shipped">
<th>' . __('Postage individual charging', 'usces') . '</th>
<td><input name="itemIndividualSCharge" id="itemIndividualSCharge" type="checkbox" value="1"';
if($itemIndividualSCharge){
	$second_section .= ' checked="checked"';
}
$second_section .= ' /></td>
</tr>';
$second_section = apply_filters('usces_item_master_second_section', $second_section, $post_ID);
echo $second_section;
?>
</table>
</div>
</div>
<?php do_action('usces_after_item_master_second_section', $post_ID); ?>


<div id="itemsku" class="postbox">
<h3 class="hndle"><span>SKU <?php _e('Price', 'usces'); ?></span></h3>
<div class="inside">
	<div id="postskucustomstuff" class="skustuff">
<?php
//$metadata = has_item_sku_meta($post->ID);
//list_item_sku_meta($metadata);
//item_sku_meta_form();
$skus = $usces->get_skus($post->ID);
list_item_sku_meta($skus);
item_sku_meta_form();

?>
	</div>
</div>
</div>
<?php do_action('usces_after_item_master_sku_section', $post_ID); ?>

<div id="itemoption" class="postbox">
<h3 class="hndle"><span><?php _e('options for items', 'usces'); ?></span></h3>
<div class="inside">
<div id="postoptcustomstuff"><div id="optajax-response"></div>
<?php
//$metadata = has_item_option_meta($post->ID);
//list_item_option_meta($metadata);
//item_option_meta_form();
$opts = usces_get_opts($post->ID);
list_item_option_meta($opts);
item_option_meta_form();
?>
</div>
</div>
</div>
<?php do_action('usces_after_item_master_option_section', $post_ID); ?>


<div class="postbox">
<?php if ( post_type_supports($post_type, 'title') ) { ?>
<div class="inside">
<div class="itempagetitle"><?php _e("The product details page title", "usces"); ?></div>
<div id="titlediv">
	<div id="titlewrap">
		<label class="hide-if-no-js" style="visibility:hidden" id="title-prompt-text" for="title"><?php _e('Enter title here') ?></label>
		<input type="text" name="post_title" size="30" tabindex="1" value="<?php echo esc_attr( htmlspecialchars( $post->post_title ) ); ?>" id="title" autocomplete="off" />
	</div>
<?php
$sample_permalink_html = get_sample_permalink_html($post->ID);
$shortlink = wp_get_shortlink($post->ID, 'post');
if ( !empty($shortlink) )
    $sample_permalink_html .= '<input id="shortlink" type="hidden" value="' . esc_attr($shortlink) . '" /><a href="#" class="button" onclick="prompt(&#39;URL:&#39;, jQuery(\'#shortlink\').val()); return false;">' . __('Get Shortlink') . '</a>';

if ( !( 'pending' == $post->post_status && !current_user_can( $post_type_object->cap->publish_posts ) ) ) { ?>
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
<?php } ?>
<?php if ( post_type_supports($post_type, 'editor') ) { ?>
<div class="itempagetitle"><?php _e("Full product details", "usces"); ?></div>
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">

<style type="text/css">
<!--
.wp_themeSkin table td {
	background-color: white;
}		
-->
</style>
<?php
		if ( version_compare($wp_version, '3.3-beta', '>') ){
			wp_editor($post->post_content, 'content', array('dfw' => true, 'tabindex' => 1) );
		}else{
			the_editor($post->post_content);
		}
?>

<table id="post-status-info" cellspacing="0"><tbody><tr>
	<td id="wp-word-count"></td>
	<td class="autosave-info">
	<span id="autosave">&nbsp;</span>
<?php
	if ( 'auto-draft' != $post->post_status ) {
		echo '<span id="last-edit">';
		if ( $last_id = get_post_meta($post_ID, '_edit_last', true) ) {
			$last_user = get_userdata($last_id);
			printf(__('Last edited by %1$s on %2$s at %3$s'), esc_html( $last_user->display_name ), mysql2date(get_option('date_format'), $post->post_modified), mysql2date(get_option('time_format'), $post->post_modified));
		} else {
			if( isset($post->post_modified) )
				printf(__('Last edited on %1$s at %2$s'), mysql2date(get_option('date_format'), $post->post_modified), mysql2date(get_option('time_format'), $post->post_modified));
		}
		echo '</span>';
	} ?>
	</td>
</tr></tbody></table>

</div>

<?php
}
?>
</div>
</div>
<?php
do_meta_boxes($post_type, 'normal', $post);

( 'page' == $post_type ) ? do_action('edit_page_form') : do_action('edit_form_advanced');

do_meta_boxes($post_type, 'advanced', $post);

do_action('dbx_post_sidebar'); ?>

</div>
</div>
<br class="clear" />
</div><!-- /poststuff -->
</form>
</div>

<?php wp_comment_reply(); ?>

<?php if ((isset($post->post_title) && '' == $post->post_title) || (isset($_GET['message']) && 2 > $_GET['message'])) : ?>
<script type="text/javascript">
try{document.post.itemCode.focus();}catch(e){}
try{
	var dfo = document.post;
	if(dfo.itemRestriction.value == '') dfo.itemRestriction.value = uscesL10n.purchase_limit;
	if(dfo.itemPointrate.value == '') dfo.itemPointrate.value = uscesL10n.point_rate;
	if(dfo.itemShipping.selectedIndex == '0') dfo.itemShipping.selectedIndex = uscesL10n.shipping_rule;
}catch(e){}

jQuery(document).ready(function($){
	$("#in-category-"+<?php echo USCES_ITEM_CAT_PARENT_ID; ?>).attr({checked: "checked"});

//	$('#itemCode').blur( 
//						function() { 
//							if ( $("#itemCode").val().length == 0 ) return;
//							uscesItem.newdraft($('#itemCode').val());
//						});
});
</script>
<?php endif; ?>
