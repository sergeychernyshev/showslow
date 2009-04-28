<?
require_once('config.php');
db_connect();

$query = sprintf("SELECT u, UNIX_TIMESTAMP(timestamp) as t, w, o, r, numcomps, cdn, expires, gzip, cssattop, jsatbottom, expression, domains, obfuscate, redirects, jstwice, etags FROM `showslow`.`yslow` ORDER BY `u`, timestamp ASC");
$result = mysql_query($query);

if (!$result) {
	error_log(mysql_error());
}

$data = array();

while ($row = mysql_fetch_assoc($result)) {
	$url = $row['u'];
	$url_md5 = md5($url);

	if (!array_key_exists($url, $data)) {
		$data[$url] = fopen("$showslow_root/details/data/$url_md5.csv", 'w');
		fwrite($data[$url], "# Measurements gathered for $url\n");

		echo "Writing to $url_md5.csv for $url\n";
	}
	fwrite($data[$url], date('c', $row['t']).','.
		$row['w'].','.
		$row['o'].','.
		$row['r'].','.
		$row['numcomps'].','.
		$row['cdn'].','.
		$row['expires'].','.
		$row['gzip'].','.
		$row['cssattop'].','.
		$row['jsatbottom'].','.
		$row['expression'].','.
		$row['domains'].','.
		$row['obfuscate'].','.
		$row['redirects'].','.
		$row['jstwice'].','.
		$row['etags']."\n"
		);
}
mysql_free_result($result);

foreach ($data as $datafile) {
	fclose($datafile);
}
