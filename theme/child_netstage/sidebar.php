<?php
/**
 * <meta content="charset=UTF-8">
 * @package Netstage
 * @subpackage Netstage Theme
 */
global $usces;
?>
<!-- begin left sidebar -->
<div id="leftbar" class="sidebar">
<ul>
<?php 	/* Widgetized sidebar, if you have the plugin installed. */
	if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
	<?php if(usces_is_membersystem_state()): ?>
	<li id="welcart_login" class="widget widget_welcart_login">
		<div class="widget_title"><?php _e('Log-in','usces') ?></div>
		<ul class="welcart_login_body welcart_widget_body">
			<li>
			<div class="loginbox">
			<?php if ( ! usces_is_login() ) { ?>
				<form name="loginform" id="loginform" action="<?php echo apply_filters('usces_filter_login_form_action', USCES_MEMBER_URL); ?>" method="post">
				<p>
				<label><?php _e('e-mail adress','usces') ?><br />
				<input type="text" name="loginmail" id="loginmail" class="loginmail" value="<?php echo usces_remembername('return'); ?>" size="35" tabindex="10" /></label><br />
				<label><?php _e('password','usces') ?><br />
				<input type="password" name="loginpass" id="loginpass" class="loginpass" value="<?php echo usces_rememberpass('return'); ?>" size="35" tabindex="20" /></label><br />
				</p>
				<p class="submit clearfix">
				<input type="image" src="<?php bloginfo('stylesheet_directory'); ?>/images/sidebar/btn_login.png" name="member_login" id="member_login" alt="<?php _e('Log-in','usces') ?>" tabindex="100"  value="<?php _e('Log-in','usces') ?>"/>
				</p>
				<p class="forgetmenot">
				<label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90"<?php echo usces_remembercheck('return'); ?> /> <?php _e('memorize login information','usces') ?></label>
				</p>
				</form>
				<a href="<?php echo USCES_LOSTMEMBERPASSWORD_URL; ?>" title="<?php _e('Pssword Lost and Found','usces') ?>"><?php _e('Did you forget your password?','usces') ?></a><br />
			<?php }else{ ?>
				<?php printf(__('Mr/Mrs %s', 'usces'), usces_the_member_name()); ?><br />
				<?php echo usces_loginout(); ?><br />
				<a href="<?php echo USCES_MEMBER_URL; ?>"><?php _e('Membership information','usces') ?></a>
			<?php } ?>
			</div>
			</li>
		</ul>
	</li>
	<?php endif; ?>
	<li id="welcart_search" class="widget widget_welcart_search">
		<div class="widget_title"><?php _e('keyword search','usces') ?></div>
		<ul class="welcart_search_body welcart_widget_body">
			<li>
			<form method="get" id="searchform" name="searchform" action="<?php bloginfo('home'); ?>" >
			<input type="hidden" value="post" name="post_type" />
			<select class="searchbox" name="cat" id="cat">
				<option value="-<?php echo usces_get_cat_id(__('Uncategorized')); ?>" label="すべての商品" selected="selected">すべてのカテゴリから</option>
				<option value="306" label="&nbsp;メーカー">カテゴリ1</option>
				<option value="309" label="&nbsp;&nbsp;三菱レイヨン">&nbsp;&nbsp;子カテゴリ1</option>
				<option value="311" label="&nbsp;&nbsp;&nbsp;FUBUKI">&nbsp;&nbsp;&nbsp;孫カテゴリ1</option>
				<option value="1" label="&nbsp;ヘッド">&nbsp;カテゴリ2</option>
				<option value="2" label="&nbsp;&nbsp;ドライバー">&nbsp;&nbsp;子カテゴリ1</option>
				<option value="6" label="&nbsp;&nbsp;&nbsp;RomaRo">&nbsp;&nbsp;&nbsp;孫カテゴリ1</option>
				<option value="24" label="&nbsp;&nbsp;&nbsp;BALDO">&nbsp;&nbsp;&nbsp;孫カテゴリ2</option>
				<option value="187" label="&nbsp;&nbsp;アイアン">&nbsp;&nbsp;子カテゴリ2</option>
				<option value="188" label="&nbsp;&nbsp;&nbsp;Callaway">&nbsp;&nbsp;&nbsp;孫カテゴリ1</option>
				<option value="196" label="&nbsp;&nbsp;&nbsp;ROYALCOLLECTION">&nbsp;&nbsp;&nbsp;孫カテゴリ2</option>
				<option value="202" label="&nbsp;&nbsp;ウェッジ">&nbsp;&nbsp;子カテゴリ3</option>
				<option value="206" label="&nbsp;&nbsp;&nbsp;RomaRo">&nbsp;&nbsp;&nbsp;孫カテゴリ1</option>
				<option value="217" label="&nbsp;&nbsp;ユーティリティ">&nbsp;&nbsp;子カテゴリ4</option>
				<option value="230" label="&nbsp;&nbsp;&nbsp;その他">&nbsp;&nbsp;&nbsp;孫カテゴリ1</option>
			</select>
			<input type="text" value="<?php echo wp_specialchars($s); ?>" name="s" id="s" class="searchtext" /><br />
			<input type="image" src="<?php bloginfo('stylesheet_directory'); ?>/images/sidebar/btn_search.png" id="searchsubmit" alt="<?php _e('Search','usces') ?>" />
			</form>
			</li>
		</ul>
	</li>
	<li id="welcart_incart" class="widget widget_welcart_incart">
		<div class="widget_title">買い物かごの中</div>
		<ul class="welcart_incart_body welcart_widget_body">
			<li class="total_quantity">商品数 : <?php usces_totalquantity_in_cart(); ?>点</li>
			<li class="total_price"><div class="price">合計 : <?php usces_totalprice_in_cart(); ?>円</span></li>
		</ul>
		<div id="incartbutton"><a href="<?php echo USCES_CART_URL; ?>">詳細を見る</a></div>
	</li>
	<li id="welcart_category" class="widget widget_welcart_category">
		<div class="widget_title"><?php _e('Item Category','usces') ?></div>
		<ul class="welcart_widget_body">
		<?php $cats = get_category_by_slug('itemgenre'); ?>
		<?php wp_list_categories('orderby=id&use_desc_for_title=0&child_of='.$cats->term_id.'&title_li='); ?>
 		</ul>
	</li>
	<li>
		<script src="http://widgets.twimg.com/j/2/widget.js" type="text/javascript"></script>
		<script type="text/javascript">
		new TWTR.Widget({
			version: 2,
			type: 'profile',
			rpp: 5,
			interval: 5000,
			width: 240,
			height: 350,
			theme: {
				shell: {
					background: '#333333',
					color: '#ffffff'
				},
				tweets: {
					background: '#e3e3e3',
					color: '#000000',
					links: '#787822'
				}
			},
		  features: {
			scrollbar: false,
			loop: true,
			live: true,
			hashtags: true,
			timestamp: true,
			avatars: true,
			behavior: 'default'
		  }
		}).render().setUser('welcart').start();
		</script>
	</li>
	<?php /* ?>
	<li id="welcart_calendar" class="widget widget_welcart_calendar">
		<div class="widget_title"><?php _e('Business Calendar','usces') ?></div>
		<ul class="welcart_calendar_body welcart_widget_body"><li>
		<?php usces_the_calendar(); ?>
		</li></ul>
	</li>
	<?php */ ?>
<?php endif; ?>
</ul>
</div>
<!-- end left sidebar -->
