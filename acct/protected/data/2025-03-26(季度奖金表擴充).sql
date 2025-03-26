delete from acc_performance_bonus where id>0;

CREATE TABLE `acc_performance_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bonus_id` int(10) NOT NULL,
  `year_no` int(4) NOT NULL DEFAULT '2000',
  `month_no` int(2) NOT NULL DEFAULT '1' COMMENT '季度',
  `bonus_sum` double(11,2) DEFAULT '0.00' COMMENT '累计业绩',
  `bonus_amt` double(11,2) DEFAULT '0.00' COMMENT '奖金金额',
  `bonus_out` double(11,2) DEFAULT '0.00' COMMENT '实际发放金额',
  `status_type` int(11) DEFAULT '0' COMMENT '0:草稿 1：固定',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='员工季度奖金表';