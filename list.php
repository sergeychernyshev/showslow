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

$query = sprintf("SELECT url, urls.id as url_id, last_update,
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

	if ($enabledMetrics['yslow'] && !$yslow && !is_null($row['o'])) {
		$yslow = true;
	}
	if ($enabledMetrics['pagespeed'] && !$pagespeed && !is_null($row['ps_o'])) {
		$pagespeed = true;
	}
	if ($enabledMetrics['dynatrace'] && !$dynatrace && !is_null($row['dt_o'])) {
		$dynatrace = true;
	}
}

$TITLE = $customLists[$_GET['id']]['title'];
$SECTION = 'custom_list_'.$_GET['id'];
require_once(dirname(__FILE__).'/header.php');
?>
<h2 style="margin-bottom: 0"><?php echo htmlentities($customLists[$_GET['id']]['title'])?></h2>
<p style="margin-top: 0.2em"><?php echo $customLists[$_GET['id']]['description'] ?></p>

<?php if (!is_null($addThisProfile)) {?>
<!-- AddThis Button BEGIN -->
<div class="addthis_toolbox addthis_default_style" style="margin-right: 10px;">
<a href="http://www.addthis.com/bookmark.php?v=250&amp;username=<?php echo urlencode($addThisProfile)?>" class="addthis_button_compact">Share</a>
<span class="addthis_separator">|</span>
<a class="addthis_button_twitter"></a>
<a class="addthis_button_facebook"></a>
<a class="addthis_button_google"></a>
<a class="addthis_button_delicious"></a>
<a class="addthis_button_stumbleupon"></a>
<a class="addthis_button_reddit"></a>
<span class="addthis_separator">|</span>
<a class="addthis_button_favorites"></a>
<a class="addthis_button_print"></a>
<a class="addthis_button_email"></a>
</div>
<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#username=<?php echo urlencode($addThisProfile)?>"></script>
<!-- AddThis Button END -->
<?php } ?>

<?php
if (count($rows) && ($yslow || $pagespeed || $dynatrace))
{
?>
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
		if (!array_key_exists($url, $rows)) {
			continue;
		}

		$row = $rows[$url];

		if (is_null($row) || (is_null($row['o']) && is_null($row['ps_o']) && is_null($row['dt_o']))) {
			continue;
		}
	?><tr>
		<?php if ($row['last_update']) { ?>
			<td style="text-align: right; padding-right: 1em"><a title="Time of last check for this URL" href="<?php echo detailsUrl($row['url_id'], $row['url']);?>"><?php echo htmlentities($row['last_update']); ?></a></td>

			<?php if (!$yslow) {?>
			<?php }else if (is_null($row['o'])) {?>
				<td class="score" style="color: silver" title="No data collected">no data</td>
				<td><div class="gbox" title="No data collected"><div class="bar"/></div></td>
			<?php }else{?>
				<td class="score" title="Current YSlow grade: <?php echo prettyScore($row['o'])?> (<?php echo $row['o']?>)"><?php echo prettyScore($row['o'])?> (<?php echo $row['o']?>)</td>
				<td title="Current YSlow grade: <?php echo prettyScore($row['o'])?> (<?php echo $row['o']?>)"><div class="gbox"><div style="width: <?php echo $row['o']+1?>px" class="bar c<?php echo scoreColorStep($row['o'])?>"/></div></td>
			<?php }?>

			<?php if (!$pagespeed) {?>
			<?php }else if (is_null($row['ps_o'])) {?>
				<td class="score" style="color: silver" title="No data collected">no data</td>
				<td><div class="gbox" title="No data collected"><div class="bar"/></div></td>
			<?php }else{?>
				<td class="score" title="Current Page Speed score: <?php echo prettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)"><?php echo prettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)</td>
				<td title="Current Page Speed score: <?php echo prettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)"><div class="gbox"><div style="width: <?php echo $row['ps_o']+1?>px" class="bar c<?php echo scoreColorStep($row['ps_o'])?>"/></div></td>
			<?php }?>

			<?php if (!$dynatrace) {?>
			<?php }else if (is_null($row['dt_o'])) {?>
				<td class="score" style="color: silver" title="No data collected">no data</td>
				<td><div class="gbox" title="No data collected"><div class="bar"/></div></td>
			<?php }else{?>
				<td class="score" title="Current dynaTrace score: <?php echo prettyScore($row['dt_o'])?> (<?php echo $row['dt_o']?>)"><?php echo prettyScore($row['dt_o'])?> (<?php echo $row['dt_o']?>)</td>
				<td title="Current dynaTrace score: <?php echo prettyScore($row['dt_o'])?> (<?php echo $row['dt_o']?>)"><div class="gbox"><div style="width: <?php echo $row['dt_o']+1?>px" class="bar c<?php echo scoreColorStep($row['dt_o'])?>"/></div></td>
			<?php }?>

			<td style="padding-left: 1em; overflow: hidden; white-space: nowrap;"><a href="<?php echo detailsUrl($row['url_id'], $row['url'])?>"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></a></td>
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
