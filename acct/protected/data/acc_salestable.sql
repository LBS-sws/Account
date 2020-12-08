/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : account

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2020-12-08 14:55:08
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `acc_salestable`
-- ----------------------------
DROP TABLE IF EXISTS `acc_salestable`;
CREATE TABLE `acc_salestable` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `hdr_id` int(10) NOT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `information` varchar(255) DEFAULT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `commission` decimal(11,2) DEFAULT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of acc_salestable
-- ----------------------------
INSERT INTO `acc_salestable` VALUES ('1', '1834', '阿速达啊', 'ia', '企鹅', '2020-12-25 00:00:00', '20.00', 'test', 'test', '2020-12-07 16:00:01');
INSERT INTO `acc_salestable` VALUES ('2', '1834', '沙雕阿速达', 'ia', '请问', '2020-12-15 00:00:00', '11.00', 'test', 'test', '2020-12-07 16:00:01');
