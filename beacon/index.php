<?
if (!array_key_exists('w', $_GET) || filter_var($_GET['w'], FILTER_VALIDATE_INT) === false
	|| !array_key_exists('o', $_GET) || filter_var($_GET['o'], FILTER_VALIDATE_INT) === false
	|| !array_key_exists('u', $_GET) || filter_var($_GET['u'], FILTER_VALIDATE_URL) === false
	|| !array_key_exists('r', $_GET) || filter_var($_GET['r'], FILTER_VALIDATE_INT) === false
	|| !array_key_exists('numcomps', $_GET) || filter_var($_GET['numcomps'], FILTER_VALIDATE_INT) === false
	|| !array_key_exists('cdn', $_GET) || filter_var($_GET['cdn'], FILTER_VALIDATE_INT) === false
	|| !array_key_exists('expires', $_GET) || filter_var($_GET['expires'], FILTER_VALIDATE_INT) === false
	|| !array_key_exists('gzip', $_GET) || filter_var($_GET['gzip'], FILTER_VALIDATE_INT) === false
	|| !array_key_exists('cssattop', $_GET) || filter_var($_GET['cssattop'], FILTER_VALIDATE_INT) === false
	|| !array_key_exists('jsatbottom', $_GET) || filter_var($_GET['jsatbottom'], FILTER_VALIDATE_INT) === false
	|| !array_key_exists('expression', $_GET) || filter_var($_GET['expression'], FILTER_VALIDATE_INT) === false
	|| !array_key_exists('domains', $_GET) || filter_var($_GET['domains'], FILTER_VALIDATE_INT) === false
	|| !array_key_exists('obfuscate', $_GET) || filter_var($_GET['obfuscate'], FILTER_VALIDATE_INT) === false
	|| !array_key_exists('redirects', $_GET) || filter_var($_GET['redirects'], FILTER_VALIDATE_INT) === false
	|| !array_key_exists('jstwice', $_GET) || filter_var($_GET['jstwice'], FILTER_VALIDATE_INT) === false
	|| !array_key_exists('etags', $_GET) || filter_var($_GET['etags'], FILTER_VALIDATE_INT) === false
	)
{
	?><html>
<head>
<title>YSlow beacon</title>
</head>
<body>
<h1>YSlow beacon</h1>
<p>This is <a href="http://developer.yahoo.com/yslow/">YSlow</a> beacon entry point.</p>
</body></html>
<?
	return;
}

require_once('../config.php');
db_connect();

$query = sprintf("INSERT INTO `showslow`.`yslow` (
	`ip` ,
	`user_agent` ,
	`w` ,
	`o` ,
	`u` ,
	`r` ,
	`numcomps` ,
	`cdn` ,
	`expires` ,
	`gzip` ,
	`cssattop` ,
	`jsatbottom` ,
	`expression` ,
	`domains` ,
	`obfuscate` ,
	`redirects` ,
	`jstwice` ,
	`etags`
)
VALUES (inet_aton('%s'), '%s', '%d', '%d', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d')",
	mysql_real_escape_string($_SERVER['REMOTE_ADDR']),
	mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']),
	mysql_real_escape_string($_GET['w']),
	mysql_real_escape_string($_GET['o']),
	mysql_real_escape_string($_GET['u']),
	mysql_real_escape_string($_GET['r']),
	mysql_real_escape_string($_GET['numcomps']),
	mysql_real_escape_string($_GET['cdn']),
	mysql_real_escape_string($_GET['expires']),
	mysql_real_escape_string($_GET['gzip']),
	mysql_real_escape_string($_GET['cssattop']),
	mysql_real_escape_string($_GET['jsatbottom']),
	mysql_real_escape_string($_GET['expression']),
	mysql_real_escape_string($_GET['domains']),
	mysql_real_escape_string($_GET['obfuscate']),
	mysql_real_escape_string($_GET['redirects']),
	mysql_real_escape_string($_GET['jstwice']),
	mysql_real_escape_string($_GET['etags'])
);

if (!mysql_query($query))
{
	error_log(mysql_error());
}































