<?php
$this->pageTitle=Yii::app()->name . ' - InvoiceEmail Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'invoiceEmail-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Invoice Email'); ?></strong>
	</h1>
<!--
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Layout</a></li>
		<li class="active">Top Navigation</li>
	</ol>
-->
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if ($model->scenario!='new' && !$model->isReadOnly()) {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('invoiceEmail/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('invoiceEmail/index'))); 
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('invoiceEmail/save'))); 
			?>
<?php endif ?>
<?php if ($model->scenario=='edit' && !$model->isReadOnly()): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>

            <div class="form-group">
                <?php echo $form->labelEx($model,'start_dt',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <div class="input-group date">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <?php echo $form->textField($model, 'start_dt',
                            array('class'=>'form-control pull-right','autocomplete'=>'off',
                                'readonly'=>($model->scenario=='view'),
                            ));
                        ?>
                    </div>
                </div>
            </div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'email_text',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'email_text',
						array('rows'=>3,'cols'=>60,'maxlength'=>1000,'readonly'=>($model->isReadOnly()))
					); ?>
				</div>
                <div class="col-sm-10 col-sm-offset-2">
                    <p class="form-control-static text-danger">多个邮箱请用<kbd>;</kbd>分割。例如：LBS00001@lbsgroup.com.cn;LBS00002@lbsgroup.com.cn;LBS00003@lbsgroup.com.cn;</p>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'remarks',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'remarks',
						array('rows'=>3,'cols'=>60,'maxlength'=>1000,'readonly'=>($model->isReadOnly()))
					); ?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
$js = Script::genDeleteData(Yii::app()->createUrl('invoiceEmail/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

if ($model->scenario!='view') {
	$js = Script::genDatePicker(array(
			'InvoiceEmailForm_start_dt',
		));
	Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


