<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
get_header();
?>
<div id="content" class="two-column">
<div class="catbox">

<?php if (have_posts()) : the_post(); ?>

<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
<h1 class="item_page_title"><?php the_title(); ?></h1>
<div class="contbody storycontent">

<?php usces_remove_filter(); ?>
<?php usces_the_item(); ?>
<?php usces_have_skus(); ?>

<div id="itempage" class="border_arround clearfix">
	<div class="item_header clearfix">
		<div class="item_info alignleft">
			<div class="item_maker"><?php NS_teh_item_maker(); ?></div>
			<h2 class="item_name"><?php usces_the_itemName(); ?></h2>
		</div>
		<div class="item_addition alignright">
			<div class="sale_tag"><?php NS_the_salse_tag(); ?></div>
			<div class="itemstar">★★★★☆</div>
		</div>
	</div>
	<div class="item_exp_1 clear">
		<?php the_content(); ?>
	</div>
	<div class="itemdetail_left">
		<div class="itemimg border_arround">
			<a href="<?php usces_the_itemImageURL(0); ?>" <?php echo apply_filters('usces_itemimg_anchor_rel', NULL); ?>><?php usces_the_itemImage(0, 300, 300, $post); ?></a>
		</div>
	<?php $imageid = usces_get_itemSubImageNums(); ?>
	<?php if($imageid): $count = 1;?>
		<div class="itemsubimg">
		<ul class="clearfix">
		<?php foreach ( $imageid as $id ) : ?>
			<li class="subimg_<?php echo $id; ?>"><a href="<?php usces_the_itemImageURL($id); ?>" <?php echo apply_filters('usces_itemimg_anchor_rel', NULL); ?>><?php usces_the_itemImage($id, 55, 55, $post); ?></a></li>
			<?php if ($count >= 5) break;?>
			<?php $count += 1; ?>
		<?php endforeach; ?>
		</ul>
		</div><!-- end of itemsubimg -->
	<?php endif; ?>

		<div class="item_country textright">
			生産国：日本
		</div>
		<div class="item_exp_2">
			<?php NS_the_item_explanation( 2 ); ?>
		</div>
	</div>
	<div class="itemdetail_right">
		<div class="tag_field">
		<?php NS_the_fantastic4(); ?>
		</div>
		<div class="item_field clear">
			<div class="field_name">商品コード</div>
			<div class="field_code">：<?php usces_the_itemCode(); ?></div>

			<div class="field_name">商品名</div>
			<div class="field_itemname">：<?php usces_the_itemName(); ?></div>
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
		<div class="item_exp_3"><?php NS_the_item_explanation( 3 ); ?></div>

		<form action="<?php echo USCES_CART_URL; ?>" method="post">
			<div class="skuform">
			<?php //_e('Please appoint an option.', 'usces'); ?>
			<?php if (usces_is_options()) : ?>
				<div class="item_option">
					<table id="option_list">
				<?php while (usces_have_options()) : ?>
					<tr>
						<th><?php usces_the_itemOptName(); ?></th>
						<td><?php usces_the_itemOption(usces_getItemOptName(),''); ?></td>
					</tr>
				<?php endwhile; ?>
					</table>
				</div>
			<?php endif; ?>
			<?php if( $item_custom = usces_get_item_custom( $post->ID, 'list', 'return' ) ) : ?>
				<div class="field"><?php echo $item_custom; ?></div>
			<?php endif; ?>
				<div class="send_info">発送日目安：<?php usces_the_shipment_aim(); ?></div>
			<?php if( !usces_have_zaiko() ) : ?>
				<div class="zaiko_status"><?php echo apply_filters('usces_filters_single_sku_zaiko_message', __('Sold Out', 'usces')); ?></div>
			<?php else : ?>
				<div style="margin:10px 0"><?php _e('Quantity', 'usces'); ?><?php usces_the_itemQuant(); ?><?php usces_the_itemSkuUnit(); ?></div>
				<div><?php ntstg_the_itemSkuButton(__('Add to Shopping Cart', 'usces'), 0); ?></div>
				<div class="error_message"><?php usces_singleitem_error_message($post->ID, usces_the_itemSku('return')); ?></div>
			<?php endif; ?>
			</div><!-- end of skuform -->
			<?php echo apply_filters('single_item_single_sku_after_field', NULL); ?>
			<?php do_action('usces_action_single_item_inform'); ?>
		</form>

<?php elseif(usces_sku_num() > 1) : ?>
<!--some SKU-->
		<?php if( usces_the_itemCprice('return') > 0 ) : ?>
			<div class="field_name">通常価格</div>
			<div class="field_cprice">：<?php usces_crform( usces_the_firstCprice('return'), true, false ); ?></div>
		<?php endif; ?>
			<div class="field_name">販売価格</div>
			<div class="field_cprice price">：<?php NS_the_item_pricesCr(); ?></div>
	</div>
	<div class="item_exp_3"><?php NS_the_item_explanation( 3 ); ?></div>
	<form action="<?php echo USCES_CART_URL; ?>" method="post">
	<div class="skuform">
		<?php if( usces_is_options() ): ?>
			<div class="item_option">
				<table id="option_list">
					<?php while (usces_have_options()) : ?>
					<tr>
						<th><?php usces_the_itemOptName(); ?></th>
						<td><?php usces_the_itemOption(usces_getItemOptName(),''); ?></td>
					</tr>
					<?php endwhile; ?>
				</table>
			</div>
		<?php endif; ?>
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
				<td colspan="2" class="skudisp subborder"><?php usces_the_itemSkuDisp(); ?></td>
				<td colspan="1" class="subborder price"><span class="price"><?php usces_the_itemPriceCr(); ?></span></td>
			</tr>
			<tr>
				<td class="zaiko"><?php usces_the_itemZaiko(); ?></td>
				<td class="quant"><?php usces_the_itemQuant(); ?><?php usces_the_itemSkuUnit(); ?></td>
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
	<?php echo apply_filters('single_item_multi_sku_after_field', NULL); ?>
	<?php do_action('usces_action_single_item_inform'); ?>
	</form>
<?php endif; ?>
</div>
	<div class="item_caution clear">
	はじめにお読みください！
	<ul>
		<li>注意事項注意事項注意事項注意事項注意事項注意事項注意事項注意事項注意事項注意事項</li>
		<li>注意事項注意事項注意事項注意事項注意事項注意事項注意事項注意事項注意事項注意事項</li>
		<li>注意事項注意事項注意事項注意事項注意事項注意事項注意事項注意事項注意事項注意事項</li>
		<li>注意事項注意事項注意事項注意事項注意事項注意事項注意事項注意事項注意事項注意事項</li>
	</ul>
	</div>
</div><!-- end of itemspage -->

<div class="item_detail">
	<h3 class="titlebar">この商品の解説</h2>
	<div class="item_exp_4"><?php NS_the_item_explanation( 4 ); ?></div>
	<table class="spec_list">
		<tr>
			<th>品番</th>
			<th>項目1</th>
			<th>項目2</th>
			<th>項目3</th>
			<th>項目4</th>
			<th>項目5</th>
		</tr>
		<tr class="odd">
			<td>111</td>
			<td>222</td>
			<td>333</td>
			<td>444</td>
			<td>555</td>
			<td>666</td>
		</tr>
		<tr class="even">
			<td>111</td>
			<td>222</td>
			<td>333</td>
			<td>444</td>
			<td>555</td>
			<td>666</td>
		</tr>
		<tr class="odd">
			<td>111</td>
			<td>222</td>
			<td>333</td>
			<td>444</td>
			<td>555</td>
			<td>666</td>
		</tr>
		<tr class="even">
			<td>111</td>
			<td>222</td>
			<td>333</td>
			<td>444</td>
			<td>555</td>
			<td>666</td>
		</tr>
		<tr class="odd">
			<td>111</td>
			<td>222</td>
			<td>333</td>
			<td>444</td>
			<td>555</td>
			<td>666</td>
		</tr>
	</table>
</div>

<?php ntstg_assistance_item( $post->ID, 'おすすめ商品'); ?>


</div><!-- end of storycontent -->
</div>

<?php else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>

</div><!-- end of catbox -->
</div><!-- end of content -->

<?php/* get_sidebar( 'other' ); */?>

<?php get_footer(); ?>
