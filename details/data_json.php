<?php 
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
<?php 
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

header('Content-type: text/plain');
if (array_key_exists('ver', $_GET)) {
	header('Expires: '.date('r', time() + 315569260));
	header('Cache-control: max-age=315569260');
}
#header('Content-type: application/jsonrequest');

$data = array();

while ($row = mysql_fetch_assoc($result)) {
	$data[] = array(
        	'timestamp' => date('c', $row['t']),
		'w' => $row['i'] == 'yslow1' ? $row['w'] * 1024 : $row['w'],
		'o' => $row['o'],
		'r' => $row['r'],
                'ynumreq' => $row['ynumreq'],
		'ycdn' => $row['ycdn'],
		'yexpires' => $row['yexpires'],
		'ycompress' => $row['ycompress'],
		'ycsstop' => $row['ycsstop'],
		'yjsbottom' => $row['yjsbottom'],
		'yjsbottom' => $row['yjsbottom'],
		'yexternal' => $row['yexternal'],
		'ydns' => $row['ydns'],
		'yminify' => $row['yminify'],
		'yredirects' => $row['yredirects'],
		'ydupes' => $row['ydupes'],
		'yetags' => $row['yetags'],
		'yxhr' => $row['yxhr'],
		'yxhrmethod' => $row['yxhrmethod'],
		'ymindom' => $row['ymindom'],
		'yno404' => $row['yno404'],
		'ymincookie' => $row['ymincookie'],
		'ycookiefree' => $row['ycookiefree'],
		'ynofilter' => $row['ynofilter'],
		'yimgnoscale' => $row['yimgnoscale'],
		'yfavicon' => $row['yfavicon'],
		'profile' => $row['i']
	);
}

echo json_encode(array(
	'recordsReturned' => mysql_num_rows($result),
	'totalRecords'	=> mysql_num_rows($result),
	'startIndex' => 0,
	'sort' => 'timestamp',
	'dir' => 'desc',
	'records' => $data));

mysql_free_result($result);
