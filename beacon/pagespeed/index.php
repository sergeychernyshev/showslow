<?php
/**
 * This is a data receiver for Google Page Speed extension
 *
 * Documentation for the beacon is available here:
 * http://code.google.com/p/page-speed/wiki/BeaconDocs
 */
require_once(dirname(dirname(dirname(__FILE__))).'/global.php');

function updateUrlAggregates($url_id, $measurement_id)
{
	# updating latest values for the URL
	$query = sprintf("UPDATE urls set pagespeed_last_id = %d, last_update = now(), p_refresh_request = 0 WHERE id = %d",
		mysql_real_escape_string($measurement_id),
		mysql_real_escape_string($url_id)
	);
	$result = mysql_query($query);

	if (!$result) {
		beaconError(mysql_error());
	}
}

if (array_key_exists('u', $_GET)) {
	checkBeaconKey('pagespeed');

	$url_id = getUrlId($_GET['u']);

	$metrics = array(
		'pBadReqs',
		'pBrowserCache',
		'pCacheValid',
		'pCharsetEarly',
		'pCombineCSS',
		'pCombineJS',
		'pCssImport',
		'pCssInHead',
		'pCssJsOrder',
		'pCssSelect',
		'pDeferJS',
		'pDocWrite',
		'pDupeRsrc',
		'pGzip',
		'pImgDims',
		'pMinDns',
		'pMinifyCSS',
		'pMinifyHTML',
		'pMinifyJS',
		'pMinRedirect',
		'pMinReqSize',
		'pNoCookie',
		'pOptImgs',
		'pParallelDl',
		'pPreferAsync',
		'pRemoveQuery',
		'pScaleImgs',
		'pSprite',
		'pUnusedCSS',
		'pVaryAE',
		'pDeferParsingJavaScript',
		'pEnableKeepAlive',
		'pInlineCSS',
		'pInlineJS',
		'pMakeLandingPageRedirectsCacheable'
	);

	// array to store core metrics:
	// w	total size of all resources loaded by the page
	//		htmlResponseBytes
	//		textResponseBytes
	//		cssResponseBytes
	//		imageResponseBytes
	//		javascriptResponseBytes
	//		flashResponseBytes
	//		otherResponseBytes
	// o	score
	// l	-
	// r	numberResources
	// t	-
	$core_metrics = array();

	// processed data will be stored in this array
	$rules = array();

	// indicates if data was successfully gathered and we can store it
	$got_data = false;

	$sdk_version = null;

	if (!is_null($pageSpeedOnlineAPIKey) && array_key_exists('api', $_GET) ) {
		// map of rule => metric relationships
		$rule_metric_map = array(
			'AvoidBadRequests'				=> 'pBadReqs',
			'LeverageBrowserCaching'			=> 'pBrowserCache',
			'SpecifyACacheValidator'			=> 'pCacheValid',
			'SpecifyCharsetEarly'				=> 'pCharsetEarly',
			'CombineExternalCSS'				=> 'pCombineCSS',
			'CombineExternalJavaScript'			=> 'pCombineJS',
			'AvoidCssImport'				=> 'pCssImport',
			'PutCssInTheDocumentHead'			=> 'pCssInHead',
			'OptimizeTheOrderOfStylesAndScripts'		=> 'pCssJsOrder',
			'AvoidDocumentWrite'				=> 'pDocWrite',
			'ServeResourcesFromAConsistentUrl'		=> 'pDupeRsrc',
			'EnableGzipCompression'				=> 'pGzip',
			'SpecifyImageDimensions'			=> 'pImgDims',
			'MinimizeDnsLookups'				=> 'pMinDns',
			'MinifyCss'					=> 'pMinifyCSS',
			'MinifyHTML'					=> 'pMinifyHTML',
			'MinifyJavaScript'				=> 'pMinifyJS',
			'MinimizeRedirects'				=> 'pMinRedirect',
			'MinimizeRequestSize'				=> 'pMinReqSize',
			'ServeStaticContentFromACookielessDomain'	=> 'pNoCookie',
			'OptimizeImages'				=> 'pOptImgs',
			'ParallelizeDownloadsAcrossHostnames'		=> 'pParallelDl',
			'PreferAsyncResources'				=> 'pPreferAsync',
			'RemoveQueryStringsFromStaticResources'		=> 'pRemoveQuery',
			'ServeScaledImages'				=> 'pScaleImgs',
			'SpriteImages'					=> 'pSprite',
			'SpecifyAVaryAcceptEncodingHeader'		=> 'pVaryAE',
			'DeferParsingJavaScript'			=> 'pDeferParsingJavaScript',
			'EnableKeepAlive'				=> 'pEnableKeepAlive',
			'InlineSmallCss'				=> 'pInlineCSS',
			'InlineSmallJavaScript'				=> 'pInlineJS',
			'MakeLandingPageRedirectsCacheable'		=> 'pMakeLandingPageRedirectsCacheable'
		);

		// making an API call
		$apicall = 'https://www.googleapis.com/pagespeedonline/v1/runPagespeed?url='.urlencode(validateURL($_GET['u'])).'&key='.$pageSpeedOnlineAPIKey;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $apicall);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);

		if (empty($output)) {
			$err = curl_error($ch);
			curl_close($ch);
			failWithMessage("API call ($apicall) failed: ".$err);
		}

		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($code != 200) {
			curl_close($ch);
			failWithMessage("API returns error code other then 200: $code");
		}
		curl_close($ch);

		$response = json_decode($output, true);

		if (!array_key_exists('responseCode', $response)
			|| !array_key_exists('kind', $response)
			|| $response['kind'] != 'pagespeedonline#result'
		) {
			failWithMessage("API returns data in the wrong format");
		}

		if ($response['responseCode'] != 200) {
			failWithMessage("URL tested returns bad response code: ".$response['responseCode']);
		}

		// core metrics
		if (!array_key_exists('score', $response)
			|| ($core_metrics['o'] = filter_var($response['score'], FILTER_VALIDATE_INT)) === false
		) {
			failWithMessage("No score returned");
		}

		if (!array_key_exists('pageStats', $response) || !is_array($response['pageStats'])) {
			failWithMessage("No page statistics returned");
		}

		$stats = $response['pageStats'];

		#error_log(var_export($stats, true));

		$h = $t = $c = $i = $j = $f = $o = 0;

		if ((array_key_exists('htmlResponseBytes', $stats)
			&& ($h = filter_var($stats['htmlResponseBytes'], FILTER_VALIDATE_INT)) === false)
			|| (array_key_exists('textResponseBytes', $response['pageStats'])
			&& ($t = filter_var($stats['textResponseBytes'], FILTER_VALIDATE_INT)) === false)
			|| (array_key_exists('cssResponseBytes', $response['pageStats'])
			&& ($c = filter_var($stats['cssResponseBytes'], FILTER_VALIDATE_INT)) === false)
			|| (array_key_exists('imageResponseBytes', $response['pageStats'])
			&& ($i = filter_var($stats['imageResponseBytes'], FILTER_VALIDATE_INT)) === false)
			|| (array_key_exists('javascriptResponseBytes', $response['pageStats'])
			&& ($j = filter_var($stats['javascriptResponseBytes'], FILTER_VALIDATE_INT)) === false)
			|| (array_key_exists('flashResponseBytes', $response['pageStats'])
			&& ($f = filter_var($stats['flashResponseBytes'], FILTER_VALIDATE_INT)) === false)
			|| (array_key_exists('otherResponseBytes', $response['pageStats'])
			&& ($o = filter_var($stats['otherResponseBytes'], FILTER_VALIDATE_INT)) === false)
		) {
			failWithMessage("One of response size statistics is invalid");
		}

		$core_metrics['w'] = $h + $t + $c + $i + $j + $f + $o;

		if (!array_key_exists('numberResources', $stats)
			|| ($core_metrics['r'] = filter_var($stats['numberResources'], FILTER_VALIDATE_INT)) === false
		) {
			failWithMessage("Number of resources is not returned");
		}

		// TODO replace these with reasonable values or make them optional
		$core_metrics['t'] = 0;
		$core_metrics['l'] = 0;

		// rules
		if (!array_key_exists('formattedResults', $response) || !is_array($response['formattedResults'])
			|| !array_key_exists('ruleResults', $response['formattedResults'])
				|| !is_array($response['formattedResults']['ruleResults'])
		) {
			failWithMessage("Data structure is not recognized");
		}

		foreach ($response['formattedResults']['ruleResults'] as $rule => $data) {
			if (!array_key_exists($rule, $rule_metric_map)) {
				error_log('Unrecognized rule: '.$rule.' (skipping)');
				continue;
			}

			$metric = $rule_metric_map[$rule];

			if (!array_key_exists('ruleScore', $data)) {
				error_log('Rule score is not specified: '.$rule.' (skipping)');
				continue;
			}

			$value = filter_var($data['ruleScore'], FILTER_VALIDATE_INT);

			if ($value === false) {
				error_log('Rule score is not an integer: '.$rule.' = '.$data['ruleScore'].' (skipping)');
				continue;
			}

			$rules[$metric] = $value;
		}

		if (!array_key_exists('version', $response) || !is_array($response['version'])
			|| !array_key_exists('major', $response['version'])
			|| $major = filter_var($response['version']['major'], FILTER_VALIDATE_INT) === false
			|| !array_key_exists('minor', $response['version'])
			|| $minor = filter_var($response['version']['minor'], FILTER_VALIDATE_INT) === false
		) {
			failWithMessage("Number of resources is not returned");
		}

		$sdk_version = "$major.$minor";

		$got_data = true;
	} else if (array_key_exists('v', $_GET)
		&& array_key_exists('w', $_GET)
			&& ($core_metrics['w'] = filter_var($_GET['w'], FILTER_VALIDATE_INT)) !== false
		&& array_key_exists('o', $_GET)
			&& ($core_metrics['o'] = filter_var($_GET['o'], FILTER_VALIDATE_FLOAT)) !== false
		&& array_key_exists('l', $_GET)
			&& ($core_metrics['l'] = filter_var($_GET['l'], FILTER_VALIDATE_INT)) !== false
		&& array_key_exists('r', $_GET)
			&& ($core_metrics['r'] = filter_var($_GET['r'], FILTER_VALIDATE_INT)) !== false
		&& array_key_exists('t', $_GET)
			&& ($core_metrics['t'] = filter_var($_GET['t'], FILTER_VALIDATE_INT)) !== false
		)
	{
		$sdk_version = $_GET['v'];

		// list of old metric names that should still be suported
		$metric_renames = array(
			'pSpecifyCharsetEarly'				=> 'pCharsetEarly',
			'pProxyCache'					=> 'pCacheValid',
			'pPutCssInTheDocumentHead'			=> 'pCssInHead',
			'pOptimizeTheOrderOfStylesAndScripts'		=> 'pCssJsOrder',
			'pMinimizeRequestSize'				=> 'pMinReqSize',
			'pParallelizeDownloadsAcrossHostnames'		=> 'pParallelDl',
			'pServeStaticContentFromACookielessDomain'	=> 'pNoCookie',
			'pAvoidBadRequests'				=> 'pBadReqs',
			'pLeverageBrowserCaching'			=> 'pBrowserCache',
			'pRemoveQueryStringsFromStaticResources'	=> 'pRemoveQuery',
			'pServeScaledImages'				=> 'pScaleImgs',
			'pSpecifyACacheValidator'			=> 'pCacheValid',
			'pSpecifyAVaryAcceptEncodingHeader'		=> 'pVaryAE',
			'pSpecifyImageDimensions' 			=> 'pImgDims'
		);

		foreach ($metrics as $metric) {
			$param = $metric;

			foreach (array_reverse($metric_renames) as $from => $to) {
				// if legacy parameter name is sent, use it to get the value
				if ($metric == $to
					&& !array_key_exists($metric, $_GET)
					&& array_key_exists($from, $_GET))
				{
					$param = $from;
				}
			}

			// if value is passed and it's a float number, store it
			if (array_key_exists($param, $_GET)) {
				$value = filter_var($_GET[$param], FILTER_VALIDATE_FLOAT);
				if ($value !== false) {
					$rules[$metric] = $value;
				}
			}
		}

		$got_data = true;
	}

	if ($got_data) {
		$values = array();

		foreach ($rules as $metric => $value) {
			$names[] = $metric;
			$values[] = "'".mysql_real_escape_string($value)."'";
		}

		# adding new entry
		$query = sprintf("INSERT INTO pagespeed (
			`ip` , `user_agent` , `url_id` , `v`,
			`w` , `o` , `l`, `r` , `t`,
			%s
		)
		VALUES (inet_aton('%s'), '%s', '%d', '%s',
			'%d', '%f', '%d', '%d', '%d',
			%s
		)",
			implode(', ', $names),

			mysql_real_escape_string($_SERVER['REMOTE_ADDR']),
			mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']),
			mysql_real_escape_string($url_id),
			mysql_real_escape_string($sdk_version),

			mysql_real_escape_string($core_metrics['w']),
			mysql_real_escape_string($core_metrics['o']),
			mysql_real_escape_string($core_metrics['l']),
			mysql_real_escape_string($core_metrics['r']),
			mysql_real_escape_string($core_metrics['t']),

			implode(', ', $values)
		);

		if (!mysql_query($query))
		{
			beaconError(mysql_error());
		}

		updateUrlAggregates($url_id, mysql_insert_id());

		header('HTTP/1.0 204 Data accepted');
		exit;
	}
}

header('HTTP/1.0 400 Bad Request');

$TITLE = 'Bad Request: Page Speed beacon';

require_once(dirname(dirname(dirname(__FILE__))).'/header.php');
?>
<h2><a href="../">Beacons</a>: Page Speed Insights</h2>

<p>This is <a target="_blank" href="https://developers.google.com/speed/pagespeed/">Page Speed Insights</a> beacon entry point.</p>

<h2>Configure your Page Speed Extension</h2>
<p><b style="color: red">WARNING! Only use this beacon If you're OK with all your Page Speed data to be recorded by this instance of ShowSlow and displayed at <a href="<?php echo $showslow_base?>"><?php echo $showslow_base?></a><br/>You can also <a target="_blank" href="http://www.showslow.org/Installation_and_configuration">install ShowSlow on your own server</a> to limit the risk.</b></p>

Set these two Firefox parameters on <b>about:config</b> page:</p>

<ul>
<li>extensions.PageSpeed.beacon.minimal.url = <b style="color: blue"><?php echo $showslow_base?>beacon/pagespeed/</b></li>
<li>extensions.PageSpeed.beacon.minimal.enabled = <b style="color: blue">true</b></li>
</ul>

<h2>Configuring Page Speed Insights API</h2>
Another alternative to using browser extension is to use <a target="_blank" href="https://developers.google.com/speed/docs/insights/v1/getting_started">Google Page Speed Insights API</a>

<h3>Getting the key</h3>
Get your Google Web Services <a target="_blank" href="https://code.google.com/apis/console/b/0/#access">API key</a>

Open your config.php file and set $pageSpeedOnlineAPIKey variable.

<pre>
$pageSpeedOnlineAPIKey = '<b style="color: blue">your-code-goes-here</b>';
</pre>

<?php if (!is_null($pageSpeedOnlineAPIKey)) { ?>
<h3>Running the tests</h3>
<p>To send API calls and import metrics into ShowSlow, just use your favorite tool to open beacon URL:</p>
<p>
<b><?php echo $showslow_base ?>/beacon/pagespeed/?api&u=<span style="color: red">url-you-are-testing</span></b>
</p>

<p>You can do that periodically using a cron-job, but keep in mind API call limits on the API.</p>
<?php }

require_once(dirname(dirname(dirname(__FILE__))).'/footer.php');
