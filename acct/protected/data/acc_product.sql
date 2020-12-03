/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : account

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2020-12-03 15:05:13
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `acc_product`
-- ----------------------------
DROP TABLE IF EXISTS `acc_product`;
CREATE TABLE `acc_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_hdr_id` int(10) DEFAULT NULL,
  `amt_install_royalty` decimal(11,2) DEFAULT NULL COMMENT '提成点数 装机',
  `final_money` decimal(11,2) DEFAULT NULL COMMENT '金额 总计',
  `examine` char(1) NOT NULL DEFAULT 'N' COMMENT 'N为未审核Y为已经审核',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of acc_product
-- ----------------------------
INSERT INTO `acc_product` VALUES ('6', '1836', '20.00', '0.00', 'N');
INSERT INTO `acc_product` VALUES ('7', '1835', '1.00', '0.00', 'N');
INSERT INTO `acc_product` VALUES ('8', '1834', '0.00', '0.00', 'Y');
