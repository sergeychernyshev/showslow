<?php 
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/users/users.php');

if (!is_null($webPageTestKey) && array_key_exists('url', $_POST))
{
	$url_id = getUrlId($_POST['url']);

	$runtest = $webPageTestBase.'runtest.php?k='.urlencode($webPageTestKey).'&'.($webPageTestExtraParams ? $webPageTestExtraParams.'&' : '').'f=xml&r=yes&url='.urlencode($_POST['url']);
	$location = null;
	$private = false;

	if (array_key_exists('location', $_POST)) {
		$location = $_POST['location'];
		$runtest.='&location='.$location;
	} else {
		header('Location: '.$showslow_base.'#no-pagetest-location');
		exit;
	}

	if (array_key_exists('private', $_POST)) {
		$private = $_POST['private'];
		$runtest.='&private='.$_POST['private'];
	}
	if (array_key_exists('fvonly', $_POST)) {
		$runtest.='&fvonly='.$_POST['fvonly'];
	}

	// fetching locations only when needed
	getPageTestLocations();

	if (!array_key_exists($location, $webPageTestLocationsById))
	{
		// location doesn't exist
		error_log("PageTest Location doesn't exist: $location");
		header('Location: '.$showslow_base.'#pagetest-location-doesn-exist');
		exit;
	} else if ($webPageTestLocationsById[$location]['tests'] > 50) {
		// location is overloaded
		error_log("PageTest Location is overloaded: $location");
		header('Location: '.$showslow_base.'#pagetest-location-overloaded');
		exit;
	}

	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $runtest); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);

	if (empty($output)) {
		$err = curl_error($ch);
		curl_close($ch);
		failWithMessage("API call ($runtest) failed: ".$err);
	}

	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if ($code != 200) {
		curl_close($ch);
		failWithMessage("PageTest didn't accept the request: $code");
	}
	curl_close($ch);

	$xml = new SimpleXMLElement($output);

	if (empty($xml)) {
		failWithMessage("Failed to parse XML response");
	}

	if ($xml->statusCode != 200) {
		failWithMessage("PageTest returned failure status code: ".$xml->statusCode." (".$xml->statusText.")");
	}

	$testId = $xml->data->testId;

	if (!$private || $keepPrivatePageTests) {
		# adding new entry
		$query = sprintf("INSERT INTO pagetest (url_id, test_id, location)
			VALUES ('%d', '%s', '%s')",
			mysql_real_escape_string($url_id),
			mysql_real_escape_string($testId),
			mysql_real_escape_string($location)
		);

		if (!mysql_query($query))
		{
			failWithMessage(mysql_error());
		}

		# updating modification date for the URL
		$query = sprintf("UPDATE urls SET last_update = now() WHERE id = %d",
			mysql_real_escape_string($url_id)
		);
		$result = mysql_query($query);
	}

	$current_user = User::get();
	if (!is_null($current_user)) {
		$current_user->recordActivity(SHOWSLOW_ACTIVITY_PAGETEST_START);
	}

	header('Location: '.$webPageTestBase.'results.php?test='.$testId);
	exit;
}

header('Location: '.$showslow_base);
