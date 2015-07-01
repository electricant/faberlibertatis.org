<?php
/**
 *
 * @author Gilbert Pellegrom
 * @link http://picocms.org
 * @license http://opensource.org/licenses/MIT
 */

require_once 'MySQLBackend.inc';
require_once 'config-eventi.php';

class Eventi {
	private $mostRecent;
	private $allEvents;
	private $admin;

	private $backend;

	public function __construct() {
		$this->mostRecent = false;
		$this->admin = false;
		$this->allEvents = false;
	
		$this->backend = new MySQLBackend();
	}
	/*
	 * Pico hooks
	 */
	public function request_url(&$url) {
		if ($url == MOST_RECENT_URL)
			$this->mostRecent = true;
		else if ($url == ALL_EVENTS_URL)
			$this->allEvents = true;
		else if ($url == ADMIN_URL)
			$this->admin = true;
	}
	
	public function before_render(&$twig_vars, &$twig, &$template) {
		if ($this->mostRecent) {
			// override 404 header
			header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
			$this->showEvents(NUM_RECENT);
			// avoid displaying 404 page
			exit;
		} else if ($this->allEvents) {
			header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
			$this->showEvents();
			exit;
		} else if ($this->admin) {
                        header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
                        $this->showAdmin();
                        exit;
		}
	}
	
	/*
	 * Helper functions
	 */
	private function showAdmin() {
		echo '<html><head>';
		echo '</head><body>';

		echo 'Pannello di amministrazione';

		echo '</body></html>';
	}

	private function showEvents($number = -1) {
		echo '<html><head>';
		echo '<meta charset="iso-8859-1">'; // default mysql charset
                echo '<link rel="stylesheet" href="/themes/faber/assets/css/foundation.css">';
                echo '</head><body><ul>';
		$events = $this->backend->getMostRecent($number);

		foreach ($events as $event) {
			$date = strftime('%A %e %B %G', $event['date']);
			echo '<li><strong>' . $date . ':</strong>';
			echo '<p>' . $event['description'];
			if (!empty($event['url']))
				echo '<a target="_parent" href="' . $event['url'] . '"> Leggi tutto</a>';
			echo '</p></li>';
		}
                echo '</ul></body></html>';
	}
}

?>
