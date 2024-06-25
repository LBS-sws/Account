<?php
class CurlForPayment extends CurlForJD{
    protected $info_type="payment";

    //日常费用银行确认
    public function sendJDCurlForPayment($model){
        $className = get_class($model);
        switch ($className){
            case "ExpensePaymentForm"://日常费用报销
                $this->info_type = "expensePayment";
                $curlData=$this->getDataForExpenseModel($model);
                break;
            case "RemitPaymentForm"://日常付款
                $this->info_type = "remitPayment";
                $curlData=$this->getDataForRemitModel($model);
                break;
            default:
                $curlData=array();
        }
        $data = array($curlData);
        $rtn = $this->sendData($data,"/kapi/v2/lbs/ap/ap_payapply/save");
        //$rtn = array('message'=>'', 'code'=>200,'outData'=>'');//成功时code=200；
        return $rtn;
    }

    public static function getJDCityCodeForCity($city){
        $suffix = Yii::app()->params['envSuffix'];
        $list = Yii::app()->db->createCommand()->select("field_value")
            ->from("security{$suffix}.sec_city_info")
            ->where("code=:code and field_id='JD_city'",array(':code'=>$city))
            ->queryRow();
        if($list){
            return $list["field_value"];
        }else{
            return "";
        }
    }

    public static function getJDCityCodeForAccount($acc_id){
        $suffix = Yii::app()->params['envSuffix'];
        $list = Yii::app()->db->createCommand()->select("field_value")
            ->from("account{$suffix}.acc_send_set_jd")
            ->where("table_id=:table_id and set_type='jd_org_code'",array(':table_id'=>$acc_id))
            ->queryRow();
        if($list){
            return $list["field_value"];
        }else{
            return "";
        }
    }

    public static function getEmployeeCodeForID($id){
        $suffix = Yii::app()->params['envSuffix'];
        $list = Yii::app()->db->createCommand()->select("code")
            ->from("hr{$suffix}.hr_employee")
            ->where("id=:id",array(':id'=>$id))
            ->queryRow();
        if($list){
            return $list["code"];
        }else{
            return "";
        }
    }

    public static function getAccountListToID($id){
        $suffix = Yii::app()->params['envSuffix'];
        $list = Yii::app()->db->createCommand()->select("*")
            ->from("account{$suffix}.acc_account")
            ->where("id=:id",array(':id'=>$id))
            ->queryRow();
        if($list){
            return $list;
        }else{
            return array();
        }
    }

    public static function getPaymentCodeToType($id){
        $suffix = Yii::app()->params['envSuffix'];
        $list = Yii::app()->db->createCommand()->select("field_value")
            ->from("account{$suffix}.acc_send_set_jd")
            ->where("table_id=:table_id and set_type='jd_trans_code'",array(':table_id'=>$id))
            ->queryRow();
        if($list){
            return $list["field_value"];
        }else{
            return "";
        }
    }

    private function getDataForExpenseModel($model){
        $curlData=array(
            "lbs_id"=>$model->id,
            "billno"=>$model->exp_code,//单据编号
            "billtype_number"=>"ap_payapply_oth_BT_S",//单据类型.编码
            "applydate"=>$model->apply_date,//申请日期
            "applyorg_number"=>self::getJDCityCodeForCity($model->city),//申请组织.编码
            "payorg_number"=>self::getJDCityCodeForAccount($model->acc_id),//付款组织.编码
            "purorg_number"=>"",//采购组织.编码
            "paycurrency_number"=>"CNY",//付款币别.货币代码
            "settlecurrency_number"=>"CNY",//结算币别.货币代码
            "exratetable_number"=>"ERT-01",//汇率表.编码
            "entry"=>array(),//付款申请分录
        );
        $accountList = self::getAccountListToID($model->acc_id);
        $payment_code = self::getPaymentCodeToType($model->payment_type);
        foreach ($model->infoDetail as $infoRow){
            $temp=array(
                "e_paymenttype_number"=>ExpenseFun::getAmtTypeStrToKey($infoRow["amtType"]),//付款类型.编码
                "e_asstacttype"=>"bos_user",//往来类型
                "e_asstact_number"=>self::getEmployeeCodeForID($model->employee_id),//往来户.编码
                "e_assacct"=>empty($accountList)?"":$accountList["bank_name"],//往来账户
                "e_bebank_number"=>empty($accountList)?"":$accountList["acct_no"],//往来银行.编码
                "e_applyamount"=>$infoRow["infoAmt"],//申请金额
                "e_expaydate"=>$model->payment_date,//期望付款日
                "info_date"=>$infoRow["infoDate"],//LBS日期
                "info_remark"=>$infoRow["infoRemark"],//LBS摘要
                "e_settlementtype_number"=>$payment_code,//结算方式.编码
            );
            $curlData["entry"][]=$temp;
        }
        return $curlData;
    }

    private function getDataForRemitModel($model){
        $curlData=array(
            "lbs_id"=>$model->id,
            "billno"=>$model->exp_code,//单据编号
            "billtype_number"=>"ap_payapply_oth_BT_S",//单据类型.编码
            "applydate"=>$model->apply_date,//申请日期
            "applyorg_number"=>self::getJDCityCodeForCity($model->city),//申请组织.编码
            "payorg_number"=>self::getJDCityCodeForAccount($model->acc_id),//付款组织.编码
            "purorg_number"=>"",//采购组织.编码
            "paycurrency_number"=>"CNY",//付款币别.货币代码
            "settlecurrency_number"=>"CNY",//结算币别.货币代码
            "exratetable_number"=>"ERT-01",//汇率表.编码
            "entry"=>array(),//付款申请分录
        );
        $accountList = self::getAccountListToID($model->acc_id);
        $payment_code = self::getPaymentCodeToType($model->payment_type);
        foreach ($model->infoDetail as $infoRow){
            $temp=array(
                "e_paymenttype_number"=>$infoRow["amtType"],//付款类型.编码
                "e_asstacttype"=>empty($model->tableDetail["outside"])?"bos_user":"bd_customer",//往来类型
                "e_asstact_number"=>self::getEmployeeCodeForID($model->employee_id),//往来户.编码
                "e_assacct"=>empty($accountList)?"":$accountList["bank_name"],//往来账户
                "e_bebank_number"=>empty($accountList)?"":$accountList["acct_no"],//往来银行.编码
                "e_applyamount"=>$infoRow["infoAmt"],//申请金额
                "e_expaydate"=>$model->payment_date,//期望付款日
                "info_date"=>$infoRow["infoDate"],//LBS日期
                "info_remark"=>$infoRow["infoRemark"],//LBS摘要
                "e_settlementtype_number"=>$payment_code,//结算方式.编码
            );
            $curlData["entry"][]=$temp;
        }
        return $curlData;
    }
}
