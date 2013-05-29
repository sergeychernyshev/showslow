<?php
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/users/users.php');

$SECTION = 'home';
require_once(dirname(__FILE__).'/header.php');
?>
<div style="width: 100%; overflow: hidden">
<?php
$query = sprintf("SELECT url, urls.id as url_id, last_update,
		yslow2.o as o,
		pagespeed.o as ps_o,
		dynatrace.rank as dt_o,
		pagetest.test_id as wpt_test_id
	FROM urls
		LEFT JOIN yslow2 ON urls.yslow2_last_id = yslow2.id
		LEFT JOIN pagespeed ON urls.pagespeed_last_id = pagespeed.id
		LEFT JOIN dynatrace ON urls.dynatrace_last_id = dynatrace.id
		LEFT JOIN har ON urls.har_last_id = har.id
		LEFT JOIN pagetest ON urls.pagetest_last_id = pagetest.id
	WHERE last_update IS NOT NULL ORDER BY urls.last_update DESC LIMIT 100");
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$yslow = false;
$pagespeed = false;
$dynatrace = false;
$wpt = false;

$rows = array();
while ($row = mysql_fetch_assoc($result)) {
	$rows[] = $row;

	if ($enabledMetrics['yslow'] && !$yslow && !is_null($row['o'])) {
		$yslow = true;
	}
	if ($enabledMetrics['pagespeed'] && !$pagespeed && !is_null($row['ps_o'])) {
		$pagespeed = true;
	}
	if ($enabledMetrics['dynatrace'] && !$dynatrace && !is_null($row['dt_o'])) {
		$dynatrace = true;
	}
	if ($enabledMetrics['webpagetest'] && !$wpt && !is_null($row['wpt_test_id'])) {
		$wpt = true;
	}
}

if (count($rows)) {

?><table>
<tr><th>Timestamp</th>
<?php if ($yslow) { ?><th colspan="2">YSlow grade</th><?php } ?>
<?php if ($pagespeed) { ?><th colspan="2">Page Speed score</th><?php } ?>
<?php if ($dynatrace) { ?><th colspan="2">dynaTrace rank</th><?php } ?>
<?php if ($wpt) { ?><th>WebPageTest</th><?php } ?>
<th style="padding-left:10px; text-align: left">URL</th>
</tr><?php

foreach ($rows as $row) {
	?><tr>
		<td><?php echo htmlentities($row['last_update'])?></td>

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

	<?php if (!$wpt) {?>
	<?php } else if (is_null($row['wpt_test_id'])) {?>
		<td style="color: silver" title="No data collected">no data</td>
	<?php }else{?>
		<td title="WebPageTest test results"><img src="<?php echo assetURL('/img/webpagetest-12h.png') ?>"/> <a href="<?php echo detailsUrl($row['url_id'], $row['url']);?>#webpagetest">test results</a></td>
	<?php }?>

	<td class="url"><a href="<?php echo detailsUrl($row['url_id'], $row['url']);?>"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></a></td>
	</tr><?php
}

mysql_free_result($result);
?>
</table>

<?php } else { ?>
<p>No data is gathered yet</p>
<?php }?>

</div>
<?php
require_once(dirname(__FILE__).'/footer.php');
