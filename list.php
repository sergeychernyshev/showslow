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

$list = '';
$first = true;
foreach ($customLists[$_GET['id']]['urls'] as $url) {
	if ($first) {
		$first = false;
	} else {
		$list .= ', ';
	}

	$list .= "'".mysql_real_escape_string($url)."'"; 
}

error_log($list);

$query = "SELECT urls.id, urls.url, yslow2.o, pagespeed.o as ps_o, last_update FROM urls LEFT JOIN yslow2 ON urls.yslow2_last_id = yslow2.id LEFT JOIN pagespeed ON urls.pagespeed_last_id = pagespeed.id WHERE urls.url IN ($list)";

$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$rows = array();
while ($row = mysql_fetch_assoc($result)) {
	$rows[] = $row;
}

$TITLE = $customLists[$_GET['id']]['title'];
$SECTION = 'custom_list_'.$_GET['id'];
require_once(dirname(__FILE__).'/header.php');
?>
<h1 style="margin-bottom: 0"><?php echo htmlentities($customLists[$_GET['id']]['title'])?></h1>

<p><?php echo $customLists[$_GET['id']]['description'] ?></p>

<?php
if (count($rows))
{
?>
	<table border="0" style="margin-top: 1em">
	<tr style="font-size: smaller; font-weight: bold">
	<td style="text-align: left; padding-right: 0.7em">Timestamp</td>
	<td colspan="2" style="text-align: right; padding-right: 0.7em">YSlow grade</td>
	<td colspan="2" style="text-align: right; padding-right: 0.7em">Page Speed score</td>
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
				<td><div style="background-color: silver; width: 101px" title="Current Page Speed score: <?php echo yslowPrettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)"><div style="width: <?php echo $row['ps_o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['ps_o'])?>"/></div></td>
			<?php } else { ?>
				<td colspan="2"/>
			<?php } ?>
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
