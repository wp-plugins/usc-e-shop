<?php
/**
 * Edit items rows table for inclusion in administration panels.
 *
 */

if ( ! defined('ABSPATH') ) die();

$item_headers = array(
				'cb' => '<input type="checkbox" />',
				'pict' => '&nbsp;',
				'title' => __('Items','usces'),
				'sku' => 'SKU',
				'price' => __('Price','usces'),
				'zaikonum' => __('stock', 'usces'),
				'zaiko' => __('stock status', 'usces'),
				'categories' => __('Categories', 'usces'),
				'date' => __('Registration date', 'usces')
				);
$zaiko_status = get_option('usces_zaiko_status');
?>


<table class="widefat post fixed" cellspacing="0">
	<thead>
	<tr>
		<th id="cd" class="manage-column check-column"><?php echo $item_headers['cb'] ?></th>
		<th class="item_picture"><?php echo $item_headers['pict'] ?></th>
		<th><?php echo $item_headers['title'] ?></th>
		<th class="item_sku"><?php echo $item_headers['sku'] ?></th>
		<th class="item_price"><?php echo $item_headers['price'] ?></th>
		<th class="item_zaikonum"><?php echo $item_headers['zaikonum'] ?></th>
		<th class="item_zaiko"><?php echo $item_headers['zaiko'] ?></th>
		<th class="item_category"><?php echo $item_headers['categories'] ?></th>
		<th class="item_date"><?php echo $item_headers['date'] ?></th>
	</tr>
	</thead>

	<tfoot>
	<tr>
		<th id="cd" class="manage-column check-column"><?php echo $item_headers['cb'] ?></th>
		<th class="item_picture"><?php echo $item_headers['pict'] ?></th>
		<th><?php echo $item_headers['title'] ?></th>
		<th class="item_sku"><?php echo $item_headers['sku'] ?></th>
		<th class="item_price"><?php echo $item_headers['price'] ?></th>
		<th class="item_zaikonum"><?php echo $item_headers['zaikonum'] ?></th>
		<th class="item_zaiko"><?php echo $item_headers['zaiko'] ?></th>
		<th class="item_category"><?php echo $item_headers['categories'] ?></th>
		<th class="item_date"><?php echo $item_headers['date'] ?></th>
	</tr>
	</tfoot>

	<tbody>
	<?php item_rows($mode, $item_headers); ?>
	</tbody>
</table>



<?php
function item_rows($mode, $item_headers, $posts = array() ) {
	global $wp_query, $usces;

	add_filter('the_title','wp_specialchars');

	// Create array of post IDs.
	$post_ids = array();

	if ( empty($posts) )
		$posts = &$wp_query->posts;

	foreach ( $posts as $a_post )
		$post_ids[] = $a_post->ID;

	$comment_pending_count = get_pending_comments_num($post_ids);
	if ( empty($comment_pending_count) )
		$comment_pending_count = array();

	foreach ( $posts as $post ) {
		if ( empty($comment_pending_count[$post->ID]) )
			$comment_pending_count[$post->ID] = 0;

		/*******************************************************/
		// 商品カテゴリー以外を省く
		//
		/********************************************************/
		if($usces->is_item( $post )) {
		//if($usces->is_item( $post->ID ))
			_item_row($post, $comment_pending_count[$post->ID], $mode, $item_headers);
		}
	}
}
function get_pictid($item_code) {
	global $wpdb;
	
	$query = "SELECT ID FROM $wpdb->posts WHERE post_title = '$item_code' AND post_type = 'attachment'";
	$result = $wpdb->get_var( $query );
	
	return $result;
}
function _item_row($a_post, $pending_comments, $mode, $item_headers) {
	global $post, $zaiko_status;
	static $rowclass;

	$global_post = $post;
	$post = $a_post;
	setup_postdata($post);

	$rowclass = 'alternate' == $rowclass ? '' : 'alternate';
	global $current_user;
	$post_owner = ( $current_user->ID == $post->post_author ? 'self' : 'other' );
	$curent_url = USCES_ADMIN_URL . $_SERVER['QUERY_STRING'];
	$edit_link = USCES_ADMIN_URL . '?page=usces_itemedit&amp;action=edit&amp;post='.$post->ID;//get_edit_post_link( $post->ID );
	$delete_link = USCES_ADMIN_URL . '?page=usces_itemedit&amp;doaction=&amp;action=delete&amp;post='.$post->ID;
	$title = _draft_or_post_title();
	$custom_fields = get_post_custom($post->ID);
	$item_code = $custom_fields['itemCode'][0];
	$item_name = $custom_fields['itemName'][0];
	$item_pictid = get_pictid($item_code);
	$item_sumnail = wp_get_attachment_image( $item_pictid, array(60, 60), true );//'<img src="#" height="60" width="60" alt="" />';
	foreach($custom_fields as $key => $value){
		if(substr($key, 0, 5) == 'isku_'){
			$key = substr($key, 5);
			$item_skus[$key] = maybe_unserialize($value[0]);
		}
	}
	if(count($item_skus) == 0)
		$item_skus = array();
	else
		natsort($item_skus);


?>
	<tr id='post-<?php echo $post->ID; ?>' class='<?php echo trim( $rowclass . ' author-' . $post_owner . ' status-' . $post->post_status ); ?> iedit' valign="top">
<?php
	$items_columns = $item_headers;
	$hidden = get_hidden_columns('edit');
	foreach ( $items_columns as $column_name=>$column_display_name ) {
		$class = "class=\"$column_name column-$column_name\"";

		$style = '';
		if ( in_array($column_name, $hidden) )
			$style = ' style="display:none;"';

		$attributes = "$class$style";

		switch ($column_name) {

		case 'cb':
		?>
		<th scope="row" class="check-column"><?php if ( current_user_can( 'edit_post', $post->ID ) ) { ?><input type="checkbox" name="post[]" value="<?php the_ID(); ?>" /><?php } ?></th>
		<?php
		break;

		case 'date':
			if ( '0000-00-00 00:00:00' == $post->post_date && 'date' == $column_name ) {
				$t_time = $h_time = __('Unpublished');
			} else {
				$t_time = get_the_time(__('Y/m/d g:i:s A'));
				$m_time = $post->post_date;
				$time = get_post_time('G', true, $post);

				$time_diff = time() - $time;

				if ( ( 'future' == $post->post_status) ) {
					if ( $time_diff <= 0 ) {
						$h_time = sprintf( __('%s from now'), human_time_diff( $time ) );
					} else {
						$h_time = $t_time;
						$missed = true;
					}
				} else {

					if ( $time_diff > 0 && $time_diff < 24*60*60 )
						$h_time = sprintf( __('%s ago'), human_time_diff( $time ) );
					else
						$h_time = mysql2date(__('Y/m/d'), $m_time);
				}
			}
			echo '<td ' . $attributes . '>';
			if ( 'excerpt' == $mode )
				echo apply_filters('post_date_column_time', $t_time, $post, $column_name, $mode);
			else
				echo '<abbr title="' . $t_time . '">' . apply_filters('post_date_column_time', $h_time, $post, $column_name, $mode) . '</abbr>';
			echo '<br />';
			if ( 'publish' == $post->post_status ) {
				_e('Published');
			} elseif ( 'future' == $post->post_status ) {
				if ( isset($missed) )
					echo '<strong class="attention">' . __('Missed schedule') . '</strong>';
				else
					_e('Scheduled');
			} else {
				_e('Last Modified');
			}
			echo '</td>';
		break;

		case 'title':
			$attributes = 'class="post-title column-title"' . $style;
		?>
		<td <?php echo $attributes ?>>
			<div><strong><?php if ( current_user_can( 'edit_post', $post->ID ) ) { ?><a class="row-title" href="<?php echo $edit_link; ?>" title="<?php echo attribute_escape(sprintf(__('Edit "%s"'), $title)); ?>"><?php echo $title ?></a><?php } else { echo $title; }; _post_states($post); ?></strong></div>
			<div><?php echo $item_code; ?></div>
			<div><?php echo $item_name; ?></div>
		<?php
			if ( 'excerpt' == $mode )
				the_excerpt();

			$actions = array();
			if ( current_user_can('edit_post', $post->ID) ) {
				$actions['edit'] = '<a href="' . $edit_link . '" title="' . attribute_escape(__('Edit this post')) . '">' . __('Edit') . '</a>';
				//$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . attribute_escape(__('Edit this post inline')) . '">' . __('Quick&nbsp;Edit') . '</a>';
				//$actions['delete'] = "<a class='submitdelete' title='" . attribute_escape(__('Delete this post')) . "' href='" . wp_nonce_url($delete_link, 'delete-post_' . $post->ID) . "' onclick=\"if ( confirm('" . js_escape(sprintf( ('draft' == $post->post_status) ? __("You are about to delete this draft '%s'\n 'Cancel' to stop, 'OK' to delete.") : __("You are about to delete this post '%s'\n 'Cancel' to stop, 'OK' to delete."), $post->post_title )) . "') ) { return true;}return false;\">" . __('Delete') . "</a>";
				//$actions['delete'] = "<a class='submitdelete' title='" . attribute_escape(__('Delete this post')) . "' href='" . wp_nonce_url("post.php?action=delete&amp;post=$post->ID", 'delete-post_' . $post->ID) . "' onclick=\"if ( confirm('" . js_escape(sprintf( ('draft' == $post->post_status) ? __("You are about to delete this draft '%s'\n 'Cancel' to stop, 'OK' to delete.") : __("You are about to delete this post '%s'\n 'Cancel' to stop, 'OK' to delete."), $post->post_title )) . "') ) { return true;}return false;\">" . __('Delete') . "</a>";
				$actions['delete'] = "<a class='submitdelete' title='" . attribute_escape(__('Delete this post')) . "' href='" . wp_nonce_url($delete_link, 'delete-post_' . $post->ID) . "' onclick=\"if ( confirm('" . js_escape(sprintf( ('draft' == $post->post_status) ? __("You are about to delete this draft '%s'\n 'Cancel' to stop, 'OK' to delete.") : __("You are about to delete this post '%s'\n 'Cancel' to stop, 'OK' to delete."), $post->post_title )) . "') ) { return true;}return false;\">" . __('Delete') . "</a>";
			}
			if ( in_array($post->post_status, array('pending', 'draft')) ) {
				if ( current_user_can('edit_post', $post->ID) )
					$actions['view'] = '<a href="' . get_permalink($post->ID) . '" title="' . attribute_escape(sprintf(__('Preview "%s"'), $title)) . '" rel="permalink">' . __('Preview') . '</a>';
			} else {
				$actions['view'] = '<a href="' . get_permalink($post->ID) . '" title="' . attribute_escape(sprintf(__('View "%s"'), $title)) . '" rel="permalink">' . __('View') . '</a>';
			}
			$action_count = count($actions);
			$i = 0;
			echo '<div class="row-actions">';
			foreach ( $actions as $action => $link ) {
				++$i;
				( $i == $action_count ) ? $sep = '' : $sep = ' | ';
				echo "<span class='$action'>$link$sep</span>";
			}
			echo '</div>';

			get_inline_data($post);
		?></td>
		<?php
		break;

		case 'categories':
		?>
		<td <?php echo $attributes ?>><?php
			$categories = get_the_category();
			if ( !empty( $categories ) ) {
				$out = array();
				foreach ( $categories as $c )
					$out[] = "<a href='admin.php?page=usces_itemedit&category_name=$c->slug'> " . wp_specialchars(sanitize_term_field('name', $c->name, $c->term_id, 'category', 'display')) . "</a>";
					echo join( ', ', $out );
			} else {
				_e('Uncategorized');
			}
		?></td>
		<?php
		break;

		case 'sku':
		?>
		<td class="sku">
		<?php $i=0; foreach($item_skus as $key => $value) { $bgc = ($i%2 == 1) ? ' bgc1' : ' bgc2'; $i++; ?>
			<div class="skuline<?php echo $bgc; ?>"><?php echo $key; ?></div>
		<?php } if(count($item_skus) == 0) echo "&nbsp;"; ?>
		</td>
		<?php
		break;

		case 'price':
		?>
		<td class="price">
		<?php $i=0; foreach($item_skus as $key => $value) { $bgc = ($i%2 == 1) ? ' bgc1' : ' bgc2'; $i++; ?>
			<div class="priceline<?php echo $bgc; ?>"><?php echo $value['price']; ?></div>
		<?php } if(count($item_skus) == 0) echo "&nbsp;"; ?>
		</td>
		<?php
		break;

		case 'zaikonum':
		?>
		<td class="zaikonum">
		<?php $i=0; foreach($item_skus as $key => $value) { $bgc = ($i%2 == 1) ? ' bgc1' : ' bgc2'; $i++; ?>
			<div class="priceline<?php echo $bgc; ?>"><?php echo $value['zaikonum']; ?></div>
		<?php } if(count($item_skus) == 0) echo "&nbsp;"; ?>
		</td>
		<?php
		break;

		case 'zaiko':
		?>
		<td class="zaiko">
		<?php $i=0; foreach($item_skus as $key => $value) { $zaikokey = $value['zaiko']; $bgc = ($i%2 == 1) ? ' bgc1' : ' bgc2'; $i++; ?>
			<div class="zaikoline<?php echo $bgc; ?>"><?php echo $zaiko_status[$zaikokey]; ?></div>
		<?php } if(count($item_skus) == 0) echo "&nbsp;"; ?>
		</td>
		<?php
		break;

		case 'pict':
		?>
		<td><?php if ( current_user_can( 'edit_post', $post->ID ) ) { ?><a href="<?php echo $edit_link; ?>" title="<?php echo attribute_escape(sprintf(__('Edit "%s"'), $title)); ?>"><?php echo $item_sumnail; ?></a><?php } else { echo $item_sumnail; } ?></td>
		<?php
		break;

/*
		case 'tags':
		?>
		<td <?php echo $attributes ?>><?php
			$tags = get_the_tags($post->ID);
			if ( !empty( $tags ) ) {
				$out = array();
				foreach ( $tags as $c )
					$out[] = "<a href='admin.php?page=".USCES_PLUGIN_BASENAME."&tag=$c->slug'> " . wp_specialchars(sanitize_term_field('name', $c->name, $c->term_id, 'post_tag', 'display')) . "</a>";
				echo join( ', ', $out );
			} else {
				_e('No Tags');
			}
		?></td>
		<?php
		break;

		case 'comments':
		?>
		<td <?php echo $attributes ?>><div class="post-com-count-wrapper">
		<?php
			$pending_phrase = sprintf( __('%s pending'), number_format( $pending_comments ) );
			if ( $pending_comments )
				echo '<strong>';
				comments_number("<a href='edit-comments.php?p=$post->ID' title='$pending_phrase' class='post-com-count'><span class='comment-count'>" . __('0') . '</span></a>', "<a href='edit-comments.php?p=$post->ID' title='$pending_phrase' class='post-com-count'><span class='comment-count'>" . __('1') . '</span></a>', "<a href='edit-comments.php?p=$post->ID' title='$pending_phrase' class='post-com-count'><span class='comment-count'>" . __('%') . '</span></a>');
				if ( $pending_comments )
				echo '</strong>';
		?></div></td>
		<?php
		break;

		case 'author':
		?>
		<td <?php echo $attributes ?>><a href="edit.php?author=<?php the_author_ID(); ?>"><?php the_author() ?></a></td>
		<?php
		break;

		case 'control_view':
		?>
		<td><a href="<?php the_permalink(); ?>" rel="permalink" class="view"><?php _e('View'); ?></a></td>
		<?php
		break;

		case 'control_edit':
		?>
		<td><?php if ( current_user_can('edit_post', $post->ID) ) { echo "<a href='$edit_link' class='edit'>" . __('Edit') . "</a>"; } ?></td>
		<?php
		break;

		case 'control_delete':
		?>
		<td><?php if ( current_user_can('delete_post', $post->ID) ) { echo "<a href='" . wp_nonce_url("post.php?action=delete&amp;post=$id", 'delete-post_' . $post->ID) . "' class='delete'>" . __('Delete') . "</a>"; } ?></td>
		<?php
		break;

		default:
		?>
		<td <?php echo $attributes ?>><?php do_action('manage_posts_custom_column', $column_name, $post->ID); ?></td>
		<?php
		break;
*/
	}
}
?>
	</tr>
<?php
	$post = $global_post;
}
?>