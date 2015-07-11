<?php
/**
 * Main class for the event manager.
 *
 * @author: Paolo Scaramuzza
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
                        session_start();
			// if login parse post data
			if ($_POST['type'] === "login") {
				if (($_POST['username'] === ADMIN_USERNAME) &&
					(ADMIN_PW === hash('sha256', $_POST['password'])))
                			$_SESSION['login_expire'] = time() + 60;
				else
                			$_SESSION['login_expire'] = -1;
			}

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
		</script></head><body><center>
		<h1>Pannello di amministrazione</h1>
		<table style="width: 75%" border="0">
		<col width="25%">
		<col width="50%">
		<col width="25%">
		<tr>
		<td align="center">Data</td>
		<td align="center">Testo</td>
		<td align="center">URL</td>
		</tr><tr>
		<td align="center"><input type="date" id="date"></td>
		<td align="center"><input type="text" id="text" style="width:100%"></td>
		<td align="center"><input type="text" id="url"></td>
		<td align="center"><button onclick="postAdd()">+</button></td>
		</tr>
<?php
		$events = $this->backend->getMostRecent();
		foreach ($events as $event) {
			$date = strftime('%x', $event['date']);
			echo '<tr>';
			echo '<td align="center">' . $date . '</td>';
			echo '<td align="center">' . $event['description'] . '</td>';
			echo '<td align="center">' . $event['url'] . '</td>';
			echo '<td><button onclick="postDel('. $event['id'] .
				 ')">X</button></td>';
			echo '</tr>';
		}
		echo '</table>';
		echo '</center></body></html>';
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
