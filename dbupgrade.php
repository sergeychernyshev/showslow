<?
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/dbupgrade/dbup.php');

$versions = array();
header('Content-type: text/plain');

// Add new migrations on top, right below this line.

// version X
/*
$versions[X] = array(
	'up' => '',
	'down' => '',
);
*/

// version 1
// To get to version 1, use snapshot in tables.sql

try {
	if (!empty($argc) && count($argv) == 2 && $argv[1] == 'down') {
		dbdown(new mysqli( $host, $user, $pass, $db), $versions);
	} else {
		dbup(new mysqli( $host, $user, $pass, $db), $versions);
	}
} catch (Exception $e) {
	echo '[ERR] Caught exception: ',  $e->getMessage(), "\n";
}
