
-- ----------------------------
-- Table structure for acc_invoice
-- ----------------------------
ALTER TABLE acc_invoice ADD COLUMN head_type int(2) NOT NULL DEFAULT 0 COMMENT '發票抬頭 0：佳駿企業有限公司 1：LBS (Macau) Limited' AFTER invoice_no;
