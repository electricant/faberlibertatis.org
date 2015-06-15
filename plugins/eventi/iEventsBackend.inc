<?php
/*
 * This interface separates the database interaction code from that of the main
 * page. The database is responsible for storing and deleting events.
 * Events are identified by a date (unix timestamp) and a description, which are
 * mandatory. There's also an optional parameter which stores the URL to an
 * article regardin the event.
 */

interface iEventsBackend {
	/*
	 * Returns an array with the number of events requested.
	 * Setting -1 or any other negative number results in an array
	 * containing all the events.
	 * NOTE: avoid requesting all the events if there are too many as this
	 *       will waste your memory and CPU time.
	 * TODO: describe how the array is organized
	 */
	public function getMostRecent($number = -1);
	/*
	 * Return an array with events whose date is greater than the one passed
	 * to this function.
	 */
	public function getNewer($fromDate);
	/*
	 * Add an event to the list of events.
	 */
	public function addEvent($date, $description, $url = "");
	/*
	 * Remove a single event.
	 */
	public function removeEvent($date, $description);
}
?> 
