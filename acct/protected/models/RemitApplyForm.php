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
        'purchase_type'=>0,//是否采购单
        'purchase_code'=>null,//采购单编号
        'payment_condition'=>null,//付款条件
    );
    protected $fileList=array(
        array("field_id"=>"outside","field_type"=>"list","field_name"=>"outside","display"=>"none"),//外部
        array("field_id"=>"payee_code","field_type"=>"text","field_name"=>"payee_code","display"=>"none"),//供应商编号
        array("field_id"=>"payee","field_type"=>"text","field_name"=>"payee","display"=>"none"),//收款单位
        array("field_id"=>"taxpayer_no","field_type"=>"text","field_name"=>"taxpayer no","display"=>"none"),//纳税人识别号
        array("field_id"=>"bank_name","field_type"=>"text","field_name"=>"bank name","display"=>"none"),//开户行名称
        array("field_id"=>"bank_no","field_type"=>"text","field_name"=>"bank no","display"=>"none"),//银行帐号

        array("field_id"=>"urgent","field_type"=>"list","field_name"=>"urgent","display"=>"none"),//加急
        array("field_id"=>"end_pay_date","field_type"=>"text","field_name"=>"end pay date","display"=>"none"),//最晚付款日
        array("field_id"=>"invoice_bool","field_type"=>"list","field_name"=>"invoice bool","display"=>"none"),//发票情况
        array("field_id"=>"invoice_no","field_type"=>"text","field_name"=>"invoice no","display"=>"none"),//发票号码

        array("field_id"=>"prepayment","field_type"=>"list","field_name"=>"prepayment","display"=>"none"),//预付款
        array("field_id"=>"purchase_type","field_type"=>"list","field_name"=>"purchase bool","display"=>"none"),//是否采购单
        array("field_id"=>"purchase_code","field_type"=>"list","field_name"=>"purchase code","display"=>"none"),//采购单编号
        array("field_id"=>"payment_condition","field_type"=>"list","field_name"=>"payment condition","display"=>"none"),//付款条件
        array("field_id"=>"payment_company","field_type"=>"list","field_name"=>"payment company","display"=>"none"),//支付公司
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
        //in_array($model->tableDetail['purchase_type'],array("A0","A4","A5","A7")
        if (!key_exists("purchase_type",$this->tableDetail)||$this->tableDetail["purchase_type"]===""){
            $this->addError($attribute, "付款类别不能为空");
        }else{
            if(in_array($this->tableDetail['purchase_type'],array("A0"))){//是物料采购
                if(empty($this->tableDetail["purchase_code"])){
                    $this->addError($attribute, "采购订单/财务应付单不能为空");
                }elseif (strpos($this->tableDetail["purchase_code"],'CGDD')===false&&strpos($this->tableDetail["purchase_code"],'AP')===false){
                    $this->addError($attribute, "采购订单/财务应付单 必须包含CGDD或AP");
                }
            }else{
                $this->tableDetail["purchase_code"]=null;
            }
        }
    }

    public function validateInfo($attribute, $params) {
        $updateList = array();
        $deleteList = array();
        $this->amt_money = 0;
        foreach ($this->infoDetail as $rowKey=>$list){
            $list["rowKey"] = $rowKey;
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
                    if($list["amtType"]===""){
                        $this->addError($attribute, "费用类别不能为空");
                        break;
                    }
                }
            }
        }

        if(empty($updateList)){
            $this->addError($attribute, "报销明细不能为空");
            return false;
        }
        $this->infoDetail = array_merge($updateList,$deleteList);
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
        $this->exp_code = "RET".(100000+$this->id);
        $connection->createCommand()->update("acc_expense", array(
            "exp_code"=>$this->exp_code,
        ), "id=:id", array(":id" =>$this->id));
    }


    protected function setPDFTable($pdf,$model){
        //申请信息
        $amt_money_max = ExpenseFun::convertCurrency($model->amt_money);
        $tdTwoList = ExpenseFun::getAmtTypeTwo();
        $setNameList = ExpenseSetNameForm::getExpenseSetAllList();
        $amtTypeList = ExpenseFun::getAmtTypeOne();
        $tdCount = count($tdTwoList);
        $tableOneWidth=270;
        $tableTwoWidth=$tableOneWidth*2;
        $tableBoxWidth=565;
        $employeeList = ExpenseFun::getEmployeeListForID($model->employee_id);
        $html=<<<EOF
<table border="0" width="{$tableBoxWidth}px" cellspacing="0" cellpadding="0" style="line-height: 18px;">
<tr>
<th style="text-align: center;font-size:12px"><b>日常付款申请</b></th>
</tr>
</table>
EOF;
        //申请人
        $html.=<<<EOF
<table border="0" width="{$tableBoxWidth}px" cellspacing="0" cellpadding="0" style="line-height: 18px;border-bottom: 2px solid black;border-left: 2px solid black;">
<tr>
<th colspan="2" style="width:45%;background-color:#BFBFBF;border-left: 2px solid black;border-top: 2px solid black;border-right: 2px solid black;">&nbsp;<b>PART A:基本信息</b></th>
<th colspan="3" style="width:55%;border-bottom:2px solid black;">&nbsp;</th>
</tr>
<tr>
<td style="border-top:1px solid black;border-right:1px solid black;width:16%">&nbsp;申请人</td><td colspan="2" style="width:34%;border-top:1px solid black;border-right:1px solid black;">&nbsp;{$employeeList['employee']}</td>
<td style="border-top:1px solid black;border-right:1px solid black;width:20%">&nbsp;申请日期</td><td style="width:30%;border-top:1px solid black;border-right:2px solid black;">&nbsp;{$model->apply_date}</td>
</tr>
<tr>
<td colspan="5" style="border-top:1px solid black;border-right:2px solid black;">&nbsp;外部：<span>个人□&nbsp;</span>&nbsp;<span>公司□&nbsp;</span>&nbsp;<span> 注：外部汇款需填收款资料部分</span></td>
</tr>
<tr>
<td style="border-top:1px solid black;border-right:1px solid black;width:16%">&nbsp;收款单位：</td><td colspan="2" style="width:34%;border-top:1px solid black;border-right:1px solid black;">&nbsp;{$model->tableDetail['payee']}</td>
<td style="border-top:1px solid black;border-right:1px solid black;width:20%">&nbsp;加急：</td><td style="width:30%;border-top:1px solid black;border-right:2px solid black;">&nbsp;<span>是□&nbsp;</span>&nbsp;<span>否□</span></td>
</tr>
<tr>
<td style="border-top:1px solid black;border-right:1px solid black;width:16%">&nbsp;纳税人识别号：</td><td colspan="2" style="width:34%;border-top:1px solid black;border-right:1px solid black;">&nbsp;{$model->tableDetail['taxpayer_no']}</td>
<td style="border-top:1px solid black;border-right:1px solid black;width:20%">&nbsp;最晚付款日：</td><td style="width:30%;border-top:1px solid black;border-right:2px solid black;">&nbsp;{$model->tableDetail['end_pay_date']}</td>
</tr>
<tr>
<td style="border-top:1px solid black;border-right:1px solid black;width:16%">&nbsp;开户行名称：</td><td colspan="2" style="width:34%;border-top:1px solid black;border-right:1px solid black;">&nbsp;{$model->tableDetail['bank_name']}</td>
<td style="border-top:1px solid black;border-right:1px solid black;width:20%">&nbsp;发票情况：</td><td style="width:30%;border-top:1px solid black;border-right:2px solid black;">&nbsp;<span>有□&nbsp;</span>&nbsp;<span>无□</span></td>
</tr>
<tr>
<td style="border-top:1px solid black;border-right:1px solid black;width:16%">&nbsp;银行帐号：</td><td colspan="2" style="width:34%;border-top:1px solid black;border-right:1px solid black;">&nbsp;{$model->tableDetail['bank_no']}</td>
<td style="border-top:1px solid black;border-right:1px solid black;width:20%">&nbsp;发票号码：</td><td style="width:30%;border-top:1px solid black;border-right:2px solid black;">&nbsp;{$model->tableDetail['invoice_no']}</td>
</tr>
</table>
EOF;
        $html.="<p>&nbsp;</p>";
        $tableInfoHtml="";
        $tableFooterHtml='<tr>';
        $tableFooterHtml.='<td colspan="4" style="text-align: right;border-top:1px solid black;border-right:1px solid black;border-left:2px solid black;"><b>人民币合计(RMB)</b>&nbsp;</td>';
        $tableFooterHtml.='<td style="text-align: right;border-top:1px solid black;border-right:2px solid black;"><b>'.$model->amt_money.'</b>&nbsp;</td>';
        $tableFooterHtml.='</tr>';
        if(!empty($this->infoDetail)){
            $style="border-top:1px solid black;border-right:1px solid black;";
            foreach ($this->infoDetail as $infoRow){
                $tableInfoHtml.="<tr>";
                $tableInfoHtml.='<td style="border-left:2px solid black;'.$style.'text-align:center;">'.ExpenseFun::getKeyNameForList($setNameList,$infoRow["setId"])."</td>";
                $tableInfoHtml.='<td style="'.$style.'text-align:center;">'.$infoRow["infoDate"]."</td>";
                $tableInfoHtml.='<td style="'.$style.'text-align:center;">'.ExpenseFun::getKeyNameForList($amtTypeList,$infoRow["amtType"])."</td>";
                $tableInfoHtml.='<td style="'.$style.'text-align:center;">'.$infoRow["infoRemark"]."</td>";
                $tableInfoHtml.='<td style="border-top:1px solid black;border-right:2px solid black;text-align:right;">'.$infoRow["infoAmt"]."&nbsp;</td>";
                $tableInfoHtml.="</tr>";
            }
        }
        //报销明细
        $html.=<<<EOF
<table border="0" width="{$tableBoxWidth}px" cellspacing="0" cellpadding="0" style="line-height: 18px;border-bottom: 2px solid black;border-left: 2px solid black;">
<tr>
<th colspan="3" style="width:45%;background-color:#BFBFBF;border-left: 2px solid black;border-top: 2px solid black;border-right: 2px solid black;">&nbsp;<b>PART B : 付款明细</b></th>
<th colspan="2" style="width:55%;border-bottom:2px solid black;">&nbsp;</th>
</tr>
<tr>
<td style="border-top:1px solid black;border-right:1px solid black;width:15%;text-align:center;"><b>费用归属</b></td>
<td style="border-top:1px solid black;border-right:1px solid black;width:15%;text-align:center;"><b>日期</b></td>
<td style="border-top:1px solid black;border-right:1px solid black;width:15%;text-align:center;"><b>类别</b></td>
<td style="border-top:2px solid black;border-right:1px solid black;width:40%;text-align:center;"><b>摘要</b></td>
<td style="border-top:2px solid black;border-right:2px solid black;width:15%;text-align:center;"><b>金额</b></td>
</tr>
{$tableInfoHtml}
{$tableFooterHtml}
<tr>
<th style="border-top:1px solid black;border-right:1px solid black;border-bottom:2px solid black;border-left:2px solid black;">&nbsp;<b style="line-height:35px;">人民币大写</b></th>
<th colspan="4" style="font-size:15px;text-align:center;border-top:1px solid black;border-right:2px solid black;border-bottom:2px solid black;"><b>{$amt_money_max}</b></th>
</tr>
</table>
EOF;
        $pdf->writeHTML($html, true, false, false, false, '');

        $auditHtml = "";
        $auditList = ExpenseFun::getAuditListForID($model->id);
        if(!empty($auditList)){
            foreach ($auditList as $userList){
                $userList['audit_user'] = ExpenseFun::getEmployeeNameForUsername($userList['audit_user']);
                $auditHtml.='<tr style="line-height: 30px;">';
                $auditHtml.='<td style="border-top:1px solid black;border-right:1px solid black;width:15%">&nbsp;审核人</td>';
                $auditHtml.='<td style="width:30%;border-top:1px solid black;border-right:1px solid black;">&nbsp;'.$userList['audit_user'].'</td>';
                $auditHtml.='<td style="border-top:1px solid black;border-right:1px solid black;width:20%">&nbsp;审核时间</td>';
                $auditHtml.='<td style="width:35%;border-top:1px solid black;border-right:2px solid black;">&nbsp;'.$userList['lcd'].'</td> ';
                $auditHtml.='</tr>';
            }
        }
        //审核人
        $html=<<<EOF
<table border="0" width="{$tableBoxWidth}px" cellspacing="0" cellpadding="0" style="border-bottom: 2px solid black;border-left: 2px solid black;">
<tr style="line-height: 18px;">
<th colspan="2" style="width:45%;background-color:#BFBFBF;border-left: 2px solid black;border-top: 2px solid black;border-right: 2px solid black;">&nbsp;<b>PART C:审批签字</b></th>
<th colspan="2" style="width:55%;border-bottom:2px solid black;">&nbsp;</th>
</tr>
{$auditHtml}
</table>
EOF;
        $y1=$pdf->GetY();
        $x1=$pdf->GetX()-1;
        $height = $y1<250?250:$y1;
        $pdf->writeHTMLCell(200, 27,$x1,$height, $html,0);

        $html = "√";
        if(empty($model->tableDetail['outside'])){
            $pdf->writeHTMLCell(7, 7, 20.9,26.5, $html, 0, 1, false, true, 'L', true);
        }else{
            $pdf->writeHTMLCell(7, 7, 35,26.5, $html, 0, 1, false, true, 'L', true);
        }
        if(empty($model->tableDetail['urgent'])){
            $pdf->writeHTMLCell(7, 7, 160.5,33, $html, 0, 1, false, true, 'L', true);
        }else{
            $pdf->writeHTMLCell(7, 7, 149,33, $html, 0, 1, false, true, 'L', true);
        }
        if(empty($model->tableDetail['invoice_bool'])){
            $pdf->writeHTMLCell(7, 7,160.5,46, $html, 0, 1, false, true, 'L', true);
        }else{
            $pdf->writeHTMLCell(7, 7,149,46, $html, 0, 1, false, true, 'L', true);
        }
        return $html;
    }

    protected function updateDocman(&$connection, $doctype) {
        if ($this->scenario=='new') {
            $docidx = strtolower($doctype);
            if ($this->docMasterId[$docidx] > 0) {
                $docman = new DocMan($doctype,$this->id,get_class($this));
                $docman->masterId = $this->docMasterId[$docidx];
                $docman->updateDocId($connection, $this->docMasterId[$docidx]);
            }
        }
    }
}