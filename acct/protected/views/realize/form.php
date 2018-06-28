<?php
$this->pageTitle=Yii::app()->name . ' - Reimbursement Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'realize-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('trans','Reimbursement Form'); ?></strong>
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
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('realize/index'))); 
		?>
		<?php 
			if (!$model->isFrozen()) {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('trans','Create Transaction'), array(
					'submit'=>Yii::app()->createUrl('realize/trans'))); 
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Submit'), array(
				'submit'=>Yii::app()->createUrl('realize/submit'))); 
		?>
		<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Cancel'), array(
				'name'=>'btnCancel','id'=>'btnCancel','data-toggle'=>'modal','data-target'=>'#canceldialog',)
			);
		?>
	</div>
	<div class="btn-group pull-right" role="group">
	<?php 
		$counter = ($model->no_of_attm['payreal'] > 0) ? ' <span id="docpayreal" class="label label-info">'.$model->no_of_attm['payreal'].'</span>' : ' <span id="docpayreal"></span>';
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('misc','Attachment').$counter, array(
			'name'=>'btnFileRE','id'=>'btnFileRE','data-toggle'=>'modal','data-target'=>'#fileuploadpayreal',)
		);
	?>
	<?php 
		$counter = ($model->no_of_attm['payreq'] > 0) ? ' <span id="docpayreq" class="label label-info">'.$model->no_of_attm['payreq'].'</span>' : ' <span id="docpayreq"></span>';
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('trans','Req. Attachment').$counter, array(
			'name'=>'btnFile','id'=>'btnFile','data-toggle'=>'modal','data-target'=>'#fileuploadpayreq',)
		);
	?>
	<?php 
		$counter = ($model->no_of_attm['tax'] > 0) ? ' <span id="doctax" class="label label-info">'.$model->no_of_attm['tax'].'</span>' : ' <span id="doctax"></span>';
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('trans','Tax Slip').$counter, array(
			'name'=>'btnFileTS','id'=>'btnFileTS','data-toggle'=>'modal','data-target'=>'#fileuploadtax',)
		);
	?>
	</div>
	</div></div>

<?php
	$currcode = City::getCurrency($model->city);
	$sign = Currency::getSign($currcode); 
?>
	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'status'); ?>
			<?php echo $form->hiddenField($model, 'req_user'); ?>
			<?php echo $form->hiddenField($model, 'city'); ?>
			<?php echo $form->hiddenField($model, 'trans_id'); ?>
			<?php echo $form->hiddenField($model, 'trans_id_c'); ?>
			<?php echo $form->hiddenField($model, 'int_fee'); ?>

	<?php if ($model->wfstatus=='QR') : ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'reason',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'reason', 
						array('rows'=>2,'cols'=>60,'maxlength'=>1000,'readonly'=>true)
					); ?>
				</div>
			</div>
	<?php endif ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'ref_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->textField($model, 'ref_no', 
						array('readonly'=>true,	)); 
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'user_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-4">
					<?php echo $form->textField($model, 'user_name', 
						array('readonly'=>true,	)); 
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'req_dt',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<?php echo $form->textField($model, 'req_dt', 
							array('class'=>'form-control pull-right',
								'readonly'=>true,
							)); 
						?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'trans_type_code',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->hiddenField($model, 'trans_type_code'); ?>
					<?php 
						if ($model->wfstatus=='QR') {
							$list = array_merge(array(''=>Yii::t('misc','-- None --')), General::getTransTypeList('OUT'));
							echo $form->dropDownList($model, 'trans_type_code', $list); 
						} else {
							$list = General::getTransTypeList('OUT');
							echo TbHtml::textField('trans_type_desc', $list[$model->trans_type_code], array('readonly'=>true,));
						}
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'acct_id',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php 
						if ($model->wfstatus=='QR') {
							$list0 = array(0=>Yii::t('misc','-- None --'));
							$list1 = General::getAccountList();
							$list = $list0 + $list1;
							echo $form->dropDownList($model, 'acct_id', $list); 
						} else {
							echo $form->hiddenField($model, 'acct_id');
							$list = General::getAccountList($model->city);
							$desc = isset($list[$model->acct_id]) ? $list[$model->acct_id] : '';
							echo TbHtml::textField('acct_name', $desc, array('readonly'=>true,)); 
						}
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'payee_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->hiddenField($model, 'payee_type'); ?>
					<?php 
						$list = array('C'=>Yii::t('trans','Client'),
									'S'=>Yii::t('trans','Supplier'),
									'F'=>Yii::t('trans','Staff'),
									'A'=>Yii::t('trans','Company A/C'),
									'O'=>Yii::t('trans','Others')
								);
						if ($model->wfstatus=='QR') {
							echo $form->dropDownList($model, 'payee_type', $list);
						} else {
							echo TbHtml::textField('payee_type_name', $list[$model->payee_type], array('readonly'=>true,)); 
						}
					?>
				</div>
				<div class="col-sm-7">
					<?php 
						echo $form->textField($model, 'payee_name', 
							array('size'=>60,'maxlength'=>500,'readonly'=>($model->wfstatus!='QR'),
							'append'=>TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('trans','Payee'),array('name'=>'btnPayee','id'=>'btnPayee','disabled'=>($model->wfstatus!='QR'))),
						)); 
						echo $form->hiddenField($model, 'payee_id');
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'pitem_desc',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php 
						echo $form->hiddenField($model, 'item_code');
						echo $form->textField($model, 'pitem_desc', 
							array('maxlength'=>500,'readonly'=>($model->wfstatus!='QR'),
							'append'=>TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('trans','Paid Item'),
										array('name'=>'btnPaidItem','id'=>'btnPaidItem',
											'disabled'=>($model->wfstatus!='QR')
										)
								)
							)
						); 
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'acct_code',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-4">
					<?php 
						echo $form->hiddenField($model, 'acct_code');
						echo $form->textField($model, 'acct_code_desc', 
							array('maxlength'=>500,'readonly'=>true,)
						); 
					?>
				</div>
				<?php echo $form->labelEx($model,'int_fee',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-1">
					<?php 
						if ($model->wfstatus=='QR') {
							$list = array('N'=>Yii::t('misc','No'),'Y'=>Yii::t('misc','Yes'));
							echo $form->dropDownList($model, 'int_fee', $list); 
						} else {
							echo $form->hiddenField($model, 'int_fee');
							$list = array(''=>Yii::t('misc','No'),'N'=>Yii::t('misc','No'),'Y'=>Yii::t('misc','Yes'));
							echo TbHtml::textField('int_fee', $list[$model->int_fee], array('readonly'=>true,)); 
						}
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'item_desc',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'item_desc', 
						array('rows'=>3,'cols'=>60,'maxlength'=>1000,'readonly'=>($model->wfstatus!='QR'))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'amount',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php
						echo $form->numberField($model, 'amount', 
							array('size'=>10,'min'=>0,
							'readonly'=>($model->wfstatus!='QR'),
							'prepend'=>'<span class="fa '.$sign.'"></span>')
						); 
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'trans_dt',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<?php echo $form->textField($model, 'trans_dt', 
							array('class'=>'form-control pull-right',
								'readonly'=>($model->isFrozen() || $model->isReadOnly()),
							)); 
						?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'cheque_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-4">
					<?php 
						echo $form->textField($model, 'cheque_no', array('size'=>60,'maxlength'=>500,'readonly'=>($model->isFrozen() || $model->isReadOnly()))); 
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'invoice_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-4">
					<?php 
						echo $form->textField($model, 'invoice_no', array('size'=>60,'maxlength'=>500,'readonly'=>($model->isFrozen() || $model->isReadOnly()))); 
					?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/canceldialog'); ?>
<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'PAYREAL',
													'header'=>Yii::t('dialog','File Attachment'),
													'ronly'=>$model->isReadOnly(),
													)); 
?>
<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'PAYREQ',
													'header'=>Yii::t('trans','Req. Attachment'),
													'ronly'=>true,
													)); 
?>
<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'TAX',
													'header'=>Yii::t('trans','Tax Slip'),
													'ronly'=>$model->isReadOnly(),
													)); 
?>

<?php
Script::genFileUpload($model,$form->id,'PAYREAL');
Script::genFileUpload($model,$form->id,'PAYREQ');
Script::genFileUpload($model,$form->id,'TAX');

$link = Yii::app()->createUrl('realize/cancel');
$js = "
$('#btnCancelData').on('click',function() {
	$('#canceldialog').modal('hide');
	canceldata();
});

function canceldata() {
	var elm=$('#btnCancel');
	jQuery.yii.submitForm(elm,'$link',{});
}
	";
Yii::app()->clientScript->registerScript('CancelRecord',$js,CClientScript::POS_READY);

if (!$model->isReadOnly()) {
	$js = Script::genDatePicker(array(
			'RealizeForm_trans_dt',
		));
	Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


