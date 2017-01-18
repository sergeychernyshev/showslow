<?php 
require_once(dirname(dirname(__FILE__)).'/global.php');

if (!array_key_exists('urlid', $_GET) || filter_var($_GET['urlid'], FILTER_VALIDATE_INT) === false) {
	header('HTTP/1.0 400 Bad Request');

	?><html>
<head>
<title>Bad Request: no valid urlid specified</title>
</head>
<body>
<h1>Bad Request: no valid urlid specified</h1>
<p>You must pass valid URL ID as 'urlid' parameter</p>
</body></html>
<?php 
	exit;
}

$all = true;

if (array_key_exists('profile', $_GET) && $_GET['profile'] != '' ) {
	$all = false;
}

$query = sprintf("SELECT url, id FROM urls WHERE urls.id = %d", mysqli_real_escape_string($conn, $_GET['urlid']));
$result = mysqli_query($conn, $query);

if (!$result) {
	error_log(mysqli_error($conn));
}

$row = mysqli_fetch_assoc($result);
$url = $row['url'];
$urlid = $row['id'];
mysqli_free_result($result);

if ($all) {
	$query = sprintf("SELECT UNIX_TIMESTAMP(timestamp) as t, y.w, y.o, y.r, y.i, y.lt, 
			y.ynumreq,	y.ycdn,		y.yexpires,	y.ycompress,	y.ycsstop,
			y.yjsbottom,	y.yexpressions,	y.yexternal,	y.ydns,		y.yminify,
			y.yredirects,	y.ydupes,	y.yetags,	y.yxhr,		y.yxhrmethod,
			y.ymindom,	y.yno404,	y.ymincookie,	y.ycookiefree,	y.ynofilter,
			y.yimgnoscale,	y.yfavicon
		FROM yslow2 y WHERE y.url_id = %d AND timestamp > DATE_SUB(now(), INTERVAL 3 MONTH)
	ORDER BY timestamp DESC",
	mysqli_real_escape_string($conn, $urlid)
	);
} else {
	$query = sprintf("SELECT UNIX_TIMESTAMP(timestamp) as t, y.w, y.o, y.r, y.i, y.lt,
			y.ynumreq,	y.ycdn,		y.yexpires,	y.ycompress,	y.ycsstop,
			y.yjsbottom,	y.yexpressions,	y.yexternal,	y.ydns,		y.yminify,
			y.yredirects,	y.ydupes,	y.yetags,	y.yxhr,		y.yxhrmethod,
			y.ymindom,	y.yno404,	y.ymincookie,	y.ycookiefree,	y.ynofilter,
			y.yimgnoscale,	y.yfavicon
		FROM yslow2 y WHERE y.url_id = %d AND y.i = '%s' AND timestamp > DATE_SUB(now(),INTERVAL 3 MONTH)
	ORDER BY timestamp DESC",
	mysqli_real_escape_string($conn, $urlid), mysqli_real_escape_string($conn, $_GET['profile'])
	);
}

$result = mysqli_query($conn, $query);

if (!$result) {
        error_log(mysqli_error($conn));
}

$data = array();

header('Content-type: text/csv');
if (array_key_exists('ver', $_GET)) {
	header('Expires: '.date('r', time() + 315569260));
	header('Cache-control: max-age=315569260');
}

$rows = array();
while ($row = mysqli_fetch_assoc($result)) {
	$rows[] = $row;
}

mysqli_free_result($result);

if (array_key_exists('smooth', $_REQUEST)) {
	require_once(dirname(__FILE__).'/smooth.php');
	smooth($rows, array('w', 'o', 'r', 'lt'));
}

if (!array_key_exists('subset', $_REQUEST) || !$_REQUEST['subset'] == 'graph')
{
	header('Content-disposition: attachment;filename=yslow.csv');

	echo '# Measurement time, ';
	echo 'Page size (in bytes), ';
	echo 'YSlow grade, ';
	echo 'Total number requests, ';
	echo 'Page Load Time, ';
	echo "YSlow profile used, ";
	echo 'Make fewer HTTP requests, ';
	echo 'Use a Content Delivery Network (CDN), ';
	echo 'Add Expires headers, ';
	echo 'Compress components with gzip, ';
	echo 'Put CSS at top, ';
	echo 'Put JavaScript at bottom, ';
	echo 'Avoid CSS expressions, ';
	echo 'Make JavaScript and CSS external, ';
	echo 'Reduce DNS lookups, ';
	echo 'Minify JavaScript and CSS, ';
	echo 'Avoid URL redirects, ';
	echo 'Remove duplicate JavaScript and CSS, ';
	echo 'Configure entity tags (ETags), ';
	echo 'Make AJAX cacheable, ';
	echo 'Use GET for AJAX requests, ';
	echo 'Reduce the number of DOM elements, ';
	echo 'Avoid HTTP 404 (Not Found) error, ';
	echo 'Reduce cookie size, ';
	echo 'Use cookie-free domains, ';
	echo 'Avoid AlphaImageLoader filter, ';
	echo 'Do not scale images in HTML, ';
	echo 'Make favicon small and cacheable';
	echo "\n";
}

foreach ($rows as $row) {
        echo date('c', $row['t']).','.
		($row['i'] == 'yslow1' ? $row['w'] * 1024 : $row['w']).','.
		$row['o'].','.
		$row['r'].','.
		$row['lt'].','.
		$row['i'];

	if (array_key_exists('subset', $_REQUEST) && $_REQUEST['subset'] == 'graph')
	{
		echo "\n";
	} else {
		echo ','.$row['ynumreq'].','.
			$row['ycdn'].','.
			$row['yexpires'].','.
			$row['ycompress'].','.
			$row['ycsstop'].','.
			$row['yjsbottom'].','.
			$row['yexpressions'].','.
			$row['yexternal'].','.
			$row['ydns'].','.
			$row['yminify'].','.
			$row['yredirects'].','.
			$row['ydupes'].','.
			$row['yetags'].','.
			$row['yxhr'].','.
			$row['yxhrmethod'].','.
			$row['ymindom'].','.
			$row['yno404'].','.
			$row['ymincookie'].','.
			$row['ycookiefree'].','.
			$row['ynofilter'].','.
			$row['yimgnoscale'].','.
			$row['yfavicon'].
			"\n";
	}
}

