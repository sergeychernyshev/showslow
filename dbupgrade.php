<?php
/*
 * Copy this script to the folder above and populate $versions array with your migrations
 * For more info see: http://www.dbupgrade.org/Main_Page#Migrations_($versions_array)
 *
 * Note: this script should be versioned in your code repository so it always reflects current code's
 *       requirements for the database structure.
*/
require_once(dirname(__FILE__).'/dbupgrade/lib.php');

$versions = array();
// Add new migrations on top, right below this line.

/* -------------------------------------------------------------------------------------------------------
 * VERSION 30
 * Switching urls table to INNODB engine for better performance
*/
$versions[30]['up'][] = 'ALTER TABLE urls ENGINE = INNODB';
$versions[30]['down'][] = 'ALTER TABLE urls ENGINE = MyISAM';

/* -------------------------------------------------------------------------------------------------------
 * VERSION 29
 * Switching to INNODB engine for better performance
*/
$versions[29]['up'][] = 'ALTER TABLE user_urls ENGINE = INNODB';
$versions[29]['down'][] = 'ALTER TABLE user_urls ENGINE = MyISAM';

/* -------------------------------------------------------------------------------------------------------
 * VERSION 28
 * Adding more WebPageTest fields
*/
$versions[28]['up'][] = "ALTER TABLE `pagetest`
DROP test_url,
ADD `version` VARCHAR( 255 ) NULL COMMENT 'WPT version' AFTER `url_id`,
ADD `r_aft` MEDIUMINT(3) UNSIGNED NULL COMMENT '[first view] Above The Fold Time (ms)' AFTER r_TTFB,
ADD `f_aft` MEDIUMINT(3) UNSIGNED NULL COMMENT '[repeat view] Above The Fold Time (ms)' AFTER r_TTFB,
ADD `r_domElements` SMALLINT(2) UNSIGNED NULL COMMENT '[repeat view] Number of DOM Elements' AFTER r_render,
ADD `f_domElements` SMALLINT(2) UNSIGNED NULL COMMENT '[first view] Number of DOM Elements' AFTER r_render,
ADD `r_connections` SMALLINT(2) UNSIGNED NULL COMMENT '[repeat view] Number of connections' AFTER r_requestsDoc,
ADD `f_connections` SMALLINT(2) UNSIGNED NULL COMMENT '[first view] Number of connections' AFTER r_requestsDoc,
ADD f_score_cache TINYINT(1) UNSIGNED NULL COMMENT 'Cache Static',
ADD r_score_cache TINYINT(1) UNSIGNED NULL COMMENT 'Cache Static',
ADD f_score_cdn TINYINT(1) UNSIGNED NULL COMMENT 'Use a CD',
ADD r_score_cdn TINYINT(1) UNSIGNED NULL COMMENT 'Use a CD',
ADD f_score_gzip TINYINT(1) UNSIGNED NULL COMMENT 'GZIP text',
ADD r_score_gzip TINYINT(1) UNSIGNED NULL COMMENT 'GZIP text',
ADD f_score_cookies TINYINT(1) UNSIGNED NULL COMMENT 'Cookies',
ADD r_score_cookies TINYINT(1) UNSIGNED NULL COMMENT 'Cookies',
ADD f_score_keep_alive TINYINT(1) UNSIGNED NULL COMMENT 'Persistent connections (keep-alive)',
ADD r_score_keep_alive TINYINT(1) UNSIGNED NULL COMMENT 'Persistent connections (keep-alive)',
ADD f_score_minify TINYINT(1) UNSIGNED NULL COMMENT 'Minify JavaScript',
ADD r_score_minify TINYINT(1) UNSIGNED NULL COMMENT 'Minify JavaScript',
ADD f_score_combine TINYINT(1) UNSIGNED NULL COMMENT 'Combine CSS/JS',
ADD r_score_combine TINYINT(1) UNSIGNED NULL COMMENT 'Combine CSS/JS',
ADD f_score_compress TINYINT(1) UNSIGNED NULL COMMENT 'Compress Images',
ADD r_score_compress TINYINT(1) UNSIGNED NULL COMMENT 'Compress Images',
ADD f_score_etags TINYINT(1) UNSIGNED NULL COMMENT 'No Etags',
ADD r_score_etags TINYINT(1) UNSIGNED NULL COMMENT 'No Etags',
ADD f_gzip_total MEDIUMINT(3) UNSIGNED NULL COMMENT 'Total size of compressible text',
ADD r_gzip_total MEDIUMINT(3) UNSIGNED NULL COMMENT 'Total size of compressible text',
ADD f_gzip_savings MEDIUMINT(3) UNSIGNED NULL COMMENT 'Potential text compression savings',
ADD r_gzip_savings MEDIUMINT(3) UNSIGNED NULL COMMENT 'Potential text compression savings',
ADD f_minify_total MEDIUMINT(3) UNSIGNED NULL COMMENT 'Total size of minifiable text',
ADD r_minify_total MEDIUMINT(3) UNSIGNED NULL COMMENT 'Total size of minifiable text',
ADD f_minify_savings MEDIUMINT(3) UNSIGNED NULL COMMENT 'Potential text minification savings',
ADD r_minify_savings MEDIUMINT(3) UNSIGNED NULL COMMENT 'Potential text minification savings',
ADD f_image_total MEDIUMINT(3) UNSIGNED NULL COMMENT 'Total size of compressible images',
ADD r_image_total MEDIUMINT(3) UNSIGNED NULL COMMENT 'Total size of compressible images',
ADD f_image_savings MEDIUMINT(3) UNSIGNED NULL COMMENT 'Potential image compression savings',
ADD r_image_savings MEDIUMINT(3) UNSIGNED NULL COMMENT 'Potential image compression savings',
ADD UNIQUE (`test_id`)
";

$versions[28]['up'][] = "ALTER TABLE urls
ADD pagetest_last_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'Last measurement ID for WebPageTest beacon' AFTER har_last_id,
ADD pagetest_refresh_request TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT 0 COMMENT  'Set it to one when WebPageTest score needs refreshing' AFTER dt_refresh_request
";

$versions[28]['down'][] = "ALTER TABLE urls
DROP pagetest_last_id,
DROP pagetest_refresh_request";
$versions[28]['down'][] = "ALTER TABLE `pagetest`
DROP `version`,
DROP f_aft,
DROP r_aft,
DROP f_connections,
DROP r_connections,
DROP f_domElements,
DROP r_domElements,
DROP f_score_cache,
DROP r_score_cache,
DROP f_score_cdn,
DROP r_score_cdn,
DROP f_score_gzip,
DROP r_score_gzip,
DROP f_score_cookies,
DROP r_score_cookies,
DROP f_score_keep_alive,
DROP r_score_keep_alive,
DROP f_score_minify,
DROP r_score_minify,
DROP f_score_combine,
DROP r_score_combine,
DROP f_score_compress,
DROP r_score_compress,
DROP f_score_etags,
DROP r_score_etags,
DROP f_gzip_total,
DROP r_gzip_total,
DROP f_gzip_savings,
DROP r_gzip_savings,
DROP f_minify_total,
DROP r_minify_total,
DROP f_minify_savings,
DROP r_minify_savings,
DROP f_image_total,
DROP r_image_total,
DROP f_image_savings,
DROP r_image_savings,
ADD `test_url` BLOB NOT NULL COMMENT 'PageTest result URL to redirect to' AFTER test_id,
DROP INDEX (`test_id`)
";

/* -------------------------------------------------------------------------------------------------------
 * VERSION 27
 * Making PageSpeed metrics optional in case some rules didn't run or didn't produce valid result
*/
$versions[27]['up'][] = "ALTER TABLE `pagespeed`
MODIFY `pMinifyCSS` FLOAT UNSIGNED NULL,
MODIFY `pMinifyJS` FLOAT UNSIGNED NULL,
MODIFY `pOptImgs` FLOAT UNSIGNED NULL,
MODIFY `pImgDims` FLOAT UNSIGNED NULL,
MODIFY `pCombineJS` FLOAT UNSIGNED NULL,
MODIFY `pCombineCSS` FLOAT UNSIGNED NULL,
MODIFY `pBrowserCache` FLOAT UNSIGNED NULL,
MODIFY `pCacheValid` FLOAT UNSIGNED NULL,
MODIFY `pNoCookie` FLOAT UNSIGNED NULL,
MODIFY `pParallelDl` FLOAT UNSIGNED NULL,
MODIFY `pCssSelect` FLOAT UNSIGNED NULL,
MODIFY `pDeferJS` FLOAT UNSIGNED NULL,
MODIFY `pGzip` FLOAT UNSIGNED NULL,
MODIFY `pMinRedirect` FLOAT UNSIGNED NULL,
MODIFY `pCssExpr` FLOAT UNSIGNED NULL,
MODIFY `pUnusedCSS` FLOAT UNSIGNED NULL,
MODIFY `pMinDns` FLOAT UNSIGNED NULL,
MODIFY `pDupeRsrc` FLOAT UNSIGNED NULL,
MODIFY `pScaleImgs` FLOAT UNSIGNED NULL,
MODIFY `pMinifyHTML` FLOAT UNSIGNED NULL,
MODIFY `pMinReqSize` FLOAT UNSIGNED NULL,
MODIFY `pCssJsOrder` FLOAT UNSIGNED NULL,
MODIFY `pCssInHead` FLOAT UNSIGNED NULL,
MODIFY `pCharsetEarly` FLOAT UNSIGNED NULL,
MODIFY `pBadReqs` FLOAT UNSIGNED NULL,
MODIFY `pCssImport` FLOAT UNSIGNED NULL,
MODIFY `pDocWrite` FLOAT UNSIGNED NULL,
MODIFY `pPreferAsync` FLOAT UNSIGNED NULL,
MODIFY `pRemoveQuery` FLOAT UNSIGNED NULL,
MODIFY `pVaryAE` FLOAT UNSIGNED NULL,
MODIFY `pSprite` FLOAT UNSIGNED NULL";

$versions[27]['down'][] = "ALTER TABLE `pagespeed` 
MODIFY `pSprite` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pVaryAE` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pRemoveQuery` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pPreferAsync` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pDocWrite` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pCssImport` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pBadReqs` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pCharsetEarly` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pCssInHead` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pCssJsOrder` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pMinReqSize` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pMinifyHTML` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pScaleImgs` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pDupeRsrc` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pMinDns` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pUnusedCSS` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pCssExpr` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pMinRedirect` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pGzip` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pDeferJS` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pCssSelect` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pParallelDl` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pNoCookie` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pCacheValid` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pBrowserCache` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pCombineCSS` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pCombineJS` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pImgDims` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pOptImgs` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pMinifyJS` FLOAT UNSIGNED NOT NULL DEFAULT '0',
MODIFY `pMinifyCSS` FLOAT UNSIGNED NOT NULL DEFAULT '0'";


/* -------------------------------------------------------------------------------------------------------
 * VERSION 26
 * Adding some indexes for faster loads
*/
$versions[26]['up'][] = "ALTER TABLE `dynatrace` ADD INDEX (`url_id`)";
$versions[26]['up'][] = "ALTER TABLE `har` ADD INDEX (`url_id`)";
$versions[26]['up'][] = "ALTER TABLE `dommonster` ADD INDEX (`url_id`)";
$versions[26]['up'][] = "ALTER TABLE `metric` ADD INDEX (`url_id`)";
$versions[26]['up'][] = "ALTER TABLE `pagetest` ADD INDEX (`url_id`)";

$versions[26]['down'][] = "ALTER TABLE `pagetest` DROP INDEX `url_id`";
$versions[26]['down'][] = "ALTER TABLE `metric` DROP INDEX `url_id`";
$versions[26]['down'][] = "ALTER TABLE `dommonster` DROP INDEX `url_id`";
$versions[26]['down'][] = "ALTER TABLE `har` DROP INDEX `url_id`";
$versions[26]['down'][] = "ALTER TABLE `dynatrace` DROP INDEX `url_id`";


/* -------------------------------------------------------------------------------------------------------
 * VERSION 24-25, 14-15
 * removed userbase database setup - leave it to userbase's scripts
*/
$versions[25]['up'][]		= "SELECT 1";
$versions[25]['udown'][]	= "SELECT 1";
$versions[24]['up'][]		= "SELECT 1";
$versions[24]['udown'][]	= "SELECT 1";
$versions[15]['up'][]		= "SELECT 1";
$versions[15]['udown'][]	= "SELECT 1";
$versions[14]['up'][]		= "SELECT 1";
$versions[14]['udown'][]	= "SELECT 1";

/* -------------------------------------------------------------------------------------------------------
 * VERSION 23
 * Added HAR beacon last_id
*/
$versions[23]['up'][] = "ALTER TABLE urls ADD har_last_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'Last measurement ID for HAR beacon'";
$versions[23]['down'][] = "ALTER TABLE urls DROP har_last_id";

/* -------------------------------------------------------------------------------------------------------
 * VERSION 22
 * Adding explicit default values for refresher bits
*/
$versions[22]['up'][]	= "ALTER TABLE  `urls` CHANGE  `y_refresh_request`  `y_refresh_request` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT 0 COMMENT  'Set it to one when YSlow score needs refreshing'";
$versions[22]['up'][]	= "ALTER TABLE  `urls` CHANGE  `p_refresh_request`  `p_refresh_request` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT 0 COMMENT  'Set it to one when YSlow score needs refreshing'";
$versions[22]['up'][]	= "ALTER TABLE  `urls` CHANGE  `dt_refresh_request`  `dt_refresh_request` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT 0 COMMENT  'Set it to one when YSlow score needs refreshing'";

$versions[22]['down'][]	= "ALTER TABLE  `urls` CHANGE  `y_refresh_request`  `y_refresh_request` TINYINT( 1 ) UNSIGNED NOT NULL COMMENT  'Set it to one when YSlow score needs refreshing'";
$versions[22]['down'][]	= "ALTER TABLE  `urls` CHANGE  `p_refresh_request`  `p_refresh_request` TINYINT( 1 ) UNSIGNED NOT NULL COMMENT  'Set it to one when YSlow score needs refreshing'";
$versions[22]['down'][]	= "ALTER TABLE  `urls` CHANGE  `dt_refresh_request`  `dt_refresh_request` TINYINT( 1 ) UNSIGNED NOT NULL COMMENT  'Set it to one when YSlow score needs refreshing'";

/* -------------------------------------------------------------------------------------------------------
 * VERSION 21
 * Added DOM Monstermetrics
*/
$versions[21]['up'][] = "ALTER TABLE urls ADD dommonster_last_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'Last measurement ID for DOM Monster beacon'";
$versions[21]['up'][] = "CREATE TABLE `dommonster` (
  `id` bigint(20) unsigned NOT NULL auto_increment COMMENT 'Measurement ID',
  `version` varchar(255) default NULL COMMENT 'Version of DOM Monster bookmarklet',
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'Measurement time',
  `url_id` bigint(20) unsigned NOT NULL COMMENT 'URL ID',
  elements bigint(20) unsigned NOT NULL COMMENT 'number of elements [number]',
  nodecount bigint(20) unsigned NOT NULL COMMENT 'number of DOM nodes [number]',
  textnodes bigint(20) unsigned NOT NULL COMMENT 'number of Text nodes [number]',
  textnodessize bigint(20) unsigned NOT NULL COMMENT 'size of Text nodes [bytes]',
  contentpercent decimal(5,2) unsigned NOT NULL COMMENT 'content percentage [percentage]',
  average decimal(10,1) unsigned NOT NULL COMMENT 'average nesting depth [number]',
  domsize bigint(20) unsigned NOT NULL COMMENT 'serialized DOM size [bytes]',
  bodycount bigint(20) unsigned default NULL COMMENT 'DOM tree serialization time [ms]',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM";

$versions[21]['down'][] = "ALTER TABLE urls DROP dommonster_last_id";
$versions[21]['down'][] = "DROP TABLE `dommonster`;";

/* -------------------------------------------------------------------------------------------------------
 * VERSION 20 
 * Allow test requests
*/
$versions[20]['up'][]	= "ALTER TABLE  `urls` ADD  `y_refresh_request` TINYINT( 1 ) UNSIGNED NOT NULL COMMENT  'Set it to one when YSlow score needs refreshing'";
$versions[20]['up'][]	= "ALTER TABLE  `urls` ADD  `p_refresh_request` TINYINT( 1 ) UNSIGNED NOT NULL COMMENT  'Set it to one when PageSpeed score needs refreshing'";
$versions[20]['up'][]	= "ALTER TABLE  `urls` ADD  `dt_refresh_request` TINYINT( 1 ) UNSIGNED NOT NULL COMMENT  'Set it to one when dynaTrace score needs refreshing'";
$versions[20]['down'][]	= "ALTER TABLE  `urls` DROP  `dt_refresh_request`";
$versions[20]['down'][]	= "ALTER TABLE  `urls` DROP  `p_refresh_request`";
$versions[20]['down'][]	= "ALTER TABLE  `urls` DROP  `y_refresh_request`";

/* -------------------------------------------------------------------------------------------------------
 * VERSION 19
 * Adding har link parameter
*/
$versions[19]['up'][]	= "ALTER TABLE  `har` CHANGE  `har`  `har` LONGBLOB NULL COMMENT  'HAR contents'";
$versions[19]['up'][]	= "ALTER TABLE  `har` ADD  `link` BLOB NULL COMMENT  'URL of HAR file'";
$versions[19]['down'][]	= "ALTER TABLE  `har` DROP  `link`";
$versions[19]['down'][]	= "ALTER TABLE  `har` CHANGE  `har`  `har` LONGBLOB NOT NULL COMMENT  'HAR contents'";

/* -------------------------------------------------------------------------------------------------------
 * VERSION 18
 * IP is not required anymore
*/
$versions[18]['up'][]	= "ALTER TABLE `pagespeed` CHANGE `ip` `ip` INT(4) UNSIGNED NULL DEFAULT '0'";
$versions[18]['up'][]	= "ALTER TABLE `yslow2` CHANGE `ip` `ip` INT(4) UNSIGNED NULL DEFAULT '0' COMMENT 'IP address of the agent'";
$versions[18]['down'][]	= "ALTER TABLE `yslow2` CHANGE `ip` `ip` INT(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'IP address of the agent'";
$versions[18]['down'][]	= "ALTER TABLE `pagespeed` CHANGE `ip` `ip` INT(4) UNSIGNED NOT NULL DEFAULT '0'";

/* -------------------------------------------------------------------------------------------------------
 * VERSION 17
 * Adding "Avoid Empty Image src" rule to yslow
 * Making all scores unsigned types
*/
$versions[17]['up'][] = "ALTER TABLE `yslow2`
	ADD `yemptysrc` SMALLINT(3) UNSIGNED COMMENT 'Avoid Empty Image src' AFTER `yexpires`,
	MODIFY `ynumreq` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Make fewer HTTP requests',
	MODIFY `ycdn` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Using CDN',
	MODIFY `yexpires` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Expires Headers',
	MODIFY `ycompress` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Gzip components',
	MODIFY `ycsstop` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'CSS at the top',
	MODIFY `yjsbottom` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'JS at the bottom',
	MODIFY `yexpressions` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'CSS expressions',
	MODIFY `yexternal` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Make JavaScript and CSS external',
	MODIFY `ydns` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Reduce DNS lookups',
	MODIFY `yminify` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Minify JavaScript and CSS',
	MODIFY `yredirects` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Avoid URL redirects',
	MODIFY `ydupes` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Remove duplicate JavaScript and CSS',
	MODIFY `yetags` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Configure entity tags (ETags)',
	MODIFY `yxhr` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Make AJAX cacheable',
	MODIFY `yxhrmethod` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Use GET for AJAX requests',
	MODIFY `ymindom` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Reduce the number of DOM elements',
	MODIFY `yno404` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Avoid HTTP 404 (Not Found) error',
	MODIFY `ymincookie` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Reduce cookie size',
	MODIFY `ycookiefree` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Use cookie-free domains',
	MODIFY `ynofilter` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Avoid AlphaImageLoader filter',
	MODIFY `yimgnoscale` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Do not scale images in HTML',
	MODIFY `yfavicon` smallint(3) UNSIGNED DEFAULT NULL COMMENT 'Make favicon small and cacheable'";

$versions[17]['down'][] = "ALTER TABLE `yslow2`
	DROP yemptysrc,
	MODIFY `ynumreq` smallint(6) DEFAULT NULL COMMENT 'Make fewer HTTP requests',
	MODIFY `ycdn` smallint(6) DEFAULT NULL COMMENT 'Using CDN',
	MODIFY `yexpires` smallint(6) DEFAULT NULL COMMENT 'Expires Headers',
	MODIFY `ycompress` smallint(6) DEFAULT NULL COMMENT 'Gzip components',
	MODIFY `ycsstop` smallint(6) DEFAULT NULL COMMENT 'CSS at the top',
	MODIFY `yjsbottom` smallint(6) DEFAULT NULL COMMENT 'JS at the bottom',
	MODIFY `yexpressions` smallint(6) DEFAULT NULL COMMENT 'CSS expressions',
	MODIFY `yexternal` smallint(6) DEFAULT NULL COMMENT 'Make JavaScript and CSS external',
	MODIFY `ydns` smallint(6) DEFAULT NULL COMMENT 'Reduce DNS lookups',
	MODIFY `yminify` smallint(6) DEFAULT NULL COMMENT 'Minify JavaScript and CSS',
	MODIFY `yredirects` smallint(6) DEFAULT NULL COMMENT 'Avoid URL redirects',
	MODIFY `ydupes` smallint(6) DEFAULT NULL COMMENT 'Remove duplicate JavaScript and CSS',
	MODIFY `yetags` smallint(6) DEFAULT NULL COMMENT 'Configure entity tags (ETags)',
	MODIFY `yxhr` smallint(6) DEFAULT NULL COMMENT 'Make AJAX cacheable',
	MODIFY `yxhrmethod` smallint(6) DEFAULT NULL COMMENT 'Use GET for AJAX requests',
	MODIFY `ymindom` smallint(6) DEFAULT NULL COMMENT 'Reduce the number of DOM elements',
	MODIFY `yno404` smallint(6) DEFAULT NULL COMMENT 'Avoid HTTP 404 (Not Found) error',
	MODIFY `ymincookie` smallint(6) DEFAULT NULL COMMENT 'Reduce cookie size',
	MODIFY `ycookiefree` smallint(6) DEFAULT NULL COMMENT 'Use cookie-free domains',
	MODIFY `ynofilter` smallint(6) DEFAULT NULL COMMENT 'Avoid AlphaImageLoader filter',
	MODIFY `yimgnoscale` smallint(6) DEFAULT NULL COMMENT 'Do not scale images in HTML',
	MODIFY `yfavicon` smallint(6) DEFAULT NULL COMMENT 'Make favicon small and cacheable'";

/* -------------------------------------------------------------------------------------------------------
 * VERSION 16
 * Adding mroe details for PageTest
*/
$versions[16]['up'][]	= "ALTER TABLE `pagetest`
ADD `f_loadTime` MEDIUMINT(3) UNSIGNED COMMENT '[first view] Load Time (ms)',
ADD `r_loadTime` MEDIUMINT(3) UNSIGNED COMMENT '[repeat view] Load Time (ms)',
ADD `f_TTFB` MEDIUMINT(3) UNSIGNED COMMENT '[first view] Time to First Byte (ms)',
ADD `r_TTFB` MEDIUMINT(3) UNSIGNED COMMENT '[repeat view] Time to First Byte (ms)',
ADD `f_bytesIn` INT(4) UNSIGNED COMMENT '[first view] Bytes In',
ADD `r_bytesIn` INT(4) UNSIGNED COMMENT '[repeat view] Bytes In',
ADD `f_bytesInDoc` INT(4) UNSIGNED COMMENT '[first view] Bytes In (Document)',
ADD `r_bytesInDoc` INT(4) UNSIGNED COMMENT '[repeat view] Bytes In (Document)',
ADD `f_requests` SMALLINT(2) UNSIGNED COMMENT '[first view] Number of Requests',
ADD `r_requests` SMALLINT(2) UNSIGNED COMMENT '[repeat view] Number of Requests',
ADD `f_requestsDoc` SMALLINT(2) UNSIGNED COMMENT '[first view] Number of Requests (Document)',
ADD `r_requestsDoc` SMALLINT(2) UNSIGNED COMMENT '[repeat view] Number of Requests (Document)',
ADD `f_render` MEDIUMINT(3) UNSIGNED COMMENT '[first view] Time to Start Render (ms)',
ADD `r_render` MEDIUMINT(3) UNSIGNED COMMENT '[repeat view] Time to Start Render (ms)',
ADD `f_fullyLoaded` MEDIUMINT(3) UNSIGNED COMMENT '[first view] Time to Fully Loaded (ms)',
ADD `r_fullyLoaded` MEDIUMINT(3) UNSIGNED COMMENT '[repeat view] Time to Fully Loaded (ms)',
ADD `f_docTime` MEDIUMINT(3) UNSIGNED COMMENT '[first view] Document Complete Time (ms)',
ADD `r_docTime` MEDIUMINT(3) UNSIGNED COMMENT '[repeat view] Document Complete Time (ms)',
ADD `f_domTime` MEDIUMINT(3) UNSIGNED COMMENT '[first view] DOM Element Time (ms)',
ADD `r_domTime` MEDIUMINT(3) UNSIGNED COMMENT '[repeat view] DOM Element Time (ms)'";
$versions[16]['down'][]	= "ALTER TABLE `pagetest`
  DROP `f_loadTime`,
  DROP `r_loadTime`,
  DROP `f_TTFB`,
  DROP `r_TTFB`,
  DROP `f_bytesIn`,
  DROP `r_bytesIn`,
  DROP `f_bytesInDoc`,
  DROP `r_bytesInDoc`,
  DROP `f_requests`,
  DROP `r_requests`,
  DROP `f_requestsDoc`,
  DROP `r_requestsDoc`,
  DROP `f_render`,
  DROP `r_render`,
  DROP `f_fullyLoaded`,
  DROP `r_fullyLoaded`,
  DROP `f_docTime`,
  DROP `r_docTime`,
  DROP `f_domTime`,
  DROP `r_domTime`
";

/* version 13
 *
 * PageSpeed 1.9 support
*/

// up
$versions[13]['up'][] = "ALTER TABLE `pagespeed` CHANGE `pSpecifyCharsetEarly` `pCharsetEarly` FLOAT UNSIGNED NOT NULL DEFAULT  '0';";
$versions[13]['up'][] = "ALTER TABLE `pagespeed` CHANGE `pProxyCache` `pCacheValid` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";
$versions[13]['up'][] = "ALTER TABLE `pagespeed` CHANGE `pPutCssInTheDocumentHead` `pCssInHead` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";
$versions[13]['up'][] = "ALTER TABLE `pagespeed` CHANGE `pOptimizeTheOrderOfStylesAndScripts` `pCssJsOrder` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";
$versions[13]['up'][] = "ALTER TABLE `pagespeed` CHANGE `pMinimizeRequestSize` `pMinReqSize` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";

$versions[13]['up'][] = "ALTER TABLE `pagespeed` ADD COLUMN `pBadReqs` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";
$versions[13]['up'][] = "ALTER TABLE `pagespeed` ADD COLUMN `pCssImport` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";
$versions[13]['up'][] = "ALTER TABLE `pagespeed` ADD COLUMN `pDocWrite` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";
$versions[13]['up'][] = "ALTER TABLE `pagespeed` ADD COLUMN `pPreferAsync` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";
$versions[13]['up'][] = "ALTER TABLE `pagespeed` ADD COLUMN `pRemoveQuery` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";
$versions[13]['up'][] = "ALTER TABLE `pagespeed` ADD COLUMN `pVaryAE` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";
$versions[13]['up'][] = "ALTER TABLE `pagespeed` ADD COLUMN `pSprite` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";

// down
$versions[13]['down'][] = "ALTER TABLE `pagespeed` CHANGE `pCharsetEarly` `pSpecifyCharsetEarly` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";
$versions[13]['down'][] = "ALTER TABLE `pagespeed` CHANGE `pCacheValid` `pProxyCache` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";
$versions[13]['down'][] = "ALTER TABLE `pagespeed` CHANGE `pCssInHead` `pPutCssInTheDocumentHead` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";
$versions[13]['down'][] = "ALTER TABLE `pagespeed` CHANGE `pCssJsOrder` `pOptimizeTheOrderOfStylesAndScripts` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";
$versions[13]['down'][] = "ALTER TABLE `pagespeed` CHANGE `pMinReqSize` `pMinimizeRequestSize` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";

$versions[13]['down'][] = "ALTER TABLE `pagespeed` DROP COLUMN `pBadReqs`";
$versions[13]['down'][] = "ALTER TABLE `pagespeed` DROP COLUMN `pCssImport`";
$versions[13]['down'][] = "ALTER TABLE `pagespeed` DROP COLUMN `pDocWrite`";
$versions[13]['down'][] = "ALTER TABLE `pagespeed` DROP COLUMN `pPreferAsync`";
$versions[13]['down'][] = "ALTER TABLE `pagespeed` DROP COLUMN `pRemoveQuery`";
$versions[13]['down'][] = "ALTER TABLE `pagespeed` DROP COLUMN `pVaryAE`";
$versions[13]['down'][] = "ALTER TABLE `pagespeed` DROP COLUMN `pSprite`";

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
$versions[6]['up'][] = "CREATE TABLE `user_urls` (
  `user_id` int(10) unsigned NOT NULL COMMENT 'User ID',
  `url_id` bigint(20) unsigned NOT NULL COMMENT 'URL ID to measure',
  PRIMARY KEY  (`user_id`,`url_id`)
) ENGINE=MyISAM;";

$versions[6]['down'][] = "DROP TABLE IF EXISTS `user_urls`";

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
// moved tables.sql here to unify upgrade and install process
$versions[1]['up'][] = "CREATE TABLE `event` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `type` varchar(25) default NULL COMMENT 'string representing type of the event',
  `url_prefix` blob NOT NULL COMMENT 'URL prefix to match the urls - usually protocol and host name',
  `title` text NOT NULL COMMENT 'event message',
  `start` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'start date of event',
  `end` timestamp NULL default NULL COMMENT 'end date of event (if null, start is the same as end)',
  `resource_url` blob COMMENT 'additional URL to resource related to the event.',
  PRIMARY KEY  (`id`),
  KEY `start` (`start`)
) ENGINE=MyISAM";
$versions[1]['up'][] = "CREATE TABLE `metric` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `url_id` bigint(20) unsigned NOT NULL default '0',
  `metric_id` mediumint(8) unsigned NOT NULL default '0',
  `value` float NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM";
$versions[1]['up'][] = "CREATE TABLE `pagespeed` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `ip` int(4) unsigned NOT NULL default '0',
  `user_agent` text NOT NULL,
  `url_id` bigint(20) unsigned NOT NULL default '0',
  `w` bigint(20) unsigned NOT NULL default '0',
  `o` float unsigned NOT NULL default '0',
  `l` bigint(20) unsigned NOT NULL default '0',
  `r` smallint(6) unsigned NOT NULL default '0',
  `t` bigint(20) unsigned NOT NULL default '0',
  `v` text NOT NULL,
  `pMinifyCSS` float unsigned NOT NULL default '0',
  `pMinifyJS` float unsigned NOT NULL default '0',
  `pOptImgs` float unsigned NOT NULL default '0',
  `pImgDims` float unsigned NOT NULL default '0',
  `pCombineJS` float unsigned NOT NULL default '0',
  `pCombineCSS` float unsigned NOT NULL default '0',
  `pCssInHead` float unsigned NOT NULL default '0',
  `pBrowserCache` float unsigned NOT NULL default '0',
  `pProxyCache` float unsigned NOT NULL default '0',
  `pNoCookie` float unsigned NOT NULL default '0',
  `pCookieSize` float unsigned NOT NULL default '0',
  `pParallelDl` float unsigned NOT NULL default '0',
  `pCssSelect` float unsigned NOT NULL default '0',
  `pCssJsOrder` float unsigned NOT NULL default '0',
  `pDeferJS` float unsigned NOT NULL default '0',
  `pGzip` float unsigned NOT NULL default '0',
  `pMinRedirect` float unsigned NOT NULL default '0',
  `pCssExpr` float unsigned NOT NULL default '0',
  `pUnusedCSS` float unsigned NOT NULL default '0',
  `pMinDns` float unsigned NOT NULL default '0',
  `pDupeRsrc` float unsigned NOT NULL default '0',
  `pScaleImgs` float unsigned NOT NULL default '0' COMMENT 'Scale Images',
  `pMinifyHTML` float unsigned NOT NULL default '0',
  `pMinimizeRequestSize` float unsigned NOT NULL default '0',
  `pOptimizeTheOrderOfStylesAndScripts` float unsigned NOT NULL default '0',
  `pPutCssInTheDocumentHead` float unsigned NOT NULL default '0',
  `pSpecifyCharsetEarly` float unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `url_id` (`url_id`)
) ENGINE=MyISAM";
$versions[1]['up'][] = "CREATE TABLE `urls` (
  `id` bigint(20) unsigned NOT NULL auto_increment COMMENT 'id to reference',
  `url` blob NOT NULL COMMENT 'url',
  `last_update` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `last_event_update` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'Last time events were updated for this URL',
  `w` bigint(20) unsigned NOT NULL default '0' COMMENT 'latest size of the page in bytes',
  `o` smallint(6) unsigned default NULL COMMENT 'latest overall YSlow grade calculated for this profile',
  `r` smallint(6) unsigned NOT NULL default '0' COMMENT 'latest amount of requests with empty cache',
  `ps_w` bigint(20) unsigned NOT NULL default '0',
  `ps_o` float unsigned default NULL,
  `ps_l` bigint(20) unsigned NOT NULL default '0',
  `ps_r` smallint(6) unsigned NOT NULL default '0',
  `ps_t` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `last_update` (`last_update`)
) ENGINE=MyISAM";
$versions[1]['up'][] = "CREATE TABLE `yslow2` (
  `id` bigint(20) unsigned NOT NULL auto_increment COMMENT 'Entry id',
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'Measurement timestamp',
  `ip` int(4) unsigned NOT NULL default '0' COMMENT 'IP address of the agent',
  `user_agent` text NOT NULL COMMENT 'User agent string',
  `url_id` bigint(20) unsigned NOT NULL default '0',
  `w` bigint(20) unsigned NOT NULL default '0' COMMENT 'size of the page in bytes',
  `o` smallint(5) unsigned NOT NULL default '0' COMMENT 'overall YSlow grade calculated for this profile',
  `r` smallint(6) unsigned NOT NULL default '0' COMMENT 'total amount of requests with empty cache',
  `i` text NOT NULL COMMENT 'testing profile used',
  `lt` bigint(20) unsigned NOT NULL default '0' COMMENT 'page load time',
  `ynumreq` smallint(6) default NULL COMMENT 'Make fewer HTTP requests',
  `ycdn` smallint(6) default NULL COMMENT 'Using CDN',
  `yexpires` smallint(6) default NULL COMMENT 'Expires Headers',
  `ycompress` smallint(6) default NULL COMMENT 'Gzip components',
  `ycsstop` smallint(6) default NULL COMMENT 'CSS at the top',
  `yjsbottom` smallint(6) default NULL COMMENT 'JS at the bottom',
  `yexpressions` smallint(6) default NULL COMMENT 'CSS expressions',
  `yexternal` smallint(6) default NULL COMMENT 'Make JavaScript and CSS external',
  `ydns` smallint(6) default NULL COMMENT 'Reduce DNS lookups',
  `yminify` smallint(6) default NULL COMMENT 'Minify JavaScript and CSS',
  `yredirects` smallint(6) default NULL COMMENT 'Avoid URL redirects',
  `ydupes` smallint(6) default NULL COMMENT 'Remove duplicate JavaScript and CSS',
  `yetags` smallint(6) default NULL COMMENT 'Configure entity tags (ETags)',
  `yxhr` smallint(6) default NULL COMMENT 'Make AJAX cacheable',
  `yxhrmethod` smallint(6) default NULL COMMENT 'Use GET for AJAX requests',
  `ymindom` smallint(6) default NULL COMMENT 'Reduce the number of DOM elements',
  `yno404` smallint(6) default NULL COMMENT 'Avoid HTTP 404 (Not Found) error',
  `ymincookie` smallint(6) default NULL COMMENT 'Reduce cookie size',
  `ycookiefree` smallint(6) default NULL COMMENT 'Use cookie-free domains',
  `ynofilter` smallint(6) default NULL COMMENT 'Avoid AlphaImageLoader filter',
  `yimgnoscale` smallint(6) default NULL COMMENT 'Do not scale images in HTML',
  `yfavicon` smallint(6) default NULL COMMENT 'Make favicon small and cacheable',
  `details` text COMMENT 'Beacon details',
  PRIMARY KEY  (`id`),
  KEY `url_id` (`url_id`)
) ENGINE=MyISAM";

$versions[1]['down'][] = "DROP TABLE event";
$versions[1]['down'][] = "DROP TABLE metric";
$versions[1]['down'][] = "DROP TABLE pagespeed";
$versions[1]['down'][] = "DROP TABLE urls";
$versions[1]['down'][] = "DROP TABLE yslow2";

require_once(dirname(__FILE__).'/global.php');

// creating DBUpgrade object with your database credentials and $versions defined above
$dbupgrade = new DBUpgrade(new mysqli($host, $user, $pass, $db, $port), $versions);

require_once(dirname(__FILE__).'/dbupgrade/client.php');
