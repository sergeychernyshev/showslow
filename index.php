<?
require_once('config.php');
db_connect();
?><html>
<head>
<title>Show Slow</title>
</head>
<body>
<img src="showslow_icon.png" style="float: right"/>
<h1>Show Slow</h1>
<div style="float: right; margin-left: 10px; width: 40%">
<h2>Configuring your YSlow</h2>
<b style="color: red">If you're OK with all your YSlow measurements to be recorded by showslow.com and displayed publicly</b>, just set these two Firefox parameters on <b>about:config</b> page:
<ul>
<li>extensions.firebug.yslow.beaconUrl = <b style="color: blue"><?=$showslow_base?>beacon/</b></li>
<li>extensions.firebug.yslow.optinBeacon = <b style="color: blue">true</b></li>
</ul>
</div>
<h2>Last 100 measurements</h2>
<table>
<?
$query = sprintf("SELECT u, o FROM `showslow`.`yslow` ORDER BY timestamp DESC LIMIT 100");
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

while ($row = mysql_fetch_assoc($result)) {
    ?><tr><td><a href="details/?url=<?=urlencode($row['u'])?>"><?=htmlentities($row['u'])?></td><td style="width: 100px; text-align: right; padding-right:10px"><?=yslowPrettyScore($row['o'])?> (<?=$row['o']?>)</td><td><div style="background-color: silver; width: 100px"><div style="width: <?=$row['o']?>px; height: 0.7em; background-color: blue" title="Current YSlow grade: <?=yslowPrettyScore($row['o'])?> (<?=$row['o']?>)"/></div></td></tr><?
}

mysql_free_result($result);
?>
</table>


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
