
-- ----------------------------
-- Table structure for acc_expense_info
-- ----------------------------
ALTER TABLE acc_expense_info ADD COLUMN trip_id int(11) NULL DEFAULT NULL COMMENT '出差id' AFTER set_id;
