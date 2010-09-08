<?php 
require_once(dirname(__FILE__).'/global.php');

if (array_key_exists('url', $_REQUEST))
{
	$url_id = getUrlId($_REQUEST['url']);

	$runtest = $webPageTestBase.'runtest.php?f=xml&r=yes&url='.urlencode($_REQUEST['url']);
	if (array_key_exists('location', $_REQUEST)) {
		$runtest.='&location='.$_REQUEST['location'];
	}
	if (array_key_exists('private', $_REQUEST)) {
		$runtest.='&private='.$_REQUEST['private'];
	}
	if (array_key_exists('fvonly', $_REQUEST)) {
		$runtest.='&fvonly='.$_REQUEST['fvonly'];
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
	$userUrl = $xml->data->userUrl;

	# adding new entry
	$query = sprintf("INSERT INTO pagetest (url_id, test_id, test_url) VALUES ('%d', '%s', '%s')",
		mysql_real_escape_string($url_id),
		mysql_real_escape_string($testId),
		mysql_real_escape_string($userUrl)
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

	header('Location: '.$userUrl);
	exit;
}

header('Location: '.$showslow_base);
