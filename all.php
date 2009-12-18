<?php 
require_once('global.php');
?><html>
<head>
<title>Show Slow</title>
<style type="text/css">
/*margin and padding on body element
  can introduce errors in determining
  element position and are not recommended;
  we turn them off as a foundation for YUI
  CSS treatments. */
body {
	margin:0;
	padding:0;
}
</style>

<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.7.0/build/fonts/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.7.0/build/tabview/assets/skins/sam/tabview.css" />
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>

<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/element/element-min.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/tabview/tabview-min.js"></script>
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
</head>
<body class="yui-skin-sam">
<a href="http://code.google.com/p/showslow/"><img src="showslow_icon.png" style="float: right; margin-left: 1em; border: 0"/></a>
<div style="float: right">powered by <a href="http://code.google.com/p/showslow/">showslow</a></div>
<h1>Show Slow</h1>
<?php echo $ShowSlowIntro?>
<div id="showslowlists" class="yui-navset">
    <ul class="yui-nav">
        <li><a href="./"><em>Last 100 measurements</em></a></li>
        <li class="selected"><a href="#urls"><em>URLs measured</em></a></li>
        <li><a href="configure.php"><em>Configuring YSlow / PageSpeed</em></a></li>
        <li><a href="http://code.google.com/p/showslow/source/checkout"><em>Download ShowSlow</em></a></li>
    </ul> 
    <div class="yui-content">
        <div id="last100">
	</div>
        <div id="urls">
		<table>
		<tr><th colspan="2">YSlow grade</th><th colspan="2">PageSpeed grade</th><th style="padding-left:10px; text-align: left">URL</th></tr>
		<?php 
		$query = sprintf("SELECT DISTINCT url, o, ps_o FROM urls");
		$result = mysql_query($query);

		if (!$result) {
			error_log(mysql_error());
		}

		while ($row = mysql_fetch_assoc($result)) {
		?><tr>

		<?php if (is_null($row['o'])) {?>
			<td></td><td></td>
		<?php }else{?>
			<td style="text-align: right; padding:0 10px 0 10px"><?php echo yslowPrettyScore($row['o'])?> (<?php echo $row['o']?>)</td>
			<td><div style="background-color: silver; width: 101px" title="Current YSlow grade: <?php echo yslowPrettyScore($row['o'])?> (<?php echo $row['o']?>)"><div style="width: <?php echo $row['o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['o'])?>"/></div></td>
		<?php }?>

		<?php if (is_null($row['ps_o'])) {?>
			<td></td><td></td>
		<?php }else{?>
			<td style="text-align: right; padding:0 10px 0 10px"><?php echo yslowPrettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)</td>
			<td><div style="background-color: silver; width: 101px" title="Current YSlow grade: <?php echo yslowPrettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)"><div style="width: <?php echo $row['ps_o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['ps_o'])?>"/></div></td>
		<?php }?>
			<td style="padding-left:10px"><a href="details/?url=<?php echo urlencode($row['url'])?>"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></a></td>
		</tr><?php 
		}

		mysql_free_result($result);
		?>
		</table>
	</div>
	<div id="configure">
	</div>
    </div>
</div>

<script type="text/javascript">
    var tabView = new YAHOO.widget.TabView('showslowlists');
    tabView.getTab(0).addListener("click", function() { window.location.href='./'; });
    tabView.getTab(2).addListener("click", function() { window.location.href='configure.php'; });
    tabView.getTab(3).addListener("click", function() { window.location.href='http://code.google.com/p/showslow/source/checkout'; });
</script>
</script>
<?php if ($googleAnalyticsProfile) {?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker('<?php echo $googleAnalyticsProfile?>');
pageTracker._trackPageview();
} catch(err) {}</script>
<?php }?>
</body></html>
