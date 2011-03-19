<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
get_header();

get_sidebar( 'other' );
?>
<div id="content" class="two-column">
<div class="catbox">

<?php if (have_posts()) : the_post(); ?>

<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
<h1 class="item_page_title"><?php the_title(); ?></h1>
<div class="storycontent">

<?php usces_remove_filter(); ?>
<?php usces_the_item(); ?>

<div id="itempage">
	<div class="itemimg">
	<a href="<?php usces_the_itemImageURL(0); ?>" <?php echo apply_filters('usces_itemimg_anchor_rel', NULL); ?>><?php usces_the_itemImage(0, 300, 300, $post); ?></a>
	</div>
	
<?php if(usces_sku_num() === 1) : usces_have_skus(); ?>
<!--1SKU-->
	<h2 class="item_name"><?php usces_the_itemName(); ?>&nbsp;(<?php usces_the_itemCode(); ?>)</h2>
	<div class="exp">
		<div class="field">
		<?php if( usces_the_itemCprice('return') > 0 ) : ?>
			<div class="field_name"><?php _e('List price', 'usces'); ?><?php usces_guid_tax(); ?></div>
			<div class="field_cprice"><?php usces_the_itemCpriceCr(); ?></div>
		<?php endif; ?>
			<div class="field_name"><?php _e('selling price', 'usces'); ?><?php usces_guid_tax(); ?></div>
			<div class="field_price"><?php usces_the_itemPriceCr(); ?></div>
		</div>
		<div class="field"><?php _e('stock status', 'usces'); ?> : <?php usces_the_itemZaiko(); ?></div>
		<?php if( $item_custom = usces_get_item_custom( $post->ID, 'list', 'return' ) ) : ?>
		<div class="field"><?php echo $item_custom; ?></div>
		<?php endif; ?>
		
		<?php the_content(); ?>
	</div><!-- end of exp -->

	<form action="<?php echo USCES_CART_URL; ?>" method="post">
	<?php usces_the_itemGpExp(); ?>
	<div class="skuform" align="right">
	<?php if (usces_is_options()) : ?>
		<table class='item_option'>
			<caption><?php _e('Please appoint an option.', 'usces'); ?></caption>
		<?php while (usces_have_options()) : ?>
			<tr><th><?php usces_the_itemOptName(); ?></th><td><?php usces_the_itemOption(usces_getItemOptName(),''); ?></td></tr>
		<?php endwhile; ?>
		</table>
	<?php endif; ?>
	<?php if( !usces_have_zaiko() ) : ?>
		<div class="zaiko_status"><?php echo apply_filters('usces_filters_single_sku_zaiko_message', __('Sold Out', 'usces')); ?></div>
	<?php else : ?>
		<div style="margin-top:10px"><?php _e('Quantity', 'usces'); ?><?php usces_the_itemQuant(); ?><?php usces_the_itemSkuUnit(); ?><?php usces_the_itemSkuButton(__('Add to Shopping Cart', 'usces'), 0); ?></div>
		<div class="error_message"><?php usces_singleitem_error_message($post->ID, usces_the_itemSku('return')); ?></div>
	<?php endif; ?>
	</div><!-- end of skuform -->
	<?php echo apply_filters('single_item_single_sku_after_field', NULL); ?>
	<?php do_action('usces_action_single_item_inform'); ?>
	</form>
	
<?php elseif(usces_sku_num() > 1) : usces_have_skus(); ?>
<!--some SKU-->
	<h2 class="item_name"><?php usces_the_itemName(); ?>&nbsp;(<?php usces_the_itemCode(); ?>)</h2>
	<div class="exp">
		<?php the_content(); ?>
		<?php if( $item_custom = usces_get_item_custom( $post->ID, 'list', 'return' ) ) : ?>
		<div class="field">
			<?php echo $item_custom; ?>
		</div>
		<?php endif; ?>
	</div><!-- end of exp -->

	<form action="<?php echo USCES_CART_URL; ?>" method="post">
	<div class="skuform">
		<table class="skumulti">
			<thead>
			<tr>
				<th rowspan="2" class="thborder"><?php _e('order number', 'usces'); ?></th>
				<th colspan="2"><?php _e('Title', 'usces'); ?></th>
	<?php if( usces_the_itemCprice('return') > 0 ) : ?>
				<th colspan="2">(<?php _e('List price', 'usces'); ?>)<?php _e('selling price', 'usces'); ?><?php usces_guid_tax(); ?></th>
	<?php else : ?>
				<th colspan="2"><?php _e('selling price', 'usces'); ?><?php usces_guid_tax(); ?></th>
	<?php endif; ?>
			</tr>
			<tr>
				<th class="thborder"><?php _e('stock status', 'usces'); ?></th>
				<th class="thborder"><?php _e('Quantity', 'usces'); ?></th>
				<th class="thborder"><?php _e('unit', 'usces'); ?></th>
				<th class="thborder">&nbsp;</th>
			</tr>
			</thead>
			<tbody>
	<?php do { ?>
			<tr>
				<td rowspan="2"><?php usces_the_itemSku(); ?></td>
				<td colspan="2" class="skudisp subborder"><?php usces_the_itemSkuDisp(); ?>
		<?php if (usces_is_options()) : ?>
					<table class='item_option'>
					<caption><?php _e('Please appoint an option.', 'usces'); ?></caption>
			<?php while (usces_have_options()) : ?>
						<tr>
							<th><?php usces_the_itemOptName(); ?></th>
							<td><?php usces_the_itemOption(usces_getItemOptName(),''); ?></td>
						</tr>
			<?php endwhile; ?>
					</table>
		<?php endif; ?>
				</td>
				<td colspan="2" class="subborder price">
		<?php if( usces_the_itemCprice('return') > 0 ) : ?>
				<span class="cprice">(<?php usces_the_itemCpriceCr(); ?>)</span>
		<?php endif; ?>			
				<span class="price"><?php usces_the_itemPriceCr(); ?></span>
				<br /><?php usces_the_itemGpExp(); ?>
				</td>
			</tr>
			<tr>
				<td class="zaiko"><?php usces_the_itemZaiko(); ?></td>
				<td class="quant"><?php usces_the_itemQuant(); ?></td>
				<td class="unit"><?php usces_the_itemSkuUnit(); ?></td>
			<?php if( !usces_have_zaiko() ) : ?>
				<td class="button"><?php echo apply_filters('usces_filters_single_sku_zaiko_message', __('Sold Out', 'usces')); ?></td>
			<?php else : ?>
				<td class="button"><?php usces_the_itemSkuButton(__('Add to Shopping Cart', 'usces'), 0); ?></td>
			<?php endif; ?>
			</tr>
			<tr>
				<td colspan="5" class="error_message"><?php usces_singleitem_error_message($post->ID, usces_the_itemSku('return')); ?></td>
			</tr>

	<?php } while (usces_have_skus()); ?>
			</tbody>
		</table>
	</div><!-- end of skuform -->
	<?php echo apply_filters('single_item_multi_sku_after_field', NULL); ?>
	<?php do_action('usces_action_single_item_inform'); ?>
	</form>
<?php endif; ?>
	
	<div class="itemsubimg">
<?php $imageid = usces_get_itemSubImageNums(); ?>
<?php foreach ( $imageid as $id ) : ?>
		<a href="<?php usces_the_itemImageURL($id); ?>" <?php echo apply_filters('usces_itemimg_anchor_rel', NULL); ?>><?php usces_the_itemImage($id, 135, 135, $post); ?></a>
<?php endforeach; ?>
	</div><!-- end of itemsubimg -->

<?php usces_assistance_item( $post->ID ); ?>
</div><!-- end of itemspage -->
</div><!-- end of storycontent -->
</div>

<?php else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>

</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_footer(); ?>
