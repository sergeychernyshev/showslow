<?php
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/users/users.php');

$current_user = User::require_login();

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

if (array_key_exists('delete', $_POST) && is_array($_POST['delete'])) {
	$delete = array_keys($_POST['delete']);

	$first = true;
	$deleteids = '';
	foreach ($delete as $id) {
		if (!is_numeric($id)) {
			next;
		}

		if ($first) {
			$first = false;
		}
		else
		{
			$deleteids.=', ';
		}

		$deleteids.=$id;
	}

	if (!$first && $deleteids != '') {
		$query = sprintf("DELETE FROM user_urls WHERE user_id = %d AND url_id IN (%s)",
			$current_user->getID(),
			$deleteids
		);

		$result = mysql_query($query);

		if (!$result) {
			error_log(mysql_error());
		}
	}
	header('Location: '.$showslow_base.'my.php#deleted');
	exit;
}

if (in_array($current_user->getID(), $noMaxURLsForUsers)) {
	$maxURLsPerUser = false;
}

$noMoreURLs = false;
if ($maxURLsPerUser)
{
	$query = sprintf('SELECT count(*) AS cnt FROM urls INNER JOIN user_urls ON urls.id = user_urls.url_id
		WHERE user_urls.user_id = %d', $current_user->getID());

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
	}

	if ($cnt = mysql_fetch_row($result) && $cnt[0] >= $maxURLsPerUser)
	{
		$noMoreURLs = true;
	}
	mysql_free_result($result);
}

if (!$noMoreURLs && array_key_exists('url', $_REQUEST)) {
	require_once(dirname(__FILE__).'/beacon/beacon_functions.php');

	$url_id = getUrlId(resolveRedirects($_REQUEST['url']), false);

	if (is_null($url_id)) {
		header('Location: '.$showslow_base.'my.php#invalid');
		exit;
	}

	$query = sprintf("INSERT IGNORE INTO user_urls (user_id, url_id) VALUES (%d, %d)",
		$current_user->getID(),
		$url_id
	);

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
	}

	error_log($result);

	header('Location: '.$showslow_base.'my.php#added');
	exit;
}

$query = sprintf("SELECT urls.id as id, url, last_update,
		yslow2.o as o,
		pagespeed.o as ps_o,
		dynatrace.rank as dt_o
	FROM urls INNER JOIN user_urls ON urls.id = user_urls.url_id
		LEFT JOIN yslow2 ON urls.yslow2_last_id = yslow2.id
		LEFT JOIN pagespeed ON urls.pagespeed_last_id = pagespeed.id
		LEFT JOIN dynatrace ON urls.dynatrace_last_id = dynatrace.id
	WHERE user_urls.user_id = %d ORDER BY url", $current_user->getID());

$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$yslow = false;
$pagespeed = false;
$dynatrace = false;

$rows = array();
$colspan = 0;
while ($row = mysql_fetch_assoc($result)) {
	$rows[] = $row;

	if (!$yslow && !is_null($row['o'])) {
		$yslow = true;
		$colspan += 2;
	}
	if (!$pagespeed && !is_null($row['ps_o'])) {
		$pagespeed = true;
		$colspan += 2;
	}
	if (!$dynatrace && !is_null($row['dt_o'])) {
		$dynatrace = true;
		$colspan += 2;
	}
}

$TITLE = 'My URLs';
$SECTION = 'my';
require_once(dirname(__FILE__).'/header.php');
?>
<style>
td, th { white-space: nowrap; }

.score {
	text-align: right;
	padding: 0 10px 0 10px;
}

.gbox {
	background-color: silver;
	width: 101px;	
}

.url {
	padding-left:10px;
}
</style>
<h1 style="margin-bottom: 0">Add URLs to monitor</h1>
<div style="font-size: small; margin-bottom: 1em">User: <a href="users/edit.php"><?php echo $current_user->getName(); ?></a></div>

<p>If you don't want to <a href="<?php echo $showslow_base; ?>configure.php">run YSlow, Page Speed and dynaTrace on your desktop</a>, you can add a URL to the list below and it'll be measured automatically every <?php echo $monitoringPeriod ?> hours.</p>

<form action="" method="POST">
<?php
if ($noMoreURLs)
{
?>
<p><?php echo $maxURLsMessage; ?></p>
<div title="<?php echo htmlentities(strip_tags($maxURLsMessage)); ?>">Add URL: <input type="text" size="80" name="url" disabled="disabled"/><input type="submit" name="add" value="add" disabled="disabled"/></div>
<?php } else { ?>
Add URL: <input type="text" size="80" name="url"/><input type="submit" name="add" value="add" title="add URL to be measured"/>
<?php
}

if (count($rows))
{
?>
<div style="width: 100%; overflow: hidden">
	<table border="0" style="margin-top: 1em">
	<tr style="font-size: smaller; font-weight: bold">
	<td style="text-align: left; padding-right: 0.7em">Timestamp</td>
	<?php if ($yslow) { ?><th colspan="2">YSlow grade</th><?php } ?>
	<?php if ($pagespeed) { ?><th colspan="2">Page Speed score</th><?php } ?>
	<?php if ($dynatrace) { ?><th colspan="2">dynaTrace rank</th><?php } ?>
	<td style="text-align: center">Remove</td>
	<td style="padding-left: 1em">URL</td>
	</tr>

	<?php
	foreach ($rows as $row) {
		$link = true;
	?><tr>
		<?php if (shouldBeIgnoredAsNonHTTP($row['url'])) {
			$link = false;
		?>
			<td style="color: red; text-align: right; padding-right: 1em"><i title="This instance of Show Slow only allows HTTP(S) URLs">non-HTTP(s) URL</i></td>
			<td colspan="<?php echo $colspan ?>"/>
		<?php } else if (!isURLAllowed($row['url'])) {
			$link = false;
		?>
			<td style="color: red; text-align: right; padding-right: 1em"><i title="URL is not allowed to be reported to this instance of Show Slow">not allowed</i></td>
			<td colspan="<?php echo $colspan ?>"/>
		<?php } else if (isURLIgnored($row['url'])) {
			$link = false;
		?>
			<td style="color: red; text-align: right; padding-right: 1em"><i title="This URL is ignored by this instance of Show Slow">ignored</i></td>
			<td colspan="<?php echo $colspan ?>"/>
		<?php } else if ($row['last_update']) { ?>
			<td style="text-align: right; padding-right: 1em"><a title="Time of last check for this URL" href="details/?url=<?php echo urlencode($row['url']); ?>"><?php echo htmlentities($row['last_update']); ?></a></td>
			<?php if (!$yslow) {?>
			<?php } else if (!is_null($row['o'])) {?>
				<td class="score"><?php echo yslowPrettyScore($row['o'])?> (<?php echo $row['o']?>)</td>
				<td><div class="gbox" title="Current YSlow grade: <?php echo yslowPrettyScore($row['o'])?> (<?php echo $row['o']?>)"><div style="width: <?php echo $row['o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['o'])?>"/></div></td>
			<?php } else { ?>
				<td colspan="2"/>
			<?php } ?>

			<?php if (!$pagespeed) {?>
			<?php } else if (!is_null($row['ps_o'])) {?>
				<td class="score"><?php echo yslowPrettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)</td>
				<td><div class="gbox" title="Current Page Speed score: <?php echo yslowPrettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)"><div style="width: <?php echo $row['ps_o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['ps_o'])?>"/></div></td>
			<?php } else { ?>
				<td colspan="2"/>
			<?php } ?>

			<?php if (!$dynatrace) {?>
			<?php } else if (!is_null($row['dt_o'])) {?>
				<td class="score"><?php echo yslowPrettyScore($row['dt_o'])?> (<?php echo $row['dt_o']?>)</td>
				<td><div class="gbox" title="Current dynaTrace score: <?php echo yslowPrettyScore($row['dt_o'])?> (<?php echo $row['dt_o']?>)"><div style="width: <?php echo $row['dt_o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['dt_o'])?>"/></div></td>
			<?php }else{?>
				<td colspan="2"/>
			<?php } ?>
		<?php } else {
			$link = false;
		?>
			<td style="text-align: right; padding-right: 1em"><i title="added to the testing queue">queued</i></td>
			<td colspan="<?php echo $colspan ?>"/>
		<?php } ?>
		<td style="text-align: center"><input type="submit" name="delete[<?php echo htmlentities($row['id'])?>]" value="X" style="font-size: xx-small" title="Stop monitoring this URL" onclick="return confirm('Are you sure you want to remove this URL?')"/></td>

		<?php if ($link) {?>
		<td style="padding-left: 1em; overflow: hidden; white-space: nowrap;"><a href="details/?url=<?php echo urlencode($row['url'])?>"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></a></td>
		<?php } else { ?>
		<td style="padding-left: 1em; overflow: hidden; white-space: nowrap;"><i title="Time of last check for this URL"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></i></td>
		<?php } ?>
	</tr><?php
	}

	mysql_free_result($result);
	?>
	</table>
	</div>
<?php 
}
?>
</form>
<?php
require_once(dirname(__FILE__).'/footer.php');
