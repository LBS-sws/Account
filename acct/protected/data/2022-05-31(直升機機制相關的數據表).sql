/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : accountdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2022-05-31 13:56:35
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for acc_plane
-- ----------------------------
DROP TABLE IF EXISTS `acc_plane`;
CREATE TABLE `acc_plane` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `plane_year` int(11) NOT NULL,
  `plane_month` int(11) NOT NULL,
  `plane_date` date NOT NULL,
  `job_id` int(11) NOT NULL DEFAULT '0',
  `job_num` int(11) NOT NULL DEFAULT '0',
  `money_id` int(11) NOT NULL DEFAULT '0',
  `money_value` float(11,2) NULL DEFAULT NULL COMMENT '當月的服務金額',
  `money_num` int(11) NOT NULL DEFAULT '0',
  `year_id` int(11) NOT NULL DEFAULT '0',
  `year_month` int(11) NOT NULL DEFAULT '0' COMMENT '員工年限',
  `year_num` int(11) NOT NULL DEFAULT '0',
  `other_sum` float(14,2) NOT NULL DEFAULT '0.00',
  `other_str` text,
  `plane_sum` float(15,2) NOT NULL DEFAULT '0.00',
  `city` varchar(255) NOT NULL,
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COMMENT='員工參與直升机';

-- ----------------------------
-- Table structure for acc_plane_info
-- ----------------------------
DROP TABLE IF EXISTS `acc_plane_info`;
CREATE TABLE `acc_plane_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `plane_id` int(11) NOT NULL,
  `other_id` int(11) NOT NULL DEFAULT '1' COMMENT '1:>=   2:>  3:=',
  `other_num` float(13,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COMMENT='直升機雜項金額';

-- ----------------------------
-- Table structure for acc_plane_set_job
-- ----------------------------
DROP TABLE IF EXISTS `acc_plane_set_job`;
CREATE TABLE `acc_plane_set_job` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `set_name` varchar(255) DEFAULT NULL COMMENT '設置的名稱',
  `start_date` date NOT NULL COMMENT '生效時間',
  `city` varchar(255) NOT NULL,
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='做单金额及对应的奖励（直升機）';

-- ----------------------------
-- Table structure for acc_plane_set_job_info
-- ----------------------------
DROP TABLE IF EXISTS `acc_plane_set_job_info`;
CREATE TABLE `acc_plane_set_job_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `formula_type` int(11) NOT NULL DEFAULT '3' COMMENT '1:>=   2:>  3:=',
  `value_name` varchar(255) NOT NULL,
  `value_money` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for acc_plane_set_money
-- ----------------------------
DROP TABLE IF EXISTS `acc_plane_set_money`;
CREATE TABLE `acc_plane_set_money` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `set_name` varchar(255) DEFAULT NULL COMMENT '設置的名稱',
  `start_date` date NOT NULL COMMENT '生效時間',
  `city` varchar(255) NOT NULL,
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='做单金额及对应的奖励（直升機）';

-- ----------------------------
-- Table structure for acc_plane_set_money_info
-- ----------------------------
DROP TABLE IF EXISTS `acc_plane_set_money_info`;
CREATE TABLE `acc_plane_set_money_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `money_id` int(11) NOT NULL,
  `formula_type` int(11) NOT NULL DEFAULT '1' COMMENT '1:>=   2:>  3:=',
  `value_name` int(11) NOT NULL,
  `value_money` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for acc_plane_set_other
-- ----------------------------
DROP TABLE IF EXISTS `acc_plane_set_other`;
CREATE TABLE `acc_plane_set_other` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `set_name` varchar(255) DEFAULT NULL COMMENT '設置的名稱',
  `z_display` int(11) NOT NULL DEFAULT '1' COMMENT '0:不顯示  1：顯示',
  `city` varchar(255) DEFAULT NULL,
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='直升机杂项配置（直升機）';

-- ----------------------------
-- Table structure for acc_plane_set_year
-- ----------------------------
DROP TABLE IF EXISTS `acc_plane_set_year`;
CREATE TABLE `acc_plane_set_year` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `set_name` varchar(255) DEFAULT NULL COMMENT '設置的名稱',
  `start_date` date NOT NULL COMMENT '生效時間',
  `city` varchar(255) NOT NULL,
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='做单金额及对应的奖励（直升機）';

-- ----------------------------
-- Table structure for acc_plane_set_year_info
-- ----------------------------
DROP TABLE IF EXISTS `acc_plane_set_year_info`;
CREATE TABLE `acc_plane_set_year_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `year_id` int(11) NOT NULL,
  `formula_type` int(11) NOT NULL DEFAULT '1' COMMENT '1:>=   2:>  3:=',
  `value_name` int(11) NOT NULL,
  `value_money` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
