<?php
/**
 * <meta content="charset=UTF-8">
 * @package WordPress
 * @subpackage Welcart Default Theme
 */
?>
<!-- begin footer -->

</div><!-- end of main -->

<div id="footer">
<?php if(function_exists('wp_nav_menu')): ?>
	<?php wp_nav_menu(array('$format' => '','menu_class' => 'footernavi clearfix')); ?>
<?php else: ?>
	<ul class="footernavi clearfix">
		<li><a href="<?php bloginfo('url'); ?>/"><?php _e('top page','usces') ?></a></li>
		<?php wp_list_pages('title_li=&exclude=' . USCES_MEMBER_NUMBER . ',' . USCES_CART_NUMBER ); ?>
	</ul>
<?php endif; ?>
	<p class="copyright"><?php usces_copyright(); ?></p>
	<p class="credit"><cite>Powered by <a href="http://www.welcart.com/" target="_blank">Welcart</a></cite></p>
</div><!-- end of footer -->

</div><!-- end of wrap -->

<?php wp_footer(); ?>
</body>
</html>