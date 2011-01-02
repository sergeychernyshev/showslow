<?php 
require_once(dirname(dirname(__FILE__)).'/global.php');
require_once(dirname(dirname(__FILE__)).'/users/users.php');

if (!array_key_exists('url', $_GET) || ($url = filter_var($_GET['url'], FILTER_VALIDATE_URL)) === false) {
?><html>
<head>
<title>Error - no URL specified</title>
</head>
<body>
<h1>Error - no URL specified</h1>
<p><a href="../">Go back</a> and pick the URL</p>
</body></html>
<?php 
return;
}

# building a query to select all beacon data in one swoop
$query = "SELECT urls.id AS url_id, UNIX_TIMESTAMP(last_update) AS t, last_event_update, yslow2.details AS yslow_details";

foreach ($all_metrics as $provider_name => $provider) {
	$query .= ",\n\t".$provider['table'].'_last_id, UNIX_TIMESTAMP('.$provider['table'].'.timestamp) AS '.$provider_name.'_timestamp';

	foreach ($provider['metrics'] as $section_name => $section) {
		foreach ($section as $metric) {
			$query .= ",\n\t\t".$provider['table'].'.'.$metric[1].' AS '.$provider_name.'_'.$metric[1];
		}
	}
}

$query .= "\nFROM urls";

foreach ($all_metrics as $provider_name => $provider) {
	$query .= "\n\tLEFT JOIN ".$provider['table'].' ON urls.'.$provider['table'].'_last_id = '.$provider['table'].'.id';
}

$query .= "\nWHERE urls.url = '".mysql_real_escape_string($url)."'";

#echo $query; exit;

$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$row = mysql_fetch_assoc($result);
$lastupdate = $row['t'];
$eventupdate = $row['last_event_update'];
$urlid = $row['url_id'];
$yslow2_last_id = $row['yslow2_last_id'];
$pagespeed_last_id = $row['pagespeed_last_id'];
$dynatrace_last_id = $row['dynatrace_last_id'];
mysql_free_result($result);

header('Last-modified: '.date(DATE_RFC2822, $lastupdate));

if (array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER) && ($lastupdate <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']))) {
	header('HTTP/1.0 304 Not Modified');
	exit;
}

$TITLE = 'Details for '.htmlentities($url);
$SCRIPTS = array(
	$showslow_base.'ajax/simile-ajax-api.js?bundle=true',
	$showslow_base.'timeline/timeline-api.js?bundle=true',
	$showslow_base.'timeplot/timeplot-api.js?bundle=true',
	'http://yui.yahooapis.com/combo?2.8.1/build/yahoo/yahoo-min.js&2.8.1/build/event/event-min.js&2.8.1/build/yuiloader/yuiloader-min.js',
	assetURL('details/details.js')
);

$SECTION = 'all';
require_once(dirname(dirname(__FILE__)).'/header.php');
?>
<script>
<?php
echo 'var metrics = '.json_encode($metrics);
?>
</script>
<style>
.yslow1 {
	color: #55009D;
}

.yslow2 {
	color: #2175D9;
}

.details {
	cursor: help;
}

.sectionname {
	padding-top: 1em;
}
.breakdowntitle {
	clear: both;
	margin-bottom: 0;
}
.titlecol {
	padding: 0 2em;
}
.value {
	font-weight: bold;
}
</style>
<h1 style="margin-bottom: 0">Details for <a href="<?php echo htmlentities($url)?>" rel="nofollow"><?php echo htmlentities(substr($url, 0, 30))?><?php if (strlen($url) > 30) { ?>...<?php } ?></a></h1>
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
<?php
}

// checking if there is har data
$query = sprintf("SELECT har.timestamp as t, har.id as id FROM har WHERE har.url_id = '%d' ORDER BY timestamp DESC",
	mysql_real_escape_string($urlid)
);

$result = mysql_query($query);

if (!$result) {
        error_log(mysql_error());
}

$har = array();
while ($har_row = mysql_fetch_assoc($result)) {
	$har[] = $har_row;
}

// checking if there were PageTest tests ran
$query = sprintf("SELECT pagetest.timestamp as t, test_url, location FROM pagetest WHERE pagetest.url_id = '%d' ORDER BY timestamp DESC",
	mysql_real_escape_string($urlid)
);

$result = mysql_query($query);

if (!$result) {
        error_log(mysql_error());
}

$pagetest = array();
while ($pagetest_row = mysql_fetch_assoc($result)) {
	$pagetest[] = $pagetest_row;
}
mysql_free_result($result);

if ($row && !(is_null($row['yslow_timestamp'])
	&& is_null($row['pagespeed_timestamp'])
	&& is_null($row['dynatrace_timestamp']))
	)
{
	?>
	<table cellpadding="15" cellspacing="5"><tr>
	<?php
	foreach ($all_metrics as $provider_name => $provider) {
		$score = $row[$provider_name.'_'.$provider['score_column']];
		if (!is_null($score)) {
			$pretty_score = prettyScore($score);
		?>
		<td valign="top" align="center" class="<?php echo $provider_name ?>">
		<img src="http://chart.apis.google.com/chart?chs=225x108&cht=gom&chd=t:<?php echo urlencode($score)?>&chl=<?php echo urlencode($pretty_score.' ('.$score.')') ?>" alt="<?php echo $pretty_score ?> (<?php echo htmlentities($score)?>)" title="Current <?php echo $provider['title'] ?> <?php echo $provider['score_name'] ?>: <?php echo $pretty_score ?> (<?php echo htmlentities($score)?>)"/>
		<div>Current <a target="_blank" href="<?php echo $provider['url'] ?>"><?php echo $provider['title'] ?></a> <?php echo $provider['score_name'] ?>: <b><?php echo $pretty_score ?> (<i><?php echo htmlentities($score)?></i>)</b></div>
		</td>
		<?php
		}
	}
	?>
	</tr></table>
<?php
}

// fetching locations only when needed
getPageTestLocations();

if (!is_null($webPageTestBase) && !is_null($webPageTestKey)) { ?>
	<a name="pagetest"/><h2>Run a test using <a href="<?php echo htmlentities($webPageTestBase)?>" target="_blank">WebPageTest</a> and store the results</h2>
	<form action="<?php echo htmlentities($showslow_base)?>pagetest.php" method="GET" target="_blank">
	<input type="hidden" name="url" size="40" value="<?php echo htmlentities($url)?>"/>
	Location: <select name="location">
	<?php foreach ($webPageTestLocations as $location) {
		if ($location['tests'] > 50) {
			continue;
		}
	?>
		<option <?php echo htmlentities($location['default']) ? 'selected ' : ''?>value="<?php echo htmlentities($location['id'])?>"><?php echo htmlentities($location['title'])?></option>
	<?php } ?></select>
	<input type="checkbox" name="private" id="wpt_private" value="1"<?php if ($webPageTestPrivateByDefault) {?> checked="true"<?php } ?>/><label for="wpt_private">Private</label>
	<input type="checkbox" name="fvonly" id="wpt_fvonly" value="1"<?php if ($webPageTestFirstRunOnlyByDefault) {?> checked="true"<?php } ?>/><label for="wpt_fvonly">First View Only</label>
	<input type="submit" style="font-weight: bold" value="start test &gt;&gt;"/>
	<?php if (count($pagetest) > 0) {?><a href="#pagetest-table">See test history below</a><?php } ?>
	</form>
<?php
}

if ($row && is_null($row['yslow_timestamp'])
	&& is_null($row['pagespeed_timestamp'])
	&& is_null($row['dynatrace_timestamp']))
{
	?>
	<a name="graph"/><h2 style="clear: both">Measurements over time</h2>
	<table width="100%" height="250px" style="border: 1px solid silver"><tr>
	<td align="center" valign="middle">
		<table cellpadding="3px">
		<tr>
		<td><img src="<?php echo assetURL('clock.png')?>"/></td>
		<td style="font-size: larger">Data is being collected</td>
		</tr>
		<tr><td colspan="2" align="center"><div class="gbox"><div class="bar ccol"></div></td></tr>
		</table>
	</td>
	</tr></table>
<?php
} else if (!$row) {
	?><div style="padding: 2em">No data is collected for this URL</div><?php
}

if ($row && !(is_null($row['yslow_timestamp'])
	&& is_null($row['pagespeed_timestamp'])
	&& is_null($row['dynatrace_timestamp']))
	)
{
	// Graph
	?>
	<script>
	url = '<?php echo htmlentities($url)?>';
	ydataversion = '<?php echo urlencode($row['yslow_timestamp'])?>';
	psdataversion = '<?php echo urlencode($row['pagespeed_timestamp'])?>';
	dtdataversion = '<?php echo urlencode($row['dynatrace_timestamp'])?>';
	eventversion = '<?php echo urlencode($eventupdate)?>';
	</script>

	<a name="graph"/><h2 style="clear: both">Measurements over time</h2>
	<div id="my-timeplot" style="height: 250px;"></div>
	<div style="font-size: 0.9em">
	<?php
	if (!is_null($row['yslow_timestamp']))
	{
	?>
	<span style="color: #D0A825">Page Size</span> (in bytes);
	<span style="color: purple">Page Load time (YSlow)</span> (in ms);
	<span style="color: #75CF74">Total Requests</span>;
	<span class="yslow2">YSlow Grade</span> (0-100);
	<?php
	}

	if (!is_null($row['pagespeed_timestamp']))
	{
	?>
	<span style="color: #6F4428">Page Speed Grade</span> (0-100);
	<span style="color: #EE4F00">Page Load time (Page Speed)</span> (in ms);
	<?php
	}

	if (!is_null($row['dynatrace_timestamp']))
	{
	?>
	<span style="color: #AB0617">dynaTrace rank</span> (0-100);
	<?php
	}

	foreach ($metrics as $name => $metric)
	{
		?><span title="<?php echo htmlentities($metric['description'])?>" style="color: <?php echo array_key_exists('color', $metric) ? $metric['color'] : 'black' ?>"><?php echo htmlentities($metric['title'])?></span> (<a href="data_metric.php?metric=<?php echo urlencode($name);?>&url=<?php echo urlencode($url);?>">csv</a>);
<?php
	}
	?>
	</div>

	<?php
	$details = json_decode($row['yslow_details'], true);
	?>
	<script>
	<?php
	$comps = array();

	if (is_array($details) && array_key_exists('g', $details)) {
		foreach ($details['g'] as $n => $y) {
			if (is_array($y) && array_key_exists('components', $y)) {
				$comps['yslow_'.$n] = $y['components'];
			}
		}
	}
	?>
	var details = <?php echo json_encode($comps)?>;
	</script>

	<?php 
	// Breakdowns for each provider
	foreach ($all_metrics as $provider_name => $provider) {
		if (!is_null($row[$provider_name.'_timestamp']))
		{
		?>
			<a name="<?php echo $provider_name ?>"/><h2 class="breakdowntitle"><?php echo $provider['title']?> breakdown</h2>
			<table>
			<?php
			foreach ($provider['metrics'] as $section_name => $metrics)
			{
				?><tr><td colspan="6" class="sectionname"><b><?php echo $section_name ?></b></td></tr><?php

				$odd = true;
				
				foreach ($metrics as $metric) {
					if ($odd) { ?><tr><?php }

					if (isset($metric[3])) {
						?><td class="titlecol"><a target="_blank" href="<?php echo $metric[3]?>"><?php echo $metric[0]?></a></td><?php
					}else{
						?><td class="titlecol"><?php echo $metric[0]?></td><?php
					}

					$value = $row[$provider_name.'_'.$metric[1]];

					if (is_null($value)) {
						?><td colspan="3" class="na">n/a</td><?php	
					} else {
						if ($metric[2] == PERCENTS){
							$pretty_score = prettyScore($value);
						?>
							<td class="value"><?php echo $pretty_score?> (<i><?php echo htmlentities($value)?></i>%)</td>
							<td><span id="details_<?php echo $provider_name.'_'.$metric[1] ?>" class="details"></span></td>
							<td><div class="gbox" title="Current <?php echo $provider['score_name']?>: <?php echo $pretty_score?> (<?php echo $value?>%)"><div class="bar c<?php echo scoreColorStep($value)?>" style="width: <?php echo $value+1?>px"/></div></td>
						<?php
						} else {
							?><td colspan="3" class="value"><?php echo $value.$metric_types[$metric[2]]['units'] ?></td><?php	
						}
					}

					if (!$odd) { ?></tr><?php }

					$odd = !$odd;
				}
				?>
			</tr>
			<?php
			}
			?>
		</table>	
	<?php 
		}
	}
}

if (!is_null($row['yslow_timestamp'])) {
?>
	<a name="yslow-table"/><h2>YSlow measurements history (<a href="data.php?ver=<?php echo urlencode($row['yslow_timestamp'])?>&url=<?php echo urlencode($url)?>">csv</a>)</h2>
	<div id="measurementstable"></div>
	<?php 
}

if (!is_null($row['pagespeed_timestamp'])) {
?>
	<a name="pagespeed-table"/><h2>Page Speed measurements history (<a href="data_pagespeed.php?ver=<?php echo urlencode($row['pagespeed_timestamp'])?>&url=<?php echo urlencode($url)?>">csv</a>)</h2>
	<div id="ps_measurementstable"></div>
<?php 
}

if (!is_null($row['dynatrace_timestamp'])) {
?>
	<a name="dynatrace-table"/><h2>dynaTrace measurements history (<a href="data_dynatrace.php?ver=<?php echo urlencode($row['dynatrace_timestamp'])?>&url=<?php echo urlencode($url)?>">csv</a>)</h2>
	<div id="dt_measurementstable"></div>
<?php 
}

if (count($pagetest) > 0) {
?>
	<a name="pagetest-table"/><h2>WebPageTest data collected</h2>

	<p>You can see latest <a href="<?php echo htmlentities($pagetest[0]['test_url']) ?>" target="_blank">PageTest report for <?php echo htmlentities($url)?></a> or check the archive:</p>

	<table cellpadding="5" cellspacing="0" border="1">
	<tr>
	<th>Time</th>
	<th>Location</th>
	<th>PageTest</th>
	</tr>
<?php
	foreach ($pagetest as $pagetestentry) {
		$location = array_key_exists($pagetestentry['location'], $webPageTestLocationsById) ?
			$webPageTestLocationsById[$pagetestentry['location']]['title'] :
			$pagetestentry['location'];
?>
	<tr>
	<td><?php echo htmlentities($pagetestentry['t'])?></td>
	<td><?php echo htmlentities($location)?></td>
	<td><a href="<?php echo htmlentities($pagetestentry['test_url'])?>" target="_blank">view PageTest report</a></td>
	</tr>
<?php
	}
?>
	</table>
<?php
}

if (count($har) > 0) {
?>
	<a name="har-table"/><h2>HAR data collected</h2>

	<p>You can see latest HAR data in the viewer here: <a href="<?php echo htmlentities($HARViewerBase)?>?inputUrl=<?php echo $showslow_base?>details/har.php%3Fid%3D<?php echo urlencode($har[0]['id']); ?>%26callback%3DonInputData" target="_blank">HAR for <?php echo htmlentities($url)?></a>.</p>

	<table cellpadding="5" cellspacing="0" border="1">
	<tr><th>Time</th><th>HAR</th></tr>
<?php
	foreach ($har as $harentry) {
?>
	<tr><td><?php echo htmlentities($harentry['t'])?></td><td><a href="<?php echo htmlentities($HARViewerBase)?>?inputUrl=<?php echo $showslow_base?>details/har.php%3Fid%3D<?php echo urlencode($harentry['id'])?>%26callback%3DonInputData" target="_blank">view in HAR viewer</a></td></tr>
<?php
	}
?>
	</table>
<?php
}

require_once(dirname(dirname(__FILE__)).'/footer.php');
