<?php 
require_once('global.php');

# cleaning up URLs that are supposed to be ignored
if ($ignoreURLs !== false && is_array($ignoreURLs)) {
	$prefixes = array();
	$regexes = array();

	foreach ($ignoreURLs as $ignoreString) {
		if (preg_match('/^[^a-zA-Z\\\s]/', $ignoreString))
		{
			$regexes[] = $ignoreString;
		}
		else
		{
			$prefixes[] = $ignoreString;
		}
	}

	$ids = array();

	if (count($prefixes) > 0 || !$enableNonHTTPURLs) {
		# prefix-matching URLs
		$query = 'SELECT id FROM urls WHERE ';
		$first = true;

		foreach ($prefixes as $prefix) {
			# safeguard in case prefix is empty
			if ($prefix == '') {
				continue;
			}

			if ($first) {
				$first = false;
			} else {
				$query .= ' OR ';
			}
			$query .= sprintf("LOCATE('%s', LOWER(url)) = 1", mysql_real_escape_string($prefix));
		}

		if (!$enableNonHTTPURLs) {
			if (!$first) {
				$query .= ' OR ';
			}
			$query .= "(LOCATE('http://', LOWER(url)) <> 1 AND LOCATE('https://', LOWER(url)) <> 1)";
		}

		$result = mysql_query($query);

		if (!$result) {
			error_log(mysql_error());
			exit;
		}

		while($row = mysql_fetch_assoc($result)) {
			$ids[] = $row['id'];
		}
	}

	# checking regexes - this takes time as we need to match every URL to every regex
	if (count($regexes) > 0) {
		$query = 'SELECT id, url FROM urls';
		$result = mysql_query($query);

		if (!$result) {
			error_log(mysql_error());
			exit;
		}

		while($row = mysql_fetch_assoc($result)) {
			$matched = false;
			foreach ($regexes as $regex) {
				if (preg_match($regex, $row['url'])) {
					$matched = true;
					break;
				}
			}

			if ($matched) {
				$ids[] = $row['id'];
			}
		}
	}

	# deleting data for custom metrics 
	$query = sprintf("DELETE FROM metric WHERE url_id IN (%s)", implode(', ', $ids));

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

	# deleting data for yslow v2
	$query = sprintf("DELETE FROM yslow2 WHERE url_id IN (%s)", implode(', ', $ids));

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

	# deleting data for pagespeed
	$query = sprintf("DELETE FROM pagespeed WHERE url_id IN (%s)", implode(', ', $ids));

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

	# deleting data for dynatrace  
	$query = sprintf("DELETE FROM dynatrace WHERE url_id IN (%s)", implode(', ', $ids));

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

	# resetting urls aggregates
	$query = sprintf("UPDATE urls SET last_update = NULL, yslow2_last_id = NULL, pagespeed_last_id = NULL, dynatrace_last_id = NULL WHERE id IN (%s)", implode(', ', $ids));

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}
}
