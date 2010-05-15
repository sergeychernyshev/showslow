<?php 
require_once(dirname(dirname(__FILE__)).'/global.php');
require_once(dirname(dirname(__FILE__)).'/users/users.php');

$pagespeed = false;
if (array_key_exists('ranker', $_GET) && $_GET['ranker'] == 'pagespeed')
{
	$pagespeed = true;
}

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

if (count($urls) < 2) {
	$badinput = true;
}

if (array_key_exists('ranker', $_GET) && $_GET['ranker'] == 'pagespeed')
{
	$TITLE = 'Compare Page Speed rankings';
} else {
	$TITLE = 'Compare YSlow rankings';
}
if (!$badinput) {
	$TITLE .= ' for: '.implode(', ', $urls);

	$SCRIPTS = array(
		'http://yui.yahooapis.com/combo?2.8.1/build/yahoo/yahoo-min.js&2.8.1/build/event/event-min.js',
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
.yslow1 {
	color: #55009D;
}

.yslow2 {
	color: #2175D9;
}

.details {
	cursor: help;
}
</style>
<?php 
$data = array();
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

	if ($pagespeed) {
		$query = "SELECT url, pagespeed.timestamp as version FROM urls INNER JOIN pagespeed ON urls.pagespeed_last_id = pagespeed.id WHERE urls.url IN ($urllist) AND urls.pagespeed_last_id IS NOT NULL";
	} else {
		$query = "SELECT url, yslow2.timestamp as version FROM urls INNER JOIN yslow2 ON urls.yslow2_last_id = yslow2.id WHERE urls.url IN ($urllist) AND urls.yslow2_last_id IS NOT NULL";
	}
	$result = mysql_query($query);

	if (!$result) {
		error_log(mysql_error());
	}

	$colors = array(
		'#FD4320',
		'#3E25FA',
		'#3EDF16',
		'#EEB423',
		'#F514B5'
	);
	$colorindex = 0;

	while ($row = mysql_fetch_assoc($result)) {
		$data[$row['url']] = array(
			'version' => urlencode($row['version']),
			'color' => $colors[$colorindex]
		);

		if ($pagespeed) {
			$data[$row['url']]['ranker'] = 'pagespeed';
		} else {
			$data[$row['url']]['ranker'] = 'yslow';
		}
	
		if ($colorindex == count($colors))
		{
			$colorindex = 0;
		} else {
			$colorindex += 1;
		}
	}
	mysql_free_result($result);
}

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

if ($pagespeed) {
?>
	<h1>Compare Page Speed rankings (<a href="?<?php echo $params?>">switch to YSlow</a>)</h1>
<?php } else {?>
	<h1>Compare YSlow rankings (<a href="?ranker=pagespeed&<?php echo $params?>">switch to Page Speed</a>)</h1>
<?php }?>
<ul>
<?php foreach ($urls as $url) { ?>
	<li>
	<a href="./?url=<?php echo urlencode($url)?>"><?php echo htmlentities(substr($url, 0, 60))?><?php if (strlen($url) > 60) { ?>...<?php } ?></a><?php
	if (!array_key_exists($url, $data)) {
		?> (no <?php echo $pagespeed ? 'Page Speed' : 'YSlow' ?> data)<?php
	}
	?></li>
<?php } ?>
</ul>
<?php
if (count($data) >= 2) {
	// Graph
	?>
	<script>
	data = <?php echo json_encode($data)?>;
	</script>

	<div id="my-timeplot" style="height: 250px;"></div>
	<div style="fint-size: 0.2em">YSlow grades for:
	<?php foreach ($urls as $url) {
		if (!array_key_exists($url, $data)) {
			continue;
		}

		?><span style="font-weight: bold; color: <?php echo $data[$url]['color'] ?>"><?php echo $url ?></span> (0-100);
	<?php } ?>
	</div>
<?php } ?>

<form action="" method="GET">
<h3>Enter URL to compare:</h3>
<?php if ($noinput) { ?>
<p>Enter up to 5 URLs in the form below:</p>
<?php
} else if (count($data) < 2) {
	?><p style="color: red; font-weight: bold">Not enought data to compare</p><?php 
}
$inputs = 5;

for ($i =0 ; $i < $inputs; $i++ ) { ?>
	<input name="url[]" type="text" size="80" value="<?php
	if ($i < count($urls)) {
		echo htmlentities($urls[$i]);
	} ?>"/><br/>
<?php
}
?>
<input type="submit" value="compare"/>
</form>
<?php
require_once(dirname(dirname(__FILE__)).'/footer.php');
