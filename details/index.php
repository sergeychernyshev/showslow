<?php 
require_once(dirname(dirname(__FILE__)).'/global.php');

if (!array_key_exists('url', $_GET) || filter_var($_GET['url'], FILTER_VALIDATE_URL) === false) {
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

?><html>
<head>
<title>Show Slow: Details for <?php echo htmlentities($_GET['url'])?></title>
<style type="text/css">
body {
margin:0;
padding:1em;
}

.yui-dt-paginator {
font-size: small;
}
</style>
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?2.7.0/build/fonts/fonts-min.css&2.7.0/build/tabview/assets/skins/sam/tabview.css">
<script type="text/javascript" src="<?php echo $TimePlotBase?>timeplot-api.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/yuiloader/yuiloader-min.js"></script>
<script src="details.js?v=4" type="text/javascript"></script>
<script>
<?php
echo 'var metrics = '.json_encode($metrics);
?>
</script>
<?php if ($showFeedbackButton) {?>
<script type="text/javascript">
var uservoiceOptions = {
  /* required */
  key: 'showslow',
  host: 'showslow.uservoice.com', 
  forum: '18807',
  showTab: true,  
  /* optional */
  alignment: 'right',
  background_color:'#f00', 
  text_color: 'white',
  hover_color: '#06C',
  lang: 'en'
};

function _loadUserVoice() {
  var s = document.createElement('script');
  s.setAttribute('type', 'text/javascript');
  s.setAttribute('src', ("https:" == document.location.protocol ? "https://" : "http://") + "cdn.uservoice.com/javascripts/widgets/tab.js");
  document.getElementsByTagName('head')[0].appendChild(s);
}
_loadSuper = window.onload;
window.onload = (typeof window.onload != 'function') ? _loadUserVoice : function() { _loadSuper(); _loadUserVoice(); };
</script>
<?php } ?>
<?php if ($googleAnalyticsProfile) {?>
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '<?php echo $googleAnalyticsProfile ?>']);
_gaq.push(['_trackPageview']);

(function() {
var ga = document.createElement('script');
ga.src = ('https:' == document.location.protocol ?
    'https://ssl' : 'http://www') +
    '.google-analytics.com/ga.js';
ga.setAttribute('async', 'true');
document.documentElement.firstChild.appendChild(ga);
})();
</script>
<?php }?>
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
</head>
<body class="yui-skin-sam" onload="onLoad('<?php echo urlencode($_GET['url'])?>', ydataversion, psdataversion, eventversion);" onresize="onResize();">
<a href="http://www.showslow.org/"><img src="../showslow_icon.png" style="float: right; margin-left: 1em; border: 0"/></a>
<div style="float: right">powered by <a href="http://www.showslow.org/">showslow</a></div>
<h1><a title="Click here to go to home page" href="../">Show Slow</a>: Details for <a href="<?php echo htmlentities($_GET['url'])?>"><?php echo htmlentities(substr($_GET['url'], 0, 30))?><?php if (strlen($_GET['url']) > 30) { ?>...<?php } ?></a></h1>
<?php 
// last event timestamp
$query = sprintf("SELECT id, yslow2_last_id, pagespeed_last_id, last_event_update FROM urls WHERE urls.url = '%s'", mysql_real_escape_string($_GET['url']));
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$row = mysql_fetch_assoc($result);
$eventupdate = $row['last_event_update'];
$urlid = $row['id'];
$yslow2_last_id = $row['yslow2_last_id'];
$pagespeed_last_id = $row['pagespeed_last_id'];
mysql_free_result($result);

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
			pCssInHead, pBrowserCache, pProxyCache, pNoCookie, pCookieSize,
			pParallelDl, pCssSelect, pCssJsOrder, pDeferJS, pGzip,
			pMinRedirect, pCssExpr, pUnusedCSS, pMinDns, pDupeRsrc, pScaleImgs,
			pMinifyHTML, pMinimizeRequestSize, pOptimizeTheOrderOfStylesAndScripts,
			pPutCssInTheDocumentHead, pSpecifyCharsetEarly
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

// checking if there is har data
$query = sprintf("SELECT har.timestamp as t, har.id as id FROM har, urls WHERE urls.url = '%s' AND har.url_id = urls.id ORDER BY timestamp DESC",
	mysql_real_escape_string($_GET['url'])
);

$result = mysql_query($query);

if (!$result) {
        error_log(mysql_error());
}

$har = array();
while ($har_row = mysql_fetch_assoc($result)) {
	$har[] = $har_row;
}

mysql_free_result($result);

if (!$row && !$ps_row && $har === false) {
	?>No data is available yet
</body></html>
<?php 
	exit;
}

if ($row || $ps_row)
{
	?>
	<table cellpadding="15" cellspacing="5"><tr>
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

	// YSlow grade indicator
	if ($ps_row) {
	?>
		<td valign="top" align="center" style="background: #ddd; border: 1px solid black">
		<h2>Current <a href="http://code.google.com/speed/page-speed/">PageSpeed</a> grade: <?php echo yslowPrettyScore($ps_row['o'])?> (<i><?php echo htmlentities($ps_row['o'])?></i>)</h2>

		<img src="http://chart.apis.google.com/chart?chs=225x125&cht=gom&chd=t:<?php echo urlencode($ps_row['o'])?>&chl=<?php echo urlencode(yslowPrettyScore($ps_row['o']).' ('.$ps_row['o'].')')?>" alt="<?php echo yslowPrettyScore($ps_row['o'])?> (<?php echo htmlentities($ps_row['o'])?>)" title="Current PageSpeed grade: <?php echo yslowPrettyScore($ps_row['o'])?> (<?php echo htmlentities($ps_row['o'])?>)" style="padding: 0 0 20px 0; border: 1px solid black; background: white"/>
		</td>
	<?php 
	}
	?>
	</tr></table>

	<?php 
	// Graph
	?>
	<script>
	ydataversion = '<?php echo urlencode($row['timestamp'])?>';
	psdataversion = '<?php echo urlencode($ps_row['timestamp'])?>';
	eventversion = '<?php echo urlencode($eventupdate)?>';
	</script>

	<h2 style="clear: both">Measurements over time</h2>
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
	<span style="color: #6F4428">PageSpeed Grade</span> (0-100);
	<span style="color: #EE4F00">Page Load time (Page Speed)</span> (in ms);
	<?php
	}

	foreach ($metrics as $name => $metric)
	{
		?><span title="<?php echo htmlentities($metric['description'])?>" style="color: <?php echo array_key_exists('color', $metric) ? $metric['color'] : 'black' ?>"><?php echo htmlentities($metric['title'])?> (<a href="data_metric.php?metric=<?php echo urlencode($name);?>&url=<?php echo urlencode($_GET['url']);?>">csv</a>)</span>;<?php
	}
	?>
	</div>

	<?php 

	// YSlow breakdown
	if ($row) {

	function printYSlowGradeBreakdown($name, $anchor, $slug) {
		global $row;

		$value = $row[$slug];

	?>
			<td><a href="http://developer.yahoo.com/performance/rules.html#<?php echo $anchor?>"><?php echo $name?></a></td>
			<?php if ($value >= 0) {?>
			<td><?php echo yslowPrettyScore($value)?> (<i><?php echo htmlentities($value)?></i>)</td>
			<td><span id="details_<?php echo $slug ?>" class="details"></span></td>
			<td><div style="background-color: silver; width: 103px" title="Current YSlow grade: <?php echo yslowPrettyScore($value)?> (<?php echo $value?>)"><div style="width: <?php echo $value+3?>px; height: 0.7em; background-color: <?php echo scoreColor($value)?>"/></div></td>
			<?php } else { ?>
			<td><i>N/A</i></td>
			<td></td>
			<?php } ?>
			<td>&nbsp;&nbsp;</td>
<?php 
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

		<h2 style="clear: both">YSlow breakdown</h2>
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
?>
		<td><a href="http://code.google.com/speed/page-speed/docs/<?php echo $doc?>"><?php echo $name?></a></td>
		<?php
		} else {
?>
		<td><?php echo $name?></td>
		<?php
		}

		if ($value >= 0) {?>
		<td><?php echo yslowPrettyScore($value)?> (<i><?php echo htmlentities($value)?></i>)</td>
		<td><div style="background-color: silver; width: 103px" title="Current PageSpeed grade: <?php echo yslowPrettyScore($value)?> (<?php echo $value?>)"><div style="width: <?php echo $value+3?>px; height: 0.7em; background-color: <?php echo scoreColor($value)?>"/></div></td>
		<?php } else { ?>
		<td><i>N/A</i></td>
		<td></td>
		<?php } ?>
		<td>&nbsp;&nbsp;</td>
<?php 
}
?>
	<h2 style="clear: both">PageSpeed breakdown</h2>
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
		<?php echo printPageSpeedGradeBreakdown('Optimize the order of styles and scripts', 'rtt.html#PutStylesBeforeScripts', $ps_row['pCssJsOrder'])?>
		<?php echo printPageSpeedGradeBreakdown('Parallelize downloads across hostnames', 'rtt.html#ParallelizeDownloads', $ps_row['pParallelDl'])?>
		</tr>
	<tr><td colspan="6" style="padding-top: 1em"><b>Minimize request size</b></td></tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Minimize cookie size', 'request.html#MinimizeCookieSize', $ps_row['pCookieSize'])?>
		<?php echo printPageSpeedGradeBreakdown('Serve static content from a cookieless domain', 'request.html#ServeFromCookielessDomain', $ps_row['pNoCookie'])?>
		</tr>
	<tr><td colspan="6" style="padding-top: 1em"><b>Minimize payload size</b></td></tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Enable gzip compression', 'payload.html#GzipCompression', $ps_row['pGzip'])?>
		<?php echo printPageSpeedGradeBreakdown('Remove unused CSS', 'payload.html#RemoveUnusedCSS', $ps_row['pUnusedCSS'])?>
		</tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Minify JavaScript', 'payload.html#MinifyJS', $ps_row['pMinifyJS'])?>
		<?php echo printPageSpeedGradeBreakdown('Minify CSS', 'payload.html#MinifyCSS', $ps_row['pMinifyCSS'])?>
		</tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Defer loading of JavaScript', 'payload.html#DeferLoadingJS', $ps_row['pDeferJS'])?>
		<?php echo printPageSpeedGradeBreakdown('Optimize images', 'payload.html#CompressImages', $ps_row['pOptImgs'])?>
		</tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Serve resources from a consistent URL', 'payload.html#duplicate_resources', $ps_row['pDupeRsrc'])?>
		<?php echo printPageSpeedGradeBreakdown('Serve scaled images', 'payload.html#ScaleImages', $ps_row['pScaleImgs'])?>
		</tr>
	<tr><td colspan="6" style="padding-top: 1em"><b>Optimize browser rendering</b></td></tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Use efficient CSS selectors', 'rendering.html#UseEfficientCSSSelectors', $ps_row['pCssSelect'])?>
		<?php echo printPageSpeedGradeBreakdown('Avoid CSS expressions', 'rendering.html#AvoidCSSExpressions', $ps_row['pCssExpr'])?>
		</tr>
	<tr><td colspan="6" style="padding-top: 1em"><b>Misc</b></td></tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Minify HTML', '', $ps_row['pMinifyHTML'])?>
		<?php echo printPageSpeedGradeBreakdown('Minimize Request Size', '', $ps_row['pMinimizeRequestSize'])?>
		</tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Optimize The Order Of Styles And Scripts', '', $ps_row['pOptimizeTheOrderOfStylesAndScripts'])?>
		<?php echo printPageSpeedGradeBreakdown('Put Css In The Document Head', '', $ps_row['pPutCssInTheDocumentHead'])?>
		</tr>
		<tr>
		<?php echo printPageSpeedGradeBreakdown('Specify Charset Early', '', $ps_row['pSpecifyCharsetEarly'])?>
		</tr>
	</table>	
<?php 
	}
}

if ($row) {
?>
	<h2>YSlow measurements history (<a href="data.php?url=<?php echo urlencode($_GET['url'])?>">csv</a>)</h3>
	<div id="measurementstable"></div>
	<?php 
}

if ($ps_row) {
?>
	<h2>PageSpeed measurements history (<a href="data_pagespeed.php?url=<?php echo urlencode($_GET['url'])?>">csv</a>)</h3>
	<div id="ps_measurementstable"></div>
<?php 
}

if (count($har) > 0) {
?>
	<h2>HAR data collected</h2>

	<p>You can see latest HAR data in the viewer here: <a href="<?php echo htmlentities($HARViewerBase)?>?inputUrl=<?php echo $showslow_base?>details/har.php%3Fid%3D<?php echo urlencode($har[0]['id']); ?>%26callback%3DonInputData" target="_blank">HAR for <?php echo htmlentities($_GET['url'])?></a>.</p>

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
?>
</body></html>
