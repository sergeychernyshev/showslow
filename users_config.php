<?php
require_once(dirname(__FILE__).'/global.php');

UserConfig::$mysql_host = $host;
UserConfig::$mysql_db = $db;
UserConfig::$mysql_user = $user;
UserConfig::$mysql_password = $pass;
UserConfig::$mysql_port = $port;

if ($facebookAPIKey) {
	UserConfig::loadModule('facebook');
	new FacebookAuthenticationModule($facebookAPIKey, $facebookSecret);
}

if ($googleFriendConnectSiteID) {
	UserConfig::loadModule('google');
	new GoogleAuthenticationModule($googleFriendConnectSiteID);
}

UserConfig::loadModule('usernamepass');
new UsernamePasswordAuthenticationModule();

// TODO - implement accounts and then switch it to true.
UserConfig::$useAccounts = false;

#UserConfig::$enableInvitations = true;

UserConfig::$supportEmailFrom = $supportEmailFrom;
UserConfig::$supportEmailReplyTo = $supportEmailReplyTo;

UserConfig::$SESSION_SECRET = $sessionSecret;

UserConfig::$admins = $instanceAdmins;
UserConfig::$dont_display_activity_for = $instanceAdmins;

UserConfig::$header = dirname(__FILE__).'/header.php';
UserConfig::$footer = dirname(__FILE__).'/footer.php';

UserConfig::$rememberMeDefault = true;

define('SHOWSLOW_ACTIVITY_ADD_URL', 1);
define('SHOWSLOW_ACTIVITY_PAGETEST_START', 2);
define('SHOWSLOW_ACTIVITY_URL_SEARCH', 3);
// array of activities in the system velue is an array of label and value of activity
UserConfig::$activities[SHOWSLOW_ACTIVITY_ADD_URL] = array('Added URL for monitoring', 5);
UserConfig::$activities[SHOWSLOW_ACTIVITY_PAGETEST_START] = array('Started WebPagetest test', 2);
UserConfig::$activities[SHOWSLOW_ACTIVITY_URL_SEARCH] = array('Searched a URL in the list', 1);

// Features

// Flot support is now enabled using $enableFlot and this feature flag is not used
define('SHOWSLOW_FLOT_SUPPORT', 		1);
new Feature(SHOWSLOW_FLOT_SUPPORT, 'Flot charting library support');
