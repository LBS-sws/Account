/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : accountdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2025-02-27 12:07:43
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for acc_appraisal
-- ----------------------------
DROP TABLE IF EXISTS `acc_appraisal`;
CREATE TABLE `acc_appraisal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) NOT NULL,
  `city` char(5) NOT NULL,
  `year_no` int(4) NOT NULL DEFAULT '2000',
  `month_no` int(2) NOT NULL DEFAULT '1' COMMENT '季度',
  `new_amount` double(11,2) DEFAULT '0.00' COMMENT '新签合同金额',
  `new_sum` int(11) DEFAULT '0' COMMENT '新签合同数量',
  `visit_sum` int(11) DEFAULT '0' COMMENT '销售拜访数量',
  `num_score` int(2) DEFAULT NULL COMMENT '日常工作完成率',
  `appraisal_amount` double(11,2) DEFAULT '0.00',
  `new_json` text COMMENT '新签合约详情',
  `appraisal_json` text COMMENT '考核占比详情',
  `status_type` int(11) DEFAULT '0' COMMENT '0:草稿 1：固定',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COMMENT='销售顾问绩效考核表';


ALTER TABLE acc_appraisal ADD COLUMN last_num_score int(2) DEFAULT '0' COMMENT '补上月打分' AFTER status_type;
ALTER TABLE acc_appraisal ADD COLUMN last_score_money double(11,2) DEFAULT '0.00'COMMENT '上月补分金额' AFTER status_type;
