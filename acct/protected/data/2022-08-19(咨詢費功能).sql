/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : accountdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2022-08-19 12:47:06
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for acc_consult
-- ----------------------------
DROP TABLE IF EXISTS `acc_consult`;
CREATE TABLE `acc_consult` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `consult_code` varchar(255) DEFAULT NULL COMMENT '咨詢編號',
  `apply_date` date NOT NULL COMMENT '申請日期',
  `customer_code` varchar(200) DEFAULT NULL COMMENT '客户识别号',
  `consult_money` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '合計金額',
  `apply_city` varchar(5) NOT NULL COMMENT '开票方（申請城市的編號）',
  `audit_city` varchar(5) NOT NULL COMMENT '收票方（審核城市的編號）',
  `audit_date` datetime DEFAULT NULL COMMENT '審核時間',
  `status` int(2) NOT NULL DEFAULT '0' COMMENT '0：草稿 1：已發送 2：已審核 3：已拒絕',
  `remark` text COMMENT '申請備註',
  `reject_remark` text COMMENT '拒絕說明',
  `city` varchar(255) DEFAULT NULL,
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='做单金额及对应的奖励（直升機）';

-- ----------------------------
-- Table structure for acc_consult_info
-- ----------------------------
DROP TABLE IF EXISTS `acc_consult_info`;
CREATE TABLE `acc_consult_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `consult_id` int(11) NOT NULL,
  `set_id` int(11) NOT NULL,
  `good_money` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '物品金額',
  `lcu` varchar(255) DEFAULT '0' COMMENT '無用字段',
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for acc_consult_set
-- ----------------------------
DROP TABLE IF EXISTS `acc_consult_set`;
CREATE TABLE `acc_consult_set` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `good_name` varchar(255) DEFAULT NULL COMMENT '商品名称',
  `z_display` int(11) NOT NULL DEFAULT '1' COMMENT '0:不顯示  1：顯示',
  `z_index` int(5) NOT NULL DEFAULT '0' COMMENT '層級，數值越低顯示越靠前',
  `city` varchar(255) DEFAULT NULL,
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='直升机杂项配置（直升機）';
