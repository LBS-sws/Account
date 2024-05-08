
-- ----------------------------
-- Table structure for acc_expense
-- ----------------------------
ALTER TABLE acc_expense ADD COLUMN payment_id int(11) NULL DEFAULT NULL COMMENT '扣款申请id' AFTER acc_id;
ALTER TABLE acc_expense ADD COLUMN payment_type varchar(10) NULL DEFAULT NULL COMMENT '扣款类型' AFTER acc_id;
ALTER TABLE acc_expense ADD COLUMN payment_date date NULL DEFAULT NULL COMMENT '扣款时间' AFTER acc_id;
