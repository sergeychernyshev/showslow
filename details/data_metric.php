<?php 
require_once(dirname(dirname(__FILE__)).'/global.php');

if (!array_key_exists('urlid', $_GET) || filter_var($_GET['urlid'], FILTER_VALIDATE_INT) === false
	|| !array_key_exists('metric', $_GET) || !array_key_exists($_GET['metric'], $metrics)) {
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

$query = sprintf("SELECT url, id FROM urls WHERE id = %d", mysql_real_escape_string($_GET['urlid']));
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$row = mysql_fetch_assoc($result);
$url = $row['url'];
$urlid = $row['id'];
mysql_free_result($result);

$query = sprintf("SELECT UNIX_TIMESTAMP(timestamp) AS t, value
	FROM metric WHERE url_id = %d AND metric_id = %d AND timestamp > DATE_SUB(now(), INTERVAL 3 MONTH)
	ORDER BY timestamp DESC",
	mysql_real_escape_string($urlid),
	mysql_real_escape_string($metrics[$_GET['metric']]['id'])
);

$result = mysql_query($query);

if (!$result) {
        error_log(mysql_error());
}

$data = array();

header('Content-type: text/plain');
if (array_key_exists('ver', $_GET)) {
	header('Expires: '.date('r', time() + 315569260));
	header('Cache-control: max-age=315569260');
}
echo '# Timestamp, '.$metrics[$_GET['metric']]['title'].' for '.$url."\n";

$rows = array();
while ($row = mysql_fetch_assoc($result)) {
	$rows[] = $row;
}
mysql_free_result($result);

if (array_key_exists('smooth', $_REQUEST)) {
	require_once(dirname(__FILE__).'/smooth.php');
	smooth($rows, array('value'));
}

foreach ($rows as $row) {
        echo date('c', $row['t']).','.$row['value']."\n";
}

