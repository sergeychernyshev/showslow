<?php
require_once(dirname(__FILE__).'/global.php');

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

	$url = filter_var($site, FILTER_VALIDATE_URL);

	# let's try to beautify the URL by appending http://www.
	if ($url === false) {
		if (substr($site, 0, 3) == 'www') {
			$url = filter_var('http://'.$site.'/', FILTER_VALIDATE_URL);
		}
		else
		{
			$url = filter_var('http://www.'.$site.'/', FILTER_VALIDATE_URL);
		}
	}
	else
	{
		# skipping non-http URLs
		if (substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://') {
			echo "Skipping non-http URL: $url\n";
			continue;
		}
	}

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
