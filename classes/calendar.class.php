<?php
class calendarData {

	var $_year;
	var $_month;
	var $_day;
	var $_row;

	var $_date;
	var $_datetext;

	function calendarData() {
		$this->_row = 0;
	}

	function setToday($year, $month, $day) {
		$this->_year = $year;
		$this->_month = $month;
		$this->_day = $day;
	}

	function getDate($row, $d)		{return $this->_date[$row][$d];}
	function getDateText($row, $d)	{return $this->_datetext[$row][$d];}
	function getRow()				{return $this->_row;}

	function setCalendarData() {

		$day = 1;	// 当月開始日に設定
		$firstw = getWeek($this->_year, $this->_month, $day);		// 当月開始日の曜日
		$lastday = getLastDay($this->_year, $this->_month);			// 当月最終日
		$lastw = getWeek($this->_year, $this->_month, $lastday);	// 当月最終日の曜日

		// 1週目
		for ($d = 0; $d <= 6; $d++) {
			if ($firstw == $d) {
				$this->_date[0][$d] = sprintf("%04d-%02d-%02d", $this->_year, $this->_month, $day);
				$this->_datetext[0][$d] = $day;
			} elseif ($firstw < $d) {
				list($this->_year, $this->_month, $day) = getNextDay($this->_year, $this->_month, $day);
				$this->_date[0][$d] = sprintf("%04d-%02d-%02d", $this->_year, $this->_month, $day);
				$this->_datetext[0][$d] = $day;
			} else {
				$this->_date[0][$d] = "";
				$this->_datetext[0][$d] = "";
			}
		}
		// 2～4週目
		for ($d = 0; $d <= 6; $d++) {
			list($this->_year, $this->_month, $day) = getNextDay($this->_year, $this->_month, $day);
			$this->_date[1][$d] = sprintf("%04d-%02d-%02d", $this->_year, $this->_month, $day);
			$this->_datetext[1][$d] = $day;
		}
		for ($d = 0; $d <= 6; $d++) {
			list($this->_year, $this->_month, $day) = getNextDay($this->_year, $this->_month, $day);
			$this->_date[2][$d] = sprintf("%04d-%02d-%02d", $this->_year, $this->_month, $day);
			$this->_datetext[2][$d] = $day;
		}
		for ($d = 0; $d <= 6; $d++) {
			list($this->_year, $this->_month, $day) = getNextDay($this->_year, $this->_month, $day);
			$this->_date[3][$d] = sprintf("%04d-%02d-%02d", $this->_year, $this->_month, $day);
			$this->_datetext[3][$d] = $day;
		}
		// 5週目
		for ($d = 0; $d <= 6; $d++) {
			if ($lastday == $day) {
				break;
			} else {
				list($this->_year, $this->_month, $day) = getNextDay($this->_year, $this->_month, $day);
				$this->_date[4][$d] = sprintf("%04d-%02d-%02d", $this->_year, $this->_month, $day);
				$this->_datetext[4][$d] = $day;
			}
		}
		if ( 0 < $d && $d <= 6) {
			while ($d <= 6) {
				$this->_date[4][$d] = "";
				$this->_datetext[4][$d] = "";
				$d++;
			}
		} elseif( $d !== 0 ) {
			// 6週目
			for ($d = 0; $d <= 6; $d++) {
				if ($lastday == $day) {
					break;
				} else {
					list($this->_year, $this->_month, $day) = getNextDay($this->_year, $this->_month, $day);
					$this->_date[5][$d] = sprintf("%04d-%02d-%02d", $this->_year, $this->_month, $day);
					$this->_datetext[5][$d] = $day;
				}
			}
			if ($d > 0) {
				while ($d <= 6) {
					$this->_date[5][$d] = "";
					$this->_datetext[5][$d] = "";
					$d++;
				}
			}
		}

		$this->_row = count($this->_date);
	}
}
?>
