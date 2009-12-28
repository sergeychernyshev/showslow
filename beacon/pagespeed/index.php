<?php 
require_once('../../global.php');

function updateUrlAggregates($url_id, $w, $o, $l, $r, $t)
{
	# updating latest values for the URL
	$query = sprintf("UPDATE urls SET ps_w = '%d', ps_o = '%f', ps_l = '%d', ps_r = '%d', ps_t = '%d', last_update = now() WHERE id = '%d'",
		mysql_real_escape_string($w),
		mysql_real_escape_string($o),
		mysql_real_escape_string($l),
		mysql_real_escape_string($r),
		mysql_real_escape_string($t),
		mysql_real_escape_string($url_id)
	);
	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}
}

function getUrlId($url)
{
	# get URL id
	$query = sprintf("SELECT id FROM urls WHERE url = '%s'", mysql_real_escape_string($url));
	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
		exit;
	}

	if (mysql_num_rows($result) == 1) {
		$row = mysql_fetch_assoc($result);
		return $row['id'];
	} else if (mysql_num_rows($result) == 0) {
		$query = sprintf("INSERT INTO urls (url) VALUES ('%s')", mysql_real_escape_string($url));
		$result = mysql_query($query);

		if (!$result) {
			error_log(mysql_error());
			exit;
		}

		return mysql_insert_id();
	} else {
		error_log('more then one entry found for the URL');
		exit;
	}

}

error_log($_GET['w'].' => '.filter_var($_GET['w'], FILTER_VALIDATE_INT));
error_log($_GET['o'].' => '.filter_var($_GET['o'], FILTER_VALIDATE_FLOAT));
error_log($_GET['l'].' => '.filter_var($_GET['l'], FILTER_VALIDATE_INT));
error_log($_GET['r'].' => '.filter_var($_GET['r'], FILTER_VALIDATE_INT));
error_log($_GET['t'].' => '.filter_var($_GET['t'], FILTER_VALIDATE_INT));
error_log($_GET['u'].' => '.filter_var($_GET['u'], FILTER_VALIDATE_URL));

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

	# adding new entry
	$query = sprintf("INSERT INTO pagespeed (
		`ip` , `user_agent` , `url_id` ,
		`w` , `o` , `l`, `r` , `t`, `v` ,
		pMinifyCSS, pMinifyJS, pOptImgs, pImgDims, pCombineJS, pCombineCSS,
		pCssInHead, pBrowserCache, pProxyCache, pNoCookie, pCookieSize,
		pParallelDl, pCssSelect, pCssJsOrder, pDeferJS, pGzip,
		pMinRedirect, pCssExpr, pUnusedCSS, pMinDns, pDupeRsrc	
	)
	VALUES (inet_aton('%s'), '%s', '%d',
		'%d', '%f', '%d', '%d', '%d', '%s',
		'%3.2f', '%3.2f', '%3.2f', '%3.2f', '%3.2f', '%3.2f',
		'%3.2f', '%3.2f', '%3.2f', '%3.2f', '%3.2f',
		'%3.2f', '%3.2f', '%3.2f', '%3.2f', '%3.2f',
		'%3.2f', '%3.2f', '%3.2f', '%3.2f', '%3.2f'
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
		mysql_real_escape_string($_GET['pMinifyCSS'] > 0 ? $_GET['pMinifyCSS'] : 0),
		mysql_real_escape_string($_GET['pMinifyJS'] > 0 ? $_GET['pMinifyJS'] : 0),
		mysql_real_escape_string($_GET['pOptImgs'] > 0 ? $_GET['pOptImgs'] : 0),
		mysql_real_escape_string($_GET['pImgDims'] > 0 ? $_GET['pImgDims'] : 0),
		mysql_real_escape_string($_GET['pCombineJS'] > 0 ? $_GET['pCombineJS'] : 0),
		mysql_real_escape_string($_GET['pCombineCSS'] > 0 ? $_GET['pCombineCSS'] : 0),
		mysql_real_escape_string($_GET['pCssInHead'] > 0 ? $_GET['pCssInHead'] : 0),
		mysql_real_escape_string($_GET['pBrowserCache'] > 0 ? $_GET['pBrowserCache'] : 0),
		mysql_real_escape_string($_GET['pProxyCache'] > 0 ? $_GET['pProxyCache'] : 0),
		mysql_real_escape_string($_GET['pNoCookie'] > 0 ? $_GET['pNoCookie'] : 0),
		mysql_real_escape_string($_GET['pCookieSize'] > 0 ? $_GET['pCookieSize'] : 0),
		mysql_real_escape_string($_GET['pParallelDl'] > 0 ? $_GET['pParallelDl'] : 0),
		mysql_real_escape_string($_GET['pCssSelect'] > 0 ? $_GET['pCssSelect'] : 0),
		mysql_real_escape_string($_GET['pCssJsOrder'] > 0 ? $_GET['pCssJsOrder'] : 0),
		mysql_real_escape_string($_GET['pDeferJS'] > 0 ? $_GET['pDeferJS'] : 0),
		mysql_real_escape_string($_GET['pGzip'] > 0 ? $_GET['pGzip'] : 0),
		mysql_real_escape_string($_GET['pMinRedirect'] > 0 ? $_GET['pMinRedirect'] : 0),
		mysql_real_escape_string($_GET['pCssExpr'] > 0 ? $_GET['pCssExpr'] : 0),
		mysql_real_escape_string($_GET['pUnusedCSS'] > 0 ? $_GET['pUnusedCSS'] : 0),
		mysql_real_escape_string($_GET['pMinDns'] > 0 ? $_GET['pMinDns'] : 0),
		mysql_real_escape_string($_GET['pDupeRsrc'] > 0 ? $_GET['pDupeRsrc'] : 0)
	);

#	error_log($query);

	if (!mysql_query($query))
	{
		error_log(mysql_error());
		exit;
	}

	updateUrlAggregates($url_id, $_GET['w'], $_GET['o'], $_GET['l'], $_GET['r'], $_GET['t']);

} else {
	header('HTTP/1.0 400 Bad Request');

	?><html>
<head>
<title>Bad Request: PageSpeed beacon</title>
</head>
<body>
<h1>Bad Request: PageSpeed beacon</h1>
<p>This is <a href="http://code.google.com/speed/page-speed/">PageSpeed</a> beacon entry point.</p>

<h1>Configure your PageSpeed</h1>
<p><b style="color: red">If you're OK with all your PageSpeed measurements to be recorded by this instance of ShowSlow and displayed at <a href="<?php echo $showslow_base?>"><?php echo $showslow_base?></a></b>, just set these two Firefox parameters on <b>about:config</b> page:</p>

<ul>
<li>extensions.PageSpeed.beacon.minimal.url = <b style="color: blue"><?php echo $showslow_base?>beacon/pagespeed/</b></li>
<li>extensions.PageSpeed.beacon.minimal.enabled = <b style="color: blue">true</b></li>
</ul>

</body></html>
<?php 
}
