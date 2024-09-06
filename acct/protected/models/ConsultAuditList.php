<?php

class ConsultAuditList extends CListPageModel
{
    public $apply_city;
    public $plus_city;//暂属城市
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'consult_code'=>Yii::t('consult','consult code'),
			'apply_date'=>Yii::t('consult','apply date'),
			'customer_code'=>Yii::t('consult','customer code'),
			'consult_money'=>Yii::t('consult','consult money'),
			'apply_city'=>Yii::t('consult','apply city'),
			'audit_city'=>Yii::t('consult','audit city'),
			'audit_date'=>Yii::t('consult','audit date'),
			'status'=>Yii::t('consult','status'),
			'remark'=>Yii::t('consult','remark'),
			'reject_remark'=>Yii::t('consult','reject remark'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $city_allow = "'{$this->apply_city}'";
        $city_allow.=empty($this->plus_city)?"":",'{$this->plus_city}'";
		$sql1 = "select * 
				from acc_consult 
				where audit_city in ({$city_allow}) and status=1 
			";
		$sql2 = "select count(id)
				from acc_consult 
				where audit_city in ({$city_allow}) and status=1 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'consult_code':
					$clause .= General::getSqlConditionClause('consult_code',$svalue);
					break;
				case 'apply_date':
					$clause .= General::getSqlConditionClause('apply_date',$svalue);
					break;
				case 'customer_code':
					$clause .= General::getSqlConditionClause('customer_code',$svalue);
					break;
				case 'consult_money':
					$clause .= General::getSqlConditionClause('consult_money',$svalue);
					break;
                case 'apply_city':
                    $clause .= " and apply_city in ".ConsultApplyList::getCitySQLLike($svalue);
                    break;
                case 'audit_city':
                    $clause .= " and audit_city in ".ConsultApplyList::getCitySQLLike($svalue);
                    break;
				case 'status':
					$clause .= General::getSqlConditionClause('status',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
            $order .= " order by {$this->orderField} ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by id desc ";
        }

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();

		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'consult_code'=>$record['consult_code'],
                    'apply_date'=>General::toDate($record['apply_date']),
                    'customer_code'=>$record['customer_code'],
                    'consult_money'=>floatval($record['consult_money']),
                    'apply_city'=>General::getCityName($record['apply_city']),
                    'audit_city'=>General::getCityName($record['audit_city']),
                    'status'=>Yii::t("consult","Pending"),
                    'color'=>" text-primary",
                );
			}
		}
		$session = Yii::app()->session;
		$session['consultAudit_c01'] = $this->getCriteria();
		return true;
	}

	public function getCountConsult(){
        //$suffix = Yii::app()->params['envSuffix'];
        $city_allow = "'{$this->apply_city}'";
        $city_allow.=empty($this->plus_city)?"":",'{$this->plus_city}'";
        $sql = "select count(id)
				from acc_consult 
				where audit_city in ($city_allow) and status=1  
			";
        $rtn = Yii::app()->db->createCommand($sql)->queryScalar();
        return $rtn;
    }
}
