<?
require_once('../global.php');

if (!array_key_exists('url', $_GET) || filter_var($_GET['url'], FILTER_VALIDATE_URL) === false) {
	?><html>
<head>
<title>Error - no URL specified</title>
</head>
<body>
<h1>Error - no URL specified</h1>
<p><a href="../">Go back</a> and pick the URL</p>
</body></html>
<?
	return;
}

?><html>
<head>
<title>Show Slow: Details for <?=htmlentities($_GET['url'])?></title>
<style type="text/css">
body {
	margin:0;
	padding:0;
}
</style>
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.7.0/build/fonts/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.7.0/build/tabview/assets/skins/sam/tabview.css" />
<script type="text/javascript" src="<?=$TimePlotBase?>timeplot-api.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/yuiloader/yuiloader-min.js"></script>
<script src="details.js?v=2" type="text/javascript"></script>
<? if ($showFeedbackButton) {?>
<script type="text/javascript">
  var uservoiceJsHost = ("https:" == document.location.protocol) ? "https://uservoice.com" : "http://cdn.uservoice.com";
  document.write(unescape("%3Cscript src='" + uservoiceJsHost + "/javascripts/widgets/tab.js' type='text/javascript'%3E%3C/script%3E"))
</script>
<script type="text/javascript">
UserVoice.Tab.show({ 
  key: 'showslow',
  host: 'showslow.uservoice.com', 
  forum: 'general', 
  alignment: 'right',
  background_color:'#f00', 
  text_color: 'white',
  hover_color: '#06C',
  lang: 'en'
})
</script>
<? } ?>
<style>
.yslow1 {
	color: #55009D;
}

.yslow2 {
	color: #2175D9;
}
</style>
</head>
<body class="yui-skin-sam" onload="onLoad('<?=urlencode($_GET['url'])?>', ydataversion, psdataversion, eventversion);" onresize="onResize();">
<a href="http://code.google.com/p/showslow/"><img src="../showslow_icon.png" style="float: right; margin-left: 1em; border: 0"/></a>
<div style="float: right">powered by <a href="http://code.google.com/p/showslow/">showslow</a></div>
<h1><a title="Click here to go to home page" href="../">Show Slow</a>: Details for <a href="<?=htmlentities($_GET['url'])?>"><?=htmlentities(substr($_GET['url'], 0, 30))?><? if (strlen($_GET['url']) > 30) { ?>...<? } ?></a></h1>
<?
// last event timestamp
$query = sprintf("SELECT last_event_update WHERE urls.url = '%s'", mysql_real_escape_string($_GET['url']));
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$row = mysql_fetch_assoc($result);
$eventupdate = $row['last_event_update'];
mysql_free_result($result);

// latest YSlow result
$query = sprintf("SELECT y.timestamp, urls.last_event_update, y.w, y.o, y.i,
		y.ynumreq,	y.ycdn,			y.yexpires,	y.ycompress,	y.ycsstop,
		y.yjsbottom,	y.yexpressions,		y.yexternal,	y.ydns,		y.yminify,
		y.yredirects,	y.ydupes,		y.yetags,	y.yxhr,		y.yxhrmethod,
		y.ymindom,	y.yno404,		y.ymincookie,	y.ycookiefree,	y.ynofilter,
		y.yimgnoscale,	y.yfavicon
		FROM yslow2 y, urls
		WHERE urls.url = '%s' AND y.url_id = urls.id
		ORDER BY y.timestamp DESC
		LIMIT 1",
	mysql_real_escape_string($_GET['url'])
);
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$row = mysql_fetch_assoc($result);
mysql_free_result($result);

// Latest PageSpeed result
$query = sprintf("SELECT p.timestamp, p.w, p.o, p.l, p.r, p.t, p.v,
			pMinifyCSS, pMinifyJS, pOptImgs, pImgDims, pCombineJS, pCombineCSS,
			pCssInHead, pBrowserCache, pProxyCache, pNoCookie, pCookieSize,
			pParallelDl, pCssSelect, pCssJsOrder, pDeferJS, pGzip,
			pMinRedirect, pCssExpr, pUnusedCSS, pMinDns, pDupeRsrc
		FROM pagespeed p, urls
		WHERE urls.url = '%s' AND p.url_id = urls.id
		ORDER BY p.timestamp DESC
		LIMIT 1",
	mysql_real_escape_string($_GET['url'])
);
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$ps_row = mysql_fetch_assoc($result);
mysql_free_result($result);

if (!$row && !$ps_row) {
	?>No data is available yet<?
} else {
?>
<table cellpadding="15" cellspacing="5"><tr>
<?
// YSlow grade indicator
if ($row) {
?>
	<td valign="top" align="center" style="background: #ddd; border: 1px solid black">
	<h2>Current <a href="http://developer.yahoo.com/yslow/">YSlow</a> grade: <?=yslowPrettyScore($row['o'])?> (<i><?=htmlentities($row['o'])?></i>)</h2>

	<img src="http://chart.apis.google.com/chart?chs=225x125&cht=gom&chd=t:<?=urlencode($row['o'])?>&chl=<?=urlencode(yslowPrettyScore($row['o']).' ('.$row['o'].')')?>" alt="<?=yslowPrettyScore($row['o'])?> (<?=htmlentities($row['o'])?>)" title="Current YSlow grade: <?=yslowPrettyScore($row['o'])?> (<?=htmlentities($row['o'])?>)" style="padding: 0 0 20px 0; border: 1px solid black; background: white"/>
	</td>
<?
}

// YSlow grade indicator
if ($ps_row) {
?>
	<td valign="top" align="center" style="background: #ddd; border: 1px solid black">
	<h2>Current <a href="http://code.google.com/speed/page-speed/">PageSpeed</a> grade: <?=yslowPrettyScore($ps_row['o'])?> (<i><?=htmlentities($ps_row['o'])?></i>)</h2>

	<img src="http://chart.apis.google.com/chart?chs=225x125&cht=gom&chd=t:<?=urlencode($ps_row['o'])?>&chl=<?=urlencode(yslowPrettyScore($ps_row['o']).' ('.$ps_row['o'].')')?>" alt="<?=yslowPrettyScore($ps_row['o'])?> (<?=htmlentities($ps_row['o'])?>)" title="Current PageSpeed grade: <?=yslowPrettyScore($ps_row['o'])?> (<?=htmlentities($ps_row['o'])?>)" style="padding: 0 0 20px 0; border: 1px solid black; background: white"/>
	</td>
<?
}
?>
</tr></table>

<?
// Graph
?>
<script>
ydataversion = '<?=urlencode($row['timestamp'])?>';
psdataversion = '<?=urlencode($ps_row['timestamp'])?>';
eventversion = '<?=urlencode($eventupdate)?>';
</script>

<h2 style="clear: both">Measurements over time</h2>
<div id="my-timeplot" style="height: 250px;"></div>
<div style="fint-size: 0.2em">
<span style="color: #D0A825">Page Size</span> (in bytes);
<span style="color: #75CF74">Total Requests</span>;
<span class="yslow2">YSlow Grade</span> (0-100);
<span style="color: #6F4428">PageSpeed Grade</span> (0-100);
<span style="color: #EE4F00">Page Load time (Page Speed)</span> (in ms)
</div>

<?
// YSlow breakdown
if ($row) {

function printYSlowGradeBreakdown($name, $anchor, $value) {
?>
		<td><a href="http://developer.yahoo.com/performance/rules.html#<?=$anchor?>"><?=$name?></a></td>
		<? if ($value >= 0) {?>
		<td><?=yslowPrettyScore($value)?> (<i><?=htmlentities($value)?></i>)</td>
		<td><div style="background-color: silver; width: 103px" title="Current YSlow grade: <?=yslowPrettyScore($value)?> (<?=$value?>)"><div style="width: <?=$value+3?>px; height: 0.7em; background-color: <?=scoreColor($value)?>"/></div></td>
		<? } else { ?>
		<td><i>N/A</i></td>
		<td></td>
		<? } ?>
		<td>&nbsp;&nbsp;</td>
<?
}

	if ($row['i'] <> 'yslow1') {
?>
	<h2 style="clear: both">YSlow breakdown</h2>
	<table>
		<tr>
		<?=printYSlowGradeBreakdown('Make fewer HTTP requests', 'num_http', $row['ynumreq'])?>
		<?=printYSlowGradeBreakdown('Use a Content Delivery Network (CDN)', 'cdn', $row['ycdn'])?>
		</tr>
		<tr>
		<?=printYSlowGradeBreakdown('Add Expires headers', 'expires', $row['yexpires'])?>
		<?=printYSlowGradeBreakdown('Compress components with gzip', 'gzip', $row['ycompress'])?>
		</tr>
		<tr>
		<?=printYSlowGradeBreakdown('Put CSS at top', 'css_top', $row['ycsstop'])?>
		<?=printYSlowGradeBreakdown('Put JavaScript at bottom', 'js_bottom', $row['yjsbottom'])?>
		</tr>
		<tr>
		<?=printYSlowGradeBreakdown('Avoid CSS expressions', 'css_expressions', $row['yexpressions'])?>
		<?=printYSlowGradeBreakdown('Make JavaScript and CSS external', 'external', $row['yexternal'])?>
		</tr>
		<tr>
		<?=printYSlowGradeBreakdown('Reduce DNS lookups', 'dns_lookups', $row['ydns'])?>
		<?=printYSlowGradeBreakdown('Minify JavaScript and CSS', 'minify', $row['yminify'])?>
		</tr>
		<tr>
		<?=printYSlowGradeBreakdown('Avoid URL redirects', 'redirects', $row['yredirects'])?>
		<?=printYSlowGradeBreakdown('Remove duplicate JavaScript and CSS', 'js_dupes', $row['ydupes'])?>
		</tr>
		<tr>
		<?=printYSlowGradeBreakdown('Configure entity tags (ETags)', 'etags', $row['yetags'])?>
		<?=printYSlowGradeBreakdown('Make AJAX cacheable', 'cacheajax', $row['yxhr'])?>
		</tr>
		<tr>
		<?=printYSlowGradeBreakdown('Use GET for AJAX requests', 'ajax_get', $row['yxhrmethod'])?>
		<?=printYSlowGradeBreakdown('Reduce the number of DOM elements', 'min_dom', $row['ymindom'])?>
		</tr>
		<tr>
		<?=printYSlowGradeBreakdown('Avoid HTTP 404 (Not Found) error', 'no404', $row['yno404'])?>
		<?=printYSlowGradeBreakdown('Reduce cookie size', 'cookie_size', $row['ymincookie'])?>
		</tr>
		<tr>
		<?=printYSlowGradeBreakdown('Use cookie-free domains', 'cookie_free', $row['ycookiefree'])?>
		<?=printYSlowGradeBreakdown('Avoid AlphaImageLoader filter', 'no_filters', $row['ynofilter'])?>
		</tr>
		<tr>
		<?=printYSlowGradeBreakdown('Do not scale images in HTML', 'no_scale', $row['yimgnoscale'])?>
		<?=printYSlowGradeBreakdown('Make favicon small and cacheable', 'favicon', $row['yfavicon'])?>
		</tr>
	</table>	
<?
	}
}

// PageSpeed breakdown
if ($ps_row) {
function printPageSpeedGradeBreakdown($name, $doc, $value) {
?>
		<td><a href="http://code.google.com/speed/page-speed/docs/<?=$doc?>"><?=$name?></a></td>
		<? if ($value >= 0) {?>
		<td><?=yslowPrettyScore($value)?> (<i><?=htmlentities($value)?></i>)</td>
		<td><div style="background-color: silver; width: 103px" title="Current PageSpeed grade: <?=yslowPrettyScore($value)?> (<?=$value?>)"><div style="width: <?=$value+3?>px; height: 0.7em; background-color: <?=scoreColor($value)?>"/></div></td>
		<? } else { ?>
		<td><i>N/A</i></td>
		<td></td>
		<? } ?>
		<td>&nbsp;&nbsp;</td>
<?
}
?>
	<h2 style="clear: both">PageSpeed breakdown</h2>
	<table>
	<tr><td colspan="6"><b>Optimize caching</b></td></tr>
		<tr>
		<?=printPageSpeedGradeBreakdown('Leverage browser caching', 'caching.html#LeverageBrowserCaching', $ps_row['pBrowserCache'])?>
		<?=printPageSpeedGradeBreakdown('Leverage proxy caching', 'caching.html#LeverageProxyCaching', $ps_row['pProxyCache'])?>
		</tr>
	<tr><td colspan="6" style="padding-top: 1em"><b>Minimize round-trip times</b></td></tr>
		<tr>
		<?=printPageSpeedGradeBreakdown('Minimize DNS lookups', 'rtt.html#MinimizeDNSLookups', $ps_row['pMinDns'])?>
		<?=printPageSpeedGradeBreakdown('Minimize redirects', 'rtt.html#AvoidRedirects', $ps_row['pMinRedirect'])?>
		</tr>
		<tr>
		<?=printPageSpeedGradeBreakdown('Combine external JavaScript', 'rtt.html#CombineExternalJS', $ps_row['pCombineJS'])?>
		<?=printPageSpeedGradeBreakdown('Combine external CSS', 'rtt.html#CombineExternalCSS', $ps_row['pCombineCSS'])?>
		</tr>
		<tr>
		<?=printPageSpeedGradeBreakdown('Optimize the order of styles and scripts', 'rtt.html#PutStylesBeforeScripts', $ps_row['pCssJsOrder'])?>
		<?=printPageSpeedGradeBreakdown('Parallelize downloads across hostnames', 'rtt.html#ParallelizeDownloads', $ps_row['pParallelDl'])?>
		</tr>
	<tr><td colspan="6" style="padding-top: 1em"><b>Minimize request size</b></td></tr>
		<tr>
		<?=printPageSpeedGradeBreakdown('Minimize cookie size', 'request.html#MinimizeCookieSize', $ps_row['pCookieSize'])?>
		<?=printPageSpeedGradeBreakdown('Serve static content from a cookieless domain', 'request.html#ServeFromCookielessDomain', $ps_row['pNoCookie'])?>
		</tr>
	<tr><td colspan="6" style="padding-top: 1em"><b>Minimize payload size</b></td></tr>
		<tr>
		<?=printPageSpeedGradeBreakdown('Enable gzip compression', 'payload.html#GzipCompression', $ps_row['pGzip'])?>
		<?=printPageSpeedGradeBreakdown('Remove unused CSS', 'payload.html#RemoveUnusedCSS', $ps_row['pUnusedCSS'])?>
		</tr>
		<tr>
		<?=printPageSpeedGradeBreakdown('Minify JavaScript', 'payload.html#MinifyJS', $ps_row['pMinifyJS'])?>
		<?=printPageSpeedGradeBreakdown('Minify CSS', 'payload.html#MinifyCSS', $ps_row['pMinifyCSS'])?>
		</tr>
		<tr>
		<?=printPageSpeedGradeBreakdown('Defer loading of JavaScript', 'payload.html#DeferLoadingJS', $ps_row['pDeferJS'])?>
		<?=printPageSpeedGradeBreakdown('Optimize images', 'payload.html#CompressImages', $ps_row['pOptImgs'])?>
		</tr>
		<tr>
		<?=printPageSpeedGradeBreakdown('Serve resources from a consistent URL', 'payload.html#duplicate_resources', $ps_row['pDupeRsrc'])?>
		</tr>
	<tr><td colspan="6" style="padding-top: 1em"><b>Optimize browser rendering</b></td></tr>
		<tr>
		<?=printPageSpeedGradeBreakdown('Use efficient CSS selectors', 'rendering.html#UseEfficientCSSSelectors', $ps_row['pCssSelect'])?>
		<?=printPageSpeedGradeBreakdown('Avoid CSS expressions', 'rendering.html#AvoidCSSExpressions', $ps_row['pCssExpr'])?>
		</tr>
		<tr>
		<?=printPageSpeedGradeBreakdown('Put CSS in the document head', 'rendering.html#PutCSSInHead', $ps_row['pCssInHead'])?>
		<?=printPageSpeedGradeBreakdown('Specify image dimensions', 'rendering.html#SpecifyImageDimensions', $ps_row['pImgDims'])?>
		</tr>
	</table>	
<?
	}
}

if ($row) {
?>
	<h2>YSlow measurements history (<a href="data.php?url=<?=urlencode($_GET['url'])?>">csv</a>)</h3>
	<div id="measurementstable"></div>
<?
}

if ($ps_row) {
?>
	<h2>PageSpeed measurements history (<a href="data_pagespeed.php?url=<?=urlencode($_GET['url'])?>">csv</a>)</h3>
	<div id="ps_measurementstable"></div>
<?
}
?>

<? if ($googleAnalyticsProfile) {?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker('<?=$googleAnalyticsProfile?>');
pageTracker._trackPageview();
} catch(err) {}</script>
<?}?>
</body></html>
