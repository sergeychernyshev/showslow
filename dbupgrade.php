<?php
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/dbupgrade/dbup.php');

$versions = array();
header('Content-type: text/plain');

// Add new migrations on top, right below this line.

/* version 2
 *
 * Adding HAR beacon
 */
$versions[2] = array(
	'up' => "CREATE TABLE  `har` (
 `id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT  'Unique HAR id',
 `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
 `url_id` BIGINT( 20 ) UNSIGNED NOT NULL COMMENT  'URL id',
 `har` LONGBLOB NOT NULL COMMENT  'HAR contents',
 `compressed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT  'Indicates that HAR data is stored compressed'
) ENGINE = MYISAM",
	'down' => 'DROP TABLE har',
);

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
