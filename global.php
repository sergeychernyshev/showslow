<?php 
require_once('config.php');

function yslowPrettyScore($num) {
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

function scoreColor($num) {
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

	for($i=1; $i<=count($colorSteps); $i++)
	{
		if ($num <= $i*100/count($colorSteps))
		{
			return '#'.$colorSteps[$i-1];
		} 
	}
}

mysql_connect($host, $user, $pass);
mysql_select_db($db);
