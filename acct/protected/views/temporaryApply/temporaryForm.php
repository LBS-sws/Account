<?php
$employeeList = ExpenseFun::getEmployeeAllListForID($model->employee_id);
?>
<?php if ($model->status_type==3): ?>
<div class="form-group has-error">
    <?php echo $form->labelEx($model,'reject_note',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-5">
        <?php
        echo $form->textArea($model, 'reject_note',array('readonly'=>true,'rows'=>4));
        ?>
    </div>
</div>
<?php endif ?>
<div class="form-group">
    <?php echo $form->labelEx($model,'employee',array('class'=>"col-sm-2 control-label",'for'=>'employee')); ?>
    <div class="col-sm-2">
        <?php
        echo TbHtml::textField("employee",$employeeList["employee"],array('readonly'=>true));
        ?>
    </div>
    <?php echo $form->labelEx($model,'city_name',array('class'=>"col-sm-2 control-label",'for'=>'city_name')); ?>
    <div class="col-sm-2">
        <?php
        echo TbHtml::textField("city_name",$employeeList["city_name"],array('readonly'=>true));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'department',array('class'=>"col-sm-2 control-label",'for'=>'department')); ?>
    <div class="col-sm-2">
        <?php
        echo TbHtml::textField("department",$employeeList["department"],array('readonly'=>true));
        ?>
    </div>
    <?php echo $form->labelEx($model,'position',array('class'=>"col-sm-2 control-label",'for'=>'position')); ?>
    <div class="col-sm-2">
        <?php
        echo TbHtml::textField("position",$employeeList["position"],array('readonly'=>true));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo Tbhtml::label(Yii::t("give","payment company"),'payment_company',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php
        $payment_company = in_array($model->status_type,array(0,3))?ExpenseFun::getCompanyIdToEmployeeID($model->employee_id):$model->tableDetail["payment_company"];
        echo $form->hiddenField($model, 'tableDetail[payment_company]');
        echo TbHtml::textField("payment_company", ExpenseFun::getCompanyNameToID($payment_company),
            array('readonly'=>true,'id'=>'payment_company'
            ));
        ?>
    </div>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model,'apply_date',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-2">
        <?php
        echo $form->textField($model, 'apply_date',
            array('readonly'=>$model->readonly(),'autocomplete'=>'off',
                'prepend'=>'<span class="fa fa-calendar"></span> ',
            ));
        ?>
    </div>
    <?php if ($model->scenario!='new'): ?>
        <?php echo $form->labelEx($model,'exp_code',array('class'=>"col-sm-2 control-label")); ?>
        <div class="col-sm-2">
            <?php
            echo $form->textField($model, 'exp_code',array('readonly'=>true));
            ?>
        </div>
    <?php endif ?>
</div>


    <div class="form-group">
        <?php echo Tbhtml::label(Yii::t("give","purpose text"),'purpose_text',array('class'=>"col-sm-2 control-label",'required'=>true)); ?>
        <div class="col-sm-6">
            <?php
            echo $form->textArea($model, 'tableDetail[purpose_text]',array('readonly'=>$model->readonly(),'rows'=>4));
            ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo Tbhtml::label(Yii::t("give","cost money"),'amt_money',array('class'=>"col-sm-2 control-label",'required'=>true)); ?>
        <div class="col-sm-2">
            <?php
            echo $form->numberField($model, 'amt_money',array('readonly'=>$model->readonly(),'autocomplete'=>'off',
                'prepend'=>'<span class="fa fa-money"></span>','id'=>'amt_money'));
            ?>
        </div>
    </div>

<!--额外资料-->
<div class="form-group">
    <?php echo Tbhtml::label(Yii::t("give","loan start date"),'payment_condition',array('class'=>"col-sm-2 control-label",'required'=>true)); ?>
    <div class="col-sm-3">
        <?php
        echo $form->textField($model, 'tableDetail[loan_start_date]',
            array('readonly'=>$model->readonly(),'id'=>'loan_start_date','autocomplete'=>'off',
                'prepend'=>'<span class="fa fa-calendar"></span> '
            ));
        ?>
    </div>
    <?php echo Tbhtml::label(Yii::t("give","loan end date"),'loan_end_date',array('class'=>"col-sm-2 control-label",'required'=>true)); ?>
    <div class="col-sm-3">
        <?php
        echo $form->textField($model, 'tableDetail[loan_end_date]',
            array('readonly'=>$model->readonly(),'id'=>'loan_end_date','autocomplete'=>'off',
                'prepend'=>'<span class="fa fa-calendar"></span> '
            ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo Tbhtml::label(Yii::t("give","payment condition"),'payment_condition',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php
        echo $form->dropDownList($model, 'tableDetail[payment_condition]',ExpenseFun::getPaymentConditionList(),
            array('readonly'=>$model->readonly(),'id'=>'payment_condition','empty'=>''
            ));
        ?>
    </div>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model,'remark',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-4">
        <?php
        echo $form->textArea($model, 'remark',array('readonly'=>$model->readonly(),'rows'=>4));
        ?>
    </div>
</div>



<?php
$js = <<<EOF
$('input[name="TemporaryApplyForm[tableDetail][purchase_bool]"]').on('click',function() {
    if($(this).val()==0){
        $('#purchase_code').val('').attr('readonly','readonly').addClass('readonly');
    }else{
        $('#purchase_code').removeAttr('readonly').removeClass('readonly');
    }
});
$('input[name="TemporaryApplyForm[tableDetail][invoice_bool]"]').on('click',function() {
    if($(this).val()==0){
        $('#invoice_no').val('').attr('readonly','readonly').addClass('readonly');
    }else{
        $('#invoice_no').removeAttr('readonly').removeClass('readonly');
    }
});
EOF;
switch(Yii::app()->language) {
    case 'zh_cn': $lang = 'zh-CN'; break;
    case 'zh_tw': $lang = 'zh-TW'; break;
    default: $lang = Yii::app()->language;
}
if(!$model->readonly()){
    $disabled = 'false';
    /*
    $js.="
    $('#payment_company').select2({
        multiple: false,
        maximumInputLength: 10,
        language: '$lang',
        disabled: $disabled
    });
    function formatState(state) {
        var rtn = $('<span style=\"color:black\">'+state.text+'</span>');
        return rtn;
    }
    ";
    */
}
Yii::app()->clientScript->registerScript('changeTripDiv',$js,CClientScript::POS_READY);
?>