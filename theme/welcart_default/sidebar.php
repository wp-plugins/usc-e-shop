<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
global $usces, $post;//kanpari
?>
<!-- begin left sidebar -->
<div id="leftbar" class="sidebar">
<ul>
	<li id="welcart_login-3" class="widget widget_welcart_login">
		<div class="widget_title"><img src="<?php bloginfo('template_url'); ?>/images/login.png" alt="<?php _e('Log-in','usces') ?>" /><?php _e('Log-in','usces') ?></div>
		<ul class="welcart_login_body welcart_widget_body"><li>
			<div class="loginbox">
			<?php if( !usces_is_login() ) : ?>
				<form name="loginwidget" id="loginform" action="<?php echo USCES_MEMBER_URL; ?>/?ret_page=<?php echo $post->ID; ?>" method="post">
				<p>
				<label><?php _e('e-mail adress','usces') ?><br />
				<input type="text" name="loginmail" id="loginmail" class="loginmail" value="<?php echo usces_remembername('return'); ?>" size="20" tabindex="10" /></label><br />
				<label><?php _e('password','usces') ?><br />
				<input type="password" name="loginpass" id="loginpass" class="loginpass" value="<?php echo usces_rememberpass('return'); ?>" size="20" tabindex="20" /></label><br />
				<label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90"<?php echo usces_remembercheck('return'); ?> /> <?php _e('memorize login information','usces') ?></label></p>
				<p class="submit">
				<input type="submit" name="member_login" id="member_login" value="<?php _e('Log-in','usces') ?>" tabindex="100" />
				</p>
				</form>
				<a href="<?php echo USCES_LOSTMEMBERPASSWORD_URL; ?>" title="<?php _e('Pssword Lost and Found','usces') ?>"><?php _e('Did you forget your password?','usces') ?></a><br />
				<a href="<?php echo USCES_NEWMEMBER_URL; ?>" title="<?php _e('New enrollment for membership.','usces') ?>"><?php _e('New enrollment for membership.','usces') ?></a>
			<?php else: ?>
				<?php printf(__('Mr/Mrs %s', 'usces'), usces_the_member_name()); ?><br />
				<?php $point = ( empty($_SESSION['usces_member']['point']) ) ? 0 : $_SESSION['usces_member']['point']; ?>
				<div><label><?php _e('The current point','usces') ?></label>&nbsp;<?php usces_crform($point, false, true); ?></div>
				<?php echo usces_loginout(); ?><br />
				<a href="<?php echo USCES_MEMBER_URL; ?>"><?php _e('Membership information','usces') ?></a>
			<?php endif; ?>
			</div>
		</li>
		</ul>
	</li>
<?php 	/* Widgetized sidebar, if you have the plugin installed. */
	if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
<?php endif; ?>
</ul>
</div>
<!-- end left sidebar -->

<!-- begin right sidebar -->
<div id="rightbar" class="sidebar">
<ul>
<?php 	/* Widgetized sidebar, if you have the plugin installed. */
	if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar(2) ) : ?>
<?php endif; ?>
</ul>

</div>
<!-- end right sidebar -->
