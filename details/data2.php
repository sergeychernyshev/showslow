<?php
#
# This script extracts data from Show Slow for export or for display on the graph
# It returns data for only one provider at a time so you might need to make multiple requests
#
# Perameters:
#	url		URL of the page to get data for
#	download	if present, will force browser to download the result instead of displaying inline
#	format		"csv" or "json" output format
#	provider	provider name to output data for (see $all_metrics array's keys for valid values)
#	metrics		a comma-separated list of metrics (defaults to all metrics by provider)
#	smooth		smooth values (usually used for display on the graph)
#	ver		if specified (making URL unique), aggressive caching headers are used
#	start		start date in the range (optional)
#	end		end date in the range (defaults to now)
#
require_once(dirname(dirname(__FILE__)).'/global.php');

if (!array_key_exists('urlid', $_GET) || filter_var($_GET['urlid'], FILTER_VALIDATE_INT) === false) {
	header('HTTP/1.0 400 Bad Request');

	?><html>
<head>
<title>Bad Request: no valid urlid specified</title>
</head>
<body>
<h1>Bad Request: no valid urlid specified</h1>
<p>You must pass valid URL ID as 'urlid' parameter</p>
</body></html>
<?php 
	exit;
}

$query = sprintf("SELECT id, url, UNIX_TIMESTAMP(last_update) AS lu FROM urls WHERE urls.id = %d", mysql_real_escape_string($_GET['urlid']));
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$row = mysql_fetch_assoc($result);
$urlid = $row['id'];
$url = $row['url'];
$lastupdate = $row['lu'];
mysql_free_result($result);

# building a query to select all beacon data in one swoop
$query = "SELECT UNIX_TIMESTAMP(timestamp) as timestamp";

$provider_name = $_GET['provider'];

# contains all metrics (not only names) to return
$result_metrics = array();

if ($provider_name == 'custom') {
	if (count($metrics) == 0) {
		header('HTTP/1.0 400 Bad Request');

		?><html>
	<head>
	<title>Bad Request: no custom metrics specified</title>
	</head>
	<body>
	<h1>Bad Request: no custom metrics specified</h1>
	<p>No custom metrics are stored by this instance</p>
	</body></html>
	<?php
		exit;

	}

	if (!array_key_exists('metrics', $_GET)) {
		?><html>
		<head>
		<title>Bad Request: custom metric is not specified</title>
		</head>
		<body>
		<h1>Bad Request: custom metric is not specified</h1>
		<p>Custom metric name must be specified</p>
		</body></html>
		<?php
		exit;
	}

	$custom_metric_slug = $_GET['metrics'];

	if (!array_key_exists($custom_metric_slug, $metrics)) {
		header('HTTP/1.0 400 Bad Request');

		?><html>
	<head>
	<title>Bad Request: custom metric is not supported</title>
	</head>
	<body>
	<h1>Bad Request: custom metric is not supported</h1>
	<p>Custom metric <b><?php echo htmlentities($custom_metric_slug) ?></b> is not stored in this instance</p>
	</body></html>
	<?php
		exit;
	}

	$custom_metric = $metrics[$custom_metric_slug];
	// assume the default value is NUBER
	if (!array_key_exists('type', $custom_metric)) {
		$custom_metric['type'] = NUMBER;
	}

	// faking regular metric entry
	$result_metrics[] = array($custom_metric['title'], $custom_metric_slug, $custom_metric['type']);
} else {
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
	if (array_key_exists('metrics', $_GET)) {
		$metrics_to_show = explode(',', $_GET['metrics']);

		$provider_has_metrics_to_show = array();

		# lets go through all metrics that we know for this provider
		foreach ($provider['metrics'] as $section_name => $section) {
			foreach ($section as $metric) {
				$index = array_search($metric[1], $metrics_to_show);

				# if metric was requested, lets add it to $result_metrics array
				if (is_array($metrics_to_show) && $index !== FALSE) {
					$result_metrics[$index] = $metric;
					$provider_has_metrics_to_show[] = $metric[1];
				}
			}
		}

		# if we have more metrics requested, then we have, let's errror out
		$leftover = array_diff($metrics_to_show, $provider_has_metrics_to_show);
		if (count($leftover) > 0) {
			header('HTTP/1.0 400 Bad Request');

			?><html>
		<head>
		<title>Bad Request: metrics are not supported</title>
		</head>
		<body>
		<h1>Bad Request: metrics are not supported</h1>
		<p>Metric(s) <b><?php echo htmlentities(implode(', ', $leftover)) ?></b> are not stored for <b><?php echo $provider['title']?></b></p>
		</body></html>
		<?php
			exit;
		}
	} else {
		foreach (array_values($provider['metrics']) as $section) {
			foreach ($section as $metric) {
				$result_metrics[] = $metric;
			}
		}
	}
}

# a list of result columns to smooth if requested
$to_smooth = array();

if ($provider_name == 'custom') {
	$query .= ",\n\t\tvalue AS ".$custom_metric_slug;
	$query .= "\nFROM metric";
	$query .= "\nWHERE url_id = ".mysql_real_escape_string($urlid);
	$query .= "\nAND metric_id = ".mysql_real_escape_string($custom_metric['id']);

} else {
	for ($i = 0; $i < count($result_metrics); $i++) {
		$metric = $result_metrics[$i];

		$to_smooth[] = $metric[1];
		$query .= ",\n\t\t".$provider['table'].'.'.$metric[1].' AS '.$metric[1];
	}

	$query .= "\nFROM ".$provider['table'];

	$query .= "\nWHERE ".$provider['table'].".url_id = ".mysql_real_escape_string($urlid);
}

if (array_key_exists('start', $_GET)) {
	$start = strtotime($_GET['start']);
	if ($start !== FALSE && $start != -1) {
		$query .= "\nAND timestamp >= FROM_UNIXTIME(".mysql_real_escape_string($start).")";
	}
} else {
	// fetch last 3 months by default
	$query .= "\nAND timestamp > DATE_SUB(now(), INTERVAL $oldDataInterval DAY)";
}

if (array_key_exists('end', $_GET)) {
	$end = strtotime($_GET['end']);
	if ($start !== FALSE && $start != -1) {
		$query .= "\nAND timestamp <= FROM_UNIXTIME(".mysql_real_escape_string($end).")";
	}
}

$query .= "\nORDER BY timestamp DESC";

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
	for ($i = 0; $i < count($result_metrics); $i++) {
		$metric = $result_metrics[$i];
		$units = $metric_types[$metric[2]]['legend'];
		echo ', ['.$metric[1].'] '.$metric[0].($units !== '' ? ' ('.$units.')' : '');
	}
	echo "\n";

	foreach ($rows as $row) {
		echo date('c', $row['timestamp']);

		for ($i = 0; $i < count($result_metrics); $i++) {
			$metric = $result_metrics[$i];
			echo ','.$row[$metric[1]];
		}

		echo "\n";
	}
} else if ($format == 'json') {
	# flat array with no keys - order is the same as specified in metrics parameter
	$jsondata = array();

	echo '[';

	$first = true;
	for ($i = 0; $i < count($rows); $i++) {
		echo $first ? '' : ',';
		$first = false;

		# JS timestamp (used by flot) - milliseconds since January 1, 1970 00:00:00 UTC
		echo '['.$rows[$i]['timestamp'] * 1000;

		for ($j = 0; $j < count($result_metrics); $j++) {
			$val = $rows[$i][$result_metrics[$j][1]];
			if (is_numeric($val)) {
				echo ','.$rows[$i][$result_metrics[$j][1]];
			} else {
				echo ',null';
			}
		}

		echo ']';
	}
	echo ']';
}
