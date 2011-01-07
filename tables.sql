-- MySQL dump 10.13  Distrib 5.1.49, for pc-linux-gnu (i686)
--
-- Host: localhost    Database: showslow
-- ------------------------------------------------------
-- Server version	5.1.49

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
-- Table structure for table `3f7f6ece338d68f7fbd069377de434e0_db_version`
--

DROP TABLE IF EXISTS `3f7f6ece338d68f7fbd069377de434e0_db_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `3f7f6ece338d68f7fbd069377de434e0_db_version` (
  `version` int(10) unsigned NOT NULL DEFAULT '2',
  PRIMARY KEY (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `db_version`
--

DROP TABLE IF EXISTS `db_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `db_version` (
  `version` int(10) unsigned NOT NULL DEFAULT '19',
  PRIMARY KEY (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dynatrace`
--

DROP TABLE IF EXISTS `dynatrace`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynatrace` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Measurement ID',
  `version` varchar(255) DEFAULT NULL COMMENT 'Version of the format used',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Measurement time',
  `url_id` bigint(20) unsigned NOT NULL COMMENT 'URL ID',
  `rank` smallint(5) unsigned NOT NULL COMMENT 'verall Page Rank (1-100)',
  `cache` smallint(5) unsigned DEFAULT NULL COMMENT 'Page Rank on Caching Best Practices (1-100)',
  `net` smallint(5) unsigned DEFAULT NULL COMMENT 'Page Rank on Network Requests (1-100)',
  `server` smallint(5) unsigned DEFAULT NULL COMMENT 'Page Rank on Server-Side Execution Time (1-100)',
  `js` smallint(5) unsigned DEFAULT NULL COMMENT 'Page Rank on JavaScript executions (1-100)',
  `timetoimpression` bigint(20) unsigned DEFAULT NULL COMMENT 'Time to First Impression [ms]',
  `timetoonload` bigint(20) unsigned DEFAULT NULL COMMENT 'Time to onLoad [ms]',
  `timetofullload` bigint(20) unsigned DEFAULT NULL COMMENT 'Time to Full Page Load [ms]',
  `reqnumber` smallint(6) unsigned DEFAULT NULL COMMENT '# of Requests [Count]',
  `xhrnumber` smallint(6) unsigned DEFAULT NULL COMMENT '# of XHR Requests [Count]',
  `pagesize` bigint(20) unsigned DEFAULT NULL COMMENT 'Total Page Size [bytes]',
  `cachablesize` bigint(20) unsigned DEFAULT NULL COMMENT 'Total Cachable Size [bytes]',
  `noncachablesize` bigint(20) unsigned DEFAULT NULL COMMENT 'Total Non-Cachable Size [bytes]',
  `timeonnetwork` bigint(20) unsigned DEFAULT NULL COMMENT 'Total Time on Network [ms]',
  `timeinjs` bigint(20) unsigned DEFAULT NULL COMMENT 'Total Time in JavaScript [ms]',
  `timeinrendering` bigint(20) unsigned DEFAULT NULL COMMENT 'Total Time in Rendering [ms]',
  `details` text COMMENT 'Beacon details',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `event`
--

DROP TABLE IF EXISTS `event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(25) DEFAULT NULL COMMENT 'string representing type of the event',
  `url_prefix` blob NOT NULL COMMENT 'URL prefix to match the urls - usually protocol and host name',
  `title` text NOT NULL COMMENT 'event message',
  `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'start date of event',
  `end` timestamp NULL DEFAULT NULL COMMENT 'end date of event (if null, start is the same as end)',
  `resource_url` blob COMMENT 'additional URL to resource related to the event.',
  PRIMARY KEY (`id`),
  KEY `start` (`start`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `har`
--

DROP TABLE IF EXISTS `har`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `har` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique HAR id',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `url_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'URL id',
  `har` longblob COMMENT 'HAR contents',
  `compressed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indicates that HAR data is stored compressed',
  `link` blob COMMENT 'URL of HAR file',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `metric`
--

DROP TABLE IF EXISTS `metric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `metric` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `url_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `metric_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `value` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pagespeed`
--

DROP TABLE IF EXISTS `pagespeed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagespeed` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` int(4) unsigned DEFAULT '0',
  `user_agent` text NOT NULL,
  `url_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `w` bigint(20) unsigned NOT NULL DEFAULT '0',
  `o` float unsigned NOT NULL DEFAULT '0',
  `l` bigint(20) unsigned NOT NULL DEFAULT '0',
  `r` smallint(6) unsigned NOT NULL DEFAULT '0',
  `t` bigint(20) unsigned NOT NULL DEFAULT '0',
  `v` text NOT NULL,
  `pMinifyCSS` float unsigned NOT NULL DEFAULT '0',
  `pMinifyJS` float unsigned NOT NULL DEFAULT '0',
  `pOptImgs` float unsigned NOT NULL DEFAULT '0',
  `pImgDims` float unsigned NOT NULL DEFAULT '0',
  `pCombineJS` float unsigned NOT NULL DEFAULT '0',
  `pCombineCSS` float unsigned NOT NULL DEFAULT '0',
  `pBrowserCache` float unsigned NOT NULL DEFAULT '0',
  `pCacheValid` float unsigned NOT NULL DEFAULT '0',
  `pNoCookie` float unsigned NOT NULL DEFAULT '0',
  `pParallelDl` float unsigned NOT NULL DEFAULT '0',
  `pCssSelect` float unsigned NOT NULL DEFAULT '0',
  `pDeferJS` float unsigned NOT NULL DEFAULT '0',
  `pGzip` float unsigned NOT NULL DEFAULT '0',
  `pMinRedirect` float unsigned NOT NULL DEFAULT '0',
  `pCssExpr` float unsigned NOT NULL DEFAULT '0',
  `pUnusedCSS` float unsigned NOT NULL DEFAULT '0',
  `pMinDns` float unsigned NOT NULL DEFAULT '0',
  `pDupeRsrc` float unsigned NOT NULL DEFAULT '0',
  `pScaleImgs` float unsigned NOT NULL DEFAULT '0' COMMENT 'Scale Images',
  `pMinifyHTML` float unsigned NOT NULL DEFAULT '0',
  `pMinReqSize` float unsigned NOT NULL DEFAULT '0',
  `pCssJsOrder` float unsigned NOT NULL DEFAULT '0',
  `pCssInHead` float unsigned NOT NULL DEFAULT '0',
  `pCharsetEarly` float unsigned NOT NULL DEFAULT '0',
  `pBadReqs` float unsigned NOT NULL DEFAULT '0',
  `pCssImport` float unsigned NOT NULL DEFAULT '0',
  `pDocWrite` float unsigned NOT NULL DEFAULT '0',
  `pPreferAsync` float unsigned NOT NULL DEFAULT '0',
  `pRemoveQuery` float unsigned NOT NULL DEFAULT '0',
  `pVaryAE` float unsigned NOT NULL DEFAULT '0',
  `pSprite` float unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `url_id` (`url_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pagetest`
--

DROP TABLE IF EXISTS `pagetest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagetest` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique id',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `url_id` bigint(20) unsigned NOT NULL COMMENT 'URL id',
  `test_id` varchar(255) NOT NULL COMMENT 'PageTest test id',
  `test_url` blob NOT NULL COMMENT 'PageTest result URL to redirect to',
  `location` text COMMENT 'Test location',
  `f_loadTime` mediumint(3) unsigned DEFAULT NULL COMMENT '[first view] Load Time (ms)',
  `r_loadTime` mediumint(3) unsigned DEFAULT NULL COMMENT '[repeat view] Load Time (ms)',
  `f_TTFB` mediumint(3) unsigned DEFAULT NULL COMMENT '[first view] Time to First Byte (ms)',
  `r_TTFB` mediumint(3) unsigned DEFAULT NULL COMMENT '[repeat view] Time to First Byte (ms)',
  `f_bytesIn` int(4) unsigned DEFAULT NULL COMMENT '[first view] Bytes In',
  `r_bytesIn` int(4) unsigned DEFAULT NULL COMMENT '[repeat view] Bytes In',
  `f_bytesInDoc` int(4) unsigned DEFAULT NULL COMMENT '[first view] Bytes In (Document)',
  `r_bytesInDoc` int(4) unsigned DEFAULT NULL COMMENT '[repeat view] Bytes In (Document)',
  `f_requests` smallint(2) unsigned DEFAULT NULL COMMENT '[first view] Number of Requests',
  `r_requests` smallint(2) unsigned DEFAULT NULL COMMENT '[repeat view] Number of Requests',
  `f_requestsDoc` smallint(2) unsigned DEFAULT NULL COMMENT '[first view] Number of Requests (Document)',
  `r_requestsDoc` smallint(2) unsigned DEFAULT NULL COMMENT '[repeat view] Number of Requests (Document)',
  `f_render` mediumint(3) unsigned DEFAULT NULL COMMENT '[first view] Time to Start Render (ms)',
  `r_render` mediumint(3) unsigned DEFAULT NULL COMMENT '[repeat view] Time to Start Render (ms)',
  `f_fullyLoaded` mediumint(3) unsigned DEFAULT NULL COMMENT '[first view] Time to Fully Loaded (ms)',
  `r_fullyLoaded` mediumint(3) unsigned DEFAULT NULL COMMENT '[repeat view] Time to Fully Loaded (ms)',
  `f_docTime` mediumint(3) unsigned DEFAULT NULL COMMENT '[first view] Document Complete Time (ms)',
  `r_docTime` mediumint(3) unsigned DEFAULT NULL COMMENT '[repeat view] Document Complete Time (ms)',
  `f_domTime` mediumint(3) unsigned DEFAULT NULL COMMENT '[first view] DOM Element Time (ms)',
  `r_domTime` mediumint(3) unsigned DEFAULT NULL COMMENT '[repeat view] DOM Element Time (ms)',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `u_activity`
--

DROP TABLE IF EXISTS `u_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `u_activity` (
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time of activity',
  `user_id` int(10) unsigned NOT NULL COMMENT 'User ID',
  `activity_id` int(2) unsigned NOT NULL COMMENT 'Activity ID',
  KEY `time` (`time`),
  KEY `user_id` (`user_id`),
  KEY `activity_id` (`activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Stores user activities';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `u_googlefriendconnect`
--

DROP TABLE IF EXISTS `u_googlefriendconnect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `u_googlefriendconnect` (
  `user_id` int(10) unsigned NOT NULL COMMENT 'User ID',
  `google_id` varchar(255) NOT NULL COMMENT 'Google Friend Connect ID',
  `userpic` text NOT NULL COMMENT 'Google Friend Connect User picture',
  PRIMARY KEY (`user_id`,`google_id`),
  CONSTRAINT `gfc_user` FOREIGN KEY (`user_id`) REFERENCES `u_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `u_invitation`
--

DROP TABLE IF EXISTS `u_invitation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `u_invitation` (
  `code` char(10) NOT NULL COMMENT 'Code',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When invitation was created',
  `issuedby` bigint(10) unsigned NOT NULL DEFAULT '1' COMMENT 'User who issued the invitation. Default is Sergey.',
  `sentto` text COMMENT 'Note about who this invitation was sent to',
  `user` bigint(10) unsigned DEFAULT NULL COMMENT 'User name',
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `u_users`
--

DROP TABLE IF EXISTS `u_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `u_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `regtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time of registration',
  `name` text NOT NULL,
  `username` varchar(25) DEFAULT NULL,
  `email` varchar(320) DEFAULT NULL,
  `pass` varchar(40) NOT NULL COMMENT 'Password digest',
  `salt` varchar(13) NOT NULL COMMENT 'Salt',
  `temppass` varchar(13) DEFAULT NULL COMMENT 'Temporary password used for password recovery',
  `temppasstime` timestamp NULL DEFAULT NULL COMMENT 'Temporary password generation time',
  `requirespassreset` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Flag indicating that user must reset their password before using the site',
  `fb_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Facebook user ID',
  `last_accessed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `fb_id` (`fb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `urls`
--

DROP TABLE IF EXISTS `urls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `urls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id to reference',
  `url` blob NOT NULL COMMENT 'url',
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time when URL was added to the table',
  `last_update` timestamp NULL DEFAULT NULL,
  `last_event_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Last time events were updated for this URL',
  `yslow2_last_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Last measurement ID for YSlow beacon',
  `pagespeed_last_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Last measurement ID for PageSpeed beacon',
  `dynatrace_last_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Last measurement ID for dynaTrace beacon',
  PRIMARY KEY (`id`),
  KEY `last_update` (`last_update`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_urls`
--

DROP TABLE IF EXISTS `user_urls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_urls` (
  `user_id` int(10) unsigned NOT NULL COMMENT 'User ID',
  `url_id` bigint(20) unsigned NOT NULL COMMENT 'URL ID to measure',
  PRIMARY KEY (`user_id`,`url_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `yslow2`
--

DROP TABLE IF EXISTS `yslow2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yslow2` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Entry id',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Measurement timestamp',
  `ip` int(4) unsigned DEFAULT '0' COMMENT 'IP address of the agent',
  `user_agent` text NOT NULL COMMENT 'User agent string',
  `url_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `w` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'size of the page in bytes',
  `o` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'overall YSlow grade calculated for this profile',
  `r` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT 'total amount of requests with empty cache',
  `i` text NOT NULL COMMENT 'testing profile used',
  `lt` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'page load time',
  `ynumreq` smallint(3) unsigned DEFAULT NULL COMMENT 'Make fewer HTTP requests',
  `ycdn` smallint(3) unsigned DEFAULT NULL COMMENT 'Using CDN',
  `yexpires` smallint(3) unsigned DEFAULT NULL COMMENT 'Expires Headers',
  `yemptysrc` smallint(3) unsigned DEFAULT NULL COMMENT 'Avoid Empty Image src',
  `ycompress` smallint(3) unsigned DEFAULT NULL COMMENT 'Gzip components',
  `ycsstop` smallint(3) unsigned DEFAULT NULL COMMENT 'CSS at the top',
  `yjsbottom` smallint(3) unsigned DEFAULT NULL COMMENT 'JS at the bottom',
  `yexpressions` smallint(3) unsigned DEFAULT NULL COMMENT 'CSS expressions',
  `yexternal` smallint(3) unsigned DEFAULT NULL COMMENT 'Make JavaScript and CSS external',
  `ydns` smallint(3) unsigned DEFAULT NULL COMMENT 'Reduce DNS lookups',
  `yminify` smallint(3) unsigned DEFAULT NULL COMMENT 'Minify JavaScript and CSS',
  `yredirects` smallint(3) unsigned DEFAULT NULL COMMENT 'Avoid URL redirects',
  `ydupes` smallint(3) unsigned DEFAULT NULL COMMENT 'Remove duplicate JavaScript and CSS',
  `yetags` smallint(3) unsigned DEFAULT NULL COMMENT 'Configure entity tags (ETags)',
  `yxhr` smallint(3) unsigned DEFAULT NULL COMMENT 'Make AJAX cacheable',
  `yxhrmethod` smallint(3) unsigned DEFAULT NULL COMMENT 'Use GET for AJAX requests',
  `ymindom` smallint(3) unsigned DEFAULT NULL COMMENT 'Reduce the number of DOM elements',
  `yno404` smallint(3) unsigned DEFAULT NULL COMMENT 'Avoid HTTP 404 (Not Found) error',
  `ymincookie` smallint(3) unsigned DEFAULT NULL COMMENT 'Reduce cookie size',
  `ycookiefree` smallint(3) unsigned DEFAULT NULL COMMENT 'Use cookie-free domains',
  `ynofilter` smallint(3) unsigned DEFAULT NULL COMMENT 'Avoid AlphaImageLoader filter',
  `yimgnoscale` smallint(3) unsigned DEFAULT NULL COMMENT 'Do not scale images in HTML',
  `yfavicon` smallint(3) unsigned DEFAULT NULL COMMENT 'Make favicon small and cacheable',
  `details` text COMMENT 'Beacon details',
  PRIMARY KEY (`id`),
  KEY `url_id` (`url_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Measurements gathered from yslow beacon v2.0 or earlier';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-01-07 19:01:55
