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
			'customer_code'=>Yii::t('invoice','Customer Account'),
			'invoice_to_name'=>Yii::t('invoice','Invoice Company'),
            'name_zh'=>Yii::t('invoice','Delivery Company'),

		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();
		$sql1 = "select *  from acc_invoice where city in ($city) 
			";
		$sql2 = "select count(id) from acc_invoice where city in ($city) 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'name_zh':
					$clause .= General::getSqlConditionClause('name_zh',$svalue);
					break;
				case 'invoice_no':
					$clause .= General::getSqlConditionClause('invoice_no',$svalue);
					break;
				case 'invoice_dt':
					$clause .= General::getSqlConditionClause('invoice_dt',$svalue);
					break;
				case 'customer_code':
					$clause .= General::getSqlConditionClause('customer_code',$svalue);
					break;
				case 'invoice_to_name':
					$clause .= General::getSqlConditionClause('invoice_to_name',$svalue);
					break;
                case 'city_name':
                    $clause .= ' and city in '.$this->getCityCodeSqlLikeName($svalue);
					break;
			}
		}

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
                $dates = General::toMyDate($record['invoice_dt']);
                $timestrap=strtotime($dates);
                $number=date('ym',$timestrap);
                $number=($number*10000000)+$record['id'];
				$this->attr[] = array(
					'id'=>$record['id'],
					'number'=>$number,
					'invoice_no'=>$record['invoice_no'],
					'invoice_dt'=>$dates,
					'customer_code'=>$record['customer_code'],
					'invoice_to_name'=>$record['invoice_to_name'],
					'name_zh'=>$record['name_zh'],
				);
			}
		}
		$session = Yii::app()->session;
		$session['invoice_xi01'] = $this->getCriteria();
		return true;
	}

//获取地区編號（模糊查詢）
    public function getCityCodeSqlLikeName($code)
    {
        $from =  'security'.Yii::app()->params['envSuffix'].'.sec_city';
        $rows = Yii::app()->db->createCommand()->select("code")->from($from)->where(array('like', 'name', "%$code%"))->queryAll();
        $arr = array();
        foreach ($rows as $row){
            array_push($arr,"'".$row["code"]."'");
        }
        if(empty($arr)){
            return "('')";
        }else{
            $arr = implode(",",$arr);
            return "($arr)";
        }
    }
}
