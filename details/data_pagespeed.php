<?php 
require_once(dirname(dirname(__FILE__)).'/global.php');

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
<?php 
	exit;
}

$query = sprintf("SELECT id FROM urls WHERE urls.url = '%s'", mysql_real_escape_string($_GET['url']));
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$row = mysql_fetch_assoc($result);
$urlid = $row['id'];
mysql_free_result($result);

$query = sprintf("SELECT UNIX_TIMESTAMP(p.timestamp) as time,
		p.w, p.o, p.l, p.r, p.t, p.v,
		pBadReqs, pBrowserCache, pCacheValid, pCharsetEarly, pCombineCSS,
		pCombineJS, pCssImport, pCssInHead, pCssJsOrder, pCssSelect,
		pDeferJS, pDocWrite, pDupeRsrc, pGzip, pImgDims,
		pMinDns, pMinifyCSS, pMinifyHTML, pMinifyJS, pMinRedirect,
		pMinReqSize, pNoCookie, pOptImgs, pParallelDl, pPreferAsync,
		pRemoveQuery, pScaleImgs, pSprite, pUnusedCSS, pVaryAE
	FROM pagespeed p WHERE p.url_id = %d AND p.timestamp > DATE_SUB(now(),INTERVAL 3 MONTH)
ORDER BY p.timestamp DESC",
mysql_real_escape_string($urlid)
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

$rows = array();
while ($row = mysql_fetch_assoc($result)) {
	$rows[] = $row;
}

mysql_free_result($result);

if (array_key_exists('smooth', $_REQUEST)) {
	require_once(dirname(__FILE__).'/smooth.php');
	smooth($rows, array('w', 'o', 'r', 'l'));
}

if (!array_key_exists('subset', $_REQUEST) || !$_REQUEST['subset'] == 'graph')
{
	header('Content-disposition: attachment;filename=pagespeed.csv');
}

echo '# Measurements gathered for '.$_GET['url']."\n";

foreach ($rows as $row) {

        echo date('c', $row['time']).','.
		$row['w'].','.$row['o'].','.$row['l'].','.$row['r'];

		if (array_key_exists('subset', $_REQUEST) && $_REQUEST['subset'] == 'graph')
		{
			echo "\n";
		} else {
			echo ','.$row['t'].','.$row['v'].','.$row['pMinifyCSS'].','.$row['pMinifyJS'].','.$row['pOptImgs'].','.$row['pImgDims'].','.$row['pCombineJS'].','.$row['pCombineCSS'].','.
			$row['pPutCssInTheDocumentHead'].','.$row['pBrowserCache'].','.$row['pProxyCache'].','.$row['pNoCookie'].','.$row['pMinimizeRequestSize'].','.
			$row['pParallelDl'].','.$row['pCssSelect'].','.$row['pMinimizeRequestSize'].','.$row['pDeferJS'].','.$row['pGzip'].','.
			$row['pMinRedirect'].','.$row['pCssExpr'].','.$row['pUnusedCSS'].','.$row['pMinDns'].','.$row['pDupeRsrc'].
			"\n";
		}
}

