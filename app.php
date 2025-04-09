<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once 'settings.php';
require_once 'fonctions.php';

if (isset($_GET["message"])) {
	if ($_GET["message"] == "refresh") {
		kill_process();
		get_ha_image($ha_url, $long_lived_access_token);
		display_image($folder);
	}
        if ($_GET["message"] == "kill") {
                kill_process();
        }
}

?>
