<?
$showslow_root = '/path/to/showslow/root/';
$showslow_base = 'http://www.example.com/showslow/'; # don't forget the trailing slash

# Database connection information
$db = 'showslow';
$user = 'showslow';
$pass = '... database-password ...';
$host = 'localhost';

# change it if you want to allow other profiles including your custom profiles
$YSlow2AllowedProfiles = array('ydefault', 'yslow1');

# URL of timeplot installation
$TimePlotBase = 'http://api.simile-widgets.org/timeplot/1.1/';
#$TimePlotBase = '/timeplot/';

# to see if your users are visiting the tool, enable Google Analytics
# (for publicly hosted instances)
#$googleAnalyticsProfile = '';

# show Feedback button
$showFeedbackButton = true;

# how old should data be for deletion (in days)
# anything >0 will delete old data
# don't forget to add a cron job to run deleteolddata.php
$oldDataInterval = 0;

# Put description for ShowSlow instance into this variable - it'll be displayed on home page under the header.
$ShowSlowIntro = '<p>ShowSlow is open source tool to help monitor various performance metrics over time.</p>

<p>It captures results of <a href="http://developer.yahoo.com/yslow/">YSlow</a> and <a href="http://code.google.com/speed/page-speed/">PageSpeed</a> ranking and graphs them which helps to understand how various changes on the site affect it\'s performance.</p>

<p><a href="http://www.showslow.com/">ShowSlow.com</a> is a demonstration site that measures a few resources and allows public metric reporting.</p>

<p>You can <b><a href="http://code.google.com/p/showslow/source/checkout">download ShowSlow</a></b> from SVN repository and install on your own server.</p>

<p>You can ask questions and discuss ShowSlow in our group <a href="http://groups.google.com/group/showslow">http://groups.google.com/group/showslow</a> or just leave feedback at <a href="http://showslow.uservoice.com">http://showslow.uservoice.com</a></p>
';
