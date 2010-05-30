<?php 
require_once('global.php');

if ($oldDataInterval > 0)
{
	# deleting old data for custom metrics 
	$query = sprintf("DELETE FROM metric WHERE timestamp < DATE_SUB(now(), INTERVAL '%s' DAY)",
		mysql_real_escape_string($oldDataInterval)
	);

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

	# deleting old data for yslow v2
	$query = sprintf("DELETE FROM yslow2 WHERE timestamp < DATE_SUB(now(), INTERVAL '%s' DAY)",
		mysql_real_escape_string($oldDataInterval)
	);

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

	# deleting old data for pagespeed
	$query = sprintf("DELETE FROM pagespeed WHERE timestamp < DATE_SUB(now(), INTERVAL '%s' DAY)",
		mysql_real_escape_string($oldDataInterval)
	);

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

	# deleting old data for dynatrace  
	$query = sprintf("DELETE FROM dynatrace WHERE timestamp < DATE_SUB(now(), INTERVAL '%s' DAY)",
		mysql_real_escape_string($oldDataInterval)
	);

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

	# deleting old URLs
	$query = 'DELETE urls FROM urls
		LEFT JOIN (SELECT DISTINCT url_id FROM yslow2) AS y ON urls.id = y.url_id
		LEFT JOIN (SELECT DISTINCT url_id FROM pagespeed) AS p ON urls.id = p.url_id
		LEFT JOIN (SELECT DISTINCT url_id FROM dynatrace) AS d ON urls.id = d.url_id
		LEFT JOIN (SELECT DISTINCT url_id FROM metric) AS m ON urls.id = m.url_id
		LEFT JOIN (SELECT DISTINCT url_id FROM user_urls) AS uu ON urls.id = uu.url_id
		WHERE	y.url_id IS NULL
			AND p.url_id IS NULL
			AND d.url_id IS NULL
			AND m.url_id IS NULL
			AND uu.url_id IS NULL';

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

	# resetting last_updated for URLs that have no measurements
	$query = 'UPDATE urls
		LEFT JOIN (SELECT DISTINCT url_id FROM yslow2) AS y ON urls.id = y.url_id
		LEFT JOIN (SELECT DISTINCT url_id FROM pagespeed) AS p ON urls.id = p.url_id
		LEFT JOIN (SELECT DISTINCT url_id FROM dynatrace) AS d ON urls.id = d.url_id
		LEFT JOIN (SELECT DISTINCT url_id FROM metric) AS m ON urls.id = m.url_id
		SET last_update = NULL, yslow2_last_id = NULL, pagespeed_last_id = NULL
		WHERE y.url_id IS NULL AND p.url_id IS NULL AND m.url_id IS NULL';

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

	# deleting old events
	$query = sprintf("DELETE FROM event WHERE (end IS NOT NULL AND end < DATE_SUB(now(), INTERVAL '%s' DAY)) OR (start < DATE_SUB(now(), INTERVAL '%s' DAY))",
		mysql_real_escape_string($oldDataInterval),
		mysql_real_escape_string($oldDataInterval)
	);

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}
}
