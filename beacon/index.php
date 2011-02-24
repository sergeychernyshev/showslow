<?php 
require_once(dirname(dirname(__FILE__)).'/global.php');
?><html>
<head>
<title>ShowSlow Beacons</title>
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
<body>
<h1>ShowSlow Beacons</h1>
This instance of ShowSlow supports following beacons:
<ul>
<li><a href="yslow/">YSlow beacon</a></li>
<li><a href="pagespeed/">Page Speed beacon</a></li>
<li><a href="events/">Custom events beacon</a></li>
<li><a href="metric/">Custom metrics beacon</a></li>
<li><a href="har/">HAR beacon</a></li>
<li><a href="dynatrace/">dynaTrace AJAX Edition beacon</a></li>
<li><a href="dommonster/">DOM Monster! beacon</a></li>
</ul>

</body></html>

