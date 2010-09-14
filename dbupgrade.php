<?php
require_once(dirname(__FILE__).'/global.php');
require_once(dirname(__FILE__).'/dbupgrade/dbup.php');

$versions = array();
header('Content-type: text/plain');

// Add new migrations on top, right below this line.

/* version 12
 *
 * timestamps don't need to be updatable
*/
$versions[12]['up'][] = "ALTER TABLE  `yslow2` CHANGE  `timestamp`  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT  'Measurement timestamp'";
$versions[12]['down'][] = "ALTER TABLE  `yslow2` CHANGE  `timestamp`  `timestamp` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT  'Measurement timestamp'";

/* version 11
 *
 * Storing PageTest locations
*/
$versions[11]['up'][] = "ALTER TABLE pagetest ADD location TEXT DEFAULT NULL COMMENT 'Test location'";
$versions[11]['down'][] = "ALTER TABLE pagetest DROP location";

/* version 10
 *
 * Adding PageTest history
 */
$versions[10] = array(
	'up' => "CREATE TABLE `pagetest` (
 `id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique id',
 `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `url_id` BIGINT( 20 ) UNSIGNED NOT NULL COMMENT 'URL id',
 `test_id` varchar(255) NOT NULL COMMENT 'PageTest test id',
 `test_url` BLOB NOT NULL COMMENT 'PageTest result URL to redirect to'
) ENGINE=MyISAM;",
	'down' => 'DROP TABLE pagetest',
);

/* version 9
 *
 * Adding dynaTrace beacon's details
*/
$versions[9]['up'][] = "ALTER TABLE dynatrace ADD details TEXT DEFAULT NULL COMMENT 'Beacon details'";
$versions[9]['down'][] = "ALTER TABLE dynatrace DROP details";

// Add new migrations on top, right below this line.

/* version 8
 *
 * Adding dynaTrace beacon
*/
$versions[8]['up'][] = "ALTER TABLE urls ADD dynatrace_last_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'Last measurement ID for dynaTrace beacon'";
$versions[8]['up'][] = "CREATE TABLE `dynatrace` (
  `id` bigint(20) unsigned NOT NULL auto_increment COMMENT 'Measurement ID',
  `version` varchar(255) default NULL COMMENT 'Version of the format used',
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'Measurement time',
  `url_id` bigint(20) unsigned NOT NULL COMMENT 'URL ID',
  `rank` smallint(5) unsigned NOT NULL COMMENT 'verall Page Rank (1-100)',
  `cache` smallint(5) unsigned default NULL COMMENT 'Page Rank on Caching Best Practices (1-100)',
  `net` smallint(5) unsigned default NULL COMMENT 'Page Rank on Network Requests (1-100)',
  `server` smallint(5) unsigned default NULL COMMENT 'Page Rank on Server-Side Execution Time (1-100)',
  `js` smallint(5) unsigned default NULL COMMENT 'Page Rank on JavaScript executions (1-100)',
  `timetoimpression` bigint(20) unsigned default NULL COMMENT 'Time to First Impression [ms]',
  `timetoonload` bigint(20) unsigned default NULL COMMENT 'Time to onLoad [ms]',
  `timetofullload` bigint(20) unsigned default NULL COMMENT 'Time to Full Page Load [ms]',
  `reqnumber` smallint(6) unsigned default NULL COMMENT '# of Requests [Count]',
  `xhrnumber` smallint(6) unsigned default NULL COMMENT '# of XHR Requests [Count]',
  `pagesize` bigint(20) unsigned default NULL COMMENT 'Total Page Size [bytes]',
  `cachablesize` bigint(20) unsigned default NULL COMMENT 'Total Cachable Size [bytes]',
  `noncachablesize` bigint(20) unsigned default NULL COMMENT 'Total Non-Cachable Size [bytes]',
  `timeonnetwork` bigint(20) unsigned default NULL COMMENT 'Total Time on Network [ms]',
  `timeinjs` bigint(20) unsigned default NULL COMMENT 'Total Time in JavaScript [ms]',
  `timeinrendering` bigint(20) unsigned default NULL COMMENT 'Total Time in Rendering [ms]',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM";

$versions[8]['down'][] = "ALTER TABLE urls DROP dynatrace_last_id";
$versions[8]['down'][] = "DROP TABLE `dynatrace`;";

/* version 7
 *
 * Adding URL creation time to be able to monitor new URLs quickly
*/
$versions[7]['up'][] = "ALTER TABLE urls MODIFY last_update TIMESTAMP NULL DEFAULT NULL";
$versions[7]['up'][] = "ALTER TABLE urls ADD added TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Time when URL was added to the table' AFTER  `url`";

$versions[7]['down'][] = "ALTER TABLE urls DROP added";
$versions[7]['down'][] = "ALTER TABLE urls MODIFY last_update TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL DEFAULT NULL";


/* version 6
 *
 * Adding userbase instance
*/
$versions[6]['up'][] = "CREATE TABLE `u_users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `regtime` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'Time of registration',
  `name` text NOT NULL,
  `username` varchar(25) default NULL,
  `email` varchar(320) default NULL,
  `pass` varchar(40) NOT NULL COMMENT 'Password digest',
  `salt` varchar(13) NOT NULL COMMENT 'Salt',
  `temppass` varchar(13) default NULL COMMENT 'Temporary password used for password recovery',
  `temppasstime` timestamp NULL default NULL COMMENT 'Temporary password generation time',
  `requirespassreset` tinyint(1) NOT NULL default '0' COMMENT 'Flag indicating that user must reset their password before using the site',
  `fb_id` bigint(20) unsigned default NULL COMMENT 'Facebook user ID',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `fb_id` (`fb_id`)
) ENGINE=InnoDB;";
$versions[6]['up'][] = "CREATE TABLE `u_googlefriendconnect` (
  `user_id` int(10) unsigned NOT NULL COMMENT 'User ID',
  `google_id` varchar(255) NOT NULL COMMENT 'Google Friend Connect ID',
  `userpic` text NOT NULL COMMENT 'Google Friend Connect User picture',
  PRIMARY KEY  (`user_id`,`google_id`),
  CONSTRAINT `gfc_user` FOREIGN KEY (`user_id`) REFERENCES `u_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;";
$versions[6]['up'][] = "CREATE TABLE `u_invitation` (
  `code` char(10) NOT NULL COMMENT 'Code',
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'When invitation was created',
  `issuedby` bigint(10) unsigned NOT NULL default '1' COMMENT 'User who issued the invitation. Default is Sergey.',
  `sentto` text COMMENT 'Note about who this invitation was sent to',
  `user` bigint(10) unsigned default NULL COMMENT 'User name',
  PRIMARY KEY  (`code`)
) ENGINE=InnoDB;";
$versions[6]['up'][] = "CREATE TABLE `user_urls` (
  `user_id` int(10) unsigned NOT NULL COMMENT 'User ID',
  `url_id` bigint(20) unsigned NOT NULL COMMENT 'URL ID to measure',
  PRIMARY KEY  (`user_id`,`url_id`)
) ENGINE=MyISAM;";

$versions[6]['down'][] = "DROP TABLE IF EXISTS `user_urls`";
$versions[6]['down'][] = "DROP TABLE IF EXISTS `u_googlefriendconnect`";
$versions[6]['down'][] = "DROP TABLE IF EXISTS `u_invitation`";
$versions[6]['down'][] = "DROP TABLE IF EXISTS `u_users`";

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
