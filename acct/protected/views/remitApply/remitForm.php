<?php
$employeeList = ExpenseFun::getEmployeeListForID($model->employee_id);
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
    <?php echo $form->labelEx($model,'department',array('class'=>"col-sm-2 control-label",'for'=>'department')); ?>
    <div class="col-sm-2">
        <?php
        echo TbHtml::textField("department",$employeeList["department"],array('readonly'=>true));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo Tbhtml::label(Yii::t("give","payment company"),'payment_company',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php
        $payment_company = in_array($model->status_type,array(0,3))?ExpenseFun::getCompanyIdToEmployeeID($model->employee_id):$model->tableDetail["payment_company"];
        $model->tableDetail["payment_company"] = $payment_company;
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

<!--额外资料-->
<div class="form-group">
    <?php echo TbHtml::label(Yii::t('give',"outside"),'outside',array('class'=>"col-sm-2 control-label",'required'=>true)); ?>
    <div class="col-sm-2">
        <?php
        echo $form->dropDownList($model, 'tableDetail[outside]',ExpenseFun::getOutsideList(),
            array('readonly'=>$model->readonly(),"id"=>"outside"));
        ?>
    </div>
</div>
<?php if (!in_array($model->status_type,array(0,3))): ?>
    <div class="form-group">
        <?php echo TbHtml::label(Yii::t('give',"payee code"),'payee_code',array('class'=>"col-sm-2 control-label",'required'=>$model->tableDetail["purchase_type"]!="A0")); ?>
        <div class="col-sm-3">
            <div class="btn-group" style="width:100%" id="payeeCodeGroup">
                <?php
                echo $form->textField($model, 'tableDetail[payee_code]',
                    array('readonly'=>!$model->finance_bool,"id"=>"payee_code",'autocomplete'=>'off'));
                ?>
                <ul class="dropdown-menu" style="min-width: 100%;overflow: hidden;">
                </ul>
            </div>
        </div>
        <?php echo TbHtml::label(Yii::t('give',"prepayment"),'prepayment',array('class'=>"col-sm-2 control-label",'required'=>true)); ?>
        <div class="col-sm-3">
            <?php
            echo $form->inlineRadioButtonList($model, 'tableDetail[prepayment]',ExpenseFun::getUrgentList(),
                array('readonly'=>!$model->finance_bool,"id"=>"prepayment",'labelOptions'=>array('readonly'=>!$model->finance_bool)));
            ?>
        </div>
    </div>
<?php endif ?>
<div class="form-group">
    <?php echo TbHtml::label(Yii::t('give',"payee"),'payee',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <div class="btn-group" style="width:100%" id="payeeGroup">
            <?php
            echo $form->textField($model, 'tableDetail[payee]',
                array('readonly'=>$model->readonly(),"id"=>"payee",'autocomplete'=>'off'));
            ?>
            <ul class="dropdown-menu" style="min-width: 100%;overflow: hidden;">
            </ul>
        </div>
    </div>
    <?php echo TbHtml::label(Yii::t('give',"taxpayer no"),'taxpayer_no',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php
        echo $form->textField($model, 'tableDetail[taxpayer_no]',
            array('readonly'=>$model->readonly(),"id"=>"taxpayer_no",'autocomplete'=>'off'));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label(Yii::t('give',"bank name"),'bank_name',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php
        echo $form->textField($model, 'tableDetail[bank_name]',
            array('readonly'=>$model->readonly(),"id"=>"bank_name",'autocomplete'=>'off'));
        ?>
    </div>
    <?php echo TbHtml::label(Yii::t('give',"bank no"),'bank_no',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php
        echo $form->textField($model, 'tableDetail[bank_no]',
            array('readonly'=>$model->readonly(),"id"=>"bank_no",'autocomplete'=>'off'));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label(Yii::t('give',"urgent"),'urgent',array('class'=>"col-sm-2 control-label",'required'=>true)); ?>
    <div class="col-sm-3">
        <?php
        echo $form->inlineRadioButtonList($model, 'tableDetail[urgent]',ExpenseFun::getUrgentList(),
            array('readonly'=>$model->readonly(),"id"=>"urgent",'labelOptions'=>array('readonly'=>$model->readonly())));
        ?>
    </div>
    <?php echo TbHtml::label(Yii::t('give',"end pay date"),'end_pay_date',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php
        echo $form->textField($model, 'tableDetail[end_pay_date]',
            array('readonly'=>$model->readonly(),"id"=>"end_pay_date",
                'prepend'=>'<span class="fa fa-calendar"></span> ','autocomplete'=>'off'));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label(Yii::t('give',"invoice bool"),'invoice_bool',array('class'=>"col-sm-2 control-label",'required'=>true)); ?>
    <div class="col-sm-3">
        <?php
        echo $form->inlineRadioButtonList($model, 'tableDetail[invoice_bool]',ExpenseFun::getInvoiceBoolList(),
            array('readonly'=>$model->readonly(),"id"=>"invoice_bool",'labelOptions'=>array('readonly'=>$model->readonly())));
        ?>
    </div>
    <?php echo TbHtml::label(Yii::t('give',"invoice no"),'invoice_no',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php
        echo $form->textField($model, 'tableDetail[invoice_no]',
            array('readonly'=>(empty($model->tableDetail['invoice_bool'])||$model->readonly()),"id"=>"invoice_no",'autocomplete'=>'off'));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label(Yii::t('give',"purchase type"),'purchase_type',array('class'=>"col-sm-2 control-label",'required'=>true)); ?>
    <div class="col-sm-3">
        <?php
        echo $form->dropDownList($model, 'tableDetail[purchase_type]',ExpenseFun::getRemitTypeOne(),
            array('readonly'=>$model->readonly(),"id"=>"purchase_type","empty"=>""));
        ?>
    </div>
    <?php echo TbHtml::label(Yii::t('give',"purchase code"),'purchase_code',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php
        echo $form->textField($model, 'tableDetail[purchase_code]',
            array('readonly'=>(!in_array($model->tableDetail['purchase_type'],array("A0","A4","A5","A7"))||empty($model->tableDetail['purchase_type'])||$model->readonly()),"id"=>"purchase_code",'autocomplete'=>'off'));
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

<legend><?php echo Yii::t("give","Remit Detail");?></legend>
<div class="box">
    <div class="box-body table-responsive">
        <?php
        $this->widget('ext.layout.TableView2Widget', array(
            'model'=>$model,
            'tableClass'=>' table-fixed table-condensed table-bordered',
            'attribute'=>'infoDetail',
            'viewhdr'=>'//remitApply/_formhdr',
            'viewdtl'=>'//remitApply/_formdtl',
            'viewFoot'=>'//remitApply/_formFoot',
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
$('#purchase_type').on('change',function() {
    if($(this).attr('readonly')=='readonly'){
        return false;
    }
    var purchase_type = $(this).val();
    if(["A0"].indexOf(purchase_type)>-1){
        $('#purchase_code').removeAttr('readonly').removeClass('readonly');
    }else{
        $('#purchase_code').val('').attr('readonly','readonly').addClass('readonly');
    }
    $('.amtType>option').hide();
    $('.amtType>option[value=""]').show();
    $('.amtType').find('option[value*="'+purchase_type+'"]').show();
    $('.amtType').each(function(){
        var val = $(this).data('val');
        if($(this).find('option[value="'+val+'"]').attr('style')==''){
            $(this).val(val);
        }else{
            $(this).val('');
        }
    });
});
$('#purchase_type').trigger('change');

$('#tblDetail').on('change','.amtType',function(){
    $(this).data('val',$(this).val());
});

$('input[name="RemitApplyForm[tableDetail][invoice_bool]"]').on('click',function() {
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