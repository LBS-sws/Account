/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : accountdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2022-09-07 11:35:40
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for acc_consult_history
-- ----------------------------
DROP TABLE IF EXISTS `acc_consult_history`;
CREATE TABLE `acc_consult_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `consult_id` int(11) NOT NULL,
  `record_username` varchar(255) NOT NULL,
  `record_date` datetime NOT NULL,
  `record_status` int(255) NOT NULL DEFAULT '1',
  `record_remark` text,
  `lcu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8 COMMENT='咨詢費记录表';
