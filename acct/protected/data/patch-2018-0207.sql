create table acc_delegation (
username varchar(30) NOT NULL,
delegated varchar(30) NOT NULL,
lcu varchar(30),
luu varchar(30),
lcd timestamp default CURRENT_TIMESTAMP,
lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
UNIQUE(username, delegated)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
