<?php 
require_once(dirname(dirname(__FILE__)).'/global.php');

if (!array_key_exists('urlid', $_GET) || filter_var($_GET['urlid'], FILTER_VALIDATE_INT) === false) {
	header('HTTP/1.0 400 Bad Request');

	?><html>
<head>
<title>Bad Request: no valid urlid specified</title>
</head>
<body>
<h1>Bad Request: no valid urld specified</h1>
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

if (array_key_exists('subset', $_REQUEST)) {
	if ($_REQUEST['subset'] == 'graph')
	{
		$query = sprintf("SELECT UNIX_TIMESTAMP(d.timestamp) as time, rank
			FROM dynatrace d WHERE d.url_id = %d AND d.timestamp > DATE_SUB(now(),INTERVAL 3 MONTH)
			ORDER BY d.timestamp DESC",
			mysql_real_escape_string($urlid)
		);
	}
	else if ($_REQUEST['subset'] == 'table')
	{
		$query = sprintf("SELECT UNIX_TIMESTAMP(d.timestamp) as time,
				pagesize, reqnumber, rank, timetoimpression
			FROM dynatrace d WHERE d.url_id = %d AND d.timestamp > DATE_SUB(now(),INTERVAL 3 MONTH)
			ORDER BY d.timestamp DESC",
			mysql_real_escape_string($urlid)
		);
	}
} else {
	$query = sprintf("SELECT UNIX_TIMESTAMP(d.timestamp) as time,
			rank, cache, net, server, js,
			timetoimpression, timetoonload, timetofullload,
			reqnumber, xhrnumber,
			pagesize, cachablesize, noncachablesize,
			timeonnetwork, timeinjs, timeinrendering
		FROM dynatrace d WHERE d.url_id = %d AND d.timestamp > DATE_SUB(now(),INTERVAL 3 MONTH)
		ORDER BY d.timestamp DESC",
		mysql_real_escape_string($urlid)
	);
}

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

$rows = array();
while ($row = mysql_fetch_assoc($result)) {
	$rows[] = $row;
}

mysql_free_result($result);

if (array_key_exists('smooth', $_REQUEST)) {
	require_once(dirname(__FILE__).'/smooth.php');
	smooth($rows, array('rank'));
}

if (!array_key_exists('subset', $_REQUEST))
{
	header('Content-disposition: attachment;filename=dynatrace.csv');

	echo '# Measurement time';
	echo ', Overall Page Rank (Percentage)';
	echo ', Caching Rank (Percentage)';
	echo ', Network Rank (Percentage)';
	echo ', Server-Side Rank (Percentage)';
	echo ', JavaScript Rank (Percentage)';
	echo ', Time to First Impression (ms)';
	echo ', Time to onLoad (ms)';
	echo ', Time to Full Page Load (ms)';
	echo ', Number of Requests';
	echo ', Number of XHR Requests';
	echo ', Total Page Size (bytes)';
	echo ', Total Cachable Size (bytes)';
	echo ', Total Non-Cachable Size (bytes)';
	echo ', Total Time on Network (ms)';
	echo ', Total Time in JavaScript (ms)';
	echo ', Total Time in Rendering (ms)';
	echo "\n";
}

foreach ($rows as $row) {

        echo date('c', $row['time']).',';

	if (array_key_exists('subset', $_REQUEST)) {
		if ($_REQUEST['subset'] == 'graph')
		{
			echo $row['rank'];
		}
		else if ($_REQUEST['subset'] == 'table')
		{
			echo	$row['pagesize'].','.
				$row['reqnumber'].','.
				$row['rank'].','.
				$row['timetoimpression'];
		}
	} else {
		echo $row['rank'].','.$row['cache'].','.$row['net'].','.$row['server'].','.$row['js'].','.
		$row['timetoimpression'].','.$row['timetoonload'].','.$row['timetofullload'].','.
		$row['reqnumber'].','.$row['xhrnumber'].','.
		$row['pagesize'].','.$row['cachablesize'].','.$row['noncachablesize'].','.
		$row['timeonnetwork'].','.$row['timeinjs'].','.$row['timeinrendering'];
	}

	echo "\n";
}

