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
<div class="whitebox">

	<div class="header_explanation">
	<?php do_action('usces_action_newpass_page_header'); ?>
	</div>

	<div class="error_message"><?php usces_error_message(); ?></div>
	<div class="loginbox">
	<form name="loginform" id="loginform" action="<?php usces_url('member'); ?>" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
	<p>
	<label><?php _e('e-mail adress', 'usces'); ?><br />
	<input type="text" name="loginmail" id="loginmail" class="loginmail" value="<?php echo esc_attr($this->current_member['email']); ?>" size="20" /></label>
	</p>
	<p class="submit">
	<input type="submit" name="lostpassword" id="member_login" value="<?php _e('Obtain new password', 'usces'); ?>" />
	</p>
	<?php do_action('usces_action_newpass_page_inform'); ?>
	</form>
	<div><?php _e('Chenge th epassword according to the e-mail.', 'usces'); ?></div>
	<p id="nav">
	<?php if ( ! usces_is_login() ) : ?>
	<a href="<?php usces_url('login'); ?>" title="<?php _e('Log-in', 'usces'); ?>"><?php _e('Log-in', 'usces'); ?></a>
	<?php endif; ?>
	</p>
	</div><!-- end of loginbox -->

	<div class="footer_explanation">
	<?php do_action('usces_action_newpass_page_footer'); ?>
	</div>
	
</div><!-- end of whitebox -->
</div><!-- end of memberpages -->
<script type="text/javascript">
try{document.getElementById('loginmail').focus();}catch(e){}
</script>

</div><!-- end of entry -->
</div><!-- end of post -->
<?php else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>
</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_footer(); ?>
