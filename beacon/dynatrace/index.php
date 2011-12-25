<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/global.php');

function updateUrlAggregates($url_id, $measurement_id)
{
	# updating latest values for the URL
	$query = sprintf("UPDATE urls SET dynatrace_last_id = %d, last_update = now(), dt_refresh_request = 0 WHERE id = %d",
		mysql_real_escape_string($measurement_id),
		mysql_real_escape_string($url_id)
	);

	$result = mysql_query($query);

	if (!$result) {
		beaconError(mysql_error());
	}
}

$post_data = file_get_contents("php://input");
$post = json_decode($post_data, true);

/*
	Only URL and rank will be mandatory - the rest will be optional
*/
if (!is_null($post) && array_key_exists('url', $post)
	&& array_key_exists('rank', $post) && filter_var($post['rank'], FILTER_VALIDATE_INT) !== false)
{
	checkBeaconKey('dynatrace');

	$url_id = getUrlId(urldecode($post['url']));

	if (array_key_exists('ranks', $post)) {
		$ranks = $post['ranks'];

		$cache	= array_key_exists('cache', $ranks) && array_key_exists('rank', $ranks['cache']) ?
				$ranks['cache']['rank'] : null;
		$net	= array_key_exists('net', $ranks) && array_key_exists('rank', $ranks['net']) ?
				$ranks['net']['rank'] : null;
		$server	= array_key_exists('server', $ranks) && array_key_exists('rank', $ranks['server']) ?
				$ranks['server']['rank'] : null;
		$js	= array_key_exists('js', $ranks) && array_key_exists('rank', $ranks['js']) ?
				$ranks['js']['rank'] : null;
	}

	# adding new entry
	$query = sprintf("INSERT INTO dynatrace (
		version, url_id,
		rank, cache, net, server, js,
		timetoimpression, timetoonload, timetofullload,
		reqnumber, xhrnumber, pagesize, cachablesize, noncachablesize,
		timeonnetwork, timeinjs, timeinrendering,
		details
	)
	VALUES (
		'%s', '%d',
		'%d', '%d', '%d', '%d', '%d',
		'%d', '%d', '%d',
		'%d', '%d', '%d', '%d', '%d',
		'%d', '%d', '%d',
		'%s'
	)",
		mysql_real_escape_string(array_key_exists('version', $post) ? $post['version'] : null),
		mysql_real_escape_string($url_id),
		mysql_real_escape_string($post['rank']),
		mysql_real_escape_string($cache),
		mysql_real_escape_string($net),
		mysql_real_escape_string($server),
		mysql_real_escape_string($js),
		mysql_real_escape_string(array_key_exists('timetoimpression', $post) ? $post['timetoimpression'] : null),
		mysql_real_escape_string(array_key_exists('timetoonload', $post) ? $post['timetoonload'] : null),
		mysql_real_escape_string(array_key_exists('timetofullload', $post) ? $post['timetofullload'] : null),
		mysql_real_escape_string(array_key_exists('reqnumber', $post) ? $post['reqnumber'] : null),
		mysql_real_escape_string(array_key_exists('xhrnumber', $post) ? $post['xhrnumber'] : null),
		mysql_real_escape_string(array_key_exists('pagesize', $post) ? $post['pagesize'] : null),
		mysql_real_escape_string(array_key_exists('cachablesize', $post) ? $post['cachablesize'] : null),
		mysql_real_escape_string(array_key_exists('noncachablesize', $post) ? $post['noncachablesize'] : null),
		mysql_real_escape_string(array_key_exists('timeonnetwork', $post) ? $post['timeonnetwork'] : null),
		mysql_real_escape_string(array_key_exists('timeinjs', $post) ? $post['timeinjs'] : null),
		mysql_real_escape_string(array_key_exists('timeinrendering', $post) ? $post['timeinrendering'] : null),
		mysql_real_escape_string($post_data)
	);

	if (!mysql_query($query))
	{
		beaconError(mysql_error());
	}

	updateUrlAggregates($url_id, mysql_insert_id());
} else {
	header('HTTP/1.0 400 Bad Request');

	?><html>
<head>
<title>Bad Request: dynaTrace AJAX Edition beacon</title>
</head>
<body>
<h1>dynaTrace AJAX Edition beacon</h1>
<p>This is <a href="http://ajax.dynatrace.com/">dynaTrace AJAX Edition</a> beacon entry point.</p>

<h1>Configure dynaTrace AJAX Edition</h1>
<p><b style="color: red">WARNING! Only use this beacon If you're OK with all your dynaTrace AJAX Edition data to be recorded by this instance of ShowSlow and displayed at <a href="<?php echo $showslow_base?>"><?php echo $showslow_base?></a><br/>You can also <a href="http://www.showslow.org/Installation_and_configuration">install ShowSlow on your own server</a> to limit the risk.</b></p>

<p>You can find dynaTrace configuration documentation <a href="http://www.showslow.org/Configuring_dynaTrace_AJAX_Edition">on our wiki</a>.</p>

<hr/>
<p><a href="../">&lt;&lt; back to the list of beacons</a></p>
</body></html>
<?php
	exit;
}

header('HTTP/1.0 204 Data accepted');
