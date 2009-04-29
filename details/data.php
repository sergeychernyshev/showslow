<?
require_once('../config.php');
db_connect();

header('Content-type: text/plain');

$query = sprintf("SELECT UNIX_TIMESTAMP(timestamp) as t, w, o, r, numcomps, cdn, expires, gzip, cssattop, jsatbottom, expression, domains, obfuscate, redirects, jstwice, etags
FROM `showslow`.`yslow`
WHERE u = '%s' AND timestamp > DATE_SUB(now(),INTERVAL 3 MONTH)
ORDER BY `u`, timestamp ASC",
mysql_real_escape_string($_GET['url']));

$result = mysql_query($query);

if (!$result) {
        error_log(mysql_error());
}

$data = array();

echo '# Measurements gathered for '.$_GET['url']."\n";

while ($row = mysql_fetch_assoc($result)) {
        echo date('c', $row['t']).','.
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
                $row['etags']."\n";
}
mysql_free_result($result);

