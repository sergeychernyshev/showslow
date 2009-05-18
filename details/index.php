<?
if (!array_key_exists('url', $_GET) || filter_var($_GET['url'], FILTER_VALIDATE_URL) === false) {
	?><html>
<head>
<title>Error - no URL specified</title>
</head>
<body>
<h1>Error - no URL specified</h1>
<p><a href="../">Go back</a> and pick the URL</p>
</body></html>
<?
	return;
}

?><html>
<head>
<title>Show Slow: Details for <?=htmlentities($_GET['url'])?></title>
<script type="text/javascript" src="http://api.simile-widgets.org/timeplot/1.1/timeplot-api.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/yuiloader/yuiloader-min.js"></script>
<script src="details.js" type="text/javascript"></script>
<style>
.yslow1 {
	color: #55009D;
}

.yslow2 {
	color: #2175D9;
}
</style>
</head>
<body class="yui-skin-sam" onload="onLoad('<?=urlencode($_GET['url'])?>', dataversion);" onresize="onResize();">
<a href="http://code.google.com/p/showslow/"><img src="../showslow_icon.png" style="float: right; margin-left: 1em; border: 0"/></a>
<div style="float: right">powered by <a href="http://code.google.com/p/showslow/">showslow</a></div>
<h1><a title="Click here to go to home page" href="../">Show Slow</a>: Details for <a href="<?=htmlentities($_GET['url'])?>"><?=htmlentities($_GET['url'])?></a></h1>
<?
require_once('../config.php');
db_connect();

$query = sprintf("SELECT y.timestamp, y.w, y.o, y.i FROM yslow2 y, urls WHERE urls.url = '%s' AND y.url_id = urls.id AND y.timestamp > DATE_SUB(now(),INTERVAL 3 MONTH) ORDER BY `timestamp` DESC",
	mysql_real_escape_string($_GET['url'])
);
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$row = mysql_fetch_assoc($result);

if (!$row) {
	?>No data is available yet<?
} else {
?>
	<table cellpadding="15" cellspacing="0"><tr><td valign="top" align="center" style="background: #ddd; border: 1px solid black">
	<h2>Current <a href="http://developer.yahoo.com/yslow/">YSlow</a> grade: <?=yslowPrettyScore($row['o'])?> (<i><?=htmlentities($row['o'])?></i>)</h2>

	<script>dataversion = '<?=urlencode($row['timestamp'])?>'; </script>

	<img src="http://chart.apis.google.com/chart?chs=225x125&cht=gom&chd=t:<?=urlencode($row['o'])?>&chl=<?=urlencode(yslowPrettyScore($row['o']).' ('.$row['o'].')')?>" alt="<?=yslowPrettyScore($row['o'])?> (<?=htmlentities($row['o'])?>)" title="Current YSlow grade: <?=yslowPrettyScore($row['o'])?> (<?=htmlentities($row['o'])?>)" style="padding: 0 0 20px 0; border: 1px solid black; background: white"/>
	</td>
	<td valign="top">
	<h2>Test using <a href="http://www.webpagetest.org/">WebPageTest.org</a></h2>
	Get waterfall diagram, connections diagram with two runs (empty/primed cache). It uses <a href="http://pagetest.wiki.sourceforge.net/">AOL Page Test</a> (IE).
	<form action="http://webpagetest.org/runtest.php" method="POST">
	<input type="hidden" name="url" value="<?=htmlentities($_GET['url'])?>"/>
	<fieldset><legend>Options</legend>
		<input type="radio" value="0" checked="checked" name="fvonly" id="viewBoth"/>First View and Repeat View<br/>
		<input type="radio" value="1" name="fvonly" id="viewFirst"/>First View Only<br/>
		<br/>
		<input type="checkbox" name="private" id="private"/>Keep test results private (don't log them in the test history and use a non-guessable test ID)<br/>
	</fieldset>

	<input type="submit" value="Run Test &gt;&gt;"/>
	</form>
	</td></tr></table>

	<h2 style="clear: both">YSlow grade over time</h2>
	<div id="my-timeplot" style="height: 250px;"></div>

	<div style="fint-size: 0.2em">
	<span style="color: #D0A825">Page Size</span> (in bytes);
	<span style="color: #75CF74">Total Requests</span>;
	<span class="yslow1">YSlow1 Grade</span> (0-100);
	<span class="yslow2">YSlow2 Grade</span> (0-100) 
	</div>

	<h2>Measurements history (<a href="data.php?url=<?=urlencode($_GET['url'])?>">csv</a>)</h3>

	<div id="measurementstable"></div>
<?
}
?>

<? if ($googleAnalyticsProfile) {?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker('<?=$googleAnalyticsProfile?>');
pageTracker._trackPageview();
} catch(err) {}</script>
<?}?>
</body></html>
