<?php
	
$unitid = file_get_contents(__DIR__ . '/mymeter');
$type= "idm";

exec("/root/gocode/bin/rtlamr -msgtype={$type} --format=json --filterid={$unitid} --single=true",$output);
$arr = json_decode(implode("\n",$output),true);
$meter_reading = $arr['Message']['LastConsumptionCount'];

if ($meter_reading > 1) {
	$r = new \reading($meter_reading);
}

class reading {

	private $db = null;
	private $reading = 0;

	private function getDb() {
		$db = new SQLite3(dirname(__FILE__) . '/consumption.sqlite3');

		$db->query("CREATE TABLE IF NOT EXISTS byhour (id INTEGER PRIMARY KEY ASC, ymd integer, hour tinyint, reading bigint,last_difference integer,UNIQUE(ymd,hour))");
		$db->query("CREATE TABLE IF NOT EXISTS byday (id INTEGER PRIMARY KEY ASC, ymd integer, reading bigint,last_difference integer,UNIQUE(ymd))");
		$db->query("CREATE TABLE IF NOT EXISTS bymonth (id INTEGER PRIMARY KEY ASC, ym integer, reading bigint,last_difference integer,UNIQUE(ym))");
		$db->query("CREATE TABLE IF NOT EXISTS byweek (id INTEGER PRIMARY KEY ASC, year integer, week tinyint, reading bigint,last_difference integer,UNIQUE(year,week))");

		return $db;
	}

	private function getLastReading($table) {
		$ld_res = $this->db->query("select * from {$table} order by id desc limit 1");
		if ($ld_row = $ld_res->fetchArray()) {
			return $this->reading-$ld_row['reading'];
		}
		return 0;
	}

	public function __construct($reading) {

		$this->db = $this->getDb();
		$this->reading = $reading;

		$ym = date("Ym");
		$year = date("Y");
		$week = date("W");
		$ymd = date("Ymd");
		$hour = date("H");
		
		
		//By Hour
		$res = $this->db->query("select * from byhour where ymd={$ymd} and hour={$hour}");

		if (!$row = $res->fetchArray()) {
			$last_difference = $this->getLastReading("byhour");
			$ins=$this->db->prepare("insert into byhour (ymd,hour,reading,last_difference) values (:ymd,:hour,:reading,:last_difference)");
			$ins->bindValue(':ymd', date("Ymd"), SQLITE3_INTEGER);
			$ins->bindValue(':hour', date("G"), SQLITE3_INTEGER);
			$ins->bindValue(':reading', $this->reading, SQLITE3_INTEGER);
			$ins->bindValue(':last_difference', $last_difference, SQLITE3_INTEGER);
			@$ins->execute();
		}
		//End By Hour


		//By Day
		$res = $this->db->query("select * from byday where ymd={$ymd}");

		if (!$row = $res->fetchArray()) {
			$last_difference = $this->getLastReading("byday");
			$ins=$this->db->prepare("insert into byday (ymd,reading,last_difference) values (:ymd,:reading,:last_difference)");
			$ins->bindValue(':ymd', $ymd, SQLITE3_INTEGER);
			$ins->bindValue(':reading', $this->reading, SQLITE3_INTEGER);
			$ins->bindValue(':last_difference', $last_difference, SQLITE3_INTEGER);
			@$ins->execute();
		}
		//End Day
		

		//By Week
		$res = $this->db->query("select * from byweek where year={$year} and week={$week}");

		if (!$row = $res->fetchArray()) {
			$last_difference = $this->getLastReading("byweek");
			$ins=$this->db->prepare("insert into byweek (year,week,reading,last_difference) values (:year,:week,:reading,:last_difference)");
			$ins->bindValue(':year', $year, SQLITE3_INTEGER);
			$ins->bindValue(':week', $week, SQLITE3_INTEGER);
			$ins->bindValue(':reading', $this->reading, SQLITE3_INTEGER);
			$ins->bindValue(':last_difference', $last_difference, SQLITE3_INTEGER);
			@$ins->execute();
		}
		//End Week

		
		//By Month
		$res = $this->db->query("select * from bymonth where ym={$ym}");
		
		if (!$row = $res->fetchArray()) {
			$last_difference = $this->getLastReading("bymonth");
			$ins=$this->db->prepare("insert into bymonth (ym,reading,last_difference) values (:ym,:reading,:last_difference)");
			$ins->bindValue(':ym', $ym, SQLITE3_INTEGER);
			$ins->bindValue(':reading', $this->reading, SQLITE3_INTEGER);
			$ins->bindValue(':last_difference', $last_difference, SQLITE3_INTEGER);
			@$ins->execute();
		}
		//End Month
		
		
		//Backup
		for ($i = 48; $i > 0; $i--) {
			$a = $i - 1;
			rename("/home/pi/powermon/backups/consumption.sqlite3.{$a}", "/home/pi/powermon/backups/consumption.sqlite3.{$i}");
		}

		rename("/home/pi/powermon/backups/consumption.sqlite3","/home/pi/powermon/backups/consumption.sqlite3.0");

		copy(dirname(__FILE__) . '/consumption.sqlite3',"/home/pi/powermon/backups/consumption.sqlite3");
		// End Backup
	}
}

?>