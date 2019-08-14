alter table acc_account_code add column rpt_cat varchar(30) after name;

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
left outer join acc_account_item e on d.field_value = e.code
left outer join acc_account_code f on f.code = e.acct_code
WHERE a.acct_id = p_acct_id 
and a.lcd >= p_fm_dt and a.lcd <= p_to_dt
and a.status <> 'V' and a.city = p_city
and b.trans_cat = p_cat
and (c.field_value <> 'A' or c.field_value is null)
and a.trans_type_code <> 'OPEN'
and (f.rpt_cat like '%RECRPT%' or b.trans_cat<>'IN')
);
RETURN balance;
END //
DELIMITER ;
