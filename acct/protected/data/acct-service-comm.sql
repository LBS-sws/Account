/*
	销售提成阶梯 - 主記錄
*/ 
create table acc_service_rate_hdr(
	id int unsigned not null auto_increment primary key,
	city char(5) not null,
	start_dt datetime not null,		-- 生效日期
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
create index idx_service_rate_hdr_01 on acc_service_rate_hdr(city);
insert into acc_service_rate_hdr(id, city, start_dt, lcu, luu) values
(1, 'CD', '2019-03-01', 'admin', 'admin'),
(2, 'CD', '2019-07-01', 'admin', 'admin')


/*
	销售提成阶梯 - 內容記錄
*/ 
create table acc_service_rate_dtl(
	id int unsigned not null auto_increment primary key,
	hdr_id int unsigned not null,
	operator char(2) not null, -- 符號 , GT 是 > , LE 是 <=
	sales_amount decimal(11,2) default 0 not null, -- 年銷額
	hy_pc_rate decimal(5,2) default 0 not null,
	inv_rate decimal(5,2) default 0 not null,
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
create index idx_service_rate_dtl_01 on acc_service_rate_dtl(hdr_id);
insert into acc_service_rate_dtl(hdr_id, operator, sales_amount, hy_pc_rate, inv_rate, lcu, luu) values
(1, 'LE', 10000, 5.00, 1.00, 'admin', 'admin'),
(1, 'LE', 50000, 6.00, 1.00, 'admin', 'admin'),
(1, 'LE', 90000, 7.00, 2.00, 'admin', 'admin'),
(1, 'LE', 130000, 8.00, 2.00, 'admin', 'admin'),
(1, 'LE', 180000, 9.00, 3.00, 'admin', 'admin'),
(1, 'GT', 180000, 10.00, 3.00, 'admin', 'admin'),
(2, 'LE', 30000, 5.00, 1.00, 'admin', 'admin'),
(2, 'LE', 80000, 6.00, 1.00, 'admin', 'admin'),
(2, 'LE', 130000, 7.00, 2.00, 'admin', 'admin'),
(2, 'LE', 180000, 9.00, 3.00, 'admin', 'admin'),
(2, 'GT', 180000, 10.00, 3.00, 'admin', 'admin');

/*
	销售提成記錄 - 主記錄
*/ 
create table acc_service_comm_hdr(
	id int unsigned not null auto_increment primary key,
	year_no smallint unsigned NOT NULL, -- 年
	month_no tinyint unsigned NOT NULL, -- 月
	employee_id  int unsigned not null, -- 員工記錄 ID
	comm_total_amount decimal(11,2) default 0 not null, -- 提成總金額
	city char(5) not null,
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
	销售提成記錄 - 內容記錄
*/ 
create table acc_service_comm_dtl(
	id int unsigned not null auto_increment primary key,
	hdr_id int unsigned not null,
	comm_amount decimal(11,2) default 0 not null, -- 提成金額
	serv_id int unsigned, -- 服務記錄 swo_service ID
	serv_status char(1) not null, -- 服務記錄類別, 可參考swo_service
	serv_status_dt datetime, -- 新增日期/更改日期/暫停日期/..., 可參考swo_service
	serv_company_id int unsigned, -- 客戶 ID
	serv_company_name varchar(1000) not null,  -- 客戶名稱
	serv_cust_type int unsigned, -- 客戶類型 (IA/IB/IC...)
	serv_amt_install decimal (11,2) default 0,  -- 安裝金額
	serv_amt_paid decimal (11,2) default 0, -- 服務年金額
	serv_b4_amt_paid decimal (11,2) default 0, -- 更改前服務年金額
	serv_freq varchar(1000), -- 服務頻次
	serv_b4_freq varchar(1000), -- 更改前服務頻次
	serv_sign_dt datetime, -- 签约日
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

