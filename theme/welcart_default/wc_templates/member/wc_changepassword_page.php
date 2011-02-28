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
	<?php echo apply_filters('usces_filter_changepass_page_header', NULL); ?>
	</div>

	<div class="error_message"><?php usces_error_message(); ?></div>
	<div class="loginbox">
	<form name="loginform" id="loginform" action="<?php usces_url('member'); ?>" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
	<p>
		<label><?php _e('password', 'usces'); ?><br />
		<input type="password" name="loginpass1" id="loginpass1" class="loginpass" value="" size="20" /></label>
	</p>
	<p>
		<label><?php _e('Password (confirm)', 'usces'); ?><br />
		<input type="password" name="loginpass2" id="loginpass2" class="loginpass" value="" size="20" /></label>
	</p>
	<p class="submit">
		<input type="submit" name="changepassword" id="member_login" value="<?php _e('Register', 'usces'); ?>" />
	</p>
	</form>
	</div>
	<div class="footer_explanation">
	<?php echo apply_filters('usces_filter_changepass_page_footer', NULL); ?>
	</div>

</div><!-- end of memberpages -->
<script type="text/javascript">
try{document.getElementById('loginpass1').focus();}catch(e){}
</script>

</div><!-- end of entry -->
</div><!-- end of post -->
<?php else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>
</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_footer(); ?>
