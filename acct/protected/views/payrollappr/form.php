<?php
$this->pageTitle=Yii::app()->name . ' - Payroll File Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'payroll-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('trans','Payroll File Form'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('payrollappr/index'))); 
		?>
		<?php echo TbHtml::button('<span class="fa fa-check"></span> '.Yii::t('trans','Accept'), array(
			'id'=>'btnAccept')); 
		?>
		<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('trans','Reject'), array(
			'id'=>'btnDeny')); 
		?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'lcd'); ?>
			<?php echo $form->hiddenField($model, 'lud'); ?>
			<?php echo $form->hiddenField($model, 'city'); ?>
			<?php echo $form->hiddenField($model, 'wfstatus'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'year_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->textField($model, 'year_no', 
						array('size'=>10,'readonly'=>true)
					); ?>
				</div>
				<?php echo $form->labelEx($model,'month_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->textField($model, 'month_no', 
						array('size'=>10,'readonly'=>true)
					); ?>
				</div>
			</div>
	
			<div class="form-group">
				<?php echo $form->labelEx($model,'city_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->textField($model, 'city_name', 
						array('size'=>10,'readonly'=>true)
					); ?>
				</div>
				<?php echo $form->labelEx($model,'wfstatusdesc',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php 
						echo $form->textField($model, 'wfstatusdesc', array('readonly'=>true)); 
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'remarks',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'remarks', 
					array('rows'=>2,'cols'=>60,'readonly'=>true)
					); ?>
				</div>
			</div>

<?php if (!empty($model->reason)) : ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'reason',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'reason', 
					array('rows'=>2,'cols'=>60,'readonly'=>true)
					); ?>
				</div>
			</div>
<?php endif ?>

			<legend><?php echo Yii::t('trans','Files'); ?></legend>
	
			<div class="form-group">
			<div class="col-sm-2">
	<?php 
		$counter = ($model->no_of_attm['payfile1'] > 0) ? ' <span id="docpayfile1" class="label label-info">'.$model->no_of_attm['payfile1'].'</span>' : ' <span id="docpayfile1"></span>';
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('trans','Payroll File').$counter, array(
			'name'=>'btnPayfile1','id'=>'btnPayfile1','data-toggle'=>'modal','data-target'=>'#fileuploadpayfile1',)
		);
	?>
			</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'amt_sales',array('class'=>"col-sm-1 control-label")); ?>
				<div class="col-sm-2">
					<?php
						echo $form->numberField($model, 'amt_sales', 
							array('size'=>10,'min'=>0,
							'readonly'=>true,
							'placeholder'=>Yii::t('trans','Please fill in amount'))
						); 
					?>
				</div>

				<?php echo $form->labelEx($model,'amt_tech',array('class'=>"col-sm-1 control-label")); ?>
				<div class="col-sm-2">
					<?php
						echo $form->numberField($model, 'amt_tech', 
							array('size'=>10,'min'=>0,
							'readonly'=>true,
							'placeholder'=>Yii::t('trans','Please fill in amount'))
						); 
					?>
				</div>

				<?php echo $form->labelEx($model,'amt_office',array('class'=>"col-sm-1 control-label")); ?>
				<div class="col-sm-2">
					<?php
						echo $form->numberField($model, 'amt_office', 
							array('size'=>10,'min'=>0,
							'readonly'=>true,
							'placeholder'=>Yii::t('trans','Please fill in amount'))
						); 
					?>
				</div>

				<?php echo $form->labelEx($model,'amt_total',array('class'=>"col-sm-1 control-label")); ?>
				<div class="col-sm-2">
					<?php
						echo $form->numberField($model, 'amt_total', 
							array('size'=>10,'min'=>0,
							'readonly'=>true,
							'placeholder'=>Yii::t('trans','Please fill in amount'))
						); 
					?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'PAYFILE1',
													'header'=>Yii::t('trans','Payroll File'),
													'ronly'=>true,
													)); 
?>

<?php $this->renderPartial('//payrollappr/accept',array('model'=>$model,'form'=>$form)); ?>
<?php $this->renderPartial('//payrollappr/reject',array('model'=>$model,'form'=>$form)); ?>
<?php $this->renderPartial('//payrollappr/acceptdialog',array('model'=>$model,'form'=>$form)); ?>

<?php
Script::genFileUpload($model,$form->id,'PAYFILE1');

$path = Yii::app()->createUrl('payrollappr/accept');
$isPC = $model->wfstatus=='P1' || $model->wfstatus=='P2' ? 'true' : 'false';
$js=<<<EOF
$('#btnAccept').on('click',function(){
	var isPC=$isPC;
	if (isPC) {
		$('#acceptdialog').modal('show');
	} else {
		$('#confirmdialog').modal('show');
		$('#btnAcceptData').on('click',function() {
			$('#confirmdialog').modal('hide');
			jQuery.yii.submitForm(this,'$path',{});
		});
	}
});
$('#btnDeny').on('click',function(){
	$('#rejectdialog').modal('show');
});
EOF;
Yii::app()->clientScript->registerScript('reasonPopup',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


