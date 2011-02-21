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
} else {
	header('HTTP/1.0 400 Bad Request');

	?><html>
<head>
<title>Bad Request: DOM Monster beacon</title>
<style>
.bookmarklet {
	padding: 4px;
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
<h1>Bad Request: DOM Monster beacon</h1>
<p>This is <a href="http://mir.aculo.us/dom-monster/">DOM Monster</a> beacon entry point.</p>

<h1>Sending data to beacon</h1>
<p><b style="color: red">WARNING! Only use this beacon If you're OK with all your DOM Monster data to be recorded by this instance of ShowSlow and displayed at <a href="<?php echo $showslow_base?>"><?php echo $showslow_base?></a><br/>You can also <a href="http://www.showslow.org/Installation_and_configuration">install ShowSlow on your own server</a> to limit the risk.</b></p>

<p>To send data to this instance, drag <a class="bookmarklet" href="javascript:(function(){SHOWSLOWINSTANCE%20='<?php echo $showslow_base?>';var%20script=document.createElement('script');script.src='<?php echo assetURL('beacon/dommonster/dom-monster/src/dommonster.js')?>?'+Math.floor((+new Date));document.body.appendChild(script);})()">DOM Monster!</a> to your toolmar and then click "send to Show Slow" button when report is shown.</p>


</body></html>
<?php
	exit;
}
?><html><head><script>
	alert('Sucess: data is saved to Show Slow');
</script></head><body></body></html>
