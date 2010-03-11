<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/global.php');
require_once(dirname(dirname(__FILE__)).'/beacon_functions.php');

// check if manual upload was used
if (array_key_exists('har', $_FILES))
{
	$filename = $_FILES["har"]["tmp_name"];
} else {
	$filename = "php://input";
}

if ($filename == '') {
	header('HTTP/1.0 400 Bad Request');

?><html>
<head>
<title>Bad Request: no HAR data</title>
</head>
<body>
<h1>Bad Request: no HAR data</h1>
No HAR data submitted
</body>
</html><?php
	exit;
}

$har_data = file_get_contents($filename);

if ($har_data === FALSE || json_decode($har_data) === FALSE) {
	header('HTTP/1.0 400 Bad Request');

?><html>
<head>
<title>Bad Request: malformed HAR data</title>
</head>
<body>
<h1>Bad Request: malformed HAR data</h1>
Can't parse JSON data from HAR
</body>
</html><?php
	exit;
}

if (array_key_exists('url', $_REQUEST) && filter_var(urldecode($_REQUEST['url']), FILTER_VALIDATE_URL) !== false)
{
	$url_id = getUrlId(urldecode($_REQUEST['url']));

	# adding new entry

	if (array_key_exists('timestamp', $_REQUEST))
	{
		$query = sprintf("/* HAR POST */ INSERT INTO har (timestamp, url_id, har, compressed)
		VALUES ('%s', '%d', '%s', '%d')",
			mysql_real_escape_string($_REQUEST['timestamp']),
			mysql_real_escape_string($url_id),
			mysql_real_escape_string(defined('FORCE_GZIP') ? gzcompress($har_data) : $har_data),
			mysql_real_escape_string(defined('FORCE_GZIP') ? 1 : 0)
		);
	}
	else
	{
		$query = sprintf("/* HAR POST */ INSERT INTO har (url_id, har, compressed)
		VALUES ('%d', '%s', '%d')",
			mysql_real_escape_string($url_id),
			mysql_real_escape_string(defined('FORCE_GZIP') ? gzcompress($har_data) : $har_data),
			mysql_real_escape_string(defined('FORCE_GZIP') ? 1 : 0)
		);
	}


	if (!mysql_query($query))
	{
		error_log(mysql_error());
		exit;
	}
} else {
	header('HTTP/1.0 400 Bad Request');

	?><html>
<head>
<title>Bad Request: HAR beacon</title>
</head>
<body>
<h1>Bad Request: HAR beacon</h1>
<p>This is <a href="http://groups.google.com/group/firebug-working-group/web/http-tracing---export-format">HAR</a> beacon entry point.</p>

<h1>Configure your HAR provider</h1>
<p><b style="color: red">WARNING! Only use this beacon If you're OK with all your HAR data to be recorded by this instance of ShowSlow and displayed at <a href="<?php echo $showslow_base?>"><?php echo $showslow_base?></a><br/>All your data including cookies, IP addresses, sessions and possibly other sensitive information will be displayed on this instance.<br/>You can also <a href="http://www.showslow.org/Installation_and_configuration">install ShowSlow on your own server</a> to limit the risk.</b></p>
<p>To submit a beacon, you must send HAR file as a POST body or upload it as a file using form below.</p>
<p>There is also a <tt>url</tt> parameter that you have to supply and optional <tt>timestamp</tt> parameter.</p>

<p>Beacon URL: <b style="color: blue"><?php echo $showslow_base?>beacon/har/</b></p>

<h2>You can use on of these HAR providers</h2>
<ul>
<li><a href="http://getfirebug.com/releases/extensions.html#netexport">NetExport</a> extension for Firebug</li>
</ul>
<?php
if (!$enableHARBeacon) {
	?><p style="color: red">HAR beacon is disabled on this instance of ShowSlow.<br/>Add <tt>$enableHARBeacon = true;</tt> to your configuration file to enable it.</p><?php
}
?>
<h1>Submit HAR manually</h1>
<form action="" method="POST" enctype="multipart/form-data">
<table>
<tr><td>URL:</td><td><input type="text" name="url" value="http://www.example.com/" size="80"<?php if (!$enableHARBeacon) {?> disabled="disabled"<?php } ?>/></td></tr>
<tr valign="top"><td>Time:</td><td><input type="text" name="timestamp" size="25" value="<?php echo date("Y-m-d H:i:s");?>"<?php if (!$enableHARBeacon) {?> disabled="disabled"<?php }?>/><br/>Time in MySQL <a href="http://dev.mysql.com/doc/refman/5.1/en/datetime.html">timestamp format</a></td></tr>
<tr><td>Pick HAR file:</td><td><input name="har" type="file"<?php if (!$enableHARBeacon) {?> disabled="disabled"<?php }?>/></td></tr>
<tr><td></td><td><input type="submit" value="add"<?php if (!$enableHARBeacon) {?> disabled="disabled"<?php }?>/></td></tr>

</table>
</form>

</body></html>
<?php 
}
