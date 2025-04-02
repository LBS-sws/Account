
-- ----------------------------
-- Table structure for acc_plane
-- ----------------------------
ALTER TABLE acc_plane ADD COLUMN old_money_value float(15,2) NOT NULL DEFAULT 0.00 COMMENT '做单金额(调整前)' AFTER plane_sum;
ALTER TABLE acc_plane ADD COLUMN old_take_amt float(15,2) NOT NULL DEFAULT 0.00 COMMENT '派单系统提成(调整前)' AFTER plane_sum;
ALTER TABLE acc_plane ADD COLUMN take_amt float(15,2) NOT NULL DEFAULT 0.00 COMMENT '派单系统提成' AFTER plane_sum;
ALTER TABLE acc_plane ADD COLUMN plane_status int(2) NOT NULL DEFAULT 0 COMMENT '0:草稿，1：待审核，2：已审核，3：已拒绝' AFTER plane_sum;
ALTER TABLE acc_plane ADD COLUMN reject_txt text NULL DEFAULT NULL COMMENT '拒绝原因' AFTER plane_sum;

update acc_plane set old_money_value=money_value,lud=lud where id>0;
update acc_plane set plane_status=2,lud=lud where plane_date<='2025-01-31';
update acc_plane set money_value=null,lud=lud where plane_status=0;


CREATE TABLE `acc_plane_detail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `plane_id` int(11) NOT NULL,
  `take_txt` text NOT NULL,
  `take_amt` float(13,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='直升機提成金額';

CREATE TABLE `acc_plane_money` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `plane_id` int(11) NOT NULL,
  `money_txt` text NOT NULL,
  `money_amt` float(13,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='直升機做单金额';