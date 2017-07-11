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
		<strong><?php echo Yii::t('trans','Transaction(In) Form'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('transin/index'))); 
		?>
		<?php 
			if ($model->scenario!='new' && !$model->isReadOnly()) {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('transin/new')));
			}
		?>
<?php if ($model->scenario!='new'): ?>
		<?php echo TbHtml::button('<span class="fa fa-clone"></span> '.Yii::t('misc','Copy'), array(
				'submit'=>Yii::app()->createUrl('transin/new', array('index'=>$model->id)))
			); 
		?>
<?php endif ?>
<?php if (!$model->isReadOnly()): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('transin/save'))); 
			?>
<?php endif ?>
<?php if ($model->voidRight() && $model->status!='V'): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('trans','Void'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#rmkdialog',)
		);
	?>
<?php endif ?>
	</div>
	<div class="btn-group pull-right" role="group">
	<?php 
		$counter = ($model->no_of_attm['trans'] > 0) ? ' <span id="doctrans" class="label label-info">'.$model->no_of_attm['trans'].'</span>' : ' <span id="doctrans"></span>';
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('misc','Attachment').$counter, array(
			'name'=>'btnFile','id'=>'btnFile','data-toggle'=>'modal','data-target'=>'#fileuploadtrans',)
		);
	?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'status'); ?>

<?php if (!Yii::app()->user->isSingleCity()) : ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'city',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php 
						$list = General::getCityListWithNoDescendant(Yii::app()->user->city_allow());
						echo $form->dropDownList($model, 'city', $list,array('disabled'=>($model->isReadOnly()))); 
					?>
				</div>
			</div>
<?php else: ?>
			<?php echo $form->hiddenField($model, 'city'); ?>
<?php endif ?>
			
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
<?php if ($model->posted): ?>
				<div class="col-sm-3">
					<span class="text-red"><?php echo Yii::t('trans','This record is checked already'); ?></span>
				</div>
<?php endif ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'trans_type_code',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php 
						$allowadj = $model->adjustRight();
						$list = array_merge(array(''=>Yii::t('misc','-- None --')), General::getTransTypeList('IN',false,$allowadj));
						echo $form->dropDownList($model, 'trans_type_code', $list,array('disabled'=>($model->isReadOnly()))); 
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'acct_id',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php 
						$list0 = array(0=>Yii::t('misc','-- None --'));
						$list1 = General::getAccountList('','1');
						$list = $list0 + $list1;
						echo $form->dropDownList($model, 'acct_id', $list,array('disabled'=>($model->isReadOnly()))); 
					?>
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
				<div class="col-sm-4">
					<?php echo $form->textField($model, 'cheque_no', 
						array('size'=>50,'maxlength'=>255,'readonly'=>($model->isReadOnly())
					)); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'invoice_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-4">
					<?php echo $form->textField($model, 'invoice_no', 
						array('size'=>50,'maxlength'=>255,'readonly'=>($model->isReadOnly()))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'united_inv_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-4">
					<?php echo $form->textField($model, 'united_inv_no', 
						array('size'=>50,'maxlength'=>255,'readonly'=>($model->isReadOnly()))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'handle_staff_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php 
						echo $form->textField($model, 'handle_staff_name', 
							array('maxlength'=>500,'readonly'=>true,
							'append'=>TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('trans','Handling Staff'),array('name'=>'btnStaff','id'=>'btnStaff','disabled'=>($model->isReadOnly()))),
						)); 
						echo $form->hiddenField($model, 'handle_staff');
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'citem_desc',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php 
						echo $form->hiddenField($model, 'item_code');
						echo $form->textField($model, 'citem_desc', 
							array('maxlength'=>500,'readonly'=>true,
							'append'=>TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('trans','Charge Item'),
										array('name'=>'btnChargeItem','id'=>'btnChargeItem',
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

				<?php echo $form->labelEx($model,'int_fee',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-1">
					<?php 
						$list = array('N'=>Yii::t('misc','No'),'Y'=>Yii::t('misc','Yes'));
						echo $form->dropDownList($model, 'int_fee', $list,array('disabled'=>($model->isReadOnly()))); 
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'year_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php 
						echo $form->numberField($model, 'year_no', 
							array('size'=>4,'min'=>2007,'max'=>2099,
							'readonly'=>($model->isReadOnly()),
							'prepend'=>'<span>'.Yii::t('trans','Year').'</span>')
						); 
					?>
				</div>
				<div class="col-sm-2">
					<?php 
						echo $form->numberField($model, 'month_no', 
							array('size'=>2,'min'=>1,'max'=>12,
							'readonly'=>($model->isReadOnly()),
							'prepend'=>'<span>'.Yii::t('trans','Month').'</span>')
						); 
					?>
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

			<div class="form-group">
				<?php echo $form->labelEx($model,'trans_desc',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'trans_desc', 
						array('rows'=>3,'cols'=>60,'maxlength'=>1000,'readonly'=>($model->isReadOnly()))
					); ?>
				</div>
			</div>

<?php if (!empty($model->req_ref_no)): ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'req_ref_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->textField($model, 'req_ref_no', 
						array('size'=>50,'maxlength'=>255,'readonly'=>true
					)); ?>
				</div>
			</div>
<?php endif ?>

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

<?php if (!empty($model->reason)): ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'reason',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'reason', 
						array('rows'=>3,'cols'=>60,'maxlength'=>1000,'readonly'=>true)
					); ?>
				</div>
			</div>
<?php endif ?>

		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//site/lookup'); ?>
<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'TRANS',
													'header'=>Yii::t('dialog','File Attachment'),
													'ronly'=>($model->scenario=='view' || $model->isReadOnly()),
													)); 
?>
<?php $this->renderPartial('//transin/reason',array('model'=>$model,'form'=>$form)); ?>

<?php
Script::genFileUpload($model,$form->id,'TRANS');

$defaclist = General::getJsDefaultAccountList();

$js = <<<EOF
var defacc = { $defaclist };
$('#TransInForm_trans_type_code').on('change', function() {
	var choice = $(this).val();
	var target = $('#TransInForm_acct_id').val();
//	if (target==0) {
		$('#TransInForm_acct_id').val(defacc[choice]);
//	}
});
EOF;
Yii::app()->clientScript->registerScript('defaultAc',$js,CClientScript::POS_READY);

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

$('#btnPayer').on('click',function() {
	var code = $(\"input[id*='payer_id']\").attr(\"id\");
	var value = $(\"input[id*='payer_name']\").attr(\"id\");
	var title = $(\"label[for='\"+value+\"']\").text();
	var choice = $('#TransInForm_payer_type').val();
	switch (choice) {
		case 'C':
			$('#lookuptype').val('company');
			break;
		case 'S':
			$('#lookuptype').val('supplier');
			break;
		case 'F':
			$('#lookuptype').val('staff');
			break;
	}
	$('#lookupcodefield').val(code);
	$('#lookupvaluefield').val(value);
	$('#lookupotherfield').val('');
	$('#lookup-label').attr('style','display: none');
	$('#lookupdialog').find('.modal-title').text(title);
	$('#lookupdialog').modal('show');
});
	";
//$js .= Script::genLookupButtonEx('btnPayer', '*', 'payer_id', 'payer_name');
Yii::app()->clientScript->registerScript('lookupPayer',$js,CClientScript::POS_READY);

$js .= Script::genLookupButtonEx('btnStaff', 'staff', 'handle_staff', 'handle_staff_name');
Yii::app()->clientScript->registerScript('lookupStaff',$js,CClientScript::POS_READY);

$js = Script::genLookupButtonEx('btnChargeItem', 'accountitemin', 'item_code', 'citem_desc', 
		array('acctcode'=>'TransInForm_acct_code','acctcodedesc'=>'TransInForm_acct_code_desc',),
		false,
		array('acctid'=>'TransInForm_acct_id',)
	);
Yii::app()->clientScript->registerScript('lookupChargeItem',$js,CClientScript::POS_READY);

$js .= Script::genLookupButtonEx('btnProduct', 'product', 'product_id', 'product_name');
Yii::app()->clientScript->registerScript('lookupProduct',$js,CClientScript::POS_READY);

$js = Script::genLookupSelect();
Yii::app()->clientScript->registerScript('lookupSelect',$js,CClientScript::POS_READY);

//$link = Yii::app()->createUrl('transin/delete');
//$js = "
//$('#btnDeleteData').on('click',function() {
//	$('#removedialog').modal('hide');
//	$('#rmkdialog').modal('show');
//});
//";
//Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

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


