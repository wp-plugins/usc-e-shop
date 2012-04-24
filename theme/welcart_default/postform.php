<?php
get_header();

get_sidebar( 'home' );

global $kanpari_area;
global $kanpari_area_tag;
global $kanpari_location;
global $kanpari_weather;
global $kanpari_temperature;
global $kanpari_tide;
global $kanpari_timezone;
$area_name = ( !empty($kanpari_area[$area]) ) ? $kanpari_area[$area] : '';
$area_tag = ( !empty($kanpari_area_tag[$area]) ) ? $kanpari_area_tag[$area] : '';
$country = usces_get_base_country();
$prefs = get_usces_states($country);
?>
<div id="content" class="two-column">
<!-- コンテンツ -->

<div id="blogboxL">

<h2><img name="" src="/wp-content/themes/kanpari/img/titleL_tokoform-<?php echo $area_tag; ?>.jpg" width="538" height="30" alt="" /></h2>

<div class="line"><hr /></div>

<!-- 記事ここから -->

<div id="blogbox" >

<?php if( $error_message ) : ?>
<!-- error -->
<div id="errorbox" class="clearfix">
	<dl>
		<dd>
			<ul>
				<?php echo $error_message; ?>
			</ul>
		</dd>
	</dl>
</div>
<!-- /error -->
<?php endif; ?>

<div class="post tokoform-<?php echo $area_tag; ?>">
<div class="blog_area">
<form name="postform" method="post" enctype="multipart/form-data" action="">
<table>
	<tr>
		<th><em>＊</em>お名前</th>
		<td><input type="text" name="name1" id="name1" value="<?php esc_attr_e($data['name1']); ?>" />　<input type="text" name="name2" id="name2" value="<?php esc_attr_e($data['name2']); ?>" /></td>
	</tr>
	<tr>
		<th>ハンドルネーム</th>
		<td><input type="text" name="handle" id="handle" value="<?php esc_attr_e($data['handle']); ?>" /></td>
	</tr>
	<tr>
		<th><em>＊</em>メールアドレス</th>
		<td><input type="text" name="email" id="email" value="<?php esc_attr_e($data['email']); ?>" /></td>
	</tr>
	<tr>
		<th><em>＊</em>郵便番号</th>
		<td><input type="text" name="zipcode" id="zipcode" value="<?php esc_attr_e($data['zipcode']); ?>" /></td>
	</tr>
	<tr>
		<th><em>＊</em>都道府県</th>
		<td>
			<select name="pref" id="pref">
			<?php foreach( $prefs as $value ) : ?>
				<option value="<?php esc_attr_e($value); ?>"<?php echo ($value == $data['pref'] ? ' selected="selected"' : ''); ?>><?php esc_attr_e($value); ?></option>
			<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><em>＊</em>市区郡町村</th>
		<td><input type="text" name="address1" id="address1" value="<?php esc_attr_e($data['address1']); ?>" /></td>
	</tr>
	<tr>
		<th><em>＊</em>番地</th>
		<td><input type="text" name="address2" id="address2" value="<?php esc_attr_e($data['address2']); ?>" /></td>
	</tr>
	<tr>
		<th>マンション･ビル名</th>
		<td><input type="text" name="address3" id="address3" value="<?php esc_attr_e($data['address3']); ?>" /></td>
	</tr>
	<tr>
		<th><em>＊</em>釣行場所　<?php echo $area_name; ?></th>
		<td>
			<select name="location" id="location">
			<?php foreach( $kanpari_location[$area] as $value ) : ?>
				<option value="<?php esc_attr_e($value); ?>"<?php echo ($value == $data['location'] ? ' selected="selected"' : ''); ?>><?php esc_attr_e($value); ?></option>
			<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><em>＊</em>釣行日</th>
		<td><input type="text" name="fishingdate" id="fishingdate" value="<?php esc_attr_e($data['fishingdate']); ?>" /></td>
	</tr>
	<tr>
		<th><em>＊</em>天気</th>
		<td>
			<select name="weather" id="weather">
			<?php foreach( $kanpari_weather as $value ) : ?>
				<option value="<?php esc_attr_e($value); ?>"<?php echo ($value == $data['weather'] ? ' selected="selected"' : ''); ?>><?php esc_attr_e($value); ?></option>
			<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><em>＊</em>気温</th>
		<td>
			<select name="temperature" id="temperature">
			<?php foreach( $kanpari_temperature as $value ) : ?>
				<option value="<?php esc_attr_e($value); ?>"<?php echo ($value == $data['temperature'] ? ' selected="selected"' : ''); ?>><?php esc_attr_e($value); ?></option>
			<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th>潮</th>
		<td>
			<select name="tide" id="tide">
			<?php foreach( $kanpari_tide as $value ) : ?>
				<option value="<?php esc_attr_e($value); ?>"<?php echo ($value == $data['tide'] ? ' selected="selected"' : ''); ?>><?php esc_attr_e($value); ?></option>
			<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><em>＊</em>時間帯</th>
		<td>
			<select name="timezone" id="timezone">
			<?php foreach( $kanpari_timezone as $value ) : ?>
				<option value="<?php esc_attr_e($value); ?>"<?php echo ($value == $data['timezone'] ? ' selected="selected"' : ''); ?>><?php esc_attr_e($value); ?></option>
			<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><em>＊</em>釣り方</th>
		<td><input type="text" name="style" id="style" value="<?php esc_attr_e($data['style']); ?>" /></td>
	</tr>
	<tr>
		<th><em>＊</em>釣果</th>
		<td><textarea name="fishing" id="fishing"><?php esc_attr_e($data['fishing']); ?></textarea></td>
	</tr>
	<tr>
		<th>使用タックル</th>
		<td><textarea name="usetackle" id="usetackle"><?php esc_attr_e($data['usetackle']); ?></textarea></td>
	</tr>
	<tr>
		<th><em>＊</em>釣行レポート</th>
		<td><textarea name="comment" id="comment"><?php esc_attr_e($data['comment']); ?></textarea></td>
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
		<td><input name="image1" id="image1" type="file" /></td>
	</tr>
	<tr>
		<th><em>＊</em>釣果画像02</th>
		<td><input name="image2" id="image2" type="file" /></td>
	</tr>
<?php
	endif;
?>
</table>
<input type="submit" value="確認画面へ" />
<input type="hidden" name="entry_action" value="confirm" />
<input type="hidden" name="area" value="<?php echo $area; ?>" />
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

<?php $error_message = ''; ?>
