/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : accountdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2025-03-26 10:46:35
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for acc_performance_bonus_info
-- ----------------------------
DROP TABLE IF EXISTS `acc_performance_bonus_info`;
CREATE TABLE `acc_performance_bonus_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bonus_id` int(10) NOT NULL,
  `year_no` int(4) NOT NULL DEFAULT '2000',
  `month_no` int(2) NOT NULL DEFAULT '1' COMMENT '季度',
  `bonus_sum` double(11,2) DEFAULT '0.00' COMMENT '总提成',
  `bonus_amt` double(11,2) DEFAULT '0.00' COMMENT '奖金金额',
  `bonus_out` double(11,2) DEFAULT '0.00' COMMENT '实际发放金额',
  `status_type` int(11) DEFAULT '0' COMMENT '0:草稿 1：固定',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COMMENT='员工季度奖金表';
