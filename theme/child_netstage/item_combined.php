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
				<?php if($NSSP->is_mochikomihin('head')) : ?>
				<div class="mochi_name <?php echo $NSSP->get_top_name_class('head'); ?>"><?php echo $NSSP->get_top_mochikomihin('head'); ?></div>
				<?php else : ?>
				<div class="set_thumbnail"><?php echo $NSSP->get_top_thumb('head'); ?></div>
				<div class="item_name <?php echo $NSSP->get_top_name_class('head'); ?>"><?php echo $NSSP->get_top_itemname('head'); ?></div>
				<div class="item_price">販売価格：<?php echo $NSSP->get_top_itemprice('head'); ?></div>
				<div class="item_cprice">通常価格：<?php echo $NSSP->get_top_itemcprice('head'); ?></div>
				<?php endif; ?>
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
				<?php if($NSSP->is_mochikomihin('shuft')) : ?>
				<div class="mochi_name <?php echo $NSSP->get_top_name_class('shuft'); ?>"><?php echo $NSSP->get_top_mochikomihin('shuft'); ?></div>
				<?php else : ?>
				<div class="set_thumbnail"><?php echo $NSSP->get_top_thumb('shuft'); ?></div>
				<div class="item_name <?php echo $NSSP->get_top_name_class('shuft'); ?>"><?php echo $NSSP->get_top_itemname('shuft'); ?></div>
				<div class="item_price">販売価格：<?php echo $NSSP->get_top_itemprice('shuft'); ?></div>
				<div class="item_cprice">通常価格：<?php echo $NSSP->get_top_itemcprice('shuft'); ?></div>
				<?php endif; ?>
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
				<?php if($NSSP->is_mochikomihin('grip')) : ?>
				<div class="mochi_name <?php echo $NSSP->get_top_name_class('grip'); ?>"><?php echo $NSSP->get_top_mochikomihin('grip'); ?></div>
				<?php else : ?>
				<div class="set_thumbnail"><?php echo $NSSP->get_top_thumb('grip'); ?></div>
				<div class="item_name <?php echo $NSSP->get_top_name_class('grip'); ?>"><?php echo $NSSP->get_top_itemname('grip'); ?></div>
				<div class="item_price">販売価格：<?php echo $NSSP->get_top_itemprice('grip'); ?></div>
				<div class="item_cprice">通常価格：<?php echo $NSSP->get_top_itemcprice('grip'); ?></div>
				<?php endif; ?>
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
		
		<?php /* リスト表示 */ if( 'head_list' == $NSSP->action || 'shuft_list' == $NSSP->action || 'grip_list' == $NSSP->action || 'default' == $NSSP->action || 'decide' == $NSSP->action ) : ?>
		<div class="clearfix">
		<?php NS_mochikomihin($NSSP->action); ?>
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
<?php //usces_p($NSSP->product);?>
<?php //usces_p($NSSP->action); ?>
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
		$this->get_session();

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
						$this->product['head']['options'] = NULL;
						$this->init_session( 'shuft' );
						$this->init_session( 'grip' );
					}
				}

				break;
			case 'shuft_list':
				$this->body_caption = 'シャフト一覧';
				if( isset($_POST['head_post_id']) ) {
					$this->product['head']['post_id'] = (int)$_POST['head_post_id'];
					$this->product['head']['sku'] = $_POST['head_sku'];
					$this->product['head']['price'] = (int)$_POST['head_price'];
					if( -1 == $_POST['head_post_id'] ){
						$this->product['head']['options'] = $_POST['mochiopt'];
					}else{
						$this->product['head']['options'] = $_POST['head_options'];
					}
				}

				break;
			case 'shuft_detail':
				$this->body_caption = 'シャフト詳細';
				//$this->product['shuft']['post_id'] = isset($_POST['selected_post_id']) ? (int)$_POST['selected_post_id'] : NULL;
				if(isset($_POST['selected_post_id'])) {
					if($_POST['selected_post_id'] != $this->product['shuft']['post_id']) {
						$this->product['shuft']['post_id'] = (int)$_POST['selected_post_id'];
						$this->product['shuft']['sku'] = NULL;
						$this->product['shuft']['price'] = NULL;
						$this->product['shuft']['options'] = NULL;
						$this->init_session( 'grip' );
					}
				}

				break;
			case 'grip_list':
				$this->body_caption = 'グリップ一覧';
				if( isset($_POST['shuft_post_id']) ) {
					$this->product['shuft']['post_id'] = (int)$_POST['shuft_post_id'];
					$this->product['shuft']['sku'] = $_POST['shuft_sku'];
					$this->product['shuft']['price'] = (int)$_POST['shuft_price'];
					if( -2 == $_POST['shuft_post_id'] ){
						$this->product['shuft']['options'] = $_POST['mochiopt'];
					}else{
						$this->product['shuft']['options'] = $_POST['shuft_options'];
					}
				}

				break;
			case 'grip_detail':
				$this->body_caption = 'グリップ詳細';
				//$this->product['grip']['post_id'] = isset($_POST['selected_post_id']) ? (int)$_POST['selected_post_id'] : NULL;
				if(isset($_POST['selected_post_id'])) {
					if($_POST['selected_post_id'] != $this->product['grip']['post_id']) {
						$this->product['grip']['post_id'] = (int)$_POST['selected_post_id'];
						$this->product['grip']['sku'] = NULL;
						$this->product['grip']['price'] = NULL;
						$this->product['grip']['options'] = NULL;
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
				$this->body_caption = 'ヘッド一覧';
				if( isset($_POST['grip_post_id']) ) {
					$this->product['grip']['post_id'] = (int)$_POST['grip_post_id'];
					$this->product['grip']['sku'] = $_POST['grip_sku'];
					$this->product['grip']['price'] = (int)$_POST['grip_price'];
					if( -3 == $_POST['grip_post_id'] ){
						$this->product['grip']['options'] = $_POST['mochiopt'];
					}else{
						$this->product['grip']['options'] = $_POST['grip_options'];
					}
				}

				break;
			default:
				$this->body_caption = 'ヘッド一覧';
		}
		$this->set_session();
	}

	function init_session( $type ){
//		foreach($ses as $val){
//			if( is_array($val) ){
//				$this->init_session( $val );
//			}else{
//				$val = NULL;
//			}
//		}
		$this->product[$type]['post_id'] = NULL;
		$this->product[$type]['sku'] = NULL;
		$this->product[$type]['price'] = NULL;
		$this->product[$type]['options'] = NULL;
	}

	function get_session(){
		
		if( !isset($_SESSION['nsset']['product']['head']['post_id']) )
			$_SESSION['nsset']['product']['head']['post_id'] = NULL;
		if( !isset($_SESSION['nsset']['product']['head']['sku']) )
			$_SESSION['nsset']['product']['head']['sku'] = NULL;
		if( !isset($_SESSION['nsset']['product']['head']['price']) )
			$_SESSION['nsset']['product']['head']['price'] = NULL;
		if( !isset($_SESSION['nsset']['product']['head']['options']) )
			$_SESSION['nsset']['product']['head']['options'] = NULL;
		if( !isset($_SESSION['nsset']['product']['shuft']['post_id']) )
			$_SESSION['nsset']['product']['shuft']['post_id'] = NULL;
		if( !isset($_SESSION['nsset']['product']['shuft']['sku']) )
			$_SESSION['nsset']['product']['shuft']['sku'] = NULL;
		if( !isset($_SESSION['nsset']['product']['shuft']['price']) )
			$_SESSION['nsset']['product']['shuft']['price'] = NULL;
		if( !isset($_SESSION['nsset']['product']['shuft']['options']) )
			$_SESSION['nsset']['product']['shuft']['options'] = NULL;
		if( !isset($_SESSION['nsset']['product']['grip']['post_id']) )
			$_SESSION['nsset']['product']['grip']['post_id'] = NULL;
		if( !isset($_SESSION['nsset']['product']['grip']['sku']) )
			$_SESSION['nsset']['product']['grip']['sku'] = NULL;
		if( !isset($_SESSION['nsset']['product']['grip']['price']) )
			$_SESSION['nsset']['product']['grip']['price'] = NULL;
		if( !isset($_SESSION['nsset']['product']['grip']['options']) )
			$_SESSION['nsset']['product']['grip']['options'] = NULL;

		$this->product = $_SESSION['nsset']['product'];
	}

	function set_session(){

		$_SESSION['nsset']['product'] = $this->product;

	}

	function set_list_per_page( $per_page ){
		$this->posts_per_page = $per_page;
	}

	function get_list_query(){
		$cat_item = array();
		switch( $this->action ){
			case 'shuft_list':
				if ( in_category( 'setdriverhead', (int)$this->product['head']['post_id'] ) || ( -1 == $this->product['head']['post_id'] && 'setdriverhead' == $this->product['head']['options']['genre']) ) {
					$cat_item[] = (int)usces_get_cat_id( 'setdrivershuft' );
					$cat_item[] = (int)usces_get_cat_id( 'setdriverfairwayshuft' );
				} elseif ( in_category( 'setfairwayhead', (int)$this->product['head']['post_id'] ) || ( -1 == $this->product['head']['post_id'] && 'setfairwayhead' == $this->product['head']['options']['genre']) ) {
					$cat_item[] = (int)usces_get_cat_id( 'setfairwayshuft' );
					$cat_item[] = (int)usces_get_cat_id( 'setdriverfairwayshuft' );
				} elseif ( in_category( 'setironhead', (int)$this->product['head']['post_id'] ) || ( -1 == $this->product['head']['post_id'] && 'setironhead' == $this->product['head']['options']['genre']) ) {
					$cat_item[] = (int)usces_get_cat_id( 'setironshuft' );
				} elseif ( in_category( 'setutilityhead', (int)$this->product['head']['post_id'] ) || ( -1 == $this->product['head']['post_id'] && 'setutilityhead' == $this->product['head']['options']['genre']) ) {
					$cat_item[] = (int)usces_get_cat_id( 'setutilityshuft' );
				} elseif ( in_category( 'setwedgehead', (int)$this->product['head']['post_id'] ) || ( -1 == $this->product['head']['post_id'] && 'setwedgehead' == $this->product['head']['options']['genre']) ) {
					$cat_item[] = (int)usces_get_cat_id( 'setwedgeshuft' );
				} elseif ( in_category( 'setputterhead', (int)$this->product['head']['post_id'] ) || ( -1 == $this->product['head']['post_id'] && 'setputterhead' == $this->product['head']['options']['genre']) ) {
					$cat_item[] = (int)usces_get_cat_id( 'setputtershuft' );
				}
				
				break;
			case 'grip_list':
				if ( in_category( 'setdrivershuft', (int)$this->product['shuft']['post_id'] ) 
					|| in_category( 'setfairwayshuft', (int)$this->product['shuft']['post_id'] ) 
					|| in_category( 'setdriverfairwayshuft', (int)$this->product['shuft']['post_id'] ) 
					|| in_category( 'setutilityshuft', (int)$this->product['shuft']['post_id'] ) 
					|| in_category( 'setwedgeshuft', (int)$this->product['shuft']['post_id'] ) 
					|| ( -2 == $this->product['shuft']['post_id'] && in_array($this->product['shuft']['options']['genre'], array('setdrivershuft', 'setfairwayshuft', 'setdriverfairwayshuft', 'setutilityshuft', 'setwedgeshuft', )))
					){
					$cat_item[] = (int)usces_get_cat_id( 'setwoodirongrip' );
				} elseif ( in_category( 'setputtershuft', (int)$this->product['shuft']['post_id'] ) || ( -2 == $this->product['shuft']['post_id'] && 'setputtershuft' == $this->product['shuft']['options']['genre']) ){
					$cat_item[] = (int)usces_get_cat_id( 'setputtergrip' );
				}

				break;
			case 'decide':
			case 'head_list':
			default:
				$cat_item[] = (int)usces_get_cat_id( 'sethead' );
		}
		if( empty($cat_item) ){
			$category__in = array(99999);
		}else{
			$category__in = $cat_item;
		}
		$page = get_query_var( 'page' );
		$paged = empty($page) ? 1 : $page;
		$offset = $this->posts_per_page * ($paged - 1);
		
		$query = array(
			'category__in'		=> $category__in,
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
		return $usces->get_currency($res, true, false, true);
	}

	function get_top_itemcprice( $type ){
		if( empty($this->product[$type]['post_id']) || empty($this->product[$type]['sku']) )
			return;

		global $usces;
		$res = usces_get_item_cprice($this->product[$type]['post_id'], $this->product[$type]['sku']);
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
		global $usces;
		$focus = $this->get_top_focus();
		//$res = ( 'amount' == $focus ) ? 'kouchin' : '※パーツ構成が確定していません。';
		if( 'amount' == $focus and !empty($this->product['head']['sku']) and !empty($this->product['shuft']['sku']) and !empty($this->product['grip']['sku']) ){
			$post_id = $usces->get_postIDbyCode( NS_ITEM_SET );
			$sku = (in_category( 'straightbore', $this->product['head']['post_id'] )) ? 'straightbore' : 'normal';
			$set_price = usces_get_item_price($post_id, $sku);
			$res = usces_crform($this->product['head']['price'] + $this->product['shuft']['price'] + $this->product['grip']['price'] + $set_price, true, false, 'return');
		} else {
			$res = '※パーツ構成が確定していません。';
		}
		return $res;
	}
	
	function get_top_amount_button(){
		global $usces;
		$focus = $this->get_top_focus();
		//if( 'amount' == $focus ){
		if( 'amount' == $focus and !empty($this->product['head']['sku']) and !empty($this->product['shuft']['sku']) and !empty($this->product['grip']['sku']) ){
			$post_id = $usces->get_postIDbyCode( NS_ITEM_SET );
			//$skus = $usces->get_skus($post_id);
			$sku = (in_category( 'straightbore', $this->product['head']['post_id'] )) ? 'straightbore' : 'normal';
			$set_price = usces_get_item_price($post_id, $sku);
			$price = $this->product['head']['price'] + $this->product['shuft']['price'] + $this->product['grip']['price'] + $set_price;
			$res  = '<form action="'.USCES_CART_URL.'" method="post">';
			$res .= '<input name="decide" type="submit" class="decide_button" value="　" />';
			$res .= '<input name="inCart['.$post_id.']['.$sku.']" type="hidden" value="　" />';
			$res .= '<input name="skuPrice['.$post_id.']['.$sku.']" type="hidden" value="'.$price.'" />';
			$res .= '<input name="itemOption['.$post_id.']['.$sku.'][set_head]" type="hidden" value="'.$this->product['head']['post_id'].'" />';
			$res .= '<input name="itemOption['.$post_id.']['.$sku.'][set_head_sku]" type="hidden" value="'.$this->product['head']['sku'].'" />';
			$res .= '<input name="itemOption['.$post_id.']['.$sku.'][set_shuft]" type="hidden" value="'.$this->product['shuft']['post_id'].'" />';
			$res .= '<input name="itemOption['.$post_id.']['.$sku.'][set_shuft_sku]" type="hidden" value="'.$this->product['shuft']['sku'].'" />';
			$res .= '<input name="itemOption['.$post_id.']['.$sku.'][set_grip]" type="hidden" value="'.$this->product['grip']['post_id'].'" />';
			$res .= '<input name="itemOption['.$post_id.']['.$sku.'][set_grip_sku]" type="hidden" value="'.$this->product['grip']['sku'].'" />';
			foreach((array)$this->product['head']['options'] as $key => $value) {
				$res .= '<input name="advance['.$post_id.']['.$sku.'][set_head_options]['.$key.']" type="hidden" value="'.$value.'" />';
			}
			foreach((array)$this->product['shuft']['options'] as $key => $value) {
				$res .= '<input name="advance['.$post_id.']['.$sku.'][set_shuft_options]['.$key.']" type="hidden" value="'.$value.'" />';
			}
			foreach((array)$this->product['grip']['options'] as $key => $value) {
				$res .= '<input name="advance['.$post_id.']['.$sku.'][set_grip_options]['.$key.']" type="hidden" value="'.$value.'" />';
			}
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

		global $usces;
		$post = new NS_Post();
		$post->set_id( $post_id );

		get_currentuserinfo();

		NS_the_item($post);
		usces_have_skus();

		$ioptkeys = $usces->get_itemOptionKey( $post->ID );
		$mes_opts_str = "";
		$key_opts_str = "";
		$opt_means = "";
		$opt_esse = "";
		if($ioptkeys){
			foreach($ioptkeys as $key => $value){
				$optValues = NS_get_itemOptions( $value, $post->ID );
				if($optValues) {
					if($optValues['means'] < 2){
						$mes_opts_str .= "'" . sprintf(__("Chose the %s", 'usces'), $value) . "',";
					}else{
						$mes_opts_str .= "'" . sprintf(__("Input the %s", 'usces'), $value) . "',";
					}
					$key_opts_str .= "'{$value}',";
					$opt_means .= "'{$optValues['means']}',";
					$opt_esse .= "'{$optValues['essential']}',";
				}
			}
			$mes_opts_str = rtrim($mes_opts_str, ',');
			$key_opts_str = rtrim($key_opts_str, ',');
			$opt_means = rtrim($opt_means, ',');
			$opt_esse = rtrim($opt_esse, ',');
		}
?>
		<script type='text/javascript'>
		var opt_esse = new Array( <?php echo $opt_esse; ?> );
		var opt_means = new Array( <?php echo $opt_means; ?> );
		var mes_opts = new Array( <?php echo $mes_opts_str; ?> );
		var key_opts = new Array( <?php echo $key_opts_str; ?> );

		(function($) {
		uscesCart = {
			intoCart : function (post_id, sku) {
				var mes = '';
				$(':input[name^="opt"]').each(function(i, obj) {
					if($(this).val() == '') {
						name = $(this).attr("name").substring(3);
						mes += name+'を選択してください。'+"\n";
					}
				});
				for(i=0; i<key_opts.length; i++){
					var skuob = document.getElementById("iopt"+key_opts[i]);
					if( opt_esse[i] == '1' ){
						if( opt_means[i] < 2 && skuob.value == '#NONE#' ){
							mes += mes_opts[i]+"\n";
						}else if( opt_means[i] >= 2 && skuob.value == '' ){
							mes += mes_opts[i]+"\n";
						}
					}
				}
				if( mes != '' ){
					alert( mes );
					return false;
				}
				$(':input[name^="iopt"]').each(function(i, obj) {
					name = $(this).attr("name").substring(4);
					$('#sku_option_button').append('<input name="<?php echo $type; ?>_options['+name+']" type="hidden" value="'+$(this).val()+'">');
				});
			},
			
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
			
			settings: {
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
				<?php if( usces_the_itemCprice('return') > 0 ) : ?>
					<div class="field_name">通常価格</div>
					<div class="field_cprice">：<?php usces_crform( usces_the_firstCprice('return', $post), true, false ); ?></div>
				<?php endif; ?>
					<div class="field_name">販売価格</div>
					<div class="field_cprice price">：<?php NS_the_item_pricesCr($post); ?></div>
			</div>
			<div class="item_exp_3"><?php NS_the_item_explanation(3, $post); ?></div>
			<form action="#capture" method="post">
				<div class="skuform">
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
					<div id="sku_option_price"></div>
					<div id="sku_option_message"></div>
					<div id="sku_option_button"></div>
					<div class="error_message"><?php usces_singleitem_error_message($post->ID, usces_the_itemSku('return')); ?></div>
				</div><!-- end of skuform -->
				<?php echo apply_filters('single_item_single_sku_after_field', NULL); ?>
				<?php do_action('usces_action_single_item_inform'); ?>
			</form>
		</div>
		</div><!-- end of itemspage -->
		<div class="item_detail">
			<h3 class="titlebar">この商品の解説</h2>
			<div class="item_exp_4"><?php NS_the_item_explanation(4, $post); ?></div>
			<?php NS_the_sku_list($post); ?>
		</div>
<?php
	}

	function is_mochikomihin( $type ) {
		return (!empty($this->product[$type]['post_id']) and ($this->product[$type]['post_id'] < 0));
	}

	function get_top_mochikomihin( $type ) {
		$res = '';
		$idObj = get_category_by_slug($this->product[$type]['options']['genre']);
		$genre = $idObj->cat_name;

		switch( $type ){
		case 'head':
			$bore = ($this->product[$type]['options']['bore'] == "straight") ? 'ストレート・ボア' : 'ノーマル・ボア';
			$res  = '<div>お持込ヘッド</div>';
			$res .= '<div>種類:'.$genre.'</div>';
			$res .= '<div>タイプ:'.$bore.'</div>';
			$res .= '<div>メーカー:'.$this->product['head']['options']['maker'].'</div>';
			break;

		case 'shuft':
			$res  = '<div>お持込シャフト</div>';
			$res .= '<div>種類:'.$genre.'</div>';
			break;

		case 'grip':
			$res  = '<div>お持込グリップ</div>';
			$res .= '<div>種類:'.$genre.'</div>';
			break;
		}

		return $res;
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

function NS_mochikomihin( $action ){
?>
			<script type='text/javascript'>
			(function($) {
			uscesCart = {
				intoCart : function () {
					var mes = '';
					$(':input[name^="mochiopt"]').each(function(i, obj) {
						if($(this).val() == '') {
							label = $("label[for='"+$(this).attr("id")+"']").text();
							if($(this).attr("class") == "mochi_select") {
								mes += label+'を選択してください。'+"\n";
							} else if($(this).attr("class") == "mochi_text") {
								mes += label+'を入力してください。'+"\n";
							}
						}
					});
					if( mes != '' ){
						alert( mes );
						return false;
					}
				}
			};
			})(jQuery);
			</script>
<?php
	switch( $action ) {
		case 'default':
		case 'decide':
		case 'head_list':
?>
			<div class="thumbnail_box">
				<form action="#capture" method="post">
				<div class="item_name">お持込ヘッド</div>
				<div class="item_etc">
				<label for="mochi_genre" class="mochi_label">種類</label><select name="mochiopt[genre]" id="mochi_genre" class="mochi_select">
					<option value="">選択してください</option>
<?php
				$idObj = get_category_by_slug('sethead'); 
				$parent_id = $idObj->term_id;
				$cats = get_categories( array('child_of'=>$idObj->term_id, 'hide_empty'=>0) );
				foreach( $cats as $cat ){
					echo '<option value="' . esc_attr__($cat->category_nicename) . '" >' . esc_html__($cat->cat_name) . '</option>';
				}
?>
				</select>
				<label for="mochi_bore" class="mochi_label">タイプ</label><select name="mochiopt[bore]" id="mochi_bore" class="mochi_select">
					<option value="">選択してください</option>
					<option value="normal">ノーマル・ボア</option>
					<option value="straight">ストレート・ボア</option>
				</select>
				<label for="mochi_maker" class="mochi_label">メーカー</label><input name="mochiopt[maker]" type="text" id="mochi_maker" class="mochi_text"/>
				</div>
				<div class="select_button_box">
					<input name="shuft_list" type="submit" class="select_item_button" value="　" onclick="return uscesCart.intoCart()" />
					<input name="head_post_id" type="hidden" value="-1" />
					<input name="head_sku" type="hidden" value="-1" />
					<input name="head_price" type="hidden" value="0" />
				</div>
				</form>
			</div>
<?php
			break;
			
		case 'shuft_list':
?>
			<div class="thumbnail_box">
				<form action="#capture" method="post">
				<div class="item_name">お持込シャフト</div>
				<div class="item_etc">
				<label for="mochi_genre" class="mochi_label">種類</label><select name="mochiopt[genre]" id="mochi_genre" class="mochi_select">
					<option value="">選択してください</option>
<?php
				$idObj = get_category_by_slug('setshuft'); 
				$parent_id = $idObj->term_id;
				$cats = get_categories( array('child_of'=>$idObj->term_id, 'hide_empty'=>0) );
				foreach( $cats as $cat ){
					echo '<option value="' . esc_attr__($cat->category_nicename) . '" >' . esc_html__($cat->cat_name) . '</option>';
				}
?>
				</select>
				</div>
				<div class="select_button_box">
					<input name="grip_list" type="submit" class="select_item_button" value="　" />
					<input name="shuft_post_id" type="hidden" value="-2" />
					<input name="shuft_sku" type="hidden" value="-2" />
					<input name="shuft_price" type="hidden" value="0" />
				</div>
				</form>
			</div>
<?php
			break;
			
		case 'grip_list':
?>
<!--			<div class="thumbnail_box">
				<form action="#capture" method="post">
					<div class="item_name">お持込グリップ</div>
				<div class="item_etc">
				<label for="mochi_genre" class="mochi_label">種類</label><select name="mochiopt[genre]" id="mochi_genre" class="mochi_select">
					<option value="">選択してください</option>
<?php
				$idObj = get_category_by_slug('setgrip'); 
				$parent_id = $idObj->term_id;
				$cats = get_categories( array('child_of'=>$idObj->term_id, 'hide_empty'=>0) );
				foreach( $cats as $cat ){
					echo '<option value="' . esc_attr__($cat->category_nicename) . '" >' . esc_html__($cat->cat_name) . '</option>';
				}
?>
				</select>
				</div>
				<div class="select_button_box">
					<input name="decide" type="submit" class="select_item_button" value="　" />
					<input name="grip_post_id" type="hidden" value="-3" />
					<input name="grip_sku" type="hidden" value="-3" />
					<input name="grip_price" type="hidden" value="0" />
				</div>
				</form>
			</div>-->
<?php
			break;
			
		default:
			return;
	}
}
?>