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

UserConfig::$header = dirname(__FILE__).'/header.php';
UserConfig::$footer = dirname(__FILE__).'/footer.php';

UserConfig::$rememberMeDefault = true;
