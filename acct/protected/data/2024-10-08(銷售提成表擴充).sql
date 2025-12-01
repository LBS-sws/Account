
-- ----------------------------
-- Table structure for acc_service_comm_dtl
-- ----------------------------
ALTER TABLE acc_service_comm_dtl ADD COLUMN supplement_money decimal(11,2) NULL DEFAULT 0.00 COMMENT '补充金额' AFTER product_amount;

UPDATE acc_service_comm_dtl a
SET a.supplement_money=(
	SELECT IFNULL(SUM(b.commission),0) FROM acc_salestable b WHERE a.hdr_id = b.hdr_id GROUP BY b.hdr_id
);

UPDATE acc_service_comm_dtl set supplement_money=0 where supplement_money is null;

ALTER TABLE acc_service_comm_dtl ADD COLUMN lbs_new_amount decimal(11,2) NULL DEFAULT 0.00 COMMENT '利比斯提成' AFTER new_amount;
ALTER TABLE acc_service_comm_dtl ADD COLUMN lbs_new_money decimal(11,2) NULL DEFAULT 0.00 COMMENT '利比斯业绩' AFTER new_amount;

ALTER TABLE acc_service_comm_dtl ADD COLUMN recovery_amount decimal(11,2) NULL DEFAULT 0.00 COMMENT '恢复提成' AFTER new_amount;
ALTER TABLE acc_service_comm_dtl ADD COLUMN perrecovery_amount decimal(11,2) NULL DEFAULT 0.00 COMMENT '跨区恢复提成' AFTER new_amount;
