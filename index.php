<?php 
require_once('global.php');

$compareParams = '';
if (is_array($defaultURLsToCompare)) {
	$compareParams = '?';

	if ($defaultRankerToCompare == 'pagespeed') {
		$compareParams .= 'ranker=pagespeed&';
	}

	$first = true;
	foreach ($defaultURLsToCompare as $url) {
		if ($first) {
			$first = false;	
		}
		else {
			$compareParams.= '&';
		}
		$compareParams.='url[]='.urlencode($url);
	}
}
?><html>
<head>
<title>Show Slow</title>
<style type="text/css">
body {
	margin:0;
	padding:1em;
}

.progress {
	padding: 1em;
	display: none;
}
</style>

<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?2.7.0/build/fonts/fonts-min.css&2.7.0/build/tabview/assets/skins/sam/tabview.css">
<script type="text/javascript" src="http://yui.yahooapis.com/combo?2.7.0/build/yahoo-dom-event/yahoo-dom-event.js&2.7.0/build/element/element-min.js&2.7.0/build/tabview/tabview-min.js"></script> 

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
</head>
<body class="yui-skin-sam">
<a href="http://www.showslow.org/"><img src="<?php echo assetURL('showslow_icon.png')?>" style="float: right; margin-left: 1em; border: 0"/></a>
<div style="float: right">powered by <a href="http://www.showslow.org/">showslow</a></div>
<h1>Show Slow</h1>
<?php echo $ShowSlowIntro?>
<div id="showslowlists" class="yui-navset">
    <ul class="yui-nav">
	<?php if ($enableMyURLs) { ?><li><a href="my.php"><em>Add URL</em></a></li><?php } ?>
        <li class="selected"><a href="#last100"><em>Last 100 measurements</em></a></li>
        <li><a href="all.php"><em>URLs measured</em></a></li>
        <li><a href="details/compare.php<?php echo $compareParams?>"><em>Compare rankings</em></a></li>
        <li><a href="configure.php"><em>Configuring YSlow / PageSpeed</em></a></li>
        <li><a href="http://code.google.com/p/showslow/source/checkout"><em>Download ShowSlow</em></a></li>
    </ul> 
    <div class="yui-content">
        <?php if ($enableMyURLs) { ?><div id="my">
		<div class="progress">Loading...<br/><img src="<?php echo assetURL('progressbar.gif')?>"/></div>
	</div><?php } ?>
        <div id="last100">
		<table>
		<tr><th>Timestamp</th><th colspan="2">YSlow grade</th><th colspan="2">PageSpeed grade</th><th style="padding-left:10px; text-align: left">URL</th></tr>
		<?php 
		$query = sprintf("SELECT url, yslow2.o as o, pagespeed.o as ps_o, last_update FROM urls LEFT JOIN yslow2 on urls.yslow2_last_id = yslow2.id LEFT JOIN pagespeed on urls.pagespeed_last_id = pagespeed.id WHERE last_update IS NOT NULL ORDER BY urls.last_update DESC LIMIT 100");
		$result = mysql_query($query);

		if (!$result) {
			error_log(mysql_error());
		}

		while ($row = mysql_fetch_assoc($result)) {
		?><tr>
			<td style="white-space: nowrap;"><?php echo htmlentities($row['last_update'])?></td>

		<?php if (is_null($row['o'])) {?>
			<td></td><td></td>
		<?php }else{?>
			<td style="text-align: right; padding:0 10px 0 10px; white-space: nowrap;"><?php echo yslowPrettyScore($row['o'])?> (<?php echo $row['o']?>)</td>
			<td><div style="background-color: silver; width: 101px" title="Current YSlow grade: <?php echo yslowPrettyScore($row['o'])?> (<?php echo $row['o']?>)"><div style="width: <?php echo $row['o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['o'])?>"/></div></td>
		<?php }?>

		<?php if (is_null($row['ps_o'])) {?>
			<td></td><td></td>
		<?php }else{?>
			<td style="text-align: right; padding:0 10px 0 10px; white-space: nowrap;"><?php echo yslowPrettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)</td>
			<td><div style="background-color: silver; width: 101px" title="Current YSlow grade: <?php echo yslowPrettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)"><div style="width: <?php echo $row['ps_o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['ps_o'])?>"/></div></td>
		<?php }?>
			<td style="padding-left:10px; overflow: hidden; white-space: nowrap;"><a href="details/?url=<?php echo urlencode($row['url'])?>"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></a></td>
		</tr><?php 
		}

		mysql_free_result($result);
		?>
		</table>
	</div>
        <div id="urls">
		<div class="progress">Loading...<br/><img src="<?php echo assetURL('progressbar.gif')?>"/></div>
	</div>
        <div id="compare">
		<div class="progress">Loading...<br/><img src="<?php echo assetURL('progressbar.gif')?>"/></div>
	</div>
	<div id="configure">
		<div class="progress">Loading...<br/><img src="<?php echo assetURL('progressbar.gif')?>"/></div>
	</div>
	<div id="download">
		<div class="progress">Loading...<br/><img src="<?php echo assetURL('progressbar.gif')?>"/></div>
	</div>
    </div>
</div>

<script type="text/javascript">
    var tabView = new YAHOO.widget.TabView('showslowlists');
    var i = 0;
    <?php if ($enableMyURLs) { ?>tabView.getTab(i++).addListener("click", function() { window.location.href='my.php'; });<?php } ?>
    i++;
    tabView.getTab(i++).addListener("click", function() { window.location.href='all.php'; });
    tabView.getTab(i++).addListener("click", function() { window.location.href='details/compare.php<?php echo $compareParams?>'; });
    tabView.getTab(i++).addListener("click", function() { window.location.href='configure.php'; });
    tabView.getTab(i++).addListener("click", function() { window.location.href='http://code.google.com/p/showslow/source/checkout'; });
    YAHOO.util.Dom.batch(YAHOO.util.Dom.getElementsByClassName('progress'), function(el) {
	YAHOO.util.Dom.setStyle(el, 'display', 'block');
    });
</script>
</body></html>
