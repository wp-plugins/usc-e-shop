<?php
require_once(USCES_PLUGIN_DIR . '/classes/calendar.class.php');

//当月
list($todayyy, $todaymm, $todaydd) = getToday();	// 今日
$cal1 = new calendarData();
$cal1->setToday($todayyy, $todaymm, $todaydd);
$cal1->setCalendarData();
//翌月
list($nextyy, $nextmm, $nextdd) = getAfterMonth($todayyy, $todaymm, $todaydd, 1);
$cal2 = new calendarData();
$cal2->setToday($nextyy, $nextmm, $nextdd);
$cal2->setCalendarData();
?>
<table cellspacing="0" id="wp-calendar" class="usces_calendar">
<caption>今月(<?php echo  $todayyy.'年'.$todaymm.'月'; ?>)</caption>
<thead>
	<tr>
		<th>日</th>
		<th>月</th>
		<th>火</th>
		<th>水</th>
		<th>木</th>
		<th>金</th>
		<th>土</th>
	</tr>
</thead>
<tbody>
<?php for ($i = 0; $i < $cal1->getRow(); $i++) : ?>
	<tr>
<?php for ($d = 0; $d <= 6; $d++) : 
	$mday = $cal1->getDateText($i, $d);
	if ($mday != "") {
		$business = $usces->options['business_days'][$todayyy][$todaymm][$mday];
		$style = ($business == 1) ? "" : ' style="background-color:#FFECCE; color:#ff0000;"'; ?>
		<td<?php echo $style; ?>><?php echo $mday; ?></td>
<?php } else { ?>
		<td>&nbsp;</td>
<?php } ?>
<?php endfor; ?>
	</tr>
<?php endfor; ?>
</tbody>
</table>
<table cellspacing="0" id="wp-calendar" class="usces_calendar">
<caption>翌月(<?php echo  $nextyy.'年'.$nextmm.'月'; ?>)</caption>
<thead>
	<tr>
		<th>日</th>
		<th>月</th>
		<th>火</th>
		<th>水</th>
		<th>木</th>
		<th>金</th>
		<th>土</th>
	</tr>
</thead>
<tbody>
<?php for ($i = 0; $i < $cal2->getRow(); $i++) : ?>
	<tr>
<?php for ($d = 0; $d <= 6; $d++) : 
	$mday = $cal2->getDateText($i, $d);
	if ($mday != "") {
		$business = $usces->options['business_days'][$nextyy][$nextmm][$mday];
		$style = ($business == 1) ? "" : ' style="background-color:#FFECCE; color:#ff0000;"'; ?>
		<td<?php echo $style; ?>><?php echo $mday; ?></td>
<?php } else { ?>
		<td>&nbsp;</td>
<?php } ?>
<?php endfor; ?>
	</tr>
<?php endfor; ?>
</tbody>
</table>
(<span class="business_days_exp_box" style="background-color:#FFECCE">　　</span>  発送業務休日)
