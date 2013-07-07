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
		mysql_real_escape_string($measurement_id),
		mysql_real_escape_string($url_id)
	);
	$result = mysql_query($query);

	if (!$result) {
		beaconError(mysql_error());
	}

	// Clean old details for this URL to conserve space
	if ($keepBeaconDetails && $cleanOldYSlowBeaconDetails) {
		# adding new entry
		$query = sprintf("/* clean old beacon details */
			UPDATE yslow2
			SET details = NULL
			WHERE url_id = '%d' AND id <> '%d'
		", mysql_real_escape_string($url_id), mysql_real_escape_string($measurement_id));

		if (!mysql_query($query))
		{
			beaconError(mysql_error());
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
		mysql_real_escape_string($_SERVER['REMOTE_ADDR']),
		mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']),
		mysql_real_escape_string($url_id),
		mysql_real_escape_string($post['w']),
		mysql_real_escape_string($post['o']),
		mysql_real_escape_string($post['r']),
		mysql_real_escape_string($post['i']),
		mysql_real_escape_string(array_key_exists('lt', $post) ? $post['lt'] : null),
		mysql_real_escape_string($grades['ynumreq']['score']),
		mysql_real_escape_string($grades['ycdn']['score']),
		mysql_real_escape_string($grades['yexpires']['score']),
		mysql_real_escape_string($grades['yemptysrc']['score']),
		mysql_real_escape_string($grades['ycompress']['score']),
		mysql_real_escape_string($grades['ycsstop']['score']),
		mysql_real_escape_string($grades['yjsbottom']['score']),
		mysql_real_escape_string($grades['yexpressions']['score']),
		mysql_real_escape_string($grades['yexternal']['score']),
		mysql_real_escape_string($grades['ydns']['score']),
		mysql_real_escape_string($grades['yminify']['score']),
		mysql_real_escape_string($grades['yredirects']['score']),
		mysql_real_escape_string($grades['ydupes']['score']),
		mysql_real_escape_string($grades['yetags']['score']),
		mysql_real_escape_string($grades['yxhr']['score']),
		mysql_real_escape_string($grades['yxhrmethod']['score']),
		mysql_real_escape_string($grades['ymindom']['score']),
		mysql_real_escape_string($grades['yno404']['score']),
		mysql_real_escape_string($grades['ymincookie']['score']),
		mysql_real_escape_string($grades['ycookiefree']['score']),
		mysql_real_escape_string($grades['ynofilter']['score']),
		mysql_real_escape_string($grades['yimgnoscale']['score']),
		mysql_real_escape_string($grades['yfavicon']['score']),
		$keepBeaconDetails ? mysql_real_escape_string($post_data) : null
	);

	if (!mysql_query($query))
	{
		beaconError(mysql_error());
	}

	updateUrlAggregates($url_id, mysql_insert_id());

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
		mysql_real_escape_string($_SERVER['REMOTE_ADDR']),
		mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']),
		mysql_real_escape_string($url_id),
		mysql_real_escape_string($_GET['w']),
		mysql_real_escape_string($_GET['o']),
		mysql_real_escape_string($_GET['r']),
		mysql_real_escape_string($_GET['i']),
		mysql_real_escape_string($_GET['lt']),
		mysql_real_escape_string($_GET['ynumreq']),
		mysql_real_escape_string($_GET['ycdn']),
		mysql_real_escape_string($_GET['yexpires']),
		mysql_real_escape_string($_GET['yemptysrc']),
		mysql_real_escape_string($_GET['ycompress']),
		mysql_real_escape_string($_GET['ycsstop']),
		mysql_real_escape_string($_GET['yjsbottom']),
		mysql_real_escape_string($_GET['yexpressions']),
		mysql_real_escape_string($_GET['yexternal']),
		mysql_real_escape_string($_GET['ydns']),
		mysql_real_escape_string($_GET['yminify']),
		mysql_real_escape_string($_GET['yredirects']),
		mysql_real_escape_string($_GET['ydupes']),
		mysql_real_escape_string($_GET['yetags']),
		mysql_real_escape_string($_GET['yxhr']),
		mysql_real_escape_string($_GET['yxhrmethod']),
		mysql_real_escape_string($_GET['ymindom']),
		mysql_real_escape_string($_GET['yno404']),
		mysql_real_escape_string($_GET['ymincookie']),
		mysql_real_escape_string($_GET['ycookiefree']),
		mysql_real_escape_string($_GET['ynofilter']),
		mysql_real_escape_string($_GET['yimgnoscale']),
		mysql_real_escape_string($_GET['yfavicon'])
	);

	if (!mysql_query($query))
	{
		beaconError(mysql_error());
	}

	updateUrlAggregates($url_id, mysql_insert_id());
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
