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

$query = sprintf("SELECT url, last_update,
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

$noMoreURLs = false;
if ($maxURLsPerUser && mysql_num_rows($result) >= $maxURLsPerUser)
{
	$noMoreURLs = true;
}

$yslow = false;
$pagespeed = false;
$dynatrace = false;

$rows = array();
while ($row = mysql_fetch_assoc($result)) {
	$rows[] = $row;

	if (!$yslow && !is_null($row['o'])) {
		$yslow = true;
	}
	if (!$pagespeed && !is_null($row['ps_o'])) {
		$pagespeed = true;
	}
	if (!$dynatrace && !is_null($row['dt_o'])) {
		$dynatrace = true;
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

<p>If you don't want to <a href="<?php echo $showslow_base; ?>configure.php">run YSlow and Page Speed on your desktop</a>, you can add a URL to the list below and it'll be measured automatically every <?php echo $monitoringPeriod ?> hours.</p>

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
	?><tr>
		<?php if ($row['last_update']) { ?>
			<td style="text-align: right; padding-right: 1em"><a title="Time of last check for this URL" href="details/?url=<?php echo urlencode($row['url']); ?>"><?php echo htmlentities($row['last_update']); ?></a></td>
			<?php if (!$yslow) {?>
			<?php }else if (!is_null($row['o'])) {?>
				<td class="score"><?php echo yslowPrettyScore($row['o'])?> (<?php echo $row['o']?>)</td>
				<td><div class="gbox" title="Current YSlow grade: <?php echo yslowPrettyScore($row['o'])?> (<?php echo $row['o']?>)"><div style="width: <?php echo $row['o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['o'])?>"/></div></td>
			<?php } else { ?>
				<td colspan="2"/>
			<?php } ?>

			<?php if (!$pagespeed) {?>
			<?php else if (!is_null($row['ps_o'])) {?>
				<td class="score"><?php echo yslowPrettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)</td>
				<td><div class="gbox" title="Current Page Speed score: <?php echo yslowPrettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)"><div style="width: <?php echo $row['ps_o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['ps_o'])?>"/></div></td>
			<?php } else { ?>
				<td colspan="2"/>
			<?php } ?>

			<?php if (!$dynatrace) {?>
			<?php }else if (!is_null($row['dt_o'])) {?>
				<td class="score"><?php echo yslowPrettyScore($row['dt_o'])?> (<?php echo $row['dt_o']?>)</td>
				<td><div class="gbox" title="Current dynaTrace score: <?php echo yslowPrettyScore($row['dt_o'])?> (<?php echo $row['dt_o']?>)"><div style="width: <?php echo $row['dt_o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['dt_o'])?>"/></div></td>
			<?php }else{?>
				<td colspan="2"/>
			<?php }?>

			<td style="text-align: center"><input type="submit" name="delete[<?php echo htmlentities($row['id'])?>]" value="X" style="font-size: xx-small" title="Stop monitoring this URL" onclick="return confirm('Are you sure you want to remove this URL?')"/></td>
			<td style="padding-left: 1em; overflow: hidden; white-space: nowrap;"><a href="details/?url=<?php echo urlencode($row['url'])?>"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></a></td>
		<?php } else { ?>
			<td style="text-align: right; padding-right: 1em"><i title="added to the testing queue">queued</i></td>
			<td style="text-align: center"><input type="submit" name="delete[<?php echo htmlentities($row['id'])?>]" value="X" style="font-size: xx-small" title="Stop monitoring this URL" onclick="return confirm('Are you sure you want to remove this URL?')"/></td>
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
