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
				'submit'=>Yii::app()->createUrl('payroll/index'))); 
		?>
<?php if (!$model->isReadOnly()): ?>
		<?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('misc','Save'), array(
			'submit'=>Yii::app()->createUrl('payroll/save'),)); 
		?>
<?php endif ?>
<?php if (!$model->isReadOnly() && empty($model->wfstatus) && Yii::app()->user->validRWFunction('XS05')) : ?>
		<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Submit'), array(
			'submit'=>Yii::app()->createUrl('payroll/submit'))); 
		?>
<?php endif ?>
<?php if (!$model->isReadOnly() && $model->wfstatus=='PS' && Yii::app()->user->validRWFunction('XS05')): ?>
		<?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('misc','Submit'), array(
			'submit'=>Yii::app()->createUrl('payroll/resubmit'))); 
		?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'lcd'); ?>
			<?php echo $form->hiddenField($model, 'lud'); ?>
			<?php echo $form->hiddenField($model, 'city'); ?>

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
					array('rows'=>2,'cols'=>60,'readonly'=>$model->isReadOnly())
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
							'readonly'=>($model->isReadOnly()),
							'placeholder'=>Yii::t('trans','Please fill in amount'))
						); 
					?>
				</div>

				<?php echo $form->labelEx($model,'amt_tech',array('class'=>"col-sm-1 control-label")); ?>
				<div class="col-sm-2">
					<?php
						echo $form->numberField($model, 'amt_tech', 
							array('size'=>10,'min'=>0,
							'readonly'=>($model->isReadOnly()),
							'placeholder'=>Yii::t('trans','Please fill in amount'))
						); 
					?>
				</div>

				<?php echo $form->labelEx($model,'amt_office',array('class'=>"col-sm-1 control-label")); ?>
				<div class="col-sm-2">
					<?php
						echo $form->numberField($model, 'amt_office', 
							array('size'=>10,'min'=>0,
							'readonly'=>($model->isReadOnly()),
							'placeholder'=>Yii::t('trans','Please fill in amount'))
						); 
					?>
				</div>

				<?php echo $form->labelEx($model,'amt_total',array('class'=>"col-sm-1 control-label")); ?>
				<div class="col-sm-2">
					<?php
						echo $form->numberField($model, 'amt_total', 
							array('size'=>10,'min'=>0,
							'readonly'=>($model->isReadOnly()),
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
													'ronly'=>$model->isReadOnly(),
													)); 
?>

<?php
Script::genFileUpload($model,$form->id,'PAYFILE1');

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);

$js = <<<EOF
function roundNumber(num, scale) {
  if(!("" + num).includes("e")) {
    return +(Math.round(num + "e+" + scale)  + "e-" + scale);
  } else {
    var arr = ("" + num).split("e");
    var sig = ""
    if(+arr[1] + scale > 0) {
      sig = "+";
    }
    return +(Math.round(+arr[0] + "e" + sig + (+arr[1] + scale)) + "e-" + scale);
  }
}		

$('#PayrollForm_amt_sales, #PayrollForm_amt_tech, #PayrollForm_amt_office').change(function() {
	$('#PayrollForm_amt_sales').val(parseFloat(+$('#PayrollForm_amt_sales').val() || 0 ).toFixed(2));
	$('#PayrollForm_amt_tech').val(parseFloat(+$('#PayrollForm_amt_tech').val() || 0 ).toFixed(2));
	$('#PayrollForm_amt_office').val(parseFloat(+$('#PayrollForm_amt_office').val() || 0 ).toFixed(2));
	var total = parseFloat(document.getElementById('PayrollForm_amt_sales').value)
			+ parseFloat(document.getElementById('PayrollForm_amt_tech').value)
			+ parseFloat(document.getElementById('PayrollForm_amt_office').value);
	$('#PayrollForm_amt_total').val(parseFloat(roundNumber(total,2)).toFixed(2));
});

$('#PayrollForm_amt_total').change(function() {
	$('#PayrollForm_amt_total').val(parseFloat(+$('#PayrollForm_amt_total').val() || 0 ).toFixed(2));
});
EOF;
Yii::app()->clientScript->registerScript('calculation',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


