<?php

class InvoiceList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a labe l that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'invoice_no'=>Yii::t('invoice','Number'),
			'invoice_dt'=>Yii::t('invoice','Date'),
            'head_type'=>Yii::t('invoice','head type'),
			'customer_code'=>Yii::t('invoice','Customer Account'),
			'invoice_to_name'=>Yii::t('invoice','Invoice Company'),
            'name_zh'=>Yii::t('invoice','Delivery Company'),
            'payment_term'=>Yii::t('invoice','Payment Term'),
            'city_name'=>Yii::t('misc','City'),
			'staff_name'=>Yii::t('invoice','technician'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();
		$sql1 = "select a.*,b.name as city_name from acc_invoice a 
            LEFT JOIN security$suffix.sec_city b ON a.city = b.code
            where a.city in ($city) 
			";
		$sql2 = "select count(a.id) from acc_invoice a 
            LEFT JOIN security$suffix.sec_city b ON a.city = b.code
            where a.city in ($city) 
			";
		$clause = "";
        if (!empty($this->searchField) && (!empty($this->searchValue) || $this->isAdvancedSearch())) {
            if ($this->isAdvancedSearch()) {
                $clause = $this->buildSQLCriteria();
            } else {
                $svalue = str_replace("'","\'",$this->searchValue);
                $columns = $this->searchColumns();
                $clause .= General::getSqlConditionClause($columns[$this->searchField],$svalue);
            }
        }
        $clause .= $this->getDateRangeCondition('invoice_dt');

		$order = "";
		if (!empty($this->orderField)) {
			$order .= " order by ".$this->orderField." ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
		    $order ="order by invoice_dt desc";
        }
		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		$sql = $sql1.$clause.$order;

		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();
//		print_r($sql);
		$list = array();
		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
                $dates = date('Y/m/d',strtotime($record['invoice_dt']));
                $timestrap=strtotime($dates);
                $number=date('ym',$timestrap);
                $number=($number*10000000)+$record['id'];
				$this->attr[] = array(
					'id'=>$record['id'],
					'number'=>$number,
					'invoice_no'=>$record['invoice_no'],
					'city_name'=>$record['city_name'],
					'head_type'=>empty($record['head_type'])?"佳駿企業有限公司":"LBS (Macau) Limited",
					'invoice_dt'=>$dates,
					'customer_code'=>$record['customer_code'],
					'invoice_to_name'=>$record['invoice_to_name'],
					'name_zh'=>$record['name_zh'],
					'staff_name'=>$record['staff_name'],
					'payment_term'=>$record['payment_term'],
				);
			}
		}
        $session = Yii::app()->session;
        $session[$this->criteriaName()] = $this->getCriteria();
		return true;
	}

	public function retrieveExportData()
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();
		$sql1 = "
			SELECT a.*, b.name as city_name, c.product_code, c.product_name, c.unit, c.qty, c.unit_price, c.amount,
				e.name as generated_by
			FROM acc_invoice_type c 
			LEFT JOIN acc_invoice a ON a.id = c.invoice_id
            LEFT JOIN security$suffix.sec_city b ON a.city = b.code
			LEFT JOIN hr$suffix.hr_binding d ON a.lcu = d.user_id
			LEFT JOIN hr$suffix.hr_employee e ON e.id = d.employee_id
            where a.city in ($city) 
		";
		$clause = "";
        if (!empty($this->searchField) && (!empty($this->searchValue) || $this->isAdvancedSearch())) {
            if ($this->isAdvancedSearch()) {
                $clause = $this->buildSQLCriteria();
            } else {
                $svalue = str_replace("'","\'",$this->searchValue);
                $columns = $this->searchColumns();
                $clause .= General::getSqlConditionClause($columns[$this->searchField],$svalue);
            }
        }
        $clause .= $this->getDateRangeCondition('a.invoice_dt');

	    $order ="order by a.invoice_no, c.id";

		$sql = $sql1.$clause.$order;
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		$list = array();
		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$this->attr[] = array(
					'invoice_no'=>$record['invoice_no'],
					'invoice_dt'=>date('Y/m/d',strtotime($record['invoice_dt'])),
					'customer_code'=>$record['customer_code'],
					'name_zh'=>$record['name_zh'],
                    'head_type'=>empty($record['head_type'])?"佳駿企業有限公司":"LBS (Macau) Limited",
					'addr'=>$record['addr'],
					'tel'=>$record['tel'],
					'sales_name'=>$record['sales_name'],
					'staff_name'=>$record['staff_name'],
					'payment_term'=>$record['payment_term'],
					'bowl'=>$record['bowl'],
					'baf'=>$record['baf'],
					'hand'=>$record['hand'],
					'urinal'=>$record['urinal'],
					'hsd'=>$record['hsd'],
					'td'=>$record['td'],
					'sink'=>$record['sink'],
					'abhsd'=>$record['abhsd'],
					'ptd'=>$record['ptd'],
					'ttl'=>$record['ttl'],
					'aerosal'=>$record['aerosal'],
					'toiletRoom'=>$record['toiletRoom'],
					'product_code'=>$record['product_code'],
					'product_name'=>$record['product_name'],
					'qty'=>$record['qty'],
					'unit_price'=>$record['unit_price'],
					'amount'=>$record['amount'],
					'invoice_amt'=>$record['invoice_amt'],
					'generated_by'=>$record['generated_by'],
				);
			}
		}
        $session = Yii::app()->session;
        $session[$this->criteriaName()] = $this->getCriteria();
		return true;
	}

    public function searchColumns() {
        $search = array(
            'invoice_no'=>"a.invoice_no",
            'invoice_dt'=>"date_format(a.invoice_dt,'%Y/%m/%d')",
            'customer_code'=>"a.customer_code",
            'name_zh'=>"a.name_zh",
            'payment_term'=>"a.payment_term",
            'staff_name'=>"a.staff_name",
            'head_type'=>"(case a.head_type when 0 then '佳駿企業有限公司' else 'LBS (Macau) Limited' end)",

        );
        if (!Yii::app()->user->isSingleCity()) $search['city_name'] = 'b.name';
        return $search;
    }

}
