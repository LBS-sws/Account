
-- ----------------------------
-- Table structure for acc_service_comm_dtl
-- ----------------------------
ALTER TABLE acc_service_comm_dtl ADD COLUMN service_reward decimal(5,3) NOT NULL DEFAULT 0.000 COMMENT '服务奖励点' AFTER new_calc;
ALTER TABLE acc_service_comm_dtl ADD COLUMN point decimal(5,3) NOT NULL DEFAULT 0.000 COMMENT '销售提成激励点' AFTER new_calc;
