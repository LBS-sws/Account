CREATE TABLE acc_account_file_hdr(
  id int unsigned auto_increment NOT NULL primary key,
  city char(5) NOT NULL,
  year_no smallint unsigned NOT NULL,
  month_no tinyint unsigned NOT NULL,
  status char(1) default 'N',
  lcu varchar(30),
  luu varchar(30),
  lcd timestamp default CURRENT_TIMESTAMP,
  lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

drop table if exists acc_account_file_dtl;
CREATE TABLE acc_account_file_dtl (
  hdr_id int unsigned NOT NULL,
  data_field char(30) NOT NULL,
  data_value varchar(100),
  lcu varchar(30),
  luu varchar(30),
  lcd timestamp default CURRENT_TIMESTAMP,
  lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  primary key(hdr_id, data_field)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
