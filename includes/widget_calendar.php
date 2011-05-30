<?php
require_once(USCES_PLUGIN_DIR . '/classes/calendar.class.php');

//cur
list($todayyy, $todaymm, $todaydd) = getToday();
$cal1 = new calendarData();
$cal1->setToday($todayyy, $todaymm, $todaydd);
$cal1->setCalendarData();
//next
list($nextyy, $nextmm, $nextdd) = getAfterMonth($todayyy, $todaymm, 1, 1);
$cal2 = new calendarData();
$cal2->setToday($nextyy, $nextmm, $nextdd);
$cal2->setCalendarData();
$caption1 = __('This month', 'usces') . '(' . sprintf(__('%2$s/%1$s', 'usces'),$todayyy,$todaymm) . ')';
$caption2 = __('Next month', 'usces') . '(' . sprintf(__('%2$s/%1$s', 'usces'),$nextyy,$nextmm) . ')';
?>
<table cellspacing="0" id="wp-calendar" class="usces_calendar">
<caption><?php echo apply_filters( 'usces_filter_calendar_widget_cap1', $caption1, $todayyy, $todaymm); ?></caption>
<thead>
	<tr>
		<th><?php _e('Sun', 'usces'); ?></th>
		<th><?php _e('Mon', 'usces'); ?></th>
		<th><?php _e('Tue', 'usces'); ?></th>
		<th><?php _e('Wed', 'usces'); ?></th>
		<th><?php _e('Thu', 'usces'); ?></th>
		<th><?php _e('Fri', 'usces'); ?></th>
		<th><?php _e('Sat', 'usces'); ?></th>
	</tr>
</thead>
<tbody>
<?php for ($i = 0; $i < $cal1->getRow(); $i++) : ?>
	<tr>
<?php for ($d = 0; $d <= 6; $d++) : 
	$mday = $cal1->getDateText($i, $d);
	if ($mday != "") {
		$business = $usces->options['business_days'][$todayyy][$todaymm][$mday];
		//$style = ($business == 1) ? "" : ' style="background-color:#FFECCE; color:#ff0000;"';
		if($mday == $todaydd){
			$tostyle = 'businesstoday';
		}else{
			$tostyle = '';
		}
		if($business == 0){
			$style = 'businessday ';
		}else{
			$style = '';
		}
?>
		<td class="<?php echo $style . $tostyle; ?>"><?php echo $mday; ?></td>
<?php } else { ?>
		<td>&nbsp;</td>
<?php } ?>
<?php endfor; ?>
	</tr>
<?php endfor; ?>
</tbody>
</table>
<table cellspacing="0" id="wp-calendar" class="usces_calendar">
<caption><?php echo apply_filters( 'usces_filter_calendar_widget_cap2', $caption2, $nextyy, $nextmm); ?></caption>
<thead>
	<tr>
		<th><?php _e('Sun', 'usces'); ?></th>
		<th><?php _e('Mon', 'usces'); ?></th>
		<th><?php _e('Tue', 'usces'); ?></th>
		<th><?php _e('Wed', 'usces'); ?></th>
		<th><?php _e('Thu', 'usces'); ?></th>
		<th><?php _e('Fri', 'usces'); ?></th>
		<th><?php _e('Sat', 'usces'); ?></th>
	</tr>
</thead>
<tbody>
<?php for ($i = 0; $i < $cal2->getRow(); $i++) : ?>
	<tr>
<?php for ($d = 0; $d <= 6; $d++) : 
	$mday = $cal2->getDateText($i, $d);
	if ($mday != "") {
		$business = $usces->options['business_days'][$nextyy][$nextmm][$mday];
		//$style = ($business == 1) ? "" : ' style="background-color:#FFECCE; color:#ff0000;"';
		$style = ($business == 1) ? "" : ' class="businessday"'; ?>
		<td<?php echo $style; ?>><?php echo $mday; ?></td>
<?php } else { ?>
		<td>&nbsp;</td>
<?php } ?>
<?php endfor; ?>
	</tr>
<?php endfor; ?>
</tbody>
</table>
<?php
$afterword = '(<span class="business_days_exp_box businessday">&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;' . __('Holiday for Shipping Operations', 'usces') . ')'."\n";
echo apply_filters( 'usces_filter_widget_calendar', $afterword);
?>