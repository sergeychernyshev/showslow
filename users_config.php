<?php
require_once(dirname(__FILE__).'/global.php');
UserConfig::setDB(new mysqli( $host, $user, $pass, $db));

if ($facebookAPIKey) {
	require_once(dirname(__FILE__).'/users/modules/facebook/index.php');
	UserConfig::$modules[] = new FacebookAuthenticationModule($facebookAPIKey, $facebookSecret);
}

if ($googleFriendConnectSiteID) {
	require_once(dirname(__FILE__).'/users/modules/google/index.php');
	UserConfig::$modules[] = new GoogleAuthenticationModule($googleFriendConnectSiteID);
}

require_once(dirname(__FILE__).'/users/modules/usernamepass/index.php');
UserConfig::$modules[] = new UsernamePasswordAuthenticationModule();

// TODO - implement accounts and then switch it to true.
UserConfig::$useAccounts = false;

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
