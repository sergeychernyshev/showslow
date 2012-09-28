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
		mysql_real_escape_string($post['completed']),
		mysql_real_escape_string($url_id),
		mysql_real_escape_string($post['id']),
		mysql_real_escape_string($post['location']),
		mysql_real_escape_string($post['version']),

		mysql_real_escape_string($first ? $post['first']['loadTime'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['loadTime'] : null),
		mysql_real_escape_string($first ? $post['first']['TTFB'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['TTFB'] : null),

		mysql_real_escape_string($first ? $post['first']['bytesIn'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['bytesIn'] : null),
		mysql_real_escape_string($first ? $post['first']['bytesInDoc'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['bytesInDoc'] : null),

		mysql_real_escape_string($first ? $post['first']['requests'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['requests'] : null),
		mysql_real_escape_string($first ? $post['first']['requestsDoc'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['requestsDoc'] : null),

		mysql_real_escape_string($first ? $post['first']['connections'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['connections'] : null),
		mysql_real_escape_string($first ? $post['first']['domElements'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['domElements'] : null),

		mysql_real_escape_string($first ? $post['first']['score_cache'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['score_cache'] : null),
		mysql_real_escape_string($first ? $post['first']['score_cdn'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['score_cdn'] : null),

		mysql_real_escape_string($first ? $post['first']['score_gzip'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['score_gzip'] : null),
		mysql_real_escape_string($first ? $post['first']['score_cookies'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['score_cookies'] : null),

		mysql_real_escape_string($first ? $post['first']['score_keep-alive'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['score_keep-alive'] : null),
		mysql_real_escape_string($first ? $post['first']['score_minify'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['score_minify'] : null),

		mysql_real_escape_string($first ? $post['first']['score_combine'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['score_combine'] : null),
		mysql_real_escape_string($first ? $post['first']['score_compress'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['score_compress'] : null),

		mysql_real_escape_string($first ? $post['first']['score_etags'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['score_etags'] : null),
		mysql_real_escape_string($first ? $post['first']['gzip_total'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['gzip_total'] : null),

		mysql_real_escape_string($first ? $post['first']['gzip_savings'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['gzip_savings'] : null),
		mysql_real_escape_string($first ? $post['first']['minify_total'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['minify_total'] : null),

		mysql_real_escape_string($first ? $post['first']['minify_savings'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['minify_savings'] : null),
		mysql_real_escape_string($first ? $post['first']['image_total'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['image_total'] : null),

		mysql_real_escape_string($first ? $post['first']['image_savings'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['image_savings'] : null),

		mysql_real_escape_string($first ? $post['first']['render'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['render'] : null),
		mysql_real_escape_string($first ? $post['first']['aft'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['aft'] : null),

		mysql_real_escape_string($first ? $post['first']['fullyLoaded'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['fullyLoaded'] : null),
		mysql_real_escape_string($first ? $post['first']['docTime'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['docTime'] : null),

		mysql_real_escape_string($first ? $post['first']['domTime'] : null),
		mysql_real_escape_string($repeat ? $post['repeat']['domTime'] : null)
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
