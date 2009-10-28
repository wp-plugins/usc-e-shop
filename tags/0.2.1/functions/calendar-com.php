<?php
function getToday() {
	// GMT+0900
	$time = time() + (3600 * 9);
	$hour = gmdate("H", $time);
	$minute = gmdate("i", $time);
	$second = gmdate("s", $time);
	$month = gmdate("n", $time);
	$day = gmdate("j", $time);
	$year = gmdate("Y", $time);
	$timestamp = mktime($hour, $minute, $second, $month, $day, $year);

	$dateAry = getdate($timestamp);
	return array($dateAry[year], $dateAry[mon], $dateAry[mday]);
}

function getWeek($year, $month, $day) {
	$dateAry = getdate(mktime(0, 0, 0, $month, $day, $year));
	return $dateAry[wday];
}

function getLastDay($year, $month) {
	list($nextyy, $nextmm) = getNextMonth($year, $month);
	$dateAry = getdate(mktime(0, 0, 0, $nextmm, 0, $nextyy));
	//return array($dateAry[year], $dateAry[mon], $dateAry[mday]);
	return $dateAry[mday];
}

function isToday($year, $month, $day) {
	list($todayyy, $todaymm, $todaydd) = getToday();
	if ($year == $todayyy && $month == $todaymm && $day == $todaydd) {
		return true;
	}
	return false;
}

function getNextDay($year, $month, $day) {
	$dateAry = getdate(mktime(0, 0, 0, $month, $day + 1, $year));
	return array($dateAry[year], $dateAry[mon], $dateAry[mday]);
}

function getPrevDay($year, $month, $day) {
	$dateAry = getdate(mktime(0, 0, 0, $month, $day - 1, $year));
	return array($dateAry[year], $dateAry[mon], $dateAry[mday]);
}

function getNextMonth($year, $month) {
	$dateAry = getdate(mktime(0, 0, 0, $month + 1, 1, $year));
	return array($dateAry[year], $dateAry[mon]);
}

function getPrevMonth($year, $month) {
	$dateAry = getdate(mktime(0, 0, 0, $month - 1, 1, $year));
	return array($dateAry[year], $dateAry[mon]);
}

function getAfterMonth($year, $month, $day, $n) {
	$dateAry = getdate(mktime(0, 0, 0, $month + $n, $day, $year));
	return array($dateAry[year], $dateAry[mon], $dateAry[mday]);
}

function getBeforeMonth($year, $month, $day, $n) {
	$dateAry = getdate(mktime(0, 0, 0, $month - $n, $day, $year));
	return array($dateAry[year], $dateAry[mon], $dateAry[mday]);
}
?>
