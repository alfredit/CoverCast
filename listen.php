<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "hello";

//shell_exec('sudo -u www-data /script/rpi-rgb-led-matrix/utils/music.sh');
shell_exec('sudo -u www-data /CoverCast/music.sh');

exit;

?>
