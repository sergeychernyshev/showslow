<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/global.php');
require_once(dirname(dirname(__FILE__)).'/beacon_functions.php');

function updateUrlAggregates($url_id, $measurement_id)
{
	# updating latest values for the URL
	$query = sprintf("UPDATE urls set pagespeed_last_id = %d, last_update = now() WHERE id = %d",
		mysql_real_escape_string($measurement_id),
		mysql_real_escape_string($url_id)
	);
	$result = mysql_query($query);

	if (!$result) {
		beaconError(mysql_error());
	}
}

if (array_key_exists('v', $_GET)
	&& array_key_exists('w', $_GET) && filter_var($_GET['w'], FILTER_VALIDATE_INT) !== false
	&& array_key_exists('o', $_GET) && filter_var($_GET['o'], FILTER_VALIDATE_FLOAT) !== false
	&& array_key_exists('l', $_GET) && filter_var($_GET['l'], FILTER_VALIDATE_INT) !== false
	&& array_key_exists('r', $_GET) && filter_var($_GET['r'], FILTER_VALIDATE_INT) !== false
	&& array_key_exists('t', $_GET) && filter_var($_GET['t'], FILTER_VALIDATE_INT) !== false
	&& array_key_exists('u', $_GET) && filter_var($_GET['u'], FILTER_VALIDATE_URL) !== false
	)
{
	$url_id = getUrlId($_GET['u']);

	if ($_GET['v'] >= 1.6) {
		$scaleimgs = $_GET['pSpecifyImageDimensions'];
		$imgdims = $_GET['pSpecifyImageDimensions'];
	} else {
		$scaleimgs = $_GET['pScaleImgs'];
		$imgdims = $_GET['pImgDims'];
	}

	# adding new entry
	$query = sprintf("INSERT INTO pagespeed (
		`ip` , `user_agent` , `url_id` ,
		`w` , `o` , `l`, `r` , `t`, `v` ,
		pMinifyJS,
		pOptImgs,
		pImgDims,
		pCombineJS,
		pCombineCSS,
		pBrowserCache,
		pProxyCache,
		pNoCookie,
		pParallelDl,
		pCssSelect,
		pDeferJS,
		pGzip,
		pMinRedirect,
		pCssExpr,
		pUnusedCSS,
		pMinDns,
		pDupeRsrc,
		pMinifyCSS,
		pScaleImgs,
		pMinifyHTML,
		pMinimizeRequestSize,
		pOptimizeTheOrderOfStylesAndScripts,
		pPutCssInTheDocumentHead,
		pSpecifyCharsetEarly
	)
	VALUES (inet_aton('%s'), '%s', '%d',
		'%d', '%f', '%d', '%d', '%d', '%s',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f',
		'%3.2f'
	)",
		mysql_real_escape_string($_SERVER['REMOTE_ADDR']),
		mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']),
		mysql_real_escape_string($url_id),
		mysql_real_escape_string($_GET['w']),
		mysql_real_escape_string($_GET['o']),
		mysql_real_escape_string($_GET['l']),
		mysql_real_escape_string($_GET['r']),
		mysql_real_escape_string($_GET['t']),
		mysql_real_escape_string($_GET['v']),
		mysql_real_escape_string($_GET['pMinifyJS'] > 0 ? $_GET['pMinifyJS'] : 0),
		mysql_real_escape_string($_GET['pOptImgs'] > 0 ? $_GET['pOptImgs'] : 0),
		mysql_real_escape_string($imgdims > 0 ? $imgdims : 0),
		mysql_real_escape_string($_GET['pCombineJS'] > 0 ? $_GET['pCombineJS'] : 0),
		mysql_real_escape_string($_GET['pCombineCSS'] > 0 ? $_GET['pCombineCSS'] : 0),
		mysql_real_escape_string($_GET['pBrowserCache'] > 0 ? $_GET['pBrowserCache'] : 0),
		mysql_real_escape_string($_GET['pProxyCache'] > 0 ? $_GET['pProxyCache'] : 0),
		mysql_real_escape_string($_GET['pNoCookie'] > 0 ? $_GET['pNoCookie'] : 0),
		mysql_real_escape_string($_GET['pParallelDl'] > 0 ? $_GET['pParallelDl'] : 0),
		mysql_real_escape_string($_GET['pCssSelect'] > 0 ? $_GET['pCssSelect'] : 0),
		mysql_real_escape_string($_GET['pDeferJS'] > 0 ? $_GET['pDeferJS'] : 0),
		mysql_real_escape_string($_GET['pGzip'] > 0 ? $_GET['pGzip'] : 0),
		mysql_real_escape_string($_GET['pMinRedirect'] > 0 ? $_GET['pMinRedirect'] : 0),
		mysql_real_escape_string($_GET['pCssExpr'] > 0 ? $_GET['pCssExpr'] : 0),
		mysql_real_escape_string($_GET['pUnusedCSS'] > 0 ? $_GET['pUnusedCSS'] : 0),
		mysql_real_escape_string($_GET['pMinDns'] > 0 ? $_GET['pMinDns'] : 0),
		mysql_real_escape_string($_GET['pDupeRsrc'] > 0 ? $_GET['pDupeRsrc'] : 0),
		mysql_real_escape_string($_GET['pMinifyCSS'] > 0 ? $_GET['pMinifyCSS'] : 0),
		mysql_real_escape_string($scaleimgs > 0 ? $scaleimgs : 0),
		mysql_real_escape_string($_GET['pMinifyHTML'] > 0 ? $_GET['pMinifyHTML'] : 0),
		mysql_real_escape_string($_GET['pMinimizeRequestSize'] > 0 ? $_GET['pMinimizeRequestSize'] : 0),
		mysql_real_escape_string($_GET['pOptimizeTheOrderOfStylesAndScripts'] > 0 ? $_GET['pOptimizeTheOrderOfStylesAndScripts'] : 0),
		mysql_real_escape_string($_GET['pPutCssInTheDocumentHead'] > 0 ? $_GET['pPutCssInTheDocumentHead'] : 0),
		mysql_real_escape_string($_GET['pSpecifyCharsetEarly'] > 0 ? $_GET['pSpecifyCharsetEarly'] : 0)
	);

	if (!mysql_query($query))
	{
		beaconError(mysql_error());
	}

	updateUrlAggregates($url_id, mysql_insert_id());
} else {
	header('HTTP/1.0 400 Bad Request');

	?><html>
<head>
<title>Bad Request: Page Speed beacon</title>
</head>
<body>
<h1>Bad Request: Page Speed beacon</h1>
<p>This is <a href="http://code.google.com/speed/page-speed/">Page Speed</a> beacon entry point.</p>

<h1>Configure your Page Speed</h1>
<p><b style="color: red">WARNING! Only use this beacon If you're OK with all your Page Speed data to be recorded by this instance of ShowSlow and displayed at <a href="<?php echo $showslow_base?>"><?php echo $showslow_base?></a><br/>You can also <a href="http://www.showslow.org/Installation_and_configuration">install ShowSlow on your own server</a> to limit the risk.</b></p>

Set these two Firefox parameters on <b>about:config</b> page:</p>

<ul>
<li>extensions.PageSpeed.beacon.minimal.url = <b style="color: blue"><?php echo $showslow_base?>beacon/pagespeed/</b></li>
<li>extensions.PageSpeed.beacon.minimal.enabled = <b style="color: blue">true</b></li>
</ul>

</body></html>
<?php
	exit;
}

header('HTTP/1.0 204 Data accepted');
