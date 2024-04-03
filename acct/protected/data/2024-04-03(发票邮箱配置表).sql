
-- ----------------------------
-- Table structure for acc_invoice
-- ----------------------------
ALTER TABLE acc_invoice ADD COLUMN print_email int(1) NOT NULL DEFAULT 0 COMMENT '是否发送邮件 0：未发送 1：已发送' AFTER bowl;

CREATE TABLE `acc_invoice_email` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `start_dt` date NULL DEFAULT NULL COMMENT '生效日期' ,
  `email_text` varchar(2000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '邮箱，多个邮箱;分割',
  `remarks` varchar(2000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  `lcu` varchar(50) DEFAULT NULL,
  `luu` varchar(50) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) 
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
COMMENT='发票打印后需要邮箱通知'
AUTO_INCREMENT=1
ROW_FORMAT=COMPACT
;
