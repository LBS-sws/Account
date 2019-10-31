<?php

class BonusList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
    public $new_calc;

	public function attributeLabels()
	{
		return array(	
			'year'=>Yii::t('app','Year'),
			'month'=>Yii::t('app','Month'),
			'type_group'=>Yii::t('app','Type'),
			'city'=>Yii::t('app','City'),
            'money'=>Yii::t('app','Money'),
            'city_name'=>Yii::t('app','city'),
            'first_dt'=>Yii::t('app','first_dt'),
            'sign_dt'=>Yii::t('app','sign_dt'),
            'company_name'=>Yii::t('app','company_name'),
            'type_desc'=>Yii::t('app','type_desc'),
            'service'=>Yii::t('app','service'),
            'amt_paid'=>Yii::t('app','amt_paid'),
            'amt_install'=>Yii::t('app','amt_install'),
            'employee_code'=>Yii::t('app','employee_code'),
            'employee_name'=>Yii::t('app','employee_name'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
        $citylist = Yii::app()->user->city_allow();
		$suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select a.* ,b.name as city_name
				from acc_bonus	a
				left outer join security$suffix.sec_city b on a.city=b.code		  
					where a.city in ($citylist)";
		$sql2 = "select count(a.id)
				from acc_bonus	a
				left outer join security$suffix.sec_city b on a.city=b.code		  
			   	where a.city in ($citylist)";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'year':
					$clause .= General::getSqlConditionClause('a.year',$svalue);
					break;
                case 'month':
                    $clause .= General::getSqlConditionClause('a.month',$svalue);
                    break;
                case 'money':
                    $clause .= General::getSqlConditionClause('a.money',$svalue);
                    break;
                case 'city':
                    $clause .= General::getSqlConditionClause('b.name',$svalue);
                    break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'year':
					$order .= " order by year ";
					break;
				case 'month':
					$order .= " order by month ";
					break;
                case 'city':
                    $order .= " order by city ";
                    break;
                case 'money':
                    $order .= " order by money ";
                    break;
			}
			if ($this->orderType=='D') $order .= "desc ";
		} else {
			$order .= " order by year desc";
		}

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		
		$list = array();
		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
					$this->attr[] = array(
						'id'=>$record['id'],
						'year'=>$record['year'],
                        'month'=>$record['month'],
						'city'=>$record['city_name'],
                        'money'=>$record['money'],
					);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_hc03'] = $this->getCriteria();
		return true;
	}


	public function retrieveDataByPages($index,$pageNum=1){

        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select * from acc_bonus where id='$index'";
        $records = Yii::app()->db->createCommand($sql)->queryRow();
        $start=$records['year']."-".$records['month']."-01";
        $end=$records['year']."-".$records['month']."-31";
        $sql1 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 			
				where a.city='".$records['city']."'  and a.status='A'  and a.first_dt>='$start' and a.first_dt<='$end'  and a.target='1'
			";
        $record1= Yii::app()->db->createCommand($sql1)->queryAll();
        $sql2 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 			
				where a.city='".$records['city']."'  and a.status='N'  and a.first_dt>='$start' and a.first_dt<='$end'  and a.target='1'
			";
        $record2= Yii::app()->db->createCommand($sql2)->queryAll();
        if (count($record1) > 0) {
            foreach ($record1 as $k=>$record) {
                if($record['paid_type']=='1'||$record['paid_type']=='Y'){
                    $a=$record['amt_paid'];
                }else{
                    $a=$record['amt_paid']*12;
                }
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'company_name'=>$record['company_name'],        //客户名称
                    'city_name'=>$record['city_name'],               //城市
                    'type_desc'=>$record['type_desc'],               //类别
                    'service'=>$record['service'],                    //服务频率
                    'sign_dt'=>General::toDate($record['sign_dt']),   //签约时间
                    'first_dt'=>General::toDate($record['first_dt']), //服务时间
                    'amt_paid'=>$a,                                     //服务年金额金额
                    'amt_install'=>$record['amt_install'],           //安装金额
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员
                );
            }
        }
        if (count($record2) > 0) {
            foreach ($record2 as $k=>$record) {
                if($record['paid_type']=='1'||$record['paid_type']=='Y'){
                    $a=$record['amt_paid'];
                }else{
                    $a=$record['amt_paid']*12;
                }
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'company_name'=>$record['company_name'],        //客户名称
                    'city_name'=>$record['city_name'],               //城市
                    'type_desc'=>$record['type_desc'],               //类别
                    'service'=>$record['service'],                    //服务频率
                    'sign_dt'=>General::toDate($record['sign_dt']),   //签约时间
                    'first_dt'=>General::toDate($record['first_dt']), //服务时间
                    'amt_paid'=>$a,                                     //服务年金额金额
                    'amt_install'=>$record['amt_install'],           //安装金额
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_XS04'] = $this->getCriteria();
        return true;
    }


}
