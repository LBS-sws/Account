<?php

class RemitAuditForm extends ExpenseAuditForm
{
    protected $table_type=2;
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

}