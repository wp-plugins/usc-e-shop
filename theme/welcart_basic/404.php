<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Pop Cloud Blue Theme
 */

get_header(); ?>

<div id="content">
<div class="catbox">

	<div id="post-0" class="post error404 not-found">
		<h1 class="entry-title"><?php _e( 'Not Found', 'uscestheme' ); ?></h1>
		<div class="entry-content">
			<p><?php _e( 'Apologies, but the page you requested could not be found. Perhaps searching will help.', 'uscestheme' ); ?></p>
			<?php get_search_form(); ?>
		</div><!-- .entry-content -->
	</div><!-- #post-0 -->

</div><!-- end of catbox -->
</div><!-- #content -->
<script type="text/javascript">
	// focus on search field after it has loaded
	document.getElementById('s') && document.getElementById('s').focus();
</script>

<?php get_sidebar(); ?>

<?php get_footer(); ?>