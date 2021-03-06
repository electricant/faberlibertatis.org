<?php
/*
 * Use MySQL to store the events.
 *
 * The database, named 'faber_eventi', stores all the data in a single table
 * ('eventi'). The table is created by using the following command:
 * CREATE TABLE `eventi` (
 * 	`id` int(11) NOT NULL AUTO_INCREMENT,
 *	`date` bigint(20) NOT NULL,
 *	`description` text NOT NULL,
 *	`url` varchar(255) DEFAULT NULL,
 *	PRIMARY KEY (`id`)
 * )
 *
 * Column description:
 * 	id          - Used as an identifier mainly when deleting events
 *	date        - Date stored as an unix timestamp in a 64bit integer
 *	              (64bits are chosen to avoid 'Year 2038 problem')
 *	description - Text description for the event
 *	url         - Url inside the website for an article describing the event
 */
require_once 'iEventsBackend.inc';
require_once 'config-eventi.php';

class MySQLBackend implements iEventsBackend {
	private $conn; // database connection
	
	/*
	 * CONSTRUCTOR:
	 *
	 * Used to setup the database connection
	 */
	function __construct() {
		$this->conn = new mysqli(MYSQL_SERVERNAME, MYSQL_USERNAME, 
			MYSQL_PASSWORD, 'faber_eventi');
		if ($this->conn->connect_error) {
			die("Failed connecting to the database!" . $this->conn->connect_error);
		}
	}
	/*
	 * DESTRUCTOR:
	 *
	 * Tears down the database connection created by the constructor
	 */
	function __destruct() {
		$this->conn->close();
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
		// use a prepared statement for security
		$prep = $this->conn->prepare("INSERT INTO eventi 
			(date, description, url) VALUES (?, ?, ?);");
		$prep->bind_param("iss", $date, $description, $url);
		$prep->execute();
		$prep->close();
	}

        public function removeEvent($id){
		$prep = $this->conn->prepare("DELETE FROM eventi WHERE id = ?");
		$prep->bind_param('i', $id);
		$prep->execute();
		$prep->close();
	}
}
?>
