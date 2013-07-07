<?php 
# Set your timezone to avoid problems with PHP configuration
# List of supported timezones: http://php.net/manual/en/timezones.php
date_default_timezone_set('America/Denver');

# Database connection information
$db = 'showslow';
$user = 'showslow';
$pass = '... database-password ...';
$host = 'localhost';
$port = 3306;

$sessionSecret = '...................................................';

# configuration for email communication
#$supportEmailFrom = 'ShowSlow Administrator <showslow@example.com>';
#$supportEmailReplyTo = 'showslow@example.com';

# PageSpeed Online API key (https://code.google.com/apis/console/#access)
#$pageSpeedOnlineAPIKey = '...';

# Custom metrics supported
#$metrics['bouncerate'] = array(
#	'id' => 1,
#	'title' => 'Bounce Rate (in %)',
#	'color' => 'purple',
#	'description' => 'Bounce rate measured by Google Analytics',
#	'min' => 0,
#	'max' => 100
#);

# URL groups to be displayed on URLs measured tab 
#$URLGroups['showslow'] = array(
#	'title' => "ShowSlow.com pages",
#	'urls' => array(
#		'http://www.showslow.com/'
#	)
#);

# Enabling HAR beacon will allow storing HAR data for URLs and display graphically using HAR viewer
#$enableHARBeacon = true;

# HAR Viewer base URL
#$HARViewerBase = '/harviewer/';

# change it if you want to allow other profiles including your custom profiles
#$YSlow2AllowedProfiles = array('ydefault');

# If not false, then should be an array of prefix matches or PCRE regular expressions
# if one of them matches, URL will be accepted
# for more information, check http://www.php.net/manual/en/book.pcre.php
#$limitURLs = array( 'http://www.yahoo.com/', 'http://www.google.com/', '|mysite.com|i' );

# If set to true, drop all query strings. If array, then match prefixes.
#$dropQueryStrings = true;
#$dropQueryStrings = array( 'http://www.yahoo.com/', 'http://www.google.com/' );

# URL of timeplot installation
#$TimePlotBase = '/timeplot/';

# to see if your users are visiting the tool, enable Google Analytics
# (for publicly hosted instances)
#$googleAnalyticsProfile = '';

# KissMetrics key
#$kissMetricsKey = '';

# show Feedback button
#$showFeedbackButton = true;

# how old should data be for deletion (in days)
# anything >0 will delete old data
# don't forget to add a cron job to run deleteolddata.php
#$oldDataInterval = 60;

# Put description for ShowSlow instance into this variable - it'll be displayed on home page under the header.
/*
$ShowSlowIntro = '<p>Show Slow is an open source tool that helps monitor various website performance metrics over time. It captures the results of <a href="http://www.yslow.org/">YSlow</a>, <a href="https://developers.google.com/speed/pagespeed/">Page Speed Insights</a>, <a href="http://www.webpagetest.org/">WebPageTest</a> and <a href="http://ajax.dynatrace.com/pages/">dynaTrace AJAX Edition</a> rankings and graphs them, to help you understand how various changes to your site affect its performance.</p>

<p><a href="http://www.showslow.com/">www.ShowSlow.com</a> is a demonstration site that continuously measures the performance of a few reference web pages. It also allows for public metrics reporting.</p>

<p>If you want to make your measurements publicly available on this page, see the instructions in <a href="configure.php">Configuring YSlow / Page Speed</a>. If you want to keep your measurements private, <b><a href="http://www.showslow.org/Installation_and_configuration">download and install Show Slow</a></b> on your own server.</p>

<p>You can ask questions and discuss ShowSlow in our group <a href="http://groups.google.com/group/showslow">http://groups.google.com/group/showslow</a> or just open a ticket <a href="https://github.com/sergeychernyshev/showslow/issues">https://github.com/sergeychernyshev/showslow/issues</a></p>
';
*/
