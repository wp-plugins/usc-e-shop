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
<?php //usces_p($NSSP->get_top_focus()); ?>
</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_footer(); ?>
