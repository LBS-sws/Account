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
left outer join acc_trans_info d on a.id = d.trans_id and d.field_id = 'item_code'
WHERE a.acct_id = p_acct_id 
and a.lcd >= p_fm_dt and a.lcd <= p_to_dt
and a.status <> 'V' and a.city = p_city
and b.trans_cat = p_cat
and (c.field_value <> 'A' or c.field_value is null)
and a.trans_type_code <> 'OPEN'
and (d.field_value in ('BI0001','BI0011','BI0012','BI0013','BI0014','BI0026','BO0156','CI0001','CI0017','CI0015','CI0012','CI0013','CI0014') or b.trans_cat<>'IN')
);
RETURN balance;
END //
DELIMITER ;

DELIMITER //
DROP FUNCTION IF EXISTS IncomeYTD //
CREATE FUNCTION IncomeYTD(p_code char(6), p_city char(5), p_now datetime) RETURNS decimal(11,2)
BEGIN
DECLARE yr smallint;
DECLARE mox smallint;
DECLARE balance decimal(11,2);

SET yr = (SELECT DATE_FORMAT(p_now,'%Y')+0);

SET mox = (SELECT DATE_FORMAT(p_now,'%m')+0);

SET balance = (
SELECT sum(if(a.data_value='' or a.data_value is null,0,round(a.data_value+0,2)))
FROM opr_monthly_dtl a, opr_monthly_hdr b 
WHERE a.hdr_id = b.id 
and b.city = p_city
and ((b.year_no=yr-1 and b.month_no=12) or (b.year_no=yr and b.month_no < mox))
and data_field=p_code
);
RETURN balance;
END //
DELIMITER ;

DELIMITER //
DROP FUNCTION IF EXISTS IncomeMTD //
CREATE FUNCTION IncomeMTD(p_code char(6), p_city char(5), p_now datetime) RETURNS decimal(11,2)
BEGIN
DECLARE yr smallint;
DECLARE mox smallint;
DECLARE balance decimal(11,2);

SET yr = (SELECT DATE_FORMAT(p_now,'%Y')+0);

SET mox = (SELECT DATE_FORMAT(p_now,'%m')+0);

SET balance = (
SELECT sum(if(a.data_value='' or a.data_value is null,0,round(a.data_value+0,2)))
FROM opr_monthly_dtl a, opr_monthly_hdr b 
WHERE a.hdr_id = b.id 
and b.city = p_city
and (b.year_no=yr and b.month_no = mox)
and data_field=p_code
);
RETURN balance;
END //
DELIMITER ;
