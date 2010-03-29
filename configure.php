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
<title>Show Slow: Configuring YSlow / PageSpeed</title>
<style type="text/css">
/*margin and padding on body element
  can introduce errors in determining
  element position and are not recommended;
  we turn them off as a foundation for YUI
  CSS treatments. */
body {
	margin:0;
	padding:1em;
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
<a href="http://www.showslow.org/"><img src="showslow_icon.png" style="float: right; margin-left: 1em; border: 0"/></a>
<div style="float: right">powered by <a href="http://www.showslow.org/">showslow</a></div>
<h1>Show Slow</h1>
<?php echo $ShowSlowIntro?>
<div id="showslowlists" class="yui-navset">
    <ul class="yui-nav">
        <li><a href="./"><em>Last 100 measurements</em></a></li>
        <li><a href="all.php"><em>URLs measured</em></a></li>
        <li><a href="details/compare.php<?php echo $compareParams?>"><em>Compare rankings</em></a></li>
        <li class="selected"><a href="configure.php"><em>Configuring YSlow / PageSpeed</em></a></li>
        <li><a href="http://code.google.com/p/showslow/source/checkout"><em>Download ShowSlow</em></a></li>
    </ul> 
    <div class="yui-content">
        <div id="last100">
	</div>
        <div id="urls">
	</div>
        <div id="compare">
	</div>
	<div id="configure">
		<p>
		<p><b style="color: red">WARNING! Only use this if you're OK with all your measurements to be recorded by this instance of ShowSlow and displayed at <a href="<?php echo $showslow_base?>"><?php echo $showslow_base?></a><br/>You can also <a href="http://www.showslow.org/Installation_and_configuration">install ShowSlow on your own server</a> to limit the risk.</b></p>

		<p>Set these two Firefox parameters on <b>about:config</b> page:</p>
		<h2>YSlow 2.x</h2>
		<ul>
		<li>extensions.yslow.beaconUrl = <b style="color: blue"><?php echo $showslow_base?>beacon/yslow/</b></li>
		<li>extensions.yslow.beaconInfo = <b style="color: blue">grade</b></li>
		<li>extensions.yslow.optinBeacon = <b style="color: blue">true</b></li>
		</ul>
		<h2>PageSpeed</h2>
		<ul>
		<li>extensions.PageSpeed.beacon.minimal.url = <b style="color: blue"><?php echo $showslow_base?>beacon/pagespeed/</b></li>
		<li>extensions.PageSpeed.beacon.minimal.enabled = <b style="color: blue">true</b></li>
		</ul>

		<h2>More metrics</h2>
		<p>For more information about different beacons supported by this instance of ShowSlow, see <a href="beacon/">beacons page</a></p>
	</div>
    </div>
</div>

<script type="text/javascript">
    var tabView = new YAHOO.widget.TabView('showslowlists');
    tabView.getTab(0).addListener("click", function() { window.location.href='./'; });
    tabView.getTab(1).addListener("click", function() { window.location.href='all.php'; });
    tabView.getTab(2).addListener("click", function() { window.location.href='details/compare.php<?php echo $compareParams?>'; });
    tabView.getTab(4).addListener("click", function() { window.location.href='http://code.google.com/p/showslow/source/checkout'; });
</script>
</script>
</body></html>
