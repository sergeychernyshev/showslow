-- MySQL dump 10.9
--
-- Host: localhost    Database: showslow
-- ------------------------------------------------------
-- Server version	4.1.16

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `db_version`
--

DROP TABLE IF EXISTS `db_version`;
CREATE TABLE `db_version` (
  `version` int(10) unsigned NOT NULL default '4',
  PRIMARY KEY  (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `event`
--

DROP TABLE IF EXISTS `event`;
CREATE TABLE `event` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `type` varchar(25) default NULL COMMENT 'string representing type of the event',
  `url_prefix` blob NOT NULL COMMENT 'URL prefix to match the urls - usually protocol and host name',
  `title` text NOT NULL COMMENT 'event message',
  `start` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'start date of event',
  `end` timestamp NULL default NULL COMMENT 'end date of event (if null, start is the same as end)',
  `resource_url` blob COMMENT 'additional URL to resource related to the event.',
  PRIMARY KEY  (`id`),
  KEY `start` (`start`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `har`
--

DROP TABLE IF EXISTS `har`;
CREATE TABLE `har` (
  `id` bigint(20) unsigned NOT NULL auto_increment COMMENT 'Unique HAR id',
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `url_id` bigint(20) unsigned NOT NULL default '0' COMMENT 'URL id',
  `har` longblob NOT NULL COMMENT 'HAR contents',
  `compressed` tinyint(1) NOT NULL default '0' COMMENT 'Indicates that HAR data is stored compressed',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `metric`
--

DROP TABLE IF EXISTS `metric`;
CREATE TABLE `metric` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `url_id` bigint(20) unsigned NOT NULL default '0',
  `metric_id` mediumint(8) unsigned NOT NULL default '0',
  `value` float NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `pagespeed`
--

DROP TABLE IF EXISTS `pagespeed`;
CREATE TABLE `pagespeed` (
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
  `pBrowserCache` float unsigned NOT NULL default '0',
  `pProxyCache` float unsigned NOT NULL default '0',
  `pNoCookie` float unsigned NOT NULL default '0',
  `pParallelDl` float unsigned NOT NULL default '0',
  `pCssSelect` float unsigned NOT NULL default '0',
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `urls`
--

DROP TABLE IF EXISTS `urls`;
CREATE TABLE `urls` (
  `id` bigint(20) unsigned NOT NULL auto_increment COMMENT 'id to reference',
  `url` blob NOT NULL COMMENT 'url',
  `last_update` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `last_event_update` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'Last time events were updated for this URL',
  `yslow2_last_id` bigint(20) unsigned default NULL COMMENT 'Last measurement ID for YSlow beacon',
  `pagespeed_last_id` bigint(20) unsigned default NULL COMMENT 'Last measurement ID for PageSpeed beacon',
  PRIMARY KEY  (`id`),
  KEY `last_update` (`last_update`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `yslow2`
--

DROP TABLE IF EXISTS `yslow2`;
CREATE TABLE `yslow2` (
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Measurements gathered from yslow beacon v2.0 or earlier';

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

