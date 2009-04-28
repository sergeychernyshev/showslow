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
-- Table structure for table `yslow`
--

DROP TABLE IF EXISTS `yslow`;
CREATE TABLE `yslow` (
  `id` bigint(20) unsigned NOT NULL auto_increment COMMENT 'Entry id',
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'Measurement timestamp',
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Measurements gathered from yslow beacon';

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

