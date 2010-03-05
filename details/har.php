<?php 
require_once(dirname(dirname(__FILE__)).'/global.php');

if (!array_key_exists('id', $_GET) && (!array_key_exists('url', $_GET) || filter_var($_GET['url'], FILTER_VALIDATE_URL) === false)) {
	header('HTTP/1.0 400 Bad Request');

	?><html>
<head>
<title>Bad Request: no valid url or har id specified</title>
</head>
<body>
<h1>Bad Request: no valid url or har id specified</h1>
<p>You must pass valid URL as 'url' parameter or HAR file ID as 'id' parameter</p>
</body></html>
<?php 
	exit;
}


if (array_key_exists('id', $_GET)) {
	$query = sprintf("SELECT har, compressed FROM har WHERE id = '%d'",
		mysql_real_escape_string($_GET['id'])
	);
}
else
{
	$query = sprintf("SELECT UNIX_TIMESTAMP(timestamp) as t, har, compressed
		FROM har, urls WHERE urls.url = '%s' AND har.url_id = urls.id ORDER BY timestamp DESC LIMIT 1",
		mysql_real_escape_string($_GET['url'])
	);
}

$result = mysql_query($query);

if (!$result) {
        error_log(mysql_error());
}

$harp = false;
if (array_key_exists('callback', $_GET)) {
	$harp = $_GET['callback'];

	if (!preg_match('/^[a-z]([a-z0-9\.]*[a-z0-9])?$/i', $harp)) {
		$harp = false;
	}
}

if ($row = mysql_fetch_assoc($result)) {
	header('Content-type: text/plain');
	if (array_key_exists('id', $_GET)) {
		header('Expires: '.date('r', time() + 315569260));
		header('Cache-control: max-age=315569260');
	}

	if ($harp) {
		echo $harp.'(';
	}

	echo $row['compressed'] ? gzuncompress($row['har']) : $row['har'];
 
	if ($harp) {
		echo ');';
	}
}
else
{
	header('HTTP/1.0 404 No HAR(P) found');

	?><html>
<head>
<title>404 No HAR<?php if ($harp) {?>P<?php } ?> found</title>
</head>
<body>
<h1>404 No HAR<?php if ($harp) {?>P<?php } ?> found</h1>
<p>No HAR<?php if ($harp) {?>P<?php } ?> data found</p>
</body></html>
<?php 
	exit;
}
