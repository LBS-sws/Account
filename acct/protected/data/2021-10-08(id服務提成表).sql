/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : accountdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2021-10-08 17:42:52
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for acc_serviceid_comm_dtl
-- ----------------------------
DROP TABLE IF EXISTS `acc_serviceid_comm_dtl`;
CREATE TABLE `acc_serviceid_comm_dtl` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `hdr_id` int(10) unsigned NOT NULL,
  `file_type` int(2) NOT NULL DEFAULT '0' COMMENT '是否參與提成總金額計算 1：是',
  `file_name` varchar(20) NOT NULL COMMENT '字段标识',
  `file_value` decimal(11,3) NOT NULL COMMENT '字段对应的金额',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for acc_serviceid_comm_hdr
-- ----------------------------
DROP TABLE IF EXISTS `acc_serviceid_comm_hdr`;
CREATE TABLE `acc_serviceid_comm_hdr` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `year_no` smallint(5) unsigned NOT NULL,
  `month_no` tinyint(3) unsigned NOT NULL,
  `employee_id` int(10) NOT NULL,
  `sum_amount` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '提成总金额',
  `commit` int(1) NOT NULL DEFAULT '0' COMMENT '是否已经计算 1：是',
  `city` char(5) NOT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for acc_serviceid_rate_dtl
-- ----------------------------
DROP TABLE IF EXISTS `acc_serviceid_rate_dtl`;
CREATE TABLE `acc_serviceid_rate_dtl` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hdr_id` int(10) unsigned NOT NULL,
  `type_id` int(11) NOT NULL DEFAULT '0' COMMENT '客戶類型id',
  `operator` char(2) NOT NULL COMMENT '符號 LE 或 GT',
  `month_num` int(11) NOT NULL DEFAULT '0' COMMENT '月数',
  `rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '提成比例',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_service_rate_dtl_01` (`hdr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for acc_serviceid_rate_hdr
-- ----------------------------
DROP TABLE IF EXISTS `acc_serviceid_rate_hdr`;
CREATE TABLE `acc_serviceid_rate_hdr` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `only_num` int(11) NOT NULL DEFAULT '0' COMMENT '是否通用 1：通用',
  `name` varchar(11) DEFAULT NULL COMMENT '名稱',
  `city` char(5) NOT NULL,
  `start_dt` datetime NOT NULL COMMENT '生效日期（暂时不使用）',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
