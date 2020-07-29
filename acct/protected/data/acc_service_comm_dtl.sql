/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : account

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2020-07-29 15:08:52
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `acc_service_comm_dtl`
-- ----------------------------
DROP TABLE IF EXISTS `acc_service_comm_dtl`;
CREATE TABLE `acc_service_comm_dtl` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hdr_id` int(10) unsigned NOT NULL,
  `new_calc` decimal(5,3) NOT NULL DEFAULT '0.000',
  `new_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `edit_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `end_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `performance_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `new_money` decimal(11,2) NOT NULL DEFAULT '0.00',
  `edit_money` decimal(11,2) NOT NULL DEFAULT '0.00',
  `out_money` decimal(11,2) NOT NULL DEFAULT '0.00',
  `performanceedit_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `performanceedit_money` decimal(11,2) NOT NULL DEFAULT '0.00',
  `performanceend_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `renewal_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `renewal_money` decimal(11,2) NOT NULL DEFAULT '0.00',
  `renewalend_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of acc_service_comm_dtl
-- ----------------------------
INSERT INTO `acc_service_comm_dtl` VALUES ('1', '1834', '0.200', '480.00', '0.00', '-120.00', '0.00', '2400.00', '2400.00', '1200.00', '-18.75', '1200.00', '0.00', '0.00', '0.00', '0.00', null, null, '2019-12-28 09:31:48', '2020-07-08 14:22:49');
INSERT INTO `acc_service_comm_dtl` VALUES ('2', '1835', '0.100', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', null, null, '2020-06-08 14:46:05', '2020-07-03 16:04:07');
