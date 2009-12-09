<?
require_once('../global.php');

if (!array_key_exists('url', $_GET) || filter_var($_GET['url'], FILTER_VALIDATE_URL) === false) {
	header('HTTP/1.0 400 Bad Request');

	?><html>
<head>
<title>Bad Request: no valid url specified</title>
</head>
<body>
<h1>Bad Request: no valid url specified</h1>
<p>You must pass valid URL as 'url' parameter</p>
</body></html>
<?
	exit;
}

ob_start("ob_gzhandler");

$query = sprintf("SELECT UNIX_TIMESTAMP(p.timestamp) as time,
		p.w, p.o, p.l, p.r, p.t, p.v,
		p.pMinifyCSS, p.pMinifyJS, p.pOptImgs, p.pImgDims, p.pCombineJS, p.pCombineCSS,
		p.pCssInHead, p.pBrowserCache, p.pProxyCache, p.pNoCookie, p.pCookieSize,
		p.pParallelDl, p.pCssSelect, p.pCssJsOrder, p.pDeferJS, p.pGzip,
		p.pMinRedirect, p.pCssExpr, p.pUnusedCSS, p.pMinDns, p.pDupeRsrc	
	FROM pagespeed p, urls WHERE urls.url = '%s' AND p.url_id = urls.id AND p.timestamp > DATE_SUB(now(),INTERVAL 3 MONTH)
ORDER BY p.timestamp DESC",
mysql_real_escape_string($_GET['url'])
);

$result = mysql_query($query);

if (!$result) {
        error_log(mysql_error());
}

$data = array();

header('Content-type: text/plain');
if (array_key_exists('ver', $_GET)) {
	header('Expires: '.date('r', time() + 315569260));
	header('Cache-control: max-age=315569260');
}
echo '# Measurements gathered for '.$_GET['url']."\n";

while ($row = mysql_fetch_assoc($result)) {
        echo date('c', $row['time']).','.
		$row['w'].','.$row['o'].','.$row['l'].','.$row['r'].','.$row['t'].','.$row['v'].','.
		$row['pMinifyCSS'].','.$row['pMinifyJS'].','.$row['pOptImgs'].','.$row['pImgDims'].','.$row['pCombineJS'].','.$row['pCombineCSS'].','.
		$row['pCssInHead'].','.$row['pBrowserCache'].','.$row['pProxyCache'].','.$row['pNoCookie'].','.$row['pCookieSize'].','.
		$row['pParallelDl'].','.$row['pCssSelect'].','.$row['pCssJsOrder'].','.$row['pDeferJS'].','.$row['pGzip'].','.
		$row['pMinRedirect'].','.$row['pCssExpr'].','.$row['pUnusedCSS'].','.$row['pMinDns'].','.$row['pDupeRsrc'].
		"\n";
}
mysql_free_result($result);

