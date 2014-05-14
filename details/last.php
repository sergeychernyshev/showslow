<?php
/**
 * This script returns just a metric value for particular provider to be used by monitoring scripts or indicators
 */
require_once(dirname(dirname(__FILE__)).'/global.php');

$urlid = array_key_exists('urlid', $_GET) ? filter_var($_GET['urlid'], FILTER_VALIDATE_INT) : null;

$url_passed = $_GET['url'];

# fixing up a URL if it is missing a double slash in domain name
$url_passed = preg_replace('#^http://?#', 'http://', $url_passed);
$url_passed = preg_replace('#^https://?#', 'https://', $url_passed);

$url = array_key_exists('url', $_GET) ? filter_var($url_passed, FILTER_VALIDATE_URL) : null;

function not_found() {
	header("HTTP/1.0 404 Not Found");
	?><html>
	<head>
	<title>Error - no such URL</title>
	</head>
	<body>
	<h1>Error - no such URL</h1>
	<p><a href="../">Go back</a> and pick the URL</p>
	</body></html>
	<?php
	exit;
}

if (!$urlid && !$url) {
	not_found();
}

if (!$urlid && $url) {
	$query = "SELECT id FROM urls WHERE urls.url_md5 = UNHEX(MD5('".mysql_real_escape_string($url)."'))";
	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
	}

	$row = mysql_fetch_assoc($result);

	if (is_null($row)) {
		not_found();
	} else {
		$urlid = $row['id'];

		if (is_null($urlid)) {
			not_found();
		}
	}
}

$requested_provider = $_GET['provider'];
if (!array_key_exists($requested_provider, $all_metrics)) {
	not_found();
}

$provider_name = $requested_provider;
$provider = $all_metrics[$requested_provider];

if (!$enabledMetrics[$provider_name] || !array_key_exists('score_column', $provider)) {
	not_found();
}

# building a query to select all beacon data in one swoop
$query = "SELECT urls.id AS url_id, urls.url as url, UNIX_TIMESTAMP(last_update) AS t,";
$query .= "\n\t".$provider['table'].'_last_id, UNIX_TIMESTAMP('.$provider['table'].'.timestamp) AS '.$provider_name.'_timestamp';

foreach ($provider['metrics'] as $section_name => $section) {
	foreach ($section as $metric) {
		$query .= ",\n\t\t".$provider['table'].'.'.$metric[1].' AS '.$provider_name.'_'.$metric[1];
	}
}

$query .= "\nFROM urls";
$query .= "\n\tLEFT JOIN ".$provider['table'].' ON urls.'.$provider['table'].'_last_id = '.$provider['table'].'.id';

$query .= "\nWHERE urls.id = ".mysql_real_escape_string($urlid);

#echo $query; exit;

$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$row = mysql_fetch_assoc($result);
mysql_free_result($result);

$score = $row[$provider_name.'_'.$provider['score_column']];
if (!is_null($score)) {
	$pretty_score = prettyScore($score);

	if (array_key_exists('output', $_GET) && $_GET['output'] == 'color') {
		$colors = array(
			1 => '#EE0000',
			2 => '#EE2800',
			3 => '#EE4F00',
			4 => '#EE7700',
			5 => '#EE9F00',
			6 => '#EEC600',
			7 => '#EEEE00',
			8 => '#C6EE00',
			9 => '#9FEE00',
			10 => '#77EE00',
			11 => '#4FEE00',
			12 => '#28EE00',
			13 => '#00EE00'
		);
		echo $colors[scoreColorStep($score)];
	} else {
		echo $score;
	}
}
