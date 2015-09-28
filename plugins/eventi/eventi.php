<?php
/**
 * Main class for the event manager.
 *
 * @author: Paolo Scaramuzza
 */

require_once 'SQLiteBackend.inc';
require_once 'MySQLBackend.inc';
require_once 'config-eventi.php';

// see also js/eventi.js which uses those URLs
define('MOST_RECENT_URL', 'eventi');
define('ALL_EVENTS_URL', 'eventi/all');

class Eventi {
	private $mostRecent;
	private $allEvents;
	private $admin;

	private $backend;

	public function __construct() {
		$this->mostRecent = false;
		$this->admin = false;
		$this->allEvents = false;

		if ('sqlite' == BACKEND)
			$this->backend = new SQLiteBackend();
		else if ('mysql' == BACKEND)
			$this->backend = new MySQLBackend();
		else
			die("Invalid database type: " . BACKEND .
				". Expected 'sqlite' or 'mysql'.");
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
			session_start();
			
			// if login parse post data
			if ($_POST['type'] === "login") {
				if (($_POST['username'] === ADMIN_USERNAME) &&
					(ADMIN_PW === hash('sha256', $_POST['password'])))
                			$_SESSION['login_expire'] = time() + 60;
				else
                			$_SESSION['login_expire'] = -1;
			}
			
			// Decide wether to show the login or the admin page
			if ($_SESSION['login_expire'] > time()) {
				$_SESSION['login_expire'] = time() 
					+ (60 * LOGIN_EXPIRE_MIN);
				/*
				 * Regenerate session for security
				 * See:
				 * https://stackoverflow.com/questions/22965067/when-and-why-i-should-use-session-regenerate-id
				 */
				session_regenerate_id(true);
				$this->showAdmin();
			} else {
				$this->showLogin();
			}
                        exit;
		}
	}
	
	/*
	 * Helper functions
	 */
	private function showLogin() {
?>	
		<html><head>
		<title>Log In</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="/plugins/eventi/css/style.css">
		<link rel="stylesheet" type="text/css" href="/plugins/eventi/css/foundation-icons.css">
		</head><body>
<?php
                if (isset($_SESSION['login_expire'])) {
                        if($_SESSION['login_expire'] > 0) {
				echo '<div class="error">';
                                echo '<p><i class="fi-alert bigerror"></i>';
				echo 'Autenticazione scaduta</p>';
				echo '</div>';
                        } else {
				echo '<div class="error">';
				echo '<p><i class="fi-alert bigerror"></i>';
                                echo 'Autenticazione fallita</p>';
				echo '</div>';
			}
			// avoid showing errors multiple times
			unset($_SESSION['login_expire']);
                }
?>
		<div class="login">
		<form action="" method="post">
		
		<p class="form">Username<br>
		<input type="text" name="username" class="input-text">
		</p>
		<p class="form">Password<br>
		<input type="password" name="password" class="input-text">
		</p>
		<input type="hidden" name="type" value="login">
		<p class="submit">
		<input type="submit" value="Log In" class="submit-button">
		</p>
		</form>
		</div>
		
		</body></html>
<?php
	}

	private function showAdmin() {
		// parse post data (event management related)
		if ($_POST['type'] === 'add') {
			$date_int = strtotime(str_replace('/', '-', 
				$_POST['date']));
			$this->backend->addEvent($date_int, $_POST['text'],
				$_POST['url']);	
		} else if ($_POST['type'] === 'del') {
			$this->backend->removeEvent($_POST['id']);
		}
?>
		<!DOCTYPE html>
		<html><head>
		<title>Amministrazione Eventi</title>
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <link rel="stylesheet" type="text/css" href="/plugins/eventi/css/style.css">
                <link rel="stylesheet" type="text/css" href="/plugins/eventi/css/foundation-icons.css">
		<script>
		function postAdd() {
			var date = document.getElementById("date").value;
			var text = document.getElementById("text").value;
			var url = document.getElementById("url").value;
			
			var http = new XMLHttpRequest();
                        var params ="type=add&date=" + date +
				"&text=" + text + "&url=" + url;

                        http.open("POST", window.location.href, true);
                        //Send the proper header information
                        http.setRequestHeader("Content-type", 
                                "application/x-www-form-urlencoded");
                        http.setRequestHeader("Content-length", params.length);
                        http.setRequestHeader("Connection", "close");

                        http.onreadystatechange = function() {
				if(http.readyState == 4 && http.status == 200){
                                        location.reload(true);
                                }
                        }
                        http.send(params);
		}
		function postDel(id) {
			var http = new XMLHttpRequest();
			var params ="type=del&id=" + id;

			http.open("POST", window.location.href, true);
			//Send the proper header information
			http.setRequestHeader("Content-type", 
				"application/x-www-form-urlencoded");
			http.setRequestHeader("Content-length", params.length);
			http.setRequestHeader("Connection", "close");

			http.onreadystatechange = function() {
				if(http.readyState == 4 && http.status == 200){
					location.reload(true);
				}
			}
			http.send(params);
		}
		</script></head><body>
		<div class="event-card">
		<input type="date" id="date" class="input-text small"
			placeholder="Data (GG/MM/AAAA)">
		<input type="text" id="text" class="input-text big"
			placeholder="Descrizione Evento">
                <input type="text" id="url" class="input-text small"
			 placeholder="Link all'articolo">
                <button onclick="postAdd()" class="fi-plus add"></button>
		</div>
<?php
		$events = $this->backend->getMostRecent();
		foreach ($events as $event) {
			$date = strftime('%x', $event['date']);
			echo '<div class="event-card">' . "\n";
			echo '<input type="text" class="input-text small" '.
				' value="' . $date . '" readonly> ';
			echo '<input type="text" class="input-text big" ' .
				' value="' . $event['description'] . '" readonly>';
			echo ' <input type="text" class="input-text small" ' .
				'value="' . $event['url'] . '" readonly>';
			echo ' <button class="fi-x remove" ' . 
				'onclick="postDel('. $event['id'] . ')"></button>';
			echo '</div>' . "\n";
		}
		echo '</body></html>';
	}

	private function showEvents($number = -1) {
		$events = $this->backend->getMostRecent($number);
		
		echo '<ul>';
		foreach ($events as $event) {
			$date = strftime('%A %e %B %G', $event['date']);
			echo '<li><strong>' . $date . ':</strong>';
			echo '<p>' . $event['description'];
			if (!empty($event['url']))
				echo '<a target="_parent" href="' . $event['url'] . 
					'"> Leggi tutto</a>';
			echo '</p></li>';
		}
		echo '</ul>';
	}
}
?>
