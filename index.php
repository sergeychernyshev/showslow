<?php 
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/users/users.php');

$SECTION = 'home';
require_once(dirname(__FILE__).'/header.php');

echo $ShowSlowIntro;
?>
<hr size="1"/>
<style>
td { white-space: nowrap; }

.score {
	text-align: right;
	padding: 0 10px 0 10px;
}

.gbox {
	background-color: silver;
	width: 101px;	
}

.url {
	padding-left:10px;
}
</style>
<div style="width: 100%; overflow: hidden">
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
	<td><?php echo htmlentities($row['last_update'])?></td>

<?php if (is_null($row['o'])) {?>
	<td></td><td></td>
<?php }else{?>
	<td class="score"><?php echo yslowPrettyScore($row['o'])?> (<?php echo $row['o']?>)</td>
	<td><div class="gbox" title="Current YSlow grade: <?php echo yslowPrettyScore($row['o'])?> (<?php echo $row['o']?>)"><div style="width: <?php echo $row['o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['o'])?>"/></div></td>
<?php }?>

<?php if (is_null($row['ps_o'])) {?>
	<td></td><td></td>
<?php }else{?>
	<td class="score"><?php echo yslowPrettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)</td>
	<td><div class="gbox" title="Current Page Speed score: <?php echo yslowPrettyScore($row['ps_o'])?> (<?php echo $row['ps_o']?>)"><div style="width: <?php echo $row['ps_o']+1?>px; height: 0.7em; background-color: <?php echo scoreColor($row['ps_o'])?>"/></div></td>
<?php }?>
	<td class="url"><a href="details/?url=<?php echo urlencode($row['url'])?>"><?php echo htmlentities(substr($row['url'], 0, 100))?><?php if (strlen($row['url']) > 100) { ?>...<?php } ?></a></td>
</tr><?php 
}

mysql_free_result($result);
?>
</table>
</div>

<div style="display: none" id="preloader"></div>
<script>
(function(urls) {
	var html = '';
	for (var i = 0; i < urls.length; i++) {
		html = html + '<iframe src="'+urls[i]+'" width="0" height="0"></iframe>\n';
	}
	document.getElementById('preloader').innerHTML = html;
})([
	'<?php echo $showslow_base; ?>ajax/simile-ajax-api.js?bundle=true',
	'<?php echo $showslow_base; ?>timeline/timeline-api.js?bundle=true',
	'<?php echo $showslow_base; ?>timeplot/timeplot-api.js?bundle=true',
	'http://yui.yahooapis.com/combo?2.8.1/build/yahoo/yahoo-min.js&2.8.1/build/event/event-min.js&2.8.1/build/yuiloader/yuiloader-min.js',
	'<?php echo assetURL('details/compare.js'); ?>',
	'<?php echo assetURL('details/details.js'); ?>',
	'<?php echo $showslow_base; ?>ajax/simile-ajax-bundle.js',
	'<?php echo $showslow_base; ?>ajax/scripts/signal.js?1',
	'<?php echo $showslow_base; ?>ajax/styles/graphics.css',
	'<?php echo $showslow_base; ?>timeline/timeline-bundle.js',
	'<?php echo $showslow_base; ?>timeline/timeline-bundle.css',
	'<?php echo $showslow_base; ?>timeline/scripts/l10n/en/timeline.js',
	'<?php echo $showslow_base; ?>timeline/scripts/l10n/en/labellers.js',
	'<?php echo $showslow_base; ?>ajax/scripts/signal.js?2',
	'<?php echo $showslow_base; ?>timeplot/timeplot-bundle.js',
	'<?php echo $showslow_base; ?>details/__history__.html?0',
	'<?php echo $showslow_base; ?>timeplot/timeplot-bundle.js',
	'<?php echo $showslow_base; ?>timeplot/timeplot-bundle.css',
	'<?php echo $showslow_base; ?>timeplot/images/copyright.png',
	'<?php echo $showslow_base; ?>timeplot/images/line_left.png',
	'<?php echo $showslow_base; ?>timeplot/images/line_right.png',
	'<?php echo $showslow_base; ?>timeplot/images/progress-running.gif',
	'<?php echo $showslow_base; ?>ajax/images/message-top-left.png',
	'<?php echo $showslow_base; ?>ajax/images/message-top-right.png',
	'<?php echo $showslow_base; ?>ajax/images/message-left.png',
	'<?php echo $showslow_base; ?>ajax/images/message-right.png',
	'<?php echo $showslow_base; ?>ajax/images/message-bottom-left.png',
	'<?php echo $showslow_base; ?>ajax/images/message-bottom-right.png',
	'http://yui.yahooapis.com/combo?2.8.1/build/assets/skins/sam/skin.css&',
	'http://yui.yahooapis.com/2.8.1/build/assets/skins/sam/sprite.png',
	'http://yui.yahooapis.com/combo?2.8.1/build/dom/dom-min.js&2.8.1/build/dragdrop/dragdrop-min.js&2.8.1/build/animation/animation-min.js&2.8.1/build/connection/connection-min.js&2.8.1/build/container/container-min.js&2.8.1/build/datasource/datasource-min.js&2.8.1/build/event-mouseenter/event-mouseenter-min.js&2.8.1/build/selector/selector-min.js&2.8.1/build/event-delegate/event-delegate-min.js&2.8.1/build/element/element-min.js&2.8.1/build/calendar/calendar-min.js&2.8.1/build/paginator/paginator-min.js&2.8.1/build/datatable/datatable-min.js&'
]);
</script>
<?php
require_once(dirname(__FILE__).'/footer.php');
