/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : accountdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2021-07-15 09:08:45
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for acc_invoice
-- ----------------------------
DROP TABLE IF EXISTS `acc_invoice`;
CREATE TABLE `acc_invoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(100) DEFAULT NULL COMMENT '客戶編號',
  `invoice_dt` date DEFAULT NULL COMMENT '發票日期',
  `invoice_no` varchar(100) DEFAULT NULL COMMENT '發票號碼',
  `invoice_amt` decimal(10,2) DEFAULT '0.00' COMMENT '發票金額',
  `invoice_to_name` varchar(100) DEFAULT NULL COMMENT '客戶名稱',
  `invoice_to_addr` varchar(255) DEFAULT NULL COMMENT '客戶地址',
  `invoice_to_tel` varchar(100) DEFAULT NULL COMMENT '客戶電話',
  `remarks` text COMMENT '特別說明',
  `payment_term` varchar(100) DEFAULT NULL COMMENT '支付方式',
  `sales_id` varchar(100) DEFAULT NULL COMMENT '銷售員編號',
  `sales_name` varchar(100) DEFAULT NULL COMMENT '銷售員姓名',
  `addr` varchar(255) DEFAULT NULL COMMENT '聯繫地址',
  `tel` varchar(100) DEFAULT NULL COMMENT '聯繫電話',
  `name_zh` varchar(100) DEFAULT NULL COMMENT '聯繫人',
  `old_no` varchar(255) DEFAULT NULL COMMENT '舊編號',
  `page_num` int(4) DEFAULT '0',
  `dates` date DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `toiletRoom` varchar(10) DEFAULT NULL COMMENT '洗手間數目',
  `aerosal` varchar(10) DEFAULT NULL COMMENT '噴機',
  `ttl` varchar(10) DEFAULT NULL COMMENT '總數',
  `ptd` varchar(10) DEFAULT NULL COMMENT '抹手紙機',
  `abhsd` varchar(10) DEFAULT NULL COMMENT '除菌皂液機',
  `sink` varchar(10) DEFAULT NULL COMMENT '洗手盤',
  `td` varchar(10) DEFAULT NULL COMMENT '廁紙機',
  `hsd` varchar(10) DEFAULT NULL COMMENT '皂液機',
  `urinal` varchar(10) DEFAULT NULL COMMENT '尿缸',
  `hand` varchar(10) DEFAULT NULL COMMENT '手部消毒機',
  `baf` varchar(10) DEFAULT NULL COMMENT '電動清新機',
  `bowl` varchar(10) DEFAULT NULL COMMENT '坐廁數量',
  `lcu` varchar(100) DEFAULT NULL,
  `luu` varchar(100) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8 COMMENT='關於澳門發票的服務';

-- ----------------------------
-- Table structure for acc_invoice_type
-- ----------------------------
DROP TABLE IF EXISTS `acc_invoice_type`;
CREATE TABLE `acc_invoice_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(100) NOT NULL COMMENT '客戶編號',
  `product_code` varchar(100) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `unit` varchar(100) DEFAULT NULL,
  `qty` varchar(100) DEFAULT NULL COMMENT '數量',
  `unit_price` decimal(10,2) DEFAULT '0.00' COMMENT '單價',
  `package` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT '0.00' COMMENT '總價',
  `is_service` varchar(10) DEFAULT NULL,
  `lcu` varchar(100) DEFAULT NULL,
  `luu` varchar(100) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8 COMMENT='關於澳門發票的服務';
