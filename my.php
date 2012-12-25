<?php
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/users/users.php');

$current_user = User::require_login();

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
	$query = sprintf('SELECT count(*) AS cnt FROM user_urls	WHERE user_urls.user_id = %d', $current_user->getID());

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
	}

	$cnt = mysql_fetch_row($result);

	if (is_array($cnt) && $cnt[0] >= $maxURLsPerUser)
	{
		$noMoreURLs = true;
		$MESSAGES[] = $maxURLsMessage;
	}
	mysql_free_result($result);
}


if (!$noMoreURLs && array_key_exists('url', $_REQUEST)) {
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

	$current_user->recordActivity(SHOWSLOW_ACTIVITY_ADD_URL);

	if (is_callable($onNewMonitoredURL)) {
		// only call when URL was never monitored
		$query = "SELECT DISTINCT url FROM urls INNER JOIN user_urls on user_urls.url_id = urls.id
			WHERE urls.id = %d AND DATE_ADD(added, INTERVAL %d HOUR) > NOW()";

		foreach ($all_metrics as $provider_name => $provider) {
			$query .= " AND ".$provider['table'].'_last_id IS NULL';
		}

		$query .= ' LIMIT 0, 1';

		$query = sprintf($query, $url_id, $monitoringPeriod);

		$result = mysql_query($query);

		if (!$result) {
			error_log(mysql_error());
		}

		if ($row = mysql_fetch_assoc($result)) {
			$url = $row['url'];
			call_user_func($onNewMonitoredURL, $url, $current_user);
		}
	}

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
$cols = 0;
while ($row = mysql_fetch_assoc($result)) {
	$rows[] = $row;

	if ($enabledMetrics['yslow'] && !$yslow && !is_null($row['o'])) {
		$yslow = true;
		$cols += 1;
	}
	if ($enabledMetrics['pagespeed'] && !$pagespeed && !is_null($row['ps_o'])) {
		$pagespeed = true;
		$cols += 1;
	}
	if ($enabledMetrics['dynatrace'] && !$dynatrace && !is_null($row['dt_o'])) {
		$dynatrace = true;
		$cols += 1;
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

.url {
	padding-left:10px;
}
</style>

<p>If you don't want to <a href="<?php echo $showslow_base; ?>configure.php">run YSlow, Page Speed and dynaTrace on your desktop</a>, you can add a URL to the list below and it'll be measured automatically every <?php echo $monitoringPeriod ?> hours.</p>

<?php
if (count($rows))
{
?>
<form action="" method="POST" style="width: 100%; overflow: hidden">
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
			<td colspan="<?php echo $cols*2 ?>"/>
		<?php } else if (!isURLAllowed($row['url'])) {
			$link = false;
		?>
			<td style="color: red; text-align: right; padding-right: 1em"><i title="URL is not allowed to be reported to this instance of Show Slow">not allowed</i></td>
			<td colspan="<?php echo $cols*2 ?>"/>
		<?php } else if (isURLIgnored($row['url'])) {
			$link = false;
		?>
			<td style="color: red; text-align: right; padding-right: 1em"><i title="This URL is ignored by this instance of Show Slow">ignored</i></td>
			<td colspan="<?php echo $cols*2 ?>"/>
		<?php } else if (!is_null($row['o']) || !is_null($row['ps_o']) || !is_null($row['dt_o'])) { ?>
			<td style="text-align: right; padding-right: 1em"><a title="Time of last check for this URL" href="<?php echo detailsUrl($row['id'], $row['url'])?>"><?php echo htmlentities($row['last_update']); ?></a></td>
			<?php if (!$yslow) {?>
			<?php } else if (!is_null($row['o'])) {?>
				<td class="score" title="Current YSlow grade: <?php echo prettyScore($row['o'])?> (<?php echo $row['o']?>)"><?php echo prettyScore($row['o'])?> (<?php echo $row['o']?>)</td>
				<td title="Current YSlow grade: <?php echo prettyScore($row['o'])?> (<?php echo $row['o']?>)"><div class="gbox"><div style="width: <?php echo $row['o']+1?>px" class="bar c<?php echo scoreColorStep($row['o'])?>"/></div></td>
			<?php } else { ?>
				<td class="score" style="color: silver" title="No data collected">no data</td>
				<td><div class="gbox" title="No data collected"><div class="bar"/></div></td>
			<?php } ?>

			<?php if (!$pagespeed) {?>
			<?php } else if (!is_null($row['ps_o'])) {?>
				<td class="score" title="Current Page Speed score: <?php echo prettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)"><?php echo prettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)</td>
				<td title="Current Page Speed score: <?php echo prettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)"><div class="gbox"><div style="width: <?php echo $row['ps_o']+1?>px" class="bar c<?php echo scoreColorStep($row['ps_o'])?>"/></div></td>
			<?php } else { ?>
				<td class="score" style="color: silver" title="No data collected">no data</td>
				<td><div class="gbox" title="No data collected"><div class="bar"/></div></td>
			<?php } ?>

			<?php if (!$dynatrace) {?>
			<?php } else if (!is_null($row['dt_o'])) {?>
				<td class="score" title="Current dynaTrace score: <?php echo prettyScore($row['dt_o'])?> (<?php echo $row['dt_o']?>)"><?php echo prettyScore($row['dt_o'])?> (<?php echo $row['dt_o']?>)</td>
				<td title="Current dynaTrace score: <?php echo prettyScore($row['dt_o'])?> (<?php echo $row['dt_o']?>)"><div class="gbox"><div style="width: <?php echo $row['dt_o']+1?>px" class="bar c<?php echo scoreColorStep($row['dt_o'])?>"/></div></td>
			<?php }else{?>
				<td class="score" style="color: silver" title="No data collected">no data</td>
				<td><div class="gbox" title="No data collected"><div class="bar"/></div></td>
			<?php } ?>
		<?php } else { ?>
			<td style="text-align: right; padding-right: 1em" title="Data for this URL is being collected"><i>collecting data</i></td>
			<?php for($i=0; $i<$cols; $i++) {?>
			<td class="score" style="color: silver" title="Collecting data"><img style="vertical-align: text-bottom" src="<?php echo assetURL('clock.png')?>"/></td>
			<td title="Collecting data"><div class="gbox"><div class="bar ccol"/></div></td>
			<?php } ?>
		<?php } ?>
		<td style="text-align: center"><input class="btn btn-mini" type="submit" name="delete[<?php echo htmlentities($row['id'])?>]" value="×" style="font-size: xx-small" title="Stop monitoring this URL" onclick="return confirm('Are you sure you want to remove this URL?')"/></td>

		<?php if ($link) {?>
		<td style="padding-left: 1em; overflow: hidden; white-space: nowrap;"><a href="<?php echo detailsUrl($row['id'], $row['url'])?>"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></a></td>
		<?php } else { ?>
		<td style="padding-left: 1em; overflow: hidden; white-space: nowrap;"><i title="Time of last check for this URL"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></i></td>
		<?php } ?>
	</tr><?php
	}

	mysql_free_result($result);
	?>
	</table>
	</form>
<?php
}

require_once(dirname(__FILE__).'/footer.php');
