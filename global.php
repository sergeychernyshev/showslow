<?php 
# change it if you want to allow other profiles including your custom profiles
$YSlow2AllowedProfiles = array('ydefault');

# If not false, then should be an array of prefix matches - if one of them matches, URL will be accepted
$limitURLs = false;

# If set to true, drop all query strings. If array, then match prefixes.
$dropQueryStrings = false;

# Custom metrics array
$metrics = array();

# to see if your users are visiting the tool, enable Google Analytics
# (for publicly hosted instances)
$googleAnalyticsProfile = '';

# show Feedback button
$showFeedbackButton = true;

# how old should data be for deletion (in days)
# anything >0 will delete old data
# don't forget to add a cron job to run deleteolddata.php
$oldDataInterval = 60;

# Put description for ShowSlow instance into this variable - it'll be displayed on home page under the header.
$ShowSlowIntro = '<p>Show Slow is an open source tool that helps monitor various website performance metrics over time. It captures the results of <a href="http://developer.yahoo.com/yslow/">YSlow</a> and <a href="http://code.google.com/speed/page-speed/">Page Speed</a> rankings and graphs them, to help you understand how various changes to your site affect its performance.</p>

<p><a href="http://www.showslow.com/">www.ShowSlow.com</a> is a demonstration site that continuously measures the performance of a few reference web pages. It also allows for public metrics reporting.</p>

<p>If you want to make your measurements publicly available on this page, see the instructions in <a href="configure.php">Configuring YSlow / Page Speed</a>. If you want to keep your measurements private, <b><a href="http://code.google.com/p/showslow/source/checkout">download Show Slow</a></b> from the SVN repository and install it on your own server.</p>

<p>You can ask questions and discuss ShowSlow in our group <a href="http://groups.google.com/group/showslow">http://groups.google.com/group/showslow</a> or just leave feedback at <a href="http://showslow.uservoice.com">http://showslow.uservoice.com</a></p>

<table><tr>
<td valign="top"><style>
#twitterbutton {
	margin-right: 0.4em;
	width: 64px;
	height: 23px;
	display: block;
	margin-right: 0.4em;
	background-image: url('.$showslow_base.'/follow.png);
	background-position: 0px 0px;
}
#twitterbutton:hover {
	background-position: 0px -46px;
}
</style><a id="twitterbutton" href="http://twitter.com/showslow" target="_blank" title="follow @showslow on twitter"/></a></td>
<td valign="top">
<iframe src="http://www.facebook.com/plugins/like.php?href=http%253A%252F%252Fwww.showslow.com%252F&amp;layout=standard&amp;show_faces=false&amp;width=450&amp;action=recommend&amp;font&amp;colorscheme=light&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:23px;" allowTransparency="true"></iframe>
</td>
</tr></table>';

$homePageMetaTags = '';

# this enables a form to run a test on WebPageTest.org
$webPageTestBase = 'http://www.webpagetest.org/';
$webPageTestLocations = array(
	'DSL' => 'Dulles, VA (IE7, DSL)',
	'FIOS' => 'Dulles, VA (IE7, FIOS)',
	'Dial' => 'Dulles, VA (IE7, 56Kbps dial-up)',
	'IE8' => 'Dulles, VA (IE8, DSL)',
	'SanJose' => 'San Jose, CA (IE8, Ethernet)',
	'NZ' => 'Wellington, New Zealand (IE7, DSL)',
	'UK' => 'Gloucester, UK (IE7, DSL)'
);
$webPageTestPrivateByDefault = false;
$webPageTestFirstRunOnlyByDefault = false;

# a list of URLs to compare by default. Set to NULL to not send any URLs
# $defaultURLsToCompare = array('http://www.google.com/', 'http://www.yahoo.com/', 'http://www.amazon.com/');
$defaultURLsToCompare = NULL;

# Change this to 'pagespeed' to use it for comparison by default
$defaultRankerToCompare = 'yslow';

# Enabling HAR beacon will allow storing HAR data for URLs and display graphically using HAR viewer
$enableHARBeacon = false;

# HAR Viewer base URL
$HARViewerBase = 'http://www.softwareishard.com/har/viewer/';

# Enable user URL monitoring
$enableMyURLs = false;

# Maximum URLs each user can add to the system to be monitored (false means no limit)
$maxURLsPerUser = false;

# Message to show the user when he riches the maximum
$maxURLsMessage = 'The number of URLs tracked is limited because of load constraints.';

# Privileged users who has no limit on URLs
$noMaxURLsForUsers = array();

# how long should monitoring scripts wait between measurements (in hours).
$monitoringPeriod = 24;

# Facebook connect properties, configure them here:
# http://www.facebook.com/developers/createapp.php
$facebookAPIKey = null;
$facebookSecret = null;

# Smoothing distance (averaging window will be from x-distance to x+distance)
$smoothDistance = 5;

require_once(dirname(__FILE__).'/asset_versions.php');
require_once(dirname(__FILE__).'/svn-assets/asset_functions.php');

# config will override defaults above
require_once(dirname(__FILE__).'/config.php');

function yslowPrettyScore($num) {
	$letter = 'F';

	if ( 90 <= $num )
		$letter = 'A';
	else if ( 80 <= $num )
		$letter = 'B';
	else if ( 70 <= $num )
		$letter = 'C';
	else if ( 60 <= $num )
		$letter = 'D';
	else if ( 50 <= $num )
		$letter = 'E';

	return $letter;
}

function scoreColor($num) {
	$colorSteps = array(
		'EE0000',
		'EE2800',
		'EE4F00',
		'EE7700',
		'EE9F00',
		'EEC600',
		'EEEE00',
		'C6EE00',
		'9FEE00',
		'77EE00',
		'4FEE00',
		'28EE00',
		'00EE00'
	);

	for($i=1; $i<=count($colorSteps); $i++)
	{
		if ($num <= $i*100/count($colorSteps))
		{
			return '#'.$colorSteps[$i-1];
		} 
	}
}

mysql_connect($host, $user, $pass);
mysql_select_db($db);
