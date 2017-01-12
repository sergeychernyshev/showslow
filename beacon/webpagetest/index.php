<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/global.php');

function updateUrlAggregates($url_id, $measurement_id)
{
	# updating latest values for the URL
	$query = sprintf("UPDATE urls SET pagetest_last_id = %d, last_update = now(), pagetest_refresh_request = 0 WHERE id = %d",
		mysqli_real_escape_string($conn, $measurement_id),
		mysqli_real_escape_string($conn, $url_id)
	);
	$result = mysqli_query($conn, $query);

	if (!$result) {
		beaconError(mysqli_error($conn));
	}
}

$post_data = file_get_contents("php://input");
$post = json_decode($post_data, true);

#error_log(implode('|', array_keys($post['first'])));

if (!is_null($post) && array_key_exists('url', $post) && array_key_exists('id', $post))
{
	checkBeaconKey('webpagetest');

	$url_id = getUrlId(urldecode($post['url']));

	$first = array_key_exists('first', $post);
	$repeat = array_key_exists('repeat', $post);

	// fixing up -1 into nulls
	if ($first) {
		foreach (array_keys($post['first']) as $metric) {
			if ($post['first'][$metric] == -1) {
				$post['first'][$metric] = null;
			}
		}
	}
	if ($repeat) {
		foreach (array_keys($post['repeat']) as $metric) {
			if ($post['repeat'][$metric] == -1) {
				$post['repeat'][$metric] = null;
			}
		}
	}

	# adding new entry
	$query = sprintf("/* WPT POST */ REPLACE INTO pagetest (
		timestamp, url_id, test_id, location, version,
		f_LoadTime, r_LoadTime, f_TTFB, r_TTFB,
		f_bytesIn, r_bytesIn, f_bytesInDoc, r_bytesInDoc,
		f_requests, r_requests, f_requestsDoc, r_requestsDoc,
		f_connections, r_connections, f_domElements, r_domElements,
		f_score_cache, r_score_cache, f_score_cdn, r_score_cdn,
		f_score_gzip, r_score_gzip, f_score_cookies, r_score_cookies,
		f_score_keep_alive, r_score_keep_alive, f_score_minify, r_score_minify,
		f_score_combine, r_score_combine, f_score_compress, r_score_compress,
		f_score_etags, r_score_etags, f_gzip_total, r_gzip_total,
		f_gzip_savings, r_gzip_savings, f_minify_total, r_minify_total,
		f_minify_savings, r_minify_savings, f_image_total, r_image_total,
		f_image_savings, r_image_savings,
		f_render, r_render, f_aft, r_aft,
		f_fullyLoaded, r_fullyLoaded, f_docTime, r_docTime,
		f_domTime, r_domTime
	)
	VALUES (
		FROM_UNIXTIME('%d'), '%d', '%s', '%s', '%s',
		'%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d',
		'%d', '%d',
		'%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d',
		'%d', '%d'
	)",
		mysqli_real_escape_string($conn, $post['completed']),
		mysqli_real_escape_string($conn, $url_id),
		mysqli_real_escape_string($conn, $post['id']),
		mysqli_real_escape_string($conn, $post['location']),
		mysqli_real_escape_string($conn, $post['version']),

		mysqli_real_escape_string($conn, $first ? $post['first']['loadTime'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['loadTime'] : null),
		mysqli_real_escape_string($conn, $first ? $post['first']['TTFB'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['TTFB'] : null),

		mysqli_real_escape_string($conn, $first ? $post['first']['bytesIn'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['bytesIn'] : null),
		mysqli_real_escape_string($conn, $first ? $post['first']['bytesInDoc'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['bytesInDoc'] : null),

		mysqli_real_escape_string($conn, $first ? $post['first']['requests'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['requests'] : null),
		mysqli_real_escape_string($conn, $first ? $post['first']['requestsDoc'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['requestsDoc'] : null),

		mysqli_real_escape_string($conn, $first ? $post['first']['connections'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['connections'] : null),
		mysqli_real_escape_string($conn, $first ? $post['first']['domElements'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['domElements'] : null),

		mysqli_real_escape_string($conn, $first ? $post['first']['score_cache'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['score_cache'] : null),
		mysqli_real_escape_string($conn, $first ? $post['first']['score_cdn'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['score_cdn'] : null),

		mysqli_real_escape_string($conn, $first ? $post['first']['score_gzip'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['score_gzip'] : null),
		mysqli_real_escape_string($conn, $first ? $post['first']['score_cookies'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['score_cookies'] : null),

		mysqli_real_escape_string($conn, $first ? $post['first']['score_keep-alive'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['score_keep-alive'] : null),
		mysqli_real_escape_string($conn, $first ? $post['first']['score_minify'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['score_minify'] : null),

		mysqli_real_escape_string($conn, $first ? $post['first']['score_combine'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['score_combine'] : null),
		mysqli_real_escape_string($conn, $first ? $post['first']['score_compress'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['score_compress'] : null),

		mysqli_real_escape_string($conn, $first ? $post['first']['score_etags'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['score_etags'] : null),
		mysqli_real_escape_string($conn, $first ? $post['first']['gzip_total'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['gzip_total'] : null),

		mysqli_real_escape_string($conn, $first ? $post['first']['gzip_savings'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['gzip_savings'] : null),
		mysqli_real_escape_string($conn, $first ? $post['first']['minify_total'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['minify_total'] : null),

		mysqli_real_escape_string($conn, $first ? $post['first']['minify_savings'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['minify_savings'] : null),
		mysqli_real_escape_string($conn, $first ? $post['first']['image_total'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['image_total'] : null),

		mysqli_real_escape_string($conn, $first ? $post['first']['image_savings'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['image_savings'] : null),

		mysqli_real_escape_string($conn, $first ? $post['first']['render'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['render'] : null),
		mysqli_real_escape_string($conn, $first ? $post['first']['aft'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['aft'] : null),

		mysqli_real_escape_string($conn, $first ? $post['first']['fullyLoaded'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['fullyLoaded'] : null),
		mysqli_real_escape_string($conn, $first ? $post['first']['docTime'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['docTime'] : null),

		mysqli_real_escape_string($conn, $first ? $post['first']['domTime'] : null),
		mysqli_real_escape_string($conn, $repeat ? $post['repeat']['domTime'] : null)
	);

	if (!mysqli_query($conn, $query))
	{
		beaconError(mysqli_error($conn));
	}

	updateUrlAggregates($url_id, mysqli_insert_id($conn));

} else {
	header('HTTP/1.0 400 Bad Request');

	$TITLE = 'Bad Request: WebPageTest beacon';

	require_once(dirname(dirname(dirname(__FILE__))).'/header.php');
	?>
<h2><a href="../">Beacons</a>: WebPageTest</h2>
<p>This is <a href="http://www.webpagetest.org/">WebPageTest</a> beacon entry point.</p>

<h2>Configure your WebPageTest instance</h2>
<p><b style="color: red">WARNING! Only use this beacon If you're OK with all your WebPageTest data to be recorded by this instance of ShowSlow and displayed at <a href="<?php echo $showslow_base?>"><?php echo $showslow_base?></a><br/>You can also <a href="http://www.showslow.org/Installation_and_configuration">install ShowSlow on your own server</a> to limit the risk.</b></p>

<?php
require_once(dirname(dirname(dirname(__FILE__))).'/footer.php');

	exit;
}

header('HTTP/1.0 204 Data accepted');
