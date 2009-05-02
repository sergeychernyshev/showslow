<?

$showslow_root = '/path/to/showslow/root/';
$showslow_base = 'http://www.example.com/showslow/'; # don't forget the trailing slash

# change it if you want to allow other profiles including your custom profiles
$YSlow2AllowedProfiles = array('ydefault', 'yslow1');

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
	$pass = '... database-password ...';

	$host = 'localhost';

	mysql_connect($host, $user, $pass);
	mysql_select_db($db);
}
