<?php
# auto-detecting showslow_root (path) and showslow_base (URL)
$showslow_root = dirname(__FILE__).'/';

# function to generate URL from current showslow_root
function getShowSlowBase() {
	global $showslow_root;
	$showslow_root = str_replace(DIRECTORY_SEPARATOR, '/', $showslow_root);

	// Chopping of trailing slash which is not supposed to be there in Apache config
	// See: http://httpd.apache.org/docs/2.0/mod/core.html#documentroot
	$docroot = str_replace(DIRECTORY_SEPARATOR, '/', $_SERVER['DOCUMENT_ROOT']);
	if (substr($docroot, -1) == '/') {
		$docroot = substr($docroot, 0, -1);
	}

	$docrootlength = strlen($docroot);

	if (array_key_exists('HTTP_HOST', $_SERVER))
	{
		$host = $_SERVER['HTTP_HOST'];
	}
	else
	{
		$host = php_uname('n');
		// if not running from command line, send warning to the log file
		if (php_sapi_name() !== 'cli') {
			error_log("[ShowSlow config] Warning: Can't determine site's host name, using $host");
		}
	}

	$protocol = 'http';
	if (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == 'HTTPS') {
		$protocol = 'https';
	}

	return $protocol.'://'.$host.substr($showslow_root, $docrootlength);
}

$showslow_base = getShowSlowBase();
$baseAssetURL = $showslow_base; # default base URL for static assets (can be overriden in config)

# change it if you want to allow other profiles including your custom profiles
$YSlow2AllowedProfiles = array('ydefault');

# If not false, then should be an array of prefix matches - if one of them matches, URL will be accepted
$limitURLs = false;

# Track non-http(s) URLs. Disabled by default
$enableNonHTTPURLs = false;

# URL groups to be displayed on URLs measured tab
$URLGroups = array();

# Default URL group to display on the page
$DefaultURLGroup = null;

# Ignore URLs matching the prefix or a regext. If one of them matches, URLs is going to be ignored
# You might want to remove 10.x, 192.168.x and 172.16-32.x if you're testing web sites on a private network.
$ignoreURLs = array(
	'http://0.0.0.0',
	'http://127.0.0.',
	'http://localhost/',
	'http://localhost:',
	'http://10.',
	'http://192.168.',
	'http://172.16.',
	'http://172.17.',
	'http://172.18.',
	'http://172.19.',
	'http://172.20.',
	'http://172.21.',
	'http://172.22.',
	'http://172.23.',
	'http://172.24.',
	'http://172.25.',
	'http://172.26.',
	'http://172.27.',
	'http://172.28.',
	'http://172.29.',
	'http://172.30.',
	'http://172.31.'
);

# Enabling display and collection of data
$enabledMetrics = array(
	'yslow'		=> true,
	'pagespeed'	=> true,
	'dynatrace'	=> true,
	'webpagetest'	=> true,
	'dommonster'	=> true
);

$defaultGraphMetrics = array(
	'yslow' => array('o', 'w', 'r'),
	'pagespeed' => array('o', 'l', 'r', 'w'),
	'dynatrace' => array('rank'),
	'webpagetest' => array('f_aft')
);

# If set to true, drop all query strings. If array, then match prefixes.
$dropQueryStrings = false;

# Custom metrics array
$metrics = array();

# to see if your users are visiting the tool, enable Google Analytics
# (for publicly hosted instances)
$googleAnalyticsProfile = '';

# exclude GA code for matching user agents
$googleAnalyticsExcludeUserAgents = array(
	'/YottaaMonitor/i'
);

# exclude GA code for matching IP addresses
$googleAnalyticsExcludeIPs = array();

# KissMetrics key
$kissMetricsKey = '';

# show Feedback button
$showFeedbackButton = true;

# AddThis profile, set it to enable sharing functions
$addThisProfile = null;

# how old should data be for deletion (in days)
# also used to determine how much data to show on the graph
# anything >0 will delete old data
# don't forget to add a cron job to run deleteolddata.php
$oldDataInterval = 180;

# Enable this if you'd like to clean old yslow beacon details to conserve space
# (beacon details are currently only used for tooltips for latest yslow breakdown)
$cleanOldYSlowBeaconDetails = false;

$homePageMetaTags = '';

# PageSpeed Online API key (https://code.google.com/apis/console/#access)
$pageSpeedOnlineAPIKey = null;

# this enables a form to run a test on WebPageTest.org
$webPageTestKey = null; # must be set to something to not null to enable
$webPageTestBase = 'http://www.webpagetest.org/';
$webPageTestPrivateByDefault = false;
$webPageTestFirstRunOnlyByDefault = false;
$webPageTestExtraParams = '';
$keepPrivatePageTests = false;
$webPageTestLocationsAPCKey = 'showslow_wpt_locations';
$webPageTestLocationsTTL = 300;

# array of tools to show above the graph
$customTools = array();

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

# disable paged browsing (which kills database and is quite useless anyway)
# useful for instances with many urls
$disableUnfilteredURLList = false;

# Facebook connect properties, configure them here:
# http://www.facebook.com/developers/createapp.php
$facebookAPIKey = null;
$facebookSecret = null;

# Google Friend connect site ID
# get it from the URL's "id" parameter on Google Friend Connect admin page for the site:
# http://www.google.com/friendconnect/admin/
$googleFriendConnectSiteID = null;

# Smoothing distance (averaging window will be from x-distance to x+distance)
$smoothDistance = 5;

# configuration for email communication
$supportEmailFrom = 'ShowSlow Administrator <showslow@example.com>';
$supportEmailReplyTo = 'showslow@example.com';

require_once(dirname(__FILE__).'/svn-assets/asset_functions.php');
loadAssetVersionsTSV(dirname(__FILE__).'/asset_versions.tsv');

# Put description for ShowSlow instance into this variable - it'll be displayed on home page under the header.
$ShowSlowIntro = null;

# configuring tabs
$customLists = array();

# additional menu items (url, title are keys for each item) 
$additionalMenuItems = array();

# a list of admin user IDs for authentication into UserBase administration interface
$instanceAdmins = array();

# Enable Flot graphs (default now)
$enableFlot = true;





# ======== PUT ALL THINGS THAT SHOULDN'T BE CONFIGURABLE BELOW THIS LINE ==================

# metric type constants
define('BYTES', 0);
define('PERCENT_GRADE', 1);
define('MS', 2);
define('NUMBER', 3);
define('PERCENTS', 4);

# used for legend (in parenthesis)
# if no label needed like for number, just don't insert it here
$metric_types = array(
	BYTES =>		array( 'legend'	=> 'in bytes',	'units'	=> ' bytes'),
	MS =>			array( 'legend'	=> 'in ms',	'units'	=> ' ms'),
	PERCENT_GRADE =>	array( 'legend'	=> '0-100',	'units'	=> '%'),
	NUMBER =>		array( 'legend'	=> '',		'units'	=> ''),
	PERCENTS =>		array( 'legend'	=> '0-100',	'units'	=> '%')
);

# defaults values for MySQL host and port
$host = 'localhost';
$port = 3306;

# config will override defaults above
require_once(dirname(__FILE__).'/config.php');

# a list of metrics (excluding custom metrics) available to be displayed on the graph
$all_metrics = array(
	'yslow' => array(
		'title' => 'YSlow',
		'url' => 'http://developer.yahoo.com/yslow/',
		'table' => 'yslow2',
		'score_name' => 'grade',
		'score_column' => 'o',
		'metrics' => array(
			'Basic measurements' => array( 
				array( 'Overall rank',					'o',		PERCENT_GRADE),
				array( 'Page Size',					'w',		BYTES),
				array( 'Amount of requests with empty cache',		'r',		NUMBER),
				array( 'Page Load time',				'lt',		MS)
			),
			'Best practices' => array( 
				array( 'Make fewer HTTP requests',		'ynumreq',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#num_http'),
				array( 'Use a Content Delivery Network (CDN)',	'ycdn', 	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#cdn'),
				array( 'Add Expires headers',			'yexpires',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#expires'),
				array( 'Avoid Empty Image src',			'yemptysrc',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#emptysrc'),
				array( 'Compress components with gzip',		'ycompress',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#gzip'),
				array( 'Put CSS at top',			'ycsstop',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#css_top'),
				array( 'Put JavaScript at bottom',		'yjsbottom',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#js_bottom'),
				array( 'Avoid CSS expressions',			'yexpressions',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#css_expressions'),
				array( 'Make JavaScript and CSS external',	'yexternal',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#external'),
				array( 'Reduce DNS lookups',			'ydns',		PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#dns_lookups'),
				array( 'Minify JavaScript and CSS',		'yminify',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#minify'),
				array( 'Avoid URL redirects',			'yredirects',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#redirects'),
				array( 'Remove duplicate JavaScript and CSS',	'ydupes',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#js_dupes'),
				array( 'Configure entity tags (ETags)',		'yetags',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#etags'),
				array( 'Make AJAX cacheable',			'yxhr',		PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#cacheajax'),
				array( 'Use GET for AJAX requests',		'yxhrmethod',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#ajax_get'),
				array( 'Reduce the number of DOM elements',	'ymindom',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#min_dom'),
				array( 'Avoid HTTP 404 (Not Found) error',	'yno404',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#no404'),
				array( 'Reduce cookie size',			'ymincookie',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#cookie_size'),
				array( 'Use cookie-free domains',		'ycookiefree',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#cookie_free'),
				array( 'Avoid AlphaImageLoader filter',		'ynofilter',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#no_filters'),
				array( 'Do not scale images in HTML',		'yimgnoscale',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#no_scale'),
				array( 'Make favicon small and cacheable',	'yfavicon',	PERCENT_GRADE,	'http://developer.yahoo.com/performance/rules.html#favicon')
			)
		)
	),
	'pagespeed' => array(
		'title' => 'Page Speed',
		'url' => 'http://code.google.com/speed/page-speed/',
		'table' => 'pagespeed',
		'score_name' => 'score',
		'score_column' => 'o',
		'metrics' => array(
			'Basic measurements' => array(
				array( 'Page size',				'w',	BYTES),
				array( 'Page load time',			'l',	MS),
				array( 'Transfer size of all resources',	't',	BYTES),
				array( 'Total Requests',			'r',	NUMBER),
				array( 'Overall grade',				'o',	PERCENT_GRADE)
			),
			'Optimize caching' => array(
				array( 'Leverage browser caching',	'pBrowserCache',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/caching.html#LeverageBrowserCaching'),
				array( 'Leverage proxy caching',	'pCacheValid',		PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/caching.html#LeverageProxyCaching'),
				array( 'Remove query strings from static resources',	'pRemoveQuery',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/caching.html#LeverageBrowserCaching'),
				array( 'Specify a Vary: Accept-Encoding header',	'pVaryAE',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/caching.html#LeverageBrowserCaching')
			),
			'Minimize round-trip times' => array(
				array( 'Minimize DNS lookups',				'pMinDns',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/rtt.html#MinimizeDNSLookups'),
				array( 'Minimize redirects',				'pMinRedirect',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/rtt.html#AvoidRedirects'),
				array( 'Avoid bad requests',				'pBadReqs',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/rtt.html#AvoidBadRequests'),
				array( 'Combine external JavaScript',			'pCombineJS',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/rtt.html#CombineExternalJS'),
				array( 'Combine external CSS',				'pCombineCSS',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/rtt.html#CombineExternalCSS'),
				array( 'Combine images using CSS sprites',		'pSprite',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/rtt.html#SpriteImages'),
				array( 'Optimize the order of styles and scripts',	'pCssJsOrder',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/rtt.html#PutStylesBeforeScripts'),
				array( 'Avoid document.write',				'pDocWrite',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/rtt.html#AvoidDocumentWrite'),
				array( 'Avoid CSS @import',				'pCssImport',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/rtt.html#AvoidCssImport'),
				array( 'Prefer asynchronous resources',			'pPreferAsync',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/rtt.html#PreferAsyncResources'),
				array( 'Parallelize downloads across hostnames',	'pParallelDl',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/rtt.html#ParallelizeDownloads')
			),
			'Minimize request overhead' => array(
				array( 'Minimize request size',				'pMinReqSize',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/request.html#MinimizeRequestSize'),
				array( 'Serve static content from a cookieless domain',	'pNoCookie',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/request.html#ServeFromCookielessDomain')
			),
			'Minimize payload size' => array(
				array( 'Enable compression',			'pGzip',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/payload.html#GzipCompression'),
				array( 'Remove unused CSS',			'pUnusedCSS',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/payload.html#RemoveUnusedCSS'),
				array( 'Minify JavaScript',			'pMinifyJS',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/payload.html#MinifyJS'),
				array( 'Minify CSS',				'pMinifyCSS',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/payload.html#MinifyCSS'),
				array( 'Minify HTML',				'pMinifyHTML',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/payload.html#MinifyHTML'),
				array( 'Defer loading of JavaScript',		'pDeferJS',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/payload.html#DeferLoadingJS'),
				array( 'Optimize images',			'pOptImgs',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/payload.html#CompressImages'),
				array( 'Serve scaled images',			'pScaleImgs',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/payload.html#ScaleImages'),
				array( 'Serve resources from a consistent URL',	'pDupeRsrc',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/payload.html#duplicate_resources')
			),
			'Optimize browser rendering' => array(
				array( 'Use efficient CSS selectors',	'pCssSelect',		PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/rendering.html#UseEfficientCSSSelectors'),
				array( 'Put CSS in the document head',	'pCssInHead',		PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/rendering.html#PutCSSInHead'),
				array( 'Specify image dimensions',	'pImgDims',		PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/rendering.html#SpecifyImageDimensions'),
				array( 'Specify a character set early',	'pCharsetEarly',	PERCENT_GRADE,	'http://code.google.com/speed/page-speed/docs/rendering.html#SpecifyCharsetEarly'),
			)
		)
	),
	'dynatrace' => array(
		'title' => 'dynaTrace',
		'url' => 'http://ajax.dynatrace.com/',
		'table' => 'dynatrace',
		'score_name' => 'rank',
		'score_column' => 'rank',
		'metrics' => array(
			'Event times' => array(
				array( 'Time to first impression',	'timetoimpression',	MS),
				array( 'Time to onLoad',		'timetoonload',		MS),
				array( 'Time to full page load',	'timetofullload',	MS)
			),
			'Total time breakdown' => array(
				array( 'Total time on network',		'timeonnetwork',	MS),
				array( 'Total time in JavaScript',	'timeinjs',		MS),
				array( 'Total time in rendering',	'timeinrendering',	MS)
			),
			'Requests and size' => array(
				array( 'Number of requests',		'reqnumber',		NUMBER),
				array( 'Number of XHR requests',	'xhrnumber',		NUMBER),
				array( 'Total page size',		'pagesize',		BYTES),
				array( 'Total cachable size',		'cachablesize',		BYTES),
				array( 'Total non-cachable size',	'noncachablesize',	BYTES)
			),
			'Best practices' => array(
				array( 'Overall rank',			'rank',		PERCENT_GRADE),
				array( 'Caching rank',			'cache',	PERCENT_GRADE,	'https://community.dynatrace.com/community/display/PUB/Best+Practices+on+Browser+Caching'),
				array( 'Network rank',			'net',		PERCENT_GRADE,	'https://community.dynatrace.com/community/display/PUB/Best+Practices+on+Network+Requests+and+Roundtrips'),
				array( 'Server rank',			'server',	PERCENT_GRADE,	'https://community.dynatrace.com/community/display/PUB/Best+Practices+on+Server-Side+Performance+Optimization'),
				array( 'JavaScript rank',		'js',		PERCENT_GRADE,	'https://community.dynatrace.com/community/display/PUB/Best+Practices+on+JavaScript+and+AJAX+Performance')
			)
		)
	),
	'dommonster' => array(
		'title' => 'DOM Monster',
		'description' => '<p>To send data to this instance, drag <a style="padding: 3px 4px; margin: 0 3px; background: #dfdfdf; border: 1px solid gray; color: black; text-decoration: none; font-size: xx-small; font-family: verdana" href="javascript:(function(){SHOWSLOWINSTANCE%20=\''.$showslow_base.'\';var%20script=document.createElement(\'script\');script.src=\''.assetURL('beacon/dommonster/dom-monster/src/dommonster.js').'?\'+Math.floor((+new Date));document.body.appendChild(script);})()">DOM Monster!</a> bookmarklet to your toolbar and click "send to Show Slow" button when report is shown.</p>',
		'url' => 'http://mir.aculo.us/dom-monster/',
		'table' => 'dommonster',
		'metrics' => array(
			'DOM Statistics' => array(
				array( 'Number of elements',		'elements',		NUMBER,
					null, 'levels', array(750, 1500)),
				array( 'Number of DOM nodes',		'nodecount',		NUMBER,
					null, 'levels', array(1500, 3000)),
				array( 'Number of Text nodes',		'textnodes',		NUMBER,
					null, 'levels', array(750, 1500)),
				array( 'Size of Text nodes',		'textnodessize',	BYTES,
					null, 'levels', array(80000, 500000)),
				array( 'Content percentage',		'contentpercent',	PERCENTS,
					null, 'reverselevels', array(25, 50)),
				array( 'Average nesting depth',		'average',		NUMBER,
					null, 'levels', array(8, 15)),
				array( 'Serialized DOM size',		'domsize',		BYTES,
					null, 'levels', array(100*1024, 250*1024)),
				array( 'DOM tree serialization time',	'bodycount',		MS,
					null, 'levels', array(500, 1000))
			)
		)
	),
	'webpagetest' => array(
		'title' => 'WebPageTest',
		'description' => 'Data sent from WebPageTest instance located at <a href="'.$webPageTestBase.'" target="_blank">'.$webPageTestBase.'</a>',
		'url' => 'http://www.webpagetest.org/',
		'table' => 'pagetest',
		'score_name' => 'score',
		'metrics' => array(
			'First view' => array(
				array( 'Load Time',			'f_LoadTime',		MS),
				array( 'Time to first byte',		'f_TTFB',		MS),
				array( 'Time to first render',		'f_render',		MS),
				array( 'Above the fold time',		'f_aft',		MS),
				array( 'Number of DOM elements',	'f_domElements',	NUMBER),
				array( 'Number of connections',		'f_connections',	NUMBER)
			),
			'First view (Document Complete)' => array(
				array( 'Load Time',			'f_docTime',		MS),
				array( 'Number of requests',		'f_requestsDoc',	NUMBER),
				array( 'Bytes In',			'f_bytesInDoc',		BYTES),
			),
			'First view (Fully Loaded)' => array(
				array( 'Load Time',			'f_fullyLoaded',	MS),
				array( 'Number of requests',		'f_requests',		NUMBER),
				array( 'Bytes In',			'f_bytesIn',		BYTES)
			),
			'First view Rankings Scores' => array(
				array( 'Persistent connections (keep-alive)',
									'f_score_keep_alive',	PERCENT_GRADE),
				array( 'GZIP text',			'f_score_gzip',		PERCENT_GRADE),
				array( 'Total size of compressible text',
									'f_gzip_total',		BYTES),
				array( 'Potential text compression savings',
									'f_gzip_savings',	BYTES),
				array( 'Compress Images',		'f_score_compress',	PERCENT_GRADE),
				array( 'Total size of compressible images',
									'f_image_total',	BYTES),
				array( 'Potential image compression savings',
									'f_image_savings',	BYTES),
				array( 'Cache Static',			'f_score_cache',	PERCENT_GRADE),
				array( 'Combine CSS/JS',		'r_score_combine',	PERCENT_GRADE),
				array( 'Use a CDN',			'f_score_cdn',		PERCENT_GRADE),
				array( 'Minify JavaScript',		'f_score_minify',	PERCENT_GRADE),
				array( 'Total size of minifiable text',	'f_minify_total',	BYTES),
				array( 'Potential text minification savings',
									'f_minify_savings',	BYTES),
				array( 'No cookies for static assets',	'f_score_cookies',	PERCENT_GRADE),
				array( 'No Etags',			'f_score_etags',	PERCENT_GRADE)
			),

			'Repeat view' => array(
				array( 'Load Time',			'r_LoadTime',		MS),
				array( 'Time to first byte',		'r_TTFB',		MS),
				array( 'Time to first render',		'r_render',		MS),
				array( 'Above the fold time',		'r_aft',		MS),
				array( 'Number of DOM elements',	'r_domElements',	NUMBER),
				array( 'Number of connections',		'f_connections',	NUMBER)
			),
			'Repeat view (Document Complete)' => array(
				array( 'Load Time',			'r_docTime',		MS),
				array( 'Number of requests',		'r_requestsDoc',	NUMBER),
				array( 'Bytes In',			'r_bytesInDoc',		BYTES)
			),
			'Repeat view (Fully Loaded)' => array(
				array( 'Load Time',			'r_fullyLoaded',	MS),
				array( 'Number of requests',		'r_requests',		NUMBER),
				array( 'Bytes In',			'r_bytesIn',		BYTES)
			),
			'Repeat view Rankings Scores' => array(
				array( 'Persistent connections (keep-alive)',
									'f_score_keep_alive',	PERCENT_GRADE),
				array( 'GZIP text',			'f_score_gzip',		PERCENT_GRADE),
				array( 'Total size of compressible text',
									'f_gzip_total',		BYTES),
				array( 'Potential text compression savings',
									'f_gzip_savings',	BYTES),
				array( 'Compress Images',		'f_score_compress',	PERCENT_GRADE),
				array( 'Total size of compressible images',
									'f_image_total',	BYTES),
				array( 'Potential image compression savings',
									'f_image_savings',	BYTES),
				array( 'Cache Static',			'f_score_cache',	PERCENT_GRADE),
				array( 'Combine CSS/JS',		'r_score_combine',	PERCENT_GRADE),
				array( 'Use a CDN',			'f_score_cdn',		PERCENT_GRADE),
				array( 'Minify JavaScript',		'f_score_minify',	PERCENT_GRADE),
				array( 'Total size of minifiable text',	'f_minify_total',	BYTES),
				array( 'Potential text minification savings',
									'f_minify_savings',	BYTES),
				array( 'No cookies for static assets',	'f_score_cookies',	PERCENT_GRADE),
				array( 'No Etags',			'f_score_etags',	PERCENT_GRADE)
			),
		)
	)
);

function prettyScore($num) {
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

function scoreColorStep($num, $total = 13) {
	for($i=1; $i<=$total; $i++)
	{
		if ($num <= $i*100/$total)
		{
			return $i;
		} 
	}
}

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

$colorStepShades = array(
	'CF0000',
	'CF2200',
	'CF4400',
	'CF6600',
	'CF8800',
	'CFAA00',
	'CDCF00',
	'ABCF00',
	'89CF00',
	'67CF00',
	'45CF00',
	'23CF00',
	'01CF00'
);

function scoreColor($num, $darker = false) {
	global $colorSteps, $colorStepShades;

	$colors = $darker ? $colorStepShades : $colorSteps;
	
	return '#'.$colors[scoreColorStep($num, count($colors))-1];
}

# returns true if URL should be ignored
function isURLIgnored($url) {
	global $ignoreURLs;

	if ($ignoreURLs !== false && is_array($ignoreURLs)) {
		$matched = false;

		foreach ($ignoreURLs as $ignoreString) {
			// checking if string is a regex or just a prefix
			if (preg_match('/^[^a-zA-Z\\\s]/', $ignoreString))
			{
				if (preg_match($ignoreString, $url)) {
					$matched = true;
				}
			} else if (substr($url, 0, strlen($ignoreString)) == $ignoreString) {
				$matched = true;
				break;
			}
		}

		return $matched;
	}

	return false;
}

# returns true if URL is in the limitedURLs array or all URLs are allowed
function isURLAllowed($url) {
	global $limitURLs;

	if ($limitURLs !== false && is_array($limitURLs)) {
		$matched = false;

		foreach ($limitURLs as $limitString) {
			// checking if string is a regex or just a prefix
			if (preg_match('/^[^a-zA-Z\\\s]/', $limitString))
			{
				if (preg_match($limitString, $url)) {
					$matched = true;
				}
			} else if (substr($url, 0, strlen($limitString)) == $limitString) {
				$matched = true;
				break;
			}
		}

		return $matched;
	}
	return true;
}

# returns true if this URLS is not an HTTP URL and should be ignored
function shouldBeIgnoredAsNonHTTP($url) {
	global $enableNonHTTPURLs;

	return (
		!$enableNonHTTPURLs &&
		(substr(strtolower($url), 0, 7) != 'http://' &&	substr(strtolower($url), 0, 8) != 'https://')
	);
}

# returns URL if it's valid and passes all checks or null if not.
# if second parameter is true (default), then 404 error page is shown
#
# used in getUrlId and event beacon (which tests prefix, not URL)
#
# TODO rewrite to use exceptions instead of $outputerror contraption
function validateURL($url, $outputerror = true) {
	$url = filter_var(urldecode(trim($url)), FILTER_VALIDATE_URL);

	if ($url === FALSE) {
		if (!$outputerror) {
			return null;
		}

		header('HTTP/1.0 400 Bad Request');

		?><html>
<head>
<title>Bad Request: ShowSlow beacon</title>
</head>
<body>
<h1>Bad Request: ShowSlow beacon</h1>
<p>Invalid URL submitted.</p>
</body></html>
<?php 
		exit;
	}

	if (shouldBeIgnoredAsNonHTTP($url)) {
		if (!$outputerror) {
			return null;
		}

		header('HTTP/1.0 400 Bad Request');

		?><html>
<head>
<title>Bad Request: ShowSlow beacon</title>
</head>
<body>
<h1>Bad Request: ShowSlow beacon</h1>
<p>This instance of Show Slow only tracks HTTP(S) URLs.</p>
</body></html>
<?php 
		exit;
	}

	if (isURLIgnored($url)) {
		if (!$outputerror) {
			return null;
		}

		header('HTTP/1.0 400 Bad Request');

		?><html>
<head>
<title>Bad Request: ShowSlow beacon</title>
</head>
<body>
<h1>Bad Request: ShowSlow beacon</h1>
<p>This URL matched ignore list for this instance of Show Slow.</p>
</body></html>
<?php 
		exit;
	}

	if (!isURLAllowed($url)) {
		if (!$outputerror) {
			return null;
		}

		header('HTTP/1.0 400 Bad Request');

		?><html>
<head>
<title>Bad Request: ShowSlow beacon</title>
</head>
<body>
<h1>Bad Request: ShowSlow beacon</h1>
<p>URL doesn't match any <a href="http://www.showslow.org/Advanced_configuration_options#Limit_URLs_accepted">URLs allowed</a> by this instance.</p>
</body></html>
<?php 
		exit;
	}

	return $url;
}

$webPageTestLocations = array();
$webPageTestLocationsById = array();
function getPageTestLocations() {
	global $webPageTestLocations, $webPageTestLocationsAPCKey, $webPageTestLocationsTTL,
		$webPageTestLocationsById, $webPageTestBase, $webPageTestKey;

	if (is_null($webPageTestKey)) {
		return;
	}

	if(function_exists('apc_cache_info') && apc_cache_info('user', TRUE) && function_exists('apc_fetch')) {
		$apc_webPageTestLocations = apc_fetch($webPageTestLocationsAPCKey);

		if (is_array($apc_webPageTestLocations)) {
			$webPageTestLocations = $apc_webPageTestLocations;
			foreach ($webPageTestLocations as $loc) {
				$webPageTestLocationsById[$loc['id']] = $loc;
			}
		}
	}

	if (count($webPageTestLocations) > 0) {
		return;
	}

	// Getting a list of locations from WebPageTest
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $webPageTestBase.'getLocations.php?f=xml&k='.urlencode($webPageTestKey));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);

	if (empty($output)) {
		$err = curl_error($ch);
		curl_close($ch);
		failWithMessage("API call ($locationsURL) failed: ".$err);
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
		failWithMessage("PageTest getLocations returned failure status code: ".$xml->statusCode." (".$xml->statusText.")");
	}
	foreach ($xml->data->location as $location) {
		$id = (string)$location->id;

		$loc = array(
			'id' => $id,
			'default' => $location->default == 1 ? true : false,
			'title' => $location->Label.' using '.$location->Browser,
			'tests' => (string)$location->PendingTests->Total
		);

		$webPageTestLocations[] = $loc;
		$webPageTestLocationsById["$id"] = $loc;
	}

	if (function_exists('apc_cache_info') && apc_cache_info('user', TRUE) && function_exists('apc_store')) {
		apc_store($webPageTestLocationsAPCKey, $webPageTestLocations, $webPageTestLocationsTTL);
	}
}

function getUrlId($url, $outputerror = true)
{
	global $dropQueryStrings;

	$url = validateURL($url, $outputerror);

	if (is_null($url)) {
		return null;
	}

	if ($dropQueryStrings) {
		$drop = false;

		if (is_array($dropQueryStrings)) {
			foreach ($dropQueryStrings as $prefix) {
				if (substr($url, 0, strlen($prefix)) == $prefix) {
					$drop = true;
					break;
				}
			}
		} else {
			$drop = true;
		}

		if ($drop) {
			$querypos = strpos($url, '?');

			if ($querypos !== false) {
				$url = substr($url, 0, $querypos);
			}
		}
	}

	# get URL id
	$query = sprintf("SELECT id FROM urls WHERE url = '%s'", mysql_real_escape_string($url));
	$result = mysql_query($query);

	if (!$result) {
		beaconError(mysql_error());
	}

	if (mysql_num_rows($result) == 1) {
		$row = mysql_fetch_assoc($result);
		return $row['id'];
	} else if (mysql_num_rows($result) == 0) {
		// Emulating unique index on a blob with unlimited length by locking the table on write
		// locking only when we're about to insert so we don't block the whole thing on every read

		// locking the table to make sure we pass it only by one concurrent process
		$result = mysql_query('LOCK TABLES urls WRITE');
		if (!$result) {
			beaconError(mysql_error());
		}

		// selecting the URL again to make sure there was no concurrent insert for this URL
		$query = sprintf("SELECT id FROM urls WHERE url = '%s'", mysql_real_escape_string($url));
		$result = mysql_query($query);
		if (!$result) {
			$mysql_err = mysql_error();
			mysql_query('UNLOCK TABLES'); // unlocking the table if in trouble
			beaconError($mysql_err);
		}

		// repeating the check
		if (mysql_num_rows($result) == 1) {
			$row = mysql_fetch_assoc($result);
			$url_id = $row['id'];
		} else if (mysql_num_rows($result) == 0) {
			$query = sprintf("INSERT INTO urls (url) VALUES ('%s')", mysql_real_escape_string($url));
			$result = mysql_query($query);
			if (!$result) {
				$mysql_err = mysql_error();
				mysql_query('UNLOCK TABLES'); // unlocking the table if in trouble
				beaconError($mysql_err);
			}

			$url_id = mysql_insert_id();
		} else if (mysql_num_rows($result) > 1) {
			mysql_query('UNLOCK TABLES'); // unlocking the table if in trouble
			beaconError('more then one entry found for the URL (when lock is aquired)');
		}

		$result = mysql_query('UNLOCK TABLES'); // now concurrent thread can try reading again
		if (!$result) {
			beaconError(mysql_error());
		}

		return $url_id;
	} else {
		beaconError('more then one entry found for the URL');
	}
}

// httpd_build_url replacement from http://www.mediafire.com/?zjry3tynkg5
// added base function feature that allows to pass an array as first parameter
if (!function_exists('http_build_url'))
{
	define('HTTP_URL_REPLACE', 1);	// Replace every part of the first URL when there's one of the second URL
	define('HTTP_URL_JOIN_PATH', 2); 	// Join relative paths
	define('HTTP_URL_JOIN_QUERY', 4);	// Join query strings
	define('HTTP_URL_STRIP_USER', 8);	// Strip any user authentication information
	define('HTTP_URL_STRIP_PASS', 16);	// Strip any password authentication information
	define('HTTP_URL_STRIP_AUTH', 32);	// Strip any authentication information
	define('HTTP_URL_STRIP_PORT', 64);	// Strip explicit port numbers
	define('HTTP_URL_STRIP_PATH', 128);	// Strip complete path
	define('HTTP_URL_STRIP_QUERY', 256);	// Strip query string
	define('HTTP_URL_STRIP_FRAGMENT', 512);	// Strip any fragments (#identifier)
	define('HTTP_URL_STRIP_ALL', 1024);	// Strip anything but scheme and host
	
	// Build an URL
	// The parts of the second URL will be merged into the first according to the flags argument. 
	// 
	// @param mixed	(Part(s) of) an URL in form of a string or associative array like parse_url() returns
	// @param mixed	Same as the first argument
	// @param int	A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
	// @param array	If set, it will be filled with the parts of the composed url like parse_url() would return 
	function http_build_url($url, $parts=array(), $flags=HTTP_URL_REPLACE, &$new_url=false)
	{
		$keys = array('user','pass','port','path','query','fragment');
		
		// HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
		if ($flags & HTTP_URL_STRIP_ALL)
		{
			$flags |= HTTP_URL_STRIP_USER;
			$flags |= HTTP_URL_STRIP_PASS;
			$flags |= HTTP_URL_STRIP_PORT;
			$flags |= HTTP_URL_STRIP_PATH;
			$flags |= HTTP_URL_STRIP_QUERY;
			$flags |= HTTP_URL_STRIP_FRAGMENT;
		}
		// HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
		else if ($flags & HTTP_URL_STRIP_AUTH)
		{
			$flags |= HTTP_URL_STRIP_USER;
			$flags |= HTTP_URL_STRIP_PASS;
		}
		
		// Parse the original URL
		if (is_array($url)) {
			$parse_url = $url;
		} else {
			$parse_url = parse_url($url);
		}
		
		// Scheme and Host are always replaced
		if (isset($parts['scheme']))
			$parse_url['scheme'] = $parts['scheme'];
		if (isset($parts['host']))
			$parse_url['host'] = $parts['host'];
		
		// (If applicable) Replace the original URL with it's new parts
		if ($flags & HTTP_URL_REPLACE)
		{
			foreach ($keys as $key)
			{
				if (isset($parts[$key]))
					$parse_url[$key] = $parts[$key];
			}
		}
		else
		{
			// Join the original URL path with the new path
			if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH))
			{
				if (isset($parse_url['path']))
					$parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
				else
					$parse_url['path'] = $parts['path'];
			}
			
			// Join the original query string with the new query string
			if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY))
			{
				if (isset($parse_url['query']))
					$parse_url['query'] .= '&' . $parts['query'];
				else
					$parse_url['query'] = $parts['query'];
			}
		}

			
		// Strips all the applicable sections of the URL
		// Note: Scheme and Host are never stripped
		foreach ($keys as $key)
		{
			if ($flags & (int)constant('HTTP_URL_STRIP_' . strtoupper($key)))
				unset($parse_url[$key]);
		}
		
		$new_url = $parse_url;
		
		return 
			 ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
			.((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') .'@' : '')
			.((isset($parse_url['host'])) ? $parse_url['host'] : '')
			.((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
			.((isset($parse_url['path'])) ? $parse_url['path'] : '')
			.((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
			.((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '')
		;
	}
}

function resolveRedirects($url) {
	if (function_exists('curl_init')) {
		$ch = curl_init($url);

		curl_setopt_array($ch, array(
			CURLOPT_NOBODY => TRUE,
			CURLOPT_FOLLOWLOCATION => TRUE,
			CURLOPT_MAXREDIRS => 10
		));

		if (curl_exec($ch)) {
			$new_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

			# TODO also test for success code
			# TODO maybe, fix www. when it's missing.

			if ($new_url) {
				$url = $new_url;
			}
		}
	}

	// now, let's fix trailing slash in case of domain-only request
	$urlparts = parse_url($url);
	if (!array_key_exists('path', $urlparts) || $urlparts['path'] == '') {
		$urlparts['path'] = '/';
	}

	$new_url = http_build_url($urlparts);
	if ($new_url) {
		$url = $new_url;
	}

	return $url;
}

function failWithMessage($message)
{
	global $showslow_base;

	error_log("[Page Error] ".$message);
	header('HTTP/1.0 500 ShowSlow Error');
	?>
<head>
<title>500 ShowSlow Error</title>
</head>
<body>
<h1>500 ShowSlow Error</h1>
<p>Something went wrong. If it persists, please report it to <a href="https://github.com/sergeychernyshev/showslow/issues">issue tracker</a>.</a>
<p><?php echo $message?></p>
<p><a href="<?php echo $showslow_base ?>">&lt;&lt; go back to Show Slow</a></p>
</body></html>
<?php
	exit;
}

function beaconError($message)
{
	error_log($message);
	header('HTTP/1.0 500 Beacon Error');
	?>
<head>
<title>500 Beacon Error</title>
</head>
<body>
<h1>500 BeaconError</h1>
<p><?php echo $message?></p>
</body></html>
<?php
	exit;
}

// returns true if GA code needs to be excluded
function excludeGoogleAnalytics() {
	global $googleAnalyticsExcludeUserAgents, $googleAnalyticsExcludeIPs;

	foreach ($googleAnalyticsExcludeUserAgents as $regex) {
		if (preg_match($regex, $_SERVER['HTTP_USER_AGENT']) > 0) {
			return true;
		}
	}

	foreach ($googleAnalyticsExcludeIPs as $ip) {
		if ($ip == $_SERVER["REMOTE_ADDR"]) {
			return true;
		}
	}

	return false;
}

/*
 * Cuts the string to be $maxlength and replaces the last $margin characters with ellipsis
*/
function ellipsis($string, $maxlength, $margin = 2) {
	if (strlen($string) > ($maxlength)) {
		return substr($string, 0, $maxlength - $margin)."...";
	}

	return $string;
}

mysql_connect("$host:$port", $user, $pass);
mysql_select_db($db);

# setting up connection settings to make MySQL communication more strict
$result = mysql_query('SET SESSION SQL_MODE=STRICT_ALL_TABLES');

if (!$result) {
	beaconError(mysql_error());
}
