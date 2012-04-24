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

<h2 class="t-thanks">ご投稿を送信できませんでした。</h2>
<p>ご投稿を送信できなかった可能性がございます。<br />お手数ですが、再度ご送信いただきますかお電話にてご連絡くださいますようお願いいたします。</p>

</div>
<!-- 記事ここまで -->

</div>
</div>
<!-- コンテンツここまで -->


<?php get_footer(); ?>
