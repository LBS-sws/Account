<?php

class ConsultSearchList extends CListPageModel
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
            'countdoc'=>Yii::t('misc','Attachment'),
		);
	}

    public function searchColumns() {
        $search = array(
            'apply_date'=>"date_format(apply_date,'%Y/%m/%d')",
            'consult_code'=>'consult_code',
            //'customer_code'=>'customer_code',
            'apply_city'=>'apply_city',
            'audit_city'=>'audit_city',
        );
        return $search;
    }
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $cityList = Yii::app()->user->city_allow();
		$sql1 = "select * ,
				docman$suffix.countdoc('consu',id) as countdoc
				from acc_consult 
				where (apply_city in ({$cityList}) or audit_city in ({$cityList})) and status=2 
			";
		$sql2 = "select count(id)
				from acc_consult 
				where (apply_city in ({$cityList}) or audit_city in ({$cityList})) and status=2 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
            if ($this->isAdvancedSearch()) {
                $clause = $this->buildSQLCriteria();
            } else {
                $svalue = str_replace("'","\'",$this->searchValue);
                $columns = $this->searchColumns();
                switch ($this->searchField) {
                    case 'apply_city':
                        $clause .= " and apply_city in ".ConsultApplyList::getCitySQLLike($svalue);
                        break;
                    case 'audit_city':
                        $clause .= " and audit_city in ".ConsultApplyList::getCitySQLLike($svalue);
                        break;
                    default:
                        $clause .= General::getSqlConditionClause($columns[$this->searchField],$svalue);
                }
            }
		}
        $clause .= $this->getDateRangeCondition('apply_date');
		
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
                    'countdoc'=>$record['countdoc'],
                    'consult_code'=>$record['consult_code'],
                    'apply_date'=>General::toDate($record['apply_date']),
                    'customer_code'=>$record['customer_code'],
                    'consult_money'=>floatval($record['consult_money']),
                    'apply_city'=>General::getCityName($record['apply_city']),
                    'audit_city'=>General::getCityName($record['audit_city']),
                    'status'=>Yii::t("consult","Audited"),
                    'color'=>" text-green",
                );
			}
		}
		$session = Yii::app()->session;
		$session['consultSearch_c01'] = $this->getCriteria();
		return true;
	}

}
