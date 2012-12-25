<?php
require_once(dirname(dirname(__FILE__)).'/global.php');
require_once(dirname(dirname(__FILE__)).'/users/users.php');

$badinput = false;
$urls = array();
$noinput = true;

if (!array_key_exists('url', $_GET) || !is_array($_GET['url'])) {
	$badinput = true;
} else {
	$noinput = false;
	foreach ($_GET['url'] as $url)
	{
		if ($url == '') {
			continue;
		}

		if (($url = filter_var($url, FILTER_VALIDATE_URL)) === false) {
			$badinput = true;
			break;
		}

		$urls[] = $url;
	}
}

$urls = array_unique($urls);

if (count($urls) < 2) {
	$badinput = true;
}

$data = array();
$urlids = array();

// flags indicating if there is data for each of the rankers
$counters = array(
	'yslow' => 0,
	'pagespeed' => 0,
	'dynatrace' => 0
);

if (count($urls) > 0) {
	// last event timestamp
	$first = true;
	$urllist = '';
	foreach ($urls as $url) {
		if ($first) {
			$first = false;
		}
		else {
			$urllist .= ', ';
		}

		$urllist .= sprintf("'%s'", mysql_real_escape_string($url));
	}

	$query = "SELECT urls.id, url,
			y.timestamp as y_version,
			p.timestamp as p_version,
			d.timestamp as d_version
		FROM urls
			LEFT JOIN yslow2 y ON urls.yslow2_last_id = y.id
			LEFT JOIN pagespeed p ON urls.pagespeed_last_id = p.id
			LEFT JOIN dynatrace d ON urls.dynatrace_last_id = d.id
		WHERE urls.url IN ($urllist)";
	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
	}

	// loading all data
	while ($row = mysql_fetch_assoc($result)) {
		$urlids[$row['url']] =  $row['id'];
		$data[$row['url']] = array(
			'yslow' => $row['y_version'],
			'pagespeed' => $row['p_version'],
			'dynatrace' => $row['d_version']
		);

		// if at least one value exists for ranker, enable it
		if ($enabledMetrics['yslow'] && !is_null($row['y_version'])) {
			$counters['yslow'] += 1;
		}
		if ($enabledMetrics['pagespeed'] && !is_null($row['p_version'])) {
			$counters['pagespeed'] += 1;
		}
		if ($enabledMetrics['dynatrace'] && !is_null($row['d_version'])) {
			$counters['dynatrace'] += 1;
		}
	}
	mysql_free_result($result);
}

// let's see data for which rankers is available and redirect accordingly
$ranker = null;
$TITLE = 'Compare rankings';

if (array_key_exists('ranker', $_GET)) {
	if ($_GET['ranker'] == 'yslow') {
		$ranker = 'yslow';
		$TITLE = 'Compare YSlow rankings';
	} else if ($_GET['ranker'] == 'pagespeed') {
		$ranker = 'pagespeed';
		$TITLE = 'Compare Page Speed rankings';
	} else if ($_GET['ranker'] == 'dynatrace') {
		$ranker = 'dynatrace';
		$TITLE = 'Compare dynaTrace rankings';
	}
}

// calculate query string
$params = '';
$first = true;
foreach ($urls as $url) {
	if ($first) {
		$first = false;
	}
	else {
		$params.= '&';
	}
	$params.='url[]='.urlencode($url);
}

// if ranker is not specified, but there is data for at least one ranker, redirect to that ranker
if (is_null($ranker)) {
	$default = null;
	// in reverse order to override if data for ranker exists
	if ($counters['dynatrace'] > 0) { $default = 'dynatrace'; }
	if ($counters['pagespeed'] > 0) { $default = 'pagespeed'; }
	if ($counters['yslow'] > 0) { $default = 'yslow'; }

	// if there is data for the urls then use default ranker, otherwise display a form
	if (!is_null($default)) {
		header('Location: ?ranker='.$default.'&'.$params);
		exit;
	}
}

// if some URLs passed, add them to the title and load javascripts
if (!$badinput) {
	$TITLE .= ' for: '.implode(', ', $urls);

	$SCRIPTS = array(
		'http://yui.yahooapis.com/combo?2.8.1/build/yahoo/yahoo-min.js&2.8.1/build/event/event-min.js&2.8.1/build/yuiloader/yuiloader-min.js',
		$showslow_base.'ajax/simile-ajax-api.js?bundle=true',
		$showslow_base.'timeline/timeline-api.js?bundle=true',
		$showslow_base.'timeplot/timeplot-api.js?bundle=true',
		assetURL('details/compare.js')
	);
}

$SECTION = 'compare';
require_once(dirname(dirname(__FILE__)).'/header.php');
?>
<style>
.details {
	cursor: help;
}
</style>

<h2 style="margin-bottom: 0">Compare rankings</h2>
<?php

// only display menu if user picked the ranker specifically
// (otherwise either there is no data or we don't reach this point and get redirected to default)
if (!is_null($ranker)) { ?>
	<div>Ranker: <b>
	<?php
	// let's see which menus should be displayed and which one is current
	$menus = array();
	if ($ranker == 'yslow') {
		$menus[] = '<span>YSlow</span>'; // current
	} else {
		if ($counters['yslow'] > 1) { // display link only if data for more then one URL is available
			$menus[] = '<span><a href="?'.$params.'">YSlow</a></span>';
		}
	}

	if ($ranker == 'pagespeed') {
		$menus[] = '<span>Page Speed</span>'; // current
	} else {
		if ($counters['pagespeed'] > 1) { // display link only if data for more then one URL is available
			$menus[] = '<span><a href="?ranker=pagespeed&'.$params.'">Page Speed</a></span>';
		}
	}

	if ($ranker == 'dynatrace') {
		$menus[] = '<span>dynaTrace</span>'; // current
	} else {
		if ($counters['dynatrace'] > 1) { // display link only if data for more then one URL is available
			$menus[] = '<span><a href="?ranker=dynatrace&'.$params.'">dynaTrace</a></span>';
		}
	}

	echo implode(' | ', $menus);
	?>
	</b></div>
<?php } ?>

<ul style="margin-top: 1em">
<?php foreach ($urls as $url) { ?>
	<li>
	<a href="<?php echo detailsUrl($urlids[$url], $url)?>"><?php echo htmlentities(substr($url, 0, 60))?><?php if (strlen($url) > 60) { ?>...<?php } ?></a><?php

	if (is_null($data[$url][$ranker])) {
		?> (no <?php
		if ($ranker == 'yslow') { echo 'YSlow '; }
		if ($ranker == 'pagespeed') { echo 'Page Speed '; }
		if ($ranker == 'dynatrace') { echo 'dynaTrace '; }
		?>data)<?php
	}
	?></li>
<?php } ?>
</ul>
<?php

$enough_data = false;

if (!is_null($ranker)) {
	$colors = array(
		'#FD4320',
		'#3E25FA',
		'#3EDF16',
		'#EEB423',
		'#F514B5'
	);

	// now, let's calculate data to display for this ranker
	$data_to_display = array();
	$colorindex = 0;
	foreach ($data as $url => $versions) {
		if (is_null($versions[$ranker])) {
			continue;
		}

		$data_to_display[$url] = array(
			'id' => $urlids[$url],
			'version' => urlencode($versions[$ranker]),
			'color' => $colors[$colorindex]
		);

		if ($colorindex == count($colors))
		{
			$colorindex = 0;
		} else {
			$colorindex += 1;
		}
	}

	if (count($data_to_display) >= 2) {
		$enough_data = true;

		// Graph
		?>
		<script>
		data = <?php echo json_encode($data_to_display)?>;
		ranker = '<?php echo $ranker ?>';
		</script>
		<?php if (!is_null($addThisProfile)) {?>
		<!-- AddThis Button BEGIN -->
		<div class="addthis_toolbox addthis_default_style" style="margin-right: 10px;">
		<a href="http://www.addthis.com/bookmark.php?v=250&amp;username=<?php echo urlencode($addThisProfile)?>" class="addthis_button_compact">Share</a>
		<span class="addthis_separator">|</span>
		<a class="addthis_button_twitter"></a>
		<a class="addthis_button_facebook"></a>
		<a class="addthis_button_google"></a>
		<a class="addthis_button_delicious"></a>
		<a class="addthis_button_stumbleupon"></a>
		<a class="addthis_button_reddit"></a>
		<span class="addthis_separator">|</span>
		<a class="addthis_button_favorites"></a>
		<a class="addthis_button_print"></a>
		<a class="addthis_button_email"></a>
		</div>
		<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#username=<?php echo urlencode($addThisProfile)?>"></script>
		<!-- AddThis Button END -->
		<?php } ?>

		<div id="my-timeplot" style="height: 250px; margin-top: 0.2em"></div>
		<div style="fint-size: 0.2em"><?php
			if ($ranker == 'yslow') { ?><b>YSlow</b> grades<?php }
			if ($ranker == 'pagespeed') { ?><b>Page Speed</b> scores<?php }
			if ($ranker == 'dynatrace') { ?><b>dynaTrace</b> ranks<?php }
		?> for:	<?php
		foreach ($urls as $url) {
			if (!array_key_exists($url, $data_to_display)) {
				continue;
			}
			?><span style="font-weight: bold; color: <?php echo $data_to_display[$url]['color'] ?>"><?php echo $url ?></span> (0-100);
		<?php
		}
		?></div><?php
	}
}
?>

<form action="" method="GET">
<p>Enter URL to compare:</p>
<?php if ($enough_data) { ?>
<p>Enter up to 5 URLs in the form below:</p>
<?php
} else {
	?><p style="color: red; font-weight: bold">Not enought data to compare</p><?php
}
$inputs = 5;

for ($i =0 ; $i < $inputs; $i++ ) { ?>
	<input class="input-xxlarge" placeholder="http://www.example.com" name="url[]" type="url" size="80" value="<?php
	if ($i < count($urls)) {
		echo htmlentities($urls[$i]);
	} ?>"/><br/>
<?php
}
?>
<input type="hidden" name="ranker" value="<?php echo $ranker ?>"/>
<input class="btn btn-primary" type="submit" value="Compare"/>
</form>
<?php
require_once(dirname(dirname(__FILE__)).'/footer.php');
