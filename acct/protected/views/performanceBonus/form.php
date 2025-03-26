<?php
$this->pageTitle=Yii::app()->name . ' - PerformanceBonus Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'performanceBonus-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Quarterly performance bonus'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('performanceBonus/index')));
		?>

<?php if (!$model->isReadOnly()): ?>
		<?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('service','static now'), array(
				'submit'=>Yii::app()->createUrl('performanceBonus/save')));
		?>
<?php endif ?>
<?php if ($model->isReadOnly()): ?>
		<?php echo TbHtml::button('<span class="fa fa-mail-reply-all"></span> '.Yii::t('dialog','Cancel'), array(
				'submit'=>Yii::app()->createUrl('performanceBonus/back')));
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
            <?php echo $form->hiddenField($model, 'info_status_type'); ?>

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
                    echo TbHtml::textField("month_no","{$model->year_no}年{$model->month_no}月",
                        array('class'=>'form-control','readonly'=>true,)
                    );
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'quarter_no',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php
                    echo TbHtml::textField("quarter_no",$model->getQuarterStr($model->year_no,$model->quarter_no),
                        array('class'=>'form-control','readonly'=>true,)
                    );
                    ?>
                </div>
            </div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'new_amount',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
                    <?php echo $form->textField($model, 'new_amount',
                        array('class'=>'form-control','readonly'=>true,));
                    ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'bonus_amount',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
                    <?php echo $form->textField($model, 'bonus_amount',
                        array('class'=>'form-control','readonly'=>true,));
                    ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'status_type',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
                    <?php
                    echo TbHtml::textField("info_status_type",$model->getStatusStr($model->info_status_type),
                        array('class'=>'form-control','readonly'=>true,)
                    );
                    ?>
				</div>
			</div>
			<div class="form-group">
                <div class="col-lg-offset-1 col-lg-7">
                    <?php
                    echo $model->new_json_html();
                    ?>
                    <?php if ($model->year_no==2025&&$model->quarter_no==1): ?>
                        <p class="text-warning">2025年1月份的销售提成不参与计算，业绩强制为0</p>
                    <?php endif ?>
				</div>
                <div class="col-lg-offset-1 col-lg-3">
                    <?php
                    echo $model->bonus_json_html();
                    ?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


