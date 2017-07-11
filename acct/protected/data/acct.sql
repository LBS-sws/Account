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
	item_type char(2) NOT NULL default 'BI',
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

insert into acc_account_code(code, name, lcu, luu) values(
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

DROP TABLE IF EXISTS acc_import_queue;
CREATE TABLE acc_import_queue (
	id int unsigned NOT NULL auto_increment primary key,
	import_type varchar(20) NOT NULL,
	req_dt datetime,
	fin_dt datetime,
	username varchar(30) NOT NULL,
	status char(1) NOT NULL,
	class_name varchar(250) NOT NULL,
	ts timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	file_name varchar(255),
	log_content varchar(5000),
	file_content longblob
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS acc_import_queue_param;
CREATE TABLE acc_import_queue_param (
	id int unsigned NOT NULL auto_increment primary key,
	queue_id int unsigned NOT NULL,
	param_field varchar(50) NOT NULL,
	param_value varchar(5000),
	ts timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
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
DROP FUNCTION IF EXISTS AccountBalanceByLCD //
CREATE FUNCTION AccountBalanceByLCD(p_acct_id int unsigned, p_city char(5), p_fm_dt datetime, p_to_dt datetime) RETURNS decimal(11,2)
BEGIN
	DECLARE balance decimal(11,2);
	SET balance = (
		SELECT sum(case b.trans_cat
					when 'IN' then (if(b.adj_type='N',a.amount,-1*a.amount))
					when 'OUT' then (if(b.adj_type='N',-1*a.amount,a.amount))
				end)
		FROM acc_trans a, acc_trans_type b
		WHERE a.acct_id = p_acct_id and a.trans_type_code = b.trans_type_code
		and a.lcd >= p_fm_dt and a.lcd <= p_to_dt
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

DELIMITER //
DROP FUNCTION IF EXISTS TransAmountByLCD //
CREATE FUNCTION TransAmountByLCD(p_cat char(5), p_acct_id int unsigned, p_city char(5), p_fm_dt datetime, p_to_dt datetime) RETURNS decimal(11,2)
BEGIN
	DECLARE balance decimal(11,2);
	SET balance = (
		SELECT sum(if(b.adj_type='N',a.amount,-1*a.amount))
		FROM acc_trans a, acc_trans_type b
		WHERE a.acct_id = p_acct_id and a.trans_type_code = b.trans_type_code
		and a.lcd >= p_fm_dt and a.lcd <= p_to_dt
		and a.status <> 'V' and a.city = p_city
		and b.trans_cat = p_cat
	);
	RETURN balance;
END //
DELIMITER ;

insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0001','库存现金','BO','1001','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0002','其他应收款(如退回住房押金,租用设备的)','BO','1221','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0003','应付账款 (有数期业务客户,未到期清付)','BO','2202','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0004','应付职工工资','BO','221101','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0005','应付奖金、津贴和补贴','BO','221102','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0006','应付福利费','BO','221103','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0007','应付社会保险费','BO','221104','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0008','应付住房公积金','BO','221105','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0009','应付工会经费','BO','221106','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0010','应付教育经费','BO','221107','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0011','非货币性福利','BO','221108','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0012','辞退福利','BO','221109','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0013','其他应付职工薪酬','BO','221110','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0014','应交增值税','BO','222101','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0015','未交增值税','BO','222102','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0016','应交营业税','BO','222103','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0017','应交消费税','BO','222104','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0018','应交资源税','BO','222105','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0019','应交所得税','BO','222106','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0020','应交土地增值税','BO','222107','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0021','应交城市维护建设税','BO','222108','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0022','应交房产税','BO','222109','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0023','应交城镇土地使用税','BO','222110','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0024','应交车船使用税','BO','222111','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0025','应交个人所得税','BO','222112','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0026','教育费附加','BO','222113','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0027','矿产资源补偿费','BO','222114','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0028','排污费','BO','222115','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0029','增值税留抵税额','BO','222116','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0030','减免税款','BO','222117','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0031','堤围费','BO','222118','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0032','其他应付款','BO','2241','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0033','财务费用-手续费用','BO','560302','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0034','财务费用-利息费用','BO','560301','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0035','管理费用-跨区介绍客戶提成介绍费','BO','560263','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0036','管理费用-跨区介绍技术员介绍费','BO','560263','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0037','营运费用-代收代付-洁净服务','BO','54010101','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0038','营运费用-代收代付-灭虫服务','BO','54010102','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0039','营运费用-代收代付-飄盈香服務','BO','54010103','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0040','营运费用-代收代付-甲醛服务','BO','54010104','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0041','营运费用-代收代付-杂项服务','BO','54010105','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0042','营运费用-代收代付-销售业务(纸品)','BO','54010201','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0043','营运费用-代收代付-杂项销售','BO','54010202','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0044','营运费用-代收代付-甲醛机器销售成本','BO','54010203','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0045','营运费用-代收代付-纸品机器销售成本','BO','54010204','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0046','营运费用-代收代付-清洁机器销售成本','BO','54010205','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0047','营运费用-代收代付-灭虫机器销售成本','BO','54010206','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0048','营运费用-代收代付-灭虫配件销售成本','BO','54010207','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0049','营运费用-代收代付-清洁物料销售成本','BO','54010208','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BO0050','营运费用-代收代付-灭虫物料销售成本','BO','54010209','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0001','供应商-灭虫用品（蚊杯，酒，柴油，液化石油气，手电筒等）','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0002','供应商-清洁用品（手套，口罩，毛巾等）','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0003','供应商-灭虫机器','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0004','供应商-香水','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0005','供应商-香水瓶','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0006','供应商-TC香水','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0007','供应商-尿缸隔','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0008','供应商-灭虫药','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0009','供应商-电池','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0010','供应商-皂液','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0011','供应商-清新机，皂液机等','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0012','供应商-纸','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0013','供应商-纸机','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0014','供应商-蚊灯光管','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0015','供应商-迷你机香水','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0016','供应商-喷机','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0017','供应商-手套','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0018','供应商-老鼠药','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0019','供应商-坐便消毒液','CO','1405','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0020','营运/销售费用-购电脑','CO','160103','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0021','营运/销售费用-外勤/销售人员电话网络通讯费(营运/销售)','CO','560104','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0022','营运/销售费用-外勤/销售人员交通费','CO','560106','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0023','营运/销售费用-外勤/销售人员交通费(营运/销售)','CO','560106','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0024','营运/销售费用-外勤/销售人员出差费用','CO','560107','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0025','营运/销售费用-外勤/销售人员交际-招待费(营运/销售)','CO','560108','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0026','营运/销售费用-外勤/销售人员福利费(营运/销售)','CO','560110','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0027','营运/销售费用-外勤/销售人员误餐费(营运/销售)','CO','560115','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0028','营运/销售费用-租车费','CO','560152','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0029','营运/销售费用-提货运费','CO','560152','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0030','营运/销售费用-搬运费','CO','560152','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0031','营运/销售费用-样品及测试费(销售)','CO','560153','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0032','营运/销售费用-销售宣传，赞助及推广(销售)','CO','560154','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0033','营运/销售费用-支付报关货品代理费','CO','560155','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0034','营运/销售费用-技术员基本工资(营运)','CO','56010101','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0035','营运/销售费用-技术员工资提成(营运)','CO','56010102','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0036','营运/销售费用-技术员社保费用(营运)','CO','56010103','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0037','营运/销售费用-技术员其它费用(营运)','CO','56010104','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0038','营运/销售费用-销售业务基本工资(销售)','CO','56010105','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0039','营运/销售费用-销售业务工资提成(销售)','CO','56010106','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0040','营运/销售费用-销售业务员社保费用(销售)','CO','56010107','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0041','营运/销售费用-销售业务员其它费用(销售)','CO','56010108','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0042','管理费用-房租','CO','560202','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0043','管理费用-物业管理费','CO','560202','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0044','管理费用-水电费','CO','560203','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0045','管理费用-电话费','CO','560204','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0046','管理费用-公司手机充值','CO','560204','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0047','管理费用-交通费','CO','560206','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0048','管理费用-公交车卡充值','CO','560206','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0049','管理费用-办公室公交车卡充值','CO','560206','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0050','管理费用-打车费','CO','560206','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0051','管理费用-总部同事住宿费（如信文师傅到地区）','CO','560207','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0052','管理费用-饶生船票，差旅费等','CO','560207','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0053','管理费用-办公室人员出差费用','CO','560207','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0054','管理费用-业务餐费','CO','560208','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0055','管理费用-购饮水水票','CO','560209','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0056','管理费用-办公用品','CO','560209','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0057','管理费用-同事生日蛋糕及红包','CO','560210','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0058','管理费用-技术员积分奖励','CO','560210','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0059','管理费用-年会奖品','CO','560210','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0060','管理费用-学分奖励','CO','560210','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0061','管理费用-印标贴','CO','560211','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0062','管理费用-印名片','CO','560211','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0063','管理费用-打印机加墨粉、鼠标等计算机耗材','CO','560211','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0064','管理费用-高速费','CO','560212','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0065','管理费用-停车费','CO','560212','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0066','管理费用-车位年费','CO','560212','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0067','管理费用-油卡充值','CO','560212','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0068','管理费用-汽车维修费','CO','560213','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0069','管理费用-汽车保险','CO','560213','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0070','管理费用-快递费','CO','560216','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0071','管理费用-灭虫资质会费','CO','560217','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0072','管理费用-开会餐费','CO','560217','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0073','管理费用-年会费用分摊','CO','560217','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0074','管理费用-网站招聘会费','CO','560218','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0075','管理费用-外勤同事购工鞋及工服','CO','560250','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0076','管理费用-地区主管房屋补贴','CO','560252','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0077','管理费用-外勤人员房屋补贴','CO','560252','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0078','管理费用-岗位职称培训费','CO','560254','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0079','管理费用-外勤同事商业保险','CO','560255','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0080','管理费用-残疾人年检费用','CO','560258','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0081','管理费用-企业审计费','CO','560260','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CO0082','管理费用-解雇员工支付的赔偿','CO','560261','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BI0001','应收账款(业务产生的收入)','BI','1122','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BI0002','其他应收款','BI','1221','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BI0003','其他应付款 (退回客户的押金及其他)','BI','2241','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BI0004','其他业务收入','BI','5051','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BI0005','政府补助','BI','530101','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BI0006','收回坏账损失','BI','530102','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BI0007','汇兑收益','BI','530103','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BI0008','非流动资产处置净收益','BI','530104','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BI0009','减免税款','BI','530105','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('BI0010','利息费用','BI','560301','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CI0001','应收账款','CI','1122','admin','admin');
insert into acc_account_item(code, name, item_type, acct_code, luu, lcu) values('CI0002','银行存款','CI','1002','admin','admin');


insert into acc_account_code(code, name, lcu, luu) values('1001','库存现金','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1002','银行存款','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1012','其他货币资金','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1101','短期投资','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('110101','股票','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('110102','债券','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('110103','基金','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('110110','其他','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1121','应收票据','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1122','应收账款','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1123','预付账款','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('112301','供应商预付款','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('112302','管理/营运费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1131','应收股利','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1132','应收利息','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1221','其他应收款','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('122101','史伟莎集团联营公司','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('122102','个人社保费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('122103','个人所得税','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('122104','押金','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('122199','其它','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1401','材料采购','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1402','在途物资','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1403','原材料','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1404','材料成本差异','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1405','库存商品','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1407','商品进销差价','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1408','委托加工物资','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1411','周转材料','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('141101','低值易粍品','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1421','消耗性生物资产','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1501','长期债券投资','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1511','长期股权投资','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1601','固定资产','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('160101','办公楼','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('160102','汽车','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('160103','办公楼设备','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('160104','装修','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('160105','电脑软件','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1602','累计折旧','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('160201','办公楼','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('160202','汽车','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('160203','办公楼设备','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('160204','装修','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('160205','电脑软件','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1604','在建工程','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1605','工程物资','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1606','固定资产清理','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1621','生产性生物资产','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1622','生产性生物资产累计折旧','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1701','无形资产','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1702','累计摊销','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1801','长期待摊费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('1901','待处理财产损溢','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('2001','短期借款','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('2201','应付票据','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('2202','应付账款','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('220201','暂估应付账款','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('2203','预收账款','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('220301','服务客户押金','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('2211','应付职工薪酬','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('221101','应付职工工资','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('221102','应付奖金、津贴和补贴','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('221103','应付福利费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('221104','应付社会保险费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('221105','应付住房公积金','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('221106','应付工会经费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('221107','应付教育经费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('221108','非货币性福利','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('221109','辞退福利','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('221110','其他应付职工薪酬','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('2221','应交税费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222101','应交增值税','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('22210101','进项税额','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('22210106','销项税额','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('22210107','进项税额转出','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222102','未交增值税','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222103','应交营业税','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222104','应交消费税','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222105','应交资源税','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222106','应交所得税','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222107','应交土地增值税','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222108','应交城市维护建设税','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222109','应交房产税','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222110','应交城镇土地使用税','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222111','应交车船使用税','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222112','应交个人所得税','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222113','教育费附加','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222114','矿产资源补偿费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222115','排污费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222116','增值税留抵税额','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222117','减免税款','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('222118','堤围费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('2231','应付利息','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('2232','应付利润','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('2241','其他应付款','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('224101','史伟莎集团联营公司','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('224102','管理/营运费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('224199','其它','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('2401','递延收益','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('2501','长期借款','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('2701','长期应付款','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('3001','实收资本','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('3002','资本公积','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('3101','盈余公积','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('310101','法定盈余公积','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('310102','任意盈余公积','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('3103','本年利润','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('3104','利润分配','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('310401','其他转入','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('310402','提取法定盈余公积','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('310403','提取法定公益金','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('310404','提取职工奖励及福利基金','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('310409','提取任意盈余公积','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('310410','应付利润','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('310415','未分配利润','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('4001','生产成本','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('4101','制造费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('4301','研发支出','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('4401','工程施工','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('4403','机械作业','admin','admin');
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
insert into acc_account_code(code, name, lcu, luu) values('560101','工资','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56010101','技术员基本工资','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56010102','技术员工资提成','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56010103','技术员社保费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56010104','技术员其它费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56010105','销售业务基本工资','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56010106','销售业务工资提成','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56010107','销售业务员社保费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56010108','销售业务员其它费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560102','租金及管理费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560103','水电费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560104','电话网络通讯费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560105','折旧費','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560106','交通费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560107','差旅费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560108','交际-招待费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560109','办公费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560110','福利费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560111','文具及印刷费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560112','自置车辆报销费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560113','自置车辆维修费及保险','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560114','其他非自置车辆报销费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560115','误餐费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560116','邮费及快递费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560117','会务及证照年检费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560118','招聘人材费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560151','特许权使用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56015101','特许权使用-洁净服务','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56015102','特许权使用-灭虫服务','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56015103','特许权使用-飄盈香服務','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56015104','特许权使用-甲醛服务','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56015105','特许权使用-杂项服务','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56015121','特许权使用-销售业务','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56015122','特许权使用-杂项销售','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560152','运杂费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560153','样品及测试费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560154','销售宣传，赞助及推广','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560155','进口商品税项及代理费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('5602','管理费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560201','工资','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56020101','办公室和运输及货仓工资','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56020102','办公室和运输及货仓工资-提成或奖金','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56020103','办公室和运输及货仓工资-社保','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('56020104','办公室和运输及货仓工资-其它','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560202','租金及管理费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560203','水电费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560204','电话网络通讯费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560205','折旧費','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560206','交通费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560207','差旅费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560208','交际-招待费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560209','办公费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560210','福利费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560211','文具及印刷费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560212','自置车辆报销费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560213','自置车辆维修费及保险','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560214','其他非自置车辆报销费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560215','误餐费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560216','邮费及快递费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560217','会务及证照年检费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560218','招聘人材费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560219','开办费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560220','低值易耗品','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560250','公司制服及配套','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560251','保养维修费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560252','宿舍费及其它','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560253','住房公积金','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560254','教育-培训经费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560255','保险费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560256','工本费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560257','企业广告宣传','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560258','税金-非主营业务，社保及个人','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560259','专业人士-顾问费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560260','审计费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560261','赔偿费','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560262','长期待摊费用','admin','admin');
insert into acc_account_code(code, name, lcu, luu) values('560263','其他','admin','admin');
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
