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
-- Dumping data for table `wf_task`
--

LOCK TABLES `wf_task` WRITE;
/*!40000 ALTER TABLE `wf_task` DISABLE KEYS */;
INSERT INTO `wf_task` VALUES (114,4,'Send Email','sendEmail','','2018-06-09 03:53:12','2018-06-09 03:53:12'),(115,4,'Status=Pending for Approval','transit','PA','2018-06-09 03:53:12','2018-06-09 03:53:12'),(116,4,'Status=Approved','transit','A','2018-06-09 03:53:12','2018-06-09 03:53:12'),(117,4,'Status=Denied','transit','D','2018-06-09 03:53:12','2018-06-09 03:53:12'),(118,4,'Status=Pending for Reimbursement','transit','PR','2018-06-09 03:53:12','2018-06-09 03:53:12'),(119,4,'Generate Transaction','generateTransaction','','2018-06-09 03:53:12','2018-06-09 03:53:12'),(120,4,'Status=Reimbursed','transit','RE','2018-06-09 03:53:12','2018-06-09 03:53:12'),(121,4,'Status=Pending for Reimbursement Approval','transit','PS','2018-06-09 03:53:12','2018-06-09 03:53:12'),(122,4,'Status=Signed','transit','SI','2018-06-09 03:53:12','2018-06-09 03:53:12'),(123,4,'Route to Approver','routeToApprover','','2018-06-09 03:53:12','2018-06-09 03:53:12'),(124,4,'Route to Signer','routeToSigner','','2018-06-09 03:53:12','2018-06-09 03:53:12'),(125,4,'Route to Requestor','routeToRequestor','','2018-06-09 03:53:12','2018-06-09 03:53:12'),(126,4,'Status=End','transit','ED','2018-06-09 03:53:12','2018-06-09 03:53:12'),(127,4,'Status=Cancel','transit','C','2018-06-09 03:53:12','2018-06-09 03:53:12'),(128,4,'Clear All Pending','clearAllPending','','2018-06-09 03:53:12','2018-06-09 03:53:12'),(129,4,'Status=Cancel','transit','RC','2018-06-09 03:53:12','2018-06-09 03:53:12'),(130,4,'Status=Checked','transit','CK','2018-06-09 03:53:12','2018-06-09 03:53:12'),(131,4,'Status=Pending for Checking','transit','PC','2018-06-09 03:53:12','2018-06-09 03:53:12'),(132,4,'Route to Account','routeToAccount','','2018-06-09 03:53:12','2018-06-09 03:53:12'),(133,4,'Status=Pending for Reapply Reimbursement','transit','QR','2018-06-09 03:53:12','2018-06-09 03:53:12'),(134,4,'Status=Return Reimbursement','transit','RR','2018-06-09 03:53:12','2018-06-09 03:53:12'),(135,4,'Cancel Transaction','cancelTransaction','','2018-06-09 03:53:12','2018-06-09 03:53:12'),(145,4,'Status=Pending for Confirmation/Approval','transitByAmount','[1000,\"PB\",\"PA\"]','2018-06-09 03:59:51','2018-06-09 03:59:51'),(146,4,'Status=Confirmed','transit','AB','2018-06-09 04:00:32','2018-06-09 04:00:32'),(147,4,'Status=Denied Confirmation','transit','DB','2018-06-09 04:00:54','2018-06-09 04:00:54'),(148,4,'Route to Manager/Approver','routeToManagerOrApprover','','2018-06-09 04:01:27','2018-06-09 04:01:27'),(149,4,'Status=Approved and Signed','transit','S','2019-10-27 16:42:34','2019-10-27 16:42:34');
/*!40000 ALTER TABLE `wf_task` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-11-07 15:15:28
