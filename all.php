<?php
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/users/users.php');
require_once(dirname(__FILE__).'/paginator.class.php');

$searchstring = null;
if (array_key_exists('search', $_GET) && trim($_GET['search']) != '') {
	$searchstring = "urls.url LIKE '%".mysql_real_escape_string(trim($_GET['search']))."%'";

	$current_user = User::get();
	if (!is_null($current_user)) {
		$current_user->recordActivity(SHOWSLOW_ACTIVITY_URL_SEARCH);
	}
}

$TITLE = 'URLs measured';
$SECTION = 'all';
require_once(dirname(__FILE__).'/header.php');

if ($disableUnfilteredURLList && is_null($searchstring)) { // start show only filtered results
	?><p align="center"><i>Use form above to search URLs</i></p><?php
} else {
?>
<style>
.paginator .current {
	text-decoration: none;
	font-weight: bold;
	color: black;
}
</style>
<h2>URLs measured</h2>
<?php

$subset = null;

if (is_array($URLGroups) && count($URLGroups) > 0) {
	$params = array();

	if (!is_null($searchstring)) {
		$params['search'] = urlencode(trim($_GET['search']));
	}

	$paramsstring = '';
?>
<ul>
<?php 
	if ($current_group == '__show_all__') {
?>
<li><b>All URLs</b></li>
<?php
	} else {
		$id = '__show_all__';

		if ($DefaultURLGroup == $id) {
			$linkparams = $params;
		} else {
			$linkparams = array_merge($params, array('group' => urlencode($id)));
		}

		$paramsstring = '';
		if (count($linkparams) > 0) {
			foreach ($linkparams as $name => $param) {
				$paramsstring .= $paramsstring == '' ? '?' : '&';
				$paramsstring .= $name.'='.$param;
			}
		}
?>
<li><a href="<?php echo $showslow_base.'all.php'.$paramsstring ?>">All URLs</a></li>
<?php
	}

	foreach ($URLGroups as $id => $group) {
		if ($current_group == $id) {
			$subset = $group['urls'];
			?><li><b><?php echo $group['title']?></b></li><?php
		} else {
			if ($DefaultURLGroup == $id) {
				$linkparams = $params;
			} else {
				$linkparams = array_merge($params, array('group' => urlencode($id)));
			}

			$paramsstring = '';
			if (count($linkparams) > 0) {
				foreach ($linkparams as $name => $param) {
					$paramsstring .= $paramsstring == '' ? '?' : '&';
					$paramsstring .= $name.'='.$param;
				}
			}
			?><li><a href="<?php echo $showslow_base.'all.php'.$paramsstring ?>"><?php echo $group['title']?></a></li><?php
		}
	}
?></ul>

<?php
}
?>
<style>
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
		LEFT JOIN har ON urls.har_last_id = har.id
	WHERE last_update IS NOT NULL";

if (!is_null($subsetstring)) {
	$query .= " AND ($subsetstring)";
}

if (!is_null($searchstring)) {
	$query .= " AND $searchstring";
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
		LEFT JOIN har ON urls.har_last_id = har.id
	WHERE last_update IS NOT NULL';

if (!is_null($subsetstring)) {
	$query .= " AND ($subsetstring)";
}
if (!is_null($searchstring)) {
	$query .= " AND $searchstring";
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

if ($yslow || $pagespeed || $dynatrace) {
	$pages->paginate($showslow_base.'all.php');
	if ($pages->num_pages > 1) {
	?><div class="paginator"><?php
		echo $pages->display_pages();
	?></div><?php
	}
?>
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

	<td class="url"><a href="details/?url=<?php echo urlencode($row['url'])?>"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></a></td>
	</tr><?php
}

mysql_free_result($result);
?>
</table>
<?php
	if ($pages->num_pages > 1) {
	?><div class="paginator"><?php
		echo $pages->display_pages();
	?></div><?php
	}
} else {
	?><p>No data is gathered yet</p><?php
}

} // end show only filtered results
?>

</div>
<?php
require_once(dirname(__FILE__).'/footer.php');
