<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once 'settings.php';
require_once 'fonctions.php';

if (isset($_GET["message"])) {

	if ($_GET["message"] == "refreshtv") {
		kill_process();
		get_ha_image($ha_url_tv, $long_lived_access_token);
		display_image($folder);
	}

        if ($_GET["message"] == "refreshmusic") {
                kill_process();
                get_ha_image($ha_url_music, $long_lived_access_token);
                display_image($folder);
        }

        if ($_GET["message"] == "kill") {
                kill_process();
        }
}

?>
