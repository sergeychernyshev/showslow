<?php
require_once(dirname(dirname(__FILE__)).'/global.php');
require_once(dirname(dirname(__FILE__)).'/users/users.php');

$urlid = array_key_exists('urlid', $_GET) ? filter_var($_GET['urlid'], FILTER_VALIDATE_INT) : null;

$url_passed = $_GET['url'];

# fixing up a URL if it is missing a double slash in domain name
$url_passed = preg_replace('#^http://?#', 'http://', $url_passed);
$url_passed = preg_replace('#^https://?#', 'https://', $url_passed);

$url = array_key_exists('url', $_GET) ? filter_var($url_passed, FILTER_VALIDATE_URL) : null;

function not_found() {
	header("HTTP/1.0 404 Not Found");
	?><html>
	<head>
	<title>Error - no such URL</title>
	</head>
	<body>
	<h1>Error - no such URL</h1>
	<p><a href="../">Go back</a> and pick the URL</p>
	</body></html>
	<?php
	exit;
}

if (!$urlid && !$url) {
	not_found();
}

if (!$urlid && $url) {
	$query = "SELECT id FROM urls WHERE urls.url_md5 = UNHEX(MD5('".mysql_real_escape_string($url)."'))";
	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
	}

	$row = mysql_fetch_assoc($result);

	if (is_null($row)) {
		not_found();
	} else {
		$urlid = $row['id'];

		if (is_null($urlid)) {
			not_found();
		}

		header("Location: ".detailsUrl($urlid, $url));
		exit;
	}
}

if ($urlid && !$url) {
	$query = "SELECT url FROM urls WHERE urls.id = ".mysql_real_escape_string($urlid);
	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
	}

	$row = mysql_fetch_assoc($result);

	if (is_null($row)) {
		not_found();
	} else {
		$url = $row['url'];

		if (is_null($url)) {
			not_found();
		}

		header("Location: ".detailsUrl($urlid, $url));
		exit;
	}
}

# building a query to select all beacon data in one swoop
$query = "SELECT urls.id AS url_id, urls.url as url, UNIX_TIMESTAMP(last_update) AS t, last_event_update, yslow2.details AS yslow_details";

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

$query .= "\nWHERE urls.id = ".mysql_real_escape_string($urlid);

#echo $query; exit;

$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$row = mysql_fetch_assoc($result);
$lastupdate = $row['t'];
$eventupdate = $row['last_event_update'];
$url = $row['url'];
$yslow2_last_id = $row['yslow2_last_id'];
$pagespeed_last_id = $row['pagespeed_last_id'];
$dynatrace_last_id = $row['dynatrace_last_id'];
mysql_free_result($result);

$custom_metrics = array();
$custom_metrics_version = 0;

foreach ($metrics as $id => $metric) {
	$query = sprintf("SELECT value, UNIX_TIMESTAMP(timestamp) as t
		FROM metric WHERE url_id = %d AND metric_id = %d AND timestamp > DATE_SUB(now(), INTERVAL 3 MONTH)
		ORDER BY timestamp DESC LIMIT 1",
		mysql_real_escape_string($urlid),
		mysql_real_escape_string($metric['id'])
	);

	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
	}

	if ($row_metrics = mysql_fetch_assoc($result)) {
		$custom_metrics[$metric['id']]['metric_slug'] = $id;
		$custom_metrics[$metric['id']]['metric'] = $metric;
		$custom_metrics[$metric['id']]['value'] = $row_metrics['value'];
		if ($row_metrics['t'] > $custom_metrics_version) {
			$custom_metrics_version = $row_metrics['t'];
		}
	}
}

// fetching locations only when needed
getPageTestLocations();

header('Last-modified: '.date(DATE_RFC2822, $lastupdate));

if (array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER) &&
	($lastupdate <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']))
) {
	header('HTTP/1.0 304 Not Modified');
	exit;
}

$TITLE = 'Details for '.htmlentities($url);

$SCRIPTS[] = 'http://yui.yahooapis.com/combo?2.8.1/build/yahoo/yahoo-min.js&2.8.1/build/event/event-min.js&2.8.1/build/yuiloader/yuiloader-min.js';

$current_user = User::get();
if (!$enableFlot && !is_null($current_user)) {
	$enableFlot = $current_user->hasFeature(SHOWSLOW_FLOT_SUPPORT);
}

if (!$enableFlot) {
	$SCRIPTS = array_merge($SCRIPTS, array(
		$showslow_base.'ajax/simile-ajax-api.js?bundle=true',
		$showslow_base.'timeline/timeline-api.js?bundle=true',
		$showslow_base.'timeplot/timeplot-api.js?bundle=true',
		assetURL('details/timeplot.js')
	));
} else {
	$SCRIPTS = array_merge($SCRIPTS, array(
		array('condition' => 'if lte IE 8', 'url' => assetURL('flot/excanvas.js')),
		'http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js',
		assetURL('flot/jquery.flot.js'),
		assetURL('flot/jquery.flot.crosshair.js'),
		assetURL('flot/jquery.flot.selection.js'),
		assetURL('flot/jquery.flot.resize.js'),
	));
}

$SCRIPTS[] = assetURL('details/details.js');

$SECTION = 'all';
require_once(dirname(dirname(__FILE__)).'/header.php');

if ($enableFlot) {
	$flot_metrics = array();
	$flot_versions = array();
	$color = 49;

	if (count($custom_metrics) > 0) {
		$flot_versions['custom'] = $custom_metrics_version;
	}

	$custom_metric_colors = array();
	foreach ($metrics as $slug => $custom_metric) {
		// assume the default value is NUBER
		if (!array_key_exists('type', $custom_metric)) {
			$custom_metric['type'] = NUMBER;
		}

		$flot_metrics['custom'][$slug] = array(
			'color' => $color++,
			'label' => $custom_metric['title'].' (custom)',
			'data' => array(),
			'yaxis' => $custom_metric['type'] + 1
		);

		$custom_metric_colors[] = $custom_metric['color'];
	}

	foreach ($all_metrics as $provider_name => $provider) {
		if ($enabledMetrics[$provider_name] && !is_null($row[$provider_name.'_timestamp']))
		{
			$flot_versions[$provider_name] = $row[$provider_name.'_timestamp'];

			foreach ($provider['metrics'] as $section_name => $section) {
				foreach ($section as $metric) {
					$flot_metrics[$provider_name][$metric[1]] = array(
						'label' => $metric[0].' ('.$provider['title'].')',
						'data' => array(),
						'yaxis' => $metric[2] + 1
					);
				}
			}
		}
	}

	$default_metrics = array();

	foreach (array_keys($defaultGraphMetrics) as $provider_name) {
		if ($provider_name == 'custom') {
			foreach ($defaultGraphMetrics['custom'] as $metric_name) {
				foreach ($custom_metrics as $id => $metric) {
					if ($metric['metric_slug'] == $metric_name) {
						$default_metrics[$provider_name][] = $metric_name;
						break;
					}
				}
			}
		} else if ($enabledMetrics[$provider_name] && !is_null($row[$provider_name.'_timestamp'])) {
			$default_metrics[$provider_name] = $defaultGraphMetrics[$provider_name];
		}
	}

	?>

	<script>
	var flot_metrics = <?php echo json_encode($flot_metrics); ?>;
	var flot_versions = <?php echo json_encode($flot_versions); ?>;
	var default_metrics = <?php echo json_encode($default_metrics); ?>;
	var custom_metric_colors = <?php echo json_encode($custom_metric_colors); ?>;
<?php } else { ?>
	<script>
<?php } ?>
var url = <?php echo json_encode($url); ?>;
var urlid = <?php echo json_encode($urlid); ?>;
var metrics = <?php echo json_encode($metrics); ?>;
</script>
<h2>Details for <?php if ($linkToURLs) {?><a target="_blank" href="<?php echo htmlentities($url)?>" rel="nofollow"><?php } else { ?><span class="sitelink"><?php } ?><span title="<?php echo htmlentities($url) ?>"><?php echo htmlentities(ellipsis($url, 61)) ?></span><?php if ($linkToURLs) {?></a><?php } else { ?></span><?php } ?></h2>
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
$query = sprintf("SELECT har.timestamp as t, har.id as id, har.link as link FROM har WHERE har.url_id = '%d' ORDER BY timestamp DESC",
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
$query = sprintf("SELECT pagetest.timestamp as t, test_id, location FROM pagetest WHERE pagetest.url_id = '%d' ORDER BY timestamp DESC",
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

$data = array();

$havemetrics = false;

if (count($custom_metrics) > 0) {
	$havemetrics = true;
}

if (!$havemetrics && $row) {
	foreach (array_keys($all_metrics) as $provider_name) {
		if ($enabledMetrics[$provider_name]
			&& !is_null($row[$provider_name.'_timestamp']))
		{
			$havemetrics = true;
			break;
		}
	}
}

if ($havemetrics)
{
	?>
	<table cellpadding="0" cellspacing="5"><tr>
	<?php
	foreach ($all_metrics as $provider_name => $provider) {
		if (!$enabledMetrics[$provider_name] || !array_key_exists('score_column', $provider)) {
			continue;
		}

		$score = $row[$provider_name.'_'.$provider['score_column']];
		if (!is_null($score)) {
			$pretty_score = prettyScore($score);
		?>
		<td valign="top" align="center" class="<?php echo $provider_name ?>">
		<a href="#<?php echo $provider_name ?>"><img src="http://chart.apis.google.com/chart?chs=225x108&cht=gom&chd=t:<?php echo urlencode($score)?>&chl=<?php echo urlencode($pretty_score.' ('.$score.')') ?>" width="225" height="108" alt="<?php echo $pretty_score ?> (<?php echo htmlentities($score)?>)" title="Current <?php echo $provider['title'] ?> <?php echo $provider['score_name'] ?>: <?php echo $pretty_score ?> (<?php echo htmlentities($score)?>)" border="0"/></a>
		<div>Current <a href="#<?php echo $provider_name ?>"><?php echo $provider['title'] ?></a> <?php echo $provider['score_name'] ?>: <b><?php echo $pretty_score ?> (<i><?php echo htmlentities($score)?></i>)</b></div>
		</td>
		<?php
		}
	}
	?>
	</tr></table>
<?php
}

if ((!is_null($webPageTestBase) && !is_null($webPageTestKey))
	|| !is_null($redBotBase)
	|| $enablePageSpeedInsightsTool
	|| is_array($customTools)
) {
?>
<a name="tools"></a><fieldset id="tools"><legend>Tools</legend>
<?php
if (!is_null($webPageTestBase) && !is_null($webPageTestKey)) { ?>
	<div class="well pull-left" style="margin: 0.5%; width: 27%">
	<a name="pagetest"></a><h4>Run a test using <a href="<?php echo htmlentities($webPageTestBase)?>" target="_blank">WebPageTest</a></h4>
	<form class="form form-inline" style="margin-bottom: 0" action="<?php echo htmlentities($showslow_base)?>pagetest.php" method="POST" target="_blank">
	<input type="hidden" name="url" size="40" value="<?php echo htmlentities($url)?>"/>
	<img src="<?php echo assetURL('img/webpagetest-20h.png')?>" height="20" width="28" style="float: left; margin-right: 0.5em"/>
	Location: <select name="location" width="150">
	<?php foreach ($webPageTestLocations as $location) {
		if ($location['tests'] > 50) {
			continue;
		}
	?>
		<option <?php echo htmlentities($location['default']) ? 'selected ' : ''?>value="<?php echo htmlentities($location['id'])?>"><?php echo htmlentities($location['title'])?></option>
	<?php } ?></select>
	<div>
	<label class="checkbox"><input type="checkbox" name="private" id="wpt_private" value="1"<?php if ($webPageTestPrivateByDefault) {?> checked="true"<?php } ?>/> Private</label>
	<label class="checkbox"><input type="checkbox" name="fvonly" id="wpt_fvonly" value="1"<?php if ($webPageTestFirstRunOnlyByDefault) {?> checked="true"<?php } ?>/> First View Only</label>
	</div>
	<input class="btn btn-mini btn-success pull-right" type="submit" value="Start Test"/>
	<?php if (count($pagetest) > 0) {?><a href="#pagetest-table">See test history below</a><?php } ?>
	</form>
	</div>
<?php
}

if (!is_null($redBotBase)) { ?>
	<div class="well pull-left" style="margin: 0.5%; width: 27%">
	<a name="redbot"></a><h4>Run a test using <a href="<?php echo htmlentities($redBotBase)?>" target="_blank"><span style="color: #D33">RED</span>bot</a></h4>
	<form class="form form-inline" style="margin-bottom: 0" action="<?php echo htmlentities($redBotBase)?>" method="GET" target="_blank">
	<input type="hidden" name="uri" size="40" value="<?php echo htmlentities($url)?>"/>
	<label class="checkbox" for="checkallassets"><input type="checkbox" id="checkallassets" name="descend" value="True"<?php if ($redBotCheckAllByDefault) {?> checked<?php } ?>/> Check all components:</label>
	<input class="btn btn-mini btn-success pull-right" type="submit" value="Start Test"/>
	</form>
	</div>
<?php
}

if ($enablePageSpeedInsightsTool) {
	?>
	<div class="well pull-left" style="margin: 0.5%; width: 27%">
	<a name="pagespeedinsights"></a><h4>Run <a href="http://developers.google.com/speed/pagespeed/insights/" target="_blank">Google PageSpeed</a> Insights</h4>
	<img src="<?php echo assetURL('img/pagespeed-20h.png')?>" height="20" width="27" style="float: left; margin-right: 0.5em"/>
	Start Google PageSpeed Insights test
	<a class="btn btn-mini btn-success pull-right" href="https://developers.google.com/speed/pagespeed/insights/?url=<?php echo urlencode($url) ?>" target="_blank">Start Test</a>
	</div>
	<?php
}

if (is_array($customTools)) {
	foreach ($customTools as $name => $title) {
		?>
		<div class="well pull-left" style="margin: 0 1em 1em 0">
		<a name="<?php echo $name ?>"></a><h3><?php echo $title ?></h3>
<?php
		call_user_func('customTool_'.$name, $url);
		?>
		</div>
		<?php
	}
}
?>
</fieldset>
<?php
}

if (!$havemetrics)
{
	?>
	<h2 style="clear: both">Measurements over time</h2>
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

if ($havemetrics)
{
?>
	<a name="graph"></a>
	<h2 style="clear: both">Measurements over time</h2>
	<script>
	ydataversion = <?php if ($enabledMetrics['yslow']) {
		echo json_encode($row['yslow_timestamp']);
	} else {
		?>null<?php
	} ?>;
	psdataversion = <?php if ($enabledMetrics['pagespeed']) {
		echo json_encode($row['pagespeed_timestamp']);
	} else {
		?>null<?php
	} ?>;
	dtdataversion = <?php if ($enabledMetrics['dynatrace']) {
		echo json_encode($row['dynatrace_timestamp']);
	} else {
		?>null<?php
	} ?>;
	eventversion = <?php echo json_encode($eventupdate)?>;
	</script>

<?php
	// Graph
	if ($enableFlot) { ?>
		<style>
		#flot {
			width: 100%;
			height: 320px;
			margin: 0 auto;
		}

		#overview {
			width: 480px;
			height: 60px;
			margin: 1em auto;
		}

		#graphbuttons{
			text-align: center;
			margin: 1em;
		}

		</style>
		<div id="flot"></div>
		<div id="graphcontrols">
			<div id="graphbuttons">
				<button class="btn" id="default">Default Metrics</button>
				<button class="btn" id="clear">Clear Metrics</button>
				<button class="btn" id="reset" disabled="disabled">Reset Zoom</button>
			</div>
			<div id="overview"></div>
		</div>
		<?php
	} else {
		?>

		<div id="my-timeplot" style="height: 250px;"></div>
		<div style="font-size: 0.9em">
		<?php
		if ($enabledMetrics['yslow'] && !is_null($row['yslow_timestamp']))
		{
		?>
		<span style="color: #D0A825">Page Size</span> (in bytes);
		<span style="color: purple">Page Load time (YSlow)</span> (in ms);
		<span style="color: #75CF74">Total Requests</span>;
		<span class="yslow2">YSlow Grade</span> (0-100);
		<?php
		}

		if ($enabledMetrics['pagespeed'] && !is_null($row['pagespeed_timestamp']))
		{
		?>
		<span style="color: #6F4428">Page Speed Grade</span> (0-100);
		<span style="color: #EE4F00">Page Load time (Page Speed)</span> (in ms);
		<?php
		}

		if ($enabledMetrics['dynatrace'] && !is_null($row['dynatrace_timestamp']))
		{
		?>
		<span style="color: #AB0617">dynaTrace rank</span> (0-100);
		<?php
		}

		foreach ($metrics as $name => $metric)
		{
			?><span title="<?php echo htmlentities($metric['description'])?>" style="color: <?php echo array_key_exists('color', $metric) ? $metric['color'] : 'black' ?>"><?php echo htmlentities($metric['title'])?></span> (<a href="<?php echo $showslow_base ?>/details/data_metric.php?metric=<?php echo urlencode($name);?>&urlid=<?php echo urlencode($urlid);?>">csv</a>);
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
	}

	// Custom metrics
	if (count($custom_metrics) > 0) {
		?><a name="custom"></a><fieldset id="custom"><legend>Custom metrics</legend>

		<div class="col">
		Metrics defined for this instance
		<table>
		<?php
		$odd = true;
		foreach ($custom_metrics as $id => $data) {
			$metric = $data['metric'];

			// assume the default value is NUBER
			if (!array_key_exists('type', $metric)) {
				$metric['type'] = NUMBER;
			}

			if ($odd) { ?><tr><?php }

			?><td class="titlecol">

			<label class="checkbox"><?php
			if ($enableFlot) { ?>
			<input type="checkbox" class="metric-toggle" id="custom-<?php echo $data['metric_slug']?>"/><?php
			}

			echo $metric['title'];

			if ($metric['type'] == NUMBER) {
				if (array_key_exists('min', $metric) && array_key_exists('max', $metric)) {
					echo ' ('.$metric['min'].' - '.$metric['max'].')';
				} else if (array_key_exists('min', $metric)) {
					echo ' ('.$metric['min'].' and up)';
				} else if (array_key_exists('max', $metric)) {
					echo ' (Under '.$metric['max'].')';
				}
			}

			?></label>
			</td>
			<?php

			$value = $data['value'];

			if (is_null($value)) {
				?><td colspan="3" class="na">n/a</td><?php
			} else {
				if ($metric['type'] == PERCENT_GRADE){
					$pretty_score = prettyScore($value);
				?>
					<td class="value"><?php echo $pretty_score?> (<i><?php echo htmlentities($value)?></i>%)</td>
					<td><div class="gbox" title="Current <?php echo $metric['title']?>: <?php echo $pretty_score?> (<?php echo $value?>%)"><div class="bar c<?php echo scoreColorStep($value)?>" style="width: <?php echo $value+1?>px"/></div></td><?php
				} else {
					?><td colspan="3" class="value"><?php

					if (array_key_exists('levels', $metric)) {
						?><div class="levelbox-<?php
						if ($value < $metric['levels'][0]) {
							echo 'low';
						} else if ($value < $metric['levels'][1]) {
							echo 'mid';
						} else {
							echo 'high';
						}
						?>"></div><?php
					} else if (array_key_exists('reverselevels', $metric)) {
						?><div class="levelbox-<?php
						if ($value < $metric['reverselevels'][0]) {
							echo 'high';
						} else if ($value < $metric['reverselevels'][1]) {
							echo 'mid';
						} else {
							echo 'low';
						}
						?>"></div><?php
					}

					if ($metric['type'] == BYTES) {
						?><span title="<?php echo $value ?> bytes"><?php echo floor($value/1000) ?>KB</span><?php
					} else {
						echo $value.$metric_types[$metric['type']]['units'];
					}

					?></td><?php
				}
			}

			if (!$odd) { ?></tr><?php }

			$odd = !$odd;
		?>
		</tr>
		<?php
		}
		?>
		</table>
		</div>
		</fieldset>
	<?php
	}

	// Breakdowns for each provider
	foreach ($all_metrics as $provider_name => $provider) {
		if (!is_null($row[$provider_name.'_timestamp']))
		{
			if (!$enabledMetrics[$provider_name]) {
				continue;
			}
		?>
			<a name="<?php echo $provider_name ?>"></a><fieldset id="<?php echo $provider_name ?>"><legend><a href="<?php echo $provider['url']; ?>" target="_blank"><?php echo $provider['title']?></a> metrics</legend>
			<div class="col">
			<?php if (array_key_exists('description', $provider)) {
				?><p><?php echo $provider['description'] ?></p><?php
			}?>
			<table>
			<?php
			foreach ($provider['metrics'] as $section_name => $metrics)
			{
				?><tr><td colspan="8" class="sectionname"><b><?php echo $section_name ?></b></td></tr><?php

				$odd = true;

				foreach ($metrics as $metric) {
					if ($odd) { ?><tr><?php }

					?><td class="titlecol"><?php

					?><label class="checkbox" for="<?php echo $provider_name.'-'.$metric[1] ?>"><?php
					if ($enableFlot) { ?>
					<input type="checkbox" class="metric-toggle" id="<?php echo $provider_name.'-'.$metric[1] ?>"><?php
					}

					if (isset($metric[3])) {
						?><a target="_blank" href="<?php echo $metric[3]?>"><?php echo $metric[0]?></a><?php
					}else{
						echo $metric[0];
					}
					?></label><?php

					$value = $row[$provider_name.'_'.$metric[1]];

					if (is_null($value)) {
						?><td colspan="3" class="na">n/a</td><?php
					} else {
						if ($metric[2] == PERCENT_GRADE){
							$pretty_score = prettyScore($value);
						?>
							<td class="value"><?php echo $pretty_score?> (<i><?php echo htmlentities($value)?></i>%)</td>
							<td><span id="details_<?php echo $provider_name.'_'.$metric[1] ?>" class="details"></span></td>
							<td><div class="gbox" title="Current <?php echo $provider['score_name']?>: <?php echo $pretty_score?> (<?php echo $value?>%)"><div class="bar c<?php echo scoreColorStep($value)?>" style="width: <?php echo $value+1?>px"/></div></td><?php
						} else {
							?><td colspan="3" class="value"><?php

							if (!array_key_exists(4, $metric)) {
							} else if ($metric[4] == 'levels') {
								?><div class="levelbox-<?php
								if ($value < $metric[5][0]) {
									echo 'low';
								} else if ($value < $metric[5][1]) {
									echo 'mid';
								} else {
									echo 'high';
								}
								?>"></div><?php
							} else if ($metric[4] == 'reverselevels') {
								?><div class="levelbox-<?php
								if ($value < $metric[5][0]) {
									echo 'high';
								} else if ($value < $metric[5][1]) {
									echo 'mid';
								} else {
									echo 'low';
								}
								?>"></div><?php
							}

							if ($metric[2] == BYTES) {
								?><span title="<?php echo $value ?> bytes"><?php echo floor($value/1000) ?>KB</span><?php
							} else {
								echo $value.$metric_types[$metric[2]]['units'];
							}

							?></td><?php
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
		</div>
		</fieldset>
	<?php
		}
	}
}

if ($enabledMetrics['yslow'] && !is_null($row['yslow_timestamp'])) {
?>
	<a name="yslow-table"></a><h2>YSlow measurements history (<a href="<?php echo $showslow_base ?>/details/data.php?ver=<?php echo urlencode($row['yslow_timestamp'])?>&urlid=<?php echo urlencode($urlid)?>">csv</a>)</h2>
	<div id="measurementstable" class="measurementstable"></div>
	<?php
}

if ($enabledMetrics['pagespeed'] && !is_null($row['pagespeed_timestamp'])) {
?>
	<a name="pagespeed-table"></a><h2>Page Speed measurements history (<a href="<?php echo $showslow_base ?>/details/data_pagespeed.php?ver=<?php echo urlencode($row['pagespeed_timestamp'])?>&urlid=<?php echo urlencode($urlid)?>">csv</a>)</h2>
	<div id="ps_measurementstable" class="measurementstable"></div>
<?php
}

if ($enabledMetrics['dynatrace'] && !is_null($row['dynatrace_timestamp'])) {
?>
	<a name="dynatrace-table"></a><h2>dynaTrace measurements history (<a href="<?php echo $showslow_base ?>/details/data_dynatrace.php?ver=<?php echo urlencode($row['dynatrace_timestamp'])?>&urlid=<?php echo urlencode($urlid)?>">csv</a>)</h2>
	<div id="dt_measurementstable" class="measurementstable"></div>
<?php
}

if (count($pagetest) > 0) {
?>
	<a name="pagetest-table"></a><h2>WebPageTest data collected</h2>

	<p>You can see latest <a href="<?php echo $webPageTestBase.'results.php?test='.htmlentities($pagetest[0]['test_id']) ?>" target="_blank">PageTest report for <?php echo htmlentities($url)?></a> or check the archive:</p>

	<table id="wpttable">

	<form class="form" action="<?php echo $showslow_base ?>/pagetestcompare.php" method="POST">

	<tr class="yui-dt-hd">
	<th>
		<div style="font-size: xx-small; text-align: left">
		<input type="submit" name="go" value="Compare"/>
		<input type="checkbox" name="repeat" value="true" id="wptrepeat"/>
		<label for="wptrepeat">repeat view</label>
		</div>
	</th>
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
	<td>
		<input id="wpttest<?php echo htmlentities($pagetestentry['test_id']) ?>" type="checkbox" name="compare[]" value="<?php echo htmlentities($pagetestentry['test_id']) ?>" />
		<input type="hidden" name="label[]" value="<?php echo htmlentities($pagetestentry['t']) ?>" />

		<label for="wpttest<?php echo htmlentities($pagetestentry['test_id']) ?>" type="checkbox" name="compare[]"><?php echo htmlentities($pagetestentry['t'])?></label></td>
	<td><?php echo htmlentities($location)?></td>
	<td><a href="<?php echo $webPageTestBase.'results.php?test='.htmlentities($pagetestentry['test_id']) ?>" target="_blank">view PageTest report</a></td>
	</tr>
<?php
	}
?>
	</form>
	</table>
<?php
}

if (count($har) > 0) {
	$har_url = is_null($har[0]['link']) ?
		$showslow_base.'details/har.php?id='.urlencode($har[0]['id']).'callback=onInputData'
		: $har[0]['link'];
?>
	<a name="har-table"></a><h2>HAR data collected</h2>

	<p>You can see latest HAR data in the viewer here: <a href="<?php echo htmlentities($HARViewerBase)?>?inputUrl=<?php echo urlencode($har_url) ?>" target="_blank">HAR for <?php echo htmlentities($url)?></a>.</p>

	<table id="hartable">
	<tr><th>Time</th><th>HAR</th></tr>
<?php
	foreach ($har as $harentry) {
		$har_url = is_null($harentry['link']) ?
			$showslow_base.'details/har.php?id='.urlencode($harentry['id']).'callback=onInputData'
			: $harentry['link'];
?>
	<tr><td><?php echo htmlentities($harentry['t'])?></td><td><a href="<?php echo htmlentities($HARViewerBase)?>?inputUrl=<?php echo urlencode($har_url) ?>" target="_blank">view in HAR viewer</a></td></tr>
<?php
	}
?>
	</table>
<?php
}

if ($enableFlot) {
	?><script src="<?php echo assetURL('details/showslow.flot.js') ?>"></script><?php
}

require_once(dirname(dirname(__FILE__)).'/footer.php');
