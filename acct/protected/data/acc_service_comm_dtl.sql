/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : account

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2021-02-09 11:51:12
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
  `product_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of acc_service_comm_dtl
-- ----------------------------
INSERT INTO `acc_service_comm_dtl` VALUES ('1', '1834', '0.000', '0.00', '-225.00', '0.00', '0.00', '20000.00', '1000.00', '0.00', '0.00', '0.00', '0.00', '0.00', '72012.00', '-24.00', '390.18', null, null, '2019-12-28 09:31:48', '2021-02-09 11:41:47');
INSERT INTO `acc_service_comm_dtl` VALUES ('2', '1835', '0.100', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', null, null, '2020-06-08 14:46:05', '2020-07-03 16:04:07');
INSERT INTO `acc_service_comm_dtl` VALUES ('3', '1836', '0.200', '300.00', '0.00', '-720.00', '0.00', '6000.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', null, null, '2020-08-10 16:23:52', '2020-08-13 15:47:03');
