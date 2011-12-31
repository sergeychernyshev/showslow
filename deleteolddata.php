<?php require_once('global.php');

if ($oldDataInterval > 0) {
	$tables = array(
		'har',
		'metric',
		'yslow2',
		'pagespeed',
		'pagetest',
		'dommonster',
		'dynatrace'
	);

	foreach ($tables as $table) {
		# deleting old data
		$query = sprintf("DELETE FROM $table WHERE timestamp < DATE_SUB(now(), INTERVAL '%s' DAY)",
			mysql_real_escape_string($oldDataInterval)
		);

		$result = mysql_query($query);

		if (!$result) {
			error_log(mysql_error());
			exit;
		}
	}

	# deleting URLs with no measurements that are not requested to be tracked by users
	$query = 'DELETE urls FROM urls';

	foreach ($tables as $table) {
		$query .= "\nLEFT JOIN (SELECT DISTINCT url_id FROM $table) AS x_$table
				ON urls.id = x_$table.url_id";
	}
	$query .= "\nLEFT JOIN (SELECT DISTINCT url_id FROM user_urls) AS uu ON urls.id = uu.url_id";
	$query .= "\nWHERE\n";

	$first = true;
	foreach ($tables as $table) {
		if ($first) {
			$first = false;
		} else {
			$query .= "AND ";
		}

		$query .= "x_$table.url_id IS NULL\n";
	}

	$query .= 'AND uu.url_id IS NULL';

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

	# resetting last_updated for URLs that have no measurements
	$query = "UPDATE urls";
	foreach ($tables as $table) {
		$query .= "\nLEFT JOIN (SELECT DISTINCT url_id FROM $table) AS x_$table
				ON urls.id = x_$table.url_id";
	}

	$query .= "\nSET last_update = NULL";

	foreach ($tables as $table) {
		$query .= ",\n$table"."_last_id = NULL";
	}
	$query .= "\nWHERE\n";

	$first = true;
	foreach ($tables as $table) {
		if ($first) {
			$first = false;
		} else {
			$query .= "AND ";
		}

		$query .= "x_$table.url_id IS NULL\n";
	}

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

	# deleting old events separately as they match broadly, not per URL
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
