<?php
/*
Template Name: セット販売テンプレート
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Net Stage Theme
*/
get_header();

$NSSP = new NS_SetPage();
$NSSP->set_list_per_page( 20 );
$NSSP->set_data();

?>

<div id="content">
<div class="catbox">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<div class="post" id="<?php echo $post->post_name; ?>">
<a name="capture"></a>
<h1 class="pagetitle"><?php the_title(); ?></h1>
<div class="entry">

	<div id="top_area" class="clearfix">
		<div class="product_box">
			<div class="step_title">STEP.1 ヘッド</div>
			<div id="step1" class="product_info <?php echo $NSSP->get_top_info_class('head'); ?>">
				<div class="set_thumbnail"><?php echo $NSSP->get_top_thumb('head'); ?></div>
				<div class="item_name <?php echo $NSSP->get_top_name_class('head'); ?>"><?php echo $NSSP->get_top_itemname('head'); ?></div>
				<div class="item_price">販売価格：<?php echo $NSSP->get_top_itemprice('head'); ?></div>
				<div class="item_cprice">通常価格：<?php echo $NSSP->get_top_itemcprice('head'); ?></div>
				<div class="set_button_box clearfix">
					<form action="#capture" method="post">
					<div id="step1_change" class="set_button"><?php echo $NSSP->get_top_change_button('head'); ?></div>
					<div id="step1_detail" class="set_button"><?php echo $NSSP->get_top_detail_button('head'); ?></div>
					</form>
				</div>
			</div>
		</div>
		<div class="product_box">
			<div class="step_title">STEP.2 シャフト</div>
			<div id="step2" class="product_info <?php echo $NSSP->get_top_info_class('shuft'); ?>">
				<div class="set_thumbnail"><?php echo $NSSP->get_top_thumb('shuft'); ?></div>
				<div class="item_name <?php echo $NSSP->get_top_name_class('shuft'); ?>"><?php echo $NSSP->get_top_itemname('shuft'); ?></div>
				<div class="item_price">販売価格：<?php echo $NSSP->get_top_itemprice('shuft'); ?></div>
				<div class="item_cprice">通常価格：<?php echo $NSSP->get_top_itemcprice('shuft'); ?></div>
				<div class="set_button_box clearfix">
					<form action="#capture" method="post">
					<div id="step2_change" class="set_button"><?php echo $NSSP->get_top_change_button('shuft'); ?></div>
					<div id="step2_detail" class="set_button"><?php echo $NSSP->get_top_detail_button('shuft'); ?></div>
					</form>
				</div>
			</div>
		</div>
		<div class="product_box">
			<div class="step_title">STEP.3 グリップ</div>
			<div id="step3" class="product_info <?php echo $NSSP->get_top_info_class('grip'); ?>">
				<div class="set_thumbnail"><?php echo $NSSP->get_top_thumb('grip'); ?></div>
				<div class="item_name <?php echo $NSSP->get_top_name_class('grip'); ?>"><?php echo $NSSP->get_top_itemname('grip'); ?></div>
				<div class="item_price">販売価格：<?php echo $NSSP->get_top_itemprice('grip'); ?></div>
				<div class="item_cprice">通常価格：<?php echo $NSSP->get_top_itemcprice('grip'); ?></div>
				<div class="set_button_box clearfix">
					<form action="#capture" method="post">
					<div id="step3_change" class="set_button"><?php echo $NSSP->get_top_change_button('grip'); ?></div>
					<div id="step3_detail" class="set_button"><?php echo $NSSP->get_top_detail_button('grip'); ?></div>
					</form>
				</div>
			</div>
		</div>
		<div class="amount_box">
			<div id="set_order">
				<div class="amount_label">工賃を含む合計金額</div>
				<div class="prouct_amount"><?php echo $NSSP->get_product_amount(); ?></div>
				<div class="amount_mes"><?php echo $NSSP->get_top_amount_mes(); ?></div>
				<div class="amount_button"><?php echo $NSSP->get_top_amount_button(); ?></div>
			</div>
		</div>
	
	</div><!-- end of top_area -->
	<div id="body_area">
		<h3 class="titlebar"><?php echo $NSSP->body_caption(); ?></h3>
		
		<?php /* リスト表示 */ if( 'head_list' == $NSSP->action || 'shuft_list' == $NSSP->action || 'grip_list' == $NSSP->action || 'default' == $NSSP->action ) : ?>
		<div class="clearfix">
		
		<?php $reco_ob = new wp_query( $NSSP->get_list_query() ); ?>
		<?php if ($reco_ob->have_posts()) : while ($reco_ob->have_posts()) : $reco_ob->the_post(); usces_the_item(); ?>
		<div class="thumbnail_box">
			<form action="#capture" method="post">
			<div class="item_code"><?php usces_the_itemCode(); ?></div>
			<div class="thumimg"><?php usces_the_itemImage($number = 0, $width = 140, $height = 140 ); ?></div>
			<?php NS_the_fantastic4(); ?>
			<div class="item_name"><?php usces_the_itemName(); ?></div>
			<div class="item_price"><?php NS_the_item_pricesCr(); ?></div>
			<div class="select_button_box">
				<input name="<?php echo $NSSP->get_select_button_name(); ?>" type="submit" class="select_item_button" value="　" />
				<input name="selected_post_id" type="hidden" value="<?php the_ID(); ?>" />
			</div>
			</form>
		</div>
		
		<?php endwhile; else: ?>
		<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
		<?php endif; wp_reset_query(); ?>
		</div>
		
		<?php /* 詳細表示 */ else : ?>
		
		<?php $NSSP->view_item_detail(); ?>
		
		<?php endif; ?>
	</div>
</div><!-- end of entry -->
</div><!-- end of post -->
<?php endwhile; endif; ?>
<?php usces_p($NSSP->product); ?>
<?php usces_p($NSSP->action); ?>
</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_footer(); ?>

<?php

class NS_SetPage
{
	var $action;
	var $product;
	var $body_caption;
	var $posts_per_page;
	
	function __construct(){
		$this->set_action();
	}

	function set_action(){
		if( isset($_POST['head_list']) )
			$this->action = 'head_list';
		elseif( isset($_POST['head_detail']) )
			$this->action = 'head_detail';
		elseif( isset($_POST['shuft_list']) )
			$this->action = 'shuft_list';
		elseif( isset($_POST['shuft_detail']) )
			$this->action = 'shuft_detail';
		elseif( isset($_POST['grip_list']) )
			$this->action = 'grip_list';
		elseif( isset($_POST['grip_detail']) )
			$this->action = 'grip_detail';
//		elseif( isset($_POST['select_item']) )
//			$this->action = 'select_item';
//		elseif( isset($_POST['enter_item']) )
//			$this->action = 'enter_item';
		elseif( isset($_POST['decide']) )
			$this->action = 'decide';
		else
			$this->action = 'default';
	}
	
	function set_data(){
		//$this->get_session();
		$this->set_session();
		
		switch( $this->action ){
			case 'head_list':
				$this->body_caption = 'ヘッド一覧';

				break;
			case 'head_detail':
				$this->body_caption = 'ヘッド詳細';
				//$this->product['head']['post_id'] = isset($_POST['selected_post_id']) ? (int)$_POST['selected_post_id'] : NULL;
				if(isset($_POST['selected_post_id'])) {
					if($_POST['selected_post_id'] != $this->product['head']['post_id']) {
						$this->product['head']['post_id'] = (int)$_POST['selected_post_id'];
						$this->product['head']['sku'] = NULL;
						$this->product['head']['price'] = NULL;
					}
				}

				break;
			case 'shuft_list':
				$this->body_caption = 'シャフト一覧';

				break;
			case 'shuft_detail':
				$this->body_caption = 'シャフト詳細';
				//$this->product['shuft']['post_id'] = isset($_POST['selected_post_id']) ? (int)$_POST['selected_post_id'] : NULL;
				if(isset($_POST['selected_post_id'])) {
					if($_POST['selected_post_id'] != $this->product['shuft']['post_id']) {
						$this->product['shuft']['post_id'] = (int)$_POST['selected_post_id'];
						$this->product['shuft']['sku'] = NULL;
						$this->product['shuft']['price'] = NULL;
					}
				}

				break;
			case 'grip_list':
				$this->body_caption = 'グリップ一覧';

				break;
			case 'grip_detail':
				$this->body_caption = 'グリップ詳細';
				//$this->product['grip']['post_id'] = isset($_POST['selected_post_id']) ? (int)$_POST['selected_post_id'] : NULL;
				if(isset($_POST['selected_post_id'])) {
					if($_POST['selected_post_id'] != $this->product['grip']['post_id']) {
						$this->product['grip']['post_id'] = (int)$_POST['selected_post_id'];
						$this->product['grip']['sku'] = NULL;
						$this->product['grip']['price'] = NULL;
					}
				}

				break;
//			case 'select_item':
//				$this->body_caption = '商品詳細';
//			
//				break;
//			case 'enter_item':
//			
//				break;
			case 'decide':

				break;
			default:
				$this->body_caption = 'ヘッド一覧';
		}
		$this->get_session();
	}

	function get_session(){
//		$_SESSION['nsset']['product']['head']['post_id']
//		$_SESSION['nsset']['product']['head']['sku']
//		$_SESSION['nsset']['product']['shuft']
//		$_SESSION['nsset']['product']['grip']
//		$_SESSION['nsset']['product']
		if(!empty($_SESSION['nsset']['product']['head']['post_id'])) $this->product['head']['post_id'] = $_SESSION['nsset']['product']['head']['post_id'];
		if(!empty($_SESSION['nsset']['product']['head']['sku'])) $this->product['head']['sku'] = $_SESSION['nsset']['product']['head']['sku'];
		if(!empty($_SESSION['nsset']['product']['head']['price'])) $this->product['head']['price'] = $_SESSION['nsset']['product']['head']['price'];
		if(!empty($_SESSION['nsset']['product']['shuft']['post_id'])) $this->product['shuft']['post_id'] = $_SESSION['nsset']['product']['shuft']['post_id'];
		if(!empty($_SESSION['nsset']['product']['shuft']['sku'])) $this->product['shuft']['sku'] = $_SESSION['nsset']['product']['shuft']['sku'];
		if(!empty($_SESSION['nsset']['product']['shuft']['price'])) $this->product['shuft']['price'] = $_SESSION['nsset']['product']['shuft']['price'];
		if(!empty($_SESSION['nsset']['product']['grip']['post_id'])) $this->product['grip']['post_id'] = $_SESSION['nsset']['product']['grip']['post_id'];
		if(!empty($_SESSION['nsset']['product']['grip']['sku'])) $this->product['grip']['sku'] = $_SESSION['nsset']['product']['grip']['sku'];
		if(!empty($_SESSION['nsset']['product']['grip']['price'])) $this->product['grip']['price'] = $_SESSION['nsset']['product']['grip']['price'];
	}

	function set_session(){
//		$_SESSION['nsset']['product']['head']['post_id']
//		$_SESSION['nsset']['product']['head']['sku']
//		$_SESSION['nsset']['product']['shuft']
//		$_SESSION['nsset']['product']['grip']
//		$_SESSION['nsset']['product']
		if(isset($_POST['head_post_id'])) $_SESSION['nsset']['product']['head']['post_id'] = $_POST['head_post_id'];
		if(isset($_POST['head_sku'])) $_SESSION['nsset']['product']['head']['sku'] = $_POST['head_sku'];
		if(isset($_POST['head_price'])) $_SESSION['nsset']['product']['head']['price'] = $_POST['head_price'];
		if(isset($_POST['shuft_post_id'])) $_SESSION['nsset']['product']['shuft']['post_id'] = $_POST['shuft_post_id'];
		if(isset($_POST['shuft_sku'])) $_SESSION['nsset']['product']['shuft']['sku'] = $_POST['shuft_sku'];
		if(isset($_POST['shuft_price'])) $_SESSION['nsset']['product']['shuft']['price'] = $_POST['shuft_price'];
		if(isset($_POST['grip_post_id'])) $_SESSION['nsset']['product']['grip']['post_id'] = $_POST['grip_post_id'];
		if(isset($_POST['grip_sku'])) $_SESSION['nsset']['product']['grip']['sku'] = $_POST['grip_sku'];
		if(isset($_POST['grip_price'])) $_SESSION['nsset']['product']['grip']['price'] = $_POST['grip_price'];
	}

	function set_list_per_page( $per_page ){
		$this->posts_per_page = $per_page;
	}

	function get_list_query(){
		$cat_item = array();
		switch( $this->action ){
			case 'shuft_list':
				if ( in_category( 'setdriverhead', (int)$this->produnct['head']['post_id'] ) )
					$cat_item[] = usces_get_cat_id( 'setdrivershuft' );
					$cat_item []= usces_get_cat_id( 'setdriverfairwayshuft' );
				elseif ( in_category( 'setfairwayhead', (int)$this->produnct['head']['post_id'] ) )
					$cat_item []= usces_get_cat_id( 'setfairwayshuft' );
					$cat_item []= usces_get_cat_id( 'setdriverfairwayshuft' );
				elseif ( in_category( 'setironhead', (int)$this->produnct['head']['post_id'] ) )
					$cat_item[] = usces_get_cat_id( 'setironshuft' );
				elseif ( in_category( 'setutilityhead', (int)$this->produnct['head']['post_id'] ) )
					$cat_item[] = usces_get_cat_id( 'setutilityshuft' );
				elseif ( in_category( 'setwedgehead', (int)$this->produnct['head']['post_id'] ) )
					$cat_item[] = usces_get_cat_id( 'setwedgeshuft' );
				elseif ( in_category( 'setputterhead', (int)$this->produnct['head']['post_id'] ) )
					$cat_item[] = usces_get_cat_id( 'setputtershuft' );
					
				break;
			case 'grip_list':
				if ( in_category( 'setdrivershuft', (int)$this->produnct['shuft']['post_id'] ) 
					|| in_category( 'setfairwayshuft', (int)$this->produnct['shuft']['post_id'] ) 
					|| in_category( 'setdriverfairwayshuft', (int)$this->produnct['shuft']['post_id'] ) 
					|| in_category( 'setutilityshuft', (int)$this->produnct['shuft']['post_id'] ) 
					|| in_category( 'setwedgeshuft', (int)$this->produnct['shuft']['post_id'] ) 
					)
					$cat_item[] = usces_get_cat_id( 'setwoodirongrip' );
				elseif ( in_category( 'setputtershuft', (int)$this->produnct['head']['post_id'] ) )
					$cat_item []= usces_get_cat_id( 'setputtergrip' );

				break;
			case 'head_list':
			default:
				$cat_item[] = usces_get_cat_id( 'sethead' );
		}
		if( empty($cat_item) ){
			$category__and = array(99999);
		}else{
			$category__and = $cat_item;
		}
		$page = get_query_var( 'page' );
		$paged = empty($page) ? 1 : $page;
		$offset = $this->posts_per_page * ($paged - 1);
		
		$query = array(
			'category__and'		=> $category__and,
			'posts_per_page'	=> $this->posts_per_page, 
			'paged'				=> $paged, 
			'offset'			=> $offset, 
			'post_status'		=> 'publish'
		);
		return $query;
	}
	
	function get_top_info_class( $type ){
		$res = !empty($this->product[$type]['post_id']) ? 'gray' : 'white';
		return $res;
	}
	
	function get_top_name_class( $type ){
		$focus = $this->get_top_focus();
		switch( $type ){
			case 'head':
				$res = 'focused';
				break;
			case 'shuft':
				$res = ( 'shuft' == $focus || 'grip' == $focus || 'amount' == $focus ) ? 'focused' : '';
				break;
			case 'grip':
				$res = ( 'grip' == $focus || 'amount' == $focus ) ? 'focused' : '';
				break;
		}
		return $res;
	}
	
	function get_top_thumb( $type ){
		$res = !empty($this->product[$type]['post_id']) ? usces_get_itemImage( $this->product[$type]['post_id'], 0, 60, 60) : '';
		return $res;
	}

	function get_top_itemname( $type ){
		global $usces;
		$res = !empty($this->product[$type]['post_id']) ? $usces->getItemName($this->product[$type]['post_id']) : '選択してください';
		return $res;
	}

	function get_top_itemprice( $type ){
		if( empty($this->product[$type]['post_id']) || empty($this->product[$type]['sku']) )
			return;
			
		global $usces;
		$res = usces_get_item_price($this->product[$type]['post_id'], $this->product[$type]['sku']);
		//return $usces->get_currency($res, true, false);
		return $usces->get_currency($res, true, false, true);
	}

	function get_top_itemcprice( $type ){
		if( empty($this->product[$type]['post_id']) || empty($this->product[$type]['sku']) )
			return;
			
		global $usces;
		$res = usces_get_item_cprice($this->product[$type]['post_id'], $this->product[$type]['sku']);
		//return $usces->get_currency($res, true, false);
		return $usces->get_currency($res, true, false, true);
	}

	function get_top_change_button( $type ){
		$focus = $this->get_top_focus();
		switch( $type ){
			case 'head':
				$res = '<input name="head_list" type="submit" class="change_button" value="　" />';
				break;
			case 'shuft':
				//if( 'shuft' == $focus || 'grip' == $focus || 'amount' == $focus ){
				if( ('shuft' == $focus || 'grip' == $focus || 'amount' == $focus) and !empty($this->product['head']['sku']) ){
					$res = '<input name="shuft_list" type="submit" class="change_button" value="　" />';
				}else{
					$res = '<input name="shuft_list" type="button" class="change_button_dis" value="　" disabled="disabled" />';
				}
				break;
			case 'grip':
				//if( 'grip' == $focus || 'amount' == $focus ){
				if( ('grip' == $focus || 'amount' == $focus) and !empty($this->product['head']['sku']) and !empty($this->product['shuft']['sku']) ){
					$res = '<input name="grip_list" type="submit" class="change_button" value="　" />';
				}else{
					$res = '<input name="grip_list" type="button" class="change_button_dis" value="　" disabled="disabled" />';
				}
				break;
		}
		return $res;
	}

	function get_top_detail_button( $type ){
		$focus = $this->get_top_focus();
		switch( $type ){
			case 'head':
				//$res = '<input name="head_detail" type="submit" class="detail_button" value="　" />';
				if( !empty($this->product['head']['sku']) ){
					$res = '<input name="head_detail" type="submit" class="detail_button" value="　" />';
				}else{
					$res = '<input name="head_detail" type="button" class="detail_button_dis" value="　" disabled="disabled" />';
				}
				break;
			case 'shuft':
				//if( 'shuft' == $focus || 'grip' == $focus || 'amount' == $focus ){
				if( ('shuft' == $focus || 'grip' == $focus || 'amount' == $focus) and !empty($this->product['shuft']['sku']) ){
					$res = '<input name="shuft_detail" type="submit" class="detail_button" value="　" />';
				}else{
					$res = '<input name="shuft_detail" type="button" class="detail_button_dis" value="　" disabled="disabled" />';
				}
				break;
			case 'grip':
				//if( 'grip' == $focus || 'amount' == $focus ){
				if( ('grip' == $focus || 'amount' == $focus) and !empty($this->product['grip']['sku']) ) {
					$res = '<input name="grip_detail" type="submit" class="detail_button" value="　" />';
				}else{
					$res = '<input name="grip_detail" type="button" class="detail_button_dis" value="　" disabled="disabled" />';
				}
				break;
		}
		return $res;
	}

	function get_select_button_name() {
		switch( $this->action ) {
		case 'shuft_list':
			$res = 'shuft_detail';
			break;
		case 'grip_list':
			$res = 'grip_detail';
			break;
		case 'head_list':
		case 'default':
			$res = 'head_detail';
		}
		return $res;
	}

	function get_product_amount(){
		
	}

	function get_top_amount_mes(){
		$focus = $this->get_top_focus();
		$res = ( 'amount' == $focus ) ? 'kouchin' : '※パーツ構成が確定していません。';
		return $res;
	}
	
	function get_top_amount_button(){
		global $usces;
		$focus = $this->get_top_focus();
		if( 'amount' == $focus ){
			$post_id = $usces->get_postIDbyCode('dummySet');
			$skus = $usces->get_skus($post_id);
			$sku = $skus['key'][0];
			$price = $this->product['head']['price'] + $this->product['shuft']['price'] + $this->product['grip']['price'];
			$res  = '<form action="'.USCES_CART_URL.'" method="post">';
			$res .= '<input name="decide" type="submit" class="decide_button" value="　" />';
			$res .= '<input name="inCart['.$post_id.']['.$sku.']" type="hidden" value="　" />';
			$res .= '<input name="skuPrice['.$post_id.']['.$sku.']" type="hidden" value="'.$price.'" />';
			$res .= '<input name="advance['.$post_id.']['.$sku.'][head_post_id]" type="hidden" value="'.$this->product['head']['post_id'].'" />';
			$res .= '<input name="advance['.$post_id.']['.$sku.'][head_sku]" type="hidden" value="'.$this->product['head']['sku'].'" />';
			$res .= '<input name="advance['.$post_id.']['.$sku.'][shuft_post_id]" type="hidden" value="'.$this->product['shuft']['post_id'].'" />';
			$res .= '<input name="advance['.$post_id.']['.$sku.'][shuft_sku]" type="hidden" value="'.$this->product['shuft']['sku'].'" />';
			$res .= '<input name="advance['.$post_id.']['.$sku.'][grip_post_id]" type="hidden" value="'.$this->product['grip']['post_id'].'" />';
			$res .= '<input name="advance['.$post_id.']['.$sku.'][grip_sku]" type="hidden" value="'.$this->product['grip']['sku'].'" />';
			$res .= '</form>';
		}else{
			$res = '<input name="decide" type="button" class="decide_button_dis" value="　" disabled="disabled" />';
		}
		return $res;
	}

	function get_top_focus(){
		if( empty($this->product['head']['post_id']) && empty($this->product['shuft']['post_id']) && empty($this->product['grip']['post_id'])){
			$res = 'head';
		}elseif( !empty($this->product['head']['post_id']) && empty($this->product['shuft']['post_id'])){
			$res = 'shuft';
		//}elseif( !empty($this->product['shuft']['post_id']) && empty($this->product['grid']['post_id'])){
		}elseif( !empty($this->product['shuft']['post_id']) && empty($this->product['grip']['post_id'])){
			$res = 'grip';
		}else{
			$res = 'amount';
		}
		return $res;
	}
	
	function body_caption(){
		return $this->body_caption;
	}

	function view_item_detail(){
		global $usces;
		$post_id = '';
		$type = '';
		$next_action = '';

		switch( $this->action ) {
		case 'head_detail':
			$post_id = $this->product['head']['post_id'];
			$type = 'head';
			$next_action = 'shuft_list';
			break;
		case 'shuft_detail':
			$post_id = $this->product['shuft']['post_id'];
			$type = 'shuft';
			$next_action = 'grip_list';
			break;
		case 'grip_detail':
			$post_id = $this->product['grip']['post_id'];
			$type = 'grip';
			$next_action = 'decide';
			break;
		default:
		}
		if($post_id == '') return;
		$post = new NS_Post();
		$post->set_id( $post_id );

		get_currentuserinfo();
		if( $usces->is_cart_or_member_page($_SERVER['REQUEST_URI']) || $usces->is_inquiry_page($_SERVER['REQUEST_URI']) ){
			$javascript_url = USCES_FRONT_PLUGIN_URL . '/js/usces_cart.js';
		}else{
			$javascript_url = USCES_WP_CONTENT_URL . '/plugins/' . USCES_PLUGIN_FOLDER . '/js/usces_cart.js';
		}

		NS_the_item($post);
		usces_have_skus();

		$ioptkeys = $usces->get_itemOptionKey( $post->ID );
		$mes_opts_str = "";
		$key_opts_str = "";
		$opt_means = "";
		$opt_esse = "";
		if($ioptkeys){
			foreach($ioptkeys as $key => $value){
				$optValues = $usces->get_itemOptions( $value, $post->ID );
				if($optValues['means'] < 2){
					$mes_opts_str .= "'" . sprintf(__("Chose the %s", 'usces'), $value) . "',";
				}else{
					$mes_opts_str .= "'" . sprintf(__("Input the %s", 'usces'), $value) . "',";
				}
				$key_opts_str .= "'{$value}',";
				$opt_means .= "'{$optValues['means']}',";
				$opt_esse .= "'{$optValues['essential']}',";
			}
			$mes_opts_str = rtrim($mes_opts_str, ',');
			$key_opts_str = rtrim($key_opts_str, ',');
			$opt_means = rtrim($opt_means, ',');
			$opt_esse = rtrim($opt_esse, ',');
		}
		$itemRestriction = get_post_custom_values('_itemRestriction', $post->ID);
?>
		<script type='text/javascript'>
		/* <![CDATA[ */
			uscesL10n = {
				'ajaxurl': "<?php echo USCES_SSL_URL; ?>/",
				'post_id': "<?php echo $post->ID; ?>",
				'opt_esse': new Array( <?php echo $opt_esse; ?> ),
				'opt_means': new Array( <?php echo $opt_means; ?> ),
				'mes_opts': new Array( <?php echo $mes_opts_str; ?> ),
				'key_opts': new Array( <?php echo $key_opts_str; ?> ), 
				'itemRestriction': "<?php echo $itemRestriction[0]; ?>"
			}
		/* ]]> */
		</script>
		<script type='text/javascript' src='<?php echo $javascript_url; ?>'></script>
		<script type='text/javascript'>
		(function($) {
		uscesCart = {
			intoCart : function (post_id, sku) {
<?php
			if( NS_have_sku_option($post) ) {
?>
				var opterr = 0;
				$(':input[name^="opt"]').each(function(i, obj) {
					if($(this).val() == '') {
						name = $(this).attr("name").substring(3);
						alert(name+'を選択してください。');
						opterr++;
						return false;
					}
				});
				if(opterr != 0) return false;
				$(':input[name^="iopt"]').each(function(i, obj) {
					name = $(this).attr("name").substring(4);
					$('#sku_option_button').append('<input name="itemOption['+post_id+']['+sku+']['+name+']" type="hidden" value="'+$(this).val()+'">');
				});
<?php
			}
?>
				
				var zaikonum = document.getElementById("zaikonum["+post_id+"]["+sku+"]").value;
				var zaiko = document.getElementById("zaiko["+post_id+"]["+sku+"]").value;
				if( (zaiko != '0' && zaiko != '1') ||  parseInt(zaikonum) == 0 ){
					alert('<?php _e('temporaly out of stock now', 'usces'); ?>');
					return false;
				}
				
				var mes = '';
				for(i=0; i<uscesL10n.key_opts.length; i++){
					var skuob = document.getElementById("itemOption["+post_id+"]["+sku+"]["+uscesL10n.key_opts[i]+"]");
					if( uscesL10n.opt_esse[i] == '1' ){
						
						if( uscesL10n.opt_means[i] < 2 && skuob.value == '#NONE#' ){
							mes += uscesL10n.mes_opts[i]+"\n";
						}else if( uscesL10n.opt_means[i] >= 2 && skuob.value == '' ){
							mes += uscesL10n.mes_opts[i]+"\n";
						}
					}
				}
				
				if( mes != '' ){
					alert( mes );
					return false;
				}
			},
			
<?php
			if( NS_have_sku_option($post) ) {
?>
			changeSkuSelect : function(index) {
				var id = '#opt'+index;
				var value = $(id).val();
				if(value == '') return;
				var key = $(id).attr("name");
				$('#sku_option_message').html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading.gif" /> 検索中').addClass('sku_option_status_message').removeClass('sku_option_error_message');

				var skukey = '';
				var skuoption = '';
				var sp = '';
				if(1 < index) {
					$(':input[name^="opt"]').each(function(i, obj) {
						if(i < (index-1)) {
							skukey += sp + $(this).attr("name").substring(3);
							skuoption += sp + $(this).val();
							sp = '#usces#';
						}
					});
				}

				var nextskukey = '';
				var skucnt = $('#skucnt').val();
				if(index < skucnt) {
					nextskukey = $('#opt'+(index+1)).attr("name").substring(3);
				}

				var s = this.settings;
				s.url = "<?php bloginfo('home'); ?>/index.php";
				s.data = "usces_ajax_action=change_sku_option_ajax&post_id=<?php echo $post->ID; ?>&key="+key.substring(3)+"&value="+value+"&index="+index+"&skukey="+skukey+"&skuoption="+skuoption+"&nextskukey="+nextskukey+"&set=1&type=<?php echo $type; ?>&nextaction=<?php echo $next_action; ?>";
				s.success = function(data, dataType) {
					d = data.split('#usces#');
					var sku = (d[0].match(/#ns#/i)) ? d[0].split("#ns#") : d[0];
					var nextval = (d[1].match(/#ns#/i)) ? d[1].split("#ns#") : new Array(d[1]);
					var optkey = (d[2].match(/#ns#/i)) ? d[2].split("#ns#") : new Array(d[2]);
					var optval = (d[3].match(/#ns#/i)) ? d[3].split("#ns#") : new Array(d[3]);
					var skuprice = d[4];
					var zaikonum = d[5];
					var html = d[6];
					var msg = d[7];
					if(nextskukey != '') {
						$(':input[name="opt'+nextskukey+'"]').attr('disabled', false);
						$(':input[name="opt'+nextskukey+'"]').html('');
						var nextopt = '<option value="">選択してください</option>';
						for(i = 0; i < nextval.length; i++) {
							nextopt += '<option value="'+nextval[i]+'">'+nextval[i]+'</option>';
						}
						$(':input[name="opt'+nextskukey+'"]').html(nextopt);
						$(':input[name="opt'+nextskukey+'"]').val(nextval);
						$(':input[name="opt'+nextskukey+'"]').attr('selectedIndex', 0);
					}
					if(0 < optkey.length && optkey[0] != '') {
						for(i = 0; i < optkey.length; i++) {
							if(index < $(':input[name="opt'+optkey[i]+'"]').attr('id').substring(3)) {
								$(':input[name="opt'+optkey[i]+'"]').attr('disabled', false);
								$(':input[name="opt'+optkey[i]+'"]').html('');
								if($(':input[name="opt'+optkey[i]+'"]').attr("class") == 'sku_option_select_field') {
									var opt = '<option value="">選択してください</option><option value="'+optval[i]+'">'+optval[i]+'</option>';
									$(':input[name="opt'+optkey[i]+'"]').html(opt);
								}
							}
							$(':input[name="opt'+optkey[i]+'"]').val(optval[i]);
						}
						$('#sku').val(sku);
						$('#sku_option_price').text(skuprice);
					} else {
						$(':input[name^="opt"]').each(function(i, obj) {
							if(index < i) {
								if($(this).attr("class") == 'sku_option_select_field') {
									$(this).html('<option value="">選択してください</option>');
									$(this).attr('disabled', true);
								}
								$(this).val('');
							}
						});
						$('#sku_option_price').text('');
					}
					$('#sku_option_button').html(html);
					if(msg){
						$('#sku_option_message').html(msg).addClass('sku_option_error_message').removeClass('sku_option_status_message');
					}else if($('#sku_option_price').text()){
						$('#sku_option_message').html('品番 ： ' + sku).removeClass('sku_option_error_message');
					}else{
						$('#sku_option_message').html('').removeClass('sku_option_error_message');
					}
					
				};
				$.ajax( s );
				return false;
			},
			
<?php
			}
?>
			settings: {
				url: uscesL10n.ajaxurl,
				type: 'POST',
				cache: false,
				success: function(data, dataType){
					//$("tbody#item-opt-list").html( data );
				}, 
				error: function(msg){
					//$("#ajax-response").html(msg);
				}
			},
			
			isNum : function (num) {
				if (num.match(/[^0-9]/g)) {
					return false;
				}
				return true;
			}

		};
		})(jQuery);
<?php
			if( NS_have_sku_option($post) ) {
?>
		
		jQuery(document).ready(function($) {
			if(0 < $('#skucnt').val()) {
				$(':input[name^="opt"]').each(function(i, obj) {
					if($(this).attr('class') == 'sku_option_select_field') {
						$(this).attr('selectedIndex', 0);
						$('#opt'+(i+1)).change(function() {
							uscesCart.changeSkuSelect(i+1);
						});
						if(0 < i) $(this).attr('disabled', true);
					} else {
						$(this).val('');
					}
				});
			}
		});
<?php
			}
?>
		</script>
		<div id="itempage" class="border_arround clearfix">
			<div class="item_header clearfix">
				<div class="item_info alignleft">
					<div class="item_maker"><?php NS_the_item_maker(); ?></div>
					<h2 class="item_name"><?php usces_the_itemName('', $post); ?></h2>
				</div>
				<div class="item_addition alignright">
					<div class="sale_tag"><?php NS_the_salse_tag(); ?></div>
					<div class="itemstar"><?php NS_the_item_star($post); ?></div>
				</div>
			</div>
			<div class="item_exp_1 clear">
				<?php the_content(); ?>
			</div>
			<div class="itemdetail_left">
				<div class="itemimg border_arround">
					<a href="<?php usces_the_itemImageURL(0, '', $post); ?>" <?php echo apply_filters('usces_itemimg_anchor_rel', NULL); ?>><?php usces_the_itemImage(0, 300, 300, $post); ?></a>
				</div>
			<?php $imageid = NS_get_itemSubImageNums($post); ?>
			<?php if($imageid): $count = 1;?>
				<div class="itemsubimg">
				<ul class="clearfix">
				<?php foreach ( $imageid as $id ) : ?>
					<li class="subimg_<?php echo $id; ?>"><a href="<?php usces_the_itemImageURL($id, $post); ?>" <?php echo apply_filters('usces_itemimg_anchor_rel', NULL); ?>><?php usces_the_itemImage($id, 55, 55, $post); ?></a></li>
					<?php if ($count >= 5) break;?>
					<?php $count += 1; ?>
				<?php endforeach; ?>
				</ul>
				</div><!-- end of itemsubimg -->
			<?php endif; ?>

				<div class="item_country textright">
					<?php NS_the_item_country($post); NS_have_sku_option($post); ?>
				</div>
				<div class="item_exp_2">
					<?php NS_the_item_explanation(2, $post); ?>
				</div>
			</div>
			<div class="itemdetail_right">
				<div class="tag_field">
				<?php NS_the_fantastic4($post); ?>
				</div>
				<div class="item_field clear">
					<div class="field_name">商品コード</div>
					<div class="field_code">：<?php NS_the_itemCode($post); ?></div>

					<div class="field_name">商品名</div>
					<div class="field_itemname">：<?php usces_the_itemName('', $post); ?></div>
		<?php if(usces_sku_num() === 1) : ?>
				<?php if( usces_the_itemCprice('return') > 0 ) : ?>
					<div class="field_name">通常価格</div>
					<div class="field_cprice">：<?php usces_the_itemCpriceCr(); ?></div>
				<?php endif; ?>
					<div class="field_name">販売価格</div>
					<div class="field_cprice">：<?php usces_the_itemPriceCr(); ?></div>

					<div class="field_name">残り在庫</div>
					<div class="field_cprice">：<?php usces_the_itemZaiko(); ?></div>
				</div>
				<div class="item_exp_3"><?php NS_the_item_explanation(3, $post); ?></div>

				<form action="#capture" method="post">
					<div class="skuform">
					<?php //_e('Please appoint an option.', 'usces'); ?>
					<?php if (usces_is_options()) : ?>
						<div class="item_option">
							<table id="option_list">
						<?php while (usces_have_options()) : ?>
							<tr>
								<th><?php usces_the_itemOptName(); ?></th>
								<td><?php NS_the_itemOption(usces_getItemOptName(), '', $post); ?></td>
							</tr>
						<?php endwhile; ?>
							</table>
						</div>
					<?php endif; ?>
					<?php if( $item_custom = usces_get_item_custom( $post->ID, 'list', 'return' ) ) : ?>
						<div class="field"><?php echo $item_custom; ?></div>
					<?php endif; ?>
						<div class="send_info">発送日目安：<?php NS_the_shipment_aim($post); ?></div>
					<?php if( !usces_have_zaiko() ) : ?>
						<div class="zaiko_status"><?php echo apply_filters('usces_filters_single_sku_zaiko_message', __('Sold Out', 'usces')); ?></div>
					<?php else : ?>
						<div style="margin:10px 0"><?php _e('Quantity', 'usces'); ?><?php usces_the_itemQuant(); ?><?php usces_the_itemSkuUnit(); ?></div>
						<div><?php ntstg_the_itemSkuButton(__('Add to Shopping Cart', 'usces'), 0, $post); ?></div>
						<div class="error_message"><?php usces_singleitem_error_message($post->ID, usces_the_itemSku('return')); ?></div>
					<?php endif; ?>
					</div><!-- end of skuform -->
				</form>

		<?php elseif(usces_sku_num() > 1) : ?>
		<!--some SKU-->
				<?php if( usces_the_itemCprice('return') > 0 ) : ?>
					<div class="field_name">通常価格</div>
					<div class="field_cprice">：<?php usces_crform( usces_the_firstCprice('return', $post), true, false ); ?></div>
				<?php endif; ?>
					<div class="field_name">販売価格</div>
					<div class="field_cprice price">：<?php NS_the_item_pricesCr($post); ?></div>
			</div>
			<div class="item_exp_3"><?php NS_the_item_explanation(3, $post); ?></div>
			<?php if( NS_have_sku_option($post) ) : ?>
				<form action="#capture" method="post">
					<div class="skuform">
					<?php //_e('Please appoint an option.', 'usces'); ?>
					<?php if (usces_is_options()) : ?>
						<div class="item_option">
							<table id="option_list">
						<?php while (usces_have_options()) : ?>
							<tr>
								<th><?php usces_the_itemOptName(); ?></th>
								<td><?php NS_the_itemOption(usces_getItemOptName(), '', $post); ?></td>
							</tr>
						<?php endwhile; ?>
							</table>
						</div>
					<?php endif; ?>
					<?php if( $item_custom = usces_get_item_custom( $post->ID, 'list', 'return' ) ) : ?>
						<div class="field"><?php echo $item_custom; ?></div>
					<?php endif; ?>
						<div class="send_info">発送日目安：<?php NS_the_shipment_aim($post); ?></div>
						<div id="sku_option_field" class="sku_option_box"><?php NS_sku_option_field($post); ?></div>
						<!--<div class="sku_option_quant"><?php _e('Quantity', 'usces'); ?><?php NS_the_itemQuant($post); ?><?php usces_the_itemSkuUnit(); ?></div>-->
						<div id="sku_option_price"></div>
						<div id="sku_option_message"></div>
						<div id="sku_option_button"><?php NS_the_itemSkuButton(__('Add to Shopping Cart', 'usces'), 0, $post); ?></div>
						<div class="error_message"><?php usces_singleitem_error_message($post->ID, usces_the_itemSku('return')); ?></div>
					</div><!-- end of skuform -->
					<?php echo apply_filters('single_item_single_sku_after_field', NULL); ?>
					<?php do_action('usces_action_single_item_inform'); ?>
				</form>
			<?php else : ?>
			<form action="#capture" method="post">
			<div class="skuform">
				<table class="skumulti">
					<thead>
					<tr>
						<th colspan="2"><?php _e('Title', 'usces'); ?></th>
						<th colspan="1"><?php _e('selling price', 'usces'); ?><?php usces_guid_tax(); ?></th>
					</tr>
					<tr>
						<th class="thborder">在庫</th>
						<th class="thborder"><?php _e('Quantity', 'usces'); ?></th>
						<th class="thborder">&nbsp;</th>
					</tr>
					</thead>
					<tbody>
			<?php do { ?>
					<tr>
						<td colspan="2" class="skudisp subborder"><?php if( '' != usces_the_itemSkuDisp('return') ) usces_the_itemSkuDisp(); else usces_the_itemSku(); ?>
						<?php if( usces_is_options() ): ?>
								<table id="item_option">
									<?php while (usces_have_options()) : ?>
									<tr>
										<th><?php usces_the_itemOptName(); ?></th>
										<td><?php usces_the_itemOption(usces_getItemOptName(),''); ?></td>
									</tr>
									<?php endwhile; ?>
								</table>
						<?php endif; ?>
						</td>
						<td colspan="1" class="subborder price"><span class="price"><?php usces_the_itemPriceCr(); ?></span></td>
					</tr>
					<tr>
						<td class="zaiko"><?php usces_the_itemZaiko(); ?></td>
						<!--<td class="quant"><?php usces_the_itemQuant(); ?><?php usces_the_itemSkuUnit(); ?></td>-->
					<?php if( !usces_have_zaiko() ) : ?>
						<td class="button"><?php echo apply_filters('usces_filters_single_sku_zaiko_message', __('Sold Out', 'usces')); ?></td>
					<?php else : ?>
						<td class="button"><?php usces_the_itemSkuButton(__('Add to Shopping Cart', 'usces'), 0); ?></td>
					<?php endif; ?>
					</tr>
					<tr>
						<td colspan="3" class="error_message"><?php usces_singleitem_error_message($post->ID, usces_the_itemSku('return')); ?></td>
					</tr>
			<?php } while (usces_have_skus()); ?>
					</tbody>
				</table>
			</div><!-- end of skuform -->
			</form>
			<?php endif; ?>
		<?php endif; ?>
		</div>
		</div><!-- end of itemspage -->
		<div class="item_detail">
			<h3 class="titlebar">この商品の解説</h2>
			<div class="item_exp_4"><?php NS_the_item_explanation(4, $post); ?></div>
			<?php NS_the_sku_list($post); ?>
		</div>
<?php
	}
}

class NS_Post {
	var $ID;

	function __construct() {
	}

	function set_id( $id ) {
		$this->ID = $id;
	}
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
	//$skus = unserialize($field);
	$skus = maybe_unserialize($field);
	return $skus['price'];
}

function usces_get_item_cprice($post_id, $sku){
	global $usces;

}
?>