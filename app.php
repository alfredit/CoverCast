<?php

require_once 'settings.php';
require_once 'fonctions.php';

$messge = "";
$brightness = '31';

if (isset($_GET["message"])) {

$parts = explode("-", $_GET["message"]);
$message = $parts[0];

if (isset($parts[1])) {
$brightness = (int)$parts[1];

}

//echo "brightness : ".$brightness." (41 is the default value)<br>";

	if ($message == "refreshtv") {
		echo "refresh tv<br>";
		kill_process();
		rmimage();
		get_ha_image($ha_url_tv, $long_lived_access_token);
		display_image($folder,$brightness);
	}

        if ($message == "refreshmusic") {
                echo "refresh music<br>";
                kill_process();
                rmimage();
                get_ha_image($ha_url_music, $long_lived_access_token);
                display_image($folder,$brightness);
        }

        if ($message == "kill") {
                kill_process();
                rmimage();
        }

        if ($message == "status") {
		status();
        }

        if ($message == "refreshbrightness") {
                echo "refresh brightness<br>";
                kill_process();
                display_image($folder,$brightness);
        }

        if ($message == "spoon") {
                echo "refresh Spoonradio<br>";
                $targetUrl = "https://www.spoonradio.com/";
                $imageUrl = getSpoonCoverImageUrl($targetUrl);
                $long_lived_access_token = "";
                get_ha_image($imageUrl, $long_lived_access_token);
		$file1 = 'ha_media_artwork.jpg';
		$file2 = 'ha_media_artwork.new.jpg';
		if (file_exists($file1) && file_exists($file2)) {
		    if (md5_file($file1) === md5_file($file2)) {
			echo "same file, skip<BR>";
		    } else {
	        echo "different images, display<BR>";
        	        kill_process();
               		rmimage();
	                display_image($folder,$brightness);
    }
}
else {
    // Let the user know if a file is missing
    echo "One or both files not found.<br>";
    kill_process();
    display_image($folder,$brightness);


}

        }

}

?>
