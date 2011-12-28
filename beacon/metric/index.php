<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/global.php');

function updateUrlAggregates($url_id, $measurement_id)
{
	# updating latest values for the URL
	$query = sprintf("UPDATE urls set metric_last_id = %d, last_update = now() WHERE id = %d",
		mysql_real_escape_string($measurement_id),
		mysql_real_escape_string($url_id)
	);
	$result = mysql_query($query);

	if (!$result) {
		beaconError(mysql_error());
	}
}

if (array_key_exists('metric', $_REQUEST) && array_key_exists($_REQUEST['metric'], $metrics)
	&& array_key_exists('value', $_REQUEST) && is_numeric($_REQUEST['value']) !== false
	&& array_key_exists('u', $_REQUEST)
	)
{
	checkBeaconKey('metric');

	$url_id = getUrlId($_REQUEST['u']);

	if (array_key_exists('timestamp', $_REQUEST) && $_REQUEST['timestamp']) {
		# adding new entry
		$query = sprintf("INSERT INTO metric (timestamp, url_id, metric_id, value) VALUES ('%s', '%d', '%d', '%f')",
			mysql_real_escape_string($_REQUEST['timestamp']),
			mysql_real_escape_string($url_id),
			mysql_real_escape_string($metrics[$_REQUEST['metric']]['id']),
			mysql_real_escape_string($_REQUEST['value'])
		);
	} else {
		# adding new entry
		$query = sprintf("INSERT INTO metric (url_id, metric_id, value) VALUES ('%d', '%d', '%f')",
			mysql_real_escape_string($url_id),
			mysql_real_escape_string($metrics[$_REQUEST['metric']]['id']),
			mysql_real_escape_string($_REQUEST['value'])
		);
	}

	if (!mysql_query($query))
	{
		beaconError(mysql_error());
	}

	updateUrlAggregates($url_id, mysql_insert_id());

	header('HTTP/1.0 204 Data accepted');
	exit;
}

header('HTTP/1.0 400 Bad Request');

$TITLE = 'Bad Request: Custom Metric beacon';

require_once(dirname(dirname(dirname(__FILE__))).'/header.php');
?>
<h2><a href="../">Beacons</a>: Custom Metric</h2>

<p>This is custom metric beacon for ShowSlow.</p>
<p>You can use automated script to publish events using GET call to one of these URLs (for specific metric):</p>
<b><pre>
<?php
if (count($metrics) > 0) {
	foreach ($metrics as $name => $metric) {
		echo $showslow_base?>beacon/metric/?metric=<i style="color: blue"><?php echo $name ?></i>&amp;u=<i>url</i>&amp;value=<i>integer_value</i>&amp;timestamp=<i>mysql_timestamp</i>
<?php
	}
} else {
	echo $showslow_base?>beacon/metric/?metric=<i>metricname</i>&amp;u=<i>url</i>&amp;value=<i>integer_value</i>&amp;timestamp=<i>mysql_timestamp</i><?php
} ?>
</pre></b>
or use form below to manually enter metric values.
<?php
$nometrics = false;
if (count($metrics) == 0) {
	$nometrics = true;

	?><p style="color: red">No custom metrics configured for this instance of ShowSlow.<br/>Add entries to <tt>$metrics</tt> array in configuration file to enable custom metric reporting.</p><?php
}
?>
<h2>Add metric</h2>
<form action="" method="GET">
<table>
<tr valign="top"><td>Time:</td><td><input type="text" name="timestamp" size="25" value="<?php echo date("Y-m-d H:i:s");?>"<?php if ($nometrics) {?> disabled="disabled"<?php }?>/><br/>Time in MySQL <a href="http://dev.mysql.com/doc/refman/5.1/en/datetime.html">timestamp format</a></td></tr>
<tr><td>Metric:</td><td><select name="metric"<?php if ($nometrics) {?> disabled="disabled"<?php }?>>
<option value="">-- pick metric --</option>
<?php
foreach ($metrics as $name => $metric) {
	?><option value="<?php echo $name?>"><?php echo $metric['title']?></option><?php
}
?>
</select></td></tr>
<tr><td>URL:</td><td><input type="text" name="u" value="http://www.example.com/" size="80"<?php if ($nometrics) {?> disabled="disabled"<?php }?>/></td></tr>
<tr><td>Value:</td><td><input type="text" name="value" size="80"<?php if ($nometrics) {?> disabled="disabled"<?php }?>/> (integer value)</td></tr>
<tr><td></td><td><input type="submit" value="add"<?php if ($nometrics) {?> disabled="disabled"<?php }?>/></td></tr>

</table>
</form>

<?php
require_once(dirname(dirname(dirname(__FILE__))).'/footer.php');
