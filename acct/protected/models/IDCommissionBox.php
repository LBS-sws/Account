<?php
/* Reimbursement Form */

class IDCommissionBox extends CFormModel
{
    //已使用的字段 （開始）
    public $id;
    public $year;
    public $month;
    public $type=0;//0:查询  1：计算
    public $city;
    public $employee_id;
    public $employee_code;
    public $employee_name;
    public $group_type;//組別
    public $new_amount;//新增生意額提成
    public $edit_amount;//更改生意額提成
    public $renewal_amount;//續約生意額提成
    public $sum_amount;//提成总金额
    public $new_money;//新增回款
    public $edit_money;//更改回款
    public $renewal_money;//續約回款
    public $updateList;//需要修改的id列表
    //已使用的字段 （結束）

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('id,updateList','safe'),
            array ('id','validateID','on'=>array('save')),
            array ('updateList','validateList','on'=>array('save')),
        );
    }

    public function validateID($attribute, $params) {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("a.id,a.year_no,a.month_no,a.employee_id,b.city,b.code,b.name")
            ->from("acc_serviceid_comm_hdr a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->where("a.id=:id",array(":id"=>$this->id))->queryRow();
        if($row){
            $this->employee_id = $row["employee_id"];
            $this->employee_code = $row["code"];
            $this->employee_name = $row["name"];
            $this->year = $row["year_no"];
            $this->month = $row["month_no"];
            $this->city = $row["city"];
        }else{
            $this->addError($attribute,"销售提成不存在，请刷新重试");
        }
    }
    public function validateList($attribute, $params) {
        if(!empty($this->updateList)){
            foreach ($this->updateList as $value){
                if(!is_numeric($value)){
                    $this->addError($attribute,"您选择的内容有误，请刷新重试");
                }else{
                    $suffix = Yii::app()->params['envSuffix'];
                    $row = Yii::app()->db->createCommand()->select("id")->from("swoper{$suffix}.swo_serviceid_info")
                        ->where("id=$value")->queryRow();
                    if(!$row){
                        $this->addError($attribute,"您选择的内容不存在，请刷新重试");
                    }
                }
            }
        }else{
            $this->updateList=array();
        }
    }

    //计算新增回款金额
    public function newSave(){
        $suffix = Yii::app()->params['envSuffix'];
        $month = $this->month<10?"0{$this->month}":$this->month;
        $startDate = "{$this->year}/{$month}/01";
        $endDate = "{$this->year}/{$month}/31";
        $records = Yii::app()->db->createCommand()
            ->select("a.*,b.status,b.cust_type_name as cust_type,g.cust_type_name,b.service_no,b.ctrt_period,f.code,f.name")
            ->from("swoper{$suffix}.swo_serviceid_info a")
            ->leftJoin("swoper{$suffix}.swo_serviceid b","a.serviceID_id=b.id")
            ->leftJoin("swoper{$suffix}.swo_company f","b.company_id=f.id")
            ->leftJoin("swoper{$suffix}.swo_customer_type_info g","b.cust_type_name=g.id")
            ->where("b.salesman_id=:id and b.status = 'N' and a.back_date between '$startDate' and '$endDate'",array(":id"=>$this->employee_id))
            ->queryAll();
        $new_amount = 0;//新增生意额提成
        $new_money=0;//新增回款
        if($records){
            foreach ($records as $record){
                $commission = 0;//是否已經計算 1：已计算
                $rate_num = 0;//提成比例
                $comm_money = 0;//实际计算回款金额
                if (in_array($record["id"],$this->updateList)){
                    //需要计算
                    $commission = 1;
                    $rate_num = IDCommissionList::getRateNum($record["cust_type"],$record["ctrt_period"],$this->city);
                    $comm_money = $record["back_money"]*$record["back_ratio"]*0.01;
                }
                Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_serviceid_info",array(
                    'commission'=>$commission,
                    'comm_money'=>$comm_money,
                    'rate_num'=>$rate_num,
                ),"id={$record['id']}");
                $new_money+=$comm_money;
                $new_amount+=$comm_money*$rate_num;
            }
        }
        $this->saveFileNameAndValue("new_amount",$new_amount,1);
        $this->saveFileNameAndValue("new_money",$new_money);
        $this->resetAllMoney();
    }

    //计算更改回款金额
    public function AmendSave(){
        $suffix = Yii::app()->params['envSuffix'];
        $month = $this->month<10?"0{$this->month}":$this->month;
        $startDate = "{$this->year}/{$month}/01";
        $endDate = "{$this->year}/{$month}/31";
        $records = Yii::app()->db->createCommand()
            ->select("a.*,b.status,b.cust_type_name as cust_type,g.cust_type_name,b.service_no,b.ctrt_period,f.code,f.name")
            ->from("swoper{$suffix}.swo_serviceid_info a")
            ->leftJoin("swoper{$suffix}.swo_serviceid b","a.serviceID_id=b.id")
            ->leftJoin("swoper{$suffix}.swo_company f","b.company_id=f.id")
            ->leftJoin("swoper{$suffix}.swo_customer_type_info g","b.cust_type_name=g.id")
            ->where("b.salesman_id=:id and b.status = 'A' and a.back_date between '$startDate' and '$endDate'",array(":id"=>$this->employee_id))
            ->queryAll();
        $edit_amount = 0;//更改生意额提成
        $edit_money=0;//更改回款
        if($records){
            foreach ($records as $record){
                $commission = 0;//是否已經計算 1：已计算
                $rate_num = 0;//提成比例
                $comm_money = 0;//实际计算回款金额
                if (in_array($record["id"],$this->updateList)){
                    //需要计算
                    $commission = 1;
                    $rate_num = IDCommissionList::getRateNum($record["cust_type"],$record["ctrt_period"],$this->city);
                    $comm_money = $record["back_money"]*$record["back_ratio"]*0.01;
                }
                Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_serviceid_info",array(
                    'commission'=>$commission,
                    'comm_money'=>$comm_money,
                    'rate_num'=>$rate_num,
                ),"id={$record['id']}");
                $edit_money+=$comm_money;
                $edit_amount+=$comm_money*$rate_num;
            }
        }
        $this->saveFileNameAndValue("edit_amount",$edit_amount,1);
        $this->saveFileNameAndValue("edit_money",$edit_money);
        $this->resetAllMoney();
    }

    //计算续约回款金额
    public function RenewSave(){
        $suffix = Yii::app()->params['envSuffix'];
        $month = $this->month<10?"0{$this->month}":$this->month;
        $startDate = "{$this->year}/{$month}/01";
        $endDate = "{$this->year}/{$month}/31";
        $records = Yii::app()->db->createCommand()
            ->select("a.*,b.status,b.cust_type_name as cust_type,g.cust_type_name,b.service_no,b.ctrt_period,f.code,f.name")
            ->from("swoper{$suffix}.swo_serviceid_info a")
            ->leftJoin("swoper{$suffix}.swo_serviceid b","a.serviceID_id=b.id")
            ->leftJoin("swoper{$suffix}.swo_company f","b.company_id=f.id")
            ->leftJoin("swoper{$suffix}.swo_customer_type_info g","b.cust_type_name=g.id")
            ->where("b.salesman_id=:id and b.status = 'C' and a.back_date between '$startDate' and '$endDate'",array(":id"=>$this->employee_id))
            ->queryAll();
        $renewal_amount = 0;//续约生意额提成
        $renewal_money=0;//续约回款
        if($records){
            foreach ($records as $record){
                $commission = 0;//是否已經計算 1：已计算
                $rate_num = 0;//提成比例
                $comm_money = 0;//实际计算回款金额
                if (in_array($record["id"],$this->updateList)){
                    //需要计算
                    $commission = 1;
                    $rate_num = IDCommissionList::getRateNum($record["cust_type"],$record["ctrt_period"],$this->city);
                    $comm_money = $record["back_money"]*$record["back_ratio"]*0.01;
                }
                Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_serviceid_info",array(
                    'commission'=>$commission,
                    'comm_money'=>$comm_money,
                    'rate_num'=>$rate_num,
                ),"id={$record['id']}");
                $renewal_money+=$comm_money;
                $renewal_amount+=$comm_money*$rate_num;
            }
        }
        $this->saveFileNameAndValue("renewal_amount",$renewal_amount,1);
        $this->saveFileNameAndValue("renewal_money",$renewal_money);
        $this->resetAllMoney();
    }

    private function saveFileNameAndValue($file_name,$file_value,$file_type=0){
        $uid = Yii::app()->user->id;
        $row = Yii::app()->db->createCommand()->select("id")->from("acc_serviceid_comm_dtl")
            ->where("file_name=:file_name and hdr_id=:hdr_id",array(":file_name"=>$file_name,":hdr_id"=>$this->id))->queryRow();
        if($row){
            Yii::app()->db->createCommand()->update("acc_serviceid_comm_dtl",array(
                'file_type'=>$file_type,
                'file_value'=>$file_value,
                'luu'=>$uid,
            ),"id={$row['id']}");
        }else{
            Yii::app()->db->createCommand()->insert("acc_serviceid_comm_dtl",array(
                'file_type'=>$file_type,
                "file_name"=>$file_name,
                "file_value"=>$file_value,
                "hdr_id"=>$this->id,
                "lcu"=>$uid,
            ));
        }
    }

    //重新计算总金额
    public function resetAllMoney(){
        $allMoney = Yii::app()->db->createCommand()->select("sum(file_value)")->from("acc_serviceid_comm_dtl")
            ->where("file_type=1 and hdr_id=:hdr_id",array(":hdr_id"=>$this->id))->queryScalar();
        Yii::app()->db->createCommand()->update("acc_serviceid_comm_hdr",array(
            "sum_amount"=>$allMoney
        ),"id={$this->id}");
    }
}
