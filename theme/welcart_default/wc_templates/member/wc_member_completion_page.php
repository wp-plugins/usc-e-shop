<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
get_header();
?>

<div id="content">
<div class="catbox">

<?php if (have_posts()) : usces_remove_filter(); ?>

<div class="post" id="wc_<?php usces_page_name(); ?>">

<h1><?php _e('Change password', 'usces'); ?></h1>
<div class="entry">
		
<div id="memberpages">

	<div class="header_explanation">
	<?php echo apply_filters('usces_filter_membercompletion_page_header', NULL); ?>
	</div>

	<?php $member_compmode = usces_page_name('return'); ?>
	<?php if ( 'newcompletion' == $member_compmode ) : ?>
	<p><?php _e('Thank you in new membership.', 'usces'); ?></p>
	
	<?php elseif ( 'editcompletion' == $member_compmode ) : ?>
	<p><?php _e('Membership information has been updated.', 'usces'); ?></p>
	
	<?php elseif ( 'lostcompletion' == $member_compmode ) : ?>
	<p><?php _e('I transmitted an email.', 'usces'); </p>
	<p><?php _e('Chenge th epassword according to the e-mail.', 'usces'); ?></p>
	
	<?php elseif ( 'changepasscompletion' == $member_compmode ) : ?>
	<p><?php _e('Password has been changed.', 'usces'); ?></p>
	
	<?php endif; ?>


	<div class="footer_explanation">
	<?php echo apply_filters('usces_filter_membercompletion_page_footer', NULL); ?>
	</div>

	<p><a href="<?php usces_url('member'); ?>"><?php _e('to vist membership information page', 'usces'); ?></a></p>
	<form action="<?php bloginfo('home'); ?>" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
	<div class="send"><input name="top" type="submit" value="<?php _e('Back to the top page.', 'usces'); ?>" /></div>
	</form>
	</div>

</div><!-- end of memberpages -->

</div><!-- end of entry -->
</div><!-- end of post -->
<?php else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>
</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_footer(); ?>