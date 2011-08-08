<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/global.php');

function updateUrlAggregates($url_id, $measurement_id)
{
	# updating latest values for the URL
	$query = sprintf("UPDATE urls SET pagetest_last_id = %d, last_update = now(), pagetest_refresh_request = 0 WHERE id = %d",
		mysql_real_escape_string($measurement_id),
		mysql_real_escape_string($url_id)
	);
	$result = mysql_query($query);

	if (!$result) {
		beaconError(mysql_error());
	}
}

$post_data = file_get_contents("php://input");
$post = json_decode($post_data, true);

if (!is_null($post) && array_key_exists('url', $post) && array_key_exists('id', $post))
{
	$url_id = getUrlId(urldecode($post['url']));

	// fixing up -1 into nulls
	foreach (array_keys($post['first']) as $metric) {
		if ($post['first'][$metric] == -1) {
			$post['first'][$metric] = null;
		}
	}
	foreach (array_keys($post['repeat']) as $metric) {
		if ($post['repeat'][$metric] == -1) {
			$post['repeat'][$metric] = null;
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
		mysql_real_escape_string($post['completed']),
		mysql_real_escape_string($url_id),
		mysql_real_escape_string($post['id']),
		mysql_real_escape_string($post['location']),
		mysql_real_escape_string($post['version']),

		mysql_real_escape_string($post['first']['loadTime']),
		mysql_real_escape_string($post['repeat']['loadTime']),
		mysql_real_escape_string($post['first']['TTFB']),
		mysql_real_escape_string($post['repeat']['TTFB']),

		mysql_real_escape_string($post['first']['bytesIn']),
		mysql_real_escape_string($post['repeat']['bytesIn']),
		mysql_real_escape_string($post['first']['bytesInDoc']),
		mysql_real_escape_string($post['repeat']['bytesInDoc']),

		mysql_real_escape_string($post['first']['requests']),
		mysql_real_escape_string($post['repeat']['requests']),
		mysql_real_escape_string($post['first']['requestsDoc']),
		mysql_real_escape_string($post['repeat']['requestsDoc']),

		mysql_real_escape_string($post['first']['connections']),
		mysql_real_escape_string($post['repeat']['connections']),
		mysql_real_escape_string($post['first']['domElements']),
		mysql_real_escape_string($post['repeat']['domElements']),

		mysql_real_escape_string($post['first']['score_cache']),
		mysql_real_escape_string($post['repeat']['score_cache']),
		mysql_real_escape_string($post['first']['score_cdn']),
		mysql_real_escape_string($post['repeat']['score_cdn']),

		mysql_real_escape_string($post['first']['score_gzip']),
		mysql_real_escape_string($post['repeat']['score_gzip']),
		mysql_real_escape_string($post['first']['score_cookies']),
		mysql_real_escape_string($post['repeat']['score_cookies']),

		mysql_real_escape_string($post['first']['score_keep-alive']),
		mysql_real_escape_string($post['repeat']['score_keep-alive']),
		mysql_real_escape_string($post['first']['score_minify']),
		mysql_real_escape_string($post['repeat']['score_minify']),

		mysql_real_escape_string($post['first']['score_combine']),
		mysql_real_escape_string($post['repeat']['score_combine']),
		mysql_real_escape_string($post['first']['score_compress']),
		mysql_real_escape_string($post['repeat']['score_compress']),

		mysql_real_escape_string($post['first']['score_etags']),
		mysql_real_escape_string($post['repeat']['score_etags']),
		mysql_real_escape_string($post['first']['gzip_total']),
		mysql_real_escape_string($post['repeat']['gzip_total']),

		mysql_real_escape_string($post['first']['gzip_savings']),
		mysql_real_escape_string($post['repeat']['gzip_savings']),
		mysql_real_escape_string($post['first']['minify_total']),
		mysql_real_escape_string($post['repeat']['minify_total']),

		mysql_real_escape_string($post['first']['minify_savings']),
		mysql_real_escape_string($post['repeat']['minify_savings']),
		mysql_real_escape_string($post['first']['image_total']),
		mysql_real_escape_string($post['repeat']['image_total']),

		mysql_real_escape_string($post['first']['image_savings']),
		mysql_real_escape_string($post['repeat']['image_savings']),

		mysql_real_escape_string($post['first']['render']),
		mysql_real_escape_string($post['repeat']['render']),
		mysql_real_escape_string($post['first']['aft']),
		mysql_real_escape_string($post['repeat']['aft']),

		mysql_real_escape_string($post['first']['fullyLoaded']),
		mysql_real_escape_string($post['repeat']['fullyLoaded']),
		mysql_real_escape_string($post['first']['docTime']),
		mysql_real_escape_string($post['repeat']['docTime']),

		mysql_real_escape_string($post['first']['domTime']),
		mysql_real_escape_string($post['repeat']['domTime'])
	);

	if (!mysql_query($query))
	{
		beaconError(mysql_error());
	}

	updateUrlAggregates($url_id, mysql_insert_id());

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
