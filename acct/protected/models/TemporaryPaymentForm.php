<?php

class TemporaryPaymentForm extends ExpensePaymentForm
{
    protected $table_type=3;
    protected $fileList=array(
        array("field_id"=>"loan_start_date","field_type"=>"list","field_name"=>"loan start date","display"=>"none"),//借款开始日期
        array("field_id"=>"loan_end_date","field_type"=>"list","field_name"=>"loan end date","display"=>"none"),//借款结束日期
        array("field_id"=>"purpose_text","field_type"=>"list","field_name"=>"purpose text","display"=>"none"),//用途
        //array("field_id"=>"payment_condition","field_type"=>"list","field_name"=>"payment condition","display"=>"none"),//付款条件
        array("field_id"=>"payment_company","field_type"=>"list","field_name"=>"payment company","display"=>"none"),//支付公司
    );
}