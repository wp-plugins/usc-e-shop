<?php
/**
 * <meta content="charset=UTF-8">
 * @package Netstage
 * @subpackage Netstage Theme
 */
?>
<?php $ua = $_SERVER['HTTP_USER_AGENT'];if (!(ereg("Windows",$ua) && ereg("MSIE",$ua)) || ereg("MSIE 7",$ua)) {echo '<?xml version="1.0" encoding="' . get_settings('blog_charset') .'"?>' . "\n";} ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes('xhtml'); ?>>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />

	<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>

	<style type="text/css" media="screen">
		@import url( <?php bloginfo('stylesheet_url'); ?> );
	</style>

	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
	<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php bloginfo('atom_url'); ?>" />

	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php wp_get_archives('type=monthly&format=link'); ?>
	<?php wp_head(); ?>
	<?php if(is_home()): ?>
	<link rel="stylesheet" type="text/css" href="<?php bloginfo('stylesheet_directory'); ?>/lib/js/simplyscroll/jquery.simplyscroll.css" />
	<script type='text/javascript' src="<?php bloginfo('stylesheet_directory'); ?>/lib/js/simplyscroll/jquery.simplyscroll.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("#reshaft_box").simplyScroll({
				className:'gighit',
				autoMode:'loop',
				pauseOnHover:true,
				speed:4
			});
		});
	</script>
    <style type="text/css">
         /* カスタマイズコンテンツ用 */
         .gighit {
            width:650px; height:225px;
            background-color:#000;
            margin:0 0 1em 0;
            border:10px solid #000;
         }
         .gighit .simply-scroll-clip {
            width:650px; height:225px;
         }
         .gighit .section {
            float:left;
            width:300px; height:225px;
         }
         .gighit .hp-highlight {
            height:225px;
            margin:-10px 10px 10px 0;
         }
         .gighit .feature-headline {
            position:relative;
            top:180px;
            width:270px;
            height:35px;
            background-color:#EEE;
            margin:10px 0px 20px 0px;
            padding:5px 10px 5px 10px;
         }
         .gighit .feature-headline a {
            font-weight:bold;
            text-decoration:none;
            color:#cc0000;
            display:block;
            width:533px;
         }
         .gighit h1 {
            font-weight:normal;
            font-size:12px;
            margin:0; padding:0;
         }
         .gighit p {
            margin:0; padding:0;
            font-size:.8em;
            color:#666;
         }
      </style>

    <?php endif;?>

</head>

<body>
<div id="bgbark">
<div id="wrapper">
<div id="header" class="clear">
	<h1><a href="<?php bloginfo('url'); ?>/"><?php bloginfo('name'); ?></a></h1>
	<div id="information">
		<ul>
		<?php if(!usces_is_login()): ?>
			<li id="header_regist"><a href="<?php echo USCES_NEWMEMBER_URL; ?>" title="<?php _e('New enrollment for membership.','usces') ?>">新規会員登録</a></li>
		<?php endif; ?>
			<li id="header_mypage"><a href="<?php echo USCES_MEMBER_URL; ?>">MYページ</a></li>
			<li id="header_cart"><a href="<?php echo USCES_CART_URL; ?>">カートの中を見る</a></li>
		</ul>
	</div>
	<div id="mainimage" class="clear">
		<img src="<?php bloginfo('stylesheet_directory'); ?>/images/header/mainimage.png" alt="<?php bloginfo('name'); ?>" width="950" height="213" />
	</div>
	<div id="mainnavi">
		<ul>
			<li><a href="<?php bloginfo('url'); ?>/">HOME</a></li>
			<li><a href="<?php bloginfo('url'); ?>/about">当サイトについて</a></li>
			<li><a href="<?php bloginfo('url'); ?>/service">利用規約</a></li>
			<li><a href="<?php bloginfo('url'); ?>/legal">特定商取引に関する法律</a></li>
			<li><a href="<?php bloginfo('url'); ?>/contact">お問合わせ</a></li>
			<li><a href="<?php echo get_page_link(30); ?>">セット販売</a></li>
		</ul>
	</div>
</div><!-- end of header -->
<!--▲HEADER-->


<div id="main" class="clearfix">
<?php if(is_home()): ?><div id="newsticker">会員登録された方には、最新の商品情報やお得な商品のお知らせをお届けいたします。</div><?php endif;?>
<?php get_sidebar(); ?>
<div id="rightbar">
<!-- end header -->
