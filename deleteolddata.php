<?
require_once('global.php');

if ($oldDataInterval > 0)
{
	# deleting old data for yslow v2
	$query = sprintf("DELETE FROM yslow2 WHERE timestamp < DATE_SUB(now(), INTERVAL '%s' DAY)",
		mysql_real_escape_string($oldDataInterval)
	);

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

	# updating latest values for the URL
	$query = 'DELETE urls FROM urls LEFT JOIN (SELECT DISTINCT url_id FROM yslow2) AS y ON urls.id = y.url_id WHERE y.url_id IS NULL';

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}
}
