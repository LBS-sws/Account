
-- ----------------------------
-- Table structure for acc_plane
-- ----------------------------
ALTER TABLE acc_plane ADD COLUMN old_pay_wage float(15,2) NOT NULL DEFAULT 0.00 COMMENT '原机制应发工资' AFTER plane_sum;
