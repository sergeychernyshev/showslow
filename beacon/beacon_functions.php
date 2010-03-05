<?php 
require_once(dirname(dirname(__FILE__)).'/global.php');

function getUrlId($url)
{
	global $limitURLs, $dropQueryStrings;

	if ($dropQueryStrings) {
		$drop = false;

		if (is_array($dropQueryStrings)) {
			foreach ($dropQueryStrings as $prefix) {
				if (substr($url, 0, strlen($prefix)) == $prefix) {
					$drop = true;
					break;
				}
			}
		} else {
			$drop = true;
		}

		if ($drop) {
			$querypos = strpos($url, '?');

			if ($querypos !== false) {
				$url = substr($url, 0, $querypos);
			}
		}
	}

	if ($limitURLs !== false && is_array($limitURLs)) {
		$matched = false;

		foreach ($limitURLs as $limitString) {
			// checking if string is a regex or just a prefix
			if (preg_match('/^[^a-zA-Z\\\s]/', $limitString))
			{
				if (preg_match($limitString, $url)) {
					$matched = true;
				}
			} else if (substr($url, 0, strlen($limitString)) == $limitString) {
				$matched = true;
				break;
			}
		}

		if (!$matched) {
			header('HTTP/1.0 400 Bad Request');

			?><html>
<head>
<title>Bad Request: ShowSlow beacon</title>
</head>
<body>
<h1>Bad Request: ShowSlow beacon</h1>
<p>URL doesn't match any URLs allowed allowed to this instance.</p>
</body></html>
<?php 
			exit;
		}
	}

	# get URL id
	$query = sprintf("SELECT id FROM urls WHERE url = '%s'", mysql_real_escape_string($url));
	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

	if (mysql_num_rows($result) == 1) {
		$row = mysql_fetch_assoc($result);
		return $row['id'];
	} else if (mysql_num_rows($result) == 0) {
		$query = sprintf("INSERT INTO urls (url) VALUES ('%s')", mysql_real_escape_string($url));
		$result = mysql_query($query);

		if (!$result) {
			error_log(mysql_error());
			exit;
		}

		return mysql_insert_id();
	} else {
		error_log('more then one entry found for the URL');
		exit;
	}

}
