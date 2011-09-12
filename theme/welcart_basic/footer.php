<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Pop Cloud Blue Theme
 */
?>
<!-- begin footer -->

</div><!-- end of main -->

<div id="footer">
	<div class="to_top"><a href="#top">&uarr;</a></div>
<?php if(function_exists('wp_nav_menu')): ?>
	<?php wp_nav_menu(array('menu_class' => 'footernavi clearfix', 'theme_location' => 'footer')); ?>
<?php else: ?>
	<ul class="footernavi clearfix">
		<li><a href="<?php bloginfo('url'); ?>/"><?php _e('top page','usces') ?></a></li>
		<?php wp_list_pages('title_li=&exclude=' . USCES_MEMBER_NUMBER . ',' . USCES_CART_NUMBER ); ?>
	</ul>
<?php endif; ?>
	<p class="copyright"><?php usces_copyright(); ?></p>
	<p class="credit"><cite>Theme Designed by USConsort</cite></p>
</div><!-- end of footer -->

</div><!-- end of wrapping -->

<?php wp_footer(); ?>
</body>
</html>