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

$all = true;

if (array_key_exists('profile', $_GET) && $_GET['profile'] != '' ) {
	$all = false;
}

if ($all) {
	$query = sprintf("SELECT UNIX_TIMESTAMP(timestamp) as t, y.w , y.o , y.r , y.i ,
			y.ynumreq,	y.ycdn,		y.yexpires,	y.ycompress,	y.ycsstop,
			y.yjsbottom,	y.yexpressions,	y.yexternal,	y.ydns,		y.yminify,
			y.yredirects,	y.ydupes,	y.yetags,	y.yxhr,		y.yxhrmethod,
			y.ymindom,	y.yno404,	y.ymincookie,	y.ycookiefree,	y.ynofilter,
			y.yimgnoscale,	y.yfavicon
		FROM yslow2 y, urls WHERE urls.url = '%s' AND y.url_id = urls.id AND timestamp > DATE_SUB(now(),INTERVAL 3 MONTH)
	ORDER BY timestamp DESC",
	mysql_real_escape_string($_GET['url'])
	);
} else {
	$query = sprintf("SELECT UNIX_TIMESTAMP(timestamp) as t, y.w , y.o , y.r , y.i ,
			y.ynumreq,	y.ycdn,		y.yexpires,	y.ycompress,	y.ycsstop,
			y.yjsbottom,	y.yexpressions,	y.yexternal,	y.ydns,		y.yminify,
			y.yredirects,	y.ydupes,	y.yetags,	y.yxhr,		y.yxhrmethod,
			y.ymindom,	y.yno404,	y.ymincookie,	y.ycookiefree,	y.ynofilter,
			y.yimgnoscale,	y.yfavicon
		FROM yslow2 y, urls WHERE urls.url = '%s' AND y.url_id = urls.id AND y.i = '%s' AND timestamp > DATE_SUB(now(),INTERVAL 3 MONTH)
	ORDER BY timestamp DESC",
	mysql_real_escape_string($_GET['url']), mysql_real_escape_string($_GET['profile'])
	);
}

$result = mysql_query($query);

if (!$result) {
        error_log(mysql_error());
}

$data = array();

header('Content-type: text/plain');
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

