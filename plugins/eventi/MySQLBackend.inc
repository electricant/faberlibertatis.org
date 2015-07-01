<?php

require_once 'iEventsBackend.inc';
require_once 'config-eventi.php';

class MySQLBackend implements iEventsBackend {
	private $conn; // database connection
	
	function __construct() {
		$this->conn = new mysqli(MYSQL_SERVERNAME, MYSQL_USERNAME, 
			MYSQL_PASSWORD, 'faber_eventi');
		if ($this->conn->connect_error) {
			die("Failed connecting to the database!" . $this->conn->connect_error);
		}
	}

	public function getMostRecent($number = -1) {
		$query_str = "";
		$retArray;       // this array holds the requested values

		if ($number > 0) {
			$query_str = 'SELECT * FROM eventi ORDER BY date DESC' .
				' LIMIT ' . $number . ';';
		} else {
			$query_str = 'SELECT * FROM eventi ORDER BY date DESC;';
		}
		
		$result = $this->conn->query($query_str);
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$retArray[] = $row;
			}
		}
		return $retArray;
	}

	public function getNewer($fromDate) {

	}

        public function addEvent($date, $description, $url = ""){

	}

        public function removeEvent($date, $description){
	}
}


?>
