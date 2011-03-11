<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
get_header();

get_sidebar( 'onlycart' );
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
	<?php do_action('usces_action_login_page_header'); ?>
	</div>

	<div class="error_message"><?php usces_error_message(); ?></div>
	<div class="loginbox">
	<form name="loginform" id="loginform" action="<?php echo apply_filters('usces_filter_login_form_action', USCES_MEMBER_URL); ?>" method="post">
	<p>
	<label><?php _e('e-mail adress', 'usces'); ?><br />
	<input type="text" name="loginmail" id="loginmail" class="loginmail" value="<?php echo esc_attr(usces_remembername('return')); ?>" size="20" /></label>
	</p>
	<p>
	<label><?php _e('password', 'usces'); ?><br />
	<input type="password" name="loginpass" id="loginpass" class="loginpass" size="20" /></label>
	</p>
	<p class="forgetmenot">
	<label><input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e('memorize login information', 'usces'); ?></label>
	</p>
	<p class="submit">
	<?php usces_login_button(); ?>
	</p>
	<?php do_action('usces_action_login_page_inform'); ?>
	</form>
	<p id="nav">
	<a href="<?php usces_url('lostmemberpassword'); ?>" title="<?php _e('Did you forget your password?', 'usces'); ?>"><?php _e('Did you forget your password?', 'usces'); ?></a>
	</p>
	<p id="nav">
	<?php if ( ! usces_is_login() ) : ?>
	<a href="<?php usces_url('newmember') . apply_filters('usces_filter_newmember_urlquery', NULL); ?>" title="<?php _e('New enrollment for membership.', 'usces'); ?>"><?php _e('New enrollment for membership.', 'usces'); ?></a>
	<?php endif; ?>
	</p>
	</div>

	<div class="footer_explanation">
	<?php do_action('usces_action_login_page_footer'); ?>
	</div>

</div><!-- end of whitebox -->
</div><!-- end of memberpages -->

<script type="text/javascript">
<?php if ( $usces_is_login ) : ?>
	setTimeout( function(){ try{
	d = document.getElementById('loginpass');
	d.value = '';
	d.focus();
	} catch(e){}
	}, 200);
<?php else : ?>
	try{document.getElementById('loginmail').focus();}catch(e){}
<?php endif; ?>
</script>

</div><!-- end of entry -->
</div><!-- end of post -->
<?php else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>
</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_footer(); ?>
