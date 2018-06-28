use account;

alter table acc_account add column coa varchar(30) after bank_name;

use swoper;

create table swo_notification(
id int unsigned auto_increment NOT NULL primary key,
system_id varchar(15) NOT NULL,
note_type varchar(5),
subject varchar(1000),
description varchar(1000),
message text,
lcu varchar(30),
luu varchar(30),
lcd timestamp default CURRENT_TIMESTAMP,
lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table swo_notification_user(
note_id int unsigned NOT NULL,
username varchar(30) NOT NULL,
status char(1) default 'N',
lcu varchar(30),
luu varchar(30),
lcd timestamp default CURRENT_TIMESTAMP,
lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
UNIQUE KEY notification_user (note_id, username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
