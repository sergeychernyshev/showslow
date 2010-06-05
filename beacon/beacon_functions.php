<?php 
require_once(dirname(dirname(__FILE__)).'/global.php');

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

