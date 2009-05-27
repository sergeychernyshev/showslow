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
-- Table structure for table `urls`
--

DROP TABLE IF EXISTS `urls`;
CREATE TABLE `urls` (
  `id` bigint(20) unsigned NOT NULL auto_increment COMMENT 'id to reference',
  `url` blob NOT NULL COMMENT 'url',
  `last_update` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `last_event_update` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'Last time events were updated for this URL',
  `w` bigint(20) unsigned NOT NULL default '0' COMMENT 'latest size of the page in bytes',
  `o` smallint(6) unsigned NOT NULL default '0' COMMENT 'latest overall YSlow grade calculated for this profile',
  `r` smallint(6) unsigned NOT NULL default '0' COMMENT 'latest amount of requests with empty cache',
  PRIMARY KEY  (`id`),
  KEY `last_update` (`last_update`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `yslow`
--

DROP TABLE IF EXISTS `yslow`;
CREATE TABLE `yslow` (
  `id` bigint(20) unsigned NOT NULL auto_increment COMMENT 'Entry id',
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'Measurement timestamp',
  `url_id` bigint(20) unsigned NOT NULL default '0',
  `ip` int(4) unsigned NOT NULL default '0' COMMENT 'IP address of the agent',
  `user_agent` text NOT NULL COMMENT 'User agent string',
  `w` smallint(6) NOT NULL default '0' COMMENT 'PAGE WEIGHT: Empty Cache (KB)',
  `o` smallint(6) NOT NULL default '0' COMMENT 'YSLOW SCORE',
  `u` text NOT NULL COMMENT 'URL',
  `r` smallint(6) NOT NULL default '0' COMMENT 'Empty Cache - Total Requests',
  `numcomps` smallint(6) NOT NULL default '0' COMMENT 'Number of Components',
  `cdn` smallint(6) NOT NULL default '0' COMMENT 'Using CDN',
  `expires` smallint(6) NOT NULL default '0' COMMENT 'Expires Headers',
  `gzip` smallint(6) NOT NULL default '0' COMMENT 'Gzip components',
  `cssattop` smallint(6) NOT NULL default '0' COMMENT 'CSS at the top',
  `jsatbottom` smallint(6) NOT NULL default '0' COMMENT 'JS at the bottom',
  `expression` smallint(6) NOT NULL default '0' COMMENT 'CSS expressions',
  `domains` smallint(6) NOT NULL default '0' COMMENT 'Reduce DNS lookups',
  `obfuscate` smallint(6) NOT NULL default '0' COMMENT 'JS minify',
  `redirects` smallint(6) NOT NULL default '0' COMMENT 'Avoid Redirects',
  `jstwice` smallint(6) NOT NULL default '0' COMMENT 'Duplicate JS',
  `etags` smallint(6) NOT NULL default '0' COMMENT 'ETAGS',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='DEPRECATED: Measurements gathered from yslow beacon';

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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Measurements gathered from yslow beacon v2.0 or earlier';

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

