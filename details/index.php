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
<script src="http://api.simile-widgets.org/timeplot/1.1/timeplot-api.js" type="text/javascript"></script>
<script src="details.js" type="text/javascript"></script>
</head>
<body onload="onLoad('<?=md5($_GET['url'])?>', dataversion);" onresize="onResize();">
<img src="../showslow_icon.png" style="float: right"/>
<h1><a title="Click here to go to home page" href="../">Show Slow</a>: Details for <a href="<?=htmlentities($_GET['url'])?>"><?=htmlentities($_GET['url'])?></a></h1>
<?
require_once('../config.php');
db_connect();

$query = sprintf("SELECT * FROM `showslow`.`yslow` WHERE `u` = '%s' ORDER BY `timestamp` DESC",
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
	?><h2>Current <a href="http://developer.yahoo.com/yslow/">YSlow</a> grade: <?=yslowPrettyScore($row['o'])?> (<i><?=htmlentities($row['o'])?></i>)</h2>
	<script>dataversion = '<?=urlencode($row['timestamp'])?>'; </script>

	<img src="http://chart.apis.google.com/chart?chs=225x125&cht=gom&chd=t:<?=urlencode($row['o'])?>&chl=<?=urlencode(yslowPrettyScore($row['o']).' ('.$row['o'].')')?>" alt="<?=yslowPrettyScore($row['o'])?> (<?=htmlentities($row['o'])?>)" title="Current YSlow grade: <?=yslowPrettyScore($row['o'])?> (<?=htmlentities($row['o'])?>)"/>

	<h2>YSlow grade over time</h2>
	<div id="my-timeplot" style="height: 250px;"></div>

	<div style="fint-size: 0.2em">
	<span style="color: #0000ff">YSlow Grade</span> (0-100) and <span style="color: #D0A825">Page Size</span> (KB)
	</div>

	<h2>Measurements history (<a href="data/<?=md5($_GET['url'])?>.csv">csv</a>)</h2>
	<table border="1" cellpadding="5" cellspacing="0">
	<tr><th>Time</th><th>Page Size</th><th>YSlow grade</th></tr>
<?
	do {
		?><tr>
		<td><?=$row['timestamp']?></td>
		<td align="right"><?=$row['w']?> KB</td>
		<td align="right"><?=yslowPrettyScore($row['o'])?> (<i><?=htmlentities($row['o'])?></i>)</td>
		</tr>
<?
	} while ($row = mysql_fetch_assoc($result));

	mysql_free_result($result);
?>
	</table>
<?
}
?>

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-817839-17");
pageTracker._trackPageview();
} catch(err) {}</script>
</body></html>
