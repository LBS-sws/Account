use swoper;

alter table swo_company add column full_name varchar(500) after name;

alter table swo_company modify code varchar(20);

alter table swo_supplier add column full_name varchar(500) after name;

alter table swo_supplier modify code varchar(20);

use account;

DROP TABLE IF EXISTS acc_trans_t3;
CREATE TABLE acc_trans_t3(
	t3_doc_no varchar(50) not null primary key,
	trans_id int unsigned not null
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

alter table acc_import_queue_param modify column param_text longtext
character set utf8mb4 collate utf8mb4_unicode_ci null;