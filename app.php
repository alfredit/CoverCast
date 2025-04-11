<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once 'settings.php';
require_once 'fonctions.php';

$brightness = '41';

if (isset($_GET["message"])) {

$parts = explode("-", $_GET["message"]);
$message = $parts[0];

if ($parts[1] > 0) {
$brightness = (int)$parts[1];
}
echo "brightness : ".$brightness." (41 is the default value)<br>";

	if ($message == "refreshtv") {
		echo "refresh tv<br>";
		kill_process();
		get_ha_image($ha_url_tv, $long_lived_access_token);
		display_image($folder,$brightness);
	}

        if ($message == "refreshmusic") {
                echo "refresh music<br>";
                kill_process();
                get_ha_image($ha_url_music, $long_lived_access_token);
                display_image($folder,$brightness);
        }

        if ($message == "kill") {
                kill_process();
        }
}

?>
