<?php
/*
 * Use SQLite to store the events.
 *
 * The table where the data is stored is called 'eventi'. It is created with the
 * following command:
 * CREATE TABLE eventi (
 *	id INTEGER PRIMARY KEY NOT NULL,
 *	date INTEGER NOT NULL,
 *	description TEXT NOT NULL,
 *	url TEXT);
 *
 *
 * Column description:
 *  id          - Used as an identifier mainly when deleting events
 *  date        - Date stored as an unix timestamp
 *  description - Text description for the event
 *  url         - Url inside the website for an article describing the event
 */

require_once 'iEventsBackend.inc';
require_once 'config-eventi.php';

class SQLiteBackend implements iEventsBackend {
	private $dbhandle; // database handle
	
	/*
	 * CONSTRUCTOR:
	 *
	 * Used to setup the database connection
	 */
	function __construct() {
		$this->dbhandle = new SQLite3(SQLITE_DB);
	}
	/*
	 * DESTRUCTOR:
	 *
	 * Tears down the database connection created by the constructor
	 */
	function __destruct() {
		$this->dbhandle->close();
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
		
		$result = $this->dbhandle->query($query_str);
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$retArray[] = $row;
		}

		return $retArray;
	}

	public function getNewer($fromDate) {

	}

	public function addEvent($date, $description, $url = ""){
		// use a prepared statement for security
		$prep = $this->dbhandle->prepare("INSERT INTO eventi 
			(date, description, url) VALUES (:date, :desc, :url);");
		$prep->bindValue(':date', $date, SQLITE3_INTEGER);
		$prep->bindValue(':desc', $description, SQLITE3_TEXT);
		$prep->bindValue(':url', $url, SQLITE3_TEXT);

		$prep->execute();
		$prep->close();
	}

	public function removeEvent($id){
		$prep = $this->dbhandle->prepare("DELETE FROM eventi WHERE id = :id");
		$prep->bindValue(':id', $id, SQLITE3_INTEGER);

		$prep->execute();
		$prep->close();
	}
}
?>
