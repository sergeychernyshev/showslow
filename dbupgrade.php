<?php
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/dbupgrade/dbup.php');

$versions = array();
header('Content-type: text/plain');

// Add new migrations on top, right below this line.

/* version 5
 *
 * Making last_update NULL unless actually updated
*/

$versions[5]['up'][] = "ALTER TABLE urls MODIFY last_update TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP";
$versions[5]['down'][] = "ALTER TABLE urls MODIFY last_update TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";

/* version 4
 *
 * Combining renamed PageSpeed's metrics
*/
$versions[4]['up'][] = "UPDATE pagespeed SET pOptimizeTheOrderOfStylesAndScripts = pCssJsOrder WHERE pCssJsOrder > pOptimizeTheOrderOfStylesAndScripts";
$versions[4]['up'][] = "ALTER TABLE pagespeed DROP COLUMN pCssJsOrder";

$versions[4]['up'][] = "UPDATE pagespeed SET pPutCssInTheDocumentHead = pCssInHead WHERE pCssInHead > pPutCssInTheDocumentHead";
$versions[4]['up'][] = "ALTER TABLE pagespeed DROP COLUMN pCssInHead";

$versions[4]['up'][] = "UPDATE pagespeed SET pMinimizeRequestSize = pCookieSize WHERE pCookieSize > pMinimizeRequestSize";
$versions[4]['up'][] = "ALTER TABLE pagespeed DROP COLUMN pCookieSize";

$versions[4]['down'][] = "ALTER TABLE pagespeed ADD pCssJsOrder FLOAT UNSIGNED NOT NULL DEFAULT '0'";
$versions[4]['down'][] = "ALTER TABLE pagespeed ADD pCssInHead FLOAT UNSIGNED NOT NULL DEFAULT '0'";
$versions[4]['down'][] = "ALTER TABLE pagespeed ADD pCookieSize FLOAT UNSIGNED NOT NULL DEFAULT '0'";

/* version 3
 *
 * Adding last measurement ID to the urls table for faster retrieval by primary key
 */
$versions[3]['up'][] = "ALTER TABLE urls ADD yslow2_last_id BIGINT(20) UNSIGNED NULL COMMENT 'Last measurement ID for YSlow beacon'";
$versions[3]['up'][] = "ALTER TABLE urls ADD pagespeed_last_id BIGINT(20) UNSIGNED NULL COMMENT 'Last measurement ID for PageSpeed beacon'";
$versions[3]['up'][] = "ALTER TABLE urls DROP w, DROP o, DROP r, DROP ps_w, DROP ps_o, DROP ps_l, DROP ps_r, DROP ps_t";

// migrating data
$versions[3]['up'][] = 'CREATE TEMPORARY TABLE yslow_max_ids SELECT url_id, max(id) as max_id FROM yslow2 GROUP BY url_id';
$versions[3]['up'][] = 'CREATE TEMPORARY TABLE pagespeed_max_ids SELECT url_id, max(id) as max_id FROM pagespeed GROUP BY url_id';
$versions[3]['up'][] = 'UPDATE urls LEFT JOIN yslow_max_ids ON urls.id = yslow_max_ids.url_id LEFT JOIN pagespeed_max_ids ON urls.id = pagespeed_max_ids.url_id SET urls.yslow2_last_id = yslow_max_ids.max_id, urls.pagespeed_last_id = pagespeed_max_ids.max_id';

// downgrading
$versions[3]['down'][] = 'ALTER TABLE urls DROP COLUMN yslow2_last_id';
$versions[3]['down'][] = 'ALTER TABLE urls DROP COLUMN pagespeed_last_id';

// restoring aggregates (no data backporting - lazy)
$versions[3]['down'][] = "ALTER TABLE urls ADD COLUMN w bigint(20) unsigned NOT NULL default '0' COMMENT 'latest size of the page in bytes'";
$versions[3]['down'][] = "ALTER TABLE urls ADD COLUMN o smallint(6) unsigned default NULL COMMENT 'latest overall YSlow grade calculated for this profile'";
$versions[3]['down'][] = "ALTER TABLE urls ADD COLUMN r smallint(6) unsigned NOT NULL default '0' COMMENT 'latest amount of requests with empty cache'";
$versions[3]['down'][] = "ALTER TABLE urls ADD COLUMN ps_w bigint(20) unsigned NOT NULL default '0'";
$versions[3]['down'][] = "ALTER TABLE urls ADD COLUMN ps_o float unsigned default NULL";
$versions[3]['down'][] = "ALTER TABLE urls ADD COLUMN ps_l bigint(20) unsigned NOT NULL default '0'";
$versions[3]['down'][] = "ALTER TABLE urls ADD COLUMN ps_r smallint(6) unsigned NOT NULL default '0'";
$versions[3]['down'][] = "ALTER TABLE urls ADD COLUMN ps_t bigint(20) unsigned NOT NULL default '0'";


/* version 2
 *
 * Adding HAR beacon
 */
$versions[2] = array(
	'up' => "CREATE TABLE  `har` (
 `id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT  'Unique HAR id',
 `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
 `url_id` BIGINT( 20 ) UNSIGNED NOT NULL COMMENT  'URL id',
 `har` LONGBLOB NOT NULL COMMENT  'HAR contents',
 `compressed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT  'Indicates that HAR data is stored compressed'
) ENGINE = MYISAM",
	'down' => 'DROP TABLE har',
);

// version 1
// To get to version 1, use snapshot in tables.sql

try {
	if (!empty($argc) && count($argv) == 2 && $argv[1] == 'down') {
		dbdown(new mysqli( $host, $user, $pass, $db), $versions);
	} else {
		dbup(new mysqli( $host, $user, $pass, $db), $versions);
	}
} catch (Exception $e) {
	echo '[ERR] Caught exception: ',  $e->getMessage(), "\n";
}
