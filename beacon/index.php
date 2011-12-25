<?php 
require_once(dirname(dirname(__FILE__)).'/global.php');

$TITLE = 'ShowSlow Beacons';

require_once(dirname(dirname(__FILE__)).'/header.php');
?>
<h2>ShowSlow Beacons</h2>
This instance of ShowSlow supports following beacons:
<ul>
<li><a href="yslow/">YSlow beacon</a></li>
<li><a href="pagespeed/">Page Speed beacon</a></li>
<li><a href="webpagetest/">WebPageTest beacon</a></li>
<li><a href="events/">Custom events beacon</a></li>
<li><a href="metric/">Custom metrics beacon</a></li>
<li><a href="har/">HAR beacon</a></li>
<li><a href="dynatrace/">dynaTrace AJAX Edition beacon</a></li>
<li><a href="dommonster/">DOM Monster! beacon</a></li>
</ul>

<?php
require_once(dirname(dirname(__FILE__)).'/footer.php');
