/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : accountdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2025-07-23 12:38:24
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for acc_group_below
-- ----------------------------
DROP TABLE IF EXISTS `acc_group_below`;
CREATE TABLE `acc_group_below` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) NOT NULL,
  `city` char(5) NOT NULL,
  `year_no` int(4) NOT NULL DEFAULT '2000',
  `month_no` int(2) NOT NULL DEFAULT '1' COMMENT '季度',
  `bonus_amount` double(11,2) DEFAULT '0.00',
  `new_json` text COMMENT '新签合约详情',
  `bonus_json` text,
  `status_type` int(11) DEFAULT '0' COMMENT '0:草稿 1：固定',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='销售团队提成表';

-- ----------------------------
-- Table structure for acc_group_set
-- ----------------------------
DROP TABLE IF EXISTS `acc_group_set`;
CREATE TABLE `acc_group_set` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `set_type` int(2) NOT NULL DEFAULT '1' COMMENT '1:销售  2：技术员',
  `start_date` date NOT NULL COMMENT '开始時間',
  `end_date` date NOT NULL COMMENT '结束时间',
  `employee_id` int(11) NOT NULL COMMENT '团队负责人',
  `employee_type` int(2) DEFAULT NULL COMMENT '负责人类型',
  `group_staff_name` text,
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='销售团队配置表';

-- ----------------------------
-- Table structure for acc_group_set_info
-- ----------------------------
DROP TABLE IF EXISTS `acc_group_set_info`;
CREATE TABLE `acc_group_set_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `set_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `employee_type` int(2) NOT NULL DEFAULT '1' COMMENT '员工类型：1：自动获取 2：新入职 3：老员工',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
