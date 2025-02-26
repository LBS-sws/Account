/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : accountdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2025-02-26 14:39:22
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for acc_performance_bonus
-- ----------------------------
DROP TABLE IF EXISTS `acc_performance_bonus`;
CREATE TABLE `acc_performance_bonus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) NOT NULL,
  `city` char(5) NOT NULL,
  `year_no` int(4) NOT NULL DEFAULT '2000',
  `quarter_no` int(2) NOT NULL DEFAULT '1' COMMENT '季度',
  `new_amount` double(11,2) DEFAULT '0.00',
  `bonus_amount` double(11,2) DEFAULT '0.00',
  `new_json` text COMMENT '新签合约详情',
  `bonus_json` text COMMENT '奖金范围详情',
  `status_type` int(11) DEFAULT '0' COMMENT '0:草稿 1：固定',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='员工季度奖金表';

-- ----------------------------
-- Records of acc_performance_bonus
-- ----------------------------

-- ----------------------------
-- Table structure for acc_performance_dtl
-- ----------------------------
DROP TABLE IF EXISTS `acc_performance_dtl`;
CREATE TABLE `acc_performance_dtl` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hdr_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `operator` char(2) NOT NULL,
  `new_amount` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '新签总金额',
  `bonus_amount` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '奖金金额',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_service_rate_dtl_01` (`hdr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of acc_performance_dtl
-- ----------------------------
INSERT INTO `acc_performance_dtl` VALUES ('32', '8', 'per_now_money', 'LE', '120000.00', '0.00', 'shenchao', 'shenchao', '2025-02-25 10:56:27', '2025-02-26 14:24:42');
INSERT INTO `acc_performance_dtl` VALUES ('33', '8', 'per_now_money', 'GT', '120000.00', '1500.00', 'shenchao', 'shenchao', '2025-02-25 10:56:27', '2025-02-25 10:56:27');
INSERT INTO `acc_performance_dtl` VALUES ('34', '8', 'per_now_money', 'GT', '180000.00', '3000.00', 'shenchao', 'shenchao', '2025-02-25 10:56:27', '2025-02-25 10:56:27');
INSERT INTO `acc_performance_dtl` VALUES ('35', '8', 'per_now_money', 'GT', '240000.00', '4500.00', 'shenchao', 'shenchao', '2025-02-25 10:56:27', '2025-02-25 10:56:27');
INSERT INTO `acc_performance_dtl` VALUES ('36', '8', 'per_now_money', 'GT', '300000.00', '7500.00', 'shenchao', 'shenchao', '2025-02-25 10:56:27', '2025-02-25 10:57:46');
INSERT INTO `acc_performance_dtl` VALUES ('37', '8', 'per_now_money', 'GT', '360000.00', '9000.00', 'shenchao', 'shenchao', '2025-02-25 10:56:27', '2025-02-25 10:57:46');

-- ----------------------------
-- Table structure for acc_performance_set
-- ----------------------------
DROP TABLE IF EXISTS `acc_performance_set`;
CREATE TABLE `acc_performance_set` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `city` char(5) NOT NULL,
  `start_dt` datetime NOT NULL,
  `city_type` int(11) NOT NULL DEFAULT '0' COMMENT '0:全部',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of acc_performance_set
-- ----------------------------
INSERT INTO `acc_performance_set` VALUES ('8', '全国通用配置', 'ZH', '2020-01-01 00:00:00', '0', 'shenchao', 'shenchao', '2025-02-25 10:56:27', '2025-02-25 10:56:27');
