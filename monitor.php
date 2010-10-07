<?php
require_once(dirname(__FILE__).'/global.php');

header('Content-type: text/plain');

// whatever to display all URLs or only new ones, just recently added
$new = false;

if (array_key_exists('new', $_GET)) {
	$new = true;
}

if ($new) {
	$query = "SELECT DISTINCT url FROM urls INNER JOIN user_urls on user_urls.url_id = urls.id WHERE DATE_ADD(added, INTERVAL %d HOUR) > NOW()";
	foreach ($all_metrics as $provider_name => $provider) {
		$query .= " AND ".$provider['table'].'_last_id IS NULL';
	}
	$query = sprintf($query, $monitoringPeriod);
} else {
	$query = "SELECT DISTINCT url FROM urls INNER JOIN user_urls on user_urls.url_id = urls.id";
}

$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$urls = array();

while ($row = mysql_fetch_assoc($result)) {
	$url = validateURL($row['url'], false);

	if (is_null($url)) {
		continue;
	}

	$urls[] = $url;
}
mysql_free_result($result);

echo implode("\n", $urls);

