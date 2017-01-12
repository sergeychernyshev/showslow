<?php
/**
 * This is a data receiver for YSlow
 *
 * Documentation for the beacon is available here:
 * http://yslow.org/user-guide/#yslow_beacon
 */
require_once(dirname(dirname(dirname(__FILE__))).'/global.php');

function updateUrlAggregates($url_id, $measurement_id)
{
	global $cleanOldYSlowBeaconDetails, $keepBeaconDetails;

	# updating latest values for the URL
	$query = sprintf("UPDATE urls SET yslow2_last_id = %d, last_update = now(), y_refresh_request = 0 WHERE id = %d",
		mysqli_real_escape_string($conn, $measurement_id),
		mysqli_real_escape_string($conn, $url_id)
	);
	$result = mysqli_query($conn, $query);

	if (!$result) {
		beaconError(mysqli_error($conn));
	}

	// Clean old details for this URL to conserve space
	if ($keepBeaconDetails && $cleanOldYSlowBeaconDetails) {
		# adding new entry
		$query = sprintf("/* clean old beacon details */
			UPDATE yslow2
			SET details = NULL
			WHERE url_id = '%d' AND id <> '%d'
		", mysqli_real_escape_string($conn, $url_id), mysqli_real_escape_string($conn, $measurement_id));

		if (!mysqli_query($conn, $query))
		{
			beaconError(mysqli_error($conn));
		}
	}

}

$post_data = file_get_contents("php://input");
$post = json_decode($post_data, true);

if (!is_null($post) && array_key_exists('u', $post) && array_key_exists('g', $post)
	&& array_key_exists('i', $post) && in_array($post['i'], $YSlow2AllowedProfiles)
	&& array_key_exists('w', $post) && filter_var($post['w'], FILTER_VALIDATE_INT) !== false
	&& array_key_exists('o', $post) && filter_var($post['o'], FILTER_VALIDATE_INT) !== false
	&& array_key_exists('r', $post) && filter_var($post['r'], FILTER_VALIDATE_INT) !== false
	)
{
	checkBeaconKey('yslow');

	$url_id = getUrlId(urldecode($post['u']));

	$grades = $post['g'];

	$metrics = array(
		'ynumreq',
		'ycdn',
		'yexpires',
		'yemptysrc',
		'ycompress',
		'ycsstop',
		'yjsbottom',
		'yexpressions',
		'yexternal',
		'ydns',
		'yminify',
		'yredirects',
		'ydupes',
		'yetags',
		'yxhr',
		'yxhrmethod',
		'ymindom',
		'yno404',
		'ymincookie',
		'ycookiefree',
		'ynofilter',
		'yimgnoscale',
		'yfavicon'
	);

	foreach ($metrics as $metric) {
		if (!array_key_exists($metric, $grades) || !array_key_exists('score', $grades[$metric])) {
			$grades[$metric]['score'] = null;
		}
	}

	# adding new entry
	$query = sprintf("/* grades POST */ INSERT INTO yslow2 (
		`ip` , `user_agent` , `url_id` ,
		`w` , `o` , `r` , `i` , lt,
		`ynumreq`,	`ycdn`,		`yexpires`,	`yemptysrc`, `ycompress`,	`ycsstop`,
		`yjsbottom`,	`yexpressions`,	`yexternal`,	`ydns`,		`yminify`,
		`yredirects`,	`ydupes`,	`yetags`,	`yxhr`,		`yxhrmethod`,
		`ymindom`,	`yno404`,	`ymincookie`,	`ycookiefree`,	`ynofilter`,
		`yimgnoscale`,	`yfavicon`".($keepBeaconDetails ? ', details' : '')."
	)
	VALUES (inet_aton('%s'), '%s', '%d',
		'%d', '%d', '%d', '%s', '%d',
		'%d', '%d', '%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d', '%d',
		'%d', '%d'".($keepBeaconDetails ? ", '%s'" : '')."
	)",
		mysqli_real_escape_string($conn, $_SERVER['REMOTE_ADDR']),
		mysqli_real_escape_string($conn, $_SERVER['HTTP_USER_AGENT']),
		mysqli_real_escape_string($conn, $url_id),
		mysqli_real_escape_string($conn, $post['w']),
		mysqli_real_escape_string($conn, $post['o']),
		mysqli_real_escape_string($conn, $post['r']),
		mysqli_real_escape_string($conn, $post['i']),
		mysqli_real_escape_string($conn, array_key_exists('lt', $post) ? $post['lt'] : null),
		mysqli_real_escape_string($conn, $grades['ynumreq']['score']),
		mysqli_real_escape_string($conn, $grades['ycdn']['score']),
		mysqli_real_escape_string($conn, $grades['yexpires']['score']),
		mysqli_real_escape_string($conn, $grades['yemptysrc']['score']),
		mysqli_real_escape_string($conn, $grades['ycompress']['score']),
		mysqli_real_escape_string($conn, $grades['ycsstop']['score']),
		mysqli_real_escape_string($conn, $grades['yjsbottom']['score']),
		mysqli_real_escape_string($conn, $grades['yexpressions']['score']),
		mysqli_real_escape_string($conn, $grades['yexternal']['score']),
		mysqli_real_escape_string($conn, $grades['ydns']['score']),
		mysqli_real_escape_string($conn, $grades['yminify']['score']),
		mysqli_real_escape_string($conn, $grades['yredirects']['score']),
		mysqli_real_escape_string($conn, $grades['ydupes']['score']),
		mysqli_real_escape_string($conn, $grades['yetags']['score']),
		mysqli_real_escape_string($conn, $grades['yxhr']['score']),
		mysqli_real_escape_string($conn, $grades['yxhrmethod']['score']),
		mysqli_real_escape_string($conn, $grades['ymindom']['score']),
		mysqli_real_escape_string($conn, $grades['yno404']['score']),
		mysqli_real_escape_string($conn, $grades['ymincookie']['score']),
		mysqli_real_escape_string($conn, $grades['ycookiefree']['score']),
		mysqli_real_escape_string($conn, $grades['ynofilter']['score']),
		mysqli_real_escape_string($conn, $grades['yimgnoscale']['score']),
		mysqli_real_escape_string($conn, $grades['yfavicon']['score']),
		$keepBeaconDetails ? mysqli_real_escape_string($conn, $post_data) : null
	);

	if (!mysqli_query($conn, $query))
	{
		beaconError(mysqli_error($conn));
	}

	updateUrlAggregates($url_id, mysqli_insert_id($conn));

} else if (array_key_exists('u', $_GET) && array_key_exists('i', $_GET) && in_array($_GET['i'], $YSlow2AllowedProfiles)
	&& array_key_exists('w', $_GET) && filter_var($_GET['w'], FILTER_VALIDATE_INT) !== false
	&& array_key_exists('o', $_GET) && filter_var($_GET['o'], FILTER_VALIDATE_INT) !== false
	&& array_key_exists('r', $_GET) && filter_var($_GET['r'], FILTER_VALIDATE_INT) !== false
	)
{
	checkBeaconKey('yslow');

	$url_id = getUrlId($_GET['u']);

	# adding new entry
	$query = sprintf("/* basic GET */ INSERT INTO yslow2 (
		`ip` , `user_agent` , `url_id` ,
		`w` , `o` , `r` , `i`, lt,
		`ynumreq`,	`ycdn`,		`yexpires`,	`yemptysrc`,	`ycompress`,	`ycsstop`,
		`yjsbottom`,	`yexpressions`,	`yexternal`,	`ydns`,		`yminify`,
		`yredirects`,	`ydupes`,	`yetags`,	`yxhr`,		`yxhrmethod`,
		`ymindom`,	`yno404`,	`ymincookie`,	`ycookiefree`,	`ynofilter`,
		`yimgnoscale`,	`yfavicon`
	)
	VALUES (inet_aton('%s'), '%s', '%d',
		'%d', '%d', '%d', '%s', '%d',
		'%d', '%d', '%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d', '%d',
		'%d', '%d', '%d', '%d', '%d',
		'%d', '%d'
	)",
		mysqli_real_escape_string($conn, $_SERVER['REMOTE_ADDR']),
		mysqli_real_escape_string($conn, $_SERVER['HTTP_USER_AGENT']),
		mysqli_real_escape_string($conn, $url_id),
		mysqli_real_escape_string($conn, $_GET['w']),
		mysqli_real_escape_string($conn, $_GET['o']),
		mysqli_real_escape_string($conn, $_GET['r']),
		mysqli_real_escape_string($conn, $_GET['i']),
		mysqli_real_escape_string($conn, $_GET['lt']),
		mysqli_real_escape_string($conn, $_GET['ynumreq']),
		mysqli_real_escape_string($conn, $_GET['ycdn']),
		mysqli_real_escape_string($conn, $_GET['yexpires']),
		mysqli_real_escape_string($conn, $_GET['yemptysrc']),
		mysqli_real_escape_string($conn, $_GET['ycompress']),
		mysqli_real_escape_string($conn, $_GET['ycsstop']),
		mysqli_real_escape_string($conn, $_GET['yjsbottom']),
		mysqli_real_escape_string($conn, $_GET['yexpressions']),
		mysqli_real_escape_string($conn, $_GET['yexternal']),
		mysqli_real_escape_string($conn, $_GET['ydns']),
		mysqli_real_escape_string($conn, $_GET['yminify']),
		mysqli_real_escape_string($conn, $_GET['yredirects']),
		mysqli_real_escape_string($conn, $_GET['ydupes']),
		mysqli_real_escape_string($conn, $_GET['yetags']),
		mysqli_real_escape_string($conn, $_GET['yxhr']),
		mysqli_real_escape_string($conn, $_GET['yxhrmethod']),
		mysqli_real_escape_string($conn, $_GET['ymindom']),
		mysqli_real_escape_string($conn, $_GET['yno404']),
		mysqli_real_escape_string($conn, $_GET['ymincookie']),
		mysqli_real_escape_string($conn, $_GET['ycookiefree']),
		mysqli_real_escape_string($conn, $_GET['ynofilter']),
		mysqli_real_escape_string($conn, $_GET['yimgnoscale']),
		mysqli_real_escape_string($conn, $_GET['yfavicon'])
	);

	if (!mysqli_query($conn, $query))
	{
		beaconError(mysqli_error($conn));
	}

	updateUrlAggregates($url_id, mysqli_insert_id($conn));
} else {
	header('HTTP/1.0 400 Bad Request');

	$TITLE = 'Bad Request: YSlow beacon';

	require_once(dirname(dirname(dirname(__FILE__))).'/header.php');
	?>
<h2><a href="../">Beacons</a>: YSlow</h2>
<p>This is <a href="http://www.yslow.org/">YSlow</a> beacon entry point.</p>

<h2>Configure your YSlow</h2>
<p><b style="color: red">WARNING! Only use this beacon If you're OK with all your YSlow data to be recorded by this instance of ShowSlow and displayed at <a href="<?php echo $showslow_base?>"><?php echo $showslow_base?></a><br/>You can also <a href="http://www.showslow.org/Installation_and_configuration">install ShowSlow on your own server</a> to limit the risk.</b></p>

<p>Set these two Firefox parameters on <b>about:config</b> page:</p>

</ul>
<h3>Yslow 2.x</h3>
<ul>
<li>extensions.yslow.beaconUrl = <b style="color: blue"><?php echo $showslow_base?>beacon/yslow/</b></li>
<li>extensions.yslow.beaconInfo = <b style="color: blue">grade</b></li>
<li>extensions.yslow.optinBeacon = <b style="color: blue">true</b></li>
</ul>

<hr/>
<p><a href="../">&lt;&lt; back to the list of beacons</a></p>
<?php
require_once(dirname(dirname(dirname(__FILE__))).'/footer.php');

	exit;
}

header('HTTP/1.0 204 Data accepted');
