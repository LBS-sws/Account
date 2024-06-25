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
    <?php echo TbHtml::label(Yii::t('give',"outside"),'outside',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-2">
        <?php
        echo $form->dropDownList($model, 'tableDetail[outside]',ExpenseFun::getOutsideList(),
            array('readonly'=>$model->readonly(),"id"=>"outside"));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo TbHtml::label(Yii::t('give',"payee"),'payee',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <div class="btn-group" style="width:100%" id="payeeGroup">
            <?php
            echo $form->textField($model, 'tableDetail[payee]',
                array('readonly'=>$model->readonly(),"id"=>"payee",'autocomplete'=>'off'));
            ?>
            <ul class="dropdown-menu" style="width: 100%;overflow: hidden;">
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
    <?php echo TbHtml::label(Yii::t('give',"urgent"),'urgent',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php
        echo $form->inlineRadioButtonList($model, 'tableDetail[urgent]',ExpenseFun::getUrgentList(),
            array('readonly'=>$model->readonly(),"id"=>"urgent"));
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
    <?php echo TbHtml::label(Yii::t('give',"invoice bool"),'invoice_bool',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php
        echo $form->inlineRadioButtonList($model, 'tableDetail[invoice_bool]',ExpenseFun::getInvoiceBoolList(),
            array('readonly'=>$model->readonly(),"id"=>"invoice_bool"));
        ?>
    </div>
    <?php echo TbHtml::label(Yii::t('give',"invoice no"),'invoice_no',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php
        echo $form->textField($model, 'tableDetail[invoice_no]',
            array('readonly'=>$model->readonly(),"id"=>"invoice_no",'autocomplete'=>'off'));
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