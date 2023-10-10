<?php

class ConsultApplyList extends CListPageModel
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
        $uid = Yii::app()->user->id;
        $sqlEpr="";
		if(Yii::app()->user->validFunction('CN14')){//CN14
            $sqlEpr=" lcu='{$uid}' or ";
        }
        $city_allow = "'{$this->apply_city}'";
		$city_allow.=empty($this->plus_city)?"":",'{$this->plus_city}'";
		$sql1 = "select * 
				from acc_consult 
				where ({$sqlEpr} apply_city in ({$city_allow}) or (audit_city in ({$city_allow}) and status in (2,3))) 
			";
		$sql2 = "select count(id)
				from acc_consult 
				where ({$sqlEpr} apply_city in ({$city_allow}) or (audit_city in ({$city_allow}) and status in (2,3)))
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
					$clause .= General::getSqlConditionClause('apply_city',$svalue);
					break;
				case 'audit_city':
					$clause .= General::getSqlConditionClause('audit_city',$svalue);
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
			    $arr = self::getStatusArr($record);
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'consult_code'=>$record['consult_code'],
                    'apply_date'=>General::toDate($record['apply_date']),
                    'customer_code'=>$record['customer_code'],
                    'consult_money'=>floatval($record['consult_money']),
                    'apply_city'=>General::getCityName($record['apply_city']),
                    'audit_city'=>General::getCityName($record['audit_city']),
                    'status'=>$arr['status'],
                    'color'=>$arr['color'],
                );
			}
		}
		$session = Yii::app()->session;
		$session['consultApply_c01'] = $this->getCriteria();
		return true;
	}

	public static function getStatusArr($row){
	    switch ($row["status"]){
            case 0://草稿
                return array("status"=>Yii::t("consult","Draft"),"color"=>"");
            case 1://待审核
                return array("status"=>Yii::t("consult","Pending"),"color"=>" text-primary");
            case 2://已审核
                return array("status"=>Yii::t("consult","Audited"),"color"=>" text-green");
            case 3://已拒绝
                return array("status"=>Yii::t("consult","Rejected"),"color"=>" text-danger");
            default:
                return array("status"=>"","color"=>"");
        }
    }

    public static function getCityList($city=""){
        $suffix = Yii::app()->params['envSuffix'];
        $sql="SELECT code,name FROM security{$suffix}.sec_city WHERE code not in (SELECT region FROM security{$suffix}.sec_city WHERE region != '')";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        $arr=array(""=>"");
        if($rows){
            foreach ($rows as $row){
                $arr[$row["code"]]=$row["name"];
            }
        }
        if(!empty($city)&&!key_exists($city,$arr)){
            $arr[$city]=$city;
        }
        return $arr;
    }

    public static function staffCompanyForUsername($model){
        $suffix = Yii::app()->params['envSuffix'];
        $username=Yii::app()->user->id;
        $row = Yii::app()->db->createCommand()
            ->select("b.id,b.code,b.name,b.city")
            ->from("hr{$suffix}.hr_binding a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->where("a.user_id=:username",array(":username"=>$username))
            ->queryRow();
        if($row){
            $model->apply_city=$row["city"];
            $plus_city=array();//暂属城市
            $cityRows = Yii::app()->db->createCommand()->select("city")
                ->from("hr{$suffix}.hr_plus_city")
                ->where("employee_id=:employee_id",array(":employee_id"=>$row["id"]))
                ->queryAll();
            if($cityRows){
                foreach ($cityRows as $cityRow){
                    $plus_city[]=$cityRow["city"];
                }
            }
            $model->plus_city=empty($plus_city)?"":implode("','",$plus_city);
            if(isset($model->customer_code)){
                $company = Yii::app()->db->createCommand()->select("taxpayer_num")->from("hr{$suffix}.hr_company")
                    ->where("city=:city",array(":city"=>$row["city"]))
                    ->order("tacitly desc")
                    ->queryRow();
                if($company){
                    $model->customer_code=$company["taxpayer_num"];
                }
            }
            return true;
        }else{
            return false;
        }
    }
}
