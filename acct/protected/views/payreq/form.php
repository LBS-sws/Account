<?php
$this->pageTitle=Yii::app()->name . ' - Payment Request Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'payreq-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('trans','Payment Request Form'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('payreq/index'))); 
		?>
<?php if ($model->scenario!='new' && !$model->isReadOnly()): ?>
		<?php 
			echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
				'submit'=>Yii::app()->createUrl('payreq/new')));
		?>
<?php endif ?>
<?php if ($model->scenario!='new' && $model->allowRequestCheck()): ?>
		<?php echo TbHtml::button('<span class="fa fa-clone"></span> '.Yii::t('misc','Copy'), array(
				'submit'=>Yii::app()->createUrl('payreq/new', array('index'=>$model->id)))
			); 
		?>
<?php endif ?>
<?php if (!$model->isReadOnly() && $model->wfstatus!='PC'): ?>
			<?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('misc','Save Draft'), array(
				'submit'=>Yii::app()->createUrl('payreq/save'),)); 
			?>
<?php endif ?>
<?php if (!$model->isReadOnly() && $model->wfstatus!='PC' && $model->allowRequestCheck() && !$model->allowSubmit()): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('trans','Request Check'), array(
				'submit'=>Yii::app()->createUrl('payreq/request'),)); 
			?>
<?php endif ?>
<?php if (!$model->isReadOnly() && !$model->allowRequestCheck() && $model->allowSubmit()): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('trans','Check and Submit'), array(
				'submit'=>Yii::app()->createUrl('payreq/check'))); 
			?>
<?php endif ?>
<?php if (!$model->isReadOnly() && $model->allowRequestCheck() && $model->allowSubmit()): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Submit'), array(
				'submit'=>Yii::app()->createUrl('payreq/submit'))); 
			?>
<?php endif ?>
<?php if ($model->scenario=='edit' && empty($model->wfstatus)): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>

<?php if ($model->scenario=='edit' && strpos('~PA~PC~','~'.$model->wfstatus.'~')!==false): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Cancel'), array(
			'name'=>'btnCancel','id'=>'btnCancel','data-toggle'=>'modal','data-target'=>'#canceldialog',)
		);
	?>
<?php endif ?>
	</div>

	<div class="btn-group pull-right" role="group">
	<?php 
		$counter = ($model->no_of_attm['payreq'] > 0) ? ' '.TbHtml::badge($model->no_of_attm['payreq'], array('class' => 'bg-blue')) : '';
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('misc','Attachment'), array(
			'name'=>'btnFile','id'=>'btnFile','data-toggle'=>'modal','data-target'=>'#fileuploadpayreq',)
		);
	?>
	<?php 
		$counter = ($model->no_of_attm['tax'] > 0) ? ' '.TbHtml::badge($model->no_of_attm['tax'], array('class' => 'bg-blue')) : '';
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('trans','Tax Slip'), array(
			'name'=>'btnFileTS','id'=>'btnFileTS','data-toggle'=>'modal','data-target'=>'#fileuploadtax',)
		);
	?>
<?php if (!empty($model->wfstatus)): ?>
	<?php 
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('misc','Flow'), array(
			'name'=>'btnFlow','id'=>'btnFlow','data-toggle'=>'modal','data-target'=>'#flowinfodialog',)
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
			<?php echo $form->hiddenField($model, 'wfstatus'); ?>
			<?php echo $form->hiddenField($model, 'req_user'); ?>

<?php if (!empty($model->wfstatus)): ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'ref_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->textField($model, 'ref_no', 
						array('readonly'=>true,	)); 
					?>
				</div>
			</div>
<?php endif ?>

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
					<?php 
						$list = array_merge(array(''=>Yii::t('misc','-- None --')), General::getTransTypeList('OUT'));
						echo $form->dropDownList($model, 'trans_type_code', $list, array('disabled'=>($model->isReadOnly()))); 
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'acct_id',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php 
						$list0 = array(0=>Yii::t('misc','-- None --'));
						$list1 = General::getAccountList();
						$list = $list0 + $list1;
						echo $form->dropDownList($model, 'acct_id', $list,array('disabled'=>($model->isReadOnly()))); 
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'payee_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->dropDownList($model, 'payee_type', 
							array(
								'C'=>Yii::t('trans','Client'),
								'S'=>Yii::t('trans','Supplier'),
								'F'=>Yii::t('trans','Staff'),
								'A'=>Yii::t('trans','Company A/C'),
								'O'=>Yii::t('trans','Others')
							),
							array('disabled'=>($model->isReadOnly()))
					); ?>
				</div>
				<div class="col-sm-7">
					<?php 
						echo $form->textField($model, 'payee_name', 
							array('size'=>60,'maxlength'=>500,'readonly'=>($model->isReadOnly()||$model->payee_type!='O'),
							'append'=>TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('trans','Payee'),array('name'=>'btnPayee','id'=>'btnPayee','disabled'=>($model->isReadOnly()||$model->payee_type=='O'))),
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
							array('maxlength'=>500,'readonly'=>true,
							'append'=>TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('trans','Paid Item'),
										array('name'=>'btnPaidItem','id'=>'btnPaidItem',
											'disabled'=>($model->isReadOnly())
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
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'item_desc',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'item_desc', 
						array('rows'=>3,'cols'=>60,'maxlength'=>1000,'readonly'=>($model->isReadOnly()))
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

<?php if ($model->wfstatus=='ED' && !empty($model->reason)): ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'reason',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'reason', 
						array('rows'=>3,'cols'=>60,'maxlength'=>1000,'readonly'=>($model->isReadOnly()))
					); ?>
				</div>
			</div>
<?php endif ?>

<?php if (!empty($model->wfstatus)): ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'wfstatusdesc',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->textField($model, 'wfstatusdesc', 
						array('readonly'=>true,	)); 
					?>
				</div>
			</div>
<?php endif ?>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//site/canceldialog'); ?>
<?php $this->renderPartial('//site/lookup'); ?>
<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'PAYREQ',
													'header'=>Yii::t('dialog','File Attachment'),
													'ronly'=>($model->scenario=='view' || $model->isReadOnly()),
													)); 
?>
<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'TAX',
													'header'=>Yii::t('trans','Tax Slip'),
													'ronly'=>$model->isTaxSlipReadOnly(),
													)); 
?>
<?php 
	if (!empty($model->wfstatus))
		$this->renderPartial('//site/flowinfo',array('model'=>$model)); 
?>

<?php
Script::genFileUpload($model,$form->id,'PAYREQ');
Script::genFileUpload($model,$form->id,'TAX');

$defaclist = General::getJsDefaultAccountList();

$js = <<<EOF
var defacc = { $defaclist };
$('#PayReqForm_trans_type_code').on('change', function() {
	var choice = $(this).val();
	var target = $('#PayReqForm_acct_id').val();
//	if (target==0) {
		$('#PayReqForm_acct_id').val(defacc[choice]);
//	}
});
EOF;
Yii::app()->clientScript->registerScript('defaultAc',$js,CClientScript::POS_READY);

$js = Script::genLookupSearchEx();
Yii::app()->clientScript->registerScript('lookupSearch',$js,CClientScript::POS_READY);

$defButtonSts = $model->isReadOnly() ? 'true' : 'false';
switch ($model->payee_type) {
	case 'F': $defLookupType = 'staff'; break;
	case 'S': $defLookupType = 'supplier'; break;
	case 'A': $defLookupType = 'account'; break;
	default: $defLookupType = 'company'; 
}
$js = "
$('#lookuptype').val('$defLookupType');
$('#PayReqForm_payee_type').on('change', function() {
	var choice = $(this).val();
	$('#PayReqForm_payee_id').val(0);
	$('#PayReqForm_payee_name').val('');
	switch (choice) {
		case 'O':
			$('#PayReqForm_payee_name').prop('readonly',false);
			$('#btnPayee').prop('disabled',true);
			break;
		case 'C':
			$('#lookuptype').val('company');
			$('#PayReqForm_payee_name').prop('readonly',true);
			$('#btnPayee').prop('disabled',$defButtonSts);
			break;
		case 'S':
			$('#lookuptype').val('supplier');
			$('#PayReqForm_payee_name').prop('readonly',true);
			$('#btnPayee').prop('disabled',$defButtonSts);
			break;
		case 'F':
			$('#lookuptype').val('staff');
			$('#PayReqForm_payee_name').prop('readonly',true);
			$('#btnPayee').prop('disabled',$defButtonSts);
			break;
		case 'A':
			$('#lookuptype').val('account');
			$('#PayReqForm_payee_name').prop('readonly',true);
			$('#btnPayee').prop('disabled',$defButtonSts);
			break;
	}
});
	";
$js .= Script::genLookupButtonEx('btnPayee', '*', 'payee_id', 'payee_name');
Yii::app()->clientScript->registerScript('lookupPayee',$js,CClientScript::POS_READY);

$js = Script::genLookupButtonEx('btnPaidItem', 'accountitemout', 'item_code', 'pitem_desc', 
		array('acctcode'=>'PayReqForm_acct_code','acctcodedesc'=>'PayReqForm_acct_code_desc',)
	);
Yii::app()->clientScript->registerScript('lookupPaidItem',$js,CClientScript::POS_READY);

$js = Script::genLookupSelect();
Yii::app()->clientScript->registerScript('lookupSelect',$js,CClientScript::POS_READY);

$js = Script::genDeleteData(Yii::app()->createUrl('payreq/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$link = Yii::app()->createUrl('payreq/cancel');
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

//if (!$model->isReadOnly()) {
//	$js = Script::genDatePicker(array(
//			'PayReqForm_req_dt',
//		));
//	Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
//}

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


