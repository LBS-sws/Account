<?php
class CurlForPayment extends CurlForJD{
    protected $info_type="payment";

    protected function resetModelTableDetailForE($model){
        $list = array();
        if(!empty($model->infoDetail)){
            foreach ($model->infoDetail as $row){
                $infoJson = json_decode($row["infoJson"],true);
                $temp = array(
                    "infoDate"=>$row["infoDate"],
                    "infoRemark"=>$row["infoRemark"],
                );
                if(!empty($infoJson)){
                    foreach ($infoJson as $key=>$item){
                        if(!empty($item)){
                            $temp["amtType"] = $key;
                            $temp["infoAmt"] = $item;
                            $list[]=$temp;
                        }
                    }
                }
            }
        }
        $model->infoDetail = $list;
    }

    protected function resetModelTableDetailForT($model){
        $list = array();
        $list[] = array("infoAmt"=>$model->amt_money);
        $model->infoDetail = $list;
    }

    //日常费用银行确认
    public function sendJDCurlForPayment($model){
        $className = get_class($model);
        switch ($className){
            case "TemporaryAuditForm"://暂支单
                $this->info_type = "temporaryAudit";
                $this->resetModelTableDetailForT($model);
                $curlData=$this->getDataForRemitModelTwo($model);
                $data = array("data"=>$curlData);
                $url = "/kapi/v2/lbs/ap/ap_payapply/save";
                break;
            case "ExpenseAuditForm"://日常费用报销
                $this->info_type = "expenseAudit";
                $this->resetModelTableDetailForE($model);
                $curlData=$this->getDataForRemitModelThree($model);
                $data = array("data"=>$curlData);
                $url = "/kapi/v2/lbs/ap/ap_finapbill/save";
                break;
            case "RemitAuditForm"://日常付款
                $this->info_type = "remitAudit";
                switch ($model->jd_curl_type){
                    case 2://付款申请-保存
                        $curlData=$this->getDataForRemitModelTwo($model);
                        $data = array("data"=>$curlData);
                        $url = "/kapi/v2/lbs/ap/ap_payapply/save";
                        break;
                    case 3://财务应付-保存
                        $curlData=$this->getDataForRemitModelThree($model);
                        $data = array("data"=>$curlData);
                        $url = "/kapi/v2/lbs/ap/ap_finapbill/save";
                        break;
                    case 6://预付：采购订单下推付款申请
                        $curlData=$this->getDataForRemitModelSix($model);
                        $data = $curlData;
                        $url = "/kapi/v2/lbs/ap/push/v2";
                        break;
                    case 7://采购付款：财务应付下推付款申请
                        $curlData=$this->getDataForRemitModelSeven($model);
                        $data = $curlData;
                        $url = "/kapi/v2/lbs/ap/push/v2";
                        break;
                    default:
                        return array('message'=>'数据异常', 'code'=>400,'outData'=>'');
                }
                break;
            default:
                return array('message'=>'数据异常', 'code'=>400,'outData'=>'');
        }

        $rtn = $this->sendData($data,$url);
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
            ->where("table_id=:table_id and field_id='jd_org_code'",array(':table_id'=>$acc_id))
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

    public static function getEmployeeListForID($id){
        $suffix = Yii::app()->params['envSuffix'];
        $list = Yii::app()->db->createCommand()->select("id,code,name,department,staff_id")
            ->from("hr{$suffix}.hr_employee")
            ->where("id=:id",array(':id'=>$id))
            ->queryRow();
        if($list){
            return $list;
        }else{
            return array("code"=>"","department"=>"");
        }
    }

    public static function getSupplierCodeForName($name){
        if(empty($name)){
            return "";
        }
        $suffix = Yii::app()->params['envSuffix'];
        $list = Yii::app()->db->createCommand()->select("code")
            ->from("swoper{$suffix}.swo_supplier")
            ->where("name=:name",array(':name'=>$name))
            ->queryRow();
        if($list){
            return $list["code"];
        }else{
            return "";
        }
    }

    public static function getCompanyCodeForID($id){
        $suffix = Yii::app()->params['envSuffix'];
        $list = Yii::app()->db->createCommand()->select("field_value")
            ->from("hr{$suffix}.hr_send_set_jd")
            ->where("table_id=:id and set_type='company' and field_id='jd_company_code'",array(':id'=>$id))
            ->queryRow();
        if($list){
            return $list["field_value"];
        }else{
            return "";
        }
    }

    public static function getHrFileCodeForID($id,$field_id="dept_code"){
        $suffix = Yii::app()->params['envSuffix'];
        $list = Yii::app()->db->createCommand()->select("field_value")
            ->from("hr{$suffix}.hr_send_set_jd")
            ->where("table_id=:id and field_id=:field_id",array(':id'=>$id,':field_id'=>$field_id))
            ->queryRow();
        if($list){
            return $list["field_value"];
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
            ->where("table_id=:table_id and field_id='jd_trans_code'",array(':table_id'=>$id))
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
            "applydate"=>General::toMyDate($model->apply_date),//申请日期
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
                "info_date"=>General::toMyDate($infoRow["infoDate"]),//LBS日期
                "info_remark"=>$infoRow["infoRemark"],//LBS摘要
                "e_settlementtype_number"=>$payment_code,//结算方式.编码
            );
            $curlData["entry"][]=$temp;
        }
        return $curlData;
    }

    //付款申请-保存
    private function getDataForRemitModelTwo($model){
        $tableDetail = ExpenseFun::getExpenseTableDetailForID($model->id);
        $companyID = key_exists("payment_company",$tableDetail)?$tableDetail["payment_company"]["field_value"]:0;
        $companyCode = self::getCompanyCodeForID($companyID);
        $supplierCode = key_exists("payee_code",$tableDetail)?$tableDetail["payee_code"]["field_value"]:0;
        $supplierList = array();
        if(!empty($supplierCode)){//有供应商
            $supplierList["e_asstacttype"]="bd_supplier";//往来类型
            $supplierList["e_asstact_number"]=$supplierCode;//往来户.编码
        }else{
            $employeeCode = self::getEmployeeCodeForID($model->employee_id);
            $supplierList["e_asstacttype"]="bos_user";//往来类型
            $supplierList["e_asstact_number"]=$employeeCode;//往来户.编码
        }
        //往来账户
        if(key_exists("taxpayer_no",$tableDetail)){
            $curlData["payeebanknum"] = $tableDetail["taxpayer_no"]["field_value"];
        }
        //往来银行.编码
        if(key_exists("bank_no",$tableDetail)){
            $curlData["e_bebank_number"] = $tableDetail["bank_no"]["field_value"];
        }
        //付款类型.编码201:采购付款,202:预付款,210	:个人借款
        $supplierList["e_paymenttype_number"]=isset($model->tableDetail["prepayment"])&&$model->tableDetail["prepayment"]==1?"202":"210";

        $curlData=array(
            "lbs_id"=>$model->id,
            "lbs_code"=>$model->exp_code,//单据编号
            "billtype_number"=>"ap_payapply_oth_BT_S",//单据类型.编码
            "applydate"=>General::toMyDate($model->apply_date),//申请日期
            "applyorg_number"=>$companyCode,//申请组织.编码
            "payorg_number"=>$companyCode,//付款组织.编码
            "purorg_number"=>$companyCode,//采购组织.编码
            "paycurrency_number"=>"CNY",//付款币别.货币代码
            "settlecurrency_number"=>"CNY",//结算币别.货币代码
            "exratetable_number"=>"ERT-01",//汇率表.编码
            "entry"=>array(),//付款申请分录
        );
        //请款事由
        if(key_exists("purpose_text",$tableDetail)){
            $curlData["applycause"] = $tableDetail["purpose_text"]["field_value"];
        }
        foreach ($model->infoDetail as $infoRow){
            $temp=array(
                "e_payamount"=>$infoRow["infoAmt"],//应付金额
                "e_applyamount"=>$infoRow["infoAmt"],//申请金额
                //"e_expaydate"=>null,//期望付款日
                //"e_duedate"=>null,//到期日
                "e_settlementtype_number"=>"JSFS04",//JSFS04
            );
            $temp =array_merge($temp,$supplierList);
            $curlData["entry"][]=$temp;
        }
        return $curlData;
    }

    //财务应付-保存
    private function getDataForRemitModelThree($model){
        $tableDetail = ExpenseFun::getExpenseTableDetailForID($model->id);
        $companyID = key_exists("payment_company",$tableDetail)?$tableDetail["payment_company"]["field_value"]:0;
        $companyCode = self::getCompanyCodeForID($companyID);
        $supplierCode = key_exists("payee_code",$tableDetail)?$tableDetail["payee_code"]["field_value"]:0;
        $employeeList = self::getEmployeeListForID($model->employee_id);
        $deptCode = self::getHrFileCodeForID($employeeList["department"],"jd_dept_code");//department_number
        $supplierList = array();
        if(!empty($supplierCode)){//有供应商
            $supplierList["asstacttype"]="bd_supplier";//往来类型
            $supplierList["asstact_number"]=$supplierCode;//往来户.编码
            $supplierList["payproperty_number"]="2010";//款项性质.编码,2008:员工报销,2010:其他费用采购
        }else{
            $employeeCode = $employeeList["code"];
            $supplierList["payproperty_number"]="2008";//款项性质.编码,2008:员工报销,2010:其他费用采购
            $supplierList["asstacttype"]="bos_user";//往来类型
            $supplierList["asstact_number"]=$employeeCode;//往来户.编码
        }

        $lud = date_format(date_create(),"Y-m-d");
        $curlData=array(
            "lbs_id"=>$model->id,
            "lbs_code"=>$model->exp_code,//单据编号
            "billtypeid_number"=>"ApFin_other_BT_S",//单据类型.编码
            "org_number"=>$companyCode,//结算组织.编码
            "bizdate"=>$lud,//单据日期
            "purmode"=>"CREDIT",//付款方式
            "currency_number"=>"CNY",//结算币别.货币代码
            "exchangerate"=>1,//汇率
            "exratetable_number"=>"ERT-01",//汇率表.编码
            "payorg_number"=>$companyCode,//付款组织.编码
            "paycond_number"=>key_exists("payment_condition",$tableDetail)?$tableDetail["payment_condition"]["field_value"]:null,//付款条件.编码-由业务人员提供
            //"settlementtype_number"=>"JSFS04",//结算方式.编码
            "isincludetax"=>true,//录入含税价
            "department_number"=>$deptCode,//部门.编码
            "exratedate"=>$lud,//汇率日期
            "ispricetotal"=>true,//录入总价
            "purorg_number"=>$companyCode,//采购组织.编码
            "remark"=>$model->remark,//备注
            "isfx"=>false,//微调金额
            "isfxpricetaxtotal"=>false,//微调应付金额
            "detailentry"=>array(),//微调应付金额
        );
        //往来账户
        if(key_exists("bank_no",$tableDetail)){
            $curlData["payeebanknum"] = $tableDetail["bank_no"]["field_value"];
        }
        $curlData =array_merge($curlData,$supplierList);
        foreach ($model->infoDetail as $infoRow){
            $temp=array(
                "expenseitem_number"=>$infoRow["amtType"],//费用项目.编码
                "quantity"=>1,//明细.数量
                "pricetax"=>$infoRow["infoAmt"],//明细.含税单价
                "discountmode"=>'NULL',//明细.含税单价
                "e_remark"=>$infoRow["infoRemark"],//明细.含税单价
                "e_pricetaxtotal"=>$infoRow["infoAmt"],//明细.应付金额
                "e_amount"=>$infoRow["infoAmt"],//明细.金额
                "lbs_costdept_number"=>$deptCode,//明细.费用承担部门
            );
            $temp =array_merge($temp,$supplierList);
            $curlData["detailentry"][]=$temp;
        }
        return $curlData;
    }

    //预付：采购订单下推付款申请
    private function getDataForRemitModelSix($model){
        $tableDetail = ExpenseFun::getExpenseTableDetailForID($model->id);
        $purchase_code = key_exists("purchase_code",$tableDetail)?$tableDetail["purchase_code"]["field_value"]:null;
        $curlData=array(
            "data"=>array(
                "lbs_id"=>$model->id,
                "lbs_code"=>$model->exp_code,//单据编号
                "applydate"=>General::toMyDate($model->apply_date),//申请日期
                "comment"=>$model->remark,//备注
                "applycause"=>$model->remark,//请款事由
                "entry"=>array(
                    array(
                        "e_payamount"=>$model->amt_money,//应付金额
                        //LBS采购订单/财务应付单
                        "_matchRowKey_"=>$purchase_code,
                        //{"billno": "采购订单号"}
                        "_matchRowVal_"=>array("billno"=>$purchase_code),
                    )
                ),//
            ),
            "rule"=>array(
                "id"=>"",//转换规则id 待业务提供
                "sourceEntityNumber"=>"pur_orderbill",//源单编码
                "targetEntityNumber"=>"ap_payapply",//源单编码
            ),//
            "matchKeys"=>array("billno"),
        );
        return $curlData;
    }

    //采购付款：财务应付下推付款申请
    private function getDataForRemitModelSeven($model){
        $tableDetail = ExpenseFun::getExpenseTableDetailForID($model->id);
        $purchase_code = key_exists("purchase_code",$tableDetail)?$tableDetail["purchase_code"]["field_value"]:null;
        $curlData=array(
            "data"=>array(
                "lbs_id"=>$model->id,
                "lbs_code"=>$model->exp_code,//单据编号
                "applydate"=>General::toMyDate($model->apply_date),//申请日期
                "comment"=>$model->remark,//备注
                "applycause"=>$model->remark,//请款事由
                "entry"=>array(
                    array(
                        "e_payamount"=>$model->amt_money,//应付金额
                        //LBS采购订单/财务应付单
                        "_matchRowKey_"=>$purchase_code,
                        //{"billno": "采购订单号"}
                        "_matchRowVal_"=>array("billno"=>$purchase_code),
                    )
                ),//
            ),
            "rule"=>array(
                "id"=>"",//转换规则id 待业务提供
                "sourceEntityNumber"=>"ap_finapbill",//源单编码
                "targetEntityNumber"=>"ap_payapply",//源单编码
            ),//
            "matchKeys"=>array("billno"),
        );
        return $curlData;
    }
}
