<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/global.php');

if (array_key_exists('title', $_GET) && $_GET['title'] != ''
	&& array_key_exists('url_prefix', $_GET) && filter_var($_GET['url_prefix'], FILTER_VALIDATE_URL) !== false
	)
{
	checkBeaconKey('events');

	$url = validateURL($_GET['url_prefix'], $outputerror);

	$type = array_key_exists('type', $_GET) && $_GET['type'] != '' ? $_GET['type'] : FALSE;
	$start = array_key_exists('start', $_GET) && $_GET['start'] != '' ? $_GET['start'] : FALSE;
	$end = array_key_exists('end', $_GET) && $_GET['type'] != '' ? $_GET['end'] : FALSE;
	$resource_url = filter_var($_GET['resource_url'], FILTER_VALIDATE_URL);

	$query = sprintf('INSERT INTO event (
			url_prefix,
			title,
			start'
			.($type !== FALSE ? ', type' : '')
			.($end !== FALSE ? ', end' : '')
			.($resource_url !== FALSE ? ', resource_url' : '')
		.") VALUES (
			'%s',
			'%s',
			'%s'"
			.($type !== FALSE ? ", '%s'" : '')
			.($end !== FALSE ? ", '%s'" : '')
			.($resource_url !== FALSE ? ", '%s'" : '')
		.')',
		mysql_real_escape_string($url),
		mysql_real_escape_string($_GET['title']),
		mysql_real_escape_string($start),
		mysql_real_escape_string($type),
		mysql_real_escape_string($end),
		mysql_real_escape_string($resource_url)
	);

	if (!mysql_query($query))
	{
		beaconError(mysql_error());
	}

	# updating last_event_update for the matching URLs
	$query = sprintf("UPDATE urls SET last_event_update = NOW() WHERE INSTR(url, '%s') = 1",
		mysql_real_escape_string($url)
	);
	$result = mysql_query($query);

	if (!$result) {
		beaconError(mysql_error());
	}

	if (array_key_exists('manual', $_GET))
	{
		?><html>
<head>
<title>Event added</title>
</head>
<body>
<h1>Event added</h1>
<p>Event successfully added.</p>
<p>Add <a href="./">one more</a>.</p>
</body></html>
<?php
		exit;
	}

} else {
	header('HTTP/1.0 400 Bad Request');

	?><html>
<head>
<title>Bad Request: Event beacon</title>
<style>
i {
	color: red;
}
</style>
</head>
<body>
<h1>Event beacon</h1>
<p>This is an event beacon for ShowSlow.</p>
<p>You can use automated script to publish events using GET call to this URL:</p>
<b><pre><?php echo $showslow_base?>beacon/events/?type=<i>sometype</i>&amp;url_prefix=<i>url_prefix</i>&amp;title=<i>some+title</i>&amp;start=<i><?php echo urlencode(date("Y-m-d"))?></i>&amp;end=<i><?php echo urlencode(date("Y-m-d"))?></i>&amp;resource_url=<i>link+to+page</i></pre></b>
or use form below to manually enter events.

<h2>Add an event</h2>
<form action="" method="GET">
<table>
<tr><td>Type:</td><td><input type="text" name="type" size="25"/> (25 characters max)</td></tr>
<tr><td>URL prefix:</td><td><input type="text" name="url_prefix" value="http://www.example.com/" size="80"/></td></tr>
<tr><td>Event title:</td><td><input type="text" name="title" size="80"/></td></tr>
<tr valign="top"><td>Start time:</td><td><input type="text" name="start" value="<?php echo date("Y-m-d H:i:s");?>"/> (leave blank for current time)<br/>Time in MySQL <a href="http://dev.mysql.com/doc/refman/5.1/en/datetime.html">timestamp format</a></td></tr>
<tr valign="top"><td>End time:</td><td><input type="text" name="end"/> (or blank for momentary event)<br/>Time in MySQL <a href="http://dev.mysql.com/doc/refman/5.1/en/datetime.html">timestamp format</a></td></tr>
<tr><td>Resource URL:</td><td><input type="text" name="resource_url" size="80"/></td></tr>
<tr><td></td><td><input type="submit" name="manual" value="add"/></td></tr>

</table>
</form>

<hr/>
<p><a href="../">&lt;&lt; back to the list of beacons</a></p>
</body></html>
<?php
	exit;
}

header('HTTP/1.0 204 Data accepted');
