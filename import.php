<?php
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/beacon/beacon_functions.php');

$sites = file('php://stdin');

$user_id = 1;

$pairs = '';
$first = true;

foreach ($sites as $site) {
	if ($first) {
		$first = false;
	} else {
		$pairs .= ', ';
	}

	$site = trim($site);

	$url = filter_var('http://www.'.$site.'/', FILTER_VALIDATE_URL);

	echo "Importing URL: $url ...";
	if ($url === false) {
		echo "Bad data ($site)\n";
		continue;
	}
	else
	{
		echo " OK\n";
	}

	$url_id = getUrlId($url);
	$pairs .= '('.$user_id.','.$url_id.')';
}

$query = "INSERT IGNORE INTO user_urls (user_id, url_id) VALUES $pairs";

$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}
