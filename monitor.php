<?php
require_once(dirname(__FILE__).'/global.php');

header('Content-type: text/plain');

// whatever to display all URLs or only new ones, just recently added
$new = false;

if (array_key_exists('new', $_GET)) {
	$new = true;
}

if ($new) {
	$query = "SELECT DISTINCT url FROM urls INNER JOIN user_urls on user_urls.url_id = urls.id WHERE last_update IS NULL";
} else {
	$query = "SELECT DISTINCT url FROM urls INNER JOIN user_urls on user_urls.url_id = urls.id";
}

$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

while ($row = mysql_fetch_assoc($result)) {
	echo $row['url']."\n";
}
mysql_free_result($result);

