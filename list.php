<?php
require_once(dirname(__FILE__).'/global.php');

if (!array_key_exists('id', $_GET) || !array_key_exists($_GET['id'], $customLists)) {
	header('HTTP/1.0 404 No list found');
	?><html>
<head>
<title>404 No list found</title>
</head>
<body>
<h1>404 No list found</h1>
<p>List with such ID is not defined</p>
</body></html>
<?php 
	exit;
}

$list_items = $customLists[$_GET['id']]['urls'];
$list = '';
$first = true;
foreach ($list_items as $url) {
	if ($first) {
		$first = false;
	} else {
		$list .= ', ';
	}

	$list .= "'".mysql_real_escape_string($url)."'"; 
}

$query = sprintf("SELECT url, last_update,
		yslow2.o as o,
		pagespeed.o as ps_o,
		dynatrace.rank as dt_o
	FROM urls
		LEFT JOIN yslow2 ON urls.yslow2_last_id = yslow2.id
		LEFT JOIN pagespeed ON urls.pagespeed_last_id = pagespeed.id
		LEFT JOIN dynatrace ON urls.dynatrace_last_id = dynatrace.id
	WHERE urls.url IN (%s)", $list);
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$yslow = false;
$pagespeed = false;
$dynatrace = false;

$rows = array();
while ($row = mysql_fetch_assoc($result)) {
	$rows[$row['url']] = $row;

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

$TITLE = $customLists[$_GET['id']]['title'];
$SECTION = 'custom_list_'.$_GET['id'];
require_once(dirname(__FILE__).'/header.php');
?>
<h1 style="margin-bottom: 0"><?php echo htmlentities($customLists[$_GET['id']]['title'])?></h1>

<p><?php echo $customLists[$_GET['id']]['description'] ?></p>

<?php
if (count($rows) && ($yslow || $pagespeed || $dynatrace))
{
?>
<style>
td { white-space: nowrap; }

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

	<table border="0" style="margin-top: 1em">
	<tr style="font-size: smaller; font-weight: bold">
	<td style="text-align: left; padding-right: 0.7em">Timestamp</td>
	<?php if ($yslow) { ?><th colspan="2">YSlow grade</th><?php } ?>
	<?php if ($pagespeed) { ?><th colspan="2">Page Speed score</th><?php } ?>
	<?php if ($dynatrace) { ?><th colspan="2">dynaTrace rank</th><?php } ?>
	<td style="padding-left: 1em">URL</td>
	</tr>

	<?php
	foreach ($list_items as $url) {
		$row = $rows[$url];

		if (is_null($row) || (is_null($row['o']) && is_null($row['ps_o']))) {
			continue;
		}
	?><tr>
		<?php if ($row['last_update']) { ?>
			<td style="text-align: right; padding-right: 1em"><a title="Time of last check for this URL" href="details/?url=<?php echo urlencode($row['url']); ?>"><?php echo htmlentities($row['last_update']); ?></a></td>

			<?php if (!$yslow) {?>
			<?php }else if (is_null($row['o'])) {?>
				<td></td><td></td>
			<?php }else{?>
				<td class="score"><?php echo yslowPrettyScore($row['o'])?> (<?php echo $row['o']?>)</td>
				<td><div class="gbox" title="Current YSlow grade: <?php echo yslowPrettyScore($row['o'])?> (<?php echo $row['o']?>)"><div style="width: <?php echo $row['o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['o'])?>"/></div></td>
			<?php }?>

			<?php if (!$pagespeed) {?>
			<?php }else if (is_null($row['ps_o'])) {?>
				<td></td><td></td>
			<?php }else{?>
				<td class="score"><?php echo yslowPrettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)</td>
				<td><div class="gbox" title="Current Page Speed score: <?php echo yslowPrettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)"><div style="width: <?php echo $row['ps_o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['ps_o'])?>"/></div></td>
			<?php }?>

			<?php if (!$dynatrace) {?>
			<?php }else if (is_null($row['dt_o'])) {?>
				<td></td><td></td>
			<?php }else{?>
				<td class="score"><?php echo yslowPrettyScore($row['dt_o'])?> (<?php echo $row['dt_o']?>)</td>
				<td><div class="gbox" title="Current dynaTrace score: <?php echo yslowPrettyScore($row['dt_o'])?> (<?php echo $row['dt_o']?>)"><div style="width: <?php echo $row['dt_o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['dt_o'])?>"/></div></td>
			<?php }?>

			<td style="padding-left: 1em; overflow: hidden; white-space: nowrap;"><a href="details/?url=<?php echo urlencode($row['url'])?>"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></a></td>
		<?php } else { ?>
			<td style="text-align: right; padding-right: 1em"><i title="added to the testing queue">queued</i></td>
			<td colspan="4"/>
			<td style="padding-left: 1em; overflow: hidden; white-space: nowrap;"><i title="Time of last check for this URL"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></i></td>
		<?php } ?>
	</tr><?php
	}

	mysql_free_result($result);
	?>
	</table>
<?php 
}

require_once(dirname(__FILE__).'/footer.php');
