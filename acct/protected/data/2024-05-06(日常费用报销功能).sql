/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : accountdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2024-05-06 12:46:45
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for acc_expense
-- ----------------------------
DROP TABLE IF EXISTS `acc_expense`;
CREATE TABLE `acc_expense` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `exp_code` varchar(255) DEFAULT NULL COMMENT '报销编号',
  `employee_id` int(11) NOT NULL,
  `apply_date` date NOT NULL COMMENT '申请日期',
  `audit_user` text COMMENT '审核人员；逗号分割',
  `audit_json` text,
  `current_num` int(11) NOT NULL DEFAULT '0' COMMENT '当前审核人(位置)',
  `current_username` varchar(255) DEFAULT NULL COMMENT '当前审核人',
  `city` varchar(50) DEFAULT NULL,
  `status_type` int(11) NOT NULL DEFAULT '0' COMMENT '0:草稿 1：待确认 2：待审核 7：已拒绝 8:已审核 9：已完成',
  `amt_money` decimal(11,2) NOT NULL COMMENT '总金额',
  `acc_id` int(11) DEFAULT NULL COMMENT '付款账号',
  `remark` text,
  `reject_note` text,
  `exp_one_num` int(11) NOT NULL DEFAULT '0' COMMENT '附件数量',
  `exp_two_num` int(11) NOT NULL DEFAULT '0' COMMENT '税票数量',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='日常费用报销表';

-- ----------------------------
-- Table structure for acc_expense_audit
-- ----------------------------
DROP TABLE IF EXISTS `acc_expense_audit`;
CREATE TABLE `acc_expense_audit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `exp_id` int(11) NOT NULL,
  `audit_user` varchar(255) NOT NULL COMMENT '审核账号',
  `audit_str` varchar(255) DEFAULT NULL COMMENT '审核人标签',
  `audit_date` datetime NOT NULL COMMENT '审核时间',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='日常费用报销审核表';

-- ----------------------------
-- Table structure for acc_expense_history
-- ----------------------------
DROP TABLE IF EXISTS `acc_expense_history`;
CREATE TABLE `acc_expense_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `exp_id` int(11) NOT NULL,
  `history_type` int(11) NOT NULL DEFAULT '1' COMMENT '记录类型 1：正常 2:审核',
  `history_text` text NOT NULL COMMENT '记录内容',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COMMENT='日常费用报销记录表';

-- ----------------------------
-- Table structure for acc_expense_info
-- ----------------------------
DROP TABLE IF EXISTS `acc_expense_info`;
CREATE TABLE `acc_expense_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `exp_id` int(11) NOT NULL,
  `set_id` int(11) NOT NULL COMMENT '费用归属',
  `info_date` date NOT NULL,
  `amt_type` int(11) NOT NULL COMMENT '费用类别 0：本地费用 1：差旅费用 2：办公费 3：快递费 4：通讯费 5：其它',
  `info_remark` text COMMENT '摘要',
  `info_amt` decimal(11,2) NOT NULL COMMENT '金额',
  `info_json` text COMMENT '金额详情的json',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='日常费用报销表';

-- ----------------------------
-- Table structure for acc_set_audit
-- ----------------------------
DROP TABLE IF EXISTS `acc_set_audit`;
CREATE TABLE `acc_set_audit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) NOT NULL,
  `audit_user_str` text COMMENT '审核人',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='指定审核人';

-- ----------------------------
-- Table structure for acc_set_audit_info
-- ----------------------------
DROP TABLE IF EXISTS `acc_set_audit_info`;
CREATE TABLE `acc_set_audit_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `set_id` int(10) NOT NULL,
  `audit_user` varchar(255) NOT NULL,
  `audit_tag` varchar(255) DEFAULT NULL,
  `info_type` int(255) NOT NULL DEFAULT '0' COMMENT '0:审核人 1：抄送人',
  `amt_bool` int(11) NOT NULL DEFAULT '0' COMMENT '0:不限制金额 1：限制金额',
  `amt_min` decimal(10,2) DEFAULT NULL,
  `amt_max` decimal(10,2) DEFAULT NULL,
  `z_index` int(11) NOT NULL DEFAULT '0' COMMENT '层级（数值越高，审核越靠前）',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='指定审核人';

-- ----------------------------
-- Table structure for acc_set_name
-- ----------------------------
DROP TABLE IF EXISTS `acc_set_name`;
CREATE TABLE `acc_set_name` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type_str` varchar(255) NOT NULL COMMENT '配置类型。expense:日常报销',
  `z_display` int(11) NOT NULL DEFAULT '1' COMMENT '0:隐藏 1：显示',
  `z_index` int(11) DEFAULT '0' COMMENT '层级(数值越高越靠前）',
  `city` varchar(50) DEFAULT NULL,
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='费用归属设置';
