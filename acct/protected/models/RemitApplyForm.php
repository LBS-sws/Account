<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2024/6/24 0024
 * Time: 9:17
 */
class RemitApplyForm extends ExpenseApplyForm
{
    protected $table_type=2;

    public $tableDetail=array(
        "outside"=>0,//外部
        "payee"=>"",//收款单位
        "taxpayer_no"=>"",//纳税人识别号
        "bank_name"=>"",//开户行名称
        "bank_no"=>"",//银行帐号
        "urgent"=>0,//加急
        "end_pay_date"=>"",//最晚付款日
        "invoice_bool"=>0,//发票情况
        "invoice_no"=>"",//发票号码
    );
    protected $fileList=array(
        array("field_id"=>"outside","field_type"=>"list","field_name"=>"outside","display"=>"none"),//外部
        array("field_id"=>"payee","field_type"=>"text","field_name"=>"payee","display"=>"none"),//收款单位
        array("field_id"=>"taxpayer_no","field_type"=>"text","field_name"=>"taxpayer no","display"=>"none"),//纳税人识别号
        array("field_id"=>"bank_name","field_type"=>"text","field_name"=>"bank name","display"=>"none"),//开户行名称
        array("field_id"=>"bank_no","field_type"=>"text","field_name"=>"bank no","display"=>"none"),//银行帐号

        array("field_id"=>"urgent","field_type"=>"list","field_name"=>"urgent","display"=>"none"),//加急
        array("field_id"=>"end_pay_date","field_type"=>"text","field_name"=>"end pay date","display"=>"none"),//最晚付款日
        array("field_id"=>"invoice_bool","field_type"=>"list","field_name"=>"invoice bool","display"=>"none"),//发票情况
        array("field_id"=>"invoice_no","field_type"=>"text","field_name"=>"invoice no","display"=>"none"),//发票号码
    );

    public function validateDetail($attribute, $params){
        if (!key_exists("outside",$this->tableDetail)||$this->tableDetail["outside"]===""){
            $this->addError($attribute, "外部不能为空");
        }else{
            if($this->tableDetail["outside"]==1){//企业
                if(empty($this->tableDetail["payee"])){
                    $this->addError($attribute, "收款单位不能为空");
                }
                if(empty($this->tableDetail["taxpayer_no"])){
                    $this->addError($attribute, "纳税人识别号不能为空");
                }
                if(empty($this->tableDetail["bank_name"])){
                    $this->addError($attribute, "开户行名称不能为空");
                }
                if(empty($this->tableDetail["bank_no"])){
                    $this->addError($attribute, "银行帐号不能为空");
                }
            }
        }
        if (!key_exists("invoice_bool",$this->tableDetail)||$this->tableDetail["invoice_bool"]===""){
            $this->addError($attribute, "发票情况不能为空");
        }else{
            if($this->tableDetail["invoice_bool"]==1){//有发票情况
                if(empty($this->tableDetail["invoice_no"])){
                    $this->addError($attribute, "发票号码不能为空");
                }
            }
        }
    }

    public function validateInfo($attribute, $params) {
        $updateList = array();
        $deleteList = array();
        $this->amt_money = 0;
        foreach ($this->infoDetail as $list){
            if($list["uflag"]=="D"){
                $deleteList[] = $list;
            }else{
                if(!empty($list["infoAmt"])){
                    $list["infoAmt"] = is_numeric($list["infoAmt"])?round($list["infoAmt"],2):0;
                    $this->amt_money+=floatval($list["infoAmt"]);
                    $updateList[]=$list;
                    if(empty($list["setId"])){
                        $this->addError($attribute, "费用归属不能为空");
                        break;
                    }
                    if(empty($list["infoDate"])){
                        $this->addError($attribute, "日期不能为空");
                        break;
                    }
                    /*
                    if($list["amtType"]===""){
                        $this->addError($attribute, "费用类别不能为空");
                        break;
                    }
                    */
                }
            }
        }

        if(empty($updateList)){
            $this->addError($attribute, "报销明细不能为空");
            return false;
        }
        $this->infoDetail = array_merge($updateList,$deleteList);
    }

    protected function updateThisExpCode($connection){
        $this->id = Yii::app()->db->getLastInsertID();
        $this->exp_code = "RET".(100000+$this->id);
        $connection->createCommand()->update("acc_expense", array(
            "exp_code"=>$this->exp_code,
        ), "id=:id", array(":id" =>$this->id));
    }
}