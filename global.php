<?php 
# change it if you want to allow other profiles including your custom profiles
$YSlow2AllowedProfiles = array('ydefault');

# If not false, then should be an array of prefix matches - if one of them matches, URL will be accepted
$limitURLs = false;

# Track non-http(s) URLs. Disabled by default
$enableNonHTTPURLs = false;

# URL groups to be displayed on URLs measured tab
$URLGroups = array();

# Ignore URLs matching the prefix or a regext. If one of them matches, URLs is going to be ignored
# You might want to remove 10.x, 192.168.x and 172.16-32.x if you're testing web sites on a private network.
$ignoreURLs = array(
	'http://127.0.0.',
	'http://localhost/',
	'http://localhost:',
	'http://10.',
	'http://192.168.',
	'http://172.16.',
	'http://172.17.',
	'http://172.18.',
	'http://172.19.',
	'http://172.20.',
	'http://172.21.',
	'http://172.22.',
	'http://172.23.',
	'http://172.24.',
	'http://172.25.',
	'http://172.26.',
	'http://172.27.',
	'http://172.28.',
	'http://172.29.',
	'http://172.30.',
	'http://172.31.'
);

# If set to true, drop all query strings. If array, then match prefixes.
$dropQueryStrings = false;

# Custom metrics array
$metrics = array();

# to see if your users are visiting the tool, enable Google Analytics
# (for publicly hosted instances)
$googleAnalyticsProfile = '';

# show Feedback button
$showFeedbackButton = true;

# AddThis profile, set it to enable sharing functions
$addThisProfile = null;

# how old should data be for deletion (in days)
# anything >0 will delete old data
# don't forget to add a cron job to run deleteolddata.php
$oldDataInterval = 60;

$homePageMetaTags = '';

# this enables a form to run a test on WebPageTest.org
$webPageTestBase = 'http://www.webpagetest.org/';
require_once(dirname(__FILE__).'/pagetestlocations.php');
$webPageTestPrivateByDefault = false;
$webPageTestFirstRunOnlyByDefault = false;
$keepPrivatePageTests = false;

# a list of URLs to compare by default. Set to NULL to not send any URLs
# $defaultURLsToCompare = array('http://www.google.com/', 'http://www.yahoo.com/', 'http://www.amazon.com/');
$defaultURLsToCompare = NULL;

# Change this to 'pagespeed' to use it for comparison by default
$defaultRankerToCompare = 'yslow';

# Enabling HAR beacon will allow storing HAR data for URLs and display graphically using HAR viewer
$enableHARBeacon = false;

# HAR Viewer base URL
$HARViewerBase = 'http://www.softwareishard.com/har/viewer/';

# Enable user URL monitoring
$enableMyURLs = false;

# Maximum URLs each user can add to the system to be monitored (false means no limit)
$maxURLsPerUser = false;

# Message to show the user when he riches the maximum
$maxURLsMessage = 'The number of URLs tracked is limited because of load constraints.';

# Privileged users who has no limit on URLs
$noMaxURLsForUsers = array();

# how long should monitoring scripts wait between measurements (in hours).
$monitoringPeriod = 24;

# Facebook connect properties, configure them here:
# http://www.facebook.com/developers/createapp.php
$facebookAPIKey = null;
$facebookSecret = null;

# Google Friend connect site ID
# get it from the URL's "id" parameter on Google Friend Connect admin page for the site:
# http://www.google.com/friendconnect/admin/
$googleFriendConnectSiteID = null;

# Smoothing distance (averaging window will be from x-distance to x+distance)
$smoothDistance = 5;

require_once(dirname(__FILE__).'/asset_versions.php');
require_once(dirname(__FILE__).'/svn-assets/asset_functions.php');

# Put description for ShowSlow instance into this variable - it'll be displayed on home page under the header.
$ShowSlowIntro = '<p>Show Slow is an open source tool that helps monitor various website performance metrics over time. It captures the results of <a href="http://developer.yahoo.com/yslow/">YSlow</a> and <a href="http://code.google.com/speed/page-speed/">Page Speed</a> rankings and graphs them, to help you understand how various changes to your site affect its performance.</p>

<p><a href="http://www.showslow.com/">www.ShowSlow.com</a> is a demonstration site that continuously measures the performance of a few reference web pages. It also allows for public metrics reporting.</p>

<p>If you want to make your measurements publicly available on this page, see the instructions in <a href="configure.php">Configuring YSlow / Page Speed</a>. If you want to keep your measurements private, <b><a href="http://code.google.com/p/showslow/source/checkout">download Show Slow</a></b> from the SVN repository and install it on your own server.</p>

<p>You can ask questions and discuss ShowSlow in our group <a href="http://groups.google.com/group/showslow">http://groups.google.com/group/showslow</a> or just leave feedback at <a href="http://showslow.uservoice.com">http://showslow.uservoice.com</a></p>

<table cellpadding="0" cellspacing="0" border="0"><tr>
<td valign="top"><style>
#twitterbutton {
	margin-right: 7px;
	width: 58px;
	height: 23px;
	display: block;
	background-image: url('.assetURL('follow.png').');
	background-position: 0px 0px;
}
#twitterbutton:hover {
	background-position: 0px -46px;
}
</style><a id="twitterbutton" href="http://twitter.com/showslow" target="_blank" title="follow @showslow on twitter"/></a></td>
<td valign="top">
<iframe src="http://www.facebook.com/plugins/like.php?href=http%253A%252F%252Fwww.showslow.com%252F&amp;layout=standard&amp;show_faces=false&amp;width=450&amp;action=recommend&amp;font&amp;colorscheme=light&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:23px;" allowTransparency="true"></iframe>
</td>
</tr></table>';

# configuring tabs
$customLists = array();

# config will override defaults above
require_once(dirname(__FILE__).'/config.php');

function yslowPrettyScore($num) {
	$letter = 'F';

	if ( 90 <= $num )
		$letter = 'A';
	else if ( 80 <= $num )
		$letter = 'B';
	else if ( 70 <= $num )
		$letter = 'C';
	else if ( 60 <= $num )
		$letter = 'D';
	else if ( 50 <= $num )
		$letter = 'E';

	return $letter;
}

function scoreColorStep($num, $total = 13) {
	for($i=1; $i<=$total; $i++)
	{
		if ($num <= $i*100/$total)
		{
			return $i;
		} 
	}
}

$colorSteps = array(
	'EE0000',
	'EE2800',
	'EE4F00',
	'EE7700',
	'EE9F00',
	'EEC600',
	'EEEE00',
	'C6EE00',
	'9FEE00',
	'77EE00',
	'4FEE00',
	'28EE00',
	'00EE00'
);

function scoreColor($num) {
	global $colorSteps;
	return '#'.$colorSteps[scoreColorStep($num, count($colorSteps))-1];
}

# returns true if URL should be ignored
function isURLIgnored($url) {
	global $ignoreURLs;

	if ($ignoreURLs !== false && is_array($ignoreURLs)) {
		$matched = false;

		foreach ($ignoreURLs as $ignoreString) {
			// checking if string is a regex or just a prefix
			if (preg_match('/^[^a-zA-Z\\\s]/', $ignoreString))
			{
				if (preg_match($ignoreString, $url)) {
					$matched = true;
				}
			} else if (substr($url, 0, strlen($ignoreString)) == $ignoreString) {
				$matched = true;
				break;
			}
		}

		return $matched;
	}

	return false;
}

# returns true if URL is in the limitedURLs array or all URLs are allowed
function isURLAllowed($url) {
	global $limitURLs;

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

		return !$matched;
	}
	return true;
}

# returns true if this URLS is not an HTTP URL and should be ignored
function shouldBeIgnoredAsNonHTTP($url) {
	global $enableNonHTTPURLs;

	return (
		!$enableNonHTTPURLs &&
		(substr(strtolower($url), 0, 7) != 'http://' &&	substr(strtolower($url), 0, 8) != 'https://')
	);
}

# returns URL if it's valid and passes all checks or null if not.
# if second parameter is true (default), then 404 error page is shown
#
# used in getUrlId and event beacon (which tests prefix, not URL)
#
# TODO rewrite to use exceptions instead of $outputerror contraption
function validateURL($url, $outputerror = true) {
	$url = filter_var(urldecode(trim($url)), FILTER_VALIDATE_URL);

	if ($url === FALSE) {
		if (!$outputerror) {
			return null;
		}

		header('HTTP/1.0 400 Bad Request');

		?><html>
<head>
<title>Bad Request: ShowSlow beacon</title>
</head>
<body>
<h1>Bad Request: ShowSlow beacon</h1>
<p>Invalid URL submitted.</p>
</body></html>
<?php 
		exit;
	}

	if (shouldBeIgnoredAsNonHTTP($url)) {
		if (!$outputerror) {
			return null;
		}

		header('HTTP/1.0 400 Bad Request');

		?><html>
<head>
<title>Bad Request: ShowSlow beacon</title>
</head>
<body>
<h1>Bad Request: ShowSlow beacon</h1>
<p>This instance of Show Slow only tracks HTTP(S) URLs.</p>
</body></html>
<?php 
		exit;
	}

	if (isURLIgnored($url)) {
		if (!$outputerror) {
			return null;
		}

		header('HTTP/1.0 400 Bad Request');

		?><html>
<head>
<title>Bad Request: ShowSlow beacon</title>
</head>
<body>
<h1>Bad Request: ShowSlow beacon</h1>
<p>This URL matched ignore list for this instance of Show Slow.</p>
</body></html>
<?php 
		exit;
	}

	if (!isURLAllowed($url)) {
		if (!$outputerror) {
			return null;
		}

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

	return $url;
}

function getUrlId($url, $outputerror = true)
{
	global $dropQueryStrings;

	$url = validateURL($url, $outputerror);

	if (is_null($url)) {
		return null;
	}

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

	# get URL id
	$query = sprintf("SELECT id FROM urls WHERE url = '%s'", mysql_real_escape_string($url));
	$result = mysql_query($query);

	if (!$result) {
		beaconError(mysql_error());
	}

	if (mysql_num_rows($result) == 1) {
		$row = mysql_fetch_assoc($result);
		return $row['id'];
	} else if (mysql_num_rows($result) == 0) {
		// Emulating unique index on a blob with unlimited length by locking the table on write
		// locking only when we're about to insert so we don't block the whole thing on every read

		// locking the table to make sure we pass it only by one concurrent process
		$result = mysql_query('LOCK TABLES urls WRITE');
		if (!$result) {
			beaconError(mysql_error());
		}

		// selecting the URL again to make sure there was no concurrent insert for this URL
		$query = sprintf("SELECT id FROM urls WHERE url = '%s'", mysql_real_escape_string($url));
		$result = mysql_query($query);
		if (!$result) {
			$mysql_err = mysql_error();
			mysql_query('UNLOCK TABLES'); // unlocking the table if in trouble
			beaconError($mysql_err);
		}

		// repeating the check
		if (mysql_num_rows($result) == 1) {
			$row = mysql_fetch_assoc($result);
			$url_id = $row['id'];
		} else if (mysql_num_rows($result) == 0) {
			$query = sprintf("INSERT INTO urls (url) VALUES ('%s')", mysql_real_escape_string($url));
			$result = mysql_query($query);
			if (!$result) {
				$mysql_err = mysql_error();
				mysql_query('UNLOCK TABLES'); // unlocking the table if in trouble
				beaconError($mysql_err);
			}

			$url_id = mysql_insert_id();
		} else if (mysql_num_rows($result) > 1) {
			mysql_query('UNLOCK TABLES'); // unlocking the table if in trouble
			beaconError('more then one entry found for the URL (when lock is aquired)');
		}

		$result = mysql_query('UNLOCK TABLES'); // now concurrent thread can try reading again
		if (!$result) {
			beaconError(mysql_error());
		}

		return $url_id;
	} else {
		beaconError('more then one entry found for the URL');
	}
}

// httpd_build_url replacement from http://www.mediafire.com/?zjry3tynkg5
// added base function feature that allows to pass an array as first parameter
if (!function_exists('http_build_url'))
{
	define('HTTP_URL_REPLACE', 1);	// Replace every part of the first URL when there's one of the second URL
	define('HTTP_URL_JOIN_PATH', 2); 	// Join relative paths
	define('HTTP_URL_JOIN_QUERY', 4);	// Join query strings
	define('HTTP_URL_STRIP_USER', 8);	// Strip any user authentication information
	define('HTTP_URL_STRIP_PASS', 16);	// Strip any password authentication information
	define('HTTP_URL_STRIP_AUTH', 32);	// Strip any authentication information
	define('HTTP_URL_STRIP_PORT', 64);	// Strip explicit port numbers
	define('HTTP_URL_STRIP_PATH', 128);	// Strip complete path
	define('HTTP_URL_STRIP_QUERY', 256);	// Strip query string
	define('HTTP_URL_STRIP_FRAGMENT', 512);	// Strip any fragments (#identifier)
	define('HTTP_URL_STRIP_ALL', 1024);	// Strip anything but scheme and host
	
	// Build an URL
	// The parts of the second URL will be merged into the first according to the flags argument. 
	// 
	// @param mixed	(Part(s) of) an URL in form of a string or associative array like parse_url() returns
	// @param mixed	Same as the first argument
	// @param int	A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
	// @param array	If set, it will be filled with the parts of the composed url like parse_url() would return 
	function http_build_url($url, $parts=array(), $flags=HTTP_URL_REPLACE, &$new_url=false)
	{
		$keys = array('user','pass','port','path','query','fragment');
		
		// HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
		if ($flags & HTTP_URL_STRIP_ALL)
		{
			$flags |= HTTP_URL_STRIP_USER;
			$flags |= HTTP_URL_STRIP_PASS;
			$flags |= HTTP_URL_STRIP_PORT;
			$flags |= HTTP_URL_STRIP_PATH;
			$flags |= HTTP_URL_STRIP_QUERY;
			$flags |= HTTP_URL_STRIP_FRAGMENT;
		}
		// HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
		else if ($flags & HTTP_URL_STRIP_AUTH)
		{
			$flags |= HTTP_URL_STRIP_USER;
			$flags |= HTTP_URL_STRIP_PASS;
		}
		
		// Parse the original URL
		if (is_array($url)) {
			$parse_url = $url;
		} else {
			$parse_url = parse_url($url);
		}
		
		// Scheme and Host are always replaced
		if (isset($parts['scheme']))
			$parse_url['scheme'] = $parts['scheme'];
		if (isset($parts['host']))
			$parse_url['host'] = $parts['host'];
		
		// (If applicable) Replace the original URL with it's new parts
		if ($flags & HTTP_URL_REPLACE)
		{
			foreach ($keys as $key)
			{
				if (isset($parts[$key]))
					$parse_url[$key] = $parts[$key];
			}
		}
		else
		{
			// Join the original URL path with the new path
			if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH))
			{
				if (isset($parse_url['path']))
					$parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
				else
					$parse_url['path'] = $parts['path'];
			}
			
			// Join the original query string with the new query string
			if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY))
			{
				if (isset($parse_url['query']))
					$parse_url['query'] .= '&' . $parts['query'];
				else
					$parse_url['query'] = $parts['query'];
			}
		}

			
		// Strips all the applicable sections of the URL
		// Note: Scheme and Host are never stripped
		foreach ($keys as $key)
		{
			if ($flags & (int)constant('HTTP_URL_STRIP_' . strtoupper($key)))
				unset($parse_url[$key]);
		}
		
		$new_url = $parse_url;
		
		return 
			 ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
			.((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') .'@' : '')
			.((isset($parse_url['host'])) ? $parse_url['host'] : '')
			.((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
			.((isset($parse_url['path'])) ? $parse_url['path'] : '')
			.((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
			.((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '')
		;
	}
}

function resolveRedirects($url) {
	if (function_exists('curl_init')) {
		$ch = curl_init($url);

		curl_setopt_array($ch, array(
			CURLOPT_NOBODY => TRUE,
			CURLOPT_FOLLOWLOCATION => TRUE,
			CURLOPT_MAXREDIRS => 10
		));

		if (curl_exec($ch)) {
			$new_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

			# TODO also test for success code
			# TODO maybe, fix www. when it's missing.

			if ($new_url) {
				$url = $new_url;
			}
		}
	}

	// now, let's fix trailing slash in case of domain-only request
	$urlparts = parse_url($url);
	if (!array_key_exists('path', $urlparts) || $urlparts['path'] == '') {
		$urlparts['path'] = '/';
	}

	$new_url = http_build_url($urlparts);
	if ($new_url) {
		$url = $new_url;
	}

	return $url;
}

function failWithMessage($message)
{
	error_log("[Page Error] ".$message);
	header('HTTP/1.0 500 ShowSlow Error');
	?>
<head>
<title>500 ShowSlow Error</title>
</head>
<body>
<h1>500 ShowSlow Error</h1>
<p>Something went wrong. If it persists, please report it to <a href="http://code.google.com/p/showslow/issues/list">issue tracker</a>.</a>
<p><?php echo $message?></p>
</body></html>
<?php
	exit;
}

function beaconError($message)
{
	error_log($message);
	header('HTTP/1.0 500 Beacon Error');
	?>
<head>
<title>500 Beacon Error</title>
</head>
<body>
<h1>500 BeaconError</h1>
<p><?php echo $message?></p>
</body></html>
<?php
	exit;
}

mysql_connect($host, $user, $pass);
mysql_select_db($db);
