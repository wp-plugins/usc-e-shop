<?php
/*
Template Name: Inquiry
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Pop Cloud Blue Theme
*/
get_header();
?>

<div id="content">
<div class="catbox">
	<div class="post">
	<h1><?php _e('Visit/Contact Us','usces') ?></h1>
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div class="inqbox">
		<?php the_content(); ?>
		<?php usces_the_inquiry_form(); ?>
	</div>
	<?php endwhile; endif; ?>
    </div>
	<?php edit_post_link(__('Edit', 'uscestheme'), '<p>', '</p>'); ?>
</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
