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
	$first = array_key_exists('firstView', $post['average']);
	$repeat = array_key_exists('repeatView', $post['average']);

	// fixing up -1 into nulls
	if ($first) {
		foreach (array_keys($post['average']['firstView']) as $metric) {
			if ($post['average']['firstView'][$metric] == -1) {
				$post['average']['firstView'][$metric] = null;
			}
		}
	}
	if ($repeat) {
		foreach (array_keys($post['average']['repeatView']) as $metric) {
			if ($post['average']['repeatView'][$metric] == -1) {
				$post['average']['repeatView'][$metric] = null;
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

		mysql_real_escape_string($first ? $post['average']['firstView']['loadTime'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['loadTime'] : null),
		mysql_real_escape_string($first ? $post['average']['firstView']['TTFB'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['TTFB'] : null),

		mysql_real_escape_string($first ? $post['average']['firstView']['bytesIn'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['bytesIn'] : null),
		mysql_real_escape_string($first ? $post['average']['firstView']['bytesInDoc'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['bytesInDoc'] : null),

		mysql_real_escape_string($first ? $post['average']['firstView']['requests'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['requests'] : null),
		mysql_real_escape_string($first ? $post['average']['firstView']['requestsDoc'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['requestsDoc'] : null),

		mysql_real_escape_string($first ? $post['average']['firstView']['connections'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['connections'] : null),
		mysql_real_escape_string($first ? $post['average']['firstView']['domElements'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['domElements'] : null),

		mysql_real_escape_string($first ? $post['average']['firstView']['score_cache'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['score_cache'] : null),
		mysql_real_escape_string($first ? $post['average']['firstView']['score_cdn'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['score_cdn'] : null),

		mysql_real_escape_string($first ? $post['average']['firstView']['score_gzip'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['score_gzip'] : null),
		mysql_real_escape_string($first ? $post['average']['firstView']['score_cookies'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['score_cookies'] : null),

		mysql_real_escape_string($first ? $post['average']['firstView']['score_keep-alive'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['score_keep-alive'] : null),
		mysql_real_escape_string($first ? $post['average']['firstView']['score_minify'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['score_minify'] : null),

		mysql_real_escape_string($first ? $post['average']['firstView']['score_combine'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['score_combine'] : null),
		mysql_real_escape_string($first ? $post['average']['firstView']['score_compress'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['score_compress'] : null),

		mysql_real_escape_string($first ? $post['average']['firstView']['score_etags'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['score_etags'] : null),
		mysql_real_escape_string($first ? $post['average']['firstView']['gzip_total'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['gzip_total'] : null),

		mysql_real_escape_string($first ? $post['average']['firstView']['gzip_savings'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['gzip_savings'] : null),
		mysql_real_escape_string($first ? $post['average']['firstView']['minify_total'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['minify_total'] : null),

		mysql_real_escape_string($first ? $post['average']['firstView']['minify_savings'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['minify_savings'] : null),
		mysql_real_escape_string($first ? $post['average']['firstView']['image_total'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['image_total'] : null),

		mysql_real_escape_string($first ? $post['average']['firstView']['image_savings'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['image_savings'] : null),

		mysql_real_escape_string($first ? $post['average']['firstView']['render'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['render'] : null),
		mysql_real_escape_string($first ? $post['average']['firstView']['aft'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['aft'] : null),

		mysql_real_escape_string($first ? $post['average']['firstView']['fullyLoaded'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['fullyLoaded'] : null),
		mysql_real_escape_string($first ? $post['average']['firstView']['docTime'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['docTime'] : null),

		mysql_real_escape_string($first ? $post['average']['firstView']['domTime'] : null),
		mysql_real_escape_string($repeat ? $post['average']['repeatView']['domTime'] : null)
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
