-- MySQL dump 10.13  Distrib 5.7.18, for Linux (x86_64)
--
-- Host: localhost    Database: workflow
-- ------------------------------------------------------
-- Server version	5.7.18

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
-- Dumping data for table `wf_action`
--

LOCK TABLES `wf_action` WRITE;
/*!40000 ALTER TABLE `wf_action` DISABLE KEYS */;
INSERT INTO `wf_action` VALUES (107,4,'APPROVE','批准付款申请','2018-06-09 03:53:12','2018-06-09 03:53:12'),(108,4,'DENY','拒绝付款申请','2018-06-09 03:53:12','2018-06-09 03:53:12'),(109,4,'SUBMIT','提交付款申请','2018-06-09 03:53:12','2018-06-09 03:53:12'),(110,4,'CANCEL','取消付款申请','2018-06-09 03:53:12','2018-06-09 03:53:12'),(111,4,'REIMBURSE','报销单申请','2018-06-09 03:53:12','2018-06-09 03:53:12'),(112,4,'REIMAPPR','报销单签字','2018-06-09 03:53:12','2018-06-09 03:53:12'),(113,4,'REIMCANCEL','取消报销单申请','2018-06-09 03:53:12','2018-06-09 03:53:12'),(114,4,'REQUEST','要求覆核付款申请','2018-06-09 03:53:12','2018-06-09 03:53:12'),(115,4,'CHECK','覆核并提交付款申请','2018-06-09 03:53:12','2018-06-09 03:53:12'),(116,4,'REIMREJ','报销单退回','2018-06-09 03:53:12','2018-06-09 03:53:12'),(122,4,'CONFIRM','确认付款申请','2018-06-09 03:54:46','2018-06-09 03:54:46'),(123,4,'CONFDENY','拒绝确认付款申请','2018-06-09 03:55:05','2018-06-09 03:55:05'),(124,4,'APPRNSIGN','申请批准並签字','2019-10-27 16:42:33','2019-10-27 16:42:33');
/*!40000 ALTER TABLE `wf_action` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-11-07 15:14:40