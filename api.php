<?php
$usage = new usage();

if (!empty($_POST) || (!empty($_GET) && $_GET['action'] == 'bydaynexosis') ) {
	if ($_POST['action'] == 'byday') {
		echo json_encode($usage->getByDay());
	}
	if ((isset($_POST['action']) && $_POST['action'] == 'bydaynexosis') || (isset($_GET['action']) && $_GET['action'] == 'bydaynexosis')) {
		$ld_data = $usage->getByDay(31);
		
		$data = array();
			
		foreach ($ld_data as $day => $value) {
			$row = array();
			
			$row["day"] = $day;
			$row["usage"] = $value;
			
			$data[] = $row;
		}
		
		$json = array(
			"columns" => 
				array(
					"day" =>
						array("dataType" => "date", "role" => "timestamp"),
					"usage" =>
						array("dataType" => "numeric", "role" => "target"),
				),	
			"data" => array_values($data)
		);
		
		echo json_encode($json);
	}
	if ($_POST['action'] == 'bymonth') {
		echo json_encode($usage->getByMonth());
	}
	if ($_POST['action'] == 'byweek') {
		echo json_encode($usage->getByWeek());
	}
	if ($_POST['action'] == 'byhour') {
		$byHourDay = isset($_POST['byHourDay']) && strlen($_POST['byHourDay']) > 0 ? urldecode($_POST['byHourDay']) : null;
		echo json_encode($usage->getByHour($byHourDay));
	}
}

class usage {
	private $db = null;

	public function __construct() {
		$this->db = new SQLite3("/home/pi/powermon/backups/consumption.sqlite3");
	}
	
	public function getByMonth($limit = 12) {
		$ret = [];

		$ld_res = $this->db->query("select * from bymonth order by ym desc limit {$limit}");
		while ($ld_data = $ld_res->fetchArray()) {
			$fdate = date("M Y", strtotime(($ld_data['ym'] . '01') - 1));
			
			##in decawatts, want kilo
			$ret[$fdate] = $ld_data['last_difference']/100;
		}
		
		return $ret;
	}
	
	public function getByWeek($limit = 5) {
		$ret = [];

		$ld_res = $this->db->query("select * from byweek order by year desc, week desc limit {$limit}");
		while ($ld_data = $ld_res->fetchArray()) {
			##in decawatts, want kilo
			$ret[$ld_data['week']] = $ld_data['last_difference']/100;
		}
		
		return $ret;
	}

	public function getByDay($limit = 10) {
		$ret = [];
		
		##Add in today's usage since it hasn't been written yet
		$fdate = date("j M Y");

		$tot = 0;
		$byhour = $this->getByHour();
		foreach ($byhour as $usage) {
			$tot += $usage;
		}

		##Swap the watts back to kilo
		$ret[$fdate] = $tot/1000;
		
		$ld_res = $this->db->query("select * from byday order by ymd desc limit {$limit}");
		while ($ld_data = $ld_res->fetchArray()) {
			$fdate = date("j M Y",strtotime(($ld_data['ymd']-1)));

			##in decawatts, want kilo
			$ret[$fdate] = $ld_data['last_difference']/100;
		}

		return $ret;
	}

	public function getByHour($date = null) {
		##Pull today if nothing passed in
		if ($date == null) {
			$date = date("Ymd");
		} else {
			//format that guy
			$date = date("Ymd",strtotime($date));
		}

		##This addresses the problem where I am writing the previous hour's data to the current hour
		$tomm = $date+1;

		$ret = [];

		##Usage is marked current hour when it actually means last hour
		##so do some fanangling
		$ld_res = $this->db->query("select * from byhour where ((ymd={$date} and hour > 0) || (ymd={$tomm} and hour=0)) order by ymd,hour");
		while ($ld_data = $ld_res->fetchArray()) {
			$hour = $ld_data['hour'] - 1;
			if ($hour < 0) {
				$hour = 23;
			}
			//convert to watts
			$ret[$hour] = $ld_data['last_difference']*10;
		}

		##Fill in blanks spots with "0" in the final output so we have a full 24 hours worth
		for($i=0; $i < 24; $i++) {
			if (!isset($ret[$i])) {
				$ret[$i] = 0;
			}
		}
		
		ksort($ret);
		return $ret;
	}
}
?>