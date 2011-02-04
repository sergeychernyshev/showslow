<?php 
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

$all = true;

$query = sprintf("SELECT type, title, UNIX_TIMESTAMP(start) as s, UNIX_TIMESTAMP(end) as e, resource_url as link FROM event
	WHERE INSTR('%s', url_prefix) = 1
	ORDER BY start DESC",
	mysql_real_escape_string($_GET['url'])
);

$result = mysql_query($query);

if (!$result) {
        error_log(mysql_error());
}

$data = array();

header('Content-type: text/xml');
if (array_key_exists('ver', $_GET)) {
	header('Expires: '.date('r', time() + 315569260));
	header('Cace-control: max-age=315569260');
}
$xml = new SimpleXMLElement('<data/>');

while ($row = mysql_fetch_assoc($result)) {
	$event = $xml->addChild('event');
	$event->addAttribute('start', date('r', $row['s']));
	$event->addAttribute('latestStart', date('r', $row['s']));
	$event->addAttribute('title', ($row['type'] ? $row['type'].': ' : '').$row['title']);

	$end = $row['e'];

	if (!$row['e'])
	{
		$end = $row['s'];
	}

	$event->addAttribute('end', date('r', $end));
	$event->addAttribute('earliestEnd', date('r', $end));

	if ($row['link'])
	{
		$event->addAttribute('link', $row['link']);
	}
}
mysql_free_result($result);

echo $xml->asXML();
