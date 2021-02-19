/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : account

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2021-02-19 14:53:36
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `acc_service_comm_copy`
-- ----------------------------
DROP TABLE IF EXISTS `acc_service_comm_copy`;
CREATE TABLE `acc_service_comm_copy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hdr_id` int(11) NOT NULL,
  `status_dt` datetime NOT NULL,
  `first_dt` datetime NOT NULL,
  `sign_dt` datetime NOT NULL,
  `cust_type` int(11) DEFAULT NULL,
  `service` varchar(1000) DEFAULT NULL,
  `paid_type` char(1) DEFAULT NULL,
  `salesman` varchar(111) DEFAULT NULL COMMENT '业务员',
  `othersalesman` varchar(111) DEFAULT NULL,
  `amt_paid` decimal(11,2) DEFAULT '0.00',
  `amt_install` decimal(11,2) DEFAULT '0.00',
  `surplus` decimal(11,2) DEFAULT '0.00',
  `other_commission` char(100) DEFAULT NULL,
  `commission` char(100) DEFAULT NULL,
  `ctrt_period` decimal(11,0) DEFAULT '12',
  `all_number` decimal(11,2) DEFAULT '0.00',
  `target` int(1) unsigned zerofill DEFAULT '0',
  `company_name` varchar(255) DEFAULT NULL,
  `city` varchar(5) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of acc_service_comm_copy
-- ----------------------------
INSERT INTO `acc_service_comm_copy` VALUES ('1', '1834', '0000-00-00 00:00:00', '2020-01-03 00:00:00', '2020-01-03 00:00:00', '1', '', 'M', null, '', '0.00', '0.00', '0.00', null, null, '12', '0.00', '0', '', 'SH', null, null);
INSERT INTO `acc_service_comm_copy` VALUES ('2', '1834', '0000-00-00 00:00:00', '2020-07-08 00:00:00', '2020-07-08 00:00:00', '1', '', 'M', '', '', '0.00', '0.00', '0.00', null, null, '12', '0.00', '0', '', 'SH', null, null);
INSERT INTO `acc_service_comm_copy` VALUES ('3', '1834', '0000-00-00 00:00:00', '2020-08-04 00:00:00', '2020-08-04 00:00:00', '1', '', 'M', '', '', '0.00', '0.00', '0.00', null, null, '12', '0.00', '0', '', 'SH', null, null);
INSERT INTO `acc_service_comm_copy` VALUES ('4', '1834', '0000-00-00 00:00:00', '2020-08-04 00:00:00', '2020-08-04 00:00:00', '1', '', 'M', '', '', '0.00', '0.00', '0.00', null, null, '12', '0.00', '0', '', 'SH', null, null);
