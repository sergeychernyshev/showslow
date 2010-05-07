<?php
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/users/users.php');

$current_user = User::require_login();

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

if (in_array($current_user->getID(), $noMaxURLsForUsers)) {
	$maxURLsPerUser = false;
}

$query = sprintf("SELECT urls.id, urls.url, yslow2.o, pagespeed.o as ps_o, last_update FROM urls INNER JOIN user_urls ON urls.id = user_urls.url_id LEFT JOIN yslow2 on urls.yslow2_last_id = yslow2.id LEFT JOIN pagespeed on urls.pagespeed_last_id = pagespeed.id WHERE user_urls.user_id = %d ORDER BY url", $current_user->getID());

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


require_once(dirname(__FILE__).'/header.php');
?>
<h1 style="margin-bottom: 0">Add URLs to monitor</h1>
<div style="font-size: small; margin-bottom: 1em">User: <a href="users/edit.php"><?php echo $current_user->getName(); ?></a></div>

<form action="" method="POST">
<table border="0">
<tr><td colspan="8" style="padding-bottom: 1em">
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
<td style="text-align: right; padding-right: 0.7em">Timestamp</td>
<td colspan="2" style="text-align: right; padding-right: 0.7em">YSlow grade</td>
<td colspan="2" style="text-align: right; padding-right: 0.7em">Page Speed grade</td>
<td style="text-align: center">Remove</td>
<td style="padding-left: 1em">URL</td>
</tr>

<?php
foreach ($rows as $row) {
?><tr>
	<?php if ($row['last_update']) { ?>
		<td style="text-align: right; padding-right: 1em"><a title="Time of last check for this URL" href="details/?url=<?php echo urlencode($row['url']); ?>"><?php echo htmlentities($row['last_update']); ?></a></td>
		<?php if (!is_null($row['o'])) {?>
			<td style="text-align: right; padding:0 10px 0 10px; white-space: nowrap;"><?php echo yslowPrettyScore($row['o'])?> (<?php echo $row['o']?>)</td>
			<td><div style="background-color: silver; width: 101px" title="Current YSlow grade: <?php echo yslowPrettyScore($row['o'])?> (<?php echo $row['o']?>)"><div style="width: <?php echo $row['o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['o'])?>"/></div></td>
		<?php } else { ?>
			<td colspan="2"/>
		<?php } ?>
		<?php if (!is_null($row['ps_o'])) {?>
			<td style="text-align: right; padding:0 10px 0 10px; white-space: nowrap;"><?php echo yslowPrettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)</td>
			<td><div style="background-color: silver; width: 101px" title="Current YSlow grade: <?php echo yslowPrettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)"><div style="width: <?php echo $row['ps_o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['ps_o'])?>"/></div></td>
		<?php } else { ?>
			<td colspan="2"/>
		<?php } ?>
		<td style="text-align: center"><input type="submit" name="delete[<?php echo htmlentities($row['id'])?>]" value="X" title="Stop monitoring this URL" onclick="return confirm('Are you sure you want to remove this URL?')"/></td>
		<td style="padding-left: 1em; overflow: hidden; white-space: nowrap;"><a href="details/?url=<?php echo urlencode($row['url'])?>"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></a></td>
	<?php } else { ?>
		<td style="text-align: right; padding-right: 1em"><i title="added to the testing queue">queued</i></td>
		<td colspan="4"/>
		<td style="text-align: center"><input type="submit" name="delete[<?php echo htmlentities($row['id'])?>]" value="X" title="Stop monitoring this URL" onclick="return confirm('Are you sure you want to remove this URL?')"/></td>
		<td style="padding-left: 1em; overflow: hidden; white-space: nowrap;"><i title="Time of last check for this URL"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></i></td>
	<?php } ?>
</tr><?php
}

mysql_free_result($result);
?>
</table>
</form>
<?php
require_once(dirname(__FILE__).'/footer.php');
