<?php
define('MAGPIE_FETCH_TIME_OUT', 10);
define('MAGPIE_CACHE_ON', false);
include_once(ABSPATH . WPINC . '/rss.php');
$rss1 = @fetch_rss('http://www.usconsort.com/usces/archives/category/information/feed');
$items1 = @array_slice($rss1->items, 0, 3);
$rss2 = @fetch_rss('http://www.usconsort.com/usces/archives/category/development/feed');
$items2 = @array_slice($rss2->items, 0, 1);
$items = array_merge($items2, $items1);

$display_mode = $this->options['display_mode'];
$data = $this->get_items_skus();
$items_num = $this->get_items_num();
?>
<div class="wrap">
<div class="usces_admin">

<h2><!--<img src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/usc-e-shop/images/warehause1.png" />-->Welcart Shop ホーム<?php //echo __('USC e-Shop Options','usces'); ?></h2>
<p class="version_info">Version <?php echo USCES_VERSION; ?></p>

<div class="usces_admin_right">

<div class="usces_side_box">
<h5>表示モード:</h5>
<div class="dispmode <?php echo $display_mode; ?>"><?php echo $this->display_mode[$display_mode]; ?></div>
<?php if ( $display_mode == 'Promotionsale' ) : ?>
<span>特典:</span><?php echo $this->options["campaign_privilege"]; ?>（<?php if($this->options["campaign_privilege"] == 'discount'){echo $this->options["privilege_discount"].'%引き';}elseif($this->options["campaign_privilege"] == 'point'){echo $this->options["privilege_point"].'倍、会員のみ';} ?>）<br />
<span>対象:</span><?php echo get_cat_name($this->options["campaign_category"]); ?><br />
<span>期間:</span><?php echo $this->options["campaign_schedule"]['start']['year']; ?>/<?php echo $this->options["campaign_schedule"]['start']['month']; ?>/<?php echo $this->options["campaign_schedule"]['start']['day']; ?>～<?php echo $this->options["campaign_schedule"]['end']['year']; ?>/<?php echo $this->options["campaign_schedule"]['end']['month']; ?>/<?php echo $this->options["campaign_schedule"]['end']['day']; ?>
<?php endif; ?>
</div>

<?php if( $this->isAdnminSSL() ) : ?>
<div class="usces_side_box">
<h5>カートページ:</h5>
<div class="urlBox"><?php echo '?page_id=' . USCES_CART_NUMBER; ?></div>
<h5>メンバーページ:</h5>
<div class="urlBox"><?php echo '?page_id=' . USCES_MEMBER_NUMBER; ?></div>
</div>
<?php endif; ?>

<div class="chui">
<ul>
<?php if (empty($items)) echo '<li>現在、お知らせはありません。</li>';
else
foreach ( $items as $item ) : ?>
<li>
<h3><a href='<?php echo $item['link']; ?>' title='<?php echo $item['title']; ?>'><?php echo $item['title']; ?></a></h3>
<?php echo $item['content']['encoded']; ?>

</li>
<?php endforeach; ?>
</ul>
</div>

</div><!--usces_admin_right-->

<div class="usces_admin_left">
<h4>受注数・金額</h4>
<div class="usces_box">
<table class="dashboard">
<tr>
<th>&nbsp;</th><th>受注数</th><th>受注金額</th>
</tr>
<tr>
<td>今日：</td><td class="bignum"><?php echo number_format($this->get_order_num('today')); ?></td><td class="bignum"><?php echo number_format($this->get_order_amount('today')); ?></td>
</tr>
<tr>
<td>今月：</td><td class="bignum"><?php echo number_format($this->get_order_num('thismonth')); ?></td><td class="bignum"><?php echo number_format($this->get_order_amount('thismonth')); ?></td>
</tr>
<tr>
<td>昨年同月：</td><td class="bignum"><?php echo number_format($this->get_order_num('lastyear')); ?></td><td class="bignum"><?php echo number_format($this->get_order_amount('lastyear')); ?></td>
</tr>
</table>
</div>
<h4>商品登録情報</h4>
<div class="usces_box">
<table class="dashboard">
<tr>
<th>商品数</th><th colspan="5">SKU総数</th>
</tr>
<tr>
<td rowspan="3" class="bignum"><?php echo number_format($items_num); ?></td><td colspan="5" class="bignum"><?php echo number_format(count($data['data'])); ?></td>
</tr>
<tr>
<?php foreach($this->zaiko_status as $value): ?>
<th><?php if($value == '有り') {echo '在庫有り';}else{echo $value;} ?></th>
<?php endforeach; ?>
</tr>
<tr>
<?php foreach($this->zaiko_status as $value): $count = isset($data['count'][$value]) ? $data['count'][$value] : 0; ?>
<td class="bignum"><?php echo number_format($count); ?></td>
<?php endforeach; ?>
</tr>
<tr>
<th colspan="6">在庫数 0 の商品一覧</th>
</tr>
<?php foreach((array)$data['data'] as $value): if($value['num'] === "0"): ?>
<tr>
<td colspan="6"><a href="<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=usces_itemedit&action=edit&post=' . $value['ID']; ?>"><?php echo $value['name'] . ' ' . $value['code'] . ' ' . $value['sku']; ?></a></td>
</tr>
<?php endif; endforeach; ?>
</table>
</div>
<h4>ご利用の環境</h4>
<div class="usces_box">
<table class="dashboard">
<tr>
<th>&nbsp;</th><th colspan="2">ソフトウェア・バージョン</th>
</tr>
<tr>
<td>サーバー</td><td colspan="2"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
</tr>
<tr>
<td>MySQL</td><td colspan="2"><?php echo mysql_get_server_info(); ?></td>
</tr>
<tr>
<td>PHP</td><td colspan="2"><?php echo phpversion(); ?><?php if(ini_get('safe_mode')) echo "（セーフモード）"; ?></td>
</tr>
</table>
</div>
</div>
<!--usces_admin_left-->
<?php
//$xml = USCES_PLUGIN_DIR . '/includes/initial_data.xml';
//$match = $this->get_initial_data($xml);
//var_dump($match);
?>
</div><!--usces_admin-->
</div><!--wrap-->