<?php 
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/users/users.php');

$TITLE = 'Configuring YSlow / Page Speed';
$SECTION = 'configure';
require_once(dirname(__FILE__).'/header.php');
?>
<p><b style="color: #CC14BE">WARNING! Only use this if you're OK with all your measurements to be recorded by this instance of ShowSlow and displayed at <a href="<?php echo $showslow_base?>"><?php echo $showslow_base?></a><br/>You can also <a href="http://www.showslow.org/Installation_and_configuration">install ShowSlow on your own server</a> to limit the risk.</b></p>

<p>Set these Firefox parameters on <b>about:config</b> page:</p>
<h2>YSlow 2.x</h2>
<ul>
<li>extensions.yslow.beaconUrl = <b style="color: blue"><?php echo $showslow_base?>beacon/yslow/</b></li>
<li>extensions.yslow.beaconInfo = <b style="color: blue">grade</b></li>
<li>extensions.yslow.optinBeacon = <b style="color: blue">true</b></li>
</ul>
<h2>Page Speed</h2>
<p>Page Speed is configured to send metrics to <a href="http://www.showslow.com/">showslow.com</a> by default.</p>
<p>To send metrics to your instance located at <a href="<?php echo $showslow_base?>"><?php echo $showslow_base?></a>, set these Firefox parameters:</p>
<ul>
<li>extensions.PageSpeed.beacon.minimal.url = <b style="color: blue"><?php echo $showslow_base?>beacon/pagespeed/</b></li>
<li>extensions.PageSpeed.beacon.minimal.enabled = <b style="color: blue">true</b></li>
</ul>

<h2>dynaTrace AJAX Edition 2.0 Beta 1</h2>
<p><a href="http://ajax.dynatrace.com/">dynaTrace AJAX Edition</a> is configured to upload send metrics to <a href="http://www.showslow.com/">showslow.com</a> when clicking on the <b><i>Upload your results to showslow.com</i></b> link in the dynaTrace AJAX Performance Report.</p>
<p>The uploaded beacon contains information about the dynaTrace AJAX Ranks which includes overall page rank and rankings for Browser Caching, Network, JavaScript and Server-Side Activities.</p>
<p>As for the Beta 1, the upload URL is not configurable to a different instance than www.showslow.com.</p>

<h2>More metrics</h2>
<p>For more information about different beacons supported by this instance of ShowSlow, see <a href="beacon/">beacons page</a></p>

<h2>Additional documentation</h2>
<p>You can find more detailed documentation on configuring tools to be sending data to Show Slow on our wiki here.</p>
<ul>
	<li><a href="http://www.showslow.org/Tools_configuration">http://www.showslow.org/Tools_configuration</a></li>
</ul>
<?php
require_once(dirname(__FILE__).'/footer.php');
