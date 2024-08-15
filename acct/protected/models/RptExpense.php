<?php
class RptExpense extends CReport {
	protected function fields() {
		return array(
		    //申请人所在公司/地区
			'company_city'=>array('label'=>Yii::t('give','company and city'),'width'=>22,'align'=>'L'),
            //单据编码
            'expense_code'=>array('label'=>Yii::t('give','rpt expense code'),'width'=>22,'align'=>'L'),
            //单据编码
            'apply_date'=>array('label'=>Yii::t('give','rpt apply date'),'width'=>22,'align'=>'L'),
            //申请人
            'apply_username'=>array('label'=>Yii::t('give','apply username'),'width'=>22,'align'=>'L'),
            //部门
            'department'=>array('label'=>Yii::t('give','department'),'width'=>15,'align'=>'L'),
            //此费用是否归属本地区
            'local_bool'=>array('label'=>Yii::t('give','local bool'),'width'=>15,'align'=>'L'),
            //费用归属
            'cost_attr'=>array('label'=>Yii::t('give','cost attribution'),'width'=>20,'align'=>'L'),
            //费用发生日期
            'cost_date'=>array('label'=>Yii::t('give','cost date'),'width'=>20,'align'=>'L'),
            //费用类别
            'cost_type'=>array('label'=>Yii::t('give','cost type'),'width'=>20,'align'=>'L'),
            //费用明细
            'cost_detail'=>array('label'=>Yii::t('give','cost detail'),'width'=>30,'align'=>'L'),
            //摘要
            'abstract'=>array('label'=>Yii::t('give','abstract'),'width'=>30,'align'=>'L'),
            //金额
            'cost_money'=>array('label'=>Yii::t('give','cost money'),'width'=>20,'align'=>'L'),
            //流程状态
            'order_state'=>array('label'=>Yii::t('give','order state'),'width'=>20,'align'=>'L'),
            //付款账户
            'account_no'=>array('label'=>Yii::t('give','account no'),'width'=>30,'align'=>'L'),
            //付款时间
            'account_date'=>array('label'=>Yii::t('give','account date'),'width'=>20,'align'=>'L'),
            //关联出差申请编号
            'trip_code'=>array('label'=>Yii::t('give','link trip code'),'width'=>20,'align'=>'L'),
            //关联暂支申请编号
            'pay_code'=>array('label'=>Yii::t('give','link pay code'),'width'=>20,'align'=>'L'),
		);
	}
	
	public function genReport() {
		$this->retrieveData();
		$this->title = $this->getReportName();
		$this->subtitle = Yii::t('report','Date').':'.$this->criteria['START_DT'].' - '.$this->criteria['END_DT']
			;
        if (!empty($this->criteria['CITY'])) {
            $this->subtitle.= empty($this->subtitle)?"":" ；";
            $this->subtitle.= Yii::t('report','City').': ';
            $this->subtitle.= General::getCityNameForList($this->criteria['CITY']);
        }
		return $this->exportExcel();
	}
	
	public function retrieveData() {
		$start_dt = $this->criteria['START_DT'];
		$end_dt = $this->criteria['END_DT'];
		$city = $this->criteria['CITY'];

        if(!General::isJSON($city)){
            $city_allow = strpos($city,"'")!==false?$city:"'{$city}'";
        }else{
            $city_allow = json_decode($city,true);
            $city_allow = "'".implode("','",$city_allow)."'";
        }

		$sql = "select a.*,b.employee_id,b.apply_date,b.acc_id,b.payment_date,b.status_type,b.city,b.exp_code
				from acc_expense_info a 
				left join acc_expense b on a.exp_id=b.id
				where b.city in({$city_allow}) and b.status_type !=0 and b.table_type=1 
					and b.apply_date >= '$start_dt' and b.apply_date <= '$end_dt'
				order by b.city desc,b.id desc,a.info_date desc 
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if ($rows) {
		    $companyList = $this->getCompanyList($city_allow);
            $cityList = $this->getCityList($city_allow);
            $setIDList = ExpenseSetNameForm::getExpenseSetAllList();
            $accountList = $this->getAccountList($city_allow);
            $staffList = array();
            foreach ($rows as $row) {
                $expenseDetail=$this->getExpenseDetail($row["exp_id"]);
                $employee_id = "".$row["employee_id"];
                if(!key_exists($employee_id,$staffList)){
                    $employeeList=ExpenseFun::getEmployeeListForID($employee_id);
                    $staffList[$employee_id] = $employeeList;
                }else{
                    $employeeList=$staffList[$employee_id];
                }
                $temp = array();
                $temp['company_city'] = $this->getKeyNameForList($companyList,$row["city"]);
                $temp['company_city'].= "/".$this->getKeyNameForList($cityList,$row["city"]);
                $temp['expense_code'] = $row["exp_code"];
                $temp['apply_date'] = General::toDate($row['apply_date']);
                $temp['apply_username'] = $employeeList["employee"];
                $temp['department'] = $employeeList["department"];
                $temp['local_bool'] = empty($expenseDetail["local_bool"])?"否":"是";
                $temp['cost_attr'] = $this->getKeyNameForList($setIDList,$row["set_id"]);
                $temp['cost_date'] = General::toDate($row['info_date']);
                $temp['cost_type'] = ExpenseFun::getAmtTypeStrToKey($row["amt_type"]);
                $temp['cost_detail'] = $this->getCostDetail($row["info_json"]);
                $temp['abstract'] = $row["info_remark"];
                $temp['cost_money'] = $row["info_amt"];
                $temp['order_state'] = ExpenseFun::getStatusStrForStatusType($row["status_type"]);
                $temp['account_no'] = $this->getKeyNameForList($accountList,$row["acc_id"]);
                $temp['account_date'] = General::toDate($row['payment_date']);
                $temp['trip_code'] = !empty($row["trip_id"])?$this->getTripCodeForTripId($row["trip_id"]):"";
                $temp['pay_code'] = "";
                $this->data[] = $temp;
            }
		}
		return true;
	}

	protected function getCostDetail($json_str){
	    $str = "";
	    if(!empty($json_str)){
	        $data = json_decode($json_str,true);
            $list = array();
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    if(!empty($value)){
                        $keyStr = ExpenseFun::getAmtTypeStrToKeyTwo($key);
                        $list[]=$keyStr.":".$value.";";
                    }
                }
            }
            $str = implode(" ",$list);
        }
        return $str;
    }

	protected function getCompanyList($city_allow){
	    $list =array();
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("city,name")
            ->from("hr{$suffix}.hr_company")->where("city in ({$city_allow}) and tacitly=1")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["city"]]=$row["name"];
            }
        }
        return $list;
    }

	protected function getTripCodeForTripId($trip_id){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("id,trip_cause,trip_cost,trip_code")
            ->from("hr{$suffix}.hr_employee_trip")
            ->where("id=:id",array(":id"=>$trip_id))->queryRow();
        if($row){
            return $row["trip_code"];
        }
        return "";
    }

	protected function getCityList($city_allow){
	    $list =array();
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("code,name")
            ->from("security{$suffix}.sec_city")->where("code in ({$city_allow})")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["code"]]=$row["name"];
            }
        }
        return $list;
    }

	protected function getAccountList($city_allow){
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("a.*,b.acct_type_desc")
            ->from("acc_account a")
            ->leftJoin("acc_account_type b","a.acct_type_id=b.id")
            ->where("a.city in ({$city_allow})")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row['id']] = "(".$row["acct_type_desc"].")".$row["acct_name"]." ".$row["acct_no"]."(".$row["bank_name"].")";
            }
        }
        return $list;
    }

	protected function getExpenseDetail($exp_id){
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("field_id,field_value")
            ->from("acc_expense_detail")
            ->where("exp_id='{$exp_id}'")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row['field_id']] = $row["field_value"];
            }
        }
        return $list;
    }

    protected function getKeyNameForList($list,$key){
        $key="".$key;
        if(key_exists($key,$list)){
            return $list[$key];
        }else{
            return $key;
        }
    }

	public function getReportName() {
		$city_name = '';
		return (isset($this->criteria) ? Yii::t('report',$this->criteria['RPT_NAME']) : Yii::t('report','Nil')).$city_name;
	}
}
?>