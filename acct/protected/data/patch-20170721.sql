use swoper;

alter table swo_company add column full_name varchar(1000) after name,
add column tax_reg_no varchar(100) after full_name;

alter table swo_company modify code varchar(20);

alter table swo_supplier add column full_name varchar(1000) after name,
add column tax_reg_no varchar(100) after full_name;

alter table swo_supplier modify code varchar(20);

use account;

DROP TABLE IF EXISTS acc_trans_t3;
CREATE TABLE acc_trans_t3(
	t3_doc_no varchar(50) not null primary key,
	trans_id int unsigned not null
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

alter table acc_import_queue_param modify column param_text longtext
character set utf8mb4 collate utf8mb4_unicode_ci null;

DELIMITER //
DROP FUNCTION IF EXISTS TransAmountByLCDWoIntTrf //
CREATE FUNCTION TransAmountByLCDWoIntTrf(p_cat char(5), p_acct_id int unsigned, p_city char(5), p_fm_dt datetime, p_to_dt datetime) RETURNS decimal(11,2)
BEGIN
DECLARE balance decimal(11,2);
SET balance = (
SELECT sum(if(b.adj_type='N',a.amount,-1*a.amount))
FROM acc_trans a inner join acc_trans_type b on a.trans_type_code = b.trans_type_code
left outer join acc_trans_info c on a.id = c.trans_id and c.field_id = 'payer_type'
WHERE a.acct_id = p_acct_id 
and a.lcd >= p_fm_dt and a.lcd <= p_to_dt
and a.status <> 'V' and a.city = p_city
and b.trans_cat = p_cat
and (c.field_value <> 'A' or c.field_value is null)
);
RETURN balance;
END //
DELIMITER ;
