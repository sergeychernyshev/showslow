<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/global.php');

function updateUrlAggregates($url_id, $measurement_id)
{
	# updating latest values for the URL
	$query = sprintf("UPDATE urls set pagespeed_last_id = %d, last_update = now(), p_refresh_request = 0 WHERE id = %d",
		mysql_real_escape_string($measurement_id),
		mysql_real_escape_string($url_id)
	);
	$result = mysql_query($query);

	if (!$result) {
		beaconError(mysql_error());
	}
}

if (array_key_exists('v', $_GET) && array_key_exists('u', $_GET)
	&& array_key_exists('w', $_GET) && filter_var($_GET['w'], FILTER_VALIDATE_INT) !== false
	&& array_key_exists('o', $_GET) && filter_var($_GET['o'], FILTER_VALIDATE_FLOAT) !== false
	&& array_key_exists('l', $_GET) && filter_var($_GET['l'], FILTER_VALIDATE_INT) !== false
	&& array_key_exists('r', $_GET) && filter_var($_GET['r'], FILTER_VALIDATE_INT) !== false
	&& array_key_exists('t', $_GET) && filter_var($_GET['t'], FILTER_VALIDATE_INT) !== false
	)
{
	$url_id = getUrlId($_GET['u']);

	$metrics = array(
		'pBadReqs',
		'pBrowserCache',
		'pCacheValid',
		'pCharsetEarly',
		'pCombineCSS',
		'pCombineJS',
		'pCssImport',
		'pCssInHead',
		'pCssJsOrder',
		'pCssSelect',
		'pDeferJS',
		'pDocWrite',
		'pDupeRsrc',
		'pGzip',
		'pImgDims',
		'pMinDns',
		'pMinifyCSS',
		'pMinifyHTML',
		'pMinifyJS',
		'pMinRedirect',
		'pMinReqSize',
		'pNoCookie',
		'pOptImgs',
		'pParallelDl',
		'pPreferAsync',
		'pRemoveQuery',
		'pScaleImgs',
		'pSprite',
		'pUnusedCSS',
		'pVaryAE'
	);

	$metric_renames = array(
		'pSpecifyCharsetEarly'				=> 'pCharsetEarly',
		'pProxyCache'					=> 'pCacheValid',
		'pPutCssInTheDocumentHead'			=> 'pCssInHead',
		'pOptimizeTheOrderOfStylesAndScripts'		=> 'pCssJsOrder',
		'pMinimizeRequestSize'				=> 'pMinReqSize',
		'pParallelizeDownloadsAcrossHostnames'		=> 'pParallelDl',
		'pServeStaticContentFromACookielessDomain'	=> 'pNoCookie',
		'pAvoidBadRequests'				=> 'pBadReqs',
		'pLeverageBrowserCaching'			=> 'pBrowserCache',
		'pRemoveQueryStringsFromStaticResources'	=> 'pRemoveQuery',
		'pServeScaledImages'				=> 'pScaleImgs',
		'pSpecifyACacheValidator'			=> 'pCacheValid',
		'pSpecifyAVaryAcceptEncodingHeader'		=> 'pVaryAE',
		'pSpecifyImageDimensions' 			=> 'pImgDims'
	);

	$data_version = preg_replace('/[^0-9\.]+.*/', '', $_GET['v']);

	foreach ($metrics as $metric) {
		$param = $metric;

		foreach (array_reverse($metric_renames) as $from => $to) {
			if ($metric == $to
				&& !array_key_exists($metric, $_GET)
				&& array_key_exists($from, $_GET))
			{
				$param = $from;
			}
		}

		if (array_key_exists($param, $_GET) && $_GET[$param] > 0) {
			$value = filter_var($_GET[$param], FILTER_VALIDATE_FLOAT);
			if ($value !== false) {
				$beacon[$metric] = $value;
			}
		}
	}

	$names = array();
	$values = array();

	foreach ($beacon as $metric => $value) {
		$names[] = $metric;
		$values[] = "'".mysql_real_escape_string($value)."'";
	}

	# adding new entry
	$query = sprintf("INSERT INTO pagespeed (
		`ip` , `user_agent` , `url_id` ,
		`w` , `o` , `l`, `r` , `t`, `v` ,
		%s
	)
	VALUES (inet_aton('%s'), '%s', '%d',
		'%d', '%f', '%d', '%d', '%d', '%s',
		%s
	)",
		implode(', ', $names),
		mysql_real_escape_string($_SERVER['REMOTE_ADDR']),
		mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']),
		mysql_real_escape_string($url_id),
		mysql_real_escape_string($_GET['w']),
		mysql_real_escape_string($_GET['o']),
		mysql_real_escape_string($_GET['l']),
		mysql_real_escape_string($_GET['r']),
		mysql_real_escape_string($_GET['t']),
		mysql_real_escape_string($_GET['v']),
		implode(', ', $values)
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
