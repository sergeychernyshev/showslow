-- MySQL dump 10.11
--
-- Host: localhost    Database: showslow
-- ------------------------------------------------------
-- Server version	5.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `db_version`
--

DROP TABLE IF EXISTS `db_version`;
CREATE TABLE `db_version` (
  `version` int(10) unsigned NOT NULL default '6',
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
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM AUTO_INCREMENT=127 DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM AUTO_INCREMENT=359 DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM AUTO_INCREMENT=6828 DEFAULT CHARSET=latin1;

--
-- Table structure for table `u_googlefriendconnect`
--

DROP TABLE IF EXISTS `u_googlefriendconnect`;
CREATE TABLE `u_googlefriendconnect` (
  `user_id` int(10) unsigned NOT NULL COMMENT 'User ID',
  `google_id` varchar(255) NOT NULL COMMENT 'Google Friend Connect ID',
  `userpic` text NOT NULL COMMENT 'Google Friend Connect User picture',
  PRIMARY KEY  (`user_id`,`google_id`),
  CONSTRAINT `gfc_user` FOREIGN KEY (`user_id`) REFERENCES `u_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `u_invitation`
--

DROP TABLE IF EXISTS `u_invitation`;
CREATE TABLE `u_invitation` (
  `code` char(10) NOT NULL COMMENT 'Code',
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'When invitation was created',
  `issuedby` bigint(10) unsigned NOT NULL default '1' COMMENT 'User who issued the invitation. Default is Sergey.',
  `sentto` text COMMENT 'Note about who this invitation was sent to',
  `user` bigint(10) unsigned default NULL COMMENT 'User name',
  PRIMARY KEY  (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `u_users`
--

DROP TABLE IF EXISTS `u_users`;
CREATE TABLE `u_users` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `urls`
--

DROP TABLE IF EXISTS `urls`;
CREATE TABLE `urls` (
  `id` bigint(20) unsigned NOT NULL auto_increment COMMENT 'id to reference',
  `url` blob NOT NULL COMMENT 'url',
  `last_update` timestamp NULL default NULL on update CURRENT_TIMESTAMP,
  `last_event_update` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'Last time events were updated for this URL',
  `yslow2_last_id` bigint(20) unsigned default NULL COMMENT 'Last measurement ID for YSlow beacon',
  `pagespeed_last_id` bigint(20) unsigned default NULL COMMENT 'Last measurement ID for PageSpeed beacon',
  PRIMARY KEY  (`id`),
  KEY `last_update` (`last_update`)
) ENGINE=MyISAM AUTO_INCREMENT=19176 DEFAULT CHARSET=latin1;

--
-- Table structure for table `user_urls`
--

DROP TABLE IF EXISTS `user_urls`;
CREATE TABLE `user_urls` (
  `user_id` int(10) unsigned NOT NULL COMMENT 'User ID',
  `url_id` bigint(20) unsigned NOT NULL COMMENT 'URL ID to measure',
  PRIMARY KEY  (`user_id`,`url_id`)
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
) ENGINE=MyISAM AUTO_INCREMENT=352607 DEFAULT CHARSET=latin1 COMMENT='Measurements gathered from yslow beacon v2.0 or earlier';
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-04-18 23:58:53
