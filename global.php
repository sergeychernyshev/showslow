<?

$showslow_root = '/path/to/showslow/root/';

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

	return $letter;
}

function db_connect()
{
	$db = 'showslow';
	$user = 'showslow';
	$pass = 'yDrteUvab6WLw6Wj';

	$host = 'localhost';

	mysql_connect($host, $user, $pass);
	mysql_select_db($db);
}
