<?php
#
# This script extracts data from Show Slow for export or for display on the graph
#
# Perameters:
#	url		URL of the page to get data for
#	download	if present, will force browser to download the result instead of displaying inline
#	format		"csv" or "json" output format
#	provider	provider name to output data for (see $metrics array for values)
#	smooth		smooth values (usually used for display on the graph)
#	ver		if specified (making URL unique), aggressive caching headers are used
#
require_once(dirname(dirname(__FILE__)).'/global.php');

if (!array_key_exists('url', $_GET) || filter_var($_GET['url'], FILTER_VALIDATE_URL) === false) {
	header('HTTP/1.0 400 Bad Request');

	?><html>
<head>
<title>Bad Request: no valid url specified</title>
</head>
<body>
<h1>Bad Request: no valid url specified</h1>
<p>You must pass valid URL as 'url' parameter</p>
</body></html>
<?php 
	exit;
}

$query = sprintf("SELECT id, UNIX_TIMESTAMP(last_update) AS lu FROM urls WHERE urls.url = '%s'", mysql_real_escape_string($_GET['url']));
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$row = mysql_fetch_assoc($result);
$urlid = $row['id'];
$lastupdate = $row['lu'];
mysql_free_result($result);

# building a query to select all beacon data in one swoop
$query = "SELECT UNIX_TIMESTAMP(timestamp) as timestamp";

$provider_name = $_GET['provider'];
$provider = $all_metrics[$provider_name];

if (!$enabledMetrics[$provider_name] || is_null($provider)) {
	header('HTTP/1.0 400 Bad Request');

	?><html>
<head>
<title>Bad Request: provider is not supported</title>
</head>
<body>
<h1>Bad Request: provider is not supported</h1>
<p>Data for this provider is not stored in this instance</p>
</body></html>
<?php 
	exit;
}

# a list of result columns to smooth if requested
$to_smooth = array();

foreach ($provider['metrics'] as $section_name => $section) {
	foreach ($section as $metric) {
		$to_smooth[] = $metric[1];
		$query .= ",\n\t\t".$provider['table'].'.'.$metric[1].' AS '.$metric[1];
	}
}

$query .= "\nFROM ".$provider['table'];

$query .= "\nWHERE ".$provider['table'].".url_id = ".mysql_real_escape_string($urlid);

#echo $query; exit;

$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$data = array();

$format = 'csv';
if (array_key_exists('format', $_GET) && $_GET['format'] == 'json') {
	$format = 'json';
}

$formats = array(
	'csv' => array(
		'content-type' => 'text/csv',
		'extension' => 'csv'
	),
	'json' => array(
		'content-type' => 'application/json',
		'extension' => 'json'
	)
);

header('Content-type: '.$formats[$format]['content-type']);
if (array_key_exists('ver', $_GET)) {
	header('Expires: '.date('r', time() + 315569260));
	header('Cache-control: max-age=315569260');
}

$rows = array();
while ($row = mysql_fetch_assoc($result)) {
	$rows[] = $row;
}

mysql_free_result($result);

if (array_key_exists('smooth', $_GET)) {
	require_once(dirname(__FILE__).'/smooth.php');
	smooth($rows, $to_smooth);
}

header('Content-disposition: '.(array_key_exists('download', $_GET) ? 'attachment' : 'inline').';filename='.$provider_name.'_'. date('M-d-Y_G-i-s', $lastupdate).'.'.$formats[$format]['extension']);

if ($format == 'csv') {
	echo '# Measurement time';
	foreach ($provider['metrics'] as $section_name => $section) {
		foreach ($section as $metric) {
			echo ', '.$metric[0];
		}
	}
	echo "\n";

	foreach ($rows as $row) {
		echo date('c', $row['timestamp']);

		foreach ($provider['metrics'] as $section_name => $section) {
			foreach ($section as $metric) {
				echo ','.$row[$metric[1]];
			}
		}

		echo "\n";
	}
} else if ($format == 'json') {
	# JS timestamp (used by flot) - milliseconds since January 1, 1970 00:00:00 UTC
	for ($i = 0; $i < count($rows); $i++) {
		$rows[$i]['timestamp'] = $rows[$i]['timestamp'] * 1000;
	}
	echo json_encode($rows);
}
