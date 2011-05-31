<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
get_header();

?>
<div id="content">
<div class="contbody">
	<div id="newsarea" class="contentarea">
		<h2 class="title"><?php _e('Information','usces') ?></h2>
		<div class="infobox">
		<ul>
		<?php ntstg_list_post(__('Uncategorized'),10); ?>
		</ul>
		</div>
	</div><!-- end of newsarea -->

	<div id="reshaftarea" class="contentarea">
		<?php ntstg_reshaft_slideshow(); ?>
        <img src="<?php bloginfo('stylesheet_directory'); ?>/images/home/txt_reshaft.png" alt="リシャフト" width="510" height="50" />
	</div>

	<div id="newitemarea" class="contentarea">
		<h2 class="titlebar"><?php _e('New items','usces') ?></h2>
		<div class="clearfix">
			<?php $new_ob = new wp_query(array('category_name'=>'itemnew', 'posts_per_page'=>9, 'post_status'=>'publish')); ?>
			<?php $itemcount = 1; ?>
			<?php if ($new_ob->have_posts()) : while ($new_ob->have_posts()) : $new_ob->the_post(); usces_the_item(); ?>
			<div class="thumbnail_box<?php if($itemcount % 3 == 0) echo " right";?>">
				<div class="thumtitle"><a href="<?php the_permalink() ?>" rel="bookmark"><?php usces_the_itemName(); ?></a></div>
				<div class="thumimg"><a href="<?php the_permalink() ?>"><?php usces_the_itemImage($number = 0, $width = 130, $height = 130 ); ?></a></div>
				<?php $content_summary = str_replace("\n", "", strip_tags($post->post_content)); ?>
				<div class="thumcomment"><?php echo (mb_strlen($content_summary) > 27) ? mb_substr($content_summary, 0, 25) . "..." : $content_summary; ?></div>
			<?php if (usces_is_skus()) : ?>
				<div class="price"><a href="<?php the_permalink() ?>"><?php _e('$', 'usces'); ?><?php usces_the_firstPrice(); ?><?php usces_guid_tax(); ?></a></div>
			<?php endif; ?>
			</div>
			<?php $itemcount += 1; ?>
			<?php endwhile; else: ?>
			<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
			<?php endif; wp_reset_query(); ?>
		</div>
	</div>

	<div id="recomendarea" class="contentarea">
		<h2 class="titlebar"><?php _e('Items recommended','usces') ?></h2>
		<div class="clearfix">
			<?php $reco_ob = new wp_query(array('category_name'=>'itemreco', 'posts_per_page'=>9, 'post_status'=>'publish')); ?>
			<?php $itemcount = 1; ?>
			<?php if ($reco_ob->have_posts()) : while ($reco_ob->have_posts()) : $reco_ob->the_post(); usces_the_item(); ?>
			<div class="thumbnail_box<?php if($itemcount % 3 == 0) echo " right";?>">
				<div class="thumtitle"><a href="<?php the_permalink() ?>" rel="bookmark"><?php usces_the_itemName(); ?></a></div>
				<div class="thumimg"><a href="<?php the_permalink() ?>"><?php usces_the_itemImage($number = 0, $width = 130, $height = 130 ); ?></a></div>
				<?php $content_summary = str_replace("\n", "", strip_tags($post->post_content)); ?>
				<div class="thumcomment"><?php echo (mb_strlen($content_summary) > 27) ? mb_substr($content_summary, 0, 25) . "..." : $content_summary; ?></div>
			<?php if (usces_is_skus()) : ?>
				<div class="price"><a href="<?php the_permalink() ?>"><?php _e('$', 'usces'); ?><?php usces_the_firstPrice(); ?><?php usces_guid_tax(); ?></a></div>
			<?php endif; ?>
			</div>
			<?php $itemcount += 1; ?>
			<?php endwhile; else: ?>
			<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
			<?php endif; wp_reset_query(); ?>
		</div>
	</div>
</div><!-- end of contbody -->
</div><!-- end of content -->
<?php get_footer(); ?>
