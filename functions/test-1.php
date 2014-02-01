<?php

function wc2_load_test_List_action(){
	$current_screen = get_current_screen();

	if ( ! class_exists( 'WC2_Test_List_Table' ) )
		require_once(USCES_PLUGIN_DIR."/functions/test-1-class.php");

	add_filter( 'manage_' . $current_screen->id . '_columns',
		array( 'WC2_Test_List_Table', 'define_columns' ) );
//
//	add_screen_option( 'per_page', array(
//		'label' => 'Contact Forms',
//		'default' => 20,
//		'option' => 'cfseven_contact_forms_per_page' ) );
	
}
function wc_item_post($where){
	global $wpdb;
	return $where .= " AND {$wpdb->posts}.post_mime_type = 'item'";
}

function wc2_test_list_page() {
	global $usces;
	remove_action('pre_get_posts', array(&$usces, 'filter_divide_item'));
	add_action('posts_where', 'wc_item_post');
	$args['screen']= get_current_screen();
	$list_table = new WC2_Test_List_Table($args);
//	$list_table->define_columns = $list_table->get_columns();
	$list_table->prepare_items();
?>
<div class="wrap">
<?php screen_icon(); ?>

<h2><?php
	echo esc_html( 'Test Forms' );

	echo ' <a href="' . esc_url( menu_page_url( 'wpcf7-new', false ) ) . '" class="add-new-h2">' . esc_html( __( 'Add New', 'contact-form-7' ) ) . '</a>';

	if ( ! empty( $_REQUEST['s'] ) ) {
		echo sprintf( '<span class="subtitle">'
			. __( 'Search results for &#8220;%s&#8221;', 'contact-form-7' )
			. '</span>', esc_html( $_REQUEST['s'] ) );
	}
?></h2>

<?php do_action( 'wpcf7_admin_notices' ); ?>

<form method="get" action="">
	<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
	<?php $list_table->search_box( __( 'Search Contact Forms', 'contact-form-7' ), 'wpcf7-contact' ); ?>
	<?php $list_table->display(); ?>
</form>

</div>
<?php
}