CREATE DATABASE docman CHARACTER SET utf8 COLLATE utf8_general_ci;

GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON docman.* TO 'swuser'@'localhost' IDENTIFIED BY 'swisher168';

use docman;

DROP TABLE IF EXISTS dm_doc_type;
CREATE TABLE dm_doc_type(
	doc_type_code varchar(10) not null primary key,
	doc_type_desc varchar(255),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO dm_doc_type(doc_type_code, doc_type_desc) values
('SERVICE','Service Contract')

;

DROP TABLE IF EXISTS dm_master;
CREATE TABLE dm_master(
	id int unsigned not null auto_increment primary key,
	doc_type_code varchar(10) not null,
	doc_id int unsigned not null,
	remove char(1) default 'N',
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE UNIQUE INDEX idx_dm_master_01 ON dm_master(doc_type_code, doc_id);

DROP TABLE IF EXISTS dm_file;
CREATE TABLE dm_file(
	id int unsigned not null auto_increment primary key,
	mast_id int unsigned not null,
	phy_file_name varchar(300) not null,
	phy_path_name varchar(100) not null,
	display_name varchar(255) not null,
	file_type varchar(255),
	archive char(1) default 'N',
	remove char(1) default 'N',
	lcu varchar(30),
	luu varchar(30),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX idx_dm_file_01 ON dm_file(mast_id);

/*
DROP TABLE IF EXISTS dm_detail;
CREATE TABLE dm_detail(
	id int unsigned not null auto_increment primary key,
	file_id int unsigned not null,
	field_code varchar(50) not null,
	field_value varchar(1000),
	lcd timestamp default CURRENT_TIMESTAMP,
	lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE UNIQUE INDEX idx_dm_detail_01 ON dm_detail(file_id, field_code);
*/
