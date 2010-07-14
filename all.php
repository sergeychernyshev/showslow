<?php
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/users/users.php');
require_once(dirname(__FILE__).'/paginator.class.php');

$TITLE = 'URLs measured';
$SECTION = 'all';
require_once(dirname(__FILE__).'/header.php');
?>
<style>
.current {
	text-decoration: none;
	font-weight: bold;
	color: black;
}
</style>
<h1>URLs measured</h1>
<?php
$current_group = array_key_exists('group', $_GET) ? $_GET['group'] : null;

$subset = null;

if (is_array($URLGroups) && count($URLGroups) > 0) {
?>
<ul>
<?php 
	if (is_null($current_group)) {
?>
<li><b>All URLs</b></li>
<?php
	} else {
?>
<li><a href="<?php echo $showslow_base.'all.php'?>">All URLs</a></li>
<?php
	}

	foreach ($URLGroups as $id => $group) {
		if ($current_group == $id) {
			$subset = $group['urls'];
			?><li><b><?php echo $group['title']?></b></li><?php
		} else {
			?><li><a href="<?php echo $showslow_base.'all.php?group='.$id; ?>"><?php echo $group['title']?></a></li><?php
		}
	}
?></ul>
<hr size="1">
<?php
}
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



.paginator {
	padding: 1em;
}

.paginator .paginate {
	padding: 2px 6px;
	border: 1px solid silver;
	text-decoration: none;
}

.paginator .current {
	padding: 2px 6px;
	border: 2px solid #7F6F26;
	background: #7F6F26;
	color: white;
}

.paginator .inactive {
	color: silver;
	padding: 2px 6px;
}
</style>
<div style="width: 100%; overflow: hidden">
<?php
$perPage = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
	$page = 1;
}
$offset = ($page - 1) * $perPage;

$subsetstring = null;
$first = true;
if (is_array($subset)) {
	foreach ($subset as $url) {
		if ($first) {
			$first = false;
		} else {
			$subsetstring .= ' OR ';
		}
		$subsetstring .= "urls.url LIKE '".mysql_real_escape_string($url)."%'";
	}
}

$query = "SELECT count(*)
	FROM urls
		LEFT JOIN yslow2 ON urls.yslow2_last_id = yslow2.id
		LEFT JOIN pagespeed ON urls.pagespeed_last_id = pagespeed.id
		LEFT JOIN dynatrace ON urls.dynatrace_last_id = dynatrace.id
	WHERE last_update IS NOT NULL";

if (!is_null($subsetstring)) {
	$query .= " AND ($subsetstring)";
}

$result = mysql_query($query);
$row = mysql_fetch_row($result);
$total = $row[0];

$pages = new Paginator();
$pages->items_total = $total;
$pages->mid_range = 7;
$pages->items_per_page = $perPage;

$query = 'SELECT url, last_update,
		yslow2.o as o,
		pagespeed.o as ps_o,
		dynatrace.rank as dt_o
	FROM urls
		LEFT JOIN yslow2 ON urls.yslow2_last_id = yslow2.id
		LEFT JOIN pagespeed ON urls.pagespeed_last_id = pagespeed.id
		LEFT JOIN dynatrace ON urls.dynatrace_last_id = dynatrace.id
	WHERE last_update IS NOT NULL';

if (!is_null($subsetstring)) {
	$query .= " AND ($subsetstring)";
}
$query .= sprintf(" ORDER BY url LIMIT %d OFFSET %d", $perPage, $offset);

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
?>
<div class="paginator">
<?php
	$pages->paginate($showslow_base.'all.php');
	echo $pages->display_pages();
?>
</div>
<table>
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
<div class="paginator">
<?php
	echo $pages->display_pages();
?>
</div>
<?php
} else {
	?><p>No data is gathered yet</p><?php
}
?>

</div>
<?php
require_once(dirname(__FILE__).'/footer.php');
