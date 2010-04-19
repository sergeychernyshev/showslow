<?php
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/users/users.php');

$current_user = User::require_login();

$query = sprintf("SELECT id, url, TIMEDIFF(now(), last_update) AS last FROM urls INNER JOIN user_urls ON urls.id = user_urls.url_id WHERE user_urls.user_id = %d ORDER BY url", $current_user->getID());

$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$noMoreURLs = false;
if ($maxURLsPerUser && mysql_num_rows($result) >= $maxURLsPerUser)
{
	$noMoreURLs = true;
}

$rows = array();
while ($row = mysql_fetch_assoc($result)) {
	$rows[] = $row;
}

if (array_key_exists('delete', $_POST) && is_array($_POST['delete'])) {
	$delete = array_keys($_POST['delete']);

	$first = true;
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

	if (!$first) {
		$query = sprintf("DELETE FROM user_urls WHERE user_id = %d AND url_id IN (%s)",
			$current_user->getID(),
			$deleteids
		);

		error_log($query);

		$result = mysql_query($query);

		if (!$result) {
			error_log(mysql_error());
		}
	}
	header('Location: '.$showslow_base.'my.php');
}

if (!$noMoreURLs && array_key_exists('url', $_REQUEST)
	&& ($url = filter_var($_REQUEST['url'], FILTER_VALIDATE_URL)) !== false) {
	require_once(dirname(__FILE__).'/beacon/beacon_functions.php');

	$url_id = getUrlId($url);

	$query = sprintf("INSERT IGNORE INTO user_urls (user_id, url_id) VALUES (%d, %d)",
		$current_user->getID(),
		$url_id
	);

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
	}

	header('Location: '.$showslow_base.'my.php');
}

require_once(dirname(__FILE__).'/header.php');
?>
<h1 style="margin-bottom: 0">Add URLs to monitor</h1>
<div style="font-size: small; margin-bottom: 1em">User: <a href="users/edit.php"><?php echo $current_user->getName(); ?></a></div>

<form action="" method="POST">
<table border="0">
<tr><td colspan="4" style="padding-bottom: 1em">
<?php
if ($noMoreURLs)
{
?>
<div title="URLs tracked are limited because of load constraints"><input type="text" size="80" name="url" disabled="disabled"/><input type="submit" name="add" value="add" disabled="disabled"/></div>
<?php } else { ?>
<input type="text" size="80" name="url"/><input type="submit" name="add" value="add" title="add URL to be measured"/>
<?php
}
?>
</td></tr>

<tr style="font-size: smaller; font-weight: bold">
<td style="text-align: center">Remove</td>
<td style="padding-left: 1em">URL</td>
<td style="text-align: right; padding-right: 0.7em">Time Since Last Check</td>
</tr>

<?php
foreach ($rows as $row) {
?><tr>
	<td style="text-align: center"><input type="submit" name="delete[<?php echo htmlentities($row['id'])?>]" value="X" title="Stop monitoring this URL" onclick="return confirm('Are you sure you want to remove this URL?')"/></td>
	<td style="padding-left: 1em; overflow: hidden; white-space: nowrap;"><?php if ($row['last']) { ?><a href="details/?url=<?php echo urlencode($row['url'])?>"><?php } else { ?><i title="Time since last check for this URL"><?php } ?><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?><?php if ($row['last']) { ?></a><?php } else { ?></i><?php } ?></td>
	<td style="text-align: right; padding-right: 1em"><?php echo $row['last'] ? '<a title="Time since last check for this URL" href="details/?url='.urlencode($row['url']).'">'.htmlentities($row['last']).'</span>' : '<i title="added to the testing queue">queued</i>'?></td>
</tr><?php
}

mysql_free_result($result);
?>
</table>
</form>
<?php
require_once(dirname(__FILE__).'/footer.php');
