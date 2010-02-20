<?php
require_once(USCES_PLUGIN_DIR . '/classes/calendar.class.php');

//当月
list($todayyy, $todaymm, $todaydd) = getToday();	// 今日
$cal1 = new calendarData();
$cal1->setToday($todayyy, $todaymm, $todaydd);
$cal1->setCalendarData();
//翌月
list($nextyy, $nextmm, $nextdd) = getAfterMonth($todayyy, $todaymm, 1, 1);
$cal2 = new calendarData();
$cal2->setToday($nextyy, $nextmm, $nextdd);
$cal2->setCalendarData();
//翌々月
list($lateryy, $latermm, $laterdd) = getAfterMonth($todayyy, $todaymm, 1, 2);
$cal3 = new calendarData();
$cal3->setToday($lateryy, $latermm, $laterdd);
$cal3->setCalendarData();



$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';
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
	} else {
		e.value = '0';
		v.style.backgroundColor = '#FFECCE';
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
		} else {
			e.value = '0';
			v.style.backgroundColor = '#FFECCE';
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
		} else {
			e.value = '0';
			v.style.backgroundColor = '#FFECCE';
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
		} else {
			e.value = '0';
			v.style.backgroundColor = '#FFECCE';
		}
	}
<?php endfor; ?>
}

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

<div class="postbox">
<h3 class="hndle"><span><?php _e('Campaign Schedule','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_campaign_schedule');">（<?php _e('explanation', 'usces'); ?>）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><?php _e('starting time','usces'); ?></th>
	    <td><select name="campaign_schedule[start][year]">
	    		<option value="0"<?php if($this->options['campaign_schedule']['start']['year'] == 0) echo ' selected="selected"'; ?>></option>
	    		<option value="<?php echo date('Y'); ?>"<?php if($this->options['campaign_schedule']['start']['year'] == date('Y')) echo ' selected="selected"'; ?>><?php echo date('Y'); ?></option>
	    		<option value="<?php echo date('Y')+1; ?>"<?php if($this->options['campaign_schedule']['start']['year'] == (date('Y')+1)) echo ' selected="selected"'; ?>><?php echo date('Y')+1; ?></option>
		</select></td>
		<td><?php _e('year','usces'); ?></td>
	    <td><select name="campaign_schedule[start][month]">
	    		<option value="0"<?php if($this->options['campaign_schedule']['start']['month'] == 0) echo ' selected="selected"'; ?>></option>
<?php for($i=1; $i<13; $i++) : ?>
	    		<option value="<?php echo $i; ?>"<?php if($this->options['campaign_schedule']['start']['month'] == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
<?php endfor; ?>
		</select></td>
		<td><?php _e('month','usces'); ?></td>
	    <td><select name="campaign_schedule[start][day]">
	    		<option value="0"<?php if($this->options['campaign_schedule']['start']['day'] == 0) echo ' selected="selected"'; ?>></option>
<?php for($i=1; $i<32; $i++) : ?>
	    		<option value="<?php echo $i; ?>"<?php if($this->options['campaign_schedule']['start']['day'] == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
<?php endfor; ?>
		</select></td>
		<td><?php _e('day','usces'); ?></td>
	    <td><select name="campaign_schedule[start][hour]">
<?php for($i=0; $i<24; $i++) : ?>
	    		<option value="<?php echo $i; ?>"<?php if($this->options['campaign_schedule']['start']['hour'] == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
<?php endfor; ?>
		</select></td>
		<td><?php _e('hour','usces'); ?></td>
	    <td><select name="campaign_schedule[start][min]">
<?php for($i=0; $i<12; $i++) : ?>
	    		<option value="<?php echo $i*5; ?>"<?php if($this->options['campaign_schedule']['min']['hour'] == ($i*5)) echo ' selected="selected"'; ?>><?php echo $i*5; ?></option>
<?php endfor; ?>
		</select></td>
		<td><?php _e('min','usces'); ?></td>
	</tr>
	<tr>
	    <th><?php _e('date and time of termination','usces'); ?></th>
	    <td><select name="campaign_schedule[end][year]">
	    		<option value="0"<?php if($this->options['campaign_schedule']['end']['year'] == 0) echo ' selected="selected"'; ?>></option>
	    		<option value="<?php echo date('Y'); ?>"<?php if($this->options['campaign_schedule']['end']['year'] == date('Y')) echo ' selected="selected"'; ?>><?php echo date('Y'); ?></option>
	    		<option value="<?php echo date('Y')+1; ?>"<?php if($this->options['campaign_schedule']['end']['year'] == (date('Y')+1)) echo ' selected="selected"'; ?>><?php echo date('Y')+1; ?></option>
		</select></td>
		<td><?php _e('year','usces'); ?></td>
	    <td><select name="campaign_schedule[end][month]">
	    		<option value="0"<?php if($this->options['campaign_schedule']['end']['month'] == 0) echo ' selected="selected"'; ?>></option>
<?php for($i=1; $i<13; $i++) : ?>
	    		<option value="<?php echo $i; ?>"<?php if($this->options['campaign_schedule']['end']['month'] == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
<?php endfor; ?>
		</select></td>
		<td><?php _e('month','usces'); ?></td>
	    <td><select name="campaign_schedule[end][day]">
	    		<option value="0"<?php if($this->options['campaign_schedule']['end']['day'] == 0) echo ' selected="selected"'; ?>></option>
<?php for($i=1; $i<32; $i++) : ?>
	    		<option value="<?php echo $i; ?>"<?php if($this->options['campaign_schedule']['end']['day'] == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
<?php endfor; ?>
		</select></td>
		<td><?php _e('day','usces'); ?></td>
	    <td><select name="campaign_schedule[end][hour]">
<?php for($i=0; $i<24; $i++) : ?>
	    		<option value="<?php echo $i; ?>"<?php if($this->options['campaign_schedule']['end']['hour'] == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
<?php endfor; ?>
		</select></td>
		<td><?php _e('hour','usces'); ?></td>
	    <td><select name="campaign_schedule[end][min]">
<?php for($i=0; $i<12; $i++) : ?>
	    		<option value="<?php echo $i*5; ?>"<?php if($this->options['campaign_schedule']['min']['hour'] == ($i*5)) echo ' selected="selected"'; ?>><?php echo $i*5; ?></option>
<?php endfor; ?>
		</select></td>
		<td><?php _e('min','usces'); ?></td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_campaign_schedule" class="explanation"><?php _e('reserve the period of campaign.', 'usces'); ?></div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('Business Calendar', 'usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_charge');">（<?php _e('explanation', 'usces'); ?>）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th><?php _e('current month', 'usces'); ?><br /><?php echo sprintf(__('%2$s/%1$s', 'usces'),$todayyy,$todaymm); ?></th>
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
				$color = ($business == 1) ? "#DFFFDD" : "#FFECCE"; ?>
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
		<td><span class="business_days_exp_box" style="background-color:#DFFFDD">　　</span><?php _e('Working day', 'usces'); ?><br /><span class="business_days_exp_box" style="background-color:#FFECCE">　　</span><?php _e('Holiday for Shipping Operations', 'usces'); ?></td>
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
				$color = ($business == 1) ? "#DFFFDD" : "#FFECCE"; ?>
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
				$color = ($business == 1) ? "#DFFFDD" : "#FFECCE"; ?>
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
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_shipping_charge" class="explanation"></div>
</div>
</div><!--postbox-->


</div><!--poststuff-->
<input name="usces_option_update" type="submit" class="button" value="<?php _e('change decision','usces'); ?>" />
</form>
</div><!--usces_admin-->
</div><!--wrap-->