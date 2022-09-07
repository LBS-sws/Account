<?php if ($model->status==3): ?>
<div class="form-group has-error">
    <?php echo $form->labelEx($model,'reject_remark',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-5">
        <?php echo $form->textArea($model, 'reject_remark',
            array('readonly'=>(true),'rows'=>3)
        ); ?>
    </div>
</div>
    <legend>&nbsp;</legend>
<?php endif ?>
<?php if ($model->getScenario()!="new"): ?>
<div class="form-group">
    <?php echo $form->labelEx($model,'consult_code',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php echo $form->textField($model, 'consult_code',
            array('readonly'=>(true))
        ); ?>
    </div>
</div>
<?php endif ?>
<div class="form-group">
    <?php echo $form->labelEx($model,'apply_date',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php echo $form->textField($model, 'apply_date',
            array('readonly'=>($model->isReady()),'prepend'=>"<span class='fa fa-calendar'></span>","id"=>"apply_date",'autocomplete'=>'off')
        ); ?>
    </div>
</div>
<!-- 刪除客戶識別號
<div class="form-group">
    <?php echo $form->labelEx($model,'customer_code',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-5">
        <?php echo $form->textField($model, 'customer_code',
            array('readonly'=>(true))
        ); ?>
    </div>
</div>
-->
<div class="form-group">
    <?php echo $form->labelEx($model,'apply_city',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php echo $form->dropDownList($model, 'apply_city',ConsultApplyList::getCityList($model->apply_city),
            array('readonly'=>($model->isReady()||!Yii::app()->user->validFunction('CN14')),'id'=>'apply_city')
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'audit_city',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php echo $form->dropDownList($model, 'audit_city',ConsultApplyList::getCityList($model->audit_city),
            array('readonly'=>($model->isReady()))
        ); ?>
    </div>
</div>
<div class="form-group">
    <?php echo $form->labelEx($model,'consult_money',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-3">
        <?php echo $form->numberField($model, 'consult_money',
            array('readonly'=>($model->isReady()),'id'=>'consult_money','autocomplete'=>'off')
        ); ?>
    </div>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model,'remark',array('class'=>"col-lg-2 control-label")); ?>
    <div class="col-lg-5">
        <?php echo $form->textArea($model, 'remark',
            array('readonly'=>($model->isReady()),'rows'=>3,'autocomplete'=>'off')
        ); ?>
    </div>
</div>