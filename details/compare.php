<?php 
require_once(dirname(dirname(__FILE__)).'/global.php');

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

?><html>
<head>
<title>Show Slow: Compare <?php if (array_key_exists('ranker', $_GET) && $_GET['ranker'] = 'pagespeed') {
?>Page Speed<?php } else {?>YSlow<?php } ?> rankings<?php if (!$badinput) { ?> for: <?php echo implode(', ', $urls);?><?php } ?></title>
<style type="text/css">
body {
margin:0;
padding:1em;
}
</style>
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?2.7.0/build/fonts/fonts-min.css&2.7.0/build/tabview/assets/skins/sam/tabview.css">
<?php if (!$badinput) { ?>
<script type="text/javascript" src="<?php echo $showslow_base?>ajax/simile-ajax-api.js?bundle=true"></script>
<script type="text/javascript" src="<?php echo $showslow_base?>timeline/timeline-api.js?bundle=true"></script>
<script type="text/javascript" src="<?php echo $showslow_base?>timeplot/timeplot-api.js?bundle=true"></script>
<script src="compare.js?v=4" type="text/javascript"></script>
<?php } ?>

<?php if ($showFeedbackButton) {?>
<script type="text/javascript">
var uservoiceOptions = {
  /* required */
  key: 'showslow',
  host: 'showslow.uservoice.com', 
  forum: '18807',
  showTab: true,  
  /* optional */
  alignment: 'right',
  background_color:'#f00', 
  text_color: 'white',
  hover_color: '#06C',
  lang: 'en'
};

function _loadUserVoice() {
  var s = document.createElement('script');
  s.setAttribute('type', 'text/javascript');
  s.setAttribute('src', ("https:" == document.location.protocol ? "https://" : "http://") + "cdn.uservoice.com/javascripts/widgets/tab.js");
  document.getElementsByTagName('head')[0].appendChild(s);
}
_loadSuper = window.onload;
window.onload = (typeof window.onload != 'function') ? _loadUserVoice : function() { _loadSuper(); _loadUserVoice(); };
</script>
<?php } ?>
<?php if ($googleAnalyticsProfile) {?>
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '<?php echo $googleAnalyticsProfile ?>']);
_gaq.push(['_trackPageview']);

(function() {
var ga = document.createElement('script');
ga.src = ('https:' == document.location.protocol ?
    'https://ssl' : 'http://www') +
    '.google-analytics.com/ga.js';
ga.setAttribute('async', 'true');
document.documentElement.firstChild.appendChild(ga);
})();
</script>
<?php }?>
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
</head>
<body class="yui-skin-sam"<?php if (!$badinput) { ?> onload="onLoad(data);" onresize="onResize();"<?php } ?>>
<a href="http://www.showslow.org/"><img src="<?php echo assetURL('showslow_icon.png')?>" style="float: right; margin-left: 1em; border: 0"/></a>
<div style="float: right">powered by <a href="http://www.showslow.org/">showslow</a></div>
<h1><a title="Click here to go to home page" href="../">Show Slow</a></h1>

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
	<h2>Compare Page Speed rankings (<a href="?<?php echo $params?>">switch to YSlow</a>)</h2>
<?php } else {?>
	<h2>Compare YSlow rankings (<a href="?ranker=pagespeed&<?php echo $params?>">switch to Page Speed</a>)</h2>
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
<?php if ($badinput) {
?>
<script type="text/javascript">
(function(urls) {
	for (var i = 0; i < urls.length; i++) {
		var s = document.createElement('script');
		s.setAttribute('type', 'text/javascript');
		s.setAttribute('src', urls[i]);
		document.getElementsByTagName('head')[0].appendChild(s);
	}
})(['<?php echo $TimePlotBase?>timeplot-api.js', 'compare.js']);
</script>
<?php } ?>
</body></html>
