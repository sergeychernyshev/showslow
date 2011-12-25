<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/global.php');

function updateUrlAggregates($url_id, $measurement_id)
{
	# updating latest values for the URL
	$query = sprintf("UPDATE urls SET dommonster_last_id = %d, last_update = now() WHERE id = %d",
		mysql_real_escape_string($measurement_id),
		mysql_real_escape_string($url_id)
	);

	$result = mysql_query($query);

	if (!$result) {
		beaconError(mysql_error());
	}
}

/*
	Only URL and rank will be mandatory - the rest will be optional
*/
if (array_key_exists('url', $_POST) && array_key_exists('stats', $_POST))
{
	checkBeaconKey('dommonster');

	$url_id = getUrlId(urldecode($_POST['url']));

	$stats = json_decode($_POST['stats'], true);

	# adding new entry
	$query = sprintf("INSERT INTO dommonster (
		version, url_id,
		elements,
		nodecount,
		textnodes,
		textnodessize,
		contentpercent,
		average,
		domsize,
		bodycount
	)
	VALUES (
		'%s', '%d',
		'%d',
		'%d',
		'%d',
		'%d',
		'%f',
		'%f',
		'%d',
		'%d'
	)",
		mysql_real_escape_string(array_key_exists('version', $_POST) ? $_POST['version'] : null),
		mysql_real_escape_string($url_id),
		mysql_real_escape_string(array_key_exists('elements', $stats) ? $stats['elements'] : null),
		mysql_real_escape_string(array_key_exists('nodecount', $stats) ? $stats['nodecount'] : null),
		mysql_real_escape_string(array_key_exists('textnodes', $stats) ? $stats['textnodes'] : null),
		mysql_real_escape_string(array_key_exists('textnodessize', $stats) ? $stats['textnodessize'] : null),
		mysql_real_escape_string(array_key_exists('contentpercent', $stats) ? $stats['contentpercent'] : null),
		mysql_real_escape_string(array_key_exists('average', $stats) ? $stats['average'] : null),
		mysql_real_escape_string(array_key_exists('domsize', $stats) ? $stats['domsize'] : null),
		mysql_real_escape_string(array_key_exists('bodycount', $stats) ? $stats['bodycount']*1000 : null)
	);

	if (!mysql_query($query))
	{
		beaconError(mysql_error());
	}

	updateUrlAggregates($url_id, mysql_insert_id());

	$url = validateURL($_POST['url']);
?><html><head><script>
	if (confirm('Sucess: data is saved to Show Slow (<?php echo $showslow_base ?>)\nWould you like to open the resuls page?')) {
		top.location = '<?php echo $showslow_base ?>details/?url=' + encodeURIComponent('<?php echo $url?>');
	}
</script></head><body></body></html>
<?php
	exit;
} else {
	header('HTTP/1.0 400 Bad Request');

	?><html>
<head>
<title>Bad Request: DOM Monster beacon</title>
<style>
.bookmarklet {
	padding: 3px 4px;
	margin: 0 3px;
	background: #dfdfdf;
	border: 1px solid gray;
	color: black;
	text-decoration: none;
	font-size: xx-small;
	font-family: verdana
}
</style>
</head>
<body>
<h1>DOM Monster beacon</h1>
<p>This is <a href="http://mir.aculo.us/dom-monster/">DOM Monster</a> beacon entry point.</p>

<h1>Sending data to beacon</h1>
<p><b style="color: red">WARNING! Only use this beacon If you're OK with all your DOM Monster data to be recorded by this instance of ShowSlow and displayed at <a href="<?php echo $showslow_base?>"><?php echo $showslow_base?></a><br/>You can also <a href="http://www.showslow.org/Installation_and_configuration">install ShowSlow on your own server</a> to limit the risk.</b></p>

<p>To send data to this instance, drag <a class="bookmarklet" href="javascript:(function(){SHOWSLOWINSTANCE%20='<?php echo $showslow_base?>';var%20script=document.createElement('script');script.src='<?php echo assetURL('beacon/dommonster/dom-monster/src/dommonster.js')?>?'+Math.floor((+new Date));document.body.appendChild(script);})()">DOM Monster!</a> bookmarklet to your toolbar and click "send to Show Slow" button when report is shown.</p>

<h2>Installing on mobile devices</h2>

<h3>Method 1</h3>
<p>Bookmark this link: <a href="#<?php echo urlencode("javascript:(function(){SHOWSLOWINSTANCE ='$showslow_base';var script=document.createElement('script');script.src='".assetURL('beacon/dommonster/dom-monster/src/dommonster.js')."?'+Math.floor((+new Date));document.body.appendChild(script);})()")?>">DOM Monster + ShowSlow</a> and then edit it removing everything up to # symbol.</p>
<h3>Method 2</h3>
<p>
<ol>
<li>Go to this page and bookmark it: <a href="install.php">DOM Monster + ShowSlow</a></li>
<li>Select all code from the field below and copy to your clipboard<br/>
<input style="width: 100%; font-size: 1em" type="text" value="javascript:(function(){SHOWSLOWINSTANCE%20='<?php echo $showslow_base?>';var%20script=document.createElement('script');script.src='<?php echo assetURL('beacon/dommonster/dom-monster/src/dommonster.js')?>?'+Math.floor((+new Date));document.body.appendChild(script);})()"/></li>
<li>Edit bookmarklet you created in step one and paste the code instead of the URL of the page</li>
</ol>
</p>
<hr/>
<p><a href="../">&lt;&lt; back to the list of beacons</a></p>
</body></html>
<?php
	exit;
}
