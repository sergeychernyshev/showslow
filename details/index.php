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

// last timestamps
$query = sprintf("SELECT id, UNIX_TIMESTAMP(last_update) as t, last_event_update,
		yslow2_last_id, pagespeed_last_id, dynatrace_last_id
	FROM urls
	WHERE urls.url = '%s'", mysql_real_escape_string($url));
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$row = mysql_fetch_assoc($result);
$lastupdate = $row['t'];
$eventupdate = $row['last_event_update'];
$urlid = $row['id'];
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
// latest YSlow result
$query = sprintf("SELECT timestamp, w, o, i, lt,
		ynumreq,	ycdn,		yexpires,	ycompress,	ycsstop,
		yjsbottom,	yexpressions,	yexternal,	ydns,		yminify,
		yredirects,	ydupes,		yetags,		yxhr,		yxhrmethod,
		ymindom,	yno404,		ymincookie,	ycookiefree,	ynofilter,
		yimgnoscale,	yfavicon,	details
		FROM yslow2 y
		WHERE id = %d",
	mysql_real_escape_string($yslow2_last_id)

);
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$row = mysql_fetch_assoc($result);
mysql_free_result($result);

// Latest PageSpeed result
$query = sprintf("SELECT timestamp, w, o, l, r, t, v,
			pMinifyCSS, pMinifyJS, pOptImgs, pImgDims, pCombineJS, pCombineCSS,
			pPutCssInTheDocumentHead, pBrowserCache, pProxyCache, pNoCookie, 
			pParallelDl, pCssSelect, pOptimizeTheOrderOfStylesAndScripts, pDeferJS, pGzip,
			pMinRedirect, pCssExpr, pUnusedCSS, pMinDns, pDupeRsrc, pScaleImgs,
			pMinifyHTML, pMinimizeRequestSize, pSpecifyCharsetEarly
		FROM pagespeed p
		WHERE id = %d",
	mysql_real_escape_string($pagespeed_last_id)
);
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$ps_row = mysql_fetch_assoc($result);
mysql_free_result($result);

// Latest dynatrace result
$query = sprintf("SELECT timestamp, rank, cache, net, server, js
		FROM dynatrace
		WHERE id = %d",
	mysql_real_escape_string($dynatrace_last_id)
);
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$dt_row = mysql_fetch_assoc($result);
mysql_free_result($result);

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
$query = sprintf("SELECT pagetest.timestamp as t, test_url FROM pagetest WHERE pagetest.url_id = '%d' ORDER BY timestamp DESC",
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

if (!$row && !$ps_row && !$dt_row && $har === false) {
	?>No data is available yet
</body></html>
<?php 
	exit;
}

if ($row || $ps_row || $dt_row)
{
	?>
	<table cellpadding="15" cellspacing="5" style="margin-top: 1em"><tr>
	<?php 
	// YSlow grade indicator
	if ($row) {
	?>
		<td valign="top" align="center" style="background: #ddd; border: 1px solid black">
		<h2>Current <a href="http://developer.yahoo.com/yslow/">YSlow</a> grade: <?php echo yslowPrettyScore($row['o'])?> (<i><?php echo htmlentities($row['o'])?></i>)</h2>

		<img src="http://chart.apis.google.com/chart?chs=225x125&cht=gom&chd=t:<?php echo urlencode($row['o'])?>&chl=<?php echo urlencode(yslowPrettyScore($row['o']).' ('.$row['o'].')')?>" alt="<?php echo yslowPrettyScore($row['o'])?> (<?php echo htmlentities($row['o'])?>)" title="Current YSlow grade: <?php echo yslowPrettyScore($row['o'])?> (<?php echo htmlentities($row['o'])?>)" style="padding: 0 0 20px 0; border: 1px solid black; background: white"/>
		</td>
	<?php 
	}

	// Page Speed score indicator
	if ($ps_row) {
	?>
		<td valign="top" align="center" style="background: #ddd; border: 1px solid black">
		<h2>Current <a href="http://code.google.com/speed/page-speed/">Page Speed</a> score: <?php echo yslowPrettyScore($ps_row['o'])?> (<i><?php echo htmlentities($ps_row['o'])?></i>)</h2>

		<img src="http://chart.apis.google.com/chart?chs=225x125&cht=gom&chd=t:<?php echo urlencode($ps_row['o'])?>&chl=<?php echo urlencode(yslowPrettyScore($ps_row['o']).' ('.$ps_row['o'].')')?>" alt="<?php echo yslowPrettyScore($ps_row['o'])?> (<?php echo htmlentities($ps_row['o'])?>)" title="Current Page Speed score: <?php echo yslowPrettyScore($ps_row['o'])?> (<?php echo htmlentities($ps_row['o'])?>)" style="padding: 0 0 20px 0; border: 1px solid black; background: white"/>
		</td>
	<?php 
	}

	// dynaTrace rank indicator
	if ($dt_row) {
	?>
		<td valign="top" align="center" style="background: #ddd; border: 1px solid black">
		<h2>Current <a href="http://ajax.dynatrace.com/">dynaTrace rank</a> score: <?php echo yslowPrettyScore($dt_row['rank'])?> (<i><?php echo htmlentities($dt_row['rank'])?></i>)</h2>

		<img src="http://chart.apis.google.com/chart?chs=225x125&cht=gom&chd=t:<?php echo urlencode($dt_row['rank'])?>&chl=<?php echo urlencode(yslowPrettyScore($dt_row['rank']).' ('.$dt_row['rank'].')')?>" alt="<?php echo yslowPrettyScore($dt_row['rank'])?> (<?php echo htmlentities($dt_row['rank'])?>)" title="Current dynaTrace  rank: <?php echo yslowPrettyScore($dt_row['rank'])?> (<?php echo htmlentities($dt_row['rank'])?>)" style="padding: 0 0 20px 0; border: 1px solid black; background: white"/>
		</td>
	<?php 
	}
	?>
	</tr></table>
	<?php if (!is_null($webPageTestBase)) { ?>
	<a name="pagetest"/><h2>Run a test using <a href="<?php echo htmlentities($webPageTestBase)?>" target="_blank">WebPageTest</a> and store the results</h2>
	<form action="<?php echo htmlentities($showslow_base)?>/pagetest.php" method="GET" target="_blank">
	<input type="hidden" name="url" size="40" value="<?php echo htmlentities($url)?>"/>
	Location: <select name="location">
	<?php foreach ($webPageTestLocations as $code => $label) { ?>
	<option value="<?php echo htmlentities($code)?>"><?php echo htmlentities($label)?></option>
	<?php } ?></select>
	<input type="checkbox" name="private" id="wpt_private" value="1"<?php if ($webPageTestPrivateByDefault) {?> checked="true"<?php } ?>/><label for="wpt_private">Private</label>
	<input type="checkbox" name="fvonly" id="wpt_fvonly" value="1"<?php if ($webPageTestFirstRunOnlyByDefault) {?> checked="true"<?php } ?>/><label for="wpt_fvonly">First View Only</label>
	<input type="submit" style="font-weight: bold" value="start test &gt;&gt;"/>
	<?php if (count($pagetest) > 0) {?><a href="#pagetest-table">See test history below</a><?php } ?>
	</form>
	<?php } ?>

	<?php 
	// Graph
	?>
	<script>
	url = '<?php echo htmlentities($url)?>';
	ydataversion = '<?php echo urlencode($row['timestamp'])?>';
	psdataversion = '<?php echo urlencode($ps_row['timestamp'])?>';
	dtdataversion = '<?php echo urlencode($dt_row['timestamp'])?>';
	eventversion = '<?php echo urlencode($eventupdate)?>';
	</script>

	<a name="graph"/><h2 style="clear: both">Measurements over time</h2>
	<div id="my-timeplot" style="height: 250px;"></div>
	<div style="fint-size: 0.2em">
	<?php
	if ($row)
	{
	?>
	<span style="color: #D0A825">Page Size</span> (in bytes);
	<span style="color: purple">Page Load time (YSlow)</span> (in ms);
	<span style="color: #75CF74">Total Requests</span>;
	<span class="yslow2">YSlow Grade</span> (0-100);
	<?php
	}

	if ($ps_row)
	{
	?>
	<span style="color: #6F4428">Page Speed Grade</span> (0-100);
	<span style="color: #EE4F00">Page Load time (Page Speed)</span> (in ms);
	<?php
	}

	if ($dt_row)
	{
	?>
	<span style="color: #AB0617">dynaTrace rank</span> (0-100);
	<?php
	}

	foreach ($metrics as $name => $metric)
	{
		?><span title="<?php echo htmlentities($metric['description'])?>" style="color: <?php echo array_key_exists('color', $metric) ? $metric['color'] : 'black' ?>"><?php echo htmlentities($metric['title'])?></span> (<a href="data_metric.php?metric=<?php echo urlencode($name);?>&url=<?php echo urlencode($url);?>">csv</a>);<?php
	}
	?>
	</div>

	<?php 

	// YSlow breakdown
	if ($row) {
		function printYSlowGradeBreakdown($name, $anchor, $slug) {
			global $row;

			$value = $row[$slug];

			?><td><a href="http://developer.yahoo.com/performance/rules.html#<?php echo $anchor?>"><?php echo $name?></a></td>
			<?php if ($value >= 0) {?>
			<td><?php echo yslowPrettyScore($value)?> (<i><?php echo htmlentities($value)?></i>)</td>
			<td><span id="details_<?php echo $slug ?>" class="details"></span></td>
			<td><div style="background-color: silver; width: 103px" title="Current YSlow grade: <?php echo yslowPrettyScore($value)?> (<?php echo $value?>)"><div style="width: <?php echo $value+3?>px; height: 0.7em; background-color: <?php echo scoreColor($value)?>"/></div></td>
			<?php } else { ?>
			<td><i>N/A</i></td>
			<td></td>
			<?php } ?>
			<td>&nbsp;&nbsp;</td><?php 
		}

		if ($row['i'] <> 'yslow1') {

		$details = json_decode($row['details'], true);
?>
		<script>
<?php
		$comps = array();

		foreach ($details['g'] as $n => $y) {
			if (is_array($y) && array_key_exists('components', $y)) {
				$comps[$n] = $y['components'];
			}
		}
?>
		var details = <?php echo json_encode($comps)?>;
		</script>

		<a name="yslow"/><h2 style="clear: both">YSlow breakdown</h2>
		<table>
			<tr>
			<?php echo printYSlowGradeBreakdown('Make fewer HTTP requests', 'num_http', 'ynumreq')?>
			<?php echo printYSlowGradeBreakdown('Use a Content Delivery Network (CDN)', 'cdn', 'ycdn')?>
			</tr>
			<tr>
			<?php echo printYSlowGradeBreakdown('Add Expires headers', 'expires', 'yexpires')?>
			<?php echo printYSlowGradeBreakdown('Compress components with gzip', 'gzip', 'ycompress')?>
			</tr>
			<tr>
			<?php echo printYSlowGradeBreakdown('Put CSS at top', 'css_top', 'ycsstop')?>
			<?php echo printYSlowGradeBreakdown('Put JavaScript at bottom', 'js_bottom', 'yjsbottom')?>
			</tr>
			<tr>
			<?php echo printYSlowGradeBreakdown('Avoid CSS expressions', 'css_expressions', 'yexpressions')?>
			<?php echo printYSlowGradeBreakdown('Make JavaScript and CSS external', 'external', 'yexternal')?>
			</tr>
			<tr>
			<?php echo printYSlowGradeBreakdown('Reduce DNS lookups', 'dns_lookups', 'ydns')?>
			<?php echo printYSlowGradeBreakdown('Minify JavaScript and CSS', 'minify', 'yminify')?>
			</tr>
			<tr>
			<?php echo printYSlowGradeBreakdown('Avoid URL redirects', 'redirects', 'yredirects')?>
			<?php echo printYSlowGradeBreakdown('Remove duplicate JavaScript and CSS', 'js_dupes', 'ydupes')?>
			</tr>
			<tr>
			<?php echo printYSlowGradeBreakdown('Configure entity tags (ETags)', 'etags', 'yetags')?>
			<?php echo printYSlowGradeBreakdown('Make AJAX cacheable', 'cacheajax', 'yxhr')?>
			</tr>
			<tr>
			<?php echo printYSlowGradeBreakdown('Use GET for AJAX requests', 'ajax_get', 'yxhrmethod')?>
			<?php echo printYSlowGradeBreakdown('Reduce the number of DOM elements', 'min_dom', 'ymindom')?>
			</tr>
			<tr>
			<?php echo printYSlowGradeBreakdown('Avoid HTTP 404 (Not Found) error', 'no404', 'yno404')?>
			<?php echo printYSlowGradeBreakdown('Reduce cookie size', 'cookie_size', 'ymincookie')?>
			</tr>
			<tr>
			<?php echo printYSlowGradeBreakdown('Use cookie-free domains', 'cookie_free', 'ycookiefree')?>
			<?php echo printYSlowGradeBreakdown('Avoid AlphaImageLoader filter', 'no_filters', 'ynofilter')?>
			</tr>
			<tr>
			<?php echo printYSlowGradeBreakdown('Do not scale images in HTML', 'no_scale', 'yimgnoscale')?>
			<?php echo printYSlowGradeBreakdown('Make favicon small and cacheable', 'favicon', 'yfavicon')?>
			</tr>
		</table>	
	<?php 
		}
	}

	// PageSpeed breakdown
	if ($ps_row) {
		function printPageSpeedGradeBreakdown($name, $doc, $value) {
			if ($doc)
			{
			?><td><a href="http://code.google.com/speed/page-speed/docs/<?php echo $doc?>"><?php echo $name?></a></td><?php
			} else {
			?><td><?php echo $name?></td><?php
			}

			if ($value >= 0) {?>
			<td><?php echo yslowPrettyScore($value)?> (<i><?php echo htmlentities($value)?></i>)</td>
			<td><div style="background-color: silver; width: 103px" title="Current Page Speed score: <?php echo yslowPrettyScore($value)?> (<?php echo $value?>)"><div style="width: <?php echo $value+3?>px; height: 0.7em; background-color: <?php echo scoreColor($value)?>"/></div></td>
			<?php } else { ?>
			<td><i>N/A</i></td>
			<td></td>
			<?php } ?>
			<td>&nbsp;&nbsp;</td><?php 
		}
	?>
	<a name="pagespeed"/><h2 style="clear: both">Page Speed breakdown</h2>
	<table>
	<tr><td colspan="6"><b>Optimize caching</b></td></tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Leverage browser caching', 'caching.html#LeverageBrowserCaching', $ps_row['pBrowserCache'])?>
		<?php echo printPageSpeedGradeBreakdown('Leverage proxy caching', 'caching.html#LeverageProxyCaching', $ps_row['pProxyCache'])?>
		</tr>
	<tr><td colspan="6" style="padding-top: 1em"><b>Minimize round-trip times</b></td></tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Minimize DNS lookups', 'rtt.html#MinimizeDNSLookups', $ps_row['pMinDns'])?>
		<?php echo printPageSpeedGradeBreakdown('Minimize redirects', 'rtt.html#AvoidRedirects', $ps_row['pMinRedirect'])?>
		</tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Combine external JavaScript', 'rtt.html#CombineExternalJS', $ps_row['pCombineJS'])?>
		<?php echo printPageSpeedGradeBreakdown('Combine external CSS', 'rtt.html#CombineExternalCSS', $ps_row['pCombineCSS'])?>
		</tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Optimize the order of styles and scripts', 'rtt.html#PutStylesBeforeScripts', $ps_row['pOptimizeTheOrderOfStylesAndScripts'])?>
		<?php echo printPageSpeedGradeBreakdown('Parallelize downloads across hostnames', 'rtt.html#ParallelizeDownloads', $ps_row['pParallelDl'])?>
		</tr>
	<tr><td colspan="6" style="padding-top: 1em"><b>Minimize request overhead</b></td></tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Minimize Request Size', 'request.html#MinimizeRequestSize', $ps_row['pMinimizeRequestSize'])?>
		<?php echo printPageSpeedGradeBreakdown('Serve static content from a cookieless domain', 'request.html#ServeFromCookielessDomain', $ps_row['pNoCookie'])?>
		</tr>
		<tr>
		</tr>
	<tr><td colspan="6" style="padding-top: 1em"><b>Minimize payload size</b></td></tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Enable compression', 'payload.html#GzipCompression', $ps_row['pGzip'])?>
		<?php echo printPageSpeedGradeBreakdown('Remove unused CSS', 'payload.html#RemoveUnusedCSS', $ps_row['pUnusedCSS'])?>
		</tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Minify JavaScript', 'payload.html#MinifyJS', $ps_row['pMinifyJS'])?>
		<?php echo printPageSpeedGradeBreakdown('Minify CSS', 'payload.html#MinifyCSS', $ps_row['pMinifyCSS'])?>
		</tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Minify HTML', 'payload.html#MinifyHTML', $ps_row['pMinifyHTML'])?>
		<?php echo printPageSpeedGradeBreakdown('Defer loading of JavaScript', 'payload.html#DeferLoadingJS', $ps_row['pDeferJS'])?>
		</tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Optimize images', 'payload.html#CompressImages', $ps_row['pOptImgs'])?>
		<?php echo printPageSpeedGradeBreakdown('Serve scaled images', 'payload.html#ScaleImages', $ps_row['pScaleImgs'])?>
		</tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Serve resources from a consistent URL', 'payload.html#duplicate_resources', $ps_row['pDupeRsrc'])?>
		</tr>
	<tr><td colspan="6" style="padding-top: 1em"><b>Optimize browser rendering</b></td></tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Use efficient CSS selectors', 'rendering.html#UseEfficientCSSSelectors', $ps_row['pCssSelect'])?>
		<?php echo printPageSpeedGradeBreakdown('Avoid CSS expressions', 'rendering.html#AvoidCSSExpressions', $ps_row['pCssExpr'])?>
		</tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Put CSS In The Document Head', 'rendering.html#PutCSSInHead', $ps_row['pPutCssInTheDocumentHead'])?>
		<?php echo printPageSpeedGradeBreakdown('Specify Charset Early', 'rendering.html#SpecifyCharsetEarly', $ps_row['pSpecifyCharsetEarly'])?>
		</tr>
	</table>	
<?php 
	}

	// dynaTrace breakdown
	if ($dt_row) {
		function printDynaTraceRankBreakdown($name, $doc, $value) {
			if ($doc)
			{
				// TODO: impelement this
			?><td><a href="https://community.dynatrace.com/community/display/PUB/<?php echo $doc?>"><?php echo $name?></a></td>	<?php
			} else {
			?><td><?php echo $name?></td><?php
			}

			if ($value >= 0) {?>
			<td><?php echo yslowPrettyScore($value)?> (<i><?php echo htmlentities($value)?></i>)</td>
			<td><div style="background-color: silver; width: 103px" title="Current dynaTrace rank: <?php echo yslowPrettyScore($value)?> (<?php echo $value?>)"><div style="width: <?php echo $value+3?>px; height: 0.7em; background-color: <?php echo scoreColor($value)?>"/></div></td>
			<?php } else { ?>
			<td><i>N/A</i></td>
			<td></td>
			<?php } ?>
			<td>&nbsp;&nbsp;</td><?php 
		}?>
	<a name="dynatrace"/><h2 style="clear: both">dynaTrace breakdown</h2>
	<table>
		<tr>
		<?php echo printDynaTraceRankBreakdown('Caching Rank', 'Best+Practices+on+Browser+Caching', $dt_row['cache'])?>
		<?php echo printDynaTraceRankBreakdown('Network Rank', 'Best+Practices+on+Network+Requests+and+Roundtrips', $dt_row['net'])?>
		</tr>
		<tr>
		<?php echo printDynaTraceRankBreakdown('Server-side rank', 'Best+Practices+on+Server-Side+Performance+Optimization', $dt_row['server'])?>
		<?php echo printDynaTraceRankBreakdown('JavaScript Rank', 'Best+Practices+on+JavaScript+and+AJAX+Performance', $dt_row['js'])?>
		</tr>
	</table>	
<?php 
	}
}

if ($row) {
?>
	<a name="yslow-table"/><h2>YSlow measurements history (<a href="data.php?ver=<?php echo urlencode($row['timestamp'])?>&url=<?php echo urlencode($url)?>">csv</a>)</h3>
	<div id="measurementstable"></div>
	<?php 
}

if ($ps_row) {
?>
	<a name="pagespeed-table"/><h2>Page Speed measurements history (<a href="data_pagespeed.php?ver=<?php echo urlencode($ps_row['timestamp'])?>&url=<?php echo urlencode($url)?>">csv</a>)</h3>
	<div id="ps_measurementstable"></div>
<?php 
}

if ($dt_row) {
?>
	<a name="dynatrace-table"/><h2>dynaTrace measurements history (<a href="data_dynatrace.php?ver=<?php echo urlencode($dt_row['timestamp'])?>&url=<?php echo urlencode($url)?>">csv</a>)</h3>
	<div id="dt_measurementstable"></div>
<?php 
}

if (count($pagetest) > 0) {
?>
	<a name="pagetest-table"/><h2>WebPageTest data collected</h2>

	<p>You can see latest <a href="<?php echo htmlentities($pagetest[0]['test_url']) ?>" target="_blank">PageTest report for <?php echo htmlentities($url)?></a> or check the archive:</p>

	<table cellpadding="5" cellspacing="0" border="1">
	<tr><th>Time</th><th>PageTest</th></tr>
<?php
	foreach ($pagetest as $pagetestentry) {
?>
	<tr><th><?php echo htmlentities($pagetestentry['t'])?></th><th><a href="<?php echo htmlentities($pagetestentry['test_url'])?>" target="_blank">view PageTest report</a></th></tr>
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
	<tr><th><?php echo htmlentities($harentry['t'])?></th><th><a href="<?php echo htmlentities($HARViewerBase)?>?inputUrl=<?php echo $showslow_base?>details/har.php%3Fid%3D<?php echo urlencode($harentry['id'])?>%26callback%3DonInputData" target="_blank">view in HAR viewer</a></th></tr>
<?php
	}
?>
	</table>
<?php
}

require_once(dirname(dirname(__FILE__)).'/footer.php');
