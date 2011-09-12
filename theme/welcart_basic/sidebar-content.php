<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Pop Cloud Blue Theme
 */
?>

<div id="content-widget-area" role="complementary" class="clearfix">

<div id="hu-ul" class="clearfix">
<div class="retsu">
<ul class="para">
<?php 	if ( ! dynamic_sidebar( 'first-content-widget-area' ) ) : ?>
	<li id="widget_welcart_bestseller" class="widget">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/bestseller.png" alt="<?php _e('best seller','usces') ?>" /><?php _e('best seller','usces') ?></div>
		<ul class="welcart_widget_body"> 
		<?php usces_list_bestseller(10); ?>
		</ul> 
	</li>
<?php endif; ?>
</ul>
</div>
<div class="retsu">
<ul class="para">
<?php 	if ( ! dynamic_sidebar( 'second-content-widget-area' ) ) : ?>
	<li id="widget_welcart_post" class="widget">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/post.png" alt="<?php _e('Information','usces') ?>" /><?php _e('Information','usces') ?></div>
		<ul class="welcart_widget_body">
		<?php usces_list_post('information',3); ?>
		</ul>
	</li>
<?php endif; ?>
</ul>
</div>
<div class="retsu">
<ul class="para">
<?php 	if ( ! dynamic_sidebar( 'third-content-widget-area' ) ) : ?>
	<li id="widget_welcart_page" class="widget">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/page.png" alt="<?php _e('Page','usces') ?>" /><?php _e('Page','usces') ?></div>
   		<ul class="welcart_widget_body"> 
		<?php wp_list_pages('title_li=') ; ?>
		</ul> 
	</li>
<?php endif; ?>
</ul>
</div>
</div>

</div>
