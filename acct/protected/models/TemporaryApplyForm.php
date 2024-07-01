<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2024/6/24 0024
 * Time: 9:17
 */
class TemporaryApplyForm extends ExpenseApplyForm
{
    protected $table_type=3;

    public $infoDetail=array();
    public $tableDetail=array(
        "loan_start_date"=>null,//借款开始日期
        'loan_end_date'=>null,//借款结束日期
        'purpose_text'=>null,//用途
        'payment_condition'=>null,//付款条件
        'payment_company'=>null,//支付公司
    );
    protected $fileList=array(
        array("field_id"=>"loan_start_date","field_type"=>"list","field_name"=>"loan start date","display"=>"none"),//借款开始日期
        array("field_id"=>"loan_end_date","field_type"=>"list","field_name"=>"loan end date","display"=>"none"),//借款结束日期
        array("field_id"=>"purpose_text","field_type"=>"list","field_name"=>"purpose text","display"=>"none"),//用途
        array("field_id"=>"payment_condition","field_type"=>"list","field_name"=>"payment condition","display"=>"none"),//付款条件
        array("field_id"=>"payment_company","field_type"=>"list","field_name"=>"payment company","display"=>"none"),//支付公司
    );

    public function validateDetail($attribute, $params){
        if (!key_exists("purpose_text",$this->tableDetail)||$this->tableDetail["purpose_text"]===""){
            $this->addError($attribute, "用途不能为空");
        }
        if ($this->amt_money===""){
            $this->addError($attribute, "金额不能为空");
        }
        if (!key_exists("loan_start_date",$this->tableDetail)||$this->tableDetail["loan_start_date"]===""){
            $this->addError($attribute, "借款开始日期不能为空");
        }
        if (!key_exists("loan_end_date",$this->tableDetail)||$this->tableDetail["loan_end_date"]===""){
            $this->addError($attribute, "借款结束日期不能为空");
        }
    }

    public function validateInfo($attribute, $params) {
        return true;
    }

    public function printOne(){
        $pdf = new MyPDF2('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->resetPDFConfig($pdf,$this);

        $pdf->AddPage();
        $this->setPDFTable($pdf,$this);
        //$pdf->writeHTML($html, true, false, false, false, '');

        ob_clean();
        $address=str_replace('/','-',$this->exp_code);
        $address.='.pdf';
        $pdf->Output($address, 'I');
        return $address;
    }

    protected function updateThisExpCode($connection){
        $this->id = Yii::app()->db->getLastInsertID();
        $this->exp_code = "TEA".(100000+$this->id);
        $connection->createCommand()->update("acc_expense", array(
            "exp_code"=>$this->exp_code,
        ), "id=:id", array(":id" =>$this->id));
    }


    protected function setPDFTable($pdf,$model){
        //申请信息
        $model->amt_money = floatval($model->amt_money);
        $amt_money_max = ExpenseFun::convertCurrency($model->amt_money);
        $tableBoxWidth=565;
        $companyList = ExpenseFun::getPaymentCompanyList();
        $companyName = ExpenseFun::getKeyNameForList($companyList,$this->tableDetail["payment_company"]);
        $employeeList = ExpenseFun::getEmployeeAllListForID($model->employee_id);
        $html=<<<EOF
        <p style="line-height: 10px;height=10px;">&nbsp;</p>
<table border="0" width="{$tableBoxWidth}px" cellspacing="0" cellpadding="0" style="line-height: 18px;">
<tr>
<th style="text-align: center;font-size:12px"><b>备用金申请表</b></th>
</tr>
</table>
EOF;
        //申请人
        $html.=<<<EOF
<table border="0" width="{$tableBoxWidth}px" cellspacing="0" cellpadding="0" style="line-height: 22px;">
<tr>
<td style="width:14%">公司名称</td>
<td style="width:35%;border-bottom:1px solid black;">{$companyName}</td>
<td style="width:2%">&nbsp;</td>
<td style="width:14%">地区</td>
<td style="width:35%;border-bottom:1px solid black;">{$employeeList["city_name"]}</td>
</tr>
<tr>
<td>申请人</td>
<td style="border-bottom:1px solid black;">{$employeeList["name"]}</td>
<td>&nbsp;</td>
<td>申请时间</td>
<td style="border-bottom:1px solid black;">{$model->apply_date}</td>
</tr>
<tr>
<td>所属部门</td>
<td style="border-bottom:1px solid black;">{$employeeList["department"]}</td>
<td>&nbsp;</td>
<td>职位</td>
<td style="border-bottom:1px solid black;">{$employeeList["position"]}</td>
</tr>
</table>
EOF;
        //申请内容
        $html.=<<<EOF
        <p style="line-height: 10px;height=10px;">&nbsp;</p>
<table border="0" width="{$tableBoxWidth}px" cellspacing="0" cellpadding="0" style="line-height: 25px;border-top:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-right:1px solid black;">
<tr>
<td style="width:10%;border-bottom:1px solid black;height:90px;">&nbsp;用途</td>
<td colspan="4" style="width:90%;border-bottom:1px solid black;">{$model->tableDetail['purpose_text']}</td>
</tr>
<tr>
<td style="border-bottom:1px solid black;">&nbsp;金额</td>
<td colspan="4" style="border-bottom:1px solid black;text-align:center;">{$model->amt_money}</td>
</tr>
<tr>
<td style="border-bottom:1px solid black;">&nbsp;人民币大写</td>
<td colspan="4" style="border-bottom:1px solid black;text-align:center;"><b>{$amt_money_max}</b></td>
</tr>
<tr>
<td style="width:10%;border-bottom:1px solid black;">&nbsp;借款期</td>
<td style="width:5%;border-bottom:1px solid black;">由：</td>
<td style="width:40%;border-bottom:1px solid black;text-align:center;">{$model->tableDetail['loan_start_date']}</td>
<td style="width:5%;border-bottom:1px solid black;">至：</td>
<td style="width:40%;border-bottom:1px solid black;text-align:center;">{$model->tableDetail['loan_end_date']}</td>
</tr>
<tr>
<td style="height:70px;">&nbsp;备注：</td>
<td colspan="4">{$model->remark}</td>
</tr>
</table>
EOF;
        //审核内容
        $html.=<<<EOF
        <p style="line-height: 10px;height=10px;">&nbsp;</p>
<table border="0" width="{$tableBoxWidth}px" cellspacing="0" cellpadding="0" style="line-height: 25px;border-top:1px solid black;border-left:1px solid black;border-bottom:1px solid black;">
<tr>
<td style="width:20%;border-right:1px solid black;">&nbsp;</td>
<td style="width:20%;border-right:1px solid black;">&nbsp;</td>
<td style="width:20%;border-right:1px solid black;">&nbsp;</td>
<td style="width:20%;border-right:1px solid black;">&nbsp;</td>
<td style="width:20%;border-right:1px solid black;">&nbsp;</td>
</tr>
<tr>
<td style="border-right:1px solid black;">&nbsp;申请人</td>
<td style="border-right:1px solid black;">&nbsp;部门主管</td>
<td style="border-right:1px solid black;">&nbsp;财务批准</td>
<td style="border-right:1px solid black;">&nbsp;地区负责人批准</td>
<td style="border-right:1px solid black;">&nbsp;签收</td>
</tr>
<tr>
<td style="width:20%;border-right:1px solid black;">&nbsp;</td>
<td style="width:20%;border-right:1px solid black;">&nbsp;</td>
<td style="width:20%;border-right:1px solid black;">&nbsp;</td>
<td style="width:20%;border-right:1px solid black;">&nbsp;</td>
<td style="width:20%;border-right:1px solid black;text-align:center;"><small>(如付现金请签字）</small></td>
</tr>
<tr>
<td style="border-right:1px solid black;height:33px;">&nbsp;签名</td>
<td style="border-right:1px solid black;height:33px;">&nbsp;签名</td>
<td style="border-right:1px solid black;height:33px;">&nbsp;签名</td>
<td style="border-right:1px solid black;height:33px;">&nbsp;签名</td>
<td style="border-right:1px solid black;height:33px;">&nbsp;签名</td>
</tr>
<tr>
<td style="border-right:1px solid black;">&nbsp;姓名/日期</td>
<td style="border-right:1px solid black;">&nbsp;姓名/日期</td>
<td style="border-right:1px solid black;">&nbsp;姓名/日期</td>
<td style="border-right:1px solid black;">&nbsp;姓名/日期</td>
<td style="border-right:1px solid black;">&nbsp;姓名/日期</td>
</tr>
</table>
EOF;
        //表格说明
        $html.=<<<EOF
        <p style="line-height: 10px;height=10px;">&nbsp;</p>
<table border="0" width="{$tableBoxWidth}px" cellspacing="0" cellpadding="0" style="line-height: 18px;">
<tr>
<td colspan="2" style="width:100%;">&nbsp;<b>总原则：</b></td>
</tr>
<tr>
<td style="width:2%;">&nbsp;</td>
<td style="width:98%;">&nbsp;1.备用金原则上随用随申领，专款专用；</td>
</tr>
<tr>
<td style="width:2%;">&nbsp;</td>
<td style="width:98%;">&nbsp;2.财务部采用银行支付（不随每月的报销时间）。</td>
</tr>
<tr>
<td style="width:2%;">&nbsp;</td>
<td style="width:98%;">&nbsp;3.备用金每半年一次清理，所有备用金每年6月12月底必须清零归还。</td>
</tr>
<tr>
<td colspan="2" style="width:100%;">&nbsp;<b>清账流程：</b></td>
</tr>
<tr>
<td style="width:2%;">&nbsp;</td>
<td style="width:98%;">&nbsp;1.申请人--归还借款差额并提交已经审批的报销单（发票必须齐全）</td>
</tr>
<tr>
<td style="width:2%;">&nbsp;</td>
<td style="width:98%;">&nbsp;2.财务部--收款记账</td>
</tr>
<tr>
<td style="width:2%;">&nbsp;</td>
<td style="width:98%;">&nbsp;3.财务部--报销单记账</td>
</tr>
<tr><td colspan="2" style="height:10px;">&nbsp;</td></tr>
<tr>
<td style="width:2%;">&nbsp;</td>
<td style="width:98%;">&nbsp;申请人总申请备用金无风险（总额不超出该员工工资额的50%，近期备用金申领都已如期归还）</td>
</tr>
</table>
EOF;
        
        $pdf->writeHTML($html, true, false, false, false, '');
        return $html;
    }

    protected function saveDataForInfo(&$connection)
    {
        $uid = Yii::app()->user->id;
        switch ($this->scenario) {
            case 'delete':
                $connection->createCommand()->delete('acc_expense_history', 'exp_id=:id',array(":id"=>$this->id));
                $connection->createCommand()->delete('acc_expense_audit', 'exp_id=:id',array(":id"=>$this->id));
                break;
            case 'new':
                break;
            case 'edit':
                break;
        }

    }
}