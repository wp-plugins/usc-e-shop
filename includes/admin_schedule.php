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
//aft
list($lateryy, $latermm, $laterdd) = getAfterMonth($todayyy, $todaymm, 1, 2);
$cal3 = new calendarData();
$cal3->setToday($lateryy, $latermm, $laterdd);
$cal3->setCalendarData();
//20131205_kitamu_start
//the_4th_month(three months later)
list($the_4th_monthyy, $the_4th_monthmm, $the_4th_monthdd) = getAfterMonth($todayyy, $todaymm, 1, 3);
$cal4 = new calendarData();
$cal4->setToday($the_4th_monthyy, $the_4th_monthmm, $the_4th_monthdd);
$cal4->setCalendarData();
//5th
list($the_5th_monthyy, $the_5th_monthmm, $the_5th_monthdd) = getAfterMonth($todayyy, $todaymm, 1, 4);
$cal5 = new calendarData();
$cal5->setToday($the_5th_monthyy, $the_5th_monthmm, $the_5th_monthdd);
$cal5->setCalendarData();
//6th
list($the_6th_monthyy, $the_6th_monthmm, $the_6th_monthdd) = getAfterMonth($todayyy, $todaymm, 1, 5);
$cal6 = new calendarData();
$cal6->setToday($the_6th_monthyy, $the_6th_monthmm, $the_6th_monthdd);
$cal6->setCalendarData();
//7th
list($the_7th_monthyy, $the_7th_monthmm, $the_7th_monthdd) = getAfterMonth($todayyy, $todaymm, 1, 6);
$cal7 = new calendarData();
$cal7->setToday($the_7th_monthyy, $the_7th_monthmm, $the_7th_monthdd);
$cal7->setCalendarData();
//8th
list($the_8th_monthyy, $the_8th_monthmm, $the_8th_monthdd) = getAfterMonth($todayyy, $todaymm, 1, 7);
$cal8 = new calendarData();
$cal8->setToday($the_8th_monthyy, $the_8th_monthmm, $the_8th_monthdd);
$cal8->setCalendarData();
//9th
list($the_9th_monthyy, $the_9th_monthmm, $the_9th_monthdd) = getAfterMonth($todayyy, $todaymm, 1, 8);
$cal9 = new calendarData();
$cal9->setToday($the_9th_monthyy, $the_9th_monthmm, $the_9th_monthdd);
$cal9->setCalendarData();
//10th
list($the_10th_monthyy, $the_10th_monthmm, $the_10th_monthdd) = getAfterMonth($todayyy, $todaymm, 1, 9);
$cal10 = new calendarData();
$cal10->setToday($the_10th_monthyy, $the_10th_monthmm, $the_10th_monthdd);
$cal10->setCalendarData();
//11th
list($the_11th_monthyy, $the_11th_monthmm, $the_11th_monthdd) = getAfterMonth($todayyy, $todaymm, 1, 10);
$cal11 = new calendarData();
$cal11->setToday($the_11th_monthyy, $the_11th_monthmm, $the_11th_monthdd);
$cal11->setCalendarData();
//12th
list($the_12th_monthyy, $the_12th_monthmm, $the_12th_monthdd) = getAfterMonth($todayyy, $todaymm, 1, 11);
$cal12 = new calendarData();
$cal12->setToday($the_12th_monthyy, $the_12th_monthmm, $the_12th_monthdd);
$cal12->setCalendarData();
//20131205_kitamu_end

$yearstr = substr(get_date_from_gmt(gmdate('Y-m-d H:i:s', time())), 0, 4);

$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';

$campaign_schedule_start_year = isset($this->options['campaign_schedule']['start']['year']) ? $this->options['campaign_schedule']['start']['year'] : 0;
$campaign_schedule_start_month = isset($this->options['campaign_schedule']['start']['month']) ? $this->options['campaign_schedule']['start']['month'] : 0;
$campaign_schedule_start_day = isset($this->options['campaign_schedule']['start']['day']) ? $this->options['campaign_schedule']['start']['day'] : 0;
$campaign_schedule_start_hour = isset($this->options['campaign_schedule']['start']['hour']) ? $this->options['campaign_schedule']['start']['hour'] : 0;
$campaign_schedule_start_min = isset($this->options['campaign_schedule']['start']['min']) ? $this->options['campaign_schedule']['start']['min'] : 0;
$campaign_schedule_end_year = isset($this->options['campaign_schedule']['end']['year']) ? $this->options['campaign_schedule']['end']['year'] : 0;
$campaign_schedule_end_month = isset($this->options['campaign_schedule']['end']['month']) ? $this->options['campaign_schedule']['end']['month'] : 0;
$campaign_schedule_end_day = isset($this->options['campaign_schedule']['end']['day']) ? $this->options['campaign_schedule']['end']['day'] : 0;
$campaign_schedule_end_hour = isset($this->options['campaign_schedule']['end']['hour']) ? $this->options['campaign_schedule']['end']['hour'] : 0;
$campaign_schedule_end_min = isset($this->options['campaign_schedule']['end']['min']) ? $this->options['campaign_schedule']['end']['min'] : 0;
?>
<script type="text/javascript">
jQuery(function($){
<?php if($status == 'success'){ ?>
			$("#anibox").animate({ backgroundColor: "#ECFFFF" }, 2000);
<?php }else if($status == 'caution'){ ?>
			$("#anibox").animate({ backgroundColor: "#FFF5CE" }, 2000);
<?php }else if($status == 'error'){ ?>
			$("#anibox").animate({ backgroundColor: "#FFE6E6" }, 2000);
<?php } ?>

	$("#aAdditionalURLs").click(function () {
		$("#AdditionalURLs").toggle();
	});
});

function toggleVisibility(id) {
   var e = document.getElementById(id);
   if(e.style.display == 'block')
	  e.style.display = 'none';
   else
	  e.style.display = 'block';
}

function cangeBus(id, r, c) {
	var e = document.getElementById(id+'_'+r+'_'+c);
	var v = document.getElementById(id).rows[r].cells[c];
	if (e.value == '0') {
		e.value = '1';
		v.style.backgroundColor = '#DFFFDD';
		v.style.color = '#555555';
		v.style.fontWeight = 'normal';
	} else {
		e.value = '0';
		v.style.backgroundColor = '#FFAA55';
		v.style.color = '#FFFFFF';
		v.style.fontWeight = 'bold';
	}
}

function cangeWday1(id, c) {
<?php for ($i = 0; $i < $cal1->getRow(); $i++) : ?>
	if (document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c)) {
		var e = document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c);
		var v = document.getElementById(id).rows[<?php echo ($i+1); ?>].cells[c];
		if (e.value == '0') {
			e.value = '1';
			v.style.backgroundColor = '#DFFFDD';
			v.style.color = '#555555';
			v.style.fontWeight = 'normal';
		} else {
			e.value = '0';
			v.style.backgroundColor = '#FFAA55';
			v.style.color = '#FFFFFF';
			v.style.fontWeight = 'bold';
		}
	}
<?php endfor; ?>
}

function cangeWday2(id, c) {
<?php for ($i = 0; $i < $cal2->getRow(); $i++) : ?>
	if (document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c)) {
		var e = document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c);
		var v = document.getElementById(id).rows[<?php echo ($i+1); ?>].cells[c];
		if (e.value == '0') {
			e.value = '1';
			v.style.backgroundColor = '#DFFFDD';
			v.style.color = '#555555';
			v.style.fontWeight = 'normal';
		} else {
			e.value = '0';
			v.style.backgroundColor = '#FFAA55';
			v.style.color = '#FFFFFF';
			v.style.fontWeight = 'bold';
		}
	}
<?php endfor; ?>
}

function cangeWday3(id, c) {
<?php for ($i = 0; $i < $cal3->getRow(); $i++) : ?>
	if (document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c)) {
		var e = document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c);
		var v = document.getElementById(id).rows[<?php echo ($i+1); ?>].cells[c];
		if (e.value == '0') {
			e.value = '1';
			v.style.backgroundColor = '#DFFFDD';
			v.style.color = '#555555';
			v.style.fontWeight = 'normal';
		} else {
			e.value = '0';
			v.style.backgroundColor = '#FFAA55';
			v.style.color = '#FFFFFF';
			v.style.fontWeight = 'bold';
		}
	}
<?php endfor; ?>
}

//20131206_kitamu_start▼
//4th
function cangeWday4(id, c) {
<?php for ($i = 0; $i < $cal4->getRow(); $i++) : ?>
	if (document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c)) {
		var e = document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c);
		var v = document.getElementById(id).rows[<?php echo ($i+1); ?>].cells[c];
		if (e.value == '0') {
			e.value = '1';
			v.style.backgroundColor = '#DFFFDD';
			v.style.color = '#555555';
			v.style.fontWeight = 'normal';
		} else {
			e.value = '0';
			v.style.backgroundColor = '#FFAA55';
			v.style.color = '#FFFFFF';
			v.style.fontWeight = 'bold';
		}
	}
<?php endfor; ?>
}
//5th
function cangeWday5(id, c) {
<?php for ($i = 0; $i < $cal5->getRow(); $i++) : ?>
	if (document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c)) {
		var e = document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c);
		var v = document.getElementById(id).rows[<?php echo ($i+1); ?>].cells[c];
		if (e.value == '0') {
			e.value = '1';
			v.style.backgroundColor = '#DFFFDD';
			v.style.color = '#555555';
			v.style.fontWeight = 'normal';
		} else {
			e.value = '0';
			v.style.backgroundColor = '#FFAA55';
			v.style.color = '#FFFFFF';
			v.style.fontWeight = 'bold';
		}
	}
<?php endfor; ?>
}

//6th
function cangeWday6(id, c) {
<?php for ($i = 0; $i < $cal6->getRow(); $i++) : ?>
	if (document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c)) {
		var e = document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c);
		var v = document.getElementById(id).rows[<?php echo ($i+1); ?>].cells[c];
		if (e.value == '0') {
			e.value = '1';
			v.style.backgroundColor = '#DFFFDD';
			v.style.color = '#555555';
			v.style.fontWeight = 'normal';
		} else {
			e.value = '0';
			v.style.backgroundColor = '#FFAA55';
			v.style.color = '#FFFFFF';
			v.style.fontWeight = 'bold';
		}
	}
<?php endfor; ?>
}

//7th
function cangeWday7(id, c) {
<?php for ($i = 0; $i < $cal7->getRow(); $i++) : ?>
	if (document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c)) {
		var e = document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c);
		var v = document.getElementById(id).rows[<?php echo ($i+1); ?>].cells[c];
		if (e.value == '0') {
			e.value = '1';
			v.style.backgroundColor = '#DFFFDD';
			v.style.color = '#555555';
			v.style.fontWeight = 'normal';
		} else {
			e.value = '0';
			v.style.backgroundColor = '#FFAA55';
			v.style.color = '#FFFFFF';
			v.style.fontWeight = 'bold';
		}
	}
<?php endfor; ?>
}

//8th
function cangeWday8(id, c) {
<?php for ($i = 0; $i < $cal8->getRow(); $i++) : ?>
	if (document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c)) {
		var e = document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c);
		var v = document.getElementById(id).rows[<?php echo ($i+1); ?>].cells[c];
		if (e.value == '0') {
			e.value = '1';
			v.style.backgroundColor = '#DFFFDD';
			v.style.color = '#555555';
			v.style.fontWeight = 'normal';
		} else {
			e.value = '0';
			v.style.backgroundColor = '#FFAA55';
			v.style.color = '#FFFFFF';
			v.style.fontWeight = 'bold';
		}
	}
<?php endfor; ?>
}

//9th
function cangeWday9(id, c) {
<?php for ($i = 0; $i < $cal9->getRow(); $i++) : ?>
	if (document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c)) {
		var e = document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c);
		var v = document.getElementById(id).rows[<?php echo ($i+1); ?>].cells[c];
		if (e.value == '0') {
			e.value = '1';
			v.style.backgroundColor = '#DFFFDD';
			v.style.color = '#555555';
			v.style.fontWeight = 'normal';
		} else {
			e.value = '0';
			v.style.backgroundColor = '#FFAA55';
			v.style.color = '#FFFFFF';
			v.style.fontWeight = 'bold';
		}
	}
<?php endfor; ?>
}

//10th
function cangeWday10(id, c) {
<?php for ($i = 0; $i < $cal10->getRow(); $i++) : ?>
	if (document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c)) {
		var e = document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c);
		var v = document.getElementById(id).rows[<?php echo ($i+1); ?>].cells[c];
		if (e.value == '0') {
			e.value = '1';
			v.style.backgroundColor = '#DFFFDD';
			v.style.color = '#555555';
			v.style.fontWeight = 'normal';
		} else {
			e.value = '0';
			v.style.backgroundColor = '#FFAA55';
			v.style.color = '#FFFFFF';
			v.style.fontWeight = 'bold';
		}
	}
<?php endfor; ?>
}

//11th
function cangeWday11(id, c) {
<?php for ($i = 0; $i < $cal11->getRow(); $i++) : ?>
	if (document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c)) {
		var e = document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c);
		var v = document.getElementById(id).rows[<?php echo ($i+1); ?>].cells[c];
		if (e.value == '0') {
			e.value = '1';
			v.style.backgroundColor = '#DFFFDD';
			v.style.color = '#555555';
			v.style.fontWeight = 'normal';
		} else {
			e.value = '0';
			v.style.backgroundColor = '#FFAA55';
			v.style.color = '#FFFFFF';
			v.style.fontWeight = 'bold';
		}
	}
<?php endfor; ?>
}

//12th
function cangeWday12(id, c) {
<?php for ($i = 0; $i < $cal12->getRow(); $i++) : ?>
	if (document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c)) {
		var e = document.getElementById(id+'_'+<?php echo ($i+1); ?>+'_'+c);
		var v = document.getElementById(id).rows[<?php echo ($i+1); ?>].cells[c];
		if (e.value == '0') {
			e.value = '1';
			v.style.backgroundColor = '#DFFFDD';
			v.style.color = '#555555';
			v.style.fontWeight = 'normal';
		} else {
			e.value = '0';
			v.style.backgroundColor = '#FFAA55';
			v.style.color = '#FFFFFF';
			v.style.fontWeight = 'bold';
		}
	}
<?php endfor; ?>
}
//20131206_kitamu_end▲

</script>
<div class="wrap">
<div class="usces_admin">
<h2>Welcart Shop <?php _e('Business Days Setting','usces'); ?></h2>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>
<form action="" method="post" name="option_form" id="option_form">
<input name="usces_option_update" type="submit" class="button" value="<?php _e('change decision','usces'); ?>" />
<div id="poststuff" class="metabox-holder">
<?php do_action( 'usces_action_admin_schedule1' ); ?>
<div class="postbox">
<h3 class="hndle"><span><?php _e('Campaign Schedule','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_campaign_schedule');"> (<?php _e('explanation', 'usces'); ?>) </a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><?php _e('starting time','usces'); ?></th>
	    <td><select name="campaign_schedule[start][year]">
	    		<option value="0"<?php if($campaign_schedule_start_year == 0) echo ' selected="selected"'; ?>></option>
	    		<option value="<?php echo $yearstr; ?>"<?php if($campaign_schedule_start_year == $yearstr) echo ' selected="selected"'; ?>><?php echo $yearstr; ?></option>
	    		<option value="<?php echo $yearstr+1; ?>"<?php if($campaign_schedule_start_year == ($yearstr+1)) echo ' selected="selected"'; ?>><?php echo $yearstr+1; ?></option>
		</select></td>
		<td><?php _e('year','usces'); ?></td>
	    <td><select name="campaign_schedule[start][month]">
	    		<option value="0"<?php if($campaign_schedule_start_month == 0) echo ' selected="selected"'; ?>></option>
<?php for($i=1; $i<13; $i++) : ?>
	    		<option value="<?php echo $i; ?>"<?php if($campaign_schedule_start_month == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
<?php endfor; ?>
		</select></td>
		<td><?php _e('month','usces'); ?></td>
	    <td><select name="campaign_schedule[start][day]">
	    		<option value="0"<?php if($campaign_schedule_start_day == 0) echo ' selected="selected"'; ?>></option>
<?php for($i=1; $i<32; $i++) : ?>
	    		<option value="<?php echo $i; ?>"<?php if($campaign_schedule_start_day == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
<?php endfor; ?>
		</select></td>
		<td><?php _e('day','usces'); ?></td>
	    <td><select name="campaign_schedule[start][hour]">
<?php for($i=0; $i<24; $i++) : ?>
	    		<option value="<?php echo $i; ?>"<?php if($campaign_schedule_start_hour == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
<?php endfor; ?>
		</select></td>
		<td><?php _e('hour','usces'); ?></td>
	    <td><select name="campaign_schedule[start][min]">
<?php for($i=0; $i<12; $i++) : ?>
	    		<option value="<?php echo $i*5; ?>"<?php if($campaign_schedule_start_min == ($i*5)) echo ' selected="selected"'; ?>><?php echo $i*5; ?></option>
<?php endfor; ?>
		</select></td>
		<td><?php _e('min','usces'); ?></td>
	</tr>
	<tr>
	    <th><?php _e('date and time of termination','usces'); ?></th>
	    <td><select name="campaign_schedule[end][year]">
	    		<option value="0"<?php if($campaign_schedule_end_year == 0) echo ' selected="selected"'; ?>></option>
	    		<option value="<?php echo $yearstr; ?>"<?php if($campaign_schedule_end_year == $yearstr) echo ' selected="selected"'; ?>><?php echo $yearstr; ?></option>
	    		<option value="<?php echo $yearstr+1; ?>"<?php if($campaign_schedule_end_year == ($yearstr+1)) echo ' selected="selected"'; ?>><?php echo $yearstr+1; ?></option>
		</select></td>
		<td><?php _e('year','usces'); ?></td>
	    <td><select name="campaign_schedule[end][month]">
	    		<option value="0"<?php if($campaign_schedule_end_month == 0) echo ' selected="selected"'; ?>></option>
<?php for($i=1; $i<13; $i++) : ?>
	    		<option value="<?php echo $i; ?>"<?php if($campaign_schedule_end_month == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
<?php endfor; ?>
		</select></td>
		<td><?php _e('month','usces'); ?></td>
	    <td><select name="campaign_schedule[end][day]">
	    		<option value="0"<?php if($campaign_schedule_end_day == 0) echo ' selected="selected"'; ?>></option>
<?php for($i=1; $i<32; $i++) : ?>
	    		<option value="<?php echo $i; ?>"<?php if($campaign_schedule_end_day == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
<?php endfor; ?>
		</select></td>
		<td><?php _e('day','usces'); ?></td>
	    <td><select name="campaign_schedule[end][hour]">
<?php for($i=0; $i<24; $i++) : ?>
	    		<option value="<?php echo $i; ?>"<?php if($campaign_schedule_end_hour == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
<?php endfor; ?>
		</select></td>
		<td><?php _e('hour','usces'); ?></td>
	    <td><select name="campaign_schedule[end][min]">
<?php for($i=0; $i<12; $i++) : ?>
	    		<option value="<?php echo $i*5; ?>"<?php if($campaign_schedule_end_min == ($i*5)) echo ' selected="selected"'; ?>><?php echo $i*5; ?></option>
<?php endfor; ?>
		</select></td>
		<td><?php _e('min','usces'); ?></td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_campaign_schedule" class="explanation"><?php _e('reserve the period of campaign.', 'usces'); ?></div>
</div>
</div><!--postbox-->
<?php do_action( 'usces_action_admin_schedule2' ); ?>
<div class="postbox">
<h3 class="hndle"><span><?php _e('Business Calendar', 'usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_charge');"> (<?php _e('explanation', 'usces'); ?>) </a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><?php _e('This month', 'usces'); ?><br /><?php echo sprintf(__('%2$s/%1$s', 'usces'),$todayyy,$todaymm); ?></th>
	    <td>
		<table cellspacing="0" id="calendar1" class="calendar">
			<tr>
				<th class="cal"><div onclick="cangeWday1('calendar1', '0');"><font color="#FF3300"><?php _e('Sun', 'usces'); ?></font></div></th>
				<th class="cal"><div onclick="cangeWday1('calendar1', '1');"><?php _e('Mon', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday1('calendar1', '2');"><?php _e('Tue', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday1('calendar1', '3');"><?php _e('Wed', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday1('calendar1', '4');"><?php _e('Thu', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday1('calendar1', '5');"><?php _e('Fri', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday1('calendar1', '6');"><?php _e('Sat', 'usces'); ?></div></th>
			</tr>
<?php for ($i = 0; $i < $cal1->getRow(); $i++) : ?>
			<tr>
	<?php for ($d = 0; $d <= 6; $d++) : 
			$mday = $cal1->getDateText($i, $d);
			if ($mday != "") {
				$business = $this->options['business_days'][$todayyy][$todaymm][$mday];
				$color = ($business == 1) ? '#DFFFDD' : '#FFAA55;color:#FFFFFF;font-weight:bold;'; ?>
				<td class="cal" style="background-color:<?php echo $color; ?>"><div onclick="cangeBus('calendar1', <?php echo ($i + 1); ?>, <?php echo $d; ?>);"><?php echo $mday; ?></div>
				<input name="business_days[<?php echo $todayyy; ?>][<?php echo $todaymm; ?>][<?php echo $mday; ?>]" id="calendar1_<?php echo ($i+1); ?>_<?php echo $d; ?>" type="hidden" value="<?php echo $business; ?>"></td>
		<?php } else { ?>
				<td>&nbsp;</td>
		<?php } ?>
	<?php endfor; ?>
			</tr>
<?php endfor; ?>
		</table>
		</td>
		<td><span class="business_days_exp_box" style="background-color:#DFFFDD">&nbsp;&nbsp;&nbsp;</span><?php _e('Working day', 'usces'); ?><br /><span class="business_days_exp_box" style="background-color:#FFAA55">&nbsp;&nbsp;&nbsp;</span><?php _e('Holiday for Shipping Operations', 'usces'); ?></td>
	</tr>
	<tr>
	    <th><?php _e('Next month', 'usces'); ?><br /><?php echo sprintf(__('%2$s/%1$s', 'usces'),$nextyy,$nextmm); ?></th>
	    <td>
		<table cellspacing="0" id="calendar2" class="calendar">
			<tr>
				<th class="cal"><div onclick="cangeWday2('calendar2', '0');"><font color="#FF3300"><?php _e('Sun', 'usces'); ?></font></div></th>
				<th class="cal"><div onclick="cangeWday2('calendar2', '1');"><?php _e('Mon', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday2('calendar2', '2');"><?php _e('Tue', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday2('calendar2', '3');"><?php _e('Wed', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday2('calendar2', '4');"><?php _e('Thu', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday2('calendar2', '5');"><?php _e('Fri', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday2('calendar2', '6');"><?php _e('Sat', 'usces'); ?></div></th>
			</tr>
<?php for ($i = 0; $i < $cal2->getRow(); $i++) : ?>
			<tr>
	<?php for ($d = 0; $d <= 6; $d++) : 
			$mday = $cal2->getDateText($i, $d);
			if ($mday != "") {
				$business = $this->options['business_days'][$nextyy][$nextmm][$mday];
				$color = ($business == 1) ? '#DFFFDD' : '#FFAA55;color:#FFFFFF;font-weight:bold;'; ?>
				<td class="cal" style="background-color:<?php echo $color; ?>"><div onclick="cangeBus('calendar2', <?php echo ($i + 1); ?>, <?php echo $d; ?>);"><?php echo $mday; ?></div>
				<input name="business_days[<?php echo $nextyy; ?>][<?php echo $nextmm; ?>][<?php echo $mday; ?>]" id="calendar2_<?php echo ($i+1); ?>_<?php echo $d; ?>" type="hidden" value="<?php echo $business; ?>"></td>
		<?php } else { ?>
				<td>&nbsp;</td>
		<?php } ?>
	<?php endfor; ?>
			</tr>
<?php endfor; ?>
		</table>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th><?php _e('Month after next month', 'usces'); ?><br /><?php echo sprintf(__('%2$s/%1$s', 'usces'),$lateryy,$latermm); ?></th>
	    <td>
		<table cellspacing="0" id="calendar3" class="calendar">
			<tr>
				<th class="cal"><div onclick="cangeWday3('calendar3', '0');"><font color="#FF3300"><?php _e('Sun', 'usces'); ?></font></div></th>
				<th class="cal"><div onclick="cangeWday3('calendar3', '1');"><?php _e('Mon', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday3('calendar3', '2');"><?php _e('Tue', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday3('calendar3', '3');"><?php _e('Wed', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday3('calendar3', '4');"><?php _e('Thu', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday3('calendar3', '5');"><?php _e('Fri', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday3('calendar3', '6');"><?php _e('Sat', 'usces'); ?></div></th>
			</tr>
<?php for ($i = 0; $i < $cal3->getRow(); $i++) : ?>
			<tr>
	<?php for ($d = 0; $d <= 6; $d++) : 
			$mday = $cal3->getDateText($i, $d);
			if ($mday != "") {
				$business = $this->options['business_days'][$lateryy][$latermm][$mday];
//20110131ysk start
				//if(empty($business)) $business = '0';
				if($business != 0) $business = 1;
//20110131ysk end
				$color = ($business == 1) ? '#DFFFDD' : '#FFAA55;color:#FFFFFF;font-weight:bold;'; ?>
				<td class="cal" style="background-color:<?php echo $color; ?>"><div onclick="cangeBus('calendar3', <?php echo ($i + 1); ?>, <?php echo $d; ?>);"><?php echo $mday; ?></div>
				<input name="business_days[<?php echo $lateryy; ?>][<?php echo $latermm; ?>][<?php echo $mday; ?>]" id="calendar3_<?php echo ($i+1); ?>_<?php echo $d; ?>" type="hidden" value="<?php echo $business; ?>"></td>
		<?php } else { ?>
				<td>&nbsp;</td>
		<?php } ?>
	<?php endfor; ?>
			</tr>
<?php endfor; ?>
		</table>
		</td>
		<td>&nbsp;</td>
	</tr>
<!--20131206_kitamu_start▼-->
<!--4th-->
	<tr>
	    <th><?php echo sprintf(__('%2$s/%1$s', 'usces'),$the_4th_monthyy,$the_4th_monthmm); ?></th>
	    <td>
		<table cellspacing="0" id="calendar4" class="calendar">
			<tr>
				<th class="cal"><div onclick="cangeWday4('calendar4', '0');"><font color="#FF3300"><?php _e('Sun', 'usces'); ?></font></div></th>
				<th class="cal"><div onclick="cangeWday4('calendar4', '1');"><?php _e('Mon', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday4('calendar4', '2');"><?php _e('Tue', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday4('calendar4', '3');"><?php _e('Wed', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday4('calendar4', '4');"><?php _e('Thu', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday4('calendar4', '5');"><?php _e('Fri', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday4('calendar4', '6');"><?php _e('Sat', 'usces'); ?></div></th>
			</tr>
<?php for ($i = 0; $i < $cal4->getRow(); $i++) : ?>
			<tr>
	<?php for ($d = 0; $d <= 6; $d++) : 
			$mday = $cal4->getDateText($i, $d);
			if ($mday != "") {
				$business = isset( $this->options['business_days'][$the_4th_monthyy][$the_4th_monthmm][$mday]) ? $this->options['business_days'][$the_4th_monthyy][$the_4th_monthmm][$mday] : 1;
				$color = ($business == 1) ? '#DFFFDD' : '#FFAA55;color:#FFFFFF;font-weight:bold;'; ?>
				<td class="cal" style="background-color:<?php echo $color; ?>"><div onclick="cangeBus('calendar4', <?php echo ($i + 1); ?>, <?php echo $d; ?>);"><?php echo $mday; ?></div>
				<input name="business_days[<?php echo $the_4th_monthyy; ?>][<?php echo $the_4th_monthmm; ?>][<?php echo $mday; ?>]" id="calendar4_<?php echo ($i+1); ?>_<?php echo $d; ?>" type="hidden" value="<?php echo $business; ?>"></td>
		<?php } else { ?>
				<td>&nbsp;</td>
		<?php } ?>
	<?php endfor; ?>
			</tr>
<?php endfor; ?>
		</table>
		</td>
		<td>&nbsp;</td>
	</tr>
<!--5th-->
	<tr>
	    <th><?php echo sprintf(__('%2$s/%1$s', 'usces'),$the_5th_monthyy,$the_5th_monthmm); ?></th>
	    <td>
		<table cellspacing="0" id="calendar5" class="calendar">
			<tr>
				<th class="cal"><div onclick="cangeWday5('calendar5', '0');"><font color="#FF3300"><?php _e('Sun', 'usces'); ?></font></div></th>
				<th class="cal"><div onclick="cangeWday5('calendar5', '1');"><?php _e('Mon', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday5('calendar5', '2');"><?php _e('Tue', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday5('calendar5', '3');"><?php _e('Wed', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday5('calendar5', '4');"><?php _e('Thu', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday5('calendar5', '5');"><?php _e('Fri', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday5('calendar5', '6');"><?php _e('Sat', 'usces'); ?></div></th>
			</tr>
<?php for ($i = 0; $i < $cal5->getRow(); $i++) : ?>
			<tr>
	<?php for ($d = 0; $d <= 6; $d++) : 
			$mday = $cal5->getDateText($i, $d);
			if ($mday != "") {
				$business = isset( $this->options['business_days'][$the_5th_monthyy][$the_5th_monthmm][$mday]) ? $this->options['business_days'][$the_5th_monthyy][$the_5th_monthmm][$mday] : 1;
				$color = ($business == 1) ? '#DFFFDD' : '#FFAA55;color:#FFFFFF;font-weight:bold;'; ?>
				<td class="cal" style="background-color:<?php echo $color; ?>"><div onclick="cangeBus('calendar5', <?php echo ($i + 1); ?>, <?php echo $d; ?>);"><?php echo $mday; ?></div>
				<input name="business_days[<?php echo $the_5th_monthyy; ?>][<?php echo $the_5th_monthmm; ?>][<?php echo $mday; ?>]" id="calendar5_<?php echo ($i+1); ?>_<?php echo $d; ?>" type="hidden" value="<?php echo $business; ?>"></td>
		<?php } else { ?>
				<td>&nbsp;</td>
		<?php } ?>
	<?php endfor; ?>
			</tr>
<?php endfor; ?>
		</table>
		</td>
		<td>&nbsp;</td>
	</tr>
<!--6th-->
	<tr>
	    <th><?php echo sprintf(__('%2$s/%1$s', 'usces'),$the_6th_monthyy,$the_6th_monthmm); ?></th>
	    <td>
		<table cellspacing="0" id="calendar6" class="calendar">
			<tr>
				<th class="cal"><div onclick="cangeWday6('calendar6', '0');"><font color="#FF3300"><?php _e('Sun', 'usces'); ?></font></div></th>
				<th class="cal"><div onclick="cangeWday6('calendar6', '1');"><?php _e('Mon', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday6('calendar6', '2');"><?php _e('Tue', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday6('calendar6', '3');"><?php _e('Wed', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday6('calendar6', '4');"><?php _e('Thu', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday6('calendar6', '5');"><?php _e('Fri', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday6('calendar6', '6');"><?php _e('Sat', 'usces'); ?></div></th>
			</tr>
<?php for ($i = 0; $i < $cal6->getRow(); $i++) : ?>
			<tr>
	<?php for ($d = 0; $d <= 6; $d++) : 
			$mday = $cal6->getDateText($i, $d);
			if ($mday != "") {
				$business = isset( $this->options['business_days'][$the_6th_monthyy][$the_6th_monthmm][$mday]) ? $this->options['business_days'][$the_6th_monthyy][$the_6th_monthmm][$mday] : 1;
				$color = ($business == 1) ? '#DFFFDD' : '#FFAA55;color:#FFFFFF;font-weight:bold;'; ?>
				<td class="cal" style="background-color:<?php echo $color; ?>"><div onclick="cangeBus('calendar6', <?php echo ($i + 1); ?>, <?php echo $d; ?>);"><?php echo $mday; ?></div>
				<input name="business_days[<?php echo $the_6th_monthyy; ?>][<?php echo $the_6th_monthmm; ?>][<?php echo $mday; ?>]" id="calendar6_<?php echo ($i+1); ?>_<?php echo $d; ?>" type="hidden" value="<?php echo $business; ?>"></td>
		<?php } else { ?>
				<td>&nbsp;</td>
		<?php } ?>
	<?php endfor; ?>
			</tr>
<?php endfor; ?>
		</table>
		</td>
		<td>&nbsp;</td>
	</tr>
<!--7th-->
	<tr>
	    <th><?php echo sprintf(__('%2$s/%1$s', 'usces'),$the_7th_monthyy,$the_7th_monthmm); ?></th>
	    <td>
		<table cellspacing="0" id="calendar7" class="calendar">
			<tr>
				<th class="cal"><div onclick="cangeWday7('calendar7', '0');"><font color="#FF3300"><?php _e('Sun', 'usces'); ?></font></div></th>
				<th class="cal"><div onclick="cangeWday7('calendar7', '1');"><?php _e('Mon', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday7('calendar7', '2');"><?php _e('Tue', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday7('calendar7', '3');"><?php _e('Wed', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday7('calendar7', '4');"><?php _e('Thu', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday7('calendar7', '5');"><?php _e('Fri', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday7('calendar7', '6');"><?php _e('Sat', 'usces'); ?></div></th>
			</tr>
<?php for ($i = 0; $i < $cal7->getRow(); $i++) : ?>
			<tr>
	<?php for ($d = 0; $d <= 6; $d++) : 
			$mday = $cal7->getDateText($i, $d);
			if ($mday != "") {
				$business = isset( $this->options['business_days'][$the_7th_monthyy][$the_7th_monthmm][$mday]) ? $this->options['business_days'][$the_7th_monthyy][$the_7th_monthmm][$mday] : 1;
				$color = ($business == 1) ? '#DFFFDD' : '#FFAA55;color:#FFFFFF;font-weight:bold;'; ?>
				<td class="cal" style="background-color:<?php echo $color; ?>"><div onclick="cangeBus('calendar7', <?php echo ($i + 1); ?>, <?php echo $d; ?>);"><?php echo $mday; ?></div>
				<input name="business_days[<?php echo $the_7th_monthyy; ?>][<?php echo $the_7th_monthmm; ?>][<?php echo $mday; ?>]" id="calendar7_<?php echo ($i+1); ?>_<?php echo $d; ?>" type="hidden" value="<?php echo $business; ?>"></td>
		<?php } else { ?>
				<td>&nbsp;</td>
		<?php } ?>
	<?php endfor; ?>
			</tr>
<?php endfor; ?>
		</table>
		</td>
		<td>&nbsp;</td>
	</tr>
<!--8th-->
	<tr>
	    <th><?php echo sprintf(__('%2$s/%1$s', 'usces'),$the_8th_monthyy,$the_8th_monthmm); ?></th>
	    <td>
		<table cellspacing="0" id="calendar8" class="calendar">
			<tr>
				<th class="cal"><div onclick="cangeWday8('calendar8', '0');"><font color="#FF3300"><?php _e('Sun', 'usces'); ?></font></div></th>
				<th class="cal"><div onclick="cangeWday8('calendar8', '1');"><?php _e('Mon', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday8('calendar8', '2');"><?php _e('Tue', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday8('calendar8', '3');"><?php _e('Wed', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday8('calendar8', '4');"><?php _e('Thu', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday8('calendar8', '5');"><?php _e('Fri', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday8('calendar8', '6');"><?php _e('Sat', 'usces'); ?></div></th>
			</tr>
<?php for ($i = 0; $i < $cal8->getRow(); $i++) : ?>
			<tr>
	<?php for ($d = 0; $d <= 6; $d++) : 
			$mday = $cal8->getDateText($i, $d);
			if ($mday != "") {
				$business = isset( $this->options['business_days'][$the_8th_monthyy][$the_8th_monthmm][$mday]) ? $this->options['business_days'][$the_8th_monthyy][$the_8th_monthmm][$mday] : 1;
				$color = ($business == 1) ? '#DFFFDD' : '#FFAA55;color:#FFFFFF;font-weight:bold;'; ?>
				<td class="cal" style="background-color:<?php echo $color; ?>"><div onclick="cangeBus('calendar8', <?php echo ($i + 1); ?>, <?php echo $d; ?>);"><?php echo $mday; ?></div>
				<input name="business_days[<?php echo $the_8th_monthyy; ?>][<?php echo $the_8th_monthmm; ?>][<?php echo $mday; ?>]" id="calendar8_<?php echo ($i+1); ?>_<?php echo $d; ?>" type="hidden" value="<?php echo $business; ?>"></td>
		<?php } else { ?>
				<td>&nbsp;</td>
		<?php } ?>
	<?php endfor; ?>
			</tr>
<?php endfor; ?>
		</table>
		</td>
		<td>&nbsp;</td>
	</tr>
<!--9th-->
	<tr>
	    <th><?php echo sprintf(__('%2$s/%1$s', 'usces'),$the_9th_monthyy,$the_9th_monthmm); ?></th>
	    <td>
		<table cellspacing="0" id="calendar9" class="calendar">
			<tr>
				<th class="cal"><div onclick="cangeWday9('calendar9', '0');"><font color="#FF3300"><?php _e('Sun', 'usces'); ?></font></div></th>
				<th class="cal"><div onclick="cangeWday9('calendar9', '1');"><?php _e('Mon', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday9('calendar9', '2');"><?php _e('Tue', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday9('calendar9', '3');"><?php _e('Wed', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday9('calendar9', '4');"><?php _e('Thu', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday9('calendar9', '5');"><?php _e('Fri', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday9('calendar9', '6');"><?php _e('Sat', 'usces'); ?></div></th>
			</tr>
<?php for ($i = 0; $i < $cal9->getRow(); $i++) : ?>
			<tr>
	<?php for ($d = 0; $d <= 6; $d++) : 
			$mday = $cal9->getDateText($i, $d);
			if ($mday != "") {
				$business = isset( $this->options['business_days'][$the_9th_monthyy][$the_9th_monthmm][$mday]) ? $this->options['business_days'][$the_9th_monthyy][$the_9th_monthmm][$mday] : 1;
				$color = ($business == 1) ? '#DFFFDD' : '#FFAA55;color:#FFFFFF;font-weight:bold;'; ?>
				<td class="cal" style="background-color:<?php echo $color; ?>"><div onclick="cangeBus('calendar9', <?php echo ($i + 1); ?>, <?php echo $d; ?>);"><?php echo $mday; ?></div>
				<input name="business_days[<?php echo $the_9th_monthyy; ?>][<?php echo $the_9th_monthmm; ?>][<?php echo $mday; ?>]" id="calendar9_<?php echo ($i+1); ?>_<?php echo $d; ?>" type="hidden" value="<?php echo $business; ?>"></td>
		<?php } else { ?>
				<td>&nbsp;</td>
		<?php } ?>
	<?php endfor; ?>
			</tr>
<?php endfor; ?>
		</table>
		</td>
		<td>&nbsp;</td>
	</tr>
<!--10th-->
	<tr>
	    <th><?php echo sprintf(__('%2$s/%1$s', 'usces'),$the_10th_monthyy,$the_10th_monthmm); ?></th>
	    <td>
		<table cellspacing="0" id="calendar10" class="calendar">
			<tr>
				<th class="cal"><div onclick="cangeWday10('calendar10', '0');"><font color="#FF3300"><?php _e('Sun', 'usces'); ?></font></div></th>
				<th class="cal"><div onclick="cangeWday10('calendar10', '1');"><?php _e('Mon', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday10('calendar10', '2');"><?php _e('Tue', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday10('calendar10', '3');"><?php _e('Wed', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday10('calendar10', '4');"><?php _e('Thu', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday10('calendar10', '5');"><?php _e('Fri', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday10('calendar10', '6');"><?php _e('Sat', 'usces'); ?></div></th>
			</tr>
<?php for ($i = 0; $i < $cal10->getRow(); $i++) : ?>
			<tr>
	<?php for ($d = 0; $d <= 6; $d++) : 
			$mday = $cal10->getDateText($i, $d);
			if ($mday != "") {
				$business = isset( $this->options['business_days'][$the_10th_monthyy][$the_10th_monthmm][$mday]) ? $this->options['business_days'][$the_10th_monthyy][$the_10th_monthmm][$mday] : 1;				
				$color = ($business == 1) ? '#DFFFDD' : '#FFAA55;color:#FFFFFF;font-weight:bold;'; ?>
				<td class="cal" style="background-color:<?php echo $color; ?>"><div onclick="cangeBus('calendar10', <?php echo ($i + 1); ?>, <?php echo $d; ?>);"><?php echo $mday; ?></div>
				<input name="business_days[<?php echo $the_10th_monthyy; ?>][<?php echo $the_10th_monthmm; ?>][<?php echo $mday; ?>]" id="calendar10_<?php echo ($i+1); ?>_<?php echo $d; ?>" type="hidden" value="<?php echo $business; ?>"></td>
		<?php } else { ?>
				<td>&nbsp;</td>
		<?php } ?>
	<?php endfor; ?>
			</tr>
<?php endfor; ?>
		</table>
		</td>
		<td>&nbsp;</td>
	</tr>
<!--11th-->
	<tr>
	    <th><?php echo sprintf(__('%2$s/%1$s', 'usces'),$the_11th_monthyy,$the_11th_monthmm); ?></th>
	    <td>
		<table cellspacing="0" id="calendar11" class="calendar">
			<tr>
				<th class="cal"><div onclick="cangeWday11('calendar11', '0');"><font color="#FF3300"><?php _e('Sun', 'usces'); ?></font></div></th>
				<th class="cal"><div onclick="cangeWday11('calendar11', '1');"><?php _e('Mon', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday11('calendar11', '2');"><?php _e('Tue', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday11('calendar11', '3');"><?php _e('Wed', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday11('calendar11', '4');"><?php _e('Thu', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday11('calendar11', '5');"><?php _e('Fri', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday11('calendar11', '6');"><?php _e('Sat', 'usces'); ?></div></th>
			</tr>
<?php for ($i = 0; $i < $cal11->getRow(); $i++) : ?>
			<tr>
	<?php for ($d = 0; $d <= 6; $d++) : 
			$mday = $cal11->getDateText($i, $d);
			if ($mday != "") {
				$business = isset( $this->options['business_days'][$the_11th_monthyy][$the_11th_monthmm][$mday]) ? $this->options['business_days'][$the_11th_monthyy][$the_11th_monthmm][$mday] : 1;				
				$color = ($business == 1) ? '#DFFFDD' : '#FFAA55;color:#FFFFFF;font-weight:bold;'; ?>
				<td class="cal" style="background-color:<?php echo $color; ?>"><div onclick="cangeBus('calendar11', <?php echo ($i + 1); ?>, <?php echo $d; ?>);"><?php echo $mday; ?></div>
				<input name="business_days[<?php echo $the_11th_monthyy; ?>][<?php echo $the_11th_monthmm; ?>][<?php echo $mday; ?>]" id="calendar11_<?php echo ($i+1); ?>_<?php echo $d; ?>" type="hidden" value="<?php echo $business; ?>"></td>
		<?php } else { ?>
				<td>&nbsp;</td>
		<?php } ?>
	<?php endfor; ?>
			</tr>
<?php endfor; ?>
		</table>
		</td>
		<td>&nbsp;</td>
	</tr>
<!--12th-->
	<tr>
	    <th><?php echo sprintf(__('%2$s/%1$s', 'usces'),$the_12th_monthyy,$the_12th_monthmm); ?></th>
	    <td>
		<table cellspacing="0" id="calendar12" class="calendar">
			<tr>
				<th class="cal"><div onclick="cangeWday12('calendar12', '0');"><font color="#FF3300"><?php _e('Sun', 'usces'); ?></font></div></th>
				<th class="cal"><div onclick="cangeWday12('calendar12', '1');"><?php _e('Mon', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday12('calendar12', '2');"><?php _e('Tue', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday12('calendar12', '3');"><?php _e('Wed', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday12('calendar12', '4');"><?php _e('Thu', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday12('calendar12', '5');"><?php _e('Fri', 'usces'); ?></div></th>
				<th class="cal"><div onclick="cangeWday12('calendar12', '6');"><?php _e('Sat', 'usces'); ?></div></th>
			</tr>
<?php for ($i = 0; $i < $cal12->getRow(); $i++) : ?>
			<tr>
	<?php for ($d = 0; $d <= 6; $d++) : 
			$mday = $cal12->getDateText($i, $d);
			if ($mday != "") {
				$business = isset( $this->options['business_days'][$the_12th_monthyy][$the_12th_monthmm][$mday]) ? $this->options['business_days'][$the_12th_monthyy][$the_12th_monthmm][$mday] : 1;
				$color = ($business == 1) ? '#DFFFDD' : '#FFAA55;color:#FFFFFF;font-weight:bold;'; ?>
				<td class="cal" style="background-color:<?php echo $color; ?>"><div onclick="cangeBus('calendar12', <?php echo ($i + 1); ?>, <?php echo $d; ?>);"><?php echo $mday; ?></div>
				<input name="business_days[<?php echo $the_12th_monthyy; ?>][<?php echo $the_12th_monthmm; ?>][<?php echo $mday; ?>]" id="calendar12_<?php echo ($i+1); ?>_<?php echo $d; ?>" type="hidden" value="<?php echo $business; ?>"></td>
		<?php } else { ?>
				<td>&nbsp;</td>
		<?php } ?>
	<?php endfor; ?>
			</tr>
<?php endfor; ?>
		</table>
		</td>
		<td>&nbsp;</td>
	</tr>
<!--20131206_kitamu_end▲-->
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_shipping_charge" class="explanation"></div>
</div>
</div><!--postbox-->
<?php do_action( 'usces_action_admin_schedule3' ); ?>


</div><!--poststuff-->
<input name="usces_option_update" type="submit" class="button" value="<?php _e('change decision','usces'); ?>" />
<?php wp_nonce_field( 'admin_schedule', 'wc_nonce' ); ?>
</form>
</div><!--usces_admin-->
</div><!--wrap-->