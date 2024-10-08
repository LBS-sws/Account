
-- ----------------------------
-- Table structure for acc_service_comm_dtl
-- ----------------------------
ALTER TABLE acc_service_comm_dtl ADD COLUMN supplement_money decimal(11,2) NULL DEFAULT 0.00 COMMENT '补充金额' AFTER product_amount;

UPDATE acc_service_comm_dtl a
SET a.supplement_money=(
	SELECT IFNULL(SUM(b.commission),0) FROM acc_salestable b WHERE a.hdr_id = b.hdr_id GROUP BY b.hdr_id
);

UPDATE acc_service_comm_dtl set supplement_money=0 where supplement_money is null;
