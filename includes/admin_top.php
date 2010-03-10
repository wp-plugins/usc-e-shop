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

<h2><!--<img src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/usc-e-shop/images/warehause1.png" />-->Welcart Shop <?php _e('Home','usces'); ?></h2>
<p class="version_info">Version <?php echo USCES_VERSION; ?></p>

<div class="usces_admin_right">

<div class="usces_side_box">
<h5><?php _e('Display Modes','usces'); ?>:</h5>
<div class="dispmode <?php echo $display_mode; ?>"><?php echo $this->display_mode[$display_mode]; ?></div>
<?php if ( $display_mode == 'Promotionsale' ) : ?>
<span><?php _e('Special Benefits', 'usces'); ?>:</span><?php echo $this->options["campaign_privilege"]; ?>（<?php if($this->options["campaign_privilege"] == 'discount'){echo $this->options["privilege_discount"].__('% Discount', 'usces');}elseif($this->options["campaign_privilege"] == 'point'){echo $this->options["privilege_point"].__(" times (limited to members)", 'usces');} ?>）<br />
<span><?php _e('applied material', 'usces'); ?>:</span><?php echo get_cat_name($this->options["campaign_category"]); ?><br />
<span><?php _e('Period', 'usces'); ?>:</span><?php echo $this->options["campaign_schedule"]['start']['year']; ?>/<?php echo $this->options["campaign_schedule"]['start']['month']; ?>/<?php echo $this->options["campaign_schedule"]['start']['day']; ?>～<?php echo $this->options["campaign_schedule"]['end']['year']; ?>/<?php echo $this->options["campaign_schedule"]['end']['month']; ?>/<?php echo $this->options["campaign_schedule"]['end']['day']; ?>
<?php endif; ?>
</div>

<?php if( $this->isAdnminSSL() ) : ?>
<div class="usces_side_box">
<h5><?php _e('Cart page', 'usces'); ?>:</h5>
<div class="urlBox"><?php echo '?page_id=' . USCES_CART_NUMBER; ?></div>
<h5><?php _e('Membership page', 'usces'); ?>:</h5>
<div class="urlBox"><?php echo '?page_id=' . USCES_MEMBER_NUMBER; ?></div>
</div>
<?php endif; ?>

<div class="chui">
<ul>
<?php if (empty($items)) echo '<li>'.__('There is no news for this moment.', 'usces').'</li>';
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
<h4><?php _e('number & amount of order', 'usces'); ?></h4>
<div class="usces_box">
<table class="dashboard">
<tr>
<th>&nbsp;</th><th><?php _e('number of order', 'usces'); ?></th><th><?php _e('amount of order', 'usces'); ?></th>
</tr>
<tr>
<td><?php _e('today', 'usces'); ?>：</td><td class="bignum"><?php echo number_format($this->get_order_num('today')); ?></td><td class="bignum"><?php echo number_format($this->get_order_amount('today')); ?></td>
</tr>
<tr>
<td><?php _e('This month', 'usces'); ?>：</td><td class="bignum"><?php echo number_format($this->get_order_num('thismonth')); ?></td><td class="bignum"><?php echo number_format($this->get_order_amount('thismonth')); ?></td>
</tr>
<tr>
<td><?php _e('Same date in last year', 'usces'); ?>：</td><td class="bignum"><?php echo number_format($this->get_order_num('lastyear')); ?></td><td class="bignum"><?php echo number_format($this->get_order_amount('lastyear')); ?></td>
</tr>
</table>
</div>
<h4><?php _e('information for registration of items', 'usces'); ?></h4>
<div class="usces_box">
<table class="dashboard">
<tr>
<th><?php _e('number of item', 'usces'); ?></th><th colspan="5"><?php _e('SKU total number', 'usces'); ?></th>
</tr>
<tr>
<td rowspan="3" class="bignum"><?php echo number_format($items_num); ?></td><td colspan="5" class="bignum"><?php echo number_format(count($data['data'])); ?></td>
</tr>
<tr>
<?php foreach($this->zaiko_status as $value): ?>
<th><?php if($value == __('OK', 'usces')) {echo __('In Stock', 'usces');}else{echo $value;} ?></th>
<?php endforeach; ?>
</tr>
<tr>
<?php foreach($this->zaiko_status as $value): $count = isset($data['count'][$value]) ? $data['count'][$value] : 0; ?>
<td class="bignum"><?php echo number_format($count); ?></td>
<?php endforeach; ?>
</tr>
<tr>
<th colspan="6"><?php _e('List of items without stock', 'usces'); ?></th>
</tr>
<?php foreach((array)$data['data'] as $value): if($value['num'] === "0"): ?>
<tr>
<td colspan="6"><a href="<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=usces_itemedit&action=edit&post=' . $value['ID']; ?>"><?php echo $value['name'] . ' ' . $value['code'] . ' ' . $value['sku']; ?></a></td>
</tr>
<?php endif; endforeach; ?>
</table>
</div>
<h4><?php _e('Your environment', 'usces'); ?></h4>
<div class="usces_box">
<table class="dashboard">
<tr>
<th>&nbsp;</th><th colspan="2"><?php _e('Software Version', 'usces'); ?></th>
</tr>
<tr>
<td><?php _e('Server', 'usces'); ?></td><td colspan="2"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
</tr>
<tr>
<td>MySQL</td><td colspan="2"><?php echo mysql_get_server_info(); ?></td>
</tr>
<tr>
<td>PHP</td><td colspan="2"><?php echo phpversion(); ?><?php if(ini_get('safe_mode')) echo "(".__('Safe mode', 'usces').")"; ?></td>
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