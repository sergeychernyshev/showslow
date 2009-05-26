<?
require_once('../../global.php');

if (array_key_exists('title', $_GET) && $_GET['title'] != ''
	&& array_key_exists('url_prefix', $_GET) && filter_var($_GET['url_prefix'], FILTER_VALIDATE_URL) !== false
	)
{
	$type = array_key_exists('type', $_GET) && $_GET['type'] != '' ? $_GET['type'] : FALSE;
	$start = array_key_exists('start', $_GET) && $_GET['start'] != '' ? $_GET['start'] : FALSE;
	$end = array_key_exists('end', $_GET) && $_GET['type'] != '' ? $_GET['end'] : FALSE;
	$resource_url = filter_var($_GET['resource_url'], FILTER_VALIDATE_URL);

	$query = sprintf('INSERT INTO event (
			url_prefix,
			title'
			.($type !== FALSE ? ', type' : '')
			.($start !== FALSE ? ', start' : '')
			.($end !== FALSE ? ', end' : '')
			.($resource_url !== FALSE ? ', resource_url' : '')
		.") VALUES (
			'%s',
			'%s'"
			.($type !== FALSE ? ", '%s'" : '')
			.($start !== FALSE ? ", '%s'" : '')
			.($end !== FALSE ? ", '%s'" : '')
			.($resource_url !== FALSE ? ", '%s'" : '')
		.')',
		mysql_real_escape_string($_GET['url_prefix']),
		mysql_real_escape_string($_GET['title']),
		mysql_real_escape_string($type),
		mysql_real_escape_string($start),
		mysql_real_escape_string($end),
		mysql_real_escape_string($resource_url)
	);

	error_log($query);

	if (!mysql_query($query))
	{
		error_log(mysql_error());
		exit;
	}

	# updating last_event_update for the matching URLs
	$query = sprintf("UPDATE urls SET last_event_update = NOW() WHERE INSTR(url, '%s') = 1",
		mysql_real_escape_string(mysql_real_escape_string($_GET['url_prefix']))
	);
	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}
	?><html>
<head>
<title>Event added</title>
</head>
<body>
<h1>Event added</h1>
<p>Event successfully added.</p>
<p>Add <a href="./">one more</a>.</p>
</body></html>
<?

} else {
	header('HTTP/1.0 400 Bad Request');

	?><html>
<head>
<title>Bad Request: Event beacon</title>
</head>
<body>
<h1>Bad Request: Event beacon</h1>
<p>This is an event beacon for ShowSlow.</p>
<p>You can use automated script to publish events using GET call to this URL:</p>
<b><pre>/beacon/events/?type=<i>sometype</i>&amp;url_prefix=<i>url_prefix</i>&amp;title=<i>some+title</i>&amp;start=<i><?=urlencode(date("Y-m-d"))?></i>&amp;end=<i><?=urlencode(date("Y-m-d"))?></i>&amp;resource_url=<i>link+to+page</i></pre></b>
or use form below to manually enter events.

<h2>Add an event</h2>
<form action="" method="GET">
<table>
<tr><td>Type:</td><td><input type="text" name="type" size="25"/> (25 characters max)</td></tr>
<tr><td>URL prefix:</td><td><input type="text" name="url_prefix" value="http://www.example.com/" size="80"/></td></tr>
<tr><td>Event title:</td><td><input type="text" name="title" size="80"/></td></tr>
<tr valign="top"><td>Start time:</td><td><input type="text" name="start" value="<?=date("Y-m-d H:i:s");?>"/> (leave blank for current time)<br/>Time in MySQL <a href="http://dev.mysql.com/doc/refman/5.1/en/datetime.html">timestamp format</a></td></tr>
<tr valign="top"><td>End time:</td><td><input type="text" name="end"/> (or blank for momentary event)<br/>Time in MySQL <a href="http://dev.mysql.com/doc/refman/5.1/en/datetime.html">timestamp format</a></td></tr>
<tr><td>Resource URL:</td><td><input type="text" name="resource_url" size="80"/></td></tr>
<tr><td></td><td><input type="submit" value="add"/></td></tr>

</table>
</form>
</body></html>
<?
}
