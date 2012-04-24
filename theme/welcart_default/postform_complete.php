<?php
get_header();

get_sidebar( 'home' );

global $kanpari_area_tag;
$area_tag = ( !empty($kanpari_area_tag[$area]) ) ? $kanpari_area_tag[$area] : '';
?>
<div id="content" class="two-column">
<!-- コンテンツ -->

<div id="blogboxL">

<h2><img name="" src="/wp-content/themes/kanpari/img/titleL_tokoform-<?php echo $area_tag; ?>.jpg" width="538" height="30" alt="" /></h2>


<div class="line"><hr /></div>

<!-- 記事ここから -->

<div id="blogbox" >

<h2 class="t-thanks">ご投稿いただき、誠にありがとうございました。</h2>
<?php
	if( my_is_iphone() ) :
?>
<p><a href="sms:yskysmr@gmail.com">info@fishing.ne.jp</a></p>
<p>iPhone（アイフォン）をご使用の方は、上記アドレスまで該当の写真をメールにて2枚お送り下さい。<br />
写真の送信が無い場合、掲載不可になりますのでご注意下さい。</p>
<p>画像添付のメールタイトルには必ず会員番号、お名前の記載をお願い致します。</p>
<?php
	else :
?>
<p>改めてメールでご連絡いたしますのでしばらくお待ちください。</p>
<?php
	endif;
?>

</div>
<!-- 記事ここまで -->

</div>
</div>
<!-- コンテンツここまで -->


<?php get_footer(); ?>
