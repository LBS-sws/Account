<?php
$this->pageTitle=Yii::app()->name . ' - SalesGroupBelow Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'salesGroupBelow-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Sales Group Below'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('salesGroupBelow/index')));
		?>
	</div>
            <?php if (SellComputeForm::isVivienne()): ?>
                <div class="btn-group pull-right" role="group">
                    <?php echo TbHtml::button('刷新且发送至北森', array(
                        'submit'=>Yii::app()->createUrl('salesGroupBelow/sendBsByOne',array("index"=>$model->id))));
                    ?>
                </div>
            <?php endif ?>
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
                    echo TbHtml::textField("month_no","{$model->year_no}年{$model->month_no}月",
                        array('class'=>'form-control','readonly'=>true,)
                    );
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
                <div class="col-lg-offset-1 col-lg-10">
                    <?php
                    echo $model->new_json_html();
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


