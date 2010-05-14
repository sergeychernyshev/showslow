<?php 
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/users/users.php');

$SECTION = 'home';
require_once(dirname(__FILE__).'/header.php');

echo $ShowSlowIntro;
?>
<hr size="1"/>
<table>
<tr><th>Timestamp</th><th colspan="2">YSlow grade</th><th colspan="2">Page Speed score</th><th style="padding-left:10px; text-align: left">URL</th></tr>
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
	<td><div style="background-color: silver; width: 101px" title="Current Page Speed score: <?php echo yslowPrettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)"><div style="width: <?php echo $row['ps_o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['ps_o'])?>"/></div></td>
<?php }?>
	<td style="padding-left:10px; overflow: hidden; white-space: nowrap;"><a href="details/?url=<?php echo urlencode($row['url'])?>"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></a></td>
</tr><?php 
}

mysql_free_result($result);
?>
</table>

<?php
require_once(dirname(__FILE__).'/footer.php');
