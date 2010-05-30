<?php 
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/users/users.php');

$SECTION = 'home';
require_once(dirname(__FILE__).'/header.php');

echo $ShowSlowIntro;
?>
<hr size="1"/>
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
<div style="width: 100%; overflow: hidden">
<?php 
$query = sprintf("SELECT url, last_update,
		yslow2.o as o,
		pagespeed.o as ps_o,
		dynatrace.rank as dt_o
	FROM urls
		LEFT JOIN yslow2 ON urls.yslow2_last_id = yslow2.id
		LEFT JOIN pagespeed ON urls.pagespeed_last_id = pagespeed.id
		LEFT JOIN dynatrace ON urls.dynatrace_last_id = dynatrace.id
	WHERE last_update IS NOT NULL ORDER BY urls.last_update DESC LIMIT 100");
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
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

if ($yslow || $pagespeed || $dynatrace) {

?><table>
<tr><th>Timestamp</th>
<?php if ($yslow) { ?><th colspan="2">YSlow grade</th><?php } ?>
<?php if ($pagespeed) { ?><th colspan="2">Page Speed score</th><?php } ?>
<?php if ($dynatrace) { ?><th colspan="2">dynaTrace rank</th><?php } ?>
<th style="padding-left:10px; text-align: left">URL</th>
</tr><?php

foreach ($rows as $row) {
	?><tr>
		<td><?php echo htmlentities($row['last_update'])?></td>

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

	<td class="url"><a href="details/?url=<?php echo urlencode($row['url'])?>"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></a></td>
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
