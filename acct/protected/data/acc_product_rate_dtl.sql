/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : account

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2020-11-24 16:24:38
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `acc_product_rate_dtl`
-- ----------------------------
DROP TABLE IF EXISTS `acc_product_rate_dtl`;
CREATE TABLE `acc_product_rate_dtl` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hdr_id` int(10) unsigned NOT NULL,
  `name` varchar(11) NOT NULL,
  `operator` char(2) NOT NULL,
  `sales_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_service_rate_dtl_01` (`hdr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of acc_product_rate_dtl
-- ----------------------------
INSERT INTO `acc_product_rate_dtl` VALUES ('13', '9', '99998', 'LE', '1000.00', '0.10', 'test', 'test', '2020-11-24 15:15:44', '2020-11-24 15:15:44');
INSERT INTO `acc_product_rate_dtl` VALUES ('14', '9', '99999', 'LE', '1000.00', '0.20', 'test', 'test', '2020-11-24 15:15:44', '2020-11-24 15:15:44');
INSERT INTO `acc_product_rate_dtl` VALUES ('15', '9', '99997', 'LE', '2000.00', '0.10', 'test', 'test', '2020-11-24 15:15:44', '2020-11-24 15:15:44');
INSERT INTO `acc_product_rate_dtl` VALUES ('16', '9', '99994', 'LE', '3000.00', '0.10', 'test', 'test', '2020-11-24 15:15:44', '2020-11-24 15:15:44');
INSERT INTO `acc_product_rate_dtl` VALUES ('17', '9', '65', 'LE', '1222.00', '0.30', 'test', 'test', '2020-11-24 15:15:44', '2020-11-24 15:15:44');
INSERT INTO `acc_product_rate_dtl` VALUES ('18', '10', '175', 'LE', '2000.00', '0.10', 'test', 'test', '2020-11-24 15:27:46', '2020-11-24 15:38:32');
INSERT INTO `acc_product_rate_dtl` VALUES ('19', '10', '58', 'LE', '2200.00', '0.10', 'test', 'test', '2020-11-24 15:27:46', '2020-11-24 15:27:46');
INSERT INTO `acc_product_rate_dtl` VALUES ('20', '11', '175', 'LE', '4000.00', '0.10', 'test', 'test', '2020-11-24 16:12:37', '2020-11-24 16:12:37');
INSERT INTO `acc_product_rate_dtl` VALUES ('21', '11', '58', 'LE', '5200.00', '0.10', 'test', 'test', '2020-11-24 16:12:37', '2020-11-24 16:12:37');
