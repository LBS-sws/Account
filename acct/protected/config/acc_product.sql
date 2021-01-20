/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : account

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2021-01-20 16:50:57
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `acc_product`
-- ----------------------------
DROP TABLE IF EXISTS `acc_product`;
CREATE TABLE `acc_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_hdr_id` int(10) DEFAULT NULL,
  `final_money` decimal(11,2) DEFAULT NULL COMMENT '金额 总计',
  `examine` char(1) NOT NULL DEFAULT 'N' COMMENT 'N为未审核Y为待审核A为已经审核',
  `ject_remark` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of acc_product
-- ----------------------------
INSERT INTO `acc_product` VALUES ('6', '1837', '0.00', 'Y', null, 'SH');
INSERT INTO `acc_product` VALUES ('8', '1834', '3713.35', 'A', 'd sad a qdw qd ', 'SH');
INSERT INTO `acc_product` VALUES ('9', '1835', '31.00', 'Y', null, 'SH');
INSERT INTO `acc_product` VALUES ('12', '1836', '0.00', 'N', null, 'SH');
