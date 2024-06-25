
-- ----------------------------
-- Table structure for acc_expense
-- ----------------------------
ALTER TABLE acc_expense ADD COLUMN payment_id int(11) NULL DEFAULT NULL COMMENT '扣款申请id' AFTER acc_id;
ALTER TABLE acc_expense ADD COLUMN payment_type varchar(10) NULL DEFAULT NULL COMMENT '扣款类型' AFTER acc_id;
ALTER TABLE acc_expense ADD COLUMN payment_date date NULL DEFAULT NULL COMMENT '扣款时间' AFTER acc_id;

-- ----------------------------
-- Table structure for acc_send_set_jd
-- ----------------------------
DROP TABLE IF EXISTS `acc_send_set_jd`;
CREATE TABLE `acc_send_set_jd` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table_id` varchar(255) NOT NULL,
  `set_type` varchar(255) NOT NULL DEFAULT 'warehouse',
  `field_id` varchar(255) NOT NULL,
  `field_value` varchar(255) DEFAULT NULL,
  `field_type` varchar(255) DEFAULT 'text',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` datetime DEFAULT NULL,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COMMENT='金蝶关联的配置表';
