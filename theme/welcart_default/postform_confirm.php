<?php
get_header();

get_sidebar( 'home' );

global $kanpari_area;
global $kanpari_area_tag;
$area_name = ( !empty($kanpari_area[$area]) ) ? $kanpari_area[$area] : '';
$area_tag = ( !empty($kanpari_area_tag[$area]) ) ? $kanpari_area_tag[$area] : '';
?>
<div id="content" class="two-column">
<!-- コンテンツ -->

<div id="blogboxL">

<h2><img name="" src="/wp-content/themes/kanpari/img/titleL_tokoform-<?php echo $area_tag; ?>.jpg" width="538" height="30" alt="" /></h2>


<div class="line"><hr /></div>

<!-- 記事ここから -->

<div id="blogbox" >

<div>
<h3>以下の内容でお間違いはございませんか？</h3>
<p>入力内容にお間違いがなければ、<strong>「送信する」ボタン</strong>を押して送信してください。<br />
入力内容を修正する場合は<strong>「修正する」ボタン</strong>で入力画面に戻り、修正してください。</p>
</div>

<div class="post tokoform-<?php echo $area_tag; ?>">
<div class="blog_area">
<form name="postform_confirm" method="post" enctype="multipart/form-data" action="">
<table>
	<tr>
		<th><em>＊</em>お名前</th>
		<td><?php esc_html_e($data['name1']); ?>　<?php esc_html_e($data['name2']); ?></td>
	</tr>
	<tr>
		<th>ハンドルネーム</th>
		<td><?php esc_html_e($data['handle']); ?></td>
	</tr>
	<tr>
		<th><em>＊</em>メールアドレス</th>
		<td><?php esc_html_e($data['email']); ?></td>
	</tr>
	<tr>
		<th><em>＊</em>郵便番号</th>
		<td><?php esc_html_e($data['zipcode']); ?></td>
	</tr>
	<tr>
		<th><em>＊</em>都道府県</th>
		<td><?php esc_html_e($data['pref']); ?></td>
	</tr>
	<tr>
		<th><em>＊</em>市区郡町村</th>
		<td><?php esc_html_e($data['address1']); ?></td>
	</tr>
	<tr>
		<th><em>＊</em>番地</th>
		<td><?php esc_html_e($data['address2']); ?></td>
	</tr>
	<tr>
		<th>マンション･ビル名</th>
		<td><?php esc_html_e($data['address3']); ?></td>
	</tr>
	<tr>
		<th><em>＊</em>釣行場所　<?php echo $area_name; ?></th>
		<td><?php esc_html_e($data['location']); ?></td>
	</tr>
	<tr>
		<th><em>＊</em>釣行日</th>
		<td><?php esc_html_e($data['fishingdate']); ?></td>
	</tr>
	<tr>
		<th><em>＊</em>天気</th>
		<td><?php esc_html_e($data['weather']); ?></td>
	</tr>
	<tr>
		<th><em>＊</em>気温</th>
		<td><?php esc_html_e($data['temperature']); ?></td>
	</tr>
	<tr>
		<th>潮</th>
		<td><?php esc_html_e($data['tide']); ?></td>
	</tr>
	<tr>
		<th><em>＊</em>時間帯</th>
		<td><?php esc_html_e($data['timezone']); ?></td>
	</tr>
	<tr>
		<th><em>＊</em>釣り方</th>
		<td><?php esc_html_e($data['style']); ?></td>
	</tr>
	<tr>
		<th><em>＊</em>釣果</th>
		<td><?php echo my_change_br($data['fishing']); ?></td>
	</tr>
	<tr>
		<th>使用タックル</th>
		<td><?php echo my_change_br($data['usetackle']); ?></td>
	</tr>
	<tr>
		<th><em>＊</em>釣行レポート</th>
		<td><?php echo my_change_br($data['comment']); ?></td>
	</tr>
<?php
	if( my_is_iphone() ) :
?>
	<tr>
		<th><em>＊</em>釣果画像</th>
		<td>iPhone（アイフォン）をご使用の方は画像添付ができませんので、このフォーム内容を送信後、指定のメールアドレスに写真2枚を添付してお送り下さい。</td>
	</tr>
<?php
	else :
?>
	<tr>
		<th><em>＊</em>釣果画像01</th>
		<td><?php esc_html_e($data['image1']); ?><p id="display_image1"><?php echo $data['display_image1']; ?></p></td>
	</tr>
	<tr>
		<th><em>＊</em>釣果画像02</th>
		<td><?php esc_html_e($data['image2']); ?><p id="display_image2"><?php echo $data['display_image2']; ?></p></td>
	</tr>
<?php
	endif;
?>
</table>
<input type="submit" value="修正する" onClick="document.getElementById('entry_action').value = 'edit';" /><input type="submit" value="送信する" onClick="document.getElementById('entry_action').value = 'send';" />
<input type="hidden" name="entry_action" id="entry_action" value="" />
<input type="hidden" name="name1" id="name1" value="<?php esc_attr_e($data['name1']); ?>" />
<input type="hidden" name="name2" id="name2" value="<?php esc_attr_e($data['name2']); ?>" />
<input type="hidden" name="handle" id="handle" value="<?php esc_attr_e($data['handle']); ?>" />
<input type="hidden" name="email" id="email" value="<?php esc_attr_e($data['email']); ?>" />
<input type="hidden" name="zipcode" id="zipcode" value="<?php esc_attr_e($data['zipcode']); ?>" />
<input type="hidden" name="pref" id="pref" value="<?php esc_attr_e($data['pref']); ?>" />
<input type="hidden" name="address1" id="address1" value="<?php esc_attr_e($data['address1']); ?>" />
<input type="hidden" name="address2" id="address2" value="<?php esc_attr_e($data['address2']); ?>" />
<input type="hidden" name="address3" id="address3" value="<?php esc_attr_e($data['address3']); ?>" />
<input type="hidden" name="area" id="location" value="<?php esc_attr_e($data['area']); ?>" />
<input type="hidden" name="location" id="location" value="<?php esc_attr_e($data['location']); ?>" />
<input type="hidden" name="fishingdate" id="fishingdate" value="<?php esc_attr_e($data['fishingdate']); ?>" />
<input type="hidden" name="weather" id="weather" value="<?php esc_attr_e($data['weather']); ?>" />
<input type="hidden" name="temperature" id="temperature" value="<?php esc_attr_e($data['temperature']); ?>" />
<input type="hidden" name="tide" id="tide" value="<?php esc_attr_e($data['tide']); ?>" />
<input type="hidden" name="timezone" id="timezone" value="<?php esc_attr_e($data['timezone']); ?>" />
<input type="hidden" name="style" id="style" value="<?php esc_attr_e($data['style']); ?>" />
<input type="hidden" name="fishing" id="fishing" value="<?php esc_attr_e($data['fishing']); ?>" />
<input type="hidden" name="usetackle" id="usetackle" value="<?php esc_attr_e($data['usetackle']); ?>" />
<input type="hidden" name="comment" id="comment" value="<?php esc_attr_e($data['comment']); ?>" />
<input type="hidden" name="image1" id="image1" value="<?php esc_attr_e($data['image1']); ?>" />
<input type="hidden" name="image2" id="image2" value="<?php esc_attr_e($data['image2']); ?>" />
</form>
</div>

<div class="line"><hr /></div>

</div>


<div class="line"><hr /></div>

</div>
<!-- 記事ここまで -->

</div>
</div>
<!-- コンテンツここまで -->


<?php get_footer(); ?>
