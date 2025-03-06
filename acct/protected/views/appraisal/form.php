<?php
$this->pageTitle=Yii::app()->name . ' - Appraisal Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'appraisal-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Performance appraisal'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('appraisal/index')));
		?>

<?php if ($model->ready&&!$model->isReadOnly()): ?>
		<?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('service','static now'), array(
				'submit'=>Yii::app()->createUrl('appraisal/save')));
		?>
<?php endif ?>
<?php if ($model->ready&&$model->isReadOnly()): ?>
		<?php echo TbHtml::button('<span class="fa fa-mail-reply-all"></span> '.Yii::t('dialog','Cancel'), array(
				'submit'=>Yii::app()->createUrl('appraisal/back')));
		?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo $form->hiddenField($model, 'city'); ?>
            <?php echo $form->hiddenField($model, 'employee_id'); ?>
            <?php echo $form->hiddenField($model, 'status_type'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'employee_code',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
                    <?php echo $form->textField($model, 'employee_code',
                        array('class'=>'form-control','readonly'=>true,));
                    ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'employee_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
                    <?php echo $form->textField($model, 'employee_name',
                        array('class'=>'form-control','readonly'=>true,));
                    ?>
				</div>
			</div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'month_no',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php
                    echo TbHtml::textField("month_no",$model->getMonthStr($model->year_no,$model->month_no),
                        array('class'=>'form-control','readonly'=>true,)
                    );
                    ?>
                </div>
            </div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'status_type',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
                    <?php
                    echo TbHtml::textField("status_type",$model->getStatusStr($model->status_type),
                        array('class'=>'form-control','readonly'=>true,)
                    );
                    ?>
				</div>
			</div>
			<div class="form-group">
				<div class="col-lg-10 col-lg-offset-1">
                    <?php
                    echo $model->new_json_html();
                    ?>
				</div>
			</div>
            <div class="form-group">
                <?php echo Tbhtml::label('目标绩效奖金','',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <p class="form-control-static">2000</p>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'appraisal_amount',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php
                    echo TbHtml::textField("appraisal_money",sprintf("%.2f",$model->appraisal_amount*20),
                        array('class'=>'form-control','readonly'=>true,)
                    );
                    ?>
                </div>
            </div>
		</div>
	</div>
</section>

<?php

$js = "
$('#num_score').change(function(){
    var num_score = $(this).val();
    var rate = $(this).data('rate');
    var num = $(this).data('num');
    var max_rate = $(this).data('max_rate');
    var num_score_rate = num_score/num;
    num_score_rate = num_score_rate>max_rate?max_rate:num_score_rate;
    num_score_ok = 100*rate*num_score_rate;
    num_score_rate*=100;
    num_score_rate=parseInt(num_score_rate,10);
    num_score_rate+='%';
    $('#num_score_rate').text(num_score_rate);
    $('#num_score_ok').text(num_score_ok);
    changeTotalAmt();
});

function changeTotalAmt(){
    var appraisal_amount = 0;
    var appraisal_money = 0;
    $('.changeTr').each(function(){
        var money = $(this).find('td:last').eq(0).text();
        appraisal_amount+=parseFloat(money);
    });
    appraisal_money = appraisal_amount*20;
    $('#appraisal_amount').text(appraisal_amount);
    $('#appraisal_money').val(appraisal_money);
}
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


