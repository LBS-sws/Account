CREATE DATABASE account CHARACTER SET utf8 COLLATE utf8_general_ci;

GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON account.* TO 'swuser'@'localhost' IDENTIFIED BY 'swisher168';

use account;

/* 
	Account Type: CASH, BASIC ACCOUNT, General ACCOUNT, etc.
*/
DROP TABLE IF EXISTS acc_account_type;
CREATE TABLE acc_account_type(
	id int unsigned not null auto_increment primary key,
	acct_type_desc varchar(255) not null,
	rpt_cat char(5),
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*
INSERT INTO acc_account_type(id, acct_type_desc, rpt_cat, lcu, luu)
VALUES (1, '现金', 'CASH', 'admin', 'admin');

INSERT INTO acc_account_type(id, acct_type_desc, rpt_cat, lcu, luu)
VALUES (2, '基本戶', 'BANK', 'admin', 'admin');
*/

DROP TABLE IF EXISTS acc_account;
CREATE TABLE acc_account(
	id int unsigned not null auto_increment primary key,
	acct_type_id int unsigned not null,
	acct_no varchar(255),
	acct_name varchar(255),
	bank_name varchar(255),
	remarks varchar(1000),
	city char(5) not null,
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
INSERT INTO acc_account(id, acct_type_id, acct_no, acct_name, bank_name, remarks, city, lcu, luu)
VALUES (1, 1, '888888', '备用金户', null, null, '99999', 'admin', 'admin');

INSERT INTO acc_account(id, acct_type_id, acct_no, acct_name, bank_name, remarks, city, lcu, luu)
VALUES (2, 1, '999999', '客户收款现金户', null, null, '99999', 'admin', 'admin');
*/

DROP TABLE IF EXISTS acc_trans_type;
CREATE TABLE acc_trans_type(
	trans_type_code varchar(10) not null primary key,
	trans_type_desc varchar(255) not null,
	adj_type char(1) not null default 'N',
	trans_cat char(5) not null,
	counter_type varchar(10),
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*
INSERT INTO acc_trans_type(trans_type_code, trans_type_desc, adj_type, trans_cat, counter_type, lcu, luu) VALUES 
('OPEN', '初始金額', 'N', 'IN', null, 'admin', 'admin'),
('CASHIN', '现金收款', 'N', 'IN', 'CASHOUT', 'admin', 'admin'),
('CASHOUT', '现金付款', 'N', 'OUT', 'CASHIN', 'admin', 'admin'),
('CHQIN', '支票收款', 'N', 'IN', 'CHQOUT', 'admin', 'admin'),
('CHQOUT', '支票付款', 'N', 'OUT', 'CHQIN', 'admin', 'admin'),
('BANKIN', '银行转帐收款', 'N', 'IN', 'BANKOUT', 'admin', 'admin'),
('BANKOUT', '银行转帐付款', 'N', 'OUT', 'BANKIN', 'admin', 'admin'),
('ALIPAY', '阿里收款', 'N', 'IN', null, 'admin', 'admin'),
('WEIXIN', '微信收款', 'N', 'IN', null, 'admin', 'admin'),
('ADJIN', '收款调整', 'Y', 'IN', null, 'admin', 'admin');

INSERT INTO acc_trans_type(trans_type_code, trans_type_desc, adj_type, trans_cat, lcu, luu)
VALUES ('CHQRETI', '收款支票退回', 'Y', 'IN', 'admin', 'admin'),
('CHQRETO', '付款支票退回', 'Y', 'OUT', 'admin', 'admin');

*/

DROP TABLE IF EXISTS acc_trans_type_def;
CREATE TABLE acc_trans_type_def(
	trans_type_code varchar(10) not null,
	city char(5) not null,
	acct_id int unsigned,
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	UNIQUE KEY trans (trans_type_code, city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS acc_trans;
CREATE TABLE acc_trans(
	id int unsigned not null auto_increment primary key,
	trans_dt datetime not null,
	trans_type_code char(10) not null,
	acct_id int unsigned not null,
	trans_desc varchar(1000),
	amount decimal(11,2) default 0,
	status char(1) not null default 'A',
	city char(5) not null,
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS acc_trans_info;
CREATE TABLE acc_trans_info(
	trans_id int unsigned not null,
	field_id varchar(30) not null,
	field_value varchar(2000),
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	UNIQUE KEY trans (trans_id, field_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS acc_request;
CREATE TABLE acc_request(
	id int unsigned not null auto_increment primary key,
	req_dt datetime not null,
	req_user varchar(30) not null,
	payee_type char(10) not null,
	payee_id int unsigned not null,
	payee_name varchar(255),
	trans_type_code char(10) not null,
	item_desc varchar(1000),
	amount decimal(11,2) default 0,
	city char(5) not null,
	status char(1) not null default 'Y',
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS acc_request_info;
CREATE TABLE acc_request_info(
	req_id int unsigned not null,
	field_id varchar(30) not null,
	field_value varchar(2000),
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	UNIQUE KEY request (req_id, field_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS acc_account_item;
CREATE TABLE acc_account_item (
	code varchar(20) NOT NULL primary key,
	name varchar(255) NOT NULL,
	item_type char(1) NOT NULL default 'I',
	acct_code varchar(20) NOT NULL,
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS acc_account_code;
CREATE TABLE acc_account_code (
	code varchar(20) NOT NULL primary key,
	name varchar(255) NOT NULL,
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into acc_account_code(code, name, lcu, luu) values('5001','主营业务收入','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('500101','服务收入','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('50010101','洁净服务','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('50010102','灭虫服务','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('50010103','飄盈香服務','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('50010104','甲醛服务','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('50010105','杂项服务','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('500102','销售收入','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('50010201','纸品销售业务','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('50010202','杂项销售','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('50010203','甲醛机器销售','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('50010204','纸品机器销售','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('50010205','清洁机器销售','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('50010206','灭虫机器销售','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('50010207','灭虫配件销售','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('50010208','清洁物料销售','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('50010209','灭虫物料销售','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('5051','其他业务收入','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('5111','投资收益','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('5301','营业外收入','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('530101','政府补助','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('530102','收回坏账损失','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('530103','汇兑收益','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('530104','非流动资产处置净收益','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('530105','减免税款','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('5401','主营业务成本','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('540101','服务成本','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('54010101','洁净服务','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('54010102','灭虫服务','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('54010103','飄盈香服務','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('54010104','甲醛服务','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('54010105','杂项服务','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('540102','销售成本','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('54010201','纸品销售业务','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('54010202','杂项销售','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('54010203','甲醛机器销售成本','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('54010204','纸品机器销售成本','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('54010205','清洁机器销售成本','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('54010206','灭虫机器销售成本','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('54010207','灭虫配件销售成本','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('54010208','清洁物料销售成本','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('54010209','灭虫物料销售成本','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('5402','其他业务成本','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('5403','营业税金及附加','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('540301','营业税','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('540302','城建税','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('540303','教育费附加','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('540304','地方教育费附加','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('540305','增值税','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('540306','堤围费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('5601','销售费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560101','工资(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56010101','技术员基本工资(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56010102','技术员工资提成(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56010103','技术员社保费用(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56010104','技术员其它费用(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56010105','销售业务基本工资(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56010106','销售业务工资提成(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56010107','销售业务员社保费用(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56010108','销售业务员其它费用(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560102','租金及管理费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560103','水电费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560104','电话网络通讯费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560105','折旧費(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560106','交通费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560107','差旅费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560108','交际-招待费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560109','办公费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560110','福利费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560111','文具及印刷费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560112','自置车辆报销费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560113','自置车辆维修费及保险(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560114','其他非自置车辆报销费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560115','误餐费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560116','邮费及快递费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560117','会务及证照年检费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560118','招聘人材费用(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560151','特许权使用(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56015101','特许权使用-洁净服务(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56015102','特许权使用-灭虫服务(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56015103','特许权使用-飄盈香服務(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56015104','特许权使用-甲醛服务(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56015105','特许权使用-杂项服务(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56015121','特许权使用-销售业务(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56015122','特许权使用-杂项销售(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560152','运杂费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560153','样品及测试费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560154','销售宣传，赞助及推广(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560155','进口商品税项及代理费(销售)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('5602','管理费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560201','工资(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56020101','办公室和运输及货仓工资(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56020102','办公室和运输及货仓工资-提成或奖金(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56020103','办公室和运输及货仓工资-社保(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56020104','办公室和运输及货仓工资-其它(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560202','租金及管理费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560203','水电费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560204','电话网络通讯费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560205','折旧費(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560206','交通费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560207','差旅费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560208','交际-招待费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560209','办公费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560210','福利费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560211','文具及印刷费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560212','自置车辆报销费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560213','自置车辆维修费及保险(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560214','其他非自置车辆报销费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560215','误餐费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560216','邮费及快递费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560217','会务及证照年检费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560218','招聘人材费用(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560219','开办费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560220','低值易耗品(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560250','公司制服及配套(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560251','保养维修费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560252','宿舍费及其它(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560253','住房公积金(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560254','教育-培训经费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560255','保险费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560256','工本费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560257','企业广告宣传(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560258','税金-非主营业务，社保及个人(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560259','专业人士-顾问费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560260','审计费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560261','赔偿费(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560262','长期待摊费用(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560263','其他(管理)','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('5603','财务费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560301','利息费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560302','手续费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560303','现金折扣','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560304','汇兑损失','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('5711','营业外支出','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('571101','坏账损失','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('571102','无法收回的长期债券投资损失','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('571103','无法收回的长期股权投资损失','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('571104','自然灾害等不可抗力因素造成的损失','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('571105','税收滞纳金','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('5801','所得税费用','admin','admin');

DROP TABLE IF EXISTS acc_approver;
CREATE TABLE acc_approver (
	id int unsigned NOT NULL auto_increment primary key,
	city char(5) NOT NULL,
	approver_type varchar(20) NOT NULL,
	username varchar(30),
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	UNIQUE KEY approver (city, approver_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS acc_trans_audit_hdr;
CREATE TABLE acc_trans_audit_hdr (
	id int unsigned NOT NULL auto_increment primary key,
	audit_dt datetime NOT NULL,
	acct_id int unsigned NOT NULL,
	balance decimal(11,2) default 0,
	req_user varchar(30),
	audit_user varchar(30),
	city char(5) NOT NULL,
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS acc_trans_audit_dtl;
CREATE TABLE acc_trans_audit_dtl (
	hdr_id int unsigned NOT NULL,
	trans_id int unsigned NOT NULL,
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	UNIQUE KEY audit_dtl (hdr_id, trans_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS acc_queue;
CREATE TABLE acc_queue (
	id int unsigned NOT NULL auto_increment primary key,
	rpt_desc varchar(250) NOT NULL,
	req_dt datetime,
	fin_dt datetime,
	username varchar(30) NOT NULL,
	status char(1) NOT NULL,
	rpt_type varchar(10) NOT NULL,
	ts timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	rpt_content longblob
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS acc_queue_param;
CREATE TABLE acc_queue_param (
	id int unsigned NOT NULL auto_increment primary key,
	queue_id int unsigned NOT NULL,
	param_field varchar(50) NOT NULL,
	param_value varchar(500),
	ts timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS acc_queue_user;
CREATE TABLE acc_queue_user (
	id int unsigned NOT NULL auto_increment primary key,
	queue_id int unsigned NOT NULL,
	username varchar(30) NOT NULL,
	ts timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS acc_email_queue;
CREATE TABLE acc_email_queue (
	id int unsigned auto_increment NOT NULL primary key,
	request_dt datetime NOT NULL,
	from_addr varchar(255) NOT NULL,
	to_addr varchar(1000) NOT NULL,
	cc_addr varchar(1000),
	subject varchar(1000),
	description varchar(1000),
	message varchar(5000),
	status char(1) default 'N',
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER //
DROP FUNCTION IF EXISTS AccountBalance //
CREATE FUNCTION AccountBalance(p_acct_id int unsigned, p_city char(5), p_fm_dt datetime, p_to_dt datetime) RETURNS decimal(11,2)
BEGIN
	DECLARE balance decimal(11,2);
	SET balance = (
		SELECT sum(case b.trans_cat
					when 'IN' then (if(b.adj_type='N',a.amount,-1*a.amount))
					when 'OUT' then (if(b.adj_type='N',-1*a.amount,a.amount))
				end)
		FROM acc_trans a, acc_trans_type b
		WHERE a.acct_id = p_acct_id and a.trans_type_code = b.trans_type_code
		and a.trans_dt >= p_fm_dt and a.trans_dt <= p_to_dt
		and a.status <> 'V' and a.city = p_city
	);
	RETURN balance;
END //
DELIMITER ;

DELIMITER //
DROP FUNCTION IF EXISTS AccountTransAmount //
CREATE FUNCTION AccountTransAmount(p_cat char(5), p_acct_id int unsigned, p_city char(5), p_fm_dt datetime, p_to_dt datetime) RETURNS decimal(11,2)
BEGIN
	DECLARE balance decimal(11,2);
	SET balance = (
		SELECT sum(if(b.adj_type='N',a.amount,-1*a.amount))
		FROM acc_trans a, acc_trans_type b
		WHERE a.acct_id = p_acct_id and a.trans_type_code = b.trans_type_code
		and a.trans_dt >= p_fm_dt and a.trans_dt <= p_to_dt
		and a.status <> 'V' and a.city = p_city
		and b.trans_cat = p_cat
	);
	RETURN balance;
END //
DELIMITER ;