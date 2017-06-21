<?php
$this->pageTitle=Yii::app()->name . ' - Transaction In Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'transin-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('trans','Transaction In Form'); ?></strong>
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
					'submit'=>Yii::app()->createUrl('transin/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('transin/index'))); 
		?>
<?php if (!$model->isReadOnly()): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('transin/save'))); 
			?>
<?php endif ?>
<?php if ($model->scenario=='edit' && !$model->isReadOnly()): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Void'), array(
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
			<?php echo $form->hiddenField($model, 'status'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'trans_dt',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<?php echo $form->textField($model, 'trans_dt', 
							array('class'=>'form-control pull-right',
								'readonly'=>($model->isReadOnly()),
							)); 
						?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'trans_type_code',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->dropDownList($model, 'trans_type_code', General::getTransTypeList('IN'),array('disabled'=>($model->isReadOnly()))); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'acct_id',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->dropDownList($model, 'acct_id', General::getAccountList(),array('disabled'=>($model->isReadOnly()))); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'payer_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->dropDownList($model, 'payer_type', 
							array('C'=>Yii::t('trans','Client'),'S'=>Yii::t('trans','Supplier'),'F'=>Yii::t('trans','Staff'),'O'=>Yii::t('trans','Others')),
							array('disabled'=>($model->isReadOnly()))
					); ?>
				</div>
				<div class="col-sm-7">
					<?php 
						echo $form->textField($model, 'payer_name', 
							array('size'=>60,'maxlength'=>500,'readonly'=>($model->isReadOnly()||$model->payer_type!='O'),
							'append'=>TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('trans','Payer'),array('name'=>'btnPayer','id'=>'btnPayer','disabled'=>($model->isReadOnly()||$model->payer_type=='O'))),
						)); 
						echo $form->hiddenField($model, 'payer_id');
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'cheque_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textField($model, 'cheque_no', 
						array('size'=>50,'maxlength'=>255,'readonly'=>($model->isReadOnly())
					)); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'invoice_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textField($model, 'invoice_no', 
						array('size'=>50,'maxlength'=>255,'readonly'=>($model->isReadOnly()))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'handle_staff',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->dropDownList($model, 'handle_staff', General::getStaffList(),array('disabled'=>($model->isReadOnly()))); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'trans_desc',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'trans_desc', 
						array('rows'=>3,'cols'=>60,'maxlength'=>1000,'readonly'=>($model->scenario=='view'))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'amount',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php
						echo $form->numberField($model, 'amount', 
							array('size'=>10,'min'=>0,
							'readonly'=>($model->isReadOnly()),
							'prepend'=>'<span class="fa fa-cny"></span>')
						); 
					?>
				</div>
			</div>

<?php if ($model->status=='V'): ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'status_desc',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->textField($model, 'status_desc', 
						array('size'=>50,'maxlength'=>255,'readonly'=>true
					)); ?>
				</div>
			</div>
<?php endif ?>

		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//site/lookup'); ?>
<?php $this->renderPartial('//site/fileupload',array('model'=>$model,'form'=>$form)); ?>

<?php
Script::genFileUpload(get_class($model),$form->id, 'trans');

$js = Script::genLookupSearchEx();
Yii::app()->clientScript->registerScript('lookupSearch',$js,CClientScript::POS_READY);

$defButtonSts = $model->isReadOnly() ? 'true' : 'false';
switch ($model->payer_type) {
	case 'F': $defLookupType = 'staff'; break;
	case 'S': $defLookupType = 'supplier'; break;
	default: $defLookupType = 'company'; 
}
$js = "
$('#lookuptype').val('$defLookupType');
$('#TransInForm_payer_type').on('change', function() {
	var choice = $(this).val();
	$('#TransInForm_payer_id').val('');
	$('#TransInForm_payer_name').val('');
	switch (choice) {
		case 'O':
			$('#TransInForm_payer_name').prop('readonly',false);
			$('#btnPayer').prop('disabled',true);
			break;
		case 'C':
			$('#lookuptype').val('company');
			$('#TransInForm_payer_name').prop('readonly',true);
			$('#btnPayer').prop('disabled',$defButtonSts);
			break;
		case 'S':
			$('#lookuptype').val('supplier');
			$('#TransInForm_payer_name').prop('readonly',true);
			$('#btnPayer').prop('disabled',$defButtonSts);
			break;
		case 'F':
			$('#lookuptype').val('staff');
			$('#TransInForm_payer_name').prop('readonly',true);
			$('#btnPayer').prop('disabled',$defButtonSts);
			break;
	}
});
	";
$js .= Script::genLookupButtonEx('btnPayer', '*', 'payer_id', 'payer_name');
Yii::app()->clientScript->registerScript('lookupPayer',$js,CClientScript::POS_READY);

$js = Script::genLookupSelect();
Yii::app()->clientScript->registerScript('lookupSelect',$js,CClientScript::POS_READY);

$js = Script::genDeleteData(Yii::app()->createUrl('transin/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

if (!$model->isReadOnly()) {
	$js = Script::genDatePicker(array(
			'TransInForm_trans_dt',
		));
	Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


