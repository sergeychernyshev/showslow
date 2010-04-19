<?php 
require_once(dirname(__FILE__).'/global.php');

header('Content-type: text/plain');

// whatever to display all URLs or the ones to monitor only.
$all = false;

if (array_key_exists('all', $_GET)) {
	$all = true;
}

if ($all) {
	$query = sprintf("SELECT DISTINCT url FROM urls INNER JOIN user_urls on user_urls.url_id = urls.id");
} else {
	$query = sprintf("SELECT DISTINCT url FROM urls INNER JOIN user_urls on user_urls.url_id = urls.id WHERE last_update IS NULL OR last_update < DATE_SUB(now(), INTERVAL %d HOUR)", $monitoringPeriod);
}

$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

while ($row = mysql_fetch_assoc($result)) {
        echo $row['url']."\n";
}
mysql_free_result($result);

