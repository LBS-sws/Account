CREATE DATABASE security CHARACTER SET utf8 COLLATE utf8_general_ci;

GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON security.* TO 'swpuser'@'localhost' IDENTIFIED BY 'swisher168';

use security;

DROP TABLE IF EXISTS sec_wservice;
CREATE TABLE sec_wservice (
	wsvc_key varchar(50) NOT NULL primary key,
	wsvc_desc varchar(100) NOT NULL,
	city char(5) NOT NULL,
	session_key varchar(50),
	session_time datetime
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sec_station_request;
CREATE TABLE sec_station_request (
	req_key varchar(50) NOT NULL primary key,
	email varchar(100) NOT NULL,
	station_name varchar(30) NOT NULL,
	city char(5) NOT NULL,
	station_id varchar(30) default NULL,
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sec_station;
CREATE TABLE sec_station (
	station_id varchar(30) NOT NULL primary key,
	station_name varchar(30) NOT NULL,
	city char(5) NOT NULL,
	status char(1) default 'Y',
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sec_login_log;
CREATE TABLE sec_login_log (
	station_id varchar(30) NOT NULL,
	username varchar(30) NOT NULL,
	client_ip varchar(20),
	login_time timestamp default CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sec_user;
CREATE TABLE sec_user (
	username varchar(30) NOT NULL,
	password varchar(128) default NULL,
	disp_name varchar(100) default NULL,
	email varchar(100) default NULL,
	logon_time datetime default NULL,
	logoff_time datetime default NULL,
	status char(1) default NULL,
	fail_count tinyint unsigned default 0,
	locked char(1) default 'N',
	session_key varchar(500),
	city char(5) NOT NULL default '',
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	PRIMARY KEY  (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO sec_user(username, password, disp_name, logon_time, logoff_time, status, fail_count, locked, session_key, city, lcu, luu) 
	VALUES('admin','319153b210a3f6efde35e1486638f2cd','Administrator',null,null,'A',0,'N',null,'HK','admin','admin');

DROP TABLE IF EXISTS sec_user_info;
CREATE TABLE sec_user_info (
	username varchar(30) NOT NULL,
	field_id varchar(30) NOT NULL,
	field_value varchar(2000),
	field_blob longblob,
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	UNIQUE KEY user_info (username, field_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
DROP TABLE IF EXISTS sec_user_access;
CREATE TABLE sec_user_access (
	username varchar(30) NOT NULL,
	system_id varchar(15) NOT NULL,
	a_read_only varchar(255) default '',
	a_read_write varchar(255) default '',
	a_control varchar(255) default '',
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE UNIQUE INDEX idx_sec_user_access_01 ON sec_user_access(username, system_id);
INSERT INTO sec_user_access(username, system_id, a_read_only, a_read_write, a_control, lcu, luu)
	VALUES('admin','acct','','C01C02C05C06C07D01D02D03D04D05','','admin','admin'),
	('admin','drs','','C01C02C05C06C07D01D02D03D04D05','','admin','admin')
;

DROP TABLE IF EXISTS sec_template;
CREATE TABLE sec_template (
	temp_id int unsigned auto_increment NOT NULL primary key,
	system_id varchar(15) NOT NULL,
	temp_name varchar(255) NOT NULL,
	a_read_only varchar(255) default '',
	a_read_write varchar(255) default '',
	a_control varchar(255) default '',
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX idx_sec_template_01 ON sec_template(system_id);

DROP TABLE IF EXISTS sec_user_option;
CREATE TABLE sec_user_option (
	username varchar(30) NOT NULL,
	option_key varchar(30) NOT NULL,
	option_value varchar(255) default NULL,
	UNIQUE KEY idx_sec_user_option_1 (username,option_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sec_city;
CREATE TABLE sec_city(
	code char(5) not null primary key,
	name varchar(255) not null default '',
	region char(5),
	incharge varchar(30),
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sec_city_info;
CREATE TABLE sec_city_info(
	code char(5) not null primary key,
	field_id varchar(30) not null,
	field_value varchar(2000),
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	UNIQUE KEY idx_sec_city_info_1 (code,field_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

