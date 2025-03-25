/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : accountdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2025-03-25 17:54:28
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for acc_plane_detail
-- ----------------------------
DROP TABLE IF EXISTS `acc_plane_detail`;
CREATE TABLE `acc_plane_detail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `plane_id` int(11) NOT NULL,
  `take_txt` text NOT NULL,
  `take_amt` float(13,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='直升機提成金額';
