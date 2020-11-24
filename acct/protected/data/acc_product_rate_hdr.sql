/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : account

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2020-11-24 16:24:42
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `acc_product_rate_hdr`
-- ----------------------------
DROP TABLE IF EXISTS `acc_product_rate_hdr`;
CREATE TABLE `acc_product_rate_hdr` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(11) NOT NULL,
  `city` char(5) NOT NULL,
  `start_dt` datetime NOT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of acc_product_rate_hdr
-- ----------------------------
INSERT INTO `acc_product_rate_hdr` VALUES ('9', '99998', 'CD', '2020-07-13 00:00:00', 'test', 'test', '2020-11-24 15:15:44', '2020-11-24 15:15:44');
INSERT INTO `acc_product_rate_hdr` VALUES ('10', '175', 'CD', '2020-07-13 00:00:00', 'test', 'test', '2020-11-24 15:27:46', '2020-11-24 15:27:46');
INSERT INTO `acc_product_rate_hdr` VALUES ('11', '175', 'CD', '2020-11-24 00:00:00', 'test', 'test', '2020-11-24 16:12:37', '2020-11-24 16:12:37');
