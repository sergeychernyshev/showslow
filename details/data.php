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

ob_start("ob_gzhandler");

$all = true;

if (array_key_exists('profile', $_GET) && $_GET['profile'] != '' ) {
	$all = false;
}

$query = sprintf("SELECT id FROM urls WHERE urls.url = '%s'", mysql_real_escape_string($_GET['url']));
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$row = mysql_fetch_assoc($result);
$urlid = $row['id'];
mysql_free_result($result);

if ($all) {
	$query = sprintf("SELECT UNIX_TIMESTAMP(timestamp) as t, y.w , y.o , y.r , y.i ,
			y.ynumreq,	y.ycdn,		y.yexpires,	y.ycompress,	y.ycsstop,
			y.yjsbottom,	y.yexpressions,	y.yexternal,	y.ydns,		y.yminify,
			y.yredirects,	y.ydupes,	y.yetags,	y.yxhr,		y.yxhrmethod,
			y.ymindom,	y.yno404,	y.ymincookie,	y.ycookiefree,	y.ynofilter,
			y.yimgnoscale,	y.yfavicon
		FROM yslow2 y WHERE y.url_id = %d AND timestamp > DATE_SUB(now(), INTERVAL 3 MONTH)
	ORDER BY timestamp DESC",
	mysql_real_escape_string($urlid)
	);
} else {
	$query = sprintf("SELECT UNIX_TIMESTAMP(timestamp) as t, y.w , y.o , y.r , y.i ,
			y.ynumreq,	y.ycdn,		y.yexpires,	y.ycompress,	y.ycsstop,
			y.yjsbottom,	y.yexpressions,	y.yexternal,	y.ydns,		y.yminify,
			y.yredirects,	y.ydupes,	y.yetags,	y.yxhr,		y.yxhrmethod,
			y.ymindom,	y.yno404,	y.ymincookie,	y.ycookiefree,	y.ynofilter,
			y.yimgnoscale,	y.yfavicon
		FROM yslow2 y WHERE y.url_id = %d AND y.i = '%s' AND timestamp > DATE_SUB(now(),INTERVAL 3 MONTH)
	ORDER BY timestamp DESC",
	mysql_real_escape_string($urlid), mysql_real_escape_string($_GET['profile'])
	);
}

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
echo '# Measurements gathered'.($all ? '' : ' using "'.$_GET['profile'].'" profile').' for '.$_GET['url']."\n";

while ($row = mysql_fetch_assoc($result)) {
        echo date('c', $row['t']).','.
		($row['i'] == 'yslow1' ? $row['w'] * 1024 : $row['w']).','.$row['o'].','.$row['r'].','.
                $row['ynumreq'].','.$row['ycdn'].','.$row['yexpires'].','.$row['ycompress'].','.$row['ycsstop'].','.
                $row['yjsbottom'].','.$row['yjsbottom'].','.$row['yexternal'].','.$row['ydns'].','.$row['yminify'].','.
                $row['yredirects'].','.$row['ydupes'].','.$row['yetags'].','.$row['yxhr'].','.$row['yxhrmethod'].','.
                $row['ymindom'].','.$row['yno404'].','.$row['ymincookie'].','.$row['ycookiefree'].','.$row['ynofilter'].','.
                $row['yimgnoscale'].','.$row['yfavicon'].($all ? ','.$row['i'] : '').
		"\n";
}
mysql_free_result($result);

